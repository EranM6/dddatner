<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Vendors extends CI_Controller {

	public function getVendors() {
		if (checkForUser()) {
			echo json_encode($this->place->getVendors());
		} else {
			show_error("goDie", 401, $heading = 'An Error Was Encountered');
		}
	}


	public function getVendor($id) {
		echo json_encode($this->place->getVendors($id));
	}

	public function addVendor() {

		$postData = file_get_contents("php://input");
		$request = json_decode($postData);

		$data = [
			'company_name' => $request->name,
			'agent_name' => $request->agent->name,
			'agent_number' => $request->agent->phoneNumber,
			'driver_name' => $request->driver->name,
			'driver_number' => $request->driver->phoneNumber,
			'orders_number' => $request->orders->phoneNumber,
			'discount' => $request->discount,
			'minimum_order' => $request->orders->minimum,
			'orders_days' => json_encode($request->orders->days),
			'orders_hours' => json_encode($request->orders->hours)
		];

		echo json_encode($this->place->addVendor($data));
	}

	public function updateVendor() {

		$postData = file_get_contents("php://input");
		$request = json_decode($postData);

		$id = $request->id;
		$data = [
			'company_name' => $request->name,
			'agent_name' => $request->agent->name,
			'agent_number' => $request->agent->phoneNumber,
			'driver_name' => $request->driver->name,
			'driver_number' => $request->driver->phoneNumber,
			'orders_number' => $request->orders->phoneNumber,
			'discount' => $request->discount,
			'minimum_order' => $request->orders->minimum,
			'orders_days' => json_encode($request->orders->days),
			'orders_hours' => json_encode($request->orders->hours)
		];

		echo json_encode($this->place->updateVendor($id, $data));
	}

	public function getProductsByVendor($id){
		echo json_encode($this->place->getProductsByVendor($id));
	}

	public function addProducts() {

		$postData = file_get_contents("php://input");
		$request = json_decode($postData);
		$newData = [];
		$editData = [];

		if ($request -> newProducts) {
			foreach ($request -> newProducts as $product) {

				$newData[] = [
					'name' => $product->name,
					'price' => $product->price,
					'weight' => $product->measurement,
					'vendorId' => $product->vendorId
				];
			}
		}

		if ($request -> editProducts) {
			foreach ($request -> editProducts as $product) {

				$editData[] = [
					'id' => $product->id,
					'name' => $product->name,
					'price' => $product->price,
					'weight' => $product->measurement,
					'vendorId' => $product->vendorId
				];
			}
		}

		echo json_encode($this->place->addProducts(['newData' => $newData, 'editData' => $editData]));
	}

	public function getReceiptsByVendor($id, $month, $year){
		echo json_encode($this->place->getReceiptsByVendor($id, $month, $year));
	}

	public function addReceipts() {

		$postData = file_get_contents("php://input");
		$request = json_decode($postData);
		$newData = [];
		$editData = [];

		if ($request -> newReceipts) {
			foreach ($request -> newReceipts as $receipt) {

				$newData[] = [
					'date' => $receipt->date,
					'serial' => $receipt->serial,
					'amount' => $receipt->amount,
					'charge' => $receipt->charge,
					'approved' => $receipt->approved,
					'comment' => $receipt->comment,
					'vendorId' => $receipt->vendorId
				];
			}
		}

		if ($request -> editReceipts) {
			foreach ($request -> editReceipts as $receipt) {

				$editData[] = [
					'id' => $receipt->id,
					'date' => $receipt->date,
					'serial' => $receipt->serial,
					'amount' => $receipt->amount,
					'charge' => $receipt->charge,
					'approved' => $receipt->approved,
					'comment' => $receipt->comment,
					'vendorId' => $receipt->vendorId
				];
			}
		}

		echo json_encode($this->place->addReceipts([
			'newData' => $newData,
			'editData' => $editData,
			'month' => $request -> month,
			'year' => $request -> year,
			'vendorId' => $request -> vendorId
		]));
	}

	public function removeReceipt($id) {
		echo json_encode($this->place->removeReceipt($id));
	}

	public function closeMonth() {
		$postData = file_get_contents("php://input");
		$request = json_decode($postData);

		$month = $request -> month;
		$year = $request -> year;
		$vendorId = $request -> vendorId;
		$charge = $request -> charge;
		$refund = $request -> refund;

		echo json_encode($this->place->closeMonth($month, $year, $vendorId, $charge, $refund));
	}

	public function getHistory($id){
		echo json_encode($this->place->getHistory($id));
	}

	public function getProductsFile($id) {
		$file = createSingleSheetExcelFile("products", $id);

		// Sending headers to force the user to download the file
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file["name"] . '"');
		header('Cache-Control: max-age=0');

		echo json_encode(['name' => $file["name"], 'file' => $file["file"]->save('php://output')]);
	}

	public function getReceiptsFile($id, $month, $year) {
		$file = createSingleSheetExcelFile("receipts", $id, $month, $year);

		// Sending headers to force the user to download the file
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file["name"] . '"');
		header('Cache-Control: max-age=0');

		echo json_encode(['name' => $file["name"], 'file' => $file["file"]->save('php://output')]);
	}
}