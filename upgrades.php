<?php
/*
 * This is not meant to be pretty, just to work. Any upgrades in the upgrades folder will 
 * automatically be included.
 * 
 * No activation/deactivation feature at present, but the idea is basically that the upgrades should
 * work the same way as the plugins.
 * 
 * All of the functions in wpsc-includes/upgrades.php are copied directly from 
 * wp-admin/includes/plugin.php and the main modifications was in terms of paths and replacing 'plugin'
 * with 'upgrade'.
 * 
 */

require_once("wpsc-includes/upgrades.php");
$upgrades = get_upgrades();
foreach ($upgrades as $path=>$upgrade) {
	$upgrade_file = WPSC_UPGRADES_DIR . '/' . $path;
	require_once($upgrade_file);
}
//exit(print_r($upgrades, true));
?>
