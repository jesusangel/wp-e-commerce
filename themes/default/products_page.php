<?php
global $wpsc_query, $wpdb;
?>

<div id='products_page_container' class="wrap wpsc_container">
	
	<?php do_action('wpsc_top_of_products_page'); // Plugin hook for adding things to the top of the products page, like the live search ?>
	<?php if(wpsc_has_breadcrumbs()) : ?>
		<div class='breadcrumb'>
			<a href='<?php echo get_option('siteurl'); ?>'><?php echo get_option('blogname'); ?></a> &raquo;
			<?php while (wpsc_have_breadcrumbs()) : wpsc_the_breadcrumb(); ?>
				<?php if(wpsc_breadcrumb_url()) :?> 	   
					<a href='<?php echo wpsc_breadcrumb_url(); ?>'><?php echo wpsc_breadcrumb_name(); ?></a> &raquo;
				<?php else: ?> 
					<?php echo wpsc_breadcrumb_name(); ?>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
	
	
	
	<?php if(wpsc_has_pages() && (get_option('wpsc_page_number_position') == (1 || 3)) ) : ?>
		<div class='wpsc_page_numbers'>
		  Pages: 
			<?php while (wpsc_have_pages()) : wpsc_the_page(); ?>
				<?php if(wpsc_page_is_selected()) :?> 	   
					<a href='<?php echo wpsc_page_url(); ?>' class='selected'><?php echo wpsc_page_number(); ?></a>
				<?php else: ?> 
					<a href='<?php echo wpsc_page_url(); ?>'><?php echo wpsc_page_number(); ?></a>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
	
<?php /** start the product loop here */?>
<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
	<div class="productdisplay default_product_display product_view_<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?>">      
		<div class="textcol">
			<div class="imagecol">
				<?php if(wpsc_the_product_thumbnail()) :?> 	   
					<a rel="<?php echo wpsc_the_product_title(); ?>" class="thickbox preview_link" href="<?php echo wpsc_the_product_image(); ?>">
						<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/>
					</a>
				<?php else: ?> 
					<div class="item_no_image">
						<a href="<?php echo wpsc_the_product_permalink(); ?>">
						<span>No Image Available</span>
						</a>
					</div>
				<?php endif; ?> 				
			</div>
				<div class="producttext">
					<h2 class="prodtitles">
						<a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
					</h2>
					<?php
						do_action('wpsc_product_addons', wpsc_the_product_id());
						
						if((wpsc_product_has_file() == true)  && (function_exists('listen_button'))){
							$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$wpsc_query->product['file']."' LIMIT 1",ARRAY_A);
							if($file_data != null) {
								echo listen_button($file_data['idhash'], $file_data['id']);
							}
						}
				?>
					
					
					<div class='wpsc_description'><?php echo wpsc_the_product_description(); ?></div>
					
					
					<?php if(wpsc_the_product_additional_description()) : ?>
					<div class='additional_description_span'>
						<a href='<?php echo wpsc_the_product_permalink(); ?>' class='additional_description_link'>
							<img class='additional_description_button'  src='<?php echo WPSC_URL; ?>/images/icon_window_expand.gif' title='Additional Description' alt='Additional Description' /><?php echo TXT_WPSC_MOREDETAILS; ?>
						</a>
						<span class='additional_description'><br />
							<?php
								$value = '';
								$the_addl_desc = wpsc_the_product_additional_description();
								if( is_serialized($the_addl_desc) ) {
									$addl_descriptions = @unserialize($the_addl_desc);
								} else {
									$addl_descriptions = array('addl_desc', $the_addl_desc);
								}
								
								if( isset($addl_descriptions['addl_desc']) ) {
									$value = $addl_descriptions['addl_desc'];
								}
							
								if( function_exists('wpsc_addl_desc_show') ) {
									echo wpsc_addl_desc_show( $addl_descriptions );
								} else {
									echo $value;
								}
							?>
						</span>
						<br />
					</div>
					<?php endif; ?>
					
					<form class='product_form'  enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >
						<?php do_action('wpsc_product_addon_after_descr', wpsc_the_product_id()); ?>
						
						<?php /** the custom meta HTML and loop */?>
						<div class="custom_meta">
							<?php while (wpsc_have_custom_meta()) : wpsc_the_custom_meta(); 	?>
								<strong><?php echo wpsc_custom_meta_name(); ?>: </strong><?php echo wpsc_custom_meta_name(); ?><br />
							<?php endwhile; ?>
						</div>
						<?php /** the custom meta HTML and loop ends here */?>
						
						<?php /** add the comment link here */?>
						<?php echo wpsc_product_comment_link();	?>
						
						
						<?php /** the variation group HTML and loop */?>
						<div class="wpsc_variation_forms">
							<?php while (wpsc_have_variation_groups()) : wpsc_the_variation_group(); ?>
								<p>
									<label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label>
									<?php /** the variation HTML and loop */?>
									<select class='wpsc_select_variation' name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">
									<?php while (wpsc_have_variations()) : wpsc_the_variation(); ?>
										<option value="<?php echo wpsc_the_variation_id(); ?>"><?php echo wpsc_the_variation_name(); ?></option>
									<?php endwhile; ?>
									</select> 
								</p>
							<?php endwhile; ?>
						</div>
						<?php /** the variation group HTML and loop ends here */?>
						
						<p class="wpsc_extras_forms"/>
						<p class="wpsc_product_price">
								<?php if(wpsc_product_is_donation()) : ?>
								<label for='donation_price_<?php echo wpsc_the_product_id(); ?>'><?php echo TXT_WPSC_DONATION; ?></label><br />
								<input type='text' id='product_price_<?php echo wpsc_the_product_id(); ?>' name='donation_price' value='' size='6' /><br />
								
								
								<?php else : ?>
									<?php if(wpsc_product_on_special()) : ?>
										<span class='oldprice'><?php echo TXT_WPSC_PRICE; ?>: <?php echo wpsc_product_normal_price(); ?></span><br />
									<?php endif; ?>
									<?php echo TXT_WPSC_PRICE; ?>:  <span id="product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay"><?php echo wpsc_the_product_price(); ?></span><br/>
									<?php if(get_option('display_pnp') == 1) : ?>
										<?php echo TXT_WPSC_PNP; ?>:  <span class="pricedisplay"><?php echo wpsc_product_postage_and_packaging(); ?></span><br />
									<?php endif; ?>							
								<?php endif; ?>
						</p>  
						<?php if(function_exists('wpsc_akst_share_link') && (get_option('wpsc_share_this') == 1)) {
							echo wpsc_akst_share_link('return');
						} ?>
						
						<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
				
						<!-- END OF QUANTITY OPTION -->
						<?php if(get_option('addtocart_or_buynow') !='1') : ?>
							<?php if(wpsc_product_has_stock()) : ?>
								<input type="submit" value="<?php echo TXT_WPSC_ADDTOCART; ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button"/>
							<?php else : ?>
								<p class='soldout'><?php echo TXT_WPSC_PRODUCTSOLDOUT; ?></p>
							<?php endif ; ?>
						<?php endif ; ?>
					</form>
					
					<?php if(get_option('addtocart_or_buynow')=='1') : ?>
						<?php echo wpsc_buy_now_button(wpsc_the_product_id()); ?>
					<?php endif ; ?>
					
					<?php echo wpsc_product_rater(); ?>
					
				</div>
		</div>
	</div>

	<?php endwhile; ?>
	<?php /** end the product loop here */?>
	
	
	<?php if(wpsc_product_count() < 1):?>
		<p><?php  echo TXT_WPSC_NOITEMSINTHISGROUP; ?></p>
	<?php endif ; ?>

<?php

if(function_exists('fancy_notifications')) {
  echo fancy_notifications();
}
?>

	<?php if(wpsc_has_pages() &&  ((get_option('wpsc_page_number_position') == 2) || (get_option('wpsc_page_number_position') == 3))) : ?>
		<div class='wpsc_page_numbers'>
		  Pages: 
			<?php while ($wpsc_query->have_pages()) : $wpsc_query->the_page(); ?>
				<?php if(wpsc_page_is_selected()) :?> 	   
					<a href='<?php echo wpsc_page_url(); ?>' class='selected'><?php echo wpsc_page_number(); ?></a>
				<?php else: ?> 
					<a href='<?php echo wpsc_page_url(); ?>'><?php echo wpsc_page_number(); ?></a>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
</div>