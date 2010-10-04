<?php
/**
 * This is the Paypal Payments Standard 2.0 Gateway. 
 * It uses the wpsc_merchant class as a base class which is handy for collating user details and cart contents.
 */
 
 /*
  * This is the gateway variable $nzshpcrt_gateways, it is used for displaying gateway information on the wp-admin pages and also
  * for internal operations.
  */
$nzshpcrt_gateways[$num] = array(
	'name' => 'Paypal Payments Standard 2.0',
	'api_version' => 2.0,
	'class_name' => 'wpsc_merchant_paypal_standard',
	'has_recurring_billing' => true,
	'wp_admin_cannot_cancel' => true,
	'requirements' => array(
		/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		'php_version' => 4.3,
		 /// for modules that may not be present, like curl
		'extra_modules' => array()
	),
	
	// this may be legacy, not yet decided
	'internalname' => 'wpsc_merchant_paypal_standard',

	// All array members below here are legacy, and use the code in paypal_multiple.php
	'form' => 'form_paypal_multiple',
	'submit_function' => 'submit_paypal_multiple',
	'payment_type' => 'paypal',
	'supported_currencies' => array(
		'currency_list' =>  array('AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'),
		'option_name' => 'paypal_curcode'
	)
);



/**
	* WP eCommerce Paypal Standard Merchant Class
	*
	* This is the paypal standard merchant class, it extends the base merchant class
	*
	* @package wp-e-commerce
	* @since 3.7.6
	* @subpackage wpsc-merchants
*/
class wpsc_merchant_paypal_standard extends wpsc_merchant {
  var $name = 'Paypal Payments Standard';
  var $paypal_ipn_values = array();

	/**
	* construct value array method, converts the data gathered by the base class code to something acceptable to the gateway
	* @access public
	*/
	function construct_value_array() {
		//$collected_gateway_data
		$paypal_vars = array();
		// Store settings to be sent to paypal
		$paypal_vars += array(
			'business' => get_option('paypal_multiple_business'),
			'return' => add_query_arg('sessionid', $this->cart_data['session_id'], $this->cart_data['transaction_results_url']),
			'cancel_return' => $this->cart_data['transaction_results_url'],
			'notify_url' => add_query_arg('gateway', 'wpsc_merchant_paypal_standard', $this->cart_data['notification_url']), 
			'rm' => '2',
			'currency_code' => $this->cart_data['store_currency'],
			'lc' => $this->cart_data['store_currency'],
			'bn' => $this->cart_data['software_name'],
			'no_shipping' => (int)(bool)get_option('paypal_ship'),
			'no_note' => '1',
			'charset' => 'utf-8'
			);
			
		if(get_option('address_override') == 1) {
			$paypal_vars += array(
				'address_override' => '1'
			);
		}

		// User settings to be sent to paypal
		$paypal_vars += array(
			'email' => $this->cart_data['email_address'],
			'first_name' => $this->cart_data['shipping_address']['first_name'],
			'last_name' => $this->cart_data['shipping_address']['last_name'],
			'address1' => $this->cart_data['shipping_address']['address'],
			'city' => $this->cart_data['shipping_address']['city'],
			'country' => $this->cart_data['shipping_address']['country'],
			'zip' => $this->cart_data['shipping_address']['post_code']
			);
		if($this->cart_data['shipping_address']['state'] != '') {
			$paypal_vars += array(
				'state' => $this->cart_data['shipping_address']['state']
			);
		}


		// Order settings to be sent to paypal
		$paypal_vars += array(
			//'tax' => '',
			//'custom' => '',
			'invoice' => $this->cart_data['session_id']
		);

		if ($this->cart_data['has_discounts']) {
			// Pass a cart discount to PayPal
			$paypal_vars += array(
					'discount_amount_cart' => $this->cart_data['cart_discount_value'],
					'tax_cart' => $this->cart_data['cart_tax']
			);
		}

			if($this->cart_data['is_subscription'] == true) {


				$reprocessed_cart_data['shopping_cart'] = array(
					'is_used' => false,
					'price' => 0,
					'length' => 1,
					'unit' => 'd',
					'times_to_rebill' => 1
				);
				
				$reprocessed_cart_data['subscription'] = array(
					'is_used' => false,
					'price' => 0,
					'length' => 1,
					'unit' => 'D',
					'times_to_rebill' => 1
				);
				
				foreach($this->cart_items as $cart_row) {
					if($cart_row['is_recurring'] == true) {
						$reprocessed_cart_data['subscription']['is_used'] = true;
						$reprocessed_cart_data['subscription']['price'] = $cart_row['price'];
						$reprocessed_cart_data['subscription']['length'] = $cart_row['recurring_data']['rebill_interval']['length'];
						$reprocessed_cart_data['subscription']['unit'] = strtoupper($cart_row['recurring_data']['rebill_interval']['unit']);
						$reprocessed_cart_data['subscription']['times_to_rebill'] =$cart_row['recurring_data']['times_to_rebill'];

					} else {
						$item_cost = ($cart_row['price'] + $cart_row['shipping'] + $cart_row['tax']) + $cart_row['quantity'];
						if($item_cost > 0) {
							$reprocessed_cart_data['shopping_cart']['price'] += $item_cost;
							$reprocessed_cart_data['shopping_cart']['is_used'] = true;
						}
					}
					
					$paypal_vars += array(
						"item_name" => __('Your Subscription', 'wpsc'),
						"src" => "1" // I fail to see the point of sending a subscription to paypal as a subscription if it does not recur, if (src == 0) then (this == underfeatured waste of time)
					);


					// this can be false, we don't need to have additional items in the cart
					if($reprocessed_cart_data['shopping_cart']['is_used'] == true) {
						$paypal_vars += array(
							"a1" => $this->format_price($reprocessed_cart_data['shopping_cart']['price']),
							"p1" => $reprocessed_cart_data['shopping_cart']['length'],
							"t1" => $reprocessed_cart_data['shopping_cart']['unit'],
						);
					}
					
					//we need at least one subscription product,  if we are in thise piece of code and this is not true, something is rather wrong
					if($reprocessed_cart_data['subscription']['is_used'] == true) {
						$paypal_vars += array(
							"a3" => $this->format_price($reprocessed_cart_data['subscription']['price']),
							"p3" => $reprocessed_cart_data['subscription']['length'],
							"t3" => $reprocessed_cart_data['subscription']['unit'],
						);
						// If the srt value for the number of times to rebill is not greater than 1, paypal won't accept the transaction.
						if($reprocessed_cart_data['subscription']['times_to_rebill'] > 1) {
							$paypal_vars += array("srt" => $reprocessed_cart_data['subscription']['times_to_rebill']);
						}
					}


				
				}

				
			} else {
				// Stick the cart item values together here
				
				$i = 1;
				foreach($this->cart_items as $cart_row) {
					$paypal_vars += array(
						"item_name_$i" => $cart_row['name'],
						"amount_$i" => $this->format_price($cart_row['price']),
						"quantity_$i" => $cart_row['quantity'],
						"item_number_$i" => $cart_row['product_id'],
						"shipping_$i" => $this->format_price($cart_row['shipping']), // additional shipping for the the (first item / total of the items)
						"shipping2_$i" => $this->format_price($cart_row['shipping']), // additional shipping beyond the first item
					);
					// Don't pass item tax amounts if cart has discount
					if (!$this->cart_data['has_discounts']) {
						$paypal_vars += array(
							"tax_$i" => $this->format_price($cart_row['tax'])
						);
					}
					++$i;
				}
				$paypal_vars += array(
				'handling_1' => $this->cart_data['base_shipping'],
				);
			}

		// Payment Type settings to be sent to paypal

		if($this->cart_data['is_subscription'] == true) {
			$paypal_vars += array(
				'cmd'=> '_xclick-subscriptions'
			);
		} else {
			$paypal_vars += array(
				'upload' => '1',
				'cmd' => '_ext-enter',
				'redirect_cmd' => '_cart'
			);
		}

		$this->collected_gateway_data = $paypal_vars;
	}
	
	/**
	* submit method, sends the received data to the payment gateway
	* @access public
	*/
	function submit() {
		$name_value_pairs = array();
		foreach($this->collected_gateway_data as $key=>$value) {
			//$output .= $key.'='.urlencode($value).$amp;
			$name_value_pairs[]= $key.'='.urlencode($value);
		}
		$gateway_values =  implode('&', $name_value_pairs);

		
		if(defined('WPSC_ADD_DEBUG_PAGE') and (WPSC_ADD_DEBUG_PAGE == true) ) {
			echo "<a href='".get_option('paypal_multiple_url')."?".$gateway_values."'>Test the URL here</a>";
			//echo "<pre>".print_r($this->cart_items,true)."</pre>";
			echo "<pre>".print_r($this->collected_gateway_data,true)."</pre>";
			exit();
		}
		header("Location: ".get_option('paypal_multiple_url')."?".$gateway_values);
		exit();
	}


	/**
	* parse_gateway_notification method, receives data from the payment gateway
	* @access private
	*/
	function parse_gateway_notification() {
		/// PayPal first expects the IPN variables to be returned to it within 30 seconds, so we do this first.
		$paypal_url = get_option('paypal_multiple_url');
		$received_values = array();
		$received_values['cmd'] = '_notify-validate';
    $received_values += $_POST;
		$options = array(
			'timeout' => 5,
			'body' => $received_values,
			'user-agent' => ('WP e-Commerce/'.WPSC_PRESENTABLE_VERSION)
		);
		
		$response = wp_remote_post($paypal_url, $options);
		
		if(strpos($response['body'], 'VERIFIED') !== false) {
			$this->paypal_ipn_values = $received_values;
			$this->session_id = $received_values['invoice'];
			$this->set_purchase_processed_by_sessionid(3);
		} else {
			exit("IPN Request Failure");
		}
	}

	/**
	* process_gateway_notification method, receives data from the payment gateway
	* @access public
	*/
	function process_gateway_notification() {
	  // Compare the received store owner email address to the set one
		if(strtolower($this->paypal_ipn_values['receiver_email']) == strtolower(get_option('paypal_multiple_business'))) {
			switch($this->paypal_ipn_values['txn_type']) {
				case 'cart':
				case 'express_checkout':
					if((float)$this->paypal_ipn_values['mc_gross'] == (float)$this->cart_data['total_price']) {
						$this->set_transaction_details($this->paypal_ipn_values['txn_id'], 3);
						transaction_results($this->cart_data['session_id'],false);
					}
				break;

				case 'subscr_signup':
				case 'subscr_payment':
					$this->set_transaction_details($this->paypal_ipn_values['subscr_id'], 3);
					foreach($this->cart_items as $cart_row) {
						if($cart_row['is_recurring'] == true) {
							do_action('wpsc_activate_subscription', $cart_row['cart_item_id'], $this->paypal_ipn_values['subscr_id']);
						}
					}
					transaction_results($this->cart_data['session_id'],false);
				break;
				
				case 'subscr_cancel':
				case 'subscr_eot':
				case 'subscr_failed':
					foreach($this->cart_items as $cart_row) {
						$altered_count = 0;
						if((bool)$cart_row['is_recurring'] == true) {
							$altered_count++;
							wpsc_update_cartmeta($cart_row['cart_item_id'], 'is_subscribed', 0);
						}
					}
				break;
				
				default:
				break;
			}
		}

		$message = "
		{$this->paypal_ipn_values['receiver_email']} => ".get_option('paypal_multiple_business')."
		{$this->paypal_ipn_values['txn_type']}
		{$this->paypal_ipn_values['mc_gross']} => {$this->cart_data['total_price']}
		{$this->paypal_ipn_values['txn_id']}

		".print_r($this->cart_items, true)."
		{$altered_count}
		";
	}



	function format_price($price) {
		$paypal_currency_code = get_option('paypal_curcode');
		switch($paypal_currency_code) {
			case "JPY":
			$decimal_places = 0;
			break;

			case "HUF":
			$decimal_places = 0;

			default:
			$decimal_places = 2;
			break;
		}
		$price = number_format(sprintf("%01.2f",$price),$decimal_places,'.','');
		return $price;
	}


	
}





/**
 * submit_paypal_multiple function.
 * 
 * Use this for now, but it will eventually be replaced with a better form API for gateways
 * @access public
 * @return void
 */
function submit_paypal_multiple(){
  if(isset($_POST['paypal_multiple_business'])) {
    update_option('paypal_multiple_business', $_POST['paypal_multiple_business']);
	}
    
  if(isset($_POST['paypal_multiple_url'])) {
    update_option('paypal_multiple_url', $_POST['paypal_multiple_url']);
	}
    
  if(isset($_POST['paypal_curcode'])) {
    update_option('paypal_curcode', $_POST['paypal_curcode']);
	}
    
  if(isset($_POST['paypal_curcode'])) {
    update_option('paypal_curcode', $_POST['paypal_curcode']);
	}
    
  if(isset($_POST['paypal_ipn'])) {
    update_option('paypal_ipn', (int)$_POST['paypal_ipn']);
	}

  if(isset($_POST['address_override'])) {
    update_option('address_override', (int)$_POST['address_override']);
	}
  if(isset($_POST['paypal_ship'])) {
    update_option('paypal_ship', (int)$_POST['paypal_ship']);
	}  
    
  if (!isset($_POST['paypal_form'])) $_POST['paypal_form'] = array();
  foreach((array)$_POST['paypal_form'] as $form => $value) {
    update_option(('paypal_form_'.$form), $value);
	}
	
  return true;
}



/**
 * form_paypal_multiple function.
 *
 * Use this for now, but it will eventually be replaced with a better form API for gateways 
 * @access public
 * @return void
 */
function form_paypal_multiple() {
  global $wpdb, $wpsc_gateways;
  $output = "
  <tr>
      <td>Username:
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_business')."' name='paypal_multiple_business' />
      </td>
  </tr>
  <tr>
      <td>Url:
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_url')."' name='paypal_multiple_url' /> <br />
   
      </td>
  </tr>
  ";
  
  
	$paypal_ipn = get_option('paypal_ipn');
	$paypal_ipn1 = "";
	$paypal_ipn2 = "";
	switch($paypal_ipn) {
		case 0:
		$paypal_ipn2 = "checked ='checked'";
		break;
		
		case 1:
		$paypal_ipn1 = "checked ='checked'";
		break;
	}
	$paypal_ship = get_option('paypal_ship');
	$paypal_ship1 = "";
	$paypal_ship2 = "";	
	switch($paypal_ship){
		case 1:
		$paypal_ship1 = "checked='checked'";
		break;
		
		case 0:
		default:
		$paypal_ship2 = "checked='checked'";
		break;
	
	}
	$address_override = get_option('address_override');
	$address_override1 = "";
	$address_override2 = "";
	switch($address_override) {
		case 1:
		$address_override1 = "checked ='checked'";
		break;
		
		case 0:
		default:
		$address_override2 = "checked ='checked'";
		break;
	}
	$output .= "
   <tr>
     <td>IPN :
     </td>
     <td>
       <input type='radio' value='1' name='paypal_ipn' id='paypal_ipn1' ".$paypal_ipn1." /> <label for='paypal_ipn1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='paypal_ipn' id='paypal_ipn2' ".$paypal_ipn2." /> <label for='paypal_ipn2'>".__('No', 'wpsc')."</label>
     </td>
  </tr>
  <tr>
     <td style='padding-bottom: 0px;'>Send shipping details:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='paypal_ship' id='paypal_ship1' ".$paypal_ship1." /> <label for='paypal_ship1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='paypal_ship' id='paypal_ship2' ".$paypal_ship2." /> <label for='paypal_ship2'>".__('No', 'wpsc')."</label>

  	</td>
  </tr>
  <tr>
  	<td colspan='2'>
  	<span  class='wpscsmall description'>
  	Note: If your checkout page does not have a shipping details section, or if you don't want to send Paypal shipping information. You should change Send shipping details option to No.</span>
  	</td>
  </tr>
  <tr>
     <td style='padding-bottom: 0px;'>
      Address Override:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='address_override' id='address_override1' ".$address_override1." /> <label for='address_override1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='address_override' id='address_override2' ".$address_override2." /> <label for='address_override2'>".__('No', 'wpsc')."</label>
     </td>
   </tr>
   <tr>
  	<td colspan='2'>
  	<span  class='wpscsmall description'>
  	This setting affects your PayPal purchase log. If your customers already have a PayPal account PayPal will try to populate your PayPal Purchase Log with their PayPal address. This setting tries to replace the address in the PayPal purchase log with the Address customers enter on your Checkout page.
  	</span>
  	</td>
   </tr>\n";



	$store_currency_data = $wpdb->get_row("SELECT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id` IN ('".absint(get_option('currency_type'))."')", ARRAY_A);
	$current_currency = get_option('paypal_curcode');
	if(($current_currency == '') && in_array($store_currency_data['code'], $wpsc_gateways['wpsc_merchant_paypal_standard']['supported_currencies']['currency_list'])) {
		update_option('paypal_curcode', $store_currency_data['code']);
		$current_currency = $store_currency_data['code'];
	}

		//	exit($current_currency.'<br />'.$store_currency_data['code']);
	if($current_currency != $store_currency_data['code']) {
		$output .= "
  <tr>
      <td colspan='2'><strong class='form_group'>".__('Currency Converter')."</td>
  </tr>
  <tr>
		<td colspan='2'>".sprintf(__('Your website uses <strong>%s</strong>. This currency is not supported by PayPal, please  select a currency using the drop down menu below. Buyers on your site will still pay in your local currency however we will send the order through to Paypal using the currency you choose below.', 'wpsc'), $store_currency_data['currency'])."</td>
		</tr>\n";
		
		$output .= "    <tr>\n";

		
		
		$output .= "    <td>Select Currency:</td>\n";
		$output .= "          <td>\n";
		$output .= "            <select name='paypal_curcode'>\n";

		$paypal_currency_list = $wpsc_gateways['wpsc_merchant_paypal_standard']['supported_currencies']['currency_list'];

		$currency_list = $wpdb->get_results("SELECT DISTINCT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `code` IN ('".implode("','",$paypal_currency_list)."')", ARRAY_A);

		foreach($currency_list as $currency_item) {
			$selected_currency = '';
			if($current_currency == $currency_item['code']) {
				$selected_currency = "selected='selected'";
			}
			$output .= "<option ".$selected_currency." value='{$currency_item['code']}'>{$currency_item['currency']}</option>";
		}
		$output .= "            </select> \n";
		$output .= "          </td>\n";
		$output .= "       </tr>\n";
	}

     
$output .= "
   <tr class='update_gateway' >
		<td colspan='2'>
			<div class='submit'>
			<input type='submit' value='".__('Update &raquo;', 'wpsc')."' name='updateoption'/>
		</div>
		</td>
	</tr>
	
	<tr class='firstrowth'>
		<td style='border-bottom: medium none;' colspan='2'>
			<strong class='form_group'>Forms Sent to Gateway</strong>
		</td>
	</tr>
   
    <tr>
      <td>
      First Name Field
      </td>
      <td>
      <select name='paypal_form[first_name]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_first_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Last Name Field
      </td>
      <td>
      <select name='paypal_form[last_name]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_last_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Address Field
      </td>
      <td>
      <select name='paypal_form[address]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_address'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      City Field
      </td>
      <td>
      <select name='paypal_form[city]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_city'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      State Field
      </td>
      <td>
      <select name='paypal_form[state]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_state'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Postal code/Zip code Field
      </td>
      <td>
      <select name='paypal_form[post_code]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_post_code'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Country Field
      </td>
      <td>
      <select name='paypal_form[country]'>
      ".nzshpcrt_form_field_list(get_option('paypal_form_country'))."
      </select>
      </td>
  </tr> ";
  
  return $output;
}

?>
