<?php
require 'bootstrap.php';
$database = new Database();

// Gather page data to display
require APP_ROOT .'Classes/Utility.php';
require_once APP_ROOT . 'Classes/NflPlayer.php';
require_once APP_ROOT . 'Classes/FfOwner.php';
require_once APP_ROOT . 'Classes/FfPosition.php';
require_once APP_ROOT . 'Classes/FfTeam.php';
require_once APP_ROOT . 'Classes/FfLeague.php';
require_once APP_ROOT . 'Control/AvgScoreData.php';
require_once 'Cache/Lite.php';

$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database or from _GET</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}

$App->StyleSheets = array(
		'Default' => 'View/CSS/default.css',
		'DataTable' => 'View/CSS/DataTable.css',
		'Forms' => 'View/CSS/Forms.css',
);

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * @todo Try and speed this up by creating an object and reusing the curl handle
 */

$cbsSports = new CBSSports(CBSSports::NFL_PLAYER);
$nflPlayerData = $cbsSports->GetData();

if($cbsSports->UpdateURL(CBSSports::FPSWS, 'player_status=all')){ $nflPlayerWeeklyScoring = $cbsSports->GetData(); }
else{ array_push($cbsSports->ErrorMessage, "Problem updating GetURL with ". CBSSports::FPSWS ." to fetch new data"); }

if($cbsSports->UpdateURL(CBSSports::ROSTERS, 'all')){ $ffTeamRosters = $cbsSports->GetData(TRUE); }
else{ array_push($cbsSports->ErrorMessage, "Problem updating GetURL with ". CBSSports::ROSTERS ." to fetch new data"); }

$playerSummarys = array();

foreach($nflPlayerData->body->players as $player){
	// create NFLPlayer object
	$p = NFLPlayer::withJson($player);
	$periods = array();
	$scores = array();
	// find players fantasy team from roster data
	foreach($ffTeamRosters->body->rosters->teams as $team){
		foreach ($team->players as $member){
			if ($p->Id == $member->id) {
				$p->FfTeam->Name = $team->name;
				$p->FfTeam->Logo = $team->logo;
				$p->FfTeam->Abbr = $team->abbr;
				$p->FfTeam->ShortName = $team->short_name;
			}
		}
	}
	
	if (empty($p->FfTeam->Name)) {
		$p->FfTeam->Name = 'Free Agent';
	}
	
	// populate weekly scoring data
	foreach ($nflPlayerWeeklyScoring->body->weekly_scoring->players as $plyr){
		if($p->Id == $plyr->id){
			// save periods[] for this player
			foreach($plyr->periods as $period){
				array_push($periods, array('period' => $period->period, 'score' => $period->score));
				array_push($scores, $period->score);
			}
			
		}
	}
	
	// set player stats
	if (!(empty($scores)) && (count($scores) != 0) ) {
		$playerAvg = (array_sum($scores) / count($scores));
		$sampleStdDev = Helpers::sd($scores);
		$playerMax = max($scores);
		$playerMin = ceil($sampleStdDev);
	}
	else{
		$playerAvg = 0;
		$sampleStdDev = 0;
		$playerMax = 0;
		$playerMin = 0;
	}
	
	// set indv player summary array
	$playerSummary = array(
		'id' => $p->Id,
		'fullName' => $p->FullName,
		'pos' => $p->Pos,
		'relivantPositions' => $p->Position->relivantPositions,
		'nflTeam' => $p->NFLTeam,
		'bye' => $p->ByeWeek,
		'fTeam' => $p->FfTeam,
		'playerAvg' => $playerAvg,
		'playerStdDev' => $sampleStdDev,
		'playerMax' => $playerMax,
		'playerMin' => $playerMin,
		'Rating' => 'Need to calc this',
		'scores' => $scores,
	);
	
	array_push($playerSummarys, $playerSummary);
	
}

$populationScores = array();

// populate population scores array
foreach($playerSummarys as $summary){
	if (in_array($summary['pos'], $summary['relivantPositions'] )) {
		// $populationScores = array_merge($populationScores, $summary['scores']);
		foreach($summary['scores'] as $score){
			array_push($populationScores, $score);
		}
	}
}

// calculate populate statistics
if(!(empty($populationScores)) && ( count($populationScores) != 0 )){
	$populationAvg = (array_sum($populationScores) / count($populationScores));
	$populationStdDev = Helpers::sd($populationScores);
}
else{
	$populationAvg = 0;
	$populationStdDev = 0;
}

$populationMax = max($populationScores);
$populationMin = min($populationScores);

// find max average of all players
$populationMaxAvg = 0;

foreach($playerSummarys as $summary){
	$populationMaxAvg = ($summary['playerAvg'] > $populationMaxAvg ? $summary['playerAvg'] : $populationMaxAvg);
}

// set player rating and scale value
foreach ($playerSummarys as &$summary){
	$result = Helpers::setPlayerRatingScale($populationAvg, $populationMaxAvg, $populationStdDev, $summary);
	if($result == FALSE){
		$summary['rating'] = 'Unavailble';
		$summary['scale'] = 0;
	}
}

// sort player summaries by player avg score
$averages = array();
foreach($playerSummarys as $key => $row){
	$averages[$key] = $row['playerAvg'];
}
array_multisort($averages, SORT_DESC, $playerSummarys);


define('CURR_DIR', dirname(__FILE__) . DS);

$presentation = array('main' => CURR_DIR . 'main.php', );

require_once APP_ROOT .'page.php';

/* echo "<p>";
foreach ($playerSummarys as $summary){
	if((in_array($summary['pos'], $summary['relivantPositions']))){
		$avg = round($summary['playerAvg'],2);
		echo $summary['fullName'] .":";
		echo "<ul>
				<li>Avg: ". $avg ."</li>";
		echo "<li>Max: ". $summary['playerMax'] ."</li>";
		echo "<li>Min: ". $summary['playerMin'] ."</li>";
		echo "<li>Rating: ". $summary['rating'] ."</li>";
		echo "<li>Scale: ". $summary['scale'] ."</li>";
		echo "</ul><br>";
	}
}
echo "</p>"; */

//require 'page.php';

?>