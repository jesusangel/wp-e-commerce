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
		global $wpdb, $wpsc_cart;
		if (isset($_POST['country'])) {
			$country = $_POST['country'];
			$_SESSION['wpsc_delivery_country'] = $country;
		} else {
			$country = $_SESSION['wpsc_delivery_country'];
		}
		
		
		if (get_option('base_country') != $country) {
			$results = $wpdb->get_var("SELECT `continent` FROM `{$wpdb->prefix}currency_list` WHERE `isocode` IN('{$country}') LIMIT 1");
			$flatrates = get_option('flat_rates');
			
			if ($flatrates != '') {
					
				if($_SESSION['quote_shipping_method'] == $this->internal_name) {
					if($_SESSION['quote_shipping_option'] != "Flat Rate") {
						$_SESSION['quote_shipping_option'] = null;
					}
				}
			  
				return array("Flat Rate"=>(float)$flatrates[$results]);
			}
		} else {
			$flatrates = get_option('flat_rates');

			switch($country) {
			  case 'NZ':
				$shipping_quotes["North Island"] = (float)$flatrates['northisland'];
				$shipping_quotes["South Island"] = (float)$flatrates['southisland'];
			  break;
			  
			  case 'US':
				$shipping_quotes["Continental 48 States"] = (float)$flatrates['continental'];
				$shipping_quotes["All 50 States"] = (float)$flatrates['all'];
			  break;
			  
			  default:
				$shipping_quotes["Local Shipping"] = (float)$flatrates['local'];
			  break;
			}
			if($_SESSION['quote_shipping_method'] == $this->internal_name) {
			  $shipping_options = array_keys($shipping_quotes);
			  if(array_search($_SESSION['quote_shipping_option'], $shipping_options) === false) {
					$_SESSION['quote_shipping_option'] = null;
			  }
			}
			
			return $shipping_quotes;
		}
	}
	
	
	function get_item_shipping($unit_price, $quantity, $weight, $product_id) {
		global $wpdb;
    if(is_numeric($product_id) && (get_option('do_not_use_shipping') != 1)) {
			$country_code = $_SESSION['wpsc_delivery_country'];
      $product_list = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}product_list` WHERE `id`='{$product_id}' LIMIT 1",ARRAY_A);
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
	
	function get_cart_shipping($total_price, $weight) {
	  return $output;
	}
}
$flatrate = new flatrate();
$wpsc_shipping_modules[$flatrate->getInternalName()] = $flatrate;