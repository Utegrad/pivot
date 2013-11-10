<?php
class NFL_Team {
	function __construct() {
	
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

# $access_token = $_GET['access_token'];
$access_token = "U2FsdGVkX1_fPq3nW79D6JXMISH39UkPd5fxM2U__Wosz7hZrT7HlZBgUe8rjPiEyu3AcUF53NLuOf8vMHaSppWRzwNSCZ3dIwKWIoCUPfynJvDscEQaA3m3weq4TNSe";

$crl = curl_init();
$timeout = 30;
# curl_setopt($crl, CURLOPT_URL,
#   "http://api.cbssports.com/fantasy/league/teams?access_token=$access_token&response_format=json");
curl_setopt($crl, CURLOPT_URL,
   "http://api.cbssports.com/fantasy/players/list?version=2.0&access_token=$access_token&response_format=json");

curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout );
$ret = curl_exec($crl);
curl_close($crl);
$data = json_decode($ret);
echo "<html><table>";
$players = $data->body->players;
echo "<tr><td>Full name</td><td>Position</td><td>Pro Team</td><td>Player ID</td></tr>";
foreach($players as $player)
{
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