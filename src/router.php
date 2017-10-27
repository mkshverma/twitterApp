<?php
require('fetchData.php');
$function = $_GET['call'];
if (method_exists('TwitterApp', $function)
    && is_callable(array('TwitterApp', $function)))
{
    $tw = new TwitterApp();
    $tw->{$function}();
}