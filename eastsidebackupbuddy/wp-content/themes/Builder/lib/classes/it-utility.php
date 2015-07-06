<?php

/*
Written by Chris Jean for iThemes.com
Version 1.3.0

Version History
	1.0.0 - 2009-07-14
		Release-ready
	1.0.1 - 2009-09-11
		Changed verify_file function:
			Added it-utility.php file to search path
	1.0.2 - 2009-09-18
		Added TEMPLATEPATH check to prevent warnings if it has not been defined yet
	1.0.3 - 2009-09-18
		Fixed additional warnings
	1.0.4 - 2009-12-04
		Made code more robust by adding ITError check
	1.0.5 - 2009-12-11
		Added a \n after get_open_tag
	1.0.6 - 2010-06-25
		Replaced all require_once calls with it_classes_load
	1.0.7 - 2010-12-20
		Added STYLESHEETPATH to verify_file()'s path finding
	1.1.0 - 2011-02-22 - Chris Jean
		Added functions:
			show_inline_status_message
			show_inline_error_message
			sort_array
			get_array_value
			add_array_value
	1.2.0 - 2011-06-27 - Chris Jean
		Added functions:
			add_tooltip_scripts
			add_tooltip_styles
			get_tooltip
			add_tooltip
	1.3.0 - 2011-08-04 - Chris Jean
		Added functions:
			fix_url
			get_random_string
*/


if ( ! class_exists( 'ITUtility' ) ) {
	it_classes_load( 'it-error.php' );
	
	class ITUtility {
		function require_file_once( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'require_once', true );
			@require_once( $file );
			ITUtility::check_class( $class, true );
		}
		
		function require_file( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'require', true );
			@require( $file );
			ITUtility::check_class( $class, true );
		}
		
		function include_file_once( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'include_once', false );
			@include_once( $file );
			ITUtility::check_class( $class, false );
		}
		
		function include_file( $file, $class = false ) {
			$file = ITUtility::verify_file( $file, 'include', false );
			@include( $file );
			ITUtility::check_class( $class, false );
		}
		
		function verify_file( $file, $type, $required = true ) {
			if ( defined( 'STYLESHEETPATH' ) && file_exists( STYLESHEETPATH . "/$file" ) )
				$file = STYLESHEETPATH . "/$file";
			else if ( defined( 'TEMPLATEPATH' ) && file_exists( TEMPLATEPATH . "/$file" ) )
				$file = TEMPLATEPATH . "/$file";
			else if ( file_exists( ABSPATH . "/$file" ) )
				$file = ABSPATH . "/$file";
			else if ( file_exists( dirname( __FILE__ ) . "/$file" ) )
				$file = dirname( __FILE__ ) . "/$file";
			else if ( ! file_exists( $file ) ) {
				if ( $required )
					ITError::fatal( "missing_file:$type:$file", 'A file necessary for the theme to function is missing or unable to be read by the web server.' );
				else {
					ITError::warn( "missing_file:$type:$file", 'A theme file is missing or unable to be read by the web server.' );
					return false;
				}
			}
			
			return $file;
		}
		
		function check_class( $class, $required = true ) {
			if ( false !== $class ) {
				if ( ! class_exists( $class ) ) {
					if ( $required )
						ITError::fatal( "missing_class:$class", 'A necessary core component of the theme is missing.' );
					else
						ITError::warn( "missing_class:$class", 'A core component of the theme is missing.' );
				}
			}
		}
		
		function merge_defaults( $values, $defaults, $force = false ) {
			if ( ! ITUtility::is_associative_array( $defaults ) ) {
				if ( ! isset( $values ) )
					return $defaults;
				
				if ( false === $force )
					return $values;
				
				if ( isset( $values ) || is_array( $values ) )
					return $values;
				return $defaults;
			}
			
			foreach ( (array) $defaults as $key => $val ) {
				if ( ! isset( $values[$key] ) )
					$values[$key] = null;
				
				$values[$key] = ITUtility::merge_defaults( $values[$key], $val, $force );
			}
			
			return $values;
		}
		
		function is_associative_array( &$array ) {
			if ( ! is_array( $array ) || empty( $array ) )
				return false;
			
			$next = 0;
			
			foreach ( $array as $k => $v )
				if ( $k !== $next++ )
					return true;
			
			return false;
		}
		
		function show_status_message( $message ) {
			echo "<div class=\"updated fade\"><p><strong>$message</strong></p></div>\n";
		}
		
		function show_error_message( $message ) {
			if ( is_wp_error( $message ) )
				$message = $message->get_error_message();
			
			if ( ! is_string( $message ) )
				return;
			
			echo "<div class=\"error\"><p><strong>$message</strong></p></div>\n";
		}
		
		function show_inline_status_message( $message ) {
			echo "<div class=\"updated fade inline\"><p><strong>$message</strong></p></div>\n";
		}
		
		function show_inline_error_message( $message ) {
			if ( is_wp_error( $message ) )
				$message = $message->get_error_message();
			
			if ( ! is_string( $message ) )
				return;
			
			echo "<div class=\"error inline\"><p><strong>$message</strong></p></div>\n";
		}
		
		function verify_class( $var, $class ) {
			if ( isset( $var ) && is_object( $var ) && ( strtolower( $class ) === strtolower( get_class( $var ) ) ) )
				return true;
			return false;
		}
		
		function get_open_tag( $tag_name, $attributes = array() ) {
			$tag = "<$tag_name";
			
			foreach ( (array) $attributes as $attribute => $values ) {
				$attr_value = '';
				foreach ( (array) $values as $value ) {
					if ( ! empty( $attr_value ) )
						$attr_value .= ' ';
					$attr_value .= str_replace( '"', '&quot;', $value );
				}
				$tag .= " $attribute=\"$attr_value\"";
			}
			
			$tag .= '>';
			
			return $tag;
		}
		
		function print_open_tag( $tag_name, $attributes ) {
			echo ITUtility::get_open_tag( $tag_name, $attributes ) . "\n";
		}
		
		function cleanup_request_vars() {
			$_REQUEST = ITUtility::strip_slashes( $_REQUEST );
			$_POST = ITUtility::strip_slashes( $_POST );
			$_GET = ITUtility::strip_slashes( $_GET );
		}
		
		function strip_slashes( $var ) {
			if ( is_array( $var ) ) {
				foreach ( (array) $var as $index => $val )
					$var[$index] = ITUtility::strip_slashes( $val );
			}
			else
				$var = stripslashes( $var );
			
			return $var;
		}
		
		function sort_array( $array, $index, $args = array() ) {
			it_classes_load( 'it-array-sort.php' );
			
			$sorter = new ITArraySort( $array, $index, $args );
			
			return $sorter->get_sorted_array();
		}
		
		// Deprecated
		function sort_array_by_index( $array, $index ) {
			if ( ! is_array( $array ) )
				ITError::fatal( 'invalid_var:parameter:array', 'Invalid data was passed to ITUtility::sort_array_by_index. This indicates a code bug.' );
			
			$new_array = array();
			$indexes = array();
			
			foreach ( (array) $array as $sub_index => $sub_array )
				$indexes[$sub_index] = $sub_array[$index];
			
			asort( $indexes );
			
			foreach ( (array) $indexes as $sub_index => $sub_value )
				$new_array[] = $array[$sub_index];
			
			return $new_array;
		}
		
		function get_array_value( $array, $index ) {
			if ( is_string( $index ) ) {
				if ( false === strpos( $index, '[' ) )
					$index = array( $index );
				else {
					$index = rtrim( $index, '[]' );
					$index = preg_split( '/[\[\]]+/', $index );
				}
			}
			
			while ( count( $index ) > 1 ) {
				if ( isset( $array[$index[0]] ) ) {
					$array = $array[$index[0]];
					array_shift( $index );
				}
				else
					return null;
			}
			
			if ( isset( $array[$index[0]] ) )
				return $array[$index[0]];
			
			return null;
		}
		
		function add_array_value( &$array, $index, $val ) {
			if ( is_string( $index ) ) {
				if ( false === strpos( $index, '[' ) )
					$index = array( $index );
				else {
					$index = rtrim( $index, '[]' );
					$index = preg_split( '/[\[\]]+/', $index );
				}
			}
			
			$cur_array =& $array;
			
			while ( count( $index ) > 1 ) {
				if ( ! isset( $cur_array[$index[0]] ) || ! is_array( $cur_array[$index[0]] ) )
					$cur_array[$index[0]] = array();
				
				$cur_array =& $cur_array[$index[0]];
				array_shift( $index );
			}
			
			$cur_array[$index[0]] = $val;
		}
		
		function print_js_vars( $options = array() ) {
			
?>
	<script type="text/javascript">
		/* <![CDATA[ */
			<?php foreach ( (array) $options as $var => $val ) : ?>
				<?php $val = str_replace( '"', '\\"', $val ); ?>
				var <?php echo $var; ?> = "<?php echo $val; ?>";
			<?php endforeach; ?>
		/* ]]> */
	</script>
<?php
			
		}
		
		function print_js_script( $script ) {
			
?>
	<script type="text/javascript">
		/* <![CDATA[ */
			<?php echo $script; ?>
		/* ]]> */
	</script>
<?php
			
		}
		
		function add_tooltip_scripts() {
			global $it_utility_cached_url_base;
			
			if ( empty( $it_utility_cached_url_base ) )
				$it_utility_cached_url_base = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
			
			wp_enqueue_script( 'jquery-tooltip', $it_utility_cached_url_base . '/js/jquery.tooltip.js', array( 'jquery' ) );
			wp_enqueue_script( 'it-tooltip', $it_utility_cached_url_base . '/js/it-tooltip.js', array( 'jquery-tooltip' ) );
		}
		
		function add_tooltip_styles() {
			global $it_utility_cached_url_base;
			
			if ( empty( $it_utility_cached_url_base ) )
				$it_utility_cached_url_base = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
			
			wp_enqueue_style( 'it-tooltip', $it_utility_cached_url_base . '/css/it-tooltip.css' );
		}
		
		function get_tooltip( $message, $title = '', $class = '', $alt = '(?)' ) {
			global $it_utility_cached_url_base;
			
			if ( empty( $it_utility_cached_url_base ) )
				$it_utility_cached_url_base = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
			
			$message = esc_attr( $message );
			$title = esc_attr( $title );
			
			if ( empty( $class ) )
				$class = 'it-tooltip';
			else
				$class = "it-tooltip-$class";
			
			$tip = "<a class='$class' title='$title |:|~| $message'><img src='" . $it_utility_cached_url_base . "/images/it-tooltip.png' alt='(?)' /></a>";
			
			return $tip;
		}
		
		function add_tooltip( $message, $title = '', $class = '', $alt = '(?)' ) {
			echo ITUtility::get_tooltip( $message, $title, $class, $alt );
		}
		
		/* Automatically changes http protocols to https when is_ssl is true */
		function fix_url( $url ) {
			if ( ! is_ssl() )
				return $url;
			
			return preg_replace( '|^http://|', 'https://', $url );
		}
		
		function get_random_string( $length = 10, $use_sets = array( 'lower', 'upper', 'num' ) ) {
			$sets = array(
				'lower'    => 'abcdefghijklmnopqrstuvwxyz',
				'upper'    => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
				'num'      => '0123456789',
				'basic'    => '-_,.',
				'extended' => '`~!@#$%^&*()=+[]{};:\'"<>/?\|',
			);
			
			
			if ( is_array( $length ) ) {
				if ( ! isset( $length[0] ) || ! isset( $length[1] ) )
					return '';
				
				$lower = intval( $length[0] );
				$upper = intval( $length[1] );
				
				$length = rand( $lower, $upper );
			}
			else
				$length = intval( $length );
			
			if ( $length < 1 )
				$length = 1;
			
			
			$source_string = '';
			
			if ( is_string( $use_sets ) )
				$source_string = $use_sets;
			else if ( is_array( $use_sets ) ) {
				foreach ( $use_sets as $set ) {
					if ( is_string( $set ) && isset( $sets[$set] ) )
						$source_string .= $sets[$set];
				}
			}
			else if ( true === $use_sets ) {
				foreach ( $sets as $chars )
					$source_string .= $chars;
			}
			
			if ( empty( $source_string ) )
				return false;
			
			
			$string = '';
			
			while ( strlen( $string ) < $length )
				$string .= substr( $source_string, rand( 0, strlen( $source_string ) - 1 ), 1 );
			
			
			return $string;
		}
	}
}
