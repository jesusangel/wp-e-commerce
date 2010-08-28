<?php
/*
Plugin Name: WP e-Commerce
Plugin URI: http://getshopped.org/
Description: A Plugin that provides a WordPress Shopping Cart. See also: <a href="http://getshopped.org" target="_blank">GetShopped.org</a> | <a href="http://getshopped.org/forums/" target="_blank">Support Forum</a> | <a href="http://getshopped.org/resources/docs/" target="_blank">Documentation</a>
Version: 3.8 Development
Author: Instinct Entertainment
Author URI: http://getshopped.org/
*/
/**
 * WP e-Commerce Main Plugin File
 * @package wp-e-commerce
*/
global $wpdb;
//Define the path to the plugin folder
define('WPSC_FILE_PATH', dirname(__FILE__));
define('WPSC_DIR_NAME', basename(WPSC_FILE_PATH));

//Define Plugin version
define('WPSC_VERSION', '3.8');	
define('WPSC_MINOR_VERSION', ('00000'.microtime(true)));
define('WPSC_PRESENTABLE_VERSION', '3.8 Development');

//Define Debug Variables for developers
define('WPSC_DEBUG', false);
define('WPSC_GATEWAY_DEBUG', false);

//Define the URL to the plugin folder
define('WPSC_FOLDER', dirname(plugin_basename(__FILE__)));
define('WPSC_URL', plugins_url('',__FILE__));


//Define other constants used by wp-e-commerce
require_once( WPSC_FILE_PATH. '/wpsc-core/define.constants.php' );

function wpsc_load_plugin(){
	global $wp_query,$wpdb, $wpsc_query,$wpsc_purchlog_statuses,$wpsc_gateways,$wpsc_page_titles,$wpsc_shipping_modules,$nzshpcrt_gateways, $wp_version,$purchlogitem, $gateway_checkout_form_fields;
	

	do_action('wpsc_before_init');
	$wpsc_purchlog_statuses = array(
          array(
             'internalname' => 'incomplete_sale'  ,
              'label' => 'Incomplete Sale',
              'order' => 1,
           ),
          array(
              'internalname' =>'order_received',
              'label' => 'Order Received',
              'order' => 2,
         ),

         array(
             'internalname' => 'accepted_payment',
              'label' => 'Accepted Payment',
              'is_transaction' => true,
              'order' => 3,
         ),
          array(
             'internalname' => 'job_dispatched',
              'label' => 'Job Dispatched',
              'is_transaction' => true,
              'order' => 4,
           ),
           array(
             'internalname' => 'closed_order',
              'label' => 'Closed Order',
            'is_transaction' => true,
              'order' => 5,
           ),
         );
    //  exit('With:'.get_option('product_image_width').' height'.get_option('product_image_height'));    
	add_image_size( 'product-thumbnails', get_option('product_image_width'), get_option('product_image_height'), TRUE ); 
    add_image_size( 'admin-product-thumbnails', 38,38, TRUE ); 
    add_image_size( 'featured-product-thumbnails',540,260, TRUE);
	//Include the rest of the Plugin files
	require_once( WPSC_FILE_PATH. '/wpsc-core/include.files.php' );
	do_action('wpsc_core_included');
	add_action( 'init', 'wpsc_register_post_types', 8 ); // highest priority
	add_action('template_redirect', 'wpsc_start_the_query', 8);

	
	/* 
	 * This plugin gets the merchants from the merchants directory and
	 * needs to search the merchants directory for merchants, the code to do this starts here
	 */
	$gateway_directory = WPSC_FILE_PATH.'/merchants';
	$nzshpcrt_merchant_list = wpsc_list_dir($gateway_directory);

	$num=0;
	foreach($nzshpcrt_merchant_list as $nzshpcrt_merchant) {
	  if(stristr( $nzshpcrt_merchant , '.php' )) {
	    //echo $nzshpcrt_merchant;
	    require(WPSC_FILE_PATH."/merchants/".$nzshpcrt_merchant);
		}
	  $num++;
	}

	$nzshpcrt_gateways = apply_filters('wpsc_merchants_modules',$nzshpcrt_gateways);
	uasort($nzshpcrt_gateways, 'wpsc_merchant_sort');
	
	// make an associative array of references to gateway data.
	$wpsc_gateways = array(); 
	foreach((array)$nzshpcrt_gateways as $key => $gateway) {
		$wpsc_gateways[$gateway['internalname']] = &$nzshpcrt_gateways[$key];
	}

	/* 
	 * and ends here
	 */
	
		 
	/* 
	 * This plugin gets the shipping modules from the shipping directory and
	 * needs to search the shipping directory for modules
	 */
	$shipping_directory = WPSC_FILE_PATH.'/shipping';
	$nzshpcrt_shipping_list = wpsc_list_dir($shipping_directory);
	foreach($nzshpcrt_shipping_list as $nzshpcrt_shipping) {
		if(stristr( $nzshpcrt_shipping , '.php' )) {
			require(WPSC_FILE_PATH."/shipping/".$nzshpcrt_shipping);
		}
	}
	$wpsc_shipping_modules = apply_filters('wpsc_shipping_modules',$wpsc_shipping_modules);
	
	
	// set page title array for important WPSC pages 
	$wpsc_page_titles = wpsc_get_page_post_names();

	$theme_path = WPSC_FILE_PATH . '/themes/';
	if((get_option('wpsc_selected_theme') != '') && (file_exists($theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".php") )) {    
	  include_once(WPSC_FILE_PATH.'/themes/'.get_option('wpsc_selected_theme').'/'.get_option('wpsc_selected_theme').'.php');
	}

	//* heres where we start running our functions *//

	add_action('init', 'widget_wp_shopping_cart_init', 10);
	
	
	// refresh page urls when permalinks are turned on or altered
	add_filter('mod_rewrite_rules', 'wpsc_refresh_page_urls');
	
	switch(get_option('cart_location')) {
	  case 1:
	  add_action('wp_list_pages','nzshpcrt_shopping_basket');
	  break;
	  
	  case 2:
	  add_action('the_content', 'nzshpcrt_shopping_basket' , 14);
	  break;
	  
	  default:
	  break;
	}
	
	if(is_ssl()) {
		function wpsc_add_https_to_page_url_options($url) {
			return str_replace("http://", "https://", $url);
		}
		add_filter('option_product_list_url', 'wpsc_add_https_to_page_url_options');
		add_filter('option_shopping_cart_url', 'wpsc_add_https_to_page_url_options');
		add_filter('option_transact_url', 'wpsc_add_https_to_page_url_options');
		add_filter('option_user_account_url', 'wpsc_add_https_to_page_url_options');
	}
	add_action('shutdown','wpsc_serialize_shopping_cart');
	
	do_action('wpsc_init');
}

// after init and after when the wp query string is parsed but before anything is displayed

add_action('plugins_loaded','wpsc_load_plugin', 8);
add_action('plugins_loaded','wpsc_initialisation', 8);

function wpsc_check_thumbnail_support() {
	if (!current_theme_supports('post-thumbnails')) {
		add_theme_support( 'post-thumbnails' ); 
		add_action('init','wpsc_remove_post_type_thumbnail_support');
	}
}
add_action('after_setup_theme','wpsc_check_thumbnail_support',99);

function wpsc_remove_post_type_thumbnail_support() {
		remove_post_type_support('post','thumbnail');
		remove_post_type_support('page','thumbnail');
}

include_once(WPSC_FILE_PATH."/wpsc-includes/install_and_update.functions.php");

register_activation_hook(__FILE__, 'wpsc_install');

/**
 * Check to see if the session exists, if not, start it
 */

if (!isset($_SESSION)) $_SESSION = null;

if((!is_array($_SESSION)) xor (!isset($_SESSION['nzshpcrt_cart'])) xor (!$_SESSION)) {
  session_start();
}


/**
 * Update Notice
 *
 * Displays an update message below the auto-upgrade link in the WordPress admin
 * to notify users that they should check the upgrade information and changelog
 * before upgrading in case they need to may updates to their theme files.
 *
 * @package wp-e-commerce
 * @since 3.7.6.1
 */
function wpsc_update_notice() {
	$info_title = __( 'Please Note', 'wpsc' );
	$info_text = sprintf( __( 'Before upgrading you should check the <a %s>upgrade information</a> and changelog as you may need to make updates to your template files.', 'wpsc' ), 'href="http://getshopped.org/resources/docs/upgrades/staying-current/" target="_blank"' );
	echo '<div style="border-top:1px solid #CCC; margin-top:3px; padding-top:3px; font-weight:normal;"><strong style="color:#CC0000">' . strip_tags( $info_title ) . '</strong>: ' . strip_tags( $info_text, '<br><a><strong><em><span>' ) . '</div>';
}

if ( is_admin() ) {
	add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), 'wpsc_update_notice' );
}



?>
