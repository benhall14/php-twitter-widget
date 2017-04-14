# PHP Twitter Widget
A PHP alternative to the Twitter Embed Timelines JavaScript.

Twitter Embedded Timelines allow you to show your latest tweets using JavaScript. While this can help you easily implement your latest tweets on you website, I needed to create a similar effect without including any additional JavaScript files on the web page. 

I decided to create a script that will fetch tweets server side, and allow for including in a web page without JavaScript includes. I also needed to ensure I cached the result, to avoid hitting API limits.

Using the popular TwitterOAuth PHP Library for authentication, we can access the Twitter Rest API.

# Usage

First, you need to head over to <a href="https://apps.twitter.com/" target="_blank">https://apps.twitter.com</a> and register your new Application. You will need the following keys for your new 'App'.
 * Consumer Key
 * Consumer Secret Key (This must always remain private)
 * Access Token
 * Access Token Secret

You can now install the PHP Twitter Widget using composer:
                
```bash
composer install benhall14/php-twitter-widget
```
To implement, first instantiate a TwitterWidget with your credentials to fetch the feed. The feed will be cached so we don't call the API on every page load. The default cache file age limit is 3600 seconds (1 hour).
                
```php
require '../vendor/autoload.php';

try {
    $twitterWidget = new TwitterWidget(
        $consumer_key, # your Twitter consumer key
        $consumer_secret, # your Twitter consumer secret
        $access_token, # your Twitter access token
        $access_token_secret, # your Twitter access token secret
        'nasa', # the screen name of the Twitter feed to display
        15, # the number of tweets to return
        '../cache/twitter_status_feed.json', # path to the cache file
        3600 # the number of seconds to cache the feed for, default is 3600 (1 hour)
    );
} catch (Exception $e) {
    /* handle any error thrown, such as unwritable cache file */
    echo $e->getMessage();
}
```

You can then use the following to display the feed on your web page, passing the your chosen feed title, such as "My Tweets". The second optional parameter should be a boolean of whether to print(true) or return(false) the formatted feed. The third optional parameter is the tweet date format - default = M d H:i A.

```php
<php echo $twitterWidget->display('My Tweets', true, 'M d H:i A'); ?>
```

# Requirements
PHP 5.5+

TwitterOAuth Library

# License
Copyright (c) 2016-2017 Benjamin Hall, benhall14@hotmail.com

Licensed under the MIT license
