<?php

/**
 * nzshpcrt_get_gateways()
 *
 * Deprecated function for returning the merchants global
 *
 * @global array $nzshpcrt_gateways
 * @return array
 * @todo Actually correctly deprecate this
 */
function nzshpcrt_get_gateways() {
	global $nzshpcrt_gateways;

	if ( !is_array( $nzshpcrt_gateways ) )
		wpsc_core_load_gateways();

	return $nzshpcrt_gateways;

}

/**
 * wpsc_merchants_modules_deprecated()
 *
 * Deprecated function for merchants modules
 *
 */
function wpsc_merchants_modules_deprecated($nzshpcrt_gateways){

	$nzshpcrt_gateways = apply_filters( 'wpsc_gateway_modules', $nzshpcrt_gateways );
	return $nzshpcrt_gateways;
//	return apply_filters('wpsc_gateway_modules', $nzshpcrt_gateways);
}
add_filter('wpsc_merchants_modules','wpsc_merchants_modules_deprecated',1);

/**
 * nzshpcrt_price_range()
 * Deprecated
 * Alias of Price Range Widget content function
 *
 * Displays a list of price ranges.
 *
 * @param $args (array) Arguments.
 */
function nzshpcrt_price_range($args){
	wpsc_price_range($args);
}

?>
