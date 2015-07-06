<?php

/*
Written by Chris Jean for iThemes.com
Version 3.4.0

Version History
	3.0.0 - 2010-01-07
		Major rewite
	3.0.1 - 2010-01-07
		Improved width filters
		
		builder_module_filter_width filter changed to builder_module_filter_widths and argument
			changed to $fields array with a widths array entry
		builder_module_filter_calculated_widths filter added to allow for filtering widths after
			all calculations have finished
	3.0.2 - 2010-01-10
		Added class_prefix to class names that weren't using it
		Pass $fields through _modify_module_inner_wrapper_fields if the module supplies it
		Standardized wrapper divs around sidebar contents
		Added .builder-module-sidebar-SIDEBAR-TYPE to sidebar wrapper
	3.0.3 - 2010-01-10
		Fixed sidebar registration/rendering naming for modules that allow more than one instance
	3.0.4 - 2010-01-18
		Fixed bad width style declarations for the top and bottom sidebars.
		Added builder_module_render_VAR action that calls the render method.
		Removed _start and _end actions.
	3.0.5 - 2010-01-18
		Removed clearfix from the element inner wrapper for navigation as it causes problems in IE7 and IE 8
	3.0.6 - 2010-01-18
		Added padding:0 and margin:0 to all outer wrappers to prevent plugins from breaking widths.
	3.0.7 - 2010-02-17
		Added widget-wrapper-NUM classes
	3.0.8 - 2010-03-03
		Fixed wideget-wrapper-top typo, changing it to widget-wrapper-top
		Added builder-module-sidebar-with-element class
	3.0.9 - 2010-04-20
		Added code to handle the layout engine's new width-tracking code
	3.0.10 - 2010-04-22
		Added buider-module-element-outer-wrapper and builder-module-sidebar-outer-wrapper classes
	3.0.11 - 2010-04-26
		Internationalized strings
	3.1.0 - 2010-08-24
		Added support for alternate module styles
	3.1.1 - 2010-09-02
		Fixed warning condition for modules that support sidebars but aren't using one
	3.1.2 - 2010-10-05
		Changed ITError::fatal calls to ITError::admin_warn when sidebar_widths is empty
	3.1.3 - 2010-12-14
		Added export and import function placeholders
	3.2.0 - 2011-02-22
		Added max-width styling to modules (conditional), elements, and sidebars
		
		Added functions:
			show_conflicts_form
			_pre_render_validate
		Updated function import
	3.3.0 - 2011-07-05 - Chris Jean
		Moved some help info to tooltips
		Added support for all modules to have names
	3.4.0 - 2011-08-04 - Chris Jean
		Added widget-section-wrapper div
*/



if ( ! class_exists( 'LayoutModule' ) ) {
	class LayoutModule {
		var $_name = 'Replace Me';
		var $_var = 'replace_me';
		var $_description = 'Replace this description.';
		var $_max = 0; // 0 or 1
		var $_editor_width = 300;
		var $_module_count = 1;
		var $_has_sidebars = true;
		var $_can_remove_wrappers = false;
		var $_current_width = null;
		var $_module_styles = array();
		var $_module_sidebars = array();
		
		
		function LayoutModule() {
			add_action( 'builder_register_modules', array( &$this, 'register' ) );
			add_action( "builder_module_render_{$this->_var}", array( &$this, 'render' ) );
			add_action( "builder_module_render_contents_{$this->_var}", array( &$this, 'render_contents' ) );
			add_action( "builder_module_render_element_block_{$this->_var}", array( &$this, 'render_element_block' ) );
			add_action( "builder_module_render_element_block_contents_{$this->_var}", array( &$this, '_render' ) );
			add_action( "builder_module_render_sidebar_block_{$this->_var}", array( &$this, 'render_sidebar_block' ), 10, 2 );
			add_action( "builder_module_render_sidebar_block_contents_{$this->_var}", array( &$this, 'render_sidebar_block_contents' ), 10, 2 );
			
			if ( ! empty( $_REQUEST['modify_module_settings'] ) )
				$module = $_REQUEST['modify_module_settings'];
			else if ( ! empty( $_REQUEST['add_module'] ) )
				$module = $_REQUEST['module'];
			
			
			if ( ! empty( $module ) && ( $module === $this->_var ) ) {
				add_action( 'builder_module_enqueue_admin_scripts', array( &$this, 'enqueue_admin_scripts' ) );
				add_action( 'builder_module_enqueue_admin_styles', array( &$this, 'enqueue_admin_styles' ) );
			}
		}
		
		function enqueue_admin_scripts( $url_base ) {
			if ( true === $this->_has_sidebars )
				wp_enqueue_script( "{$this->_var}-sidebar-script", "$url_base/js/sidebars.js" );
			
			if ( file_exists( dirname( __FILE__ ) . "/{$this->_var}/js/module.js" ) )
				wp_enqueue_script( "{$this->_var}-module-script", "$url_base/{$this->_var}/js/module.js" );
		}
		
		function enqueue_admin_styles( $url_base ) {
			// Meant to be overridden.
		}
		
		function register() {
			global $builder_modules;
			
			$builder_modules->register_module( $this );
		}
		
		function export( $data ) {
			return $data;
		}
		
		function show_conflicts_form( $form, $data ) {
			// return true if user interaction with the output is needed
		}
		
		function import( $data, $attachments, $post_data ) {
			return $data;
		}
		
		function get_editor_width() {
			return $this->_editor_width;
		}
		
		function get_layout_option() {
			if ( true === $this->_has_sidebars )
				return 'sidebar';
			else
				return 'notused';
		}
		
		function get_defaults() {
			$defaults = array();
			
			$this->_init_module_styles();
			
			$defaults['name'] = $this->_name;
			
			if ( ! empty( $this->_module_styles ) )
				$defaults['style'] = '';
			if ( true === $this->_has_sidebars ) {
				$defaults['sidebar'] = '1_right';
				$defaults['sidebar_widths'] = '180';
				$defaults['custom_sidebar_widths'] = '';
			}
			if ( true === $this->_can_remove_wrappers )
				$defaults['remove_wrappers'] = '';
			
			if ( method_exists( &$this, '_get_defaults' ) )
				$defaults = $this->_get_defaults( $defaults );
			
			return $defaults;
		}
		
		function validate() {
			$result = array( 'data' => $_POST );
			
			if ( empty( $_POST['name'] ) )
				$result['data']['name'] = $this->_name;
			
			if ( true === $this->_has_sidebars ) {
				if ( isset( $_POST['sidebar_widths'] ) && ( 'custom' === $_POST['sidebar_widths'] ) ) {
					$result['data']['custom_sidebar_widths'] = preg_replace( '/\s+/', '', $_POST['custom_sidebar_widths'] );
					
					$expected_widths = array(
						'none'      => 0,
						'1_left'    => 1,
						'2_left'    => 2,
						'split'     => 2,
						'1_right'   => 1,
						'2_right'   => 2,
						'split'     => 2,
						'split_1_2' => 3,
						'split_2_1' => 3,
						'split_2_2' => 4,
					);
					
					$num_widths = count( explode( ',', $_POST['custom_sidebar_widths'] ) );
					
					if ( ( 1 === $expected_widths[$_POST['sidebar']] ) && ( 1 !== $num_widths ) )
						$result['errors'][] = __( 'You must supply a width for the sidebar', 'it-l10n-Builder' );
					if ( $num_widths != $expected_widths[$_POST['sidebar']] )
						$result['errors'][] = sprintf( __( 'You must supply %s widths separated by commas', 'it-l10n-Builder' ), $expected_widths[$_POST['sidebar']] );
					else if ( preg_match( '/[^0-9,\.]/', $result['data']['custom_sidebar_widths'] ) ) {
						$result['errors'][] = __( 'Only numbers can be used for sidebar widths', 'it-l10n-Builder' );
					}
					else if ( array_sum( explode( ',', $result['data']['custom_sidebar_widths'] ) ) > ( $_POST['layout_width'] - 200 ) )
						$result['errors'][] = __( 'You must leave at least a 200 pixel area for the module content to render in. Please reduce the size of the sidebar widths.', 'it-l10n-Builder' );
					else {
						foreach ( (array) explode( ',', $result['data']['custom_sidebar_widths'] ) as $width ) {
							if ( $width < 20 ) {
								$result['errors'][] = __( 'The minimum width for a sidebar is 20 pixels. Please ensure that all of your custom sidebar widths are 20 pixels or larger.', 'it-l10n-Builder' );
								break;
							}
						}
					}
				}
			}
			
			if ( method_exists( &$this, '_validate' ) )
				$result = $this->_validate( $result );
			
			return $result;
		}
		
		function edit( $form, $results = true ) {
			if ( isset( $results['errors'] ) )
				$this->_print_errors( $results['errors'] );
			
			
			$this->_init_module_styles();
			
			
			if ( method_exists( &$this, '_before_table_edit' ) )
				$this->_before_table_edit( $form, $results );
			
			echo "<table class='valign-top'>\n";
			
?>
	<tr><td><label for="name"><?php _e( 'Name', 'it-l10n-Builder' ); ?></label></td>
		<td>
			<?php $form->add_text_box( 'name' ); ?>
			<?php ITUtility::add_tooltip( __( 'The module\'s name helps you identify specific widget locations. Descriptive, short names typically work best.', 'it-l10n-Builder' ) ); ?>
			<br /><br />
		</td>
	</tr>
<?php
			
			if ( method_exists( &$this, '_start_table_edit' ) )
				$this->_start_table_edit( $form, $results );
			
			
			if ( true === $this->_has_sidebars ) {
				$sidebars = array(
					'none'      => __( 'No Sidebars', 'it-l10n-Builder' ),
					'1_left'    => __( '1 Left', 'it-l10n-Builder' ),
					'2_left'    => __( '2 Left', 'it-l10n-Builder' ),
					'1_right'   => __( '1 Right', 'it-l10n-Builder' ),
					'2_right'   => __( '2 Right', 'it-l10n-Builder' ),
					'split'     => __( 'Split (1 Left & 1 Right)', 'it-l10n-Builder' ),
/*					'split_1_2' => __( 'Split (1 Left & 2 Right)', 'it-l10n-Builder' ),
					'split_2_1' => __( 'Split (2 Left & 1 Right)', 'it-l10n-Builder' ),
					'split_2_2' => __( 'Split (2 Left & 2 Right)', 'it-l10n-Builder' ),*/
				);
				
				$default_widths = array(
					'none'      => array(),
					'1_left'    => array(
						'150'     => __( 'Narrow (150px)', 'it-l10n-Builder' ),
						'180'     => __( 'Standard (180px)', 'it-l10n-Builder' ),
						'200'     => __( 'Wide (200px)', 'it-l10n-Builder' ),
						'custom'  => __( 'Custom...', 'it-l10n-Builder' ),
					),
					'2_left'    => array(
						'150,150' => __( 'Narrow/Narrow (150px/150px)', 'it-l10n-Builder' ),
						'150,180' => __( 'Narrow/Standard (150px/180px)', 'it-l10n-Builder' ),
						'150,200' => __( 'Narrow/Wide (150px/200px)', 'it-l10n-Builder' ),
						'180,150' => __( 'Standard/Narrow (180px/150px)', 'it-l10n-Builder' ),
						'180,180' => __( 'Standard/Standard (180px/180px)', 'it-l10n-Builder' ),
						'180,200' => __( 'Standard/Wide (180px/200px)', 'it-l10n-Builder' ),
						'200,150' => __( 'Wide/Narrow (200px/150px)', 'it-l10n-Builder' ),
						'200,180' => __( 'Wide/Standard (200px/180px)', 'it-l10n-Builder' ),
						'200,200' => __( 'Wide/Wide (200px/200px)', 'it-l10n-Builder' ),
						'custom'  => __( 'Custom...', 'it-l10n-Builder' ),
					),
/*					'split_1_2' => array(
						'150,150,150' => __( 'Narrow/Narrow/Narrow (150px/150px/150px)', 'it-l10n-Builder' ),
						'150,150,180' => __( 'Narrow/Narrow/Standard (150px/150px/180px)', 'it-l10n-Builder' ),
						'150,180,150' => __( 'Narrow/Standard/Narrow (150px/180px/150px)', 'it-l10n-Builder' ),
						'150,180,180' => __( 'Narrow/Standard/Standard (150px/180px/180px)', 'it-l10n-Builder' ),
						'180,150,150' => __( 'Standard/Narrow/Narrow (180px/150px/150px)', 'it-l10n-Builder' ),
						'180,150,180' => __( 'Standard/Narrow/Standard (180px/150px/180px)', 'it-l10n-Builder' ),
						'180,180,150' => __( 'Standard/Standard/Narrow (180px/180px/150px)', 'it-l10n-Builder' ),
						'180,180,180' => __( 'Standard/Standard/Standard (180px/180px/180px)', 'it-l10n-Builder' ),
						'custom'      => __( 'Custom...', 'it-l10n-Builder' ),
					),
					'split_2_2' => array(
						'150,150,150,150' => __( 'Default (150px/150px/150px/150px)', 'it-l10n-Builder' ),
						'custom'          => __( 'Custom...', 'it-l10n-Builder' ),
					),*/
				);
				
				$default_widths['1_right'] = $default_widths['1_left'];
				$default_widths['2_right'] = $default_widths['2_left'];
				$default_widths['split'] = $default_widths['2_left'];
//				$default_widths['split_2_1'] = $default_widths['split_1_2'];
				
				if ( isset( $_REQUEST['sidebar'] ) && isset( $default_widths[$_REQUEST['sidebar']] ) )
					$starting_sidebar_widths = $default_widths[$_REQUEST['sidebar']];
				else
					$starting_sidebar_widths = null;
				
?>
	<tr><td><label for="sidebar"><?php _e( 'Sidebars', 'it-l10n-Builder' ); ?></label></td>
		<td>
			<?php $form->add_drop_down( 'sidebar', $sidebars ); ?>
			<?php ITUtility::add_tooltip( __( 'Sidebars can be added to this module so that you can have widgets to the left, right, or on both sides of the modules content.', 'it-l10n-Builder' ) ); ?>
			
			<?php if ( 'navigation' == $this->_var ) : ?>
				<p><strong><?php _e( 'Note: The Sidebar feature is still experimental for this module.' ); ?></strong></p>
			<?php endif; ?>
		</td>
	</tr>
	<tr id="sidebar-widths-row">
		<td><?php _e( 'Sidebar&nbsp;Widths', 'it-l10n-Builder' ); ?></td>
		<td>
			<?php $form->add_drop_down( 'sidebar_widths', null ); ?>
			<?php ITUtility::add_tooltip( __( 'You can make the widget areas wider or narrower by using these options. To get more control over the widths used, select "Custom..." to put in your own widths.', 'it-l10n-Builder' ) ); ?>
			
			<div id="sidebar-widths-custom" style="display:none;">
				<?php $form->add_text_box( 'custom_sidebar_widths', array( 'size' => '10', 'maxlength' => '20' ) ); ?><br />
				<?php _e( 'Specify the width of each sidebar in pixels.', 'it-l10n-Builder' ); ?><br />
				<?php _e( 'For example, if you selected to have 2 Right sidebars and want to separate them into 170 pixel and 185 pixel widths, input <strong><code>170,185</code></strong> in the box above.', 'it-l10n-Builder' ); ?>
			</div>
		</td>
	</tr>
<?php
				
			}
			
			if ( ! empty( $this->_module_styles ) ) {
				asort( $this->_module_styles );
				
				$styles = array( '' => 'Default' );
				$styles = array_merge( $styles, $this->_module_styles );
				
?>
	<tr>
		<td colspan="2">
			<br />
			<p>The active child theme (theme design) provides alternate styling options for this module. Use the following option to select an alternate style if desired.</p>
		</td>
	</tr>
	<tr><td><label for="sidebar"><?php _e( 'Style', 'it-l10n-Builder' ); ?></label></td>
		<td><?php $form->add_drop_down( 'style', $styles ); ?></td>
	</tr>
<?php
				
			}
			
			
			if ( method_exists( &$this, '_end_table_edit' ) )
				$this->_end_table_edit( $form, $results );
			
			if ( true === $this->_can_remove_wrappers ) {
				
?>
	<tr><td><label for="remove_wrappers"><?php _e( 'Remove&nbsp;Wrapper&nbsp;DIVs', 'it-l10n-Builder' ); ?></label></td>
		<td>
			<?php $form->add_drop_down( 'remove_wrappers', array( '' => __( 'No', 'it-l10n-Builder' ), '1' => __( 'Yes', 'it-l10n-Builder' ) ) ); ?><?php ITUtility::add_tooltip( __( 'Enabling this option is only recommended for advanced users as this can cause layout issues if not properly handled.', 'it-l10n-Builder' ) ); ?>
		</td>
	</tr>
<?php
				
			}
			
			echo "</table>\n";
			
			if ( method_exists( &$this, '_after_table_edit' ) )
				$this->_after_table_edit( $form, $results );
			
			
			if ( true === $this->_has_sidebars ) {
				
?>
	<div id="sidebar-widths-container" style="display:none;">
		<?php foreach ( (array) $default_widths as $id => $options ) : ?>
			<div id="sidebar-widths-container-<?php echo $id; ?>">
				<?php $form->add_drop_down( "sidebar-widths-$id", $options ); ?>
			</div>
		<?php endforeach; ?>
	</div>
	
	<script type="text/javascript">
		/* <![CDATA[ */
		init_module_sidebar_editor();
		/* ]]> */
	</script>
<?php
				
			}
			
			
			if ( isset( $_REQUEST['save'] ) )
				$form->add_hidden( 'had_error', '1' );
		}
		
		function _print_errors( $errors ) {
			if ( ! empty( $errors ) ) {
				foreach ( (array) $errors as $error )
					echo "<div id=\"message\" class=\"error\"><p><strong>$error</strong></p></div>\n";
				
				echo "<br />\n";
			}
		}
		
		function _pre_render_validate() {
			return true;
		}
		
		function render( $fields ) {
			$fields['remove_wrappers'] = false;
			if ( ( true === $this->_can_remove_wrappers ) && ( ! empty( $fields['data']['remove_wrappers'] ) ) )
				$fields['remove_wrappers'] = true;
			
			$fields = apply_filters( 'builder_module_filter_render_fields', $fields );
			
			$this->_data = $fields['data'];
			$this->_get_widths( $fields );
			
			if ( true !== $this->_pre_render_validate() )
				return;
			
			
			$this->_open_module_wrappers( $fields );
			
			do_action( 'builder_module_render_contents', $fields );
			
			$this->_close_module_wrappers( $fields );
		}
		
		function render_contents( $fields ) {
			if ( true === $this->_has_sidebars ) {
				if ( in_array( $fields['data']['sidebar'], array( '1_left', '2_left', 'split' ) ) )
					do_action( 'builder_module_render_sidebar_block', $fields, 'left' );
				
				do_action( 'builder_module_render_element_block', $fields );
				
				if ( in_array( $fields['data']['sidebar'], array( '1_right', '2_right', 'split' ) ) )
					do_action( 'builder_module_render_sidebar_block', $fields, 'right' );
			}
			else
				do_action( 'builder_module_render_element_block', $fields );
		}
		
		function _get_widths( $fields ) {
			$data = $fields['data'];
			
			
			$fields['widths']['container_width'] = apply_filters( 'builder_get_container_width', 0 );
			$fields['widths']['content_width'] = $fields['widths']['container_width'];
			$fields = apply_filters( 'builder_module_filter_widths', $fields );
			
			$this->_widths = $fields['widths'];
			$this->_widths['element_width'] = $fields['widths']['content_width'];
			$this->_widths['sidebar_widths'] = array();
			$this->_widths['full_sidebar_width'] = 0;
			
			if ( true === $this->_has_sidebars ) {
				if ( 'custom' !== $data['sidebar_widths'] )
					$this->_widths['sidebar_widths'] = explode( ',', $data['sidebar_widths'] );
				else
					$this->_widths['sidebar_widths'] = explode( ',', $data['custom_sidebar_widths'] );
				
				$this->_widths['full_sidebar_width'] = array_sum( (array) $this->_widths['sidebar_widths'] );
				
				$this->_widths['element_width'] = $this->_widths['content_width'] - $this->_widths['full_sidebar_width'];
			}
			
			$fields['widths'] = $this->_widths;
			$fields = apply_filters( 'builder_module_filter_calculated_widths', $fields );
			$this->_widths = $fields['widths'];
		}
		
		function render_element_block( $fields ) {
			$this->_open_element_wrappers( $fields );
			
			do_action( 'builder_module_render_element_block_contents', $fields );
			
			$this->_close_element_wrappers( $fields );
		}
		
		function _open_element_wrappers( $fields ) {
			$data = $fields['data'];
			
			if ( false === $fields['remove_wrappers'] ) {
				if ( ( true !== $this->_has_sidebars ) || ( 'none' === $data['sidebar'] ) )
					$class = 'single';
				else if ( 'split' === $data['sidebar'] )
					$class = 'middle';
				else if ( preg_match( '/left/', $data['sidebar'] ) )
					$class = 'right';
				else
					$class = 'left';
				
				$fields['attributes'] = array( 'class' => array( "{$fields['class_prefix']}-block-outer-wrapper", "{$fields['class_prefix']}-element-outer-wrapper", $class, 'clearfix' ), 'style' => array( "width:{$this->_widths['element_width']}px;", "max-width:{$this->_widths['element_width']}px;", 'overflow:hidden;', 'padding:0;', 'margin:0' ) );
				$outer_wrapper_results = apply_filters( 'builder_module_filter_element_outer_wrapper_attributes', $fields );
				
				
				$fields['attributes'] = array( 'class' => array( "{$fields['class_prefix']}-block", "{$fields['class_prefix']}-element" ) );
				if ( 'navigation' !== $this->_var )
					$fields['attributes']['class'][] = 'clearfix';
				$inner_wrapper_results = apply_filters( 'builder_module_filter_element_inner_wrapper_attributes', $fields );
				
				ITUtility::print_open_tag( 'div', $outer_wrapper_results['attributes'] );
				ITUtility::print_open_tag( 'div', $inner_wrapper_results['attributes'] );
				
				do_action( 'builder_layout_engine_set_current_area_width', $this->_widths['element_width'] );
			}
		}
		
		function _close_element_wrappers( $fields ) {
			if ( false === $fields['remove_wrappers'] ) {
				do_action( 'builder_layout_engine_set_current_area_width', null );
				
				echo "\n</div>\n</div>\n";
			}
		}
		
		function _open_module_wrappers( $fields ) {
			if ( false === $fields['remove_wrappers'] ) {
				$this->_render_module_outer_wrapper( $fields );
				$this->_render_module_inner_wrapper( $fields );
			}
		}
		
		function _close_module_wrappers( $fields ) {
			if ( false === $fields['remove_wrappers'] )
				echo "\n</div>\n</div>\n";
		}
		
		function _render_module_outer_wrapper( $fields ) {
			$fields['attributes'] = $fields['outer_wrapper'];
			$fields['attributes']['style'] = array( "width:{$this->_widths['container_width']}px;", "max-width:{$this->_widths['container_width']}px;", 'padding:0;', 'margin:0 auto;' , 'display:block;', 'clear:both;', 'overflow:hidden;' );
			
			$results = apply_filters( 'builder_module_filter_outer_wrapper_attributes', $fields );
			
			
			$width_index = $max_width_index = false;
			
			foreach ( (array) $results['attributes']['style'] as $index => $style ) {
				if ( 'width:' === strtolower( substr( $style, 0, 6 ) ) )
					$width_index = $index;
				else if ( 'max-width:' === strtolower( substr( $style, 0, 10 ) ) )
					$max_width_index = $index;
			}
			
			if ( ( false === $width_index ) && ( false !== $max_width_index ) )
				unset( $results['attributes']['style'][$max_width_index] );
			
			
			ITUtility::print_open_tag( 'div', $results['attributes'] );
		}
		
		function _render_module_inner_wrapper( $fields ) {
			$fields['attributes'] = $fields['inner_wrapper'];
			
			if ( method_exists( $this, '_modify_module_inner_wrapper_fields' ) )
				$fields = $this->_modify_module_inner_wrapper_fields( $fields );
			
			$results = apply_filters( 'builder_module_filter_inner_wrapper_attributes', $fields );
			
			ITUtility::print_open_tag( 'div', $results['attributes'] );
			
			$this->_module_count++;
		}
		
		function render_sidebar_block( $fields, $side ) {
			$data = $fields['data'];
			
			if ( empty( $data['sidebar_widths'] ) )
				ITError::admin_warn( 'empty_var:parameter:data[\'sidebar_widths\']', __( 'Unable to decide widths due to missing sidebar_widths value.', 'it-l10n-Builder' ) );
			
			if ( "2_$side" === $data['sidebar'] )
				$this->_open_sidebar_wrappers( $fields, $side, $this->_widths['full_sidebar_width'] );
			else if ( in_array( $data['sidebar'], array( "1_$side", 'split' ) ) ) {
				if ( 'left' === $side )
					$width = $this->_widths['sidebar_widths'][0];
				else
					$width = ( '1_right' === $data['sidebar'] ) ? $this->_widths['sidebar_widths'][0] : $this->_widths['sidebar_widths'][1];
				
				$this->_open_sidebar_wrappers( $fields, $side, $width );
			}
			
			do_action( 'builder_module_render_sidebar_block_contents', $fields, $side );
			
			$this->_close_sidebar_wrappers( $fields );
		}
		
		function render_sidebar_block_contents( $fields, $side ) {
			$data = $fields['data'];
			
			if ( 'none' === $data['sidebar'] )
				return;
			
			if ( empty( $data['sidebar_widths'] ) )
				ITError::admin_warn( 'empty_var:parameter:data[\'sidebar_widths\']', __( 'Unable to decide widths due to missing sidebar_widths value.', 'it-l10n-Builder' ) );
			
			
			$name = $this->_get_sidebar_name( $fields['layout'], $fields['guid'], $data );
			
			
			if ( method_exists( &$this, '_render_sidebar_block_contents' ) )
				$this->_render_sidebar_block_contents( $fields, $side, $name );
			
			
			if ( ( true === $this->_has_sidebars ) && ( ! empty( $data['sidebar'] ) ) ) {
				if ( "2_$side" === $data['sidebar'] ) {
					echo "<div class='widget-outer-wrapper widget-outer-wrapper-top' style='clear:both; width:{$this->_widths['full_sidebar_width']}px; max-width:{$this->_widths['full_sidebar_width']}px; overflow:hidden;'>\n";
					$this->_render_sidebar( $fields, "$name - Top", array( 'class' => array( 'widget-wrapper', 'widget-wrapper-single', 'widget-wrapper-top', 'single', 'widget-wrapper-1' ) ) );
					echo "</div>\n";
					
					echo "<div class='widget-section-wrapper'>\n";
					
					echo "<div class='widget-outer-wrapper widget-outer-wrapper-left' style='float:left; width:{$this->_widths['sidebar_widths'][0]}px; max-width:{$this->_widths['sidebar_widths'][0]}px; overflow:hidden;'>\n";
					do_action( 'builder_layout_engine_set_current_area_width', $this->_widths['sidebar_widths'][0] );
					$this->_render_sidebar( $fields, "$name - Left", array( 'class' => array( 'widget-wrapper', 'widget-wrapper-left', 'left', 'widget-wrapper-1' ) ) );
					echo "</div>\n";
					
					echo "<div class='widget-outer-wrapper widget-outer-wrapper-right' style='float:right; width:{$this->_widths['sidebar_widths'][1]}px; max-width:{$this->_widths['sidebar_widths'][1]}px; overflow:hidden;'>\n";
					do_action( 'builder_layout_engine_set_current_area_width', $this->_widths['sidebar_widths'][1] );
					$this->_render_sidebar( $fields, "$name - Right", array( 'class' => array( 'widget-wrapper', 'widget-wrapper-right', 'right', 'widget-wrapper-2' ) ) );
					echo "</div>\n";
					
					echo "</div>\n";
					
					echo "<div class='widget-outer-wrapper widget-outer-wrapper-bottom' style='clear:both; width:{$this->_widths['full_sidebar_width']}px; max-width:{$this->_widths['full_sidebar_width']}px; overflow:hidden;'>\n";
					do_action( 'builder_layout_engine_set_current_area_width', $this->_widths['full_sidebar_width'] );
					$this->_render_sidebar( $fields, "$name - Bottom", array( 'class' => array( 'widget-wrapper', 'widget-wrapper-single', 'widget-wrapper-bottom', 'single', 'widget-wrapper-1' ) ) );
					echo "</div>\n";
				}
				else if ( preg_match( "/^(1_$side|split)$/", $data['sidebar'] ) ) {
					if ( 'left' === $side )
						$width = $this->_widths['sidebar_widths'][0];
					else
						$width = ( '1_right' === $data['sidebar'] ) ? $this->_widths['sidebar_widths'][0] : $this->_widths['sidebar_widths'][1];
					
					$this->_render_sidebar( $fields, "$name - " . ucfirst( $side ), array( 'class' => array( 'widget-wrapper', 'widget-wrapper-single', 'single', 'widget-wrapper-1' ) ) );
				}
			}
		}
		
		function _open_sidebar_wrappers( $fields, $side, $width ) {
			$sidebar_type_class = '';
			$with_element_sidebar_class = '';
			if ( isset( $fields['data']['sidebar'] ) ) {
				$sidebar_type = str_replace( '_', '-', $fields['data']['sidebar'] );
				$sidebar_type_class = "{$fields['class_prefix']}-sidebar-$sidebar_type";
				
				$with_element_sidebar_class = "{$fields['class_prefix']}-sidebar-with-element";
			}
			
			$fields['attributes'] = array( 'class' => array( "{$fields['class_prefix']}-block-outer-wrapper", "{$fields['class_prefix']}-sidebar-outer-wrapper", $side, 'clearfix' ), 'style' => array( "width:{$width}px;", "max-width:{$width}px;", 'overflow:hidden;', 'padding:0;', 'margin:0;' ) );
			$outer_wrapper_results = apply_filters( 'builder_module_filter_sidebar_outer_wrapper_attributes', $fields );
			
			$fields['attributes'] = array( 'class' => array( "{$fields['class_prefix']}-block", "{$fields['class_prefix']}-sidebar", $sidebar_type_class, $with_element_sidebar_class, 'sidebar', $side, 'clearfix' ) );
			$inner_wrapper_results = apply_filters( 'builder_module_filter_sidebar_inner_wrapper_attributes', $fields );
			
			ITUtility::print_open_tag( 'div', $outer_wrapper_results['attributes'] );
			ITUtility::print_open_tag( 'div', $inner_wrapper_results['attributes'] );
			
			do_action( 'builder_layout_engine_set_current_area_width', $width );
		}
		
		function _close_sidebar_wrappers( $fields ) {
			do_action( 'builder_layout_engine_set_current_area_width', null );
			
			echo "\n</div>\n</div>\n";
		}
		
		function _render_sidebar( $fields, $name, $attributes ) {
			$fields['sidebar_name'] = $name;
			$fields['attributes'] = $attributes;
			
			$results = apply_filters( 'builder_module_filter_sidebar_wrapper_attributes', $fields );
			
			ITUtility::print_open_tag( 'div', $results['attributes'] );
			
			unset( $fields['attributes'] );
			do_action( 'builder_sidebar_render', $fields );
			
			echo "</div>\n";
		}
		
		function register_sidebars( $module_data, $id, $layout ) {
			$data = $module_data['data'];
			$guid = $module_data['guid'];
			
			$name = $this->_get_sidebar_name( $layout, $guid, $data );
			
			
			if ( method_exists( &$this, '_register_sidebars' ) )
				$this->_register_sidebars( $module_data, $id, $layout, $name );
			
			
			if ( ( true === $this->_has_sidebars ) && ( ! empty( $data['sidebar'] ) ) ) {
//				if ( 1 !== $this->_max )
//					$name .= " - $id";
				
				if ( preg_match( '/^(split_1_2|split_2_1|split_2_2)$/', $data['sidebar'] ) ) {
					if ( 'split_1_2' === $data['sidebar'] )
						$this->_register_sidebar( "$name - Left", $guid );
					else {
						$this->_register_sidebar( "$name - Left - Top", $guid );
						$this->_register_sidebar( "$name - Left - Left", $guid );
						$this->_register_sidebar( "$name - Left - Right", $guid );
						$this->_register_sidebar( "$name - Left - Bottom", $guid );
					}
					
					if ( 'split_2_1' === $data['sidebar'] )
						$this->_register_sidebar( "$name - Right", $guid );
					else {
						$this->_register_sidebar( "$name - Right - Top", $guid );
						$this->_register_sidebar( "$name - Right - Left", $guid );
						$this->_register_sidebar( "$name - Right - Right", $guid );
						$this->_register_sidebar( "$name - Right - Bottom", $guid );
					}
				}
				else {
					if ( preg_match( '/^(2_right|2_left)$/', $data['sidebar'] ) )
						$this->_register_sidebar( "$name - Top", $guid );
					
					if ( preg_match( '/^(1_left|2_right|2_left|split)$/', $data['sidebar'] ) )
						$this->_register_sidebar( "$name - Left", $guid );
					
					if ( preg_match( '/^(1_right|2_right|2_left|split)$/', $data['sidebar'] ) )
						$this->_register_sidebar( "$name - Right", $guid );
					
					if ( preg_match( '/^(2_right|2_left)$/', $data['sidebar'] ) )
						$this->_register_sidebar( "$name - Bottom", $guid );
				}
			}
		}
		
		function _get_sidebar_name( $layout, $guid, $data ) {
			if ( isset( $this->_layout_sidebar_base_name ) && isset( $this->_layout_sidebar_base_name[$guid] ) )
				return $this->_layout_sidebar_base_name[$guid];
			
			
			$name = ( empty( $data['name'] ) ) ? $this->_name : $data['name'];
			
			if ( ! isset( $this->_layout_modules_cache ) )
				$this->_layout_modules_cache = array();
			if ( ! isset( $this->_layout_modules_cache[$layout] ) )
				$this->_layout_modules_cache[$layout] = array();
			if ( ! isset( $this->_layout_modules_cache[$layout][$name] ) )
				$this->_layout_modules_cache[$layout][$name] = 0;
			
			if ( ++$this->_layout_modules_cache[$layout][$name] > 1 )
				$name .= " {$this->_layout_modules_cache[$layout][$name]}";
			
			if ( ! is_admin() || ! preg_match( '/\/widgets\.php$/', $_SERVER['SCRIPT_NAME'] ) || empty( $_REQUEST['builder_layout_id'] ) )
				$name = "$layout - $name";
			
			
			if ( ! isset( $this->_layout_sidebar_base_name ) )
				$this->_layout_sidebar_base_name = array();
			
			$this->_layout_sidebar_base_name[$guid] = $name;
			
			
			return $name;
		}
		
		function _register_sidebar( $options, $guid ) {
			if ( ! is_array( $options ) )
				$options = array( 'name' => $options );
			
			
			if ( ! isset( $this->_guid_count[$guid] ) )
				$this->_guid_count[$guid] = 1;
			else
				$this->_guid_count[$guid]++;
			
			
			$default_options = array(
				'id' => "$guid-{$this->_guid_count[$guid]}",
			);
			$options = array_merge( $default_options, $options );
			
			
			do_action( 'builder_sidebar_register', $options );
		}
		
		function _init_module_styles() {
			global $builder_module_styles;
			
			if ( ! empty( $this->_module_styles ) )
				return;
			
			if ( isset( $builder_module_styles['*'] ) )
				$this->_module_styles = array_merge( $this->_module_styles, $builder_module_styles['*'] );
			if ( isset( $builder_module_styles[$this->_var] ) )
				$this->_module_styles = array_merge( $this->_module_styles, $builder_module_styles[$this->_var] );
			
			$this->_module_styles = apply_filters( 'builder_module_register_styles', $this->_module_styles, $this->_var );
			$this->_module_styles = apply_filters( "builder_module_register_styles_{$this->_var}", $this->_module_styles );
			
			if ( ! is_array( $this->_module_styles ) )
				$this->_module_styles = array();
		}
	}
	
	// The following line should be uncommented and updated for active modules.
//	new LayoutModule();
}
