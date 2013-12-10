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
	function __construct($token = NULL) {
		$this->_setAccessToken($token);
	}
	
	public $AccessToken;
	public $Errors = array();
	public $url = "https://api.cbssports.com/general/oauth/generate_token";
	public $app_id = "19979";
	public $app_secret = "884437ba66c22ebc2fd7e5070ea4448d72db72c76bdf34d913";
	public $user_id ="af25290de1c2e84bb65f189c4fb73451";
	public $league_id = "11540-h2h";
	public $sport = "football";
	public $response_format = "JSON";
	
	/**
	 * gets, sets, and returns $this-AccessToken with value from $_GET or database for use with CBS Sports API
	 *   
	 * @param string $token
	 * @return string|boolean $this->AccessToken or FALSE
	 */
	public static function TouchToken($token = NULL){
		$instance = new self($token);
		if(!(empty($instance->AccessToken))){
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
			$this->user_id = $_GET['user_id'];
			$this->league_id = $_GET['league_id'];
		}
		else{
			// get an after hours access token
			$data = array(
					'app_id' => $this->app_id,
					'app_secret' => $this->app_secret,
					'user_id' => $this->user_id,
					'league_id' => $this->league_id,
					'sport' => $this->sport,
					'response_format' => $this->response_format,
			);
			
			// use key 'http' even if you send the request to https://...
			$options = array(
					'http' => array(
							'header'  => "Content-type: application/x-www-form-urlencoded",
							'method'  => 'POST',
							'content' => http_build_query($data),
					),
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($this->url, false, $context);
			$result = json_decode($result);
			$this->AccessToken = $result->body->access_token;
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
		global $LOG;
		$updateTokenQuery = "UPDATE app_config SET access_token = ? WHERE id = '". $appGUID ."'";
		$insertNewToken = "INSERT INTO app_config VALUES (? , ? , NULL)";
		$closeDbRes = FALSE;
		
		// create a database connection if we don't have one passed
		if ($dbConn === NULL) {
			$LOG->logInfo('creating new database object');
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
		
		
		if ((strlen($token) != 128)) { // didn't get a token of the right length
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
			if($count > 0){
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
				// check for App::APP_GUID
				$newGuid = App::APP_GUID;
				$newPairData = Data::WithValues($conn, $insertNewToken, 'ss', array($newGuid, $token));
				$newPairData->bindParameters();
				if(!($newPairData->stmt->execute())){
					array_push($this->Errors, "Insert failed: (".$newPairData->stmt->errno.") ".$newPairData->stmt->error );
					$newPairData->stmt->close();
					if($closeDbRes == TRUE){ $db->conn->close(); }
					return FALSE;
				}
				else{
					$newPairData->stmt->close();
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
	function __destruct(){
		global $LOG;
		if(isset($LOG)){
			foreach ($this->errorMsgs as $msg){
				$LOG->logDebug($msg);
			}
		}
		if(isset($this->conn)){
			$this->conn->close();
		}
	}
	private $connectInfo = array( 
		'host' => 'localhost',
		'username' => 'FFApplication',
		'password' => 'keep0ut!',
		'database' => 'dev_ff_stats'
	);
	public $errorMsgs = array();
	public $conn;	
	
}


?>