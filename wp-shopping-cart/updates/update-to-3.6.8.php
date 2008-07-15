<?php
/*
 * Updates to 3.6.8
*/


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}product_list` LIKE 'weight';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_list` ADD `weight` INT( 11 ) NOT NULL DEFAULT 0 AFTER `price`;");
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_list` ADD `weight_unit` VARCHAR( 10 ) NOT NULL AFTER `weight`;");
}


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}cart_contents` LIKE 'files';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}cart_contents` ADD `files` TEXT NOT NULL AFTER `no_shipping`");
}
?>