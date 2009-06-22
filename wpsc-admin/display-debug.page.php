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
	  <h2>Debugging Page</h2>
	  <p>
	  This is debugging and unsafe updating code to debug or fix specific problems on some sites that is either not safe to run automatically or not usually needed<br />
    It is unwise to use anything on this page unless you know exactly what it will do and why you need to run it.
    </p>
	  <h4>Action List</h4>
		<ul>
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=download_links'>Update Download Links</a>
			</li>
			
			<li>
				<a href='?page=wpsc-debug&amp;wpsc_debug_action=product_url_names'>Clean Duplicate Product URL names</a>
			</li>
		</ul>
	  
		<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif; font-size:8px;'><?php
		 switch($_GET['wpsc_debug_action']) {
		   case 'download_links':
		   wpsc_group_and_update_download_links();
		   break;
		   
		   
		   case 'product_url_names':
		   wpsc_clean_product_url_names();
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


function wpsc_clean_product_url_names() {
	global $wpdb;
	
	 $check_product_names = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_LIST."`.`id`, `".WPSC_TABLE_PRODUCT_LIST."`.`name`, `".WPSC_TABLE_PRODUCTMETA."`.`meta_key` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_PRODUCTMETA."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_PRODUCTMETA."`.`product_id` WHERE (`".WPSC_TABLE_PRODUCTMETA."`.`meta_key` IN ('url_name') AND  `".WPSC_TABLE_PRODUCTMETA."`.`meta_value` IN (''))  OR ISNULL(`".WPSC_TABLE_PRODUCTMETA."`.`meta_key`)");  

	
	$duplicated_meta_data = $wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('url_name') GROUP BY `meta_value` HAVING COUNT(`meta_value`) > 1 ");
	
	$product_data = $wpdb->get_results("SELECT `products`.* FROM `".WPSC_TABLE_PRODUCTMETA."` AS `meta` LEFT JOIN `".WPSC_TABLE_PRODUCT_LIST."` AS `products` ON `meta`.`product_id` =  `products`.`id` WHERE `meta`.`meta_key` IN('url_name') AND `meta`.`meta_value` IN('".implode("', '", $duplicated_meta_data)."')", ARRAY_A);
	
	foreach($product_data as $product_row) {
		if($product_row['name'] != '') {
			$tidied_name = strtolower(trim($product_row['name']));
			$url_name = preg_replace(array("/(\s-\s)+/","/(\s)+/","/[^\w-]+/i"), array("-","-", ''), $tidied_name);
			$similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ('url_name') AND `meta_value` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
			$extension_number = '';
			if($similar_names['count'] > 0) {
				$extension_number = (int)$similar_names['max_number']+1;
			}
			$url_name .= $extension_number;
			update_product_meta($product_row['id'], 'url_name', $url_name);
		}
	}	
	print_r($product_data);
}
?>