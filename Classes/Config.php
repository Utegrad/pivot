<?php
class Api {
	function __construct() {
		
	}
	public $AccessToken = "U2FsdGVkX1-DeuK2FOdb6a4Jxz-SvI-rewk97Cfe-QlMYwPYf1BVaWNM5kS3zrlPn8A1PHL_uiI3memWF8ySZeGrDA22pLzNj-9d1WEUhu5_oW90muN5jfwOOWw_E34q";
	
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