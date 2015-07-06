<?php

/*
Written by Chris Jean for iThemes.com
Version 1.1.0

Version History
	1.0.0 - 2010-12-15 - Chris Jean
		Release ready
	1.0.1 - 2011-03-16 - Chris Jean
		Fixed issue where legacy custom settings no longer functioned
	1.1.0 - 2011-08-04 - Chris Jean
		Added Favicon settings
*/


if ( ! class_exists( 'ITThemeSettingsTabBasic' ) ) {
	class ITThemeSettingsTabBasic extends ITThemeSettingsTab {
		var $_var = 'theme-settings-basic';
		
		
		function screen_settings( $settings, $screen ) {
			return $settings;
//			return 'This is the basic tab\'s settings.';
		}
		
		function add_admin_scripts() {
			wp_enqueue_script( "{$this->_var}-basic-tab-script", "{$this->_parent->_plugin_url}/js/basic.js", array( 'jquery', 'postbox' ) );
		}
		
		function contextual_help( $text, $screen ) {
			ob_start();
			
?>
	<p><?php _e( 'This Settings page helps you control global settings for your Builder theme. To make working with the options easier, groups of options are divided into tabs.', 'it-l10n-Builder' ); ?></p>
	<p><?php _e( 'Each tab is divided into sections that can be collapsed by clicking on the title bar of the section. You can also rearrange the sections by dragging them by the title bar. When you collapse and rearrange items, their arrangement will be remembered.', 'it-l10n-Builder' ); ?></p>
	<p><?php _e( 'The Basic tab includes a mix of many different kinds of options that can help you quickly configure your Builder theme. Options include configuring basic menus, changing how Builder identifies widget areas, modifying comment functionality on your site, and enabling/disabling different Builder features. Some child themes add custom options in their own section to this tab. Each section describes its options in further details.', 'it-l10n-Builder' ); ?></p>
	<p><?php _e( 'The SEO tab is placeholder for a future feature. There is currently a beta SEO plugin you can download from the iThemes member area if you are interested in trying out the feature before it is bundled into Builder.', 'it-l10n-Builder' ); ?></p>
	<p><?php _e( 'The Import/Export tab allows you to export and import Builder settings, including Layouts and Views. Please visit that tab for more details.', 'it-l10n-Builder' ); ?></p>
<?php
			
			$text = ob_get_contents();
			ob_end_clean();
			
			return $text;
		}
		
		function _set_option_defaults() {
			$defaults = array();
			
			
			$post_type_descriptions = array(
				'post'       => __( 'Individual blog posts', 'it-l10n-Builder' ),
				'page'       => __( 'Individual pages', 'it-l10n-Builder' ),
				'attachment' => __( 'When you upload images, they get their own Media page that shows the image. This option controls whether or not comments are enabled on these Media pages.', 'it-l10n-Builder' ),
				''           => __( 'Custom post type', 'it-l10n-Builder' ),
			);
			
			if ( function_exists( 'get_post_types' ) && function_exists( 'post_type_supports' ) ) {
				$post_types = get_post_types( array() , 'objects' );
				
				foreach ( (array) $post_types as $post_type => $post_object ) {
					$post_type_var = str_replace( ' ', '_', $post_type );
					
					if ( post_type_supports( $post_type, 'comments' ) )
						$comment_post_types[$post_type_var] = array( 'name' => $post_object->labels->name );
				}
			}
			else {
				$comment_post_types = array( 'post' => array( 'name' => 'Post' ), 'page' => array( 'name' => 'Page' ) );
			}
			
			foreach ( array_keys( $comment_post_types ) as $post_type ) {
				if ( isset( $post_type_descriptions[$post_type] ) )
					$comment_post_types[$post_type]['description'] = $post_type_descriptions[$post_type];
				else
					$comment_post_types[$post_type]['description'] = $post_type_descriptions[''];
			}
			
			$comment_post_types = ITUtility::sort_array( $comment_post_types, 'name' );
			
			foreach ( array_keys( $comment_post_types ) as $post_type )
				$defaults["enable_comments_$post_type"] = 1;
			
			$this->_comment_post_types = $comment_post_types;
			
			
			$defaults['favicon_option'] = 'preset';
			$defaults['dashboard_favicon'] = 'on';
			
			
			
			$this->_options = array_merge( $defaults, $this->_options );
		}
		
		function _save() {
			it_classes_load( 'it-file-utility.php' );
			
			
			$data = ITForm::get_post_data();
			
			
			if ( ITFileUtility::file_uploaded( 'uploaded_favicon' ) ) {
				if ( preg_match( '/\.ico$/', $_FILES['uploaded_favicon']['name'] ) ) {
					$path = ITFileUtility::get_writable_directory( 'builder-favicon' );
					$name = ITUtility::get_random_string( array( 6, 10 ) ) . '.ico';
					
					if ( copy( $_FILES['uploaded_favicon']['tmp_name'], "$path/$name" ) ) {
						$data['favicon']['file'] = "$path/$name";
						$data['favicon']['url'] = ITFileUtility::get_url_from_file( "$path/$name" );
					}
					else
						$this->_errors[] = new WP_Error( 'unable-to-save-favicon', __( 'Unable to save the supplied Favicon image file. Pleave verify that the wp-content/uploads directory can be written to.', 'it-l10n-Builder' ) );
				}
				else {
					$file = ITFileUtility::create_favicon( 'builder-favicon', $_FILES['uploaded_favicon']['tmp_name'] );
					
					if ( false != $file ) {
						$url = ITFileUtility::get_url_from_file( $file );
						
						$data['favicon'] = array(
							'file' => $file,
							'url'  => $url,
						);
						
						
						$original_extension = strtolower( preg_replace( '/.+\.([^.]+)$/', '\1', $_FILES['uploaded_favicon']['name'] ) );
						$original_file = preg_replace( '/\.ico/', "-original.$original_extension", $file );
						$original_url = ITFileUtility::get_url_from_file( $original_file );
						
						if ( copy( $_FILES['uploaded_favicon']['tmp_name'], $original_file ) ) {
							$data['favicon']['original_file'] = $original_file;
							$data['favicon']['original_url'] = $original_url;
							
							
							if ( false !== ( $file_data = @file_get_contents( $original_file ) ) ) {
								if ( false !== ( $im = @imagecreatefromstring( $file_data ) ) ) {
									list( $width, $height ) = getimagesize( $original_file );
									
									$new_im = imagecreatetruecolor( 16, 16 );
									
									imagecolortransparent( $new_im, imagecolorallocatealpha( $new_im, 0, 0, 0, 127 ) );
									imagealphablending( $new_im, false );
									imagesavealpha( $new_im, true );
									
									if ( false !== imagecopyresampled( $new_im, $im, 0, 0, 0, 0, 16, 16, $width, $height ) ) {
										$resized_file = preg_replace( '/(\.[^.]+)$/', '-resized\1', $original_file );
										$result = imagepng( $new_im, $resized_file );
										
										if ( true == $result ) {
											$data['favicon']['original_resized_file'] = $resized_file;
											$data['favicon']['original_resized_url'] = ITFileUtility::get_url_from_file( $resized_file );
										}
									}
								}
								
								unset( $file_data );
							}
						}
					}
					else
						$this->_errors[] = new WP_Error( 'unable-to-create-favicon', __( 'Unable to generate a Favicon image from the supplied file. Please verify that the file is a valid JPG, JPEG, PNG, GIF, or ICO image.', 'it-l10n-Builder' ) );
				}
			}
			
			
			$this->_options = array_merge( $this->_options, $data );
			
			$this->_parent->_save();
		}
		
		function _register_meta_boxes() {
			$boxes = builder_get_settings_editor_boxes( 'basic' );
			
			$has_custom_boxes = false;
			
			foreach ( (array) $boxes as $var => $args ) {
				if ( true !== $args['_builtin'] )
					$has_custom_boxes = true;
			}
			
			if ( ( false === $has_custom_boxes ) && ( has_action( 'builder_custom_settings' ) ) )
				builder_add_settings_editor_box( __( 'Child Theme Settings', 'it-l10n-Builder' ), array( &$this, 'legacy_custom_meta_box_handler' ) );
			
			$boxes = builder_get_settings_editor_boxes( 'basic' );
			
			foreach ( (array) $boxes as $var => $args )
				$this->_add_meta_box( $var, $args );
		}
		
		function _editor() {
			if ( isset( $_REQUEST['updated'] ) )
				ITUtility::show_status_message( 'Theme Settings Updated' );
			
			if ( isset( $_REQUEST['errors'] ) ) {
				$error_codes = explode( ',', $_REQUEST['errors'] );
				
				foreach ( (array) $error_codes as $code ) {
					$message = get_transient( "it_bt_{$code}" );
					
					if ( false != $message )
						ITUtility::show_error_message( $message );
				}
			}
			
			$this->_set_option_defaults();
			
			$form =& new ITForm( $this->_options );
			$this->_form =& $form;
			
?>
	<div class="wrap">
		<?php $form->start_form(); ?>
			<?php screen_icon(); ?>
			<?php $this->_print_editor_tabs(); ?>
			
			<p><?php _e( 'For information about this page, please click the "Help" button at the top right.', 'it-l10n-Builder' ); ?></p>
			
			<?php $this->_print_meta_boxes(); ?>
			
			<p class="submit">
				<?php $form->add_submit( 'save', array( 'value' => 'Save Settings', 'class' => 'button-primary' ) ); ?>
				<?php //$form->add_submit( 'reset', array( 'value' => 'Restore Default Settings', 'class' => 'button-secondary', 'onClick' => "return confirm('Restoring default settings will reset all Builder settings. The layouts and views will not be reset. Are you sure that you want to restore all of Builder\'s settings to default values?');" ) ); ?>
			</p>
			
			<?php $form->add_hidden_no_save( 'editor_tab', $this->_parent->_active_tab ); ?>
		<?php $form->end_form(); ?>
		
		<form style="display:none" method="get" action="">
			<p>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			</p>
		</form>
	</div>
	
	<?php $this->_init_meta_boxes(); ?>
<?php
			
		}
		
		
		// Meta Boxes //////////////////////////////////////
		
		function meta_box_favicon() {
			$form =& $this->_form;
			
			$preset_files = glob( get_template_directory() . '/favicons/*.png' );
			$presets = array();
			
			foreach ( (array) $preset_files as $file ) {
				$base = basename( $file );
				$name = ucwords( preg_replace( '/[\-_]+/', ' ', preg_replace( '/\.[^.]+$/', '', $base ) ) );
				$file = preg_replace( '/\.[^.]+$/', '', $base );
				
				$presets[$file] = $name;
			}
			natcasesort( $presets );
			
			if ( '' == $form->get_option( 'favicon_preset' ) )
				$form->set_option( 'favicon_preset', key( $presets ) );
			
?>
	<p><?php _e( 'Favicons are small images that are used in a variety of ways, but the one most people are familiar with is that the Favicon is the small image used next to the title of a site in browser tabs. This makes it easy for people to quickly identify specific sites just by skimming the images on the tabs.', 'it-l10n-Builder' ); ?></p>
	<hr />
	
	<p><?php _e( 'The following options allow you to control the Favicon settings for your site. You can select from a few built-in options, upload your own image to use, or opt for the minimalist route by disabling the Favicon entirely.', 'it-l10n-Builder' ); ?></p>
	
	<ul class="no-bullets">
		<li>
			<label for="favicon_option-preset"><?php $form->add_radio( 'favicon_option', array( 'value' => 'preset', 'class' => 'show-hide-toggle' ) ); ?> <?php _e( 'Choose from a set of provided images (default)', 'it-l10n-Builder' ); ?></label>
			<?php ITUtility::add_tooltip( __( 'Don\'t have your own image that you want to use? Try one of ours.', 'it-l10n-Builder' ) ); ?>
		</li>
		<li>
			<label for="favicon_option-custom"><?php $form->add_radio( 'favicon_option', array( 'value' => 'custom', 'class' => 'show-hide-toggle' ) ); ?> <?php _e( 'Upload your own image', 'it-l10n-Builder' ); ?></label>
			<?php ITUtility::add_tooltip( __( 'Make your site your own by uploading a custom image to use.', 'it-l10n-Builder' ) ); ?>
		</li>
		<li>
			<label for="favicon_option-theme"><?php $form->add_radio( 'favicon_option', array( 'value' => 'theme', 'class' => 'show-hide-toggle' ) ); ?> <?php _e( 'Use the active child theme\'s Favicon image located at images/favicon.ico', 'it-l10n-Builder' ); ?></label>
			<?php ITUtility::add_tooltip( __( 'This is a legacy option provided to help some users transition to this new feature. Since this feature helps prevent issues with different browsers caching the Favicon image, it is recommended that you use the "Upload your own image" option to upload the image rather than this option.', 'it-l10n-Builder' ) ); ?>
		</li>
		<li>
			<label for="favicon_option-off"><?php $form->add_radio( 'favicon_option', array( 'value' => 'off', 'class' => 'show-hide-toggle' ) ); ?> <?php _e( 'Don\'t use a Favicon from Builder', 'it-l10n-Builder' ); ?></label>
			<?php ITUtility::add_tooltip( __( 'This will turn off Builder\'s built-in Favicon feature. Plugins can still provide a Favicon, and you can modify the header.php file if you\'d like to hand-code in your own solution.', 'it-l10n-Builder' ) ); ?>
		</li>
	</ul>
	
	<div class="favicon_option-options">
		<div class="favicon_option-preset-option">
			<br />
			
			<p><?php _e( 'Which image would you like to use for your Favicon?', 'it-l10n-Builder' ); ?></p>
			
			<ul class="no-bullets">
				<?php foreach ( $presets as $file => $name ) : ?>
					<li><label for="favicon_preset-<?php echo str_replace( '.', '-', $file ); ?>"><?php $form->add_radio( 'favicon_preset', $file ); ?> <img width="16" height="16" src="<?php echo get_template_directory_uri() . "/favicons/$file.png"; ?>" alt="<?php echo $name; ?>" title="<?php echo $name; ?>" /></li>
				<?php endforeach; ?>
			</ul>
		</div>
		
		<div class="favicon_option-custom-option">
			<br />
			
			
			<?php if ( isset( $this->_options['favicon'] ) && ! empty( $this->_options['favicon']['file'] ) ) : ?>
				<?php
					$image_url = '';
					
					if ( ! empty( $this->_options['favicon']['original_resized_url'] ) )
						$image_url = $this->_options['favicon']['original_resized_url'];
					else if ( ! empty( $this->_options['favicon']['original_url'] ) )
						$image_url = $this->_options['favicon']['original_url'];
				?>
				
				<div class="existing-custom-favicon-details">
					<?php if ( ! empty( $image_url ) ) : ?>
						<p><?php _e( 'You currently have the following image uploaded:', 'it-l10n-Builder' ); ?> <img width="16" height="16" alt="Uploaded Favicon Image" src="<?php echo $image_url; ?>" /></p>
					<?php else : ?>
						<p><?php _e( 'You currently have an image uploaded.', 'it-l10n-Builder' ); ?></p>
					<?php endif; ?>
					
					<p><a href="#" class="upload-new-custom-favicon">Click here</a> to upload a new image.</p>
				</div>
			<?php endif; ?>
			
			<div class="upload-favicon-info">
				<p><?php _e( 'The file format for a Favicon image should be ICO, a format created for Windows; however, if you upload a JPG, PNG, or GIF image, a valid ICO file will automatically be generated for you.', 'it-l10n-Builder' ); ?></p>
				<p><?php _e( 'This image will be shown at just 16 pixels by 16 pixels, so make sure that the image you use is that size or is square and can resize nicely to those dimensions.', 'it-l10n-Builder' ); ?></p>
				
				<p>
					<?php $form->add_file_upload( 'uploaded_favicon' ); ?>
					<?php ITUtility::add_tooltip( 'The following image formats are accepted: JPG, PNG, GIF, and ICO.', 'it-l10n-Builder' );?>
				</p>
				
				<p><?php _e( '<em>Make sure that you click on the "Save Settings" button below for your file to be uploaded.</em>', 'it-l10n-Builder' ); ?></p>
			</div>
		</div>
		
		<div class="favicon_option-preset-option favicon_option-custom-option">
			<br />
			
			<p><?php _e( 'Should the selected Favicon be used for the WordPress Dashboard?', 'it-l10n-Builder' ); ?></p>
			
			<ul class="no-bullets">
				<li>
					<label for="dashboard_favicon-on"><?php $form->add_radio( 'dashboard_favicon', 'on' ); ?> <?php _e( 'Yes, use the selected Favicon in the Dashboard (default)', 'it-l10n-Builder' ); ?></label>
					<?php ITUtility::add_tooltip( __( 'Make your site\'s tabs easier to identify by using the same Favicon for all tabs, even those that show the Dashboard (the back-end of WordPress).', 'it-l10n-Builder' ) ); ?>
				</li>
				<li>
					<label for="dashboard_favicon-off"><?php $form->add_radio( 'dashboard_favicon', 'off' ); ?> <?php _e( 'No, do not use the selected Favicon in the Dashboard', 'it-l10n-Builder' ); ?></label>
					<?php ITUtility::add_tooltip( __( 'Do not add the Favicon to the Dashboard. This will not prevent a plugin from adding a Favicon to the Dashboard.', 'it-l10n-Builder' ) ); ?>
				</li>
			</ul>
		</div>
	</div>
	
<?php
			
		}
		
		function meta_box_menu_builder() {
			
?>
	<?php if ( function_exists( 'wp_nav_menu' ) ) : ?>
		<p><?php printf( __( 'Builder offers a variety of ways to create navigation menus. The "Pages" and "Categories" sets of checkboxes below are the original Menu Builder offered by Builder. As long as your site is running WordPress 3.0 or above, you can use the much more powerful <a href="%1$s">Menus editor</a> built into WordPress and found under <a href="%1$s">Appearance &gt; Menus</a>. It is recommended that you use the new Menus editor rather than this now legacy set of menu options as WordPress\' Menus editor offers many more options.', 'it-l10n-Builder' ), admin_url( 'nav-menus.php' ) ); ?></p>
	<?php endif; ?>
	
	<p><?php printf( __( 'Menus are added to layouts with the Navigation module. To add or modify Navigation modules use the <a href="%1$s">Layouts editor</a> found under <a href="%1s$">My Theme &gt; Layouts</a>. Once in the Layout editor, select which layout you would like to modify, add new or modify Navigation modules as needed, and select the appropriate menu that should be used at that location. The menu options available in the Navigation module are as follows:', 'it-l10n-Builder' ), admin_url( 'admin.php?page=layout-editor' ) ); ?></p>
	
	<table class="settings-table" cellspacing="0">
		<tr><th scope="row"><?php _e( 'Builder Settings Pages', 'it-l10n-Builder' ); ?></th><td><?php _e( 'Controlled by the Pages checkboxes below', 'it-l10n-Builder' ); ?></td></tr>
		<tr><th scope="row"><?php _e( 'Builder Settings Categories', 'it-l10n-Builder' ); ?></th><td><?php _e( 'Controlled by the Categories checkboxes below', 'it-l10n-Builder' ); ?></td></tr>
		<tr><th scope="row"><?php _e( 'WordPress Pages', 'it-l10n-Builder' ); ?></th><td><?php _e( 'Lists all the pages on the site', 'it-l10n-Builder' ); ?></td></tr>
		<?php if ( function_exists( 'wp_nav_menu' ) ) : ?>
			<tr scope="row"><th><?php _e( 'Custom Menu - MENU NAME', 'it-l10n-Builder' ); ?></th><td><?php printf( __( 'An option is available for each menu created in WordPress\' built-in <a href="%s">Menus editor</a>', 'it-l10n-Builder' ), admin_url( 'nav-menus.php' ) ); ?></td></tr>
		<?php endif; ?>
	</table>
	<hr />
	
	<div class="clearfix">
		<div class="menu-builder-type pages">
			<h4>Pages</h4>
			
			<div class="border-box">
				<?php $this->_create_menu_builder_checkboxes( 'include_pages', 'pages' ); ?>
			</div>
		</div>
		
		<div class="menu-builder-type categories">
			<h4>Categories</h4>
			
			<div class="border-box">
				<?php $this->_create_menu_builder_checkboxes( 'include_cats', 'categories' ); ?>
			</div>
		</div>
	</div>
<?php
			
		}
		
		function meta_box_widgets() {
			$form =& $this->_form;
			
?>
	<p><?php _e( 'Much of the power of Builder comes from the large number of widget areas you can create and manage. To make it easier to manage such a large number of widgets, Builder can show the name of each widget area (sidebar) by adding some filler content to the area. This both identifies the name of the widget area and makes it easier to see the full structure of a layout. These settings control this feature.', 'it-l10n-Builder' ); ?></p>
	<hr />
	
	<p><?php _e( 'When should a widget area be identified?', 'it-l10n-Builder' ); ?></p>
	<ul class="no-bullets">
		<li><label for="identify_widget_areas_method_empty"><?php $form->add_radio( 'identify_widget_areas_method', array( 'value' => 'empty', 'id' => 'identify_widget_areas_method_empty' ) ); ?> <?php _e( 'Only if widget area does not have any widgets (default)', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="identify_widget_areas_method_always"><?php $form->add_radio( 'identify_widget_areas_method', array( 'value' => 'always', 'id' => 'identify_widget_areas_method_always' ) ); ?> <?php _e( 'Always identify the widget area', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="identify_widget_areas_method_never"><?php $form->add_radio( 'identify_widget_areas_method', array( 'value' => 'never', 'id' => 'identify_widget_areas_method_never' ) ); ?> <?php _e( 'Disable widget area identification', 'it-l10n-Builder' ); ?></label></li>
	</ul>
	<br />
	
	<p><?php _e( 'Who should be able to see the widget identification information?', 'it-l10n-Builder' ); ?></p>
	<ul class="no-bullets">
		<li><label for="identify_widget_areas_admin"><?php $form->add_radio( 'identify_widget_areas', array( 'value' => 'admin', 'id' => 'identify_widget_areas_admin' ) ); ?> <?php _e( 'Only logged in users that can modify widgets (default)', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="identify_widget_areas_user"><?php $form->add_radio( 'identify_widget_areas', array( 'value' => 'user', 'id' => 'identify_widget_areas_user' ) ); ?> <?php _e( 'Any logged in user', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="identify_widget_areas_all"><?php $form->add_radio( 'identify_widget_areas', array( 'value' => 'all', 'id' => 'identify_widget_areas_all' ) ); ?> <?php _e( 'Everyone including visitors that are not logged in', 'it-l10n-Builder' ); ?></label></li>
	</ul>
<?php
			
		}
		
		function meta_box_analytics() {
			$form =& $this->_form;
			
?>
	<p><?php printf( __( '<a href="%1$s">Web analytics software</a> tracks what content visitors to your site are interested in and how they get to your site. Two web analytics providers popular with WordPress users are <a href="%2$s">Google Analytics</a> and <a href="%3$s">Woopra</a>. Both offer free services depending on usage. Use the settings below to easily add Google Analytics and Woopra tracking code to your site.', 'it-l10n-Builder' ), 'http://en.wikipedia.org/wiki/Web_analytics', 'http://www.google.com/analytics/', 'http://www.woopra.com/' ); ?></p>
	
	<p><?php _e( 'Before activating Builder\'s built-in support for either Google Analytics or Woopra, disable any plugins offering the same feature. Failure to do so can result in each visitor being counted multiple times, which will badly skew your data.', 'it-l10n-Builder' ); ?></p>
	
<!--<p><?php printf( __( 'When using this Builder feature to integrate Google Analytics or Woopra code, the following list of features are used by default. These features can be customized in the <a href="%s">Advanced Tab</a>.', 'it-l10n-Builder' ), $this->_parent->_tabless_self_link . '&editor_tab=advanced' ); ?></p>
	
	<ul>
		<li><?php _e( 'Information about the visitor and current site view are tracked.', 'it-l10n-Builder' ); ?></li>
		<li><?php _e( 'Outbound, download, and email link clicks are tracked.', 'it-l10n-Builder' ); ?></li>
		<li><?php _e( 'Searches and submitted comments are tracked as events.', 'it-l10n-Builder' ); ?></li>
		<li><?php _e( 'The recommended asynchronous code is used in the header. This speeds page load times and offers more reliable tracking.', 'it-l10n-Builder' ); ?></li>
		<li><?php _e( 'Logged in users with a role of Editor or Administrator are not tracked. This prevents traffic inflation by editors and site administrators.', 'it-l10n-Builder' ); ?></li>
	</ul>-->
	
	<p><?php _e( 'Beyond web analytics, some web tools or applications require adding JavaScript code either inside the head tag or in the site\'s footer. Use the text area inputs below to manually add code where it is needed.', 'it-l10n-Builder' ); ?></p>
	
	<hr />
	
	
	<p><label><?php $form->add_check_box( 'google_analytics_enable', array( 'class' => 'show-hide-toggle' ) ); ?> <?php _e( 'Enable Google Analytics', 'it-l10n-Builder' ); ?></label></li>
	
	<p class="google_analytics_enable-option"><?php printf( __( 'Your site is uniquely identified in Google Analytics by an Account ID. An example Account ID is UA-12345-6. For help on finding your Account ID, watch <a href="%s">this video</a>.', 'it-l10n-Builder' ), "{$this->_parent->_self_link}&render_clean=1&show_video=LhZ-Zwy06Ik&video_width=848&video_height=504&TB_iframe=true&width=848&height=504" ); ?></p>
	<p class="google_analytics-option"><label><?php _e( 'Google Analytics Account ID', 'it-l10n-Builder' ); ?> <?php $form->add_text_box( 'google_analytics_account_id' ); ?> <?php _e( '(required)', 'it-l10n-Builder' ); ?></label></li>
	
	<br class="google_analytics-option" />
	
	
	<p><label><?php $form->add_check_box( 'woopra_enable', array( 'class' => 'show-hide-toggle' ) ); ?> <?php _e( 'Enable Woopra', 'it-l10n-Builder' ); ?></label></p>
	
	<p class="woopra_enable-option"><?php printf( __( 'By default (when the Woopra Domain input below is empty), Woopra will use the domain of the "Site address (URL)" configured in <a href="%s">Settings &gt; General</a>. This can be changed by supplying a new domain below.', 'it-l10n-Builder' ), admin_url( 'options-general.php' ) ); ?></p>
	<p class="woopra-option"><label><?php _e( 'Woopra Domain', 'it-l10n-Builder' ); ?> <?php $form->add_text_box( 'woopra_domain' ); ?> <?php _e( '(optional)', 'it-l10n-Builder' ); ?></label></p>
	
	
	<br />
	
	<p><?php _e( 'List any JavaScript or other code to be manually inserted inside the site\'s <code>&lt;head&gt;</code> tag in the input below.', 'it-l10n-Builder' ); ?></p>
	<p><?php $form->add_text_area( 'javascript_code_header', array( 'style' => 'width:600px;height:150px;' ) ); ?></p>
	<br />
	
	<p><?php _e( 'List any JavaScript or other code to be manually inserted in the site\'s footer just above the <code>&lt;/body&gt;</code> tag in the input below.', 'it-l10n-Builder' ); ?></p>
	<p><?php $form->add_text_area( 'javascript_code_footer', array( 'style' => 'width:600px;height:150px;' ) ); ?></p>
<?php
			
		}
		
		function meta_box_theme_features() {
			global $builder_theme_feature_options;
			
			$form =& $this->_form;
			
?>
	<p><?php _e( 'Builder offers a large number of built-in features. These features can be enabled and disabled using the following options.', 'it-l10n-Builder' ); ?>
	<hr />
<?php
			
			foreach ( (array) $builder_theme_feature_options as $feature => $details ) {
				if ( ! current_theme_supports( $feature ) )
					continue;
				
				$default_enabled = ( ! empty( $details['default_enabled'] ) ) ? __( ' (default)', 'it-l10n-Builder' ) : '';
				$default_disabled = ( empty( $details['default_enabled'] ) ) ? __( ' (default)', 'it-l10n-Builder' ) : '';
				
				if ( isset( $after_first ) )
					echo "<br />\n";
				
?>
		<p><?php echo $details['description']; ?></p>
		<ul class="no-bullets">
			<li><label><?php $form->add_radio( "theme_supports_$feature", array( 'value' => 'enable', 'id' => "theme_supports_{$feature}_enable" ) ); ?> <?php printf( __( 'Enable %1$s%2$s', 'it-l10n-Builder' ), $details['name'], $default_enabled ); ?></label></li>
			<li><label><?php $form->add_radio( "theme_supports_$feature", array( 'value' => '', 'id' => "theme_supports_{$feature}_disable" ) ); ?> <?php printf( __( 'Disable %1$s%2$s', 'it-l10n-Builder' ), $details['name'], $default_disabled ); ?></label></li>
		</ul>
<?php
		
				$after_first = true;
			}
		}
		
		function meta_box_seo() {
			$form =& $this->_form;
			
?>
	<p><?php printf( __( '<a href="%s">SEO</a> is short for Search Engine Optimization. The goal of of SEO is to increase traffic to the site through search engines, typically by focusing on specific keywords and phrases.', 'it-l10n-Builder' ), 'http://en.wikipedia.org/wiki/Search_engine_optimization' ); ?></p>
	<p><?php _e( 'While much of the SEO process relies upon creating content that is relavent and follows SEO best-practices, both WordPress and Builder offer tools that help you control the SEO characteristics of your site. These options allow you to customize the SEO features of Builder.', 'it-l10n-Builder' ); ?></p>
	<hr />
	
<!--	<p><?php //printf( __( 'Page titles tell visitors what page they are currently on; however, studies show that users rarely look at the title at the top of the browser. Titles are still very important though. Search engines, social news websites (such as <a href="%1$s">Digg</a> or <a href="%2$s">reddit</a>), and ', 'it-l10n-Builder' ), 'http://digg.com/', 'http://www.reddit.com/' ); ?></p>-->
	
	<p><?php printf( __( 'The value of <a href="%s">META keywords</a> is typically considered to be very low or non-existent. Having them can\'t hurt your site however.', 'it-l10n-Builder' ), 'http://en.wikipedia.org/wiki/Meta_element#The_keywords_attribute' ); ?></p>
	<p><?php _e( 'Builder can automatically generate keywords for your post pages based upon the tags you have assigned to the post. You can use the following options to customize how Builder uses keywords.', 'it-l10n-Builder' ); ?></p>
	<ul class="no-bullets">
		<li><label for="tag_as_keyword_yes"><?php $form->add_radio( 'tag_as_keyword', array( 'value' => 'yes', 'id' => 'tag_as_keyword_yes' ) ); ?> <?php _e( 'Enable automatic generation of META keywords for individual posts based upon the assigned tags. (default)', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="tag_as_keyword_no"><?php $form->add_radio( 'tag_as_keyword', array( 'value' => 'no', 'id' => 'tag_as_keyword_no' ) ); ?> <?php _e( 'Do not automatically generate META keywords for individual posts.', 'it-l10n-Builder' ); ?></label></li>
	</ul>
	<br />
	
	<p><?php printf( __( 'Allowing a search engine to <a href="%s">index</a> content on your site means that the content is searchable and that you would like search engines to send traffic to that location on your site. Since there are many views on a WordPress site that contain duplicate information (individual post views, post listings, etc) and duplicate content on your site can hurt your search rankings, Builder (by default) will only tell search engines to index the home page, blog posts, and pages.', 'it-l10n-Builder' ), 'http://en.wikipedia.org/wiki/Index_(search_engine)' ); ?></p>
	<p><?php _e( 'Depending on the configuration of your site, you may have unique content on your category archive pages. The following option allows you to control the indexing of category archives. Enabling category archive indexing without having a specific reason to do so is not recommended.', 'it-l10n-Builder' ); ?></p>
	<ul class="no-bullets">
		<li><label for="cat_index_no"><?php $form->add_radio( 'cat_index', array( 'value' => 'no', 'id' => 'cat_index_no' ) ); ?> <?php _e( 'Disable category archive indexing (default)', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="cat_index_yes"><?php $form->add_radio( 'cat_index', array( 'value' => 'yes', 'id' => 'cat_index_yes' ) ); ?> <?php _e( 'Enable category archive indexing', 'it-l10n-Builder' ); ?></label></li>
	</ul>
<?php
			
		}
		
		function meta_box_comments() {
			$form =& $this->_form;
			
?>
	<p><?php printf( __( 'Most comments settings for your site are provided by WordPress in the <a href="%s">Settings &gt; Discussion</a> page. The following settings customize options specific to the theme.', 'it-l10n-Builder' ), admin_url( 'options-discussion.php' ) ); ?></p>
	<hr />
	
	<p><?php _e( 'Displaying comments and the comment entry form can be enabled or disabled for each type of content by checking or unchecking the appropriate checkboxes below. When a content type has comments disabled, neither any comment counts, existing comments, the comment entry form, nor the "comments are closed" message will be shown.', 'it-l10n-Builder' ); ?></p>
	<p><?php _e( 'Comments can be disabled on a per-entry basis. If comments are not appearing, modify the specific entry (post, page, etc) that should have comments to ensure that the "Allow comments" checkbox under "Discussion" is checked.', 'it-l10n-Builder' ); ?></p>
	<p><?php _e( 'Which content types should display comment counts (in listings), comments, the comment form, and "comments are closed" messages?', 'it-l10n-Builder' ); ?></p>
	<ul class="no-bullets">
		<?php foreach ( (array) $this->_comment_post_types as $post_type => $details ) : ?>
			<li>
				<label for="enable_comments_<?php echo $post_type; ?>"><?php $form->add_check_box( "enable_comments_{$post_type}" ); ?> <?php echo $details['name'] ?></label>
				<?php ITUtility::add_tooltip( $details['description'] ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<br />
	
	<p><?php _e( 'What should be displayed if comments have been disabled for a specific entry? This message will be shown in place of the comment entry form.', 'it-l10n-Builder' ); ?></p>
	<ul class="no-bullets">
		<li><label for="comments_disabled_message_none"><?php $form->add_radio( 'comments_disabled_message', array( 'value' => 'none', 'id' => 'comments_disabled_message_none' ) ); ?> <?php _e( 'Do not display any message (default)', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="comments_disabled_message_standard"><?php $form->add_radio( 'comments_disabled_message', array( 'value' => 'standard', 'id' => 'comments_disabled_message_standard' ) ); ?> <?php _e( 'Display message provided by theme, default in Builder is "Comments are closed."', 'it-l10n-Builder' ); ?></label></li>
		<li><label for="comments_disabled_message_custom"><?php $form->add_radio( 'comments_disabled_message', array( 'value' => 'custom', 'id' => 'comments_disabled_message_custom' ) ); ?> <?php _e( 'Custom message: ', 'it-l10n-Builder' ); ?></label> <?php $form->add_text_box( 'comments_disabled_message_custom_message', array( 'style' => 'width:400px;' ) ); ?></li>
	</ul>
<?php
			
		}
		
		
		// Helper Functions //////////////////////////////////////
		
		function _create_menu_builder_checkboxes( $var, $type ) {
			if ( empty( $this->_options[$var] ) )
				$this->_options[$var] = array();
			
			$options = array();
			
			if ( 'pages' == $type ) {
				$options['home'] = array( 'title' => 'Home', 'depth' => 0 );
				$options['site_name'] = array( 'title' => get_option( 'blogname' ) . ' (links to home)', 'depth' => 0 );
				$source_options = get_pages();
			}
			else if ( 'categories' == $type ) {
				$source_options = array();
				$this->_get_sorted_hierarchical_categories( $source_options );
			}
			
			
			foreach ( (array) $source_options as $option ) {
				if ( 'pages' == $type ) {
					$parent = $option->post_parent;
					$title = $option->post_title;
					$id = $option->ID;
				}
				else if ( 'categories' == $type ) {
					$parent = $option['parent'];
					$title = $option['name'];
					$id = $option['id'];
				}
				
				if ( 0 == $parent )
					$options[$id] = array( 'title' => $title, 'depth' => 0 );
				else
					$options[$id] = array( 'title' => $title, 'depth' => ( $options[$parent]['depth'] + 1 ) );
			}
			
			$last_depth = 0;
			
			echo "<ul class='no-bullets'>\n";
			
			foreach ( (array) $options as $id => $data ) {
				if ( $data['depth'] == $last_depth ) {
					echo "</li>\n";
				}
				else if ( $data['depth'] > $last_depth ) {
					echo "\n<ul>\n";
				}
				else {
					while ( $data['depth'] < $last_depth ) {
						echo "</li>\n</ul>\n</li>\n";
						$last_depth--;
					}
				}
				
				$attributes = array(
					'value' => $id,
					'id'	=> "$var-$id",
				);
				
				if ( in_array( $id, $this->_options[$var] ) )
					$attributes['checked'] = 'checked';
				
				echo "<li><label for='{$attributes['id']}'>" . $this->_form->get_multi_check_box( $var, $attributes ) . " {$data['title']}</label>";
				
				$last_depth = $data['depth'];
			}
			
			while ( -1 < $last_depth ) {
				echo "\n</ul>\n";
				$last_depth--;
			}
		}
		
		function _get_sorted_hierarchical_categories( &$retval, $parent = 0, $depth = 0 ) {
			$categories = get_categories( "hide_empty=0&orderby=name&child_of=$parent" );
			
			if ( empty( $categories ) )
				return array();
			
			foreach ( (array) $categories as $category ) {
				if ( $category->parent != $parent )
					continue;
				
				$retval[] = array( 'name' => $category->name, 'id' => $category->cat_ID, 'depth' => $depth, 'parent' => $category->parent );
				
				$this->_get_sorted_hierarchical_categories( $retval, $category->term_id, $depth + 1 );
			}
			
			return $retval;
		}
	}
}

?>
