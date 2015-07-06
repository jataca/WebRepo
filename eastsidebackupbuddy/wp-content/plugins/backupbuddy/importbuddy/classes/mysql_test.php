<?php
$connect_status = '<font color=red>Failed</font>';
$connect_status_error = '';
$select_status = 'N/A';
$select_status_error = '';
$overall_status = '<font color=red>Failed</font>';
$existing_status = 'N/A';

if ( false === @mysql_connect( $_POST['server'], $_POST['user'], $_POST['pass'] ) ) { // Couldnt connect to server or invalid credentials.
	$connect_status = '<font color=red>Failed</font>';
	$connect_status_error = mysql_error();
	$this->log( 'mysql ajax test FAILED: Connection failed. Error: ' . mysql_error(), true );
} else {
	$connect_status = 'Success';
	if ( false === @mysql_select_db( $_POST['name'] ) ) {
		$select_status = '<font color=red>Failed</font>';
		$select_status_error = mysql_error();
		$this->log( 'mysql ajax test FAILED: Connected but database access denied. Error: ' . mysql_error(), true );
	} else {
		$select_status = 'Success';
		
		// Check number of tables already existing with this prefix.
		$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $_POST['prefix'] ) . "%'" );
		if ( mysql_num_rows( $result ) > 0 ) {
			$this->log( 'Database already contains a WordPress installation with this prefix (' . mysql_num_rows( $result ) . ' tables). Restore halted.', 'error' );
			$existing_status = '<font color=red>Failed</font>';
			$exiting_status_error = mysql_error();
		} else {
			$existing_status = 'Success';
			$overall_status = 'Success';
			$this->log( 'mysql ajax test SUCCESS' );
		}
		unset( $result );
	}
}

echo '1. Logging in to server ... ' . $connect_status . '.<br>';
if ( $connect_status != 'Success' ) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: ' . $connect_status_error . '<br>';;
}

echo '2. Verifying database access & permission ... ' . $select_status . '.<br>';
if ( ( $select_status != 'Success' ) && ( $select_status != 'N/A' ) ) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: ' . $select_status_error . '<br>';
}

if ( ( $existing_status != 'Success' ) && ( $_POST['wipe_database'] != '1' ) ) {
	$existing_status = '<font color=red>Warning</font>';
	if ( ( $connect_status == 'Success' ) && ( $select_status == 'Success' ) ) {
		$overall_status = '<font color=red>Warning</font>';
	}
}
echo '3. Verifying no existing WP data ... ' . $existing_status . '.<br>';
if ( $select_status == 'N/A' ) {
	//echo '&nbsp;&nbsp;&nbsp;&nbsp;N/A';
} else {
	if ( $existing_status = 'Warning' ) {
		if ( $_POST['wipe_database'] != '1' ) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;WordPress already exists in this database with this prefix.<br>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;It will be wiped prior to import on the next step. Use caution.<br>';
		} elseif ( $existing_status != 'Success' ) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: WordPress already exists in this database with this prefix.<br>';
		}
	}
}
echo '4. Overall mySQL test result ... ' . $overall_status . '.<br>';


die();
?>