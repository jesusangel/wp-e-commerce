<?php
/*
Plugin Name:WP Shopping Cart
Plugin URI: http://www.instinct.co.nz
Description: A plugin that provides a WordPress Shopping Cart. Contact <a href='http://www.instinct.co.nz/?p=16#support'>Instinct Entertainment</a> for support. <br />Click here to to <a href='?wpsc_uninstall=ask'>Uninstall</a>.
Version: 3.6.10
Author: Instinct Entertainment
Author URI: http://www.instinct.co.nz/e-commerce/
*/
/**
 * WP eCommerce Main Plugin File
 * @package wp-e-commerce
*/
define('WPSC_VERSION', '3.6');
define('WPSC_MINOR_VERSION', '116');


define('WPSC_PRESENTABLE_VERSION', '3.6.10');

define('WPSC_DEBUG', false);

$v1 = str_replace(array('_','-','+'),'.',strtolower($wp_version));
$v1 = str_replace(array('alpha','beta','gamma'), array('a','b','g'), $v1);
$v1 = preg_split("/([a-z]+)/i",$v1,-1, PREG_SPLIT_DELIM_CAPTURE);
array_walk($v1, create_function('&$v', '$v = trim($v,". ");'));

define('IS_WP25', version_compare($v1[0], '2.5', '>=') );
define('IS_WP27', version_compare($v1[0], '2.7', '>=') );

// // we need to know where we are, rather than assuming where we are
define('WPSC_FILE_PATH', dirname(__FILE__));
define('WPSC_DIR_NAME', basename(WPSC_FILE_PATH));

$siteurl = get_option('siteurl');

// thanks to ikool for this fix
define('WPSC_FOLDER', dirname(plugin_basename(__FILE__)));
define('WPSC_URL', get_option('siteurl').'/wp-content/plugins/' . WPSC_FOLDER);

if(isset($wpmu_version)) {
    define('IS_WPMU', 1);
}

if(get_option('language_setting') != '') {
  require(WPSC_FILE_PATH.'/languages/'.get_option('language_setting'));
} else {
  require(WPSC_FILE_PATH.'/languages/EN_en.php');
}


require(WPSC_FILE_PATH.'/wpsc-includes/wpsc_query.php');
require(WPSC_FILE_PATH.'/wpsc-includes/variations.class.php');
//require(WPSC_FILE_PATH.'/wpsc-includes/extra.class.php');
require(WPSC_FILE_PATH.'/wpsc-includes/ajax.functions.php');
require(WPSC_FILE_PATH.'/wpsc-includes/mimetype.php');
require(WPSC_FILE_PATH.'/wpsc-includes/cart.class.php');
require(WPSC_FILE_PATH.'/wpsc-includes/xmlparser.php');
if (!IS_WP25) {
	require(WPSC_FILE_PATH.'/editor.php');
} else { 
	require(WPSC_FILE_PATH.'/js/tinymce3/tinymce.php');
}

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




/**
 * Check to see if the session exists, if not, start it
 */
if((!is_array($_SESSION)) xor (!isset($_SESSION['nzshpcrt_cart'])) xor (!$_SESSION)) {
  session_start();
}

function wpsc_initialisation() {
  global $wpsc_cart;
  // set the theme directory constant
	if(!file_exists($theme_path.get_option('wpsc_selected_theme'))) {
		$theme_dir = 'default';
	} else {
		$theme_dir = get_option('wpsc_selected_theme');
	}
	define('WPSC_THEME_DIR', $theme_dir);
	
  
  
  // initialise the cart session, if it exist, unserialize it, otherwise make it
  if(isset($_SESSION['wpsc_cart'])) {
		$wpsc_cart = unserialize($_SESSION['wpsc_cart']);
  } else {
    $wpsc_cart = new wpsc_cart;
  }
}
// first plugin hook in wordpress
add_action('plugins_loaded','wpsc_initialisation');



  
/*
 * This serializes the shopping cart variable as a backup in case the unserialized one gets butchered by various things
 */  
function wpsc_serialize_shopping_cart() {
  global $wpsc_start_time, $wpsc_cart;
  @$_SESSION['nzshpcrt_serialized_cart'] = serialize($_SESSION['nzshpcrt_cart']);
  
  $_SESSION['wpsc_cart'] = serialize($wpsc_cart);
  return true;
}  
add_action('shutdown','wpsc_serialize_shopping_cart');


/// OLD CODE INCLUDED HERE
include_once('wp-shopping-cart.old.php');
?>