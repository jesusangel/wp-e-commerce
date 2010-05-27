<?php

/**
 * WP eCommerce form and other data saving functions
 *
 * This is used for functions that save things like variation sets and product groups that would be too large to have in the ajax.php file.
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
 
/**
 * Saves the variation set data
 * @param nothing
 * @return nothing
 */
function wpsc_save_variation_set() {
	global $wpdb, $wp_rewrite;
	
	

	/*  delete variation_value */
	if($_GET['delete_value'] == 'true') {
		if(is_numeric($_GET['value_id'])) {
			$value_id = absint($_GET['value_id']);
			check_admin_referer("delete-variation-$value_id");
			
			$return_value = wp_delete_term($value_id, 'wpsc-variation');
			if($_POST['ajax'] == 'true') {
				echo (string)$value_id;
				exit();
			}
		}
	}
	
	
	
	
	 //print("<pre>".print_r($term,true)."</pre>");
	 //exit("<pre>".print_r($_POST,true)."</pre>");
	
	if(($_POST['submit_action'] == "add") || ($_POST['submit_action'] == "edit")) {
		check_admin_referer('edit-variation', 'wpsc-edit-variation');
		  
		/* add variation */
		if($_POST['submit_action'] == "add") {
			$name = $_POST['name'];
			$term = get_term_by('name', $name, 'wpsc-variation', ARRAY_A);
			if(empty($term)) {
				$term = wp_insert_term( $name, 'wpsc-variation',array('parent' => 0));
			}
			
			//print("<pre>".print_r($term,true)."</pre>");
			//exit("<pre>".print_r($_POST,true)."</pre>");
		    if(!empty($term)) { 
				$variation_id = $term['term_id'];
				$variation_values = $_POST['new_variation_values'];
				$variation_value_sql_items = array();
				foreach($variation_values as $variation_value) {
					$term = get_term_by('name', $variation_value, 'wpsc-variation', ARRAY_A);
					if(empty($term)) {
						$term = wp_insert_term( $variation_value, 'wpsc-variation',array('parent' => $variation_id));
					}
				}
			}
		}
	    
		/* edit variation */
		if(($_POST['submit_action'] == "edit") && is_numeric($_POST['variation_id'])) {
			$variation_id = absint($_POST['variation_id']);
			
			$variation_set_name = $_POST['name'];
			$term = get_term_by('name', $name, 'wpsc-variation', ARRAY_A);
			if(empty($term)) {
				$term = wp_insert_term( $name, 'wpsc-variation',array('parent' => 0));
			} else {
				wp_update_term($variation_id, 'wpsc-variation', array(
					'name' => $variation_set_name
				));
			}
			
			
			
			
			//exit("<pre>".print_r($_POST, true)."</pre>");
			foreach($_POST['variation_values'] as $variation_value_id => $variation_value_name) {
				if(is_numeric($variation_value_id)) {
					$variation_value_id = absint($variation_value_id);
					wp_update_term($variation_value_id, 'wpsc-variation', array(
						'name' => $variation_value_name
					));
					//$variation_value_state = $wpdb->get_results("SELECT `name` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '$variation_value_id' AND `variation_id` = '$variation_id' LIMIT 1",ARRAY_A);
					//$variation_value_state = $variation_value_state[0]['name'];
				}
				
				if($variation_value_state != $variation_value) {
					//$wpdb->query("UPDATE `".WPSC_TABLE_VARIATION_VALUES."` SET `name` = '".$wpdb->escape($variation_value)."' WHERE `id` = '$variation_value_id' AND `variation_id` = '".$variation_id."' LIMIT 1;");
				}
			}
			
			if($_POST['new_variation_values'] != null) {
				foreach($_POST['new_variation_values'] as $variation_value) {
					$term = get_term_by('name', $variation_value, 'wpsc-variation', ARRAY_A);
					if(empty($term)) {
						$term = wp_insert_term( $variation_value, 'wpsc-variation',array('parent' => $variation_id));
					}
				}
			}
		}
	}
	//http://sandbox.boiling-pukeko.geek.nz/wp-admin/admin.php?wpsc_admin_action=wpsc-variation-set&delete_value=true&value_id=20&_wpnonce=c1eab6de52
	$sendback = remove_query_arg(array(
		'wpsc_admin_action',
		'delete_value',
		'_wpnonce',
		'value_id'
	));
	
	if($_GET['page'] == null) {
		$sendback = add_query_arg('page', 'wpsc-edit-variations', $sendback);
	}
	$sendback = add_query_arg('message', 1, $sendback);
	//exit($sendback);
	wp_redirect($sendback);
}


/**
 * wpsc_save_category_set, Saves the category set data
 * @param nothing
 * @return nothing
 */
function wpsc_save_category_set() {
	global $wpdb;
	
	if(($_POST['submit_action'] == "add") || ($_POST['submit_action'] == "edit")) {
		check_admin_referer('edit-category', 'wpsc-edit-category');
		
		//exit("<pre>".print_r($_POST,true)."</pre>"); 
		  
		/* Image Processing Code*/
		if(($_FILES['image'] != null) && preg_match("/\.(gif|jp(e)*g|png){1}$/i",$_FILES['image']['name'])) {
			if(function_exists("getimagesize")) {
				if(((int)$_POST['width'] > 10 && (int)$_POST['width'] < 512) && ((int)$_POST['height'] > 10 && (int)$_POST['height'] < 512) ) {
					$width = (int)$_POST['width'];
					$height = (int)$_POST['height'];
					image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']), $width, $height);
				} else {
					image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']));
				}	
				$image = $wpdb->escape($_FILES['image']['name']);
			} else {
				$new_image_path = (WPSC_CATEGORY_DIR.basename($_FILES['image']['name']));
				move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path);
				$stat = stat( dirname( $new_image_path ));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $new_image_path, $perms );	
				$image = $wpdb->escape($_FILES['image']['name']);
			}
		} else {
			$image = '';
		}
		
	
		/* Set the parent category ID variable*/
		if(is_numeric($_POST['category_parent']) && absint($_POST['category_parent']) > 0) {
			$parent_category = (int)$_POST['category_parent'];
		} else {
			$parent_category = 0;
		}
		
		  
		/* add category code */
		if($_POST['submit_action'] == "add") {
			$name = $_POST['name'];
			$term = get_term_by('name', $name, 'wpsc_product_category', ARRAY_A);
			if(empty($term)) {
				$term = wp_insert_term( $name, 'wpsc_product_category',array('parent' => 0));
			}
			
			$category_id= $term['term_id'];
			
			$category = get_term_by('id', $category_id, 'wpsc_product_category');
			$url_name=$category->slug;
			
			//$wp_rewrite->flush_rules(); 
			if($category_id > 0) {
				wpsc_update_categorymeta($category_id, 'nice-name', $url_name);
				wpsc_update_categorymeta($category_id, 'description', $wpdb->escape(stripslashes($_POST['description'])));
				if($image != '') {
					wpsc_update_categorymeta($category_id, 'image', $image);
				}
				//wpsc_update_categorymeta($category_id, 'image', $image);
				wpsc_update_categorymeta($category_id, 'fee', '0');
				wpsc_update_categorymeta($category_id, 'active', '1');
				wpsc_update_categorymeta($category_id, 'order', '0');
				
				if($_POST['use_additonal_form_set'] != '') {
					wpsc_update_categorymeta($category_id, 'use_additonal_form_set', $_POST['use_additonal_form_set']);
				} else {
					wpsc_delete_categorymeta($category_id, 'use_additonal_form_set');
				}
	
				if((bool)(int)$_POST['uses_billing_address'] == true) {
					wpsc_update_categorymeta($category_id, 'uses_billing_address', 1);
					$uses_additional_forms = true;
				} else {
					wpsc_update_categorymeta($category_id, 'uses_billing_address', 0);
					$uses_additional_forms = false;
				}
			}
		}
		
		
	    
		/* edit category code */
		if(($_POST['submit_action'] == "edit") && is_numeric($_POST['category_id'])) {
			$category_id = absint($_POST['category_id']);
			
			$name = $_POST['name'];
			
			 
			if($category->name != $name) {
				wp_update_term($category_id, 'wpsc_product_category', array(
					'name' => $name
				));
				$category = get_term($category_id, 'wpsc_product_category');
				//$wp_rewrite->flush_rules(); 
			}
			
			
			$url_name=$category->slug;
			wpsc_update_categorymeta($category_id, 'nice-name', $url_name);
			wpsc_update_categorymeta($category_id, 'description', $wpdb->escape(stripslashes($_POST['description'])));
			
			
			if($_POST['deleteimage'] == 1) {
				wpsc_delete_categorymeta($category_id, 'image');
			} else if($image != '') {
				wpsc_update_categorymeta($category_id, 'image', $image);
			}
			
			if(is_numeric($_POST['height']) && is_numeric($_POST['width']) && ($image == null)) {
				$imagedata = wpsc_get_categorymeta($category_id, 'image');
				if($imagedata != null) {
					$height = $_POST['height'];
					$width = $_POST['width'];
					$imagepath = WPSC_CATEGORY_DIR . $imagedata;
					$image_output = WPSC_CATEGORY_DIR . $imagedata;
					image_processing($imagepath, $image_output, $width, $height);
				}
			}
			
			
			wpsc_update_categorymeta($category_id, 'fee', '0');
			wpsc_update_categorymeta($category_id, 'active', '1');
			wpsc_update_categorymeta($category_id, 'order', '0');
			
			
			if($_POST['use_additonal_form_set'] != '') {
				wpsc_update_categorymeta($category_id, 'use_additonal_form_set', $_POST['use_additonal_form_set']);
			} else {
				wpsc_delete_categorymeta($category_id, 'use_additonal_form_set');
			}
	
			if((bool)(int)$_POST['uses_billing_address'] == true) {
				wpsc_update_categorymeta($category_id, 'uses_billing_address', 1);
				$uses_additional_forms = true;
			} else {
				wpsc_update_categorymeta($category_id, 'uses_billing_address', 0);
				$uses_additional_forms = false;
			}	
		}
	}
	
	$sendback = remove_query_arg(array(
		'wpsc_admin_action',
		'delete_category',
		'_wpnonce',
		'category_id'
	));
	
	if($_GET['page'] == null) {
		$sendback = add_query_arg('page', 'wpsc-edit-variations', $sendback);
	}
	
	$sendback = add_query_arg('message', 1, $sendback);
	wp_redirect($sendback);
}


?>