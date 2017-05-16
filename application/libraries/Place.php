<?php


abstract class Place {

	protected static function _getVendors() {
		$conn = getConnection();

		$sql = "SELECT company_name AS name, id FROM vendors";
		$query = $conn->query($sql);

		$results = null;
		if ($query->result()) {
			$results['category'] = 'vendors';
			foreach ($query->result() as $row) {
				$vendors[$row->id] = $row->name;
			}

			$results['vendors'] = $vendors;
		}

		return $results;
	}

	protected static function _getVendor($id) {
		$conn = getConnection();

		$sql = "SELECT * FROM vendors WHERE id = (" . $conn->escape($id) . ")";
		$query = $conn->query($sql);

		$results = null;
		if ($query->result()) {

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

	protected static function _addVendor($data) {
		$conn = getConnection();

		$conn->insert('vendors', $data);

		return ['newId' => $conn->insert_id()];
	}

	protected static function _updateVendor($id, $data) {
		$conn = getConnection();

		$where = "id = {$id}";
		$query = $conn->update('vendors', $data, $where);

		return $query;
	}

	protected static function _getProductsByVendor($id) {
		$conn = getConnection();

		$sql = "SELECT * FROM products WHERE vendorId = (" . $conn->escape($id) . ")";
		$query = $conn->query($sql);

		$results = null;
		if ($query->result()) {

			foreach ($query->result() as $row) {
				$products[$row->id] = [
					'name' => $row->name,
					'price' => $row->price,
					'measurement' => $row->weight
				];
			}
			$results['products'] = $products;
		}

		return $results;
	}

	protected static function _addProducts($data) {
		$result = null;
		$conn = getConnection();

		if ($data['newData']) {
			$count = count($data['newData']);

			$conn->insert_batch('products', $data['newData']);

			$first_id = $conn->insert_id();

			$last_id = $first_id + ($count - 1);

			$result['new'] = ['firstId' => $first_id, 'lastId' => $last_id];
		}
		if ($data['editData']) {

			$conn->update_batch('products', $data['editData'], 'id');


			$result['edit'] = 'ok';
		}

		return $result;
	}

	protected static function _getReceiptsByVendor($id, $month, $year) {
		$conn = getConnection();

		$sql =
			<<<SQL
SELECT 
	*, DATE_FORMAT(`date`, '%d/%m/%Y') AS formattedDate
FROM 
	prince.receipts
WHERE 
	`vendorId` = {$conn->escape($id)}
AND (
	YEAR(`date`) = {$conn->escape($year)} 
AND
	MONTH(`date`) = {$conn->escape($month)}
	)
ORDER BY 
	`date`;
SQL;
		$query = $conn->query($sql);

		$results = null;
		if ($query->result()) {

			foreach ($query->result() as $row) {
				$receipts[$row->id] = [
					'date' => $row->formattedDate,
					'serial' => $row->serial,
					'amount' => $row->amount,
					'refund' => $row->refund,
					'charge' => $row->charge,
					'approved' => $row->approved
				];
			}
			$results['receipts'] = $receipts;
		}

		return $results;
	}
}