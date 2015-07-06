<?php

/*
Written by Chris Jean for iThemes.com
Version 1.2.0

Version History
	1.0.0 - 2010-12-15 - Chris Jean
		Release ready
	1.0.1 - 2011-02-22 - Chris Jean
		Added Basic and SEO tabs using builder_add_settings_tab
	1.1.0 - 2011-06-29 - Chris Jean
		Added builder-extensions default support
	1.2.0 - 2011-08-04 - Chris Jean
		Added Favicon section
*/


if ( ! function_exists( 'builder_theme_settings_set_defaults' ) ) {
	function builder_theme_settings_set_defaults( $defaults ) {
		global $builder_theme_feature_options, $builder_analytics_options;
		
		$categories = array();
		
		if ( ! isset( $defaults['include_cats'] ) ) {
			$category_objects = get_categories();
			$category_objects = array_slice( $category_objects, 0, 8 );
			
			foreach ( (array) $category_objects as $category )
				$categories[] = $category->term_id;
		}
		
		$new_defaults = array(
			'include_pages'					=> array( 'home' ),
			'include_cats'					=> $categories,
			'javascript_code_header'		=> '',
			'javascript_code_footer'		=> '',
			'identify_widget_areas'			=> 'admin',
			'identify_widget_areas_method'	=> 'empty',
			'enable_comments_page'			=> '',
			'enable_comments_post'			=> '1',
			'comments_disabled_message'		=> 'none',
			'tag_as_keyword'				=> 'yes',
			'cat_index'						=> 'no',
			'google_analytics_enable'		=> '',
			'woopra_enable'					=> '',
		);
		
		foreach ( (array) $builder_theme_feature_options as $feature => $details )
			$new_defaults["theme_supports_$feature"] = $details['default_enabled'];
		
		foreach ( (array) $builder_analytics_options as $type => $services ) {
			foreach ( (array) $services as $service => $options ) {
				foreach ( (array) $options as $option_args ) {
					if ( is_bool( $option_args['default'] ) )
						$option_args['default'] = ( true === $option_args['default'] ) ? '1' : '';
					
					$new_defaults["{$service}_{$type}_{$option_args['name']}"] = $option_args['default'];
				}
			}
		}
		
		$defaults = ITUtility::merge_defaults( $defaults, $new_defaults );
		$defaults = apply_filters( 'builder_filter_theme_settings_defaults', $defaults );
		
		// Legacy
		$defaults = apply_filters( 'builder_filter_default_settings', $defaults );
		
		return $defaults;
	}
	add_filter( 'it_storage_get_defaults_builder-theme-settings', 'builder_theme_settings_set_defaults', 0 );
}

builder_add_settings_tab( __( 'Basic', 'it-l10n-Builder' ), 'basic', 'ITThemeSettingsTabBasic', dirname( __FILE__ ) . '/tab-basic.php' );
builder_add_settings_tab( __( 'SEO (coming soon)', 'it-l10n-Builder' ), 'seo-inactive', null, null );

builder_add_settings_editor_box( __( 'Menu Builder', 'it-l10n-Builder' ), null, array( 'var' => 'menu_builder', '_builtin' => true ) );
builder_add_settings_editor_box( __( 'Analytics and JavaScript Code', 'it-l10n-Builder' ), null, array( 'var' => 'analytics', '_builtin' => true ) );
builder_add_settings_editor_box( __( 'Favicon', 'it-l10n-Builder' ), null, array( 'var' => 'favicon', '_builtin' => true ) );
builder_add_settings_editor_box( __( 'Identify Widget Areas', 'it-l10n-Builder' ), null, array( 'var' => 'widgets', '_builtin' => true ) );
builder_add_settings_editor_box( __( 'Comments', 'it-l10n-Builder' ), null, array( 'var' => 'comments', '_builtin' => true ) );
builder_add_settings_editor_box( __( 'Theme Features', 'it-l10n-Builder' ), null, array( 'var' => 'theme_features', '_builtin' => true ) );

builder_add_theme_feature_option( 'builder-billboard', __( 'Billboard', 'it-l10n-Builder' ), __( 'Billboard (found in the DisplayBuddy > Billboard menu) provides a quick and easy way to create a set of linked images that can be used as a widget.', 'it-l10n-Builder' ) );
builder_add_theme_feature_option( 'builder-feedburner-widget', __( 'Feedburner Widget', 'it-l10n-Builder' ), sprintf( __( 'The Feedburner Widget adds a <a href="%s">Feedburner</a> subscription form.', 'it-l10n-Builder' ), 'http://feedburner.com/' ) );
builder_add_theme_feature_option( 'builder-widget-duplicate-sidebar', __( 'Duplicate Sidebar Widget', 'it-l10n-Builder' ), __( 'The Duplicate Sidebar Widget allows for easy duplication of another sidebar\'s widgets.', 'it-l10n-Builder' ) );
builder_add_theme_feature_option( 'builder-widget-widget-content', __( 'Widget Content', 'it-l10n-Builder' ), __( 'The Widget Content feature adds a new top-level menu called "Widget Content" that allows for easy creation of content that can then be added to a sidebar by using the Widget Content widget.', 'it-l10n-Builder' ) );
builder_add_theme_feature_option( 'builder-plugin-features', __( 'Plugin Features', 'it-l10n-Builder' ), __( 'Builder can provide custom coding, styling, and JavaScript to enhance specific plugins running alongside Builder. All of these enhancements can be removed by disabling this option.', 'it-l10n-Builder' ) );
builder_add_theme_feature_option( 'builder-extensions', __( 'Extensions', 'it-l10n-Builder' ), __( 'Builder\'s Extensions are like mini-themes that can be applied to Layouts or Views. This feature can be disabled if Extensions are not used so that Extensions are hidden from the interface.', 'it-l10n-Builder' ) );

/*
builder_analytics_add_setting( __( 'Send tracking information for Administrator and Editor users. Disabling this option helps prevent inflated visitor numbers due to frequent visits by Administrator and Editor users.', 'it-l10n-Builder' ), 'ignore_admin_editor', 'checkbox', '' );
builder_analytics_add_setting( __( 'Idle visitor timeout in minutes', 'it-l10n-Builder' ), 'idle_timeout', 'textbox', '4', 'woopra' );

builder_analytics_add_data_tracker( __( 'Visitor name - Uses logged in user information first followed by commenter information. Defaults to "Guest" if no information can be found.', 'it-l10n-Builder' ), 'visitor', 'builder_analytics_get_visitor_name', true, 'google_analytics' );
builder_analytics_add_data_tracker( __( 'Visitor details - Uses logged in user information first followed by commenter information. Defaults to "Guest" if no information can be found.', 'it-l10n-Builder' ), 'visitor', 'builder_analytics_get_visitor_details', true, 'woopra' );
builder_analytics_add_data_tracker( __( 'Viewed page/post author.', 'it-l10n-Builder' ), 'author', 'builder_analytics_get_author_name' );

builder_analytics_add_action_tracker( __( 'Outbound link clicks', 'it-l10n-Builder' ), 'outbound_links' );
builder_analytics_add_action_tracker( __( 'Download link clicks', 'it-l10n-Builder' ), 'download_links' );
builder_analytics_add_action_tracker( __( 'Email link clicks', 'it-l10n-Builder' ), 'email_links' );

builder_analytics_add_event_tracker( __( 'Visitor search terms', 'it-l10n-Builder' ), 'search', 'builder_analytics_get_search_terms' );
builder_analytics_add_event_tracker( __( 'Comments', 'it-l10n-Builder' ), 'comment', 'builder_analytics_get_comment' );
*/
