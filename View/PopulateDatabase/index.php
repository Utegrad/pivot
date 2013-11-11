<?php
// any session or preprocessing
require '../../bootstrap.php';
$database = new Database();
$accessToken = Api::TouchToken();
require_once APP_ROOT .'Classes/Utility.php';

// set contents to load

$App->StyleSheets['Default'] = 'View/CSS/default.css';
$App->StyleSheets['DataTable'] = 'View/CSS/DataTable.css';

define('CURR_DIR', dirname(__FILE__) . DS);

$presentation = array('main' => CURR_DIR . 'main.php', );
$scripts = CURR_DIR .'Scripts'. DS;
$tables = array(
		'app_config' => (Data::GetTableRowCount('app_config')),
		'ff_stats_groups' => (Data::GetTableRowCount('ff_stats_groups')),
		'ff_positions' => (Data::GetTableRowCount('ff_positions')),
		'nfl_teams' => (Data::GetTableRowCount('nfl_teams')),
		'ff_owners' => (Data::GetTableRowCount('ff_owners')),
		'ff_leagues' => (Data::GetTableRowCount('ff_leagues')),
		'ff_teams' => (Data::GetTableRowCount('ff_teams')),
		'nfl_player_statuses' => (Data::GetTableRowCount('nfl_player_statuses')),
		'ff_seasons' => (Data::GetTableRowCount('ff_seasons')),
		'ff_weeks' => (Data::GetTableRowCount('ff_weeks')),
		'nfl_players' => (Data::GetTableRowCount('nfl_players')),
		'nfl_players_nfl_teams' => (Data::GetTableRowCount('nfl_players_nfl_teams', 'nfl_team_id')),
		'ff_teams_nfl_players' => (Data::GetTableRowCount('ff_teams_nfl_players', 'ff_team_id')),
		'nfl_players_weekly_scores' => (Data::GetTableRowCount('nfl_players_weekly_scores', '*')),		
);



require_once APP_ROOT .'page.php';

?>