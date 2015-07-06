<?php

/*
Written by Chris Jean for iThemes.com
Version 1.2.1

Version History
	1.0.0 - 2010-04-20
		Initial release version
	1.0.1 - 2010-06-02
		Updated builder_custom_caption_shortcode to fix
			caption output
	1.0.2 - 2010-07-16
		Added builder_get_custom_post_types function
	1.0.3 - 2010-08-24
		Added builder_register_module_style function
	1.1.0 - 2010-10-05
		Added functions:
			builder_get_module_styles
			builder_register_widget_style
			builder_get_widget_styles
	1.1.1 - 2010-12-14
		Added filter_it_tutorials_top_menu_icon and associated filter
		Switched not_found.php include to locate_template call
		Removed functions:
			builder_theme_file
			builder_theme_file_url
			builder_get_theme_option
			builder_register_widget_style
			builder_get_widget_styles
	1.1.2 - 2011-02-22
		Removed functions and associated add_filter calls:
			it_set_theme_menu_var
			it_set_theme_index
			filter_it_tutorials_top_menu_icon
	1.2.0 - 2011-07-05 - Chris Jean
		Added function builder_get_default_layouts
	1.2.1 - 2011-07-12 - Chris Jean
		Updated the serialize/unserialize process to use base64
			encoding/decoding to avoid system-specific read errors
*/


// Pass a module of * to register the style for all modules
if ( ! function_exists( 'builder_register_module_style' ) ) {
	function builder_register_module_style( $modules, $name, $selector ) {
		global $builder_module_styles;
		
		if ( ! is_array( $modules ) )
			$modules = array( $modules );
		if ( ! is_array( $builder_module_styles ) )
			$builder_module_styles = array();
		
		foreach ( (array) $modules as $module )
			$builder_module_styles[$module][$selector] = $name;
	}
}

if ( ! function_exists( 'builder_get_module_styles' ) ) {
	function builder_get_module_styles( $module = '' ) {
		global $builder_module_styles;
		
		if ( ! is_array( $builder_module_styles ) ) {
			$builder_module_styles = array();
			return array();
		}
		
		$styles = array();
		
		if ( is_array( $builder_module_styles['*'] ) )
			$styles = array_merge( $styles, $builder_module_styles['*'] );
		if ( ! empty( $module ) && is_array( $builder_module_styles[$module] ) )
			$styles = array_merge( $styles, $builder_module_styles[$module] );
		
		asort( $styles );
		
		return $styles;
	}
}


// Get current taxonomy term title
function builder_get_tax_term_title() {
	if ( is_tax() ) {
		global $wp_query;
		
		$term = $wp_query->get_queried_object();
		return $term->name;
	}
	
	return '';
}


// Get current post's author link
function builder_get_author_link() {
	global $post;
	
	if ( isset( $post ) )
		return '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '" title="' . esc_attr( get_the_author() ) . '">' . get_the_author() . '</a>';
	return '';
}


// Load the not_found.php file
function builder_template_show_not_found() {
	locate_template( array( 'not_found.php' ), true );
}
add_action( 'builder_template_show_not_found', 'builder_template_show_not_found' );


// Customize image shortcode output
// Built from version 2.9.2
function builder_custom_caption_shortcode( $output, $attr, $content ) {
	$defaults = array(
		'id'		=> '',
		'align'		=> 'alignnone',
		'width'		=> '',
		'caption'	=> '',
	);
	extract( shortcode_atts( $defaults, $attr ) );
	
	if ( 1 > (int) $width || empty( $caption ) )
		return $content;
	
	if ( ! empty( $id ) )
		$id = 'id="' . esc_attr( $id ) . '"';
	
	$align = esc_attr( $align );
	
	return "<div $id class='wp-caption $align' style='width:{$width}px;'>" . do_shortcode( $content ) . "<p class='wp-caption-text'>$caption</p></div>";
}
add_filter( 'img_caption_shortcode', 'builder_custom_caption_shortcode', 10, 3 );


// Customize post gallery output
// Built from version 2.9.2
function builder_custom_post_gallery( $output, $attr ) {
	global $post, $wp_locale;
	
	static $instance = 0;
	$instance++;
	
	
	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( empty( $attr['orderby'] ) )
			unset( $attr['orderby'] );
	}
	
	$defaults = array(
		'order'			=> 'ASC',
		'orderby'		=> 'menu_order ID',
		'id'			=> $post->ID,
		'itemtag'		=> 'dl',
		'icontag'		=> 'dt',
		'captiontag'	=> 'dd',
		'size'			=> 'thumbnail',
		'include'		=> '',
		'exclude'		=> ''
	);
	extract( shortcode_atts( $defaults, $attr ) );
	
	$id = intval( $id );
	if ( 'RAND' === $order )
		$orderby = 'none';
	
	if ( ! empty( $include ) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
		
		$attachments = array();
		foreach ( $_attachments as $key => $val )
			$attachments[$val->ID] = $_attachments[$key];
	}
	else if ( ! empty( $exclude ) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	}
	else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby ) );
	}
	
	if ( empty( $attachments ) )
		return '';
	
	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
		return $output;
	}
	
	$itemtag = tag_escape( $itemtag );
	$captiontag = tag_escape( $captiontag );
	$float = ( 'rtl' === $wp_locale->text_direction ) ? 'right' : 'left';
	
	$selector = "gallery-{$instance}";
	
	$output .= "<div class='gallery clearfix'>\n";
	
	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);
		
		if ( $captiontag && trim($attachment->post_excerpt) )
			$gallery_item_class = 'gallery-item gallery-item-with-caption';
		else
			$gallery_item_class = 'gallery-item';
		
		$output .= "<{$itemtag} class='$gallery_item_class'>";
		$output .= "
			<$icontag class='gallery-icon'>
				$link
			</$icontag>";
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<$captiontag class='gallery-caption'>
				" . wptexturize($attachment->post_excerpt) . "
				</$captiontag>";
		}
		$output .= "</{$itemtag}>";
	}
	
	$output .= "</div>\n";
	
	
	return $output;
}
add_filter( 'post_gallery', 'builder_custom_post_gallery', 10, 2 );


// Do smart comments_popup_link replacement
function builder_comments_popup_link( $before, $after, $format, $zero = '(0)', $one = '(1)', $multi = '(%)' ) {
	if ( ! builder_show_comments() || ( ! comments_open() && ! pings_open() ) || post_password_required() )
		return;
	
	ob_start();
	comments_popup_link( $zero, $one, $multi );
	$comments = ob_get_contents();
	ob_end_clean();
	
	echo $before;
	printf( $format, $comments );
	echo $after;
}
add_action( 'builder_comments_popup_link', 'builder_comments_popup_link', 10, 6 );


function builder_get_custom_post_types() {
	global $builder_custom_post_types;
	
	if ( isset( $builder_custom_post_types ) )
		return $builder_custom_post_types;
	
	
	global $wp_version;
	
	$builder_custom_post_types = array();
	
	if ( version_compare( $wp_version, '2.9.7', '>' ) ) {
		$custom_post_type_objects = get_post_types( array( 'show_ui' => true, '_builtin' => false ), false );
		
		foreach ( (array) $custom_post_type_objects as $post_type => $settings )
			$builder_custom_post_types[$post_type] = $settings->labels->name;
	}
	else if ( version_compare( $wp_version, '2.8.7', '>' ) ) {
		$custom_post_type_objects = get_post_types();
		$core_post_types = array( 'post', 'page', 'revision', 'attachment' );
		
		foreach ( (array) $custom_post_type_objects as $post_type => $settings ) {
			if ( ! in_array( $post_type, $core_post_types ) )
				$builder_custom_post_types[$post_type] = $post_type;
		}
	}
	
	return $builder_custom_post_types;
}

function builder_get_default_layouts( $layouts ) {
//	file_put_contents( dirname( __FILE__ ) . '/default-layouts.txt', base64_encode( serialize( $layouts ) ) );
	
	if ( ! empty( $layouts ) && is_array( $layouts ) && isset( $layouts['default'] ) )
		return $layouts;
	if ( ! is_array( $layouts ) )
		$layouts = array();
	
	$layouts = array();
	
	$defaults = unserialize( base64_decode( file_get_contents( dirname( __FILE__ ) . '/default-layouts.txt' ) ) );
	
	
	include_once( dirname( __FILE__ ) . '/upgrade-storage.php' );
	$data = apply_filters( 'it_storage_upgrade_layout_settings', array( 'data' => $defaults ) );
	$defaults = $data['data'];
	
	require_once( dirname( __FILE__ ) . '/class-builder-layout-settings-guid-randomizer.php' );
	$defaults = BuilderLayoutSettingsGUIDRandomizer::randomize_guids( $defaults );
	
	return ITUtility::merge_defaults( $layouts, $defaults );
}
