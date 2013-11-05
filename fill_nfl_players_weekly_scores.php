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
$basket = 100;

/**
 * @todo work out a way to run the insert over multiple batches rather than 
 * in one big chunk.
 */
for ($i = 0; $i < $playersScoringCount; $i++) {
		if (in_array($cbsWeeklyScoring['data']->body->weekly_scoring->players[$i]->id, $dbNflPlayerIds)) {
			if($i > 0 && $i % $basket == 0){
				echo "\nAt $i Run insert.";
				// insert values collected in $records
				insQuery($records);
				// empty $records
				unset($records);
				$records = array();
			}
			$weeklyScoresCount = count($cbsWeeklyScoring['data']->body->weekly_scoring->players[$i]->periods);
			for ($j = 0; $j < $weeklyScoresCount; $j++) {
				// if $records count == 0 then start first value with select
				if (count($records) == 0) {
					populateRecords($i, $j, TRUE);
				}
				else{
					// otherwise, start with UNION ALL SELECT
					populateRecords($i, $j);
				}
				// for the last player from curl request do final insert
			}
			if ($i == $playersScoringCount -1) {
				echo "\nLast Record at $i";
				insQuery($records);
				unset($records);
				$records = array();
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

function populateRecords($playerIndex, $periodIndex, $start = FALSE){
	global $records, $statusIdSelect;
	if($start == TRUE){
		// empty $records[] to fill it with next batch
		//unset($records);
		//$records = array();
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

//print_r($records);

$database->conn->close();
?>