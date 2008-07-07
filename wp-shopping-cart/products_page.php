<?php
global $wpdb,$wp_query;
$siteurl = get_option('siteurl');
//$_SESSION['selected_country'] = '';
//echo("<pre>".print_r($_SERVER,true)."</pre>");
if(is_numeric($_GET['brand']) || (is_numeric(get_option('default_brand')) && (get_option('show_categorybrands') == 3))) {
  if(is_numeric($_GET['brand'])) {
    $brandid = $_GET['brand'];
	} else {
		$brandid = get_option('default_brand');
	}
     
  $group_sql = "AND `brand`='".$brandid."'";
  
  $cat_sql = "SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `id`='".$brandid."' LIMIT 1";
  $group_type = TXT_WPSC_BRANDNOCAP;
} else if(is_numeric($_GET['category']) || ($wp_query->query_vars['prodcat_name'] != null) || (is_numeric(get_option('default_category')) && (get_option('show_categorybrands') != 3))) {
  if(is_numeric($wp_query->query_vars['product_category'])) {
    $category_id = $wp_query->query_vars['product_category'];
	} else if(is_numeric($_GET['category'])) {
    $category_id = $_GET['category'];
	} else { 
    $category_id = get_option('default_category');
	}
  $cat_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `id`='".$category_id."' LIMIT 1";
  $group_type = TXT_WPSC_CATEGORYNOCAP;
} else {
  $group_type = TXT_WPSC_BRANDNOCAP;
}

$category_data = $wpdb->get_results($cat_sql,ARRAY_A);

if($_GET['cart']== 'empty') {
  $_SESSION['nzshpcrt_cart'] = '';
  $_SESSION['nzshpcrt_cart'] = Array();
}
  
?>
<div id='products_page_container' class="wrap wpsc_container">
<?php
if(function_exists('fancy_notifications')) {
  echo fancy_notifications();
  }



  $num = 0; 
  
  //else if(is_numeric($_GET['category']) || (is_numeric(get_option('default_category')) && (get_option('show_categorybrands') != 3)))
  if((is_numeric($category_id) || is_numeric(get_option('default_category'))) && ((get_option('show_categorybrands') == 1) || (get_option('show_categorybrands') == 2)) || (is_numeric($_GET['product_id']))) {
			$display_items = true;
    } else if((is_numeric($_GET['brand']) || is_numeric(get_option('default_brand'))) && ((get_option('show_categorybrands') == 3) || (get_option('show_categorybrands') == 1))) {
      $display_items = true;
    } else if($_GET['product_search'] != '') {
      $display_items = true;
		}
      
  if($display_items == true) {
    if(get_option('permalink_structure') != '') {
      $seperator ="?";
		} else {
			$seperator ="&amp;";
		}
        
		if($wp_query->query_vars['product_name'] != null){
			$product_id = $wpdb->get_var("SELECT `product_id` FROM `".$wpdb->prefix."wpsc_productmeta` WHERE `meta_key` IN ( 'url_name' ) AND `meta_value` IN ( '".$wp_query->query_vars['product_name']."' ) LIMIT 1");
		} else {
			$product_id = $_GET['product_id'];
		}


     if(is_numeric($product_id)) { 
       echo single_product_display($product_id);
       } else { 
				 if(function_exists('gold_shpcrt_search_form') && get_option('show_search') == 1) {
					 echo gold_shpcrt_search_form();
				 }

         //echo nzshpcrt_display_categories_groups();
				if($_GET['product_search'] != null) {
					echo "<br /><strong class='cattitles'>".TXT_WPSC_SEARCH_FOR." : ".stripslashes($_GET['product_search'])."</strong>";
				} else {
					$category_image = '';
					if((get_option('show_category_thumbnails') == 1) && ($category_data[0]['image'] != null)) {
						$category_image = "<img src='".WPSC_CATEGORY_URL.$category_data[0]['image']."' class='category_image' alt='' title='' />";
					}
					echo "".$category_image."<strong class='cattitles'>".stripslashes($category_data[0]['name'])."</strong>";
					if((get_option('wpsc_category_description') == 'true') && ($category_data[0]['description'] != '')) {
						//echo "<p>".stripslashes($category_data[0]['description'])."</p>";
						echo "<p>".nl2br($category_data[0]['description'])."</p>";
					}
				}
				if(get_option('fancy_notifications') != 1) {
					echo "<span id='loadingindicator'><img id='loadingimage' src='".WPSC_URL."/images/indicator.gif' alt='Loading' title='Loading' /> ".TXT_WPSC_UDPATING."...</span><br />";
				}
				if (isset($GET['item_per_page'])){
					$item_per_page = $_GET['item_per_page'];
					$_SESSION['item_per_page'] = $item_per_page;
					update_option('use_pagination',1);
				} 
				
				
				if(((get_option('show_advanced_search') != 1) || (get_option('show_search') != 1)) && (get_option('product_view') == 'grid') ) {
				  $_SESSION['customer_view'] = 'grid';
				} else if(((get_option('show_advanced_search') != 1) || (get_option('show_search') != 1)) && (get_option('product_view') == 'default')) {
				  $_SESSION['customer_view'] = 'default';
				}
				
				if(function_exists('product_display_list') && (get_option('product_view') == 'list')) {
					echo product_display_list($product_list, $group_type, $group_sql, $search_sql);
				} else if(function_exists('product_display_grid') && (($_SESSION['customer_view'] == 'grid') || ((get_option('product_view') == 'grid') && ($_SESSION['customer_view'] != 'default')))) {		
					//echo get_option('show_search');
					
					echo product_display_grid($product_list, $group_type, $group_sql, $search_sql);
				} else {
					echo product_display_default($product_list, $group_type, $group_sql, $search_sql);
				}
			}
		} else {
      switch(get_option('show_categorybrands')) {
        case 1:
        $group_type = TXT_WPSC_CATEGORYORBRAND;
        break;

        case 2:
        $group_type = TXT_WPSC_CATEGORY;
        break;

        case 3:
        $group_type = TXT_WPSC_BRAND;
        break;
			}

      echo "<a name='products' ></a><strong class='prodtitles'>".TXT_WPSC_PLEASECHOOSEA." ".ucfirst($group_type)."</strong><br />";
      echo nzshpcrt_display_categories_groups();
		}
  ?>
</div>