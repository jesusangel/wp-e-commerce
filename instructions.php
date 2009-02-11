<?php
global $wpdb;
?>
<div class="wrap">
<?php




$variations_processor = new nzshpcrt_variations;

$variation_values = $variations_processor->falsepost_variation_values(1);
echo "<pre>";
echo print_r($variation_values,true);
echo "</pre>";



?>
</div>