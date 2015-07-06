<?php

/*
Written by Chris Jean for iThemes.com
Version 0.0.1

Version History
	0.0.1 - 2010-11-13 - Chris Jean
		Initial version
*/


if ( ! function_exists( 'builder_theme_settings_seo_set_defaults' ) ) {
	function builder_theme_settings_seo_set_defaults( $defaults ) {
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
		
		return $defaults;
	}
	add_filter( 'it_storage_get_defaults_builder-theme-settings', 'builder_theme_settings_seo_set_defaults' );
}


function builder_seo_get_views_data() {
	global $builder_seo_views_views;
	
	if ( isset( $builder_seo_views_data ) )
		return $builder_seo_views_data;
	
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
	
	$builder_seo_views_data = $robots_views;
	
	
	return $robots_views;
}
