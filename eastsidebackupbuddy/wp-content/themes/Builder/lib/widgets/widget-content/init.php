<?php

/*
Copyright 2010 iThemes (email: support@ithemes.com)

Written by Chris Jean
Version 1.0.1

Version History
	1.0.0 - 2010-10-05 - Chris Jean
		Release-ready
	1.0.1 - 2010-12-15 - Chris Jean
		Post type initialization code no longer requires the action hook
*/


if ( ! function_exists( 'get_post_type_object' ) )
	return;

require_once( 'widget.php' );


$post_type_files = glob( dirname( __FILE__ ) . '/post-types/*.php' );

foreach ( (array) $post_type_files as $post_type_file )
	require_once( $post_type_file );

?>
