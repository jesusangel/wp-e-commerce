<?php
	$curr_cat = get_term($category_id ,'wpsc_product_category',ARRAY_A );
	$category_list = get_terms('wpsc_product_category','hide_empty=0&parent='.$category_id);
	$link = '?wpsc_product_category='.$curr_cat['slug'];
	$link = home_url($link);
	$category_image = wpsc_get_categorymeta($curr_cat['term_id'], 'image');
	$category_image = WPSC_CATEGORY_URL.$category_image;
	if($grid){
	?>
	
			<a href="<?php echo $link;?>" style='padding:4px 4px 0 0;width:<?php echo $width; ?>px;height:<?php echo $height; ?>px;' title='<?php echo $curr_cat['name']; ?>' class='wpsc_category_grid_item'><?php wpsc_parent_category_image($show_thumbnails, $category_image , $width, $height, true); ?></a>
			<?php wpsc_start_category_query(array('parent_category_id'=>$category_id,'show_thumbnails'=>$show_thumbnails)); ?>
				<a href="<?php wpsc_print_category_url();?>" style='padding:4px 4px 0 0;width:<?php echo $width; ?>px;height:<?php echo $height; ?>px' class="wpsc_category_grid_item" title='<?php wpsc_print_category_name();?>'><?php wpsc_print_category_image($width, $height); ?></a>
										<?php wpsc_print_subcategory("", ""); ?>
			<?php wpsc_end_category_query(); ?>


	<?php
	}else{
	?>
		
		<div class='wpsc_categorisation_group' id='categorisation_group_<?php echo $category_id; ?>'>
		
			<ul class='wpsc_categories wpsc_top_level_categories <?php echo implode(" ", (array)$provided_classes); ?>'>
				<li class='wpsc_category_<?php echo $curr_cat['term_id'];?>'>
						<a href="<?php echo $link;?>" class='wpsc_category_image_link'>
					<?php wpsc_parent_category_image($show_thumbnails, $category_image , $width, $height); ?>
					<a href='<?php echo $link; ?>'><?php echo $curr_cat['name']; ?></a>
					<ul class='wpsc_categories wpsc_second_level_categories <?php echo implode(" ", (array)$provided_classes); ?>'>
					<?php
						 wpsc_start_category_query(array('parent_category_id'=>$category_id,'show_thumbnails'=>$show_thumbnails)); ?>
								<li class='wpsc_category_<?php wpsc_print_category_id();?>'>
									<a href="<?php wpsc_print_category_url();?>" class='wpsc_category_image_link'>
										<?php wpsc_print_category_image($width, $height); ?>
									</a>
			
									<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link">
										<?php wpsc_print_category_name();?>
										<?php if (get_option('show_category_count') == 1) : ?>
											<?php wpsc_print_category_products_count("(",")"); ?>
										<?php endif;?>
									</a>
									<?php wpsc_print_product_list(); ?>
									<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
								</li>
						<?php wpsc_end_category_query(); ?>
					</ul>
		
				</li>
			</ul>
			<div class='clear_category_group'></div>
		</div>
<?php } ?>
