<?php

/*
Version: 1.0.4

Version History
	See history.txt
*/


if ( ! class_exists( 'iThemesFeedburnerWidget' ) ) {
	class iThemesFeedburnerWidget {
		var $_var = 'ithemes_feedburner_widget';
		var $_class = 'ithemes-feedburner-widget';
		var $_name = 'Feedburner';
		var $_page = 'feedburner-widget';
		var $_tab = 'Feedburner';
		var $_widgetName = 'Feedburner';
		var $_widgetDescription = 'Add a Feedburner subscription form';
		
		var $_initialized = false;
		var $_options = array();
		var $_errors = array();
		var $_pageRef = '';
		
		var $_usedInputs = array();
		var $_selectedVars = array();
		var $_pluginPath = '';
		var $_pluginRelativePath = '';
		var $_pluginURL = '';
		
		
		function iThemesFeedburnerWidget() {
			$this->_setVars();
			
			add_action( 'init', array( &$this, 'init' ), -10 );
			add_action( 'widgets_init', array( &$this, 'widgetsInit' ) );
			add_action( 'get_header', array( &$this, 'addStyles' ) );
		}
		
		function init() {
			$this->_load();
			
			$this->_initialized = true;
		}
		
		function widgetsInit() {
			global $wp_registered_sidebars;
			
			
			if ( ! is_array( $this->_options['widgets'] ) )
				$this->_options['widgets'] = array();
			
			
			$widget_ops = array( 'classname' => 'widget_' . $this->_var, 'description' => $this->_widgetDescription );
			$control_ops = array( 'width' => 330, 'height' => 350, 'id_base' => $this->_var );
			
			$registered = false;
			
			foreach ( (array) array_keys( $this->_options['widgets'] ) as $num ) {
				$id = $this->_var . '-' . $num;
				
				$registered = true;
				
				wp_register_sidebar_widget( $id, $this->_widgetName, array( &$this, 'widgetsRender' ), $widget_ops, array( 'number' => $num ) );
				wp_register_widget_control( $id, $this->_widgetName, array( &$this, 'widgetsControl' ), $control_ops, array( 'number' => $num ) );
			}
			
			if ( ! $registered ) {
				wp_register_sidebar_widget( $this->_var . '-1', $this->_widgetName, array( &$this, 'widgetsRender' ), $widget_ops, array( 'number' => -1 ) );
				wp_register_widget_control( $this->_var . '-1', $this->_widgetName, array( &$this, 'widgetsControl' ), $control_ops, array( 'number' => -1 ) );
			}
		}
		
		function addStyles() {
			wp_enqueue_style( $this->_var . '-theme-options', $this->_pluginURL . '/css/style.css.php' );
		}
		
		function _setVars() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = get_option( 'siteurl' ) . '/' . $this->_pluginRelativePath;
			
			// Double parenthesis added around array_shift argument to fix PHP 4.4/5.0.5 bug
			// http://the-stickman.com/web-development/php/php-505-fatal-error-only-variables-can-be-passed-by-reference/
			$page = ( isset( $_REQUEST['page'] ) ) ? $_REQUEST['page'] : '';
			$this->_selfLink = array_shift( ( explode( '?', $_SERVER['REQUEST_URI'] ) ) ) . '?page=' . $page;
		}
		
		
		// Options Storage ////////////////////////////
		
		function _initializeOptions() {
			$this->_options = array();
			
			$this->_options['groups'] = array();
			$this->_options['widgets'] = array();
			
			$this->_save();
		}
		
		function _save() {
			$data['groups'] = $this->_options['groups'];
			$data['widgets'] = $this->_options['widgets'];
			
			if ( $data == @get_option( $this->_var ) )
				return true;
			
			return @update_option( $this->_var, $data );
		}
		
		function _load() {
			$data = @get_option( $this->_var );
			
			if ( is_array( $data ) )
				$this->_options = $data;
			else
				$this->_initializeOptions();
		}
		
		
		// Widget Functions /////////////////////////
		
		function widgetsRender( $args, $widget_args = 1 ) {
			extract( $args, EXTR_SKIP );
			
			
			if ( is_numeric( $widget_args ) )
				$widget_args = array( 'number' => $widget_args );
			$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
			
			$widget = $this->_options['widgets'][$widget_args['number']];
			
			if ( empty( $widget['class'] ) ) $widget['class'] = '';
			if ( empty( $widget['link'] ) ) $widget['link'] = '';
			
			if ( preg_match( '/([^\/]+)\/*$/', $widget['link'], $matches ) )
				$widget['feed_name'] = $matches[1];
			
			
			echo $before_widget;
			
?>
	<?php if ( ! empty( $widget['title'] ) ) : ?>
		<?php echo $before_title . $widget['title'] . $after_title; ?>
	<?php endif; ?>
	<div class="<?php echo $widget['class']; ?>">
		<a class="feed-button" href="<?php echo $widget['link']; ?>" title="RSS Feed">Subscribe to <?php echo bloginfo( 'name' ); ?></a>
		
		<form action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open('http://feedburner.google.com/fb/a/mailverify?uri=<?php echo $widget['feed_name']; ?>&amp;loc=en_US', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true">
			<input class="input-text" type="text" name="email" value="<?php echo $widget['email_prompt']; ?>" onfocus="if (this.value == '<?php echo $widget['email_prompt']; ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php echo $widget['email_prompt']; ?>';}" />
			<input class="input-submit" type="submit" value="<?php echo $widget['submit_text']; ?>" />
			
			<input type="hidden" name="uri" value="<?php echo $widget['feed_name']; ?>" />
			<input type="hidden" name="loc" value="en_US" />
			<input type="hidden" name="title" value="<?php echo bloginfo( 'name' ); ?>" />
		</form>
		<p><?php echo $widget['description']; ?></p>
	</div>
<?php
			
			echo $after_widget;
		}
		
		function widgetsControl( $widget_args = 1 ) {
			global $wp_registered_widgets;
			static $updated = false;
			
			if ( is_numeric( $widget_args ) )
				$number = (int) $widget_args;
			elseif ( is_array( $widget_args ) && ! empty( $widget_args['number'] ) )
				$number = (int) $widget_args['number'];
			
			if ( empty( $number ) )
				$number = -1;
			
			
			if ( ! is_array( $this->_options['widgets'] ) )
				$this->_options['widgets'] = array();
			
			
			if ( ! $updated && ! empty( $_POST['sidebar'] ) ) {
				$sidebar = (string) $_POST['sidebar'];
				
				$widgets = wp_get_sidebars_widgets();
				
				if ( is_array( $widgets[$sidebar] ) ) {
					foreach ( (array) $widgets[$sidebar] as $id ) {
						if ( ( array( &$this, 'widgetsRender' ) == $wp_registered_widgets[$id]['callback'] ) && isset( $wp_registered_widgets[$id]['params'][0]['number'] ) ) {
							$num = $wp_registered_widgets[$id]['params'][0]['number'];
							if ( ! in_array( $this->_var . '-' . $num, $_POST['id'] ) )
								unset( $this->_options['widgets'][$num] );
						}
					}
				}
				
				foreach ( (array) $_POST[$this->_var] as $num => $widget )
					$this->_options['widgets'][$num] = $widget;
				
				$this->_save();
				
				$updated = true;
			}
			
			
			if ( -1 == $number )
				$number = '%i%';
			
			
			$classes = array();
			$classes['feedburner-basic'] = 'Basic';
			$classes['feedburner-light'] = 'Light';
			
			
			if ( ! isset( $this->_options['widgets'] ) )
				$this->_options['widgets'] = array();
			if ( ! isset( $this->_options['widgets'][$number] ) )
				$this->_options['widgets'][$number] = array();
			
			$defaults = array(
				'submit_text'	=> 'Subscribe Now!',
				'email_prompt'	=> 'Enter email address...',
				'description'	=> 'Get the latest updates delivered via email',
				'title'			=> '',
				'link'			=> '',
				'class'			=> '',
			);
			
			$this->_options['widgets'][$number] = wp_parse_args( $this->_options['widgets'][$number], $defaults );
			
			
?>
		<p><label for="<?php echo $this->_var . "-${number}-title"; ?>">
			Title (optional):<br />
			<?php $this->_addTextBox( "[$number][title]", array(), false, $this->_options['widgets'][$number]['title'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-link"; ?>">
			Feedburner Link:<br />
			<?php $this->_addTextBox( "[$number][link]", array( 'size' => '37' ), false, $this->_options['widgets'][$number]['link'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-description"; ?>">
			Description:<br />
			<?php $this->_addTextArea( "[$number][description]", array(), false, $this->_options['widgets'][$number]['description'] ); ?>
		</label></p>
		<br />
		
		<p><label for="<?php echo $this->_var . "-${number}-class"; ?>">
			Style:<br />
			<?php $this->_addDropDown( "[$number][class]", $classes, false, $this->_options['widgets'][$number]['class'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-submit_text"; ?>">
			Submit Button Text:<br />
			<?php $this->_addTextBox( "[$number][submit_text]", array(), false, $this->_options['widgets'][$number]['submit_text'] ); ?>
		</label></p>
		<p><label for="<?php echo $this->_var . "-${number}-email_prompt"; ?>">
			Email Input Text:<br />
			<?php $this->_addTextBox( "[$number][email_prompt]", array(), false, $this->_options['widgets'][$number]['email_prompt'] ); ?>
		</label></p>
<?php
			
		}
		
		
		// Form Functions ///////////////////////////
		
		function _newForm() {
			$this->_usedInputs = array();
		}
		
		function _addSubmit( $var, $options = array(), $override_value = true, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'submit';
			$options['name'] = $var;
			$options['class'] = ( empty( $options['class'] ) ) ? 'button-primary' : $options['class'];
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addButton( $var, $options = array(), $override_value = true, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'button';
			$options['name'] = $var;
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addTextBox( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'text';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addTextArea( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'textarea';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addFileUpload( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'file';
			$options['name'] = $var;
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addCheckBox( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'checkbox';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addMultiCheckBox( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'checkbox';
			$var = $var . '[]';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addRadio( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'radio';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addDropDown( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array();
			else if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'dropdown';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addHidden( $var, $options = array(), $override_value = false, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['type'] = 'hidden';
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $widget, $options, $override_value );
			else
				$this->_addSimpleInput( $var, $options, $override_value );
		}
		
		function _addHiddenNoSave( $var, $options = array(), $override_value = true, $widget = false ) {
			if ( ! is_array( $options ) )
				$options = array( 'value' => $options );
			
			$options['name'] = $var;
			
			$this->_addHidden( $var, $options, $override_value, $widget );
		}
		
		function _addDefaultHidden( $var ) {
			$options = array();
			$options['value'] = $this->defaults[$var];
			
			$var = "default_option_$var";
			
			if ( false !== $widget )
				$this->_addWidgetInput( $var, $options );
			else
				$this->_addSimpleInput( $var, $options );
		}
		
		function _addUsedInputs() {
			$options['type'] = 'hidden';
			$options['value'] = implode( ',', $this->_usedInputs );
			$options['name'] = 'used-inputs';
			
			$this->_addSimpleInput( 'used-inputs', $options, true );
		}
		
		function _addSimpleInput( $var, $options = false, $override_value = false ) {
			if ( empty( $options['type'] ) ) {
				echo "<!-- _addSimpleInput called without a type option set. -->\n";
				return false;
			}
			
			
			$scrublist['textarea']['value'] = true;
			$scrublist['file']['value'] = true;
			$scrublist['dropdown']['value'] = true;
			
			$defaults = array();
			$defaults['name'] = $this->_var . '-' . $var;
			
			$var = str_replace( '[]', '', $var );
			
			if ( 'checkbox' === $options['type'] )
				$defaults['class'] = $var;
			else
				$defaults['id'] = $var;
			
			$options = $this->_merge_defaults( $options, $defaults );
			
			if ( ( false === $override_value ) && isset( $this->_options[$var] ) ) {
				if ( 'checkbox' === $options['type'] ) {
					if ( (string) $value === (string) $options['value'] )
						$options['checked'] = 'checked';
				}
				elseif ( 'dropdown' !== $options['type'] )
					$options['value'] = $this->_options[$var];
			}
			
			if ( ( preg_match( '/^' . $this->_var . '/', $options['name'] ) ) && ( ! in_array( $options['name'], $this->_usedInputs ) ) )
				$this->_usedInputs[] = $options['name'];
			
			
			$attributes = '';
			
			if ( false !== $options )
				foreach ( (array) $options as $name => $val )
					if ( ! is_array( $val ) && ( true !== $scrublist[$options['type']][$name] ) )
						if ( ( 'submit' === $options['type'] ) || ( 'button' === $options['type'] ) )
							$attributes .= "$name=\"$val\" ";
						else
							$attributes .= "$name=\"" . esc_html( $val ) . '" ';
			
			
			if ( 'textarea' === $options['type'] )
				echo '<textarea ' . $attributes . '>' . $options['value'] . '</textarea>';
			elseif ( 'dropdown' === $options['type'] ) {
				echo "<select $attributes>\n";
				
				foreach ( (array) $options['value'] as $val => $name ) {
					$selected = ( (string) $this->_options[$var] === (string) $val ) ? ' selected="selected"' : '';
					echo "<option value=\"$val\"$selected>$name</option>\n";
				}
				
				echo "</select>\n";
			}
			else
				echo '<input ' . $attributes . '/>';
		}
		
		function _addWidgetInput( $var, $value, $options = false, $override_value = false ) {
			if ( empty( $options['type'] ) ) {
				echo "<!-- _addWidgetInput called without a type option set. -->\n";
				return false;
			}
			
			
			$scrublist['textarea']['value'] = true;
			$scrublist['file']['value'] = true;
			$scrublist['dropdown']['value'] = true;
			
			$defaults = array();
			$defaults['name'] = $this->_var . $var;
			
			$clean_var = $this->_var . $var;
			$clean_var = str_replace( '[', '-', $clean_var );
			$clean_var = str_replace( ']', '' , $clean_var );
			
			if ( 'checkbox' === $options['type'] )
				$defaults['class'] = $clean_var;
			else
				$defaults['id'] = $clean_var;
			
			$options = $this->_merge_defaults( $options, $defaults );
			
			if ( ( false === $override_value ) && isset( $value ) ) {
				if ( 'checkbox' === $options['type'] ) {
					if ( (string) $value === (string) $options['value'] )
						$options['checked'] = 'checked';
				}
				elseif ( 'dropdown' !== $options['type'] )
					$options['value'] = $value;
			}
			
			if ( ( preg_match( '/^' . $this->_var . '/', $options['name'] ) ) && ( ! in_array( $options['name'], $this->_usedInputs ) ) )
				$this->_usedInputs[] = $options['name'];
			
			
			$attributes = '';
			
			if ( false !== $options ) {
				foreach ( (array) $options as $name => $val ) {
					if ( ! is_array( $val ) && ( ! isset( $scrublist[$options['type']][$name] ) || ( true !== $scrublist[$options['type']][$name] ) ) ) {
						if ( ( 'submit' === $options['type'] ) || ( 'button' === $options['type'] ) )
							$attributes .= "$name=\"$val\" ";
						else
							$attributes .= "$name=\"" . esc_html( $val ) . '" ';
					}
				}
			}
			
			
			if ( 'textarea' === $options['type'] )
				echo '<textarea ' . $attributes . '>' . $options['value'] . '</textarea>';
			elseif ( 'dropdown' === $options['type'] ) {
				echo "<select $attributes>\n";
				
				foreach ( (array) $options['value'] as $val => $name ) {
					$selected = ( (string) $value === (string) $val ) ? ' selected="selected"' : '';
					echo "<option value=\"$val\"$selected>$name</option>\n";
				}
				
				echo "</select>\n";
			}
			else
				echo '<input ' . $attributes . '/>';
		}
		
		
		// Plugin Functions ///////////////////////////
		
		function _merge_defaults( $values, $defaults, $force = false ) {
			if ( ! $this->_is_associative_array( $defaults ) ) {
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
				
				$values[$key] = $this->_merge_defaults($values[$key], $val, $force );
			}
			
			return $values;
		}
		
		function _is_associative_array( &$array ) {
			if ( ! is_array( $array ) || empty( $array ) )
				return false;
			
			$next = 0;
			
			foreach ( $array as $k => $v )
				if ( $k !== $next++ )
					return true;
			
			return false;
		}
	}
}

if ( class_exists( 'iThemesFeedburnerWidget' ) && empty( $iThemesFeedburnerWidget ) ) {
	$iThemesFeedburnerWidget = new iThemesFeedburnerWidget();
}

?>
