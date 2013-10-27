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

$playerStatus = array();

$mysqli = new mysqli("localhost","FFApplication", "keep0ut!", "ffstats");
if($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (". $mysqli->connect_errno .") ". $mysqli->connect_error;
}

echo "<html><table>";
echo "<tr><td>Player ID</td><td>Full Name</td><td>Pos ID</td><td>Bye Week</td></tr>";

foreach ($players as $player){
	
	
}



echo "</table>";


echo "</html>";
$mysqli->close();
?>