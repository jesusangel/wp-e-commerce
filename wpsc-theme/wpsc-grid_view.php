
<div id="grid_view_products_page_container">

	<?php wpsc_output_breadcrumbs(); ?>

	<?php

	// Plugin hook for adding things to the top of the products page, like the live search
	do_action( 'wpsc_top_of_products_page' );

	?>

	<?php if ( wpsc_display_categories() ) : ?>

		<?php if ( 1 == get_option( 'wpsc_category_grid_view' ) ) : ?>

			<div class="wpsc_categories wpsc_category_grid group">

				<?php wpsc_start_category_query( array( 'category_group' => 1, 'show_thumbnails' => 1 ) ); ?>

					<a href="<?php wpsc_print_category_url(); ?>" class="wpsc_category_grid_item" title="<?php wpsc_print_category_name(); ?>">

						<?php wpsc_print_category_image( 45, 45 ); ?>

					</a>

					<?php wpsc_print_subcategory( '', '' ); ?>

				<?php wpsc_end_category_query(); ?>

			</div>

		<?php else : ?>

			<ul class="wpsc_categories">

				<?php wpsc_start_category_query( array( 'category_group' => 1, 'show_thumbnails' => get_option( 'show_category_thumbnails' ) ) ); ?>

					<li>
						<?php wpsc_print_category_image( 32, 32 ); ?>

						<a href="<?php wpsc_print_category_url(); ?>" class="wpsc_category_link"><?php wpsc_print_category_name(); ?></a>

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

					<img src="<?php echo wpsc_category_image(); ?>" alt="<?php echo wpsc_category_name(); ?>" title="<?php echo wpsc_category_name(); ?>" />

				<?php endif; ?>

				<?php if ( get_option( 'wpsc_category_description' ) &&  wpsc_category_description() ) : ?>

					<?php echo wpsc_category_description(); ?>

				<?php endif; ?>

			</div>

		<?php endif; ?>


		<?php if ( wpsc_has_pages_top() ) : ?>

			<div class="wpsc_page_numbers_top">

				<?php wpsc_pagination(); ?>

			</div>

		<?php endif; ?>

		<div class="product_grid_display">

			<?php while ( wpsc_have_products() ) :  wpsc_the_product(); ?>

				<div class="product_grid_item product_view_<?php echo wpsc_the_product_id(); ?>">

					<?php if ( wpsc_the_product_thumbnail() ) : ?>

						<div class="item_image">
							<a href="<?php echo wpsc_the_product_permalink(); ?>">
								<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>" />
							</a>
						</div>

					<?php else : ?>

						<div class="item_no_image">
							<a href="<?php echo wpsc_the_product_permalink(); ?>">
								<img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>" width="<?php echo get_option('product_image_width'); ?>" height="<?php echo get_option('product_image_height'); ?>" />
							</a>
						</div>

					<?php endif; ?>


					<?php if ( 1 != get_option( 'show_images_only' ) ) : ?>

						<div class="grid_product_info">
							<h2 class="prodtitle"><?php echo wpsc_the_product_title(); ?></h2>

							<?php if ( ( wpsc_the_product_description() != '' ) && ( 1 == get_option( 'display_description' ) ) ) : ?>

								<div class="grid_description"><?php echo wpsc_the_product_description(); ?></div>

							<?php endif; ?>

							<div class="price_container">

								<?php if ( wpsc_product_on_special() ) : ?>

									<p class="pricedisplay <?php echo wpsc_the_product_id(); ?>"><?php _e( 'Price', 'wpsc' ); ?>: <span class="oldprice"><?php echo wpsc_product_normal_price(); ?></span></p>

								<?php endif; ?>

								<p class="pricedisplay <?php echo wpsc_the_product_id(); ?>"><?php _e( 'Price', 'wpsc' ); ?>: <span class="currentprice"><?php echo wpsc_the_product_price(); ?></span></p>

								<?php if ( 1 == get_option( 'display_pnp' ) ) : ?>

									<p class="pricedisplay"><?php _e( 'P&amp;P', 'wpsc' ); ?>:<span class="pp_price"><?php echo wpsc_product_postage_and_packaging(); ?></span></p>

								<?php endif; ?>

							</div>

							<?php if ( 1 == get_option( 'display_moredetails' ) ) : ?>

								<a href="<?php echo wpsc_the_product_permalink(); ?>" class="more_details"><?php _e( 'More Details', 'wpsc' ); ?></a>

							<?php endif; ?>

						</div>

						<div class="grid_more_info">
							<form class="product_form"  enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >
								<input type="hidden" value="add_to_cart" name="wpsc_ajax_action" />
								<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id" />

								<?php if ( 1 == get_option( 'display_variations' ) ) : ?>

									<div class="wpsc_variation_forms">
										<table>

											<?php while ( wpsc_have_variation_groups() ) : wpsc_the_variation_group(); ?>

												<tr>
													<td class="col1">
														<label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label>
													</td>

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
									</div>

								<?php endif; ?>

								<?php if ( ( 1 == get_option( 'display_addtocart' ) ) && ( '1' != get_option( 'addtocart_or_buynow' ) ) ) : ?>

									<?php if ( wpsc_product_has_stock() ) : ?>

										<input type="submit" value="<?php _e( 'Add To Cart', 'wpsc' ); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button" />

									<?php else : ?>

										<p class="soldout"><?php _e( 'This product has sold out.', 'wpsc' ); ?></p>

									<?php endif ; ?>

								<?php endif; ?>

								<div class="wpsc_loading_animation">
									<img title="Loading" alt="Loading" src="<?php echo WPSC_CORE_IMAGES_URL; ?>/indicator.gif" />

									<?php _e( 'Updating cart...', 'wpsc' ); ?>

								</div>
							</form>
						</div>

						<?php if ( ( 1 == get_option( 'display_addtocart' ) ) && ( '1' == get_option( 'addtocart_or_buynow' ) ) ) : ?>

							<?php echo wpsc_buy_now_button( wpsc_the_product_id() ); ?>

						<?php endif; ?>

					<?php endif; ?>

				</div>

				<?php if ( ( get_option( 'grid_number_per_row' ) > 0 ) && ( ( ( $wp_query->current_post +1 ) % get_option( 'grid_number_per_row' ) ) == 0 ) ) : ?>

					<div class="grid_view_clearboth"></div>

				<?php endif ; ?>

			<?php endwhile; ?>

			<?php if ( !wpsc_product_count() ) : ?>

				<p><?php  _e( 'There are no products in this group.', 'wpsc' ); ?></p>

			<?php endif ; ?>

		</div>

		<?php if ( wpsc_has_pages_bottom() ) : ?>

			<div class="wpsc_page_numbers_bottom">

				<?php wpsc_pagination(); ?>

			</div>

		<?php endif; ?>
	<?php endif; ?>

	<?php do_action( 'wpsc_theme_footer' ); ?>

</div>
