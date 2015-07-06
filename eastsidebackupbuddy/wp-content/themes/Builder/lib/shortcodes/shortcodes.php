<?php

/*
Plugin Name: iThemes Shortcodes
Plugin URI: http://ithemes.com/
Description: A set of very useful shortcodes from iThemes.com
Version: 0.0.1
Author: Chris Jean
Author URI: http://ithemes.com/
*/


function test_shortcode() {
	return "<h1>I'm a shortcode!</h1>";
}
add_shortcode( 'test_shortcode', 'test_shortcode' );


?>
