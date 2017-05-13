<?php

require_once __DIR__ . '../../libraries/Place.php';

class Malki extends Place {

	private static $_conn = null;

	public function __construct() {

		if (self::$_conn === null) {
			$CI =& get_instance();
			self::$_conn = $CI->load->database('malki', TRUE);
		}
	}


	static function getVendors() {
		return parent::_getVendors(self::$_conn);
	}

	static function getVendor($id) {
		return parent::_getVendor($id, self::$_conn);
	}

	static function addVendor($data) {
		return parent::_addVendor($data, self::$_conn);
	}

	static function updateVendor($id, $data) {
		return parent::_updateVendor($id, $data, self::$_conn);
	}

	static function getProductsByVendor($id) {
		return parent::_getProductsByVendor($id, self::$_conn);
	}

	static function addProducts($data) {
		return parent::_addProducts($data, self::$_conn);
	}
}