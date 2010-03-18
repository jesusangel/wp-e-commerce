<?php
/**
 * Deprecated functions that will be removed at a later date.
 * @package Wp-e-commerce
 * Since 3.7.6rc2
 *
 */
 if(!wpsc_check_theme_versions()){
	include_once(WPSC_FILE_PATH.'/languages/EN_en.php');
 }



/**
 * Filter: wpsc-purchlogitem-links-start
 *
 * This filter has been deprecated and replaced with one that follows the
 * correct naming conventions with underscores.
 *
 * Since 3.7.6rc2
 */
function wpsc_purchlogitem_links_start_deprecated() {
	
	do_action( 'wpsc-purchlogitem-links-start' );
	
}
add_action( 'wpsc_purchlogitem_links_start', 'wpsc_purchlogitem_links_start_deprecated' );



?>