<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');
require_once('Classes/NflPlayer.php');

if(!($cbsSports = new CBSSports('nfl_player'))){
	echo "Unable to create CBS Sports Object";
}
$database = new Database();
$helpers = new Helpers();
$players = $cbsSports->GetData();

$playerIds = array();

$selectPlayerIdQuery = "SELECT id FROM nfl_players";
if(!($result = $database->conn->query($selectPlayerIdQuery))){
	echo "Query failed: (". $database->conn->errno .") ". $database->conn->error;
	exit();
}
$playerIds = $result->fetch_all(MYSQLI_NUM);
$result->close();

echo "<html><table>";
echo "<tr><td>Player ID</td><td>Full Name</td><td>Pos ID</td><td>Bye Week</td><td>Inserted</td></tr>";

foreach ($players->body as $players){
	foreach($players as $player){
		
		$insertStatus = "No";
		
		// create a new nfl_player object
		
		$nflPlayer = NFLPlayer::withJson($player);
		
		/*
		// select the player's pos ID from database 
		// not all players will return a result - they should cause the loop to skip to the next value
		$selectPosID = "SELECT id from ff_positions WHERE abvr = ?";
		$idResult;
		$utilData = Data::WithValues($database->conn,$selectPosID,'s', array($nflPlayer->Pos));
		$utilData->bindParameters();
		$utilData->stmt->execute();
		$utilData->stmt->bind_result($idResult);
		$fetchResult = $utilData->stmt->fetch();
		if($fetchResult === NULL){
			$utilData->stmt->close();
			continue;
		}
		if($fetchResult === FALSE){
			echo "<p>Error with fetch action: (". $utilData->stmt->errno .") ". $utilData->stmt-error ."</p>";
			$utilData->stmt->close();
			continue;
		}
		$utilData->stmt->close();*/
		
		// if player ID is NOT in the database, insert the player
		if(!($helpers->searchIfExists($nflPlayer->Id,$playerIds)))
		{
			// insert the relivant (having a relivant position) values from $nflPlayer
			if(in_array($nflPlayer->Pos, $nflPlayer->Position->relivantPositions))
			{
				$insertPlayerQuery = "INSERT INTO nfl_players 
					(id, first_name, last_name, full_name,
					bye_week, ff_positions_id )
					VALUES(?, ?, ?, ?, ?,(SELECT id FROM ff_positions
							WHERE abvr = ?) )";
				$utilData = Data::WithValues($database->conn,$insertPlayerQuery,'isssis',
					array($nflPlayer->Id, $nflPlayer->FirstName, $nflPlayer->LastName,
					$nflPlayer->FullName,$nflPlayer->ByeWeek, $nflPlayer->Pos) );
				$utilData->bindParameters();
				if(!($utilData->stmt->execute())){
					echo "Execute failed: (".$utilData->stmt->errno.") ".$utilData->stmt->error;
					$utilData->GetErrorMsgs();
					exit();
				}
				else{
					$insertStatus = 'Yes';
				}
				$utilData->stmt->close();
				
			} // end if(in_array($nflPlayer->Pos, $nflPlayer->Position->relivantPositions))
		}// end if(!($helpers->searchIfExists($nflPlayer->Id,$playerIds))) 
		// if player ID is in the database skip it (for now)
		else{
			$insertStatus = "N/A";
		}
		
		
		echo "<tr><td>". $player->id ."</td><td>". $player->fullname ."</td><td>". $nflPlayer->Pos ."</td><td>". $player->bye_week ."</td>";
		
		echo "<td>$insertStatus</td></tr>";
	} // end foreach($players as $player)
} // end foreach ($players->body as $players)



echo "</table>";

echo "</html>";
$database->conn->close();


?>