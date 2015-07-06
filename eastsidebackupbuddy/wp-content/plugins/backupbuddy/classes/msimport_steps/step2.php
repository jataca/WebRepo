<?php
check_admin_referer( 'bbms-migration', 'pb_bbms_migrate' );
if ( !current_user_can( 'manage_sites' ) ) 
	wp_die( __( 'You do not have permission to access this page.', 'it-l10n-backupbuddy' ) );
global $current_blog;
$errors = false;	
$blog = $domain = $path = '';
if ( isset( $_POST[ 'add-site' ] ) ) {
	global $current_user, $base;
	$messages = array(
		'updates' => array(),
		'errors' => array()
	);
	//Code conveniently lifted from site-new.php in /wp-admin/network/
	$blog = $_POST['blog'];
	
	$domain = '';
	$blog_domain = $blog[ 'domain' ];
	$blog_domain = str_replace( 'https://', '', $blog_domain );
	$blog_domain = str_replace( 'http://', '', $blog_domain );
	$blog_domain = str_replace( 'www.', '', $blog_domain );
	if ( ( !preg_match( '/(--)/', $blog_domain ) && preg_match( '|^([a-zA-Z0-9-])+$|', $blog_domain ) ) || domain_exists( $blog_domain, '/', $current_blog->blog_id ) )
		$domain = strtolower( $blog_domain );

	// If not a subdomain install, make sure the domain isn't a reserved word
	if ( ! is_subdomain_install() ) {
		$subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed' ) );
		if ( in_array( $domain, $subdirectory_reserved_names ) ) {
			$messages[ 'errors' ][] = sprintf( __('The following words are reserved for use by WordPress functions and cannot be used as blog names: <code>%s</code>', 'it-l10n-backupbuddy' ), implode( '</code>, <code>', $subdirectory_reserved_names ) );
		}
	}
	if ( empty( $domain ) ) {
		$messages[ 'errors' ][] =  __( 'Missing or invalid site address.', 'it-l10n-backupbuddy' );
	}
	if ( is_subdomain_install() ) {
		if ( domain_exists( $blog_domain, '/', $current_blog->blog_id ) ) {
			$newdomain = $blog_domain;
			$path = '/';
		} else {
			$newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_blog->domain );
			$path = $base;
		}
	} else {
		$newdomain = $current_blog->domain;
		$path = $base . $domain . '/';
	}
	$blog_id = 0;
	if ( domain_exists( $newdomain, $path, $current_blog->blog_id ) ) {
		$blog_id = domain_exists( $newdomain, $path, $current_blog->blog_id );
		$messages[ 'updates' ][] = __( 'This domain path already exists.  Click "Proceed" to use this site.', 'it-l10n-backupbuddy' );
	} else {
		if ( count( $messages[ 'errors' ] ) <= 0 ) {
			$messages[ 'updates' ][] = __( 'The site has been created. Click "Proceed" to use this site.', 'it-l10n-backupbuddy' );
			$blog_id = wpmu_create_blog( $newdomain, $path, 'temp title', $current_user->ID, array( 'public' => 1 ) );
		}
	}
	 
	//Output alerts
	foreach ( $messages[ 'updates' ] as $update ) {
		$this->_parent->alert( $update );
	}
	foreach ( $messages[ 'errors' ] as $error ) {
		$this->_parent->alert( $error, true );
	}
	if ( count( $messages[ 'errors' ] ) > 0 ) {
		$errors = true;
		require_once( 'step1.php' );
	}
	
} //end add site
if ( !$errors ) :
$form_url = add_query_arg( array(
	'step' => '3',
	'action' => 'step3'
) , $form_url );
?>
<h3><?php esc_html_e( 'Step 2 - Site to import into', 'it-l10n-backupbuddy' ); ?></h3>
<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>
<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row"><?php _e( 'Site address', 'it-l10n-backupbuddy' ) ?></th>
		<td>
		<p><?php esc_html_e( 'Migrate BackupBuddy content into this site?', 'it-l10n-backupbuddy' ); ?></p>
		<p>
		<?php 
		if ( is_subdomain_install() ) { 
			if ( domain_exists( $blog_domain, '/', $current_blog->blog_id ) ) {
				$newdomain = $blog_domain;
				$path = $blog_domain . '/';
			} else {
				$path = $domain . '.' . preg_replace( '|^www\.|', '', $current_blog->domain );
			}
		
			
			?>
			<?php echo '<strong>http://' . $path . '</strong>'; ?>
		<?php } else {
			echo 'http://' . $current_blog->domain . '<strong>' . $path . '</strong>'; ?>
		<?php }?></p>
		</td>
	</tr>
</table>
<input type='hidden' name='blog_id' value='<?php echo esc_attr( absint( $blog_id ) ); ?>' />
<input type='hidden' name='blog_path' value='<?php echo esc_attr( $path ); ?>' />
<?php submit_button( __('Proceed'), 'primary', 'add-site' ); ?>
</form>
<?php endif; ?>