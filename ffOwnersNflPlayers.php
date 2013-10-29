<?php
require_once('Classes/NflPlayer.php');
require_once('Classes/FfOwner.php');
require_once('Classes/Utility.php');

$database = new Database();
$helpers = new Helpers();

// Get the data from CBS Sports for players
if(!($cbsSportsNflPlayers = new CBSSports('nfl_player'))){
    echo "Unable to create CBS Sports Object with NFL Player data";
}
if(!($curlNflPlayers = $cbsSportsNflPlayers->GetData())){
	echo "<p>Error retreiving NFL Player data from CBS Sports</p>";
}

$dbNflPlayerIds= array();
$selectNflPlayers = "SELECT nfl_players.id FROM nfl_players";
$dbNflPlayerIds = Data::SelectFetchAll($selectNflPlayers, $database->conn);
if(empty($dbNflPlayerIds) && ($dbNflPlayerIds!== FALSE)){
    // INFO : no players found.  
}

// Get the data from CBS Sports for players
if(!($cbsSportsFfOwners = new CBSSports('ff_owner'))){
    echo "Unable to create CBS Sports Object with FF Owner data";
}
if(!($curlFfOwners = $cbsSportsFfOwners->GetData())){
	echo "<p>Error retreiving FF Owner data from CBS Sports.";
}

$dbFfOwnerIds = array();
$selectFfOwners = "SELECT ff_owners.id from ff_owners";
$dbFfOwnerIds = Data::SelectFetchAll($selectFfOwners, $database->conn);
if(empty($dbFfOwnerIds) && ($dbFfOwnerIds !== FALSE)){
    // INFO : no FF Owners found from the database.
}


echo "<html>";

echo "</html>";

$database->conn->close();

?>