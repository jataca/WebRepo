<?php
// Used for drag & drop / collapsing boxes.
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');

wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );
// Handles resizing thickbox.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}
wp_enqueue_script( 'backupbuddy-ms-export', $this->_parent->_pluginURL . '/js/ms.js', array( 'jquery' ) );
wp_print_scripts( 'backupbuddy-ms-export' );

global $current_blog;
$action = isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : false;
$form_url = $this->_selfLink . '-msimport';
?>
<div class='wrap'>
<p>For BackupBuddy Multisite documentation, please visit the <a href='http://ithemes.com/codex/page/BackupBuddy_Multisite'>BackupBuddy Multisite Codex</a>.</p>
<?php
switch( $action ) {
	case 'step2':
		require( $this->_parent->_pluginPath . '/classes/' . 'msimport_steps/step2.php' );
		break;
	case 'step3':
		require( $this->_parent->_pluginPath . '/classes/' . 'msimport_steps/step3.php' );
		break;
	case 'step4':
	case 'step5':
	case 'step6':
	case 'step7':
	case 'step8':
		require_once( $this->_parent->_pluginPath . '/classes/' . 'msimport_steps/ms_importbuddy.php' );
		break;
	default:
		require( $this->_parent->_pluginPath . '/classes/' . 'msimport_steps/step1.php' );
		break;
} //end switch
?>
</div><!-- .wrap-->