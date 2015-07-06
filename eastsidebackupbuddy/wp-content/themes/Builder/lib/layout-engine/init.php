<?php

/*
Written by Chris Jean for iThemes.com
Version 2.4.0

Version History
	1.0.0 - 2009-12-02
		Release ready
	2.0.0 - 2010-01-07
		Changed layout_styles.php to extensions.php
	2.1.0 - 2010-01-18
		Added compat.php
	2.1.1 - 2010-12-14
		Removed compat.php
		Added functions.php
	2.1.2 - 2011-02-22
		Added builder_set_data_version call to set layout-settings version
		Check support for builder-my-theme-menu to load editor.php
		Added data source for BuilderDataSourceLayoutsViews
	2.2.0 - 2011-06-30 - Chris Jean
		Removed require for extensions.php
		Updated layout-settings data version to 1.4
	2.3.0 - 2011-07-01 - Chris Jean
		Updated layout-settings data version to 1.5
	2.4.0 - 2011-07-05 - Chris Jean
		Added filter to push to builder_get_default_layouts
		Updated layout-settings data version to 1.6
*/


builder_set_data_version( 'layout-settings', '1.6' );


ITUtility::require_file_once( dirname( __FILE__ ) . '/functions.php' );
ITUtility::require_file_once( dirname( __FILE__ ) . '/available-views.php' );
ITUtility::require_file_once( dirname( __FILE__ ) . '/modules.php' );
ITUtility::require_file_once( dirname( __FILE__ ) . '/sidebars.php' );

if ( builder_theme_supports( 'builder-default-layouts' ) )
	add_filter( 'it_storage_filter_load_layout_settings', 'builder_get_default_layouts', 0 );

if ( is_admin() ) {
	if ( current_theme_supports( 'builder-my-theme-menu' ) )
		ITUtility::require_file_once( dirname( __FILE__ ) . '/editor.php' );
	
	ITUtility::require_file_once( dirname( __FILE__ ) . '/add_layout_screen_options.php' );
	
	builder_add_import_export_data_source( 'BuilderDataSourceLayoutsViews', dirname( __FILE__ ) . '/class.builder-data-source-layouts-views.php' );
}
else
	ITUtility::require_file_once( dirname( __FILE__ ) . '/layout-engine.php' );
