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
  
	$product_data = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_LIST."`. * , `".WPSC_TABLE_PRODUCT_ORDER."`.order FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` ON `".WPSC_TABLE_PRODUCT_LIST."`.id = `".WPSC_TABLE_PRODUCT_ORDER."`.product_id WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`active` IN ( '1' )
GROUP BY wp_wpsc_product_list.id", ARRAY_A);
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
				'post_name' => sanitize_title($product['name']),
				'menu_order' => $product['order']
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
			update_post_meta($post_id, $meta_key, $meta_value);
		}

		// get the wordpress upload directory data
		$wp_upload_dir_data = wp_upload_dir();
		$wp_upload_basedir = $wp_upload_dir_data['basedir'];

		
		//print_r($wpdb);
		//echo "Post ID:".$post_id."\n";
		
		$category_ids = array();
		$category_data = $wpdb->get_col("SELECT `category_id` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` IN ('{$product['id']}')");
		foreach($category_data as $old_category_id) {
			$category_ids[] = wpsc_get_meta($old_category_id, 'category_id', 'wpsc_old_category');
		
		}
		wp_set_product_categories($post_id, $category_ids);
		
		
		
		$product_data = get_post($post_id);
		$image_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` IN ('{$product['id']}') ORDER BY `image_order` ASC", ARRAY_A);
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
			// echo "Image ID:".$attachment_id."\n";
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
		//echo "<span style='font-size: 12pt;'>";
		//echo "————————————————————————————————\n";
		//    
		//echo "</span>";
	}
	
	//Just throwing the payment gateway update in here because it doesn't really warrant it's own function :)
	
	$custom_gateways = get_option('custom_gateway_options');
	array_walk($custom_gateways, "wpec_update_gateway");
	
}

function wpec_update_gateway(&$value,$key) {
		if ( $value == "testmode" ) {
			$value = "wpsc_merchant_testmode";
		}	
}
function wpsc_convert_variation_combinations() {
	global $wpdb, $user_ID;

	// get the posts
	// I use a direct SQL query here because the get_posts function sometimes does not function for a reason that is not clear.
	$posts = $wpdb->get_results("SELECT * FROM `{$wpdb->posts}` WHERE `post_type` IN('wpsc-product')");
	
	
	$posts = get_posts( array(
	'post_type' => 'wpsc-product',
	'post_status' => 'all',
	'numberposts' => -1
	) );

    //print_r($posts);
    
    //return false;
	foreach((array)$posts as $post) {
	
		$base_product_terms = array();
		//create a post template
		$child_product_template = array(
			'post_author' => $user_ID,
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_title' => $post->post_title,
			'post_status' => 'inherit',
			'post_type' => "wpsc-product",
			'post_name' => sanitize_title($post->post_title),
			'post_parent' => $post->ID
		);
		
		
		
		// select the original product ID
		$original_id = get_post_meta($post->ID, '_wpsc_original_id', true);
		$parent_stock = get_post_meta($post->ID, '_wpsc_stock', true);
		
		// select the variation set associations
		$variation_set_associations = $wpdb->get_col("SELECT `variation_id` FROM ".WPSC_TABLE_VARIATION_ASSOC." WHERE `associated_id` = '{$original_id}'");
		//print_r($variation_set_associations);
		// select the variation associations if the count of variation sets is greater than zero
		if(($original_id > 0) && (count($variation_set_associations) > 0)) {
			$variation_associations = $wpdb->get_col("SELECT `value_id` FROM ".WPSC_TABLE_VARIATION_VALUES_ASSOC." WHERE `product_id` = '{$original_id}' AND `variation_id` IN(".implode(", ", $variation_set_associations).") AND `visible` IN ('1')");
		} else {
			// otherwise, we have no active variations, skip to the next product
			continue;
		}
		//print_r($variation_set_associations);
		
		foreach($variation_set_associations as $variation_set_id) {
			$base_product_terms[] = wpsc_get_meta($variation_set_id, 'variation_set_id', 'wpsc_variation_set');
		}
	
		foreach($variation_associations as $variation_association_id) {
			$base_product_terms[] = wpsc_get_meta($variation_association_id, 'variation_id', 'wpsc_variation');
		}
		
		// Now that we have the term IDs, we need to retrieve the slugs, as wp_set_object_terms will not use IDs in the way we want
		// If we pass IDs into wp_set_object_terms, it creates terms using the ID as the name.
		$parent_product_terms = get_terms('wpsc-variation', array(
			'hide_empty' => 0,
			'include' => implode(",", $base_product_terms),
			'orderby' => 'parent'
		));
		$base_product_term_slugs = array();
		foreach($parent_product_terms as $parent_product_term) {
			$base_product_term_slugs[] = $parent_product_term->slug;
		
		}
		
		
		wp_set_object_terms($post->ID, $base_product_term_slugs, 'wpsc-variation');
		
		
		
		// select all variation "products"
		$variation_items = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_VARIATION_PROPERTIES." WHERE `product_id` = '{$original_id}'");
		//print_r($variation_items);
		//echo "\n";
		foreach((array)$variation_items as $variation_item) {
			// initialize the requisite arrays to empty
			$variation_ids = array();
			$term_data = array();
			// make a temporary copy of the product teplate
			$product_values = $child_product_template;
			
			// select all values this "product" is associated with, then loop through them, getting the term id of the variation using the value ID
			$variation_associations = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_VARIATION_COMBINATIONS." WHERE `priceandstock_id` = '{$variation_item->id}'");
			foreach((array)$variation_associations as $association) {
				$variation_id = (int)wpsc_get_meta($association->value_id, 'variation_id', 'wpsc_variation');
				// discard any values that are null, as they break the selecting of the terms
				if($variation_id > 0) {
					$variation_ids[] = $variation_id;
				}
			} 
			
			// if we have more than zero remaining terms, get the term data, then loop through it to convert it to a more useful set of arrays.
			if(count($variation_ids) > 0) {
				$combination_terms = get_terms('wpsc-variation', array(
					'hide_empty' => 0,
					'include' => implode(",", $variation_ids),
					'orderby' => 'parent',
				));
				foreach($combination_terms as $term) {
					$term_data['ids'][] = $term->term_id;
					$term_data['slugs'][] = $term->slug;
					$term_data['names'][] = $term->name;
				}
				
				
				
				$product_values['post_title'] .= " (".implode(", ", $term_data['names']).")";
				$product_values['post_name'] = sanitize_title($product_values['post_title']);
				// wp_get_post_terms( $post_id = 0, $taxonomy = 'post_tag', $args = array() ) {
		
				//print_r($product_values);
				
				
				$selected_post = get_posts(array(
					//'numberposts' => 1,
					'name' => $product_values['post_name'],
					'post_parent' => $post->ID,
					'post_type' => "wpsc-product",
					'post_status' => 'all',
					'suppress_filters' => true
				));
				
				
				$selected_post = array_shift($selected_post);
				
				$child_product_id = wpsc_get_child_object_in_terms($post->ID, $term_data['ids'], 'wpsc-variation');
				$post_data = array();
				$post_data['_wpsc_price'] = (float)$variation_item->price;
				$post_data['_wpsc_stock'] = (float)$variation_item->stock;
				if($parent_stock === false) {
				  $post_data['_wpsc_stock'] = false;
				}
				$post_data['_wpsc_original_variation_id'] = (float)$variation_item->id;
				
				
				$weight = wpsc_convert_weights($variation_item->weight, $variation_item->weight_unit);
				
				// Product Weight
				$post_data['_wpsc_product_metadata']['weight'] = (float)($weight * 453.59237); // convert all weights to grams
				$post_data['_wpsc_product_metadata']['display_weight_as'] = $variation_item->weight_unit;
	            
				
	            
	            
            	//file
				
				
				
				
				
				//echo "<pre>".print_r($product_values, true)."</pre>";
				if($child_product_id == false) {
					if($selected_post != null) {
						$child_product_id = $selected_post->ID;
					} else {
						$child_product_id = wp_update_post($product_values);
					}
				} else {
					// sometimes there have been problems saving the variations, this gets the correct product ID
					if(($selected_post != null) && ($selected_post->ID != $child_product_id)) {
						$child_product_id = $selected_post->ID;
					}
				}
				if($child_product_id > 0) {
					
					foreach($post_data as $meta_key => $meta_value) {
						// prefix all meta keys with _wpsc_
						update_post_meta($child_product_id, $meta_key, $meta_value);
					}
							
				
					wp_set_object_terms($child_product_id, $term_data['slugs'], 'wpsc-variation');
				}
				
				//echo print_r($child_product_id, true);
				unset($term_data);
			}

		}
	}
+	delete_option("wpsc-variation_children");
+	_get_term_hierarchy('wpsc-variation');
}

function wpsc_update_files() {
	global $wpdb, $user_ID; 
	$product_files = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_PRODUCT_FILES."");
	
	//$product_file_meta = get_product_meta($product_id, 'product_files');
	//	$product_meta_files = $wpdb->get_results("
	//	SELECT `product_id`, `meta_value`
	//	FROM `".WPSC_TABLE_PRODUCTMETA."`
	//	WHERE `meta_key` IN ('product_files' )
	//	", ARRAY_A);
	
	//print_r($product_meta_files);
	
	foreach($product_files as $product_file) {
		$variation_post_ids = array();
		//echo print_r($product_file, true);
		$product_post_id = (int)$wpdb->get_var($wpdb->prepare( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE meta_key = %s AND `meta_value` = %d LIMIT 1", '_wpsc_original_id', $product_file->product_id ));
		
		$variation_items = $wpdb->get_col("SELECT `id` FROM ".WPSC_TABLE_VARIATION_PROPERTIES." WHERE `file` = '{$product_file->id}'");
		
		if(count($variation_items) > 0) {
			$variation_post_ids = $wpdb->get_col("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE meta_key = '_wpsc_original_variation_id' AND `meta_value` IN(".implode(", ", $variation_items).")");
		}

		$attachment_template = array(
			'post_mime_type' => $product_file->mimetype,
			'post_title' => $product_file->filename,
			'post_name' => $product_file->idhash,
			'post_content' => '',
			'post_parent' => 0,
			'post_type' => "wpsc-product-file",
			'post_status' => 'inherit'
		);
		
		
		$file_id = wpsc_get_meta($product_file->id, '_new_file_id', 'wpsc_files');
		
		if($file_id == null) {
			$file_data = $attachment_template;
			$file_data['post_parent'] = $product_post_id;
			$new_file_id = wp_insert_post($file_data);
			wpsc_update_meta($product_file->id, '_new_file_id', $new_file_id, 'wpsc_files');
		}
		
		if(count($variation_post_ids) > 0) {
			foreach($variation_post_ids as $variation_post_id) {				
				$old_file_id = get_product_meta($variation_post_id, 'old_file_id', true);
				if($old_file_id == null) {
					$file_data = $attachment_template;
					$file_data['post_parent'] = $variation_post_id;
					$new_file_id = wp_insert_post($file_data);
					update_product_meta($variation_post_id, 'old_file_id', $product_file->id, 'wpsc_files');
				}
			}
		}
		
	}
	
	
	$download_ids = $wpdb->get_col("SELECT `id` FROM ".WPSC_TABLE_DOWNLOAD_STATUS."");
	foreach($download_ids as $download_id) {
		if(wpsc_get_meta($download_id, '_is_legacy', 'wpsc_downloads') !== 'false') {
			wpsc_update_meta($download_id, '_is_legacy', 'true', 'wpsc_downloads');
		}
	}
}

?>