<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');

$database = new Database();

$cbsWeeklyScoring = CBSSports::GetJsonData('league/fantasy-points/weekly-scoring', 'player_status=all');
if(!$cbsWeeklyScoring){
	echo "Failed to retrieve data";
}

$dbNflPlayerIds= array();
$selectNflPlayers = "SELECT nfl_players.id FROM nfl_players";
$dbNflPlayerIds = Data::SelectFetchAll($selectNflPlayers, $database->conn);
if(empty($dbNflPlayerIds) && ($dbNflPlayerIds!== FALSE)){
	// INFO : no players found.
}
$dbPlayerCount = count($dbNflPlayerIds);
for ($i = 0; $i < $dbPlayerCount; $i++) {
	$dbNflPlayerIds[$i] = $dbNflPlayerIds[$i][0];
}

$insertPlayerWeeklyScores = "INSERT INTO nfl_players_weekly_scores
		(nfl_player_id, ff_week_id, score, nfl_player_status_id) ";
$playersScoringCount = count($cbsWeeklyScoring['data']->body->weekly_scoring->players);
$statusIdSelect = "SELECT id FROM nfl_player_statuses WHERE abvr = 'A'";

$records = array();
$basket = 300;

/**
 * @todo work out a way to run the insert over multiple batches rather than 
 * in one big chunk.
 */
for ($i = 0; $i < $playersScoringCount; $i++) {
	
		if (in_array($cbsWeeklyScoring['data']->body->weekly_scoring->players[$i]->id, $dbNflPlayerIds)) {
			if ($i % $basket == 0) {
				
			}
			$weeklyScoresCount = count($cbsWeeklyScoring['data']->body->weekly_scoring->players[$i]->periods);
			for ($j = 0; $j < $weeklyScoresCount; $j++) {
				populateRecords($i, $j);
			}
		}
		else{
			// skip adding player not yet in the database for now
		}	
}

function getWeekIdSelect($playersIndex, $periodIndex){
	global $cbsWeeklyScoring;
	$weekIdSelect = "SELECT id FROM ff_weeks WHERE number = '".
			$cbsWeeklyScoring['data']->body->weekly_scoring->players[$playersIndex]->periods[$periodIndex]->period ."'";
	return $weekIdSelect;
}

function getPlayerId($playersIndex){
	global $cbsWeeklyScoring;
	return $cbsWeeklyScoring['data']->body->weekly_scoring->players[$playersIndex]->id;
}

function getPlayerWeekScore($playerIndex, $periodIndex){
	global $cbsWeeklyScoring;
	return $cbsWeeklyScoring['data']->body->weekly_scoring->players[$playerIndex]->periods[$periodIndex]->score;
}

function concatInsPWS($playerIndex, $periodIndex){
	global $insertPlayerWeeklyScores, $statusIdSelect;
	if ($playerIndex == 0 && $periodIndex == 0) {
		$insertPlayerWeeklyScores .= "SELECT '". getPlayerId($playerIndex) ."' , (".
			getWeekIdSelect($playerIndex, $periodIndex).") , '". getPlayerWeekScore($playerIndex, $periodIndex) ."' , (".
			$statusIdSelect .") <br>";
	}
	else{
		$insertPlayerWeeklyScores .= "UNION ALL SELECT '". getPlayerId($playerIndex) ."' , (".
			getWeekIdSelect($playerIndex, $periodIndex).") , '". getPlayerWeekScore($playerIndex, $periodIndex) ."' , (".
			$statusIdSelect .") <br>";
	}
}

function populateRecords($playerIndex, $periodIndex){
	global $records, $statusIdSelect;
	
	if($playerIndex == 0 && $periodIndex == 0 ){
		$rec = "SELECT '". getPlayerId($playerIndex) ."' , (". getWeekIdSelect($playerIndex, $periodIndex) .") , '". 
			getPlayerWeekScore($playerIndex, $periodIndex) ."' , (". $statusIdSelect .")";
		array_push($records, $rec);
	}
	else{
		$rec = "UNION ALL SELECT '". getPlayerId($playerIndex) ."' , (". getWeekIdSelect($playerIndex, $periodIndex).
			") , '". getPlayerWeekScore($playerIndex, $periodIndex) ."' , (". $statusIdSelect .")";
		array_push($records, $rec);
	}
}

function insQuery($records){
	global $insertPlayerWeeklyScores, $database;
	if($database->conn->query($insertPlayerWeeklyScores .' '. implode(' ', $records))){
		printf("\n%d row(s) inserted.\n",$database->conn->affected_rows);
	}
	else{
		echo "\nFailed to insert data: (". $database->conn->errno .") ". $database->conn->error ."\n";
	}
}

/* $recCount = count($records);
$batch1 = array();
$batch2 = array();
$batch3 = array();
$batch4 = array();
$batch5 = array();
$allBatches = array();
array_push($allBatches, $batch1);
array_push($allBatches, $batch2);
array_push($allBatches, $batch3);
array_push($allBatches, $batch4);
array_push($allBatches, $batch5);

for ($i = 0; $i < $recCount; $i++) {
	
	if ($i < 300) {
		array_push($batch1, $records[$i]);
	}
	if (($i >= 300) && ($i < 600)) {
		array_push($batch2, $records[$i]);
	}
	if (($i >= 600) && ($i < 900)) {
		array_push($batch3, $records[$i]);
	}
	if (($i >= 900) && ($i < 1200)) {
		array_push($batch4, $records[$i]);
	}
	if ($i > 1200) {
		array_push($batch5, $records[$i]);
	}
	
}

foreach ($allBatches as $batches){
	foreach ($batches as $batch){
		print_r($batch);
	}
}
 */


//print_r($records);

/* if($database->conn->query($insertPlayerWeeklyScores .' '. implode(' ', $records))){
	printf("\n%d row(s) inserted.\n",$database->conn->affected_rows);
}
else{
	echo "\nFailed to insert data: (". $database->conn->errno .") ". $database->conn->error ."\n";
} */

//echo "$insertPlayerWeeklyScores";

$database->conn->close();
?>