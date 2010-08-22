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
* wpsc_user_enqueues products function,
* enqueue all javascript and CSS for wp ecommerce
*/
function wpsc_enqueue_user_script_and_css() {
  global $wp_styles, $wpsc_theme_url, $wpsc_theme_path, $wp_query;
	/**
	* added by xiligroup.dev to be compatible with touchshop
	*/
	if (has_filter('wpsc_enqueue_user_script_and_css') && apply_filters('wpsc_mobile_scripts_css_filters',false)){
	 		do_action('wpsc_enqueue_user_script_and_css'); 
	} else {
		/**
		* end of added by xiligroup.dev to be compatible with touchshop
		*/
		$version_identifier = WPSC_VERSION.".".WPSC_MINOR_VERSION;
		//$version_identifier = '';
		$category_id = '';
		if (   (isset($_GET['category']) && is_numeric($_GET['category']))
			|| (isset($wp_query) && isset($wp_query->query_vars['product_category']) && is_numeric($wp_query->query_vars['product_category'])) 
			|| is_numeric(get_option('wpsc_default_category'))
		) {
			if(is_numeric($wp_query->query_vars['product_category'])) {
				$category_id = $wp_query->query_vars['product_category'];
			} else if(is_numeric($_GET['category'])) {
				$category_id = $_GET['category'];
			} else {
				$category_id = get_option('wpsc_default_category');
			}
		}
		
		$siteurl = get_option('siteurl');
		if(is_ssl()) {
			$siteurl = str_replace("http://", "https://", $siteurl);
		}
		
		wp_enqueue_script( 'jQuery');
		wp_enqueue_script('wp-e-commerce', WPSC_URL.'/js/wp-e-commerce.js', array('jquery'), $version_identifier);
		wp_enqueue_script('wp-e-commerce-ajax-legacy', WPSC_URL.'/js/ajax.js', false, $version_identifier);
		wp_enqueue_script('wp-e-commerce-dynamic', $siteurl."/index.php?wpsc_user_dynamic_js=true", false, $version_identifier);
		wp_enqueue_script('livequery', WPSC_URL.'/wpsc-admin/js/jquery.livequery.js', array('jquery'), '1.0.3');
		wp_enqueue_script('jquery-rating', WPSC_URL.'/js/jquery.rating.js', array('jquery'), $version_identifier);

		
		wp_enqueue_script('wp-e-commerce-legacy', WPSC_URL.'/js/user.js', array('jquery'), WPSC_VERSION.WPSC_MINOR_VERSION);

		
		wp_enqueue_script('wpsc-thickbox',WPSC_URL.'/js/thickbox.js', array('jquery'), 'Instinct_e-commerce');

		if(file_exists($wpsc_theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css")) {
			$theme_url = $wpsc_theme_url.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css";
		} else {
			$theme_url = $wpsc_theme_url. '/default/default.css';
		}
		
		wp_enqueue_style( 'wpsc-theme-css', $theme_url, false, $version_identifier, 'all');
		wp_enqueue_style( 'wpsc-theme-css-compatibility', WPSC_URL. '/themes/compatibility.css', false, $version_identifier, 'all');
		wp_enqueue_style( 'wpsc-product-rater', WPSC_URL.'/js/product_rater.css', false, $version_identifier, 'all');
		wp_enqueue_style( 'wp-e-commerce-dynamic', $siteurl."/index.php?wpsc_user_dynamic_css=true&category=$category_id" , false, $version_identifier, 'all' );
		wp_enqueue_style( 'wpsc-thickbox', WPSC_URL.'/js/thickbox.css', false, $version_identifier, 'all');
		
		/* IE conditional css 
			wp_enqueue_style( 'wpsc-ie-fixes', WPSC_URL.'/themes/wpsc-ie-fixes.css', false, $version_identifier, 'all');
			$wp_styles->add_data( 'wpsc-ie-fixes', 'conditional', 'lt IE 7' );
		*/
	}

	
	if(!defined('WPSC_MP3_MODULE_USES_HOOKS') and function_exists('listen_button') ) {
			function wpsc_legacy_add_mp3_preview($product_id, &$product_data) {
				global $wpdb;
				if(function_exists('listen_button')){
					$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_data['file']."' LIMIT 1",ARRAY_A);
					if($file_data != null) {
						echo listen_button($file_data['idhash'], $file_data['id']);
					}
				}
			}
			add_action('wpsc_product_before_description', 'wpsc_legacy_add_mp3_preview', 10, 2);
		}
}

if(strpos($_SERVER['SCRIPT_NAME'], "wp-admin") === false) {
	add_action('init', 'wpsc_enqueue_user_script_and_css');
}

function wpsc_product_list_rss_feed() {
	$rss_url = add_query_arg('wpsc_action', 'rss');
  echo "<link rel='alternate' type='application/rss+xml' title='".get_option('blogname')." Product List RSS' href='{$rss_url}'/>";
}
add_action('wp_head', 'wpsc_product_list_rss_feed');


function wpsc_user_dynamic_js() { 
 	header('Content-Type: text/javascript');
 	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
 	header('Cache-Control: public, must-revalidate, max-age=86400');
 	header('Pragma: public');
	$siteurl = get_option('siteurl'); 
	?>
	jQuery.noConflict();
	
	/* base url */
	var base_url = "<?php echo $siteurl; ?>";
	var WPSC_URL = "<?php echo WPSC_URL; ?>";
	var WPSC_IMAGE_URL = "<?php echo WPSC_IMAGE_URL; ?>";
	var WPSC_DIR_NAME = "<?php echo WPSC_DIR_NAME; ?>";
	
	/* LightBox Configuration start*/
	var fileLoadingImage = "<?php echo WPSC_URL; ?>/images/loading.gif";
	var fileBottomNavCloseImage = "<?php echo WPSC_URL; ?>/images/closelabel.gif";
	var fileThickboxLoadingImage = "<?php echo WPSC_URL; ?>/images/loadingAnimation.gif";
	var resizeSpeed = 9;  // controls the speed of the image resizing (1=slowest and 10=fastest)
	var borderSize = 10;  //if you adjust the padding in the CSS, you will need to update this variable
	<?php
	exit();
}

if(isset($_GET['wpsc_user_dynamic_js']) && ($_GET['wpsc_user_dynamic_js'] == 'true')) {
  add_action("init", 'wpsc_user_dynamic_js');  
}



function wpsc_user_dynamic_css() {  
	global $wpdb;  
	header('Content-Type: text/css');
	header('Expires: '.gmdate('r',mktime(0,0,0,date('m'),(date('d')+12),date('Y'))).'');
	header('Cache-Control: public, must-revalidate, max-age=86400');
	header('Pragma: public'); 	
	
	$category_id = absint($_GET['category']);
	
	if(!defined('WPSC_DISABLE_IMAGE_SIZE_FIXES') || (constant('WPSC_DISABLE_IMAGE_SIZE_FIXES') != true)) {
		$thumbnail_width = get_option('product_image_width');
		if($thumbnail_width <= 0) {
			$thumbnail_width = 96;
		}
		$thumbnail_height = get_option('product_image_height'); 
		if($thumbnail_height <= 0) { 
			$thumbnail_height = 96; 
		}
		
		$single_thumbnail_width = get_option('single_view_image_width');
		$single_thumbnail_height = get_option('single_view_image_height');
		if($single_thumbnail_width <= 0) {
			$single_thumbnail_width = 128;
		}
		
		?>
		
    /*
     * Default View Styling
     */
		div.default_product_display div.textcol{
			margin-left: <?php echo $thumbnail_width + 10; ?>px !important;
			min-height: <?php echo $thumbnail_height;?>px;
			_height: <?php echo $thumbnail_height;?>px;
		}
			
		div.default_product_display  div.textcol div.imagecol{
			position:absolute;
			top:0px;
			left: 0px;
			margin-left: -<?php echo $thumbnail_width + 10; ?>px !important;
		}
		
		div.default_product_display  div.textcol div.imagecol a img {
			width: <?php echo $thumbnail_width; ?>px;
			height: <?php echo $thumbnail_height; ?>px;
		}
		
		div.default_product_display div.item_no_image  {
			width: <?php echo $thumbnail_width-2; ?>px;
			height: <?php echo $thumbnail_height-2; ?>px;
		}
		div.default_product_display div.item_no_image a  {
			width: <?php echo $thumbnail_width-2; ?>px;
		}


    /*
     * Grid View Styling
     */
		div.product_grid_display div.item_no_image  {
			width: <?php echo $thumbnail_width-2; ?>px;
			height: <?php echo $thumbnail_height-2; ?>px;
		}
		div.product_grid_display div.item_no_image a  {
			width: <?php echo $thumbnail_width-2; ?>px;
		}
		
		
		
    /*
     * Single View Styling
     */
     
		div.single_product_display div.item_no_image  {
			width: <?php echo $single_thumbnail_width-2; ?>px;
			height: <?php echo $single_thumbnail_height-2; ?>px;
		}
		div.single_product_display div.item_no_image a  {
			width: <?php echo $single_thumbnail_width-2; ?>px;
		}
		
		div.single_product_display div.textcol{
			margin-left: <?php echo $single_thumbnail_width + 10; ?>px !important;
			min-height: <?php echo $single_thumbnail_height;?>px;
			_height: <?php echo $single_thumbnail_height;?>px;
		}
			
			
		div.single_product_display  div.textcol div.imagecol{
			position:absolute;
			top:0px;
			left: 0px;
			margin-left: -<?php echo $single_thumbnail_width + 10; ?>px !important;
		}
		
		div.single_product_display  div.textcol div.imagecol a img {
			width: <?php echo $single_thumbnail_width; ?>px;
			height: <?php echo $single_thumbnail_height; ?>px;
		}
      
    <?php
		foreach((array)$product_image_size_list as $product_image_sizes) {
			$individual_thumbnail_height = $product_image_sizes['height']; 
			$individual_thumbnail_width = $product_image_sizes['width'];     
			$product_id = $product_image_sizes['id'];
			if($individual_thumbnail_height > $thumbnail_height) { 
				echo "		div.default_product_display.product_view_$product_id div.textcol{\n\r";
				echo "			min-height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
				echo "			_height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
				echo "		}\n\r";
			} 

			if($individual_thumbnail_width > $thumbnail_width) {
				echo "		div.default_product_display.product_view_$product_id div.textcol{\n\r";
				echo "			margin-left: ".($individual_thumbnail_width + 10)."px !important;\n\r";
				echo "		}\n\r";
				
				echo "		div.default_product_display.product_view_$product_id  div.textcol div.imagecol{\n\r";
				echo "			position:absolute;\n\r";
				echo "			top:0px;\n\r";
				echo "			left: 0px;\n\r";
				echo "			margin-left: -".($individual_thumbnail_width + 10)."px !important;\n\r";
				echo "		}\n\r";
			}
			
			if(($individual_thumbnail_width > $thumbnail_width) || ($individual_thumbnail_height > $thumbnail_height)) {
				echo "		div.default_product_display.product_view_$product_id  div.textcol div.imagecol a img{\n\r";
				echo "			width: ".$individual_thumbnail_width."px;\n\r";
				echo "			height: ".$individual_thumbnail_height."px;\n\r";
				echo "		}\n\r";
			}
		}
	}
	if(is_numeric($_GET['brand']) || (get_option('show_categorybrands') == 3)) {
		$brandstate = 'block';
		$categorystate = 'none';
	} else {
		$brandstate = 'none';
		$categorystate = 'block';
	}
      
    ?>
		div#categorydisplay{
			display: <?php echo $categorystate; ?>;
		}
    
		div#branddisplay{
			display: <?php echo $brandstate; ?>;
		}
    <?php
	exit();
}

if(isset($_GET['wpsc_user_dynamic_css']) && ($_GET['wpsc_user_dynamic_css'] == 'true')) {
  add_action("init", 'wpsc_user_dynamic_css');  
}

// Template tags
/**
* wpsc display products function
* @return string - html displaying one or more products
*/
function wpsc_display_products_page($query) {
	global $wpdb, $wpsc_query, $wpsc_theme_path;
	static $count = 0;
	$count++;
	if($count > 10) {
		exit('fail');
	}
	/// added by xiligroup.dev to be compatible with touchshop
	//	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
		$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path);
	/// end of added by xiligroup.dev to be compatible with touchshop
  
	//$temp_wpsc_query = new wpsc_query($query);
	list($wpsc_query, $temp_wpsc_query) = array($temp_wpsc_query, $wpsc_query); // swap the wpsc_query objects
	
	$GLOBALS['nzshpcrt_activateshpcrt'] = true;
	ob_start();
	//Pretty sure this single_product code is legacy...but fixing it up just in case.
	if(wpsc_is_single_product()) {
		include($cur_wpsc_theme_folder."/wpsc-single_product.php");
		$is_single = true;
	} else {
		// get the display type for the selected category
		if(is_numeric($wpsc_query->query_vars['category_id'])) {
			$category_id =(int) $wpsc_query->query_vars['category_id'];
			$display_type = wpsc_get_categorymeta($category_id, 'display_type');
		}
		
		if($display_type == '') {
			$display_type = get_option('product_view');
		}

		if(isset($_SESSION['wpsc_display_type'])) {
		  $display_type = $_SESSION['wpsc_display_type'];
		}

		if(isset($_GET['view_type'])) {
			switch($_GET['view_type']) {
				case 'grid':
				$display_type = 'grid';
				$_SESSION['wpsc_display_type'] = $display_type;
				break;
				
				case 'list':
				$display_type = 'list';
				$_SESSION['wpsc_display_type'] = $display_type;
				break;
				
				case 'default':
				$display_type = 'default';
				$_SESSION['wpsc_display_type'] = $display_type;
				break;

			  default:
			  break;
			}
		}

		
		//exit($display_type);
		// switch the display type, based on the display type variable...
		switch($display_type) {
			case "grid":
			if(file_exists($cur_wpsc_theme_folder."/wpsc-grid_view.php")) {
				include($cur_wpsc_theme_folder."/wpsc-grid_view.php");
				break; // only break if we have the file;
			}
			
			case "list":
			if(file_exists($cur_wpsc_theme_folder."/wpsc-list_view.php")) {
				include($cur_wpsc_theme_folder."/wpsc-list_view.php");
				break; // only break if we have the file;
			}
			
			case "default":  // this may be redundant :D
			default:
				include($cur_wpsc_theme_folder."/wpsc-products_page.php");
			break;
		}
			$is_single = false;
	}
	$output = ob_get_contents();
	ob_end_clean();
	//$output = str_replace('$','\$', $output);

	list($temp_wpsc_query, $wpsc_query) = array($wpsc_query, $temp_wpsc_query); // swap the wpsc_query objects back
	if($is_single == false) {
		$GLOBALS['post'] = $wp_query->post;
	}
	return $output;
}

//Better way (in my opinion) to handle single product templating, outside of the child theme system

function wpsc_single_template() {
     global $post, $wpsc_theme_path;

     if ($post->post_type == 'wpsc-product') {
          return $wpsc_theme_path.'wpsc-single_product.php';
     }
}

add_filter( "single_template", "wpsc_single_template" ) ;

//handles replacing the tags in the pages
  
function wpsc_products_page($content = '') {
	global $wpdb, $wp_query, $wpsc_query, $wpsc_theme_path, $wpsc_query_vars;

//	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path);
	remove_filter('the_content', 'wpsc_products_page');
	$output = '';
	if(preg_match("/\[productspage\]/",$content)) {	
		list($wp_query, $wpsc_query) = array($wpsc_query, $wp_query); // swap the wpsc_query object		
		$GLOBALS['nzshpcrt_activateshpcrt'] = true;
		ob_start();
		if(count($wp_query->posts) == 1) {			
			include($cur_wpsc_theme_folder."/wpsc-single_product.php");
			$is_single = true;
		} else {
		$category_id = '';
		  // get the display type for the selected category
			if ( (isset($_GET['category']) || isset($wp_query->query_vars['category_id']) )  && ( is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['category_id']) || is_numeric(get_option('wpsc_default_category')) ) ) {
				if(is_numeric($wp_query->query_vars['category_id'])) {
					$category_id = (int) $wp_query->query_vars['category_id'];
				} else if(is_numeric($_GET['category'])) {
					$category_id = (int)$_GET['category'];
				} else { 
					$category_id = (int)get_option('wpsc_default_category');
				}
			}

			$display_type = wpsc_get_categorymeta($category_id, 'display_type');
		
			if($display_type == '') {
				$display_type = get_option('product_view');
			}
	
			if(isset($_SESSION['wpsc_display_type'])) {
				$display_type = $_SESSION['wpsc_display_type'];
			}
	
			if(isset($_GET['view_type'])) {
				switch($_GET['view_type']) {
					case 'grid':
						$display_type = 'grid';
						$_SESSION['wpsc_display_type'] = $display_type;
					break;
					
					case 'list':
						$display_type = 'list';
						$_SESSION['wpsc_display_type'] = $display_type;
					break;
					
					case 'default':
						$display_type = 'default';
						$_SESSION['wpsc_display_type'] = $display_type;
					break;
	
					default:
					break;
				}
			}
	
			// switch the display type, based on the display type variable...
			switch($display_type) {
				case "grid":
				if(file_exists($cur_wpsc_theme_folder."/wpsc-grid_view.php")) {
					include($cur_wpsc_theme_folder."/wpsc-grid_view.php");
					break; // only break if we have the function;
				}
				
				case "list":
				if(file_exists($cur_wpsc_theme_folder."/wpsc-list_view.php")) {
					include($cur_wpsc_theme_folder."/wpsc-list_view.php");
					break; // only break if we have the file;
				}
				
				case "default":  // this may be redundant :D
				default:
					include($cur_wpsc_theme_folder."/wpsc-products_page.php");
				break;
			}
			$is_single = false;
		}
		$output .= ob_get_contents();
		ob_end_clean();
		$output = str_replace('$','\$', $output);
		
		$product_id = $wp_query->post->ID;

		list($wp_query, $wpsc_query) = array($wpsc_query, $wp_query); // swap the wpsc_query objects back

		
		$product_meta = get_post_meta($product_id, '_wpsc_product_metadata', true);
		
		if(($is_single == false) || ($product_meta['enable_comments'] == '0')) {
			$GLOBALS['post'] = $wp_query->post;
		}
		//echo "<pre>".print_r($product_meta['enable_comments'], true)."</pre>";
		
	    return preg_replace("/(<p>)*\[productspage\](<\/p>)*/",$output, $content);
		
	} else {
	    return $content;
	}
}

/**
 * wpsc_count_themes_in_uploads_directory, does exactly what the name says
*/
function wpsc_count_themes_in_uploads_directory() {
if(is_dir(WPSC_THEMES_PATH))  $uploads_dir = @opendir(WPSC_THEMES_PATH); // might cause problems if dir doesnt exist
  if (!$uploads_dir) {
	  return FALSE;
  }
  $file_names = array();
  while(($file = @readdir($uploads_dir)) !== false) {
    if(is_dir(WPSC_THEMES_PATH.$file) && ($file != "..") && ($file != ".") && ($file != ".svn")){
			$file_names[] = $file;
    }
  }
  @closedir($uploads_dir);
  return count($file_names);
}

function wpsc_place_shopping_cart($content = '') {
  global $wpsc_theme_path;
	/// added by xiligroup.dev to be compatible with touchshop
//	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path.WPSC_THEME_DIR);
	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path);
	/// end of added by xiligroup.dev to be compatible with touchshop
	
  if(preg_match("/\[shoppingcart\]/",$content)) {
		$GLOBALS['nzshpcrt_activateshpcrt'] = true;
		define('DONOTCACHEPAGE', true);
		ob_start();
		include($cur_wpsc_theme_folder."/wpsc-shopping_cart_page.php");
		$output = ob_get_contents();
		ob_end_clean();
		$output = str_replace('$','\$', $output);
    return preg_replace("/(<p>)*\[shoppingcart\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
 
}
  
/// wpsc_checkout is an obsolete function
// function wpsc_checkout($content = '') {
//   if(preg_match("/\[checkout\]/",$content)) {
//     ob_start();
//     include_once(WPSC_FILE_PATH . "/checkout.php");
//     $output = ob_get_contents();
//     ob_end_clean();
//     return preg_replace("/(<p>)*\[checkout\](<\/p>)*/",$output, $content);
// 	} else {
//     return $content;
// 	}
// }

function wpsc_transaction_results($content = '') {
  global $wpsc_theme_path;

	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path);
  if(preg_match("/\[transactionresults\]/",$content)) {
		define('DONOTCACHEPAGE', true);
    ob_start();
	include($cur_wpsc_theme_folder."/wpsc-transaction_results.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/(<p>)*\[transactionresults\](<\/p>)*/",$output, $content);
	} else { 
    return $content;
	}
}
  
function wpsc_user_log($content = '') {
  global $wpsc_theme_path;

	$cur_wpsc_theme_folder = apply_filters('wpsc_theme_folder',$wpsc_theme_path);
  if(preg_match("/\[userlog\]/",$content)) {
		define('DONOTCACHEPAGE', true);
    ob_start();
	
	include($cur_wpsc_theme_folder."/wpsc-user-log.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/(<p>)*\[userlog\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}
  
  
//displays a list of categories when the code [showcategories] is present in a post or page.
function wpsc_show_categories($content = '') {
  if(preg_match("/\[showcategories\]/",$content)) {
    $GLOBALS['nzshpcrt_activateshpcrt'] = true;
    $output = nzshpcrt_display_categories_groups();
    return preg_replace("/(<p>)*\[showcategories\](<\/p>)*/",$output, $content);
	} else {
    return $content;
	}
}

// substitutes in the buy now buttons where the shortcode is in a post.
function wpsc_substitute_buy_now_button($content = '') {
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


function wpsc_enable_page_filters($excerpt = ''){
	global $wp_query;
	add_filter('the_content', 'add_to_cart_shortcode', 12);//Used for add_to_cart_button shortcode
	add_filter('the_content', 'wpsc_products_page', 12);
	add_filter('the_content', 'wpsc_place_shopping_cart', 12);
	add_filter('the_content', 'wpsc_transaction_results', 12);
	add_filter('the_content', 'nszhpcrt_homepage_products', 12);
	add_filter('the_content', 'wpsc_user_log', 12);
	add_filter('the_content', 'nszhpcrt_category_tag', 12);
	add_filter('the_content', 'wpsc_show_categories', 12);
	add_filter('the_content', 'wpsc_substitute_buy_now_button', 12);
	return $excerpt;
}

function wpsc_disable_page_filters($excerpt = '') {
	remove_filter('the_content', 'add_to_cart_shortcode');//Used for add_to_cart_button shortcode
	remove_filter('the_content', 'wpsc_products_page');
	remove_filter('the_content', 'wpsc_place_shopping_cart');
	remove_filter('the_content', 'wpsc_transaction_results');
	remove_filter('the_content', 'nszhpcrt_homepage_products');
	remove_filter('the_content', 'wpsc_user_log');
	remove_filter('the_content', 'wpsc_category_tag');
	remove_filter('the_content', 'wpsc_show_categories');
	remove_filter('the_content', 'wpsc_substitute_buy_now_button');
	return $excerpt;
}

wpsc_enable_page_filters();

//add_filter('get_the_excerpt', 'wpsc_disable_page_filters', -1000000);
//add_filter('get_the_excerpt', 'wpsc_enable_page_filters', 1000000);



/**
 * Body Class Filter
 * @modified:     2009-10-14 by Ben
 * @description:  Adds additional wpsc classes to the body tag.
 * @param:        $classes = Array of body classes
 * @return:       (Array) of classes
 */

function wpsc_body_class( $classes ) {
	global $wp_query, $wpsc_query;
	$post_id = 0;
	if(isset($wp_query->post->ID))
		$post_id = $wp_query->post->ID;
	$page_url = get_permalink($post_id);
	
	// If on a product or category page...
	if ( get_option('product_list_url') == $page_url ) {
	
		$classes[] = 'wpsc';
	
		if ( !is_array($wpsc_query->query) ) {
			$classes[] = 'wpsc-home';
		}
	 
		if ( wpsc_is_single_product() ) {
			$classes[] = 'wpsc-single-product';
			if ( absint($wpsc_query->products[0]['id']) > 0) {
				$classes[] = 'wpsc-single-product-' . $wpsc_query->products[0]['id'];
			}
		}
		
		if ( wpsc_is_in_category() && !wpsc_is_single_product() ) {
			$classes[] = 'wpsc-category';
		}
		
		if ( isset($wpsc_query->query_vars['category_id']) && absint($wpsc_query->query_vars['category_id']) > 0) {
			$classes[] = 'wpsc-category-' . $wpsc_query->query_vars['category_id'];
		}
		
	}
	
	// If viewing the shopping cart...
	if ( get_option('shopping_cart_url') == $page_url ) {
		$classes[] = 'wpsc';
		$classes[] = 'wpsc-shopping-cart';
	}
	
	// If viewing the transaction...
	if ( get_option('transact_url') == $page_url ) {
		$classes[] = 'wpsc';
		$classes[] = 'wpsc-transaction-details';
	}
	
	// If viewing your account...
	if ( get_option('user_account_url') == $page_url ) {
		$classes[] = 'wpsc';
		$classes[] = 'wpsc-user-account';
	}
	
	return $classes;
	
}

add_filter( 'body_class', 'wpsc_body_class' );

/**
 * Featured Product
 *
 * Refactoring Featured Product Plugin to utilize Sticky Post Status, available since WP 2.7
 * also utilizes Featured Image functionality, available as post_thumbnail since 2.9, Featured Image since 3.0
 * Main differences - Removed 3.8 conditions, removed meta box from admin, changed meta_values
 * Removes shortcode, as it automatically ties in to top_of_page hook if sticky AND featured product exists.
 *
 * @package wp-e-commerce
 * @since 3.8
 */ 

function wpsc_the_sticky_image($product_id) {
global $wpdb;
	if(has_post_thumbnail($product_id)) {
    	add_image_size( 'featured-product-thumbnails',540,260, TRUE);
		$image = get_the_post_thumbnail($product_id, 'featured-product-thumbnails');
		return $image;
	} else {
		return false;
	}
//Previously checked product_meta, now get_vars guid from attachment with this post_parent, checking against _thumbnail_id   
$sticky_product_image = $wpdb->get_var($wpdb->prepare("SELECT guid FROM ".WP_POSTS," p, ".WP_POSTMETA." pm WHERE p.post_parent = $product_id AND pm.post_id = $product_id AND pm.meta_value = p.ID"));

	if($sticky_product_image != ''){
		return $sticky_product_image;
	}else{
		return wpsc_the_product_image(340, 260);
	}
}

/**
 * wpsc_display_products_page function.
 * 
 * @access public
 * @param mixed $query
 * @return void
 */
function wpsc_display_featured_products_page() {
	global $wpdb, $wpsc_query;	
	
	$sticky_array =get_option('sticky_products');
	if ( (is_front_page() || is_home()) && !empty($sticky_array) ) {
	
 	$query = get_posts(array(
			'post__in'  => $sticky_array,
			'post_type' => 'wpsc-product',
			'orderby' => 'rand',
			'meta_key' => '_thumbnail_id',
			'numberposts' => 1
		));
		if ( count($query) > 0 ) { 

			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
			$image_width = get_option('product_image_width');
			$image_height = get_option('product_image_height');
			//Begin outputting featured product.  We can worry about templating later, or folks can just CSS it up.
			 foreach($query as $product) :
			setup_postdata($product);
?>

<div class="wpsc_container wpsc_featured">
		<div class="featured_product_display">
			<div class="featured_product_display_item product_view_<?php the_ID(); ?>">
				<div class="item_text">
						<h3>
							<a href='<?php echo get_permalink($product->ID); ?>'><?php echo get_the_title($product->ID); ?></a>
						</h3> 
						<div class="pricedisplay"><?php echo wpsc_the_product_price(); ?></div> 
						<div class='wpsc_description'>
							<?php the_excerpt(); ?>
							<a href='<?php echo get_permalink($product->ID); ?>'>
							  More Information&hellip;
							</a>
						</div>
				</div>
			
				<?php if(wpsc_the_product_thumbnail()) :?> 	   
					<div class="featured_item_image">
						<a href="<?php echo get_permalink($product->ID); ?>" title="<?php echo get_the_title($product->ID); ?>">
						<?php echo wpsc_the_sticky_image(wpsc_the_product_id()); ?>
						</a>
					</div>
				<?php else: ?> 
					<div class="item_no_image">
						<a href="<?php echo get_the_title($product->ID); ?>">
						<span>No Image Available</span>
						</a>
					</div>
				<?php endif; ?>
				<div class="wpsc_clear"></div>
			</div>
	</div>
</div>
<?php
		endforeach;
		//End output	
		}
	}
}

add_action('wpsc_top_of_products_page', 'wpsc_display_featured_products_page', 12);

?>
