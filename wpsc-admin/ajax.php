<?php
/**
 * WP eCommerce Admin AJAX functions
 *
 * These are the WPSC Admin AJAX functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_ajax_load_product() {
  global $wpdb;
  $product_id = absint($_REQUEST['product_id']);
	wpsc_display_product_form($product_id);
	exit();
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'load_product') {
	add_action('admin_init', 'wpsc_ajax_load_product');
}


function wpsc_crop_thumb() {
	global $wpdb;
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$targ_w = $targ_h = $_POST['thumbsize'];
		$jpeg_quality = $_POST['jpegquality'];
		$product_id = $_POST['product_id'];
		
		$image['x'] = absint($_POST['x']);
		$image['y'] = absint($_POST['y']);
		$image['w'] = absint($_POST['w']);
		$image['h'] = absint($_POST['h']);
		
		
		$imagename = basename($_POST['imagename']);
		$source = WPSC_IMAGE_DIR.$imagename;
		$destination =  WPSC_THUMBNAIL_DIR.$imagename;
		
		if(is_file($source)) {
			$imagetype = getimagesize($source);
			
			switch($imagetype[2]) {
				case IMAGETYPE_JPEG:
				$img_r = imagecreatefromjpeg($source);
				break;
		
				case IMAGETYPE_GIF:
				$img_r = imagecreatefromgif($source);
				break;
		
				case IMAGETYPE_PNG:
				$img_r = imagecreatefrompng($source);
				break;
			}
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
		
		//	exit($full_path);
			//exit(' destination '.$dst_r.' resource '.$img_r.' destination X and Y 0 0  resource X and Y'.$_POST['x'].' '.$_POST['y'].'destination width and height '.$targ_w.' '. $targ_h.' resource width and height'.$_POST['w'].' '.$_POST['h']);
			imagecopyresampled($dst_r,$img_r,0,0,$image['x'],$image['y'],$targ_w,$targ_h,$image['w'],$image['h']);
		//	imagejpeg($dst_r,$full_path);
		//	header('Content-type: image/jpeg');
		
			imagejpeg($dst_r,$destination,$jpeg_quality);
			$cropped = true;
		}
		$sendback = wp_get_referer();
		if($cropped){
			$sendback = add_query_arg('product_id', $product_id, $sendback);
		}
		wp_redirect($sendback);
		//exit();
	}
}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'crop_thumb') {
	add_action('admin_init', 'wpsc_crop_thumb');
} 
 

function wpsc_bulk_modify_products() {
  global $wpdb;
  
  // exit("<pre>".print_r($_GET ,true)."</pre>");
  
  $doaction = $_GET['action'];
  
	$sendback = wp_get_referer();
  switch ( $doaction ) {
		case 'delete':
		  //echo "<pre>".print_r($_GET,true)."</pre>";
			if ( isset($_GET['product']) && ! isset($_GET['bulk_edit']) && (isset($doaction) || isset($_GET['doaction2'])) ) {
			
		  //echo "<pre>".print_r($_GET,true)."</pre>";
				check_admin_referer('bulk-products', 'wpsc-bulk-products');
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
			if ( isset($deleted) ) {
				$sendback = add_query_arg('deleted', $deleted, $sendback);
			}
			break;
			
			default:
				if(isset($_GET['search']) && !empty($_GET['search'])) {
				$sendback = add_query_arg('search',$_GET['search'], $sendback);
				
				
				}
			
			break;
			
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
 
 
function wpsc_duplicate_product() {
	global $wpdb;
	$product_id = absint($_GET['product']);
  check_admin_referer('duplicate_product_' .  $product_id);
	if ($product_id > 0) {
		$sql = " INSERT INTO ".WPSC_TABLE_PRODUCT_LIST."( `name` , `description` , `additional_description` , `price` , `weight` , `weight_unit` , `pnp` , `international_pnp` , `file` , `image` , `category` , `brand` , `quantity_limited` , `quantity` , `special` , `special_price` , `display_frontpage` , `notax` , `active` , `donation` , `no_shipping` , `thumbnail_image` , `thumbnail_state` ) SELECT `name` , `description` , `additional_description` , `price` , `weight` , `weight_unit` , `pnp` , `international_pnp` , `file` , `image` , `category` , `brand` , `quantity_limited` , `quantity` , `special` , `special_price` , `display_frontpage` , `notax` , `active` , `donation` , `no_shipping` , `thumbnail_image` , `thumbnail_state` FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = '".$product_id."' ";
		$wpdb->query($sql);
		$new_id= $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_LIST."` LIMIT 1");
		
		//Inserting duplicated category record.
		$category_assoc = $wpdb->get_col("SELECT category_id FROM ".WPSC_TABLE_ITEM_CATEGORY_ASSOC." WHERE product_id = '".$product_id."'");
		$new_product_category = "";
		if (count($category_assoc) > 0) {
			foreach($category_assoc as $key => $category) {
				$new_product_category .= "('".$new_id."','".$category."')";
				if (count($category_assoc) != $key+1) {
					$new_product_category .= ",";
				}
				
				$check_existing = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN('$category') AND `order` IN('0') LIMIT 1;",ARRAY_A);
				if($wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_ORDER."` WHERE `category_id` IN('$category') AND `product_id` IN('$product_id') LIMIT 1")) {
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = '0' WHERE `category_id` IN('$category') AND `product_id` IN('$product_id') LIMIT 1;");
				} else {				  
					$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_ORDER."` (`category_id`, `product_id`, `order`) VALUES ('$category', '$product_id', 0))");
				}
				if($check_existing != null) {
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_ORDER."` SET `order` = (`order` + 1) WHERE `category_id` IN('$category') AND `product_id` NOT IN('$product_id') AND `order` < '0'");
				}
			}
			$sql = "INSERT INTO ".WPSC_TABLE_ITEM_CATEGORY_ASSOC." (product_id, category_id) VALUES ".$new_product_category;
			$wpdb->query($sql);
		}
	
		
	
	
		//Inserting duplicated meta info
		$meta_values = $wpdb->get_results("SELECT `meta_key`, `meta_value`, `custom` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id='".$product_id."'", ARRAY_A);
		$new_meta_value = '';
		if (count($meta_values)>0){
			foreach($meta_values as $key => $meta) {
				$new_meta_value .= "('".$new_id."','".$meta['meta_key']."','".$meta['meta_value']."','".$meta['custom']."')";
			
				if (count($meta_values) != $key+1) {
					$new_meta_value .= ",";
				}
			}
			$sql = "INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (`product_id`, `meta_key`, `meta_value`, `custom`) VALUES ".$new_meta_value;
			$wpdb->query($sql);
		}
		
		
		
		//Inserting duplicated image info
		$image_values = $wpdb->get_results("SELECT `image`, `width`, `height`, `image_order`, `meta` FROM ".WPSC_TABLE_PRODUCT_IMAGES." WHERE product_id='".$product_id."'", ARRAY_A);
		$new_image_value = array();
		if (count($image_values)>0){
			foreach($image_values as $key => $image) {
			  if($image['image'] != '') {
			    if(is_numeric($image['width']) && is_numeric($image['height'])) {
			      $image['width'] = absint($image['width']);
			      $image['height'] = absint($image['height']);
			    } else {
			      $image['width'] = 'null';
			      $image['height'] = 'null';
			    }
			  
					$new_image_value[] = "('".$new_id."','".$image['image']."',".$image['width'].",".$image['height'].",'".$image['image_order']."','".$image['meta']."')";
				}
			}
			if(count($new_image_value) > 0) {
				$new_image_value = implode(",", $new_image_value);
				$sql = "INSERT INTO ".WPSC_TABLE_PRODUCT_IMAGES." (`product_id`, `image`, `width`, `height`, `image_order`, `meta`) VALUES ".$new_image_value;
				$wpdb->query($sql);
			}
		}
		
	  $duplicated = true;
	}	
	
	$sendback = wp_get_referer();
	if ( isset($duplicated) ) {
		$sendback = add_query_arg('duplicated', (int)$duplicated, $sendback);
	}
	wp_redirect($sendback);
	exit();
}

if ($_GET['wpsc_admin_action'] == 'duplicate_product') {
	add_action('admin_init', 'wpsc_duplicate_product');
}
 
 
 
function wpsc_purchase_log_csv() {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();
  if(($_GET['rss_key'] == 'key') && is_numeric($_GET['start_timestamp']) && is_numeric($_GET['end_timestamp']) && ($user_level >= 7)) {
  //exit('in use');
    $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `display_log` = '1';";
    $form_data = $wpdb->get_results($form_sql,ARRAY_A);
    
    $start_timestamp = $_GET['start_timestamp'];
    $end_timestamp = $_GET['end_timestamp'];
    $data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC",ARRAY_A);
         // exit('<pre>'.print_r($data, true).'</pre>');  
    header('Content-Type: text/csv');
    header('Content-Disposition: inline; filename="Purchase Log '.date("M-d-Y", $start_timestamp).' to '.date("M-d-Y", $end_timestamp).'.csv"');      
    
    foreach((array)$data as $purchase) {
      $country_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
      $country_data = $wpdb->get_results($country_sql,ARRAY_A);
      $country = $country_data[0]['value'];
   
      $output .= "\"".$purchase['totalprice'] ."\",";
                
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
        $skusql = "SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key`= 'sku' AND `product_id` = ".$item['prodid'];  
      //  exit($skusql);
        $skuvalue = $wpdb->get_var($skusql);  
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
        
      //  exit('<pre>'.print_r($item,true).'</pre>');
        $output .= "\"".$item['quantity']." ".$product['name'].$variation_list."\"";
        $output .= ",".$skuvalue;
			}
      $output .= "\n"; // terminates the row/line in the CSV file
		}
    echo $output;
    exit();
	}
}
 if($_REQUEST['wpsc_admin_action'] == 'wpsc_downloadcsv') {
	add_action('admin_init', 'wpsc_purchase_log_csv');
}


function wpsc_admin_ajax() {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();  
  if(is_numeric($_POST['catid'])) {
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
      		$completed_variation_values = $variation_processor->edit_product_values($product_id,$_POST['edit_var_val'], $selected_price);
      

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
					echo "edit_variation_combinations_html = \"".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_grid_view($product_id,  (array)$completed_variation_values)))."\";\n";

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
        		
						echo "add_variation_combinations_html = \"".TXT_WPSC_EDIT_VAR."<br />".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_add_grid_view((array)$variations_selected, (array)$completed_variation_values, $selected_price, $limited_stock)))."\";\n";

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
		$product_id = absint($_POST['product_id']);
    $timestamp = time();
		$new_main_image = (int)$images[0];
		
		$have_set_first_item = false;
		for($i=0;$i<count($images); ++$i ) {
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_IMAGES."` SET `image_order`='$i' WHERE `id`='".absint($images[$i])."' LIMIT 1");
			if($have_set_first_item === false) {
				$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image`='".absint($images[$i])."' WHERE `id`='{$product_id}' LIMIT 1");
				$have_set_first_item = true;
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
    
	if(isset($_POST['language_setting']) && ($_GET['page'] = WPSC_DIR_NAME.'/wpsc-admin/display-options.page.php')) {
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
		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/wpsc-admin/display-options.page.php");
		exit();
	}
	
	if ($_POST['gateway_submits']=='true'){
		require_once(WPSC_FILE_PATH."/gatewayoptions.php");
		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/wpsc-admin/display-options.page.php");
		exit();
	}
	
	if ($_POST['checkout_submits']=='true'){
		require_once(WPSC_FILE_PATH."/form_fields.php");
		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/wpsc-admin/display-options.page.php");
		exit();
	}
	
// 	if ($_POST['gold_submits']=='true'){
// 		require_once(WPSC_FILE_PATH.'/gold_cart_files/gold_options.php');
// 		wp_redirect($_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/wpsc-admin/display-options.page.php");
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
  $purchase_id = (int)$_GET['purchaselog_id'];
  include_once(WPSC_FILE_PATH."/admin-form-functions.php");
  // echo "testing";
	require_once(ABSPATH.'wp-admin/includes/media.php');
	wp_iframe('wpsc_packing_slip', $purchase_id);  
  //wpsc_packing_slip($purchase_id);
  exit();
}
 if($_REQUEST['wpsc_admin_action'] == 'wpsc_display_invoice') {
	add_action('admin_init', 'wpsc_display_invoice');
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


/**
 * Purchase log ajax code starts here
*/
function wpsc_purchlog_resend_email(){
	global $wpdb;
	
	$log_id = $_GET['email_buyer_id'];
	if(is_numeric($log_id)) {
	
		$selectsql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`= ".$log_id." LIMIT 1";

		$purchase_log = $wpdb->get_row($selectsql,ARRAY_A) ;
		
		if(($purchase_log['gateway'] == "testmode") && ($purchase_log['processed'] < 2))  {
			$message = get_option("wpsc_email_receipt");
			$message_html = "<h2  style='font-size:16px;font-weight:bold;color:#000;border:0px;padding-top: 0px;' >".TXT_WPSC_YOUR_ORDER."</h2>";
		} else {
			$message = get_option("wpsc_email_receipt");
			$message_html = $message;
		}
		
		$order_url = $siteurl."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php&amp;purchcaseid=".$purchase_log['id'];

		$cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase_log['id']."";
		$cart = $wpdb->get_results($cartsql,ARRAY_A);
		if($purchase_log['shipping_country'] != '') {
			$billing_country = $purchase_log['billing_country'];
			$shipping_country = $purchase_log['shipping_country'];
		} else {
			$country = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`=".$purchase_log['id']." AND `form_id` = '".get_option('country_form_field')."' LIMIT 1",ARRAY_A);
			$billing_country = $country[0]['value'];
			$shipping_country = $country[0]['value'];
		}
	
		$email_form_field = $wpdb->get_results("SELECT `id`,`type` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1",ARRAY_A);
		$email_address = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`=".$purchase_log['id']." AND `form_id` = '".$email_form_field[0]['id']."' LIMIT 1",ARRAY_A);
		$email = $email_address[0]['value'];
	
		$previous_download_ids = array(0); 
	
		if(($cart != null)) {
			foreach($cart as $row) {
				$link = "";
				$productsql= "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$row['prodid']."";
				$product_data = $wpdb->get_results($productsql,ARRAY_A) ;
				
				if($product_data[0]['file'] > 0) {
					if($purchase_log['email_sent'] != 1) {
						$wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `active`='1' WHERE `fileid`='".$product_data[0]['file']."' AND `purchid` = '".$purchase_log['id']."' LIMIT 1");
					}
					
					if (($purchase_log['processed'] >= 2)) {
						$download_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `fileid`='".$product_data[0]['file']."' AND `purchid`='".$purchase_log['id']."' AND (`cartid` = '".$row['id']."' OR `cartid` IS NULL) AND `id` NOT IN (".make_csv($previous_download_ids).") LIMIT 1",ARRAY_A);
						if($download_data != null) {
              if($download_data['uniqueid'] == null) {  // if the uniqueid is not equal to null, its "valid", regardless of what it is
                $link = $siteurl."?downloadid=".$download_data['id'];
              } else {
                $link = $siteurl."?downloadid=".$download_data['uniqueid'];
              }
						}
						$previous_download_ids[] = $download_data['id'];
						$order_status= 4;
					}
				}
				do_action('wpsc_confirm_checkout', $purchase_log['id']);
		
				$shipping = nzshpcrt_determine_item_shipping($row['prodid'], $row['quantity'], $shipping_country);
				if (isset($_SESSION['quote_shipping'])){
					$shipping = $_SESSION['quote_shipping'];
				}
				$total_shipping += $shipping;
		
				if($product_data[0]['special']==1) {
					$price_modifier = $product_data[0]['special_price'];
				} else {
					$price_modifier = 0;
				}
		
				$total+=($row['price']*$row['quantity']);
				$message_price = nzshpcrt_currency_display(($row['price']*$row['quantity']), $product_data[0]['notax'], true);

				$shipping_price = nzshpcrt_currency_display($shipping, 1, true);
				
				$variation_sql = "SELECT * FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id`='".$row['id']."'";
				$variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
				$variation_count = count($variation_data);
		
				if($variation_count > 1) {
					$variation_list = " (";
		
					if($purchase['gateway'] != 'testmode') {
						if($gateway['internalname'] == $purch_data[0]['gateway'] ) {
							$gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
							}
							$i = 0;
							foreach($variation_data as $variation) {
								if($i > 0) {
									$variation_list.= ", ";
								}
								
								$value_id = $variation['value_id'];
								$value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
								$variation_list.= $value_data[0]['name'];
								$i++;	
							}
							$variation_list .= ")";
						} else {
							if($variation_count == 1) {
								$value_id = $variation_data[0]['value_id'];
								$value_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
								$variation_list = " (".$value_data[0]['name'].")";
							} else {
								$variation_list = '';
							}
						}
			
						if($link != '') {
							$product_list.= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price ." ".TXT_WPSC_CLICKTODOWNLOAD.":\n $link\n";
							$product_list_html.= " - ". $product_data['name'] . stripslashes($variation_list) ."  ".$message_price ."&nbsp;&nbsp;<a href='$link'>".TXT_WPSC_CLICKTODOWNLOAD."</a>\n";
						} else {
							$plural = '';
							
							if($row['quantity'] > 1) {
								$plural = "s";
							  }
							$product_list.= " - ".$row['quantity']." ". $product_data[0]['name'].$variation_list ."  ". $message_price ."\n - ". TXT_WPSC_SHIPPING.":".$shipping_price ."\n\r";
							$product_list_html.= " - ".$row['quantity']." ". $product_data[0]['name'].$variation_list ."  ". $message_price ."\n - ". TXT_WPSC_SHIPPING.":".$shipping_price ."\n\r";
						}
						
						$report.= " - ". $product_data[0]['name'] .$variation_list."  ".$message_price ."\n";
				}
				
				if($purchase_log['discount_data'] != '') {
					$coupon_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` WHERE coupon_code='".$wpdb->escape($purchase_log['discount_data'])."' LIMIT 1",ARRAY_A);
					if($coupon_data['use-once'] == 1) {
						$wpdb->query("UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `active`='0', `is-used`='1' WHERE `id`='".$coupon_data['id']."' LIMIT 1");
					}
				}
				//$wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `active`='1' WHERE `fileid`='".$product_data[0]['file']."' AND `purchid` = '".$purchase_log['id']."' LIMIT 1");
				$total_shipping += $purchase_log['base_shipping'];

				$total = (($total+$total_shipping) - $purchase_log['discount_value']);
			// $message.= "\n\r";
			$product_list.= "Your Purchase No.: ".$purchase_log['id']."\n\r";
				if($purchase_log['discount_value'] > 0) {
					$discount_email.= TXT_WPSC_DISCOUNT.": ".nzshpcrt_currency_display($purchase_log['discount_value'], 1, true)."\n\r";
				}
				$total_shipping_email.= TXT_WPSC_TOTALSHIPPING.": ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
				$total_price_email.= TXT_WPSC_TOTAL.": ".nzshpcrt_currency_display($total,1,true)."\n\r";
				$product_list_html.= "Your Purchase No.: ".$purchase_log['id']."\n\n\r";
				if($purchase_log['discount_value'] > 0) {
					$discount_html.= TXT_WPSC_DISCOUNT.": ".nzshpcrt_currency_display($purchase_log['discount_value'], 1, true)."\n\r";
				}
				$total_shipping_html.= TXT_WPSC_TOTALSHIPPING.": ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
				$total_price_html.= TXT_WPSC_TOTAL.": ".nzshpcrt_currency_display($total, 1,true)."\n\r";
				if(isset($_GET['ti'])) {
					$message.= "\n\r".TXT_WPSC_YOURTRANSACTIONID.": " . $_GET['ti'];
					$message_html.= "\n\r".TXT_WPSC_YOURTRANSACTIONID.": " . $_GET['ti'];
					$report.= "\n\r".TXT_WPSC_TRANSACTIONID.": " . $_GET['ti'];
				} else {
					$report_id = "Purchase No.: ".$purchase_log['id']."\n\r";
				}
				
				
				
		$message = str_replace('%product_list%',$product_list,$message);
        $message = str_replace('%total_shipping%',$total_shipping_email,$message);
        $message = str_replace('%total_price%',$total_price_email,$message);
        //$message = str_replace('%order_status%',get_option('blogname'),$message);
        $message = str_replace('%shop_name%',get_option('blogname'),$message);
        
        $report = str_replace('%product_list%',$report_product_list,$report);
        $report = str_replace('%total_shipping%',$total_shipping_email,$report);
        $report = str_replace('%total_price%',$total_price_email,$report);
        $report = str_replace('%shop_name%',get_option('blogname'),$report);
        
        $message_html = str_replace('%product_list%',$product_list_html,$message_html);
        $message_html = str_replace('%total_shipping%',$total_shipping_html,$message_html);
        $message_html = str_replace('%total_price%',$total_price_email,$message_html);
        $message_html = str_replace('%shop_name%',get_option('blogname'),$message_html);
 
				
			//	exit($message_html);
				
				if(($email != '')) {
					if($purchase_log['processed'] < 2) {
						$payment_instructions = strip_tags(get_option('payment_instructions'));
						$message = TXT_WPSC_ORDER_PENDING . "\n\r" . $payment_instructions ."\n\r". $message;
						$resent = (bool)wp_mail($email, TXT_WPSC_ORDER_PENDING_PAYMENT_REQUIRED, $message, "From: ".get_option('return_email')."");
						$sent = 1;
					} else {
						$resent = (bool)wp_mail($email, TXT_WPSC_PURCHASERECEIPT, $message, "From: ".get_option('return_email')."");
						$sent = 1;
					}
				}
		}
	
}
	$sendback = wp_get_referer();

	if ( isset($sent) ) {
		$sendback = add_query_arg('sent', $sent, $sendback);
	}
	wp_redirect($sendback);
	exit();
}



if(isset($_REQUEST['email_buyer_id']) && is_numeric($_REQUEST['email_buyer_id'])) {
	add_action('admin_init', 'wpsc_purchlog_resend_email');
} 
function wpsc_purchlog_clear_download_items(){
	global $wpdb;
//exit('Just about to redirect');
	if(is_numeric($_GET['purchaselog_id'])) {
	  $purchase_id = (int)$_GET['purchaselog_id'];
	  $downloadable_items = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `purchid` IN ('$purchase_id')", ARRAY_A);
	  
	  $clear_locks_sql = "UPDATE`".WPSC_TABLE_DOWNLOAD_STATUS."` SET `ip_number` = '' WHERE `purchid` IN ('$purchase_id')";
	  $wpdb->query($clear_locks_sql);
	  $cleared =true;
	  
		$email_form_field = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1");
		$email_address = $wpdb->get_var("SELECT `value` FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id`='{$purchase_id}' AND `form_id` = '{$email_form_field}' LIMIT 1");
		
		foreach((array)$downloadable_items as $downloadable_item) {
		  $download_links .= $siteurl."?downloadid=".$downloadable_item['uniqueid']. "\n";
		}
		
		
		wp_mail($email_address, TXT_WPSC_USER_UNLOCKED_EMAIL, str_replace("[download_links]", $download_links, TXT_WPSC_USER_UNLOCKED_EMAIL_MESSAGE), "From: ".get_option('return_email')."");
	  

	$sendback = wp_get_referer();

	if ( isset($cleared) ) {
		$sendback = add_query_arg('cleared', $cleared, $sendback);
	}
	wp_redirect($sendback);
	exit();
	}

}
if($_REQUEST['wpsc_admin_action'] == 'clear_locks') {
	add_action('admin_init', 'wpsc_purchlog_clear_download_items');
}
 
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
		$sendback = remove_query_arg('purchaselog_id', $sendback);
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
 
 



/*
 * Get Shipping Form for wp-admin 
 */
function wpsc_get_shipping_form() {
  global $wpdb, $wpsc_shipping_modules;
//  exit('<pre>'.print_r($wpsc_shipping_modules, true).'</pre>');
  
  $shippingname = $_REQUEST['shippingname'];
  if(array_key_exists($shippingname, $wpsc_shipping_modules)){
	$output = $wpsc_shipping_modules[$shippingname]->getForm();
	exit($output);
  	
  }
  exit();
}
 
function wpsc_crop_thumbnail_html() {
  include(WPSC_FILE_PATH."/wpsc-admin/includes/crop.php"); 
  exit();
}
 
 
 
 	if ($_REQUEST['wpsc_admin_action'] == 'crop_image') {
		add_action('admin_init','wpsc_crop_thumbnail_html');
	}
 
 
 
 if($_REQUEST['wpsc_admin_action'] == 'get_shipping_form') {
	add_action('admin_init', 'wpsc_get_shipping_form');
}




//other actions are here
if($_GET['display_invoice']=='true') {
  add_action('admin_init', 'wpsc_display_invoice', 0);
}


add_action('admin_init','wpsc_shipping_options');
add_action('admin_init','wpsc_shipping_submits');
add_action('init','wpsc_swfupload_images');

 if($_REQUEST['wpsc_admin_action'] == 'edit_product') {
	add_action('admin_init', 'wpsc_admin_submit_product');
}
 

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