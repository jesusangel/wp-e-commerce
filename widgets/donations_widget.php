<?php
/*
 * Special widget function, 
 * @todo make this use the new widget API
 * takes the settings, works out if there is anything to display, if so, displays it	
 */
function widget_donations($args) {
  global $wpdb, $table_prefix;
  extract($args);
  $options = get_option('wpsc-nzshpcrt_donations');    
	$title = empty($options['title']) ? __(__('Product Donations', 'wpsc')) : $options['title'];
	//$donation_count = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `donation` IN ('1') AND `active` IN ('1')");
    $donation_count = $wpdb->get_var("SELECT COUNT(DISTINCT `p`.`ID`) AS `count`
	FROM `".$wpdb->postmeta."` AS `m`
	JOIN `".$wpdb->posts."` AS `p` ON `m`.`post_id` = `p`.`ID`
	WHERE `p`.`post_parent` 
		IN ('0')
	AND `m`.`meta_key`
		IN ('_wpsc_is_donation')
	AND `m`.`meta_value` 
		IN( '1' )
	AND `p`.`post_status` = 'publish'");   
	if($donation_count > 0) {
    echo $before_widget; 
    $full_title = $before_title . $title . $after_title;
    echo $full_title;
			nzshpcrt_donations();
    echo $after_widget;
    }
}


/*
 * Specials Widget content function
 * Displays the products
 */
function nzshpcrt_donations($input = null) {
	global $wpdb;
	$siteurl = get_option('siteurl');
	
	$products = $wpdb->get_results("SELECT 
		DISTINCT `p` . * , 
		`m`.`meta_value` AS `special_price`
		FROM `".$wpdb->postmeta."` AS `m`
		JOIN `".$wpdb->posts."` AS `p` 
		ON `m`.`post_id` = `p`.`ID`
		WHERE `p`.`post_parent` 
			IN ('0')
		AND `m`.`meta_key`
			IN ('_wpsc_is_donation')
		AND `m`.`meta_value` 
			IN( '1' )
		ORDER BY RAND( )
		LIMIT 1", ARRAY_A) ;
//	exit('<pre>'.print_r($products,true).'</pre>');
	

	if($products != null) {
		$output = "<div><div>";
		foreach($products as $product) {
		 	$attached_images = (array)get_posts(array(
				'post_type' => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $product['ID'],
				'orderby' => 'menu_order',
				'order' => 'ASC'
			));
			$attached_image = $attached_images[0]; 
			if(($attached_image->ID > 0)) {
					$output .= "	<img src='". wpsc_product_image($attached_image->ID, get_option('product_image_width'), get_option('product_image_height'))."' title='".$product['post_title']."' alt='".$product['post_title']."' /><br />";
			}
			//exit('<pre>'.print_r($image,true).'</pre>');
			$output .= "<strong>".$product['post_title']."</strong><br />";
		
			$output .= $product['post_content']."<br />";
		
			$output .= "<form name='".$product['ID']."' method='post' action='' >";
			$output .= "<input type='hidden' name='product_id' value='".$product['ID']."'/>";
			$output .= "<input type='hidden' name='item' value='".$product['ID']."' />";
			$output .= "<input type='hidden' name='wpsc_ajax_action' value='donations_widget' />";		
			$currency_sign_location = get_option('currency_sign_location');
			$currency_type = get_option('currency_type');
			$currency_symbol = $wpdb->get_var("SELECT `symbol_html` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$currency_type."' LIMIT 1") ;
			$output .= "<label for='donation_widget_price_".$product['ID']."'>".__('Donation', 'wpsc').":</label> $currency_symbol<input type='text' id='donation_widget_price_".$product['ID']."' name='donation_price' value='".number_format($product['price'],2)."' size='6' /><br />"; 
			$output .= "<input type='submit' name='Buy' value='".__('Add To Cart', 'wpsc')."'  />";
			$output .= "</form>";
		}
		$output .= "</div></div>";
	} else {
		$output = '';
	}
	echo $input.$output;
}

/*
 * Specials Widget control function
 * Displays the products
 */
function widget_donations_control() { 
  $option_name = 'wpsc-nzshpcrt_donations';  // because I want to only change this to reuse the code.
	$options = $newoptions = get_option($option_name);
	if ( isset($_POST[$option_name]) ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST[$option_name]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option($option_name, $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	
	echo "<p>\n\r";
	echo "  <label for='{$option_name}'>"._e('Title:')."<input class='widefat' id='{$option_name}' name='{$option_name}' type='text' value='{$title}' /></label>\n\r";
	echo "</p>\n\r";
}


/*
 * Specials Widget init function
 * Displays the products
 */
function widget_donations_init() {
  if(function_exists('register_sidebar_widget')) {
    register_sidebar_widget(__('Product Donations', 'wpsc'), 'widget_donations');
    register_widget_control(__('Product Donations', 'wpsc'), 'widget_donations_control');
	}
  return;
}

add_action('plugins_loaded', 'widget_donations_init');
?>