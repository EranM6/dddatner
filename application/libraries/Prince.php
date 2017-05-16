<?php

require_once __DIR__ . './Place.php';

class Prince extends Place {

	static function getVendors() {
		return parent::_getVendors();
	}

	static function getVendor($id) {
		return parent::_getVendor($id);
	}

	static function addVendor($data) {
		return parent::_addVendor($data);
	}

	static function updateVendor($id, $data) {
		return parent::_updateVendor($id, $data);
	}

	static function getProductsByVendor($id) {
		return parent::_getProductsByVendor($id);
	}

	static function addProducts($data) {
		return parent::_addProducts($data);
	}

	static function getReceiptsByVendor($id, $month, $year) {
		return parent::_getReceiptsByVendor($id, $month, $year);
	}
}