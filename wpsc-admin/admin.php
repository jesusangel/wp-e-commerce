<?php
/**
 * WP eCommerce Main Admin functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
 
/// admin includes
require_once(WPSC_FILE_PATH."/wpsc-admin/display-items.page.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/includes/display-items-functions.php"); 
require_once(WPSC_FILE_PATH."/wpsc-admin/includes/product-functions.php"); 

require_once(WPSC_FILE_PATH."/wpsc-admin/ajax.php");

require_once(WPSC_FILE_PATH."/wpsc-admin/display-sales-logs.php"); 

/**
	* wpsc_admin_pages function, all the definitons of admin pages are stores here.
	* No parameters, returns nothing
*/
function wpsc_admin_pages(){
  global $userdata;
    /*
     * Fairly standard wordpress plugin API stuff for adding the admin pages, rearrange the order to rearrange the pages
     * The bits to display the options page first on first use may be buggy, but tend not to stick around long enough to be identified and fixed
     * if you find bugs, feel free to fix them.
     *
     * If the permissions are changed here, they will likewise need to be changed for the other secions of the admin that either use ajax
     * or bypass the normal download system.
     */
    if(function_exists('add_options_page')) {
			$base_page = WPSC_DIR_NAME.'/wpsc-admin/display-sales-logs.php';
			
			
		if ($userdata->user_level <= 2) {
				if(file_exists(dirname(__FILE__).'/gold_cart_files/affiliates.php')) {
					add_object_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 0,  WPSC_URL.'/gold_cart_files/affiliates.php','affiliate_page', WPSC_URL."/images/cart.png");
				} else {
					if (function_exists('add_object_page')) {
						add_object_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page,array(), WPSC_URL."/images/cart.png");
					} else {
						add_menu_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page);
					}
				}
			} else {
				if (function_exists('add_object_page')) {
					add_object_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page,array(), WPSC_URL."/images/cart.png");
					
				} else {
					add_menu_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 2, $base_page);
				}
			}

				
	
				//add_submenu_page(WPSC_DIR_NAME.'/display-log.php',TXT_WPSC_PURCHASELOG, TXT_WPSC_PURCHASELOG, 7, WPSC_DIR_NAME.'/display-log.php');
				$page_hooks[] = add_submenu_page($base_page, TXT_WPSC_PURCHASELOG, TXT_WPSC_PURCHASELOG, 7, WPSC_DIR_NAME.'/wpsc-admin/display-sales-logs.php', 'wpsc_display_sales_logs');
				//         }
			//written by allen
			add_submenu_page('users.php',TXT_WPSC_ECOMMERCE_SUBSCRIBERS, TXT_WPSC_ECOMMERCE_SUBSCRIBERS, 7, WPSC_DIR_NAME.'/display-ecommerce-subs.php');
			
			
			//add_submenu_page($base_page,TXT_WPSC_PRODUCTS, TXT_WPSC_PRODUCTS, 7, WPSC_DIR_NAME.'/display-items.php');
			
			$page_hooks[] = add_submenu_page($base_page,__("Products"), __("Products"), 7, 'edit-products', 'wpsc_display_products_page');
			
			
			
			
			foreach((array)get_option('wpsc_product_page_order') as $box) {
				$boxes[$box] = ucwords(str_replace("_"," ",$box));
			}			//exit('-->'.$help);
			add_submenu_page($base_page,TXT_WPSC_CATEGORISATION, TXT_WPSC_CATEGORISATION, 7, WPSC_DIR_NAME.'/display-category.php');
			if (function_exists('add_contextual_help')) {

				add_contextual_help(WPSC_DIR_NAME.'/display-log',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/sales/'>About this page</a>");

				add_contextual_help(WPSC_DIR_NAME.'/display-category',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/product-groups/'>About this page</a>");
				add_contextual_help(WPSC_DIR_NAME.'/display_variations',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/variations/'>About this page</a>");
				add_contextual_help(WPSC_DIR_NAME.'/display-coupons',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/marketing/'>About this page</a>");
				add_contextual_help(WPSC_DIR_NAME.'/options',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/shop-settings-general/'>General Settings</a><br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/presentation/'>Presentation Options</a> <br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/admin-settings/'>Admin Options</a> <br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/shipping/'>Shipping Options</a> <br />
																<a target='_blank' href='http://www.instinct.co.nz/e-commerce/payment-option/'>Payment Options</a> <br />");
				add_contextual_help(WPSC_DIR_NAME.'/display-items',"<a target='_blank' href='http://www.instinct.co.nz/e-commerce/products/'>About this page</a>");;
			}

			add_submenu_page($base_page,TXT_WPSC_VARIATIONS, TXT_WPSC_VARIATIONS, 7, WPSC_DIR_NAME.'/display_variations.php');
			add_submenu_page($base_page,TXT_WPSC_MARKETING, TXT_WPSC_MARKETING, 7, WPSC_DIR_NAME.'/display-coupons.php');
			if (file_exists(dirname(__FILE__).'/gold_cart_files/csv_import.php')) {
				add_submenu_page($base_page,TXT_WPSC_IMPORT_CSV, TXT_WPSC_IMPORT_CSV, 7, WPSC_DIR_NAME.'/gold_cart_files/csv_import.php');
			}
			
// 			add_submenu_page($base_page,TXT_WPSC_PAYMENTGATEWAYOPTIONS, TXT_WPSC_PAYMENTGATEWAYOPTIONS, 7, WPSC_DIR_NAME.'/gatewayoptions.php');
// 			add_submenu_page($base_page,TXT_WPSC_SHIPPINGOPTIONS, TXT_WPSC_SHIPPINGOPTIONS, 7, WPSC_DIR_NAME.'/display-shipping.php');
// 			add_submenu_page($base_page,TXT_WPSC_FORM_FIELDS, TXT_WPSC_FORM_FIELDS, 7, WPSC_DIR_NAME.'/form_fields.php');
			add_submenu_page($base_page,TXT_WPSC_OPTIONS, TXT_WPSC_OPTIONS, 7, WPSC_DIR_NAME.'/options.php');
			if(function_exists('gold_shpcrt_options')) {
				gold_shpcrt_options($base_page);
			}
			
			do_action('wpsc_add_submenu');
//       add_submenu_page($base_page,TXT_WPSC_HELPINSTALLATION, TXT_WPSC_HELPINSTALLATION, 7, WPSC_DIR_NAME.'/instructions.php');
		}
		
		
		// Include the javascript and CSS for this page
		
		foreach($page_hooks as $page_hook) {
			add_action("load-$page_hook", 'wpsc_admin_include_css_and_js');
			//echo $page_hook."<br />";
		}
		
		return;
  }
  
  

function wpsc_meta_boxes(){
  $pagename = 'products_page_edit-products';
	add_meta_box('category_and_tag', 'Category and Tags', 'wpsc_category_and_tag_forms', $pagename, 'normal', 'high');
	add_meta_box('price_and_stock', 'Price and Stock', 'wpsc_price_and_stock_forms', $pagename, 'normal', 'high');
	add_meta_box('variation', 'Variations', 'wpsc_variation_forms', $pagename, 'normal', 'high');
	add_meta_box('shipping', 'Shipping', 'wpsc_shipping_forms', $pagename, 'normal', 'high');
	add_meta_box('advanced', 'Advanced Settings', 'wpsc_advanced_forms', $pagename, 'normal', 'high');
	add_meta_box('product_download', 'Product Download', 'wpsc_product_download_forms', $pagename, 'normal', 'high');
	add_meta_box('product_image', 'Product Images', 'wpsc_product_image_forms', $pagename, 'normal', 'high');
}

add_action('admin_menu', 'wpsc_meta_boxes');

/**
	* wpsc_admin_css_and_js function, includes the wpsc_admin CSS and JS
	* No parameters, returns nothing
*/
function  wpsc_admin_include_css_and_js() {
  $siteurl = get_option('siteurl'); 
	
	
	wp_enqueue_script('swfupload');
	wp_enqueue_script('swfupload-swfobject');
	wp_enqueue_script('swfupload-queue');
	wp_enqueue_script('swfupload-handlers');

	wp_enqueue_script( 'postbox', '/wp-admin/js/postbox.js', array('jquery'));
	
  $version_identifier = WPSC_VERSION.".".WPSC_MINOR_VERSION;
	wp_enqueue_script('wp-e-commerce-admin-parameters', $siteurl."/wp-admin/admin.php?wpsc_dynamic_js=true", false, $version_identifier);
	wp_enqueue_script('wp-e-commerce-admin', WPSC_URL.'/wpsc-admin/js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), $version_identifier);
	
	wp_enqueue_script('wp-e-commerce-legacy-ajax', WPSC_URL.'/wpsc-admin/js/ajax.js', $version_identifier); // needs removing
	wp_enqueue_script('wp-e-commerce-variations', WPSC_URL.'/wpsc-admin/js/variations.js', array('jquery'), $version_identifier);
	
	//wp_enqueue_script('wp-e-commerce-swfuploader', WPSC_URL.'/wpsc-admin/js/wpsc-swfuploader.js', array('swfupload'), $version_identifier);
	
	
	
	wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL.'/wpsc-admin/css/admin.css', false, $version_identifier, 'all' );
	wp_enqueue_style( 'wp-e-commerce-admin-dynamic', $siteurl."/wp-admin/admin.php?wpsc_dynamic_css=true" , false, $version_identifier, 'all' );

	
	//wp_enqueue_script('post');
 	if ( user_can_richedit() )
		wp_enqueue_script('editor');
		
	wp_enqueue_script('media-upload');
	wp_enqueue_script('word-count');
	wp_admin_css( 'dashboard' );
	
	// remove the old javascript and CSS, we want it no more, it smells bad
	remove_action('admin_head', 'wpsc_admin_css');
}
  
  
  
function wpsc_admin_dynamic_js() { 
 	header('Content-Type: text/javascript');
 	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
 	header('Cache-Control: public, must-revalidate, max-age=86400');
 	header('Pragma: public');
  $siteurl = get_option('siteurl'); 
	$hidden_boxes = get_option('wpsc_hidden_box');
	$hidden_boxes = implode(',', (array)$hidden_boxes);
	
	echo "var base_url = '".$siteurl."';\n\r";
	echo "var WPSC_URL = '". WPSC_URL."';\n\r";
	echo "var WPSC_IMAGE_URL = '".WPSC_IMAGE_URL."';\n\r";
	echo "var WPSC_DIR_NAME = '".WPSC_DIR_NAME."';\n\r";
	echo "var WPSC_IMAGE_URL = '".WPSC_IMAGE_URL."';\n\r";
	
	// LightBox Configuration start
	echo "var fileLoadingImage = '".WPSC_URL."/images/loading.gif';\n\r";
	echo "var fileBottomNavCloseImage = '".WPSC_URL."/images/closelabel.gif';\n\r";
	echo "var fileThickboxLoadingImage = '".WPSC_URL."/images/loadingAnimation.gif';\n\r";
	
	echo "var resizeSpeed = 9;\n\r";
	
	echo "var borderSize = 10;\n\r";
	
	echo "var hidden_boxes = '".$hidden_boxes."';\n\r";
	echo "var IS_WP27 = '".IS_WP27."';\n\r";
	echo "var TXT_WPSC_DELETE = '".TXT_WPSC_DELETE."';\n\r";
	echo "var TXT_WPSC_TEXT = '".TXT_WPSC_TEXT."';\n\r";
	echo "var TXT_WPSC_EMAIL = '".TXT_WPSC_EMAIL."';\n\r";
	echo "var TXT_WPSC_COUNTRY = '".TXT_WPSC_COUNTRY."';\n\r";
	echo "var TXT_WPSC_TEXTAREA = '".TXT_WPSC_TEXTAREA."';\n\r";
	echo "var TXT_WPSC_HEADING = '".TXT_WPSC_HEADING."';\n\r";
	echo "var TXT_WPSC_COUPON = '".TXT_WPSC_COUPON."';\n\r";
	echo "var HTML_FORM_FIELD_TYPES =\"<option value='text' >".TXT_WPSC_TEXT."</option>";
	echo "<option value='email' >".TXT_WPSC_EMAIL."</option>";
	echo "<option value='address' >".TXT_WPSC_ADDRESS."</option>";
	echo "<option value='city' >".TXT_WPSC_CITY."</option>";
	echo "<option value='country'>".TXT_WPSC_COUNTRY."</option>";
	echo "<option value='delivery_address' >".TXT_WPSC_DELIVERY_ADDRESS."</option>";
	echo "<option value='delivery_city' >".TXT_WPSC_DELIVERY_CITY."</option>";
	echo "<option value='delivery_country'>".TXT_WPSC_DELIVERY_COUNTRY."</option>";
	echo "<option value='textarea' >".TXT_WPSC_TEXTAREA."</option>";    
	echo "<option value='heading' >".TXT_WPSC_HEADING."</option>";
	echo "<option value='coupon' >".TXT_WPSC_COUPON."</option>\";\n\r";
	
	echo "var TXT_WPSC_LABEL = '".TXT_WPSC_LABEL."';\n\r";
	echo "var TXT_WPSC_LABEL_DESC = '".TXT_WPSC_LABEL_DESC."';\n\r";
	echo "var TXT_WPSC_ITEM_NUMBER = '".TXT_WPSC_ITEM_NUMBER."';\n\r";
	echo "var TXT_WPSC_LIFE_NUMBER = '".TXT_WPSC_LIFE_NUMBER."';\n\r";
	echo "var TXT_WPSC_PRODUCT_CODE = '".TXT_WPSC_PRODUCT_CODE."';\n\r";
	echo "var TXT_WPSC_PDF = '".TXT_WPSC_PDF."';\n\r";
	
	echo "var TXT_WPSC_AND_ABOVE = '".TXT_WPSC_AND_ABOVE."';\n\r";
	echo "var TXT_WPSC_IF_PRICE_IS = '".TXT_WPSC_IF_PRICE_IS."';\n\r";
	echo "var TXT_WPSC_IF_WEIGHT_IS = '".TXT_WPSC_IF_WEIGHT_IS."';\n\r";
	exit();
}
if($_GET['wpsc_dynamic_js'] == 'true') {
  add_action("admin_init", 'wpsc_admin_dynamic_js');  
}

function wpsc_admin_dynamic_css() { 
 	header('Content-Type: text/css');
 	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
 	header('Cache-Control: public, must-revalidate, max-age=86400');
 	header('Pragma: public'); 	
	if(get_option('wpsc_use_flash_uploader') == 1) {
		?>
		table.flash-image-uploader {
			display: block;
		}
		
		table.browser-image-uploader {
			display: none;
		}
		<?php
	} else {
		?>
		table.flash-image-uploader {
			display: none;
		}
		
		table.browser-image-uploader {
			display: block;
		}
		<?php
	}
	exit();
}

if($_GET['wpsc_dynamic_css'] == 'true') {
  add_action("admin_init", 'wpsc_admin_dynamic_css');  
}






add_action( 'admin_head', 'wp_tiny_mce' );
//add_action("admin_init", 'wpsc_admin_css_and_js');  
add_action('admin_menu', 'wpsc_admin_pages');



?>