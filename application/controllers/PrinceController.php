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

	public function getProductsByVendor($id){
		$this->load->model('Prince');
		$prince = new Prince();
		echo json_encode($prince::getProductsByVendor($id));
	}

}