<?php

/*
Code responsible for loading required parts of the theme
Written by Chris Jean for iThemes.com
Version 1.3.0

Version History
	1.0.0 - 2010-12-15 - Chris Jean
		Release ready
	1.0.1 - 2011-02-22 - Chris Jean
		Added functions.php load
		Connected lib/tutorials/tutorials.php load to builder-my-theme-menu support
		Enforce minimum 64M memory limit
		Added lib/import-export/init.php load
		Added do_action call for builder_theme_features_loaded
		Moved builder_add_title function to functions.php
		Updated hooks at bottom of file
	1.0.2 - 2011-03-09 - Chris Jean
		Added builder_start_here_url global to store the Start Here URL
		Added it_tutorials_filter_url filter
	1.1.0 - 2011-06-27 - Chris Jean
		Added load handler for builder-plugin-features
		Created add_action hook for builder_add_favicon
		Created add_action hook for builder_render_stylesheets
		Created add_filter hook for builder_filter_favicon_url
	1.1.1 - 2011-06-28 - Chris Jean
		Added load handler for loop-standard
		Removed builder_add_favicon hook
		Removed builder_render_stylesheets hook
		Added add_action hook for comment_form_comments_closed
	1.2.0 - 2011-07-05 - Chris Jean
		Removed require for default-layouts.php as it is no longer needed
		Added filter for admin_body_class
		Added automatic support for builder-3.0 for parent theme
	1.3.0 - 2011-08-04 - Chris Jean
		Added feature to add Favicons to Dashboard
*/


do_action( 'builder_start' );

// Set theme-specific global variables
$GLOBALS['wp_theme_name']			= 'Builder';
$GLOBALS['theme_index']				= 'it-builder';
$GLOBALS['theme_menu_var']			= 'ithemes-builder-theme';
$GLOBALS['wp_theme_page_name']		= 'ithemes-builder-theme';
$GLOBALS['builder_start_here_url']	= 'http://ithemes.com/start-here-tuts/?site=' . get_option( 'siteurl' );


require_once( dirname( dirname( __FILE__ ) ) . '/classes/load.php' );
require_once( dirname( __FILE__ ) . '/functions.php' );
require_once( dirname( __FILE__ ) . '/compat.php' );


// Set the memory_limit to be at least 64M
// This is to help bypass out of memory errors that happen with WordPress 3.0:
// http://core.trac.wordpress.org/ticket/14889
builder_set_minimum_memory_limit( '64M' );


function it_builder_load_theme_features() {
	global $wp_version;
	
	
	it_classes_load( 'it-cache.php' );
	
	ITUtility::require_file_once( 'lib/import-export/init.php' );
	ITUtility::require_file_once( 'lib/theme-settings/init.php' );
	ITUtility::require_file_once( 'lib/layout-engine/init.php' );
	ITUtility::require_file_once( 'lib/widgets/init.php' );
	
	
	$file_cache = ( builder_theme_supports( 'builder-file-cache' ) ) ? true : false;
	
	$GLOBALS['builder_cache'] =& new ITCache( 'builder-core', array( 'enable_file_cache' => $file_cache ) );
	$GLOBALS['builder_cache']->add_content_type( 'javascript-footer', 'javascript-footer.js', 'text/javascript', array( 'async_load' => true ) );
	$GLOBALS['builder_cache']->add_content_filter( 'javascript', 'builder_filter_javascript_content' );
	$GLOBALS['builder_cache']->add_content_filter( 'javascript-footer', 'builder_filter_javascript_footer_content' );
	$GLOBALS['builder_cache']->add_content_filter( 'css', 'builder_filter_css_content' );
	
	
	// Add support for builder-3.0 if the Builder core theme is the active theme
	if ( builder_parent_is_active() )
		add_theme_support( 'builder-3.0' );
	
	
	if ( builder_theme_supports( 'builder-my-theme-menu' ) )
		ITUtility::require_file_once( 'lib/tutorials/tutorials.php' );
	
	// Compatibility check for pre-3.0 automatic-feed-links support
	if ( builder_theme_supports( 'automatic-feed-links' ) && version_compare( $wp_version, '2.9.7', '<=' ) && function_exists( 'automatic_feed_links' ) )
		automatic_feed_links();
	
	if ( builder_theme_supports( 'builder-extensions' ) )
		ITUtility::require_file_once( 'lib/extensions/init.php' );
	
	if ( builder_theme_supports( 'builder-billboard' ) )
		ITUtility::require_file_once( 'lib/billboard/billboard.php' );
	
	if ( builder_theme_supports( 'builder-feedburner-widget' ) )
		ITUtility::require_file_once( 'lib/feedburner-widget/feedburner-widget.php' );
	
	if ( builder_theme_supports( 'builder-plugin-features' ) )
		ITUtility::require_file_once( 'lib/plugin-features/init.php' );
	
	if ( builder_theme_supports( 'builder-3.0' ) ) {
		add_theme_support( 'loop-standard' );
		ITUtility::require_file_once( 'lib/loop-standard/functions.php' );
	}
	
//	ITUtility::require_file_once( 'lib/shortcodes/shortcodes.php' );
	
	
	if ( 'on' == builder_get_theme_setting( 'dashboard_favicon' ) )
		add_action( 'admin_enqueue_scripts', 'builder_add_favicon', 0 );
	
	
	do_action( 'builder_theme_features_loaded' );
}
add_action( 'it_libraries_loaded', 'it_builder_load_theme_features', -10 );


// Now the text widget supports shortcodes
add_filter( 'widget_text', 'do_shortcode' );


// Temporary Builder title function until Builder SEO can be finished
add_action( 'builder_add_title', 'builder_add_title' );

add_action( 'admin_print_styles', 'builder_add_global_admin_styles' );
add_action( 'comment_form_comments_closed', 'builder_print_closed_comments_message' );


add_filter( 'builder_filter_favicon_url', 'builder_filter_favicon_url' );
add_filter( 'it_filter_theme_menu_var', 'it_set_theme_menu_var' );
add_filter( 'it_storage_filter_theme_index', 'it_set_theme_index' );
add_filter( 'it_tutorials_top_menu_icon', 'filter_it_tutorials_top_menu_icon' );
add_filter( 'it_tutorials_filter_url', 'builder_set_start_here_url' );
add_filter( 'admin_body_class', 'builder_filter_admin_body_classes' );
