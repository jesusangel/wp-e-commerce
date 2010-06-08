<?php
/**
 * Deprecated functions that will be removed at a later date.
 * @package Wp-e-commerce
 * Since 3.7.6rc2
 *
 */

//// This language file is no longer used, but it is still included for
//// users that have old (non gettext) WPEC themes
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