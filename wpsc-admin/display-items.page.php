<?php
/**
 * WP eCommerce edit and add product page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_display_products_page() {
  global $wpdb;
	$category_id = absint($_GET['category_id']);
	
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'image' => '',
		'title' => 'Name',
		'price' => 'Price',
		'categories' => 'Categories',
	);
	register_column_headers('display-product-list', $columns);	
	
	$baseurl = includes_url('js/tinymce');

  ?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo wp_specialchars( TXT_WPSC_DISPLAYPRODUCTS ); ?> </h2>
		
		<?php if (isset($_GET['skipped']) || isset($_GET['updated']) || isset($_GET['deleted']) || isset($_GET['message']) || isset($_GET['duplicated'])) { ?>
			<div id="message" class="updated fade">
				<p>
				<?php if ( isset($_GET['updated'])) {
					printf( __ngettext( '%s product updated.', '%s products updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
					unset($_GET['updated']);
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
				
			if(count($unwriteable_directories) > 0) {
				echo "<div class='error fade'>".str_replace(":directory:","<ul><li>".implode($unwriteable_directories, "</li><li>")."</li></ul>",TXT_WPSC_WRONG_FILE_PERMS)."</div>";
			}
	?>
		
		<div id="col-container" class='stuffbox'>
			<div id="col-right">			
				<div id='poststuff' class="col-wrap">
					<form id="modify-products" method="post" action="" enctype="multipart/form-data" >
					<?php
						$product_id = absint($_GET['product_id']);
						wpsc_display_product_form($product_id);
					?>
					</form>
				</div>
			</div>
			
			<div id="col-left">
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
	columns.init('edit');
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
	
	$search_sql = "AND (`".WPSC_TABLE_PRODUCT_LIST."`.`name` LIKE '".$search_string_title."' OR `".WPSC_TABLE_PRODUCT_LIST."`.`description` LIKE '".$search_string_description."')";
	
	$search_string = $_GET['search'];
} else {
  $search_sql = '';
  $search_string = '';
}

	if($category_id > 0) {  // if we are getting items from only one category, this is a monster SQL query to do this with the product order
		$sql = "SELECT `products`.`id` , `products`.`name` , `products`.`price` , `products`.`image`, `categories`.`category_id`,`order`.`order`, IF(ISNULL(`order`.`order`), 0, 1) AS `order_state`
			FROM `".WPSC_TABLE_PRODUCT_LIST."` AS `products`
			LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` AS `categories` ON `products`.`id` = `categories`.`product_id` 
			LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` AS `order` ON ( 
				(	`products`.`id` = `order`.`product_id` )
			AND 
				( `categories`.`category_id` = `order`.`category_id` )
			)
			WHERE `products`.`active` = '1' $search_sql
			AND `categories`.`category_id` 
			IN (
			'".$category_id."'
			)
			ORDER BY `order_state` DESC,`order`.`order` ASC,  `products`.`id` DESC";
	  
		// if we are selecting a category, set is_sortable to true
		$is_sortable = true;
	} else {
		$itempp = 10;
		if ($_GET['pageno']!='all') {
		  if($_GET['pageno'] > 0) {
				$page = absint($_GET['pageno']);
		  } else {
		    $page = 1;
		  }
			
			$start = (int)($page * $itempp) - $itempp;
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`='1' $search_sql LIMIT $start,$itempp";
			
		} else {
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`='1' $search_sql";
		}
	}  
	$product_list = $wpdb->get_results($sql,ARRAY_A);
	$num_products = $wpdb->get_var("SELECT COUNT(DISTINCT `id`) FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`='1' $search_sql");
	
	if (isset($itempp)) {
		$num_pages = ceil($num_products/$itempp);
	}
	
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
	<div class="tablenav">
		
		<div class="alignright search-box">
		  
			<form action="" method="get">
				<input type='hidden' name='page' value='edit-products'  />
				<input type="text" class="search-input" id="page-search-input" name="search" value="<?php echo $_GET['search']; ?>" />
				<input type="submit" value="<?php _e( 'Search' ); ?>" class="button" />
			</form>
		</div>
		
		
		<div class="alignleft actions">
			<form action="" method="get">
				<?php
						echo wpsc_admin_category_dropdown();
				?>
			</form>
		</div>
		
	</div>
	
	
	<div class="tablenav">
		<?php
		?>
	
	
		<div class="alignleft actions">
			<select name="action">
				<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
				<option value="delete"><?php _e('Delete'); ?></option>
			</select>
		<input type='hidden' name='wpsc_admin_action' value='bulk_modify' />
		<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		<?php wp_nonce_field('bulk-products'); ?>
		</div>
		<div class="tablenav-pages">
			<?php
				echo $page_links;
			?>	
		</div>
	</div>
	
	<form id="posts-filter" action="" method="get">
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
				foreach((array)$product_list as $product) {
				
					if(($product['thumbnail_image'] != null) && file_exists(WPSC_THUMBNAIL_DIR.$product['thumbnail_image'])) { // check for custom thumbnail images
						$image_path = WPSC_THUMBNAIL_URL.$product['thumbnail_image'];
					} else if(($product['image'] != null) && file_exists(WPSC_THUMBNAIL_DIR.$product['image'])) { // check for automatic thumbnail images
						$image_path = WPSC_THUMBNAIL_URL.$product['image'];
					} else { // no image, display this fact
						$image_path = WPSC_URL."/images/no-image-uploaded.gif";
					}
					
					// get the  product name, unless there is no name, in which case, display text indicating so
					if ($product['name']=='') {
						$product_name = "(".TXT_WPSC_NONAME.")";
					} else {
						$product_name = htmlentities(stripslashes($product['name']), ENT_QUOTES, 'UTF-8');
					}
					
					
				$category_html = '';	
				$category_list = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id`,`".WPSC_TABLE_PRODUCT_CATEGORIES."`.`name` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` , `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` IN ('".$product['id']."') AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`id` AND `".WPSC_TABLE_PRODUCT_CATEGORIES."`.`active` IN('1')",ARRAY_A);
				$i = 0;
				foreach((array)$category_list as $category_row) {
					if($i > 0) {
						$category_html .= "<br />";
					}
					
					
					$category_html .= "<a class='category_link' href='". remove_query_arg('product_id',add_query_arg('category_id', $category_row['id']))."'>".stripslashes($category_row['name'])."</a>";
					$i++;
				}        
								
					
					?>
						<tr class="product-edit" id="product-<?php echo $product['id']?>">
								<th class="check-column" scope="row"><input type='checkbox' name='product[]' class='deletecheckbox' value='<?php echo $product['id'];?>' /></th>
								
								
								<td class="product-image ">
									<img title='Drag to a new position' src='<?php echo $image_path; ?>' title='<?php echo $product['name']; ?>' alt='<?php echo $product['name']; ?>' width='38' height='38' />
								</td>
								<td class="product-title column-title">
									<a class='edit-product' href='<?php echo add_query_arg('product_id', $product['id']); ?>'><?php echo $product_name; ?></a>
										<?php
										$product_alert = apply_filters('wpsc_product_alert', array(false, ''), $product);
										if(count($product_alert['messages']) > 0) {
											$product_alert['messages'] = implode("\n",(array)$product_alert['messages']);
										}
										if($product_alert['state'] === true) {
											?>
											<img alt='<?php echo $product_alert['messages'];?>' title='<?php echo $product_alert['messages'];?>' class='product-alert-image' src='<?php echo  WPSC_URL;?>/images/product-alert.jpg' alt='' title='' />
											<?php
										}
										?>
								
								
								
									<div class="wpsc-row-actions">
										<span class="edit">
											<a class='edit-product' title="Edit this post" href='<?php echo add_query_arg('product_id', $product['id']); ?>' style="cursor:pointer;">Edit</a>
										</span> |
										<span class="delete">
											<a class='submitdelete' title='<?php echo attribute_escape(__('Delete this product')); ?>' href='<?php echo wp_nonce_url("page.php?wpsc_admin_action=delete_product&amp;product={$product['id']}", 'delete_product_' . $product['id']); ?>' onclick="if ( confirm(' <?php echo js_escape(sprintf( __("You are about to delete this product '%s'\n 'Cancel' to stop, 'OK' to delete."), $product['name'] )) ?>') ) { return true;}return false;"><?php _e('Delete') ?></a>
										</span> |
									<span class="view"><a target="_blank" rel="permalink" title='View <?php echo $product_name; ?>' href="<?php echo wpsc_product_url($product['id']); ?>">View</a></span> |
									<span class="view"><a rel="permalink" title='Duplicate <?php echo $product_name; ?>' href="<?php echo wp_nonce_url("page.php?wpsc_admin_action=duplicate_product&amp;product={$product['id']}", 'duplicate_product_' . $product['id']); ?>">Duplicate</a></span>
								</div>
								</td>
								
								<td class="product-price column-price"><?php echo nzshpcrt_currency_display($product['price'], 1); ?></td>
								<td class="column-categories"><?php echo $category_html; ?></td>
						</tr>
					<?php
				}
				?>			
			</tbody>
		</table>
	</form>
	<?php
}

//function 

function wpsc_admin_category_dropdown() {
	global $wpdb,$category_data;
	$siteurl = get_option('siteurl');
	$url =  remove_query_arg(array('product_id','category_id'));
	$options = "";
	$options .= "<option value='$url'>".TXT_WPSC_ALLCATEGORIES."</option>\r\n";
	$options .= wpsc_admin_category_dropdown_tree(null, 0, absint($_GET['category_id']));
	$concat .= "<select name='category' id='category_select'>".$options."</select>\r\n";
	$concat .= "<button class='button' id='submit_category_select'>Filter</button>";
	return $concat;
}

function wpsc_admin_category_dropdown_tree($category_id = null, $iteration = 0, $selected_id = null) {
  /*
   * Displays the category forms for adding and editing products
   * Recurses to generate the branched view for subcategories
   */
  global $wpdb;
  $siteurl = get_option('siteurl');
  $url = $siteurl."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-items.php";
  if(is_numeric($category_id)) {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '$category_id'  ORDER BY `id` ASC",ARRAY_A);
	} else {
    $values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '0'  ORDER BY `id` ASC",ARRAY_A);
	}
  foreach((array)$values as $option) {
    if($selected_id == $option['id']) {
      $selected = "selected='selected'";
    }
    $url = remove_query_arg('product_id',add_query_arg('category_id', $option['id']));
    $output .= "<option $selected value='$url'>".str_repeat("-", $iteration).stripslashes($option['name'])."</option>\r\n";
    $output .= wpsc_admin_category_dropdown_tree($option['id'], $iteration+1, $selected_id);
    $selected = "";
  }
  return $output;
}

?>