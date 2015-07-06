<?php

/*
Template Name: Sitemap
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
					
					<h4><?php _e( 'Pages', 'it-l10n-Builder' ); ?></h4>
					<ul>
						<?php wp_list_pages( 'title_li=&depth=0' ); ?>
					</ul>
					
					<h4><?php _e( 'Recent Blog Posts', 'it-l10n-Builder' ); ?></h4>
					<ul>
						<?php wp_get_archives( 'postbypost', 25 ); ?>
					</ul>
					
					<h4><?php _e( 'Posts by Month', 'it-l10n-Builder' ); ?></h4>
					<ul>
						<?php wp_get_archives( 'type=monthly&limit=12' ); ?>
					</ul>
					
					<h4><?php _e( 'Posts by Categories', 'it-l10n-Builder' ); ?></h4>
					<ul>
						<?php wp_list_categories( 'title_li=&depth=0' ); ?>
					</ul>
				</div>
			</div>
			<!-- end .post -->
			
			<?php comments_template(); // include comments template ?>
		<?php endwhile; // end of one post ?>
		
		<!-- Previous/Next page navigation -->
		<div class="paging clearfix">
			<div class="alignleft"><?php previous_posts_link( __( '&laquo; Previous Page', 'it-l10n-Builder' ) ); ?></div>
			<div class="alignright"><?php next_posts_link( __( 'Next Page &raquo;', 'it-l10n-Builder' ) ); ?></div>
		</div>
	<?php else : // do not delete ?>
		<?php do_action( 'builder_template_show_not_found' ); ?>
	<?php endif; // do not delete ?>
<?php
	
}

remove_action( 'builder_layout_engine_render_content', 'render_content' );
add_action( 'builder_layout_engine_render_content', 'legacy_render_content' );
