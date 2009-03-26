<?php
/**
 * WP eCommerce checkout class
 *
 * These are the class for the WP eCommerce checkout
 * The checkout class handles dispaying the checkout form fields
 *
 * @package wp-e-commerce
 * @subpackage wpsc-checkout-classes 
*/

function wpsc_have_checkout_items() {
	global $wpsc_checkout;
	return $wpsc_checkout->have_checkout_items();
}

function wpsc_the_checkout_item() {
	global $wpsc_checkout;
	return $wpsc_checkout->the_checkout_item();
}
function wpsc_checkout_form_is_header() {
	global $wpsc_checkout;
	if($wpsc_checkout->checkout_item->type == 'heading') {
	  $output = true;
	} else {
	  $output = false;
	}
	return $output;
}


function wpsc_checkout_form_name() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_name();
}
function wpsc_checkout_form_element_id() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_element_id();
}

function wpsc_checkout_form_field() {
	global $wpsc_checkout;
	return $wpsc_checkout->form_field();
}




function wpsc_shipping_country_list() {
	global $wpdb, $wpsc_shipping_modules;
	$output = "<input type='hidden' name='wpsc_ajax_actions' value='update_location' />";
	$selected_country = $_SESSION['wpsc_delivery_country'];
	$selected_region = $_SESSION['wpsc_delivery_region'];
	if($selected_country == null) {
		$selected_country = get_option('base_country');
	}
	if($selected_region == null) {
		$selected_region = get_option('base_region');
	}
	$country_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
	$output .= "<select name='country' id='current_country' onchange='submit_change_country();' >";
	foreach ($country_data as $country) {
	// 23-02-09 fix for custom target market by jeffry
	// recon this should be taken out and put into a function somewhere maybe,,,
	 if($country['visible'] == '1'){
		$selected ='';
		if($selected_country == $country['isocode']) {
			$selected = "selected='true'";
		}
		$output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>";
	 }
	}

	$output .= "</select>";
	
	if ($selected_country == 'US') {
		$region_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."region_tax` WHERE country_id='136'",ARRAY_A);
		$output .= "<select name='region'  onchange='submit_change_country();' >";
		foreach ($region_data as $region) {
			$selected ='';
			if($selected_region == $region['id']) {
				$selected = "selected='true'";
			}
			$output .= "<option $selected value='{$region['id']}'>{$region['name']}</option>";
		}
		$output .= "";
		
		$output .= "</select>";
	} else {
		$output .= " ";
	}
	
// 	$output .= "ZipCode:";
if(isset($_POST['zipcode'])) {
		if ($_POST['zipcode']=='') {
			$zipvalue = 'Your Zipcode';
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
			$color = '#999';
		} else {
			$zipvalue = $_POST['zipcode'];
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
			$color = '#000';
		}
	} else if(isset($_SESSION['wpsc_zipcode']) && ($_SESSION['wpsc_zipcode'] != '')) {
			$zipvalue = $_SESSION['wpsc_zipcode'];
			$color = '#000';
	} else {
		$zipvalue = 'Your Zipcode';
		$_SESSION['wpsc_zipcode'] = '';
		$color = '#999';
	}
	
		$uses_zipcode = false;
		$custom_shipping = get_option('custom_shipping_options');
		foreach((array)$custom_shipping as $shipping) {
		  if($wpsc_shipping_modules[$shipping]->needs_zipcode == true) {
		    $uses_zipcode = true;
		  }
		}
	
	if($uses_zipcode == true) {
		$output .= " <input type='text' style='color:".$color.";' onclick='if (this.value==\"Your Zipcode\") {this.value=\"\";this.style.color=\"#000\";}' onblur='if (this.value==\"\") {this.style.color=\"#999\"; this.value=\"Your Zipcode\"; }' value='".$zipvalue."' size='10' name='zipcode' id='zipcode'>";
	}
	return $output;
}








/**
 * The WPSC Checkout class
 */
class wpsc_checkout {
	// The checkout loop variables
	var $checkout_items = array();
	var $checkout_item;
	var $checkout_item_count = 0;
	var $current_checkout_item = -1;
	var $in_the_loop = false;
   
	/**
	* wpsc_checkout method, gets the tax rate as a percentage, based on the selected country and region
	* @access public
	*/
  function wpsc_checkout() {
    global $wpdb;
    $this->checkout_items = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}collect_data_forms` WHERE `active` = '1' ORDER BY `order`;");
    $this->checkout_item_count = count($this->checkout_items);
  }
  
  function form_name() {
		return $this->checkout_item->name;
	}  
  
  function form_element_id() {
		return 'wpsc_checkout_form_'.$this->checkout_item->id;
	}  
	
  function form_field() {
    //global $wpdb;
    	switch($this->checkout_item->type) {
				case "address":
				case "delivery_address":
				case "textarea":
				$output = "<textarea id='".$this->form_element_id()."' name='collected_data[{$this->checkout_item->id}]'>".$_SESSION['collected_data'][$this->checkout_item->id]."</textarea>";
				break;
				
				case "country":
				$output = wpsc_country_region_list($this->checkout_item->id , false, $_SESSION['selected_country'], $_SESSION['selected_region']);
				break;

				case "delivery_country":
				$country_name = $wpdb->get_var("SELECT `country` FROM `{$wpdb->prefix}currency_list` WHERE `isocode`='".$_SESSION['delivery_country']."' LIMIT 1");
				$output = "<input type='hidden' name='collected_data[{$this->checkout_item->id}]' value='".$_SESSION['delivery_country']."'>".$country_name." ";
				break;
				
				case "text":
				case "city":
				case "delivery_city":
				case "email":
				case "coupon":
				default:
				$output = "<input type='text' id='".$this->form_element_id()."' class='text' value='".$_SESSION['collected_data'][$this->checkout_item->id]."' name='collected_data[{$this->checkout_item->id}]' />";
				break;
			}
    return $output;
	}
  
  function validate_forms() {
   global $wpdb;
   $any_bad_inputs = false;
		foreach($_POST['collected_data'] as $value_id => $value) {
			$form_sql = "SELECT * FROM `".$wpdb->prefix."collect_data_forms` WHERE `id` = '$value_id' LIMIT 1";
			$form_data = $wpdb->get_results($form_sql,ARRAY_A);
			$form_data = $form_data[0];
			
			$bad_input = false;
			if(($form_data['mandatory'] == 1) || ($form_data['type'] == "coupon")) {
				switch($form_data['type']) {
					case "email":
					if(!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/",$value)) {
						$any_bad_inputs = true;
						$bad_input = true;
					}
					break;

					case "delivery_country":
					break;

					case "country":
					break;
					
					default:
					if($value == null) {
						$any_bad_inputs = true;
						$bad_input = true;
					}
					break;
				}
				if($bad_input === true) {
					$bad_input_message[] = TXT_WPSC_PLEASEENTERAVALID . " " . strtolower($form_data['name']) . ".";
				}
			}
		}
		return array('is_valid' => !$any_bad_inputs, 'error_messages' => $bad_input_message);
  }
  
  
  /**
	 * checkout loop methods
	*/ 
  
  function next_checkout_item() {
		$this->current_checkout_item++;
		$this->checkout_item = $this->checkout_items[$this->current_checkout_item];
		return $this->checkout_item;
	}

  
  function the_checkout_item() {
		$this->in_the_loop = true;
		$this->checkout_item = $this->next_checkout_item();
		if ( $this->current_checkout_item == 0 ) // loop has just started
			do_action('wpsc_checkout_loop_start');
	}

	function have_checkout_items() {
		if ($this->current_checkout_item + 1 < $this->checkout_item_count) {
			return true;
		} else if ($this->current_checkout_item + 1 == $this->checkout_item_count && $this->checkout_item_count > 0) {
			do_action('wpsc_checkout_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_checkout_items();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_checkout_items() {
		$this->current_checkout_item = -1;
		if ($this->checkout_item_count > 0) {
			$this->checkout_item = $this->checkout_items[0];
		}
	}    
  
}
?>