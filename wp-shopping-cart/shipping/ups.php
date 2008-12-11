<?php
class ups {
	var $internal_name, $name;
	function ups () {
		$this->internal_name = "ups";
		$this->name="UPS";
		$this->is_external=true;
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
		$output="<tr><td colspan='2'>No info needed, but if you want UPS to work please set your base zipcode in the settings page.</td></tr>";
		return $output;
	}
	
	function submit_form() {
		return true;
	}
	
	function getMethod($dest) {
		if ($dest == 'US') {
			$method= array(
				'1DM'    => 'Next Day Air Early AM',
				'1DML'   => 'Next Day Air Early AM Letter',
				'1DA'    => 'Next Day Air',
				'1DAL'   => 'Next Day Air Letter',
// 				'1DAPI'  => 'Next Day Air Intra (Puerto Rico)',
				'1DP'    => 'Next Day Air Saver',
				'1DPL'   => 'Next Day Air Saver Letter',
				'2DM'    => '2nd Day Air AM',
				'2DML'   => '2nd Day Air AM Letter',
				'2DA'    => '2nd Day Air',
				'2DAL'   => '2nd Day Air Letter',
				'3DS'    => '3 Day Select',
				'GND'    => 'Ground',
// 				'GNDCOM' => 'Ground Commercial',
				'GNDRES' => 'Ground Residential',
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
				'XPRL'   => 'Worldwide Express Letter',
				'XDM'    => 'Worldwide Express Plus',
				'XDML'   => 'Worldwide Express Plus Letter',
				'XPD'    => 'Worldwide Expedited',
			);
		}
		return $method;
	}
	
	function getQuote() {
		global $wpdb;
		$dest = $_SESSION['delivery_country'];
		$weight = shopping_cart_total_weight();
// 		exit('<pre>'.print_r($weight,1)."</pre>");
		$services = $this->getMethod($_POST['country']);
		$ch = curl_init();
		foreach ($services as $key => $service) {
			$Url = join("&", array("http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes",
				"10_action=3",
				"13_product=".$key,
				"14_origCountry=".get_option('base_country'),
				"15_origPostal=".get_option('base_zipcode'),
				"19_destPostal=" . $_POST['zipcode'],
				"22_destCountry=".$_POST['country'],
				"23_weight=" . $weight,
				"47_rate_chart=Regular Daily Pickup",
				"48_container=00",
				"49_residential=01",
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