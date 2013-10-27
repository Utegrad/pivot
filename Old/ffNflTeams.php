<?php
class NFL_Team {
	function __construct() {
	
	}
	function printTeam()
	{
		echo "<tr><td>".$this->Name."</td><td>".$this->NickName."</td><td>".$this->Abvr."</td></tr>";	
	}
	
	public $TeamID;
	public $Name;
	public $NickName;
	public $Abvr;
}

function echoPlayer($player)
{
	$name = $player->fullname;
	$pos = $player->position;
	$pro_team = $player->pro_team;
	echo "<tr><td>$name</td><td>$pos</td><td>$pro_team</td><td>$player->id</tr>";
}

function echoTeam($team)
{
	$name = $team->name;
	$nickname = $team->nickname;
	$abvr = $team->abbr;
	echo "<tr><td>$name</td><td>$nickname</td><td>$abvr</td></tr>";
	
}

# $access_token = $_GET['access_token'];
$access_token = "U2FsdGVkX1_fPq3nW79D6JXMISH39UkPd5fxM2U__Wosz7hZrT7HlZBgUe8rjPiEyu3AcUF53NLuOf8vMHaSppWRzwNSCZ3dIwKWIoCUPfynJvDscEQaA3m3weq4TNSe";

$crl = curl_init();
$timeout = 30;

curl_setopt($crl, CURLOPT_URL,
   "http://api.cbssports.com/fantasy/pro-teams?version=2.0&access_token=$access_token&response_format=json");
curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout );
$ret = curl_exec($crl);
curl_close($crl);
$data = json_decode($ret);

$mysqli = new mysqli("localhost","FFApplication", "keep0ut!", "ffstats");
if($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (". $mysqli->connect_errno .") ". $mysqli->connect_error;
}
# $stmt = $mysqli->stmt_init();
$stmt = $mysqli->prepare("INSERT INTO nfl_team (NFL_TeamName,NFL_TeamAbvr,NFL_TeamNickname)
  		VALUES(?,?,?)");
if(!$stmt)
{
	echo "Prepare failed: (". $mysqli->errno .") ". $mysqli->error;
	exit();
}
$stmt->bind_param('sss',$name,$abvr,$nickname);


echo "<html><table>";
$teams = $data->body->pro_teams;
echo "<tr><td>Name</td><td>Nickname</td><td>Abrv</td></tr>";
foreach($teams as $team)
{
	$nfl_team = new NFL_Team();
	$nfl_team->Abvr = $team->abbr;
	$nfl_team->Name = $team->name;
	$nfl_team->NickName = $team->nickname;
	
	$name = $team->name;
	$abvr = $team->abbr;
	$nickname = $team->nickname;
	
	$stmt->execute() or die("Execute failed: (". $stmt->errno .") ". $stmt->error);
	
	
	/*
	if(!($stmt->bind_param(':abvr',$nfl_team->Abvr))){
		echo "Binding parameter failed: (". $stmt->errno .") ". $stmt->error;
	}
	if(!($stmt->bind_param(':nickname',$nfl_team->NickName))){
		echo "Binding parameter failed: (". $stmt->errno .") ". $stmt->error;
	}*/
	
	$nfl_team->printTeam();
	
	
	
	#echoTeam($team);
	/*
	switch ($player->position){
		case "DST":
			echoPlayer($player);
			break;
		case "RB":
			echoPlayer($player);
			break;
		case "WR":
			echoPlayer($player);
			break;
		case "QB":
			echoPlayer($player);
			break;
		case "TE":
			echoPlayer($player);
			break;
		case "K":
			echoPlayer($player);
			break;
	}
	*/
}



/*
$teams = $data->body->teams;
foreach ( $teams as $team ) {
    $name = $team->name;
    $logo = $team->logo;
    echo "<tr><td><img src='$logo'></td><td>$name</td>";
    $owners = $team->owners;
    foreach ( $owners as $owner ) {
        $ownname = $owner->name;
        echo "<td>$ownname</td>";
    }
    echo "</tr>";
}
*/
echo "</table></html>";
?>