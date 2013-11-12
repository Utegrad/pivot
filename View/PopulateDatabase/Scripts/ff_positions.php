<?php
require_once '../../../bootstrap.php';
$token = Api::TouchToken();
if ($token == FALSE) {
	echo "<h2>Problem getting token from the database</h2>";
	echo "<p><a href='". $APP_URL ."/View/PopulateDatabase/index.php'>Index</a></p>";
}
require_once APP_ROOT . 'Classes/FfPosition.php';

$positions = CBSSports::GetJsonData(CBSSports::FF_POSITION);

foreach ($positions['data']->body->positions as $position){
	$pos = FfPosition::withJson($position);
	if (in_array($pos->Abbr, $pos->relivantPositions)) {
		echo "<p>Stats Group: ". $pos->PositionGroup->FF_StatsGroupName ." : ". $pos->PositionGroup->FF_StatsGroupId ."</p>";
		echo "<p>Abbr: ". $pos->Abbr ."</p>";
		echo "<p>Name: ". $pos->Name ."</p>";
	}
}

?>
