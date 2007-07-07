<?php
function nzshpcrt_shopping_basket($input = null, $override_state = null)
  {
  global $wpdb;
  
  if(is_numeric($override_state))
    {
    $state = $override_state;
    }
    else
      {
      $state = get_option('cart_location');
      }
  
  if($state == 1)
    {
    if($input != '')
      {
      $cart = $_SESSION['nzshpcrt_cart'];
      echo "<div id='sideshoppingcart'><div id='shoppingcartcontents'>";
      echo nzshpcrt_shopping_basket_internals($cart);
      echo "</div></div>";
      }
    }
    else if(($state == 3) || ($state == 4))
      {
      $cart = $_SESSION['nzshpcrt_cart'];
      if($state == 4)
        {
        #echo $input;
        echo "<div id='widgetshoppingcart'><div id='shoppingcartcontents'>";
        echo nzshpcrt_shopping_basket_internals($cart);
        echo "</div></div>";
        $dont_add_input = true;
        }
        else
          {
          echo "<div id='sideshoppingcart'><div id='shoppingcartcontents'>";
          echo nzshpcrt_shopping_basket_internals($cart);
          echo "</div></div>";
          }
      }
      else
        {
        if(($GLOBALS['nzshpcrt_activateshpcrt'] === true))
          {
          $cart = $_SESSION['nzshpcrt_cart'];
          echo "<div id='shoppingcart'><div id='shoppingcartcontents'>";
          echo nzshpcrt_shopping_basket_internals($cart);
          echo "</div></div>";
          }
        }
  
  if($dont_add_input !== true)
    {
    if($input != '')
      {
      echo $input;
      }
    }
  }

function nzshpcrt_shopping_basket_internals($cart,$quantity_limit = false, $title='')
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
    
  
  if(function_exists("drag_and_drop_cart"))
    {
    //add_action('wp_footer', 'drag_and_drop_cart');
    }
  
  if(get_option('show_sliding_cart') == 1)
    {
    if(is_numeric($_SESSION['slider_state']))
      {
      if($_SESSION['slider_state'] == 0) { $collapser_image = 'plus.png'; } else { $collapser_image = 'minus.png'; }
      $fancy_collapser = "<a href='#' onclick='return shopping_cart_collapser()' id='fancy_collapser_link'><img src='".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/$collapser_image' title='' alt='' id='fancy_collapser' /></a>";
      }
      else
      {
      if($_SESSION['nzshpcrt_cart'] == null) { $collapser_image = 'plus.png'; } else { $collapser_image = 'minus.png'; }
      $fancy_collapser = "<a href='#' onclick='return shopping_cart_collapser()' id='fancy_collapser_link'><img src='".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/$collapser_image' title='' alt='' id='fancy_collapser' /></a>";
      }
    } else { $fancy_collapser = ""; }
  
  $current_url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  switch(get_option('cart_location'))
    {
    case 1:
    $output .= "<h2>".TXT_WPSC_SHOPPINGCART." $fancy_collapser</h2>";
    $output .="<span id='alt_loadingindicator'><img id='alt_loadingimage' src='". get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/indicator.gif' alt='Loading' title='Loading' /> ".TXT_WPSC_UDPATING."...</span></strong><br />";
    $spacing = "";
    break;
    
    case 3:
    $output .= "<strong class='cart_title'>".TXT_WPSC_SHOPPINGCART." $fancy_collapser</strong>";
    break;
    
    case 4:
    if(is_array($GLOBALS['registered_sidebars']))
      {
      $sidebar_args = end($GLOBALS['registered_sidebars']);
      }    
      else
        {
        $sidebar_args['before_title'] = "<h2>";
        $sidebar_args['after_title'] = "</h2>";
        }
    $output .= $sidebar_args['before_title'] . TXT_WPSC_SHOPPINGCART." $fancy_collapser" . $sidebar_args['after_title'];
    break;
    
    default:
    $output .= "<strong class='cart_title'>".TXT_WPSC_SHOPPINGCART." $fancy_collapser</strong>";
    break;
    }
 
 foreach((array)$cart as $item)
   {
   $cart_count += $item->quantity;
   }
 
 $output .= "<div id='sliding_cart'>";
  if($cart != null)
    {
    if($quantity_limit == true)
      {
      $output .= TXT_WPSC_NUMBEROFITEMS.": &nbsp;&nbsp;".$cart_count."<br /><br />";
      $output .= TXT_WPSC_NOMOREAVAILABLE."<br /><br />";
      }
      else
        {
        $output .= TXT_WPSC_NUMBEROFITEMS.": &nbsp;&nbsp;".$cart_count."<br /><br />";
        }
    

    $output .= "<table class='shoppingcart'>\n\r";
    $output .= "<tr><th>".TXT_WPSC_PRODUCT."</th><th>".TXT_WPSC_QUANTITY_SHORT."</th><th>".TXT_WPSC_PRICE."</th></tr>\n\r";
    $all_donations = true;
    $tax = 0;
    foreach($cart as $cart_item)
      {
      $product_id = $cart_item->product_id;
      $quantity = $cart_item->quantity;
      //echo("<pre>".print_r($cart_item->product_variations,true)."</pre>");
      $product = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
      if($product['donation'] == 1)
        {
        $price = $quantity * $cart_item->donation_price;
        }
        else
        {
        $price = $quantity * calculate_product_price($product_id, $cart_item->product_variations);
        if($product['notax'] != 1)
          {
          $tax += nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']) - $price;
          }
        $all_donations = false;
        }
        
      if($_SESSION['delivery_country'] != null)
        {        
        $total_shipping += nzshpcrt_determine_item_shipping($product['id'], $quantity, $_SESSION['delivery_country']);
        }
      
      $total += $price;
      
      $output .= "<tr>";
      $output .= "<td><a href='".get_option('product_list_url').$seperator."product_id=".$product['id']."' >".$product['name']."</a></td>";
      $output .= "<td>".$quantity."</td>";
      $output .= "<td>".nzshpcrt_currency_display($price, 1)."</td>";
      $output .= "</tr>\n\r";
      }
    $output .= "</table>";
    if($_SESSION['delivery_country'] != null)
      {
      $total_shipping = nzshpcrt_determine_base_shipping($total_shipping, $_SESSION['delivery_country']);
      $output .= "<strong>".TXT_WPSC_SUBTOTAL.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display(($total), 1)."<br />";
      if((get_option('do_not_use_shipping') != 1) && ($all_donations == false))
        {
        $output .= "<strong>".TXT_WPSC_POSTAGE.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display($total_shipping, 1)."<br />";
        }
      if($tax > 0)
        {
        $output .= "<strong>".TXT_WPSC_TAX.":</strong> &nbsp;&nbsp;".nzshpcrt_currency_display($tax, 1)."<br />";        
        }
      $output .= "<strong>".TXT_WPSC_TOTAL.":</strong> &nbsp;&nbsp;".nzshpcrt_overall_total_price($_SESSION['delivery_country'],true)."<br /><br />";
      }
      else
        {
        $output .= "<strong>".TXT_WPSC_TOTAL.":</strong> &nbsp;&nbsp;".nzshpcrt_overall_total_price($_SESSION['selected_country'],true)."<br /><br />";
        }
    if(get_option('permalink_structure') != '')
      {
      $seperator ="?";
      }
      else
         {
         $seperator ="&amp;";
         }
    $output .= "<a href='".get_option('product_list_url').$seperator."category=".$_GET['category']."&amp;cart=empty' onclick='emptycart();return false;'>".TXT_WPSC_EMPTYYOURCART."</a><br />";
    $output .= "<a href='".get_option('shopping_cart_url')."'>".TXT_WPSC_GOTOCHECKOUT."</a><br />";
    //$output .= "<a href='".get_option('product_list_url')."'>".TXT_WPSC_CONTINUESHOPPING."</a>";
    }
    else
      {
      $output .= $spacing;
      $output .= TXT_WPSC_YOURSHOPPINGCARTISEMPTY.".<br />";
      $output .= "<a href='".get_option('product_list_url')."'>".TXT_WPSC_VISITTHESHOP."</a>";
      }
  
 $output .= "</div>";
  return $output;
  }
  
function wpsc_country_region_list($form_id = null, $ajax = false , $selected_country = null, $selected_region = null )
  {
  global $wpdb;
  if($selected_country == null)
    {
    $selected_country = get_option('base_country');
    }
  if($selected_region == null)
    {
    $selected_region = get_option('base_region');
    }
  if($form_id != null)
    {
    $html_form_id = "region_country_form_$form_id";
    }
    else
      {
      $html_form_id = 'region_country_form';
      }
  $country_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
  $output .= "<div id='$html_form_id'>\n\r";
  $output .= "<select name='collected_data[".$form_id."][0]' class='current_country' onchange='set_billing_country(\"$html_form_id\", \"$form_id\");' >\n\r";
  foreach ($country_data as $country)
    {
    $selected ='';
    if($selected_country == $country['isocode'])
      {
      $selected = "selected='true'";
      }
    $output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>\n\r";
    }  
  $output .= "</select>\n\r";
  
  
  $region_list = $wpdb->get_results("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".$selected_country."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`",ARRAY_A) ;
    $output .= "<div id='region_select_$form_id'>";
    if($region_list != null)
      {
      $output .= "<select name='collected_data[".$form_id."][1]' class='current_region' onchange='set_billing_country(\"$html_form_id\", \"$form_id\");'>\n\r";
      //$output .= "<option value=''>None</option>";
      foreach($region_list as $region)
        {
        if($selected_region == $region['id'])
          {
          $selected = "selected='true'";
          }
          else
            {
            $selected = "";
            }
        $output .= "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
        }
      $output .= "</select>\n\r";
      }
  $output .= "</div>";
  $output .= "</div>\n\r";
  return $output;
  }
?>