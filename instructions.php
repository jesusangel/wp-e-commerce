<?php
global $wpdb;
?>
<div class="wrap">
<?php
define('SAVEQUERIES', true);
$wpdb->queries = array();

		$variation_processor = new nzshpcrt_variations();
echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif;', font-size:8px;>";

$post['edit_var_val'][1][3]	 = 1;
$post['edit_var_val'][1][4] = 1;
$post['edit_var_val'][2][5] = 1;
$post['edit_var_val'][2][6] = 1;	
$post['edit_var_val'][2][7] = 1;	
$post['edit_var_val'][2][8] = 1;	
$post['edit_var_val'][2][9] = 1;	
 $post['edit_var_val'][3][11] = 1;	
  $post['edit_var_val'][3][12] = 1;	
$post['limited_stock'] = 	'false';

$post['list_variation_values'] = 	'true';

$post['product_id']	= 63;

$post['selected_price']	 = 	'0.00';

$post['variations'][1]	 = 1;
$post['variations'][2] = 1;
$post['variations'][3]	 = 1;
$_POST = $post;
//print_r($post);

foreach((array)$_POST['variations'] as $variation_id => $checked) {
	$variations_selected[] = (int)$variation_id;
}

	if(is_numeric($post['product_id'])) {
		$product_id = absint($post['product_id']);
		$selected_price = (float)$post['selected_price'];
		
		// variation values housekeeping
		$completed_variation_values = $variation_processor->edit_product_values($product_id,$post['edit_var_val'], $selected_price);

		print_r($completed_variation_values);

		// get all the currently associated variations from the database
		$associated_variations = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `type` IN ('product') AND `associated_id` IN ('{$product_id}')", ARRAY_A);

		$variations_still_associated = array();
		foreach((array)$associated_variations as $associated_variation) {
		// remove variations not checked that are in the database
			if(array_search($associated_variation['variation_id'], $variations_selected) === false) {
					$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_ASSOC."` WHERE `id` = '{$associated_variation['id']}' LIMIT 1");
					$wpdb->query("DELETE FROM `".WPSC_TABLE_VARIATION_VALUES_ASSOC."` WHERE `product_id` = '{$product_id}' AND `variation_id` = '{$associated_variation['variation_id']}' ");
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
		//echo "/* ".print_r($associated_variations,true)." */\n\r";
		$output = $variation_processor->variations_grid_view($product_id,  (array)$completed_variation_values);

	} else {
		if(count($variations_selected) > 0) {
			// takes an array of variations, returns a form for adding data to those variations.
			if((float)$post['selected_price'] > 0) {
					$selected_price = (float)$post['selected_price'];
			}
			
			$limited_stock = false;
			if($post['limited_stock'] == 'true') {
				$limited_stock = true;
			}
			
		$output = $variation_processor->variations_add_grid_view((array)$variations_selected, (array)$completed_variation_values, $selected_price, $limited_stock);

		} else {
			echo "add_variation_combinations_html = \"\";\n";
		}
}



print_r($wpdb->queries);

echo "</pre>";
echo $output;
?>
</div>