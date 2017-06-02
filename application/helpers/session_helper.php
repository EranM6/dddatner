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
	loadSession()->session->set_userdata(['user' => $_SERVER['REMOTE_USER']]);
	setLocation($_SERVER['REMOTE_USER']);
}

function checkForUser(){
	$user = loadSession()->session->userdata('user');
	return isset($user);
}

function killSession(){
	session_unset();
	loadSession()->session->sess_destroy();
	$_SERVER['REMOTE_USER'] = null;
}

function getUser() {
	return loadSession()->session->userdata('user');
}

function getLocation() {
	return loadSession()->session->userdata('place');
}

function setLocation($placeId){
	loadSession()->session->set_userdata('place', $placeId);
}

function validConnection(){
	$CI = getInstance();
	return $CI;
}
