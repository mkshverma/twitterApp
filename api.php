<?php
session_start();
header('Access-Control-Allow-Origin: *');
define('BASE_PATH','C:/xampp/htdocs/www/twitterapp/');
define('BASE_URL','http://localhost:8888/');
require('src/fetchData.php');
$tw = new TwitterApp();
$function = @$_GET['call'];
if (method_exists('TwitterApp', $function)
    && is_callable(array('TwitterApp', $function)))
{
    $tw->{$function}();
}