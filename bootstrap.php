<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('APP_ROOT', ROOT . DS);
require APP_ROOT . 'Classes/Config.php';

$App = new App();

$APP_URL = $App->APP_URL;


?>