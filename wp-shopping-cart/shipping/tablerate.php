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
		$output.="<tr><th>Total price</th><th>Shipping price</th></tr>";
		$layers = get_option("table_rate_layers");
		if ($layers != '') {
			foreach($layers as $key => $shipping) {
				$output.="<tr><td><input type='text' value='$key' name='layer[]'> and above</td><td><input type='text' value='{$shipping}' name='shipping[]'></td></tr>";
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
		foreach ($shopping_cart as $item) {
			$price += $wpdb->get_var("SELECT price FROM {$wpdb->prefix}product_list WHERE id='{$item->product_id}'");
		}
		
		$layers = get_option('table_rate_layers');
		if ($layers != '') {
			$layers = array_reverse($layers);
			foreach ($layers as $key => $shipping) {
				if ($price >= (double)$key) {
					return array(array("Table Rate"=>$shipping));
				}
			}
		
			return array(array("Table Rate"=>array_shift($layers)));
		}
	}
}
$tablerate = new tablerate();
$wpsc_shipping_modules[$tablerate->getInternalName()] = $tablerate;
?>