<?php
/**
 * WP eCommerce database updating functions
 *
 * @package wp-e-commerce
 * @since 3.8
 */


/**
 * wpsc_convert_category_groups function.
 * 
 * @access public
 * @return void
 */
function wpsc_convert_category_groups() {
	global $wpdb, $wp_rewrite, $user_ID;
	$categorisation_groups = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')");
	
	foreach((array)$categorisation_groups as $cat_group) {
		$category_id = wpsc_get_meta($cat_group->id, 'category_group_id', 'wpsc_category_group');
		
		if(!is_numeric($category_id) || ( $category_id < 1)) {
			$new_category = wp_insert_term( $cat_group->name, 'wpsc_product_category', array('description' => $cat_group->description));
			$category_id = $new_category['term_id'];
		}
		if(is_numeric($category_id)) {
			
			wpsc_update_meta($cat_group->id, 'category_group_id', $category_id, 'wpsc_category_group');
			wpsc_update_categorymeta($category_id, 'category_group_id', $cat_group->id);
			
			wpsc_update_categorymeta($category_id, 'image', '');
			wpsc_update_categorymeta($category_id, 'uses_billing_address', 0);
		}	
		wpsc_convert_categories($category_id, $cat_group->id);
	}
	
	
	//$wp_rewrite->flush_rules();
}

/**
 * wpsc_convert_categories function.
 * 
 * @access public
 * @param int $parent_category. (default: 0)
 * @return void
 */
function wpsc_convert_categories($new_parent_category, $group_id, $old_parent_category = 0) {
	global $wpdb, $wp_rewrite, $user_ID;
	
	if($old_parent_category > 0) {
		$categorisation = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN ('1') AND `group_id` IN ('{$group_id}') AND `category_parent` IN ('{$old_parent_category}')");
	} else {
		$categorisation = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN ('1') AND `group_id` IN ('{$group_id}') AND `category_parent` IN (0)");
	}
	
	
	
	
	if($categorisation > 0) {
		//echo "<pre>".print_r($categorisation, true)."</pre>";
		//echo "<div style='margin-left: 6px;'>";
		foreach((array)$categorisation as $category) {
			$category_id = wpsc_get_meta($category->id, 'category_id', 'wpsc_old_category');
			//echo "$category_id";		
			if(!is_numeric($category_id) || ( $category_id < 1)) {
				$new_category = wp_insert_term( $category->name, 'wpsc_product_category', array('description' => $category->description, 'parent' => $new_parent_category));
				$category_id = $new_category['term_id'];
			}
			
			
			
			if(is_numeric($category_id)) {
				
				wpsc_update_meta($category->id, 'category_id', $category_id, 'wpsc_old_category');
				wpsc_update_categorymeta($category_id, 'category_id', $category->id);
				
				wpsc_update_categorymeta($category_id, 'image', $category->image);
				wpsc_update_categorymeta($category_id, 'display_type', $category->display_type);
				
				wpsc_update_categorymeta($category_id, 'image_height', $category->image_height);	
			    wpsc_update_categorymeta($category_id, 'image_width', $category->image_width);
			    
				$use_additonal_form_set = wpsc_get_categorymeta($category->id, 'use_additonal_form_set');
	      		if($use_additonal_form_set != '') {
					wpsc_update_categorymeta($category_id, 'use_additonal_form_set', $use_additonal_form_set);
				} else {
					wpsc_delete_categorymeta($category_id, 'use_additonal_form_set');
				}
	
	
				wpsc_update_categorymeta($category_id, 'uses_billing_address', (bool)(int)wpsc_get_categorymeta($category->id, 'uses_billing_address'));

	
			}
			if($category_id > 0) {
				wpsc_convert_categories($category_id, $group_id, $category->id);
			}
				
		}	
		//echo "</div>";
	}
	
	//$wp_rewrite->flush_rules();
}




function wpsc_convert_variation_sets() {
	global $wpdb, $wp_rewrite, $user_ID;
	$variation_sets = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."`");
	
	foreach((array)$variation_sets as $variation_set) {
		$variation_set_id = wpsc_get_meta($variation_set->id, 'variation_set_id', 'wpsc_variation_set');
		
		if(!is_numeric($variation_set_id) || ( $variation_set_id < 1)) {
			$new_variation_set = wp_insert_term( $variation_set->name, 'wpsc-variation',array('parent' => 0));
			$variation_set_id = $new_variation_set['term_id'];
		}
		
		if(is_numeric($variation_set_id)) {
			wpsc_update_meta($variation_set->id, 'variation_set_id', $variation_set_id, 'wpsc_variation_set');
			
			
			$variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id` IN ({$variation_set->id})");
			foreach((array)$variations as $variation) {
				$variation_id = wpsc_get_meta($variation->id, 'variation_id', 'wpsc_variation');
				
				if(!is_numeric($variation_id) || ( $variation_id < 1)) {
					$new_variation = wp_insert_term( $variation->name, 'wpsc-variation',array('parent' => $variation_set_id));
					$variation_id = $new_variation['term_id'];
				}				
				if(is_numeric($variation_id)) {
					wpsc_update_meta($variation->id, 'variation_id', $variation_id, 'wpsc_variation');
					
					
				}
			}			
		}	
	}
	//$wp_rewrite->flush_rules();
}





/**
 * wpsc_convert_products_to_posts function.
 * 
 * @access public
 * @return void
 */
function wpsc_convert_products_to_posts() {
  global $wpdb, $wp_rewrite, $user_ID;
  // Select all products
  // print_r($wpdb);
	$product_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN ('1')", ARRAY_A);
	foreach((array)$product_data as $product) {
		$post_id = (int)$wpdb->get_var($wpdb->prepare( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE meta_key = %s AND `meta_value` = %d LIMIT 1", '_wpsc_original_id', $product['id'] ));
		
		// print_r(array($post_id));
		$sku = get_product_meta($product['id'], 'sku', true);
		$weight = wpsc_convert_weights($product['weight'], $product['weight_unit']);
		if($post_id == 0) {
			$post_status = "publish";
			if($product['publish'] != 1) {
				$post_status = "draft";
			
			}
			
			$product_post_values = array(
				'post_author' => $user_ID,
				'post_date' => $product['date_added'],
				'post_content' => $product['description'],
				'post_excerpt' => $post_data['additional_description'],
				'post_title' => $product['name'],
				'post_status' => $post_status,
				'post_type' => "wpsc-product",
				'post_name' => sanitize_title($product['name'])
			);
			$post_id = wp_insert_post($product_post_values);
		}
		
		
		$product_meta = $wpdb->get_results("
			SELECT $post_id AS `post_id`,
				IF( ( `custom` != 1	),
					CONCAT( '_wpsc_', `meta_key` ) ,
				`meta_key`
				) AS `meta_key`,
				`meta_value`
			FROM `".WPSC_TABLE_PRODUCTMETA."`
			WHERE `product_id` IN ( '{$product['id']}' )
			AND `meta_value` NOT IN ( '' )
			", ARRAY_A);

		//echo print_r($product_meta,true);
		
		$post_data = array();
		$post_data['_wpsc_original_id'] = (int)$product['id'];
		$post_data['_wpsc_price'] = (float)$product['price'];
		$post_data['_wpsc_special_price'] = (float)$product['special_price'];
		$post_data['_wpsc_stock'] = (float)$product['quantity'];
		$post_data['_wpsc_is_donation'] = $product['donation'];
		$post_data['_wpsc_sku'] = $sku;
		if((bool)$product['quantity_limited'] != true) {
		  $post_data['_wpsc_stock'] = false;
		}
		unset($post_data['_wpsc_limited_stock']);
		
		
		
		
		$post_data['_wpsc_product_metadata']['is_stock_limited'] = (int)(bool)$product['quantity_limited'];
		
		// Product Weight
		$post_data['_wpsc_product_metadata']['weight'] = (float)($weight * 453.59237); // convert all weights to grams
		$post_data['_wpsc_product_metadata']['display_weight_as'] = $product['weight_unit'];
		
		$post_data['_wpsc_product_metadata']['has_no_shipping'] = (int)(bool)$product['no_shipping'];
		$post_data['_wpsc_product_metadata']['per_item_shipping'] = array('local' => $product['pnp'], 'international' => $product['international_pnp']);
		
		
		$post_data['_wpsc_product_metadata']['quantity_limited'] = (int)(bool)$product['quantity_limited'];
		$post_data['_wpsc_product_metadata']['special'] = (int)(bool)$product['special'];
		
		
		
		$post_data['_wpsc_product_metadata']['unpublish_when_none_left'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['unpublish_when_none_left'];
		/* $post_data['meta']['_wpsc_product_metadata']['notax'] = (int)(bool)$post_data['notax'];; */
		$post_data['_wpsc_product_metadata']['no_shipping'] = (int)(bool)$product['no_shipping'];
				
				
				
		/*		
		// table rate price
		$post_data['_wpsc_product_metadata']['table_rate_price'] = $post_data['table_rate_price'];
		// if table_rate_price is unticked, wipe the table rate prices
		if($post_data['table_rate_price']['state'] != 1) {
			$post_data['_wpsc_product_metadata']['table_rate_price']['quantity'] = null;
			$post_data['_wpsc_product_metadata']['table_rate_price']['table_rate_price'] = null;
		}
		
		if($post_data['_wpsc_product_metadata']['custom_tax']['state'] == 1) {
			$custom_tax_value = (float)$post_data['_wpsc_product_metadata']['custom_tax']['value'];
		} else {
			$custom_tax_value = null;
		}
		$post_data['_wpsc_product_metadata']['custom_tax'] = $custom_tax_value;
		
		$post_data['_wpsc_product_metadata']['shipping']['local'] = (float)$post_data['meta']['_wpsc_product_metadata']['shipping']['local'];
		$post_data['_wpsc_product_metadata']['shipping']['international'] = (float)$post_data['meta']['_wpsc_product_metadata']['shipping']['international'];
		
		
		// Advanced Options
		$post_data['_wpsc_product_metadata']['engraved'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['engraved'];	
		$post_data['_wpsc_product_metadata']['can_have_uploaded_image'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['can_have_uploaded_image'];
		$post_data['_wpsc_product_metadata']['google_prohibited'] = (int)(bool)$post_data['meta']['_wpsc_product_metadata']['google_prohibited'];
		$post_data['_wpsc_product_metadata']['external_link'] = (string)$post_data['meta']['_wpsc_product_metadata']['external_link'];
		
		$post_data['_wpsc_product_metadata']['enable_comments'] = $post_data['meta']['_wpsc_product_metadata']['enable_comments'];
		$post_data['_wpsc_product_metadata']['merchant_notes'] = $post_data['meta']['_wpsc_product_metadata']['merchant_notes'];
		
		
		/*
		*/
		
		foreach($post_data as $meta_key => $meta_value) {
			// prefix all meta keys with _wpsc_
			$meta_key = '_wpsc_'.$meta_key;
			update_post_meta($post_id, $meta_key, $meta_value);
		}

		// get the wordpress upload directory data
		$wp_upload_dir_data = wp_upload_dir();
		$wp_upload_basedir = $wp_upload_dir_data['basedir'];

		
			//print_r($wpdb);
		echo "Post ID:".$post_id."\n";
		
		$product_data = get_post($post_id);
		$image_data = $wpdb->get_results("SELECT *  FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` IN ('{$product['id']}') ORDER BY `image_order` ASC", ARRAY_A);
		//echo "SELECT *  FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` IN ('{$product['id']}') ORDER BY `image_order` ASC \n";
		foreach((array)$image_data as $image_row) {
			// Get the image path info
			$image_pathinfo = pathinfo($image_row['image']);
			
			// use the path info to clip off the file extension
			$image_name = basename($image_pathinfo['basename'], ".{$image_pathinfo['extension']}");
			
			// construct the full image path
			$full_image_path = WPSC_IMAGE_DIR.$image_row['image'];
			$attached_file_path = str_replace($wp_upload_basedir."/", '', $full_image_path);


			// construct the full image url
			$image_url = WPSC_IMAGE_URL.$image_row['image'];
			
			$attachment_id = (int)$wpdb->get_var("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_title` IN('$image_name') AND `post_parent` IN('$post_id') LIMIT 1");
			echo "Image ID:".$attachment_id."\n";
			// get the image MIME type
			$mime_type_data = wpsc_get_mimetype($full_image_path, true);
			if((int)$attachment_id == 0 ) {
				// construct the image data array
				$image_post_values = array(
					'post_author' => $user_ID,
					'post_parent' => $post_id,
					'post_date' => $product_data->post_date,
					'post_content' => $image_name,
					'post_title' => $image_name,
					'post_status' => "inherit",
					'post_type' => "attachment",
					'post_name' => sanitize_title($image_name),
					'post_mime_type' => $mime_type_data['mime_type'],
					'menu_order' => absint($image_row['image_order']),
					'guid' => $image_url
				);
				$attachment_id = wp_insert_post($image_post_values);
			}

			$image_size_data = getimagesize($full_image_path);
			$image_metadata = array(
				'width' => $image_size_data[0],
				'height' => $image_size_data[1],
				'file' => $attached_file_path
			);
			
		
			update_post_meta( $attachment_id, '_wp_attached_file', $attached_file_path );
			update_post_meta( $attachment_id, '_wp_attachment_metadata', $image_metadata);
			//print_r($attached_file_path);
			//print_r($image_post_values);
		}

		// yay, stars!
		//echo "\n";
		echo "<span style='font-size: 12pt;'>";
		echo "————————————————————————————————————————————————————————————————————————————\n";
		//    
		echo "</span>";
	}
}

?>