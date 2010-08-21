<?php
/**
 * WP eCommerce product functions and product utility function.
 *
 * This is the wpsc equivalent of post-template.php
 * 
 * @package wp-e-commerce
 * @since 3.8
 * @subpackage wpsc-template-functions
 */




/**
* wpsc product image function
* if no parameters are passed, the image is not resized, otherwise it is resized to the specified dimensions
* @param integer attachment_ID
* @param integer width
* @param integer height
* @return string - the product image URL, or the URL of the resized version
*/
function wpsc_product_image($attachment_id, $width = null, $height = null) {
	global $wp_query, $wpdb;
	$image_exists = false;
	if((($width >= 10) && ($height >= 10)) && (($width <= 1024) && ($height <= 1024))) {
		$intermediate_size = "wpsc-{$width}x{$height}";
	} 
	
	
	
	if(($attachment_id > 0) && ($intermediate_size != '')) {
		// Get all the required information about the attachment
		$uploads = wp_upload_dir();
		
		$image_meta = get_post_meta($attachment_id, '');
		$file_path = get_attached_file($attachment_id);
		foreach($image_meta as $meta_name => $meta_value) { // clean up the meta array
			$image_meta[$meta_name] = maybe_unserialize(array_pop($meta_value));
		}
		$attachment_metadata = $image_meta['_wp_attachment_metadata'];

		// determine if we already have an image of this size
		if(isset($attachment_metadata['sizes']) && (count($attachment_metadata['sizes']) > 0) && (isset($attachment_metadata['sizes'][$intermediate_size]))) {
			$intermediate_image_data = image_get_intermediate_size($attachment_id, $intermediate_size);
			$image_exists = true;
			$image_url = $intermediate_image_data['url'];
		}
	}
	
	
	if($image_exists == false) {
		$image_url = "index.php?wpsc_action=scale_image&amp;attachment_id={$attachment_id}&amp;width=$width&amp;height=$height";
	}
	
	return $image_url;
}




/**
* wpsc product price function
* @return string - the product price
*/
function wpsc_the_product_price($no_decimals = false) {
	global $wpsc_query, $wpsc_variations;	

	if(count($wpsc_variations->first_variations) > 0) {
		$product_id = wpsc_get_child_object_in_terms(get_the_ID(), $wpsc_variations->first_variations,'wpsc-variation');
		//echo "<pre>".print_r($variation_data, true)."</pre>";
	} else {
		$product_id = get_the_ID();
	}
	
	$full_price = get_post_meta($product_id, '_wpsc_price', true);
	$special_price = get_post_meta($product_id, '_wpsc_special_price', true);
	
	
	$price = $full_price;
	if(($full_price > $special_price) && ($special_price > 0)) {
		$price = $special_price;
	}
	$output = nzshpcrt_currency_display($price, null, true);
	
	if($no_decimals == true) {
		$output = array_shift(explode(".", $output));
	}
	
	return $output;
}

function wpsc_calculate_price($product_id, $variations = null, $special = true) {
	if(count($variations) > 0) {
		$product_id = wpsc_get_child_object_in_terms($product_id, $variations,'wpsc-variation');
	} else {
		$product_id = get_the_ID();
	}
	
	if($special == true) {
		$full_price = get_post_meta($product_id, '_wpsc_price', true);
		$special_price = get_post_meta($product_id, '_wpsc_special_price', true);
		
		$price = $full_price;
		if(($full_price > $special_price) && ($special_price > 0)) {
			$price = $special_price;
		}
	} else {
		$price = get_post_meta($product_id, '_wpsc_price', true);
	}
	return $price;
}





/**
* wpsc display categories function
* Used to determine whether to display products on the page
* @return boolean - true for yes, false for no
*/
function wpsc_display_categories() {
	global $wp_query;
	//_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	$output = false;
	if(!is_numeric(get_option('wpsc_default_category'))) {
		if(isset($wp_query->query_vars['products'])) {
			$category_id = $wp_query->query_vars['products'];
		} else if(isset($_GET['products'])) {
			$category_id = $_GET['products'];
		}
		
		// if we have no categories, and no search, show the group list
		if(is_numeric(get_option('wpsc_default_category')) || (isset($product_id) && is_numeric($product_id)) || (isset($_GET['product_search']) && $_GET['product_search'] != '')) {
		  $output = true;
		}
		if((get_option('wpsc_default_category') == 'all+list')|| (get_option('wpsc_default_category') == 'list')){
		  $output = true;
		}
	}
	
	if(isset($category_id) && $category_id > 0) {
		$output = false;
	}
  return $output;
}

/**
* wpsc display products function
* Used to determine whether to display products on the page
* @return boolean - true for yes, false for no
*/
function wpsc_display_products() {
	//we have to display something, if we are not displaying categories, then we must display products
	$output = true;
	if(wpsc_display_categories()) {
		if(get_option('wpsc_default_category') == 'list') {
			$output = false;
		}
		if(isset($_GET['range']) || isset($_GET['category'])){
			$output = true;
		}
	}
  return $output;
}

/**
*	this page url function, returns the URL of this page
* @return string - the URL of the current page
*/
function wpsc_this_page_url() {
	//_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wp_query;
	//echo "<pr".print_r($wpsc_query->category,true)."</pre>";
	if($wpsc_query->is_single === true) {
		$output = wpsc_product_url($wp_query->post->ID);
	} else if(isset($wpsc_query->category) && $wpsc_query->category != null) {
		$output = wpsc_category_url($wpsc_query->category);
		if($wpsc_query->query_vars['page'] > 1) {
			//
			if(get_option('permalink_structure')) {
				$output .= "page/{$wpsc_query->query_vars['page']}/";
			} else {
				$output = add_query_arg('page_number', $wpsc_query->query_vars['page'], $output);
			}
		}
	} elseif(isset($id)) {
		$output = get_permalink($id);	
	}	else {
		$output = get_permalink(get_the_ID());
	}
	return $output;
}

/**
*	is single product function, determines if we are viewing a single product
* @return boolean - true, or false...
*/
function wpsc_is_single_product() {
	global $wpsc_query;
	//echo "<pre>".print_r($wpsc_query->is_single,true)."</pre>";
	if($wpsc_query->is_single === 1) {
		$state = true;
	} else {
		$state = false;
	}
	return $state;
}

/**
* category class function, categories can have a specific class, this gets that
* @return string - the class of the selected category
*/
function wpsc_category_class() {
	//_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpdb, $wp_query; 
	
	$category_nice_name = '';
	if($wp_query->query_vars['product_category'] != null) {
		$catid = $wp_query->query_vars['product_category'];
	} else if(is_numeric($_GET['category'])) {
		$catid = $_GET['category'];
	} else if(is_numeric($GLOBALS['wpsc_category_id'])) {
		$catid = $GLOBALS['wpsc_category_id'];
	} else {
		$catid = get_option('wpsc_default_category');
		if($catid == 'all+list') {
			$catid = 'all';
		}
	}
	
	if((int)$catid > 0) {
		$category_nice_name = $wpdb->get_var("SELECT `nice-name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` ='".(int)$catid."' LIMIT 1");
	} else if($catid == 'all') {
		$category_nice_name = 'all-categories';
	}
	//exit("<pre>".print_r(get_option('wpsc_default_category'),true)."</pre>");
	return $category_nice_name;
}


/**
* category transition function, finds the transition between categories
* @return string - the class of the selected category
*/
function wpsc_current_category_name() {
	global $wp_query;
	$term_data = get_term($wp_query->post->term_id, 'wpsc_product_category');
	
	return $term_data->name; //$wpsc_query->product['category'];
}

/**
* category transition function, finds the transition between categories
* @return string - the class of the selected category
*/
function wpsc_category_transition() {
	global $wpdb, $wp_query, $wpsc_query;
	$current_product_index = (int)$wp_query->current_post;
	$previous_product_index = ((int)$wp_query->current_post - 1);

	if($previous_product_index >= 0) {
		$previous_category_id = $wp_query->posts[$previous_product_index]->term_id;
	} else {
		$previous_category_id = 0;
	}

	$current_category_id =	$wp_query->post->term_id;
	if($current_category_id != $previous_category_id) {
		return true;
	} else {
		return false;
	}
}



/**
* wpsc have products function, the product loop
* @return boolean true while we have products, otherwise, false
*/
function wpsc_have_products() {
	return have_posts();
}

/**
* wpsc the product function, gets the next product, 
* @return nothing
*/
function wpsc_the_product() {
	global $wpsc_custom_meta, $wpsc_variations;
	the_post();
	$wpsc_custom_meta = new wpsc_custom_meta(get_the_ID());
	$wpsc_variations = new wpsc_variations(get_the_ID());
}

/**
* wpsc in the loop function, 
* @return boolean - true if we are in the loop
*/
function wpsc_in_the_loop() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->in_the_loop;
}

/**
* wpsc rewind products function, rewinds back to the first product
* @return nothing
*/
function wpsc_rewind_products() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->rewind_posts();
}

/**
* wpsc the product id function, 
* @return integer - the product ID
*/
function wpsc_the_product_id() {
	return get_the_ID();
}

/**
* wpsc edit the product link function
* @return string - a link to edit this product
*/
function wpsc_edit_the_product_link( $link = null, $before = '', $after = '', $id = 0 ) {
	global $wpsc_query, $current_user, $table_prefix, $wp_query;
	if ( $link == null ) {
		$link = __('Edit', 'wpsc');
	}
	$product_id = $wp_query->post->ID;
	if ( $id > 0 ) {
		$product_id = $id;
	}

	$siteurl = get_option('siteurl');
	get_currentuserinfo();
	$output = '';
	if($current_user->{$table_prefix . 'capabilities'}['administrator'] == 1) {
		$output = $before . "<a class='wpsc_edit_product' href='{$siteurl}/wp-admin/admin.php?page=wpsc-edit-products&amp;action=wpsc_add_edit&amp;product={$product_id}'>" . $link . "</a>" . $after;
	}
	return $output;
}

/**
* wpsc the product title function
* @return string - the product title
*/
function wpsc_the_product_title() {
	return get_the_title();
}

/**
* wpsc product description function
* @return string - the product description
*/
function wpsc_the_product_description() {
	global $wpsc_query, $allowedtags;
	return get_the_content('Read the rest of this entry &raquo;');
}

/**
* wpsc additional product description function
* TODO make this work with the tabbed multiple product descriptions, may require another loop
* @return string - the additional description
*/
function wpsc_the_product_additional_description() {
	return get_the_excerpt();
}


/**
* wpsc product permalink function
* @return string - the URL to the single product page for this product
*/
function wpsc_the_product_permalink() {
	global $wp_query;
	return get_permalink();
}

/**
* wpsc external link function
* @return string - the product price
*/
function wpsc_product_external_link($id = null){
	if(is_numeric($id) && ($id > 0)) {
		$id = absint($id);		
	} else {
		$id = get_the_ID();
	}
	$product_meta = get_post_meta($id, '_wpsc_product_metadata', true);
	if(isset($product_meta['external_link'])) {
		$external_link = $product_meta['external_link'];
	return $external_link;
	}
}

/**
* wpsc product sku function
* @return string - the product price
*/
function wpsc_product_sku($id = null){
	if(is_numeric($id) && ($id > 0)) {
		$id = absint($id);		
	} else {
		$id = get_the_ID();
	}

	$product_sku = get_post_meta($id, '_wpsc_sku', true);
	return $product_sku;
}



/**
* wpsc product creation time function
* @return string - the product price
*/
function wpsc_product_creation_time($format = null) {
	global $wpsc_query;
	if($format == null) {
		$format = "Y-m-d H:i:s";
	}
	return mysql2date($format, $wpsc_query->product['date_added']);
}


/**
* wpsc product has stock function
* TODO this may need modifying to work with variations, test this
* @return boolean - true if the product has stock or does not use stock, false if it does not
*/
function wpsc_product_has_stock($id = null) {
	if(is_numeric($id) && ($id > 0)) {
		$id = absint($id);		
	} else {
		$id = get_the_ID();
	}
	$is_limited_stock = get_post_meta($id, '_wpsc_stock', true);
	
	if(is_numeric($is_limited_stock) && $is_limited_stock < 1) {
		return false;
	} else {
		return true;
	}
}




/**
* wpsc product remaining stock function
* @return integer - the amount of remaining stock, or null if product is stockless
*/
function wpsc_product_remaining_stock($id = null){
	if(is_numeric($id) && ($id > 0)) {
		$id = absint($id);		
	} else {
		$id = get_the_ID();
	}
	$is_limited_stock = get_post_meta($id, '_wpsc_stock', true);
	
	if(is_numeric($is_limited_stock)) {
		$product_stock = get_post_meta($id, '_wpsc_stock', true);
		return $product_stock;
	} else {
		return null;
	}
}

/**
* wpsc is donation function
* @return boolean - true if it is a donation, otherwise false
*/
function wpsc_product_is_donation($id = null){
	if(is_numeric($id) && ($id > 0)) {
		$id = absint($id);		
	} else {
		$id = get_the_ID();
	}

	$is_donation = get_post_meta($id, '_wpsc_is_donation', true);
	if($is_donation == 1) {
		return true;
	} else {
		return false;
	}
}

/**
* wpsc product on special function
* @return boolean - true if the product is on special, otherwise false
*/
function wpsc_product_on_special() {
	global $wpsc_query;
	
	$price = array_pop(get_post_meta(get_the_ID(), '_wpsc_price'));
	$special_price = array_pop(get_post_meta(get_the_ID(), '_wpsc_special_price'));
	if(($special_price > 0) && (($price - $special_price) >= 0)) {
		return true;
	} else {
		return false;
	}
}

/**
* wpsc product has file function
* @return boolean - true if the product has a file
*/
function wpsc_product_has_file() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wpdb;
	if(is_numeric($wpsc_query->product['file']) && ($wpsc_query->product['file'] > 0)) {
		return true;
	}
	return false;
}

/**
* wpsc product is modifiable function
* @return boolean - true if the product has a file
*/
function wpsc_product_is_customisable() {
	global $wpsc_query, $wpdb;
	$id = get_the_ID();
	$product_meta = get_post_meta($id, '_wpsc_product_metadata', true);
	if(($product_meta['engraved'] == true) || ($product_meta['can_have_uploaded_image'] == true)){
		return true;
	}
	return false;
}


/**
* wpsc product has personal text function
* @return boolean - true if the product has a file
*/
function wpsc_product_has_personal_text() {
	global $wpsc_query, $wpdb;
	$id = get_the_ID();
	$product_meta = get_post_meta($id, '_wpsc_product_metadata', true);
	if($product_meta['engraved'] == true) {
		return true;
	}
	return false;
}

/**
* wpsc product has personal file function
* @return boolean - true if the product has a file
*/
function wpsc_product_has_supplied_file() {
	global $wpsc_query, $wpdb;
	$id = get_the_ID();
	$product_meta = get_post_meta($id, '_wpsc_product_metadata', true);
	if($product_meta['can_have_uploaded_image'] == true) {
		return true;
	}
	return false;
}

/**
* wpsc product postage and packaging function
* @return string - currently only valid for flat rate
*/
function wpsc_product_postage_and_packaging() {
	if(isset($id) && is_numeric($id) && ($id > 0)) {
		$id = absint($id);		
	} else {
		$id = get_the_ID();
	}
	$product_meta = get_post_meta($id, '_wpsc_product_metadata', true);
	//echo "<pre>".print_r($product_meta, true)."</pre>";
	if(is_array($product_meta['shipping'])) {
		return nzshpcrt_currency_display($product_meta['shipping']['local'], 1, true);
	} else {
		return nzshpcrt_currency_display(0,1,true);
	}
}

/**
* wpsc normal product price function
* TODO determine why this function is here
* @return string - returns some form of product price
*/
function wpsc_product_normal_price() {
	global $wpsc_query;	
	$price = get_post_meta(get_the_ID(), '_wpsc_price', true);
	$output = nzshpcrt_currency_display($price, null, true);
	return $output;
}

/**
* wpsc product image function
* @return string - the URL to the thumbnail image
*/
function wpsc_the_product_image($product_id = '') {
	if(empty($product_id)){
		$product_id =  get_the_ID() ;
	}
	$post_thumbnail_id = get_post_thumbnail_id($product_id);
	$src =wp_get_attachment_image_src($post_thumbnail_id, 'product-thumbnail');
	
	if(!empty($src) && is_string($src[0])) {

		return $src[0];
	} else {
		return false;
	}
}
/**
* wpsc product thumbnail function
* @return string - the URL to the thumbnail image
*/
function wpsc_the_product_thumbnail($width = null, $height = null) {
	// show the thumbnail image for the product
	
	if(($width < 10) || ($width < 10)) {	
		$width  = get_option('product_image_width');
		$height = get_option('product_image_height');
	}

	if(has_post_thumbnail(get_the_ID())) {
	add_image_size( 'product-thumbnails', get_option('product_image_width'), get_option('product_image_height'), TRUE ); 
		$image = get_the_post_thumbnail(get_the_ID(), 'product-thumbnails');
		return $image;
	} else {
		return false;
	}
}

/**
 * Return the class(es) that should be applied to a product image's <a> tag.
 *
 * If the thickbox effect is enabled for product images (presentation setting), the thickbox class name is included
 *
 * This function is called from theme files when outputting product img tags
 *
 * @since 3.8
 * @return string space-separated list of class names (for use in a class="") attribute
 */
function wpsc_the_product_image_link_classes() {
    $classes = array();
    if ( get_option( 'show_thumbnails_thickbox' ) ) {
    	$classes[] = 'thickbox';
    }
    $classes[] = 'preview_link';

    $classes = apply_filters( 'wpsc_the_product_image_link_classes', $classes );
    return implode( ' ', $classes );
}

/**
* wpsc product comment link function
* @return string - javascript required to make the intense debate link work
*/
function wpsc_product_comment_link() {
	// add the product comment link
	global $wpsc_query;
	
	if (get_option('wpsc_enable_comments') == 1) {
		$enable_for_product = get_product_meta(get_the_ID(), 'enable_comments');
	
		if ((get_option('wpsc_comments_which_products') == 1 && $enable_for_product == '') || $enable_for_product == 'yes') {
			$original = array("&","'",":","/","@","?","=");
			$entities = array("%26","%27","%3A","%2F","%40","%3F","%3D");
	
			$output = "<div class=\"clear comments\">
						<script src='http://www.intensedebate.com/js/getCommentLink.php?acct=".get_option("wpsc_intense_debate_account_id")."&postid=product_".$wpsc_query->product['id']."&posttitle=".urlencode(get_the_title())."&posturl=".str_replace($original, $entities, wpsc_product_url(get_the_ID(), null, false))."&posttime=".urlencode(date('Y-m-d h:i:s', time()))."&postauthor=author_".get_the_ID()."' type='text/javascript' defer='defer'></script>
					</div>";
		}
	}
	return $output;
}

/**
* wpsc product comments function
* @return string - javascript for the intensedebate comments
*/
function wpsc_product_comments() {
//Commenting out deprecated functions - they aren't used properly, they need to specify the function that should be used.
//	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$output = '';
	// add the product comments
	if (get_option('wpsc_enable_comments') == 1) {
		$enable_for_product = get_product_meta($wpsc_query->product['id'], 'enable_comments');

		if ((get_option('wpsc_comments_which_products') == 1 && $enable_for_product == '') || $enable_for_product == 'yes') {
			$output = "<script>
				var idcomments_acct = '".get_option('wpsc_intense_debate_account_id')."';
				var idcomments_post_id = 'product_".$wpsc_query->product['id']."';
				var idcomments_post_url = encodeURIComponent('".wpsc_product_url($wpsc_query->product['id'], null, false)."');
				</script>
				<span id=\"IDCommentsPostTitle\" style=\"display:none\"></span>
				<script type='text/javascript' src='http://www.intensedebate.com/js/genericCommentWrapperV2.js'></script>
				";
				
		}
	}
	return $output;
}






/**
* wpsc have custom meta function
* @return boolean - true while we have custom meta to display
*/
function wpsc_have_custom_meta() {
	global $wpsc_custom_meta;
	return $wpsc_custom_meta->have_custom_meta();
}

/**
* wpsc the custom meta function
* @return nothing - iterate through the custom meta vallues
*/
function wpsc_the_custom_meta() {
	global $wpsc_custom_meta;
	return $wpsc_custom_meta->the_custom_meta();
}



/**
* wpsc custom meta name function
* @return string - the custom metal name
*/
function wpsc_custom_meta_name() {
	global $wpsc_custom_meta;
	return $wpsc_custom_meta->custom_meta_values['meta_key'];
}

/**
* wpsc custom meta value function
* @return string - the custom meta value
*/
function wpsc_custom_meta_value() {
	global $wpsc_custom_meta;
	return $wpsc_custom_meta->custom_meta_values['meta_value'];
}












/**
* wpsc have variation groups function
* @return boolean - true while we have variation groups
*/
function wpsc_have_variation_groups() {
	global $wpsc_variations;
	return $wpsc_variations->have_variation_groups();
}

/**
* wpsc the variation group function
* @return nothing - iterate through the variation groups
*/
function wpsc_the_variation_group() {
	global $wpsc_variations;
	$wpsc_variations->the_variation_group();
}

/**
* wpsc have variations function
* @return boolean - true while we have variations
*/
function wpsc_have_variations() {
	global $wpsc_variations;
	return $wpsc_variations->have_variations();
}

/**
* wpsc the variation function
* @return nothing - iterate through the variations
*/
function wpsc_the_variation() {
	global $wpsc_variations;
	$wpsc_variations->the_variation();
}


function wpsc_product_has_multicurrency(){
	global $wpdb, $wpsc_query;
	//echo "<pre>".print_r($wpdb,true)."</pre>";

	$sql = "SELECT `meta_key`, `meta_value` FROM `".$wpdb->postmeta."` WHERE `post_id`=".get_the_ID()." AND `meta_key` LIKE '_wpsc_currency%'";
	$results = $wpdb->get_results($sql, ARRAY_A);
	if(count($results) > 0){
		return true;
	}else{
		return false;
	}
//	exit('<pre>'.print_r($results, true).'</pre>');

}

function wpsc_display_product_multicurrency(){
	global $wpdb, $wpsc_query;
	
	$output = '';	$sql = "SELECT `meta_key`, `meta_value` FROM `".$wpdb->postmeta."` WHERE `post_id`=".get_the_ID()." AND `meta_key` LIKE '_wpsc_currency%'";
	$results = $wpdb->get_results($sql, ARRAY_A);
	if(count($results) > 0){
		foreach((array)$results as $curr){
			$isocode = str_ireplace("currency[", "", $curr['meta_key']);
			$isocode = str_ireplace("]", "", $isocode);			
			$currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".$isocode."' LIMIT 1",ARRAY_A) ;
			if($currency_data['symbol'] != '') {
				$currency_sign = $currency_data['symbol_html'];
			} else {
				$currency_sign = $currency_data['code'];
			}

			$output .='<span class="wpscsmall pricefloatright pricedisplay">'.$currency_sign.' '.nzshpcrt_currency_display($curr["meta_value"],false,false,false,true).'</span><br />';
			//exit('<pre>'.print_r($currency_sign, true).'</pre>');
		}
	
	}
	return $output;
}

/**
* wpsc variation group name function
* @return string - the variaton group name
*/
function wpsc_the_vargrp_name() {
 // get the variation group name;
	global $wpsc_variations;
	return $wpsc_variations->variation_group->name;
}

/**
* wpsc variation group form ID function
* @return string - the variation group form id, for labels and the like
*/
function wpsc_vargrp_form_id() {
 // generate the variation group form ID;
	global $wpsc_variations,$wpsc_variations;
	$product_id = get_the_ID();
	$form_id = "variation_select_{$product_id}_{$wpsc_variations->variation_group->term_id}";
	return $form_id;
}

/**
* wpsc variation group ID function
* @return integer - the variation group ID
*/
function wpsc_vargrp_id() {
	global $wpsc_variations;
	return $wpsc_variations->variation_group->term_id;
}

/**
* wpsc the variation name function
* @return string - the variation name
*/
function wpsc_the_variation_name() {
	global $wpsc_variations;
	return stripslashes($wpsc_variations->variation->name);
}

/**
* wpsc the variation ID function
* @return integer - the variation ID
*/
function wpsc_the_variation_id() {
	global $wpsc_variations;
	return $wpsc_variations->variation->term_id;
}

/**
* wpsc the variation out_of_stock function
* @return string - HTML attribute to disable select options and radio buttons
*/
function wpsc_the_variation_out_of_stock() {
	//_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wpdb;
	$out_of_stock = false;
	//$wpsc_query->the_variation();
	if(($wpsc_query->variation_group_count == 1) && ($wpsc_query->product['quantity_limited'] == 1)) {
		$product_id = $wpsc_query->product['id'];
		$variation_group_id = $wpsc_query->variation_group->term_id;
		$variation_id = $wpsc_query->variation->term_id;


		$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product_id}' AND `value_id` IN ( '$variation_id' ) AND `all_variation_ids` IN('$variation_group_id') LIMIT 1");
		
		$variation_stock_data = $wpdb->get_var("SELECT `stock` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1");
		if($variation_stock_data < 1) {
			$out_of_stock = true;
		}
	}
  if($out_of_stock == true) {
		return "disabled='disabled'";
  } else {
		return '';
  }
}
/**
* wpsc product rater function
* @return string - HTML to display the product rater
*/
function wpsc_product_rater() {
	global $wpsc_query;
	$product_id =get_the_ID();
	$output = '';
	if(get_option('product_ratings') == 1) {
		$output .= "<div class='product_footer'>";

		$output .= "<div class='product_average_vote'>";
		$output .= "<strong>".__('Avg. Customer Rating', 'wpsc').":</strong>";
		$output .= wpsc_product_existing_rating($product_id);
		$output .= "</div>";
		
		$output .= "<div class='product_user_vote'>";

		$output .= "<strong><span id='rating_".$product_id."_text'>".__('Your Rating', 'wpsc').":</span>";
		$output .= "<span class='rating_saved' id='saved_".$product_id."_text'> ".__('Saved', 'wpsc')."</span>";
		$output .= "</strong>";
		
		$output .= wpsc_product_new_rating($product_id);
		$output .= "</div>";
		$output .= "</div>";
	}
	return $output;
}


function wpsc_product_existing_rating($product_id) {
	global $wpdb;
	$get_average = $wpdb->get_results("SELECT AVG(`rated`) AS `average`, COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `productid`='".$product_id."'",ARRAY_A);
	$average = floor($get_average[0]['average']);
	$count = $get_average[0]['count'];
	$output .= "  <span class='votetext'>";
	for($l=1; $l<=$average; ++$l) {
		$output .= "<img class='goldstar' src='". WPSC_URL."/images/gold-star.gif' alt='$l' title='$l' />";
	}
	$remainder = 5 - $average;
	for($l=1; $l<=$remainder; ++$l) {
		$output .= "<img class='goldstar' src='". WPSC_URL."/images/grey-star.gif' alt='$l' title='$l' />";
	}
	$output .=  "<span class='vote_total'>&nbsp;(<span id='vote_total_$prodid'>".$count."</span>)</span> \r\n";
	$output .=  "</span> \r\n";
	return $output;
}


function wpsc_product_new_rating($product_id) {
	global $wpdb;
	$cookie_data = explode(",",$_COOKIE['voting_cookie'][$product_id]);
	$vote_id = 0;
	if(is_numeric($cookie_data[0])){
			$vote_id = absint($cookie_data[0]);
	}
	$previous_vote = 1;
	if($vote_id > 0) {
		$previous_vote = $wpdb->get_var("SELECT `rated` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `id`='".$vote_id."' LIMIT 1");
	}
	
	
	//print("<pre>".print_r($previous_vote, true)."</pre>");
	//print("<pre>".print_r(func_get_args(), true)."</pre>");
	$output = "<form class='wpsc_product_rating' method='post'>\n";
	//$output .= "			<input type='hidden' name='product_id' value='{$product_id}' />\n";
	$output .= "			<input type='hidden' name='wpsc_ajax_action' value='rate_product' />\n";
	$output .= "			<input type='hidden' class='wpsc_rating_product_id' name='product_id' value='{$product_id}' />\n";
	$output .= "			<select class='wpsc_select_product_rating' name='product_rating'>\n";
	$output .= "					<option ". (($previous_vote == '1') ? "selected='selected'" : '')." value='1'>1</option>\n";
	$output .= "					<option ". (($previous_vote == '2') ? "selected='selected'" : '')." value='2'>2</option>\n";
	$output .= "					<option ". (($previous_vote == '3') ? "selected='selected'" : '')." value='3'>3</option>\n";
	$output .= "					<option ". (($previous_vote == '4') ? "selected='selected'" : '')." value='4'>4</option>\n";
	$output .= "					<option ". (($previous_vote == '5') ? "selected='selected'" : '')." value='5'>5</option>\n";
	$output .= "			</select>\n";
	$output .= "			<input type='submit' value='".__('Save','wpsc')."'>";
	$output .= "	</form>";
	return $output;
}


/**
* wpsc currency sign function
* @return string - the selected currency sign for the store
*/
function wpsc_currency_sign() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpdb;
	$currency_sign_location = get_option('currency_sign_location');
	$currency_type = get_option('currency_type');
	$currency_symbol = $wpdb->get_var("SELECT `symbol_html` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$currency_type."' LIMIT 1") ;
	return $currency_symbol;
}

/**
* wpsc has pages function
* @return boolean - true if we have pages
*/
function wpsc_has_pages() {
//	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	if(isset($wpsc_query->page_count) && $wpsc_query->page_count > 0) {
		return true;
	} else {
		return false;
	}
}

/**
* wpsc have pages function
* @return boolean - true while we have pages to loop through
*/
function wpsc_have_pages() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->have_pages();
}

/**
* wpsc the page function
* @return nothing - iterate through the pages
*/
function wpsc_the_page() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$wpsc_query->the_page();
}
	
/**
* wpsc page number function
* @return integer - the page number
*/
function wpsc_page_number() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->page['number'];
}

/**
 * this is for the multi adding property, it checks to see whether multi adding is enabled;
 * 
 */
function wpsc_has_multi_adding(){
	if(get_option('multi_add') == 1 && (get_option('addtocart_or_buynow') != 1)){
		return true;
	}else{
		return false;
	}
}
/**
* wpsc page is selected function
* @return boolean - true if the page is selected
*/
function wpsc_page_is_selected() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
 // determine if we are on this page
	global $wpsc_query;
	return $wpsc_query->page['selected'];
}

/**
* wpsc page URL function
* @return string - the page URL
*/
function wpsc_page_url() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
 // generate the page URL
	global $wpsc_query;
	return $wpsc_query->page['url'];
}

/**
* wpsc product count function
* @return string - the page URL
*/
function wpsc_product_count() {
	global $wp_query;
	return $wp_query->found_posts;
}

//The following code was removed from WP 3.8, present in 3.7 - Not sure why it was removed and not refactored. (JS)


/**
* wpsc the variation price function
* @return string - the variation price
*/
function wpsc_the_variation_price() {
	global $wpdb, $wpsc_variations;

    if($wpsc_variations->variation_count > 1) {
		
		$product_id = get_the_ID();
		$variation_id = $wpsc_variations->variation->term_id;

		$variation_product_id = $wpdb->get_var($wpdb->prepare("SELECT object_id FROM ".WP_TERM_RELATIONSHIPS." AS rel LEFT JOIN ".WP_POSTS." AS posts ON rel.object_id = posts.ID WHERE rel.term_taxonomy_id = %d AND posts.post_parent = %d", $variation_id, $product_id )) ;
		
		$price = get_product_meta($variation_product_id, "price");
		$price = $price[0];
		
		$output = nzshpcrt_currency_display($price, '', true);    		
    } else {
    	$output = false;
    }
	return $output;
}

/**
* wpsc the variation stock function
* @return string - HTML attribute to disable select options and radio buttons
*/
function wpsc_the_variation_stock() {
	global $wpdb, $wpsc_variations;

	if($wpsc_variations->variation_count > 1) {
		
		$product_id = get_the_ID();
		$variation_id = $wpsc_variations->variation->term_id;
				
		$variation_product_id = $wpdb->get_var($wpdb->prepare("SELECT object_id FROM ".WP_TERM_RELATIONSHIPS." AS rel LEFT JOIN ".WP_POSTS." AS posts ON rel.object_id = posts.ID WHERE rel.term_taxonomy_id = %d AND posts.post_parent = %d", $variation_id, $product_id )) ;
		
		$stock = get_product_meta($variation_product_id, "stock");
		$output = $stock[0];
				
    } else {
    	$output = false;
    }
	return $output;
}


?>
