<?php
class flatrate {
	var $internal_name, $name;
	function flatrate () {
		$this->internal_name = "flatrate";
		$this->name="Flat Rate";
		$this->is_external=false;
		return true;
	}
	
	function getId() {
// 		return $this->usps_id;
	}
	
	function setId($id) {
// 		$usps_id = $id;
// 		return true;
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	
	function getForm() {
		$shipping = get_option('flat_rates');
		$output = "<tr><td colspan='1'><strong>Base Local</strong></td>";
		
		switch(get_option('base_country')) {
		  case 'NZ':
			$output .= "<tr><td>South Island</td><td>$<input type='text' name='shipping[southisland]' value='{$shipping['southisland']}'></td></tr>";
			$output .= "<tr><td>North Island</td><td>$<input type='text' name='shipping[northisland]'  value='{$shipping['northisland']}'></td></tr>";
		  break;
		  
		  case 'US':
			$output .= "<tr><td>Continental 48 States</td><td>$<input type='text' name='shipping[continental]' value='{$shipping['continental']}'></td></tr>";
			$output .= "<tr><td>All 50 States</td><td>$<input type='text' name='shipping[all]'  value='{$shipping['all']}'></td></tr>";
		  break;
		  
		  default:
			$output .= "<td>$<input type='text' name='shipping[local]' value='{$shipping['local']}'></td></tr>";		  
		  break;
		  
		
		}
		
		
		if (get_option('base_country')=='NZ') {
		} else {
		}
		$output.= "<tr><td colspan='2'><strong>International</strong></td></tr>";
		$output .= "<tr><td>North America</td><td>$<input type='text' name='shipping[northamerica]'  value='{$shipping['northamerica']}'></td></tr>";
		$output .= "<tr><td>South America</td><td>$<input type='text' name='shipping[southamerica]'  value='{$shipping['southamerica']}'></td></tr>";
		$output .= "<tr><td>Asia and Pacific</td><td>$<input type='text' name='shipping[asiapacific]'  value='{$shipping['asiapacific']}'></td></tr>";
		$output .= "<tr><td>Europe</td><td>$<input type='text' name='shipping[europe]'  value='{$shipping['europe']}'></td></tr>";
		$output .= "<tr><td>Africa</td><td>$<input type='text' name='shipping[africa]'  value='{$shipping['africa']}'></td></tr>";
		return $output;
	}
	
	function submit_form() {
	  if($_POST['shipping'] != null) {
			$shipping = get_option('flat_rates');
			$submitted_shipping = $_POST['shipping'];
			update_option('flat_rates',array_merge($shipping, $submitted_shipping));
		}
		return true;
	}
	
	function getQuote($for_display = false) {
		global $wpdb;
		if (isset($_POST['country'])) {
			$country = $_POST['country'];
			$_SESSION['delivery_country'] = $country;
		} else {
			$country = $_SESSION['delivery_country'];
		}
		if (get_option('base_country') != $country) {
			$results = $wpdb->get_var("SELECT `continent` FROM `{$wpdb->prefix}currency_list` WHERE `isocode` IN('{$country}') LIMIT 1");
			$flatrates = get_option('flat_rates');
			
			if ($flatrates != '') {
			  if($for_display == true) {
			    // if it is for display, we need to add the per item shipping too
			    foreach((array)$_SESSION['nzshpcrt_cart'] as $cart_item) {
						$product_id = $cart_item->product_id;
						$quantity = $cart_item->quantity;
			      $flatrates[$results] += nzshpcrt_determine_item_shipping($product_id, $quantity, $country);
			    }
			  }
			  
			if($_SESSION['quote_shipping_method'] == $this->internal_name) {
			  if($_SESSION['quote_shipping_option'] != "Flat Rate") {
			    $_SESSION['quote_shipping_option'] = null;
			  }
			}
			  
				return array(array("Flat Rate"=>(float)$flatrates[$results]));
			}
		} else {
			$flatrates = get_option('flat_rates');
			if($for_display == true) {
				foreach((array)$_SESSION['nzshpcrt_cart'] as $cart_item) {
					$product_id = $cart_item->product_id;
					$quantity = $cart_item->quantity;
					$per_item_shipping += nzshpcrt_determine_item_shipping($product_id, $quantity, $country);
				}
			}
			switch($country) {
			  case 'NZ':
				$shipping_quotes["North Island"] = (float)$flatrates['northisland']+$per_item_shipping;
				$shipping_quotes["South Island"] = (float)$flatrates['southisland']+$per_item_shipping;
			  break;
			  
			  case 'US':
				$shipping_quotes["Continental 48 States"] = (float)$flatrates['continental']+$per_item_shipping;
				$shipping_quotes["All 50 States"] = (float)$flatrates['all']+$per_item_shipping;
			  break;
			  
			  default:
				$shipping_quotes["Local Shipping"] = (float)$flatrates['local']+$per_item_shipping;
			  break;
			}
			if($_SESSION['quote_shipping_method'] == $this->internal_name) {
			  $shipping_options = array_keys($shipping_quotes);
			  if(array_search($_SESSION['quote_shipping_option'], $shipping_options) === false) {
					$_SESSION['quote_shipping_option'] = null;
			  }
			}
			
			return array($shipping_quotes);
		}	
	}
}
$flatrate = new flatrate();
$wpsc_shipping_modules[$flatrate->getInternalName()] = $flatrate;