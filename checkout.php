<?php
global $wpdb,$gateway_checkout_form_fields, $gateway_checkout_form_field, $user_ID;
$_SESSION['cart_paid'] = false;


if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."usermeta'")) {
	if(is_numeric($user_ID) && ($user_ID > 0)) {
		$saved_data_sql = "SELECT * FROM `".$wpdb->prefix."usermeta` WHERE `user_id` = '".$user_ID."' AND `meta_key` = 'wpshpcrt_usr_profile';";
		$saved_data = $wpdb->get_row($saved_data_sql,ARRAY_A);
		$meta_data = unserialize($saved_data['meta_value']);
	}
}

if(!isset($_SESSION['collected_data']) || ($_SESSION['collected_data'] == null)) {
  $_SESSION['collected_data'] = $meta_data;
} else {
	foreach($_SESSION['collected_data'] as $form_key => $session_form_data) {
		if($session_form_data == null) {
			$_SESSION['collected_data'][$form_key] = $meta_data[$form_key];
		}
	}
}

$checkout = $_SESSION['checkoutdata'];
if(get_option('permalink_structure') != '') {
	$seperator ="?";
} else {
	$seperator ="&amp;";
}
$currenturl = get_option('checkout_url') . $seperator .'total='.$_GET['total'];
if(get_option('permalink_structure') == '') {
  $currenturl = str_replace(trailingslashit(get_option('siteurl')).'?',trailingslashit(get_option('siteurl')) . 'index.php?', $currenturl);
}
if(!isset($_GET['result'])){
//   if(!(get_option('payment_gateway')=='google')) {
?>
<div class="wrap wpsc_container">
<strong><?php echo TXT_WPSC_CONTACTDETAILS;?></strong><br />
<?php
 echo TXT_WPSC_CREDITCARDHANDY;
 if(!is_numeric($user_ID) && ($user_ID < 1) && get_settings('users_can_register')) {
   echo " ".TXT_WPSC_IF_USER_CHECKOUT."<a href='#' onclick='jQuery(\"#checkout_login_box\").slideToggle(\"fast\"); return false;'>".TXT_WPSC_LOG_IN."</a>";
   echo "<div id='checkout_login_box'>";
   ?>
<form name="loginform" id="loginform" action="<?php echo get_option('siteurl'); ?>/wp-login.php" method="post">
  <label>Username:<br /><input type="text" name="log" id="log" value="" size="20" tabindex="1" /></label><br />
  <label>Password:<br /> <input type="password" name="pwd" id="pwd" value="" size="20" tabindex="2" /></label>
  <input type="submit" name="submit" id="submit" value="Login &raquo;" tabindex="4" />
  <input type="hidden" name="redirect_to" value="<?php echo get_option('shopping_cart_url'); ?>" />
</form>
   <?php 
   echo "<a class='thickbox' rel='".TXT_WPSC_REGISTER."' href='".$siteurl."?ajax=true&amp;action=register&amp;width=360&amp;height=300' >".TXT_WPSC_REGISTER."</a>";
   echo "</div>";
}
echo "<br /><br />";
echo TXT_WPSC_ASTERISK;
if($_SESSION['nzshpcrt_checkouterr'] != null) {
  echo "<br /><span style='color: red;'>".$_SESSION['nzshpcrt_checkouterr']."</span>";
  $_SESSION['nzshpcrt_checkouterr'] = '';
}
?>

<form action='' method='POST' enctype="multipart/form-data">
<table class='wpsc_checkout_table'>

 
 

<?php
  $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' ORDER BY `order`;";
  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
  //exit("<pre>".print_r($form_data,true)."</pre>");
  foreach($form_data as $form_field) {
    if($form_field['type'] == 'heading') {
      
      echo "<tr>\n\r";
      echo "  <td colspan='2'>\n\r";
      echo "    <strong>".$form_field['name']."</strong>\n\r";
      echo "  </td>\n\r";
      echo "</tr>\n\r";
		} else {
			
			echo "<tr>\n\r";
			echo "	<td>\n\r";
			echo $form_field['name'];
			if($form_field['mandatory'] == 1) {
				if(!(($form_field['type'] == 'country') || ($form_field['type'] == 'delivery_country'))) {
					echo "*";
				}
			}
			
			echo "	</td>\n\r";
			echo "	<td>\n\r";
			switch($form_field['type']) {
				case "city":
				if (function_exists('getdistance')) {
					echo "<input onblur='store_list()' id='user_city' type='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
				} else  {
					echo "<input type='text' class='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
				}
				break;

				case "text":
				case "city":
				case "delivery_city":
				case "coupon":
				echo "<input type='text' class='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
				break;

				case "address":
				if (function_exists('getdistance')) {
					echo "<input type='text' class='text' id='user_address' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]'>";
				} else  {
					echo "<textarea class='text' name='collected_data[".$form_field['id']."]'>".$_SESSION['collected_data'][$form_field['id']]."</textarea>";
				}
				break;

				case "address":
				case "delivery_address":
				case "textarea":
				echo "<textarea name='collected_data[".$form_field['id']."]'>".$_SESSION['collected_data'][$form_field['id']]."</textarea>";
				break;
				

				/*
				case "region":
				case "delivery_region":
				echo "<select name='collected_data[".$form_field['id']."]'>".nzshpcrt_region_list($_SESSION['collected_data'][$form_field['id']])."</select>";
				break;
				*/

				case "country":
				echo wpsc_country_region_list($form_field['id'] , false, $_SESSION['selected_country'], $_SESSION['selected_region']);
				break;

				case "delivery_country":
				$country_name = $wpdb->get_var("SELECT `country` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `isocode`='".$_SESSION['delivery_country']."' LIMIT 1");
				echo "<input type='hidden' name='collected_data[".$form_field['id']."]' value='".$_SESSION['delivery_country']."'>".$country_name." ";
				break;

				case "email":
				echo "<input type='text' class='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
				break;

				default:
				echo "<input type='text' class='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
				break;
			}
			echo "	</td>\n\r";
			echo "</tr>\n\r";
		}
	}
    
	$cart = $_SESSION['nzshpcrt_cart'];
  foreach($cart as $key => $product) {
		$product_data = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id` = '{$product->product_id}' LIMIT 1",ARRAY_A);
		$can_have_uploaded_image = get_product_meta($product->product_id,'can_have_uploaded_image',true);
		if ($can_have_uploaded_image[0]=='on'){
			echo "<tr>\n\r";
			echo "  <td colspan='2'>\n\r";
			echo "<h2 style='margin-bottom: 6px;'>".TXT_WPSC_UPLOAD_IMAGE_FOR." ".$product_data['name']."</h2>\n\r";
			echo "<input type='file' name='uploaded_image[$key]' value=''> \n\r";//
			
			echo "  </td>\n\r";
			echo "</tr>\n\r";
		}
  }    


  $gateway_options = get_option('custom_gateway_options');
 
  $google_decrement = 0;
  if(array_search("google",(array)$gateway_options) !== false) {
     $google_decrement = 1;
  }
	if ((count($gateway_options) - $google_decrement) > 1) {
		echo "<tr>\n\r";
		echo "  <td colspan='2'>\n\r";
		echo "    <strong>".TXT_WPSC_SELECTGATEWAY."</strong>\n\r";
		echo "  </td>\n\r";
		echo "</tr>\n\r";
		echo "<tr>\n\r";
		echo "  <td colspan='2'>\n\r";
		foreach ($gateway_options as $option) {
			if($option == 'google') {
			  //skip google
        continue;
			}
		  if($count == 0) {
		    $checked = "checked='true'";
				$gateway_form_css = "";
		  }  else {
				$checked = " ";
				$gateway_form_css = "style='display: none;'";
		  }
			foreach ($GLOBALS['nzshpcrt_gateways'] as $gateway){
				if ($gateway['internalname'] == $option) {
					echo "<div class='custom_gateway'>\n\r";
					echo "  <label><input class='custom_gateway' name='custom_gateway' $checked value='$option' type='radio'>{$gateway['name']}</label>\n\r";
					if(isset($gateway_checkout_form_fields[$gateway['internalname']])) {
					  echo "  <table $gateway_form_css>\n\r";
					  echo $gateway_checkout_form_fields[$gateway['internalname']];
					  echo "  </table>\n\r";
					}
					echo "</div>\n\r";
				}
			}
		  $count++;
		}
		echo "  </td>\n\r";
		echo "</tr>";
	} else {
		foreach ((array)get_option('custom_gateway_options') as $option) {
			foreach ($GLOBALS['nzshpcrt_gateways'] as $gateway){
				if ($gateway['internalname'] == $option) {
					echo "<input name='custom_gateway' value='$option' type='hidden' />";
				}
			}
		}
	}
	//echo "<h5>Test Code</h5><pre>".print_r($gateway_checkout_form_field, true)."</pre>";
	if(isset($gateway_checkout_form_field)) {
		echo $gateway_checkout_form_field;
	}
	$product=$_SESSION['nzshpcrt_cart'][0];
	$engrave = get_product_meta($product->product_id,'engraved',true);
	if ($engrave[0] == true) {
		echo "	<tr>\n\r";
		echo "		<td>\n\r";
		echo "			Engrave text:\n\r";
		echo "		</td>\n\r";
		echo "		<td>\n\r";
		echo "			<input type='text' name='engrave1'>\n\r";
		echo "		</td>\n\r";
		echo "	</tr>\n\r";
		echo "	<tr>\n\r";
		echo "		<td>\n\r";
		echo "		</td>\n\r";
		echo "		<td>\n\r";
		echo "			<input type='text' name='engrave2'>\n\r";
		echo "		</td>\n\r";
		echo "	</tr>\n\r";
	}
	
	if (get_option('display_find_us') == '1') {
		echo "<tr><td>&nbsp;</td></tr><tr>
		<td>".TXT_WPSC_HOW_DID_YOU_FIND_US.":</td>
		<td><select name='how_find_us'>
			<option value='Word of Mouth'>".TXT_WPSC_HOW_DID_YOU_FIND_US_WOM."</option>
			<option value='Advertisement'>".TXT_WPSC_HOW_DID_YOU_FIND_US_ADV."</option>
			<option value='Internet'>".TXT_WPSC_HOW_DID_YOU_FIND_US_INT."</option>
			<option value='Customer'>".TXT_WPSC_HOW_DID_YOU_FIND_US_EC."</option>
		</select></td></tr>";
	}
	
    $termsandconds = get_option('terms_and_conditions');
    if($termsandconds != '') {
      ?>
	<tr>
      <td>
      </td>
      <td>
      <input type='checkbox' value='yes' name='agree' /> <?php echo TXT_WPSC_TERMS1;?><a class='thickbox' target='_blank' href='<?php
      echo get_option('siteurl')."?termsandconds=true&amp;width=360&amp;height=400'"; ?>' class='termsandconds'><?php echo TXT_WPSC_TERMS2;?></a>
      </td>
    </tr>
      <?php
		} else {
			echo "<input type='hidden' value='yes' name='agree' />";
			echo "";
		}
	
	?>
    <tr>
      <?php if((is_user_logged_in() && (get_option('require_register') == 1)) xor (get_option('require_register') == 0)) { ?>
      <td colspan='2'><br />
      <input type='hidden' value='true' name='submitwpcheckout' />
      <input type='submit' value='<?php echo TXT_WPSC_MAKEPURCHASE;?>' name='submit' class='make_purchase' />
	
	
	<?php } else { ?>
      <td colspan='2'>
      <br /><strong><?php echo TXT_WPSC_PLEASE_LOGIN;?></strong><br />
      <?php echo TXT_WPSC_IF_JUST_REGISTERED;?>
      </td>
      <?php } ?>
    </tr>
</table>
</form>
</div>
<?php
//     }
  }
  else
    {
    echo TXT_WPSC_BUYPRODUCTS;
    }
  ?> 