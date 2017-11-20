<?php

namespace benhall14;

use Abraham\TwitterOAuth\TwitterOAuth;
use \Exception;

/**
 *
 */
class TwitterWidget
{
    /**
     * Twitter API Consumer Key.
     * @var string
     */
    private $consumer_key;

    /**
     * Twitter API Consumer Secret.
     * @var string
     */
    private $consumer_secret;

    /**
     * Twitter API Access Token.
     * @var string
     */
    private $access_token;

    /**
     * Twitter API Access Secret.
     * @var string
     */
    private $access_token_secret;

    /**
     * The Twitter handle.
     * @var string
     */
    private $twitter_user;

    /**
     * The cache file name.
     * @var string
     */
    private $cache_file;

    /**
     * The cache time, in seconds.
     * @var int
     */
    private $cache_limit;

    /**
     * The number of tweets to retrieve.
     * @var integer
     */
    private $tweet_limit = 10;

    /**
     * The feed content.
     * @var array
     */
    private $content;

    /**
     *
     * @param string  $consumer_key        The Twitter API consumer key.
     * @param string  $consumer_secret     The Twitter API consumer secret key.
     * @param string  $access_token        The Twitter API access token.
     * @param string  $access_token_secret The Twitter API access token secret.
     * @param string  $user                The Twitter handle of the feed to retrieve.
     * @param string  $cache_file          The filename of where to save the cached feed.
     * @param integer $cache_limit         The number of seconds to keep the cache file for.
     */
    public function __construct($consumer_key, $consumer_secret, $access_token, $access_token_secret, $user, $number_of_tweets, $cache_file, $cache_limit = 3600)
    {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->access_token = $access_token;
        $this->access_token_secret = $access_token_secret;
        $this->twitter_user = $user;
        $this->tweet_limit = $number_of_tweets;
        $this->cache_file = $cache_file;
        
        if (!is_writable(dirname($this->cache_file))) {
            throw new Exception('Your cache file is not writable.');
        }

        $this->cache_limit = (int) $cache_limit;

        # get the feed content
        $this->content = $this->get();
    }

    /**
     * Set an optional parameter.
     * @param string $name  The option name.
     * @param string $value The option value.
     */
    public function setOption($name, $value)
    {
        $this->{$name} = $value;

        return $this;
    }

    /**
     * Get the age of the cached file, in seconds.
     * @return int The file age.
     */
    private function cacheFileAge()
    {
        return time() - filemtime($this->cache_file);
    }

    /**
     * Get the feed from the cache or API
     * @param  integer $limit The number of items to return.
     * @return array         The content
     */
    private function get()
    {
        if (file_exists($this->cache_file)) {
            if ($this->cacheFileAge() < $this->cache_limit) {
                return json_decode(file_get_contents($this->cache_file));
            }
        }

        $connection = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->access_token, $this->access_token_secret);

        $response = $connection->get("account/verify_credentials");

        #$content = $connection->get("statuses/home_timeline", ["count" => 25, "exclude_replies" => true]);

        $content = $connection->get("statuses/user_timeline", array(
            "screen_name" => $this->twitter_user,
            #"trim_user" => true,
            "count" => $this->tweet_limit,
            #"include_entities" => true,
            "tweet_mode" => "extended",
            "exclude_replies" => true
        ));

        if ($content) {
            if (!file_put_contents($this->cache_file, json_encode($content))) {
                throw new Exception('Could not save cache file.');
            }
        }

        return $content;
    }

    /**
     * Return the Twitter content.
     * @return array The Twitter feed array.
     */
    public function asArray()
    {
        return $this->content;
    }

    /**
     * Generate the avatar HTML based on the given user object.
     * @param  object $user Either the $tweet->user or $tweet->retweeted_status->user object.
     * @return string       The formated HTML string.
     */
    private function tweetAvatar($user)
    {
        $author_avatar = '<div class="tweet-avatar">';
            
        $author_avatar .= '<a href="' . $user->url . '" title="' . $user->name . '">';

        $author_avatar .= '<img alt="' . $user->name . '" src="' . $user->profile_image_url . '">';

        $author_avatar .= '<div class="tweet-name">' . $user->name . '</div>';
            
        $author_avatar .= '<div class="tweet-screen">@' . $user->screen_name. '</div>';
            
        $author_avatar .= '</a>';

        $author_avatar .= '</div>';

        return $author_avatar;
    }

    /**
     * Generate the tweet content HTML.
     * Replace hashtags, mentions and URLS with formated hyper-links.
     * If the tweet has images, include them in the formated string.
     * @param  string $the_tweet_content The original tweet content.
     * @param  object $entities          Object of entities - either $tweet->entities, or $tweet->retweeted_status->entities.
     * @return string                    The formatted HTML string.
     */
    private function formatTweet($the_tweet_content, $entities)
    {
        if (isset($entities->hashtags)) {
            foreach ($entities->hashtags as $hashtag) {
                $the_tweet_content = str_replace('#' . $hashtag->text, '<a href="https://twitter.com/hashtag/' . $hashtag->text . '">#' . $hashtag->text . '</a>', $the_tweet_content);
            }
        }

        if (isset($entities->urls)) {
            foreach ($entities->urls as $url) {
                $the_tweet_content = str_replace($url->url, '<a href="' . $url->url . '">' . $url->url . '</a>', $the_tweet_content);
            }
        }

        if (isset($entities->user_mentions)) {
            foreach ($entities->user_mentions as $mention) {
                $the_tweet_content = str_replace('@' . $mention->screen_name, '<a href="https://twitter.com/' . $mention->screen_name . '">@' . $mention->screen_name . '</a>', $the_tweet_content);
            }
        }

        $tweet_media = '';

        if (isset($entities->media)) {
            $tweet_media .= '<div class="tweet-media">';
    
            foreach ($entities->media as $media) {
                $the_tweet_content = str_replace($media->url, '<a href="' . $media->url . '">' . $media->url . '</a>', $the_tweet_content);
                
                $tweet_media .= '<a href="' . $media->expanded_url . '"><img src="' . $media->media_url_https . '"/></a>';
            }

            $tweet_media .= '</div>';
        }

        $the_tweet_content = '<div class="tweet-content">' . $the_tweet_content . '</div>';

        $the_tweet_content .= $tweet_media;
                        
        return $the_tweet_content;
    }

    /**
     * Generate the Twitter widget.
     * @param  string  $title The title to use. Default = 'Tweets'.
     * @param  boolean $print Should the method return the HTML string, or echo it.
     * @return string         The HTML string (based on the $print boolean).
     */
    public function display($title = 'Tweets', $print = true, $date_format = 'M d H:i A')
    {
        $tweets = '<div class="twitter-widget">';
        
        $tweets .= '<h2>' . $title . '</h2>';
        
        $tweets .= '<ul class="tweet-list">';

        if ($this->content) {
            if (is_array($this->content)) {
                foreach ($this->content as $tweet) {
                    if (isset($tweet->retweeted_status)) {
                        $tweets .= '<li class="retweet">';

                        $tweets .= '<div class="icon"></div>';

                        $tweets .= '<div class="retweeted"><b>' . $tweet->user->name . '</b> Retweeted:</div>';

                        $tweets .= $this->tweetAvatar($tweet->retweeted_status->user);

                        $tweets .= $this->formatTweet($tweet->retweeted_status->full_text, $tweet->retweeted_status->entities);

                        $tweets .= '<div class="tweet-date">' . date($date_format, strtotime($tweet->created_at)) . '</div>';

                        $tweets .= '</li>';
                    } else {
                        $tweets .= '<li class="tweet">';

                        $tweets .= '<div class="icon"></div>';

                        $tweets .= $this->tweetAvatar($tweet->user);

                        $tweets .= $this->formatTweet($tweet->full_text, $tweet->entities);

                        $tweets .= '<div class="tweet-date">' . date($date_format, strtotime($tweet->created_at)) . '</div>';

                        $tweets .= '</li>';
                    }
                }
            }
        }

        $tweets .= '</ul>';

        $tweets .= '</div>';

        if ($print) {
            echo $tweets;
        }

        return $tweets;
    }

    /**
     * Save the formatted HTML string into a HTML file.
     * @param  string $filename The filename to save the HTML string to.
     * @param  string $title    The title of your Twitter widget. Default = Tweets.
     * @return boolean          Whether or not the save was successful.
     */
    public function save($filename, $title = 'Tweets')
    {
        if (is_writable(dirname($filename))) {
            $content = $this->display($title, false);
            file_put_contents($filename, $content);
        } else {
            return false;
        }

        return true;
    }
}
