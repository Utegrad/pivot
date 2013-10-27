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

class FF_Owner {
	function __construct() {
		
	}
	public function echoOwner(){
	echo "<tr><td>".$this->Id."</td><td>".$this->Name."</td></tr>";
	}

	public $Id;
	public $Name;
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
   "http://api.cbssports.com/fantasy/league/owners?version=2.0&access_token=$access_token&response_format=json");
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
$insertQuery = "INSERT INTO ff_owner (FF_OwnerID, FF_OwnerName)VALUES(?,?)";
$stmt = $mysqli->prepare($insertQuery);
if(!$stmt)
{
	echo "Prepare failed: (". $mysqli->errno .") ". $mysqli->error;
	exit();
}
$stmt->bind_param('ss',$id,$name);


echo "<html><table>";
$owners = $data->body->owners;
echo "<tr><td>ID</td><td>Name</td></tr>";
foreach($owners as $owner)
{
	$ff_owner = new FF_Owner();
	$ff_owner->Id = $owner->id;
	$ff_owner->Name = $owner->name;
	
	$name = $owner->name;
	$id = $owner->id;
	
	$stmt->execute() or die("Execute failed: (". $stmt->errno .") ". $stmt->error);
	
	$ff_owner->echoOwner();
	
	
	
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