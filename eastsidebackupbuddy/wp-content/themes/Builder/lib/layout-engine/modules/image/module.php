<?php

/*
Written by Chris Jean for iThemes.com
Version 2.3.0

Version History
	See history.txt
*/



if ( ! class_exists( 'LayoutModuleImage' ) ) {
	class LayoutModuleImage extends LayoutModule {
		var $_name = '';
		var $_var = 'image';
		var $_description = '';
		var $_editor_width = '600';
		
		
		function LayoutModuleImage() {
			$this->_name = _x( 'Image', 'module', 'it-l10n-Builder' );
			$this->_description = __( 'This module adds an image to the layout. This can be used to add static headers or just something to spice up your layout.', 'it-l10n-Builder' );
			
			$this->LayoutModule();
		}
		
		function export( $data ) {
			if ( ! empty( $data['attachment'] ) && is_numeric( $data['attachment'] ) )
				do_action( 'builder_import_export_add_attachment', $data['attachment'] );
			
			return $data;
		}
		
		function import( $data, $attachments, $post_data ) {
			if ( ! empty( $data['attachment'] ) && is_numeric( $data['attachment'] ) ) {
				if ( isset( $attachments[$data['attachment']]['id'] ) )
					$data['attachment'] = $attachments[$data['attachment']]['id'];
				else
					$data['attachment'] = '';
			}
			
			return $data;
		}
		
/*		function get_layout_option() {
			return 'notused';
		}*/
		
		function _get_defaults( $defaults ) {
			$new_defaults = array(
//				'notused'     => 'image',
				'image'       => '',
				'attachment'  => '',
				'height_type' => 'auto',
				'height'      => '150',
				'url'         => '',
				'new_window'  => '',
				'sidebar'     => '',
			);
			
			return ITUtility::merge_defaults( $new_defaults, $defaults );
		}
		
		function _validate( $result ) {
			$result['data']['notused'] = 'image';
			
			it_classes_load( 'it-file-utility.php' );
			
			if ( ITFileUtility::file_uploaded( 'image' ) ) {
				$file = ITFileUtility::upload_file( 'image' );
				
				if ( is_wp_error( $file ) )
					$result['errors'][] = sprintf( __( "Unable to save uploaded image. Ensure that the web server has permissions to write to the uploads folder.\n\nMessage: %s", 'it-l10n-Builder' ), $file->get_error_message() );
				else {
					$result['data']['attachment'] = $file['id'];
					$_POST['attachment'] = $file['id'];
				}
			}
			else if ( empty( $_POST['attachment'] ) )
				$result['errors'][] = __( 'You must upload an image.', 'it-l10n-Builder' );
			
			
			if ( empty( $_POST['height'] ) || preg_match( '/[^0-9]/', $_POST['height'] ) )
				$result['errors'][] = __( 'You must enter an integer value for the Height.', 'it-l10n-Builder' );
			else if ( $_POST['height'] < 10 )
				$result['errors'][] = __( 'The minimum Height is 10 pixels. Please increase the Height.', 'it-l10n-Builder' );
			
			
			return $result;
		}
		
		function _before_table_edit( $form, $results = true ) {
			
?>
	<div id="image-already-uploaded-message" style="margin-bottom:10px;display:none;">
		<?php _e( 'An image has already been uploaded.', 'it-l10n-Builder' ); ?><br />
		<?php _e( '<a href="#" id="show-image-upload-row">Click here</a> to upload a new image.', 'it-l10n-Builder' ); ?>
	</div>
<?php
			
		}
		
		function _start_table_edit( $form, $results = true ) {
			$height_types = array(
				'auto'   => __( 'Automatic, use a height that preserves the image\'s aspect ratio', 'it-l10n-Builder' ),
				'custom' => __( 'Manual, select a height manually', 'it-l10n-Builder' ),
			);
			
			if ( ! isset( $form->_options['height_type'] ) )
				$form->set_option( 'height_type', '' );
			
?>
	<tr id="image-upload-row" style="display:none;">
		<td><?php _e( 'Upload Image', 'it-l10n-Builder' ); ?></td>
		<td>
			<?php $form->add_file_upload( 'image' ); ?>
		</td>
	</tr>
	<tr><td><label for="height_type"><?php _e( 'Height', 'it-l10n-Builder' ); ?></label></td>
		<td>
			<?php $form->add_drop_down( 'height_type', $height_types ); ?>
			<?php ITUtility::add_tooltip( __( 'If Manual is selected, the image will first be resized to fit the height and then cropped to fit the width.', 'it-l10n-Builder' ), '', 'left' ); ?>
			
			<div id="height_type-options">
				<p><?php _e( 'Manual height', 'it-l10n-Builder' ); ?> <?php $form->add_text_box( 'height', array( 'style' => 'width:50px;' ) ); ?> px</p>
			</div>
		</td>
	</tr>
	<tr><td valign="top"><label for="height"><?php _e( 'Link URL', 'it-l10n-Builder' ); ?></label></td>
		<td>
			<?php $form->add_text_box( 'url' ); ?>
			<?php ITUtility::add_tooltip( __( 'Make sure that you use complete URLs including the <code>http://</code> portion. If you leave this fied empty, no link URL will be used.', 'it-l10n-Builder' ) ); ?>
		</td>
	</tr>
	<tr><td valign="top"><label for="height"><?php _e( 'Link Opens New Window', 'it-l10n-Builder' ); ?></label></td>
		<td>
			<?php $form->add_check_box( 'new_window' ); ?>
		</td>
	</tr>
<?php
			
		}
		
		function _after_table_edit( $form, $results = true ) {
			
?>
	<?php $form->add_hidden( 'attachment' ); ?>
	<?php $form->add_hidden( 'notused' ); ?>
	
	<script type="text/javascript">
		/* <![CDATA[ */
		init_module_editor();
		
		imgLoader = new Image(); // preload image
		imgLoader.src = "../wp-includes/js/thickbox/loadingAnimation.gif";
		
		resize_thickbox_width("<?php echo $this->_editor_width; ?>");
		
		jQuery("form").submit(
			function() {
				jQuery("table").hide();
				jQuery("div").hide();
				resize_thickbox(300, 80);
				jQuery("body").append("<div id='submit_loader' style='position:fixed;top:10px;left:0px;width:100%;text-align:center;'><div style='font-weight:bold;'><?php _e( 'Uploading, please wait.', 'it-l10n-Builder' ); ?></div><br /><img src='"+imgLoader.src+"' /></div>");//add loader to the page
				jQuery('#submit_loader').show();//show loader
				return true;
			}
		);
		/* ]]> */
	</script>
<?php
			
		}
		
		function _render( $fields ) {
			$data = $fields['data'];
			
			if ( ! empty( $data['attachment'] ) ) {
				if ( ! wp_attachment_is_image( $data['attachment'] ) )
					return;
				
				$post = get_post( $data['attachment'] );
				
				$file = get_attached_file( $data['attachment'] );
			}
			else if ( ! empty( $data['manual_file'] ) ) {
				$file = TEMPLATEPATH . '/lib/layout-engine/default-images/' . $data['manual_file'];
				
				if ( ! file_exists( $file ) )
					return;
			}
			
			$image_width = $this->_widths['element_width'];
			
			
			it_classes_load( 'it-file-utility.php' );
			
			if ( 'custom' == $data['height_type'] )
				$resized_image = ITFileUtility::resize_image( $file, $image_width, $data['height'], true );
			else
				$resized_image = ITFileUtility::resize_image( $file, $image_width );
			
			if ( ! is_array( $resized_image ) && is_wp_error( $resized_image ) )
				echo "<!-- Resize Error: " . $resized_image->get_error_message() . " -->";
			else
				$image_url = $resized_image['url'];
			
			if ( ! empty( $data['url'] ) ) {
				$attributes['href'] = $data['url'];
				
				if ( ! empty( $data['new_window'] ) )
					$attributes['target'] = '_blank';
				
				ITUtility::print_open_tag( 'a', $attributes );
			}
			
			echo "<img src=\"$image_url\" alt=\"" . __( 'Layout Image', 'it-l10n-Builder' ) . "\" />";
			
			if ( ! empty( $data['url'] ) )
				echo "</a>";
		}
	}
	
	new LayoutModuleImage();
}
