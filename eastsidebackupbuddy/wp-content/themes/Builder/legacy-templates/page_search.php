<?php

/*
Template Name: Search Template
*/


function legacy_render_content() {
	
?>
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : // The Loop ?>
			<?php the_post(); ?>
			
			<div <?php post_class(); ?>>
				<!-- title, meta, and date info -->
				<div class="title clearfix">
					<div class="post-title">
						<h1 id="post-<?php the_ID(); ?>"><?php the_title(); ?></h1>
					</div>
				</div>
				
				<!-- post content -->
				<div class="post-content">
					<?php the_content(); ?>
				</div>
				
				<?php get_search_form(); ?>
			</div>
			<!-- end .post -->
		<?php endwhile; // end of one post ?>
	<?php else : // do not delete ?>
		<?php do_action( 'builder_template_show_not_found' ); ?>
	<?php endif; // do not delete ?>
<?php
	
}

remove_action( 'builder_layout_engine_render_content', 'render_content' );
add_action( 'builder_layout_engine_render_content', 'legacy_render_content' );
