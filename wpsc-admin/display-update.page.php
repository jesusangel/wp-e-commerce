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
		<br />
		Currently this page runs when loaded and updates your site from previous versions of WP e-Commerce. However shortly this will be overhauled to run like the WordPress Database upgrade page.
		<br />
		A still pending 3.8 job is to turn the following into clickable links. In the meantime this should be stable. However some older servers may time out or run out of memory.
		<br />
		If the server times out or runs out of memory, it is safe to reload the page, the script keeps track of what it has updated, and will continue from where it stopped.
		<br />
		
		- click here to run the first part of the update (updating categories) <br />
		<?php
		wpsc_convert_category_groups();
		?>
		- click here to run the next part of the update (updating variation groups and variations) <br />
		<?php
		wpsc_convert_variation_sets();
		?>
		- click here to run the next part of the update (updating products and product images) <br />
		<pre><?php
		wpsc_convert_products_to_posts();
		?></pre>
		- click here to run the next part of the update (updating variation combinations into child products) <br />
		<pre><?php 
		- wpsc_convert_variation_combinations();
		?></pre>
		- click here to run the next part of the update (updating product files) <br />
		<pre><?php
		wpsc_update_files();
		?></pre>
	</div>
		<?php 
}

?>