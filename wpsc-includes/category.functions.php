<?php
/**
 * WP eCommerce category display functions
 *
 * These are functions for the wp-eCommerce categories
 * I would like to use an object and the theme engine for this, but it uses a recursive function, and I cannot think of a way to make that work with an object like the rest of the theme engine.
 *
 * @package wp-e-commerce
 * @since 3.7
*/

/**
 * wpsc_list_categories function.
 * 
 * @access public
 * @param string $callback_function - The function name you want to use for displaying the data
 * @param mixed $parameters (default: null) - the additional parameters to the callback function
 * @param int $category_id. (default: 0) - The category id defaults to zero, for displaying all categories
 * @param int $level. (default: 0)
 */
function wpsc_list_categories($callback_function, $parameters = null, $category_id = 0, $level = 0) {
	global $wpdb,$category_data;
	$output = '';
	$category_list = get_terms('wpsc_product_category','hide_empty=0&parent='.$category_id);
	if($category_list != null) {
		foreach((array)$category_list as $category) {
			$callback_output = $callback_function($category, $level, $parameters);
			if(is_array($callback_output)) {
				$output .= array_shift($callback_output);
			} else {
				$output .= $callback_output;  
			}
			$output .= wpsc_list_categories($callback_function, $parameters , $category->term_id, ($level+1));
			if(is_array($callback_output) && (isset($callback_output[1]))) {
				$output .= $callback_output[1];
			}
		}
	}
	return $output;
}


/**
* Gets the Function Parent Image link and checks whether Image should be displayed or not
* 
*/
function wpsc_parent_category_image($show_thumbnails , $category_image , $width, $height, $grid=false){

	if(!$show_thumbnails) return;
	
	if($category_image == WPSC_CATEGORY_URL){
	?>
	<span class='wpsc_category_image item_no_image ' style='width:<?php echo $width; ?>px; height: <?php echo $height; ?>px;'>
		<span class='link_substitute' >
			<span><?php _e('N/A', 'wpsc'); ?></span>
		</span>
	</span>
	<?php
	}else{
	?><img src='<?php echo $category_image; ?>' width='<?php echo $width; ?>' height='<?php echo $height; ?>' /><?php	
	}
}
/// category template tags start here

/**
* wpsc starts category query function
* gets passed the query and makes it into a global variable, then starts capturing the html for the category loop
*/
function wpsc_start_category_query($arguments = array()) {
  global $wpdb, $wpsc_category_query;
  $wpsc_category_query = $arguments;
  ob_start();
}

/**
* wpsc print category name function
* places the shortcode for the category name
*/
function wpsc_print_category_name() {
	echo "[wpsc_category_name]";
}

/**
* wpsc print category description function
* places the shortcode for the category description, accepts parameters for the description container
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_category_description($start_element = '', $end_element = '') {
  global $wpsc_category_query;
  $wpsc_category_query['description_container'] = array('start_element' => $start_element, 'end_element' =>  $end_element);
	echo "[wpsc_category_description]";
}

/**
* wpsc print category url function
* places the shortcode for the category URL
*/
function wpsc_print_category_url() {
	echo "[wpsc_category_url]";
}

/**
* wpsc print category id function
* places the shortcode for the category URL
*/
function wpsc_print_category_id() {
	echo "[wpsc_category_id]";
}

/**
* wpsc print category classes function
* places classes for the category including selected state
*/
function wpsc_print_category_classes() {
	echo "[wpsc_category_classes]";
}

/**
* wpsc print product list function
* places the shortcode for the product list
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_product_list() {
  global $wpsc_category_query;
	if (get_option('catsprods_display_type') == 1) {
		echo "[wpsc_category_product_list]";
  }
}


/**
* wpsc print subcategory function
* places the shortcode for the subcategories, accepts parameters for the subcategories container, have this as <ul> and </ul> if using a list
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_subcategory($start_element = '', $end_element = '') {
  global $wpsc_category_query;
  $wpsc_category_query['subcategory_container'] = array('start_element' => $start_element, 'end_element' =>  $end_element);
  echo "[wpsc_subcategory]";
}


/**
* wpsc print category image function
* places the shortcode for the category image, accepts parameters for width and height
* @param integer width
* @param integer height
*/
function wpsc_print_category_image($width = null, $height = null) {
  global $wpsc_category_query;
  $wpsc_category_query['image_size'] = array('width' => $width, 'height' =>  $height);
	echo "[wpsc_category_image]";
}

/**
* wpsc print category products count function
* places the shortcode for the category product count, accepts parameters for the container element
* @param string starting HTML element
* @param string ending HTML element
*/
function wpsc_print_category_products_count($start_element = '', $end_element = '') {
  global $wpsc_category_query;
  $wpsc_category_query['products_count'] = array('start_element' => $start_element, 'end_element' =>  $end_element);
	echo "[wpsc_category_products_count]";
}

/**
* wpsc end category query function
*/
function wpsc_end_category_query() {
	global $wpdb, $wpsc_category_query;
  $category_html = ob_get_clean();
  echo wpsc_display_category_loop($wpsc_category_query, $category_html);
  unset($GLOBALS['wpsc_category_query']);
}

/**
* wpsc category loop function
* This function recursively loops through the categories to display the category tree.
* This function also generates a tree of categories at the same time
* WARNING: as this function is recursive, be careful what you do with it.
* @param array the category query
* @param string the category html
* @param array the category array branch, is an internal value, leave it alone.
* @return string - the finished category html
*/
function wpsc_display_category_loop($query, $category_html, &$category_branch = null){
	static $category_count_data = array(); // the array tree is stored in this
	global $wpdb, $wpsc_query;
	/*
	$category_sql_segments = array();
	
	$category_sql_segments[] = "`active`='1'";
	if(is_numeric($query['category_group']) ) {
		$category_group = absint($query['category_group']);
		$category_sql_segments[] = "`group_id`='$category_group'";
	} elseif($query['category_group']=='all' || $query['category_group']=='all+list') {
		$category_group = 1;
	}
	
	
	/// select by parent category
	$category_sql_segments[] = "`category_parent` = '".absint($query['parent_category_id'])."'";
	
	// order by what in which direction
	if(!isset($query['order_by'])) {
		$query['order_by'] = array("column" => 'name', "direction" =>'asc');
	}
	
	$column = $wpdb->escape($query['order_by']['column']);
	
	if(strtolower($query['order_by']['direction']) == "desc") {
		$order = "DESC";
	} else {
		$order = "ASC";
	}
	
	// filter for other plugins
	$category_sql_segments = apply_filters('wpsc_display_category_loop_category_sql_segments', $category_sql_segments); 
	$category_data = $wpdb->get_results("SELECT  `id`, `name`, `nice-name`, `description`, `image` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE ".implode(" AND ", $category_sql_segments)." ORDER BY `{$column}` $order",ARRAY_A);
	//*/
	
	
	$category_id = absint($query['parent_category_id']);
	

	//exit("<pre>".print_r($category_data,true)."</pre>");
	$category_data = get_terms('wpsc_product_category','hide_empty=0&parent='.$category_id, OBJECT, 'display');
	//print("<pre>".print_r($category_data,true)."</pre>");
	$output ='';
	
	// if the category branch is identical to null, make it a reference to $category_count_data
	if($category_branch === null) {
		$category_branch =& $category_count_data;
	}

	
	//$current_category_level = array();
	foreach((array)$category_data as $category_row) {
	
		// modifys the query for the next round
		$modified_query = $query;
		$modified_query['parent_category_id'] = $category_row->term_id;
		
		// gets the count of products associated with this category
		$category_count = $category_row->count;
		
		
		// Sticks the category description in
		$category_description = '';
		if($category_row->description != '') {
			$start_element = $query['description_container']['start_element'];
			$end_element = $query['description_container']['end_element'];
			$category_description =  $start_element.wpautop(wptexturize( wp_kses(stripslashes($category_row->description), $allowedtags ))).$end_element;
		}
		
		
		// Creates the list of classes on the category item
		$category_classes = 'wpsc-cat-item wpsc-cat-item-' . $category_row->term_id;
		if ( $wpsc_query->query_vars['category_id'] == $category_row->term_id) {
			$category_classes .= ' wpsc-current-cat';
		}
		
		// Set the variables for this category
		$category_branch[$category_row->term_id]['children'] = array();
		$category_branch[$category_row->term_id]['count'] = (int)$category_count;
		
		
		// Recurse into the next level of categories
		$sub_categories = wpsc_display_category_loop($modified_query, $category_html, $category_branch[$category_row->term_id]['children']);
		
		// grab the product count from the subcategories		
		foreach((array)$category_branch[$category_row->term_id]['children'] as $child_category) {
			$category_branch[$category_row->term_id]['count'] += (int)$child_category['count'];
			//echo "<pre>".print_r($child_category, true)."</pre>";
		}
		
		// stick the category count array together here
		// this must run after the subcategories and the count of products belonging to them has been obtained

		$category_count = $category_branch[$category_row->term_id]['count'];
		
		
		$start_element = $query['products_count']['start_element'];
		$end_element = $query['products_count']['end_element'];
		$category_count_html =  $start_element.$category_count.$end_element;
		
		
		if($sub_categories != '') {
			$start_element = $query['subcategory_container']['start_element'];
			$end_element = $query['subcategory_container']['end_element'];
			$sub_categories = $start_element.$sub_categories.$end_element;
		}
		
		
		
		
		// get the category images
		$category_image = wpsc_place_category_image($category_row->term_id, $modified_query);

		$width = $query['image_size']['width'];
		$height = $query['image_size']['height'];
		$category_image = wpsc_get_categorymeta($category_row->term_id, 'image');
		$category_image_html = '';
		if(($query['show_thumbnails'] == 1)) {
			if(($category_image != '') && is_file(WPSC_CATEGORY_DIR.$category_image)) {
				$category_image_html = "<img src='".WPSC_CATEGORY_URL."$category_image' alt='{$category_row->name}' title='{$category_row->name}' style='width: {$width}px; height: {$height}px;' class='wpsc_category_image' />";
			} else {
				$category_image_html  = "";
				$category_image_html .= "				<span class='wpsc_category_image item_no_image ' style='width: {$width}px; height: {$height}px;'>\n\r";
				$category_image_html .= "					<span class='link_substitute' >\n\r";
				$category_image_html .= "						<span>".$category_row->name."</span>\n\r";
				$category_image_html .= "					</span>\n\r";
				$category_image_html .= "				</span>\n\r";
			}
		
		}
		
		
		// get the list of products associated with this category.
		$category_product_list = wpsc_category_product_list($category_row->term_id);
		$tags_to_replace = array('[wpsc_category_name]',
		'[wpsc_category_description]',
		'[wpsc_category_url]',
		'[wpsc_category_id]',
		'[wpsc_category_classes]',
		'[wpsc_category_image]',
		'[wpsc_subcategory]',
		'[wpsc_category_products_count]',
		'[wpsc_category_product_list]');
		
		$content_to_place = array(
		htmlentities($category_row->name,ENT_QUOTES, 'UTF-8'),
		$category_description,
		get_term_link($category_row->slug, 'wpsc_product_category'),
		$category_row->term_id,
		$category_classes,
		$category_image_html,
		$sub_categories,
		$category_count_html,
		$category_product_list);
		
		// Stick all the category html together and concatenate it to the previously generated HTML
		$output .= str_replace($tags_to_replace, $content_to_place ,$category_html);
	}
	
	if($_GET['debug'] == 'true') {
		//echo "<pre>".print_r($category_count_data,true)."</pre>";
		//phpinfo();
	}

	return $output;
}

/**
* wpsc category image function
* if no parameters are passed, the category is not resized, otherwise it is resized to the specified dimensions
* @param integer category id
* @param array category query array
* @return string - the category image URL, or the URL of the resized version
*/
function wpsc_place_category_image($category_id, $query) {
	// show the full sized image for the product, if supplied with dimensions, will resize image to those.
		global $wpsc_query, $wpdb;
		$width = $query['image_size']['width'];
		$height = $query['image_size']['height'];
		//echo "<pre>".print_r($query, true)."</pre>";
		$image_url = "index.php?wpsc_request_image=true&category_id=".$category_id."&width=".$width."&height=".$height;
		return htmlspecialchars($image_url);
}


function wpsc_category_product_list($category_id) {
	global $wpdb;
	$output = '';
	$category_id = (int)$category_id;

	if (get_option('catsprods_display_type') == 1) {
		$product_data = $wpdb->get_results("SELECT `products`.`id`, `products`.`name`
			FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `cats`
			JOIN `".WPSC_TABLE_PRODUCT_LIST."` as `products`
			ON  `cats`.`product_id` = `products`.`id`
			WHERE `cats`.`category_id` = '$category_id'
			AND `products`.`publish`='1'
			AND `products`.`active` = '1'
			ORDER BY `products`.`name` ASC
			", ARRAY_A);
		if(count($product_data) > 0){
			$output .= "<ul class='category-product-list'>\n\r";
			foreach($product_data as $product_row) {
				$output .= "<li class='cat-item'><a class='productlink' href='".wpsc_product_url($product_row['id'],$category_id)."'>".$product_row['name']."</a></li>\n\r";
			} //end foreach
			$output .= "</ul>\n\r";
		} //end if productsIDs
	}
	return $output;
}


/// category template tags end here


// pe.{
// To stick this in sidebar, main page (calling products_page.php) must be called before sidebar.php in the loop (think)
  
function display_subcategories($id) {
  global $wpdb;  
  if(get_option('permalink_structure') != '') {
    $separator ="?";
  } else {
    $separator ="&amp;";
	}   
  $subcategory_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '".absint($id)."'  ORDER BY `nice-name`";
  $subcategories = $wpdb->get_results($subcategory_sql,ARRAY_A);
  if($subcategories != null) {
    $output .= "<ul class='SubCategories'>";
    foreach($subcategories as $subcategory) {
			if (get_option('show_category_count') == 1) {
				//show product count for each category
				$count = $wpdb->get_var("SELECT COUNT(`p`.`id`) FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `a` JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `p` ON `a`.`product_id` = `p`.`id` WHERE `a`.`category_id` IN ('{$subcategory['id']}') AND `p`.`active` IN ('1') AND `p`.`publish` IN('1')");
				$addCount =  " (".$count.")";
			} //end get_option
      $output .= "<li class='cat-item'><a class='categorylink' href='".wpsc_category_url($subcategory['id'])."'>".stripslashes($subcategory['name'])."</a>$addCount".display_subcategories($subcategory['id'])."</li>";
		} 
    $output .= "</ul>";
	} else {
		return '';
	}
  return $output;
  }



/**
* wpsc_category_url  function, makes permalink to the category or 
* @param integer category ID, can be 0
* @param boolean permalink compatibility, adds a prefix to prevent permalink namespace conflicts
*/
function wpsc_category_url($category_id, $permalink_compatibility = false) {
  global $wpdb, $wp_rewrite;
  return get_term_link( $category_id, 'wpsc_product_category');
}


function wpsc_is_in_category() {
  global $wpdb, $wp_query;
  $category_id = null;
  if(isset($wp_query->query_vars['category_id'] ) && $wp_query->query_vars['category_id'] > 0) {
    $category_id = absint($wp_query->query_vars['category_id']);
  } else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
    $category_id = absint($_GET['category']);
  }
  if($category_id > 0) {
    return true;
  }
  return false;
}

/**
* wpsc_category_image function, Gets the category image or returns false
* @param integer category ID, can be 0
* @param string url to the category image
*/
function wpsc_category_image($category_id = null) {
  global $wpdb, $wp_query;
  if($category_id < 1) {
		if($wp_query->query_vars['category_id'] > 0) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
			$category_id = $_GET['category'];
		}
  }
  $category_id = absint($category_id);
  $category_image = wpsc_get_categorymeta($category_id, 'image');
  
  $category_path = WPSC_CATEGORY_DIR.basename($category_image);
  $category_url = WPSC_CATEGORY_URL.basename($category_image);
  if(file_exists($category_path) && is_file($category_path)) {
    return $category_url;
  } else {
    return false;
  }
}


/**
* wpsc_category_description function, Gets the category description
* @param integer category ID, can be 0
* @param string category description
*/
function wpsc_category_description($category_id = null) {
  global $wpdb, $wp_query;
  if($category_id < 1) {
		if($wp_query->query_vars['category_id'] > 0) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
			$category_id = $_GET['category'];
		}
  }
  $category_id = absint($category_id);
  $category_description = wpsc_get_categorymeta($category_id, 'description');
  return $category_description;
}

function wpsc_category_name($category_id = null) {
  global $wpdb, $wp_query;
  if($category_id < 1) {
		if($wp_query->query_vars['category_id'] > 0) {
			$category_id = $wp_query->query_vars['category_id'];
		} else if(isset($_GET['category']) && ($_GET['category'] > 0)) {
			$category_id = $_GET['category'];
		}
  }
  $category_id = absint($category_id);
  
  $category_data = get_term_by('id', $category_id, 'wpsc_product_category', ARRAY_A);
  
  $category_name = $category_data['name'];
  return $category_name;
}

function nzshpcrt_display_categories_groups() {
    global $wpdb;

    return $output;
  }

/** wpsc list subcategories function
		used to get an array of all the subcategories of a category.
*/
function wpsc_list_subcategories($category_id = null) {
  global $wpdb,$category_data;
  if(is_numeric($category_id)) {
    $category_list = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `category_parent` = '".$category_id."'");
	}
  if($category_list != null) {
    foreach($category_list as $subcategory_id) {
			$category_list = array_merge((array)$category_list, (array)wpsc_list_subcategories($subcategory_id));
		}
	}
	return $category_list;
}


  
?>
