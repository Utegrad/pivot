<?php
require_once('ffClasses.php');

# $access_token = $_GET['access_token'];
#$access_token = "U2FsdGVkX1_fPq3nW79D6JXMISH39UkPd5fxM2U__Wosz7hZrT7HlZBgUe8rjPiEyu3AcUF53NLuOf8vMHaSppWRzwNSCZ3dIwKWIoCUPfynJvDscEQaA3m3weq4TNSe";
$ffApp = new TestApp();
$ffApp->Element = "positions";
$ffApp->GetURL = $ffApp->BaseURL . $ffApp->Element . $ffApp->URLSuffix;



$crl = curl_init();
$timeout = 30;

curl_setopt($crl, CURLOPT_URL,
   "http://api.cbssports.com/fantasy/positions?version=2.0&access_token=".$ffApp->AccessToken."&response_format=json");
curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout );
$ret = curl_exec($crl);
curl_close($crl);
$data = json_decode($ret);

$mysqli = new mysqli("localhost","FFApplication", "keep0ut!", "ffstats");
if($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (". $mysqli->connect_errno .") ". $mysqli->connect_error;
}

$positions = $data->body->positions;
echo "<html><table>";
echo "<tr><td>Stats Group</td><td>Abbr</td><td>Pos Name</td><td>Desc</td></tr>";
$posGroups = array();

foreach($positions as $position)
{
	$ff_position = new FF_Position();
	$ff_position->Abbr = $position->abbr;
	$ff_position->Desc = $position->description;
	$ff_position->Name = $position->name;
	$ff_position->PositionGroup->FF_StatsGroupName = $position->stats_group;
	
	echo "<tr><td>".$ff_position->PositionGroup->FF_StatsGroupName."</td><td>".$ff_position->Abbr."
	</td><td>".$ff_position->Name."</td><td>".$ff_position->Desc."</td></tr>";
	
	// Get StatsGroupID 
	$selectGroupId = "SELECT ff_statsgroup.FF_StatsGroupId FROM ff_statsgroup WHERE FF_StatsGroupName = ?";
	if(!($stmtSelGroupId = $mysqli->prepare($selectGroupId))){
		echo "Prepare failed: (". $mysqli->errno .") ". $mysqli->error;
		exit();
	}
	if(!($stmtSelGroupId->bind_param('s',$paramGroupName))){
		echo "Bind failed: (".$stmtSelGroupId->errno.") ".$stmtSelGroupId->error;
		exit();
	}
	$paramGroupName = $ff_position->PositionGroup->FF_StatsGroupName;
	$groupId = null;
	if(!($stmtSelGroupId->execute())){
		echo "Execute failed: (". $stmtSelGroupId->errno .") ". $stmtSelGroupId->error;
		exit();
	} 
	if(!($stmtSelGroupId->bind_result($groupId))){
		echo "Bind Failed: (".$stmtSelGroupId->errno.") ".$stmtSelGroupId->error;
		exit();
	}
	if(!($stmtSelGroupId->fetch())){
		echo "Fetch failed: (".$stmtSelGroupId->errno.") ".$stmtSelGroupId->error;
	}
	$ff_position->PositionGroup->FF_StatsGroupId = $groupId;
	$stmtSelGroupId->close();
	// end Get StatsGroupID
	
	if(!(in_array($ff_position->PositionGroup->FF_StatsGroupName,$posGroups))){
		array_push($posGroups,$ff_position->PositionGroup->FF_StatsGroupName);
	}
	
	// Insert Positions
	$insertPositions = "INSERT INTO FF_Position (FF_PositionAbvr, FF_PositionName,FF_StatsGroupId)VALUES(?,?,?)";
	if(!($stmtPosition = $mysqli->prepare($insertPositions))){
		echo "Prepare failed: (". $mysqli->errno .") ". $mysqli->error;
		exit();
	}
	if(!($stmtPosition->bind_param('ssi',$paramPosAbvr,$paramPosName,$paramStatsGroupId))){
		echo "Bind failed: (".$stmtPosition->errno.") ".$stmtPosition->error;
		exit();
	}
	$paramPosAbvr = $ff_position->Abbr;
	$paramPosName = $ff_position->Name;
	$paramStatsGroupId = $ff_position->PositionGroup->FF_StatsGroupId;
	if(!($stmtPosition->execute())){
		echo "Execute failed: (".$stmtPosition->errno.") ".$stmtPosition->error;
		exit();
	}
	$stmtPosition->close();
	// end Insert Positions
}

echo "</table>";

$mysqli->close();

echo "</html>";
?>