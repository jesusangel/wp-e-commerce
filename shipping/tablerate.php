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
				$output.="<tr class='rate_row'><td><i style='color: grey;'>".TXT_WPSC_IF_PRICE_IS."</i><input type='text' name='layer[]' value='$key' size='10'><i style='color: grey;'> ".TXT_WPSC_AND_ABOVE."</i></td><td><input type='text' value='{$shipping}' name='shipping[]'  size='10'>&nbsp;&nbsp;<a href='#' class='delete_button' >".TXT_WPSC_DELETE."</a></td></tr>";
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
		if ($_POST['checkpage'] == 'table') {
			update_option('table_rate_layers',$new_layer);
		}
		return true;
	}
	
	function getQuotes() {
		global $wpdb, $wpsc_cart;
		$shopping_cart = $_SESSION['nzshpcrt_cart'];

		if(is_object($wpsc_cart)) {
			$price = $wpsc_cart->calculate_subtotal();
		}
		//$price = nzshpcrt_overall_total_price();
		$layers = get_option('table_rate_layers');
		
		//echo "<pre>".print_r($layers,true)."</pre>";
		
		if ($layers != '') {
			$layers = array_reverse($layers,true);
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
      $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='$product_id' LIMIT 1";
      $product_list = $wpdb->get_row($sql,ARRAY_A) ;
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
	}
	
	function get_cart_shipping($total_price, $weight) {
		$layers = get_option('table_rate_layers');
		if ($layers != '') {
			$layers = array_reverse($layers,true);
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