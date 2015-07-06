<?php

/*
Written by Chris Jean for iThemes.com
Version 0.0.1

Version History
	0.0.1 - 2010-12-20 - Chris Jean
		Initial test version
*/


builder_set_data_version( 'builder-exports', '1.0' );


if ( ! function_exists( 'builder_add_import_export_settings_tab' ) ) {
	function builder_add_import_export_settings_tab() {
		builder_add_settings_tab( __( 'Import / Export', 'it-l10n-Builder' ), 'import-export', 'ITThemeSettingsTabImportExport', dirname( __FILE__ ) . '/settings-tab.php' );
	}
}

if ( current_theme_supports( 'builder-import-export' ) )
	add_action( 'builder_theme_settings_loaded', 'builder_add_import_export_settings_tab' );


if ( ! function_exists( 'builder_add_import_export_data_source' ) ) {
	function builder_add_import_export_data_source( $class, $file = null ) {
		global $builder_import_export_data_sources;
		
		if ( ! is_array( $builder_import_export_data_sources ) )
			$builder_import_export_data_sources = array();
		
		$builder_import_export_data_sources[] = compact( 'class', 'file' );
	}
}

if ( ! function_exists( 'builder_import_export_cleanup' ) ) {
	function builder_import_export_cleanup( $guid, $path ) {
		require_once( dirname( __FILE__ ) . '/class.builder-import-export.php' );
		
		BuilderImportExport::cleanup( $guid, $path );
	}
	add_action( 'builder_import_export_cleanup', 'builder_import_export_cleanup', 10, 2 );
}
