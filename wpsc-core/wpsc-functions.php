<?php

/**
 * WP eCommerce core functions
 *
 * These are core functions for wp-eCommerce
 * Things like registering custom post types and taxonomies, rewrite rules, wp_query modifications, link generation and some basic theme finding code is located here
 *
 * @package wp-e-commerce
 * @since 3.8
 */

/**
 * wpsc_core_load_textdomain()
 *
 * Load up the WPEC textdomain
 */
function wpsc_core_load_textdomain() {
	$locale = apply_filters( 'wpsc_locale', get_locale() );
	$mofile = WPSC_FOLDER . "/wpsc-languages/wpsc-$locale.mo";

	if ( file_exists( $mofile ) )
		load_textdomain( 'wpsc', $mofile );
}
add_action( 'init', 'wpsc_core_load_textdomain' );

/**
 * wpsc_core_load_thumbnail_sizes()
 *
 * Load up the WPEC core thumbnail sizes
 */
function wpsc_core_load_thumbnail_sizes() {
	// Add image sizes for products
	add_image_size( 'product-thumbnails', get_option( 'product_image_width' ), get_option( 'product_image_height' ), get_option( 'wpsc_crop_thumbnails', false )  );
	add_image_size( 'gold-thumbnails', get_option( 'wpsc_gallery_image_height' ), get_option( 'wpsc_gallery_image_width' ), get_option( 'wpsc_crop_thumbnails', false ) );
	add_image_size( 'admin-product-thumbnails', 38, 38, get_option( 'wpsc_crop_thumbnails', true )  );
	add_image_size( 'featured-product-thumbnails', 540, 260, get_option( 'wpsc_crop_thumbnails', true )  );
	add_image_size( 'small-product-thumbnail', get_option( 'product_image_width' ), get_option( 'product_image_height' ), get_option( 'wpsc_crop_thumbnails', false ) );
	add_image_size( 'medium-single-product', get_option( 'single_view_image_width' ), get_option( 'single_view_image_height' ), get_option( 'wpsc_crop_thumbnails', false) );
}

/**
 * wpsc_core_load_purchase_log_statuses()
 *
 * @global array $wpsc_purchlog_statuses
 */
function wpsc_core_load_purchase_log_statuses() {
	global $wpsc_purchlog_statuses;

	$wpsc_purchlog_statuses = array(
		array(
			'internalname' => 'incomplete_sale',
			'label'        => __( 'Incomplete Sale', 'wpsc' ),
			'order'        => 1,
		),
		array(
			'internalname' => 'order_received',
			'label'        => __( 'Order Received', 'wpsc' ),
			'order'        => 2,
		),
		array(
			'internalname'   => 'accepted_payment',
			'label'          => __( 'Accepted Payment', 'wpsc' ),
			'is_transaction' => true,
			'order'          => 3,
		),
		array(
			'internalname'   => 'job_dispatched',
			'label'          => __( 'Job Dispatched', 'wpsc' ),
			'is_transaction' => true,
			'order'          => 4,
		),
		array(
			'internalname'   => 'closed_order',
			'label'          => __( 'Closed Order', 'wpsc' ),
			'is_transaction' => true,
			'order'          => 5,
		),
		array(
			'internalname'   => 'declined_payment',
			'label'          => __( 'Payment Declined', 'wpsc' ),
			'order'          => 6,
		),
	);
	$wpsc_purchlog_statuses = apply_filters('wpsc_set_purchlog_statuses',$wpsc_purchlog_statuses);
}

/**
 * wpsc_core_load_page_titles()
 *
 * Load the WPEC page titles
 *
 * @global array $wpsc_page_titles
 */
function wpsc_core_load_page_titles() {
	global $wpsc_page_titles;
	$wpsc_page_titles = wpsc_get_page_post_names();
}

/***
 * wpsc_core_load_gateways()
 *
 * Gets the merchants from the merchants directory and eeds to search the
 * merchants directory for merchants, the code to do this starts here.
 *
 * @todo Come up with a better way to do this than a global $num value
 */
function wpsc_core_load_gateways() {
	global $nzshpcrt_gateways, $num, $wpsc_gateways,$gateway_checkout_form_fields;

	$gateway_directory      = WPSC_FILE_PATH . '/wpsc-merchants';
	$nzshpcrt_merchant_list = wpsc_list_dir( $gateway_directory );

	$num = 0;
	foreach ( $nzshpcrt_merchant_list as $nzshpcrt_merchant ) {
		if ( stristr( $nzshpcrt_merchant, '.php' ) ) {
			require( WPSC_FILE_PATH . '/wpsc-merchants/' . $nzshpcrt_merchant );
		}
		$num++;
	}
	unset( $nzshpcrt_merchant );

	$nzshpcrt_gateways = apply_filters( 'wpsc_merchants_modules', $nzshpcrt_gateways );
	uasort( $nzshpcrt_gateways, 'wpsc_merchant_sort' );

	// make an associative array of references to gateway data.
	$wpsc_gateways = array();
	foreach ( (array)$nzshpcrt_gateways as $key => $gateway )
		$wpsc_gateways[$gateway['internalname']] = &$nzshpcrt_gateways[$key];

	unset( $key, $gateway );

}

/***
 * wpsc_core_load_shipping_modules()
 *
 * Gets the shipping modules from the shipping directory and needs to search
 * the shipping directory for modules.
 */
function wpsc_core_load_shipping_modules() {
	global $wpsc_shipping_modules;

	$shipping_directory     = WPSC_FILE_PATH . '/wpsc-shipping';
	$nzshpcrt_shipping_list = wpsc_list_dir( $shipping_directory );

	foreach ( $nzshpcrt_shipping_list as $nzshpcrt_shipping ) {
		if ( stristr( $nzshpcrt_shipping, '.php' ) ) {
			require( WPSC_FILE_PATH . '/wpsc-shipping/' . $nzshpcrt_shipping );
		}
	}

	$wpsc_shipping_modules = apply_filters( 'wpsc_shipping_modules', $wpsc_shipping_modules );
}

/**
 * Update Notice
 *
 * Displays an update message below the auto-upgrade link in the WordPress admin
 * to notify users that they should check the upgrade information and changelog
 * before upgrading in case they need to may updates to their theme files.
 *
 * @package wp-e-commerce
 * @since 3.7.6.1
 */
function wpsc_update_notice() {
	$info_title = __( 'Please Note', 'wpsc' );
	$info_text = sprintf( __( 'Before upgrading you should check the <a %s>upgrade information</a> and changelog as you may need to make updates to your template files.', 'wpsc' ), 'href="http://getshopped.org/resources/docs/upgrades/staying-current/" target="_blank"' );
	echo '<div style="border-top:1px solid #CCC; margin-top:3px; padding-top:3px; font-weight:normal;"><strong style="color:#CC0000">' . strip_tags( $info_title ) . '</strong>: ' . strip_tags( $info_text, '<br><a><strong><em><span>' ) . '</div>';
}
if ( is_admin() )
	add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), 'wpsc_update_notice' );

function wpsc_add_product_price_to_rss() {
	global $post;
	$price = get_post_meta( $post->ID, '_wpsc_price', true );
	echo '<price>' . $price . '</price>';
}
add_action( 'rss2_item', 'wpsc_add_product_price_to_rss' );
add_action( 'rss_item', 'wpsc_add_product_price_to_rss' );
add_action( 'rdf_item', 'wpsc_add_product_price_to_rss' );

/**
 * wpsc_register_post_types()
 *
 * The meat of this whole operation, this is where we register our post types
 *
 * @global array $wpsc_page_titles
 * @global object $wp_rewrite
 */
function wpsc_register_post_types() {
	global $wpsc_page_titles, $wp_rewrite;

	// Products
	register_post_type( 'wpsc-product', array(
		'_edit_link' => 'admin.php?page=wpsc-edit-products&action=wpsc_add_edit&product=%d',
		'capability_type' => 'post',
		'hierarchical' => true,
		'exclude_from_search' => false,
		'public' => true,
		'show_ui' => false,
		'show_in_nav_menus' => true,
		'label' => __( 'Products' ),
		'singular_label' => __( 'Product' ),
		'query_var' => true,
		'register_meta_box_cb' => 'wpsc_meta_boxes',
		'rewrite' => array(
			'slug' => $wpsc_page_titles['products'] . '/%wpsc_product_category%',
			'with_front' => false
		)
	) );

	// Purchasable product files
	register_post_type( 'wpsc-product-file', array(
		'capability_type' => 'post',
		'hierarchical' => false,
		'exclude_from_search' => true,
		'rewrite' => false
	) );

	// Product tags
	$labels = array( 'name' => _x( 'Product Tags', 'taxonomy general name' ),
		'singular_name' => _x( 'Product Tag', 'taxonomy singular name' ),
		'search_items' => __( 'Product Search Tags' ),
		'all_items' => __( 'All Product Tags' ),
		'edit_item' => __( 'Edit Tag' ),
		'update_item' => __( 'Update Tag' ),
		'add_new_item' => __( 'Add new Product Tag' ),
		'new_item_name' => __( 'New Product Tag Name' ) );

	register_taxonomy( 'product_tag', 'wpsc-product', array(
		'hierarchical' => false,
		'labels' => $labels,
		'rewrite' => array(
			'slug' => '/tagged',
			'with_front' => false )
	) );

	// Product categories, is heirarchical and can use permalinks
	
	register_taxonomy( 'wpsc_product_category', 'wpsc-product', array(
		'hierarchical' => true,
		'rewrite' => array(
			'slug' => $wpsc_page_titles['products'],
			'with_front' => false
		)
	) );
	$labels = array(
		'name' => _x( 'Variations', 'taxonomy general name' ),
		'singular_name' => _x( 'Variation', 'taxonomy singular name' ),
		'search_items' => __( 'Search Variations' ),
		'all_items' => __( 'All Variations' ),
		'parent_item' => __( 'Parent Variation' ),
		'parent_item_colon' => __( 'Parent Variations:' ),
		'edit_item' => __( 'Edit Variation' ),
		'update_item' => __( 'Update Variation' ),
		'add_new_item' => __( 'Add New Variation' ),
		'new_item_name' => __( 'New Variation Name' ),
	);

	// Product Variations, is internally heirarchical, externally, two separate types of items, one containing the other
	register_taxonomy( 'wpsc-variation', 'wpsc-product', array(
		'hierarchical' => true,
		'query_var' => 'variations',
		'rewrite' => false,
		'public' => true,
		'labels' => $labels
	) );
	$role = get_role( 'administrator' );
	$role->add_cap( 'read_wpsc-product' );
	$role->add_cap( 'read_wpsc-product-file' );
	// Product categories, temporarily register them to create first default category if none exist
	$category_list = get_terms( 'wpsc_product_category', 'hide_empty=0&parent=0' );
	if ( count( $category_list ) == 0 ) {
		$new_category = wp_insert_term( __( 'Product Category', 'wpsc' ), 'wpsc_product_category', "parent=0" );
		$category_id = $new_category['term_id'];
		$term = get_term_by( 'id', $new_category['term_id'], 'wpsc_product_category' );
		$url_name = $term->slug;

		$wp_rewrite->flush_rules();
		wpsc_update_categorymeta( $category_id, 'nice-name', $url_name );
		wpsc_update_categorymeta( $category_id, 'description', __( "This is a description", 'wpsc' ) );
		wpsc_update_categorymeta( $category_id, 'image', $image );
		wpsc_update_categorymeta( $category_id, 'fee', '0' );
		wpsc_update_categorymeta( $category_id, 'active', '1' );
		wpsc_update_categorymeta( $category_id, 'order', '0' );
	}
}
add_action( 'init', 'wpsc_register_post_types', 8 );

function wpsc_check_thumbnail_support() {
	if ( !current_theme_supports( 'post-thumbnails' ) ) {
		add_theme_support( 'post-thumbnails' );
		add_action( 'init', 'wpsc_remove_post_type_thumbnail_support' );
	}
}
add_action( 'after_setup_theme', 'wpsc_check_thumbnail_support', 99 );

function wpsc_remove_post_type_thumbnail_support() {
	remove_post_type_support( 'post', 'thumbnail' );
	remove_post_type_support( 'page', 'thumbnail' );
}

/**
 * This serializes the shopping cart variable as a backup in case the
 * unserialized one gets butchered by various things
 */
function wpsc_serialize_shopping_cart() {
	global $wpdb, $wpsc_start_time, $wpsc_cart;

	if ( is_object( $wpsc_cart ) )
		$wpsc_cart->errors = array( );

	$_SESSION['wpsc_cart'] = serialize( $wpsc_cart );

	return true;
}
add_action( 'shutdown', 'wpsc_serialize_shopping_cart' );

/**
 * wpsc_start_the_query
 */
function wpsc_start_the_query() {
	global $wp_query, $wpsc_query, $wpsc_query_vars;
	
	if ( null == $wpsc_query ) {

		if ( count( $wpsc_query_vars ) <= 1 ) {
			$wpsc_query_vars = array(
				'post_parent' => 0,
				'order'       => 'ASC'
			);
		if( isset($wp_query->query_vars['wpsc_product_category']) ){
			$wpsc_query_vars['wpsc_product_category'] = $wp_query->query_vars['wpsc_product_category'];
			$wpsc_query_vars['taxonomy'] = $wp_query->query_vars['taxonomy'];
			$wpsc_query_vars['term'] = $wp_query->query_vars['term'];
		}else{
			$wpsc_query_vars['post_type'] = 'wpsc-product';		
			if(1 == get_option('use_pagination')){
				$wpsc_query_vars['nopaging'] = false;
				$wpsc_query_vars['posts_per_page'] = get_option('wpsc_products_per_page');
				$wpsc_query_vars['paged'] = get_query_var('paged');
				$wpsc_query_vars['pagename'] = 'products-page';
			}
		}
			$orderby = get_option( 'wpsc_sort_by' );

			switch ( $orderby ) {

				case "dragndrop":
					$wpsc_query_vars["orderby"] = 'menu_order';
					break;

				case "name":
					$wpsc_query_vars["orderby"] = 'title';
					break;

				//This only works in WP 3.0.
				case "price":
					$wpsc_query_vars["meta_key"] = '_wpsc_price';
					$wpsc_query_vars["orderby"] = 'meta_value_num';
					break;

				case "id":
					$wpsc_query_vars["orderby"] = 'ID';
					break;
			}
	
		
			add_filter( 'pre_get_posts', 'wpsc_generate_product_query', 11 );
			$wpsc_query = new WP_Query( $wpsc_query_vars );
		}
	}

	if($wp_query->is_404)
		$wp_query = $wpsc_query;
	if ( isset( $wp_query->post->ID ) )
		$post_id = $wp_query->post->ID;
	else
		$post_id = 0;

	if ( get_permalink( $post_id ) == get_option( 'shopping_cart_url' ) )
		$_SESSION['wpsc_has_been_to_checkout'] = true;
}
add_action( 'template_redirect', 'wpsc_start_the_query', 8 );

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
function wpsc_taxonomy_rewrite_rules( $rewrite_rules ) {
	global $wpsc_page_titles;
	$products_page = $wpsc_page_titles['products'];
	$checkout_page = $wpsc_page_titles['checkout'];

	$target_string = "index.php?product";
	$replacement_string = "index.php?post_type=wpsc-product&product";
	$target_rule_set_query_var = 'products';

	$target_rule_set = array( );
	foreach ( $rewrite_rules as $rewrite_key => $rewrite_query ) {
		if ( stristr( $rewrite_query, "index.php?product" ) ) {
			$rewrite_rules[$rewrite_key] = str_replace( $target_string, $replacement_string, $rewrite_query );
		}
		if ( stristr( $rewrite_query, "$target_rule_set_query_var=" ) ) {
			$target_rule_set[] = $rewrite_key;
		}
	}

	//$new_rewrite_rules['products/.+?/[^/]+/attachment/([^/]+)/?$'] = 'index.php?attachment=$1';
	//$new_rewrite_rules['products/(.+?)/([^/]+)/comment-page-([0-9]{1,})/?$'] = 'index.php??post_type=wpsc-product&products=$1&name=$2&cpage=$3';
	//$new_rewrite_rules['products/.+?/([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&paged=$matches[2]';
	//$new_rewrite_rules['(products/checkout)(/[0-9]+)?/?$'] = 'index.php?pagename=$1&page=$2';
	$new_rewrite_rules[$products_page . '/(.+?)/product/([^/]+)/comment-page-([0-9]{1,})/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&name=$matches[2]&cpage=$matches[3]';
	$new_rewrite_rules[$products_page . '/(.+?)/product/([^/]+)/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&name=$matches[2]';
	$new_rewrite_rules[$products_page . '/(.+?)/([^/]+)/comment-page-([0-9]{1,})/?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&wpsc_item=$matches[2]&cpage=$matches[3]';
	$new_rewrite_rules[$products_page . '/(.+?)/([^/]+)?$'] = 'index.php?post_type=wpsc-product&products=$matches[1]&wpsc_item=$matches[2]';


	$last_target_rule = array_pop( $target_rule_set );

	$rebuilt_rewrite_rules = array( );
	foreach ( $rewrite_rules as $rewrite_key => $rewrite_query ) {
		if ( $rewrite_key == $last_target_rule ) {
			$rebuilt_rewrite_rules = array_merge( $rebuilt_rewrite_rules, $new_rewrite_rules );
		}
		$rebuilt_rewrite_rules[$rewrite_key] = $rewrite_query;
	}

	//echo "<pre>".print_r($new_rewrite_rules, true)."</pre>";
	return $rebuilt_rewrite_rules;
}

add_filter( 'rewrite_rules_array', 'wpsc_taxonomy_rewrite_rules' );

/**
 * wpsc_query_vars function.
 * adds in the post_type and wpsc_item query vars
 *
 * @since 3.8
 * @access public
 * @param mixed $vars
 * @return void
 */
function wpsc_query_vars( $vars ) {
	// post_type is used to specify that we are looking for products
	$vars[] = "post_type";
	// wpsc_item is used to find items that could be either a product or a product category, it defaults to category, then tries products
	$vars[] = "wpsc_item";
	return $vars;
}

add_filter( 'query_vars', 'wpsc_query_vars' );

/**
 * wpsc_query_modifier function.
 *
 * @since 3.8
 * @access public
 * @param object - reference to $wp_query
 * @return $query
 */
function wpsc_split_the_query( $query ) {
	global $wpsc_page_titles, $wpsc_query, $wpsc_query_vars;
	//exit('Page Titles: <pre>'.print_r($wpsc_page_titles,true).'</pre>');
	// These values are to be dynamically defined
	$products_page = $wpsc_page_titles['products'];
	$checkout_page = $wpsc_page_titles['checkout'];
	$userlog_page = $wpsc_page_titles['userlog'];
	$transaction_results_page = $wpsc_page_titles['transaction_results'];


	// otherwise, check if we are looking at a product, if so, duplicate the query and swap the old one out for a products page request
	// JS - 6.4.1020 - Added is_admin condition, as the products condition broke categories in backend
	if ( ($query->query_vars['pagename'] == $products_page) || isset( $query->query_vars['products'] ) && !is_admin() ) {
		// store a copy of the wordpress query
		$wpsc_query_data = $query->query;

		// wipe and replace the query vars
		$query->query                   = array();
		$query->query['pagename']       = "$products_page";
		$query->query_vars['pagename']  = "$products_page";
		$query->query_vars['name']      = '';
		$query->query_vars['post_type'] = '';

		$query->queried_object = & get_page_by_path( $query->query['pagename'] );

		if ( !empty( $query->queried_object ) )
			$query->queried_object_id = (int)$query->queried_object->ID;
		else
			unset( $query->queried_object );

		unset( $query->query_vars['products'] );
		unset( $query->query_vars['name'] );
		unset( $query->query_vars['taxonomy'] );
		unset( $query->query_vars['term'] );
		unset( $query->query_vars['wpsc_item'] );

		$query->is_singular = true;
		$query->is_page     = true;
		$query->is_tax      = false;
		$query->is_archive  = false;
		$query->is_single   = false;
		//$post = get_post($post_id);
		//$query->get_posts();

		if ( ($wpsc_query_vars == null ) ) {
			unset( $wpsc_query_data['pagename'] );
			$wpsc_query_vars = $wpsc_query_data;
		}
	}

	add_filter( 'redirect_canonical', 'wpsc_break_canonical_redirects', 10, 2 );
	remove_filter( 'pre_get_posts', 'wpsc_split_the_query', 8 );
}

/**
 * wpsc_generate_product_query function.
 *
 * @access public
 * @param mixed $query
 * @return void
 */
function wpsc_generate_product_query( $query ) {
	global $wp_query;

	$prod_page = wpec_get_the_post_id_by_shortcode('[productspage]');
	$prod_page = get_post($prod_page);
	remove_filter( 'pre_get_posts', 'wpsc_generate_product_query', 11 );
	$query->query_vars['taxonomy'] = null;
	$query->query_vars['term'] = null;


	// default product selection
	if ( $query->query_vars['pagename'] != '' ) {
		$query->query_vars['post_type'] = 'wpsc-product';
		$query->query_vars['pagename']  = '';
		$query->is_page     = false;
		$query->is_tax      = false;
		$query->is_archive  = true;
		$query->is_singular = false;
		$query->is_single   = false;
	}

	// If wpsc_item is not null, we are looking for a product or a product category, check for category
	if ( isset( $query->query_vars['wpsc_item'] ) && ($query->query_vars['wpsc_item'] != '') ) {
		$test_term = get_term_by( 'slug', $query->query_vars['wpsc_item'], 'wpsc_product_category' );
		if ( $test_term->slug == $query->query_vars['wpsc_item'] ) {
			// if category exists (slug matches slug), set products to value of wpsc_item
			$query->query_vars['products'] = $query->query_vars['wpsc_item'];
		} else {
			// otherwise set name to value of wpsc_item
			$query->query_vars['name'] = $query->query_vars['wpsc_item'];
		}
	}
	
	if ( isset( $query->query_vars['products'] ) && ($query->query_vars['products'] != null) && ($query->query_vars['name'] != null) ) {
		unset( $query->query_vars['taxonomy'] );
		unset( $query->query_vars['term'] );
		$query->query_vars['post_type'] = 'wpsc-product';
		$query->is_tax      = false;
		$query->is_archive  = true;
		$query->is_singular = false;
		$query->is_single   = false;

	}
	if( isset($wp_query->query_vars['wpsc_product_category']) && !isset($wp_query->query_vars['wpsc-product'])){
		$query->query_vars['wpsc_product_category'] = $wp_query->query_vars['wpsc_product_category'];
		$query->query_vars['taxonomy'] = $wp_query->query_vars['taxonomy'];
		$query->query_vars['term'] = $wp_query->query_vars['term'];
	}elseif( '' != ($default_category = get_option('wpsc_default_category')) && !isset($wp_query->query_vars['wpsc-product'])){
		$default_term = get_term($default_category,'wpsc_product_category');

		if(!empty($default_term) && empty($wp_query->query_vars['category_name'])){
			$query->query_vars['taxonomy'] = 'wpsc_product_category';
			$query->query_vars['term'] = $default_term->slug;
			$query->is_tax = true;
		}elseif(isset($wp_query->query_vars['name']) && $wp_query->is_404 && $wp_query->query_vars['category_name'] != $prod_page->post_name){
			unset( $query->query_vars['taxonomy'] );
			unset( $query->query_vars['term'] );
			$query->query_vars['wpsc-product'] = $wp_query->query_vars['name'];
			$query->query_vars['name'] = $wp_query->query_vars['name'];

		}else{
			$query->is_tax = true;
			$term =	get_term_by('slug',$wp_query->query_vars['name'], 'wpsc_product_category' );
			if(!empty($term)){
				$query->query_vars['taxonomy'] = 'wpsc_product_category';
				$query->query_vars['wpsc_product_category__in'] = array($term->term_taxonomy_id);
				$query->query_vars['wpsc_product_category'] = $wp_query->query_vars['name'];
				$query->query_vars['term'] = $wp_query->query_vars['name'];
			}else{
				$query->query_vars['taxonomy'] = 'wpsc_product_category';
			}
		}
	}
	if(get_option('wpsc_products_per_page'))
		$query->query_vars['posts_per_page'] = get_option('wpsc_products_per_page');
	else
		$query->query_vars['posts_per_page'] = '-1';	
	if ( $query->is_tax == true )
		new wpsc_products_by_category( $query );

	return $query;
}

function wpsc_mark_product_query( $query ) {

	if ( isset( $query->query_vars['post_type'] ) && ($query->query_vars['post_type'] == 'wpsc-product') )
		$query->is_product = true;

	return $query;
}
add_filter( 'pre_get_posts', 'wpsc_split_the_query', 8 );
add_filter( 'parse_query', 'wpsc_mark_product_query', 12 );

/**
 * wpsc_products_by_category class.
 *
 */
class wpsc_products_by_category {

	var $sql_components = array( );

	/**
	 * wpsc_products_by_category function.
	 *
	 * @access public
	 * @param mixed $query
	 * @return void
	 */
	function wpsc_products_by_category( $query ) {
		global $wpdb;
		$q = $query->query_vars;

		
		// Category stuff for nice URLs
		if ( ('' != $q['taxonomy']) && ('' != $q['term']) && !$query->is_singular ) {
			$join = " INNER JOIN $wpdb->term_relationships
				ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			INNER JOIN $wpdb->term_taxonomy
				ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			";

			$whichcat = " AND $wpdb->term_taxonomy.taxonomy = '{$q['taxonomy']}' ";

			$term_data = get_term_by( 'slug', $q['term'], $q['taxonomy'] );

			$term_children_data = get_term_children( $term_data->term_id, $q['taxonomy'] );

			$in_cats = array( $term_data->term_id );
			$in_cats = array_reverse( array_merge( $in_cats, $term_children_data ) );

			$in_cats = "'" . implode( "', '", $in_cats ) . "'";
			$whichcat .= "AND $wpdb->term_taxonomy.term_id IN ($in_cats)";
			$groupby = "{$wpdb->posts}.ID";

			$this->sql_components['join']     = $join;
			$this->sql_components['where']    = $whichcat;
			$this->sql_components['fields']   = "{$wpdb->posts}.*, {$wpdb->term_taxonomy}.term_id";
			$this->sql_components['order_by'] = "{$wpdb->term_taxonomy}.term_id";
			$this->sql_components['group_by'] = $groupby;
			add_filter( 'posts_join', array( &$this, 'join_sql' ) );
			add_filter( 'posts_where', array( &$this, 'where_sql' ) );
			add_filter( 'posts_fields', array( &$this, 'fields_sql' ) );
			add_filter( 'posts_orderby', array( &$this, 'order_by_sql' ) );
			add_filter( 'posts_groupby', array( &$this, 'group_by_sql' ) );
		}
		//add_filter('posts_request', array(&$this, 'request_sql'));
	}

	function join_sql( $sql ) {
		if ( isset( $this->sql_components['join'] ) )
			$sql = $this->sql_components['join'];

		remove_filter( 'posts_join', array( &$this, 'join_sql' ) );
		return $sql;
	}

	function where_sql( $sql ) {
		if ( isset( $this->sql_components['where'] ) )
			$sql = $this->sql_components['where'];

		remove_filter( 'posts_where', array( &$this, 'where_sql' ) );
		return $sql;
	}

	function order_by_sql( $sql ) {
		$order_by_parts   = array( );
		$order_by_parts[] = $sql;

		if ( isset( $this->sql_components['order_by'] ) )
			$order_by_parts[] = $this->sql_components['order_by'];

		$order_by_parts = array_reverse( $order_by_parts );
		$sql = implode( ',', $order_by_parts );

		remove_filter( 'posts_orderby', array( &$this, 'order_by_sql' ) );
		return $sql;
	}

	function fields_sql( $sql ) {
		if ( isset( $this->sql_components['fields'] ) )
			$sql = $this->sql_components['fields'];

		remove_filter( 'posts_fields', array( &$this, 'fields_sql' ) );
		return $sql;
	}

	function group_by_sql( $sql ) {
		if ( isset( $this->sql_components['group_by'] ) )
			$sql = $this->sql_components['group_by'];

		remove_filter( 'posts_groupby', array( &$this, 'group_by_sql' ) );
		return $sql;
	}

	function request_sql( $sql ) {
		echo $sql . "<br />";
		remove_filter( 'posts_request', array( &$this, 'request_sql' ) );
		return $sql;
	}
}

function wpsc_break_canonical_redirects( $redirect_url, $requested_url ) {
	global $wp_query;

	//exit("<pre>".print_r($wp_query,true)."</pre>");
	if ( ( isset( $wp_query->query_vars['products'] ) && ($wp_query->query_vars['products'] != '') ) || ( isset( $wp_query->query_vars['products'] ) && $wp_query->query_vars['products'] != 'wpsc_item') )
		return false;

	if ( stristr( $requested_url, $redirect_url ) )
		return false;

	return $redirect_url;
}

/**
 * wpsc_is_product function.
 *
 * @since 3.8
 * @access public
 * @return boolean
 */
function wpsc_is_product() {
	global $wp_query, $rewrite_rules;
	$tmp = false;

	if ( isset( $wp_query->is_product ) )
		$tmp = $wp_query->is_product;

	return $tmp;
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
	$tmp = false;

	if ( isset( $wp_query->is_checkout ) )
		$tmp = $wp_query->is_checkout;

	return $tmp;
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
function wpsc_product_link( $permalink, $post, $leavename ) {
	global $wp_query, $wpsc_page_titles;
	$term_url = '';
	$rewritecode = array(
		'%wpsc_product_category%',
		'%postname%'
	);
	if ( is_object( $post ) ) {
		// In wordpress 2.9 we got a post object
		$post_id = $post->ID;
	} else {
		// In wordpress 3.0 we get a post ID
		$post_id = $post;
		$post = get_post( $post_id );
	}

	$permalink_structure = get_option( 'permalink_structure' );
	// This may become customiseable later

	$our_permalink_structure = $wpsc_page_titles['products'] . "/%wpsc_product_category%/%postname%/";
	// Mostly the same conditions used for posts, but restricted to items with a post type of "wpsc-product "

	if ( '' != $permalink_structure && !in_array( $post->post_status, array( 'draft', 'pending' ) ) ) {
		$product_categories = wp_get_object_terms( $post_id, 'wpsc_product_category' );
		$product_category_slugs = array( );
		foreach ( $product_categories as $product_category ) {
			$product_category_slugs[] = $product_category->slug;
		}
		// If the product is associated with multiple categories, determine which one to pick

		if ( count( $product_categories ) == 0 ) {
			$category_slug = 'uncategorized';
		} elseif ( count( $product_categories ) > 1 ) {
			if ( (isset( $wp_query->query_vars['products'] ) && $wp_query->query_vars['products'] != null) && in_array( $wp_query->query_vars['products'], $product_category_slugs ) ) {
				$product_category = $wp_query->query_vars['products'];
			} else {
				$product_category = $product_category_slugs[0];
			}
			$category_slug = $product_category;
			$term_url = get_term_link( $category_slug, 'wpsc_product_category' );
		} else {
			// If the product is associated with only one category, we only have one choice
			if ( !isset( $product_categories[0] ) )
				$product_categories[0] = '';

			$product_category = $product_categories[0];
			//if(!isset($product_category->slug)) $product_category->slug="";

			if ( !is_object( $product_category ) )
				$product_category = new stdClass();

			if ( !isset( $product_category->slug ) )
				$product_category->slug = null;

			$category_slug = $product_category->slug;

			$term_url = get_term_link( $category_slug, 'wpsc_product_category' );
		}

		//echo "'><pre>_".print_r($product_categories, true)."_</pre>";
		$post_name = $post->post_name;
		if ( in_array( $post_name, $product_category_slugs ) )
			$post_name = "product/{$post_name}";

		$rewritereplace = array(
			$category_slug,
			$post_name
		);

		$permalink = str_replace( $rewritecode, $rewritereplace, $our_permalink_structure );
		$permalink = user_trailingslashit( $permalink, 'single' );
		$permalink = home_url( $permalink );
	}
	return $permalink;
}

// for wordpress 3.0
if ( IS_WP30 == true )
	add_filter( 'post_type_link', 'wpsc_product_link', 10, 3 );
// for wordpress 2.9
else	
	add_filter( 'post_link', 'wpsc_product_link', 10, 3 );

/**
 * wpsc_get_product_template function.
 *
 * @since 3.8
 * @access public
 * @return void
 */
function wpsc_get_template( $template ) {
	return get_query_template( $template );
}

/**
 * wpsc_product_template_fallback function.
 *
 * @since 3.8
 * @access public
 * @param mixed $template_path
 * @return string - the corrected template path
 */
function wpsc_template_fallback( $template_path ) {

	$prospective_file_name = basename( "{$template_path}.php" );
	$prospective_file_path = trailingslashit( WPSC_CORE_THEME_PATH ) . $prospective_file_name;

	if ( !file_exists( $prospective_file_path ) )
		exit( $prospective_file_path );

	return $prospective_file_path;
}

function wpsc_products_template_fallback() {
	return wpsc_template_fallback( 'products' );
}

//add_filter("products_template", 'wpsc_products_template_fallback');

function wpsc_checkout_template_fallback() {
	return wpsc_template_fallback( 'checkout' );
}

//add_filter("checkout_template", 'wpsc_checkout_template_fallback');

/**
 * wpsc_get_page_post_names function.
 * Seems that using just one SQL query and then processing the results is probably going to be around as efficient as just doing three separate queries
 * But using three queries is a hell of a lot simpler to write and easier to read.
 * @since 3.8
 * @access public
 * @return void
 */
function wpsc_get_page_post_names() {
	global $wpdb;
	$wpsc_page['products']            = $wpdb->get_var( "SELECT post_name FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[productspage]%'  AND `post_type` NOT IN('revision') LIMIT 1" );
	$wpsc_page['checkout']            = $wpdb->get_var( "SELECT post_name FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[shoppingcart]%'  AND `post_type` NOT IN('revision') LIMIT 1" );
	$wpsc_page['transaction_results'] = $wpdb->get_var( "SELECT post_name FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[transactionresults]%'  AND `post_type` NOT IN('revision') LIMIT 1" );
	$wpsc_page['userlog']             = $wpdb->get_var( "SELECT post_name FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%[userlog]%'  AND `post_type` NOT IN('revision') LIMIT 1" );
	return $wpsc_page;
}

/**
 * wpsc_template_loader function.
 *
 * @since 3.8
 * @access public
 * @return void
 */
function wpsc_template_loader() {
	global $wp_query;

	if ( wpsc_is_product() && $template = wpsc_get_template( 'products' ) ) {
		include( $template );
		exit();
	}

	if ( wpsc_is_checkout() && $template = wpsc_get_template( 'checkout' ) ) {
		include( $template );
		exit();
	}
}

// add_action('template_redirect','wpsc_template_loader');

/**
 * select_wpsc_theme_functions function, provides a place to override the e-commece theme path
 * add to switch "theme's functions file
 * © with xiligroup dev
 */
function wpsc_select_theme_functions() {
	$selected_theme = get_option( 'wpsc_selected_theme' );
	if ( !empty( $selected_theme ) && file_exists( WPSC_CORE_THEME_PATH . '/' . WPSC_THEME_DIR . '.php' ) )
		include_once( WPSC_CORE_THEME_PATH . '/' . WPSC_THEME_DIR . '.php' );
}
add_action( 'wp', 'wpsc_select_theme_functions', 10, 1 );

/**
 * if the user is on a checkout page, force SSL if that option is so set
 */
function wpsc_force_ssl() {
	global $post;
	if ( get_option( 'wpsc_force_ssl' ) && !is_ssl() && strpos( $post->post_content, '[shoppingcart]' ) !== FALSE ) {
		$sslurl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		header( 'Location: ' . $sslurl );
		echo 'Redirecting';
	}
}
add_action( 'get_header', 'wpsc_force_ssl' );

/**
 * wpsc_add_https_to_page_url_options( $url )
 *
 * Forces SSL onto option URLs
 *
 * @param string $url
 * @return string
 */
function wpsc_add_https_to_page_url_options( $url ) {
	return str_replace( 'http://', 'https://', $url );
}
if ( is_ssl() ) {
	add_filter( 'option_product_list_url',  'wpsc_add_https_to_page_url_options' );
	add_filter( 'option_shopping_cart_url', 'wpsc_add_https_to_page_url_options' );
	add_filter( 'option_transact_url',      'wpsc_add_https_to_page_url_options' );
	add_filter( 'option_user_account_url',  'wpsc_add_https_to_page_url_options' );
}

?>
