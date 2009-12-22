<?php
/**
 * WP eCommerce edit and add product page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

require_once(WPSC_FILE_PATH.'/wpsc-admin/includes/products.php');


function wpsc_image_downsize($id, $size) {
	echo "<pre>".print_r(func_get_args(),true)."</pre>";
	exit();

}

//add_filter('image_downsize', 'wpsc_image_downsize',2,3);




function wpsc_display_edit_products_page() {
  global $wpdb, $wp_query, $wpsc_products;
	$category_id = absint($_GET['category_id']);
	
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'image' => 'Name',
		'title' => '',
		'price' => 'Price',
		'categories' => 'Categories',
	);
	register_column_headers('display-product-list', $columns);	
	
	$baseurl = includes_url('js/tinymce');

  ?>
	<div class="wrap">
		<?php // screen_icon(); ?>
		<h2><?php echo wp_specialchars( __('Display Products', 'wpsc') ); ?> </h2>
		
		<?php if(isset($_GET['ErrMessage']) && is_array($_SESSION['product_error_messages'])){ ?>
				<div id="message" class="error fade">
					<p>
						<?php
						foreach($_SESSION['product_error_messages'] as $error) {
							echo $error;
						}
						?>
					</p>
				</div>
				<?php 	unset($_GET['ErrMessage']); ?>
				<?php $_SESSION['product_error_messages'] = ''; ?>
		<?php } ?>
			
		<?php if (isset($_GET['flipped']) || isset($_GET['skipped']) || isset($_GET['updated']) || isset($_GET['deleted']) || isset($_GET['message']) || isset($_GET['duplicated']) ) { ?>
			<div id="message" class="updated fade">
				<p>
				<?php if ( isset($_GET['updated'])) {
					printf( __ngettext( '%s product updated.', '%s products updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
					unset($_GET['updated']);
				}
				
				if ( isset($_GET['flipped'])) {
					printf( __ngettext( '%s product updated.', '%s products updated.', $_GET['flipped'] ), number_format_i18n( $_GET['flipped'] ) );
					unset($_GET['flipped']);
				}
				
				if ( isset($_GET['skipped'])) {
					unset($_GET['skipped']);
				}
				
				if ( isset($_GET['deleted'])) {
					printf( __ngettext( 'Product deleted.', '%s products deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
					unset($_GET['deleted']);
				}
				
				if ( isset($_GET['duplicated']) ) {
					printf( __ngettext( 'Product duplicated.', '%s products duplicated.', $_GET['duplicated'] ), number_format_i18n( $_GET['duplicated'] ) );
					unset($_GET['duplicated']);
				}
				
				if ( isset($_GET['message']) ) {
					$message = absint( $_GET['message'] );
					$messages[1] =  __( 'Product updated.' );
					echo $messages[$message];			
					unset($_GET['message']);
				}
				
				
				$_SERVER['REQUEST_URI'] = remove_query_arg( array('locked', 'skipped', 'updated', 'deleted', 'message', 'duplicated'), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
		<?php } ?>
		
		<?php		 
			$unwriteable_directories = Array();
			
			if(!is_writable(WPSC_FILE_DIR)) {
				$unwriteable_directories[] = WPSC_FILE_DIR;
			}
			
			if(!is_writable(WPSC_PREVIEW_DIR)) {
				$unwriteable_directories[] = WPSC_PREVIEW_DIR;
			}
		
			if(!is_writable(WPSC_IMAGE_DIR)) {
				$unwriteable_directories[] = WPSC_IMAGE_DIR;
			}
			
			if(!is_writable(WPSC_THUMBNAIL_DIR)) {
				$unwriteable_directories[] = WPSC_THUMBNAIL_DIR;
			}
			
			if(!is_writable(WPSC_CATEGORY_DIR)) {
				$unwriteable_directories[] = WPSC_CATEGORY_DIR;
			}
			
			if(!is_writable(WPSC_UPGRADES_DIR)) {
				$unwriteable_directories[] = WPSC_UPGRADES_DIR;
			}
				
			if(count($unwriteable_directories) > 0) {
				echo "<div class='error fade'>".str_replace(":directory:","<ul><li>".implode($unwriteable_directories, "</li><li>")."</li></ul>",__('The following directories are not writable: :directory: You won&#39;t be able to upload any images or files here. You will need to change the permissions on these directories to make them writable.', 'wpsc'))."</div>";
			}
			// class='stuffbox'
	?>
		
		<div id="col-container">
			<div id="wpsc-col-right">			
				<div id='poststuff' class="col-wrap">
					<form id="modify-products" method="post" action="" enctype="multipart/form-data" >
					<?php
						$product_id = absint($_GET['product']);
						wpsc_display_product_form($product_id);
					?>
					</form>
				</div>
			</div>
			
			<div id="wpsc-col-left">
				<div class="col-wrap">		
					<?php
						wpsc_admin_products_list($category_id);
					?>
				</div>
			</div>
		</div>

	</div>
	<script type="text/javascript">
	/* <![CDATA[ */
	(function($){
		$(document).ready(function(){
			$('#doaction, #doaction2').click(function(){
				if ( $('select[name^="action"]').val() == 'delete' ) {
					var m = '<?php echo js_escape(__("You are about to delete the selected products.\n  'Cancel' to stop, 'OK' to delete.")); ?>';
					return showNotice.warn(m);
				}
			});
		});
	})(jQuery);
	/* ]]> */
	</script>
	<?php
}


function wpsc_admin_products_list($category_id = 0) {
  global $wpdb,$_wp_column_headers;
  // set is_sortable to false to start with
  $is_sortable = false;
  $page = null;
  
	if($_GET['search']) {
		$search_string_title = "%".$wpdb->escape(stripslashes($_GET['search']))."%";
		$search_string_description = "% ".$wpdb->escape(stripslashes($_GET['search']))."%";
		
		$search_sql = "AND (`products`.`name` LIKE '".$search_string_title."' OR `products`.`description` LIKE '".$search_string_description."')";
		
		$search_string = $_GET['search'];
	} else {
		$search_sql = '';
		$search_string = '';
	}

	$search_sql = apply_filters('wpsc_admin_products_list_search_sql', $search_sql);

	$query = array('post_type' => 'wpsc-product', 'orderby' => 'menu_order title',
	'posts_per_page' => -1, 'posts_per_archive_page' => -1, 'order' => 'asc');

	wp($query);
	
	if($page !== null) {
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'pageno', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $num_pages,
			'current' => $page
		));
	}
	$this_page_url = stripslashes($_SERVER['REQUEST_URI']);
  
	?>
	<div class="wpsc-separator"><br/></div>
	
	<div class="tablenav">
		<div class="tablenav-pages">
			<?php
				echo $page_links;
			?>	
		</div>
		
		<div class="alignleft actions">
			<form action="admin.php" method="get">
				<?php
					echo wpsc_admin_category_dropdown();
				?>
			</form>
		</div>	
	</div>
	
	
	<form id="posts-filter" action="" method="get">
		<div class="tablenav">	
			<div class="alignright search-box">
				<input type='hidden' name='page' value='wpsc-edit-products'  />
				<input type="text" class="search-input" id="page-search-input" name="search" value="<?php echo $_GET['search']; ?>" />
				<input type="submit" name='wpsc_search' value="<?php _e( 'Search' ); ?>" class="button" />
			</div>
		
			<div class="alignleft actions">
					<select name="bulkAction">
						<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
						<option value="delete"><?php _e('Delete'); ?></option>
						<option value="show"><?php _e('Publish'); ?></option>
						<option value="hide"><?php _e('Draft'); ?></option>

					</select>
					<input type='hidden' name='wpsc_admin_action' value='bulk_modify' />
					<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
					<?php wp_nonce_field('bulk-products', 'wpsc-bulk-products'); ?>
			</div>
		</div>
	
		<input type='hidden' id='products_page_category_id'  name='category_id' value='<?php echo $category_id; ?>' />
		<table class="widefat page fixed" id='wpsc_product_list' cellspacing="0">
			<thead>
				<tr>
					<?php print_column_headers('display-product-list'); ?>
				</tr>
			</thead>
		
			<tfoot>
				<tr>
					<?php print_column_headers('display-product-list', false); ?>
				</tr>
			</tfoot>
		
			<tbody>
				<?php
				wpsc_admin_product_listing();
				if(count($wpsc_products) < 1) {
				?>
				<tr>
					<td colspan='5'>
					  <?php _e("You have no products added."); ?>
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
	global $wpdb,$category_data;
	$siteurl = get_option('siteurl');
	$url =  urlencode(remove_query_arg(array('product_id','category_id')));
	
	$options = "<option value=''>".__('View All Categories', 'wpsc')."</option>\r\n";
	$options .= wpsc_admin_category_dropdown_tree(null, 0, absint($_GET['category_id']));
	
	$concat = "<input type='hidden' name='page' value='{$_GET['page']}' />\r\n";
	$concat .= "<select name='category_id' id='category_select'>".$options."</select>\r\n";
	$concat .= "<button class='button' id='submit_category_select'>Filter</button>\r\n";
	return $concat;
}

function wpsc_admin_category_dropdown_tree($category_id = null, $iteration = 0, $selected_id = null) {
		/*
   * Displays the category forms for adding and editing products
   * Recurses to generate the branched view for subcategories
   */
  global $wpdb;
  $siteurl = get_option('siteurl');
  $url = $siteurl."/wp-admin/admin.php?page=wpsc-edit-products";

	$search_sql = apply_filters('wpsc_admin_category_dropdown_tree_search_sql', '');

  if(is_numeric($category_id)) {
    $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '$category_id' ".$search_sql." ORDER BY `id` ASC";
	} else {
    $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '0' ".$search_sql." ORDER BY `id` ASC";
	}

//	echo $sql;
  $values = $wpdb->get_results($sql, ARRAY_A);

  foreach((array)$values as $option) {
    if($selected_id == $option['id']) {
      $selected = "selected='selected'";
    }
    //$url = htmlentities(remove_query_arg('product_id',add_query_arg('category_id', $option['id'])));
    $output .= "<option $selected value='{$option['id']}'>".str_repeat("-", $iteration).stripslashes($option['name'])."</option>\r\n";
    $output .= wpsc_admin_category_dropdown_tree($option['id'], $iteration+1, $selected_id);
    $selected = "";
  }
  return $output;
}

?>