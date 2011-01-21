<?php

/**
 * WP eCommerce transaction results class
 *
 * This class is responsible for theming the transaction results page.
 *
 * @package wp-e-commerce
 * @since 3.8
 */
function wpsc_transaction_theme() {
	global $wpdb, $user_ID, $nzshpcrt_gateways, $sessionid, $cart_log_id, $errorcode;
	$errorcode = '';
	$transactid = '';

	if ( isset( $_GET['sessionid'] ) )
		$sessionid = $_GET['sessionid'];

	if ( !isset( $_GET['sessionid'] ) && isset( $_GET['ms'] ) )
		$sessionid = $_GET['ms'];

	if ( isset( $_GET['gateway'] ) && 'google' == $_GET['gateway'] ) {
		wpsc_google_checkout_submit();
		unset( $_SESSION['wpsc_sessionid'] );
	}

	if ( 'paypal_certified' == $_SESSION['wpsc_previous_selected_gateway'] )
		$sessionid = $_SESSION['paypalexpresssessionid'];

	if ( isset( $_REQUEST['eway'] ) && '1' == $_REQUEST['eway'] )
		$sessionid = $_GET['result'];
	elseif ( isset( $_REQUEST['eway'] ) && '0' == $_REQUEST['eway'] )
		echo $_SESSION['eway_message'];
	elseif ( isset( $_REQUEST['payflow'] ) && '1' == $_REQUEST['payflow'] ){
		echo $_SESSION['payflow_message'];
		$_SESSION['payflow_message'] = '';
	}
	
	// Replaces the ugly if else for gateways
	switch($_SESSION['wpsc_previous_selected_gateway']){
		case 'paypal_certified':
		case 'wpsc_merchant_paypal_express':
			if(($_SESSION['reshash']['ACK'] != 'Completed') && ( $_SESSION['reshash']['ACK'] != 'Success')) {
				echo $_SESSION['paypalExpressMessage'];
			}
		break;
		case 'dps':
			$sessionid = decrypt_dps_response();
		break;
	}
	
	if ( !empty($sessionid) ){
		$cart_log_id = $wpdb->get_var( "SELECT `id` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1" );
		return transaction_results( $sessionid, true );
	}else
		_e( 'Sorry your transaction was not accepted.<br /><a href=' . get_option( "shopping_cart_url" ) . '>Click here to go back to checkout page.</a>' );
	
	
}


/**
 * transaction_results function main function for creating the purchase reports, transaction results page, and email receipts
 * @access public
 *
 * @since 3.7
 * @param $sessionid (string) unique session id
 * @param echo_to_screen (boolean) whether to output the results or return them (potentially redundant)
 * @param $transaction_id (int) the transaction id
 */
function transaction_results( $sessionid, $echo_to_screen = true, $transaction_id = null ) {
	// Do we seriously need this many globals?
	global $wpdb, $wpsc_cart, $echo_to_screen, $purchase_log, $order_url; 
	global $message_html, $cart, $errorcode,$wpsc_purchlog_statuses, $wpsc_gateways;
	
	$wpec_taxes_controller = new wpec_taxes_controller();
	$is_transaction = false;
	$errorcode = 0;
	$purchase_log = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1", ARRAY_A );
	$order_status = $purchase_log['processed'];
	$curgateway = $purchase_log['gateway'];
	//new variable to check whether function is being called from resen_email
	if(isset($_GET['email_buyer_id']))
		$resend_email = true;
	else
		$resend_email = false;
		
	if( !is_bool( $echo_to_screen )  )
		$echo_to_screen = true;

	if ( is_numeric( $sessionid ) ) {
		if ( $echo_to_screen )
			echo apply_filters( 'wpsc_pre_transaction_results', '' );
		
		// New code to check whether transaction is processed, true if accepted false if pending or incomplete
		$is_transaction = wpsc_check_purchase_processed($purchase_log['processed']);
		$message_html = $message = stripslashes( get_option( 'wpsc_email_receipt' ) );
	
		if( $is_transaction ){
			$message = __('The Transaction was successful', 'wpsc')."\r\n".$message;
			$message_html = __('The Transaction was successful', 'wpsc')."<br />".$message;
		}
		// Checks for PayPal IPN
		if ( (!isset( $_GET['ipn_request'] ) || 'true' != $_GET['ipn_request']) 
			&& ((get_option( 'paypal_ipn' ) == 1)  && ('wpsc_merchant_paypal_standard' == $purchase_log['gateway'] )) ) {

			if ( $purchase_log == null ) {
				if ( (get_option( 'purch_log_email' ) != null) && ($purchase_log['email_sent'] != 1 || $resend_email) ) {
					$order_url = site_url( "/wp-admin/admin.php?page=wpsc-sales-logs&purchaselog_id=" . $purchase_log['id'] );
					wp_mail( get_option( 'purch_log_email' ), __( 'New pending order', 'wpsc' ), __( 'There is a new order awaiting processing:', 'wpsc' ) . $order_url, "From: " . get_option( 'return_email' ) . "" );
				}
				_e( 'We&#39;re Sorry, your order has not been accepted, the most likely reason is that you have insufficient funds.', 'wpsc' );

				return false;
			} else if (!$is_transaction) {
				_e( 'Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc' ) . "<p style='margin: 1em 0px 0px 0px;' >" . nl2br( stripslashes( get_option( 'payment_instructions' ) ) ) . "</p>";
				return;
			}
		}
		if ( !empty($purchase_log['shipping_country']) ) {
			$billing_country = $purchase_log['billing_country'];
			$shipping_country = $purchase_log['shipping_country'];
		} else {
			$country = $wpdb->get_var( "SELECT `value` FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id`=" . $purchase_log['id'] . " AND `form_id` = '" . get_option( 'country_form_field' ) . "' LIMIT 1" );
						
			$billing_country = $country;
			$shipping_country = $country;
		}

		$email = wpsc_get_buyers_email($purchase_log['id']);
		$previous_download_ids = array( );
		$product_list = $product_list_html = $report_product_list = '';
	
		$cart = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid` = '{$purchase_log['id']}'" , ARRAY_A );
		//echo '<pre>'.print_r($wpsc_cart,1).'</pre>';
		if ( ($cart != null) && ($errorcode == 0) ) {
			$link = array( );
			$total_shipping = '';
			foreach ( $cart as $row ) {
				if ( $purchase_log['email_sent'] != 1 )
					$wpdb->update(WPSC_TABLE_DOWNLOAD_STATUS, array('active' => '1'), array('cartid' => $row['id'], 'purchid'=>$purchase_log['id']) );
				do_action( 'wpsc_transaction_result_cart_item', array( "purchase_id" => $purchase_log['id'], "cart_item" => $row, "purchase_log" => $purchase_log ) );

				if ( $is_transaction ) {

					$download_data = $wpdb->get_results( "SELECT *
					FROM `" . WPSC_TABLE_DOWNLOAD_STATUS . "`
					WHERE `active`='1'
					AND `purchid`='" . $purchase_log['id'] . "'
					AND `cartid` = '" . $row['id'] . "'", ARRAY_A );

					if ( count( $download_data ) > 0 ) {
						foreach ( $download_data as $single_download ) {
							$file_data = get_post( $single_download['product_id'] );
							// if the uniqueid is not equal to null, its "valid", regardless of what it is
							if ( $single_download['uniqueid'] == null )
								$link[] = array( "url" => site_url( "?downloadid=" . $single_download['id'] ), "name" => $file_data->post_title );
							else
								$link[] = array( "url" => site_url( "?downloadid=" . $single_download['uniqueid'] ), "name" => $file_data->post_title );
							
						}
					} else {
						$order_status = $purchase_log['processed'];
					}
					$previous_download_ids[] = $download_data['id'];
				}

				do_action( 'wpsc_confirm_checkout', $purchase_log['id'] );

				$total = 0;
				$shipping = $row['pnp'] * $row['quantity'];
				$total_shipping += $shipping;

				$total += ( $row['price'] * $row['quantity']);
				$message_price = wpsc_currency_display( $total, array( 'display_as_html' => false ) );
				$message_price_html = wpsc_currency_display( $total );
				$shipping_price = wpsc_currency_display( $shipping, array( 'display_as_html' => false ) );

				if ( isset( $purchase['gateway'] ) && 'wpsc_merchant_testmode' != $purchase['gateway'] ) {
					if ( $gateway['internalname'] == $purch_data[0]['gateway'] )
						$gateway_name = $gateway['name'];
				} else {
					$gateway_name = "Manual Payment";
				}

				$variation_list = '';

				if ( !empty( $link ) ) {
					$additional_content = apply_filters( 'wpsc_transaction_result_content', array( "purchase_id" => $purchase_log['id'], "cart_item" => $row, "purchase_log" => $purchase_log ) );
					if ( !is_string( $additional_content ) ) {
						$additional_content = '';
					}
					$product_list .= " - " . $row['name'] . "  " . $message_price . " " . __( 'Click to download', 'wpsc' ) . ":";
					$product_list_html .= " - " . $row['name'] . "  " . $message_price_html . "&nbsp;&nbsp;" . __( 'Click to download', 'wpsc' ) . ":\n\r";
					foreach ( $link as $single_link ) {
						$product_list .= "\n\r " . $single_link["name"] . ": " . $single_link["url"] . "\n\r";
						$product_list_html .= "<a href='" . $single_link["url"] . "'>" . $single_link["name"] . "</a>\n";
					}
					$product_list .= $additional_content;
					$product_list_html .= $additional_content;
				} else {
				
					$product_list.= " - " . $row['quantity'] . " " . $row['name'] . "  " . $message_price . "\n\r";
					if ( $shipping > 0 )
						$product_list .= " - " . __( 'Shipping', 'wpsc' ) . ":" . $shipping_price . "\n\r";
					$product_list_html.= "\n\r - " . $row['quantity'] . " " . $row['name'] . "  " . $message_price_html . "\n\r";
					if ( $shipping > 0 )
						$product_list_html .= " &nbsp; " . __( 'Shipping', 'wpsc' ) . ":" . $shipping_price . "\n\r";
				}

				//add tax if included
				if($wpec_taxes_controller->wpec_taxes_isenabled() && $wpec_taxes_controller->wpec_taxes_isincluded())
				{
					$taxes_text = ' - - '.__('Tax Included', 'wpsc').': '.wpsc_currency_display( $row['tax_charged'], array( 'display_as_html' => false ) )."\n\r";
					$product_list .= $taxes_text;
					$product_list_html .= $taxes_text;
				}// if

				$report = get_option( 'wpsc_email_admin' );
				$report_product_list.= " - " . $row['name'] . "  " . $message_price . "\n\r";
			} // closes foreach cart as row

			// Decrement the stock here
			if ( $is_transaction )
				wpsc_decrement_claimed_stock( $purchase_log['id'] );

			if ( !empty($purchase_log['discount_data'])) {
				$coupon_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_COUPON_CODES . "` WHERE coupon_code='" . $wpdb->escape( $purchase_log['discount_data'] ) . "' LIMIT 1", ARRAY_A );
				if ( $coupon_data['use-once'] == 1 ) {
					$wpdb->update(WPSC_TABLE_COUPON_CODES, array('active' => '0', 'is-used' => '1'), array('id' => $coupon_data['id']) );
				}
			}

			$total_shipping += $purchase_log['base_shipping'];

			$total = $purchase_log['totalprice'];
			if ( $purchase_log['discount_value'] > 0 ) {
				$discount_email.= __( 'Discount', 'wpsc' ) . "\n\r: ";
				$discount_email .=$purchase_log['discount_data'] . ' : ' . wpsc_currency_display( $purchase_log['discount_value'], array( 'display_as_html' => false ) ) . "\n\r";
			}
			$total_price_email = '';
			$total_price_html = '';
			$total_tax_html = '';
			$total_tax = '';
			$total_shipping_html = '';
			$total_shipping_email = '';
			$total_shipping_email.= __( 'Total Shipping', 'wpsc' ) . ": " . wpsc_currency_display( $total_shipping, array( 'display_as_html' => false ) ) . "\n\r";
			$total_price_email.= __( 'Total', 'wpsc' ) . ": " . wpsc_currency_display( $total, array( 'display_as_html' => false ) ) . "\n\r";

			if ( $purchase_log['discount_value'] > 0 ) {
				$report.= $discount_email . "\n\r";
				$total_shipping_html.= __( 'Discount', 'wpsc' ) . ": " . wpsc_currency_display( $purchase_log['discount_value'], array( 'display_as_html' => false ) ) . "\n\r";
			}

			//only show total tax if tax is not included
			if($wpec_taxes_controller->wpec_taxes_isenabled() && !$wpec_taxes_controller->wpec_taxes_isincluded()){
				$total_tax_html .= __('Total Tax', 'wpsc').': '. wpsc_currency_display( $purchase_log['wpec_taxes_total'] )."\n\r";
				$total_tax .= __('Total Tax', 'wpsc').': '. wpsc_currency_display( $purchase_log['wpec_taxes_total'] , array( 'display_as_html' => false ) )."\n\r"; 		
			}
			$total_shipping_html.= __( 'Total Shipping', 'wpsc' ) . ": " . wpsc_currency_display( $total_shipping ) . "\n\r";
			$total_price_html.= __( 'Total', 'wpsc' ) . ": " . wpsc_currency_display( $total ) . "\n\r";
			$report_id = "Purchase # " . $purchase_log['id'] . "\n\r";
			
			if ( isset( $_GET['ti'] ) ) {
				$message.= "\n\r" . __( 'Your Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
				$message_html.= "\n\r" . __( 'Your Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
				$report.= "\n\r" . __( 'Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
			} 
			$message = str_replace( '%purchase_id%', $report_id, $message );
			$message = str_replace( '%product_list%', $product_list, $message );
			$message = str_replace( '%total_tax%', $total_tax, $message );
			$message = str_replace( '%total_shipping%', $total_shipping_email, $message );
			$message = str_replace( '%total_price%', $total_price_email, $message );
			$message = str_replace( '%shop_name%', get_option( 'blogname' ), $message );
			$message = str_replace( '%find_us%', $purchase_log['find_us'], $message );

			$report = str_replace( '%purchase_id%', $report_id, $report );
			$report = str_replace( '%product_list%', $report_product_list, $report );
			$report = str_replace( '%total_tax%', $total_tax, $report );
			$report = str_replace( '%total_shipping%', $total_shipping_email, $report );
			$report = str_replace( '%total_price%', $total_price_email, $report );
			$report = str_replace( '%shop_name%', get_option( 'blogname' ), $report );
			$report = str_replace( '%find_us%', $purchase_log['find_us'], $report );

			$message_html = str_replace( '%purchase_id%', $report_id, $message_html );
			$message_html = str_replace( '%product_list%', $product_list_html, $message_html );
			$message_html = str_replace( '%total_tax%', $total_tax_html, $message_html );
			$message_html = str_replace( '%total_shipping%', $total_shipping_html, $message_html );
			$message_html = str_replace( '%total_price%', $total_price_html, $message_html );
			$message_html = str_replace( '%shop_name%', get_option( 'blogname' ), $message_html );
			$message_html = str_replace( '%find_us%', $purchase_log['find_us'], $message_html );

			if ( !empty($email) && ($purchase_log['email_sent'] != 1 || $resend_email) ) {
				$wpdb->update(WPSC_TABLE_PURCHASE_LOGS, array('email_sent' => '1'), array('id' => $purchase_log['id']) );
				add_filter( 'wp_mail_from', 'wpsc_replace_reply_address', 0 );
				add_filter( 'wp_mail_from_name', 'wpsc_replace_reply_name', 0 );

				if ( !$is_transaction ) {
	
					$payment_instructions = strip_tags( stripslashes( get_option( 'payment_instructions' ) ) );
					if(!empty($payment_instructions))
						$payment_instructions .= "\n\r";					
					$message = __( 'Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc' ) . "\n\r" . $payment_instructions . $message;
					$message_html = __( 'Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc' ) . "\n\r" . $payment_instructions . $message_html;
					
					wp_mail( $email, __( 'Order Pending: Payment Required', 'wpsc' ), $message );
				} else {
					wp_mail( $email, __( 'Purchase Receipt', 'wpsc' ), $message );
				}
			}

			remove_filter( 'wp_mail_from_name', 'wpsc_replace_reply_name' );
			remove_filter( 'wp_mail_from', 'wpsc_replace_reply_address' );

			$report_user = __( 'Customer Details', 'wpsc' ) . "\n\r";
			$form_sql = "SELECT * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id` = '" . $purchase_log['id'] . "'";
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
			
			if ( $form_data != null ) {
				foreach ( $form_data as $form_field ) {
					$form_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `id` = '" . $form_field['form_id'] . "' LIMIT 1", ARRAY_A );
		
					switch ( $form_data['type'] ) {
						case "country":
							$country_code = $form_field['value'];
							$report_user .= $form_data['name'] . ": " . wpsc_get_country( $country_code ) . "\n";
							//check if country has a state then display if it does.
							$country_data = wpsc_country_has_state($country_code);
							if(($country_data['has_regions'] == 1))
								$report_user .= __( 'Billing State', 'wpsc' ) . ": " . wpsc_get_region( $purchase_log['billing_region'] ) . "\n";
							break;

						case "delivery_country":
							$report_user .= $form_data['name'] . ": " . wpsc_get_country( $form_field['value'] ) . "\n";			
							break;
					
						default:
							if ($form_data['name'] == 'State' && is_numeric($form_field['value'])){
								$report_user .= __( 'Delivery State', 'wpsc' ) . ": " . wpsc_get_state_by_id( $form_field['value'], 'name' ) . "\n";
							}else{
							$report_user .= wp_kses( $form_data['name'], array( ) ) . ": " . $form_field['value'] . "\n";
							}
							break;
					}
				}
			}

			$report_user .= "\n\r";
			$report = $report_id . $report_user . $report;

			//echo '======REPORT======<br />'.$report.'<br />';
			//echo '======EMAIL======<br />'.$message.'<br />';
			if ( (get_option( 'purch_log_email' ) != null) && ($purchase_log['email_sent'] != 1) )
				wp_mail( get_option( 'purch_log_email' ), __( 'Purchase Report', 'wpsc' ), $report );

			/// Adjust stock and empty the cart
			$wpsc_cart->submit_stock_claims( $purchase_log['id'] );
			$wpsc_cart->empty_cart();
		}
	}
}

?>