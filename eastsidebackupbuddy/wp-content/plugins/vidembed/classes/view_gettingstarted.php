<?php
// Needed for fancy boxes...
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');
// Load scripts and CSS used on this page.
$this->admin_scripts();

// If they clicked the button to reset plugin defaults...
if (!empty($_POST['reset_defaults'])) {
	$this->_options = $this->_parent->_defaults;
	$this->_parent->save();
	$this->_showStatusMessage( 'Plugin settings have been reset to defaults.' );
}
?>

<div class="wrap">
	<div class="postbox-container" style="width:70%;">
		<h2>Getting Started with <?php echo $this->_parent->_name; ?> v<?php echo $this->_parent->_version; ?></h2>
		
		<p>
			This plugin adds the ability to easily embed videos in your posts, pages, and widgets on your site.
			The different video sites supported are youtube and vimeo. This plugin can also embed any stand alone mp4, flv, or mov files.
		</p>
		<h3>Instructions</h3>
		<h4>Add to posts or pages</h4>
		<ol>
			<li>Start adding/editing a post or page in the wp-admin area.</li>
			<li>Click the <?php echo $this->_parent->_name; ?> shortcode button <img src="<?php echo $this->_pluginURL; ?>/images/vidembed-short.png" alt="VidEmbed video" /> at the top of the wysiwyg editor.</li>
			<li>After clicking the shortcode button a form will appear.</li>
			<li>Enter the required information for the video you would like to embed.</li>
			<li>Click the insert video button.</li>
		</ol>
		<h4>Add to a widget area</h4>
		<ol>
			<li>Navigate to the widgets area in the wp-admin area.</li>
			<li>Drag the <?php echo $this->_parent->_name; ?> widget into the widget area where you would like it to display.</li>
			<li>Enter the required information for the video you would like to add.</li>
			<li>Save your settings.</li>
		</ol>
		
		<h4>Customize players</h4>
		<p>The <?php echo $this->_parent->_name; ?> <a href="<?php echo $this->_selfLink; ?>-settings">setttings page</a> has forms
		that will allow you to easily customize the different players settings. Those setting will be applied to every
		occurrence of the players throughout the site.</p>
		<br/>
		
		<h3>Version History</h3>
		<textarea rows="7" cols="65"><?php readfile( $this->_parent->_pluginPath . '/history.txt' ); ?></textarea>
		<br /><br />
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#pluginbuddy_debugtoggle").click(function() {
					jQuery("#pluginbuddy_debugtoggle_div").slideToggle();
				});
			});
		</script>
		
		<a id="pluginbuddy_debugtoggle" class="button secondary-button">Debugging Information</a>
		<div id="pluginbuddy_debugtoggle_div" style="display: none;">
			<h3>Debugging Information</h3>
			<?php
			echo '<textarea rows="7" cols="65">';
			echo 'Plugin Version = '.$this->_name.' '.$this->_parent->_version.' ('.$this->_parent->_var.')'."\n";
			echo 'WordPress Version = '.get_bloginfo("version")."\n";
			echo 'PHP Version = '.phpversion()."\n";
			global $wpdb;
			echo 'DB Version = '.$wpdb->db_version()."\n";
			echo "\n".serialize($this->_options);
			echo '</textarea>';
			?>
			<p>
			<form method="post" action="<?php echo $this->_selfLink; ?>">
				<input type="hidden" name="reset_defaults" value="true" />
				<input type="submit" name="submit" value="Reset Plugin Settings & Defaults" id="reset_defaults" class="button secondary-button" onclick="if ( !confirm('WARNING: This will reset all settings associated with this plugin to their defaults. Are you sure you want to do this?') ) { return false; }" />
			</form>
			</p>
		</div>
		<br /><br /><br />
		<a href="http://pluginbuddy.com" style="text-decoration: none;"><img src="<?php echo $this->_pluginURL; ?>/images/pluginbuddy.png" style="vertical-align: -3px;" /> PluginBuddy.com</a><br /><br />
	</div>
	<div class="postbox-container" style="width:20%; margin-top: 35px; margin-left: 15px;">
		<div class="metabox-holder">	
			<div class="meta-box-sortables">
				
				<div id="breadcrumbslike" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span>Things to do...</span></h3>
					<div class="inside">
						<ul class="pluginbuddy-nodecor">
							<li>- <a href="http://twitter.com/home?status=<?php echo urlencode('Check out this awesome plugin, ' . $this->_parent->_name . '! ' . $this->_parent->_url . ' @pluginbuddy'); ?>" title="Share on Twitter" onClick="window.open(jQuery(this).attr('href'),'ithemes_popup','toolbar=0,status=0,width=820,height=500,scrollbars=1'); return false;">Tweet about this plugin.</a></li>
							<li>- <a href="http://pluginbuddy.com/purchase/">Check out PluginBuddy plugins.</a></li>
							<li>- <a href="http://pluginbuddy.com/purchase/">Check out iThemes themes.</a></li>
							<li>- <a href="http://secure.hostgator.com/cgi-bin/affiliates/clickthru.cgi?id=ithemes">Get HostGator web hosting.</a></li>
						</ul>
					</div>
				</div>

				<div id="breadcrumsnews" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span>Latest news from PluginBuddy</span></h3>
					<div class="inside">
						<p style="font-weight: bold;">PluginBuddy.com</p>
						<?php $this->get_feed( 'http://pluginbuddy.com/feed/', 5 );  ?>
						<p style="font-weight: bold;">Twitter @pluginbuddy</p>
						<?php
						$twit_append = '<li>&nbsp;</li>';
						$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/twitter.png" style="vertical-align: -3px;" /> <a href="http://twitter.com/pluginbuddy/">Follow @pluginbuddy on Twitter.</a></li>';
						$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/feed.png" style="vertical-align: -3px;" /> <a href="http://pluginbuddy.com/feed/">Subscribe to RSS news feed.</a></li>';
						$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/email.png" style="vertical-align: -3px;" /> <a href="http://pluginbuddy.com/subscribe/">Subscribe to Email Newsletter.</a></li>';
						$this->get_feed( 'http://twitter.com/statuses/user_timeline/108700480.rss', 5, $twit_append, 'pluginbuddy: ' );
						?>
					</div>
				</div>
				
				<div id="breadcrumbssupport" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span>Need support?</span></h3>
					<div class="inside">
						<p>See our <a href="http://pluginbuddy.com/tutorials/">tutorials & videos</a> or visit our <a href="http://pluginbuddy.com/support/">support forum</a> for additional information and help.</p>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>
