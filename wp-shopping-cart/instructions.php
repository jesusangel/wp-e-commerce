<?php

require_once (ABSPATH . WPINC . '/rss.php');
global $wpdb;
?>
<div class="wrap">
  <?php
if($_GET['debug'] == 'true') {  
    //echo "<pre>".print_r($_SESSION['nzshpcrt_cart'],true)."</pre>";
		phpinfo();
} else if($_GET['zipup'] == 'true') {  
	  // Code to zip the plugin up for ease of downloading from slow or otherwise cruddy FTP servers, we sometimes develop on servers like that
		$ecommerce_path = escapeshellarg(ABSPATH."wp-content/plugins/wp-shopping-cart");
		$destination_path = escapeshellarg(ABSPATH."wp-content/plugins/wp-shopping-cart.tar.gz");
		/// disabled for excess paranoia
		//echo `tar -czf $destination_path $ecommerce_path`;
		//echo "<a href='".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart.tar.gz' />Downloaded the zipped up plugin here</a>";
		exit();
} else {
	//phpinfo();
	$file = WPSC_PREVIEW_DIR."testfile.zip";
	$target_dir = WP_CONTENT_DIR."/uploads/wpsc/test/";
	
	$success = false;
	// first try ZipArchive, it uses less memory
	if(! class_exists('ZipArchive')) {
		$zip = new ZipArchive;
		if($zip->open($file) === true) {
			if($zip->extractTo($target_dir) === true) {
				$success = true;
			}
// 			$zip->close();
// 			unset($zip);
		}
	}
	/*// otherwise fall back to PclZip
	if($success === false) {
	  if(!class_exists('PclZip')) {
	    include_once(WPSC_PREVIEW_DIR."pclzip.lib.php");
	  }
	  $zip = new PclZip($file);
	}*/
	
	echo "<pre>";
	print_r($zip);
	//print_r(stream_get_wrappers());
	echo "</pre>";
}
?>
</div>