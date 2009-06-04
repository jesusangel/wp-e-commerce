<?php
global $wpsc_cart, $wpdb, $wpsc_checkout, $wpsc_gateway, $wpsc_coupons;
$wpsc_checkout = new wpsc_checkout();
$wpsc_gateway = new wpsc_gateways();
$wpsc_coupons = new wpsc_coupons($_SESSION['coupon_numbers']);
//echo "<pre>".print_r($wpsc_cart,true)."</pre>";
?>

<table class="productcart">
	<tr class="firstrow">
		<td class='firstcol'></td>
		<td><?php echo TXT_WPSC_PRODUCT; ?>:</td>
	  <td><?php echo TXT_WPSC_QUANTITY; ?>:</td>
	  <td><?php echo TXT_WPSC_PRICE; ?>:</td>
	  <td></td>
	</tr>
	<?php while (wpsc_have_cart_items()) : wpsc_the_cart_item(); ?>
	
	<?php  //this displays the confirm your order html	?>
		
		<tr class="product_row">
			<td class="firstcol"><img src='<?php echo wpsc_cart_item_image(48,48); ?>' alt='<?php echo wpsc_cart_item_name(); ?>' title='<?php echo wpsc_cart_item_name(); ?>' /></td>
			<td class="firstcol">
			<a href='<?php echo wpsc_cart_item_url();?>'><?php echo wpsc_cart_item_name(); ?></a>
			</td>
			<td>
				<form action="<?php echo get_option('shopping_cart_url'); ?>" method="post" class="adjustform">
					<input type="text" name="quantity" size="2" value="<?php echo wpsc_cart_item_quantity(); ?>"/>
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
					<input type="hidden" name="wpsc_update_quantity" value="true"/>
					<input type="submit" value="<?php echo TXT_WPSC_APPLY; ?>" name="submit"/>
				</form>
			</td>
			<td><span class="pricedisplay"><?php echo wpsc_cart_item_price(); ?></span></td>
			<td>
			
				<form action="<?php echo get_option('shopping_cart_url'); ?>" method="post" class="adjustform">
					<input type="hidden" name="quantity" value="0"/>
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
					<input type="hidden" name="wpsc_update_quantity" value="true"/>
					<button class='remove_button' type="submit"><span><?php echo TXT_WPSC_REMOVE; ?></span></button>
				</form>
			</td>
		</tr>
	<?php endwhile; ?>
	<?php //this HTML displays coupons if there are any active coupons to use ?>
<?php //exit('<pre>'.print_r($wpsc_coupons, true).'</pre>'); ?>
	<?php if(wpsc_uses_coupons()): ?>
		
		<?php if(wpsc_coupons_error()): ?>
			<tr><td><?php echo TXT_WPSC_COUPONSINVALID; ?></td></tr>
		<?php endif; ?>
		<tr>
		
		<td colspan="2"><?php _e('Enter your coupon number'); ?> :</td>
		<td  colspan="3" align='left'>
		<form  method='post' action="<?php echo get_option('shopping_cart_url'); ?>">
			<input type='text' name='coupon_num' id='coupon_num' value='<?php echo $wpsc_cart->coupons_name; ?>' />
			<input type='submit' value='<?php echo TXT_WPSC_APPLY ?>' />
		</form>
		</td>
		</tr>
	<?php endif; ?>	
	</table>
	<?php  //this HTML dispalys the calculate your order HTML	?>
	
	<?php if(wpsc_uses_shipping()) : ?>
		<h2><?php echo TXT_WPSC_SHIPPING_COUNTRY; ?></h2>
		<table class="productcart">
			<tr>
				<td colspan='5'>
					<?php echo TXT_WPSC_SHIPPING_DETAIL; ?>
				</td>
			</tr>
			
			<tr>
				<td colspan='5'>
					<?php if($_SESSION['categoryAndShippingCountryConflict'] != '') : ?>
						<p class='validation-error'><?php echo $_SESSION['categoryAndShippingCountryConflict']; ?></p>
					<?php
					endif;
					if($_SESSION['WpscGatewayErrorMessage'] != '') :
					?>
						<p class='validation-error'><?php echo $_SESSION['WpscGatewayErrorMessage']; ?></p>
					<?php
					endif;
					?>

					<form name='change_country' id='change_country' action='' method='post'>
						<?php echo wpsc_shipping_country_list();?>
						<input type='hidden' name='wpsc_update_location' value='true' />
					</form>
				
				</td>
			</tr>
			
			<?php while (wpsc_have_shipping_methods()) : wpsc_the_shipping_method(); ?>
					<tr><td class='shipping_header' colspan='5'><?php echo wpsc_shipping_method_name().TXT_WPSC_CHOOSE_A_SHIPPING_RATE; ?> </td></tr>
					<?php while (wpsc_have_shipping_quotes()) : wpsc_the_shipping_quote();
				
					 ?>
						<tr>
							<td colspan='3'>
								<label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_name(); ?></label>
							</td>
							<td style='text-align:center;'>
								<label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_value(); ?></label>
							</td>
							<td style='text-align:center;'>
							<?php if(wpsc_have_morethanone_shipping_methods_and_quotes()): ?>
								<input type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>' <?php echo wpsc_shipping_quote_selected_state(); ?>  onclick='switchmethod("<?php echo wpsc_shipping_quote_name(); ?>", "<?php echo wpsc_shipping_method_internal_name(); ?>")' value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
							<?php else: ?>
								<input <?php echo wpsc_shipping_quote_selected_state(); ?> disabled='disabled' type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>'  value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
									<?php wpsc_update_shipping_single_method(); ?>
							<?php endif; ?>
							</td>
						</tr>
			
					<?php endwhile; ?>
			<?php endwhile;  ?>
			<?php wpsc_update_shipping_multiple_methods(); ?>
		</table>
	<?php endif;  ?>
	
	<table class="productcart">
	<tr class="total_price total_tax">
		<td colspan="3">
			<?php echo TXT_WPSC_TAX; ?>
		</td>
		<td colspan="2">
			<span id="checkout_tax" class="pricedisplay checkout-tax"><?php echo wpsc_cart_tax(); ?></span>
	  </td>
	</tr>
	  <?php if(wpsc_uses_coupons()): ?>
	<tr class="total_price">
		<td colspan="3">
			<?php echo TXT_WPSC_COUPONS; ?>
		</td>
		<td colspan="2">
			<span id="coupons_amount" class="pricedisplay"><?php echo wpsc_coupon_amount(); ?></span>
	  </td>
	  <?php endif ?>
	</tr>
		
	
	<tr class='total_price'>
		<td colspan='3'>
		<?php echo TXT_WPSC_TOTALPRICE; ?>
		</td>
		<td colspan='2'>
			<span id='checkout_total' class="pricedisplay checkout-total"><?php echo wpsc_cart_total(); ?></span>
		</td>
	</tr>
	
	
	</table>
	
<!-- <pre><?php#print_r($wpsc_cart);?></pre> -->

	<?php  //this HTML displays the Checkout form fields (individual form fields are generated by php)	?>


	<h2><?php echo TXT_WPSC_CONTACTDETAILS; ?></h2>
	<?php echo TXT_WPSC_CREDITCARDHANDY; ?><br />
	<?php echo TXT_WPSC_ASTERISK; ?>
<form action='' method='post' enctype="multipart/form-data">
	<table class='wpsc_checkout_table'>
		<?php while (wpsc_have_checkout_items()) : wpsc_the_checkout_item(); ?>
		<tr <?php echo wpsc_the_checkout_item_error_class();?>>
		  <?php if(wpsc_checkout_form_is_header() == true) : ?>
			<td colspan='2'>
				<h4>
					<?php echo wpsc_checkout_form_name();?>
				</h4>
			</td>
		  <?php else: ?>
			<td>
				<label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
				<?php echo wpsc_checkout_form_name();?>:
				</label>
			</td>
			<td>
				<?php echo wpsc_checkout_form_field();?>
				
		    <?php if(wpsc_the_checkout_item_error() != ''): ?>
		    <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
			<?php endif; ?>
			</td>
			<?php endif; ?>
		</tr>
		<?php endwhile; ?>
		<tr>
			<td colspan='2'>
			
			<?php  //this HTML displays activated payment gateways?>
			  
				<?php if(wpsc_gateway_count() > 1): // if we have more than one gateway enabled, offer the user a choice ?>
					<h3><?php echo TXT_WPSC_SELECTGATEWAY;?></h3>
					<?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
						<div class="custom_gateway">
							<label><input type="radio" value="<?php echo wpsc_gateway_internal_name();?>" checked="checked" name="custom_gateway" class="custom_gateway"/><?php echo wpsc_gateway_name();?></label>
							
							<?php if(wpsc_gateway_form_fields()): ?> 
								<table class='<?php echo wpsc_gateway_form_field_style();?>'>
									<?php echo wpsc_gateway_form_fields();?> 
								</table>		
							<?php endif; ?>			
						</div>
					<?php endwhile; ?>
				<?php else: // otherwise, there is no choice, stick in a hidden form ?>
					<?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
						<input name='custom_gateway' value='<?php echo wpsc_gateway_internal_name();?>' type='hidden' />
						
							<?php if(wpsc_gateway_form_fields()): ?> 
								<table>
									<?php echo wpsc_gateway_form_fields();?> 
								</table>		
							<?php endif; ?>	
					<?php endwhile; ?>				
				<?php endif; ?>				
				
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input type='hidden' value='submit_checkout' name='wpsc_action' />
				<input type='submit' value='<?php echo TXT_WPSC_MAKEPURCHASE;?>' name='submit' class='make_purchase' />
			</td>
		</tr>
	</table>
		
</form>