<?php
$purchlogs = new wpsc_purchaselogs();
function wpsc_formdata(){
	global $purchlogs;
	$formdata = $purchlogs->getall_formdata();
	//echo 'Form Data : <pre>'.print_r($formdata, true).'</pre>';

}

function wpsc_test_purchlogs(){
	global $purchlogs;
	wpsc_formdata();
	//exit('purchase item: <pre>'.print_r($purchlogs->purchitem, true).'</pre>');
}
function wpsc_have_purch_items(){
	global $purchlogs;
	return $purchlogs->have_purch_items();
}
function wpsc_the_purch_item(){
	global $purchlogs;
	return $purchlogs->the_purch_item();
}
function wpsc_the_purch_item_price(){
	global $purchlogs;
	return $purchlogs->purchitem->totalprice;
}
function wpsc_the_purch_item_id(){
	global $purchlogs;
	//exit('<pre>'.print_r($purchlogs->purchitem,true).'</pre>');
	return $purchlogs->purchitem->id;
}
function wpsc_the_purch_item_date(){
	global $purchlogs;
	return date('M d Y',$purchlogs->purchitem->date);
}
function wpsc_the_purch_item_name(){
	global $purchlogs;
	return $purchlogs->the_purch_item_name();
}
function wpsc_the_purch_item_details(){
	global $purchlogs;
	return $purchlogs->the_purch_item_details();
}

//status loop functions
function wpsc_have_purch_items_statuses(){
	global $purchlogs;
	return $purchlogs->have_purch_status();
}
function wpsc_the_purch_status(){
	global $purchlogs;

	return $purchlogs->the_purch_status();
}
function wpsc_the_purch_item_statuses(){
	global $purchlogs;
	return $purchlogs->the_purch_item_statuses();
}
function wpsc_the_purch_item_status(){
	global $purchlogs;
	return $purchlogs->the_purch_item_status();
}
function wpsc_the_purch_status_id(){
	global $purchlogs;
//	exit(print_r($purchlogs->purchstatus, true));
	return $purchlogs->purchstatus->id;
}
function wpsc_is_checked_status(){
	global $purchlogs;
	return $purchlogs->is_checked_status();
}
function wpsc_the_purch_status_name(){
	global $purchlogs;
	//exit(print_r($purchlogs->purchstatus, true));
	return $purchlogs->purchstatus->name;
}
function wpsc_purchlogs_getfirstdates(){
	global $purchlogs;
	$dates = $purchlogs->getdates();
	foreach($dates as $date){
		$cleanDate = date('d M', $date['start']);
		$fDate .= '<option value='.$date['start'].'_'.$date['end'].'>'.$cleanDate.'</option>';
	}
//	exit($i);
	return $fDate;
}
function wpsc_change_purchlog_view(){

}
wpsc_test_purchlogs();



/**
 * WP eCommerce purchaselogs AND purchaselogs_items class
 *
 * These is the classes for the WP eCommerce purchase logs,
 * The purchaselogs class handles adding, removing and adjusting details in the purchaselogs,
 *The purchaselogs_items class handles adding, removing and adjusting individual item details in the purchaselogs,
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
	
	var $form_data;
	
	var $purch_item_count;
	//individual purch log variables
	var $allpurchaselogs;
	var $currentitem = -1;
	var $purchitem;
	
	//used for purchase options
	var $currentstatus = -1;
	var $purch_status_count;
	var $allpurchaselogstatuses;
	
	/* Constructor function*/
	function wpsc_purchaselogs(){
		$dates = $this->getdates();
		$purchaselogs = $this->get_purchlogs($dates);
		$this->allpurchaselogs = $purchaselogs;
	//	$this->the_purch_item();
		$this->purch_item_count = count($this->allpurchaselogs);
		$statuses = $this->the_purch_item_statuses();
		
	}
	
	function get_purchlogs($dates){
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
          $purchase_logs = $wpdb->get_results($sql) ;
        
		}
	  }
		//  exit('<pre>'.print_r($purchase_logs, true).'</pre>');
		  return $purchase_logs;
	}
	
	function  getall_formdata(){
		global $wpdb;
		$form_sql = "SELECT * FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `active` = '1' AND `display_log` = '1';";
    	$form_data = $wpdb->get_results($form_sql,ARRAY_A);
    	$this->form_data = $form_data;
    	return $form_data;
    }

	/*
	 * This finds the earliest time in the shopping cart and sorts out the timestamp system for the month by month display
	 * or if there was a filter applied use the filter to sort the dates.
	 */  
	function getdates(){
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
	
	function deletelog($deleteid){
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
		  return '<div id="message" class="updated fade"><p>'.TXT_WPSC_THANKS_DELETED.'</p></div>';
		}
		
	}
	//individual purchase log functions
	function next_purch_item(){
		$this->currentitem++;
		
		$this->purchitem = $this->allpurchaselogs[$this->currentitem];
		return $this->purchitem ;
	}
	
	function the_purch_item() {
		$this->purchitem = $this->next_purch_item();
		//if ( $this->currentitem == 0 ) // loop has just started

	}
	
	function have_purch_items() {	
		if ($this->currentitem + 1 < $this->purch_item_count) {
			return true;
		} else if ($this->currentitem + 1 == $this->purch_item_count && $this->purch_item_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_purch_items();
		}
		return false;
	}
	
	function rewind_purch_items() {
		$this->currentitem = -1;
		if ($this->purch_item_count > 0) {
			$this->purchitem = $this->allpurchaselogs[0];
		}
	}
	
	function the_purch_item_statuses(){
		global $wpdb;
		$sql = "SELECT name,id FROM ".WPSC_TABLE_PURCHASE_STATUSES;
		$statuses = $wpdb->get_results($sql);
		$this->purch_status_count = count($statuses);
		$this->allpurchaselogstatuses = $statuses;
		return $statuses;
	
	}
	// purchase status loop functions
	function next_purch_status(){
		$this->currentstatus++;
		
		$this->purchstatus = $this->allpurchaselogstatuses[$this->currentstatus];
		return $this->purchstatus ;
	}
	
	function the_purch_status() {
		$this->purchstatus = $this->next_purch_status();
		//if ( $this->currentitem == 0 ) // loop has just started

	}
	
	function have_purch_status() {	
		
		if ($this->currentstatus + 1 < $this->purch_status_count) {
			return true;
		} else if ($this->currentstatus + 1 == $this->purch_status_count && $this->purch_status_count > 0) {
			// Do some cleaning up after the loop,
			$this->rewind_purch_status();
		}
		return false;
	}
	
	function rewind_purch_status() {
		$this->currentstatus = -1;
		if ($this->purch_status_count > 0) {
			$this->purchstatus = $this->allpurchaselogstatuses[0];
		}
	}
	function is_checked_status(){
		if($this->purchstatus->id == $this->purchitem->processed){
			return 'selected="selected"';
		}else{
			return '';
		}
	}
/*
	function the_purch_item_status(){
		//exit('Purchlog status'.$this->purchitem->processed);
		return $this->purchitem->processed;
	}
	
*/
	function the_purch_item_name(){
		global $wpdb;
		foreach($this->form_data as $formdata){
			if(in_array('Email', $formdata)){
				$emailformid = $formdata['id'];
			}
			if(in_array('First Name', $formdata)){
				$fNameformid = $formdata['id'];
			}
			if(in_array('Last Name', $formdata)){
				$lNameformid = $formdata['id'];
			}
		}
		//exit($emailformid.' '.$fNameformid.' '.$lNameformid);
		//$values = array();
		$sql = "SELECT value FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." WHERE log_id=".$this->purchitem->id." AND form_id=".$emailformid;
		$email = $wpdb->get_var($sql);
		$sql = "SELECT value FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." WHERE log_id=".$this->purchitem->id." AND form_id=".$fNameformid;
		$fname = $wpdb->get_var($sql);
		$sql = "SELECT value FROM ".WPSC_TABLE_SUBMITED_FORM_DATA." WHERE log_id=".$this->purchitem->id." AND form_id=".$lNameformid;
		$lname = $wpdb->get_var($sql);
		$namestring = $fname.' '.$lname.' ('.$email.') ';
		//exit($fname.' '.$lname.' ('.$email.') ');
		return $namestring;
		/*
		exit($sql);
		exit('<pre>'.print_r($this->form_data, true).'</pre>');
		*/
	
	}
	
	function the_purch_item_details(){
		global $wpdb;
		$sql="SELECT SUM(quantity) FROM ".WPSC_TABLE_CART_CONTENTS." WHERE purchaseid=".$this->purchitem->id;
		$sum = $wpdb->get_var($sql);
		return $sum;
	
	}
}

class wpsc_purchaselogs_items{


	
	function wpsc_purchaselogs_items(){
	
	}
	

}
?>