<?php


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
require_once(WPSC_FILE_PATH."/wpsc-includes/category.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/processing.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/form-display.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/merchant.class.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/meta.functions.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/productfeed.php");

//add taxes
require_once(WPSC_FILE_PATH."/wpec-taxes/taxes_module.php");
require_once(WPSC_FILE_PATH."/wpsc-includes/upgrades.php");


if (!IS_WP25) {
	require_once(WPSC_FILE_PATH.'/editor.php');
} else { 
	require_once(WPSC_FILE_PATH.'/js/tinymce3/tinymce.php');
}

if((get_option('wpsc_share_this') == 1) && (get_option('product_list_url') != '')) {
    include_once(WPSC_FILE_PATH."/share-this.php");
}

$wpsc_currency_data = array();
$wpsc_title_data = array();
require_once(WPSC_FILE_PATH."/currency_converter.inc.php"); 
require_once(WPSC_FILE_PATH."/shopping_cart_functions.php"); 
require_once(WPSC_FILE_PATH."/homepage_products_functions.php"); 
require_once(WPSC_FILE_PATH."/transaction_result_functions.php"); 
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
include_once(WPSC_FILE_PATH.'/widgets/category_widget.php');
include_once(WPSC_FILE_PATH.'/image_processing.php');

include_once(WPSC_FILE_PATH."/wpsc-includes/install_and_update.functions.php");


if( is_admin() ) {
	include_once(WPSC_FILE_PATH."/wpsc-admin/admin.php");
}
?>