<?php
/**
 *
 * Plugin Name: VidEmbed
 * Plugin URI: http://pluginbuddy.com/purchase/vidembed/
 * Description: Easily embed videos in posts,pages, and widgets.
 * Version: 1.0.13
 * Author: The PluginBuddy Team
 * Author URI: http://pluginbuddy.com/
 *
 * Installation:
 * 
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 * 
 * Usage:
 * 
 * 1. Navigate to the new plugin menu in the Wordpress Administration Panel.
 *
 */


if (!class_exists("pluginbuddy_vidembed")) {
	class pluginbuddy_vidembed {
		var $_version = '1.0.13';
		var $_updater = '1.0.7';
		var $_var = 'pluginbuddy_vidembed';				// Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'VidEmbed';					// Pretty plugin name. Only used for display so any format is valid.
		var $_series = '';						// Series name if applicable.
		var $_url = 'http://pluginbuddy.com/purchase/vidembed/';	// Purchase URL.
		var $_timeformat = '%b %e, %Y, %l:%i%p';			// Mysql time format.
		var $_timestamp = 'M j, Y, g:iA';				// PHP timestamp format.
		var $_defaults = array(
			'players' =>	array(
				'youtube'	=>	array(
					'color1'	=>	'',
					'color2'	=>	'',
					'border'	=>	'0',
					'theme'		=>	'0',
					'related'	=>	'false'
				),
				'vimeo'		=>	array(
					'color'		=>	'00ADEF',
					'portrait'	=>	'hide',
					'title'		=>	'hide',
					'byline'	=>	'hide'
				),
				'op'		=>	array(
					'theme'		=>	'0395d3',
					'font'		=>	'cccccc',
					'frame'		=>	'333333'
				)
			)
		);
		
		var $_widget = 'Display embeded video.';
		var $_widgetdefaults = array(
			'title'		=>	'',
			'url'		=>	'',
			'vtitle'	=>	'',
			'caption'	=>	'',
			'width'		=>	'480',
			'height'	=>	'385',
			'auto'		=>	'',
			'type'		=>	''
		);
		
		// Default constructor. This is executed when the plugin first runs.
		function pluginbuddy_vidembed() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			if ( is_admin() ) { // Runs when in the admin dashboard.
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				require_once( $this->_pluginPath . '/classes/admin.php' );
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				register_activation_hook( $this->_pluginPath, array( &$this, 'activate' ) ); // Run some code when plugin is activated in dashboard.
			} else { // Runs when in non-dashboard parts of the site.
				add_shortcode( 'pb_vidembed', array( &$this, 'shortcode' ) );
				add_action( $this->_var . '-widget', array( &$this, 'widget' ), 10, 2 ); // Add action to run widget function.
			}
		}
		
		/**
		 *	alert()
		 *
		 *	Displays a message to the user at the top of the page when in the dashboard.
		 *
		 *	$message		string		Message you want to display to the user.
		 *	$error			boolean		OPTIONAL! true indicates this alert is an error and displays as red. Default: false
		 *	$error_code		int		OPTIONAL! Error code number to use in linking in the wiki for easy reference.
		 */
		function alert( $message, $error = false, $error_code = '' ) {
			$log_error = false;
			
			echo '<div id="message" class="';
			if ( $error == false ) {
				echo 'updated fade';
			} else {
				echo 'error';
				$log_error = true;
			}
			if ( $error_code != '' ) {
				$message .= '<p><a href="http://ithemes.com/codex/page/' . $this->_name . ':_Error_Codes#' . $error_code . '" target="_new"><i>' . $this->_name . ' Error Code ' . $error_code . ' - Click for more details.</i></a></p>';
				$log_error = true;
			}
			if ( $log_error === true ) {
				$this->log( $message . ' Error Code: ' . $error_code, 'error' );
			}
			echo '"><p><strong>'.$message.'</strong></p></div>';
		}
		
		
		/**
		 *	tip()
		 *
		 *	Displays a message to the user when they hover over the question mark. Gracefully falls back to normal tooltip.
		 *	HTML is supposed within tooltips.
		 *
		 *	$message		string		Actual message to show to user.
		 *	$title			string		Title of message to show to user. This is displayed at top of tip in bigger letters. Default is blank. (optional)
		 *	$echo_tip		boolean		Whether to echo the tip (default; true), or return the tip (false). (optional)
		 */
		function tip( $message, $title = '', $echo_tip = true ) {
			$tip = ' <a class="pluginbuddy_tip" title="' . $title . ' - ' . $message . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_tip.png" alt="(?)" /></a>';
			if ( $echo_tip === true ) {
				echo $tip;
			} else {
				return $tip;
			}
		}
		
		
		/**
		 *	log()
		 *
		 *	Logs to a text file depending on settings.
		 *	0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
		 *
		 *	$text	string			Text to log.
		 *	$log_type	string		Valid options: error, warning, all (default so may be omitted).
		 *
		 */
		function log( $text, $log_type = 'all' ) {
			$write = false;
			
			if ( !isset( $this->_options['log_level'] ) ) {
				$this->load();
			}
			
			if ( $this->_options['log_level'] == 0 ) { // No logging.
				return;
			} elseif ( $this->_options['log_level'] == 1 ) { // Errors only.
				if ( $log_type == 'error' ) {
					$write = true;
				}
			} elseif ( $this->_options['log_level'] == 2 ) { // Errors and warnings only.
				if ( ( $log_type == 'error' ) || ( $log_type == 'warning' ) ) {
					$write = true;
				}
			} elseif ( $this->_options['log_level'] == 3 ) { // Log all; Errors, warnings, actions, notes, etc.
				$write = true;
			}
			
			if ( $write === true ) {
				$fh = fopen( WP_CONTENT_DIR . '/uploads/' . $this->_var . '.txt', 'a');
				fwrite( $fh, '[' . date( $this->_timestamp . ' ' . get_option( 'gmt_offset' ), time() + (get_option( 'gmt_offset' )*3600) ) . '-' . $log_type . '] ' . $text . "\n" );
				fclose( $fh );
			}
		}
		
		
		/**
		 * activate()
		 *
		 * Run on plugin activation. Useful for setting up initial stuff.
		 *
		 */
		function activate() {
		}
		
		
		// OPTIONS STORAGE //////////////////////
		
		
		function save() {
			add_option($this->_var, $this->_options, '', 'no'); // 'No' prevents autoload if we wont always need the data loaded.
			update_option($this->_var, $this->_options);
			return true;
		}
		
		
		function load() {
			$this->_options=get_option($this->_var);
			$options = array_merge( $this->_defaults, (array)$this->_options );
			
			if ( $options !== $this->_options ) {
				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$this->_options = $options;
				$this->save();
			}
			
			return true;
		}
		
		
		function shortcode($atts) {
			//shortcode atts
			$ve_title = $atts['title'];
			$ve_caption = $atts['caption'];
			$ve_url = $atts['url'];
			$ve_type = $atts['type'];
			$ve_width = $atts['w'];
			$ve_height = $atts['h'];
			if(!isset($atts['autoplay'])) {
				$ve_auto = 'false';
			}
			else {
				$ve_auto = 'true';
			}
			
			return $this->ve_embed($ve_title,$ve_caption,$ve_url,$ve_type,$ve_width,$ve_height,$ve_auto);
		}
		
		
		/**
		 * widget()
		 *
		 * Function is called when a widget is to be displayed. Use echo to display to page.
		 *
		 * $instance	array		Associative array containing the options saved on the widget form.
		 * @return	none
		 *
		 */
		function widget( $instance ) {
			$ve_title = $instance['vtitle'];
			$ve_caption = $instance['caption'];
			$ve_url = $instance['url'];
			$ve_type = $instance['type'];
			$ve_width = $instance['width'];
			$ve_height = $instance['height'];
			if(!isset($instance['auto'])) {
				$ve_auto = 'false';
			}
			else {
				$ve_auto = 'true';
			}
			
			echo $this->ve_embed($ve_title,$ve_caption,$ve_url,$ve_type,$ve_width,$ve_height,$ve_auto);
		}
		
		// Embed video
		function ve_embed($ve_title,$ve_caption,$ve_url,$ve_type,$ve_width,$ve_height,$ve_auto) {
			$this->load();
			
			if ( ($ve_type == 'op') && (!wp_script_is( $this->_var . '_op_script' )) ) {
				wp_enqueue_script( $this->_var . '_op_script', $this->_pluginURL . "/js/swfobject.js");
				wp_print_scripts( $this->_var . '_op_script' );
			}
			
			// increment videos
			$this->_instance++;

			$return = '';
			$return .= '<div id="pb-vidembed-c' . $this->_instance . '" class="pb-vidembed-container">';
			if ($ve_title !== '') {
				$return .= '<h4>' . $ve_title . '</h4>';
			}
			// check video type
			if ($ve_type == 'yt') {
				// change url to embed url
				$ve_url = str_replace("watch?v=", "embed/", $ve_url);
				
				// define database path
				$ytplayer = $this->_options['players']['youtube'];
				// check autoplay
				if ($ve_auto == 'true') {
					$ve_auto = '&autoplay=1';
				} else {
					$ve_auto = '';
				}
				// check if colors are custom
				/*$color1 = '';
				if($ytplayer['color1'] !== '') {
					$color1 = '&color1=0x' . $ytplayer['color1'];
				}
				$color2 = '';
				if($ytplayer['color2'] !== '') {
					$color2 = '&color2=0x' . $ytplayer['color2'];
				}
				// check border
				$ytborder = '';
				if($ytplayer['border'] !== '0') {
					$ytborder = '&border=1';
				}*/
				//check theme
				$yttheme = '';
				if($ytplayer['theme'] !== '0') {
					$yttheme = '&theme=light';
				}
				// check related
				$related = 'rel=1';
				if($ytplayer['related'] !== 'false') {
					$related = 'rel=0';
				}
				
				$yt_url = $ve_url . '?' . $related . ''  . $yttheme . '' . '&fs=1' . $ve_auto . '&amp;wmode=Opaque'; // wmode prevents overlay issues.
				$return .= '<iframe width="' . $ve_width . '" height="' . $ve_height . '" src="' . $yt_url . '" frameborder="0" allowfullscreen></iframe>';
				
				/* TODO: Removed v1.0.10.
				$return .= '<object width="' . $ve_width . '" height="' . $ve_height . '">';
				$return .=	'<param name="movie" value="' . $yt_url . '"></param>';
				$return .=	'<param name="allowFullScreen" value="true"></param>';
				$return .=	'<param name="allowscriptaccess" value="always"></param>';
				$return .=	'<param name="wmode" value="opaque"></param>';
				$return .=	'<embed src="' . $ve_url . '?' . $related . '' . $color1 . '' . $color2 . '' . $ytborder . '&fs=1' . $ve_auto . '&amp;hl=en_US" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $ve_width . '" height="' . $ve_height . '" wmode="opaque"></embed>';
				$return .= '</object>';
				*/
			} elseif ($ve_type == 'vem') {
				// define database path
				$vimplayer = $this->_options['players']['vimeo'];
				// check autoplay
				if ($ve_auto == 'true') {
					$ve_auto = '1';
				} else {
					$ve_auto = '0';
				}
				$path = parse_url($ve_url, PHP_URL_PATH);
				$VEMid = str_replace( '/', '', $path);
				
				/* TODO: Removed v1.1.10.
				$ve_url = 'http://vimeo.com/moogaloop.swf?clip_id=' . $VEMid . '&amp;server=vimeo.com&amp;show_title=' . $vimplayer['title'] . '&amp;show_byline=' . $vimplayer['byline'] . '&amp;show_portrait=' . $vimplayer['portrait'] . '&amp;color=' . $vimplayer['color'] . '&amp;fullscreen=1&amp;autoplay=' . $ve_auto . '&amp;loop=0';
				$return .= '<embed src="' . $ve_url . '" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="' . $ve_width . '" height="' . $ve_height . '" wmode="opaque"></embed>';
				*/
				
				$return .= '<iframe src="http://player.vimeo.com/video/' . $VEMid . '?title=' . $vimplayer['title'] . '&amp;byline=' . $vimplayer['byline']. '&amp;portrait=' . $vimplayer['portrait'] . '&amp;color=' . $vimplayer['color'] . '&amp;fullscreen=1&amp;autoplay=' . $ve_auto . '&amp;loop=0" width="400" height="225" frameborder="0" allowfullscreen="true"></iframe>';
				
			} elseif ($ve_type == 'mov') {
				$return .= '<object width="' . $ve_width . '" height="' . $ve_height . '" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab">';
				$return .= 	'<param name="src" value="' . $ve_url . '">';
				$return .= 	'<param name="autoplay" value="' . $ve_auto . '">';
				$return .= 	'<param name="controller" value="true">';
				$return .=	'<param name="scale" value="tofit">';
				$return .= '<embed src="' . $ve_url . '" width="' . $ve_width . '" height="' . $ve_height . '" autoplay="' . $ve_auto . '" controller="true" scale="tofit" pluginspage="http://www.apple.com/quicktime/download/"></embed>';
				$return .= '</object>';
			} else {
				$opplayer = $this->_options['players']['op'];
				$return .= '<script type="text/javascript">
						var flashvars = {
							src: "' . $ve_url . '",
							autostart: "' . $ve_auto . '",
							themeColor: "' . $opplayer['theme'] . '",
							mode: "sidebyside",
							scaleMode: "fit",
							frameColor: "' . $opplayer['frame'] . '",
							fontColor: "' . $opplayer['font'] . '",
							link: "",embed: ""
						};
						var params = {allowFullScreen: "true"};
						var attributes = {id: "veop-' . $this->_instance . '",name: "veop-' . $this->_instance . '"};
						swfobject.embedSWF("' . $this->_pluginURL . '/js/AkamaiFlashPlayer.swf","veop-here-' . $this->_instance . '","' . $ve_width . '","' . $ve_height . '","9.0.0","' . $this->_pluginURL . '/js/expressInstall.swf",flashvars,params,attributes);
					</script>
					<div id="veop-here-' . $this->_instance . '">
						<a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>
					</div>';
			}
			if ( $ve_caption !== '' ) {
				$return .= '<p>' . $ve_caption . '</p>';
			}
			$return .= '</div>';
			return $return;
		}
		
		
		/**
		 * widget_form()
		 *
		 * Displays the widget form on the widget selection page for setting widget settings.
		 * Widget defaults are pre-merged into $instance right before this function is called.
		 * Use $widget->get_field_id() and $widget->get_field_name() to get field IDs and names for form elements.
		 *
		 * $instance	array		Associative array containing the options set previously in this form and/or the widget defaults (merged already).
		 * &$widget	object		Reference to the widget object/class that handles parsing data. Use $widget->get_field_id(), $widget->get_field_name(), etc.
		 * @return	none
		 *
		 */
		function widget_form( $instance, &$widget ) {
			if($instance['type'] == 'fail') {
				echo '<p style="background-color: #FFeBE8; border: 1px solid #CC0000" >Please use a youtube, vimeo, mov, flv, or mp4 video.</p>';
			}
			?>
			<p>Find a video at <a href="http://www.youtube.com/" target="_blank">Youtube</a> and
			 <a href="http://vimeo.com/" target="_blank">Vimeo</a> or add a stand alone.</p>
			<p>
				<label for="<?php echo $widget->get_field_id('title'); ?>">
					Widget Title (optional):
				</label>
				<input class="widefat" id="<?php echo $widget->get_field_id('title'); ?>" name="<?php echo $widget->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			</p>
			<p>
				<label for="<?php echo $widget->get_field_id('url'); ?>">
					Video Url:
				</label>
				<input class="widefat" id="<?php echo $widget->get_field_id('url'); ?>" name="<?php echo $widget->get_field_name('url'); ?>" type="text" value="<?php echo $instance['url']; ?>" />
				<span style="color: #AFAFAF;">Ex: http://www.mysite.com/video.flv</span>
			</p>
			<p>
				<label for="<?php echo $widget->get_field_id('vtitle'); ?>">
					Video Title (optional):
				</label>
				<input class="widefat" id="<?php echo $widget->get_field_id('vtitle'); ?>" name="<?php echo $widget->get_field_name('vtitle'); ?>" type="text" value="<?php echo $instance['vtitle']; ?>" />
			</p>
			<p>
				<label for="<?php echo $widget->get_field_id('caption'); ?>">
					Video Caption (optional):
				</label>
				<input class="widefat" id="<?php echo $widget->get_field_id('caption'); ?>" name="<?php echo $widget->get_field_name('caption'); ?>" type="text" value="<?php echo $instance['caption']; ?>" />
			</p>
			<p>
				<label for="<?php echo $widget->get_field_id('width'); ?>">
					Video Width:
				</label>
				<input id="<?php echo $widget->get_field_id('width'); ?>" size="6" maxlength="18" name="<?php echo $widget->get_field_name('width'); ?>" type="text" style="text-align: right" value="<?php echo $instance['width']; ?>" />px<br/>			
				
				<label for="<?php echo $widget->get_field_id('height'); ?>">
					Video Height:
				</label>
				<input id="<?php echo $widget->get_field_id('height'); ?>" size="6" maxlength="18" name="<?php echo $widget->get_field_name('height'); ?>" type="text" style="text-align: right" value="<?php echo $instance['height']; ?>" />px<br/>
			</p>
			<p>
				<label for="<?php echo $widget->get_field_id('auto'); ?>">
					Autoplay:
				</label>
				<input type="checkbox" id="<?php echo $widget->get_field_id('auto'); ?>" name="<?php echo $widget->get_field_name('auto'); ?>" <?php if ($instance['auto'] == 'on') { echo " checked "; } ?>/>
			</p>

			<input type="hidden" id="<?php echo $widget->get_field_id('type'); ?>" name="<?php echo $widget->get_field_name('type'); ?>" value="<?php echo $instance['type']; ?>" />
			<input type="hidden" id="<?php echo $widget->get_field_id('submit'); ?>" name="<?php echo $widget->get_field_name('submit'); ?>" value="1" />
			<?php
			
		}
		//Register the updater version
		function upgrader_register() {
			$GLOBALS['pb_classes_upgrade_registration_list'][$this->_var] = $this->_updater;
		} //end register_upgrader
		//Select the greatest version
		function upgrader_select() {
			if ( !isset( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] ) ) {
				//Fallback - Just include this class
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				return;
			}
			//Go through each global and find the highest updater version and the plugin slug
			$updater_version = 0;
			$plugin_var = '';
			foreach ( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] as $var => $version) {
				if ( version_compare( $version, $updater_version, '>=' ) ) {
					$updater_version = $version;
					$plugin_var = $var;
				}
			}
			//If the slugs match, load this version
			if ( $this->_var == $plugin_var ) {
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
			}
		} //end upgrader_select
		function upgrader_instantiate() {
			
			$pb_product = strtolower( $this->_var );
			$pb_product = str_replace( 'ithemes-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy_', '', $pb_product );
			$pb_product = str_replace( 'pb_thumbsup', '', $pb_product );
			
			$args = array(
				'parent' => $this, 
				'remote_url' => 'http://updater2.ithemes.com/index.php',
				'version' => $this->_version,
				'plugin_slug' => $this->_var,
				'plugin_path' => plugin_basename( __FILE__ ),
				'plugin_url' => $this->_pluginURL,
				'product' => $pb_product,
				'time' => 43200,
				'return_format' => 'json',
				'method' => 'POST',
				'upgrade_action' => 'check' );
			$this->_pluginbuddy_upgrader = new iThemesPluginUpgrade( $args );

		} //end upgrader_instantiate
	} // End class
	
	$pluginbuddy_vidembed = new pluginbuddy_vidembed();
	require_once( dirname( __FILE__ ) . '/classes/widget.php');
}



?>
