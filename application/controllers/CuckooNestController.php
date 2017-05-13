<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class CuckooNestController extends CI_Controller {

	public function getVendors() {
		$this->load->model('CuckooNest');
		$cuckooNest = new CuckooNest();
		echo json_encode($cuckooNest::getVendors());
	}

	public function getVendor($id) {
		$this->load->model('CuckooNest');
		$cuckooNest = new CuckooNest();
		echo json_encode($cuckooNest::getVendor($id));
	}

	public function addVendor() {
		$this->load->model('CuckooNest');
		$cuckooNest = new CuckooNest();

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

		echo json_encode($cuckooNest::addVendor($data));
	}

	public function updateVendor() {
		$this->load->model('CuckooNest');
		$cuckooNest = new CuckooNest();

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

		echo json_encode($cuckooNest::updateVendor($id, $data));
	}

	public function getProductsByVendor($id){
		$this->load->model('CuckooNest');
		$cuckooNest = new CuckooNest();
		echo json_encode($cuckooNest::getProductsByVendor($id));
	}

	public function addProducts() {
		$this->load->model('CuckooNest');
		$cuckooNest = new CuckooNest();

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

		echo json_encode($cuckooNest::addProducts(['newData' => $newData, 'editData' => $editData]));
	}
}