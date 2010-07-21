<?php
/**
 * WP eCommerce database updating page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.8
 */

global $show_update_page;
global $wpdb;
$show_update_page = FALSE;
if (count(get_option("wpsc-variation_children")) == 0) : //if there's nothing in the children variation cache, refresh it, just to make sure.
	delete_option("wpsc-variation_children");
	_get_term_hierarchy('wpsc-variation');
endif;
if(get_option('wpsc_version') < 3.8 || !get_option('wpsc_version')) :
	/////////////////////////////////////////////////////////////////////
	// Check to see if there are any products... if they don't have any, they don't need to update
	/////////////////////////////////////////////////////////////////////

	$product_count = $wpdb->get_var("SELECT COUNT(*) FROM " . WPSC_TABLE_PRODUCT_LIST);
	if($product_count > 0) :
		$show_update_page = TRUE;
		function wpsc_display_update_notice() {
			echo "<div id='wpsc-warning' class='error fade'><p><strong>".__('WP e-Commerce is almost ready.')."</strong> ".sprintf(__('You must <a href="%1$s">update your database</a> to import all of your products.'), "admin.php?page=wpsc-update")."</p></div>";
		}
		if($_GET['page'] != 'wpsc-update') :
			add_action('admin_notices', 'wpsc_display_update_notice');
		endif;
			
	else :
		//there weren't any products, so mark the update as complete
		update_option('wpsc_version', '3.8');
		
	endif; //product count > 0
endif; //get_option('wpsc_db_version') < 3.8 || !get_option('wpsc_db_version')



function wpsc_display_update_page() {
	?>
	<div class="wrap">
		<?php // screen_icon(); ?>
		<h2><?php echo esc_html( __('Update WP e-Commerce', 'wpsc') ); ?> </h2>
		<br />	
	<?php 
	if($_POST['run_updates']) :
		echo 'Updating Categories...';
		wpsc_convert_category_groups();
		echo '<br />Updating Variations...';
		wpsc_convert_variation_sets();
		echo '<br />Updating Products...';
		wpsc_convert_products_to_posts();
		echo '<br />Updating Child Products...';
		wpsc_convert_variation_combinations();
		echo '<br />Updating Product Files...';
		wpsc_update_files();
		echo '<br /><br /><strong>WP e-Commerce updated successfully!</strong>';
		update_option('wpsc_version', 3.8);
	else:
	?>

		Your WP e-Commerce database needs to be updated for WP e-Commerce 3.8.  To perform this update, press the button below.  It is highly recommended that you back up your database before performing this update.
		<br />
		<br />
		<em>Note: If the server times out or runs out of memory, just reload this page, the server will pick up where it left off.</em>
		<br />
		
		<form action="" method="post" id="setup">
			<input type="hidden" name="run_updates" value="true" id="run_updates">
			<p class="step"><input type="submit" class="button" value="Update WP e-Commerce" name="Submit"></p>
		</form>
	<?php
		endif;
	?>
	</div>

		<?php 
}

?>