<?php
/**
 * WP eCommerce edit and add product category page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */


/**
 * wpsc_display_categories_page, assembles the category page
 * @param nothing
 * @return nothing
 */

function wpsc_display_categories_page() {
	$columns = array(
		'img' =>  __('Image', 'wpsc'),
		'title' => __('Name', 'wpsc'),
		'edit' => __('Edit', 'wpsc')
	);
	register_column_headers('display-categories-list', $columns);	
	
	?>
	<script language='javascript' type='text/javascript'>
		function conf() {
			var check = confirm("<?php echo __('Are you sure you want to delete this category?', 'wpsc');?>");
			if(check) {
				return true;
			} else {
				return false;
			}
		}
		
		<?php

		?>
	</script><noscript>
	</noscript>
	
	<div class="wrap">
		<?php // screen_icon(); ?>
		<h2><?php echo wp_specialchars( __('Display categories', 'wpsc') ); ?> </h2>
		<p>
				<?php echo __('Categorizing your products into groups help your customers find them. '.
				'For instance if you sell hats and trousers you	might want to setup a Group called clothes and add hats and trousers to that group.', 'wpsc');?>
		</p>
  
		
		<?php if (isset($_GET['deleted']) || isset($_GET['message'])) { ?>
			<div id="message" class="updated fade">
				<p>
				<?php		
				if (isset($_GET['message']) ) {
					$message = absint( $_GET['message'] );
					$messages[1] =  __( 'Category updated.', 'wpsc');
					echo $messages[$message];
					unset($_GET['message']);
				}
				
				$_SERVER['REQUEST_URI'] = remove_query_arg( array('deleted', 'message'), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
		<?php } ?>
				
		<div id="col-container" class=''>
			<div id="col-right">			
				<div id='poststuff' class="col-wrap">
					<form id="modify-category-groups" method="post" action="" enctype="multipart/form-data" >
					<?php
						//$product_id = absint($_GET['product_id']);
						//wpsc_display_product_form($product_id);
						wpsc_admin_category_forms($_GET['category_id']);
					?>
					</form>
				</div>
			</div>
			
			<div id="col-left">
				<div class="col-wrap">		
					<?php
						wpsc_admin_category_group_list();
					?>
				</div>
			</div>
		</div>
				
				
	</div>
	<?php
}


/**
 * wpsc_admin_category_group_list, prints the left hand side of the edit categories page
 * @param nothing
 * @return nothing
 */


function wpsc_admin_category_group_list() {
  global $wpdb;
	?>
		<table class="widefat page" id='wpsc_category_list' cellspacing="0">
			<thead>
				<tr>
					<?php print_column_headers('display-categories-list'); ?>
				</tr>
			</thead>
		
			<tfoot>
				<tr>
					<?php print_column_headers('display-categories-list', false); ?>
				</tr>
			</tfoot>
		
			<tbody>
				<?php
					wpsc_list_categories('wpsc_admin_display_category_row', null, 0);
				?>			
			</tbody>
		</table>
		<?php
}

/**
 * wpsc_admin_display_category_row, recursively displays category rows according to their parent categories 
 * @param object - category data
 * @param integer - execution depth, default = 0
 * @return nothing
 */

function wpsc_admin_display_category_row($category,$subcategory_level = 0) {
	//echo "<pre>".print_r($category,true)."</pre>";
	$category_image = wpsc_get_categorymeta($category->term_id, 'image');
	?>
	<tr>
		<td colspan='3' class='colspan'>
			<?php if($subcategory_level > 0) {
				$css_modifier = (8*$subcategory_level); 
				$padding = $css_modifier;
				$width = 416 - $css_modifier;
				?>
				<div class='subcategory' style='width:  <?php echo $width; ?>px; padding-left: <?php echo $css_modifier; ?>px;'>
				<img class='category_indenter' src='<?php echo WPSC_URL; ?>/images/indenter.gif' alt='' title='' />
			<?php } ?>
			<table  class="category-edit" id="category-<?php echo $category->term_id; ?>">
				<tr>
					<td class='manage-column column-img'>
					<?php if($category_image !=null) { ?>
						<img src='<?php echo WPSC_CATEGORY_URL.$category_image; ?>' title='".$category->name; ?>' alt='".$category->name; ?>' width='30' height='30' />
					<?php } else { ?>
						<img style='border-style:solid; border-color: red' src='<?php echo WPSC_URL; ?>/images/no-image-uploaded.gif' title='<?php echo $category->name; ?>' alt='<?php echo $category->name; ?>' width='30' height='30'	/>
					<?php } ?>
					</td>
					
					<td class='manage-column column-title'>
						<?php echo htmlentities(stripslashes($category->name), ENT_QUOTES, 'UTF-8'); ?>
					</td>
					
					<td class='manage-column column-edit'>
						<a href='<?php echo add_query_arg('category_id', $category->term_id); ?>'><?php echo __('Edit', 'wpsc'); ?></a>
					</td>
				</tr>
			</table>
			
			<?php if($subcategory_level > 0) { ?>
				</div>
			<?php } ?>
		</td>
	</tr>
	<?php
}

/*
 * wpsc_admin_category_group_list, prints the right hand side of the edit categories page
 * @param int $category_id the category ID
 * nothing returned
 */
function wpsc_admin_category_forms($category_id =  null) {
	global $wpdb;
	$category_value_count = 0;
	$category_name = '';
	if($category_id > 0 ) {
		$category_id = absint($category_id);		
		
		$category = get_term($category_id, 'wpsc_product_category', ARRAY_A);
		$category['nice-name'] = wpsc_get_categorymeta($category['term_id'], 'nice-name');
		$category['description'] = wpsc_get_categorymeta($category['term_id'], 'description');
		$category['image'] = wpsc_get_categorymeta($category['term_id'], 'image');
		$category['fee'] = wpsc_get_categorymeta($category['term_id'], 'fee');
		$category['active'] = wpsc_get_categorymeta($category['term_id'], 'active');
		$category['order'] = wpsc_get_categorymeta($category['term_id'], 'order');	
	}
	
	?>
	<table class='category_forms'>
		<tr>
			<td>
				<?php echo __('Name', 'wpsc'); ?>:
			</td>
			<td>
				<input type='text'  class="text" name='name' value='<?php echo $category['name']; ?>' />
			</td>
		</tr>
		
		<tr>
			<td><?php _e('Description', 'wpsc'); ?> </td>
			<td>
			<textarea name='description' cols='40' rows='8' ><?php echo stripslashes($category['description']); ?></textarea>
			</td>
		</tr>
		</tr>

		<tr>
			<td>
				<?php _e('Category Parent', 'wpsc'); ?> 
			</td>
			<td>
			<?php
			$top_parent=$category;
			while(absint($top_parent['parent']>0)){
				$top_parent = get_term_by('id', $top_parent['parent'], 'wpsc_product_category', ARRAY_A);
			}
			 wpsc_parent_category_list($top_parent['term_id'], $category['term_id'], $category['parent']);
			?>
		</td>
		</tr>


		<tr>
			<td>
				<?php _e('Group&nbsp;Image', 'wpsc'); ?> 
			</td>
			<td>
				<input type='file' name='image' value='' />
			</td>
		</tr>
		<?php
		if(function_exists("getimagesize")) {
			if($category['image'] != '') {
			$imagepath = WPSC_CATEGORY_DIR . $category['image'];
			$imagetype = @getimagesize($imagepath); //previously exif_imagetype()
			?>
				<tr>
					<td>
					</td>
					<td>
						<?php _e('Height', 'wpsc'); ?>
						<input type='text' size='6' name='height' value='<?php echo $imagetype[1]; ?>' />
						<?php _e('Width', 'wpsc'); ?>
						<input type='text' size='6' name='width' value='<?php echo $imagetype[0]; ?>' />
						<br />
						<span class='wpscsmall description'><?php echo $nzshpcrt_imagesize_info; ?></span>
						<br />
						<span class='wpscsmall description'>
						<?php _e('You can upload thumbnail images for each group.'.
						'To display Group details in your shop you must configure '.
						'these settings under <a href="admin.php?page=wpsc-settings&tab=presentation">Presentation Settings</a>.', 'wpsc'); ?>
						</span>
					</td>
				</tr>
		<?php } else { ?>
				<tr>
					<td>
					</td>
					<td>
						<?php _e('Height', 'wpsc'); ?>
						<input type='text' size='6' name='height' value='<?php echo get_option('product_image_height'); ?>' />
						<?php _e('Width', 'wpsc'); ?>
						<input type='text' size='6' name='width' value='<?php echo get_option('product_image_width'); ?>' />
						<br />
						<span class='wpscsmall description'><?php echo $nzshpcrt_imagesize_info; ?></span>
						<br />
						
						<span class='wpscsmall description'>
						<?php _e('You can upload thumbnail images for each group.'.
						'To display Group details in your shop you must configure '.
						'these settings under <a href="admin.php?page=wpsc-settings&tab=presentation">Presentation Settings</a>.', 'wpsc'); ?>
						</span>
					</td>
				</tr>
			<?php
			}
		}
		?>
		<tr>
			<td>
				<?php _e('Delete Image', 'wpsc'); ?> 
			</td>
			<td>
				<input type='checkbox' name='deleteimage' value='1' />
			</td>
		</tr>

		<tr>
			<td colspan='2' class='category_presentation_settings'>
				<h4><?php _e('Presentation Settings', 'wpsc'); ?></h4>
				<span class='small'><?php _e('To over-ride the presentation settings for this group you can enter in your prefered settings here', 'wpsc'); ?></span>
			</td>
		</tr>

		<tr>
			<td>
			<?php _e('Catalog View', 'wpsc'); ?>
			</td>
			<td>
			<?php
				if ($category['display_type'] == 'grid') {
					$display_type1="selected='selected'";
				} else if ($category['display_type'] == 'default') {
					$display_type2="selected='selected'";
				}
				
				switch($category['display_type']) {
					case "default":
						$category_view1 = "selected ='selected'";
					break;
					
					case "grid":
					if(function_exists('product_display_grid')) {
						$category_view3 = "selected ='selected'";
						break;
					}
					
					case "list":
					if(function_exists('product_display_list')) {
						$category_view2 = "selected ='selected'";
						break;
					}
					
					default:
						$category_view0 = "selected ='selected'";
					break;
				}	
				?>
				<select name='display_type'>	
					<option value='' $category_view0 ><?php _e('Please select', 'wpsc'); ?></option>	
					<option value='default' $category_view1 ><?php _e('Default View', 'wpsc'); ?></option>	
					<?php	if(function_exists('product_display_list')) {?> 
						<option value='list' <?php echo  $category_view2; ?>><?php _e('List View', 'wpsc'); ?></option> 
					<?php	} else { ?>
						<option value='list' disabled='disabled' <?php echo $category_view2; ?>><?php _e('List View', 'wpsc'); ?></option>
						<?php	} ?>
						<?php if(function_exists('product_display_grid')) { ?>
							<option value='grid' <?php echo  $category_view3; ?>><?php _e('Grid View', 'wpsc'); ?></option>
						<?php	} else { ?>
							<option value='grid' disabled='disabled' <?php echo  $category_view3; ?>><?php  _e('Grid View', 'wpsc'); ?></option>
						<?php	} ?>	
				</select>	
			</td>
		</tr>


		<?php	if(function_exists("getimagesize")) { ?>
			<tr>
				<td>
				<?php _e('Thumbnail&nbsp;Size', 'wpsc'); ?> 
				</td>
				<td>
				<?php _e('Height', 'wpsc'); ?> <input type='text' value='<?php echo $category['image_height']; ?>' name='product_height' size='6'/> 
				<?php _e('Width', 'wpsc'); ?> <input type='text' value='<?php echo $category['image_width']; ?>' name='product_width' size='6'/> <br/>
				</td>
			</tr>
		<?php	} ?>



		<tr>
			<td colspan='2' class='category_presentation_settings'>
				<h4><?php _e('Checkout Settings', 'wpsc'); ?></h4>

			</td>
		</tr>


		<?php		$used_additonal_form_set = wpsc_get_categorymeta($category['term_id'], 'use_additonal_form_set'); ?>
			<tr>
				<td>
				<?php _e("This category requires additional checkout form fields",'wpsc'); ?>
				</td>
				<td>

				<select name='use_additonal_form_set'>
					<option value=''><?php _e("None",'wpsc'); ?></option>
				<?php		
				$checkout_sets = get_option('wpsc_checkout_form_sets');
				unset($checkout_sets[0]);
				foreach((array)$checkout_sets as $key => $value) {
					$selected_state = "";
					if($used_additonal_form_set == $key) {
						$selected_state = "selected='selected'";
					} ?>
					<option <?php echo $selected_state; ?> value='<?php echo $key; ?>'><?php echo stripslashes($value); ?></option>
				<?php 
				} 
				?>
				</select>
			</td>
		</tr>


		<tr>
			<td colspan='2'>						</td>
		</tr>

			<?php $uses_billing_address = (bool)wpsc_get_categorymeta($category['term_id'], 'uses_billing_address'); ?>
			<tr>
				<td>
				<?php _e("Products in this category use the billing address to calculate shipping",'wpsc'); ?> 
				</td>
				<td>
				<label><input type='radio' value='1' name='uses_billing_address' <?php echo (($uses_billing_address == true) ? "checked='checked'" : ""); ?> /><?php _e("Yes",'wpsc'); ?></label>
				<label><input type='radio' value='0' name='uses_billing_address' <?php echo (($uses_billing_address != true) ? "checked='checked'" : ""); ?> /><?php _e("No",'wpsc'); ?></label>
				</td>
			</tr>

		<tr>
			<td>
			</td>
			<td>
				<?php wp_nonce_field('edit-category', 'wpsc-edit-category'); ?>
		        <input type='hidden' name='wpsc_admin_action' value='wpsc-category-set' />
				
				<?php if($category_id > 0) { ?>
					<?php
					$nonced_url = wp_nonce_url("admin.php?wpsc_admin_action=wpsc-delete-category-set&amp;deleteid={$category_id}", 'delete-category');
					?>
					<input type='hidden' name='category_id' value='<?php echo $category_id; ?>' />
					<input type='hidden' name='submit_action' value='edit' />
					<input class='button' style='float:left;'  type='submit' name='submit' value='<?php echo __('Edit', 'wpsc'); ?>' />
					<a class='button delete_button' href='<?php echo $nonced_url; ?>' onclick="return conf();" ><?php echo __('Delete', 'wpsc'); ?></a>
				<?php } else { ?>
					<input type='hidden' name='submit_action' value='add' />
					<input class='button'  type='submit' name='submit' value='<?php echo __('Add', 'wpsc');?>' />
				<?php } ?>    
			</td>
		</tr>
	</table>
  <?php
}


?>