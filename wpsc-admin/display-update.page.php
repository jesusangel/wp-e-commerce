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
		<?php
		wpsc_convert_category_groups();
		
		
		wpsc_convert_variation_sets();
		
		?><pre>
		<?php
		wpsc_convert_products_to_posts();
		?></pre>
		<pre><?php 
		
		//wpsc_convert_variation_combinations();
		
		?></pre>
		<?php 
}

?>