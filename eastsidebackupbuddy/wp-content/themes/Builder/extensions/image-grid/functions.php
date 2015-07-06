<?php

if ( is_admin() )
	return;


// Roll out the image sizes
require_once( 'lib/image-size.php' );

it_classes_load( 'it-file-utility.php' );

// We need to print the scripts for the functionality to work.
function builder_feature_image_grid_print_scripts() {
	$base_url = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
	
	wp_enqueue_script( 'builder_feature_image_pngfix', "$base_url/js/pngfix.js" );
	wp_enqueue_script( 'it_colorbox', "$base_url/js/jquery.colorbox-min.js", array( 'jquery' ) );
	wp_enqueue_script( 'builder_feature_image_colorbox_reference', "$base_url/js/colorbox-reference.js", array( 'it_colorbox' ) );
}
add_action( 'wp_print_scripts', 'builder_feature_image_grid_print_scripts' );

// We also need to registure and print the styles for colorbox.
function builder_feature_image_grid_print_styles() {
	$base_url = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
	
	wp_enqueue_style( 'colorbox-1', "$base_url/css/colorbox-1.min.css" );
}
add_action('wp_print_styles', 'builder_feature_image_grid_print_styles');


// Feature Image Grid and Colorbox
function builder_feature_image_grid() {
	global $post, $wp_query;
	
	it_classes_load( 'it-file-utility.php' );
	$base_url = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
	
	$args = array(
		'ignore_sticky_posts' => true,
		'posts_per_page'      => 9,
		'meta_key'            => '_thumbnail_id',
		'paged'               => get_query_var( 'paged' ),
	);
	
	$args = wp_parse_args( $args, $wp_query->query );
	
	query_posts( $args ); // Query only posts with a feature image set.
	
?>
	<?php if ( have_posts() ) : ?>
		<div class="loop">
			<div class="loop-content">
				<?php while ( have_posts() ) : the_post(); // the loop ?>
					<?php if ( has_post_thumbnail() ) : ?>
						<?php $galleryurl = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' ); ?>
						<div class="grid_wrapper entry-content">
							<div class="inner">
								<p class="slide_box">
									<a href="<?php echo $galleryurl[0]; ?>" title="<?php the_title(); ?>" rel="gallery-images" class="gallery-image"><img src="<?php echo $base_url ?>/images/zoom.png" alt="Zoom Feature Image" /></a>
									<a href="<?php the_permalink(); ?>"  class="permalink"><img src="<?php echo $base_url ?>/images/more.png" alt="Read This Article" /><span>Read: <strong><?php the_title(); ?></strong></span></a>
								</p>
								<?php the_post_thumbnail( 'it-gallery-thumb', array( 'class' => 'it-gallery-thumb' ) ); ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endwhile; // end of one post ?>
			</div>
			
			<!-- Previous/Next page navigation -->
			<div class="loop-footer">
				<div class="loop-utility clearfix">
					<div class="alignleft"><?php previous_posts_link( '&laquo; Previous Page' ); ?></div>
					<div class="alignright"><?php next_posts_link( 'Next Page &raquo;' ); ?></div>
				</div>
			</div>
		</div>
	<?php else : // do not delete ?>
		<?php do_action( 'builder_template_show_not_found' ); ?>
	<?php endif; // do not delete ?>
<?php
	
}

if ( ! is_singular() ) {
	add_action( 'builder_layout_engine_render', 'feature_image_grid_change_render_content', 0 );
}
function feature_image_grid_change_render_content() {
	remove_action( 'builder_layout_engine_render_content', 'render_content' );
	add_action( 'builder_layout_engine_render_content', 'builder_feature_image_grid' );
}
