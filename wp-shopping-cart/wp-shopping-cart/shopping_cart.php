<?php
global $wpdb, $user_ID;
//$_SESSION['coupon_num'] = '';


$saved_data_sql = "SELECT * FROM `".$wpdb->prefix."usermeta` WHERE `user_id` = '".$user_ID."' AND `meta_key` = 'wpshpcrt_usr_profile';";
$saved_data = $wpdb->get_row($saved_data_sql,ARRAY_A);
$meta_data = unserialize($saved_data['meta_value']);

if($_POST['country'] != null) {
  $_SESSION['delivery_country'] = $_POST['country'];
  if($_SESSION['selected_country'] == null) {
    $_SESSION['selected_country'] = $_POST['country'];
	}
} else if($_SESSION['selected_country'] == '') {
	$_SESSION['selected_country'] = get_option('base_country');
	$_SESSION['delivery_country'] = get_option('base_country');
}

if($_SESSION['delivery_country'] == '') {
  $_SESSION['delivery_country'] = $_SESSION['selected_country'];
}

if($_POST['region'] != null) {
  $_SESSION['selected_region'] = $_POST['region'];
} else if($_SESSION['selected_region'] == '') {
	$_SESSION['selected_region'] = get_option('base_region');
}

if(get_option('permalink_structure') != '') {
  $seperator ="?";
} else {
	$seperator ="&amp;";
}
 
if($_POST['coupon_num']){
	$_SESSION['coupon_num'] = $_POST['coupon_num'];
}

//exit($_SESSION['coupon_num']);
 
$rawnum = null;
$number = null;  
$cart = $_SESSION['nzshpcrt_cart'];

function wpsc_shipping_country_list($selected_country = null) {
  global $wpdb;  
  if($selected_country == null) {
    $selected_country = get_option('base_country');
	}
  if($selected_region == null) {
    $selected_region = get_option('base_region');
	}
  $country_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
  $output .= "<select name='country' id='current_country' onchange='submit_change_country();' >";
  foreach ($country_data as $country) {
    $selected ='';
    if($selected_country == $country['isocode']) {
      $selected = "selected='true'";
		}
    $output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>";
	} 
  $output .= "</select>";
  return $output;
}
?>
<div class="wrap wpsc_container">
<?php
  if($_SESSION['nzshpcrt_cart'] != null) {
  
  echo "<span>".TXT_WPSC_CONFIRM_TOTALS."</span>\n\r";
  echo "<hr class='productcart' />\n\r";
  
  echo "<table class='productcart'>\n\r";  
  echo "<tr class='firstrow'>\n\r";
  echo "  <td class='firstcol'>".TXT_WPSC_PRODUCT.":</td>\n\r";
  echo "  <td>".TXT_WPSC_QUANTITY.":</td>\n\r";
  echo "  <td>". TXT_WPSC_PRICE.":</td>\n\r";
  echo "  <td></td>\n\r";  
  echo "</tr>\n\r";
  $num = 1;
  $total = 0;
  $total_shipping = 0;
  $all_donations = true;
  $all_no_shipping = true;
  $tax =0;
  foreach($cart as $key => $cart_item) {
    $product_id = $cart_item->product_id;
    $quantity = $cart_item->quantity;
    $extras = $cart_item->extras;
    $number =& $quantity;
    $product_variations = $cart_item->product_variations;
    $extras_count = count($cart_item->extras);
    $variation_count = count($product_variations);
    //exit("<pre>".print_r($product_variations,true)."</pre>");
    if($variation_count >= 1) {
      $variation_list = "&nbsp;(";
      $i = 0;
	//exit(print_r($product_variations,1));
      foreach($product_variations as $value_id) {
        if($i > 0) {
          $variation_list .= ",&nbsp;";
				}
        $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
        #$variation_list .= str_replace(" ", "&nbsp;",$value_data[0]['name']);
	$variation_list .= str_replace("", " ",$value_data[0]['name']);
	//echo("<pre>".print_r($variation,true)."</pre>");
        $i++;
        }
      $variation_list .= ")";
		} else {
			$variation_list = '';
		}
    $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='$product_id' LIMIT 1";
    $product_list = $wpdb->get_row($sql,ARRAY_A) ;
    echo "<tr class='product_row'>\n\r";
    
    echo "  <td class='firstcol'>\n\r";
    echo $product_list['name'] . $variation_list;
    echo "  </td>\n\r";
    
    echo "  <td>\n\r";
    echo  "<form class='adjustform' method='POST' action='".get_option('shopping_cart_url')."'><input type='text' value='".$number."' size='2' name='quantity' /><input type='hidden' value='".$key."' name='key' />&nbsp; <input type='submit' name='submit' value='".TXT_WPSC_APPLY."' /></form>";
    echo "  </td>\n\r";
    
    echo "  <td>\n\r";
    if($product_list['donation'] == 1) {
      $price = $quantity * $cart_item->donation_price;
    } else {
      $price = $quantity * calculate_product_price($product_id, $cart_item->product_variations,'stay',$extras);
      if($product_list['notax'] != 1) {
        $tax += nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']) - $price;
			}
      $all_donations = false;
		}
    
    if($product_list['no_shipping'] != 1) {
      $all_no_shipping = false;
		}
    echo nzshpcrt_currency_display($price, $product_list['notax']);
    $total += $price;
        
    echo "  </td>\n\r";
    $shipping = nzshpcrt_determine_item_shipping($product_id, $number, $_SESSION['delivery_country']);
    $total_shipping += $shipping;
    echo "  <td>\n\r";
    echo "<a href='".get_option('shopping_cart_url').$seperator."remove=".$key."'>Remove</a>";
    echo "  </td>\n\r";
    
    echo "</tr>\n\r";
	}
    
  $siteurl = get_option('siteurl');
  if(($all_donations == false) && ($all_no_shipping == false)) {
    $total_shipping = nzshpcrt_determine_base_shipping($total_shipping, $_SESSION['delivery_country']);
    $total += $total_shipping;
	}
	
 	//Written by allen
	$status = get_product_meta($cart[0]->product_id,'is_membership',true);
	$coupon_info = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'wpsc_coupon_codes WHERE active="1"',ARRAY_A);
	if (($status[0]=='1')||(count($coupon_info)<1)){
	
	} else {
		
		echo "<tr>";
		echo "		<form  method='POST' action='".get_option('shopping_cart_url')."'>";
		echo "		<td>Enter your coupon number:</td>";
		echo "		<td colspan='2' align='left'>";	
		echo "		<input type='text' name='coupon_num' id='coupon_num' value='".$_SESSION['coupon_num']."'>";
		echo "		</td>";
		echo "		<td>";
		echo "		<input type='submit' value='".TXT_WPSC_APPLY."'>";
		echo "		</td>";
		echo "		</form>";
		echo "</tr>";
	}
	
	//End of written by allen
    
  if((get_option('do_not_use_shipping') != 1) && (get_option('base_country') != null))
    {
	//if (!function_exists('getdistance')) {
		if (get_option("payment_gateway")!='google') {
		echo "<tr class='product_shipping'>\n\r";
		echo "  <td colspan='2'>\n\r";
	
	  
	?>
	
      <h2><?php echo TXT_WPSC_SHIPPING_COUNTRY; ?></h2>
    <?php
	  
    echo "  </td>\n\r";
    echo "  <td colspan='2' style='vertical-align: middle;'>";
    echo "</td>\n\r";
    echo "</tr>\n\r";
	   }

    echo "<tr class='total_price'>\n\r";
    echo "  <td colspan='2' >\n\r";
    if (get_option('payment_gateway')=='google') {
	    echo TXT_WPSC_POSTAGE.":";
    } else {
	    
    
    ?>
    <div class='select_country'>
      <form name='change_country' action='' method='POST'>
      <?php
      echo wpsc_shipping_country_list($_SESSION['delivery_country'], $_SESSION['selected_region']);
      ?>
      </form>
    </div>
    <?php
    }
    echo "  </td>\n\r";
    echo "  <td  colspan='2' style='vertical-align: middle;'>\n\r";
    if($all_donations == false)
      {
      echo "" . nzshpcrt_currency_display($total_shipping, 1) . "";
      }
      else
        {
        echo TXT_WPSC_DONATION_SHIPPING;
        }
    echo "  </td>\n\r";
    echo "</tr>\n\r";
	}
  //  }
    
  //echo "<tr style='total-price'>\n\r";
  if($tax > 0)
    {
    echo "<tr class='total_price'>\n\r";
    echo "  <td colspan='2'>\n\r";
    echo "".TXT_WPSC_TAX.":";
    echo "  </td>\n\r";
    echo "  <td colspan='2' id='checkout_tax' style='vertical-align: middle;'>\n\r";
    echo "" . nzshpcrt_currency_display($tax, 1) . "";
    echo "  </td>\n\r";
    echo "</tr>\n\r";
    $total += $tax;
    }
 
if(!empty($_SESSION['coupon_num'])) {
	$discount = $total - nzshpcrt_apply_coupon($total,$_SESSION['coupon_num']) ;
	$total_after_discount = $total-$discount;
}

	if ($_SESSION['coupon_num']) {
		echo "<tr class='total_price'>\n\r";
		echo "  <td colspan='2'>\n\r";
		echo "".TXT_WPSC_DISCOUNT.":";
		echo "  </td>\n\r";
		echo "  <td colspan='2' style='vertical-align: middle;'>\n\r";
		if ($discount > 0) {
			echo "" . nzshpcrt_currency_display($discount, 1) . "";
		} else {
			echo "<font color='red'>".TXT_WPSC_INVALID_COUPON."</font>";
			$_SESSION['coupon_num'] = '';
		}
		echo "  </td>\n\r";
		echo "</tr>\n\r";
	}
		
  echo "<tr class='total_price'>\n\r";
  echo "  <td colspan='2'>\n\r";
  echo "".TXT_WPSC_TOTALPRICE.":";
  echo "  </td>\n\r";
  echo "  <td colspan='2' id='checkout_total' style='vertical-align: middle;'>\n\r";
  echo nzshpcrt_overall_total_price($_SESSION['selected_country'],true,false,$total);
  echo "  </td>\n\r";
  echo "</tr>\n\r";
  echo "</table>";
  if ($_POST['coupon_num']) {
  	$_SESSION['nzshpcrt_totalprice'] = $total_after_discount;
  } else {
  	$_SESSION['nzshpcrt_totalprice'] = $total;
  }
  
 if (get_option('payment_gateway') == 'google') { 
	 $google_cart = unserialize($_SESSION['google_shopping_cart']);
	 if($_SESSION['coupon_num']){
		 $overall_total = nzshpcrt_overall_total_price_numeric(null,true);
		 $discount = $overall_total - nzshpcrt_apply_coupon($overall_total,$_SESSION['coupon_num']);
		 $total_after_discount = $overall_total-$discount;
		 $_SESSION['wpsc_discount']= $discount;
	 } else {
		 $_SESSION['wpsc_discount']= 0;
	 }
	 if ($discount > 0) {
		 $google_item = new GoogleItem(utf8_decode("Coupon Code: '".$_SESSION['coupon_num']."'"),      // Item name
				 utf8_decode("A coupon redeem"), // Item      description
				 1, // Quantity
				 -$discount); // Unit price
		//echo serialize($cart_item->product_variations);
		$google_item->SetMerchantPrivateItemData("Coupon Deduction");
		$google_cart->AddItem($google_item);
	 }
	 //exit("---><pre>".print_r($_SESSION,1)."</pre>");
	 if (get_option('payment_gateway') == 'google') {
		 if (get_option('google_button_size') == '0'){
			 $google_button_size = 'BIG';
		 } elseif(get_option('google_button_size') == '1') {
			 $google_button_size = 'MEDIUM';
		 } elseif(get_option('google_button_size') == '2') {
			 $google_button_size = 'SMALL';
		 }
	 }
	 echo "<br>".$google_cart->CheckoutButtonCode($google_button_size);
 } else {
	  echo "<h2>".TXT_WPSC_ENTERDETAILS."</h2>";
 } 
include('checkout.php');
  } else {
  echo TXT_WPSC_NOITEMSINTHESHOPPINGCART;
  }
?>
</div>