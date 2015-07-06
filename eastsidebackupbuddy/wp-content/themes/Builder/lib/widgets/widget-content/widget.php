<?php

/*
Copyright 2010 iThemes (email: support@ithemes.com)

Written by Chris Jean
Version 1.0.0

Version History
	1.0.0 - 2010-10-05 - Chris Jean
		Release-ready
*/


if ( ! class_exists( 'ITWidgetContent' ) ) {
	class ITWidgetContent extends WP_Widget {
		function ITWidgetContent() {
			$widget_ops = array( 'classname' => 'widget-it-content', 'description' => __( 'Add "Widget Content" entries to a sidebar', 'it-l10n-Builder' ) );
			$control_ops = array( 'width' => 400 );
			
			$this->WP_Widget( 'it_widget_content', __( 'Widget Content', 'it-l10n-Builder' ), $widget_ops, $control_ops );
		}
		
		function widget( $args, $instance ) {
			if ( empty( $instance['entry_id'] ) )
				return;
			
			extract( $args );
			
			$post = get_post( $instance['entry_id'] );
			
			if ( empty( $post ) || ! isset( $post->post_content ) )
				return;
			
			$content = apply_filters( 'the_content', $post->post_content );
			
			
			if ( ! empty( $instance['style'] ) )
				$before_widget = preg_replace( '/(<div[^>]* class=[\'"][^\'"]+)([\'"])/i', "\$1 {$instance['style']}\$2", $before_widget );
			
			
			echo $before_widget;
			
			if ( ! empty( $instance['title'] ) )
				echo $before_title . $instance['title'] . $after_title;
			
?>
	<div class="widget-content clearfix">
		<?php echo $content; ?>
	</div>
<?php
			
			echo $after_widget;
		}
		
		function form( $instance ) {
			$defaults = array(
				'title'			=> '',
				'entry_id'	=> '',
				'style'			=> '',
			);
			
			$instance = wp_parse_args( (array) $instance, $defaults );
			
			$form =& new ITForm( $instance, array( 'widget_instance' => &$this ) );
			
			
			$posts = get_posts( array( 'post_type' => 'widget_content', 'numberposts' => '1000' ) );
			
			$entries = array();
			foreach ( (array) $posts as $post ) {
				$title = $post->post_title;
				
				if ( strlen( $title ) > 80 )
					$title = substr( $title, 0, 80 ) . '...';
				
				$entries[$post->ID] = $title;
			}
			asort( $entries );
			
			
			$styles = builder_get_widget_styles();
			
			if ( ! empty( $styles ) )
				$styles = array_merge( array( '' => 'Default' ), $styles );
			
?>
	<?php if ( ! empty( $entries ) ) : ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'it-l10n-Builder' ); ?></label>
			<?php $form->add_text_box( 'title', array( 'class' => 'widefat' ) ); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'entry_id' ); ?>"><?php _e( 'Widget Content Entry:', 'it-l10n-Builder' ); ?></label><br />
			<?php $form->add_drop_down( 'entry_id', $entries ); ?>
		</p>
		<?php if ( ! empty( $styles ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'Widget Style:', 'it-l10n-Builder' ); ?></label><br />
				<?php $form->add_drop_down( 'style', $styles ); ?>
			</p>
		<?php else : ?>
			<?php $form->add_hidden( 'style' ); ?>
		<?php endif; ?>
		<p>
			<em>Note: Use the <a href="<?php echo admin_url( 'edit.php?post_type=widget_content' ); ?>">Widget Content editor</a> to manage content for the Widget Content widgets.</em>
		</p>
	<?php else : ?>
		<p>
			This widget allows you to easily add advanced content to a sidebar.
		</p>
		<p>
			Currently, your site doesn't have any Widget Content entries. Use the <a href="<?php echo admin_url( 'edit.php?post_type=widget_content' ); ?>">Widget Content editor</a> to create new entries. Once you have created one or more new Widget Content entries, edit this widget again to select the desired entry and customize the widget.
		</p>
		<?php $form->add_hidden( 'title' ); ?>
		<?php $form->add_hidden( 'entry_id' ); ?>
		<?php $form->add_hidden( 'style' ); ?>
	<?php endif; ?>
<?php
			
		}
	}
}

register_widget( 'ITWidgetContent' );

?>
