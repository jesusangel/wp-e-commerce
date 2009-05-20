<?php
global $wpdb;
?>
<div class="wrap">
<?php
define('SAVEQUERIES', true);
//$wpdb->queries = array();
$product_data = $wpdb->get_results("SELECT * FROM  `".WPSC_TABLE_PRODUCT_LIST."` WHERE `image` = `id`", ARRAY_A);
echo "<pre style='font-family:\"Lucida Grande\",Verdana,Arial,\"Bitstream Vera Sans\",sans-serif;', font-size:8px;>";
echo print_r($product_data,true);
echo "</pre>";
echo $output;
?>
</div>