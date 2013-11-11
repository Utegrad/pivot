<?php
class App {
	function __construct(){
		$this->APP_URL = $this->_getAppURL();
	}
	
	/**
	 * Sets the path from $_SERVER['HTTP_HOST'] to where the application files are
	 * @var string
	 */
	protected $APP_DIR = 'Pivot';
	
	public $APP_URL;
	public $StyleSheets = array();
	public $JsFiles = array();
	public $Imgs = array();
	const APP_GUID  = "90DB71B5-C9B9-40E2-9F2E-E1B978C8B30F";
	
	public static function GetAppGuid(){
		return self::APP_GUID;
	}
	
	public static function GetAppURL(){
		$instance = new self();
		return $instance->_getAppURL();
	}
	protected function _getAppURL(){
		return 'http://'. $_SERVER['HTTP_HOST'] .'/'. $this->APP_DIR .'/';
	}
}

class Api {
	function __construct() {
		// $this->_setAccessToken();
	}
	
	public $AccessToken;
	public $Errors = array();
	
	/**
	 * gets, sets, and returns $this-AccessToken with value from $_GET or database for use with CBS Sports API
	 *   
	 * @param string $token
	 * @return string|boolean $this->AccessToken or FALSE
	 */
	public static function TouchToken($token = NULL){
		$instance = new self();
		if($instance->_setAccessToken($token)){
			return $instance->AccessToken;
		}
		else{
			return FALSE;
		}
	}
	
	/**
	 * sets $this->AccessToken and access_token database value with data as provided from $token
	 * 
	 * Pass $token to method for updating database value for when not accessing the application via
	 * the CBS Sports fanatsy page where $_GET would contain access token and it might be expired.
	 * 
	 * @param string $token optional to set AccessToken in the database and $this->AccessToken when known
	 * @return boolean
	 */
	protected  function _setAccessToken($token = NULL){
		if(isset($_GET['access_token'])){
			$this->AccessToken = $_GET['access_token'];
			if($this->_dbUpdateAccessToken($this->AccessToken)){
				return TRUE;
			}
			else{
				array_push($this->Errors, "Error updating Access Token in the database.");
				return FALSE;
			}
		}
		else{
			if($token !== NULL){ // token given
				if(strlen($token) != 128){
					// we weren't given a valid string for the access token
					array_push($this->Errors, "Invalid string passed for token");
					return FALSE;
				}
				else{ // have a $token of the correct length
					// update it in the database
					if($this->_dbUpdateAccessToken($token)){ // set $this->AccessToken to $token given 
						$this->AccessToken = $token;
						return TRUE; 
					}
					else{
						array_push($this->Errors, "Error updating Access Token in database from token: $token");
						return FALSE;
					}
				}
			}
			else{ // no $token given 
				// get token from the database
				$selectAccessToken = "SELECT access_token FROM app_config WHERE id = '". App::GetAppGuid() ."'";
				$db = new Database();
				require_once 'Utility.php';
				$accessTokenArray = Data::SelectFetchAll($selectAccessToken, $db->conn, TRUE);
				if (empty($accessTokenArray) || $accessTokenArray === FALSE) {
					array_push($this->Errors, "No token given and unable to find one in the database");
					$db->conn->close();
					return FALSE;
				}
				else{  // set $this->AccessToken to value from database
					$this->AccessToken = $accessTokenArray[0]['access_token'];
					$db->conn->close();
					return TRUE;
				}
			}
		}
	} // end protected  function _setAccessToken()
	
	/**
	 * updates or inserts guid + token pair in database app_config table 
	 * 
	 * @param string $token Api access token
	 * @param Data::conn $dbConn mysqli database connection resource 
	 * @return boolean
	 */
	protected function _dbUpdateAccessToken($token, &$dbConn = NULL){
		require_once 'Utility.php';
		$appGUID = App::GetAppGuid();
		$updateTokenQuery = "UPDATE app_config SET access_token = ? WHERE id = '". $appGUID ."'";
		$insertNewToken = "INSERT INTO app_config VALUES (? , ? , NULL)";
		$closeDbRes = FALSE;
		
		// create a database connection if we don't have one passed
		if ($dbConn === NULL) {
			$db = new Database();
			if($db->conn){
				$conn = $db->conn;
				$closeDbRes = TRUE;
			}
			else{
				array_push($this->Errors, 'Error estabilish database connection: ('. $db->conn->connect_errno .") ". $db->conn->error );
				return FALSE;
			}
		}
		else{
			$conn = $dbConn->conn;
		}
		
		
		if (!(strlen($token) != 128)) { // didn't get a token of the right length
			array_push($this->Errors, 'Invalid $token passed to _dbUpdateAccessToken()');
			//$conn->close();
			if($closeDbRes == TRUE){ $db->conn->close(); }
			return FALSE;
		}
		else{ // given correct length string
			// is there an existing guid + token pair in the database?
			if(!($result = $conn->query("SELECT COUNT(access_token) AS num FROM app_config"))){
				array_push($this->Errors, "Query failed: (". $conn->errno .") ". $conn->error);
				if($closeDbRes == TRUE){ $db->conn->close(); }
				return FALSE;
			}
			else{
				$count = $result->fetch_assoc();
				$count = $count['num'];
			}
			// if guid + token pair in database, update token
			if($count >= 0){
				$tokenData = Data::WithValues($conn, $updateTokenQuery, 's', array($token) );
				$tokenData->bindParameters();
				if(!($tokenData->stmt->execute())){
					array_push($this->Errors, "Update failed: (".$tokenData->stmt->errno.") ".$tokenData->stmt->error );
					$tokenData->stmt->close();
					if($closeDbRes == TRUE){ $db->conn->close(); }
					return FALSE;
				}
				else{
					$tokenData->stmt->close();
					if($closeDbRes == TRUE){ $db->conn->close(); }
					return TRUE;
				}
			}
			else{ // if no guid + token pair, insert pair
				$newGuid = Helpers::getGUID();
				$newPairData = Data::WithValues($conn, $insertNewToken, 'ss', array($newGuid, $token));
				$newPairData->bindParameters();
				if(!($newPairData->stmt->execute())){
					array_push($this->Errors, "Insert failed: (".$newPairData->stmt->errno.") ".$newPairData->stmt->error );
					$newPairData->stmt->close();
					if($closeDbRes == TRUE){ $db->conn->close(); }
					return FALSE;
				}
				else{
					$tokenData->stmt->close();
					if($closeDbRes == TRUE){ $db->conn->close(); }
					return TRUE;
				}
			}
		}
	} // end protected function _dbUpdateAccessToken()
	
} // end class Api

class Database {
	function __construct(){
		if(!($this->conn = new mysqli($this->connectInfo['host'],
				$this->connectInfo['username'], $this->connectInfo['password'],
				$this->connectInfo['database'])
				)
			)
		{
			array_push($this->errorMsgs, "Failed to connect to MySQL: (". $this->conn->connect_errno .") ". $this->conn->connect_error);
		}
	}
	private $connectInfo = array( 
		'host' => 'localhost',
		'username' => 'FFApplication',
		'password' => 'keep0ut!',
		'database' => 'ff_stats'
	);
	public $errorMsgs = array();
	public $conn;	
	
}


?>