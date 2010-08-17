<?php
// ini_set('display_errors','1');

function coupon_edit_form($coupon) {

$conditions = unserialize($coupon['condition']);
	//exit('<pre>'.print_r($coupon, true).'</pre>');

  $start_timestamp = strtotime($coupon['start']);
  $end_timestamp = strtotime($coupon['expiry']);
  $id = $coupon['id'];
  $output = '';
  $output .= "<form name='edit_coupon' id='".$coupon['coupon_code']."' method='post' action=''>\n\r";
    $output .= "   <input type='hidden' value='true' name='is_edit_coupon' />\n\r";
  $output .= "<table class='add-coupon'>\n\r";
  $output .= " <tr>\n\r";
  $output .= "   <th>".__('Coupon Code', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Discount', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Start', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Expiry', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Use Once', 'wpsc')."</th>\n\r";
  $output .= "   <th>".__('Active', 'wpsc')."</th>\n\r";
	$output .= "   <th>".__('Apply On All Products', 'wpsc')."</th>\n\r";
  $output .= "   <th></th>\n\r";
  $output .= " </tr>\n\r";
  $output .= " <tr>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' size='8' value='".$coupon['coupon_code']."' name='edit_coupon[".$id."][coupon_code]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='text' style='width:28px;' value='".$coupon['value']."'  name=edit_coupon[".$id."][value]' />";
  $output .= "   <select style='width:20px;' name='edit_coupon[".$id."][is-percentage]'>";
  $output .= "     <option value='0' ".(($coupon['is-percentage'] == 0) ? "selected='true'" : '')." >$</option>\n\r";//
  $output .= "     <option value='1' ".(($coupon['is-percentage'] == 1) ? "selected='true'" : '')." >%</option>\n\r";
  $output .= "     <option value='2' ".(($coupon['is-percentage'] == 2) ? "selected='true'" : '')." >Free shipping</option>\n\r";
  $output .= "   </select>\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_start = explode(" ",$coupon['start']);
  $output .= "<input type='text' class='pickdate' size='8' name='edit_coupon[".$id."][start]' value='{$coupon_start[0]}'>";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $coupon_expiry = explode(" ",$coupon['expiry']);
  $output .= "<input type='text' class='pickdate' size='8' name='edit_coupon[".$id."][expiry]' value='{$coupon_expiry[0]}'>";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['use-once'] == 1) ? "checked='checked'" : '')." name='edit_coupon[".$id."][use-once]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['active'] == 1) ? "checked='checked'" : '')." name='edit_coupon[".$id."][active]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='0' name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "   <input type='checkbox' value='1' ".(($coupon['every_product'] == 1) ? "checked='checked'" : '')." name='edit_coupon[".$id."][every_product]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= "  <td>\n\r";
  $output .= "   <input type='hidden' value='".$id."' name='edit_coupon[".$id."][id]' />\n\r";
  $output .= "  </td>\n\r";
  $output .= " </tr>\n\r";

  if($conditions != null){
	  $output .= "<tr>";
	  $output .= "<th>";
	  $output .= "Conditions";
	  $output .= "</th>";
	  $output .= "</tr>";
	  $output .= "<th>";
	  $output .= "Delete";
	  $output .= "</th>";
	  $output .= "<th>";
	  $output .= "Property";
	  $output .= "</th>";
	  $output .= "<th>";
	  $output .= "Logic";
	  $output .= "</th>";
	  $output .= "<th>";
	  $output .= "Value";
	  $output .= "</th>";
	  $output .= " </tr>\n\r";
	  $i=0;
	  foreach ($conditions as $condition){
		  $output .= "<tr>";
		  $output .= "<td>";
		  $output .= "<input type='hidden' name='coupon_id' value='".$id."' />";
		  $output .= "<input type='submit' id='delete_condition".$i."' style='display:none;' value='".$i."' name='delete_condition' />";
		  $output .= "<span style='cursor:pointer;' class='delete_button' onclick='jQuery(\"#delete_condition".$i."\").click()'>Delete</span>";
		  $output .= "</td>";
		  $output .= "<td>";
		  $output .= $condition['property'];
		  $output .= "</td>";
		  $output .= "<td>";
		  $output .= $condition['logic'];
		  $output .= "</td>";
		  $output .= "<td>";
		  $output .= $condition['value'];
		  $output .= "</td>";
		  $output .= "</tr>";
		  $i++;
	  }
	  $output .=	wpsc_coupons_conditions( $id);
  }elseif($conditions == null){
  	$output .=	wpsc_coupons_conditions( $id);

  }
  $output .= "</table>\n\r";
  $output .= "</form>\n\r";
  echo $output;
  return $output;
  }
function wpsc_coupons_conditions($id){
?>

<?php

$output ='
<input type="hidden" name="coupon_id" value="'.$id.'" />
<tr><td colspan="3"><b>Add Conditions</b></td></tr>
<tr><td colspan="6">
	<div class="coupon_condition">
		<div>
			<select class="ruleprops" name="rules[property][]">
				<option value="item_name" rel="order">Item name</option>
				<option value="item_quantity" rel="order">Item quantity</option>
				<option value="total_quantity" rel="order">Total quantity</option>
				<option value="subtotal_amount" rel="order">Subtotal amount</option>
				' . apply_filters( 'wpsc_coupon_rule_property_options', '' ) . '
			</select>
			<select name="rules[logic][]">
				<option value="equal">Is equal to</option>
				<option value="greater">Is greater than</option>
				<option value="less">Is less than</option>
				<option value="contains">Contains</option>
				<option value="not_contain">Does not contain</option>
				<option value="begins">Begins with</option>
				<option value="ends">Ends with</option>
			</select>
			<span>
				<input type="text" name="rules[value][]"/>
			</span>
			

		</div>
	</div>
	</td>
	<td colspan="3">
	
		<input type="submit" value="'.__("Update Coupon", "wpsc").'" class="button-primary" name="edit_coupon['.$id.'][submit_coupon]" />
 		<input type="submit" value="'.__("Delete Coupon", "wpsc").'" name="edit_coupon['.$id.'][delete_coupon]" />
	</td>
</tr>
';
return $output;

}  
function setting_button(){
	$next_url	= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']."?page=wpsc-edit-products";
	
// 	$output.="<div><img src='".get_option('siteurl')."/wp-content/plugins/".WPSC_DIR_NAME."/images/settings_button.jpg' onclick='display_settings_button()'>";
	$output.="<div style='float: right; margin-top: 0px; position: relative;'> | <a href='#' onclick='display_settings_button(); return false;' style='text-decoration: underline;'>".__('Settings', 'wpsc')." &raquo;</a>";
	$output.="<span id='settings_button' style='width:180px;background-color:#f1f1f1;position:absolute; right: 10px; border:1px solid black; display:none;'>";
	$output.="<ul class='settings_button'>";
	
	$output.="<li><a href='admin.php?page=wpsc-settings'>".__('Shop Settings', 'wpsc')."</a></li>";
	$output.="<li><a href='admin.php?page=wpsc-settings&amp;tab=gateway'>".__('Money and Payment', 'wpsc')."</a></li>";
	$output.="<li><a href='admin.php?page=wpsc-settings&amp;tab=checkout'>".__('Checkout Page Settings', 'wpsc')."</a></li>";
	//$output.="<li><a href='?page=".WPSC_DIR_NAME."/instructions.php'>Help/Upgrade</a></li>";
	$output.="</ul>";
//	$output.="<div>Checkout Settings</div>";
	$output.="</span>&emsp;&emsp;</div>";
	
	return $output;
}

function wpsc_right_now() {
	global $wpdb,$nzshpcrt_imagesize_info;
	$year = date("Y");
	$month = date("m");
	$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
	$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
	
	
	$product_count = $wpdb->get_var("SELECT COUNT(*)
		FROM `".$wpdb->posts."` 
		WHERE `post_status` = 'publish'
		AND `post_type` IN ('wpsc-product')"
	);
	$replace_values[":productcount:"] = $product_count;
	

	$replace_values[":productcount:"] .= " ".(($replace_values[":productcount:"] == 1) ? __('product', 'wpsc') : __('products', 'wpsc'));
	$product_unit = (($replace_values[":productcount:"] == 1) ? __('product', 'wpsc') : __('products', 'wpsc'));
	
	
	
	$group_count = count(get_terms("wpsc_product_category"));
	$replace_values[":groupcount:"] = $group_count;
	
	
	
	$replace_values[":groupcount:"] .= " ".(($replace_values[":groupcount:"] == 1) ? __('group', 'wpsc') : __('groups', 'wpsc'));
	$group_unit = (($replace_values[":groupcount:"] == 1) ? __('group', 'wpsc') : __('groups', 'wpsc'));
	
	
	
	$sales_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$start_timestamp."' AND '".$end_timestamp."'");
	$replace_values[":salecount:"] = $sales_count. " ".((isset($replace_values[":salecount:"]) && ($replace_values[":salecount:"] == 1)) ? __('sale', 'wpsc') : __('sales', 'wpsc'));
	$sales_unit = (($replace_values[":salecount:"] == 1) ? __('sale', 'wpsc') : __('sales', 'wpsc'));
		
	$replace_values[":monthtotal:"] = nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
	$replace_values[":overaltotal:"] = nzshpcrt_currency_display(admin_display_total_price(),1);
	
	
	
	
	$variation_count = count(get_terms("wpsc-variation", array('parent' => 0)));
	$variation_unit = (($variation_count == 1) ? __('variation', 'wpsc') : __('variations', 'wpsc'));
	
	$replace_values[":pendingcount:"] = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('1')");
	$pending_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('1')");
	$replace_values[":pendingcount:"] .= " " . (($replace_values[":pendingcount:"] == 1) ? __('transaction', 'wpsc') : __('transactions', 'wpsc'));
	$pending_sales_unit = (($replace_values[":pendingcount:"] == 1) ? __('transaction', 'wpsc') : __('transactions', 'wpsc'));
	
	$accept_sales = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `processed` IN ('2' ,'3', '4')");
	$accept_sales_unit = (($accept_sales == 1) ? __('transaction', 'wpsc') : __('transactions', 'wpsc'));

	
	$replace_values[":theme:"] = get_option('wpsc_selected_theme');
	$replace_values[":versionnumber:"] = WPSC_PRESENTABLE_VERSION;
	
	if (function_exists('add_object_page')) {
		$output="";	
		$output.="<div id='dashboard_right_now' class='postbox'>";
		$output.="	<h3 class='hndle'>";
		$output.="		<span>".__('Current Month', 'wpsc')."</span>";
		$output.="		<br class='clear'/>";
		$output.="	</h3>";
		
		$output .= "<div class='inside'>";
		$output .= "<div class='table'>";
		$output .= "<p class='sub'>".__('At a Glance', 'wpsc')."</p>";
		//$output.="<p class='youhave'>".__('You have <a href='admin.php?page=wpsc-edit-products'>:productcount:</a>, contained within <a href='admin.php?page=wpsc-edit-groups'>:groupcount:</a>. This month you made :salecount: and generated a total of :monthtotal: and your total sales ever is :overaltotal:. You have :pendingcount: awaiting approval.', 'wpsc')."</p>";
		$output .= "<table style='border-top:1px solid #ececec;'>";
		$output .= "<tr class='first'>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=wpsc-edit-products'>".$product_count."</a>";
		$output .= "</td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($product_unit);
		$output .= "</td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=wpsc-sales-logs'>".$sales_count."</a>";
		$output .= "</td>";
		$output .= "<td class='last'>";
		$output .= ucfirst($sales_unit);
		$output .= "</td>";
		$output .= "</tr>";
		
		$output .= "<tr>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=wpsc-edit-groups'>".$group_count."</a>";
		$output .= "</td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($group_unit);
		$output .= "</td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=wpsc-sales-logs'>".$pending_sales."</a>";
		$output .= "</td>";
		$output .= "<td class='last t waiting'>".__('Pending', 'wpsc')." ";
		$output .= ucfirst($pending_sales_unit);
		$output .= "</td>";
		$output .= "</tr>";
		
		$output .= "<tr>";
		$output .= "<td class='first b'>";
		$output .= "<a href='?page=wpsc-edit-variations'>".$variation_count."</a>";
		$output .= "</td>";
		$output .= "<td class='t'>";
		$output .= ucfirst($variation_unit);
		$output .= "</td>";
		$output .= "<td class='b'>";
		$output .= "<a href='?page=wpsc-sales-logs'>".$accept_sales."</a>";
		$output .= "</td>";
		$output .= "<td class='last t approved'>".__('Closed', 'wpsc')." ";
		$output .= ucfirst($accept_sales_unit);
		$output .= "</td>";
		$output .= "</tr>";
		
		$output .= "</table>";
		$output .= "</div>";
		$output .= "<div class='versions'>";
		$output .= "<p><a class='button rbutton' href='admin.php?page=wpsc-edit-products'><strong>".__('Add New Product', 'wpsc')."</strong></a>".__('Here you can add products, groups or variations', 'wpsc')."</p>";
		$output .= "</div>";
		$output .= "</div>";
		$output.="</div>";
	} else {	
		$output="";	
		$output.="<div id='rightnow'>\n\r";
		$output.="	<h3 class='reallynow'>\n\r";
		$output.="		<a class='rbutton' href='admin.php?page=wpsc-edit-products'><strong>".__('Add New Product', 'wpsc')."</strong></a>\n\r";
		$output.="		<span>"._('Right Now')."</span>\n\r";
		
		//$output.="		<br class='clear'/>\n\r";
		$output.="	</h3>\n\r";
		
		$output.="<p class='youhave'>".__('You have <a href="admin.php?page=wpsc-edit-products">:productcount:</a>, contained within <a href="admin.php?page=wpsc-edit-groups">:groupcount:</a>. This month you made :salecount: and generated a total of :monthtotal: and your total sales ever is :overaltotal:. You have :pendingcount: awaiting approval.', 'wpsc')."</p>\n\r";
		$output.="	<p class='youare'>\n\r";
		$output.="		".__('You are using the :theme: style. This is WP e-Commerce :versionnumber:.', 'wpsc')."\n\r";
		//$output.="		<a class='rbutton' href='themes.php'>Change Theme</a>\n\r";
		//$output.="<span id='wp-version-message'>This is WordPress version 2.6. <a class='rbutton' href='http://wordpress.org/download/'>Update to 2.6.1</a></span>\n\r";
		$output.="		</p>\n\r";
		$output.="</div>\n\r";
		$output.="<br />\n\r";
		$output = str_replace(array_keys($replace_values), array_values($replace_values),$output);
	}
	
	return $output;
}


function wpsc_packing_slip($purchase_id) {
	global $wpdb;
	$purch_sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='".$purchase_id."'";
		$purch_data = $wpdb->get_row($purch_sql,ARRAY_A) ;
			

		//echo "<p style='padding-left: 5px;'><strong>".__('Date', 'wpsc')."</strong>:".date("jS M Y", $purch_data['date'])."</p>";

		$cartsql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`=".$purchase_id."";
		$cart_log = $wpdb->get_results($cartsql,ARRAY_A) ; 
		$j = 0;
	
		if($cart_log != null) {
			echo "<div class='packing_slip'>\n\r";
			echo apply_filters( 'wpsc_packing_slip_header', '<h2>' . __( 'Packing Slip', 'wpsc' ) . "</h2>\n\r" );
			echo "<strong>".__('Order', 'wpsc')." #</strong> ".$purchase_id."<br /><br />\n\r";
			
			echo "<table>\n\r";
			
			$form_sql = "SELECT * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE	`log_id` = '".(int)$purchase_id."'";
			$input_data = $wpdb->get_results($form_sql,ARRAY_A);
			
			foreach($input_data as $input_row) {
				$rekeyed_input[$input_row['form_id']] = $input_row;
			}
			
			
			if($input_data != null) {
				$form_data = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1'",ARRAY_A);
				
				foreach($form_data as $form_field) {
					switch($form_field['type']) {
					case 'country':

						$delivery_region_count = $wpdb->get_var("SELECT COUNT(`regions`.`id`) FROM `".WPSC_TABLE_REGION_TAX."` AS `regions` INNER JOIN `".WPSC_TABLE_CURRENCY_LIST."` AS `country` ON `country`.`id` = `regions`.`country_id` WHERE `country`.`isocode` IN('".$wpdb->escape( $purch_data['billing_country'])."')");

						if(is_numeric($purch_data['shipping_region']) && ($delivery_region_count > 0)) {
							echo "	<tr><td>".__('State', 'wpsc').":</td><td>".wpsc_get_region($purch_data['shipping_region'])."</td></tr>\n\r";
						}
						echo "	<tr><td>".wp_kses($form_field['name'], array() ).":</td><td>".wpsc_get_country($purch_data['billing_country'])."</td></tr>\n\r";
						break;
								
						case 'delivery_country':
						echo "	<tr><td>".$form_field['name'].":</td><td>".wpsc_get_country($purch_data['shipping_country'])."</td></tr>\n\r";
						break;
								
						case 'heading':
						echo "	<tr class='heading'><td colspan='2'><strong>".wp_kses($form_field['name'], array()).":</strong></td></tr>\n\r";
						break;
						
						default:
						echo "	<tr><td>".wp_kses($form_field['name'], array() ).":</td><td>".htmlentities(stripslashes($rekeyed_input[$form_field['id']]['value']), ENT_QUOTES)."</td></tr>\n\r";
						break;
					}
				}
			} else {
				echo "	<tr><td>".__('Name', 'wpsc').":</td><td>".$purch_data['firstname']." ".$purch_data['lastname']."</td></tr>\n\r";
				echo "	<tr><td>".__('Address', 'wpsc').":</td><td>".$purch_data['address']."</td></tr>\n\r";
				echo "	<tr><td>".__('Phone', 'wpsc').":</td><td>".$purch_data['phone']."</td></tr>\n\r";
				echo "	<tr><td>".__('Email', 'wpsc').":</td><td>".$purch_data['email']."</td></tr>\n\r";
			}
			
			if(get_option('payment_method') == 2) {
				$gateway_name = '';
				foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
					if($purch_data['gateway'] != 'testmode') {
						if($gateway['internalname'] == $purch_data['gateway'] ) {
							$gateway_name = $gateway['name'];
						}
					} else {
						$gateway_name = "Manual Payment";
					}
				}
			}
// 			echo "	<tr><td colspan='2'></td></tr>\n\r";
// 			echo "	<tr><td>".__('Payment Method', 'wpsc').":</td><td>".$gateway_name."</td></tr>\n\r";
// 			//echo "	<tr><td>".__('Purchase No.', 'wpsc').":</td><td>".$purch_data['id']."</td></tr>\n\r";
// 			echo "	<tr><td>".__('How The Customer Found Us', 'wpsc').":</td><td>".$purch_data['find_us']."</td></tr>\n\r";
// 			$engrave_line = explode(",",$purch_data['engravetext']);
// 			echo "	<tr><td>".__('Engrave text', 'wpsc')."</td><td></td></tr>\n\r";
// 			echo "	<tr><td>".__('Line 1', 'wpsc').":</td><td>".$engrave_line[0]."</td></tr>\n\r";
// 			echo "	<tr><td>".__('Line 2', 'wpsc').":</td><td>".$engrave_line[1]."</td></tr>\n\r";
// 			if($purch_data['transactid'] != '') {
// 				echo "	<tr><td>".__('Transaction Id', 'wpsc').":</td><td>".$purch_data['transactid']."</td></tr>\n\r";
// 			}
			echo "</table>\n\r";
			
			
			
			
			echo "<table class='packing_slip'>";
				
				echo "<tr>";
				echo " <th>".__('Quantity', 'wpsc')." </th>";
				
				echo " <th>".__('Name', 'wpsc')."</th>";
				
				
				echo " <th>".__('Price', 'wpsc')." </th>";
				
				echo " <th>".__('Shipping', 'wpsc')." </th>";
				echo '<th>Tax</th>';
				echo '</tr>';
			$endtotal = 0;
			$all_donations = true;
			$all_no_shipping = true;
			$file_link_list = array();
			foreach($cart_log as $cart_row) {
			
				$alternate = "";
				$j++;
				if(($j % 2) != 0) {
					$alternate = "class='alt'";
				}
				$productsql= "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `id`=".$cart_row['prodid']."";
				$product_data = $wpdb->get_results($productsql,ARRAY_A);
				
				$variation_list = '';
				
				if($cart_row['donation'] != 1) {
					$all_donations = false;
				}
				
				if($cart_row['no_shipping'] != 1) {
					$shipping = $cart_row['pnp'] * $cart_row['quantity'];
					$total_shipping += $shipping;						
					$all_no_shipping = false;
				} else {
					$shipping = 0;
				}
				
				$price = $cart_row['price'] * $cart_row['quantity'];
				$gst = $price - ($price	/ (1+($cart_row['gst'] / 100)));
				
				if($gst > 0) {
					$tax_per_item = $gst / $cart_row['quantity'];
				}


				echo "<tr $alternate>";
		
		
				echo " <td>";
				echo $cart_row['quantity'];
				echo " </td>";
				
				echo " <td>";
				echo $product_data[0]['name'];
				echo stripslashes($variation_list);
				echo " </td>";
				
				
				echo " <td>";
				echo nzshpcrt_currency_display( $price, 1);
				echo " </td>";
				
				echo " <td>";
				echo nzshpcrt_currency_display($shipping, 1);
				echo " </td>";
							
	

				echo '<td>';
				echo nzshpcrt_currency_display($cart_row['tax_charged'],1);
				echo '<td>';
				echo '</tr>';
				}
			echo "</table>";
			
			echo '<table class="packing-slip-totals">';
			echo '<tr><th>Base Shipping</th><td>' . nzshpcrt_currency_display( $purch_data['base_shipping'], 1 ) . '</td></tr>';
			echo '<tr><th>Total Shipping</th><td>' . nzshpcrt_currency_display( $purch_data['base_shipping'] + $total_shipping, 1 ) . '</td></tr>';
         //wpec_taxes
         if($purch_data['wpec_taxes_total'] != 0.00)
         {
            echo '<tr><th>Taxes</th><td>' . nzshpcrt_currency_display( $purch_data['wpec_taxes_total'], 1 ) . '</td></tr>';
         }
			echo '<tr><th>Total Price</th><td>' . nzshpcrt_currency_display( $purch_data['totalprice'], 1 ) . '</td></tr>';
			echo '</table>';
			
			echo "</div>\n\r";
		} else {
			echo "<br />".__('This users cart was empty', 'wpsc');
		}

}


		


function wpsc_product_item_row() {
}

?>
