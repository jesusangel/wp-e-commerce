<?php
/**
 * WP eCommerce misc functions
 *
 * These are the WPSC miscellaneous functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
 
 
/**
 * WPSC add new user function, validates and adds a new user, for the 
 *
 * @since 3.7
 *
 * @param string $user_login The user's username.
 * @param string $password The user's password.
 * @param string $user_email The user's email (optional).
 * @return int The new user's ID.
 */
 function wpsc_add_new_user($user_login,$user_pass, $user_email) {
	require_once(ABSPATH . WPINC . '/registration.php');
	$errors = new WP_Error();
 	$user_login = sanitize_user( $user_login );
	$user_email = apply_filters( 'user_registration_email', $user_email );

	// Check the username
	if ( $user_login == '' ) {
		$errors->add('empty_username', __('<strong>ERROR</strong>: Please enter a username.'));
	} elseif ( !validate_username( $user_login ) ) {
		$errors->add('invalid_username', __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.'));
		$user_login = '';
	} elseif ( username_exists( $user_login ) ) {
		$errors->add('username_exists', __('<strong>ERROR</strong>: This username is already registered, please choose another one.'));
	}

	// Check the e-mail address
	if ($user_email == '') {
		$errors->add('empty_email', __('<strong>ERROR</strong>: Please type your e-mail address.'));
	} elseif ( !is_email( $user_email ) ) {
		$errors->add('invalid_email', __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
		$user_email = '';
	} elseif ( email_exists( $user_email ) ) {
		$errors->add('email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'));
	}

	if ( $errors->get_error_code() ) {
		return $errors;
	}
 	$user_id = wp_create_user( $user_login, $user_pass, $user_email );
	if ( !$user_id ) {
		$errors->add('registerfail', sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email')));
		return $errors;
	}
	$credentials = array('user_login' => $user_login, 'user_password' => $user_pass, 'remember' => true);
	$user = wp_signon($credentials);
	return $user;

	//wp_new_user_notification($user_id, $user_pass);
 }




/**
 * WPSC product has variations function
 * @since 3.7
 * @param int product id
 * @return bool true or false
 */

function wpsc_product_has_variations($product_id) {
  global $wpdb;
  if($product_id > 0) {
		$variation_count = $wpdb->get_var("SELECT COUNT(`id`) FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN('product') AND `associated_id` IN('{$product_id}')");
		if($variation_count > 0) {
			return true;  
		}
  }
	return false;  
}

/**
 * WPSC canonical URL function
 * Needs a recent version 
 * @since 3.7
 * @param int product id
 * @return bool true or false
 */
function wpsc_change_canonical_url($url) {
  global $wpdb, $wpsc_query;
  if($wpsc_query->is_single == true) {
		$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wpsc_query->query_vars['product_url_name']."' ) ORDER BY `product_id` DESC LIMIT 1");
		$url = wpsc_product_url($product_id);

  } else {
    if($wpsc_query->query_vars['category_id'] > 0) {
      $url = wpsc_category_url($wpsc_query->query_vars['category_id']);
			if(get_option('permalink_structure') && ($wpsc_query->query_vars['page'] > 1)) {
				$url .= $url."page/{$wpsc_query->query_vars['page']}/";
			}
    }
  }
  //echo "<pre>".print_r($wpsc_query,true)."</pre>";
  return $url;
}
add_filter('aioseop_canonical_url', 'wpsc_change_canonical_url');









function add_product_meta($product_id, $key, $value, $unique = false, $custom = false) {
  global $wpdb, $post_meta_cache, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
    if(($unique == true) && $wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE meta_key = '$key' AND product_id = '$product_id'")) {
      return false;
		}
		if(!is_string($value)) {
			$value = maybe_serialize($value);
		} else {
			$value = $wpdb->escape($value);
		}
    
    if(!$wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE meta_key = '$key' AND product_id = '$product_id'")) {
      $custom = (int)$custom;
      $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (product_id,meta_key,meta_value, custom) VALUES ('$product_id','$key','$value', '$custom')");
		} else {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET meta_value = '$value' WHERE meta_key = '$key' AND product_id = '$product_id'");
		}
    return true;
	}
  return false; 
}
  
function delete_product_meta($product_id, $key, $value = '') {
  global $wpdb, $post_meta_cache, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
    if ( empty($value) ) {
      $meta_id = $wpdb->get_var("SELECT id FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key'");      
      if(is_numeric($meta_id) && ($meta_id > 0)) {
        $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key'");
        }
      } else {
      $meta_id = $wpdb->get_var("SELECT id FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key' AND meta_value = '$value'");
      if(is_numeric($meta_id) && ($meta_id > 0)) {
        $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key' AND meta_value = '$value'");
        }        
      }
  }
  return true;
}


function get_product_meta($product_id, $key, $single = false) {
  global $wpdb, $post_meta_cache, $blog_id;  
  $product_id = (int)$product_id;
  if($product_id > 0) {
    $meta_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id' LIMIT 1");
    if(is_numeric($meta_id) && ($meta_id > 0)) {      
      if($single != false) {
        $meta_values = maybe_unserialize($wpdb->get_var("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id' LIMIT 1"));
			} else {
        $meta_values = $wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id'");
				$meta_values = array_map('maybe_unserialize', $meta_values);
			}
		}
	} else {
    $meta_values = false;
	}
	
	if (is_array($meta_values) && (count($meta_values) == 1)) {
		return array_pop($meta_values);
	} else {
		return $meta_values;
	}
}

function update_product_meta($product_id, $key, $value, $prev_value = '') {
  global $wpdb, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
		if(!is_string($value)) {
			$value = $wpdb->escape(maybe_serialize($value));
		} else {
			$value = $wpdb->escape($value);
		}
	
	if(!empty($prev_value)) {
    $prev_value = $wpdb->escape(maybe_serialize($prev_value));
	}

	
	
  if($wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND product_id = '$product_id'")) {
    if (empty($prev_value)) {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_value` = '$value' WHERE `meta_key` IN('$key') AND product_id = '$product_id'");
      } else {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_value` = '$value' WHERE `meta_key` IN('$key') AND product_id = '$product_id' AND meta_value = '$prev_value'");
      }
    } else {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (product_id,meta_key,meta_value) VALUES ('$product_id','$key','$value')");
    }
  return true;
  }
}


  

 function wpsc_get_country($country_code) {
  global $wpdb;
  $country = $wpdb->get_var("SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` IN ('".$country_code."') LIMIT 1");
  return $country; 
}

 function wpsc_get_region($region_code) {
  global $wpdb;
  $region = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` IN('$region_code')");
  return $region; 
}

function nzshpcrt_display_preview_image() {
	  global $wpdb;
	  if(($_GET['wpsc_request_image'] == 'true') || is_numeric($_GET['productid']) || is_numeric($_GET['image_id'])|| isset($_GET['image_name'])) {

	  
		if(function_exists("getimagesize")) {
			if(is_numeric($_GET['productid'])) {
				$product_id = (int)$_GET['productid'];
				$image_data = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1");
				
				if(is_numeric($image_data)) {
					$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image_data}' LIMIT 1");
					$imagepath = WPSC_IMAGE_DIR . $image;
				} else {
					$imagepath = WPSC_IMAGE_DIR . $imagedata['image'];
				}
			} else if($_GET['image_id']) {
				$image_id = (int)$_GET['image_id'];
				$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image_id}' LIMIT 1");
				$imagepath = WPSC_IMAGE_DIR . $image;
			} else if( $_GET['image_name']) {
				$image = basename($_GET['image_name']);
				$imagepath = WPSC_USER_UPLOADS_DIR . $image;
			} else if( $_GET['category_id']) {
				$category_id = absint($_GET['category_id']);
				$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` = '{$category_id}' LIMIT 1");
				if($image != '') {
					$imagepath = WPSC_CATEGORY_DIR.$image;
				}
			}
			
			if(!is_file($imagepath)) {
				$imagepath = WPSC_FILE_PATH."/images/no-image-uploaded.gif";
			}
			$image_size = @getimagesize($imagepath);
			if(is_numeric($_GET['height']) && is_numeric($_GET['width'])) {
				$height = (int)$_GET['height'];
				$width = (int)$_GET['width'];
			} else {
				$width = $image_size[0];
				$height = $image_size[1];
			}
			if(!(($height > 0) && ($height <= 1024) && ($width > 0) && ($width <= 1024))) { 
				$width = $image_size[0];
				$height = $image_size[1];
			}
			if($product_id > 0) {
				$cache_filename = basename("product_{$product_id}_{$height}x{$width}");
			} else if ($category_id > 0 ) {
				$cache_filename = basename("category_{$category_id}_{$height}x{$width}");
			} else {
				$cache_filename = basename("product_img_{$image_id}_{$height}x{$width}");
			}
			$imagetype = @getimagesize($imagepath);
			$use_cache = false;
			switch($imagetype[2]) {
				case IMAGETYPE_JPEG:
				$extension = ".jpg";
				break;

				case IMAGETYPE_GIF:
				$extension = ".gif";
				break;

				case IMAGETYPE_PNG:
				$extension = ".png";
				break;
			}
			if(file_exists(WPSC_CACHE_DIR.$cache_filename.$extension)) {
				$original_modification_time = filemtime($imagepath);
				$cache_modification_time = filemtime(WPSC_CACHE_DIR.$cache_filename.$extension);
				if($original_modification_time < $cache_modification_time) {
					$use_cache = true;
				}
			}

			if($use_cache ===true ) {
				header("Location: ".WPSC_CACHE_URL.$cache_filename.$extension);
				exit('');
			} else {
				switch($imagetype[2]) {
					case IMAGETYPE_JPEG:
					//$extension = ".jpg";
					$src_img = imagecreatefromjpeg($imagepath);
					$pass_imgtype = true;
					break;

					case IMAGETYPE_GIF:
					//$extension = ".gif";
					$src_img = imagecreatefromgif($imagepath);
					$pass_imgtype = true;
					break;

					case IMAGETYPE_PNG:
					//$extension = ".png";
					$src_img = imagecreatefrompng($imagepath);
					$pass_imgtype = true;
					break;

					default:
					$pass_imgtype = false;
					break;
				}

				if($pass_imgtype === true) {
					$source_w = imagesx($src_img);
					$source_h = imagesy($src_img);

					//Temp dimensions to crop image properly
					$temp_w = $width;
					$temp_h = $height;

					// select our scaling method
					$scaling_method = 'cropping';

					//list($source_h, $source_w) = array($source_w, $source_h);

					// set both offsets to zero
					$offset_x = $offset_y = 0;

					// Here are the scaling methods, non-cropping causes black lines in tall images, but doesnt crop images.
					switch($scaling_method) {
						case  'cropping':
							// if the image is wider than it is high and at least as wide as the target width.
							if (($source_h <= $source_w)) {
								if ($height < $width ) {
									$temp_h = ($width / $source_w) * $source_h;
								} else {
									$temp_w = ($height / $source_h) * $source_w;
								}
							} else {
								$temp_h = ($width / $source_w) * $source_h;
							}
						break;

						case 'non-cropping':
						default:
							if ($height < $width ) {
								$temp_h = ($width / $source_w) * $source_h;
							} else {
								$temp_w = ($height / $source_h) * $source_w;
							}
						break;
					}

					// Create temp resized image
					$temp_img = ImageCreateTrueColor( $temp_w, $temp_h );
					$bgcolor = ImageColorAllocate( $temp_img, 255, 255, 255 );
					ImageFilledRectangle( $temp_img, 0, 0, $temp_w, $temp_h, $bgcolor );
					ImageAlphaBlending( $temp_img, TRUE );
					ImageCopyResampled( $temp_img, $src_img, 0, 0, 0, 0, $temp_w, $temp_h, $source_w, $source_h );

					$dst_img = ImageCreateTrueColor($width,$height);
					$bgcolor = ImageColorAllocate( $dst_img, 255, 255, 255 );
					ImageFilledRectangle( $dst_img, 0, 0, $width, $height, $bgcolor );
					ImageAlphaBlending($dst_img, TRUE );
					if (($imagetype[2]==IMAGETYPE_PNG) ||($imagetype[2]==IMAGETYPE_GIF)){
						//imagecolortransparent($dst_img, $bgcolor);
					}

					// X & Y Offset to crop image properly
					if($temp_w < $width) {
						$w1 = ($width/2) - ($temp_w/2);
					} else if($temp_w == $width) {
						$w1 = 0;
					} else {
						$w1 = ($width/2) - ($temp_w/2);
					}

					if($temp_h < $height) {
						$h1 = ($height/2) - ($temp_h/2);
					} else if($temp_h == $height) {
						$h1 = 0;
					} else {
						$h1 = ($height/2) - ($temp_h/2);
					}

					switch($scaling_method) {
						case  'cropping':
							ImageCopy( $dst_img, $temp_img, $w1, $h1, 0, 0, $temp_w, $temp_h );
						break;

						case 'non-cropping':
						default:
							ImageCopy( $dst_img, $temp_img, 0, 0, 0, 0, $temp_w, $temp_h );
						break;
					}


					ImageAlphaBlending($dst_img, false);
					switch($imagetype[2]) {
						case IMAGETYPE_JPEG:
						header("Content-type: image/jpeg");
						ImagePNG($dst_img);
						ImagePNG($dst_img, WPSC_CACHE_DIR.$cache_filename.".jpg");
						@ chmod( WPSC_CACHE_DIR.$cache_filename.".jpg", 0775 );
						break;

						case IMAGETYPE_GIF:
						header("Content-type: image/gif");
						ImagePNG($dst_img);
						ImagePNG($dst_img, WPSC_CACHE_DIR.$cache_filename.".gif");
						@ chmod( WPSC_CACHE_DIR.$cache_filename.".gif", 0775 );
						break;

						case IMAGETYPE_PNG:
						header("Content-type: image/png");
						ImagePNG($dst_img);
						ImagePNG($dst_img, WPSC_CACHE_DIR.$cache_filename.".png");
						@ chmod( WPSC_CACHE_DIR.$cache_filename.".png", 0775 );
						break;

						default:
						$pass_imgtype = false;
						break;
					}/*
					header("Content-type: image/png");
					ImagePNG($dst_img);
					ImagePNG($dst_img, WPSC_CACHE_DIR.$cache_filename.".png");
					@ chmod( WPSC_CACHE_DIR.$cache_filename.".png", 0775 );*/
					exit();
				}
			}
		}
	}
}

add_action('init', 'nzshpcrt_display_preview_image');

function wpsc_list_dir($dirname) {
  /*
  lists the provided directory, was nzshpcrt_listdir
  */
  $dir = @opendir($dirname);
  $num = 0;
  while(($file = @readdir($dir)) !== false) {
    //filter out the dots and any backup files, dont be tempted to correct the "spelling mistake", its to filter out a previous spelling mistake.
    if(($file != "..") && ($file != ".") && !stristr($file, "~") && !stristr($file, "Chekcout") && !( strpos($file, ".") === 0 )) {
      $dirlist[$num] = $file;
      $num++;
    }
  }
  if($dirlist == null) {
    $dirlist[0] = "paypal.php";
    $dirlist[1] = "testmode.php";
  }
  return $dirlist; 
}

/**
 * wpsc_recursive_copy function, copied from here and renamed: http://nz.php.net/copy
	* Why doesn't PHP have one of these built in?
 
*/
    
 function wpsc_recursive_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					wpsc_recursive_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					@ copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
    }
    closedir($dir);
}



/**
 * wpsc_replace_reply_address function,
 * Replace the email address for the purchase receipts
*/
function wpsc_replace_reply_address($input) {
  $output = get_option('return_email');
  if($output == '') {
    $output = $input;
  }
  return $output;
}

/**
 * wpsc_replace_reply_address function,
 * Replace the email address for the purchase receipts
*/
function wpsc_replace_reply_name($input) {
  $output = get_option('return_name');
  if($output == '') {
    $output = $input;
  }
  return $output;
}

?>