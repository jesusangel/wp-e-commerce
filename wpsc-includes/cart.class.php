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
* cart total function, no parameters
* @return string the total price of the cart, with a currency sign
*/
function wpsc_cart_total() {
	global $wpsc_cart;
	$total = $wpsc_cart->calculate_subtotal();
	$total += $wpsc_cart->calculate_total_shipping();
	$total += $wpsc_cart->calculate_total_tax();
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
	
	
  function wpsc_cart() {
    global $wpdb, $wpsc_shipping_modules;
    $coupon = 'percentage'; 
    
	  $this->update_location();
	  $this->get_tax_rate();
	  
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
      foreach($parameters as $name => $value) {
        $this->cart_items[$key]->$name = $value; 
      }
			$this->cart_items[$key]->refresh_item();
			$this->clear_cache();
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
			$this->total_price = $total;
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
      $this->shipping_quotes[$num]['value'] = $shipping_value+$per_item_shipping;
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
    // still need to add the ability to limit the number of an item in the cart at once.
    
    
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
    global $wpdb, $wpsc_shipping_modules;
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
		// change no_shipping to boolean and invert it
		$this->uses_shipping = !(bool)$product['no_shipping'];
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
	  $this->shipping = $wpsc_shipping_modules[$this->cart->selected_shipping_method]->get_item_shipping($this->unit_price, $this->quantity, $this->weight, $this->product_id);
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
		$prepared_query = $wpdb->query("INSERT INTO `wp_cart_contents` (`prodid`, `name`, `purchaseid`, `price`, `pnp`,`tax_charged`, `gst`, `quantity`, `donation`, `no_shipping`, `files`, `meta`) VALUES ('{$this->product_id}', '{$this->product_name}', '{$purchase_log_id}', '{$this->unit_price}', '{$shipping}', '{$tax}', '{$this->cart->tax_percentage}', {$this->quantity}, '{$this->is_donation}', '0', '', NULL)");		
	}
	
}
?>