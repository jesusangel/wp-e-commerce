<?php
/**
 * WPSC Product form generation functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

global $wpsc_product_defaults;
$wpsc_product_defaults = array(
	'id' => '0',
	'name' => '',
	'description' => '',
	'additional_description' => '',
	'price' => '0.00',
	'weight' => '0',
	'weight_unit' => 'pound',
	'pnp' => '0.00',
	'international_pnp' => '0.00',
	'file' => '0',
	'image' => '',
	'category' => '0',
	'brand' => '0',
	'quantity_limited' => '0',
	'quantity' => '0',
	'special' => '0',
	'special_price' => 0.00,
	'display_frontpage' => '0',
	'notax' => '0',
	'publish' => '1',
	'active' => '1',
	'donation' => '0',
	'no_shipping' => '0',
	'thumbnail_image' => '',
	'thumbnail_state' => '1',
	'meta' =>
	array(
		'external_link' => NULL,
		'external_link_text' => NULL,
		'external_link_target' => NULL,
		'merchant_notes' => NULL,
		'sku' => NULL,
		'engrave' => '0',
		'can_have_uploaded_image' => '0',
		'table_rate_price' =>
		array(
			'quantity' =>
			array(
				0 => '',
			),
			'table_price' =>
			array(
				0 => '',
			),
		),
	),
);
// Justin Sainton - 5.8.2010 - Adding this function for backwards_compatible array_replace

if ( !function_exists( 'array_replace_recursive' ) ) {

	function array_replace_recursive( $array, $array1 ) {

		function recurse( $array, $array1 ) {
			foreach ( $array1 as $key => $value ) {
				// create new key in $array, if it is empty or not an array
				if ( !isset( $array[$key] ) || (isset( $array[$key] ) && !is_array( $array[$key] )) ) {
					$array[$key] = array( );
				}

				// overwrite the value in the base array
				if ( is_array( $value ) ) {
					$value = recurse( $array[$key], $value );
				}
				$array[$key] = $value;
			}
			return $array;
		}

		// handle the arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if ( !is_array( $array ) ) {
			return $array;
		}
		for ( $i = 1; $i < count( $args ); $i++ ) {
			if ( is_array( $args[$i] ) ) {
				$array = recurse( $array, $args[$i] );
			}
		}
		return $array;
	}

}

function wpsc_populate_product_data( $product_id, $wpsc_product_defaults ) {
	global $wpdb, $product;

	$tt_ids = array( );
	$term_ids = array( );
	$product = get_post( $product_id );

	$product_data['id'] = $product->ID;
	$product_data['name'] = $product->post_title;
	$product_data['post_type'] = $product->post_type;
	$product_data['post_status'] = $product->post_status;
	$product_data['description'] = $product->post_content;
	$product_data['additional_description'] = $product->post_excerpt;
	// get the list of categories this Product is associated with

	$product_data['categories'] = wp_get_product_categories( $product->ID );
	$product_data['tags'] = wp_get_product_tags( $product->ID );
	$product_data['category_ids'] = array( );

	$product_data['product_object'] = $product;

	foreach ( (array)$product_data['categories'] as $category_item ) {
		$product_data['category_ids'][] = (int)$category_item->term_id;
	}


	// Meta Values come straight from the meta table
	$product_data['meta'] = array( );
	$product_data['meta'] = get_post_meta( $product->ID, '' );
	if ( is_array( $product_data['meta'] ) ) {
		foreach ( $product_data['meta'] as $meta_name => $meta_value ) {
			$product_data['meta'][$meta_name] = maybe_unserialize( array_pop( $meta_value ) );
		}
	}
	$product_data['dimensions'] = get_product_meta( $product_id, 'dimensions', true );

	// Transformed Values have been altered in some way since being extracted from some data source
	$product_data['transformed'] = array( );
	$product_data['transformed']['weight'] = wpsc_convert_weight( $product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['display_weight_as'] );

	if ( function_exists( 'wp_insert_term' ) ) {
		$term_relationships = $wpdb->get_results( "SELECT * FROM `{$wpdb->term_relationships}` WHERE object_id = '{$product_data['id']}'", ARRAY_A );

		foreach ( (array)$term_relationships as $term_relationship ) {
			$tt_ids[] = $term_relationship['term_taxonomy_id'];
		}
		foreach ( (array)$tt_ids as $tt_id ) {
			$term_ids[] = $wpdb->get_var( "SELECT `term_id` FROM `{$wpdb->term_taxonomy}` WHERE `term_taxonomy_id` = '{$tt_id}' AND `taxonomy` = 'product_tag' LIMIT 1" );
		}
		foreach ( (array)$term_ids as $term_id ) {
			if ( $term_id != NULL ) {
				$tags[] = $wpdb->get_var( "SELECT `name` FROM `{$wpdb->terms}` WHERE `term_id`='{$term_id}' LIMIT 1" );
			}
		}
		if ( isset( $tags ) ) {
			$imtags = implode( ',', $tags );
		}
	}
	return $product_data;
}

function wpsc_display_product_form( $product_id = 0 ) {
	global $wpdb, $wpsc_product_defaults;
	$product_id = absint( $product_id );

	if ( $product_id > 0 ) {
		$product_data = wpsc_populate_product_data( $product_id, $wpsc_product_defaults );
	} else {
		if ( isset( $_SESSION['wpsc_failed_product_post_data'] ) && (count( $_SESSION['wpsc_failed_product_post_data'] ) > 0 ) ) {
			$product_data = array_merge( $wpsc_product_defaults, $_SESSION['wpsc_failed_product_post_data'] );
			$_SESSION['wpsc_failed_product_post_data'] = null;
		} else {
			$product_data = $wpsc_product_defaults;
		}
	}

	$current_user = wp_get_current_user();

	// we put the closed postboxes array into the Product data to propagate it to each form without having it global.
	$product_data['closed_postboxes'] = (array)get_user_meta( $current_user->ID, 'closedpostboxes_products_page_wpsc-edit-products' );
	$product_data['hidden_postboxes'] = (array)get_user_meta( $current_user->ID, 'metaboxhidden_products_page_wpsc-edit-products' );
	if ( count( $product_data ) > 0 ) {
		wpsc_product_basic_details_form( $product_data );
		
	}
	
}

/*
  Stop-gap function replicating native WP functionality
 */

function add_new_product_id() {
	global $wpdb;
	$prod_id = $wpdb->get_var( $wpdb->prepare( "SELECT MAX( ID ) FROM  $wpdb->posts" ) );
	$prod_id = $prod_id + 1;

	$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->posts ( post_author, post_date, post_date_gmt, post_title, post_status, post_parent, post_type)
   VALUES ( %d, %s, %s, %s, %s, %d, %s )",
					1, 'CURDATE()', 'CURDATE()', 'stopgap', 'inherit', $prod_id, 'attachment' ) );

	return $prod_id;
}

function delete_stopgap() {
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->posts WHERE post_parent = $uploading_iframe_ID AND post_title = 'stopgap'" );
}

if ( isset( $product_data['id'] ) && $product_data['id'] > 0 ) {
	add_action( 'transition_post_status', 'delete_stopgap' );
}

function wpsc_product_basic_details_form( &$product_data ) {
	global $wpdb, $nzshpcrt_imagesize_info, $user_ID;

	if ( !isset( $product_data['product_object'] ) )
		$product_data['product_object'] = new stdClass();
	$product = $product_data['product_object'];
	$post_ID = (int)$product_data["id"];
?>
	<h3 class='form_heading' style="display:none;">
<?php
	
	if ( !isset( $_GET["product"] ) || $_GET["product"] <= 0 ) 
		$product_data["id"] = $_GET["product"] = add_new_product_id();
	
	$form_extra = '';
	if ( !isset( $product->post_status ) )
		$product->post_status = '';
	if ( 'auto-draft' == $product->post_status ) {
		if ( 'edit' == $action )
			$product->post_title = '';
		$autosave = false;
		$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
	} else {
		$autosave = wp_get_post_autosave( $product_data["id"] );
	}
	if ( !isset( $product->post_type ) )
		$product->post_type = '';
	$nonce_action = 'update-' . $product->post_type . '_' . $product_data["id"];
	$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr( $product_data["id"] ) . "' />";
?>
	</h3>
	<div id="side-info-column" class="inner-sidebar">
		<div id="side-sortables" class='meta-box-sortables-wpec ui-sortable'>
<?php
	wp_nonce_field( $nonce_action );
	if ( !isset( $form_action ) )
		$form_action = '';
	if ( !isset( $product->post_author ) )
		$product->post_author = '';
	if ( !isset( $product->post_type ) )
		$product->post_type = '';
	if ( !isset( $product->post_status ) )
		$product->post_status = '';
?>
			<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int)$user_ID ?>" />
			<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ) ?>" />
			<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ) ?>" />
			<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $product->post_author ); ?>" />
			<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $product->post_type ) ?>" />
			<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $product->post_status ) ?>" />
			<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url( stripslashes( wp_get_referer() ) ); ?>" />
<?php
		if ( 'draft' != $product->post_status )
			wp_original_referer_field( true, 'previous' );

		echo $form_extra;

		wp_nonce_field( 'autosave', 'autosavenonce', false );
?>
			<input type='hidden' name='product_id' id='product_id' value='<?php echo (int)$product_data["id"]; ?>' />
			<input type='hidden' name='wpsc_admin_action' value='edit_product' />
		<?php wp_nonce_field( 'edit-product', 'wpsc-edit-product' ); ?>
			<div id="submitdiv" class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle"><span>Publish</span></h3>
				<div class="inside publish">
					<div class="submitbox" id="submitpost"><br />
						<div id="minor-publishing">
							<div id="minor-publishing-actions">
								<div id="save-action">
								<?php if ( ($product->post_status == 'draft') || ($product->post_status == null) ) : ?>
									<input type='submit' value='<?php _e( 'Save Draft', 'wpsc' ); ?>' class='button button-highlighted' id="save-post" name='save' />
								<?php else : ?>
									<input type='submit' value='<?php _e( 'Unpublish', 'wpsc' ); ?>' class='button button-highlighted' id='save-post' name='unpublish' />
								<?php endif; ?>
							</div>
							<div id="preview-action">
								<a class="preview button" target='_blank' href="<?php echo wpsc_product_url( $product->ID ); ?>"><?php _e( 'View Product' ) ?></a>
							</div>
							<div class="clear"></div>
						</div>
					</div>
					<div id="major-publishing-actions">
						<div id="delete-action">
							<a class='submitdelete deletion' title='<?php echo esc_attr( __( 'Delete this Product' ) ); ?>' href='<?php echo wp_nonce_url( admin_url("admin.php?wpsc_admin_action=trash&amp;product={$product_data['id']}&product_parent"), 'delete_product_' . $product_data['id'] ); ?>' onclick="if ( confirm(' <?php echo esc_js( sprintf( __( "You are about to delete this Product '%s'\n 'Cancel' to stop, 'OK' to delete." ), $product_data['name'] ) ) ?>') ) { return true;}return false;"><?php _e( 'Move to Trash' ) ?>
							</a><br />
							</div>
							<div id="publishing-action">
							<?php if ( ($product->post_status == 'draft') || ($product->post_status == null) ) : ?>
									<input type='submit' value='<?php _e( 'Publish', 'wpsc' ); ?>' id='publish' class='button-primary' name='publish' />
							<?php else : ?>
									<input type='submit' value='<?php _e( 'Update', 'wpsc' ); ?>' id='publish' class='button-primary' name='save' />
							<?php endif; ?>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
<?php
			$default_order = array(
				"advanced" => array(
					"wpsc_product_shipping_forms",
					"wpsc_product_variation_forms",
					"wpsc_product_external_link_forms",
					"wpsc_product_advanced_forms"
				),
				"side" => array(
					"wpsc_product_category_and_tag_forms",
					"wpsc_product_tag_forms",
					"wpsc_price_control_forms",
					"wpsc_stock_control_forms",
					"wpsc_product_taxes_forms",
					"wpsc_product_image_forms",
					"wpsc_product_download_forms"
				),
				"closedboxes" => array(
					"wpsc_product_shipping_forms" => 1,
					"wpsc_product_tag_forms" => 1,
					"wpsc_product_variation_forms" => 1,
					"wpsc_product_external_link_forms" => 1,
					"wpsc_product_advanced_forms" => 1,
					"wpsc_product_category_and_tag_forms" => 1,
					"wpsc_price_control_forms" => 1,
					"wpsc_stock_control_forms" => 1,
					"wpsc_product_taxes_forms" => 1,
					"wpsc_product_image_forms" => 1,
					"wpsc_product_download_forms" => 1
				),
				"hiddenboxes" => array(
					"wpsc_product_shipping_forms" => 1,
					"wpsc_product_tag_forms" => 1,
					"wpsc_product_variation_forms" => 1,
					"wpsc_product_external_link_forms" => 1,
					"wpsc_product_advanced_forms" => 1,
					"wpsc_product_category_and_tag_forms" => 1,
					"wpsc_price_control_forms" => 1,
					"wpsc_stock_control_forms" => 1,
					"wpsc_product_taxes_forms" => 1,
					"wpsc_product_image_forms" => 1,
					"wpsc_product_download_forms" => 1
				)
			);

			$order = get_option( 'wpsc_product_page_order' );
			$order = apply_filters( 'wpsc_products_page_forms', $order );
			if ( ( $order == '' ) || ( count( $order, COUNT_RECURSIVE ) < 32 ) || ( count( $order ) == count( $order, COUNT_RECURSIVE ) ) ) {
				$order = $default_order;
			}
			foreach ( $order as $key => $values ) {
				$check_missing_items = array_diff( $default_order[$key], $values );

				if ( count( $check_missing_items ) > 0 ) {
					$order[$key] = array_merge( $check_missing_items, $order[$key] );
				}
			}
			$check_missing_items = array_diff( $default_order, $order );

			if ( count( $check_missing_items ) > 0 ) {
				$order = array_merge( $check_missing_items, $order );
			}

			update_option( 'wpsc_product_page_order', $order );

	        // if this is a child Product, we need to filter out the variations box here
	        if(isset($product_data['product_object']->post_parent) && $product_data['product_object']->post_parent > 0) {
	          foreach($order as $key => $values){
		          $variation_box_key = array_search('wpsc_product_variation_forms', $values);	  
			      if(count($variation_box_key) > 0) 
			         unset($order[$key][$variation_box_key]);
		      	  
	         	  $category_box_key = array_search('wpsc_product_category_and_tag_forms', $values);
		          if(is_numeric($category_box_key) && isset($order[$key][$category_box_key])) 
	             	unset($order[$key][$category_box_key]);
		      }
	
	        }

			foreach ( (array)$order["side"] as $key => $box_function_name ) {
				if ( function_exists( $box_function_name ) ) {
					echo call_user_func( $box_function_name, $product_data );
				}
			}

?>
		</div>
	</div>
	<script type="text/javascript">
		var makeSlugeditClickable;
		makeSlugeditClickable = null;
		//<![CDATA[
		jQuery(document).ready( function($) {
<?php
			$closed_boxes = $order["closedboxes"];
			foreach ( $closed_boxes as $key => $val ) {
				if ( $val == 0 ) {
?>
							 $('div#<?php echo $key; ?>').addClass('closed');
<?php
				}
			}
?>
			   $('#poststuff .postbox h3, .postbox div.handlediv').click( function() {
				   $(this).parent().toggleClass('closed');
				   wpsc_save_postboxes_state('toplevel_page_wpsc-edit-products', '#poststuff');
			   });

<?php
			$hidden_boxes = $order["hiddenboxes"];
			foreach ( $hidden_boxes as $key => $val ) {
				if ( $val == 0 ) {
?>
							 $('div#<?php echo $key; ?>').css('display', 'none');
							 $('div.metabox-prefs label input[value=<?php echo $key; ?>]').attr('checked', false);
<?php
				} elseif ( $val == 1 ) {
?>
								$('div.metabox-prefs label input[value=<?php echo $key; ?>]').attr('checked', true);

<?php
				}
			}
?>

			   $('div.variation input, div.variation_set input').click(function(){
				   $('a.update_variations_action').show();
			   });
		   });
		   //]]>
	</script>
<?php
	if ( isset( $_GET["product_parent"] ) && ($_GET["product_parent"] != '') ) {
		$parent_link = add_query_arg( array( 'page' => 'wpsc-edit-products', 'action' => 'wpsc_add_edit', 'product' => $_GET["product_parent"] ) );
		$parent_link = wp_nonce_url( $parent_link, 'edit-product_' . $product->ID );
	}

	if ( isset( $_GET["product_parent"] ) && ($_GET["product_parent"] != '') && ($_GET["product_parent"] != $_GET["product"]) ) : ?>

	<a class="button preview update_variations" href="<?php echo $parent_link ?>">Back to Main Product</a>

<?php endif; ?>

	<!-- Start of post body -->
	<div id="post-body" class="has-sidebar">
		<div id="post-body-content">
			<table class='product_editform' >
				<tr>
					<td colspan='2' class='itemfirstcol'>

						<div class='admin_product_name'>
							<input id='title' class='wpsc_product_name text' size='15' type='text' name='post_title' value='<?php echo htmlentities( stripslashes( $product_data['name'] ), ENT_QUOTES, 'UTF-8' ); ?>' />
							<a href='#' class='shorttag_toggle'></a>
						</div>
						<div class='admin_product_shorttags'>
							<h4>
							<?php _e( 'Shortcodes', 'wpsc' ); global $wpsc_product_defaults; ?></h4>

							<dl>
								<dt><?php _e( 'Display Product Shortcode', 'wpsc' ); ?>: </dt><dd>[wpsc_products product_id='<?php echo $product_data['id']; ?>']</dd>
								<dt><?php _e( 'Buy Now Shortcode', 'wpsc' ); ?>: </dt><dd>[buy_now_button=<?php echo $product_data['id']; ?>]</dd>
								<dt><?php _e( 'Add to Cart Shortcode', 'wpsc' ); ?>: </dt><dd>[add_to_cart=<?php echo $product_data['id']; ?>]</dd>
							</dl>

							<h4><?php _e( 'Template Tags', 'wpsc' ); ?></h4>

							<dl>
								<dt><?php _e( 'Display Product Template Tag', 'wpsc' ); ?>: </dt><dd> &lt;?php echo wpsc_display_products('product_id=<?php echo $product_data['id']; ?>'); ?&gt;</dd>
								<dt><?php _e( 'Buy Now PHP', 'wpsc' ); ?>: </dt><dd>&lt;?php echo wpsc_buy_now_button(<?php echo $product_data['id']; ?>); ?&gt;</dd>
								<dt><?php _e( 'Add to Cart PHP', 'wpsc' ); ?>: </dt><dd>&lt;?php echo wpsc_add_to_cart_button(<?php echo $product_data['id']; ?>); ?&gt;</dd>
								<dt><?php _e( 'Display Product SKU', 'wpsc' ); ?>: </dt><dd>&lt;?php echo wpsc_product_sku(<?php echo $product_data['id']; ?>); ?&gt;</dd>
							</dl>

<?php if ( $product_data['id'] > 0 ) : ?>
							<p><a href="<?php echo wpsc_product_url( $product_data['id'] ); ?>" target="_blank" class="button">View Products</a></p>
<?php endif; ?>

							</div>
							<div style='clear:both; height: 0px; margin-bottom: 15px;'></div>
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea" >
	
								<?php wpsc_the_editor( $product_data['description'], 'content', true, true ); ?>

								<table id="post-status-info" cellspacing="0">
									<tbody>
										<tr>
											<td id="wp-word-count"></td>
											<td class="autosave-info">
												<span id="autosave">&nbsp;</span>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div id="advanced-sortables" class="meta-box-sortables-wpec ui-sortable">
				<div class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class='hndle'><?php _e( 'Additional Description', 'wpsc' ); ?></h3>
					<div class="inside">

						<textarea name='additional_description' id='additional_description' cols='40' rows='5' ><?php echo stripslashes( $product_data['additional_description'] ); ?></textarea>
					</div>
				</div>
<?php
				foreach ( (array)$order["advanced"] as $key => $box_function_name ) {
					if ( function_exists( $box_function_name ) ) {
						echo call_user_func( $box_function_name, $product_data );
					}
				}
?>
			</div>
		</div>
<?php
	do_meta_boxes('wpsc-product', 'side', $product_data);
}

function wpsc_product_tag_forms( $product_data='' ) {
	global $closed_postboxes, $wpdb, $variations_processor;
	$output = '';
	$tag_array = array( );

	if ( !isset( $product_data['tags'] ) )
		$product_data['tags'] = array( );

	foreach ( (array)$product_data['tags'] as $tag )
		$tag_array[] = $tag->name;

	if ( $product_data == 'empty' )
		$display = "style='visibility:hidden;'";

	$output .= "<div id='wpsc_product_tag_forms' class=' postbox " . ((array_search( 'wpsc_product_tag_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : '') . "'><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

	if ( IS_WP27 )
		$output .= "<h3 class='hndle'>";
	else
		$output .= "<h3><a class='togbox'>+</a>";

	$output .= __( 'Product Tags', 'wpsc' );

	$output .= "</h3>
	    <div class='inside'>";
	$output .= "
			<p id='jaxtag'>
			   <label for='tags-input' class='hidden'>" . __( 'Product Tags', 'wpsc' ) . "</label>
			   <input type='text' value='" . implode( ',', $tag_array ) . "' tabindex='3' size='20' id='tags-input' class='tags-input' name='product_tags'/>
			<span class='howto'>" . __( 'Separate tags with commas' ) . "</span>
			</p>
			<div id='tagchecklist' class='tagchecklist' onload='tag_update_quickclicks();'></div>";
	$output .= "
			</div>
		</div>";

	$output = apply_filters( 'wpsc_product_tag_forms_output', $output );

	return $output;
}

function wpsc_product_category_and_tag_forms( $product_data='' ) {
	global $closed_postboxes, $wpdb, $variations_processor;

	$output = '';
	$tag_array = array();

	if ( !isset( $product_data['tags'] ) )
		$product_data['tags'] = array( );

	foreach ( (array)$product_data['tags'] as $tag )
		$tag_array[] = $tag->name;

	if ( $product_data == 'empty' )
		$display = "style='visibility:hidden;'";

	$output .= "<div id='wpsc_product_category_and_tag_forms' class=' postbox " . ((array_search( 'wpsc_product_category_and_tag_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : '') . "'><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

	if ( IS_WP27 )
		$output .= "<h3 class='hndle'>";
	else
		$output .= "<h3><a class='togbox'>+</a>";

	$output .= __( 'Categories', 'wpsc' );

	$output .= "</h3>
    <div class='inside'>";

	$output .= "<div id='categorydiv' >";

	$search_sql = apply_filters( 'wpsc_product_category_and_tag_forms_group_search_sql', '' );

	$output .= wpsc_category_list( $product_data, 0, $product_data['id'], 'edit_' );

	$output .= "</div>";

	$output .= "</div>
	</div>";

	$output = apply_filters( 'wpsc_product_category_and_tag_forms_output', $output );

	return $output;
}
function wpsc_price_control_forms($product_data){
	global $closed_postboxes, $wpdb, $variations_processor, $wpsc_product_defaults;
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];
	if( isset( $product_data['meta']['_wpsc_currency']))
		$product_alt_currency = $product_data['meta']['_wpsc_currency'];
		
	if ( !isset( $product_data['meta']['_wpsc_table_rate_price'] ) )
		$product_data['meta']['_wpsc_table_rate_price'] = $wpsc_product_defaults['meta']['table_rate_price'];
	$table_rate_price = $product_data['meta']['_wpsc_table_rate_price'];
	
	if ( !isset( $product_data['meta']['_wpsc_is_donation'] ) )
		$product_data['meta']['_wpsc_is_donation'] = $wpsc_product_defaults['donation'];
	
	if ( !isset( $product_meta['table_rate_price']['state'] ) )
		$product_meta['table_rate_price']['state'] = null;
	
	if ( !isset( $product_meta['table_rate_price']['quantity'] ) )
		$product_meta['table_rate_price']['quantity'] = $wpsc_product_defaults['meta']['table_rate_price']['quantity'][0];
	
	if ( !isset( $product_data['meta']['_wpsc_price'] ) )
		$product_data['meta']['_wpsc_price'] = $wpsc_product_defaults['price'];
	
	if ( !isset( $product_data['special'] ) )
		$product_data['special'] = $wpsc_product_defaults['special'];
	
	if ( !isset( $product_data['meta']['_wpsc_special_price'] ) )
		$product_data['meta']['_wpsc_special_price'] = $wpsc_product_defaults['special_price'];	
		
	$currency_data = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CURRENCY_LIST . "` ORDER BY `country` ASC", ARRAY_A );	
	?>
	
	<div id='wpsc_price_control_forms' class='wpsc_price_control_forms postbox <?php if(array_search( 'wpsc_price_control_forms', $product_data['closed_postboxes'] ) !== false) echo 'closed'; else echo ''; ?>' <?php if(array_search( 'wpsc_price_control_forms', $product_data['hidden_postboxes'] ) !== false) echo 'style="display: none;"'; else echo '';?> ><div class="handlediv" title="Click to toggle"><br></div>

	<h3 class='hndle'><?php _e( 'Price Control', 'wpsc' ); ?></h3>
    <div class="inside">
    	<?php /* Check product if a product has variations */ ?>
    	<?php if( wpsc_product_has_children($product_data['id']) ) : ?>
    		<?php $price = wpsc_product_variation_price_available($product_data['id']); ?>
			<p><?php printf( __( 'This Product has variations, to edit the price please use the %1s Variation Controls %2s below.' , 'wpsc' ), '<a href="#variation_control">','</a>' ); ?></p>
			<p><?php printf( __( 'Price: %s and above.' ,'wpsc' ) , $price ); ?></p>
		<?php else: ?>
		
    	<div class='wpsc_floatleft' style="width:85px;">
    		<label><?php _e( 'Price', 'wpsc' ); ?>:</label><br />
			<input type='text' class='text' size='10' name='meta[_wpsc_price]' value='<?php echo number_format( $product_data['meta']['_wpsc_price'],2,'.','' ); ?>' />
		</div>
		<div class='wpsc_floatleft' style='display:<?php if ( ($product_data['special'] == 1) ? 'block' : 'none'
						); ?>; width:85px; margin-left:30px;'>
			<label for='add_form_special'><?php _e( 'Sale Price', 'wpsc' ); ?>:</label>
			<div id='add_special'>
				<input type='text' size='10' value='<?php echo number_format( $product_data['meta']['_wpsc_special_price'], 2,'.','' ); ?>' name='meta[_wpsc_special_price]' />
			</div>
		</div>
		<br style="clear:both" />
		<br style="clear:both" />
		<a href='#' class='wpsc_add_new_currency'>+ <?php _e( 'New Currency', 'wpsc' ); ?></a>
		<br />
		<!-- add new currency layer -->
		<div class='new_layer'>
			<label for='newCurrency[]'><?php _e( 'Currency type', 'wpsc' ); ?>:</label><br />
			<select name='newCurrency[]' class='newCurrency' style='width:42%'>
			<?php
				foreach ( (array)$currency_data as $currency ) {?>
					<option value='<?php echo $currency['id']; ?>' >
						<?php echo htmlspecialchars( $currency['country'] ); ?> (<?php echo $currency['currency']; ?>)
					</option> <?php
				} ?>
			</select>
			<?php _e( 'Price', 'wpsc' ); ?> : 
			<input type='text' class='text' size='8' name='newCurrPrice[]' value='0.00' style='display:inline' />
			<a href='' class='deletelayer' rel='<?php echo $isocode; ?>'><img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png' /></a>

		</div> <!-- close new_layer -->
<?php
	if ( isset( $product_alt_currency ) && is_array($product_alt_currency)) :
		$i = 0;
		foreach ($product_alt_currency as $iso => $alt_price ) {
			$i++; ?>
			<br /><label for='newCurrency[]'><?php _e( 'Currency type', 'wpsc' ); ?>:</label><br />
			<select name='newCurrency[]' class='newCurrency' style='width:42%'> <?php
				foreach ( $currency_data as $currency ) {
					if ( $iso == $currency['isocode'] )
						$selected = "selected='selected'";
					else
						$selected = ""; ?>
					<option value='<?php echo $currency['id']; ?>' <?php echo $selected; ?> >
						<?php echo htmlspecialchars( $currency['country'] ); ?> (<?php echo $currency['currency']; ?>)
					</option> <?php
				} ?>
			</select>
			<?php _e( 'Price', 'wpsc' ); ?>:  <input type='text' class='text' size='8' name='newCurrPrice[]' value='<?php echo $alt_price; ?>' style=' display:inline' />
			<a href='' class='wpsc_delete_currency_layer' rel='<?php echo $iso; ?>'><img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png' /></a><br />
<?php	}

		endif;

		echo "<br style='clear:both' />
          <br/><input id='add_form_donation' type='checkbox' name='meta[_wpsc_is_donation]' value='yes' " . (($product_data['meta']['_wpsc_is_donation'] == 1) ? 'checked="checked"' : '') . " />&nbsp;<label for='add_form_donation'>" . __( 'This is a donation, checking this box populates the donations widget.', 'wpsc' ) . "</label>";
?>
				<br /><br /> <input type='checkbox' value='1' name='table_rate_price[state]' id='table_rate_price'  <?php echo (((bool)$product_meta['table_rate_price']['state'] == true) ? 'checked=\'checked\'' : ''); ?> />
				<label for='table_rate_price'><?php _e( 'Table Rate Price', 'wpsc' ); ?></label>
				<div id='table_rate'>
					<a class='add_level' style='cursor:pointer;'>+ Add level</a><br />
					<br style='clear:both' />
					<table>
						<tr>
							<th><?php _e( 'Quantity In Cart', 'wpsc' ); ?></th>
							<th colspan='2'><?php _e( 'Discounted Price', 'wpsc' ); ?></th>
						</tr>
<?php
		if ( count( $product_meta['table_rate_price']['quantity'] ) > 0 ) {
			foreach ( (array)$product_meta['table_rate_price']['quantity'] as $key => $quantity ) {
				if ( $quantity != '' ) {
					$table_price = number_format( $product_meta['table_rate_price']['table_price'][$key], 2, '.', '' );
?>
						<tr>
							<td>
								<input type="text" size="5" value="<?php echo $quantity; ?>" name="table_rate_price[quantity][]"/><span class='description'>and above</span> 
							</td>
							<td>
								<input type="text" size="10" value="<?php echo $table_price; ?>" name="table_rate_price[table_price][]" />
							</td>
							<td><img src="<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png" class="remove_line" /></td>
						</tr>
<?php
					}
				}
			}
?>
						<tr>
							<td><input type="text" size="5" value="<?php echo $quantity; ?>" name="table_rate_price[quantity][]"/><span class='description'>and above</span> </td>
							<td><input type='text' size='10' value='' name='table_rate_price[table_price][]'/></td>
						</tr>
					</table>
				</div>
				<?php endif; ?>
			</div>
			</div>
<?php
}
function wpsc_stock_control_forms( $product_data='' ) {
	global $closed_postboxes, $wpdb, $variations_processor, $wpsc_product_defaults;
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];

	if ( !isset( $product_meta['unpublish_when_none_left'] ) )
		$product_meta['unpublish_when_none_left'] = ''; ?>
		
	<div id='wpsc_stock_control_forms' class='wpsc_stock_control_forms postbox <?php  if(array_search( 'wpsc_stock_control_forms', $product_data['closed_postboxes'] ) !== false) echo 'closed'; else echo ''; ?>' <?php if(array_search( 'wpsc_stock_control_forms', $product_data['hidden_postboxes'] ) !== false) echo 'style="display: none;"'; else echo ''; ?> >
		<div class="handlediv" title="<?php _e('Click to toggle', 'wpsc'); ?>"><br /></div>
		<h3 class='hndle'><?php _e( 'Stock Control', 'wpsc' ); ?></h3>
	    <div class="inside">
				<label for="wpsc_sku"><abbr title="<?php _e( 'Stock Keeping Unit', 'wpsc' ); ?>">SKU:</abbr></label>
<?php
		if ( !isset( $product_data['meta']['_wpsc_sku'] ) )
			$product_data['meta']['_wpsc_sku'] = $wpsc_product_defaults['meta']['sku']; ?><br />
			<input size='32' type='text' class='text' id="wpsc_sku" name='meta[_wpsc_sku]' value='<?php echo htmlentities( stripslashes( $product_data['meta']['_wpsc_sku'] ), ENT_QUOTES, 'UTF-8' ); ?>' />
			<br style="clear:both" />
			<?php
			if ( !isset( $product_data['meta']['_wpsc_stock'] ) )
				$product_data['meta']['_wpsc_stock'] = ''; ?>
			<br /><input class='limited_stock_checkbox' id='add_form_quantity_limited' type='checkbox' value='yes' <?php if(is_numeric( $product_data['meta']['_wpsc_stock'] )) echo 'checked="checked"'; else echo ''; ?> name='meta[_wpsc_limited_stock]' />
			<label for='add_form_quantity_limited' class='small'><?php _e( 'I have limited stock for this Product', 'wpsc' ); ?></label>
			<?php
			if ( $product_data['id'] > 0 ){
				if ( is_numeric( $product_data['meta']['_wpsc_stock'] ) ){?>
					<div class='edit_stock' style='display: block;'> <?php 
				} else { ?>
					<div class='edit_stock' style='display: none;'><?php
				} ?>
					<?php if( wpsc_product_has_children($product_data['id']) ) : ?>
			    		<?php $stock = wpsc_variations_stock_remaining($product_data['id']); ?>
						<p><?php _e( 'This Product has variations, to edit the quantity please use the Variation Controls below.' , 'wpsc' ); ?></p>
						<p><?php printf(_n("%s variant item in stock.", "%s variant items in stock.", $stock), $stock); ?></p>
					<?php else: ?>
						<label for="stock_limit_quantity"><?php _e( 'Quantity:', 'wpsc' ); ?></label>
						<input type='text' id="stock_limit_quantity" name='meta[_wpsc_stock]' size='3' value='<?php echo $product_data['meta']['_wpsc_stock']; ?>' class='stock_limit_quantity' />
					<?php endif; ?>
						<div class='unpublish_when_none_left'>
							<input type='checkbox' id="inform_when_oos" name='meta[_wpsc_product_metadata][unpublish_when_none_left]' class='inform_when_oos'<?php if( $product_meta['unpublish_when_none_left'] == 1 ) echo ' checked="checked"'; ?> />
							<label for="inform_when_oos"><?php _e( 'Notify site owner and unpublish this Product if stock runs out', 'wpsc' ); ?></label>
						</div>
						<?php _e('If stock runs out, this Product will not be available on the shop unless you untick this box or add more stock.', 'wpsc'); ?>
				</div> <?php
			} else { ?>
				<div style='display: none;' class='edit_stock'>
					 <?php _e( 'Stock Qty', 'wpsc' ); ?><input type='text' name='meta[_wpsc_stock]' value='0' size='10' />
					<div style='font-size:9px; padding:5px;'>
						<input type='checkbox' class='inform_when_oos' name='meta[_wpsc_product_metadata][unpublish_when_none_left]' /> <?php _e( 'If this Product runs out of stock set status to Unpublished & email site owner', 'wpsc' ); ?>
					</div>
				</div><?php
			}
?>
		</div>
	</div>	
<?php	
}
function wpsc_product_taxes_forms($product_data=''){
	global $closed_postboxes, $wpdb, $variations_processor, $wpsc_product_defaults;
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];
	
	if ( !isset( $product_data['meta']['_wpsc_custom_tax'] ) )
		$product_data['meta']['_wpsc_custom_tax'] = '';
	$custom_tax = $product_data['meta']['_wpsc_custom_tax'];

	
	if ( !isset( $product_meta['custom_tax'] ) )
		$product_meta['custom_tax'] = 0.00;
	//Add New WPEC-Taxes Bands Here
	$wpec_taxes_controller = new wpec_taxes_controller();

	//display tax bands
	$band_select_settings = array(
		'id' => 'wpec_taxes_band',
		'name' => 'meta[_wpsc_product_metadata][wpec_taxes_band]',
		'label' => __( 'Custom Tax Band' )
	);
	$wpec_taxes_band = '';
	if(isset($product_meta['wpec_taxes_band']))
		$wpec_taxes_band = $product_meta['wpec_taxes_band'];
	
	echo "<div id='wpsc_product_taxes_forms' class='wpsc_product_taxes_forms postbox " . ((array_search( 'wpsc_product_taxes_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : '') . "' " . ((array_search( 'wpsc_product_taxes_forms', $product_data['hidden_postboxes'] ) !== false) ? 'style="display: none;"' : '') . " ><div class=\"handlediv\" title=\"Click to toggle\"><br></div>"; ?>
		<h3 class="hndle"><?php _e( 'Taxes' ); ?></h3>
			<div class="inside">
				<p>
					<?php echo $wpec_taxes_controller->wpec_taxes_display_tax_bands( $band_select_settings, $wpec_taxes_band ); ?>
				</p>
			</div>
		</div>
<?php				
}

function wpsc_product_variation_forms( $product_data = '' ) {
	global $closed_postboxes, $variations_processor, $wp_query;

	$siteurl = get_option( 'siteurl' );
	$output  = '';

	// Hide if there is no Product data
	if ( 'empty' == $product_data )
		$display = "style='display:none;'";

	// Get variation data from WP Terms
	$product_term_data = wp_get_object_terms( $product_data['id'], 'wpsc-variation' );
	if ( !empty( $product_term_data ) ) {
		foreach ( $product_term_data as $product_term )
			$product_terms[] = $product_term->term_id;
	} else {
		$product_terms = array();
	}

	$form_classes = array_search( 'wpsc_product_variation_forms', $product_data['closed_postboxes'] ) !== false ? 'closed' : '';
	$form_style   = array_search( 'wpsc_product_variation_forms', $product_data['hidden_postboxes'] ) !== false ? 'style="display: none;"' : ''
	?>

	<div id="wpsc_product_variation_forms" class="postbox <?php echo $form_classes; ?>" <?php echo $form_style; ?>>
		<div class="handlediv" title="Click to toggle"><br></div>
			<h3 class="hndle"><?php _e( 'Variation Control', 'wpsc' ); ?></h3>
			<div class="inside">
				<?php if( empty( $product_data['name'] ) ) : ?>
					<p><?php _e( 'You must first save this Product as a Draft before adding variations', 'wpsc' ); ?></p>
				<?php else : ?>
				<div id="product_variations">
					<div class="variation_checkboxes">
						<?php
						// Get the terms from variations
						$variation_sets = get_terms( 'wpsc-variation', array (
							'hide_empty' => 0,
							'parent'     => 0
						) );
						// Loop through each variation set
						foreach ( (array)$variation_sets as $variation_set ) :
							$set_checked_state = '';

							// If this Product includes this variation, check it
							if ( in_array( $variation_set->term_id, $product_terms ) )
								$set_checked_state = "checked='checked'";	?>
								<div class="variation_set">

									<label class='set_label'>
										<input type="checkbox" <?php echo $set_checked_state; ?> name="variations[<?php echo $variation_set->term_id; ?>]" value="1">
										<?php echo $variation_set->name; ?>
									</label>

										<?php
										$variations = get_terms( 'wpsc-variation', array (
											'hide_empty' => 0,
											'parent'     => $variation_set->term_id
										) );
										// Loop through the variations
										foreach ( (array)$variations as $variation ) :
											$checked_state = '';

											if ( in_array( $variation->term_id, $product_terms ) )
												$checked_state = "checked='checked'";

										?>

										<div class="variation">
											<label>
												<input type="checkbox" <?php echo $checked_state; ?> name="edit_var_val[<?php echo $variation_set->term_id; ?>][<?php echo $variation->term_id; ?>]" value="1">
												<?php echo $variation->name; ?>
											</label>
										</div>

										<?php endforeach; ?>

								</div>

							<?php endforeach; ?>

					</div>
					<!-- <a href='<?php echo add_query_arg( array( 'page' => 'wpsc-edit-products', 'parent_product' => $product_data['id'] ), "admin.php" ); ?>'><?php _e( 'Edit Variations Products', 'wpsc' ); ?></a> -->
					<a class="preview button update_variations_action" href='#'><?php _e( 'Update Variations &rarr;', 'wpsc' ); ?></a>

				</div>
	<?php
		$parent_product = $product_data['id'];

		$query = array(
			'post_type'   => 'wpsc-product',
			'orderby'     => 'menu_order post_title',
			'post_parent' => $parent_product,
			'post_status' => 'all',
			'order'       => "ASC"
		);

		$args = array(
			'post_type'   => 'attachment',
			'numberposts' => 1,
			'post_status' => null,
			'post_parent' => $parent_product,
			'orderby'     => 'menu_order',
			'order'       => 'ASC'
		);

		$image_data                   = (array)get_posts( $args );
		$parent_product_data['image'] = array_shift( $image_data );

		add_filter( 'posts_request', 'wpsc_edit_variations_request_sql' );
	?>

			<p><a name='variation_control'>&nbsp;</a><?php _e( 'Check or uncheck variation boxes and then click Update Variations to add or remove variations.' ) ?></p>
			<table class="widefat page" id='wpsc_product_list' cellspacing="0">
				<thead>
					<tr>
						<?php print_column_headers( 'display-product-list' ); ?>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<?php print_column_headers( 'display-product-list', false ); ?>
					</tr>
				</tfoot>

				<tbody>
	<?php
		$wp_query = new WP_Query( $query );
		
		if ( !isset( $parent_product_data ) )
			$parent_product_data = null;

		wpsc_admin_product_listing( $parent_product_data );
		if ( count( $wp_query->posts ) < 1 ) :
	?>
					<tr>
						<td colspan="8">
							<?php _e( 'You have no Products added.', 'wpsc' ); ?>
						</td>
					</tr>

	<?php endif; ?>
				</tbody>

			</table>

	<?php endif; ?>

			</div>
		</div>

<?php
}

/**
 * Adding function to change text for media buttons
 */
function change_context() {
	return __( 'Upload Image%s' );
}

// Since we're hacking this all together anyway until the complete integration of
// custom_post_types, we'll go ahead and add the attachment to the db here as well.
function change_link( $product_data='' ) {
	$uploading_iframe_ID = $_GET["product"];
	$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";

	return $media_upload_iframe_src . "&amp;type=image&parent_page=wpsc-edit-products";
}

if ( !isset( $_GET["product"] ) )
	$_GET["product"] = '';

$uploading_iframe_ID = $_GET["product"];

//Justin Sainton - 5.19.2010 - Adding filters/actions for the media goodness :) Conditions important to not kill media functionality elsewhere
if ( isset( $_GET["page"] ) && ($_GET["page"] == "wpsc-edit-products" ) ) {
	add_filter( 'media_buttons_context', 'change_context' );
	add_filter( 'image_upload_iframe_src', "change_link" );
}

/*
* Modifications to Media Gallery
*/

if ( (isset( $_REQUEST['parent_page'] ) && ( $_REQUEST['parent_page'] == 'wpsc-edit-products' ) ) ) {
	add_filter( 'media_upload_tabs', 'wpsc_media_upload_tab_gallery', 12 );
    add_filter( 'attachment_fields_to_save', 'wpsc_save_attachment_fields', 9, 2 );
    add_filter( 'media_upload_form_url', 'wpsc_media_upload_url', 9, 1 );
	add_action( 'admin_head', 'wpsc_gallery_css_mods' );
}
	add_filter( 'gettext','wpsc_filter_delete_text',12 , 3 );
    add_filter( 'attachment_fields_to_edit', 'wpsc_attachment_fields', 11, 2 );
	add_filter( 'gettext','wpsc_filter_feature_image_text', 12, 3 );

if( isset( $_REQUEST["save"] ) && is_array($_REQUEST["attachments"]) ) {
	wpsc_regenerate_thumbnails();
}	

/*
 * This filter translates string before it is displayed 
 * specifically for the words 'Use as featured image' with 'Use as Product Thumbnail' when the user is selecting a Product Thumbnail
 * using media gallery.
 * 
 * @param $translation The current translation
 * @param $text The text being translated
 * @param $domain The domain for the translation
 * @return string The translated / filtered text.
 */
function wpsc_filter_feature_image_text($translation, $text, $domain) {

	if( 'Use as featured image' == $text && isset( $_REQUEST['post_id'] ) ){
		$translations = &get_translations_for_domain($domain);
		return $translations->translate('Use as Product Thumbnail') ;
	}
	return $translation;
}

function wpsc_attachment_fields($form_fields, $post) {

	$parent_post = get_post($post->post_parent);

	if ($parent_post->post_type == "wpsc-product" || $parent_post->post_title == "stopgap") {
	
	//Unfortunate hack, as I'm not sure why the From Computer tab doesn't process filters the same way the Gallery does
	
	echo '	
<script type="text/javascript"> 

	jQuery(function($){
	
		var product_image = $("a.wp-post-thumbnail").text();
		
		if (product_image == "Use as featured image") {
			$("a.wp-post-thumbnail").text("Use as Product Thumbnail");
		}
		
		var trash = $("#media-upload a.del-link").text();
		
		if (trash == "Delete") {
			$("#media-upload a.del-link").text("Trash");
		}
		
		
		});
	
</script>';
        $size_names = array('small-product-thumbnail' => __('Small Product Thumbnail'), 'medium-single-product' => __('Medium Single Product'), 'full' => __('Full Size'));
			
		$check = get_post_meta( $post->ID, '_wpsc_selected_image_size', true );

	//This loop attaches the custom thumbnail/single image sizes to this page
        foreach ( $size_names as $size => $name ) {
            $downsize = image_downsize($post->ID, $size);

	        // is this size selectable?
           $enabled = ( $downsize[3] || 'full' == $size );
			$css_id = "image-size-{$size}-{$post->ID}";
			// if this size is the default but that's not available, don't select it
	
            $html = "<div class='image-size-item'><input type='radio' " . disabled( $enabled, false, false ) . "name='attachments[$post->ID][image-size]' id='{$css_id}' value='{$size}' ".checked($size, $check, false)." />";

            $html .= "<label for='{$css_id}'>" . __($name). "</label>";
            // only show the dimensions if that choice is available
            if ( $enabled )
                $html .= " <label for='{$css_id}' class='help'>" . sprintf( __("(%d&nbsp;&times;&nbsp;%d)"), $downsize[1], $downsize[2] ). "</label>";

            $html .= '</div>';

            $out .= $html;
	}
		
		unset($form_fields['post_excerpt'],$form_fields['image_url'], $form_fields['post_content'], $form_fields['post_title'], $form_fields['url'], $form_fields['align'], $form_fields['image_alt']['helps'], $form_fields["image-size"]);
		$form_fields['image_alt']['helps'] =  __('Alt text for the product image, e.g. &#8220;Rockstar T-Shirt&#8221;');

		$form_fields["image-size"] = array(
				 'label' => __('Size'),
				 'input' => 'html',
				 'html'  => $out,
				 'helps' => "<span style='text-align:left; clear:both; display:block; padding-top:3px;'>Thumbnail size for this image on the single product page</span>"
		);
		
	//This is for the custom thumbnail size.	
	
		$custom_thumb_size_w = get_post_meta($post->ID, "_wpsc_custom_thumb_w", true);
		$custom_thumb_size_h = get_post_meta($post->ID, "_wpsc_custom_thumb_h", true);
		$custom_thumb_html = "
			
			<input style='width:50px; text-align:center' type='text' name='attachments[{$post->ID}][wpsc_custom_thumb_w]' value='{$custom_thumb_size_w}' /> X <input style='width:50px; text-align:center' type='text' name='attachments[{$post->ID}][wpsc_custom_thumb_h]' value='{$custom_thumb_size_h}' />
		
		";
		$form_fields["wpsc_custom_thumb"] = array(
		"label" => __("Custom Thumbnail Size"),
		"input" => "html", // this is default if "input" is omitted
		"helps" => "<span style='text-align:left; clear:both; display:block; padding-top:3px;'>Custom thumbnail size for this image on the main Product Page</span>",
		"html" => $custom_thumb_html
	);
		
		}
        return $form_fields;
		
	}
	
function wpsc_save_attachment_fields($post, $attachment) {

	if ( isset  ( $attachment['wpsc_custom_thumb_w']) ) 
		update_post_meta($post['ID'], '_wpsc_custom_thumb_w', $attachment['wpsc_custom_thumb_w']);
		
	if ( isset  ( $attachment['wpsc_custom_thumb_h']) ) 
		update_post_meta($post['ID'], '_wpsc_custom_thumb_h', $attachment['wpsc_custom_thumb_h']);
		
	if ( isset  ( $attachment['image-size']) ) 
		update_post_meta($post['ID'], '_wpsc_selected_image_size', $attachment['image-size']);

	return $post;
}

function wpsc_media_upload_url($form_action_url) {

	$form_action_url = esc_url(add_query_arg(array('parent_page'=>'wpsc-edit-products')));

return $form_action_url;

}

function wpsc_gallery_css_mods() {

			print '<style type="text/css">
			#gallery-settings *{
			display:none;
			}
			a.wp-post-thumbnail {
					color:green;
			}
			#media-upload a.del-link {
				color:red;
			}
			#media-upload a.wp-post-thumbnail {
				margin-left:0px;
			}	
			td.savesend input.button {
				display:none;
			}
	</style>';
	print '	
	<script type="text/javascript"> 

	jQuery(function($){
		$("td.A1B1").each(function(){
		
			var target = $(this).next();
				$("p > input.button", this).appendTo(target);

		});
		
		var title = $("div.media-item span.title").text();
		
		if(title == "stopgap") {
			$("div.media-item").hide();
		}
		
		var product_image = $("a.wp-post-thumbnail").text();
		
		if (product_image == "Use as featured image") {
			$("a.wp-post-thumbnail").text("Use as Product Thumbnail");
		}
		
		
		
	});
	
	</script>';
}


function wpsc_media_upload_tab_gallery($tabs) {
	
		unset($tabs['gallery']);
		$tabs['gallery'] = __('Product Image Gallery');

	return $tabs;
}

function wpsc_filter_delete_text($translation, $text, $domain){

	if( 'Delete' == $text && isset( $_REQUEST['post_id'] ) && isset( $_REQUEST["parent_page"] ) ){
		$translations = &get_translations_for_domain($domain);
		return $translations->translate('Trash') ;
	}
	return $translation;
}


function wpsc_product_shipping_forms( $product_data='' ) {
	global $closed_postboxes;

	$output = '';
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];

	if ( $product_data == 'empty' ) {
		$display = "style='display:none;'";
	}
	$output .= "<div class='postbox " . ((array_search( 'wpsc_product_shipping_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : '') . "' " . ((array_search( 'wpsc_product_shipping_forms', $product_data['hidden_postboxes'] ) !== false) ? 'style="display: none;"' : '') . " id='wpsc_product_shipping_forms'><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

	if ( IS_WP27 )
		$output .= "<h3 class='hndle'>";
	else
		$output .= "<h3><a class='togbox'>+</a>";

	$output .= __( 'Shipping Details', 'wpsc' );

	if ( !isset( $product_data['transformed']['weight'] ) )
		$product_data['transformed']['weight'] = '';

	$output .= "</h3>
	<div class='inside'>
		<table>

     <!--USPS shipping changes-->
		   <tr>
			  <td>
				 " . __( 'Weight', 'wpsc' ) . "
			  </td>
			  <td>
				 <input type='text' size='5' name='meta[_wpsc_product_metadata][weight]' value='" . $product_data['transformed']['weight'] . "' />
				 <select name='meta[_wpsc_product_metadata][weight_unit]'>
					<option value='pound' " . (($product_meta['display_weight_as'] == 'pound') ? 'selected="selected"' : '') . ">Pounds</option>
					<option value='ounce' " . ((preg_match( "/o(u)?nce/", $product_meta['display_weight_as'] )) ? 'selected="selected"' : '') . ">Ounces</option>
					<option value='gram' " . (($product_meta['display_weight_as'] == 'gram') ? 'selected="selected"' : '') . ">Grams</option>
					<option value='kilogram' " . (($product_meta['display_weight_as'] == 'kilogram' || $product_meta['display_weight_as'] == 'kilograms') ? 'selected="selected"' : '') . ">Kilograms</option>
				 </select>
			  </td>
			</tr>
			  <!--dimension-->
			<tr>
			  <td>
				 Height
			  </td>
			  <td>
				 <input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][height]' value='" . $product_meta['dimensions']['height'] . "'>
				 <select name='meta[_wpsc_product_metadata][dimensions][height_unit]'>
					<option value='in' " . (($product_meta['dimensions']['height_unit'] == 'in') ? 'selected' : '') . ">inches</option>
					<option value='cm' " . (($product_meta['dimensions']['height_unit'] == 'cm') ? 'selected' : '') . ">cm</option>
					<option value='meter' " . (($product_meta['dimensions']['height_unit'] == 'meter') ? 'selected' : '') . ">meter</option>
				 </select>
				 </td>
				 </tr>
				 <tr>
			  <td>
				 Width
			  </td>
			  <td>
				 <input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][width]' value='" . $product_meta['dimensions']['width'] . "'>
				 <select name='meta[_wpsc_product_metadata][dimensions][width_unit]'>
					<option value='in' " . (($product_meta['dimensions']['width_unit'] == 'in') ? 'selected' : '') . ">inches</option>
					<option value='cm' " . (($product_meta['dimensions']['width_unit'] == 'cm') ? 'selected' : '') . ">cm</option>
					<option value='meter' " . (($product_meta['dimensions']['width_unit'] == 'meter') ? 'selected' : '') . ">meter</option>
				 </select>
				 </td>
				 </tr>
				 <tr>
			  <td>
				 Length
			  </td>
			  <td>
				 <input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][length]' value='" . $product_meta['dimensions']['length'] . "'>
				 <select name='meta[_wpsc_product_metadata][dimensions][length_unit]'>
					<option value='in' " . (($product_meta['dimensions']['length_unit'] == 'in') ? 'selected' : '') . ">inches</option>
					<option value='cm' " . (($product_meta['dimensions']['length_unit'] == 'cm') ? 'selected' : '') . ">cm</option>
					<option value='meter' " . (($product_meta['dimensions']['length_unit'] == 'meter') ? 'selected' : '') . ">meter</option>
				 </select>
				 </td>
			 </tr>

    <!--//dimension-->
    <!--USPS shipping changes ends-->
			<tr>
			  <td colspan='2'>
			  <strong>" . __( 'Flat Rate Settings', 'wpsc' ) . "</strong>
			  </td>
			</tr>
			<tr>
			  <td>
			  " . __( 'Local Shipping Fee', 'wpsc' ) . "
			  </td>
			  <td>
				<input type='text' size='10' name='meta[_wpsc_product_metadata][shipping][local]' value='" . number_format( $product_meta['shipping']['local'], 2, '.', '' ) . "' />
			  </td>
			</tr>

			<tr>
			  <td>
			  " . __( 'International Shipping Fee', 'wpsc' ) . "
			  </td>
			  <td>
				<input type='text' size='10' name='meta[_wpsc_product_metadata][shipping][international]' value='" . number_format( $product_meta['shipping']['international'], 2, '.', '' ) . "' />
			  </td>
			</tr>
			<tr>
				 <td>
				 <br />
				  <input id='add_form_no_shipping' type='checkbox' name='meta[_wpsc_product_metadata][no_shipping]' value='1' " . (($product_meta['no_shipping'] == 1) ? 'checked="checked"' : '') . "/>&nbsp;<label for='add_form_no_shipping'>" . __( 'Disregard Shipping for this Product', 'wpsc' ) . "</label>
			   </td>
			</tr>
	    </table>
	</div></div>";

	return $output;
}

function wpsc_product_advanced_forms( $product_data='' ) {
	global $closed_postboxes, $wpdb;

	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];

	$custom_fields = $wpdb->get_results( "
		SELECT
			`meta_id`, `meta_key`, `meta_value`
		FROM
			`{$wpdb->postmeta}`
		WHERE
			`post_id` = {$product_data['id']}
		AND
			`meta_key` NOT LIKE '\_%'
		ORDER BY
			LOWER(meta_key)", ARRAY_A
	);

	$output = '';

	if ( $product_data == 'empty' )
		$display = "style='display:none;'";

	$output .= "<div id='wpsc_product_advanced_forms' class='postbox " . ((array_search( 'wpsc_product_advanced_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : '') . "' " . ((array_search( 'wpsc_product_advanced_forms', $product_data['hidden_postboxes'] ) !== false) ? 'style="display: none;"' : '') . "><div class=\"handlediv\" title=\"Click to toggle\"><br /></div>";

	$output .= "<h3 class='hndle'>";
	$output .= __( 'Advanced Options', 'wpsc' );
	$output .= "</h3>
       <div class='inside'>
       <table>";

	$output .= "<tr>
      <td colspan='2' class='itemfirstcol'>
        <strong>" . __( 'Custom Meta', 'wpsc' ) . ":</strong><br />
         <a href='#' class='add_more_meta' onclick='return add_more_meta(this)'> + " . __( 'Add Custom Meta', 'wpsc' ) . "</a><br /><br />";

	foreach ( (array)$custom_fields as $custom_field ) {
		$i = $custom_field['meta_id'];
		// for editing, the container needs an id, I can find no other tidyish method of passing a way to target this object through an ajax request
		$output .= "
				<div class='product_custom_meta'  id='custom_meta_$i'>
					" . __( 'Name', 'wpsc' ) . "
					<input type='text' class='text'  value='{$custom_field['meta_key']}' name='custom_meta[$i][name]' id='custom_meta_name_$i'>

					" . __( 'Value', 'wpsc' ) . "
					<textarea class='text' name='custom_meta[$i][value]' id='custom_meta_value_$i'>{$custom_field['meta_value']}</textarea>
					<a href='#' class='remove_meta' onclick='return remove_meta(this, $i)'>" . __( 'Delete' ) . "</a>
					<br />
				</div>";
	}

	$output .= "
				<div class='product_custom_meta'>
					" . __( 'Name', 'wpsc' ) . ": <br />
					<input type='text' name='new_custom_meta[name][]' value='' class='text'/><br />
					" . __( 'Description', 'wpsc' ) . ": <br />
					<textarea name='new_custom_meta[value][]' cols='40' rows='10' class='text' ></textarea>
					<br />
				</div>
			</td>
		</tr>";

	$output .= "
		<tr>
			<td class='itemfirstcol' colspan='2'><br /> <strong>" . __( 'Merchant Notes', 'wpsc' ) . ":</strong><br />

			<textarea cols='40' rows='3' name='meta[_wpsc_product_metadata][merchant_notes]' id='merchant_notes'>";
	if ( isset( $product_meta['merchant_notes'] ) )
		$output .= stripslashes( $product_meta['merchant_notes'] );

	$output .= "</textarea>
			<small>" . __( 'These notes are only available here.', 'wpsc' ) . "</small>
		</td>
	</tr>";

	$output .="
	<tr>
		<td class='itemfirstcol' colspan='2'><br />
			<strong>" . __( 'Personalisation Options', 'wpsc' ) . ":</strong><br />
			<input type='hidden' name='meta[_wpsc_product_metadata][engraved]' value='0' />
			<input type='checkbox' name='meta[_wpsc_product_metadata][engraved]' " . (($product_meta['engraved'] == true) ? 'checked="checked"' : '') . " id='add_engrave_text' />
			<label for='add_engrave_text'> " . __( 'Users can personalize this Product by leaving a message on single product page', 'wpsc' ) . "</label>
			<br />
		</td>
	</tr>
	<tr>
		<td class='itemfirstcol' colspan='2'>
			<input type='hidden' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' value='0' />
			<input type='checkbox' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' " . (($product_meta['can_have_uploaded_image'] == true) ? 'checked="checked"' : '') . " id='can_have_uploaded_image' />
			<label for='can_have_uploaded_image'> " . __( 'Users can upload images on single product page to purchase logs.', 'wpsc' ) . "</label>
			<br />
		</td>
	</tr>";

	if ( get_option( 'payment_gateway' ) == 'google' ) {
		$output .= "
	<tr>
		<td class='itemfirstcol' colspan='2'>

			<input type='checkbox' " . $product_meta['google_prohibited'] . " name='meta[_wpsc_product_metadata][google_prohibited]' id='add_google_prohibited' /> <label for='add_google_prohibited'>
			" . __( 'Prohibited', 'wpsc' ) . "
			<a href='http://checkout.google.com/support/sell/bin/answer.py?answer=75724'>by Google?</a></label><br />
		</td>
	</tr>";
	}

	ob_start();
	do_action( 'wpsc_add_advanced_options', $product_data['id'] );
	$output .= ob_get_contents();
	ob_end_clean();

	$output .= "
	<tr>
		<td class='itemfirstcol' colspan='2'><br />
			<strong>" . __( 'Enable Comments', 'wpsc' ) . ":</strong><br />
			<select name='meta[_wpsc_product_metadata][enable_comments]'>
				<option value='' " . ((isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '' ) ? 'selected' : '') . ">Use Default</option>
				<option value='1' " . ((isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '1') ? 'selected' : '') . ">Yes</option>
				<option value='0' " . ((isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '0') ? 'selected' : '') . ">No</option>
			</select>
			<br/>" . __( 'Allow users to comment on this Product.', 'wpsc' ) . "
		</td>
	</tr>";

	$output .= "
    </table></div></div>";

	return $output;
}

function wpsc_product_external_link_forms( $product_data = '' ) {

	global $closed_postboxes, $wpdb;
	
	$product_meta = &$product_data['meta']['_wpsc_product_metadata'];

	$output = '';

	if ( $product_data == 'empty' )
		$display = "style='display:none;'";
	
	// Get External Link Values
	$external_link_value        = isset( $product_meta['external_link'] ) ? $product_meta['external_link'] : '';
	$external_link_text_value   = isset( $product_meta['external_link_text'] ) ? $product_meta['external_link_text'] : '';
	$external_link_target_value = isset( $product_meta['external_link_target'] ) ? $product_meta['external_link_target'] : '';
	$external_link_target_value_selected[$external_link_target_value] = ' selected="selected"';
	if( !isset($external_link_target_value_selected['_self']) ) $external_link_target_value_selected['_self'] = '';
	if( !isset($external_link_target_value_selected['_blank']) ) $external_link_target_value_selected['_blank'] = '';
	$output .= "<div id='wpsc_product_external_link_forms' class='postbox " . ((array_search( 'wpsc_product_external_link_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : '') . "' " . ((array_search( 'wpsc_product_external_link_forms', $product_data['hidden_postboxes'] ) !== false) ? 'style="display: none;"' : '') . "><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

	$output .= "<h3 class='hndle'>";
	$output .= __( 'Off Site Product Link', 'wpsc' );
	$output .= '</h3>
       <div class="inside">
			<p>' . __( 'If this product is for sale on another website enter the link here. For instance if your product is an MP3 file for sale on itunes you could put the link here. This option overrides the buy now and add to cart links and takes you to the site linked here. You can also customise the Buy Now text and choose to open the link in a new window.', 'wpsc' ) . '</p>
       <table class="form-table" style="width: 100%;" cellspacing="2" cellpadding="5">
	<tbody><tr class="form-field">
		<th valign="top" scope="row"><label for="external_link">' . __( 'External Link', 'wpsc' ) . '</label></th>
		<td><input type="text" name="meta[_wpsc_product_metadata][external_link]" id="external_link" value="' . $external_link_value . '" size="50" style="width: 95%"></td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row"><label for="external_link_text">' . __( 'External Link Text', 'wpsc' ) . '</label></th>
		<td><input type="text" name="meta[_wpsc_product_metadata][external_link_text]" id="external_link_text" value="' . $external_link_text_value . '" size="50" style="width: 95%"></td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row"><label for="external_link_target">' . __( 'External Link Target', 'wpsc' ) . '</label></th>
		<td>
			<select id="external_link_target" name="meta[_wpsc_product_metadata][external_link_target]">
				<option value="">Default (set by theme)</option>
				<option value="_self"' . $external_link_target_value_selected['_self'] . '>Open link in the same window</option>
				<option value="_blank"' . $external_link_target_value_selected['_blank'] . '>Open link in a new window</option>
			</select>
		</td>
	</tr>
</tbody></table>
</div></div>';

	return $output;
	
}

function wpsc_product_image_forms( $product_data = '' ) {
	global $closed_postboxes;

	if ( $product_data == 'empty' )
		$display = "style='display:none;'";
?>
	<div id='wpsc_product_image_forms' class='postbox <?php echo ((array_search( 'wpsc_product_image_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : ''); ?>' <?php echo ((array_search( 'wpsc_product_image_forms', $product_data['hidden_postboxes'] ) !== false) ? 'style="display: none;"' : ''); ?> ><div class="handlediv" title="Click to toggle"><br></div>
		<h3 class='hndle'> <?php _e( 'Product Images', 'wpsc' ); ?></h3>
		<div class='inside'>

			<?php edit_multiple_image_gallery( $product_data ); ?>

			<p><strong <?php if ( isset( $display ) ) echo $display; ?>><a href="media-upload.php?parent_page=wpsc-edit-products&post_id=<?php echo $product_data['id']; ?>&type=image&tab=gallery&TB_iframe=1&width=640&height=566" class="thickbox" title="Manage Your Product Images"><?php _e( 'Manage Product Images', 'wpsc' ); ?></a></strong></p>
		</div>

		<div style='clear:both'></div>

	</div>
<?php
}

function wpsc_product_download_forms( $product_data='' ) {
	global $wpdb, $closed_postboxes;

	if ( $product_data == 'empty' )
		$display = "style='display:none;'";

	$output = '';
	$upload_max = wpsc_get_max_upload_size();
	$output .= "<div id='wpsc_product_download_forms' class='postbox " . ((array_search( 'wpsc_product_download_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : '') . "' " . ((array_search( 'wpsc_product_download_forms', $product_data['hidden_postboxes'] ) !== false) ? 'style="display: none;"' : '') . "><div class=\"handlediv\" title=\"Click to toggle\"><br></div>";

	$output .= "<h3 class='hndle'>" . __( 'Product Downloads', 'wpsc' ) . "</h3>";
	$output .= "<div class='inside'>";

	$output .= wpsc_select_product_file( $product_data['id'] );

	$output .= "<h4>" . __( 'Upload New File', 'wpsc' ) . ":</h4>";
	$output .= "<input type='file' name='file' value='' /><br />" . __( 'Max Upload Size', 'wpsc' ) . " : <span>" . $upload_max . "</span><br /><br />";
	$output .= "<h4>" . __( '<a href="admin.php?wpsc_admin_action=product_files_existing&product_id=' . $product_data['id'] . '" class="thickbox" title="Select all downloadable files for ' . $product_data['name'] . '">'.__("Select from existing files").'</a>', 'wpsc' ) . "</h4>";

	if ( function_exists( "make_mp3_preview" ) || function_exists( "wpsc_media_player" ) ) {

		$output .= "<br />";
		$output .="<h4>" . __( "Select an MP3 file to upload as a preview" ) . "</h4>";

		$output .= "<input type='file' name='preview_file' value='' /><br />";
		$output .= "<br />";
	}
	$output = apply_filters('wpsc_downloads_metabox', $output);
	$output .="</div></div>";

	return $output;
}

function wpsc_product_label_forms() {
	global $closed_postboxes;
?>
	<div id='wpsc_product_label_forms' class='postbox <?php echo ((array_search( 'wpsc_product_label_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : ''); ?>'><div class="handlediv" title="Click to toggle"><br></div>
		<h3 class="<?php if ( function_exists( 'add_object_page' ) ) : ?>hndle<?php endif; ?>">
			<?php if ( !function_exists( 'add_object_page' ) ) : ?>

				<a class='togbox'>+</a>

			<?php endif; ?>

			<?php _e( 'Label Control', 'wpsc' ); ?>
		</h3>
		<div class='inside'>
			<table>
				<tr>
					<td colspan='2'>
<?php _e( 'Add Label', 'wpsc' ); ?> :
						<a id='add_label'><?php _e( 'Add Label', 'wpsc' ); ?></a>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<div id="labels">
							<table>
								<tr>
									<td><?php _e( 'Label', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[labels][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'Label Description', 'wpsc' ) ?> :</td>
									<td><textarea name="productmeta_values[labels_desc][]"></textarea></td>
								</tr>
								<tr>
									<td><?php _e( 'Life Number', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[life_number][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'Item Number', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[item_number][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'Product Code', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[product_code][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'PDF', 'wpsc' ) ?> :</td>
									<td><input type="file" name="pdf[]"></td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

<?php
}

function edit_multiple_image_gallery( $product_data ) {
	global $wpdb;

	$siteurl = get_option( 'siteurl' );

	if ( $product_data['id'] > 0 ) {
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $product_data['id'],
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);

		$attached_images = (array)get_posts( $args );

		if ( has_post_thumbnail( $product_data['id'] ) )
			echo get_the_post_thumbnail( $product_data['id'], 'admin-product-thumbnails' );
	}
}

/**
 * Displays the category forms for adding and editing Products
 * Recurses to generate the branched view for subcategories
 */
function wpsc_category_list( &$product_data, $group_id, $unique_id = '', $category_id = null ) {
	global $wpdb;
	static $iteration = 0;

	$iteration++;

	$output   = '';
	$selected = '';

	if ( is_numeric( $category_id ) )
		$values = get_terms( 'wpsc_product_category', "hide_empty=0&parent=" . $category_id, ARRAY_A );
	else
		$values = get_terms( 'wpsc_product_category', "hide_empty=0&parent=" . $group_id, ARRAY_A );

	if ( $category_id < 1 ){
		$output .= "<ul class='list:category categorychecklist form-no-clear'>\n\r";
		$indenter = "";	
	}elseif ( (count( $values ) > 0 ) ){
		$output .= "<ul class='children'>\n\r";
		$indenter = "<img class='category_indenter' src='".WPSC_CORE_IMAGES_URL."/indenter.gif' alt='' title='' />";

	}

	foreach ( (array)$values as $option ) {
		$option = (array)$option;

		if ( isset( $product_data['category_ids'] ) && count( $product_data['category_ids'] ) > 0 ) {
			if ( in_array( $option['term_id'], $product_data['category_ids'] ) ) {
				$selected = "checked='checked'";
			}
		}

		$output .= "  <li id='category-" . $option['term_id'] . "'>\n\r";
		$output .= $indenter."    <label class='selectit'>\n\r";
		$output .= "    <input id='" . $unique_id . "category_form_" . $option['term_id'] . "' type='checkbox' {$selected} name='category[]' value='" . $option['term_id'] . "' /></label>\n\r";
		$output .= "    <label for='" . $unique_id . "category_form_" . $option['term_id'] . "' class='greytext' >" . stripslashes( $option['name'] ) . "</label>\n\r";
		$output .= wpsc_category_list( $product_data, $group_id, $unique_id, $option['term_id'] );

		$output .= "  </li>\n\r";

		$selected = "";
	}

	if ( (count( $values ) > 0 ) )
		$output .= "</ul>\n\r";

	return $output;
}

/**
 * Slightly modified copy of the Wordpress the_editor function
 *
 *  We have to use a modified version because the wordpress one calls javascript that uses document.write
 *  When this javascript runs after being loaded through AJAX, it replaces the whole page.
 *
 * The amount of rows the text area will have for the content has to be between
 * 3 and 100 or will default at 12. There is only one option used for all users,
 * named 'default_post_edit_rows'.
 *
 * If the user can not use the rich editor (TinyMCE), then the switch button
 * will not be displayed.
 *
 * @since 3.7
 *
 * @param string $content Textarea content.
 * @param string $id HTML ID attribute value.
 * @param string $prev_id HTML ID name for switching back and forth between visual editors.
 * @param bool $media_buttons Optional, default is true. Whether to display media buttons.
 * @param int $tab_index Optional, default is 2. Tabindex for textarea element.
 */
function wpsc_the_editor( $content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2 ) {
	$rows = get_option( 'default_post_edit_rows' );
	if ( ($rows < 3) || ($rows > 100) )
		$rows = 12;

	if ( !current_user_can( 'upload_files' ) )
		$media_buttons = false;

	$richedit = user_can_richedit();
	$class = '';

	if ( $richedit || $media_buttons ) {
?>
	<div id="editor-toolbar">
<?php
		if ( $richedit ) {
			$wp_default_editor = wp_default_editor(); ?>
			<div class="zerosize"><input accesskey="e" type="button" onclick="switchEditors.go('<?php echo $id; ?>')" /></div>
<?php		if ( 'html' == $wp_default_editor ) {
				add_filter( 'the_editor_content', 'wp_htmledit_pre' ); ?>
					<a id="edButtonHTML" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e( 'HTML' ); ?></a>
					<a id="edButtonPreview" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e( 'Visual' ); ?></a>
<?php
			} else {
				$class = " class='theEditor'";
				add_filter( 'the_editor_content', 'wp_richedit_pre' ); ?>
					<a id="edButtonHTML" class="hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'html');"><?php _e( 'HTML' ); ?></a>
					<a id="edButtonPreview" class="active hide-if-no-js" onclick="switchEditors.go('<?php echo $id; ?>', 'tinymce');"><?php _e( 'Visual' ); ?></a>
<?php
			}
		}

		if ( $media_buttons ) {
?>
			<div id="media-buttons" class="hide-if-no-js">
<?php do_action( 'media_buttons' ); ?>
			</div>
<?php } ?>
	</div>
<?php
	}
?>
	<div id="quicktags"><?php wp_print_scripts( 'quicktags' ); ?>
		<div id="ed_toolbar">
		</div>
		<script type="text/javascript" defer="defer'">wpsc_edToolbar()</script>
	</div>

<?php
	$the_editor = apply_filters( 'the_editor', "<div id='editorcontainer'><textarea rows='$rows'$class cols='40' name='$id' tabindex='$tab_index' id='$id'>%s</textarea></div>\n" );
	$the_editor_content = apply_filters( 'the_editor_content', $content );

	printf( $the_editor, $the_editor_content );
?>
	<script type="text/javascript">
		edCanvas = document.getElementById('<?php echo $id; ?>');
	</script>
<?php
}

?>
