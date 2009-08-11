
<div class='wpsc_categorisation_group' id='categorisation_group_<?php echo $categorisation_group['id']; ?>'>
	<?php if(count($categorisation_groups) > 1) :  // no title unless multiple category groups ?>
	<h4 class='wpsc_category_title'><?php echo $categorisation_group['name']; ?></h4>
	<?php endif; ?>
	
	<ul class='wpsc_categories wpsc_top_level_categories <?php echo implode(" ", (array)$provided_classes); ?>'>
		<?php wpsc_start_category_query($category_settings); ?>
				<li class='wpsc_category_<?php wpsc_print_category_id();?>'>
					<a href="<?php wpsc_print_category_url();?>" class='wpsc_category_image_link'>
						<?php wpsc_print_category_image(45, 25); ?>
					</a>

					<a href="<?php wpsc_print_category_url();?>" class="wpsc_category_link"><?php wpsc_print_category_name();?></a>

					<?php/* if(get_option('wpsc_category_description')) :?>
						<?php wpsc_print_category_description("<div class='wpsc_subcategory'>", "</div>"); ?>
					<?php endif; */?>

					<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
				</li>
		<?php wpsc_end_category_query(); ?>
	</ul>
	<div class='clear_category_group'></div>
</div>