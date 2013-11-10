<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');
require_once('Classes/FfOwner.php');

if(!($cbsSports = new CBSSports('ff_owner'))){
	echo "Unable to create CBS Sports Object";
}
$database = new Database();
$helpers = new Helpers();
$owners = $cbsSports->GetData();

$ownerIds = array();

$selectOwnerIds = "SELECT id FROM ff_owners";
$ownerIds = Data::SelectFetchAll($selectOwnerIds, $database->conn);
if(empty($ownerIds)&& ($ownerIds !== FALSE)){
	// INFO : no owners found.  
}

echo "<html>";

// loop through Data
foreach($owners->body->owners as $owner){
	$insertStatus = 'No';
	// populate the FfOwner object
	$ffOwner = FfOwner::withJson($owner);
	// check to see if they are already in the database by comparing to $ownerIds
	if(!($helpers->searchIfExists($ffOwner->Id,$ownerIds))){
		// insert the owner if not in the database
		$insertPlayerQuery = "INSERT INTO ff_owners (id, name) VALUES(?, ?)";
		$utilData = Data::WithValues($database->conn,$insertPlayerQuery,'ss',
			array($ffOwner->Id, $ffOwner->Name));
		$utilData->bindParameters();
		if(!($utilData->stmt->execute())){
					echo "Execute failed: (".$utilData->stmt->errno.") ".$utilData->stmt->error;
					exit();
				}
				else{
					$insertStatus = 'Yes';
				}
		$utilData->stmt->close();
	}
	else{
		// set indicator for not inserted if already in the database
		echo "<p>Owner". $ffOwner->Name ." already in the database</p>";
	}
	
	// echo out all the owner data 
	echo "<p>Name: ". $ffOwner->Name ."<br>";
	echo "ID: ". $ffOwner->Id ."<br>";
	echo "Inserted to the database: $insertStatus";
	
}

echo "</html>";
$database->conn->close();
?>