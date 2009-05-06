<?php
/**
 * WPSC Product modifying functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
 
 /**
	* wpsc_admin_submit_product function 
	*
	* @return nothing
*/
function wpsc_admin_submit_product() {
  //echo "<pre>".print_r(wpsc_sanitise_product_forms(),true)."</pre>";
	check_admin_referer('edit-product');
  $post_data = wpsc_sanitise_product_forms();
  wpsc_insert_product($post_data, true);
  
  
	$sendback = add_query_arg('message', 1);
	wp_redirect($sendback);
	exit();
}
 
 
  /**
	* wpsc_insert_product function 
	* 
	* @return unknown
*/
function wpsc_sanitise_product_forms($post_data = null) {
	if ( empty($post_data) ) {
		$post_data = &$_POST;
	}
// 	$post_data['product_id'] = isset($post_data['product_id']) ? $post_data['product_id'] : '';
	$post_data['name'] = isset($post_data['title']) ? $post_data['title'] : '';
	$post_data['description'] = isset($post_data['content']) ? $post_data['content'] : '';
	$post_data['meta'] = isset($post_data['productmeta_values']) ? $post_data['productmeta_values'] : '';

	$post_data['quantity_limited'] = (int)(bool)$post_data['quantity_limited'];
	$post_data['special'] = (int)(bool)$post_data['special'];
	$post_data['notax'] = (int)(bool)$post_data['notax'];
	$post_data['donation'] = (int)(bool)$post_data['donation'];
	$post_data['no_shipping'] = (int)(bool)$post_data['no_shipping'];
	
	
	$post_data['files'] = $_FILES;

  return $post_data;
}
  
 /**
	* wpsc_insert_product function 
	*
	* @param unknown 
	* @return unknown
*/
function wpsc_insert_product($post_data, $wpsc_error = false) {
  global $wpdb;
  //echo "<pre>".print_r(wpsc_sanitise_product_forms(),true)."</pre>";
  $update = false;
  if((int)$post_data['product_id'] > 0) {
	  $product_id	= absint($post_data['product_id']);
    $update = true;
  }
  
  $product_columns = array(
		'name' => '',
		'description' => '',
		'additional_description' => '',
		'price' => null,
		'weight' => null,
		'weight_unit' => '',
		'pnp' => null,
		'international_pnp' => null,
		'file' => null,
		'image' => '',
		'quantity_limited' => '',
		'quantity' => null,
		'special' => null,
		'special_price' => null,
		'display_frontpage' => null,
		'notax' => null,
		'publish' => null,
		'active' => null,
		'donation' => null,
		'no_shipping' => null,
		'thumbnail_image' => null,
		'thumbnail_state' => null
  );
  
  foreach($product_columns as $column => $default) {
    if($post_data[$column]) {
			$update_values[$column] = stripslashes($post_data[$column]);
    } else if(($update != true) && ($default !== null)) {
			$update_values[$column] = stripslashes($default);
    }
  }
  if($update === true) {
		$where = array( 'id' => $product_id );
		if ( false === $wpdb->update( WPSC_TABLE_PRODUCT_LIST, $update_values, $where ) ) {
			if ( $wpsc_error ) {
				return new WP_Error('db_update_error', __('Could not update product in the database'), $wpdb->last_error);
			} else {
				return false;
			}
		}			
  } else {  
		if ( false === $wpdb->insert( WPSC_TABLE_PRODUCT_LIST, $update_values ) ) {
			if ( $wp_error ) {
				return new WP_Error('db_insert_error', __('Could not insert product into the database'), $wpdb->last_error);
			} else {
				return 0;
			}
		}
		$product_id = (int) $wpdb->insert_id;
  }
  
	// if we succeed, we can do further editing
	
	// update the categories
	wpsc_update_category_associations($product_id, $post_data['category']);
	
	// and the tags
	wpsc_update_product_tags($product_id, $post_data['product_tags']);
	
	// and the meta
	wpsc_update_product_meta($product_id, $post_data['meta']);
	
	// and the images
	wpsc_update_product_images($product_id, $post_data);
	
	if($post_data['files']['file']['tmp_name'] != '') {
		wpsc_item_process_file($product_id, $post_data['files']['file'], $post_data['files']['preview_file']);
	}
	
}


/**
 * wpsc_update_categories function 
 *
 * @param integer product ID
 * @param array submitted categories
 */
function wpsc_update_category_associations($product_id, $categories = array()) {
  global $wpdb;
  
  $associated_categories = $wpdb->get_col($wpdb->prepare("SELECT `category_id` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` IN('%s')", $product_id));
  
  $categories_to_add = array_diff($categories, $associated_categories);
  $categories_to_delete = array_diff($associated_categories, $categories);
  $insert_sections = array();
  foreach($categories_to_add as $category_id) {
    $insert_sections[] = $wpdb->prepare("( %d, %d)", $product_id, $category_id);
  }
  if(count($insert_sections)) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` (`product_id`, `category_id`) VALUES ".implode(", ",$insert_sections)."");
  }
  
  if(count($categories_to_delete) > 0) {
    $wpdb->query($wpdb->prepare("DELETE FROM`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `product_id` = %d AND `category_id` IN(%s)",$product_id,  implode($categories_to_delete)));
  
  }
}
  
  /**
 * wpsc_update_product_tags function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_tags($product_id, $product_tags) {

	if(isset($product_tags)) {
		$tags = explode(',',$product_tags);
		product_tag_init();
		if(is_array($tags)) {
			foreach((array)$tags as $tag){
				$tt = wp_insert_term((string)$tag, 'product_tag');
			}
		}
		wp_set_object_terms($product_id, $tags, 'product_tag');
	}
}

 /**
 * wpsc_update_product_tags function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_update_product_meta($product_id, $product_meta) {
    if($product_meta != null) {
      foreach((array)$product_meta as $key => $value) {
        if(get_product_meta($product_id, $key) != false) {
          update_product_meta($product_id, $key, $value);
				} else {
          add_product_meta($product_id, $key, $value);
				}
			}
		}
}


/**
* wpsc_update_product_tags function 
*
* @param integer product ID
* @param array the post data
*/
function wpsc_update_product_images($product_id, $post_data) {
  global $wpdb;

/* Handle new image uploads here */
  if($post_data['files']['image']['tmp_name'] != '') {
		$image = wpsc_item_process_image($product_id, $post_data['files']['image']['tmp_name'], str_replace(" ", "_", $post_data['files']['image']['name']), $post_data['width'], $post_data['height'], $post_data['image_resize']);
		
		$image_action = absint($post_data['image_resize']);
		$image_width = $post_data['width'];
		$image_height = $post_data['height'];
	} else {
		$image_action = absint($post_data['gallery_resize']);
		$image_width = $post_data['gallery_width'];
		$image_height = $post_data['gallery_height'];
	}
	
  //echo "<pre>".print_r($post_data['gallery_width'], true)."</pre>";
  //exit( "<pre>".print_r($image_width, true)."</pre>");
	wpsc_resize_image_thumbnail($product_id, $image_action, $image_width, $image_height);
	
	


}

 /**
 * wpsc_resize_image_thumbnail function 
 *
 * @param integer product ID
 * @param integer the action to perform on the image
 * @param integer the width of the thumbnail image
 * @param integer the height of the thumbnail image
 * @param array the custom image array from $_FILES
 */
function wpsc_resize_image_thumbnail($product_id, $image_action= 0, $width = 0, $height = 0, $custom_image = null) {
  global $wpdb;
	$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1");
	// check if there is an image that is supposed to be there.
	if($image != '') {
	  // check that is really there
	  if(file_exists(WPSC_IMAGE_DIR.$image)) {
			// if the width or height is less than 1, set the size to the default
	    if(($width  < 1) || ($height < 1)) {
	      $image_action = 1;
	    }
	  
			switch($image_action) {
				case 0:
					if(!file_exists(WPSC_THUMBNAIL_DIR.$image)) {
						copy(WPSC_IMAGE_DIR.$image, WPSC_THUMBNAIL_DIR.$image);
					}
				break;
					
				
				case 1:
				  // if case 1, replace the provided size with the default size
					$height = get_option('product_image_height');
					$width  = get_option('product_image_width');				
				case 2:
				  // if case 1, use the provided size
					$image_input = WPSC_IMAGE_DIR . $image;
					$image_output = WPSC_THUMBNAIL_DIR . $image;
					image_processing($image_input, $image_output, $width, $height);
					update_product_meta($product_id, 'thumbnail_width', $width);
					update_product_meta($product_id, 'thumbnail_height', $height);
				break;
				
				case 3:
				  // replacing the thumbnail with a custom image is done here
				break;
			}
			
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `thumbnail_state` = '$image_action' WHERE `id`='{$product_id}' LIMIT 1");
		} else {
			//if it is not, we need to unset the associated image
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '' WHERE `id`='{$product_id}' LIMIT 1");
		}
	}

}




 /**
 * wpsc_upload_image_thumbnail function 
 *
 * @param integer product ID
 * @param string comma separated tags
 */
function wpsc_upload_image_thumbnail($product_id, $product_meta) {
		if(($_POST['image_resize'] == 3) && ($_FILES['thumbnailImage'] != null) && file_exists($_FILES['thumbnailImage']['tmp_name'])) {
			$imagefield='thumbnailImage';
			$image=image_processing($_FILES['thumbnailImage']['tmp_name'], WPSC_THUMBNAIL_DIR.$_FILES['thumbnailImage']['name'],null,null,$imagefield);
			$thumbnail_image = $image;
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `thumbnail_image` = '".$thumbnail_image."' WHERE `id` = '".$image_data['id']."'");
			$stat = stat( dirname( (WPSC_THUMBNAIL_DIR.$image_data['image']) ));
			$perms = $stat['mode'] & 0000775;
			@ chmod( (WPSC_THUMBNAIL_DIR.$image_data['image']), $perms );	
		}
}


 /**
 * wpsc_item_process_file function 
 *
 * @param integer product ID
 * @param array the file array from $_FILES 
 * @param array the preview file array from $_FILES
 */
function wpsc_item_process_file($product_id, $submitted_file, $preview_file = null) {
  global $wpdb;
	$files = $wpdb->get_results("SELECT * FROM ".WPSC_TABLE_PRODUCT_FILES." ORDER BY id ASC", ARRAY_A);
	
	
	
	if (is_array($files)){
		foreach($files as $file){
			$file_names[] = $file['filename'];
			$file_hashes[] = $file['idhash'];
		}
	}
		
	if(apply_filters( 'wpsc_filter_file', $submitted_file['tmp_name'] )) {
	  // initialise $idhash to null to prevent issues with undefined variables and error logs
	  $idhash = null;
		$fileid_data = $wpdb->get_results("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
		/* if we are adding, make a new file row and get the ID of it */
		$timestamp = time();
		$query_results = $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_FILES."` ( `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '', '', '', '$timestamp');");
		$fileid = $wpdb->get_var("SELECT LAST_INSERT_ID() FROM `".WPSC_TABLE_PRODUCT_FILES."`");
			
			
		/* if there is no idhash, generate it */
		if($idhash == null) {
			$idhash = sha1($fileid);
			if($idhash == '') {
			  // if sha1 doesnt spit an error, but doesnt return anything either (it has done so on some servers)
				$idhash = md5($fileid);
			}
		}
		// if needed, we can add code here to stop hash doubleups in the unlikely event that they shoud occur
	
		$mimetype = wpsc_get_mimetype($submitted_file['tmp_name']);
		
		$filename = basename($submitted_file['name']);
		
		
		if (in_array($submitted_file['name'],(array)$file_names)){
			$i=0;
			$new_name = $submitted_file['name'].".old";
			while(file_exists(WPSC_FILE_DIR.$new_name)){
				$new_name = $submitted_file['name'].".old_".$i;
				$i++;
			}
			$old_idhash_id = array_search($submitted_file['name'],(array)$file_names);
			$old_idhash = $file_hashes[$old_idhash_id];
			while(!file_exists(WPSC_FILE_DIR.$old_idhash)){
				unset($file_hashes[$old_idhash_id]);
				unset($file_names[$old_idhash_id]);
				
				$old_idhash_id = array_search($submitted_file['name'],(array)$file_names);
				$old_idhash = $file_hashes[$old_idhash_id];
			}
			copy(WPSC_FILE_DIR.$old_idhash, WPSC_FILE_DIR.$new_name);
			unlink(WPSC_FILE_DIR.$old_idhash);
		}
		if(move_uploaded_file($submitted_file['tmp_name'],(WPSC_FILE_DIR.$idhash)))	{
			$stat = stat( dirname( (WPSC_FILE_DIR.$idhash) ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( (WPSC_FILE_DIR.$idhash), $perms );	
			if(function_exists("make_mp3_preview"))	{
				if($mimetype == "audio/mpeg" && (!isset($preview_file['tmp_name']))) {
				  // if we can generate a preview file, generate it (most can't due to sox being rare on servers and sox with MP3 support being even rarer), thus this needs to be enabled by editing code
					make_mp3_preview((WPSC_FILE_DIR.$idhash), (WPSC_PREVIEW_DIR.$idhash.".mp3"));
					$preview_filepath = (WPSC_PREVIEW_DIR.$idhash.".mp3");
				} else if(file_exists($preview_file['tmp_name'])) {    
					$preview_filename = basename($preview_file['name']);
					$preview_mimetype = wpsc_get_mimetype($preview_file['tmp_name']);
					copy($preview_file['tmp_name'], (WPSC_PREVIEW_DIR.$preview_filename));
					$preview_filepath = (WPSC_PREVIEW_DIR.$preview_filename);
					$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `preview` = '".$wpdb->escape($preview_filename)."', `preview_mimetype` = '".$preview_mimetype."' WHERE `id` = '$fileid' LIMIT 1");
				}
				$stat = stat( dirname($preview_filepath));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $preview_filepath, $perms );	
			}
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `filename` = '".$wpdb->escape($filename)."', `mimetype` = '$mimetype', `idhash` = '$idhash' WHERE `id` = '$fileid' LIMIT 1");
		}
		$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
		return $fileid;
  } else {
		return false;
  }
}

function wpsc_item_reassign_file($selected_product_file, $mode = 'add') {
  global $wpdb;
	// initialise $idhash to null to prevent issues with undefined variables and error logs
	$idhash = null;
	if($mode == 'edit') {
		/* if we are editing, grab the current file and ID hash */ 
		$product_id = (int)$_POST['prodid'];
		if($selected_product_file == '.none.') {
			// unlikely that anyone will ever upload a file called .none., so its the value used to signify clearing the product association
			$wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '0' WHERE `id` = '$product_id' LIMIT 1");
			return null;
		}
		
		// if we already use this file, there is no point doing anything more.
		$current_fileid = $wpdb->get_var("SELECT `file` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
		if($current_fileid > 0) {
			$current_file_data = $wpdb->get_row("SELECT `id`,`idhash` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id` = '$current_fileid' LIMIT 1",ARRAY_A);
			if(basename($selected_product_file) == $file_data['idhash']) {
				return $current_fileid;
			}
		}
	}

	
	$selected_product_file = basename($selected_product_file);
	if(file_exists(WPSC_FILE_DIR.$selected_product_file)) {
		$timestamp = time();
		$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `idhash` IN('".$wpdb->escape($selected_product_file)."') LIMIT 1", ARRAY_A);
		$fileid = (int)$file_data['id'];
		if($fileid < 1) { // if the file does not have a database row, add one.
		  $mimetype = wpsc_get_mimetype(WPSC_FILE_DIR.$selected_product_file);
		  $filename = $idhash = $selected_product_file;
			$timestamp = time();
			$wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_FILES."` ( `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '{$filename}', '{$mimetype}', '{$idhash}', '{$timestamp}');");
			$fileid = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `date` = '{$timestamp}' AND `filename` IN ('{$filename}')");
		}
		if($mode == 'edit') {
      //if we are editing, update the file ID in the product row, this cannot be done for add because the row does not exist yet.
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `file` = '$fileid' WHERE `id` = '$product_id' LIMIT 1");
		}
	}	
	return $fileid;
}
?>