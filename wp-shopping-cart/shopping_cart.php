<?php
global $wpdb;
if($_POST['country'] != null)
  {
  $_SESSION['selected_country'] = $_POST['country'];
  }
  else if($_SESSION['selected_country'] == '')
    {
    $_SESSION['selected_country'] = get_option('base_country');
    }

if($_POST['region'] != null)
  {
  $_SESSION['selected_region'] = $_POST['region'];
  }
  else if($_SESSION['selected_region'] == '')
    {
    $_SESSION['selected_region'] = get_option('base_region');
    }

if(get_option('permalink_structure') != '')
  {
  $seperator ="?";
  }
  else
    {
    $seperator ="&amp;";
    }
 
$rawnum = null;
$number = null;  
$cart = $_SESSION['nzshpcrt_cart'];

function country_region_list($selected_country = null, $selected_region = null)
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
  $country_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
  $output .= "<select name='country' id='current_country' onchange='submit_change_country();' >";
  foreach ($country_data as $country)
    {
    $selected ='';
    if($selected_country == $country['isocode'])
      {
      $selected = "selected='true'";
      }
    $output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>";
    }  
  $output .= "</select>";
  
  
  $region_list = $wpdb->get_results("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".$selected_country."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`",ARRAY_A) ;
    if($region_list != null)
      {
      $output .= "<select name='region' onchange='submit_change_country();'>\n\r";
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
  return $output;
  }

?>
<div class="wrap">
  <?php
  if($_SESSION['nzshpcrt_cart'] != null)
    {
  ?>
  <span>
  <?php echo TXT_WPSC_CONFIRM_TOTALS; ?></span>
  <hr class='productcart' />
  <table class='productcart'>
  <?php
    
  echo "<tr class='firstrow'>\n\r";
  echo "  <td class='firstcol'>".TXT_WPSC_PRODUCT.":</td>\n\r";
  echo "  <td>".TXT_WPSC_QUANTITY.":</td>\n\r";
  echo "  <td>". TXT_WPSC_PRICE.":</td>\n\r";
  echo "  <td></td>\n\r";  
  echo "</tr>\n\r";
  $num = 1;
  $total = 0;
  $total_shipping = 0;
  foreach($cart as $key => $cart_item)
    {
    $product_id = $cart_item->product_id;
    $quantity = $cart_item->quantity;
    $number =& $quantity;
    $product_variations = $cart_item->product_variations;
    $variation_count = count($product_variations);
    //exit("<pre>".print_r($product_variations,true)."</pre>");
    if($variation_count >= 1)
      {
      $variation_list = "&nbsp;(";
      $i = 0;
      foreach($product_variations as $value_id)
        {
        if($i > 0)
          {
          $variation_list .= ",&nbsp;";
          }
        $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
        $variation_list .= str_replace(" ", "&nbsp;",$value_data[0]['name']);    
        //echo("<pre>".print_r($variation,true)."</pre>");          
        $i++;
        }
      $variation_list .= ")";
      }
      else
        {
        $variation_list = '';
        }
    $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='$product_id' LIMIT 1";
    $product_list = $wpdb->get_results($sql,ARRAY_A) ;
    echo "<tr class='product_row'>\n\r";
    
    echo "  <td class='firstcol'>\n\r";
    echo $product_list[0]['name'] . $variation_list;
    echo "  </td>\n\r";
    
    echo "  <td>\n\r";
    echo  "<form class='adjustform' method='POST' action='".get_option('shopping_cart_url')."'><input type='text' value='".$number."' size='2' name='quantity' /><input type='hidden' value='".$key."' name='key' />&nbsp; <input type='submit' name='submit' value='".TXT_WPSC_APPLY."' /></form>";
    echo "  </td>\n\r";
    
    echo "  <td>\n\r";
        
    $price = $quantity * calculate_product_price($product_id, $cart_item->product_variations);
    
    if($product_list[0]['notax'] != 1)
      {
      $price = nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']);
      }
    
    echo nzshpcrt_currency_display($price, $product_list[0]['notax']);
    $total += $price;
        
    echo "  </td>\n\r";
    $shipping = nzshpcrt_determine_item_shipping($product_id, $number, $_SESSION['selected_country']);
    $total_shipping += $shipping;
    echo "  <td>\n\r";
    echo "<a href='".get_option('shopping_cart_url').$seperator."remove=".$key."'>Remove</a>";
    echo "  </td>\n\r";
    
    echo "</tr>\n\r";
    }
    
  $siteurl = get_option('siteurl');
  
      //echo $total;
  $total_shipping = nzshpcrt_determine_base_shipping($total_shipping, $_SESSION['selected_country']);
  $total += $total_shipping;
  if(get_option('base_country') != null)
    {
    echo "<tr class='product_shipping'>\n\r";
    echo "  <td colspan='2'>\n\r";
    ?>
    <div class='select_country'>
      <?php echo TXT_WPSC_SHIPPING_COUNTRY; ?>
      <form name='change_country' action='' method='POST'>
      <?php
      echo country_region_list($_SESSION['selected_country'], $_SESSION['selected_region']);
      ?>
      </form>
    </div>
    <?php
    echo "  </td>\n\r";
    echo "  <td colspan='2' style='vertical-align: middle;'>\n\r";
    echo "" . nzshpcrt_currency_display($total_shipping, 1) . "";
    echo "  </td>\n\r";
    echo "</tr>\n\r";
    }
    
  //echo "<tr style='total-price'>\n\r";
  echo "<tr class='total_price'>\n\r";
  echo "  <td colspan='2'>\n\r";
  echo "".TXT_WPSC_TOTALPRICE.":";
  echo "  </td>\n\r";
  echo "  <td colspan='2' style='vertical-align: middle;'>\n\r";
  echo "" . nzshpcrt_currency_display($total, 1) . "";
  echo "  </td>\n\r";
  echo "</tr>\n\r";
    
  echo "</table>";
  
    
  echo "
  <ul class='checkout_links'>
    <li>
      &gt;
      <a href='".get_option('checkout_url').$seperator."total=$total'>".TXT_WPSC_MAKEPAYMENT."</a>
    </li>
    <li>
      &gt;
      <a href='".get_option('product_list_url')."'>".TXT_WPSC_CONTINUESHOPPING."</a>
    </li>
    <li>
      &gt;
      <a href='".get_option('shopping_cart_url').$seperator."cart=empty'>".TXT_WPSC_EMPTYSHOPPINGCART."</a>
    </li>
  </ul>\n\r";
  
  $_SESSION['nzshpcrt_totalprice'] = $total; 
  
    }
    else
      {
      echo TXT_WPSC_NOITEMSINTHESHOPPINGCART;
      }
  ?>
</div>