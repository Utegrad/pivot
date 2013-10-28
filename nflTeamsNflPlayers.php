<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');
require_once('Classes/NflPlayer.php');

if(!($cbsSportsNflPlayers = new CBSSports('nfl_player'))){
	echo "Unable to create CBS Sports Object";
}
$database = new Database();
$helpers = new Helpers();
$curlNflPlayers = $cbsSportsNflPlayers->GetData();


$dbNflPlayerIds= array();

$selectNflPlayers = "SELECT nfl_players.id FROM nfl_players";
$dbNflPlayerIds = Data::SelectFetchAll($selectNflPlayers, $database->conn);
if(empty($dbNflPlayerIds)&& ($dbNflPlayerIds!== FALSE)){
	// INFO : no players found.  
}

echo "<html>";

// loop through Data
foreach($curlNflPlayers->body->players as $player){
	$insertStatus = 'No';
	// populate the NflPlayer object
	$nflPlayer = NFLPlayer::withJson($player);
	// check to see if they are already in the database by comparing to $dbNflPlayerIds
	if(!($helpers->searchIfExists($nflPlayer->Id,$dbNflPlayerIds))){
		// insert the team if not in the database
		if(in_array($nflPlayer->Pos, $nflPlayer->Position->relivantPositions)){
			// and the player has a relivant position insert the player
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
		} // end if(!($helpers->searchIfExists($nflPlayer->Id,$dbNflPlayerIds)))
	} //end if(!($helpers->searchIfExists($nflPlayer->Id,$dbNflPlayerIds)))
	else{
		// set indicator for not inserted if already in the database
		# echo "<p>Team: ". $nflTeam->Name ." already in the database</p>";
	}
	// populate insert values to nfl_players_nfl_teams table
	// populate the NflPlayer::NflTeam values
	// lookup the nfl_team values from $nflPlayer object properties
	if(in_array($nflPlayer->Pos, $nflPlayer->Position->relivantPositions)){
		$selectNflTeamQuery = "SELECT nfl_teams.id, nfl_teams.name, nfl_teams.nick_name
			FROM nfl_teams WHERE nfl_teams.abvr = ?";
		/* TODO create a $utilData object if I don't already have one because the player wasn't inserted in the database */
		if(!(isset($utilData))){
			$utilData = Data::WithDbConn($database->conn);
		}
		$utilData->updateStmt($selectNflTeamQuery,'s', array($nflPlayer->NFLTeam->Abvr));
		$utilData->bindParameters();
		if(!($utilData->stmt->execute())){
			echo "Execute failed: (".$utilData->stmt->errno.") ".$utilData->stmt->error;
			$utilData->GetErrorMsgs();
			exit();
		}
		$utilData->stmt->bind_result($nflPlayer->NFLTeam->TeamID, 
			$nflPlayer->NFLTeam->Name, $nflPlayer->NFLTeam->NickName);
		if($utilData->stmt->fetch() === NULL){
			echo "<p>Nothing fetched: (". $utilData->stmt->errno .") ". $utilData->stmt->error ."</p>";
		}
		$utilData->stmt->close();
		
		// insert the nfl_team to nfl_player record
		$insertNflPlayerNflTeam = "INSERT INTO nfl_players_nfl_teams (nfl_team_id, nfl_player_id, modified)
			VALUES(?, ?, ?)";
		$utilData->updateStmt($insertNflPlayerNflTeam,'iis',array ($nflPlayer->NFLTeam->TeamID,
			$nflPlayer->Id, date('Y-m-d H:i:s')));
		$utilData->bindParameters();
		if(!($utilData->stmt->execute())){
			echo "Execute failed: (".$utilData->stmt->errno.") ".$utilData->stmt->error;
			exit();
		}
		$utilData->stmt->close();
		
		echo "<p>Team Name: ". $nflPlayer->NFLTeam->Name ."<br>";
		echo "Player Name: ". $nflPlayer->FullName ."<br>";
		echo "Team ID: ". $nflPlayer->NFLTeam->TeamID ."<br>";
		echo "Player ID:". $nflPlayer->Id ."<br>";
		echo "Inserted to the database: $insertStatus </p>";	
	}
	
	
}

echo "</html>";
$database->conn->close();
?>