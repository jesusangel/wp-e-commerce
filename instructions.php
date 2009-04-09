<?php
global $wpdb;
?>
<div class="wrap">
<?php
$purchase_log_id = 144;

echo wpsc_decrement_claimed_stock($purchase_log_id);


//echo "<pre>".print_r($_SESSION,true)."</pre>";
?>
</div>