<?php
global $wpsc_cart, $wpdb, $wpsc_checkout, $wpsc_gateway, $wpsc_coupons;
$wpsc_checkout = new wpsc_checkout();
$wpsc_gateway = new wpsc_gateways();

if(isset($_SESSION['coupon_numbers']))
	$wpsc_coupons = new wpsc_coupons($_SESSION['coupon_numbers']);

if(wpsc_cart_item_count() < 1) :
	_e('Oops, there is nothing in your cart.', 'wpsc') . "<a href=".get_option("product_list_url").">" . __('Please visit our shop', 'wpsc') . "</a>";
	return;
endif;
?>
<div id="checkout_page_container">
<h3><?php _e('Please review your order', 'wpsc'); ?></h3>
<table class="checkout_cart">
	<tr class="header">
    	<td>&nbsp;</td>
		<td><?php _e('ITEM', 'wpsc'); ?></td>
		<td><?php _e('QUANTITY', 'wpsc'); ?></td>
		<?php if(wpsc_uses_shipping()): ?>
		<!-- 	<td><?php //_e('Shipping', 'wpsc'); ?>:</td> -->
		<?php endif; ?>
		<td><?php _e('PRICE', 'wpsc'); ?></td>
        <td><?php _e('TOTAL', 'wpsc'); ?></td>
        <td>&nbsp;</td>
	</tr>
	<?php while (wpsc_have_cart_items()) : wpsc_the_cart_item(); ?>
		<?php
	    $alt++;
	    if ($alt %2 == 1) 
	    	$alt_class = 'alt';
	    else	
	    	$alt_class = '';
	    ?>
		<?php  //this displays the confirm your order html	?>
		
		<tr class="product_row product_row_<?php echo wpsc_the_cart_item_key(); ?> <?php echo $alt_class;?>">

			<td class="firstcol wpsc_product_image wpsc_product_image_<?php echo wpsc_the_cart_item_key(); ?>">
			<?php if('' != wpsc_cart_item_image()): ?>
				<img src="<?php echo wpsc_cart_item_image(48,48); ?>" alt="<?php echo wpsc_cart_item_name(); ?>" title="<?php echo wpsc_cart_item_name(); ?>" class="product_image" />
			<?php else: ?>
				<div class="item_no_image">
					<a href="<?php echo wpsc_the_product_permalink(); ?>">
					<span>No Image Available</span>
					</a>
				</div>
			<?php endif; ?>
			</td>

			<td class="wpsc_product_name wpsc_product_name_<?php echo wpsc_the_cart_item_key(); ?>">
				<a href="<?php echo wpsc_cart_item_url();?>"><?php echo wpsc_cart_item_name(); ?></a>
			</td>

			<td class="wpsc_product_quantity wpsc_product_quantity_<?php echo wpsc_the_cart_item_key(); ?>">
				<form action="<?php echo get_option('shopping_cart_url'); ?>" method="post" class="adjustform qty">
					<input type="text" name="quantity" size="2" value="<?php echo wpsc_cart_item_quantity(); ?>" />
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
					<input type="hidden" name="wpsc_update_quantity" value="true" />
					<input type="submit" value="<?php _e('Update', 'wpsc'); ?>" name="submit" />
				</form>
			</td>

			<?php if(wpsc_uses_shipping()): ?>

			<?php endif; ?>
            <td><?php echo wpsc_cart_single_item_price(); ?></td>
			<td class="wpsc_product_price wpsc_product_price_<?php echo wpsc_the_cart_item_key(); ?>"><span class="pricedisplay"><?php echo wpsc_cart_item_price(); ?></span></td>
			
			<td class="wpsc_product_remove wpsc_product_remove_<?php echo wpsc_the_cart_item_key(); ?>">
				<form action="<?php echo get_option('shopping_cart_url'); ?>" method="post" class="adjustform remove">
					<input type="hidden" name="quantity" value="0" />
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
					<input type="hidden" name="wpsc_update_quantity" value="true" />
					<input type="submit" value="<?php _e('Remove', 'wpsc'); ?>" name="submit" />
				</form>
			</td>
		</tr>
	<?php endwhile; ?>
	<?php //this HTML displays coupons if there are any active coupons to use ?>
	
	<?php //echo "<pre>"; print_r($wpsc_cart); echo "</pre>"; echo "total cart weight: ".wpsc_cart_weight_total(); 

 	if(wpsc_uses_coupons()): ?>
		
		<?php if(wpsc_coupons_error()): ?>
			<tr class="wpsc_coupon_error_row"><td><?php _e('Coupon is not valid.', 'wpsc'); ?></td></tr>
		<?php endif; ?>
		<tr class="wpsc_coupon_row">
			<td colspan="2"><?php _e('Enter your coupon number'); ?> :</td>
			<td  colspan="3" align='left'>
				<form  method="post" action="<?php echo get_option('shopping_cart_url'); ?>">
					<input type="text" name="coupon_num" id="coupon_num" value="<?php echo $wpsc_cart->coupons_name; ?>" />
					<input type="submit" value="<?php _e('Update', 'wpsc') ?>" />
				</form>
			</td>
		</tr>
	<?php endif; ?>	
	</table>
	<!-- cart contents table close -->
	
	<p class="wpsc_cost_before"><?php _e('Cost before shipping = ','wpsc'); ?> <?php echo wpsc_cart_total_widget(false,false,false);?></p>
	<?php  //this HTML dispalys the calculate your order HTML	?>

	<?php if(wpsc_has_noca_message()): ?>
		<p class="validation-error"><?php echo $_SESSION['nocamsg']; ?></p>
	<?php endif; ?>
	
	<?php if(wpsc_has_category_and_country_conflict()): ?>
		<p class='validation-error'><?php echo $_SESSION['categoryAndShippingCountryConflict']; ?></p>
	<?php endif;
	
	if(isset($_SESSION['WpscGatewayErrorMessage']) && $_SESSION['WpscGatewayErrorMessage'] != '') :?>
		<p class="validation-error"><?php echo $_SESSION['WpscGatewayErrorMessage']; ?></p>
	<?php
	endif;
	?>
	
	<?php do_action('wpsc_before_shipping_of_shopping_cart'); ?>
	
	<div id="wpsc_shopping_cart_container">
	<?php if(wpsc_uses_shipping()) : ?>
		<h2><?php _e('Calculate Shipping Price', 'wpsc'); ?></h2>
		<table class="productcart">
			<tr class="wpsc_shipping_info">
				<td colspan="5">
					<?php _e('Please choose a country below to calculate your shipping costs', 'wpsc'); ?>
				</td>
			</tr>

			<?php if (!wpsc_have_shipping_quote()) : // No valid shipping quotes ?>
				<?php if (wpsc_have_valid_shipping_zipcode()) : ?>
					<?php if ($_SESSION['wpsc_update_location'] == true) :?>
						<tr class='wpsc_update_location'>
							<td colspan='5' class='shipping_error' >
								<?php _e('Please provide a Zipcode and click Calculate in order to continue.', 'wpsc'); ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php else: ?>
					<tr class='wpsc_update_location_error'>
						<td colspan='5' class='shipping_error' >
							<?php _e('Sorry, online ordering is unavailable to this destination and/or weight. Please double check your destination details.', 'wpsc'); ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endif; ?>
			<tr class='wpsc_change_country'>
				<td colspan='5'>
					<form name='change_country' id='change_country' action='' method='post'>
						<?php echo wpsc_shipping_country_list();?>
						<input type='hidden' name='wpsc_update_location' value='true' />
						<input type='submit' name='wpsc_submit_zipcode' value='Calculate' />
					</form>
				</td>
			</tr>
			
			<?php if (wpsc_have_morethanone_shipping_quote()) :?>
				<?php while (wpsc_have_shipping_methods()) : wpsc_the_shipping_method(); ?>
						<?php 	if (!wpsc_have_shipping_quotes()) { continue; } // Don't display shipping method if it doesn't have at least one quote ?>
						<tr class='wpsc_shipping_header'><td class='shipping_header' colspan='5'><?php echo wpsc_shipping_method_name().__('- Choose a Shipping Rate', 'wpsc'); ?> </td></tr>
						<?php while (wpsc_have_shipping_quotes()) : wpsc_the_shipping_quote();	?>
							<tr class='<?php echo wpsc_shipping_quote_html_id(); ?>'>
								<td class='wpsc_shipping_quote_name wpsc_shipping_quote_name_<?php echo wpsc_shipping_quote_html_id(); ?>' colspan='3'>
									<label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_name(); ?></label>
								</td>
								<td class='wpsc_shipping_quote_price wpsc_shipping_quote_price_<?php echo wpsc_shipping_quote_html_id(); ?>' style='text-align:center;'>
									<label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_value(); ?></label>
								</td>
								<td class='wpsc_shipping_quote_radio wpsc_shipping_quote_radio_<?php echo wpsc_shipping_quote_html_id(); ?>' style='text-align:center;'>
									<?php if(wpsc_have_morethanone_shipping_methods_and_quotes()): ?>
										<input type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>' <?php echo wpsc_shipping_quote_selected_state(); ?>  onclick='switchmethod("<?php echo wpsc_shipping_quote_name(); ?>", "<?php echo wpsc_shipping_method_internal_name(); ?>")' value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
									<?php else: ?>
										<input <?php echo wpsc_shipping_quote_selected_state(); ?> disabled='disabled' type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>'  value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
											<?php wpsc_update_shipping_single_method(); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endwhile; ?>
				<?php endwhile; ?>
			<?php endif; ?>
			
			<?php wpsc_update_shipping_multiple_methods(); ?>

			
			<?php if (!wpsc_have_shipping_quote()) : // No valid shipping quotes ?>
					</table>
					</div>
				<?php return; ?>
			<?php endif; ?>
		</table>
	<?php endif;  ?>
	
	<table class="productcart">
		<tr class="total_price total_tax">
			<td colspan="3">
				<?php echo wpsc_display_tax_label(true); ?>
			</td>
			<td colspan="2">
				<span id="checkout_tax" class="pricedisplay checkout-tax"><?php echo wpsc_cart_tax(); ?></span>
			</td>
		</tr>
	</table>
	<?php do_action('wpsc_before_form_of_shopping_cart'); ?>
	
	<form class='wpsc_checkout_forms' action='' method='post' enctype="multipart/form-data">
	
	   <?php 
	   /**  
	    * Both the registration forms and the checkout details forms must be in the same form element as they are submitted together, you cannot have two form elements submit together without the use of JavaScript.
	   */
	   ?>

	 <?php if(wpsc_show_user_login_form()):
			 global $current_user;
    		 get_currentuserinfo();	  ?>
			<h2><?php _e('Not yet a member?');?></h2>
			<p><?php _e('In order to buy from us, you\'ll need an account. Joining is free and easy. All you need is a username, password and valid email address.');?></p>
			
			<?php if(!empty($_SESSION['wpsc_checkout_user_error_messages'])): ?>
				<div class="login_error"> 
					<?php		  
					foreach($_SESSION['wpsc_checkout_user_error_messages'] as $user_error )
					  echo $user_error."<br />\n";
					
					$_SESSION['wpsc_checkout_user_error_messages'] = array();
					?>			
			    </div>
			<?php endif; ?>
					
			<fieldset class='wpsc_registration_form'>
				<label><?php _e('Username'); ?>:</label><input type="text" name="log" id="log" value="" size="20"/>
				<label><?php _e('Password'); ?>:</label><input type="password" name="pwd" id="pwd" value="" size="20" />
				<label><?php _e('E-mail'); ?>:</label><input type="text" name="user_email" id="user_email" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="20" />
			</fieldset>
	<?php endif; // closes user login form
	
	  	if(!empty($_SESSION['wpsc_checkout_misc_error_messages'])): ?>
			<div class='login_error'>
				<?php foreach((array)$_SESSION['wpsc_checkout_misc_error_messages'] as $user_error ){?>
					<?php echo $user_error; ?><br />
					<?php } ?>
			</div>
		<?php	
		endif;
		 $_SESSION['wpsc_checkout_misc_error_messages'] =array(); ?>
		
	<table class='wpsc_checkout_table table-1'>
		<?php $i = 0; 
		while (wpsc_have_checkout_items()) : wpsc_the_checkout_item(); ?>
		
		  <?php if(wpsc_checkout_form_is_header() == true){
		  			$i++;
		  			//display headers for form fields ?>
					<?php if($i > 1):?>
						</table>
						<table class='wpsc_checkout_table table-<?php echo $i; ?>'>
					<?php endif; ?>
			  
			  		<tr <?php echo wpsc_the_checkout_item_error_class();?>>
						<td <?php wpsc_the_checkout_details_class(); ?> colspan='2'>
							<h4><?php echo wpsc_checkout_form_name();?></h4>
						</td>
					</tr>
					<?php if(!wpsc_is_shipping_details() && $i <= 1):?>	
					<tr class='same_as_shipping_row'>
						<td colspan ='2'>
							<input type='checkbox' value='true' name='billing_same_as_shipping' id='billing_same_as_shipping' />
							<label for='billing_same_as_shipping'><?php _e('Same as shipping address?','wpsc'); ?></label>
						</td>
					</tr>
					<?php endif;
					
				// Not a header so start display form fields 
				}elseif(wpsc_disregard_state_fields()){		  
		  		// if state fields are not required (i.e country has drop down regions)
		 		}elseif( $wpsc_checkout->checkout_item->unique_name == 'billingemail'){ ?>
					<div class='wpsc_email_address'>
						<p class='<?php echo wpsc_checkout_form_element_id(); ?>'>
							<label class='wpsc_email_address' for='<?php echo wpsc_checkout_form_element_id(); ?>'>
							<?php echo wpsc_checkout_form_name();?>
							</label>
						<p>
						<?php echo wpsc_checkout_form_field();?>
					    <?php if(wpsc_the_checkout_item_error() != ''): ?>
						    <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
						<?php endif; ?>
					</div>	<?php
				 }else{ ?>
		  	
					<td class='<?php echo wpsc_checkout_form_element_id(); ?>'>
						<label for='<?php echo wpsc_checkout_form_element_id(); ?>'>
						<?php echo wpsc_checkout_form_name();?>						
						</label>
					</td>
					<td>
						<?php echo wpsc_checkout_form_field();?>						
					    <?php if(wpsc_the_checkout_item_error() != ''): ?>
					  			  <p class='validation-error'><?php echo wpsc_the_checkout_item_error(); ?></p>
						<?php endif; ?>
					</td>
				</tr>
		
			<?php }//endif; ?>
		
		<?php endwhile; ?>
		
		<?php if (wpsc_show_find_us()) : ?>
		<tr>
			<td><?php _e('How did you find us' , 'wpsc'); ?> :</td>
			<td>
				<select name='how_find_us'>
					<option value='Word of Mouth'>Word of mouth</option>
					<option value='Advertisement'>Advertising</option>
					<option value='Internet'>Internet</option>
					<option value='Customer'>Existing Customer</option>
				</select>
			</td>
		</tr>
		<?php endif; ?>		
		<?php do_action('wpsc_inside_shopping_cart'); ?>
		
		<?php  //this HTML displays activated payment gateways?>
		<?php if(wpsc_gateway_count() > 1): // if we have more than one gateway enabled, offer the user a choice ?>
			<tr>
			<td colspan='2' class='wpsc_gateway_container'>
				<h3><?php _e('Select a payment gateway', 'wpsc');?></h3>
				<?php while (wpsc_have_gateways()) : wpsc_the_gateway(); ?>
					<div class="custom_gateway">
						<?php if(wpsc_gateway_internal_name() == 'noca'){ ?>
							<label><input type="radio" id='noca_gateway' value="<?php echo wpsc_gateway_internal_name();?>" <?php echo wpsc_gateway_is_checked(); ?> name="custom_gateway" class="custom_gateway"/><?php echo wpsc_gateway_name();?></label>
						<?php }else{ ?>
							<label><input type="radio" value="<?php echo wpsc_gateway_internal_name();?>" <?php echo wpsc_gateway_is_checked(); ?> name="custom_gateway" class="custom_gateway"/><?php echo wpsc_gateway_name();?></label>
						<?php } ?>

						
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
			</td>
			</tr>		
			<?php endif; ?>						
		
		<?php if(wpsc_has_tnc()) : ?>
			<tr>
				<td colspan='2'>
	     			 <input type='checkbox' value='yes' name='agree' /> <?php _e('I agree to The ', 'wpsc');?>
	     			 <a class='thickbox' target='_blank' href='<?php
	      echo site_url("?termsandconds=true&amp;width=360&amp;height=400'"); ?>' class='termsandconds'> <?php _e('Terms and Conditions', 'wpsc');?></a>
	   		   </td>
	 	   </tr>
		<?php endif; ?>	
		</table>
		
	<table  class='wpsc_checkout_table table-4'>
		<tr>
			<td class='wpsc_total_price_and_shipping'colspan='2'>
				<h4><?php _e('Total Price with Shipping','wpsc'); ?></h4>
			</td>
		</tr>
		<?php if(wpsc_uses_shipping()) : ?>
		<tr class="total_price total_shipping">
			<td class='wpsc_totals'>
				<?php _e('Total Shipping', 'wpsc'); ?>
			</td>
			<td class='wpsc_totals'>
				<span id="checkout_shipping" class="pricedisplay checkout-shipping"><?php echo wpsc_cart_shipping(); ?></span>
			</td>
		</tr>
		<?php endif; ?>

	  <?php if(wpsc_uses_coupons() && (wpsc_coupon_amount(false) > 0)): ?>
		<tr class="total_price">
			<td class='wpsc_totals'>
				<?php _e('Discount', 'wpsc'); ?>
			</td>
			<td class='wpsc_totals'>
				<span id="coupons_amount" class="pricedisplay"><?php echo wpsc_coupon_amount(); ?></span>
		    </td>
	   	</tr>
	  <?php endif ?>

		
	
	<tr class='total_price'>
		<td class='wpsc_totals'>
		<?php _e('Total Price', 'wpsc'); ?>
		</td>
		<td class='wpsc_totals'>
			<span id='checkout_total' class="pricedisplay checkout-total"><?php echo wpsc_cart_total(); ?></span>
		</td>
	</tr>
	</table>	

<!-- div for make purchase button -->
		<div class='wpsc_make_purchase'>
			<span>
				<?php if(!wpsc_has_tnc()) : ?>
					<input type='hidden' value='yes' name='agree' />
				<?php endif; ?>	
				<?php //exit('<pre>'.print_r($wpsc_gateway->wpsc_gateways[0]['name'], true).'</pre>');
				 if(wpsc_is_noca_gateway()){
				 
				 }else{?>
					<input type='hidden' value='submit_checkout' name='wpsc_action' />
					<input type='submit' value='<?php _e('Purchase', 'wpsc');?>' name='submit' class='make_purchase wpsc_buy_button' />
				<?php } ?>				
			</span>
		</div>

<div class='clear'></div>
</form>
</div>
</div><!--close checkout_page_container-->
<?php
do_action('wpsc_bottom_of_shopping_cart');

?>