<?php
/**
* Generate relative date
*
* Function call to create a string containing a requested relative date calculation
*
* @package	ArtissRelativeDate
* @since	1.0
*
* @param	string	$para1		First parameter (optional)
* @param	string	$para2		Second parameter (optional)
* @param	string	$para3		Third parameter (optional)
* @param	string	$para4		Fourth parameter (optional)
* @return	string				Relative date
*/

function ard_generate_date_code( $para1, $para2, $para3, $para4 ) {

	// Transfer parameters into an array

	$para[ 1 ] = $para1;
	$para[ 2 ] = $para2;
	$para[ 3 ] = $para3;
	$para[ 4 ] = $para4;

	// Work out current date, adjusted to GMT offset of user's WP installation

	$time_gmt_adjusted = gmmktime() + ( get_option( 'gmt_offset' ) * 3600 );

	// Set initial parameter values

	$date_num = 1;
	$depth = 2;
	$divider = '';
	$date = array( 1 => get_the_time( 'U' ), 2 => $time_gmt_adjusted );

	// Read array and extract parameters

	foreach ($para as $para_value) {
		if ( $para_value != '' ) {
			if ( is_numeric( $para_value ) ) {
				if ( ( $para_value == 1 ) or ( $para_value == 2 ) ) {
					$depth = $para_value;
				} else {
					if  ( $date_num == 3 ) { return ard_report_error( __( 'More than 2 dates have been specified', 'wp-relative-date' ), 'Artiss Relative Date', false ); }
					$date[ $date_num ] = $para_value;
					$date_num++;
				}
			} else {
				if ( $divider != '' ) { return ard_report_error( __( 'More than 1 divider was specified', 'wp-relative-date' ), 'Artiss Relative Date', false ); }
				$divider = $para_value;
			}
		}
	}

	// Set default divider, if nothing is specified

	if ( $divider == '' ) { $divider = ', '; } else { $divider = htmlspecialchars( $divider ); }

	// Work out which date is greater and subtract appropriately

	if ( $date[ 1 ] > $date[ 2 ] ) {
		$diff = $date[ 1 ] - $date[ 2 ];
	} else {
		$diff = $date[ 2 ] - $date[ 1 ];
	}

	// Work out how many years, months, etc, there are between the dates

	$years	 = floor( $diff / 31449600 );
	$diff	-= $years * 31449600;           // Seconds in a year
	$months	 = floor( $diff / 2620800 );
	$diff	-= $months * 2620800;           // Seconds in a month (assumes 4.3r weeks in month)
	$weeks	 =  floor( $diff / 604800 );
	$diff	-= $weeks * 604800;             // seconds in a week
	$days	 =  floor( $diff / 86400 );
	$diff	-= $days * 86400;               // seconds in a day
	$hours	 =  floor( $diff / 3600 );
	$diff	-= $hours * 3600;               // seconds in an hour
	$minutes = floor( $diff / 60 );
	$diff	-= $minutes * 60;               // seconds in a minute
	$seconds = $diff;

	$relative_date = '';

	// Now output the results

	if ( $years > 0 ) {

		// Years and Months

		$relative_date .= ( $relative_date?$divider:'' ) . $years . ' year' . ( $years>1?'s':'' );
		if ( ( $months > 0 ) && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $months . ' month' . ( $months>1?'s':'' ); }
		if ( ( $months = 0 ) && ( $weeks > 0 ) && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $weeks . ' week' . ( $weeks>1?'s':'' ); }

	} elseif ( $months > 0 ) {

		// Months and weeks

		$relative_date .= ( $relative_date?$divider:'' ) . $months . ' month' . ( $months>1?'s':'' );
		if ( ( $weeks > 0 ) && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $weeks . ' week' . ( $weeks>1?'s':'' ); }
		if ( ( $weeks = 0 ) && ( $days > 0 ) && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $days . ' day' . ( $days>1?'s':'' ); }

	} elseif ( $weeks > 0 ) {

		// Weeks and days

		$relative_date .= ( $relative_date?$divider:'' ) . $weeks . ' week' . ( $weeks>1?'s':'' );
		if ( ( $days > 0 ) && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $days . ' day'.( $days>1?'s':'' );}

	} elseif ( $days > 0 ) {

		// days and hours

		$relative_date .= ( $relative_date?$divider:'' ) . $days . ' day' . ( $days>1?'s':'' );
		if ( ( $hours > 0 ) && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $hours . ' hr' . ( $hours>1?'s':'' ); }

	} elseif ( $hours > 0 ) {

		// hours and minutes

		$relative_date .= ( $relative_date?$divider:'' ) . $hours . ' hour' . ( $hours>1?'s':'' );
		if ( ( $minutes > 0 )  && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $minutes . ' min' . ( $minutes>1?'s':'' ); }

	} elseif ( $minutes > 0 ) {

		// minutes and seconds

		$relative_date .= ( $relative_date?$divider:'' ) . $minutes . ' minute' . ( $minutes>1?'s':'' );
		if ( ( $seconds > 0 ) && ( $depth == 2 ) ) { $relative_date .= ( $relative_date?$divider:'' ) . $seconds . ' sec' . ( $seconds>1?'s':'' ); }

	} else {

		// seconds only

		$relative_date .= ( $relative_date?$divider:'' ) . $seconds . ' second' . ( $seconds>1?'s':'' );
	}

	return $relative_date;
}

/**
* Report an error (1.4)
*
* Function to report an error
*
* @since	1.0
*
* @param	$error			string	Error message
* @param	$plugin_name	string	The name of the plugin
* @param	$echo			string	True or false, depending on whether you wish to return or echo the results
* @return					string	True
*/

function ard_report_error( $error, $plugin_name, $echo = true ) {

	$output = '<p style="color: #f00; font-weight: bold;">' . $plugin_name . ': ' . $error . "</p>\n";

	if ( $echo ) {
		echo $output;
		return true;
	} else {
		return $output;
	}

}
?>