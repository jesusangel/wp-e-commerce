<?php
/**
 * WP eCommerce core functions
 *
 * These are core functions for wp-eCommerce
 * Things like rewrite rules, wp_query modifications, link generation and some basic theme finding code is located here
 *
 * @package wp-e-commerce
 * @since 3.8
*/

/**
 * wpsc_taxonomy_rewrite_rules function.
 * Adds in new rewrite rules for categories, products, category pages, and ambiguities (either categories or products)
 * Also modifies the rewrite rules for product URLs to add in the post type.
 * 
 * @since 3.8
 * @access public
 * @param array $rewrite_rules
 * @return array - the modified rewrite rules
 */
function wpsc_taxonomy_rewrite_rules($rewrite_rules) {
	$target_string = "index.php?product";
	$replacement_string = "index.php?post_type=wpsc-product&product";
	$target_rule_set_query_var = 'products';
	
	$target_rule_set = array();
	foreach($rewrite_rules as $rewrite_key => $rewrite_query) {
		if(stristr($rewrite_query, "index.php?product")) {
			$rewrite_rules[$rewrite_key] = str_replace($target_string, $replacement_string, $rewrite_query);
		}
		if(stristr($rewrite_query, "$target_rule_set_query_var=")) {
			$target_rule_set[] = $rewrite_key;
		}
	}
	
	//$new_rewrite_rules['products/.+?/[^/]+/attachment/([^/]+)/?$'] = 'index.php?attachment=$1';
	//$new_rewrite_rules['products/(.+?)/([^/]+)/comment-page-([0-9]{1,})/?$'] = 'index.php??post_type=wpsc-product&products=$1&name=$2&cpage=$3';
	//$new_rewrite_rules['products/.+?/([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&paged=$matches[2]';
	
	
	$new_rewrite_rules['products/(.+?)/product/([^/]+)/comment-page-([0-9]{1,})/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&name=$matches[2]&cpage=$matches[3]';
	$new_rewrite_rules['products/(.+?)/product/([^/]+)/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&name=$matches[2]';
	$new_rewrite_rules['products/(.+?)/([^/]+)/comment-page-([0-9]{1,})/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&wpsc_item=$matches[2]&cpage=$matches[3]';
	$new_rewrite_rules['products/(.+?)/([^/]+)?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&wpsc_item=$matches[2]';
	$new_rewrite_rules['(products/checkout)(/[0-9]+)?/?$'] = 'index.php?pagename=$1&page=$2';

	
	$last_target_rule = array_pop($target_rule_set);
	
	$rebuilt_rewrite_rules = array();
	foreach($rewrite_rules as $rewrite_key => $rewrite_query) {
		if($rewrite_key == $last_target_rule) {
			$rebuilt_rewrite_rules = array_merge($rebuilt_rewrite_rules, $new_rewrite_rules);
		}
		$rebuilt_rewrite_rules[$rewrite_key] = $rewrite_query;
	}
	
	//echo "<pre>".print_r($new_rewrite_rules, true)."</pre>";
	return $rebuilt_rewrite_rules;
}


add_filter('rewrite_rules_array', 'wpsc_taxonomy_rewrite_rules');


/**
 * wpsc_query_vars function.
 * adds in the post_type and wpsc_item query vars
 * 
 * @since 3.8
 * @access public
 * @param mixed $vars
 * @return void
 */
function wpsc_query_vars($vars) {
	// post_type is used to specify that we are looking for products
	$vars[] = "post_type";
	// wpsc_item is used to find items that could be either a product or a product category, it defaults to category, then tries products
	$vars[] = "wpsc_item";
	return $vars;
}
add_filter('query_vars', 'wpsc_query_vars');


/**
 * wpsc_query_modifier function.
 * 
 * @since 3.8
 * @access public
 * @param object - reference to $wp_query
 * @return $query
 */
 
function wpsc_split_the_query($query) {
	global $wpsc_query;
	// These values are to be dynamically defined
	
	$products_pages = array(
		"products", 
		"products-page"
	); 
	$checkout_pagename = "products/checkout";
	
	if (in_array($query->query_vars['pagename'], $products_pages) || isset($query->query_vars['products'])) {
		$query->query_vars['pagename'] = "products";
		$query->query_vars['name'] = '';
		$query->query_vars['post_type'] = '';
		$query->is_singular = true;
		$query->is_page = true;
		$query->is_tax = false;
		$query->is_archive = false;
		$query->is_single = false;
		
		add_filter('redirect_canonical', 'wpsc_break_canonical_redirects', 10, 2);
		remove_filter('parse_query', 'wpsc_split_the_query');
		
		add_filter('parse_query', 'wpsc_generate_product_query', 11);
		
		if($wpsc_query == null) {
			$wpsc_query = new WP_Query($query->query);
		}
	}
	
	//exit("<pre>".print_r($query,true)."</pre>");
	if($query->query_vars['pagename'] == $checkout_pagename ) {
		$query->is_checkout = true;
	}
	
	return $query;
}


/**
 * wpsc_generate_product_query function.
 * 
 * @access public
 * @param mixed $query
 * @return void
 */
function wpsc_generate_product_query($query) {

	// default product selection
	if($query->query_vars['pagename'] != '') {
		$query->query_vars['post_type'] = 'wpsc-product';
		$query->query_vars['pagename'] = '';
		$query->is_page = false;
		$query->is_tax = false;
		$query->is_archive = true;
		$query->is_singular = false;
		$query->is_single = false;
	}

	// If wpsc_item is not null, we are looking for a product or a product category, check for category
	if($query->query_vars['wpsc_item'] != '') {
		$test_term = get_term_by('slug', $query->query_vars['wpsc_item'], 'wpsc_product_category');
		if($test_term->slug == $query->query_vars['wpsc_item']) {
			// if category exists (slug matches slug), set products to value of wpsc_item
			$query->query_vars['products'] = $query->query_vars['wpsc_item'];
		} else {
			// otherwise set name to value of wpsc_item
			$query->query_vars['name'] = $query->query_vars['wpsc_item'];
		}
	}
	if(($query->query_vars['products'] != null) && ($query->query_vars['name'] != null)) {
        $query->query_vars['taxonomy'] = 'wpsc_product_category';
        $query->query_vars['term'] = $query->query_vars['products'];
		$query->is_tax = false;
		$query->is_archive = true;
		$query->is_singular = false;
		$query->is_single = false;
	}
	if($query->is_tax == true) {
		new wpsc_products_by_category($query);
	} 
	
	//echo "<pre>".print_r($query, true)."</pre>";
	return $query;
}

function wpsc_mark_product_query($query) {

	if($query->query_vars['post_type'] == 'wpsc-product') {
		$query->is_product = true;
	}
	return $query;
}



add_filter('parse_query', 'wpsc_split_the_query', 10);
add_filter('parse_query', 'wpsc_mark_product_query', 12);


/**
 * wpsc_products_by_category class.
 * 
 */
class wpsc_products_by_category {
	var $sql_components = array();

	/**
	 * wpsc_products_by_category function.
	 * 
	 * @access public
	 * @param mixed $query
	 * @return void
	 */
	function wpsc_products_by_category($query) {
		global $wpdb;		
		$q = $query->query_vars;
		
		//echo "<pre>".print_r($q, true)."</pre>";
		// Category stuff for nice URLs
		if ( ('' != $q['taxonomy']) && ('' != $q['term']) && !$query->is_singular ) {
			$join = " INNER JOIN $wpdb->term_relationships 
				ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) 
			INNER JOIN $wpdb->term_taxonomy 
				ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			";
			
			$whichcat = " AND $wpdb->term_taxonomy.taxonomy = '{$q['taxonomy']}' ";
			
			$term_data = get_term_by('slug', $q['term'], $q['taxonomy']);
			
			$term_children_data = get_term_children( $term_data->term_id, $q['taxonomy'] );
			
			$in_cats = array($term_data->term_id);
			$in_cats = array_reverse(array_merge($in_cats, $term_children_data));
			
			$in_cats = "'" . implode("', '", $in_cats) . "'";
			$whichcat .= "AND $wpdb->term_taxonomy.term_id IN ($in_cats)";
			$groupby = "{$wpdb->posts}.ID";
		
			$this->sql_components['join'] = $join;
			$this->sql_components['where'] = $whichcat;
			$this->sql_components['fields'] = "{$wpdb->posts}.*, {$wpdb->term_taxonomy}.term_id";
			$this->sql_components['order_by'] = "{$wpdb->term_taxonomy}.term_id";
			$this->sql_components['group_by'] = $groupby;
		
			add_filter('posts_join', array(&$this, 'join_sql'));
			add_filter('posts_where', array(&$this, 'where_sql'));
			add_filter('posts_fields', array(&$this, 'fields_sql'));
			add_filter('posts_orderby', array(&$this, 'order_by_sql'));
			add_filter('posts_groupby', array(&$this, 'group_by_sql'));
			
		}
		//add_filter('posts_request', array(&$this, 'request_sql'));
		
	}
	
	function join_sql($sql) {
		if(isset($this->sql_components['join'])) {
			$sql = $this->sql_components['join'];
		}
		remove_filter('posts_join', array(&$this, 'join_sql'));
		return $sql;
	}

	function where_sql($sql) {
		if(isset($this->sql_components['where'])) {
			$sql = $this->sql_components['where'];
		}
		remove_filter('posts_where', array(&$this, 'where_sql'));
		return $sql;
	}
	
	function order_by_sql($sql) {
		$order_by_parts =array();
		$order_by_parts[] = $sql;
		if(isset($this->sql_components['order_by'])) {
			$order_by_parts[] = $this->sql_components['order_by'];
		}
		$order_by_parts = array_reverse($order_by_parts);
		$sql = implode(',', $order_by_parts);
		remove_filter('posts_orderby', array(&$this, 'order_by_sql'));
		return $sql;
	}
	
	function fields_sql($sql) {
		if(isset($this->sql_components['fields'])) {
			$sql = $this->sql_components['fields'];
		}
		remove_filter('posts_fields', array(&$this, 'fields_sql'));
		return $sql;
	}
	
	function group_by_sql($sql) {
		if(isset($this->sql_components['group_by'])) {
			$sql = $this->sql_components['group_by'];
		}
		remove_filter('posts_groupby', array(&$this, 'group_by_sql'));
		return $sql;
	}
	
	function request_sql($sql) {
		echo $sql ."<br />";
		remove_filter('posts_request', array(&$this, 'request_sql'));
		return $sql;
	}
	
}


function wpsc_break_canonical_redirects($redirect_url, $requested_url) {
	global $wp_query;
	
	if(stristr($requested_url, $redirect_url)) {
		return false;
	}
	return $redirect_url;

}

//


/**
 * wpsc_is_product function.
 * 
 * @since 3.8
 * @access public
 * @return boolean
 */
function wpsc_is_product() {
	global $wp_query, $rewrite_rules;
	return $wp_query->is_product;
}

/**
 * wpsc_is_product function.
 * 
 * @since 3.8
 * @access public
 * @return boolean
 */
function wpsc_is_checkout() {
	global $wp_query, $rewrite_rules;
	return $wp_query->is_checkout;
}




/**
 * wpsc_product_link function.
 * Gets the product link, hooks into post_link
 * Uses the currently selected, only associated or first listed category for the term URL
 * If the category slug is the same as the product slug, it prefixes the product slug with "product/" to counteract conflicts
 * 
 * @access public
 * @return void
 */
function wpsc_product_link($permalink, $post, $leavename) {
	global $wp_query;
	$rewritecode = array(
		'%term_url%',
		'%postname%'
	);
	$permalink_structure = get_option('permalink_structure');
	// This may become customiseable later
	$our_permalink_structure = "%term_url%/%postname%/";
	// Mostly the same conditions used for posts, but restricted to items with a post type of "wpsc-product " 
	if ( '' != $permalink_structure && !in_array($post->post_status, array('draft', 'pending')) ) {
		$product_categories = wp_get_object_terms($post->ID, 'wpsc_product_category');
		$product_category_slugs = array();
		foreach($product_categories as $product_category) {
			$product_category_slugs[] = $product_category->slug;
		}
			
		// If the product is associated with multiple categories, determine which one to pick	
		if(count($product_categories) > 1) {
			if(($wp_query->query_vars['products']!= null) && in_array($wp_query->query_vars['products'], $product_category_slugs)) {
				$product_category = $wp_query->query_vars['products'];
			} else  {
				$product_category = $product_category_slugs[0];
			}
			$category_slug = $product_category;
			$term_url = get_term_link($category_slug, 'wpsc_product_category');
		} else {
			// If the product is associated with only one category, we only have one choice
			$product_category = $product_categories[0];
			$category_slug = $product_category->slug;
			$term_url = get_term_link($category_slug, 'wpsc_product_category');
		}
		
		//echo "'><pre>_".print_r($product_category_slugs, true)."_</pre>";
		$post_name = $post->post_name;
		if(in_array($post_name, $product_category_slugs)) {
			$post_name = "product/{$post_name}";
		}
		
		$rewritereplace = array(
			untrailingslashit($term_url),
			$post_name,
		);
		
		$permalink = str_replace($rewritecode, $rewritereplace, $our_permalink_structure);
		$permalink = user_trailingslashit($permalink, 'single');
	
	}
	return $permalink;
}

add_filter('post_link', 'wpsc_product_link', 10, 3);


/**
 * wpsc_get_product_template function.
 * 
 * @since 3.8
 * @access public
 * @return void
 */
function wpsc_get_template($template) {
	return get_query_template($template);
}

/**
 * wpsc_product_template_fallback function.
 * 
 * @since 3.8
 * @access public
 * @param mixed $template_path
 * @return string - the corrected template path
 */
function wpsc_template_fallback($template_path) {
	global $wpsc_theme_path;
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
	$prospective_file_name = basename("{$template_path}.php");
	$prospective_file_path = trailingslashit($cur_wpsc_theme_folder).$prospective_file_name;
	//exit($prospective_file_path);
	
	if(!file_exists($prospective_file_path)) {
		exit($prospective_file_path);
	}
	return $prospective_file_path;
}


function wpsc_products_template_fallback() {
	return wpsc_template_fallback('products');

}
//add_filter("products_template", 'wpsc_products_template_fallback');

function wpsc_checkout_template_fallback() {
	return wpsc_template_fallback('checkout');

}
//add_filter("checkout_template", 'wpsc_checkout_template_fallback');


/**
 * wpsc_template_loader function.
 * 
 * @since 3.8
 * @access public
 * @return void
 */
function wpsc_template_loader() {
	global $wp_query;
	if ( wpsc_is_product() && $template = wpsc_get_template('products') ) {
		include($template);
		exit();
	}
	if ( wpsc_is_checkout() && $template = wpsc_get_template('checkout') ) {
		include($template);
		exit();
	}
}

add_action('template_redirect','wpsc_template_loader');



/**
 *wpsc_get_theme_file_path function, gets the path to the theme file, uses the plugin themes folder if the file is not in the uploads one
 */
function wpsc_get_theme_file_path($file) {
	// get the theme folder here
	global $wpsc_theme_path;
	$file = basename($file);
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
	if(is_file($cur_wpsc_theme_folder."/".$file)) {
		$output = $cur_wpsc_theme_folder."/".$file;
	} else {
		$wpsc_theme_path = WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR;
		$output =  $wpsc_theme_path."/".$file;
	}
	return $output;
}

/**
	*select_wpsc_theme_functions function, provides a place to override the e-commece theme path
  * add to switch "theme's functions file 
  * © with xiligroup dev
  */
function wpsc_select_theme_functions() {
  global $wpsc_theme_path;
  $theme_dir = WPSC_THEME_DIR; /* done by plugins_loaded */
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.$theme_dir);
	
	if((get_option('wpsc_selected_theme') != '') && (file_exists($cur_wpsc_theme_folder."/".$theme_dir.".php") )) { 
		include_once($cur_wpsc_theme_folder.'/'.$theme_dir.'.php');
	}
  // end add by xiligroup.dev
}

add_action('wp','wpsc_select_theme_functions',10,1);
?>