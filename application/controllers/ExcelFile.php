<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class ExcelFile extends CI_Controller {

	function index($vendorId) {

		$conn = validConnection();
		$conn->load->database();

		$vendorName = $this->place->getVendors($vendorId, true);
		$query = $this->place->getProductsByVendor($vendorId, true);

		if (!$query)
			return false;

		// Starting the PHPExcel library
		$this->load->library('Excel');
//		$this->load->thirdParty('PHPExcel/IOFactory');

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setTitle("export")->setDescription("none");

		$objPHPExcel->setActiveSheetIndex(0);

		// Field names in the first row
		$fields = $query->list_fields();
		$col = 0;
		foreach ($fields as $field) {
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
			$col++;
		}

		// Fetching the table data
		$row = 2;
		foreach ($query->result() as $data) {
			$col = 0;
			foreach ($fields as $field) {

				$field = $data->$field == '1' ? 'משקל' : ($data->$field == '0' ? 'יחידה' : $data->$field);

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $field);
				$col++;
			}

			$row++;
		}

		$objPHPExcel->setActiveSheetIndex(0);

		$objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

		$name = $vendorName . "-סחורה-" . date('dMy') . ".xls";

		// Sending headers to force the user to download the file
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $name . '"');
		header('Cache-Control: max-age=0');

		echo json_encode(['name' => $name, 'file' => $objWriter->save('php://output')]);
	}
}