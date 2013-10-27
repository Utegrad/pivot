<?php
class Api {
	function __construct() {
		
	}
	public $AccessToken = "U2FsdGVkX1-zftgl6_-NyiYI-F5Ka9gzD7b_zRzoJ6UuO8WlZrOk86PauVltUelTExrCUN5Ae7HJwOfsaMsAd7OzNrxFih5f9-_qguzOu_TUPfeqKGPV62LgLCcoCC7O";
	
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