<?php


function render_footer() {
	$builder_link = '<a href="http://ithemes.com/purchase/builder-theme/" title="iThemes Builder">iThemes Builder</a>';
	$ithemes_link = '<a href="http://ithemes.com/" title="iThemes WordPress Themes">iThemes</a>';
	$wordpress_link = '<a href="http://wordpress.org">WordPress</a>';
	
	$footer_credit = sprintf( __( '%1$s by %2$s<br />Powered by %3$s', 'it-l10n-Builder' ), $builder_link, $ithemes_link, $wordpress_link );
	$footer_credit = apply_filters( 'builder_footer_credit', $footer_credit );
	
?>

	<div class="alignright">
		<!-- <?php echo $footer_credit; ?> -->
		<a href="http://www.eastsidefriendsofseniors.org/sitemap-2/" alt="Sitemap" title="Sitemap">Sitemap</a> | <a href="http://www.eastsidefriendsofseniors.org/privacy-policy/" alt="Privacy Policy" title="Privacy Policy">Privacy Policy</a><br /> Eastside Friends of Seniors
	<br /> (425) 369-9120 | 
	<a href="mailto:info@eastsidefriendsofseniors.org">info@eastsidefriendsofseniors.org</a>
		<br />1121 228th Ave SE Sammamish, WA 98075
	</div>
	<?php wp_footer(); ?>
<?php
	
}

add_action( 'builder_layout_engine_render_footer', 'render_footer' );


?>
