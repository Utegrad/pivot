<?php
class Api {
	function __construct() {
		
	}
	public $AccessToken = "U2FsdGVkX1_jlDVhfEgnqpmyAqLFHt6Wy-vOW8Oa1EFUKUbjD9Unp_TSceEtN9NX8X62qDXrVaJgwXjVOUZ_5Bim1lHN9aI0vrzH4Cs_pg0MScP5iJXvdJ2vLprhAwH0";
	
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