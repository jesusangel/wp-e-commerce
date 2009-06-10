<?php
global $wpsc_query, $wpdb;
?>
<div class="productdisplay example-category">
	
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
	


	<div class="product_grid_display">
		<?php while (wpsc_have_products()) :  wpsc_the_product(); ?>
			<div style="width: 96px;" class="product_grid_item product_view_<?php echo wpsc_the_product_id(); ?>">
				<div class="item_image">
					<a href="<?php echo wpsc_the_product_permalink(); ?>"><img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/></a>
				</div>
				<div class="grid_product_info">
					<div class="product_text">
						<strong><?php echo wpsc_the_product_title(); ?></strong>
						<br/>
						Price: <span class="pricedisplay"><?php echo wpsc_product_normal_price(); ?></span>
					</div>
				</div>
				<div class="grid_more_info">
					<form class='product_form'  enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>" >
						<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
					</form>
				</div>
			</div>
		<?php endwhile; ?>
	</div>
</div>