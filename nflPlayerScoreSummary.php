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

$token = Api::TouchToken('U2FsdGVkX1_laPUP2JFms7EWi43KMWANsx3nNMPdlvv3-zQZNRHCprnNXNz3UR2PSw3oSxV675pnTdWl_SzXGWEwjSb928i5RAHs4uvT2VwITmyNiYt6VQLXk3Rnd8Ks');
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database or from _GET</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}

$styleSheets = array(
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
$nflPlayerSets = array();
foreach($nflPlayerData['data']->body->players as $player){
	$p = NFLPlayer::withJson($player);
	$periods = array();
	// find players fantasy team from roster data
	if(in_array($p->Pos, $p->Position->relivantPositions)){
		foreach($ffTeamRosters['data']->body->rosters->teams as $team){
			foreach ($team->players as $member){
				if ($p->FullName == $member->fullname) {
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
				$periods = $plyr->periods;
			}
		}
	}
	$playerSummary = array(
		'id' => $p->Id,
		'fullName' => $p->FullName,
		'pos' => $p->Pos,
		'nflTeam' => $p->NFLTeam,
		'bye' => $p->ByeWeek,
		'fTeam' => $p->FfTeam,
		'avg' => 'Need to calc this',
		'sd' => 'Need to calc this',
		'Rating' => 'Need to calc this',
	);
	
	$set = array('player' => $p, 'periods' => $periods, );
	array_push($nflPlayerSets, $set);
}



foreach($nflPlayerSets as $set){
	if(!(empty($set['player']->FfTeam->Name))){
		$id = $set['player'];
		$fullName;
		
		foreach ($set['periods'] as $period){
			
		}
		
	}
}
//require 'page.php';

?>