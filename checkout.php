<?php
global $wpdb,$gateway_checkout_form_fields, $user_ID;
$_SESSION['cart_paid'] = false;


$saved_data_sql = "SELECT * FROM `".$wpdb->prefix."usermeta` WHERE `user_id` = '".$user_ID."' AND `meta_key` = 'wpshpcrt_usr_profile';";
$saved_data = $wpdb->get_row($saved_data_sql,ARRAY_A);
$meta_data = unserialize($saved_data['meta_value']);

if(!isset($_SESSION['collected_data']) || ($_SESSION['collected_data'] == null))
  {
  $_SESSION['collected_data'] = $meta_data;
  }
  else
    {
    foreach($_SESSION['collected_data'] as $form_key => $session_form_data)
      {
      if($session_form_data == null)
        {
        $_SESSION['collected_data'][$form_key] = $meta_data[$form_key];
        }
      }
    }

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
if(get_option('permalink_structure') == '')
  {
  $currenturl = str_replace(trailingslashit(get_option('siteurl')).'?',trailingslashit(get_option('siteurl')) . 'index.php?', $currenturl);
  } 
if(!isset($_GET['result']))
  {
?>
<div class="wrap wpsc_container">
<strong><?php echo TXT_WPSC_CONTACTDETAILS;?></strong><br />
<?php
 echo TXT_WPSC_CREDITCARDHANDY;
 if(!is_numeric($user_ID) && ($user_ID < 1) && get_settings('users_can_register'))
   {
   echo " ".TXT_WPSC_IF_USER_CHECKOUT."<a href='#' onclick='jQuery(\"#checkout_login_box\").slideToggle(\"fast\"); return false;'>".TXT_WPSC_LOG_IN."</a>";
   echo "<div id='checkout_login_box'>";
   ?>
<form name="loginform" id="loginform" action="wp-login.php" method="post">
  <label>Username:<br /><input type="text" name="log" id="log" value="" size="20" tabindex="1" /></label><br />
  <label>Password:<br /> <input type="password" name="pwd" id="pwd" value="" size="20" tabindex="2" /></label>
  <input type="submit" name="submit" id="submit" value="Login &raquo;" tabindex="4" />
  <input type="hidden" name="redirect_to" value="<?php echo get_option('checkout_url'); ?>" />
</form>
   <?php 
   echo "<a class='thickbox' rel='".TXT_WPSC_REGISTER."' href='".$siteurl."?ajax=true&amp;action=register&amp;width=360&amp;height=300' >".TXT_WPSC_REGISTER."</a>";
   echo "</div>";
   }
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
          echo wpsc_country_region_list($form_field['id'] , false, $_SESSION['selected_country'], $_SESSION['selected_region']);
          /*
          $country_name = $wpdb->get_var("SELECT `country` FROM `".$wpdb->prefix."currency_list` WHERE `isocode`='".$_SESSION['selected_country']."' LIMIT 1");
          if($_SESSION['delivery_country'] != '') {
            $delivery_country = $_SESSION['delivery_country'];
            }
          //$_SESSION['collected_data'][$form_field['id']]
          echo "<select name='collected_data[".$form_field['id']."]' onchange='set_delivery_country(this.options[this.selectedIndex].value);'>".nzshpcrt_country_list($delivery_country)."</select>";
          */
          break;
          
          case "delivery_country":          
          $country_name = $wpdb->get_var("SELECT `country` FROM `".$wpdb->prefix."currency_list` WHERE `isocode`='".$_SESSION['delivery_country']."' LIMIT 1");
          echo "<input type='hidden' name='collected_data[".$form_field['id']."]' value='".$_SESSION['delivery_country']."'>".$country_name." ";
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
      <input type='checkbox' value='yes' name='agree' /> <?php echo TXT_WPSC_TERMS1;?><a class='thickbox' target='_blank' href='<?php
      echo get_option('siteurl')."?termsandconds=true&amp;width=360&amp;height=400'"; ?>' class='termsandconds'><?php echo TXT_WPSC_TERMS2;?></a>
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