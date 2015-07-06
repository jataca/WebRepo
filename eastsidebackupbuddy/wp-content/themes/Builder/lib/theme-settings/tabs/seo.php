<?php

/*
Written by Chris Jean for iThemes.com
Version 0.0.1

Version History
	0.0.1 - 2010-11-03 - Chris Jean
		Initial version
*/


if ( ! class_exists( 'ITThemeSettingsTabSEO' ) ) {
	class ITThemeSettingsTabSEO extends ITThemeSettingsTab {
		var $_var = 'theme-settings-seo';
		
		
		function add_admin_scripts() {
			wp_enqueue_script( "{$this->_parent->_var}-seo-tab-script", "{$this->_parent->_plugin_url}/js/seo-editor.js" );
		}
		
		function add_admin_styles() {
			wp_enqueue_style( "{$this->_parent->_var}-seo-tab-style", "{$this->_parent->_plugin_url}/css/seo-editor.css" );
		}
		
		function set_defaults( $defaults ) {
			$new_defaults = array(
				'show_editor_information'	=> '1',
				'show_editor_advanced'		=> '',
				
				'description_setting'								=> 'default',
				'enable_automatic_descriptions_pages'				=> '1',
				'enable_automatic_descriptions_posts'				=> '1',
				'enable_automatic_descriptions_custom_post_types'	=> '1',
				'enable_custom_descriptions_pages'					=> '1',
				'enable_custom_descriptions_posts'					=> '1',
				'enable_custom_descriptions_custom_post_types'		=> '1',
				'other_views_description'							=> '',
				
				'title_setting'								=> 'default',
				'enable_custom_titles_pages'				=> '1',
				'enable_custom_titles_posts'				=> '1',
				'enable_custom_titles_custom_post_types'	=> '1',
				'title_type'								=> 'simple',
				'simple_title_style'						=> 'title_name',
				'simple_title_separator'					=> '::',
				'simple_title_separator_custom'				=> '',
				'page_number_listing_format'				=> ' %sep% Page %page-number%',
				'separator_format'							=> '::',
				
				'robots_setting'							=> 'default',
				'enable_dmoz'								=> '',
				'enable_yahoo_directory'					=> '',
				'enable_archive'							=> '1',
				'enable_snippet'							=> '1',
				'enable_custom_robots_pages'				=> '1',
				'enable_custom_robots_posts'				=> '1',
				'enable_custom_robots_custom_post_types'	=> '1',
				'indexing_setting'							=> 'default',
				
				'keywords_setting'							=> 'default',
				'enable_custom_keywords_pages'				=> '1',
				'enable_custom_keywords_posts'				=> '1',
				'enable_custom_keywords_custom_post_types'	=> '1',
				'post_keywords'								=> 'categories_and_tags',
			);
			
			$views_data = builder_seo_get_views_data();
			
			foreach ( (array) $views_data as $view ) {
				if ( ! empty( $view['robots_default'] ) )
					$new_defaults["robots_views_{$view['priority']}_{$view['function']}"] = $view['robots_default'];
				
				if ( ! empty( $view['title_default'] ) )
					$new_defaults["title_views_{$view['priority']}_{$view['function']}"] = $view['title_default'];
			}
			
			
			if ( ! isset( $defaults['seo'] ) )
				$defaults['seo'] = $new_defaults;
			else
				$defaults['seo'] = array_merge( $defaults['seo'], $new_defaults );
			
			$this->_defaults = $defaults['seo'];
			
			return $defaults;
		}
		
		function _save() {
			$data = ITForm::get_post_data();
			
			$this->_options['seo'] = array_merge( $this->_options['seo'], $data );
			
			$this->_parent->_save();
			
			ITUtility::show_status_message( 'SEO Settings Updated' );
		}
		
		function _editor() {
			$form =& new ITForm( $this->_options['seo'] );
			$this->_form =& $form;
			
			$this->_add_meta_box( 'titles', __( 'Titles', 'it-l10n-Builder' ) );
			$this->_add_meta_box( 'description', __( 'Description', 'it-l10n-Builder' ) );
			$this->_add_meta_box( 'robots', __( 'Robots', 'it-l10n-Builder' ) );
			$this->_add_meta_box( 'keywords', __( 'Keywords', 'it-l10n-Builder' ) );
//			$this->_add_meta_box( 'best_practices', __( 'SEO Best Practices', 'it-l10n-Builder' ) );
//			$this->_add_meta_box( 'info', __( 'More Information', 'it-l10n-Builder' ) );
			
?>
	<div class="wrap">
		<?php $form->start_form(); ?>
			<?php screen_icon(); ?>
			<?php $this->_print_editor_tabs(); ?>
			
			<p>
				<?php $form->add_check_box( 'show_editor_information' ); ?>
				<label for="show_editor_information">Show detailed information about the options</label>
			</p>
<!--		<p>
				<?php $form->add_check_box( 'show_editor_advanced' ); ?>
				<label for="show_editor_advanced">Show advanced options</label>
				<span class="option-description information">The advanced options gives more control but shouldn't be needed by most users.</span>
			</p>-->
			
			<p><?php _e( 'Meta tags give web browsers and search engines information about the current page. While creating quality, relevant content is the most important SEO practice of all, having good meta tags <i>can</i> improve your rankings and provide better information in your search listings.', 'it-l10n-Builder' ); ?></p>
			
			<?php $this->_print_meta_boxes(); ?>
			
			<p class="submit">
				<?php $form->add_submit( 'save', array( 'value' => 'Save SEO Settings', 'class' => 'button-primary' ) ); ?>
				<?php $form->add_submit( 'reset', array( 'value' => 'Restore Default Settings', 'class' => 'button-secondary' ) ); ?>
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
		
		function _save_options() {
			$new_options = array();
			
			foreach ( (array) explode( ',', $_POST['used-inputs'] ) as $var )
				$new_options[$var] = ( isset( $_POST[$var] ) ) ? $_POST[$var] : '';
			
			$views =& builder_seo_get_views_data();
			
			
			$function_ids = array();
			
			foreach ( (array) $views as $id => $view ) {
				$function_ids[$view['function']]['id'] = $id;
				
				if ( isset( $view['overrides'] ) )
					$function_ids[$views[$view['overrides']]['function']]['overrides'][] = $id;
			}
			
			
			$robots_views = array();
			$title_views = array();
			
			foreach ( (array) $new_options as $var => $val ) {
				$val = htmlentities( $val );
				$new_options[$var] = $val;
				
				if ( preg_match( '/^(robots_views|title_views)_(\d+)_(.+)/', $var, $matches ) ) {
					if ( 'robots_views' === $matches[1] )
						$robots_views[$matches[2]][$matches[3]] = $val;
					else
						$title_views[$matches[2]][$matches[3]] = $val;
				}
			}
			
			krsort( $robots_views );
			krsort( $title_views );
			
			$views_data = array( 'robots' => $robots_views, 'title' => $title_views );
			
			$this->_options = $new_options;
			
			
			$flat_values = array();
			
			foreach ( (array) $views_data as $type => $data ) {
				foreach ( (array) $data as $priority => $functions ) {
					foreach ( (array) $functions as $function => $value ) {
						if ( ! isset( $this->_options["{$type}_views"][$priority][$function] ) ) {
							$id = $function_ids[$function]['id'];
							$parent_id = ( isset( $views[$id]['overrides'] ) ) ? $views[$id]['overrides'] : null;
							
							if ( 'inherit' === $value )
								$value = $flat_values[$parent_id];
							
							$this->_options["{$type}_views"][$priority][$function] = $value;
							$flat_values[$id] = $value;
							
							if ( empty( $this->_options["customize_{$type}_sub_views_{$function_ids[$function]['id']}"] ) && ! empty( $function_ids[$function]['overrides'] ) )
								$this->_set_sub_view_options( $type, $function_ids[$function]['id'], $value, &$function_ids, &$views );
						}
					}
				}
			}
			
			
			$this->_save();
			
			$this->_options_screen();
		}
		
		function _set_sub_view_options( $type, $id, $value, $function_ids, $views ) {
			foreach ( (array) $function_ids[$views[$id]['function']]['overrides'] as $override_id ) {
				$view = $views[$override_id];
				
				$this->_options["{$type}_views"][$view['priority']][$view['function']] = $value;
				
				
				if ( ! empty( $function_ids[$view['function']]['overrides'] ) )
					$this->_set_sub_view_options( $type, $override_id, $value, &$function_ids, &$views );
			}
		}
		
		function meta_box_titles() {
			$form = $this->_form;
			
			$title_settings = array(
				''			=> 'Disabled - Builder will simply call wp_title() so that plugins can modify the title',
				'default'	=> 'Default - Builder will provide titles if they are not already provided by a plugin',
				'forced'	=> 'Forced - Builder will always remove any plugin-set titles and use its own',
			);
			
			$title_types = array(
				'simple'	=> 'Simple Titles (default)',
				'custom'	=> 'Custom Titles',
			);
			
			$title_styles = array(
				'title_name'	=> 'Title :: Site Name (default)',
				'name_title'	=> 'Site Name :: Title',
				'title'			=> 'Title',
			);
			
			$temp_separators = array(
				'&raquo;', '&rsaquo;', '&rArr;', '&rarr;',
				'&laquo;', '&lsaquo;', '&lArr;', '&larr;',
				'&hArr;', '&harr;', '&mdash;', '&ndash;',
				'::', ':', '&frasl;&frasl;', '&frasl;',
				'||', '|', '&brvbar;&brvbar;', '&brvbar;',
				'&sect;', '&bull;', '&middot;', '&curren;',
				'&dagger;', '&Dagger;', '&there4;', '&sim;',
				'&asymp;', '&ne;', '&int;', '&loz;',
				'&lang;', '&rang;', '&oplus;', '&otimes;',
				'&times;', 'custom',
			);
			
			foreach ( (array) $temp_separators as $sep ) {
				$default = '';
				if ( '::' === $sep )
					$default = ' (default)';
				
				if ( 'custom' !== $sep )
					$separators[htmlentities( $sep )] = "Title $sep Site Name$default";
				else
					$separators[$sep] = "Custom$default";
			}
			
?>
	<div class="information">
		<p>Titles show in the title bar and/or tab of browsers. They are also used for search result links as you can see in the following sample search result.</p>
		<p><img style="border:1px solid #CCC;" src="<?php echo $this->_parent->_plugin_url; ?>/images/ithemes-search-result.jpg" alt="iThemes Search Result" /></p>
		<p>By default, Builder will do its best to allow plugins to provide the title, and will only provide a title if a plugin has not already provided one. This behavior can be changed to either disable Builder's handling of the title tag or to force Builder to always use the title tag it generates.</p>
	</div>
	
	<p>Title Setting: <?php $form->add_drop_down( 'title_setting', $title_settings ); ?></p>
	
	<div id="title-options">
		<h3>Custom Titles for Pages and Posts</h3>
		
		<p class="information">Pages and Posts always have titles associated with them; however, sometimes you might wish to customize the title used for the title tag separately from the actual title.</p>
		<p>A custom title entry box can be added to the edit screens of these content types. When the option is enabled, a text area labeled "Title Tag" will be added to the "SEO Options" box in that content type's edit screen.</p>
		<div class="left-indent-options">
			<div><?php $form->add_check_box( 'enable_custom_titles_pages' ); ?><label for='enable_custom_titles_pages'> Pages</label></div>
			<div><?php $form->add_check_box( 'enable_custom_titles_posts' ); ?><label for='enable_custom_titles_posts'> Posts</label></div>
		</div>
		
		
		<h3>Title Formats</h3>
		
		<p class="information">There are two options you can use to customize the title formats of the site. The default option (Simple Titles) is to use a set of two dropdowns to select the basic format of the titles and the separator used to divide the menu elements. The second option (Custom Title) gives you full control over the titles.</p>
		<p>Select the type of title control you'd like: <?php $form->add_drop_down( 'title_type', $title_types ); ?></p>
		
		<div class="titles-options-group" id="simple-titles-options-group">
			<h3>Simple Title Formats</h3>
			
			<p class="information">The style defines the order of items that are included in the title. For example, it defines whether the site name is listed at the beginning or end of the title. It also can remove the site name from the title entirely.</p>
			<p>Select a title style: <?php $form->add_drop_down( 'simple_title_style', $title_styles ); ?></p>
			
			<p class="information">The separator divides the different sections of the title. For example, it separates the main page/post title from the site name. Even if you have a style without the site name, some titles use the separator.</p>
			<p>Select a title separator: <?php $form->add_drop_down( 'simple_title_separator', array( 'value' => $separators, 'style' => 'font-size:14px;' ) ); ?></p>
			<p class="title-separator-group" id="custom-title-separator">Custom title separator: <?php $form->add_text_box( 'simple_title_separator_custom' ); ?></p>
		</div>
		
		<div class="titles-options-group" id="custom-titles-options-group">
			<h3>Custom Title Formats</h3>
			
			<p>The following format variables can be used to build a title:</p>
			
			<table>
				<tr>
					<th>%title%</th>
					<td><p>This is the WordPress-generated title for the specific view. For example, it will return the title of the post for posts, the category for a category view, or the Blog Title if the view is the home page / static front page.</p></td>
				</tr>
				<tr>
					<th>%blog-title%</th>
					<td><p>The Blog Title as found on the <a href="<?php echo admin_url( 'options-general.php' ); ?>">Settings > General page</a>.</p></td>
				</tr>
				<tr>
					<th>%search-terms%</th>
					<td><p>The search terms used if the current page is a search results page (valid only with search results).</p></td>
				</tr>
				<tr>
					<th>%category%</th>
					<td><p>The content's first category alphabetically (valid only with post content).</p></td>
				</tr>
				<tr>
					<th>%categories%</th>
					<td><p>The content's categories separated by commas. (valid only with post content).</p></td>
				</tr>
				<tr>
					<th>%date%</th>
					<td><p>The content's publishing date in MM-DD-YYYY format (valid only with post, page, etc content).</p></td>
				</tr>
				<tr>
					<th>%alt-date%</th>
					<td><p>The content's publishing date in DD-MM-YYYY format (valid only with post, page, etc content).</p></td>
				</tr>
				<tr>
					<th>%author%</th>
					<td><p>The author (valid only with post, page, etc content).</p></td>
				</tr>
				<tr>
					<th>%page-number%</th>
					<td><p>The current page number. This is useful for building the Page Number Listing option.</p></td>
				</tr>
				<tr>
					<th>%page-number-listing%</th>
					<td><p>This adds the title portion that is generated off of the Page Number Listing option below.</p></td>
				</tr>
				<tr>
					<th>%sep%</th>
					<td><p>This adds the Separator defined below.</p></td>
				</tr>
			</table>
			
			
			<h4>Separator</h4>
			
			<p>The separator is used to help generate some of the format variables. For example, when the %title% variable is generated, it sometimes produces multiple fields that must be separated. The separator below will be used to add that separation.</p>
			
			<div class="title-views-options clearfix">
				<div class="view">
					<span><label for="separator_format">Separator</label></span>
					<div>
						<?php $form->add_text_box( 'separator_format' ); ?>
						<span class="option-description information">Ex: <code>::</code></span>
					</div>
				</div>
			</div>
			
			
			<h4>Page Number Listing</h4>
			
			<div class="information">
				<p>Some site views can have more than one page. It is helpful to indicate when the user is on a page after the first one by adding the page number to the title. The following option defines the format of this title addition.</p>
				<p>By default, this will be automatically added after <code>%title%</code> or <code>%search-terms%</code> if they are present in the title format. Otherwise, it is added to the end of the title.</p>
				<p>You can also explicitly indicate the position of the Page Number Listing in any of the other title formats by adding <code>%page-number-listing%</code> which will add in the Page Number Listing only if the page number if greater than 1.</p>
			</div>
			
			<div class="title-views-options clearfix">
				<div class="view">
					<span><label for="page_number_listing_format">Page Number Listing</label></span>
					<div>
						<?php $form->add_text_box( 'page_number_listing_format' ); ?>
						<span class="option-description information">Ex: <code>&nbsp;%sep% Page %page-number%</code></span>
					</div>
				</div>
			</div>
			
			
			<h4>View-Specific Title Formats</h4>
			
			<p>The following options control the title formats for all the views on the site. Use the formatting variables given above to customize the options.</p>
			
			<?php $this->_add_title_views_options( $form ); ?>
		</div>
	</div>
<?php
			
		}
		
		function meta_box_description() {
			$form = $this->_form;
			
			$description_settings = array(
				''			=> 'Disabled - Builder will not provide any description meta tags',
				'default'	=> 'Default - Builder will provide descriptions if they are not already provided by a plugin',
				'forced'	=> 'Forced - Builder will always remove any plugin-set descriptions and use its own',
			);
			
?>
	<div class="information">
		<p>The description meta tag provides a brief summary of the current page's content. Most search engines use the description for some or all of the information listed below a result's link.</p>
		<p><img style="border:1px solid #CCC;" src="<?php echo $this->_parent->_plugin_url; ?>/images/ithemes-search-result.jpg" alt="iThemes Search Result" /></p>
		<p>By default, Builder will allow plugins to provide the description, and will only provide a description if a plugin has not already provided one. This behavior can be changed to either disable Builder's handling of the description tag or to force Builder to always use the description tag it generates.</p>
	</div>
	
	<p>
		Description Setting:
		<?php $form->add_drop_down( 'description_setting', $description_settings ); ?>
	</p>
	
	<div id="description-options">
		<h3>Pages and Posts</h3>
		
		<div class="information">
			<p>Builder is able to automatically generate descriptions for pages and posts; however, creating hand-written descriptions can provide much better results and will often look more natural.</p>
			<p>Customize these features by enabling/disabling automatic description generation and the ability to provide custom descriptions with the options below.</p>
		</div>
		
		<p>Select which content types Builder can automatically generate descriptions for.</p>
		<div class="left-indent-options">
			<div><?php $form->add_check_box( 'enable_automatic_descriptions_pages' ); ?><label for="enable_automatic_descriptions_pages"> Pages</label></div>
			<div><?php $form->add_check_box( 'enable_automatic_descriptions_posts' ); ?><label for='enable_automatic_descriptions_posts'> Posts</label></div>
		</div>
		
		<p>A custom description entry box can be added to the edit screens of these content types. When the option is enabled, a text area labeled "Description" will be added to the "SEO Options" box in that content type's edit screen.</p>
		<div class="left-indent-options">
			<div><?php $form->add_check_box( 'enable_custom_descriptions_pages' ); ?><label for='enable_custom_descriptions_pages'> Pages</label></div>
			<div><?php $form->add_check_box( 'enable_custom_descriptions_posts' ); ?><label for='enable_custom_descriptions_posts'> Posts</label></div>
		</div>
		
		
		<div class="information">
			<h3>Categories, Tags, Custom Taxonomy Types, and Media</h3>
			
			<p>WordPress has a built-in interface for supplying descriptions for these types of content. Simply go to the editor for the specific type you wish to modify (<a href="<?php echo admin_url( 'categories.php' ); ?>" title="Categories Editor">Posts > Categories</a>, <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=post_tag' ); ?>" title="Tags Editor">Posts > Post Tags</a>, <a href="<?php echo admin_url( 'upload.php' ); ?>" title="Media Library">Media</a>, etc), edit the entry you wish to add a description to, input a Description and save your changes.</p>
			<p>Note that disabling Builder's ability to manage the description meta tag or disabling Builder's SEO will prevent these descriptions from being used unless a plugin provides that support.</p>
		</div>
		
		
		<h3>Other Descriptions</h3>
		
		<div class="information">
			<p>By default, Builder will use your <a href="<?php echo admin_url( 'options-general.php' ); ?>">Tagline</a> for views that do not have content-specific descriptions. A different description can be supplied below.</p>
		</div>
		
		<p><?php $form->add_text_area( 'other_views_description' ); ?></p>
	</div>
<?php
			
		}
		
		function meta_box_robots() {
			$form = $this->_form;
			
			$robots_settings = array(
				''			=> 'Disabled - Builder will not provide any robots meta tags',
				'default'	=> 'Default - Builder will provide robots meta tags if they are not already provided by a plugin',
				'forced'	=> 'Forced - Builder will always remove any plugin-set robots meta tags and use its own',
			);
			
			$indexing_settings = array(
				'default'	=> 'Default - "index, follow" for unique content views and "noindex, follow" for the other views',
				'custom'	=> 'Custom - Each view can be customized for specific indexing requirements',
			);
			
?>
	<div class="information">
		<p>The robots meta tag gives information to search engines about how to index the content of the site. When used properly, this meta tag can help prevent issues such as duplicate content indexing. Beyond just indexing, some search engines respect additional options that tailor how their search result descriptions are created.</p>
		<p>Note that all of these options should be thought of as suggestions for search engines. In addition, changing these settings will not result in immediate changes on search engines as they will need to index the content again to see the changes.</p>
		<p>By default, Builder will allow plugins to provide the robots meta tag, and will only provide the tag if a plugin has not already provided one. This behavior can be changed to either disable Builder's handling of the robots meta tag or to force Builder to always use the robots meta tag it generates.</p>
	</div>
	
	<p>
		Robots Setting:
		<?php $form->add_drop_down( 'robots_setting', $robots_settings ); ?>
	</p>
	
	<div id="robots-options">
		<h3>Search Engine Rules</h3>
		
		<div class="information">
			<p>Most SEO techniques are merely suggestions to search engines; however, there are a number of agreed upon options that most major search engines will respect. The following list of options fit into this category.</p>
			<p>Note that making changes to these settings will not result in immediate results as the search engines will have to process the content again before it can see the new options.</p>
		</div>
		
		<div class="left-indent-options">
			<p>
				<?php $form->add_check_box( 'enable_dmoz' ); ?>
				<label for="enable_dmoz">Allow search engines to use the <a href="http://www.dmoz.org/">Open Directory Project</a> to generate search result descriptions</label>
				<span class="option-description information">This is disabled by default. Enabling this option removes the noodp robots entry and is not recommended.</span>
			</p>
			<p>
				<?php $form->add_check_box( 'enable_yahoo_directory' ); ?>
				<label for="enable_yahoo_directory">Allow <a href="http://yahoo.com/">Yahoo!</a> to use its directory to generate search result titles descriptions</label>
				<span class="option-description information">This is disabled by default. Enabling this option removes the noydir robots entry and is not recommended.</span>
			</p>
			<p>
				<?php $form->add_check_box( 'enable_archive' ); ?>
				<label for="enable_archive">Allow search engines to cache / archive content</label>
				<span class="option-description information">This is enabled by default. Disabling this option adds the noarchive robots entry. In the search example below, the "Cached" link provides a cached version of the page.</span>
			</p>
			<p>
				<?php $form->add_check_box( 'enable_snippet' ); ?>
				<label for="enable_snippet">Allow Google to add descriptive details (the snippet) below the search title</label>
				<span class="option-description information">This is enabled by default. Disabling this option adds the nosnippet robots entry. Looking at the search example below, the snippet is everything below the title.</span>
			</p>
			<p class="information">
				<img style="border:1px solid #CCC;" src="<?php echo $this->_parent->_plugin_url; ?>/images/ithemes-search-result.jpg" alt="iThemes Search Result" />
			</p>
		</div>
		
		
		<h3>Site Indexing Settings</h3>
		
		<div class="information">
			<p>The most powerful feature of the robots meta tag is that it can tell search engines how to spider and index site content. There are four attributes that can used: <code>index</code>, <code>noindex</code>, <code>follow</code>, and <code>nofollow</code>.</p>
			<p>The <code>index</code> and <code>noindex</code> attributes tell search engines whether or not the current page should be indexed. In other words, if a page is listed as <code>index</code>, then search engines should process the content of the page so that search traffic can be sent to it. If <code>noindex</code> is used, then the search engines should not send search traffic to the page.</p>
			<p>The <code>follow</code> and <code>nofollow</code> attributes tell search engines whether the links on the current page should be visited for possible indexing.</p>
			<p>The following table explains what the possible combinations mean.</p>
		</div>
		
		<table class="information robots-attribute-descriptions">
			<tr>
				<th>index, follow</th>
				<td>
					<p>This is the default option and tells search engines to index (make the content available in search results) the current page and follow the links to see if they need to be indexed. This should be used everywhere you have unique content.</p>
				</td>
			</tr>
			<tr>
				<th>noindex, follow</th>
				<td>
					<p>This tells search engines to not index the content but that the links on the current page should be followed to see if they need to be indexed. This should be used where content is duplicated such as archive listings and search results since the full content is available for indexing elsewhere on the site.</p>
				</td>
			</tr>
			<tr>
				<th>index, nofollow</th>
				<td>
					<p>This tells the search engines that the page should be indexed but that none of the links should be followed for indexing. This option should only be used if you must use it.</p>
					<p>Note that this nofollow attribute is different from the <code>rel="nofollow"</code> you may have read about in terms of PageRank shaping. This option applies to all links on the page, not just specific links.</p>
				</td>
			</tr>
			<tr>
				<th>noindex, nofollow</th>
				<td>
					<p>This tells the search engines that the page should not be indexed and its links should not be followed for possible indexing. Basically, this makes the page invisible to search engines. As with the <code>index, nofollow</code> option, this one should only be used if it absolutely must be.</p>
				</td>
			</tr>
		</table>
		
		
		<h4>Custom Indexing for Pages and Posts</h4>
		
		<p>A custom indexing entry box can be added to the edit screens of these content types. When the option is enabled, a text area labeled "Indexing" will be added to the "SEO Options" box in that content type's edit screen.</p>
		<div class="left-indent-options">
			<div><?php $form->add_check_box( 'enable_custom_robots_pages' ); ?><label for='enable_custom_robots_pages'> Pages</label></div>
			<div><?php $form->add_check_box( 'enable_custom_robots_posts' ); ?><label for='enable_custom_robots_posts'> Posts</label></div>
		</div>
		
		
		<h4>Customize Site View Settings</h4>
		
		<div class="information">
			<p>The default setting for Builder is to use <code>index, follow</code> for all unique content (posts, pages, attachments, plugin pages, and custom post types). All other views (home page, archives, search results, etc) use <code>noindex, follow</code> to prevent duplicate content issues.</p>
			<p>Select whether this site should use the default or custom indexing options.</p>
		</div>
		
		<p>Indexing Setting: <?php $form->add_drop_down( 'indexing_setting', $indexing_settings ); ?></p>
		
		<div id="indexing-options">
			<?php $this->_add_robots_views_options( $form ); ?>
		</div>
	</div>
<?php
			
		}
		
		function meta_box_keywords() {
			$form = $this->_form;
			
			$keywords_settings = array(
				''			=> 'Disabled - Builder will not provide any keywords meta tags',
				'default'	=> 'Default - Builder will provide keywords if they are not already provided by a plugin',
				'forced'	=> 'Forced - Builder will always remove any plugin-set keywords and use its own',
			);
			
?>
	<div class="information">
		<p>The keywords meta tag is meant to list a few words that are related to the current page's content. However, due to massive abuse of this attribute and the rise of Google's content-driven search results, this tag has lost most of its value.</p>
		<p>Google has <a href="http://googlewebmastercentral.blogspot.com/2009/09/google-does-not-use-keywords-meta-tag.html" title="Google does not use the keywords meta tag in web ranking">publically declared</a> that their search engine has not used the keywords meta tag for years and is not likely to change that policy in the future. However, as <a href="http://searchengineland.com/sorry-yahoo-you-do-index-the-meta-keywords-tag-27743" title="Sorry, Yahoo, You Do Index The Meta Keywords Tag">this article</a> points out, both Bing and Yahoo! seem to use the keywords meta information for at least some part of the search indexing.</p>
		<p>While there is not a concensus on whether use of keywords can improve rankings, there are a set of rules you should follow if you decide to make use of keywords. First, do not go overboard. Keep the list of words reasonable (don't add every word you can think of). Second, make sure the keywords actually relate to the content. Third, using the keywords you want to get results for in the content is much more valuable than in the keywords, so write good content that uses the words you want results for.</p>
		<p>By default, Builder will allow plugins to provide the keywords, and will only provide keywords if a plugin has not already provided one. This behavior can be changed to either disable Builder's handling of the keywords meta tag or to force Builder to always use the keywords meta tag it generates.</p>
	</div>
	
	<p>
		Keywords Setting:
		<?php $form->add_drop_down( 'keywords_setting', $keywords_settings ); ?>
	</p>
	
	<div id="keywords-options">
		<h3>Custom Keywords for Pages and Posts</h3>
		
		<p>A custom keywords entry box can be added to the edit screens of pages and posts. When the option is enabled, a text area labeled "Keywords" will be added to the "SEO Options" box in that content type's edit screen.</p>
		<div class="left-indent-options">
			<div><?php $form->add_check_box( 'enable_custom_keywords_pages' ); ?><label for='enable_custom_keywords_pages'> Pages</label></div>
			<div><?php $form->add_check_box( 'enable_custom_keywords_posts' ); ?><label for='enable_custom_keywords_posts'> Posts</label></div>
		</div>
		
		
		<h3>Post Keywords</h3>
		
		<p>By default, Builder creates a set of keywords for posts by combining the tags and categories, removing any duplicates. You can change that behavior with the following option.</p>
		<p>
			<label for="post_keywords">Automatically generate post keywords using the following: </label>
			<?php
				$post_keywords_options = array(
					'categories_and_tags'	=> 'Categories and Tags (default)',
					'categories'			=> 'Categories',
					'tags'					=> 'Tags',
					''						=> 'No Automatic Post Keywords',
				);
				
				$form->add_drop_down( 'post_keywords', $post_keywords_options );
			?>
		</p>
		
		
		<h3>Home Keywords</h3>
		
		<p>Enter the desired keywords for the home page. The keywords should be a list of words or word groups separated by commas, such as: fishing, trout, Montana, river, guided tours.</p>
		<p>
			<?php $form->add_text_area( 'home_keywords' ); ?>
		</p>
	</div>
<?php
			
		}
		
		function meta_box_best_practices() {
			
?>
	<p>This information is by no means comprehensive. If you want completely, robust SEO advice and guidance for your site, you should contact an SEO expert or agency for consultation.</p>
	<p>The aim of this section is to provide a set of guidelines that you can use to better use these SEO features and create better content.</p>
	
	<h4>Keywords</h4>
	
	<p>Keywords can refer to two different concepts: a list of words in the keywords meta tag or words that are repeated in the content to establish it as a key topic. This section explores the second concept and will help establish one of the most powerful methods for generating search traffic.</p>
	<p>If you want to use a search engine to find content, you first translate what you want to find into a set of keywords, phrases, or specific strings. For example, if you want to find information about SEO with WordPress, you might search for <code>seo wordpress</code> or <code>search engine optimization for wordpress</code>. If you want to find information on a specific error that you just encountered when modifying PHP, you would probably search for that specific string, such as <code></code>.
	
	<h4>Titles</h4>
	<p>Using good titles is very important as it helps your readers better understand the value they will get from the content. This being the case, they are also very important to search engines. Creating a short, noteworthy title that uses words relevant to the content can really improve your search results and readership.</p>
	<p>
<?php
			
		}
		
		function meta_box_info() {
			
?>
	<p>Here is a list of useful links.</p>
<?php
			
		}
		
		function _add_title_views_options( $form ) {
			$views = builder_seo_get_views_data();
			
			$current_level = 0;
			$override_stack = array();
			
			echo "<div class='title-views-options'>\n";
			
			foreach ( (array) $views as $id => $view ) {
				if ( empty( $view['title_default'] ) )
					continue;
				
				if ( empty( $view['overrides'] ) ) {
					while ( ! empty( $override_stack ) ) {
						array_pop( $override_stack );
						$current_level = 0;
						
						echo "</div>\n";
					}
				}
				else if ( $view['overrides'] !== end( $override_stack ) ) {
					if ( in_array( $view['overrides'], $override_stack ) ) {
						while ( $view['overrides'] !== end( $override_stack ) ) {
							array_pop( $override_stack );
							$current_level--;
							
							echo "</div>\n";
						}
					}
					else {
						array_push( $override_stack, $view['overrides'] );
						$current_level++;
						
						echo "<div class='overrides level-$current_level' id='title-overrides-{$view['overrides']}'>\n";
					}
				}
				
				
				$input = $form->get_text_box( "title_views_{$view['priority']}_{$view['function']}" );
				
				foreach ( (array) $views as $override_view ) {
					if ( ! empty( $override_view['overrides'] ) && ( $id == $override_view['overrides'] ) ) {
						$input .= $form->get_check_box( "customize_title_sub_views_$id", array ( 'class' => 'show-sub-views' ) );
						$input .= "<label for='customize_title_sub_views_$id'> Customize subviews</label>";
						
						break;
					}
				}
				
				echo "<div class='view clearfix' id='view-$id'>\n";
				echo "<span>{$view['description']}</span>\n";
				echo "<div>$input</div>\n";
				echo "</div>\n";
			}
			
			while ( ! empty( $override_stack ) ) {
				array_pop( $override_stack );
				$current_level = 0;
				
				echo "</div>\n";
			}
			
			echo "</div>\n";
		}
		
		function _add_robots_views_options( $form ) {
			$robots_views = builder_seo_get_views_data();
			
			$current_level = 0;
			$override_stack = array();
			
			foreach ( (array) $robots_views as $id => $view ) {
				if ( empty( $view['overrides'] ) ) {
					while ( ! empty( $override_stack ) ) {
						array_pop( $override_stack );
						$current_level = 0;
						
						echo "</div>\n";
					}
				}
				else if ( $view['overrides'] !== end( $override_stack ) ) {
					if ( in_array( $view['overrides'], $override_stack ) ) {
						while ( $view['overrides'] !== end( $override_stack ) ) {
							array_pop( $override_stack );
							$current_level--;
							
							echo "</div>\n";
						}
					}
					else {
						array_push( $override_stack, $view['overrides'] );
						$current_level++;
						
						echo "<div class='overrides level-$current_level' id='robots-overrides-{$view['overrides']}'>\n";
					}
				}
				
				
				$options = array();
				
				if ( ! empty( $view['overrides'] ) )
					$options['inherit'] = 'Use parent view\'s setting';
				
				$options['index,follow']		= 'index, follow';
				$options['noindex,follow']		= 'noindex, follow';
				$options['index,nofollow']		= 'index, nofollow';
				$options['noindex,nofollow']	= 'noindex, nofollow';
				
				$options[$view['robots_default']] .= ' (default)';
				
				$input = $form->get_drop_down( "robots_views_{$view['priority']}_{$view['function']}", $options );
				
				foreach ( (array) $robots_views as $override_view ) {
					if ( ! empty( $override_view['overrides'] ) && ( $id == $override_view['overrides'] ) ) {
						$input .= $form->get_check_box( "customize_robots_sub_views_$id", array ( 'class' => 'show-sub-views' ) );
						$input .= "<label for='customize_robots_sub_views_$id'> Customize subviews</label>";
						
						break;
					}
				}
				
				echo "<div class='view clearfix' id='view-$id'>\n";
				echo "<span>{$view['description']}</span>\n";
				echo "<div>$input</div>\n";
				echo "</div>\n";
			}
			
			while ( ! empty( $override_stack ) ) {
				array_pop( $override_stack );
				$current_level = 0;
				
				echo "</div>\n";
			}
		}
		
		function _get_views_data() {
			if ( isset( $this->_robots_views ) )
				return $this->_robots_views;
			
			$robots_views = array(
				'10'	=> array(
					'description'		=> 'Content',
					'function'			=> 'is_content',
					'priority'			=> '20',
					'robots_default'	=> 'index,follow',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'20'	=> array(
					'description'		=> 'Posts',
					'function'			=> 'is_post',
					'priority'			=> '5',
					'overrides'			=> '10',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'30'	=> array(
					'description'		=> 'Pages',
					'function'			=> 'is_page',
					'priority'			=> '5',
					'overrides'			=> '10',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'40'	=> array(
					'description'		=> 'Attachments',
					'function'			=> 'is_attachment',
					'priority'			=> '5',
					'overrides'			=> '10',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'50'	=> array(
					'description'		=> 'Plugin Pages',
					'function'			=> 'is_plugin_page',
					'priority'			=> '5',
					'overrides'			=> '10',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'60'	=> array(
					'description'		=> 'Custom Post Types',
					'function'			=> 'is_custom_post_type',
					'priority'			=> '5',
					'overrides'			=> '10',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'90'	=> array(
					'description'		=> 'Archives',
					'function'			=> 'is_archive',
					'priority'			=> '20',
					'robots_default'	=> 'noindex,follow',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'100'	=> array(
					'description'		=> 'Category',
					'function'			=> 'is_category',
					'priority'			=> '10',
					'overrides'			=> '90',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'110'	=> array(
					'description'		=> 'Tag',
					'function'			=> 'is_tag',
					'priority'			=> '10',
					'overrides'			=> '90',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'120'	=> array(
					'description'		=> 'Author',
					'function'			=> 'is_author',
					'priority'			=> '10',
					'overrides'			=> '90',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'130'	=> array(
					'description'		=> 'Date',
					'function'			=> 'is_date',
					'priority'			=> '10',
					'overrides'			=> '90',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'131'	=> array(
					'description'		=> 'Year',
					'function'			=> 'is_year',
					'priority'			=> '5',
					'overrides'			=> '130',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'132'	=> array(
					'description'		=> 'Month',
					'function'			=> 'is_month',
					'priority'			=> '5',
					'overrides'			=> '130',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'133'	=> array(
					'description'		=> 'Day',
					'function'			=> 'is_day',
					'priority'			=> '5',
					'overrides'			=> '130',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'134'	=> array(
					'description'		=> 'Time',
					'function'			=> 'is_time',
					'priority'			=> '5',
					'overrides'			=> '130',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'140'	=> array(
					'description'		=> 'Custom Taxonomy',
					'function'			=> 'is_tax',
					'priority'			=> '10',
					'overrides'			=> '90',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				),
				'210'	=> array(
					'description'		=> 'Home Page (blog)',
					'function'			=> 'is_home',
					'priority'			=> '20',
					'robots_default'	=> 'index,follow',
					'title_default'		=> '%blog-title%',
				),
				'220'	=> array(
					'description'		=> 'Home Page (blog) Sub-Pages',
					'function'			=> 'is_home_paged',
					'priority'			=> '0',
					'robots_default'	=> 'noindex,follow',
				),
				'230'	=> array(
					'description'		=> 'Static Front Page',
					'function'			=> 'is_front_page',
					'priority'			=> '20',
					'robots_default'	=> 'index,follow',
					'title_default'		=> '%blog-title%',
				),
				'200'	=> array(
					'description'		=> 'Search Results',
					'function'			=> 'is_search',
					'priority'			=> '20',
					'robots_default'	=> 'noindex,follow',
					'title_default'		=> 'Search for \'%search-terms%\' %sep% %blog-title%',
				),
				'250'	=> array(
					'description'		=> 'RSS Feeds',
					'function'			=> 'is_feed',
					'priority'			=> '0',
					'robots_default'	=> 'noindex,follow',
				),
				'240'	=> array(
					'description'		=> 'Admin Pages',
					'function'			=> 'is_admin',
					'priority'			=> '0',
					'robots_default'	=> 'noindex,follow',
				),
			);
			
			
			global $wp_taxonomies;
			$taxonomies = array_keys( $wp_taxonomies );
			sort( $taxonomies );
			
			$count = 1;
			
			foreach ( (array) $taxonomies as $taxonomy ) {
				if ( ! in_array( $taxonomy, array( 'category', 'post_tag', 'link_category' ) ) ) {
					$robots_views[140 + $count] = array(
						'description'		=> $wp_taxonomies[$taxonomy]->label,
						'function'			=> "is_tax|$taxonomy",
						'priority'			=> '0',
						'overrides'			=> '140',
						'robots_default'	=> 'inherit',
						'title_default'		=> '%title% %sep% %blog-title%',
					);
					
					$count ++;
				}
			}
			
			
			$post_types = get_post_types( array(), 'objects' );
			
			$count = 1;
			
			foreach ( (array) $post_types as $post_type ) {
				if ( in_array( $post_type->name, array( 'post', 'page', 'attachment', 'revision' ) ) )
					continue;
				
				$name = $post_type->name;
				
				if ( ! empty( $post_type->label ) )
					$label = $post_type->label;
				else
					$label = ucfirst( $name );
				
				$robots_views[60 + $count] = array(
					'description'		=> $label,
					'function'			=> "is_custom_post_type|$name",
					'priority'			=> '0',
					'overrides'			=> '60',
					'robots_default'	=> 'inherit',
					'title_default'		=> '%title% %sep% %blog-title%',
				);
				
				$count++;
			}
			
			
			ksort( $robots_views );
			
			$this->_robots_views = $robots_views;
			
			
			return $robots_views;
		}
		
		
		// Helper Functions //////////////////////////////////////
		
	}
}

?>
