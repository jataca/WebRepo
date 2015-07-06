<?php

/*
Written by Chris Jean for iThemes.com
Version 3.4.1

Version History
	3.0.0 - 2010-01-16
		Added width back to builder-container-outer-wrapper.
		Merged builder_layout_engine_render_container_start and builder_layout_engine_render_container_end
			into builder_layout_engine_render_container.
		Added builder_layout_engine_render_header action.
		Added builder_layout_engine_render_modules action.
		Added builder_finish action.
	3.0.1 - 2010-01-18
		Changed the builder_module_render and builder_module_render_contents argument from
			$module to $fields to keep in line with other methods/actions
	3.0.2 - 2010-02-03
		Fixed bug where the top post in a listing could change the layout
	3.1.0 - 2010-02-04
		Added print_body_tag which is now added to the builder_layout_engine_render_header action. This
			function now prints the body tag rather than the header.php template file.
		Added builder-template-{TEMPLATEFILE} and builder-view-{VIEW} classes to the body tag.
		Added builder-layout-{LAYOUTID} id to the body tag.
		Added _layout_views and _layout_functions vars which are set by setup_layout.
	3.1.1 - 2010-02-04
		Improved body tag classes
	3.1.2 - 2010-02-11
		Fixed warning conditions
	3.1.3 - 2010-02-24
		Added flush() to top of print_body_tag function. This can help pages load faster
	3.2.0 - 2010-03-03
		Added widget-module-[top,bottom,middle,single] classes to the widget-module class' div
	3.2.1 - 2010-03-22
		Fixed typo causing builder-view-VIEW classes to not work properly
	3.2.2 - 2010-04-20
		Added builder_layout_engine_get_current_module and builder_layout_engine_get_current_area_width filters
	3.2.3 - 2010-04-22
		Fixed warning in setup_layout()
	3.2.4 - 2010-05-18
		Changed setup_layout priority to -10
	3.2.5 - 2010-07-16
		Moved builder_get_available_views filter call to inside the pertinent function:
			Speeds up code and prevents issue with calling it too early.
		Added _is_current_view method to handle more complex view functions.
		Simplified query_vars checks in setup_layout to improve efficiency.
	3.2.6 - 2010-07-22
		Added WordPress-calculated body classes to body tag.
	3.2.7 - 2010-09-02
		Changed edit_themes to switch_themes for better multisite support
	3.2.8 - 2010-12-14
		Added check to prevent from printing "width:px;" when no container width is set
	3.2.9 - 2010-02-22
		Added max-width for container
		Added ability to disable flush by using the BUILDER_DISABLE_FLUSH define set to true
	3.3.0 - 2011-06-29 - Chris Jean
		Added a builder_debug query var check that allows for printing the rendering details w/o logging in
		Added support for the new Views data format
	3.4.0 - 2011-07-06 - Chris Jean
		Added support for legacy templates for child themes that don't have builder-3.0 support
	3.4.1 - 2011-08-04 - Chris Jean
		Changed legacy_templates to legacy-templates
*/


if ( ! class_exists( 'BuilderLayoutEngine' ) ) {
	class BuilderLayoutEngine {
		var $_layout_id = '';
		var $_layout = array();
		var $_view_id = '';
		var $_view = '';
		var $_modules = array();
		var $_options = array();
		var $_var = 'layout_settings';
		var $_current_module = null;
		var $_current_area_width = null;
		
		var $_layout_functions = array();
		var $_layout_views = array();
		
		
		function BuilderLayoutEngine() {
			$this->_storage =& new ITStorage( $this->_var );
			
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'template_redirect', array( &$this, 'setup_layout' ), -10 );
			add_action( 'admin_bar_menu', array( &$this, 'add_admin_bar_menu_entry' ), 200 );
			
			
			add_action( 'builder_layout_engine_render', array( &$this, 'render' ) );
			
			add_action( 'builder_layout_engine_render_header', 'get_header', 10, 0 );
			add_action( 'builder_layout_engine_render_header', array( &$this, 'print_render_comments' ), 15 );
			add_action( 'builder_layout_engine_render_header', array( &$this, 'print_body_tag' ), 16 );
			
			add_action( 'builder_layout_engine_render_container', array( &$this, 'render_container' ) );
			add_action( 'builder_layout_engine_render_container_contents', array( &$this, 'render_container_contents' ) );
			
			add_action( 'builder_layout_engine_render_modules', array( &$this, 'render_modules' ) );
			add_action( 'builder_module_render', array( &$this, 'render_module' ) );
			add_action( 'builder_module_render_contents', array( &$this, 'render_module_contents' ) );
			add_action( 'builder_module_render_element_block', array( &$this, 'render_module_element_block' ) );
			add_action( 'builder_module_render_element_block_contents', array( &$this, 'render_module_element_block_contents' ) );
			add_action( 'builder_module_render_sidebar_block', array( &$this, 'render_module_sidebar_block' ), 10, 2 );
			add_action( 'builder_module_render_sidebar_block_contents', array( &$this, 'render_module_sidebar_block_contents' ), 10, 2 );
			
			add_action( 'builder_finish', array( &$this, 'render_finish' ) );
			
			
			add_action( 'builder_layout_engine_set_current_module', array( &$this, 'set_current_module' ) );
			add_action( 'builder_layout_engine_set_current_area_width', array( &$this, 'set_current_area_width' ) );
			
			
			add_filter( 'builder_layout_engine_get_current_module', array( &$this, 'get_current_module' ) );
			add_filter( 'builder_layout_engine_get_current_area_width', array( &$this, 'get_current_area_width' ) );
			
			add_filter( 'builder_filter_container_outer_wrapper_attributes', array( &$this, 'get_container_outer_wrapper_attributes' ) );
			add_filter( 'builder_get_container_width', array( &$this, 'get_container_width' ), 0 );
			add_filter( 'builder_get_current_layout', array( &$this, 'get_current_layout' ), 0 );
		}
		
		function add_admin_bar_menu_entry( &$wp_admin_bar ) {
			if ( ! current_user_can( 'switch_themes' ) )
				return;
			
			$wp_admin_bar->add_menu( array( 'id' => 'builder', 'title' => 'Builder', 'href' => admin_url( 'admin.php?page=ithemes-builder-theme' ) ) );
			
			if ( ! empty( $this->_layout_id ) ) {
				$wp_admin_bar->add_menu( array( 'parent' => 'builder', 'id' => 'builder_edit_layout', 'title' => sprintf( __( 'Edit Layout (%s)', 'it-l10n-Builder' ), $this->_layout_settings['layouts'][$this->_layout_id]['description'] ), 'href' => admin_url( 'admin.php?page=layout-editor&editor_tab=layouts&layout=' . $this->_layout_id ) ) );
				
				if ( 'on' != get_user_setting( 'widgets_access' ) )
					$wp_admin_bar->add_menu( array( 'parent' => 'builder', 'id' => 'builder_edit_widgets', 'title' => __( 'Manage Widgets for this Layout', 'it-l10n-Builder' ), 'href' => admin_url( 'widgets.php?builder_layout_id=' . $this->_layout_id ) ) );
			}
			
			$wp_admin_bar->add_menu( array( 'parent' => 'builder', 'id' => 'builder_edit_settings', 'title' => __( 'Modify Builder Settings', 'it-l10n-Builder' ), 'href' => admin_url( 'admin.php?page=theme-settings' ) ) );
			
			
			do_action_ref_array( 'builder_add_admin_bar_menu_entries', array( &$wp_admin_bar ) );
		}
		
		function init() {
			$this->_modules = apply_filters( 'builder_get_modules', array() );
			$this->_layout_settings = $this->_storage->load();
//			print_r( $this->_layout_settings );
		}
		
		function set_current_module( $module ) {
			$this->_current_module = $module;
		}
		
		function get_current_module( $module ) {
			return $this->_current_module;
		}
		
		function set_current_area_width( $width ) {
			$this->_current_area_width = $width;
		}
		
		function get_current_area_width( $width ) {
			return $this->_current_area_width;
		}
		
		function render( $view ) {
			if ( ! builder_theme_supports( 'builder-3.0' ) && ! file_exists( STYLESHEETPATH . "/$view" ) && file_exists( TEMPLATEPATH . "/legacy-templates/$view" ) )
				require_once( TEMPLATEPATH . "/legacy-templates/$view" );
			
			$this->_view = strtolower( preg_replace( '/-+/', '-', preg_replace( '/[^a-z0-9\-\.]/i', '-', $view ) ) );
			
			$class_prefix = apply_filters( 'builder_module_filter_css_prefix', 'builder-module' );
			
			$render_data = array(
				'layout_id'    => $this->_layout_id,
				'layout'       => $this->_layout_settings['layouts'][$this->_layout_id]['description'],
				'view'         => $view,
				'class_prefix' => $class_prefix,
			);
			
			
			do_action( 'builder_layout_engine_render_header', $render_data );
			
			if ( file_exists( get_stylesheet_directory() . '/footer.php' ) )
				load_template( get_stylesheet_directory() . '/footer.php' );
			else if ( file_exists( get_template_directory() . '/footer.php' ) )
				load_template( get_template_directory() . '/footer.php' );
			
			do_action( 'builder_layout_engine_render_container', $render_data );
			
			
			do_action( 'builder_finish', $render_data );
		}
		
		function render_finish() {
//			echo "<h1>Memory Usage: " . ( intval( memory_get_usage() / 1024 / 1.024 ) / 1000 ) . " mb</h1>\n";
			echo "\n</body>\n</html>";
		}
		
		function render_container( $render_data ) {
			$this->_render_container_start();
			
			do_action( 'builder_layout_engine_render_container_contents', $render_data );
			
			$this->_render_container_end();
		}
		
		function render_container_contents( $render_data ) {
			do_action( 'builder_layout_engine_render_modules', $render_data );
		}
		
		function render_modules( $render_data ) {
			$class_prefix = $render_data['class_prefix'];
			
			
			$module_positions = array();
			$module_counts = array();
			$module_count = 0;
			$footer_position = 0;
			
			foreach ( (array) $this->_layout_settings['layouts'][$this->_layout_id]['modules'] as $module ) {
				$module_count++;
				
				if ( 'footer' === $module['module'] )
					$footer_position = $module_count;
				
				if ( ! isset( $module_counts[$module['module']] ) )
					$module_counts[$module['module']] = 0;
				$module_counts[$module['module']]++;
				
				$module_positions[$module_count] = $module['module'];
			}
			
			$module_use_count = array();
			$module_count = 0;
			
			$id = 1;
			$last_id = count( $this->_layout_settings['layouts'][$this->_layout_id]['modules'] );
			
			
			foreach ( (array) $this->_layout_settings['layouts'][$this->_layout_id]['modules'] as $module ) {
				if ( method_exists( $this->_modules[$module['module']], 'render' ) ) {
					if ( ! isset( $module_use_count[$module['module']] ) )
						$module_use_count[$module['module']] = 0;
					$module_use_count[$module['module']]++;
					
					$module_count++;
					
					if ( 1 === $last_id )
						$module_location = 'single';
					else if ( $module_count === $last_id )
						$module_location = 'bottom';
					else if ( 1 === $module_count )
						$module_location = 'top';
					else
						$module_location = 'middle';
					
					
					$module = array_merge( $module, $render_data );
					
					$module['id'] = $id;
					
					$module['outer_wrapper']['class'] = array(
						"$class_prefix-outer-wrapper",
						"$class_prefix-{$module['module']}-outer-wrapper",
					);
					
					if ( isset( $module_positions[$module_count + 1] ) )
						$module['outer_wrapper']['class'][] = "$class_prefix-before-{$module_positions[$module_count + 1]}-outer-wrapper";
					if ( ( $id === $last_id ) && ( 0 === $footer_position ) )
						$module['outer_wrapper']['class'][] = "$class_prefix-before-footer-outer-wrapper";
					if ( $module_count > 1 )
						$module['outer_wrapper']['class'][] = "$class_prefix-after-{$module_positions[$module_count - 1]}-outer-wrapper";
					
					if ( ! empty( $module['data']['style'] ) )
						$module['outer_wrapper']['class'][] = "{$module['data']['style']}-outer-wrapper";
					else
						$module['outer_wrapper']['class'][] = 'default-module-style-outer-wrapper';
					
					
					$module['inner_wrapper']['class'] = array(
						$class_prefix,
						"$class_prefix-{$module['module']}",
						"$class_prefix-$id",
						"$class_prefix-{$module['module']}-{$module_use_count[$module['module']]}",
						"$class_prefix-$module_location",
					);
					
					if ( $id === $last_id )
						$module['inner_wrapper']['class'][] = "$class_prefix-last";
					
					if ( $module_use_count[$module['module']] === $module_counts[$module['module']] )
						$module['inner_wrapper']['class'][] = "$class_prefix-{$module['module']}-last";
					
					if ( isset( $module_positions[$module_count + 1] ) )
						$module['inner_wrapper']['class'][] = "$class_prefix-before-{$module_positions[$module_count + 1]}";
					if ( ( $id === $last_id ) && ( 0 === $footer_position ) )
						$module['inner_wrapper']['class'][] = "$class_prefix-before-footer";
					if ( $module_count > 1 )
						$module['inner_wrapper']['class'][] = "$class_prefix-after-{$module_positions[$module_count - 1]}";
					
					if ( ! empty( $module['data']['style'] ) ) {
						$module['outer_wrapper']['class'][] = $module['data']['style'] . '-outer-wrapper';
						$module['inner_wrapper']['class'][] = $module['data']['style'];
					}
					else {
						$module['outer_wrapper']['class'][] = 'default-module-style-outer-wrapper';
						$module['inner_wrapper']['class'][] = 'default-module-style';
					}
					
					$module['inner_wrapper']['class'][] = 'clearfix';
					
					$module['inner_wrapper']['id'] = "$class_prefix-{$module['guid']}";
					
					
					do_action( 'builder_module_render', $module );
					
					$id++;
				}
			}
			
			if ( 0 === $footer_position ) {
				do_action( 'get_footer' );
				do_action( 'wp_footer' );
			}
		}
		
		function render_module( $fields ) {
			do_action( 'builder_layout_engine_set_current_module', $fields['module'] );
			
			do_action( "builder_module_render_{$fields['module']}", $fields );
			
			do_action( 'builder_layout_engine_set_current_module', null );
		}
		
		function render_module_contents( $fields ) {
			do_action( "builder_module_render_contents_{$fields['module']}", $fields );
		}
		
		function render_module_element_block( $fields ) {
			do_action( "builder_module_render_element_block_{$fields['module']}", $fields );
		}
		
		function render_module_element_block_contents( $fields ) {
			do_action( "builder_module_render_element_block_contents_{$fields['module']}", $fields );
		}
		
		function render_module_sidebar_block( $fields, $side ) {
			do_action( "builder_module_render_sidebar_block_{$fields['module']}", $fields, $side );
		}
		
		function render_module_sidebar_block_contents( $fields, $side ) {
			do_action( "builder_module_render_sidebar_block_contents_{$fields['module']}", $fields, $side );
		}
		
		function print_body_tag( $render_data ) {
			if ( ! defined( 'BUILDER_DISABLE_FLUSH' ) || ( false === BUILDER_DISABLE_FLUSH ) )
				flush();
			
			$attributes = array(
				'class'		=> array(
					"builder-template-" . substr( $this->_view, 0, -4 ),
				),
				'id'		=> array(
					"builder-layout-{$this->_layout_settings['layouts'][$this->_layout_id]['guid']}",
				),
			);
			
			foreach ( (array) $this->_layout_views as $view )
				$attributes['class'][] = "builder-view-$view";
			
			$attributes['class'] = array_merge( $attributes['class'], get_body_class() );
			
			$attributes = apply_filters( 'builder_filter_body_attributes', $attributes );
			
			ITUtility::print_open_tag( 'body', $attributes );
		}
		
		function print_render_comments( $render_data ) {
			if ( current_user_can( 'switch_themes' ) || ! empty( $_GET['builder_debug'] ) ) {
				echo "<!--\n";
				echo "\tLayout:               {$this->_layout_settings['layouts'][$this->_layout_id]['description']}\n";
				echo "\tTemplate File:        $this->_view\n";
				
				if ( ! empty( $this->_view_id ) )
					echo "\tActive View Function: {$this->_view_id}\n";
				
				echo "\tView" . ( ( 1 !== count( $this->_layout_views ) ) ? 's:' : ': ' ) . '                ' . implode( ', ', $this->_layout_views ) . "\n";
				echo "\tView Function" . ( ( 1 !== count( $this->_layout_functions ) ) ? 's:' : ': ' ) . '       ' . implode( ', ', $this->_layout_functions ) . "\n\n";
				
				echo "\tTemplate Class (body): .builder-template-" . preg_replace( '/\.php$/', '', $this->_view ) . "\n";
				
				foreach ( (array) $this->_layout_views as $view )
					echo "\tView Class (body):     .builder-view-$view\n";
				echo "\n";
				
				echo "\tLayout ID (body):         #builder-layout-{$this->_layout_settings['layouts'][$this->_layout_id]['guid']}\n";
				echo "\tContainer ID (container): #builder-container-{$this->_layout_settings['layouts'][$this->_layout_id]['guid']}\n\n";
				
				echo "\tModule IDs:\n";
				
				foreach ( (array) $this->_layout_settings['layouts'][$this->_layout_id]['modules'] as $id => $module )
					printf( "\t\t%-12s #%s-%s\n", "{$this->_modules[$module['module']]->_name}:", $render_data['class_prefix'], $module['guid'] );
				
				echo "-->\n";
			}
		}
		
		function get_current_layout( $layout ) {
			return $this->_layout_settings['layouts'][$this->_layout_id];
		}
		
		function get_container_width( $width ) {
			if ( 'custom' === $this->_layout_settings['layouts'][$this->_layout_id]['width'] )
				$width = intval( $this->_layout_settings['layouts'][$this->_layout_id]['custom_width'] );
			else
				$width = intval( $this->_layout_settings['layouts'][$this->_layout_id]['width'] );
			
			return $width;
		}
		
		function get_container_outer_wrapper_attributes( $options ) {
			$width = apply_filters( 'builder_get_container_width', 0 );
			
			$options['class'][] = 'builder-container-outer-wrapper';
			
			if ( ! empty( $width ) && ( $width > 0 ) ) {
				$options['style'][] = "width:{$width}px;";
				$options['style'][] = "max-width:{$width}px;";
			}
			
			return $options;
		}
		
		function _render_container_start() {
			$view = str_replace( '.php', '', $this->_view );
			
			$outer_wrapper_attributes = apply_filters( 'builder_filter_container_outer_wrapper_attributes', array() );
			
			$inner_wrapper_attributes = array( 'class' => array( 'builder-container', "builder-view-$view" ), 'id' => "builder-container-{$this->_layout_settings['layouts'][$this->_layout_id]['guid']}" );
			$inner_wrapper_attributes = apply_filters( 'builder_filter_container_inner_wrapper_attributes', $inner_wrapper_attributes );
			
			ITUtility::print_open_tag( 'div', $outer_wrapper_attributes );
			ITUtility::print_open_tag( 'div', $inner_wrapper_attributes );
		}
		
		function _render_container_end() {
			echo "\n</div>\n</div>\n";
		}
		
		function _is_current_view( $function ) {
			$args = explode( '|', $function );
			$function = array_shift( $args );
			
			if ( ! function_exists( $function ) )
				return false;
			
			return call_user_func_array( $function, $args );
		}
		
		function setup_layout() {
			global $post, $wp_the_query;
			
			$available_views = apply_filters( 'builder_get_available_views', array() );
			
			$this->_view_stack = array();
			
			if ( is_single() || is_page() ) {
				if ( is_object( $post ) ) {
					$this->_layout_id = get_post_meta( $post->ID, '_custom_layout', true );
					
					if ( ! empty( $this->_layout_id ) && ( ! isset( $this->_layout_settings['layouts'][$this->_layout_id] ) || ! is_array( $this->_layout_settings['layouts'][$this->_layout_id] ) ) )
						$this->_layout_id = null;
					
					if ( ! empty( $this->_layout_id ) ) {
						$this->_layout_functions[] = 'custom_layout';
						$this->_layout_views = array( $post->post_type, "$post->post_type-$post->ID" );
						$this->_view_id = "post:{$post->ID}";
					}
				}
			}
			
			if ( empty( $this->_layout_id ) || ! is_array( $this->_layout_settings['layouts'][$this->_layout_id] ) ) {
				$priority = 0;
				
				foreach ( (array) $available_views as $function => $view ) {
					if ( $this->_is_current_view( $function ) ) {
						if ( ( $view['priority'] > $priority ) && ! empty( $this->_layout_settings['views'][$function] ) && ( '//INHERIT//' != $this->_layout_settings['views'][$function]['layout'] ) ) {
							$this->_layout_id = $this->_layout_settings['views'][$function]['layout'];
							$this->_view_id = $function;
							$priority = $view['priority'];
						}
						
						$this->_layout_functions[] = $function;
						$this->_layout_views[] = strtolower( str_replace( ' ', '-', $available_views[$function]['name'] ) );
					}
				}
			}
			
			if ( in_array( 'is_category', $this->_layout_functions ) )
				$this->_layout_views[] = "category-{$wp_the_query->query_vars['cat']}";
			else if ( in_array( 'is_tag', $this->_layout_functions ) )
				$this->_layout_views[] = "tag-{$wp_the_query->query_vars['tag_id']}";
			else if ( in_array( 'is_author', $this->_layout_functions ) )
				$this->_layout_views[] = "author-{$wp_the_query->query_vars['author']}";
			else if ( ( in_array( 'is_single', $this->_layout_functions ) || in_array( 'builder_is_page', $this->_layout_functions ) ) && isset( $post ) )
				$this->_layout_views[] = "$post->post_type-$post->ID";
			
			if ( ! empty( $wp_the_query->query_vars['cat'] ) ) {
				if ( isset( $this->_layout_settings['views']["is_category__{$wp_the_query->query_vars['cat']}"] ) && ( '//INHERIT//' != $this->_layout_settings['views']["is_category__{$wp_the_query->query_vars['cat']}"]['layout'] ) ) {
					$this->_layout_id = $this->_layout_settings['views']["is_category__{$wp_the_query->query_vars['cat']}"]['layout'];
					$this->_view_id = "is_category__{$wp_the_query->query_vars['cat']}";
				}
			}
			else if ( ! empty( $wp_the_query->query_vars['tag_id'] ) ) {
				if ( isset( $this->_layout_settings['views']["is_tag__{$wp_the_query->query_vars['tag_id']}"] ) && ( '//INHERIT//' != $this->_layout_settings['views']["is_tag__{$wp_the_query->query_vars['tag_id']}"]['layout'] ) ) {
					$this->_layout_id = $this->_layout_settings['views']["is_tag__{$wp_the_query->query_vars['tag_id']}"]['layout'];
					$this->_view_id = "is_tag__{$wp_the_query->query_vars['tag_id']}";
				}
			}
			else if ( ! empty( $wp_the_query->query_vars['author'] ) ) {
				if ( isset( $this->_layout_settings['views']["is_author__{$wp_the_query->query_vars['author']}"] ) && ( '//INHERIT//' != $this->_layout_settings['views']["is_author__{$wp_the_query->query_vars['author']}"]['layout'] ) ) {
					$this->_layout_id = $this->_layout_settings['views']["is_author__{$wp_the_query->query_vars['author']}"]['layout'];
					$this->_view_id = "is_author__{$wp_the_query->query_vars['author']}";
				}
			}
			else if ( ! empty( $wp_the_query->query_vars['post_type'] ) ) {
				if ( isset( $this->_layout_settings['views']["builder_is_custom_post_type__{$wp_the_query->query_vars['post_type']}"] ) && ( '//INHERIT//' != $this->_layout_settings['views']["builder_is_custom_post_type__{$wp_the_query->query_vars['post_type']}"]['layout'] ) ) {
					$this->_layout_id = $this->_layout_settings['views']["builder_is_custom_post_type__{$wp_the_query->query_vars['post_type']}"]['layout'];
					$this->_view_id = "builder_is_custom_post_type__{$wp_the_query->query_vars['post_type']}";
				}
			}
			
			
			$original_layout_id = $this->_layout_id;
			
			$this->_layout_id = apply_filters( 'builder_filter_current_layout', $this->_layout_id );
			
			if ( $this->_layout_id !== $original_layout_id )
				$this->_layout_functions[] = 'filter';
			
			
			if ( empty( $this->_layout_settings['layouts'][$this->_layout_id] ) ) {
				$this->_layout_id = $this->_layout_settings['default'];
				$this->_layout_functions[] = 'default';
			}
			
			if ( empty( $this->_layout_views ) )
				$this->_layout_views[] = 'default';
			
			
			$this->_layout = $this->_layout_settings['layouts'][$this->_layout_id]['modules'];
			
			do_action_ref_array( 'builder_layout_engine_identified_view', array( $this->_view_id, &$this->_layout_settings ) );
			do_action_ref_array( 'builder_layout_engine_identified_layout', array( $this->_layout_id, &$this->_layout_settings ) );
			
			do_action( 'builder_sidebar_register_layout_sidebars', $this->_layout_id );
		}
	}
	
	new BuilderLayoutEngine();
}

?>
