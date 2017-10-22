<?php
require "../vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

$config = require_once './config.php';
if(!isset($_SESSION['oauth_token'])) {
    ?>
    <a href="authorize.php">Login</a>
    <?php
}else{
    $twitter = new TwitterOAuth(
        $config['consumer_key'],
        $config['consumer_secret'],
        $token['oauth_token'],
        $token['oauth_token_secret']
    );

    $statuses = $twitter->get("statuses/user_timeline", ["count" => 10, "exclude_replies" => false]);
    print_r(
        $statuses
    );
}