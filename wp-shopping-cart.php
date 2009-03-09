<?php
/*
Plugin Name:WP Shopping Cart
Plugin URI: http://www.instinct.co.nz
Description: A plugin that provides a WordPress Shopping Cart. Contact <a href='http://www.instinct.co.nz/?p=16#support'>Instinct Entertainment</a> for support. <br />Click here to to <a href='?wpsc_uninstall=ask'>Uninstall</a>.
Version: 3.6.10
Author: Instinct Entertainment
Author URI: http://www.instinct.co.nz/e-commerce/
*/
/**
 * WP eCommerce Main Plugin File
 * @package wp-e-commerce
*/
define('WPSC_VERSION', '3.6');
define('WPSC_MINOR_VERSION', '116');


define('WPSC_PRESENTABLE_VERSION', '3.6.10');

define('WPSC_DEBUG', false);





function wpsc_unserialize_shopping_cart() {
  global $wpsc_cart;
  $wpsc_cart = unserialize($_SESSION['wpsc_cart']);

}
// first plugin hook in wordpress
add_action('plugins_loaded','wpsc_unserialize_shopping_cart');



  
/*
 * This serializes the shopping cart variable as a backup in case the unserialized one gets butchered by various things
 */  
function wpsc_serialize_shopping_cart() {
  global $wpsc_start_time, $wpsc_cart;
  @$_SESSION['nzshpcrt_serialized_cart'] = serialize($_SESSION['nzshpcrt_cart']);
  
  $_SESSION['wpsc_cart'] = serialize($wpsc_cart);
  return true;
}  
add_action('shutdown','wpsc_serialize_shopping_cart');

/// OLD CODE INCLUDED HERE
include_once('wp-shopping-cart.old.php');
?>