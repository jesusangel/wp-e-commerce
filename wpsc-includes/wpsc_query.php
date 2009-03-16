<?php
/*
* this is the wpsc equivalent of the wp query class, needed for the wpsc templates to work
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

function wpsc_is_single_product() {
	global $wpsc_query;
	if($wpsc_query->is_single === true) {
	  $state = true;
	} else {
	  $state = false;
	}
  return $state;
}

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
		$category_nice_name = $wpdb->get_var("SELECT `nice-name` FROM `{$wpdb->prefix}product_categories` WHERE `id` ='".(int)$catid."' LIMIT 1");
  } else if($catid == 'all') {
    $category_nice_name = 'all-categories';
  }
  //exit("<pre>".print_r(get_option('wpsc_default_category'),true)."</pre>");
	return $category_nice_name;
}

function wpsc_have_products() {
	global $wpsc_query;
	return $wpsc_query->have_products();
}

function wpsc_in_the_loop() {
	global $wpsc_query;
	return $wpsc_query->in_the_loop;
}

function rewind_products() {
	global $wpsc_query;
	return $wpsc_query->rewind_products();
}

function wpsc_the_product() {
	global $wpsc_query;
	$wpsc_query->the_product();
}

function wpsc_the_product_id() {
	global $wpsc_query;
	return $wpsc_query->product['id'];
}

function wpsc_the_product_title() {
	global $wpsc_query;
	
	return htmlentities(stripslashes($wpsc_query->the_product_title()), ENT_QUOTES);
}


function wpsc_the_product_description() {
	global $wpsc_query;
// 	echo "<pre>".print_r($wpsc_query->product,true)."</pre>";
	return wpautop(stripslashes($wpsc_query->product['description']));
}

function wpsc_the_product_additional_description() {
	global $wpsc_query;
	return $wpsc_query->product['additional_description'];
}

function wpsc_the_product_permalink() {
	global $wpsc_query;
	return wpsc_product_url($wpsc_query->product['id']);
}

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
  //$output .= "<pre>".print_r($wpsc_query->first_variations,true)."</pre>";
  return $output;
}

function wpsc_product_has_stock() {
 // Is the product in stock?
	global $wpsc_query;
	if((count($wpsc_query->first_variations) == 0) && ($wpsc_query->product['quantity_limited'] == 1) && ($wpsc_query->product['quantity'] < 1)) {
		return false;
	} else {
		return true;
	}
}

function wpsc_product_is_donation() {
 // Is the product a donation?
	global $wpsc_query;
	if($wpsc_query->product['donation'] == 1) {
		return true;
	} else {
		return false;
	}
}

function wpsc_product_on_special() {
  // function to determine if the product is on special
	global $wpsc_query;
	if(($wpsc_query->product['special'] == 1) && (count($wpsc_query->first_variations) < 1)) {
	  return true;
	} else {
	  return false;
	}
}

function wpsc_product_has_file() {
  // function to determine if the product is on special
	global $wpsc_query, $wpdb;
	if(is_numeric($wpsc_query->product['file']) && ($wpsc_query->product['file'] > 0)) {
		return true;
	}
	return false;
}

function wpsc_product_postage_and_packaging() {
	global $wpsc_query;
  return nzshpcrt_currency_display($wpsc_query->product['pnp'], 1);
}

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



function wpsc_the_vargrp_name() {
 // get the variation group name;
	global $wpsc_query;
  return $wpsc_query->variation_group['name'];
}

function wpsc_vargrp_form_id() {
 // generate the variation group form ID;
	global $wpsc_query;
	$form_id = "variation_select_{$wpsc_query->product['id']}_{$wpsc_query->variation_group['variation_id']}";
  return $form_id;
}

function wpsc_vargrp_id() {
 // generate the variation group form ID;
	global $wpsc_query;
  return $wpsc_query->variation_group['variation_id'];
}

function wpsc_the_variation_name() {
 // get the variation name;
	global $wpsc_query;
	return $wpsc_query->variation['name'];
}

function wpsc_the_variation_id() {
 // generate the variation ID;
	global $wpsc_query;
	return $wpsc_query->variation['id'];
}


function wpsc_custom_meta_name() {
 // get the variation name;
	global $wpsc_query;
	return  $wpsc_query->custom_meta_values['meta_key'];
}

function wpsc_custom_meta_value() {
 // generate the variation ID;
	global $wpsc_query;
	return  $wpsc_query->custom_meta_values['meta_value'];
}

function wpsc_product_rater() {
 // generate the variation ID;
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


function wpsc_has_breadcrumbs() {
	global $wpsc_query;
	
  if(($wpsc_query->breadcrumb_count > 0) && (get_option("show_breadcrumbs") == 1)){
    return true;
  } else {
    return false;
  }
}

function wpsc_breadcrumb_name() {
 // get the variation name;
	global $wpsc_query;
	return $wpsc_query->breadcrumb['name'];
}

function wpsc_breadcrumb_url() {
 // generate the variation ID;
	global $wpsc_query;
	if($wpsc_query->breadcrumb['url'] == '') {
	  return false;
	} else {
		return $wpsc_query->breadcrumb['url'];
	}
}

function wpsc_currency_sign() {
  global $wpdb;
	$currency_sign_location = get_option('currency_sign_location');
	$currency_type = get_option('currency_type');
	$currency_symbol = $wpdb->get_var("SELECT `symbol_html` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1") ;
	return $currency_symbol;
}

function wpsc_has_pages() {
	global $wpsc_query;
  if($wpsc_query->page_count > 0) {
    return true;
  } else {
    return false;
  }
}

function wpsc_page_number() {
 // get the variation name;
	global $wpsc_query;
	return $wpsc_query->page['number'];
}

function wpsc_page_is_selected() {
 // get the variation name;
	global $wpsc_query;
	return $wpsc_query->page['selected'];
}

function wpsc_page_url() {
 // generate the variation ID;
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
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wp_query->query_vars['product_name']."' ) ORDER BY `product_id` DESC LIMIT 1");
		} else {
			$product_id = $_GET['product_id'];
		}
		
		if(($product_id > 0)) {
		  $product_list = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".(int)$product_id."' AND `publish` IN('1') AND `active` IN('1') LIMIT 1",ARRAY_A);
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
				$rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`{$wpdb->prefix}product_list`.`id`) AS `count` FROM `{$wpdb->prefix}product_list`,`{$wpdb->prefix}item_category_associations` WHERE `{$wpdb->prefix}product_list`.`publish`='1' AND `{$wpdb->prefix}product_list`.`active`='1' AND `{$wpdb->prefix}product_list`.`id` = `{$wpdb->prefix}item_category_associations`.`product_id` $no_donations_sql $search_sql");
				if (isset($_SESSION['item_per_page']))
				$products_per_page = $_SESSION['item_per_page'];
				//exit($products_per_page);
			if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
				if(($startnum >= $rowcount) && (($rowcount - $products_per_page) >= 0)) {
					$startnum = $rowcount - $products_per_page;
				}
				
				$sql = "SELECT DISTINCT `{$wpdb->prefix}product_list`.* FROM `{$wpdb->prefix}product_list`,`{$wpdb->prefix}item_category_associations` WHERE `{$wpdb->prefix}product_list`.`publish`='1' AND `{$wpdb->prefix}product_list`.`active`='1' AND `{$wpdb->prefix}product_list`.`id` = `{$wpdb->prefix}item_category_associations`.`product_id` $no_donations_sql $search_sql ORDER BY `{$wpdb->prefix}product_list`.`special` DESC LIMIT $startnum, $products_per_page";
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
		
			$sql = "SELECT * FROM {$wpdb->prefix}product_list WHERE id IN (".$product_id.") AND `publish` IN('1') AND `active` IN('1')"; //Transom - added publish & active
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
					
					
				$rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`{$wpdb->prefix}product_list`.`id`) AS `count` FROM `{$wpdb->prefix}product_list` LEFT JOIN `{$wpdb->prefix}item_category_associations` ON `{$wpdb->prefix}product_list`.`id` = `{$wpdb->prefix}item_category_associations`.`product_id` WHERE `{$wpdb->prefix}product_list`.`publish`='1' AND `{$wpdb->prefix}product_list`.`active` = '1' AND `{$wpdb->prefix}item_category_associations`.`category_id` IN ('".$catid."') $no_donations_sql");
				
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
					$order_by = "`{$wpdb->prefix}product_list`.`name` $order";
				} else if (get_option('wpsc_sort_by') == 'price') {
					$order_by = "`{$wpdb->prefix}product_list`.`price` $order";
				} else {
					$order_by = " `order_state` DESC,`{$wpdb->prefix}product_order`.`order` $order, `{$wpdb->prefix}product_list`.`id` DESC";
				}
				
				$sql = "SELECT DISTINCT `{$wpdb->prefix}product_list`.*, `{$wpdb->prefix}item_category_associations`.`category_id`,`{$wpdb->prefix}product_order`.`order`, IF(ISNULL(`{$wpdb->prefix}product_order`.`order`), 0, 1) AS `order_state` FROM `{$wpdb->prefix}product_list` LEFT JOIN `{$wpdb->prefix}item_category_associations` ON `{$wpdb->prefix}product_list`.`id` = `{$wpdb->prefix}item_category_associations`.`product_id` LEFT JOIN `{$wpdb->prefix}product_order` ON ( ( `{$wpdb->prefix}product_list`.`id` = `{$wpdb->prefix}product_order`.`product_id` ) AND ( `{$wpdb->prefix}item_category_associations`.`category_id` = `{$wpdb->prefix}product_order`.`category_id` ) ) WHERE `{$wpdb->prefix}product_list`.`publish`='1' AND `{$wpdb->prefix}product_list`.`active` = '1' AND `{$wpdb->prefix}item_category_associations`.`category_id` IN ('".$catid."') $no_donations_sql ORDER BY $order_by LIMIT $startnum, $products_per_page";
			} else {
				$rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`{$wpdb->prefix}product_list`.`id`) AS `count` FROM `{$wpdb->prefix}product_list`,`{$wpdb->prefix}item_category_associations` WHERE `{$wpdb->prefix}product_list`.`publish`='1' AND `{$wpdb->prefix}product_list`.`active`='1' AND `{$wpdb->prefix}product_list`.`id` = `{$wpdb->prefix}item_category_associations`.`product_id` $no_donations_sql $group_sql");
				
				if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
				if(($startnum >= $rowcount) && (($rowcount - $products_per_page) >= 0)) {
					$startnum = $rowcount - $products_per_page;
				}
				
				$sql = "SELECT DISTINCT `{$wpdb->prefix}product_list`.* FROM `{$wpdb->prefix}product_list`,`{$wpdb->prefix}item_category_associations` WHERE `{$wpdb->prefix}product_list`.`publish`='1' AND `{$wpdb->prefix}product_list`.`active`='1' AND `{$wpdb->prefix}product_list`.`id` = `{$wpdb->prefix}item_category_associations`.`product_id` $no_donations_sql $group_sql ORDER BY `{$wpdb->prefix}product_list`.`special`, `{$wpdb->prefix}product_list`.`id`  DESC LIMIT $startnum, $products_per_page";
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
    $this->variation_groups = $wpdb->get_results("SELECT `v`.`id` AS `variation_id`,`v`.`name`  FROM `{$wpdb->prefix}variation_associations` AS `a` JOIN `{$wpdb->prefix}product_variations` AS `v` ON `a`.`variation_id` = `v`.`id` WHERE `a`.`type` IN ('product') AND `a`.`associated_id` IN ('{$this->product['id']}')", ARRAY_A);
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
      $this->first_variations[] = $wpdb->get_var("SELECT `v`.`id` FROM `{$wpdb->prefix}variation_values_associations` AS `a`JOIN `{$wpdb->prefix}variation_values` AS `v` ON `a`.`value_id` = `v`.`id` WHERE `a`.`product_id` IN ('{$this->product['id']}') AND `a`.`variation_id` IN ('{$variation_group['variation_id']}') AND `a`.`visible` IN ('1') ORDER BY `v`.`id` ASC LIMIT 1");
    }
  }


  function get_variations() {
    global $wpdb;
    //$this->variations  = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}variation_values` WHERE `id` = '$value_id' ORDER BY `id` ASC",ARRAY_A);
    $this->variations = $wpdb->get_results("SELECT `v`.* FROM `{$wpdb->prefix}variation_values_associations` AS `a`JOIN `{$wpdb->prefix}variation_values` AS `v` ON `a`.`value_id` = `v`.`id` WHERE `a`.`product_id` IN ('{$this->product['id']}') AND `a`.`variation_id` IN ('{$this->variation_group['variation_id']}') AND `a`.`visible` IN ('1') ORDER BY `v`.`id` ASC", ARRAY_A);
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
    //$this->variations  = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}variation_values` WHERE `id` = '$value_id' ORDER BY `id` ASC",ARRAY_A);
    $this->custom_meta = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_productmeta` WHERE `product_id` IN('{$this->product['id']}') AND `custom` IN('1') ", ARRAY_A);
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
		  
			$category_info =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}product_categories WHERE id='".(int)$this->category."'",ARRAY_A);
			$this->breadcrumbs[$i]['name'] = $category_info['name'];
			if($i > 0) {
				$this->breadcrumbs[$i]['url'] = wpsc_category_url($category_info['id']);
			} else {
				$this->breadcrumbs[$i]['url'] = '';
			}
			$i++;
			
			
			while ($category_info['category_parent']!=0) {
				$category_info =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}product_categories WHERE id='{$category_info['category_parent']}'",ARRAY_A);			
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