<?php
global $wpdb, $user_ID, $wpsc_shipping_modules;

			$shipping_quotes = null;
			if($_SESSION['quote_shipping_method'] != null) {
			   // use the selected shipping module
			  $shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
			} else {
			  // otherwise select the first one with any quotes
				foreach((array)$custom_shipping as $shipping_module) {
					$_SESSION['quote_shipping_method'] = $shipping_module;
					$shipping_quotes = $wpsc_shipping_modules[$_SESSION['quote_shipping_method']]->getQuote();
					if(count($shipping_quotes) > 0) { // if we have any shipping quotes, break the loop.
					  break;
					}
				}
			}
$shipping_quotes = null;
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
 
if($_POST['coupon_num']) {
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
	
	if ($selected_country == 'US') {
		$region_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."region_tax` WHERE country_id='136'",ARRAY_A);
		$output .= "<select>";
		foreach ($region_data as $region) {
			$output .= "<option>".$region['name']."</option>";
		}
		$output .= "";
		
		$output .= "</select>";
	} else {
		$output .= " ";
	}
	
// 	$output .= "ZipCode:";
if(isset($_POST['zipcode'])) {
		if ($_POST['zipcode']=='') {
			$zipvalue = 'Your Zipcode';
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
			$color = '#999';
		} else {
			$zipvalue = $_POST['zipcode'];
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
			$color = '#000';
		}
	} else if(isset($_SESSION['wpsc_zipcode'])) {
			$zipvalue = $_SESSION['wpsc_zipcode'];
			$color = '#000';
	}
	$output .= " <input type='text' style='color:".$color.";' onclick='if (this.value==\"Your Zipcode\") {this.value=\"\";this.style.color=\"#000\";}' onblur='if (this.value==\"\") {this.style.color=\"#999\"; this.value=\"Your Zipcode\"; }' value='".$zipvalue."' size='10' name='zipcode' id='zipcode'>";
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
	
		if((get_option('base_country') != null) && (get_option('do_not_use_shipping') == 0)) {
			//if (!function_exists('getdistance')) {
		
		
// 				if (get_option("payment_gateway")!='google') {
				echo "<tr class='product_shipping'>\n\r";
				echo "  <td colspan='2'>\n\r";
				echo "<h2>".TXT_WPSC_SHIPPING_COUNTRY."</h2>";
				echo "  </td>\n\r";
				echo "  <td colspan='2' style='vertical-align: middle;'>";
				echo "</td>\n\r";
				echo "</tr>\n\r";
// 				}

				echo "<tr class='total_price'>\n\r";
				echo "  <td colspan='3' >\n\r";
					?>
							<div class='select_country'>
							<form name='change_country' action='' method='POST'>
							<?php
							echo wpsc_shipping_country_list($_SESSION['delivery_country'], $_SESSION['selected_region']);
					?>
							</div>
							<?php
				echo "  </td>\n\r";
				echo "<td>";
				echo "<input type='submit' onclick='' value='Rate'>";
				echo "</td>";
				echo "</form>";
    echo "</tr>\n\r";
		}
	 if(get_option('do_not_use_shipping') == 0) {
			//// usps changes
			$custom_shipping = get_option('custom_shipping_options');
			foreach((array)$custom_shipping as $shipping) {
				foreach ($wpsc_shipping_modules as $available_shipping) {
					if ($shipping == $available_shipping->internal_name) {
						$shipping_quotes[$available_shipping->internal_name] = $available_shipping->getQuote(true);
					}
				}
			}
		
			if(array_search($_SESSION['quote_shipping_method'], $custom_shipping) === false) {
			  unset($_SESSION['quote_shipping_method']);
			}
		//echo ('<pre>'.print_r($_SESSION['quote_shipping_option'],1)."</pre>");
		$_SESSION['uspsQuote']=$shipping_quotes;
		$i=0;
		$shipping_is_selected = false;
		if(($_SESSION['quote_shipping_method'] != null) && ($_SESSION['quote_shipping_option']  != null)) {
			$shipping_is_selected = true;
		}
		foreach ((array)$shipping_quotes as $key1 => $shipping_quote) {
			$shipping_method_name = $wpsc_shipping_modules[$key1]->name;
			echo "<tr><td class='shipping_header' colspan='4'>$shipping_method_name</td></tr>";
			if (empty($shipping_quote)) {
				echo "<tr><td colspan='4'>No Shipping Data available</td></tr>";
			}
			$j=0;
			foreach ((array)$shipping_quote as $quotes) {
				foreach((array)$quotes as $key=>$quote) {
					if($shipping_is_selected == true) {
						if(($_SESSION['quote_shipping_method'] == $key1) && ($_SESSION['quote_shipping_option']  == $key)) {
							$selected = "checked='checked'";
						} else {
							$selected ="";
						}
					} else {
						if (($i == 0) && ($j == 0)) {
							$selected = "checked='checked'";
						} else {
							$selected ="";
						}
					}
					
					echo "<tr><td colspan='2'><label for='{$key1}_{$j}'>".$key."</label></td><td><label for='{$key1}_{$j}'>".nzshpcrt_currency_display($quote,1)."</label></td><td style='text-align:center;'><input type='radio' id='{$key1}_{$j}' $selected onclick='switchmethod(\"$key\", \"$key1\")' value='$quote' name='shipping_method'></td></tr>";
					$j++;
				}
			}
			$i++;
		}
		// usps changes ends
    
  }
    
    
  //echo "<tr style='total-price'>\n\r";
	if($tax > 0) {
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
// 	if (isset($_SESSION['quote_shipping_total'])) {
// 		if ($discount <= 0) 
// 			echo nzshpcrt_currency_display($_SESSION['quote_shipping_total'],1);
// 		else 
// 			echo nzshpcrt_currency_display($_SESSION['quote_shipping_total'] - $discount,1);
// 	} else {
		echo nzshpcrt_overall_total_price($_SESSION['delivery_country'],true,false,$total);
// 	}
	echo "<input id='shopping_cart_total_price' type='hidden' value='".$total."'>";
	

	echo "  </td>\n\r";
	echo "</tr>\n\r";
	echo "</table>";

	if ($_POST['coupon_num']) {
		$_SESSION['nzshpcrt_totalprice'] = $total_after_discount;
	} else {
		$_SESSION['nzshpcrt_totalprice'] = $total;
	}
  
	echo "<h2>".TXT_WPSC_ENTERDETAILS."</h2>";
	include('checkout.php');
} else {
	echo TXT_WPSC_NOITEMSINTHESHOPPINGCART;
}
?>
				</div>