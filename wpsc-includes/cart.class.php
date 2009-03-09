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
 * The WPSC Cart class
 */
class wpsc_cart {
  var $delivery_country;
	var $selected_country;
	var $delivery_region;
	var $selected_region;
	
	
	
	
	var $coupon;
	var $tax_percentage;
	
	
	var $cart_items = array();
   
  function wpsc_cart() {
    $coupon = 'percentage';
    
    $this->delivery_country =& $_SESSION['delivery_country'];
	  $this->selected_country =& $_SESSION['selected_country'];
	  $this->delivery_region =& $_SESSION['delivery_region'];
	  $this->selected_region =& $_SESSION['selected_region'];
	  $this->get_tax_rate();
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
      //$new_key = sha1(uniqid(rand(), true));
			$this->cart_items[] = $new_cart_item;
		}
		
		
	  // if some action was performed, return true, otherwise, return false;
	  $status = false;
		if(($add_item == true) || ($edit_item == true)) {
			$status = true;
		}	
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
			unset($this->cart_items[$key]);
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
			return true;
		} else {
			return false;
		}
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
// 		$tax = $this->taxable_price - ();
		
	}
   // spare, no longer needed, delete when possible
	function empty_item() {
	}
}
?>
