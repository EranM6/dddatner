<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class PrinceController extends CI_Controller {

	public function getVendors() {
		$this->load->model('Prince');
		$prince = new Prince();
		echo json_encode($prince::getVendors());
	}

	public function getVendor($id) {
		$this->load->model('Prince');
		$prince = new Prince();
		echo json_encode($prince::getVendor($id));
	}

	public function addVendor() {
		$this->load->model('Prince');
		$prince = new Prince();

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

		echo json_encode($prince::addVendor($data));
	}

	public function updateVendor() {
		$this->load->model('Prince');
		$prince = new Prince();

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

		echo json_encode($prince::updateVendor($id, $data));
	}

	public function getProductsByVendor($id){
		$this->load->model('Prince');
		$prince = new Prince();
		echo json_encode($prince::getProductsByVendor($id));
	}

}