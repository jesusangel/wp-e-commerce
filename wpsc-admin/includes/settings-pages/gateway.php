<?php
function wpsc_options_gateway(){
global $wpdb;
$curgateway = get_option('payment_gateway');


if (is_array($GLOBALS['nzshpcrt_gateways'])) {
	$selected_gateways = get_option('custom_gateway_options');
	foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
		if($gateway['internalname'] == $curgateway ) {
			$selected = "selected='selected'";
			$form = $gateway['form']();
			//exit($form);
		} else {
			$selected = '';
		}
		
		
		if(isset($gateway['admin_name'])) {
			$gateway['name'] = $gateway['admin_name'];
		}
		$disabled = '';
		if (!in_array($gateway['internalname'], (array)$selected_gateways)) {
		  $disabled = "disabled='disabled'";
		}
		
		$gatewaylist .="<option $disabled value='".$gateway['internalname']."' ".$selected." >".$gateway['name']."</option>";
	}
}
$gatewaylist = "<option value='".$nogw."'>".TXT_WPSC_PLEASESELECTAPAYMENTGATEWAY."</option>" . $gatewaylist;


?>

	
						
		
<script language='javascript' type='text/javascript'>
function selectgateway() {
	document.forms.gateway_opt.submit();
}

</script>
<div class="wrap">
	<div class='metabox-holder'>
		<form name='gatewayopt' method='post' id='gateway_opt' action='' >
		<input type='hidden' name='gateway_submits' value='true' />
		<input type='hidden' name='wpsc_gateway_settings' value='gateway_settings' />
		<?php 
			if (get_option('custom_gateway') == 1){ 
				$custom_gateway_hide="style='display:block;'";
				$custom_gateway1 = 'checked="checked"';
			} else {
				$custom_gateway_hide="style='display:none;'";
				$custom_gateway2 = 'checked="checked"';
			}
		?>
		  <h2><?php echo TXT_WPSC_GATEWAY_OPTIONS;?></h2>
		  <table id='gateway_options' >
            <tr>
				<td class='select_gateway'>
			<?php if (IS_WP27) { ?>
				<div class='postbox'>
				<h3 class='hndle'><?=TXT_WPSC_OPTIONS_GENERAL_HEADER?></h3>
				<div class='inside'>
			<?php } else { ?>
					<div class="categorisation_title">
					  <strong class="form_group"><?php echo TXT_WPSC_PAYMENT_GATEWAYS; ?></strong>
					</div>
			<?php }	?>
					
				  <p><?php echo TXT_WPSC_CHOOSE_PAYMENT_GATEWAYS; ?></p>
				  <br />
					<?php
					$selected_gateways = get_option('custom_gateway_options');
					//echo("<pre>".print_r($selected_gateways,true)."</pre>");
					foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
					  if(isset($gateway['admin_name'])) {
					    $gateway['name'] = $gateway['admin_name'];
					  }
						if (in_array($gateway['internalname'], (array)$selected_gateways)) {
					?>
						<p><input name='wpsc_options[custom_gateway_options][]' checked='checked' type='checkbox' value='<?php echo $gateway['internalname']; ?>' id='<?php echo $gateway['internalname']; ?>_id' /><label for='<?php echo $gateway['internalname']; ?>_id'><?php echo $gateway['name']; ?></label></p>
				<?php	} else { ?>
						<p><input name='wpsc_options[custom_gateway_options][]' type='checkbox' value='<?php echo $gateway['internalname']; ?>' id='<?php echo $gateway['internalname']; ?>_id' />
						<label for='<?php echo $gateway['internalname']; ?>_id'><?php echo $gateway['name']; ?></label></p>
				<?php	}
					}
					?>
						<div class='submit gateway_settings'>
							<input type='hidden' value='true' name='update_gateways' />
							<input type='submit' value='<?php echo TXT_WPSC_UPDATE_BUTTON?>' name='updateoption' />
						</div>	
				<?php if (IS_WP27){ ?>
				</div>
				</div>
				<?php } ?>		
				</td>
				
				<td class='gateway_settings' rowspan='2'>										
					<?php if (IS_WP27) { ?>
						<div class='postbox'>
						<h3 class='hndle'><?=TXT_WPSC_CONFIGURE_PAYMENT_GATEWAY?></h3>
						<div  class='inside'>
						<table class='form-table'>
					<?php } else { ?>					
					<table class='form-table'>
					<tr class="firstrowth">
					  <td colspan='2' style='border-bottom: none;'>
					    <strong class="form_group"><?php echo TXT_WPSC_CONFIGURE_PAYMENT_GATEWAY;?></strong>
					  </td>
					</tr>
					<?php 
						} 	
					?>
					<tr>
					  <td style='border-top: none;'>
							<h4><?php echo TXT_WPSC_PAYMENTGATEWAY2;?></h4>
					  </td>
					  <td style='border-top: none;'>
					<select name='payment_gw' onchange='selectgateway();'>
					<?php echo $gatewaylist; ?>
					</select>
						</td>
					</tr>
					<?php echo $form; ?>   
					
					<tr class='update_gateway' >
						<td colspan='2'>
							<div class='submit'>
								<input type='submit' value='<?php echo TXT_WPSC_UPDATE_BUTTON?>' name='updateoption' />
							</div>
						</td>
					</tr>
					</table>
					<?php if (IS_WP27){ ?>
					</div>
					</div>
					<?php } ?>
				</td>
      </tr>
      
      
      
      <tr>
        <td>
					<h4><?php echo TXT_WPSC_WE_RECOMMEND; ?></h4>
						<a style="border-bottom:none;" href="https://www.paypal.com/nz/mrb/pal=LENKCHY6CU2VY" target="_blank"><img src="<?php echo WPSC_URL; ?>/images/paypal-referal.gif" border="0" alt="Sign up for PayPal and start accepting credit card payments instantly." /></a> <br /><br />
						<a style="border-bottom:none;" href="http://checkout.google.com/sell/?promo=seinstinct" target="_blank"><img src="https://checkout.google.com/buyer/images/google_checkout.gif" border="0" alt="Sign up for Google Checkout" /></a>
        </td>
      </tr>
		</table>

  
  
  
  </form>
</div>
</div>			
	
<?php
}
?>