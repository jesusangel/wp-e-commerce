<?php
/*
Plugin Name:WP e-Commerce
Plugin URI: http://www.instinct.co.nz
Description: A Plugin that provides a WordPress Shopping Cart. Visit the <a href='http://getshopped.org/forums'>Getshopped Forums</a> for support.
Version: 3.8 Development
Author: Instinct Entertainment
Author URI: http://www.instinct.co.nz/e-commerce/
*/
/**
 * WP e-Commerce Main Plugin File
 * @package wp-e-commerce
*/
// this is to make sure it sets up the table name constants correctly on activation
global $wpdb, $wpsc_purchlog_statuses;
define('WPSC_VERSION', '3.8');
define('WPSC_MINOR_VERSION', ('00000'.microtime(true)));

define('WPSC_PRESENTABLE_VERSION', '3.8 Development');

define('WPSC_DEBUG', false);
define('WPSC_GATEWAY_DEBUG', false);


// Get the wordpress version number
$version_processing = str_replace(array('_','-','+'), '.', strtolower($wp_version));
$version_processing = str_replace(array('alpha','beta','gamma'), array('a','b','g'), $version_processing);
$version_processing = preg_split("/([a-z]+)/i",$version_processing,-1, PREG_SPLIT_DELIM_CAPTURE);
array_walk($version_processing, create_function('&$v', '$v = trim($v,". ");'));

define('IS_WP25', version_compare($version_processing[0], '2.5', '>='));
define('IS_WP27', version_compare($version_processing[0], '2.7', '>='));
define('IS_WP29', version_compare($version_processing[0], '2.9', '>='));
define('IS_WP30', version_compare($version_processing[0], '3.0', '>='));



// // we need to know where we are, rather than assuming where we are

//Define the path to the plugin folder
define('WPSC_FILE_PATH', dirname(__FILE__));
define('WPSC_DIR_NAME', basename(WPSC_FILE_PATH));




$wpsc_siteurl = get_option('siteurl');
if(is_ssl()) {
	$wpsc_siteurl = str_replace("http://", "https://", $wpsc_siteurl);
}

$wpsc_plugin_url = WP_CONTENT_URL;
if(is_ssl()) {
  $plugin_url_parts = parse_url($wpsc_plugin_url);
  $site_url_parts = parse_url($wpsc_siteurl);
  if(stristr($plugin_url_parts['host'], $site_url_parts['host']) && stristr($site_url_parts['host'], $plugin_url_parts['host'])) {
		$wpsc_plugin_url = str_replace("http://", "https://", $wpsc_plugin_url);
	}
}


// the WPSC meta prefix, used for the product meta functions.
define('WPSC_META_PREFIX', "_wpsc_");

//Define the URL to the plugin folder
define('WPSC_FOLDER', dirname(plugin_basename(__FILE__)));
define('WPSC_URL', $wpsc_plugin_url.'/plugins/'.WPSC_FOLDER);


if(isset($wpdb->blogid)) {
   define('IS_WPMU', 1);
} else {
	define('IS_WPMU', 0);
}

/* 
  load plugin text domain for get_text files. Plugin language will be the same 
  as wordpress language defined in wp-config.php line 67
*/
load_plugin_textdomain('wpsc', false, dirname( plugin_basename(__FILE__) ) . '/languages');



if(!empty($wpdb->prefix)) {
  $wp_table_prefix = $wpdb->prefix;
} else if(!empty($table_prefix)) {
  $wp_table_prefix = $table_prefix;
}

// Define the database table names
// These tables are required, either for speed, or because there are no existing wordpress tables suitable for the data stored in them.
define('WPSC_TABLE_PURCHASE_LOGS', "{$wp_table_prefix}wpsc_purchase_logs");
define('WPSC_TABLE_CART_CONTENTS', "{$wp_table_prefix}wpsc_cart_contents");
define('WPSC_TABLE_SUBMITED_FORM_DATA', "{$wp_table_prefix}wpsc_submited_form_data");


define('WPSC_TABLE_CURRENCY_LIST', "{$wp_table_prefix}wpsc_currency_list");

// These tables may be needed in some situations, but are not vital to the core functionality of the plugin
define('WPSC_TABLE_CLAIMED_STOCK', "{$wp_table_prefix}wpsc_claimed_stock");
define('WPSC_TABLE_ALSO_BOUGHT', "{$wp_table_prefix}wpsc_also_bought");


// Theoretically, this could be done using the posts table and the post meta table, but its a bit of a kludge
define('WPSC_TABLE_META', "{$wp_table_prefix}wpsc_meta"); // only as long as wordpress doesn't ship with one.

// This could be made to use the posts and post meta table.
define('WPSC_TABLE_CHECKOUT_FORMS', "{$wp_table_prefix}wpsc_checkout_forms"); // dubious
define('WPSC_TABLE_COUPON_CODES', "{$wp_table_prefix}wpsc_coupon_codes"); // ought to be fine

// The tables below are marked for removal, the data in them is to be placed into other tables.
define('WPSC_TABLE_CATEGORISATION_GROUPS', "{$wp_table_prefix}wpsc_categorisation_groups");
define('WPSC_TABLE_DOWNLOAD_STATUS', "{$wp_table_prefix}wpsc_download_status");
define('WPSC_TABLE_ITEM_CATEGORY_ASSOC', "{$wp_table_prefix}wpsc_item_category_assoc");
define('WPSC_TABLE_PRODUCT_CATEGORIES', "{$wp_table_prefix}wpsc_product_categories");
define('WPSC_TABLE_PRODUCT_FILES', "{$wp_table_prefix}wpsc_product_files");
define('WPSC_TABLE_PRODUCT_IMAGES', "{$wp_table_prefix}wpsc_product_images");
define('WPSC_TABLE_PRODUCT_LIST', "{$wp_table_prefix}wpsc_product_list");
define('WPSC_TABLE_PRODUCT_ORDER', "{$wp_table_prefix}wpsc_product_order");
define('WPSC_TABLE_PRODUCT_RATING', "{$wp_table_prefix}wpsc_product_rating");
define('WPSC_TABLE_PRODUCT_VARIATIONS', "{$wp_table_prefix}wpsc_product_variations");
define('WPSC_TABLE_PURCHASE_STATUSES', "{$wp_table_prefix}wpsc_purchase_statuses");
define('WPSC_TABLE_PRODUCTMETA', "{$wp_table_prefix}wpsc_productmeta");
define('WPSC_TABLE_VARIATION_ASSOC', "{$wp_table_prefix}wpsc_variation_assoc");
define('WPSC_TABLE_VARIATION_PROPERTIES', "{$wp_table_prefix}wpsc_variation_properties");
define('WPSC_TABLE_VARIATION_VALUES', "{$wp_table_prefix}wpsc_variation_values");
define('WPSC_TABLE_VARIATION_VALUES_ASSOC', "{$wp_table_prefix}wpsc_variation_values_assoc");
define('WPSC_TABLE_VARIATION_COMBINATIONS', "{$wp_table_prefix}wpsc_variation_combinations");
define('WPSC_TABLE_REGION_TAX', "{$wp_table_prefix}wpsc_region_tax");
define('WPSC_TABLE_CATEGORY_TM', "{$wp_table_prefix}wpsc_category_tm");

// define('WPSC_TABLE_LOGGED_SUBSCRIPTIONS', "{$wp_table_prefix}wpsc_logged_subscriptions");

// start including the rest of the plugin here
require_once(WPSC_FILE_PATH.'/wpsc-includes/core.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/product-template.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/breadcrumbs.class.php');

require_once(WPSC_FILE_PATH.'/wpsc-includes/variations.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/ajax.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/misc.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/mimetype.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/cart.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/checkout.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/display.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/theme.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/shortcode.functions.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/coupons.class.php');
require_once(WPSC_FILE_PATH.'/wpsc-includes/purchaselogs.class.php');
require_once(WPSC_FILE_PATH."/wpsc-includes/category.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/processing.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/form-display.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/merchant.class.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/meta.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/productfeed.php");
//exit(print_r($v1,true));
if($v1[0] >= 2.8){
	require_once(WPSC_FILE_PATH."/wpsc-includes/upgrades.php");
}

if (!IS_WP25) {
	require_once(WPSC_FILE_PATH.'/editor.php');
} else { 
	require_once(WPSC_FILE_PATH.'/js/tinymce3/tinymce.php');
}

if((get_option('wpsc_share_this') == 1) && (get_option('product_list_url') != '')) {
  //if(stristr(("http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), get_option('product_list_url'))){
    include_once(WPSC_FILE_PATH."/share-this.php");
  //}
}

$wpsc_currency_data = array();
$wpsc_title_data = array();
$GLOBALS['nzshpcrt_imagesize_info'] = __('Note: if this is blank, the image will not be resized', 'wpsc');
$nzshpcrt_log_states[0]['name'] = __('Order Received', 'wpsc');
$nzshpcrt_log_states[1]['name'] = TXT_WPSC_PROCESSING;
$nzshpcrt_log_states[2]['name'] = __('Closed Order', 'wpsc');


require_once(WPSC_FILE_PATH."/currency_converter.inc.php"); 
require_once(WPSC_FILE_PATH."/shopping_cart_functions.php"); 
require_once(WPSC_FILE_PATH."/homepage_products_functions.php"); 
require_once(WPSC_FILE_PATH."/transaction_result_functions.php"); 
// include_once(WPSC_FILE_PATH.'/submit_checkout_function.php');
require_once(WPSC_FILE_PATH."/admin-form-functions.php");
require_once(WPSC_FILE_PATH."/shipwire_functions.php"); 

/* widget_section */
include_once(WPSC_FILE_PATH.'/widgets/product_tag_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/shopping_cart_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/donations_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/specials_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/latest_product_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/price_range_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/admin_menu_widget.php');
//include_once(WPSC_FILE_PATH.'/widgets/api_key_widget.php');
 if (class_exists('WP_Widget')) {
	include_once(WPSC_FILE_PATH.'/widgets/category_widget.28.php');
} else {
	include_once(WPSC_FILE_PATH.'/widgets/category_widget.27.php');
}


include_once(WPSC_FILE_PATH.'/image_processing.php');


// if we are in the admin section, include the admin code
if(WP_ADMIN == true) {
	require_once(WPSC_FILE_PATH."/wpsc-admin/admin.php");
}


/**
* Code to define where the uploaded files are stored starts here
*/
/*
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
}*/



$wp_upload_dir_data = wp_upload_dir();
//echo "<pre>".print_r($wp_upload_dir_data, true)."</pre>";
$upload_path = $wp_upload_dir_data['basedir'];
$upload_url = $wp_upload_dir_data['baseurl'];

if(is_ssl()) {
	 $upload_url = str_replace("http://", "https://", $upload_url);
}
	
$wpsc_upload_dir = "{$upload_path}/wpsc/";
$wpsc_file_dir = "{$wpsc_upload_dir}downloadables/";
$wpsc_preview_dir = "{$wpsc_upload_dir}previews/";
$wpsc_image_dir = "{$wpsc_upload_dir}product_images/";
$wpsc_thumbnail_dir = "{$wpsc_upload_dir}product_images/thumbnails/";
$wpsc_category_dir = "{$wpsc_upload_dir}category_images/";
$wpsc_user_uploads_dir = "{$wpsc_upload_dir}user_uploads/";
$wpsc_cache_dir = "{$wpsc_upload_dir}cache/";
$wpsc_upgrades_dir = "{$wpsc_upload_dir}upgrades/";
$wpsc_themes_dir = "{$wpsc_upload_dir}themes/";

define('WPSC_UPLOAD_DIR', $wpsc_upload_dir);
define('WPSC_FILE_DIR', $wpsc_file_dir);
define('WPSC_PREVIEW_DIR', $wpsc_preview_dir);
define('WPSC_IMAGE_DIR', $wpsc_image_dir);
define('WPSC_THUMBNAIL_DIR', $wpsc_thumbnail_dir);
define('WPSC_CATEGORY_DIR', $wpsc_category_dir);
define('WPSC_USER_UPLOADS_DIR', $wpsc_user_uploads_dir);
define('WPSC_CACHE_DIR', $wpsc_cache_dir);
define('WPSC_UPGRADES_DIR', $wpsc_upgrades_dir);
define('WPSC_THEMES_PATH', $wpsc_themes_dir);


/**
* files that are uploaded as part of digital products are not directly downloaded, therefore there is no need for a URL constant for them
*/
$wpsc_upload_url = "{$upload_url}/wpsc/";
$wpsc_preview_url = "{$wpsc_upload_url}previews/";
$wpsc_image_url = "{$wpsc_upload_url}product_images/";
$wpsc_thumbnail_url = "{$wpsc_upload_url}product_images/thumbnails/";
$wpsc_category_url = "{$wpsc_upload_url}category_images/";
$wpsc_user_uploads_url = "{$wpsc_upload_url}user_uploads/";
$wpsc_cache_url = "{$wpsc_upload_url}cache/";
$wpsc_upgrades_url = "{$wpsc_upload_url}upgrades/";
$wpsc_themes_url = "{$wpsc_upload_url}themes/";

define('WPSC_UPLOAD_URL', $wpsc_upload_url);
define('WPSC_PREVIEW_URL', $wpsc_preview_url);
define('WPSC_IMAGE_URL', $wpsc_image_url);
define('WPSC_THUMBNAIL_URL', $wpsc_thumbnail_url);
define('WPSC_CATEGORY_URL', $wpsc_category_url);
define('WPSC_USER_UPLOADS_URL', $wpsc_user_uploads_url);
define('WPSC_CACHE_URL', $wpsc_cache_url);
define('WPSC_UPGRADES_URL', $wpsc_upgrades_url);
define('WPSC_THEMES_URL', $wpsc_themes_url);



/* 
 * This plugin gets the merchants from the merchants directory and
 * needs to search the merchants directory for merchants, the code to do this starts here
 */
$gateway_directory = WPSC_FILE_PATH.'/merchants';
$nzshpcrt_merchant_list = wpsc_list_dir($gateway_directory);
 //exit("<pre>".print_r($nzshpcrt_merchant_list,true)."</pre>");
$num=0;
foreach($nzshpcrt_merchant_list as $nzshpcrt_merchant) {
  if(stristr( $nzshpcrt_merchant , '.php' )) {
    //echo $nzshpcrt_merchant;
    require(WPSC_FILE_PATH."/merchants/".$nzshpcrt_merchant);
	}
  $num++;
}
/* 
 * and ends here
 */
// include shipping modules here.
$shipping_directory = WPSC_FILE_PATH.'/shipping';
$nzshpcrt_shipping_list = wpsc_list_dir($shipping_directory);
foreach($nzshpcrt_shipping_list as $nzshpcrt_shipping) {
	if(stristr( $nzshpcrt_shipping , '.php' )) {
		require(WPSC_FILE_PATH."/shipping/".$nzshpcrt_shipping);
	}
}
$wpsc_shipping_modules = apply_filters('wpsc_shipping_modules',$wpsc_shipping_modules);

// if the gold cart file is present, include it, this must be done before the admin file is included
if(is_file(WPSC_UPGRADES_DIR . "gold_cart_files/gold_shopping_cart.php")) {
  require_once(WPSC_UPGRADES_DIR . "gold_cart_files/gold_shopping_cart.php");
}

// need to sort the merchants here, after the gold ones are included. 
if(!function_exists('wpsc_merchant_sort')) {
	function wpsc_merchant_sort($a, $b) { 
		return strnatcmp(strtolower($a['name']), strtolower($b['name']));
	}
}
uasort($nzshpcrt_gateways, 'wpsc_merchant_sort');

// make an associative array of references to gateway data.
$wpsc_gateways = array(); 
foreach((array)$nzshpcrt_gateways as $key => $gateway) {
	$wpsc_gateways[$gateway['internalname']] = &$nzshpcrt_gateways[$key];
}


// set page title array for important WPSC pages 
$wpsc_page_titles = wpsc_get_page_post_names();


$theme_path = WPSC_FILE_PATH . '/themes/';
if((get_option('wpsc_selected_theme') != '') && (file_exists($theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".php") )) {    
  include_once(WPSC_FILE_PATH.'/themes/'.get_option('wpsc_selected_theme').'/'.get_option('wpsc_selected_theme').'.php');
}
$current_version_number = get_option('wpsc_version');
if(count(explode(".",$current_version_number)) > 2) {
	// in a previous version, I accidentally had the major version number have two dots, and three numbers
	// this code rectifies that mistake
	$current_version_number_array = explode(".",$current_version_number);
	array_pop($current_version_number_array);
	$current_version_number = (float)implode(".", $current_version_number_array );
} else if(!is_numeric(get_option('wpsc_version'))) {
  $current_version_number = 0;
}


//if there are any upgrades present, include them., thanks to nielo.info and lsdev.biz
if($v1[0] >= 2.8){
	$upgrades = get_upgrades();
	foreach ($upgrades as $path=>$upgrade) {
		$upgrade_file = WPSC_UPGRADES_DIR . '/' . $path;
		require_once($upgrade_file);
	}
}


include_once(WPSC_FILE_PATH."/wpsc-includes/install_and_update.functions.php");
register_activation_hook(__FILE__, 'wpsc_install');


/**
 * wpsc_start_the_query
 */
if(!function_exists('wpsc_start_the_query')) {
	function wpsc_start_the_query() {
		global $wp_query, $wpsc_query, $wpsc_query_vars;
		
		  
		if($wpsc_query == null) {
			if(count($wpsc_query_vars) < 1) {
			
				$wpsc_query_vars = array(
					'post_type' => 'wpsc-product',
					'post_parent' => 0,
					'order' => 'ASC'
				);
				$orderby =  get_option ( 'wpsc_sort_by' );
				switch($orderby) {
				
				case "dragndrop":
					$wpsc_query_vars["orderby"] = 'menu_order';
					break;
				case "name":
					$wpsc_query_vars["orderby"] = 'title';
					break;
				case "price":
				//This only works in WP 3.0.
					$wpsc_query_vars["meta_key"] = '_wpsc_price';
					$wpsc_query_vars["orderby"] = 'meta_value_num';
					break;
				case "id":
					$wpsc_query_vars["orderby"] = 'ID';
					break;
				}
			}
			
			add_filter('pre_get_posts', 'wpsc_generate_product_query', 11);
			$wpsc_query = new WP_Query($wpsc_query_vars);
		}
		
		//echo "<pre>".print_r($wpsc_query_vars,true)."</pre>";
		//echo "<pre>".print_r($wpsc_query,true)."</pre>";
		
		$post_id = $wp_query->post->ID;
		$page_url = get_permalink($post_id);
		if(get_option('shopping_cart_url') == $page_url) {
			$_SESSION['wpsc_has_been_to_checkout'] = true;
			//echo $_SESSION['wpsc_has_been_to_checkout'];
		}

	}
}
// after init and after when the wp query string is parsed but before anything is displayed
add_action('template_redirect', 'wpsc_start_the_query', 0);


/**
 * Check to see if the session exists, if not, start it
 */
if((!is_array($_SESSION)) xor (!isset($_SESSION['nzshpcrt_cart'])) xor (!$_SESSION)) {
  session_start();
}
if(!function_exists('wpsc_initialisation')){
	function wpsc_initialisation() {
	  global $wpsc_cart,  $wpsc_theme_path, $wpsc_theme_url, $wpsc_category_url_cache, $wpsc_query_vars;
	  
	  // initialize the wpsc query fats, must be a global variable as we cannot start it off from within the wp query object,
	  // starting it in wp_query results in intractable infinite loops in 3.0
	  $wpsc_query_vars = array();
	  
	  
	  // set the theme directory constant
	
	  $uploads_dir = @opendir(WPSC_THEMES_PATH);
	  $file_names = array();
	  
	  if ( $uploads_dir ) {
		  while(($file = @readdir($uploads_dir)) !== false) {
			//echo "<br />test".WPSC_THEMES_PATH.$file;
			if(is_dir(WPSC_THEMES_PATH.$file) && ($file != "..") && ($file != ".") && ($file != ".svn")){
					$file_names[] = $file;
			}
		  }
	  }
	  
	  if(count($file_names) > 0) {
			$wpsc_theme_path = WPSC_THEMES_PATH;
			$wpsc_theme_url = WPSC_THEMES_URL;
	  } else {
			$wpsc_theme_path = WPSC_FILE_PATH . "/themes/";
			$wpsc_theme_url = WPSC_URL. '/themes/';
	  }
	  //$theme_path = WPSC_FILE_PATH . "/themes/";
	  //exit(print_r($file_names,true));
		if((get_option('wpsc_selected_theme') == null) || (!file_exists($wpsc_theme_path.get_option('wpsc_selected_theme')))) {
			$theme_dir = 'default';
		} else {
			$theme_dir = get_option('wpsc_selected_theme');
		}
		define('WPSC_THEME_DIR', $theme_dir);
	  
	  // initialise the cart session, if it exist, unserialize it, otherwise make it
		if(isset($_SESSION['wpsc_cart'])) {
			if(is_object($_SESSION['wpsc_cart'])) {
				$GLOBALS['wpsc_cart'] = $_SESSION['wpsc_cart'];
			} else {
				$GLOBALS['wpsc_cart'] = unserialize($_SESSION['wpsc_cart']);
			}
			if(!is_object($GLOBALS['wpsc_cart']) || (get_class($GLOBALS['wpsc_cart']) != "wpsc_cart")) {
				$GLOBALS['wpsc_cart'] = new wpsc_cart;
			}
		} else {
			$GLOBALS['wpsc_cart'] = new wpsc_cart;
		}
	}
	$GLOBALS['wpsc_category_url_cache'] = get_option('wpsc_category_url_cache');

}
// first plugin hook in wordpress
add_action('plugins_loaded','wpsc_initialisation', 0);

/**
 * wpsc_query_modifier function.
 * 
 * @access public
 * @param object - reference to $wp_query
 * @return $query
 */
/*function wpsc_query_modifier($query) {
	//echo "<pre>".print_r($query,true)."</pre>";
	if($query->query_vars['taxonomy'] == 'wpsc_product_category') {
		$query->is_product = true;
	}
	
	return $query;
}

add_filter('parse_query', 'wpsc_query_modifier');
*/
// Register the wpsc post types
function wpsc_register_post_types() {
	global $wpsc_page_titles;
	// Products
	register_post_type( 'wpsc-product', array(
	    '_edit_link' => 'admin.php?page=wpsc-edit-products&action=wpsc_add_edit&product=%d',
	    'capability_type' => 'post',
	    'hierarchical' => true,
		'exclude_from_search' => false,
		'public' => true,
		'show_ui' => false,
		'show_in_nav_menus' => true,
		'label' => __('Products'),  
        'singular_label' => __('Product'),
		'query_var' => true,
		'rewrite' => array(
			'slug' => $wpsc_page_titles['products'].'/%wpsc_product_category%',
			'with_front' => false
		)
	));
	// Purchasable product files
	register_post_type( 'wpsc-product-file', array(
	    'capability_type' => 'post',
	    'hierarchical' => false,
		'exclude_from_search' => true,
		'rewrite' => false
	));
	
	// Product tags
	register_taxonomy('product_tag', 'wpsc-product');
	
	// Product categories, is heirarchical and can use permalinks
	register_taxonomy('wpsc_product_category', 'wpsc-product', array(
		'hierarchical' => true,
		'rewrite' => array(
			'slug' => $wpsc_page_titles['products'],
			'with_front' => false
		)
	));
$labels = array(
    'name' => _x( 'Variations', 'taxonomy general name' ),
    'singular_name' => _x( 'Variation', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Variations' ),
    'all_items' => __( 'All Variations' ),
    'parent_item' => __( 'Parent Variation' ),
    'parent_item_colon' => __( 'Parent Variations:' ),
    'edit_item' => __( 'Edit Variation' ), 
    'update_item' => __( 'Update Variation' ),
    'add_new_item' => __( 'Add New Variation' ),
    'new_item_name' => __( 'New Variation Name' ),
  ); 	
	// Product Variations, is internally heirarchical, externally, two separate types of items, one containing the other
	register_taxonomy('wpsc-variation', 'wpsc-product', array(
		'hierarchical' => true,
		'query_var' => 'variations',
		'rewrite' => false,
		'public' =>	true,
		'labels' => $labels
	));
	$role = get_role('administrator');
	$role->add_cap('read_wpsc-product');
	$role->add_cap('read_wpsc-product-file');
}
add_action( 'init', 'wpsc_register_post_types', 8 ); // highest priority


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
add_action('plugins_loaded', 'widget_wp_shopping_cart_init', 10);


// refresh page urls when permalinks are turned on or altered
add_filter('mod_rewrite_rules', 'wpsc_refresh_page_urls');




if(is_ssl()) {
	function wpsc_add_https_to_page_url_options($url) {
		return str_replace("http://", "https://", $url);
	}
	add_filter('option_product_list_url', 'wpsc_add_https_to_page_url_options');
	add_filter('option_shopping_cart_url', 'wpsc_add_https_to_page_url_options');
	add_filter('option_transact_url', 'wpsc_add_https_to_page_url_options');
	add_filter('option_user_account_url', 'wpsc_add_https_to_page_url_options');
}



/**
 * This serializes the shopping cart variable as a backup in case the unserialized one gets butchered by various things
 */  
if(!function_exists('wpsc_serialize_shopping_cart')){
	function wpsc_serialize_shopping_cart() {
		global $wpdb, $wpsc_start_time, $wpsc_cart, $wpsc_category_url_cache;
		if(is_object($wpsc_cart)) {
			$wpsc_cart->errors = array();
		}
		$_SESSION['wpsc_cart'] = serialize($wpsc_cart);
		
		$previous_category_url_cache = get_option('wpsc_category_url_cache');
		if($wpsc_category_url_cache != $previous_category_url_cache) {
			update_option('wpsc_category_url_cache', $wpsc_category_url_cache);
		}
		
		return true;
	} 
} 
add_action('shutdown','wpsc_serialize_shopping_cart');



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


/**
 * Featured Product
 *
 * Refactoring Featured Product Plugin to utilize Sticky Post Status, available since WP 2.7
 * also utilizes Featured Image functionality, available as post_thumbnail since 2.9, Featured Image since 3.0
 * Main differences - Removed 3.8 conditions, removed meta box from admin, changed meta_values
 * Removes shortcode, as it automatically ties in to top_of_page hook if sticky AND featured product exists.
 *
 * @package wp-e-commerce
 * @since 3.8
 */ 

function wpsc_the_sticky_image($product_id) {
global $wpdb;
//Previously checked product_meta, now get_vars guid from attachment with this post_parent, checking against _thumbnail_id   

$sticky_product_image = $wpdb->get_var($wpdb->prepare("SELECT guid FROM wp_posts p, wp_postmeta pm WHERE p.post_parent = $product_id AND pm.post_id = $product_id AND pm.meta_value = p.ID"));

	if($sticky_product_image != ''){
		return $sticky_product_image;
	}else{
		return wpsc_the_product_image(340, 260);
	}
}

if (is_admin()) {
 	/**
 	 * wpsc_update_featured_products function.
 	 * 
 	 * @access public
 	 * @return void
 	 */

 	function wpsc_update_featured_products() { 	
		global $wpdb;
		$is_ajax = (int)(bool)$_POST['ajax'];
		$product_id = absint($_GET['product_id']);
		check_admin_referer('feature_product_' . $product_id);
		$status = get_option( 'sticky_posts' );
		
		$new_status = (in_array($product_id, $status)) ? false : true;
		
		if ($new_status) {
		
			$status[] = $product_id;
		
		} else { 
			$status = array_diff($status, array($product_id));
			$status = array_values($status);
		}
		update_option('sticky_posts', $status);
		
		if($is_ajax == true) {
			 if($new_status == true) :?>
jQuery('.featured_toggle_<?php echo $product_id; ?>').html("<img class='gold-star' src='<?php echo WPSC_URL; ?>/images/gold-star.gif' alt='<?php _e('Unmark as Featured', 'wpsc'); ?>' title='<?php _e('Unmark as Featured', 'wpsc'); ?>' />");
			<?php else: ?>
jQuery('.featured_toggle_<?php echo $product_id; ?>').html("<img class='grey-star' src='<?php echo WPSC_URL; ?>/images/grey-star.gif' alt='<?php _e('Mark as Featured', 'wpsc'); ?>' title='<?php _e('Mark as Featured', 'wpsc'); ?>' />");
			<?php endif; 
			exit();
		
		}
		//$sendback = add_query_arg('featured', "1", wp_get_referer());
		wp_redirect(wp_get_referer());
	 	exit();
 	}
 
 
	if($_REQUEST['wpsc_admin_action'] == 'update_featured_product') {
		add_action('admin_init', 'wpsc_update_featured_products');
	}
	
	
	/**
	 * wpsc_featured_products_toggle function.
	 * 
	 * @access public
	 * @param mixed $product_id
	 * @return void
	 */
	function wpsc_featured_products_toggle($product_id) {
		global $wpdb;							
		$featured_product_url = wp_nonce_url("admin.php?wpsc_admin_action=update_featured_product&amp;product_id=$product_id}", 'feature_product_'.$product_id);
		?>
		<a class="wpsc_featured_product_toggle featured_toggle_<?php echo $product_id; ?>" href='<?php echo $featured_product_url; ?>' >
			<?php if (in_array($product_id, get_option( 'sticky_posts' ))) :?>
				<img class='gold-star' src='<?php echo WPSC_URL; ?>/images/gold-star.gif' alt='<?php _e('Unmark as Featured', 'wpsc'); ?>' title='<?php _e('Unmark as Featured', 'wpsc'); ?>' />
			<?php else: ?>
				<img class='grey-star' src='<?php echo WPSC_URL; ?>/images/grey-star.gif' alt='<?php _e('Mark as Featured', 'wpsc'); ?>' title='<?php _e('Mark as Featured', 'wpsc'); ?>' />
			<?php endif; ?>
		</a>
		<?php	
	}
	
	//add_filter('wpsc_products_page_forms', 'wpsc_add_featured_products');
	
	add_action('wpsc_admin_product_checkbox', 'wpsc_featured_products_toggle', 10, 1);
}

/**
 * wpsc_display_products_page function.
 * 
 * @access public
 * @param mixed $query
 * @return void
 */
function wpsc_display_featured_products_page() {
	global $wpdb, $wpsc_query;	
	
	

	if ( is_front_page() || is_home() ) {  
 	$query = get_posts(array(
			'post__in'  => get_option('sticky_posts'),
			'post_type' => 'wpsc-product',
			'orderby' => 'rand',
			'meta_key' => '_thumbnail_id',
			'numberposts' => 1
		));

		if ( count($query) > 0 ) { 

			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
			$image_width = get_option('product_image_width');
			$image_height = get_option('product_image_height');
			//Begin outputting featured product.  We can worry about templating later, or folks can just CSS it up.
			 foreach($query as $product) :
			setup_postdata($product);
?>

<div class="wpsc_container wpsc_featured">
		<div class="product_grid_display">
			<div class="product_grid_item product_view_<?php the_ID(); ?>">
				<div class="item_text">
						<h3>
							<a href='<?php echo get_permalink($product->ID); ?>'><?php echo get_the_title($product->ID); ?></a>
						</h3> 
						<div class="pricedisplay"><?php echo wpsc_the_product_price(); ?></div> 
						<div class='wpsc_description'>
							<?php the_excerpt(); ?>
							<a href='<?php echo get_permalink($product->ID); ?>'>
							  More Information&hellip;
							</a>
						</div>
				</div>
			
				<?php if(wpsc_the_product_thumbnail()) :?> 	   
					<div class="item_image">
						<a href="<?php echo get_permalink($product->ID); ?>" style='background-image: url(<?php echo wpsc_the_sticky_image(wpsc_the_product_id()); ?>);'>
						</a>
					</div>
				<?php else: ?> 
					<div class="item_no_image">
						<a href="<?php echo get_the_title($product->ID); ?>">
						<span>No Image Available</span>
						</a>
					</div>
				<?php endif; ?>
				<div class="wpsc_clear"></div>
			</div>
	</div>
</div>
<?php
		endforeach;
		//End output	
		}
	}
}

add_action('wpsc_top_of_products_page', 'wpsc_display_featured_products_page', 12);
?>