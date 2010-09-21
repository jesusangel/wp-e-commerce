<?php

// Get the wordpress version number
$version_processing = str_replace( array( '_', '-', '+' ), '.', strtolower( $wp_version ) );
$version_processing = str_replace( array( 'alpha', 'beta', 'gamma' ), array( 'a', 'b', 'g' ), $version_processing );
$version_processing = preg_split( "/([a-z]+)/i", $version_processing, -1, PREG_SPLIT_DELIM_CAPTURE );

array_walk( $version_processing, create_function( '&$v', '$v = trim($v,". ");' ) );

if ( !empty( $wpdb->prefix ) )
	$wp_table_prefix = $wpdb->prefix;
else if ( !empty( $table_prefix ) )
	$wp_table_prefix = $table_prefix;

$wp_upload_dir_data = wp_upload_dir();
$upload_path = '';
$upload_url = '';
$error_msg = '';

if ( isset( $wp_upload_dir_data['error'] ) )
	$error_msg = $wp_upload_dir_data['error'];

if ( isset( $wp_upload_dir_data['basedir'] ) )
	$upload_path = $wp_upload_dir_data['basedir'];

if ( isset( $wp_upload_dir_data['baseurl'] ) )
	$upload_url = $wp_upload_dir_data['baseurl'];

if ( is_ssl ( ) )
	$upload_url = str_replace( "http://", "https://", $upload_url );


$active_wp_theme       = get_stylesheet_directory();
$active_wp_theme       = $active_wp_theme . '/wpsc/';

$wpsc_upload_dir       = "{$upload_path}/wpsc/";
$wpsc_file_dir         = "{$wpsc_upload_dir}downloadables/";
$wpsc_preview_dir      = "{$wpsc_upload_dir}previews/";
$wpsc_image_dir        = "{$wpsc_upload_dir}product_images/";
$wpsc_thumbnail_dir    = "{$wpsc_upload_dir}product_images/thumbnails/";
$wpsc_category_dir     = "{$wpsc_upload_dir}category_images/";
$wpsc_user_uploads_dir = "{$wpsc_upload_dir}user_uploads/";
$wpsc_cache_dir        = "{$wpsc_upload_dir}cache/";
$wpsc_theme_backup_dir = "{$wpsc_upload_dir}theme_backup/";
$wpsc_upgrades_dir     = "{$wpsc_upload_dir}upgrades/";
$old_wpsc_themes_dir   = "{$wpsc_upload_dir}themes/";
$wpsc_themes_dir       = $active_wp_theme;

/**
 * files that are uploaded as part of digital products are not directly downloaded, therefore there is no need for a URL constant for them
 */
$wpsc_upload_url       = "{$upload_url}/wpsc/";
$wpsc_preview_url      = "{$wpsc_upload_url}previews/";
$wpsc_image_url        = "{$wpsc_upload_url}product_images/";
$wpsc_thumbnail_url    = "{$wpsc_upload_url}product_images/thumbnails/";
$wpsc_category_url     = "{$wpsc_upload_url}category_images/";
$wpsc_user_uploads_url = "{$wpsc_upload_url}user_uploads/";
$wpsc_cache_url        = "{$wpsc_upload_url}cache/";
$wpsc_upgrades_url     = "{$wpsc_upload_url}upgrades/";
$old_wpsc_themes_url   = "{$wpsc_upload_url}themes/";
$wpsc_themes_url       = get_stylesheet_directory_uri();

// Currency
$wpsc_currency_data = array();
$wpsc_title_data    = array();

/**
 * Check to see if the session exists, if not, start it
 */
function wpsc_start_session() {
	if ( !isset( $_SESSION ) )
		$_SESSION = null;

	if ( ( !is_array( $_SESSION ) ) xor ( !isset( $_SESSION['nzshpcrt_cart'] ) ) xor ( !$_SESSION ) )
		session_start();
}

?>
