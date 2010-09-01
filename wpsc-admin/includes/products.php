<?php
/**
 * WP eCommerce product page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.8
 */

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
function wpsc_filter_feature_image_text($translation, $text, $domain){
	if('Use as featured image' == $text && isset($_REQUEST['post_id']) && ('wpsc-product' == get_post_type($_REQUEST['post_id']))){
		$translations = &get_translations_for_domain($domain);
		return $translations->translate('Use as Product Thumbnail') ;
	}
	return $translation;
}
add_filter('gettext','wpsc_filter_feature_image_text',10,3);

/**
 * wpsc_product_rows function, copies the functionality of the wordpress code for displaying posts and pages, but is for products
 * 
 */
function wpsc_admin_product_listing($parent_product = null) {
	global $wp_query, $wpsc_products, $mode;

	add_filter('the_title','esc_html');

	// Create array of post IDs.
	$product_ids = array();

	if ( empty($wpsc_products) ) {
		$wpsc_products = &$wp_query->posts;
	}
	
	foreach ( $wpsc_products as $product ) {
		$product_ids[] = $product->ID;
	}
	
	foreach ( $wpsc_products as $product ) {
		wpsc_product_row($product, $parent_product);
	}
}

function wpsc_product_row(&$product, $parent_product = null) {
	global $wp_query, $wpsc_products, $mode, $current_user;
	static $rowclass;
	//echo "<pre>".print_r($product, true)."</pre>";

	$global_product = $product;
	setup_postdata($product);

	$rowclass = 'alternate' == $rowclass ? '' : 'alternate';
	$post_owner = ( $current_user->ID == $product->post_author ? 'self' : 'other' );
	$edit_link = get_edit_post_link( $product->ID );

	$title = get_the_title( $product->ID);
	if ( empty($title) ) {
		$title = __('(no title)');
	}
	
	?>
	
	<tr id='post-<?php echo $product->ID; ?>' class='<?php echo trim( $rowclass . ' author-' . $post_owner . ' status-' . $product->post_status ); ?> iedit <?php if ( get_option ( 'wpsc_sort_by' ) == 'dragndrop') { echo 'product-edit'; } ?>' valign="top">
	<?php
	$posts_columns = get_column_headers('display-product-list');
	$posts_columns = apply_filters( 'manage_display-product-list_columns', $posts_columns );
	$hidden = get_hidden_columns('display-product-list');
	//exit('<pre>'.print_r($product,true).'</pre>');

	foreach ( $posts_columns as $column_name=>$column_display_name ) {
		$class = "class=\"$column_name column-$column_name\"";

		$style = '';
		if ( in_array($column_name, $hidden) )
			$style = ' style="display:none;"';

		$attributes = "$class$style";

		//echo "<pre>".print_r($column_name,true)."</pre>".count($posts_columns);
		switch ($column_name) {

		case 'cb':
		?>
		<th scope="row" class="check-column"><?php if ( current_user_can( 'edit_post', $product->ID ) ) { ?>
		<input type="checkbox" name="post[]" value="<?php the_ID(); ?>" />										
		<?php do_action('wpsc_admin_product_checkbox', $product->ID); ?>
		
<?php } ?></th>
		<?php
		break;

		case 'date': /* !date case */
			if ( '0000-00-00 00:00:00' == $product->post_date && 'date' == $column_name ) {
				$t_time = $h_time = __('Unpublished');
				$time_diff = 0;
			} else {
				$t_time = get_the_time(__('Y/m/d g:i:s A'));
				$m_time = $product->post_date;
				$time = get_post_time('G', true, $post);

				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < 24*60*60 )
					$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
				else
					$h_time = mysql2date(__('Y/m/d'), $m_time);
			}

			echo '<td ' . $attributes . '>';
			if ( 'excerpt' == $mode )
				echo apply_filters('post_date_column_time', $t_time, $post, $column_name, $mode);
			else
				echo '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $post, $column_name, $mode) . '</abbr>';
			echo '<br />';
			if ( 'publish' == $product->post_status ) {
				_e('Published');
			} elseif ( 'future' == $product->post_status ) {
				if ( $time_diff > 0 )
					echo '<strong class="attention">' . __('Missed schedule') . '</strong>';
				else
					_e('Scheduled');
			} else {
				_e('Last Modified');
			}
			echo '</td>';
		break;



		case 'title': /* !title case */
			$attributes = 'class="post-title column-title"' . $style;
			$_GET["product_parent"]  = '';
			if( isset($_GET["product"]) && ($_GET["product_parent"] == '' )){
				$edit_link = add_query_arg(array('page' => 'wpsc-edit-products', 'action' => 'wpsc_add_edit' ,'product' => $product->ID, 'product_parent' => $_GET["product"]));
			} else {
				$edit_link = add_query_arg(array('page' => 'wpsc-edit-products', 'action' => 'wpsc_add_edit' ,'product' => $product->ID));
				$edit_link = remove_query_arg( 'product_parent' );
			}
			$edit_link = wp_nonce_url($edit_link, 'edit-product_'.$product->ID);
		?>
		<td <?php echo $attributes ?>>
			<strong>
			<?php if ( current_user_can('edit_post', $product->ID) && $product->post_status != 'trash' ) { ?>
				<a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo esc_attr(sprintf(__('Edit &#8220;%s&#8221;'), $title)); ?>"><?php echo $title ?></a>
			<?php } else {
				echo $title;
			};
			 _post_states($product);
			 ?>
			</strong>
			<?php

			$actions = array();
			if ( current_user_can('edit_post', $product->ID) && 'trash' != $product->post_status ) {
				$actions['edit'] = '<a class="edit-product" href="'.$edit_link.'" title="' . esc_attr(__('Edit this product', 'wpsc')) . '">'. __('Edit', 'wpsc') . '</a>';				
				$actions['quick_edit'] = "<a class='wpsc-quickedit' title='".esc_attr(__('Quick Edit', 'wpsc'))."' href='#'>".__('Quick Edit')."</a>";
			}

			if ( current_user_can('delete_post', $product->ID) ) {
				if ( 'trash' == $product->post_status ) {
					$actions['untrash'] = "<a title='" . esc_attr(__('Restore this product from the Trash', 'wpsc')) . "' href='" . wp_nonce_url("admin.php?page=wpsc-edit-products&amp;wpsc_admin_action=untrash&amp;product={$product->ID}", 'untrash-product_' . $product->ID) . "'>".__('Restore')."</a>";
				} else if ( EMPTY_TRASH_DAYS ) {
					$actions['trash'] = "<a class='submitdelete' title='".esc_attr(__('Move this product to the Trash', 'wpsc'))."' href='" . get_delete_post_link($product->ID) . "'>".__('Trash')."</a>";
				}
				
				if ( 'trash' == $product->post_status || !EMPTY_TRASH_DAYS ) {
					$actions['delete'] = "<a class='submitdelete' title='".esc_attr(__('Delete this product permanently', 'wpsc'))."' href='" . wp_nonce_url("admin.php?page=wpsc-edit-products&amp;wpsc_admin_action=delete&amp;product={$product->ID}", 'delete-product_'.$product->ID)."'>".__('Delete Permanently')."</a>";
				}
			}
			
			if ( in_array($product->post_status, array('pending', 'draft')) ) {
				if ( current_user_can('edit_product', $product->ID) ) {
					$actions['view'] = '<a href="'.get_permalink($product->ID).'" title="'.esc_attr(sprintf(__('Preview &#8220;%s&#8221;'), $title)) . '" rel="permalink">'.__('Preview').'</a>';
				}
			} else if ( 'trash' != $product->post_status ) {
				$actions['view'] = '<a href="'.get_permalink($product->ID).'" title="'.esc_attr(sprintf(__('View &#8220;%s&#8221;'), $title)).'" rel="permalink">'.__('View').'</a>';
			}
			if(!isset($_GET["product"]) || $_GET["product"] == '' ) {
			$actions['duplicate'] = "<a class='submitdelete' title='".esc_attr(__('Duplicate', 'wpsc'))."' href='" . wp_nonce_url("admin.php?page=wpsc-edit-products&amp;wpsc_admin_action=duplicate_product&amp;product={$product->ID}", 'duplicate-product_'.$product->ID)."'>".__('Duplicate')."</a>";
}
			
			$actions = apply_filters('post_row_actions', $actions, $product);
			$action_count = count($actions);
			$i = 0;
			echo '<div class="row-actions">';
			
			foreach ( $actions as $action => $link ) {
				++$i;
				( $i == $action_count ) ? $sep = '' : $sep = ' | ';
				echo "<span class='$action'>$link$sep</span>";
			}
			
			echo '</div>';
			get_inline_data($product);
		?>
		</td>
		<?php
		break;



		case 'image':  /* !image case */
			?>
			<td class="product-image ">
			<?php
		   $attached_images = (array)get_posts(array( 
	          'post_type' => 'attachment', 
	          'numberposts' => 1, 
	          'post_status' => null, 
	          'post_parent' => $product->ID, 
	          'orderby' => 'menu_order', 
	          'order' => 'ASC' 
		    ));
		     
			

		 	 if(isset($product->ID) && has_post_thumbnail($product->ID)){
				echo get_the_post_thumbnail($product->ID, 'admin-product-thumbnails');
		     }elseif(!empty($attached_images)){
			    $attached_image = $attached_images[0];
				
				$src =wp_get_attachment_url($attached_image->ID);
		     ?>
		     	<div style='width:38px;height:38px;overflow:hidden;'>
					<img title='Drag to a new position' src='<?php echo $src; ?>' alt='<?php echo $title; ?>' width='38' height='38' />
				</div>
				<?php
			
		     
		     }else{
		      	$image_url = WPSC_URL."/images/no-image-uploaded.gif";
				?>
					<img title='Drag to a new position' src='<?php echo $image_url; ?>' alt='<?php echo $title; ?>' width='38' height='38' />
				<?php

		      
		      }
		    
		?>
			</td>
			<?php
		break;
		
		
		
		case 'price':  /* !price case */
	
			$price = get_post_meta($product->ID, '_wpsc_price', true);
		//	exit($product->ID.'PRICE IS: <pre>'.print_r($price, true).'</pre>');
			?>
				<td  <?php echo $attributes ?>>
					<?php echo nzshpcrt_currency_display($price, 1); ?>
					<div class='price-editing-fields' id='price-editing-fields-<?php echo $product->ID; ?>'>
						<input type='text' class='the-product-price' name='product_price[<?php echo $product->ID; ?>][price]' value='<?php echo number_format($price,2,'.',''); ?>' />
						<input type='hidden' name='product_price[<?php echo $product->ID; ?>][id]' value='<?php echo $product->ID; ?>' />
						<input type='hidden' name='product_price[<?php echo $product->ID; ?>][nonce]' value='<?php echo wp_create_nonce('edit-product_price-'.$product->ID); ?>' />


					</div>
				</td>
			<?php
		break;

	// 5.8.2010 - Justin Sainton - Addition of weight and stock cases to column header switch.
	
		case 'weight' :
		
			$product_data['meta'] = array();
			$product_data['meta'] = get_post_meta($product->ID, '');
				foreach($product_data['meta'] as $meta_name => $meta_value) {
					$product_data['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
				}
		$product_data['transformed'] = array();
		if(!isset($product_data['meta']['_wpsc_product_metadata']['weight'])) $product_data['meta']['_wpsc_product_metadata']['weight'] = "";
		if(!isset($product_data['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data['meta']['_wpsc_product_metadata']['weight_unit'] = "";
		
		$product_data['transformed']['weight'] = wpsc_convert_weight($product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['weight_unit']);
			$weight = $product_data['transformed']['weight'];
			if($weight == ''){
				$weight = '0';
			}
			
			$unit = $product_data['meta']['_wpsc_product_metadata']['weight_unit'];
			switch($unit) {
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
			?>
				<td  <?php echo $attributes ?>>
					<span class="weightdisplay"><?php echo $weight; ?></span>
					<div class='weight-editing-fields' id='weight-editing-fields-<?php echo $product->ID; ?>'>
						<input type='text' class='the-weight-fields' name='weight_field[<?php echo $product->ID; ?>][weight]' value='<?php echo $weight; ?>' />
						<input type='hidden' name='weight_field[<?php echo $product->ID; ?>][id]' value='<?php echo $product->ID; ?>' />
						<input type='hidden' name='weight_field[<?php echo $product->ID; ?>][nonce]' value='<?php echo wp_create_nonce('edit-weight-'.$product->ID); ?>' />
					</div><?php echo $unit; ?>
				</td>
			<?php

		break;
		
		case 'stock' :
			$stock = get_post_meta($product->ID, '_wpsc_stock', true);
			if($stock == ''){
				$stock = '0';
			}
			?>
				<td  <?php echo $attributes ?>>
					<span class="stockdisplay"><?php echo $stock; ?></span>
					<div class='stock-editing-fields' id='stock-editing-fields-<?php echo $product->ID; ?>'>
						<input type='text' class='the-stock-fields' name='stock_field[<?php echo $product->ID; ?>][stock]' value='<?php echo $stock; ?>' />
						<input type='hidden' name='stock_field[<?php echo $product->ID; ?>][id]' value='<?php echo $product->ID; ?>' />
						<input type='hidden' name='stock_field[<?php echo $product->ID; ?>][nonce]' value='<?php echo wp_create_nonce('edit-stock-'.$product->ID); ?>' />


					</div>
				</td>
	<?php
		break;

		case 'categories':  /* !categories case */
		?>
		<td <?php echo $attributes ?>><?php
			$categories = get_the_product_category($product->ID);
			if ( !empty( $categories ) ) {
				$out = array();
				foreach ( $categories as $c )
					$out[] = "<a href='admin.php?page=wpsc-edit-products&amp;category={$c->slug}'> " . esc_html(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . "</a>";
					echo join( ', ', $out );
			} else {
				_e('Uncategorized');
			}
		?></td>
		<?php
		break;



		case 'tags':  /* !tags case */
		?>
		<td <?php echo $attributes ?>><?php
			$tags = get_the_tags($product->ID);
			if ( !empty( $tags ) ) {
				$out = array();
				foreach ( $tags as $c )
					$out[] = "<a href='edit.php?tag=$c->slug'> " . esc_html(sanitize_term_field('name', $c->name, $c->term_id, 'post_tag', 'display')) . "</a>";
				echo join( ', ', $out );
			} else {
				_e('No Tags');
			}
		?></td>
		<?php
		break;
		case 'SKU':
			$sku = get_post_meta($product->ID, '_wpsc_sku', true);
			if($sku == ''){
				$sku = 'N/A';
			}
		//	exit($product->ID.'PRICE IS: <pre>'.print_r($price, true).'</pre>');
			?>
				<td  <?php echo $attributes ?>>
					<span class="skudisplay"><?php echo $sku; ?></span>
					<div class='sku-editing-fields' id='sku-editing-fields-<?php echo $product->ID; ?>'>
						<input type='text' class='the-sku-fields' name='sku_field[<?php echo $product->ID; ?>][sku]' value='<?php echo $sku; ?>' />
						<input type='hidden' name='sku_field[<?php echo $product->ID; ?>][id]' value='<?php echo $product->ID; ?>' />
						<input type='hidden' name='sku_field[<?php echo $product->ID; ?>][nonce]' value='<?php echo wp_create_nonce('edit-sku-'.$product->ID); ?>' />


					</div>
				</td>
			<?php
		break;
		case 'sale_price':
		
			$price = get_post_meta($product->ID, '_wpsc_special_price', true);
		//	exit($product->ID.'PRICE IS: <pre>'.print_r($price, true).'</pre>');
			?>
				<td  <?php echo $attributes ?>>
					<?php echo nzshpcrt_currency_display($price, 1); ?>
					<div class='sales-price-fields' id='sales-price-editing-fields-<?php echo $product->ID; ?>'>
						<input type='text'  class='the-sale-price' name='sale_product_price[<?php echo $product->ID; ?>][price]' value='<?php echo number_format($price,2,'.',''); ?>' />
						<input type='hidden' name='sale_product_price[<?php echo $product->ID; ?>][id]' value='<?php echo $product->ID; ?>' />
						<input type='hidden' name='sale_product_price[<?php echo $product->ID; ?>][nonce]' value='<?php echo wp_create_nonce('sale-edit-product_price-'.$product->ID); ?>' />


					</div>
				</td>
			<?php

		break;


		case 'comments':  /* !comments case */
		?>
		<td <?php echo $attributes ?>><div class="post-com-count-wrapper">
		<?php
			$pending_phrase = sprintf( __('%s pending'), number_format( $pending_comments ) );
			if ( $pending_comments )
				echo '<strong>';
				comments_number("<a href='edit-comments.php?p=$product->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . /* translators: comment count link */ _x('0', 'comment count') . '</span></a>', "<a href='edit-comments.php?p=$product->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . /* translators: comment count link */ _x('1', 'comment count') . '</span></a>', "<a href='edit-comments.php?p=$product->ID' title='$pending_phrase' class='post-com-count'><span class='comment-count'>" . /* translators: comment count link: % will be substituted by comment count */ _x('%', 'comment count') . '</span></a>');
				if ( $pending_comments )
				echo '</strong>';
		?>
		</div></td>
		<?php
		break;



		case 'author':  /* !author case */
		?>
		<td <?php echo $attributes ?>><a href="edit.php?author=<?php the_author_meta('ID'); ?>"><?php the_author() ?></a></td>
		<?php
		break;

		
		case 'control_view':  /* !control view case */
		?>
		<td><a href="<?php the_permalink(); ?>" rel="permalink" class="view"><?php _e('View'); ?></a></td>
		<?php
		break;



		case 'control_edit':  /* !control edit case */
		?>
		<td><?php if ( current_user_can('edit_post', $product->ID) ) { echo "<a href='$edit_link' class='edit'>" . __('Edit') . "</a>"; } ?></td>
		<?php
		break;



		case 'control_delete':  /* !control delete case */
		?>
		<td><?php if ( current_user_can('delete_post', $product->ID) ) { echo "<a href='" . wp_nonce_url("post.php?action=delete&amp;post=$id", 'delete-post_' . $product->ID) . "' class='delete'>" . __('Delete') . "</a>"; } ?></td>
		<?php
		break;



		default:   /* !default case */
		?>
		<td <?php echo $attributes ?>><?php do_action('manage_posts_custom_column', $column_name, $product->ID); ?></td>
		<?php
		break;
	}
}
?>
	</tr>
<?php
	$product = $global_product;
}

?>