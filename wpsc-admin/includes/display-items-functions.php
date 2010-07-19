<?php
/**
 * WPSC Product form generation functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
//$closed_postboxes = (array)get_usermeta( $current_user->ID, 'editproduct');
//$variations_processor = new nzshpcrt_variations;


$wpsc_product_defaults =array (
  'id' => '0',
  'name' => '',
  'description' => '',
  'additional_description' => '',
  'price' => '0.00',
  'weight' => '0',
  'weight_unit' => 'pound',
  'pnp' => '0.00',
  'international_pnp' => '0.00',
  'file' => '0',
  'image' => '',
  'category' => '0',
  'brand' => '0',
  'quantity_limited' => '0',
  'quantity' => '0',
  'special' => '0',
  'special_price' => '',
  'display_frontpage' => '0',
  'notax' => '0',
  'publish' => '1',
  'active' => '1',
  'donation' => '0',
  'no_shipping' => '0',
  'thumbnail_image' => '',
  'thumbnail_state' => '1',
  'meta' => 
  array (
    'external_link' => NULL,
    'merchant_notes' => NULL,
    'sku' => NULL,
    'engrave' => '0',
    'can_have_uploaded_image' => '0',
    'table_rate_price' => 
    array (
      'quantity' => 
      array (
        0 => '',
      ),
      'table_price' => 
      array (
        0 => '',
      ),
    ),
  ),
);
// Justin Sainton - 5.8.2010 - Adding this function for backwards_compatible array_replace

if (!function_exists('array_replace_recursive'))
{
  function array_replace_recursive($array, $array1)
  {
    function recurse($array, $array1)
    {
      foreach ($array1 as $key => $value)
      {
        // create new key in $array, if it is empty or not an array
        if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
        {
          $array[$key] = array();
        }
  
        // overwrite the value in the base array
        if (is_array($value))
        {
          $value = recurse($array[$key], $value);
        }
        $array[$key] = $value;
      }
      return $array;
    }
  
    // handle the arguments, merge one by one
    $args = func_get_args();
    $array = $args[0];
    if (!is_array($array))
    {
      return $array;
    }
    for ($i = 1; $i < count($args); $i++)
    {
      if (is_array($args[$i]))
      {
        $array = recurse($array, $args[$i]);
      }
    }
    return $array;
  }
}

function wpsc_populate_product_data($product_id, $wpsc_product_defaults) {
  global $wpdb;
	$product = get_post($product_id);
	 //print("<pre>" . print_r($product, true) . "</pre>");

	$product_data['id'] = $product->ID;
	$product_data['name'] = $product->post_title;
	$product_data['post_type'] = $product->post_type;
	$product_data['post_status'] = $product->post_status;
	$product_data['description'] = $product->post_content;
	$product_data['additional_description'] = $product->post_excerpt;
	// get the list of categories this product is associated with
	
	$product_data['categories'] = wp_get_product_categories($product->ID);
	$product_data['tags'] = wp_get_product_tags($product->ID);
	$product_data['category_ids'] = array();
	
	$product_data['product_object'] = $product;
	
	foreach((array)$product_data['categories'] as $category_item) {
		$product_data['category_ids'][] = (int)$category_item->term_id;  
	}
	
	
	// Meta Values come straight from the meta table
	$product_data['meta'] = array();
	$product_data['meta'] = get_post_meta($product->ID, '');
	if ( is_array ( $product_data['meta'] ) ) {
	foreach($product_data['meta'] as $meta_name => $meta_value) {
		$product_data['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
	}
}
	//$sql ="SELECT `meta_key`, `meta_value` FROM ".WPSC_TABLE_PRODUCTMETA." WHERE `meta_key` LIKE 'currency%' AND `product_id`=".$product_id;
	//$product_data['newCurr']= $wpdb->get_results($sql, ARRAY_A);
	$product_data['dimensions'] = get_product_meta($product_id, 'dimensions',true);

	// Transformed Values have been altered in some way since being extracted from some data source
	$product_data['transformed'] = array();
	$product_data['transformed']['weight'] = wpsc_convert_weight($product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['display_weight_as']);

	//echo "<pre>".print_r($product_data,true)."</pre>";
	if(function_exists('wp_insert_term')) {
		$term_relationships = $wpdb->get_results("SELECT * FROM `{$wpdb->term_relationships}` WHERE object_id = '{$product_data['id']}'", ARRAY_A);

		foreach ((array)$term_relationships as $term_relationship) {
			$tt_ids[] = $term_relationship['term_taxonomy_id'];
		}
		foreach ((array)$tt_ids as $tt_id) {
			$term_ids[] = $wpdb->get_var("SELECT `term_id` FROM `{$wpdb->term_taxonomy}` WHERE `term_taxonomy_id` = '{$tt_id}' AND `taxonomy` = 'product_tag' LIMIT 1");
		}
		foreach ((array)$term_ids as $term_id ) {
			if ($term_id != NULL){
				$tags[] = $wpdb->get_var("SELECT `name` FROM `{$wpdb->terms}` WHERE `term_id`='{$term_id}' LIMIT 1");
			}
		}
		if ($tags != NULL){
			$imtags = implode(',', $tags);
		}
	}
	return $product_data;
}

function wpsc_display_product_form ($product_id = 0) {
	global $wpdb, $wpsc_product_defaults;
	$product_id = absint($product_id);
		
	if($product_id > 0) {
		$product_data = wpsc_populate_product_data($product_id, $wpsc_product_defaults);
	} else {
		if(isset($_SESSION['wpsc_failed_product_post_data']) && (count($_SESSION['wpsc_failed_product_post_data']) > 0 )) {
			$product_data = array_merge($wpsc_product_defaults, $_SESSION['wpsc_failed_product_post_data']);
			$_SESSION['wpsc_failed_product_post_data'] = null;
		} else {
			$product_data = $wpsc_product_defaults;
		}
	}
	
	$current_user = wp_get_current_user();
	
	// we put the closed postboxes array into the product data to propagate it to each form without having it global.
	$product_data['closed_postboxes'] = (array)get_usermeta( $current_user->ID, 'closedpostboxes_products_page_wpsc-edit-products');
	$product_data['hidden_postboxes'] = (array)get_usermeta( $current_user->ID, 'metaboxhidden_products_page_wpsc-edit-products');
	
	if(count($product_data) > 0) {
		wpsc_product_basic_details_form($product_data);
	}
}
/*
	Stop-gap function replicating native WP functionality
*/

function add_new_product_id() {
	global $wpdb;
	$prod_id = $wpdb->get_var($wpdb->prepare("SELECT MAX( ID ) FROM  $wpdb->posts"));
	$prod_id = $prod_id + 1;
	
	$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->posts ( post_author, post_date, post_date_gmt, post_title, post_status, post_parent, post_type)
	VALUES ( %d, %s, %s, %s, %s, %d, %s )", 
        1, 'CURDATE()', 'CURDATE()', 'stopgap', 'inherit', $prod_id, 'attachment' ) );

	return $prod_id;
}

function delete_stopgap() {
global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->posts WHERE post_parent = $uploading_iframe_ID AND post_title = 'stopgap'" );
}
if($product_data['id'] > 0) {
	add_action('transition_post_status', 'delete_stopgap');
}
function wpsc_product_basic_details_form(&$product_data) {
	global $wpdb,$nzshpcrt_imagesize_info, $user_ID;
	$product = $product_data['product_object'];
	$post_ID = (int) $product_data["id"];
	/*<h3 class='hndle'><?php echo  __('Product Details', 'wpsc'); ?> <?php echo __('(enter in your product details here)', 'wpsc'); ?></h3>*/
  ?>
  <h3 class='form_heading' style="display:none;">
 <?php
  if($product_data['id'] > 0) {
		echo __('Edit Product', 'wpsc');
	} else {
		echo __('Add New', 'wpsc');
		$product_data["id"] = $_GET["product"] = add_new_product_id(); 
	}
	$form_extra = '';
if ( 'auto-draft' == $product->post_status ) {
	if ( 'edit' == $action )
		$product->post_title = '';
	$autosave = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave( $product_data["id"] );
}

$nonce_action = 'update-' . $product->post_type . '_' . $product_data["id"];
$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr($product_data["id"]) . "' />";

$sticky = get_option( 'sticky_posts' );
if ( in_array ( $product_data["id"], $sticky ) )
$sticky_checked = 'checked="checked" '; 
 
?>
	</h3>
	<div id="side-info-column" class="inner-sidebar">
		<div id="side-sortables" class='meta-box-sortables-wpec ui-sortable'>
		<?php wp_nonce_field($nonce_action); ?>
		<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
		<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr($form_action) ?>" />
		<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr($form_action) ?>" />
		<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $product->post_author ); ?>" />
		<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr($product->post_type) ?>" />
		<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr($product->post_status) ?>" />
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />
		<?php
		if ( 'draft' != $product->post_status )
		wp_original_referer_field(true, 'previous');

		echo $form_extra;

		wp_nonce_field( 'autosave', 'autosavenonce', false );
		?>
		<input type='hidden' name='product_id' id='product_id' value='<?php echo (int) $product_data["id"]; ?>' />
		<input type='hidden' name='wpsc_admin_action' value='edit_product' />
		<?php wp_nonce_field('edit-product', 'wpsc-edit-product'); ?>
	
	<?php /*
	<input class='button-primary' style='float:left;'  type='submit' name='submit' value='<?php if($product_data['id'] > 0) { 	_e('Update Product', 'wpsc'); } else {	_e('Add New Product', 'wpsc');	} ?>' />&nbsp;
	*/ ?> 
	<div id="submitdiv" class="postbox">
		<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Publish</span></h3>
			<div class="inside publish">
			<div class="submitbox" id="submitpost"><br />
				<span id="sticky-span" style="display: inline; "><input id="sticky" name="sticky" type="checkbox" <?php echo $sticky_checked; ?>value="sticky" tabindex="4"> <label for="sticky" class="selectit">Stick this product to the front page</label><br></span>
				<div id="minor-publishing">
					<div id="minor-publishing-actions">
						<div id="save-action">
							<?php
								if(($product->post_status == 'draft') || ($product->post_status == null)) {
									?>
									<input type='submit' value='<?php _e('Save Draft', 'wpsc'); ?>' class='button button-highlighted' id="save-post" name='save' />
									<?php	
								} else {
									?>	
								<input type='submit' value='<?php _e('Unpublish', 'wpsc'); ?>' class='button button-highlighted' id='save-post' name='unpublish' />
									<?php
								}
							?>
						</div>
						<div id="preview-action">
							<a class="preview button" id="post-preview" href="<?php echo wpsc_product_url( $product_data['id'] ); ?>"><?php _e('View Product') ?></a>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div id="major-publishing-actions">
				<div id="delete-action">
					<a class='submitdelete deletion' title='<?php echo attribute_escape(__('Delete this product')); ?>' href='<?php echo wp_nonce_url("page.php?wpsc_admin_action=trash&amp;product={$product_data['id']}", 'delete_product_' . $product_data['id']); ?>' onclick="if ( confirm(' <?php echo js_escape(sprintf( __("You are about to delete this product '%s'\n 'Cancel' to stop, 'OK' to delete."), $product_data['name'] )) ?>') ) { return true;}return false;"><?php _e('Move to Trash') ?>
					</a><br />
					</div>
				<div id="publishing-action">
					<?php
					if(($product->post_status == 'draft') || ($product->post_status == null)) {
						?>
						<input type='submit' value='<?php _e('Publish', 'wpsc'); ?>' id='publish' class='button-primary' name='publish' />
						<?php	
					} else {
						?>	
									<input type='submit' value='<?php _e('Update', 'wpsc'); ?>' id='publish' class='button-primary' name='save' />
						<?php
					}
					?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
	
	
		<?php
		
		$default_order = array( 
			"advanced" => array(				
				"wpsc_product_shipping_forms",
				"wpsc_product_variation_forms",
				"wpsc_product_advanced_forms"
				), 
			"side" => array(
				"wpsc_product_category_and_tag_forms",
				"wpsc_product_price_and_stock_forms",
				"wpsc_product_image_forms",
				"wpsc_product_download_forms"
				),
			"closedboxes" => array(
				"wpsc_product_shipping_forms" => 1,
				"wpsc_product_variation_forms" => 1,
				"wpsc_product_advanced_forms" => 1,
				"wpsc_product_category_and_tag_forms" => 1,
				"wpsc_product_price_and_stock_forms" => 1,
				"wpsc_product_image_forms" => 1,
				"wpsc_product_download_forms" => 1
			),
			"hiddenboxes" => array(
				"wpsc_product_shipping_forms" => 1,
				"wpsc_product_variation_forms" => 1,
				"wpsc_product_advanced_forms" => 1,
				"wpsc_product_category_and_tag_forms" => 1,
				"wpsc_product_price_and_stock_forms" => 1,
				"wpsc_product_image_forms" => 1,
				"wpsc_product_download_forms" => 1
			)
		  );
		
	 	$order = get_option('wpsc_product_page_order');	 	
	 	
		$order = apply_filters( 'wpsc_products_page_forms', $order);
	  
	 	if ( ( $order == '' ) || ( count ( $order, COUNT_RECURSIVE ) < 24 ) || ( count ( $order ) == count ( $order, COUNT_RECURSIVE ) ) ) {
				$order = $default_order;
	 	}
	 	$check_missing_items = array_diff($default_order, $order);
	 	
	 	if(count($check_missing_items) > 0) {
	 	  $order = array_merge($check_missing_items, $order);
	 	}
		
		update_option('wpsc_product_page_order', $order);
		
	 	// if this is a child product, we need to filter out the variations box here
	 	if($product_data['product_object']->post_parent > 0) {
	 		$variation_box_key = array_search('wpsc_product_variation_forms', $order);
	 		if(is_numeric($variation_box_key) && isset($order[$variation_box_key])) {
	 			unset($order[$variation_box_key]);
	 		}
	 		
	 		$category_box_key = array_search('wpsc_product_category_and_tag_forms', $order);
	 		if(is_numeric($category_box_key) && isset($order[$category_box_key])) {
	 			unset($order[$category_box_key]);
	 		}
	 		
	 	}
		foreach((array)$order["side"] as $key => $box_function_name) {
			if(function_exists($box_function_name)) {
				echo call_user_func($box_function_name,$product_data);
			}
		}
	//	echo "<pre>"; print_r($order); echo "</pre>";
		?>	
	</div>
	</div>
<script type="text/javascript">
var makeSlugeditClickable;
makeSlugeditClickable = null;
	//<![CDATA[
	jQuery(document).ready( function($) { 
	<?php 
		$closed_boxes = $order["closedboxes"];
		foreach ($closed_boxes as $key=>$val) {
			if ( $val == 0 ) {
			?>
				$('div#<?php echo $key; ?>').addClass('closed');
			<?
			}
		}
	?>
	$('#poststuff .postbox h3, .postbox div.handlediv').click( function() {			
			$(this).parent().toggleClass('closed');
			wpsc_save_postboxes_state('store_page_wpsc-edit-products', '#poststuff');
		});

	<?php 
		$hidden_boxes = $order["hiddenboxes"];
		foreach ($hidden_boxes as $key=>$val) {
			if ( $val == 0 ) {
			?>
				$('div#<?php echo $key; ?>').css('display', 'none');
				$('div.metabox-prefs label input[value=<?php echo $key; ?>]').attr('checked', false);
			<?php
			}
				elseif ($val == 1) {
				
				?>
				$('div.metabox-prefs label input[value=<?php echo $key; ?>]').attr('checked', true);

			<?php
			
			}
		}
	?>		
        });
	//]]>
</script>
<div id="post-body" class="has-sidebar">
	<div id="post-body-content">
		<table class='product_editform' >
			<tr>
				<td colspan='2' class='itemfirstcol'>  
					<div class='admin_product_name'>
						<input id='title' class='wpsc_product_name text' size='15' type='text' name='post_title' value='<?php echo htmlentities(stripslashes($product_data['name']), ENT_QUOTES, 'UTF-8'); ?>' />
						<a href='#' class='shorttag_toggle'></a>
					</div>
					<div class='admin_product_shorttags'>
						<h4><?php _e('Shortcodes', 'wpsc'); ?></h4>
	
						<dl>
							<dt><?php _e('Display Product Shortcode', 'wpsc'); ?>: </dt><dd>[wpsc_products product_id='<?php echo $product_data['id'];?>']</dd>
							<dt><?php _e('Buy Now Shortcode', 'wpsc'); ?>: </dt><dd>[buy_now_button=<?php echo $product_data['id'];?>]</dd>
							<dt><?php _e('Add to Cart Shortcode', 'wpsc'); ?>: </dt><dd>[add_to_cart=<?php echo $product_data['id'];?>]</dd>
						</dl>

						<h4><?php _e('Template Tags', 'wpsc'); ?></h4>

						<dl>
							<dt><?php _e('Display Product Template Tag', 'wpsc'); ?>: </dt><dd> &lt;?php echo wpsc_display_products('product_id=<?php echo $product_data['id'];?>'); ?&gt;</dd>
							<dt><?php _e('Buy Now PHP', 'wpsc'); ?>: </dt><dd>&lt;?php echo wpsc_buy_now_button(<?php echo $product_data['id'];?>); ?&gt;</dd>
							<dt><?php _e('Add to Cart PHP', 'wpsc'); ?>: </dt><dd>&lt;?php echo wpsc_add_to_cart_button(<?php echo $product_data['id'];?>); ?&gt;</dd>
							<dt><?php _e('Display Product SKU', 'wpsc'); ?>: </dt><dd>&lt;?php echo wpsc_product_sku(<?php echo $product_data['id'];?>); ?&gt;</dd>
						</dl>
						
						<?php if ( $product_data['id'] > 0 ) { ?>
							<p><a href="<?php echo wpsc_product_url( $product_data['id'] ); ?>" target="_blank" class="button">View product</a></p>
						<?php } ?>
						
					</div>
					<div style='clear:both; height: 0px; margin-bottom: 15px;'></div>	
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea" >
				 <?php
						wpsc_the_editor($product_data['description'], 'content',true, true);
				 ?>
				 <table id="post-status-info" cellspacing="0"><tbody><tr>
	<td id="wp-word-count"></td>
	<td class="autosave-info">
	<span id="autosave">&nbsp;</span>
	</td>
</tr></tbody></table>
				 </div>
				</td>
			</tr>
		
			<tr>
				<td class='itemfirstcol' colspan='2'>
					
					<strong ><?php echo __('Additional Description', 'wpsc'); ?> :</strong><br />			
					
					<textarea name='additional_description' id='additional_description' cols='40' rows='5' ><?php echo stripslashes($product_data['additional_description']); ?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div id="advanced-sortables" class="meta-box-sortables-wpec ui-sortable">
		<?php 
					foreach((array)$order["advanced"] as $key => $box_function_name) {
			if(function_exists($box_function_name)) {
				echo call_user_func($box_function_name,$product_data);
			}
		}
		?>
	</div>
</div>
	<?php
  }
function wpsc_product_category_and_tag_forms($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor;
	
	$output = '';
	//echo "<pre>".print_r($product_data['tags'], true)."</pre>";
	$tag_array = array();
	foreach((array)$product_data['tags'] as $tag) {
	  $tag_array[] = $tag->name;
	}
	if ($product_data == 'empty') {
		$display = "style='visibility:hidden;'";
	}
	$output .= "<div id='wpsc_product_category_and_tag_forms' class=' postbox ".((array_search('wpsc_product_category_and_tag_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."'><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

    if (IS_WP27) {
        $output .= "<h3 class='hndle'>";
    } else {
        $output .= "<h3>
	    <a class='togbox'>+</a>";
    }
    $output .= __('Categories and Tags', 'wpsc');

    $output .= "
	</h3>
    <div class='inside'>
    <table>";
    $output .= "
      <tr>
      <td class='itemfirstcol'>
				<strong >".__('Product Categories', 'wpsc')." </strong>
				<div id='categorydiv' >";
					$search_sql = apply_filters('wpsc_product_category_and_tag_forms_group_search_sql', '');
					//$categorisation_groups = get_terms('wpsc_product_category', "hide_empty=0&parent=0", ARRAY_A);
				
					$output .= wpsc_category_list($product_data, 0, $product_data['id'], 'edit_');
						

     $output .= "
			</div>
     </td>
     <td class='itemfirstcol product_tags'>
				<strong > ".__('Product Tags', 'wpsc')."</strong><br />
				<p id='jaxtag'>
					<label for='tags-input' class='hidden'>".__('Product Tags', 'wpsc')."</label>
					<input type='text' value='".implode(',',$tag_array)."' tabindex='3' size='20' id='tags-input' class='tags-input' name='product_tags'/>
				<span class='howto'>".__('Separate tags with commas')."</span>
				</p>
				<div id='tagchecklist' class='tagchecklist' onload='tag_update_quickclicks();'></div>

      </td>
      
    </tr>";
$output .= "
  </table>
 </div>
</div>";
$output = apply_filters('wpsc_product_category_and_tag_forms_output', $output);

return $output;

}
function wpsc_product_price_and_stock_forms($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor;
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];
	$table_rate_price = $product_data['meta']['_wpsc_table_rate_price'];
	$custom_tax = $product_data['meta']['_wpsc_custom_tax'];
	
	if ($product_data == 'empty') {
		$display = "style='visibility:hidden;'";
	}
	echo "<div id='wpsc_product_price_and_stock_forms' class='wpsc_product_price_and_stock_forms postbox ".((array_search('wpsc_product_price_and_stock_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_price_and_stock_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '')." ><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

	echo "<h3 class='hndle'>";

    echo __('Price and Stock Control', 'wpsc');
    echo "
	</h3>
    <div class='inside'>
    <table>
    ";    
   // echo "<pre>".print_r($product_data['meta']['_wpsc_product_metadata'],true)."</pre>";
    ?><br />
				<tr>
				<td colspan='3' class='skuandprice'>
					<div class='wpsc_floatleft'>
						<?php echo __('Stock Keeping Unit', 'wpsc'); ?> :<br />
						<input size='17' type='text' class='text'  name='meta[_wpsc_sku]' value='<?php echo htmlentities(stripslashes($product_data['meta']['_wpsc_sku']), ENT_QUOTES, 'UTF-8'); ?>' />
					</div>
					
					<div class='wpsc_floatleft'>
					<?php echo __('Price', 'wpsc'); ?> :<br />
					<input type='text' class='text' size='17' name='meta[_wpsc_price]' value='<?php echo number_format($product_data['meta']['_wpsc_price'], 2); ?>' />
					</div>
					
					<div class='wpsc_floatleft' style='display:<?php if(($product_data['special'] == 1) ? 'block' : 'none'); ?>'>
	    			   <label for='add_form_special'><?php echo __('Sale Price :', 'wpsc'); ?></label>
				       <div id='add_special'>		
	        			  <input type='text' size='17' value='<?php echo number_format( $product_data['meta']['_wpsc_special_price'], 2); ?>' name='meta[_wpsc_special_price]' />
				       </div>
			       </div>

      			</td>
    
	
			</tr>
		
			<tr>
				<td ><a href='' class='wpsc_add_new_currency'>+ <?php echo __('New Currency', 'wpsc');?></a></td>
			</tr>
			<tr class='new_layer'>
					<td>
						<label for='newCurrency[]'><?php echo __('Currency type', 'wpsc');?>:</label><br />
						<select name='newCurrency[]' class='newCurrency'>
						<?php
						$currency_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
						foreach((array)$currency_data as $currency) {
							if($isocode == $currency['isocode']) {
								$selected = "selected='selected'";
							} else {
								$selected = "";
							} ?>
							<option value='<?php echo $currency['id']; ?>' <?php echo $selected; ?> ><?php echo htmlspecialchars($currency['country']); ?> (<?php echo $currency['currency']; ?>)</option>
				<?php	}  
						$currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A) ;
						if($currency_data['symbol'] != '') {
							$currency_sign = $currency_data['symbol_html'];
						} else {
							$currency_sign = $currency_data['code'];
						}
				?>
						</select>
						</td>
						<td>
						<?php echo __('Price', 'wpsc');?> :<br />
						<input type='text' class='text' size='15' name='newCurrPrice[]' value='0.00' />
						<a href='' class='deletelayer' rel='<?php echo $isocode; ?>'><?php echo __('Delete Currency', 'wpsc');?></a>
						</td>

			</tr>
			<?php if(count($product_data['newCurr']) > 0) :
				$i = 0;
				foreach($product_data['newCurr'] as $newCurr){  
				$i++;
				$isocode = str_replace("currency[", "", $newCurr['meta_key']);
				$isocode = str_replace("]", "", $isocode);
			//	exit('ere<pre>'.print_r($isocode, true).'</pre>'); 
				
				?>
					<tr>
						<td>
						<label for='newCurrency[]'><?php echo __('Currency type', 'wpsc');?>:</label><br />
						<select name='newCurrency[]' class='newCurrency'>
						<?php
						$currency_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
						foreach($currency_data as $currency) {
							if($isocode == $currency['isocode']) {
								$selected = "selected='selected'";
							} else {
								$selected = "";
							}
							?>
							<option value='<?php echo $currency['id']; ?>' <?php echo $selected; ?> ><?php echo htmlspecialchars($currency['country']); ?> (<?php echo $currency['currency']; ?>)</option>
							<?php
						}  
						$currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A) ;
						if($currency_data['symbol'] != '') {
							$currency_sign = $currency_data['symbol_html'];
						} else {
							$currency_sign = $currency_data['code'];
						}
						?>
						</select>
						</td>
						<td>
						Price:<br />
						<input type='text' class='text' size='15' name='newCurrPrice[]' value='<?php echo $newCurr['meta_value']; ?>' />
						<a href='' class='wpsc_delete_currency_layer' rel='<?php echo $isocode; ?>'><?php echo __('Delete Currency', 'wpsc');?></a>
						</td>
					</tr>
			<?php } ?>
			<?php endif; 
    echo "
    <tr>
       <td>
          <br /><input id='add_form_donation' type='checkbox' name='meta[_wpsc_is_donation]' value='yes' ".(($product_data['meta']['_wpsc_is_donation'] == 1) ? 'checked="checked"' : '')." />&nbsp;<label for='add_form_donation'>".__('This is a donation, checking this box populates the donations widget.', 'wpsc')."</label>
       </td>
    </tr>";
?>
			
     <tr>
      <td><br />

        <input type='checkbox' value='1' name='table_rate_price[state]' id='table_rate_price'  <?php echo (((bool)$product_meta['table_rate_price']['state'] == true) ? 'checked=\'checked\'' : ''); ?> />
        
        
        <label for='table_rate_price'><?php echo __('Table Rate Price', 'wpsc'); ?></label>
        <div id='table_rate'>
          <a class='add_level' style='cursor:pointer;'>+ Add level</a><br />
          <table>
						<tr>
							<td><br /><?php echo __('Quantity In Cart', 'wpsc'); ?></td>
							<td><?php echo __('Discounted Price', 'wpsc'); ?></td>
						</tr>
						<?php
						if(count($product_meta['table_rate_price']['quantity']) > 0 ) {
							foreach((array)$product_meta['table_rate_price']['quantity'] as $key => $quantity) {
								if($quantity != '') {
									$table_price = number_format($product_meta['table_rate_price']['table_price'][$key], 2, '.', '');
									?>
									<tr>
										<td>
											<input type="text" size="10" value="<?php echo $quantity; ?>" name="table_rate_price[quantity][]"/> and above
										</td>
										<td>
											<input type="text" size="10" value="<?php echo $table_price; ?>" name="table_rate_price[table_price][]" />
										</td>
										<td><img src="<?php echo WPSC_URL; ?>/images/cross.png" class="remove_line" /></td>
									</tr>
									<?php
								}
							}
						}
						?>						
						<tr>
							<td><input type='text' size='10' value='' name='table_rate_price[quantity][]'/> and above</td>
							<td><input type='text' size='10' value='' name='table_rate_price[table_price][]'/></td>
						</tr>
          </table>
        </div>
      </td>
    </tr>
    
     <tr>
      <td>
        <input type='checkbox' value='1' name='meta[_wpsc_product_metadata][custom_tax][state]' id='custom_tax_checkbox'  <?php echo ((is_numeric($product_meta['custom_tax']) > 0) ? 'checked=\'checked\'' : ''); ?>  />
        <label for='custom_tax_checkbox'><?php echo _e("Custom Tax Rate",'wpsc'); ?></label>
        <div style='display:<?php echo ((is_numeric($product_meta['custom_tax'])) ? 'block' : 'none'); ?>;' id='custom_tax'>
					<input type='text' size='10' value='<?php echo number_format($product_meta['custom_tax'], 2, '.', ''); ?>' name='meta[_wpsc_product_metadata][custom_tax][value]'/>
        </div>
      </td>
    </tr>


    
    <?php
    echo "
    <tr>
      <td style='width:430px;'>
      <input class='limited_stock_checkbox' id='add_form_quantity_limited' type='checkbox' value='yes' ".((is_numeric($product_data['meta']['_wpsc_stock'])) ? 'checked="checked"' : '')." name='meta[_wpsc_limited_stock]'/>";
       //onclick='hideelement(\"add_stock\")'
		echo "&nbsp;<label for='add_form_quantity_limited' class='small'>".__('I have a limited number of this item in stock. If the stock runs out, this product will not be available on the shop unless you untick this box or add more stock.', 'wpsc')."</label>";
		if ($product_data['id'] > 0){
			if(is_numeric($product_data['meta']['_wpsc_stock'])) {
				echo "            <div class='edit_stock' style='display: block;'>\n\r";
			} else {
				echo "            <div class='edit_stock' style='display: none;'>\n\r";
			}
						
			echo __('Stock Qty', 'wpsc') . " <input type='text' class='stock_limit_quantity' name='meta[_wpsc_stock]' size='10' value='".$product_data['meta']['_wpsc_stock']."' />";
			
			
			
			echo "<div style='font-size:9px; padding:5px;'><input type='checkbox' " . (($product_meta['unpublish_when_none_left'] == 1) ? 'checked="checked"' : '') . " class='inform_when_oos' name='meta[_wpsc_product_metadata][unpublish_when_none_left]' /> " . __('If this product runs out of stock set status to Unpublished & email site owner', 'wpsc') . "</div>";
			echo "              </div>\n\r";
	} else {
						echo "
					<div style='display: none;' class='edit_stock'>
						" .__('Stock Qty', 'wpsc') . " <input type='text' name='meta[_wpsc_stock]' value='0' size='10' />";
						echo "<div style='font-size:9px; padding:5px;'><input type='checkbox' class='inform_when_oos' name='meta[_wpsc_product_metadata][unpublish_when_none_left]' /> " . __('If this product runs out of stock set status to Unpublished & email site owner', 'wpsc') . "</div>";
					echo "</div>";  
			}
	echo "
				
				</td>
			</tr>";
	echo "
		</table>
	</div>
</div>";

//return $output;

}

function wpsc_product_variation_forms($product_data=''){
	global $closed_postboxes, $variations_processor;
	$siteurl = get_option('siteurl');
	$output='';
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	
	$product_term_data = wp_get_object_terms($product_data['id'], 'wpsc-variation');
	$product_terms = array();
	foreach($product_term_data as $product_term) {
		$product_terms[] = $product_term->term_id;
	}
	
	?>
	
	<div id='wpsc_product_variation_forms' class='postbox <?php echo ((array_search('wpsc_product_variation_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '');	?>' <?php echo ((array_search('wpsc_product_variation_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : ''); ?>><div class="handlediv" title="Click to toggle"><br></div>
		<h3 class='hndle'><?php echo __('Variation Control', 'wpsc'); ?></h3>
		
		<div class='inside'>
			<strong><?php echo __('Add Variation Set', 'wpsc'); ?></strong>
			<h4 class='product_action_link'><a target='_blank' href='admin.php?page=wpsc-edit-variations'><?php echo __('+ Add New Variations', 'wpsc'); ?></a></h4>
			<br />
			<div id="product_variations">
				<div class="variation_checkboxes">
					<?php
					$variation_sets = get_terms('wpsc-variation', array(
						'hide_empty' => 0,
						'parent' => 0
					));
					foreach((array)$variation_sets as $variation_set) {
						$set_checked_state = '';
						if(in_array($variation_set->term_id, $product_terms)) {
							$set_checked_state = "checked='checked'";
						}
						//$product_terms
						?>
						<div class="variation_set">						
							<label class='set_label'>
								<input type="checkbox" <?php echo $set_checked_state; ?> name="variations[<?php echo $variation_set->term_id; ?>]" value="1">
								<?php echo $variation_set->name; ?>
							</label>
							<?php
							$variations = get_terms('wpsc-variation', array(
								'hide_empty' => 0,
								'parent' => $variation_set->term_id
							));
							foreach((array)$variations as $variation) {
								$checked_state = '';
								if(in_array($variation->term_id, $product_terms)) {
									$checked_state = "checked='checked'";
								}
								?>
								<div class="variation">
									<label>
										<input type="checkbox" <?php echo $checked_state; ?> name="edit_var_val[<?php echo $variation_set->term_id; ?>][<?php echo $variation->term_id; ?>]" value="1">
										<?php echo $variation->name; ?>
									</label>
								</div>
								<?php
							}
							?>
								
						</div>
						<?php
					}
					?>
					
					
				</div>
			</div>
			
			<a href='<?php echo add_query_arg(array('page'=>'wpsc-edit-products', 'parent_product'=> $product_data['id']), "admin.php"); ?>'><?php _e('Edit Variations Products', 'wpsc'); ?></a>
			
		</div>
	</div>
	<?php 
}
/**
* Adding function to change text for media buttons
*/

function change_context() {
	return __('Upload Image%s');
}

function change_link($product_data='') {
//Since we're hacking this all together anyway until the complete integration of custom_post_types, we'll go ahead and add the attachment to the db here as well.
	$uploading_iframe_ID = $_GET["product"]; 
	$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";		
	return $media_upload_iframe_src."&amp;type=image";
}
	$uploading_iframe_ID = $_GET["product"]; 

	//Justin Sainton - 5.19.2010 - Adding filters/actions for the media goodness :) Conditions important to not kill media functionality elsewhere
if ( $_GET["page"] == "wpsc-edit-products" ) {
	add_filter('media_buttons_context','change_context');
	add_filter('image_upload_iframe_src', "change_link");
}
function wpsc_product_shipping_forms($product_data=''){
	global $closed_postboxes;
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div class='postbox ".((array_search('wpsc_product_shipping_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_shipping_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '')." id='wpsc_product_shipping_forms'><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

    	if (IS_WP27) {
    		$output .= "<h3 class='hndle'>";
    	} else {
    		$output .= "<h3>
			<a class='togbox'>+</a>";
    	}
		$output .= __('Shipping Details', 'wpsc');
		$output .= "
		</h3>
      <div class='inside'>
  <table>

  	  <!--USPS shipping changes-->
	<tr>
		<td>
			".__('Weight', 'wpsc')."
		</td>
		<td>
			<input type='text' size='5' name='meta[_wpsc_product_metadata][weight]' value='".$product_data['transformed']['weight']."' />
			<select name='meta[_wpsc_product_metadata][weight_unit]'>
				<option value='pound' ". (($product_meta['display_weight_as'] == 'pound') ? 'selected="selected"' : '') .">Pounds</option>
				<option value='ounce' ". ((preg_match("/o(u)?nce/",$product_meta['display_weight_as'])) ? 'selected="selected"' : '') .">Ounces</option>
				<option value='gram' ". (($product_meta['display_weight_as'] == 'gram') ? 'selected="selected"' : '') .">Grams</option>
				<option value='kilogram' ". (($product_meta['display_weight_as'] == 'kilogram') ? 'selected="selected"' : '') .">Kilograms</option>
			</select>
		</td>
    </tr>
      <!--dimension-->
    <tr>
		<td>
			Height
		</td>
		<td>
			<input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][height]' value='".$product_meta['dimensions']['height']."'>
			<select name='meta[_wpsc_product_metadata][dimensions][height_unit]'>
				<option value='in' ". (($product_meta['dimensions']['height_unit'] == 'in') ? 'selected' : '') .">inches</option>
				<option value='cm' ". (($product_meta['dimensions']['height_unit'] == 'cm') ? 'selected' : '') .">cm</option>
				<option value='meter' ". (($product_meta['dimensions']['height_unit'] == 'meter') ? 'selected' : '') .">meter</option>
			</select>
			</td>
			</tr>
			<tr>
		<td>
			Width
		</td>
		<td>
			<input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][width]' value='".$product_meta['dimensions']['width']."'>
			<select name='meta[_wpsc_product_metadata][dimensions][width_unit]'>
				<option value='in' ". (($product_meta['dimensions']['width_unit'] == 'in') ? 'selected' : '') .">inches</option>
				<option value='cm' ". (($product_meta['dimensions']['width_unit'] == 'cm') ? 'selected' : '') .">cm</option>
				<option value='meter' ". (($product_meta['dimensions']['width_unit'] == 'meter') ? 'selected' : '') .">meter</option>
			</select>
			</td>
			</tr>
			<tr>
		<td>
			Length
		</td>
		<td>
			<input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][length]' value='".$product_meta['dimensions']['length']."'>
			<select name='meta[_wpsc_product_metadata][dimensions][length_unit]'>
				<option value='in' ". (($product_meta['dimensions']['length_unit'] == 'in') ? 'selected' : '') .">inches</option>
				<option value='cm' ". (($product_meta['dimensions']['length_unit'] == 'cm') ? 'selected' : '') .">cm</option>
				<option value='meter' ". (($product_meta['dimensions']['length_unit'] == 'meter') ? 'selected' : '') .">meter</option>
			</select>
			</td>
			</tr>

    <!--//dimension-->


    <!--USPS shipping changes ends-->


    <!--USPS shipping changes ends-->

 
    <tr>
      <td colspan='2'>
      <strong>".__('Flat Rate Settings', 'wpsc')."</strong> 
      </td>
    </tr>
    <tr>
      <td>
      ".__('Local Shipping Fee', 'wpsc')." 
      </td>
      <td>
        <input type='text' size='10' name='meta[_wpsc_product_metadata][shipping][local]' value='".number_format($product_meta['shipping']['local'], 2, '.', '')."' />
      </td>
    </tr>
  
    <tr>
      <td>
      ".__('International Shipping Fee', 'wpsc')."
      </td>
      <td>
        <input type='text' size='10' name='meta[_wpsc_product_metadata][shipping][international]' value='".number_format($product_meta['shipping']['international'], 2, '.', '')."' />
      </td>
    </tr>
    <tr>
   		<td>
   		<br />
          <input id='add_form_no_shipping' type='checkbox' name='meta[_wpsc_product_metadata][no_shipping]' value='1' ".(($product_meta['no_shipping'] == 1) ? 'checked="checked"' : '')."/>&nbsp;<label for='add_form_no_shipping'>".__('Disregard Shipping for this product', 'wpsc')."</label>
       </td>
    </tr>
    </table></div></div>";
    
    return $output;
}

function wpsc_product_advanced_forms($product_data='') {
	global $closed_postboxes,$wpdb;
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];
	
	$custom_fields = $wpdb->get_results( "
	SELECT `meta_id`, `meta_key`, `meta_value`
	FROM `{$wpdb->postmeta}`
	WHERE `post_id` = {$product_data['id']}
	AND `meta_key` NOT LIKE '\_%'
	ORDER BY LOWER(meta_key)", ARRAY_A);
	
	
	$output ='';
	
	//$custom_fields =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` IN('{$product_data['id']}') AND `custom` IN('1') ",ARRAY_A);


	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div id='wpsc_product_advanced_forms' class='postbox ".((array_search('wpsc_product_advanced_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_advanced_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '')."><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

		$output .= "<h3 class='hndle'>";
		$output .= __('Advanced Options', 'wpsc');
		$output .= "
	    </h3>
	    <div class='inside'>
	    <table>";
	$output .= "
	<tr>
		<td colspan='2' class='itemfirstcol'>
		  <strong>".__('Custom Meta', 'wpsc').":</strong><br />
			<a href='#' class='add_more_meta' onclick='return add_more_meta(this)'> + ".__('Add Custom Meta', 'wpsc')."</a><br /><br />
		";
		foreach((array)$custom_fields as $custom_field) {
			$i = $custom_field['meta_id'];
			// for editing, the container needs an id, I can find no other tidyish method of passing a way to target this object through an ajax request
			$output .= "
			<div class='product_custom_meta'  id='custom_meta_$i'>
				".__('Name', 'wpsc')."
				<input type='text' class='text'  value='{$custom_field['meta_key']}' name='custom_meta[$i][name]' id='custom_meta_name_$i'>
				
				".__('Value', 'wpsc')."
				<textarea class='text' name='custom_meta[$i][value]' id='custom_meta_value_$i'>{$custom_field['meta_value']}</textarea>
				<a href='#' class='remove_meta' onclick='return remove_meta(this, $i)'>".__('Delete')."</a>
				<br />
			</div>
			";
		}
		
		$output .= "<div class='product_custom_meta'>
		".__('Name', 'wpsc').": <br />
		<input type='text' name='new_custom_meta[name][]' value='' class='text'/><br />
		
		".__('Description', 'wpsc').": <br />
		<textarea name='new_custom_meta[value][]' cols='40' rows='10' class='text' ></textarea>
		<br /></div></td></tr>";
		
	    $output .= "<tr>
      <td class='itemfirstcol' colspan='2'><br /> <strong>". __('Merchant Notes', 'wpsc') .":</strong><br />
      
        <textarea cols='40' rows='3' name='meta[_wpsc_product_metadata][merchant_notes]' id='merchant_notes'>".stripslashes($product_meta['merchant_notes'])."</textarea> 
      	<small>".__('These notes are only available here.', 'wpsc')."</small>
      </td>
    </tr>";

		$output .="
		<tr>
      <td class='itemfirstcol' colspan='2'><br />
       <strong>". __('Personalisation Options', 'wpsc') .":</strong><br />
        <input type='hidden' name='meta[_wpsc_product_metadata][engraved]' value='0' />
        <input type='checkbox' name='meta[_wpsc_product_metadata][engraved]' ".(($product_meta['engraved'] == true) ? 'checked="checked"' : '')." id='add_engrave_text' />
        <label for='add_engrave_text'> ".__('Users can personalize this product by leaving a message on single product page', 'wpsc')."</label>
        <br />
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol' colspan='2'>
      
        <input type='hidden' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' value='0' />
        <input type='checkbox' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' ".(($product_meta['can_have_uploaded_image'] == true) ? 'checked="checked"' : '')." id='can_have_uploaded_image' />
        <label for='can_have_uploaded_image'> ".__('Users can upload images on single product page to purchase logs.', 'wpsc')."</label>
        <br />
      </td>
    </tr>";
	
    
    if(get_option('payment_gateway') == 'google') {
		$output .= "<tr>
      <td class='itemfirstcol' colspan='2'>
      
        <input type='checkbox' ".$product_meta['google_prohibited']." name='meta[_wpsc_product_metadata][google_prohibited]' id='add_google_prohibited' /> <label for='add_google_prohibited'>
       ".__('Prohibited', 'wpsc')."
	 <a href='http://checkout.google.com/support/sell/bin/answer.py?answer=75724'>by Google?</a></label><br />
      </td>
    </tr>";
    }

  	ob_start();
  	do_action('wpsc_add_advanced_options', $product_data['id']);
  	$output .= ob_get_contents();
  	ob_end_clean();
	
	$output .= "
	<tr>
      <td class='itemfirstcol' colspan='2'><br />
       <strong>".__('Off Site Product Link', 'wpsc').":</strong><br />
       <small>".__('If this product is for sale on another website enter the link here. For instance if your product is an MP3 file for sale on itunes you could put the link here. This option over rides the buy now and add to cart links and takes you to the site linked here.', 'wpsc')."</small><br /><br />
		<label for='external_link'>".__('External Link', 'wpsc')."</label>:<br />
		  <input type='text' class='text' name='meta[_wpsc_product_metadata][external_link]' value='".$product_meta['external_link']."' id='external_link' size='40' /> 
      </td>
    </tr>";
	//if (get_option('wpsc_enable_comments') == 1) {
		$output .= "
		<tr>
			<td class='itemfirstcol' colspan='2'><br />
				<strong>".__('Enable Comments', 'wpsc').":</strong><br />
			<select name='meta[_wpsc_product_metadata][enable_comments]'>
				<option value='' ".  (($product_meta['enable_comments'] == '' ) ? 'selected' : '') .">Use Default</option>
				<option value='1' ". (($product_meta['enable_comments'] == '1') ? 'selected' : '') .">Yes</option>
				<option value='0' ". (($product_meta['enable_comments'] == '0') ? 'selected' : '') .">No</option>
			</select>
			<br/>".__('Allow users to comment on this product.', 'wpsc')."
			</td>
		</tr>";
	//}
	$output .= "
    </table></div></div>";
	return $output;
}

function wpsc_product_image_forms($product_data='') {
	global $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}

	 	//echo "<pre>".print_r($product_data,true)."</pre>";
/* Removing all this goodness in place of the Upload Image Above - justin - 5.20
	//As in WordPress,  If Mac and mod_security, no Flash
	$flash = true;
	if ( (false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac')) && apache_mod_loaded('mod_security') ) {
		$flash = false;
	}
	
	$flash_action_url = admin_url('async-upload.php');
	$flash = apply_filters('flash_uploader', $flash);
*/
?>
	<div id='wpsc_product_image_forms' class='postbox <?php echo ((array_search('wpsc_product_image_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : ''); ?>' <?php echo ((array_search('wpsc_product_image_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : ''); ?> ><div class="handlediv" title="Click to toggle"><br></div>
		<h3 class='hndle'> <?php echo	__('Product Images', 'wpsc'); ?></h3>
		<div class='inside'>
 			<p><strong <?php echo $display; ?>><a href="media-upload.php?post_id=<?php echo $product_data['id']; ?>&type=image&tab=gallery&TB_iframe=1&width=640&height=566" class="thickbox" title="Manage your images"><?php echo __('Manage your thumbnails', 'wpsc');?></a></strong></p>
			<?php
			edit_multiple_image_gallery($product_data);
			?>

		</div>
		<div style='clear:both'></div>
	</div>
	<?php
  return $output;
}

function wpsc_product_download_forms($product_data='') {
	global $wpdb, $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}

	$output ='';
 	$upload_max = wpsc_get_max_upload_size();
 	$output .= "<div id='wpsc_product_download_forms' class='postbox ".((array_search('wpsc_product_download_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : '')."' ".((array_search('wpsc_product_download_forms', $product_data['hidden_postboxes']) !== false) ? 'style="display: none;"' : '')."><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";
 	
	$output .= "<h3 class='hndle'>".__('Product Downloads', 'wpsc')."</h3>";
	$output .= "<div class='inside'>";
	
	$output .= wpsc_select_product_file($product_data['id']);

	$output .= "<h4>".__('Upload New File', 'wpsc').":</h4>";
	$output .= "<input type='file' name='file' value='' /><br />".__('Max Upload Size', 'wpsc')." : <span>".$upload_max."</span><br /><br />";
	$output .= "<h4>".__('<a href="admin.php?wpsc_admin_action=product_files_existing&product_id='.$product_data['id'].'" class="thickbox" title="Select from all product files for '.$product_data['name'].'">Select from existing files</a>', 'wpsc')."</h4>";

	if($product_data['file'] > 0) {
    	$output .= __('Preview File', 'wpsc').": ";
    	
    	$output .= "<a class='admin_download' href='index.php?admin_preview=true&product_id=".$product_data['id']."' ><img align='absmiddle' src='".WPSC_URL."/images/download.gif' alt='' title='' /><span>".__('Click to download', 'wpsc')."</span></a>";
		
    	$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_data['file']."' LIMIT 1",ARRAY_A);
    	if(($file_data != null) && (function_exists('listen_button'))) {
    	  $output .= "".listen_button($file_data['idhash'], $file_data['id'])."<br style='clear: both;' /><br />";
    	}
    }
	if(function_exists("make_mp3_preview") || function_exists("wpsc_media_player")) {    

	$output .= "<br />";
    $output .="<h4>".__("Select an MP3 file to upload as a preview")."</h4>";
	
		$output .= "<input type='file' name='preview_file' value='' /><br />";
		$output .= "<br />";
	}
	$output .="</div></div>";
	return $output;
}

function wpsc_product_label_forms() {
	global $closed_postboxes;
	?>
	<div id='wpsc_product_label_forms' class='postbox <?php echo ((array_search('wpsc_product_label_forms', $product_data['closed_postboxes']) !== false) ? 'closed' : ''); ?>'><div class="handlediv" title="Click to toggle"><br></div>
		<?php
    	if (function_exists('add_object_page')) {
    		echo "<h3 class='hndle'>";
    	} else {
    		echo "<h3>
		<a class='togbox'>+</a>";
    	}
    ?> 
		<?php echo __('Label Control', 'wpsc'); ?>
	</h3>
	<div class='inside'>
    <table>
    <tr>
      <td colspan='2'>
        <?php echo __('Add Label', 'wpsc'); ?> :
      	<a id='add_label'><?php echo __('Add Label', 'wpsc'); ?></a>
      </td>
    </tr> 
    <tr>
      <td colspan='2'>
      <div id="labels">
        <table>
        	<tr>
        		<td><?php _e('Label', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[labels][]"></td>
        	</tr>
        	<tr>
        		<td><?php _e('Label Description', 'wpsc')?> :</td>
        		<td><textarea name="productmeta_values[labels_desc][]"></textarea></td>
        	</tr>
        	<tr>
        		<td><?php _e('Life Number', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[life_number][]"></td>
        	</tr>
        	<tr>
        		<td><?php _e('Item Number', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[item_number][]"></td>
        	</tr>
        	<tr>
        		<td><?php _e('Product Code', 'wpsc')?> :</td>
        		<td><input type="text" name="productmeta_values[product_code][]"></td>
        	</tr>
        	<tr>
        		<td><?php _e('PDF', 'wpsc')?> :</td>
        		<td><input type="file" name="pdf[]"></td>
        	</tr>
        </table>
        </div>
      </td>
    </tr> 
	</table></div></div>
	<?php
}


function edit_multiple_image_gallery($product_data) {
	global $wpdb;
	$siteurl = get_option('siteurl');
	?>
	<ul id="gallery_list" class="ui-sortable" style="position: relative;">
	<?php
	if($product_data['id'] > 0) {
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $product_data['id'],
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);
		
		$attached_images = (array)get_posts($args);
		if($attached_images != null) {
			foreach($attached_images as $image) {
				$image_meta = get_post_meta($image->ID, '');
				foreach($image_meta as $meta_name => $meta_value) {
					$image_meta[$meta_name] = maybe_unserialize(array_pop($meta_value));
				}

				if(function_exists("getimagesize")) {
					$num++;
					$image_url = "index.php?wpsc_action=scale_image&amp;attachment_id={$image->ID}&amp;width=60&amp;height=60";
					?>
					<li id="product_image_<?php echo $image->ID; ?>" class='gallery_image'>
						<input type='hidden' class='image-id'  name='gallery_image_id[]' value='<?php echo $image->ID; ?>' />
						<div class='previewimage' id='gallery_image_<?php echo $image->ID; ?>'>
							<a id='extra_preview_link_<?php echo $image->ID; ?>' onclick='return false;' href='' rel='product_extra_image_<?php echo $image->ID; ?>' >
								<img class='previewimage' src='<?php echo $image_url; ?>' alt='<?php echo __('Preview', 'wpsc'); ?>' title='<?php echo __('Preview', 'wpsc'); ?>' /><br />
							</a>
							<?php // echo wpsc_main_product_image_menu($product_data['id']); ?>
						</div>
					</li>
					<?php
				}
			}
		}
		/*
		$main_image = $wpdb->get_row("SELECT `images`.*
		FROM `".WPSC_TABLE_PRODUCT_IMAGES."` AS `images`
		JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `product`
		ON `product`.`image` = `images`.`id`
		WHERE `product`.`id` = '{$product_data['id']}'
		LIMIT 1", ARRAY_A);
		*/
	}
	?>
	</ul>
	<?php
}

/*
function wpsc_main_product_image_menu($product_id) {
  global $wpdb;
  $thumbnail_state = 0;
	if($product_id > 0) {
		//$main_image = $wpdb->get_row("SELECT `images`.*,  `product`.`thumbnail_state` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` AS `images` JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `product` ON `product`.`image` = `images`.`id`  WHERE `product`.`id` = '{$product_id}' LIMIT 1", ARRAY_A);
		$thumbnail_state = $main_image['thumbnail_state'];
	} else {
		$thumbnail_state = 1;
	}
	
	$sendback = wp_get_referer();
	$presentation_link = add_query_arg('page','wpsc-settings', $sendback);
	$presentation_link = add_query_arg('tab','presentation#thumb_settings', $presentation_link);
	$thumbnail_image_height = get_product_meta($product_id, 'thumbnail_height');
	$thumbnail_image_width = get_product_meta($product_id, 'thumbnail_width');



// 	echo $thumbnail_image_height;
// 	echo "|";
// 	echo $thumbnail_image_width;
	ob_start();
	?>
	<div class='image_settings_box'>
		<div class='upper_settings_box'>
			<div class='upper_image'><img src='<?php echo WPSC_URL; ?>/images/pencil.png' alt='' /></div>
			<div class='upper_txt'><?php _e('Thumbnail Settings'); ?><a class='closeimagesettings'>X</a></div>
		</div>

		<div class='lower_settings_box'>
			<input type='hidden' id='current_thumbnail_image' name='current_thumbnail_image' value='S' />
			<ul>		

				<li>
					<input type='radio' name='gallery_resize' value='1' id='gallery_resize1' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize1'><?php echo __('use default size', 'wpsc'); ?>(<a href='<?php echo $presentation_link; ?>' title='<?php echo __('This is set on the Settings Page', 'wpsc'); ?>'><?php echo get_option('product_image_height'); ?>&times;<?php echo get_option('product_image_width'); ?>px</a>)
					</label>

				</li>
				
				<li>
					<input type='radio' <?php echo (($thumbnail_state != 2) ? "checked='checked'" : "") ;?> name='gallery_resize' value='0' id='gallery_resize0' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize0'> <?php echo __('do not resize thumbnail image', 'wpsc'); ?></label><br />
				</li>
				
				<li>
					<input type='radio' <?php echo (($thumbnail_state == 2) ? "checked='checked'" : "") ;?>  name='gallery_resize' value='2' id='gallery_resize2' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize2'><?php echo __('use specific size', 'wpsc'); ?> </label>
					<div class='heightWidth image_resize_extra_forms' <?php echo (($thumbnail_state == 2) ? "style='display: block;'" : "") ;?>>
						<input id='gallery_image_width' type='text' size='4' name='gallery_width' value='<?php echo $thumbnail_image_width; ?>' /><label for='gallery_image_width'><?php echo __('px width', 'wpsc'); ?></label>
						<input id='gallery_image_height' type='text' size='4' name='gallery_height' value='<?php echo $thumbnail_image_height; ?>' /><label for='gallery_image_height'><?php echo __('px height', 'wpsc'); ?> </label>
					</div>
				</li>

				<li>
					<input type='radio'  name='gallery_resize' value='3' id='gallery_resize3' class='image_resize'  onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize3'> <?php echo __('use separate thumbnail', 'wpsc'); ?></label><br />
					<div class='browseThumb image_resize_extra_forms'>
						<input type='file' name='gallery_thumbnailImage' size='15' value='' />
					</div>
				</li>
				<li>
				<a href='<?php echo htmlentities("admin.php?wpsc_admin_action=crop_image&imagename=".$main_image['image']."&imgheight=".$image_data[1]."&imgwidth=".$image_data[0]."&width=630&height=500&product_id=".$product_id); ?>' title='Crop Image' class='thickbox'>Crop This Image Using jCrop</a>

				</li>
				<li>
					<a href='#' class='delete_primary_image delete_button'>Delete this Image</a>
				</li>

			</ul>
		</div>
	</div>
	<a class='editButton'>Edit   <img src='<?php echo WPSC_URL; ?>/images/pencil.png' alt='' /></a>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
} /*

  /**
	* Displays the category forms for adding and editing products
	* Recurses to generate the branched view for subcategories
	*/
function wpsc_category_list(&$product_data, $group_id, $unique_id = '', $category_id = null) {
	global $wpdb;
    static $iteration = 0;
    $iteration++;	
	
	if(is_numeric($category_id)) {
		$values = get_terms('wpsc_product_category', "hide_empty=0&parent=".$category_id, ARRAY_A);
	} else {
	    $values = get_terms('wpsc_product_category', "hide_empty=0&parent=".$group_id, ARRAY_A);
	}
	
	if($category_id < 1) {
		$output .= "<ul class='list:category categorychecklist form-no-clear'>\n\r";
	} elseif((count($values) >0)){
		$output .= "<ul class='children'>\n\r";
	}
		
	//echo "<pre>".print_r($values, true)."</pre>";

	foreach((array)$values as $option) {
		$option=(array)$option;
		
		if(count($product_data['category_ids']) > 0) {
			if(in_array($option['term_id'], $product_data['category_ids'])) {
			    $selected = "checked='checked'";
			}
		}
			
		$output .= "  <li id='category-".$option['term_id']."'>\n\r";
		$output .= "    <label class='selectit'>\n\r";
		$output .= "    <input id='".$unique_id."category_form_".$option['term_id']."' type='checkbox' {$selected} name='category[]' value='".$option['term_id']."' /></label>\n\r";
		
		$output .= "    <label for='".$unique_id."category_form_".$option['term_id']."' class='greytext' >".stripslashes($option['name'])."</label>\n\r";
		$output .= wpsc_category_list($product_data, $group_id, $unique_id, $option['term_id']);
		
		$output .= "  </li>\n\r";
		
		$selected = "";
	}
	if((count($values) >0) ){
		$output .= "</ul>\n\r";
	}
	return $output;
}

/**
 * Slightly modified copy of the Wordpress the_editor function
 *
 *  We have to use a modified version because the wordpress one calls javascript that uses document.write
 *  When this javascript runs after being loaded through AJAX, it replaces the whole page.
 *
 * The amount of rows the text area will have for the content has to be between
 * 3 and 100 or will default at 12. There is only one option used for all users,
 * named 'default_post_edit_rows'.
 *
 * If the user can not use the rich editor (TinyMCE), then the switch button
 * will not be displayed.
 *
 * @since 3.7
 *
 * @param string $content Textarea content.
 * @param string $id HTML ID attribute value.
 * @param string $prev_id HTML ID name for switching back and forth between visual editors.
 * @param bool $media_buttons Optional, default is true. Whether to display media buttons.
 * @param int $tab_index Optional, default is 2. Tabindex for textarea element.
 */
function wpsc_the_editor($content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2) {
	$rows = get_option('default_post_edit_rows');
	if (($rows < 3) || ($rows > 100))
		$rows = 12;

	if ( !current_user_can( 'upload_files' ) )
		$media_buttons = false;

	$richedit =  user_can_richedit();
	$class = '';

	if ( $richedit || $media_buttons ) {
 ?>
	<div id="editor-toolbar">
<?php
	if ( $richedit ) {
		$wp_default_editor = wp_default_editor(); ?>
		<div class="zerosize"><input accesskey="e" type="button" onclick="switchEditors.go('<?php echo $id; ?>')" /></div>
<?php	if ( 'html' == $wp_default_editor ) {
			add_filter('the_editor_content', 'wp_htmledit_pre'); ?>
			<a id="edButtonHTML" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e('HTML'); ?></a>
			<a id="edButtonPreview" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e('Visual'); ?></a>
<?php	} else {
			$class = " class='theEditor'";
			add_filter('the_editor_content', 'wp_richedit_pre'); ?>
			<a id="edButtonHTML" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e('HTML'); ?></a>
			<a id="edButtonPreview" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e('Visual'); ?></a>
<?php	}
	}

	if ( $media_buttons ) { ?>
		<div id="media-buttons" class="hide-if-no-js">
<?php	do_action( 'media_buttons' ); ?>
		</div>
<?php
	} ?>
	</div>
<?php
	}
?>
	<div id="quicktags"><?php
	wp_print_scripts( 'quicktags' ); ?>
	  <div id="ed_toolbar">
		</div>
		<script type="text/javascript" defer="defer'">wpsc_edToolbar()</script>

	</div>

<?php
	$the_editor = apply_filters('the_editor', "<div id='editorcontainer'><textarea rows='$rows'$class cols='40' name='$id' tabindex='$tab_index' id='$id'>%s</textarea></div>\n");
	$the_editor_content = apply_filters('the_editor_content', $content);

	printf($the_editor, $the_editor_content);

?>
	<script type="text/javascript">
	edCanvas = document.getElementById('<?php echo $id; ?>');
	</script>
<?php
	}
?>