<?php
global $wpsc_cart, $wpdb, $wpsc_checkout;
$wpsc_checkout = new wpsc_checkout();
 //echo "<pre>".print_r($_SESSION,true)."</pre>";
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
		<tr class="product_row">
			<td class="firstcol"><img src='<?php echo wpsc_cart_item_image(); ?>' alt='<?php echo wpsc_cart_item_name(); ?>' title='<?php echo wpsc_cart_item_name(); ?>' /></td>
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
					<button class='remove_button' type="submit"/><span><?php echo TXT_WPSC_REMOVE; ?></span></button>
				</form>
			</td>
		</tr>
	<?php endwhile; ?>
	
	<tr>
		<td colspan='5'>
      <h2><?php echo TXT_WPSC_SHIPPING_COUNTRY; ?></h2>
			<?php echo TXT_WPSC_SHIPPING_DETAIL; ?>
		</td>
	</tr>
	
	<tr>
		<td colspan='5'>
			<form name='change_country' action='' method='POST'>
				<?php echo wpsc_shipping_country_list();?>
				<input type='hidden' name='wpsc_update_location' value='true' />
			</form>
		</td>
	</tr>
	
	<?php while (wpsc_have_shipping_methods()) : wpsc_the_shipping_method(); ?>
			<tr><td class='shipping_header' colspan='5'><?php echo wpsc_shipping_method_name().TXT_WPSC_CHOOSE_A_SHIPPING_RATE; ?> </td></tr>
			<?php while (wpsc_have_shipping_quotes()) : wpsc_the_shipping_quote(); ?>
				<tr>
				  <td colspan='3'>
				    <label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_name(); ?></label>
				  </td>
				  <td>
				    <label for='<?php echo wpsc_shipping_quote_html_id(); ?>'><?php echo wpsc_shipping_quote_value(); ?></label>
				  </td>
				  <td style='text-align:center;'>
				    <input type='radio' id='<?php echo wpsc_shipping_quote_html_id(); ?>' <?php echo wpsc_shipping_quote_selected_state(); ?> onclick='switchmethod("<?php echo wpsc_shipping_quote_name(); ?>", "<?php echo wpsc_shipping_method_internal_name(); ?>")' value='<?php echo wpsc_shipping_quote_value(true); ?>' name='shipping_method' />
				  </td>
				</tr>
			<?php endwhile; ?>
	<?php endwhile; ?>
	
	<tr class="total_price total_tax">
		<td colspan="3">
			<?php echo TXT_WPSC_TAX; ?>
		</td>
		<td colspan="2">
			<span id="checkout_tax" class="pricedisplay"><?php echo wpsc_cart_tax(); ?></span>
	  </td>
	</tr>
	
	<tr class='total_price'>
		<td colspan='3'>
		<?php echo TXT_WPSC_TOTALPRICE; ?>
		</td>
		<td colspan='2'>
			<span id='checkout_total' class="pricedisplay"><?php echo wpsc_cart_total(); ?></span>
		</td>
	</tr>
	
	
	</table>
	
<!-- <pre><?php#print_r($wpsc_cart);?></pre> -->



	<h2><?php echo TXT_WPSC_CONTACTDETAILS; ?></h2>
<form action='' method='POST' enctype="multipart/form-data">
	<table class='wpsc_checkout_table'>
		<?php while (wpsc_have_checkout_items()) : wpsc_the_checkout_item(); ?>
		<tr>
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
			</td>
			<?php endif; ?>
		</tr>
		<?php endwhile; ?>
		<tr>
			<td colspan='2'>
				<input type='hidden' value='submit_checkout' name='wpsc_action' />
				<input type='submit' value='<?php echo TXT_WPSC_MAKEPURCHASE;?>' name='submit' class='make_purchase' />
			</td>
		</tr>
	</table>
</form>