<?php
/**
 * WPSC Product modifying functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 /**
	* wpsc_admin_submit_product function 
	*
	* @return nothing
*/
function wpsc_admin_submit_product() {
  //echo "<pre>".print_r(wpsc_sanitise_product_forms(),true)."</pre>";
	check_admin_referer('edit-product');
  $post_data = wpsc_sanitise_product_forms();
  wpsc_insert_product($post_data, true);
  
  
	$sendback = add_query_arg('message', 1);
	wp_redirect($sendback);
	exit();
}
 
 
  /**
	* wpsc_insert_product function 
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

	$post_data['quantity_limited'] = (int)(bool)$post_data['quantity_limited'];
	$post_data['special'] = (int)(bool)$post_data['special'];
	$post_data['notax'] = (int)(bool)$post_data['notax'];
	$post_data['donation'] = (int)(bool)$post_data['donation'];
	$post_data['no_shipping'] = (int)(bool)$post_data['no_shipping'];
	

  return $post_data;
}
  
 /**
	* wpsc_insert_product function 
	*
	* @param unknown 
	* @return unknown
*/
function wpsc_insert_product($post_data, $wpsc_error = false) {
  global $wpdb;
  //echo "<pre>".print_r(wpsc_sanitise_product_forms(),true)."</pre>";
  $update = false;
  if((int)$post_data['product_id'] > 0) {
	  $product_id	= absint($post_data['product_id']);
    $update = true;
  }
  
  $product_columns = array(
		'name',
		'description',
		'additional_description',
		'price',
 		'weight',
 		'weight_unit',
 		'pnp',
 		'international_pnp',
// 		'file',
// 		'image',
		'quantity_limited',
		'quantity',
		'special',
		'special_price',
// 		'display_frontpage',
 		'notax',
// 		'publish',
// 		'active',
 		'donation',
 		'no_shipping',
// 		'thumbnail_image',
// 		'thumbnail_state'
  );
  
  foreach($product_columns as $column) {
    if($post_data[$column]) {
			$update_values[$column] = stripslashes($post_data[$column]);
    }
  }
  if($update === true) {
		$where = array( 'id' => $product_id );
		if ( false === $wpdb->update( WPSC_TABLE_PRODUCT_LIST, $update_values, $where ) ) {
			if ( $wpsc_error ) {
				return new WP_Error('db_update_error', __('Could not update post in the database'), $wpdb->last_error);
			} else {
				return false;
			}
		}
	// if we succeed, we can do further editing
	
	// update the categories
	wpsc_update_category_associations($product_id, $post_data['category']);
	// and the tags
	wpsc_update_product_tags($product_id, $post_data['product_tags']);
				
				
  }
}


/**
 * wpsc_update_categories function 
 *
 * @param integer product ID
 * @param array submitted categories
 */
function wpsc_update_category_associations($product_id, $categories = array()) {
  global $wpdb;
  
  $associated_categories = $wpdb->get_col($wpdb->prepare("SELECT `category_id` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` IN('%s')", $product_id));
  
  $categories_to_add = array_diff($categories, $associated_categories);
  $categories_to_delete = array_diff($associated_categories, $categories);
  $insert_sections = array();
  foreach($categories_to_add as $category_id) {
    $insert_sections[] = $wpdb->prepare("( %d, %d)", $product_id, $category_id);
  }
  if(count($insert_sections)) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` (`product_id`, `category_id`) VALUES ".implode(", ",$insert_sections)."");
  }
  
  if(count($categories_to_delete) > 0) {
    $wpdb->query($wpdb->prepare("DELETE FROM`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` = %d AND `category_id` IN(%s)",$product_id,  implode($categories_to_delete)));
  
  }
}
  
  /**
 * wpsc_update_product_tags function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_tags($product_id, $product_tags) {

	if(isset($product_tags)) {
		$tags = explode(',',$product_tags);
		product_tag_init();
		if(is_array($tags)) {
			foreach((array)$tags as $tag){
				$tt = wp_insert_term((string)$tag, 'product_tag');
			}
		}
		wp_set_object_terms($product_id, $tags, 'product_tag');
	}
  //echo("<pre>".print_r($categories_to_add,true)."</pre>");
  //exit("<pre>".print_r($categories_to_delete,true)."</pre>");
}
?>