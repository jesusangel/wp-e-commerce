<?php
function nzshpcrt_shopping_basket($input = null, $override_state = null) {
  global $wpdb;
  
  if(is_numeric($override_state)) {
    $state = $override_state;
	} else {
		$state = get_option('cart_location');
	}
  
  if($state == 1) {
    if($input != '') {
      $cart = $_SESSION['nzshpcrt_cart'];
      echo "<div id='sideshoppingcart'><div id='shoppingcartcontents'>";
      echo nzshpcrt_shopping_basket_internals($cart);
      echo "</div></div>";
		}
  } else if(($state == 3) || ($state == 4)) {
		$cart = $_SESSION['nzshpcrt_cart'];
		if($state == 4) {
			#echo $input;
			echo "<div id='widgetshoppingcart'><div id='shoppingcartcontents'>";
			echo nzshpcrt_shopping_basket_internals($cart,false,true);
			echo "</div></div>";
			$dont_add_input = true;
		} else {
			echo "<div id='sideshoppingcart'><div id='shoppingcartcontents'>";
			echo nzshpcrt_shopping_basket_internals($cart);
			echo "</div></div>";
		}
	} else {
		if(($GLOBALS['nzshpcrt_activateshpcrt'] === true)) {
			$cart = $_SESSION['nzshpcrt_cart'];
			echo "<div id='shoppingcart'><div id='shoppingcartcontents'>";
			echo nzshpcrt_shopping_basket_internals($cart);
			echo "</div></div>";
		}
	}
  
	return $input;
}
  

function nzshpcrt_shopping_basket_internals($cart,$quantity_limit = false, $no_title=false) {
  global $wpdb;
  		echo "    <div id='sliding_cart' class='shopping-cart-wrapper'>";
//     if(get_option('wpsc_use_theme_engine') == TRUE) {	    
			include_once(WPSC_FILE_PATH . "/themes/".WPSC_THEME_DIR."/cart_widget.php");
    /*
	  } else {
			nzshpcrt_shopping_basket("", 4);	  
	  }*/
		echo "    </div>";
  return $output;
  }
  
function wpsc_country_region_list($form_id = null, $ajax = false , $selected_country = null, $selected_region = null, $supplied_form_id = null) {
  global $wpdb;
  if($selected_country == null) {
    $selected_country = get_option('base_country');
	}
  if($selected_region == null) {
    $selected_region = get_option('base_region');
	}
  if($form_id != null) {
    $html_form_id = "region_country_form_$form_id";
	} else {
		$html_form_id = 'region_country_form';
	}
	if($supplied_form_id != null) {
	  $supplied_form_id = "id='$supplied_form_id'";
	}
	
  $country_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CURRENCY_LIST."` ORDER BY `country` ASC",ARRAY_A);
  $output .= "<div id='$html_form_id'>\n\r";
  $output .= "<select $supplied_form_id name='collected_data[".$form_id."][0]' class='current_country' onchange='set_billing_country(\"$html_form_id\", \"$form_id\");' >\n\r";
  foreach ($country_data as $country) {
    $selected ='';
   if($country['visible'] == '1'){
  
		if($selected_country == $country['isocode']) {
		  $selected = "selected='selected'";
			}
		$output .= "<option value='".$country['isocode']."' $selected>".htmlentities($country['country'])."</option>\n\r";
		}  
	}

  $output .= "</select>\n\r";
  
  
  $region_list = $wpdb->get_results("SELECT `".WPSC_TABLE_REGION_TAX."`.* FROM `".WPSC_TABLE_REGION_TAX."`, `".WPSC_TABLE_CURRENCY_LIST."`  WHERE `".WPSC_TABLE_CURRENCY_LIST."`.`isocode` IN('".$selected_country."') AND `".WPSC_TABLE_CURRENCY_LIST."`.`id` = `".WPSC_TABLE_REGION_TAX."`.`country_id`",ARRAY_A) ;
    $output .= "<div id='region_select_$form_id'>";
    if($region_list != null) {
      $output .= "<select name='collected_data[".$form_id."][1]' class='current_region' onchange='set_billing_country(\"$html_form_id\", \"$form_id\");'>\n\r";
      //$output .= "<option value=''>None</option>";
      foreach($region_list as $region) {
        if($selected_region == $region['id']) {
          $selected = "selected='selected'";
				} else {
					$selected = "";
				}
        $output .= "<option value='".$region['id']."' $selected>".htmlentities($region['name'])."</option>\n\r";
			}
      $output .= "</select>\n\r";
		}
  $output .= "</div>";
  $output .= "</div>\n\r";
  return $output;
}
?>