<?php
class tablerate {
	var $internal_name, $name;
	function tablerate () {
		$this->internal_name = "tablerate";
		$this->name="Table Rate";
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
 	//	$output.="<table>";
		$output.="<tr><th>".__('Total Price', 'wpsc')."</th><th>".__('Shipping Price', 'wpsc')."</th></tr>";
		$layers = get_option("table_rate_layers");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr class='rate_row'>
							<td>
						
								<i style='color: grey;'>".__('If price is ', 'wpsc')."</i>
								<input type='text' name='layer[]' value='$key' size='4' />
								<i style='color: grey;'> ".__(' and above', 'wpsc')."</i>
							</td>
							<td>
								".wpsc_get_currency_symbol()."
								<input type='text' value='{$shipping}' name='shipping[]'	size='4'>
								&nbsp;&nbsp;<a href='#' class='delete_button' >".__('Delete', 'wpsc')."</a>
							
							</td>
						</tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='table'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a href='' style='cursor:pointer;' id='addlayer' >Add Layer</a></td></tr>";
	//	$output.="</table>";
		return $output;
	}
	
	function submit_form() {
		if (!isset($_POST['layer'])) $_POST['layer'] = '';
		
		$layers = (array)$_POST['layer'];
		$shippings = (array)$_POST['shipping'];
		if ($shippings != '') {
			foreach($shippings as $key => $price) {
				if ($price == '') {
					unset($shippings[$key]);
					unset($layers[$key]);
				} else {
					$new_layer[$layers[$key]] = $price;
				}
			}
		}
		if (!isset($_POST['checkpage'])) $_POST['checkpage'] = '';
		if ($_POST['checkpage'] == 'table') {
			update_option('table_rate_layers',$new_layer);
		}
		return true;
	}
	
	function getQuotes() {
		global $wpdb, $wpsc_cart;
		$shopping_cart = $_SESSION['nzshpcrt_cart'];

		if(is_object($wpsc_cart)) {
			$price = $wpsc_cart->calculate_subtotal(true);
		}
		//$price = nzshpcrt_overall_total_price();
		$layers = get_option('table_rate_layers');
		
		//echo "<pre>".print_r($layers,true)."</pre>";
		
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($price >= (float)$key) {
					//echo "<pre>$price $key</pre>";
					return array("Table Rate"=>$shipping);
					exit();
				}
			}
			return array("Table Rate"=>array_shift($layers));
		}
	}
	
	function getQuote() {
		return $this->getQuotes();
	}
		
	
	function get_item_shipping($unit_price, $quantity, $weight, $product_id) {
		global $wpdb;
		if(is_numeric($product_id) && (get_option('do_not_use_shipping') != 1) && ($_SESSION['quote_shipping_method'] == 'flatrate')) {
			if($cart_item->uses_shipping == true) {
				//if the item has shipping
				$shipping_values = $cart_item->meta[0]['shipping'];
				if($country_code == get_option('base_country')) {
					$additional_shipping = $shipping_values['local'];
				} else {
					$additional_shipping = $shipping_values['international'];
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
	}
	
	function get_cart_shipping($total_price, $weight) {
		$layers = get_option('table_rate_layers');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($total_price >= (float)$key) {
					$output = $shipping;
				}
			}
		}
		return $output;
	}
	
	
}
$tablerate = new tablerate();
$wpsc_shipping_modules[$tablerate->getInternalName()] = $tablerate;
?>