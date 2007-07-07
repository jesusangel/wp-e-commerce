<?php
global $wpdb, $user_ID;
$curgateway = get_option('payment_gateway');
$sessionid = $_GET['sessionid'];
$errorcode = '';
$transactid = '';

//$profile = new WP_User($user_ID);
//echo "<pre>".print_r($profile,true)."</pre>";

if(function_exists('decrypt_dps_response'))
  {
  $sessionid = decrypt_dps_response();
  }

echo transaction_results($sessionid, true);
?>