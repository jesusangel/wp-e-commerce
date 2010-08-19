<?php

/**
	 * WP eCommerce theme porting class
	 *
	 * This class is responsible for moving all of the /theme folder from the plugin folder to the wp-themes folder on new installs
	 * On upgrades, responsible for checking appropriate folders for themes, converting and porting to wp/themes folder
	 *
	 * @package wp-e-commerce
	 * @since 3.8
 */
 

/* 

Roadmap

1. If WP Theme has templates use those (only for new installs(i.e. no theme in uploads folder), theme files will be ported from plugin folder to active theme on init).
2. If WP Theme doesn't have active theme and we're an upgrade, do the following(with option in settings>presentation page to move to WP Theme)
	- check for active theme in /uploads/wpsc folder
		- if exists, port active theme to wpsc theme and move from uploads/themes to wp-content/themes/activetheme
		- if does not exist, then move plugin default theme from plugin to themes
3. Provide a 'backup your theme' Option that copies your WP Theme to your uploads directory purely for backup purposes...

@TODO - 8.19
	- Make sure this all runs on admin_init
	- 
	
*/

 class wpsc_theming {
//Note - use function wpsc_recursive_copy()
	var $active_wp_theme;
	var $active_wpsc_theme;
	var $theme_file_prefix;
	
	function wpsc_theming() {
		//Construct
		$theme_file_prefix = 'wpsc-';
		
		if($this->theme_exists()) {
			return;
		} else {
		
		
			//WP-WPSC theme doesn't exist, so let's figure out where we're porting from, either the plugin directory or the wpsc-themes directory
			$theme_location = $this->theme_location();
			$active_wp_theme = get_stylesheet_directory();
			
			//Now that we have the theme location, let's copy it over to the themes directory, we'll modify it from there.

			wpsc_recursive_copy($theme_location, $active_wp_theme.'/wpsc');
			
			$path = $active_wp_theme.'/wpsc'; 
			$dh = opendir($path); 

			$i=1; 
			while (($file = readdir($dh)) !== false) {
				if($file != "." && $file != ".." && !strstr($file, ".svn") && !strstr($file, "images") && !strstr($file, $theme_file_prefix)) {
					rename($path."/".$file, $path."/".$theme_file_prefix.$file); 
					$i++; 
				} 
			} 
			closedir($dh);
			
			/*
				$file_data = "Stuff you want to add\n"
				$file_data .= file_get_contents('database.txt');
				file_put_contents('database.txt', $file_data);
			*/
		}	
		
	}
	
	/**
	 * Checks to see whether theme files exist in the current WP theme folder
	 * @access public 
	 *
	 * @since 3.8
	 * @param None
	 * @return None
	 */
	
	function theme_exists() {
	global $wpsc_themes_dir;
		$active_wpsc_theme = $wpsc_themes_dir;
		
		if(file_exists($active_wp_theme.'/wpsc/'.$theme_file_prefix.'functions.php') && !is_dir($active_wpsc_theme)) {
		
			//if theme already exists, we're set, do nothing - this should also indicate a new install
			return true;
			
		} else {
			return false;
		}
	}
	
	
 /* Determines the current theme location
	 * @access public 
	 *
	 * @since 3.8
	 * @param None
	 * @return None
*/
	function theme_location() {
		global $wpsc_themes_dir;
			$wpsc_theme_folder = get_option('wpsc_selected_theme');
			$active_wpsc_dir = $wpsc_themes_dir.'/'.$wpsc_theme_folder;
			
			//First, check if theme exists in uploads folder, if so, that's theme location - if it's not there, then the theme location will be the plugins folder.
			
			if(file_exists($active_wpsc_dir.'/functions.php')) {
			
				$theme_location = $active_wpsc_dir;
				
			} else {
				$theme_location = WPSC_FILE_PATH . '/themes/default/';
			}
			
			return $theme_location;
	}
	
 
 }

$theming = new wpsc_theming;

?>