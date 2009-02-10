<?php
class ups {
	var $internal_name, $name;
	function ups () {
		$this->internal_name = "ups";
		$this->name="UPS";
		$this->is_external=true;
		$this->requires_curl=true;
		$this->requires_weight=true;
		$this->needs_zipcode=true;
		return true;
	}
	
	function getId() {
// 		return $this->usps_id;
	}
	
	function setId($id) {
// 		$usps_id = $id;
// 		return true;
	}
	
	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function getForm() {
		$wpsc_ups_settings = get_option("wpsc_ups_settings");
		
		$packaging_options['02'] = TXT_WPSC_UPS_PACKAGING_YOURS;
		$packaging_options['01'] = TXT_WPSC_UPS_PACKAGING_LETTER;
		$packaging_options['21S'] = TXT_WPSC_UPS_PACKAGING_UEB_SMALL;
		$packaging_options['21M'] = TXT_WPSC_UPS_PACKAGING_UEB_MEDIUM;
		$packaging_options['21L'] = TXT_WPSC_UPS_PACKAGING_UEB_LARGE;
		$packaging_options['03'] = TXT_WPSC_UPS_PACKAGING_TUBE;
		$packaging_options['04'] = TXT_WPSC_UPS_PACKAGING_PAK;
		
		
		
		$output = "<tr>\n\r";
		$output .= "	<td>".TXT_WPSC_UPS_DESTINATION."</td>\n\r";
		$output .= "	<td>\n\r";
		switch($wpsc_ups_settings['49_residential']) {
		  case '01':
		  $checked[0] = "checked='true'";
		  $checked[1] = "";
		  break;		  
		  
		  case '02':
		  default:
		  $checked[0] = "";
		  $checked[1] = "checked='true'";
		  break;
		}
		$output .= "		<label><input type='radio' {$checked[0]} value='01' name='wpsc_ups_settings[49_residential]'/>".TXT_WPSC_UPS_DESTINATION_RESIDENTIAL."</label><br />\n\r";
		$output .= "		<label><input type='radio' {$checked[1]} value='02' name='wpsc_ups_settings[49_residential]'/>".TXT_WPSC_UPS_DESTINATION_COMMERCIAL."</label>\n\r";
		$output .= "	</td>\n\r";
		$output .= "</tr>\n\r";
		
		
		$output .= "<tr>\n\r";
		$output .= "	<td>".TXT_WPSC_UPS_PACKAGING."</td>\n\r";
		$output .= "	<td>\n\r";
		$output .= "		<select name='wpsc_ups_settings[48_container]'>\n\r";
		foreach($packaging_options as $key => $name) {
		  $selected = '';
			if($key == $wpsc_ups_settings['48_container']) {
				$selected = "selected='true' ";
			}
			$output .= "			<option value='{$key}' {$selected}>{$name}</option>\n\r";		
		}
		$output .= "		</select>\n\r";
		$output .= "	</td>\n\r";
		$output .= "</tr>\n\r";	
		
		return $output;
	}
	
	function submit_form() {
		if ($_POST['wpsc_ups_settings'] != '') {
			update_option('wpsc_ups_settings', $_POST['wpsc_ups_settings']);
		}
		return true;
	}
	
	function getMethod($dest) {
		if ($dest == 'US') {
			$method= array(
				'1DM'    => 'Next Day Air Early AM',
				//'1DML'   => 'Next Day Air Early AM Letter',
				'1DA'    => 'Next Day Air',
				//'1DAL'   => 'Next Day Air Letter',
// 				'1DAPI'  => 'Next Day Air Intra (Puerto Rico)',
				'1DP'    => 'Next Day Air Saver',
				//'1DPL'   => 'Next Day Air Saver Letter',
				'2DM'    => '2nd Day Air AM',
				//'2DML'   => '2nd Day Air AM Letter',
				'2DA'    => '2nd Day Air',
				//'2DAL'   => '2nd Day Air Letter',
				'3DS'    => '3 Day Select',
				'GND'    => 'Ground',
// 				'GNDCOM' => 'Ground Commercial',
				//'GNDRES' => 'Ground Residential',
// 				'STD'    => 'Canada Standard',
// 				'XPR'    => 'Worldwide Express',
// 				'WXS'    => 'Worldwide Express Saver',
// 				'XPRL'   => 'Worldwide Express Letter',
// 				'XDM'    => 'Worldwide Express Plus',
// 				'XDML'   => 'Worldwide Express Plus Letter',
// 				'XPD'    => 'Worldwide Expedited',
			);
		} else if ($dest == 'CA') {
			$method = array(
				'STD'    => 'Canada Standard',
			);
		} else if ($dest == 'PR') {
			$method = array(
				'1DAPI'  => 'Next Day Air Intra (Puerto Rico)',
			);
		} else {
			$method = array(
				'XPR'    => 'Worldwide Express',
				'WXS'    => 'Worldwide Express Saver',
				//'XPRL'   => 'Worldwide Express Letter',
				'XDM'    => 'Worldwide Express Plus',
				//'XDML'   => 'Worldwide Express Plus Letter',
				'XPD'    => 'Worldwide Expedited',
			);
		}
		return $method;
	}
	
	function getQuote() {
		global $wpdb;
		$dest = $_SESSION['delivery_country'];
		$wpsc_ups_settings = get_option("wpsc_ups_settings");
		$weight = shopping_cart_total_weight();
    if(isset($_POST['zipcode'])) {
      $zipcode = $_POST['zipcode'];      
      $_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
    } else if(isset($_SESSION['wpsc_zipcode'])) {
      $zipcode = $_SESSION['wpsc_zipcode'];
    }

		if($_GET['debug'] == 'true') {
 			echo('<pre>'.print_r($wpsc_ups_settings,true).'</pre>');
 		}
		$services = $this->getMethod($_SESSION['delivery_country']);
		$ch = curl_init();
		foreach ($services as $key => $service) {
			$Url = join("&", array("http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes",
				"10_action=3",
				"13_product=".$key,
				"14_origCountry=".get_option('base_country'),
				"15_origPostal=".get_option('base_zipcode'),
				"19_destPostal=" . $zipcode,
				"22_destCountry=".$_SESSION['delivery_country'],
				"23_weight=" . $weight,
				"47_rate_chart=Regular Daily Pickup",
				"48_container={$wpsc_ups_settings['48_container']}",
				"49_residential={$wpsc_ups_settings['49_residential']}",
				"document=01",
				"billToUPS=no")
			);
// 			exit('<pre>'.print_r($Url,1)."</pre>");s
			curl_setopt($ch, CURLOPT_URL, $Url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$Results[]=curl_exec($ch);
		}
		curl_close($ch);
// 		$Result = explode("%", $Results);
		//echo ('<pre>'.print_r($Results,1)."</pre>");
		foreach($Results as $result) {
			$result = explode("%", $result);
// 			echo ('--><pre>'.print_r($pre,1)."</pre>");
			if ($services[$result[1]] != ''){
				if ((($result[1]=='XPR') && ($pre == 'XPR')) || (($result[1]=='XDM') && ($pre == 'XDM')) || (($result[1]=='1DP') && ($pre == '1DP')) || (($result[1]=='1DM') && ($pre == '1DM')) || (($result[1]=='1DA') && ($pre == '1DA')) || (($result[1]=='2DA') && ($pre == '2DA')))
					$shipping_list[] = array($services[$result[1]."L"] => $result[8]);
				else if (($result[1]=='GND') && ($pre == 'GND'))
					$shipping_list[] = array($services[$result[1]."RES"] => $result[8]);
				else
					$shipping_list[] = array($services[$result[1]] => $result[8]);
				$pre = $result[1];
			}
		}
		return $shipping_list;
// 		exit('---><pre>'.print_r($shipping_list,1)."</pre>");
		
		$Err = substr($Result[0], -1);
		switch($Err) {
			case 3:
			$ResCode = $Result[10];
			break;

			case 4:
			$ResCode = $Result[10];
			break;

			case 5:
			$ResCode = $Result[1];
			break;

			case 6:
			$ResCode = $Result[1];
			break;
		}

		if(!$ResCode) {
			$ResCode = "An error occured.";
		}
		$fuelSurcharge = $ResCode * .0625;
		$ResCode = $ResCode + $fuelSurcharge;
	}
}
$ups = new ups();
$wpsc_shipping_modules[$ups->getInternalName()] = $ups;
?>