<?PHP
/*
  Plugin Name: Lightbox Evolution
  Plugin URI: http://codecanyon.net/item/lightbox-evolution-for-wordpress/119478
  Description: <strong>Lightbox Evolution</strong> is a tool for displaying images, html content, maps, and videos in a "lightbox" style that floats overtop of web page. Using Lightbox Evolution, website authors can showcase a wide assortment of media in all major browsers without navigating users away from the linking page.
  Version: 1.5.3
  Author: Eduardo Daniel Sada
  Author URI: http://codecanyon.net/user/aeroalquimia/portfolio
*/

$myplugin = array();  
$myplugin['file'] = __FILE__;

include("classes/aeropanel.php");
include("panel-config.php");    

$lbe_panel->queue($lbe_panel->path() . "/js/aeropanel/themepreview.js", "script");


function lbe_myplugin_lightbox($content)
{
  global $post, $lbe_panel;
  
  $autolightboxing = (int) $lbe_panel->get_option('autolightboxing');
  $autogroup       = (bool) $lbe_panel->get_option('autogroup') ? 'rel=\"gallery-'.$post->ID.'\"' : '';

  if ($autolightboxing==3 || $autolightboxing==2)
  {
    $pattern['search']  = '/(<a(.*?)href="([^"]*.)(jpg|jpeg|png|gif|tiff|bmp|swf)"(.*?)>)/ie';
    $pattern['replace'] = 'stripslashes(strstr("\2\5","class=") ? "\1" : "<a\2href=\"\3\4\"\5 class=\"lightbox\" '.$autogroup.'>")';
    
    $content = preg_replace($pattern['search'], $pattern['replace'], $content);
  }

  if ($autolightboxing==3 || $autolightboxing==1)
  {
    $videoregs['youtube']       = "youtube\.com\/watch";
    $videoregs['metacafe']      = "metacafe\.com\/watch";
    $videoregs['dailymotion']   = "dailymotion\.com\/video";
    $videoregs['google']        = "google\.com\/videoplay";
    $videoregs['vimeo']         = "vimeo\.com\/";
    $videoregs['megavideo']     = "megavideo.com\/\?v";
    $videoregs['gametrailers']  = "gametrailers.com\/";
    $videoregs['collegehumor']  = "collegehumor.com\/video";
    $videoregs['ustream']       = "ustream.tv";
    $videoregs['twitvid']       = "twitvid.com\/";
    $videoregs['vzaar']         = "vzaar.com\/videos";

    $video_options = implode("|", $videoregs);
    $pattern['search']  = '/(<a(.*?)href="(.*?)('.$video_options.')(.*?)"(.*?)>)/ie';
    $pattern['replace'] = 'stripslashes(strstr("\2\5","class=") ? "\1" : "<a\2href=\"\3\4\5\"\6 class=\"lightbox\">")';
    
    $content = preg_replace($pattern['search'], $pattern['replace'], $content);
  }
  
  return $content;
}
add_filter('the_content', 'lbe_myplugin_lightbox', 12);


function lbe_myplugin_lightbox_head()
{
  global $lbe_panel;
  wp_deregister_script('jquery');
  wp_register_script('jquery', $lbe_panel->path().'/js/jquery.min.js', false, '1.6.4');
  wp_enqueue_script('jquery');

  echo '<link rel="stylesheet" href="'.$lbe_panel->path().'/js/lightbox/themes/'.$lbe_panel->get_option('theme').'/jquery.lightbox.css" type="text/css" media="all"/>';
  echo '<!--[if IE 6]><link rel="stylesheet" type="text/css" href="'.$lbe_panel->path().'/js/lightbox/themes/'.$lbe_panel->get_option('theme').'/jquery.lightbox.ie6.css" /><![endif]-->';
  echo '<style type="text/css">.jquery-lightbox-overlay { background: '.($lbe_panel->get_option('background_custom') ? '#'.$lbe_panel->get_option('background_custom') : $lbe_panel->get_option('background')).'; }</style>';
}
add_action('wp_head', 'lbe_myplugin_lightbox_head', 1);



function lbe_myplugin_lightbox_footer()
{
  global $lbe_panel;

  $exec = trim($lbe_panel->get_option('exec'));

  $flash = '';
  if ($lbe_panel->get_option('default_width') || $lbe_panel->get_option('default_height'))
  {
    $flash = 'jQuery.extend(jQuery.lightbox().options.flash, {';
    
    if ($lbe_panel->get_option('default_width'))
    {
      $flash .= 'width: '.(int)$lbe_panel->get_option('default_width').',';
    }
    if ($lbe_panel->get_option('default_height'))
    {
      $flash .= 'height: '.(int)$lbe_panel->get_option('default_height').',';
    }
    $flash .= 'custom: 1';

    $flash .= '});';
  }

  echo '<script type="text/javascript">if (!window.jQuery) document.write(unescape("%3Cscript src=\"'.$lbe_panel->path().'/js/jquery.min.js?ver=1.6.4\"%3E%3C/script%3E"))</script>';
  echo '<script type="text/javascript" src="'.$lbe_panel->path().'/js/lightbox/jquery.lightbox.min.js"></script>';
  echo '
  <script type="text/javascript">
    jQuery(document).ready(function($){
      '.$exec.'
      '.$flash.'
      $.lightbox().overlay.options.style.opacity = '.(float)$lbe_panel->get_option('background_opacity').';
      $.extend($.lightbox().options, {
        emergefrom      : "'.$lbe_panel->get_option('emergefrom').'",
        moveDuration    : '.(int)$lbe_panel->get_option('moveduration').',
        resizeDuration  : '.(int)$lbe_panel->get_option('resizeduration').'
      });
      $(".lightbox").lightbox({
        modal       : '.(int)$lbe_panel->get_option('modal').',
        autoresize  : '.(int)$lbe_panel->get_option('autoresize').'
      });
    });
  </script>';
}
add_action('wp_footer', 'lbe_myplugin_lightbox_footer');



/*!
 * Gallery Shortcode
 * wp-includes/media.php
 */

function lbe_gallery_shortcode($attr)
{
  global $post;

	static $instance = 0;
	$instance++;

	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

  extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => ''
  ), $attr));

  $id = intval($id);
	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( !empty($include) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}

  if ( empty($attachments) )
  {
    return '';
  }

  if ( is_feed() )
  {
    $output = "\n";
    foreach ( $attachments as $id => $attachment )
    {
      $output .= wp_get_attachment_link($id, $size, true) . "\n";
    }
    return $output;
  }

  $listtag    = tag_escape($listtag);
  $itemtag    = tag_escape($itemtag);
  $captiontag = tag_escape($captiontag);
  $columns    = intval($columns);
  $itemwidth  = $columns > 0 ? floor(100/$columns) : 100;
	$float      = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";

	$output = apply_filters('gallery_style', "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
		</style>
		<!-- see lbe_gallery_shortcode() in wp-content/plugins/jquery.lightbox/plugin-lightbox.php -->
		<div id='$selector' class='gallery galleryid-{$id}'>");

	$i = 0;
  foreach ( $attachments as $id => $attachment ) {
  //$link = wp_get_attachment_link($id);
    
    $a_img = wp_get_attachment_url($id);
  // Attachment page ID
    $att_page = get_attachment_link($id);
  // Returns array
    $img = wp_get_attachment_image_src($id, $size);
    $img = $img[0];
    
    $title = trim($attachment->post_excerpt) ? wptexturize($attachment->post_excerpt) : $attachment->post_title;

    $output .= "<{$itemtag} class='gallery-item'>";
    $output .= "
      <{$icontag} class='gallery-icon'>
          <a href=\"$a_img\" title=\"$title\" rel=\"gallery-{$post->ID}\">
          <img src=\"$img\" alt=\"$title\" />
          </a>
      </{$icontag}>";

		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<{$captiontag} class='gallery-caption'>
				" . wptexturize($attachment->post_excerpt) . "
				</{$captiontag}>";
		}
    
    $output .= "</{$itemtag}>";
    if ( $columns > 0 && ++$i % $columns == 0 )
      $output .= '<br style="clear: both" />';
  }

  $output .= "
      <br style='clear: both;' />
    </div>\n";

  return $output;
}
remove_shortcode('gallery');
add_shortcode('gallery', 'lbe_gallery_shortcode');


function lbe_lightbox_shortcode($atts, $content = null)
{
  $options = array();
  
  foreach ((array)$atts as $key=>$option)
  {
    if ($key != 'href' && $key != 'rel')
    {
      $options["lightbox[$key]"] = $option;
    }
  }
  
  $atts['href'] = do_shortcode($atts['href']);
  $content      = do_shortcode($content);
  
  return '<a href="'.add_query_arg($options, $atts['href']).'" class="lightbox" rel="'.$atts['rel'].'">'.$content.'</a>';
}
add_shortcode("lightbox", "lbe_lightbox_shortcode");
?>