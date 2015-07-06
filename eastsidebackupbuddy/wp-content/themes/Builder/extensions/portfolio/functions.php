<?php

if ( is_admin() )
	return;


// Roll out the image sizes
require_once( 'lib/image-size.php' );

it_classes_load( 'it-file-utility.php' );


// Portfolio Extension
function builder_portfolio_extension() {
	$base_url = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );
	
?>
	<?php if ( have_posts() ) : ?>
		<div class="loop">
			<div class="loop-content">
				<?php while ( have_posts() ) : // the loop ?>
					<?php the_post(); ?>
					
					<div <?php post_class('portfolio-post-wrap'); ?>>
						<div class='portfolio-post entry-content'>
							<?php if ( has_post_thumbnail() ) : ?>
								<a class="entry-image" href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'it-portfolio-thumb' ); ?>
								</a>
							<?php else : ?>
								<?php edit_post_link( '<img width="350" height="150" src="' . $base_url . '/images/no-feature-image.jpg" class="it-magazine-thumb no-thumb" />', '<div class="post-image">', '</div>' ) ; ?>
							<?php endif; ?>
							
							<span class="portfolio-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></span>
						</div>
					</div>
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
	add_action( 'builder_layout_engine_render', 'builder_portfolio_extension_change_render_content', 0 );
}

function builder_portfolio_extension_change_render_content() {
	remove_action( 'builder_layout_engine_render_content', 'render_content' );
	add_action( 'builder_layout_engine_render_content', 'builder_portfolio_extension' );
}
