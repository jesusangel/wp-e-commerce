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
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 style='_color: #ff0000;'><?php echo wp_specialchars( TXT_WPSC_DISPLAYPRODUCTS ); ?> </h2>
		<form id="posts-filter" action="" method="get">
			<div class="tablenav">
			
				<p class="search-box">
					<label class="hidden" for="page-search-input"><?php _e( 'Search Pages' ); ?>:</label>
					<input type="text" class="search-input" id="page-search-input" name="s" value="<?php _admin_search_query(); ?>" />
					<input type="submit" value="<?php _e( 'Search Pages' ); ?>" class="button" />
				</p>
			
			
				<div class="alignleft actions">
					<select name="action">
						<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
						<option value="edit"><?php _e('Edit'); ?></option>
						<option value="delete"><?php _e('Delete'); ?></option>
					</select>
				<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
				<?php wp_nonce_field('bulk-pages'); ?>
				</div>
			</div>
			<?php
				wpsc_admin_products_list($category_id);
			?>
		</form>
	</div>
	<?php
}


function wpsc_admin_products_list($category_id = 0) {
  global $wpdb,$_wp_column_headers;
  // set is_sortable to false to start with
  $is_sortable = false;
  
  
	if($category_id > 0) {    // if we are getting items from only one category, this is a monster SQL query to do this with the product order
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
		$itempp = 20;
		if ($_GET['pnum']!='all') {
			$page = (int)$_GET['pnum'];
			
			$start = $page * $itempp;
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`='1' $search_sql LIMIT $start,$itempp";
		} else {
			$sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`='1' $search_sql";
		}
	}  
			
	$product_list = $wpdb->get_results($sql,ARRAY_A);
	$num_products = $wpdb->get_var("SELECT COUNT(DISTINCT `id`) FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`='1' $search_sql");


  
  
	?>
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
			foreach($product_list as $product) {
			
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
				$category_html .= "<a class='category_link' href='?page=".$_GET['page']."&amp;catid=".$category_row['id']."'>".stripslashes($category_row['name'])."</a>";
				$i++;
			}        
							
				
				?>
					<tr class="product-edit" id="product-<?php echo $product['id']?>">
							<th class="check-column" scope="row"><input type='checkbox' name='product[]' class='deletecheckbox' value='<?php echo $product['id']?>' /></th>
							
							
							<td class="product-image ">
								<img title='Drag to a new position' src='<?php echo $image_path; ?>' title='<?php echo $product['name']; ?>' alt='<?php echo $product['name']; ?>' width='35' height='35' />
							</td>
							<td class="product-title column-title">
								<a href='#'><?php echo $product_name; ?></a>				
							
								<div class="wpsc-row-actions">
									<span class="edit">
										<a title="Edit this post" style="cursor:pointer;">Edit</a>
									</span> |
									<span class="delete">
									<?php
									/*
									<a onclick="if ( confirm(\'Are you sure to delete this product?\') ) { return true;}return false;" href="?page='.WPSC_DIR_NAME.'/display-items.php&deleteid='.$product['id'].'" title="Delete this product">Delete</a>
									*/
									?>
									</span> |
								<span class="view"><a target="_blank" rel="permalink" title='View <?php echo $product_name; ?>' href="<?php wpsc_product_url($product['id']); ?>">View</a></span> |
								<span class="view"><a rel="permalink" title='Duplicate <?php echo $product_name; ?>' href="#">Duplicate</a></span>
						   </div>
							</td>
							
							<td class="product-price column-price"><?php echo nzshpcrt_currency_display($product['price'], 1); ?></td>
							<td class="comments column-comments"><?php echo $category_html; ?></td>
					</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<?php
}



?>