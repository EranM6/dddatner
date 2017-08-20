<?php

function getAllScriptFiles($root, $dir, $fileList) {

	$files = scandir($root . $dir);
	forEach ($files as $file) {
		if ($file[0] != ".") {
			if (is_dir($root . $dir . '/' . $file)) {
				$fileList = getAllScriptFiles($root, $dir . '/' . $file, $fileList);
			} else {
				if (pathinfo($file)['extension'] == "js") {
					array_push($fileList, './' . $dir . '/' . $file);
				}
			}
		};
	}
	return $fileList;
}

function createSingleSheetExcelFile($section, $id, $month = null, $year = null) {
	$CI =& get_instance();

	$CI->load->library('place');

	$query = null;
	$name = "";

	$conn = validConnection();
	$conn->load->database();

	if ($section == "products") {
		$query = $CI->place->getProductsByVendor($id, true);
		$vendorName = $CI->place->getVendors($id, true);
		$name = $vendorName . "-סחורה-" . date('dMy') . ".xls";
	} elseif ($section == "receipts") {
		$query = $CI->place->getReceiptsByVendor($id, $month, $year, true);
		$place = $CI->place->getPlaces(null, getLocation())[0];
		$vendorName = $CI->place->getVendors($id, true);
		$name = $vendorName . "-תעודות-" . $place["heb_name"] . "-" . $month . "/" . $year . ".xls";
	} elseif ($section == "entry") {
		$query = $CI->place->getEntryItems($id, true);
		$entry = $CI->place->getEntryById($id, true);
		$name = $entry["place"] . "-ספירות-" . $entry["vendor"] . "-" . $entry["month"] . "/" . $entry["year"] . ".xls";
	}

	if (!$query)
		return false;

	// Starting the PHPExcel library
	$CI->load->library('Excel');

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setTitle($section."-export")->setDescription("none")->setCreator("Eran");

	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle($section, false);
	$objPHPExcel->getActiveSheet()->setRightToLeft(true);
	// Field names in the first row
	$fields = $query->list_fields();
	$col = 0;
	foreach ($fields as $field) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
		if ($field == "משקל/יחידה")
			$measurementColumn = $col;
		if ($field == "חיוב/זיכוי")
			$chargeColumn = $col;
		if ($field == "סכום" || $field == "כמות" || $field == "מחיר")
			$numberFormat[] = $col;

		$col++;

	}

	if ($section == "entry")
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, "סכום");

	// Fetching the table data
	$row = 2;
	foreach ($query->result() as $data) {
		$col = 0;
		foreach ($fields as $field) {
			$field = $data->$field;

			if(isset($measurementColumn) && $measurementColumn == $col)
				$field = $field == '1' ? 'משקל' : ($field == '0' ? 'יחידה' : $field);

			if(isset($chargeColumn) && $chargeColumn == $col)
				$field = $field == '1' ? 'חיוב' : ($field == '0' ? 'זיכוי' : $field);

			if(isset($numberFormat) && in_array($col, $numberFormat)) {
				$objPHPExcel->getActiveSheet()
					->getStyleByColumnAndRow($col, $row)
					->getNumberFormat()
					->setFormatCode(
						PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
					);
			}

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $field);
			$col++;
		}

		if ($section == "entry") {
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, "=(B" . $row . "*D" . $row . ")");
			$objPHPExcel->getActiveSheet()
				->getStyleByColumnAndRow($col, $row)
				->getNumberFormat()
				->setFormatCode(
					PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
				);
		}
		$row++;
	}

	foreach(range('A','E') as $columnID) {
		$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
	}

	$objPHPExcel->setActiveSheetIndex(0);

	$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

	return ['name' => $name, 'file' => $objWriter->save('php://output')];
}

function createMultiSheetExcelFile($section, $month = null, $year = null) {
	$CI =& get_instance();

	$CI->load->library('place');

	$data = null;
	$name = "";

	$conn = validConnection();
	$conn->load->database();

	if ($section === "entry")
		$data = $CI->place->getEntriesByDate($month, $year, true);


	if (!$data)
		return false;
	// Starting the PHPExcel library
	$CI->load->library('Excel');

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setTitle($section."-export")->setDescription("none")->setCreator("Eran");

	$counter = 0;
	$vendorsNames = array_keys($data);
	foreach ($data as $vendor){
		if ($counter > 0)
			$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex($counter);
		$objPHPExcel->getActiveSheet()->setTitle($vendorsNames[$counter], false);
		$objPHPExcel->getActiveSheet()->setRightToLeft(true);

		$fields = array_keys($vendor[0]);

		$row = 1;
		foreach ($vendor as $item){

			$col = 0;
			if ($row === 1){
				foreach ($fields as $field) {
					$field = $field === "name" ? "מוצר" :
						($field === "price" ? "מחיר" :
							($field === "measurement" ? "משקל/יחידה" :
								($field === "amount" ? "כמות" : "")
							)
						);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $field);
					if ($field == "משקל/יחידה")
						$measurementColumn = $col;
					if ($field == "סכום" || $field == "כמות" || $field == "מחיר")
						$numberFormat[] = $col;

					$col++;
				}
				if ($section == "entry")
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, "סכום");

				$col = 0;
				$row++;
			}
			foreach ($item as $field) {

				if(isset($measurementColumn) && $measurementColumn == $col)
					$field = $field == '1' ? 'משקל' : ($field == '0' ? 'יחידה' : $field);

				if(isset($chargeColumn) && $chargeColumn == $col)
					$field = $field == '1' ? 'חיוב' : ($field == '0' ? 'זיכוי' : $field);

				if(isset($numberFormat) && in_array($col, $numberFormat)) {
					$objPHPExcel->getActiveSheet()
						->getStyleByColumnAndRow($col, $row)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
						);
				}
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $field);
				$col++;
			}

			if ($section == "entry" && $row > 1) {
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, "=(B" . $row . "*D" . $row . ")");
				$objPHPExcel->getActiveSheet()
					->getStyleByColumnAndRow($col, $row)
					->getNumberFormat()
					->setFormatCode(
						PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00
					);
			}

			$row++;
		}

		foreach(range('A','E') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}
		$counter++;
	}

	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

	return ['name' => $name, 'file' => $objWriter->save('php://output')];
}