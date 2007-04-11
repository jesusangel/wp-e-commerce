<?php
global $wpdb,$gateway_checkout_form_fields;
$_SESSION['cart_paid'] = false;
$checkout = $_SESSION['checkoutdata'];
if(get_option('permalink_structure') != '')
   {
   $seperator ="?";
    }
    else
       {
       $seperator ="&amp;";
       }
$currenturl = get_option('checkout_url') . $seperator .'total='.$_GET['total'];
if(!isset($_GET['result']))
  {
?>
<div class="wrap">
<strong><?php echo TXT_WPSC_CONTACTDETAILS;?></strong><br />
<?php
 echo TXT_WPSC_CREDITCARDHANDY;
 echo "<br /><br />";
echo TXT_WPSC_ASTERISK;
if($_SESSION['nzshpcrt_checkouterr'] != null)
  {
  echo "<br /><span style='color: red;'>".$_SESSION['nzshpcrt_checkouterr']."</span>";
  $_SESSION['nzshpcrt_checkouterr'] = '';
  }
?>
 <table>
 <form action='<?php echo  $currenturl;?>' method='POST'><?php
  $form_sql = "SELECT * FROM `".$wpdb->prefix."collect_data_forms` WHERE `active` = '1' ORDER BY `order`;";
  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
  //exit("<pre>".print_r($form_data,true)."</pre>");
  foreach($form_data as $form_field)
    {
    if($form_field['type'] == 'heading')
      {
      echo "
      <tr>
        <td colspan='2'>\n\r";
      echo "<strong>".$form_field['name']."</strong>";        
      echo "
        </td>
      </tr>\n\r";
      }
      else
        {
        echo "
        <tr>
          <td>\n\r";
        echo $form_field['name'];
        if($form_field['mandatory'] == 1)
          {
          if(!(($form_field['type'] == 'country') || ($form_field['type'] == 'delivery_country')))
            {
            echo "*";
            }
          }
        echo "
          </td>\n\r
          <td>\n\r";
        switch($form_field['type'])
          {
          case "text":
          case "city":
          case "delivery_city":
          echo "<input type='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
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
          case "delivery_country":
          $country_name = $wpdb->get_var("SELECT `country` FROM `".$wpdb->prefix."currency_list` WHERE `isocode`='".$_SESSION['selected_country']."' LIMIT 1");
          echo "<input type='hidden' name='collected_data[".$form_field['id']."]' value='".$_SESSION['selected_country']."'>".$country_name." ";
          //echo "<select name='collected_data[".$form_field['id']."]'>".nzshpcrt_country_list($_SESSION['collected_data'][$form_field['id']])."</select>";
          break;
          
          case "email":
          echo "<input type='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
          break;
          
          default:
          echo "<input type='text' value='".$_SESSION['collected_data'][$form_field['id']]."' name='collected_data[".$form_field['id']."]' />";
          break;
          }
        echo "
          </td>
        </tr>\n\r";
        }
    }
?>
    <?php
    if(isset($gateway_checkout_form_fields))
      {
      echo $gateway_checkout_form_fields;
      }
    $termsandconds = get_option('terms_and_conditions');
    if($termsandconds != '')
      {
      ?>
    <tr>
      <td>
      </td>
      <td>
      <input type='checkbox' value='yes' name='agree' /> <?php echo TXT_WPSC_TERMS1;?><a target='_blank' href='' class='termsandconds' onclick='window.open("<?php
      echo get_option('siteurl')."?termsandconds=true";
       ?>","","width=550,height=600,scrollbars,resizable"); return false;'><?php echo TXT_WPSC_TERMS2;?></a>
      </td>
    </tr>
      <?php
      }
      else
        {
        echo "<input type='hidden' value='yes' name='agree' />";
        echo "";
        }
    if(get_option('payment_method') == 2)
      {
      $curgateway = get_option('payment_gateway');
      foreach($GLOBALS['nzshpcrt_gateways'] as $gateway)
        {
        if($gateway['internalname'] == $curgateway )
          {
          $gateway_name = $gateway['name'];
          }
        }
      ?>
      <tr>
        <td colspan="2">
        <strong>Payment Method</strong>
        </td>
      </tr>
      
      <tr>
        <td colspan='2'>
        <input type='radio' name='payment_method' value='1' id='payment_method_1' checked='true'>
        <label for='payment_method_1'><?php echo TXT_WPSC_PAY_USING;?> <?php echo $gateway_name; ?>/<?php echo TXT_WPSC_CREDIT_CARD;?></label>
        </td>
      </tr>
      
      <tr>
        <td colspan='2'>
        <input type='radio' name='payment_method' value='2' id='payment_method_2'>
        <label for='payment_method_2'><?php echo TXT_WPSC_PAY_MANUALLY;?></label>
        </td>
      </tr>
      <?php
      }
    ?>
    <tr>
      <td>
      </td>
      <td>
      <input type='hidden' value='true' name='submitwpcheckout' />
      <input type='submit' value='<?php echo TXT_WPSC_MAKEPURCHASE;?>' name='submit' />
      </td>
    </tr>
</table>
</form>
</div>
<?php
  }
  else
    {
    echo TXT_WPSC_BUYPRODUCTS;
    }
  ?> 