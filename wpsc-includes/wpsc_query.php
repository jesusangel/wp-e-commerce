<?php
/**
 * WP eCommerce query class and product display functions
 *
 * This is the wpsc equivalent of the wp query class, needed for the wpsc templates to work
 * The Cart class handles adding, removing and adjusting items in the cart, and totaling up the cost of the items in the cart.
 * The Cart Items class handles the same, but for cart items themselves.
 *
 * This code contains modified methods from the wp_query object of WordPress, located in wp-includes/query.php, these parts are to do with the loops we use that mirror the functionality of the wordpress loop
 * As such, they can be used in the same way, if needed.
 * 
 * @package wp-e-commerce
 * @since 3.7
 * @subpackage wpsc-cart-classes
*/


/**
 * The WPSC Query class.
 *
 * @since 3.7
 */
class WPSC_Query {

	var $query;
	var $query_vars = array();
	var $queried_object;
	var $queried_object_id;
	var $request;

	// This selected category, for the breadcrumbs
	var $category;
	var $category_id_list = array();
	var $category_product = array();
	
	
	// product loop variables.
	var $products;
	var $product_count = 0;
	var $total_product_count = 0;
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

	/**
	 * The WPSC query constructor, if no query is passed to it, it makes one from the WP_Query data
	 *
	 * @since 3.7
	 *
	 * @param string $query URL query string.
	 * @return WPSC_Query
	 */
	function WPSC_Query ($query = '') {
		if (empty($query)) {
			$query = $this->create_default_query();
		}
		$this->parse_query($query);
		
		$this->get_products();
		//echo("<pre>".print_r($this,true)."</pre>");
	}


	function create_default_query() {
		global $wp_query;
		
		// get the product_url_name or ID
		if(isset($wp_query->query_vars['product_url_name']) && ($wp_query->query_vars['product_url_name'] != '')) {
			$query['product_url_name'] = $wp_query->query_vars['product_url_name'];
		} else if(is_numeric($_GET['category'])) {
			$query['product_id'] = $_GET['product_id'];
		}
		
		
		// get the category ID
		if($wp_query->query_vars['category_id'] > 0) {
			$query['category_id'] = $wp_query->query_vars['category_id'];
		} else if(is_numeric($_GET['category'])) {
			$query['category_id'] = $_GET['category'];
		}
		
		// get the category ID
		if($wp_query->query_vars['category_id'] > 0) {
			$query['category_id'] = $wp_query->query_vars['category_id'];
		}				
		
		// get the page number
		if($wp_query->query_vars['wpsc_page'] > 0) {
			$query['page'] = $wp_query->query_vars['wpsc_page'];
		} else if(is_numeric($_GET['page_number'])) {
			$query['page'] = $_GET['page_number'];
		}
		
		if(isset($_GET['order'])) {
			$query['sort_order'] = $_GET['order'];
			$_SESSION['wpsc_product_order'] = $_GET['order'];
		} else if(isset($_GET['product_order'])) {
			$query['sort_order'] = $_GET['product_order'];
			$_SESSION['wpsc_product_order'] = $_GET['order'];
		} else if(isset($_SESSION['wpsc_product_order'])) {
			$query['sort_order'] = $_SESSION['wpsc_product_order'];
		}
		
		
		if(isset($_GET['items_per_page'])) {
			$query['number_per_page'] = $_GET['items_per_page'];
			$_SESSION['wpsc_number_per_page'] = $_GET['items_per_page'];
		} else if(isset($_SESSION['wpsc_product_order'])) {
			$query['number_per_page'] = $_SESSION['wpsc_number_per_page'];
		}
		
		//$query['sort_order']
		//$query['number_per_page']
		
		return $query;
	}

	/**
	* Resets query flags to false.
	*
	* The query flags are what page info wp-eCommerce was able to figure out, same as the equivalent method on WordPress
	**/
	function init_query_flags() {
		$this->is_search = false;
		$this->is_feed = false;
		$this->is_404 = false;
	}
	
	
	/**
	 * Initiates object properties and sets default values, same as the equivalent method on WordPress
	 *
	 * @since 3.7
	 * @access public
	**/
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
	
	/**
	 * Fills in the query variables, which do not exist within the parameter.
	 *
	 * @since 3.7
	 * @access public
	 *
	 * @param array $array Defined query variables.
	 * @return array Complete query variables with undefined ones filled in empty.
	 */
	function fill_query_vars($array) {
	
		/// remove the comments at the ends of lines in this array once all of these work, until then, only the ones with "// works" work
		$keys = array(
			'product_id'	// works
			, 'product_url_name'	// works
			, 'product_name'	
			, 'category_id'	// works
			, 'category_url_name'	// works
			, 'tag'
			, 'price'
			, 'limit_of_items'
			, 'sort_order'
			, 'number_per_page'	// works
			, 'page'
			, 'custom_query'
			//, 'sku'
		);
		
		foreach ($keys as $key) {
			if ( !isset($array[$key]))
				$array[$key] = '';
		}
		
		return $array;
	}
	
	/**
	 * Parse a query string and set query type booleans.
	 *
	 * @since 3.7
	 * @access public
	 *
	 * @param string|array $query
	 */
	function parse_query ($query) {
		if ( !empty($query) || !isset($this->query) ) {
			$this->init();
			if ( is_array($query) )
				$this->query_vars = $query;
			else
				parse_str($query, $this->query_vars);
			$this->query = $query;
		}

		$this->query_vars = $this->fill_query_vars($this->query_vars);
		$qv = &$this->query_vars;
		
		
		// we need a category ID
		if(!($qv['category_id'] > 0) && ($qv['category_url_name'] == '')) {
			$qv['category_id'] = get_option('wpsc_default_category');
		}
		
		// we need a number of items per page
		if(!($qv['number_per_page'] > 0) && ($qv['number_per_page'] != 'all')) {
			$qv['number_per_page'] = get_option('wpsc_products_per_page');
		}
		
		
		
		
		
		
		$qv['product_id'] = absint($qv['product_id']);
		$qv['product_url_name'] = trim($qv['product_url_name']);
		$qv['product_name'] = trim($qv['product_name']);
		$qv['category_id'] = absint($qv['category_id']);
		$qv['category_url_name'] = trim($qv['category_url_name']);
		$qv['tag'] = trim($qv['tag']);
		$qv['price'] = absint($qv['price']);
		$qv['limit_of_items'] = absint($qv['limit_of_items']);
		$qv['sort_order'] = trim($qv['sort_order']);
		$qv['number_per_page'] = absint($qv['number_per_page']);
		$qv['page'] = absint($qv['page']);
		$qv['custom_query'] = (bool)$qv['custom_query'];

		
}
	
	
	function &get_products() {
		global $wpdb, $wp_query;
		do_action_ref_array('pre_get_products', array(&$this));
		
		if(($this->query_vars['category_url_name'] != '')) {
			$category_data = $wpdb->get_row("SELECT `id`, `image_height`, `image_width` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `nice-name` = '{$this->query_vars['category_url_name']}' LIMIT 1", ARRAY_A);	
			$this->query_vars['category_id'] = $category_data['id'];
			$this->category = $this->query_vars['category_id'];
		} else if($this->query_vars['category_id'] > 0) {
			$category_data = $wpdb->get_row("SELECT `image_height`, `image_width` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `id` = '{$this->query_vars['category_id']}' LIMIT 1", ARRAY_A);
		}
		$this->category_id_list = array($this->query_vars['category_id']);
		if($this->query_vars['category_id'] > 0) {
		  $this->category_id_list = array_merge((array)$this->category_id_list, (array)wpsc_list_subcategories($this->query_vars['category_id']));
		}

		
		//exit('Here:<pre>'.print_r($category_id_list, true).'</pre>');
		if(is_array($category_data)) {
			$this->category_product['image_height'] = $category_data['image_height'];
			$this->category_product['image_width'] = $category_data['image_width'];
		}
		
		if($this->query_vars['product_url_name'] != null) {
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".stripslashes($this->query_vars['product_url_name'])."' ) ORDER BY `product_id` DESC LIMIT 1");
		} else {
			$product_id = absint($this->query_vars['product_id']);
		}
		

		if(($product_id > 0)) {
			$product_list = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".(int)$product_id."' AND `active` IN('1') AND `publish` IN('1') LIMIT 1",ARRAY_A);
		}
		
		if(isset($_SESSION['price_range']) && isset($_GET['range'])){
			if (is_numeric($_GET['range']) || isset($_SESSION['price_range'])) {
					$price_ranges = $_SESSION['price_range'];

					$selected_price_point = absint($_GET['range']);
					$next_price_point = $selected_price_point + 1;
					//echo "<pre>".print_r($ranges,true)."</pre>";
					$product_range_sql_parts = array();
					$variation_sql_parts = array();
					$product_sql_parts = array();
					
					if(isset($price_ranges[$selected_price_point])) {
						$product_range_sql_parts[] = "(`price` - `special_price`) >= '".absint($price_ranges[$selected_price_point])."'";
						$variation_sql_parts[] = "`price` >= '".absint($price_ranges[$selected_price_point])."'";
						
						if(isset($price_ranges[$next_price_point])) {
							$product_range_sql_parts[] = "(`price` - `special_price`) < '".absint($price_ranges[$next_price_point])."'";
							$variation_sql_parts[] = "`price` < '".absint($price_ranges[$next_price_point])."'";
						}
						$variation_product_ids = (array)$wpdb->get_col("SELECT DISTINCT `product_id` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE ".implode(" AND ", $variation_sql_parts)."");
						if(count($variation_product_ids) > 0 ) {
							$product_sql_parts[] = "( (".implode(" AND ", $product_range_sql_parts).") OR `id` IN('".implode("', '", $variation_product_ids)."') )";
						} else {
							$product_sql_parts += $product_range_sql_parts;
						}
					}

				$product_sql_parts[] = "`active` IN ('1')";
				$product_sql_parts[] = "`publish` IN('1')";
										
					
				$range_sql="SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE ".implode(" AND ", $product_sql_parts)."";
				//echo $range_sql;

				$product_list = $wpdb->get_results($range_sql,ARRAY_A);
			}
		}
		//exit('Here:<pre>'.print_r($this->query_vars, true).'</pre>');
		if(count($product_list) > 0 && !isset($_GET['range'])) {
			// if is a single product
			$this->is_single = true;
			$this->products = $product_list;
			
			$this->category = $this->query_vars['category_id'];
			
		} elseif(count($product_list) > 0 && isset($_GET['range'])) {
		
			$this->is_single = false;
			$this->products = $product_list;
			
			$this->category = $this->query_vars['category_id'];
			
		}else{
			// Otherwise
			
			
		//if we are using pages, how many items per page and what page?
		if((get_option('use_pagination') == 1)) {
			$products_per_page = $this->query_vars['number_per_page'];
			if($this->query_vars['page'] > 0) {
				$startnum = ($this->query_vars['page']-1)*$products_per_page;
			} else {
				$startnum = 0;
			}
		} else {
			$startnum = 0;
		}
			
			
			
			
		// search section is done here
		if(function_exists('gold_shpcrt_search_sql') && ($_GET['product_search'] != '')) {
			$search_sql = gold_shpcrt_search_sql();
			if($search_sql != '') {
				// this cannot currently list products that are associated with no categories
				$rowcount = $wpdb->get_var("SELECT COUNT( DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.`id`) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` AND `".WPSC_TABLE_PRODUCT_LIST."`.donation = '0' $search_sql");
				if (isset($_SESSION['item_per_page']))
				$products_per_page = $_SESSION['item_per_page'];
				//exit($products_per_page);
			if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
			
			if($startnum >= $rowcount) {
				$startnum = 0;			
			}
				
				$sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` $no_donations_sql $search_sql ORDER BY `".WPSC_TABLE_PRODUCT_LIST."`.`special` DESC LIMIT $startnum, $products_per_page";
			}
		} else if (($wp_query->query_vars['ptag'] != null) || ( $_GET['ptag']!=null)) {
			// search by tags is done here
			if($wp_query->query_vars['ptag'] != null) {
				$tag = $wp_query->query_vars['ptag'];
			} else {
				$tag = $_GET['ptag'];
			}
		
		
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->terms}` WHERE slug='$tag'");
			
			$term_id = $results[0]->term_id;
			
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->term_taxonomy}` WHERE term_id = '".$term_id."' AND taxonomy='product_tag'");
			
			$taxonomy_id = $results[0]->term_taxonomy_id;
			
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->term_relationships}` WHERE term_taxonomy_id = '".$taxonomy_id."'");
			
			foreach ($results as $result) {
				$product_ids[] = $result->object_id; 
			}
			$product_id = implode(",",$product_ids);
		
			$sql = "SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id IN (".$product_id.") AND `publish` IN('1') AND `active` IN('1')"; //Transom - added publish & active
		} else {
			// select by category is done here
		 
			
			
		 
			if(is_numeric($this->query_vars['category_id']) && ($this->query_vars['category_id'] > 0)) {
					
				/*
					* The reason this is so complicated is because of the product ordering, it is done by category/product association
					* If you can see a way of simplifying it and speeding it up, then go for it.
				*/
					
					
				$rowcount = $wpdb->get_var("SELECT COUNT( DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.`id`) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active` = '1' AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` IN ('".implode("','", $this->category_id_list)."') $no_donations_sql");
				
				if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
				if($startnum >= $rowcount) {
					$startnum = 0;			
				}
				
				if($this->query_vars['sort_order']=='DESC') {
					$order = 'DESC';
				} else {
					$order = 'ASC';
				}
				
				
				// Invert this for alphabetical ordering.
				if (get_option('wpsc_sort_by')=='name') {
					if(	$order == 'ASC'){
						$order = 'DESC';
					}else{
						$order = 'ASC';
					}
					$order_by = "`products`.`name` $order";
				} else if (get_option('wpsc_sort_by') == 'price') {
					$order_by = "`products`.`price` $order";
				} elseif(get_option('wpsc_sort_by') == 'dragndrop'){
					$order_by = "`".WPSC_TABLE_PRODUCT_ORDER."`.`order` ASC";
				} else {
				
					//$order = 'ASC';
					if(	$order == 'ASC'){
						$product_id_order = 'DESC';
					}else{
						$product_id_order = 'ASC';
					}				
					$order_by = " `category`.`name`, `order_state` DESC,`order`.`order` $order, `products`.`id` $product_id_order";
					//$order_by = " `order_state` DESC, `products`.`id` $product_id_order,`order`.`order` $order";
				}
					
				$sql = "SELECT DISTINCT `products`. * , `category`.`name` AS `category` , `cat_assoc`.`category_id` , `order`.`order` , IF( ISNULL( `order`.`order` ) , 0, 1 ) AS `order_state`
				FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products`
				LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `cat_assoc`
					ON `products`.`id` = `cat_assoc`.`product_id`
				LEFT JOIN `".WPSC_TABLE_PRODUCT_CATEGORIES."` AS `category`
					ON `cat_assoc`.`category_id` = `category`.`id`
				LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` AS `order`
					ON (
						(`products`.`id` = `order`.`product_id`)
						AND
						(`cat_assoc`.`category_id` = `order`.`category_id`)
					)
				WHERE `products`.`publish` = '1'
				AND `products`.`active` = '1'
				AND `cat_assoc`.`category_id` IN ( '".implode("','", $this->category_id_list)."' ) $no_donations_sql
				ORDER BY $order_by LIMIT $startnum, $products_per_page";
				
			} else {
				if ($this->query_vars['sort_order']=='DESC') {
					$order = 'DESC';
				} else {
					$order = 'ASC';
				}
				
				if (get_option('wpsc_sort_by')=='name') {
					$order_by = "`".WPSC_TABLE_PRODUCT_LIST."`.`name` $order";
				} else if (get_option('wpsc_sort_by') == 'price') {
					$order_by = "`".WPSC_TABLE_PRODUCT_LIST."`.`price` $order";
				} else {
					if(	$order == 'ASC'){
						$order = 'DESC';
					}else{
						$order = 'ASC';
					}				
					$order_by = "`".WPSC_TABLE_PRODUCT_LIST."`.`id` $order";
				}
				$rowcount = $wpdb->get_var("SELECT COUNT( DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.`id`) AS `count` FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` $no_donations_sql $group_sql");
				
				if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
				if($startnum >= $rowcount) {
					$startnum = 0;			
				}
				
				$sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.* FROM `".WPSC_TABLE_PRODUCT_LIST."`,`".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`publish`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`active`='1' AND `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` $no_donations_sql $group_sql ORDER BY `".WPSC_TABLE_PRODUCT_LIST."`.`special`, $order_by LIMIT $startnum, $products_per_page";
			}
		}
		
	
					
		//echo "{$sql}";
		$this->category = $this->query_vars['category_id'];
		$this->products = $wpdb->get_results($sql,ARRAY_A);
		
		$this->total_product_count = $rowcount;
		
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
		$product_view_url = wpsc_category_url($this->category);

		if(!is_numeric($_GET['category']) && ($_GET['product_search'] != '')) {
			$product_view_url = add_query_arg('product_search', $_GET['product_search'], $product_view_url);
			//$product_view_url_not_used .= "product_search=".$_GET['product_search']."&amp;"."view_type=".$_GET['view_type']."&amp;"."item_per_page=".$_GET['item_per_page']."&amp;";
		}
		
		if(!is_numeric($_GET['category']) && ($_GET['item_per_page'] > 0)) {
			$product_view_url = add_query_arg('item_per_page', $_GET['item_per_page'], $product_view_url);
		}
		
		if(isset($_GET['order']) && ($_GET['order'] == 'ASC') || ($_GET['order'] == 'DESC')	) {
			$product_view_url = add_query_arg('order', $_GET['order'], $product_view_url);
		}
		
		if(isset($_GET['view_type']) && ($_GET['view_type'] == 'default') || ($_GET['view_type'] == 'grid')	) {
			$product_view_url = add_query_arg('view_type', $_GET['view_type'], $product_view_url);
		}
		
		
		
		for($i=1;$i<=$pages;$i++) {
			if(($this->query_vars['page'] == $i) || (($this->query_vars['page'] <= $i) && ($i <= 1))) {
				if($_GET['view_all'] != 'true') {
					$selected = true;
				}
			} else {
				$selected = false;
			}
			
			if(get_option('permalink_structure')) {
				//if()
				$page_url = wpsc_category_url($this->category, true)."page/$i/";
			} else {
				$page_url = add_query_arg('page_number', $i, $product_view_url);
			}
			$this->pages[$i-1]['number'] = $i;
			$this->pages[$i-1]['url'] = $page_url;
			$this->pages[$i-1]['selected'] = $selected;
		}		
		
		$this->page_count =	count($this->pages);
	
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
		unset($this->product); // make sure it is unset
		$this->product =& $this->products[$this->current_product];
		return $this->product;
	}

	
	function the_product() {
		$this->in_the_loop = true;
		$this->next_product();
		$this->get_variation_groups();
		$this->get_custom_meta();
		if ( $this->current_product == 0 ) {
			do_action('wpsc_loop_start');
		}
	}

	function have_products() {
		if ($this->current_product + 1 < $this->product_count) {
			return true;
		} else if ($this->current_product + 1 == $this->product_count && $this->product_count > 0) {
			do_action('wpsc_loop_end');
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
		$this->variation_groups = $wpdb->get_results("SELECT `v`.`id` AS `variation_id`,`v`.`name`	FROM `".WPSC_TABLE_VARIATION_ASSOC."` AS `a` JOIN `".WPSC_TABLE_PRODUCT_VARIATIONS."` AS `v` ON `a`.`variation_id` = `v`.`id` WHERE `a`.`type` IN ('product') AND `a`.`associated_id` IN ('{$this->product['id']}')", ARRAY_A);
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
		if ($this->current_variation_group + 1 < $this->variation_group_count) {
			return true;
		} else if ($this->current_variation_group + 1 == $this->variation_group_count && $this->variation_group_count > 0) {
			$this->rewind_variation_groups();
		}
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
		$this->all_associated_variations = array();
		foreach((array)$this->variation_groups as $variation_group) {
		  $variation_id = $variation_group['variation_id'];
			$this->all_associated_variations[$variation_id] = $wpdb->get_results("SELECT `v`.* FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` AS `a`JOIN `".WPSC_TABLE_VARIATION_VALUES."` AS `v` ON `a`.`value_id` = `v`.`id` WHERE `a`.`product_id` IN ('{$this->product['id']}') AND `a`.`variation_id` IN ('$variation_id') AND `a`.`visible` IN ('1') ORDER BY `v`.`id` ASC", ARRAY_A);
			$this->first_variations[] = $this->all_associated_variations[$variation_id][0]['id'];
		}
	}


	function get_variations() {
		global $wpdb;
		//$this->variations	= $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '$value_id' ORDER BY `id` ASC",ARRAY_A);
		$this->variations = $this->all_associated_variations[$this->variation_group['variation_id']];
		$this->variation_count = count($this->variations);
		//echo "<pre>".print_r($this->all_associated_variations,true)."</pre>";
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
		//$this->variations	= $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` = '$value_id' ORDER BY `id` ASC",ARRAY_A);
		$this->custom_meta = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `product_id` IN('{$this->product['id']}') AND `custom` IN('1') ", ARRAY_A);
		$this->custom_meta_count = count($this->custom_meta);
	}
	
	function next_custom_meta() {
		$this->current_custom_meta++;
		$this->custom_meta_values = $this->custom_meta[$this->current_custom_meta];
		
//		 echo "<pre>".print_r($this->custom_meta_values,true)."</pre>";
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
				$this->breadcrumbs[$i]['name'] = htmlentities(stripslashes($this->product['name']), ENT_QUOTES, 'UTF-8');
				$this->breadcrumbs[$i]['url'] = '';
				$i++;
			}
			
			$category_info =	$wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." WHERE id='".(int)$this->category."'",ARRAY_A);
			$this->breadcrumbs[$i]['id'] = $category_info['id'];
			$this->breadcrumbs[$i]['name'] = $category_info['name'];
			if($i > 0) {
				$this->breadcrumbs[$i]['url'] = wpsc_category_url($category_info['id']);
			} else {
				$this->breadcrumbs[$i]['url'] = '';
			}
			$i++;
			
			while ($category_info['category_parent']!=0) {
				$category_info =	$wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_CATEGORIES." WHERE id='{$category_info['category_parent']}'",ARRAY_A);			
				$this->breadcrumbs[$i]['id'] = $category_info['id'];
				$this->breadcrumbs[$i]['name'] = htmlentities(stripslashes($category_info['name']), ENT_QUOTES, 'UTF-8');
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
		
//		 echo "<pre>".print_r($this->breadcrumb,true)."</pre>";
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
		
//		 echo "<pre>".print_r($this->page,true)."</pre>";
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
	
	
	function the_product_thumbnail() {
		
		global $wpdb;
		$image_file_name = null;
		
		if ( $this->product['thumbnail_image'] != null ) {
			$image_file_name = $this->product['thumbnail_image'];
		} else if ( $this->product['image'] != null ) {
			if ( $this->product['image_file'] != null ) {
				$image_file_name = $this->product['image_file'];
			} else {
				if ( is_numeric($this->product['image']) ) {
					$image_file_name = $wpdb->get_var("SELECT `image` FROM `" . WPSC_TABLE_PRODUCT_IMAGES . "` WHERE `id`= '" . $this->product['image'] . "' LIMIT 1");
				} else {
					$image_file_name = $this->product['image'];
				}
				$this->product['image_file'] = $image_file_name;
			}
			$this->product['thumbnail_image'] = $image_file_name;
		}
		
		if ( $image_file_name !== null ) {
			if ( ($this->category_product['image_height'] != null) && ($this->category_product['image_width'] != null) && (function_exists('ImagePNG')) ) {
				$image_path = "index.php?productid=" . $this->product['id'] . "&amp;width=" . $this->category_product['image_width']."&amp;height=" . $this->category_product['image_height'] . "";
			} else {
				$image_path = WPSC_THUMBNAIL_URL . $image_file_name;
				if ( is_ssl() ) {
					$image_path = str_replace("http://", "https://", $image_path);
				}
			}
			return $image_path;
		} else {
			return false;
		}
		
	}
	/** 
 * NEW CODE SUPPLIED BY btray77 FOR AWESOME PAGINATION
 */
 ///////////////////////////////////////////////////////////////////////////////////////

	function is_pagination_on()	{
		if (get_option( 'use_pagination' ) == 1){
			return true;
		}else {
			return false;
		}
	}
	
	function prodcuts_per_page()
	{
		return get_option( 'wpsc_products_per_page' );
	}

	function a_page_url($page=null) {
	//exit('<pre>'.print_r($this, true).'</pre>');
		$curpage = $this->query_vars['page'];
		if($page != ''){
			$this->query_vars['page'] = $page;
		}
		//global $wpsc_query;

		if($this->is_single === true) {
			$this->query_vars['page'] = $curpage;
			return wpsc_product_url($this->product['id']);
		} else {
			$output = wpsc_category_url($this->category);
				//exit('PAge <pre>'.print_r($this,true).'</pre>');

			if($this->query_vars['page'] > 1) {
				//
				if(get_option('permalink_structure')) {
					$output .= "page/{$this->query_vars['page']}/";
				} else {
					$output = add_query_arg('page_number', '', $output);
				}
			}
		//	$this->query_vars['page'] = $urpage;
		//	exit('Whats returned: '.$output);
			return $output;
		}
	}
	function pagination($totalpages, $per_page, $current_page, $page_link) {
			if ($current_page ==''){
			if(isset($_GET['page_number']) && is_numeric($_GET['page_number'])){
			 $current_page = $_GET['page_number'];
			}else{
				$current_page=1;
			}
		
		} 
		//exit($pagelink.'<<<<<');
		if(!get_option('permalink_structure')) {
			$category = '';
			if(isset($_GET['category']) && is_numeric($_GET['category'])){
				$category = '&category='.$_GET['category'];
			}
			$page_link = get_option('product_list_url').$category.'&page_number';
			$seperator = '=';
		}else{
			$page_link = get_option('product_list_url');
			$seperator = 'page/';
		}
			//exit($totalpages.'<br />'.$per_page.'<br />'.$current_page.'<br />'.$page_link);

		// If there's only one page, return now and don't bother
		if($totalpages == 1) return;
		// Pagination Prefix
		$output = "Pages: ";
		// Should we show the FIRST PAGE link?
		if($current_page > 1) {
			$output .= "<a href=\"". $page_link ."\" title=\"First Page\"> << First </a>";
		}
		// Should we show the PREVIOUS PAGE link?
		if($current_page > 1 && ($current_page-1) != 1) {
			$previous_page = $current_page - 1;	
			$output .= " <a href=\"". $page_link .$seperator. $previous_page ."\" title=\"Previous Page\"> < Previous </a>";
		}
		$i =$current_page - 5;
		$count = 0;
		if($i > 0){
	//		exit($i.' '.$current_page);
			while(($i) < $current_page){
				if($i > 0){
					$output .= " <a href=\"". $page_link .$seperator. $i ."\" title=\"Page ".$i." \"> ".$i."  </a>";
					$i++;
				}
			}
		}else{
	
		}
		if($current_page > 0) {
		// Current Page Number
		$output .= "<strong>[ ". $current_page ." ]</strong>";
		// Should we show the NEXT PAGE link?
		}
		$i = $current_page+1;
		$count = 0;
		if($current_page + 5 <= $totalpages){
			while(($i) < $totalpages){
				if($count <=4 ){
					$output .= " <a href=\"". $page_link .$seperator. $i ."\" title=\"Page ".$i." \"> ".$i."  </a>";
					$i++;
				}else{
					break;
				}
				$count ++;

			}

		
		}
		
		if($current_page < $totalpages) {
			$next_page = $current_page + 1;
			$output .= "<a href=\"". $page_link  .$seperator. $next_page ."\" title=\"Next Page\"> Next > </a>";
		}
		// Should we show the LAST PAGE link?
		if($current_page < $totalpages - 1) {
			$output .= " <a href=\"". $page_link  .$seperator. $totalpages ."\" title=\"Last Page\"> Last >> </a>";
		}
		// Return the output.
		return $output;
	}

	///////////////////////////////////////////////////////////////////////////////////////
	/* 	END OF btray77 code for pagination */
	
}

//The following code was removed from WP 3.8, present in 3.7 - Not sure why it was removed and not re-written. (JS)


/**
* wpsc the variation price function
* @return string - the variation price
*/
function wpsc_the_variation_price() {
	global $wpdb, $wpsc_query;
	
    if(count($wpsc_query->variation_groups) == 1) {
		//echo "<pre>".print_r($wpsc_query->variation, true)."</pre>";
		$product_id = $wpsc_query->product['id'];
		$variation_group_id = $wpsc_query->variation_group['variation_id'];
		$variation_id = $wpsc_query->variation['id'];
		
		$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product_id}' AND `value_id` IN ( '$variation_id' ) AND `all_variation_ids` IN('$variation_group_id') LIMIT 1");
		
		$variation_price = $wpdb->get_var("SELECT `price` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1");

		$output = nzshpcrt_currency_display($variation_price, $wpsc_query->product['notax'], true);    		
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
	global $wpsc_query, $wpdb;
	$out_of_stock = false;
	if(($wpsc_query->variation_group_count == 1) && ($wpsc_query->product['quantity_limited'] == 1)) {
		$product_id = $wpsc_query->product['id'];
		$variation_group_id = $wpsc_query->variation_group['variation_id'];
		$variation_id = $wpsc_query->variation['id'];
		

		$priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '{$product_id}' AND `value_id` IN ( '$variation_id' ) AND `all_variation_ids` IN('$variation_group_id') LIMIT 1");
		
		$variation_stock_data = $wpdb->get_var("SELECT `stock` FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1");
		
	}
	return $variation_stock_data;
}
			
?>