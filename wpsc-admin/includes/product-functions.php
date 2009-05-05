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
	$post_data['meta'] = isset($post_data['productmeta_values']) ? $post_data['productmeta_values'] : '';

	$post_data['quantity_limited'] = (int)(bool)$post_data['quantity_limited'];
	$post_data['special'] = (int)(bool)$post_data['special'];
	$post_data['notax'] = (int)(bool)$post_data['notax'];
	$post_data['donation'] = (int)(bool)$post_data['donation'];
	$post_data['no_shipping'] = (int)(bool)$post_data['no_shipping'];
	
	
	$post_data['files'] = $_FILES;

  //echo "<pre>".print_r($post_data, true)."</pre>";
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
		'name' => '',
		'description' => '',
		'additional_description' => '',
		'price' => null,
		'weight' => null,
		'weight_unit' => '',
		'pnp' => null,
		'international_pnp' => null,
		'file' => null,
		'image' => '',
		'quantity_limited' => '',
		'quantity' => null,
		'special' => null,
		'special_price' => null,
		'display_frontpage' => null,
		'notax' => null,
		'publish' => null,
		'active' => null,
		'donation' => null,
		'no_shipping' => null,
		'thumbnail_image' => null,
		'thumbnail_state' => null
  );
  
  foreach($product_columns as $column => $default) {
    if($post_data[$column]) {
			$update_values[$column] = stripslashes($post_data[$column]);
    } else if(($update != true) && ($default !== null)) {
			$update_values[$column] = stripslashes($default);
    }
  }
  if($update === true) {
		$where = array( 'id' => $product_id );
		if ( false === $wpdb->update( WPSC_TABLE_PRODUCT_LIST, $update_values, $where ) ) {
			if ( $wpsc_error ) {
				return new WP_Error('db_update_error', __('Could not update product in the database'), $wpdb->last_error);
			} else {
				return false;
			}
		}			
  } else {  
		if ( false === $wpdb->insert( WPSC_TABLE_PRODUCT_LIST, $update_values ) ) {
			if ( $wp_error ) {
				return new WP_Error('db_insert_error', __('Could not insert product into the database'), $wpdb->last_error);
			} else {
				return 0;
			}
		}
		$product_id = (int) $wpdb->insert_id;
  }
  
	// if we succeed, we can do further editing
	
	// update the categories
	wpsc_update_category_associations($product_id, $post_data['category']);
	
	// and the tags
	wpsc_update_product_tags($product_id, $post_data['product_tags']);
	
	// and the meta
	wpsc_update_product_meta($product_id, $post_data['meta']);
	
	// and the images
	wpsc_update_product_images($product_id, $post_data);
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
}

 /**
 * wpsc_update_product_tags function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_meta($product_id, $product_meta) {
    if($product_meta != null) {
      foreach((array)$product_meta as $key => $value) {
        if(get_product_meta($product_id, $key) != false) {
          update_product_meta($product_id, $key, $value);
				} else {
          add_product_meta($product_id, $key, $value);
				}
			}
		}
}


/**
* wpsc_update_product_tags function 
*
* @param integer product ID
* @param array the post data
*/
function wpsc_update_product_images($product_id, $post_data) {
  global $wpdb;

/* Handle new image uploads here */
	$image = wpsc_item_process_image($product_id, $post_data['files']['image']['tmp_name'], str_replace(" ", "_", $post_data['files']['image']['name']), $post_data['width'], $post_data['height'], $post_data['image_resize']);



  if(is_numeric($product_id)) {
		if(($post_data['image_resize'] == 1 || $post_data['image_resize'] == 2) && ($image == '')) {
      /*  resize the image if directed to do so and no new image is supplied  */
      $image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$product_id." LIMIT 1",ARRAY_A);
      
      // prevent images from being replaced by those from other products
      $check_multiple_use = $wpdb->get_var("SELECT COUNT(`image`) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `image`='".$image_data['image']."'");
      if($check_multiple_use > 1) {
        $new_filename = $image_data['id']."_".$image_data['image'];
        if(file_exists(WPSC_THUMBNAIL_DIR.$image_data['image']) && ($image_data['image'] != null)) {
          copy(WPSC_THUMBNAIL_DIR.$image_data['image'], WPSC_THUMBNAIL_DIR.$new_filename);
				}
        if(file_exists(WPSC_IMAGE_DIR.$image_data['image']) && ($image_data['image'] != null)) {
          copy(WPSC_IMAGE_DIR.$image_data['image'], WPSC_IMAGE_DIR.$new_filename);
				}
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$new_filename."' WHERE `id`='".$image_data['id']."' LIMIT 1");
        $image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$product_id." LIMIT 1",ARRAY_A);
			}
        
        
      if(file_exists(WPSC_THUMBNAIL_DIR.$image_data['image']) && ($image_data['image'] != '')) {
        $imagepath = WPSC_IMAGE_DIR . $image_data['image'];
        $image_output = WPSC_THUMBNAIL_DIR . $image_data['image'];
        switch($_POST['image_resize']) {
          case 1:
          $height = get_option('product_image_height');
          $width  = get_option('product_image_width');
          break;
  
          case 2:
          $height = $post_data['height'];
          $width  = $post_data['width'];
          break;
				}
				image_processing($imagepath, $image_output, $width, $height);
				update_product_meta($id, 'thumbnail_width', $width);
				update_product_meta($id, 'thumbnail_height', $height);
			}
    }
	}
}
?>