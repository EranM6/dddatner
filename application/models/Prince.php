<?php

require_once __DIR__ . '../../libraries/Place.php';

class Prince extends Place {

	private static $_conn = null;

	public function __construct() {

		if (self::$_conn === null) {
			$CI =& get_instance();
			self::$_conn = $CI->load->database('prince', TRUE);
		}
	}


	static function getVendors() {
		return parent::_getVendors(self::$_conn);
	}

	static function getVendor($id) {
		return parent::_getVendor($id, self::$_conn);
	}

	static function getProductsByVendor($id) {
		return parent::_getProductsByVendor($id, self::$_conn);
	}

}