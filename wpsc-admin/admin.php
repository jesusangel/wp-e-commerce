<?php
/**
 * WP eCommerce Main Admin functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

// admin includes
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-update.page.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-items.page.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-groups.page.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-variations.page.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-upgrades.page.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/includes/display-items-functions.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/includes/product-functions.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/includes/save-data.functions.php');
require_once( WPSC_FILE_PATH . '/wpsc-admin/includes/updating-functions.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-coupons.php' );
require_once( WPSC_FILE_PATH . '/wpsc-includes/purchaselogs.class.php' );
require_once( WPSC_FILE_PATH . '/wpsc-includes/theming.class.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/ajax-and-init.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-options-settings.page.php' );
require_once( WPSC_FILE_PATH . '/wpsc-admin/display-sales-logs.php' );
if ( (isset( $_SESSION['wpsc_activate_debug_page'] ) && ($_SESSION['wpsc_activate_debug_page'] == true)) || (defined( 'WPSC_ADD_DEBUG_PAGE' ) && (constant( 'WPSC_ADD_DEBUG_PAGE' ) == true)) )
	require_once( WPSC_FILE_PATH . '/wpsc-admin/display-debug.page.php' );

//settings pages include
require_once( WPSC_FILE_PATH . '/wpsc-admin/includes/settings-pages/general.php' );

if ( !get_option( 'wpsc_checkout_form_sets' ) ) {
	$form_sets = array( 'Default Checkout Forms' );
	update_option( 'wpsc_checkout_form_sets', $form_sets );
}

/**
 * wpsc_admin_pages function, all the definitons of admin pages are stores here.
 * No parameters, returns nothing
 *
 * Fairly standard wordpress plugin API stuff for adding the admin pages, rearrange the order to rearrange the pages
 * The bits to display the options page first on first use may be buggy, but tend not to stick around long enough to be identified and fixed
 * if you find bugs, feel free to fix them.
 *
 * If the permissions are changed here, they will likewise need to be changed for the other sections of the admin that either use ajax
 * or bypass the normal download system.
 */
function wpsc_admin_pages() {
	global $userdata, $show_update_page, $post_type, $typenow, $current_screen; // set in /wpsc-admin/display-update.page.php

	// Code to enable or disable the debug page
	if ( isset( $_GET['wpsc_activate_debug_page'] ) ) {
		if ( 'true' == $_GET['wpsc_activate_debug_page'] ) {
			$_SESSION['wpsc_activate_debug_page'] = true;
		} else if ( 'false' == $_GET['wpsc_activate_debug_page'] ) {
			$_SESSION['wpsc_activate_debug_page'] = false;
		}
	}

	// Only add pages if the function exists to do so?
	if ( function_exists( 'add_options_page' ) ) {

		// Add to Dashboard
		$page_hooks[] = $purchase_log_page = add_submenu_page( 'index.php', __( 'Store Sales', 'wpsc' ), __( 'Store Sales', 'wpsc' ), 'administrator', 'wpsc-sales-logs', 'wpsc_display_sales_logs' );

//		This doesnt work,, need a better trigger
//		if ( !isset( $show_update_page ) )
			$show_update_page = 1;

		if ( 1 === (int)$show_update_page )
			$page_hooks[] = add_submenu_page( 'index.php', __( 'Update Store', 'wpsc' ), __( 'Store Update', 'wpsc' ), 'administrator', 'wpsc-update', 'wpsc_display_update_page' );

		$page_hooks[] = add_submenu_page( 'index.php', __( 'Store Upgrades', 'wpsc' ), __( 'Store Upgrades', 'wpsc' ), 'administrator', 'wpsc-upgrades', 'wpsc_display_upgrades_page' );

		// Set the base page for Products
		$products_page = 'wpsc-edit-products';

		$page_hooks[] = $edit_coupons_page = add_submenu_page( 'edit.php?post_type=wpsc-product' , __( 'Coupons', 'wpsc' ), __( 'Coupons', 'wpsc' ), 'administrator', 'wpsc-edit-coupons', 'wpsc_display_coupons_page' );

		// Add Settings pages
		$page_hooks[] = $edit_options_page = add_options_page( __( 'Store Settings', 'wpsc' ), __( 'Store', 'wpsc' ), 'administrator', 'wpsc-settings', 'wpsc_display_settings_page' );
		add_action('admin_print_scripts-' . $edit_options_page , 'wpsc_print_admin_scripts');

		// Debug Page
		if ( ( defined( 'WPSC_ADD_DEBUG_PAGE' ) && ( WPSC_ADD_DEBUG_PAGE == true ) ) || ( isset( $_SESSION['wpsc_activate_debug_page'] ) && ( true == $_SESSION['wpsc_activate_debug_page'] ) ) )
			$page_hooks[] = add_options_page( __( 'Store Debug' ), __( 'Store Debug' ), 'administrator', 'wpsc-debug', 'wpsc_debug_page' );

		// Contextual help
		if ( function_exists( 'add_contextual_help' ) ) {
			$header = '<p><strong>' . __( 'For More Information', 'wpsc' ) . '</strong></p>';

			add_contextual_help( 'toplevel_page_wpsc-sales-logs',        $header . "<a target='_blank' href='http://getshopped.org/resources/docs/building-your-store/sales/'>About the Sales Page</a>" );
			add_contextual_help( 'toplevel_page_wpsc-edit-products',     $header . "<a target='_blank' href='http://getshopped.org/resources/docs/building-your-store/products'>About the Products Page</a>" );
			add_contextual_help( 'products_page_wpsc-edit-groups',       $header . "<a target='_blank' href='http://getshopped.org/resources/docs/building-your-store/categories/'>About the Categories Page</a>" );
			add_contextual_help( 'products_page_edit-tags',              $header . "<a target='_blank' href='http://getshopped.org/resources/docs/building-your-store/variations/'>About the Variations Page</a>" );
			add_contextual_help( 'settings_page_wpsc-settings',          $header . "<a target='_blank' href='http://getshopped.org/resources/docs/store-settings/general/'>General Settings</a><br />
																						<a target='_blank' href='http://getshopped.org/resources/docs/store-settings/checkout/'>Checkout Options</a> <br />" );
                        add_contextual_help( 'products_page_wpsc-edit-coupons',          $header . "<a target='_blank' href='http://getshopped.org/resources/docs/building-your-store/marketing'>Marketing Options</a><br />");
		}
 
		$page_hooks = apply_filters( 'wpsc_additional_pages', $page_hooks, $products_page );

		do_action( 'wpsc_add_submenu' );
	};

	// Include the javascript and CSS for this page
	// This is so important that I can't even express it in one line

	foreach ( $page_hooks as $page_hook ) {
		add_action( 'load-' . $page_hook, 'wpsc_admin_include_css_and_js_refac' );

                switch ( $page_hook ) {

			case $edit_options_page :
				add_action( 'load-' . $page_hook, 'wpsc_admin_include_optionspage_css_and_js' );
				break;

			case $purchase_log_page :
				add_action( 'admin_head', 'wpsc_product_log_rss_feed' );
				break;
				
			case $edit_coupons_page :
				add_action( 'load-' . $page_hook, 'wpsc_admin_include_coupon_js' );
				break;
		}
	}

	// Some updating code is run from here, is as good a place as any, and better than some
	if ( ( null == get_option( 'wpsc_trackingid_subject' ) ) && ( null == get_option( 'wpsc_trackingid_message' ) ) ) {
		update_option( 'wpsc_trackingid_subject', __( 'Product Tracking Email', 'wpsc' ) );
		update_option( 'wpsc_trackingid_message', __( "Track & Trace means you may track the progress of your parcel with our online parcel tracker, just login to our website and enter the following Tracking ID to view the status of your order.\n\nTracking ID: %trackid%\n", 'wpsc' ) );
	}

	return;
}
function wpsc_product_log_rss_feed() {
	echo "<link type='application/rss+xml' href='" . get_option( 'siteurl' ) . "/wp-admin/index.php?rss=true&amp;rss_key=key&amp;action=purchase_log&amp;type=rss' title='WP e-Commerce Purchase Log RSS' rel='alternate'/>";
}
function wpsc_admin_include_coupon_js() {

	// Variables
	$siteurl            = get_option( 'siteurl' );
	$version_identifier = WPSC_VERSION . "." . WPSC_MINOR_VERSION;

	// Coupon CSS
	wp_enqueue_style( 'wp-e-commerce-admin_2.7',        WPSC_URL         . '/wpsc-admin/css/settingspage.css', false, false,               'all' );
	wp_enqueue_style( 'wp-e-commerce-admin',            WPSC_URL         . '/wpsc-admin/css/admin.css',        false, $version_identifier, 'all' );

	// Coupon JS
	wp_enqueue_script( 'wp-e-commerce-admin-parameters', $siteurl        . '/wp-admin/admin.php?wpsc_admin_dynamic_js=true', false,                     $version_identifier );
	wp_enqueue_script( 'livequery',                     WPSC_URL         . '/wpsc-admin/js/jquery.livequery.js',             array( 'jquery' ),         '1.0.3' );
	wp_enqueue_script( 'datepicker-ui',                 WPSC_CORE_JS_URL . '/ui.datepicker.js',                              array( 'jquery-ui-core' ), $version_identifier );
	wp_enqueue_script( 'wp-e-commerce-admin_legacy',    WPSC_URL         . '/wpsc-admin/js/admin-legacy.js',                 array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'datepicker-ui' ), $version_identifier );
}

/**
 * wpsc_admin_css_and_js function, includes the wpsc_admin CSS and JS
 * No parameters, returns nothing
 */
function wpsc_admin_include_css_and_js(  ) {
	$siteurl = get_option( 'siteurl' );
	if ( is_ssl ( ) )
		$siteurl = str_replace( "http://", "https://", $siteurl );

	wp_admin_css( 'dashboard' );
	//wp_admin_css( 'media' );

	$version_identifier = WPSC_VERSION . "." . WPSC_MINOR_VERSION;
	wp_enqueue_script( 'livequery',                      WPSC_URL . '/wpsc-admin/js/jquery.livequery.js',             array( 'jquery' ), '1.0.3' );
	wp_enqueue_script( 'wp-e-commerce-admin-parameters', $siteurl . '/wp-admin/admin.php?wpsc_admin_dynamic_js=true', false,             $version_identifier );
	wp_enqueue_script( 'wp-e-commerce-admin',            WPSC_URL . '/wpsc-admin/js/admin.js',                        array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), $version_identifier, false );
	wp_enqueue_script( 'wp-e-commerce-legacy-ajax',      WPSC_URL . '/wpsc-admin/js/ajax.js',                         false,             $version_identifier ); // needs removing
	wp_enqueue_script( 'wp-e-commerce-variations',       WPSC_URL . '/wpsc-admin/js/variations.js',                   array( 'jquery' ), $version_identifier );

	wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL . '/wpsc-admin/css/admin.css', false, $version_identifier, 'all' );
	wp_enqueue_style( 'wp-e-commerce-admin-dynamic', $siteurl . "/wp-admin/admin.php?wpsc_admin_dynamic_css=true", false, $version_identifier, 'all' );

        // Prototype breaks dragging and dropping, I need it gone
	wp_deregister_script( 'prototype' );

        // remove the old javascript and CSS, we want it no more, it smells bad
	remove_action( 'admin_head', 'wpsc_admin_css' );
}

/**
 * wpsc_admin_include_optionspage_css_and_js function, includes the wpsc_admin CSS and JS for the specific options page
 * No parameters, returns nothing
 */
function wpsc_admin_include_optionspage_css_and_js() {
	$version_identifier = WPSC_VERSION . "." . WPSC_MINOR_VERSION;
	wp_enqueue_script( 'wp-e-commerce-js-ajax', WPSC_URL . '/wpsc-core/js/ajax.js', false, $version_identifier );
	wp_enqueue_script( 'wp-e-commerce-js-ui-tabs', WPSC_URL . '/wpsc-admin/js/jquery-ui.js', false, $version_identifier );
	wp_enqueue_script( 'wp-e-commerce-js-dimensions', WPSC_URL . '/wpsc-admin/js/dimensions.js', false, $version_identifier );
	wp_enqueue_style( 'wp-e-commerce-admin_2.7', WPSC_URL . '/wpsc-admin/css/settingspage.css', false, false, 'all' );
	wp_enqueue_style( 'wp-e-commerce-ui-tabs', WPSC_URL . '/wpsc-admin/css/jquery.ui.tabs.css', false, $version_identifier, 'all' );
}

function wpsc_meta_boxes() {

	$pagename = 'wpsc-product';
        remove_meta_box( 'wpsc-variationdiv', 'wpsc-product', 'core' );
	add_meta_box( 'wpsc_price_control_forms','Price Control','wpsc_price_control_forms',$pagename,'side','low' );
	add_meta_box( 'wpsc_stock_control_forms','Stock Control','wpsc_stock_control_forms',$pagename,'side','low' );
	add_meta_box( 'wpsc_product_taxes_forms','Taxes','wpsc_product_taxes_forms',$pagename,'side','low' );
	add_meta_box( 'wpsc_additional_desc', 'Additional Description', 'wpsc_additional_desc', $pagename, 'normal', 'high' );
	add_meta_box( 'wpsc_product_variation_forms', 'Variations', 'wpsc_product_variation_forms', $pagename, 'normal', 'high' );
	add_meta_box( 'wpsc_product_download_forms', 'Product Download', 'wpsc_product_download_forms', $pagename, 'normal', 'high' );
	add_meta_box( 'wpsc_product_image_forms', 'Product Images', 'wpsc_product_image_forms', $pagename, 'normal', 'high' );
	add_meta_box( 'wpsc_product_shipping_forms', 'Shipping', 'wpsc_product_shipping_forms', $pagename, 'normal', 'high' );
	add_meta_box( 'wpsc_product_advanced_forms', 'Advanced Settings', 'wpsc_product_advanced_forms', $pagename, 'normal', 'high' );
	add_meta_box( 'wpsc_product_external_link_forms', 'Off Site Product link', 'wpsc_product_external_link_forms', $pagename, 'normal', 'high' );
}

add_action( 'admin_footer', 'wpsc_meta_boxes' );

add_action( 'admin_head', 'wpsc_admin_include_css_and_js' );
add_action( 'admin_head', 'wpsc_admin_include_css_and_js_refac' );
add_action( 'admin_enqueue_scripts', 'wpsc_admin_include_css_and_js_refac' );
function wpsc_admin_include_css_and_js_refac( $pagehook )  {

      global $post_type, $current_screen;
      
	$siteurl = get_option( 'siteurl' );
	if ( is_ssl ( ) )
		$siteurl = str_replace( "http://", "https://", $siteurl );

	wp_admin_css( 'dashboard' );

	$version_identifier = WPSC_VERSION . "." . WPSC_MINOR_VERSION;
        $pages = array( 'index.php', 'options-general.php', 'edit.php', 'post.php', 'post-new.php' );

          if ( ( in_array( $pagehook, $pages ) && $post_type == 'wpsc-product' )  || $current_screen->id == 'dashboard_page_wpsc-sales-logs' || $current_screen->id == 'settings_page_wpsc-settings' || $current_screen->id == 'wpsc-product_page_wpsc-edit-coupons') {
            wp_enqueue_script( 'livequery',                      WPSC_URL . '/wpsc-admin/js/jquery.livequery.js',             array( 'jquery' ), '1.0.3' );
            wp_enqueue_script( 'wp-e-commerce-admin-parameters', $siteurl . '/wp-admin/admin.php?wpsc_admin_dynamic_js=true', false,             $version_identifier );
            wp_enqueue_script( 'wp-e-commerce-admin',            WPSC_URL . '/wpsc-admin/js/admin.js',                        array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), $version_identifier, false );
            wp_enqueue_script( 'wp-e-commerce-legacy-ajax',      WPSC_URL . '/wpsc-admin/js/ajax.js',                         false,             $version_identifier ); // needs removing
            wp_enqueue_script( 'wp-e-commerce-variations',       WPSC_URL . '/wpsc-admin/js/variations.js',                   array( 'jquery' ), $version_identifier );
            wp_enqueue_script('inline-edit-post');
            wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL . '/wpsc-admin/css/admin.css', false, $version_identifier, 'all' );
            wp_enqueue_style( 'wp-e-commerce-admin-dynamic', $siteurl . "/wp-admin/admin.php?wpsc_admin_dynamic_css=true", false, $version_identifier, 'all' );
        }
        // Prototype breaks dragging and dropping, I need it gone
	wp_deregister_script( 'prototype' );

        // remove the old javascript and CSS, we want it no more, it smells bad
	remove_action( 'admin_head', 'wpsc_admin_css' );
}

function wpsc_admin_dynamic_js() {
	header( 'Content-Type: text/javascript' );
	header( 'Expires: ' . gmdate( 'r', mktime( 0, 0, 0, date( 'm' ), (date( 'd' ) + 12 ), date( 'Y' ) ) ) . '' );
	header( 'Cache-Control: public, must-revalidate, max-age=86400' );
	header( 'Pragma: public' );

	$siteurl = get_option( 'siteurl' );
	$hidden_boxes = get_option( 'wpsc_hidden_box' );

	$form_types1 = get_option( 'wpsc_checkout_form_fields' );
	$unique_names1 = get_option( 'wpsc_checkout_unique_names' ); 
	
	$form_types = '';
	foreach ( (array)$form_types1 as $form_type ) {
		$form_types .= "<option value='" . $form_type . "'>" . __( $form_type, 'wpsc' ) . "</option>";
	}

	$unique_names = "<option value='-1'>Select a Unique Name</option>";
	foreach ( (array)$unique_names1 as $unique_name ) {
		$unique_names.= "<option value='" . $unique_name . "'>" . $unique_name . "</option>";
	}

	$hidden_boxes = implode( ',', (array)$hidden_boxes );

	echo "var base_url = '" . $siteurl . "';\n\r";
	echo "var WPSC_URL = '" . WPSC_URL . "';\n\r";
	echo "var WPSC_IMAGE_URL = '" . WPSC_IMAGE_URL . "';\n\r";
	echo "var WPSC_DIR_NAME = '" . WPSC_DIR_NAME . "';\n\r";
	echo "var WPSC_IMAGE_URL = '" . WPSC_IMAGE_URL . "';\n\r";

	// LightBox Configuration start
	echo "var fileLoadingImage = '" . WPSC_CORE_IMAGES_URL . "/loading.gif';\n\r";
	echo "var fileBottomNavCloseImage = '" . WPSC_CORE_IMAGES_URL . "/closelabel.gif';\n\r";
	echo "var fileThickboxLoadingImage = '" . WPSC_CORE_IMAGES_URL . "/loadingAnimation.gif';\n\r";

	echo "var resizeSpeed = 9;\n\r";

	echo "var borderSize = 10;\n\r";

	echo "var hidden_boxes = '" . $hidden_boxes . "';\n\r";
	echo "var IS_WP27 = '" . IS_WP27 . "';\n\r";
	echo "var TXT_WPSC_DELETE = '" . __( 'Delete', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_TEXT = '" . __( 'Text', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_EMAIL = '" . __( 'Email', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_COUNTRY = '" . __( 'Country', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_TEXTAREA = '" . __( 'Textarea', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_HEADING = '" . __( 'Heading', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_COUPON = '" . __( 'Coupon', 'wpsc' ) . "';\n\r";

	echo "var HTML_FORM_FIELD_TYPES =\" " . $form_types . "; \" \n\r";
	echo "var HTML_FORM_FIELD_UNIQUE_NAMES = \" " . $unique_names . "; \" \n\r";

	echo "var TXT_WPSC_LABEL = '" . __( 'Label', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_LABEL_DESC = '" . __( 'Label Description', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_ITEM_NUMBER = '" . __( 'Item Number', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_LIFE_NUMBER = '" . __( 'Life Number', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_PRODUCT_CODE = '" . __( 'Product Code', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_PDF = '" . __( 'PDF', 'wpsc' ) . "';\n\r";

	echo "var TXT_WPSC_AND_ABOVE = '" . __( ' and above', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_IF_PRICE_IS = '" . __( 'If price is ', 'wpsc' ) . "';\n\r";
	echo "var TXT_WPSC_IF_WEIGHT_IS = '" . __( 'If weight is ', 'wpsc' ) . "';\n\r";

	exit();
}

if ( isset( $_GET['wpsc_admin_dynamic_js'] ) && ($_GET['wpsc_admin_dynamic_js'] == 'true') ) {
	add_action( "admin_init", 'wpsc_admin_dynamic_js' );
}

function wpsc_admin_dynamic_css() {
	header( 'Content-Type: text/css' );
	header( 'Expires: ' . gmdate( 'r', mktime( 0, 0, 0, date( 'm' ), (date( 'd' ) + 12 ), date( 'Y' ) ) ) . '' );
	header( 'Cache-Control: public, must-revalidate, max-age=86400' );
	header( 'Pragma: public' );
	$flash = 0;
	$flash = apply_filters( 'flash_uploader', $flash );

	if ( $flash = 1 ) {
?>
		div.flash-image-uploader {
			display: block;
		}

		div.browser-image-uploader {
			display: none;
		}
<?php
	} else {
?>
		div.flash-image-uploader {
			display: none;
		}

		div.browser-image-uploader {
			display: block;
		}
<?php
	}
	exit();
}

if ( isset( $_GET['wpsc_admin_dynamic_css'] ) && ($_GET['wpsc_admin_dynamic_css'] == 'true') ) {
	add_action( "admin_init", 'wpsc_admin_dynamic_css' );
}

add_action( 'admin_menu', 'wpsc_admin_pages' );


function wpsc_admin_latest_activity() {
	global $wpdb;
	$totalOrders = $wpdb->get_var( "SELECT COUNT(*) FROM `" . WPSC_TABLE_PURCHASE_LOGS . "`" );

	/*
	 * This is the right hand side for the past 30 days revenue on the wp dashboard
	 */
	echo "<div id='leftDashboard'>";
	echo "<strong class='dashboardHeading'>" . __( 'Current Month', 'wpsc' ) . "</strong><br />";
	echo "<p class='dashboardWidgetSpecial'>";
	// calculates total amount of orders for the month
	$year = date( "Y" );
	$month = date( "m" );
	$start_timestamp = mktime( 0, 0, 0, $month, 1, $year );
	$end_timestamp = mktime( 0, 0, 0, ($month + 1 ), 0, $year );
	$sql = "SELECT COUNT(*) FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' AND `processed` IN (2,3,4) ORDER BY `date` DESC";
	$currentMonthOrders = $wpdb->get_var( $sql );

	//calculates amount of money made for the month
	$currentMonthsSales = wpsc_currency_display( admin_display_total_price( $start_timestamp, $end_timestamp ) );
	echo $currentMonthsSales;
	echo "<span class='dashboardWidget'>" . __( 'Sales', 'wpsc' ) . "</span>";
	echo "</p>";
	echo "<p class='dashboardWidgetSpecial'>";
	echo "<span class='pricedisplay'>";
	echo $currentMonthOrders;
	echo "</span>";
	echo "<span class='dashboardWidget'>" . __( 'Orders', 'wpsc' ) . "</span>";
	echo "</p>";
	echo "<p class='dashboardWidgetSpecial'>";
	//calculates average sales amount per order for the month
	if ( $currentMonthOrders > 0 ) {
		$monthsAverage = ((int)admin_display_total_price( $start_timestamp, $end_timestamp ) / (int)$currentMonthOrders);
		echo wpsc_currency_display( $monthsAverage );
	}
	//echo "</span>";
	echo "<span class='dashboardWidget'>" . __( 'Avg Orders', 'wpsc' ) . "</span>";
	echo "</p>";
	echo "</div>";
	/*
	 * This is the left side for the total life time revenue on the wp dashboard
	 */

	echo "<div id='rightDashboard' >";
	echo "<strong class='dashboardHeading'>" . __( 'Total Income', 'wpsc' ) . "</strong><br />";

	echo "<p class='dashboardWidgetSpecial'>";
	echo wpsc_currency_display( admin_display_total_price() );
	echo "<span class='dashboardWidget'>" . __( 'Sales', 'wpsc' ) . "</span>";
	echo "</p>";
	echo "<p class='dashboardWidgetSpecial'>";
	echo "<span class='pricedisplay'>";
	echo $totalOrders;
	echo "</span>";
	echo "<span class='dashboardWidget'>" . __( 'Orders', 'wpsc' ) . "</span>";
	echo "</p>";
	echo "<p class='dashboardWidgetSpecial'>";
	//calculates average sales amount per order for the month
	if ( (admin_display_total_price() > 0) && ($totalOrders > 0) ) {
		$totalAverage = ((int)admin_display_total_price() / (int)$totalOrders);
	} else {
		$totalAverage = 0;
	}
	echo wpsc_currency_display( $totalAverage );
	//echo "</span>";
	echo "<span class='dashboardWidget'>" . __( 'Avg Orders', 'wpsc' ) . "</span>";
	echo "</p>";
	echo "</div>";
	echo "<div style='clear:both'></div>";
}

add_action( 'wpsc_admin_pre_activity', 'wpsc_admin_latest_activity' );


/*
 * Dashboard Widget Setup
 * Adds the dashboard widgets if the user is an admin
 * Since 3.6
 */

function wpsc_dashboard_widget_setup() {
	global $current_user;
	get_currentuserinfo();
	if ( is_admin() ) {
		$version_identifier = WPSC_VERSION . "." . WPSC_MINOR_VERSION;
		// Enqueue the styles and scripts necessary
		wp_enqueue_style( 'wp-e-commerce-admin', WPSC_URL . '/wpsc-admin/css/admin.css', false, $version_identifier, 'all' );
		wp_enqueue_script( 'datepicker-ui', WPSC_URL . "/wpsc-core/js/ui.datepicker.js", array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), $version_identifier );
		// Add the dashboard widgets
		wp_add_dashboard_widget( 'wpsc_dashboard_news', __( 'Getshopped News' , 'wpsc' ), 'wpsc_dashboard_news' );
		wp_add_dashboard_widget( 'wpsc_dashboard_widget', __( 'Sales Summary', 'wpsc' ), 'wpsc_dashboard_widget' );
		wp_add_dashboard_widget( 'wpsc_quarterly_dashboard_widget', __( 'Sales by Quarter', 'wpsc' ), 'wpsc_quarterly_dashboard_widget' );
		wp_add_dashboard_widget( 'wpsc_dashboard_4months_widget', __( 'Sales by Month', 'wpsc' ), 'wpsc_dashboard_4months_widget' );
	
		// Sort the Dashboard widgets so ours it at the top
		global $wp_meta_boxes;
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
		// Backup and delete our new dashbaord widget from the end of the array
		$wpsc_widget_backup = array('wpsc_dashboard_news' => $normal_dashboard['wpsc_dashboard_news']);
		$wpsc_widget_backup += array('wpsc_dashboard_widget' => $normal_dashboard['wpsc_dashboard_widget']);	
		$wpsc_widget_backup += array('wpsc_quarterly_dashboard_widget' => $normal_dashboard['wpsc_quarterly_dashboard_widget']);
		$wpsc_widget_backup += array('wpsc_dashboard_4months_widget' => $normal_dashboard['wpsc_dashboard_4months_widget']);

		unset($normal_dashboard['wpsc_dashboard_news']);
		unset($normal_dashboard['wpsc_dashboard_widget']);
		unset($normal_dashboard['wpsc_quarterly_dashboard_widget']);
		unset($normal_dashboard['wpsc_dashboard_4months_widget']);
	
		// Merge the two arrays together so our widget is at the beginning
	
		$sorted_dashboard = array_merge($wpsc_widget_backup, $normal_dashboard);
	
		// Save the sorted array back into the original metaboxes 
	
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}
}

/*
 * 	Registers the widgets on the WordPress Dashboard
 */

add_action( 'wp_dashboard_setup', 'wpsc_dashboard_widget_setup' );

function wpsc_dashboard_news(){
	$rss = fetch_feed('http://getshopped.org/category/wp-e-commerce-plugin/'); 
	$args = array('show_author' => 1, 'show_date' => 1, 'show_summary' => 1, 'items'=>3 );
	wp_widget_rss_output($rss, $args); 

}

function wpsc_get_quarterly_summary() {
	global $wpdb;
	(int)$firstquarter = get_option( 'wpsc_first_quart' );
	(int)$secondquarter = get_option( 'wpsc_second_quart' );
	(int)$thirdquarter = get_option( 'wpsc_third_quart' );
	(int)$fourthquarter = get_option( 'wpsc_fourth_quart' );
	(int)$finalquarter = get_option( 'wpsc_final_quart' );

	$results[] = admin_display_total_price( $thirdquarter + 1, $fourthquarter );
	$results[] = admin_display_total_price( $secondquarter + 1, $thirdquarter );
	$results[] = admin_display_total_price( $firstquarter + 1, $secondquarter );
	$results[] = admin_display_total_price( $finalquarter, $firstquarter );
	return $results;
}

function wpsc_quarterly_dashboard_widget() {
	if ( get_option( 'wpsc_business_year_start' ) == false ) {
?>
		<form action='' method='post'>
			<label for='date_start'><?php _e('Financial Year End' ,'wpsc'); ?>: </label>
			<input id='date_start' type='text' class='pickdate' size='11' value='<?php echo get_option( 'wpsc_last_date' ); ?>' name='add_start' />
			   <!--<select name='add_start[day]'>
<?php
		for ( $i = 1; $i <= 31; ++$i ) {
			$selected = '';
			if ( $i == date( "d" ) ) {
				$selected = "selected='selected'";
			}
			echo "<option $selected value='$i'>$i</option>";
		}
?>
				   </select>
		   <select name='add_start[month]'>
	<?php
		for ( $i = 1; $i <= 12; ++$i ) {
			$selected = '';
			if ( $i == (int)date( "m" ) ) {
				$selected = "selected='selected'";
			}
			echo "<option $selected value='$i'>" . date( "M", mktime( 0, 0, 0, $i, 1, date( "Y" ) ) ) . "</option>";
		}
	?>
				   </select>
		   <select name='add_start[year]'>
	<?php
		for ( $i = date( "Y" ); $i <= (date( "Y" ) + 12); ++$i ) {
			$selected = '';
			if ( $i == date( "Y" ) ) {
				$selected = "selected='true'";
			}
			echo "<option $selected value='$i'>" . $i . "</option>";
		}
	?>
				   </select>-->
		<input type='hidden' name='wpsc_admin_action' value='wpsc_quarterly' />
		<input type='submit' class='button primary' value='Submit' name='wpsc_submit' />
	</form>
<?php
		if ( get_option( 'wpsc_first_quart' ) != '' ) {
			$firstquarter = get_option( 'wpsc_first_quart' );
			$secondquarter = get_option( 'wpsc_second_quart' );
			$thirdquarter = get_option( 'wpsc_third_quart' );
			$fourthquarter = get_option( 'wpsc_fourth_quart' );
			$finalquarter = get_option( 'wpsc_final_quart' );
			$revenue = wpsc_get_quarterly_summary();
			$currsymbol = wpsc_get_currency_symbol();
			foreach ( $revenue as $rev ) {
				if ( $rev == '' ) {
					$totals[] = '0.00';
				} else {
					$totals[] = $rev;
				}
			}
?>
			<div id='box'>
				<p class='atglance'>
					<span class='wpsc_quart_left'><?php _e('At a Glance' , 'wpsc'); ?></span>
					<span class='wpsc_quart_right'><?php _e('Revenue' , 'wpsc'); ?></span>
				</p>
				<div style='clear:both'></div>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>01</strong>&nbsp; (<?php echo date( 'M Y', $thirdquarter ) . ' - ' . date( 'M Y', $fourthquarter ); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol . ' ' . $totals[0]; ?></span></p>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>02</strong>&nbsp; (<?php echo date( 'M Y', $secondquarter ) . ' - ' . date( 'M Y', $thirdquarter ); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol . ' ' . $totals[1]; ?></span></p>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>03</strong>&nbsp; (<?php echo date( 'M Y', $firstquarter ) . ' - ' . date( 'M Y', $secondquarter ); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol . ' ' . $totals[2]; ?></span></p>
				<p class='quarterly'>
					<span class='wpsc_quart_left'><strong>04</strong>&nbsp; (<?php echo date( 'M Y', $finalquarter ) . ' - ' . date( 'M Y', $firstquarter ); ?>)</span>
					<span class='wpsc_quart_right'><?php echo $currsymbol . ' ' . $totals[3]; ?></span>
				</p>
				<div style='clear:both'></div>
			</div>
<?php
		}
	}
}


function wpsc_dashboard_widget() {
	global $current_user;
	get_currentuserinfo();
	if ( $current_user->user_level > 9 ) {
		do_action( 'wpsc_admin_pre_activity' );
		do_action( 'wpsc_admin_post_activity' );
	}
}

/*
 * END - Dashboard Widget for 2.7
 */


/*
 * Dashboard Widget Last Four Month Sales.
 */

function wpsc_dashboard_4months_widget() {
	global $wpdb;

	$this_year = date( "Y" ); //get current year and month
	$this_month = date( "n" );

	$months[] = mktime( 0, 0, 0, $this_month - 3, 1, $this_year ); //generate  unix time stamps fo 4 last months
	$months[] = mktime( 0, 0, 0, $this_month - 2, 1, $this_year );
	$months[] = mktime( 0, 0, 0, $this_month - 1, 1, $this_year );
	$months[] = mktime( 0, 0, 0, $this_month, 1, $this_year );

	$products = $wpdb->get_results( "SELECT `cart`.`prodid`,
	 `cart`.`name` 
	 FROM `" . WPSC_TABLE_CART_CONTENTS . "` AS `cart`
	 INNER JOIN `" . WPSC_TABLE_PURCHASE_LOGS . "` AS `logs`
	 ON `cart`.`purchaseid` = `logs`.`id` 
	 WHERE `logs`.`processed` >= 2 
	 AND `logs`.`date` >= " . $months[0] . "
	 GROUP BY `cart`.`prodid` 
	 ORDER BY SUM(`cart`.`price` * `cart`.`quantity`) DESC 
	 LIMIT 4", ARRAY_A ); //get 4 products with top income in 4 last months.

	$timeranges[0]["start"] = mktime( 0, 0, 0, $this_month - 3, 1, $this_year ); //make array of time ranges
	$timeranges[0]["end"] = mktime( 0, 0, 0, $this_month - 2, 1, $this_year );
	$timeranges[1]["start"] = mktime( 0, 0, 0, $this_month - 2, 1, $this_year );
	$timeranges[1]["end"] = mktime( 0, 0, 0, $this_month - 1, 1, $this_year );
	$timeranges[2]["start"] = mktime( 0, 0, 0, $this_month - 1, 1, $this_year );
	$timeranges[2]["end"] = mktime( 0, 0, 0, $this_month, 1, $this_year );
	$timeranges[3]["start"] = mktime( 0, 0, 0, $this_month, 1, $this_year );
	$timeranges[3]["end"] = mktime();

	$prod_data = array( );
	foreach ( (array)$products as $product ) { //run through products and get each product income amounts and name
		$sale_totals = array( );
		foreach ( $timeranges as $timerange ) { //run through time ranges of product, and get its income over each time range
			$prodsql = "SELECT 
			SUM(`cart`.`price` * `cart`.`quantity`) AS sum 			
			FROM `" . WPSC_TABLE_CART_CONTENTS . "` AS `cart`
			INNER JOIN `" . WPSC_TABLE_PURCHASE_LOGS . "` AS `logs`
				ON `cart`.`purchaseid` = `logs`.`id` 
			WHERE `logs`.`processed` >= 2 
				AND `logs`.`date` >= " . $timerange["start"] . "
				AND `logs`.`date` < " . $timerange["end"] . "
				AND `cart`.`prodid` = " . $product['prodid'] . "
			GROUP BY `cart`.`prodid`"; //get the amount of income that current product has generaterd over current time range
			$sale_totals[] = $wpdb->get_var( $prodsql ); //push amount to array
		}
		$prod_data[] = array(
			'sale_totals' => $sale_totals,
			'product_name' => $product['name'] ); //result: array of 2: $prod_data[0] = array(income)
		$sums = array( ); //reset array				//$prod_data[1] = product name
	}

	$tablerow = 1;
	$output = '';
	$output.='<div style="padding-bottom:15px; ">Last four months of sales on a per product basis:</div>
    <table style="width:100%" border="0" cellspacing="0">
    	<tr style="font-style:italic; color:#666;" height="20">
    		<td colspan="2" style=" font-family:\'Times New Roman\', Times, serif; font-size:15px; border-bottom:solid 1px #000;">At a Glance</td>';
	foreach ( $months as $mnth ) {
		$output.='<td align="center" style=" font-family:\'Times New Roman\'; font-size:15px; border-bottom:solid 1px #000;">' . date( "M", $mnth ) . '</td>';
	}
	$output.='</tr>';
	foreach ( (array)$prod_data as $sales_data ) {
		$output.='<tr height="20">
				<td width="20" style="font-weight:bold; color:#008080; border-bottom:solid 1px #000;">' . $tablerow . '</td>
				<td style="border-bottom:solid 1px #000;width:60px">' . $sales_data['product_name'] . '</td>';
		$currsymbol = wpsc_get_currency_symbol();
		$tablerow++;
		foreach ( $sales_data['sale_totals'] as $amount ) {
			$output.= '<td align="center" style="border-bottom:solid 1px #000;">' . $currsymbol . number_format( absint( $amount ), 2 ) . '</td>';
		}
		$output.='</tr>';
	}
	$output.='</table>';
	echo $output;
}


//Modification to allow for multiple column layout

function wpec_two_columns( $columns, $screen ) {
	if ( $screen == 'toplevel_page_wpsc-edit-products' )
		$columns['toplevel_page_wpsc-edit-products'] = 2;

	return $columns;
}
add_filter( 'screen_layout_columns', 'wpec_two_columns', 10, 2 );

function wpsc_fav_action( $actions ) {
	$actions['admin.php?page=wpsc-edit-products&amp;action=wpsc_add_edit'] = array( 'New Product', 'manage_options' );
	return $actions;
}
add_filter( 'favorite_actions', 'wpsc_fav_action' );

function wpsc_print_admin_scripts(){
	global $version_identifier;
	wp_enqueue_script( 'wp-e-commerce-dynamic',       get_bloginfo('url')			. "/index.php?wpsc_user_dynamic_js=true", false,             $version_identifier );
}

/**
 * wpsc_update_permalinks update the product pages permalinks when WordPress permalinks are changed
 * @public 
 *
 * @3.8 
 * @returns nothing
 */
function wpsc_update_permalinks($return = ''){
wpsc_update_page_urls(true);
return $return;
}
add_action('permalink_structure_changed' ,'wpsc_update_permalinks');
add_action('get_sample_permalink_html' ,'wpsc_update_permalinks');
add_action('wp_ajax_category_sort_order', 'wpsc_ajax_set_category_order');


?>