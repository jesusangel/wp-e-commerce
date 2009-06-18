<?php
/**
 * WP eCommerce Debug page and functions
 *
 * This is debugging and unsafe updating code to debug or fix specific problems on some sites that is either not safe to run automatically or not usually needed
 * It is unwise to use anything on this page unless you know exactly what it will do and why you need to run it.
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_debug_page() {
	global $wpdb;
	?>
	<div class="wrap">
	  <a href='?page=wpsc-debug&amp;wpsc_debug_action=download_links'>Update Download Links</a>
		<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif; font-size:8px;'><?php
		 switch($_GET['wpsc_debug_action']) {
		   case 'download_links': 
		   wpsc_group_and_update_download_links();
		   break;
		 }
		?></pre>
	</div>
	<?
}

function wpsc_group_and_update_download_links() {
	global $wpdb;
	$unique_file_names = $wpdb->get_col("SELECT DISTINCT `filename` FROM  `".WPSC_TABLE_PRODUCT_FILES."`");
	foreach($unique_file_names as $filename) {
		echo "$filename \n";
		
		$file_id_list = array();
		$file_data = $wpdb->get_results("SELECT * FROM  `".WPSC_TABLE_PRODUCT_FILES."` WHERE `filename` IN ('$filename')", ARRAY_A);
		foreach($file_data as $file_row) {
			$file_id_list[] = $file_row['id'];
		}
		$product_data = $wpdb->get_row("SELECT * FROM  `".WPSC_TABLE_PRODUCT_LIST."` WHERE `file` IN ('".implode("', '", $file_id_list)."') AND `active` IN('1') ORDER BY `id` DESC LIMIT 1 ",ARRAY_A);
		$product_id = $product_data['id'];
		if($product_id > 0) {
			if($wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_FILES."` SET `product_id` = '{$product_id}' WHERE `id` IN ('".implode("', '", $file_id_list)."')")) {	
				if($wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `product_id` = '{$product_id}' WHERE `fileid` IN ('".implode("', '", $file_id_list)."')")) {
					echo "$filename done \n";
				}
			}
		}
	}
}
?>