<?php

function legacy_render_content() {
	// Load the not_found.php file, by default
	do_action( 'builder_template_show_not_found' );
}

remove_action( 'builder_layout_engine_render_content', 'render_content' );
add_action( 'builder_layout_engine_render_content', 'legacy_render_content' );
