<?php
/**
 * WP eCommerce edit and add product category page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_ajax_set_category_order(){
  global $wpdb;
  $sort_order = $_POST['sort_order'];
  $parent_id  = $_POST['parent_id'];
  
  $result = true;
  foreach($sort_order as $key=>$value){
    if(!wpsc_update_meta($value, 'sort_order', $key, 'wpsc_category')){
      $result = false; 
    }
  }
}


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
		   function categorySort(order, parent){
			var data = {
				action: 'category_sort_order',
				sort_order: order,
				parent_id: parent
			};

			var id = '#debugData_';
			// Send an Ajax Request to get the WinRetail SQL Dump good for Debugging
			jQuery.post(ajaxurl, data, function(response) {
				jQuery(id).append(response);
			});
			return false;
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
  $category_list = wpsc_admin_get_category_array();
  
  ?>
  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery('#category_list_li').sortable({
        axis: 'y',
        containment: 'parent',
        handle: '.handle',
        tolerance: 'pointer',
        update: function(event, ui){
          categorySort(jQuery('#category_list_li').sortable('toArray'), 0);
        }
      });
    });
  </script>
  <div id='poststuff' class='postbox'>
    <h3 class='hndle'><?php _e('Product Categories', 'wpsc'); ?></h3>
    <div class="inside">
      <ul id='category_list_li' class='ui-sortable'>
        <?php 

        print wpsc_admin_list_category_array($category_list); ?>
      </ul>
    </div>
  </div>
  <?php
}

/**
 *  Create the actual drag and drop list used for the admin category view
 * 
 * @param array $categories
 * @param int $level
 * @return string $output
 */
function wpsc_admin_list_category_array($categories, $level = 0){
  $output = '';
  foreach($categories as $cat){
	
    $output .= "<li id='".$cat['id']."'>";
    $output .= "<div id='category-".$cat['id']."-container'>";

    $output .= "<div class='category_admin_list_img' id='category-".$cat['id']."-imgs'>";
    $output .= "<span title='click and drag to move' class='handle'>↕</span>";
    if($level > 0){
      $output .= "<img class='category_indenter' src='".WPSC_CORE_IMAGES_URL."/indenter.gif' alt='' title='' />";
    }
    $output .= "<a class='row-title' href='".add_query_arg('category_id', $cat['id'])."'>";
    if(isset($cat['image'])){
      $output .= "<img src=\"".WPSC_CATEGORY_URL.stripslashes($cat['image'])."\" title='".$cat['name']."' alt='".$cat['name']."' width='30' height='30' />";
    }else{
      $output .= "<img src='".WPSC_CORE_IMAGES_URL."/no-image-uploaded.gif' title='".$cat['name']."' alt='".$cat['name']."' width='30' height='30' />";
    }
    $output .= stripslashes($cat['name'])."</a>";

    $output .= "<div class='row-actions'><span class='edit'><a class='edit-product' style='cursor:pointer;' title='Edit This Category' href='".add_query_arg('category_id', $cat['id'])."'>". __('Edit', 'wpsc')."</a>";
    $output .= "</span> | <span class='edit'>";
    $nonced_url = wp_nonce_url("admin.php?wpsc_admin_action=wpsc-delete-category&amp;deleteid={$cat['id']}", 'delete-category');
    $output .=  "<a class='delete_button' style='text-decoration:none;' href='".$nonced_url."' onclick=\"return conf();\" >". __('Delete', 'wpsc')."</a>"; 
    $output .=  "</span></div>";
    $output .= "</div>";    
    if(is_array($cat['children'])){
      $newhandle = "category-".$cat['id']."-children";
      $output .= <<<EOT
  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery('#{$newhandle}').sortable({
        axis: 'y',
        containment: 'parent',
        handle: '.handle',
        tolerance: 'pointer',
        update: function(event, ui){
          categorySort(jQuery('#{$newhandle}').sortable('toArray'), 0);
        }
      });
    });
  </script>
EOT;
      $output .= "<ul id='{$newhandle}' class='ui-sortable'>";
      $output .= wpsc_admin_list_category_array($cat['children'], ($level + 1));
      $output .= "</ul>";
    }
    $output .= "</div></li>";

  }
  return $output;
}

/**
 * wpsc_admin_get_category_array
 * Recursively step through the categories and return it in a clean multi demensional array
 * for use in other list functions
 * @param int $parent_id
 */
function wpsc_admin_get_category_array($parent_id = null){
  global $wpdb;
 
  $orderedList = array();
  if(!isset($parent_id)) $parent_id = 0;
  $category_list = get_terms('wpsc_product_category','hide_empty=0&parent='.$parent_id);
  if(!is_array($category_list)){
    return false;
  }
  foreach($category_list as $category){
    $category_order = wpsc_get_categorymeta($category->term_id, 'order');
    $category_image = wpsc_get_categorymeta($category->term_id, 'image');
    if(!isset($category_order) || $category_order == 0) $category_order = (count($orderedList) +1);
    print "<!-- setting category Order number to ".$category_order."-->";
    $orderedList[$category_order]['id'] = $category->term_id;
    $orderedList[$category_order]['name'] = $category->name;
    $orderedList[$category_order]['image'] = $category_image;
    $orderedList[$category_order]['parent_id'] = $parent_id;
    $orderedList[$category_order]['children'] = wpsc_admin_get_category_array($category->term_id);
  }

  ksort($orderedList);
  return($orderedList);
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
