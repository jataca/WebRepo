<?php
/*
Plugin Name: ClickBump SEO!
Plugin URI: http://clickbump.com
Description: ClickBump SEO: Comprehensive Post/Page SEO Analysis & Suggestion Engine. Click "Settings" link below to customize options.
Author: Scott Blanchard
Version: 3.2.2
Author URI: http://clickbump.com
*/
add_action('save_post', 'rseo_save_rseo_keyword',10,2);
add_filter('manage_posts_columns', 'rseo_add_seo_columns', 10, 2);
add_action('manage_posts_custom_column', 'rseo_display_seo_columns', 10, 2);
add_filter('manage_pages_columns', 'rseo_add_seo_columns_pages', 10, 2);
add_action('manage_pages_custom_column', 'rseo_display_seo_columns', 10, 2);
add_action('admin_head', 'rseo_customize_css');
add_action('admin_menu', 'rseo_post_options_box');
$rseo = get_option('seo_settings');
if(isset($rseo['no_cat_base'])){
	add_filter('category_rewrite_rules', 'rseo_category_base_rewrite_rules');
	add_filter('query_vars', 'rseo_category_base_query_vars');
	add_filter('request', 'rseo_category_base_request');
	add_filter('category_link', 'rseo_category_base',1000,2);
	add_action('created_category','rseo_category_base_refresh_rules');
	add_action('edited_category','rseo_category_base_refresh_rules');
	add_action('delete_category','rseo_category_base_refresh_rules');
}
	
function rseo_post_options_box() {
	if ( function_exists('add_meta_box') ) { 
		add_meta_box('rock-seo', __('ClickBump SEO!'), 'rseo_rock_seo', 'post', 'side', 'high');
		add_meta_box('rock-seo', __('ClickBump SEO!'), 'rseo_rock_seo', 'page', 'side', 'high'); 
		}		
	$rseo_dir = plugins_url('/img', __FILE__);
	add_options_page( 'SEO! Settings', 'SEO! Settings', 'manage_options', 'clickbump-seo-admin.php', 'seo_settings_admin', $rseo_dir.'/favicon.png', 'top');
	register_setting( 'seo_settings_options', 'seo_settings', 'seo_settings_validate' );
}

/*category base*/
function rseo_category_base_refresh_rules() {wp_cache_flush();global $wp_rewrite;$ce4_permalinks = get_option('permalink_structure');$wp_rewrite->set_permalink_structure($ce4_permalinks);$wp_rewrite->flush_rules();}
function rseo_category_base_deactivate() {remove_filter('category_rewrite_rules', 'rseo_category_base_rewrite_rules');rseo_category_base_refresh_rules();}
function rseo_category_base($catlink, $category_id) {$category = &get_category( $category_id );if ( is_wp_error( $category ) )return $category;$category_nicename = $category->slug;if ( $category->parent == $category_id )$category->parent = 0;elseif ($category->parent != 0 )$category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;$catlink = trailingslashit(get_option( 'home' )) . user_trailingslashit( $category_nicename, 'category' );return $catlink;}
function rseo_category_base_rewrite_rules($category_rewrite) {$category_rewrite=array();$categories=get_categories(array('hide_empty'=>false));foreach($categories as $category) {$category_nicename = $category->slug;if ( $category->parent == $category->cat_ID )$category->parent = 0;elseif ($category->parent != 0 )$category_nicename = get_category_parents( $category->parent, false, '/', true ) . $category_nicename;$category_rewrite['('.$category_nicename.')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';$category_rewrite['('.$category_nicename.')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';$category_rewrite['('.$category_nicename.')/?$'] = 'index.php?category_name=$matches[1]';}global $wp_rewrite;$old_base = $wp_rewrite->get_category_permastruct();$old_base = str_replace( '%category%', '(.+)', $old_base );$old_base = trim($old_base, '/');$category_rewrite[$old_base.'$'] = 'index.php?category_redirect=$matches[1]';return $category_rewrite;}
function rseo_category_base_query_vars($public_query_vars) {$public_query_vars[] = 'category_redirect';return $public_query_vars;}
function rseo_category_base_request($query_vars) {if(isset($query_vars['category_redirect'])) {$catlink = trailingslashit(get_option( 'home' )) . user_trailingslashit( $query_vars['category_redirect'], 'category' );status_header(301);header("Location: $catlink");exit();}return $query_vars;}
function seo_settings_validate($input) {if($input['no_cat_base']==0) {rseo_category_base_deactivate();}global $wp_rewrite;$wp_rewrite->flush_rules();return $input;}
function seo_settings_admin(){global $wp_rewrite;$wp_rewrite->flush_rules();include_once dirname(__FILE__) . '/clickbump-seo-admin.php';}
define( 'RSEO_BASENAME', plugin_basename( __FILE__ ) );define( 'RSEO_BASEFOLDER', plugin_basename( dirname( __FILE__ ) ) );define( 'RSEO_FILENAME', str_replace( RSEO_BASEFOLDER.'/', '', plugin_basename(__FILE__) ) );
function rseo_filter_plugin_meta($links, $file) {if ( $file == RSEO_BASENAME ) {array_unshift($links,sprintf( '<a href="options-general.php?page=clickbump-seo-admin.php">Settings</a>', RSEO_FILENAME, __('Settings') ));}return $links;}global $wp_version;if ( version_compare( $wp_version, '2.8alpha', '>' ) )add_filter( 'plugin_row_meta', 'rseo_filter_plugin_meta', 10, 2 );add_filter( 'plugin_action_links', 'rseo_filter_plugin_meta', 10, 2 );
if(isset($rseo['nofollow']) || isset($rseo['nofollow_folder']) && $rseo['nofollow_folder'] !==''){add_filter('wp_insert_post_data', 'save_rseo_nofollow' );}
function save_rseo_nofollow($content) {$content["post_content"] = preg_replace_callback('~<(a[^>]+)>~isU', "rseo_replace_nofollow", $content["post_content"]);return $content;}
function rseo_replace_nofollow($match) {global $rseo;list($original, $tag) = $match;$my_nofollow = $rseo['nofollow'];$my_folder =  $rseo['nofollow_folder'];$blog_url = get_bloginfo('url');if (strpos($tag, "nofollow") || (!$my_nofollow && !strpos($tag, $blog_url)) ) {return $original;}elseif (strpos($tag, $blog_url) && (!$my_folder || !strpos($tag, $my_folder))) {return $original;} else { return "<$tag rel=\"nofollow\">";}}
function rseo_customize_css(){$rseo_dir = plugins_url('/img', __FILE__);?>
<style type="text/css">
.seo_score.column-seo_score {padding-top:7px !important;}
.column-seo_score span {padding:5px; display:inline-block; -moz-border-radius:10px;border-radius:10px;background:#ddd; -webkit-box-shadow: 1px 2px #fff, -1px -1px #777; padding:6px 12px; margin-top:2px;font-weight:normal;text-shadow:0 1px #fff; color:#333;}
.column-seo_score span.seo-low {background:#ddd url(<?php echo $rseo_dir ?>/alert-icon.png) no-repeat 4px center; padding:6px 0 6px 28px;font-weight:bold;width:25px;}
.column-seo_score span.seo-rocks {background:#ddd url(<?php echo $rseo_dir ?>/thumbs-up.png) no-repeat 5px center; padding:6px 12px 6px 28px;font-weight:bold;}
</style>
<?php
}


function rseo_add_seo_columns($posts_columns) {
	$posts_columns['seo_keyword'] = 'SEO! Keyword';
	$posts_columns['seo_score'] = 'SEO! Score';
	return $posts_columns;
}

function rseo_add_seo_columns_pages($pages_columns) {
	$pages_columns['seo_keyword'] = 'SEO! Keyword';
	$pages_columns['seo_score'] = 'SEO! Score';
	return $pages_columns;
}

function rseo_display_seo_columns($column_name, $post_id) {
	if ('seo_keyword' == $column_name) {
		if(get_post_meta($post_id, '_rseo_keyword', true)){
			echo get_post_meta($post_id, '_rseo_keyword', true);
		} else {
			echo "(Not Set)";
		}
	}
	if ('seo_score' == $column_name) {
		if(get_post_meta($post_id, '_rseo_rockScore', true) < 0 || get_post_meta($post_id, '_rseo_rockScore', true) =="") 			{
			echo "(Not Set)";
		} else {
			$theScore = get_post_meta($post_id, '_rseo_rockScore', true);
			if($theScore <= 29) {
				$className = " class='seo-low'";
			} elseif($theScore >= 30 && $theScore <= 69) {
				$className = " class='seo-med'";
			} elseif($theScore >= 70 && $theScore <= 89) {
				$className = " class='seo-high'";
			} elseif($theScore >= 90) {
				$className = " class='seo-rocks'";
			}
			echo "<span".$className.">".get_post_meta($post_id, '_rseo_rockScore', true)."</span>";
		}       
	}
}


function rseo_sanitizeOLD($s) {
	$result = preg_replace("/[^a-zA-Z0-9]+/", "", $s);
	return $result;
}

function rseo_sanitize($s) {
    $result = preg_replace("/[^a-zA-Z0-9'-]+/", "", html_entity_decode($s, ENT_QUOTES));
//    $result = preg_replace("/[^a-zA-Z0-9]+/", "", html_entity_decode($s, ENT_QUOTES));
    return $result;
}

function rseo_sanitize2($s) {
    $result = preg_replace("/[^a-zA-Z0-9'-]+/", " ", html_entity_decode($s, ENT_QUOTES));
//  $result = preg_replace("/[^a-zA-Z0-9]+/", " ", html_entity_decode($s, ENT_QUOTES));
    return $result;
}

function rseo_getKeyword($post) {
	$myKeyword = get_post_meta($post->ID, '_rseo_keyword', true);
	if($myKeyword == "") $myKeyword = $post->post_title;
	$myKeyword = rseo_sanitize2($myKeyword);
	return " ".$myKeyword;
}

function rseo_keyword_density($post) {
	$word_count =  rseo_word_count($post);
	$keyword_count = rseo_keyword_count($post);
	$density = ($keyword_count / $word_count) * 100;
	$density = number_format($density, 1);
	return $density;
}

function rseo_keyword_count($post) {
	$text = strip_tags($post->post_content);
	$keyword = trim(rseo_getKeyword($post));
	$keyword = rseo_sanitize2($keyword);
	$keyword_count = preg_match_all("#{$keyword}#si", $text, $matches);
	return $keyword_count;
}

function rseo_word_count($post) {
	$text = strip_tags($post->post_content);
	$word_count = explode(' ', $text);
	$word_count = count($word_count);
	return $word_count;
}

function rseo_get_kw_first_sentence($post) {
	$theContent = rseo_sanitize_string( strip_tags(strtolower($post->post_content)) );
	$theKeyword = rseo_sanitize_string( trim(strtolower(rseo_getKeyword($post))) );
	$theKeyword = rseo_sanitize2($theKeyword);
    $thePiecesByKeyword = rseo_get_chunk_keyword($theKeyword,$theContent);
    if (count($thePiecesByKeyword) > 0) {
		$myPieceIndex = $thePiecesByKeyword[0];
		if (substr_count($myPieceIndex,'.') > 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	return FALSE;
}

function rseo_get_chunk_keyword($theKeyword, $theContent) {
	if (!rseo_get_kw_in_content($theKeyword,$theContent)) {
		return array();
	}
	
	$myPieceReturn = preg_split('/\\b' . $theKeyword . '\\b/i', $theContent);
	return $myPieceReturn;
}
        
function rseo_get_kw_in_content($theKeyword, $theContent) {
	$theKeyword = preg_quote($theKeyword, '/');
	return preg_match('/\\b' . $theKeyword . '\\b/i', $theContent);
}

function rseo_get_kw_last_sentence($post) {
	$theContent = rseo_sanitize_string( strip_tags(strtolower($post->post_content)) );
	$theKeyword = rseo_sanitize_string( trim(strtolower(rseo_getKeyword($post))) );
	$theKeyword = rseo_sanitize2($theKeyword);
    $needle = '/' . $theKeyword . '[^.!?]*[.!?][^.!?]*$/';
    $haystack = strip_tags(strtolower($theContent));
    return preg_match($needle, $haystack);
}

function rseo_get_has_internal_link($thePost) {
	$theContent = $thePost->post_content;
	$myVar1 = array();
	preg_match_all('/<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU',$theContent,$myVar1);
	$myVar2 = 0;
	foreach ($myVar1[1] as $myVar3) {
		$myVar4 = $myVar1[2][$myVar2];
		$myVar5 = FALSE;
		$theSiteURL = get_bloginfo('wpurl');
		$theSiteURLwithoutWWW = str_replace('http://www.','',$myVar3);
		$theSiteURLwithoutWWW = str_replace('http://','',$theSiteURLwithoutWWW);
		$theSiteURLwithoutWWW2 = str_replace('http://www.','',$theSiteURL);
		$theSiteURLwithoutWWW2 = str_replace('http://','',$theSiteURLwithoutWWW2);
		if (strpos($myVar3,'http://')!==0 || strpos($theSiteURLwithoutWWW,$theSiteURLwithoutWWW2)===0) {
			return TRUE;
		}
		$myVar2++;
	}
	return FALSE;
}

function rseo_get_kw_title($post) {
	if($post->post_title == "") {
		return false;
	}
	$haystack = rseo_sanitize($post->post_title);
	$needle = rseo_sanitize(rseo_getKeyword($post));
	$pos = stripos($haystack, $needle);
	if ($pos !== false) {
		return true;
	}
}

function rseo_save_rseo_keyword($postID, $post) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $postID;
	} else {
		global $rseo;
		if($parent_id = wp_is_post_revision($postID)) {
			$postID = $parent_id;$post = get_post($postID);
		}
		$rockScore = 0;
		$demerit = 11;
		if(isset($rseo['kwd_low']) && $rseo['kwd_low'] !=='') $rseo_densityLow = $rseo['kwd_low']; else $rseo_densityLow = 1.0;
		if(isset($rseo['kwd_high']) && $rseo['kwd_high'] !=='') $rseo_densityHigh = $rseo['kwd_high']; else $rseo_densityHigh = 4.0;
		if(rseo_keyword_density($post) >= $rseo_densityLow && rseo_keyword_density($post) <= $rseo_densityHigh) {$rockScore+=10;$demerit-=1;}

		if(rseo_get_kw_title($post)) {
			$rockScore+=10;
		} else {
			$demerit+=1;$demerit-=1;
		}
		if($rseo['theme_h1']==1 || rseo_get_seo('h1', $post) || function_exists('ce3_admin') || function_exists('ce4_admin')) {
			$rockScore+=10;$demerit-=1;
		}
		if($rseo['theme_h2']==1 || rseo_get_seo('h2', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if($rseo['theme_h3']==1 || rseo_get_seo('h3', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(rseo_get_seo('b', $post) OR rseo_get_seo('strong', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if($rseo['theme_img']==1 || rseo_get_seo('img-alt', $post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(rseo_get_kw_first_sentence($post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(rseo_get_kw_last_sentence($post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(rseo_get_has_internal_link($post)) {
			$rockScore+=10;$demerit-=1;
		}
		if(rseo_word_count($post) >= 299 ) {
			$demerit-=1;
		} else {
			if ($rockScore > 20) {
				$rockScore-=20;
			}
		}
		$rockScore = number_format($rockScore,0);
		rseo_update_custom_meta($postID, $rockScore, '_rseo_rockScore');
		rseo_update_custom_meta($postID, $_POST['rseo_keyword'], '_rseo_keyword');
	}
}


function rseo_get_seo($check, $post) {
	switch ($check) {
		case "b": return rseo_doTheParse('b', $post);
		case "strong": return rseo_doTheParse('strong', $post);
		case "h1": return rseo_doTheParse('h1', $post);
		case "h2": return rseo_doTheParse('h2', $post);
		case "h3": return rseo_doTheParse('h3', $post);
		case "img-alt": return rseo_doTheParse('img-alt', $post);
	}
}

function rseo_doTheParse($heading, $post) {
	$content = $post->post_content;
	if($content=="" || !class_exists('DOMDocument')) {
		return false;
	}
	$keyword = rseo_sanitize_string( trim(strtolower(rseo_getKeyword($post))) );
	//JSB 1-27-2011
	$keyword = rseo_sanitize2($keyword);
	@$dom = new DOMDocument;
	@$dom->loadHTML(rseo_sanitize_string( strtolower($content) ));
	$xPath = new DOMXPath(@$dom);
	switch ($heading) {
		case "img-alt": 
			return $xPath->evaluate('boolean(//img[contains(@alt, "'.$keyword.'")])');
		default: 
			return $xPath->evaluate('boolean(/html/body//'.$heading.'[contains(.,"'.$keyword.'")])');
	}
}

function rseo_update_custom_meta($postID, $newvalue, $field_name) {
	// To create new meta
	if(!get_post_meta($postID, $field_name)) {
		add_post_meta($postID, $field_name, $newvalue);
	} else {
		// or to update existing meta
		update_post_meta($postID, $field_name, $newvalue);
	}
}

function rseo_sanitize_string( $content ) {
	$regex = '/( [\x00-\x7F] | [\xC0-\xDF][\x80-\xBF] | [\xE0-\xEF][\x80-\xBF]{2} | [\xF0-\xF7][\x80-\xBF]{3} ) | ./x';
	return preg_replace($regex, '$1', $content);
}

function rseo_rock_seo($post)
{
global $rseo;
wp_enqueue_script('jquery');
$rockScore = 0;
$demerit=11;
$rseo_dir = plugins_url('/img', __FILE__);
	?><style type='text/css'>#rseo_loader{display:none;filter:alpha(opacity=75);-moz-opacity:0.75;-khtml-opacity: 0.75;opacity: 0.75; padding:10px; position:absolute; top:0; left:0; height:1000px; width:260px; background:#fff url(<?php echo $rseo_dir ?>/loader.gif) no-repeat 170px 52px;} .rseo-button {height:24px;  position:absolute; top:47px; right:16px; border: 1px solid #8ec1da;background-color: #ddeef6; border-radius: 4px;box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;-o-box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;-webkit-box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;-moz-box-shadow: inset 0 1px 3px #fff, inset 0 -12px #ddd, 0 0 3px #8ec1da;color: #3985a8;text-shadow: 0 1px #fff;padding: 5px 10px;}#rseo_related, #rseo_related_keywords {display:none; margin:-13px -2px 10px -2px;} .rseo_related_kw p {font-style:italic; margin:2px 0 20px 0 !important;}.rseo_related_kw span {background:#ccc;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;padding:3px 4px;display:inline-block; text-align:left; margin:0 -3px 5px 0;white-space:nowrap !important;}.inside.rock-seo li {background:url(<?php echo $rseo_dir ?>/thumbs-up.png) no-repeat; padding:0 0 10px 25px; margin-left:0; color:#333;}.inside.rock-seo li.alert-on {color:#333; background:url(<?php echo $rseo_dir ?>/alert-icon.png) no-repeat;}.inside.rock-seo li.alert-off {background:none;}.inside.rock-seo li span {font-size:1.5em; font-weight:bold; color:#fff; text-shadow: 1px 1px 4px#000; background:#32b10f; background: -webkit-gradient(linear, left bottom, left top, from(#fff), to(#32b10f));background: -moz-linear-gradient(bottom,#fff,#32b10f);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#32b10f', endColorstr='#ffffff'); padding:10px 12px 13px 12px; -moz-border-radius: 10px 10px 0 0;border-radius: 10px 10px 0 0; border:1px solid #888; border-bottom:none;}.inside.rock-seo li span.alert-on {font-size:1.2em; font-weight:bold; color:#333; text-shadow:0 1px #fff; background:#fed201; background: -webkit-gradient(linear, left bottom, left top, from(#fff), to(#fed201));background: -moz-linear-gradient(bottom,  #fff,  #fed201); filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#fed201', endColorstr='#ffffff'); }.inside.rock-seo li {font-size:.95em !important;}.rockScore {border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; -moz-border-radius-topleft:0; -moz-border-radius-topright:0; border-top-right-radius:0;border-top-left-radius:0; color:#333; font-size:1.25em; font-weight:bold; padding:20px 15px 17px 10px; margin:0 -6px -2px -6px; margin:0 -12px -9px -12px; text-shadow: 0 1px #fff;white-space:nowrap;background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#cccccc));background: -moz-linear-gradient(top,  #fff,  #cccccc);filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#cccccc');}.rockScore .score {border:1px solid #888; border-top:none; font-size:1.5em; font-style:italic;color:#fff; text-shadow: -1px -1px #333; border-radius:10px; -moz-border-radius:10px; -webkit-border-radius:10px; -moz-border-radius-topleft:0; -moz-border-radius-topright:0; border-top-right-radius:0;border-top-left-radius:0;display:inline-block; margin:-20px -5px -10px 0; !important; padding:10px 10px 10px 10px; vertical-align:bottom;}.rockScore .rating {margin-left:10px; }#rock-seo {overflow:hidden;}.rockScore.red  .score { padding-left:7px; padding-right:7px; background:#fdb700; background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#fdb700));background: -moz-linear-gradient(top,  #fff,  #fdb700); filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#fdb700');}.rockScore.yellow .score {background:#dab404; background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#dab404));background: -moz-linear-gradient(top,  #fff,  #dab404); filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#dab404');}.rockScore.green .score {background:green; background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#047e00));background: -moz-linear-gradient(top,  #fff,  #047e00);filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#047e00');}.text-box {border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; -moz-border-radius-bottomleft:0; -moz-border-radius-bottomright:0; border-bottom-right-radius:0;border-bottom-left-radius:0; color:#555; font-weight:normal; padding:2px 25px 17px 30px; margin:-6px -20px 0 -20px; text-shadow: 0 1px #fff;white-space:nowrap;background: -webkit-gradient(linear, left top, left bottom, from(#e0e0e0), to(#ffffff));background: -moz-linear-gradient(top,#e0e0e0,#ffffff);filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#e0e0e0', endColorstr='#ffffff'); text-indent:-13px;} #rseo_keyword {height:32px; padding-right:80px;margin-top:5px;width:257px;margin-left:-14px;}.rseo_related_kw h4 {font-weight:normal; margin:-5px 0 7px 0 !important}.rseo_related_kw span.highlight{background:green;color:#fff;}</style>
	<?php
	echo "<div class='inside rock-seo'><div id='rseo_loader'>&nbsp;</div>";
	echo "<div class='text-box'>Primary Keyword Phrase:";
	echo "<br><input type='text' name='rseo_keyword' id='rseo_keyword' value='".trim(rseo_getKeyword($post))."'></div>";
	echo "<div id='rseo_related_keywords'>Related Keywords:</div>";
	echo "<div><input type='button' class='rseo-button' value='Get LSI' title='Get Related Keywords (Latent Semantic Indexing)' id='rseo_get_related_keywords' /></div><div id='rseo_related'></div>";
	echo "<ul>";
	$kwDensity = rseo_keyword_density($post);
	if(isset($rseo['kwd_low']) && $rseo['kwd_low'] !=='') $rseo_densityLow = $rseo['kwd_low']; else $rseo_densityLow = 1.0;
	if(isset($rseo['kwd_high']) && $rseo['kwd_high'] !=='') $rseo_densityHigh = $rseo['kwd_high']; else $rseo_densityHigh = 4.0;
	if($kwDensity < $rseo_densityLow) echo "<li class='alert-on' title='Keyword density should be between ".$rseo_densityLow."-".$rseo_densityHigh."%'><b>Low</b> Keyword Density: <span class='alert-on'>".$kwDensity. "%</span></li>";
	elseif($kwDensity > $rseo_densityHigh) echo "<li class='alert-on' title='Keyword density should be between 1-4%'><b>High</b> Keyword Density: <span class='alert-on'>".$kwDensity. "%</span></li>";
	else { $rockScore+=10;$demerit-=1; echo "<li>Keyword Density: <span>".$kwDensity."%</span></li>";}
	if(rseo_get_kw_title($post) < 1) { echo "<li class='alert-on'>SEO! Suggests: <b>Add keyword phrase</b> to post title</li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Keyword phrase in post title!</li>";}
	if($rseo['theme_h1']==1 || function_exists('ce3_admin') || function_exists('ce4_admin')){ $rseo_theme_h1_text = " found in active theme";}
	if($rseo['theme_h1']==1 || rseo_get_seo('h1', $post) || function_exists('ce3_admin') || function_exists('ce4_admin')) {$rockScore+=10;$demerit-=1; echo "<li>H1 heading with keyword phrase".$rseo_theme_h1_text."!</li>";} else { echo "<li class='alert-on'>SEO! Suggests: <b>Add an H1 heading</b> containing your keyword phrase</li>";}	
	if($rseo['theme_h2']==1 ){ $rseo_theme_h2_text = " found in active theme";}
	if($rseo['theme_h2']==1 || rseo_get_seo('h2', $post)) {$rockScore+=10;$demerit-=1; echo "<li>H2 heading with keyword phrase".$rseo_theme_h2_text."!</li>";} else { echo "<li class='alert-on'>SEO! Suggests: <b>Add an H2 heading</b> containing your keyword phrase</li>";}
	if($rseo['theme_h3']==1 ){ $rseo_theme_h3_text = " found in active theme";}
	if($rseo['theme_h3']==1 || rseo_get_seo('h3', $post)) {$rockScore+=10;$demerit-=1; echo "<li>H3 heading with keyword phrase".$rseo_theme_h3_text."!</li>";} else { echo "<li class='alert-on'>SEO! Suggests: <b>Add an H3 heading</b> containing your keyword phrase</li>";}
	if(rseo_get_seo('b', $post) OR rseo_get_seo('strong', $post)) { $rockScore+=10;$demerit-=1; echo "<li>Keyword Phrase in bold/strong tag found!</li>";} else {echo "<li class='alert-on'>SEO! Suggests: <b>highlight your primary keyword phrase with boldface or strong</b> near the top of your content</li>";}
	if($rseo['theme_img']==1 ) $rseo_theme_img_text = " found in active theme";
	if($rseo['theme_img']==1 || rseo_get_seo('img-alt', $post)) { $rockScore+=10;$demerit-=1; echo "<li>Image with keyword in alt text".$rseo_theme_img_text."!</li>";} else {echo "<li class='alert-on'>SEO! Suggests: Add an image with keyword phrase in <b>alt text</b></li>";}
	if(rseo_get_kw_first_sentence($post) < 1) { echo "<li class='alert-on'>SEO! Suggests: Add Keyword phrase to <b>first sentence</b></li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Keyword phrase in first sentence!</li>";}
	if(rseo_get_kw_last_sentence($post) < 1) { echo "<li class='alert-on'>SEO! Suggests: Add Keyword phrase to <b>last sentence</b></li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Keyword phrase in last sentence!</li>";}
	if(rseo_get_has_internal_link($post) < 1) { echo "<li class='alert-on'>SEO! Suggests: Add an <b>internal link</b> near to the top of your content.</li>";} else {$rockScore+=10;$demerit-=1; echo "<li>Internal link found!</li>";}
	if(rseo_word_count($post) < 299 ) {echo "<li class='alert-on' title='Google likes longer pages of at least 300 words'>SEO! Suggests: <b>Add</b> More Words!</li>"; if ($rockScore > 20) $rockScore-=20; } else {$demerit-=1; echo "<li>Post word count: ".rseo_word_count($post)."</li>";}
	if(!$rseo['nofollow']) { echo "<li class='alert-on'>SEO! Can automatically add rel='nofollow' to your external links. Check SEO! Settings to activate this feature</li>";} else {echo "<li>SEO! is applying nofollow to external links in this post content</li>";}

	echo "</ul>";
	$rockScore = number_format($rockScore,0);
	if($rockScore <= 29) echo "<div class='rockScore red'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>(Needs Work!)</span></div>";
	else if($rockScore >= 30 && $rockScore <= 69) echo "<div class='rockScore yellow'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>(Not Bad!)</span></div>";
	else if($rockScore >= 70 && $rockScore < 90) echo "<div class='rockScore green'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>(Sweet!)</span></div>";
	else if($rockScore >= 90) echo "<div class='rockScore green'>SEO Score: <span class='score'>".$rockScore."</span><span class='rating'>You Rock!</span></div>";
	else echo "<div class='rockScore yellow'>Temp SEO Score: <span class='score'>".$rockScore."</span></div>";
	echo "</div>";
	?>
	<script type="text/javascript">jQuery('#rseo_get_related_keywords').click(function(){
		if (jQuery('#rseo_keyword').val() == '') return false;jQuery('#rseo_loader').show();var result = '<div class="rseo_related_kw"><h4>Top Related Semantic Keywords:</h4>';jQuery.ajax({contentType: "application/json; charset=utf-8",dataType: "json",url: "http://boss.yahooapis.com/ysearch/web/v1/"+jQuery('#rseo_keyword').val()+"?appid=kWrwnCHIkY0hVfrO7LKRwQROh25X6qOP0o4yPvpCCA--&lang=en&format=json&count=50&view=keyterms&callback=?",success: function(data){if(data['ysearchresponse']['totalhits'] == 0) this.error('No results returned for this search term');var keywords = new Array();jQuery.each(data['ysearchresponse']['resultset_web'],function(i,item){if(item['keyterms']['terms']==undefined) item['keyterms']['terms'] = "" ;jQuery.each(item['keyterms']['terms'],function(i,kw){key = kw.toLowerCase();if (keywords[key] == undefined) keywords[key] = 1;else keywords[key] = (keywords[key] + 1);});});for (key in keywords){if (keywords[key] > 5) result += '<span>' + key + '</span>, ';}result += '<p style="color:green"><b>Items in green are already in your content. Great work!</b></div></div>';jQuery('#rseo_related').html( result );jQuery('#rseo_related').show();jQuery('#rseo_loader').hide();
		/* new LSI highlighting function */
		var html = jQuery('#content').html().toLowerCase();
		jQuery(".rseo_related_kw").find("span").filter(function() {
			return html.indexOf(jQuery(this).html()) != -1;
			}).each(function() {
				jQuery(this).addClass('highlight');
			});
		},error: function(errorThrown){result += '<span>&nbsp;'+errorThrown+'&nbsp;</span></div>';jQuery('#rseo_related').html( result );jQuery('#rseo_related').show();jQuery('#rseo_loader').hide();}});return false;});
	</script>
	<?php	}
register_activation_hook(__FILE__, 'rseo_post_options_box');
?>