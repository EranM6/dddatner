<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Controller extends CI_Controller {

	public function getVendors() {

		echo json_encode($this->place->getVendors());
	}

	public function getVendor($id) {

		echo json_encode($this->place->getVendor($id));
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
			'minimum_order' => $request->orders->minimum.' '
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
			'minimum_order' => $request->orders->minimum.' '
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
					'date' => "STR_TO_DATE('".$receipt->date."', '%d/%m/%Y')",
					'serial' => $receipt->serial,
					'amount' => $receipt->amount,
					'charge' => $receipt->charge,
					'approved' => $receipt->approved,
					'vendorId' => $receipt->vendorId
				];
			}
		}

		if ($request -> editReceipts) {
			foreach ($request -> editReceipts as $receipt) {

				$editData[] = [
					'id' => $receipt->id,
					'date' => "STR_TO_DATE('".$receipt->date."', '%d/%m/%Y')",
					'serial' => $receipt->serial,
					'amount' => $receipt->amount,
					'charge' => $receipt->charge,
					'approved' => $receipt->approved,
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
}