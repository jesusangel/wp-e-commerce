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
        $output .= nzshpcrt_currency_display($product['price'], $product['notax'],false,$product['id']);
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
?>