<?php
/**
 * WP eCommerce Admin AJAX functions
 *
 * These are the WPSC Admin AJAX functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
 //call to view more details for purchase log
 
 
 //call to search purchase logs
 
  function wpsc_purchlog_search_by(){
//  exit('<pre>'.print_r($_POST,true).'</pre>');
 	wpsc_search_purchlog_view($_POST['purchlogs_searchbox']);
 } 
 
 if($_REQUEST['wpsc_admin_action'] == 'purchlogs_search') {
	add_action('admin_init', 'wpsc_purchlog_search_by');
}
 //call to change view for purchase log
 
 function wpsc_purchlog_filter_by(){
 	wpsc_change_purchlog_view($_POST['view_purchlogs_by'], $_POST['view_purchlogs_by_status']);
 } 
 
 if($_REQUEST['wpsc_admin_action'] == 'purchlog_filter_by') {
	add_action('admin_init', 'wpsc_purchlog_filter_by');
}
 //bulk actions for purchase log
function wpsc_purchlog_bulk_modify(){
	if($_POST['purchlog_multiple_status_change'] != -1){
		if(is_numeric($_POST['purchlog_multiple_status_change'])){
			foreach((array)$_POST['purchlogids'] as $purchlogid){
				wpsc_purchlog_edit_status($purchlogid, $_POST['purchlog_multiple_status_change']);
				$updated++;
			}
			
		}elseif($_POST['purchlog_multiple_status_change'] == 'delete'){
			foreach((array)$_POST['purchlogids'] as $purchlogid){
				wpsc_delete_purchlog($purchlogid);
				$deleted++;
			}
		}
		
	}
	$sendback = wp_get_referer();
	if ( isset($updated) ) {
		$sendback = add_query_arg('updated', $updated, $sendback);
	}
	if ( isset($deleted) ) {
		$sendback = add_query_arg('deleted', $deleted, $sendback);
	}
	wp_redirect($sendback);
	
	exit();
}

if($_REQUEST['wpsc_admin_action'] == 'purchlog_bulk_modify') {
	add_action('admin_init', 'wpsc_purchlog_bulk_modify');
}
//edit purchase log status function
function wpsc_purchlog_edit_status($purchlog_id='', $purchlog_status=''){
	global $wpdb;
	if(($purchlog_id =='') && ($purchlog_status == '')){
		$purchlog_id = (int)$_POST['purchlog_id'];
		$purchlog_status = (int)$_POST['purchlog_status'];
	}
	//exit($purchlog_id.' BEING TRIGGERED '.$purchlog_status);
	$sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET processed=".$purchlog_status." WHERE id=".$purchlog_id;
	$wpdb->query($sql);
}

if($_REQUEST['wpsc_admin_action'] == 'purchlog_edit_status') {
	add_action('admin_init', 'wpsc_purchlog_edit_status');
}

function wpsc_save_product_order() {
  global $wpdb;
	if(is_numeric($_POST['category_id'])) {
		$category_id = (int)$_POST['category_id'];
		$products = $_POST['product'];
		$order=1;
		foreach($products as $product_id) {
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order`=$order WHERE `product_id`=".(int)$product_id." AND `category_id`=".(int)$category_id." LIMIT 1");
			$order++;
		} 
		$success = true;
	} else {
		$success = false; 
	}
	exit((string)$success);
}
 
 
if($_REQUEST['wpsc_admin_action'] == 'save_product_order') {
	add_action('admin_init', 'wpsc_save_product_order');
}
 
//delete a purchase log
function wpsc_delete_purchlog($purchlog_id='') {
	global $wpdb;
	$deleted = 0;
	if($purchlog_id == ''){
		$purchlog_id = absint($_GET['purchlog_id']);
		check_admin_referer('delete_purchlog_' .  $purchlog_id);
  	}
  
  
	///
	if(is_numeric($purchlog_id)) {
		  
		  $delete_log_form_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$purchlog_id'";
		  $cart_content = $wpdb->get_results($delete_log_form_sql,ARRAY_A);
		  foreach((array)$cart_content as $cart_item) {
		    $cart_item_variations = $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id` = '".$cart_item['id']."'", ARRAY_A);
			}
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$purchlog_id'");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` IN ('$purchlog_id')");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='$purchlog_id' LIMIT 1");
		//  return '<div id="message" class="updated fade"><p>'.TXT_WPSC_THANKS_DELETED.'</p></div>';
		$deleted = 1;
		}
 
	////	
	if(is_numeric($_GET['purchlog_id'])){
		$sendback = wp_get_referer();
		if ( isset($deleted) ) {
			$sendback = add_query_arg('deleted', $deleted, $sendback);
		}
		wp_redirect($sendback);
		
		exit();
	}
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'delete_purchlog') {
	add_action('admin_init', 'wpsc_delete_purchlog');
}
 
 
 

function wpsc_bulk_modify_products() {
  global $wpdb;
  $doaction = $_GET['action'];
  
  switch ( $doaction ) {
		case 'delete':
		  //echo "<pre>".print_r($_GET,true)."</pre>";
			if ( isset($_GET['product']) && ! isset($_GET['bulk_edit']) && (isset($doaction) || isset($_GET['doaction2'])) ) {
			
		  //echo "<pre>".print_r($_GET,true)."</pre>";
				check_admin_referer('bulk-products');
				$deleted = 0;
				foreach( (array) $_GET['product'] as $product_id ) {
				  $product_id = absint($product_id);
					if($wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET  `active` = '0' WHERE `id`='{$product_id}' LIMIT 1")) {
						$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` = '{$product_id}' AND `meta_key` IN ('url_name')");  
						product_tag_init();
						$term = wp_get_object_terms($product_id, 'product_tag');
						if ($term->errors == '') {
							wp_delete_object_term_relationships($product_id, 'product_tag');
						}
	
						$deleted++;
					}
				}
			}
			break;
	}
	
	$sendback = wp_get_referer();
	if ( isset($deleted) ) {
		$sendback = add_query_arg('deleted', $deleted, $sendback);
	}
	wp_redirect($sendback);
	
	exit();
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'bulk_modify') {
	add_action('admin_init', 'wpsc_bulk_modify_products');
}
 
 
 
function wpsc_delete_product() {
  global $wpdb;
  
	$deleted = 0;
	$product_id = absint($_GET['product']);
  check_admin_referer('delete_product_' .  $product_id);
	if($wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET  `active` = '0' WHERE `id`='{$product_id}' LIMIT 1")) {
		$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` = '{$product_id}' AND `meta_key` IN ('url_name')");  
		product_tag_init();
		$term = wp_get_object_terms($product_id, 'product_tag');
		if ($term->errors == '') {
			wp_delete_object_term_relationships($product_id, 'product_tag');
		}
		$deleted = 1;
	}
	
	$sendback = wp_get_referer();
	if ( isset($deleted) ) {
		$sendback = add_query_arg('deleted', $deleted, $sendback);
	}
	wp_redirect($sendback);
	
	exit();
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'delete_product') {
	add_action('admin_init', 'wpsc_delete_product');
}
 
 
 
function wpsc_purchase_log_csv() {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();
  if(($_GET['purchase_log_csv'] == "true") && ($_GET['rss_key'] == 'key') && is_numeric($_GET['start_timestamp']) && is_numeric($_GET['end_timestamp']) && ($user_level >= 7)) {
    $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `display_log` = '1';";
    $form_data = $wpdb->get_results($form_sql,ARRAY_A);
    
    $start_timestamp = $_GET['start_timestamp'];
    $end_timestamp = $_GET['end_timestamp'];
    $data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC",ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: inline; filename="Purchase Log '.date("M-d-Y", $start_timestamp).' to '.date("M-d-Y", $end_timestamp).'.csv"');      
    
    foreach($data as $purchase) {
      $country_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
      $country_data = $wpdb->get_results($country_sql,ARRAY_A);
      $country = $country_data[0]['value'];
           
      $output .= "\"".nzshpcrt_find_total_price($purchase['id'],$country) ."\",";
                
      foreach($form_data as $form_field) {
        $collected_data_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".$form_field['id']."' LIMIT 1";
        $collected_data = $wpdb->get_results($collected_data_sql,ARRAY_A);
        $collected_data = $collected_data[0];
        $output .= "\"".$collected_data['value']."\",";
			}
        
      if(get_option('payment_method') == 2) {
        $gateway_name = '';
        foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
          if($purchase['gateway'] != 'testmode') {
            if($gateway['internalname'] == $purchase['gateway'] ) {
              $gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
					}
				}
        $output .= "\"". $gateway_name ."\",";
			}
              
      if($purchase['processed'] < 1) {
        $purchase['processed'] = 1;
			}
      $stage_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_STATUSES."` WHERE `id`='".$purchase['processed']."' AND `active`='1' LIMIT 1";
      $stage_data = $wpdb->get_results($stage_sql,ARRAY_A);
              
      $output .= "\"". $stage_data[0]['name'] ."\",";
      
      $output .= "\"". date("jS M Y",$purchase['date']) ."\"";
      
      $cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase['id']."";
      $cart = $wpdb->get_results($cartsql,ARRAY_A) ; 
      //exit(nl2br(print_r($cart,true)));
      
      foreach($cart as $item) {
        $output .= ",";
        $product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$item['prodid']." LIMIT 1",ARRAY_A);        
        $variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$item['id']."'";
        $variation_data = $wpdb->get_results($variation_sql,ARRAY_A);
         $variation_count = count($variation_data);
          if($variation_count >= 1) {
            $variation_list = " (";
            $i = 0;
            foreach($variation_data as $variation) {
              if($i > 0) {
                $variation_list .= ", ";
							}
              $value_id = $variation['value_id'];
              $value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
              $variation_list .= $value_data[0]['name'];              
              $i++;
						}
            $variation_list .= ")";
					}
        
        
        $output .= "\"".$item['quantity']." ".$product['name'].$variation_list."\"";
			}
      $output .= "\n"; // terminates the row/line in the CSV file
		}
    echo $output;
    exit();
	}
}










function wpsc_admin_ajax() {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();  
	if(is_numeric($_POST['prodid']) && !isset($_POST['imageorder'])) {
		/* fill product form */    
		echo nzshpcrt_getproductform($_POST['prodid']);
		exit();
	} else if(is_numeric($_POST['catid'])) {
		/* fill category form */   
		echo nzshpcrt_getcategoryform($_POST['catid']);
		exit();
	} else if(is_numeric($_POST['brandid'])) {
		/* fill brand form */   
		echo nzshpcrt_getbrandsform($_POST['brandid']);
		exit();
	} else if(is_numeric($_POST['variation_id'])) {  
		echo nzshpcrt_getvariationform($_POST['variation_id']);
		exit();
	}
	
	
	if ($_POST['action'] == 'product-page-order'){
		$order = $_POST['order'];
		if(!isset($order[0])) {
			$order = $order['normal'];
		} else {
			$order = $order[0];
		}
		$order = array_unique(explode(',', $order));
		update_option('wpsc_product_page_order', $order);
		exit(print_r($order,1));
	}
	

	if ($_POST['del_prod'] == 'true') {
		$ids = $_POST['del_prod_id'];
		$ids = explode(',',$ids);
		foreach ($ids as $id) {
			$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='$id'");
		}
		exit();
	}
	
	if($_POST['del_img'] == 'true') {
		$img_id = (int)$_POST['del_img_id'];
		$wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`='{$img_id}' LIMIT 1");
		exit();
	}
	if ($_POST['del_file'] == 'true') {
		$wpdb->query("DELETE FROM ".WPSC_TABLE_PRODUCT_FILES." WHERE idhash=".$_POST['del_file_hash']);
		unlink(WPSC_FILE_DIR.$_POST['del_file_hash']);
		exit();
	}
		
	if(($_POST['save_image_upload_state'] == "true") && is_numeric($_POST['image_upload_state'])) {
		//get_option('wpsc_image_upload_state');
		$upload_state = (int)(bool)$_POST['image_upload_state'];
		update_option('wpsc_use_flash_uploader', $upload_state);
		exit("done");
	}
      
	if(($_POST['remove_variation_value'] == "true") && is_numeric($_POST['variation_value_id'])) {
		$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `value_id` = '".$_POST['variation_value_id']."'");
		$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '".$_POST['variation_value_id']."' LIMIT 1");
		exit();
	}
		

	if(($_POST['edit_variation_value_list'] == 'true') && is_numeric($_POST['variation_id']) && is_numeric($_POST['product_id'])) {
		$variation_id = (int)$_POST['variation_id'];
		$product_id = (int)$_POST['product_id'];
		$variations_processor = new nzshpcrt_variations();
		$variation_values = $variations_processor->falsepost_variation_values($variation_id);
		if(is_array($variation_values)) {
			//echo(print_r($variation_values,true));
			$check_variation_added = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}') AND `variation_id` IN ('{$variation_id}') LIMIT 1");
			//exit("<pre>".print_r($variation_values,true)."<pre>");
			if($check_variation_added == null) {
				$variations_processor->add_to_existing_product($product_id,$variation_values);			
			}
			echo $variations_processor->display_attached_variations($product_id);
			echo $variations_processor->variations_grid_view($product_id); 
		} else {
			echo "false";
		}
		exit();
	}
		

            
	if(($_POST['remove_form_field'] == "true") && is_numeric($_POST['form_id'])) {
		//exit(print_r($user,true));
		if(current_user_can('level_7')) {
			$wpdb->query("UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `active` = '0' WHERE `id` ='".$_POST['form_id']."' LIMIT 1 ;");
			exit(' ');
		}
	}
	
	
	if($_POST['hide_ecom_dashboard'] == 'true') {
		require_once (ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss('http://www.instinct.co.nz/feed/');				
		$rss->items = array_slice($rss->items, 0, 5);
		$rss_hash = sha1(serialize($rss->items));				
		update_option('wpsc_ecom_news_hash', $rss_hash);
		exit(1);
	}			
	
	if(($_POST['remove_meta'] == 'true') && is_numeric($_POST['meta_id'])) {
		$meta_id = (int)$_POST['meta_id'];
		$selected_meta = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `id` IN('{$meta_id}') ",ARRAY_A);
		if($selected_meta != null) {
			if($wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `id` IN('{$meta_id}')  LIMIT 1")) {
				echo $meta_id;
				exit();
			}
		}
		echo 0;
		exit();
	}
		
	if(($_REQUEST['log_state'] == "true") && is_numeric($_POST['id']) && is_numeric($_POST['value'])) {
		$newvalue = $_POST['value'];
		if ($_REQUEST['suspend']=='true'){
			if ($_REQUEST['value']==1){
				wpsc_member_dedeactivate_subscriptions($_POST['id']);
			} else {
				wpsc_member_deactivate_subscriptions($_POST['id']);
			}
			exit();
		} else {
		
			$log_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = '".$_POST['id']."' LIMIT 1",ARRAY_A);  
			if (($newvalue==2) && function_exists('wpsc_member_activate_subscriptions')){
				wpsc_member_activate_subscriptions($_POST['id']);
			}
			
			$update_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '".$newvalue."' WHERE `id` = '".$_POST['id']."' LIMIT 1";  
			$wpdb->query($update_sql);
			//echo("/*");
			if(($newvalue > $log_data['processed']) && ($log_data['processed'] < 2)) {
				transaction_results($log_data['sessionid'],false);
			}      
			//echo("*/");
			$stage_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_STATUSES."` WHERE `id`='".$newvalue."' AND `active`='1' LIMIT 1";
			$stage_data = $wpdb->get_row($stage_sql,ARRAY_A);
					
			echo "document.getElementById(\"form_group_".$_POST['id']."_text\").innerHTML = '".$stage_data['name']."';\n";
			echo "document.getElementById(\"form_group_".$_POST['id']."_text\").style.color = '#".$stage_data['colour']."';\n";
			
			
			$year = date("Y");
			$month = date("m");
			$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
			$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
			
			echo "document.getElementById(\"log_total_month\").innerHTML = '".addslashes(nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1))."';\n";
			echo "document.getElementById(\"log_total_absolute\").innerHTML = '".addslashes(nzshpcrt_currency_display(admin_display_total_price(),1))."';\n";
			exit();
		}
	}
  
	if(($_POST['list_variation_values'] == "true")) {
   		// retrieve the forms for associating variations and their values with products
		$variation_processor = new nzshpcrt_variations();
		$variations_selected = array();
    	foreach((array)$_POST['variations'] as $variation_id => $checked) {
    		$variations_selected[] = (int)$variation_id;
    	}

    	if(is_numeric($_POST['product_id'])) {
      		$product_id = (int)$_POST['product_id'];
					$selected_price = (float)$_POST['selected_price'];
      		
       		// variation values housekeeping
      		$variation_processor->edit_product_values($product_id,$_POST['edit_variation_values'], $selected_price);
      

      		// get all the currently associated variations from the database
      		$associated_variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}')", ARRAY_A);
      
      		$variations_still_associated = array();
      		foreach((array)$associated_variations as $associated_variation) {
			  	// remove variations not checked that are in the database
        		if(array_search($associated_variation['variation_id'], $variations_selected) === false) {
          			$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `id` = '{$associated_variation['id']}' LIMIT 1");
          			$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` = '{$product_id}' AND `variation_id` = '{$associated_variation['variation_id']}' ");
        		} else {
          			// make an array for adding in the variations next step, for efficiency
          			$variations_still_associated[] = $associated_variation['variation_id'];
        		}
      		}
       
					foreach((array)$variations_selected as $variation_id) {
			  	// add variations not already in the database that have been checked.
        		$variation_values = $variation_processor->falsepost_variation_values($variation_id);
        		if(array_search($variation_id, $variations_still_associated) === false) {
      	  			$variation_processor->add_to_existing_product($product_id,$variation_values);
        		}
      		}
      		//echo "/* ".print_r($associated_variations,true)." */\n\r";
					echo "edit_variation_combinations_html = \"".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_grid_view($product_id,  (array)$_POST['edit_variation_values'])))."\";\n";

    	} else {
      		if(count($variations_selected) > 0) {
        		// takes an array of variations, returns a form for adding data to those variations.
        		if((float)$_POST['selected_price'] > 0) {
          			$selected_price = (float)$_POST['selected_price'];
        		}
        		
						$limited_stock = false;
        		if($_POST['limited_stock'] == 'true') {
							$limited_stock = true;
        		}
        		
						echo "add_variation_combinations_html = \"".TXT_WPSC_EDIT_VAR."<br />".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_add_grid_view((array)$variations_selected, (array)$_POST['edit_variation_values'], $selected_price, $limited_stock)))."\";\n";

      		} else {
        		echo "add_variation_combinations_html = \"\";\n";
      		}
		}
		exit();
	}
	
	
	if ($_POST['imageorder']=='true') {
		$height = get_option('product_image_height');
		$width  = get_option('product_image_width');
		$images = explode(",",$_POST['order']);
		$prodid = (int)$_POST['prodid'];
    $timestamp = time();
		$new_main_image = (int)$images[0];
		

		
		if ($new_main_image!=0) {
		  
		  if($_POST['delete_primary'] == 'true' ) {
        $new_image_name = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`='{$new_main_image}' LIMIT 1");
		    $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$new_main_image}' LIMIT 1");
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image`='$new_image_name' WHERE `id`='{$prodid}' LIMIT 1");		    
        for($i=1;$i<count($images);$i++ ) {
          $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_IMAGES."` SET `image_order`='$i' WHERE `id`='".(int)$images[$i]."' LIMIT 1");
        }
		  } else {
        $new_image_name = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`='{$images[0]}' LIMIT 1");
        $old_image_name =  $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$prodid}' LIMIT 1");
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image`='$new_image_name' WHERE `id`='{$prodid}' LIMIT 1");
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_IMAGES."` SET `image`='$old_image_name' WHERE `id`='{$images[0]}' LIMIT 1");
        $image= image_processing(WPSC_IMAGE_DIR.$new_image_name, (WPSC_THUMBNAIL_DIR.$new_image_name),$width,$height,'thumbnailImage');
			}
		} else {
      if($_POST['delete_primary'] == 'true' ) {
        $new_image_name = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`='{$new_main_image}' LIMIT 1");
		    $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$new_main_image}' LIMIT 1");		
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image`='$new_image_name' WHERE `id`='{$prodid}' LIMIT 1");		    
      }
			for($i=1;$i<count($images);$i++ ) {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_IMAGES."` SET `image_order`='$i' WHERE `id`='".(int)$images[$i]."' LIMIT 1");
			}
		}
		$output .= "<div id='image_settings_box'>";
		$output .= "<div class='upper_settings_box'>";
		$output .= "<div class='upper_image'><img src='".WPSC_URL."/images/pencil.png'/></div><div class='upper_txt'>Thumbnail Settings<a class='closeimagesettings'>X</a></div>";
		$output .= "</div>";
		$output .= "<div class='lower_settings_box'>";
		$output .= "<table>";// style='border: 1px solid black'
		$output .= "  <tr>";
		$output .= "    <td style='height: 1em;'>";
		$output .= "<input type='hidden' id='current_thumbnail_image' name='current_thumbnail_image' value='" . $product['thumbnail_image'] . "' />";
		$output .= "<input type='radio' ";
		if ($product['thumbnail_state'] == 0) {
			$output .= "checked='true'";
		}
		$output .= " name='image_resize' value='0' id='image_resize0_$timestamp' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='image_resize0_$timestamp'> ".TXT_WPSC_DONOTRESIZEIMAGE."<br />";
		$output .= "    </td>";
		$output .= "  </tr>";

		$output .= "  <tr>";
		$output .= "    <td>";
		$output .= "<input type='radio' ";
		if ($product['thumbnail_state'] == 1) {
			$output .= "checked='true'";
		}
		$output .= "name='image_resize' value='1' id='image_resize1_$timestamp' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='image_resize1_$timestamp'>".TXT_WPSC_USEDEFAULTSIZE."(<abbr title='".TXT_WPSC_SETONSETTINGS."'>".get_option('product_image_height') ."&times;".get_option('product_image_width')."px</abbr>)";
		$output .= "    </td>";
		$output .= "  </tr>";

		$output .= "  <tr>";
		$output .= "    <td>";
		$output .= "<input type='radio' ";
		if ($product['thumbnail_state'] == 2) {
			$output .= "checked='true'";
		}
		$output .= " name='image_resize' value='2' id='image_resize2_$timestamp' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='image_resize2_$timestamp'>".TXT_WPSC_USESPECIFICSIZE." </label>
				<div class='heightWidth image_resize_extra_forms' style=display: ";

		if ($product['thumbnail_state'] == 2) {
			$output .= "block;";
		} else {
			$output .= "none;";
		}

		$output .= "'>
				<input id='image_width' type='text' size='4' name='width' value='' /><label for='image_resize2'>".TXT_WPSC_PXWIDTH."</label>
				<input id='image_height' type='text' size='4' name='height' value='' /><label for='image_resize2'>".TXT_WPSC_PXHEIGHT." </label></div>";
		$output .= "    </td>";
		$output .= "  </tr>";
		$output .= "  <tr>";
		$output .= "    <td>";
		$output .= "<input type='radio' ";
		if ($product['thumbnail_state'] == 3) {
			$output .= "checked='true'";
		}
		$output .= " name='image_resize' value='3' id='image_resize3_$timestamp' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='image_resize3_$timestamp'> ".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />";
		$output .= "<div class='browseThumb image_resize_extra_forms' style='display: ";

		if($product['thumbnail_state'] == 3) {
			$output .= "block";
		} else {
			$output .= "none";
		}

		$output .= ";'>\n\r<input type='file' name='thumbnailImage' size='15' value='' />";
		$output .= "</div>\n\r";
		$output .= "    </td>";
		$output .= "  </tr>";
		
    $output .= "  <tr>";
    $output .= "    <td>";
    $output .= "    <a href='#' class='delete_primary_image'>Delete this Image</a>";
    $output .= "    </td>";
    $output .= "  </tr>";
		
		$output .= "</table>";
		$output .= "</div>";
		echo "output='".str_replace(array("\n", "\r"), array('\n', '\r'), addslashes($output))."';\n\r";
		echo "ser='".$images[0]."';\n\r";
		
		exit();
	}
    
	if(isset($_POST['language_setting']) && ($_GET['page'] = WPSC_DIR_NAME.'/options.php')) {
		if($user_level >= 7) {
			update_option('language_setting', $_POST['language_setting']);
		}
	}
}


function wpsc_admin_sale_rss() {
  global $wpdb;
  if(($_GET['rss'] == "true") && ($_GET['rss_key'] == 'key') && ($_GET['action'] == "purchase_log")) {
    $sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date`!='' ORDER BY `date` DESC";
    $purchase_log = $wpdb->get_results($sql,ARRAY_A);
    header("Content-Type: application/xml; charset=UTF-8"); 
    header('Content-Disposition: inline; filename="WP_E-Commerce_Purchase_Log.rss"');
    $output = '';
    $output .= "<?xml version='1.0'?>\n\r";
    $output .= "<rss version='2.0'>\n\r";
    $output .= "  <channel>\n\r";
    $output .= "    <title>WP E-Commerce Product Log</title>\n\r";
    $output .= "    <link>".get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php</link>\n\r";
    $output .= "    <description>This is the WP E-Commerce Product Log RSS feed</description>\n\r";
    $output .= "    <generator>WP E-Commerce Plugin</generator>\n\r";
    
    foreach((array)$purchase_log as $purchase) {
      $purchase_link = get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php&amp;purchaseid=".$purchase['id'];
      $output .= "    <item>\n\r";
      $output .= "      <title>Purchase No. ".$purchase['id']."</title>\n\r";
      $output .= "      <link>$purchase_link</link>\n\r";
      $output .= "      <description>This is an entry in the purchase log.</description>\n\r";
      $output .= "      <pubDate>".date("r",$purchase['date'])."</pubDate>\n\r";
      $output .= "      <guid>$purchase_link</guid>\n\r";
      $output .= "    </item>\n\r";
		}
    $output .= "  </channel>\n\r";
    $output .= "</rss>";
    echo $output;
    exit();
	}
}

function wpsc_shipping_options(){
	if ($_GET['shipping_options']=='true'){
		include(WPSC_FILE_PATH.'/display-shipping.php');
		exit();
	}
	
	if ($_GET['payments_options']=='true'){
		include(WPSC_FILE_PATH.'/gatewayoptions.php');
		exit();
	}
	
	if ($_GET['checkout_options']=='true'){
		include(WPSC_FILE_PATH.'/form_fields.php');
		exit();
	}
	
// 	if ($_GET['gold_options']=='true'){
// 		include(WPSC_FILE_PATH.'/gold_cart_files/gold_options.php');
// 		exit();
// 	}
}

function wpsc_shipping_submits(){
	if ($_POST['shipping_submits']=='true'){
		require_once(WPSC_FILE_PATH."/display-shipping.php");
		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/options.php");
		exit();
	}
	
	if ($_POST['gateway_submits']=='true'){
		require_once(WPSC_FILE_PATH."/gatewayoptions.php");
		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/options.php");
		exit();
	}
	
	if ($_POST['checkout_submits']=='true'){
		require_once(WPSC_FILE_PATH."/form_fields.php");
		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/options.php");
		exit();
	}
	
// 	if ($_POST['gold_submits']=='true'){
// 		require_once(WPSC_FILE_PATH.'/gold_cart_files/gold_options.php');
// 		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/options.php");
// 		exit();
// 	}
}



function wpsc_swfupload_images() {
	global $wpdb;
	if ($_REQUEST['action']=='wpsc_add_image') {
		$file = $_FILES['Filedata'];
		$pid = (int)$_POST['prodid'];
		//mail('thomas.howard@gmail.com','swfuploader', print_r($_POST,true).print_r($_FILES,true));
		if(function_exists('gold_shpcrt_display_gallery')) {
		  // if more than one image is permitted
      $existing_image_data = $wpdb->get_row("SELECT COUNT(*) AS `count`,  MAX(image_order) AS `order` FROM ".WPSC_TABLE_PRODUCT_IMAGES." WHERE product_id='$pid'", ARRAY_A);
      $order = (int)$existing_image_data['order'];
      $count = $existing_image_data['count'];
      
      $previous_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$pid}' LIMIT 1");
      if(($count >  0) || (strlen($previous_image) > 0)) {
        // if there is more than one image
        $success = move_uploaded_file($file['tmp_name'], WPSC_IMAGE_DIR.basename($file['name']));
				if ($pid == '') {
					copy(WPSC_IMAGE_DIR.basename($file['name']),WPSC_THUMBNAIL_DIR.basename($file['name']));
				}
				$order++;
				if ($success) {
					if ($pid != '') {
						$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` ( `product_id` , `image` , `width` , `height` , `image_order` ) VALUES( '$pid','".basename($file['name'])."', '0', '0',  '$order')");
					}
					$id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` LIMIT 1");
					$src = $file['name'];
					$output = "src='".$src."';id='".$id."';pid='$pid';";
				} else {
					$output = "file uploading error";
				}
			} else {
			  // if thereare no images
				$src = wpsc_item_process_image($product_id, $file['tmp_name'], $file['name']);
				if($src != null) {
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$src}' WHERE `id`='{$pid}' LIMIT 1");
					$output = "src='".$src."';id='0';pid='$pid';";
				} else {
					$output = "file uploading error";
				}
			}
		} else {
      // Otherwise...
      $previous_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$pid}' LIMIT 1");
      
      $src = wpsc_item_process_image($product_id, $file['tmp_name'], $file['name']);
      if($src != null) {
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '{$src}' WHERE `id`='{$pid}' LIMIT 1");
        if(strlen($previous_image) > 0) {
					$output = "replacement_src='".WPSC_IMAGE_URL.$src."';"; 
        } else {
					$output = "src='".$src."';id='0';pid='$pid';";        
        }
      } else {
        $output = "file uploading error";
      }
		}
		
// 		$wpsc_swfupload_log = get_option('wpsc_swfupload_log');
// 		$wpsc_swfupload_log .= $output;
// 		update_option('wpsc_swfupload_log', $wpsc_swfupload_log);
		exit($output);
	}
}


function wpsc_display_invoice() {
  $purchase_id = (int)$_GET['purchaseid'];
  include_once(WPSC_FILE_PATH."/admin-form-functions.php");
  // echo "testing";
	require_once(ABSPATH.'wp-admin/includes/media.php');
	wp_iframe('wpsc_packing_slip', $purchase_id);  
  //wpsc_packing_slip($purchase_id);
  exit();
}


function wpsc_save_inline_price() {
	global $wpdb;
	$pid = $_POST['id'];
	$new_price = $_POST['value'];
	$new_price1 = str_replace('$','',$new_price);
	$wpdb->query("UPDATE ".WPSC_TABLE_PRODUCT_LIST." SET price='$new_price1' WHERE id='$pid'");
	exit($new_price);
}

if($_GET['inline_price']=='true') {
	add_action('admin_init', 'wpsc_save_inline_price', 0);
}


if($_GET['display_invoice']=='true') {
  add_action('admin_init', 'wpsc_display_invoice', 0);
}


add_action('admin_init','wpsc_shipping_options');
add_action('admin_init','wpsc_shipping_submits');
add_action('init','wpsc_swfupload_images');

if($_GET['action'] == "purchase_log") {
	add_action('admin_init', 'wpsc_admin_sale_rss');
}


if($_GET['purchase_log_csv'] == "true") {
	add_action('admin_init', 'wpsc_purchase_log_csv');
}

if(($_REQUEST['ajax'] == "true") && ($_REQUEST['admin'] == "true")) {
	add_action('admin_init', 'wpsc_admin_ajax');
}

?>