<?php
function nzshpcrt_overall_total_price($country_code = null, $for_display = false)
    {
    /*
     * Determines the total in the shopping cart, adds the tax and shipping if a country code is supplied
     * Adds a dollar sign and information if there is no tax and shipping if $for_display is true
     */
    global $wpdb;
    $cart  =& $_SESSION['nzshpcrt_cart'];
    $total_quantity =0;
    $total_weight = 0;
    foreach($cart as $cart_item)
      {
      $product_id = $cart_item->product_id;
      $quantity = $cart_item->quantity;
      $product_variations = $cart_item->product_variations;
      $raw_price = 0;
      $variation_count = count($product_variations);
      if($variation_count > 0)
        {
        foreach($product_variations as $product_variation)
          {
          $value_id = $product_variation;
          $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          }
        }
      //$total_quantity += $quantity;
      $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1";
      $product = $wpdb->get_results($sql,ARRAY_A);
      
      $price = $quantity * calculate_product_price($product_id, $product_variations);
      if($country_code != null)
        {
        if($product[0]['notax'] != 1)
          {
          $price = nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']);
          }
        $shipping = nzshpcrt_determine_item_shipping($product_id, $quantity, $country_code);
        $price += $shipping;
        }
      $total += $price;
      }
    
    if($country_code != null)
      {
      $total +=  nzshpcrt_determine_base_shipping(0, $country_code);
      }
    
    if($for_display === true)
      {
      $total = nzshpcrt_currency_display($total,1);
      if($country_code == null)
        {
        $total .= " + ".TXT_WPSC_POSTAGE_AND_TAX;
        }
      }
    return $total;
    }
  
  function nzshpcrt_calculate_tax($price, $country, $region)
    {
    global $wpdb;
    $country_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."currency_list` WHERE `isocode` IN('".get_option('base_country')."') LIMIT 1",ARRAY_A);
    if(($country_data['has_regions'] == 1))
      {
      $region_data = $wpdb->get_row("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax` WHERE `".$wpdb->prefix."region_tax`.`country_id` IN('".$country_data['id']."') AND `".$wpdb->prefix."region_tax`.`id` IN('".get_option('base_region')."') ",ARRAY_A) ;
       $tax_percentage =  $region_data['tax'];
      }
      else
        {
        $tax_percentage =  $country_data['tax'];
        }
    $add_tax = false;
    if($country == get_option('base_country'))
      {
      if($country_data['has_regions'] == 1)
        {
        if($region == get_option('base_region'))
          {
          $add_tax = true;
          }
        }
        else
          {
          $add_tax = true;          
          }
      }
    if($add_tax === true)
      {
      $price = $price * (1 + ($tax_percentage/100));
      }
    return $price;
    }
  
  function nzshpcrt_find_total_price($purchase_id,$country_code)
    {
    global $wpdb;
    if(is_numeric($purchase_id))
      {
      $purch_sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `id`='".$purchase_id."'";
      $purch_data = $wpdb->get_results($purch_sql,ARRAY_A) ;

      $cartsql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`=".$purchase_id."";
      $cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
      if($cart_log != null)
        {
        foreach($cart_log as $cart_row)
          {
          $productsql= "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=".$cart_row['prodid']."";
          $product_data = $wpdb->get_results($productsql,ARRAY_A); 
        
          $variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$cart_row['id']."'";
          $variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
          $variation_count = count($variation_data);
          $price = ($cart_row['price'] * $cart_row['quantity']);
          
          
          if($purch_data['shipping_country'] != '')
            {
            $country_code = $purch_data['shipping_country'];
            }
          $shipping = nzshpcrt_determine_item_shipping($cart_row['prodid'], $cart_row['quantity'], $country_code);
          $endtotal += $shipping + $price;
          }
        $endtotal += nzshpcrt_determine_base_shipping(0, $country_code);
        }
      return $endtotal;
      }
    }
    
  function nzshpcrt_determine_base_shipping($per_item_shipping, $country_code)
    {    
    global $wpdb;
    if($country_code == get_option('base_country'))
      {
      $base_shipping = get_option('base_local_shipping');
      }
      else
        {
        $base_shipping = get_option('base_international_shipping');
        }

    $shipping = $base_shipping + $per_item_shipping;
    return $shipping;    
    }
    
  function nzshpcrt_determine_item_shipping($product_id, $quantity, $country_code)
    {    
    global $wpdb;
    if(is_numeric($product_id))
      {
      $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='$product_id' LIMIT 1";
      $product_list = $GLOBALS['wpdb']->get_results($sql,ARRAY_A) ;
      
      if($country_code == get_option('base_country'))
        {
        $additional_shipping = $product_list[0]['pnp'];
        }
        else
          {
          $additional_shipping = $product_list[0]['international_pnp'];
          }
  
      $shipping = $quantity * $additional_shipping;
      }
    return $shipping;    
    }

function nzshpcrt_currency_display($price_in, $tax_status, $nohtml = false, $id = false, $no_dollar_sign = false)
  {
  /*
   * This now ignores tax status, but removing it entirely will probably have to wait for the inevitable total rewrite.
   */
  global $wpdb;
  $currency_sign_location = get_option('currency_sign_location');
  $currency_type = get_option('currency_type');
  $currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1",ARRAY_A) ;
  $price_out = null;
  $currency_sign_location = get_option('currency_sign_location');
  $currency_type = get_option('currency_type');
  $currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$currency_type."' LIMIT 1",ARRAY_A) ;
  $price_out = null;
  if(is_numeric($id))
    {
    
    }

  $price_out =  number_format($price_in, 2, '.', '');

  if($currency_data[0]['symbol'] != '')
    {    
    if($nohtml == false)
      {
      $currency_sign = $currency_data[0]['symbol_html'];
      }
      else
        {
        $currency_sign = $currency_data[0]['symbol'];
        }
    }
    else
      {
      $currency_sign = $currency_data[0]['code'];
      }

  switch($currency_sign_location)
    {
    case 1:
    $output = $price_out.$currency_sign;
    break;

    case 2:
    $output = $price_out.' '.$currency_sign;
    break;

    case 3:
    $output = $currency_sign.$price_out;
    break;

    case 4:
    $output = $currency_sign.'  '.$price_out;
    break;
    }

  if($nohtml == true)
    {
    $output = "".$output."";
    }
    else
      {
      $output = "<span class='pricedisplay'>".$output."</span>";
      }
      
  if($no_dollar_sign == true)
    {
    return $price_out;
    }
  return $output;
  }
  
function admin_display_total_price($start_timestamp = '', $end_timestamp = '')
  {
  global $wpdb;
  if(($start_timestamp != '') && ($end_timestamp != ''))
    {
    $sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` > '1' AND `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC";
    }
    else
      {
      $sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `processed` > '1' AND `date` != ''";
      }
  $purchase_log = $wpdb->get_results($sql,ARRAY_A) ;
  $total = 0;
  if($purchase_log != null)
    {
    foreach($purchase_log as $purchase)
      {
      $country_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
      $country_data = $wpdb->get_results($country_sql,ARRAY_A);
      $country = $country_data[0]['value'];
      $total += nzshpcrt_find_total_price($purchase['id'],$country);
      }
    }
  return $total;
  }
  


function calculate_product_price($product_id, $variations = false)
  {
  global $wpdb;
  if(is_numeric($product_id))
    {
    if(is_array($variations) && ((count($variations) >= 1) && (count($variations) <= 2)))
      {
      $variation_count = count($variations);
      $variations = array_values($variations);
      }
      
    if(($variation_count >= 1) && ($variation_count <= 2))
      {
      switch($variation_count)
        {
        case 1:
        $sql = "SELECT `price` FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$product_id."' AND `variation_id_1` = '".$variations[0]."' AND `variation_id_2` = '0' LIMIT 1";
        break;
        
        case 2:
        $sql = "SELECT `price` FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$product_id."' AND (`variation_id_1` = '".$variations[0]."' AND `variation_id_2` = '".$variations[1]."') OR (`variation_id_1` = '".$variations[1]."' AND `variation_id_2` = '".$variations[0]."') LIMIT 1";
        break;
        }
      $price = $wpdb->get_var($sql);
      }
      else
        {
        $sql = "SELECT `price`,`special`,`special_price` FROM `".$wpdb->prefix."product_list` WHERE `id`='".$product_id."' LIMIT 1";
        $product_data = $wpdb->get_row($sql,ARRAY_A);
        if($product_data['special_price'] > 0)
          {
          $price = $product_data['price'] - $product_data['special_price'];
          }
          else
          {
          $price = $product_data['price'];
          }
        }
    }
    else
      {
      $price = false;
      }
  return $price;
  }
?>