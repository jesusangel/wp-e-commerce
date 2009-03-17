<?php
global $wpsc_cart, $wpdb, $wpsc_checkout;
$wpsc_checkout = new wpsc_checkout();
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
	</table>
	
		
	<h2><?php echo TXT_WPSC_SHIPPING_COUNTRY; ?></h2>
	<?php echo TXT_WPSC_SHIPPING_DETAIL; ?>
	
	<form name='change_country' action='' method='POST'>
	<?php echo wpsc_shipping_country_list();?>
	</form>


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
				<input type='hidden' value='true' name='submitwpcheckout' />
				<input type='submit' value='<?php echo TXT_WPSC_MAKEPURCHASE;?>' name='submit' class='make_purchase' />
			</td>
		</tr>
	</table>
</form>