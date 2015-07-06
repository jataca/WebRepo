<?php


function render_content() {
	
?>
	<?php if ( have_posts() ) : ?>
		<div class="loop">
			<div class="loop-content">
				<?php while ( have_posts() ) : // The Loop ?>
					<?php the_post(); ?>
					
					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<!-- title, meta, and date info -->
						<div class="entry-header clearfix">
							<h1 class="entry-title"><?php the_title(); ?></h1>
							
							<div class="entry-meta">
								<?php printf( __( 'By %s', 'it-l10n-Builder' ), '<span class="author">' . builder_get_author_link() . '</span>' ); ?>
							</div>
							
							<div class="entry-meta date">
								<span class="weekday"><?php the_time( 'l' ); ?><span class="weekday-comma">,</span></span>
								<span class="month"><?php the_time( 'F' ); ?></span>
								<span class="day"><?php the_time( 'j' ); ?><span class="day-suffix"><?php the_time( 'S' ); ?></span><span class="day-comma">,</span></span>
								<span class="year"><?php the_time( 'Y' ); ?></span>
							</div>
						</div>
						
						<!-- post content -->
						<div class="entry-content clearfix">
							<?php the_content(); ?>
						</div>
						
						<!-- categories and tags -->
						<div class="entry-footer clearfix">
							<?php wp_link_pages( array( 'before' => '<p class="entry-utility"><strong>' . __( 'Pages:', 'it-l10n-Builder' ) . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number' ) ); ?>
							<?php edit_post_link( __( 'Edit this entry.', 'it-l10n-Builder' ), '<p class="entry-utility edit-entry-link">', '</p>' ); ?>
							
							<div class="entry-meta alignleft">
								<div class="categories"><?php printf( __( 'Categories : %s', 'it-l10n-Builder' ), get_the_category_list( ', ' ) ); ?></div>
								<?php the_tags( '<div class="tags">' . __( 'Tags : ', 'it-l10n-Builder' ), ', ', '</div>' ); ?>
							</div>
						</div>
					</div>
					<!-- end .post -->
					
					<?php comments_template(); // include comments template ?>
				<?php endwhile; // end of one post ?>
			</div>
			
			<!-- Previous/Next page navigation -->
			<div class="loop-footer clearfix">
				<div class="loop-utility alignleft"><?php previous_posts_link( __( '&laquo; Previous Page', 'it-l10n-Builder' ) ); ?></div>
				<div class="loop-utility alignright"><?php next_posts_link( __( 'Next Page &raquo;', 'it-l10n-Builder' ) ); ?></div>
			</div>
		</div>
	<?php else : // do not delete ?>
		<?php do_action( 'builder_template_show_not_found' ); ?>
	<?php endif; // do not delete ?>
<?php
	
}

add_action( 'builder_layout_engine_render_content', 'render_content' );

do_action( 'builder_layout_engine_render', basename( __FILE__ ) );
