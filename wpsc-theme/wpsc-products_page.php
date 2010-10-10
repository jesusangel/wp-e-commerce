<?php
/*
 * Most functions called in this page can be found in the wpsc_query.php file
 */
global $wpsc_query, $wp_query; ?>

<div id="default_products_page_container">

	<?php wpsc_output_breadcrumbs(); ?>
	
	<?php
	
	// Plugin hook for adding things to the top of the products page, like the live search
	do_action('wpsc_top_of_products_page');

	?>

	<?php if ( wpsc_display_categories() ) : ?>

		<?php if ( 1 == get_option( 'wpsc_category_grid_view' ) ) : ?>

			<div class="wpsc_categories wpsc_category_grid group">

				<?php wpsc_start_category_query( array( 'category_group' => get_option( 'wpsc_default_category' ), 'show_thumbnails' => 1 ) ); ?>

					<a href="<?php wpsc_print_category_url(); ?>" class="wpsc_category_grid_item" title="<?php wpsc_print_category_name(); ?>">

						<?php wpsc_print_category_image( 45, 45 ); ?>

					</a>

					<?php wpsc_print_subcategory( '', '' ); ?>

				<?php wpsc_end_category_query(); ?>

			</div><!--close wpsc_categories-->

		<?php else : ?>

			<ul class="wpsc_categories">

				<?php wpsc_start_category_query( array( 'category_group' => get_option( 'wpsc_default_category' ), 'show_thumbnails'=> get_option( 'show_category_thumbnails' ) ) ); ?>

					<li>
						<?php wpsc_print_category_image( 32, 32 ); ?>

						<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link" title="<?php wpsc_print_category_name(); ?>"><?php wpsc_print_category_name(); ?></a>

						<?php if ( get_option( 'wpsc_category_description' ) ) : ?>

							<?php wpsc_print_category_description( '<div class="wpsc_subcategory">', '</div>' ); ?>

						<?php endif;?>

						<?php wpsc_print_subcategory( '<ul>', '</ul>' ); ?>

					</li>

				<?php wpsc_end_category_query(); ?>

			</ul>

		<?php endif; ?>

	<?php endif; ?>
	
	<?php if ( wpsc_display_products() ) : ?>

		<?php if ( wpsc_is_in_category() ) : ?>

			<div class="wpsc_category_details">

				<?php if ( get_option( 'show_category_thumbnails' ) && wpsc_category_image() ) : ?>

					<img src="<?php echo wpsc_category_image(); ?>" alt="<?php echo wpsc_category_name(); ?>" />

				<?php endif; ?>
				
				<?php if ( get_option( 'wpsc_category_description' ) && wpsc_category_description() ) : ?>

					<?php echo wpsc_category_description(); ?>

				<?php endif; ?>

			</div><!--close wpsc_category_details-->

		<?php endif; ?>

		<?php if ( wpsc_has_pages_top() ) : ?>

			<div class="wpsc_page_numbers_top">

				<?php wpsc_pagination(); ?>

			</div><!--close wpsc_page_numbers_top-->

		<?php endif; ?>

		<?php while ( wpsc_have_products() ) :  wpsc_the_product(); ?>

			<?php if ( wpsc_category_transition() ) : ?>

				<h3 class="wpsc_category_name"><?php echo wpsc_current_category_name(); ?></h3>

			<?php endif; ?>			

			<div class="default_product_display product_view_<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?> group">

					<?php if ( get_option( 'show_thumbnails' ) ) : ?>

						<div class="imagecol">

							<?php if ( wpsc_the_product_thumbnail() ) : ?>

								<a rel="<?php echo str_replace(array(" ", '"',"'", '&quot;','&#039;'), array("_", "", "", "",''), wpsc_the_product_title()); ?>" class="<?php echo wpsc_the_product_image_link_classes(); ?>" href="<?php echo wpsc_the_product_image(); ?>">
									<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/>
								</a>

							<?php else: ?>

								<a href="<?php echo wpsc_the_product_permalink(); ?>">
									<img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>" width="<?php echo get_option('product_image_width'); ?>" height="<?php echo get_option('product_image_height'); ?>" />
								</a>

							<?php endif; ?>

						</div><!--close imagecol-->

					<?php endif; ?>
				
					<div class="productcol">
						<h2 class="prodtitle">
							<?php if ( 1 == get_option( 'hide_name_link' ) ) : ?>

								<?php echo wpsc_the_product_title(); ?>

							<?php else: ?>

								<a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>

							<?php endif; ?>

							<?php echo wpsc_edit_the_product_link(); ?>

						</h2>
						
						<?php
							// Actions for external component usage
							do_action( 'wpsc_product_before_description', wpsc_the_product_id(), $wp_query->post );
							do_action( 'wpsc_product_addons', wpsc_the_product_id() );
						?>

						<div class="wpsc_description">

							<?php echo wpsc_the_product_description(); ?>

                        </div><!--close wpsc_description-->
				
						<?php if ( wpsc_the_product_additional_description() ) : ?>

							<div class="additional_description_container">
							
								<img class="additional_description_button" src="<?php echo WPSC_CORE_IMAGES_URL; ?>/icon_window_expand.gif" alt="Additional Description" />
								<a href="<?php echo wpsc_the_product_permalink(); ?>" class="additional_description_link"><?php _e('More Details', 'wpsc'); ?></a>

								<div class="additional_description">
									<p><?php echo wpsc_the_product_additional_description(); ?></p>
								</div><!--close additional_description-->
							</div><!--close additional_description_container-->

						<?php endif; ?>
						
						<?php
							if ( wpsc_product_external_link( wpsc_the_product_id() ) != '' )
								$action =  wpsc_product_external_link(wpsc_the_product_id() );
							else
								$action = htmlentities(wpsc_this_page_url(),ENT_QUOTES);

							if ( function_exists( 'gold_shpcrt_display_gallery' ) )
								echo gold_shpcrt_display_gallery(wpsc_the_product_id(), true );

						?>

						<form class="product_form"  enctype="multipart/form-data" action="<?php echo $action; ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >

							<?php if ( wpsc_have_variation_groups() ) : ?>

								<div class="wpsc_variation_forms">
									<table>

										<?php while ( wpsc_have_variation_groups() ) : wpsc_the_variation_group(); ?>

											<tr>
												<td class="col1"><label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label></td>
												<td class="col2">
													<select class="wpsc_select_variation" name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">

														<?php while ( wpsc_have_variations() ) : wpsc_the_variation(); ?>

															<option value="<?php echo wpsc_the_variation_id(); ?>" <?php echo wpsc_the_variation_out_of_stock(); ?>><?php echo wpsc_the_variation_name(); ?></option>

														<?php endwhile; ?>

													</select>
												</td>
											</tr>

										<?php endwhile; ?>

									</table>
								</div><!--close wpsc_variation_forms-->
						<?php endif; ?>

							<?php if ( wpsc_has_multi_adding() ): ?>

								<div class="wpsc_quantity_update">
									<label for="wpsc_quantity_update_<?php echo wpsc_the_product_id(); ?>"><?php _e('Quantity', 'wpsc'); ?>:</label>
									<input type="text" id="wpsc_quantity_update_<?php echo wpsc_the_product_id(); ?>" name="wpsc_quantity_update" size="2" value="1" />
									<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
									<input type="hidden" name="wpsc_update_quantity" value="true" />
                                </div><!--close wpsc_quantity_update-->

							<?php endif ;?>

							<div class="wpsc_product_price">

								<?php if ( wpsc_product_is_donation() ) : ?>

									<label for="donation_price_<?php echo wpsc_the_product_id(); ?>"><?php _e('Donation', 'wpsc'); ?>:</label>
									<input type="text" id="donation_price_<?php echo wpsc_the_product_id(); ?>" name="donation_price" value="<?php echo $wpsc_query->product['price']; ?>" size="6" />

								<?php else : ?>

									<?php if ( wpsc_product_on_special() ) : ?>

										<p class="pricedisplay <?php echo wpsc_the_product_id(); ?>"><?php _e('Price', 'wpsc'); ?>:<span class="oldprice"><?php echo wpsc_product_normal_price(); ?></span></p>

									<?php endif; ?>

									<p class="pricedisplay <?php echo wpsc_the_product_id(); ?>"><?php _e('Price', 'wpsc'); ?>:<span class="currentprice"><?php echo wpsc_the_product_price(); ?></span></p>

									<?php if ( 1 == get_option( 'display_pnp' ) ) : ?>

										<p class="pricedisplay"><?php _e('P&amp;P', 'wpsc'); ?>:<span class="pp_price"><?php echo wpsc_product_postage_and_packaging(); ?></span></p>

									<?php endif; ?>

								<?php endif; ?>

							</div><!--close wpsc_product_price-->
							
							<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
							<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
					
							<?php if ( ( 0 == get_option( 'hide_addtocart_button' ) ) && ( '1' != get_option( 'addtocart_or_buynow' ) ) ) : ?>

								<?php if ( wpsc_product_has_stock() ) : ?>

									<div class="wpsc_buy_button_container">
										<?php if ( '' != wpsc_product_external_link( wpsc_the_product_id() ) ) :

												$action =  wpsc_product_external_link( wpsc_the_product_id() ); ?>

												<input class="wpsc_buy_button" type="submit" value="<?php _e('Buy Now', 'wpsc'); ?>" onclick="gotoexternallink("<?php echo $action; ?>")">

										<?php else: ?>

											<input type="submit" value="<?php _e('Add To Cart', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button"/>

										<?php endif; ?>

										<div class="wpsc_loading_animation">
											<img title="Loading" alt="Loading" src="<?php echo WPSC_CORE_IMAGES_URL; ?>/indicator.gif" />

											<?php _e('Updating cart...', 'wpsc'); ?>

										</div><!--close wpsc_loading_animation-->
									</div><!--close wpsc_buy_button_container-->

								<?php else : ?>

									<p class="soldout"><?php _e('This product has sold out.', 'wpsc'); ?></p>

								<?php endif ; ?>

							<?php endif ; ?>

						</form><!--close product_form-->
						
						<?php if ( ( 0 == get_option( 'hide_addtocart_button' ) ) && ( '1' == get_option( 'addtocart_or_buynow' ) ) ) : ?>

							<?php echo wpsc_buy_now_button(wpsc_the_product_id()); ?>

						<?php endif ; ?>
						
						<?php echo wpsc_product_rater(); ?>						

					</div><!--close productcol-->
				</div><!--close default_product_display-->

		<?php endwhile; ?>

		<?php if ( !wpsc_product_count() ) : ?>

			<h3><?php  _e('There are no products in this group.', 'wpsc'); ?></h3>

		<?php endif ; ?>

		<?php if ( wpsc_has_pages_bottom() ) : ?>

			<div class="wpsc_page_numbers_bottom">
				<?php wpsc_pagination(); ?>
			</div><!--close wpsc_page_numbers_bottom-->

		<?php endif; ?>

	<?php endif; ?>

	<?php do_action( 'wpsc_theme_footer' ); ?>

</div><!--close default_products_page_container-->
