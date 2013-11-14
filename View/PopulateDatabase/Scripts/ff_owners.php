<?php
require_once '../../../bootstrap.php';
$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}
require_once APP_ROOT . 'Classes/FfOwner.php';
require_once APP_ROOT . 'Classes/Config.php';
require_once APP_ROOT . 'Classes/Utility.php';

$db = new Database();

$ffTeamOwnerData = CBSSports::GetJsonData(CBSSports::FF_OWNER);

foreach($ffTeamOwnerData['data']->body->owners as $owner){
	$owner = FfOwner::withJson($owner);
	
	$cnt = Data::GetTableRowCount('ff_owners', '*', $db->conn, "WHERE `id` = '". $owner->Id ."'");
	if ($cnt == 0) {
		$insertFFTeamOwner = "INSERT INTO ff_owners (`id`, `name`, `commissioner`) SELECT ?, ?, ?";
		$ffOwnerDb = Data::WithValues($db->conn, $insertFFTeamOwner, 'ssi', array($owner->Id, $owner->Name, $owner->Commissioner) );
		$ffOwnerDb->bindParameters();
		if(!($ffOwnerDb->stmt->execute())){
			array_push($ffOwnerDb::$errorMsg, "Execution error: (" .$ffOwnerDb->stmt->errno. ") - ". $ffOwnerDb->stmt->error);
			break;
		}
	}
	
}



$db->conn->close();

header('Location: '. $APP_URL .'/View/PopulateDatabase/index.php' );
