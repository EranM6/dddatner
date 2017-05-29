<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index() {
		setUserSession();

		$root = ($_SERVER['DOCUMENT_ROOT'] . '/dddatner/');
		$this->load->helper('files');
		$data['root'] = $root;
		$this->load->view('home', $data);
	}

	public function setLocation() {
		$postData = file_get_contents("php://input");
		$location = json_decode($postData);

		setLocation($location->location);
	}

	public function getLocation() {
		$output = null;
		$location = getLocation();
		if ($location === 'Prince')
			$output = ['id' => 0, 'codeName' => 'prince', 'displayName' => 'הנסיך'];
		elseif ($location === 'SuraMare')
			$output = ['id' => 1, 'codeName' => 'suraMare', 'displayName' => 'סורה-מארה'];
		elseif ($location === 'Malki')
			$output = ['id' => 2, 'codeName' => 'malki', 'displayName' => 'מלכי'];
		elseif ($location === 'CuckooNest')
			$output = ['id' => 3, 'codeName' => 'cuckooNest', 'displayName' => 'קן הקוקיה'];
		elseif ($location === 'God')
			$output = [
				['id' => 0, 'codeName' => 'prince', 'displayName' => 'הנסיך'],
				['id' => 1, 'codeName' => 'suraMare', 'displayName' => 'סורה-מארה'],
				['id' => 2, 'codeName' => 'malki', 'displayName' => 'מלכי'],
				['id' => 3, 'codeName' => 'cuckooNest', 'displayName' => 'קן הקוקיה'],
			];

		echo json_encode($output);
	}
}
