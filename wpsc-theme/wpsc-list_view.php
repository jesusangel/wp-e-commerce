<?php
global $wpsc_query, $wpdb;
?>
<div id="list_view_products_page_container">

<?php wpsc_output_breadcrumbs(); ?>
	
	<?php do_action('wpsc_top_of_products_page'); // Plugin hook for adding things to the top of the products page, like the live search ?>

	<?php if(wpsc_display_categories()): ?>
	  <?php if(wpsc_category_grid_view()) :?>
			<div class="wpsc_categories wpsc_category_grid group">
				<?php wpsc_start_category_query(array('category_group'=> 1, 'show_thumbnails'=> 1)); ?>
					<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_grid_item" title="<?php wpsc_print_category_name();?>">
						<?php wpsc_print_category_image(get_option('category_image_width'), get_option('category_image_height')); ?>
					</a>
					<?php wpsc_print_subcategory("", ""); ?>
				<?php wpsc_end_category_query(); ?>
			</div><!--close wpsc_categories-->
	  <?php else:?>
			<ul class="wpsc_categories">
				<?php wpsc_start_category_query(array('category_group'=> 1, 'show_thumbnails'=> get_option('show_category_thumbnails'))); ?>
						<li>
							<?php wpsc_print_category_image(get_option('category_image_width'), get_option('category_image_height')); ?>
							
							<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link"><?php wpsc_print_category_name();?></a>
							<?php if(get_option('wpsc_category_description')) :?>
								<?php wpsc_print_category_description("<div class='wpsc_subcategory'>", "</div>"); ?>				
							<?php endif;?>
							
							<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
						</li>
				<?php wpsc_end_category_query(); ?>
			</ul>
		<?php endif; ?>
	<?php endif; ?>



	
	<?php if(wpsc_display_products()): ?>
		<?php if(wpsc_is_in_category()) : ?>
			<div class="wpsc_category_details">
				<?php if(get_option('show_category_thumbnails') && wpsc_category_image()) : ?>
					<img src='<?php echo wpsc_category_image(); ?>' alt='<?php echo wpsc_category_name(); ?>' title='<?php echo wpsc_category_name(); ?>' />
				<?php endif; ?>
				
				<?php if(get_option('wpsc_category_description') &&  wpsc_category_description()) : ?>
					<?php echo wpsc_category_description(); ?>
				<?php endif; ?>
			</div><!--close wpsc_category_details-->
		<?php endif; ?>
		
		<?php if(wpsc_has_pages_top()) : ?>
			<div class="wpsc_page_numbers_top">
				<?php wpsc_pagination(); ?>
			</div><!--close wpsc_page_numbers_top-->
		<?php endif; ?>		
		<table class="list_productdisplay <?php echo wpsc_category_class(); ?>">
			<?php /** start the product loop here */?>
			<?php $alt = 0;	?>
			<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
				<?php
				$alt++;
				if ($alt %2 == 1) { $alt_class = 'alt'; } else { $alt_class = ''; }
				?>
				<tr class="product_view_<?php echo wpsc_the_product_id(); ?> <?php echo $alt_class;?>">
					<td width="40%">
						<h2 class="prodtitle"><a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a></h2>
					</td>
					<?php if(wpsc_show_stock_availability()): ?>	
						<td class="stock">
							<?php if(wpsc_product_has_stock()) : ?>
								<img src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/yes_stock.gif" alt="Yes" title="Yes" />
							<?php else: ?> 
								<img src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/no_stock.gif" title='No' alt="No" />
							<?php endif; ?> 
						</td>
					<?php endif; ?>
					
					<td>
 						<?php do_action('wpsc_product_before_description', wpsc_the_product_id(), $wpsc_query->product); ?>
					</td>
					<td>
                  			<?php if(wpsc_product_on_special()) : ?>
						<p class="oldprice"><?php echo wpsc_product_normal_price(); ?></p>
					<?php endif; ?>
						<p id="product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay currentprice"><?php echo wpsc_the_product_price(); ?></p>
					</td>
					
					<td>
						<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
							<?php	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
						<?php else: ?>
							<?php	$action =  wpsc_this_page_url(); ?>						
						<?php endif; ?>
						<form class='product_form'  enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>">
													
							<?php if(wpsc_has_multi_adding()): ?>
                            <div class="quantity_container">
								<label class="wpsc_quantity_update" for="wpsc_quantity_update_<?php echo wpsc_the_product_id(); ?>"><?php _e('Quantity', 'wpsc'); ?>:</label>
								<input type="text" id="wpsc_quantity_update_<?php echo wpsc_the_product_id(); ?>" name="wpsc_quantity_update" size="2" value="1" />
								<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
								<input type="hidden" name="wpsc_update_quantity" value="true" />
							</div><!--close quantity_container-->
							<?php endif ;?>		
							<input type="hidden" value="add_to_cart" name="wpsc_ajax_action" />
							<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id" />
	
							<?php if((get_option('hide_addtocart_button') == 0) &&  (get_option('addtocart_or_buynow') !='1')) : ?>
								<?php if(wpsc_product_has_stock()) : ?>
									<div class="wpsc_buy_button_container">
											<?php if(wpsc_product_external_link(wpsc_the_product_id()) != '') : ?>
											<?php 	$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>
											<input class="wpsc_buy_button" type="button" value="<?php _e( 'Buy Now', 'wpsc' ); ?>" onclick="gotoexternallink('<?php echo $action; ?>')">
											<?php else: ?>
										<input type="submit" value="<?php _e('Add To Cart', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button" />
											<?php endif; ?>
										<div class="wpsc_loading_animation">
											<img title="Loading" alt="Loading" src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/indicator.gif" />
											<?php _e('Updating cart...', 'wpsc'); ?>
										</div><!--close wpsc_loading_animation-->
									</div><!--close wpsc_buy_button_container-->
								<?php else : ?>
									<p class="soldout"><?php _e('This product has sold out.', 'wpsc'); ?></p>
								<?php endif ; ?>
							<?php endif ; ?>
							
						</form>
					</td>
				</tr>
			<?php endwhile; ?>
			<?php /** end the product loop here */?>
		</table>
		
		
		<?php if(wpsc_product_count() == 0):?>
			<p><?php  _e('There are no products in this group.', 'wpsc'); ?></p>
		<?php endif ; ?>
	
	    <?php do_action( 'wpsc_theme_footer' ); ?> 	

			<?php if(wpsc_has_pages_bottom()) : ?>
			<div class="wpsc_page_numbers_bottom">
				<?php wpsc_pagination(); ?>
			</div><!--close wpsc_page_numbers_bottom-->
		<?php endif; ?>
	<?php endif; ?>
</div><!--close list_view_products_page_container-->
