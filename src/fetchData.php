<?php

require "../vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;


/**
* 
*/
class TwitterApp
{
	protected $loggedIn = false;
	protected $twitter;
	function __construct()
	{
		$config = require_once './config.php';
		if(isset($_SESSION['oauth_token'])) {
		    $this->loggedIn = true;
		    $token = $_SESSION;
		    $this->twitter = new TwitterOAuth(
		        $config['consumer_key'],
		        $config['consumer_secret'],
		        $token['oauth_token'],
		        $token['oauth_token_secret']
		    );
		}
	}

	function fetch_tweets()
	{
		if($this->loggedIn){
			return $this->twitter->get("statuses/user_timeline", ["count" => 10, "exclude_replies" => false]);
		}else{
			return false;
		}
	}

	function fetch_followers($screen_name)
	{
		if($this->loggedIn){
			return $this->twitter->get("followers/list", ["count" => 10, "screen_name" => $screen_name]);
		}else{
			return false;
		}
	}
}