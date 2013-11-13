<?php
require_once '../../../bootstrap.php';
$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}
require_once APP_ROOT . 'Classes/NflTeam.php';
require_once APP_ROOT . 'Classes/Config.php';
require_once APP_ROOT . 'Classes/Utility.php';

$db = new Database();

$nflTeamData = CBSSports::GetJsonData(CBSSports::NFL_TEAM);

foreach($nflTeamData['data']->body->pro_teams as $proTeam){
	// is this team already in the database?
	$team = NflTeam::withJson($proTeam);
	
	$cnt = Data::GetTableRowCount('nfl_teams', '*', $db->conn, "WHERE `abvr` = '". $team->Abvr ."'");
	if ($cnt == 0 ) {
		$insertProTeamQuery = "INSERT INTO nfl_teams (`abvr`, `name`, `nick_name`) SELECT ?, ?, ?";
		$teamData = Data::WithValues($db->conn, $insertProTeamQuery, 'sss', array($team->Abvr, $team->Name, $team->NickName) );
		$teamData->bindParameters();
		if(!($teamData->stmt->execute())){
			array_push($teamData->errorMsg, "Execution error: (" .$teamData->stmt->errno. ") - ". $teamData->stmt->error);
			break;
		}
	}
	
}

$db->conn->close();

header('Location: '. $APP_URL .'/View/PopulateDatabase/index.php' );

?>