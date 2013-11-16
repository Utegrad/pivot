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

$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database or from _GET</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}

$App->StyleSheets = array(
		'Default' => 'View/CSS/default.css',
		'DataTable' => 'View/CSS/DataTable.css',
);

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

$nflPlayerData = CBSSports::GetJsonData(CBSSports::NFL_PLAYER);

$nflPlayerWeeklyScoring = CBSSports::GetJsonData(CBSSports::FPSWS, 'player_status=all');

$ffTeamRosters = CBSSports::GetJsonData(CBSSports::ROSTERS, 'all');

$playerSummarys = array();

foreach($nflPlayerData['data']->body->players as $player){
	$p = NFLPlayer::withJson($player);
	$periods = array();
	$scores = array();
	// find players fantasy team from roster data
	foreach($ffTeamRosters['data']->body->rosters->teams as $team){
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
	
	foreach ($nflPlayerWeeklyScoring['data']->body->weekly_scoring->players as $plyr){
		if($p->Id == $plyr->id){
			// save periods[] for this player
			foreach($plyr->periods as $period){
				array_push($periods, array('period' => $period->period, 'score' => $period->score));
				array_push($scores, $period->score);
			}
			
		}
	}
	
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

foreach($playerSummarys as $summary){
	if (in_array($summary['pos'], $summary['relivantPositions'] )) {
		$populationScores = array_merge($populationScores, $summary['scores']);
	}
}

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


$avgScoreBuckets = array( 
		(floor($populationAvg)), 
		($populationAvg + $populationStdDev), 
		($populationAvg + 2 * $populationStdDev), 
		($populationAvg + 3 * $populationStdDev), 
);

$populationMaxAvg = 0;

foreach($playerSummarys as $summary){
	$populationMaxAvg = ($summary['playerAvg'] > $populationMaxAvg ? $summary['playerAvg'] : $populationMaxAvg);
}

foreach($playerSummarys as &$summary){
	if ($summary['playerAvg'] > $populationAvg) {
		
		$summary['scale'] = 100 * (round(($summary['playerAvg'] / $populationMaxAvg), 2));
		if($summary['playerAvg'] > $avgScoreBuckets[0] && $summary['playerAvg'] <= $avgScoreBuckets[1]){
			$summary['rating'] = 'Average';
		}
		elseif ($summary['playerAvg'] > $avgScoreBuckets[1] && $summary['playerAvg'] <= $avgScoreBuckets[2]){
			$summary['rating'] = 'Good';
		}
		elseif ($summary['playerAvg'] > $avgScoreBuckets[2] && $summary['playerAvg'] <= $avgScoreBuckets[3]){
			$summary['rating'] = 'Great';
		}
		elseif ($summary['playerAvg'] > $avgScoreBuckets[3] ) {
			$summary['rating'] = 'Outstanding';
		}
	}
	else{
		$summary['scale'] = 0;
		$summary['rating'] = "Below Average";
	}			
}

$averages = array();
foreach($playerSummarys as $key => $row){
	$averages[$key] = $row['playerAvg'];
}
array_multisort($averages, SORT_DESC, $playerSummarys);

define('CURR_DIR', dirname(__FILE__) . DS);

$presentation = array('main' => CURR_DIR . 'main.php', );

require_once 'page.php';

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