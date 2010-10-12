<?php

require_once( dirname( dirname( dirname(__FILE__) ) ) . '/wpsc-config.php');
$categorylist = get_terms('wpsc_product_category',array('hide_empty'=> 0));

//Check capabilities
if ( !current_user_can('edit_pages') && !current_user_can('edit_posts') ) 
	wp_die(__("You don't have permission to be doing that!"));

global $wpdb; 
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>WP e-Commerce</title>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/jquery.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/mctabs.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/form_utils.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo WPSC_URL; ?>/wpsc-core/js/tinymce3/tinymce.js"></script>
	
		<base target="_self" />
		<style type='text/css'>
			div.current{
				overflow-y: auto !important;
			}
		</style>
	</head>
	<body id="link" onload="tinyMCEPopup.executeOnLoad('init();'); document.body.style.display=''; document.getElementById('category').focus();" style="display:none;">
		<form name="WPSC" action="#">
			<div class="tabs">
				<ul>
					<li id="category" class="current"><span><a href="javascript:mcTabs.displayTab('category','wpsc_category_panel');" onmousedown="return false;"><?php _e("Category", 'wpsc'); ?></a></span></li>
					<li id="add_product"><span><a href="javascript:mcTabs.displayTab('add_product','add_product_panel');" onmousedown="return false;"><?php _e("Products", 'wpsc'); ?></a></span></li>
					<li id="product_slider"><span><a href="javascript:mcTabs.displayTab('product_slider','product_slider_panel');" onmousedown="return false;"><?php _e("Premium Upgrades", 'wpsc'); ?></a></span></li>
				</ul>
			</div>
			
<div class="panel_wrapper">
	<div id="wpsc_category_panel" class="panel current">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
			<tr valign="top">
				<td><strong><label for="wpsc_category"><?php _e("Select Category: ", 'wpsc'); ?></label></strong></td>
				
				<td><select id="wpsc_category" name="wpsc_category" style="width: 150px">
						<option value="0"><?php _e("No Category", 'wpsc'); ?></option>
				
						<?php 						
							foreach($categorylist as $category) 
							echo "<option value=".$category->term_id." >".$category->name."</option>"."\n";
						?>
				</select><br />
				<em><span class="description"><?php _e('Select the category you would like to display with a Shortcode.') ?></em></span>
				</td>
			</tr>
			
			<tr valign="top">
				<td><strong><label for="wpsc_perpage"><?php _e("Number of products per Page: ", 'wpsc'); ?></label></strong></td>
				<td><input name="number_per_page" id="wpsc_perpage" type="text" value="6" style="width: 80px" /><br />
				<span class="description"><em><?php _e('Select the number of products you would like to display per page.') ?></em></span>
				</td>
			</tr>
		</table>
	</div>
	
<!-- Premium upgrades, check is upgrade exists if so display short code. -->
<div id="product_slider_panel" class="panel">
	<br />
	<table border="0" cellpadding="4" cellspacing="0">
		<tr valign="top"><strong><?php _e("Product Slider", 'wpsc'); ?></strong></tr>
			<?php if (function_exists('product_slider_preload')){?>
				<td><strong><label for="wpsc_category"><?php _e("Select Category", 'wpsc'); ?></label></strong></td>
					<td><select id="wpsc_slider_category" name="wpsc_category" style="width: 200px">
						<option value="0"><?php _e("No Category", 'wpsc'); ?></option>	
						<?php
			
							foreach($categorylist as $category) 
								echo "<option value=".$category->term_id." >".$category->name."</option>"."\n";
			
						 ?>
					</select><br />
					<em><span class="description"><?php _e('Select the category you would like to display with a Shortcode.') ?></em></span>
					</td>
					<tr valign="top">
					<td><strong><label for="wpsc_perpage"><?php _e("Number of Products", 'wpsc_category'); ?>:</label></strong></td>
			
		</td>
		<td>
			<input type='text' id='wpsc_slider_visibles' name='wpsc_slider_visibles'> <br />
			<em><span class="description"><?php _e('Number of Products to be displayed in the slider.') ?></em></span>
		</td>
		</tr>

			<?php }else{ ?>
					<td><?php _e('You don\'t have the product slider installed, for a cool way to display your shop check out the <a href="http://getshopped.org/extend/premium-upgrades/premium-upgrades/product-slider-2010/" target="_blank">Product Slider</a>','wpsc'); ?>
</td>
				<?php	}?>	
	</table>
	<strong><?php _e("Members and Capabilities", 'wpsc'); ?></strong>
	<?php if (function_exists('wpsc_display_purchasable_capabilities')){ ?>
To create a preview on your restricted page put the following short code at the top of your page:
	<code>[preview] Preview In Here [/preview]</code>
	
	<?php }else{ ?>
		<p> You don't have the Members and Capabilities plugin installed, to start managing your users and creating subscription for you site visit: <a href="http://getshopped.org/extend/premium-upgrades/premium-upgrades/product-slider-2010/" target="_blank">Premium Upgrades</a> </p>
	<?php }?>
</div>


				<div id="add_product_panel" class="panel">
					<br />
					<table border="0" cellpadding="4" cellspacing="0">
			<tr valign="top">
				<td><strong><label for="add_product_name"><?php _e("Name", 'wpsc'); ?></label></strong></td>
				<td><input type="text" id="add_product_name" name="add_product_name" style="width: 200px"><br />
				<span class="description"><em><?php _e('The name of the product') ?></em></span>
				</td>
			</tr>
			<tr valign="top">
				<td><strong><label for="add_product_description"><?php _e("Description", 'wpsc'); ?></label></strong></td>
				<td><input type="text" id="add_product_description" name="add_product_description" style="width: 200px"><br />
				<span class="description"><em><?php _e('Product Description') ?></em></span>
				</td>
			</tr>
			<tr valign="top">
				<td><strong><label for="add_product_description"><?php _e("Price", 'wpsc'); ?></label></strong></td>
				<td><input type="text" id="add_product_price" name="add_product_price" style="width: 200px"><br />
				</td>
			</tr>
				<tr valign="top">
				<td><strong><label for="add_product_category"><?php _e("Category", 'wpsc'); ?></label></label></strong></td>
				<td>				
					<select id="add_product_category" name="add_product_category" style="width: 200px">
						<option value="0"><?php _e("No Category", 'wpsc'); ?></option>
						<?php
						foreach($categorylist as $category) 
							echo "<option value=".$category->term_id." >".$category->name."</option>"."\n";
						?>
					</select>
				</td>
			</tr><?php
			$selected_gateways = get_option( 'custom_gateway_options' );
			if (in_array( 'wpsc_merchant_paypal_standard', (array)$selected_gateways )) {?>
				<tr valign="top">
				<td><strong><label for="add_product_description"><?php _e("Buy now button", 'wpsc'); ?></label></strong></td>
				<td><code>short code needed in here</code><br />
				[buy_now_button product_id=12]
				<span class="description"><em><?php _e('Please note that this will only work with PayPal Standard 2.0') ?></em></span>
				</td>
			</tr>
			<?php }
				
			?>
			</table>
				</div>
				
			</div>
			<div class="mceActionPanel">
				<div style="float: left">
					<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'wpsc'); ?>" onclick="tinyMCEPopup.close();" />
				</div>
				<div style="float: right">
					<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'wpsc'); ?>" onclick="insertWPSCLink();" />
				</div>
			</div>
		</form>
	</body>
</html>