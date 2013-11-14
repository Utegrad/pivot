<?php
require_once '../../../bootstrap.php';
$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}
require_once APP_ROOT . 'Classes/FfLeague.php';
require_once APP_ROOT . 'Classes/Config.php';
require_once APP_ROOT . 'Classes/Utility.php';

$db = new Database();

$ffLeagueDetails = CBSSports::GetJsonData(CBSSports::DETAILS);

$league = FfLeague::withLeagueJson($ffLeagueDetails['data']->body->league_details);
/**
 *  @todo long term this doesn't work because it doesn't figure out which owner is in which league before inserting it.
 */
$commissioner = Data::SelectFetchAll("SELECT id from ff_owners where commissioner <> '0'", $db->conn, TRUE);
$league->Commissioner = $commissioner[0]['id'];

$cnt = Data::GetTableRowCount('ff_leagues', '*', $db->conn, "WHERE `name` = '". $league->Name ."'");
if($cnt == 0 ){
	$insertLeagueDetailsQuery = "INSERT INTO ff_leagues (`name`, `commissioner_id`) SELECT ?, ?";
	$ffLeageDb = Data::WithValues($db->conn, $insertLeagueDetailsQuery, 'ss', array($league->Name, $league->Commissioner));
	$ffLeageDb->bindParameters();
	if(!($ffLeageDb->stmt->execute())){
		array_push($ffLeageDb::$errorMsg, "Execution error: (". $ffLeageDb->stmt->errno .") - ". $ffLeageDb->stmt->error);
	}
}



$db->conn->close();

header('Location: '. $APP_URL .'/View/PopulateDatabase/index.php' );
