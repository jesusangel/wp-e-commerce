<?php
function wpsc_options_gateway(){
global $wpdb;
if (is_array($GLOBALS['nzshpcrt_gateways'])) {
	$selected_gateways = get_option('custom_gateway_options');
	foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
		if($gateway['internalname'] == $curgateway ) {
			$selected = " selected='selected'";
			$form = $gateway['form']();
		} else {
			$selected = '';
		}
		
		
		if(isset($gateway['admin_name'])) {
			$gateway['name'] = $gateway['admin_name'];
		}
		$disabled = '';
		if (!in_array($gateway['internalname'], (array)$selected_gateways)) {
		  $disabled = "disabled='true'";
		}
		
		$gatewaylist .="<option $disabled value='".$gateway['internalname']."' ".$selected." >".$gateway['name']."</option>";
	}
}
$gatewaylist = "<option value='".$nogw."'>".TXT_WPSC_PLEASESELECTAPAYMENTGATEWAY."</option>" . $gatewaylist;
?>
<form name='cart_options' id='cart_options' method='post' action=''>
	
						
		
<script language='javascript' type='text/javascript'>
function selectgateway() {
	document.forms.gatewayopt.submit();
}

</script>
<div class="wrap">
<div class='metabox-holder'>
		<form name='gatewayopt' method='post' id='gateway_options_tbl' action=''>
		<input type='hidden' name='gateway_submits' value='true'>
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

		<!--
<div class="tablenav wpsc_admin_nav">
			<div class="alignright">
				<a class="about_this_page_sub" href="http://www.instinct.co.nz/e-commerce/payment-options/" target="_blank"><span><?php echo TXT_WPSC_ABOUT_THIS_PAGE;?></span></a>
			</div>
			<br class="clear"/>
		</div>
-->


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
					
				  <p>
				  <?php echo TXT_WPSC_CHOOSE_PAYMENT_GATEWAYS; ?>
				  </p>
				  <br />
						<?php
						$selected_gateways = get_option('custom_gateway_options');
						//echo("<pre>".print_r($selected_gateways,true)."</pre>");
						foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
						  if(isset($gateway['admin_name'])) {
						    $gateway['name'] = $gateway['admin_name'];
						  }
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
							<input type='submit' value='<?php echo TXT_WPSC_UPDATE_BUTTON?>' name='updateoption'/>
						</div>
								
						
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
					<select name='payment_gw' onChange='selectgateway();'>
					<?php echo $gatewaylist; ?>
					</select>
						</td>
					</tr>
					<?php echo $form; ?>   
					
					<tr class='update_gateway' >
						<td colspan='2'>
							<div class='submit'>
								<input type='submit' value='<?php echo TXT_WPSC_UPDATE_BUTTON?>' name='updateoption'/>
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
						<a style="border-bottom:none;" href="https://www.paypal.com/nz/mrb/pal=LENKCHY6CU2VY" target="_blank"><img src="<?php echo WPSC_URL; ?>/images/paypal-referal.gif" border="0" alt="Sign up for PayPal and start accepting credit card payments instantly."></a> <br /><br />
						<a style="border-bottom:none;" href="http://checkout.google.com/sell/?promo=seinstinct" target="_blank"><img src="https://checkout.google.com/buyer/images/google_checkout.gif" border="0" alt="Sign up for Google Checkout"></a>
        </td>
      </tr>
		</table>

  
  
  
  </form>
</div>
</div>						
<?php
}
?>