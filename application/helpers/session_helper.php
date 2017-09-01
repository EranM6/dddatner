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
	setSentry();
}

function setSentry(){
    require_once "./vendor/sentry/sentry/lib/Raven/Autoloader.php";
    Raven_Autoloader::register();
    Raven_Autoloader::autoload("Raven_Client");
    $dsn = 'https://14c703f9fe024a05bf4818207219211a:ff81847a99b84ae1b1d5f87f62a3b6a6@sentry.io/210634';
    $client = new Raven_Client($dsn);
    $client->install();
}

function getSentry(){
    setSentry();
    return loadSession()->session->userdata('sentry');
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