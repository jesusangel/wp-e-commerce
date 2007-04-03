<?php
// pe.{
// To stick this in sidebar, main page (calling products_page.php) must be called before sidebar.php in the loop (think)

function show_cats_brands()
{
	global $wpdb;

	// Show cats & brands list if displaying on every page or if on a shop page (bit hacky but out of time).
	if (get_option('cat_brand_loc') != 3 && !function_exists("nzshpcrt_display_categories_groups"))
	{
		return;
	}
	
	if(get_option('permalink_structure') != '')
	{
		$seperator ="?";
	}
	else
	{
		$seperator ="&";
	}		
	
	$output = "<div class='PeSwitcher'>";
	
	switch(get_option('show_categorybrands'))
	{
		case 1:
			$output .= "<ul id='PeCatsBrandsBoth' class='category_brand_header'><li id='PeSwitcherFirst'><a href='' onclick='return prodgroupswitch(\"categories\");'>".TXT_WPSC_CATEGORIES."</a></li><li id='PeSwitcherMid'><a href='' onclick='return prodgroupswitch(\"brands\");'>".TXT_WPSC_BRANDS."</a></li></ul>";
			break;
		
		case 2:
			$output .= "<ul id='PeCatsOnly' class='category_brand_header'><li><a href='' onclick='return prodgroupswitch(\"categories\");'>".TXT_WPSC_CATEGORIES."</a></li></ul>";
			break;
		
		case 3:
			$output .= "<ul id='PeBrandsOnly' class='category_brand_header'><li><a href='' onclick='return prodgroupswitch(\"brands\");'>".TXT_WPSC_BRANDS."</a></li></ul>";
			break;
	}
	$output .= "</div>";
  
  $output .= "<div class='PeCatsBrands'>";
	
	
	if((get_option('show_categorybrands') == 1 ) || (get_option('show_categorybrands') == 2))
	{
		//exit("done");
		$output .= "<div class='PeCatfile:///media/katana/apps.instinct/wordpress_development/wp-content/plugins/wp-shopping-cart/show_cats_brands.phpegories' id='categorydisplay'>";
		$categories = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '0' ORDER BY `order` ASC",ARRAY_A);
		if($categories != null)
		{
			$output .= "<ul class='PeCategories'>";
			foreach($categories as $option)
			{
				$output .= "<li><a class='categorylink' href='".get_option('product_list_url').$seperator."category=".$option['id']."'>".stripslashes($option['name'])."</a></li>";
				$subcategory_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '".$option['id']."' ORDER BY `id`";
				$subcategories = $wpdb->get_results($subcategory_sql,ARRAY_A);
				if($subcategories != null)
				{
					$output .= "<ul class='SubCategories'>";
					
					foreach($subcategories as $subcategory)
					{
						$output .= "<li><a class='categorylink' href='".get_option('product_list_url').$seperator."category=".$subcategory['id']."'>".stripslashes($subcategory['name'])."</a></li>";
					}
					
					$output .= "</ul>";
				}
			}
			$output .= "</ul>";
		}
		$output .= "</div>";
	}
	
	if((get_option('show_categorybrands') == 1 ) || (get_option('show_categorybrands') == 3))
	{
		$output .= "<ul class='PeBrands' id='branddisplay'>";
		//$output ='';
		$brands = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `active`='1' ORDER BY `order` ASC",ARRAY_A);
		if($brands != null)
		{
			foreach($brands as $option)
			{
				$output .= "<li><a class='categorylink' href='".get_option('product_list_url').$seperator."brand=".$option['id']."'>".stripslashes($option['name'])."</a></li>";
			}
		}
		//$output .= $output;
		$output .= "</ul>";
	}
	
	$output .= "</div>";
	echo $output;
}
// }.pe
?>