<?php


class Place {

	static function getVendors() {
		$conn = getConnection();

		$sql = "SELECT company_name AS name, id FROM vendors";
		$query = $conn->query($sql);

		$results = null;
		if ($query->result()) {
			$results['category'] = 'vendors';
			foreach ($query->result() as $row) {
				$vendors[$row->id] = [
					"id" =>$row->id,
					"name" =>$row->name
				];
			}

			$results['vendors'] = $vendors;
		}

		return $results;
	}

	static function getVendor($id) {
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

	static function addVendor($data) {
		$conn = getConnection();

		$conn->insert('vendors', $data);

		return ['newId' => $conn->insert_id()];
	}

	static function updateVendor($id, $data) {
		$conn = getConnection();

		$where = "id = {$id}";
		$query = $conn->update('vendors', $data, $where);

		return $query;
	}

	static function getProductsByVendor($id) {
		$conn = getConnection();

		$sql = "SELECT * FROM products WHERE vendorId = (" . $conn->escape($id) . ")";
		$query = $conn->query($sql);

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

	static function getReceiptsByVendor($id, $month, $year) {
		$conn = getConnection();

		$receiptsQuery =
			<<<SQL
SELECT 
	*, DATE_FORMAT(`date`, '%d/%m/%Y') AS formattedDate
FROM 
	receipts
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

		$query = $conn->query($receiptsQuery);


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
			$details = self::getVendorReceiptsDetails($conn, $id, $month, $year);

			$results['charge'] = $details['charge'];
			$results['refund'] = $details['refund'];
			$results['notApproved'] = $details['notApproved'];
			$results['closed'] = self::checkForClosedMonth($id, $month, $year);
		}
		return $results;
	}

	static function getVendorReceiptsDetails($conn, $id, $month, $year) {
		$sql =
			<<<SQL
SELECT
  SUM(amount) as details
FROM
  receipts
WHERE
  vendorId = {$id}
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
AND (
    YEAR(`date`) = {$year}
  AND
    MONTH(`date`) = {$month}
      );
SQL;
		$query = $conn->query($sql);

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
		$conn = getConnection();

		if ($data['newData']) {
			$count = count($data['newData']);

			$values = "";
			foreach ($data['newData'] as $value) :
				$value =
					<<<VAL
({$value['date']}, 
{$value['vendorId']}, 
{$value['serial']}, 
{$value['amount']}, 
{$value['charge']}, 
{$value['approved']}),
VAL;

				$values .= $value;
			endforeach;
			$values = rtrim($values, ', ');

			$sql =
				<<<SQL
INSERT INTO
 	receipts(date, vendorId, serial, amount, charge, approved)
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
 	date = {$value['date']}, 
 	vendorId = {$value['vendorId']}, 
 	serial = {$value['serial']}, 
 	amount = {$value['amount']}, 
 	charge = {$value['charge']}, 
 	approved = {$value['approved']}
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
		$conn = getConnection();

		$sql =
			<<<SQL
INSERT INTO
 	history(month, year, vendorId, charge, refund)
VALUES ({$month}, {$year}, {$vendorId}, {$charge}, {$refund});
SQL;
		$conn->query($sql);

		return ['closed' => 'ok'];
	}

	static function checkForClosedMonth($vendorId, $month, $year) {
		$conn = getConnection();

		$sql =
			<<<SQL
SELECT EXISTS(
	SELECT * 
	FROM
		history
	WHERE 
		vendorId = {$vendorId}
	AND
		month = {$month}
	AND
		year = {$year}
	)AS exist
SQL;

		$query = $conn->query($sql);

		if ($query->result()[0]->exist == 1) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	static function getHistory($id) {
		$conn = getConnection();

		$sql = <<<SQL
SELECT *
FROM
  history
WHERE
  vendorId = {$id}
ORDER BY year DESC, month;
SQL;

		$query = $conn->query($sql);

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