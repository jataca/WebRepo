<?php

/*
Load plugin features.
Written by Chris Jean for iThemes.com
Version 1.1.0

Version History
	1.0.0 - 2011-05-19 - Chris Jean
		Release ready
	1.1.0 - 2011-08-04 - Chris Jean
		Added Shopp support
*/


require( dirname( __FILE__ ) . '/functions.php' );


global $builder_enabled_plugin_features;

$builder_enabled_plugin_features = array(
	'gravity-forms',
	'shopp',
);
$builder_enabled_plugin_features = apply_filters( 'builder_filter_enabled_plugin_features', $builder_enabled_plugin_features );


// Gravity Forms
if ( class_exists( 'RGForms' ) )
	builder_setup_plugin_features( 'gravity-forms' );

// Shopp
if ( class_exists( 'Shopp' ) )
	builder_setup_plugin_features( 'shopp' );
