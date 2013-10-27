<?php
require_once('ffClasses.php');
$ffApp = new TestApp();
$ffApp->Element = "players/list";
$ffApp->GetURL = $ffApp->BaseURL . $ffApp->Element . $ffApp->URLSuffix;

$crl = curl_init();
$timeout = 30;

curl_setopt($crl, CURLOPT_URL, $ffApp->GetURL);
curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout );
$ret = curl_exec($crl);
curl_close($crl);
$data = json_decode($ret);
$players = $data->body->players;

#$mysqli = new mysqli("localhost","FFApplication", "keep0ut!", "ffstats");
$mysqli = new mysqli($ffApp->database['host'], $ffApp->database['username'], $ffApp->database['password'], $ffApp->database['database']);
if($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (". $mysqli->connect_errno .") ". $mysqli->connect_error;
}

// get all player IDs from database and save them in $playerIds
$playerIds = array();
$selectPlayerIdQuery = "SELECT nfl_player.NFL_PlayerId FROM nfl_player";
if(!($result = $mysqli->query($selectPlayerIdQuery))){
	echo "Query failed: (". $mysqli->errno .") ". $mysqli->error;
	exit();
}
$playerIds = $result->fetch_all(MYSQLI_NUM);
$result->close();
function searchIfExists($array, $searchValue){
	$exists = FALSE;
	foreach($array as $value){
		if($value[0] == $searchValue){
			$exists = TRUE;
		}
	}
	return $exists;
}

$relivantPositions = array("QB", "RB", "WR", "TE", "DST", "K");

echo "<html><table>";
echo "<tr><td>Player ID</td><td>Full Name</td><td>Pos ID</td><td>Bye Week</td><td>Inserted</td></tr>";

foreach ($players as $player){
	$insertStatus = "No";
	// create a new nfl_player object
	$nflPlayer = new NFL_Player();
	$nflPlayer->FullName = $player->fullname;
	$nflPlayer->FirstName = $player->firstname;
	$nflPlayer->LastName = $player->lastname;
	$nflPlayer->Id = $player->id;
	$nflPlayer->Pos = $player->position;
	
	$nflPlayer->ByeWeek = ( (empty($player->bye_week)) ? 0 : $player->bye_week );
	$nflPlayer->NFLTeam->Abvr = $player->pro_team;
	$nflPlayer->Status = $player->pro_status;
	
	// get the player's pos ID
	
	
	// if player ID is NOT in the database, insert the player
	if(!(searchIfExists($playerIds,$nflPlayer->Id)))
	{
		// insert the relivant values from $nflPlayer
		if(in_array($nflPlayer->Pos, $relivantPositions))
		{
			$insertPlayerQuery = "INSERT INTO nfl_player 
	(NFL_PlayerId, NFL_PlayerFirstName, NFL_PlayerLastName, NFL_PlayerFullName,
	NFL_PlayerByeWeek, FK_FF_PositionId)
	VALUES(?, ?, ?, ?, ?,(SELECT ff_position.FF_PositionID FROM ff_position
			WHERE ff_position.FF_PositionAbvr = ?)
		 )";
		if(!($stmtPlayer = $mysqli->prepare($insertPlayerQuery)))
		{
			echo "Prepare failed: (". $mysqli->errno .") ". $mysqli->error;
			exit();
		}
		if(!($stmtPlayer->bind_param('isssis', $paramPlayerId, $paramFirstName, $paramLastName, $paramFullName, $paramByeWeek, $paramPosAbvr)))
		{
			echo "Bind failed: (". $stmtPlayer->errno .") ". $stmtPlayer->error;
			exit();
		}
		$paramPlayerId = $nflPlayer->Id;
		$paramFirstName = $nflPlayer->FirstName;
		$paramLastName = $nflPlayer->LastName;
		$paramFullName = $nflPlayer->FullName;
		$paramByeWeek = $nflPlayer->ByeWeek;
		$paramPosAbvr = $nflPlayer->Pos;
		if(!($stmtPlayer->execute()))
		{
			echo "Execute failed: (". $stmtPlayer->errno .") ". $stmtPlayer->error;
			echo "<p>";
			print_r($nflPlayer);
			echo "</p>";
			echo "<p>";
			print_r($playerIds);
			echo "</p>";
			exit();
		}
		else{
			$insertStatus = "Yes";
		}
		$stmtPlayer->close();
		
				
		}
	}// if player ID is in the database skip it (for now)
	
	
	echo "<tr><td>". $player->id ."</td><td>". $player->fullname ."</td><td>". $player->position ."</td><td>". $player->bye_week ."</td>";
	
	echo "<td>$insertStatus</td></tr>";
}




echo "</table>";

#print_r($playerIds);

echo "</html>";
$mysqli->close();
?>