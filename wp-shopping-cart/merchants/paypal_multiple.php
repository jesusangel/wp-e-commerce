<?php
$nzshpcrt_gateways[$num]['name'] = 'Paypal';
$nzshpcrt_gateways[$num]['internalname'] = 'paypal_multiple';
$nzshpcrt_gateways[$num]['function'] = 'gateway_paypal_multiple';
$nzshpcrt_gateways[$num]['form'] = "form_paypal_multiple";
$nzshpcrt_gateways[$num]['submit_function'] = "submit_paypal_multiple";

function gateway_paypal_multiple($seperator, $sessionid)
  {
  global $wpdb;
  $purchase_log_sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `sessionid`= ".$sessionid." LIMIT 1";
  $purchase_log = $wpdb->get_results($purchase_log_sql,ARRAY_A) ;

  $cart_sql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`='".$purchase_log[0]['id']."'";
  $cart = $wpdb->get_results($cart_sql,ARRAY_A) ; 
  
  $transact_url = get_option('transact_url');
  // paypal connection variables
  $data['business'] = get_option('paypal_multiple_business');
  $data['return'] = $transact_url.$seperator."sessionid=".$sessionid."&gateway=paypal";
  $data['cancel_return'] = $transact_url;
  $data['notify_url'] = $transact_url;
  $data['rm'] = '2';
  
   // look up the currency codes and local price
  $currency_code = $wpdb->get_results("SELECT `code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".get_option(currency_type)."' LIMIT 1",ARRAY_A);
  $local_currency_code = $currency_code[0]['code'];
  $paypal_currency_code = get_option('paypal_curcode');

  // Stupid paypal only accepts payments in one of 5 currencies. Convert from the currency of the users shopping cart to the curency which the user has specified in their paypal preferences.
  $curr=new CURRENCYCONVERTER();

  
  $data['currency_code'] = $paypal_currency_code;
  $data['Ic'] = 'US';
  $data['bn'] = 'toolkit-php';
  $data['no_shipping'] = '1';
  $data['no_note'] = '1';
  
  switch($paypal_currency_code)
    {
    case "JPY":
    $decimal_places = 0;
    break;
    
    case "HUF":
    $decimal_places = 0;
    break;
    
    default:
    $decimal_places = 2;
    }
  
  $i = 1;
  foreach($cart as $item)
    {
    $product_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".$item['prodid']."' LIMIT 1",ARRAY_A);
    $product_data = $product_data[0];
    //exit("<pre>" . print_r($item,true) ."</pre>");
    $variation_count = count($product_variations);
    
    $variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$item['id']."'";
    $variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
    //exit("<pre>" . print_r($variation_data,true) ."</pre>");
    $variation_count = count($variation_data);
    if($variation_count >= 1)
      {
      $variation_list = " (";
      $j = 0;
      foreach($variation_data as $variation)
        {
        if($j > 0)
          {
          $variation_list .= ", ";
          }
        $value_id = $variation['venue_id'];
        $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
        $variation_list .= $value_data[0]['name'];              
        $j++;
        }
      $variation_list .= ")";
      }
      else
        {
        $variation_list = '';
        }
    
    if($product_data['special']==1)
      {
      $price_modifier = $product_data['special_price'];
      }
      else
        {
        $price_modifier = 0;
        }
    
    $local_currency_productprice = ($product_data['price'] - $price_modifier);
    
    if($product_data['notax'] != 1)
      {
      $local_currency_productprice = nzshpcrt_calculate_tax($local_currency_productprice, $_SESSION['selected_country'], $_SESSION['selected_region']);
      }
    
    $local_currency_shipping = nzshpcrt_determine_item_shipping($item['prodid'], $item['quantity'], $_SESSION['selected_country']);
    if($paypal_currency_code != $local_currency_code)
      {
      $paypal_currency_productprice = $curr->convert($local_currency_productprice,$paypal_currency_code,$local_currency_code);
      $paypal_currency_shipping = $curr->convert($local_currency_shipping,$paypal_currency_code,$local_currency_code);
      //exit("bad");
      }
      else
        {
        $paypal_currency_productprice = $local_currency_productprice;
        $paypal_currency_shipping = $local_currency_shipping;
        //exit("good");
        }
    $data['item_name_'.$i] = $product_data['name'].$variation_list;
    $data['amount_'.$i] = number_format(sprintf("%01.2f", $paypal_currency_productprice),$decimal_places,'.','');
    $data['quantity_'.$i] = $item['quantity'];
    $data['item_number_'.$i] = $product_data['id'];
    //exit($paypal_currency_shipping);
    $data['shipping_'.$i] = number_format($paypal_currency_shipping,$decimal_places,'.','');
    $data['handling_'.$i] = '';
    $i++;
    }
  
  $data['tax'] = '';
  
  $base_shipping = nzshpcrt_determine_base_shipping(0, $_SESSION['selected_country']);
  if($base_shipping >= 1)
    {
    $data['item_name_'.$i] = "Shipping";
    $data['amount_'.$i] = number_format(0,$decimal_places,'.','');
    $data['quantity_'.$i] = 1;
    $data['item_number_'.$i] = 0;
    $data['shipping_'.$i] = number_format($base_shipping,$decimal_places,'.','');
    $data['handling_'.$i] = '';
    }
    
  
  
  $data['custom'] = '';
  $data['invoice'] = $sessionid;
  
  // User details
  /*
  $data['first_name'] = $_POST['firstname'];
  $data['last_name'] = $_POST['lastname'];
  */
  
  $address_data = $wpdb->get_results("SELECT `id`,`type` FROM `".$wpdb->prefix."collect_data_forms` WHERE `type` IN ('address','delivery_address') AND `active` = '1'",ARRAY_A);
  foreach((array)$address_data as $address)
    {
    $data['address1'] = $_POST['collected_data'][$address['id']];
    if($address['type'] == 'delivery_address')
      {
      break;
      }
    }
  
  $city_data = $wpdb->get_results("SELECT `id`,`type` FROM `".$wpdb->prefix."collect_data_forms` WHERE `type` IN ('city','delivery_city') AND `active` = '1'",ARRAY_A);
  foreach((array)$city_data as $city)
    {
    $data['city'] = $_POST['collected_data'][$city['id']];
    if($city['type'] == 'delivery_city')
      {
      break;
      }
    }
  $country_data = $wpdb->get_results("SELECT `id`,`type` FROM `".$wpdb->prefix."collect_data_forms` WHERE `type` IN ('country','delivery_country') AND `active` = '1'",ARRAY_A);
  foreach((array)$country_data as $country)
    {
    $data['country'] = $_POST['collected_data'][$country['id']];
    if($address['type'] == 'delivery_country')
      {
      break;
      }
    }
  //$data['country'] = $_POST['address'];
  
  // Change suggested by waxfeet@gmail.com, if email to be sent is not there, dont send an email address
  if($_POST['collected_data'][get_option('email_form_field')] != null)
    {
    $data['email'] = $_POST['collected_data'][get_option('email_form_field')];
    }
    
  $email_data = $wpdb->get_results("SELECT `id`,`type` FROM `".$wpdb->prefix."collect_data_forms` WHERE `type` IN ('email') AND `active` = '1'",ARRAY_A);
  foreach((array)$email_data as $email)
    {
    $data['email'] = $_POST['collected_data'][$email['id']];
    }
  $data['upload'] = '1';
  $data['cmd'] = "_ext-enter";
  $data['redirect_cmd'] = "_cart";
  $datacount = count($data);
  $num = 0;
  foreach($data as $key=>$value)
    {
    $amp = '&';
    $num++;
    if($num == $datacount)
      {
      $amp = '';
      }
    $output .= $key.'='.urlencode($value).$amp;
    }
  //exit("<pre>" . print_r($_POST,true) ."</pre>"); 
  //exit("<pre>" . print_r($_SESSION,true) ."</pre>");
  //exit("<pre>" . print_r($data,true) ."</pre>");
  header("Location: ".get_option('paypal_multiple_url')."?".$output);
  exit();
  }

function submit_paypal_multiple()
  {
  update_option('paypal_multiple_business', $_POST['paypal_multiple_business']);
  update_option('paypal_multiple_url', $_POST['paypal_multiple_url']);
  update_option('paypal_curcode', $_POST['paypal_curcode']);
  return true;
  }

function form_paypal_multiple()
  {
  $select_currency[get_option('paypal_curcode')] = "selected='true'";
  $output = "
  <tr>
      <td>
      PayPal Username
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_business')."' name='paypal_multiple_business' />
      </td>
  </tr>
  <tr>
      <td>
      PayPal Url
      </td>
      <td>
      <input type='text' size='40' value='".get_option('paypal_multiple_url')."' name='paypal_multiple_url' />
      </td>
  </tr>
  <tr>
      <td>
      </td>
      <td>
      <strong>Note:</strong>The URL to use for the paypal gateway is: https://www.paypal.com/cgi-bin/webscr
      </td>
   </tr>
  <tr>
      <td>
      PayPal Accepted Currency (e.g. USD, AUD)
      </td>
      <td>
        <select name='paypal_curcode'>
          <option ".$select_currency['USD']." value='USD'>U.S. Dollar</option>
          <option ".$select_currency['CAD']." value='CAD'>Canadian Dollar</option>
          <option ".$select_currency['AUD']." value='AUD'>Australian Dollar</option>
          <option ".$select_currency['EUR']." value='EUR'>Euro</option>
          <option ".$select_currency['GBP']." value='GBP'>Pound Sterling</option>
          <option ".$select_currency['JPY']." value='JPY'>Yen</option>
          <option ".$select_currency['NZD']." value='NZD'>New Zealand Dollar</option>
          <option ".$select_currency['CHF']." value='CHF'>Swiss Franc</option>
          <option ".$select_currency['HKD']." value='HKD'>Hong Kong Dollar</option>
          <option ".$select_currency['SGD']." value='SGD'>Singapore Dollar</option>
          <option ".$select_currency['SEK']." value='SEK'>Swedish Krona</option>
          <option ".$select_currency['HUF']." value='HUF'>Hungarian Forint</option>
          <option ".$select_currency['DKK']." value='DKK'>Danish Krone</option>
          <option ".$select_currency['PLN']." value='PLN'>Polish Zloty</option>
          <option ".$select_currency['NOK']." value='NOK'>Norwegian Krone</option>
          <option ".$select_currency['CZK']." value='CZK'>Czech Koruna</option>
        </select> 
      </td>
   </tr>";
  return $output;
  }
  ?>