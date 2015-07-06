<?php

/*
Written by Chris Jean for iThemes.com
Version 2.1.0

Version History
	1.4.6 - 2009-07-14
		Merged into classes directory
	1.4.7 - 2010-07-15
		Improved get_file_from_url and get_url_from_file to better handle
			WordPress 3.0 multisite paths and URLs.
	1.5.0 - 2010-12-14
		Added ITFileUtility functions:
			write
			get_writable_uploads_directory
			find_writable_path
			create_writable_path
			create_writable_file
			get_file_listing
			mkdir
			copy
			delete_directory
		Added compat function sys_get_temp_dir
	2.0.0 - 2011-02-22
		Complete rewrite
	2.0.1 - 2011-05-16 - Chris Jean
		Added require_existing arg to get_writable_file
	2.1.0 - 2011-08-04 - Chris Jean
		Added an argument to upload_file to prevent the file from being added to the media library
		Added require_existing arg to get_writable_directory to keep in line with API for get_writable_file
		Added create_favicon
*/


if ( !class_exists( 'ITFileUtility' ) ) {
	class ITFileUtility {
		function file_uploaded( $file_id ) {
			if ( ! empty( $_FILES[$file_id] ) && ( '4' != $_FILES[$file_id]['error'] ) )
				return true;
			return false;
		}
		
		function add_to_media_library( $file, $args = array() ) {
			if ( is_bool( $args ) )
				$args = array( 'move_to_uploads' => false );
			
			$default_args = array(
				'move_to_uploads' => true,
				'url'             => null,
				'type'            => null,
				'name'            => null,
				'title'           => null,
				'content'         => null,
				'attachment_id'   => null,
			);
			$args = array_merge( $default_args, $args );
			
			
			if ( is_null( $args['name'] ) )
				$args['name'] = basename( $file );
			
			if ( is_null( $args['type'] ) ) {
				$wp_filetype = wp_check_filetype_and_ext( $file, $file );
				
				if ( false !== $wp_filetype['proper_filename'] )
					$args['name'] = $wp_filetype['proper_filename'];
				
				if ( false !== $wp_filetype['type'] )
					$args['type'] = $wp_filetype['type'];
				else
					$args['type'] = '';
			}
			
			if ( true === $args['move_to_uploads'] ) {
				$uploads = wp_upload_dir();
				
				if ( ! is_array( $uploads ) || ( false !== $uploads['error'] ) )
					return false;
				
				$filename = wp_unique_filename( $uploads['path'], $args['name'] );
				
				$new_file = "{$uploads['path']}/$filename";
				
				if ( false === @copy( $file, $new_file ) )
					return false;
				
				$stat = stat( dirname( $new_file ));
				$perms = $stat['mode'] & 0000666;
				@chmod( $new_file, $perms );
				
				$args['url'] = "{$uploads['url']}/$filename";
				$file = $new_file;
				
				if ( is_multisite() )
					delete_transient( 'dirsize_cache' );
			}
			
			if ( is_null( $args['url'] ) )
				$args['url'] = ITFileUtility::get_url_from_file( $file );
			
			if ( is_null( $args['title'] ) )
				$title = preg_replace( '/\.[^.]+$/', '', basename( $file ) );
			
			if ( is_null( $args['content'] ) )
				$args['content'] = '';
			
			
			if ( false !== ( $image_meta = @wp_read_image_metadata( $file ) ) ) {
				if ( '' !== trim( $image_meta['title'] ) )
					$args['title'] = $image_meta['title'];
				if ( '' !== trim( $image_meta['caption'] ) )
					$args['content'] = $image_meta['caption'];
			}
			
			
			$attachment = array(
				'post_mime_type' => $args['type'],
				'guid'           => $args['url'],
				'post_title'     => $args['title'],
				'post_content'   => $args['content'],
			);
			
			$id = wp_insert_attachment( $attachment, $file );
			if ( !is_wp_error( $id ) )
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
			
			
			$data = array(
				'id'      => $id,
				'file'    => $file,
				'url'     => $args['url'],
				'type'    => $args['type'],
				'title'   => $args['title'],
				'caption' => $args['content'],
			);
			
			return $data;
		}
		
		function upload_file( $file_id, $add_to_media_library = true ) {
			$overrides = array( 'test_form' => false );
			$file = wp_handle_upload( $_FILES[$file_id], $overrides );
			
			if ( isset( $file['error'] ) )
				return new WP_Error( 'upload_error', $file['error'] );
			
			
			$args = array(
				'move_to_uploads' => false,
				'url'             => $file['url'],
				'type'            => $file['type'],
			);
			
			if ( $add_to_media_library )
				return ITFileUtility::add_to_media_library( $file['file'], $args );
			else
				return $file;
		}
		
		function get_image_dimensions( $file ) {
			if ( is_numeric( $file ) ) {
				$file_info = ITFileUtility::get_file_attachment( $file );
				
				if ( false === $file_info )
					return new WP_Error( 'error_loading_image_attachment', "Could not find requested file attachment ($file)" );
				
				$file = $file_info['file'];
			}
			
			list ( $width, $height, $type ) = getimagesize( $file );
			
			return array( $width, $height );
		}
		
		function resize_image( $file, $max_w = 0, $max_h = 0, $crop = true, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {
			if ( is_numeric( $file ) ) {
				$file_info = ITFileUtility::get_file_attachment( $file );
				
				if ( false === $file_info )
					return new WP_Error( 'error_loading_image_attachment', "Could not find requested file attachment ($file)" );
				
				$file = $file_info['file'];
			}
			else
				$file_attachment_id = '';
			
			if ( preg_match( '/\.ico$/', $file ) )
				return array( 'file' => $file, 'url' => ITFileUtility::get_url_from_file( $file ), 'name' => basename( $file ) );
			
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			
			$image = wp_load_image( $file );
			if ( ! is_resource( $image ) )
				return new WP_Error( 'error_loading_image', $image );
			
			list( $orig_w, $orig_h, $orig_type ) = getimagesize( $file );
			$dims = ITFileUtility::_image_resize_dimensions( $orig_w, $orig_h, $max_w, $max_h, $crop );
			if ( ! $dims )
				return new WP_Error( 'error_resizing_image', "Could not resize image" );
			list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;
			
			
			if ( ( $orig_w == $dst_w ) && ( $orig_h == $dst_h ) )
				return array( 'file' => $file, 'url' => ITFileUtility::get_url_from_file( $file ), 'name' => basename( $file ) );
			
			if ( ! $suffix )
				$suffix = "resized-image-${dst_w}x${dst_h}";
			
			$info = pathinfo( $file );
			$dir = $info['dirname'];
			$ext = $info['extension'];
			$name = basename( $file, ".${ext}" );
			
			if ( ! is_null( $dest_path ) && $_dest_path = realpath( $dest_path ) )
				$dir = $_dest_path;
			$destfilename = "${dir}/${name}-${suffix}.${ext}";
			
			
			if ( file_exists( $destfilename ) ) {
				if ( filemtime( $file ) > filemtime( $destfilename ) )
					unlink( $destfilename );
				else
					return array( 'file' => $destfilename, 'url' => ITFileUtility::get_url_from_file( $destfilename ), 'name' => basename( $destfilename ) );
			}
			
			
			// ImageMagick cannot resize animated PNG files yet, so this only works for
			// animated GIF files.
			$animated = false;
			if ( ITFileUtility::is_animated_gif( $file ) ) {
				$coalescefilename = "${dir}/${name}-coalesced-file.${ext}";
				
				if ( ! file_exists( $coalescefilename ) )
					system( "convert $file -coalesce $coalescefilename" );
				
				if ( file_exists( $coalescefilename ) ) {
					system( "convert -crop ${src_w}x${src_h}+${src_x}+${src_y}! $coalescefilename $destfilename" );
					
					if ( file_exists( $destfilename ) ) {
						system( "mogrify -resize ${dst_w}x${dst_h} $destfilename" );
						system( "convert -layers optimize $destfilename" );
						
						$animated = true;
					}
				}
			}
			
			
			if ( ! $animated ) {
				$newimage = imagecreatetruecolor( $dst_w, $dst_h );
				
				// preserve PNG transparency
				if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) ) {
					imagealphablending( $newimage, false );
					imagesavealpha( $newimage, true );
				}
				
				imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
				
				// we don't need the original in memory anymore
				if ( $orig_type == IMAGETYPE_GIF ) {
					if ( ! imagegif( $newimage, $destfilename ) )
						return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
				}
				elseif ( $orig_type == IMAGETYPE_PNG ) {
					if ( ! imagepng( $newimage, $destfilename ) )
						return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
				}
				else {
					// all other formats are converted to jpg
					$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
					if ( ! imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality ) ) )
						return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
				}
				
				imagedestroy( $newimage );
			}
			
			imagedestroy( $image );
			
			
			// Set correct file permissions
			$stat = stat( dirname( $destfilename ) );
			$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
			@ chmod( $destfilename, $perms );
			
			
			return array( 'file' => $destfilename, 'url' => ITFileUtility::get_url_from_file( $destfilename ), 'name' => basename( $destfilename ) );
		}
		
		// Customized image_resize_dimensions() from 2.6.3 wp-admin/includes/media.php (cheanged to resize to fill on crop)
		function _image_resize_dimensions( $orig_w, $orig_h, $dest_w = 0, $dest_h = 0, $crop = false ) {
			if ( ( $orig_w <= 0 ) || ( $orig_h <= 0 ) )
				return new WP_Error ( 'error_resizing_image', "Supplied invalid original dimensions ($orig_w, $orig_h)" );
			if ( ( $dest_w < 0 ) || ( $dest_h < 0 ) )
				return new WP_Error ( 'error_resizing_image', "Supplied invalid destination dimentions ($dest_w, $dest_h)" );
			
			
			if ( ( $dest_w == 0 ) || ( $dest_h == 0 ) )
				$crop = false;
			
			
			$new_w = $dest_w;
			$new_h = $dest_h;
			
			$s_x = 0;
			$s_y = 0;
			
			$crop_w = $orig_w;
			$crop_h = $orig_h;
			
			
			if ( $crop ) {
				$cur_ratio = $orig_w / $orig_h;
				$new_ratio = $dest_w / $dest_h;
				
				if ( $cur_ratio > $new_ratio ) {
					$crop_w = floor( $orig_w / ( ( $dest_h / $orig_h ) / ( $dest_w / $orig_w ) ) );
					$s_x = floor( ( $orig_w - $crop_w ) / 2 );
				}
				elseif ( $new_ratio > $cur_ratio ) {
					$crop_h = floor( $orig_h / ( ( $dest_w / $orig_w ) / ( $dest_h / $orig_h ) ) );
					$s_y = floor( ( $orig_h - $crop_h ) / 2 );
				}
			}
			else
				list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
			
			
			return array( 0, 0, $s_x, $s_y, $new_w, $new_h, $crop_w, $crop_h );
		}
		
		function get_url_from_file( $file ) {
			$url = '';
			
			if ( ( $uploads = wp_upload_dir() ) && ( false === $uploads['error'] ) ) {
				if ( 0 === strpos( $file, $uploads['basedir'] ) )
					$url = str_replace( $uploads['basedir'], $uploads['baseurl'], $file );
				else if ( false !== strpos( $file, 'wp-content/uploads' ) )
					$url = $uploads['baseurl'] . substr( $file, strpos( $file, 'wp-content/uploads' ) + 18 );
			}
			
			if ( empty( $url ) )
				$url = get_option( 'siteurl' ) . str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $file ) );
			
			return $url;
		}
		
		function get_file_from_url( $url ) {
			$file = '';
			
			if ( ( $uploads = wp_upload_dir() ) && ( false === $uploads['error'] ) ) {
				if ( 0 === strpos( $url, $uploads['baseurl'] ) )
					$file = str_replace( $uploads['baseurl'], $uploads['basedir'], $url );
				else if ( false !== strpos( $url, 'wp-content/uploads' ) )
					$file = $uploads['basedir'] . substr( $url, strpos( $url, 'wp-content/uploads' ) + 18 );
			}
			
			if ( empty( $file ) )
				$file = ABSPATH . get_option( 'upload_path' ) . '/' . ltrim( $url, get_option( 'siteurl' ) . '/files' );
			
			return $file;
		}
		
		function get_mime_type( $file ) {
			if ( preg_match( '|^https?://|', $file ) )
				$file = get_file_from_url( $file );
			
			return mime_content_type( $file );
		}
		
		function get_file_attachment( $id ) {
			if ( wp_attachment_is_image( $id ) ) {
				$post = get_post( $id );
				
				$file = array();
				$file['ID'] = $id;
				$file['file'] = get_attached_file( $id );
				$file['url'] = wp_get_attachment_url( $id );
				$file['title'] = $post->post_title;
				$file['name'] = basename( get_attached_file( $id ) );
				
				return $file;
			}
			
			return false;
		}
		
		function delete_file_attachment( $id ) {
			if ( wp_attachment_is_image( $id ) ) {
				$file = get_attached_file( $id );
				
				$info = pathinfo( $file );
				$ext = $info['extension'];
				$name = basename( $file, ".$ext" );
				
				
				if ( $dir = opendir( dirname( $file ) ) ) {
					while ( false !== ( $filename = readdir( $dir ) ) ) {
						if ( preg_match( "/^$name-resized-image-\d+x\d+\.$ext$/", $filename ) )
							unlink( dirname( $file ) . '/' . $filename );
						elseif ( "$name-coalesced-file.$ext" === $filename )
							unlink( dirname( $file ) . '/' . $filename );
					}
					
					closedir( $dir );
				}
				
				unlink( $file );
				
				
				return true;
			}
			
			return false;
		}
		
		// Can only detect animated GIF files, which is fine because ImageMagick doesn't seem
		// to be able to resize animated PNG (MNG) files yet.
		function is_animated_gif( $file ) {
			$filecontents=file_get_contents($file);
			
			$str_loc=0;
			$count=0;
			while ($count < 2) # There is no point in continuing after we find a 2nd frame
			{
				$where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
				if ($where1 === FALSE)
				{
					break;
				}
				else
				{
					$str_loc=$where1+1;
					$where2=strpos($filecontents,"\x00\x2C",$str_loc);
					if ($where2 === FALSE)
					{
						break;
					}
					else
					{
						if ($where1+8 == $where2)
						{
							$count++;
						}
						$str_loc=$where2+1;
					}
				}
			}
			
			if ($count > 1)
				return(true);
			return(false);
		}
		
		function write( $path, $content, $args = array() ) {
			if ( is_bool( $args ) )
				$args = array( 'append' => $args );
			else if ( is_int( $args ) )
				$args = array( 'permissions' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'append'      => false,
				'permissions' => 0644,
			);
			$args = array_merge( $default_args, $args );
			
			
			$mode = ( false === $args['append'] ) ? 'w' : 'a';
			
			if ( ! is_dir( dirname( $path ) ) ) {
				ITFileUtility::mkdir( dirname( $path ) );
				
				if ( ! is_dir( dirname( $path ) ) )
					return false;
			}
			
			$created = ! is_file( $path );
			
			if ( false === ( $handle = fopen( $path, $mode ) ) )
				return false;
			
			$result = fwrite( $handle, $content );
			fclose( $handle );
			
			if ( false === $result )
				return false;
			
			if ( ( true === $created ) && is_int( $args['append'] ) )
				@chmod( $path, $args['append'] );
			
			return true;
		}
		
		function is_file_writable( $path ) {
			return ITFileUtility::write( $path, '', array( 'append' => true ) );
		}
		
		function locate_file( $args ) {
			if ( is_string( $args ) )
				$args = array( 'glob_pattern' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'glob_pattern'         => null,
				'regex_pattern'        => null,   // The regex_pattern can refine results from glob
				'default_search_paths' => array( 'uploads_basedir', 'uploads_path', 'wp-content', 'abspath' ),
				'custom_search_paths'  => array(),
				'locate_all_matches'   => true,
				'type'                 => 'all',  // all, file, dir
			);
			$args = array_merge( $default_args, $args );
			
			if ( is_null( $args['glob_pattern'] ) )
				return new WP_Error( 'locate_file_no_name', 'The call to ITFileUtility::locate_file is missing the glob_pattern attribute' );
			
			
			$uploads = wp_upload_dir();
			
			if ( ! is_array( $uploads ) || ( false !== $uploads['error'] ) )
				$uploads = array( 'basedir' => false, 'path' => false );
			
			
			$default_search_paths = array(
				'uploads_basedir' => $uploads['basedir'],
				'uploads_path'    => $uploads['path'],
				'wp-content'      => WP_CONTENT_DIR,
				'abspath'         => ABSPATH,
			);
			
			
			$search_paths = array_merge( (array) $args['custom_search_paths'], (array) $args['default_search_paths'] );
			
			$results = array();
			
			foreach ( (array) $search_paths as $search_path ) {
				if ( isset( $default_search_paths[$search_path] ) ) {
					if ( false === $default_search_paths[$search_path] )
						continue;
					
					$search_path = $default_search_paths[$search_path];
				}
				
				if ( is_dir( $search_path ) ) {
					$files = glob( "$search_path/{$args['glob_pattern']}" );
					
					foreach ( (array) $files as $file ) {
						if ( ! is_null( $args['regex_pattern'] ) && ! preg_match( $args['regex_pattern'], $file ) )
							continue;
						
						if ( ( 'dir' === $args['type'] ) && ! is_dir( $file ) )
							continue;
						if ( ( 'file' === $args['type'] ) && ! is_file( $file ) )
							continue;
						
						$results[] = $file;
					}
					
					if ( ! empty( $results ) && ( true !== $args['locate_all_matches'] ) ) {
						if ( 1 === count( $results ) )
							return $results[0];
						return $results;
					}
				}
			}
			
			if ( empty( $results ) )
				return new WP_Error( 'locate_file_failed', 'Unable to locate requested file' );
			if ( 1 === count ( $results ) )
				return $results[0];
			
			return $results;
		}
		
		function get_writable_directory( $args ) {
			if ( is_string( $args ) )
				$args = array( 'name' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'name'                 => '',
				'create_new'           => false,  // Indicates if a new file is needed. Often used with rename set to true.
				'rename'               => false,  // Generates a new name if a file already exists with the specified name. Not used if create_new is false.
				'require_existing'     => false,  // Set to true to throw an error if the file does not already exist.
				'random'               => false,  // Generate a random name to be used
				'permissions'          => 0755,
				'default_search_paths' => array( 'uploads_basedir', 'uploads_path', 'wp-content', 'abspath' ),
				'custom_search_paths'  => array(),
			);
			$args = array_merge( $default_args, $args );
			
			
			if ( empty( $args['name'] ) && ( true === $args['create_new'] ) && ( false === $args['random'] ) )
				return new WP_Error( 'get_writable_directory_no_name', 'The call to ITFileUtility::get_writable_directory is missing the name attribute' );
			
			
			$uploads = wp_upload_dir();
			
			if ( ! is_array( $uploads ) || ( false !== $uploads['error'] ) )
				$uploads = array( 'basedir' => false, 'path' => false );
			
			
			$default_search_paths = array(
				'uploads_basedir' => $uploads['basedir'],
				'uploads_path'    => $uploads['path'],
				'wp-content'      => WP_CONTENT_DIR,
				'abspath'         => ABSPATH,
			);
			
			
			$search_paths = array_merge( (array) $args['custom_search_paths'], (array) $args['default_search_paths'] );
			$path = false;
			
			foreach ( (array) $search_paths as $search_path ) {
				if ( isset( $default_search_paths[$search_path] ) ) {
					if ( false === $default_search_paths[$search_path] )
						continue;
					
					$search_path = $default_search_paths[$search_path];
				}
				
				if ( is_dir( $search_path ) && is_writable( $search_path ) ) {
					$path = $search_path;
					break;
				}
			}
			
			
			if ( false === $path )
				return new WP_Error( 'get_writable_base_directory_failed', 'Unable to find a writable base directory' );
			
			if ( empty( $args['name'] ) && ( false === $args['random'] ) )
				return $path;
			
			
			if ( true === $args['random'] ) {
				$name = ( isset( $args['name'] ) ) ? $args['name'] : '';
				$uid = uniqid( "$name-", true );
				
				while ( is_dir( "$path/$uid" ) )
					$uid = uniqid( "$name-", true );
				
				$name = $uid;
			}
			else
				$name = $args['name'];
			
			if ( is_dir( "$path/$name" ) ) {
				if ( true === $args['create_new'] ) {
					if ( false === $args['rename'] )
						return new WP_Error( 'get_writable_directory_no_rename', 'Unable to create the named writable directory' );
					
					$name = ITFileUtility::get_unique_name( $path, $name );
				}
				else {
					if ( is_writable( "$path/$name" ) )
						return "$path/$name";
					
					return new WP_Error( 'get_writable_directory_cannot_write', 'Required directory exists but is not writable' );
				}
			}
			else if ( true === $args['require_existing'] )
				return new WP_Error( 'get_writable_directory_does_not_exist', 'Required writable directory does not exist' );
		
			if ( true === ITFileUtility::mkdir( "$path/$name", $args['permissions'] ) )
				return "$path/$name";
			
			return new WP_Error( 'get_writable_directory_failed', 'Unable to create a writable directory' );
		}
		
		function create_writable_directory( $args ) {
			if ( is_string( $args ) )
				$args = array( 'name' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'create_new' => true,
				'rename'     => true,
				'random'     => false,
			);
			$args = array_merge( $default_args, $args );
			
			return ITFileUtility::get_writable_directory( $args );
		}
		
		function get_writable_file( $args, $extension = null ) {
			if ( is_string( $args ) )
				$args = array( 'name' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'name'                 => '',
				'extension'            => '',
				'create_new'           => false,  // Indicates if a new file is needed. Often used with rename set to true.
				'rename'               => false,  // Generates a new name if a file already exists with the specified name. Not used if create_new is false.
				'require_existing'     => false,  // Set to true to throw an error if the file does not already exist.
				'permissions'          => 0644,
				'path_permissions'     => 0755,
				'default_search_paths' => array( 'uploads_basedir', 'uploads_path', 'wp-content', 'abspath' ),
				'custom_search_paths'  => array(),
			);
			$args = array_merge( $default_args, $args );
			
			if ( empty( $args['name'] ) )
				return new WP_Error( 'get_writable_file_no_name', 'The call to ITFileUtility::get_writable_file is missing the name attribute' );
			
			if ( is_null( $extension ) )
				$extension = $args['extension'];
			
			if ( ! empty( $extension ) && ! preg_match( '/^\./', $extension ) )
				$extension = ".$extension";
			
			
			$base_path = ITFileUtility::get_writable_directory( array( 'permissions' => $args['path_permissions'], 'default_search_paths' => $args['default_search_paths'], 'custom_search_paths' => $args['custom_search_paths'] ) );
			
			if ( is_wp_error( $base_path ) )
				return $base_path;
			
			
			$name = $args['name'];
			
			if ( preg_match( '|/|', $name ) ) {
				$base_path .= '/' . dirname( $name );
				$name = basename( $name );
			}
			
			
			$file = "$base_path/$name$extension";
			
			
			if ( is_file( $file ) ) {
				if ( true === $args['create_new'] ) {
					if ( false === $args['rename'] )
						return new WP_Error( 'get_writable_file_no_rename', 'Unable to create the named writable file' );
					
					$name = ITFileUtility::get_unique_name( $base_path, $name, $extension );
					$file = "$base_path/$name";
				}
				else {
					if ( is_writable( $file ) )
						return $file;
					
					return new WP_Error( 'get_writable_file_cannot_write', 'Required file exists but is not writable' );
				}
			}
			else if ( true === $args['require_existing'] )
				return new WP_Error( 'get_writable_file_does_not_exist', 'Required writable file does not exist' );
			
			if ( true === ITFileUtility::is_file_writable( $file ) ) {
				@chmod( $file, $args['permissions'] );
				return $file;
			}
			
			return new WP_Error( 'get_writable_file_failed', 'Unable to create a writable file' );
		}
		
		function create_writable_file( $args, $extension = null ) {
			if ( is_string( $args ) )
				$args = array( 'name' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'create_new' => true,
				'rename'     => true,
			);
			$args = array_merge( $default_args, $args );
			
			return ITFileUtility::get_writable_file( $args, $extension );
		}
		
		function get_writable_uploads_directory( $directory ) {
			$uploads = wp_upload_dir();
			
			if ( ! is_array( $uploads ) || ( false !== $uploads['error'] ) )
				return false;
			
			
			$path = "{$uploads['basedir']}/$directory";
			
			if ( ! is_dir( $path ) ) {
				ITFileUtility::mkdir( $path );
				
				if ( ! is_dir( $path ) )
					return false;
			}
			if ( ! is_writable( $path ) )
				return false;
			
			$directory_info = array(
				'path' => $path,
				'url'  => "{$uploads['baseurl']}/$directory",
			);
			
			return $directory_info;
		}
		
		function find_writable_path( $args = array(), $vars = array() ) {
			$default_args = array(
				'private'        => true,
				'possible_paths' => array(),
				'permissions'    => 0755,
			);
			$args = array_merge( $default_args, $args );
			
			$uploads_dir_data = wp_upload_dir();
			
			$default_vars = array(
				'uploads_basedir' => $uploads_dir_data['basedir'],
				'uploads_path'    => $uploads_dir_data['path'],
			);
			$vars = array_merge( $default_vars, $vars );
			
			
			foreach ( (array) $args['possible_paths'] as $path ) {
				foreach ( (array) $vars as $var => $val )
					$path = preg_replace( '/%' . preg_quote( $var, '/' ) . '%/', $val, $path );
				
				if ( ! is_dir( $path ) )
					ITFileUtility::mkdir( $path, $args['permissions'] );
				
				$path = realpath( $path );
				
				if ( ! empty( $path ) && is_writable( $path ) ) {
					$writable_dir = $path;
					break;
				}
			}
			
			if ( empty( $writable_dir ) || ! is_writable( $writable_dir ) ) {
				if ( is_writable( $uploads_dir_data['basedir'] ) )
					$writable_dir = $uploads_dir_data['basedir'];
				else if ( is_writable( $uploads_dir_data['path'] ) )
					$writable_dir = $uploads_dir_data['path'];
				else if ( is_writable( dirname( __FILE__ ) ) )
					$writable_dir = dirname( __FILE__ );
				else if ( is_writable( ABSPATH ) )
					$writable_dir = ABSPATH;
				else if ( true === $args['private'] )
					return new WP_Error( 'no_private_writable_path', 'Unable to find a writable path within the private space' );
				else
					$writable_dir = sys_get_temp_dir();
			}
			
			if ( empty( $writable_dir ) || ! is_dir( $writable_dir ) || ! is_writable( $writable_dir ) )
				return new WP_Error( 'no_writable_path', 'Unable to find a writable path' );
			
			$writable_dir = preg_replace( '|/+$|', '', $writable_dir );
			
			return $writable_dir;
		}
		
		function create_writable_path( $args = array() ) {
			if ( is_string( $args ) )
				$args = array( 'name' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'name'           => 'temp-deleteme',
				'private'        => true,
				'possible_paths' => array(),
				'permissions'    => 0755,
				'rename'         => false,
			);
			$args = array_merge( $default_args, $args );
			
			
			$writable_dir = ITFileUtility::find_writable_path( array( 'private' => $args['private'], 'possible_paths' => $args['possible_paths'], 'permissions' => $args['permissions'] ) );
			
			if ( is_wp_error( $writable_dir ) )
				return $writable_dir;
			
			
			$test_dir_name = $args['name'];
			$path = "$writable_dir/$test_dir_name";
			
			
			if ( file_exists( $path ) && ( false === $args['rename'] ) ) {
				if ( is_writable( $path ) )
					return $path;
				else
					return new WP_Error( 'create_writable_path_failed', 'Requested path exists and cannot be written to' );
			}
			
			
			$count = 0;
			
			while ( is_dir( "$writable_dir/$test_dir_name" ) ) {
				$count++;
				$test_dir_name = "{$args['name']}-$count";
			}
			
			$path = "$writable_dir/$test_dir_name";
			
			if ( false === ITFileUtility::mkdir( $path, $args['permissions'] ) )
				return new WP_Error( 'create_path_failed', 'Unable to create a writable path' );
			if ( ! is_writable( $path ) )
				return new WP_Error( 'create_writable_path_failed', 'Unable to create a writable path' );
			
			return $path;
		}
		
		function create_writable_file_old( $args ) {
			$default_args = array(
				'name'             => 'deleteme',
				'extension'        => '.tmp',
				'path_name'        => '',
				'private'          => true,
				'possible_paths'   => array(),
				'permissions'      => 0644,
				'path_permissions' => 0755,
				'overwrite'        => false,
				'rename'           => true,
			);
			$args = array_merge( $default_args, $args );
			
			
			if ( empty( $args['path_name'] ) )
				$writable_dir = ITFileUtility::find_writable_path( array( 'private' => $args['private'], 'possible_paths' => $args['possible_paths'], 'permissions' => $args['path_permissions'] ) );
			else
				$writable_dir = ITFileUtility::create_writable_path( array( 'name' => $args['path_name'], 'private' => $args['private'], 'possible_paths' => $args['possible_paths'], 'permissions' => $args['path_permissions'] ) );
			
			if ( is_wp_error( $writable_dir ) )
				return $writable_dir;
			
			
			$test_file_name = "{$args['name']}{$args['extension']}";
			
			if ( file_exists( "$writable_dir/$test_file_name" ) ) {
				if ( false === $args['overwrite'] ) {
					if ( false === $args['rename'] )
						return new WP_Error( 'requested_file_exists', 'The requested file exists and settings don\'t allow overwriting' );
					
					$count = 0;
					
					while ( is_file( "$writable_dir/$test_file_name" ) ) {
						$count++;
						$test_file_name = "{$args['name']}-$count{$args['extension']}";
					}
				}
			}
			
			$file = "$writable_dir/$test_file_name";
			
			
			if ( false === ITFileUtility::is_file_writable( $file ) )
				return new WP_Error( 'create_file_failed', 'Unable to create the file' );
			@chmod( $file, $args['permissions'] );
			
			if ( ! is_writable( $file ) )
				return new WP_Error( 'create_writable_file_failed', 'The file was successfully created but cannot be written to' );
			
			return $file;
		}
		
		function get_file_listing( $path ) {
			if ( ! is_dir( $path ) )
				return false;
			
			$files = array_merge( glob( "$path/*" ), glob( "$path/.*" ) );
			$contents = array();
			
			foreach ( (array) $files as $file ) {
				if ( in_array( basename( $file ), array( '.', '..' ) ) )
					continue;
				
				if ( is_dir( $file ) )
					$contents[basename( $file )] = ITFileUtility::get_file_listing( $file );
				else if ( is_file( $file ) )
					$contents[basename( $file )] = true;
			}
			
			return $contents;
		}
		
		function get_flat_file_listing( $path, $recurse = false ) {
			if ( ! is_dir( $path ) )
				return false;
			
			$path = rtrim( $path, '/' );
			
			$files = array_merge( glob( "$path/*" ), glob( "$path/.*" ) );
			$contents = array();
			
			foreach ( (array) $files as $file ) {
				if ( in_array( basename( $file ), array( '.', '..' ) ) )
					continue;
				
				if ( is_dir( $file ) ) {
					$results = ITFileUtility::get_flat_file_listing( $file, true );
					$contents = array_merge( $contents, $results );
				}
				else if ( is_file( $file ) )
					$contents[] = $file;
			}
			
			if ( false === $recurse )
				$contents = str_replace( "$path/", '', $contents );
			
			return $contents;
		}
		
		function mkdir( $directory, $args = array() ) {
			if ( is_dir( $directory ) )
				return true;
			if ( is_file( $directory ) )
				return false;
			
			
			if ( is_int( $args ) )
				$args = array( 'permissions' => $args );
			if ( is_bool( $args ) )
				$args = array( 'create_index' => false );
			
			$default_args = array(
				'permissions'	=> 0755,
				'create_index'	=> true,
			);
			$args = array_merge( $default_args, $args );
			
			
			if ( ! is_dir( dirname( $directory ) ) ) {
				if ( false === ITFileUtility::mkdir( dirname( $directory ), $args ) )
					return false;
			}
			
			if ( false === @mkdir( $directory, $args['permissions'] ) )
				return false;
			
			if ( true === $args['create_index'] )
				ITFileUtility::write( "$directory/index.php", '<?php // Silence is golden.' );
			
			return true;
		}
		
		function copy( $source, $destination, $args = array() ) {
			$default_args = array(
				'max_depth'    => 100,
				'folder_mode'  => 0755,
				'file_mode'    => 0744,
				'ignore_files' => array(),
			);
			$args = array_merge( $default_args, $args );
			
			ITFileUtility::_copy( $source, $destination, $args );
		}
		
		function _copy( $source, $destination, $args, $depth = 0 ) {
			if ( $depth > $args['max_depth'] )
				return true;
			
			if ( is_file( $source ) ) {
				if ( is_dir( $destination ) || preg_match( '|/$|', $destination ) ) {
					$destination = preg_replace( '|/+$|', '', $destination );
					
					$destination = "$destination/" . basename( $source );
				}
				
				if ( false === ITFileUtility::mkdir( dirname( $destination ), $args['folder_mode'] ) )
					return false;
				
				if ( false === @copy( $source, $destination ) )
					return false;
				
				@chmod( $destination, $args['file_mode'] );
				
				return true;
			}
			else if ( is_dir( $source ) || preg_match( '|/\*$|', $source ) ) {
				if ( preg_match( '|/\*$|', $source ) )
					$source = preg_replace( '|/\*$|', '', $source );
				else if ( preg_match( '|/$|', $destination ) )
					$destination = $destination . basename( $source );
				
				$destination = preg_replace( '|/$|', '', $destination );
				
				$files = array_diff( array_merge( glob( $source . '/.*' ), glob( $source . '/*' ) ), array( $source . '/.', $source . '/..' ) );
				
				if ( false === ITFileUtility::mkdir( $destination, $args['folder_mode'] ) )
					return false;
				
				$result = true;
				
				foreach ( (array) $files as $file ) {
					if ( false === ITFileUtility::_copy( $file, "$destination/", $args, $depth + 1 ) )
						$result = false;
				}
				
				return $result;
			}
			
			return false;
		}
		
		function delete_directory( $path ) {
			if ( ! is_dir( $path ) )
				return true;
			
			$files = array_merge( glob( "$path/*" ), glob( "$path/.*" ) );
			$contents = array();
			
			foreach ( (array) $files as $file ) {
				if ( in_array( basename( $file ), array( '.', '..' ) ) )
					continue;
				
				if ( is_dir( $file ) )
					ITFileUtility::delete_directory( $file );
				else if ( is_file( $file ) )
					@unlink( $file );
			}
			
			@rmdir( $path );
			
			if ( ! is_dir( $path ) )
				return true;
			return false;
		}
		
		function get_unique_name( $path, $prefix, $postfix = '' ) {
			$count = 0;
			
			$test_name = "$prefix$postfix";
			
			while ( file_exists( "$path/$test_name" ) ) {
				$count++;
				$test_name = "$prefix-$count$postfix";
			}
			
			return $test_name;
		}
		
		function create_favicon( $dir_name, $image, $sizes = false ) {
			require_once( dirname( __FILE__ ) . '/class-php-ico.php' );
			
			
			if ( ! is_array( $sizes ) || ( false === $sizes ) ) {
				$sizes = array(
					array( 16, 16 ),
					array( 24, 24 ),
					array( 32, 32 ),
					array( 48, 48 ),
				);
			}
			
			
			$ico = new PHP_ICO( $image, $sizes );
			
			$path = ITFileUtility::get_writable_directory( $dir_name );
			
			while ( empty( $name ) || file_exists( "$path/$name" ) )
				$name = ITUtility::get_random_string( array( 6, 10 ) ) . '.ico';
			
			
			$result = $ico->save_ico( "$path/$name", $sizes );
			
			if ( true != $result )
				return false;
			
			return "$path/$name";
		}
	}
}

if ( ! function_exists( 'mime_content_type' ) ) {
	function mime_content_type( $filename ) {
		$mime_types = array(
			'txt'  => 'text/plain',
			'htm'  => 'text/html',
			'html' => 'text/html',
			'php'  => 'text/html',
			'css'  => 'text/css',
			'js'   => 'application/javascript',
			'json' => 'application/json',
			'xml'  => 'application/xml',
			'swf'  => 'application/x-shockwave-flash',
			'flv'  => 'video/x-flv',
			'png'  => 'image/png',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'gif'  => 'image/gif',
			'bmp'  => 'image/bmp',
			'ico'  => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif'  => 'image/tiff',
			'svg'  => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			'zip'  => 'application/zip',
			'rar'  => 'application/x-rar-compressed',
			'exe'  => 'application/x-msdownload',
			'msi'  => 'application/x-msdownload',
			'cab'  => 'application/vnd.ms-cab-compressed',
			'mp3'  => 'audio/mpeg',
			'qt'   => 'video/quicktime',
			'mov'  => 'video/quicktime',
			'pdf'  => 'application/pdf',
			'psd'  => 'image/vnd.adobe.photoshop',
			'ai'   => 'application/postscript',
			'eps'  => 'application/postscript',
			'ps'   => 'application/postscript',
			'doc'  => 'application/msword',
			'rtf'  => 'application/rtf',
			'xls'  => 'application/vnd.ms-excel',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'odt'  => 'application/vnd.oasis.opendocument.text',
			'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
		);
		
		$ext = strtolower( array_pop( explode( '.', $filename ) ) );
		
		if ( array_key_exists( $ext, $mime_types ) )
			return $mime_types[$ext];
		elseif ( function_exists( 'finfo_open' ) ) {
			$finfo = finfo_open( FILEINFO_MIME );
			$mimetype = finfo_file( $finfo, $filename );
			finfo_close( $finfo );
			return $mimetype;
		}
		else
			return 'application/octet-stream';
	}
}

if ( ! function_exists( 'sys_get_temp_dir' ) ) {
	function sys_get_temp_dir() {
		if ( $temp = getenv('TMP') )
			return $temp;
		if ( $temp = getenv('TEMP') )
			return $temp;
		if ( $temp = getenv('TMPDIR') )
			return $temp;
		
		$temp = tempnam( dirname( __FILE__ ), '' );
		if ( file_exists( $temp ) ) {
			unlink( $temp );
			return dirname( $temp );
		}
		return null;
	}
}
