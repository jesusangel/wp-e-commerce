<?php
class weightrate {
	var $internal_name, $name;
	function weightrate () {
		$this->internal_name = "weightrate";
		$this->name="Weight Rate";
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
	//	$output ="<table>";
		$output.="<tr><th>".__('Total weight <br />(<abbr alt="You must enter the weight here in pounds, regardless of what you used on your products" title="You must enter the weight here in pounds, regardless of what you used on your products">in Pounds</abbr>)', 'wpsc')."</th><th>".__('Shipping Price', 'wpsc')."</th></tr>";
		$layers = get_option("weight_rate_layers");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr class='rate_row'><td >";
				$output .="<i style='color: grey;'>".__('If weight is ', 'wpsc')."</i><input type='text' value='$key' name='weight_layer[]'size='4'><i style='color: grey;'>".__(' and above', 'wpsc')."</i></td><td>".wpsc_get_currency_symbol()."<input type='text' value='{$shipping}' name='weight_shipping[]' size='4'>&nbsp;&nbsp;<a href='#' class='delete_button' >".__('Delete', 'wpsc')."</a></td></tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='weight'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a style='cursor:pointer;' id='addweightlayer' >Add Layer</a></td></tr>";
	//	$output .="</table>";
		return $output;
	}
	
	function submit_form() {
		if (!isset($_POST['weight_layer'])) $_POST['weight_layer'] = '';
		if (!isset($_POST['weight_shipping'])) $_POST['weight_shipping'] = '';
		$layers = (array)$_POST['weight_layer'];
		$shippings = (array)$_POST['weight_shipping'];
		if ($shippings != ''){
			foreach($shippings as $key => $price) {
				if ($price == '') {
					unset($shippings[$key]);
					unset($layers[$key]);
				} else {
					$new_layer[$layers[$key]] = $price;
				}
			}
		}
		if ($_POST['checkpage'] == 'weight') {
			update_option('weight_rate_layers',$new_layer);
		}
		return true;
	}
	
	function getQuote() {
		global $wpdb;
		$weight = wpsc_cart_weight_total();
		$layers = get_option('weight_rate_layers');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					return array("Weight Rate"=>$shipping);
				}
			}
		
			return array("Weight Rate"=>array_shift($layers));
		}
	}
	
	function get_item_shipping($unit_price, $quantity, $weight, $product_id) {
	  return 0;
	}
	
	function get_cart_shipping($total_price, $weight) {
		$layers = get_option('weight_rate_layers');
		if ($layers != '') {
			krsort($layers);
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					$output = $shipping;
				}
			}
		}
	  return $output;
	}
}
$weightrate = new weightrate();
$wpsc_shipping_modules[$weightrate->getInternalName()] = $weightrate;