<?php
  require_once('Classes/Config.php');
  
  $query = "SELECT id, name from ff_stats_groups WHERE (id = ?) AND (name = ?)";
  $typeString = 'is';
  $paramValues = array (3, 'Off');
  $result = (TestData::matchTstring($typeString, $paramValues, $query) ? "TRUE" : "FALSE");

$selectResult = array( 'id' => NULL, 'name' => NULL);
$db = new Database();

$test = new TestData($db->conn,$query,$typeString,$paramValues);
$test->bindParameters();
$test->stmt->execute();

$test->stmt->bind_result($selectResult['id'],$selectResult['name']);
if($test->stmt->fetch() === NULL){
	echo "<p>Nothing fetched: (". $test->stmt->errno .") ". $test->stmt->error ."</p>";
}

$test->stmt->close();

print_r($selectResult);

#echo "<p>$result</p>";
#$result == TRUE ? echo "True" : echo "False";

$db->conn->close();

class TestData{
	function __construct(&$dbConn, $query, $types, array $parameterValues){
		$this->query = $query;
		$this->typeString = $types;
		$this->dbConn = $dbConn;
		$this->parameters = $this->setParameters($parameterValues) ? $parameterValues : NULL;
		#$this->dataValidated = $this->matchTstring($this->typeString,$this->parameters, $this->query) ? TRUE : FALSE;
		$this->stmtPrepare();
	}
	
	private $query;
	private $dbConn;
	private $parameters = array();
	private $typeString;
	
	public $dataValidated;	
	public $stmt;
	
	public function setParameters(array $parameters){
		if($this->matchTstring($this->typeString, $parameters, $this->query)){
			$this->dataValidated = TRUE;
			$this->parameters = $parameters;
		}
		else{
			$this->dataValidated = FALSE;
		}
		return $this->dataValidated;
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
			} // end case
		} // end foreach($typeStringArray as $key => $value)
		if($returnValue == TRUE) return TRUE;
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
			echo "<p>Data not validated or stmt is empty</p>";
			printf("<p>dataValidated: %s</p><p>stmt: <br>%s</p>",print_r($this->dataValidated), print_r($this->stmt));
		}
	}
	
}  // end class TestData
/*class BindParam{ 
    private $values = array(), $types = ''; 
    
    public function add( $type, &$value ){ 
        $this->values[] = $value; 
        $this->types .= $type; 
    } 
    
    public function get(){ 
        return array_merge(array($this->types), $this->values); 
    } 
}*/
  
?>