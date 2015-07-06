<?php

if ( ! function_exists( 'builder_setup_plugin_features' ) ) {
	function builder_setup_plugin_features( $plugin ) {
		global $builder_enabled_plugin_features;
		if ( ! in_array( $plugin, $builder_enabled_plugin_features ) )
			return;
		
		$dir = "plugin-features/$plugin";
		
		locate_template( "$dir/init.php", true );
		
		if ( ! is_admin() ) {
			it_classes_load( 'it-file-utility.php' );
			
			$file = locate_template( "$dir/style.css" );
			if ( ! empty( $file ) ) {
				$url = ITFileUtility::get_url_from_file( $file );
				wp_enqueue_style( "builder-plugin-feature-$plugin-style", $url );
			}
			
			$file = locate_template( "$dir/script.js" );
			if ( ! empty( $file ) ) {
				$url = ITFileUtility::get_url_from_file( $file );
				wp_enqueue_script( "builder-plugin-feature-$plugin-script", $url );
			}
		}
	}
}
