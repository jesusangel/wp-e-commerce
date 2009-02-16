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
// 		$output.="<tr><td colspan='2'>Layers: <a style='cursor:pointer;' onclick='addlayer()'>Add Layer</a></td></tr>";
		$output.="<tr><th>".TXT_WPSC_TOTALPRICE."</th><th>".TXT_WPSC_SHIPPING_PRICE."</th></tr>";
		$layers = get_option("table_rate_layers");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr><td><i style='color: grey;'>".TXT_WPSC_IF_PRICE_IS."</i><input type='text' name='layer[]' value='$key' size='10'><i style='color: grey;'> ".TXT_WPSC_AND_ABOVE."</i></td><td><input type='text' value='{$shipping}' name='shipping[]'></td></tr>";
			}
		}
		$output.="<input type='hidden' name='checkpage' value='table'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a style='cursor:pointer;' onclick='addlayer()'>Add Layer</a></td></tr>";
		return $output;
	}
	
	function submit_form() {
		$layers = $_POST['layer'];
		$shippings = $_POST['shipping'];
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
		if ($_POST['checkpage'] == 'table')
			update_option('table_rate_layers',$new_layer);
		return true;
	}
	
	function getQuote() {
		global $wpdb;
		$shopping_cart = $_SESSION['nzshpcrt_cart'];
// 		exit(print_r($shopping_cart,1));
		$price=0;
		foreach ((array)$shopping_cart as $cart_item) {
	    $price += $cart_item->quantity * calculate_product_price($cart_item->product_id, $cart_item->product_variations,'stay',$extras);
		}
		
		$layers = get_option('table_rate_layers');
		
		//echo "<pre>".print_r($layers,true)."</pre>";
		
		if ($layers != '') {
			$layers = array_reverse($layers,true);
			foreach ($layers as $key => $shipping) {
				if ($price >= (float)$key) {
				  //echo "<pre>$price $key</pre>";
					return array(array("Table Rate"=>$shipping));
					exit();
				}
			}
		
			return array(array("Table Rate"=>array_shift($layers)));
		}
	}
}
$tablerate = new tablerate();
$wpsc_shipping_modules[$tablerate->getInternalName()] = $tablerate;
?>