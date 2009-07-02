<?php
function wpsc_display_upgrades_page() {
	do_action('wpsc_gold_module_activation');
	?>
	<div class='wrap'>
		<div class='metabox-holder wpsc_gold_side'>
			<?php
			/* ADDITIONAL GOLD CART MODULES SECTION
			* ADDED 18-06-09
			*/
			?>
			<strong><?php _e('WP e-Commerce Upgrades'); ?></strong><br />
			<span><?php _e('Add more functionality to your e-Commerce site. Prices may be subject to change.'); ?><input type='button' class='button-primary' onclick='window.open ("http://www.instinct.co.nz/shop/","mywindow"); ' value='Buy Now' id='visitInstinct' name='visitInstinct' /></span>
			
			<br />
			<div class='wpsc_gold_module'>
				<br />
				<strong><?php _e('Pure Gold'); ?></strong>
				<p class='wpsc_gold_text'>Add product search, multiple image upload, gallery view, Grid View and multiple payment gateway options to your shop</p>
				<span class='wpsc_gold_info'>$25</span>
			</div>
			<div class='wpsc_gold_module'>
				<br />
				<strong><?php _e('DropShop'); ?></strong>
				<p class='wpsc_gold_text'>Impress your customers with our AJAX powered DropShop that lets your customers drag and drop products into their shopping cart</p>
				<span class='wpsc_gold_info'>$75</span>
			</div>
			<div class='wpsc_gold_module'>
				<br />
				<strong><?php _e('MP3 Player'); ?></strong>
				<p class='wpsc_gold_text'>Adding this module lets you upload and manage MP3 preview files that can be associated with your digital downloads.</p>
				<span class='wpsc_gold_info'>$10</span>
			</div>
			<div class='wpsc_gold_module'>
				<br />
				<strong><?php _e('Members Only Module'); ?> </strong>
				<p class='wpsc_gold_text'>The Members modules lets you set private pages and posts that are only available to paying customers. Activating this module also adds a new option under "WordPress Users" menu that shop owners can use to manage their subscribers.</p>
				<span class='wpsc_gold_info'>$25</span>
			</div>
			<div class='wpsc_gold_module'>
				<br />
				<strong><?php _e('Product Slider'); ?> </strong>
				<p class='wpsc_gold_text'>Display your products in a new and fancy way using the "Product Slider" module.</p>
				<span class='wpsc_gold_info'>$25</span>
			</div>
			<div class='wpsc_gold_module'>
				<br />
				<strong><?php _e('NextGen Gallery Buy Now Buttons'); ?> </strong>
				<p class='wpsc_gold_text'>Make your Online photo gallery into an e-Commerce solution.</p>
				<span class='wpsc_gold_info'>$10</span>
			</div>
		</div>

		<h2><?php echo TXT_WPSC_UPGRADES_PAGE;?></h2>
		<div class='wpsc_gold_float'>
			<div class='metabox-holder'>
				<form method='post' id='gold_cart_form' action=''>
				<?php
					if(defined('WPSC_GOLD_MODULE_PRESENT') && (constant('WPSC_GOLD_MODULE_PRESENT') == true)) {
						do_action('wpsc_gold_module_activation_forms');
					} else {
					  ?>
					  <div  class='form-wrap' >
							<p>
							Opps. You don't have any Upgrades yet!
							</p>
					  </div>
					  <?php
					}
				?>
				</form>
			</div> 
	</div>
</div>
<?php
}
?>
