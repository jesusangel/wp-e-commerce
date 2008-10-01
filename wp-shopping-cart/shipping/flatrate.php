<?php
class flatrate {
	var $internal_name, $name;
	function flatrate () {
		$this->internal_name = "flatrate";
		$this->name="Flat Rate";
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
		$shipping = get_option('flat_rates');
		$output = "<tr><td colspan='1'><strong>Base Local</strong></td>";
		if (get_option('base_country')=='NZ') {
			$output .= "<tr><td>South Island</td><td>$<input type='text' name='shipping[southisland]' value='{$shipping['southisland']}'></td></tr>";
			$output .= "<tr><td>North Island</td><td>$<input type='text' name='shipping[northisland]'  value='{$shipping['northisland']}'></td></tr>";
		} else {
			$output .= "<td>$<input type='text' name='shipping[local]' value='{$shipping['local']}'></td></tr>";
		}
		$output.= "<tr><td colspan='2'><strong>International</strong></td></tr>";
		if (get_option('base_country')=='US') {
			$output .= "<tr><td>Continental 48 States</td><td>$<input type='text' name='shipping[continental]' value='{$shipping['continental']}'></td></tr>";
			$output .= "<tr><td>All 50 States</td><td>$<input type='text' name='shipping[all]'  value='{$shipping['all']}'></td></tr>";
			$output .= "<tr><td>Canada</td><td>$<input type='text' name='shipping[canada]'  value='{$shipping['canada']}'></td></tr>";
		} else {
			$output .= "<tr><td>North America</td><td>$<input type='text' name='shipping[northamerica]'  value='{$shipping['northamerica']}'></td></tr>";
		}
		$output .= "<tr><td>South America</td><td>$<input type='text' name='shipping[southamerica]'  value='{$shipping['southamerica']}'></td></tr>";
		$output .= "<tr><td>Asia and Pacific</td><td>$<input type='text' name='shipping[asiapacific]'  value='{$shipping['asiapacific']}'></td></tr>";
		$output .= "<tr><td>Europe</td><td>$<input type='text' name='shipping[europe]'  value='{$shipping['europe']}'></td></tr>";
		$output .= "<tr><td>Africa</td><td>$<input type='text' name='shipping[africa]'  value='{$shipping['africa']}'></td></tr>";
		return $output;
	}
	
	function submit_form() {
		$shippings = $_POST['shipping'];
		update_option('flat_rates',$shippings);
		return true;
	}
	
	function getQuote() {
		global $wpdb;
		if (isset($_POST['country']))
			$country = $_POST['country'];
		else
			$country = $_SESSION['selected_country'];
		if (get_option('base_country') != $country) {
			$results = $wpdb->get_var("SELECT continent FROM {$wpdb->prefix}currency_list WHERE ISOCODE='{$country}'");
			$flatrates = get_option('flat_rates');
			
			if ($flatrates != '') {
// 				exit($results);
				return array(array("Flat Rate"=>(float)$flatrates[$results]));
			}
		}
	}
}
$flatrate = new flatrate();
$wpsc_shipping_modules[$flatrate->getInternalName()] = $flatrate;