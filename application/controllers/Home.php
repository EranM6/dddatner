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
		if (checkForUser()) {
			$postData = file_get_contents("php://input");
			$placeId = json_decode($postData);
			$placeId = $placeId->placeId;
			setLocation($placeId);
		}
		else{
			show_error("goDie", 401, $heading = 'An Error Was Encountered');
		}
	}
	public function getOut() {
		killSession();
		print_r($this->getLocation());
		print_r($_SERVER['REMOTE_USER']);
		show_error("goDie", 401, $heading = 'An Error Was Encountered');
	}

	public function getLocation() {
		if (checkForUser()) {
			$output = null;
			$user = getUser();

			if ($user === 'God') {
				$output = $this->place->getPlaces();
			}else {
				$output = $this->place->getPlaces(lcfirst($user))[0];
				setLocation($output['id']);
			}

			echo json_encode ($output);
		}else{
			show_error("goDie", 401, $heading = 'An Error Was Encountered');
		}
	}
}
