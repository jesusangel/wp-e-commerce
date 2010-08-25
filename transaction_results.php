<?php
global $wpdb, $user_ID, $nzshpcrt_gateways, $sessionid, $cart_log_id;
//$curgateway = get_option('payment_gateway');

if(isset($_GET['sessionid'])) {
	$sessionid = $_GET['sessionid'];
}
if(!isset($_GET['sessionid']) && isset($_GET['ms']) ){
	$sessionid = $_GET['ms'];
}
if(isset($_GET['gateway']) && $_GET['gateway'] == 'google'){
	wpsc_google_checkout_submit();
	unset($_SESSION['wpsc_sessionid']);
}elseif(isset($_GET['gateway']) && $_GET['gateway'] == 'noca'){
	wpsc_submit_checkout();
}
if($_SESSION['wpsc_previous_selected_gateway'] == 'paypal_certified'){
	$sessionid = $_SESSION['paypalexpresssessionid'];
}

//exit("test!");
$errorcode = '';
$transactid = '';
if(isset($_REQUEST['eway']) && $_REQUEST['eway']=='1') {
	$sessionid = $_GET['result'];
}elseif(isset($_REQUEST['eway']) && $_REQUEST['eway']=='0'){
	echo $_SESSION['eway_message'];
}elseif (isset($_REQUEST['payflow'])&& $_REQUEST['payflow']=='1') {	
	echo $_SESSION['payflow_message'];
	$_SESSION['payflow_message']='';
}
	//exit('getting here?<pre>'.print_r($_SESSION['wpsc_previous_selected_gateway'], true).'</pre>'.get_option('payment_gateway'));
if($_session['wpsc_previous_selected_gateway'] == 'paypal_certified'){
	echo $_session['paypalexpressmessage'];

} else {
	if($_SESSION['wpsc_previous_selected_gateway']== 'dps') {
		$sessionid = decrypt_dps_response();
		//exit($sessionid);
		if($sessionid != ''){
		//exit('<pre>'.print_r($sessionid, true).'</pre>');
			transaction_results($sessionid, true); 
		}else{
			_e('Sorry your transaction was not accepted.<br /><a href='.get_option("shopping_cart_url").'>Click here to go back to checkout page.</a>');
		}
	} else {
		//exit('<pre>sess - '.print_r($_SESSION, true).'</pre>');
		echo transaction_results($sessionid, true);
	}
	$cart_log_id = $wpdb->get_var( "SELECT `id` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1" );
}
?>