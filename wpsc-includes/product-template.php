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
		if((count($attachment_metadata['sizes']) > 0) && ($attachment_metadata['sizes'][$intermediate_size])) {
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
function wpsc_the_product_price() {
	global $wpsc_query;	
	/*
	if($special_price < $price) {
		$output = nzshpcrt_currency_display($special_price, $wpsc_query->product['notax'],true, $wpsc_query->product['id']);
	} else {
		$output = nzshpcrt_currency_display($price, $wpsc_query->product['notax'], true);
	}
	*/
	
	$price = array_pop(get_post_meta(get_the_ID(), '_wpsc_price'));
	$output = nzshpcrt_currency_display($price, 0, true);
	return $output;
}


























/**
* wpsc display categories function
* Used to determine whether to display products on the page
* @return boolean - true for yes, false for no
*/
function wpsc_display_categories() {
  global $wp_query;
  
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
  $output = false;
	if(!is_numeric(get_option('wpsc_default_category'))) {
		if(is_numeric($wp_query->query_vars['category_id'])) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if(is_numeric($_GET['category'])) {
			$category_id = $_GET['category'];
		}
		
		// if we have no categories, and no search, show the group list
		//exit('product id '.$product_id.' catid '.$category_id );
		if(is_numeric(get_option('wpsc_default_category')) || (is_numeric($product_id)) || ($_GET['product_search'] != '')) {
		  $output = true;
		}
		if((get_option('wpsc_default_category') == 'all+list')|| (get_option('wpsc_default_category') == 'list')){
		  $output = true;
		}
	}
	
	if($category_id > 0) {
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wp_query;
	//echo "<pr".print_r($wpsc_query->category,true)."</pre>";
	if($wpsc_query->is_single === true) {
		return wpsc_product_url($wp_query->post->ID);
	} else {
		$output = wpsc_category_url($wpsc_query->category);
		if($wpsc_query->query_vars['page'] > 1) {
			//
			if(get_option('permalink_structure')) {
				$output .= "page/{$wpsc_query->query_vars['page']}/";
			} else {
				$output = add_query_arg('page_number', $wpsc_query->query_vars['page'], $output);
			}
			
		}
		return $output;
	}
}

/**
*	is single product function, determines if we are viewing a single product
* @return boolean - true, or false...
*/
function wpsc_is_single_product() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->product['category'];
}

/**
* category transition function, finds the transition between categories
* @return string - the class of the selected category
*/
function wpsc_category_transition() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpdb, $wp_query, $wpsc_query;
	$current_product_index = (int)$wpsc_query->current_product;
	$previous_product_index = ((int)$wpsc_query->current_product - 1);

	if($previous_product_index >= 0) {
		$previous_category_id = $wpsc_query->products[$previous_product_index]->category_id;
	} else {
		$previous_category_id = 0;
	}

	$current_category_id =	$wpsc_query->product['category_id'];
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
//	 exit('alo<pre>'.print_r($wpsc_query, true).'</pre>'); 
	return $wpsc_query->have_products();
}

/**
* wpsc the product function, gets the next product, 
* @return nothing
*/
function wpsc_the_product() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$wpsc_query->the_product();
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
	return $wpsc_query->rewind_products();
}

/**
* wpsc the product id function, 
* @return integer - the product ID
*/
function wpsc_the_product_id() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wp_query;
//	exit('Here'.$wp_query->post->ID);
	return $wp_query->post->ID;
}

/**
* wpsc edit the product link function
* @return string - a link to edit this product
*/
function wpsc_edit_the_product_link( $link = null, $before = '', $after = '', $id = 0 ) {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $current_user, $table_prefix, $wp_query;
	if ( $link == null ) {
		$link = __('Edit');
	}
	$product_id = $wp_query->post->ID;
		//exit('ID = '.$id.' ____<br /><pre>'.print_r($wp_query->post->ID,true).'</pre>');
	if ( $id > 0 ) {
		$product_id = $id;
	}

	$siteurl = get_option('siteurl');
	get_currentuserinfo();
	$output = '';
	if($current_user->{$table_prefix . 'capabilities'}['administrator'] == 1) {
		$output = $before . "<a class='wpsc_edit_product' href='{$siteurl}/wp-admin/admin.php?page=wpsc-edit-products&amp;product_id={$product_id}'>" . $link . "</a>" . $after;
	}
	return $output;
}

/**
* wpsc the product title function
* @return string - the product title
*/
function wpsc_the_product_title() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	//return stripslashes($wpsc_query->the_product_title());
	return htmlentities(stripslashes($wpsc_query->the_product_title()), ENT_QUOTES, "UTF-8");
}

/**
* wpsc product description function
* @return string - the product description
*/
function wpsc_the_product_description() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $allowedtags;
	$description_allowed_tags = $allowedtags + array(
		'img' => array(
			'src' => array(),'width' => array(),'height' => array(),
		),
		'span' => array(
			'style' => array()
		),
		'ul' => array(),
		'li' => array(),
		'table' => array(),
		'tr'=>array(
			'class'=>array(),
		),
		'th' => array(
			'class'=>array(),
		),
		'td' => array(
			'class'=>array(),		
		),

	);
	return wpautop(wptexturize( wp_kses(stripslashes($wpsc_query->product['description']), $description_allowed_tags )));
}

/**
* wpsc additional product description function
* TODO make this work with the tabbed multiple product descriptions, may require another loop
* @return string - the additional description
*/
function wpsc_the_product_additional_description() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->product['additional_description'];
}


/**
* wpsc product permalink function
* @return string - the URL to the single product page for this product
*/
function wpsc_the_product_permalink() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wp_query;
	return wpsc_product_url($wp_query->post->ID);
}

/**
* wpsc external link function
* @return string - the product price
*/
function wpsc_product_external_link($id){
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpdb, $wpsc_query;
	$id = absint($id);
	$externalLink = $wpdb->get_var("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id`='{$id}' AND `meta_key`='external_link' LIMIT 1");
	return $externalLink;
}

/**
* wpsc product sku function
* @return string - the product price
*/
function wpsc_product_sku($id){
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpdb;
	$id = absint($id);
	$sku = $wpdb->get_var("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id`='{$id}' AND `meta_key`='sku' LIMIT 1");
	return $sku;
}



/**
* wpsc product creation time function
* @return string - the product price
*/
function wpsc_product_creation_time($format = null) {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
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
function wpsc_product_has_stock() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
 // Is the product in stock?
	global $wpsc_query;
	if((count($wpsc_query->first_variations) == 0) && ($wpsc_query->product['quantity_limited'] == 1) && ($wpsc_query->product['quantity'] < 1)) {
		return false;
	} else {
		return true;
	}
}




/**
* wpsc product remaining stock function
* @return integer - the amount of remaining stock, or null if product is stockless
*/
function wpsc_product_remaining_stock() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	// how much stock is left?
	global $wpsc_query;
	if((count($wpsc_query->first_variations) == 0) && ($wpsc_query->product['quantity_limited'] == 1) && ($wpsc_query->product['quantity'] > 0)) {
		return $wpsc_query->product['quantity'];
	} else {
		return null;
	}
}

/**
* wpsc is donation function
* @return boolean - true if it is a donation, otherwise false
*/
function wpsc_product_is_donation() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
 // Is the product a donation?
	global $wpsc_query;
	if($wpsc_query->product['donation'] == 1) {
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	// function to determine if the product is on special
	global $wpsc_query;
	//echo "<pre>".print_r($wpsc_query,true)."</pre>";
	// && (count($wpsc_query->first_variations) < 1)
	if(($wpsc_query->product['special_price'] > 0) && (($wpsc_query->product['price'] - $wpsc_query->product['special_price']) >= 0)) {
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wpdb;
	
	$engraved_text = get_product_meta($wpsc_query->product['id'], 'engraved');
	$can_have_uploaded_image = get_product_meta($wpsc_query->product['id'], 'can_have_uploaded_image');
	if(($engraved_text == 'on') || ($can_have_uploaded_image == 'on')) {
		return true;
	}
	return false;
}


/**
* wpsc product has personal text function
* @return boolean - true if the product has a file
*/
function wpsc_product_has_personal_text() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wpdb;
	$engraved_text = get_product_meta($wpsc_query->product['id'], 'engraved');
	if($engraved_text == 'on') {
		return true;
	}
	return false;
}

/**
* wpsc product has personal file function
* @return boolean - true if the product has a file
*/
function wpsc_product_has_supplied_file() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wpdb;
	$can_have_uploaded_image = get_product_meta($wpsc_query->product['id'], 'can_have_uploaded_image');
	if($can_have_uploaded_image == 'on') {
		return true;
	}
	return false;
}

/**
* wpsc product postage and packaging function
* @return string - currently only valid for flat rate
*/
function wpsc_product_postage_and_packaging() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return nzshpcrt_currency_display($wpsc_query->product['pnp'], 1, true);
}

/**
* wpsc normal product price function
* TODO determine why this function is here
* @return string - returns some form of product price
*/
function wpsc_product_normal_price() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$price = calculate_product_price($wpsc_query->product['id'], $wpsc_query->first_variations, true);
	if(($wpsc_query->product['special_price'] > 0) && (($wpsc_query->product['price'] - $wpsc_query->product['special_price']) >= 0)) {
		$output = nzshpcrt_currency_display($price, $wpsc_query->product['notax'],true,$wpsc_query->product['id']);
	} else {
		$output = nzshpcrt_currency_display($price, $wpsc_query->product['notax'], true);
	}
	return $output;
}



/**
* wpsc product thumbnail function
* @return string - the URL to the thumbnail image
*/
function wpsc_the_product_thumbnail() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	// show the thumbnail image for the product
	global $wpsc_query;
	return $wpsc_query->the_product_thumbnail();
}

/**
* wpsc product comment link function
* @return string - javascript required to make the intense debate link work
*/
function wpsc_product_comment_link() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
 // add the product comment link
	global $wpsc_query;
	
	if (get_option('wpsc_enable_comments') == 1) {
		$enable_for_product = get_product_meta($wpsc_query->product['id'], 'enable_comments');
	
		if ((get_option('wpsc_comments_which_products') == 1 && $enable_for_product == '') || $enable_for_product == 'yes') {
			$original = array("&","'",":","/","@","?","=");
			$entities = array("%26","%27","%3A","%2F","%40","%3F","%3D");
	
			$output = "<div class=\"clear comments\">
						<script src='http://www.intensedebate.com/js/getCommentLink.php?acct=".get_option("wpsc_intense_debate_account_id")."&postid=product_".$wpsc_query->product['id']."&posttitle=".urlencode($wpsc_query->product['name'])."&posturl=".str_replace($original, $entities, wpsc_product_url($wpsc_query->product['id'], null, false))."&posttime=".urlencode(date('Y-m-d h:i:s', time()))."&postauthor=author_".$wpsc_query->product['id']."' type='text/javascript' defer='defer'></script>
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->have_custom_meta();
}

/**
* wpsc the custom meta function
* @return nothing - iterate through the custom meta vallues
*/
function wpsc_the_custom_meta() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$wpsc_query->the_custom_meta();
}

/**
* wpsc have variation groups function
* @return boolean - true while we have variation groups
*/
function wpsc_have_variation_groups() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->have_variation_groups();
}

/**
* wpsc the variation group function
* @return nothing - iterate through the variation groups
*/
function wpsc_the_variation_group() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$wpsc_query->the_variation_group();
}

/**
* wpsc have variations function
* @return boolean - true while we have variations
*/
function wpsc_have_variations() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->have_variations();
}

/**
* wpsc the variation function
* @return nothing - iterate through the variations
*/
function wpsc_the_variation() {
	global $wpsc_query;
	$wpsc_query->the_variation();
}


function wpsc_product_has_multicurrency(){
	global $wpdb, $wpsc_query;
	$sql = "SELECT `meta_key`, `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id`=".$wpsc_query->product['id']." AND `meta_key` LIKE 'currency%'";
	$results = $wpdb->get_results($sql, ARRAY_A);
	if(count($results) > 0){
		return true;
	}else{
		return false;
	}
//	exit('<pre>'.print_r($results, true).'</pre>');

}

function wpsc_display_product_multicurrency(){
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpdb, $wpsc_query;
	
	$output = '';
	$sql = "SELECT `meta_key`, `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id`=".$wpsc_query->product['id']." AND `meta_key` LIKE 'currency%'";
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
 // get the variation group name;
	global $wpsc_query;
	return $wpsc_query->variation_group['name'];
}

/**
* wpsc variation group form ID function
* @return string - the variation group form id, for labels and the like
*/
function wpsc_vargrp_form_id() {
 // generate the variation group form ID;
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$form_id = "variation_select_{$wpsc_query->product['id']}_{$wpsc_query->variation_group['variation_id']}";
	return $form_id;
}

/**
* wpsc variation group ID function
* @return integer - the variation group ID
*/
function wpsc_vargrp_id() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->variation_group['variation_id'];
}

/**
* wpsc the variation name function
* @return string - the variation name
*/
function wpsc_the_variation_name() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return stripslashes($wpsc_query->variation['name']);
}

/**
* wpsc the variation ID function
* @return integer - the variation ID
*/
function wpsc_the_variation_id() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->variation['id'];
}


/**
* wpsc the variation out_of_stock function
* @return string - HTML attribute to disable select options and radio buttons
*/
function wpsc_the_variation_out_of_stock() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query, $wpdb;
	$out_of_stock = false;
	//$wpsc_query->the_variation();
	if(($wpsc_query->variation_group_count == 1) && ($wpsc_query->product['quantity_limited'] == 1)) {
		$product_id = $wpsc_query->product['id'];
		$variation_group_id = $wpsc_query->variation_group['variation_id'];
		$variation_id = $wpsc_query->variation['id'];
		

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
* wpsc custom meta name function
* @return string - the custom metal name
*/
function wpsc_custom_meta_name() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return	$wpsc_query->custom_meta_values['meta_key'];
}

/**
* wpsc custom meta value function
* @return string - the custom meta value
*/
function wpsc_custom_meta_value() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return	$wpsc_query->custom_meta_values['meta_value'];
}

/**
* wpsc product rater function
* @return string - HTML to display the product rater
*/
function wpsc_product_rater() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	if(get_option('product_ratings') == 1) {
		$output .= "<div class='product_footer'>";

		$output .= "<div class='product_average_vote'>";
		$output .= "<strong>".__('Avg. Customer Rating', 'wpsc').":</strong>";
		$output .= wpsc_product_existing_rating($wpsc_query->product['id']);
		$output .= "</div>";
		
		$output .= "<div class='product_user_vote'>";

		//$vote_output = nzshpcrt_product_vote($wpsc_query->product['id'],"onmouseover='hide_save_indicator(\"saved_".$wpsc_query->product['id']."_text\");'");
		$output .= "<strong><span id='rating_".$wpsc_query->product['id']."_text'>".__('Your Rating', 'wpsc').":</span>";
		$output .= "<span class='rating_saved' id='saved_".$wpsc_query->product['id']."_text'> ".__('Saved', 'wpsc')."</span>";
		$output .= "</strong>";
		
		$output .= wpsc_product_new_rating($wpsc_query->product['id']);
		$output .= "</div>";
		$output .= "</div>";
	}
	return	$output;
}


function wpsc_product_existing_rating($product_id) {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
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
* wpsc has breadcrumbs function
* @return boolean - true if we have and use them, false otherwise
*/
function wpsc_has_breadcrumbs() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;	
	if(($wpsc_query->breadcrumb_count > 0) && (get_option("show_breadcrumbs") == 1)){
		return true;
	} else {
		return false;
	}
}

/**
* wpsc have breadcrumbs function
* @return boolean - true if we have breadcrimbs to loop through
*/
function wpsc_have_breadcrumbs() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->have_breadcrumbs();
}

/**
* wpsc the breadcrumbs function
* @return nothing - iterate through the breadcrumbs
*/
function wpsc_the_breadcrumb() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	$wpsc_query->the_breadcrumb();
}

/**
* wpsc breadcrumb name function
* @return string - the breadcrumb name 
*/
function wpsc_breadcrumb_name() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->breadcrumb['name'];
}

/**
* wpsc breadcrumb URL function
* @return string - the breadcrumb URL
*/
function wpsc_breadcrumb_url() {
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	if($wpsc_query->breadcrumb['url'] == '') {
		return false;
	} else {
		return $wpsc_query->breadcrumb['url'];
	}
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	if($wpsc_query->page_count > 0) {
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	if(get_option('multi_add') == 1){
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
	_deprecated_function( __FUNCTION__, '3.8', 'the updated '.__FUNCTION__.'' );
	global $wpsc_query;
	return $wpsc_query->product_count;
}
?>