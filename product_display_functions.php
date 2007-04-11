<?php
function product_display_default($product_list, $group_type, $group_sql = '', $search_sql = '')
  {
  global $wpdb;
  $siteurl = get_option('siteurl');
  
  if(function_exists('gold_shpcrt_search_sql') && ($_GET['product_search'] != ''))
    {
    $search_sql = gold_shpcrt_search_sql();
    if($search_sql != '')
      {
      // this cannot currently list products that are associated with no categories
      $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.* FROM `".$wpdb->prefix."product_list`,`".$wpdb->prefix."item_category_associations` WHERE `".$wpdb->prefix."product_list`.`active`='1' AND `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` $search_sql ORDER BY `".$wpdb->prefix."product_list`.`special` DESC";
      //exit($sql);
      }
    }
    else
      {
      if(is_numeric($_GET['category']) || (is_numeric(get_option('default_category')) && (get_option('show_categorybrands') != 3) && !is_numeric($_GET['brand'])))
        {
        if(is_numeric($_GET['category']))
          {
          $catid = $_GET['category'];
          }
          else
            {
            $catid = get_option('default_category');
            }
        /*
         * The reason this is so complicated is because of the product ordering, it is done by category/product association
         * If you can see a way of simplifying it and speeding it up, then go for it (go for gold, go for gold, go for gold, or something, kekeke)
         */
        $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.*, `".$wpdb->prefix."item_category_associations`.`category_id`,`".$wpdb->prefix."product_order`.`order`, IF(ISNULL(`".$wpdb->prefix."product_order`.`order`), 0, 1) AS `order_state` FROM `".$wpdb->prefix."product_list` LEFT JOIN `".$wpdb->prefix."item_category_associations` ON `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` LEFT JOIN `".$wpdb->prefix."product_order` ON ( ( `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."product_order`.`product_id` ) AND ( `".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_order`.`category_id` ) ) WHERE `".$wpdb->prefix."product_list`.`active` = '1' AND `".$wpdb->prefix."item_category_associations`.`category_id` IN ('".$catid."') ORDER BY `order_state` DESC,`".$wpdb->prefix."product_order`.`order` ASC";
        }
        else
          {
          $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.* FROM `".$wpdb->prefix."product_list`,`".$wpdb->prefix."item_category_associations` WHERE `".$wpdb->prefix."product_list`.`active`='1' AND `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` $group_sql ORDER BY `".$wpdb->prefix."product_list`.`special` DESC"; 
          }
      }         
        
  /*
  
*/      
        
        
  $product_list = $GLOBALS['wpdb']->get_results($sql,ARRAY_A);
  
  if($product_list != null)
    {
    $output .= "<table class='productdisplay'>";
    foreach($product_list as $product)
      {
      $num++;
      $output .= "    <tr>";
      if($category_data[0]['fee'] == 0)
        {
        $output .= "      <td class='imagecol' style='vertical-align: top;'>";
        if(get_option('show_thumbnails') == 1)
          {
          if($product['image'] !=null)
            {
            $image_size = @getimagesize($imagedir.$product['image']);
            $image_link = "$siteurl/wp-content/plugins/wp-shopping-cart/product_images/".$product['image']."";

            $output .= "<a id='preview_link' href='".$image_link."' class='thickbox'  rel='".str_replace(" ", "_",$product['name'])."'>";
            
            if($product['thumbnail_image'] != null)
              {
              $image_file_name = $product['thumbnail_image'];
              }
              else
              {
              $image_file_name = $product['image'];
              }
                  
            $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/".$image_file_name."' title='".$product['name']."' alt='".$product['name']."' id='product_image_".$product['id']."' class='product_image'/>";
            $output .= "</a>";
            if(function_exists("gold_shpcrt_display_extra_images"))
              {
              $output .= gold_shpcrt_display_extra_images($product['id'],$product['name']);
              }
            }
            else
              {
              if(get_option('product_image_width') != '')
                {
                $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/no-image-uploaded.gif' title='".$product['name']."' alt='".$product['name']."' width='".get_option('product_image_width')."' height='".get_option('product_image_height')."' id='product_image_".$product['id']."' class='product_image' />";
                }
                else
                  {
                  $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/no-image-uploaded.gif' title='".$product['name']."' alt='".$product['name']."' id='product_image_".$product['id']."' class='product_image' />";
                  }
              }
          
          if(function_exists('drag_and_drop_items'))
            {
            $output .= drag_and_drop_items("product_image_".$product['id']);
            }          
          }
        $output .= "</td>";
        }
      $output .= "      <td class='textcol'>";
      if($product['special'] == 1)
        {
        $special = "<strong class='special'>".TXT_WPSC_SPECIAL." - </strong>";
        }
        else
          {
          $special = "";
          }
      $output .= "<form id='product_".$product['id']."' name='product_".$product['id']."' method='POST' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
      $output .= "<input type='hidden' name='prodid' value='".$product['id']."'>";
      
      $imagedir = ABSPATH."wp-content/plugins/wp-shopping-cart/product_images/";
      $output .= "<div class='producttext'>$special";
      $output .= "<strong>". stripslashes($product['name']) . "</strong>";
      $output .= "<br />";
      
      if(is_numeric($product['file']) && ($product['file'] > 0))
        {
        $file_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$product['file']."' LIMIT 1",ARRAY_A);
        if(($file_data != null) && (function_exists('listen_button')))
          {
          $output .= listen_button($file_data[0]['idhash']);
          }
        }
      
      
      if($product['description'] != '')
        {
        $output .= nl2br(stripslashes($product['description'])) . "<br />";
        }
        
      if($product['additional_description'] != '')
        {
        
        $output .= "<a href='#' class='additional_description_link' onclick='return show_additional_description(\"additionaldescription".$product['id']."\",\"link_icon".$product['id']."\");'>";
        $output .= "<img id='link_icon".$product['id']."' style='margin-right: 3px;' src='$siteurl/wp-content/plugins/wp-shopping-cart/images/icon_window_expand.gif' title='".$product['name']."' alt='".$product['name']."' />";
        $output .= TXT_WPSC_MOREDETAILS."</a>";
        
        $output .= "<span class='additional_description' id='additionaldescription".$product['id']."'><br />";
        $output .= nl2br(stripslashes($product['additional_description'])) . "";
        $output .= "</span><br />";
        }
      
      $variations_procesor = new nzshpcrt_variations;
          
      $variations_output = $variations_procesor->display_product_variations($product['id'],false, false, true);
      
      $output .= $variations_output[0];
      if($variations_output[1] !== null)
        {
        $product['price'] = $variations_output[1];
        }
      //echo("<pre>".print_r($variations_output[1],true)."</pre>");
      if(($product['special']==1) && ($variations_output[1] === null))
        {
        $output .= "<span class='oldprice'>".TXT_WPSC_PRICE.": " . nzshpcrt_currency_display($product['price'], $product['notax']) . "</span><br />";
        $output .= TXT_WPSC_PRICE.": " . nzshpcrt_currency_display(($product['price'] - $product['special_price']), $product['notax'],false,$product['id']) . "<br />";
        }
        else
          {
          $output .= TXT_WPSC_PRICE.": <span id='product_price_".$product['id']."'>" . nzshpcrt_currency_display($product['price'], $product['notax']) . "</span><br />";
          }
          
      if(get_option('display_pnp') == 1)
        {
        $output .= TXT_WPSC_PNP.": " . nzshpcrt_currency_display($product['pnp'], 1) . "<br />";
        }
      
      $output .= "<input type='hidden' name='item' value='".$product['id']."' />";
      //AND (`quantity_limited` = '1' AND `quantity` > '0' OR `quantity_limited` = '0' )
      if(($product['quantity_limited'] == 1) && ($product['quantity'] < 1) && $variations_output[1] === null)
        {
        $output .= TXT_WPSC_PRODUCTSOLDOUT."";
        }
        else
          {
          $output .= "<input type='submit' name='Buy' value='".TXT_WPSC_ADDTOCART."'  />";
          }
      if(get_option('product_ratings') == 1)
        {
        $output .= "<div class='product_footer'>";
        
        $output .= "<div class='product_average_vote'>";
        $output .= "<strong>".TXT_WPSC_AVGCUSTREVIEW.":</strong>";
        $output .= nzshpcrt_product_rating($product['id']);
        $output .= "</div>";
        
        $output .= "<div class='product_user_vote'>";
        $vote_output = nzshpcrt_product_vote($product['id'],"onmouseover='hide_save_indicator(\"saved_".$product['id']."_text\");'");
        if($vote_output[1] == 'voted')
          {
          $output .= "<strong><span id='rating_".$product['id']."_text'>".TXT_WPSC_YOURRATING.":</span>";
          $output .= "<span class='rating_saved' id='saved_".$product['id']."_text'> ".TXT_WPSC_RATING_SAVED."</span>";
          $output .= "</strong>";
          }
          else if($vote_output[1] == 'voting')
            {
            $output .= "<strong><span id='rating_".$product['id']."_text'>".TXT_WPSC_RATETHISITEM.":</span>";
            $output .= "<span class='rating_saved' id='saved_".$product['id']."_text'> ".TXT_WPSC_RATING_SAVED."</span>";
            $output .= "</strong>";
            }
        $output .= $vote_output[0];
        $output .= "</div>";
        $output .= "</div>";
        }
      
      $output .= "</div>";
      
      $output .= "</form>";
      $output .= "      </td>\n\r";
      $output .= "    </tr>\n\r";
      }
    $output .= "</table>";
    }
    else
      {
      if($_GET['product_search'] != null)
        {
        $output .= "<br /><strong class='cattitles'>".TXT_WPSC_YOUR_SEARCH_FOR." \"".$_GET['product_search']."\" ".TXT_WPSC_RETURNED_NO_RESULTS."</strong>";
        }
        else
          {
          $output .= "<p>".TXT_WPSC_NOITEMSINTHIS." ".$group_type.".</p>";
          }
      }
  return $output;
  }
  

function single_product_display($product_id)
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
  if(is_numeric($product_id))
    {
    $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".$product_id."' LIMIT 1";
    $product_list = $wpdb->get_results($sql,ARRAY_A);
    }
  
  if($product_list != null)
    {
    $output .= "<strong class='cattitles'>".$product_list[0]['name']."</strong>";
    $output .= "<table class='productdisplay'>";
    foreach((array)$product_list as $product)
      {
      $num++;
      $output .= "    <tr class='single_product_display'>";
      if($category_data[0]['fee'] == 0)
        {
        $output .= "      <td class='imagecol' style='vertical-align: top;'>";
        if(get_option('show_thumbnails') == 1)
          {
          if($product['image'] !=null)
              {
              $image_link = "$siteurl/wp-content/plugins/wp-shopping-cart/product_images/".$product['image']."";
              
              if($product['thumbnail_image'] != null)
                {
                $image_file_name = $product['thumbnail_image'];
                }
                else
                {
                $image_file_name = $product['image'];
                }             
              
              $output .= "<a id='preview_link' href='".$image_link."' class='thickbox'  rel='".str_replace(" ", "_",$product['name'])."'>";
              $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/".$image_file_name."' title='".$product['name']."' alt='".$product['name']."' id='product_image_".$product['id']."' class='product_image'/>";
              $output .= "</a>";
              if(function_exists("gold_shpcrt_display_extra_images"))
                {
                $output .= gold_shpcrt_display_extra_images($product['id'],$product['name']);
                }
              }
              else
                {
                if(get_option('product_image_width') != '')
                  {
                  $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/no-image-uploaded.gif' title='".$product['name']."' alt='".$product['name']."' width='".get_option('product_image_width')."' height='".get_option('product_image_height')."' />";
                  }
                  else
                    {
                    $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/no-image-uploaded.gif' title='".$product['name']."' alt='".$product['name']."' />";
                    }
                }
          }
        $output .= "</td>";
        }
      $output .= "      <td class='textcol'>";
      if($product['special'] == 1)
        {
        $special = "<strong class='special'>".TXT_WPSC_SPECIAL." - </strong>";
        }
        else
          {
          $special = "";
          }
      $output .= "<form id='product_".$product['id']."' name='$num' method='POST' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
      $output .= "<input type='hidden' name='prodid' value='".$product['id']."'>";
      
      $imagedir = ABSPATH."wp-content/plugins/wp-shopping-cart/product_images/";
      if(($product['image'] != '') && (file_exists($imagedir.$product['image']) === true) && function_exists("getimagesize"))
        {
        $image_size = @getimagesize($imagedir.$product['image']);
        $image_link = "index.php?productid=".$product['id']."&width=".$image_size[0]."&height=".$image_size[1]."";
        $output .= "<div class='producttext'>$special";
        //$output .= $imagedir.$product['image'];
        if(function_exists("gold_shpcrt_display_extra_images"))
          {
          //$output .= gold_shpcrt_display_extra_images($product['id'],$num);
          }
        //$output .= "<a id='preview_link' href='".$image_link."' rel='lightbox[$num]' class='lightbox_links'>";
        //  $output .= "<strong>". stripslashes($product['name']) . "</strong>";
       // $output .= "</a>";
        }
        else
          {
          $output .= "<div class='producttext'>$special";
          //  $output .= "<strong>". stripslashes($product['name']) . "</strong>";
          //  $output .= "<br />";
          }
      
      if(is_numeric($product['file']) && ($product['file'] > 0))
        {
        $file_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$product['file']."' LIMIT 1",ARRAY_A);
        if(($file_data != null) && ($file_data[0]['mimetype'] == 'audio/mpeg') && (function_exists('listen_button')))
          {
          $output .= listen_button($file_data[0]['idhash']);
          }
        }
            
      if($product['description'] != '')
        {
        $output .= nl2br(stripslashes($product['description'])) . "<br />";
        }
        
      if($product['additional_description'] != '')
        {                
        $output .= "<span class='single_additional_description' >";
        $output .= nl2br(stripslashes($product['additional_description'])) . "";
        $output .= "</span><br /><br />";
        }
      
      $variations_procesor = new nzshpcrt_variations;
          
      $variations_output = $variations_procesor->display_product_variations($product['id'],false, false, true);
      
      $output .= $variations_output[0];
      if($variations_output[1] !== null)
        {
        $product['price'] = $variations_output[1];
        }
      //echo("<pre>".print_r($variations_output[1],true)."</pre>");
      if(($product['special']==1) && ($variations_output[1] === null))
        {
        $output .= "<span class='oldprice'>".TXT_WPSC_PRICE.": " . nzshpcrt_currency_display($product['price'], $product['notax']) . "</span><br />";
        $output .= TXT_WPSC_PRICE.": " . nzshpcrt_currency_display(($product['price'] - $product['special_price']), $product['notax'],false,$product['id']) . "<br />";
        }
        else
          {
          $output .= TXT_WPSC_PRICE.": <span id='product_price_".$product['id']."'>" . nzshpcrt_currency_display($product['price'], $product['notax']) . "</span><br />";
          }
      
      if(get_option('display_pnp') == 1)
        {
        $output .= TXT_WPSC_PNP.": " . nzshpcrt_currency_display($product['pnp'], 1) . "<br />";
        }
      
      $output .= "<input type='hidden' name='item' value='".$product['id']."' />";
      //AND (`quantity_limited` = '1' AND `quantity` > '0' OR `quantity_limited` = '0' )
      if(($product['quantity_limited'] == 1) && ($product['quantity'] < 1))
        {
        $output .= TXT_WPSC_PRODUCTSOLDOUT."";
        }
        else
          {
          $output .= "<input type='submit' class='buy_button' name='Buy' value='".TXT_WPSC_ADDTOCART."'  />";
          }
      if(get_option('product_ratings') == 1)
        {
        $output .= "<div class='product_footer'>";
        
        $output .= "<div class='product_average_vote'>";
        $output .= "<strong>".TXT_WPSC_AVGCUSTREVIEW.":</strong>";
        $output .= nzshpcrt_product_rating($product['id']);
        $output .= "</div>";
        
        $output .= "<div class='product_user_vote'>";
        $vote_output = nzshpcrt_product_vote($product['id'],"onmouseover='hide_save_indicator(\"saved_".$product['id']."_text\");'");
        if($vote_output[1] == 'voted')
          {
          $output .= "<strong><span id='rating_".$product['id']."_text'>".TXT_WPSC_YOURRATING.":</span>";
          $output .= "<span class='rating_saved' id='saved_".$product['id']."_text'> ".TXT_WPSC_RATING_SAVED."</span>";
          $output .= "</strong>";
          }
          else if($vote_output[1] == 'voting')
            {
            $output .= "<strong><span id='rating_".$product['id']."_text'>".TXT_WPSC_RATETHISITEM.":</span>";
            $output .= "<span class='rating_saved' id='saved_".$product['id']."_text'> ".TXT_WPSC_RATING_SAVED."</span>";
            $output .= "</strong>";
            }
        $output .= $vote_output[0];
        $output .= "</div>";
        $output .= "</div>";
        }
      
      $output .= "</div>";
      
      $output .= "</form>";
      
      $output .= "<form id='product_".$product['id']."' name='product_".$product['id']."' method='POST' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
      $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
      $output .= "<input type='hidden' name='item' value='".$product['id']."' />";
      $output .= "</form>";
      
      $output .= "      </td>\n\r";
      $output .= "    </tr>\n\r";
      }
    $output .= "</table>";
    }
    else
      {
      $output .= "<p>".TXT_WPSC_NOITEMSINTHIS." ".$group_type.".</p>";
      }
  return $output;
  }
?>