<?php

require_once('Classes/Config.php');
require_once('Classes/Utility.php');

$database = new Database();

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


echo "done";
?>