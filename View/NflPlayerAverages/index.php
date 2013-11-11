<?php
// any session or preprocessing
require '../../bootstrap.php';
$database = new Database();
$accessToken = Api::TouchToken();

// Gather page data to display
require_once APP_ROOT .'Classes/Utility.php';

$q = "SELECT * from v_nfl_players_avg_scores";
$nflPlayerAvgs = Data::SelectFetchAll($q, $database->conn, TRUE);

// average of all player averages
$total = 0;
$avgs = array();
foreach ($nflPlayerAvgs as &$player){
	$total += $player['avg_score'];
	array_push($avgs, $player['avg_score']);
}

$avg = $total / count($avgs);
$sd = Helpers::sd($avgs);
$max = max($avgs);
$min = ceil($sd);
$avgScoreBuckets = array( (ceil($sd)), (2 * ceil($sd)), (3 * ceil($sd)), (4 * ceil($sd)) );

foreach ($nflPlayerAvgs as $key => $value){
	if ($value['avg_score'] > $min) {
		$nflPlayerAvgs[$key]['scale'] = 100 * (round(($value['avg_score'] / $max), 2));
		if($value['avg_score'] > $avgScoreBuckets[0] && $value['avg_score'] <= $avgScoreBuckets[1]){
			$nflPlayerAvgs[$key]['rating'] = 'Average';
		}
		elseif ($value['avg_score'] > $avgScoreBuckets[1] && $value['avg_score'] <= $avgScoreBuckets[2]){
			$nflPlayerAvgs[$key]['rating'] = 'Good';
		}
		elseif ($value['avg_score'] > $avgScoreBuckets[2] && $value['avg_score'] <= $avgScoreBuckets[3]){
			$nflPlayerAvgs[$key]['rating'] = 'Great';
		}
		elseif ($value['avg_score'] > $avgScoreBuckets[3] ) {
			$nflPlayerAvgs[$key]['rating'] = 'Outstanding';
		}
	}
	else{
		$nflPlayerAvgs[$key]['scale'] = 0;
		$nflPlayerAvgs[$key]['rating'] = "Don't bother";
	}
}

require APP_ROOT . 'Control/AvgScoreData.php';

$App->StyleSheets['Default'] = 'View/CSS/default.css';
$App->StyleSheets['DataTable'] = 'View/CSS/DataTable.css';

/* $styleSheets = array(
		'Default' => 'View/CSS/default.css',
		'DataTable' => 'View/CSS/DataTable.css',
); */

define('CURR_DIR', dirname(__FILE__) . DS);

$presentation = array('main' => CURR_DIR . 'main.php', );

require_once APP_ROOT .'page.php';


?>


