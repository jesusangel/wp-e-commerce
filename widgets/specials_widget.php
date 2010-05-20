<?php
/*
 * Special widget function, 
 * @todo make this use the new widget API
 * takes the settings, works out if there is anything to display, if so, displays it	
 */
function widget_specials($args) {
  global $wpdb, $table_prefix;
  extract($args);
  $options = get_option('wpsc-widget_specials');

  $special_count = $wpdb->get_var("SELECT DISTINCT `p`.`ID`
		FROM `".$wpdb->postmeta."` AS `m`
		JOIN `".$wpdb->posts."` AS `p` ON `m`.`post_id` = `p`.`ID`
		WHERE `m`.`meta_key`
		IN ('_wpsc_special_price')
		AND `m`.`meta_value` >0
		AND `p`.`post_status` = 'publish'
		");   
  	  
  //exit('COUNT'.$special_count);
  if($special_count > 0) {
    $title = empty($options['title']) ? __(__('Product Specials', 'wpsc')) : $options['title'];
    echo $before_widget; 
    $full_title = $before_title . $title . $after_title;
    echo $full_title;
    nzshpcrt_specials();
    echo $after_widget;
	}
}

/*
 * Specials Widget content function
 * Displays the products
 * @todo make this use wp_query and a theme file
 */
 function nzshpcrt_specials($input = null) {
	 global $wpdb;
	 $image_width = get_option('product_image_width');
	 $image_height = get_option('product_image_height');
     $siteurl = get_option('siteurl');
   
	 $product = $wpdb->get_results("SELECT DISTINCT `p` . * , `m`.`meta_value` AS `special_price`
		FROM `".$wpdb->postmeta."` AS `m`
		JOIN `".$wpdb->posts."` AS `p` ON `m`.`post_id` = `p`.`ID`
		WHERE `m`.`meta_key`
		IN (
		'_wpsc_special_price'
		)
		AND `m`.`meta_value` >0
		AND `p`.`post_status` = 'publish'
		AND `p`.`post_type` IN ('wpsc-product')
		ORDER BY RAND( )
		LIMIT 1", ARRAY_A) ;

	if($product != null) {
		$output = "<div>";
		foreach($product as $special) {
		
		 	$attached_images = (array)get_posts(array(
				'post_type' => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $special['ID'],
				'orderby' => 'menu_order',
				'order' => 'ASC'
			));
			$attached_image = $attached_images[0]; 
			if(($attached_image->ID > 0)) {
					$output .= "	<img src='". wpsc_product_image($attached_image->ID, get_option('product_image_width'), get_option('product_image_height'))."' title='".$product['post_title']."' alt='".$product['post_title']."' /><br />";
			}
		
		  	$special['name'] =  htmlentities(stripslashes($special['name']), ENT_QUOTES, "UTF-8");
			$output .= "<strong><a class='wpsc_product_title' href='".wpsc_product_url($special['id'],false)."'>".$special['post_title']."</a></strong><br /> ";

			if(get_option('wpsc_special_description') != '1'){
				$output .= $special['post_content']."<br />";
			}

			$output .= "<span id='special_product_price_".$special['ID']."'><span class='pricedisplay'>";       
			$output .= wpsc_calculate_price($special['ID']);
			$output .= "</span></span><br />";
			
			$output .= "<form id='specials_".$special['ID']."' method='post' action='' onsubmit='submitform(this, null);return false;' >";
			$output .= "<input type='hidden' name='product_id' value='".$special['ID']."'/>";
			$output .= "<input type='hidden' name='item' value='".$special['ID']."' />";
			$output .= "<input type='hidden' name='wpsc_ajax_action' value='special_widget' />";			
			$output .= "</form>";
		}
		$output .= "</div>";
	} else {
		$output = '';
	}
	echo $input.$output;
}

/*
 * Specials Widget control function
 * Displays the products
 */
function widget_specials_control() {
  $option_name = 'wpsc-widget_specials';  // because I want to only change this to reuse the code.
	$options = $newoptions = get_option($option_name);
	if ( isset($_POST[$option_name]) ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST[$option_name]));
	}
	if(isset($_POST['wpsc_special_description'])){
		update_option('wpsc_special_description', $_POST['wpsc_special_description']);
	}else{
		update_option('wpsc_special_description', '0');
	}
	if(get_option('wpsc_special_description') == '1'){
		$checked = "checked='checked'";
	}else{
		$checked = '';
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option($option_name, $options);
	}
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	
	echo "<p>\n\r";
	echo "  <label for='{$option_name}'>"._e('Title:')."<input class='widefat' id='{$option_name}' name='{$option_name}' type='text' value='{$title}' /></label>\n\r";
	echo "</p>\n\r";
	echo "<p>\n\r";
	echo "  <label for='{$option_name}'>"._e('Show Description:')."<input $checked id='wpsc_special_description' name='wpsc_special_description' type='checkbox' value='1' /></label>\n\r";
	echo "</p>\n\r";
}

/*
 * Specials Widget init function
 * Displays the products
 */
function widget_specials_init() {
  if(function_exists('register_sidebar_widget')) {
    register_sidebar_widget(__('Product Specials', 'wpsc'), 'widget_specials');
    register_widget_control(__('Product Specials', 'wpsc'), 'widget_specials_control');
	}
  return;
}
add_action('plugins_loaded', 'widget_specials_init');
?>