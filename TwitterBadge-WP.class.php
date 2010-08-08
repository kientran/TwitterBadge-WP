<?php

  class TwitterBadge_WP {

    public $adminOptionsName = 'TwitterBadgeAdminOptions';

    function init() {
      $this->getAdminOptions();
      register_setting($this->adminOptionsName, 'TwitterBadge');
     
    }

    function getAdminOptions() {
      $twitterBadgeAdminOptions = array( 'number_tweets' => 5,
          'cache_timeout'       => 5,
          'tweetFormat'         => '<li>[@text]</li>',
          'listFormat'          => '<ul>[@tweets]</ul>',
          'consumer_key'        => '',
          'consumer_secret'     => '',
          'oauth_token'         => '',
          'oauth_token_secret'  => ''
      );
      
      $adminOptions = get_option( 'TwitterBadge' );
      if (!empty( $adminOptions ) ) {
        foreach ( $adminOptions as $key => $option )
          $twitterBadgeAdminOptions[$key] = $option;
      }
      update_option( 'TwitterBadge', $twitterBadgeAdminOptions);
      return $twitterBadgeAdminOptions;
    }

    function getFeed () {

      $options = get_option('TwitterBadge');

      if (false === ($tweets = get_transient('TwitterFeed') ) )
      {
        if ( !class_exists('TwitterOAuth') )
          require_once ('lib/twitteroauth/twitteroauth.php');
        $connection = new TwitterOAuth( $options['consumer_key'], 
                                        $options['consumer_secret'],
                                        $options['oauth_token'],
                                        $options['oauth_token_secret'] ); 

        $content = $connection->get( 'statuses/user_timeline',
                                      array('count' => $options['number_tweets'],
                                            'include_rts' => true));

        set_transient('TwitterFeed', $content, $options['cache_timeout']);
        $tweets = $content;
      }
      
      return $tweets;
    }

    function parseFeed($content) {
      $options = get_option('TwitterBadge');
      if ( !class_exists('TwitterBadge') )
        require_once ('lib/TwitterBadge-PHP/TwitterBadge.php');
      $tb = new TwitterBadge();
      $tb->setListFormat($options['listFormat']);
      $tb->setTweetFormat($options['tweetFormat']);
      
      return $tb->parseObject($content);
   
    }

    function printFeed() {
      $feed = $this->getFeed();
      if ($feed->error) {
        return "Feed Error: $feed->error";
      } elseif (empty($feed)) {
        return "Feed Error: Twitter didn't respond.";
      }
      else {
        return $this->parseFeed($feed);
      }
    }

    function printAdminPage() {
?>
<div class='wrap'>
<form method='POST' action='options.php'>
<h2>Twitter Badge Options</h2>
<h3>Display Options</h3>
<?php settings_fields($this->adminOptionsName); ?>
<?php $options = get_option('TwitterBadge'); ?>
<table class='form-table'>
                <tr valign="top"><th scope="row">Cache Timeout</th>
                    <td><input type="text" name="TwitterBadge[cache_timeout]" value="<?php echo $options['cache_timeout']; ?>" /></td>
                </tr>
 
                <tr valign="top"><th scope="row">Tweet Format</th>
                    <td><input type="text" name="TwitterBadge[tweetFormat]" value="<?php echo $options['tweetFormat']; ?>" /></td>
                </tr>
                <tr valign="top"><th scope="row">Tweet List Format</th>
                    <td><input type="text" name="TwitterBadge[listFormat]" value="<?php echo $options['listFormat']; ?>" /></td>
                </tr>
                <tr valign="top"><th scope="row">Number of Tweets to Show</th>
                    <td><input type="text" name="TwitterBadge[number_tweets]" value="<?php echo $options['number_tweets']; ?>" /></td>
                </tr>
</table>
<h3>Twitter Single User API Keys</h3>

<table class='form-table'>
  <tr valign="top"><th scope="row">Consumer Key</th>
      <td><input style="width:400px" type="text" name="TwitterBadge[consumer_key]" value="<?php echo $options['consumer_key']; ?>" /></td>
  </tr>
  <tr valign="top"><th scope="row">Consumer Secret</th>
      <td><input style="width:400px" type="text" name="TwitterBadge[consumer_secret]" value="<?php echo $options['consumer_secret']; ?>" /></td>
  </tr>
  <tr valign="top"><th scope="row">OAuth Token</th>
      <td><input style="width:400px" type="text" name="TwitterBadge[oauth_token]" value="<?php echo $options['oauth_token']; ?>" /></td>
  </tr>
  <tr valign="top"><th scope="row">Oauth Token Secret</th>
      <td><input style="width:400px" type="text" name="TwitterBadge[oauth_token_secret]" value="<?php echo $options['oauth_token_secret']; ?>" /></td>
  </tr>

</table>
<input class='button-primary' type='submit' name='save' 
  value='<?php _e('Save Options'); ?>' id='submitbutton' />
</form>
</div>

<?php
      
    }

    function AdminMenu() {
		add_submenu_page('options-general.php', __('Twitter Badge'), __('Twitter Badge'), 6, str_replace('.php', '', __FILE__), array($this, 'printAdminPage'));
    }
  }


?>
