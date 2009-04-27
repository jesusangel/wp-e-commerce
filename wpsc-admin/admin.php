<?php
/**
 * WP eCommerce Main Admin functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

require_once(WPSC_FILE_PATH."/wpsc-admin/ajax.php");
require_once(WPSC_FILE_PATH."/wpsc-admin/display-items.page.php");

function wpsc_admin_pages(){
  global $userdata;
    /*
     * Fairly standard wordpress plugin API stuff for adding the admin pages, rearrange the order to rearrange the pages
     * The bits to display the options page first on first use may be buggy, but tend not to stick around long enough to be identified and fixed
     * if you find bugs, feel free to fix them.
     *
     * If the permissions are changed here, they will likewise need to be changed for the other secions of the admin that either use ajax
     * or bypass the normal download system.
     * its in an object because nobody has moved it out of the object yet.
     */
    if(function_exists('add_options_page')) {
				//       if(get_option('nzshpcrt_first_load') == 0) {
				//         $base_page = WPSC_DIR_NAME.'/options.php';
				//         add_menu_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 7, $base_page);
				//         add_submenu_page($base_page,TXT_WPSC_OPTIONS, TXT_WPSC_OPTIONS, 7, WPSC_DIR_NAME.'/options.php');
				//         } else {
			$base_page = WPSC_DIR_NAME.'/display-log.php';
			
			
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


			

			add_submenu_page(WPSC_DIR_NAME.'/display-log.php',TXT_WPSC_PURCHASELOG, TXT_WPSC_PURCHASELOG, 7, WPSC_DIR_NAME.'/display-log.php');
				//         }
			//written by allen
			add_submenu_page('users.php',TXT_WPSC_ECOMMERCE_SUBSCRIBERS, TXT_WPSC_ECOMMERCE_SUBSCRIBERS, 7, WPSC_DIR_NAME.'/display-ecommerce-subs.php');
			//exit(ABSPATH.'wp-admin/users.php');
			//end of written by allen
			//Jeffs code
			add_submenu_page($base_page,TXT_WPSC_PURCHASELOG.'new', TXT_WPSC_PURCHASELOG.'new', 7, WPSC_DIR_NAME.'/wpsc-admin/display-sales-logs.php');
			$display_items_page = add_submenu_page($base_page,TXT_WPSC_PRODUCTS, TXT_WPSC_PRODUCTS, 7, WPSC_DIR_NAME.'/display-items.php');
			
			$display_items_page = add_submenu_page($base_page,TXT_WPSC_PRODUCTS, TXT_WPSC_PRODUCTS, 7, WPSC_DIR_NAME.'/wpsc-admin/display-items.page.php', 'wpsc_display_products_page');
			
			
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
		return;
  
  
  }
  
  
add_action('admin_menu', 'wpsc_admin_pages');



?>