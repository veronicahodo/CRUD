<?php

/*

vcrud.php

Version 1.3.0 - 2024/08/06

I created this (with the help of OpenAI) to use with all my various API
projects I work on. This handles basic CRUD operations against the
database. This is surprisingly flexible after adding the AND/OR options
to the read command.

functions:
    create(table,fields): takes an associative array and tries to insert
        it into the database under the provided table. Returns false if
        it fails, and the last inserted ID if successful.

    read(table,conditions,[orOperand],[orderBy]): Pulls records from the database 
        that match "conditions". Conditions are a three-part array 
        containing a field name, a string operand (=,<,>,LIKE,etc.) and 
        a value. Default is to AND the conditions, but setting orOperand
        to true will OR the conditions. Setting orderBy passes the value
		at the end of the SQL statement

    update(table,fields,conditions,[orOperand]): Updates records from the database
        matching conditions with the values from the associated array
        fields.

    delete(table,conditions,[orOperand]): Removes records from the database that meet
        conditions.

*/

class Vcrud
{
	public PDO $connection;    // Stores the PDO connection so we don't have to pass it every time
	private $maxRows = 20000;

	public function __construct($dbUser, $dbPass, $dbHost, $dbName)
	{
		// Just calls the connect function. Did it this way in case I ever wanted to change
		// databases
		$this->connect($dbUser, $dbPass, $dbHost, $dbName);
	}

	private function connect($dbUser, $dbPass, $dbHost, $dbName)
	{
		// connects to a mysql/mariadb database
		$dsn = "mysql:host={$dbHost};dbname={$dbName}";
		$this->connection  = new PDO($dsn, $dbUser, $dbPass);
	}

	private function conditionsToStrings($conditions)
	{
		// turns the datasets [column, operand, value] into the SQL string formatted
		// markup
		$working = [];
		foreach ($conditions as $condition) {
			[$column, $operator, $value] = $condition;
			$workingStr = "{$column} {$operator} ";
			// if it's a LIKE operand we have to add the % to either side
			if (strtolower($operator) === 'like') {
				$workingStr .= "\"%{$value}%\"";
			} else {
				$workingStr .= "\"{$value}\"";
			}
			$working[] = $workingStr;
		}
		return $working;
	}

	public function create($table, $fields)
	{
		// inserts a single row into the database. Perhaps in the future it will
		// support multi row but for now until I need it, this is fine
		$columns = array_keys($fields);
		$placeholders = ':' . implode(',:', $columns);
		$sql = "INSERT INTO `{$table}` (" . implode(',', $columns) . ") VALUES ({$placeholders})";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute($fields);
		return $this->connection->lastInsertId();
	}

	public function read($table, $conditions, $orOperand = false, $orderBy = null)
	{
		// reads up to 20000 rows and returns them based on conditions. 
		// Conditions are formatted [column, operand, value]
		$strConditions = $this->conditionsToStrings($conditions);
		$logicalOperator = $orOperand ? ' OR ' : ' AND ';
		$sql = "SELECT * FROM `{$table}` WHERE (" . implode($logicalOperator, $strConditions) . ")";
		if ($orderBy) {
			$sql .= " ORDER BY {$orderBy}";
		}
		$sql .= " LIMIT " . $this->maxRows;
		$stmt = $this->connection->prepare($sql);
		$stmt->execute();
		$return = [];
		while ($line = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$return[] = $line;
		}
		return $return;
	}

	public function update($table, $fields, $conditions, $orOperand = false)
	{
		// updates up to 20000 rows with the data from fields.
		// Conditions are formatted based on [column, operand, value]
		$strConditions = $this->conditionsToStrings($conditions);
		$frames = [];
		foreach (array_keys($fields) as $column) {
			$frames[] = "{$column}=:{$column}";
		}
		$logicalOperator = $orOperand ? ' OR ' : ' AND ';
		$sql = "UPDATE `{$table}` SET " . implode(',', $frames) . " WHERE (" . implode($logicalOperator, $strConditions) . ") LIMIT " . $this->maxRows;
		$stmt = $this->connection->prepare($sql);
		$stmt->execute($fields);
	}

	public function delete($table, $conditions, $orOperand = false)
	{
		// deletes up to 20000 rows that meet the conditions
		// Conditions are formatted based on [column, operand, value]
		$strConditions = $this->conditionsToStrings($conditions);
		$logicalOperator = $orOperand ? ' OR ' : ' AND ';
		$sql = "DELETE FROM `{$table}` WHERE (" . implode($logicalOperator, $strConditions) . ") LIMIT " . $this->maxRows;
		$stmt = $this->connection->prepare($sql);
		$stmt->execute();
	}

	public function get($table, $conditions, $orOperand = false)
	{
		// Wrapper for the read function, aligned with the HTTP GET method
		return $this->read($table, $conditions, $orOperand);
	}

	public function post($table, $fields)
	{
		// Wrapper for the create function, aligned with the HTTP POST method
		return $this->create($table, $fields);
	}

	public function put($table, $fields, $conditions, $orOperand = false)
	{
		// Wrapper for the update function, aligned with the HTTP PUT method
		return $this->update($table, $fields, $conditions, $orOperand);
	}

	public function close()
	{
		// as for good cleanup, this should be called before exiting
		$this->connection = null;
	}
}
