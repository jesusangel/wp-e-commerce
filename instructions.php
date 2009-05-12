<?php
global $wpdb;
?>
<div class="wrap">
<?php
define('SAVEQUERIES', true);
$wpdb->queries = array();

echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif;', font-size:12px;>";

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

$post['product_id']	= '55';

$post['selected_price']	 = 	'0.00';

$post['variations'][1]	 = 1;
$post['variations'][2] = 1;
$post['variations'][3]	 = 1;
$_POST = $post;

print_r($_POST);














print_r($wpdb->queries);

echo "</pre>";
?>
</div>