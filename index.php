
<?php
if(file_exists("'vendor/autoload.php'")){
    require_once 'vendor/autoload.php';
}


define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
// set a constant that holds the project's "application" folder, like "/var/www/application".
define('APP', ROOT . 'FeedReader' . DIRECTORY_SEPARATOR);


require APP.'core/Application.php';
require APP.'core/Controller.php';

$app = new Application();
