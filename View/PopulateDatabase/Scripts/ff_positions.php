<?php
require_once '../../../bootstrap.php';
$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}
require_once APP_ROOT . 'Classes/FfPosition.php';
require_once APP_ROOT . 'Classes/Config.php';
require_once APP_ROOT . 'Classes/Utility.php';

$db = new Database();

$positions = CBSSports::GetJsonData(CBSSports::FF_POSITION);

foreach ($positions['data']->body->positions as $position){
	$pos = FfPosition::withJson($position);
	
	// is the current stats group in the database
	$count = Data::GetTableRowCount('ff_stats_groups', '*', $db->conn, "WHERE name = '". $pos->PositionGroup->FF_StatsGroupName ."'");
	if ($count == 0) {
		$insertStatsGroupQuery = "INSERT INTO ff_stats_groups (`name`) SELECT ?";
		$data = Data::WithValues($db->conn, $insertStatsGroupQuery, 's', array($pos->PositionGroup->FF_StatsGroupName));
		$data->bindParameters();
		if(!($data->stmt->execute())){
			array_push($data->errorMsg, "Execute failed: (". $data->stmt->errno .") ".$data->stmt->error);
			break;
		}
	}
	
	if (in_array($pos->Abbr, $pos->relivantPositions)) {
		$posCount = Data::GetTableRowCount('ff_positions', '*', $db->conn, "WHERE abvr = '". $pos->Abbr ."'" );
		if ($posCount == 0) {
			$insertPosQuery = "INSERT INTO ff_positions (`abvr`, `name`, `ff_stats_group_id`) SELECT  ?, ?, ?";
			$grpIdQuery = "SELECT `id` from ff_stats_groups WHERE `name` = '". $pos->PositionGroup->FF_StatsGroupName ."'";
			$grp = Data::SelectFetchAll($grpIdQuery, $db->conn, TRUE);
			$posData = Data::WithValues($db->conn, $insertPosQuery, 'ssi', array($pos->Abbr, $pos->Name, $grp[0]['id']) );
			$posData->bindParameters();
			if(!($posData->stmt->execute())){
				array_push($posData->errorMsg, "Execute Failed: (". $posData->stmt->errno .") ". $posData->stmt-error);
				break;
			}
		}
	}
}

$db->conn->close();

header('Location: '. $APP_URL .'/View/PopulateDatabase/index.php' );

?>
