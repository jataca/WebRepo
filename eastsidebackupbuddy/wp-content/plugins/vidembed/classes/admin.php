<?php
if ( !class_exists( "pluginbuddy_vidembed_admin" ) ) {
	class pluginbuddy_vidembed_admin {
		
		function pluginbuddy_vidembed_admin(&$parent) {
			$this->_parent = &$parent;
			$this->_var = &$parent->_var;
			$this->_name = &$parent->_name;
			$this->_options = &$parent->_options;
			$this->_pluginPath = &$parent->_pluginPath;
			$this->_pluginURL = &$parent->_pluginURL;
			$this->_selfLink = &$parent->_selfLink;
			
			add_action('admin_menu', array(&$this, 'admin_menu')); // Add menu in admin.

			// Button on post page related stuff...
			add_action( 'media_buttons_context', array( &$this, 'add_post_button' ) );
			if( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post.php', 'page.php', 'page-new.php', 'post-new.php' ) ) ) {
				add_action('admin_footer',  array( &$this, 'add_post_popup'));
			}
		}
		
		// Button on post page related stuff ... functions
		function add_post_button( $content ){
			return $content . '<a href="post.php#TB_inline?inlineId=vidembed_form" class="thickbox" title="VidEmbed"><img src="' . $this->_pluginURL . '/images/vidembed-short.png" alt="VidEmbed" /></a>';
		}
		function add_post_popup(){
			?>
			<script>
				function ve_add_post() {
					// video title
					var ve_title = jQuery( '#ve_title' ).val();
					// video caption
					var ve_caption = jQuery( '#ve_caption' ).val();
					// video url
					var ve_url = jQuery( '#ve_url' ).val();
					if ( ve_url == '' ){
						alert( 'Please enter the video url.' );
						return;
					}
					
					// check video type
					if (ve_url.match(/youtube\.com\/watch/i)) {
						var extn = 'yt';
						var ve_url = 'http://www.youtube.com/watch?v='+grab_param('v',ve_url);
					}else if (ve_url.match(/vimeo\.com/i)) {
						var extn = 'vem';
						var regExp = /http:\/\/(www\.)?vimeo.com\/(\d+)/;
						var match = ve_url.match(regExp);
						var ve_url = 'http://vimeo.com/'+ match[2];
					}else if(ve_url.indexOf('.mov') != -1){ 
						var extn = 'mov';
						// check for https
						if (ve_url.match(/https:/i)) {
							var ve_url = (ve_url.replace(/https:/i, "http:"));
						}
					}else if((ve_url.indexOf('mp4') != -1) || (ve_url.indexOf('flv') != -1) || (ve_url.indexOf('3gp') != -1)){
						var extn = 'op';
						// check for https
						if (ve_url.match(/https:/i)) {
							var ve_url = (ve_url.replace(/https:/i, "http:"));
						}
					} else {
						alert('Only youtube, vimeo, mov,flv, and mp4 videos can be added.');
						return;
					}
					
					// filter youtube url
					function grab_param(name,url){
					  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
					  var regexS = "[\\?&]"+name+"=([^&#]*)";
					  var regex = new RegExp( regexS );
					  var results = regex.exec( url );
					  return ( results == null ) ? "" : results[1];
					}
					
					// validate width
					var ve_width = jQuery('#ve_width').val();
					var wcheck = isNaN(ve_width);
					if (ve_width == '') {
						alert('Please enter the video width.');
						return;
					} else if (wcheck == true) {
						alert('Please enter a number for video width.');
						return;
					}
					// validate height
					var ve_height = jQuery('#ve_height').val();
					var hcheck = isNaN(ve_height);
					if (ve_height == '') {
						alert('Please enter the video height.');
						return;
					} else if (hcheck == true) {
						alert('Please enter a number for video height.');
						return;
					}
					
					// autoplay
					if( jQuery( '#ve_auto' ).attr('checked') ){
						ve_auto = ' autoplay="true"';
					} else {
						ve_auto = '';
					}
						
					
					var win = window.dialogArguments || opener || parent || top;
					win.send_to_editor( '[pb_vidembed title="' + ve_title + '" caption="' + ve_caption + '" url="' + ve_url + '" type="' + extn + '" w="' + ve_width + '" h="' + ve_height + '"' + ve_auto + ']' );
				}
			</script>

			<div id="vidembed_form" style="display:none;">
				<div class="wrap">
					<style type="text/css">
						optgroup option {
							margin-left: 10px;
						}
						.widefat {
							style="width: 100%; padding: 4px;"
						}
						input.vepixels {
							text-align: right;
						}
							
					</style>
					<p>Find a videos at <a href="http://www.youtube.com/" target="_blank">Youtube</a> and
					 <a href="http://vimeo.com/" target="_blank">Vimeo</a> or add the url to a stand alone
					video(Ex: mp4, flv, or mov).</p>
					<table style="width: 100%; margin-top: 18px;">
						<tr>
							<td><label for="ve_url">Video URL</label></td>
							<td><input type="text" class="widefat" id="ve_url" name="ve_url" value="" /></td>
						</tr>
						<tr>
							<td></td>
							<td><span style="color: #AFAFAF;">Ex: http://www.mysite.com/video.flv</span></td>
						</tr>
						<tr>
							<td><label for="ve_title">Video Title</label></td>
							<td><input type="text" class="widefat" id="ve_title" name="ve_title" value="" /></td>
						</tr>
						<tr>
							<td><label for="ve_caption">Video Caption</label></td>
							<td><input type="text" class="widefat" id="ve_caption" name="ve_caption" value="" /></td>
						</tr>
						<tr>
							<td><label for="ve_width">Video Width </label></td>
							<td><input type="text" name="ve_width" id="ve_width" size="6" maxlength="18" value="480" class="vepixels" />px</td>
						</tr>
						<tr>
							<td><label for="ve_height">Video Height </label></td>
							<td><input type="text" name="ve_height" id="ve_height" size="6" maxlength="18" value="385" class="vepixels" />px</td>
						</tr>
						<tr>
							<td>
								<label for="ve_auto">Autoplay</label>
							</td>
							<td>
								<label for="ve_auto"><input type="checkbox" name="ve_auto" id="ve_auto" /></label>
							</td>
						</tr>
					</table>
					
					<p>
						<input type="button" class="button-primary" value="Insert video" onclick="ve_add_post();"/>
						<a class="button" href="#" onclick="tb_remove(); return false;">Cancel</a>
					</p>
				</div>
			</div>
			
			<?php
		}
		
		
		function alert( $arg1, $arg2 = false ) {
			$this->_parent->alert( $arg1, $arg2 );
		}
		
		
		function tip( $message, $title = '', $echo_tip = true ) {
			if ( $echo_tip === true ) {
				$this->_parent->tip( $message, $title = '', $echo_tip );
			} else {
				return $this->_parent->tip( $title = '', $message, $echo_tip );
			}
		}
		
		
		function nonce() {
			wp_nonce_field( $this->_parent->_var . '-nonce' );
		}
		
		
		function savesettings() {
			check_admin_referer( $this->_parent->_var . '-nonce' );
			
			foreach( $_POST as $index => $item ) {
				if ( substr( $index, 0, 1 ) == '#' ) {
					$savepoint = '$this->_options' . stripslashes( $_POST['savepoint'] );
					eval( $savepoint . '[\'' . substr( $index, 1 ) . '\'] = \'' . $item . '\';' );
				}
			}
			
			$this->_parent->save();
			$this->alert( 'Settings saved...' );
		}
		
		// save YouTube settings
		function _ytsave() {
			if ( ( empty($_POST[$this->_var . '-ytcolor1']) ) ) {
				$_POST[$this->_var . '-ytcolor1'] = '';
			}
			if ( ( empty($_POST[$this->_var . '-ytcolor2']) ) ) {
				$_POST[$this->_var . '-ytcolor2'] = '';
			}
			if (!isset($_POST[$this->_var . '-ytborder'])) {
				$_POST[$this->_var . '-ytborder'] = '0';
			}
			if (!isset($_POST[$this->_var . '-yttheme'])) {
				$_POST[$this->_var . '-yttheme'] = '0';
			}
			
			// save to database
			$this->_options['players']['youtube']['color1'] = $_POST[$this->_var . '-ytcolor1'];
			$this->_options['players']['youtube']['color2'] = $_POST[$this->_var . '-ytcolor2'];
			$this->_options['players']['youtube']['border'] = $_POST[$this->_var . '-ytborder'];
			$this->_options['players']['youtube']['related'] = $_POST[$this->_var . '-relate'];
			$this->_options['players']['youtube']['theme'] = $_POST[$this->_var . '-yttheme'];
			
			$this->_parent->save();
			$this->alert( 'YouTube player settings saved.' );
		}
		// save Vimeo settings
		function _vimsave() {

			if (empty($_POST[$this->_var . '-vimcolor'])) {
				$_POST[$this->_var . '-vimcolor'] = '00ADEF';
			}
			
			$vimops = array('portrait','title','byline');
			foreach( $vimops as $vimbox ) {
				if(!isset($_POST[$this->_var . '-vim' . $vimbox])) {
					$_POST[$this->_var . '-vim' . $vimbox] = '0';
				}
			}
			
			// save to database
			$this->_options['players']['vimeo']['color'] = $_POST[$this->_var . '-vimcolor'];
			foreach($vimops as $vimval) {
				$this->_options['players']['vimeo'][$vimval] = $_POST[$this->_var . '-vim' . $vimval];
			}
			
			$this->_parent->save();
			$this->alert( 'Vimeo player settings saved.' );
		}
		// save open source player settings
		function _opsave() {
			if ( ( empty($_POST[$this->_var . '-optheme']) ) ) {
				$_POST[$this->_var . '-optheme'] = '0395d3';
			}
			if ( ( empty($_POST[$this->_var . '-opfont']) ) ) {
				$_POST[$this->_var . '-opfont'] = 'cccccc';
			}
			if ( ( empty($_POST[$this->_var . '-opframe']) ) ) {
				$_POST[$this->_var . '-opframe'] = '333333';
			}
			
			// save to database
			$this->_options['players']['op']['theme'] = $_POST[$this->_var . '-optheme'];
			$this->_options['players']['op']['font'] = $_POST[$this->_var . '-opfont'];
			$this->_options['players']['op']['frame'] = $_POST[$this->_var . '-opframe'];
			
			$this->_parent->save();
			$this->alert( 'Open source player settings saved.' );
		}
		
		
		function admin_scripts() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'pluginbuddy-tooltip-js', $this->_parent->_pluginURL . '/js/tooltip.js' );
			wp_print_scripts( 'pluginbuddy-tooltip-js' );
			wp_enqueue_script( 'pluginbuddy-'.$this->_var.'-admin-js', $this->_parent->_pluginURL . '/js/admin.js' );
			wp_print_scripts( 'pluginbuddy-'.$this->_var.'-admin-js' );
			wp_enqueue_script('jpicker-js', $this->_pluginURL . '/js/jpicker.js' );
			wp_print_scripts('jpicker-js');
			echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
			echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/css/jpicker.css" type="text/css" media="all" />';
		}
		
		
		/**
		 *	get_feed()
		 *
		 *	Gets an RSS or other feed and inserts it as a list of links...
		 *
		 *	$feed		string		URL to the feed.
		 *	$limit		integer		Number of items to retrieve.
		 *	$append		string		HTML to include in the list. Should usually be <li> items including the <li> code.
		 *	$replace	string		String to replace in every title returned. ie twitter includes your own username at the beginning of each line.
		 *	$cache_time	int			Amount of time to cache the feed, in seconds.
		 */
		function get_feed( $feed, $limit, $append = '', $replace = '', $cache_time = 300 ) {
			require_once(ABSPATH.WPINC.'/feed.php');  
			$rss = fetch_feed( $feed );
			if (!is_wp_error( $rss ) ) {
				$maxitems = $rss->get_item_quantity( $limit ); // Limit 
				$rss_items = $rss->get_items(0, $maxitems); 
				
				echo '<ul class="pluginbuddy-nodecor">';

				$feed_html = get_transient( md5( $feed ) );
				if ( $feed_html == '' ) {
					foreach ( (array) $rss_items as $item ) {
						$feed_html .= '<li>- <a href="' . $item->get_permalink() . '">';
						$title =  $item->get_title(); //, ENT_NOQUOTES, 'UTF-8');
						if ( $replace != '' ) {
							$title = str_replace( $replace, '', $title );
						}
						if ( strlen( $title ) < 30 ) {
							$feed_html .= $title;
						} else {
							$feed_html .= substr( $title, 0, 32 ) . ' ...';
						}
						$feed_html .= '</a></li>';
					}
					set_transient( md5( $feed ), $feed_html, $cache_time ); // expires in 300secs aka 5min
				}
				echo $feed_html;
				
				echo $append;
				echo '</ul>';
			} else {
				echo 'Temporarily unable to load feed...';
			}
		}
		
		
		function view_gettingstarted() {
			require( 'view_gettingstarted.php' );
		}
		
		
		function view_settings() {
			require( 'view_settings.php' );
		}
		
		
		/** admin_menu()
		 *
		 * Initialize menu for admin section.
		 *
		 */		
		function admin_menu() {
			if ( isset( $this->_parent->_series ) && ( $this->_parent->_series != '' ) ) {
				// Handle series menu. Create series menu if it does not exist.
				global $menu;
				$found_series = false;
				foreach ( $menu as $menus => $item ) {
					if ( $item[0] == $this->_parent->_series ) {
						$found_series = true;
					}
				}
				if ( $found_series === false ) {
					add_menu_page( $this->_parent->_series . ' Getting Started', $this->_parent->_series, 'administrator', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png' );
					add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name.' Getting Started', 'Getting Started', 'administrator', 'pluginbuddy-' . strtolower( $this->_parent->_series ), array(&$this, 'view_gettingstarted') );
				}
				// Register for getting started page
				global $pluginbuddy_series;
				if ( !isset( $pluginbuddy_series[ $this->_parent->_series ] ) ) {
					$pluginbuddy_series[ $this->_parent->_series ] = array();
				}
				$pluginbuddy_series[ $this->_parent->_series ][ $this->_parent->_name ] = $this->_pluginPath;
				
				add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name, $this->_parent->_name, 'administrator', $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			} else { // NOT IN A SERIES!
				// Add main menu (default when clicking top of menu)
				add_menu_page($this->_parent->_name.' Getting Started', $this->_parent->_name, 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'), $this->_parent->_pluginURL.'/images/pluginbuddy.png');
				// Add sub-menu items (first should match default page above)
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Getting Started', 'Getting Started', 'administrator', $this->_parent->_var, array(&$this, 'view_gettingstarted'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name.' Settings', 'Settings', 'administrator', $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			}
		}
		
	} // End class
	
	$pluginbuddy_vidembed_admin = new pluginbuddy_vidembed_admin($this);
}
