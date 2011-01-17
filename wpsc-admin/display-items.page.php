<?php
/**
 * WP eCommerce edit and add product page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */


/**
 * wpsc_is_admin function.
 *
 * @access public
 * @return void
 * General use function for checking if user is on WPSC admin pages
 */

require_once(WPSC_FILE_PATH . '/wpsc-admin/includes/products.php');

function wpsc_is_admin() {
    global $pagenow, $current_screen;

        if( 'post.php' == $pagenow && 'wpsc-product' == $current_screen->post_type ) return true;

    return false;
    
}

/**
 * wpsc_additional_column_names function.
 *
 * @access public
 * @param (array) $columns
 * @return (array) $columns
 *
 */
function wpsc_additional_column_names( $columns ){
    unset( $columns['title'] );
    unset( $columns['date'] );
    unset( $columns['author'] );
    
    $columns['image'] = __('');
    $columns['title'] = __('Name');
    $columns['weight'] = __('Weight');
    $columns['stock'] = __('Stock');
    $columns['price'] = __('Price');
    $columns['sale_price'] = __('Sale Price');
    $columns['SKU'] = __('SKU');
    $columns['cats'] = __('Categories');
    $columns['featured'] = __('Featured');
    $columns['hidden_alerts'] = __('');
    $columns['date'] = __('Date');

    return $columns;
}
function wpsc_additional_sortable_column_names( $columns ){

    $columns['stock'] = __('Stock');
    $columns['price'] = __('Price');
    $columns['sale_price'] = __('Sale Price');
    $columns['SKU'] = __('SKU');

    return $columns;
}
function wpsc_additional_column_name_variations( $columns ){
    global $post;
    if( $post->post_parent != '0' )
       remove_meta_box( 'wpsc_product_variation_forms', 'wpsc-product', 'normal' );

    $columns['image'] = __('');
    $columns['title'] = __('Name');
    $columns['weight'] = __('Weight');
    $columns['stock'] = __('Stock');
    $columns['price'] = __('Price');
    $columns['sale_price'] = __('Sale Price');
    $columns['SKU'] = __('SKU');
    $columns['hidden_alerts'] = __('');

    //For BC for 3.0 (hoping to remove for WPEC 3.9)
    register_column_headers( 'wpsc-product_variants', $columns );

    return apply_filters( 'wpsc_variation_column_headers', $columns);
}

/**
 * wpsc_additional_column_data.
 *
 * @access public
 * @param (array) $column
 * @return void
 * @todo Need to check titles / alt tags ( I don't think thumbnails have any in this code )
 * @desc Switch function to generate columns the right way...no more UI hacking!
 * 
 */
function wpsc_additional_column_data( $column ) {
    global $post, $wpdb;

    $is_parent = ( bool )wpsc_product_has_children($post->ID);
        switch ( $column ) :

            case 'image' :

                  $attached_images = get_posts( array(
                      'post_type' => 'attachment',
                      'numberposts' => 1,
                      'post_parent' => $post->ID,
                      'orderby' => 'menu_order',
                      'order' => 'ASC'
		    ) );

                if( isset( $post->ID ) && has_post_thumbnail( $post->ID ) )
                    echo get_the_post_thumbnail( $post->ID, 'admin-product-thumbnails' );
                else if( !empty( $attached_images  ) ) {
                    $attached_image = $attached_images[0];
                    $src = wp_get_attachment_url( $attached_image->ID );
                 ?>
                    <div style='width:38px; height:38px; overflow:hidden;'>
                        <img title='<?php _e( 'Drag to a new position' ); ?>' src='<?php echo $src; ?>' alt='<?php echo $title; ?>' width='38' height='38' />
                    </div>
                <?php
		     } else {
		      	$image_url = WPSC_CORE_IMAGES_URL . "/no-image-uploaded.gif";
                ?>
                      <img title='<?php _e( 'Drag to a new position' ); ?>' src='<?php echo $image_url; ?>' alt='<?php echo $title; ?>' width='38' height='38' />
                <?php
                     }
                break;
            case 'weight' :

                if( $is_parent ) :
                    _e( 'N/A', 'wpsc' );
				else :
                    $product_data['meta'] = array();
                    $product_data['meta'] = get_post_meta( $post->ID, '' );
                    foreach( $product_data['meta'] as $meta_name => $meta_value )
                        $product_data['meta'][$meta_name] = maybe_unserialize( array_pop( $meta_value ) );

                    $product_data['transformed'] = array();
                    if( !isset( $product_data['meta']['_wpsc_product_metadata']['weight'] ) )
                        $product_data['meta']['_wpsc_product_metadata']['weight'] = "";
                    if( !isset( $product_data['meta']['_wpsc_product_metadata']['weight_unit'] ) )
                        $product_data['meta']['_wpsc_product_metadata']['weight_unit'] = "";

                    $product_data['transformed']['weight'] = wpsc_convert_weight( $product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['weight_unit'] );

                    $weight = $product_data['transformed']['weight'];
                    if( $weight == '' )
                        $weight = '0';
			
			$unit = $product_data['meta']['_wpsc_product_metadata']['weight_unit'];

			switch( $unit ) {
				case "pound":
					$unit = " lbs.";
					break;
				case "ounce":
					$unit = " oz.";
					break;
				case "gram":
					$unit = " g";
					break;
				case "kilograms":
				case "kilogram":
					$unit = " kgs.";
					break;
			}
                        echo $weight.$unit;
                        echo '<div id="inline_' . $post->ID . '_weight" class="hidden">' . $weight . '</div>';

                  endif;
                break;
            case 'stock' :
                $stock = get_post_meta( $post->ID, '_wpsc_stock', true );
                    if( $stock == '' )
                        $stock = 'N/A';
                    if( !$is_parent ) {
                        echo $stock;
                        echo '<div id="inline_' . $post->ID . '_stock" class="hidden">' . $stock . '</div>';
                    }
                    else
                        echo '~'.wpsc_variations_stock_remaining( $post->ID );
                 break;
            case 'price' :
                $price = get_post_meta( $post->ID, '_wpsc_price', true );
                if( !$is_parent ) {
                    echo wpsc_currency_display( $price );
                    echo '<div id="inline_' . $post->ID . '_price" class="hidden">' . $price . '</div>';
                }
                else
                    echo wpsc_product_variation_price_available( $post->ID ).'+';
                break;
            case 'sale_price' :
                $price = get_post_meta( $post->ID, '_wpsc_special_price', true );
                if( !$is_parent ) {
                    echo wpsc_currency_display( $price );
                    echo '<div id="inline_' . $post->ID . '_sale_price" class="hidden">' . $price . '</div>';
                } else
                    echo wpsc_product_variation_price_available( $post->ID ).'+';
                break;
            case 'SKU' :
                $sku = get_post_meta( $post->ID, '_wpsc_sku', true );
                    if( $sku == '' )
                        $sku = 'N/A';

                    echo $sku;
                    echo '<div id="inline_' . $post->ID . '_sku" class="hidden">' . $sku . '</div>';
               break;
            case 'cats' :
                $categories = get_the_product_category( $post->ID );
                    if ( !empty( $categories ) ) {
                        $out = array();
                        foreach ( $categories as $c )
                            $out[] = "<a href='?post_type=wpsc-product&amp;wpsc_product_category={$c->slug}'> " . esc_html( sanitize_term_field( 'name', $c->name, $c->term_id, 'category', 'display' ) ) . "</a>";
                            echo join( ', ', $out );
			} else {
                            _e('Uncategorized');
			}
                break;
            case 'featured' :
                $featured_product_url = wp_nonce_url( "index.php?wpsc_admin_action=update_featured_product&amp;product_id=$post->ID", 'feature_product_' . $post->ID);
?>
	<a class="wpsc_featured_product_toggle featured_toggle_<?php echo $post->ID; ?>" href='<?php echo $featured_product_url; ?>' >
            <?php if ( in_array( $post->ID, (array)get_option( 'sticky_products' ) ) ) : ?>
                <img class='gold-star' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/gold-star.gif' alt='<?php _e( 'Unmark as Featured', 'wpsc' ); ?>' title='<?php _e( 'Unmark as Featured', 'wpsc' ); ?>' />
            <?php else: ?>
                <img class='grey-star' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/grey-star.gif' alt='<?php _e( 'Mark as Featured', 'wpsc' ); ?>' title='<?php _e( 'Mark as Featured', 'wpsc' ); ?>' />
            <?php endif; ?>
	</a>
        <?php
                break;
            case 'hidden_alerts' :
                $product_alert = apply_filters( 'wpsc_product_alert', array( false, '' ), $post );
                    if( !empty( $product_alert['messages'] ) )
                        $product_alert['messages'] = implode( "\n",( array )$product_alert['messages'] );

                    if( $product_alert['state'] === true ) {
                        ?>
                            <img alt='<?php echo $product_alert['messages'];?>' title='<?php echo $product_alert['messages'];?>' class='product-alert-image' src='<?php echo  WPSC_CORE_IMAGES_URL;?>/product-alert.jpg' alt='' />
                        <?php
                    }

                    // If a product alert has stuff to display, show it.
                    // Can be used to add extra icons etc
                    if ( !empty( $product_alert['display'] ) )
                        echo $product_alert['display'];
                break;
        endswitch;

}
function wpsc_column_sql_orderby( $orderby, $wp_query ) {
	global $wpdb;

	$wp_query->query = wp_parse_args( $wp_query->query );

          switch ( $wp_query->query['orderby'] ) :
            case 'stock' :
                $orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = '_wpsc_stock') " . $wp_query->get('order');
                break;
            case 'price' :
                $orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = '_wpsc_price') " . $wp_query->get('order');
                break;
            case 'sale_price' :
                $orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = '_wpsc_special_price') " . $wp_query->get('order');
                break;
            case 'SKU' :
                $orderby = "(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = '_wpsc_sku') " . $wp_query->get('order');
                break;
        endswitch;
        
	return $orderby;
}
function wpsc_cats_restrict_manage_posts() {
    global $typenow;

    if ( $typenow == 'wpsc-product' ) {

        $filters = array( 'wpsc_product_category' );
        
        foreach ( $filters as $tax_slug ) {
            // retrieve the taxonomy object
            $tax_obj = get_taxonomy( $tax_slug );
            $tax_name = $tax_obj->labels->name;
            // retrieve array of term objects per taxonomy
            $terms = get_terms( $tax_slug );

            // output html for taxonomy dropdown filter
            echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
            echo "<option value=''>Show All $tax_name</option>";
            foreach ( $terms as $term ) 
                echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
            echo "</select>";
        }
    }
}

/**
 * wpsc no minors allowed
 * Restrict the products page to showing only parent products and not variations.
 * @since 3.8
 */
function wpsc_no_minors_allowed( $vars ) {
    global $current_screen;
    
    if( $current_screen->post_type != 'wpsc-product' )
        return $vars;

    $vars['post_parent'] = 0;
    
    return $vars;
}

add_filter( 'request', 'wpsc_no_minors_allowed' );
add_action( 'admin_head', 'wpsc_additional_column_name_variations' );
add_action( 'restrict_manage_posts', 'wpsc_cats_restrict_manage_posts' );
add_action( 'manage_pages_custom_column', 'wpsc_additional_column_data', 10, 2 );
add_filter( 'manage_edit-wpsc-product_sortable_columns', 'wpsc_additional_sortable_column_names' );
add_filter( 'manage_edit-wpsc-product_columns', 'wpsc_additional_column_names' );
add_filter( 'posts_orderby', 'wpsc_column_sql_orderby', 10, 2 );


/**
 * wpsc_update_featured_products function.
 *
 * @access public
 * @todo Should be refactored to e
 * @return void
 */
function wpsc_update_featured_products() {
	global $wpdb;
	$is_ajax = (int)(bool)$_POST['ajax'];
	$product_id = absint( $_GET['product_id'] );
	check_admin_referer( 'feature_product_' . $product_id );
	$status = get_option( 'sticky_products' );

	$new_status = (in_array( $product_id, $status )) ? false : true;

	if ( $new_status ) {

		$status[] = $product_id;
	} else {
		$status = array_diff( $status, array( $product_id ) );
		$status = array_values( $status );
	}
	update_option( 'sticky_products', $status );

	if ( $is_ajax == true ) {
		if ( $new_status == true ) : ?>
                    jQuery('.featured_toggle_<?php echo $product_id; ?>').html("<img class='gold-star' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/gold-star.gif' alt='<?php _e( 'Unmark as Featured', 'wpsc' ); ?>' title='<?php _e( 'Unmark as Featured', 'wpsc' ); ?>' />");
            <?php else: ?>
                    jQuery('.featured_toggle_<?php echo $product_id; ?>').html("<img class='grey-star' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/grey-star.gif' alt='<?php _e( 'Mark as Featured', 'wpsc' ); ?>' title='<?php _e( 'Mark as Featured', 'wpsc' ); ?>' />");
<?php
		endif;
		exit();
	}
	wp_redirect( wp_get_referer() );
	exit();
}

if ( isset( $_REQUEST['wpsc_admin_action'] ) && ($_REQUEST['wpsc_admin_action'] == 'update_featured_product') ) {
	add_action( 'admin_init', 'wpsc_update_featured_products' );
}
