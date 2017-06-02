<?php


class Place {

	static function getPlaces($name = null){
		$conn = validConnection();
		$conn->load->database();

		if(!isset($name)){
			$sql = "SELECT * FROM places";
		}else{
			$sql = "SELECT * FROM places WHERE eng_name = '{$name}'";
		}

		$query = $conn->db->query($sql);

		$results = null;
		if ($query->result()) {
			foreach ($query->result() as $row) {
				$results[] = [
					'id' => $row->id,
					'heb_name' => $row->heb_name,
					'eng_name' => $row->eng_name,

				];
			}
		}
		return $results;
	}

	static function getVendors($id = null) {
		$conn = validConnection();
		$conn->load->database();
		if (!isset($id)) {

			$sql = "SELECT company_name AS name, id FROM vendors";
			$query = $conn->db->query($sql);

			$results = null;
			if ($query->result()) {
				$results['category'] = 'vendors';
				foreach ($query->result() as $row) {
					$vendors[$row->id] = [
						"id" => $row->id,
						"name" => $row->name
					];
				}

				$results['vendors'] = $vendors;
			}
		} else {

			$sql = "SELECT * FROM vendors WHERE id = (" . $conn->db->escape($id) . ")";
			$query = $conn->db->query($sql);

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
							'minimum' => $row->minimum_order,
							'days' => json_decode($row->orders_days),
							'hours' => json_decode($row->orders_hours)
						],
						'discount' => $row->discount
					];
				}
				$results['vendor'] = $vendor;
			}
		}
		return $results;
	}

	static function addVendor($data) {
		$conn = validConnection();
		$conn->load->database();

		$conn->insert('vendors', $data);

		return ['newId' => $conn->insert_id()];
	}

	static function updateVendor($id, $data) {
		$conn = validConnection();
		$conn->load->database();

		$where = "id = {$id}";
		$query = $conn->update('vendors', $data, $where);

		return $query;
	}

	static function getProductsByVendor($id) {
		$conn = validConnection();
		$conn->load->database();

		$sql = "SELECT * FROM products WHERE vendorId = (" . $conn->db->escape($id) . ")";
		$query = $conn->db->query($sql);

		$results = null;
		if ($query->result()) {

			foreach ($query->result() as $row) {
				$products[$row->id] = [
					'id' => $row->id,
					'name' => $row->name,
					'price' => $row->price,
					'measurement' => $row->weight,
					'vendorId' => $row->vendorId
				];
			}
			$results['products'] = $products;
		}

		return $results;
	}

	static function addProducts($data) {
		$result = null;
		$conn = validConnection();
		$conn->load->database();

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

	static function getReceiptsByVendor($id, $month, $year) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$receiptsQuery =
			<<<SQL
SELECT 
	*, DATE_FORMAT(`date`, '%d/%m/%Y') AS formattedDate
FROM 
	receipts
WHERE 
	`vendorId` = {$conn->db->escape($id)}
AND
	`placeId` = {$placeId}
AND (
	YEAR(`date`) = {$conn->db->escape($year)} 
AND
	MONTH(`date`) = {$conn->db->escape($month)}
	)
ORDER BY 
	`date`;
SQL;

		$query = $conn->db->query($receiptsQuery);


		$results = null;
		if ($query->result()) {
			foreach ($query->result() as $row) {
				$receipts[$row->id] = [
					'id' => $row->id,
					'date' => $row->formattedDate,
					'serial' => $row->serial,
					'amount' => $row->amount,
					'charge' => $row->charge,
					'approved' => $row->approved,
					'vendorId' => $row->vendorId
				];
			}
			$results['receipts'] = $receipts;
		}

		if ($results) {
			$details = self::getVendorReceiptsDetails($conn, $placeId, $id, $month, $year);

			$results['charge'] = $details['charge'];
			$results['refund'] = $details['refund'];
			$results['notApproved'] = $details['notApproved'];
		}

		$results['closed'] = self::checkForClosedMonth($id, $month, $year);
		return $results;
	}

	static function getVendorReceiptsDetails($conn, $placeId, $id, $month, $year) {
		$sql =
			<<<SQL
SELECT
  SUM(amount) as details
FROM
  receipts
WHERE
  vendorId = {$id}
AND
	placeId = {$placeId}
  AND (
    YEAR(`date`) = {$year}
    AND
    MONTH(`date`) = {$month}
  )
  AND
  charge = '0'
UNION
SELECT
  SUM(amount)
FROM
  receipts
WHERE
  vendorId = {$id}
AND
	placeId = {$placeId}
  AND (
    YEAR(`date`) = {$year}
    AND
    MONTH(`date`) = {$month}
  )
  AND
  charge = '1'
UNION
SELECT COUNT(approved)
FROM receipts
WHERE
  approved = '0'
AND
  vendorId = {$id}
AND
	placeId = {$placeId}
AND (
    YEAR(`date`) = {$year}
  AND
    MONTH(`date`) = {$month}
      );
SQL;
		$query = $conn->db->query($sql);

		$details = [];
		if ($query->result()) {
			$details['refund'] = $query->result()[0]->details;
			$details['charge'] = $query->result()[1]->details;
			$details['notApproved'] = $query->result()[2]->details;
		}

		return $details;
	}

	static function addReceipts($data) {
		$result = null;
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		if ($data['newData']) {
			$count = count($data['newData']);

			$values = "";
			foreach ($data['newData'] as $value) :
				$value =
					<<<VAL
(STR_TO_DATE('{$value['date']}', '%d/%m/%Y'), 
'{$value['vendorId']}', 
'{$value['serial']}', 
'{$value['amount']}', 
'{$value['charge']}', 
'{$value['approved']}',
'{$placeId}'),
VAL;

				$values .= $value;
			endforeach;
			$values = rtrim($values, ', ');

			$sql =
				<<<SQL
INSERT INTO
 	receipts(date, vendorId, serial, amount, charge, approved, placeId)
VALUES {$values};
SQL;
			$conn->query($sql);

			$first_id = $conn->insert_id();

			$last_id = $first_id + ($count - 1);

			$result['new'] = ['firstId' => $first_id, 'lastId' => $last_id];
		}
		if ($data['editData']) {

			$values = "";

			foreach ($data['editData'] as $value) :

				$sql =
					<<<VAL
UPDATE receipts
SET 
 	date = STR_TO_DATE('{$value['date']}', '%d/%m/%Y'), 
 	vendorId = '{$value['vendorId']}', 
 	serial = '{$value['serial']}', 
 	amount = '{$value['amount']}', 
 	charge = '{$value['charge']}', 
 	approved = '{$value['approved']}',
 	placeId = '{$placeId}'
WHERE id = '{$value['id']}';
VAL;

				$values .= $sql;

			endforeach;


			$conn->query($values);

			$result['edit'] = 'ok';
		}

		$sql =
			<<<VAL
SELECT COUNT(approved) as counts
FROM receipts
WHERE
  approved = '0'
AND
  vendorId = {$data["vendorId"]}
AND (
    YEAR(`date`) = {$data["year"]}
  AND
    MONTH(`date`) = {$data["month"]}
      );
VAL;

		$query = $conn->conn_id->prepare($sql);
		$count = $query->execute();
		$count = $query->fetchAll(PDO::FETCH_ASSOC);
		$result['notApproved'] = $count[0]["counts"];
		return $result;
	}

	static function closeMonth($month, $year, $vendorId, $charge, $refund) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sql =
			<<<SQL
INSERT INTO
 	history(month, year, vendorId, charge, refund, placeId)
VALUES ({$month}, {$year}, {$vendorId}, {$charge}, {$refund}, {$placeId});
SQL;
		$conn->query($sql);

		return ['closed' => 'ok'];
	}

	static function checkForClosedMonth($vendorId, $month, $year) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sql =
			<<<SQL
SELECT EXISTS(
	SELECT * 
	FROM
		history
	WHERE 
		vendorId = {$vendorId}
	AND
		placeId = {$placeId}
	AND
		month = {$month}
	AND
		year = {$year}
	)AS exist
SQL;

		$query = $conn->db->query($sql);

		if ($query->result()[0]->exist == 1) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	static function getHistory($id) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sql = <<<SQL
SELECT *
FROM
  history
WHERE
  vendorId = {$id}
AND
	placeId = {$placeId}
ORDER BY year DESC, month;
SQL;

		$query = $conn->db->query($sql);

		$results = null;
		if ($query->result()) {

			foreach ($query->result() as $row) {
				$closedMonths[$row->year][$row->month] = [
					'id' => $row->id,
					'month' => $row->month,
					'year' => $row->year,
					'charge' => $row->charge,
					'refund' => $row->refund
				];
			}
			$results['records'] = $closedMonths;
		}

		return $results;
	}
}