<?php

/*
Interface class for all import export data source classes

Written by Chris Jean for iThemes.com
Version 0.0.1

Version History
	0.0.1 - 2010-12-20 - Chris Jean
		Initial version
*/


if ( ! class_exists( 'BuilderDataSourceThemeSettings' ) ) {
	class BuilderDataSourceThemeSettings extends BuilderDataSource {
		function get_name() {
			return 'Theme Settings';
		}
		
		function get_var() {
			return 'theme-settings';
		}
		
		function get_version() {
			return builder_get_data_version( 'theme-settings' );
		}
		
		function get_export_data() {
			$storage =& new ITStorage2( 'builder-theme-settings', $this->get_version() );
			$settings = $storage->load();
			
			return $settings;
		}
		
		function run_import( $info, $data, $post_data, $attachments ) {
			$storage =& new ITStorage2( 'builder-theme-settings', $this->get_version() );
			$settings = $storage->load();
			
			$storage->save( $data );
			
			return true;
		}
	}
}
