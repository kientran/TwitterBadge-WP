<?php
 /* 
    Plugin Name: Twitter Badge 
    Plugin URI: http://www.kientran.com
    Description: Plugin for displaying Tweets in a Widget 
    Author: Kien Tran
    Version: 1.0 
    Author URI: http://www.kientran.com
    */


if (!class_exists('TwitterBadge_WP')) {		
	require_once(dirname(__FILE__) .'/'. '/TwitterBadge-WP.class.php');	
}

if (class_exists('TwitterBadge_WP')) {
      $twitterBadge = new TwitterBadge_WP();
}

if(isset($twitterBadge)){

	add_action('admin_init', array($twitterBadge,'init'));
	add_action('admin_menu', array($twitterBadge,'AdminMenu'));
	
}

?>
