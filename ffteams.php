<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');
require_once('Classes/FfTeam.php');

if(!($cbsSports = new CBSSports('ff_team'))){
	echo "Unable to create CBS Sports Object";
}
$database = new Database();
$helpers = new Helpers();
$teams = $cbsSports->GetData();

$teamIds = array();

$selectTeamIds = "SELECT id FROM ff_teams";
$teamIds = Data::SelectFetchAll($selectTeamIds, $database->conn);
if(empty($teamIds)&& ($teamIds !== FALSE)){
	// INFO : no owners found.  
}

echo "<html>";

// loop through Data
foreach($teams->body->teams as $team){
	$insertStatus = 'No';
	// populate the FfTeam object
	$ffTeam = FfTeam::withJson($team);
	/*echo "Team Name: ". $team->name ."</p>";
	echo "<p>Owner ID: ". $team->owners[0]->id ."</p>";
	echo "<p>Owner Name: ". $team->owners[0]->name . "</p>";*/
	// check to see if they are already in the database by comparing to $teamIds
	if(!($helpers->searchIfExists($ffTeam->Id,$teamIds))){
		// insert the team if not in the database
		$insertTeamQuery = "INSERT INTO ff_teams (id, abbr, long_abbr, name, short_name, logo, ff_owner_id)
		VALUES(?, ?, ?, ?, ?, ?, ?)";
		$utilData = Data::WithValues($database->conn,$insertTeamQuery,'issssss',
			array($ffTeam->Id, $ffTeam->Abbr, $ffTeam->LongAbbr, 
			$ffTeam->Name, $ffTeam->ShortName, $ffTeam->Logo,
			$team->owners[0]->id) );
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
		echo "<p>Team". $ffTeam->Name ." already in the database</p>";
	}
	
	// echo out all the owner data 
	echo "<p>Name: ". $ffTeam->Name ."<br>";
	echo "ID: ". $ffTeam->Id ."<br>";
	echo "Inserted to the database: $insertStatus";
	
}

echo "</html>";
$database->conn->close();
?>