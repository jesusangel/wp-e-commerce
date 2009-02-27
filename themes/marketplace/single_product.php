<?php
global $wpsc_query, $wpdb;

$image_width = get_option('single_view_image_width');
$image_height = get_option('single_view_image_height');


?>
<div id='products_page_container' class="wrap wpsc_container">
	
	
	<?php if(wpsc_has_breadcrumbs()) :?>
		<div class='breadcrumb'>
			<a href='<?php echo get_option('siteurl'); ?>'><?php echo get_option('blogname'); ?></a> &raquo;
			<?php while ($wpsc_query->have_breadcrumbs()) : $wpsc_query->the_breadcrumb(); ?>
				<?php if(wpsc_breadcrumb_url()) :?> 	   
					<a href='<?php echo wpsc_breadcrumb_url(); ?>'><?php echo wpsc_breadcrumb_name(); ?></a> &raquo;
				<?php else: ?> 
					<?php echo wpsc_breadcrumb_name(); ?>
				<?php endif; ?> 
			<?php endwhile; ?>
		</div>
	<?php endif; ?>
	
	
	<div class="productdisplay">
			<?php
			while ($wpsc_query->have_products()) {
				$wpsc_query->the_product();
			?>
			<div class="single_product_display product_view_<?php echo wpsc_the_product_id(); ?>">
				<div class="textcol">
					<div class="imagecol">
						<a rel="<?php echo wpsc_the_product_title(); ?>" class="thickbox preview_link" href="<?php echo wpsc_the_product_image(); ?>">
							<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_image($image_width, $image_height); ?>"/>
						</a>
					</div>
		
					<form onsubmit="submitform(this);return false;" action="http://www.instinct.co.nz/wordpress_2.6/products-page/?category=" method="post" name="1" id="product_<?php echo wpsc_the_product_id(); ?>">
		
					<div class="producttext">
						<h2 class="prodtitles"><?php echo wpsc_the_product_title(); ?></h2>
					<?php
						do_action('wpsc_product_addons', wpsc_the_product_id());
						
						if((wpsc_product_has_file() == true)  && (function_exists('listen_button'))){
							$file_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$wpsc_query->product['file']."' LIMIT 1",ARRAY_A);
							if($file_data != null) {
								echo listen_button($file_data['idhash'], $file_data['id']);
							}
						}
				?>
						
						
						<p class="description"><?php echo wpsc_the_product_description(); ?></p>
		
						<?php if(wpsc_the_product_additional_description()) : ?>
						<p class="single_additional_description">
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
						</p>
					<?php endif; ?>
				
						<div class="custom_meta">
						<?php
							while ($wpsc_query->have_custom_meta()) {
								$wpsc_query->the_custom_meta();
								?>
								<strong><?php echo wpsc_custom_meta_name(); ?>: </strong><?php echo wpsc_custom_meta_name(); ?><br />
								<?php
							}
							?>
						</div>
			
						<p class="wpsc_variation_forms">
							<?php
							//$wpsc_query->get_variation_groups();
							while ($wpsc_query->have_variation_groups()) {
								$wpsc_query->the_variation_group();
							?>
							<p>
								<label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label>
								<select class='wpsc_select_variation' name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">
								<?php
								while ($wpsc_query->have_variations()) {
									$wpsc_query->the_variation();
									?>
									<option value="<?php echo wpsc_the_variation_id(); ?>"><?php echo wpsc_the_variation_name(); ?></option>
									<?php
								}
								?>
								</select> 
							</p>
							<?php
							}
							?>
						</p>        
									
						<p class="wpsc_product_price">
							<?php if(wpsc_product_is_donation()) { ?>
								<label for='donation_price_<?php echo wpsc_the_product_id(); ?>'><?php echo TXT_WPSC_DONATION; ?></label><br />
								<input type='text' id='donation_price_<?php echo wpsc_the_product_id(); ?>' name='donation_price' value='<?php echo $wpsc_query->product['price']; ?>' size='6' /><br />
							
							
							<?php } else { ?>
								<?php if(wpsc_product_on_special()) : ?>
									<span class='oldprice'><?php echo TXT_WPSC_PRICE; ?>: <?php echo wpsc_product_normal_price(); ?></span><br />
								<?php endif; ?>
								<?php echo TXT_WPSC_PRICE; ?>:  <span id="product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay"><?php echo wpsc_the_product_price(); ?></span><br/>
								<?php if(get_option('display_pnp') == 1) : ?>
									<?php echo TXT_WPSC_PNP; ?>:  <span class="pricedisplay"><?php echo wpsc_product_postage_and_packaging(); ?></span><br />
								<?php endif; ?>							
							<?php } ?>
						</p>
					<?php if(function_exists('wpsc_akst_share_link') && (get_option('wpsc_share_this') == 1)) {
						echo wpsc_akst_share_link('return');
					} ?>
						
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="prodid"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="item"/>
						
						
						
					<?php if(wpsc_product_has_stock()) : ?>
						<input type='image' src='<?php echo WPSC_URL; ?>/themes/marketplace/images/atc.gif' id='product_<?php echo wpsc_the_product_id(); ?>_submit_button' class='wpsc_buy_button' name='Buy' value='<?php echo TXT_WPSC_ADDTOCART; ?>'  />
					<?php else : ?>
						<p class='soldout'><?php echo TXT_WPSC_PRODUCTSOLDOUT; ?></p>
					<?php endif ; ?>
					
					<?php echo wpsc_product_rater(); ?>
						
						
					<?php
						if(function_exists('gold_shpcrt_display_gallery')) :
						?>
						<?php
							gold_shpcrt_display_gallery(wpsc_the_product_id());
						endif;
					?>
					</div>
					</form>
		
					<form onsubmit="submitform(this);return false;" action="http://www.instinct.co.nz/wordpress_2.6/products-page/?category=" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_extra_<?php echo wpsc_the_product_id(); ?>">
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="prodid"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="item"/>
					</form>
				</div>
			</div>
		</div>
		<?php
		}
		if(function_exists('fancy_notifications')) {
			echo fancy_notifications();
		}
		?>
		<div class="clear"/>
	</div>
</div>


