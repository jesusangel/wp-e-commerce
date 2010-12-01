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
	$output = "";
	$columns = array(
		'img' =>  __('Image', 'wpsc'),
		'title' => __('Name', 'wpsc')
	);
	register_column_headers('display-categories-list', $columns);	
	
	?>
	<script language='javascript' type='text/javascript'>
		function conf() {
			var check = confirm("<?php _e('Are you sure you want to delete this category?', 'wpsc');?>");
			if(check) {
				return true;
			} else {
				return false;
			}
		}
	</script>
	<noscript></noscript>
	
	<div class="wrap">

		<h2><?php echo esc_html( __('Product Categories', 'wpsc') ); 
			if ( isset( $_GET["category_id"] ) ) {
				$sendback = remove_query_arg('category_id');
				echo "<a class='button add-new-h2' href='".$sendback."' title='Add New'>".__('Add New', 'wpsc')."</a>";
			}
		?> </h2>
			
	<?php if (isset($_GET['deleted']) || isset($_GET['message'])) { ?>
			<div id="message" class="updated fade">
				<p>
					<?php		
					if ( isset($_GET['deleted']) ) {
						_e("Thanks, the category has been deleted", 'wpsc');
						remove_query_arg('deleted');
						unset($_GET['deleted']);
					}
					
					
					if ( isset($_GET['message']) ) {
						if ( 'empty_term_name' == $_GET['message'] )
							_e("Please give a category name", 'wpsc');
						elseif( 'term_exists' == $_GET['message'])
							_e('A term with the name provided already exists with this parent.','wpsc');
						else
							_e("Thanks, the category has been edited", 'wpsc');
						unset($_GET['message']);
						remove_query_arg('message');
					}
					
					$sendback = remove_query_arg( array('deleted', 'message'), $sendback );
					?>
				</p>
			</div>
	<?php } ?>
	
		<div id="col-container">
		
			<div id="col-right" style="width:39%">
				<div class="col-wrap">		
					<?php wpsc_admin_category_group_list();?>
				</div>
			</div>
			
			<div id="col-left" style="width:59%">			
				<div class="col-wrap">
					<div class="form-wrap">
				
				<?php if ( isset( $_GET["category_id"] ) ) {

						$product = get_term($_GET["category_id"], "wpsc_product_category" );
						
						$output .= "<h3 class='hndle'>".str_replace("[categorisation]", (stripslashes($product->name)), __('You are editing the &quot;[categorisation]&quot; Category', 'wpsc'))."</h3>\n\r";	
						echo $output;
						}else{
							echo "<h3>" . __('Add New Category', 'wpsc')."</h3>";
						} ?>
				<form id="modify-category-groups" method="post" action="" enctype="multipart/form-data" >
					<?php
						$category_id = null;
						if (isset($_GET['category_id']))
							$category_id = $_GET['category_id'];
						wpsc_admin_category_forms($category_id);
					?>
				</form>
				<?php if ( isset( $category_id ) ) { echo "</div>";} ?>
						</div>
					</div><!-- form-wrap -->
				</div><!-- col-wrap" -->
			</div><!-- col-left -->
		</div>	<!-- col-container -->		
<?php
}

/**
 * wpsc_admin_category_group_list, prints the right hand side of the edit categories page
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
			<table  class="category-edit" id="category-<?php echo $category->term_id; ?>">
				<tr>
					<td class='manage-column column-img'>
						<?php if($subcategory_level > 0) { ?>
							<div class='category-image-container' style='margin-left: <?php echo (1*$subcategory_level) -1; ?>em;'>
								<img class='category_indenter' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/indenter.gif' alt='' title='' />
							<?php } ?>
							
							<?php if($category_image !=null) { ?>
								<img src='<?php echo WPSC_CATEGORY_URL.$category_image; ?>' title='<?php echo $category->name; ?>' alt='<?php echo $category->name; ?>' width='30' height='30' />
							<?php } else { ?>
								<img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/no-image-uploaded.gif' title='<?php echo $category->name; ?>' alt='<?php echo $category->name; ?>' width='30' height='30'	/>
							<?php } ?>
						<?php if($subcategory_level > 0) { ?>
							</div>
						<?php } ?>
					</td>
					
					<td class='manage-column column-title'>
						<a class="row-title" href='<?php echo add_query_arg('category_id', $category->term_id); ?>'> <?php echo (stripslashes($category->name)); ?></a>
						<div class="row-actions">
							<span class="edit">
											<a class='edit-product' style="cursor:pointer;" title="Edit this Category" href='<?php echo add_query_arg('category_id', $category->term_id); ?>'><?php _e('Edit', 'wpsc'); ?></a>
											</span> | 
										<span class="edit">
										<?php
						$nonced_url = wp_nonce_url("admin.php?wpsc_admin_action=wpsc-delete-category&amp;deleteid={$category->term_id}", 'delete-category');
					?>
					<a class='delete_button' style="text-decoration:none;" href='<?php echo $nonced_url; ?>' onclick="return conf();" ><?php _e('Delete', 'wpsc'); ?></a>	
								</span>
							</div>
						</td>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
}

/*
 * wpsc_admin_category_group_list, prints the left hand side of the edit categories page
 * @param int $category_id the category ID
 * nothing returned
 */
function wpsc_admin_category_forms($category_id =  null) {
	global $wpdb;
	$category_value_count = 0;
	$category_name = '';
	$category = array();
	
	if($category_id > 0 ) {
		$category_id = absint($category_id);		
			
		$category = get_term($category_id, 'wpsc_product_category', ARRAY_A);
		$category['nice-name'] = wpsc_get_categorymeta($category['term_id'], 'nice-name');
		$category['description'] = wpsc_get_categorymeta($category['term_id'], 'description');
		$category['image'] = wpsc_get_categorymeta($category['term_id'], 'image');
		$category['fee'] = wpsc_get_categorymeta($category['term_id'], 'fee');
		$category['active'] = wpsc_get_categorymeta($category['term_id'], 'active');
		$category['order'] = wpsc_get_categorymeta($category['term_id'], 'order');	
		$category['display_type'] = wpsc_get_categorymeta($category['term_id'], 'display_type');	
		$category['image_height'] = wpsc_get_categorymeta($category['term_id'], 'image_height');	
		$category['image_width'] = wpsc_get_categorymeta($category['term_id'], 'image_width');	
	}
	
	?>

	<div class="form-field">
		<label for="tag-name"><?php _e('Name', 'wpsc'); ?></label>
		<input type='text'  class="text" size='40' name='name' value='<?php if(isset($category['name'])) echo $category['name']; ?>' />
	</div>
	
	<div class="form-field">
		<label for="cat-parent"><?php _e('Description', 'wpsc'); ?></label>
		<textarea name='description' cols='40' rows='8' ><?php if (isset($category['description'])) echo stripslashes($category['description']); ?></textarea>
	</div>
	
	<div class="form-field">
		<label for="parent"><?php _e('Category Parent', 'wpsc'); ?></label><?php
				$taxonomies = array('wpsc_product_category');
				$args = array('orderby'=>'name', 'hide_empty' => 0);
				$parent = '';
				$current_term_id = '';
				
				if (isset($category['parent']))
					$parent = $category['parent'];
				
				if (isset($category['term_id']))
					$current_term_id = $category['term_id'];
				
				$select = wpsc_parent_category_list($taxonomies, $args,$parent,$current_term_id);
				echo $select;?>
	</div>

	<h3><?php _e('Advanced Settings', 'wpsc'); ?></h3>	

	<div id="poststuff" class="postbox">
		<h3 class="hndle"><?php _e('Presentation Settings'); ?></h3>
		
		<div class="inside">
			<input type='file' name='image' value='' /><br /><br />
		
				<tr>
					<td>
						<?php _e('Catalog View', 'wpsc'); ?>
					</td>
					<td>
						<?php
					if (!isset($category['display_type'])) $category['display_type'] = '';
						
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
						}?>
							<span class='small'><?php _e('To over-ride the presentation settings for this group you can enter in your prefered settings here', 'wpsc'); ?></span><br /><br />
	
						<select name='display_type'>	
							<option value=''<?php echo $category_view0; ?> ><?php _e('Please select', 'wpsc'); ?></option>	
							<option value='default' <?php if (isset($category_view1)) echo $category_view1; ?> ><?php _e('Default View', 'wpsc'); ?></option>	
							
							<?php	if(function_exists('product_display_list')) {?> 
										<option value='list' <?php echo  $category_view2; ?>><?php _e('List View', 'wpsc'); ?></option> 
							<?php	} else { ?>
										<option value='list' disabled='disabled' <?php if (isset($category_view2)) echo $category_view2; ?>><?php _e('List View', 'wpsc'); ?></option>
							<?php	} ?>
							<?php if(function_exists('product_display_grid')) { ?>
										<option value='grid' <?php if (isset($category_view3)) echo  $category_view3; ?>><?php _e('Grid View', 'wpsc'); ?></option>
							<?php	} else { ?>
										<option value='grid' disabled='disabled' <?php if (isset($category_view3)) echo  $category_view3; ?>><?php  _e('Grid View', 'wpsc'); ?></option>
							<?php	} ?>	
						</select><br /><br />
					</td>
				</tr>
			
			
			<?php	if(function_exists("getimagesize")) { ?>
			<tr>
				<td>
					<?php _e('Thumbnail&nbsp;Size', 'wpsc'); ?> 
				</td>
				<td>
					<?php _e('Height', 'wpsc'); ?> <input type='text' value='<?php if (isset($category['image_height'])) echo $category['image_height']; ?>' name='image_height' size='6'/> 
			<?php _e('Width', 'wpsc'); ?> <input type='text' value='<?php if (isset($category['image_width'])) echo $category['image_width']; ?>' name='image_width' size='6'/> <br/>
				</td>
			</tr>
			<?php	} 
			 _e('Delete Image', 'wpsc'); ?><input type='checkbox' name='deleteimage' value='1' /><br/><br/>
		</div>
	</div> 
	
<!--  SHORT CODE META BOX only display if product has been created -->
 
<?php if ( isset( $_GET["category_id"] ) ) {?>

		<div id="poststuff" class="postbox">
			<h3 class="hndle"><?php _e('Shortcodes and Template Tags'); ?></h3>
			<div class="inside">
				<?php
				$output = '';
				$product = get_term($_GET["category_id"], "wpsc_product_category" );
				$output .= " <span class='wpscsmall description'>Template tags and Shortcodes are used to display a particular category or group within your theme / template or any wordpress page or post.</span>\n\r";
				$output .="<div class='inside'>\n\r";  
				$output .="<div class='editing_this_group form_table'>";
				$output .="<dl>\n\r";
				$output .="<dt>Display Category Shortcode: </dt>\n\r";
				$output .="<dd> [wpsc_products category_url_name='{$product->slug}']</dd>\n\r";
				$output .="<dt>Display Category Template Tag: </dt>\n\r";
				$output .="<dd> &lt;?php echo wpsc_display_products_page(array('category_url_name'=>'{$product->slug}')); ?&gt;</dd>\n\r";
				$output .="</dl>\n\r";
				$output .= "</div></div>";
			$output .= "</div>";
		$output .= "</div>";
		echo $output;	
}?>

<!-- START OF TARGET MARKET SELECTION -->
<div id="poststuff" class="postbox">
	<h3 class="hndle"><?php _e('Target Market Restrictions'); ?></h3>
	<div class="inside"><?php
		$category_id = '';	
		if (isset($_GET["category_id"])) $category_id = $_GET["category_id"];		
		$countrylist = $wpdb->get_results("SELECT id,country,visible FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY country ASC ",ARRAY_A);
		$selectedCountries = wpsc_get_meta($category_id,'target_market','wpsc_category');
		$output = '';
		$output .= " <tr>\n\r";
		$output .= " 	<td>\n\r";
		$output .= __('Target Markets', 'wpsc').":\n\r";
		$output .= " 	</td>\n\r";
		$output .= " 	<td>\n\r";
		
		if(@extension_loaded('suhosin')) {
			$output .= "<em>".__("The Target Markets feature has been disabled because you have the Suhosin PHP extension installed on this server. If you need to use the Target Markets feature then disable the suhosin extension, if you can not do this, you will need to contact your hosting provider.
			",'wpsc')."</em>";
		
		} else {
			$output .= "<span>Select: <a href='' class='wpsc_select_all'>All</a>&nbsp; <a href='' class='wpsc_select_none'>None</a></span><br />";
			$output .= " 	<div id='resizeable' class='ui-widget-content multiple-select'>\n\r";
			foreach($countrylist as $country){
				if(in_array($country['id'], (array)$selectedCountries)){
					$output .= " <input type='checkbox' name='countrylist2[]' value='".$country['id']."'  checked='".$country['visible']."' />".$country['country']."<br />\n\r";
				} else {
					$output .= " <input type='checkbox' name='countrylist2[]' value='".$country['id']."'  />".$country['country']."<br />\n\r";
				}
			}
			$output .= " </div><br /><br />";
			$output .= " <span class='wpscsmall description'>Select the markets you are selling this category to.<span>\n\r";
		}
		$output .= "   </td>\n\r";
		$output .= " </tr>\n\r";
		////////
		echo $output;
		?>
	</div>
</div>

<!-- Checkout settings -->
<div id="poststuff" class="postbox">
	<h3 class="hndle"><?php _e('Checkout Settings', 'wpsc'); ?></h3>
	<div class="inside">
		<table class='category_forms'>
			<?php		
			if (!isset($category['term_id'])) $category['term_id'] = '';
				$used_additonal_form_set = wpsc_get_categorymeta($category['term_id'], 'use_additonal_form_set'); ?>
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
							if($used_additonal_form_set == $key)
								$selected_state = "selected='selected'";
							 ?>
								<option <?php echo $selected_state; ?> value='<?php echo $key; ?>'><?php echo stripslashes($value); ?></option>
							<?php 
							} 
							?>
						</select>
					</td>
				</tr>
			<?php $uses_billing_address = (bool)wpsc_get_categorymeta($category['term_id'], 'uses_billing_address'); ?>
				<tr>
					<td>
						<?php _e("Products in this category use the billing address to calculate shipping",'wpsc'); ?> 
					</td>
					<td>
						<input type='radio' value='1' name='uses_billing_address' <?php echo (($uses_billing_address == true) ? "checked='checked'" : ""); ?> /><?php _e("Yes",'wpsc'); ?>
						<input type='radio' value='0' name='uses_billing_address' <?php echo (($uses_billing_address != true) ? "checked='checked'" : ""); ?> /><?php _e("No",'wpsc'); ?>
					</td>
				</tr>
		</table>
	</div>
</div>

<table class="category_forms">
	<tr>
		<td>
			<?php wp_nonce_field('edit-category', 'wpsc-edit-category'); ?>
			<input type='hidden' name='wpsc_admin_action' value='wpsc-category-set' />
			
			<?php if($category_id > 0) { ?>
			<?php
					$nonced_url = wp_nonce_url("admin.php?wpsc_admin_action=wpsc-delete-category&amp;deleteid={$category_id}", 'delete-category');
					?>
					<input type='hidden' name='category_id' value='<?php echo $category_id; ?>' />
					<input type='hidden' name='submit_action' value='edit' />
					<input class='button-primary' style='float:left;'  type='submit' name='submit' value='<?php _e('Edit Category', 'wpsc'); ?>' />
					<a class='delete_button' style="text-decoration:none;" href='<?php echo $nonced_url; ?>' onclick="return conf();" ><?php _e('Delete', 'wpsc'); ?></a>
			<?php } else { ?>
					<input type='hidden' name='submit_action' value='add' />
					<input class='button-primary'  type='submit' name='submit' value='<?php _e('Add Category', 'wpsc');?>' />
			<?php } ?>    
		</td>
	</tr>
</table>
  <?php
}


?>
