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

	public function getLocation() {
		$location = null;
		$user = getUserType();
		if ($user === 'Prince')
//		if ($user === 'God')
			$location = ['id' => 0, 'codeName' => 'prince', 'displayName' => 'הנסיך'];
		elseif ($user === 'SuraMare')
			$location = ['id' => 1, 'codeName' => 'suraMare', 'displayName' => 'סורה-מארה'];
		elseif ($user === 'Malki')
			$location = ['id' => 2, 'codeName' => 'malki', 'displayName' => 'מלכי'];
		elseif ($user === 'CuckooNest')
			$location = ['id' => 3, 'codeName' => 'cuckooNest', 'displayName' => 'קן הקוקיה'];
		elseif ($user === 'God')
			$location = [
				['id' => 0, 'codeName' => 'prince', 'displayName' => 'הנסיך'],
				['id' => 1, 'codeName' => 'suraMare', 'displayName' => 'סורה-מארה'],
				['id' => 2, 'codeName' => 'malki', 'displayName' => 'מלכי'],
				['id' => 3, 'codeName' => 'cuckooNest', 'displayName' => 'קן הקוקיה'],
			];

		echo json_encode($location);
	}
}
