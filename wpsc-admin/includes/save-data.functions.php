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
 */
function wpsc_save_variation_set() {
	global $wpdb;
	
	$imagedir = WPSC_FILE_PATH."/variation_images/";
	

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


?>