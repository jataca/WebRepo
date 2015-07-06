<?php
/*
Plugin Name: Simple Google Checkout Shopping Cart
Plugin URI: http://www.jasoncapshaw.com/blog/simple-google-checkout-cart-wordpress-plugin/
Description: Insert Google Cart Buttons Easily
Version: 1.1
Author: Jason Capshaw
Author URI: http://www.jasoncapshaw.com/
*/

/*  Copyright 2008  Jason Capshaw  (email : jason@mywebtronics.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/







function google_checkout($html) {

$regex = '~\[g(.+?)\]~s';
preg_match_all($regex,$html,$attributes);

 
//print_r($attributes);
foreach ($attributes[0] as $digit) {

	$gtitle = gtitle($digit);
	
	$gprice = gprice($digit);
	$price ="<strong>Price: $ ".$gprice."</strong>";
	}

$form = "<div class=\"product\">".$price."<input value=\"".$gtitle."\" class=\"product-title\" type=\"hidden\"><input value=\"".$gprice."\" class=\"product-price\" type=\"hidden\"><div title=\"Add to cart\" role=\"button\" tabindex=\"0\" class=\"googlecart-add-button\"></div></div><br />";
 


	
return preg_replace($regex,$form,$html);

}

//Get the title of product for Google
function gtitle($string) {
$regex = '~gtitle=(.+?);~s';
preg_match_all($regex,$string,$attributes);

foreach ($attributes[1] as $digit) {
		
		return $digit;
		
	}

}


function gprice($string) {
$regex = '~gprice=(.+?);~s';
preg_match_all($regex,$string,$attributes);
foreach ($attributes[1] as $digit) {
		return $digit;
	}
}




add_filter('the_content', 'google_checkout');

?>
