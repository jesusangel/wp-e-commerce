<?php

/**
 * WP eCommerce theme porting class
 *
 * This class is responsible for moving all of the /theme folder from the plugin folder to the wp-themes 
 * folder on new installs
 * On upgrades, responsible for checking appropriate folders for themes, converting and porting to wp/themes folder
 *
 * @package wp-e-commerce
 * @since 3.8
 */
 
 class wpsc_theming {

	var $active_wp_theme;
	var $active_wpsc_theme;
	var $theme_file_prefix;
	var $templates_to_move;
	var $list_of_templates;
	
	function wpsc_theming() {
		//Construct
		global $theme_file_prefix;	
		check_admin_referer('wpsc_copy_themes');
		$template_dir =get_template_directory().'/';
		$this->templates_to_move = $_POST['wpsc_templates_to_port'];
		$this->list_of_templates = wpsc_list_product_templates($template_dir);
		$theme_file_prefix = 'wpsc-';
		
		if($this->theme_exists()) {
			return;
		} else {
			//WP-WPSC theme doesn't exist, so let's figure out where we're porting from, either the plugin directory or the wpsc-themes directory
			
			$theme_location = $this->theme_location();
			$active_wp_theme = get_stylesheet_directory();
			
			//Now that we have the theme location, let's copy it over to the themes directory and mod from there.
			$this->move_theme($theme_location, $active_wp_theme);
			
			//The rest of this is ported from the previous copy_theme function
			$_SESSION['wpsc_themes_copied'] = true;
			
			$sendback = wp_get_referer();
			//$sendback = add_query_arg('tab', $_SESSION['wpsc_settings_curr_page'], remove_query_arg('tab', $sendback));
			wp_redirect($sendback);
						
		}	
		
	}
	
	/**
	 * Checks to see which theme files exist in the current WP theme folder
	 * and which theme files that have been selected but have not been moved over
	 * @access public 
	 *
	 * @since 3.8
	 * @param None
	 * @return true if no templates need to be moved or false if some templates do need to be moved
	 */
	
	function theme_exists() {
		global $wpsc_themes_dir;
		$results = array_diff($this->templates_to_move,$this->list_of_templates);

		//if theme already exists, we're set, do nothing - this should also indicate a new install
		if(count($results) == 0) 
			return true;
		else
			return false;
	}
	
	
	 /* 
	  * Determines the current theme location
	  * @access public 
	  *
	  * @since 3.8
	  * @param None
	  * @return None
	  */
	function theme_location() {
		global $old_wpsc_themes_dir;

			$wpsc_theme_folder = get_option('wpsc_selected_theme');
			$active_wpsc_dir = $old_wpsc_themes_dir.$wpsc_theme_folder;
			
			//First, check if theme exists in uploads folder, if so, that's theme location - if it's not there, then the theme location will be the plugins folder.
			
			if(file_exists($active_wpsc_dir.'/functions.php'))
				$theme_location = $active_wpsc_dir;
			else
				$theme_location = WPSC_FILE_PATH . '/themes';
			
			return $theme_location;
	}
	
	function recursive_copy($src,$dst){		
		$theme_file_prefix = '';
		if($old != WPSC_FILE_PATH . '/themes/')
			$theme_file_prefix = "wpsc-";
	
		$dir = opendir($src);
	    @mkdir($dst);
	    while(false !== ( $file = readdir($dir)) ) {
	    	    //exit($src.'<br /><pre>'.print_r($this->templates_to_move,true).'</pre>');
			if (in_array($file,$this->templates_to_move)) {
				if ( is_dir($src . '/' . $file) )
					$this->recursive_copy($src . '/' . $file,$dst . '/' . $file);
				else
					@ copy($src . '/' . $file,$dst . '/' . $theme_file_prefix . $file);
			}
	    }
	    closedir($dir);
	}
	
 /* Moves, renames, and appends header and footer functions to theme if they do not currently have it.
	 * @access public 
	 *
	 * @since 3.8
	 * @param old - Current location of theme
	 * @param new -New location for theme 
	 * @return None
*/

	function move_theme($old, $new) {
		$theme_file_prefix = '';
		if($old != WPSC_FILE_PATH . '/themes/')
			$theme_file_prefix = "wpsc-";
	
		$this->recursive_copy($old, $new);		
		$path = $new; 
		$dh = opendir($path); 

		$i=1; 
		while (($file = readdir($dh)) !== false) {
			if($file != "." && $file != ".." && !strstr($file, ".svn") && !strstr($file, "images") && strstr($file, 'wpsc-')) {
				if(in_array($file,$this->templates_to_move)){				
				if(!strstr($file, "functions") && !strstr($file, "css") && !strstr($file, "widget")) {
						$file_data = file_get_contents($path."/".$file);
						file_put_contents($path."/".$file, $file_data);
						rename($path."/".$file, $path."/".$theme_file_prefix.$file); 
				}

				}
				$i++; 
			} 
		} 
		closedir($dh);
	}
}
   
if(isset($_REQUEST['wpsc_move_themes']) && ($_REQUEST['wpsc_move_themes'] == 'Move Template Files')) {
	add_action( 'admin_init', create_function( '', 'global $wpsc_theming; $wpsc_theming = new wpsc_theming();' ) );
}
  
?>