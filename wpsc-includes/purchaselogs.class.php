<?php
/**
 * WP eCommerce purchaselogs class
 *
 * These is the classes for the WP eCommerce purchase logs,
 * The purchaselogs class handles adding, removing and adjusting details in the purchaselogs,
 *
 *
 * @package wp-e-commerce
 * @since 3.7
 * @subpackage wpsc-cart-classes 
*/

class wpsc_purchaselogs{
	
	var $earliest_timestamp;
	var $current_timestamp;
	var $earliest_year;
	var $current_year;
	/* Constructor function*/
	function wpsc_purchaselogs(){
		$dates = $this->wpsc_getdates();
		$this->wpsc_getall_formdata();
		$purchaselogs = $this->wpsc_get_purchlogs($dates);
	}
	
	function wpsc_get_purchlogs($dates){
		global $wpdb;
	   foreach($dates as $date_pair){
        if(($date_pair['end'] >= $this->earliest_timestamp) && ($date_pair['start'] <= $this->current_timestamp)) {   
          $sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$date_pair['start']."' AND '".$date_pair['end']."' ORDER BY `date` DESC";
		  if ($paidlog) {
				$sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$date_pair['start']."' AND '".$date_pair['end']."' AND `processed` >= '2' ORDER BY `date` DESC";
		  } else if($_GET['filteremail']) {
				$sql = "SELECT DISTINCT `".WPSC_TABLE_PURCHASE_LOGS."` . * FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` LEFT JOIN `".WPSC_TABLE_PURCHASE_LOGS."` ON `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id` = `".WPSC_TABLE_PURCHASE_LOGS."`.`id` WHERE `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`value` IN ( '".$wpdb->escape($_GET['filteremail'])."' ) AND `".WPSC_TABLE_PURCHASE_LOGS."`.`date` BETWEEN '".$date_pair['start']."' AND '".$date_pair['end']."' ORDER BY `".WPSC_TABLE_PURCHASE_LOGS."`.`date` DESC;";
		  } else if ($_GET['filter']=='affiliate') {
				$sql = "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date` BETWEEN '".$date_pair['start']."' AND '".$date_pair['end']."' AND `affiliate_id` IS NOT  NULL ORDER BY `date` DESC";
		  }
          $purchase_log = $wpdb->get_results($sql,ARRAY_A) ;
         // exit('<pre>'.print_r($purchase_log, true).'</pre>');
		}
		}
	}
	
	function  wpsc_getall_formdata(){
	global $wpdb;
		$form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `display_log` = '1';";
    	$form_data = $wpdb->get_results($form_sql,ARRAY_A);
    	
    	return $form_data;
    }

	/*
	 * This finds the earliest time in the shopping cart and sorts out the timestamp system for the month by month display
	 * or if there was a filter applied use the filter to sort the dates.
	 */  
	function wpsc_getdates(){
		global $wpdb;
		$earliest_record_sql = "SELECT MIN(`date`) AS `date` FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `date`!=''";
		$earliest_record = $wpdb->get_results($earliest_record_sql,ARRAY_A) ;
		
		$this->current_timestamp = time();
		$this->earliest_timestamp = $earliest_record[0]['date'];
		
		$this->current_year = date("Y");
		$this->earliest_year = date("Y",$this->earliest_timestamp);
		
		$j = 0;
		for($year = $this->current_year; $year >= $this->earliest_year; $year--) {
		  for($month = 12; $month >= 1; $month--) {          
		    $this->start_timestamp = mktime(0, 0, 0, $month, 1, $year);
		    $this->end_timestamp = mktime(0, 0, 0, ($month+1), 1, $year);
		    if(($this->end_timestamp >= $this->earliest_timestamp) && ($this->start_timestamp <= $this->current_timestamp)) {
		      $date_list[$j]['start'] = $this->start_timestamp;
		      $date_list[$j]['end'] = $this->end_timestamp;
		      $j++;
				}
			}
		}
		
		if($_GET['filter'] !== 'true') {
		  if(is_numeric($_GET['filter'])) {
		    $max_number = $_GET['filter'];
			} else {
				if ($_GET['filter']=='paid') {
					$paidlog=true;
				}
				$max_number = 3;
			}
		  
		  $date_list = array_slice($date_list, 0, $max_number);
		}
		return $date_list;
	}
	
	function wpsc_deletelog($deleteid){
	//change $_GET[deleteid] to $deleteid
		global $wpdb;
		if(is_numeric($deleteid)) {
		  
		  $delete_log_form_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$deleteid'";
		  $cart_content = $wpdb->get_results($delete_log_form_sql,ARRAY_A);
		  foreach((array)$cart_content as $cart_item) {
		    $cart_item_variations = $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_ITEM_VARIATIONS."` WHERE `cart_id` = '".$cart_item['id']."'", ARRAY_A);
			}
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='$deleteid'");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_SUBMITED_FORM_DATA."` WHERE `log_id` IN ('$deleteid')");
		  $wpdb->query("DELETE FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `id`='$deleteid' LIMIT 1");
		  echo '<div id="message" class="updated fade"><p>'.TXT_WPSC_THANKS_DELETED.'</p></div>';
		}
		
	}

}
?>