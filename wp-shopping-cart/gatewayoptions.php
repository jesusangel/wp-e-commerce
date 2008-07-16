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

if ($_POST['custom_gateway_options'] != null) {
	update_option('custom_gateway_options', $_POST['custom_gateway_options']);
	$changes_made = true;
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
function selectgateway()
  {
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
  <table class='form-table'>
    <tr>
	   <!-- <th scope='row'><?php echo TXT_WPSC_CUSTOMERCHOOSEGATEWAY;?></th>-->
	    <td colspan='2'>
	    <?php /*
	    <input onclick="jQuery('#custom_gateway_div').slideDown(200)" <?=$custom_gateway1;?> type='radio' value='1' name='custom_gateway' id='custom_gateway_1'>
	    <label for='custom_gateway_1'><?php echo TXT_WPSC_YES;?></label>
	    <input <?=$custom_gateway2;?> onclick="jQuery('#custom_gateway_div').slideUp(200)" type='radio' value='0' name='custom_gateway' id='custom_gateway_2'>
	    <label for='custom_gateway_2'><?php echo TXT_WPSC_NO;?></label><br>
	    <small>Note: Select the ones that you have entered your details for only</small>
	    <div id='custom_gateway_div' <?=$custom_gateway_hide?>>
	    */?>
	    <div style='float: right;width: 600px;'>
				<strong><?php echo TXT_WPSC_PAYMENT_INSTRUCTIONS_DESCR; ?>:</strong><br />
				<textarea cols='50' rows='9' name='payment_instructions'><?php echo  get_option('payment_instructions');?></textarea><br />
				<em><?php echo TXT_WPSC_PAYMENT_INSTRUCTIONS_BELOW_DESCR; ?> </em>
      </div>
      
	    <table style='width: 360px;'>
				<tr>
					<td  style='border-bottom:none; padding-top: 0px;'>
					
					<strong><?php echo TXT_WPSC_CHOOSE_PAYMENT_GATEWAYS; ?></strong><br /><br />
					<?php
					$selected_gateways = get_option('custom_gateway_options');
					//echo("<pre>".print_r($selected_gateways,true)."</pre>");
					foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
						if (in_array($gateway['internalname'], (array)$selected_gateways)) {
							echo "<input name='custom_gateway_options[]' checked='checked' type='checkbox' value='{$gateway['internalname']}' id='{$gateway['internalname']}_id'><label for='{$gateway['internalname']}_id'>{$gateway['name']}</label><br>";
						} else {
							echo "<input name='custom_gateway_options[]' type='checkbox' value='{$gateway['internalname']}' id='{$gateway['internalname']}_id'><label for='{$gateway['internalname']}_id'>{$gateway['name']}</label><br>";
						}
					}
					?>
					</td>
				</tr>
	    </table>
      
	    </div>
			</td>
		</tr>
	
    <tr>
      <th scope='row'>
      <?php echo TXT_WPSC_PAYMENTGATEWAY2;?>
      </th>
      <td>
      <select name='payment_gw' onChange='selectgateway();'>
      <?php
      echo $gatewaylist;
      ?>
      </select>
      </td>
    </tr><?php
	echo $form;
  ?>
  <table>
  <tr>
      <td>
      </td>
      <td>
      <input type='submit' value='<?php echo TXT_WPSC_SUBMIT;?>' name='submit_details' />
      </td>
    </tr>
	<?php if (($curgateway=='paypal_multiple')||($curgateway=='paypal_certified')) { ?>
	<tr>
      <td colspan="2">
	  	When you signup for PayPal, you can start accepting credit card payments instantly. As the world's number one online payment service, PayPal is the fastest way to open your doors to over 150 million member accounts worldwide. Best of all, it's completely free to sign up! To sign up or learn more:
      </td>
    </tr>
	<tr>
      <td colspan="2">
	  	<a style="border-bottom:none;" href="https://www.paypal.com/nz/mrb/pal=LENKCHY6CU2VY" target="_blank"><img src=" http://images.paypal.com/en_US/i/bnr/paypal_mrb_banner.gif" border="0" alt="Sign up for PayPal and start accepting credit card payments instantly."></A>
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
	<td></td>
      <td>
	  	<a style="border-bottom:none;" href="http://checkout.google.com/sell/?promo=seinstinct" target="_blank"><img src="https://checkout.google.com/buyer/images/google_checkout.gif" border="0" alt="Sign up for Google Checkout"></A>
      </td>
    </tr>
	<?php }?>
  </table>
  </form>
  <br />
<?php
if((get_option('activation_state') !== 'true')&&($curgateway!='google')) {
  echo TXT_WPSC_PAYMENTGATEWAYNOTE;
}
?>
</div>