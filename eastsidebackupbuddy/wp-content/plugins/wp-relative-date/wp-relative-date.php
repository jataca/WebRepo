<?php
/*
Plugin Name: Artiss Relative Date
Plugin URI: http://www.artiss.co.uk/relative-date
Description: Return a relative formatted date
Version: 1.2
Author: David Artiss
Author URI: http://www.artiss.co.uk
*/

/**
* Artiss Relative Date
*
* Main code - include various functions
*
* @package	Artiss-Relative-Date
* @since	1.2
*/

define( 'artiss_relative_date_version', '1.2' );

/**
* Plugin initialisation
*
* Loads the plugin's translated strings and the plugins' JavaScript
*
* @since	1.2
*/

function ard_plugin_init() {

	$language_dir = plugin_basename( dirname( __FILE__ ) ) . '/languages/';

	load_plugin_textdomain( 'wp-relative-date', false, $language_dir );

}

add_action( 'init', 'ard_plugin_init' );

/**
* Main Includes
*
* Include all the plugin's functions
*
* @since	1.2
*/

$functions_dir = WP_PLUGIN_DIR . '/wp-relative-date/includes/';

// Include all the various functions

include_once( $functions_dir . 'ard-generate-relative-date.php' );		// Main code to perform currency conversion

if ( is_admin() ) {

	//if ( !function_exists( 'artiss_plugin_ads' ) ) {

	//    include_once( $functions_dir . 'artiss-plugin-ads.php' );   // Option screen ads

	//}

	include_once( $functions_dir . 'ard-admin-config.php' );        	// Assorted admin configuration changes

} else {

	include_once( $functions_dir . 'ard-function-calls.php' );	        // Function calls

}
?>