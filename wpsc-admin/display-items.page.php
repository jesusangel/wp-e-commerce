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
		'title' => 'Name',
		'price' => 'Price',
		'categories' => 'Categories',
	);
	register_column_headers('display-product-list', $columns);	
	
	
	wpsc_admin_products_list();

}


function wpsc_admin_products_list($category_id) {
  global $wpdb,$_wp_column_headers;
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
	<table class="widefat page fixed" cellspacing="0">
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
		<?php page_rows($posts, $pagenum, $per_page); ?>
		</tbody>
	</table><?php

}



?>