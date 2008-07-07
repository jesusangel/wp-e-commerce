<?php
if (isset($_GET['googlecheckoutshipping'])) {
	include('google_shipping_country.php');
	exit();
}
	
	

$curgateway = get_option('payment_gateway');
$changes_made = false;
 

if(is_numeric($_POST['payment_method']) && (get_option('payment_gateway') != $_POST['payment_method'])) {
	update_option('payment_method', $_POST['payment_method']);
	$changes_made = true;
}


if ($_POST['custom_gateway_options'] != null){
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
//exit($curgateway);
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
  
	<h2><?php echo TXT_WPSC_PAYMENT_OPTIONS;?></h2>
  <p><?php echo TXT_WPSC_PAYMENT_DESCRIPTION;?></p>
  <table>
    <tr>
      <td colspan='2' width='50%'>
      <input type='radio' name='payment_method' value='3' id='payment_method_3' <?php echo $selected[3]; ?>>
      <label for='payment_method_3'><?php echo TXT_WPSC_MANUAL_PAYMENT; ?></label>
      </td>
      
      <td rowspan='4'>
      <strong><?php echo TXT_WPSC_PAYMENT_INSTRUCTIONS; ?>:</strong>
      <textarea cols='55' rows='6' name='payment_instructions'><?php echo  get_option('payment_instructions');?></textarea>
      </td>
      
    </tr>
    <tr>
      <td colspan='2'>
      <input type='radio' name='payment_method' value='1' id='payment_method_1' <?php echo $selected[1]; ?>>
      <label for='payment_method_1'><?php echo TXT_WPSC_CREDIT_CARD; ?></label>
      </td>
    </tr>
    <tr>
      <td colspan='2'>
      <input type='radio' name='payment_method' value='2' id='payment_method_2' <?php echo $selected[2]; ?>>
      <label for='payment_method_2'><?php echo TXT_WPSC_CREDIT_CARD_AND_MANUAL_PAYMENT; ?></label>
      </td>
    </tr>
    <tr>
      <td colspan='2' style='padding: 2px;'>
        <input type='hidden' name='submit_action' value='add' />
        <input type='submit' name='submit_details' value='<?php echo TXT_WPSC_SAVE_CHANGES;?>' />
      </td>
    </tr>
  </table>
  
  <br />
  <br />
  
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
  <p>Do you want your customers to be able to choose from multiple payment gateways at checkout?</p>
  <table class='form-table'>
    <tr>
	   <!-- <th scope='row'><?php echo TXT_WPSC_CUSTOMERCHOOSEGATEWAY;?></th>-->
	    <td colspan='2'>
	    <input onclick="jQuery('#custom_gateway_div').slideDown(200)" <?=$custom_gateway1;?> type='radio' value='1' name='custom_gateway' id='custom_gateway_1'>
	    <label for='custom_gateway_1'><?php echo TXT_WPSC_YES;?></label>
	    <input <?=$custom_gateway2;?> onclick="jQuery('#custom_gateway_div').slideUp(200)" type='radio' value='0' name='custom_gateway' id='custom_gateway_2'>
	    <label for='custom_gateway_2'><?php echo TXT_WPSC_NO;?></label><br>
	    <small>Note: Select the ones that you have entered your details only</small>
	    <div id='custom_gateway_div' <?=$custom_gateway_hide?>>
	    <table>
	    <tr>
	  <th style='border-bottom:none;'>
	  Select Gateways
	  </th>
	  <td  style='border-bottom:none;'>
	  
	  <?php
		foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
			if (($gateway['internalname'] != 'testmode') && ($gateway['internalname'] != 'google')) {
				$selected_gateways = get_option('custom_gateway_options');
				if (in_array($gateway['internalname'], (array)$selected_gateways)) {
					echo "<input name='custom_gateway_options[]' checked='checked' type='checkbox' value='{$gateway['internalname']}' id='{$gateway['internalname']}_id'><label for='{$gateway['internalname']}_id'>{$gateway['name']}</label><br>";
				} else {
					echo "<input name='custom_gateway_options[]' type='checkbox' value='{$gateway['internalname']}' id='{$gateway['internalname']}_id'><label for='{$gateway['internalname']}_id'>{$gateway['name']}</label><br>";
				}
			}
		}
		?>
		</div>
	</td>
	</td>
	    </table>
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
	<?php if (($curgateway=='paypal_multiple')||($curgateway=='paypal_certified')){?>
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
	<?php }  elseif ($curgateway=='google') {?>
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
if((get_option('activation_state') !== 'true')&&($curgateway!='google'))
  {
  echo TXT_WPSC_PAYMENTGATEWAYNOTE;
  }
?>
</div>