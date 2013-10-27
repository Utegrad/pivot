<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');
require_once('Classes/FfPosition.php');

if(!($cbsSports = new CBSSports('ff_position'))){
	echo "Unable to create CBS Sports Object";
}
$database = new Database();
$helpers = new Helpers();
#printf("<p>URL: %s</p>",$cbsSports->GetURL);
$positions = $cbsSports->GetData();

$positionAbvrs = array();

$selectPosAbvrQuery = "SELECT abvr FROM ff_positions";
if(!($positionAbvrs = Data::SelectFetchAll($selectPosAbvrQuery, $database->conn))){
	printf("<p>Error getting positions IDs:<br>%s</p>",print_r($positionAbvrs));
}


echo "<html>";

#echo "<table><tr><td>Pos Name</td><td>Pos Abvr</td><td>Pos Desc</td><td>Stats Group</td><td>Inserted</td></tr>";

foreach($positions->body->positions as $_positions){
	#foreach($_positions as $position){
	#	$insertStatus = 'No';
		$insertStatus = 'No';
		
		// create new FFPostion object
		
		$ffPos = FfPosition::withJson($_positions);
		
		#if(!($helpers->searchIfExists($nflPlayer->Id,$playerIds)))
		if(!($helpers->searchIfExists($ffPos->Abbr,$positionAbvrs))) // $ffPos->Abbr from JSON & $positionsAbvrs from database
		{
			echo "<p>". $ffPos->Abbr ." not in the database</p>";
			// insert the relivant values from $ffPos
			if(in_array($ffPos->Abbr, $ffPos->relivantPositions)){
				// insert $ffPos values for relivant Positions
				$insertPosQuery = "INSERT INTO ff_positions 
					(abvr, ff_stats_group_id, name)
					 VALUES(?, 
					 (SELECT id FROM ff_stats_groups WHERE name = ?),
					  ?)";
				$utilData = Data::WithValues($database->conn,$insertPosQuery,'sss',
					array ( $ffPos->Abbr,
						$ffPos->PositionGroup->FF_StatsGroupName,
						$ffPos->Name ));
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
		
		} // end if(!($helpers->searchIfExists($nflPlayer->Id,$playerIds)))
		else{
			echo "<p>". $ffPos->Abbr ." already in the database.</p>";
		}
		/*echo "<p>Current _position:</p><p>";
		print_r($ffPos);
		echo "</p><p>----</p>";
		*/
		echo "<p>Pos Name: ". $ffPos->Name ."</p>";
		echo "<p>Pos Abvr: ". $ffPos->Abbr ."</p>";
		echo "<p>Pos Desc: ". $ffPos->Desc ."</p>";
		echo "<p>Pos Group Name: ". $ffPos->PositionGroup->FF_StatsGroupName . "</p>";
		echo "<p>Inserted: ". $insertStatus ."</p>";
		echo "<p>----</p>";	
#		echo "<tr><td>". $ffPos->Name ."</td><td>". $ffPos->Abbr ."</td><td>". $ffPos-Desc ."</td><td>". $ffPos->PositionGroup->FF_StatsGroupName ."</td>";
		
#		echo "<td>$insertStatus</td></tr>";
		
	#} // end foreach($_positions as $position)
} // end foreach($positions->body as $_positions)


echo "</table>";

echo "</html>";
$database->conn->close();
?>