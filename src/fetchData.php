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
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        if (!empty($_POST) && $_POST['screen'] != '' && $_POST['format'] != '') {
            $input = $_POST;
            $cursor = -1;
            $ids = [];
            $users = [];
            $followers = [];
            while ( $cursor != 0 ){
                $temp = $this->twitter->get("followers/ids", ["screen_name" => $input['screen'],'cursor' => $cursor, 'count'=>5000]);
                if(isset($temp->ids)){
                    $ids = array_merge($ids, $temp->ids);
                    $cursor = $temp->next_cursor;
                }else{
                    break;
                }
            }
            $chunks = array_chunk($ids, 100);
            foreach ($chunks as $key => $value) {
                $temp = $this->twitter->get("users/lookup", ['user_id'=>implode(',', $value)]);
                $followers = array_merge($followers, $temp);
            }
            foreach ($followers as $kk => $row)
            {
                $users[] = [$row->name, $row->screen_name];
            }
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
                foreach ($users as $row)
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
                foreach ($users as $row)
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

                require(BASE_PATH.'lib/fpdf/fpdf.php');

                $pdf = new FPDF();
                $pdf->AddPage();
                $pdf->SetFont('Arial','B',16);
                $pdf->Cell(400,10,'Followers of @'.$input['screen'],0,1,'L');
                $pdf->SetFont('Arial','',14);
                foreach ($users as $k => $user) {
                    $pdf->Cell( 400, 10, ($k+1).'. '.$user[0].' (@'.$user[1].')', 0, 1, 'L' );
                }
                $pdf->Output('D');
            }
            if($input['format']=='xls') {
                require(BASE_PATH.'lib/PHPExcel/PHPExcel.php');
                $objPHPExcel = new PHPExcel();
                $sheet = $objPHPExcel->getActiveSheet();
                $sheet->getStyle("A1:B200")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                // $sheet->getStyle("A1:A2")->applyFromArray(array("font" => array( "bold" => true)));
                // $sheet->setCellValue('A1',"Followers List");
                $sheet->setCellValue('A1',"Followers List");
                $sheet->setCellValue('A2',"Name");
                $sheet->setCellValue('B2',"Handler");
                $x=2;
                foreach ($users as $key => $value) {
                    $sheet->setCellValue('A' . $x, $value[0] );
                    $sheet->setCellValue('B' . $x, '@'.$value[1] );
                    $x++;
                }
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $filename =  "followers-list-" . date( 'd-m-Y' ). ".xlsx";
                // Redirect output to a client’s web browser (Excel2007)
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$filename.'"');
                header('Cache-Control: max-age=0');
                // If you're serving to IE 9, then the following may be needed
                header('Cache-Control: max-age=1');
                // If you're serving to IE over SSL, then the following may be needed
                header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header ('Pragma: public'); // HTTP/1.0

                $objWriter->save('php://output');
            }
            if($input['format']=='json') {
                $name = strftime('folowers_%m_%d_%Y.json');
                header('Content-Disposition: attachment;filename=' . $name);
                header('Content-Type: application/json');
                exit(json_encode(['followers'=>$users]));
            }
            // exit(json_encode($followers));
        } else {
            exit(json_encode(['erroor' => 'Invalid handler']));
        }
    }
}