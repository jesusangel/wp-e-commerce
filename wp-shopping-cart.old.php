<?php
$wpsc_currency_data = array();
$wpsc_title_data = array();
if(WPSC_DEBUG === true) {
	function microtime_float() {
		list($usec, $sec) = explode(" ", microtime()); 
		return ((float)$usec + (float)$sec);
	}
	
	function wpsc_debug_start_subtimer($name, $action, $loop = false) {	
		global $wpsc_debug_sections,$loop_debug_increment;
		
		if($loop === true) {
			if ($action == 'start') {
				$loop_debug_increment[$name]++;
				$wpsc_debug_sections[$name.$loop_debug_increment[$name]][$action] = microtime_float();
			} else if($action == 'stop') {
				$wpsc_debug_sections[$name.$loop_debug_increment[$name]][$action] = microtime_float();
			}
		} else {
			$wpsc_debug_sections[$name][$action] = microtime_float();		
		}
	}
	
  $wpsc_start_time = microtime_float();
} else {
	function wpsc_debug_start_subtimer($name) {
		return null;
	}
}


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





function nzshpcrt_style() {
  global $wpdb,$wp_query;
  if(function_exists('xili_display4mobile')) {  //check for the function before using it
    if (xili_display4mobile() === true) {
      // instead of wrapping the whole block of code in brackets, resulting in mysterious brackets, simply break out of the function here.
      return null;
    }
  }
  
  
	if(is_numeric($_GET['category']) || is_numeric($wp_query->query_vars['product_category']) || is_numeric(get_option('wpsc_default_category'))) {
		if(is_numeric($wp_query->query_vars['product_category'])) {
			$category_id = $wp_query->query_vars['product_category'];
		} else if(is_numeric($_GET['category'])) {
			$category_id = $_GET['category'];
		} else { 
			$category_id = get_option('wpsc_default_category');
		}
	}

	$category_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1",ARRAY_A);
	
	
	if($category_data['display_type'] != '') {
		$display_type = $category_data['display_type'];
	} else {
		$display_type = get_option('product_view');
	}
  ?>
  <style type="text/css" media="screen">
  
	<?php
	if(!defined('WPSC_DISABLE_IMAGE_SIZE_FIXES') || (constant('WPSC_DISABLE_IMAGE_SIZE_FIXES') != true)) {
    if(($display_type == 'default') ||  ($display_type == '')) {
      $thumbnail_width = get_option('product_image_width');
      if($thumbnail_width <= 0) {
        $thumbnail_width = 96;
      }
      $thumbnail_height = get_option('product_image_height'); 
      if($thumbnail_height <= 0) { 
        $thumbnail_height = 96; 
      }
      
      
    ?>
      div.default_product_display div.textcol{
        margin-left: <?php echo $thumbnail_width + 10; ?>px !important;
        _margin-left: <?php echo ($thumbnail_width/2) + 5; ?>px !important;
        min-height: <?php echo $thumbnail_height;?>px;
        _height: <?php echo $thumbnail_height;?>px;
      }
        
        
      div.default_product_display  div.textcol div.imagecol{
        position:absolute;
        top:0px;
        left: 0px;
        margin-left: -<?php echo $thumbnail_width + 10; ?>px !important;
      }
      
      div.default_product_display  div.textcol div.imagecol a img {
        width: <?php echo $thumbnail_width; ?>px;
        height: <?php echo $thumbnail_height; ?>px;
      }
      
    <?php
    }
        
      
    $single_thumbnail_width = get_option('single_view_image_width');
    $single_thumbnail_height = get_option('single_view_image_height');
    if($single_thumbnail_width <= 0) {
      $single_thumbnail_width = 128;
    }
    ?>
      div.single_product_display div.textcol{
        margin-left: <?php echo $single_thumbnail_width + 10; ?>px !important;
        _margin-left: <?php echo ($single_thumbnail_width/2) + 5; ?>px !important;
        min-height: <?php echo $single_thumbnail_height;?>px;
        _height: <?php echo $single_thumbnail_height;?>px;
      }
        
        
      div.single_product_display  div.textcol div.imagecol{
        position:absolute;
        top:0px;
        left: 0px;
        margin-left: -<?php echo $single_thumbnail_width + 10; ?>px !important;
      }
      
      div.single_product_display  div.textcol div.imagecol a img {
        width: <?php echo $single_thumbnail_width; ?>px;
        height: <?php echo $single_thumbnail_height; ?>px;
      }
      
    <?php
    $product_ids = $wpdb->get_col("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `thumbnail_state` IN(0,2,3)"); 
    foreach($product_ids as $product_id) {
      $individual_thumbnail_height = get_product_meta($product_id, 'thumbnail_height'); 
      $individual_thumbnail_width = get_product_meta($product_id, 'thumbnail_width');     
      if($individual_thumbnail_height> $thumbnail_height) { 
        echo "    div.default_product_display.product_view_$product_id div.textcol{\n\r"; 
        echo "            min-height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
        echo "            _height: ".($individual_thumbnail_height + 10)."px !important;\n\r"; 
        echo "      }\n\r";
      } 
      if($individual_thumbnail_width> $thumbnail_width) {
          echo "      div.default_product_display.product_view_$product_id div.textcol{\n\r";
          echo "            margin-left: ".($individual_thumbnail_width + 10)."px !important;\n\r";
          echo "            _margin-left: ".(($individual_thumbnail_width/2) + 5)."px !important;\n\r";
          echo "      }\n\r";
  
          echo "      div.default_product_display.product_view_$product_id  div.textcol div.imagecol{\n\r";
          echo "            position:absolute;\n\r";
          echo "            top:0px;\n\r";
          echo "            left: 0px;\n\r";
          echo "            margin-left: -".($individual_thumbnail_width + 10)."px !important;\n\r";
          echo "      }\n\r";
  
          echo "      div.default_product_display.product_view_$product_id  div.textcol div.imagecol a img{\n\r";
          echo "            width: ".$individual_thumbnail_width."px;\n\r";
          echo "            height: ".$individual_thumbnail_height."px;\n\r";
          echo "      }\n\r";
        }
      }	
    }
    
  if(is_numeric($_GET['brand']) || (get_option('show_categorybrands') == 3)) {
    $brandstate = 'block';
    $categorystate = 'none';
  } else {
    $brandstate = 'none';
    $categorystate = 'block';
  }
      
    ?>
    div#categorydisplay{
    display: <?php echo $categorystate; ?>;
    }
    
    div#branddisplay{
    display: <?php echo $brandstate; ?>;
    }
  </style>
  <?php
  }
  
function nzshpcrt_javascript()
  {
  $siteurl = get_option('siteurl'); 
  if(function_exists('xili_display4mobile')) {  //check for the function before using it
    if (xili_display4mobile() === true) {
      // instead of wrapping the whole block of code in brackets, resulting in mysterious brackets, simply break out of the function here.
      return null;
    }
  }
  if(($_SESSION['nzshpcrt_cart'] == null) && (get_option('show_sliding_cart') == 1)) {
		?>
			<style type="text/css" media="screen">
		div#sliding_cart{
			display: none;
			}
		</style>
		<?php
	} else {
		?>
			<style type="text/css" media="screen">
		div#sliding_cart{
			display: block;
			}
		</style>
	<?php
	}
  ?>
<?php if (get_option('product_ratings') == 1){ ?>
<link href='<?php echo WPSC_URL; ?>/product_rater.css' rel="stylesheet" type="text/css" />
<?php } ?>
<link href='<?php echo WPSC_URL; ?>/thickbox.css' rel="stylesheet" type="text/css" />
<?php if (get_option('catsprods_display_type') == 1){ ?>
  <script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/slideMenu.js"></script>
<?php } ?>
<script language='JavaScript' type='text/javascript'>
jQuery.noConflict();
/* base url */
var base_url = "<?php echo $siteurl; ?>";
var WPSC_URL = "<?php echo WPSC_URL; ?>";
var WPSC_IMAGE_URL = "<?php echo WPSC_IMAGE_URL; ?>";
var WPSC_DIR_NAME = "<?php echo WPSC_DIR_NAME; ?>";
/* LightBox Configuration start*/
var fileLoadingImage = "<?php echo WPSC_URL; ?>/images/loading.gif";
var fileBottomNavCloseImage = "<?php echo WPSC_URL; ?>/images/closelabel.gif";
var fileThickboxLoadingImage = "<?php echo WPSC_URL; ?>/images/loadingAnimation.gif";
var resizeSpeed = 9;  // controls the speed of the image resizing (1=slowest and 10=fastest)
var borderSize = 10;  //if you adjust the padding in the CSS, you will need to update this variable
jQuery(document).ready( function() {
  <?php
  if(get_option('show_sliding_cart') == 1) {
    if(is_numeric($_SESSION['slider_state'])) {
      if($_SESSION['slider_state'] == 0) {
        ?>
        jQuery("#sliding_cart").css({ display: "none"});
        <?php
			} else {
        ?>
        jQuery("#sliding_cart").css({ display: "block"});  
        <?php
			}
    } else {
			if($_SESSION['nzshpcrt_cart'] == null) {
				?>
				jQuery("#sliding_cart").css({ display: "none"});  
				<?php
			} else {
				?>
				jQuery("#sliding_cart").css({ display: "block"});  
				<?php
			}
		}
	}
  ?>
});
</script>

<script src="<?php echo WPSC_URL; ?>/ajax.js" language='JavaScript' type="text/javascript"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/jquery.jeditable.pack.js"></script>
<script src="<?php echo WPSC_URL; ?>/user.js" language='JavaScript' type="text/javascript"></script>

<?php
  $theme_path = WPSC_FILE_PATH. '/themes/';
  if((get_option('wpsc_selected_theme') != '') && (file_exists($theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css") )) {    
    ?>    
<link href='<?php echo WPSC_URL; ?>/themes/<?php echo get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".css"; ?>' rel="stylesheet" type="text/css" />
    <?php
    } else {
    ?>    
<link href='<?php echo WPSC_URL; ?>/themes/default/default.css' rel="stylesheet" type="text/css" />
    <?php
    }
    ?>    
<link href='<?php echo WPSC_URL; ?>/themes/compatibility.css' rel="stylesheet" type="text/css" />
    <?php
  }

function wpsc_admin_css() {
  $siteurl = get_option('siteurl'); 
  if((strpos($_SERVER['REQUEST_URI'], WPSC_DIR_NAME) !== false) || ($_GET['mass_upload'] == 'true') || ((strpos($_SERVER['REQUEST_URI'], 'wp-admin/index.php') !== false) && !isset($_GET['page']))) {
  	if(function_exists('add_object_page')) {
  		echo "<link href='".WPSC_URL."/admin_2.7.css' rel='stylesheet' type='text/css' />";
  	} else {
  		echo "<link href='".WPSC_URL."/admin.css' rel='stylesheet' type='text/css' />";
  	}
?>

<link href='<?php echo WPSC_URL; ?>/js/jquery.ui.tabs.css' rel="stylesheet" type="text/css" />
<?php
if (($_GET['page'] == WPSC_DIR_NAME.'/display-log.php') || ($_GET['page'] == WPSC_DIR_NAME.'/gold_cart_files/affiliates.php') || ($_GET['page'] == WPSC_DIR_NAME.'/wpsc-admin/display-sales-logs.php')) {
	?>
		<link href='<?php echo $siteurl; ?>/wp-admin/css/dashboard.css?ver=2.6' rel="stylesheet" type="text/css" />
	<?php
}
?>
<!-- <link href='<?php echo WPSC_URL; ?>/thickbox.css' rel="stylesheet" type="text/css" /> -->
<script src="<?php echo WPSC_URL; ?>/ajax.js" language='JavaScript' type="text/javascript"></script>

<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/jquery.tooltip.js"></script>
<!--		<script src="http://dev.jquery.com/view/tags/ui/latest/ui/ui.core.js"></script>
<script src="http://dev.jquery.com/view/tags/ui/latest/ui/ui.sortable.js"></script>-->
<script language='JavaScript' type='text/javascript'>
//<![CDATA[
/* base url */
var base_url = "<?php echo $siteurl; ?>";
var WPSC_URL = "<?php echo WPSC_URL; ?>";
var WPSC_IMAGE_URL = "<?php echo WPSC_IMAGE_URL; ?>";
var WPSC_DIR_NAME = "<?php echo WPSC_DIR_NAME; ?>";
/* LightBox Configuration start*/
var fileLoadingImage = "<?php echo WPSC_URL; ?>/images/loading.gif";
var fileBottomNavCloseImage = "<?php echo WPSC_URL; ?>/images/closelabel.gif";
var fileThickboxLoadingImage = "<?php echo WPSC_URL; ?>/images/loadingAnimation.gif";

var resizeSpeed = 9;  

var borderSize = 10;
/* LightBox Configuration end*/
/* custom admin functions start*/
<?php
	$hidden_boxes = get_option('wpsc_hidden_box');
	$hidden_boxes = implode(',', (array)$hidden_boxes);
	echo "var hidden_boxes = '".$hidden_boxes."';";
	echo "var IS_WP27 = '".IS_WP27."';";
    echo "var TXT_WPSC_DELETE = '".TXT_WPSC_DELETE."';\n\r";
    echo "var TXT_WPSC_TEXT = '".TXT_WPSC_TEXT."';\n\r";
    echo "var TXT_WPSC_EMAIL = '".TXT_WPSC_EMAIL."';\n\r";
    echo "var TXT_WPSC_COUNTRY = '".TXT_WPSC_COUNTRY."';\n\r";
    echo "var TXT_WPSC_TEXTAREA = '".TXT_WPSC_TEXTAREA."';\n\r";
    echo "var TXT_WPSC_HEADING = '".TXT_WPSC_HEADING."';\n\r";
    echo "var TXT_WPSC_COUPON = '".TXT_WPSC_COUPON."';\n\r";
    echo "var HTML_FORM_FIELD_TYPES =\"<option value='text' >".TXT_WPSC_TEXT."</option>";
    echo "<option value='email' >".TXT_WPSC_EMAIL."</option>";
    echo "<option value='address' >".TXT_WPSC_ADDRESS."</option>";
    echo "<option value='city' >".TXT_WPSC_CITY."</option>";
    echo "<option value='country'>".TXT_WPSC_COUNTRY."</option>";
    echo "<option value='delivery_address' >".TXT_WPSC_DELIVERY_ADDRESS."</option>";
    echo "<option value='delivery_city' >".TXT_WPSC_DELIVERY_CITY."</option>";
    echo "<option value='delivery_country'>".TXT_WPSC_DELIVERY_COUNTRY."</option>";
    echo "<option value='textarea' >".TXT_WPSC_TEXTAREA."</option>";    
    echo "<option value='heading' >".TXT_WPSC_HEADING."</option>";
    echo "<option value='coupon' >".TXT_WPSC_COUPON."</option>\";\n\r";
    
    echo "var TXT_WPSC_LABEL = '".TXT_WPSC_LABEL."';\n\r";
    echo "var TXT_WPSC_LABEL_DESC = '".TXT_WPSC_LABEL_DESC."';\n\r";
    echo "var TXT_WPSC_ITEM_NUMBER = '".TXT_WPSC_ITEM_NUMBER."';\n\r";
    echo "var TXT_WPSC_LIFE_NUMBER = '".TXT_WPSC_LIFE_NUMBER."';\n\r";
    echo "var TXT_WPSC_PRODUCT_CODE = '".TXT_WPSC_PRODUCT_CODE."';\n\r";
    echo "var TXT_WPSC_PDF = '".TXT_WPSC_PDF."';\n\r";
    
    echo "var TXT_WPSC_AND_ABOVE = '".TXT_WPSC_AND_ABOVE."';\n\r";
    echo "var TXT_WPSC_IF_PRICE_IS = '".TXT_WPSC_IF_PRICE_IS."';\n\r";
    echo "var TXT_WPSC_IF_WEIGHT_IS = '".TXT_WPSC_IF_WEIGHT_IS."';\n\r";
?>
//]]>
/* custom admin functions end*/
</script>
<!--<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/thickbox.js"></script>-->
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/jquery.tooltip.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/dimensions.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/admin.js"></script>
<?php if($_GET['page'] == 'trunk/display-coupons.php') { ?>
<script language="JavaScript" type="text/javascript" src="<?php echo WPSC_URL; ?>/js/ui.datepicker.js"></script>
<?php } ?>
  <style type="text/css" media="screen">
  <?php
  
    // $flash = true;
    // if ( false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mac') && apache_mod_loaded('mod_security') )
    // 	$flash = false;
    
    if(get_option('wpsc_use_flash_uploader') == 1) {
      ?>
      table.flash-image-uploader {
        display: block;
      }
      
      table.browser-image-uploader {
        display: none;
      }
      <?php
    } else {
      ?>
      table.flash-image-uploader {
        display: none;
      }
      
      table.browser-image-uploader {
        display: block;
      }
      <?php
    
    }
  ?>
  </style>
<?php
	}
}
function nzshpcrt_submit_ajax()
  {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();  
  if(get_option('permalink_structure') != '') {
    $seperator ="?";
	} else {
		$seperator ="&amp;";
	}
   
   $cartt = $_SESSION['nzshpcrt_cart'];
   $cartt1=$cartt[0]->product_id;
   
  // if is an AJAX request, cruddy code, could be done better but getting approval would be impossible
if(($_POST['ajax'] == "true") || ($_GET['ajax'] == "true")) {

	if ($_POST['metabox'] == 'true') {
		$output .= "<div class='meta_box'>";
		if (get_option('multi_add')=='1')
			$output .= TXT_WPSC_QUANTITY.": <input type='text' name='quantity[]' size='3'><br>";
		if (get_option('time_requested')=='1')
			$output .= TXT_WPSC_DATE_REQUESTED.": <input type='text' class='time_requested' name='time_requested[]' size='10'><br>";
		if (get_option('commenting')=='1')
			$output .= TXT_WPSC_COMMENT.":<br><textarea type='text' name='comment[]'></textarea><br>";
			
		$output .= TXT_WPSC_LABEL.":<br><textarea type='text' name='label[]'></textarea><br>";
		$output .= "</div>";
		exit($output);
	}
	

	
	
	if ($_POST['submittogoogle']) {
		$newvalue=$_POST['value'];
		$amount=$_POST['amount'];
		$reason=$_POST['reason'];
		$comment=$_POST['comment'];
		$message=$_POST['message'];
		$amount=number_format($amount, 2, '.', '');
		$log_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = '".$_POST['id']."' LIMIT 1",ARRAY_A);  
		if (($newvalue==2) && function_exists('wpsc_member_activate_subscriptions')){
			wpsc_member_activate_subscriptions($_POST['id']);
		}
		$google_status = unserialize($log_data['google_status']);
		
		switch($newvalue) {
			case "Charge":
				if ($google_status[0]!='CANCELLED_BY_GOOGLE') {
					if ($amount=='') {
						$google_status['0']='Partially Charged';
					} else {
						$google_status['0']='CHARGED';
						$google_status['partial_charge_amount']=$amount;
					}
				}
				break;
				
			case "Cancel":
				if ($google_status[0]!='CANCELLED_BY_GOOGLE')
				$google_status[0]='CANCELLED';
				if ($google_status[1]!='DELIVERED')
					$google_status[1]='WILL_NOT_DELIVER';
				break;
				
			case "Refund":
				if ($amount=='') {
					$google_status['0']='Partially Refund';
				} else {
					$google_status['0']='REFUND';
					$google_status['partial_refund_amount']=$amount;
				}
				break;
				
			case "Ship":
				if ($google_status[1]!='WILL_NOT_DELIVER')
					$google_status[1]='DELIVERED';
				break;
				
			case "Archive":
				$google_status[1]='ARCHIVED';
				break;
		}
		$google_status_sql="UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET google_status='".serialize($google_status)."' WHERE `id` = '".$_POST['id']."' LIMIT 1";
		$wpdb->query($google_status_sql);
		$merchant_id = get_option('google_id');
		$merchant_key = get_option('google_key');
		$server_type = get_option('google_server_type');
		$currency = get_option('google_cur');
		$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type,$currency);
		$google_order_number=$wpdb->get_var("SELECT google_order_number FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id` = '".$_POST['id']."' LIMIT 1");
		switch ($newvalue) {
			case 'Charge':
				$Grequest->SendChargeOrder($google_order_number,$amount);
				break;
				
			case 'Ship':
				$Grequest->SendDeliverOrder($google_order_number);
				break;
				
			case 'Archive':
				$Grequest->SendArchiveOrder($google_order_number);
				break;
			
			case 'Refund':
				$Grequest->SendRefundOrder($google_order_number,$amount,$reason);
				break;
				
			case 'Cancel':
				$Grequest->SendCancelOrder($google_order_number,$reason,$comment);
				break;
			
			case 'Send Message':
				$Grequest->SendBuyerMessage($google_order_number,$message);
				break;
		}
		$newvalue++;
		$update_sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '".$newvalue."' WHERE `id` = '".$_POST['id']."' LIMIT 1";  
		//$wpdb->query($update_sql);
		
		exit();
	}


	if(($_GET['user'] == "true") && is_numeric($_POST['prodid'])) {
		if(function_exists('wpsc_members_init')) {
			$memberstatus = get_product_meta($_POST['prodid'],'is_membership',true);
		}

		if(($memberstatus=='1') && ($_SESSION['nzshopcrt_cart']!=NULL)){
		} else{
			$sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='".$_POST['prodid']."' LIMIT 1";
			$item_data = $wpdb->get_results($sql,ARRAY_A);
			if ($_POST['quantity']!='') {
				$add_quantity = $_POST['quantity'];
			}
			$item_quantity = 0;
			if($_SESSION['nzshpcrt_cart'] != null) {
				foreach($_SESSION['nzshpcrt_cart'] as $cart_key => $cart_item) {
					if (($memberstatus[0]!='1')&&($_SESSION['nzshpcrt_cart']!=NULL)){
						if($cart_item->product_id == $_POST['prodid']) {
							if(($_SESSION['nzshpcrt_cart'][$cart_key]->product_variations === $_POST['variation'])&&($_SESSION['nzshpcrt_cart'][$cart_key]->extras === $_POST['extras'])) {
								$item_quantity += $_SESSION['nzshpcrt_cart'][$cart_key]->quantity;
								$item_variations = $_SESSION['nzshpcrt_cart'][$cart_key]->product_variations;
							}
						}
					}
				}
			}
		  
		  $item_stock = null;
		  $variation_count = count($_POST['variation']);
		  if($variation_count >= 1) {
				foreach($_POST['variation'] as $value_id) {
					if(is_numeric($value_id)) {
						$value_ids[] = (int)$value_id;
					}
				}
				
        if(count($value_ids) > 0) {
          $variation_ids = $wpdb->get_col("SELECT `variation_id` FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `id` IN ('".implode("','",$value_ids)."')");
          asort($variation_ids);
          $all_variation_ids = implode(",", $variation_ids);
        
        
          $priceandstock_id = $wpdb->get_var("SELECT `priceandstock_id` FROM `".WPSC_TABLE_VARIATION_COMBINATIONS."` WHERE `product_id` = '".(int)$_POST['prodid']."' AND `value_id` IN ( '".implode("', '",$value_ids )."' )  AND `all_variation_ids` IN('$all_variation_ids')  GROUP BY `priceandstock_id` HAVING COUNT( `priceandstock_id` ) = '".count($value_ids)."' LIMIT 1");
          
          $variation_stock_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_VARIATION_PROPERTIES."` WHERE `id` = '{$priceandstock_id}' LIMIT 1", ARRAY_A);
          
          $item_stock = $variation_stock_data['stock'];
          //echo "/*".print_r($variation_stock_data,true)."*/";
        }				
			}

			
		if($item_stock === null) {
			$item_stock = $item_data[0]['quantity'];
		}
		
			if((($item_data[0]['quantity_limited'] == 1) && ($item_stock > 0) && ($item_stock > $item_quantity)) || ($item_data[0]['quantity_limited'] == 0)) {
				$cartcount = count($_SESSION['nzshpcrt_cart']);
				if(is_array($_POST['variation'])) {  $variations = $_POST['variation'];  }  else  { $variations = null; }
				//if(is_array($_POST['extras'])) {  $extras = $_POST['extras'];  }  else  { $extras = null; }
				$updated_quantity = false;
				if($_SESSION['nzshpcrt_cart'] != null) {
					foreach($_SESSION['nzshpcrt_cart'] as $cart_key => $cart_item) {
						if ((!($memberstatus[0]=='1')&&(count($_SESSION['nzshpcrt_cart'])>0))) {
							if((int)$cart_item->product_id === (int)$_POST['prodid']) {  // force both to integer before testing for identicality
								if(($_SESSION['nzshpcrt_cart'][$cart_key]->extras === $extras)&&($_SESSION['nzshpcrt_cart'][$cart_key]->product_variations === $variations) && ((int)$_SESSION['nzshpcrt_cart'][$cart_key]->donation_price == (int)$_POST['donation_price'])) {
									if ($_POST['quantity'] != ''){
									  if(is_array($_POST['quantity'])) {
											foreach ((array)$_POST['quantity'] as $qty) {
												$_SESSION['nzshpcrt_cart'][$cart_key]->quantity += (int)$qty;
											}
										} else {
											$_SESSION['nzshpcrt_cart'][$cart_key]->quantity += (int)$_POST['quantity'];
										}
									} else {
										$_SESSION['nzshpcrt_cart'][$cart_key]->quantity++;
									}
									$_SESSION['nzshpcrt_cart'][$cart_key]->comment = $_POST['comment'];
									foreach((array)$_POST['label'] as $key => $label) {
										if ($label != '') {
											if (array_key_exists($label, $_SESSION['nzshpcrt_cart'][$cart_key]->meta)) {
												$_SESSION['nzshpcrt_cart'][$cart_key]->meta[$label]+=(int)$_POST['quantity'][$key];
												$_SESSION['nzshpcrt_cart'][$cart_key]->time_requested[$label] = $_POST['time_requested'][$key];
											} else {
												$_SESSION['nzshpcrt_cart'][$cart_key]->meta[$label] = $_POST['quantity'][$key];
												$_SESSION['nzshpcrt_cart'][$cart_key]->time_requested[$label] = $_POST['time_requested'][$key];
											}
										}
									}
									$updated_quantity = true;
								}
							}
						}
					}
				}
				if($item_data[0]['donation'] == 1) {
					$donation = $_POST['donation_price'];
				} else {
					$donation = false;
				}
				if(!(($memberstatus=='1')&&(count($_SESSION['nzshpcrt_cart'])>0))){
					$status = get_product_meta($cartt1, 'is_membership', true);
					if (function_exists('wpsc_members_init') && ( $status=='1')){
						exit();
					}	
					$parameters = array();
					if($updated_quantity === false) {
						$parameters['variation_values'] = $variations;
						$parameters['provided_price'] = $donation;
						$parameters['meta']=null;
						if($_POST['quantity'] != '') {
							$total_qty = 0;
							foreach ($_POST['quantity'] as $key=>$qty) {
								$total_qty+=$qty;
								$label[$_POST['label'][$key]] = $qty;
								$time_requested[$_POST['label'][$key]] = $_POST['time_requested'][$key];
							}
							$parameters['quantity'] = $total_qty;
							//$new_cart_item = new wpsc_cart_item($_POST['prodid'],$variations,$total_qty, $donation,$_POST['comment'],$time_requested,$label);
						} else {
							$parameters['quantity'] = 1;
						}
						//mail('tom@instinct.co.nz', 'stuff', print_r($parameters,true));
						$new_cart_item = new wpsc_cart_item($_POST['prodid'],$parameters);
						$_SESSION['nzshpcrt_cart'][] = $new_cart_item;
					}
				}
			} else {
				$quantity_limit = true;
			}
		
			$cart = $_SESSION['nzshpcrt_cart'];
		
			if (($memberstatus[0]=='1')&&(count($cart)>1)) {
			} else {
				$status = get_product_meta($cartt1, 'is_membership', true);
				if (function_exists('wpsc_members_init') && ( $status=='1')){
					exit('st');
				}

			  echo  "if(document.getElementById('shoppingcartcontents') != null)
					  {
					  document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(nzshpcrt_shopping_basket_internals($cart,$quantity_limit))). "\";
					  }
					";
		
			  if(($_POST['prodid'] != null) &&(get_option('fancy_notifications') == 1)) {
				echo "if(document.getElementById('fancy_notification_content') != null)
					  {
					  document.getElementById('fancy_notification_content').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(fancy_notification_content($_POST['prodid'], $quantity_limit))). "\";
					  jQuery('#loading_animation').css('display', 'none');
					  jQuery('#fancy_notification_content').css('display', 'block');  
					  }
					";
				}
			  
			  if($_SESSION['slider_state'] == 0) {
				echo  'jQuery("#sliding_cart").css({ display: "none"});'."\n\r";
				} else {
				echo  'jQuery("#sliding_cart").css({ display: "block"});'."\n\r";
				}
			}
		}
      exit();
		} else if(($_POST['user'] == "true") && ($_POST['emptycart'] == "true")) {
			//exit("/* \n\r ".get_option('shopping_cart_url')." \n\r ".print_r($_POST,true)." \n\r */");
			$_SESSION['nzshpcrt_cart'] = '';			
			$_SESSION['nzshpcrt_cart'] = Array();      
			echo  "if(document.getElementById('shoppingcartcontents') != null) {   
			document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "", addslashes(nzshpcrt_shopping_basket_internals($cart))). "\";
			}\n\r";
			
			if($_POST['current_page'] == get_option('shopping_cart_url')) {
			  echo "window.location = '".get_option('shopping_cart_url')."';\n\r"; // if we are on the checkout page, redirect back to it to clear the non-ajax cart too
			}
			exit();
		}

	if ($_POST['store_list']=="true") {
		$map_data['address'] = $_POST['addr'];
		$map_data['city'] = $_POST['city'];
		$map_data['country'] = 'US';
		$map_data['zipcode']='';
		$map_data['radius'] = '50000';
		$map_data['state'] = '';
		$map_data['submit'] = 'Find Store';
		$stores = getdistance($map_data);
		$i=0;
		while($rows = mysql_fetch_array($stores)) {
			//echo "<pre>".print_r($rows,1)."</pre>";
			if ($i==0) {
				$closest_store = $rows[5];
			}
			$i++;
			$store_list[$i] = $rows[5];
		}
	foreach ($store_list as $store){
		$output.="<option value='$store'>$store</option>";
	}
	echo $output;
	exit();
	}
    
            
    
    if(is_numeric($_POST['currencyid'])){
      $currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id`='".$_POST['currencyid']."' LIMIT 1",ARRAY_A) ;
      $price_out = null;
      if($currency_data[0]['symbol'] != '') {
        $currency_sign = $currency_data[0]['symbol_html'];
			} else {
				$currency_sign = $currency_data[0]['code'];
			}
      echo $currency_sign;
      exit();
		}
		
	if($_POST['buynow'] == "true") {
		if(is_numeric($_REQUEST['product_id']) && is_numeric($_REQUEST['price'])) {
			$id = $wpdb->escape((int)$_REQUEST['product_id']);
			$price = $wpdb->escape((float)$_REQUEST['price']);
			$downloads = get_option('max_downloads');
			$product_info = $wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = ".$id." LIMIT 1", ARRAY_A);
			if(count($product_info) > 0) {
				$sessionid = (mt_rand(100,999).time());
				$sql = "INSERT INTO `".WPSC_TABLE_PURCHASE_LOGS."` ( `totalprice` , `sessionid` , `date`, `billing_country`, `shipping_country`,`shipping_region`, `user_ID`, `discount_value` ) VALUES ( '".$price."', '".$sessionid."', '".time()."', 'BuyNow', 'BuyNow', 'BuyNow' , NULL , 0)";
				$wpdb->query($sql) ;
				$log_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid` IN('".$sessionid."') LIMIT 1") ;
				$cartsql = "INSERT INTO `".WPSC_TABLE_CART_CONTENTS."` ( `prodid` , `purchaseid`, `price`, `pnp`, `gst`, `quantity`, `donation`, `no_shipping` ) VALUES ('".$id."', '".$log_id."','".$price."','0', '0','1', '".$donation."', '1')";
				$wpdb->query($cartsql);
				$wpdb->query("INSERT INTO `".WPSC_TABLE_DOWNLOAD_STATUS."` ( `fileid` , `purchid` , `downloads` , `active` , `datetime` ) VALUES ( '".$product_info['file']."', '".$log_id."', '$downloads', '0', NOW( ));");
			}
		}
		exit();
	}

	
    
    /* rate item */    
    if(($_POST['rate_item'] == "true") && is_numeric($_POST['product_id']) && is_numeric($_POST['rating'])) {
      $nowtime = time();
      $prodid = $_POST['product_id'];
      $ip_number = $_SERVER['REMOTE_ADDR'];
      $rating = $_POST['rating'];
      
      $cookie_data = explode(",",$_COOKIE['voting_cookie'][$prodid]);
      
      if(is_numeric($cookie_data[0]) && ($cookie_data[0] > 0)) {
        $vote_id = $cookie_data[0];
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_RATING."` SET `rated` = '".$rating."' WHERE `id` ='".$vote_id."' LIMIT 1 ;");
			} else {
				$insert_sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_RATING."` ( `ipnum`  , `productid` , `rated`, `time`) VALUES ( '".$ip_number."', '".$prodid."', '".$rating."', '".$nowtime."');";
				$wpdb->query($insert_sql);
				
				$data = $wpdb->get_results("SELECT `id`,`rated` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `ipnum`='".$ip_number."' AND `productid` = '".$prodid."'  AND `rated` = '".$rating."' AND `time` = '".$nowtime."' ORDER BY `id` DESC LIMIT 1",ARRAY_A) ;
				
				$vote_id = $data[0]['id'];
				setcookie("voting_cookie[$prodid]", ($vote_id.",".$rating),time()+(60*60*24*360));
			}
      
      
      
      $output[1]= $prodid;
      $output[2]= $rating;
      echo $output[1].",".$output[2];
      exit();
		}
//written by allen
	if ($_REQUEST['save_tracking_id'] == "true"){
		$id = $_POST['id'];
		$value = $_POST['value'];
		$update_sql = "UPDATE ".WPSC_TABLE_PURCHASE_LOGS." SET track_id = '".$value."' WHERE id=$id";
		$wpdb->query($update_sql);
		exit();
	}
      
	if(($_POST['get_updated_price'] == "true") && is_numeric($_POST['product_id'])) {
		$notax = $wpdb->get_var("SELECT `notax` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` IN('".$_POST['product_id']."') LIMIT 1");
		foreach((array)$_POST['variation'] as $variation) {
			if(is_numeric($variation)) {
				$variations[] = (int)$variation;
			}
		}
		$pm=$_POST['pm'];
		echo "product_id=".(int)$_POST['product_id'].";\n";
		
		echo "price=\"".nzshpcrt_currency_display(calculate_product_price((int)$_POST['product_id'], $variations,'stay',$extras), $notax, true)."\";\n";
		echo "numeric_price=\"".number_format(calculate_product_price((int)$_POST['product_id'], $variations,'stay',$extras), 2)."\";\n";
				//exit(print_r($extras,1));
		exit(" ");
  }
      
      
      
      
   
 


// 	if(($_POST['redisplay_variation_values'] == "true")) {
// 		$variation_processor = new nzshpcrt_variations();
// 		$variations_selected = array_values(array_unique(array_merge((array)$_POST['new_variation_id'], (array)$_POST['variation_id'])));		
// 		foreach($variations_selected as $variation_id) {
// 		  // cast everything to integer to make sure nothing nasty gets in.
// 		  $variation_list[] = (int)$variation_id;
// 		}
// 		echo $variation_processor->variations_add_grid_view((array)$variation_list);
// 		//echo "/*\n\r".print_r(array_values(array_unique($_POST['variation_id'])),true)."\n\r*/";
// 		exit();
// 	}
// 	


      
      
      /*
       * function for handling the checkout billing address
       */      
		if(preg_match("/[a-zA-Z]{2,4}/", $_POST['billing_country'])) {
			if($_SESSION['selected_country'] == $_POST['billing_country']) {
				$do_not_refresh_regions = true;
			} else {
				$do_not_refresh_regions = false;
				$_SESSION['selected_country'] = $_POST['billing_country'];
			}
      if(is_numeric($_POST['form_id'])) {
        $form_id = $_POST['form_id'];
        $html_form_id = "region_country_form_$form_id";
			} else {
				$html_form_id = 'region_country_form';
			}
        
			if(is_numeric($_POST['billing_region'])) {
				$_SESSION['selected_region'] = $_POST['billing_region'];
			}
      $cart =& $_SESSION['nzshpcrt_cart'];
			if (($memberstatus[0]=='1')&&(count($cart)>0)){
				echo "\n\r";
			} else {
				if ($status[0]=='1'){
					exit();
				}
			  echo  "if(document.getElementById('shoppingcartcontents') != null)
					  {
					  document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(nzshpcrt_shopping_basket_internals($cart,$quantity_limit))). "\";
					  }\n\r";
		
			  if($do_not_refresh_regions == false) {
					$region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".$_POST['billing_country']."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
				  if($region_list != null) {
						$output .= "<select name='collected_data[".$form_id."][1]' class='current_region' onchange='set_billing_country(\\\"$html_form_id\\\", \\\"$form_id\\\");'>";
						//$output .= "<option value=''>None</option>";
						foreach($region_list as $region) {
							if($_SESSION['selected_region'] == $region['id']) {
								$selected = "selected='true'";
							} else {
								$selected = "";
							}
							$output .= "<option value='".$region['id']."' $selected>".$region['name']."</option>";
						}
						$output .= "</select>";
						echo  "if(document.getElementById('region_select_$form_id') != null)
							{
							document.getElementById('region_select_$form_id').innerHTML = \"".$output."\";
							}\n\r";
					} else {
						echo  "if(document.getElementById('region_select_$form_id') != null)
						{
						document.getElementById('region_select_$form_id').innerHTML = \"\";
						}\n\r";
					}
				}
			}
		if ($_POST['changetax'] == "true") {
				if (isset($_POST['billing_region'])){
					$billing_region=$_POST['billing_region'];
				} else {
					$billing_region=$_SESSION['selected_region'];
				}
				$billing_country=$_POST['billing_country'];
				$price = 0;
				$tax = 0;
				foreach($cart as $cart_item) {
					$product_id = $cart_item->product_id;
					$quantity = $cart_item->quantity;
					//echo("<pre>".print_r($cart_item->product_variations,true)."</pre>");
					$product = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
				
					if($product['donation'] == 1) {
						$price += $quantity * $cart_item->donation_price;
					} else {
						$product_price = $quantity * calculate_product_price($product_id, $cart_item->product_variations);
						if($product['notax'] != 1) {
							$tax += nzshpcrt_calculate_tax($product_price, $billing_country, $billing_region) - $product_price;
						}
						$price += $product_price;
						$all_donations = false;
					}
		
					if($_SESSION['delivery_country'] != null) {
						$total_shipping += nzshpcrt_determine_item_shipping($product['id'], $quantity, $_SESSION['delivery_country']);
					}
				}
				
				$total_shipping +=  nzshpcrt_determine_base_shipping(0, $_SESSION['delivery_country']);
				
				$total = number_format(($tax+$price+$total_shipping), 2);
				
				
					if($tax > 0) {
						echo  "jQuery(\"tr.total_tax td\").show();\n\r";
					} else {
						echo  "jQuery(\"tr.total_tax td\").hide();\n\r";
					}
					$tax = number_format($tax,2);
					echo  "jQuery('#checkout_tax').html(\"<span class='pricedisplay'>\${$tax}</span>\");\n\r";
					echo  "jQuery('#checkout_total').html(\"<span class='pricedisplay'>\${$total}</span><input id='shopping_cart_total_price' type='hidden' value='\${$total}'>\");\n\r";
			}  
			exit();
		}
    
    if(($_POST['get_country_tax'] == "true") && preg_match("/[a-zA-Z]{2,4}/",$_POST['country_id'])) {
      $country_id = $_POST['country_id'];
      $region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".$country_id."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
      if($region_list != null) {
        echo "<select name='base_region'>\n\r";
        foreach($region_list as $region) {
          if(get_option('base_region')  == $region['id']) {
            $selected = "selected='true'";
					} else {
						$selected = "";
					}
          echo "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
				}
        echo "</select>\n\r";    
			}  else { echo "&nbsp;"; }
      exit();
		}
    /* fill product form */    
    if(($_POST['set_slider'] == "true") && is_numeric($_POST['state'])) {
      $_SESSION['slider_state'] = $_POST['state'];
      exit();
		}  /* fill category form */
      
      
     
      
    if($_GET['action'] == "register")
      {
      $siteurl = get_option('siteurl');       
      require_once( ABSPATH . WPINC . '/registration-functions.php');
      if(($_POST['action']=='register') && get_settings('users_can_register'))
        {        
        //exit("fail for testing purposes");
        $user_login = sanitize_user( $_POST['user_login'] );
        $user_email = $_POST['user_email'];
        
        $errors = array();
          
        if ( $user_login == '' )
          exit($errors['user_login'] = __('<strong>ERROR</strong>: Please enter a username.'));
      
        /* checking e-mail address */
        if ($user_email == '') {
          exit(__('<strong>ERROR</strong>: Please type your e-mail address.'));
        } else if (!is_email($user_email)) {
          exit( __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
          $user_email = '';
        }
      
        if ( ! validate_username($user_login) ) {
          $errors['user_login'] = __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.');
          $user_login = '';
        }
      
        if ( username_exists( $user_login ) )
          exit( __('<strong>ERROR</strong>: This username is already registered, please choose another one.'));
      
        /* checking the email isn't already used by another user */
        $email_exists = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
        if ( $email_exists)
          die (__('<strong>ERROR</strong>: This email address is already registered, please supply another.'));
      
      
      
        
        if ( 0 == count($errors) ) {
          $password = substr( md5( uniqid( microtime() ) ), 0, 7);
          //xit('there?');      
          $user_id = wp_create_user( $user_login, $password, $user_email );
          if ( !$user_id ) {
            exit(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_settings('admin_email')));
          } else {
            wp_new_user_notification($user_id, $password);
            ?>
<div id="login"> 
  <h2><?php _e('Registration Complete') ?></h2>
  <p><?php printf(__('Username: %s'), "<strong>" . wp_specialchars($user_login) . "</strong>") ?><br />
  <?php printf(__('Password: %s'), '<strong>' . __('emailed to you') . '</strong>') ?> <br />
  <?php printf(__('E-mail: %s'), "<strong>" . wp_specialchars($user_email) . "</strong>") ?></p>
</div>
<?php
            }
          }
        }
        else
          {
          // onsubmit='submit_register_form(this);return false;'
          echo "<div id='login'>
    <h2>Register for this blog</h2>
    <form id='registerform' action='index.php?ajax=true&amp;action=register'  onsubmit='submit_register_form(this);return false;' method='post'>
      <p><input type='hidden' value='register' name='action'/>
      <label for='user_login'>Username:</label><br/> <input type='text' value='' maxlength='20' size='20' id='user_login' name='user_login'/><br/></p>
      <p><label for='user_email'>E-mail:</label><br/> <input type='text' value='' maxlength='100' size='25' id='user_email' name='user_email'/></p>
      <p>A password will be emailed to you.</p>
      <p class='submit'><input type='submit' name='submit_form' id='submit' value='".TXT_WPSC_REGISTER." »'/><img id='register_loading_img' src='".WPSC_URL."/images/loading.gif' alt='' title=''></p>

      
    </form>
    </div>";
         }
      
      exit();
      } 
      
    }
    /*
    * AJAX stuff stops here, I would put an exit here, but it may screw up other plugins
    //exit();
    */
    }
  
    
    
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
    
    include_once(WPSC_FILE_PATH."/product_display_functions.php");
    include_once(WPSC_FILE_PATH."/show_cats_brands.php");
    
    
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
    
  
  if($_GET['termsandconds'] === 'true')
    {
    echo stripslashes(get_option('terms_and_conditions'));
    exit();
    }
    
    require_once(WPSC_FILE_PATH . '/processing_functions.php');


/* 
 * This plugin gets the merchants from the merchants directory and
 * needs to search the merchants directory for merchants, the code to do this starts here
 */
$gateway_directory = WPSC_FILE_PATH.'/merchants';
$nzshpcrt_merchant_list = nzshpcrt_listdir($gateway_directory);
 //exit("<pre>".print_r($nzshpcrt_merchant_list,true)."</pre>");
$num=0;
foreach($nzshpcrt_merchant_list as $nzshpcrt_merchant) {
  if(stristr( $nzshpcrt_merchant , '.php' )) {
    //echo $nzshpcrt_merchant;
    require(WPSC_FILE_PATH."/merchants/".$nzshpcrt_merchant);
	}
  $num++;
}
/* 
 * and ends here
 */
// include shipping modules here.
$shipping_directory = WPSC_FILE_PATH.'/shipping';
$nzshpcrt_shipping_list = nzshpcrt_listdir($shipping_directory);
foreach($nzshpcrt_shipping_list as $nzshpcrt_shipping) {
	if(stristr( $nzshpcrt_shipping , '.php' )) {
		require(WPSC_FILE_PATH."/shipping/".$nzshpcrt_shipping);
	}
}
    
    if(is_numeric($_GET['remove']) && ($_SESSION['nzshpcrt_cart'] != null)) {
      $key = $_GET['remove'];
      if(is_object($_SESSION['nzshpcrt_cart'][$key])){
        $_SESSION['nzshpcrt_cart'][$key]->empty_item();
			}
      unset($_SESSION['nzshpcrt_cart'][$key]);
		}
    
    if($_GET['cart']== 'empty') {
      $_SESSION['nzshpcrt_cart'] = '';
      $_SESSION['nzshpcrt_cart'] = Array();
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
// 				return false;
				exit(WPSC_DOWNLOAD_INVALID);
		  }
		}
		
    //exit("<pre>".print_r($download_data,true)."</pre>");
   
    if($download_data != null) {
      $file_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_FILES."` WHERE `id`='".$download_data['fileid']."' LIMIT 1",ARRAY_A) ;
      $file_data = $file_data[0];      
      
      if((int)$download_data['downloads'] >= 1) {
        $download_count = (int)$download_data['downloads'] - 1;
      } else {
        $download_count = 0;
      }
      
      
      $wpdb->query("UPDATE `".WPSC_TABLE_DOWNLOAD_STATUS."` SET `downloads` = '{$download_count}' WHERE `id` = '{$download_data['id']}' LIMIT 1");

      $wpdb->query("UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed` = '4' WHERE `id` = '".$download_data['purchid']."' LIMIT 1");
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
        session_destroy();
        readfile_chunked($filename);   
        exit();
			}
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
					readfile_chunked($filename);   
					session_destroy();
					exit();
				}            
			}
    }
  }
}

function nzshpcrt_display_preview_image() {
	  global $wpdb;
	  if(is_numeric($_GET['productid']) || is_numeric($_GET['image_id'])) {
		if(function_exists("getimagesize")) {
			if(is_numeric($_GET['productid'])) {
				$product_id = (int)$_GET['productid'];
				$imagesql = "SELECT `image`,`thumbnail_image` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1";
				$imagedata = $wpdb->get_row($imagesql,ARRAY_A);
				if($_GET['thumbnail'] == 'true') {
					if($imagedata['thumbnail_image'] != '') {
						$image_name = $imagedata['thumbnail_image'];
					} else {
						$image_name = $imagedata['image'];
					}
					$imagepath = WPSC_THUMBNAIL_DIR . $image_name;
				} else {
					$imagepath = WPSC_IMAGE_DIR . $imagedata['image'];
				}
			} else if($_GET['image_id']) {
				$image_id = (int)$_GET['image_id'];
				$image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id` = '{$image_id}' LIMIT 1");
				$imagepath = WPSC_IMAGE_DIR . $image;
			}
			$image_size = @getimagesize($imagepath);
			if(is_numeric($_GET['height']) && is_numeric($_GET['width'])) {
				$height = (int)$_GET['height'];
				$width = (int)$_GET['width'];
			} else {
				$width = $image_size[0];
				$height = $image_size[1];
			}
			if(!(($height > 0) && ($height <= 1024) && ($width > 0) && ($width <= 1024))) { 
				$width = $image_size[0];
				$height = $image_size[1];
			}
			$cache_filename = basename("product_{$product_id}_{$height}x{$width}");
			include("image_preview.php");
		}
	}
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
    
    

function nzshpcrt_product_rating($prodid)
      {
      global $wpdb;
      $get_average = $wpdb->get_results("SELECT AVG(`rated`) AS `average`, COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `productid`='".$prodid."'",ARRAY_A);
      $average = floor($get_average[0]['average']);
      $count = $get_average[0]['count'];
      $output .= "  <span class='votetext'>";
      for($l=1; $l<=$average; ++$l)
        {
        $output .= "<img class='goldstar' src='". WPSC_URL."/images/gold-star.gif' alt='$l' title='$l' />";
        }
      $remainder = 5 - $average;
      for($l=1; $l<=$remainder; ++$l)
        {
        $output .= "<img class='goldstar' src='". WPSC_URL."/images/grey-star.gif' alt='$l' title='$l' />";
        }
      $output .=  "<span class='vote_total'>&nbsp;(<span id='vote_total_$prodid'>".$count."</span>)</span> \r\n";
      $output .=  "</span> \r\n";
      return $output;
      }

// this appears to have some star rating code in it
function nzshpcrt_product_vote($prodid, $starcontainer_attributes = '')
      {
      global $wpdb;
      $output = null;
      $useragent = $_SERVER['HTTP_USER_AGENT'];
      $visibility = "style='display: none;'";
      
      preg_match("/(?<=Mozilla\/)[\d]*\.[\d]*/", $useragent,$rawmozversion );
      $mozversion = $rawmozversion[0];
      if(stristr($useragent,"opera"))
        {
        $firstregexp = "Opera[\s\/]{1}\d\.[\d]+";
        }
        else
          {
          $firstregexp = "MSIE\s\d\.\d";
          }
      preg_match("/$firstregexp|Firefox\/\d\.\d\.\d|Netscape\/\d\.\d\.\d|Safari\/[\d\.]+/", $useragent,$rawbrowserinfo);
      $browserinfo = preg_split("/[\/\s]{1}/",$rawbrowserinfo[0]);
      $browsername = $browserinfo[0];
      $browserversion = $browserinfo[1];  
      
      //exit($browsername . " " . $browserversion);
       
      if(($browsername == 'MSIE') && ($browserversion < 7.0))
        {
        $starimg = ''. get_option('siteurl').'/wp-content/plugins/'.WPSC_DIR_NAME.'/images/star.gif';
        $ie_javascript_hack = "onmouseover='ie_rating_rollover(this.id,1)' onmouseout='ie_rating_rollover(this.id,0)'";
        }
        else 
          {
          $starimg = ''. get_option('siteurl').'/wp-content/plugins/'.WPSC_DIR_NAME.'/images/24bit-star.png';
          $ie_javascript_hack = '';
          }
       
      $cookie_data = explode(",",$_COOKIE['voting_cookie'][$prodid]);
       
      if(is_numeric($cookie_data[0]))
        {
        $vote_id = $cookie_data[0];
        }
      
      $chkrate = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_RATING."` WHERE `id`='".$vote_id."' LIMIT 1",ARRAY_A);
      //$output .= "<pre>".print_r($chkrate,true)."</pre>";
      if($chkrate[0]['rated'] > 0)
        {
        $rating = $chkrate[0]['rated'];
        $type = 'voted';
        }
        else
          {
          $rating = 0;
          $type = 'voting';
          }
      //$output .= "<pre>".print_r($rating,true)."</pre>";
      $output .=  "<div class='starcontainer' $starcontainer_attributes >\r\n";
      for($k=1; $k<=5; ++$k)
        {
        $style = '';
        if($k <= $rating)
          {
          $style = "style='background: url(". WPSC_URL."/images/gold-star.gif)'";
          }
        $output .= "      <a id='star".$prodid."and".$k."_link' onclick='rate_item(".$prodid.",".$k.")' class='star$k' $style $ie_javascript_hack ><img id='star".$prodid."and".$k."' class='starimage' src='$starimg' alt='$k' title='$k' /></a>\r\n";
        }
      $output .=  "   </div>\r\n";
      $output .= "";
      $voted = TXT_WPSC_CLICKSTARSTORATE;
      
      switch($ratecount[0]['count'])
        {
        case 0:
        $votestr = TXT_WPSC_NOVOTES;
        break;
        
        case 1:
        $votestr = TXT_WPSC_1VOTE;
        break;
        
        default:
        $votestr = $ratecount[0]['count']." ".TXT_WPSC_VOTES2;
        break;
        }
        
      for($i= 5; $i>= 1; --$i)
         {
        //$tmpcount = $this->db->GetAll("SELECT COUNT(*) AS 'count' FROM `pxtrated` WHERE `pxtid`=".$dbdat['rID']." AND `rated`=$i");
            
         switch($tmpcount[0]['count'])
           {
           case 0:
           $othervotes .= "";
           break;
           
           case 1:
           $othervotes .= "<br />". $tmpcount[0]['count'] . " ".TXT_WPSC_PERSONGIVEN." $i ".TXT_WPSC_PERSONGIVEN2;
           break;
           
           default:
           $othervotes .= "<br />". $tmpcount[0]['count'] . " ".TXT_WPSC_PEOPLEGIVEN." $i ".TXT_WPSC_PEOPLEGIVEN2;
           break;
           }  
         } /*
      $output .=  "</td><td class='centerer2'>&nbsp;</td></tr>\r\n";
      $output .= "<tr><td colspan='3' class='votes' >\r\n";//id='startxtmove'
      $output .= "   <p class='votes'> ".$votestr."<br />$voted <br />
      $othervotes</p>";*/
      
      return Array($output,$type);
      } //*/
  

 function get_country($country_code)  
  {
  global $wpdb;
  $country = $wpdb->get_var("SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode` IN ('".$country_code."') LIMIT 1");
  return $country; 
  }

 function get_region($region_code)  
  {
  global $wpdb;
  $region = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_REGION_TAX."` WHERE `id` IN('$region_code')");
  return $region; 
  }
  
function get_brand($brand_id) {  }


function filter_input_wp($input) {
  // if the input is numeric, then its probably safe
  if(is_numeric($input)) {
    $output = $input;
	} else {
		// if its not numeric, then make it safe
		if(!get_magic_quotes_gpc()) {
			$output = mysql_real_escape_string($input);
		} else {
			$output = mysql_real_escape_string(stripslashes($input));
		}
	}
	return $output;
}
    
function make_csv($array) {
  $count = count($array);
  $num = 1;
  foreach($array as $value) {
    $output .= "'$value'";
    if($num < $count) {
      $output .= ",";
		}
    $num++;
	}
  return $output;
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



// This function displays the category groups, it is used by the above function
function nzshpcrt_display_categories_groups() {
    global $wpdb;

    if(get_option('permalink_structure') != '') {
      $seperator ="?";
    } else {
      $seperator ="&amp;";
    }

    if(function_exists('gold_shpcrt_search_form') && get_option('show_search') == 1) {
      echo gold_shpcrt_search_form();
    }

    //include("show_cats_brands.php");
    if (get_option('cat_brand_loc') == 0) {
      show_cats_brands();
    }
  }


function add_product_meta($product_id, $key, $value, $unique = false, $custom = false) {
  global $wpdb, $post_meta_cache, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
    if(($unique == true) && $wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE meta_key = '$key' AND product_id = '$product_id'")) {
      return false;
		}
    
    $value = $wpdb->escape(maybe_serialize($value));
    
    if(!$wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE meta_key = '$key' AND product_id = '$product_id'")) {
      $custom = (int)$custom;
      $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (product_id,meta_key,meta_value, custom) VALUES ('$product_id','$key','$value', '$custom')");
		} else {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET meta_value = '$value' WHERE meta_key = '$key' AND product_id = '$product_id'");
		}
    return true;
	}
  return false; 
}
  
function delete_product_meta($product_id, $key, $value = '') {
  global $wpdb, $post_meta_cache, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
    if ( empty($value) ) {
      $meta_id = $wpdb->get_var("SELECT id FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key'");      
      if(is_numeric($meta_id) && ($meta_id > 0)) {
        $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key'");
        }
      } else {
      $meta_id = $wpdb->get_var("SELECT id FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key' AND meta_value = '$value'");
      if(is_numeric($meta_id) && ($meta_id > 0)) {
        $wpdb->query("DELETE FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id = '$product_id' AND meta_key = '$key' AND meta_value = '$value'");
        }        
      }
  }
  return true;
}


function get_product_meta($product_id, $key, $single = false) {
  global $wpdb, $post_meta_cache, $blog_id;  
  $product_id = (int)$product_id;
  if($product_id > 0) {
    $meta_id = $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id' LIMIT 1");
    if(is_numeric($meta_id) && ($meta_id > 0)) {      
      if($single != false) {
        $meta_values = maybe_unserialize($wpdb->get_var("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id' LIMIT 1"));
			} else {
        $meta_values = $wpdb->get_col("SELECT `meta_value` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND `product_id` = '$product_id'");
				$meta_values = array_map('maybe_unserialize', $meta_values);
			}
		}
	} else {
    $meta_values = false;
	}
	
	if (is_array($meta_values) && (count($meta_values) == 1)) {
		return array_pop($meta_values);
	} else {
		return $meta_values;
	}
}

function update_product_meta($product_id, $key, $value, $prev_value = '') {
  global $wpdb, $blog_id;
  $product_id = (int)$product_id;
  if($product_id > 0) {
  $value = $wpdb->escape(maybe_serialize($value));
  
  if(!empty($prev_value)) {
    $prev_value = $wpdb->escape(maybe_serialize($prev_value));
    }

  if($wpdb->get_var("SELECT meta_key FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN('$key') AND product_id = '$product_id'")) {
    if (empty($prev_value)) {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_value` = '$value' WHERE `meta_key` IN('$key') AND product_id = '$product_id'");
      } else {
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCTMETA."` SET `meta_value` = '$value' WHERE `meta_key` IN('$key') AND product_id = '$product_id' AND meta_value = '$prev_value'");
      }
    } else {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (product_id,meta_key,meta_value) VALUES ('$product_id','$key','$value')");
    }
  return true;
  }
}
    
    
    
    
function wpsc_refresh_page_urls($content) {
 global $wpdb;
 $wpsc_pageurl_option['product_list_url'] = '[productspage]';
 $wpsc_pageurl_option['shopping_cart_url'] = '[shoppingcart]';
 $check_chekout = $wpdb->get_var("SELECT `guid` FROM `".$wpdb->prefix."posts` WHERE `post_content` LIKE '%[checkout]%' AND `post_type` NOT IN('revision') LIMIT 1");
 if($check_chekout != null) {
   $wpsc_pageurl_option['checkout_url'] = '[checkout]';
   } else {
   $wpsc_pageurl_option['checkout_url'] = '[checkout]';
   }
 $wpsc_pageurl_option['transact_url'] = '[transactionresults]';
 $wpsc_pageurl_option['user_account_url'] = '[userlog]';
 $changes_made = false;
 foreach($wpsc_pageurl_option as $option_key => $page_string) {
   $post_id = $wpdb->get_var("SELECT `ID` FROM `".$wpdb->prefix."posts` WHERE `post_type` IN('page','post') AND `post_content` LIKE '%$page_string%' AND `post_type` NOT IN('revision') LIMIT 1");
   $the_new_link = get_permalink($post_id);
   if(stristr(get_option($option_key), "https://")) {
     $the_new_link = str_replace('http://', "https://",$the_new_link);
   }    
   update_option($option_key, $the_new_link);
  }
 return $content;
}
  

		function wpsc_product_permalinks($rewrite_rules) {
		global $wpdb, $wp_rewrite;  
		
		$page_details = $wpdb->get_row("SELECT * FROM `".$wpdb->posts."` WHERE `post_content` LIKE '%[productspage]%' AND `post_type` NOT IN('revision') LIMIT 1", ARRAY_A);
		$is_index = false;
		if((get_option('page_on_front') == $page_details['ID']) && (get_option('show_on_front') == 'page')) {		
		  $is_index = true;
		}
		
		$page_name_array[] = $page_details['post_name'];
		if($page_details['post_parent'] > 0) {
		  $count = 0;
		  while(($page_details['post_parent'] > 0) && ($count <= 20)) {
				$page_details = $wpdb->get_row("SELECT * FROM `".$wpdb->posts."` WHERE `ID` IN('{$page_details['post_parent']}') AND `post_type` NOT IN('revision') LIMIT 1", ARRAY_A);
				$page_name_array[] = $page_details['post_name'];
				$count ++;	  
		  }		
		}
		
		$page_name_array = array_reverse($page_name_array);
		$page_name = implode("/",$page_name_array);
		
		if(!function_exists('wpsc_rewrite_categories')) {	 // to stop this function from being declared multiple times
		  /*
		   * This is the function for making the e-commerce rewrite rules, it is recursive
		  */
			function wpsc_rewrite_categories($page_name, $id = null, $level = 0, $parent_categories = array(), $is_index = false) {
				global $wpdb,$category_data;
				if($is_index == true) {
				  $rewrite_page_name = '';				  
				} else {
				  $rewrite_page_name = $page_name.'/';
				}
				
				if(is_numeric($id)) {
					$category_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '".$id."' ORDER BY `id`";
					$category_list = $wpdb->get_results($category_sql,ARRAY_A);
				}	else {
					$category_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active`='1' AND `category_parent` = '0' ORDER BY `id`";
					$category_list = $wpdb->get_results($category_sql,ARRAY_A);
				}
				if($category_list != null)	{
					foreach($category_list as $category) {
						if($level === 0) {
							$parent_categories = array();
						}
						$parent_categories[] = $category['nice-name'];
						$new_rules[($rewrite_page_name.implode($parent_categories,"/").'/?$')] = 'index.php?pagename='.$page_name.'&category_id='.$category['id'];
						$new_rules[($rewrite_page_name.implode($parent_categories,"/").'/([A-Za-z0-9\-]+)/?$')] = 'index.php?pagename='.$page_name.'&category_id='.$category['id'].'&product_url_name=$matches[1]';
						$new_rules[($rewrite_page_name.implode($parent_categories,"/").'/page/([0-9]+)/?$')] = 'index.php?pagename='.$page_name.'&category_id='.$category['id'].'&wpsc_page=$matches[1]';
						// recurses here
						$sub_rules = wpsc_rewrite_categories($page_name, $category['id'], ($level+1), $parent_categories, $is_index);
						array_pop($parent_categories);
						$new_rules = array_merge((array)$new_rules, (array)$sub_rules);
					}
				}
			return $new_rules;
			}
		}
		
		
		$new_rules = wpsc_rewrite_categories($page_name, null, 0, null, $is_index);
		$new_rules = array_reverse((array)$new_rules);
	  //$new_rules[$page_name.'/product-tag/(.+?)/page/?([0-9]{1,})/?$'] = 'index.php?pagename='.$page_name.'&ptag=$matches[1]&paged=$matches[2]';
	  $new_rules[$page_name.'/tag/([A-Za-z0-9\-]+)?$'] = 'index.php?pagename='.$page_name.'&ptag=$matches[1]';
		$new_rewrite_rules = array_merge((array)$new_rules,(array)$rewrite_rules);
		return $new_rewrite_rules;
	}


function wpsc_query_vars($vars) {
	//   $vars[] = "product_category";
	//   $vars[] = "product_name";
  $vars[] = "category_id";
  $vars[] = "product_url_name";
  $vars[] = "wpsc_page";
  return $vars;
  }

add_filter('query_vars', 'wpsc_query_vars');

// using page_rewrite_rules makes it so that odd permalink structures like /%category%/%postname%.htm do not override the plugin permalinks.
add_filter('page_rewrite_rules', 'wpsc_product_permalinks');
 
 
 
 
 
 
  
function wpsc_obtain_the_title() {
  global $wpdb, $wp_query, $wpsc_title_data;
  $output = null;
	if(is_numeric($wp_query->query_vars['product_category'])) {
	  $category_id = $wp_query->query_vars['product_category'];
	  if(isset($wpsc_title_data['category'][$category_id])) {
			$output = $wpsc_title_data['category'][$category_id];
	  } else {
			$output = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id`='{$category_id}' LIMIT 1");
			$wpsc_title_data['category'][$category_id] = $output;
		}
		
	}
	if(isset($wp_query->query_vars['product_name'])) {
	  $product_name = $wp_query->query_vars['product_name'];
	  if(isset($wpsc_title_data['product'][$product_name])) {
	    $product_list = array();
	    $product_list['name'] = $wpsc_title_data['product'][$product_name];
	  } else {
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '{$wp_query->query_vars['product_name']}' ) ORDER BY `id` DESC LIMIT 1");
			$product_list = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`='{$product_id}' LIMIT 1",ARRAY_A);
			$wpsc_title_data['product'][$product_name] = $product_list['name'];
		}
  }
  if(isset($product_list ) && ($product_list != null)) {
  	$output = htmlentities(stripslashes($product_list['name']), ENT_QUOTES);
  }
	return $output;
}
 
function wpsc_replace_the_title($input) {
  global $wpdb, $wp_query;
	$output = wpsc_obtain_the_title();
	if($output != null) {
		$backtrace = debug_backtrace();
		if($backtrace[3]['function'] == 'get_the_title') {
			return $output;
		}
	}
	return $input;
}

function wpsc_replace_wp_title($input) {
  global $wpdb, $wp_query;
	$output = wpsc_obtain_the_title();
	if($output != null) {
		return $output;
	}
	return $input;
}
 
if(get_option('wpsc_replace_page_title') == 1) {
  add_filter('the_title', 'wpsc_replace_the_title', 10, 2);
  add_filter('wp_title', 'wpsc_replace_wp_title', 10, 2);
}



// need to sort the merchants here, after the gold ones are included. 
function wpsc_merchant_sort($a, $b) { 
  return strnatcmp(strtolower($a['name']), strtolower($b['name'])); 
} 
uasort($nzshpcrt_gateways, 'wpsc_merchant_sort'); 
require_once(WPSC_FILE_PATH."/currency_converter.inc.php"); 
require_once(WPSC_FILE_PATH."/form_display_functions.php"); 
require_once(WPSC_FILE_PATH."/shopping_cart_functions.php"); 
require_once(WPSC_FILE_PATH."/homepage_products_functions.php"); 
require_once(WPSC_FILE_PATH."/transaction_result_functions.php"); 
// include_once(WPSC_FILE_PATH.'/submit_checkout_function.php');
require_once(WPSC_FILE_PATH."/admin-form-functions.php");
require_once(WPSC_FILE_PATH."/shipwire_functions.php"); 

/* widget_section */
include_once(WPSC_FILE_PATH.'/widgets/product_tag_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/shopping_cart_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/category_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/donations_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/specials_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/latest_product_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/price_range_widget.php');
include_once(WPSC_FILE_PATH.'/widgets/admin_menu_widget.php');


include_once(WPSC_FILE_PATH.'/image_processing.php');
include_once(WPSC_FILE_PATH."/show_cats_brands.php");


$theme_path = WPSC_FILE_PATH . '/themes/';
if((get_option('wpsc_selected_theme') != '') && (file_exists($theme_path.get_option('wpsc_selected_theme')."/".get_option('wpsc_selected_theme').".php") )) {    
  include_once(WPSC_FILE_PATH.'/themes/'.get_option('wpsc_selected_theme').'/'.get_option('wpsc_selected_theme').'.php');
}
$current_version_number = get_option('wpsc_version');
if(count(explode(".",$current_version_number)) > 2) {
	// in a previous version, I accidentally had the major version number have two dots, and three numbers
	// this code rectifies that mistake
	$current_version_number_array = explode(".",$current_version_number);
	array_pop($current_version_number_array);
	$current_version_number = (float)implode(".", $current_version_number_array );
} else if(!is_numeric(get_option('wpsc_version'))) {
  $current_version_number = 0;
}

if(isset($_GET['activate']) && ($_GET['activate'] == 'true')) {
	include_once("install_and_update.php");
  add_action('init', 'nzshpcrt_install');
} else if(($current_version_number < WPSC_VERSION ) || (($current_version_number == WPSC_VERSION ) && (get_option('wpsc_minor_version') <= WPSC_MINOR_VERSION))) {
	include_once("install_and_update.php");
  add_action('init', 'wpsc_auto_update');
}

add_filter('single_post_title','wpsc_post_title_seo');
   

 
function wpsc_include_css_and_javascript() {
  // This must be weapped in a function in order to selectively prevent it from running using filters
  if(!apply_filters( 'wpsc_override_header', false)) {
    // expects false in order to to include the css and javascript
    add_action('wp_head', 'nzshpcrt_style');
    add_action('wp_head', 'nzshpcrt_javascript');
  }
}

add_action('init', 'wpsc_include_css_and_javascript');
add_action('wp_head', 'nzshpcrt_product_list_rss_feed');



add_action('admin_head', 'wpsc_admin_css');
if($_GET['page'] == WPSC_DIR_NAME."/display-log.php") {
  add_action('admin_head', 'nzshpcrt_product_log_rss_feed');
}

if(($_POST['submitwpcheckout'] == 'true')) {
  //add_action('init', 'nzshpcrt_submit_checkout');
}
add_action('init', 'nzshpcrt_submit_ajax');
add_action('init', 'nzshpcrt_download_file');
add_action('init', 'nzshpcrt_display_preview_image');

if(stristr($_GET['page'], WPSC_DIR_NAME)) {
  add_action('admin_notices', 'wpsc_admin_notices');
}

function wpsc_admin_notices() {
  global $wpdb;
  if(get_option('wpsc_default_category') != 'all') {
		if((get_option('wpsc_default_category') < 1) || $wpdb->get_var("SELECT `id` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `id` IN ('".get_option('wpsc_default_category')."') AND `active` NOT IN ('1');")) {  // if there is no default category or it is deleted
			if(!$_POST['wpsc_default_category']) { // if we are not changing the default category
				echo "<div id='message' class='updated fade' style='background-color: rgb(255, 251, 204);'>";
				echo "<p>".TXT_WPSC_NO_DEFAULT_PRODUCTS."</p>";
				echo "</div>\n\r";
			}
		}
  }
}


/*
 *	Inserts the summary box on the WordPress Dashboard
 */

//if(function_exists('wp_add_dashboard_widget')) {
if( IS_WP27 ) {
    add_action('wp_dashboard_setup','wpsc_dashboard_widget_setup');
} else {
    add_action('activity_box_end', 'wpsc_admin_dashboard_rightnow');
}

function wpsc_admin_latest_activity() {
global $wpdb;
		$totalOrders = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."`");
	
		 
		/*
		 * This is the right hand side for the past 30 days revenue on the wp dashboard
		 */
		
		echo "<div id='leftDashboard'>";
		echo "<strong class='dashboardHeading'>".TXT_WPSC_TOTAL_THIS_MONTH."</strong><br />";
		echo "<p class='dashboardWidgetSpecial'>";
		// calculates total amount of orders for the month
		$year = date("Y");
		$month = date("m");
		$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
		$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
		$sql = "SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC";
		$currentMonthOrders = $wpdb->get_var($sql);
		
		//calculates amount of money made for the month
		$currentMonthsSales = nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
		echo $currentMonthsSales;
		echo "<span class='dashboardWidget'>".TXT_WPSC_SALES_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		echo "<span class='pricedisplay'>";
		echo $currentMonthOrders;
		echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_ORDERS_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		//echo "<span class='pricedisplay'>";
		//calculates average sales amount per order for the month
		$monthsAverage = ((int)admin_display_total_price($start_timestamp, $end_timestamp)/(int)$currentMonthOrders);
		echo nzshpcrt_currency_display($monthsAverage,1);
		//echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_AVGORDER_TITLE."</span>";
		echo "</p>";
		
		
		echo "</div>";
		/*
		 *This is the left side for the total life time revenue on the wp dashboard
		 */
		
		echo "<div id='rightDashboard' >";
		echo "<strong class='dashboardHeading'>".TXT_WPSC_TOTAL_INCOME."</strong><br />";

		echo "<p class='dashboardWidgetSpecial'>";
		echo nzshpcrt_currency_display(admin_display_total_price(),1);
		echo "<span class='dashboardWidget'>".TXT_WPSC_SALES_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		echo "<span class='pricedisplay'>";
		echo $totalOrders;
		echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_ORDERS_TITLE."</span>";
		echo "</p>";
		echo "<p class='dashboardWidgetSpecial'>";
		//echo "<span class='pricedisplay'>";
		//calculates average sales amount per order for the month
		$totalAverage = ((int)admin_display_total_price()/(int)$totalOrders);
		echo nzshpcrt_currency_display($totalAverage,1);
		//echo "</span>";
		echo "<span class='dashboardWidget'>".TXT_WPSC_AVGORDER_TITLE."</span>";
		echo "</p>";
		echo "</div>";
		echo "<div style='clear:both'></div>";


}
add_action('wpsc_admin_pre_activity','wpsc_admin_latest_activity');

/*
 *	Pre-2.7 Dashboard Information
 */

function wpsc_admin_dashboard_rightnow() {
  $user = wp_get_current_user();
	if($user->user_level>9){
		echo "<div>";
		echo "<h3>".TXT_WPSC_E_COMMERCE."</h3>";
		echo "<p>";
		do_action('wpsc_admin_pre_activity');
//		wpsc_admin_latest_activity();
		do_action('wpsc_admin_post_activity');
		echo "</div>";
    }
}
		
/*
 * Dashboard Widget for 2.7 (TRansom)
 */
function wpsc_dashboard_widget_setup() {
    wp_add_dashboard_widget('wpsc_dashboard_widget', __('E-Commerce'),'wpsc_dashboard_widget');
}

function wpsc_dashboard_widget() {
    do_action('wpsc_admin_pre_activity');
//    wpsc_admin_latest_activity();
    do_action('wpsc_admin_post_activity');
}

/*
 * END - Dashboard Widget for 2.7
 */

//this adds all the admin pages, before the code was a mess, now it is slightly less so.

// pe.{
if((get_option('wpsc_share_this') == 1) && (get_option('product_list_url') != '')) {
  if(stristr(("http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), get_option('product_list_url'))){
    include_once(WPSC_FILE_PATH."/share-this.php");
  }
}
 
add_filter('option_update_plugins', 'wpsc_plugin_no_upgrade');
function wpsc_plugin_no_upgrade($option) {
	$this_plugin = plugin_basename(__FILE__);
  //echo "<pre>".print_r($option->response[ $this_plugin ],true)."</pre>";
	if( isset($option->response[ $this_plugin ]) ) {
		$option->response[ $this_plugin ]->package = '';
	}
	return $option;
}

// if(get_option('cat_brand_loc') != 0) {
//   add_action('wp_list_pages', 'show_cats_brands');
//   }
// }.pe
add_action('plugins_loaded', 'widget_wp_shopping_cart_init', 10);


// refresh page urls when permalinks are turned on or altered
add_filter('mod_rewrite_rules', 'wpsc_refresh_page_urls');

// refresh the page URL's when permalinks are turned off
// the plugin hook used just above doesnt run when they are turned off
// if(stristr($_POST['_wp_http_referer'], 'options-permalink.php')) {
// 	add_filter('admin_head', 'wpsc_refresh_page_urls');
// }


if(strpos($_SERVER['SCRIPT_NAME'], "wp-admin") === false) {
  wp_enqueue_script( 'jQuery', WPSC_URL.'/js/jquery.js', false, '1.2.3');
// 	wp_enqueue_script('instinct_thickbox',WPSC_URL.'/js/thickbox.js', 'jQuery', 'Instinct_e-commerce');
	wp_enqueue_script('ngg-thickbox',WPSC_URL.'/js/thickbox.js', 'jQuery', 'Instinct_e-commerce');
} else {
	wp_enqueue_script('thickbox');
	if(function_exists('wp_enqueue_style')) {  // DO NOT ALTER THIS!! This function is not present on older versions of wordpress
		wp_enqueue_style( 'thickbox' );
	}
	wp_enqueue_script('jQuery-ui',WPSC_URL.'/js/jquery-ui.js?ver=1.6', array('jquery'), '1.6');
	wp_enqueue_script('jEditable',WPSC_URL.'/js/jquery.jeditable.pack.js', array('jquery'), '2.7.4');
}
if(strpos($_SERVER['REQUEST_URI'], WPSC_DIR_NAME.'') !== false) {
// 	wp_enqueue_script('interface',WPSC_URL.'/js/interface.js', 'Interface');
	
		if($_GET['page'] == WPSC_DIR_NAME.'/display-items.php') {
			wp_enqueue_script( 'postbox', '/wp-admin/js/postbox.js', array('jquery'));
      wp_enqueue_script('new_swfupload', WPSC_URL.'/js/swfupload.js');
      wp_enqueue_script('new_swfupload.swfobject', WPSC_URL.'/js/swfupload/swfupload.swfobject.js');
      //wp_enqueue_script('swfupload-degrade');
      //wp_enqueue_script('swfupload-queue');
      //wp_enqueue_script('swfupload-handlers');
		}
}




switch(get_option('cart_location')) {
  case 1:
  add_action('wp_list_pages','nzshpcrt_shopping_basket');
  break;
  
  case 2:
  add_action('the_content', 'nzshpcrt_shopping_basket' , 14);
  break;
  
  case 4:
  break;
  
  case 5:
  //exit("<pre>".print_r($_SERVER,true)."</pre>");
  if(function_exists('drag_and_drop_cart')) {
    $shop_pages_only = 1;
		add_action('init', 'drag_and_drop_cart_ajax');  
		if (get_option('dropshop_display')=='product'){
		  $url_prefix_array = explode("://", get_option('product_list_url'));
		  $url_prefix = $url_prefix_array[0]."://";
			if(stristr(($url_prefix.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), get_option('product_list_url'))){
			  
				wp_enqueue_script('interface',WPSC_URL.'/js/interface.js', 'Interface');
				add_action('wp_head', 'drag_and_drop_js');  
				add_action('wp_footer', 'drag_and_drop_cart');  
			}
		} else {		  
			wp_enqueue_script('interface',WPSC_URL.'/js/interface.js', 'Interface');
			add_action('wp_head', 'drag_and_drop_js');  
			add_action('wp_footer', 'drag_and_drop_cart');  
		}
	}
  break;
  
  case 3:
  //add_action('the_content', 'nzshpcrt_shopping_basket');
  //<?php nzshpcrt_shopping_basket(); ?/>   
  break;
  
  default:
  add_action('the_content', 'nzshpcrt_shopping_basket', 14);
  break;
}





function thickbox_variation() {
	global $wpdb, $siteurl;
	$variations_processor = new nzshpcrt_variations;
	echo "<head>";
	echo "<link rel='stylesheet' href='{$siteurl}/wp-admin/wp-admin.css?ver=2.6.3' type='text/css' media='all' />
	<link rel='stylesheet' href='{$siteurl}/wp-admin/css/colors-fresh.css?ver=2.6.3' type='text/css' media='all' />
	<link href='{$siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/admin.css' rel='stylesheet' type='text/css'/>
	<link rel='stylesheet' href='{$siteurl}/wp-admin/css/global.css?ver=2.6.3' type='text/css' media='all' />";
	echo "<script type='text/javascript' src='{$siteurl}/wp-includes/js/jquery/jquery.js?ver=1.2.6'></script>";
	echo "<script type='text/javascript' src='{$siteurl}/wp-includes/js/thickbox/thickbox.js?ver=3.1-20080430'></script>
	<script language='JavaScript' type='text/javascript' src='{$siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/js/jquery.tooltip.js'></script>
<script type='text/javascript' src='{$siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/js/jquery-ui.js?ver=1.6'></script>
<script type='text/javascript' src='{$siteurl}/wp-content/plugins/".WPSC_DIR_NAME."/js/jquery.jeditable.pack.js?ver=2.7.4'></script>
<script type='text/javascript' src='{$siteurl}/wp-includes/js/swfupload/swfupload.js?ver=2.0.2-20080430'></script>
";
	echo "<script language='JavaScript' type='text/javascript'>
			var base_url = '".$siteurl."';
			var WPSC_URL = '".WPSC_URL."';
			var WPSC_IMAGE_URL = '".WPSC_IMAGE_URL."';";
		echo "var TXT_WPSC_DELETE = '".TXT_WPSC_DELETE."';\n\r";
		echo "var TXT_WPSC_TEXT = '".TXT_WPSC_TEXT."';\n\r";
		echo "var TXT_WPSC_EMAIL = '".TXT_WPSC_EMAIL."';\n\r";
		echo "var TXT_WPSC_COUNTRY = '".TXT_WPSC_COUNTRY."';\n\r";
    echo "var TXT_WPSC_TEXTAREA = '".TXT_WPSC_TEXTAREA."';\n\r";
    echo "var TXT_WPSC_HEADING = '".TXT_WPSC_HEADING."';\n\r";
    echo "var TXT_WPSC_COUPON = '".TXT_WPSC_COUPON."';\n\r";
    echo "var HTML_FORM_FIELD_TYPES =\"<option value='text' >".TXT_WPSC_TEXT."</option>";
    echo "<option value='email' >".TXT_WPSC_EMAIL."</option>";
    echo "<option value='address' >".TXT_WPSC_ADDRESS."</option>";
    echo "<option value='city' >".TXT_WPSC_CITY."</option>";
    echo "<option value='country'>".TXT_WPSC_COUNTRY."</option>";
    echo "<option value='delivery_address' >".TXT_WPSC_DELIVERY_ADDRESS."</option>";
    echo "<option value='delivery_city' >".TXT_WPSC_DELIVERY_CITY."</option>";
    echo "<option value='delivery_country'>".TXT_WPSC_DELIVERY_COUNTRY."</option>";
    echo "<option value='textarea' >".TXT_WPSC_TEXTAREA."</option>";    
    echo "<option value='heading' >".TXT_WPSC_HEADING."</option>";
    echo "<option value='coupon' >".TXT_WPSC_COUPON."</option>\";\n\r";
		
	echo	"</script>";
		
	echo "<script language='JavaScript' type='text/javascript' src='".WPSC_URL."/admin.js'></script></head>";
	if($_POST){
				if($_POST['submit_action'] == "add") {
    //exit("<pre>".print_r($_POST,true)."</pre>");
    $variation_sql = "INSERT INTO `".WPSC_TABLE_PRODUCT_VARIATIONS."` (`name`, `variation_association`) VALUES ( '".$_POST['name']."', 0);";
    if($wpdb->query($variation_sql)) {
      $variation_id = $wpdb->get_results("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` LIMIT 1",ARRAY_A);
      $variation_id = $variation_id[0]['id'];
      $variation_values = $_POST['variation_values'];
      $variation_value_sql ="INSERT INTO `".WPSC_TABLE_VARIATION_VALUES."` ( `name` , `variation_id` ) VALUES ";
      $num = 0;
      foreach($variation_values as $variation_value) {
        switch($num) {
          case 0:
          $comma = '';
          break;
          
          default:
          $comma = ', ';
          break;
				}
        $variation_value_sql .= "$comma( '".$wpdb->escape(trim($variation_value))."', '".$variation_id."')";
        $num++;
			}
      $variation_value_sql .= ";";
      $wpdb->query($variation_value_sql);
      echo "<head>";
		echo "
		<script language='JavaScript' type='text/javascript' src='".WPSC_URL."/admin.js'></script>
		<script language='JavaScript' type='text/javascript'>
				parent.jQuery('#add_product_variations').html(\"".nl2br($variations_processor->list_variations())."\");
				parent.tb_remove();
		</script>";
	
		echo "</head>";

      echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASBEENADDED."</p></div>";
		} else {
			echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASNOTBEENADDED."</p></div>";
		}
	}

	}
		echo "  <table id='productpage'>\n\r";
		echo "    <tr>";
		/*
echo "  <div class='categorisation_title'>\n\r";
		echo "		<strong class='form_group'>".TXT_WPSC_VARIATION_LIST."</strong>\n\r";
		echo "	</div>\n\r";
		echo "      <table id='itemlist'>\n\r";
		echo "        <tr class='firstrow'>\n\r";
	
		echo "          <td>\n\r";
		echo TXT_WPSC_NAME;
		echo "          </td>\n\r";
	
		echo "          <td>\n\r";
		echo TXT_WPSC_EDIT;
		echo "          </td>\n\r";
		
		echo "        </tr>\n\r";
		$variation_sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` ORDER BY `id`";
		$variation_list = $wpdb->get_results($variation_sql,ARRAY_A);
		if($variation_list != null) {
		  foreach($variation_list as $variation) {
		    display_variation_row($variation);
			}
		}
		  
		echo "      </table>\n\r";
*/
		echo "      <td class='secondcol'>\n\r";
		echo "        <div id='productform'>";
		echo "  <div class='categorisation_title'>\n\r";
		echo "		<strong class='form_group'>".TXT_WPSC_EDITVARIATION."</strong>\n\r";
		echo "	</div>\n\r";

		echo "<form method='POST'  enctype='multipart/form-data' name='editproduct$num'>";
		echo "        <div id='formcontent'>\n\r";
		echo "        </div>\n\r";
		echo "</form>";
		echo "        </div>";
		?>
		<div id='additem'>
  <div class="categorisation_title">
		<strong class="form_group"><?php echo TXT_WPSC_ADDVARIATION;?></strong>
	</div>
  <form method='POST' action='admin.php?thickbox_variations=true&amp;width=550'>
  <table class='category_forms'>
    <tr>
      <td>
        <?php echo TXT_WPSC_NAME;?>:
      </td>
      <td>
        <input type='text'  class="text" name='name' value=''  />
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_VARIATION_VALUES;?>:
      </td>
      <td>
        <div id='add_variation_values'><span id='variation_value_1'>
        <input type='text' class="text" name='variation_values[]' value='' />
        <a class='image_link' href='#' onclick='remove_variation_value_field("variation_value_1")'><img src='<?php echo WPSC_URL; ?>/images/trash.gif' alt='<?php echo TXT_WPSC_DELETE; ?>' title='<?php echo TXT_WPSC_DELETE; ?>' /></a><br />
        </span><span id='variation_value_2'>
        <input type='text' class="text" name='variation_values[]' value='' />
        <a class='image_link' href='#' onclick='remove_variation_value_field("variation_value_2")'><img src='<?php echo WPSC_URL; ?>/images/trash.gif' alt='<?php echo TXT_WPSC_DELETE; ?>' title='<?php echo TXT_WPSC_DELETE; ?>' /></a><br />
        </span></div>
       <a href='#' onclick='return add_variation_value("add")'><?php echo TXT_WPSC_ADD;?></a>
      </td>
    </tr>
    <tr>
      <td>
      </td>
      <td>
        <input type='hidden' name='submit_action' value='add' />
        <input class='button'  type='submit' name='submit' value='<?php echo TXT_WPSC_ADD;?>' />
      </td>
    </tr>
  </table>
  </form>
</div>
<?php
echo "      </td></tr>\n\r";
echo "     </table>\n\r";
		
		exit();
	}
	
	if ($_GET['thickbox_variations']) {
		add_action('admin_init','thickbox_variation');
	}















add_filter('favorite_actions', 'wpsc_fav_action');
function wpsc_fav_action($actions) {
    // remove the "Add new page" link
    // unset($actions['page-new.php']);
  	// add quick link to our favorite plugin
    $actions['admin.php?page='.WPSC_DIR_NAME.'/display-items.php'] = array('New Product', 'manage_options');
    return $actions;
}

//duplicating a product
function wpsc_duplicate() {
	global $wpdb;
	if (is_numeric($_GET['duplicate'])) {
		$dup_id = $_GET['duplicate'];
		$sql = " INSERT INTO ".WPSC_TABLE_PRODUCT_LIST."( `name` , `description` , `additional_description` , `price` , `weight` , `weight_unit` , `pnp` , `international_pnp` , `file` , `image` , `category` , `brand` , `quantity_limited` , `quantity` , `special` , `special_price` , `display_frontpage` , `notax` , `active` , `donation` , `no_shipping` , `thumbnail_image` , `thumbnail_state` ) SELECT `name` , `description` , `additional_description` , `price` , `weight` , `weight_unit` , `pnp` , `international_pnp` , `file` , `image` , `category` , `brand` , `quantity_limited` , `quantity` , `special` , `special_price` , `display_frontpage` , `notax` , `active` , `donation` , `no_shipping` , `thumbnail_image` , `thumbnail_state` FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = '".$dup_id."' ";
		$wpdb->query($sql);
		$new_id= $wpdb->get_var("SELECT LAST_INSERT_ID() AS `id` FROM `".WPSC_TABLE_PRODUCT_LIST."` LIMIT 1");
		
		//Inserting duplicated category record.
		$category_assoc = $wpdb->get_col("SELECT category_id FROM ".WPSC_TABLE_ITEM_CATEGORY_ASSOC." WHERE product_id = '".$dup_id."'");
		$new_product_category = "";
		if (count($category_assoc) > 0) {
			foreach($category_assoc as $key => $category) {
				$new_product_category .= "('".$new_id."','".$category."')";
				
				if (count($category_assoc) != $key+1) {
					$new_product_category .= ",";
				}
			}
			$sql = "INSERT INTO ".WPSC_TABLE_ITEM_CATEGORY_ASSOC." (product_id, category_id) VALUES ".$new_product_category;
			$wpdb->query($sql);
		}
	
		//Inserting duplicated meta info
		$meta_values = $wpdb->get_results("SELECT `meta_key`, `meta_value`, `custom` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE product_id='".$dup_id."'", ARRAY_A);
		$new_meta_value = '';
		if (count($meta_values)>0){
			foreach($meta_values as $key => $meta) {
				$new_meta_value .= "('".$new_id."','".$meta['meta_key']."','".$meta['meta_value']."','".$meta['custom']."')";
			
				if (count($meta_values) != $key+1) {
					$new_meta_value .= ",";
				}
			}
			$sql = "INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` (`product_id`, `meta_key`, `meta_value`, `custom`) VALUES ".$new_meta_value;
			$wpdb->query($sql);
		}
		
		
		
		//Inserting duplicated image info
		$image_values = $wpdb->get_results("SELECT `image`, `width`, `height`, `image_order`, `meta` FROM ".WPSC_TABLE_PRODUCT_IMAGES." WHERE product_id='".$dup_id."'", ARRAY_A);
		$new_image_value = '';
		if (count($image_values)>0){
			foreach($image_values as $key => $image) {
				$new_image_value .= "('".$new_id."','".$image['image']."','".$image['width']."','".$image['height']."','".$image['image_order']."','".$image['meta']."')";
			
				if (count($meta_values) != $key+1) {
					$new_image_value .= ",";
				}
			}
			$sql = "INSERT INTO ".WPSC_TABLE_PRODUCT_IMAGES." (`product_id`, `image`, `width`, `height`, `image_order`, `meta`) VALUES ".$new_image_value;
			$wpdb->query($sql);
		}
	}
	wp_redirect('?page='.WPSC_DIR_NAME.'/display-items.php');
}

if (isset($_GET['duplicate'])) {
	add_action('admin_init', 'wpsc_duplicate');
}
//add_action('init', 'save_hidden_box');
?>