<?php

/*
Written by Chris Jean of iThemes.com for Duct Tape Marketing
Version 1.0.0

Version History
	1.0.0 - 2010-10-05 - Chris Jean
		Release-ready
*/


if ( ! class_exists( 'ITPostTypeWidgetContent' ) ) {
	it_classes_load( 'it-post-type.php' );
	
	class ITPostTypeWidgetContent extends ITPostType {
		var $_file = __FILE__;
		
		var $_var = 'widget_content';
		var $_name = 'Widget Contents';
		var $_name_plural = 'Widget Content';
		
		var $_settings = array(
			'rewrite'	=> array(
				'slug'			=> 'post-type-widget-content',
			),
			'supports'				=> array( 'title', 'editor' ),
			'exclude_from_search'	=> true,
			'show_in_nav_menus'		=> false,
			'publicly_queryable'	=> false,
//			'menu_icon'	=> 'images/menu-item-icon-inactive.png',
		);
		
		function ITPostTypeWidgetContent() {
			ITPostTypeWidgetContent::ITPostType();
			
			add_filter( 'builder_layout_filter_non_layout_post_types', array( &$this, 'filter_non_layout_post_types' ) );
		}
		
		function filter_non_layout_post_types( $post_types ) {
			if ( ! in_array( $this->_var, $post_types ) )
				$post_types[] = $this->_var;
			
			return $post_types;
		}
	}
	
	new ITPostTypeWidgetContent();
}


?>
