<?php
global $wpdb;
$form_types = Array("text","email","address","city","country","delivery_address","delivery_city","delivery_country","textarea","heading","coupon");


if($_POST != null)
  {
  if($_POST['payment_method'] != null)
    {
    update_option('payment_method', $_POST['payment_method']);
    }


    if($_POST['require_register'] == 1) {
      update_option('require_register', 1);
		} else {
			update_option('require_register', 0);
		}
  }

if($_POST['submit_action'] == 'add')
  {
  if($_POST['form_name'] != null)
    {
    foreach($_POST['form_name'] as $form_id => $form_name) 
      {
      $form_type = $_POST['form_type'][$form_id];
      $form_mandatory = 0;
      if($_POST['form_mandatory'][$form_id] == 1) {  $form_mandatory = 1;  }
      $form_display_log = 0;
      if($_POST['form_display_log'][$form_id] == 1) {  $form_display_log = 1;  }
      $form_order = $_POST['form_order'][$form_id];
      $wpdb->query("UPDATE `".WPSC_TABLE_CHECKOUT_FORMS."` SET `name` = '$form_name', `type` = '$form_type', `mandatory` = '$form_mandatory', `display_log` = '$form_display_log', `order` = '$form_order' WHERE `id` ='".$form_id."' LIMIT 1 ;");
      }
    }
  
  if($_POST['new_form_name'] != null)
    {
    foreach($_POST['new_form_name'] as $form_id => $form_name) 
      {
      $form_type = $_POST['new_form_type'][$form_id];
      $form_mandatory = 0;
      if($_POST['new_form_mandatory'][$form_id] == 1) {  $form_mandatory = 1;  }
      $form_display_log = 0;
      if($_POST['new_form_display_log'][$form_id] == 1) {  $form_display_log = 1;  }
      $max_order_sql = "SELECT MAX(`order`) AS `order` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1';";
      if($_POST['new_form_order'][$form_id] != '')
        {
        $order_number = $_POST['new_form_order'][$form_id];
        }
        else
          {
          $max_order_sql = $wpdb->get_results($max_order_sql,ARRAY_A);
          $order_number = $max_order_sql[0]['order'] + 1;
          }
      $wpdb->query("INSERT INTO `".WPSC_TABLE_CHECKOUT_FORMS."` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '$form_name', '$form_type', '$form_mandatory', '$form_display_log', '', '1','".$order_number."');");
      }
    }
//   echo "<div class='updated'><p align='center'>".TXT_WPSC_THANKSAPPLIED."</p></div>";
  }
  if ($_GET['checkout_options']=='true') {
?>
<div class="wrap">
  <h2><?php echo TXT_WPSC_FORM_FIELDS;?></h2>  
		<form method='POST' action='admin.php?page=<?php echo WPSC_DIR_NAME; ?>/options.php' id='chekcout_options_tbl'>
		  <input type='hidden' name='checkout_submits' value='true'>
<table>
<tr>
        <td>
        <?php echo TXT_WPSC_REQUIRE_REGISTRATION;?>:
        </td>
        <td>
        <?php
  $require_register = get_option('require_register');
  $require_register1 = "";
  $require_register2 = "";
  switch($require_register)
    {
    case 0:
    $require_register2 = "checked ='true'";
    break;
    
    case 1:
    $require_register1 = "checked ='true'";
    break;
    }
  
        ?>
        <input type='radio' value='1' name='require_register' id='require_register1' <?php echo $require_register1; ?> /> <label for='require_register1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
        <input type='radio' value='0' name='require_register' id='require_register2' <?php echo $require_register2; ?> /> <label for='require_register2'><?php echo TXT_WPSC_NO;?></label>
        </td>
	<td>
		(<?=TXT_WPSC_ANYONEREGISTER;?>)
	</td>
      </tr>
</table>
  <p><?php echo TXT_WPSC_CHECKOUT_FORM_FIELDS_DESCRIPTION;?></p>
  <table id='form_field_table' style='border-collapse: collapse;'>
    <tr>
      <th class='namecol'>
      <?php echo TXT_WPSC_NAME; ?>
      </th>
      <th class='typecol'>
      <?php echo TXT_WPSC_TYPE; ?>
      </th>
      <th class='mandatorycol'>
      <?php echo TXT_WPSC_MANDATORY; ?>
      </th>
      <th class='logdisplaycol'>
       <?php echo TXT_WPSC_DISPLAY_IN_LOG; ?>      
      </th>
      <?php
      /*
      <th class='logdisplaycol'>
       <?php echo TXT_WPSC_SEND_TO_GATEWAY; ?>      
      </th>
      */
      ?>
      <th class='ordercol'>
       <?php echo TXT_WPSC_ORDER; ?>      
      </th>
      <th>
      </th>
      <th>
      </th>
    </tr>
    <tr>
      <td colspan='6' style='padding: 0px;'>
      <div id='form_field_form_container'>
  <?php
  
  $email_form_field = $wpdb->get_results("SELECT `id` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1",ARRAY_A);
  $email_form_field = $email_form_field[0];
  
  $form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' ORDER BY `order`;";
  $form_data = $wpdb->get_results($form_sql,ARRAY_A);
  //exit("<pre>".print_r($form_data,true)."</pre>");
  foreach((array)$form_data as $form_field) {
    echo "
    <div id='form_id_".$form_field['id']."'>
    <table>
    <tr>\n\r";
    echo "      <td class='namecol'><input type='text' name='form_name[".$form_field['id']."]' value='".$form_field['name']."' /></td>";
    
    echo "      <td class='typecol'><select name='form_type[".$form_field['id']."]'>";
    foreach($form_types as $form_type) {
      $selected = '';
      if($form_type === $form_field['type']) {
        $selected = "selected='selected'";
      }
       // define('TXT_WPSC_TEXTAREA', 'Textarea');
      echo "<option value='".$form_type."' ".$selected.">".constant("TXT_WPSC_".strtoupper($form_type))."</option>";
    }
    echo "</select></td>";
    
    
    $checked = "";
    if($form_field['mandatory']) {
      $checked = "checked='true'";
    }
    echo "      <td class='mandatorycol' style='text-align: center;'><input $checked type='checkbox' name='form_mandatory[".$form_field['id']."]' value='1' /></td>";
    $checked = "";
    if($form_field['display_log']) {
      $checked = "checked='true'";
    }
    echo "      <td class='logdisplaycol' style='text-align: center;'><input $checked type='checkbox' name='form_display_log[".$form_field['id']."]' value='1' /></td>";
    
    echo "      <td class='ordercol'><input type='text' size='3' name='form_order[".$form_field['id']."]' value='".$form_field['order']."' /></td>";
    
    echo "      <td style='text-align: center; width: 12px;'><a class='image_link' href='#' onclick='return remove_form_field(\"form_id_".$form_field['id']."\",".$form_field['id'].");'><img src='".WPSC_URL."/images/trash.gif' alt='".TXT_WPSC_DELETE."' title='".TXT_WPSC_DELETE."' /></a>";
    echo "</td>";
    
   
    if($email_form_field['id'] == $form_field['id']) {
     echo "<td>";
      echo "<a title='".TXT_WPSC_RECIEPT_EMAIL_ADDRESS."' class='flag_email' href='#' ><img src='".WPSC_URL."/images/exclamation.png' alt='' /> </a>";
    }else{
	 echo "<td style='width:16px'>";    
    	echo "&nbsp;";
    }
    echo "</td>";
    
    echo "
    </tr>
    </table>
    </div>";
    }
  ?>
    </div>
    </td>
  </tr>
    <tr>
      <td colspan='6' style='padding: 2px;'>
        <input type='hidden' name='submit_action' value='add' />
        <input type='submit' name='submit' value='<?php echo TXT_WPSC_SAVE_CHANGES;?>' />
        <a href='#' onclick='return add_form_field();'><?php echo TXT_WPSC_ADD_NEW_FORM;?></a>
      </td>
    </tr>
  </table>
   
   <?php
    $curgateway = get_option('payment_gateway');
    foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
      if($gateway['internalname'] == $curgateway ) {
        $gateway_name = $gateway['name'];
      }
    }
      
   $selected[get_option('payment_method')] = "checked='true'";
   ?>
  </form>
</div>
		   <?php
  }
  ?>