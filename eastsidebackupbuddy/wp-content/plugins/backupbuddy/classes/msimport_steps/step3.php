<?php
check_admin_referer( 'bbms-migration', 'pb_bbms_migrate' );
if ( !current_user_can( 'manage_sites' ) ) 
	wp_die( __( 'You do not have permission to access this page.', 'it-l10n-backupbuddy' ) );
global $current_blog;
$errors = false;	
$blog = $domain = $path = '';
$form_url = add_query_arg( array(
	'step' => '4',
	'action' => 'step4'
) , $form_url );
?>
<h3><?php esc_html_e( 'Step 3 - Choose a BackupBuddy ZIP file', 'it-l10n-backupbuddy' ); ?></h3>
<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>
<table class="form-table">
	<tr class="form-field form-required">
		<td>
		<?php 
		// Files in wp-content/plugins directory
		$backup_count = 0;
		$path = ABSPATH;
		if ( is_dir( $path ) ) {
			$root_dir = @ opendir($path);
			while (($file = readdir( $root_dir ) ) !== false ) {
				if ( is_file( $path . $file ) ) {
					if ( pathinfo( $file, PATHINFO_EXTENSION ) == 'zip' ) {
						$backup_count += 1;
						?>
						<input style="width: auto;" type='radio' id='backup_<?php echo esc_attr( $backup_count ); ?>' value='<?php echo esc_attr( $path . $file ); ?>' name='backuppath' />&nbsp;&nbsp;<label for='backup_<?php echo esc_attr( $backup_count ); ?>'><?php echo esc_html( $file ); ?></label><br />
						<?php
					}
				}
			}
			//echo "</select>";
		} //end is_dir $path
		if ( $backup_count <= 0 ) {
			echo sprintf( "<p>%s</p>", __( 'There are no zip files available.', 'it-l10n-backupbuddy' ) );
		}
		?>
		</td>
	</tr>
</table>
<?php 
	$blog_id = absint( $_POST[ 'blog_id' ] );
	switch_to_blog( $blog_id );
	$uploads = wp_upload_dir();
	$upload_url = $uploads[ 'baseurl' ];
	?>
<input type='hidden' name='blog_id' value='<?php echo esc_attr( absint( $_POST[ 'blog_id' ] ) ); ?>' />
<input type='hidden' name='blog_path' value='<?php echo esc_attr( $_POST[ 'blog_path' ] ); ?>' />
<input type='hidden' name='upload_url' value='<?php echo esc_url( $upload_url ); ?>' />
<?php submit_button( __(' Extract', 'it-l10n-backupbuddy' ), 'primary', 'add-site' ); ?>
</form>