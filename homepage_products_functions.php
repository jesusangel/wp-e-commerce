<?php
function nszhpcrt_homepage_products($content = '')
  {
  global $wpdb;
  $siteurl = get_option('siteurl');
  if(get_option('permalink_structure') != '')
    {
    $seperator ="?";
    }
    else
      {
      $seperator ="&amp;";
      }
  $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `display_frontpage` IN('1') AND `active` IN('1')";
  $product_list = $wpdb->get_results($sql,ARRAY_A);
    
  $output = "<div id='homepage_products'>\n\r";
  foreach((array)$product_list as $product)
    {
    $output .= "<div class='frontpage_product'>\n\r";
    $output .= "<a href='".get_option('product_list_url').$seperator."product_id=".$product['id']."'>";
    if($product['image'] != '')
      {
      $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/".$product['image']."' title='".$product['name']."' alt='".$product['name']."' />\n\r";
      $output .= "<p>\n\r";
      $output .= stripslashes($product['name']);
      $output .= "<span class='front_page_price'>\n\r";
      if($product['special']==1)
        {
        $output .= "<span class='oldprice'>".nzshpcrt_currency_display($product['price'], $product['notax'])."</span><br />\n\r";
        $output .= nzshpcrt_currency_display(($product['price'] - $product['special_price']), $product['notax'],false,$product['id']);
        }
        else
          {
          $output .= "".nzshpcrt_currency_display($product['price'], $product['notax']);
          }
      $output .= "</span>\n\r";
      $output .= "</p>\n\r";
      }
    $output .= "</a>";
    $output .= "</div>\n\r";
    }
  $output .= "</div>\n\r";
  $output .= "<br style='clear: left;'>\n\r";
  
  return preg_replace("/\[homepage_products\]/", $output, $content);
  }
  
  

function nszhpcrt_category_tag($content = '')
  {
  global $wpdb;
  if(preg_match("/\[wpsc_category=(\d)+\]/", $content, $matches))
    {  
    $category_id = $matches[1];
    //exit($category_id);
    $siteurl = get_option('siteurl');
    if(get_option('permalink_structure') != '')
      {
      $seperator ="?";
      }
      else
        {
        $seperator ="&amp;";
        }
    
  foreach((array)$activated_widgets as $widget_container)
    {
    if(is_array($widget_container) && array_search(TXT_WPSC_DONATIONS, $widget_container))
      {
      $no_donations_sql = "AND `".$wpdb->prefix."product_list`.`donation` != '1'";
      break;
      }
    }  
    
    $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.*, `".$wpdb->prefix."item_category_associations`.`category_id`,`".$wpdb->prefix."product_order`.`order`, IF(ISNULL(`".$wpdb->prefix."product_order`.`order`), 0, 1) AS `order_state` FROM `".$wpdb->prefix."product_list` LEFT JOIN `".$wpdb->prefix."item_category_associations` ON `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` LEFT JOIN `".$wpdb->prefix."product_order` ON ( ( `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."product_order`.`product_id` ) AND ( `".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_order`.`category_id` ) ) WHERE `".$wpdb->prefix."product_list`.`active` = '1' AND `".$wpdb->prefix."item_category_associations`.`category_id` IN ('".$category_id."') $no_donations_sql ORDER BY `order_state` DESC,`".$wpdb->prefix."product_order`.`order` ASC";
    
    $product_list = $wpdb->get_results($sql,ARRAY_A);
      
    $output = "<div id='homepage_products'>\n\r";
    foreach((array)$product_list as $product)
      {
      $output .= "<div class='category_view_product'>\n\r";
      
      /* product image is here */      
      $output .= "<a href='".get_option('product_list_url').$seperator."product_id=".$product['id']."'>";
      if($product['image'] != '')
        {
        $output .= "<img class='product_image' src='$siteurl/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/".$product['image']."' title='".$product['name']."' alt='".$product['name']."' />\n\r";
        }
      $output .= "</a>";
      
      $output .= "<div class='product_details'>";
      $output .= "<a href='".get_option('product_list_url').$seperator."product_id=".$product['id']."'>";
      $output .= stripslashes($product['name']);
      $output .= "</a>";
           
      
      /*
      adding to cart stuff
      */
      $output .= "<form id='product_".$product['id']."' name='product_".$product['id']."' method='post' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
      $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
      $output .= "<input type='hidden' name='item' value='".$product['id']."' />";
      
      $variations_procesor = new nzshpcrt_variations;
          
      $variations_output = $variations_procesor->display_product_variations($product['id'],false, false, true);
      $output .= $variations_output[0];
      if($variations_output[1] !== null)
        {
        $product['price'] = $variations_output[1];
        }
        
      if(($product['special']==1) && ($variations_output[1] === null))
          {
          $output .= "<span class='oldprice'>".nzshpcrt_currency_display($product['price'], $product['notax']) . "</span><br />";
          $output .= nzshpcrt_currency_display(($product['price'] - $product['special_price']), $product['notax'],false,$product['id']) . "<br />";
          }
          else
            {
            $output .= "<span id='product_price_".$product['id']."'>" . nzshpcrt_currency_display($product['price'], $product['notax']) . "</span><br />";
            }
      
      
      $output .= "<input type='submit' id='product_".$product['id']."_submit_button' class='wpsc_buy_button' name='Buy' value='".TXT_WPSC_ADDTOCART."'  />";
      $output .= "</form>";
      $output .= "</div>";
      
      $output .= "</div>\n\r";
      }
    $output .= "</div>\n\r";
    $output .= "<br style='clear: left;'>\n\r";
    return preg_replace("/\[wpsc_category=(\d)+\]/", $output, $content);
    }
    else
    {
    return $content;
    }
  }
?>