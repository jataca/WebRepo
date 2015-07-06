<?php

/*
Generators for web analytics JavaScript code

Written by Chris Jean for iThemes.com
Version 1.0.0

Version History
	1.0.0 - 2010-12-15 - Chris Jean
		Release ready
*/


/*
function builder_add_universal_analytics_functions( $content ) {
	ob_start();
	
	require_once( dirname( dirname( __FILE__ ) ) . '/js/universal-analytics.js.php' );
	
	$script = ob_get_contents();
	ob_end_clean();
	
	if ( ! empty( $content ) )
		$content .= "\n";
	
	return $content . $script;
}
add_filter( 'builder_filter_javascript_content', 'builder_add_universal_analytics_functions', 20 );
*/

function builder_generate_google_analytics_code( $content ) {
	if ( ! builder_get_theme_setting( 'google_analytics_enable' ) )
		return $content;
	
	$account_id = builder_get_theme_setting( 'google_analytics_account_id' );
	
	if ( empty( $account_id ) )
		return $content;
	
	ob_start();
	
?>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', "<?php echo $account_id; ?>"]);
_gaq.push(['_trackPageview']);

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
<?php
	
	$script = ob_get_contents();
	ob_end_clean();
	
	if ( ! empty( $content ) )
		$content .= "\n";
	
	return $content . $script;
}
add_filter( 'builder_filter_javascript_content', 'builder_generate_google_analytics_code' );

function builder_generate_woopra_code( $content ) {
	if ( ! builder_get_theme_setting( 'woopra_enable' ) )
		return $content;
	
	$woo_settings = $woo_actions = array();
	
	
	$domain = builder_get_theme_setting( 'woopra_domain' );
	
	if ( ! empty( $domain ) )
		$woo_settings[] = "domain:'$domain'";
	else {
		$domain = get_option( 'home' );
		
		if ( preg_match( '|//([^/]+)|', $domain, $match ) )
			$woo_settings[] = "domain:'" . addslashes( $match[1] ) . "'";
	}
	
	
/*	$idle_timeout = builder_get_theme_setting( 'woopra_setting_idle_timeout' );
	
	if ( ! empty( $idle_timeout ) ) {
		$idle_timeout = intval( $idle_timeout );
		$woo_settings[] = "idle_timeout:'$idle_timeout'";
	}
	
	
	if ( builder_get_theme_setting( 'woopra_event_tracker_search' ) ) {
		$search = apply_filters( 'the_search_query', get_search_query( false ) );
		
		if ( ! empty( $search ) ) {
			$search = addslashes( trim( preg_replace( "|^(.{1,50})\b.*$|s", "$1", str_replace( "\n", ' ', $search ) ) ) );
			$woo_actions[] = "{'type':'event','name':'Search','terms':'$search'}";
		}
	}
	
	if ( builder_get_theme_setting( 'woopra_event_tracker_comment' ) ) {
//		$comment = 
	}*/
	
	
	if ( ! empty( $woo_settings ) )
		$woo_settings = 'var woo_settings = {' . implode( ', ', $woo_settings ) . "};\n";
	else
		$woo_settings = '';
	
	if ( ! empty( $woo_actions ) )
		$woo_actions = 'var woo_actions = [' . implode( ',', $woo_actions ) . "];\n";
	else
		$woo_actions = '';
	
	ob_start();
	
?>
<?php echo $woo_settings; ?>
<?php echo $woo_actions; ?>
(function(){
var wsc = document.createElement('script');
wsc.src = document.location.protocol+'//static.woopra.com/js/woopra.js';
wsc.type = 'text/javascript';
wsc.async = true;
var ssc = document.getElementsByTagName('script')[0];
ssc.parentNode.insertBefore(wsc, ssc);
})();
<?php
	
	$script = ob_get_contents();
	ob_end_clean();
	
	if ( ! empty( $content ) )
		$content .= "\n";
	
	return $content . $script;
}
add_filter( 'builder_filter_javascript_content', 'builder_generate_woopra_code' );
