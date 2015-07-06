<?php
/*
 * Plugin Name: WP Font Resizer widget - button A+ A  A-
 * Version: 1.1
 * Plugin URI: http://newplugins.us/wp-font-resizer-widget/
 * Description: With Auto Font Resizer you can enable your Wordpress blog/website with auto generated famous implementation of buttons A+ and A-, which alter the font size on your sites with very large texts or make it smaller. This plugin can be used to increase the accessibility of sites, helping people who have visual problems to see content better. It makes use of JQuery plugin by Fred Vanelli.
 * Author: Vaske
 * Author URI: http://newplugins.us
 * License: GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
class auto_font_resizer extends WP_Widget
{

	function auto_font_resizer(){
		$widget_ops = array('classname' => 'widget_auto_font_resizer', 'description' => __( "Alter the font size on your sites with very large texts or make it smaller. Helping people who have visual problems to see content better.") );
		$control_ops = array('width' => 320, 'height' => 300);
		$this->WP_Widget('auto_font_resizer', __('WP Font Resizer'), $widget_ops, $control_ops);
	}


	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? 'Font Resizer' : $instance['title']);

		$applyToClassGroup = empty($instance['applyToClassGroup']) ? 'body' : $instance['applyToClassGroup'];
		$buttonType = empty($instance['buttonType']) ? 'image' : $instance['buttonType'];
		$creditYes = empty($instance['creditYes']) ? 'no' : $instance['creditYes'];



		# Before the widget
		echo $before_widget;

		# The title
		if ( $title )
			echo $before_title . $title . $after_title;

		$img_live_dir = 'http://vivociti.com/images/plus2x2.gif';
		$html = "<a href=\"http://newplugins.us\" title=\"Get Font Resizer Plugin From VivoCiti.com\" target=\"_blank\"><img src=\"$img_live_dir\" border=\"0\"/></a>";
		$html2 = "";
		$siteurl = get_option('siteurl');
		$img_url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/';
		switch ($buttonType) {
			case 'image' :
				$html2 .= '<a class="jfontsize-button" id="jfontsize-m" href="#"><img src="' . $img_url . 'small.jpg" alt="Decrease font size" /></a>';
				$html2 .= '<a class="jfontsize-button" id="jfontsize-d" href="#"><img src="' . $img_url . 'normal.jpg" alt="Default font size" /></a>';
				$html2 .= '<a class="jfontsize-button" id="jfontsize-p" href="#"><img src="' . $img_url . 'big.jpg" alt="Increase font size" /></a>';
				break;
			case 'text' :
				$html2 .= '<a class="jfontsize-button" id="jfontsize-m" href="#">-</a>';
				$html2 .= '<a class="jfontsize-button" id="jfontsize-d" href="#">A</a>';
				$html2 .= '<a class="jfontsize-button" id="jfontsize-p" href="#">+</a>';
				break;
		}

	if ($creditYes == "no") {
            $html2 .= $html;
        }
	echo $html2;
	?>
	<script type="text/javascript" language="javascript">
	$('<?php echo $applyToClassGroup;?>').jfontsize({
		btnMinusClasseId: '#jfontsize-m',
		btnDefaultClasseId: '#jfontsize-d',
		btnPlusClasseId: '#jfontsize-p'
	});
	</script>
<?php
	//end of creditOn is yes

		# After the widget
		echo $after_widget;
	}

	/**
	* Saves the widgets settings.
	*
	*/
	function update($new_instance, $old_instance){

		$applyToClassGroup = empty($instance['applyToClassGroup']) ? 'body' : $instance['applyToClassGroup'];
		$buttonType = empty($instance['buttonType']) ? 'image' : $instance['buttonType'];
		$creditYes = empty($instance['creditYes']) ? 'no' : $instance['creditYes'];


		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['applyToClassGroup'] = strip_tags(stripslashes($new_instance['applyToClassGroup']));
		$instance['buttonType'] = strip_tags(stripslashes($new_instance['buttonType']));
		$instance['creditYes'] = strip_tags(stripslashes($new_instance['creditYes']));

		$instance['applyToClassGroup'] = strip_tags(stripslashes($new_instance['applyToClassGroup']));
		$instance['buttonType'] = strip_tags(stripslashes($new_instance['buttonType']));
		$instance['creditYes'] = strip_tags(stripslashes($new_instance['creditYes']));

	return $instance;
	}

	/**
	* Creates the edit form for the widget.
	*
	*/
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=>'Font Resizer', 'applyToClassGroup'=>'body', 'buttonType'=>'image', 'creditYes'=>'yes') );


		$title = htmlspecialchars($instance['title']);
		$applyToClassGroup = empty($instance['applyToClassGroup']) ? 'body' : $instance['applyToClassGroup'];
		$buttonType = empty($instance['buttonType']) ? 'image' : $instance['buttonType'];
		$creditYes = empty($instance['creditYes']) ? 'no' : $instance['creditYes'];

		# Output the options
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('title') . '">' . __('Title:') . ' <input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
		# Fill Button Style Selection
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('buttonType') . '">' . __('Button Style:') . ' <select name="' . $this->get_field_name('buttonType')  . '" id="' . $this->get_field_id('buttonType')  . '">"';
?>
		<option value="image" <?php if ($pluginDisplayType == 'image') echo 'selected="yes"'; ?> >Image</option>
		<option value="text" <?php if ($pluginDisplayType == 'text') echo 'selected="yes"'; ?> >Text</option>
<?php
		echo '</select></label>';
		echo '<p style="text-align:right;"><label for="' . $this->get_field_name('applyToClassGroup') . '">' . __('Class/ID:') . ' <input style="width: 150px;" id="' . $this->get_field_id('applyToClassGroup') . '" name="' . $this->get_field_name('applyToClassGroup') . '" type="text" value="' . $applyToClassGroup . '" /></label></p>';
		
?>

<?php
		echo '</select></label>';
		echo '<p/>';



	} //end of form

}// END class


	function auto_font_resizerInit() {

		wp_enqueue_style('my-style', '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/jf.css');
		wp_enqueue_script('my-fontresizer', '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/jq.js');
		wp_enqueue_script('my-fontresizer2', '/wp-content/plugins/' . basename(dirname(__FILE__))  . '/jq1.js');
		register_widget('auto_font_resizer');
	}


	add_action('widgets_init', 'auto_font_resizerInit');


?>