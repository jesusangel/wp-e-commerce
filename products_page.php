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
<div class="wrap">
<?php
/*
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
?>
<div class="wrap">
<?php
  if(function_exists('gold_shpcrt_search_form'))
    {
    echo gold_shpcrt_search_form();
    }
?>
<?php
  
  echo "<table>";
  
  switch(get_option('show_categorybrands'))
    {
    case 1:
    echo "<tr><td class='prodgroupmidline'><a href='' onclick='return prodgroupswitch(\"categories\");'>".TXT_WPSC_CATEGORIES."</a></td><td class='prodgroupright'><a href='' onclick='return prodgroupswitch(\"brands\");'>".TXT_WPSC_BRANDS."</a></td></tr>";
    break;
    
    case 2:
    echo "<tr><td colspan='2'><a href='' onclick='return prodgroupswitch(\"categories\");'>".TXT_WPSC_CATEGORIES."</a></td></tr>";
    break;
    
    case 3:
    echo "<tr><td colspan='2'><a href='' onclick='return prodgroupswitch(\"brands\");'>".TXT_WPSC_BRANDS."</a></td></tr>";
    break;
    }
  echo "<tr><td colspan='2'>";
  
  
  if((get_option('show_categorybrands') == 1 ) || (get_option('show_categorybrands') == 2))
    {
    //exit("done");
    echo "<div id='categorydisplay'>";
    $categories = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '0' ORDER BY `order` ASC",ARRAY_A);
    if($categories != null)
      {
      foreach($categories as $option)
        {
        $options .= "<a class='categorylink' href='".get_option('product_list_url').$seperator."category=".$option['id']."'>".stripslashes($option['name'])."</a><br />";
        $subcategory_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '".$option['id']."' ORDER BY `id`";
        $subcategories = $wpdb->get_results($subcategory_sql,ARRAY_A);
        if($subcategories != null)
          {
          foreach($subcategories as $subcategory)
            {
            $options .= "<a class='categorylink' href='".get_option('product_list_url').$seperator."category=".$subcategory['id']."'>-".stripslashes($subcategory['name'])."</a><br />";
            }
          }
        }
      }
    echo $options;
    }
    
  
  if((get_option('show_categorybrands') == 1 ) || (get_option('show_categorybrands') == 3))
    {
    echo "<div id='branddisplay'>";
    $options ='';
    $brands = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `active`='1' ORDER BY `order` ASC",ARRAY_A);
    if($brands != null)
      {
      foreach($brands as $option)
        {
        $options .= "<a class='categorylink' href='".get_option('product_list_url').$seperator."brand=".$option['id']."'>".stripslashes($option['name'])."</a><br />";
        }
      }
    echo $options;
    echo "</div>";
    }
    
    
  echo "</td></tr>";
  echo "</table>";
  }*/
  
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

    if(function_exists('gold_shpcrt_search_form'))
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
  if((is_numeric($_GET['category']) || is_numeric(get_option('default_category'))) && ((get_option('show_categorybrands') == 1) || (get_option('show_categorybrands') == 2)))
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
       echo "<div>";
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
             echo "<br /><strong class='cattitles'>".stripslashes($category_data[0]['name'])."</strong>";
             }
         echo "<span id='loadingindicator'><img id='loadingimage' src='$siteurl/wp-content/plugins/wp-shopping-cart/images/indicator.gif' alt='Loading' title='Loading' /> ".TXT_WPSC_UDPATING."...</span></strong><br />";
        
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