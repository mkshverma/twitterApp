<?php
session_start();
header('Access-Control-Allow-Origin: *');
define('BASE_PATH','/opt/bitnami/apache2/htdocs/');
define('BASE_URL','http://35.202.132.52/');
require('src/fetchData.php');
$tw = new TwitterApp();
$function = @$_GET['call'];
if (method_exists('TwitterApp', $function)
    && is_callable(array('TwitterApp', $function)))
{
    $tw->{$function}();
}