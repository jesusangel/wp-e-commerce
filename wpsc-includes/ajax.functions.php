<?php
/**
 * WP eCommerce AJAX functions
 *
 * These are the WPSC AJAX functions
 *
 * @package wp-e-commerce
 * @since 3.7
 * @subpackage wpsc-cart-classes 
 */
 
 
/**
	* add_to_cart function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_add_to_cart() {
  global $wpdb, $wpsc_cart;
  /// default values
	$default_parameters['variation_values'] = null;
	$default_parameters['quantity'] = 1;
	$default_parameters['provided_price'] = null;
	$default_parameters['comment'] =null;
	$default_parameters['time_requested']= null;
	$default_parameters['custom_message'] = null;
	$default_parameters['file_data'] = null;
	$default_parameters['is_customisable'] = false;
	$default_parameters['meta'] = null;
  
  
  /// sanitise submitted values
  $product_id = (int)$_POST['product_id'];
  foreach((array)$_POST['variation'] as $key => $variation) {
    $provided_parameters['variation_values'][(int)$key] = (int)$variation;
  }
  if($_POST['quantity'] > 0) {
		$provided_parameters['quantity'] = (int)$_POST['quantity'];
  }
  if($_POST['is_customisable'] == 'true') {
		$provided_parameters['is_customisable'] = true;
		
		if(isset($_POST['custom_text'])) {
			$provided_parameters['custom_message'] = $_POST['custom_text'];
		}
		if(isset($_FILES['custom_file'])) {
			$provided_parameters['file_data'] = $_FILES['custom_file'];
		}
	}
  
  $parameters = array_merge($default_parameters, (array)$provided_parameters);
  
	$state = $wpsc_cart->set_item($product_id,$parameters); 
  if($_GET['ajax'] == 'true') {
		if(($product_id != null) &&(get_option('fancy_notifications') == 1)) {
			echo "if(jQuery('#fancy_notification_content')) {\n\r";
			echo "  jQuery('#fancy_notification_content').html(\"".str_replace(Array("\n","\r") , Array('\n','\r'),addslashes(fancy_notification_content($product_id, (!$state)))). "\");\n\r";
			echo "  jQuery('#loading_animation').css('display', 'none');\n\r";
			echo "  jQuery('#fancy_notification_content').css('display', 'block');\n\r";
			echo "}\n\r";
		}
		ob_start();
		include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/cart_widget.php");
	  $output = ob_get_contents();
		ob_end_clean();
		//exit("/*<pre>".print_r($wpsc_cart,true)."</pre>*/");
		$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
		
    echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";
    

    
    
    
    echo "wpsc_bind_to_events();\n";
		exit();
  }
}
// execute on POST and GET
if($_REQUEST['wpsc_ajax_action'] == 'add_to_cart') {
	add_action('init', 'wpsc_add_to_cart');
}



/**
	* empty cart function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_empty_cart() {
  global $wpdb, $wpsc_cart;
  $wpsc_cart->empty_cart();
  
  if($_REQUEST['ajax'] == 'true') {
		ob_start();
    if(get_option('wpsc_use_theme_engine') == TRUE) {	    
			include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/cart_widget.php");
	  } else {
			nzshpcrt_shopping_basket("", 4);
	  }
		$output = ob_get_contents();
		ob_end_clean();
		$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
    echo "jQuery('div.shopping-cart-wrapper').html('$output');";
		exit();
  }
}
// execute on POST and GET
if(($_REQUEST['wpsc_ajax_action'] == 'empty_cart') || ($_GET['sessionid'] > 0)) {
	add_action('init', 'wpsc_empty_cart');
}


/**
	* coupons price, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_coupon_price() {
  global $wpdb, $wpsc_cart, $wpsc_coupons;
  $coupon = $wpdb->escape($_POST['coupon_num']);
  $wpsc_coupons = new wpsc_coupons($coupon);
  if($wpsc_coupons->validate_coupon()){
  	$discountAmount = $wpsc_coupons->calculate_discount();
  	$wpsc_cart->apply_coupons($discountAmount, $coupon);
  }else{
  	echo 'coupon is not valid';
  	$wpsc_cart->coupons_amount = 0;
  }
	
 }

// execute on POST and GET
if(isset($_POST['coupon_num'])) {
	add_action('init', 'wpsc_coupon_price');
}


/**
	* update quantity function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_update_item_quantity() {
  global $wpdb, $wpsc_cart;
  if(is_numeric($_POST['key'])) {
    $key = (int)$_POST['key'];
		if($_POST['quantity'] > 0) {
		  // if the quantity is greater than 0, update the item;
		  $parameters['quantity'] = (int)$_POST['quantity'];
			$wpsc_cart->edit_item($key, $parameters);
		} else {
		  // if the quantity is 0, remove the item.
			$wpsc_cart->remove_item($key);
		}
  }
}
  
// execute on POST and GET
if($_REQUEST['wpsc_update_quantity'] == 'true') {
	add_action('init', 'wpsc_update_item_quantity');
}


/**
	* update_shipping_price function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_update_shipping_price() {
  global $wpdb, $wpsc_cart;
 	$quote_shipping_method = $_POST['key1'];
 	$quote_shipping_option = $_POST['key'];
	$wpsc_cart->update_shipping($quote_shipping_method, $quote_shipping_option);
	
	echo "jQuery('.pricedisplay.checkout-shipping').html('".wpsc_cart_shipping()."');\n\r";
	echo "jQuery('.pricedisplay.checkout-total').html('".wpsc_cart_total()."');\n\r";
	exit();
}
// execute on POST and GET
if($_REQUEST['wpsc_ajax_action'] == 'update_shipping_price') {
	add_action('init', 'wpsc_update_shipping_price');
}


/**
	* update_shipping_price function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_get_rating_count() {
  global $wpdb, $wpsc_cart;
  $prodid = $_POST['product_id'];
	$data = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."product_rating` WHERE `productid` = '".$prodid."'",ARRAY_A) ;
	echo $data[0]['count'].",".$prodid;
	exit();
}
// execute on POST and GET
if(($_REQUEST['get_rating_count'] == 'true') && is_numeric($_POST['product_id'])) {
	add_action('init', 'wpsc_get_rating_count');
}


/**
	* update quantity function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_update_location() {
  global $wpdb, $wpsc_cart;

	if($_POST['country'] != null) {
		$_SESSION['wpsc_delivery_country'] = $_POST['country'];
		if($_SESSION['wpsc_selected_country'] == null) {
			$_SESSION['wpsc_selected_country'] = $_POST['country'];
		}
		if($_POST['region'] != null) {
			$_SESSION['wpsc_delivery_region'] = $_POST['region'];
			if($_SESSION['wpsc_selected_region'] == null) {
				$_SESSION['wpsc_selected_region'] = $_POST['region'];
			}
		} else if($_SESSION['wpsc_selected_region'] == '') {
			$_SESSION['wpsc_delivery_region'] = get_option('base_region');
			$_SESSION['wpsc_selected_region'] = get_option('base_region');
		}
		
		
		if($_SESSION['wpsc_delivery_region'] == '') {
			$_SESSION['wpsc_delivery_region'] = $_SESSION['selected_region'];
		}
	}
	
	if($_POST['zipcode'] == '') {
		$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
	}
	
	$wpsc_cart->update_location();
	$wpsc_cart->get_shipping_method();
	$wpsc_cart->get_shipping_option();
	
	
	if($_GET['ajax'] == 'true') {
		exit();
	}
}
  
// execute on POST and GET
if($_REQUEST['wpsc_ajax_actions'] == 'update_location') {
	add_action('init', 'wpsc_update_location');
}


/**
	* submit checkout function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_submit_checkout() {
  global $wpdb, $wpsc_cart, $user_ID,$nzshpcrt_gateways;
	$wpsc_checkout = new wpsc_checkout();
	//exit('coupons:'.$wpsc_cart->coupons_name);
	$selected_gateways = get_option('custom_gateway_options');
	$submitted_gateway = $_POST['custom_gateway'];
	$form_validity = $wpsc_checkout->validate_forms();
//	exit('<pre>'.print_r($form_validity, true).'</pre>');
	extract($form_validity); // extracts $is_valid and $error_messages
	//exit("<pre>".print_r($submitted_gateway,true)."</pre>");
	
	
	$selectedCountry = $_SESSION['wpsc_delivery_country'];
	$sql="SELECT id, country FROM `{$wpdb->prefix}currency_list` WHERE isocode='".$selectedCountry."'";
	$selectedCountry = $wpdb->get_results($sql, ARRAY_A);


   foreach($wpsc_cart->cart_items as $cartitem){
   //	exit('<pre>'.print_r($cartitem, true).'</pre>');
   		$categoriesIDs = $wpdb->get_col("SELECT category_id FROM `{$wpdb->prefix}item_category_associations` WHERE product_id=".$cartitem->product_id);
   		foreach((array)$categoriesIDs as $catid){
   			$sql ="SELECT countryid FROM `{$wpdb->prefix}wpsc_category_tm` WHERE visible=0 AND categoryid=".$catid;
   			//exit($sql);
   			$countries = $wpdb->get_col($sql);
   			//exit(print_r($selectedCountry).'<br />');
   			//echo print_r($countries, true);
   			if(in_array($selectedCountry[0]['id'], (array)$countries)){
   					$errormessage =sprintf(TXT_WPSC_CATEGORY_TARGETMARKET, $cartitem->product_name, $selectedCountry[0]['country']);
   					 /*
TXT_WPSC_CATEGORY_TARGETMARKET;
   					"Oops the product : ".$cartitem->product_name." cannot be shipped to ".$selectedCountry[0]['country']." to continue with your transaction please remove this product.";
*/
   			//	exit($errormessage);
	   				$_SESSION['categoryAndShippingCountryConflict']= $errormessage;
					$is_valid = false;
   				}
   			//   				exit( $sql.'<br /><pre>'.print_r($countries, true).'</pre><br />');
   		
   
   		}
    }
  
	//exit('<pre>'.print_r($categoriesIDs, true).'</pre>');
	if($is_valid == true) {
	$_SESSION['categoryAndShippingCountryConflict']= '';
		// check that the submitted gateway is in the list of selected ones
		if(array_search($submitted_gateway,$selected_gateways) !== false) {
		
  
		
			$sessionid = (mt_rand(100,999).time());
			$subtotal = $wpsc_cart->calculate_subtotal();
			$base_shipping= $wpsc_cart->calculate_base_shipping();
			$tax = $wpsc_cart->calculate_total_tax();
			$total = $wpsc_cart->calculate_total_price();
			
			    

			$wpdb->query("INSERT INTO `{$wpdb->prefix}purchase_logs` (`totalprice`,`statusno`, `sessionid`, `user_ID`, `date`, `gateway`, `billing_country`,`shipping_country`, `base_shipping`,`shipping_method`, `shipping_option`, `plugin_version`, `discount_value`, `discount_data`) VALUES ('$total' ,'0', '{$sessionid}', '".(int)$user_ID."', UNIX_TIMESTAMP(), '{$submitted_gateway}', '{$wpsc_cart->delivery_country}', '{$wpsc_cart->selected_country}', '{$base_shipping}', '{$wpsc_cart->selected_shipping_method}', '{$wpsc_cart->selected_shipping_option}', '".WPSC_VERSION."', '{$wpsc_cart->coupons_amount}','{$wpsc_cart->coupons_name}')");
			
			
			$purchase_log_id = $wpdb->get_var("SELECT `id` FROM `{$wpdb->prefix}purchase_logs` WHERE `sessionid` IN('{$sessionid}') LIMIT 1") ;
			//$purchase_log_id = 1;
			$wpsc_checkout->save_forms_to_db($purchase_log_id);
			$wpsc_cart->save_to_db($purchase_log_id);
			$wpsc_cart->submit_stock_claims($purchase_log_id);
			
			
			if(get_option('permalink_structure') != '') {
				$seperator = "?";
			} else {
				$seperator = "&";
			}
			// submit to gateway
			foreach($nzshpcrt_gateways as $gateway) {
        if($gateway['internalname'] == $submitted_gateway ) {
          $gateway_used = $gateway['internalname'];
          $wpdb->query("UPDATE `".$wpdb->prefix."purchase_logs` SET `gateway` = '".$gateway_used."' WHERE `id` = '".$log_id."' LIMIT 1 ;");
          $gateway['function']($seperator, $sessionid);
          break;
        }
      }
			exit('');
		}
	} else {
	
	}
}

// execute on POST and GET
if($_REQUEST['wpsc_action'] == 'submit_checkout') {
	add_action('init', 'wpsc_submit_checkout');
}

?>