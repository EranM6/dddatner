<?php


abstract class Place {

	protected static function _getVendors($conn) {

		$sql = "SELECT company_name AS name, id FROM vendors";
		$query = $conn->query($sql);

		$results = null;
		if($query->result()) {
			$results['category'] = 'vendors';
			foreach ($query->result() as $row) {
				$vendors[$row->id] = $row->name;
			}

			$results['vendors'] = $vendors;
		}

		return $results;
	}

	protected static function _getVendor($id, $conn) {

		$sql = "SELECT * FROM vendors WHERE id = (".$conn->escape($id).")";
		$query = $conn->query($sql);

		$results = null;
		if($query->result()) {

			foreach ($query->result() as $row) {
				$vendor = [
					'id' => $row->id,
					'name' => $row->company_name,
					'agent' => [
						'name' => $row->agent_name,
						'phoneNumber' => $row->agent_number
					],
					'driver' => [
						'name' => $row->driver_name,
						'phoneNumber' => $row->driver_number
					],
					'orders' => [
						'phoneNumber' => $row->orders_number,
						'minimum' => $row->minimum_order
					],
					'discount' => $row->discount
				];
			}
			$results['vendor'] = $vendor;
		}

		return $results;
	}

	protected static function _getProductsByVendor($id, $conn) {

		$sql = "SELECT * FROM products WHERE vendorId = (".$conn->escape($id).")";
		$query = $conn->query($sql);

		$results = null;
		if($query->result()) {

			foreach ($query->result() as $row) {
				$products[$row->id] = [

					'name' => $row->name,
					'price' => $row->price,
					'measurement' => $row->weight === '1' ? 'weight' : 'unit'
				];
			}
			$results['products'] = $products;
		}

		return $results;
	}

}