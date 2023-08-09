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
		$dsn = "mysql:host={$dbHost};dbname={$dbName}";
		$this->connection  = new PDO($dsn, $dbUser, $dbPass);
	}

	private function conditionsToStrings($conditions)
	{
		$working = [];
		foreach ($conditions as $condition) {
			[$column, $operator, $value] = $condition;
			$workingStr = "{$column} {$operator} ";
			if (strtolower($operator) === 'like') {
				$workingStr .= "%{$value}%";
			} else {
				$workingStr .= $value;
			}
			$working[] = $workingStr;
		}
		return $working;
	}

	public function create($table, $fields)
	{
		$columns = array_keys($fields);
		$placeholders = ':' . implode(',:', $columns);
		$sql = "INSERT INTO `{$table}` (" . implode(',', $columns) . ") VALUES ({$placeholders})";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute($fields);
		return $this->connection->lastInsertId();
	}

	public function read($table, $conditions)
	{
		$strConditions = $this->conditionsToStrings($conditions);
		$sql = "SELECT * FROM `{$table}` WHERE (" . implode(' AND ', $strConditions) . ") LIMIT 20000";
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
		$frames = [];
		foreach (array_keys($fields) as $column) {
			$frames[] = "{$column}=:{$column}";
		}
		$sql = "UPDATE `{$table}` SET " . implode(',', $frames) . " WHERE (" . implode(' AND ', $strConditions) . ") LIMIT 20000";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute($fields);
	}

	public function delete($table, $conditions)
	{
		$strConditions = $this->conditionsToStrings($conditions);
		$sql = "DELETE FROM `{$table}` WHERE (" . implode(' AND ', $strConditions) . ") LIMIT 20000";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute();
	}

	public function close()
	{
		$this->connection = null;
	}
}
