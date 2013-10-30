<?php
require_once('Config.php');

/** Tools for working with the CBS Sports Fantasy API
 * 
 * @author Matthew Larsen <matthew@utegrads.com>
 */
class CBSSports {
    /** construct to assemble GetURL for the data set specified by $entity
     * @param string $entity URL part to specialize the object to get the desired data
     * from the API
     * @param int id optional parameter for when cURL needs an id in the suffix
     * @return bool don't know if this is necessary
     */
	function __construct($entity, $id = NULL){		
		$this->ErrorMessage = array('place holder',);
		$this->api = new Api();
		$this->AccessToken = $this->api->AccessToken;
		
		switch($entity){
			case 'nfl_player':
				break;
			case 'ff_position':
				break;
			case 'ff_owner':
				break;
			case 'ff_team':
				break;
			case 'nfl_team':
				break;
            case 'nfl_player_profile':
                break;
            case 'rosters';
                break;
			default:
				array_push($this->ErrorMessage, 'Undefined entity');
				return FALSE;
				break;
		}
		$this->Element = $this->elements[$entity]['element'];
		if($id !== NULL) {
		    $this->elements[$entity]['suffix'] .= $id;
		}
        $this->URLSuffix = "?version=2.0&access_token=". $this->AccessToken ."&response_format=json"."&".$this->elements[$entity]['suffix'];
		$this->GetURL = $this->BaseURL. $this->Element .$this->URLSuffix;
		return TRUE;
	}
	/** @var array $ErrorMessage errors encoutered by the object for troubleshooting */
	public $ErrorMessage; 
	public $AccessToken;
	public $GetURL;
	private $BaseURL = 'http://api.cbssports.com/fantasy/';
	private $Element; # the part of the URL specific to what you're trying to _GET
	private $URLSuffix; 
	private $timeout = 30;
	private $api;
    
	/** @var array $elements Possible URL elements from API sent to __construct($entity) to build the GET URL
	 * for the desired data from the CBS Sports API
	 */
	private $elements = array(
		'nfl_player' => array ( 'element' => "players/list", 'suffix' => "SPORT=football" ),
		'ff_position' => array ( 'element' => 'positions', 'suffix' => '' ),
		'ff_team' => array( 'element' => 'league/teams', 'suffix' => '' ),
		'ff_owner' => array( 'element' => 'league/owners', 'suffix' => ''),
		'nfl_team' => array( 'element' => 'pro-teams', 'suffix' => ''),
		'nfl_player_profile' => array( 'element' => 'players/profile', 'suffix' => 'player_id='),
		'rosters' => array( 'element' => 'league/rosters', 'suffix' => 'team_id=')
	);
    
    /** Retreives JSON data from CBS Sports API or FALSE if http request error.
     * @return object|bool
     */
	public function GetData(){
		$crl = curl_init();
		$timeout = 30;

		curl_setopt($crl, CURLOPT_URL, $this->GetURL);
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $this->timeout );
		curl_setopt($crl, CURLINFO_HEADER_OUT, TRUE);
		$ret = curl_exec($crl);
		$curlInfo = curl_getinfo($crl);
		if(curl_errno($crl) || $curlInfo['http_code'] >= 400 ){
			$error = "Error from cURL curl_exec() (". curl_errno($crl) .") ". curl_error($crl) ."or http_code >= 400.
			     $ret";
			array_push($this->ErrorMessage, $error);
			curl_close($crl);
			return FALSE;
		}
		curl_close($crl);
		$data = json_decode($ret);
		return $data;
	}
	
} // end class CBSSports

/**
 * Helpers
 * 
 * helper methods for the Pivot application
 * 
 * @package Pivot
 * @author Matthew Larsen <matthew@utegrads.com>
 * @copyright 2013
 * @version $Id$
 * @access public
 */
class Helpers {
	public static function searchIfExists($needle, $haystack ){
		$exists = FALSE;
		foreach($haystack as $value){
			if($value[0] == $needle){
				$exists = TRUE;
			}
		}
		return $exists;
	}
    
	/**
	 * Helpers::matchTstring()
	 * 
     * check that msyqli::stmt type string, parameters and query have matching count
     * of parameters
     * 
	 * @param string $typeString types string for mysqli_stmt::bind_param method
	 * @param array $paramArray array of variables passed to mysqli_stmt::bind_param method
	 * @param string $query query string for mysqli_stmt::prepare method
	 * @return bool
	 */
	public static function matchTstring($typeString, array $paramArray, $query){
	  	$returnValue = FALSE;
		$typeStringArray = str_split($typeString);
		if(count($typeStringArray) !== count($paramArray)){
			#echo "Type String count doesn't match param count.";
			return FALSE;
		}
		if(count($typeStringArray) !== substr_count($query,'?')){
			#echo "Type string count doesn't match num parameters in query.";
			return FALSE;
		}
		foreach($typeStringArray as $key => $value){
	#		echo "<p>Key: $key Type: $value Param: ". $paramArray[$key] ."</p>";
			switch($value){
			case 'i':
				if(!is_int($paramArray[$key]))
				{
#					echo "<p>Int specified and not found</p>";
					return FALSE;
					break;
				}
				else{
#					echo "<p>Int found</p>";
					$returnValue = TRUE;
					break;
				}
			case 'd':
				if(!is_float($paramArray[$key]))
				{
#					echo "<p>Double specified and not found</p>";
					return FALSE;
					break;
				}
				else{
#					echo "<p>Double found</p>";
					$returnValue = TRUE;
					break;
				}
			case 's':
				if(!is_string($paramArray[$key]))
				{
#					echo "<p>String specified and not found.</p>";
					return FALSE;
					break;
				}
				else
				{
#					echo "<p>String found</p>";
					$returnValue = TRUE;
					break;
				}	
			case 'b':
#				echo "<p>Blob - don't think I really care on this one</p>";
				$returnValue = TRUE;
				#return FALSE;
				break;
			default:
#				echo "<p>Parameter type didn't match type string.</p>";
				return FALSE;
				break;
		}																																															// end switch	
		}
		if($returnValue == TRUE) return TRUE;
	} // end public static function matchTstring($typeString, array $paramArray, $query)
} // end class Helpers

/**
 * Class for working with data from the ff_stats database and the CBS Sports API
 */
class Data {
	function __construct(){
		$this->errorMsg = array('place holder');
	}
	
    /** @var string $query to be used by mysqli_stmt::prepare */
	private $query;
    /** @var object $dbConn prexisting mysqli object to be used for database queries */
	private $dbConn;
	/** @var array $parameters to hold paramaters to be passed to mysqli_stmt::bind_param() */
	private $parameters = array();
    /** @var string $typeString value to pass to mysqli_stmt::bind_param() to indicate data types going to the query */
	private $typeString;
	private $errorMsg;
	
    /** @var bool $dataValided indicator for success matchTstring() checking $typeString, $parameters, and $query */
	public $dataValidated;	
    /** @var object $stmt mysqli_stmt object derived from $dbConn */
	public $stmt;
	
	public function GetErrorMsgs(){
		foreach($this->errorMsg as $msg){
			echo "<p>$msg</p>";
		}
	}
	public function setParameters(array $parameters){
		if(($this->_matchTstring($this->typeString, $parameters, $this->query))){
			$this->dataValidated = TRUE;
			$this->parameters = $parameters;
		}
		else{
			$this->dataValidated = FALSE;
		}
		return $this->dataValidated;
	}
	
    /**
     * calls _matchTstring() method to match count of 
     * $typeString, $paramArray[], and '?' in $query to see that they all have the same count
     * @param string $typeString type string used by mysqli_stmt::bind_param
     * @param array $paramArray paramater values passed to mysqli_stmt::bind_param
     * @param string $query string to be used by mysqli_stmt::prepare()
     */
	public static function matchTstring($typeString, array $paramArray, $query){
		$instance = new self();
		$returnValue = $instance->_matchTstring($typeString, $paramArray, $query);
		return $returnValue;
	}
	protected function _matchTstring($typeString, array $paramArray, $query){
	  	$returnValue = TRUE;
		$typeStringArray = str_split($typeString);
		if(count($typeStringArray) !== count($paramArray)){
			array_push($this->errorMsg, "Type String count doesn't match number of parameters.");
			return FALSE;
		}
		if(count($typeStringArray) !== substr_count($query,'?')){
			array_push($this->errorMsg, "Type string count doesn't match num parameters in query.");
			return FALSE;
		}
		/*foreach($typeStringArray as $key => $value){
			echo "<p>Key: $key - Value: $value</p>";
			switch($value){
				case 'i':
					if(!is_int($paramArray[$key]))
					{
						 array_push($this->errorMsg, "Int specified and not found");
						 array_push($this->errorMsg, "Paramater Value: ". $paramArray[$key]);
						return FALSE;
						break;
					}
					else{
	#					echo "<p>Int found</p>";
						$returnValue = TRUE;
						break;
					}
				case 'd':
					if(!is_float($paramArray[$key]))
					{
						array_push($this->errorMsg, "Double specified and not found");
						return FALSE;
						break;
					}
					else{
	#					echo "<p>Double found</p>";
						$returnValue = TRUE;
						break;
					}
				case 's':
					if(!is_string($paramArray[$key]))
					{
						array_push($this->errorMsg, "String specified and not found.");
						return FALSE;
						break;
					}
					else
					{
	#					echo "<p>String found</p>";
						$returnValue = TRUE;
						break;
					}	
				case 'b':
	#				echo "<p>Blob - don't think I really care on this one</p>";
					$returnValue = TRUE;
					#return FALSE;
					break;
				default:
					array_push($this->errorMsg, "Parameter type didn't match type string.");
					return FALSE;
					break;
			} // end case
		} // end foreach($typeStringArray as $key => $value)*/
		if($returnValue == TRUE){ return TRUE;}
  	} // end public static function matchTstring($typeString, array $paramArray, $query)

  	/**
     * runs the mysqli_stmt::prepare() for $this->stmt object
     */
	private function stmtPrepare(){
		$this->stmt = $this->dbConn->prepare($this->query);
	}  	
	
    /**
     * Binds variables saved in $this->paramaters to $this->stmt
     */
	public function bindParameters(){
		if($this->dataValidated == TRUE && !empty($this->stmt)){
			$paramArr = array($this->typeString);
			foreach($this->parameters as &$parameter){
				array_push($paramArr, $parameter);
			}
			$ref = new ReflectionClass('mysqli_stmt'); 
			$method = $ref->getMethod("bind_param"); 
			$method->invokeArgs($this->stmt,$paramArr);
		}
		else{
			if(!($this->dataValidated)){
				array_push($this->errorMsg, "Data not validated");
			}
			if(empty($this->stmt)){
				array_push($this->errorMsg, "stmt is empty");
			}
		}
	}
	
    /**
     * Sets $this->stmt to mysqli_stmt object if previous one was closed or not created
     */
	public function updateStmt($queryString, $typeString, array $parameters){
		$this->query = $queryString;
		$this->typeString = $typeString;
		$this->parameters = $this->setParameters($parameters) ? $parameters : FALSE;
		$this->stmtPrepare();
	}
	
    /**
     * Alternate way to create Data object without using the __construct()
     * 
     * Calls _withValues() to set parameters and return Data object.
     * 
     * @param object $dbConn pre-existing mysqli object for working with the database
     * @param string $query string for use with mysqli_stmt::prepare()
     * @param string $types to represent data types passed to  mysqli_stmt::bind_param()
     * @param array $paramaterValues[] to set to $paramaters and use with mysqli_stmt::bind_param()
     * @return Data object
     */
	public static function WithValues(&$dbConn, $query, $types, array $parameterValues){
		$instance = new self();
		$instance->_withValues($dbConn,$query,$types, $parameterValues);
		return $instance;
	}
	protected function _withValues(&$dbConn, $query, $types, array $parameterValues){
		$this->query = $query;
		$this->typeString = $types;
		$this->dbConn = $dbConn;
		$this->parameters = $this->setParameters($parameterValues) ? $parameterValues : FALSE;
		#$this->dataValidated = $this->matchTstring($this->typeString,$this->parameters, $this->query) ? TRUE : FALSE;
		$this->stmtPrepare();
	}
	
    /**
     * Quick way to select and fetch all data from a table
     * 
     * Fetches all rows from queries without paramters like 'SELECT foo, bar, bah FROM table'.
     * 
     * @param string $query string to use with mysqli::query()
     * @param mysqli $dbConn pre-existing mysqli object for working with the database
     * @return array $result numeric indexed array with results from mysqli_fetch_all()
     */
	public static function SelectFetchAll($query, &$dbConn){
		$instance = new self();
		$result = $instance->_selectFetchAll($query, $dbConn);
		return $result;
	}
	protected function _selectFetchAll($query, &$dbConn){
		if(!($result = $dbConn->query($query))){
			echo "Query failed: (". $dbConn->errno .") ". $dbConn->error;
			return FALSE;
		}
		$data = $result->fetch_all(MYSQLI_NUM);
		$result->close();
		return $data;
	}
	
	public static function WithDbConn(&$dbConn){
		$instance = new self();
		$instance->_withDbConn($dbConn);
		return $instance;
	}
	protected function _withDbConn(&$dbConn){
		$this->dbConn = $dbConn;
	}
	/* Finish insert for ff_positions and then nfl_players*/
	
} // end class Data
?>