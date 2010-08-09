<?php
	// This is a config file for PHPtidy
	// http://developer.berlios.de/projects/phptidy/

	$project_files = array('*.php',
	                       'merchants/*.php',	
	                       'shipping/*.php',	
	                       'merchants/*.php',	
	                       'updates/*.php',	
	                       'widgets/*.php',	
	                       'wpsc-includes/*.php',	
	                       'wpsc-admin/*.php',	
	                       'wpsc-admin/includes/*.php',	
	                       'wpsc-admin/includes/settings-pages/*.php');

	$default_package = "WP e-Commerce";
	$add_file_docblock = TRUE;
	$add_functions_docblock = TRUE;
	$add_doctags = TRUE;
	$fix_docblock_format = TRUE;
	$fix_docblock_space = TRUE;
	$add_blank_lines = FALSE;
	
?>
