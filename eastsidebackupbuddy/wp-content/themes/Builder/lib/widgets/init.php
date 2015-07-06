<?php

/*
Widget style functions and Builder-provided widget initialization code
Written by Chris Jean for iThemes.com
Version 1.0.1

Version History
	1.0.0 - 2010-10-05 - Chris Jean
		Release-ready
	1.0.1 - 2010-12-15 - Chris Jean
		Added builder_register_widget_style and builder_get_widget_styles
*/


if ( ! function_exists( 'builder_register_widget_style' ) ) {
	function builder_register_widget_style( $name, $selector ) {
		global $builder_widget_styles;
		
		if ( ! is_array( $builder_widget_styles ) )
			$builder_widget_styles = array();
		
		$builder_widget_styles[$selector] = $name;
	}
}

if ( ! function_exists( 'builder_get_widget_styles' ) ) {
	function builder_get_widget_styles() {
		global $builder_widget_styles;
		
		if ( ! is_array( $builder_widget_styles ) )
			$builder_widget_styles = array();
		
		asort( $builder_widget_styles );
		
		return $builder_widget_styles;
	}
}

/*if ( builder_theme_supports( 'builder-widget-styles' ) )
	ITUtility::require_file_once( 'lib/widgets/widget-styler.php' );*/


$directories = glob( dirname( __FILE__ ) . '/*', GLOB_ONLYDIR );

foreach ( (array) $directories as $directory ) {
	if ( file_exists( "$directory/init.php" ) && builder_theme_supports( 'builder-widget-' . basename( $directory ) ) )
		include_once( "$directory/init.php" );
}
