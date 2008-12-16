 <?php
include_once('tagging_functions.php');
include_once('google_base_functions.php');
$category_data = null;

$current_user = wp_get_current_user();

$closed_postboxes = (array)get_usermeta( $current_user->ID, 'closedpostboxes_products');

$variations_processor = new nzshpcrt_variations;

$flash = true;
if ( false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac') && apache_mod_loaded('mod_security') )
	$flash = false;
function topcategorylist() {
	global $wpdb,$category_data;
	$siteurl = get_option('siteurl');
	$url = $siteurl."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-items.php";
	$options = "";
	$options .= "<option value='$url'>".TXT_WPSC_ALLCATEGORIES."</option>\r\n";
	$options .= top_category_options(null, 0, $_GET['catid']);
	//$concat .= "<select name='category' id='category_select' onChange='categorylist(this.options[this.selectedIndex].value);'>".$options."</select>\r\n";
	$concat .= "<select name='category' id='category_select'>".$options."</select>\r\n";
	$concat .= "<button class='button' id='submit_category_select'>Filter</button>";
	return $concat;
}

function top_category_options($category_id = null, $iteration = 0, $selected_id = null) {
  /*
   * Displays the category forms for adding and editing products
   * Recurses to generate the branched view for subcategories
   */
  global $wpdb;
  $siteurl = get_option('siteurl');
  $url = $siteurl."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-items.php";
  if(is_numeric($category_id)) {
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '$category_id'  ORDER BY `id` ASC",ARRAY_A);
	} else {
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '0'  ORDER BY `id` ASC",ARRAY_A);
	}
  foreach((array)$values as $option) {
    if($selected_id == $option['id']) {
      $selected = "selected='selected'";
    }
    $output .= "<option $selected value='$url&amp;catid=".$option['id']."'>".str_repeat("-", $iteration).stripslashes($option['name'])."</option>\r\n";
    $output .= top_category_options($option['id'], $iteration+1, $selected_id);
    $selected = "";
  }
  return $output;
}


/*
 * Makes the order changes
 */

if(is_numeric($_GET['catid']) && is_numeric($_GET['product_id']) && ($_GET['position_action'] != ''))
  {
  $position_cat_id = $_GET['catid'];
  $position_prod_id = $_GET['product_id'];
  $current_order_row = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('$position_prod_id') LIMIT 1;",ARRAY_A);
  $current_order_row = $current_order_row[0];
  switch($_GET['position_action'])
    {
    case 'top':
    if($current_order_row['order'] > 0) 
      {
      $check_existing = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN('$position_cat_id') AND `order` IN('0') LIMIT 1;",ARRAY_A);
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '0' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('$position_prod_id') LIMIT 1;");
      if($check_existing != null)
        {
        $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = (`order` + 1) WHERE `category_id` IN('$position_cat_id') AND `product_id` NOT IN('$position_prod_id') AND `order` < '".$current_order_row['order']."'");
        }
      }
    break;
    
    case 'up':
    if($current_order_row['order'] > 0) 
      {
      $target_rows = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN ('".$position_cat_id."') AND `order` <= '".$current_order_row['order']."' ORDER BY `order` DESC LIMIT 2",ARRAY_A);
      //exit("<pre>".print_r($target_rows,true)."</pre>");
      if(count($target_rows) == 2)
        {
        $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[1]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[0]['product_id']."') LIMIT 1");
        $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[0]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[1]['product_id']."') LIMIT 1");
        }
      }
    break;
    
    case 'down':
    $target_rows = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN ('".$position_cat_id."') AND `order` >= '".$current_order_row['order']."' ORDER BY `order` ASC LIMIT 2",ARRAY_A);
    //exit("<pre>".print_r($target_rows,true)."</pre>");
    if(count($target_rows) == 2)
      {
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[1]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[0]['product_id']."') LIMIT 1");
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[0]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[1]['product_id']."') LIMIT 1");
      }
    break;
    
    case 'bottom':
    $end_row = $wpdb->get_results("SELECT MAX( `order` ) AS `order` FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN ('".$position_cat_id."') LIMIT 1",ARRAY_A);
    $end_order_number = $end_row[0]['order'];
    //exit($current_order_row['order'] . " | " . $end_order_number);
    if($current_order_row['order'] < $end_order_number)
      {
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '$end_order_number' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('$position_prod_id') LIMIT 1;");      
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = (`order` - 1) WHERE `category_id` IN('$position_cat_id') AND `product_id` NOT IN('$position_prod_id') AND `order` > '".$current_order_row['order']."'");
      }
    break;
    
    default:
    break;
    }
  }


/*
 * Adds new products
 */
if($_POST['submit_action'] == 'add') {
  
  // well, there is simply no way to do this other than the blatantly obvious, so here it is
  if(!is_callable('getshopped_item_limit') || (@getshopped_item_limit() !== false)) {
  
		//Allen's Change for Google base
		if (isset($_GET['token']) || isset($_SESSION['google_base_sessionToken'])) {
			if (isset($_GET['token'])) {
				$sessionToken=exchangeToken($_GET['token']);
				$_SESSION['google_base_sessionToken'] = $sessionToken;
			}
			if (isset($_SESSION['google_base_sessionToken']))
				$sessionToken=$_SESSION['google_base_sessionToken'];
			postItem($_POST['name'], $_POST['price'], $_POST['description'], $sessionToken);
		}
	//Google base change ends here
	
		$file_name = null;
		if($_POST['file_url'] != null) {
			$url_array = array_reverse((array)explode("/",$_POST['file_url']));
			if(is_file(WPSC_FILE_DIR.$url_array[0])) {
				$file_name = $url_array[0];
			}
		}
		
		$thumbnail_image = '';
		
			
		$file = 0;  
		/* handle adding file uploads here */
		if(!empty($_FILES['file']['name'])) {
			$fileid = wpsc_item_process_file('add');
			$file = $fileid;
		} else if (($_POST['select_product_file'] != '')) {
			$fileid = wpsc_item_reassign_file($_POST['select_product_file'], 'add');
			$file = $fileid;
		}
				
		
	if(is_numeric((int)$_POST['quantity']) && ($_POST['quantity_limited'] == "yes")) {
				$quantity_limited = 1;
				$quantity = (int)$_POST['quantity'];
			} else {
				$quantity_limited = 0;
				$quantity = 0;
			}
				
			if($_POST['special'] == 'yes') {
				$special = 1;
				if(is_numeric($_POST['special_price'])) {
					$special_price = $_POST['price'] - $_POST['special_price'];
				}
			} else {
				$special = 0;
				$special_price = '';
			}
			
			if($_POST['notax'] == 'yes') {
				$notax = 1;
			} else {
				$notax = 0;
			}
	
				
			if($_POST['display_frontpage'] == "yes") {
				$display_frontpage = 1;
			} else {
				$display_frontpage = 0;
			}
			
			if($_POST['donation'] == "yes") {
				$is_donation = 1;
			} else {
				$is_donation = 0;
			}
			
			if($_POST['no_shipping'] == "yes") {
				$no_shipping = 1;
			} else {
				$no_shipping = 0;
			}
	
		$insertsql = "INSERT INTO `".$wpdb->prefix."product_list` ( `name` , `description` , `additional_description` , `price`, `weight`, `weight_unit`, `pnp`, `international_pnp`, `file` , `image` , `brand`, `quantity_limited`, `quantity`, `special`, `special_price`, `display_frontpage`,`notax`, `donation`, `no_shipping`, `thumbnail_image`, `thumbnail_state`) VALUES ('".$wpdb->escape($_POST['name'])."', '".$wpdb->escape($_POST['description'])."', '".$wpdb->escape($_POST['additional_description'])."','".(float)$wpdb->escape(str_replace(",","",$_POST['price']))."','".$wpdb->escape((float)$_POST['weight'])."','".$wpdb->escape($_POST['weight_unit'])."', '".$wpdb->escape((float)$_POST['pnp'])."', '".$wpdb->escape($_POST['international_pnp'])."', '".(int)$file."', '".$_POST['images'][0]."', '0', '$quantity_limited','$quantity','$special','$special_price', '$display_frontpage', '$notax', '$is_donation', '$no_shipping', '".$wpdb->escape($thumbnail_image)."', '" . $wpdb->escape($_POST['image_resize']) . "');";
		
		if($wpdb->query($insertsql)) {
			$product_id= $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".$wpdb->prefix."product_list` LIMIT 1");
			for($i=1;$i<count($_POST['images']);$i++) {
				$wpdb->query("INSERT INTO {$wpdb->prefix}product_images VALUES('','$product_id','".$_POST['images'][$i]."','0','0','$i','')");
			}
			if(function_exists('wp_insert_term')) {
				product_tag_init();
				$tags = $_POST['product_tag'];
				if ($tags!="") {
					$tags = explode(',',$tags);
					foreach($tags as $tag) {
						$tt = wp_insert_term((string)$tag, 'product_tag');
					}
					$return = wp_set_object_terms($product_id, $tags, 'product_tag');
				}
			}
		
				/* Handle new image uploads here */
			$image = wpsc_item_process_image($product_id, $_FILES['image']['tmp_name'], $_FILES['image']['name'], $_POST['width'], $_POST['height'], $_POST['image_resize']);
			
			
      if(file_exists(WPSC_THUMBNAIL_DIR.basename($_POST['images'][0])) && ($_POST['images'][0] != '')) {
        $imagepath = WPSC_IMAGE_DIR . basename($_POST['images'][0]);
        $image_output = WPSC_THUMBNAIL_DIR . basename($_POST['images'][0]);
        switch($_POST['image_resize']) {
          case 1:
          $height = get_option('product_image_height');
          $width  = get_option('product_image_width');
          break;
  
          case 2:
          $height = (int)$_POST['height'];
          $width  = (int)$_POST['width'];
          break;
				}
        image_processing($imagepath, $image_output, $width, $height);
				update_product_meta($product_id, 'thumbnail_width', $width);
				update_product_meta($product_id, 'thumbnail_height', $height);
			}
	
	
			/* Process extra meta values */
			if($_POST['productmeta_values'] != null) {
				foreach((array)$_POST['productmeta_values'] as $key => $value) {
					if(get_product_meta($product_id, $key) != false) {
						update_product_meta($product_id, $key, $value);
					} else {
						add_product_meta($product_id, $key, $value);
					}
				}
			}
			
			if($_FILES['pdf'] != null) {
				foreach($_FILES['pdf'] as $key => $pdf) {
					move_uploaded_file($_FILES['pdf'][$key]['tmp_name'], WPSC_PREVIEW_DIR.$_FILES['pdf'][$key]['name']);
					$pdf_names[] = $_FILES['pdf'][$key]['name'];
				}
				if (get_product_meta($product_id,'pdf') != false) {
					update_product_meta($product_id,'pdf',$pdf_names);
				} else {
					add_product_meta($product_id,'pdf',$pdf_names);
				}
			}
			
			if($_POST['new_custom_meta'] != null) {
				foreach((array)$_POST['new_custom_meta']['name'] as $key => $name) {
					$value = $_POST['new_custom_meta']['value'][(int)$key];
					if(($name != '') && ($value != '')) {
						add_product_meta($product_id, $name, $value, false, true);
					}
				}
			}
			
			do_action('wpsc_product_form_submit', $product_id);
			
			/* Add tidy url name */
			$tidied_name = trim($_POST['name']);
			$tidied_name = strtolower($tidied_name);
			$url_name = preg_replace(array("/(\s)+/","/[^\w-]+/i"), array("-", ''), $tidied_name);
			$similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '$url_name', '')) AS `max_number` FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `meta_key` IN ('url_name') AND `meta_value` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
			$extension_number = '';
			if($similar_names['count'] > 0) {
				$extension_number = (int)$similar_names['max_number']+1;
				}
			$url_name .= $extension_number;
			add_product_meta($product_id, 'url_name', $url_name,true);
			
			if(($_FILES['extra_image'] != null) && function_exists('edit_submit_extra_images')) {
				$var = edit_submit_extra_images($product_id);
			}
			
			$variations_processor = new nzshpcrt_variations;
			if($_POST['variations'] != null) {
			
        foreach((array)$_POST['variations'] as $variation_id => $state) {
          $variation_id = (int)$variation_id;
          if($state == 1) {
            $variation_values = $variations_processor->falsepost_variation_values($variation_id);
            $variations_processor->add_to_existing_product($product_id,$variation_values);
          }
        }
			}

				
			if($_POST['variation_priceandstock'] != null) {
				$variations_processor->update_variation_values($product_id, $_POST['variation_priceandstock']);
	// 			  exit("<pre>".print_r($_POST,true)."</pre>");
			}
			
			
				//$variations_procesor->edit_add_product_values($_POST['prodid'],$_POST['edit_add_variation_values']);
			$counter = 0;
			$item_list = '';
			if(count($_POST['category']) > 0) {
				foreach($_POST['category'] as $category_id) {
					$check_existing = $wpdb->get_var("SELECT `id` FROM `".$wpdb->prefix."item_category_associations` WHERE `product_id` = ".$product_id." AND `category_id` = '$category_id' LIMIT 1");
					if($check_existing == null) {
						$wpdb->query("INSERT INTO `".$wpdb->prefix."item_category_associations` ( `product_id` , `category_id` ) VALUES ( '".$product_id."', '".$category_id."');");        
					}
				}
			}
			// send the pings out.
		 wpsc_ping();		
			
			$display_added_product = "filleditform(".$product_id.");";
			
			echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASBEENADDED."</p></div>";
		} else {
			echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASNOTBEENADDED."</p></div>";
		}
	} else {
		echo "<div class='updated'><p align='center'>".TXT_WPSC_MAX_PRODUCTS."</p></div>";
	}
}

if($_GET['submit_action'] == "remove_set")
  {
  if(is_numeric($_GET['product_id']) && is_numeric($_GET['variation_assoc_id']))
    {
    $product_id = $_GET['product_id'];
    $variation_assoc_id = $_GET['variation_assoc_id'];
    $check_association_id = $wpdb->get_var("SELECT `id` FROM `".$table_prefix."variation_associations` WHERE `id` = '$variation_assoc_id' LIMIT 1");
    if(($variation_assoc_id > 0) && ($variation_assoc_id == $check_association_id))
      {
      $variation_association = $wpdb->get_row("SELECT * FROM `".$table_prefix."variation_associations` WHERE `id` = '$variation_assoc_id' LIMIT 1",ARRAY_A);
      $delete_variation_sql = "DELETE FROM `".$table_prefix."variation_associations` WHERE `id` = '$variation_assoc_id' LIMIT 1";
      $wpdb->query($delete_variation_sql);
      //echo("<pre>".print_r($variation_association,true)."</pre>");
      if($variation_association != null)
        {
        $variation_id = $variation_association['variation_id'];
        $delete_value_sql = "DELETE FROM `".$table_prefix."variation_values_associations` WHERE `product_id` = '$product_id' AND `variation_id` = '$variation_id'";
        //exit($delete_value_sql);
        $wpdb->query($delete_value_sql);
        }
      echo "<div class='updated'><p align='center'>".TXT_WPSC_PRODUCTHASBEENEDITED."</p></div>";
      }
    } 
  }

if($_POST['submit_action'] == "edit") {
//   exit("<pre>".print_r($_POST,true)."</pre>");
  $id = $_POST['prodid'];
  if(function_exists('edit_submit_extra_images'))
    {
    if(($_FILES['extra_image'] != null))
      {
      $var = edit_submit_extra_images($id);
      }
    }
  if(function_exists('edit_extra_images'))
    {
    $var = edit_extra_images($id);
    } 
    
	$file_name = null;
	if($_POST['file_url'] != null) {
	$url_array = array_reverse(explode("/",$_POST['file_url']));
	//exit("<pre>".print_r($url_array,true)."</pre>");
	if(is_file(WPSC_FILE_DIR.$url_array[0])) {
		$file_name = $url_array[0];
		}
	}
  
	//written by allen
	if(isset($_POST['product_tags'])) {
		$imtags = $_POST['product_tags'];
		$tags = explode(',',$imtags);
		product_tag_init();
		if(is_array($tags)) {
			foreach((array)$tags as $tag){
				$tt = wp_insert_term((string)$tag, 'product_tag');
			}
		}
		wp_set_object_terms($id, $tags, 'product_tag');
	}
	//end of written by allen

	if (isset($_POST['external_link'])) {
		add_product_meta($_POST['prodid'], 'external_link', $_POST['external_link'],true);
	}
	
	if (isset($_POST['images'])) {
		$id = (int)$_POST['prodid'];
		$old_image = $wpdb->get_var("SELECT image FROM {$wpdb->prefix}product_list WHERE id='{$_POST['prodid']}'");
		if ($old_image == ''){
			$updatelink_sql = "UPDATE `".$wpdb->prefix."product_list` SET `image` = '{$_POST['images'][0]}' WHERE `id` = '$id'";
			$updatelink_data = $wpdb->query($updatelink_sql);
		}
	}
	
	if (isset($_POST['merchant_notes'])) {
		$id = (int)$_POST['prodid'];
		$notes = $_POST['merchant_notes'];
		$updatelink_sql = "SELECT * FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `product_id` = '$id' AND `meta_key`='merchant_notes'";
		$updatelink_data = $wpdb->get_results($updatelink_sql, ARRAY_A);
		if (count($updatelink_data)>0){
			$updatelink_sql = "UPDATE `".$wpdb->prefix."wpsc_productmeta` SET `meta_value` = '$notes' WHERE `product_id` = '$id' AND `meta_key`='merchant_notes'";
			$updatelink_data = $wpdb->query($updatelink_sql);
		} else {
			$updatelink_sql = "INSERT INTO `".$wpdb->prefix."wpsc_productmeta` (`product_id`,`meta_key`,`meta_value`) VALUES('$id','merchant_notes' ,'$notes')";
			$updatelink_data = $wpdb->query($updatelink_sql);
		}
	}
	
// 	if (isset($_POST['engrave'])) {
// 		$id = $_POST['prodid'];
// 		$engrave = $_POST['engrave'];
// 		$updatelink_sql = "SELECT * FROM ".$wpdb->prefix."wpsc_productmeta WHERE product_id = $id AND meta_key='merchant_notes'";
// 		$updatelink_data = $wpdb->get_results($updatelink_sql, ARRAY_A);
// 		if (count($updatelink_data)>0){
// 			$updatelink_sql = "UPDATE ".$wpdb->prefix."wpsc_productmeta SET meta_value = '$notes' WHERE product_id = $id AND meta_key='merchant_notes'";
// 			$updatelink_data = $wpdb->query($updatelink_sql);
// 		} else {
// 			$updatelink_sql = "INSERT INTO ".$wpdb->prefix."wpsc_productmeta VALUES('',$id,'merchant_notes' ,'$notes')";
// 			$updatelink_data = $wpdb->query($updatelink_sql);
// 		}
// 	}
  
  /* handle editing file uploads here */
		if(!empty($_FILES['file']['name'])) {
			$fileid = wpsc_item_process_file('edit');
			$file = $fileid;
		} else if (($_POST['select_product_file'] != '')) {
			$fileid = wpsc_item_reassign_file($_POST['select_product_file'], 'edit');
			$file = $fileid;
		}


		if($_FILES['pdf'] != null) {
			foreach($_FILES['pdf'] as $key => $pdf){
				move_uploaded_file($_FILES['pdf'][$key]['tmp_name'], WPSC_PREVIEW_DIR.$_FILES['pdf'][$key]['name']);
				$pdf_names[]=$_FILES['pdf'][$key]['name'];
			}
			update_product_meta($product_id,'pdf',$pdf_names);
		}

		if(file_exists($_FILES['preview_file']['tmp_name'])) {
			$fileid = $wpdb->get_var("SELECT `file` FROM `".$wpdb->prefix."product_list` WHERE `id` = '$id' LIMIT 1");
			copy($_FILES['preview_file']['tmp_name'], (WPSC_PREVIEW_DIR.basename($_FILES['preview_file']['name'])));
			$mimetype = wpsc_get_mimetype(WPSC_PREVIEW_DIR.basename($_FILES['preview_file']['name']));
			$wpdb->query("UPDATE `".$wpdb->prefix."product_files` SET `preview` = '".$wpdb->escape(basename($_FILES['preview_file']['name']))."', `preview_mimetype` = '".$mimetype."' WHERE `id` = '$fileid' LIMIT 1");
		}

  /* Handle new image uploads here */
  $image = wpsc_item_process_image($_POST['prodid'], $_FILES['image']['tmp_name'], $_FILES['image']['name'], $_POST['width'], $_POST['height'], $_POST['image_resize']);


  if(is_numeric($_POST['prodid'])) {
		if(($_POST['image_resize'] == 1 || $_POST['image_resize'] == 2) && ($image == '')) {
      /*  resize the image if directed to do so and no new image is supplied  */
      $image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".$wpdb->prefix."product_list` WHERE `id`=".$_POST['prodid']." LIMIT 1",ARRAY_A);      
      // prevent images from being replaced by those from other products
      $check_multiple_use = $wpdb->get_var("SELECT COUNT(`image`) AS `count` FROM `".$wpdb->prefix."product_list` WHERE `image`='".$image_data['image']."'");
      if($check_multiple_use > 1) {
        $new_filename = $image_data['id']."_".$image_data['image'];
        if(file_exists(WPSC_THUMBNAIL_DIR.$image_data['image']) && ($image_data['image'] != null)) {
          copy(WPSC_THUMBNAIL_DIR.$image_data['image'], WPSC_THUMBNAIL_DIR.$new_filename);
          }
        if(file_exists(WPSC_IMAGE_DIR.$image_data['image']) && ($image_data['image'] != null)) {
          copy(WPSC_IMAGE_DIR.$image_data['image'], WPSC_IMAGE_DIR.$new_filename);
          }
        $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `image` = '".$new_filename."' WHERE `id`='".$image_data['id']."' LIMIT 1");
        $image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".$wpdb->prefix."product_list` WHERE `id`=".$_POST['prodid']." LIMIT 1",ARRAY_A);
        }
        
        
      if(file_exists(WPSC_THUMBNAIL_DIR.$image_data['image']) && ($image_data['image'] != '')) {
        $imagepath = WPSC_IMAGE_DIR . $image_data['image'];
        $image_output = WPSC_THUMBNAIL_DIR . $image_data['image'];
        switch($_POST['image_resize']) {
          case 1:
          $height = get_option('product_image_height');
          $width  = get_option('product_image_width');
          break;
  
          case 2:
          $height = $_POST['height'];
          $width  = $_POST['width'];
          break;
				}
			image_processing($imagepath, $image_output, $width, $height);
				update_product_meta($id, 'thumbnail_width', $width);
				update_product_meta($id, 'thumbnail_height', $height);
			}
    }
    
    if(is_numeric($_POST['prodid'])) {
      $counter = 0;
      $item_list = '';
      if(count($_POST['category']) > 0) {
        foreach($_POST['category'] as $category_id) {
          $category_id = (int)$category_id; // force it to be an integer rather than check if it is one
          $check_existing = $wpdb->get_var("SELECT `id` FROM `".$wpdb->prefix."item_category_associations` WHERE `product_id` = ".$id." AND `category_id` = '$category_id' LIMIT 1");
          if($check_existing == null) {
            $wpdb->query("INSERT INTO `".$wpdb->prefix."item_category_associations` ( `product_id` , `category_id` ) VALUES ('".$id."', '".$category_id."');");        
					}
          if($counter > 0) {
            $item_list .= ", ";
					}
          $item_list .= "'".$category_id."'";
          $counter++;
				}
			} else {
				$item_list = "'0'";
			}
      $wpdb->query("DELETE FROM `".$wpdb->prefix."item_category_associations` WHERE `product_id`= '$id' AND `category_id` NOT IN (".$item_list.")"); 
		}
      
		$key = Array();
		
         
		if(is_numeric((int)$_POST['quantity']) && ($_POST['quantity_limited'] == "yes")){
			$quantity_limited = 1;
			$quantity = $_POST['quantity'];
		} else {
			$quantity_limited = 0;
			$quantity = 0;
		}
       
    if($_POST['special'] == 'yes') {
      $special = 1;
			if(is_numeric($_POST['special_price'])) {
				$special_price = $_POST['price'] - $_POST['special_price'];
				}
      } else {
        $special = 0;
        $special_price = '';
			}
  
    if($_POST['notax'] == 'yes') {
      $notax = 1;
		} else {
			$notax = 0;
		}

      
		if($_POST['display_frontpage'] == "yes") {
			$display_frontpage = 1;
		} else {
			$display_frontpage = 0;
		}
   
		if($_POST['donation'] == "yes") {
			$is_donation = 1;
		} else {
			$is_donation = 0;
		}
   
		if($_POST['no_shipping'] == "yes") {
			$no_shipping = 1;
		} else {
			$no_shipping = 0;
		}
		
		$updatesql = "UPDATE `".$wpdb->prefix."product_list` SET `name` = '".$wpdb->escape($_POST['title'])."', `description` = '".$wpdb->escape($_POST['description'])."', `additional_description` = '".$wpdb->escape($_POST['additional_description'])."', `price` = '".$wpdb->escape(str_replace(",","",$_POST['price']))."', `pnp` = '".(float)$wpdb->escape($_POST['pnp'])."', `international_pnp` = '".(float)$wpdb->escape($_POST['international_pnp'])."', `brand` = '0', quantity_limited = '".$quantity_limited."', `quantity` = '".(int)$quantity."', `special`='$special', `special_price`='$special_price', `display_frontpage`='$display_frontpage', `notax`='$notax', `donation`='$is_donation', `no_shipping` = '$no_shipping', `weight` = '".$wpdb->escape($_POST['weight'])."', `weight_unit` = '".$wpdb->escape($_POST['weight_unit'])."'  WHERE `id`='".$_POST['prodid']."' LIMIT 1";

		$wpdb->query($updatesql);
		if(($_FILES['image']['name'] != null) && ($image != null)) {
			$wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `image` = '".$image."' WHERE `id`='".$_POST['prodid']."' LIMIT 1");
		}
    
    if($_POST['productmeta_values'] != null) {
      foreach((array)$_POST['productmeta_values'] as $key => $value) {
        if(get_product_meta($_POST['prodid'], $key) != false) {
          update_product_meta($_POST['prodid'], $key, $value);
				} else {
          add_product_meta($_POST['prodid'], $key, $value);
				}
			}
		}

    if($_POST['new_custom_meta'] != null) {
      foreach((array)$_POST['new_custom_meta']['name'] as $key => $name) {
				$value = $_POST['new_custom_meta']['value'][(int)$key];
        if(($name != '') && ($value != '')) {
					add_product_meta($_POST['prodid'], $name, $value, false, true);
        }
			}
		}
		
		
    if($_POST['custom_meta'] != null) {
      foreach((array)$_POST['custom_meta'] as $key => $values) {
        if(($values['name'] != '') && ($values['value'] != '')) {
          $wpdb->query("UPDATE `".$wpdb->prefix."wpsc_productmeta` SET `meta_key` = '".$wpdb->escape($values['name'])."', `meta_value` = '".$wpdb->escape($values['value'])."' WHERE `id` IN ('".(int)$key."')LIMIT 1 ;");
         // echo "UPDATE `".$wpdb->prefix."wpsc_productmeta` SET `meta_key` = '".$wpdb->escape($values['name'])."', `meta_value` = '".$wpdb->escape($values['value'])."' WHERE `id` IN ('".(int)$key."') LIMIT 1 ;";
					//add_product_meta($_POST['prodid'], $values['name'], $values['value'], false, true);
        }
			}
		}




    do_action('wpsc_product_form_submit', $product_id);
    
    /* Add or edit tidy url name */
    $tidied_name = trim($_POST['title']);
    $tidied_name = strtolower($tidied_name);
    $url_name = preg_replace(array("/(\s)+/","/[^\w-]+/i"), array("-", ''), $tidied_name);
    $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '$url_name', '')) AS `max_number` FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `meta_key` IN ('url_name') AND `meta_value` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
    $extension_number = '';
    if($similar_names['count'] > 0) {
      $extension_number = (int)$similar_names['max_number']+1;
		}
		
    $stored_name = get_product_meta($_POST['prodid'], 'url_name', true);
    if(get_product_meta($_POST['prodid'], 'url_name', true) != false) {
      $current_url_name = get_product_meta($_POST['prodid'], 'url_name');
      if($current_url_name[0] != $url_name) {
        $url_name .= $extension_number;
        update_product_meta($_POST['prodid'], 'url_name', $url_name);
			}
		} else {
      $url_name .= $extension_number;
      add_product_meta($_POST['prodid'], 'url_name', $url_name, true);
		}
    
    /* update thumbnail images */
    if(!($thumbnail_image == null && $_POST['image_resize'] == 3 && $_POST['current_thumbnail_image'] != null)) {
      if($thumbnail_image != null) {
        $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `thumbnail_image` = '".$thumbnail_image."' WHERE `id`='".(int)$_POST['prodid']."' LIMIT 1");
			}
		}
    
    
		$thumbnail_state = $wpdb->get_var("SELECT `thumbnail_state` FROM `".$wpdb->prefix."product_list` WHERE `id`=".(int)$_POST['prodid']." LIMIT 1",ARRAY_A);      
    
    
		$image_resize = (int)$_POST['image_resize'];
		if(!is_numeric($image_resize) || ($image_resize < 1)) {
			$image_resize = 0;
		}
    if(($image_resize == 0) && ($thumbnail_state != 0)) {
			$wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `thumbnail_state` = '".$image_resize."' WHERE `id`='".(int)$_POST['prodid']."' LIMIT 1");
    }
    
    if($_POST['deleteimage'] == 1) {
			$wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `image` = ''  WHERE `id`='".(int)$_POST['prodid']."' LIMIT 1");
		}
     
		$variations_procesor = new nzshpcrt_variations;
		if($_POST['variation_values'] != null) {
			//$variations_procesor->add_to_existing_product($_POST['prodid'],$_POST['variation_values']);
		}
		
		if($_POST['edit_variation_values'] != null) {
			$variations_procesor->edit_product_values($_POST['prodid'],$_POST['edit_variation_values']);
		}
		
		if($_POST['edit_add_variation_values'] != null) {
			$variations_procesor->edit_add_product_values($_POST['prodid'],$_POST['edit_add_variation_values']);
		}
			
		if($_POST['variation_priceandstock'] != null) {
			$variations_procesor->update_variation_values($_POST['prodid'], $_POST['variation_priceandstock']);
		}     
		
		// send the pings out.
		wpsc_ping();
		
		echo "<div class='updated'><p align='center'>".TXT_WPSC_PRODUCTHASBEENEDITED."</p></div>";
	}
}

if(is_numeric($_GET['deleteid'])) { 
  	$wpdb->query("DELETE FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `product_id` = '".$_GET['deleteid']."' AND `meta_key` IN ('url_name')");  
  	$wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET  `active` = '0' WHERE `id`='".$_GET['deleteid']."' LIMIT 1");
	product_tag_init();
	$term = wp_get_object_terms($_GET['deleteid'], 'product_tag');
	if ($term->errors == '')
		wp_delete_object_term_relationships($_GET['deleteid'], 'product_tag');
}

/*
 * Sort out the searching of the products
 */
if($_GET['search_products']) {
	$search_string_title = "%".$wpdb->escape(stripslashes($_GET['search_products']))."%";
	$search_string_description = "% ".$wpdb->escape(stripslashes($_GET['search_products']))."%";
	
	$search_sql = "AND (`".$wpdb->prefix."product_list`.`name` LIKE '".$search_string_title."' OR `".$wpdb->prefix."product_list`.`description` LIKE '".$search_string_description."')";
	
	$search_string = $_GET['search_products'];
} else {
  $search_sql = '';
  $search_string = '';
}



/*
 * Gets the product list, commented to make it stick out more, as it is hard to notice 
 */
if(is_numeric($_GET['catid'])) {    // if we are getting items from only one category, this is a monster SQL query to do this with the product order
  $sql = "SELECT `".$wpdb->prefix."product_list`.`id` , `".$wpdb->prefix."product_list`.`name` , `".$wpdb->prefix."product_list`.`price` , `".$wpdb->prefix."product_list`.`image`, `".$wpdb->prefix."item_category_associations`.`category_id`,`".$wpdb->prefix."product_order`.`order`, IF(ISNULL(`".$wpdb->prefix."product_order`.`order`), 0, 1) AS `order_state`
FROM `".$wpdb->prefix."product_list` 
LEFT JOIN `".$wpdb->prefix."item_category_associations` ON `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` 
LEFT JOIN `".$wpdb->prefix."product_order` ON ( (
`".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."product_order`.`product_id` 
)
AND (
`".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_order`.`category_id` 
) ) 
WHERE `".$wpdb->prefix."product_list`.`active` = '1' $search_sql
AND `".$wpdb->prefix."item_category_associations`.`category_id` 
IN (
'".$_GET['catid']."'
)
ORDER BY `order_state` DESC,`".$wpdb->prefix."product_order`.`order` ASC,  `".$wpdb->prefix."product_list`.`id` DESC";

  } else {
		$itempp = 20;
		if ($_GET['pnum']!='all') {
			$page = (int)$_GET['pnum'];
			
			$start = $page * $itempp;
			$sql = "SELECT DISTINCT * FROM `{$wpdb->prefix}product_list` WHERE `active`='1' $search_sql LIMIT $start,$itempp";
		} else {
			$sql = "SELECT DISTINCT * FROM `{$wpdb->prefix}product_list` WHERE `active`='1' $search_sql";
		}
	}  
    
$product_list = $wpdb->get_results($sql,ARRAY_A);
$num_products = $wpdb->get_var("SELECT COUNT(DISTINCT `id`) FROM `".$wpdb->prefix."product_list` WHERE `active`='1' $search_sql");

/*
 * The product list is stored in $product_list now
 */
 
 /*
  * Detects if the directories for images, thumbnails and files are writeable, if they are not, tells the user to make them writeable.
 */
 
  $unwriteable_directories = Array();
  
  if(!is_writable(WPSC_FILE_DIR)) {
    $unwriteable_directories[] = WPSC_FILE_DIR;
	}
  
  if(!is_writable(WPSC_PREVIEW_DIR)) {
    $unwriteable_directories[] = WPSC_PREVIEW_DIR;
	}
 
  if(!is_writable(WPSC_IMAGE_DIR)) {
    $unwriteable_directories[] = WPSC_IMAGE_DIR;
	}
  
  if(!is_writable(WPSC_THUMBNAIL_DIR)) {
    $unwriteable_directories[] = WPSC_THUMBNAIL_DIR;
	}
  
  if(!is_writable(WPSC_CATEGORY_DIR)) {
    $unwriteable_directories[] = WPSC_CATEGORY_DIR;
	}
    
  if(count($unwriteable_directories) > 0)
    {
    echo "<div class='error'>".str_replace(":directory:","<ul><li>".implode($unwriteable_directories, "</li><li>")."</li></ul>",TXT_WPSC_WRONG_FILE_PERMS)."</div>";
    }
?>


<div class="wrap">

  <h2><?php echo TXT_WPSC_DISPLAYPRODUCTS;?></h2>

  <?php
  
  
	if(function_exists('add_object_page')) {
		echo "
			<div id='dashboard-widgets' class='metabox-holder'>";
	}
$baseurl = includes_url('js/tinymce');
?>
<script type="text/javascript" src="<?php echo $baseurl; ?>/tiny_mce.js"></script>
<script type="text/javascript" src="<?php echo $baseurl; ?>/langs/wp-langs-en.js"></script>
  <script src="../../wp-includes/js/tinymce/tiny_mce_popup.js" language="javascript" type='text/javascript' ></script>
  <script language='javascript' type='text/javascript'>
  jQuery('.hide-postbox-tog').click( function() {
	    var box = jQuery(this).val();
	    if ( jQuery(this).attr('checked') ) {
	    	jQuery('#' + box).show();
	    	if ( jQuery.isFunction( postboxes.pbshow ) )
	    		postboxes.pbshow( box );
	
	    } else {
	    	jQuery('#' + box).hide();
	    	if ( jQuery.isFunction( postboxes.pbhide ) )
	    		postboxes.pbhide( box );
	
	    }
	    postboxes.save_state('products');
	} );
	
tinyMCE.init({
	theme : "advanced",
	mode : "specific_textareas",
	width : '100%',
	height : '194px',
	skin : 'wp_theme',
	editor_selector : "mceEditor",
	plugins : "spellchecker,pagebreak",
	theme_advanced_buttons1 : "bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,pagebreak",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	content_css : WPSC_URL+"/js/tinymce3/mce.css",
	theme_advanced_resize_horizontal : false
});

	
function conf() {
  var check = confirm("<?php echo TXT_WPSC_SURETODELETEPRODUCT;?>");
  if(check) {
    return true;
  } else  {
    return false;
	}
}


<?php
if(is_numeric($_POST['prodid'])) {
		echo "filleditform(".$_POST['prodid'].");";
  }
else if(is_numeric($_GET['product_id'])) {
    echo "filleditform(".$_GET['product_id'].");";
  }
  
echo $display_added_product ;
?>
</script>

<?php
if (function_exists('add_object_page')) {
	echo "<div class='wpsc_products_nav27'>";
} else {
	echo "<div class='tablenav wpsc_products_nav'>";
}
?>
	<div style="width: 500px;" class="alignleft">
		<a href='' onclick='return showaddform()' class='add_item_link'><img src='<?php echo WPSC_URL; ?>/images/package_add.png' alt='<?php echo TXT_WPSC_ADD; ?>' title='<?php echo TXT_WPSC_ADD; ?>' />&nbsp;<span><?php echo TXT_WPSC_ADDPRODUCT;?></span></a>
		<?php
		do_action('wpsc_admin_products_tablenav');
		?>
		<?php echo topcategorylist();?>
	</div>
	
	
	<div class="alignright">
		<?php echo setting_button(); ?>
		<!-- <a target="_blank" href='http://www.instinct.co.nz/e-commerce/products/' class='about_this_page'><span><?php echo TXT_WPSC_ABOUT_THIS_PAGE;?></span>&nbsp;</a> -->
	</div>

	
	<br class="clear"/>
</div>


  <?php
$num = 0;


echo "    <table id='productpage'>\n\r";
echo "      <tr><td style='padding-right: 15px;'>\n\r";
if (function_exists('add_object_page')){
	echo "<div class='postbox'>";
	echo "<h3 class='hndle'>".TXT_WPSC_SELECT_PRODUCT."</h3>";
	echo "<div class='inside'>";
}
echo "        <table id='itemlist'>\n\r";
if (!function_exists('add_object_page')) {
	echo "          <tr class='firstrowth'>\n\r";
	echo "            <td colspan='4' style='text-align: left;'>\n\r";
	echo "<span id='loadingindicator_span' class='product_loadingindicator'><img id='loadingimage' src='".WPSC_URL."/images/grey-loader.gif' alt='Loading' title='Loading' /></span>";
	echo "<strong class='form_group'>".TXT_WPSC_SELECT_PRODUCT."</strong>";
	echo "            </td>\n\r";
	echo "          </tr>\n\r";
}
if(($num_products > 20) || ($search_string != '')) {
	echo "          <tr class='selectcategory'>\n\r";
	echo "            <td colspan='3'>\n\r";
	echo TXT_WPSC_ADMIN_SEARCH_PRODUCTS.": ";
	echo "            </td>\n\r";
	echo "            <td colspan='1'>\n\r";
	echo "<div>\n\r";
	echo "  <form method='GET' action=''>\n\r";
	echo "<input type='hidden' value='{$_GET['page']}' name='page'>";
	echo "<input type='text' value='{$search_string}' name='search_products' style='width: 115px; padding: 1px;'>";
	echo "  </form>\n\r";
	echo "</div>\n\r";
	echo "            </td>\n\r";
	echo "          </tr>\n\r";
}

if (function_exists('add_object_page')){
	//echo topcategorylist();
}else{
echo "          <tr class='selectcategory'>\n\r";
echo "            <td colspan='4'>\n\r";
echo TXT_WPSC_PLEASESELECTACATEGORY.": ";
echo "            </td>\n\r";
/*
echo "            <td colspan='1'>\n\r";
echo "<div>\n\r";
echo topcategorylist();
*/
//echo "<div style='float: right; width: 160px;'>". topcategorylist() ."</div>";
/*
echo "</div>\n\r";

echo "            </td>\n\r";
*/
echo "          </tr>\n\r";
}
if(is_numeric($_GET['catid'])) {
	$name_style = 'class="pli_name"';
	$price_style = 'class="pli_price"';
	$edit_style = 'class="pli_edit"';
} else {
	$name_style = '';
	$price_style = '';
	$edit_style = '';
}


echo "          <tr class='firstrow'>\n\r";

echo "            <td width='45px'>";
echo "<input type='checkbox' id='selectall'>";
echo "</td>\n\r";

echo "            <td ".$name_style.">";
echo TXT_WPSC_NAME;
echo "</td>\n\r";

echo "            <td ".$price_style.">";
echo TXT_WPSC_PRICE;
echo "</td>\n\r";

if(!is_numeric($_GET['catid'])) {
	echo "            <td>";
	echo TXT_WPSC_CATEGORIES;
	echo "</td>\n\r";
}

echo "          </tr>\n\r";
if(is_numeric($_GET['catid'])) {
	echo "<tr><td colspan='4'  class='category_item_container'>\n\r";
} 

if($product_list != null)
  {
  $order_number = 0;
	if(is_numeric($_GET['catid'])){
	  echo "   <form><input type='hidden' name='category_id' id='item_list_category_id' value='".(int)$_GET['catid']."'/></form>";
    echo "   <div id='sort1' class='groupWrapper'>\n\r";
  }
  $tablei=1;
  foreach($product_list as $product)
    {
    /*
     * Creates order table entries if they are not already present
     * No need for extra database queries to determine the highest order number
     * anything without one is automatically at the bottom
     * so anything with an order number is already processed by the time it starts adding rows
     */
	if(is_numeric($_GET['catid'])){
		echo "    <div id='".$product['id']."' class='groupItem'>\n\r";
		//echo "    <div class='itemHeader'></div>\n\r";
		echo "    <div class='itemContent'>\n\r";
	} else {
		if ($tablei==1) {
			echo "<tr class='products'>";
		} else {
			echo "<tr class='productsalt'>";
		}
		$tablei*=-1;
	}
	
	if(is_numeric($_GET['catid'])) {
		if($product['order_state'] > 0) {
			if($product['order'] > $order_number) {
				$order_number = $product['order'];
				$order_number++;
			}
		} else {
			$wpdb->query("INSERT INTO `".$wpdb->prefix."product_order` (  `category_id` , `product_id` , `order` ) VALUES ( '".$product['category_id']."', '".$product['id']."', '$order_number');");
			$order_number++;
		}
	}
	
	if(is_numeric($_GET['catid'])) {
    	echo "	<div class='itemHeader pli_img'>\n\r";
		echo "<a class='noline' title='Drag to a new position'>";
	} else {
		echo "	<td style='width: 18%;' class='imagecol'>\r\n";
	}
	echo "<input type='checkbox' name='productdelete[]' class='deletecheckbox' value='{$product['id']}'>";
	if(($product['thumbnail_image'] != null) && file_exists(WPSC_THUMBNAIL_DIR.$product['thumbnail_image'])) { // check for custom thumbnail images
		echo "<img title='Drag to a new position' src='".WPSC_THUMBNAIL_URL.$product['thumbnail_image']."' title='".$product['name']."' alt='".$product['name']."' width='35' height='35'  />";
	} else if(($product['image'] != null) && file_exists(WPSC_THUMBNAIL_DIR.$product['image'])) { // check for automatic thumbnail images
		echo "<img title='Drag to a new position' src='".WPSC_THUMBNAIL_URL.$product['image']."' title='".$product['name']."' alt='".$product['name']."' width='35' height='35'  />";
	} else { // no image, display this fact
		echo "<img title='Drag to a new position' src='".WPSC_URL."/no-image-uploaded.gif' title='".$product['name']."' alt='".$product['name']."' width='35' height='35' />";
	}

	echo "</a>";
  if(is_numeric($_GET['catid'])){ 
    echo "	</div>\n\r";
	} else {
	echo "</td><td width='40%'>";
	}
    
	if(is_numeric($_GET['catid'])) { 
    echo "            <div class='pli_name'>\n\r";
   }
   
	echo "<a href='#' onclick='filleditform(".$product['id'].");return false;'>";
	if ($product['name']=='') {
		echo "(".TXT_WPSC_NONAME.")";
	} else {
		echo htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8');
	}
	echo "</a>";

	
	
	if(is_numeric($_GET['catid'])){
		echo "            </div>\n\r";    
	} else {
		echo '<div class="wpsc-row-actions"><span class="edit"><a title="Edit this post" style="cursor:pointer;" onclick="filleditform('.$product['id'].');return false;">Edit</a></span> | <span class="delete"><a onclick="if ( confirm(\'Are you sure to delete this product?\') ) { return true;}return false;" href="?page=wp-shopping-cart/display-items.php&deleteid='.$product['id'].'" title="Delete this product">Delete</a></span> | <span class="view"><a target="_blank" rel="permalink" title=\'View "'.$product['name'].'"\' href="'.wpsc_product_url($product['id']).'">View</a></span> | <span class="view"><a rel="permalink" title=\'Duplicate "'.$product['name'].'"\' href="?page=wp-shopping-cart/display-items.php&duplicate='.$product['id'].'">Duplicate</a></span></div>';
		echo "</td><td id=".$product['id'].">";
	}
		if(is_numeric($_GET['catid'])){ 
			echo "            <div class='pli_price'>\n\r";
    }
    echo nzshpcrt_currency_display($product['price'], 1);
    if(is_numeric($_GET['catid'])){ 
			echo "            </div>\n\r";
    }
    
    if(!is_numeric($_GET['catid'])) {
			echo "            <td>\n\r";
	$category_list = $wpdb->get_results("SELECT `".$wpdb->prefix."product_categories`.`id`,`".$wpdb->prefix."product_categories`.`name` FROM `".$wpdb->prefix."item_category_associations` , `".$wpdb->prefix."product_categories` WHERE `".$wpdb->prefix."item_category_associations`.`product_id` IN ('".$product['id']."') AND `".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_categories`.`id` AND `".$wpdb->prefix."product_categories`.`active` IN('1')",ARRAY_A);
			$i = 0;
			foreach((array)$category_list as $category_row) {
				if($i > 0) {
					echo "<br />";
				}
				echo "<a class='category_link' href='?page=".$_GET['page']."&amp;catid=".$category_row['id']."'>".stripslashes($category_row['name'])."</a>";
				$i++;
			}        
		}
		if(!is_numeric($_GET['catid'])){
			echo "</td>";
		}    
		
   // echo "<a href='#' title='sth' onclick='filleditform(".$product['id'].");return false;'>".TXT_WPSC_EDIT."</a>";
    echo "				</div>\n\r";
		echo "            </div>\n\r";
		if(!is_numeric($_GET['catid'])){
			echo "</tr>";
		}
	}
	echo "    </div>\n\r";
	echo "</td></tr>";
	if(is_numeric($_GET['catid'])){
		//echo "<tr><td>&nbsp;&nbsp;&nbsp;<a href='#' onClick='serialize();return false;'>".TXT_WPSC_SAVE_PRODUCT_ORDER."</a></td><td></td></tr>";
	} else {
		if (isset($itempp)) {
		$num_pages = floor($num_products/$itempp);
		}
		if (!isset($_GET['pnum'])) {
			$_GET['pnum']=0;
		}
		
		if (function_exists('add_object_page')){
			echo "</table>";
			echo "<div id='major-publishing-actions' class='wpsc_delete_product'>";
		} else {
			echo "<tr class='selectcategory' style='border: none;'><td style='text-align:right;' colspan='4' width='70%'>";
		}
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'pnum', '%#%' ),
			'format' => '',
			'total' => $num_pages,
			'current' => $_GET['pnum'],
			'end_size' => 2, // How many numbers on either end including the end
			'mid_size' => 2, // How many numbers to either side of current not including current
		));
		if(function_exists('add_object_page')){
			echo "<div class='deleteproducts' style='float:left;'><button class='button-primary'>Delete</button></div>";
		} else {
			echo "<div class='deleteproducts' style='float:left;'><button class='button'>Delete</button></div>";
		}
		
			echo "<div class='tablenav-pages'>";
			
			echo $page_links;
			
// 		for ($i=0;$i<$num_pages;$i++) {
// 			$newpage=$_GET['pnum']+1;
// 			$pagenumber=$i+1;
// 			if (($i==$_GET['pnum']) && is_numeric($_GET['pnum'] )) {
// 				echo '<span class="page-numbers current">'.$pagenumber.'</span>';
// 			} else {
// 				echo "<a style='text-decoration:none;' class='page-numbers' href='?page=".$_GET['page']."&pnum=".$i."'>".$pagenumber."</a>";
// 			}
// 		}
// 		
		
		if (!isset($_GET['catid'])) {
			if ($_GET['pnum']==='all') {
				echo '<span class="page-numbers current">'.TXT_WPSC_SHOWALL.'</span>';
			} else {
				echo "<a style='text-decoration:none;' class='page-numbers' href='?page=".$_GET['page']."&pnum=all'>".TXT_WPSC_SHOWALL."</a>";
			}
			echo "</div>";
		}
		echo "</td>";
		if (!function_exists('add_object_page')){
			echo "</tr>";
		}
	}
	
	
  }
$product_data_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}product_list WHERE active='1'");
if (isset($_GET['catid'])) {
	if (($product_data_count >= 1) && (IS_WP27))
 		echo "</table>";
}
if (function_exists('add_object_page')){
	echo "</div>"; //id major-publishing-actions ends
	echo "</div>"; //class inside ends
	echo "</div>"; //class postbox ends
} else {
	echo "</table>\n\r";
}
//No product closing table fix

if (($product_data_count < 1)&& (IS_WP27)){
	echo "</table>";	
}

//First column ends here
echo "      </td><td class='secondcol'>\n\r";
echo "<form method='POST' enctype='multipart/form-data' class='edititem' name='editproduct$num'>";


/*
echo "        <table class='producttext'>\n\r"; 


echo "        </table>\n\r";
*/
echo "        <div id='formcontent' style='width:100%;'>\n\r";
echo "        </div>\n\r";
echo "</form>";
echo "</div>";
?>

<form method='POST' enctype='multipart/form-data' class='additem'>
<?php
if (function_exists('add_object_page')){
	echo "        <div id='additem'>";
	echo "        <div id='additem27' class='postbox'>";
	echo "<h3 class='hndle'>". TXT_WPSC_PRODUCTDETAILS." ".TXT_WPSC_ENTERPRODUCTDETAILSHERE."</h3>";
	
} else {
	echo "        <div id='additem'>";
	echo "<div class='categorisation_title'><strong class='form_group'>". TXT_WPSC_PRODUCTDETAILS." <span>".TXT_WPSC_ENTERPRODUCTDETAILSHERE."</span></strong></div>";
}
?>
<!-- <div class="categorisation_title"><strong class="form_group"><?php echo TXT_WPSC_PRODUCTDETAILS;?> <span><?php echo TXT_WPSC_ENTERPRODUCTDETAILSHERE;?></span></strong></div> -->

  <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
  
<?php
	if(function_exists('add_object_page')) {
		echo "<div class='inside'>";
	}
?>
  <table class='additem' style='width:100%;'>
    <tr>
      <!--
<td class='itemfirstcol'>
        <?php echo TXT_WPSC_PRODUCTNAME;?>:
      </td>
-->
      <td colspan="2" class='itemfirstcol'>
      
        <div class='admin_product_name'>
					<input size='40' class='wpsc_product_name' type='text' name='name' value='' class='text'/>
					<a href='#' class='shorttag_toggle'></a>					
					<div class='admin_product_shorttags'>
					<?php echo TXT_WPSC_NO_SHORTCODE;?>
					</div>        
        </div>
        
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol'>
        <?php echo TXT_WPSC_SKU_FULL;?> :<br />
        <input size='30' type='text' name='productmeta_values[sku]' value='' class='text' />
      </td>
      <td class='itemfirstcol'>
      	<?=TXT_WPSC_PRICE;?> :<br />
        <input size='30' type='text' name='price' value='' class='text' />
      </td>
    </tr>
    <tr>
      <!--
<td class='itemfirstcol'>
        <?php echo TXT_WPSC_PRODUCTDESCRIPTION;?>:
      </td> 
-->
      <td colspan="2" class='itemfirstcol'>
      	<div id='editorcontainer'>
        	<textarea name='description' class='mceEditor' id='description' cols='50' rows='10'></textarea>
      	</div>
      </td>
    </tr>
    <tr>
      <!--
<td class='itemfirstcol'>
       <?php echo TXT_WPSC_ADDITIONALDESCRIPTION;?>:
      </td>
-->
      <td colspan="2" class='itemfirstcol'>
      	<?php echo TXT_WPSC_ADDITIONALDESCRIPTION;?>:<br />
        <textarea name='additional_description' cols='40' rows='8'></textarea>
      </td>
    </tr>
    <!--
<tr>
      <td class='itemfirstcol'>
       <?php echo TXT_WPSC_PRODUCT_TAGS;?>:
      </td>
      <td class='itemformcol'>
        <input type='text' class='text' name='product_tag' id='product_tag'><br /><span class='small_italic'>Seperate with commas</span>
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol'>
			<?php echo TXT_WPSC_CATEGORISATION; ?>
      </td>
      <td>
        <?php
         $categorisation_groups =  $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_categorisation_groups` WHERE `active` IN ('1')", ARRAY_A);
					foreach($categorisation_groups as $categorisation_group){
					  $category_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}product_categories` WHERE `group_id` IN ('{$categorisation_group['id']}')");
					  if($category_count > 0) {
							echo "<p>";
						  $category_group_name = str_replace("[categorisation]", $categorisation_group['name'], TXT_WPSC_PRODUCT_CATEGORIES);
						  echo "<strong>".$category_group_name.":</strong><br>";
						  echo categorylist($categorisation_group['id'], false, 'add_');
						  echo "</p>";
						}
					}
				?>
      </td>
    </tr>
-->
   
<?php
//Commented out the part we wished to moved to the first box of product page.
	/*
echo " <tr>
      <td class='itemfirstcol' colspan='2'>
        <input type='checkbox' value='yes' id='add_form_display_frontpage' name='display_frontpage' ".(($product_data['display_frontpage'] == 1) ? 'checked="true"' : '')."/> 
        <label for='add_form_display_frontpage'> ".TXT_WPSC_DISPLAY_FRONT_PAGE."</label>
      </td>
    </tr>";
	echo "
	<tr>
		<td colspan='2' class='itemfirstcol'>
			<a href='#' style='font-style:normal;border-bottom:1px solid;' class='add_more_meta' onclick='return add_more_meta(this)'> + ".TXT_WPSC_ADD_CUSTOM_FIELD."</a><br><br>
		";
		foreach((array)$custom_fields as $custom_field) {
			$i = $custom_field['id'];
			// for editing, the container needs an id, I can find no other tidyish method of passing a way to target this object through an ajax request
			echo "
			<div class='product_custom_meta'  id='custom_meta_$i'>
				".TXT_WPSC_NAME."
				<input type='text' class='text'  value='{$custom_field['meta_key']}' name='custom_meta[$i][name]' id='custom_meta_name_$i'>
				
				".TXT_WPSC_DESCRIPTION."
				<textarea class='text'  value='{$custom_field['meta_value']}' name='custom_meta[$i][value]' id='custom_meta_value_$i'></textarea>
				<a href='#' class='remove_meta' onclick='return remove_meta(this, $i)'>&ndash;</a>
				<br />
			</div>
			";
		}
		
		echo "<div class='product_custom_meta'>
		".TXT_WPSC_NAME.": <br />
		<input type='text' name='new_custom_meta[name][]' value='' class='text'/><br />
		
		".TXT_WPSC_DESCRIPTION.": <br />
		<textarea name='new_custom_meta[value][]' value='' class='text' ></textarea>
 
		
		<br />";
*/




	if(function_exists('add_object_page')){
		echo "</table>
   </div></div>
<table class='additem' style='margin-top:0px;'>";
	}
?>
    
<tr><td  colspan='2'>
<?php
	if (function_exists('add_object_page')){
		echo "<div id='normal-sortables' class='meta-box-sortables'>";
	}
	$order = get_option('wpsc_product_page_order');
	if (($order == '') || ($order[0]=='') || (count($order) < 7)){
		$order=array("category_and_tag", "price_and_stock", "shipping", "variation", "advanced", "product_image", "product_download");
	}
	foreach((array)$order as $key => $box) {
		$box_function_name = $box."_box";
		$output = call_user_func($box_function_name);
		echo $output;
		if(!function_exists('add_object_page') && ($key!=count($order)-1)) {
			echo "</td></tr>";
  			echo "<tr><td colspan='2'>";
  		}
	}
	if (function_exists('add_object_page')){
		echo "</div>";
	}
?>
	
	</td></tr>
	
	
    <tr>
      <td>
      </td>
      <td>
      
      <?php
      	if (!function_exists('add_object_page')){
      		echo "<br>";
      	}
      ?>
        <input type='hidden' name='submit_action' value='add' />
        <input class='button' type='submit' name='submit' value='<?php echo TXT_WPSC_ADD_PRODUCT;?>' />
      </td>
    </tr>
  </table>
  </form>
  </div>
<?php
echo "      </td></tr>\n\r";
echo "     </table>\n\r"

  ?>
</div>
