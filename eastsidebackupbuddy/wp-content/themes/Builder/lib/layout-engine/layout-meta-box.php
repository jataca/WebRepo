<?php

/*
Written by Chris Jean for iThemes.com
Version 2.0.7

Version History
	2.0.0 - 2010-01-07
		Removed returned $post_id in save_data
	2.0.1 - 2010-01-17
		Fixed the autosave of editors removing the custom layout
	2.0.2 - 2010-03-02
		Added 3.0.0-specific add_meta_boxes_* hooks for add_meta_box.
		Removed unnecessary '; text after meta box drop down.
	2.0.3 - 2010-04-20
		Removed unnecessary function_exists check for add_meta_box.
	2.0.4 - 2010-04-26
		Internationalized strings
	2.0.5 - 2010-07-16
		Improved registration to work for any WP version.
		Added layout selection meta box to 3.0 custom post types.
		Improved save handling.
	2.0.6 - 2010-07-22
		Removed add_post_meta call as only update_post_meta is needed.
			This caused the data to be added twice.
	2.0.7 - 2010-10-05
		Added filter builder_layout_filter_non_layout_post_types. This filter is
			used to remove the Custom Layout meta box from specific post types.
*/


if ( ! class_exists( 'LayoutMetaBox' ) ) {
	class LayoutMetaBox {
		var $_var = 'layout-meta-box';
		
		
		function LayoutMetaBox() {
			global $wp_version;
			
			if ( version_compare( $wp_version, '2.9.7', '>' ) )
				add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
			else
				add_action( 'admin_menu', array( &$this, 'add_meta_box' ) );
			
			add_action( 'save_post', array( &$this, 'save_data' ) );
		}
		
		function add_meta_box( $post_type = null ) {
			if ( ! empty( $post_type ) ) {
				$non_layout_post_types = array( 'link', 'comment', 'attachment', 'revision', 'nav_menu_item' );
				$non_layout_post_types = apply_filters( 'builder_layout_filter_non_layout_post_types', $non_layout_post_types );
				
				if ( ! in_array( $post_type, $non_layout_post_types ) )
					add_meta_box( 'layout_meta_box', 'Custom Layout', array( &$this, 'render_box' ), $post_type, 'side', 'low' );
			}
			else {
				add_meta_box( 'layout_meta_box', 'Custom Layout', array( &$this, 'render_box' ), 'post', 'side' );
				add_meta_box( 'layout_meta_box', 'Custom Layout', array( &$this, 'render_box' ), 'page', 'side' );
			}
		}
		
		function render_box( $post ) {
			$layout_data = apply_filters( 'it_storage_load_layout_settings', array() );
			
			$layouts = array( '' => '' );
			foreach ( (array) $layout_data['layouts'] as $layout => $layout_data )
				$layouts[$layout] = $layout_data['description'];
			
			$type = ( preg_match( '|page[^/]+$|', $_SERVER['REQUEST_URI'] ) ) ? 'page' : 'post';
			
			$data = array();
			if ( isset( $post->ID ) )
				$data['custom_layout'] = get_post_meta( $post->ID, '_custom_layout', true );
			
			$form =& new ITForm( $data, array( 'prefix' => $this->_var ) );
			
?>
	<p><strong><?php _e( 'Select a Layout:', 'it-l10n-Builder' ); ?></strong></p>
	<p><?php $form->add_drop_down( 'custom_layout', $layouts ); ?></p>
	<p><?php printf( __( 'Choosing a custom layout option here causes the chosen layout to be used for this %1$s. It will override any custom layout views you have configured in the <a href="%2$s">Layout Editor</a>.', 'it-l10n-Builder' ), $type, admin_url( 'admin.php?page=layout-editor' ) ); ?></p>
	<?php $form->add_hidden( 'nonce', wp_create_nonce( $this->_var ) ); ?>
<?php
			
		}
		
		function save_data( $post_id ) {
			// Skip if the nonce check fails
			if ( ! isset( $_POST["{$this->_var}-nonce"] ) || ! wp_verify_nonce( $_POST["{$this->_var}-nonce"], $this->_var ) )
				return;
			
			// Don't save or update on autosave
			if ( defined( 'DOING_AUTOSAVE' ) && ( true === DOING_AUTOSAVE ) )
				return;
			
			// Only allow those with permissions to modify the type to save/update a layout
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
			
			
			// Finally, time to do some real work
			if ( ! empty( $_POST["{$this->_var}-custom_layout"] ) )
				update_post_meta( $post_id, '_custom_layout', $_POST["{$this->_var}-custom_layout"] );
			else
				delete_post_meta( $post_id, '_custom_layout' );
		}
	}
	
	new LayoutMetaBox();
}
