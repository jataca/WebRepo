<?php

if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'it-portfolio-thumb', 350, 150, true );
}

// Custom image resize function by iThemes.com. iTeration 19
if ( !function_exists( 'it_builderchild_filter_image_downsize' ) && ! is_admin() ) {
	add_filter( 'image_downsize', 'it_builderchild_filter_image_downsize', 10, 3 ); // Latch in when a custom image size is called.
	add_filter( 'intermediate_image_sizes_advanced', 'it_builderchild_filter_image_downsize_blockextra', 10, 3 ); // Custom image size blocker to block generation of thumbs for sizes other sizes except when called.
	function it_builderchild_filter_image_downsize( $result, $id, $size ) {
		global $_ithemes_temp_downsize_size;
		if ( is_array( $size ) ) { // Dont bother with non-named sizes. Let them proceed normally. We need to set something to block the blocker though.
			$_ithemes_temp_downsize_size = 'array_size';
			return;
		}

		// Store current meta information and size data.
		global $_ithemes_temp_downsize_meta;
		$_ithemes_temp_downsize_size = $size;
		$_ithemes_temp_downsize_meta = wp_get_attachment_metadata( $id );

		if ( !is_array( $imagedata = wp_get_attachment_metadata( $id ) ) ) { return $result; }
		if ( !is_array( $size ) && !empty( $imagedata['sizes'][$size] ) ) {
			$data = $imagedata['sizes'][$size];
			// Some handling if the size defined for this size name has changed.
			global $_wp_additional_image_sizes;
			if ( empty( $_wp_additional_image_sizes[$size] ) ) { // Not a custom size so return data as is.
				$img_url = wp_get_attachment_url( $id );
				$img_url = path_join( dirname( $img_url ), $data['file'] );
				return array( $img_url, $data['width'], $data['height'], true );
			} else { // Custom size so only return if current image file dimensions match the defined ones.
				global $_wp_additional_image_sizes;
				if ( ( $_wp_additional_image_sizes[$size]['width'] == $data['width'] ) && ( $_wp_additional_image_sizes[$size]['height'] == $data['height'] ) ) { // Only return if size hasnt changed.
					$img_url = wp_get_attachment_url( $id );
					$img_url = path_join( dirname( $img_url ), $data['file'] );
					return array( $img_url, $data['width'], $data['height'], true );
				}
			}
		}

		require_once( ABSPATH . '/wp-admin/includes/image.php' );
		$uploads = wp_upload_dir();
		if ( !is_array( $uploads ) || ( false !== $uploads['error'] ) ) { return $result; }
		$file_path = "{$uploads['basedir']}/{$imagedata['file']}";

		// Image is resized within the function in the following line.
		$temp_meta_information = wp_generate_attachment_metadata( $id, $file_path ); // triggers filter_image_downsize_blockextra() function via filter within. generate images. returns new meta data for image (only includes the just-generated image size).

		$meta_information = $_ithemes_temp_downsize_meta; // Get the old original meta information.

		if ( array_key_exists( 'sizes', $temp_meta_information ) ) {
			$meta_information['sizes'][$_ithemes_temp_downsize_size] = $temp_meta_information['sizes'][$_ithemes_temp_downsize_size]; // Merge old meta back in.
		}
		wp_update_attachment_metadata( $id, $meta_information ); // Update image meta data.

		unset( $_ithemes_temp_downsize_size ); // Cleanup.
		unset( $_ithemes_temp_downsize_meta );

		return $result;
	}
	/* Prevents image resizer from resizing ALL images; just the currently requested size. */
	function it_builderchild_filter_image_downsize_blockextra( $sizes ) {
		global $_ithemes_temp_downsize_size;
		if ( empty( $_ithemes_temp_downsize_size ) || ( $_ithemes_temp_downsize_size == 'array_size' ) ) { // Dont bother with non-named sizes. Let them proceed normally.
			return $sizes;
		}
		$sizes = array( $_ithemes_temp_downsize_size => $sizes[$_ithemes_temp_downsize_size] ); // Strip out all extra meta data so only the requested size will be generated.
		return $sizes;
	}
}


?>