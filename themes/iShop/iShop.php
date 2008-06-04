<?php
function wpsc_theme_html($product) {
  $siteurl = get_option('siteurl');
  $wpsc_theme['html'] ="<input type='image' src='".$siteurl."/wp-content/plugins/wp-shopping-cart/themes/iShop/images/buy_button.gif' id='product_".$product['id']."_submit_button' class='wpsc_buy_button' name='Buy' value='".TXT_WPSC_ADDTOCART."'  />";
  return $wpsc_theme;
  }    
?>