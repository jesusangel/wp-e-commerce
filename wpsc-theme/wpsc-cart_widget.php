<?php
 //echo "<pre>".print_r($GLOBALS['wpsc_cart']->cart_items[0], true)."</pre>";
?>
<?php if(isset($cart_messages) && count($cart_messages) > 0) { ?>
	<?php foreach((array)$cart_messages as $cart_message) { ?>
	  <span class="cart_message"><?php echo $cart_message; ?></span>
	<?php } ?>
<?php } ?>

<?php if(wpsc_cart_item_count() > 0): ?>
    <div class="shoppingcart">
	<table>
		<thead>
			<tr>
				<th id="product" colspan='2'><?php _e('Product', 'wpsc'); ?></th>
				<th id="quantity"><?php _e('Qty', 'wpsc'); ?></th>
				<th id="price"><?php _e('Price', 'wpsc'); ?></th>
	            <th id="remove">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php while(wpsc_have_cart_items()): wpsc_the_cart_item(); ?>
			<tr>
					<td colspan='2' class='product-name'><a href="<?php echo wpsc_cart_item_url(); ?>"><?php echo wpsc_cart_item_name(); ?></a></td>
					<td><a href="<?php echo wpsc_cart_item_url(); ?>"><?php echo wpsc_cart_item_quantity(); ?></a></td>
					<td><a href="<?php echo wpsc_cart_item_url(); ?>"><?php echo wpsc_cart_item_price(); ?></a></td>
                    <td class="cart-widget-remove"><form action="" method="post" class="adjustform">
					<input type="hidden" name="quantity" value="0" />
					<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>" />
					<input type="hidden" name="wpsc_update_quantity" value="true" />
					<input class="remove_button" type="submit" />
				</form></td>
			</tr>	
		<?php endwhile; ?>
		<tbody>
		<tfoot>
			<tr class="cart-widget-total">
				<td class="cart-widget-count">
					<?php printf( _n('%d item', '%d items', wpsc_cart_item_count(), 'wpsc'), wpsc_cart_item_count() ); ?>
				</td>
				<td class="pricedisplay checkout-total" colspan='4'>
					<?php _e('Total', 'wpsc'); ?>: <?php echo wpsc_cart_total_widget(); ?>
				</td>
			</tr>
			<?php if(wpsc_cart_show_plus_postage()) : ?>
			<tr>
				<td class="pluspostagetax" colspan='5'>
					+ <?php _e('Postage &amp; Tax ', 'wpsc'); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if(wpsc_cart_has_shipping() && !wpsc_cart_show_plus_postage()) : ?>
			<tr>
				<td class="pricedisplay checkout-shipping" colspan='5'>
					<?php _e('Shipping', 'wpsc'); ?>: <?php echo wpsc_cart_shipping(); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if( (wpsc_cart_tax(false) >0) && !wpsc_cart_show_plus_postage()) : ?>
			<tr>
				<td class="pricedisplay checkout-tax" colspan='5'>
					<?php echo wpsc_display_tax_label(true); ?>: <?php echo wpsc_cart_tax(); ?>
				</td>
			</tr>                
			<?php endif; ?>
		</tfoot>
	</table>
	</div><!--close shoppingcart-->		
	<div id='cart-widget-links'>
		<a target="_parent" href="<?php echo get_option('shopping_cart_url'); ?>" title="Checkout" class="gocheckout"><?php _e('Checkout', 'wpsc'); ?></a>
		<form action="" method="post" class="wpsc_empty_the_cart">
			<input type="hidden" name="wpsc_ajax_action" value="empty_cart" />
				<a target="_parent" href="<?php echo htmlentities(add_query_arg('wpsc_ajax_action', 'empty_cart', remove_query_arg('ajax')), ENT_QUOTES); ?>" class="emptycart" title="Empty Your Cart"><?php _e('Clear cart', 'wpsc'); ?></a>                                                                                    
		</form>
	</div>
<?php else: ?>
	<p class="empty"><?php _e('Your shopping cart is empty', 'wpsc'); ?></p>
	  <a target="_parent" href="<?php echo get_option('product_list_url'); ?>" class="visitshop" title="Visit Shop"><?php _e('Visit the shop', 'wpsc'); ?></a>
<?php endif; ?>

<?php
wpsc_google_checkout();


?>