<?php

/*
Written by Chris Jean for iThemes.com
Version 1.0.1

Version History
	1.0.0 - 2010-12-15
		Release ready
	1.0.1 - 2011-02-22
		Set theme-settings data version to 1.0 using builder_set_data_version
		Check support for builder-my-theme-menu in order to load editor.php
		Add data source for BuilderDataSourceThemeSettings
		Added call to builder_theme_settings_loaded action
*/


builder_set_data_version( 'theme-settings', '1.0' );


/*
if ( current_theme_supports( 'builder-seo' ) ) {
	require_once( dirname( __FILE__ ) . '/defaults-seo.php' );
	require_once( dirname( __FILE__ ) . '/seo-frontend.php' );
	
	if ( is_admin() )
		require_once( dirname( __FILE__ ) . '/seo-post-editor.php' );
}
*/


require_once( dirname( __FILE__ ) . '/functions.php' );
require_once( dirname( __FILE__ ) . '/defaults.php' );

if ( is_admin() ) {
	if ( current_theme_supports( 'builder-my-theme-menu' ) )
		ITUtility::require_file_once( dirname( __FILE__ ) . '/editor.php' );
	
	builder_add_import_export_data_source( 'BuilderDataSourceThemeSettings', dirname( __FILE__ ) . '/class.builder-data-source-theme-settings.php' );
}

function builder_theme_settings_upgrade() {
	require_once( dirname( __FILE__ ) . '/upgrade.php' );
}
add_action( 'it_storage_do_upgrade_builder-theme-settings', 'builder_theme_settings_upgrade' );

function builder_theme_settings_load_javascript_cache_generators() {
	require_once( dirname( __FILE__ ) . '/generators/analytics.php' );
}
add_action( 'it_file_cache_prefilter_builder-core_javascript', 'builder_theme_settings_load_javascript_cache_generators' );


add_action( 'wp_head', 'builder_render_javascript_header_cache' );
add_action( 'wp_head', 'builder_render_css_cache' );
add_action( 'builder_layout_engine_render_container', 'builder_render_javascript_footer_cache', 20 );

add_action( 'wp_head', 'builder_render_header_tracking_code' );
add_action( 'builder_layout_engine_render_container', 'builder_render_footer_tracking_code', 20 );


builder_load_theme_settings( true );

do_action( 'builder_theme_settings_loaded' );
