<?php


function setUserSession(){
	$CI =& get_instance();

	$CI->load->library('session');
	$CI->session->set_userdata('user', $_SERVER['REMOTE_USER']);

}

function userLocation() {
	$CI =& get_instance();
	$CI->load->library('session');

	$user = $CI->session->userdata('user');

	if (!isset($user)) { return false; } else { return $user; }
}