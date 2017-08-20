<?php


class Place {

	static function getPlaces($name = null, $id = null) {
		$conn = validConnection();
		$conn->load->database();

		if (isset($name)) {
			$sql = "SELECT * FROM places WHERE eng_name = '{$name}'";
		}elseif (isset($id)) {
			$sql = "SELECT * FROM places WHERE id = '{$id}'";
		} else {
			$sql = "SELECT * FROM places";
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

	static function getVendors($id = null, $file = false) {
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
			if (!$file) {
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
			}else{
				$sql = "SELECT company_name FROM vendors WHERE id = (" . $conn->db->escape($id) . ")";
				$query = $conn->db->query($sql);
				return $query->result()[0]->company_name;
			}
		}
		return $results;
	}

	static function addVendor($data) {
		$conn = validConnection();
		$conn->load->database();

		$conn->db->insert('vendors', $data);

		return ['newId' => $conn->db->insert_id()];
	}

	static function updateVendor($id, $data) {
		$conn = validConnection();
		$conn->load->database();

		$where = "id = {$id}";
		$query = $conn->db->update('vendors', $data, $where);

		return $query;
	}

	static function getProductsByVendor($id, $file = false) {
		$conn = validConnection();
		$conn->load->database();

		$sqlFields = !$file ? "*" : "name AS 'מוצר', price AS 'מחיר', weight AS 'משקל/יחידה'";

		$sql = "SELECT {$sqlFields} FROM products WHERE vendorId = (" . $conn->db->escape($id) . ")";

		$query = $conn->db->query($sql);

		if ($file)
			return $query;

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

			$conn->db->insert_batch('products', $data['newData']);

			$first_id = $conn->db->insert_id();

			$last_id = $first_id + ($count - 1);

			$result['new'] = ['firstId' => $first_id, 'lastId' => $last_id];
		}
		if ($data['editData']) {

			$conn->db->update_batch('products', $data['editData'], 'id');


			$result['edit'] = 'ok';
		}

		return $result;
	}

	static function getReceiptsByVendor($id, $month, $year, $file = false) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sqlFields = !$file ?
			"*, DATE_FORMAT(`date`, '%d/%m/%Y') AS formattedDate" :
			"DATE_FORMAT(`date`,
			 '%d/%m/%Y') AS 'תאריך',
			  serial AS 'חשבונית/תעודה',
			   amount AS 'סכום',
			    charge AS 'חיוב/זיכוי',
			     comment AS 'הערה'";

		$receiptsQuery =
			<<<SQL
SELECT 
	{$sqlFields}
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
AND
	`dead` = false
ORDER BY 
	`date`;
SQL;

		$query = $conn->db->query($receiptsQuery);

		if ($file)
			return $query;

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
					'vendorId' => $row->vendorId,
					'comment' => $row->comment
				];
			}
			$results['receipts'] = $receipts;
		}

		if ($results) {
			$details = self::getVendorReceiptsDetails($conn, $placeId, $id, $month, $year);

			$results['charge'] = $details['charge'];
			$results['total'] = $details['total'];
			$results['notApproved'] = $details['notApproved'];
		}

		$results['closed'] = self::checkForClosedMonth($id, $month, $year);
		return $results;
	}

	static function getVendorReceiptsDetails($conn, $placeId, $id, $month, $year) {
		$sql =
			<<<SQL
SELECT
  charge.charge,
  charge.notApproved,
  SUM(amount) AS total
FROM
  receipts
  JOIN (
         SELECT
           notApproved.placeId,
           notApproved.notApproved,
           SUM(amount) AS charge
         FROM
           receipts
           JOIN (
                  SELECT
                    placeId,
                    COUNT(approved) notApproved
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
                    )
                    AND
                    dead = false
                ) AS notApproved
             ON {$placeId} = receipts.placeId
         WHERE
           vendorId = {$id}
           AND
           receipts.placeId = {$placeId}
           AND (
             YEAR(`date`) = {$year}
             AND
             MONTH(`date`) = {$month}
           )
           AND
           charge = '1'
           AND
		  dead = false
       ) AS charge
    ON {$placeId} = receipts.placeId
WHERE
  vendorId = {$id}
  AND
  receipts.placeId = {$placeId}
  AND (
    YEAR(`date`) = {$year}
    AND
    MONTH(`date`) = {$month}
  )
  AND
	receipts.dead = false;
SQL;
		$query = $conn->db->query($sql);
		$result = $query->result()[0];
		$details = [];
		if ($query->result()) {
			$details['total'] = $result->total;
			$details['charge'] = $result->charge;
			$details['notApproved'] = $result->notApproved;
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
			foreach ($data['newData'] as $row) :
				$row =
					<<<VAL
(STR_TO_DATE('{$row['date']}', '%d/%m/%Y'), 
'{$row['vendorId']}', 
'{$row['serial']}', 
'{$row['amount']}', 
'{$row['charge']}', 
'{$row['approved']}',
"{$row['comment']}",
'{$placeId}'),
VAL;

				$values .= $row;
			endforeach;
			$values = rtrim($values, ', ');

			$sql =
				<<<SQL
INSERT INTO
 	receipts(date, vendorId, serial, amount, charge, approved, comment, placeId)
VALUES {$values};
SQL;
			$conn->db->query($sql);

			$first_id = $conn->db->insert_id();

			$last_id = $first_id + ($count - 1);

			$result['new'] = ['firstId' => $first_id, 'lastId' => $last_id];
		}
		if ($data['editData']) {

			$values = ['date', 'vendorId', 'serial', 'amount', 'charge', 'approved', 'comment'];
			$sqlString = "UPDATE receipts SET";
			foreach ($values as $value) :
				$sqlString .= " {$value} = CASE";
				foreach ($data['editData'] as $row) :
					if ($value == 'date')
						$sqlString .= " WHEN id = '{$row['id']}' THEN STR_TO_DATE('{$row[$value]}', '%d/%m/%Y')";
					else
						$sqlString .= " WHEN id = '{$row['id']}' THEN ".$conn->db->escape($row[$value]);

				endforeach;
				$sqlString .= " ELSE {$value} END,";
			endforeach;
			$sqlString = rtrim($sqlString, ', ');
			$ids = [];
			foreach ($data['editData'] as $entry) :
				$ids[] = "'{$entry['id']}'";
			endforeach;
			$ids = implode(",", $ids);
			$sqlString .= " WHERE id in ({$ids});";

			$conn->db->query($sqlString);

			$result['edit'] = 'ok';
		}

		$result['isApproved'] = self::getUnApproved($data["vendorId"], $data["year"], $data["month"]);

		return $result;
	}

	static function removeReceipt($id) {
		$conn = validConnection();
		$conn->load->database();

		$sql = "UPDATE receipts SET dead = TRUE WHERE id={$id};";
		return $conn->db->query($sql);
	}

	static function getUnApproved($vendorId, $year, $month) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sql =
			<<<VAL
SELECT COUNT(approved) as counts
FROM receipts
WHERE
  approved = '0'
AND
  vendorId = {$vendorId}
AND
	placeId = {$placeId}
AND (
    YEAR(`date`) = {$year}
  AND
    MONTH(`date`) = {$month}
      );
VAL;

		$count = $conn->db->query($sql);
		return $count->result()[0]->counts > 0 ? false : true;

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
		$conn->db->query($sql);

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

	static function getVendorInventory($id, $month, $year) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sql = <<<SQL
SELECT
  id,
  name,
  price,
  weight,
  vendorId,
  entryId,
  itemEntryId,
  amount,
  total AS totalAmount,
  close
FROM products
  LEFT JOIN (
              SELECT
                inventory_entry.id AS entryId,
                itemId,
                amount,
                close,
                total,
                inventory_items.id AS itemEntryId
              FROM inventory_entry
                JOIN inventory_items ON inventory_entry.id = entryId
              WHERE month = {$month} AND year = {$year} AND placeId = {$placeId}
            ) AS entries
    ON entries.itemId = products.id
WHERE vendorId = {$id};
SQL;
		$query = $conn->db->query($sql);

		$results = null;
		if ($query->result()) {

			foreach ($query->result() as $row) {
				$results["products"][$row->id] = [
					'id' => $row->id,
					'name' => $row->name,
					'price' => $row->price,
					'measurement' => $row->weight,
					'vendorId' => $row->vendorId,
					'entryId' => $row->entryId,
					'itemEntryId' => $row->itemEntryId,
					'amount' => isset($row->amount) ? $row->amount : 0
				];
			}
			$results['close'] = $query->result()[0]->close;
			$results['totalAmount'] = $query->result()[0]->totalAmount;
		}
		return $results;
	}

	static function saveRecords($entry, $items) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sql = <<<SQL
SELECT id
FROM inventory_entry
WHERE vendorId = {$entry["vendorId"]} AND month = {$entry["month"]} AND year = {$entry["year"]};
SQL;

		$query = $conn->db->query($sql);

		if ($query->result())
			$entryId = $query->result()[0] -> id;

		if (isset($entryId)){
			$sql = <<<SQL
UPDATE inventory_entry
SET total = {$entry["total"]}, close = {$entry["close"]}
WHERE id = {$entryId};
SQL;

			$query = $conn->db->query($sql);

			if ($query){
				$values = ['amount'];
				$sqlString = "UPDATE inventory_items SET";
				foreach ($values as $value) :
					$sqlString .= " {$value} = CASE";
					foreach ($items as $row) :
						$sqlString .= " WHEN id = '{$row -> itemEntryId}' THEN '{$row -> $value}'";
					endforeach;
					$sqlString .= " ELSE {$value} END,";
				endforeach;
				$sqlString = rtrim($sqlString, ', ');
				$ids = [];
				foreach ($items as $item) :
					$ids[] = "'{$item -> itemEntryId}'";
				endforeach;
				$ids = implode(",", $ids);
				$sqlString .= " WHERE id in ({$ids});";

				return ['saved' => $conn->db->query($sqlString), 'closed' => $entry["close"]];
			}
		}else {

			$sql = <<<SQL
INSERT INTO inventory_entry (vendorId, month, year, placeId, close, total) VALUES
({$entry["vendorId"]}, {$entry["month"]}, {$entry["year"]}, {$placeId}, {$entry["close"]}, {$entry["total"]})
SQL;

			$conn->db->query($sql);
			$entryId = $conn->db->insert_id();

			if (isset($entryId)) {
				$sql = "INSERT INTO inventory_items (itemId, amount, price, entryId) VALUES ";
				foreach ($items as $item) :
					$amount = !isset($item->amount) ? 0 : $item->amount;
					$sql .= "({$item->id}, {$amount}, {$item->price}, {$entryId}),";
				endforeach;
				$sql = rtrim($sql, ', ');
				return ['saved' => $conn->db->query($sql), 'closed' => $entry["close"]];
			}
		}
		return null;
	}

	static function getEntries() {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sql = <<<SQL
SELECT inventory_entry.id AS id, month, year, total, close, company_name AS vendorName
FROM
  inventory_entry
  JOIN vendors
  ON vendorId = vendors.id
WHERE
	placeId = {$placeId}
ORDER BY year DESC, month;
SQL;

		$query = $conn->db->query($sql);

		$results = null;
		if ($query->result()) {
			$entries = null;
			foreach ($query->result() as $row) {
				$entries[$row->year][$row->month][$row->vendorName] = [
					'id' => $row->id,
					'vendorName' => $row->vendorName,
					'month' => $row->month,
					'year' => $row->year,
					'total' => $row->total,
					'close' => $row->close
				];
			}
			$results['records'] = $entries;
		}

		return $results;
	}

	static function getEntryItems($entryId, $file = false){
		$conn = validConnection();
		$conn->load->database();

		$sqlFields = !$file ?
			"name, inventory_items.price, weight, amount" :
			"name AS 'מוצר',
			  inventory_items.price AS 'מחיר',
			   weight AS 'קילו/יחידה',
			    amount AS 'כמות'";

		$sql = <<<SQL
SELECT
  {$sqlFields}
FROM inventory_items
  JOIN products ON products.id = itemId
WHERE entryId = {$entryId}
ORDER BY `name`;
SQL;

		$query = $conn->db->query($sql);

		if ($file)
			return $query;

		$results = null;
		if ($query->result()) {
			$entries = null;
			foreach ($query->result() as $row) {
				$entries[$row->year][$row->month][$row->vendorName] = [
					'name' => $row->name,
					'price' => $row->price,
					'month' => $row->month,
					'measurement' => $row->weight,
					'amount' => $row->amount
				];
			}
			$results['records'] = $entries;
		}

		return $results;
	}

	static function getEntryById($entryId, $file = false){
		$conn = validConnection();
		$conn->load->database();

		$sql = <<<SQL
SELECT
  heb_name AS place, company_name AS vendor, month, year, total
FROM inventory_entry
JOIN places ON places.id = placeId
  JOIN vendors ON vendors.id = vendorId
WHERE inventory_entry.id = {$entryId};
SQL;

		$query = $conn->db->query($sql);

		$entry = null;
		if ($query->result()) {
			foreach ($query->result() as $row) {
				$entry = [
					'place' => $row->place,
					'vendor' => $row->vendor,
					'month' => $row->month,
					'year' => $row->year,
					'total' => $row->total
				];
			}
		}

		return $entry;
	}

	static function getEntriesByDate($month, $year, $file = false) {
		$conn = validConnection();
		$conn->load->database();
		$placeId = getLocation();

		$sqlFields = !$file ?
			"name, inventory_items.price, weight, amount, company_name" :
			"name                  AS 'מוצר',
			 inventory_items.price AS 'מחיר',
  			 weight                AS 'קילו/יחידה',
  			 amount                AS 'כמות',
  			 company_name";

		$sql = <<<SQL
SELECT
  name, inventory_items.price, weight, amount, company_name
FROM inventory_entry
  JOIN inventory_items ON inventory_entry.id = entryId
  JOIN products ON products.id = itemId
  JOIN vendors ON vendors.id = inventory_entry.vendorId
WHERE year = {$year} AND month = {$month} AND placeId = {$placeId};
SQL;

		$query = $conn->db->query($sql);

		$results = null;
		if ($query->result()) {
			foreach ($query->result() as $row) {
				$results[$row->company_name][] = [
					'name' => $row->name,
					'price' => $row->price,
					'measurement' => $row->weight,
					'amount' => $row->amount
				];
			}
		}
		return $results;
	}
}