<?php	

/**
 * wpsc_currency_display function.
 * 
 * @access public
 * @param mixed $price_in
 * @param mixed $args
 * @return void
 */
function wpsc_currency_display( $price_in, $args = null ) {
	global $wpdb, $wpsc_currency_data;
	$currency_code = '';
	$args = apply_filters( 'wpsc_toggle_display_currency_code', $args );
	$query = shortcode_atts( array(
		'display_currency_symbol' => true,
		'display_decimal_point'   => true,
		'display_currency_code'   => false,
		'display_as_html'         => true
	), $args );

	// No decimal point, no decimals
	if ( false == $query['display_decimal_point'] )
		$decimals = 0;
	else
		$decimals = 2; // default is 2

	// Format the price for output
	$price_out = number_format( (double)$price_in, $decimals, '.', ',' );

	// Get currency settings	
	$currency_type = get_option( 'currency_type' );

	// Load data if it is not set
	if ( count( $wpsc_currency_data ) < 3 )
		$wpsc_currency_data = $wpdb->get_row( "SELECT `symbol`, `symbol_html`, `code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id` = '" . $currency_type . "' LIMIT 1", ARRAY_A );

	// Figure out the currency code
	if ( true == $query['display_currency_code'] )
		$currency_code = $wpsc_currency_data['code'];

	// Figure out the currency sign
	if ( true == $query['display_currency_symbol'] ) {
		if ( !empty( $wpsc_currency_data['symbol'] ) ) {
			if ( false == $query['display_as_html'] ) {
				$currency_sign = $wpsc_currency_data['symbol_html'];
			} else {
				$currency_sign = $wpsc_currency_data['symbol'];
			}
		} else {
			$currency_sign = $wpsc_currency_data['code'];
			$currency_code = '';
		}
	}

	$currency_sign_location = get_option( 'currency_sign_location' );

	// Rejig the currency sign location
	switch ( $currency_sign_location ) {
		case 1:
			$format_string = '%3$s%1$s%2$s';
			break;
		
		case 2:
			$format_string = '%3$s %1$s%2$s';
			break;
		
		case 4:
			$format_string = '%1$s%2$s  %3$s';
			break;
		
		case 3:
		default:
			$format_string = '%1$s %2$s%3$s';
			break;
	}

	// Compile the output
	$output = sprintf( $format_string, $currency_code, $currency_sign, $price_out );

	if ( false == $query['display_as_html'] ) {
		$output = "".$output."";
	} else {
		$output = "<span class='pricedisplay'>".$output."</span>";
	}

	// Return results
	return apply_filters( 'wpsc_currency_display', $output );
}

/**
	* wpsc_decrement_claimed_stock method 
	*
	* @param float a price
	* @return string a price with a currency sign
*/
function wpsc_decrement_claimed_stock($purchase_log_id) {
  global $wpdb;
  $all_claimed_stock = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` IN('%s') AND `cart_submitted` IN('1')", $purchase_log_id), ARRAY_A);
	
	foreach((array)$all_claimed_stock as $claimed_stock) {
		// for people to have claimed stock, it must have been available to take, no need to look at the existing stock, just subtract from it
		// If this is ever wrong, and you get negative stock, do not fix it here, go find the real cause of the problem 		if($claimed_stock['variation_stock_id'] > 0) {
		$product_id = absint($claimed_stock['product_id']);	
	
		$product = get_post($product_id);
		$current_stock = get_post_meta($product_id, '_wpsc_stock', true);
		$remaining_stock = $current_stock - $claimed_stock['stock_claimed'];
		update_post_meta($product_id, '_wpsc_stock', $remaining_stock);
		
		$remaining_stock = $wpdb->get_row($sql_query, ARRAY_A);
		if($remaining_stock == 0 && get_product_meta($product_id,'unpublish_oos',true) == 1){
			wp_mail(get_option('admin_email'), $product->post_title . __(' is out of stock', 'wpsc'), __('Remaining stock of ', 'wpsc') . $product->post_title . __(' is 0. Product was unpublished.', 'wpsc'));
			$wpdb->query("UPDATE `".$wpdb->posts."` SET `post_status` = 'draft' WHERE `ID` = '{$product_id}'");
		}
	}
	$wpdb->query($wpdb->prepare("DELETE FROM `".WPSC_TABLE_CLAIMED_STOCK."` WHERE `cart_id` IN ('%s')", $purchase_log_id));
}
  
/**
 *	wpsc_get_currency_symbol
 *	@param does not receive anything
 *  @return returns the currency symbol used for the shop
*/  
function wpsc_get_currency_symbol(){
	global $wpdb;
	$currency_type = get_option('currency_type');
	$wpsc_currency_data = $wpdb->get_var("SELECT `symbol` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$currency_type."' LIMIT 1") ;
	return  $wpsc_currency_data;
}  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
/**
* All the code below here needs commenting and looking at to see if it needs to be altered or disposed of.
* Correspondingly, all the code above here has been commented, uses the wpsc prefix, and has been made for or modified to work with the object oriented cart code.
*/

	
function nzshpcrt_determine_item_shipping($product_id, $quantity, $country_code) {
	global $wpdb;
	if(is_numeric($product_id) && (get_option('do_not_use_shipping') != 1) && ($_SESSION['quote_shipping_method'] == 'flatrate')) {
		$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='$product_id' LIMIT 1";
		$product_list = $wpdb->get_row($sql,ARRAY_A) ;
		if($product_list['no_shipping'] == 0) {
			//if the item has shipping
			if($country_code == get_option('base_country')) {
				$additional_shipping = $product_list['pnp'];
			} else {
				$additional_shipping = $product_list['international_pnp'];
			}
			$shipping = $quantity * $additional_shipping;
		} else {
			//if the item does not have shipping
			$shipping = 0;
		}
	} else {
		//if the item is invalid or all items do not have shipping
		$shipping = 0;
	}
	return $shipping;
}
	
  function nzshpcrt_determine_base_shipping($per_item_shipping, $country_code) {    
    global $wpdb, $wpsc_shipping_modules;
		$custom_shipping = get_option('custom_shipping_options');
    if((get_option('do_not_use_shipping') != 1) && (count($custom_shipping) > 0)) {
			if(array_search($_SESSION['quote_shipping_method'], (array)$custom_shipping) === false) {
			  //unset($_SESSION['quote_shipping_method']);
			}
			
			$shipping_quotes = null;
			if($_SESSION['quote_shipping_method'] != null) {
				// use the selected shipping module
			  $shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
			} else {
			  // otherwise select the first one with any quotes
				foreach((array)$custom_shipping as $shipping_module) {
					// if the shipping module does not require a weight, or requires one and the weight is larger than zero
					if(($custom_shipping[$shipping_module]->requires_weight != true) or (($custom_shipping[$shipping_module]->requires_weight == true) and (shopping_cart_total_weight() > 0))) {
						$_SESSION['quote_shipping_method'] = $shipping_module;
						$shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
						if(count($shipping_quotes) > 0) { // if we have any shipping quotes, break the loop.
							break;
						}
					}
				}
			}
			
			//echo "<pre>".print_r($_SESSION['quote_shipping_method'],true)."</pre>";
			if(count($shipping_quotes) < 1) {
			$_SESSION['quote_shipping_option'] = '';
			}
			if(($_SESSION['quote_shipping_option'] == null) && ($shipping_quotes != null)) {
				$_SESSION['quote_shipping_option'] = array_pop(array_keys(array_slice($shipping_quotes,0,1)));
			}
			foreach((array)$shipping_quotes as $shipping_quote) {
				foreach((array)$shipping_quote as $key=>$quote) {
					if($key == $_SESSION['quote_shipping_option']) {
					  $shipping = $quote;
					}
				}
			}
		} else {
      $shipping = 0;
		}
    return $shipping;
	}
  
function admin_display_total_price($start_timestamp = '', $end_timestamp = '') {
  global $wpdb;
  if(($start_timestamp != '') && ($end_timestamp != '')) {
    $sql = "SELECT SUM(`totalprice`) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN (2,3,4) AND `date` BETWEEN '$start_timestamp' AND '$end_timestamp'";
	} else {
		$sql = "SELECT SUM(`totalprice`) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN (2,3,4) AND `date` != ''";
	}
  $total = $wpdb->get_var($sql);
  return $total;
}
  

  
function check_in_stock($product_id, $variations, $item_quantity = 1) {
  global $wpdb;
  $product_id = (int)$product_id;
  $item_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1",ARRAY_A);
  
  $item_stock = null;
  $variation_count = count($variations);
  if($variation_count > 0) {
    foreach($variations as $variation_id) {
      if(is_numeric($variation_id)) {
        $variation_ids[] = $variation_id;
			}
		}
    if(count($variation_ids) > 0) {
      
			$actual_variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$variation_ids)."')");
			asort($actual_variation_ids);
			$all_variation_ids = implode(",", $actual_variation_ids);
    
      $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product_id}' AND `value_id` IN ( '".implode("', '",$variation_ids )."' ) AND `all_variation_ids` IN('$all_variation_ids') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variation_ids)."' LIMIT 1");
      
      $variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
      
      $item_stock = $variation_stock_data['stock'];
		}
	}
    
  if($item_stock === null) {
    $item_stock = $item_data['quantity'];
	}
  
  if((($item_data['quantity_limited'] == 1) && ($item_stock > 0) && ($item_stock >= $item_quantity)) || ($item_data['quantity_limited'] == 0))  {
    $output = true;
	} else {
		$output = false;
	}
  return $output;
}
 
  
  
function wpsc_item_process_image($id, $input_file, $output_filename, $width = 0, $height = 0, $resize_method = 1, $return_imageid = false) {
//  the function for processing images, takes a product_id, input_file outout file name, height and width
	global $wpdb;
	//$_FILES['image']['tmp_name']
	//$_FILES['image']['name']
	if(preg_match("/\.(gif|jp(e)*g|png){1}$/i",$output_filename) && apply_filters( 'wpsc_filter_file', $input_file )) {
		//$active_signup = apply_filters( 'wpsc_filter_file', $_FILES['image']['tmp_name'] );
		if(function_exists("getimagesize")) {
			$image_name = basename($output_filename);
			if(is_file((WPSC_IMAGE_DIR.$image_name))) {
				$name_parts = explode('.',basename($image_name));
				$extension = array_pop($name_parts);
				$name_base = implode('.',$name_parts);
				$dir = glob(WPSC_IMAGE_DIR."$name_base*");
				
				foreach($dir as $file) {
					$matching_files[] = basename($file);
				}
				$image_name = null;
				$num = 2;
				//  loop till we find a free file name, first time I get to do a do loop in yonks
				do {
					$test_name = "{$name_base}-{$num}.{$extension}";
					if(!file_exists(WPSC_IMAGE_DIR.$test_name)) {
						$image_name = $test_name;
					}
					$num++;
				} while ($image_name == null);
			}			
			
			//exit("<pre>".print_r($image_name,true)."</pre>");
			
			$new_image_path = WPSC_IMAGE_DIR.$image_name;
			
			// sometimes rename doesn't work, if the file is recently uploaded, use move_uploaded_file instead
			if(is_uploaded_file($input_file)) {
				move_uploaded_file($input_file, $new_image_path);
			} else {
				rename($input_file, $new_image_path);
			}
			$stat = stat( dirname( $new_image_path ));
			$perms = $stat['mode'] & 0000775;
			@ chmod( $new_image_path, $perms );
			
			switch($resize_method) {
				case 2:
				if($height < 1) {
					$height = get_option('product_image_height');
				}
				if($width < 1) {
					$width  = get_option('product_image_width');
				}
				break;


				case 0:
				$height = (int)null;
				$width  = (int)null;
				break;

				case 1:
				default:
				$height = (int)get_option('product_image_height');
				$width  = (int)get_option('product_image_width');
				break;
			}
					if($width < 1) {
						$width = 96;
					}
					if($height < 1) {
						$height = 96;
					}	     
				image_processing($new_image_path, (WPSC_THUMBNAIL_DIR.$image_name), $width, $height);
// 			}
			$sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_IMAGES."` (`product_id`, `image`, `width`, `height`) VALUES ('{$id}', '{$image_name}', '{$width}', '{$height}' )";
			$wpdb->query($sql);
			$image_id = (int) $wpdb->insert_id;			
			$updatelink_sql = "UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$image_id."', `thumbnail_image` = '".$thumbnail_image."'  WHERE `id` = '$id'";
			$wpdb->query($updatelink_sql);
			//exit($sql.'<br />image is about to be stored in the DB<br />'.$updatelink_sql);

			if(function_exists('getimagesize')) {
				$imagetype = getimagesize(WPSC_THUMBNAIL_DIR.$image_name);
				update_product_meta($id, 'thumbnail_width', $imagetype[0]);
				update_product_meta($id, 'thumbnail_height', $imagetype[1]);
			}
			
			
			$image = $wpdb->escape($image_name);
		} else {
			$image_name = basename($output_filename);
			if(is_file((WPSC_IMAGE_DIR.$image_name))) {
				$name_parts = explode('.',basename($image_name));
				$extension = array_pop($name_parts);
				$name_base = implode('.',$name_parts);
				$dir = glob(WPSC_IMAGE_DIR."$name_base*");
				
				foreach($dir as $file) {
					$matching_files[] = basename($file);
				}
				$image_name = null;
				$num = 2;
				//  loop till we find a free file name
				do {
					$test_name = "{$name_base}-{$num}.{$extension}";
					if(!file_exists(WPSC_IMAGE_DIR.$test_name)) {
						$image_name = $test_name;
					}
					$num++;
				} while ($image_name == null);
			}
			$new_image_path = WPSC_IMAGE_DIR.$image_name;
			move_uploaded_file($input_file, $new_image_path);
			$stat = stat( dirname( $new_image_path ));
			$perms = $stat['mode'] & 0000775;
			@ chmod( $new_image_path, $perms );
			$image = $wpdb->escape($image_name);
		}
	} else {
			$image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".(int)$id."' LIMIT 1",ARRAY_A);
		  $image = false;
	}
	if($return_imageid == true) {
		return array('image_id' => $image_id, 'filename' => $image);
	} else {
		return $image;
  }
}



function wpsc_get_mimetype($file, $check_reliability = false) {
  // Sometimes we need to know how useless the result from this is, hence the "check_reliability" parameter
	if(file_exists($file)) {
		$mimetype_data = wp_check_filetype($file);
		$mimetype = $mimetype_data['type'];
		$is_reliable = true;
	} else {
		$mimetype = false;
		$is_reliable = false;
	}
	if($check_reliability == true) {
		return array('mime_type' => $mimetype, 'is_reliable' => $is_reliable );
	} else {
		return $mimetype;
	}
}


function shopping_cart_total_weight() {
	global $wpdb;
	$cart = $_SESSION['nzshpcrt_cart'];
	$total_weight=0;
	foreach((array)$cart as $item) {
	  $weight = array();
		if(($weight == null) || ($weight['weight'] == null) && ($weight['weight_unit'] == null)) {
			$weight=$wpdb->get_row("SELECT `weight`, `weight_unit` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE id='{$item->product_id}'", ARRAY_A);
		}
		$weight = $item->product_meta['weight'];
		
		$sub_weight = $weight*$item->quantity;
		$total_weight += $sub_weight;
	}
	return $total_weight;
}

function wpsc_convert_weights($weight, $unit) {
	if (is_array($weight)) {
		$weight = $weight['weight'];
	}
	switch($unit) {		
		case "kilogram":
		$weight = $weight / 1000;
		break;
		
		case "gram":
		$weight = $weight;
		break;
	
		case "once":
		case "ounce":
		$weight = ($weight / 453.59237) * 16;
		break;
		
		case "pound":
		default:
		$weight = $weight / 453.59237;
		break;
	}
	return $weight;
}



function wpsc_convert_weight($in_weight, $in_unit, $out_unit = 'gram') {
	if (isset($weight) && is_array($weight)) {
		$weight = $weight['weight'];
	}
	switch($in_unit) {
		case "kilogram":
		$intermediate_weight = $in_weight * 1000;
		break;
		
		case "gram":
		$intermediate_weight = $in_weight;
		break;
	
		case "once":
		case "ounce":
		$intermediate_weight = ($in_weight / 16) * 453.59237;
		break;
		
		case "pound":
		default:
		$intermediate_weight = $in_weight * 453.59237;
		break;
	}
	
	switch($out_unit) {
		case "kilogram":
		$weight = $intermediate_weight / 1000;
		break;
		
		case "gram":
		$weight = $intermediate_weight;
		break;
	
		case "once":
		case "ounce":
		$weight = ($intermediate_weight / 453.59237) * 16;
		break;
		
		case "pound":
		default:
		$weight = $intermediate_weight / 453.59237;
		break;
	}
	return round($weight, 2);
}


function wpsc_ping() {
	$services = get_option('ping_sites');
	$services = explode("\n", $services);
	foreach ( (array) $services as $service ) {
		$service = trim($service);
		if($service != '' ) {
			wpsc_send_ping($service);
		}
	}
}

function wpsc_send_ping($server) {
	global $wp_version;
	$path = "";
	include_once(ABSPATH . WPINC . '/class-IXR.php');

	// using a timeout of 3 seconds should be enough to cover slow servers
	$client = new IXR_Client($server, ((!strlen(trim($path)) || ('/' == $path)) ? false : $path));
	$client->timeout = 3;
	$client->useragent .= ' -- WordPress/'.$wp_version;

	// when set to true, this outputs debug messages by itself
	$client->debug = false;
	$home = trailingslashit( get_option('product_list_url') );
	$rss_url = get_option('siteurl')."/index.php?rss=true&amp;action=product_list";
	if ( !$client->query('weblogUpdates.extendedPing', get_option('blogname'), $home, $rss_url ) ) {
		$client->query('weblogUpdates.ping', get_option('blogname'), $home);
	}
}


function wpsc_sanitise_keys($value) {
  /// Function used to cast array items to integer.
  return (int)$value;
}



/*
 * this function checks every product on the products page to see if it has any stock remaining
 * it is executed through the wpsc_product_alert filter
 */
function wpsc_check_stock($state, $product) {
	global $wpdb;
	// if quantity is enabled and is zero
	$out_of_stock = false;
	$product_meta = get_product_meta($product->ID, 'product_metadata',true);
	$stock_count = get_product_meta($product->ID, 'stock',true);
	// only do anything if the quantity is limited.
	if(($stock_count === '0')) // otherwise, use the stock from the products list table
		$out_of_stock = true;

	if($out_of_stock === true) {
		$state['state'] = true;
		$state['messages'][] = __('This product has no available stock', 'wpsc');
	}
	
	return array('state' => $state['state'], 'messages' => $state['messages']);
}


/*
 * if UPS is on, this function checks every product on the products page to see if it has a weight
 * it is executed through the wpsc_product_alert filter
 */
function wpsc_check_weight($state, $product) {
	global $wpdb;
	$custom_shipping = (array)get_option('custom_shipping_options');
	$has_no_weight = false;
	$shipping_modules = array();
	$product_meta = get_product_meta($product->ID, 'product_metadata',true);
	// only do anything if UPS is on and shipping is used
	if(array_search('ups', $custom_shipping) !== false)
		$shipping_modules[] = 'UPS';
	if(array_search('weightrate', $custom_shipping) !== false)
		$shipping_modules[] = 'Weight Rate';
	if(array_search('usps', $custom_shipping) !== false)
		$shipping_modules[] = 'Weight Rate';
	
	
	if($product_meta['no_shipping'] != 1 && !empty($shipping_modules)) {
		if($product_meta['weight'] == 0) // otherwise, use the weight from the products list table
			$has_no_weight = true;
		
		if($has_no_weight === true) {
			$state['state'] = true;
			$state['messages'][] = implode(',',$shipping_modules). __(' does not support products without a weight set. Please either disable shipping for this product or give it a weight', 'wpsc');
		}
	}
	return array('state' => $state['state'], 'messages' => $state['messages']);
}

add_filter('wpsc_product_alert', 'wpsc_check_stock', 10, 2);
add_filter('wpsc_product_alert', 'wpsc_check_weight', 10, 2);



/**
 * WPSC Image Quality
 *
 * Returns the value to use for image quality when creating jpeg images.
 * By default the quality is set to 75%. It is then run through the main jpeg_quality WordPress filter
 * to add compatibility with other plugins that customise image quality.
 *
 * It is then run through the wpsc_jpeg_quality filter so that it is possible to override
 * the quality setting just for WPSC images.
 *
 * @since 3.7.6
 *
 * @param (int) $quality Optional. Image quality when creating jpeg images.
 * @return (int) The image quality.
 */
function wpsc_image_quality( $quality = 75 ) {
	
	$quality = apply_filters( 'jpeg_quality', $quality );
	return apply_filters( 'wpsc_jpeg_quality', $quality );
	
}



?>
