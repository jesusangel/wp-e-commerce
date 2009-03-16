<?php
/**
 * WP eCommerce Cart and Cart Item classes
 *
 * These are the classes for the WP eCommerce Cart and Cart Items,
 * The Cart class handles adding, removing and adjusting items in the cart, and totaling up the cost of the items in the cart.
 * The Cart Items class handles the same, but for cart items themselves.
 *
 *
 * @package wp-e-commerce
 * @subpackage wpsc-cart-classes 
*/
/**
 * The WPSC Cart API for templates
 */


/**
* cart item count function, no parameters
* * @return integer the item count
*/
function wpsc_cart_item_count() {
	global $wpsc_cart;
	return count($wpsc_cart->cart_items);
}

/**
* cart total function, no parameters
* @return string the total price of the cart, with a currency sign
*/
function wpsc_cart_total() {
	global $wpsc_cart;
	return $wpsc_cart->process_as_currency($wpsc_cart->calculate_total_price());
}
 
/**
* have cart items function, no parameters
* @return boolean true if there are cart items left
*/
function wpsc_have_cart_items() {
	global $wpsc_cart;
	return $wpsc_cart->have_cart_items();
}

function wpsc_the_cart_item() {
	global $wpsc_cart;
	return $wpsc_cart->the_cart_item();
}
 
 
 
/**
* cart item key function, no parameters
* @return integer the cart item key from the array in the cart object
*/
function wpsc_the_cart_item_key() {
	global $wpsc_cart;
	return $wpsc_cart->current_cart_item;
}
 
 //$this->
 /**
* cart item name function, no parameters
* @return string the cart item name
*/
function wpsc_cart_item_name() {
	global $wpsc_cart;
	return htmlentities(stripslashes($wpsc_cart->cart_item->product_name), ENT_QUOTES);
}
 
 /**
* cart item quantity function, no parameters
* @return string the selected quantity of items
*/
function wpsc_cart_item_quantity() {
	global $wpsc_cart;
	return $wpsc_cart->cart_item->quantity;
}

/**
* cart item price function, no parameters
* @return string the cart item price multiplied by the quantity, with a currency sign
*/
function wpsc_cart_item_price() {
	global $wpsc_cart;
	return $wpsc_cart->process_as_currency($wpsc_cart->cart_item->total_price);
}


/**
* cart item image function
* returns the url to the to the cart item thumbnail image, if a width and height is specified, it resizes the thumbnail image to that size using the preview code (which caches the thumbnail also)
* @param integer width
* @param integer height
* @return string url to the to the cart item thumbnail image
*/
function wpsc_cart_item_image($width = null, $height = null) {
	global $wpsc_cart;
	if(($width > 0) && ($height > 0)) {
		$image_path = "index.php?productid=".$wpsc_cart->cart_item->product_id."&amp;thumbnail=true&amp;width=".$width."&amp;height=".$height."";
	} else {
		$image_path = WPSC_THUMBNAIL_URL.$wpsc_cart->cart_item->thumbnail_image;	
	}	
	return $image_path;
}



/**
 * The WPSC Cart class
 */
class wpsc_cart {
  var $delivery_country;
	var $selected_country;
	var $delivery_region;
	var $selected_region;
	
	var $shipping_method = null;
	var $shipping_option = null;
	
	var $coupon;
	var $tax_percentage;
	 
	// The cart loop variables
	var $cart_items = array();
	var $cart_item;
	var $cart_item_count = 0;
	var $current_cart_item = -1;
	var $in_the_loop = false;
   
  function wpsc_cart() {
    global $wpdb;
    $coupon = 'percentage';
    
    $this->delivery_country =& $_SESSION['delivery_country'];
	  $this->selected_country =& $_SESSION['selected_country'];
	  $this->delivery_region =& $_SESSION['delivery_region'];
	  $this->selected_region =& $_SESSION['selected_region'];
	  
	  
	  $this->get_tax_rate();
	  
	  $this->get_shipping_rates();
  }
  
  	/**
	* get_shipping_rates method, gets the shipping rates
	* @access public
	*/
  function get_shipping_rates() {
    global $wpdb;
    
	  // set us up with a shipping method.
	  if($this->shipping_method == null) {
	  		$custom_shipping = get_option('custom_shipping_options');
			if((get_option('do_not_use_shipping') != 1) && (count($custom_shipping) > 0)) {
				if(array_search($_SESSION['quote_shipping_method'], (array)$custom_shipping) === false) {
					//unset($_SESSION['quote_shipping_method']);
				}
				
				$shipping_quotes = null;
				if($_SESSION['quote_shipping_method'] != null) {
					// use the selected shipping module
//					$shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
				} else {
					// otherwise select the first one with any quotes
					foreach((array)$custom_shipping as $shipping_module) {
						// if the shipping module does not require a weight, or requires one and the weight is larger than zero
						if(($custom_shipping[$shipping_module]->requires_weight != true) or (($custom_shipping[$shipping_module]->requires_weight == true) and (shopping_cart_total_weight() > 0))) {
							$_SESSION['quote_shipping_method'] = $shipping_module;
	//						$shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
							if(count($shipping_quotes) > 0) { // if we have any shipping quotes, break the loop.
								break;
							}
						}
					}
				}
			}
	  }
	  if($this->shipping_option == null) {
	  }
	  
  }
  
  
  
	/**
	* get_tax_rate method, gets the tax rate as a percentage, based on the selected country and region
	* @access public
	*/
  function get_tax_rate() {
    global $wpdb;
    $country_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."currency_list` WHERE `isocode` IN('".get_option('base_country')."') LIMIT 1",ARRAY_A);
		if(($country_data['has_regions'] == 1)) {
			$region_data = $wpdb->get_row("SELECT `{$wpdb->prefix}region_tax`.* FROM `{$wpdb->prefix}region_tax` WHERE `{$wpdb->prefix}region_tax`.`country_id` IN('".$country_data['id']."') AND `".$wpdb->prefix."region_tax`.`id` IN('".get_option('base_region')."') ",ARRAY_A) ;
			$tax_percentage =  $region_data['tax'];
		} else {
			$tax_percentage =  $country_data['tax'];
		}
		$add_tax = false;
		if($this->selected_country == get_option('base_country')) {
			if($this->selected_country == 'US' ) { // tax handling for US 
				if($this->selected_region == get_option('base_region')) {
					// if they in the state, they pay tax
					$add_tax = true;
				} else if($this->delivery_region == get_option('base_region')) {
					// if they live outside the state, but are delivering to within the state, they pay tax also
					$add_tax = true;
				}
			} else { // tax handling for everywhere else
				if($country_data['has_regions'] == 1) {
					if(get_option('base_region') == $region ) {
						$add_tax = true;
					}
				} else {
					$add_tax = true;
				}
			}
		}
		if($add_tax !== true) {
			$tax_percentage = 0;
		}
		$this->tax_percentage = $tax_percentage;
  }


	/**
	 * Set Item method, requires a product ID and the parameters for the product
	 * @access public
	 *
	 * @param integer the product ID
	 * @param array parameters
	 * @return boolean true on sucess, false on failure
	*/
  function set_item($product_id, $parameters) {
    // default action is adding
		$new_cart_item = new wpsc_cart_item($product_id,$parameters, $this);
    $add_item = true;
    $edit_item = false;
    if(count($this->cart_items) > 0) {
      //loop through each cart item
      foreach($this->cart_items as $key => $cart_item) {
        // compare product ids and variations.
				if(($cart_item->product_id == $new_cart_item->product_id) && ($cart_item->product_variations == $new_cart_item->product_variations)) {
				  // if they are the same, increment the count, and break out;
					$this->cart_items[$key]->quantity  += $new_cart_item->quantity;
					$this->cart_items[$key]->refresh_item();
					$add_item = false;
					$edit_item = true;
				}
      }
    
    }
    // if we are still adding the item, add it
    if($add_item === true) {
			$this->cart_items[] = $new_cart_item;
		}
		
		
	  // if some action was performed, return true, otherwise, return false;
	  $status = false;
		if(($add_item == true) || ($edit_item == true)) {
			$status = true;
		}	
		$this->cart_item_count = count($this->cart_items);
		return $status;
	}
  
	/**
	 * Edit Item method
	 * @access public
	 *
	 * @param integer a cart_items key
	 * @param array an array of parameters to change
	 * @return boolean true on sucess, false on failure
	*/
  function edit_item($key, $parameters) {
    if(isset($this->cart_items[$key])) {
      foreach($parameters as $name => $value) {
        $this->cart_items[$key]->$name = $value; 
      }
			$this->cart_items[$key]->refresh_item();
      return true;
    } else {
     return false;
    }
  }
  
	/**
	 * Remove Item method 
	 * @access public
	 *
	 * @param integer a cart_items key
	 * @return boolean true on sucess, false on failure
	*/
  function remove_item($key) {
    if(isset($this->cart_items[$key])) {
			$this->cart_items[$key]->empty_item();
			unset($this->cart_items[$key]);
	    $this->cart_items = array_values($this->cart_items);
			$this->cart_item_count = count($this->cart_items);
	    $this->current_cart_item = -1;
			return true;
		} else {
			return false;
		}
  }
  
	/**
	 * Empty Cart method 
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function empty_cart() {
		$this->cart_items = array();
		$this->cart_item = null;
		$this->cart_item_count = 0;
		$this->current_cart_item = -1;
  }
  
  	/**
	 * calculate_total_price method 
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function calculate_total_price() {
    global $wpdb;
    
		foreach($this->cart_items as $key => $cart_item) {
		  $total += $cart_item->total_price;
		}
		return $total;
  }
  	/**
	 * calculate_total_weight method 
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function calculate_total_weight() {
    global $wpdb;
    
		foreach($this->cart_items as $key => $cart_item) {
		  $total += $cart_item->weight;
		}
		return $total;
  }
  
  
  
	/**
	 * process_as_currency method 
	 * @access public
	 *
	 * @param float a price
	 * @return string a price with a currency sign
	*/
	function process_as_currency($price) {
		global $wpdb, $wpsc_currency_data;
		$currency_type = get_option('currency_type');
		if(count($wpsc_currency_data) < 3) {
			$wpsc_currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1",ARRAY_A) ;
		}
	
		$price =  number_format($price, 2, '.', ',');
	
		if($wpsc_currency_data['symbol'] != '') {
			if($nohtml == false) {
				$currency_sign = $wpsc_currency_data['symbol_html'];
			} else {
				$currency_sign = $wpsc_currency_data['symbol'];
			}
		} else {
			$currency_sign = $wpsc_currency_data['code'];
		}
	
		$currency_sign_location = get_option('currency_sign_location');
		switch($currency_sign_location) {
			case 1:
			$output = $price.$currency_sign;
			break;
	
			case 2:
			$output = $price.' '.$currency_sign;
			break;
	
			case 3:
			$output = $currency_sign.$price;
			break;
	
			case 4:
			$output = $currency_sign.'  '.$price;
			break;
		}
	
		return $output;  
  }
  
  
  /**
	 * cart loop methods
	*/
 
  
  function next_cart_item() {
		$this->current_cart_item++;
		$this->cart_item = $this->cart_items[$this->current_cart_item];
		return $this->cart_item;
	}

  
  function the_cart_item() {
		$this->in_the_loop = true;
		$this->cart_item = $this->next_cart_item();
		if ( $this->current_cart_item == 0 ) // loop has just started
			do_action('wpsc_cart_loop_start');
	}

	function have_cart_items() {
		if ($this->current_cart_item + 1 < $this->cart_item_count) {
			return true;
		} else if ($this->current_cart_item + 1 == $this->cart_item_count && $this->cart_item_count > 0) {
			do_action('wpsc_cart_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_cart_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_cart_items() {
		$this->current_cart_item = -1;
		if ($this->cart_item_count > 0) {
			$this->cart_item = $this->cart_items[0];
		}
	}    
}






/**
 * The WPSC Cart Items class
 */
class wpsc_cart_item {
  // each cart item contains a reference to the cart that it is a member of
	var $cart;
  // provided values
	var $product_id;
	var $variation_values;
	var $product_variations;
	var $quantity = 1;
	var $provided_price;
	
	
	//values from the database
	var $product_name;
	var $unit_price;
	var $total_price;
	var $taxable_price = 0;
	var $tax = 0;
	var $weight = 0;
	var $shipping = 0;
	var $product_url;
	var $fullsize_image;
	var $thumbnail_image;
	
	var $is_donation = false;
	var $apply_tax = true;
	var $priceandstock_id;
	
	var $meta = array();
		/**
	 * wpsc_cart_item constructor, requires a product ID and the parameters for the product
	 * @access public
	 *
	 * @param integer the product ID
	 * @param array parameters
	 * @param objcet  the cart object
	 * @return boolean true on sucess, false on failure
	*/
	function wpsc_cart_item($product_id, $parameters, &$cart) {
    global $wpdb;
    // each cart item contains a reference to the cart that it is a member of, this makes that reference 
    $this->cart =& $cart;
    //extract($parameters);
    foreach($parameters as $name => $value) {
			$this->$name = $value;
    }
    
    
		$this->product_id = (int)$product_id;
		// to preserve backwards compatibility, make product_variations a reference to variations.
		$this->product_variations =& $this->variation_values;
		
		
		
		//$this->meta = $meta;
		$this->refresh_item();
	}

		/**
	 * update item method, currently can only update the quantity
	 * will require the parameters to update (no, you cannot change the product ID, delete the item and make a new one)
	 * @access public
	 *
	 * @param integer quantity
	 * #@param array parameters
	 * @return boolean true on sucess, false on failure
	*/
	function update_item($quantity) {
		$this->quantity = (int)$quantity;
	}
			/**
	 * refresh_item method, refreshes the item, calculates the prices, gets the name
	 * @access public
	 *
	 * @return array array of monetary and other values
	*/
	function refresh_item() {
    global $wpdb;
    $product = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}product_list` WHERE `id` = '{$this->product_id}' LIMIT 1", ARRAY_A);
    $priceandstock_id = 0;
    if(count($this->variation_values) > 0) {
      // if there are variations, get the price of the combination and the names of the variations.
			$variation_data = $wpdb->get_results("SELECT `name`,`variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` IN ('".implode("','",$this->variation_values)."')", ARRAY_A);
			$variation_names = array();
			$variation_ids = array();
			foreach($variation_data as $variation_row) {
				$variation_names[] = $variation_row['name'];
				$variation_ids[] = $variation_row['variation_id'];
			}
			
			asort($variation_ids);         
			$variation_id_string = implode(",", $variation_ids);
			
			$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `product_id` = '{$this->product_id}' AND `value_id` IN ( '".implode("', '",$this->variation_values )."' ) AND `all_variation_ids` IN('$variation_id_string') GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($this->variation_values)."' LIMIT 1");	
			
			$priceandstock_values = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}variation_priceandstock` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
			$price = $priceandstock_values['price'];
			$weight = wpsc_convert_weights($priceandstock_values['weight'], $priceandstock_values['weight_unit']);
		} else {
			$weight = wpsc_convert_weights($product['weight'], $product['weight_unit']);
		  // otherwise, just get the price.
      if($product['special_price'] > 0) {
        $price = $product['price'] - $product['special_price'];
      } else {
        if($product['special'] == 1) {
          $sale_discount = (float)$product['special_price'];
        } else {
					$sale_discount = 0;
        }
        $price = $product['price'] - $sale_discount;
      }
		}
		// create the string containing the product name.
		$product_name = $product['name'];
		if(count($variation_names) > 0) {
			$product_name .= " (".implode(", ",$variation_names).")";
		}
		$this->product_name = $product_name;
		$this->priceandstock_id = $priceandstock_id;
		$this->is_donation = (bool)$product['donation'];
		// change notax to boolean and invert it
		$this->apply_tax = !(bool)$product['notax'];
		if($this->is_donation == 1) {
			$this->unit_price = $this->provided_price;
		} else {
			$this->unit_price = $price;
		}
		$this->weight = $weight;
		$this->total_price = $this->unit_price * $this->quantity;
		if($this->apply_tax == true) {
		  $this->taxable_price = $this->total_price;
			$this->tax = $this->taxable_price * ($this->cart->tax_percentage/100);
		}
		$this->product_url = wpsc_product_url($this->product_id);
		
		$this->fullsize_image = $product['image'];
		if($product['thumbnail_image'] != null) {
			$this->thumbnail_image = $product['thumbnail_image'];
		} else {
			$this->thumbnail_image = $product['image'];
		}		
	}
   // spare, no longer needed, delete when possible
	function empty_item() {
	}
}
?>
