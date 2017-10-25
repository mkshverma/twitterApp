<?php
require "../vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

$config = require_once './config.php';
$twitteroauth = new TwitterOAuth($config['consumer_key'], $config['consumer_secret']);
$request_token = $twitteroauth->oauth(
    'oauth/request_token', [
        'oauth_callback' => $config['callback_url']
    ]
);

// throw exception if something gone wrong
if($twitteroauth->getLastHttpCode() != 200) {
    throw new \Exception('There was a problem performing this request');
}

// save token of application to session
$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

// generate the URL to make request to authorize our application
$url = $twitteroauth->url(
    'oauth/authorize', [
        'oauth_token' => $request_token['oauth_token']
    ]
);

// and redirect
header('Location: '. $url);
//echo '<pre>';
//$statuses = $connection->get("statuses/user_timeline", ["count" => 10, "exclude_replies" => false]);
// Tweets
//print_r($statuses);