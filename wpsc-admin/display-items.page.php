<?php
/**
 * WP eCommerce edit and add product page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
require_once(WPSC_FILE_PATH . '/wpsc-admin/includes/products.php');


/*function wpsc_image_downsize( $id, $size ) {
	echo "<pre>" . print_r( func_get_args(), true ) . "</pre>";
	exit();
}
*/
//add_filter('image_downsize', 'wpsc_image_downsize',2,3);

function wpsc_display_edit_products_page() {
	global $wpdb, $wp_query, $wpsc_products;

	$category_id = 0;
	if ( isset( $_GET['category_id'] ) )
		$category_id = absint( $_GET['category_id'] );

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'image' => '',
		'title' => 'Name',
		'weight' => 'Weight',
		'stock' => 'Stock',
		'price' => 'Price',
		'sale_price' => 'Sale Price',
		'SKU' => 'SKU',
		'categories' => 'Categories',
		'featured' => 'Featured',
	);
	if ( isset( $_GET["product"] ) && $_GET["product"] != '' ) {
		unset( $columns["categories"] );
	}
	$columns = apply_filters( 'manage_display-product-list_columns', $columns );

	register_column_headers( 'display-product-list', $columns );

	$baseurl = includes_url( 'js/tinymce' );
?>
	<div class="wrap">
<?php // screen_icon();  ?>
		<div id="icon_card"><br /></div>
		<h2>
			<a href="admin.php?page=wpsc-edit-products" class="nav-tab nav-tab-active" id="manage"><?php echo esc_html( __( 'Manage Products', 'wpsc' ) ); ?></a>
			<a href="<?php echo wp_nonce_url( "admin.php?page=wpsc-edit-products&action=wpsc_add_edit", "_add_product" ); ?>" class="nav-tab" id="add"><?php  _e( 'Add New', 'wpsc' ); ?></a>
	</h2>
<?php if ( isset( $_GET['ErrMessage'] ) && isset( $_SESSION['product_error_messages'] ) && is_array( $_SESSION['product_error_messages'] ) ) { ?>
		<div id="message" class="error fade">
			<p>
<?php
		foreach ( $_SESSION['product_error_messages'] as $error ) {
			echo $error;
		}
?>
		</p>
	</div>
			<?php unset( $_GET['ErrMessage'] ); ?>
			<?php $_SESSION['product_error_messages'] = ''; ?>
<?php } ?>

	<?php if ( isset( $_GET['addedgroup'] ) || isset( $_GET['published'] ) || isset( $_GET['skipped'] ) || isset( $_GET['updated'] ) || isset( $_GET['deleted'] ) || isset( $_GET['message'] ) || isset( $_GET['duplicated'] ) ) {
 ?>
			<div id="message" class="updated fade">
				<p>
	<?php
			//Not sure when or why this was added...seems to be the culprit for the constant deletion notice.  Just commenting out in case it's actually necessary.
//   			if(!isset($_GET['deleted'])) $_GET['deleted'] = 0.00;

			if ( isset( $_GET['updated'] ) ) {
				printf( _n( '%s product updated.', '%s products updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
				unset( $_GET['updated'] );
			}

			if ( isset( $_GET['addedgroup'] ) ) {
				if(is_int($_GET['addedgroup']) && $_GET['addedgroup'] > 0){
					printf( _n( '%s product updated.', '%s products updated.', $_GET['addedgroup'] ), number_format_i18n( $_GET['addedgroup'] ) );
				}else{
					printf( _n( 'Invalid Category Selected.', 'Invalid Category Selected.' ) );
				}
				unset( $_GET['addedgroup'] );
			}

			if ( isset( $_GET['published'] ) ) {
				printf( _n( '%s product updated.', '%s products updated.', $_GET['published'] ), number_format_i18n( $_GET['published'] ) );
				unset( $_GET['published'] );
			}


			if ( isset( $_GET['skipped'] ) ) {
				unset( $_GET['skipped'] );
			}

			if ( isset( $_GET['deleted'] ) ) {
				printf( _n( 'Product deleted.', '%s products deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
				unset( $_GET['deleted'] );
			}
			if ( isset( $_GET['trashed'] ) && isset( $_GET['deleted'] ) ) {
				printf( _n( 'Product trashed.', '%s products deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
				unset( $_GET['trashed'] );
			}

			if ( isset( $_GET['duplicated'] ) ) {
				printf( _n( 'Product duplicated.', '%s products duplicated.', $_GET['duplicated'] ), number_format_i18n( $_GET['duplicated'] ) );
				unset( $_GET['duplicated'] );
			}

			if ( isset( $_GET['message'] ) ) {
				$message = absint( $_GET['message'] );
				$messages[1] = __( 'Product updated.' );
				echo $messages[$message];
				unset( $_GET['message'] );
			}

			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'skipped', 'updated', 'deleted', 'message', 'duplicated', 'trashed' ), $_SERVER['REQUEST_URI'] );
	?>
		</p>
	</div>
			<?php } ?>

<?php
		$unwriteable_directories = Array( );

		if ( !is_writable( WPSC_FILE_DIR ) ) {
			$unwriteable_directories[] = WPSC_FILE_DIR;
		}

		if ( !is_writable( WPSC_PREVIEW_DIR ) ) {
			$unwriteable_directories[] = WPSC_PREVIEW_DIR;
		}

		if ( !is_writable( WPSC_IMAGE_DIR ) ) {
			$unwriteable_directories[] = WPSC_IMAGE_DIR;
		}

		if ( !is_writable( WPSC_THUMBNAIL_DIR ) ) {
			$unwriteable_directories[] = WPSC_THUMBNAIL_DIR;
		}

		if ( !is_writable( WPSC_CATEGORY_DIR ) ) {
			$unwriteable_directories[] = WPSC_CATEGORY_DIR;
		}

		if ( !is_writable( WPSC_UPGRADES_DIR ) ) {
			$unwriteable_directories[] = WPSC_UPGRADES_DIR;
		}

		if ( count( $unwriteable_directories ) > 0 ) { 
			// remember the user that the uploads directory needs to be writable
			echo "<div class='error fade'>".WPSC_UPLOAD_ERR."<br/>";
			
			echo str_replace( ":directory:", "<ul><li>" . implode( $unwriteable_directories, "</li><li>" ) . "</li></ul>", __( 'The following directories are not writable: :directory: You won&#39;t be able to upload any images or files here. You will need to change the permissions on these directories to make them writable.', 'wpsc' ) ) . "</div>";
		}
		// class='stuffbox'
		// Justin Sainton - 5.7.2010 - Re-ordered columns, applying jQuery to toggle divs on click.
?>	
		<script type="text/javascript">
			/* <![CDATA[ */
			(function($){
				$(document).ready(function(){

					$('#doaction, #doaction2').click(function(){
						if ( $('select[name^="action"]').val() == 'delete' ) {
							var m = '<?php echo esc_js( __( "You are about to delete the selected products.\n  'Cancel' to stop, 'OK' to delete." ) ); ?>';
							return showNotice.warn(m);
						}
					});
					$('form#filt_cat').insertAfter('input#doaction').css("display", "inline")
				});
			})(jQuery);
			/* ]]> */
		</script>
<?php
		if ( !isset( $_GET["action"] ) )
			$_GET["action"] = '';
		if ( ($_GET["action"] != "wpsc_add_edit" ) ) {
?>
			<div id="wpsc-col-left">
				<div class="col-wrap">
<?php
			wpsc_admin_products_list( $category_id );
?>
		</div>
	</div>
<?php } else { ?>
	<script type="text/javascript">
		/* <![CDATA[ */
		(function($){
			$(document).ready(function(){
		
				jQuery('#wpsc-col-left').hide();
				<?php
				if(isset($_GET['product']) && $_GET['product'] <= 0){?>
				jQuery('a#add').addClass('nav-tab-active');
				jQuery('a#manage').removeClass('nav-tab-active');
				<?php 
				} ?>
			});
		})(jQuery);
		/* ]]> */
	</script>
	<div id="wpsc-col-right">
<?php
			global $screen_layout_columns;
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>
			<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
				<form id="modify-products" method="post" action="" enctype="multipart/form-data" >
<?php
			$product_id = 0;
			if ( isset( $_GET['product'] ) )
				$product_id = absint( $_GET['product'] );
			wpsc_display_product_form( $product_id );
?>
			</form>
		</div>
	</div>
<?php } ?>
</div>

<?php
	}

	/*
	 * wpsc_edit_variations_request_sql function, modifies the wp-query SQL statement for displaying variations
	 * @todo will need refinement later to work with pagionation
	 * @param $sql
	 * @returns string - SQL statement
	 */

	function wpsc_edit_variations_request_sql( $sql ) {
		global $wpdb;

		if ( is_numeric( $_GET['product'] ) ) {
			$parent_product = absint( $_GET['product'] );
			$product_term_data = wp_get_object_terms( $parent_product, 'wpsc-variation' );

			$parent_terms = array( );
			$child_terms = array( );
			foreach ( $product_term_data as $product_term_row ) {
				if ( $product_term_row->parent == 0 ) {
					$parent_terms[] = $product_term_row->term_id;
				} else {
					$child_terms[] = $product_term_row->term_id;
				}
			}

			if ( count( $parent_terms ) > 0 ) {
				//echo "<pre>".print_r($parent_terms, true)."</pre>";
				//echo $sql;
				$term_count = count( $parent_terms );
				$child_terms = implode( ", ", $child_terms );

				$parent_terms = implode( ", ", $parent_terms );
				$new_sql = "SELECT posts.*, COUNT(tr.object_id) AS `count`
			FROM {$wpdb->term_relationships} AS tr
			INNER JOIN {$wpdb->posts} AS posts
			ON posts.ID = tr.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.taxonomy IN ('wpsc-variation')
			AND tt.parent IN ({$parent_terms})
			AND tt.term_id IN ({$child_terms})
			AND posts.post_parent = {$parent_product}
			
			GROUP BY tr.object_id
			HAVING `count` = {$term_count}";
				//echo "<br /><br />". $new_sql;
				return $new_sql;
			}
		}


		return $sql;
	}

	function wpsc_admin_products_list( $category_id = 0 ) {
		global $wp_query, $wpdb, $_wp_column_headers;
		// set is_sortable to false to start with
		$is_sortable = false;
		$search_sql = '';
		$page = null;
		// Justin Sainton - 5.11.2010 - Re-included these variables from 3.7.6.1, as they appear to have been removed.  Necessary for pagination.  Also re-wrote query for new table structure.
		$itempp = 20;
		if ( isset( $_POST['product'] ) && (is_numeric( $_POST['product'] )) ) 
			$parent_product = absint( $_POST['product'] );
		
		$search_input = '';
		if ( isset( $_POST['search'] ) ) {
			$search_input = stripslashes( $_POST['search'] );

			$search_string = "%" . $wpdb->escape( $search_input ) . "%";

			$search_sql = "AND (`products`.`name` LIKE '" . $search_string . "' OR `products`.`description` LIKE '" . $search_string . "')";
		} else {
			$search_sql = '';
		}

		$search_sql = apply_filters( 'wpsc_admin_products_list_search_sql', $search_sql );

		if ( isset( $_POST['pageno'] ) && ($_POST['pageno'] > 0) ) {
			$page = absint( $_POST['pageno'] );
		} else {
			$page = 1;
		}
		$start = (int)($page * $itempp) - $itempp;
		$all_products = '';
		$trash_products = '';
		$draft_products = '';
		if('trash' == $_REQUEST['post_status']){
			$post_status = 'trash';
			$trash_products = 'class="current"';
		}elseif('draft' == $_REQUEST['post_status']){
			$post_status = 'draft';
			$draft_products = 'class="current"';
		
		}else{
			$all_products = 'class="current"';			
			$post_status = 'published inherit';
		}
		
		$query = array(
			'post_type' => 'wpsc-product',
			'post_parent' => 0,
			'orderby' => 'menu_order post_title',
			'order' => "ASC",
			'post_status' => $post_status,
			'posts_per_page' => $itempp,
			'offset' => $start
		);

		if ( isset( $_POST['category'] ) ) {
			$category_id = $_POST['category'];
			$query['wpsc_product_category'] = $category_id;
		}


		if ( isset( $_POST['search'] ) && (strlen( $_POST['search'] ) > 0 ) ) {
			$search = $_POST['search'];
			$query['s'] = $search;
		}
		$wp_query = new WP_Query( $query );

		if ( isset( $itempp ) )
			$num_pages = ceil( $wp_query->found_posts / $itempp );

		remove_filter( 'posts_request', 'wpsc_edit_variations_request_sql' );

		if ( $page !== null ) {
			$page_links = paginate_links( array(
						'base' => add_query_arg( 'pageno', '%#%' ),
						'format' => '',
						'prev_text' => __( '&laquo;' ),
						'next_text' => __( '&raquo;' ),
						'total' => $num_pages,
						'current' => $page
					) );
		}

		$this_page_url = stripslashes( $_SERVER['REQUEST_URI'] );

		$is_trash = isset( $_POST['post_status'] ) && $_POST['post_status'] == 'trash';

		// Justin Sainton - 5.7.2010 - Added conditional code below as blank space would show up if $page_links was NULL.  Now the area only shows up if page links exist.
?>	

<?php if ( $page_links && get_option( 'wpsc_sort_by' ) != 'dragndrop' ) {
 ?>
			<div class="tablenav">
				<div class="tablenav-pages">
<?php
			echo $page_links;
?>	
				</div>
			</div>
<?php } ?>	
		<form action="admin.php?page=wpsc-edit-products" method="post" id="filt_cat">
		<?php
		echo wpsc_admin_category_dropdown();
		?>
</form>
<form id="search-products" action="admin.php?page=wpsc-edit-products" method="post">
	<div class="alignright search-box">
			<input type='hidden' name='page' value='wpsc-edit-products'  />
			<input type="text" class="search-input" id="page-search-input" name="search" value="<?php echo $search_input; ?>" />
			<input type="submit" name='wpsc_search' value="<?php _e( 'Search Products' ); ?>" class="button" />
		</div>
		</form>
<form id="posts-filter" action="admin.php?page=wpsc-edit-products" method="post">
	<ul class='subsubsub'>
		<li><a <?php echo $all_products; ?> href='<?php echo add_query_arg('post_status','all'); ?>' title='View All Products'><?php _e('All','wpsc'); ?></a></li>
		<li> | </li>
		<li><a <?php echo $draft_products; ?> href='<?php echo add_query_arg('post_status','draft'); ?>' title='View Draft Products'><?php _e('Draft','wpsc'); ?></a></li>
		<li> | </li>
		<li><a <?php echo $trash_products; ?> href='<?php echo add_query_arg('post_status','trash'); ?>' title='View Trashed Products'><?php _e('Trash','wpsc'); ?></a></li>
	</ul>
	<br />
	<br />
	<div class="productnav">
		<div class="alignleft actions">

			<select id="bulkaction" name="bulkAction">
				<option value="-1" selected="selected"><?php _e( 'Bulk Actions' ); ?></option>
					<option value="addgroup"><?php _e( 'Add To Group' ); ?></option>
					<option value="publish"><?php _e( 'Publish', 'wpsc' ); ?></option>
					<option value="unpublish"><?php _e( 'Unpublish', 'wpsc' ); ?></option>
<?php if ( $is_trash ) { ?>
						<option value="untrash"><?php _e( 'Restore' ); ?></option>
<?php } ?>
						<option value="delete"><?php _e( 'Delete Permanently' ); ?></option>

						<option value="trash"><?php _e( 'Move to Trash' ); ?></option>



				</select>
				<?php 
					$options = "<option selected='selected' value=''>" . __( 'Select a Category', 'wpsc' ) . "</option>\r\n";
					$options .= wpsc_list_categories( 'wpsc_admin_category_options_byid' );
					echo  "<select name='category' id='category_select'>" . $options . "</select>\r\n";
				?>

				<input type='hidden' name='wpsc_admin_action' value='bulk_modify' />
				<input type="submit" value="<?php _e( 'Apply' ); ?>" name="doaction" id="doaction" class="button-secondary action" />
<?php wp_nonce_field( 'bulk-products', 'wpsc-bulk-products' ); ?>
		</div>

	
	</div>

	<input type='hidden' id='products_page_category_id'  name='category_id' value='<?php echo $category_id; ?>' />
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
		if ( !isset( $parent_product_data ) )
			$parent_product_data = null;

		wpsc_admin_product_listing( $parent_product_data );
		//echo "<pre>".print_r($wp_query, true)."</pre>";
		if ( count( $wp_query->posts ) < 1 ) {
?>
			<tr>
				<td colspan='5'>
			<?php _e( "You have no products added." ); ?>
				</td>
			</tr>
			<?php
		}
			?>			
		</tbody>
	</table>
</form>
<?php
}

function wpsc_admin_category_dropdown() {
	global $wpdb, $category_data;
	$siteurl = get_option( 'siteurl' );
	$category_slug = '';
	if ( isset( $_POST['category'] ) )
		$category_slug = $_POST['category'];

	$selected ='';
	if(empty($category_slug))
		$selected = 'selected="selected"';
	$url = urlencode( remove_query_arg( array( 'product_id', 'category_id' ) ) );

	$options = "<option {$selected} value=''>" . __( 'View All Categories', 'wpsc' ) . "</option>\r\n";

	$options .= wpsc_list_categories( 'wpsc_admin_category_options', $category_slug );

	$concat = "<input type='hidden' name='page' value='{$_POST['page']}' />\r\n";
	$concat .= "<select name='category' id='category_select'>" . $options . "</select>\r\n";
	$concat .= "<input type='submit' value='Filter' class='button-secondary action' id='post-query-submit' />\r\n";
	return $concat;
}

/*
 * Displays the category forms for adding and editing products
 * Recurses to generate the branched view for subcategories
 */

function wpsc_admin_category_options( $category, $subcategory_level = 0, $category_slug = null ) {

	if ( $category_slug == $category->slug ) {

		$selected = "selected='selected'";

	} else {

		$selected = '';

	}

	$output = "<option $selected value='{$category->slug}'>" . str_repeat( "-", $subcategory_level ) . stripslashes( $category->name ) . "</option>\n";

	return $output;
}
/*
 * Displays the category forms for adding and editing products
 * Recurses to generate the branched view for subcategories
 */

function wpsc_admin_category_options_byid( $category, $subcategory_level = 0 ) {


        $output = "<option $selected value='{$category->term_id}'>" . str_repeat( "-", $subcategory_level ) . stripslashes( $category->name ) . "</option>\n";

        return $output;
}

/**
 * wpsc_update_featured_products function.
 *
 * @access public
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
	//$sendback = add_query_arg('featured', "1", wp_get_referer());
	wp_redirect( wp_get_referer() );
	exit();
}

if ( isset( $_REQUEST['wpsc_admin_action'] ) && ($_REQUEST['wpsc_admin_action'] == 'update_featured_product') ) {
	add_action( 'admin_init', 'wpsc_update_featured_products' );
}

/**
 * wpsc_featured_products_toggle function.
 *
 * @access public
 * @param mixed $product_id
 * @return void
 */
function wpsc_featured_products_toggle( $product_id ) {
	global $wpdb;
	$featured_product_url = wp_nonce_url( "index.php?wpsc_admin_action=update_featured_product&amp;product_id=$product_id", 'feature_product_' . $product_id );
?>
	<a class="wpsc_featured_product_toggle featured_toggle_<?php echo $product_id; ?>" href='<?php echo $featured_product_url; ?>' >
<?php if ( in_array( $product_id, (array)get_option( 'sticky_products' ) ) ) : ?>
		<img class='gold-star' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/gold-star.gif' alt='<?php _e( 'Unmark as Featured', 'wpsc' ); ?>' title='<?php _e( 'Unmark as Featured', 'wpsc' ); ?>' />
<?php else: ?>
		<img class='grey-star' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/grey-star.gif' alt='<?php _e( 'Mark as Featured', 'wpsc' ); ?>' title='<?php _e( 'Mark as Featured', 'wpsc' ); ?>' />
<?php endif; ?>
	</a>
<?php
}
add_action( 'manage_posts_featured_column', 'wpsc_featured_products_toggle', 10, 1 );
?>
