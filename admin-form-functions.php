<?php
// ini_set('display_errors','1');
function nzshpcrt_getproductform($prodid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;
  $variations_processor = new nzshpcrt_variations;
 /*
  * makes the product form
  * has functions inside a function
  */ 
  $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=$prodid LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A);
  $product = $product_data[0];
  $sql = "SELECT * FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `product_id`=$prodid AND meta_key='external_link' LIMIT 1";
  $meta_data = $wpdb->get_results($sql,ARRAY_A);
  $product['external_link'] = $meta_data[0]['meta_value'];
  $sql = "SELECT * FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `product_id`=$prodid AND meta_key='merchant_notes' LIMIT 1";
  $meta_data = $wpdb->get_results($sql,ARRAY_A);
  $product['merchant_notes'] = $meta_data[0]['meta_value'];
  $engrave = get_product_meta($prodid,'engraved',true);
  $can_have_uploaded_image = get_product_meta($prodid,'can_have_uploaded_image',true);
  
   $table_rate_price = get_product_meta($prodid,'table_rate_price',true);
//    exit("<pre>".print_r($table_rate_price,1)."</pre>");
  if(function_exists('wp_insert_term')) {
		$term_relationships = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."term_relationships WHERE object_id = $prodid", ARRAY_A);
		
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

  $check_variation_value_count = $wpdb->get_var("SELECT COUNT(*) as `count` FROM `".$wpdb->prefix."variation_values_associations` WHERE `product_id` = '".$product['id']."'");
  
  
	$current_user = wp_get_current_user();
	$closed_postboxes = (array)get_usermeta( $current_user->ID, 'closedpostboxes_editproduct');
	if (IS_WP27){
		$output .= "        <div id='productform'>";
		$output .= "        <div id='productform27' class='postbox'>";
		$output .= "<h3 class='hndle'>". TXT_WPSC_PRODUCTDETAILS." ".TXT_WPSC_ENTERPRODUCTDETAILSHERE."</h3>";
		$output .= "        <div class='inside'>";
	} else {
		$output .= "        <div id='productform'>";
		$output .= "<div class='categorisation_title'><strong class='form_group'>". TXT_WPSC_PRODUCTDETAILS." <span>".TXT_WPSC_ENTERPRODUCTDETAILSHERE."</span></strong></div>";
	}
  $output .= "        <table class='product_editform' style='width:100%;'>\n\r";
  $output .= "          <tr>\n\r";
/*
  $output .= "            <td class='itemformcol'>\n\r";
  $output .= TXT_WPSC_PRODUCTNAME.": ";
  $output .= "            </td>\n\r";
*/
  $output .= "            <td colspan='2' class='itemfirstcol'>\n\r";
  
	$output .= "        <div class='admin_product_name'>\n\r";
  $output .= "          <input class='wpsc_product_name' size='30' type='text' class='text'  name='title' value='".htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8')."' />\n\r";
	$output .= "				   <a href='#' class='shorttag_toggle'></a>\n\r";
	$output .= "				   <div class='admin_product_shorttags'>\n\r";
	$output .= "				   <h4>Shortcodes</h4>\n\r";

	$output .= "				     <dl>\n\r";
	$output .= "				       <dt>".TXT_WPSC_BUY_NOW_SHORTCODE.": </dt><dd>[buy_now_button={$product['id']}]</dd>\n\r";
	$output .= "				       <dt>".TXT_WPSC_ADD_TO_CART_SHORTCODE.":: </dt><dd>[add_to_cart={$product['id']}]</dd>\n\r";
	$output .= "				     </dl>\n\r";

	$output .= "				   <h4>Template Tags</h4>\n\r";
	
	$output .= "				     <dl>\n\r";
	$output .= "				       <dt>".TXT_WPSC_BUY_NOW_PHP.":: </dt><dd>&lt;?php echo wpsc_buy_now_button({$product['id']}); ?&gt;</dd>\n\r";
	$output .= "				       <dt>".TXT_WPSC_ADD_TO_CART_PHP.":: </dt><dd>&lt;?php echo wpsc_add_to_cart_button({$product['id']}); ?&gt;</dd>\n\r";
	$output .= "				     </dl>\n\r";
	
	$output .= "				     <p>\n\r";
	$output .= "More Shortcodes and Template Tags will be available in 3.7. In the meantime <a href='http://www.instinct.co.nz/more-shortcodes-for-37/'>tell us which ones you'd like</a> to see added to 3.7";
	$output .= "				     </p>\n\r";
	$output .= "				   </div>\n\r";
	
	$output .= "				   <div style='clear:both; height: 0px;'></div>\n\r";	
	$output .= "        </div>\n\r";
        
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  
  $output .= "          <tr>\n\r";
  $output .= "            <td  class='skuandprice'>\n\r";
  $output .= TXT_WPSC_SKU_FULL." :<br />";
/*
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
*/
  $sku = get_product_meta($product['id'], 'sku');
  $output .= "<input size='30' type='text' class='text'  name='productmeta_values[sku]' value='".htmlentities(stripslashes($sku), ENT_QUOTES, 'UTF-8')."' />\n\r";
  $output .= "            </td>\n\r";
  $output .= "<td  class='skuandprice'>";
  $output .= TXT_WPSC_PRICE." :<br />";
  $output .= "<input type='text' class='text' size='30' name='price' value='".$product['price']."'>";
  $output .= "</td>";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td colspan='2'>\n\r";
/*
  $output .= TXT_WPSC_PRODUCTDESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
*/
  $output .= "<div id='editorcontainer'>";
  $output .= "<textarea name='description' class='mceEditor' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "</div>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol' colspan='2'>\n\r";
  $output .= "<strong >".TXT_WPSC_ADDITIONALDESCRIPTION." :</strong><br />";
/*
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
*/

 $output .= "<textarea name='additional_description' cols='40' rows='8' >".stripslashes($product['additional_description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 /*    
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>\n\r";
  $output .= TXT_WPSC_PRODUCT_TAGS.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td class='itemformcol'>\n\r";
  $output .= "<input type='text' class='text'  name='product_tags' value='$imtags'><br /><span class='small_italic'>Seperate with commas</span>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

//   $output .="<tr><td>&nbsp;</td></tr>";
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>".TXT_WPSC_CATEGORISATION.":</td>\n\r";
  $output .= "            <td>\n\r";
  
    $categorisation_groups =  $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_categorisation_groups` WHERE `active` IN ('1')", ARRAY_A);
					
	foreach((array)$categorisation_groups as $categorisation_group) {
		$category_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}product_categories` WHERE `group_id` IN ('{$categorisation_group['id']}')");
		if($category_count > 0) {
			$output .= "<p>";
			$category_group_name = str_replace("[categorisation]", $categorisation_group['name'], TXT_WPSC_PRODUCT_CATEGORIES);
			$output .= "<strong>".$category_group_name.":</strong><br>";
			$output .= categorylist($categorisation_group['id'], $product['id'], 'edit_');
			$output .= "</p>\n\r";
		}
	} 

	$output .= "            </td>\n\r";
	$output .= "          </tr>\n\r";
*/
	if (IS_WP27) {
		 $output .= "          </table>\n\r";
	   $output .= "</div></div>";
	   $output .= "<div class='meta-box-sortables'>";
	} else {
		$output .= "<table style='width:100%'>";
		$output .= "<tr><td  colspan='2'>";
	}
	$order = get_option('wpsc_product_page_order');
	if (($order == '') || (count($order ) < 6)){
		$order=array("category_and_tag", "price_and_stock", "shipping", "variation", "advanced", "product_image", "product_download");
	}
	
	update_option('wpsc_product_page_order', $order);
	foreach((array)$order as $key => $box) {
		$box_function_name = $box."_box";
		if(function_exists($box_function_name)) {
			$output .= call_user_func($box_function_name,$product);
		}
		//echo $output;
		if(!IS_WP27 && ($key!=count($order)-1)) {
			$output .= "</td></tr>";
			if ($box == "product_image") {
				$output .= "<tr class='edit_product_image'><td colspan='2'>";
			} else {
  				$output .= "<tr><td colspan='2'>";
  			}
  			if ($box == "price_and_stock") {
  				ob_start();
				do_action('wpsc_product_form', $product['id']);
				$output .= ob_get_contents();
				ob_end_clean();
  			}
  		}
	}
	
	ob_start();
	do_action('wpsc_product_form', $product['id']);
	$output .= ob_get_contents();
	ob_end_clean();
		
		
	if (!IS_WP27) {
		$output .= "</td></tr>";
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		$output .= "            <br />\n\r";
		$output .= "<input type='hidden' name='prodid' id='prodid' value='".$product['id']."' />";
		$output .= "<input type='hidden' name='submit_action' value='edit' />";
		$output .= "<input  class='button' style='float:left;'  type='submit' name='submit' value='".TXT_WPSC_EDIT_PRODUCT."' />";
		$output .= "<a class='button delete_button' ' href='admin.php?page=".WPSC_DIR_NAME."/display-items.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE_PRODUCT."</a>";
		$output .= "            <td>\n\r";
		$output .= "          </tr>\n\r";
		$output .= "        </table>\n\r";
	} else {
		$output .= "<input type='hidden' name='prodid' id='prodid' value='".$product['id']."' />";
		$output .= "<input type='hidden' name='submit_action' value='edit' />";
		$output .= "<input class='button-primary' style='float:left;'  type='submit' name='submit' value='".TXT_WPSC_EDIT_PRODUCT."' />&nbsp;";
		$output .= "<a class='delete_button' ' href='admin.php?page=".WPSC_DIR_NAME."/display-items.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE_PRODUCT."</a>";
	}
		
		$output .= "</div>";
		return $output;
  }

function nzshpcrt_getcategoryform($catid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;
  $sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `id`=$catid LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A) ;
  $product = $product_data[0];
  $output = '';
  $output .= "<div class='editing_this_group'><p>";
	$output .= str_replace("[categorisation]", htmlentities(stripslashes($product['name'])), TXT_WPSC_EDITING_GROUP);
	
	//$output .= "       [ <a href='#' onclick='return showedit_categorisation_form()'>".TXT_WPSC_EDIT_THIS_GROUP."</a> ]";
	
	$output .= "</p></div>";
  $output .= "        <table class='category_forms'>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' class='text' name='title' value='".htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_CATEGORY_PARENT.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= wpsc_parent_category_list($product['group_id'], $product['id'], $product['category_parent']);
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";


	if ($product['display_type'] == 'grid') {
		$display_type1="selected='selected'";
	} else if ($product['display_type'] == 'default') {
		$display_type2="selected='selected'";
	}
	
	switch($product['display_type']) {
	  case "default":
			$product_view1 = "selected ='true'";
		break;
		
		case "grid":
		if(function_exists('product_display_grid')) {
			$product_view3 = "selected ='true'";
			break;
		}
		
		case "list":
		if(function_exists('product_display_list')) {
			$product_view2 = "selected ='true'";
			break;
		}
		
		default:
			$product_view0 = "selected ='true'";
		break;
	}	
	
	

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_GROUP_IMAGE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='file' name='image' value='' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  if(function_exists("getimagesize")) {
    if($product['image'] != '') {
      $imagepath = WPSC_CATEGORY_DIR . $product['image'];
      $imagetype = @getimagesize($imagepath); //previously exif_imagetype()
      $output .= "          <tr>\n\r";
      $output .= "            <td>\n\r";
      $output .= "            </td>\n\r";
      $output .= "            <td>\n\r";
      $output .= TXT_WPSC_HEIGHT.":<input type='text' size='6' name='height' value='".$imagetype[1]."' /> ".TXT_WPSC_WIDTH.":<input type='text' size='6' name='width' value='".$imagetype[0]."' /><br /><span class='small'>$nzshpcrt_imagesize_info</span><br />\n\r";
			$output .= "<span class='small'>".TXT_WPSC_GROUP_IMAGE_TEXT."</span>\n\r";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
		} else {
			$output .= "          <tr>\n\r";
			$output .= "            <td>\n\r";
			$output .= "            </td>\n\r";
			$output .= "            <td>\n\r";
			$output .= TXT_WPSC_HEIGHT.":<input type='text' size='6' name='height' value='".get_option('product_image_height')."' /> ".TXT_WPSC_WIDTH.":<input type='text' size='6' name='width' value='".get_option('product_image_width')."' /><br /><span class='small'>$nzshpcrt_imagesize_info</span><br />\n\r";
			$output .= "<span class='small'>".TXT_WPSC_GROUP_IMAGE_TEXT."</span>\n\r";
			$output .= "            </td>\n\r";
			$output .= "          </tr>\n\r";
		}
	}
	
	$output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DELETEIMAGE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='checkbox' name='deleteimage' value='1' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";
	
	$output .= "          <tr>\n\r";
	$output .= "          	<td colspan='2' class='category_presentation_settings'>\n\r";
	$output .= "          		<h4>".TXT_WPSC_PRESENTATIONSETTINGS."</h4>\n\r";
	$output .= "          		<span class='small'>".TXT_WPSC_GROUP_PRESENTATION_TEXT."</span>\n\r";
	$output .= "          	</td>\n\r";
	$output .= "          </tr>\n\r";
	
	$output .= "          <tr>\n\r";
	$output .= "          	<td>\n\r";
	$output .= "          	". TXT_WPSC_CATALOG_VIEW.":\n\r";
	$output .= "          	</td>\n\r";
	$output .= "          	<td>\n\r";
	$output .= "          		<select name='display_type'>\n\r";	
	$output .= "          			<option value='' $product_view0 >".TXT_WPSC_PLEASE_SELECT."</option>\n\r";	
	$output .= "          			<option value='default' $product_view1 >".TXT_WPSC_DEFAULT."</option>\n\r";	
	if(function_exists('product_display_list')) {
		$output .= "          			<option value='list' ". $product_view2.">". TXT_WPSC_LIST."</option>\n\r"; 
	} else {
		$output .= "          			<option value='list' disabled='disabled' ". $product_view2.">". TXT_WPSC_LIST."</option>\n\r";
	}	
	if(function_exists('product_display_grid')) {
		$output .= "          			<option value='grid' ". $product_view3.">". TXT_WPSC_GRID."</option>\n\r";
	} else {
		$output .= "          			<option value='grid' disabled='disabled' ". $product_view3.">". TXT_WPSC_GRID."</option>\n\r";
	}	
	$output .= "          		</select>\n\r";	
	$output .= "          	</td>\n\r";
	$output .= "          </tr>\n\r";
	$output .= "          <tr>\n\r";
	
	
  if(function_exists("getimagesize")) {
		$output .= "          <tr>\n\r";
		$output .= "            <td>\n\r";
		$output .= TXT_WPSC_THUMBNAIL_SIZE.": ";
		$output .= "            </td>\n\r";
		$output .= "            <td>\n\r";
		$output .= TXT_WPSC_HEIGHT.": <input type='text' value='".$product['image_height']."' name='product_height' size='6'/> ";
		$output .= TXT_WPSC_WIDTH.": <input type='text' value='".$product['image_width']."' name='product_width' size='6'/> <br/>";
		$output .= "            </td>\n\r";
		$output .= "          </tr>\n\r";
		$output .= "          </tr>\n\r";
	}


  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;' type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=".WPSC_DIR_NAME."/display-category.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r"; 
  return $output;
  }

function nzshpcrt_getvariationform($variation_id)
  {
  global $wpdb,$nzshpcrt_imagesize_info;

  $variation_sql = "SELECT * FROM `".$wpdb->prefix."product_variations` WHERE `id`='$variation_id' LIMIT 1";
  $variation_data = $wpdb->get_results($variation_sql,ARRAY_A) ;
  $variation = $variation_data[0];
  $output .= "        <table class='category_forms' >\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text'  class='text' name='title' value='".htmlentities(stripslashes($variation['name']), ENT_QUOTES, 'UTF-8')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_VARIATION_VALUES.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $variation_values_sql = "SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `variation_id`='$variation_id' ORDER BY `id` ASC";
  $variation_values = $wpdb->get_results($variation_values_sql,ARRAY_A);
  $variation_value_count = count($variation_values);
  $output .= "<div id='edit_variation_values'>";
  $num = 0;
  foreach($variation_values as $variation_value) {
    $output .= "<span class='variation_value'>";
    $output .= "<input type='text' class='text' name='variation_values[".$variation_value['id']."]' value='".htmlentities(stripslashes($variation_value['name']), ENT_QUOTES, 'UTF-8')."' />";
    if($variation_value_count > 1) {
      $output .= " <a  class='image_link' onclick='return remove_variation_value(this,".$variation_value['id'].")' href='#'><img src='".WPSC_URL."/images/trash.gif' alt='".TXT_WPSC_DELETE."' title='".TXT_WPSC_DELETE."' /></a>";
		}
    $output .= "<br />";
    $output .= "</span>";
    $num++;
	}
  $output .= "</div>";
  $output .= "<a href='#'  onclick='return add_variation_value(\"edit\")'>".TXT_WPSC_ADD."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$variation['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;'  type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=".WPSC_DIR_NAME."/display_variations.php&amp;deleteid=".$variation['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r";
  return $output;
  } 
      
function coupon_edit_form($coupon) {
  $start_timestamp = strtotime($coupon['start']);
  $end_timestamp = strtotime($coupon['expiry']);
  $id = $coupon['id'];
  $output = '';
  $output .= "<form name='edit_coupon' method='post' action='".get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-coupons.php'>\n\r";
    $output .= "   <input type='hidden' value='true' name='is_edit_coupon' />\n\r";
  $output .= "<table class='add-coupon'>\n\r";
  $output .= " <tr>\n\r";
  $output .= "   <th>".TXT_WPSC_COUPON_CODE."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_DISCOUNT."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_START."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_EXPIRY."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_USE_ONCE."</th>\n\r";
  $output .= "   <th>".TXT_WPSC_ACTIVE."</th>\n\r";
	$output .= "   <th>".TXT_WPSC_PERTICKED."</th>\n\r";
  $output .= "   <th></th>\n\r";
  $output .= " </tr>\n\r";
  $output .= " <tr>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' value='".$coupon['coupon_code']."' name='edit_coupon[".$id."][coupon_code]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' value='".$coupon['value']."' size='3' name=edit_coupon[".$id."][value]' />\n\r";
  $output .= "   <select name='edit_coupon[".$id."][is-percentage]'>\n\r";
  $output .= "     <option value='0' ".(($coupon['is-percentage'] == 0) ? "selected='true'" : '')." >$</option>\n\r";//
  $output .= "     <option value='1' ".(($coupon['is-percentage'] == 1) ? "selected='true'" : '')." >%</option>\n\r";
  $output .= "   </select>\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_start = explode(" ",$coupon['start']);
  $output .= "<input type='text' class='pickdate' size='10' name='edit_coupon[".$id."][start]' value='{$coupon_start[0]}'>";
/*  $output .= "   <select name='edit_coupon[".$id."][start][day]'>\n\r";  
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d", $start_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>$i</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][start][month]'>\n\r";  
   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m", $start_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][start][year]'>\n\r";
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == date("Y", $start_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".$i."</option>";
     }
  $output .= "   </select>\n\r";*/
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_expiry = explode(" ",$coupon['expiry']);
  $output .= "<input type='text' class='pickdate' size='10' name='edit_coupon[".$id."][expiry]' value='{$coupon_expiry[0]}'>";
  /*$output .= "   <select name='edit_coupon[".$id."][expiry][day]'>\n\r";
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d", $end_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>$i</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][expiry][month]'>\n\r";

   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m", $end_timestamp)) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
  $output .= "   </select>\n\r";
  $output .= "   <select name='edit_coupon[".$id."][expiry][year]'>\n\r";
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == (date("Y", $end_timestamp))) { $selected = "selected='true'"; }
     $output .= "    <option $selected value='$i'>".$i."</option>\n\r";
     }
  $output .= "   </select>\n\r";*/
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['use-once'] == 1) ? "checked='true'" : '')." name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['active'] == 1) ? "checked='true'" : '')." name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['every_product'] == 1) ? "checked='true'" : '')." name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='".$id."' name='edit_coupon[".$id."][id]' />\n\r";
  //$output .= "   <input type='hidden' value='false' name='add_coupon' />\n\r";
  $output .= "   <input type='submit' value='".TXT_WPSC_SUBMIT."' name='edit_coupon[".$id."][submit_coupon]' />\n\r";
  $output .= "   <input type='submit' value='".TXT_WPSC_DELETE."' name='edit_coupon[".$id."][delete_coupon]' />\n\r";
  
  $output .= "  </td>\n\r";
  $output .= " </tr>\n\r";
  $output .= "</table>\n\r";
  $output .= "</form>\n\r";
  return $output;
  }
  
function setting_button(){
	$itemsFeedURL = "http://www.google.com/base/feeds/items";
	$next_url  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']."?page=".WPSC_DIR_NAME."/display-items.php";
	$redirect_url = 'https://www.google.com/accounts/AuthSubRequest?session=1';
	$redirect_url .= '&next=';
	$redirect_url .= urlencode($next_url);
	$redirect_url .= "&scope=";
	$redirect_url .= urlencode($itemsFeedURL);
	
// 	$output.="<div><img src='".get_option('siteurl')."/wp-content/plugins/".WPSC_DIR_NAME."/images/settings_button.jpg' onclick='display_settings_button()'>";
	$output.="<div style='float: right; margin-top: 0px; position: relative;'> | <a href='#' onclick='display_settings_button(); return false;' style='text-decoration: underline;'>".TXT_WPSC_SETTINGS." &raquo;</a>";
	$output.="<span id='settings_button' style='width:180px;background-color:#f1f1f1;position:absolute; right: 10px; border:1px solid black; display:none;'>";
	$output.="<ul class='settings_button'>";
	
	$output.="<li><a href='admin.php?page=".WPSC_DIR_NAME."/options.php'>".TXT_WPSC_SHOP_SETTINGS."</a></li>";
	$output.="<li><a href='admin.php?page=".WPSC_DIR_NAME."/options.php#ui-tabs-76'>".TXT_WPSC_MONEY_AND_PAYMENT."</a></li>";
	$output.="<li><a href='admin.php?page=".WPSC_DIR_NAME."/options.php#ui-tabs-78'>".TXT_WPSC_CHECKOUT_PAGE_SETTINGS."</a></li>";
	//$output.="<li><a href='?page=".WPSC_DIR_NAME."/instructions.php'>Help/Upgrade</a></li>";
	//$output.="<li><a href='{$redirect_url}'>".TXT_WPSC_LOGIN_TO_GOOGLE_BASE."</a></li>";
	$output.="</ul>";
//	$output.="<div>Checkout Settings</div>";
	$output.="</span>&emsp;&emsp;</div>";
	
	return $output;
}

function wpsc_right_now() {
  global $wpdb,$nzshpcrt_imagesize_info;
	$year = date("Y");
	$month = date("m");
	$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
	$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);

  $replace_values[":productcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."product_list` WHERE `active` IN ('1')");
  $product_count = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."product_list` WHERE `active` IN ('1')");
  $replace_values[":productcount:"] .= " ".(($replace_values[":productcount:"] == 1) ? TXT_WPSC_PRODUCTCOUNT_SINGULAR : TXT_WPSC_PRODUCTCOUNT_PLURAL);
  $product_unit = (($replace_values[":productcount:"] == 1) ? TXT_WPSC_PRODUCTCOUNT_SINGULAR : TXT_WPSC_PRODUCTCOUNT_PLURAL);
  
  $replace_values[":groupcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."product_categories` WHERE `active` IN ('1')");
  $group_count = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."product_categories` WHERE `active` IN ('1')");
  $replace_values[":groupcount:"] .= " ".(($replace_values[":groupcount:"] == 1) ? TXT_WPSC_GROUPCOUNT_SINGULAR : TXT_WPSC_GROUPCOUNT_PLURAL);
  $group_unit = (($replace_values[":groupcount:"] == 1) ? TXT_WPSC_GROUPCOUNT_SINGULAR : TXT_WPSC_GROUPCOUNT_PLURAL);
  
  $replace_values[":salecount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."purchase_logs` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
  $sales_count = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."purchase_logs` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
  $replace_values[":salecount:"] .= " ".(($replace_values[":salecount:"] == 1) ? TXT_WPSC_SALECOUNT_SINGULAR : TXT_WPSC_SALECOUNT_PLURAL);
  $sales_unit = (($replace_values[":salecount:"] == 1) ? TXT_WPSC_SALECOUNT_SINGULAR : TXT_WPSC_SALECOUNT_PLURAL);
		
  $replace_values[":monthtotal:"] = nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
  $replace_values[":overaltotal:"] = nzshpcrt_currency_display(admin_display_total_price(),1);
  
  $variation_count = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."product_variations`");
  $variation_unit = (($variation_count == 1) ? TXT_WPSC_VARIATION_SINGULAR : TXT_WPSC_VARIATION_PLURAL);
  
  $replace_values[":pendingcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` IN ('1')");
  $pending_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` IN ('1')");
  $replace_values[":pendingcount:"] .= " " . (($replace_values[":pendingcount:"] == 1) ? TXT_WPSC_PENDINGCOUNT_SINGULAR : TXT_WPSC_PENDINGCOUNT_PLURAL);
  $pending_sales_unit = (($replace_values[":pendingcount:"] == 1) ? TXT_WPSC_PENDINGCOUNT_SINGULAR : TXT_WPSC_PENDINGCOUNT_PLURAL);
  
  $accept_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` IN ('2' ,'3', '4')");
  $accept_sales_unit = (($accept_sales == 1) ? TXT_WPSC_PENDINGCOUNT_SINGULAR : TXT_WPSC_PENDINGCOUNT_PLURAL);

  
  $replace_values[":theme:"] = get_option('wpsc_selected_theme');
  $replace_values[":versionnumber:"] = WPSC_PRESENTABLE_VERSION;
  
	if (function_exists('add_object_page')) {
		$output="";	
		$output.="<div id='dashboard_right_now' class='postbox'>";
		$output.="	<h3 class='hndle'>";
		$output.="		<span>".TXT_WPSC_CURRENT_MONTH."</span>";
		//$output.="		<a class='rbutton' href='admin.php?page=".WPSC_DIR_NAME."/display-items.php'><strong>".TXT_WPSC_ADDNEWPRODUCT."</strong></a>";
		$output.="		<br class='clear'/>";
		$output.="	</h3>";
		
		$output .= "<div class='inside'>";
		$output .= "<p class='sub'>".TXT_WPSC_AT_A_GLANCE."</p>";
		//$output.="<p class='youhave'>".TXT_WPSC_SALES_DASHBOARD."</p>";
		$output .= "<div class='table'>";
		$output .= "<table>";
		
		$output .= "<tr class='first'>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=".WPSC_DIR_NAME."/display-items.php'>".$product_count."</a>";
		$output .= "<td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($product_unit);
		$output .= "<td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=".WPSC_DIR_NAME."/display-log.php'>".$sales_count."</a>";
		$output .= "<td>";
		$output .= "<td class='last'>";
		$output .= ucfirst($sales_unit);
		$output .= "<td>";
		$output .= "</tr>";
		
		$output .= "<tr>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=".WPSC_DIR_NAME."/display-category.php'>".$group_count."</a>";
		$output .= "<td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($group_unit);
		$output .= "<td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=".WPSC_DIR_NAME."/display-log.php'>".$pending_sales."</a>";
		$output .= "<td>";
		$output .= "<td class='last t waiting'>".TXT_WPSC_PENDING;
		$output .= ucfirst($pending_sales_unit);
		$output .= "<td>";
		$output .= "</tr>";
		
		$output .= "<tr>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=".WPSC_DIR_NAME."/display_variations.php'>".$variation_count."</a>";
		$output .= "<td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($variation_unit);
		$output .= "<td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=".WPSC_DIR_NAME."/display-log.php'>".$accept_sales."</a>";
		$output .= "<td>";
		$output .= "<td class='last t approved'>".TXT_WPSC_CLOSED;
		$output .= ucfirst($accept_sales_unit);
		$output .= "<td>";
		$output .= "</tr>";
		
		$output .= "</table>";
		$output .= "</div>";
		$output .= "<div class='versions'>";
		$output .= "<p><a class='button rbutton' href='admin.php?page=".WPSC_DIR_NAME."/display-items.php'><strong>".TXT_WPSC_ADD_NEW_PRODUCT."</strong></a>".TXT_WPSC_HERE_YOU_CAN_ADD."</p>";
		$output .= "</div>";
		$output .= "</div>";
		$output.="</div>";
	} else {  
		$output="";	
		$output.="<div id='rightnow'>\n\r";
		$output.="	<h3 class='reallynow'>\n\r";
		$output.="		<a class='rbutton' href='admin.php?page=".WPSC_DIR_NAME."/display-items.php'><strong>".TXT_WPSC_ADD_NEW_PRODUCT."</strong></a>\n\r";
		$output.="		<span>"._('Right Now')."</span>\n\r";
		
		//$output.="		<br class='clear'/>\n\r";
		$output.="	</h3>\n\r";
		
		$output.="<p class='youhave'>".TXT_WPSC_SALES_DASHBOARD."</p>\n\r";
		$output.="	<p class='youare'>\n\r";
		$output.="		".TXT_WPSC_YOUAREUSING."\n\r";
		//$output.="		<a class='rbutton' href='themes.php'>Change Theme</a>\n\r";
		//$output.="<span id='wp-version-message'>This is WordPress version 2.6. <a class='rbutton' href='http://wordpress.org/download/'>Update to 2.6.1</a></span>\n\r";
		$output.="		</p>\n\r";
		$output.="</div>\n\r";
		$output.="<br />\n\r";
		$output = str_replace(array_keys($replace_values), array_values($replace_values),$output);
	}
	
	return $output;
}


function wpsc_packing_slip($purchase_id) {
  global $wpdb;
	$purch_sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `id`='".$purchase_id."'";
		$purch_data = $wpdb->get_row($purch_sql,ARRAY_A) ;
			
			
	  //echo "<p style='padding-left: 5px;'><strong>".TXT_WPSC_DATE."</strong>:".date("jS M Y", $purch_data['date'])."</p>";

		$cartsql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`=".$purchase_id."";
		$cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
		$j = 0;
		if($cart_log != null) {
      echo "<div class='packing_slip'>\n\r";
			echo "<h2>".TXT_WPSC_PACKING_SLIP."</h2>\n\r";
			echo "<strong>".TXT_WPSC_ORDER." #</strong> ".$purchase_id."<br /><br />\n\r";
			
			echo "<table>\n\r";
			
			$form_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE  `log_id` = '".(int)$purchase_id."'";
			$input_data = $wpdb->get_results($form_sql,ARRAY_A);
			
			foreach($input_data as $input_row) {
			  $rekeyed_input[$input_row['form_id']] = $input_row;
			}
			
			
			if($input_data != null) {
        $form_data = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}collect_data_forms` WHERE `active` = '1'",ARRAY_A);
        
        foreach($form_data as $form_field) {
          switch($form_field['type']) {
            case 'country':
            if(is_numeric($purch_data['shipping_region'])) {
              echo "  <tr><td>".TXT_WPSC_STATE.":</td><td>".get_region($purch_data['shipping_region'])."</td></tr>\n\r";
            }
            echo "  <tr><td>".$form_field['name'].":</td><td>".get_country($purch_data['billing_country'])."</td></tr>\n\r";
            break;
                
            case 'delivery_country':
            echo "  <tr><td>".$form_field['name'].":</td><td>".get_country($purch_data['shipping_country'])."</td></tr>\n\r";
            break;
                
            case 'heading':
            echo "  <tr><td colspan='2'><strong>".$form_field['name'].":</strong></td></tr>\n\r";
            break;
            
            default:
            echo "  <tr><td>".$form_field['name'].":</td><td>".$rekeyed_input[$form_field['id']]['value']."</td></tr>\n\r";
            break;
          }
        }
			} else {
        echo "  <tr><td>".TXT_WPSC_NAME.":</td><td>".$purch_data['firstname']." ".$purch_data['lastname']."</td></tr>\n\r";
        echo "  <tr><td>".TXT_WPSC_ADDRESS.":</td><td>".$purch_data['address']."</td></tr>\n\r";
        echo "  <tr><td>".TXT_WPSC_PHONE.":</td><td>".$purch_data['phone']."</td></tr>\n\r";
        echo "  <tr><td>".TXT_WPSC_EMAIL.":</td><td>".$purch_data['email']."</td></tr>\n\r";
			}
			
			if(get_option('payment_method') == 2) {
				$gateway_name = '';
				foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
					if($purch_data['gateway'] != 'testmode') {
						if($gateway['internalname'] == $purch_data['gateway'] ) {
							$gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
					}
				}
			}
// 			echo "  <tr><td colspan='2'></td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_PAYMENT_METHOD.":</td><td>".$gateway_name."</td></tr>\n\r";
// 			//echo "  <tr><td>".TXT_WPSC_PURCHASE_NUMBER.":</td><td>".$purch_data['id']."</td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_HOWCUSTOMERFINDUS.":</td><td>".$purch_data['find_us']."</td></tr>\n\r";
// 			$engrave_line = explode(",",$purch_data['engravetext']);
// 			echo "  <tr><td>".TXT_WPSC_ENGRAVE."</td><td></td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_ENGRAVE_LINE_ONE.":</td><td>".$engrave_line[0]."</td></tr>\n\r";
// 			echo "  <tr><td>".TXT_WPSC_ENGRAVE_LINE_TWO.":</td><td>".$engrave_line[1]."</td></tr>\n\r";
// 			if($purch_data['transactid'] != '') {
// 				echo "  <tr><td>".TXT_WPSC_TXN_ID.":</td><td>".$purch_data['transactid']."</td></tr>\n\r";
// 			}
			echo "</table>\n\r";
			
			
			
			
      echo "<table class='packing_slip'>";
				
				echo "<tr>";
				echo " <th>".TXT_WPSC_QUANTITY." </th>";
				
				echo " <th>".TXT_WPSC_NAME."</th>";
				
				
				echo " <th>".TXT_WPSC_PRICE." </th>";
				
				echo " <th>".TXT_WPSC_SHIPPING." </th>";
							
				echo '</tr>';
			$endtotal = 0;
			$all_donations = true;
			$all_no_shipping = true;
			$file_link_list = array();
			foreach($cart_log as $cart_row) {
				$alternate = "";
				$j++;
				if(($j % 2) != 0) {
					$alternate = "class='alt'";
        }
				$productsql= "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=".$cart_row['prodid']."";
				$product_data = $wpdb->get_results($productsql,ARRAY_A); 
			
			
			
				$variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$cart_row['id']."'";
				$variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
				$variation_count = count($variation_data);
				
				if($variation_count > 1) {
					$variation_list = " (";
					$i = 0;
					foreach($variation_data as $variation) {
						if($i > 0) {
							$variation_list .= ", ";
            }
						$value_id = $variation['value_id'];
						$value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
						$variation_list .= $value_data[0]['name'];
						$i++;
          }
					$variation_list .= ")";
        } else if($variation_count == 1) {
          $value_id = $variation_data[0]['value_id'];
          $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          $variation_list = " (".$value_data[0]['name'].")";
        } else {
							$variation_list = '';
        }
				
				
				if($cart_row['donation'] != 1) {
					$all_donations = false;
				}
				if($cart_row['no_shipping'] != 1) {
					$shipping = $cart_row['pnp'] * $cart_row['quantity'];
					$total_shipping += $shipping;            
					$all_no_shipping = false;
				} else {
					$shipping = 0;
				}
				
				$price = $cart_row['price'] * $cart_row['quantity'];
				$gst = $price - ($price  / (1+($cart_row['gst'] / 100)));
				
				if($gst > 0) {
				  $tax_per_item = $gst / $cart_row['quantity'];
				}
				

				echo "<tr $alternate>";
		
		
				echo " <td>";
				echo $cart_row['quantity'];
				echo " </td>";
				
				echo " <td>";
				echo $product_data[0]['name'];
				echo $variation_list;
				echo " </td>";
				
				
				echo " <td>";
				echo nzshpcrt_currency_display( $price, 1);
				echo " </td>";
				
				echo " <td>";
				echo nzshpcrt_currency_display($shipping, 1);
				echo " </td>";
							
				echo '</tr>';
				}
			echo "</table>";
			echo "</div>\n\r";
		} else {
			echo "<br />".TXT_WPSC_USERSCARTWASEMPTY;
		}

}


    
function edit_multiple_image_gallery($id) {
	global $wpdb;
	$siteurl = get_option('siteurl');
	$main_image = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$id'",ARRAY_A);
	$timestamp = time();
	$output .= "<li class='first' id='0'>";
	if ($main_image[0]['image'] == '') {
		$output .='';
	} else {
		$output .= "<div class='previewimage' id='gallery_image_0'><a id='extra_preview_link_".$image['id']."' href='".WPSC_IMAGE_URL.$main_image[0]['image']."' rel='product_extra_image_".$image['id']."' class='thickbox'><img class='previewimage' src='".WPSC_IMAGE_URL.$main_image[0]['image']."' alt='".TXT_WPSC_PREVIEW."' title='".TXT_WPSC_PREVIEW."' /></a>";
	}
	$output .= "<div id='image_settings_box'>";
	$output .= "<div class='upper_settings_box'>";
	$output .= "<div class='upper_image'><img src='".WPSC_URL."/images/pencil.png'/></div><div class='upper_txt'>Thumbnail Settings<a class='closeimagesettings'>X</a></div>";
	$output .= "</div>";
	$output .= "<div class='lower_settings_box'>";
	$output .= "<table>";// style='border: 1px solid black'
	$output .= "  <tr>";
	$output .= "    <td style='height: 1em;'>";
	$output .= "<input type='hidden' id='current_thumbnail_image' name='current_thumbnail_image' value='" . $product['thumbnail_image'] . "' />";
	$output .= "<input type='radio' ";
	if ($product['thumbnail_state'] == 0) {
		$output .= "checked='true'";
	}
	$output .= " name='image_resize' value='0' id='image_resize0_$timestamp' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='image_resize0_$timestamp'> ".TXT_WPSC_DONOTRESIZEIMAGE."<br />";
	$output .= "    </td>";
	$output .= "  </tr>";

	$output .= "  <tr>";
	$output .= "    <td>";
	$output .= "<input type='radio' ";
	if ($product['thumbnail_state'] == 1) {
		$output .= "checked='true'";
	}
	$output .= "name='image_resize' value='1' id='image_resize1_$timestamp' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='image_resize1_$timestamp'>".TXT_WPSC_USEDEFAULTSIZE."(<abbr title='".TXT_WPSC_SETONSETTINGS."'>".get_option('product_image_height') ."&times;".get_option('product_image_width')."px</abbr>)";
	$output .= "    </td>";
	$output .= "  </tr>";

	$output .= "  <tr>";
	$output .= "    <td>";
	$output .= "<input type='radio' ";
	if ($product['thumbnail_state'] == 2) {
		$output .= "checked='true'";
	}
	$output .= " name='image_resize' value='2' id='image_resize2_$timestamp' class='image_resize' onclick='image_resize_extra_forms(this)' /> <label for='image_resize2_$timestamp'>".TXT_WPSC_USESPECIFICSIZE." </label>
	<div class='heightWidth image_resize_extra_forms' style=\"display: none;\">
	<input id='image_width' type='text' size='4' name='width' value='' /><label for='image_resize2'>".TXT_WPSC_PXWIDTH."</label>
	<input id='image_height' type='text' size='4' name='height' value='' /><label for='image_resize2'>".TXT_WPSC_PXHEIGHT." </label></div>";
	$output .= "    </td>";
	$output .= "  </tr>";
	$output .= "  <tr>";
	$output .= "    <td>";
	$output .= "<input type='radio' ";
	if ($product['thumbnail_state'] == 3) {
		$output .= "checked='true'";
	}
	$output .= " name='image_resize' value='3' id='image_resize3_$timestamp' class='image_resize'  onclick='image_resize_extra_forms(this)' /> <label for='image_resize3_$timestamp'> ".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />";
	$output .= "<div class='browseThumb image_resize_extra_forms' style='display: ";
	
	if($product['thumbnail_state'] == 3) {
		$output .= "block";
	} else {
		$output .= "none";
	}

	$output .= ";'>\n\r<input type='file' name='thumbnailImage' size='15' value='' />";
	$output .= "</div>\n\r";
	$output .= "    </td>";
	$output .= "  </tr>";
	
	$output .= "  <tr>";
	$output .= "    <td>";
	$output .= "    <a href='#' class='delete_primary_image'>Delete this Image</a>";
	$output .= "    </td>";
	$output .= "  </tr>";
	
	$output .= "</table>";
	$output .= "</div>";
	$output .= "</div>";
	$output .= "<a class='editButton'>Edit   <img src='".WPSC_URL."/images/pencil.png'/></a>";
	$output .= "</div>";
	$output .= "</li>";
	
	$num = 0;
	if(function_exists('gold_shpcrt_display_gallery')) {
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_images` WHERE `product_id` = '$id' ORDER BY image_order ASC",ARRAY_A);
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
  return $output;
}



function wpsc_product_item_row() {
/*
"<tr class='products'>	<td class='imagecol' style='width: 25%;'>
<input type='checkbox' value='3' class='deletecheckbox' name='productdelete[]'/><img width='35' height='35' alt='Praying Mantis' title='Drag to a new position' src='http://apps.instinct.co.nz/wp_2.6.5/wp-content/uploads/wpsc/product_images/thumbnails/mantis-3.jpg'/></td><td width='25%'><a onclick='filleditform(3);return false;' href='#'>Praying Mantis</a></td><td id='3'><span class='pricedisplay' id='3' title='Click to edit...'>$32.00</span>            </td><td>

<a href='?page=".WPSC_DIR_NAME."/display-items.php&amp;catid=1'>Arthropods</a></td>				
</tr>
";*/
}

?>
