<?php
global $wpdb;
?>
<div class="wrap">
<?php
$_SESSION['selected_country'] = 'US';
$_SESSION['delivery_country'] = 'US';
$_SESSION['delivery_region'] = '18';
$_SESSION['selected_region'] = '18';



//echo "<pre>".print_r($_SESSION,true)."</pre>";
$num = 0;
$products[$num]['id'] = 47;
$products[$num]['params']['variation_values'] = array(1 => 3);
$products[$num]['params']['quantity'] = 1;
$products[$num]['params']['provided_price'] = null;
$products[$num]['params']['comment']=null;
$products[$num]['params']['time_requested']=null;
$products[$num]['params']['meta']=null;

$num++;
$products[$num]['id'] = 5;
$products[$num]['params']['variation_values'] = array(1 => 3,2 => 5);
$products[$num]['params']['quantity'] = 1;
$products[$num]['params']['provided_price'] = null;
$products[$num]['params']['comment']=null;
$products[$num]['params']['time_requested']=null;
$products[$num]['params']['meta']=null;

$num++;
$products[$num]['id'] = 5;
$products[$num]['params']['variation_values'] = array(1 => 3,2 => 6);
$products[$num]['params']['quantity'] = 1;
$products[$num]['params']['provided_price'] = null;
$products[$num]['params']['comment']=null;
$products[$num]['params']['time_requested']=null;
$products[$num]['params']['meta']=null;
$num++;
$products[$num]['id'] = 5;
$products[$num]['params']['variation_values'] = array(1 => 3,2 => 6);
$products[$num]['params']['quantity'] = 1;
$products[$num]['params']['provided_price'] = null;
$products[$num]['params']['comment']=null;
$products[$num]['params']['time_requested']=null;
$products[$num]['params']['meta']=null;

$wpsc_cart = new wpsc_cart;
foreach($products as $product) {
  $wpsc_cart->set_item($product['id'],$product['params']);
}
echo $wpsc_cart->calculate_total_price();

echo "<br />";
echo "<br />";
echo "<pre>".print_r($wpsc_cart,true)."</pre>";
?>
</div>