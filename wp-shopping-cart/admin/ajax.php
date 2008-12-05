<?php
function wpsc_purchase_log_csv() {
  global $wpdb;
  if(($_GET['purchase_log_csv'] == "true") && ($_GET['rss_key'] == 'key') && is_numeric($_GET['start_timestamp']) && is_numeric($_GET['end_timestamp'])) {
    $form_sql = "SELECT * FROM `".$wpdb->prefix."collect_data_forms` WHERE `active` = '1' AND `display_log` = '1';";
    $form_data = $wpdb->get_results($form_sql,ARRAY_A);
    
    $start_timestamp = $_GET['start_timestamp'];
    $end_timestamp = $_GET['end_timestamp'];
    $data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC",ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: inline; filename="Purchase Log '.date("M-d-Y", $start_timestamp).' to '.date("M-d-Y", $end_timestamp).'.csv"');      
    
    foreach($data as $purchase) {
      $country_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
      $country_data = $wpdb->get_results($country_sql,ARRAY_A);
      $country = $country_data[0]['value'];
           
      $output .= "\"".nzshpcrt_find_total_price($purchase['id'],$country) ."\",";
                
      foreach($form_data as $form_field) {
        $collected_data_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".$form_field['id']."' LIMIT 1";
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
      $stage_sql = "SELECT * FROM `".$wpdb->prefix."purchase_statuses` WHERE `id`='".$purchase['processed']."' AND `active`='1' LIMIT 1";
      $stage_data = $wpdb->get_results($stage_sql,ARRAY_A);
              
      $output .= "\"". $stage_data[0]['name'] ."\",";
      
      $output .= "\"". date("jS M Y",$purchase['date']) ."\"";
      
      $cartsql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`=".$purchase['id']."";
      $cart = $wpdb->get_results($cartsql,ARRAY_A) ; 
      //exit(nl2br(print_r($cart,true)));
      
      foreach($cart as $item) {
        $output .= ",";
        $product = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=".$item['prodid']." LIMIT 1",ARRAY_A);        
        $variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$item['id']."'";
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
              $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
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
  global $wpdb;
	if(is_numeric($_POST['prodid'])) {
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
		$selected_meta = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}wpsc_productmeta` WHERE `id` IN('{$meta_id}') ",ARRAY_A);
		if($selected_meta != null) {
			if($wpdb->query("DELETE FROM `{$wpdb->prefix}wpsc_productmeta` WHERE `id` IN('{$meta_id}')  LIMIT 1")) {
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
		
			$log_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `id` = '".$_POST['id']."' LIMIT 1",ARRAY_A);  
			if (($newvalue==2) && function_exists('wpsc_member_activate_subscriptions')){
				wpsc_member_activate_subscriptions($_POST['id']);
			}
			
			$update_sql = "UPDATE `".$wpdb->prefix."purchase_logs` SET `processed` = '".$newvalue."' WHERE `id` = '".$_POST['id']."' LIMIT 1";  
			$wpdb->query($update_sql);
			//echo("/*");
			if(($newvalue > $log_data['processed']) && ($log_data['processed'] < 2)) {
				transaction_results($log_data['sessionid'],false);
			}      
			//echo("*/");
			$stage_sql = "SELECT * FROM `".$wpdb->prefix."purchase_statuses` WHERE `id`='".$newvalue."' AND `active`='1' LIMIT 1";
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
      
      		// get all the currently associated variations from the database
      		$associated_variations = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}variation_associations` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}')", ARRAY_A);
      
      		$variations_still_associated = array();
      		foreach((array)$associated_variations as $associated_variation) {
			  	// remove variations not checked that are in the database
        		if(array_search($associated_variation['variation_id'], $variations_selected) === false) {
          			$wpdb->query("DELETE FROM `{$wpdb->prefix}variation_associations` WHERE `id` = '{$associated_variation['id']}' LIMIT 1");
          			$wpdb->query("DELETE FROM `{$wpdb->prefix}variation_values_associations` WHERE `product_id` = '{$product_id}' AND `variation_id` = '{$associated_variation['variation_id']}' ");
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
      		echo "edit_variation_combinations_html = \"".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_grid_view($product_id)))."\";\n";
    	} else {
      		if(count($variations_selected) > 0) {
        		// takes an array of variations, returns a form for adding data to those variations.
        		if((float)$_POST['selected_price'] > 0) {
          			$selected_price = (float)$_POST['selected_price'];
        		}
        		echo "add_variation_combinations_html = \"".TXT_WPSC_EDIT_VAR."<br />".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_add_grid_view((array)$variations_selected, $selected_price)))."\";\n";
      		} else {
        		echo "add_variation_combinations_html = \"\";\n";
      		}
		}
		exit();
	}
	
	
    
		if(isset($_POST['language_setting']) && ($_GET['page'] = WPSC_DIR_NAME.'/options.php')) {
		if($user_level >= 7) {
			update_option('language_setting', $_POST['language_setting']);
		}
	}
}


if($_GET['purchase_log_csv'] == "true") {
	add_action('admin_init', 'wpsc_purchase_log_csv');
}

if(($_REQUEST['ajax'] == "true") && ($_REQUEST['admin'] == "true")) {
	add_action('admin_init', 'wpsc_admin_ajax');
}
?>