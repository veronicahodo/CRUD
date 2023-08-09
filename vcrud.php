<?php

class CRUD
{
	private $connection;

	public function __construct($dbUser, $dbPass, $dbHost, $dbName)
	{
		$this->connect($dbUser, $dbPass, $dbHost, $dbName);
	}

	private function connect($dbUser, $dbPass, $dbHost, $dbName)
	{
		$dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName;
		$this->connection  = new PDO($dsn, $dbUser, $dbPass);
	}

	private function conditionsToStrings($conditions)
	{
		$working = [];
		foreach ($conditions as $condition) {
			$workingStr = $condition[0] . " " . $condition[1] . " ";
			if (strtolower($condition[1]) === 'like') {
				$workingStr .= "%" . $condition[2] . "%";
			} else {
				$workingStr .=  $condition[2];
			}
			$working[] = $workingStr;
		}
		return $working;
	}

	private function getColumnArray($dataset)
	{
		return array_keys($dataset);
	}

	public function create($table, $fields)
	{
		$columns = $this->getColumnArray($fields);
		$sql = "INSERT INTO `" . $table . "` (" . implode(',', $columns) . ") VALUES (:" . implode(',:', $columns) . ")";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute($fields);
		return $this->connection->lastInsertId();
	}

	public function read($table, $conditions)
	{
		$strConditions = $this->conditionsToStrings($conditions);
		$sql = "SELECT * FROM `" . $table . "` WHERE (" . implode(' AND ', $strConditions) . ") LIMIT 20000";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute();
		$return = [];
		while ($line = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$return[] = $line;
		}
		return $return;
	}

	public function update($table, $fields, $conditions)
	{
		$strConditions = $this->conditionsToStrings($conditions);
		$columns = $this->getColumnArray($fields);
		$frames = [];
		foreach ($columns as $column) {
			$frames[] = $column . "=:" . $column;
		}
		$sql = "UPDATE `" . $table . "` SET " . implode(',', $frames) . " WHERE (" . implode(' AND ', $strConditions) . ") LIMIT 20000";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute($fields);
	}

	public function delete($table, $conditions)
	{
		$strConditions = $this->conditionsToStrings($conditions);
		$sql = "DELETE FROM `" . $table . "` WHERE (" . implode(' AND ', $strConditions) . ") LIMIT 20000";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute();
	}

	public function close()
	{
		$this->connection = null;
	}
}
