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

// preserved for backwards compatibility
function nzshpcrt_shopping_basket( $input = null, $override_state = null ) {
	return wpsc_shopping_cart( $input, $override_state );
}

/**
 * Filter: wpsc-purchlogitem-links-start
 *
 * This filter has been deprecated and replaced with one that follows the
 * correct naming conventions with underscores.
 *
 * @since 3.7.6rc2
 */
function wpsc_purchlogitem_links_start_deprecated() {	
	do_action( 'wpsc-purchlogitem-links-start' );
}
add_action( 'wpsc_purchlogitem_links_start', 'wpsc_purchlogitem_links_start_deprecated' );


function nzshpcrt_donations($args){
	wpsc_donations($args);
}

/**
 * Latest Product Widget content function
 *
 * Displays the latest products.
 *
 * @todo Make this use wp_query and a theme file (if no theme file present there should be a default output).
 * @todo Remove marketplace theme specific code and maybe replce with a filter for the image output? (not required if themeable as above)
 * @todo Should this latest products function live in a different file, seperate to the widget logic?
 *
 * Changes made in 3.8 that may affect users:
 *
 * 1. The product title link text does now not have a bold tag, it should be styled via css.
 * 2. <br /> tags have been ommitted. Padding and margins should be applied via css.
 * 3. Each product is enclosed in a <div> with a 'wpec-latest-product' class.
 * 4. The product list is enclosed in a <div> with a 'wpec-latest-products' class.
 * 5. Function now expects two arrays as per the standard Widget API.
 */
function nzshpcrt_latest_product( $args = null, $instance ) {
	echo wpsc_latest_product( $args, $instance );
}

/**
 * nzshpcrt_currency_display function.
 * Obsolete, preserved for backwards compatibility
 *
 * @access public
 * @param mixed $price_in
 * @param mixed $tax_status
 * @param bool $nohtml deprecated 
 * @param bool $id. deprecated
 * @param bool $no_dollar_sign. (default: false)
 * @return void
 */
function nzshpcrt_currency_display($price_in, $tax_status, $nohtml = false, $id = false, $no_dollar_sign = false) {
	//_deprecated_function( __FUNCTION__, '3.8', 'wpsc_currency_display' );
	$output = wpsc_currency_display($price_in, array(
		'display_currency_symbol' => !(bool)$no_dollar_sign,
		'display_as_html' => (bool)$nohtml,
		'display_decimal_point' => true,
		'display_currency_code' => false
	));
	return $output;
}


function wpsc_include_language_constants(){
	if(!defined('TXT_WPSC_ABOUT_THIS_PAGE'))
		include_once(WPSC_FILE_PATH.'/wpsc-languages/EN_en.php');
}
add_action('init','wpsc_include_language_constants');

if(!function_exists('wpsc_has_noca_message')){
	function wpsc_has_noca_message(){
		if(isset($_SESSION['nocamsg']) && isset($_GET['noca']) && $_GET['noca'] == 'confirm')
			return true;
		else
			return false;
	}
}

if(!function_exists('wpsc_is_noca_gateway')){
	function wpsc_is_noca_gateway(){
		if(count($wpsc_gateway->wpsc_gateways) == 1 && $wpsc_gateway->wpsc_gateways[0]['name'] == 'Noca')
			return true;
		else
			return false;
	}
}


/**
 * wpsc pagination
 * It is intended to move some of this functionality to a paging class
 * so that paging functionality can easily be created for multiple uses.
 */



/**
 * wpsc current_page
 * @return (int) The current page number
 */
function wpsc_current_page() {
	
	global $wpsc_query;
	
	$current_page = 1;
	
	if ( $wpsc_query->query_vars['page'] > 1) {
		$current_page = $wpsc_query->query_vars['page'];
	}
	
	return $current_page;
	
}

/**
 * wpsc showing products
 * Displays the number of page showing in the form "10 to 20".
 * If only on page is being display it will return the total amount of products showing.
 * @return (string) Number of products showing
 */
function wpsc_showing_products() {
	
	global $wpsc_query;
				
	// If we are using pages...
	if ( ( get_option( 'use_pagination' ) == 1 ) ) {
		$products_per_page = $wpsc_query->query_vars['number_per_page'];
		if ( $wpsc_query->query_vars['page'] > 0 ) {
			$startnum = ( $wpsc_query->query_vars['page'] - 1 ) * $products_per_page;
		} else {
			$startnum = 0;
		}
		return ( $startnum + 1 ) . ' to ' . ( $startnum + wpsc_product_count() );
	}
	
	return wpsc_total_product_count();
	
}

/**
 * wpsc showing products page
 * Displays the number of page showing in the form "5 of 10".
 * @return (string) Number of pages showing.
 */
function wpsc_showing_products_page() {
	
	global $wpsc_query;
	
	$output = $wpsc_query->page_count;
	$current_page = wpsc_current_page();
	
	return $current_page . ' of ' . $output;
	
}

/**
 * wpsc pagination
 * Page numbers as links - limit by passing the $show parameter.
 * @param $show (int) Number of pages to show, -1 shows all. Zero will be used to show default setting in a future release.
 * @return (string) Linked page numbers.
 */
function wpsc_pagination( $show = -1 ) {
	
	global $wpsc_query;
	
	$output = '';
	$start = 1;
	$end   = $wpsc_query->page_count;
	$show  = intval( $show );
	
	$current_page = wpsc_current_page();
	
	if ( $show > 0 ) {
		$start = $current_page - ( floor( $show / 2 ) );
		if ( $start < 1 ) {
			$start = 1;
		}
		$end = $start + $show - 1;
		if ( $end > $wpsc_query->page_count ) {
			$end = $wpsc_query->page_count;
			if ( $end - $show + 1 > 0 ) {
				$start = $end - $show + 1;
			}
		}
	}
	while ( wpsc_have_pages() ) : wpsc_the_page();
		if ( wpsc_page_number() >= $start && wpsc_page_number() <= $end ) {
			$page_url = wpsc_page_url();
			$page_url = wpsc_product_search_url( $page_url );
			if ( wpsc_page_is_selected() ) :
				$output .= '<a href="' . $page_url . '" class="selected">' . wpsc_page_number() . '</a> ';
			else :
				$output .= '<a href="' . $page_url . '">' . wpsc_page_number() . '</a> ';
			endif;
		}
	endwhile;
	
	$wpsc_query->rewind_pages();
	
	return $output;
	
}

/**
 * wpsc product search url
 * Add product_search parameter if required.
 * @param $url (string) URL.
 * @return (string) URL.
 */
function wpsc_product_search_url( $url ) {
			
	if ( isset( $_GET['product_search'] ) ) {
		if ( strrpos( $url, '?') ) {
			$url .= '&product_search=' . $_GET['product_search'];
		} else {
			$url .= '?product_search=' . $_GET['product_search'];
		}
	}
	
	return $url;

}

/**
 * wpsc adjacent products url
 * URL for the next or previous page of products on a category or group page.
 * @param $n (int) Page number.
 * @return (string) URL for the adjacent products page link.
 */
function wpsc_adjacent_products_url( $n ) {
	
	global $wpsc_query;
	
	$current_page = wpsc_current_page();
	
	$n = $current_page + $n;
	
	if ( $n < 1 || $n > $wpsc_query->page_count ) {
		return;
	}
	
	while ( wpsc_have_pages() ) : wpsc_the_page();
		if ( wpsc_page_number() == $n ) {
			$url = wpsc_page_url();
			$url = wpsc_product_search_url( $url );
			$wpsc_query->rewind_pages();
			return $url;
		}
	endwhile;
	
	$wpsc_query->rewind_pages();
	
	return;
	
}

/**
 * wpsc next products link
 * Links to the next page of products on a category or group page.
 * @param $text (string) Link text.
 * @param $show_disabled (bool) Show unlinked text if last page.
 * @return (string) Next page link or text.
 */
function wpsc_next_products_link( $text = 'Next', $show_disabled = false ) {
	
	$page_url = wpsc_adjacent_products_url( 1 );
	
	if ( $page_url ) {
		return '<a href="' . $page_url . '">' . $text . '</a>';
	}
	
	if ( $show_disabled ) {
		return '<span>' . $text . '</span>';
	}
	
	return;
	
}

/**
 * wpsc previous products link
 * Links to the previous page of products on a category or group page.
 * @param $text (string) Link text.
 * @param $show_disabled (bool) Show unlinked text if first page.
 * @return (string) Previous page link or text.
 */
function wpsc_previous_products_link( $text = 'Previous', $show_disabled = false ) {
	
	$page_url = wpsc_adjacent_products_url( -1 );
	
	if ( $page_url ) {
		return '<a href="' . $page_url . '">' . $text . '</a>';
	}
	
	if ( $show_disabled ) {
		return '<span>' . $text . '</span>';
	}
	
	return;
	
}

/**
 * wpsc first products link
 * Links to the first page of products on a category or group page.
 * @param $text (string) Link text.
 * @param $show_disabled (bool) Show unlinked text if last page.
 * @return (string) First page link or text.
 */
function wpsc_first_products_link( $text = 'First', $show_disabled = false ) {
	
	global $wpsc_query;
	
	$page_url = '';
	
	while ( wpsc_have_pages() ) : wpsc_the_page();
		$page_url = wpsc_page_url();
		break;
	endwhile;
	
	$wpsc_query->rewind_pages();
	
	$page_url = wpsc_product_search_url( $page_url );
	
	if ( $page_url && wpsc_current_page() > 1 ) {
		return '<a href="' . $page_url . '">' . $text . '</a>';
	}
	
	if ( $show_disabled ) {
		return '<span>' . $text . '</span>';
	}
	
	return;
	
}

/**
 * wpsc last products link
 * Links to the last page of products on a category or group page.
 * @param $text (string) Link text.
 * @param $show_disabled (bool) Show unlinked text if first page.
 * @return (string) Last page link or text.
 */
function wpsc_last_products_link( $text = 'Last', $show_disabled = false ) {
	
	global $wpsc_query;
	
	$page_url = '';
	
	while ( wpsc_have_pages() ) : wpsc_the_page();
		$page_url = wpsc_page_url();
	endwhile;
	
	$wpsc_query->rewind_pages();
	
	$page_url = wpsc_product_search_url( $page_url );
	
	if ( $page_url && wpsc_current_page() < $wpsc_query->page_count ) {
		return '<a href="' . $page_url . '">' . $text . '</a>';
	}
	
	if ( $show_disabled ) {
		return '<span>' . $text . '</span>';
	}
	
	return;
	
}



?>