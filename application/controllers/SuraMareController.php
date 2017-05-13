<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class SuraMareController extends CI_Controller {

	public function getVendors() {
		$this->load->model('SuraMare');
		$suraMare = new SuraMare();
		echo json_encode($suraMare::getVendors());
	}

	public function getVendor($id) {
		$this->load->model('SuraMare');
		$suraMare = new SuraMare();
		echo json_encode($suraMare::getVendor($id));
	}

	public function addVendor() {
		$this->load->model('SuraMare');
		$suraMare = new SuraMare();

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

		echo json_encode($suraMare::addVendor($data));
	}

	public function updateVendor() {
		$this->load->model('SuraMare');
		$suraMare = new SuraMare();

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

		echo json_encode($suraMare::updateVendor($id, $data));
	}

	public function getProductsByVendor($id){
		$this->load->model('SuraMare');
		$suraMare = new SuraMare();
		echo json_encode($suraMare::getProductsByVendor($id));
	}

	public function addProducts() {
		$this->load->model('SuraMare');
		$suraMare = new SuraMare();

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

		echo json_encode($suraMare::addProducts(['newData' => $newData, 'editData' => $editData]));
	}
}