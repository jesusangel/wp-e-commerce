<?php

require_once (ABSPATH . WPINC . '/rss.php');
global $wpdb;
?>
<div class="wrap">
  <?php
  
  if($wpdb->get_var("SELECT COUNT( * ) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `all_variation_ids` IN ( '' )") > 0 ) {
    
    $variation_priceandstock_ids = $wpdb->get_col("SELECT DISTINCT `priceandstock_id` FROM `{$wpdb->prefix}wpsc_variation_combinations`");
    foreach($variation_priceandstock_ids as $variation_priceandstock_id) {
      $variation_priceandstock_rows = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `priceandstock_id` IN ('$variation_priceandstock_id')", ARRAY_A);
      $all_value_array = array();
      foreach($variation_priceandstock_rows as $variation_priceandstock_row) {
        $all_value_array[] = $variation_priceandstock_row['variation_id'];
      }
      asort($all_value_array);    
      
      $all_value_ids = implode(",", $all_value_array);
      $update_sql = "UPDATE `{$wpdb->prefix}wpsc_variation_combinations` SET `all_variation_ids` = '".$all_value_ids."' WHERE `priceandstock_id` IN( '$variation_priceandstock_id' );";
      
      //echo "<pre>".print_r($update_sql,true)."</pre>";
      $wpdb->query($update_sql);
    }
  }
/*
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


*/

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