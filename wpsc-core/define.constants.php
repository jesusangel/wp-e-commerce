<?php
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


// the WPSC meta prefix, used for the product meta functions.
define('WPSC_META_PREFIX', "_wpsc_");



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
//define('WPSC_TABLE_PURCHASE_STATUSES', "{$wp_table_prefix}wpsc_purchase_statuses");
define('WPSC_TABLE_PRODUCTMETA', "{$wp_table_prefix}wpsc_productmeta");
define('WPSC_TABLE_VARIATION_ASSOC', "{$wp_table_prefix}wpsc_variation_assoc");
define('WPSC_TABLE_VARIATION_PROPERTIES', "{$wp_table_prefix}wpsc_variation_properties");
define('WPSC_TABLE_VARIATION_VALUES', "{$wp_table_prefix}wpsc_variation_values");
define('WPSC_TABLE_VARIATION_VALUES_ASSOC', "{$wp_table_prefix}wpsc_variation_values_assoc");
define('WPSC_TABLE_VARIATION_COMBINATIONS', "{$wp_table_prefix}wpsc_variation_combinations");
define('WPSC_TABLE_REGION_TAX', "{$wp_table_prefix}wpsc_region_tax");
define('WPSC_TABLE_CATEGORY_TM', "{$wp_table_prefix}wpsc_category_tm");
define('WP_TERM_RELATIONSHIPS', "{$wp_table_prefix}term_relationships");
define('WP_POSTS', "{$wp_table_prefix}posts");
define('WP_POSTMETA', "{$wp_table_prefix}postmeta");

$wp_upload_dir_data = wp_upload_dir();
$upload_path = '';
$upload_url = '';
$error_msg = '';

if (isset($wp_upload_dir_data['error'])) {
	$error_msg = $wp_upload_dir_data['error'];
}

if (isset($wp_upload_dir_data['basedir'])) {
	$upload_path = $wp_upload_dir_data['basedir'];
}

if (isset($wp_upload_dir_data['baseurl'])) {
	$upload_url = $wp_upload_dir_data['baseurl'];	
}

if(is_ssl()) {
	 $upload_url = str_replace("http://", "https://", $upload_url);
}

$active_wp_theme = get_stylesheet_directory();
$active_wp_theme = $active_wp_theme.'/wpsc/';

$wpsc_upload_dir = "{$upload_path}/wpsc/";
$wpsc_file_dir = "{$wpsc_upload_dir}downloadables/";
$wpsc_preview_dir = "{$wpsc_upload_dir}previews/";
$wpsc_image_dir = "{$wpsc_upload_dir}product_images/";
$wpsc_thumbnail_dir = "{$wpsc_upload_dir}product_images/thumbnails/";
$wpsc_category_dir = "{$wpsc_upload_dir}category_images/";
$wpsc_user_uploads_dir = "{$wpsc_upload_dir}user_uploads/";
$wpsc_cache_dir = "{$wpsc_upload_dir}cache/";
$wpsc_upgrades_dir = "{$wpsc_upload_dir}upgrades/";
//$wpsc_themes_dir = "{$wpsc_upload_dir}themes/";
$wpsc_themes_dir = $active_wp_theme;

define('WPSC_UPLOAD_ERR', $error_msg);
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




?>
