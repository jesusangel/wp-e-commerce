<?php
$wpsc_currency_data = array();
$wpsc_title_data = array();


if(isset($_SESSION['nzshpcrt_cart'])) {
  foreach((array)$_SESSION['nzshpcrt_cart'] as $key => $item) {
      if(get_class($item) == "__PHP_Incomplete_Class") {
          $_SESSION['nzshpcrt_cart'] = unserialize($_SESSION['nzshpcrt_serialized_cart']);
    }
  }
} else {
  if(isset($_SESSION['nzshpcrt_cart'])) {
    $_SESSION['nzshpcrt_cart'] = unserialize($_SESSION['nzshpcrt_serialized_cart']);
  }
}


if(is_numeric($_GET['sessionid'])) {
  $sessionid = $_GET['sessionid'];
  $cart_log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1");
  if(is_numeric($cart_log_id)) {
    $_SESSION['nzshpcrt_cart'] = null;
    $_SESSION['nzshpcrt_serialized_cart'] = null;
    }
  }



$GLOBALS['nzshpcrt_imagesize_info'] = TXT_WPSC_IMAGESIZEINFO;
$nzshpcrt_log_states[0]['name'] = TXT_WPSC_RECEIVED;
$nzshpcrt_log_states[1]['name'] = TXT_WPSC_PROCESSING;
$nzshpcrt_log_states[2]['name'] = TXT_WPSC_PROCESSED;




  
    
    
  if(($_GET['rss'] == "true") && ($_GET['action'] == "product_list")) {
    $siteurl = get_option('siteurl');    
    if(is_numeric($_GET['limit'])) {
      $limit = "LIMIT ".$_GET['limit']."";
		} else {
      $limit = '';
		}
    
    // LIMIT $startnum
    if(is_numeric($_GET['product_id'])) {
      $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN('1') AND `id` IN('".$_GET['product_id']."') LIMIT 1";
      } else if($_GET['random'] == 'true') {
      $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN('1') ORDER BY RAND() $limit";
      } else if(is_numeric($_GET['category_id'])) {
      /* man, this is a hard to read SQL statement */
      $sql = "SELECT DISTINCT `".WPSC_TABLE_PRODUCT_LIST."`.*, `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id`,`".WPSC_TABLE_PRODUCT_ORDER."`.`order`, IF(ISNULL(`".WPSC_TABLE_PRODUCT_ORDER."`.`order`), 0, 1) AS `order_state` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`product_id` LEFT JOIN `".WPSC_TABLE_PRODUCT_ORDER."` ON ( ( `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_PRODUCT_ORDER."`.`product_id` ) AND ( `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` = `".WPSC_TABLE_PRODUCT_ORDER."`.`category_id` ) ) WHERE `".WPSC_TABLE_PRODUCT_LIST."`.`active` = '1' AND `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`.`category_id` IN ('".$_GET['category_id']."') ORDER BY `order_state` DESC,`".WPSC_TABLE_PRODUCT_ORDER."`.`order` ASC $limit";      
    } else {
      $sql = "SELECT DISTINCT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN('1') ORDER BY `id` DESC $limit";
    }
    
//     include_once(WPSC_FILE_PATH."/product_display_functions.php");
    
    
		if(isset($_GET['category_id']) and is_numeric($_GET['category_id'])){
			$selected_category = "&amp;category_id=".$_GET['category']."";
		}
		$self = get_option('siteurl')."/index.php?rss=true&amp;action=product_list$selected_category";
    
    $product_list = $wpdb->get_results($sql,ARRAY_A);
    header("Content-Type: application/xml; charset=UTF-8"); 
    header('Content-Disposition: inline; filename="E-Commerce_Product_List.rss"');
    $output = "<?xml version='1.0'?>\n\r";
    $output .= "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom' xmlns:product='http://www.buy.com/rss/module/productV2/'>\n\r";    
    $output .= "  <channel>\n\r";
    $output .= "    <title>".get_option('blogname')." Products</title>\n\r";
    $output .= "    <link>".get_option('siteurl')."/wp-admin/admin.php?page=".WPSC_DIR_NAME."/display-log.php</link>\n\r";
    $output .= "    <description>This is the WP E-Commerce Product List RSS feed</description>\n\r";
    $output .= "    <generator>WP E-Commerce Plugin</generator>\n\r";
    $output .= "    <atom:link href='$self' rel='self' type='application/rss+xml' />";
    foreach($product_list as $product) {
      $purchase_link = wpsc_product_url($product['id']);
      $output .= "    <item>\n\r";
      $output .= "      <title>".htmlentities(stripslashes($product['name']), ENT_NOQUOTES, 'UTF-8')."</title>\n\r";
      $output .= "      <link>$purchase_link</link>\n\r";
      $output .= "      <description>".htmlentities(stripslashes($product['description']), ENT_NOQUOTES, 'UTF-8')."</description>\n\r";
      $output .= "      <pubDate>".date("r")."</pubDate>\n\r";
      $output .= "      <guid>$purchase_link</guid>\n\r"; 
      if($product['thumbnail_image'] != null) {
        $image_file_name = $product['thumbnail_image'];
        } else {
        $image_file_name = $product['image'];
        }      
      $image_path = WPSC_THUMBNAIL_DIR.$image_file_name;
      if(is_file($image_path) && (filesize($image_path) > 0)) {
        $image_data = @getimagesize($image_path); 
        $image_link = WPSC_THUMBNAIL_URL.$product['image'];
        $output .= "      <enclosure url='$image_link' length='".filesize($image_path)."' type='".$image_data['mime']."' width='".$image_data[0]."' height='".$image_data[1]."' />\n\r"; 
        }
      $output .= "      <product:price>".$product['price']."</product:price>\n\r";
      $output .= "    </item>\n\r";
      }
    $output .= "  </channel>\n\r";
    $output .= "</rss>";
    echo $output;
    exit();
    }
    
  
if($_GET['termsandconds'] === 'true'){
	echo stripslashes(get_option('terms_and_conditions'));
	exit();
}

      


function nzshpcrt_download_file() {
  global $wpdb,$user_level,$wp_rewrite; 
  get_currentuserinfo();  
  function readfile_chunked($filename, $retbytes = true) {
    $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
    $buffer = '';
    $cnt = 0;
    $handle = fopen($filename, 'rb');
    if($handle === false) {
      return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			ob_flush();
			flush();
			if($retbytes)	{
				$cnt += strlen($buffer);
			}
		}
    $status = fclose($handle);
    if($retbytes && $status) {
      return $cnt; // return num. bytes delivered like readfile() does.
		}
    return $status;
	}  
  
  if(isset($_GET['downloadid'])) {
    // strip out anything that isnt 'a' to 'z' or '0' to '9'
    //ini_set('max_execution_time',10800);
    $downloadid = preg_replace("/[^a-z0-9]+/i",'',strtolower($_GET['downloadid']));
    
		$download_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `uniqueid` = '".$downloadid."' AND `downloads` > '0' AND `active`='1' LIMIT 1",ARRAY_A);
		
		if(($download_data == null) && is_numeric($downloadid)) {
		  $download_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_DOWNLOAD_STATUS."` WHERE `id` = '".$downloadid."' AND `downloads` > '0' AND `active`='1' AND `uniqueid` IS NULL LIMIT 1",ARRAY_A);
		}
		
		if((get_option('wpsc_ip_lock_downloads') == 1) && ($_SERVER['REMOTE_ADDR'] != null)) {
		  $ip_number = $_SERVER['REMOTE_ADDR'];
		  if($download_data['ip_number'] == '') {
		    // if the IP number is not set, set it
		    $wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `ip_number` = '{$ip_number}' WHERE `id` = '{$download_data['id']}' LIMIT 1");
		  } else if($ip_number != $download_data['ip_number']) {
		    // if the IP number is set but does not match, fail here.
				exit(WPSC_DOWNLOAD_INVALID);
		  }
		}
   
    if($download_data != null) {
			if($download_data['fileid'] > 0) {
				$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1", ARRAY_A);
      } else {
				$old_file_data = $wpdb->get_row("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1", ARRAY_A);
				$file_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1", ARRAY_A);
			}
      
      if((int)$download_data['downloads'] >= 1) {
        $download_count = (int)$download_data['downloads'] - 1;
      } else {
        $download_count = 0;
      }
          $wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `downloads` = '{$download_count}' WHERE `id` = '{$download_data['id']}' LIMIT 1");
	  $cart_contents = $wpdb->get_results('SELECT `'.WPSC_TABLE_CART_CONTENTS.'`.*,`'.WPSC_TABLE_PRODUCT_LIST.'`.`file` FROM `'.WPSC_TABLE_CART_CONTENTS.'` LEFT JOIN `'.WPSC_TABLE_PRODUCT_LIST.'` ON `'.WPSC_TABLE_CART_CONTENTS.'`.`prodid`= `'.WPSC_TABLE_PRODUCT_LIST.'`.`id` WHERE `purchaseid` ='.$download_data['purchid'], ARRAY_A);
	    $dl = 0;

      foreach($cart_contents as $cart_content) {
      	if($cart_content['file'] == 1) {
      		$dl++;
      	}
      }
      if(count($cart_contents) == $dl) {
    //  	exit('called');
         $wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '4' WHERE `id` = '".$download_data['purchid']."' LIMIT 1");
      }

	  //exit('<pre>'.print_r($cart_contents,true).'</pre>');
   
      if(is_file(WPSC_FILE_DIR.$file_data['idhash'])) {
        header('Content-Type: '.$file_data['mimetype']);      
        header('Content-Length: '.filesize(WPSC_FILE_DIR.$file_data['idhash']));
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.stripslashes($file_data['filename']).'"');
        if(isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != '')) {
          /*
          There is a bug in how IE handles downloads from servers using HTTPS, this is part of the fix, you may also need:
            session_cache_limiter('public');
            session_cache_expire(30);
          At the start of your index.php file or before the session is started
          */
          header("Pragma: public");
          header("Expires: 0");      
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: public"); 
				} else {
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');       
				}        
        $filename = WPSC_FILE_DIR.$file_data['idhash'];
        // destroy the session to allow the file to be downloaded on some buggy browsers and webservers
        session_destroy();
        readfile_chunked($filename);   
        exit();
			}
		} else {
			exit(WPSC_DOWNLOAD_INVALID);
		}
	} else {
		if(($_GET['admin_preview'] == "true") && is_numeric($_GET['product_id']) && current_user_can('edit_plugins')) {
			$product_id = $_GET['product_id'];
			$product_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
			if(is_numeric($product_data[0]['file']) && ($product_data[0]['file'] > 0)) {
				$file_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$product_data[0]['file']."' LIMIT 1",ARRAY_A) ;
				$file_data = $file_data[0];
				if(is_file(WPSC_FILE_DIR.$file_data['idhash'])) {
					header('Content-Type: '.$file_data['mimetype']);
					header('Content-Length: '.filesize(WPSC_FILE_DIR.$file_data['idhash']));
					header('Content-Transfer-Encoding: binary');
					if($_GET['preview_track'] != 'true') {
						header('Content-Disposition: attachment; filename="'.$file_data['filename'].'"');
					} else {
						header('Content-Disposition: inline; filename="'.$file_data['filename'].'"');
					}
					if(isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] != '')) {
						header("Pragma: public");
						header("Expires: 0");      
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Cache-Control: public"); 
					} else {
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');       
					}             
					$filename = WPSC_FILE_DIR.$file_data['idhash'];  
					session_destroy();
					readfile_chunked($filename);   
					exit();
				}            
			}
    }
  }
}

function nzshpcrt_product_log_rss_feed() {
  echo "<link type='application/rss+xml' href='".get_option('siteurl')."/wp-admin/index.php?rss=true&amp;rss_key=key&amp;action=purchase_log&amp;type=rss' title='WP E-Commerce Purchase Log RSS' rel='alternate'/>";
}
  
function nzshpcrt_product_list_rss_feed() {
  if(isset($_GET['category']) and is_numeric($_GET['category'])){
    $selected_category = "&amp;category_id=".$_GET['category']."";
	}
  echo "<link rel='alternate' type='application/rss+xml' title='".get_option('blogname')." Product List RSS' href='".get_option('siteurl')."/index.php?rss=true&amp;action=product_list$selected_category'/>";
}

  
 
 
function nzshpcrt_listdir($dirname) {
  /*
  lists the merchant directory
  */
  $dir = @opendir($dirname);
  $num = 0;
  while(($file = @readdir($dir)) !== false) {
    //filter out the dots and any backup files, dont be tempted to correct the "spelling mistake", its to filter out a previous spelling mistake.
    if(($file != "..") && ($file != ".") && !stristr($file, "~") && !stristr($file, "Chekcout") && !( strpos($file, ".") === 0 )) {
      $dirlist[$num] = $file;
      $num++;
    }
  }
  if($dirlist == null) {
    $dirlist[0] = "paypal.php";
    $dirlist[1] = "testmode.php";
  }
  return $dirlist; 
}

add_action('wp_head', 'nzshpcrt_product_list_rss_feed');

if($_GET['page'] == WPSC_DIR_NAME."/display-log.php") {
  add_action('admin_head', 'nzshpcrt_product_log_rss_feed');
}

//add_action('init', 'nzshpcrt_submit_ajax');
add_action('init', 'nzshpcrt_download_file');

if(stristr($_GET['page'], WPSC_DIR_NAME)) {
  add_action('admin_notices', 'wpsc_admin_notices');
}

function wpsc_admin_notices() {
  global $wpdb;
//  exit(get_option('wpsc_default_category'));
  if(get_option('wpsc_default_category') != 'all+list' && get_option('wpsc_default_category') != 'all' && get_option('wpsc_default_category') != 'list') {
		if((get_option('wpsc_default_category') < 1) || $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('".get_option('wpsc_default_category')."') AND `active` NOT IN ('1');")) {  // if there is no default category or it is deleted
			if(!$_POST['wpsc_default_category']) { // if we are not changing the default category
				echo "<div id='message' class='updated fade' style='background-color: rgb(255, 251, 204);'>";
				echo "<p>".TXT_WPSC_NO_DEFAULT_PRODUCTS."</p>";
				echo "</div>\n\r";
			}
		}
  }
}


//this adds all the admin pages, before the code was a mess, now it is slightly less so.

// pe.{
if((get_option('wpsc_share_this') == 1) && (get_option('product_list_url') != '')) {
  if(stristr(("http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), get_option('product_list_url'))){
    include_once(WPSC_FILE_PATH."/share-this.php");
  }
}
 

add_action('plugins_loaded', 'widget_wp_shopping_cart_init', 10);


// refresh page urls when permalinks are turned on or altered
add_filter('mod_rewrite_rules', 'wpsc_refresh_page_urls');

if(strpos($_SERVER['REQUEST_URI'], WPSC_DIR_NAME.'') !== false) {
	if($_GET['page'] == 'wpsc-edit-products') {
	}
}




switch(get_option('cart_location')) {
  case 1:
  add_action('wp_list_pages','nzshpcrt_shopping_basket');
  break;
  
  case 2:
  add_action('the_content', 'nzshpcrt_shopping_basket' , 14);
  break;
  
  case 3:
  case 4:
  case 5:
  break;
  
  default:
  add_action('the_content', 'nzshpcrt_shopping_basket', 14);
  break;
}





add_filter('favorite_actions', 'wpsc_fav_action');
function wpsc_fav_action($actions) {
    $actions['admin.php?page=wpsc-edit-products'] = array('New Product', 'manage_options');
    return $actions;
}
?>