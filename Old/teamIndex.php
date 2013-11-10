<?php
require_once('Classes/NflPlayer.php');
require_once('Classes/FfOwner.php');
require_once('Classes/Utility.php');

$database = new Database();
$helpers = new Helpers();

// Get the data from CBS Sports for players
/* if(!($cbsSportsNflPlayers = new CBSSports('nfl_player'))){
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
} */

if(!($cbsSportsFfRosters = new CBSSports('rosters', 'all'))){
    echo "Unable to create CBS Sports Object for getting FF Team Roster Data";
}
if(!($curlFfRosters = $cbsSportsFfRosters->GetData())){
    echo "Unable to retrieve FF Team Roster Data";
}

// Get the data from CBS Sports for ff_owners
/*
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
}*/



echo "<html>";

$insertFfTeamRoster = "<p>INSERT INTO ff_teams_nfl_players<br>
        (ff_team_id, nfl_player_id, modified )<br>";
$teamsCount = count($curlFfRosters->body->rosters->teams);

echo "<p>Teams Count: $teamsCount </p>";

// $insertFfTeamRoster .= "SELECT `". $curlFfRosters->body->rosters->teams[$i]->id ."` , `". $curlFfRosters->body->rosters->teams[$i]->players[$j]->id ."` , `". date('Y-m-d H:i:s') ."`<br>";
// for the first team 
//		for the first player SELECT
// 		then for each subsequent player UNION ALL SELECT
// for rest of the teams
// 		and for all of their players UNION ALL SELECT
for ($teamIndex = 0; $teamIndex < $teamsCount; $teamIndex++) {
	$playersCount = count($curlFfRosters->body->rosters->teams[$teamIndex]->players);
	if($teamIndex == 0){  // at the first team
		// SELECT ... for the first player
		$insertFfTeamRoster .= "SELECT". getInsValues($teamIndex, 0);
		for ($playerIndex = 0; $playerIndex < $playersCount; $playerIndex++) {
			if($playerIndex == 0) { continue; } // skip the first player on the first team
			$insertFfTeamRoster .= "UNION ALL SELECT". getInsValues($teamIndex, $playerIndex);
		}
	}
	else{ // at all subsequent teams
		foreach($curlFfRosters->body->rosters->teams[$teamIndex]->players as $player){
			$insertFfTeamRoster .= "UNION ALL SELECT '". $curlFfRosters->body->rosters->teams[$teamIndex]->id ."' , '".
					$player->id ."' , '". date('Y-m-d H:i:s') ."'<br> ";
		}
	}
}

function getInsValues($i, $j){
	global $curlFfRosters;
	$teamId = $curlFfRosters->body->rosters->teams[$i]->id;
	$playerId = $curlFfRosters->body->rosters->teams[$i]->players[$j]->id;
	return " '". $teamId ."' ,
				 '". $playerId."' , '". 
				 date('Y-m-d H:i:s') ."' <br>";
}

$cleanInsQuery = $insertFfTeamRoster;

$cleanInsQuery = strip_tags($cleanInsQuery);
// $cleanInsQuery = $database->conn->real_escape_string($cleanInsQuery); 
if($database->conn->query($cleanInsQuery)){
	printf("<p>%d row(s) inserted.<p>",$database->conn->affected_rows);
}
else{
	echo "<p>Failed to insert data: (". $database->conn->errno .") ". $database->conn->error ."</p>";
}


/* foreach($curlFfRosters->body->rosters->teams as $team){
	for ($i = 0; $i < $teamsCount; $i++) {
		$playersCount = count($team->players);
		if ($i == 0) {
			for ($j = 0; $j < $playersCount; $j++) {
				$k = $j+1;
				$insertFfTeamRoster .= "UNION ALL SELECT `". $team->id ."` , `". $team->players[$k]->id ."` , `". date('Y-m-d H:i:s') ."`<br>";
			}
		}
		else{
			
		}
	}	 */
	/* for ($i = 0; $i < $teamsCount; $i++) {
		if($i == 0){
			$insertFfTeamRoster .= "SELECT `". $team->id ."` , `". $team->players[0]->id ."` , `". date('Y-m-d H:i:s') ."`<br>";
		}
	} */
	/* for ($i = 0; $i < $teamsCount; $i++) {
		foreach($team->players as $player){
			if ($i == 0) {
				$insertFfTeamRoster .= "SELECT `". $team->id ."` , `". $player->id ."` , `". date('Y-m-d H:i:s') ."`<br>";
			}
			else{
				$insertFfTeamRoster .= "
					UNION ALL SELECT `". $team->id ."` , `". $player->id . "` , `". date('Y-m-d H:i:s') ."`<br>";
			}
		}
	} 
}*/
$insertFfTeamRoster .= "</p>";

echo "<p>Insert Statment: <br>$insertFfTeamRoster</p>";

// loop through the roster data
/* foreach($curlFfRosters->body->rosters->teams as $team){
        
    $nflPlayerIdsOnTeam = array();
    foreach($team->players as $player){
        array_push($nflPlayerIdsOnTeam, $player->id);
    }
    
    foreach($team->players as $player){
    	$insertFfTeamRoster .= "UNION ALL SELECT `". $team->id ."` , `". $player->id ."` , `". date('Y-m-d H:i:s') ."`";
    }
    foreach($nflPlayerIdsOnTeam as $playerId){
    	$insertFfTeamRoster .= "VALUES( ". $team->id . ", ". $player->id .", ". date('Y-m-d H:i:s') .")";
    }
    
    if(!($utilData->stmt->execute())){
    	echo "Execute failed: (".$utilData->stmt->errno.") ".$utilData->stmt->error;
        exit();
    }
    else{
    	$insertStatus = 'Yes';
    }
    $utilData->stmt->close();
    
    echo "<p>Team name: ". $team->name ."</p>";
    echo "<p>Team ID: ". $team->id ."</p>";
    echo "<table><tr><td>Player</td><td>Player ID</td></tr>";
    
    echo "</table>";
} */

/*

// loop through the players from CBS Sports
foreach($curlNflPlayers->body->players as $player){
    $playerInsertStatus = "No";
    
    // populate an NflPlayer object
    $nflPlayer = NFLPlayer::withJson($player);
    
    // check if $player is already in the database and insert them if not
    if(!($helpers->searchIfExists($nflPlayer->Id, $dbNflPlayerIds))){
        // and the player has a relivant position
        if(in_array($nflPlayer->Pos, $nflPlayer->Position->relivantPositions)){
            $insertNflPlayerQuery = "INSERT INTO nfl_players 
                        (id, first_name, last_name, full_name,
                        bye_week, ff_positions_id )
                        VALUES(?, ?, ?, ?, ?,(SELECT id FROM ff_positions
                                WHERE abvr = ?) )";
            $utilData = Data::WithValues($database->conn,$insertNflPlayerQuery,'isssis',
                        array($nflPlayer->Id, $nflPlayer->FirstName, $nflPlayer->LastName,
                        $nflPlayer->FullName,$nflPlayer->ByeWeek, $nflPlayer->Pos));
            $utilData->bindParameters();
            if(!($utilData->stmt->execute())){
                echo "Execute failed: (".$utilData->stmt->errno.") ".$utilData->stmt->error;
                exit();
            }
            else{
                $insertStatus = 'Yes';
            }
            $utilData->stmt->close();
        } // end if(in_array($nflPlayer->Pos, $nflPlayer->Position->relivantPositions))
    } // end if(!($helpers->searchIfExists($nflPlayer->Id, $dbNflPlayerIds)))
    else{
        
    }
   
   if(in_array($nflPlayer->Pos, $nflPlayer->Position->relivantPositions)){
           // get the player's profile
           if(!($cbsSportsPlayerProfile = new CBSSports('nfl_player_profile', $nflPlayer->Id))){
               echo "Unable to create CBS Sports object for player profile data";
           }
           if(!($curlPlayerProfile = $cbsSportsPlayerProfile->GetData())){
               echo "Unable to retrieve player profile data from CBS Sports";
           }
           $nflPlayer->loadFfTeamByJson($curlPlayerProfile->body->player_profile->player); 
           echo "<p>NFL Player Object<br>";
           print_r($nflPlayer);
           echo "</p>";  
   } 

      
}*/


echo "</html>";

$database->conn->close();

?>