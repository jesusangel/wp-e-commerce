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
 * wpsc_register_theme_file( $file_name )
 *
 * Adds a file name to a global list of
 *
 * @param string $file_name Name of file to add to global list of files
 */
function wpsc_register_theme_file( $file_name ) {
	global $wpec_theme_files;

	if ( !in_array( $file_name, (array)$wpec_theme_files ) )
		$wpec_theme_files[] = $file_name;
}

/**
 * wpsc_get_theme_files()
 *
 * Returns the global wpec_theme_files
 *
 * @global array $wpec_theme_files
 * @return array
 */
function wpsc_get_theme_files() {
	global $wpec_theme_files;
	if ( empty( $wpec_theme_files ) )
		return array();
	else
		return apply_filters( 'wpsc_get_theme_files', (array)array_values( $wpec_theme_files ) );
}

/**
 * wpsc_register_core_theme_files()
 *
 * Registers the core WPEC files into the global array
 */
function wpsc_register_core_theme_files() {
	wpsc_register_theme_file( 'wpsc-single_product.php' );
	wpsc_register_theme_file( 'wpsc-grid_view.php' );
	wpsc_register_theme_file( 'wpsc-list_view.php' );
	wpsc_register_theme_file( 'wpsc-products_page.php' );
	wpsc_register_theme_file( 'wpsc-shopping_cart_page.php' );
	wpsc_register_theme_file( 'wpsc-transaction_results.php' );
	wpsc_register_theme_file( 'wpsc-user_log.php' );
	wpsc_register_theme_file( 'wpsc-cart_widget.php' );

	// Let other plugins register their theme files
	do_action( 'wpsc_register_core_theme_files' );
}
add_action( 'init', 'wpsc_register_core_theme_files' );

/**
 * wpsc_flush_theme_transients()
 *
 * This function will delete the temporary values stored in WordPress transients
 * for all of the additional WPEC theme files and their locations. This is
 * mostly used when the active theme changes, or when files are moved around. It
 * does a complete flush of all possible path/url combinations of files.
 *
 * @uses wpsc_get_theme_files
 */
function wpsc_flush_theme_transients( $force = false ) {

	if ( true === $force || isset( $_REQUEST['wpsc_flush_theme_transients'] ) && !empty( $_REQUEST['wpsc_flush_theme_transients'] ) ) {

		// Loop through current theme files and remove transients
		if ( $theme_files = wpsc_get_theme_files() ) {
			foreach( $theme_files as $file ) {
				delete_transient( WPEC_TRANSIENT_THEME_PATH_PREFIX . $file );
				delete_transient( WPEC_TRANSIENT_THEME_URL_PREFIX . $file );
			}

			delete_transient( 'wpsc_theme_path' );

			return true;
		}
	}

	// No files were registered so return false
	return false;
}
add_action( 'wpsc_move_theme', 'wpsc_flush_theme_transients', 10, true );
add_action( 'wpsc_switch_theme', 'wpsc_flush_theme_transients', 10, true );
add_action( 'switch_theme', 'wpsc_flush_theme_transients', 10, true );

/**
 * wpsc_check_theme_location()
 * 
 * Check theme location, compares the active theme and the themes within WPSC_CORE_THEME_PATH
 * finds files of the same name.
 *
 * @access public
 * @since 3.8
 * @param null
 * @return $results (Array) of Files OR false if no similar files are found
 */
function wpsc_check_theme_location() {
	// Get the current theme
	$current_theme       = get_stylesheet_directory();

	// Load up the files in the current theme
	$current_theme_files = wpsc_list_product_templates( $current_theme . '/' );

	// Load up the files in the wpec themes folder
	$wpsc_template_files = wpsc_list_product_templates( WPSC_CORE_THEME_PATH );

	// Compare the two
	$results             = array_intersect( $current_theme_files, $wpsc_template_files );

	// Return the differences
	if ( count( $results ) > 0 )
		return $results;

	// No differences so return false
	else
		return false;
}

/**
 * wpsc_list_product_templates( $path = '' )
 *
 * Lists the files within the WPSC_CORE_THEME_PATH directory
 * 
 * @access public
 * @since 3.8
 * @param $path - you can provide a path to find the files within it
 * @return $templates (Array) List of files
 */
function wpsc_list_product_templates( $path = '' ) {

	$selected_theme = get_option( 'wpsc_selected_theme' );

	// If no path, then try to make some assuptions
	if ( empty( $path ) ) {
		if ( file_exists( WPSC_OLD_THEMES_PATH . $selected_theme . '/' . $selected_theme . '.css' ) ) {
			$path = WPSC_OLD_THEMES_PATH . $selected_theme . '/';
		} else {
			$path = WPSC_CORE_THEME_PATH;
		}
	}

	// Open the path and get the file names
	$dh = opendir( $path );
	while ( ( $file = readdir( $dh ) ) !== false ) {
		if ( $file != "." && $file != ".." && !strstr( $file, ".svn" ) && !strstr( $file, "images" ) && is_file( $path . $file ) ) {
			$templates[] = $file;
		}
	}

	// Return template names
	return $templates;
}

/**
 * Displays the theme upgrade notice
 * @access public
 *
 * @since 3.8
 * @param null
 * @return null
 */
function wpsc_theme_upgrade_notice() { ?>

	<div id="message" class="updated fade">
		<p><?php printf( __( '<strong>WP e-Commerce is ready</strong>. If you plan on editing the look of your site, you should <a href="%1s">update your active theme</a> to include the additional WP e-Commerce files. <a href="%2s">Click here</a> to ignore and remove this box.', 'wpsc' ), admin_url( 'admin.php?page=wpsc-settings&tab=presentation' ), admin_url( 'admin.php?page=wpsc-settings&tab=presentation&wpsc_notices=theme_ignore' ) ) ?></p>
	</div>

<?php
}
if ( !get_option('wpsc_ignore_theme') )
	add_action( 'admin_notices', 'wpsc_theme_upgrade_notice' );

if ( isset( $_REQUEST['wpsc_notices'] ) && $_REQUEST['wpsc_notices'] == 'theme_ignore' ) {
	update_option( 'wpsc_ignore_theme', true );
	wp_redirect( remove_query_arg( 'wpsc_notices' ) );
}

/**
 * wpsc_get_template_file_url( $file )
 *
 * Checks the active theme folder for the particular file, if it exists then
 * return the active theme url, otherwise return the global wpsc_theme_url
 *
 * @access public
 * @since 3.8
 * @param $file string filename
 * @return PATH to the file
 */
function wpsc_get_template_file_url( $file = '' ) {

	// If we're not looking for a file, do not proceed
	if ( empty( $file ) )
		return;

	// No cache, so find one and set it
	if ( false === ( $file_url = get_transient( WPEC_TRANSIENT_THEME_URL_PREFIX . $file ) ) ) {

		// Look for file in stylesheet
		if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
			$file_url = get_stylesheet_directory_uri() . '/' . $file;

		// Look for file in template
		} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
			$file_url = get_template_directory_uri() . '/' . $file;

		// Backwards compatibility
		} else {
			// Look in old theme url
			$selected_theme_check = WPSC_OLD_THEMES_PATH . get_option( 'wpsc_selected_theme' ) . '/' . str_ireplace( 'wpsc-', '', $file );
			// Check the selected theme
			if ( file_exists( $selected_theme_check ) ) {
				
				$file_url = WPSC_OLD_THEMES_URL . get_option( 'wpsc_selected_theme' ) . '/' . str_ireplace( 'wpsc-', '', $file );
			// Use the bundled theme CSS
			} else {
				$file_url = WPSC_CORE_THEME_URL . $file;
			}
		}

		// Save the transient and update it every 12 hours
		if ( !empty( $file_url ) )
			set_transient( WPEC_TRANSIENT_THEME_URL_PREFIX . $file, $file_url, 60 * 60 * 12 );
	}elseif(!file_exists($file_path)){
		delete_transient(WPEC_TRANSIENT_THEME_URL_PREFIX . $file);
		wpsc_get_template_file_url($file);
	}

	// Return filtered result
	return apply_filters( WPEC_TRANSIENT_THEME_URL_PREFIX . $file, $file_url );
}

/**
 * Checks the active theme folder for the particular file, if it exists then return the active theme directory otherwise
 * return the global wpsc_theme_path
 * @access public
 *
 * @since 3.8
 * @param $file string filename
 * @return PATH to the file
 */
function wpsc_get_template_file_path( $file = '' ){

	// If we're not looking for a file, do not proceed
	if ( empty( $file ) )
		return;

	// No cache, so find one and set it
	if ( false === ( $file_path = get_transient( WPEC_TRANSIENT_THEME_PATH_PREFIX . $file ) ) ) {
		// Look for file in stylesheet
		if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
			$file_path = get_stylesheet_directory() . '/' . $file;

		// Look for file in template
		} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
			$file_path = get_template_directory() . '/' . $file;

		// Backwards compatibility
		} else {
			// Look in old theme path
			$selected_theme_check = WPSC_OLD_THEMES_PATH . get_option( 'wpsc_selected_theme' ) . '/' . str_ireplace( 'wpsc-', '', $file );

			// Check the selected theme
			if ( file_exists( $selected_theme_check ) ) {
				$file_path = $selected_theme_check;

			// Use the bundled file
			} else {
				$file_path = WPSC_CORE_THEME_PATH . '/' . $file;
			}
		}
		// Save the transient and update it every 12 hours
		if ( !empty( $file_path ) )
			set_transient( WPEC_TRANSIENT_THEME_PATH_PREFIX . $file, $file_path, 60 * 60 * 12 );
		
	}elseif(!file_exists($file_path)){
		delete_transient(WPEC_TRANSIENT_THEME_PATH_PREFIX . $file);
		wpsc_get_template_file_path($file);
	}

	// Return filtered result
	return apply_filters( WPEC_TRANSIENT_THEME_PATH_PREFIX . $file, $file_path );
}

/**
 * Get the Product Category ID by either slug or name
 * @access public
 *
 * @since 3.8
 * @param $slug (string) to be searched
 * @param $type (string) column to search, i.e name or slug
 * @return $category_id (int) Category ID
 */
function wpsc_get_the_category_id($slug, $type = 'name'){
	global $wpdb,$wp_query;
	if(isset($wp_query->query_vars['taxonomy']))
		$taxonomy = $wp_query->query_vars['taxonomy'];
	else
		$taxonomy = 'wpsc_product_category';
		
	$sql = 'SELECT '.$wpdb->terms.'.`term_id` FROM '.$wpdb->terms.' LEFT JOIN '.$wpdb->term_taxonomy.' ON '.$wpdb->terms.'.term_id = '.$wpdb->term_taxonomy.'.term_taxonomy_id WHERE '.$wpdb->term_taxonomy.'.`taxonomy` = "'.$taxonomy.'" AND '.$wpdb->terms.'.'.$type.' LIKE "%'.$slug.'%"';

	$category_id = $wpdb->get_var($sql);
	return $category_id;
}

/**
 * Checks the category slug for a display type, if none set returns default
 * << May need reworking to be more specific to the taxonomy type >>
 * @access public
 *
 * @since 3.8
 * @param $slug(string)
 * @return $slug either from db or 'default' if none set
 */

function wpsc_get_the_category_display($slug){
	global $wpdb;
	if ( !empty($slug) && is_string($slug) ) {
		$category_id = wpsc_get_the_category_id($slug , 'slug');
		$display_type = wpsc_get_categorymeta( $category_id, 'display_type' );
	}

	if(!empty($display_type))
		return $display_type;		
	else
		return 'default';
}

/**
 * Checks if wpsc-single_product.php has been moved to the active theme, if it has then include the 
 * template from the active theme.
 * @access public
 *
 * @since 3.8
 * @param $content content of the page
 * @return $content with wpsc-single_product content if its a single product
 */
function wpsc_single_template( $content ) {

	global $wpdb, $post, $wp_query, $wpsc_query;
	
	$single_theme_path = wpsc_get_template_file_path( 'wpsc-single_product.php' );	
	//echo 'Single <pre>'.print_r($wp_query,true).'</pre>';
	if ( 'wpsc-product' == $post->post_type && !is_archive() && $wp_query->post_count <= 1) {

		remove_filter( "the_content", "wpsc_single_template" );
		$wpsc_temp_query = new WP_Query( array( 'post__in' => array( $post->ID ), 'post_type' => 'wpsc-product','posts_per_page'=>1 ) );
		list( $wp_query, $wpsc_temp_query ) = array( $wpsc_temp_query, $wp_query ); // swap the wpsc_query object
		ob_start();
		include( $single_theme_path );
		$content = ob_get_contents();
		ob_end_clean();
		list( $wp_query, $wpsc_temp_query ) = array( $wpsc_temp_query, $wp_query ); // swap the wpsc_query objects back
	}elseif(is_archive() && wpsc_is_viewable_taxonomy() || ($wp_query->post_count > 1 && 1 == $wp_query->is_product)){
//		echo 'Archive <pre>'.print_r($wp_query,true).'</pre>';
		remove_filter( "the_content", "wpsc_single_template" );		
		list( $wp_query, $wpsc_query ) = array( $wpsc_query, $wp_query ); // swap the wpsc_query object
		if(isset($wp_query->query['pagename']))
			$display_type = get_option( 'product_view' );
		else			
			$display_type = wpsc_get_the_category_display($wp_query->query_vars['term'],'slug');

		if ( isset( $_SESSION['wpsc_display_type'] ) )
			$display_type = $_SESSION['wpsc_display_type'];
			

		ob_start();
		wpsc_include_products_page_template($display_type);
		$content = ob_get_contents();
		ob_end_clean();
		list( $wp_query, $wpsc_query ) = array( $wpsc_query, $wp_query ); // swap the wpsc_query object
		$wp_query->current_post = $wp_query->post_count;
	}

	return $content;
}



function wpsc_is_viewable_taxonomy(){
	global $wp_query;
	if('wpsc_product_category' == $wp_query->query_vars['taxonomy'] ||  'product_tag' == $wp_query->query_vars['taxonomy'] )
		return true;
	else
		return false;
}
/**
 * Checks and replaces the Page title with the category title if on a category page
 * @access public
 *
 * @since 3.8
 * @param $title (string) The Page Title
 * @param $id (int) The Page ID
 * @return $title (string) the new title
 */
function wpsc_the_category_title($title, $id){
	global $wp_query;
	$post = get_post($id);
		remove_filter('the_title','wpsc_the_category_title');
	if(isset($wp_query->query_vars['post_type']) && 'wpsc-product' == $wp_query->query_vars['post_type'] && isset($wp_query->query_vars['paged']) && $wp_query->current_post == 0 &&  $wp_query->posts[0]->post_title == $post->post_title && count($wp_query->posts) >1 ){
		remove_filter('the_title','wpsc_the_category_title');
		$id = wpec_get_the_post_id_by_shortcode('[productspage]');
		$post = get_post($id);
		return $post->post_title;
	}
	if('wpsc-product' == $post->post_type && $wp_query->posts[0]->post_title == $post->post_title && isset($wp_query->query_vars['term']) && isset($wp_query->query_vars['taxonomy']) && !($wp_query->is_admin)){
		remove_filter('the_title','wpsc_the_category_title');
		$category_id = wpsc_get_the_category_id($wp_query->query_vars['term'],'slug');
		$category = get_term($category_id, $wp_query->query_vars['taxonomy']);
	}
	if(!empty($category->name))	
		return $category->name;
	else
		return $title;
}

/**
 * wpsc_the_category_template swaps the template used for product categories with pageif archive template is being used use
 * @access public
 *
 * @since 3.8
 * @param $template (string) template path
 * @return $template (string)
 */
function wpsc_the_category_template($template){
	if(wpsc_is_viewable_taxonomy() && false !== strpos($template,'archive')){
		return str_ireplace('archive', 'page',$template);
	}else{
		return $template;
	}	
}

/**
 * wpsc_form_action
 *
 * Echo the form action for use in the template files
 *
 * @global <type> $wpec_form_action
 * @return <type>
 */
function wpsc_form_action() {
	echo wpsc_get_form_action();
}
	/**
	 * wpsc_get_form_action
	 *
	 * Return the form action for use in the template files
	 *
	 * @global <type> $wpec_form_action
	 * @return <type>
	 */
	function wpsc_get_form_action() {
		global $wpec_form_action;

		$product_id = wpsc_the_product_id();

		// Function has already ran in this page load
		if ( isset( $wpec_form_action ) ) {
			$action =  $wpec_form_action;

		// No global so figure it out
		} else {

			// Use external if set
			if ( wpsc_is_product_external() ) {
				$action = wpsc_product_external_link( $product_id );

			// Otherwise use this page
			} else {
				$action = wpsc_this_page_url();
			}
		}

		// Return form action
		return $action;
	}

/**
 * wpsc_user_enqueues products function,
 * enqueue all javascript and CSS for wp ecommerce
 */
function wpsc_enqueue_user_script_and_css() {
	global $wp_styles, $wpsc_theme_url, $wp_query;
	/**
	 * added by xiligroup.dev to be compatible with touchshop
	 */
	if ( has_filter( 'wpsc_enqueue_user_script_and_css' ) && apply_filters( 'wpsc_mobile_scripts_css_filters', false ) ) {
		do_action( 'wpsc_enqueue_user_script_and_css' );
	} else {
		/**
		 * end of added by xiligroup.dev to be compatible with touchshop
		 */
		$version_identifier = WPSC_VERSION . "." . WPSC_MINOR_VERSION;
		//$version_identifier = '';
		$category_id = '';
		if (isset( $wp_query ) && isset( $wp_query->query_vars['taxonomy'] ) && ('wpsc_product_category' ==  $wp_query->query_vars['taxonomy'] ) || is_numeric( get_option( 'wpsc_default_category' ) )
		) {
			if ( is_string( $wp_query->query_vars['term'] ) ) {
				$category_id = wpsc_get_category_id($wp_query->query_vars['term'], 'slug');
			} else {
				$category_id = get_option( 'wpsc_default_category' );
			}
		}

		$siteurl = get_option( 'siteurl' );

		if ( is_ssl() )
			$siteurl = str_replace( "http://", "https://", $siteurl );

		wp_enqueue_script( 'jQuery' );
		wp_enqueue_script( 'wp-e-commerce',               WPSC_CORE_JS_URL	. '/wp-e-commerce.js',                 array( 'jquery' ), $version_identifier );
		wp_enqueue_script( 'infieldlabel',               WPSC_CORE_JS_URL	. '/jquery.infieldlabel.min.js',                 array( 'jquery' ), $version_identifier );
		wp_enqueue_script( 'wp-e-commerce-ajax-legacy',   WPSC_CORE_JS_URL	. '/ajax.js',                          false,             $version_identifier );
		wp_enqueue_script( 'wp-e-commerce-dynamic',       $siteurl			. "/index.php?wpsc_user_dynamic_js=true", false,             $version_identifier );
		wp_enqueue_script( 'livequery',                   WPSC_URL 			. '/wpsc-admin/js/jquery.livequery.js',   array( 'jquery' ), '1.0.3' );
		wp_enqueue_script( 'jquery-rating',               WPSC_CORE_JS_URL 	. '/jquery.rating.js',                 array( 'jquery' ), $version_identifier );
		wp_enqueue_script( 'wp-e-commerce-legacy',        WPSC_CORE_JS_URL 	. '/user.js',                          array( 'jquery' ), WPSC_VERSION . WPSC_MINOR_VERSION );
		wp_enqueue_script( 'wpsc-thickbox',               WPSC_CORE_JS_URL 	. '/thickbox.js',                      array( 'jquery' ), 'Instinct_e-commerce' );
		
		wp_enqueue_style( 'wpsc-theme-css',               wpsc_get_template_file_url( 'wpsc-' . get_option( 'wpsc_selected_theme' ) . '.css' ), false, $version_identifier, 'all' );
		wp_enqueue_style( 'wpsc-theme-css-compatibility', WPSC_CORE_THEME_URL . 'compatibility.css',                                    false, $version_identifier, 'all' );
		wp_enqueue_style( 'wpsc-product-rater',           WPSC_CORE_JS_URL 	. '/product_rater.css',                                       false, $version_identifier, 'all' );
		wp_enqueue_style( 'wp-e-commerce-dynamic',        $siteurl 			. "/index.php?wpsc_user_dynamic_css=true&category=$category_id", false, $version_identifier, 'all' );
		wp_enqueue_style( 'wpsc-thickbox',                WPSC_CORE_JS_URL 	. '/thickbox.css',                                            false, $version_identifier, 'all' );

	}


	if ( !defined( 'WPSC_MP3_MODULE_USES_HOOKS' ) && function_exists( 'listen_button' ) ) {

		function wpsc_legacy_add_mp3_preview( $product_id, &$product_data ) {
			global $wpdb;
			if ( function_exists( 'listen_button' ) ) {
				$file_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_PRODUCT_FILES . "` WHERE `id`='" . $product_data['file'] . "' LIMIT 1", ARRAY_A );
				if ( $file_data != null ) {
					echo listen_button( $file_data['idhash'], $file_data['id'] );
				}
			}
		}

		add_action( 'wpsc_product_before_description', 'wpsc_legacy_add_mp3_preview', 10, 2 );
	}
}
if ( !is_admin() )
	add_action( 'init', 'wpsc_enqueue_user_script_and_css' );

function wpsc_product_list_rss_feed() {
	$rss_url = add_query_arg( 'wpsc_action', 'rss' );
	$rss_url = str_replace('&', '&amp;', $rss_url);
	echo "<link rel='alternate' type='application/rss+xml' title='" . get_option( 'blogname' ) . " Product List RSS' href='{$rss_url}'/>";
}
add_action( 'wp_head', 'wpsc_product_list_rss_feed' );

function wpsc_user_dynamic_js() {
	header( 'Content-Type: text/javascript' );
	header( 'Expires: ' . gmdate( 'r', mktime( 0, 0, 0, date( 'm' ), (date( 'd' ) + 12 ), date( 'Y' ) ) ) . '' );
	header( 'Cache-Control: public, must-revalidate, max-age=86400' );
	header( 'Pragma: public' );
	$siteurl = get_option( 'siteurl' );
?>
		jQuery.noConflict();

		/* base url */
		var base_url = "<?php echo $siteurl; ?>";
		var WPSC_URL = "<?php echo WPSC_URL; ?>";
		var WPSC_IMAGE_URL = "<?php echo WPSC_IMAGE_URL; ?>";
		var WPSC_DIR_NAME = "<?php echo WPSC_DIR_NAME; ?>";
		var WPSC_CORE_IMAGES_URL = "<?php echo WPSC_CORE_IMAGES_URL; ?>";

		/* LightBox Configuration start*/
		var fileLoadingImage = "<?php echo WPSC_CORE_IMAGES_URL; ?>/loading.gif";
		var fileBottomNavCloseImage = "<?php echo WPSC_CORE_IMAGES_URL; ?>/closelabel.gif";
		var fileThickboxLoadingImage = "<?php echo WPSC_CORE_IMAGES_URL; ?>/loadingAnimation.gif";
		var resizeSpeed = 9;  // controls the speed of the image resizing (1=slowest and 10=fastest)
		var borderSize = 10;  //if you adjust the padding in the CSS, you will need to update this variable
<?php
	exit();
}
if ( isset( $_GET['wpsc_user_dynamic_js'] ) && ($_GET['wpsc_user_dynamic_js'] == 'true') )
	add_action( "init", 'wpsc_user_dynamic_js' );

function wpsc_user_dynamic_css() {
	global $wpdb;
	header( 'Content-Type: text/css' );
	header( 'Expires: ' . gmdate( 'r', mktime( 0, 0, 0, date( 'm' ), (date( 'd' ) + 12 ), date( 'Y' ) ) ) . '' );
	header( 'Cache-Control: public, must-revalidate, max-age=86400' );
	header( 'Pragma: public' );

	$category_id = absint( $_GET['category'] );

	if ( !defined( 'WPSC_DISABLE_IMAGE_SIZE_FIXES' ) || (constant( 'WPSC_DISABLE_IMAGE_SIZE_FIXES' ) != true) ) {
		$thumbnail_width = get_option( 'product_image_width' );
		if ( $thumbnail_width <= 0 ) {
			$thumbnail_width = 96;
		}
		$thumbnail_height = get_option( 'product_image_height' );
		if ( $thumbnail_height <= 0 ) {
			$thumbnail_height = 96;
		}

		$single_thumbnail_width = get_option( 'single_view_image_width' );
		$single_thumbnail_height = get_option( 'single_view_image_height' );
		if ( $single_thumbnail_width <= 0 ) {
			$single_thumbnail_width = 128;
		}
?>

		/*
		* Default View Styling
		*/
		div.default_product_display div.textcol{
			margin-left: <?php echo $thumbnail_width + 10; ?>px !important;
			min-height: <?php echo $thumbnail_height; ?>px;
			_height: <?php echo $thumbnail_height; ?>px;
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
			width: <?php echo $thumbnail_width - 2; ?>px;
			height: <?php echo $thumbnail_height - 2; ?>px;
		}
		div.default_product_display div.item_no_image a  {
			width: <?php echo $thumbnail_width - 2; ?>px;
		}

		div.default_product_display .imagecol img.no-image, #content div.default_product_display .imagecol img.no-image {
			width: <?php echo $thumbnail_width; ?>px;
			height: <?php echo $thumbnail_height; ?>px;                    
        }

		/*
		* Grid View Styling
		*/
		div.product_grid_display div.item_no_image  {
			width: <?php echo $thumbnail_width - 2; ?>px;
			height: <?php echo $thumbnail_height - 2; ?>px;
		}
		div.product_grid_display div.item_no_image a  {
			width: <?php echo $thumbnail_width - 2; ?>px;
		}

			.product_grid_display .product_grid_item  {
			width: <?php echo $thumbnail_width; ?>px;
		}
		.product_grid_display .product_grid_item img.no-image, #content .product_grid_display .product_grid_item img.no-image {
			width: <?php echo $thumbnail_width; ?>px;
			height: <?php echo $thumbnail_height; ?>px;                    
        }
        <?php if(get_option('show_images_only') == 1): ?>
        .product_grid_display .product_grid_item  {
        	min-height:0 !important;
			width: <?php echo $thumbnail_width; ?>px;
			height: <?php echo $thumbnail_height; ?>px;
            
		}
		<?php endif; ?>



		/*
		* Single View Styling
		*/

		div.single_product_display div.item_no_image  {
			width: <?php echo $single_thumbnail_width - 2; ?>px;
			height: <?php echo $single_thumbnail_height - 2; ?>px;
		}
		div.single_product_display div.item_no_image a  {
			width: <?php echo $single_thumbnail_width - 2; ?>px;
		}

		div.single_product_display div.textcol{
			margin-left: <?php echo $single_thumbnail_width + 10; ?>px !important;
			min-height: <?php echo $single_thumbnail_height; ?>px;
			_height: <?php echo $single_thumbnail_height; ?>px;
		}


		div.single_product_display  div.textcol div.imagecol{
			position:absolute;

			margin-left: -<?php echo $single_thumbnail_width + 10; ?>px !important;
		}

		div.single_product_display  div.textcol div.imagecol a img {
			width: <?php echo $single_thumbnail_width; ?>px;
			height: <?php echo $single_thumbnail_height; ?>px;
		}

<?php
		foreach ( (array)$product_image_size_list as $product_image_sizes ) {
			$individual_thumbnail_height = $product_image_sizes['height'];
			$individual_thumbnail_width = $product_image_sizes['width'];
			$product_id = $product_image_sizes['id'];
			if ( $individual_thumbnail_height > $thumbnail_height ) {
				echo "		div.default_product_display.product_view_$product_id div.textcol{\n\r";
				echo "			min-height: " . ($individual_thumbnail_height + 10) . "px !important;\n\r";
				echo "			_height: " . ($individual_thumbnail_height + 10) . "px !important;\n\r";
				echo "		}\n\r";
			}

			if ( $individual_thumbnail_width > $thumbnail_width ) {
				echo "		div.default_product_display.product_view_$product_id div.textcol{\n\r";
				echo "			margin-left: " . ($individual_thumbnail_width + 10) . "px !important;\n\r";
				echo "		}\n\r";

				echo "		div.default_product_display.product_view_$product_id  div.textcol div.imagecol{\n\r";
				echo "			position:absolute;\n\r";
				echo "			top:0px;\n\r";
				echo "			left: 0px;\n\r";
				echo "			margin-left: -" . ($individual_thumbnail_width + 10) . "px !important;\n\r";
				echo "		}\n\r";
			}

			if ( ($individual_thumbnail_width > $thumbnail_width) || ($individual_thumbnail_height > $thumbnail_height) ) {
				echo "		div.default_product_display.product_view_$product_id  div.textcol div.imagecol a img{\n\r";
				echo "			width: " . $individual_thumbnail_width . "px;\n\r";
				echo "			height: " . $individual_thumbnail_height . "px;\n\r";
				echo "		}\n\r";
			}
		}
	}
	if ( is_numeric( $_GET['brand'] ) || (get_option( 'show_categorybrands' ) == 3) ) {
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
if ( isset( $_GET['wpsc_user_dynamic_css'] ) && ($_GET['wpsc_user_dynamic_css'] == 'true') )
	add_action( "init", 'wpsc_user_dynamic_css' );

// Template tags
/**
 * wpsc display products function
 * @return string - html displaying one or more products
 */
function wpsc_display_products_page( $query ) {
	global $wpdb, $wpsc_query,$wp_query;
	static $count = 0;
	$count++;
		remove_filter('the_title','wpsc_the_category_title');
	if ( $count > 10 )
		exit( 'fail' );

	// If the data is coming from a shortcode parse the values into the args variable, 
	// I did it this was to preserve backwards compatibility
	if(!empty($query)){	
		$args['post_type'] = 'wpsc-product';
		if(!empty($query['product_id']) && is_array($query['product_id'])){
			$args['post__in'] = $query['product_id'];
		}elseif(is_string($query['product_id'])){
			$args['post__in'] = (array)$query['product_id'];
		}
		if(!empty($query['price']) && 'sale' != $query['price']){
			$args['meta_key'] = '_wpsc_price';
			$args['meta_value'] = $query['price'];
		}elseif(!empty($query['price']) && 'sale' == $query['price']){
			$args['meta_key'] = '_wpsc_special_price';
			$args['meta_compare'] = '>=';
			$args['meta_value'] = '1';
		}
		if(!empty($query['product_name'])){
			$args['pagename'] = $query['product_name'];
		}
		if(!empty($query['category_id'])){
			//unset($args['post_type']);

			$term = get_term($query['category_id'],'wpsc_product_category');
			$id = wpsc_get_meta($query['category_id'], 'category_id','wpsc_old_category');
			if( !empty($id)){
				$term = get_term($id,'wpsc_product_category');
				$args['wpsc_product_category'] = $term->slug;
				$args['wpsc_product_category__in'] = $term->term_id;
			}else{
				$args['wpsc_product_category'] = $term->slug;
				$args['wpsc_product_category__in'] = $term->term_id;
			}
		}
		if(!empty($query['category_url_name'])){
			$args['wpsc_product_category'] = $query['category_url_name'];
		}
		if(!empty($query['sort_order'])){
			$args['orderby'] = $query['sort_order'];
		}
		if(!empty($query['order'])){
			$args['order'] = $query['order'];
		}
		if(!empty($query['limit_of_items'])){
			$args['posts_per_page'] = $query['limit_of_items'];
		}	
		if(!empty($query['number_per_page'])){
			$args['posts_per_page'] = $query['number_per_page'];
		}	
		if(!empty($query['tag'])){
			$args['product_tag'] = $query['tag'];
		}
	
		$temp_wpsc_query = new WP_Query($args);
	}
	//	exit('<pre>'.print_r($temp_wpsc_query,1).'</pre>');
	// swap the wpsc_query objects
	list( $wp_query, $temp_wpsc_query ) = array( $temp_wpsc_query, $wp_query ); 
	$GLOBALS['nzshpcrt_activateshpcrt'] = true;
	
	//Pretty sure this single_product code is legacy...but fixing it up just in case.
	// get the display type for the selected category
	if(!empty($temp_wpsc_query->query_vars['term']))
		$display_type = wpsc_get_the_category_display($temp_wpsc_query->query_vars['term']);	
	else
		$display_type = wpsc_get_the_category_display($args['wpsc_product_category']);	


	if ( isset( $_SESSION['wpsc_display_type'] ) )
		$display_type = $_SESSION['wpsc_display_type'];

	ob_start();
	wpsc_include_products_page_template($display_type);
	$is_single = false;
	
	$output = ob_get_contents();
	ob_end_clean();
	$output = str_replace('\$','$', $output);
	list($temp_wpsc_query, $wp_query) = array( $wp_query, $temp_wpsc_query ); // swap the wpsc_query objects back
	if ( $is_single == false ) {
		$GLOBALS['post'] = $wp_query->post;
	}
	return $output;
}

/**
 * This switched between the 3 view types on category and products pages and includes the necessary tempalte part
 * @access public
 *
 * @since 3.8
 * @param $display_type
 * @return NULL
 */
function wpsc_include_products_page_template($display_type = 'default'){
	if ( isset( $_GET['view_type'] ) ) {
			switch ( $_GET['view_type'] ) {
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
		switch ( $display_type ) {
			case "grid":
				include( wpsc_get_template_file_path( 'wpsc-grid_view.php' ) );
				break; // only break if we have the function;

			case "list":
				include( wpsc_get_template_file_path( 'wpsc-list_view.php' ) );
				break; // only break if we have the file;

			case "default":  // this may be redundant :D
			default:
				include( wpsc_get_template_file_path( 'wpsc-products_page.php' ) );
				break;
		}
	
}

//handles replacing the tags in the pages  
function wpsc_products_page( $content = '' ) {
	global $wpdb, $wp_query, $wpsc_query, $wpsc_query_vars;
	$output = '';
	remove_filter( 'the_content', 'wpautop' );
		//exit($content.'<pre>'.print_r($wp_query,true).'</pre>');
	if ( preg_match( "/\[productspage\]/", $content ) ) {
		//	remove_filter( 'the_content', 'wpsc_products_page' );
		list($wp_query, $wpsc_query) = array( $wpsc_query, $wp_query ); // swap the wpsc_query object
		$GLOBALS['nzshpcrt_activateshpcrt'] = true;

		// get the display type for the productspage		
		$display_type = get_option('product_view');
		if ( isset( $_SESSION['wpsc_display_type'] ) ) {
			$display_type = $_SESSION['wpsc_display_type'];
		}
		ob_start();
		wpsc_include_products_page_template($display_type);
		$is_single = false;
		$output .= ob_get_contents();
		ob_end_clean();
		$output = str_replace( '$', '\$', $output );
		
		$product_id = $wp_query->post->ID;
		$product_meta = get_post_meta( $product_id, '_wpsc_product_metadata', true );
		
		list($wp_query, $wpsc_query) = array( $wpsc_query, $wp_query ); // swap the wpsc_query objects back
		if ( ($is_single == false) || ($product_meta['enable_comments'] == '0') )
			$GLOBALS['post'] = $wp_query->post;
		return preg_replace( "/(<p>)*\[productspage\](<\/p>)*/", $output, $content );
	} elseif(is_archive() && wpsc_is_viewable_taxonomy()){	

		return wpsc_products_page('[productspage]');
	} else {
		return $content;
	}
}
function wpsc_all_products_on_page(){
	global $wp_query,$wpsc_query;
	if($wp_query->query_vars['post_type'] == 'wpsc-product'){
		if(1 ==$wp_query->query_vars['post_count'] && file_exists(TEMPLATEPATH.'/single-wpsc-product.php'))
			include(TEMPLATEPATH. '/single-wpsc-product.php');
		else
			include(TEMPLATEPATH. '/page.php');
		exit();
	}
	return;

}
add_action('template_redirect', 'wpsc_all_products_on_page');

/**
 * wpsc_count_themes_in_uploads_directory, does exactly what the name says
 */
function wpsc_count_themes_in_uploads_directory() {
	$uploads_dir = false;
	if ( is_dir( WPSC_OLD_THEMES_PATH.get_option('wpsc_selected_theme').'/' ) )
		$uploads_dir = @opendir( WPSC_OLD_THEMES_PATH.get_option('wpsc_selected_theme').'/' ); // might cause problems if dir doesnt exist

	if ( !$uploads_dir )
		return false;

	$file_names = array( );
	while ( ($file = @readdir( $uploads_dir )) !== false ) {
		if ( is_dir( WPSC_OLD_THEMES_PATH . get_option('wpsc_selected_theme') . '/' . $file ) && ($file != "..") && ($file != ".") && ($file != ".svn") )
			$file_names[] = $file;

	}
	@closedir( $uploads_dir );
	return count( $file_names );
}

function wpsc_place_shopping_cart( $content = '' ) {
	if ( preg_match( "/\[shoppingcart\]/", $content ) ) {
		$GLOBALS['nzshpcrt_activateshpcrt'] = true;
		define( 'DONOTCACHEPAGE', true );
		ob_start();
		include( wpsc_get_template_file_path( 'wpsc-shopping_cart_page.php' ) );
		$output = ob_get_contents();
		ob_end_clean();
		$output = str_replace( '$', '\$', $output );
		return preg_replace( "/(<p>)*\[shoppingcart\](<\/p>)*/", $output, $content );
	} else {
		return $content;
	}
}

function wpsc_transaction_results( $content = '' ) {

	if ( preg_match( "/\[transactionresults\]/", $content ) ) {
		define( 'DONOTCACHEPAGE', true );
		ob_start();
		include( wpsc_get_template_file_path( 'wpsc-transaction_results.php' ) );
		$output = ob_get_contents();
		ob_end_clean();
		return preg_replace( "/(<p>)*\[transactionresults\](<\/p>)*/", $output, $content );
	} else {
		return $content;
	}
}

function wpsc_user_log( $content = '' ) {

	if ( preg_match( "/\[userlog\]/", $content ) ) {
		define( 'DONOTCACHEPAGE', true );

		ob_start();

		include( wpsc_get_template_file_path('wpsc-user-log.php') );
		$output = ob_get_contents();

		ob_end_clean();
		return preg_replace( "/(<p>)*\[userlog\](<\/p>)*/", $output, $content );
	} else {
		return $content;
	}
}

//displays a list of categories when the code [showcategories] is present in a post or page.
function wpsc_show_categories( $content = '' ) {
	if ( preg_match( "/\[showcategories\]/", $content ) ) {
		$GLOBALS['nzshpcrt_activateshpcrt'] = true;
		$output = nzshpcrt_display_categories_groups();
		return preg_replace( "/(<p>)*\[showcategories\](<\/p>)*/", $output, $content );
	} else {
		return $content;
	}
}
function wpec_get_the_post_id_by_shortcode($shortcode){
	global $wpdb;
	$sql = "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` IN('page','post') AND `post_content` LIKE '%$shortcode%' LIMIT 1";
	$page_id = $wpdb->get_var($sql);
	return $page_id;
}

function wpec_remap_shop_subpages($vars) {
  if(empty($vars))
  	return $vars;
  $reserved_names = array('[shoppingcart]','[userlog]','[transactionresults]');
  foreach($reserved_names as $reserved_name){
	  $page_id = wpec_get_the_post_id_by_shortcode($reserved_name);	
	  $page = get_post($page_id);
	  if (isset($vars['taxonomy']) && $vars['taxonomy'] == 'wpsc_product_category') {
	    if (isset($vars['term']) && $vars['term'] == $page->post_name) {
	      return array('page_id' => $page->ID);
	    }
	  }
  }
//  echo '<pre>'.print_r($vars,true).'</pre>';
  return $vars;
}

add_filter('request','wpec_remap_shop_subpages');
function wpsc_remove_page_from_query_string($query_string)
{
	
	if ($query_string['name'] == 'page' && isset($query_string['page'])) {
		unset($query_string['name']);
		list($delim, $page_index) = split('/', $query_string['page']);

		$query_string['paged'] = $page_index;
	}
	
	if('page' == $query_string['wpsc-product'])
		$query_string['wpsc-product'] = '';
	
	if(is_numeric($query_string['name'])){
		$query_string['paged'] = $query_string['name'];
		$query_string['page'] = '/'.$query_string['name'];

		$query_string['posts_per_page'] = get_option('wpsc_products_per_page');		
	}
	if(is_numeric($query_string['wpsc-product']))
		unset( $query_string['wpsc-product'] );
		
	if('page' ==$query_string['wpsc_product_category'])	
		unset( $query_string['wpsc_product_category'] );
	if(is_numeric($query_string['name']))
		unset( $query_string['name'] );	
	if('page' ==$query_string['term'])	{
		unset( $query_string['term'] );	
		unset( $query_string['taxonomy'] );	
	}
//	echo '2<pre>'.print_r($query_string,true).'</pre>';
	return $query_string;
}
add_filter('request', 'wpsc_remove_page_from_query_string');

/* 19-02-09
 * add to cart shortcode function used for shortcodes calls the function in
 * product_display_functions.php
 */

function add_to_cart_shortcode( $content = '' ) {
	//exit($content);
	if ( preg_match_all( "/\[add_to_cart=([\d]+)\]/", $content, $matches ) ) {
		foreach ( $matches[1] as $key => $product_id ) {
			$original_string = $matches[0][$key];
			$output = wpsc_add_to_cart_button( $product_id, true );
			$content = str_replace( $original_string, $output, $content );
		}
	}
	return $content;
}
function wpsc_enable_page_filters( $excerpt = '' ) {
	global $wp_query;
	add_filter( 'the_content', 'add_to_cart_shortcode', 12 ); //Used for add_to_cart_button shortcode
	add_filter( 'the_content', 'wpsc_products_page', 1 );
	add_filter( 'the_content', 'wpsc_single_template',12 );
	add_filter( 'archive_template','wpsc_the_category_template');
	add_filter( 'the_title', 'wpsc_the_category_title',10,2 );	
	add_filter( 'the_content', 'wpsc_place_shopping_cart', 12 );
	add_filter( 'the_content', 'wpsc_transaction_results', 12 );
	add_filter( 'the_content', 'nszhpcrt_homepage_products', 12 );
	add_filter( 'the_content', 'wpsc_user_log', 12 );
	add_filter( 'the_content', 'nszhpcrt_category_tag', 12 );
	add_filter( 'the_content', 'wpsc_show_categories', 12 );
	return $excerpt;
}

function wpsc_disable_page_filters( $excerpt = '' ) {
	remove_filter( 'the_content', 'add_to_cart_shortcode' ); //Used for add_to_cart_button shortcode
	remove_filter( 'the_content', 'wpsc_products_page' );
	remove_filter( 'the_content', 'wpsc_single_template' );
	remove_filter( 'the_content', 'wpsc_place_shopping_cart' );
	remove_filter( 'the_content', 'wpsc_transaction_results' );
	remove_filter( 'the_content', 'nszhpcrt_homepage_products' );
	remove_filter( 'the_content', 'wpsc_user_log' );
	remove_filter( 'the_content', 'wpsc_category_tag' );
	remove_filter( 'the_content', 'wpsc_show_categories' );
	remove_filter( 'the_content', 'wpsc_substitute_buy_now_button' );
	return $excerpt;
}

wpsc_enable_page_filters();

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
	if ( isset( $wp_query->post->ID ) )
		$post_id = $wp_query->post->ID;
	$page_url = get_permalink( $post_id );

	// If on a product or category page...
	if ( get_option( 'product_list_url' ) == $page_url ) {

		$classes[] = 'wpsc';

		if ( !is_array( $wpsc_query->query ) )
			$classes[] = 'wpsc-home';

		if ( wpsc_is_single_product ( ) ) {
			$classes[] = 'wpsc-single-product';
			if ( absint( $wpsc_query->products[0]['id'] ) > 0 ) {
				$classes[] = 'wpsc-single-product-' . $wpsc_query->products[0]['id'];
			}
		}

		if ( wpsc_is_in_category() && !wpsc_is_single_product() )
			$classes[] = 'wpsc-category';

		if ( isset( $wpsc_query->query_vars['category_id'] ) && absint( $wpsc_query->query_vars['category_id'] ) > 0 )
			$classes[] = 'wpsc-category-' . $wpsc_query->query_vars['category_id'];

	}

	// If viewing the shopping cart...
	if ( get_option( 'shopping_cart_url' ) == $page_url ) {
		$classes[] = 'wpsc';
		$classes[] = 'wpsc-shopping-cart';
	}

	// If viewing the transaction...
	if ( get_option( 'transact_url' ) == $page_url ) {
		$classes[] = 'wpsc';
		$classes[] = 'wpsc-transaction-details';
	}

	// If viewing your account...
	if ( get_option( 'user_account_url' ) == $page_url ) {
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
function wpsc_the_sticky_image( $product_id ) {
	global $wpdb;
	$attached_images = (array)get_posts( array(
				'post_type' => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $product_id,
				'orderby' => 'menu_order',
				'order' => 'ASC'
			) );
	if ( has_post_thumbnail( $product_id ) ) {
		add_image_size( 'featured-product-thumbnails', 540, 260, TRUE );
		$image = get_the_post_thumbnail( $product_id, 'featured-product-thumbnails' );
		return $image;
	} elseif ( !empty( $attached_images ) ) {
		$attached_image = $attached_images[0];
		$image_link = wpsc_product_image( $attached_image->ID, 540, 260 );
		return '<img src="' . $image_link . '" alt="" />';
	} else {
		return false;
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
	global $wpdb, $wpsc_query,$wp_query;
//	echo('<pre>'.print_r($wp_query,true).'</pre>');
	$sticky_array = get_option( 'sticky_products' );
	if ( (is_front_page() || is_home()) && !empty( $sticky_array ) && $wp_query->post_count > 1) {
		$query = get_posts( array(
					'post__in' => $sticky_array,
					'post_type' => 'wpsc-product',
					'orderby' => 'rand',
					'numberposts' => 1,
					'posts_per_page' => 1
				) );

		if ( count( $query ) > 0 ) {

			$GLOBALS['nzshpcrt_activateshpcrt'] = true;
			$image_width = get_option( 'product_image_width' );
			$image_height = get_option( 'product_image_height' );

			//Begin outputting featured product.  We can worry about templating later, or folks can just CSS it up.
			foreach ( $query as $product ) :
				setup_postdata( $product );

?>

				<div class="wpsc_container wpsc_featured">
					<div class="featured_product_display">
						<div class="featured_product_display_item product_view_<?php the_ID(); ?>">
							<div class="item_text">
								<h3>
									<a href='<?php echo get_permalink( $product->ID ); ?>'><?php echo get_the_title( $product->ID ); ?></a>
								</h3>
								<div class="pricedisplay"><?php echo wpsc_the_product_price(); ?></div>
								<div class='wpsc_description'>
<?php the_excerpt(); ?>
									<a href='<?php echo get_permalink( $product->ID ); ?>'>
											  More Information&hellip;
									</a>
								</div>
							</div>

<?php if ( wpsc_the_product_thumbnail ( ) ) : ?>
								<div class="featured_item_image">
									<a href="<?php echo get_permalink( $product->ID ); ?>" title="<?php echo get_the_title( $product->ID ); ?>">
<?php echo wpsc_the_sticky_image( wpsc_the_product_id() ); ?>
									</a>
								</div>
					<?php else: ?>
				<div class="item_no_image">
					<a href="<?php echo get_the_title( $product->ID ); ?>">
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
add_action( 'wpsc_top_of_products_page', 'wpsc_display_featured_products_page', 12 );


/**
 * wpsc_display_products_page class
 *
 * Shows only products from current category, but not from subcategories.
 * 
 * @access public
 * @return void
 */

class WPSC_Hide_subcatsprods_in_cat {
	var $q;

	function get_posts( &$q ) {
		$this->q =& $q;
		
		if ( "wpsc_product_category" != $q->query_vars['taxonomy'] )
			return false;
		
		add_action( 'posts_where', array( &$this, 'where' ) );
		add_action( 'posts_join', array( &$this, 'join' ) );
	}

	function where( $where ) {
		global $wpdb;

		remove_action( 'posts_where', array( &$this, 'where' ) );
		
		$term_id=$wpdb->get_var($wpdb->prepare('SELECT term_id FROM '.$wpdb->terms.' WHERE slug = %s ', $this->q->query_vars['term']));
		
		if ( !is_numeric( $term_id ) || $term_id < 1 )
			return $where;

		$term_taxonomy_id = $wpdb->get_var($wpdb->prepare('SELECT term_taxonomy_id FROM '.$wpdb->term_taxonomy.' WHERE term_id = %d and taxonomy = %s', $term_id, $this->q->query_vars['taxonomy']));

		if ( !is_numeric($term_taxonomy_id) || $term_taxonomy_id < 1)
			return $where;

		$field = preg_quote( "$wpdb->term_relationships.term_taxonomy_id", '#' );

		$just_one = $wpdb->prepare( " AND $wpdb->term_relationships.term_taxonomy_id = %d ", $term_taxonomy_id );
		if ( preg_match( "#AND\s+$field\s+IN\s*\(\s*(?:['\"]?\d+['\"]?\s*,\s*)*['\"]?\d+['\"]?\s*\)#", $where, $matches ) )
			$where = str_replace( $matches[0], $just_one, $where );
		else
			$where .= $just_one;

		return $where;
	}
	
	function join($join){
		global $wpdb;
		remove_action( 'posts_where', array( &$this, 'where' ) );
		if(strpos($join, "JOIN $wpdb->term_relationships ON $wpdb->posts.id =  $wpdb->term_relationships.object_id"))
			return $join;
		$join .= " JOIN $wpdb->term_relationships ON $wpdb->posts.id =  $wpdb->term_relationships.object_id ";
		return $join;
	}
}

function wpsc_hidesubcatprods_init() {
	$hide_subcatsprods = new WPSC_Hide_subcatsprods_in_cat;
	add_action( 'pre_get_posts', array( &$hide_subcatsprods, 'get_posts' ) );
}


$show_subcatsprods_in_cat = get_option( 'show_subcatsprods_in_cat' );
if(!$show_subcatsprods_in_cat)
	add_action( 'init', 'wpsc_hidesubcatprods_init' );


function wpsc_the_featured_image_fix($stuff){
	global $wp_query;
	remove_action('post_thumbnail_html','wpsc_the_featured_image_fix');
	if(isset($wp_query->query_vars['wpsc-product'])){
		$stuff ='';	?>
		<img src="<?php header_image(); ?>" width="<?php echo HEADER_IMAGE_WIDTH; ?>" height="<?php echo HEADER_IMAGE_HEIGHT; ?>" alt="" /><?php
	}
	return $stuff;

}

add_action('post_thumbnail_html','wpsc_the_featured_image_fix');
?>