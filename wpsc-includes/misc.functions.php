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



?>