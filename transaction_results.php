<?php
global $wpdb, $user_ID;
//$curgateway = get_option('payment_gateway');
$sessionid = $_GET['sessionid'];

if(get_option('payment_gateway') == 'paypal_certified'){
	$sessionid = $_SESSION['paypalexpresssessionid'];
}
$errorcode = '';
$transactid = '';
if($_REQUEST['eway']=='1') {
 	echo $_SESSION['eway_message'];
 	$_SESSION['eway_message']='';
} else if ($_REQUEST['payflow']=='1') {	
	echo $_SESSION['payflow_message'];
	$_SESSION['payflow_message']='';
}
if(get_option('payment_gateway') == 'paypal_certified'){
	echo $_SESSION['paypalExpressMessage'];
} else {
	if(function_exists('decrypt_dps_response') && !is_numeric($sessionid)) {
		$sessionid = decrypt_dps_response();
		transaction_results($sessionid); 
	} else {
		echo transaction_results($sessionid, true);
	}
}
?>