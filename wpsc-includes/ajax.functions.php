<?php
/**
 * WP eCommerce AJAX functions
 *
 * These are the WPSC AJAX functions
 *
 * @package wp-e-commerce
 * @subpackage wpsc-cart-classes 
 */
 
 
/**
	* add_to_cart function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_add_to_cart() {
  global $wpdb, $wpsc_cart;
  /// default values
	$default_parameters['variation_values'] = null;
	$default_parameters['quantity'] = 1;
	$default_parameters['provided_price'] = null;
	$default_parameters['comment']=null;
	$default_parameters['time_requested']=null;
	$default_parameters['meta']=null;
  
   /// sanitise submitted values
  $product_id = (int)$_POST['product_id'];
  foreach((array)$_POST['variation'] as $key => $variation) {
    $provided_parameters['variation_values'][(int)$key] = (int)$variation;
  }
  if($_POST['quantity'] > 0) {
		$provided_parameters['quantity'] = (int)$_POST['quantity'];
  }
  $parameters = array_merge($default_parameters, (array)$provided_parameters);
  $wpsc_cart->set_item($product_id,$parameters);
  if($_GET['ajax'] == 'true') {
		ob_start();
    if(get_option('wpsc_use_theme_engine') == TRUE) {	    
			include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/cart_widget.php");
	  } else {
			nzshpcrt_shopping_basket("", 4);	  
	  }
		$output = ob_get_contents();
		ob_end_clean();
		$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
    echo "jQuery('div.shopping-cart-wrapper').html('$output');\n";
    echo "wpsc_bind_to_events();\n";
		exit();
  }
}
// execute on POST and GET
if($_REQUEST['wpsc_ajax_action'] == 'add_to_cart') {
	add_action('init', 'wpsc_add_to_cart');
}



/**
	* empty cart function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_empty_cart() {
  global $wpdb, $wpsc_cart;
  $wpsc_cart->empty_cart();
  
  if($_REQUEST['ajax'] == 'true') {
		ob_start();
    if(get_option('wpsc_use_theme_engine') == TRUE) {	    
			include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/cart_widget.php");
	  } else {
			nzshpcrt_shopping_basket("", 4);	  
	  }
		$output = ob_get_contents();
		ob_end_clean();
		$output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
    echo "jQuery('div.shopping-cart-wrapper').html('$output');";
		exit();
  }
}
// execute on POST and GET
if($_REQUEST['wpsc_ajax_action'] == 'empty_cart') {
	add_action('init', 'wpsc_empty_cart');
}



/**
	* update quantity function, used through ajax and in normal page loading.
	* No parameters, returns nothing
*/
function wpsc_update_item_quantity() {
  global $wpdb, $wpsc_cart;
  if(is_numeric($_POST['key'])) {
    $key = (int)$_POST['key'];
		if($_POST['quantity'] > 0) {
		  // if the quantity is greater than 0, update the item;
		  $parameters['quantity'] = (int)$_POST['quantity'];
			$wpsc_cart->edit_item($key, $parameters);
		} else {
		  // if the quantity is 0, remove the item.
			$wpsc_cart->remove_item($key);
		}
  }
}
  
// execute on POST and GET
if($_REQUEST['wpsc_update_quantity'] == 'true') {
	add_action('init', 'wpsc_update_item_quantity');
}


?>