<?php require('./fetchData.php'); 
$tw = new TwitterApp();
$statuses = $tw->fetch_tweets(); 
$followers = $tw->fetch_followers($statuses[0]->user->screen_name); ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Bootstrap 101 Template</title>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.0/css/swiper.min.css">


        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style type="text/css">
            .swiper-container {
                width: 100%;
                height: 400px;
            }
            body{
                color: #000;
            }
        </style>
    </head>
    <body class="container">
        <div class="row">
       <!-- <pre><?=print_r($followers);?></pre> -->
       <?php if(!$statuses){
        echo '<a class="btn btn-info" href="autorize.php">LOGIN To twitter</a>';
       }else{ ?>
       <section class="col-sm-6 col-sm-offset-3">
       <h1> Tweets</h1>
       <div class="swiper-container">
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper">
                <!-- Slides -->
                <?php foreach ($statuses as $k => $status) { ?>
                    <div class="swiper-slide">
                            <img src="<?=$status->user->profile_image_url;?>"> @<?=$status->user->screen_name;?><br>
                            <?=$status->text;?><br>
                            <?php 
                            if(!empty($status->entities->media)){
                                foreach ($status->entities->media as $media) {
                                    if($media->type == 'photo'){
                                        echo '<img src="'.$media->media_url.'" width="100%"><br>';
                                    }else if($media->type == 'video'){
                                        echo '<video src="'.$media->media_url.'" width="100%" controls><br>';
                                    }
                                }
                            } ?>
                            <?=date('jS F Y',strtotime($status->created_at));?>
                    </div>
                <?php } ?>
            </div>
         
            <!-- If we need navigation buttons -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
         
            <!-- If we need scrollbar -->
            <div class="swiper-scrollbar"></div>
        </div>
        </section>
        <section class="col-sm-3">
            <h1>Folowers</h1>
            <?php foreach($followers->users as $follow){ ?>
            <a href="#" class="followers btn btn-block btn-default" data-screen="<?=$follow->screen_name;?>">
                <img src="<?=$follow->profile_image_url;?>" alt="">
                <?=$follow->name;?><br>
                <small><?=$follow->screen_name;?></small>
            </a>
            <?php } ?>
        </section>
        <?php
        }
        ?>
        </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.0/js/swiper.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            //initialize swiper when document ready
            var mySwiper = new Swiper ('.swiper-container', {
              // Optional parameters
              direction: 'horizontal',
              loop: true
            });
            $(document).on('click','.followers', function(e){
                e.preventDefault();
                $screen_name = $(this).data('screen');
                $.get('getTweets.php?screen='+$screen_name).then(function(data){
                    $('.swiper-wrapper').html('');
                    $.each(data, function(k,tweet){
                        $('.swiper-wrapper').append(displayTweet(tweet));
                    });
                });
            });
          });
        function displayTweet(tweet){
            var media = '';
            if(tweet.entities.media){
                for (var i = tweet.entities.media.length - 1; i >= 0; i--) {
                    if(tweet.entities.media[i].type =='photo'){
                        media += '<img src="'.tweet.media[i].media_url+'" width="100%"><br>';
                    }else if(tweet.entities.media[i].type =='video'){
                        media += '<video src="'.tweet.media[i].media_url+'" width="100%" controls><br>';
                    }
                }
            }
            var template = '<div class="swiper-slide">\
                            <img src="" alt="" height="200px">\
                            <img src="'+tweet.user.profile_image_url+'"> '+tweet.user.screen_name+'<br>\
                            '+tweet.text+'<br>\
                            '+media+'<br>\
                    </div>';
                    return template;
        }
    </script>
    </body>
    </html>