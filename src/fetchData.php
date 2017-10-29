<?php
require BASE_PATH."vendor/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;


/**
 *  TwitterApp
 */
class TwitterApp
{
    protected $loggedIn = false;
    protected $twitter;
    protected $config = array(
        'consumer_key' => 'zKhRKZyRmUBW5lVFciSQaqUC2',
        'consumer_secret' => 'TEDdsq7p3OeL3drlxGxvyGCxBvZ4ocUy6zUocm5AhkGuNYG5xg',
        'login_url' => BASE_URL,
        'callback_url' => BASE_URL.'api.php?call=callback'
    );

    function __construct()
    {
        if (isset($_SESSION['oauth_token'])) {
            $this->loggedIn = true;
            $token = $_SESSION;
            $this->twitter = new TwitterOAuth(
                $this->config['consumer_key'],
                $this->config['consumer_secret'],
                $token['oauth_token'],
                $token['oauth_token_secret']
            );
        } else {
            $this->twitter = new TwitterOAuth(
                $this->config['consumer_key'],
                $this->config['consumer_secret']
            );
        }
    }

    function authorize()
    {
        $request_token = $this->twitter->oauth(
            'oauth/request_token', [
                'oauth_callback' => $this->config['callback_url']
            ]
        );

        // throw exception if something gone wrong
        if ($this->twitter->getLastHttpCode() != 200) {
            throw new \Exception('There was a problem performing this request');
        }

        // save token of application to session
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        // print_r($_SESSION);die;
        // generate the URL to make request to authorize our application
        $url = $this->twitter->url(
            'oauth/authorize', [
                'oauth_token' => $request_token['oauth_token']
            ]
        );

        // and redirect
        header('Location: ' . $url);
        exit();
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
        header('Location: '.BASE_URL);
        exit();
    }

    function getTweets()
    {
        if (isset($_SESSION['screen_name'])) {
            $this->loggedIn = true;
            $token = $_SESSION;
            $screen = $_GET['screen'];
            $tweets = $this->twitter->get("statuses/user_timeline", ["count" => 10, "exclude_replies" => false, 'screen_name' => $screen]);
            header("Content-type: application/json");
            exit(json_encode($tweets));
        }
        exit(json_encode([]));
    }

    function fetch_tweets()
    {
        // print_r($_SESSION);
        if ($this->loggedIn) {
            exit(json_encode($this->twitter->get("statuses/home_timeline", ["count" => 10, "exclude_replies" => false])));
        } else {
            exit(json_encode([]));
        }
    }

    function fetch_followers()
    {
        $screen_name = @$_SESSION['screen_name'];
        if ($this->loggedIn) {
            exit(json_encode($this->twitter->get("followers/list", ["screen_name" => $screen_name])));
        } else {
            exit(json_encode([]));
        }
    }

    function download()
    {
        if (!empty($_POST) && $_POST['screen'] != '' && $_POST['format'] != '') {
            $input = $_POST;
            $followers = $this->twitter->get("followers/list", ["screen_name" => $input['screen']]);
            foreach ($followers->users as $kk => $row)
            {
                $data[] = [$row->name,$row->screen_name];
            }
//            print_r($data);
            if($input['format']=='csv')
            {
                // output headers so that the file is downloaded rather than displayed
                header('Content-type: text/csv');
                header('Content-Disposition: attachment; filename="Followers-list.csv"');

                // do not cache the file
                header('Pragma: no-cache');
                header('Expires: 0');

                // create a file pointer connected to the output stream
                $file = fopen('php://output', 'w');

                // send the column headers
                fputcsv($file, array('Name', 'Handler'));

                // output each row of the data
                foreach ($data as $row)
                {
                    fputcsv($file, $row);
                }
                exit();
            }
            if($input['format']=='xml') {
                $name = strftime('backup_%m_%d_%Y.xml');
                header('Content-Disposition: attachment;filename=' . $name);
                header('Content-Type: text/xml');
                $dom = new DomDocument();
                $root = $dom->createElement('followers');
                foreach ($data as $row)
                {
                    $user = $dom->createElement('User');
                    $name = $dom->createElement('name', $row[0]);
                    $handler = $dom->createElement('handler', $row[1]);
                    $user->appendChild($name);
                    $user->appendChild($handler);
                    $root->appendChild( $user );
                }
                $dom->appendChild($root);
                exit($dom->saveXML());
            }
            if($input['format']=='pdf') {

                require(BASE_PATH.'lib/fpdf.php');

                $pdf = new FPDF();
                $pdf->AddPage();
                $pdf->SetFont('Arial','B',16);
                $pdf->Cell(40,10,'Hello World!');
                $pdf->Output();
            }
            if($input['format']=='json') {
                $name = strftime('folowers_%m_%d_%Y.json');
                header('Content-Disposition: attachment;filename=' . $name);
                header('Content-Type: application/json');
                exit(json_encode(['followers'=>$data]));
            }
            exit(json_encode($followers));
        } else {
            exit(json_encode(['erroor' => 'Invalid handler']));
        }
    }
}