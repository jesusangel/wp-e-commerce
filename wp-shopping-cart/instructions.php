<?php

require_once (ABSPATH . WPINC . '/rss.php');
global $wpdb;
?>
<div class="wrap">
  <?php
    $variations = array(5,6,7);
 		$variation_processor = new nzshpcrt_variations();
 		echo $variation_processor->variations_add_grid_view((array)$variations);
		
		
		foreach((array)$variations as $variation) {		
      $variation = (int)$variation;
      // generate all the various bits of SQL to bind the tables together
      $join_selected_cols[] = "`a{$variation}`.`id` AS `id_{$variation}`, `a{$variation}`.`name` AS `name_{$variation}`";
      $join_tables[] = "`{$wpdb->prefix}variation_values` AS `a{$variation}`";
      $join_conditions[] = "`a{$variation}`.`variation_id` = '{$variation}'";
    }
    
    // implode the SQL statment segments into bigger segments
    $join_selected_cols = implode(", ", $join_selected_cols);
    $join_tables = implode(" JOIN ", $join_tables);
    $join_conditions = implode(" AND ", $join_conditions);
    
    // Assemble and execute the SQL query
    $associated_variation_values = $wpdb->get_results("SELECT {$join_selected_cols} FROM {$join_tables} WHERE {$join_conditions}", ARRAY_A);
		
		$variation_sets = array();
		$i = 0;
		foreach($associated_variation_values as $associated_variation_value_set) {
		  foreach($variations as $variation) {
		    $value_id = $associated_variation_value_set["id_$variation"];
		    $name_id = $associated_variation_value_set["name_$variation"];
		    $variation_sets[$i][$value_id] = $name_id;
		  }
      $i++;
		}
		
		
		
		$sql_query ="SELECT `a1`.`id` AS `id_1` , `a1`.`name` AS `name_1` , `a2`.`id` , `a2`.`name` , `a3`.`id` , `a3`.`name`
    FROM `wp_variation_values` AS `a1`
    JOIN `wp_variation_values` AS `a2`
    JOIN `wp_variation_values` AS `a3`
    WHERE `a1`.`variation_id` = '2'
    AND `a2`.`variation_id` = '3'
    AND `a3`.`variation_id` = '4';
    ";
		// */
  
   //$variation_priceandstock = $wpdb->get_results("$associated_variation_values",ARRAY_A);
   echo "<pre>".print_r($variation_sets,true)."</pre>";
// 
//     foreach($variation_priceandstock_items as $variation_priceandstock_item) {
//       $keys = array();
//       $keys[] = $variation_priceandstock_item['variation_id_1'];
//       $keys[] = $variation_priceandstock_item['variation_id_2'];
//       $variation_priceandstock_id = $variation_priceandstock_item['id'];
//       $product_id = $variation_priceandstock_item['product_id'];
//       foreach($keys as $key) {
//         if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `priceandstock_id` = '{$variation_priceandstock_id}' AND `value_id` = '$key'") < 1) {
//           $variation_id = $wpdb->get_var("SELECT `variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` = '{$key}'");
//           if($variation_id > 0) {
//             $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_variation_combinations` ( `product_id` , `priceandstock_id` , `value_id`, `variation_id` ) VALUES ( '$product_id', '{$variation_priceandstock_id}', '$key', '$variation_id' )");
//           }
//         }
//       }
//     }
//   

   // echo "<pre>".print_r($_SERVER,true)."</pre>";
		//phpinfo();
if($_GET['zipup'] == 'true') {  
	  // Code to zip the plugin up for ease of downloading from slow or otherwise cruddy FTP servers, we sometimes develop on servers like that
		$ecommerce_path = escapeshellarg(ABSPATH."wp-content/plugins/wp-shopping-cart");
		$destination_path = escapeshellarg(ABSPATH."wp-content/plugins/wp-shopping-cart.tar.gz");
		/// disabled for excess paranoia
		//echo `tar -czf $destination_path $ecommerce_path`;
		//echo "<a href='".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart.tar.gz' />Downloaded the zipped up plugin here</a>";
		exit();
} else {
// 	//phpinfo();
// 	$file = WPSC_PREVIEW_DIR."testfile.zip";
// 	$target_dir = WP_CONTENT_DIR."/uploads/wpsc/test/";
// 	
// 	$success = false;
// 	// first try ZipArchive, it uses less memory
// 	if(! class_exists('ZipArchive')) {
// 		$zip = new ZipArchive;
// 		if($zip->open($file) === true) {
// 			if($zip->extractTo($target_dir) === true) {
// 				$success = true;
// 			}
// // 			$zip->close();
// // 			unset($zip);
// 		}
// 	}
// 	/*// otherwise fall back to PclZip
// 	if($success === false) {
// 	  if(!class_exists('PclZip')) {
// 	    include_once(WPSC_PREVIEW_DIR."pclzip.lib.php");
// 	  }
// 	  $zip = new PclZip($file);
// 	}*/
// 	
// 	echo "<pre>";
// 	print_r($zip);
// 	//print_r(stream_get_wrappers());
// 	echo "</pre>";
}
?>
</div>