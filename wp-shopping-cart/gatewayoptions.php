<?php
if (isset($_GET['googlecheckoutshipping'])) {
	include('google_shipping_country.php');
	return;
	exit();
}
	
$curgateway = get_option('payment_gateway');
$changes_made = false;

if(is_numeric($_POST['payment_method']) && (get_option('payment_gateway') != $_POST['payment_method'])) {
	update_option('payment_method', $_POST['payment_method']);
	$changes_made = true;
}
if($_POST['update_gateways'] == 'true') {
	if($_POST['custom_gateway_options'] != null) {
		update_option('custom_gateway_options', $_POST['custom_gateway_options']);
		$changes_made = true;
	} else if($_POST['custom_gateway_options'] == null) {
		update_option('custom_gateway_options', null);
	}
}
if(isset($_POST['payment_instructions']) && ($_POST['payment_instructions'] != get_option('payment_instructions'))) {
	update_option('payment_instructions', $_POST['payment_instructions']);
	$changes_made = true;
}

if(isset($_POST['custom_gateway'])) {
  // this particular form field refuses to submit in a way that appears to defy logic if dealt with like the others, hence this overkill
	if($_POST['custom_gateway'] == 1) {
		update_option('custom_gateway', 1);
		$changes_made = true;
	} else if(($_POST['custom_gateway'] == 0) && (get_option('custom_gateway') != 0)) {
		update_option('custom_gateway', 0);
		$changes_made = true;
	}
}

if(($_POST['payment_gw'] != null) && ($_POST['submit_details'] == null)) {
  update_option('payment_gateway', $_POST['payment_gw']);
	$curgateway = get_option('payment_gateway');
  $changes_made = true;
}
 
if(($_POST['payment_gw'] != null) && ($_POST['submit_details'] != null)) {
  foreach($nzshpcrt_gateways as $gateway) {
    if($gateway['internalname'] == $curgateway) {
      $gateway['submit_function']();
      $changes_made = true;
		}
	}
}

if($changes_made == true) {
  echo "<div class='updated'><p align='center'>".TXT_WPSC_THANKSAPPLIED."</p></div>";
}
if (get_option('custom_gateway')) {
	$custom_gateway1 = "checked='checked'";
} else {
	$custom_gateway2 = "checked='checked'";
}

$form = "";
foreach($nzshpcrt_gateways as $gateway) {
  if($gateway['internalname'] == $curgateway ) {
    $selected = " selected='selected'";
    $form = $gateway['form']();
	} else {
		$selected = '';
	}
  $gatewaylist .="<option value='".$gateway['internalname']."' ".$selected." >".$gateway['name']."</option>"; 
}
$gatewaylist = "<option value='".$nogw."'>".TXT_WPSC_PLEASESELECTAPAYMENTGATEWAY."</option>" . $gatewaylist;



$selected[get_option('payment_method')] = "checked='true'";
?>
<script language='JavaScript' type='text/javascript'>
function selectgateway() {
  document.forms.gatewayopt.submit()
}
</script>
<div class="wrap">
  <form name='gatewayopt' method='POST'>
	<?php 
		if (get_option('custom_gateway') == 1){ 
			$custom_gateway_hide="style='display:block;'";
			$custom_gateway1 = 'checked="true"';
		} else {
			$custom_gateway_hide="style='display:none;'";
			$custom_gateway2 = 'checked="true"';
		}
	?>
  <h2><?php echo TXT_WPSC_GATEWAY_OPTIONS;?></h2>

		<table id='gateway_options' >
      
      <tr>
				<td class='select_gateway'>
						<h4><?php echo TXT_WPSC_CHOOSE_PAYMENT_GATEWAYS; ?></h4>
						<?php
						$selected_gateways = get_option('custom_gateway_options');
						//echo("<pre>".print_r($selected_gateways,true)."</pre>");
						foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
							if (in_array($gateway['internalname'], (array)$selected_gateways)) {
								echo "						";// add the whitespace to the html
								echo "<p><input name='custom_gateway_options[]' checked='checked' type='checkbox' value='{$gateway['internalname']}' id='{$gateway['internalname']}_id'><label for='{$gateway['internalname']}_id'>{$gateway['name']}</label></p>\n\r";
							} else {
							  echo "						";
								echo "<p><input name='custom_gateway_options[]' type='checkbox' value='{$gateway['internalname']}' id='{$gateway['internalname']}_id'><label for='{$gateway['internalname']}_id'>{$gateway['name']}</label></p>\n\r";
							}
						}
						?>
						<div class='submit gateway_settings'>
							<input type='hidden' value='true' name='update_gateways'/>
							<input type='submit' value='Update &raquo;' name='updateoption'/>
						</div>
						
						<br />
						
						<br />
						<h4><?php echo TXT_WPSC_WE_RECOMMEND; ?></h4>
							<a style="border-bottom:none;" href="https://www.paypal.com/nz/mrb/pal=LENKCHY6CU2VY" target="_blank"><img src="<?php echo WPSC_URL; ?>/images/paypal-referal.gif" border="0" alt="Sign up for PayPal and start accepting credit card payments instantly."></a> <br /><br />
							<a style="border-bottom:none;" href="http://checkout.google.com/sell/?promo=seinstinct" target="_blank"><img src="https://checkout.google.com/buyer/images/google_checkout.gif" border="0" alt="Sign up for Google Checkout"></a>
		
						
				</td>
				
				<td class='gateway_settings'>										
					<?php

					?>					
					<table class='form-table'>
					<tr>
					  <td colspan='2'  style='border-bottom: none;'>
							<h4 style='font-size: 13px;' ><?php echo TXT_WPSC_CONFIGURE_PAYMENT_GATEWAY;?></h4>
					  </td>
					</tr>
					<tr>
					  <td style='border-top: none;'>
							<h4><?php echo TXT_WPSC_PAYMENTGATEWAY2;?></h4>
					  </td>
					  <td style='border-top: none;'>
					<select name='payment_gw' onChange='selectgateway();'>
					<?php echo $gatewaylist; ?>
					</select>
						</td>
					</tr>
					<?php echo $form; ?>   
					
					<tr class='update_gateway' >
						<td colspan='2'>
							<div class='submit'>
								<input type='submit' value='Update &raquo;' name='updateoption'/>
							</div>
						</td>
					</tr>
					</table>
				</td>
      </tr>
		</table>

  
  
  
  <table>
	<?php if (($curgateway=='paypal_multiple')||($curgateway=='paypal_certified')) { ?>
			<tr>
				<td colspan="2">
				When you signup for PayPal, you can start accepting credit card payments instantly. As the world's number one online payment service, PayPal is the fastest way to open your doors to over 150 million member accounts worldwide. Best of all, it's completely free to sign up! To sign up or learn more:
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<a style="border-bottom:none;" href="https://www.paypal.com/nz/mrb/pal=LENKCHY6CU2VY" target="_blank"><img src=" http://images.paypal.com/en_US/i/bnr/paypal_mrb_banner.gif" border="0" alt="Sign up for PayPal and start accepting credit card payments instantly."></a>
				</td>
			</tr>  
	<?php }  elseif ($curgateway=='google') { ?>
	<tr>
		<td colspan="2">Find it with Google.  Buy it with Google Checkout.
	</tr>
	<tr>
	<td></td>
	<td>
	Want a faster, safer and more convenient way to shop online? You got it.</td>
	</td>
	</tr>
	<tr>
		<td>
		</td>
		<td>
	  	<a style="border-bottom:none;" href="http://checkout.google.com/sell/?promo=seinstinct" target="_blank"><img src="https://checkout.google.com/buyer/images/google_checkout.gif" border="0" alt="Sign up for Google Checkout"></a>
		</td>
	</tr>
	<?php }?>
	</table>
  </form>
<?php
if((get_option('activation_state') !== 'true')&&($curgateway!='google')) {
  echo TXT_WPSC_PAYMENTGATEWAYNOTE;
}
?>
</div>