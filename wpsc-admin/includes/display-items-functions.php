<?php
/**
 * WPSC Product form generation functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
$closed_postboxes = (array)get_usermeta( $current_user->ID, 'closedpostboxes_products');
$variations_processor = new nzshpcrt_variations;


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








function wpsc_display_product_form ($product_id = 0) {
  global $wpdb, $wpsc_product_defaults;
  $product_id = absint($product_id);
	$variations_processor = new nzshpcrt_variations;
  if($product_id > 0) {

		$product_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`={$product_id} LIMIT 1",ARRAY_A);

		$product_data['meta']['external_link'] = get_product_meta($product_id,'external_link',true);
		$product_data['meta']['merchant_notes'] = get_product_meta($product_id,'merchant_notes',true);
		$product_data['meta']['sku'] = get_product_meta($product_id,'sku',true);
		
		$product_data['meta']['engrave'] = get_product_meta($product_id,'engraved',true);
		$product_data['meta']['can_have_uploaded_image'] = get_product_meta($product_id,'can_have_uploaded_image',true);
		
		$product_data['meta']['table_rate_price'] = get_product_meta($product_id,'table_rate_price',true);
				
		if(function_exists('wp_insert_term')) {
			$term_relationships = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_relationships WHERE object_id = {$product_id}", ARRAY_A);
			
			foreach ((array)$term_relationships as $term_relationship) {
				$tt_ids[] = $term_relationship['term_taxonomy_id'];
			}
			foreach ((array)$tt_ids as $tt_id) {
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_taxonomy WHERE term_taxonomy_id = ".$tt_id." AND taxonomy = 'product_tag'", ARRAY_A);
				$term_ids[] = $results[0]['term_id'];
			}
			foreach ((array)$term_ids as $term_id ) {
				if ($term_id != NULL){
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."terms WHERE term_id=".$term_id." ",ARRAY_A);
				$tags[] = $results[0]['name'];
				}
			}
			if ($tags != NULL){ 
				$imtags = implode(',', $tags);
			}
		}
	
		$check_variation_value_count = $wpdb->get_var("SELECT COUNT(*) as `count` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` = '{$product_id}'");
		
		$current_user = wp_get_current_user();
		$closed_postboxes = (array)get_usermeta( $current_user->ID, 'closedpostboxes_editproduct');
  } else {
		$product_data =$wpsc_product_defaults;
  }
  
  if(count($product_data) > 0) {
		wpsc_product_basic_details_form($product_data);
  }
}









function wpsc_product_basic_details_form(&$product_data) {
  global $wpdb,$nzshpcrt_imagesize_info;
  ?>
	<h3 class='hndle'><?php echo  TXT_WPSC_PRODUCTDETAILS; ?> <?php echo TXT_WPSC_ENTERPRODUCTDETAILSHERE; ?></h3>
	<div>
		<table class='product_editform' style='width:100%;'>
			<tr>
				<td colspan='2' class='itemfirstcol'>  
					<div class='admin_product_name'>
						<input class='wpsc_product_name' size='30' type='text' class='text'  name='title' value='<?php echo htmlentities(stripslashes($product_data['name']), ENT_QUOTES, 'UTF-8'); ?>' />
										<a href='#' class='shorttag_toggle'></a>
										<div class='admin_product_shorttags'>
										<h4>Shortcodes</h4>
				
											<dl>
												<dt><?php echo TXT_WPSC_DISPLAY_PRODUCT_SHORTCODE; ?>: </dt><dd> [wpsc_products product_id='<?php echo $product_data['id'];?>']</dd>
												<dt><?php echo TXT_WPSC_BUY_NOW_SHORTCODE; ?>: </dt><dd>[buy_now_button=<?php echo $product_data['id'];?>]</dd>
												<dt><?php echo TXT_WPSC_ADD_TO_CART_SHORTCODE; ?>: </dt><dd>[add_to_cart=<?php echo $product_data['id'];?>]</dd>
											</dl>
				
										<h4>Template Tags</h4>
					
											<dl>
												<dt><?php echo TXT_WPSC_DISPLAY_PRODUCT_TEMPLATE_TAG; ?>: </dt><dd> &lt;?php echo wpsc_display_products('product_id=<?php echo $product_data['id'];?>'); ?&gt;</dd>
												<dt><?php echo TXT_WPSC_BUY_NOW_PHP; ?>: </dt><dd>&lt;?php echo wpsc_buy_now_button(<?php echo $product_data['id'];?>); ?&gt;</dd>
												<dt><?php echo TXT_WPSC_ADD_TO_CART_PHP; ?>: </dt><dd>&lt;?php echo wpsc_add_to_cart_button(<?php echo $product_data['id'];?>); ?&gt;</dd>
											</dl>
					
											<p>
				
											</p>
										</div>
							<div style='clear:both; height: 0px;'></div>	
					</div>
					
				</td>
			</tr>
		
		
			<tr>
				<td  class='skuandprice'>
					<?php echo TXT_WPSC_SKU_FULL; ?> :<br />
					<input size='30' type='text' class='text'  name='productmeta_values[sku]' value='<?php echo htmlentities(stripslashes($product_data['meta']['sku']), ENT_QUOTES, 'UTF-8'); ?>' />
				</td>
				<td  class='skuandprice'>
					<?php echo TXT_WPSC_PRICE; ?> :<br />
					<input type='text' class='text' size='30' name='price' value='<?php echo $product_data['price']; ?>'>
				</td>
			</tr>
		
			<tr>
				<td colspan='2'>
					<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
				 <?php
				 the_editor($product_data['description'], 'content', false, false);
				 ?>
				 </div>
				 <?php
					 /*<div id='editorcontainer'>
						<textarea name='description' class='mceEditor' cols='40' rows='8' ><?php echo stripslashes($product_data['description']); ?></textarea>
					</div>*/
				 ?>
				</td>
			</tr>
		
			<tr>
				<td class='itemfirstcol' colspan='2'>
					<strong ><?php echo TXT_WPSC_ADDITIONALDESCRIPTION; ?> :</strong><br />			
					<textarea name='additional_description' cols='40' rows='8' ><?php echo stripslashes($product_data['additional_description']); ?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div class='meta-box-sortables'>
		<?php
	
	// 	$order = get_option('wpsc_product_page_order');
	// 	if (($order == '') || (count($order ) < 6)){
				$order=array("wpsc_product_category_and_tag", "wpsc_product_price_and_stock", "wpsc_product_shipping", "wpsc_product_variation", "wpsc_product_advanced", "wpsc_product_image", "wpsc_product_download");
	// 	}
		
		update_option('wpsc_product_page_order', $order);
		foreach((array)$order as $key => $forms) {
			$box_function_name = $forms."_forms";
			if(function_exists($box_function_name)) {
				echo call_user_func($box_function_name,$product_data);
			}
		}
		do_action('wpsc_product_form', $product_data['id']);
		?>
	</div>

	<input type='hidden' name='product_id' id='product_id' value='<?php echo $product_data['id']; ?>' />
	<input type='hidden' name='wpsc_admin_action' value='edit_product' />
	<?php wp_nonce_field('edit-product'); ?>
	<input type='hidden' name='submit_action' value='edit' />
	
	<input class='button-primary' style='float:left;'  type='submit' name='submit' value='<?php echo TXT_WPSC_EDIT_PRODUCT; ?>' />&nbsp;
	<a class='delete_button' ' href='admin.php?page=<?php echo WPSC_DIR_NAME; ?>/display-items.php&amp;deleteid=<?php echo $product_data['id']; ?>' onclick="return conf();" ><?php echo TXT_WPSC_DELETE_PRODUCT; ?></a>
	<?php
  }














function wpsc_product_category_and_tag_forms($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor;
	$output = '';
	if ($product_data == 'empty') {
		$display = "style='visibility:hidden;'";
	}
	$output .= "<div id='category_and_tag' class=' postbox ".((array_search('category_and_tag', $closed_postboxes) !== false) ? 'closed' : '')."' >";

    if (IS_WP27) {
        $output .= "<h3 class='hndle'>";
    } else {
        $output .= "<h3>
	    <a class='togbox'>+</a>";
    }
    $output .= TXT_WPSC_CATEGORY_AND_TAG_CONTROL;
    if ($product_data != '') {
    	if(function_exists('wp_insert_term')) {
				$term_relationships = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_relationships WHERE object_id = ".$product_data['id'], ARRAY_A);
				
				foreach ((array)$term_relationships as $term_relationship) {
					$tt_ids[] = $term_relationship['term_taxonomy_id'];
				}
				foreach ((array)$tt_ids as $tt_id) {
					$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_taxonomy WHERE term_taxonomy_id = ".$tt_id." AND taxonomy = 'product_tag'", ARRAY_A);
					$term_ids[] = $results[0]['term_id'];
				}
				foreach ((array)$term_ids as $term_id ) {
					if ($term_id != NULL){
					$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."terms WHERE term_id=".$term_id." ",ARRAY_A);
					$tags[] = $results[0]['name'];
					}
				}
				if ($tags != NULL){ 
					$imtags = implode(',', $tags);
				}
  		}
  	}
    $output .= "
	</h3>
    <div class='inside'>
    <table>";
    $output .= "<tr>
      <td class='itemfirstcol'>
			".TXT_WPSC_CATEGORISATION.": <br />";
        
         $categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')", ARRAY_A);
					foreach($categorisation_groups as $categorisation_group){
					  $category_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('{$categorisation_group['id']}')");
					  if($category_count > 0) {
							$output .= "<p>";
						  $category_group_name = str_replace("[categorisation]", $categorisation_group['name'], TXT_WPSC_PRODUCT_CATEGORIES);
						  $output .= "<strong>".$category_group_name.":</strong><br />";
						  if ($product_data == '')
						  	$output .= categorylist($categorisation_group['id'], false, 'add_');
						  else 
						  	$output .= categorylist($categorisation_group['id'], $product_data['id'], 'edit_');
						  $output .= "</p>";
						}
					}

     $output .= "</td>
     <td class='itemfirstcol product_tags'>
       ".TXT_WPSC_PRODUCT_TAGS.":<br />
        <input type='text' class='text wpsc_tag' value='".$imtags."' name='product_tags' id='product_tag'><br /><span class='small_italic'>".__("These values are comma separated")."</span>
      </td>
    </tr>";
    
$output .= "
  </table>
 </div>
</div>";

return $output;

}
function wpsc_product_price_and_stock_forms($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor;
	$table_rate_price = get_product_meta($product['id'], 'table_rate_price');
	$output = '';
	if ($product_data == 'empty') {
		$display = "style='visibility:hidden;'";
	}
	$output .= "<div id='price_and_stock' class='price_and_stock postbox ".((array_search('price_and_stock', $closed_postboxes) !== false) ? 'closed' : '')."' >";

    if (IS_WP27) {
        $output .= "<h3 class='hndle'>";
    } else {
        $output .= "<h3>
	    <a class='togbox'>+</a>";
    }
    $output .= TXT_WPSC_PRICE_AND_STOCK_CONTROL;
    $output .= "
	</h3>
    <div class='inside'>
    <table>
    <!--<tr>
      <td>
       ".TXT_WPSC_PRICE.":&nbsp;<input type='text' size='10' name='price' value='".$product_data['price']."' />
      </td>
    </tr>-->
    <tr>
       <td>
          <input id='add_form_tax' type='checkbox' name='notax' value='yes' ".(($product_data['notax'] == 1) ? 'checked="true"' : '')."/>&nbsp;<label for='add_form_tax'>".TXT_WPSC_TAXALREADYINCLUDED."</label>
       </td>
    </tr>
    <tr>

       <td>
          <input id='add_form_donation' type='checkbox' name='donation' value='yes' ".(($product_data['donation'] == 1) ? 'checked="true"' : '').">&nbsp;<label for='add_form_donation'>".TXT_WPSC_IS_DONATION."</label>
       </td>
    </tr>
    <tr>

       <td>
          <input id='add_form_no_shipping' type='checkbox' name='no_shipping' value='yes' ".(($product_data['no_shipping'] == 1) ? 'checked="true"' : '')."/>&nbsp;<label for='add_form_no_shipping'>".TXT_WPSC_NO_SHIPPING."</label>
       </td>
    </tr>
    <tr>
      <td>
        <input type='checkbox' onclick='hideelement(\"add_special\")' value='yes' name='special' id='add_form_special' ".(($product_data['special'] == 1) ? 'checked="true"' : '')."/>
        <label for='add_form_special'>".TXT_WPSC_SPECIAL."</label>
        <div style='display:".(($product_data['special'] == 1) ? 'block' : 'none').";' id='add_special'>
          <input type='text' size='10' value='".($product_data['price'] - $product_data['special_price'])."' name='special_price'/>
        </div>
      </td>
    </tr>
     <tr>
      <td>
        <input type='checkbox' value='yes' name='table_rate_price' id='table_rate_price'/>
        <label for='table_rate_price'>".TXT_WPSC_TABLE_RATED_PRICE."</label>
        <div style='display:".(($table_rate_price != '') ? 'block' : 'none').";' id='table_rate'>
          <a class='add_level' style='cursor:pointer;'>Add level</a><br />
          <table>
          <tr><td>".TXT_WPSC_QUANTITY."</td><td>".TXT_WPSC_PRICE."</td></tr>";
          
          	if ($table_rate_price != '') {
          		foreach($table_rate_price['quantity'] as $key => $qty) {
					if ($qty != '')
						$output .= '<tr><td><input type="text" size="10" value="'.$qty.'" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="'.$table_rate_price['table_price'][$key].'" name="productmeta_values[table_rate_price][table_price][]"/></td><td><img src="'.WPSC_URL.'/images/cross.png" class="remove_line"></td></tr>';
				}
			}
          
          $output .= "<tr><td><input type='text' size='10' value='' name='productmeta_values[table_rate_price][quantity][]'/> and above</td><td><input type='text' size='10' value='' name='productmeta_values[table_rate_price][table_price][]'/></td></tr>
          </table>
        </div>
      </td>
    </tr>
    <tr>
      <td style='width:430px;'>
      <input class='limited_stock_checkbox' id='add_form_quantity_limited' type='checkbox' value='yes'".(($product_data['quantity_limited'] == 1) ? 'checked="true"' : '')."name='quantity_limited'/>"; //onclick='hideelement(\"add_stock\")'
	$output .= "&nbsp;<label for='add_form_quantity_limited' class='small'>".TXT_WPSC_UNTICKBOX."</label>";
	if ($product_data != ''){
      	$variations_output = $variations_processor->variations_grid_view($product_data['id']); 
  		if($variations_output != '') {
  	    	$output .= "<div class='edit_stock' style='display: none;'>\n\r";
  	    	
  	    	$output .= "<input type='hidden' name='quantity' value='".$product_data['quantity']."' />";
  	    	$output .= "</div>\n\r";
  	    } else {
  	    	switch($product_data['quantity_limited']) {
  	    		case 1:
  	    		$output .= "            <div class='edit_stock' style='display: block;'>\n\r";
  	    		break;
  	    		
  	    		default:
  	    		$output .= "            <div class='edit_stock' style='display: none;'>\n\r";
  	    		break;
  	    	}
  	    	
  	    	$output .= "<input type='text' name='quantity' size='10' value='".$product_data['quantity']."' />";
  	    	$output .= "              </div>\n\r";
  	    }
	} else {
  	  		$output .= "
        <div style='display: none;' class='edit_stock'>
          <input type='text' name='quantity' value='0' size='10' />
        </div>";  
  	}
$output .= "
      
      </td>
    </tr>
  </table>
 </div>
</div>";

return $output;

}

function wpsc_product_variation_forms($product_data=''){
	global $closed_postboxes, $variations_processor;
	$siteurl = get_option('siteurl');
	$output='';
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	?>
	
	<div id='variation' class='postbox <?php echo ((array_search('variation', $closed_postboxes) !== false) ? 'closed' : '');	?>'>
		<h3 class='hndle'><?php echo TXT_WPSC_VARIATION_CONTROL; ?></h3>
		
		<div class='inside'>
			<strong><?php echo TXT_WPSC_ADD_VAR; ?></strong>
			<h4 class='product_action_link'><a class='thickbox' href='admin.php?thickbox_variations=true&amp;width=550&amp;TB_iframe=true'><?php echo TXT_WPSC_ADD_NEW_VARIATIONS; ?></a></h4>
			<br />
			
			<?php 
			if ($product_data['id'] > 0) { ?>
				<div id='edit_product_variations'>
					<?php echo $variations_processor->list_variations($product_data['id']); ?>
				</div>
				<div id='edit_variations_container'>
					<?php echo $variations_processor->variations_grid_view($product_data['id']); ?>
				</div>
			<?php } else { ?>
					<div id='add_product_variations'>
						<?php echo $variations_processor->list_variations($product_data['id']); ?>
					</div>
					<div id='add_product_variation_details'>
					</div>
			<?php
			} ?>
		</div>
	</div>
	<?php 
}

function wpsc_product_shipping_forms($product_data=''){
	global $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div class='postbox ".((array_search('shipping', $closed_postboxes) !== false) ? 'closed' : '')."' id='shipping'>";

    	if (IS_WP27) {
    		$output .= "<h3 class='hndle'>";
    	} else {
    		$output .= "<h3>
			<a class='togbox'>+</a>";
    	}
		$output .= TXT_WPSC_SHIPPING_DETAILS;
		$output .= "
		</h3>
      <div class='inside'>
  <table>
  
  	  <!--USPS shipping changes-->
	<tr>
		<td>
			".TXT_WPSC_WEIGHT."
		</td>
		<td>
			<input type='text' size='5' name='weight' value='".$product_data['weight']."'>
			<select name='weight_unit'>
				<option value='pound' ". (($product_data['weight_unit'] == 'pound') ? 'selected' : '') .">Pounds</option>
				<option value='once' ". (($product_data['weight_unit'] == 'once') ? 'selected' : '') .">Ounces</option>
				<option value='gram' ". (($product_data['weight_unit'] == 'gram') ? 'selected' : '') .">Grams</option>
				<option value='kilogram' ". (($product_data['weight_unit'] == 'kilogram') ? 'selected' : '') .">Kilograms</option>
			</select>
		</td>
    </tr>
    <!--USPS shipping changes ends-->

    <tr>
    <tr>
      <td colspan='2'>
      <strong>".TXT_WPSC_FLAT_RATE_SETTINGS."</strong> 
      </td>
    </tr>
    <tr>
      <td>
      ".TXT_WPSC_LOCAL_PNP." 
      </td>
      <td>
        <input type='text' size='10' name='pnp' value='".$product_data['pnp']."' />
      </td>
    </tr>
  
    <tr>
      <td>
      ".TXT_WPSC_INTERNATIONAL_PNP."
      </td>
      <td>
        <input type='text' size='10' name='international_pnp' value='".$product_data['international_pnp']."' />
      </td>
    </tr>
    </table></div></div>";
    
    return $output;
}

function wpsc_product_advanced_forms($product_data='') {
	global $closed_postboxes,$wpdb;
	$merchant_note = $product_data['meta']['metchant_notes'];
	$engraved_text = $product_data['meta']['engraved'];
	$can_have_uploaded_image = $product_data['meta']['can_have_uploaded_image'];
	$external_link = $product_data['meta']['external_link'];
	$enable_comments = $product_data['meta']['enable_comments'];
	
	
	$output ='';
	
	$custom_fields =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` IN('{$product_data['id']}') AND `custom` IN('1') ",ARRAY_A);


	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div id='advanced' class='postbox ".((array_search('advanced', $closed_postboxes) !== false) ? 'closed' : '')."'>";

		$output .= "<h3 class='hndle'>";
		$output .= TXT_WPSC_ADVANCED_OPTIONS;
		$output .= "
	    </h3>
	    <div class='inside'>
	    <table>";
	$output .= "
	<tr>
		<td colspan='2' class='itemfirstcol'>
		  <strong>".TXT_WPSC_CUSTOM_META.":</strong><br />
			<a href='#' class='add_more_meta' onclick='return add_more_meta(this)'> + ".TXT_WPSC_ADD_CUSTOM_FIELD."</a><br /><br />
		";
		foreach((array)$custom_fields as $custom_field) {
			$i = $custom_field['id'];
			// for editing, the container needs an id, I can find no other tidyish method of passing a way to target this object through an ajax request
			$output .= "
			<div class='product_custom_meta'  id='custom_meta_$i'>
				".TXT_WPSC_NAME."
				<input type='text' class='text'  value='{$custom_field['meta_key']}' name='custom_meta[$i][name]' id='custom_meta_name_$i'>
				
				".TXT_WPSC_VALUE."
				<textarea class='text' name='custom_meta[$i][value]' id='custom_meta_value_$i'>{$custom_field['meta_value']}</textarea>
				<a href='#' class='remove_meta' onclick='return remove_meta(this, $i)'>".__('Delete')."</a>
				<br />
			</div>
			";
		}
		
		$output .= "<div class='product_custom_meta'>
		".TXT_WPSC_NAME.": <br />
		<input type='text' name='new_custom_meta[name][]' value='' class='text'/><br />
		
		".TXT_WPSC_DESCRIPTION.": <br />
		<textarea name='new_custom_meta[value][]' value='' class='text' ></textarea>
		<br /></td></tr>";
		
	    $output .= "<tr>
      <td class='itemfirstcol' colspan='2'><br /> <strong>". TXT_WPSC_ADMINNOTES .":</strong><br />
      
        <textarea cols='40' rows='3' type='text' name='productmeta_value[merchant_notes]' id='merchant_notes'>".stripslashes($merchant_note)."</textarea> 
      	<small>".TXT_WPSC_NOTE_ONLY_AVAILABLE_HERE."</small>
      </td>
    </tr>
	
    <tr>
      <td class='itemfirstcol' colspan='2'><br />
       <strong>". TXT_WPSC_PERSONALISATION_OPTIONS .":</strong><br />
        <input type='hidden' name='productmeta_values[engraved]' id='add_engrave_text' value='0'>
        <input type='checkbox' name='productmeta_values[engraved]' ".(($engraved_text == 'on') ? 'checked="true"' : '')." id='add_engrave_text'>
        <label for='add_engrave_text'> ".TXT_WPSC_ADMIN_ENGRAVE."</label>
        <br />
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol' colspan='2'>
      
        <input type='hidden' name='productmeta_values[can_have_uploaded_image]' id='add_engrave_text' value='0'>
        <input type='checkbox' name='productmeta_values[can_have_uploaded_image]' ".(($can_have_uploaded_image == 'on') ? 'checked="true"' : '')." id='can_have_uploaded_image'>
        <label for='can_have_uploaded_image'> ".TXT_WPSC_ALLOW_UPLOADING_IMAGE."</label>
        <br />
      </td>
    </tr>";
	
    
    if(get_option('payment_gateway') == 'google') {
		$output .= "<tr>
      <td class='itemfirstcol' colspan='2'>
      
        <input type='checkbox' name='productmeta_values[google_prohibited]' id='add_google_prohibited' /> <label for='add_google_prohibited'>
       ".TXT_WPSC_PROHIBITED."
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
       <strong>".TXT_WPSC_OFF_SITE_LINK.":</strong><br />
       <small>".TXT_WPSC_USEONLYEXTERNALLINK."</small><br /><br />
		<label for='external_link'>".TXT_WPSC_EXTERNALLINK."</label>:<br />
		  <input type='text' class='text' name='productmeta_values[external_link]' value='".$external_link."' id='external_link' size='40'> 
      </td>
    </tr>";
	if (get_option('wpsc_enable_comments') == 1) {
		$output .= "
		<tr>
			<td class='itemfirstcol' colspan='2'><br />
				<strong>".TXT_WPSC_PRODUCT_ENABLE_COMMENTS.":</strong><br />
			<select name='productmeta_values[enable_comments]'>
				<option value='' ". (($enable_comments == '') ? 'selected' : '') .">Use Default</option>
				<option value='yes' ". (($enable_comments == 'yes') ? 'selected' : '') .">Yes</option>
				<option value='no' ". (($enable_comments == 'no') ? 'selected' : '') .">No</option>
			</select>
			<br/>".TXT_WPSC_PRODUCT_ENABLE_COMMENTS_INFO."
			</td>
		</tr>";
	}
	$output .= "
    </table></div></div>";
	return $output;
}

function wpsc_product_image_forms($product_data='') {
	global $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	?>
	<div id='product_image' class='postbox <?php echo ((array_search('product_image', $closed_postboxes) !== false) ? 'closed' : ''); ?>'>
		<h3 class='hndle'> <?php echo	TXT_WPSC_PRODUCTIMAGES; ?></h3>
		<div class='inside'>
		
		
		  <div class='browser-image-uploader'>
				<h4><?php _e("Select an image to upload:"); ?></h4>
				<ul>  
					<li>
						<input type="file" value="" name="image" />
					</li>
					<li>
						<input type="radio" onclick='hideOptionElement(null, "image_resize0");' class="image_resize" id="add_image_resize0" value="0" name="image_resize"/> <label for="add_image_resize0">do not resize thumbnail image</label>
					</li>
					<li>
						<input type="radio" onclick='hideOptionElement(null, "image_resize1");' class="image_resize" id="add_image_resize1" value="1" name="image_resize" checked="true"/> <label for="add_image_resize1">use default size(<abbr title="This is set on the Settings Page">96×96px</abbr>) </label>
					</li>
					<li>
						<input type="radio" onclick='hideOptionElement("heightWidth", "image_resize2");' class="image_resize" id="add_image_resize2" value="2" name="image_resize"/>
						<label for="add_image_resize2">use specific size</label>        
						<div style="display: none;" id="heightWidth">
							<input type="text" value="" name="width" size="4"/><label for="add_image_resize2">px width</label>
							<input type="text" value="" name="height" size="4"/><label for="add_image_resize2">px height</label>
						</div>
					</li>
					<li>
						<input type="radio" onclick='hideOptionElement("browseThumb", "image_resize3");' class="image_resize" id="add_image_resize3" value="3" name="image_resize"/>
						<label for="add_image_resize3">use separate thumbnail</label><br/>
						<div style="display: none;" id="browseThumb">
							<input type="file" value="" name="thumbnailImage"/>
						</div>
					</li>
				</ul>
				
				<div id="add_additional_images"> </div>
				<a class="add_additional_image" onclick='add_image_upload_forms("add_");return false;' href="">Add Additional Image</a>
			</div>
			
			<?php
			edit_multiple_image_gallery($product_data);
			?>
			
			
			


			
<!-- 					<p>You are using the Browser uploader.  Problems?  Try the <a onclick='wpsc_upload_switcher("flash")' class="wpsc_upload_switcher">Flash uploader</a> instead.</p> -->

		</div>
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

 	$output .= "<div id='product_download' class='postbox ".((array_search('product_download', $closed_postboxes) !== false) ? 'closed' : '')."'>";
    if (IS_WP27) {
        $output .= "<h3 class='hndle'>";
    } else {
        $output .= "<h3>
	<a class='togbox'>+</a>";
    }
    $output .= TXT_WPSC_PRODUCTDOWNLOAD;
	$output .= "</h3>
	<div class='inside'>
	<table>
    <tr>
      <td>
        ".TXT_WPSC_DOWNLOADABLEPRODUCT.":
      </td>
      <td>
        <input type='file' name='file' value='' /><br />
        ".wpsc_select_product_file($product_data['id'])."
        <br />
      </td>
    </tr>";
	if($product_data['file'] > 0) {
    	$output .= "          <tr>\n\r";
    	$output .= "            <td>\n\r";
    	$output .= TXT_WPSC_PREVIEW_FILE.": ";
    	$output .= "            </td>\n\r";
    	$output .= "            <td>\n\r";    
    	
    	$output .= "<a class='admin_download' href='index.php?admin_preview=true&product_id=".$product_data['id']."' style='float: left;' ><img align='absmiddle' src='".WPSC_URL."/images/download.gif' alt='' title='' /><span>".TXT_WPSC_CLICKTODOWNLOAD."</span></a>";
		
    	$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_data['file']."' LIMIT 1",ARRAY_A);
    	if(($file_data != null) && (function_exists('listen_button'))) {
    	  $output .= "".listen_button($file_data['idhash'], $file_data['id']);
    	}
    	  
    	$output .= "            </td>\n\r";
    	$output .= "          </tr>\n\r";
    }
    $output .="</table>";
	if(function_exists("make_mp3_preview") || function_exists("wpsc_media_player")) {    
    $output .="<h4>".__("Select an MP3 file to upload as a preview")."</h4>";
	
    $output .="<table>";
		$output .= "    <tr>\n\r";
		$output .= "      <td>\n\r";
		$output .= TXT_WPSC_PREVIEW_FILE.": ";
		$output .= "      </td>\n\r";
		$output .= "      <td>\n\r";
		$output .= "<input type='file' name='preview_file' value='' /><br />";
		$output .= "<br />";
		$output .= "<br />";
		$output .= "      </td>\n\r";
		$output .= "    </tr>\n\r";
    $output .="</table>";
	}
	$output .="</div></div>";
	return $output;
}

function wpsc_product_label_forms() {
	global $closed_postboxes;
	?>
	<div id='product_label' class='postbox <?php echo ((array_search('variation', $closed_postboxes) !== false) ? 'closed' : ''); ?>'>
		<?php
    	if (function_exists('add_object_page')) {
    		echo "<h3 class='hndle'>";
    	} else {
    		echo "<h3>
		<a class='togbox'>+</a>";
    	}
    ?> 
		<?php echo TXT_WPSC_LABEL_CONTROL; ?>
	</h3>
	<div class='inside'>
    <table>
    <tr>
      <td colspan='2'>
        <?php echo TXT_WPSC_LABELS; ?> :
      	<a id='add_label'><?php echo TXT_WPSC_LABELS; ?></a>
      </td>
    </tr> 
    <tr>
      <td colspan='2'>
      <div id="labels">
        <table>
        	<tr>
        		<td><?=TXT_WPSC_LABEL?> :</td>
        		<td><input type="text" name="productmeta_values[labels][]"></td>
        	</tr>
        	<tr>
        		<td><?=TXT_WPSC_LABEL_DESC?> :</td>
        		<td><textarea name="productmeta_values[labels_desc][]"></textarea></td>
        	</tr>
        	<tr>
        		<td><?=TXT_WPSC_LIFE_NUMBER?> :</td>
        		<td><input type="text" name="productmeta_values[life_number][]"></td>
        	</tr>
        	<tr>
        		<td><?=TXT_WPSC_ITEM_NUMBER?> :</td>
        		<td><input type="text" name="productmeta_values[item_number][]"></td>
        	</tr>
        	<tr>
        		<td><?=TXT_WPSC_PRODUCT_CODE?> :</td>
        		<td><input type="text" name="productmeta_values[product_code][]"></td>
        	</tr>
        	<tr>
        		<td><?=TXT_WPSC_PDF?> :</td>
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
	//$main_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1");
	$timestamp = time();
	
	?>
	<ul id="gallery_list" class="ui-sortable" style="position: relative;">
	
		
		<li class='first' id='0'>
			<div class='previewimage' id='gallery_image_0'>
				<?php if ($product_data['image'] != '') { ?><a id='extra_preview_link_0' href='<?php echo WPSC_IMAGE_URL.$product_data['image']; ?>' rel='product_extra_image_0' class='thickbox'><img class='previewimage' src='<?php echo WPSC_IMAGE_URL.$product_data['image']; ?>' alt='<?php echo TXT_WPSC_PREVIEW; ?>' title='<?php echo TXT_WPSC_PREVIEW; ?>' /></a>
				<?php } ?>
				
				
				<div id='image_settings_box'>
					<div class='upper_settings_box'>
						<div class='upper_image'><img src='<?php echo WPSC_URL; ?>/images/pencil.png'/></div>
						<div class='upper_txt'><?php _e('Thumbnail Settings'); ?><a class='closeimagesettings'>X</a></div>
					</div>
				
					<div class='lower_settings_box'>
						<input type='hidden' id='current_thumbnail_image' name='current_thumbnail_image' value='<?php echo $product_data['thumbnail_image']; ?>' />
					  <ul>
					    <li>
								<input type='radio' checked='true' name='gallery_resize' value='0' id='gallery_resize0' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize0'> <?php echo TXT_WPSC_DONOTRESIZEIMAGE; ?><br />
							</li>
							
					    <li>
								<input type='radio' name='gallery_resize' value='1' id='gallery_resize1' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize1'><?php echo TXT_WPSC_USEDEFAULTSIZE; ?>(<abbr title='<?php echo TXT_WPSC_SETONSETTINGS; ?>'><?php echo get_option('product_image_height'); ?>&times;<?php echo get_option('product_image_width'); ?>px</abbr>)
								</label>
					    </li>
							
					    <li>
								<input type='radio'  name='gallery_resize' value='2' id='gallery_resize2' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize2'><?php echo TXT_WPSC_USESPECIFICSIZE; ?> </label>
								<div class='heightWidth image_resize_extra_forms'>
									<input id='gallery_image_width' type='text' size='4' name='gallery_width' value='' /><label for='gallery_image_resize2'><?php echo TXT_WPSC_PXWIDTH; ?></label>
									<input id='gallery_image_height' type='text' size='4' name='gallery_height' value='' /><label for='gallery_image_resize2'><?php echo TXT_WPSC_PXHEIGHT; ?> </label>
								</div>
					    </li>
							
					    <li>
								<input type='radio'  name='gallery_resize' value='3' id='gallery_resize3' class='image_resize'  onclick='image_resize_extra_forms(this)' /> <label for='gallery_resize3'> <?php echo TXT_WPSC_SEPARATETHUMBNAIL; ?></label><br />
								<div class='browseThumb image_resize_extra_forms'>
									<input type='file' name='gallery_thumbnailImage' size='15' value='' />
								</div>
							</li>
					    <li>
								<a href='#' class='delete_primary_image'>Delete this Image</a>
					    </li>
						</ul>
					</div>
				</div>
				<a class='editButton'>Edit   <img src='<?php echo WPSC_URL; ?>/images/pencil.png'/></a>
			</div>
		</li>
	</ul>
	<?php
	$num = 0;
	if(function_exists('gold_shpcrt_display_gallery')) {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `product_id` = '{$product_data['id']}' ORDER BY image_order ASC",ARRAY_A);
    if($values != null) {
      foreach($values as $image) {
        if(function_exists("getimagesize")) {
          if($image['image'] != '') {
            $num++;
            $imagepath = WPSC_IMAGE_DIR . $image['image'];
            include('getimagesize.php');
            $output .= "<li id=".$image['id'].">";
            //  $output .= $image['image'];
            $output .= "<div class='previewimage' id='gallery_image_{$image['id']}'><a id='extra_preview_link_".$image['id']."' href='".WPSC_IMAGE_URL.$image['image']."' rel='product_extra_image_".$image['id']."' class='thickbox'><img class='previewimage' src='".WPSC_IMAGE_URL.$image['image']."' alt='".TXT_WPSC_PREVIEW."' title='".TXT_WPSC_PREVIEW."' /></a>";
            $output .= "<img alt='-' class='deleteButton' src='".WPSC_URL."/images/cross.png'/>";
            $output .= "</div>";
            $output .= "</li>";
          }
        }
      }
    }
  }
  //return $output;
}




function wpsc_meta_forms(){
	add_meta_forms('category_and_tag', 'Category and Tags', 'wpsc_category_and_tag_forms', WPSC_DIR_NAME.'/display-items', 'normal', 'high');
	add_meta_forms('price_and_stock', 'Price and Stock', 'wpsc_price_and_stock_forms', WPSC_DIR_NAME.'/display-items', 'normal', 'high');
	add_meta_forms('variation', 'Variations', 'wpsc_variation_forms', WPSC_DIR_NAME.'/display-items', 'normal', 'high');
	add_meta_forms('shipping', 'Shipping', 'wpsc_shipping_forms', WPSC_DIR_NAME.'/display-items', 'normal', 'high');
	add_meta_forms('advanced', 'Advanced Settings', 'wpsc_advanced_forms', WPSC_DIR_NAME.'/display-items', 'normal', 'high');
	add_meta_forms('product_download', 'Product Download', 'wpsc_product_download_forms', WPSC_DIR_NAME.'/display-items', 'normal', 'high');
	add_meta_forms('product_image', 'Product Images', 'wpsc_product_image_forms', WPSC_DIR_NAME.'/display-items', 'normal', 'high');
}

//add_action('admin_menu', 'wpsc_meta_forms');

?>