<?php
global $wpdb;
$siteurl = get_option('siteurl');
//$_SESSION['selected_country'] = '';
if(is_numeric($_GET['brand']) || (is_numeric(get_option('default_brand')) && (get_option('show_categorybrands') == 3)))
  {
  if(is_numeric($_GET['brand']))
    {
    $brandid = $_GET['brand'];
    }
    else
      {
      $brandid = get_option('default_brand');
      }
     
  $group_sql = "AND `brand`='".$brandid."'";
  
  $cat_sql = "SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `id`='".$brandid."' LIMIT 1";
  $group_type = TXT_WPSC_BRANDNOCAP;
  }
  else if(is_numeric($_GET['category']) || (is_numeric(get_option('default_category')) && (get_option('show_categorybrands') != 3)))
    {
    if(is_numeric($_GET['category']))
      {
      $catid = $_GET['category'];
      }
      else
        {
        $catid = get_option('default_category');
        }
    //$group_sql = "AND `".$wpdb->prefix."item_category_associations`.`category_id`='".$catid."'";
    $cat_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `id`='".$catid."' LIMIT 1";
    $group_type = TXT_WPSC_CATEGORYNOCAP;
    }
    else
      {
      $group_type = TXT_WPSC_BRANDNOCAP;
      }

$category_data = $GLOBALS['wpdb']->get_results($cat_sql,ARRAY_A);


if($_GET['cart']== 'empty')
  {
  $_SESSION['nzshpcrt_cart'] = '';
  $_SESSION['nzshpcrt_cart'] = Array();
  }
  
?>
<div id='products_page_container' class="wrap wpsc_container">
<?php  
if(function_exists('fancy_notifications'))
  {
  echo fancy_notifications();
  }
function nzshpcrt_display_categories_groups()
  {
    global $wpdb;

    if(get_option('permalink_structure') != '')
    {
      $seperator ="?";
    }
    else
    {
      $seperator ="&amp;";
    }

    if(function_exists('gold_shpcrt_search_form') && get_option('show_search') == 1)
    {
      echo gold_shpcrt_search_form();
    }

    // pe.{
    //include("show_cats_brands.php");
    if (get_option('cat_brand_loc') == 0)
    {
      show_cats_brands();
    }
    // }.pe
  }


  $num = 0;
  //else if(is_numeric($_GET['category']) || (is_numeric(get_option('default_category')) && (get_option('show_categorybrands') != 3)))
  if((is_numeric($_GET['category']) || is_numeric(get_option('default_category'))) && ((get_option('show_categorybrands') == 1) || (get_option('show_categorybrands') == 2)) || (is_numeric($_GET['product_id'])))
    {
    $display_items = true;
    }
    else if((is_numeric($_GET['brand']) || is_numeric(get_option('default_brand'))) && ((get_option('show_categorybrands') == 3) || (get_option('show_categorybrands') == 1)))
      {
      $display_items = true;
      }
      
  if($display_items == true)
    {
    if(get_option('permalink_structure') != '')
      {
      $seperator ="?";
      }
      else
        {
        $seperator ="&amp;";
        }
     
     if(is_numeric($_GET['product_id']))
       {
       echo single_product_display($_GET['product_id']);
       }
       else
         {
         echo nzshpcrt_display_categories_groups();
         if($_GET['product_search'] != null)
           {
           echo "<br /><strong class='cattitles'>".TXT_WPSC_SEARCH_FOR." : ".stripslashes($_GET['product_search'])."</strong>";
           }
           else
             {
             $category_image = '';
             if((get_option('show_category_thumbnails') == 1) && ($category_data[0]['image'] != null))
               {
               $category_image = "<img src='".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/category_images/".$category_data[0]['image']."' class='category_image' alt='' title='' />";
               }
//              echo "".$category_image."<strong class='cattitles'>".stripslashes($category_data[0]['name'])."</strong>";
             }
         if(get_option('fancy_notifications') != 1)
           {
           echo "<span id='loadingindicator'><img id='loadingimage' src='$siteurl/wp-content/plugins/wp-shopping-cart/images/indicator.gif' alt='Loading' title='Loading' /> ".TXT_WPSC_UDPATING."...</span><br />";
           }
           else
           {
           echo "<br />";
           }
        
         if(function_exists('product_display_list') && (get_option('product_view') == 'list'))
           {
           echo product_display_list($product_list, $group_type, $group_sql, $search_sql);
           }
           else if(function_exists('product_display_grid') && (get_option('product_view') == 'grid'))
             {
             echo product_display_grid($product_list, $group_type, $group_sql, $search_sql);
             }
             else
               {
               echo product_display_default($product_list, $group_type, $group_sql, $search_sql);
               }
         }
     }
    else
      {
      switch(get_option('show_categorybrands'))
        {
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