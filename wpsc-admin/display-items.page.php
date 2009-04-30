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
	
// 	wpsc_modify_products();
	
	$baseurl = includes_url('js/tinymce');
  
  ?>
<script type="text/javascript" src="<?php echo $baseurl; ?>/tiny_mce.js"></script>
<script type="text/javascript" src="<?php echo $baseurl; ?>/langs/wp-langs-en.js"></script>
<script language='javascript' type='text/javascript'>
	/* <![CDATA[ */
tinyMCE.init({
	theme : "advanced",
	mode : "specific_textareas",
	width : '100%',
	height : '194px',
	skin : 'wp_theme',
	editor_selector : "mceEditor",
	plugins : "spellchecker,pagebreak",
	theme_advanced_buttons1 : "bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,pagebreak",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	content_css : WPSC_URL+"/js/tinymce3/mce.css",
	theme_advanced_resize_horizontal : false
});
	/* ]]> */
</script>
	
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 style='_color: #ff0000;'><?php echo wp_specialchars( TXT_WPSC_DISPLAYPRODUCTS ); ?> </h2>
		
		<?php if (isset($_GET['skipped']) || isset($_GET['updated']) || isset($_GET['deleted']) ) { ?>
			<div id="message" class="updated fade"><p>
			<?php if ( isset($_GET['updated']) && (int) $_GET['updated'] ) {
				printf( __ngettext( '%s product updated.', '%s products updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
				unset($_GET['updated']);
			}
			
			if ( isset($_GET['skipped']) && (int) $_GET['skipped'] )
				unset($_GET['skipped']);
			
			if ( isset($_GET['locked']) && (int) $_GET['locked'] ) {
				printf( __ngettext( '%s product not updated, somebody is editing it.', '%s products not updated, somebody is editing them.', $_GET['locked'] ), number_format_i18n( $_GET['locked'] ) );
				unset($_GET['locked']);
			}
			
			if ( isset($_GET['deleted']) && (int) $_GET['deleted'] ) {
				printf( __ngettext( 'Product deleted.', '%s products deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
				unset($_GET['deleted']);
			}
			
			$_SERVER['REQUEST_URI'] = remove_query_arg( array('locked', 'skipped', 'updated', 'deleted'), $_SERVER['REQUEST_URI'] );
			?>
		</p></div>
		<?php } ?>
		
		
		<div id="col-container" class='stuffbox'>
			<div id="col-right">
				<div class="col-wrap">
					<form id="modify-products" method="post" action="">
					<?php
						$product_id = absint($_GET['product_id']);
						wpsc_display_product_form($product_id);
					?>
					</form>
				</div>
			</div>
			
			<div id="col-left">
				<div class="col-wrap">		
					<form id="posts-filter" action="" method="get">
						<div class="tablenav">
						  <?php
						  /*
							<p class="search-box">
								<label class="hidden" for="page-search-input"><?php _e( 'Search Pages' ); ?>:</label>
								<input type="text" class="search-input" id="page-search-input" name="s" value="<?php _admin_search_query(); ?>" />
								<input type="submit" value="<?php _e( 'Search Pages' ); ?>" class="button" />
							</p>
							*/
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
						</div>
						<?php
							wpsc_admin_products_list($category_id);
						?>
					</form>

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


	$this_page_url = stripslashes($_SERVER['REQUEST_URI']);
  
  
	?>
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
				
				
				$category_html .= "<a class='category_link' href='".add_query_arg('category_id', $category_row['id'])."'>".stripslashes($category_row['name'])."</a>";
				$i++;
			}        
							
				
				?>
					<tr class="product-edit" id="product-<?php echo $product['id']?>">
							<th class="check-column" scope="row"><input type='checkbox' name='product[]' class='deletecheckbox' value='<?php echo $product['id'];?>' /></th>
							
							
							<td class="product-image ">
								<img title='Drag to a new position' src='<?php echo $image_path; ?>' title='<?php echo $product['name']; ?>' alt='<?php echo $product['name']; ?>' width='35' height='35' />
							</td>
							<td class="product-title column-title">
								<a href='<?php echo add_query_arg('product_id', $product['id']); ?>'><?php echo $product_name; ?></a>				
							
								<div class="wpsc-row-actions">
									<span class="edit">
										<a title="Edit this post" href='<?php echo add_query_arg('product_id', $product['id']); ?>' style="cursor:pointer;">Edit</a>
									</span> |
									<span class="delete">
										<a class='submitdelete' title='<?php echo attribute_escape(__('Delete this product')); ?>' href='<?php echo wp_nonce_url("page.php?wpsc_admin_action=delete_product&amp;product={$product['id']}", 'delete_product_' . $product['id']); ?>' onclick="if ( confirm(' <?php echo js_escape(sprintf( __("You are about to delete this product '%s'\n 'Cancel' to stop, 'OK' to delete."), $product['name'] )) ?>') ) { return true;}return false;"><?php _e('Delete') ?></a>
									</span> |
								<span class="view"><a target="_blank" rel="permalink" title='View <?php echo $product_name; ?>' href="<?php wpsc_product_url($product['id']); ?>">View</a></span> |
								<span class="view"><a rel="permalink" title='Duplicate <?php echo $product_name; ?>' href="<?php echo add_query_arg('product_id', $product['id']); ?>">Duplicate</a></span>
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
	<?php
}

?>