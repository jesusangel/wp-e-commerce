<?php
/*
Plugin Name:WP Shopping Cart
Plugin URI: http://www.instinct.co.nz
Description: A plugin that provides a WordPress Shopping Cart. Contact <a href='http://www.instinct.co.nz/?p=16#support'>Instinct Entertainment</a> for support. <br />Click here to to <a href='?wpsc_uninstall=ask'>Uninstall</a>.
Version: 3.7 Beta 1
Author: Instinct Entertainment
Author URI: http://www.instinct.co.nz/e-commerce/
*/
/**
 * WP eCommerce Main Plugin File
 * @package wp-e-commerce
*/
// this is to make sure it sets up the table name constants correctly on activation
global $wpdb;

define('WPSC_VERSION', '3.7');
define('WPSC_MINOR_VERSION', '8');

define('WPSC_PRESENTABLE_VERSION', '3.7 Beta 1');

define('WPSC_DEBUG', false);
define('WPSC_GATEWAY_DEBUG', false);

$v1 = str_replace(array('_','-','+'),'.',strtolower($wp_version));
$v1 = str_replace(array('alpha','beta','gamma'), array('a','b','g'), $v1);
$v1 = preg_split("/([a-z]+)/i",$v1,-1, PREG_SPLIT_DELIM_CAPTURE);
array_walk($v1, create_function('&$v', '$v = trim($v,". ");'));

define('IS_WP25', version_compare($v1[0], '2.5', '>=') );
define('IS_WP27', version_compare($v1[0], '2.7', '>=') );

// // we need to know where we are, rather than assuming where we are

//Define the path to the plugin folder
define('WPSC_FILE_PATH', dirname(__FILE__));
define('WPSC_DIR_NAME', basename(WPSC_FILE_PATH));

$siteurl = get_option('siteurl');

//Define the URL to the plugin folder
define('WPSC_FOLDER', dirname(plugin_basename(__FILE__)));
define('WPSC_URL', get_option('siteurl').'/wp-content/plugins/' . WPSC_FOLDER);

if(isset($wpmu_version)) {
    define('IS_WPMU', 1);
}

// include the selected language file
if(get_option('language_setting') != '') {
  require(WPSC_FILE_PATH.'/languages/'.get_option('language_setting'));
} else {
  require(WPSC_FILE_PATH.'/languages/EN_en.php');
}



/**
 * on some servers, these values are empty when the plugin is activated, if it is empty at that moment the tables get moved to the wrong place
 * if this happens, the plugin breaks because when this file runs in the normal scope, it does pick up on the correct table prefix and can no longer find its tables.
 * this could do unusual things on wordpress MU under the same conditions that cause it to break in wordpress on some servers.
*/
$wp_table_prefix = "wp_";
if(!empty($wpdb->prefix)) {
  $wp_table_prefix = $wpdb->prefix;
} else if(!empty($table_prefix)) {
  $wp_table_prefix = $table_prefix;
}

// Define the database table names
define('WPSC_TABLE_CATEGORY_TM', "{$wp_table_prefix}wpsc_category_tm");
define('WPSC_TABLE_ALSO_BOUGHT', "{$wp_table_prefix}wpsc_also_bought");
define('WPSC_TABLE_CART_CONTENTS', "{$wp_table_prefix}wpsc_cart_contents");
define('WPSC_TABLE_CART_ITEM_EXTRAS', "{$wp_table_prefix}wpsc_cart_item_extras");
define('WPSC_TABLE_CART_ITEM_VARIATIONS', "{$wp_table_prefix}wpsc_cart_item_variations");
define('WPSC_TABLE_CHECKOUT_FORMS', "{$wp_table_prefix}wpsc_checkout_forms");
define('WPSC_TABLE_CURRENCY_LIST', "{$wp_table_prefix}wpsc_currency_list");
define('WPSC_TABLE_DOWNLOAD_STATUS', "{$wp_table_prefix}wpsc_download_status");
define('WPSC_TABLE_ITEM_CATEGORY_ASSOC', "{$wp_table_prefix}wpsc_item_category_assoc");
define('WPSC_TABLE_PRODUCT_CATEGORIES', "{$wp_table_prefix}wpsc_product_categories");
define('WPSC_TABLE_PRODUCT_FILES', "{$wp_table_prefix}wpsc_product_files");
define('WPSC_TABLE_PRODUCT_IMAGES', "{$wp_table_prefix}wpsc_product_images");
define('WPSC_TABLE_PRODUCT_LIST', "{$wp_table_prefix}wpsc_product_list");
define('WPSC_TABLE_PRODUCT_ORDER', "{$wp_table_prefix}wpsc_product_order");
define('WPSC_TABLE_PRODUCT_RATING', "{$wp_table_prefix}wpsc_product_rating");
define('WPSC_TABLE_PRODUCT_VARIATIONS', "{$wp_table_prefix}wpsc_product_variations");
define('WPSC_TABLE_PURCHASE_LOGS', "{$wp_table_prefix}wpsc_purchase_logs");
define('WPSC_TABLE_PURCHASE_STATUSES', "{$wp_table_prefix}wpsc_purchase_statuses");
define('WPSC_TABLE_REGION_TAX', "{$wp_table_prefix}wpsc_region_tax");
define('WPSC_TABLE_SUBMITED_FORM_DATA', "{$wp_table_prefixx}wpsc_submited_form_data");
define('WPSC_TABLE_VARIATION_ASSOC', "{$wp_table_prefix}wpsc_variation_assoc");
define('WPSC_TABLE_VARIATION_PROPERTIES', "{$wp_table_prefix}wpsc_variation_properties");
define('WPSC_TABLE_VARIATION_VALUES', "{$wp_table_prefix}wpsc_variation_values");
define('WPSC_TABLE_VARIATION_VALUES_ASSOC', "{$wp_table_prefix}wpsc_variation_values_assoc");
define('WPSC_TABLE_COUPON_CODES', "{$wp_table_prefix}wpsc_coupon_codes");
define('WPSC_TABLE_LOGGED_SUBSCRIPTIONS', "{$wp_table_prefix}wpsc_logged_subscriptions");
define('WPSC_TABLE_PRODUCTMETA', "{$wp_table_prefix}wpsc_productmeta");
define('WPSC_TABLE_CATEGORISATION_GROUPS', "{$wp_table_prefix}wpsc_categorisation_groups");
define('WPSC_TABLE_VARIATION_COMBINATIONS', "{$wp_table_prefix}wpsc_variation_combinations");
define('WPSC_TABLE_CLAIMED_STOCK', "{$wp_table_prefix}wpsc_claimed_stock");


// start including the rest of the plugin here
require_once(WPSC_FILE_PATH.'/wpsc-includes/wpsc_query.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/variations.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/ajax.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/mimetype.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/cart.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/checkout.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/xmlparser.php');
require_once(WPSC_FILE_PATH . '/wpsc-includes/display.functions.php');

require_once(WPSC_FILE_PATH.'/wpsc-includes/theme.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/shortcode.functions.php');
//coupons
require_once(WPSC_FILE_PATH.'/wpsc-includes/coupons.class.php');
//purchaselogs
require_once(WPSC_FILE_PATH.'/wpsc-includes/purchaselogs.class.php');
if (!IS_WP25) {
	require_once(WPSC_FILE_PATH.'/editor.php');
} else { 
	require_once(WPSC_FILE_PATH.'/js/tinymce3/tinymce.php');
}


/// OLD CODE INCLUDED HERE
include_once('wp-shopping-cart.old.php');

// if the gold cart file is present, include it, this must be done before the admin file is included
if(is_file(WPSC_FILE_PATH.'/gold_shopping_cart.php')) {
  require_once(WPSC_FILE_PATH.'/gold_shopping_cart.php');
}

// if we are in the admin section, include the admin code
if(WP_ADMIN == true) {
	require_once(WPSC_FILE_PATH."/wpsc-admin/admin.php");
}


/**
* Code to define where the uploaded files are stored starts here
*/

if(IS_WPMU == 1) {
		$upload_url = get_option('siteurl').'/files';
		$upload_path = ABSPATH.get_option('upload_path');
} else {
	if ( !defined('WP_CONTENT_URL') ) {
			define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
		}
	if ( !defined('WP_CONTENT_DIR') ) {
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content');
	}
	
	$upload_path = WP_CONTENT_DIR."/uploads";
	$upload_url = WP_CONTENT_URL."/uploads";
}

$wpsc_file_dir = "{$upload_path}/wpsc/downloadables/";
$wpsc_preview_dir = "{$upload_path}/wpsc/previews/";
$wpsc_image_dir = "{$upload_path}/wpsc/product_images/";
$wpsc_thumbnail_dir = "{$upload_path}/wpsc/product_images/thumbnails/";
$wpsc_category_dir = "{$upload_path}/wpsc/category_images/";
$wpsc_user_uploads_dir = "{$upload_path}/wpsc/user_uploads/";
$wpsc_cache_dir = "{$upload_path}/wpsc/cache/";


define('WPSC_FILE_DIR', $wpsc_file_dir);
define('WPSC_PREVIEW_DIR', $wpsc_preview_dir);
define('WPSC_IMAGE_DIR', $wpsc_image_dir);
define('WPSC_THUMBNAIL_DIR', $wpsc_thumbnail_dir);
define('WPSC_CATEGORY_DIR', $wpsc_category_dir);
define('WPSC_USER_UPLOADS_DIR', $wpsc_user_uploads_dir);
define('WPSC_CACHE_DIR', $wpsc_cache_dir);


/**
* files that are uploaded as part of digital products are not directly downloaded, therefore there is no need for a URL constant for them
*/

$wpsc_preview_url = "{$upload_url}/wpsc/previews/";
$wpsc_image_url = "{$upload_url}/wpsc/product_images/";
$wpsc_thumbnail_url = "{$upload_url}/wpsc/product_images/thumbnails/";
$wpsc_category_url = "{$upload_url}/wpsc/category_images/";
$wpsc_user_uploads_url = "{$upload_url}/wpsc/user_uploads/";
$wpsc_cache_url = "{$upload_url}/wpsc/cache/";

define('WPSC_PREVIEW_URL', $wpsc_preview_url);
define('WPSC_IMAGE_URL', $wpsc_image_url);
define('WPSC_THUMBNAIL_URL', $wpsc_thumbnail_url);
define('WPSC_CATEGORY_URL', $wpsc_category_url);
define('WPSC_USER_UPLOADS_URL', $wpsc_user_uploads_url);
define('WPSC_CACHE_URL', $wpsc_cache_url);










if(isset($_GET['activate']) && ($_GET['activate'] == 'true')) {
	include_once("install_and_update.php");
 	register_activation_hook(__FILE__, 'nzshpcrt_install'); 
  //add_action('init', 'nzshpcrt_install');
} else if(($current_version_number < WPSC_VERSION ) || (($current_version_number == WPSC_VERSION ) && (get_option('wpsc_minor_version') <= WPSC_MINOR_VERSION))) {
	include_once("install_and_update.php");
	register_activation_hook(__FILE__, 'wpsc_auto_update'); 
  //add_action('init', 'wpsc_auto_update');
}



/**
* Code to define where the uploaded files are stored ends here
*/
function wpsc_start_the_query() {
  global $wp_query, $wpsc_query;
  $wpsc_query = new WPSC_query();
}
// after init and after when the wp query string is parsed but before anything is displayed
add_action('template_redirect', 'wpsc_start_the_query', 0);


/**
 * Check to see if the session exists, if not, start it
 */
if((!is_array($_SESSION)) xor (!isset($_SESSION['nzshpcrt_cart'])) xor (!$_SESSION)) {
  session_start();
}

function wpsc_initialisation() {
  global $wpsc_cart;
  // set the theme directory constant
  $theme_path = WPSC_FILE_PATH . "/themes/";
	if(!file_exists($theme_path.get_option('wpsc_selected_theme'))) {
		$theme_dir = 'default';
	} else {
		$theme_dir = get_option('wpsc_selected_theme');
	}
	define('WPSC_THEME_DIR', $theme_dir);	
  
  
  // initialise the cart session, if it exist, unserialize it, otherwise make it
  if(isset($_SESSION['wpsc_cart'])) {
		$GLOBALS['wpsc_cart'] = unserialize($_SESSION['wpsc_cart']);
		if(get_class($GLOBALS['wpsc_cart']) != "wpsc_cart") {
			$GLOBALS['wpsc_cart'] = new wpsc_cart;
		}
  } else {
    $GLOBALS['wpsc_cart'] = new wpsc_cart;
  }
  
  
//   if(empty($GLOBALS['wpsc_cart']->selected_shipping_method) && (get_option('custom_shipping_options') != null)) {
//     $GLOBALS['wpsc_cart']->get_shipping_method();
//   }
}
// first plugin hook in wordpress
add_action('plugins_loaded','wpsc_initialisation', 0);



  
/**
 * This serializes the shopping cart variable as a backup in case the unserialized one gets butchered by various things
 */  
function wpsc_serialize_shopping_cart() {
  global $wpdb, $wpsc_start_time, $wpsc_cart;
  //@$_SESSION['nzshpcrt_serialized_cart'] = serialize($_SESSION['nzshpcrt_cart']);
  
  $wpsc_cart->errors = array();
  $_SESSION['wpsc_cart'] = serialize($wpsc_cart);
  /// Delete the old claims on stock
//   echo "/*test */";
  //$session_timeout = @session_cache_expire()*60;
  //if($session_timeout <= 0) { 
	$session_timeout = 60*60; // 180 * 60 = three hours in seconds
  $old_claimed_stock_timestamp = time() - $session_timeout;
  $old_claimed_stock_datetime = date("Y-m-d H:i:s", $old_claimed_stock_timestamp);
  $wpdb->query("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `last_activity` < '{$old_claimed_stock_datetime}' AND `cart_submitted` IN ('0')");
  
  return true;
}  
add_action('shutdown','wpsc_serialize_shopping_cart');
?>