<?php
global $wpdb;
$curgateway = get_option('payment_gateway');
$sessionid = $_GET['sessionid'];
$errorcode = '';
$transactid = '';
//$cart = $_SESSION['nzshpcrt_cart'];
if($sessionid != null)
  {
  $message = TXT_WPSC_EMAILMSG1;
  
  $message_html = $message;

  $report = TXT_WPSC_EMAILMSG2;

  $selectsql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `sessionid`= ".$sessionid." LIMIT 1";
  $check = $wpdb->get_results($selectsql,ARRAY_A) ;


  $cartsql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`=".$check[0]['id']."";
  $cart = $wpdb->get_results($cartsql,ARRAY_A); 
  
  
          
                           
  if($check[0]['shipping_country'] != '')
    {
    $country = $check[0]['shipping_country'];
    }
    else
      {
      $country = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id`=".$check[0]['id']." AND `form_id` = '".get_option('country_form_field')."' LIMIT 1",ARRAY_A);
      $country = $country[0]['value'];
      }
  
  // gets first email address from checkout details
  $email_form_field = $wpdb->get_results("SELECT `id`,`type` FROM `".$wpdb->prefix."collect_data_forms` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1",ARRAY_A);
  $email_address = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id`=".$check[0]['id']." AND `form_id` = '".$email_form_field[0]['id']."' LIMIT 1",ARRAY_A);
  $email = $email_address[0]['value'];
  }

$siteurl = get_option('siteurl');
  
$previous_download_ids = Array(0);  
  
if($cart != null && ($errorcode == 0))
  {
  foreach($cart as $row)
     {
     $link ="";
     $productsql= "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=".$row['prodid']."";
     $product_data = $wpdb->get_results($productsql,ARRAY_A) ;
    //      if($product_data[0]['quantity_limited'] == 1)
    //        {
    //        $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `quantity`='".($product_data[0]['quantity']-$row['quantity'])."' WHERE `id`='".$product_data[0]['id']."' LIMIT 1");
    //        }
     if($product_data[0]['file'] > 0)
       {
       $wpdb->query("UPDATE `".$wpdb->prefix."download_status` SET `active`='1' WHERE `fileid`='".$product_data[0]['file']."' AND `purchid` = '".$check[0]['id']."' LIMIT 1");
       $download_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."download_status` WHERE `fileid`='".$product_data[0]['file']."' AND `purchid`='".$check[0]['id']."' AND `id` NOT IN (".make_csv($previous_download_ids).") LIMIT 1",ARRAY_A);
       $download_data = $download_data[0];
       $link = $siteurl."?downloadid=".$download_data['id'];
       $previous_download_ids[] = $download_data['id'];
       }
       
     $shipping = nzshpcrt_determine_item_shipping($row['prodid'], $row['quantity'], $country);
      
      
     //echo nzshpcrt_currency_display(($number * $shipping), 1);
     $total_shipping += $shipping;
     if($product_data[0]['special']==1)
       {
       $price_modifier = $product_data[0]['special_price'];
       }
       else
         {
         $price_modifier = 0;
         }
     
     $total += ($row['price']*$row['quantity']);
     $message_price = nzshpcrt_currency_display(($row['price']*$row['quantity']), $product_data[0]['notax'], true);
     $shipping_price  = nzshpcrt_currency_display($shipping, 1, true);
     
     $variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$row['id']."'";
     $variation_data = $wpdb->get_results($variation_sql,ARRAY_A); 
     //echo("<pre>".print_r($variation_data,true)."</pre>");
     $variation_count = count($variation_data);
     if($variation_count > 1)
        {
        $variation_list = " (";
        $i = 0;
        foreach($variation_data as $variation)
          {
          if($i > 0)
            {
            $variation_list .= ", ";
            }
          $value_id = $variation['venue_id'];
          $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          $variation_list .= $value_data[0]['name'];              
          $i++;
          }
        $variation_list .= ")";
        }
        else if($variation_count == 1)
          {
          $value_id = $variation_data[0]['venue_id'];
          $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
          $variation_list = " (".$value_data[0]['name'].")";
          }
          else
            {
            $variation_list = '';
            }
     
      if($link != '')
        {
        $message .= " - ". $product_data[0]['name'] . $variation_list ."  ".$message_price ."  ".TXT_WPSC_CLICKTODOWNLOAD.": $link\n";
        $message_html .= " - ". $product_data[0]['name'] . $variation_list ."  ".$message_price ."&nbsp;&nbsp;<a href='$link'>".TXT_WPSC_DOWNLOAD."</a>\n";
        }
        else
          {
          $plural = '';
          if($row['quantity'] > 1)
            {
            $plural = "s";
            }
          $message .= " - ".$row['quantity']." ". $product_data[0]['name'].$variation_list ."  ". $message_price ."\n - ". TXT_WPSC_SHIPPING.":".$shipping_price ."\n\r";
          $message_html .= " - ".$row['quantity']." ". $product_data[0]['name'].$variation_list ."  ". $message_price ."\n - ". TXT_WPSC_SHIPPING.":".$shipping_price ."\n\r";
          }

      $report .= " - ". $product_data[0]['name'] ."  ".$message_price ."\n";
     }
     
     
  $total_shipping = nzshpcrt_determine_base_shipping($total_shipping, $country);
  $message .= "\n\r";
  $message .= "Total Shipping: ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
  $message .= "Total: ".nzshpcrt_currency_display(($total+$total_shipping),1,true)."\n\r";
  
  
  $message_html .= "\n\r";
  $message_html .= "Total Shipping: ".nzshpcrt_currency_display($total_shipping,1,true)."\n\r";
  $message_html .= "Total: ".nzshpcrt_currency_display(($total+$total_shipping),1,true)."\n\r";
     
  if(isset($_GET['ti']))
    {
    $message .= "\n\r".TXT_WPSC_YOURTRANSACTIONID.": " . $_GET['ti'];
    $message_html .= "\n\r".TXT_WPSC_YOURTRANSACTIONID.": " . $_GET['ti'];
    $report .= "\n\r".TXT_WPSC_TRANSACTIONID.": " . $_GET['ti'];
    }
  if($email != '')
    {
    mail($email, TXT_WPSC_PURCHASERECEIPT, $message, "From: ".get_option('return_email')."");
    }
  
  $purch_sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `id`!='".$check[0]['id']."'";
  $purch_data = $wpdb->get_results($purch_sql,ARRAY_A) ; 

  $report_user = TXT_WPSC_CUSTOMERDETAILS."\n\r";
  
  


  $form_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id` = '".$check[0]['id']."'";
  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
  if($form_data != null)
    {
    foreach($form_data as $form_field)
      {
      $form_sql = "SELECT * FROM `".$wpdb->prefix."collect_data_forms` WHERE `id` = '".$form_field['form_id']."' LIMIT 1";
      $form_data = $wpdb->get_results($form_sql,ARRAY_A);
      $form_data = $form_data[0];
      if($form_data['type'] == 'country' )
        {
        $report_user .= $form_data['name'].": ".get_country($form_field['value'])."\n";
        }
        else
          {
          $report_user .= $form_data['name'].": ".$form_field['value']."\n";
          }
      }
    }
  
  $report_user .= "\n\r";
  $report = $report_user . $report;
  if(get_option('purch_log_email') != null)
    {
    mail(get_option('purch_log_email'), TXT_WPSC_PURCHASEREPORT, $report, "From: ".get_option('return_email')."");
    }
  $_SESSION['nzshpcrt_cart'] = '';
  $_SESSION['nzshpcrt_cart'] = Array();
  
  echo '<div class="wrap">';
  if($sessionid != null)
    {
    echo TXT_WPSC_THETRANSACTIONWASSUCCESSFUL."<br />";
    echo "<br />" . nl2br(str_replace("$",'\$',$message_html));
    }
  echo '</div>';
  }
  else
    {
    echo '<div class="wrap">';
    echo TXT_WPSC_BUYPRODUCTS;
    echo '</div>';
    }   
  

if($check != null)
  {
  $sql = "UPDATE `".$wpdb->prefix."purchase_logs` SET `statusno` = '".$errorcode."',`transactid` = '".$transactid."',`authcode` = '".$authcode."',`date` = '".time()."' WHERE `sessionid` = ".$sessionid." LIMIT 1";
   $wpdb->query($sql) ;
   }
     
?>