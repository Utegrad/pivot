<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');
require_once('Classes/NflTeam.php');

if(!($cbsSports = new CBSSports('nfl_team'))){
	echo "Unable to create CBS Sports Object";
}
$database = new Database();
$helpers = new Helpers();
$nflTeams = $cbsSports->GetData();

$nflTeamAbbrs = array();

$selectNflTeamAbbrs = "SELECT abvr FROM nfl_teams";
$nflTeamAbbrs = Data::SelectFetchAll($selectNflTeamAbbrs, $database->conn);
if(empty($nflTeamAbbrs)&& ($nflTeamAbbrs!== FALSE)){
	// INFO : no teams found.  
}

echo "<html>";

// loop through Data
foreach($nflTeams->body->pro_teams as $team){
	$insertStatus = 'No';
	// populate the NflTeam object
	$nflTeam = NflTeam::withJson($team);
	// check to see if they are already in the database by comparing to $nflTeamAbbrs
	if(!($helpers->searchIfExists($nflTeam->Abvr,$nflTeamAbbrs))){
		// insert the team if not in the database
		$insertTeamQuery = "INSERT INTO nfl_teams (abvr, name, nick_name) VALUES(?, ?, ?)";
		$utilData = Data::WithValues($database->conn,$insertTeamQuery,'sss',
			array($nflTeam->Abvr, $nflTeam->Name, $nflTeam->NickName));
		$utilData->bindParameters();
		if(!($utilData->stmt->execute())){
					echo "Execute failed: (".$utilData->stmt->errno.") ".$utilData->stmt->error;
					exit();
				}
				else{
					$insertStatus = 'Yes';
				}
		$utilData->stmt->close();
	}
	else{
		// set indicator for not inserted if already in the database
		echo "<p>Team: ". $nflTeam->Name ." already in the database</p>";
	}
	
	// echo out all the owner data 
	echo "<p>Name: ". $nflTeam->Name ."<br>";
	echo "NickName: ". $nflTeam->NickName ."<br>";
	echo "Abbr: ". $nflTeam->Abvr ."<br>";
	echo "Inserted to the database: $insertStatus </p>";
	
}

echo "</html>";
$database->conn->close();
?>