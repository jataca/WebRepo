<?php

if ( is_admin() )
	return;


// We need the image sizes that are registured.
require_once( 'lib/image-size.php' );

it_classes_load( 'it-file-utility.php' );


// An extension for Builder that outputs content in a magazine style layout.
function builder_magazine_layout() {
	$base_url = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
	
	add_filter( 'excerpt_length', 'builder_magazine_excerpt_length' );
	add_filter( 'excerpt_more', 'builder_magazine_excerpt_more' );
	
?>
	<?php if ( have_posts() ) : ?>
		<div class="loop">
			<div class="loop-content">
				<?php while ( have_posts() ) : // the loop ?>
					<?php the_post(); ?>
					
					<div <?php post_class('magazine-post-wrap'); ?>>
						<div class='magazine-post entry-content'>
							<div class="entry-header">
								<?php if ( has_post_thumbnail() ) : ?>
									<div class="entry-meta">
										<a class="post-image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'it-magazine-thumb' ); ?></a>
									</div>
								<?php else : ?>
									<?php edit_post_link( '<img width="350" height="150" src="' . $base_url . '/images/no-feature-image.jpg" class="it-magazine-thumb no-thumb" />', '<div class="entry-meta post-image">', '</div>' ) ; ?>
								<?php endif; ?>
								
								<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								<span class="entry-meta the_date"><?php echo get_the_date(); ?></span>
								<?php do_action( 'builder_comments_popup_link', '<span class="comments">', '</span>', __( '%s Comments', 'it-l10n-Builder' ), __( '0', 'it-l10n-Builder' ), __( '1', 'it-l10n-Builder' ), __( '%', 'it-l10n-Builder' ) ); ?>
							</div>
							
							<div class="entry-content">
								<?php the_excerpt(); ?>
							</div>
						</div>
					</div>
				<?php endwhile; // end of one post ?>
			</div>
			
			<div class="loop-footer">
				<!-- Previous/Next page navigation -->
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
	
	// remember those modfication to the excerpt above? Let's remove them now.
	remove_filter( 'excerpt_length', 'builder_magazine_excerpt_length' );
	remove_filter( 'excerpt_more', 'builder_magazine_excerpt_more' );
}

// Now let's edit the excerpt for this extension.
function builder_magazine_excerpt_length( $length ) {
	return 40;
}

// Cannot forget about the excerpt more link. Let's make it look like the posts default link.
function builder_magazine_excerpt_more( $more ) {
	global $post;
	return '...<p><a href="'. get_permalink( $post->ID ) . '" class="more-link">Read More&rarr;</a></p>';
}


if ( ! is_singular() ) {
	add_action( 'builder_layout_engine_render', 'builder_magazine_layout_change_render_content', 0 );
}

function builder_magazine_layout_change_render_content() {
	remove_action( 'builder_layout_engine_render_content', 'render_content' );
	add_action( 'builder_layout_engine_render_content', 'builder_magazine_layout' );
}
