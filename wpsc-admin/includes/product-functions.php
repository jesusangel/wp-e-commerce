<?php
/**
 * WPSC Product modifying functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_get_max_upload_size(){
// Get PHP Max Upload Size
	if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');	
	else $upload_max = __('N/A', 'wpsc');	
	return $upload_max;
}

/**
* wpsc_admin_submit_product function 
*
* @return nothing
*/
function wpsc_admin_submit_product() {
	check_admin_referer('edit-product', 'wpsc-edit-product');
	
	$sendback = wp_get_referer();
	$post_data = wpsc_sanitise_product_forms();
	//$post_data['category'] = 1;  /// remove this
	if(isset($post_data['title']) && $post_data['title'] != '' && isset($post_data['category'])) {
		$product_id = wpsc_insert_product($post_data, true);
		if($product_id > 0) {
			$sendback = add_query_arg('product', $product_id);
		}
		
		$sendback = add_query_arg('message', 1, $sendback);
		//exit('<pre>'.print_r($sendback,true).'</pre>');
		wp_redirect($sendback);
	} else {
		$_SESSION['product_error_messages'] = array();	
		if($post_data['title'] == ''){
			$_SESSION['product_error_messages'][] = __('<strong>ERROR</strong>: Please enter a Product name.<br />');
		}
		if(!isset($post_data['category'])){
			$_SESSION['product_error_messages'][] = __('<strong>ERROR</strong>: Please enter a Product Category.<br />');
		}
		
		$_SESSION['wpsc_failed_product_post_data'] = $post_data;
		$sendback = add_query_arg('ErrMessage', 1);
		wp_redirect($sendback);
	}
	exit();
}
 
 
  /**
	* wpsc_sanitise_product_forms function 
	* 
	* @return array - Sanitised product details
*/
function wpsc_sanitise_product_forms($post_data = null) {
	if ( empty($post_data) ) {
		$post_data = &$_POST;
	}
	
	$product = get_post(absint($post_data['post_ID']));
	
	
	// 	$post_data['product_id'] = isset($post_data['product_id']) ? $post_data['product_id'] : '';
	$post_data['name'] = isset($post_data['post_title']) ? $post_data['post_title'] : '';
	$post_data['title'] = $post_data['name'];
	$post_data['description'] = isset($post_data['content']) ? $post_data['content'] : '';
	$post_data['additional_description'] = isset($post_data['additional_description']) ? $post_data['additional_description'] : '';

	//$post_data['publish'] = (int)(bool)$post_data['publish']; 
	if($product != null) {
		$post_data['post_status'] = $product->post_status;
	} else {
		$post_data['post_status'] = 'draft';
	
	}
	
	
	
	if(isset($post_data['save'])) {
		$post_data['post_status'] = $post_data['post_status'];
	} else if(isset($post_data['publish'])) {
		$post_data['post_status'] = 'publish';	
	} else if(isset($post_data['unpublish'])) {
		$post_data['post_status'] = 'draft';
	}



	$post_meta['meta'] = (array)$_POST['meta'];
		
	$post_data['meta']['_wpsc_price'] = (float)$post_data['meta']['_wpsc_price'];
	$post_data['meta']['_wpsc_special_price'] = (float)$post_data['meta']['_wpsc_special_price'];
	$post_data['meta']['_wpsc_sku'] = $post_data['meta']['_wpsc_sku'];
	$post_data['meta']['_wpsc_is_donation'] = (int)(bool)$post_data['meta']['_wpsc_is_donation'];
	$post_data['meta']['_wpsc_stock'] = (int)$post_data['meta']['_wpsc_stock'];
	
	if((bool)$post_data['meta']['_wpsc_limited_stock'] != true) {
	  $post_data['meta']['_wpsc_stock'] = false;
	}
	unset($post_data['meta']['_wpsc_limited_stock']);
	
	
	$post_data['meta']['_wpsc_product_metadata']['unpublish_when_none_left'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['unpublish_when_none_left'];
	$post_data['meta']['_wpsc_product_metadata']['quantity_limited'] = (int)(bool)$post_data['quantity_limited'];
	$post_data['meta']['_wpsc_product_metadata']['special'] = (int)(bool)$post_data['special'];
	/* $post_data['meta']['_wpsc_product_metadata']['notax'] = (int)(bool)$post_data['notax'];; */
	$post_data['meta']['_wpsc_product_metadata']['no_shipping'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['no_shipping'];
	
	// Product Weight
	$weight = wpsc_convert_weight($post_data['meta']['_wpsc_product_metadata']['weight'], $post_data['meta']['_wpsc_product_metadata']['display_weight_as'], "gram");
	$post_data['meta']['_wpsc_product_metadata']['weight'] = (float)$weight;
	$post_data['meta']['_wpsc_product_metadata']['display_weight_as'] = $post_data['meta']['_wpsc_product_metadata']['display_weight_as'];	
	
	
	// table rate price
	$post_data['meta']['_wpsc_product_metadata']['table_rate_price'] = $post_data['table_rate_price'];
	// if table_rate_price is unticked, wipe the table rate prices
	if($post_data['table_rate_price']['state'] != 1) {
		$post_data['meta']['_wpsc_product_metadata']['table_rate_price']['quantity'] = null;
		$post_data['meta']['_wpsc_product_metadata']['table_rate_price']['table_rate_price'] = null;
	}
	
	if($post_data['meta']['_wpsc_product_metadata']['custom_tax']['state'] == 1) {
		$custom_tax_value = (float)$post_data['meta']['_wpsc_product_metadata']['custom_tax']['value'];
	} else {
		$custom_tax_value = null;
	}
	$post_data['meta']['_wpsc_product_metadata']['custom_tax'] = $custom_tax_value;
	
	$post_data['meta']['_wpsc_product_metadata']['shipping']['local'] = (float)$post_data['meta']['_wpsc_product_metadata']['shipping']['local'];
	$post_data['meta']['_wpsc_product_metadata']['shipping']['international'] = (float)$post_data['meta']['_wpsc_product_metadata']['shipping']['international'];
	
	
	// Advanced Options
	$post_data['meta']['_wpsc_product_metadata']['engraved'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['engraved'];	
	$post_data['meta']['_wpsc_product_metadata']['can_have_uploaded_image'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['can_have_uploaded_image'];
	$post_data['meta']['_wpsc_product_metadata']['google_prohibited'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['google_prohibited'];
	$post_data['meta']['_wpsc_product_metadata']['external_link'] = (string)$post_data['meta']['_wpsc_product_metadata']['external_link'];
	
	$post_data['meta']['_wpsc_product_metadata']['enable_comments'] = $post_data['meta']['_wpsc_product_metadata']['enable_comments'];
	$post_data['meta']['_wpsc_product_metadata']['merchant_notes'] = $post_data['meta']['_wpsc_product_metadata']['merchant_notes'];
	





	/*
	if(is_numeric($post_data['special_price'])) {
		$post_data['special_price'] = (float)($post_data['price'] - $post_data['special_price']);
	} else {
		$post_data['special_price'] = 0;
	}
	*/
	
	/*
	// if special is unticked, wipe the special_price value
	if($post_data['special'] !== 1) {
	  $post_data['special_price'] = 0;
	}
	*/
	
	
	

	$post_data['files'] = $_FILES;

	//exit('<pre>'.print_r($post_data, true).'</pre>');
	return $post_data;
}
  
 /**
	* wpsc_insert_product function 
	*
	* @param unknown 
	* @return unknown
*/
 // exit('Image height'.get_option('product_image_height'));	
function wpsc_insert_product($post_data, $wpsc_error = false) {
	global $wpdb, $user_ID;
	$adding = false;
	$update = false;
	if((int)$post_data['post_ID'] > 0) {
		$product_id	= absint($post_data['post_ID']);
		$update = true;
	} else if((int)$post_data['product_id'] > 0) {
		$product_id	= absint($post_data['product_id']);
		$update = true;
	}
	
	
  
	//exit('<pre>'.print_r($product_id, true).'</pre>');
	
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
		'image' => '0',
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
		if(isset($post_data[$column]) || ($post_data[$column] !== null) ) {
			$update_values[$column] = stripslashes($post_data[$column]);
		} else if(($update != true) && ($default !== null)) {
			$update_values[$column] = stripslashes($default);
		}
	}


  
	$product_post_values = array(
		'ID' => $product_id,
		'post_author' => $user_ID,
		'post_content' => $post_data['description'],
		'post_excerpt' => $post_data['additional_description'],
		'post_title' => $post_data['name'],
		'post_status' => $post_data['post_status'],
		'post_type' => "wpsc-product",
		'post_name' => sanitize_title($post_data['name'])
	);

		
	//exit("<pre>".print_r(wp_update_post($product_post_values) , true)."</pre>");
	if($sku != '') {
		$product_post_array['guid'] = $sku;
	}



  
   if($update === true) {
		$where = array( 'id' => $product_id );
		//exit('<pre>'.print_r($product_post_values,true).'</pre>');
		$product_id = wp_update_post($product_post_values);
		if ($product_id == 0) {
			if ( $wpsc_error ) {
				return new WP_Error('db_update_error', __('Could not update product in the database'), $wpdb->last_error);
			} else {
				return false;
			}
		}			
  } else {

		$product_post_values += array(
			'post_date' => $product['date_added']
		);
  		 $product_id = wp_insert_post($product_post_values);
		if ($product_id == 0 ) {
			if ( $wp_error ) {
				return new WP_Error('db_insert_error', __('Could not insert product into the database'), $wpdb->last_error);
			} else {
				return 0;
			}
		}
		$adding = true;
		//$product_id = (int)$wpdb->insert_id;
		//exit($product_id.' <-- IS the corresponding ID YAW');
  }
  
	/* Add tidy url name */
	if($post_data['name'] != '') {
		$existing_name = get_product_meta($product_id, 'url_name');
		// strip slashes, trim whitespace, convert to lowercase
		$tidied_name = strtolower(trim(stripslashes($post_data['name'])));
		// convert " - " to "-", all other spaces to dashes, and remove all foward slashes.
		//$url_name = preg_replace(array("/(\s-\s)+/","/(\s)+/", "/(\/)+/"), array("-","-", ""), $tidied_name);
		$url_name = sanitize_title($tidied_name);
		
		// Select all similar names, using an escaped version of the URL name 
		$similar_names = (array)$wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` NOT IN('{$product_id}}') AND `meta_key` IN ('url_name') AND `meta_value` REGEXP '^(".$wpdb->escape(preg_quote($url_name))."){1}[[:digit:]]*$' ");

		// Check desired name is not taken
		if(array_search($url_name, $similar_names) !== false) {
		  // If it is, try to add a number to the end, if that is taken, try the next highest number...
			$i = 0;
			do {
				$i++;
			} while(array_search(($url_name.$i), $similar_names) !== false);
			// Concatenate the first number found that wasn't taken
			$url_name .= $i;
		}
	  // If our URL name is the same as the existing name, do othing more.
		if($existing_name != $url_name) {
			update_product_meta($product_id, 'url_name', $url_name);
		}
	}
  
	// if we succeed, we can do further editing
	
	// update the categories
	/* 	wpsc_update_category_associations($product_id, $post_data['category']); */
	//wp_set_post_categories($product_id, $post_data['category']);
	
	//echo "<pre>".print_r($post_data['category'], true)."</pre>";	
	wp_set_product_categories($product_id, $post_data['category']);
	
	//echo "<pre>".print_r($test, true)."</pre>";
	//exit();
	

	// and the tags
	wpsc_update_product_tags($product_id, $post_data['product_tags'], $post_data['wpsc_existing_tags']);
	
	// and the meta
	wpsc_update_product_meta($product_id, $post_data['meta']);
	
	// and the custom meta
	wpsc_update_custom_meta($product_id, $post_data);

	// and the images
	wpsc_update_product_images($product_id, $post_data);
	
	//and the alt currency
	foreach((array)$post_data['newCurrency'] as $key =>$value){
		wpsc_update_alt_product_currency($product_id, $value, $post_data['newCurrPrice'][$key]);
	}
	
	if($post_data['files']['file']['tmp_name'] != '') {
		wpsc_item_process_file($product_id, $post_data['files']['file']);
	} else {
	  wpsc_item_reassign_file($product_id, $post_data['select_product_file']);
	}
	
	//exit('<pre>'.print_r($post_data, true).'</pre>');
	if($post_data['files']['preview_file']['tmp_name'] != '') {
 		wpsc_item_add_preview_file($product_id, $post_data['files']['preview_file']);
	}
     
	$variations_processor = new nzshpcrt_variations;
	
	if(($adding === true) && ($_POST['variations'] != null)) {
		foreach((array)$_POST['variations'] as $variation_id => $state) {
			$variation_id = (int)$variation_id;
			if($state == 1) {
				$variation_values = $variations_processor->falsepost_variation_values($variation_id);
				$variations_processor->add_to_existing_product($product_id,$variation_values);
			}
		}
	}
	
	
	if($post_data['edit_variation_values'] != null) {
		$variations_processor->edit_product_values($product_id,$post_data['edit_variation_values']);
	}
	
	if($post_data['edit_add_variation_values'] != null) {
		$variations_processor->edit_add_product_values($product_id,$post_data['edit_add_variation_values']);
	}
		
	if($post_data['variation_priceandstock'] != null) {
		$variations_processor->update_variation_values($product_id, $post_data['variation_priceandstock']);
	}

	
	do_action('wpsc_edit_product', $product_id);
	wpsc_ping();
	return $product_id;
}

function wpsc_update_alt_product_currency($product_id, $newCurrency, $newPrice){
	global $wpdb;
	$sql = "SELECT `isocode` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`=".$newCurrency;
	$isocode = $wpdb->get_var($sql);
	//exit($sql);
	$newCurrency = 'currency['.$isocode.']';
	
	if(($newPrice != '') &&  ($newPrice > 0)){
		update_product_meta($product_id, $newCurrency, $newPrice, $prev_value = '');
	} else {
		delete_product_meta($product_id, $newCurrency);
	}
	
	//exit('<pre>'.print_r($newCurrency, true).'</pre>'.$newPrice);
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
  
  $categories_to_add = array_diff((array)$categories, (array)$associated_categories);
  $categories_to_delete = array_diff((array)$associated_categories, (array)$categories);
  $insert_sections = array();
  foreach($categories_to_delete as $key => $category_to_delete) {
		$categories_to_delete[$key] = absint($category_to_delete);
  }

	//exit('<pre>'.print_r($categories_to_delete, true).'</pre>');

  foreach($categories_to_add as $category_id) {
    $insert_sections[] = $wpdb->prepare("( %d, %d)", $product_id, $category_id);
  }
  if(count($insert_sections)) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` (`product_id`, `category_id`) VALUES ".implode(", ",$insert_sections)."");
  }
  
  foreach($categories_to_add as $category_id) {
		$check_existing = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN('$category_id') AND `order` IN('0') LIMIT 1;",ARRAY_A);
		if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN('$category_id') AND `product_id` IN('$product_id') LIMIT 1")) {
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = '0' WHERE `category_id` IN('$category_id') AND `product_id` IN('$product_id') LIMIT 1;");
		} else {				  
			$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_ORDER."` (`category_id`, `product_id`, `order`) VALUES ('$category_id', '$product_id', 0)");
		}
		if($check_existing != null) {
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = (`order` + 1) WHERE `category_id` IN('$category_id') AND `product_id` NOT IN('$product_id') AND `order` < '0'");
		}
  }
  if(count($categories_to_delete) > 0) {
    $wpdb->query("DELETE FROM`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` = {$product_id} AND `category_id` IN(".implode(",",$categories_to_delete).") LIMIT ".count($categories_to_delete)."");
  }
}
  
  /**
 * wpsc_update_product_tags function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_tags($product_id, $product_tags, $existing_tags) {
	if(isset($existing_tags)){
		$tags = explode(',',$existing_tags);
		if(is_array($tags)){
			foreach((array)$tags as $tag){
				$tt = wp_insert_term((string)$tag, 'product_tag');
			}
		}
	}
	wp_set_object_terms($product_id, $tags, 'product_tag');
	if(isset($product_tags) && $product_tags != 'Add new tag') {
		
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
 * wpsc_update_product_meta function
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_meta($product_id, $product_meta) {
    if($product_meta != null) {
		foreach((array)$product_meta as $key => $value) {
		    if(get_post_meta($product_id, $key) != false) {
		      update_post_meta($product_id, $key, $value);
			} else {
		      add_post_meta($product_id, $key, $value);
			}
		}
	}
}

/*
/* Code to support Publish/No Publish (1bigidea)
*/
/**
 * set status of publish conditions
 * @return 
 * @param string 	$product_id
 * @param bool			$status		Publish State 
 */
function wpsc_set_publish_status($product_id, $state) {
	global $wpdb;
	$status = (int) ( $state ) ? 1 : 0; // Cast the Publish flag
	$result = $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `publish` = '{$status}' WHERE `id` = '{$product_id}'");
}
/**
 * Toggle publish status and update product record
 * @return bool		Publish status
 * @param string	$product_id
 */
function wpsc_toggle_publish_status($product_id) {
	global $wpdb;
	$status = (int) ( wpsc_publish_status($product_id) ) ? 0 : 1; // Flip the Publish flag True <=> False
	$sql = "UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `publish` = '{$status}' WHERE `id` = '{$product_id}'";
	$result = $wpdb->query($sql);
	return $status;
}
/**
 * Returns publish status from product database
 * @return bool		publish status
 * @param string	$product_id
 */
function wpsc_publish_status($product_id) {
	global $wpdb;
	$status = (bool)$wpdb->get_var("SELECT `publish` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$product_id}'");
	return $status;
}
/**
 * Called from javascript within product page to toggle publish status - AJAX
 * @return bool	publish status
 */
function wpsc_ajax_toggle_publish() {
/**
 * @todo - Check Admin Referer
 * @todo - Check Permissions
 */
	$status = (wpsc_toggle_publish_status($_REQUEST['productid'])) ? ('true') : ('false');
	exit( $status );
}
//add_action('wp_ajax_wpsc_toggle_publish','wpsc_ajax_toggle_publish');
/*
/*  END - Publish /No Publish functions
*/

function wpsc_update_custom_meta($product_id, $post_data) {
  global $wpdb;
    if($post_data['new_custom_meta'] != null) {
      foreach((array)$post_data['new_custom_meta']['name'] as $key => $name) {
			$value = $post_data['new_custom_meta']['value'][(int)$key];
	        if(($name != '') && ($value != '')) {
				add_post_meta($product_id, $name, $value);
	        }
		}
	}
		
	if($post_data['custom_meta'] != null) {
		foreach((array)$post_data['custom_meta'] as $key => $values) {
			if(($values['name'] != '') && ($values['value'] != '')) {
        		update_post_meta($product_id, $name, $value);
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
  $uploaded_images = array();

  // This segment is for associating the images uploaded using swfuploader when adding a product
  foreach((array)$post_data['gallery_image_id'] as $added_image) {
		if($added_image > 0) {
			$uploaded_images[] = absint($added_image);
    }
  }
  if(count($uploaded_images) > 0) {
		$uploaded_image_data = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` IN (".implode(', ', $uploaded_images).") AND `product_id` = '0'");
		if(count($uploaded_image_data) > 0) {
			$first_image = null;
			foreach($uploaded_image_data as $uploaded_image_id) {
				if($first_image === null) {
					$first_image = absint($uploaded_image_id);
				}
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_IMAGES."` SET `product_id` = '$product_id' WHERE `id` = '{$uploaded_image_id}' LIMIT 1;");
			}
			
			$previous_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1");
			if($previous_image == 0) {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$first_image}' WHERE `id`='{$product_id}' LIMIT 1");
			}
			wpsc_resize_image_thumbnail($product_id, 1);
		}
	}

  

	/* Handle new image uploads here */
  if($post_data['files']['image']['tmp_name'] != '') {
		$image = wpsc_item_process_image($product_id, $post_data['files']['image']['tmp_name'], str_replace(" ", "_", $post_data['files']['image']['name']), $post_data['width'], $post_data['height'], $post_data['image_resize']);
		
		$image_action = absint($post_data['image_resize']);
		$image_width = $post_data['width'];
		$image_height = $post_data['height'];
	
	} else {
		$image_action = absint($post_data['gallery_resize']);
		$image_width = $post_data['gallery_width'];
		$image_height = $post_data['gallery_height'];
		
	}
	
//    exit( "<pre>".print_r($image_action, true)."</pre>");
	wpsc_resize_image_thumbnail($product_id, $image_action, $image_width, $image_height);
 	//exit( " <pre>".print_r($post_data, true)."</pre>");
	
	


}

 /**
 * wpsc_resize_image_thumbnail function 
 *
 * @param integer product ID
 * @param integer the action to perform on the image
 * @param integer the width of the thumbnail image
 * @param integer the height of the thumbnail image
 * @param array the custom image array from $_FILES
 */
function wpsc_resize_image_thumbnail($product_id, $image_action= 0, $width = 0, $height = 0, $custom_image = null) {
  global $wpdb;
	$image_id = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$product_id}' LIMIT 1");
	$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image_id}' LIMIT 1");
	
	// check if there is an image that is supposed to be there.
	if($image != '') {
		if(is_numeric($image)){			
		}
	  // check that is really there
	  if(file_exists(WPSC_IMAGE_DIR.$image)) {
			// if the width or height is less than 1, set the size to the default

			if((($width  < 1) || ($height < 1)) && ($image_action == 2)) {
				$image_action = 1;
			}
			switch($image_action) {
				case 0:
					if(!file_exists(WPSC_THUMBNAIL_DIR.$image)) {
						copy(WPSC_IMAGE_DIR.$image, WPSC_THUMBNAIL_DIR.$image);
					}
				break;
					
				
				case 1:
				  // if case 1, replace the provided size with the default size
					$height = get_option('product_image_height');
					$width  = get_option('product_image_width');				
				case 2:
				  // if case 2, use the provided size
					$image_input = WPSC_IMAGE_DIR . $image;
					$image_output = WPSC_THUMBNAIL_DIR . $image;
					
					if($width < 1) {
						$width = 96;
					}
					if($height < 1) {
						$height = 96;
					}
					
					image_processing($image_input, $image_output, $width, $height);
					update_product_meta($product_id, 'thumbnail_width', $width);
					update_product_meta($product_id, 'thumbnail_height', $height);
				break;
				
				case 3:
				  // replacing the thumbnail with a custom image is done here
				  $uploaded_image = null;
				    //exit($uploaded_image);
				   if(file_exists($_FILES['gallery_thumbnailImage']['tmp_name'])) {
						$uploaded_image =  $_FILES['gallery_thumbnailImage']['tmp_name'];
				   } else if(file_exists($_FILES['thumbnailImage']['tmp_name'])) {
						$uploaded_image =  $_FILES['thumbnailImage']['tmp_name'];
				   }
				  if($uploaded_image !== null) {
				  
						move_uploaded_file($uploaded_image, WPSC_THUMBNAIL_DIR.$image);
				    //exit($uploaded_image);
				  
				  }
				break;
			}
			
			if(!file_exists(WPSC_IMAGE_DIR.$image)) {
				$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` SET `thumbnail_state` = '$image_action' WHERE `id`='{$product_id}' LIMIT 1");
				$sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` (`product_id`, `image`, `width`, `height`) VALUES ('{$product_id}', '{$image}', '{$width}', '{$height}' )";
				$wpdb->query($sql);	
				$image_id = (int) $wpdb->insert_id;
			}
			
			$sql="UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `thumbnail_state` = '$image_action', `image` ='{$image_id}' WHERE `id`='{$product_id}' LIMIT 1";
			//exit($sql);
			$wpdb->query($sql);
		} else {
			//if it is not, we need to unset the associated image
			//$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '' WHERE `id`='{$product_id}' LIMIT 1");
			//$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` (`product_id`, `image`, `width`, `height`) VALUES ('{$product_id}', '{$image}', '{$width}', '{$height}' )");	
		}
	}

}




 /**
 * wpsc_upload_image_thumbnail function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_upload_image_thumbnail($product_id, $product_meta) {
		if(($_POST['image_resize'] == 3) && ($_FILES['thumbnailImage'] != null) && file_exists($_FILES['thumbnailImage']['tmp_name'])) {
			$imagefield='thumbnailImage';
	
			$image=image_processing($_FILES['thumbnailImage']['tmp_name'], WPSC_THUMBNAIL_DIR.$_FILES['thumbnailImage']['name'],null,null,$imagefield);
			$thumbnail_image = $image;
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `thumbnail_image` = '".$thumbnail_image."' WHERE `id` = '".$image_data['id']."'");
			$stat = stat( dirname( (WPSC_THUMBNAIL_DIR.$image_data['image']) ));
			$perms = $stat['mode'] & 0000775;
			@ chmod( (WPSC_THUMBNAIL_DIR.$image_data['image']), $perms );	
		}
}


 /**
 * wpsc_item_process_file function 
 *
 * @param integer product ID
 * @param array the file array from $_FILES 
 * @param array the preview file array from $_FILES
 */
function wpsc_item_process_file($product_id, $submitted_file, $preview_file = null) {
	global $wpdb;
	add_filter('upload_dir', 'wpsc_modify_upload_directory');
	$overrides = array('test_form'=>false);

	$time = current_time('mysql');
	if ( $post = get_post($product_id) ) {
		if ( substr( $post->post_date, 0, 4 ) > 0 )
			$time = $post->post_date;
	}

	//$name = basename($submitted_file['name']);
	$file = wp_handle_upload($submitted_file, $overrides, $time);

	if ( isset($file['error']) )
		return new WP_Error( 'upload_error', $file['error'] );

	$name_parts = pathinfo($file['file']);
	//$name = trim( substr( $name, 0, -(1 + strlen($name_parts['extension'])) ) );
	$name = $name_parts['basename'];
	//echo "<pre>".print_r($name_parts,true)."</pre>"; exit();

	$url = $file['url'];
	$type = $file['type'];
	$file = $file['file'];
	$title = $name;
	$content = '';

	// Construct the attachment array
	$attachment = array(
		'post_mime_type' => $type,
		'guid' => $url,
		'post_parent' => $product_id,
		'post_title' => $title,
		'post_content' => $content,
		'post_type' => "wpsc-product-file",
		'post_status' => 'inherit'		
	);

	// Save the data
	$id = wp_insert_post($attachment, $file, $product_id);
	remove_filter('upload_dir', 'wpsc_modify_upload_directory');
	//return $id;
	//exit($id);
}

function wpsc_modify_upload_directory($input) {
	//echo "<pre>".print_r($input,true)."</pre>";
	$previous_subdir = $input['subdir'];
	$download_subdir = str_replace($input['basedir'], '', WPSC_FILE_DIR);
	
	$input['path'] = str_replace($previous_subdir, $download_subdir, $input['path']);
	$input['url'] = str_replace($previous_subdir, $download_subdir, $input['url']);
	$input['subdir'] = str_replace($previous_subdir, $download_subdir, $input['subdir']);
	
	//echo "<pre>".print_r($input,true)."</pre>";
	return $input;
}
  
  
  
 /**
 * wpsc_item_reassign_file function 
 *
 * @param integer product ID
 * @param string the selected file name;
 */
function wpsc_item_reassign_file($product_id, $selected_files) {
	global $wpdb;
	$product_file_list = array();
	// initialise $idhash to null to prevent issues with undefined variables and error logs
	$idhash = null;
	
	$args = array(
		'post_type' => 'wpsc-product-file',
		'post_parent' => $product_id,
		'numberposts' => -1,
		'post_status' => 'any'
	);
	
	$attached_files = (array)get_posts($args);
	
	foreach($attached_files as $key => $attached_file) {
		$attached_files_by_file[$attached_file->post_title] = $attached_files[$key];
	}


	//echo "<pre>\n";
	//echo print_r($attached_files,true);
	//echo wp_insert_post($attachment);
	//echo "</pre>\n";
	
	
	/* if we are editing, grab the current file and ID hash */ 
	if(!$selected_files) {
		// unlikely that anyone will ever upload a file called .none., so its the value used to signify clearing the product association
		//$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '0' WHERE `id` = '$product_id' LIMIT 1");
		return null;
	}
	
	

	foreach($selected_files as $selected_file) {
		// if we already use this file, there is no point doing anything more.
		$file_is_attached = false;		
		$selected_file_path = WPSC_FILE_DIR.basename($selected_file);
		
		if(isset($attached_files_by_file[$selected_file])) {
			$file_is_attached = true;
		}
		
		if(is_file($selected_file_path)) {
			if($file_is_attached == false ) {
				$type = wpsc_get_mimetype($selected_file_path);
				$attachment = array(
					'post_mime_type' => $type,
					'post_parent' => $product_id,
					'post_title' => $selected_file,
					'post_content' => '',
					'post_type' => "wpsc-product-file",
					'post_status' => 'inherit'
				);
				wp_insert_post($attachment);
			} else {
				$product_post_values = array(
					'ID' => $attached_files_by_file[$selected_file]->ID,
					'post_status' => 'inherit'
				);
				wp_update_post($product_post_values);			
			}
		}
	}
	
	
	foreach($attached_files as $attached_file) {
		if(!in_array($attached_file->post_title, $selected_files)) {
			$product_post_values = array(
				'ID' => $attached_file->ID,
				'post_status' => 'draft'
			);
			wp_update_post($product_post_values);
		}
	}
	
	
	//
	//exit('<pre>'.print_r($attached_files, true).'</pre>');
	//update_product_meta($product_id, 'product_files', $product_file_list);
	return $fileid;
}



 /**
 * wpsc_item_add_preview_file function 
 *
 * @param integer product ID
 * @param array the preview file array from $_FILES
 */
function wpsc_item_add_preview_file($product_id, $preview_file) {
  global $wpdb;
  
	$current_file_id = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1");
	$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='{$current_file_id}' LIMIT 1",ARRAY_A);
	
	if(apply_filters( 'wpsc_filter_file', $preview_file['tmp_name'] )) {
	  //echo "test?";
		if(function_exists("make_mp3_preview"))	{
			if($mimetype == "audio/mpeg" && (!isset($preview_file['tmp_name']))) {
				// if we can generate a preview file, generate it (most can't due to sox being rare on servers and sox with MP3 support being even rarer), thus this needs to be enabled by editing code
				make_mp3_preview((WPSC_FILE_DIR.$idhash), (WPSC_PREVIEW_DIR.$idhash.".mp3"));
				$preview_filepath = (WPSC_PREVIEW_DIR.$idhash.".mp3");
			} else if(file_exists($preview_file['tmp_name'])) {    
				$preview_filename = basename($preview_file['name']);
				$preview_mimetype = wpsc_get_mimetype($preview_file['tmp_name']);
				copy($preview_file['tmp_name'], (WPSC_PREVIEW_DIR.$preview_filename));
				$preview_filepath = (WPSC_PREVIEW_DIR.$preview_filename);
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '{$file_data['id']}' LIMIT 1");
				//exit("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '{$file_data['id']}' LIMIT 1");
			}
			$stat = stat( dirname($preview_filepath));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $preview_filepath, $perms );	
		}
		//exit("<pre>".print_r($preview_file,true)."</pre>");
		return $fileid;
   } else {
 		return $selected_files;
   }  
}


function wpsc_send_to_google_base($product_data) {
	require_once('google_base_functions.php');
	if (strlen(get_option('wpsc_google_base_token')) > 0) {
	  $token = get_option('wpsc_google_base_token');
// 		if (isset($_SESSION['google_base_sessionToken'])) {
// 			$sessionToken = $_SESSION['google_base_sessionToken'];
// 		} else {
			$sessionToken = exchangeToken($token);
// 			$_SESSION['google_base_sessionToken'] = $sessionToken;
// 		}
		postItem($product_data['name'], $product_data['price'], $product_data['description'], $sessionToken);
	}
}


?>