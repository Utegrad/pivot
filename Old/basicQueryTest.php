<?php
$mysqli = new mysqli("localhost","FFApplication", "keep0ut!", "ffstats");
if($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (". $mysqli->connect_errno .") ". $mysqli->connect_error;
}
$selectQuery = "SELECT FF_StatsGroupId FROM ff_statsgroup WHERE FF_StatsGroupName = ?";
$stmt = $mysqli->prepare($selectQuery);
if(!$stmt)
{
	echo "Prepare failed: (". $mysqli->errno .") ". $mysqli->error;
	exit();
}
$stmt->bind_param('s',$paramName);
$paramName = 'Offensive';

$stmt->execute() or die("Execute failed: (". $stmt->errno .") ". $stmt->error);
$stmt->bind_result($groupId);
$stmt->fetch();

echo "<p>$groupId</p>";



$stmt->close();
$mysqli->close();

?>