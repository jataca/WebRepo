<?php
/**
 *
 * Adds widget capabilities.
 *
 * Author:	Dustin Bolton
 * Created:	January 15, 2010
 * Update:	October 13, 2010
 *
 * Version: 2.0.0
 *
 */

 
class widget_pluginbuddy_vidembed extends WP_Widget {
	var $_widget_control_width = 300;
	var $_widget_control_height = 300;
	
	
	/**
	 * Default constructor.
	 * 
	 * @return void
	 */
	function widget_pluginbuddy_vidembed() {
		global $pluginbuddy_vidembed;
		$this->_parent = &$pluginbuddy_vidembed;
		
		$this->WP_Widget( $this->_parent->_var, $this->_parent->_name, array( 'description' => $this->_parent->_widget ) );
	}
	
	
	/**
	 * widget()
	 *
	 * Display public widget.
	 *
	 * @param	array	$args		Widget arguments -- currently not in use.
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function widget($args, $instance) {
			echo $args['before_widget'];
		
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'];
				echo apply_filters( 'widget_title', $instance['title'] );
				echo $args['after_title'];
			}
		
			do_action( 'pluginbuddy_vidembed-widget', $instance, true);
		
			echo $args['after_widget'];
	}
	
	
	
	/**
	 * update()
	 *
	 * Save widget form settings.
	 *
	 * @param	array	$new_instance	NEW instance data including title, group id, etc.
	 * @param	array	$old_instance	PREVIOUS instance data including title, group id, etc.
	 * @return	array					Instance data to save for this widget.
	 */
	function update($new_instance, $old_instance) {
		if (!isset($new_instance['submit'])) {
			return false;
		}

		function getYTid($ytURL) {
			// details in videoshowcase admin.php
			$ytvIDlen = 11;
			$idStarts = strpos($ytURL, "?v=");
			if($idStarts === FALSE){
				$idStarts = strpos($ytURL, "&v=");
			}
			if($idStarts === FALSE){
				die("YouTube video ID not found. Please double-check your URL.");
			}
			$idStarts +=3;
			$ytvID = substr($ytURL, $idStarts, $ytvIDlen);
			
			return $ytvID;	
		}

		
		// check video type
		if (preg_match('/youtube\.com\/watch/i',$new_instance['url'])) {
			$new_instance['type'] = 'yt';
			$YTid = getYTid($new_instance['url']);
			$new_instance['url'] = 'http://www.youtube.com/watch?v=' . $YTid;
		} elseif (preg_match('/vimeo\.com/i',$new_instance['url'])) {
			$new_instance['type'] = 'vem';
		} elseif (preg_match('/\.mov/i',$new_instance['url'])) {
			$new_instance['type'] = 'mov';
			// check for https
			$findme   = 'https:';
			$scheck = strpos($new_instance['url'], $findme);
			if ($scheck !== FALSE ) {
				$new_instance['url'] = str_replace("https:", "http:", $new_instance['url']);
			}
		} elseif ((preg_match('/\.mp4/i',$new_instance['url'])) || (preg_match('/\.flv/i',$new_instance['url']))) {
			$new_instance['type'] = 'op';
			// check for https
			$findme   = 'https:';
			$scheck = strpos($new_instance['url'], $findme);
			if ($scheck !== FALSE ) {
				$new_instance['url'] = str_replace("https:", "http:", $new_instance['url']);
			}
		} else {
			$new_instance['type'] = 'fail';
			$new_instance['url'] = '';
		}
		
		return $new_instance;
	}
	
	
	/**
	 * form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form( $instance ) {
		global $pluginbuddy_vidembed;
		$instance = array_merge( (array)$pluginbuddy_vidembed->_widgetdefaults, (array)$instance );
		$this->_parent->widget_form( $instance, $this );
	}
}

function widget_pluginbuddy_vidembed_init() {
	register_widget('widget_pluginbuddy_vidembed');
}
add_action('widgets_init', 'widget_pluginbuddy_vidembed_init' );

add_action( $pluginbuddy_vidembed->_var . '-widget', array( &$pluginbuddy_vidembed, 'widget' ) );
?>
