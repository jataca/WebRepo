<?php

function legacy_render_content() {
	global $post;
	
?>
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : // the loop ?>
			<?php the_post(); ?>
			
			<!--Post Wrapper Class-->
			<div <?php post_class( 'post' ); ?>>
				<!--Title/Date/Meta-->
				<div class="title clearfix">
					<div class="post-title">
						<?php if ( 0 != $post->post_parent ) : ?>
							<h1 id="post-<?php the_ID(); ?>"><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment"><?php echo get_the_title( $post->post_parent ); ?></a> &raquo; <?php the_title(); ?></h1>
						<?php else : ?>
							<h1 id="post-<?php the_ID(); ?>"><?php the_title(); ?></h1>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="entry-attachment">
					<p class="attachment">
						<a href="<?php echo wp_get_attachment_url(); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" rel="attachment">
							<?php echo get_attachment_icon(); ?>
							<?php //echo wp_get_attachment_image( $post->ID, array( $max_width, $max_width * 2 ) ); // max $content_width wide or high. ?>
						</a>
					</p>
				</div>
				
				<div class="post-content">
					<?php the_content(); ?>
				</div>
				
				<?php comments_template(); // include comments template ?>
			</div><!--end .post-->
		<?php endwhile; // end of one post ?>
	<?php else : // do not delete ?>
		<?php do_action( 'builder_template_show_not_found' ); ?>
	<?php endif; // do not delete ?>
<?php
	
}

remove_action( 'builder_layout_engine_render_content', 'render_content' );
add_action( 'builder_layout_engine_render_content', 'legacy_render_content' );
