<?php

/*
Basic functions used by various parts of Builder
Written by Chris Jean for iThemes.com
Version 1.3.0

Version History
	1.0.0 - 2011-02-03 - Chris Jean
		Initial release version
	1.0.1 - 2011-03-09 - Chris Jean
		Added builder_set_start_here_url function
	1.1.0 - 2011-06-27 - Chris Jean
		Added:
			builder_add_favicon
			builder_filter_favicon_url
			builder_render_stylesheets
			builder_enqueue_tooltip_script
	1.1.1 - 2011-06-28 - Chris Jean
		Added:
			builder_add_doctype
			builder_add_html_tag
			builder_add_charset
			builder_add_meta_data
			builder_add_scripts
		Changed:
			builder_render_stylesheets to builder_add_stylesheets
	1.2.0 - 2011-07-05 - Chris Jean
		Added builder_filter_admin_body_classes
		Added builder_parent_is_active
	1.3.0 - 2011-08-04 - Chris Jean
		Updated builder_filter_favicon_url to use Favicon settings
*/


if ( ! function_exists( 'builder_set_minimum_memory_limit' ) ) {
	function builder_set_minimum_memory_limit( $memory_limit ) {
		require_once( ABSPATH . 'wp-admin/includes/template.php' );
		
		if ( wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) ) < wp_convert_hr_to_bytes( $memory_limit ) )
			@ini_set( 'memory_limit', $memory_limit );
	}
}

// Temporary Builder title function until Builder SEO can be finished
function builder_add_title() {
	$title = trim( wp_title( '', false ) );
	
	if( ! empty( $title ) )
		$title .= ' :: ';
	
	$title .= get_bloginfo( 'name' );
	
	
	$title = apply_filters( 'builder_filter_title', $title );
	
	echo "<title>$title</title>\n";
}

function builder_set_data_version( $name, $version ) {
	global $builder_data_versions;
	
	if ( ! isset( $builder_data_versions ) )
		$builder_data_versions = array();
	
	$builder_data_versions[$name] = $version;
}

function builder_get_data_version( $name ) {
	global $builder_data_versions;
	
	return ( isset( $builder_data_versions[$name] ) ) ? $builder_data_versions[$name] : false;
}

function it_set_theme_menu_var( $menu_var ) {
	return $GLOBALS['theme_menu_var'];
}

function it_set_theme_index( $theme_index ) {
	return $GLOBALS['theme_index'];
}

function filter_it_tutorials_top_menu_icon( $icon ) {
	it_classes_load( 'it-file-utility.php' );
	
	return ITFileUtility::get_url_from_file( dirname( __FILE__ ) . '/images/builder-icon-16-inactive.png' );
}

function builder_add_global_admin_styles() {
	it_classes_load( 'it-file-utility.php' );
	
	wp_enqueue_style( 'builder-global-admin-style', ITFileUtility::get_url_from_file( dirname( __FILE__ ) . '/css/admin-global.css' ) );
}

function builder_set_start_here_url( $url ) {
	global $builder_start_here_url;
	
	if ( ! empty( $builder_start_here_url ) )
		$builder_start_here_url = esc_url( $builder_start_here_url );
	
	if ( empty( $builder_start_here_url ) )
		return $url;
	
	return $builder_start_here_url;
}

function builder_add_doctype() {
	if ( current_theme_supports( 'html5' ) )
		$doctype = '<!DOCTYPE html>';
	else
		$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	
	$doctype = apply_filters( 'builder_filter_doctype', $doctype );
	
	echo "$doctype\n";
}

function builder_add_html_tag() {
	ob_start();
	language_attributes();
	$language_attributes = ob_get_contents();
	ob_end_clean();
	
	if ( current_theme_supports( 'html5' ) )
		$html_tag = "<html %s $language_attributes>";
	else
		$html_tag = "<html %s $language_attributes xmlns=\"http://www.w3.org/1999/xhtml\">";
	
	$html_tag = apply_filters( 'builder_filter_html_tag', $html_tag );
	$html_tag .= "\n";
	
?>
<!--[if IE 6]>
	<?php printf( $html_tag, 'id="ie6"' ); ?>
<![endif]-->
<!--[if IE 7]>
	<?php printf( $html_tag, 'id="ie7"' ); ?>
<![endif]-->
<!--[if IE 8]>
	<?php printf( $html_tag, 'id="ie8"' ); ?>
<![endif]-->
<!--[if IE 9]>
	<?php printf( $html_tag, 'id="ie9"' ); ?>
<![endif]-->
<!--[if (gt IE 9) | (!IE)  ]><!-->
	<?php printf( $html_tag, '' ); ?>
<!--<![endif]-->
<?php
	
}

function builder_add_charset() {
	if ( current_theme_supports( 'html5' ) )
		$charset = '<meta charset="' . get_bloginfo( 'charset' ) . '" />';
	else
		$charset = '<meta http-equiv="Content-Type" content="' . get_bloginfo( 'html_type' ) . '; charset=' . get_bloginfo( 'charset' ) . '" />';
	
	$charset = apply_filters( 'builder_filter_charset', $charset );
	
	echo "$charset\n";
}

function builder_add_meta_data() {
	echo '<link rel="profile" href="http://gmpg.org/xfn/11" />' . "\n";
	echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";
	
	do_action( 'builder_add_meta_data' );
}

function builder_add_stylesheets() {
	$layout = apply_filters( 'builder_get_current_layout', array() );
	
	echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/css/reset.css" type="text/css" media="screen" />' . "\n";
	
	if ( ! builder_disable_theme_stylesheets() )
		echo '<link rel="stylesheet" href="' . get_stylesheet_uri() . '" type="text/css" media="screen" />' . "\n";
	
	do_action( 'builder_add_stylesheets' );
	
	echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/css/structure.css" type="text/css" media="screen" />' . "\n";
}

if ( ! function_exists( 'builder_add_scripts' ) ) {
	function builder_add_scripts() {
		// Add comment reply JavaScript if page is singular
		if ( is_singular() )
			wp_enqueue_script( 'comment-reply' );
		
?>
<!--[if lt IE 7]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/dropdown.js" type="text/javascript"></script>
<![endif]-->
<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<?php
		
		do_action( 'builder_add_scripts' );
	}
}
else {
	require_once( dirname( dirname( __FILE__ ) ) . '/special-utilities/fix-builder_add_scripts-in-child-theme.php' );
}

function builder_add_favicon() {
	$favicon_url = apply_filters( 'builder_filter_favicon_url', '' );
	$favicon = '';
	
	if ( ! empty( $favicon_url ) )
		$favicon = "<link rel=\"shortcut icon\" href=\"$favicon_url\" />";
	
	apply_filters( 'builder_filter_favicon', $favicon );
	
	if ( ! empty( $favicon ) )
		echo "$favicon\n";
}

function builder_filter_favicon_url( $url ) {
	$favicon_option = builder_get_theme_setting( 'favicon_option' );
	
	if ( 'off' == $favicon_option )
		return $url;
	else if ( 'preset' == $favicon_option ) {
		$preset = builder_get_theme_setting( 'favicon_preset' );
		
		if ( ! empty( $preset ) )
			return get_template_directory_uri() . "/favicons/$preset.ico";
	}
	else if ( 'custom' == $favicon_option ) {
		$favicon = builder_get_theme_setting( 'favicon' );
		
		if ( is_array( $favicon ) && ! empty( $favicon['url'] ) )
			return $favicon['url'];
	}
	
	
	if ( file_exists( get_stylesheet_directory() . '/images/favicon.ico' ) )
		return get_stylesheet_directory_uri() . '/images/favicon.ico';
	else if ( file_exists( get_template_directory() . '/images/favicon.ico' ) )
		return get_template_directory_uri() . '/images/favicon.ico';
	
	return $url;
}

function builder_disable_theme_stylesheets() {
	return apply_filters( 'builder_filter_disable_theme_stylesheets', false );
}

function builder_enqueue_tooltip_script() {
	wp_enqueue_script( 'pluginbuddy-tooltip-js', ITFileUtility::url_from_file( dirname( __FILE__ ) . '/js/jquery.tooltip.js' ) );
}

function builder_filter_admin_body_classes( $classes = '' ) {
	global $wp_version;
	
	if ( version_compare( $wp_version, '3.2.0', '<' ) )
		$classes .= ' it-pre-wp-3-2';
	
	return $classes;
}

function builder_parent_is_active() {
	if ( TEMPLATEPATH == STYLESHEETPATH )
		return true;
	return false;
}
