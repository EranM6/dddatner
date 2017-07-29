<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Inventory extends CI_Controller {

	public function getVendorInventory($id, $month, $year) {
		if (checkForUser()) {
			echo json_encode($this->place->getVendorInventory($id, $month, $year));
		} else {
			show_error("goDie", 401, $heading = 'An Error Was Encountered');
		}
	}

	public function saveRecord() {

		$postData = file_get_contents("php://input");
		$request = json_decode($postData);

		$entry = [
			'vendorId' => $request->vendorId,
			'month' => $request->month,
			'year' => $request->year,
			'total' => $request->totalAmount,
			'close' => ($request->close) ? 1 : 0
		];
		$items = $request->productsList;
		echo json_encode($this->place->saveRecords($entry, $items));
	}

	public function getEntries() {
		echo json_encode($this->place->getEntries());
	}
}