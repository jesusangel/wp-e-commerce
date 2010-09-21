<?php

/*
  Plugin Name: WP e-Commerce
  Plugin URI: http://getshopped.org/
  Description: A plugin that provides a WordPress Shopping Cart. See also: <a href="http://getshopped.org" target="_blank">GetShopped.org</a> | <a href="http://getshopped.org/forums/" target="_blank">Support Forum</a> | <a href="http://getshopped.org/resources/docs/" target="_blank">Documentation</a>
  Version: 3.8 Development
  Author: Instinct Entertainment
  Author URI: http://getshopped.org/
 */

/**
 * WP e-Commerce Main Plugin File
 * @package wp-e-commerce
 */

// Define the path to the plugin folder
define( 'WPSC_FILE_PATH', dirname( __FILE__ ) );
define( 'WPSC_DIR_NAME', basename( WPSC_FILE_PATH ) );

// Define Plugin version
define( 'WPSC_VERSION', '3.8' );
define( 'WPSC_MINOR_VERSION', ( '00000' . microtime( true ) ) );
define( 'WPSC_PRESENTABLE_VERSION', '3.8 Development' );

// Define Debug Variables for developers
define( 'WPSC_DEBUG', false );
define( 'WPSC_GATEWAY_DEBUG', false );

// Define the URL to the plugin folder
define( 'WPSC_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
define( 'WPSC_URL', plugins_url( '', __FILE__ ) );

// Define globals and constants used by wp-e-commerce
require_once( WPSC_FILE_PATH . '/wpsc-core/globals.php' );
require_once( WPSC_FILE_PATH . '/wpsc-core/constants.php' );

/**
 * wpsc_load_plugin()
 *
 * The main WP e-Commerce loader function
 *
 * @global object $wp_query
 * @global object $wpdb
 * @global object $wpsc_query
 * @global array $wpsc_purchlog_statuses
 * @global object $wpsc_gateways
 * @global array $wpsc_page_titles
 * @global object $wpsc_shipping_modules
 * @global object $nzshpcrt_gateways
 * @global string $wp_version
 * @global object $purchlogitem
 * @global array $gateway_checkout_form_fields
 */
function wpsc_load_plugin() {
	// Phew look at all these globals!
	global $wp_query, $wpdb, $wpsc_query, $wpsc_purchlog_statuses, $wpsc_gateways;
	global $wpsc_page_titles, $wpsc_shipping_modules, $nzshpcrt_gateways;
	global $wp_version, $purchlogitem, $gateway_checkout_form_fields;

	do_action( 'wpsc_before_init' );

	$wpsc_purchlog_statuses = array(
		array(
			'internalname' => 'incomplete_sale',
			'label'        => 'Incomplete Sale',
			'order'        => 1,
		),
		array(
			'internalname' => 'order_received',
			'label'        => 'Order Received',
			'order'        => 2,
		),
		array(
			'internalname'   => 'accepted_payment',
			'label'          => 'Accepted Payment',
			'is_transaction' => true,
			'order'          => 3,
		),
		array(
			'internalname'   => 'job_dispatched',
			'label'          => 'Job Dispatched',
			'is_transaction' => true,
			'order'          => 4,
		),
		array(
			'internalname'   => 'closed_order',
			'label'          => 'Closed Order',
			'is_transaction' => true,
			'order'          => 5,
		),
	);

	// Add image sizes for products
	add_image_size( 'product-thumbnails', get_option( 'product_image_width' ), get_option( 'product_image_height' ), TRUE );
	add_image_size( 'admin-product-thumbnails', 38, 38, TRUE );
	add_image_size( 'featured-product-thumbnails', 540, 260, TRUE );

	// Include the rest of the Plugin files
	require_once( WPSC_FILE_PATH . '/wpsc-core/functions.php' );
	require_once( WPSC_FILE_PATH . '/wpsc-core/includes.php' );

	do_action( 'wpsc_core_included' );
	add_action( 'init', 'wpsc_register_post_types', 8 ); // highest priority
	add_action( 'template_redirect', 'wpsc_start_the_query', 8 );

	/*
	 * This part gets the merchants from the merchants directory and
	 * needs to search the merchants directory for merchants, the code to do this starts here
	 */
	$gateway_directory      = WPSC_FILE_PATH . '/merchants';
	$nzshpcrt_merchant_list = wpsc_list_dir( $gateway_directory );

	foreach ( $nzshpcrt_merchant_list as $nzshpcrt_merchant ) {
		if ( stristr( $nzshpcrt_merchant, '.php' ) ) {
			require( WPSC_FILE_PATH . '/merchants/' . $nzshpcrt_merchant );
		}
	}

	$nzshpcrt_gateways = apply_filters( 'wpsc_merchants_modules', $nzshpcrt_gateways );
	uasort( $nzshpcrt_gateways, 'wpsc_merchant_sort' );

	// make an associative array of references to gateway data.
	$wpsc_gateways = array();
	foreach ( (array)$nzshpcrt_gateways as $key => $gateway )
		$wpsc_gateways[$gateway['internalname']] = &$nzshpcrt_gateways[$key];

	/*
	 * This part gets the shipping modules from the shipping directory and
	 * needs to search the shipping directory for modules
	 */
	$shipping_directory = WPSC_FILE_PATH . '/shipping';
	$nzshpcrt_shipping_list = wpsc_list_dir( $shipping_directory );
	foreach ( $nzshpcrt_shipping_list as $nzshpcrt_shipping ) {
		if ( stristr( $nzshpcrt_shipping, '.php' ) ) {
			require( WPSC_FILE_PATH . '/shipping/' . $nzshpcrt_shipping );
		}
	}
	$wpsc_shipping_modules = apply_filters( 'wpsc_shipping_modules', $wpsc_shipping_modules );

	// Set page title array for important WPSC pages
	$wpsc_page_titles = wpsc_get_page_post_names();

	// Set the selected theme if need be
	if ( ( get_option( 'wpsc_selected_theme' ) != '' ) && ( file_exists( WPSC_FILE_PATH . '/themes/' . get_option( 'wpsc_selected_theme' ) . "/" . get_option( 'wpsc_selected_theme' ) . ".php" ) ) )
		include_once( WPSC_FILE_PATH . '/themes/' . get_option( 'wpsc_selected_theme' ) . '/' . get_option( 'wpsc_selected_theme' ) . '.php' );

	// Refresh page urls when permalinks are turned on or altered
	add_filter( 'mod_rewrite_rules', 'wpsc_refresh_page_urls' );

	// Setup the cart location
	switch ( get_option( 'cart_location' ) ) {
		case 2 :
			add_action( 'the_content', 'nzshpcrt_shopping_basket', 14 );
			break;

		case 1 :
		default:
			break;
	}

	if ( is_ssl() ) {

		function wpsc_add_https_to_page_url_options( $url ) {
			return str_replace( "http://", "https://", $url );
		}
		add_filter( 'option_product_list_url',  'wpsc_add_https_to_page_url_options' );
		add_filter( 'option_shopping_cart_url', 'wpsc_add_https_to_page_url_options' );
		add_filter( 'option_transact_url',      'wpsc_add_https_to_page_url_options' );
		add_filter( 'option_user_account_url',  'wpsc_add_https_to_page_url_options' );
	}


	do_action( 'wpsc_init' );
}
// after init and after when the wp query string is parsed but before anything is displayed
add_action( 'plugins_loaded', 'wpsc_load_plugin', 8 );
add_action( 'plugins_loaded', 'wpsc_initialisation', 8 );

// Start the wpsc session
wpsc_start_session();

register_activation_hook( __FILE__, 'wpsc_install' );

?>
