<?php
/*
 * Updates to 3.6.8
*/

// here isthe code to create the database column for weight
if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}product_list` LIKE 'weight';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_list` ADD `weight` INT( 11 ) NOT NULL DEFAULT 0 AFTER `price`;");
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_list` ADD `weight_unit` VARCHAR( 10 ) NOT NULL AFTER `weight`;");
}

// here isthe code to create the database column for user uploaded files
if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}cart_contents` LIKE 'files';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}cart_contents` ADD `files` TEXT NOT NULL AFTER `no_shipping`");
}

if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}wpsc_coupon_codes` LIKE 'every_product';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}wpsc_coupon_codes` ADD `every_product` TEXT NOT NULL AFTER `active`");
}

if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}product_images` LIKE 'image_order';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_images` ADD `image_order` varchar(3) NOT NULL AFTER `height`");
}




// here isthe code to update the payment gateway options.
$selected_gateways = array();
$current_gateway = get_option('payment_gateway');
$selected_gateways = get_option('custom_gateway_options');
if($current_gateway == '') {
  // set the gateway to Manual Payment if it is not set.
  $current_gateway = 'testmode';
}
if(get_option('payment_method') != null) {
	switch(get_option('payment_method')) {
		case 2:
		// mode 2 is credit card and manual payment / test mode
		if($current_gateway == 'testmode') {
			$current_gateway = 'paypal_multiple';
		}
		$selected_gateways[] = 'testmode';
		$selected_gateways[] = $current_gateway;
		break;
		
		case 3;
		// mode 3 is manual payment / test mode
		$current_gateway = 'testmode';
		case 1:
		// mode 1 is whatever gateway is currently selected.
		default:
		$selected_gateways[] = $current_gateway;
		break;
	}
	update_option('custom_gateway_options', $selected_gateways);
	update_option('payment_method', null);
}


// switch this variable over to our own option name, seems default_category was used by wordpress
if(get_option('wpsc_default_category') == null) {
  update_option('wpsc_default_category', get_option('default_category'));
}

if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}product_categories` LIKE 'display_type';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_categories` ADD `display_type` VARCHAR(10) NOT NULL DEFAULT '' AFTER `order`");
}


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}product_categories` LIKE 'image_width';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_categories` ADD `image_width` VARCHAR(32) NOT NULL DEFAULT '' AFTER `display_type`");
}

if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}product_categories` LIKE 'image_height';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_categories` ADD `image_height` VARCHAR(32) NOT NULL DEFAULT '' AFTER `image_width`");
}



//Add table logged_subscription
if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."cart_item_extras'") != ($wpdb->prefix."cart_item_extras")) {
   $wpsc_cart_item_extras = "CREATE TABLE `".$wpdb->prefix."cart_item_extras` (
  `id` int(11) NOT NULL auto_increment,
  `cart_id` int(11) NOT NULL,
  `extra_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;";
  $wpdb->query($wpsc_cart_item_extras);
  }


if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."extras_values'") != ($wpdb->prefix."extras_values")) {
   $wpsc_extras_values= "CREATE TABLE `".$wpdb->prefix."extras_values` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `extras_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;";
  $wpdb->query($wpsc_extras_values);
  }


if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."item_category_associations'") != ($wpdb->prefix."item_category_associations")) {
   $wpsc_extras_values= "CREATE TABLE `".$wpdb->prefix."item_category_associations` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `product_id` bigint(20) unsigned NOT NULL default '0',
  `category_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `product_id` (`product_id`,`category_id`)
) ENGINE=MyISAM;";
  $wpdb->query($wpsc_extras_values);
  }

if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."extras_values_associations'") != ($wpdb->prefix."extras_values_associations")) {
   $wpsc_extras_values= "CREATE TABLE `".$wpdb->prefix."extras_values_associations` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL,
  `price` varchar(20) NOT NULL,
  `visible` varchar(1) NOT NULL,
  `extras_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;";
  $wpdb->query($wpsc_extras_values);
  }

if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."product_extra'") != ($wpdb->prefix."product_extra")) {
   $wpsc_extras_values= "CREATE TABLE `".$wpdb->prefix."product_extra` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;";
  $wpdb->query($wpsc_extras_values);
  }
  

if($wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."cart_item_variations` LIKE 'venue_id';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."cart_item_variations` CHANGE `venue_id` `value_id` BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT '0' ");
}


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'find_us';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `find_us` varchar(255) NOT NULL");
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `engravetext` varchar(255) default NULL");
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `closest_store` varchar(255) default NULL");
}


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."download_status` LIKE 'uniqueid';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."download_status` ADD `uniqueid` VARCHAR( 64 ) NULL AFTER `purchid`;");
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."download_status` ADD UNIQUE (`uniqueid`);");
}


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."region_tax` LIKE 'code';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."region_tax` ADD `code` char(2) NOT NULL default '' AFTER `name`;");    
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'AL' WHERE `name` IN('Alabama')LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'AK' WHERE `name` IN('Alaska') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'AZ' WHERE `name` IN('Arizona') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'AR' WHERE `name` IN('Arkansas') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'CA' WHERE `name` IN('California') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'CO' WHERE `name` IN('Colorado') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'CT' WHERE `name` IN('Connecticut') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'DE' WHERE `name` IN('Delaware') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'FL' WHERE `name` IN('Florida') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'GA' WHERE `name` IN('Georgia')  LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'HI' WHERE `name` IN('Hawaii')  LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'ID' WHERE`name` IN('Idaho')  LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'IL' WHERE `name` IN('Illinois')  LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'IN' WHERE `name` IN('Indiana')  LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'IA' WHERE `name` IN('Iowa')  LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'KS' WHERE `name` IN('Kansas')  LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'KY' WHERE `name` IN('Kentucky') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'LA' WHERE `name` IN('Louisiana') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'ME' WHERE `name` IN('Maine') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'MD' WHERE `name` IN('Maryland') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'MA' WHERE `name` IN('Massachusetts') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'MI' WHERE `name` IN('Michigan') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'MN' WHERE `name` IN('Minnesota') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'MS' WHERE `name` IN('Mississippi') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'MO' WHERE `name` IN('Missouri') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'MT' WHERE `name` IN('Montana') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'NE' WHERE `name` IN('Nebraska') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'NV' WHERE `name` IN('Nevada') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'NH' WHERE `name` IN('New Hampshire') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'NJ' WHERE `name` IN('New Jersey') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'NM' WHERE `name` IN('New Mexico') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'NY' WHERE `name` IN('New York') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'NC' WHERE `name` IN('North Carolina') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'ND' WHERE `name` IN('North Dakota') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'OH' WHERE `name` IN('Ohio') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'OK' WHERE `name` IN('Oklahoma') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'OR' WHERE `name` IN('Oregon') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'PA' WHERE `name` IN('Pennsylvania') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'RI' WHERE `name` IN('Rhode Island') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'SC' WHERE `name` IN('South Carolina') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'SD' WHERE `name` IN('South Dakota') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'TN' WHERE `name` IN('Tennessee') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'TX' WHERE `name` IN('Texas') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'UT' WHERE `name` IN('Utah') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'VT' WHERE `name` IN('Vermont') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'VA' WHERE `name` IN('Virginia') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'WA' WHERE `name` IN('Washington') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'DC' WHERE `name` IN('Washington DC') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'WV' WHERE `name` IN('West Virginia') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'WI' WHERE `name` IN('Wisconsin') LIMIT 1 ;");
	$wpdb->query("UPDATE `".$wpdb->prefix."region_tax` SET `code` = 'WY' WHERE `name` IN('Wyoming') LIMIT 1 ;");    
}


$coldata  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'totalprice'",ARRAY_A);
if($coldata[0]['Type'] != "varchar(128)")	{
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` CHANGE `totalprice` `totalprice` VARCHAR( 128 ) DEFAULT '0' NOT NULL");
	}
$coldata  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'base_shipping'",ARRAY_A);
if($coldata[0]['Type'] != "varchar(128)")	{
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` CHANGE `base_shipping` `base_shipping` VARCHAR( 128 ) DEFAULT '0' NOT NULL");
	}
		
if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'no_shipping';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `no_shipping` varchar(1) NOT NULL DEFAULT '0' AFTER `donation`;");
}

if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."wpsc_coupon_codes` LIKE 'every_product';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `".$wpdb->prefix."coupon_codes` ADD `every_product` varchar(255) NOT NULL AFTER `active`");
}


  
  
if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wpsc_categorisation_groups'") != ($wpdb->prefix."wpsc_categorisation_groups")) {
   $wpsc_categorisation_groups= "CREATE TABLE `{$wpdb->prefix}wpsc_categorisation_groups` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `active` varchar(1) NOT NULL default '1',
  `default` varchar(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `group_name` (`name`)
) ENGINE=MyISAM ; 
";
  $wpdb->query($wpsc_categorisation_groups);
  
  $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_categorisation_groups` (`id`, `name`, `description`, `active`, `default`) VALUES (1, 'Categories', 'Product Categories', '1', '1')");
  $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_categorisation_groups` (`id`, `name`, `description`, `active`, `default`) VALUES (2, 'Brands', 'Product Brands', '1', '0')");
}

if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_categorisation_groups`") < 1) {
  $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_categorisation_groups` (`id`, `name`, `description`, `active`, `default`) VALUES (1, 'Categories', 'Product Categories', '1', '1')");
  $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_categorisation_groups` (`id`, `name`, `description`, `active`, `default`) VALUES (2, 'Brands', 'Product Brands', '1', '0')");
}


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}product_categories` LIKE 'group_id';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}product_categories` ADD `group_id` BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `id`");
}


$brand_group = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}wpsc_categorisation_groups` WHERE `name` IN ( 'Brands' ) ",ARRAY_A);
if($brand_group == null) {
	$wpdb->get_row("INSERT INTO `{$wpdb->prefix}wpsc_categorisation_groups` ( `name`, `description`, `active`, `default`) VALUES ( 'Brands', 'Product Brands', '1', '0');", ARRAY_A);
	$brand_group = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}wpsc_categorisation_groups` WHERE `name` IN ( 'Brands' ) ",ARRAY_A);
}
	
$converted_brand_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}product_categories` WHERE `group_id` IN({$brand_group['id']}) AND `active` IN('1') ");
if($converted_brand_count <= 0) {
	$brands = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}product_brands` ",ARRAY_A);
	if(count($brands) > 0 ) {
		foreach($brands as $brand) {
			
			$tidied_name = trim($brand['name']);
			$tidied_name = strtolower($tidied_name);
			$url_name = preg_replace(array("/(\s)+/","/[^\w-]+/"), array("-", ''), $tidied_name);
			if($url_name != $category_data['nice-name']) {
				$similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".$wpdb->prefix."product_categories` WHERE `nice-name` REGEXP '^($url_name){1}(0-9)*$' AND `id` NOT IN ('".(int)$category_data['id']."') ",ARRAY_A);
				//exit("<pre>".print_r($similar_names,true)."</pre>");
				$extension_number = '';
				if($similar_names['count'] > 0) {
					$extension_number = (int)$similar_names['max_number']+1;
				}
				$url_name .= $extension_number;   
			}
			
			$wpdb->query( "INSERT INTO `{$wpdb->prefix}product_categories` ( `group_id`, `name`, `nice-name`, `description`, `image`, `fee`, `active`, `category_parent`, `order`) VALUES ( {$brand_group['id']}, '{$brand['name']}', '{$url_name}', '{$brand['description']}', '', '0', '1', 0, 0)");
		}  
	}
}
// Field 	Type 	Collation 	Null 	Key 	Default 	Extra 	Privileges 	Comment 

if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}wpsc_productmeta` LIKE 'custom';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}wpsc_productmeta` ADD `custom` VARCHAR( 1 ) NOT NULL DEFAULT '0' AFTER `meta_value`;");
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}wpsc_productmeta` ADD INDEX ( `custom` ) ;");
}


if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}variation_priceandstock` LIKE 'weight';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}variation_priceandstock` ADD `weight` VARCHAR( 64 ) NULL AFTER `price`;");
  $wpdb->query("ALTER TABLE `{$wpdb->prefix}variation_priceandstock` ADD `visibility` VARCHAR( 1 ) NOT NULL DEFAULT '1' AFTER `weight`;");
}


if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."wpsc_variation_combinations'") != ($wpdb->prefix."wpsc_variation_combinations")) {
   $wpsc_variation_combinations = "CREATE TABLE `{$wpdb->prefix}wpsc_variation_combinations` (
  `product_id` bigint(20) unsigned NOT NULL,
  `priceandstock_id` bigint(20) unsigned NOT NULL,
  `value_id` bigint(20) unsigned NOT NULL,
  `variation_id` bigint(20) unsigned NOT NULL,
  `all_variation_ids` varchar(64) collate NOT NULL,
  KEY `product_id` (`product_id`),
  KEY `priceandstock_id` (`priceandstock_id`),
  KEY `value_id` (`value_id`),
  KEY `variation_id` (`variation_id`),
  KEY `all_variation_ids` (`all_variation_ids`)
) ENGINE=MyISAM;";
  $wpdb->query($wpsc_variation_combinations);
  }


if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_variation_combinations`") < 1) {
  $variation_priceandstock = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}variation_priceandstock`",ARRAY_A);
  
  foreach((array)$variation_priceandstock_items as $variation_priceandstock_item) {
    $keys = array();
    $keys[] = $variation_priceandstock_item['variation_id_1'];
    $keys[] = $variation_priceandstock_item['variation_id_2'];
    
    asort($keys);    
    $all_value_ids = implode(",", $keys);
    
    
    $variation_priceandstock_id = $variation_priceandstock_item['id'];
    $product_id = $variation_priceandstock_item['product_id'];
    foreach((array)$keys as $key) {
      if($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `priceandstock_id` = '{$variation_priceandstock_id}' AND `value_id` = '$key'") < 1) {
        $variation_id = $wpdb->get_var("SELECT `variation_id` FROM `{$wpdb->prefix}variation_values` WHERE `id` = '{$key}'");
        if($variation_id > 0) {
          $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_variation_combinations` ( `product_id` , `priceandstock_id` , `value_id`, `variation_id`, `all_value_ids` ) VALUES ( '$product_id', '{$variation_priceandstock_id}', '$key', '$variation_id', '$all_value_ids' )");
        }
      }
    }
  }
}



if($wpdb->get_var("SELECT COUNT( * ) FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `all_variation_ids` IN ( '' )") > 0 ) {
  $variation_priceandstock_ids = $wpdb->get_col("SELECT DISTINCT `priceandstock_id` FROM `{$wpdb->prefix}wpsc_variation_combinations`");
  foreach($variation_priceandstock_ids as $variation_priceandstock_id) {
    $variation_priceandstock_rows = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_variation_combinations` WHERE `priceandstock_id` IN ('$variation_priceandstock_id')", ARRAY_A);
    $all_value_array = array();
    foreach($variation_priceandstock_rows as $variation_priceandstock_row) {
      $all_value_array[] = $variation_priceandstock_row['variation_id'];
    }
    asort($all_value_array);    
    
    $all_value_ids = implode(",", $all_value_array);
    $update_sql = "UPDATE `{$wpdb->prefix}wpsc_variation_combinations` SET `all_variation_ids` = '".$all_value_ids."' WHERE `priceandstock_id` IN( '$variation_priceandstock_id' );";
    
    //echo "<pre>".print_r($update_sql,true)."</pre>";
    $wpdb->query($update_sql);
  }
}




if(!$wpdb->get_results("SHOW FULL COLUMNS FROM `{$wpdb->prefix}currency_list` LIKE 'continent';",ARRAY_A)) {
	$wpdb->query("ALTER TABLE `{$wpdb->prefix}currency_list` ADD `continent` VARCHAR(20) NOT NULL");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='1'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='2'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='3'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='4'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='5'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='6'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='7'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='8'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='9'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='10'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='11'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='12'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='13'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='14'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='15'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='16'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='17'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='18'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='19'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='20'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='21'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='22'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='23'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='24'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='25'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='26'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='27'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='28'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='29'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='30'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='31'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='32'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='33'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='34'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='35'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='36'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='37'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='38'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='39'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='40'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='41'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='42'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='43'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='44'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='45'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='46'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='47'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='48'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='49'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='50'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='51'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='52'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='53'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='54'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='55'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='56'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='57'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='58'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='59'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='60'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='61'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='62'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='63'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='64'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='65'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='66'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='67'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='68'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='69'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='70'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='71'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='72'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='73'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='74'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='75'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='76'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='77'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='78'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='79'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='80'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='81'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='82'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='83'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='84'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='85'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamrica' WHERE id='86'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='87'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='88'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='89'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='90'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='91'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='92'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='93'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='94'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='95'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='96'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='97'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='98'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='99'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='100'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='101'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='102'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='103'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='104'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='105'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='106'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='107'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='108'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='109'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='110'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='111'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapasific' WHERE id='112'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='113'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='114'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='115'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='116'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='117'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='118'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapasific' WHERE id='119'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapasific' WHERE id='120'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='121'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='122'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='123'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='124'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='125'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='126'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='127'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='antarctica' WHERE id='128'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='129'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='130'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='131'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='132'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='133'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='134'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='135'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='136'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='137'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='138'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='139'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='140'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='141'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='142'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='143'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='144'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='145'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='146'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='147'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='148'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='149'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='150'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='151'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='152'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='153'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='154'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapasific' WHERE id='155'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='156'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='157'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='158'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='159'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapasific' WHERE id='160'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapasific' WHERE id='161'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapasific' WHERE id='162'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='163'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='164'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='165'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='166'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='167'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='168'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='169'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='170'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='171'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='172'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='173'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='174'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='175'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='176'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='177'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='178'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='179'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='180'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='181'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='182'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='183'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='184'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='185'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='186'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='187'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='188'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='189'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='190'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='191'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='192'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='193'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='194'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='195'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='196'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='197'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='198'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='199'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='200'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='201'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='202'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='203'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='southamerica' WHERE id='204'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='' WHERE id='205'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='206'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='207'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='208'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='209'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='210'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='211'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='212'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='213'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='214'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='215'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='216'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='217'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='218'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='219'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='220'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='' WHERE id='221'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='222'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='223'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='224'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='225'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='226'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='227'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='' WHERE id='228'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='229'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='230'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='231'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='232'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='233'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='234'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='northamerica' WHERE id='235'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='236'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='237'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='asiapacific' WHERE id='238'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='239'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='240'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='africa' WHERE id='241'");
	$wpdb->query("UPDATE `{$wpdb->prefix}currency_list` SET continent='europe' WHERE id='242'");
}

?>