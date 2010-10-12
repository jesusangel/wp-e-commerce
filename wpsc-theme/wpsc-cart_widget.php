<?php
 //echo "<pre>".print_r($GLOBALS['wpsc_cart']->cart_items[0], true)."</pre>";
?>
<?php if(isset($cart_messages) && count($cart_messages) > 0) { ?>
	<?php foreach((array)$cart_messages as $cart_message) { ?>
	  <span class="cart_message"><?php echo $cart_message; ?></span>
	<?php } ?>
<?php } ?>

<?php if(wpsc_cart_item_count() > 0): ?>
		<span class="numberitems">
			<?php _e('Number of items', 'wpsc'); ?>:
			<?php echo wpsc_cart_item_count(); ?>
		</span>
    <div class="shoppingcart">
	<table>
		<tr>
			<th id="product"><?php _e('Product', 'wpsc'); ?></th>
			<th id="quantity"><?php _e('Qty', 'wpsc'); ?></th>
			<th id="price"><?php _e('Price', 'wpsc'); ?></th>
            <th id="remove">&nbsp;</th>
		</tr>
		<?php while(wpsc_have_cart_items()): wpsc_the_cart_item(); ?>
			<tr>
					<td><?php echo wpsc_cart_item_name(); ?></td>
					<td><?php echo wpsc_cart_item_quantity(); ?></td>
					<td><?php echo wpsc_cart_item_price(); ?></td>
                    <td><form action="" method="post" class="adjustform">
					<input type="hidden" name="quantity" value="0" />
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
					<input type="hidden" name="wpsc_update_quantity" value="true" />
					<input class="remove_button" type="submit" />
				</form></td>
			</tr>	
		<?php endwhile; ?>
	</table>
	</div><!--close shoppingcart-->
<?php if(wpsc_cart_has_shipping() && !wpsc_cart_show_plus_postage()) : ?>
		  <span class="pricedisplay checkout-shipping"><?php _e('Shipping', 'wpsc'); ?>: <?php echo wpsc_cart_shipping(); ?></span>
	<?php endif; ?>
<?php if( (wpsc_cart_tax(false) >0) && !wpsc_cart_show_plus_postage()) : ?>
		  <span class="pricedisplay checkout-tax"><?php echo wpsc_display_tax_label(true); ?>: <?php echo wpsc_cart_tax(); ?></span>			
	<?php endif; ?>
		<span class="pricedisplay checkout-total">
			<?php _e('Total', 'wpsc'); ?>: <?php echo wpsc_cart_total_widget(); ?>
			<?php if(wpsc_cart_show_plus_postage()) : ?>
				<span class="pluspostagetax"> + <?php _e('Postage &amp; Tax ', 'wpsc'); ?></span>
			<?php endif; ?>
		</span>	

	
	<a target="_parent" href="<?php echo get_option('shopping_cart_url'); ?>" title="Checkout" class="gocheckout"><?php _e('Checkout', 'wpsc'); ?></a>
<?php else: ?>
	<p class="empty"><?php _e('Your shopping cart is empty', 'wpsc'); ?></p>
	  <a target="_parent" href="<?php echo get_option('product_list_url'); ?>" class="visitshop" title="Visit Shop"><?php _e('Visit the shop', 'wpsc'); ?></a>
<?php endif; ?>
<?php if (wpsc_have_cart_items()) : ?>
	<form action="" method="post" class="wpsc_empty_the_cart">
		<input type="hidden" name="wpsc_ajax_action" value="empty_cart" />
			<a target="_parent" href="<?php echo htmlentities(add_query_arg('wpsc_ajax_action', 'empty_cart', remove_query_arg('ajax')), ENT_QUOTES); ?>" class="emptycart" title="Empty Your Cart"><?php _e('Empty your cart', 'wpsc'); ?></a>                                                                                    
	</form>
<?php endif; ?>

<?php
wpsc_google_checkout();


?>