<?php
/*
* this is the wpsc equivalent of the wp query class, needed for the wpsc templates to work
*/

/**
*  this page url function, returns the URL of this page
* @return string - the URL of the current page
*/
function wpsc_this_page_url() {
	global $wpsc_query;
	//echo "<pre>".print_r($wpsc_query->category,true)."</pre>";
	if($wpsc_query->is_single === true) {
		return wpsc_product_url($wpsc_query->product['id']);
	} else {
		return wpsc_category_url($wpsc_query->category);
	}
}

/**
*  is single product function, determines if we are viewing a single product
* @return boolean - true, or false...
*/
function wpsc_is_single_product() {
	global $wpsc_query;
	if($wpsc_query->is_single === true) {
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
* wpsc have products function, the product loop
* @return boolean true while we have products, otherwise, false
*/
function wpsc_have_products() {
	global $wpsc_query;
	return $wpsc_query->have_products();
}

/**
* wpsc the product function, gets the next product, 
* @return nothing
*/
function wpsc_the_product() {
	global $wpsc_query;
	$wpsc_query->the_product();
}

/**
* wpsc in the loop function, 
* @return boolean - true if we are in the loop
*/
function wpsc_in_the_loop() {
	global $wpsc_query;
	return $wpsc_query->in_the_loop;
}

/**
* wpsc rewind products function, rewinds back to the first product
* @return nothing
*/
function wpsc_rewind_products() {
	global $wpsc_query;
	return $wpsc_query->rewind_products();
}

/**
* wpsc the product id function, 
* @return integer - the product ID
*/
function wpsc_the_product_id() {
	global $wpsc_query;
	return $wpsc_query->product['id'];
}

/**
* wpsc the product title function
* @return string - the product title
*/
function wpsc_the_product_title() {
	global $wpsc_query;
	return htmlentities(stripslashes($wpsc_query->the_product_title()), ENT_QUOTES);
}

/**
* wpsc product description function
* @return string - the product description
*/
function wpsc_the_product_description() {
	global $wpsc_query;
	return wpautop(stripslashes($wpsc_query->product['description']));
}

/**
* wpsc additional product description function
* TODO make this work with the tabbed multiple product descriptions, may require another loop
* @return string - the additional description
*/
function wpsc_the_product_additional_description() {
	global $wpsc_query;
	return $wpsc_query->product['additional_description'];
}


/**
* wpsc product permalink function
* @return string - the URL to the single product page for this product
*/
function wpsc_the_product_permalink() {
	global $wpsc_query;
	return wpsc_product_url($wpsc_query->product['id']);
}

/**
* wpsc product price function
* @return string - the product price
*/
function wpsc_the_product_price() {
  global $wpsc_query;  
  $price = calculate_product_price($wpsc_query->product['id'], $wpsc_query->first_variations);  
  if(($product['special']==1) && ($variations_output[1] === null)) {
    $output = nzshpcrt_currency_display($price, $wpsc_query->product['notax'],false,$wpsc_query->product['id']);
  } else {
    $output = nzshpcrt_currency_display($price, $wpsc_query->product['notax']);
  }
  if(get_option('display_pnp') == 1) {
    //$output = nzshpcrt_currency_display($wpsc_query->product['pnp'], 1);
  }
  return $output;
}

/**
* wpsc product has stock function
* TODO this may need modifying to work with variations, test this
* @return boolean - true if the product has stock or does not use stock, false if it does not
*/
function wpsc_product_has_stock() {
 // Is the product in stock?
	global $wpsc_query;
	if((count($wpsc_query->first_variations) == 0) && ($wpsc_query->product['quantity_limited'] == 1) && ($wpsc_query->product['quantity'] < 1)) {
		return false;
	} else {
		return true;
	}
}

/**
* wpsc is donation function
* @return boolean - true if it is a donation, otherwise false
*/
function wpsc_product_is_donation() {
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
  // function to determine if the product is on special
	global $wpsc_query;
	if(($wpsc_query->product['special'] == 1) && (count($wpsc_query->first_variations) < 1)) {
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
	global $wpsc_query;
  return nzshpcrt_currency_display($wpsc_query->product['pnp'], 1);
}

/**
* wpsc normal product price function
* TODO determine why this function is here
* @return string - returns some form of product price
*/
function wpsc_product_normal_price() {
	global $wpsc_query;
  $price = calculate_product_price($wpsc_query->product['id'], $wpsc_query->first_variations);  
  if(($product['special']==1) && ($variations_output[1] === null)) {
    $output = nzshpcrt_currency_display($price, $wpsc_query->product['notax'],false,$wpsc_query->product['id']);
  } else {
    $output = nzshpcrt_currency_display($price, $wpsc_query->product['notax']);
  }
  return $output;
}

/**
* wpsc product image function
* if no parameters are passed, the image is not resized, otherwise it is resized to the specified dimensions
* @param integer width
* @param integer height
* @return string - the product image URL, or the URL of the resized version
*/
function wpsc_the_product_image($width = null, $height = null) {
  // show the full sized image for the product, if supplied with dimensions, will resize image to those.
	global $wpsc_query;
	$image_file_name = null;
  if ($wpsc_query->product['image'] != null) {
    $image_file_name = $wpsc_query->product['image'];
  }
  if($image_file_name != null) {
    if(($width > 0) && ($height > 0)) {
      return "index.php?productid=".$wpsc_query->product['id']."&amp;width=".$width."&amp;height=".$height;
    } else {
      return WPSC_IMAGE_URL.$image_file_name;
    }
  } else {
    return WPSC_URL."/images/no-image-uploaded.gif";
  }
}

/**
* wpsc product thumbnail function
* @return string - the URL to the thumbnail image
*/
function wpsc_the_product_thumbnail() {
  // show the thumbnail image for the product
	global $wpsc_query;
	 $image_file_name = null;
  if($wpsc_query->product['thumbnail_image'] != null) {
    $image_file_name = $wpsc_query->product['thumbnail_image'];
  } else if ($wpsc_query->product['image'] != null) {
    $image_file_name = $wpsc_query->product['image'];
  }
  
  if($image_file_name !== null) {
    return wpsc_product_image_html($image_file_name, $wpsc_query->product['id']);
  } else {
    return WPSC_URL."/images/no-image-uploaded.gif";
  }
}

/**
* wpsc product comment link function
* @return string - javascript required to make the intense debate link work
*/
function wpsc_product_comment_link() {
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
	global $wpsc_query;
	return $wpsc_query->have_custom_meta();
}

/**
* wpsc the custom meta function
* @return nothing - iterate through the custom meta vallues
*/
function wpsc_the_custom_meta() {
	global $wpsc_query;
	$wpsc_query->the_custom_meta();
}

/**
* wpsc have variation groups function
* @return boolean - true while we have variation groups
*/
function wpsc_have_variation_groups() {
	global $wpsc_query;
	return $wpsc_query->have_variation_groups();
}

/**
* wpsc the variation group function
* @return nothing - iterate through the variation groups
*/
function wpsc_the_variation_group() {
	global $wpsc_query;
	$wpsc_query->the_variation_group();
}

/**
* wpsc have variations function
* @return boolean - true while we have variations
*/
function wpsc_have_variations() {
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



/**
* wpsc variation group name function
* @return string - the variaton group name
*/
function wpsc_the_vargrp_name() {
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
	global $wpsc_query;
	$form_id = "variation_select_{$wpsc_query->product['id']}_{$wpsc_query->variation_group['variation_id']}";
  return $form_id;
}

/**
* wpsc variation group ID function
* @return integer - the variation group ID
*/
function wpsc_vargrp_id() {
	global $wpsc_query;
  return $wpsc_query->variation_group['variation_id'];
}

/**
* wpsc the variation name function
* @return string - the variation name
*/
function wpsc_the_variation_name() {
	global $wpsc_query;
	return $wpsc_query->variation['name'];
}

/**
* wpsc the variation ID function
* @return integer - the variation ID
*/
function wpsc_the_variation_id() {
	global $wpsc_query;
	return $wpsc_query->variation['id'];
}

/**
* wpsc custom meta name function
* @return string - the custom metal name
*/
function wpsc_custom_meta_name() {
	global $wpsc_query;
	return  $wpsc_query->custom_meta_values['meta_key'];
}

/**
* wpsc custom meta value function
* @return string - the custom meta value
*/
function wpsc_custom_meta_value() {
	global $wpsc_query;
	return  $wpsc_query->custom_meta_values['meta_value'];
}

/**
* wpsc product rater function
* @return string - HTML to display the product rater
*/
function wpsc_product_rater() {
	global $wpsc_query;
	if(get_option('product_ratings') == 1) {
		$output .= "<div class='product_footer'>";

		$output .= "<div class='product_average_vote'>";
		$output .= "<strong>".TXT_WPSC_AVGCUSTREVIEW.":</strong>";
		$output .= nzshpcrt_product_rating($wpsc_query->product['id']);
		$output .= "</div>";
		
		$output .= "<div class='product_user_vote'>";
		$vote_output = nzshpcrt_product_vote($wpsc_query->product['id'],"onmouseover='hide_save_indicator(\"saved_".$wpsc_query->product['id']."_text\");'");
		if($vote_output[1] == 'voted') {
			$output .= "<strong><span id='rating_".$wpsc_query->product['id']."_text'>".TXT_WPSC_YOURRATING.":</span>";
			$output .= "<span class='rating_saved' id='saved_".$wpsc_query->product['id']."_text'> ".TXT_WPSC_RATING_SAVED."</span>";
			$output .= "</strong>";
		} else if($vote_output[1] == 'voting') {
			$output .= "<strong><span id='rating_".$wpsc_query->product['id']."_text'>".TXT_WPSC_RATETHISITEM.":</span>";
			$output .= "<span class='rating_saved' id='saved_".$wpsc_query->product['id']."_text'> ".TXT_WPSC_RATING_SAVED."</span>";
			$output .= "</strong>";
		}
		$output .= $vote_output[0];
		$output .= "</div>";
		$output .= "</div>";
	}
	return  $output;
}

/**
* wpsc has breadcrumbs function
* @return boolean - true if we have and use them, false otherwise
*/
function wpsc_has_breadcrumbs() {
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
	global $wpsc_query;
	return $wpsc_query->have_breadcrumbs();
}

/**
* wpsc the breadcrumbs function
* @return nothing - iterate through the breadcrumbs
*/
function wpsc_the_breadcrumb() {
	global $wpsc_query;
	$wpsc_query->the_breadcrumb();
}

/**
* wpsc breadcrumb name function
* @return string - the breadcrumb name 
*/
function wpsc_breadcrumb_name() {
	global $wpsc_query;
	return $wpsc_query->breadcrumb['name'];
}

/**
* wpsc breadcrumb URL function
* @return string - the breadcrumb URL
*/
function wpsc_breadcrumb_url() {
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
	global $wpsc_query;
	return $wpsc_query->have_pages();
}

/**
* wpsc the page function
* @return nothing - iterate through the pages
*/
function wpsc_the_page() {
	global $wpsc_query;
	$wpsc_query->the_page();
}
	
/**
* wpsc page number function
* @return integer - the page number
*/
function wpsc_page_number() {
	global $wpsc_query;
	return $wpsc_query->page['number'];
}

/**
* wpsc page is selected function
* @return boolean - true if the page is selected
*/
function wpsc_page_is_selected() {
 // determine if we are on this page
	global $wpsc_query;
	return $wpsc_query->page['selected'];
}

/**
* wpsc papge URL function
* @return string - the page URL
*/
function wpsc_page_url() {
 // generate the page URL
	global $wpsc_query;
	return $wpsc_query->page['url'];
}

class WPSC_Query {

	var $query;
	var $query_vars = array();
	var $queried_object;
	var $queried_object_id;
	var $request;

  // This selected category, for the breadcrumbs
	var $category;

	// product loop variables.
	var $products;
	var $product_count = 0;
	var $current_product = -1;
	var $in_the_loop = false;
	var $product;
	
	// variation groups: i.e. colour, size
	var $variation_groups;
	var $variation_group_count = 0;
	var $current_variation_group = -1;
	var $variation_group;
	
	// for getting the product price
	var $first_variations;
	
	//variations inside variation groups: i.e. (red, green, blue) or (S, M, L, XL)
	var $variations;
	var $variations_count = 0;
	var $current_variation = -1;
	var $variation;
	
	
	// Custom meta values
	var $custom_meta;
	var $custom_meta_count = 0;
	var $current_custom_meta = -1;
	var $custom_meta_values;
	
	
	// Breadcrumbs
	var $breadcrumbs;
	var $breadcrumb_count = 0;
	var $current_breadcrumb = -1;
	var $breadcrumb;
	
	// Pages
	var $pages;
	var $page_count = 0;
	var $current_page = -1;
	var $page;
	
	
	
	
	var $found_products = 0;
	var $max_num_pages = 0;
	
	var $is_single = false;
	var $is_search = false;
	var $is_feed = false;
	var $is_404 = false;
	
	function init_query_flags() {
    $this->is_search = false;
    $this->is_feed = false;
    $this->is_404 = false;
	}
	
	
	function init () {
	  $this->category = null;
		unset($this->products);
		unset($this->query);
		$this->query_vars = array();
		unset($this->queried_object);
		unset($this->queried_object_id);
		$this->product_count = 0;
		$this->current_product = -1;
		$this->in_the_loop = false;

    $this->variation_groups = null;
    $this->variation_group = null;
    
    $this->variations = null;
    $this->variation = null;
    
		$this->custom_meta = null;
		$this->custom_meta_values = null;
		
		$this->breadcrumbs = null;
		$this->breadcrumb = null;
		
		$this->pages = null;
		$this->page = null;


		$this->init_query_flags();
	}
	
  function &get_products() {
    global $wpdb, $wp_query;
  
		do_action_ref_array('pre_get_products', array(&$this));
		if($wp_query->query_vars['product_name'] != null){
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wp_query->query_vars['product_name']."' ) ORDER BY `product_id` DESC LIMIT 1");
		} else {
			$product_id = $_GET['product_id'];
		}
		
		if(($product_id > 0)) {
		  $product_list = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".(int)$product_id."' AND `publish` IN('1') AND `active` IN('1') LIMIT 1",ARRAY_A);
		}
		
		if(count($product_list) > 0) {
		  // if is a single product
			$this->is_single = true;
		  $this->products = $product_list;
			if(is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['product_category']) || is_numeric(get_option('wpsc_default_category'))) {
				if($wp_query->query_vars['product_category'] != null) {
					$catid = $wp_query->query_vars['product_category'];
					} else if(is_numeric($_GET['category'])) {
						$catid = $_GET['category'];
					} else if(is_numeric($GLOBALS['wpsc_category_id'])) {
						$catid = $GLOBALS['wpsc_category_id'];
					} else {
						$catid = get_option('wpsc_default_category');
					}
			  $this->category = $catid;
			}
		} else {
		  // Otherwise
		  $products_per_page =  get_option('posts_per_page');
		
			if((get_option('use_pagination') == 1)) {
				$products_per_page = get_option('wpsc_products_per_page');
				if (isset($_REQUEST['items_per_page'])){
					$products_per_page = $_REQUEST['items_per_page'];
				}
				if(($_GET['page_number'] > 0)) {
					$startnum = ($_GET['page_number']-1)*$products_per_page;
				} else {
					$startnum = 0;
				}
			} else {
				$startnum = 0;
			}
			
			
			
			
			
		if(function_exists('gold_shpcrt_search_sql') && ($_GET['product_search'] != '')) {
			$search_sql = gold_shpcrt_search_sql();
			if($search_sql != '') {
				// this cannot currently list products that are associated with no categories
				$rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`".WPSC_TABLE_PRODUCT_LIST."`.`id`) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` $no_donations_sql $search_sql");
				if (isset($_SESSION['item_per_page']))
				$products_per_page = $_SESSION['item_per_page'];
				//exit($products_per_page);
			if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
				if(($startnum >= $rowcount) && (($rowcount - $products_per_page) >= 0)) {
					$startnum = $rowcount - $products_per_page;
				}
				
				$sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` $no_donations_sql $search_sql ORDER BY `".WPSC_TABLE_PRODUCT_LIST."`.`special` DESC LIMIT $startnum, $products_per_page";
			}
		} else if (($wp_query->query_vars['ptag'] != null) || ( $_GET['ptag']!=null)) {
			if($wp_query->query_vars['ptag'] != null) {
				$tag = $wp_query->query_vars['ptag'];
			} else {
				$tag = $_GET['ptag'];
			}
		
		
			$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}terms WHERE slug='$tag'");
			
			$term_id = $results[0]->term_id;
			
			$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}term_taxonomy WHERE term_id = '".$term_id."' AND taxonomy='product_tag'");
			
			$taxonomy_id = $results[0]->term_taxonomy_id;
			
			$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id = '".$taxonomy_id."'");
			
			foreach ($results as $result) {
				$product_ids[] = $result->object_id; 
			}
			$product_id = implode(",",$product_ids);
		
			$sql = "SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id IN (".$product_id.") AND `publish` IN('1') AND `active` IN('1')"; //Transom - added publish & active
		} else {
			if(is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['product_category']) || is_numeric(get_option('wpsc_default_category'))) {
				if($wp_query->query_vars['product_category'] != null) {
					$catid = $wp_query->query_vars['product_category'];
					} else if(is_numeric($_GET['category'])) {
						$catid = $_GET['category'];
					} else if(is_numeric($GLOBALS['wpsc_category_id'])) {
						$catid = $GLOBALS['wpsc_category_id'];
					} else {
						$catid = get_option('wpsc_default_category');
					}
					
			  $this->category = $catid;
				/*
					* The reason this is so complicated is because of the product ordering, it is done by category/product association
					* If you can see a way of simplifying it and speeding it up, then go for it.
					*/
					
					
				$rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`".WPSC_TABLE_PRODUCT_LIST."`.`id`) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active` = '1' AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` IN ('".$catid."') $no_donations_sql");
				
				if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
				if(($startnum >= $rowcount) && (($rowcount - $products_per_page) >= 0)) {
					$startnum = $rowcount - $products_per_page;
				}
				if ($_REQUEST['order']==null) {
					$order = 'ASC';
				} elseif ($_REQUEST['order']=='DESC') {
					$order = 'DESC';
				}
				
				
				
				if (get_option('wpsc_sort_by')=='name') {
					$order_by = "`".WPSC_TABLE_PRODUCT_LIST."`.`name` $order";
				} else if (get_option('wpsc_sort_by') == 'price') {
					$order_by = "`".WPSC_TABLE_PRODUCT_LIST."`.`price` $order";
				} else {
					$order_by = " `order_state` DESC,`".WPSC_TABLE_PRODUCT_ORDER."`.`order` $order, `".WPSC_TABLE_PRODUCT_LIST."`.`id` DESC";
				}
				
				$sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.*, `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id`,`".WPSC_TABLE_PRODUCT_ORDER."`.`order`, IF(ISNULL(`".WPSC_TABLE_PRODUCT_ORDER."`.`order`), 0, 1) AS `order_state` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` ON ( ( `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_PRODUCT_ORDER."`.`product_id` ) AND ( `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_ORDER."`.`category_id` ) ) WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active` = '1' AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` IN ('".$catid."') $no_donations_sql ORDER BY $order_by LIMIT $startnum, $products_per_page";
			} else {
				$rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`".WPSC_TABLE_PRODUCT_LIST."`.`id`) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` $no_donations_sql $group_sql");
				
				if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
				if(($startnum >= $rowcount) && (($rowcount - $products_per_page) >= 0)) {
					$startnum = $rowcount - $products_per_page;
				}
				
				$sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` $no_donations_sql $group_sql ORDER BY `".WPSC_TABLE_PRODUCT_LIST."`.`special`, `".WPSC_TABLE_PRODUCT_LIST."`.`id`  DESC LIMIT $startnum, $products_per_page";
			}
		}
		
	
					
		// shows page numbers, probably fairly obviously
	// exit($sql);
		$this->products = $wpdb->get_results($sql,ARRAY_A);
		
		if($rowcount > $products_per_page) {
				if($products_per_page > 0) {
					$pages = ceil($rowcount/$products_per_page);
				} else {
					$pages = 1;
				}
			}
    }
    
    
		if(get_option('permalink_structure') != '') {
			$seperator ="?";
		} else {
			$seperator ="&amp;";
		}
		$product_view_url = wpsc_category_url($catid).$seperator;

    if(is_numeric($_GET['category'])) {
		} else if(is_numeric($_GET['brand'])) {
      $product_view_url .= "brand=".$_GET['brand']."&amp;";
		} else if($_GET['product_search'] != '') {
      $product_view_url .= "product_search=".$_GET['product_search']."&amp;"."view_type=".$_GET['view_type']."&amp;"."item_per_page=".$_GET['item_per_page']."&amp;";
		}
		
		if(isset($_GET['order']) && ($_GET['order'] == 'ASC') || ($_GET['order'] == 'DESC')  ) {
		  $product_view_url .= "order={$_GET['order']}&amp;";
		}
		
		if(isset($_GET['view_type']) && ($_GET['view_type'] == 'default') || ($_GET['view_type'] == 'grid')  ) {
		  $product_view_url .= "view_type={$_GET['view_type']}&amp;";
		}
    
    
    
    for($i=1;$i<=$pages;++$i) {
      if(($_GET['page_number'] == $i) || (!is_numeric($_GET['page_number']) && ($i == 1))) {
        if($_GET['view_all'] != 'true') {
          $selected = true;
				}
			} else {
        $selected = false;
			}
			$this->pages[$i-1]['number'] = $i;
			$this->pages[$i-1]['url'] = $product_view_url."page_number=$i";
			$this->pages[$i-1]['selected'] = $selected;
		}    
    
		$this->page_count =  count($this->pages);
  
		//if ( !$q['suppress_filters'] )
    $this->products = apply_filters('the_products', $this->products);
    
    $this->product_count = count($this->products);
		if ($this->product_count > 0) {
			$this->product = $this->products[0];
		}
		
		// get the breadcrumbs
		$this->get_breadcrumbs();
		return $this->products;
  }

	function next_product() {
		$this->current_product++;
		$this->product = $this->products[$this->current_product];
		return $this->product;
	}

  
  function the_product() {
		$this->in_the_loop = true;
		$this->product = $this->next_product();
		$this->get_variation_groups();
		$this->get_custom_meta();
		if ( $this->current_product == 0 ) // loop has just started
			do_action('wpsc_loop_start');
	}

	function have_products() {
		if ($this->current_product + 1 < $this->product_count) {
			return true;
		} else if ($this->current_product + 1 == $this->product_count && $this->product_count > 0) {
			do_action('wpsc_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_products();
		}

		$this->in_the_loop = false;
		return false;
	}

	function rewind_products() {
		$this->current_product = -1;
		if ($this->product_count > 0) {
			$this->product = $this->products[0];
		}
	}  
  
  
	/*
	 * (Variation Group and Variation) Loop Code Starts here
	*/
  function get_variation_groups() {
    global $wpdb;
    $this->variation_groups = $wpdb->get_results("SELECT `v`.`id` AS `variation_id`,`v`.`name`  FROM `".WPSC_TABLE_VARIATION_ASSOC."` AS `a` JOIN `".WPSC_TABLE_PRODUCT_VARIATIONS."` AS `v` ON `a`.`variation_id` = `v`.`id` WHERE `a`.`type` IN ('product') AND `a`.`associated_id` IN ('{$this->product['id']}')", ARRAY_A);
    $this->variation_group_count = count($this->variation_groups);
    $this->get_first_variations();
  }
  
	
	function next_variation_group() {
		$this->current_variation_group++;
		$this->variation_group = $this->variation_groups[$this->current_variation_group];
		return $this->variation_group;
	}

  
  function the_variation_group() {
		$this->variation_group = $this->next_variation_group();
		$this->get_variations();
	}

	function have_variation_groups() {
    //echo "<pre>".print_r($wpsc_query->variation_group_count,true)."</pre>";
    //echo "<pre>".print_r($wpsc_query->current_variation_group,true)."</pre>";
		if ($this->current_variation_group + 1 < $this->variation_group_count) {
			return true;
		} else if ($this->current_variation_group + 1 == $this->variation_group_count && $this->variation_group_count > 0) {
			//do_action('wpsc_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_variation_groups();
		}

		//$this->in_the_loop = false;
		return false;
	}

	function rewind_variation_groups() {
		$this->current_variation_group = -1;
		if ($this->variation_group_count > 0) {
			$this->variation_group = $this->variation_groups[0];
		}
	}
	
  function get_first_variations() {
    global $wpdb;
    $this->first_variations = array();
    foreach((array)$this->variation_groups as $variation_group) {
      $this->first_variations[] = $wpdb->get_var("SELECT `v`.`id` FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` AS `a`JOIN `".WPSC_TABLE_VARIATION_VALUES."` AS `v` ON `a`.`value_id` = `v`.`id` WHERE `a`.`product_id` IN ('{$this->product['id']}') AND `a`.`variation_id` IN ('{$variation_group['variation_id']}') AND `a`.`visible` IN ('1') ORDER BY `v`.`id` ASC LIMIT 1");
    }
  }


  function get_variations() {
    global $wpdb;
    //$this->variations  = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '$value_id' ORDER BY `id` ASC",ARRAY_A);
    $this->variations = $wpdb->get_results("SELECT `v`.* FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` AS `a`JOIN `".WPSC_TABLE_VARIATION_VALUES."` AS `v` ON `a`.`value_id` = `v`.`id` WHERE `a`.`product_id` IN ('{$this->product['id']}') AND `a`.`variation_id` IN ('{$this->variation_group['variation_id']}') AND `a`.`visible` IN ('1') ORDER BY `v`.`id` ASC", ARRAY_A);
    $this->variation_count = count($this->variations);
    //echo "<pre>".print_r($this->variations,true)."</pre>";
  }
  
	
	function next_variation() {
		$this->current_variation++;
		$this->variation = $this->variations[$this->current_variation];
		return $this->variation;
	}

  
  function the_variation() {
		$this->variation = $this->next_variation();
	}

	function have_variations() {
		if ($this->current_variation + 1 < $this->variation_count) {
			return true;
		} else if ($this->current_variation + 1 == $this->variation_count && $this->variation_count > 0) {
			//do_action('wpsc_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_variations();
		}

		//$this->in_the_loop = false;
		return false;
	}

	function rewind_variations() {
		$this->current_variation = -1;
		if ($this->variation_count > 0) {
			$this->variation = $this->variations[0];
		}
	}	
	
	
	
	
	/*
	 * Custom Meta Loop Code Starts here
	*/
  function get_custom_meta() {
    global $wpdb;
    //$this->variations  = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '$value_id' ORDER BY `id` ASC",ARRAY_A);
    $this->custom_meta = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` IN('{$this->product['id']}') AND `custom` IN('1') ", ARRAY_A);
    $this->custom_meta_count = count($this->custom_meta);
  }
  
	function next_custom_meta() {
		$this->current_custom_meta++;
		$this->custom_meta_values = $this->custom_meta[$this->current_custom_meta];
		
//     echo "<pre>".print_r($this->custom_meta_values,true)."</pre>";
		return $this->custom_meta_values;
	}

  
  function the_custom_meta() {
		$this->custom_meta_values = $this->next_custom_meta();
	}

	function have_custom_meta() {
		if ($this->current_custom_meta + 1 < $this->custom_meta_count) {
			return true;
		} else if ($this->current_custom_meta + 1 == $this->custom_meta_count && $this->custom_meta_count > 0) {
			//do_action('wpsc_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_custom_meta();
		}

		//$this->in_the_loop = false;
		return false;
	}

	function rewind_custom_meta() {
		$this->current_custom_meta = -1;
		if ($this->custom_meta_count > 0) {
			$this->custom_meta_values = $this->custom_meta[0];
		}
	}	
	
	
	/*
	 * Breadcrumb Loop Code Starts here
	*/
	
	function get_breadcrumbs() {
    global $wpdb;
    $this->breadcrumbs = array();
    $i = 0;
		if( $this->category != null) {
		  if($this->is_single == true) {
				$this->breadcrumbs[$i]['name'] = htmlentities(stripslashes($this->product['name']), ENT_QUOTES);
				$this->breadcrumbs[$i]['url'] = '';
				$i++;
		  }
		  
			$category_info =  $wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." WHERE id='".(int)$this->category."'",ARRAY_A);
			$this->breadcrumbs[$i]['name'] = $category_info['name'];
			if($i > 0) {
				$this->breadcrumbs[$i]['url'] = wpsc_category_url($category_info['id']);
			} else {
				$this->breadcrumbs[$i]['url'] = '';
			}
			$i++;
			
			
			while ($category_info['category_parent']!=0) {
				$category_info =  $wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." WHERE id='{$category_info['category_parent']}'",ARRAY_A);			
				$this->breadcrumbs[$i]['name'] = htmlentities(stripslashes($category_info['name']), ENT_QUOTES);
				$this->breadcrumbs[$i]['url'] = wpsc_category_url($category_info['id']);
				$i++;
			}
		}
		$this->breadcrumbs = array_reverse($this->breadcrumbs);
    $this->breadcrumb_count = count($this->breadcrumbs);
  }
  
	function next_breadcrumbs() {
		$this->current_breadcrumb++;
		$this->breadcrumb = $this->breadcrumbs[$this->current_breadcrumb];
		
//     echo "<pre>".print_r($this->breadcrumb,true)."</pre>";
		return $this->breadcrumb;
	}

  
  function the_breadcrumb() {
		$this->breadcrumb = $this->next_breadcrumbs();
	}

	function have_breadcrumbs() {
		if ($this->current_breadcrumb + 1 < $this->breadcrumb_count) {
			return true;
		} else if ($this->current_breadcrumb + 1 == $this->breadcrumb_count && $this->breadcrumb_count > 0) {
			//do_action('wpsc_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_breadcrumbs();
		}

		//$this->in_the_loop = false;
		return false;
	}

	function rewind_breadcrumbs() {
		$this->current_breadcrumb = -1;
		if ($this->breadcrumb_count > 0) {
			$this->breadcrumb = $this->breadcrumbs[0];
		}
	}	
	
	
	/*
	 * Page Loop Code Starts here
	*/	
	
	
	/// We get the pages in get_products
	//function get_pages() { }; 
  
	function next_pages() {
		$this->current_page++;
		$this->page = $this->pages[$this->current_page];
		
//     echo "<pre>".print_r($this->page,true)."</pre>";
		return $this->page;
	}

  
  function the_page() {
		$this->page = $this->next_pages();
	}

	function have_pages() {
		if ($this->current_page + 1 < $this->page_count) {
			return true;
		} else if ($this->current_page + 1 == $this->page_count && $this->page_count > 0) {
			//do_action('wpsc_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_pages();
		}

		//$this->in_the_loop = false;
		return false;
	}

	function rewind_pages() {
		$this->current_page = -1;
		if ($this->page_count > 0) {
			$this->page = $this->pages[0];
		}
	}	
	
	
	function the_product_title() {
    return $this->product['name'];
	}
  
	function WPSC_Query ($query = '') {
		if (! empty($query)) {
			$this->query($query);
		}
	}
}
			
		/*
			SELECT SQL_CALC_FOUND_ROWS  wp_posts.* FROM wp_posts  WHERE 1=1  AND wp_posts.post_type = 'post' AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private')  ORDER BY wp_posts.post_date DESC LIMIT 0, 5

      SELECT SQL_CALC_FOUND_ROWS  wp_posts.* FROM wp_posts  WHERE 1=1  AND wp_posts.post_type = 'post' AND (wp_posts.post_status = 'publish' OR wp_posts.post_status = 'private')  ORDER BY wp_posts.post_date DESC LIMIT 5, 5
			
    */
?>