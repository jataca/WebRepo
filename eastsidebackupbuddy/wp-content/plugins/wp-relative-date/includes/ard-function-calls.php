<?php
/**
* Function Calls
*
* Various functions, open for user access
*
* @package	Artiss-Relative-Date
*/

/**
* Output relative date
*
* Function call to output the results of a requested relative date calculation
*
* @package	ArtissRelativeDate
* @since	1.0
*
* @param	string	$para1		First parameter (optional)
* @param	string	$para2		Second parameter (optional)
* @param	string	$para3		Third parameter (optional)
* @param	string	$para4		Fourth parameter (optional)
*/

function relative_date( $para1 = '', $para2 = '', $para3 = '', $para4 = '' ) {
	echo ard_generate_date_code( $para1, $para2, $para3, $para4 );
	return;
}

/**
* Return relative date
*
* Function call to return the results of a requested relative date calculation
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

function get_relative_date( $para1 = '', $para2 = '', $para3 = '', $para4 = '' ) {
	return ard_generate_date_code( $para1, $para2, $para3, $para4 );
}
?>