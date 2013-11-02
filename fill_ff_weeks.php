<?php
require_once('Classes/Config.php');
require_once('Classes/Utility.php');

// get league dates from CBS Sports
if(!($cbsSportsLeagueDates = new CBSSports('league/dates'))){
	echo "Problem creating CBS Sports object for League Dates.";
}
if(!($curlLeagueDates = $cbsSportsLeagueDates->GetData())){
	echo "Problem getting League Date data from CBS Sports\nError Message:\n";
	print_r($cbsSportsLeagueDates->ErrorMessage);
}

if(!($cbsSportsLeagueDetails = new CBSSports('league/details'))){
	echo "Problem creating creating CBS Sports object for League details.";
}
if(!($curlLeagueDetails = $cbsSportsLeagueDetails->GetData())){
	echo "Problem getting League details from CBS Sports\nError Message:\n";
	print_r($cbsSportsLeagueDetails->ErrorMessage);
}

$seasonWeeks = $curlLeagueDetails->body->league_details->scoring_periods;

$startDate = new DateTime($curlLeagueDates->body->dates->season_start);
$weekStartDays = array();

$database = new Database();
$helpers = new Helpers();

$insertWeeks = "INSERT INTO ff_weeks ( number, start_date, ff_season_id )";
for ($i = 0; $i < $seasonWeeks; $i++) {
	$w = $i+1;
	if ($i == 0) {
		$insertWeeks .= "SELECT '$w', '". $startDate->format('Y-m-d H:i:s') ."' , '2013'";
		// array_push($weekStartDays, $startDate);
	}
	else{
		$nextDate = clone $startDate;
		$m = 'P'. 7 * $i .'D';
		$nextDate->add(new DateInterval($m));
		$insertWeeks .= "UNION ALL SELECT '$w', '". $nextDate->format('Y-m-d H:i:s') ."' , '2013'";
	}
}

print_r($insertWeeks);

if($database->conn->query($insertWeeks)){
	printf("\n%d row(s) inserted.\n",$database->conn->affected_rows);
}
else{
	echo "\nFailed to insert data: (". $database->conn->errno .") ". $database->conn->error ."\n";
}


$database->conn->close();

?>