<?php
class nzpost {
	var $internal_name, $name;
	function nzpost () {
		$this->internal_name = "nzpost";
		$this->name="NZ Post";
		$this->is_external=true;
		$this->requires_curl=true;
		$this->requires_weight=false;
		$this->needs_zipcode=false;
		$this->xml2Array;
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
		$output = "<label for='wpsc_nzpost_trackAPI'>Tracking API: </label>";
		$output .= "<input type='text' value='".get_option('wpsc_nzpost_trackAPI')."' name='wpsc_nzpost_trackAPI' />";
		return $output;
	}
	
	function submit_form() {
		global $wpdb;
		if ($_POST['wpsc_nzpost_trackAPI'] != '') {
			$value = $wpdb->escape($_POST['wpsc_nzpost_trackAPI']);
			update_option('wpsc_nzpost_trackAPI', $value);
		}
		return true;
	}
	function getStatus($trackid){
		require_once(WPSC_FILE_PATH."/wpsc-includes/xmlparser.php");
		$url = 'http://services.nzpost.co.nz/TrackAndTrace.svc/TrackID/';
		$nzposttrackAPI = get_option('wpsc_nzpost_trackAPI');
		$version = '10.1.2.3';
		$trackid = 'JB101069625NZ';
		$url = $url.$nzposttrackAPI.'/'.$version.'/'.$trackid;
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$Results=curl_exec($ch);
			
		curl_close($ch);
		$parser = new xml2array;
		$parsed = $parser->parse($Results);
		$_SESSION['wpsc_nzpost_parsed'] = $parsed;
		$this->xml2Array = $parsed;
		return 	$parsed[0]['children'][0]['children'][2]['tagData'];
	}
	function getMethod() {

	}

	function getQuote() {
		global $wpdb;
		$dest = $_SESSION['delivery_country'];
		$wpsc_ups_settings = get_option("wpsc_ups_settings");
		$weight = wpsc_cart_weight_total();
    if(isset($_POST['zipcode'])) {
      $zipcode = $_POST['zipcode'];      
      $_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
    } else if(isset($_SESSION['wpsc_zipcode'])) {
      $zipcode = $_SESSION['wpsc_zipcode'];
    }

		if($_GET['debug'] == 'true') {
 			echo('<pre>'.print_r($wpsc_ups_settings,true).'</pre>');
 		}
		$shipping_cache_check['zipcode'] = $zipcode;
		$shipping_cache_check['weight'] = $weight;
 		//$_SESSION['wpsc_shipping_cache_check']
		//this is where shipping breaks out of UPS if weight is higher than 150 LBS
		if($weight > 150){
			unset($_SESSION['quote_shipping_method']);
			$shipping_quotes[TXT_WPSC_OVER_UPS_WEIGHT] = 0;
			$_SESSION['wpsc_shipping_cache_check']['weight'] = $weight;
			$_SESSION['wpsc_shipping_cache'][$this->internal_name] = $shipping_quotes;
			$_SESSION['quote_shipping_method'] = $this->internal_name;
			return array($shipping_quotes);
		}
		//$shipping_cache_check = null;
 		if(($_SESSION['wpsc_shipping_cache_check'] === $shipping_cache_check) && ($_SESSION['wpsc_shipping_cache'][$this->internal_name] != null)) {
			$shipping_list = $_SESSION['wpsc_shipping_cache'][$this->internal_name];
 		} else {
			$services = $this->getMethod($_SESSION['wpsc_delivery_country']);
			$ch = curl_init();
			foreach ($services as $key => $service) {
				$Url = join("&", array("http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes",
					"10_action=3",
					"13_product=".$key,
					"14_origCountry=".get_option('base_country'),
					"15_origPostal=".get_option('base_zipcode'),
					"19_destPostal=" . $zipcode,
					"22_destCountry=".$_SESSION['wpsc_delivery_country'],
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
	// 		$Result = explode("%", $Results);;
			$shipping_list = array();
			foreach($Results as $result) {
				$result = explode("%", $result);
	// 			echo ('--><pre>'.print_r($pre,1)."</pre>");
				if ($services[$result[1]] != ''){
					if ((($result[1]=='XPR') && ($pre == 'XPR')) || (($result[1]=='XDM') && ($pre == 'XDM')) || (($result[1]=='1DP') && ($pre == '1DP')) || (($result[1]=='1DM') && ($pre == '1DM')) || (($result[1]=='1DA') && ($pre == '1DA')) || (($result[1]=='2DA') && ($pre == '2DA')))
						$shipping_list += array($services[$result[1]."L"] => $result[8]);
					else if (($result[1]=='GND') && ($pre == 'GND'))
						$shipping_list += array($services[$result[1]."RES"] => $result[8]);
					else
						$shipping_list += array($services[$result[1]] => $result[8]);
					$pre = $result[1];
				}
			}
			//echo ('<pre>'.print_r($shipping_list,1)."</pre>");
			$_SESSION['wpsc_shipping_cache_check']['zipcode'] = $zipcode;
			$_SESSION['wpsc_shipping_cache_check']['weight'] = $weight;
			$_SESSION['wpsc_shipping_cache'][$this->internal_name] = $shipping_list;
		}
		$shipping_list = array_reverse($shipping_list);
		return $shipping_list;
	}
	
	function get_item_shipping() {
	}
}
$nzpost = new nzpost();
$wpsc_shipping_modules[$nzpost->getInternalName()] = $nzpost;
?>