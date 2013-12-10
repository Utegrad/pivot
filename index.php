<?php
require 'bootstrap.php';
$database = new Database();
$STATUS = 'DEV';
if($STATUS != 'DEV'){
	// load content indicating application can only be loaded from CBS Sports
	die("Application can only be loaded from CBS Sports.");
}
$api = new Api();

define('CURR_DIR', dirname(__FILE__) . DS);

//$presentation = array('main' => CURR_DIR . 'beginning.php', );

//require_once APP_ROOT .'page.php';

/**
 * check that API->user_id and API->league_id are in the database and add them if not
 * In dev mode API->user_id and API->league_id are default values from dev league
 * If user_id and league_id are in the database then load page contents to show data
 * If not in the database, after adding them, give a wait a minute page and trigger scripts to import data
 * league_id could be in the database, but not user_id, in which case, we can show the data because it should already be there
 * Use a flag in the database to check with ajax on wait a minute page to see when data is ready
 * Once ready, we can come back here and get the data.
 * Need several script pages to trigger data imports that respond with basic indicator of success or failure
 * 
 */

?>