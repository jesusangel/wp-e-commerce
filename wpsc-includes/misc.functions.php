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
		}
    $value = $wpdb->escape($value);
    
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
		}
		
	$value = $wpdb->escape($value);
	
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



?>