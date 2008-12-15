<?php

$closed_postboxes = (array)get_usermeta( $current_user->ID, 'closedpostboxes_products');

$variations_processor = new nzshpcrt_variations;

function category_and_tag_box($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor;
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
			".TXT_WPSC_CATEGORISATION.": <br>";
        
         $categorisation_groups =  $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_categorisation_groups` WHERE `active` IN ('1')", ARRAY_A);
					foreach($categorisation_groups as $categorisation_group){
					  $category_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}product_categories` WHERE `group_id` IN ('{$categorisation_group['id']}')");
					  if($category_count > 0) {
							$output .= "<p>";
						  $category_group_name = str_replace("[categorisation]", $categorisation_group['name'], TXT_WPSC_PRODUCT_CATEGORIES);
						  $output .= "<strong>".$category_group_name.":</strong><br>";
						  if ($product_data == '')
						  	$output .= categorylist($categorisation_group['id'], false, 'add_');
						  else 
						  	$output .= categorylist($categorisation_group['id'], $product_data['id'], 'edit_');
						  $output .= "</p>";
						}
					}

     $output .= "</td>
     <td class='itemfirstcol'>
       ".TXT_WPSC_PRODUCT_TAGS.":<br>
        <input type='text' class='text wpsc_tag' value='".$imtags."' name='product_tags' id='product_tag'><br /><span class='small_italic'>Seperate with commas</span>
      </td>
    </tr>";
    
$output .= "
  </table>
 </div>
</div>";

return $output;

}
function price_and_stock_box($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor;
	$table_rate_price = get_product_meta($product['id'], 'table_rate_price');
	$table_rate_price = $table_rate_price[0];
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
          <input id='add_form_donation' type='checkbox' name='donation' value='yes' ".(($product_data['donation'] == 1) ? 'checked="true"' : '')."&nbsp;<label for='add_form_donation'>".TXT_WPSC_IS_DONATION."</label>
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
          <a class='add_level' style='cursor:pointer;'>Add level</a><br>
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
      <input id='add_form_quantity_limited' type='checkbox' onclick='hideelement(\"add_stock\")' value='yes'".(($product_data['quantity_limited'] == 1) ? 'checked="true"' : '')."name='quantity_limited'/>";
	$output .= "<label for='add_form_quantity_limited' class='small'>".TXT_WPSC_UNTICKBOX."</label>";
	if ($product_data != ''){
      	$variations_output = $variations_processor->variations_grid_view($product_data['id']); 
  		if($variations_output != '') {
  	    	$output .= "<div id='edit_stock' style='display: none;'>\n\r";
  	    	
  	    	$output .= "<input type='hidden' name='quantity' value='".$product_data['quantity']."' />";
  	    	$output .= "</div>\n\r";
  	    } else {
  	    	switch($product_data['quantity_limited']) {
  	    		case 1:
  	    		$output .= "            <div id='edit_stock' style='display: block;'>\n\r";
  	    		break;
  	    		
  	    		default:
  	    		$output .= "            <div id='edit_stock' style='display: none;'>\n\r";
  	    		break;
  	    	}
  	    	
  	    	$output .= "<input type='text' name='quantity' size='10' value='".$product_data['quantity']."' />";
  	    	$output .= "              </div>\n\r";
  	    }
	} else {
  	  		$output .= "
        <div style='display: none;' id='add_stock'>
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

function variation_box($product_data=''){
	global $closed_postboxes, $variations_processor;
	$siteurl = get_option('siteurl');
	$output='';
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div id='variation' class='postbox ".((array_search('variation', $closed_postboxes) !== false) ? 'closed' : '')."'>";

    if (IS_WP27) {
        $output .= "<h3 class='hndle'>";
    } else {
        $output .= "<h3>
	<a class='togbox'>+</a>";
    }
    $output .= TXT_WPSC_VARIATION_CONTROL;
    $output .= "
	</h3>
	<div class='inside'>
    <table>
    <tr>
      <td colspan='2'>";
    if ($variations_processor->list_variations($product_data['id']) == '') {
        $output .= "<a class='thickbox' href='$siteurl/?thickbox_variations=true&width=550&TB_iframe=true'>Add New Variations</a>";
    } else {
        $output .= TXT_WPSC_ADD_VAR."  <a class='thickbox' href='$siteurl/?thickbox_variations=true&width=550&TB_iframe=true'>Add New Variations</a>"; 
    }
    $output .="
      </td>
    </tr> 
    <tr>
      <td colspan='2'>
        ";
    if ($product_data!=''){
    	$output .= "<div id='edit_product_variations'>
		".$variations_processor->list_variations($product_data['id'])."
        </div>
        <div id='edit_variations_container'>";
        $output .= $variations_processor->variations_grid_view($product_data['id']);
        $output .= "</div>";
    } else {
        $output .= "<div id='add_product_variations'>
		".$variations_processor->list_variations($product_data['id'])."
        </div>
        <div id='add_product_variation_details'>
        </div>";
    }
      $output .= "</td>
    </tr> 
	</table></div></div>";
	
	return $output;

}

function shipping_box($product_data=''){
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

function advanced_box($product_data='') {
	global $closed_postboxes,$wpdb;
	$merchant_note = get_product_meta($product_data['id'], 'merchant_notes');
	$merchant_note = $merchant_note[0];
	$engraved_text = get_product_meta($product_data['id'], 'engraved');
	$engraved_text = $engraved_text[0];
	$can_have_uploaded_image = get_product_meta($product_data['id'], 'can_have_uploaded_image');
	$can_have_uploaded_image = $can_have_uploaded_image[0];
	$external_link = get_product_meta($product_data['id'], 'external_link');
	$external_link = $external_link[0];
	$output ='';
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output .= "<div id='advanced' class='postbox ".((array_search('advanced', $closed_postboxes) !== false) ? 'closed' : '')."'>";

    	if (IS_WP27) {
    		$output .= "<h3 class='hndle'>";
    	} else {
    		$output .= "<h3>
		<a class='togbox'>+</a>";
    	}
		$output .= TXT_WPSC_ADVANCED_OPTIONS;
		$output .= "
	    </h3>
	    <div class='inside'>
	    <table>";
	$output .= "
	<tr>
		<td colspan='2' class='itemfirstcol'>
			<a href='#' style='font-style:normal;border-bottom:1px solid;' class='add_more_meta' onclick='return add_more_meta(this)'> + ".TXT_WPSC_ADD_CUSTOM_FIELD."</a><br><br>
		";
		foreach((array)$custom_fields as $custom_field) {
			$i = $custom_field['id'];
			// for editing, the container needs an id, I can find no other tidyish method of passing a way to target this object through an ajax request
			$output .= "
			<div class='product_custom_meta'  id='custom_meta_$i'>
				".TXT_WPSC_NAME."
				<input type='text' class='text'  value='{$custom_field['meta_key']}' name='custom_meta[$i][name]' id='custom_meta_name_$i'>
				
				".TXT_WPSC_VALUE."
				<textarea class='text'  value='{$custom_field['meta_value']}' name='custom_meta[$i][value]' id='custom_meta_value_$i'></textarea>
				<a href='#' class='remove_meta' onclick='return remove_meta(this, $i)'>&ndash;</a>
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
      <td class='itemfirstcol' colspan='2'> ". TXT_WPSC_ADMINNOTES .":<br>
      
        <textarea cols='40' rows='3' type='text' name='productmeta_value[merchant_notes]' id='merchant_notes'>".stripslashes($merchant_note)."</textarea> 
      	<small>".TXT_WPSC_NOTE_ONLY_AVAILABLE_HERE."</small>
      </td>
    </tr>
	
    <tr>
      <td class='itemfirstcol' colspan='2'>
      
        <input type='checkbox' name='productmeta_values[engraved]' ".(($engraved_text == 'on') ? 'checked="true"' : '')." id='add_engrave_text'>
        <label for='add_engrave_text'> ".TXT_WPSC_ENGRAVE."</label>
        <br />
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol' colspan='2'>
      
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
	$custom_fields =  $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_productmeta` WHERE `product_id` IN('{$product['id']}') AND `custom` IN('1') ",ARRAY_A);
	
	$output .= "
	<tr>
      <td class='itemfirstcol' colspan='2'>
       ".TXT_WPSC_OFF_SITE_LINK.":<br><br>
       <small>".TXT_WPSC_USEONLYEXTERNALLINK."</small><br><br>
		<label for='external_link'>".TXT_WPSC_EXTERNALLINK."</label>:<br>
		  <input type='text' class='text' name='productmeta_values[external_link]' value='".$external_link."' id='external_link' size='40'> 
      </td>
    </tr>";
	
  
	$output .= "
    </table></div></div>";
    
    return $output;
}

function product_image_box($product_data='') {
	global $closed_postboxes;
	if ($product_data == 'empty') {
		$display = "style='display:none;'";
	}
	$output = "<div id='product_image' class='postbox ".((array_search('product_image', $closed_postboxes) !== false) ? 'closed' : '')."'>";
  
    if (IS_WP27) {
        $output .= "<h3 class='hndle'>";
    } else {
        $output .= "<h3>
	    <a class='togbox'>+</a>";
    }
       $output .= TXT_WPSC_PRODUCTIMAGES;
    
	if ($product_data != '') {
		$output .="</h3>";
		$output .=	  "<div class='inside'>";
    	
    	
  		$output .= "  <table width='100%' class='flash-image-uploader'>";
  		$output .= "    <td>";
  		$output .= "      <td>";
  		
  		$output .= '      <span id=\'spanButtonPlaceholder\'></span>';
  		$output .= '      <button id="add-product-image" name="add-image" class="button-secondary" type="button"><small>Add New Image</small></button>';
			
  		$output .= "      <p>".TXT_WPSC_FLASH_UPLOADER."</p>";
  		$output .= "      </td>";
  		$output .= "    </tr>";
  		$output .= "  </table>";
		
		
		
		$output .= "<table width='100%' class='browser-image-uploader'>";
		if(function_exists("getimagesize")){
			if($product['image'] != '') {
			$imagedir = WPSC_THUMBNAIL_DIR;
			$image_size = @getimagesize(WPSC_THUMBNAIL_DIR.$product_data['image']);
			
			$output .= "          <tr>\n\r";
			$output .= "            <td>\n\r";
			$output .= TXT_WPSC_RESIZEIMAGE.": <br />";      
			$output .= "<span class='image_size_text'>".$image_size[0]."&times;".$image_size[1]."px</span>";
			$output .= "            </td>\n\r";
			$output .= "            <td>\n\r";
			$output .= "<table>";// style='border: 1px solid black'
			$output .= "  <tr>";
			$output .= "    <td style='height: 1em;'>";
			$output .= "<input type='hidden' id='current_thumbnail_image' name='current_thumbnail_image' value='" . $product['thumbnail_image'] . "' />";
			$output .= "<input type='radio' ";
			if ($product_data['thumbnail_state'] == 0) {
				$output .= "checked='true'";
			}
			$output .= " name='image_resize' value='0' id='image_resize0' class='image_resize' onclick='hideOptionElement(null, \"image_resize0\")' /> <label for='image_resize0'> ".TXT_WPSC_DONOTRESIZEIMAGE."<br />";
			$output .= "    </td>";
			// Put lightbox here so doesn't move around with DHTML bits
			$output .= "    <td rowspan=4>";
			
			$image_link = WPSC_IMAGE_URL.$product_data['image'];

			$output .= "<a  href='".$image_link."' rel='edit_product_1' class='thickbox preview_link'><img id='previewimage' src='$image_link' alt='".TXT_WPSC_PREVIEW."' title='".TXT_WPSC_PREVIEW."' height='100' width='100'/>"."</a>";
			$output .= "<br /><span style=\"font-size: 7pt;\">" . TXT_WPSC_PRODUCT_IMAGE_PREVIEW . "</span><br /><br />";
			
			if(($product_data['thumbnail_image'] != null)) {
				$output .= "<a id='preview_link' href='".WPSC_THUMBNAIL_URL . $product['thumbnail_image'] . "' rel='edit_product_2' class='thickbox'><img id='previewimage' src='" . WPSC_THUMBNAIL_URL . $product_data['thumbnail_image'] . "' alt='".TXT_WPSC_PREVIEW."' title='".TXT_WPSC_PREVIEW."' height='100' width='100'/>"."</a>";
				$output .= "<br /><span style=\"font-size: 7pt;\">" . TXT_WPSC_PRODUCT_THUMBNAIL_PREVIEW . "</span><br />";
			}

						
			//<div id='preview_button'><a id='preview_button' href='#'>".TXT_WPSC_PREVIEW."</a></div>
			// onclick='return display_preview_image(".$product['id'].")' 
			$output .= "    </td>";
			$output .= "  </tr>";
	
			$output .= "  <tr>";
			$output .= "    <td>";
			$output .= "<input type='radio' ";
			if ($product_data['thumbnail_state'] == 1) {
				$output .= "checked='true'";
			}
			$output .= "name='image_resize' value='1' id='image_resize1' class='image_resize' onclick='hideOptionElement(null, \"image_resize1\")' /> <label for='image_resize1'>".TXT_WPSC_USEDEFAULTSIZE." (".get_option('product_image_height') ."x".get_option('product_image_width').")";
			$output .= "    </td>";
			$output .= "  </tr>";
	
			$output .= "  <tr>";
			$output .= "    <td>";
			$output .= "<input type='radio' ";
			if ($product_data['thumbnail_state'] == 2) {
				$output .= "checked='true'";
			}
			$output .= " name='image_resize' value='2' id='image_resize2' class='image_resize' onclick='hideOptionElement(\"heightWidth\", \"image_resize2\")' /> <label for='image_resize2'>".TXT_WPSC_USESPECIFICSIZE." </label>
			<div id=\"heightWidth\" style=\"display: ";
			
			if ($product_data['thumbnail_state'] == 2) {
				$output .= "block;";
			} else {
				$output .= "none;";
			}
			
			$output .= "\">
			<input id='image_width' type='text' size='4' name='width' value='' /><label for='image_resize2'>".TXT_WPSC_PXWIDTH."</label>
			<input id='image_height' type='text' size='4' name='height' value='' /><label for='image_resize2'>".TXT_WPSC_PXHEIGHT." </label></div>";
			$output .= "    </td>";
			$output .= "  </tr>";
			$output .= "  <tr>";
			$output .= "    <td>";
			$output .= "<input type='radio' ";
			if ($product_data['thumbnail_state'] == 3) {
				$output .= "checked='true'";
			}
			$output .= " name='image_resize' value='3' id='image_resize3' class='image_resize' onclick='hideOptionElement(\"browseThumb\", \"image_resize3\")' /> <label for='image_resize3'> ".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />";
			$output .= "<div id='browseThumb' style='display: ";
			
			if($product_data['thumbnail_state'] == 3) {
				$output .= "block";
			} else {
				$output .= "none";
			}
	
			$output .= ";'>\n\r<input type='file' name='thumbnailImage' size='15' value='' />";
			$output .= "</div>\n\r";
			$output .= "    </td>";
			$output .= "  </tr>";
			// }.pe
			
			$output .= "</table>";
			$output .= "            </td>\n\r";
			$output .= "          </tr>\n\r";
		}
	
  
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		$output .= TXT_WPSC_UPLOADNEWIMAGE.": <br />";
		$output .= "<input type='file' name='image' value='' />";
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
		if(function_exists("getimagesize")) {
			if($product_data['image'] == '') {
				$output .= "          <tr>\n\r";
				$output .= "            <td></td>\n\r";
				$output .= "            <td>\n\r";
				$output .= "<table>\n\r";
				if(is_numeric(get_option('product_image_height')) && is_numeric(get_option('product_image_width'))) {
					$output .= "      <tr>\n\r";
					$output .= "        <td>\n\r";
					$output .= "      <input type='radio' name='image_resize' value='0' id='image_resize0' class='image_resize' onclick='hideOptionElement(null, \"image_resize0\");' /> <label for='image_resize0'>".TXT_WPSC_DONOTRESIZEIMAGE."</label>\n\r";
					$output .= "        </td>\n\r";
					$output .= "      </tr>\n\r";
					$output .= "      <tr>\n\r";
					$output .= "        <td>\n\r";
					$output .= "          <input type='radio' checked='true' name='image_resize' value='1' id='image_resize1' class='image_resize' onclick='hideOptionElement(null, \"image_resize1\");' /> <label for='image_resize1'>".TXT_WPSC_USEDEFAULTSIZE." (".get_option('product_image_height') ."x".get_option('product_image_width').")</label>\n\r";
					$output .= "        </td>\n\r";
					$output .= "      </tr>\n\r";
				}
				$output .= "      <tr>\n\r";
				$output .= "        <td>\n\r";
				$output .= "          <input type='radio' name='image_resize' value='2' id='image_resize2' class='image_resize' onclick='hideOptionElement(\"heightWidth\", \"image_resize2\");' />\n\r";
				$output .= "      <label for='image_resize2'>".TXT_WPSC_USESPECIFICSIZE."</label>\n\r";
				$output .= "          <div id='heightWidth' style='display: none;'>\n\r";
				$output .= "        <input type='text' size='4' name='width' value='' /><label for='image_resize2'>".TXT_WPSC_PXWIDTH."</label>\n\r";
				$output .= "        <input type='text' size='4' name='height' value='' /><label for='image_resize2'>".TXT_WPSC_PXHEIGHT."</label>\n\r";
				$output .= "      </div>\n\r";
				$output .= "        </td>\n\r";
				$output .= "      </tr>\n\r";
				$output .= "      <tr>\n\r";
				$output .= "      <td>\n\r";
				$output .= "        <input type='radio' name='image_resize' value='3' id='image_resize3' class='image_resize' onclick='hideOptionElement(\"browseThumb\", \"image_resize3\");' />\n\r";
				$output .= "        <label for='image_resize3'>".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />";
				$output .= "        <div id='browseThumb' style='display: none;'>\n\r";
				$output .= "          <input type='file' name='thumbnailImage' value='' />\n\r";
				$output .= "        </div>\n\r";
				$output .= "      </td>\n\r";
				$output .= "    </tr>\n\r";
				$output .= "  </table>\n\r";
				$output .= "            </td>\n\r";
				$output .= "          </tr>\n\r";
			}
		}
		
		
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		$output .= "<input id='delete_image' type='checkbox' name='deleteimage' value='1' /> ";
		$output .= "<label for='delete_image'>".TXT_WPSC_DELETEIMAGE."</label>";
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
		if(function_exists('edit_multiple_image_form')) {
			//$output .= edit_multiple_image_form($product['id']);
		}
  		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
		if(function_exists('add_multiple_image_form')) {
    		$output .= add_multiple_image_form('');
  		}
  		$output .= "          <tr>\n\r";
  		$output .= "            <td colspan='2' >\n\r";
  		$output .= "              <p>".TXT_WPSC_BROWSER_UPLOADER."</p>\n\r";
  		$output .= "            </td>\n\r";
  		$output .= "          </tr>\n\r";
    	
    	$output .="</table>";
    
  	}
	$output .="<table>";
	$output .= "          <tr>\n\r";
	$output .= "            <td colspan='2'>\n\r";
	$output .= "<ul id='gallery_list'>";
	if(function_exists('edit_multiple_image_gallery')) {
		$output .= edit_multiple_image_gallery($product_data['id']);
	}
	$output .= "</ul>";
	$output .= "<br style='clear:both;'>";
	$output .= "            </td>\n\r";
	$output .= "          </tr>\n\r";
	$output .="</table>";
  	$output .="</div></div>";
		
	} else {
	
		

    $output .= "
	</h3>
	<div class='inside'>
    
    <table width='100%' class='flash-image-uploader'>
      <tr>
        <td>
          <span id='spanButtonPlaceholder'></span>
            <button id='add-product-image' name='add-image' class='button-secondary' type='button'><small>Add New Image</small></button>
            
            <p>".TXT_WPSC_FLASH_UPLOADER."</p>
        </td>
      </tr>
    </table>
    
    
    
    
    <table width='100%' class='browser-image-uploader'>
      <tr>
        <td>
        ".TXT_WPSC_PRODUCTIMAGE.":
        </td>
        <td>
          <input type='file' name='image' value='' />
        </td>
      </tr>";
      if(function_exists("getimagesize") && is_numeric(get_option('product_image_height')) && is_numeric(get_option('product_image_width'))) {
		$output .= "
        <tr>
          <td></td>
          <td>
        <input type='radio' name='image_resize' value='0' id='add_image_resize0' class='image_resize' onclick='hideOptionElement(null, \"image_resize0\");' /> <label for='add_image_resize0'>".TXT_WPSC_DONOTRESIZEIMAGE."</label>
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type='radio' checked='true' name='image_resize' value='1' id='add_image_resize1' class='image_resize' onclick='hideOptionElement(null, \"image_resize1\");' /> <label for='add_image_resize1'>".TXT_WPSC_USEDEFAULTSIZE."(<abbr title='".TXT_WPSC_SETONSETTINGS."'>".get_option('product_image_height') ."&times;".get_option('product_image_width')."px</abbr>) </label>
          </td>
        </tr>";
        $default_size_set = true;
      }
    
      if(function_exists("getimagesize")) {
        $output .= "
        <tr>
          <td></td>
          <td>
            <input type='radio' name='image_resize' value='2'id='add_image_resize2' class='image_resize'  onclick='hideOptionElement(\"heightWidth\", \"image_resize2\");' />
            <label for='add_image_resize2'>".TXT_WPSC_USESPECIFICSIZE."</label>        
            <div id='heightWidth' style='display: none;'>
              <input type='text' size='4' name='width' value='' /><label for='add_image_resize2'>".TXT_WPSC_PXWIDTH."</label>
              <input type='text' size='4' name='height' value='' /><label for='add_image_resize2'>".TXT_WPSC_PXHEIGHT."</label>
            </div>
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type='radio' name='image_resize' value='3' id='add_image_resize3' class='image_resize' onclick='hideOptionElement(\"browseThumb\", \"image_resize3\");' />
            <label for='add_image_resize3'>".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />
            <div id='browseThumb' style='display: none;'>
              <input type='file' name='thumbnailImage' value='' />
            </div>
          </td>
        </tr>";
          if(function_exists('add_multiple_image_form')) {
            $output .= add_multiple_image_form("add_");
          }
        $output .= "
        <tr>
          <td colspan='2' >
            <p>".TXT_WPSC_BROWSER_UPLOADER."</p>
          </td>
        </tr>";
      }
      
      if(function_exists('gold_shpcrt_install')) {
        $output .= "<input type='hidden' value='1' id='gold_present'>";
      }

	$output .= "      
    </table>
    
    <table width='100%'>
      <tr>
        <td>
          <ul id='gallery_list'></ul>
        </td>
      </tr>
    </table>
  </div></div>";
  }
  return $output;
}

function product_download_box($product_data='') {
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
		
    	$file_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$product_data['file']."' LIMIT 1",ARRAY_A);
    	if(($file_data != null) && (function_exists('listen_button'))) {
    	  $output .= "".listen_button($file_data['idhash'], $file_data['id']);
    	}
    	  
    	$output .= "            </td>\n\r";
    	$output .= "          </tr>\n\r";
    }
	if(function_exists("make_mp3_preview") || function_exists("wpsc_media_player")) {    
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
	}
	$output .="</table></div></div>";
	return $output;
}

function product_label_box(){
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
function wpsc_meta_boxes(){
	add_meta_box('category_and_tag', 'Category and Tags', 'category_and_tag_box', 'wp-shopping-cart/display-items', 'normal', 'high');
	add_meta_box('price_and_stock', 'Price and Stock', 'price_and_stock_box', 'wp-shopping-cart/display-items', 'normal', 'high');
	add_meta_box('variation', 'Variations', 'variation_box', 'wp-shopping-cart/display-items', 'normal', 'high');
	add_meta_box('shipping', 'Shipping', 'shipping_box', 'wp-shopping-cart/display-items', 'normal', 'high');
	add_meta_box('advanced', 'Advanced Settings', 'advanced_box', 'wp-shopping-cart/display-items', 'normal', 'high');
	add_meta_box('product_download', 'Product Download', 'product_download_box', 'wp-shopping-cart/display-items', 'normal', 'high');
	add_meta_box('product_image', 'Product Images', 'product_image_box', 'wp-shopping-cart/display-items', 'normal', 'high');
}

add_action('admin_menu', 'wpsc_meta_boxes');

?>
