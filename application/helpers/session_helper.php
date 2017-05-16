<?php

function getInstance(){
	$CI =& get_instance();
	return $CI;
}

function loadSession(){
	$CI = getInstance();
	$CI->load->library('session');
	return $CI;
}

function setUserSession(){
	loadSession()->session->set_userdata('user', $_SERVER['REMOTE_USER']);
}

function getUserType(){
	$user = loadSession()->session->userdata('user');

	if (!isset($user)) { return false; } else { return $user; }

}

function getLocation() {
	return loadSession()->session->userdata('location');
}

function setLocation($location){
	loadSession()->session->set_userdata('location', $location);
}

function business(){
	$location = getLocation();
	getInstance()->load->library(lcfirst($location));
}

function getConnection(){
	$CI = getInstance();
	return $CI->load->database(lcfirst(getLocation()), TRUE);
}