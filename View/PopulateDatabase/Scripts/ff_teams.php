<?php
require_once '../../../bootstrap.php';
$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}
require_once APP_ROOT . 'Classes/FfTeam.php';
require_once APP_ROOT . 'Classes/Config.php';
require_once APP_ROOT . 'Classes/Utility.php';

$db = new Database();

$ffTeamData = CBSSports::GetJsonData(CBSSports::FF_TEAM);

foreach ($ffTeamData['data']->body->teams as $team){
	$ffTeam = FfTeam::withJson($team);
	
	$cnt = Data::GetTableRowCount('ff_teams', '*', $db->conn, "WHERE `id` = '". $ffTeam->Id ."'");
	if ($cnt == 0 ) {
		$insertFTeam = "INSERT INTO ff_teams (`id`, `name`, `short_name`, `abbr`, `long_abbr`, `logo`, `ff_owner_id`, `ff_leagues_id`)
				SELECT ?, ?, ?, ?, ?, ?, ?, ?";
		$league = Data::SelectFetchAll("SELECT id FROM ff_leagues", $db->conn, TRUE);
		$leagueId = $league[0]['id'];
		$parameters = array(
				$ffTeam->Id,
				$ffTeam->Name,
				$ffTeam->ShortName,
				$ffTeam->Abbr,
				$ffTeam->LongAbbr,
				$ffTeam->Logo,
				$team->owners[0]->id,
				$leagueId,
		);
		$ffTeamDb = Data::WithValues($db->conn, $insertFTeam, 'issssssi', $parameters);
		$ffTeamDb->bindParameters();
		if(!($ffTeamDb->stmt->execute())){
			array_push($ffTeamDb::$errorMsg, "Execution error: (". $ffTeamDb->stmt->errno. ") - ". $ffTeamDb->stmt->error);
			// print_r($ffTeamDb::$errorMsg);
			break;
		}
	}
}

$db->conn->close();

header('Location: '. $APP_URL .'/View/PopulateDatabase/index.php' );
