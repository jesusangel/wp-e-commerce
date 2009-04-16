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
 * @since 3.7
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
* coupon amount function, no parameters
* * @return integer the item count
*/
function wpsc_coupon_amount() {
	global $wpsc_cart;
	return $wpsc_cart->process_as_currency($wpsc_cart->coupons_amount);
}
/**
* cart total function, no parameters
* @return string the total price of the cart, with a currency sign
*/
function wpsc_cart_total() {
	global $wpsc_cart;  
	$total = $wpsc_cart->calculate_subtotal();
	$total += $wpsc_cart->calculate_total_shipping();
	$total += $wpsc_cart->calculate_total_tax();
	$total -= $wpsc_cart->coupons_amount;
	return $wpsc_cart->process_as_currency($total);
}

/**
* nzshpcrt_overall_total_price function, no parameters
* @return string the total price of the cart, with a currency sign
*/
function nzshpcrt_overall_total_price() {
	global $wpsc_cart;
	$total = $wpsc_cart->calculate_subtotal();
	$total += $wpsc_cart->calculate_total_shipping();
	$total += $wpsc_cart->calculate_total_tax();
  return $total;
}

/**
* cart total weight function, no parameters
* @return float the total weight of the cart
*/
function wpsc_cart_weight_total() {
	global $wpsc_cart;
	return $wpsc_cart->calculate_total_weight();
}

/**
* tax total function, no parameters
* @return float the total weight of the cart
*/
function wpsc_cart_tax() {
	global $wpsc_cart;
	return $wpsc_cart->process_as_currency($wpsc_cart->calculate_total_tax());
}

/**
* uses shipping function, no parameters
* @return boolean if true, all items in the cart do use shipping
*/
function wpsc_uses_shipping() {
	global $wpsc_cart;
	return $wpsc_cart->uses_shipping();
}
  
/**
* cart has shipping function, no parameters
* @return boolean true for yes, false for no
*/
function wpsc_cart_has_shipping() {
	global $wpsc_cart;
	if($wpsc_cart->calculate_total_shipping() > 0) {
		$output = true;
	} else {
		$output = false;
	}
	return $output;
}

/**
* cart shipping function, no parameters
* @return string the total shipping of the cart, with a currency sign
*/
function wpsc_cart_shipping() {
	global $wpsc_cart;
	return $wpsc_cart->process_as_currency($wpsc_cart->calculate_total_shipping());
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
* cart item url function, no parameters
* @return string the cart item url
*/
function wpsc_cart_item_url() {
	global $wpsc_cart;
	return $wpsc_cart->cart_item->product_url;
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
* have shipping methods function, no parameters
* @return boolean
*/
function wpsc_have_shipping_methods() {
	global $wpsc_cart;
	return $wpsc_cart->have_shipping_methods();
}
/**
* the shipping method function, no parameters
* @return boolean
*/
function wpsc_the_shipping_method() {
	global $wpsc_cart;
	return $wpsc_cart->the_shipping_method();
}
/**
* the shipping method name function, no parameters
* @return string shipping method name
*/
function wpsc_shipping_method_name() {
	global $wpsc_cart, $wpsc_shipping_modules;
	return $wpsc_shipping_modules[$wpsc_cart->shipping_method]->name;
}


/**
* the shipping method  internal name function, no parameters
* @return string shipping method internal name
*/
function wpsc_shipping_method_internal_name() {
	global $wpsc_cart, $wpsc_shipping_modules;
	return $wpsc_cart->shipping_method;
}






 /**
* have shipping quotes function, no parameters
* @return string the cart item url
*/
function wpsc_have_shipping_quotes() {
	global $wpsc_cart;
	return $wpsc_cart->have_shipping_quotes();
}

/**
* the shipping quote function, no parameters
* @return string the cart item url
*/
function wpsc_the_shipping_quote() {
	global $wpsc_cart;
	return $wpsc_cart->the_shipping_quote();
}

/**
* the shipping quote name function, no parameters
* @return string shipping quote name
*/
function wpsc_shipping_quote_name() {
	global $wpsc_cart;
	return $wpsc_cart->shipping_quote['name'];
}

/**
* the shipping quote value function, no parameters
* @return string shipping quote value
*/
function wpsc_shipping_quote_value($numeric = false) {
	global $wpsc_cart;
	if($numeric == true) {
		return $wpsc_cart->shipping_quote['value'];
	} else {
		return $wpsc_cart->process_as_currency($wpsc_cart->shipping_quote['value']);
	}
}

/**
* the shipping quote html ID function, no parameters
* @return string shipping quote html ID
*/
function wpsc_shipping_quote_html_id() {
	global $wpsc_cart;
	return $wpsc_cart->shipping_method."_".$wpsc_cart->current_shipping_quote;
}

/**
* the shipping quote selected state function, no parameters
* @return string true or false
*/
function wpsc_shipping_quote_selected_state() {
	global $wpsc_cart;
	//
	if(($wpsc_cart->selected_shipping_method == $wpsc_cart->shipping_method)&& ($wpsc_cart->selected_shipping_option == $wpsc_cart->shipping_quote['name'])) {
		return "checked='checked'";
	} else {
		return "";
	}
}




/**
 * The WPSC Cart class
 */
class wpsc_cart {
  var $delivery_country;
	var $selected_country;
	var $delivery_region;
	var $selected_region;
	
	var $selected_shipping_method = null;
// 	var $shipping_quotes = null;
	var $selected_shipping_option = null;
	
	var $coupon;
	var $tax_percentage;
	var $unique_id;
	var $errors;
	
	
	// caching of frequently used values, these are wiped when the cart is modified and then remade when needed
	var $total_tax = null;
	var $base_shipping = null;
	var $total_item_shipping = null;
	var $total_shipping = null;
	var $subtotal = null;
	var $total_price = null;
	var $uses_shipping = null;
	 
	var $is_incomplete = true;
	
	// The cart loop variables
	var $cart_items = array();
	var $cart_item;
	var $cart_item_count = 0;
	var $current_cart_item = -1;
	var $in_the_loop = false;
   
	// The shipping method loop variables
	var $shipping_methods = array();
	var $shipping_method;
	var $shipping_method_count = 0;
	var $current_shipping_method = -1;
	var $in_the_method_loop = false;
	
	// The shipping quote loop variables
	var $shipping_quotes = array();
	var $shipping_quote;
	var $shipping_quote_count = 0;
	var $current_shipping_quote = -1;
	var $in_the_quote_loop = false;
	
	//coupon variable
	var $coupons_name = '';
	var $coupons_amount = 0;
	
  function wpsc_cart() {
    global $wpdb, $wpsc_shipping_modules;
    $coupon = 'percentage'; 
    // this is here to stop extremely bizzare errors with $wpsc_cart somehow not ending up as a global variable, yet certain code being run from it that eventually expects it to be one
    if(!is_object($GLOBALS['wpsc_cart'])) {
// 			$GLOBALS['wpsc_cart'] =& $this;
    }
	  $this->update_location();
	  $this->get_tax_rate();
	  $this->unique_id = sha1(uniqid(rand(), true));
	  
	  $this->get_shipping_method();
  }
  
    
  /**
	* update_location method, updates the location
	* @access public
	*/
  function update_location() {
   if(!isset($_SESSION['wpsc_selected_country']) && !isset($_SESSION['wpsc_delivery_country'])) {
			$_SESSION['wpsc_selected_country'] = get_option('base_country');
			$_SESSION['wpsc_delivery_country'] = get_option('base_country');   
   } else {
     if(!isset($_SESSION['wpsc_selected_country'])) {
			$_SESSION['wpsc_selected_country'] = $_SESSION['wpsc_delivery_country'];
     } else if(!isset($_SESSION['wpsc_delivery_country'])) {
			$_SESSION['wpsc_delivery_country'] = $_SESSION['wpsc_selected_country'];     
     }   
   }
  
    $this->delivery_country =& $_SESSION['wpsc_delivery_country'];
	  $this->selected_country =& $_SESSION['wpsc_selected_country'];
	  $this->delivery_region =& $_SESSION['wpsc_delivery_region'];
	  $this->selected_region =& $_SESSION['wpsc_selected_region'];
	}
	
	
  /**
	* get_shipping_rates method, gets the shipping rates
	* @access public
	*/
  function get_shipping_method() {
    global $wpdb, $wpsc_shipping_modules;
    
	  // set us up with a shipping method.
		$custom_shipping = get_option('custom_shipping_options');
		
	  $this->shipping_methods = get_option('custom_shipping_options');
	  $this->shipping_method_count = count($this->shipping_methods);
		
		if((get_option('do_not_use_shipping') != 1) && (count($this->shipping_methods) > 0)) {
			if(array_search($this->selected_shipping_method, (array)$this->shipping_methods) === false) {
				//unset($this->selected_shipping_method);
			}
			
			$shipping_quotes = null;
			if($this->selected_shipping_method != null) {
				// use the selected shipping module
				$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
			} else {
				// otherwise select the first one with any quotes
				foreach((array)$custom_shipping as $shipping_module) {
					// if the shipping module does not require a weight, or requires one and the weight is larger than zero
					$this->selected_shipping_method = $shipping_module;
					$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
					if(count($this->shipping_quotes) > 0) { // if we have any shipping quotes, break the loop.
						break;
					}
				}
			}
		}
  }
  
  /**
	* get_shipping_option method, gets the shipping option from the selected method and associated quotes
	* @access public
	*/
  function get_shipping_option() {
    global $wpdb, $wpsc_shipping_modules;
    if(count($this->shipping_quotes) < 1) {
			$this->selected_shipping_option = '';
			}
		if(($this->shipping_quotes != null) && (array_search($this->selected_shipping_option, $this->shipping_quotes) === false)) {
			//echo "<pre>".print_r(array_pop(array_keys(array_slice($this->shipping_quotes,0,1))),true)."</pre>";
			$this->selected_shipping_option = array_pop(array_keys(array_slice($this->shipping_quotes,0,1)));
		}
  }
  

  /**
	* update_shipping method, updates the shipping
	* @access public
	*/
  function update_shipping($method, $option) {
    global $wpdb, $wpsc_shipping_modules;
		$this->selected_shipping_method = $method;
		
		$this->shipping_quotes = $wpsc_shipping_modules[$method]->getQuote();
		
		$this->selected_shipping_option = $option;
		
		foreach($this->cart_items as $key => $cart_item) {
			$this->cart_items[$key]->refresh_item();
		}
		$this->clear_cache();
		$this->get_shipping_option();	
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
    
    if($this->check_remaining_quantity($product_id, $parameters['variation_values'], $parameters['quantity']) == true) {
			$new_cart_item = new wpsc_cart_item($product_id,$parameters, $this);
			
			$add_item = true;
			$edit_item = false;
			if(count($this->cart_items) > 0) {
				//loop through each cart item
				foreach($this->cart_items as $key => $cart_item) {
					// compare product ids and variations.
					if(($cart_item->product_id == $new_cart_item->product_id) &&
					  ($cart_item->product_variations == $new_cart_item->product_variations) &&
					  ($cart_item->custom_message == $new_cart_item->custom_message) &&
					  ($cart_item->custom_file == $new_cart_item->custom_file)) {
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
		} else {
			//$errors[] = new WP_Error('no_stock_available', __(TXT_WPSC_OUT_OF_STOCK_ERROR_MESSAGE));
		}
    
		
	  // if some action was performed, return true, otherwise, return false;
	  $status = false;
		if(($add_item == true) || ($edit_item == true)) {
			$status = true;
		}	
		$this->cart_item_count = count($this->cart_items);
		$this->clear_cache();
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
    
			if($this->check_remaining_quantity($product_id, $parameters['variation_values'], $parameters['quantity']) == true) {
				foreach($parameters as $name => $value) {
					$this->cart_items[$key]->$name = $value; 
				}
				$this->cart_items[$key]->refresh_item();
				$this->clear_cache();
			} else {
				//$errors[] = new WP_Error('no_stock_available', __(TXT_WPSC_OUT_OF_STOCK_ERROR_MESSAGE));
			}
      return true;
    } else {
     return false;
    }
  }
  
	/**
	 * check remaining quantity method
	 * currently only checks remaining stock, in future will do claimed stock and quantity limits
	 * will need to return errors, then, rather than true/false, maybe use the wp_error object?
	 * @access public
	 *
	 * @param integer a product ID key
	 * @param array  variations on the product
	 * @return boolean true on sucess, false on failure
	*/
  function check_remaining_quantity($product_id, $variations = array(), $quantity = 1) {
    global $wpdb;
		$quantity_data = $wpdb->get_row("SELECT `quantity_limited`, `quantity`  FROM `{$wpdb->prefix}product_list` WHERE `id` IN ('$product_id') LIMIT 1", ARRAY_A);
		// check to see if the product uses stock
		if($quantity_data['quantity_limited'] == 1){
			if(count($variations) > 0) { /// if so and we have variations, select the stock for the chosen variations
				$variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` IN ('".implode("','",$variations)."')");
				asort($variation_ids);
				$all_variation_ids = implode(",", $variation_ids);
				
				$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `product_id` = '".(int)$product_id."' AND `value_id` IN ( '".implode("', '",$variations )."' )  AND `all_variation_ids` IN('$all_variation_ids')  GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($variations)."' LIMIT 1");
				
				$variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
				$stock = $variation_stock_data['stock'];
				
			} else { /// if so and we have no variations, select the stock for the product
			  $stock = $quantity_data['quantity'];
			  $priceandstock_id = 0;
			}
	    if($stock > 0) {
				$claimed_stock = $wpdb->get_var("SELECT SUM(`stock_claimed`) FROM `{$wpdb->prefix}wpsc_claimed_stock` WHERE `product_id` IN('$product_id') AND `variation_stock_id` IN('$priceandstock_id')");
				echo "/*".print_r($claimed_stock,true)."*/";
				if(($claimed_stock + $quantity) <= $stock) {
					$output = true;
				} else {
					$output = false;
				}
		  } else {
				$output = false;	    
	    }
	     
    } else {
      $output = true;
    }
    return $output;
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
			//$this->cart_items[$key]->empty_item();
			unset($this->cart_items[$key]);
	    $this->cart_items = array_values($this->cart_items);
			$this->cart_item_count = count($this->cart_items);
	    $this->current_cart_item = -1;
			return true;
		} else {
			return false;
		}
		$this->clear_cache();
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
		$this->clear_cache();
		$this->cleanup();
  }
  
  
  
	/**
	 * Clear Cache method, used to clear the cached totals 
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function clear_cache() {
		$this->total_tax = null;
		$this->base_shipping = null;
		$this->total_item_shipping = null;
		$this->total_shipping = null;
		$this->subtotal = null;
		$this->total_price = null;
		$this->uses_shipping = null;
		$this->get_shipping_option();	
	}
  
 	/**
	 * submit_stock_claims method, changes the association of the stock claims from the cart unique to the purchase log ID
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function submit_stock_claims($purchase_log_id) {
    global $wpdb;
    //exit($wpdb->prepare("UPDATE `{$wpdb->prefix}wpsc_claimed_stock` SET `cart_id` = '%d', `cart_submitted` = '1' WHERE `cart_id` IN('%s')", $purchase_log_id, $this->unique_id));
		$wpdb->query($wpdb->prepare("UPDATE `{$wpdb->prefix}wpsc_claimed_stock` SET `cart_id` = '%d', `cart_submitted` = '1' WHERE `cart_id` IN('%s')", $purchase_log_id, $this->unique_id));
	}
	
	 	/**
	 * cleanup method, cleans up the cart just before final destruction
	 * @access public
	 *
	 * No parameters, nothing returned
	*/
  function cleanup() {
    global $wpdb;
    //echo $wpdb->prepare("DELETE FROM `{$wpdb->prefix}wpsc_claimed_stock` WHERE `cart_id` IN ('%s')", $this->unique_id);
		$wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->prefix}wpsc_claimed_stock` WHERE `cart_id` IN ('%s')", $this->unique_id));
	}
	
  /**
	 * calculate total price method 
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function calculate_total_price() {
    if($this->total_price == null) {
			$total = $this->calculate_subtotal();
			$total += $this->calculate_total_shipping();
			$total += $this->calculate_total_tax();
			$total -= $this->coupons_amount;
			$this->total_price = $total;
			//exit($this->coupons_amount);
		} else {
		  $total = $this->total_price;
		}
		return $total;
  }
  
  /**
	 * calculate_subtotal method 
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function calculate_subtotal() {
    global $wpdb;
    $total = 0;
    if($this->subtotal == null) {
			foreach($this->cart_items as $key => $cart_item) {
				$total += $cart_item->total_price;
			}
			$this->subtotal = $total;
		} else {
		  $total = $this->subtotal;
		}
		return $total;
  }
  
  	/**
	 * calculate total tax method 
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function calculate_total_tax() {
    global $wpdb;
    $total = 0;
    if($this->total_tax == null) {
			foreach($this->cart_items as $key => $cart_item) {
				$total += $cart_item->tax;
			}
			$this->total_tax = $total;
		} else {
		  $total = $this->total_tax;
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
	* calculate_total_shipping method, gets the shipping option from the selected method and associated quotes
	* @access public
	 * @return float returns the shipping as a floating point value
	*/
  function calculate_total_shipping() {
    $total = $this->calculate_base_shipping();
    $total += $this->calculate_per_item_shipping();
    return $total;
  }
  
  
    /**
	* calculate_base_shipping method, gets the shipping option from the selected method and associated quotes
	* @access public
	 * @return float returns the shipping as a floating point value
	*/
  function calculate_base_shipping() {
    global $wpdb, $wpsc_shipping_modules;
    if($this->uses_shipping()) {
			if($this->base_shipping == null) {
				$this->shipping_quotes = $wpsc_shipping_modules[$this->selected_shipping_method]->getQuote();
				$total = (float)$this->shipping_quotes[$this->selected_shipping_option];
				$this->base_shipping = $total;
			} else {
				$total = $this->base_shipping;
			}
		} else {
		  $total = 0;
		}
		return $total;
  }
  
    /**
	* calculate_per_item_shipping method, gets the shipping option from the selected method and associated quotesing 
	* @access public
	 * @return float returns the shipping as a floating point value
	*/
  function calculate_per_item_shipping($method = null) {
    global $wpdb, $wpsc_shipping_modules;
    if($method == null) {
      $method = $this->selected_shipping_method;
    }
		
    if(($this->total_item_shipping == null) || ($method != $this->selected_shipping_method)) {
			foreach((array)$this->cart_items as $cart_item) {
				$total += $cart_item->calculate_shipping($method);
			}
			if($method == $this->selected_shipping_method) {
				$this->total_item_shipping = $total;
			}
		} else {
		  $total = $this->total_item_shipping;
		}
		//exit("<pre>".print_r($total,true)."<pre>");
		return $total;
  }
  
  
  /**
	 * uses shipping method, to determine if shipping is used.
	 * @access public
	 *
	 * @return float returns the price as a floating point value
	*/
  function uses_shipping() {
    global $wpdb;
    $uses_shipping = 0;
    if(($this->uses_shipping === null)) {
			foreach($this->cart_items as $key => $cart_item) {
				$uses_shipping += (int)$cart_item->uses_shipping;
			}
		  $uses_shipping = (bool)$uses_shipping;
		} else {
		  $uses_shipping = $this->uses_shipping;
		}
		return $uses_shipping;
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
	 * save_to_db method, saves the cart to the database
	 * @access public
	 *
	*/
  function save_to_db($purchase_log_id) {
    global $wpdb;
    
		foreach($this->cart_items as $key => $cart_item) {
		  $cart_item->save_to_db($purchase_log_id);
		}
		unset($this->coupons_amount);
		unset($this->coupons_name);
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
  
  /**
	 * shipping_methods methods
	*/
	function next_shipping_method() {
		$this->current_shipping_method++;
		$this->shipping_method = $this->shipping_methods[$this->current_shipping_method];
		return $this->shipping_method;
	}
	
	
	function the_shipping_method() {
		$this->shipping_method = $this->next_shipping_method();
	 	$this->get_shipping_quotes();
	}
	
	function have_shipping_methods() {
		if ($this->current_shipping_method + 1 < $this->shipping_method_count) {
			return true;
		} else if ($this->current_shipping_method + 1 == $this->shipping_method_count && $this->shipping_method_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_shipping_methods();
		}
		return false;
	}
	
	function rewind_shipping_methods() {
		$this->current_shipping_method = -1;
		if ($this->shipping_method_count > 0) {
			$this->shipping_method = $this->shipping_methods[0];
		}
	}
	
	/**
	 * shipping_quotes methods
	*/
  function get_shipping_quotes() {
    global $wpdb, $wpsc_shipping_modules;
    $this->shipping_quotes = array();
    $unprocessed_shipping_quotes = $wpsc_shipping_modules[$this->shipping_method]->getQuote();
    $num = 0;
    foreach($unprocessed_shipping_quotes as $shipping_key => $shipping_value) {
      
			$per_item_shipping = $this->calculate_per_item_shipping($this->shipping_method);
      $this->shipping_quotes[$num]['name'] = $shipping_key;
      $this->shipping_quotes[$num]['value'] = (float)$shipping_value+(float)$per_item_shipping;
      $num++;
    }
    $this->shipping_quote_count = count($this->shipping_quotes);
  }
  
  
	function next_shipping_quote() {
		$this->current_shipping_quote++;
		$this->shipping_quote = $this->shipping_quotes[$this->current_shipping_quote];
		return $this->shipping_quote;
	}
	
	
	function the_shipping_quote() {
		$this->shipping_quote = $this->next_shipping_quote();
	}
	
	function have_shipping_quotes() {
		if ($this->current_shipping_quote + 1 < $this->shipping_quote_count) {
			return true;
		} else if ($this->current_shipping_quote + 1 == $this->shipping_quote_count && $this->shipping_quote_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_shipping_quotes();
		}
		return false;
	}
	
	function rewind_shipping_quotes() {
		$this->current_shipping_quote = -1;
		if ($this->shipping_quote_count > 0) {
			$this->shipping_quote = $this->shipping_quotes[0];
		}
	}
	
	/**
	 * Applying Coupons
	 */
	function apply_coupons($couponAmount, $coupons){
		//exit('coupon amount'.$couponAmount);
		$this->clear_cache();
		$this->coupons_name = $coupons;
		$this->coupons_amount = $couponAmount;
		$this->calculate_total_price();
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
	var $variation_data;
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
	
	// user provided values
	var $custom_message = null;
	var $custom_file = null;
	
	
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
    // still need to add the ability to limit the number of an item in the cart at once.
    // each cart item contains a reference to the cart that it is a member of, this makes that reference 
    $this->cart =& $cart;
    //$this->save_provided_file
    //$this->
    
    //extract($parameters);
    foreach($parameters as $name => $value) {
			$this->$name = $value;
    }
    
    
		$this->product_id = (int)$product_id;
		// to preserve backwards compatibility, make product_variations a reference to variations.
		$this->product_variations =& $this->variation_values;
		
		
		
    if(($parameters['is_customisable'] == true) && ($parameters['file_data'] != null)) {
      $this->save_provided_file($this->file_data);
    }
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
    global $wpdb, $wpsc_shipping_modules;
    $product = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}product_list` WHERE `id` = '{$this->product_id}' LIMIT 1", ARRAY_A);
    $priceandstock_id = 0;
    if(count($this->variation_values) > 0) {
      // if there are variations, get the price of the combination and the names of the variations.
			$variation_data = $wpdb->get_results("SELECT *FROM `{$wpdb->prefix}variation_values` WHERE `id` IN ('".implode("','",$this->variation_values)."')", ARRAY_A);
			$this->variation_data = $variation_data;
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
			$file_id = $priceandstock_values['file'];
			
		} else {
		  $priceandstock_id = 0;
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
        $file_id = $product['file'];
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
		// change no_shipping to boolean and invert it
		$this->uses_shipping = !(bool)$product['no_shipping'];
		$this->has_limited_stock = (bool)(int)$product['quantity_limited'];
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
		
		if($file_id > 0) {
			$this->file_id = (int)$file_id;
			$this->is_downloadable = true;
		} else {
			$this->file_id = null;
			$this->is_downloadable = false;
		}
		
		
	  $this->shipping = $wpsc_shipping_modules[$this->cart->selected_shipping_method]->get_item_shipping($this->unit_price, $this->quantity, $this->weight, $this->product_id);
	  // update the claimed stock here
	  $this->update_claimed_stock();
	}
		
	/**
	 * Calculate shipping method
	 * if no parameter passed, takes the currently selected method
	 * @access public
	 *
	 * @param string shipping method
	 * @return boolean true on sucess, false on failure
	*/		
		
	function calculate_shipping($method = null) {
    global $wpdb, $wpsc_shipping_modules;
    if($method === null) {
      $method = $this->cart->selected_shipping_method;
    }
    $shipping = $wpsc_shipping_modules[$method]->get_item_shipping($this->unit_price, $this->quantity, $this->weight, $this->product_id);
    if($method == $this->cart->selected_shipping_method) {
    $this->shipping = $shipping;
    }
	  return $shipping;
	}
		
	/**
	 * user provided file method
	 * @access public
	 * @param string shipping method
	 * @return boolean true on sucess, false on failure
	*/		
		
	function save_provided_file($file_data) {
    global $wpdb;
		$accepted_file_types['mime'][] = 'image/jpeg';
		$accepted_file_types['mime'][] = 'image/gif';
		$accepted_file_types['mime'][] = 'image/png';
		$accepted_file_types['mime'][] = 'image/svg+xml';
		
		
		$accepted_file_types['ext'][] = 'jpeg';
		$accepted_file_types['ext'][] = 'jpg';
		$accepted_file_types['ext'][] = 'gif';
		$accepted_file_types['ext'][] = 'png';
		$accepted_file_types['ext'][] = 'svg';
		
		
		$can_have_uploaded_image = get_product_meta($this->product_id,'can_have_uploaded_image');
		if ($can_have_uploaded_image=='on') {
		  $mime_type_data = wpsc_get_mimetype($file_data['tmp_name'], true);			
			$name_parts = explode('.',basename($file_data['name']));
			$extension = array_pop($name_parts);
		  if($mime_type_data['is_reliable'] == true) {
		    $mime_type = $mime_type_data['mime_type'];
		  } else {
		    // if we can't use what PHP provides us with, we have to trust the user as there aren't really any other choices.
		    $mime_type = $file_data['type'];
		  }
		  //echo( "<pre>".print_r($mime_type_data,true)."</pre>" );
		  //exit( "<pre>".print_r($file_data,true)."</pre>" );
			if((array_search($mime_type, $accepted_file_types['mime']) !== false) && (array_search($extension, $accepted_file_types['ext']) !== false) ) {
			  if(is_file(WPSC_USER_UPLOADS_DIR.$file_data['name'])) {
					$name_parts = explode('.',basename($file_data['name']));
					$extension = array_pop($name_parts);
					$name_base = implode('.',$name_parts);
					$file_data['name'] = null;
					$num = 2;
					//  loop till we find a free file name, first time I get to do a do loop in yonks
					do {
						$test_name = "{$name_base}-{$num}.{$extension}";
						if(!file_exists(WPSC_USER_UPLOADS_DIR.$test_name)) {
							$file_data['name'] = $test_name;
						}
						$num++;
					} while ($file_data['name'] == null);
			  }
			  //exit($file_data['name']);
			  $unique_id =  sha1(uniqid(rand(), true));
				if(move_uploaded_file($file_data['tmp_name'], WPSC_USER_UPLOADS_DIR.$file_data['name']) ) {
					$this->custom_file = array('file_name' => $file_data['name'], 'mime_type' => $mime_type, "unique_id" => $unique_id );			
				}
			}
		}
	}
		
	/**
	 * update_claimed_stock method
	 * Updates the claimed stock table, to prevent people from having more than the existing stock in their carts
	 * @access public
	 *
	 * no parameters, nothing returned
	*/
	function update_claimed_stock() {
		global $wpdb;
		if($this->has_limited_stock == true) {
			$wpdb->query($wpdb->prepare("REPLACE INTO`{$wpdb->prefix}wpsc_claimed_stock` ( `product_id` , `variation_stock_id` , `stock_claimed` , `last_activity` , `cart_id` )VALUES ('%d', '%d', '%s', NOW(), '%s');",$this->product_id, $this->priceandstock_id, $this->quantity, $this->cart->unique_id));	
 		}
	}
		
		
	/**
	 * save to database method
	 * @access public
	 *
	 * @param integer purchase log id
	*/
	function save_to_db($purchase_log_id) {
		global $wpdb, $wpsc_shipping_modules;
    $shipping = $wpsc_shipping_modules[$this->cart->selected_shipping_method]->get_item_shipping($this->unit_price, 1, $this->weight, $this->product_id);
    
		if($this->apply_tax == true) {
			$tax = $this->unit_price * ($this->cart->tax_percentage/100);
		} else {
			$tax = 0;
		}		
		
		$wpdb->query($wpdb->prepare("INSERT INTO `{$wpdb->prefix}cart_contents` (`prodid`, `name`, `purchaseid`, `price`, `pnp`,`tax_charged`, `gst`, `quantity`, `donation`, `no_shipping`, `custom_message`, `files`, `meta`) VALUES ('%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '0', '%s', '%s', NULL)", $this->product_id, $this->product_name, $purchase_log_id, $this->unit_price, (float)$shipping, $tax, $this->cart->tax_percentage, $this->quantity, $this->is_donation, $this->custom_message, serialize($this->custom_file)));
		
		$cart_id = $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".$wpdb->prefix."cart_contents` LIMIT 1");
		
		foreach((array)$this->variation_data as $variation_row) {
			$wpdb->query("INSERT INTO `".$wpdb->prefix."cart_item_variations` ( `cart_id` , `variation_id` , `value_id` ) VALUES ( '".$cart_id."', '".$variation_row['variation_id']."', '".$variation_row['id']."' );");
		}
		
    $downloads = get_option('max_downloads');
		if($this->is_downloadable == true) {
			// if the file is downloadable, check that the file is real
			if($wpdb->get_var("SELECT `id` FROM `{$wpdb->prefix}product_files` WHERE `id` IN ('{$this->file_id}')")) {
				$unique_id = sha1(uniqid(mt_rand(), true));
				$wpdb->query("INSERT INTO `{$wpdb->prefix}download_status` ( `fileid` , `purchid` , `cartid`, `uniqueid`, `downloads` , `active` , `datetime` ) VALUES ( '{$this->file_id}', '{$purchase_log_id}', '{$cart_id}', '{$unique_id}', '$downloads', '0', NOW( ));");
			}
		}
		
	}
	
}
?>