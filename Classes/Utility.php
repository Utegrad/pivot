<?php
require_once('Config.php');

class CBSSports {
	function __construct($entity){		
		$this->api = new Api();
		$this->AccessToken = $this->api->AccessToken;
		
		switch($entity){
			case 'nfl_player':
				break;
			case 'ff_position':
				break;
			default:
				return FALSE;
				break;
		}
		$this->Element = $this->elements[$entity]['element'];
		$this->URLSuffix = "?version=2.0&access_token=". $this->AccessToken ."&response_format=json"."&".$this->elements[$entity]['suffix'];
		$this->GetURL = $this->BaseURL. $this->Element .$this->URLSuffix;
		return TRUE;
	}
	
	public $AccessToken;
	public $GetURL;
	private $BaseURL = 'http://api.cbssports.com/fantasy/';
	private $Element; # the part of the URL specific to what you're trying to _GET
	private $URLSuffix; 
	private $timeout = 30;
	private $api;
	private $elements = array(
		'nfl_player' => array ( 'element' => "players/list", 'suffix' => "SPORT=football" ),
		'ff_position' => array ( 'element' => 'positions', 'suffix' => '' ),
		
	);

	public function GetData(){
		$crl = curl_init();
		$timeout = 30;

		curl_setopt($crl, CURLOPT_URL, $this->GetURL);
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $this->timeout );
		$ret = curl_exec($crl);
		curl_close($crl);
		$data = json_decode($ret);
		return $data;
	}
	
}

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

class Data {
	function __construct(){
		$this->errorMsg = array('place holder');
	}
	
	private $query;
	private $dbConn;
	private $parameters = array();
	private $typeString;
	private $errorMsg;
	
	public $dataValidated;	
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

	private function stmtPrepare(){
		$this->stmt = $this->dbConn->prepare($this->query);
	}  	
	
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
	
	public static function WithValues(&$dbConn, $query, $types, array $parameterValues){
		$instance = new self();
		$instance->_withValues($dbConn,$query,$types, $parameterValues);
		return $instance;
	}
	protected function _withValues(&$dbConn, $query, $types, array $parameterValues){
		$this->query = $query;
		$this->typeString = $types;
		$this->dbConn = $dbConn;
		$this->parameters = $this->setParameters($parameterValues) ? $parameterValues : NULL;
		#$this->dataValidated = $this->matchTstring($this->typeString,$this->parameters, $this->query) ? TRUE : FALSE;
		$this->stmtPrepare();
	}
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
	
	/* Finish insert for ff_positions and then nfl_players*/
	
} // end class Data
?>