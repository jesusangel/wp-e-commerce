<?php
function wpsc_get_product_listing($product_list, $group_type, $group_sql = '', $search_sql = '')
  {
  global $wpdb;
  $siteurl = get_option('siteurl');
  $activated_widgets = get_option('sidebars_widgets');
      
  if(get_option('permalink_structure') != '')
    {
    $seperator ="?";
    }
    else
      {
      $seperator ="&amp;";
      }
  
  
  if((get_option('use_pagination') == 1))
    {
    $products_per_page = get_option('wpsc_products_per_page');        
    if(($_GET['page_number'] > 0))
      {
      $startnum = ($_GET['page_number']-1)*$products_per_page;
      }
      else
      {
      $startnum = 0;
      }
    }
    else
    {
    $startnum = 0;
    }
   
  foreach((array)$activated_widgets as $widget_container)
    {
    if(is_array($widget_container) && array_search(TXT_WPSC_DONATIONS, $widget_container))
      {
      $no_donations_sql = "AND `".$wpdb->prefix."product_list`.`donation` != '1'";
      break;
      }
    }  
  
  if(function_exists('gold_shpcrt_search_sql') && ($_GET['product_search'] != ''))
    {
    $search_sql = gold_shpcrt_search_sql();
    if($search_sql != '')
      {
      // this cannot currently list products that are associated with no categories
      
      
      $rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`".$wpdb->prefix."product_list`.`id`) AS `count` FROM `".$wpdb->prefix."product_list`,`".$wpdb->prefix."item_category_associations` WHERE `".$wpdb->prefix."product_list`.`active`='1' AND `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` $no_donations_sql $search_sql");
      
      if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
      if($startnum >= $rowcount)
        {
        $startnum = $rowcount - $products_per_page;
        }
      
      $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.* FROM `".$wpdb->prefix."product_list`,`".$wpdb->prefix."item_category_associations` WHERE `".$wpdb->prefix."product_list`.`active`='1' AND `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` $no_donations_sql $search_sql ORDER BY `".$wpdb->prefix."product_list`.`special` DESC LIMIT $startnum, $products_per_page";
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
         * If you can see a way of simplifying it and speeding it up, then go for it.
         */
         
         
        $rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`".$wpdb->prefix."product_list`.`id`) AS `count` FROM `".$wpdb->prefix."product_list` LEFT JOIN `".$wpdb->prefix."item_category_associations` ON `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` WHERE `".$wpdb->prefix."product_list`.`active` = '1' AND `".$wpdb->prefix."item_category_associations`.`category_id` IN ('".$catid."') $no_donations_sql");
        
        if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
        if($startnum >= $rowcount)
          {
          $startnum = $rowcount - $products_per_page;
          }
         
        $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.*, `".$wpdb->prefix."item_category_associations`.`category_id`,`".$wpdb->prefix."product_order`.`order`, IF(ISNULL(`".$wpdb->prefix."product_order`.`order`), 0, 1) AS `order_state` FROM `".$wpdb->prefix."product_list` LEFT JOIN `".$wpdb->prefix."item_category_associations` ON `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` LEFT JOIN `".$wpdb->prefix."product_order` ON ( ( `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."product_order`.`product_id` ) AND ( `".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_order`.`category_id` ) ) WHERE `".$wpdb->prefix."product_list`.`active` = '1' AND `".$wpdb->prefix."item_category_associations`.`category_id` IN ('".$catid."') $no_donations_sql ORDER BY `order_state` DESC,`".$wpdb->prefix."product_order`.`order` ASC LIMIT $startnum, $products_per_page";
        //exit($sql);
        }
        else
          {
          $rowcount = $wpdb->get_var("SELECT DISTINCT COUNT(`".$wpdb->prefix."product_list`.`id`) AS `count` FROM `".$wpdb->prefix."product_list`,`".$wpdb->prefix."item_category_associations` WHERE `".$wpdb->prefix."product_list`.`active`='1' AND `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` $no_donations_sql $group_sql");
          
          if(!is_numeric($products_per_page) || ($products_per_page < 1)) { $products_per_page = $rowcount; }
          if($startnum >= $rowcount)
            {
            $startnum = $rowcount - $products_per_page;
            }
        
          $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.* FROM `".$wpdb->prefix."product_list`,`".$wpdb->prefix."item_category_associations` WHERE `".$wpdb->prefix."product_list`.`active`='1' AND `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` $no_donations_sql $group_sql ORDER BY `".$wpdb->prefix."product_list`.`special` DESC LIMIT $startnum, $products_per_page"; 
          }
      }     
      
  // shows page numbers, probably fairly obviously
  $return_array['product_list'] = $wpdb->get_results($sql,ARRAY_A);
  $return_array['page_listing'] = "";
  
  if($rowcount > $products_per_page) 
    {
    if($products_per_page > 0)
      {
      $pages = ceil($rowcount/$products_per_page);
      }
      else
      {
      $pages = 1;
      }
    
    $product_view_url = get_option('product_list_url').$seperator;
    if(is_numeric($_GET['category']))
      {
      $product_view_url .= "category=".$_GET['category']."&amp;";
      }
    
    $return_array['page_listing'] .= "<div class='wpsc_page_numbers'>\n\r";
    $return_array['page_listing'] .= "Pages: ";
    for($i=1;$i<=$pages;++$i)
      {
      if(($_GET['page_number'] == $i) || (!is_numeric($_GET['page_number']) && ($i == 0)))
        {
        if($_GET['view_all'] != 'true')
          {
          $selected = "class='selected'";
          }
        }
        else
        {
        $selected = "class='notselected'";
        }
      $return_array['page_listing'] .= "  <a href='".$product_view_url."page_number=$i' $selected >$i</a>\n\r";
      }    
    $return_array['page_listing'] .= "</div>\n\r";
    }
  
  return $return_array;
  }


function product_display_default($product_list, $group_type, $group_sql = '', $search_sql = '')
  {
  global $wpdb;
  $siteurl = get_option('siteurl');
   
  // pe.{ 350rc5
  if(get_option('permalink_structure') != '')
    {
    $seperator ="?";
    }
    else
    {
    $seperator ="&amp;";
    }
  // }.pe
   
  $product_listing_data = wpsc_get_product_listing($product_list, $group_type, $group_sql, $search_sql);
  
  $product_list = $product_listing_data['product_list'];
  
  if((get_option('wpsc_page_number_position') == 1) || (get_option('wpsc_page_number_position') == 3))
    {
    $output .= $product_listing_data['page_listing'];
    }
  
  if($product_list != null)
    {
    $output .= "<table class='productdisplay'>";
    foreach($product_list as $product)
      {
      $num++;
      $output .= "    <tr>";
      if($category_data[0]['fee'] == 0)
        {
        $output .= "      <td class='imagecol'>";
        if(get_option('show_thumbnails') == 1)
          {
          if($product['image'] !=null)
            {
            $image_size = @getimagesize($imagedir.$product['image']);
            $image_link = "$siteurl/wp-content/plugins/wp-shopping-cart/product_images/".$product['image']."";

            $output .= "<a href='".$image_link."' class='thickbox preview_link'  rel='".str_replace(" ", "_",$product['name'])."'>";
            
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
      $output .= "<form id='product_".$product['id']."' name='product_".$product['id']."' method='post' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
      $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
      
      $imagedir = ABSPATH."wp-content/plugins/wp-shopping-cart/product_images/";
      $output .= "<div class='producttext'>$special";
      // pe.{ 350rc5
      //$output .= "<strong>". stripslashes($product['name']) . "</strong>";
      $output .= "<a href='".get_option('product_list_url').$seperator."product_id=".$product['id']."' class='wpsc_product_title' ><strong>" . stripslashes($product['name']) . "</strong></a>";
      // }.pe
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
        $output .= "<img id='link_icon".$product['id']."' class='additional_description_button'  src='$siteurl/wp-content/plugins/wp-shopping-cart/images/icon_window_expand.gif' title='".$product['name']."' alt='".$product['name']."' />";
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
      
      if($product['donation'] == 1)
        {
        $currency_sign_location = get_option('currency_sign_location');
        $currency_type = get_option('currency_type');
        $currency_symbol = $wpdb->get_var("SELECT `symbol_html` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1") ;
        $output .= "<label for='donation_price_".$product['id']."'>".TXT_WPSC_DONATION.":</label> $currency_symbol<input type='text' id='donation_price_".$product['id']."' name='donation_price' value='".number_format($product['price'],2)."' size='6' /><br />";
        }
        else
        {
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
        }
          
      
      $output .= "<input type='hidden' name='item' value='".$product['id']."' />";
      //AND (`quantity_limited` = '1' AND `quantity` > '0' OR `quantity_limited` = '0' )
      if(($product['quantity_limited'] == 1) && ($product['quantity'] < 1) && $variations_output[1] === null)
        {
        $output .= TXT_WPSC_PRODUCTSOLDOUT."";
        }
        else
          {
          $output .= "<input type='submit' id='product_".$product['id']."_submit_button' class='wpsc_buy_button' name='Buy' value='".TXT_WPSC_ADDTOCART."'  />";
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
 
  if((get_option('wpsc_page_number_position') == 2) || (get_option('wpsc_page_number_position') == 3))
    {
    $output .= $product_listing_data['page_listing'];
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
        $output .= "      <td class='imagecol'>";
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
              
              $output .= "<a href='".$image_link."' class='thickbox preview_link'  rel='".str_replace(" ", "_",$product['name'])."'>";
              if((get_option('single_view_image_width') >= 1) && (get_option('single_view_image_height') >= 1))
                {
                //
                $output .= "<img src='index.php?productid=".$product['id']."&width=".get_option('single_view_image_width')."&height=".get_option('single_view_image_height')."' title='".$product['name']."' alt='".$product['name']."' id='product_image_".$product['id']."' class='product_image'/>";
                }
                else
                {
                $output .= "<img src='$siteurl/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/".$image_file_name."' title='".$product['name']."' alt='".$product['name']."' id='product_image_".$product['id']."' class='product_image'/>";
                }
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
      $output .= "<form id='product_".$product['id']."' name='$num' method='post' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
      $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
      
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
      
      if(function_exists('wpsc_akst_share_link') && (get_option('wpsc_share_this') == 1))
        {
        $output .=  wpsc_akst_share_link('return');
        }
      
      $variations_procesor = new nzshpcrt_variations;
          
      $variations_output = $variations_procesor->display_product_variations($product['id'],false, false, true);
      
      $output .= $variations_output[0];
      if($variations_output[1] !== null)
        {
        $product['price'] = $variations_output[1];
        }
      
      if($product['donation'] == 1)
        {
        $currency_sign_location = get_option('currency_sign_location');
        $currency_type = get_option('currency_type');
        $currency_symbol = $wpdb->get_var("SELECT `symbol_html` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1") ;
        $output .= "<label for='donation_price_".$product['id']."'>".TXT_WPSC_DONATION.":</label> $currency_symbol<input type='text' id='donation_price_".$product['id']."' name='donation_price' value='".number_format($product['price'],2)."' size='6' /><br />";
        }
        else
        {
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
        }
      
      $output .= "<input type='hidden' name='item' value='".$product['id']."' />";
      //AND (`quantity_limited` = '1' AND `quantity` > '0' OR `quantity_limited` = '0' )
      if(($product['quantity_limited'] == 1) && ($product['quantity'] < 1))
        {
        $output .= TXT_WPSC_PRODUCTSOLDOUT."";
        }
        else
          {
          $output .= "<input type='submit' id='product_".$product['id']."_submit_button' class='wpsc_buy_button' name='Buy' value='".TXT_WPSC_ADDTOCART."'  />";
          }
         
      if(function_exists('gold_shpcrt_display_gallery'))
        {
        $output .= gold_shpcrt_display_gallery($product['id']);
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
      
      $output .= "<form id='product_".$product['id']."' name='product_".$product['id']."' method='post' action='".get_option('product_list_url').$seperator."category=".$_GET['category']."' onsubmit='submitform(this);return false;' >";
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

function fancy_notifications()
  {
  global $wpdb;
  if(get_option('fancy_notifications') == 1)
    {
    $output = "";
    $output .= "<div id='fancy_notification'>\n\r";
    $output .= "  <div id='loading_animation'>\n\r";
    $output .= '<img id="fancy_notificationimage" title="Loading" alt="Loading" src="http://apps.instinct.co.nz/wordpress_development/wp-content/plugins/wp-shopping-cart/images/indicator.gif"/>'.TXT_WPSC_UPDATING."...\n\r";
    $output .= "  </div>\n\r";
    $output .= "  <div id='fancy_notification_content'>\n\r";
    $output .= "  </div>\n\r";
    $output .= "</div>\n\r";
    }
  return $output;
  }

function fancy_notification_content($product_id, $quantity_limit = false)
  {
  global $wpdb;
  $siteurl = get_option('siteurl');
  $instock = true;
  if(is_numeric($product_id))
    {
    $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".$product_id."' LIMIT 1";
    $product = $wpdb->get_row($sql,ARRAY_A);
    if($product['quantity_limited'] == 1)
      {
      }
    $output = "";
    if($quantity_limit == false)
      {
      $output .= "<span>".str_replace("[product_name]", $product['name'], TXT_WPSC_YOU_JUST_ADDED)."</span>";
      }
      else
        {
        $output .= "<span>".str_replace("[product_name]", $product['name'], TXT_WPSC_SORRY_NONE_LEFT)."</span>";
        }
    $output .= "<a href='".get_option('shopping_cart_url')."' class='go_to_checkout'>".TXT_WPSC_GOTOCHECKOUT."</a>";
    $output .= "<a href='#' onclick='jQuery(\"#fancy_notification\").css(\"display\", \"none\"); return false;' class='continue_shopping'>".TXT_WPSC_CONTINUE_SHOPPING."</a>";
    }
  return $output;
  }
?>