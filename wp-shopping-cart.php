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
 * WP_eCommerce
 *
 * Main WPEC Plugin Class
 * 
 * @package wp-e-commerce
 */
class WP_eCommerce {

	/**
	 * Start WPEC on plugins loaded
	 */
	function WP_eCommerce() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 8 );
	}

	/**
	 * Takes care of loading up WPEC
	 */
	function init() {
		$this->start();
		$this->constants();
		$this->includes();
		$this->setup();
		$this->cart();
		$this->theme();
	}

	/**
	 * Initialize the basic WPEC constants
	 */
	function start() {
		// Set the core file path
		define( 'WPSC_FILE_PATH', dirname( __FILE__ ) );

		// Define the path to the plugin folder
		define( 'WPSC_DIR_NAME', basename( WPSC_FILE_PATH ) );

		// Define the URL to the plugin folder
		define( 'WPSC_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
		define( 'WPSC_URL', plugins_url( '', __FILE__ ) );

		// Finished starting
		do_action( 'wpsc_started' );
	}

	/**
	 * Setup the WPEC core constants
	 */
	function constants() {
		// Define globals and constants used by wp-e-commerce
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-constants.php' );

		// Load the WPEC core constants
		wpsc_core_constants();

		// Is WordPress Multisite
		wpsc_core_is_multisite();

		// Start the wpsc session
		wpsc_core_load_session();

		// Which version of WPEC
		wpsc_core_constants_version_processing();

		// WPEC Table names and related constants
		wpsc_core_constants_table_names();

		// Uploads directory info
		wpsc_core_constants_uploads();

		do_action( 'wpsc_constants' );
	}

	/**
	 * Include the rest of WPEC's files
	 */
	function includes() {
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-functions.php' );
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-installer.php' );
		require_once( WPSC_FILE_PATH . '/wpsc-core/wpsc-includes.php' );
	}

	/**
	 * Setup the WPEC core
	 */
	function setup() {
		do_action( 'wpsc_before_init' );

		// Load the thumbnail sizes
		wpsc_core_load_thumbnail_sizes;

		// Load the purchase log statuses
		wpsc_core_load_purchase_log_statuses();

		// Load the gateways
		wpsc_core_load_gateways();

		// Load the shipping modules
		wpsc_core_load_shipping_modules();

		// Set page title array for important WPSC pages
		wpsc_core_load_page_titles();
	}

	/**
	 * Setup the cart location
	 */
	function cart() {
		if ( 2 == get_option( 'cart_location' ) )
			add_filter( 'the_content', 'wpsc_shopping_cart', 14 );
	}

	/**
	 * Setup the theme
	 */
	function theme() {
		if ( ( get_option( 'wpsc_selected_theme' ) != '' ) && ( file_exists( WPSC_FILE_PATH . '/themes/' . get_option( 'wpsc_selected_theme' ) . "/" . get_option( 'wpsc_selected_theme' ) . ".php" ) ) )
			include_once( WPSC_FILE_PATH . '/themes/' . get_option( 'wpsc_selected_theme' ) . '/' . get_option( 'wpsc_selected_theme' ) . '.php' );
	}
}

// Start WPEC
$wpec = new WP_eCommerce();

register_activation_hook( __FILE__, 'wpsc_install' );

?>
