<?php
global $wpsc_cart, $wpdb;
?>

<?php if(wpsc_cart_item_count() > 0) : ?>
  <span class='items'>
		<span class='numberitems'>
			<?php echo TXT_WPSC_NUMBEROFITEMS; ?>:
		</span>
		<span class='cartcount'>
			<?php echo wpsc_cart_item_count(); ?>
		</span>
	</span>
	<table class='shoppingcart'>
		<tr>
			<th id='product'><?php echo TXT_WPSC_PRODUCT; ?></th>
			<th id='quantity'><?php echo TXT_WPSC_QUANTITY_SHORT; ?></th>
			<th id='price'><?php echo TXT_WPSC_PRICE; ?></th>
		</tr>
			<?php while (wpsc_have_cart_items()) : wpsc_the_cart_item(); ?>
			<tr>
					<td><?php echo wpsc_cart_item_name(); ?></td>
					<td><?php echo wpsc_cart_item_quantity(); ?></td>
					<td><?php echo wpsc_cart_item_price(); ?></td>
			</tr>	
		<?php endwhile; ?>
	</table>
	
	<?php
// 		echo "<pre style='text-align: left;'>"."\n\r";
// 		echo print_r($wpsc_cart->shipping_method, true)."\n\r";
// 		echo print_r($wpsc_cart->shipping_option, true)."\n\r";
// 		echo print_r($wpsc_cart->shipping_quotes, true)."\n\r";
// 		echo "</pre>"."\n\r";
	?>
	
<?php if(wpsc_cart_has_shipping()) : ?>
		<span class='total'>
		<span class='totalhead'>
			<?php echo TXT_WPSC_SHIPPING; ?>:
	  </span>
	  <?php echo wpsc_cart_shipping(); ?>
	</span>
	<?php endif; ?>
	
	<span class='total'>
		<span class='totalhead'>
			<?php echo TXT_WPSC_TOTAL; ?>:
	  </span>
	  <?php echo wpsc_cart_total(); ?>
	</span>
	
	
	<form action='' method='POST' class='wpsc_empty_the_cart'>
		<input type='hidden' name='wpsc_ajax_action' value='empty_cart' />
		<span class='emptycart'>
			<button type='submit'><span><?php echo TXT_WPSC_EMPTYYOURCART; ?></span></button>
		</span>
	</form>
	
	<span class='gocheckout'><a href='<?php echo get_option('shopping_cart_url'); ?>'><?php echo TXT_WPSC_GOTOCHECKOUT; ?></a></span>
<?php else: ?>
	
	
	<p class="empty"><?php echo TXT_WPSC_YOURSHOPPINGCARTISEMPTY; ?></p>
	<p class="visitshop">
	  <a href="<?php echo get_option('product_list_url'); ?>"><?php echo TXT_WPSC_VISITTHESHOP; ?></a>
	</p>
<?php endif; ?>


<?php
?>