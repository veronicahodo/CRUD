<?php 

// crud.php 

// Version 0.0.something

// This is supposed to be the basic CRUD unit
// I use for development. It's only the hundreth 
// time I've built some shit like this but 
// even this was based off some code I got out of chatGPT

class CRUD {
	// We hang on to our SQL connection, so we do don't have to 
	// do as I did in the past and pass the db connection. I'm
	// sure there is an arguement for the latter but I'm lazy
	private $connection;

	
	private function connect($dbUser,$dbPass,$dbHost,$dbName) {
		// This function does all our connection work.
		// Expected: 
		//		$dbUser = username of the mysql/mariadb user
		//		$dbPass = password of the mysql/mariadb user
		//		$dbHost = FQDN for the mysql/mariadb host
		//		$dbName = name of the database on the server
		//
		// Returns:
		//		Nothing.
		$dsn = "mysql:host=".$dbHost.";dbname=".$dbName;
		// needs to be in a try [TODO]
		$this->connection = new PDO($dsn,$dbUser,$dbPass);
	}
	
	private function conditionsToStrings($conditions) {
		$working = [];
		foreach ($conditions as $condition) {
			$workingStr = $condition[0]." ".$condition[1]." ";
			if (strtolower($condition[1]) === 'like') {
				$workingStr .= "%".$condition[2]."%";
			} else {
				$workingStr .= $condition[2];
			}
			$working[] = $workingStr;
		
		}
		return $working;
	}
	
	function columnArray($dataset) {
		$columns = [];
		foreach($dataset as $column => $value) {
			$columns[] = $column;
		}
		return $columns;
	}

	function __construct($dbUser,$dbPass,$dbHost,$dbName) {
		$this->connect($dbUser,$dbPass,$dbHost,$dbName);
	
	}
	
	function create($table,$fields) {
		$columns = $this->columnArray($fields);
		$sql = "INSERT INTO `".$table."` (".implode(',',$columns).") VALUES (:".implode(',:',$columns).")";
		$stmt = $this->connection->prepare($sql).execute($fields);
		return $this->connection->lastInsertId();
	}
	
	function read($table,$conditions) {
		$strConditions = $this->conditionsToStrings($condtions);
		$sql = "SELECT * FROM `".$table."` WHERE (".implode(',',$strConditions).") LIMIT 20000";
		$stmt = $this->connection->prepare($sql)->execute();
		
		// this feels so redundant
		$return = [];
		while ($line = $stmt->fetchAssoc()) {
			$return[] = $line;	
		}
		
		return $return;
	}
	
	function update($table,$fields,$conditions) {
		$strConditions = $this->conditionsToStrings($conditions);
		$columns = $this->columnArray($fields);
		$frames = [];
		foreach ($columns as $column) {
			$frames[] = $column . "=:" . $column;
		}
		$sql = "UPDATE `".$table."` SET (".implode(',',$frames).") WHERE (".implode(',',$strConditions).") LIMIT 20000";
		
		$stmt = $this->connection->prepare($sql)->$execute($fields);
		
		
	}
	
	function delete($table,$conditions) {
		$strConditions = $this->conditionsToStrings($conditions);
		$sql = "DELETE FROM `".$table."` WHERE (".implode(',',$strConditions).") LIMIT 20000";
		
		$stmt = $this->connection->prepare($sql)->execute();
	}


	function close() {
		$this->connection->close();
	}
}
