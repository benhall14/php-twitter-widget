<?php

require '../vendor/autoload.php';

use benhall14\TwitterWidget;

?>
<!doctype html>

<html lang="en">

    <head>

        <meta charset="utf-8">

        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <title>PHP Twitter Widget By benhall14</title>

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <link href="https://fonts.googleapis.com/css?family=Oxygen:400,700" rel="stylesheet"> 

        <link rel="stylesheet" type="text/css" href="css/stylesheet.css">

        <link rel="stylesheet" type="text/css" href="css/twitter-widget.css">

    </head>

    <body>

        <h1>PHP Twitter Widget <span>By benhall14</span></h1>
        
        <div class="container">

            <div class="summary">A PHP alternative to the Twitter Embed Timelines JavaScript.</div>

            <div>            
                <p>Twitter Embedded Timelines allow you to show your latest tweets using JavaScript. While this can help you easily implement your latest tweets on you website, I needed to create a similar effect without including any additional JavaScript files on the web page. I decided to create a script that will fetch tweets server side, and allow for including in a web page without JavaScript includes. I also needed to ensure I cached the result, to avoid hitting API limits.</p>

                <p>Using the popular TwitterOAuth PHP Library for authentication, we can access the Twitter Rest API.</p>

                <p>First, you need to head over to <a href="https://apps.twitter.com/" target="_blank">https://apps.twitter.com</a> and register your new Application. You will need the following keys for your new 'App'.
                    <ul>
                        <li>Consumer Key</li>
                        <li>Consumer Secret Key (This must always remain private)</li>
                        <li>Access Token</li>
                        <li>Access Token Secret</li>
                    </ul>
                </p>

                <p>You can now install the PHP Twitter Widget using composer:</p>
                
                <pre>composer install benhall14/php-twitter-widget</pre>

                <p>To implement, first instantiate a TwitterWidget with your credentials to fetch the feed. The feed will be cached so we don't call the API on every page load. The default cache file age limit is 3600 seconds (1 hour).</p>
                
                <pre>try {
    $twitterWidget = new TwitterWidget(
        $consumer_key, # your Twitter consumer key
        $consumer_secret, # your Twitter consumer secret
        $access_token, # your Twitter access token
        $access_token_secret, # your Twitter access token secret
        'nasa', # the screen name of the Twitter feed to display
        15, # the number of tweets to return
        '../cache/twitter_status_feed.json', # path to the cache file
        (optional) # the number of seconds to cache the feed for, default is 3600 (1 hour)
    );
} catch (Exception $e) {
    /* handle any error thrown, such as unwritable cache file */
    echo $e->getMessage();
}</pre>

                <p>You can then use the following to display the feed on your web page, passing the your chosen feed title, such as "My Tweets". The second optional parameter should be a boolean of whether to print(true) or return(false) the formatted feed. The third optional parameter is the tweet date format - default = M d H:i A.</p>

                <pre>echo $twitterWidget->display('My Tweets', true, 'M d H:i A');</pre>

            </div>

        </div>

    </body>

</html>