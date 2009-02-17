<?php
global $wpdb;
?>
<div class="wrap">
<?php
$wpdb->show_errors = true;


$_POST += array (
  'admin' => 'true',
  'ajax' => 'true',
  'list_variation_values' => 'true',
  'product_id' => '5',
  'selected_price' => '42.00',
  'limited_stock' => 'true',
  'edit_variation_values' => 
  array (
    3 => '1',
    4 => '1',
    22 => '1',
    23 => '1',
    5 => '1',
    6 => '1',
    7 => '1',
    8 => '0',
    9 => '0',
    10 => '0',
    11 => '0',
    12 => '0',
    13 => '0',
    15 => '0',
    14 => '0',
    17 => '0',
    18 => '0',
    19 => '0',
  ),
  'variations' => 
  array (
    1 => '1',
    2 => '1',
  ),
);

/*
$output .= "<pre>";
$output .= print_r($_POST,true);
$output .= "</pre>";*/

echo "<pre>";
if(($_POST['list_variation_values'] == "true")) {
   		// retrieve the forms for associating variations and their values with products
		$variation_processor = new nzshpcrt_variations();
		$variations_selected = array();
    	foreach((array)$_POST['variations'] as $variation_id => $checked) {
    		$variations_selected[] = (int)$variation_id;
    	}

    	if(is_numeric($_POST['product_id'])) {
      		$product_id = (int)$_POST['product_id'];
					$selected_price = (float)$_POST['selected_price'];
      		
       		// variation values housekeeping
      		$variation_processor->edit_product_values($product_id,$_POST['edit_variation_values'], $selected_price);
      

      		// get all the currently associated variations from the database
      		$associated_variations = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}variation_associations` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}')", ARRAY_A);
      
      		$variations_still_associated = array();
      		foreach((array)$associated_variations as $associated_variation) {
			  	// remove variations not checked that are in the database
        		if(array_search($associated_variation['variation_id'], $variations_selected) === false) {
          			$wpdb->query("DELETE FROM `{$wpdb->prefix}variation_associations` WHERE `id` = '{$associated_variation['id']}' LIMIT 1");
          			$wpdb->query("DELETE FROM `{$wpdb->prefix}variation_values_associations` WHERE `product_id` = '{$product_id}' AND `variation_id` = '{$associated_variation['variation_id']}' ");
        		} else {
          			// make an array for adding in the variations next step, for efficiency
          			$variations_still_associated[] = $associated_variation['variation_id'];
        		}
      		}
       
					foreach((array)$variations_selected as $variation_id) {
			  	// add variations not already in the database that have been checked.
        		$variation_values = $variation_processor->falsepost_variation_values($variation_id);
        		if(array_search($variation_id, $variations_still_associated) === false) {
      	  			$variation_processor->add_to_existing_product($product_id,$variation_values);
        		}
      		}
      		//$output .= "/* ".print_r($associated_variations,true)." */\n\r";
					$output .= "edit_variation_combinations_html = \"".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_grid_view($product_id,  (array)$_POST['edit_variation_values'])))."\";\n";

    	} else {
      		if(count($variations_selected) > 0) {
        		// takes an array of variations, returns a form for adding data to those variations.
        		if((float)$_POST['selected_price'] > 0) {
          			$selected_price = (float)$_POST['selected_price'];
        		}
        		
						$limited_stock = false;
        		if($_POST['limited_stock'] == 'true') {
							$limited_stock = true;
        		}
        		
						$output .= "add_variation_combinations_html = \"".TXT_WPSC_EDIT_VAR."<br />".str_replace(array("\n","\r"), array('\n','\r'), addslashes($variation_processor->variations_add_grid_view((array)$variations_selected, (array)$_POST['edit_variation_values'], $selected_price, $limited_stock)))."\";\n";

      		} else {
        		$output .= "add_variation_combinations_html = \"\";\n";
      		}
		}
// 		exit();
	}
$output .= "</pre>";
?>
</div>