  <ul class='wpsc_categories'>
		<?php wpsc_start_category_query(array('category_group'=> 1, 'show_thumbnails'=> get_option('show_category_thumbnails'))); ?>
				<li>
					<?php wpsc_print_category_image(32, 32); ?>
					
					<a href="<?php wpsc_print_category_url();?>" class="productlink"><?php wpsc_print_category_name();?></a>
					
					<?php if(get_option('wpsc_category_description')) :?>
						<?php wpsc_print_category_description("<div class='wpsc_subcategory'>", "</div>"); ?>				
					<?php endif;?>
					
					<?php wpsc_print_subcategory("<ul>", "</ul>"); ?>
				</li>
		<?php wpsc_end_category_query(); ?>
	</ul>