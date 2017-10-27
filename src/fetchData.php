<?php

require "../vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;


session_start();
/**
* 
*/
class TwitterApp
{
	protected $loggedIn = false;
	protected $twitter;
	protected $config = array(
	    'consumer_key'  =>  'zKhRKZyRmUBW5lVFciSQaqUC2',
	    'consumer_secret' => 'TEDdsq7p3OeL3drlxGxvyGCxBvZ4ocUy6zUocm5AhkGuNYG5xg',
	    'login_url' =>  'http://localhost/twitterapp/src/index.php',
	    'callback_url' =>  'http://localhost/twitterapp/src/router.php?call=callback'
	);
	function __construct()
	{
		if(isset($_SESSION['oauth_token'])) {
		    $this->loggedIn = true;
		    $token = $_SESSION;
		    $this->twitter = new TwitterOAuth(
		        $this->config['consumer_key'],
		        $this->config['consumer_secret'],
		        $token['oauth_token'],
		        $token['oauth_token_secret']
		    );
		}else{
		    $this->twitter = new TwitterOAuth(
		        $this->config['consumer_key'],
		        $this->config['consumer_secret']
		    );
		}
	}

	function authorize(){

		$request_token = $this->twitter->oauth(
		    'oauth/request_token', [
		        'oauth_callback' => $this->config['callback_url']
		    ]
		);

		// throw exception if something gone wrong
		if($this->twitter->getLastHttpCode() != 200) {
		    throw new \Exception('There was a problem performing this request');
		}

		// save token of application to session
		$_SESSION['oauth_token'] = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

		// generate the URL to make request to authorize our application
		$url = $this->twitter->url(
		    'oauth/authorize', [
		        'oauth_token' => $request_token['oauth_token']
		    ]
		);

		// and redirect
		header('Location: '. $url);exit();
	}

	function callback()
	{

		$oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');


		// request user token
		$token = $this->twitter->oauth(
		    'oauth/access_token', [
		        'oauth_verifier' => $oauth_verifier
		    ]
		);
		$_SESSION = $token;
		header('Location: ./index.php');exit();
	}

	function getTweets(){
		if(isset($_SESSION['oauth_token'])) {
		    // $this->loggedIn = true;
		    $token = $_SESSION;
		    $screen = $_GET['screen'];
		    $tweets = $this->twitter->get("statuses/user_timeline", ["count" => 10, "exclude_replies" => false,'screen_name'=>$screen]);
		    header("Content-type: application/json");
		    exit(json_encode($tweets));
		}
		exit(json_encode([]));
	}

	function fetch_tweets()
	{
		if($this->loggedIn){
			return $this->twitter->get("statuses/home_timeline", ["count" => 10, "exclude_replies" => false]);
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