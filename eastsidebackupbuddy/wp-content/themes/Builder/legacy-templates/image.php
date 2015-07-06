<?php

function legacy_render_content() {
	global $post;
	
	$area_width = apply_filters( 'builder_layout_engine_get_current_area_width', null );
	$max_width = $area_width - 50;
	
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
							<?php echo wp_get_attachment_image( $post->ID, array( $max_width, $max_width * 2 ) ); // max $content_width wide or high. ?>
						</a>
					</p>
				</div>
				
				<?php if ( get_the_content() ) : ?>
					<div class="post-content">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
				
				<div class="photometa clearfix">
					<div class="EXIF">
						<h4><?php _e( 'Image Data', 'it-l10n-Builder' ); ?></h4>
						
						<?php if ( is_attachment() ) : ?>
							<?php
								$meta = wp_get_attachment_metadata( $post->ID );
								$image_meta = $meta['image_meta'];
								
								if ( ! empty( $image_meta['created_timestamp'] ) )
									$image_meta['created_timestamp'] = date( 'l, F j, Y, g:i a', $image_meta['created_timestamp'] );
								if ( ! empty( $image_meta['aperture'] ) )
									$image_meta['aperture'] = 'f/' . $image_meta['aperture'];
								if ( ! empty( $image_meta['focal_length'] ) )
									$image_meta['focal_length'] .= 'mm';
								if ( ! empty( $image_meta['shutter_speed'] ) )
									$image_meta['shutter_speed'] = number_format( $image_meta['shutter_speed'], 2 ) . ' sec';
								
								$meta_fields = array(
									'camera'            => __( 'Camera', 'it-l10n-Builder' ),
									'created_timestamp' => __( 'Date Taken', 'it-l10n-Builder' ),
									'aperture'          => __( 'Aperture', 'it-l10n-Builder' ),
									'focal_length'      => __( 'Focal Length', 'it-l10n-Builder' ),
									'iso'               => __( 'ISO', 'it-l10n-Builder' ),
									'shutter_speed'     => __( 'Shutter Speed', 'it-l10n-Builder' ),
									'credit'            => __( 'Credit', 'it-l10n-Builder' ),
									'copyright'         => __( 'Copyright', 'it-l10n-Builder' ),
									'title'             => __( 'Title', 'it-l10n-Builder' ),
								);
							?>
							
							<table>
								<tr>
									<th scope="row"><?php _e( 'Dimensions', 'it-l10n-Builder' ); ?></th>
									<td><?php echo "{$meta['width']}px &times; {$meta['height']}px"; ?></td>
								</tr>
								
								<?php foreach ( (array) $meta_fields as $field => $description ) : ?>
									<?php if ( empty( $image_meta[$field] ) ) continue; ?>
									
									<tr>
										<th scope="row"><?php echo $description; ?></th>
										<td><?php echo $image_meta[$field]; ?></td>
									</tr>
								<?php endforeach; ?>
							</table>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="meta-bottom clearfix">
					<div class="alignleft"><?php previous_image_link(); ?></div>
					<div class="alignright"><?php next_image_link(); ?></div>
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
