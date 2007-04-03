<?php
/*

       
       <option value='paypal' <?php echo $paypal; ?>>PayPal</option>
       <option value='testmode' <?php echo $testmode; ?>>Test Mode</option>
*/
$changes_made = false;
if(($_POST['payment_gw'] != null) && ($_POST['submit_details'] == null))
  {
  update_option('payment_gateway', $_POST['payment_gw']);
  $changes_made = true;
  }
$curgateway = get_option('payment_gateway');
 
if(($_POST['payment_gw'] != null) && ($_POST['submit_details'] == "Submit"))
  {
  foreach($nzshpcrt_gateways as $gateway)
    {
    if($gateway['internalname'] == $curgateway )
      {
      $gateway['submit_function']();
      $changes_made = true;
      }
    }
  }

if($changes_made == true)
  {
  echo "<div class='updated'><p align='center'>".TXT_WPSC_THANKSAPPLIED."</p></div>";
  }

$form = "";
foreach($nzshpcrt_gateways as $gateway)
  {
  if($gateway['internalname'] == $curgateway )
    {
    $selected = " selected='selected'";
    $form = $gateway['form']();
    }
    else
      {
      $selected = '';
      }
  $gatewaylist .="<option value='".$gateway['internalname']."' ".$selected." >".$gateway['name']."</option>"; 
  }
$gatewaylist = "<option value='".$nogw."'>".TXT_WPSC_PLEASESELECTAPAYMENTGATEWAY."</option>" . $gatewaylist;
?>
<script language='JavaScript' type='text/javascript'>
function selectgateway()
  {
  document.forms.gatewayopt.submit()
  }
</script>
<div class="wrap">
  <h2><?php echo TXT_WPSC_GATEWAY_OPTIONS;?></h2>
  <form name='gatewayopt' method='POST'>
  <table>
    <tr>
      <td>
      <?php echo TXT_WPSC_PAYMENTGATEWAY2;?>
      </td>
      <td>
      <select name='payment_gw' onChange='selectgateway();'>
      <?php
      echo $gatewaylist;
      ?>
      </select>
      </td>
    </tr><?php
echo $form;
  ?><tr>
      <td>
      </td>
      <td>
      <input type='submit' value='<?php echo TXT_WPSC_SUBMIT;?>' name='submit_details' />
      </td>
    </tr>
  </table>
  </form>
  <br />
<?php echo TXT_WPSC_PAYMENTGATEWAYNOTE;?>
  
  
</div>