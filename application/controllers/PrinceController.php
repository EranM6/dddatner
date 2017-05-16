<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class PrinceController extends CI_Controller {

	public function getVendors() {
		setLocation('Prince');
		business();
		echo json_encode($this->prince->getVendors());
	}

	public function getVendor($id) {
		business();
		echo json_encode($this->prince->getVendor($id));
	}

	public function addVendor() {
		business();

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

		echo json_encode($this->prince->addVendor($data));
	}

	public function updateVendor() {
		business();

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

		echo json_encode($this->prince->updateVendor($id, $data));
	}

	public function getProductsByVendor($id){
		business();
		echo json_encode($this->prince->getProductsByVendor($id));
	}

	public function addProducts() {
		business();

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

		echo json_encode($this->prince->addProducts(['newData' => $newData, 'editData' => $editData]));
	}

	public function getReceiptsByVendor($id, $month, $year){
		business();
		echo json_encode($this->prince->getReceiptsByVendor($id, $month, $year));
	}
}