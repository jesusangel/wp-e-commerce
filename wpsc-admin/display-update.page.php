<?php
/**
 * WP eCommerce database updating page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.8
 */

function wpsc_display_update_page() {
	?>
	<div class="wrap">
		<?php // screen_icon(); ?>
		<h2><?php echo wp_specialchars( __('Update WP e-Commerce', 'wpsc') ); ?> </h2>
		
		this is the update page<br />
		click here to run the second part of the update
		
		click here to run the update (updating categories) <br />
		<?php
		wpsc_convert_category_groups();
		?>
		
		click here to run the next part of the update (updating variation groups and variations) <br />
		<?php
		wpsc_convert_variation_sets();
		?>
		click here to run the next part of the update (updating products and product images) <br />
		<pre>
		<?php
		wpsc_convert_products_to_posts();
		?>
		</pre>
		click here to run the next part of the update (updating variation combinations into child products) <br />
		<pre><?php 
		wpsc_convert_variation_combinations();
		?></pre>
		
		
		click here to run the next part of the update (updating product files) <br />
		<pre><?php 
		
		wpsc_update_files();
		
		?></pre>
		<?php 
}

?>