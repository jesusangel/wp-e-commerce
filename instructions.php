<?php
global $wpdb;
?>
<div class="wrap">
<?php
include('updates/database_template.php');




echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif;', font-size:12px;>";
;

foreach($wpsc_database_template as $key => $table_data) {
  
	$table_name = $key;
  if($table_data['previous_names'] != null) {
    $table_name = $table_data['previous_names'];
  }
  
  $table_name = preg_replace("/^wp_/", "", $table_name);
  echo $table_name." \n";
}
echo "</pre>";


/*
echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif;', font-size:12px;>";
foreach($wpsc_database_template as $key => $table_data) {
  $patterns = array("/^{$wpdb->prefix}(?!wpsc)/", "/^{$wpdb->prefix}/", "/associations/", "/priceandstock/", "/also_bought_product/", "/collect_data_forms/");
	$replacements = array("{$wpdb->prefix}wpsc_","{\$wpdb->prefix}", "assoc", "properties", "also_bought", "checkout_forms");
  $old_table_name = str_replace("wp_","{\$wpdb->prefix}",$key);
  $new_table_name = preg_replace($patterns,$replacements,$key);
  $constant_name = strtoupper(str_replace("{\$wpdb->prefix}wpsc_", "wpsc_table_", $new_table_name));
  
  echo "// code to create or update the $new_table_name table\n";
  echo "\$table_name = $constant_name;\n";
  foreach($table_data as $data_type => $data_group) {
   if(is_array($data_group)) {
		foreach($data_group as $data_row_name => $data_row) {
			echo "\$wpsc_database_template[\$table_name]['$data_type']['$data_row_name'] = \"$data_row\";\n";
		}
   } else {
		$data_group = preg_replace("/^wp_/", "{\$wpdb->prefix}", $data_group);
		echo "\$wpsc_database_template[\$table_name]['$data_type'] = \"$data_group\";\n";
   }
  }
  
  echo "\n\r";
}
echo "</pre>";
*/


?>
</div>