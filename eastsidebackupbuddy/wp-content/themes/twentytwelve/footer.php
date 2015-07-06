<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>
	</div><!-- #main .wrapper -->
	<footer id="colophon" role="contentinfo">
		<div class="site-info">
			<?php do_action( 'twentytwelve_credits' ); ?>
			<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'twentytwelve' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'twentytwelve' ); ?>"><?php printf( __( 'Proudly powered by %s', 'twentytwelve' ), 'WordPress' ); ?></a>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
<div align="right">
<a href="http://www.eastsidefriendsofseniors.org/sitemap-2/" alt="Sitemap" title="Sitemap">Sitemap</a> | <a href="http://www.eastsidefriendsofseniors.org/privacy-policy/" alt="Privacy Policy" title="Privacy Policy">Privacy Policy</a><br /> <b>Jacob was here playing :D</b>
<br /> (425) 369-9120 |
<a href="mailto:info@eastsidefriendsofseniors.org">info@<b>(FOOTER.php)eastsidefriendsofseniors.org</b></a>
<br />1121 228th Ave SE Sammamish, WA 98075
</div>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
