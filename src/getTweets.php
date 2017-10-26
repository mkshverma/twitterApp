<?php

require "../vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;


$config = require_once './config.php';
if(isset($_SESSION['oauth_token'])) {
    // $this->loggedIn = true;
    $token = $_SESSION;
    $twitter = new TwitterOAuth(
        $config['consumer_key'],
        $config['consumer_secret']
    );
    $screen = $_GET['screen'];
    $tweets = $twitter->get("statuses/user_timeline", ["count" => 10, "exclude_replies" => false,'screen_name'=>$screen]);
    header("Content-type: application/json");
    exit(json_encode($tweets));
}