<?php
/*
 * Display Settings page
 */

// Clear the previously selected shipping form session variable if you are not on the shipping page
if ( isset( $_GET['tab'] ) )
	if( $_GET['tab'] == 'shipping' )
		if( isset( $_GET['shipping_module'] ) )
			$_SESSION['previous_shipping_name'] = $_GET['shipping_module'];

function wpsc_display_settings_page() { ?>

	<div id="wpsc_options" class="wrap">
		<div id="icon_card"><br /></div>
		<h2><?php _e( 'Store Settings', 'wpsc' ); ?></h2>
		<?php

		wpsc_the_settings_tabs();

		if ( isset( $_GET['tab'] ) )
			$page = $_GET['tab'];
		else
			$page = 'general';

		if ( !isset( $_GET['isocode'] ) )
			$_GET['isocode'] = '';

		if ( preg_match( "/[a-zA-Z]{2,4}/", $_GET['isocode'] ) ) {
			include(WPSC_FILE_PATH . '/wpsc-admin/includes/tax_and_shipping.php');
			return;
		}

		if ( isset( $_GET['googlecheckoutshipping'] ) ) {
			include(WPSC_FILE_PATH . '/wpsc-merchants/library/google_shipping_country.php');
			return;
			exit();
		}

		if ( isset( $_GET['selected_all'] ) )
			wpsc_submit_options( $_GET['selected_all'] );

 ?>

		<div id='wpsc_options_page'>

		<?php
			switch ( $page ) {
				case 'checkout' :
					require_once( 'includes/settings-pages/checkout.php' );
					wpsc_options_checkout();
					break;

				case 'gateway' :
					require_once( 'includes/settings-pages/gateway.php' );
					wpsc_options_gateway();
					break;

				case 'shipping' :
					require_once( 'includes/settings-pages/shipping.php' );
					wpsc_options_shipping();
					break;

				case 'admin' :
					require_once( 'includes/settings-pages/admin.php' );
					wpsc_options_admin();
					break;

				case 'presentation'  :
					require_once( 'includes/settings-pages/presentation.php' );
					wpsc_options_presentation();
					break;

				case 'taxes' :
					wpec_taxes_settings_page();
					break;

				case 'marketing' :
					require_once( 'includes/settings-pages/marketing.php' );
					wpsc_options_marketing();
					break;

				case 'import' :
					require_once( 'includes/settings-pages/import.php' );
					wpsc_options_import();
					break;

				case 'general' :
				default :
					require_once( 'includes/settings-pages/general.php' );
					wpsc_options_general();
					break;
			}

			$_SESSION['wpsc_settings_curr_page'] = $page; ?>

		</div>
	</div>

<?php
}

/*
 * Create settings page tabs 
 */

function wpsc_settings_tabs() {
	$_default_tabs = array(
		'general'      => __( 'General', 'wpsc' ),
		'presentation' => __( 'Presentation', 'wpsc' ),
		'admin'        => __( 'Admin', 'wpsc' ),
		'taxes'        => __( 'Taxes', 'wpsc' ),
		'shipping'     => __( 'Shipping', 'wpsc' ),
		'gateway'      => __( 'Payments', 'wpsc' ),
		'checkout'     => __( 'Checkout', 'wpsc' ),
		'marketing'    => __( 'Marketing', 'wpsc' ),
		'import'       => __( 'Import', 'wpsc' )
	);

	return apply_filters( 'wpsc_settings_tabs', $_default_tabs );
}

/*
 * Display settings tabs
 */

function wpsc_the_settings_tabs() {
	global $redir_tab;

	$tabs = wpsc_settings_tabs();

	if ( !empty( $tabs ) ) {
		echo '<div id="wpsc_settings_nav_bar" style="width:100%;">';
		echo "<ul id='sidemenu' >\n";

		if ( isset( $redir_tab ) && array_key_exists( $redir_tab, $tabs ) ) {
			$current = $redir_tab;
		} elseif ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ) {
			$current = $_GET['tab'];
		} else {
			$keys = array_keys( $tabs );
			$current = array_shift( $keys );
		}

		foreach ( $tabs as $callback => $text ) {
			$class = '';

			if ( $current == $callback )
				$class = " class='current'";

			$href = add_query_arg( array( 'tab' => $callback, 's' => false, 'paged' => false, 'post_mime_type' => false, 'm' => false ) );
			$href = remove_query_arg( 'isocode', $href );
			$href = wp_nonce_url( $href, "tab-$callback" );
			$link = "<a href='" . esc_url( $href ) . "'$class>$text</a>";

			echo "\t<li id='" . esc_attr( "tab-$callback" ) . "'>$link</li>\n";
		}

		echo "</ul>\n";
		echo '</div>';
		echo "<div style='clear:both;'></div>";
	}
}

function country_list( $selected_country = null ) {
	global $wpdb;

	$output       = '';
	$output      .= "<option value=''></option>";
	$country_data = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CURRENCY_LIST . "` ORDER BY `country` ASC", ARRAY_A );

	foreach ( (array)$country_data as $country ) {
		$selected = '';

		if ( $selected_country == $country['isocode'] )
			$selected = "selected='selected'";

		$output .= "<option value='" . $country['isocode'] . "' $selected>" . htmlspecialchars( $country['country'] ) . "</option>";
	}

	return $output;
}

/*
 * Get Shipping Form for wp-admin 
 */
function wpsc_get_shipping_form( $shippingname ) {
	global $wpdb, $wpsc_shipping_modules;

	if ( array_key_exists( $shippingname, $wpsc_shipping_modules ) ) {
		$shipping_forms       = $wpsc_shipping_modules[$shippingname]->getForm();
		$shipping_module_name = $wpsc_shipping_modules[$shippingname]->name;
		$output = array( 'name' => $shipping_module_name, 'form_fields' => $shipping_forms, 'has_submit_button' => 1 );
	} else {
		$output = array( 'name' => '&nbsp;', 'form_fields' => __( 'To configure a shipping module select one on the left.', 'wpsc' ), 'has_submit_button' => 0 );
	}

	return $output;
}

/***
 * Get Payment Form for wp-admin 
 */
function wpsc_get_payment_form( $paymentname ,$selected_gateway_data='') {
	global $wpdb, $nzshpcrt_gateways;

	$payment_gateway_names = get_option('payment_gateway_names');
	$form                  = array();
	$output                = array( 'name' => '&nbsp;', 'form_fields' => __( 'To configure a payment module select one on the left.', 'wpsc' ), 'has_submit_button' => 0 );

	foreach ( $nzshpcrt_gateways as $gateway ) {
		if ( $gateway["internalname"] != $paymentname ) {
			continue;
		} else {
			$selected_gateway_data	= $gateway;		
			$form = $gateway;
		}
	}

	if ( $form ) { 
		$output ='';
		$output .="<tr>
					  <td style='border-top: none;'>
					  ".__("Display Name")."
					  </td>
					  <td style='border-top: none;'>";

		if ( !empty($payment_gateway_names[$paymentname])) {
			$display_name = $payment_gateway_names[$paymentname];
		} elseif(!empty($selected_gateway_data['display_name'])){
			$display_name =$selected_gateway_data['display_name'];
		}else{
			switch($selected_gateway_data['payment_type']) {
				case "paypal";
					$display_name = "PayPal";
					break;

				case "manual_payment":
					$display_name = "Manual Payment";
					break;

				case "google_checkout":
					$display_name = "Google Checkout";
					break;

				case "credit_card":
				default:
					$display_name = "Credit Card";
					break;
			}
		}

		$output .="<input type='text' name='user_defined_name[".$paymentname."]' value='". $display_name ."' /><br />
					<span class='small description'>".__('The text that people see when making a purchase')."</span>
					</td>
					</tr>";
		$payment_forms = $form["form"]();
		$payment_module_name = $form["name"];
		
		$output = array( 'name' => $payment_module_name, 'form_fields' => $output.$payment_forms, 'has_submit_button' => 1 );
	} else {
		$output = array( 'name' => '&nbsp;', 'form_fields' => __( 'To configure a payment module select one on the left.', 'wpsc' ), 'has_submit_button' => 0 );
	}

	return $output;
}

function wpsc_settings_page_update_notification() {

	if ( isset( $_GET['skipped'] ) || isset( $_GET['updated'] ) || isset( $_GET['regenerate'] ) || isset( $_GET['deleted'] ) || isset( $_GET['shipadd'] ) ) { ?>

	<div id="message" class="updated fade"><p>
		<?php

		if ( isset( $_GET['updated'] ) && (int)$_GET['updated'] ) {
			printf( _n( ' Setting options updated.', ' %s Settings options updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
			unset( $_GET['updated'] );
			$message = true;
		}
		if ( isset( $_GET['deleted'] ) && (int)$_GET['deleted'] ) {
			printf( _n( '%s Setting option deleted.', '%s Setting option deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
			unset( $_GET['deleted'] );
			$message = true;
		}
		if ( isset( $_GET['shipadd'] ) && (int)$_GET['shipadd'] ) {
			printf( _n( ' Shipping option updated.', ' Shipping option updated.', $_GET['shipadd'] ), number_format_i18n( $_GET['shipadd'] ) );
			unset( $_GET['shipadd'] );
			$message = true;
		}
		if ( isset( $_GET['added'] ) && (int)$_GET['added'] ) {
			printf( _n( '%s Checkout field added.', '%s Checkout fields added.', $_GET['added'] ), number_format_i18n( $_GET['added'] ) );
			unset( $_GET['added'] );
			$message = true;
		}
		if ( isset( $_GET['regenerate'] ) ) {
			_e('Thumbnails regenerated.', 'wpsc');
			unset( $_GET['regenerate'] );
			$message = true;
			wpsc_regenerate_thumbnails();
		}

		if ( is_null( $message ) )
			_e( 'Settings successfully updated.', 'wpsc' );

		$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'regenerate', 'skipped', 'updated', 'deleted', 'wpsc_downloadcsv', 'rss_key', 'start_timestamp', 'end_timestamp', 'email_buyer_id' ), $_SERVER['REQUEST_URI'] ); ?>
	</p></div>

<?php
	}
}

?>