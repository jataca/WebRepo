<?php

if ( is_admin() )
	return;


// Roll out the image sizes
require_once( 'lib/image-size.php' );

it_classes_load( 'it-file-utility.php' );


// Magazine Style Layout
function builder_teasers_left_extension() {
	$base_url = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
	
	add_filter( 'excerpt_length', 'builder_teasers_left_excerpt_length' );
	add_filter( 'excerpt_more', 'builder_teasers_left_excerpt_more' );
	
?>
	<?php if ( have_posts() ) : ?>
		<div class="loop">
			<div class="loop-content">
				<?php while ( have_posts() ) : // the loop ?>
					<?php the_post(); ?>
					
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content teasers">
							<?php if ( has_post_thumbnail() ) : ?>
								<a href="<?php the_permalink(); ?>" class="teasers-thumb-wrapper">
									<?php the_post_thumbnail( 'it-teaser-thumb', array( 'class' => 'alignleft teaser-thumb' ) ); ?>
								</a>
							<?php else : ?>
								<?php edit_post_link( '<img width="150" height="200" src="' . $base_url . '/images/no-feature-image.jpg" class="alignleft teaser-thumb no-thumb" />', '<div class="teaser-thumb-wrapper">', '</div>' ) ; ?>
							<?php endif; ?>
							
							<h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							
							<div class="entry-meta">
								<span class="the_date">Posted on <?php  echo get_the_date(); ?></span>
								<span class="author"> by <?php echo get_the_author_link(); ?></span>
								<?php do_action( 'builder_comments_popup_link', '<span class="comments"> &#126; ', '</span>', __( '%s Comments', 'it-l10n-Builder' ), __( '0', 'it-l10n-Builder' ), __( '1', 'it-l10n-Builder' ), __( '%', 'it-l10n-Builder' ) ); ?>
							</div>
							
							<?php the_excerpt(); ?>
						</div>
					</div>
					<!-- end .post -->
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
	
	// Get rid of those filters.
	remove_filter( 'excerpt_length', 'builder_teasers_left_excerpt_length' );
	remove_filter( 'excerpt_more', 'builder_teasers_left_excerpt_more' );
}


// How about a teaser lenght of 60.
function builder_teasers_left_excerpt_length( $length ) {
	return 60;
}

// Let's make the read more similar to the default.
function builder_teasers_left_excerpt_more( $more ) {
	global $post;
	return '...<p><a href="'. get_permalink( $post->ID ) . '" class="more-link">Read More&rarr;</a></p>';
}


if ( ! is_singular() ) {
	add_action( 'builder_layout_engine_render', 'builder_teasers_left_extension_change_render_content', 0 );
}

function builder_teasers_left_extension_change_render_content() {
	remove_action( 'builder_layout_engine_render_content', 'render_content' );
	add_action( 'builder_layout_engine_render_content', 'builder_teasers_left_extension' );
}
