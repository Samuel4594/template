<?php 
require_once('config.php');

//connect to mysql database
@$cme = new mysqli(_CME_HOSTNAME, _CME_USERNAME, _CME_PASSWORD, _CME_DATABASE);

if(mysqli_connect_errno()) {
	echo "Error connecting DB, please try again later HERE.";
	exit;
}

$cme->select_db(_CME_DATABASE);

/* 
can use s for string or i for integer in the parser

example select statement
------------------------- 
$this->execute_sql("select", array("users", "test"), "FOEBIS_USERS", "user = ?, array("i" => "1"));

example update statement
-------------------------
$this->execute_sql("update", array("users" => "test", "users1" => "test1"), "FOEBIS_USERS", "wibble = ? and wibble2 = ?", "array("s" => "wibble1", "i" => 5)));

example insert statement
-------------------------
$this->execute_sql("insert", array("users" => "test", "users1" => "test1"), "FOEBIS_USERS", "", array());
*/

/* notes -- if there are more than one i or s value for a parseArray you need to add them as i1, i2 etc, the code below substitutes the values */
		
class mysqlwrapper_class {
	
	function __construct() {
		//$this->getTableStructures();
	} 
	
	function __destruct() {
		global $cme;
		
		$cme->close();
	}
	
	public function lookup($value, $db_field) {
		$result = $this->execute_sql("select", array("l_field_text"), "lookups", "l_field_id = '$value' and l_field = '$db_field'", array(""));
		return $result[0]["l_field_text"];
	}
	
	private function get_table_structure($table) {
		// called in the construct to save time and database calls. It builds a snapshot of the table in an array
		global $cme;
		$tableArray = array();
		
		$sql = "SHOW FIELDS FROM " . $table;
		$result = $cme->query($sql);
		if($result->num_rows > 0){
			$i=0;
			while($row = $result->fetch_assoc()){ 
			   $tableArray[$row['Field']] = $this->get_field_type($row['Field'], $table);
			   //array_push($tableArray, $this->get_field_type($row['Field'], $table));
			}
		}
		
		//print_r($this->tableArray);
		unset($result);
		unset($row);
		
		if(!empty($table)) {
			return $tableArray;
		}
		else {
			return false;
		}
	}
	
	private function get_field_type($field, $table) {
		// called  by the get_table_structure function above to get the field type so the import knows whether or not to add quotes.
		global $cme;
		
		$sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . $table . "' AND COLUMN_NAME = '" . $field . "'";
		$result = $cme->query($sql);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){ 
			   return $row['DATA_TYPE'];		  
			}
			unset($row);
		}
		unset($result);
		
		return true;
	}
	
	public function execute_sql($dbCallType, $selectArray, $dbCallTable, $whereStatement, $parseString) {
		global $cme;
		//$tableFieldArray = $this->returnTableStructure($dbCallTable);
		//$tableFieldArray = $this->get_table_structure($dbCallTable);
				
		$arrayCount = count($selectArray);
		
		/* build up query */
		$query = $dbCallType . " ";
		
		if($dbCallType == "select") {
			if(!empty($selectArray)) {
				$i = 0;
				/* add select {value}, {value1} etc */
				foreach($selectArray as $value){
					$query .= $value;
					if($i < ($arrayCount - 1)) {
						$query .= ", ";
					}
					$i++;
				}
			}
			else {
				$query .= "*";		
			}
			$query .= " from " . $dbCallTable;
		}
		elseif($dbCallType == "update") {
			$tableFieldArray = $this->get_table_structure($dbCallTable);
			$query .= $dbCallTable . " set ";
			//echo $query;
			if(!empty($selectArray)) {
				$i = 0;
				/* build update fields */
				foreach($selectArray as $key => $value){
					$query .= $key . " = " ;
					
					if(($tableFieldArray[$key] == "int") || ($tableFieldArray[$key] == "decimal") || ($tableFieldArray[$key] == "float") || ($tableFieldArray[$key] == "tinyint")) {
						if(!empty($value)) {
							if(is_numeric($value)) {
								$query .= mysqli_real_escape_string($cme, $value);
							}
							else {
								$query .= 0;
								$this->writeErrorLog("User attempted to update something other than a number into field: " . $key, $query);
							}
						}
						else {
							$query .= 0;	
						}
					}
					elseif(($tableFieldArray[$key] == "varchar") || ($tableFieldArray[$key] == "enum") || ($tableFieldArray[$key] == "time") || ($tableFieldArray[$key] == "timestamp") || ($tableFieldArray[$key] == "text") || ($tableFieldArray[$key] == "char") || ($tableFieldArray[$key] == "mediumtext")) {
						if((!empty($value)) || (($value == "0"))) {
							$query .= "'" . mysqli_real_escape_string($cme, $value) . "'";
						}
						else {
							$query .= "(null)";	
						}
					}
					elseif($tableFieldArray[$key] == "date") {
						if(!empty($value)) {
							$query .= "'" . mysqli_real_escape_string($cme, date("Y-m-d", strtotime($value))) . "'";
						}
						else {
							$query .= "(null)";	
						}
					}
					elseif($tableFieldArray[$key] == "datetime") {
						if(!empty($value)) {
							$query .= "'" . mysqli_real_escape_string($cme, date("Y-m-d H:i:s", strtotime($value))) . "'";
						}
						else {
							$query .= "(null)";	
						}
					}					
					
					 /* add trailing commas if it's not the last field in the array */ 
					if($i < ($arrayCount - 1)) {
						$query .= ", ";
					}
					$i++;
				}
			}
		}
		elseif($dbCallType == "insert") {
			$tableFieldArray = $this->get_table_structure($dbCallTable);
			$query .= "into " . $dbCallTable . " ";
			//echo $query;
			if(!empty($selectArray)) {
				$i = 0;
				/* build insert query */
				foreach($selectArray as $key => $value){
					$intoField .= $key;
					if(($tableFieldArray[$key] == "int") || ($tableFieldArray[$key] == "decimal") || ($tableFieldArray[$key] == "float") || ($tableFieldArray[$key] == "tinyint")) {
						if(!empty($value)) {
							if(is_numeric($value)) {
								$valueField .= mysqli_real_escape_string($cme, trim($value));
							}
							else {
								$valueField .= 0;
								$this->writeErrorLog("User attempted to insert something other than a number into field: " . $key, $query);
							}
						}
						else {
							$valueField .= 0;	
						}
					}
					elseif(($tableFieldArray[$key] == "varchar") || ($tableFieldArray[$key] == "enum") || ($tableFieldArray[$key] == "time") || ($tableFieldArray[$key] == "timestamp") || ($tableFieldArray[$key] == "text") || ($tableFieldArray[$key] == "char") || ($tableFieldArray[$key] == "mediumtext")) {
						if((!empty($value)) || ($value == "0")) {
							$valueField .= "'" . mysqli_real_escape_string($cme, trim($value)) . "'";
						}
						else {
							$valueField .= "(null)";	
						}
					}
					elseif($tableFieldArray[$key] == "date") {
						if(!empty($value)) {
							$valueField .= "'" . mysqli_real_escape_string($cme, date("Y-m-d", strtotime($value))) . "'";
						}
						else {
							$valueField .= "(null)";	
						}
					}
					elseif($tableFieldArray[$key] == "datetime") {
						if(!empty($value)) {
							$valueField .= "'" . mysqli_real_escape_string($cme, date("Y-m-d H:i:s", strtotime($value))) . "'";
						}
						else {
							$valueField .= "(null)";	
						}
					}
					
					/* add trailing commas if it's not the last field in the array */
					if($i < ($arrayCount - 1)) {
						$intoField .= ", ";
						$valueField .= ", ";
					}
					$i++;
				}
			}
			$query .= "(" . $intoField . ") values (" . $valueField . ")";
		}
		
		//echo $query;
				
		/* add where statement onto the query */
		if(!empty($whereStatement)) {
			$query .= " where " . $whereStatement;
		}
		
		$statement = $cme->stmt_init();
		$statement->prepare($query);
		
		$a_params = array();
		if(!empty($parseString)) {
			/* bind parameters for markers */
			foreach ($parseString as $key => $value) {
				$parseType .= rtrim($key, '0123456789');
				/* execute query */
			}
			$a_params[] = & $parseType;
			foreach ($parseString as $key => $value) {
				$a_params[] = & $parseString[$key];
				/* execute query */
			}
			//$statement->bind_param($parseType, $value);
			call_user_func_array(array($statement, 'bind_param'), $a_params);
		}
	
		//echo $query;
		//echo $dbCallType;
				
		if($dbCallType == "select") {
			/*run query and return the result to the calling page */
			//echo $query;
			//$result = $foebis->query($query);

			if($statement->execute()) {
				$result = $statement->get_result();
				$array = array();
				while($row = $result->fetch_assoc()){
					$array[] = $row;
				}
				return $array;
				}
			else {
				if($cme->error) {
					$this->writeErrorLog($cme->error, $query);
					//return json_encode(array('success'=>'false'));
				}
			}
		}
		elseif($dbCallType == "update" || $dbCallType == "insert") {
			/* run query and return the result to the calling page, upon error write to error log */
			//echo "</br></br>" . $query . "<br />";
			//$result = $cme->query($query);
			if($statement->execute()) {
				$statement->get_result();
				if($dbCallType == "insert") {
					return $cme->insert_id;
				}
				else {
					return true;	
				}
			}
			else {
				if($cme->error) {
					$this->writeErrorLog($cme->error, $query);
					return false;
					//return json_encode(array('success'=>'false'));
				}	
			}
		}
		return false;			
	}
	
	public function execute_query($sql) {
		global $cme;
		
		$result = $cme->query($sql);
		if($result) {
			return "Record has been saved";
		}
		else {
			/* WRITE TO THE ERROR LOG on error*/
			$this->writeErrorLog($cme->error, $sql);
			return "There has been a problem saving the record";
		}
	}
	
	public function saveClaimChanges($table, $pk, $fields, $pkValue) {
		global $conn;
		$tableFieldArray = $this->get_table_structure($table);
		
		$currentData = $this->execute_sql("select", array('*'), $table, $pk . " = ?", array("i" => $pkValue));
		
		foreach($fields as $column => $value) {
			if($tableFieldArray[$column] == "date") {
				$value = date("Y-m-d" , strtotime($value));
			}
			elseif($tableFieldArray[$column] == "datetime") {
				$value = date("Y-m-d H:i:s" , strtotime($value));
			}
				
			if($currentData[0][$column] <> $value) {
				$conn->execute_sql("insert", array("cc_u_id" => $_COOKIE['username'], "cc_db_field" => $table . "." . $column, "cc_changed_from" => $currentData[0][$column], "cc_changed_to" => $value, "cc_changed_date" => date("Y-m-d H:i:s"), 'cc_c_id' => $_SESSION['claimId']), "claims_changes", "", "");
			}
		}		
	}
	
	private function escape_string($value) {
		//$value = "\'" . $value . "\'";
		$value = "'" . $value . "'";
		return $value;
	}
	
	private function writeErrorLog($err, $data) {
		$this->execute_sql("insert", array("e_user" => $_COOKIE['username'], "e_timestamp" => date("d-m-Y H:i:s"), "e_error" => $err, "e_query" => $data, "e_p_id" => $_SESSION['policyId'], "e_c_id" => $_SESSION['claimId'], "e_ip" => $_SERVER['REMOTE_ADDR'], "e_user_agent" => $_SERVER['HTTP_USER_AGENT']), "error_log", "", array(""));
		
		/* WRITE TO THE ERROR LOG - JS9 
		$file = './logs/errors-' . date('Y-m-d') . '.txt';

		$output = "Timestamp: " . date("d-m-Y H:i:s") . " User: " . $_COOKIE['username'] .  "\n";
		$output .= "Error: " . $err . "\n";
		$output .= "Query Run: " . $data . "\n\n";
		$output .= "------------------------------------------------------------------------------------------\n\n";
		
		file_put_contents($file, $output, FILE_APPEND | LOCK_EX);
		
		END WRITING TO ERROR LOG */
	}
	
}
?>