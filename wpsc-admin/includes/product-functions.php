<?php
/**
 * WPSC Product modifying functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 /**
	* wpsc_admin_submit_product method 
	*
	* @param unknown 
	* @return unknown
*/
function wpsc_admin_submit_product() {
  echo "<pre>".print_r(wpsc_sanitise_product_forms(),true)."</pre>";
	check_admin_referer('edit-product');
	$sendback = wp_get_referer();
	exit($sendback);
}
 
 
  /**
	* wpsc_insert_product method 
	* 
	* @return unknown
*/
function wpsc_sanitise_product_forms($post_data = null) {
	if ( empty($post_data) ) {
		$post_data = &$_POST;
	}
// 	$post_data['product_id'] = isset($post_data['product_id']) ? $post_data['product_id'] : '';
	$post_data['name'] = isset($post_data['title']) ? $post_data['title'] : '';
	$post_data['description'] = isset($post_data['content']) ? $post_data['content'] : '';

  return $post_data;
}
  
 /**
	* wpsc_insert_product method 
	*
	* @param unknown 
	* @return unknown
*/
function wpsc_insert_product() {

}
/**
	* wpsc_update_product method 
	*
	* @param unknown 
	* @return unknown
*/
function wpsc_update_product() {

}
?>