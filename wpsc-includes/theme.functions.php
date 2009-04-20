<?php
/**
 * WP eCommerce theme functions
 *
 * These are the functions for the wp-eCommerce theme engine
 *
 * @package wp-e-commerce
 * @since 3.7
*/
/**
 * The WPSC Cart API for templates
 */


//handles replacing the tags in the pages
  
function nzshpcrt_products_page($content = '') {
  global $wpdb, $wp_query, $wpsc_query;
  //if(WPSC_DEBUG === true) {wpsc_debug_start_subtimer('nzshpcrt_products_page','start');}
  //exit(htmlentities($content));
  if(preg_match("/\[productspage\]/",$content)) {
    
    
//     if(get_option('wpsc_use_theme_engine') == TRUE) {
			$wpsc_query->get_products();
			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
			ob_start();
			if(wpsc_is_single_product()) {
				include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/single_product.php");
			} else {
			  // get the display type for the selected category
				if(is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['product_category']) || is_numeric(get_option('wpsc_default_category'))) {
					if(is_numeric($wp_query->query_vars['product_category'])) {
						$category_id =(int) $wp_query->query_vars['product_category'];
					} else if(is_numeric($_GET['category'])) {
						$category_id = (int)$_GET['category'];
					} else { 
						$category_id = (int)get_option('wpsc_default_category');
					}
				}			
				$display_type = $wpdb->get_var("SELECT `display_type` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1");
			
			
			
				if($display_type == '') {
					$display_type = get_option('product_view');
				}
				//exit($display_type);
				// switch the display type, based on the display type variable...
				switch($display_type) {
					case "grid":
					if(file_exists(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/grid_view.php")) {
						include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/grid_view.php");
						break; // only break if we have the function;
					}
				  /*
				  case "list":
					if(function_exists('product_display_list')) {
						echo product_display_list($product_list, $group_type, $group_sql, $search_sql);
						break; // only break if we have the function;
					}
				  */
				  case "default":  // this may be redundant :D
				  default:
				    include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/products_page.php");
				  break;
				}
			}
			$output = ob_get_contents();
			ob_end_clean();
			$output = str_replace('$','\$', $output);
//     } else {
// 			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
// 			ob_start();
// 			include_once(WPSC_FILE_PATH . "/products_page.php");
// 			$output = ob_get_contents();
// 			ob_end_clean();
//     }
    return preg_replace("/(<p>)*\[productspage\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}

function nzshpcrt_shopping_cart($content = '') {
		//exit($content);
  if(preg_match("/\[shoppingcart\]/",$content)) {
//   if(get_option('wpsc_use_theme_engine') == TRUE) {
			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
			ob_start();
			include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/shopping_cart_page.php");
			$output = ob_get_contents();
			ob_end_clean();
			$output = str_replace('$','\$', $output);
//     } else {
// 			ob_start();
// 			include_once(WPSC_FILE_PATH . "/shopping_cart.php");
// 			$output = ob_get_contents();
// 			ob_end_clean();
//     }
    return preg_replace("/(<p>)*\[shoppingcart\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}
  

function nzshpcrt_checkout($content = '') {
  if(preg_match("/\[checkout\]/",$content)) {
    ob_start();
    include_once(WPSC_FILE_PATH . "/checkout.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/(<p>)*\[checkout\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}

function nzshpcrt_transaction_results($content = '') {
  if(preg_match("/\[transactionresults\]/",$content)) {
    ob_start();
    include_once(WPSC_FILE_PATH . "/transaction_results.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/(<p>)*\[transactionresults\](<\/p>)*/",$output, $content);
	} else { 
    return $content;
	}
}
  
function nzshpcrt_user_log($content = '') {
  if(preg_match("/\[userlog\]/",$content)) {
    ob_start();
    include_once(WPSC_FILE_PATH . '/user-log.php');
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/(<p>)*\[userlog\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}
  
  
//displays a list of categories when the code [showcategories] is present in a post or page.
function nzshpcrt_show_categories($content = '') {
  if(preg_match("/\[showcategories\]/",$content)) {
    $GLOBALS['nzshpcrt_activateshpcrt'] = true;
    $output = nzshpcrt_display_categories_groups();
    return preg_replace("/(<p>)*\[showcategories\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}

// substitutes in the buy now buttons where the shortcode is in a post.
function nzshpcrt_substitute_buy_now_button($content = '') {
  if(preg_match_all("/\[buy_now_button=([\d]+)\]/", $content, $matches)) {
  //echo "<pre>".print_r($matches,true)."</pre>";
    foreach($matches[1] as $key => $product_id) {
      $original_string = $matches[0][$key];
      //print_r($matches);
      $output = wpsc_buy_now_button($product_id, true);  
			$content = str_replace($original_string, $output, $content);
    }
	}	
	return $content;
}

/* 19-02-09
 * add to cart shortcode function used for shortcodes calls the function in
 * product_display_functions.php
 */
function add_to_cart_shortcode($content = '') {
		//exit($content);
  if(preg_match_all("/\[add_to_cart=([\d]+)\]/",$content, $matches)) {
    	foreach($matches[1] as $key => $product_id){
  			$original_string = $matches[0][$key];
  			$output = wpsc_add_to_cart_button($product_id, true);  
			$content = str_replace($original_string, $output, $content);
  	}
  }
    return $content;	
}





?>