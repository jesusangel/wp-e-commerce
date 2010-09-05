<?php
function wpsc_display_coupons_page(){
	global $wpdb;
	if(isset($_POST) && is_array($_POST) && !empty($_POST)) {

	if(isset($_POST['add_coupon']) && ($_POST['add_coupon'] == 'true')&& (!isset($_POST['is_edit_coupon']) || !($_POST['is_edit_coupon'] == 'true'))) {
		$coupon_code = $_POST['add_coupon_code'];
		$discount = (double)$_POST['add_discount'];
		// cast to boolean, then integer, prevents the value from being anything but 1 or 0
		$discount_type = (int)$_POST['add_discount_type'];
		$use_once = (int)(bool)$_POST['add_use-once'];
		$every_product = (int)(bool)$_POST['add_every_product'];
		$is_active = (int)(bool)$_POST['add_active'];
		$start_date = date('Y-m-d', strtotime($_POST['add_start'])) . " 00:00:00";
		$end_date = date('Y-m-d', strtotime($_POST['add_end'])) . " 00:00:00";
		$rules = $_POST['rules'];
		foreach ($rules as $key => $rule) {
			foreach ($rule as $k => $r) {
				$new_rule[$k][$key] = $r;
			}
		}
		foreach($new_rule as $key => $rule) {
			if ($rule['value'] == '') {
				unset($new_rule[$key]);
			}
		}
		if($wpdb->query("INSERT INTO `".WPSC_TABLE_COUPON_CODES."` ( `coupon_code` , `value` , `is-percentage` , `use-once` , `is-used` , `active` , `every_product` , `start` , `expiry`, `condition` ) VALUES ( '$coupon_code', '$discount', '$discount_type', '$use_once', '0', '$is_active', '$every_product', '$start_date' , '$end_date' , '".serialize($new_rule)."' );")) {  
			echo "<div class='updated'><p align='center'>".__('Thanks, the coupon has been added.', 'wpsc')."</p></div>";
		}
	}
	if(isset($_POST['is_edit_coupon']) && ($_POST['is_edit_coupon'] == 'true') && !(isset($_POST['delete_condition'])) && !(isset($_POST['submit_condition']))) {
		foreach((array)$_POST['edit_coupon'] as $coupon_id => $coupon_data) {
			$coupon_id = (int)$coupon_id;
			$coupon_data['start'] = $coupon_data['start']." 00:00:00";
			$coupon_data['expiry'] = $coupon_data['expiry']." 00:00:00";
			$check_values = $wpdb->get_row("SELECT `id`, `coupon_code`, `value`, `is-percentage`, `use-once`, `active`, `start`, `expiry`,`every_product` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '$coupon_id'", ARRAY_A);
			//sort both arrays to make sure that if they contain the same stuff, that they will compare to be the same, may not need to do this, but what the heck
	
			if($check_values != null)
				ksort($check_values); 
			ksort($coupon_data);	
			
		//	exit('<pre>'.print_r($coupon_data,true).'</pre><pre>'.print_r($check_values,true).'</pre>');		
			if($check_values != $coupon_data) 
			{
				$insert_array = array();
				foreach($coupon_data as $coupon_key => $coupon_value) {
				  if(($coupon_key == "submit_coupon") || ($coupon_key == "delete_coupon")) {
				    continue;
				  }
			
					if(isset($check_values[$coupon_key]) && $coupon_value != $check_values[$coupon_key]) {
						$insert_array[] = "`$coupon_key` = '$coupon_value'";
					}
				}
				if(isset($check_values['every_product']) && $coupon_data['add_every_product'] != $check_values['every_product'] ){
						$insert_array[] = "`every_product` = '$coupon_data[add_every_product]'";
				}

				if(count($insert_array) > 0) {
					$wpdb->query("UPDATE `".WPSC_TABLE_COUPON_CODES."` SET ".implode(", ", $insert_array)." WHERE `id` = '$coupon_id' LIMIT 1;");
				}
				unset($insert_array);
				$rules = $_POST['rules'];
				
				foreach ((array)$rules as $key => $rule) {
					foreach ($rule as $k => $r) {
						$new_rule[$k][$key] = $r;
					}
				}
				foreach((array)$new_rule as $key => $rule) {
					if ($rule['value'] == '') {
						unset($new_rule[$key]);
					}
				}

				$conditions = $wpdb->get_var("SELECT `condition` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1");
				  $conditions=unserialize($conditions);
				  $new_cond=array();
				  if($_POST['rules']['value'][0] != ''){
					  $new_cond['property']=$_POST['rules']['property'][0];
					  $new_cond['logic']=$_POST['rules']['logic'][0];
					  $new_cond['value']=$_POST['rules']['value'][0];
					  $conditions []= $new_cond;
				  }
				  $sql ="UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `condition`='".serialize($conditions)."' WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1";
				  $wpdb->query($sql);

			}
				
			if(isset($coupon_data['delete_coupon']) && $coupon_data['delete_coupon'] != '') {
				$wpdb->query("DELETE FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '$coupon_id' LIMIT 1;");
			}
		}
	}
  if(isset($_POST['delete_condition'])){

	  $conditions = $wpdb->get_var("SELECT `condition` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1");
	  $conditions=unserialize($conditions);
	    
	  unset($conditions[(int)$_POST['delete_condition']]);
	  	
	  //$conditions = array_values($conditions);
	 //  exit('<pre>'.print_r($_POST, true).'</pre><pre>'.print_r($conditions, true).'</pre>'.$sql);
	  $sql ="UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `condition`='".serialize($conditions)."' WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1";
	
	  $wpdb->query($sql);
  }
  if(isset($_POST['submit_condition'])){
	$conditions = $wpdb->get_var("SELECT `condition` FROM `".WPSC_TABLE_COUPON_CODES."` WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1");
	  $conditions=unserialize($conditions);
	  $new_cond=array();
	  $new_cond['property']=$_POST['rules']['property'][0];
	  $new_cond['logic']=$_POST['rules']['logic'][0];
	  $new_cond['value']=$_POST['rules']['value'][0];
	  $conditions []= $new_cond;
	  $sql ="UPDATE `".WPSC_TABLE_COUPON_CODES."` SET `condition`='".serialize($conditions)."' WHERE `id` = '".(int)$_POST['coupon_id']."' LIMIT 1";
	  $wpdb->query($sql);
  }
  if(isset($_POST['change-settings']) && $_POST['change-settings'] == 'true') {
    if(isset($_POST['wpsc_also_bought']) && $_POST['wpsc_also_bought'] == 'on') {
      update_option('wpsc_also_bought', 1);
		} else {
      update_option('wpsc_also_bought', 0);
		}

    if(isset($_POST['display_find_us']) && $_POST['display_find_us'] == 'on') {
      update_option('display_find_us', 1);
		} else {
      update_option('display_find_us', 0);
		}
      
    if(isset($_POST['wpsc_share_this']) && $_POST['wpsc_share_this'] == 'on') {
      update_option('wpsc_share_this', 1);
		} else {
      update_option('wpsc_share_this', 0);
		}
	}
}

/*<strong><?php echo TXT_WPSC_ADD_COUPON; ?></strong>*/
?>
<script type='text/javascript'>
	jQuery(".pickdate").datepicker();
		/* jQuery datepicker selector */
	if (typeof jQuery('.pickdate').datepicker != "undefined") {
		jQuery('.pickdate').datepicker({ dateFormat: 'yy-mm-dd' });
	}
</script>
<div class="wrap">
  <h2><?php echo __('Coupons', 'wpsc');?></h2>
  <div style='margin:0px;' class="tablenav wpsc_admin_nav">
  <!-- <a target="_blank" href="http://www.instinct.co.nz/e-commerce/marketing/" class="about_this_page"><span>About This Page</span> </a> -->

 	<form action='' method='post'>
		<input id='add_coupon_box_link' type='submit' class=' add_item_link button' name='add_coupon_button' value='<?php echo __('Create Coupon', 'wpsc');?>' onclick='return show_status_box("add_coupon_box","add_coupon_box_link");return false;' />
	</form>
</div>
<!-- <form name='edit_coupon' method='post' action=''>   -->
<table style="width: 100%;">
  <tr>
    <td id="coupon_data">
    

<div id='add_coupon_box' class='modify_coupon' >
<form name='add_coupon' method='post' action=''>
<table class='add-coupon' >
 <tr>
   <th>
   <?php echo __('Coupon Code', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Discount', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Start', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Expiry', 'wpsc'); ?>
   </th>
<!--
   <th>
   <?php echo __('Use Once', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Active', 'wpsc'); ?>
   </th>
   <th>
   <?php echo __('Apply On All Products', 'wpsc'); ?>
   </th>
-->
 </tr>
 <tr>
   <td>
   <input type='text' value='' name='add_coupon_code' />
   </td>
   <td>
   <input type='text' value='' size='3' name='add_discount' />
   <select name='add_discount_type'>
     <option value='0' >$</option>
     <option value='1' >%</option>
     <option value='2' ><?php _e('Free shipping','wpsc'); ?></option>
   </select>
   </td>
   <td>
   <input type='text' class='pickdate' size='11' name='add_start' />
   <!--<select name='add_start[day]'>
   <?php
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d")) { $selected = "selected='selected'"; }
     echo "<option $selected value='$i'>$i</option>";
     }
   ?>
   </select>
   <select name='add_start[month]'>
   <?php
   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m")) { $selected = "selected='selected'"; }
     echo "<option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
   ?>
   </select>
   <select name='add_start[year]'>
   <?php
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == date("Y")) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>".$i."</option>";
     }
   ?>
   </select>-->
   </td>
   <td>
   <input type='text' class='pickdate' size='11' name='add_end'>
   <!--<select name='add_end[day]'>
   <?php
   for($i = 1; $i <=31; ++$i) {
     $selected = '';
     if($i == date("d")) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>$i</option>";
     }
   ?>
   </select>
   <select name='add_end[month]'>
   <?php
   for($i = 1; $i <=12; ++$i) {
     $selected = '';
     if($i == (int)date("m")) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>".date("M",mktime(0, 0, 0, $i, 1, date("Y")))."</option>";
     }
   ?>
   </select>
   <select name='add_end[year]'>
   <?php
   for($i = date("Y"); $i <= (date("Y") +12); ++$i) {
     $selected = '';
     if($i == (date("Y")+1)) { $selected = "selected='true'"; }
     echo "<option $selected value='$i'>".$i."</option>";
     }
   ?>
   </select>-->
   </td>
   <td>
   
   <input type='hidden' value='true' name='add_coupon' />
   <input type='submit' value='Add Coupon' name='submit_coupon' class='button-primary' />
   </td>
 </tr>
 <tr><td colspan='3' scope="row">
   <p><span class='input_label'><?php _e('Active','wpsc'); ?></span><input type='hidden' value='0' name='add_active' />
   <input type='checkbox' value='1' checked='checked' name='add_active' />
	<span class='description'><?php _e('Activate coupon on creation.', 'wpsc')?></span></p></td></tr>
 
 <tr><td colspan='3' scope="row">
   <p><span class='input_label'><?php _e('Use Once','wpsc'); ?></span><input type='hidden' value='0' name='add_use-once' />
	<input type='checkbox' value='1' name='add_use-once' />
	<span class='description'><?php _e('Deactivate coupon after it has been used.', 'wpsc')?></span></p></td></tr>

 <tr><td colspan='3' scope="row">
   <p><span class='input_label'><?php _e('Apply On All Products','wpsc'); ?></span><input type='hidden' value='0' name='add_every_product' />
	<input type="checkbox" value="1" name='add_every_product'/>
	<span class='description'><?php _e('This coupon affects each product at checkout.', 'wpsc')?></span></p></td></tr>

<tr><td colspan='3'><span id='table_header'>Conditions</span></td></tr>
<tr><td colspan="8">
	<div class='coupon_condition' >
		<div class='first_condition'>
			<select class="ruleprops" name="rules[property][]">
				<option value="item_name" rel="order"><?php _e('Item name','wpsc'); ?></option>
				<option value="item_quantity" rel="order"><?php _e('Item quantity','wpsc'); ?></option>
				<option value="total_quantity" rel="order"><?php _e('Total quantity','wpsc'); ?></option>
				<option value="subtotal_amount" rel="order"><?php _e('Subtotal amount','wpsc'); ?></option>
				<?php echo apply_filters( 'wpsc_coupon_rule_property_options', '' ); ?>
			</select>
			<select name="rules[logic][]">
				<option value="equal"><?php _e('Is equal to','wpsc');?></option>
				<option value="greater"><?php _e('Is greater than','wpsc'); ?></option>
				<option value="less"><?php _e('Is less than','wpsc'); ?></option>
				<option value="contains"><?php _e('Contains','wpsc'); ?></option>
				<option value="not_contain"><?php _e('Does not contain','wpsc'); ?></option>
				<option value="begins"><?php _e('Begins with','wpsc'); ?></option>
				<option value="ends"><?php _e('Ends with','wpsc'); ?></option>
                		<option value="category"><?php _e('In Category','wpsc'); ?></option>
			</select>
			<span>
				<input type="text" name="rules[value][]"/>
			</span>
			<span>
            	<script>
				var coupon_number=1;
				function add_another_property(this_button){
					var new_property='<div class="coupon_condition">\n'+
						'<div><img height="16" width="16" class="delete" alt="Delete" src="<?php echo WPSC_URL; ?>/images/cross.png" onclick="jQuery(this).parent().remove();"/> \n'+
							'<select class="ruleprops" name="rules[property][]"> \n'+
								'<option value="item_name" rel="order">Item name</option> \n'+
								'<option value="item_quantity" rel="order">Item quantity</option>\n'+
								'<option value="total_quantity" rel="order">Total quantity</option>\n'+ 
								'<option value="subtotal_amount" rel="order">Subtotal amount</option>\n'+ 
								'<?php echo apply_filters( 'wpsc_coupon_rule_property_options', '' ); ?>'+
							'</select> \n'+
							'<select name="rules[logic][]"> \n'+
								'<option value="equal">Is equal to</option> \n'+
								'<option value="greater">Is greater than</option> \n'+
								'<option value="less">Is less than</option> \n'+
								'<option value="contains">Contains</option> \n'+
								'<option value="not_contain">Does not contain</option> \n'+
								'<option value="begins">Begins with</option> \n'+
								'<option value="ends">Ends with</option> \n'+
							'</select> \n'+
							'<span> \n'+
								'<input type="text" name="rules[value][]"/> \n'+
							'</span>  \n'+
						'</div> \n'+
					'</div> ';
		
					jQuery('.coupon_condition :first').after(new_property);
					coupon_number++;
				}
				</script>
			
			</span>
			
		</div>
	</div>
</tr>
<tr><td>	<a class="wpsc_coupons_condition_add" onclick="add_another_property(jQuery(this));">
					<?php _e('Add New Condition','wpsc'); ?>
				</a></td></tr>
</table>

<br />
</form>  
</table>


  <?php
	$columns = array(
  	'coupon_code' => __('Coupon Code', 'wpsc'),
	'discount' => __('Discount', 'wpsc'),
	'start' => __('Start', 'wpsc'),
	'expiry' => __('Expiry', 'wpsc'),
	'active' => __('Active', 'wpsc'),
	'apply_on_prods' => __('Apply On All Products', 'wpsc'),
	'edit' => __('Edit', 'wpsc')
	);
	register_column_headers('display-coupon-details', $columns); 
	?>
	<table class="coupon-list widefat" cellspacing="0">
		<thead>
			<tr>
		<?php print_column_headers('display-coupon-details'); ?>
			</tr>
		</thead>
	
		<tfoot>
			<tr>
		<?php print_column_headers('display-coupon-details', false); ?>
			</tr>
		</tfoot>
	
		<tbody><?php
		$i=0;
$coupon_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_COUPON_CODES."` ",ARRAY_A);
//exit('Coupon Data<pre>'.print_r($coupon_data, true).'</pre>');
foreach((array)$coupon_data as $coupon) {
  $alternate = "";
  $i++;
  if(($i % 2) != 0) {
    $alternate = "class='alt'";
    }
  echo "<tr $alternate>\n\r";
  
  echo "    <td>\n\r";
  echo $coupon['coupon_code'];
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";
  if($coupon['is-percentage'] == 1){
    echo $coupon['value']."%";
    } else {    
    echo nzshpcrt_currency_display($coupon['value'], 1);
    }
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";
  echo date("d/m/Y",strtotime($coupon['start']));
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";
  echo date("d/m/Y",strtotime($coupon['expiry']));
  echo "    </td>\n\r";
  
  echo "    <td>\n\r";  
  switch($coupon['active']) {
    case 1:
    echo "<img src='".WPSC_URL."/images/yes_stock.gif' alt='' title='' />";
    break;
    
    case 0: default:
    echo "<img src='".WPSC_URL."/images/no_stock.gif' alt='' title='' />";
    break;
    }
  echo "    </td>\n\r";
  
   echo "    <td>\n\r";
  switch($coupon['every_product']) {
	  case 1:
		  echo "<img src='".WPSC_URL."/images/yes_stock.gif' alt='' title='' />";
		  break;
    
	  case 0: default:
		  echo "<img src='".WPSC_URL."/images/no_stock.gif' alt='' title='' />";
		  break;
  }
  echo "    </td>\n\r";

  
  
  echo "    <td>\n\r";
  echo "<a title='".$coupon['coupon_code']."' href='#' rel='".$coupon['id']."' class='wpsc_edit_coupon'  >".__('Edit', 'wpsc')."</a>";
  echo "    </td>\n\r";
  
  echo "  </tr>\n\r";
  echo "  <tr class='coupon_edit'>\n\r";
  echo "    <td colspan='7' style='padding-left:0px;'>\n\r";
//  $status_style = "style='display: block;'";
  echo "      <div id='coupon_box_".$coupon['id']."' class='displaynone modify_coupon' >\n\r";  
  coupon_edit_form($coupon);
  echo "      </div>\n\r";
  echo "    </td>\n\r";
  echo "  </tr>\n\r";
  }
?>
		</tbody>
		</table>

  <p style='margin: 10px 0px 5px 0px;'>
  	 <?php  _e('<strong>Note:</strong> Due to a current limitation of PayPal, if your user makes a purchase and uses a coupon, we can not send a list of items through to paypal for processing. Rather, we must send the total amount of the purchase, so that within PayPal the user who purchases a product will see your shop name and the total amount of their purchase.', 'wpsc');?>
  </p>
    </td>
  </tr>
</table>
<!-- <input type='hidden' value='true' name='is_edit_coupon' /> -->
<!-- </form> -->

<br />
<div class='metabox-holder'>
<?php 
	add_meta_box("wpsc_marketing_settings", __('Marketing Section', 'wpsc'), "wpsc_marketing_meta_box", "wpsc");
	add_meta_box("wpsc_rss_address", __('RSS Address', 'wpsc'), "wpsc_rss_address_meta_box", "wpsc");
	add_meta_box("wpsc_google_merch_center", __('Google Merchant Centre / Google Product Search', 'wpsc'), "wpsc_google_merch_center_meta_box", "wpsc");
	do_meta_boxes('wpsc','advanced',null);
?>
</div>
</div>
</div>    
<?php
}

function wpsc_marketing_meta_box(){

    $wpsc_also_bought = get_option('wpsc_also_bought');
    $wpsc_also_bought1 = "";
   	if ($wpsc_also_bought=='1') {
		$wpsc_also_bought1 = "checked ='checked'";
	}
	$wpsc_share_this = get_option('wpsc_share_this');
    $wpsc_share_this1 = "";
    if ($wpsc_share_this=='1') {
		$wpsc_share_this1 = "checked ='checked'";
	}
	$display_find_us = get_option('display_find_us');
	$display_find_us1 = "";
	if ($display_find_us=='1') {
		$display_find_us1 = "checked ='checked'";
	}
?>
<form name='cart_options' method='post' action=''>
	<input type='hidden' value='true' name='change-settings' />        
	<p><span class='input_label'><?php echo __('Display Cross Sales', 'wpsc');?></span><input <?php echo $wpsc_also_bought1; ?> type='checkbox' name='wpsc_also_bought' /><span class='description'>  <?php echo __('Adds the \'Users who bought this also bought\' item to the single products page.', 'wpsc');?></span></p><br />
	<p><span class='input_label'><?php echo __('Show Share This (Social Bookmarks)', 'wpsc');?></span><input <?php echo $wpsc_share_this1; ?> type='checkbox' name='wpsc_share_this' /><span class='description'>  <?php echo __('Adds the \'Share this link\' item to the single products page.', 'wpsc');?></span></p><br />
	<p><span class='input_label'> <?php echo __('Display How Customer Found Us Survey', 'wpsc')?></span><input <?php echo $display_find_us1; ?> type='checkbox' name='display_find_us' /><span class='description'>  <?php echo __('Adds the \'How did you find out about us\' drop-down option at checkout.', 'wpsc')?></span></p><br />
	<p><input  type='submit' class='button-primary' value='<?php echo __('Submit', 'wpsc');?>' name='form_submit' /></p>
</form>
<?php
}
function wpsc_rss_address_meta_box(){
?>
	<p><?php echo __('People can use this RSS feed to keep up to date with your product list.', 'wpsc');?></p>
	<p><?php _e('RSS Feed Address','wpsc') ?> :	<?php echo get_bloginfo('url')."/index.php?rss=true&amp;action=product_list"; ?></p>
<?php
}
function wpsc_google_merch_center_meta_box(){
?>
<p><?php _e('To import your products into <a href="http://www.google.com/merchants/" target="_blank">Google Merchant Centre</a> so that they appear within Google Product Search results, sign up for a Google Merchant Centre account and add a scheduled data feed with the following URL:','wpsc'); ?></p>
<?php $google_feed_url = get_bloginfo('url')."/index.php?rss=true&action=product_list&xmlformat=google"; ?>
<a href="<?php echo htmlentities($google_feed_url); ?>"><?php echo htmlentities($google_feed_url); ?></a>
<?php
}
?>