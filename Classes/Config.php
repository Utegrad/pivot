<?php
class Api {
	function __construct() {
		
	}
	public $AccessToken = "U2FsdGVkX19R40K6VAFyivKgTW7mxd_3_YpmGD-EVGarkUAsyZtjgUenkW_gKSefG7aoP7Z4iA_r1dgGVDYG7zpnwFFY2LuxceBA6tjXe7RN4rwWbQCQCsgxmxUnjiyi";
	
}
class Database {
	function __construct(){
		if(!($this->conn = new mysqli($this->connectInfo['host'],
			$this->connectInfo['username'], $this->connectInfo['password'],
			$this->connectInfo['database']) )){
				echo "Failed to connect to MySQL: (". $this->conn->connect_errno .") ". $this->conn->connect_error;
			}
	}
	private $connectInfo = array( 'host' => 'localhost',
		'username' => 'FFApplication',
		'password' => 'keep0ut!',
		'database' => 'ff_stats');
	public $conn;	
	
}


?>