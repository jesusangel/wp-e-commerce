<?php
/*
Plugin Name:WP Shopping Cart
Plugin URI: http://www.instinct.co.nz
Description: A plugin that provides a WordPress Shopping Cart. Contact <a href='http://www.instinct.co.nz/?p=16#support'>Instinct Entertainment</a> for support.
Version: 3.5 RC9.3
Author: Thomas Howard of Instinct Entertainment
Author URI: http://www.instinct.co.nz
*/
define('WPSC_VERSION', '3.5');

/*
 * Generic space for humorous (and not so humorous) comments, feel free to add your own
 * 1. I did it for the lulz
 * 2. You should do it for the lulz too
 * 3. lol wut?!
 */
 
/* serious comment here, if you find a security flaw, tell us */

### This points at your desired language file ###
//$wpdb->query("SET SESSION sql_mode='' ");  // this code could fix some errors on MySQL 5, but breaks on MySQL 3
if(get_option('language_setting') != '')
  {
  include_once(ABSPATH.'wp-content/plugins/wp-shopping-cart/languages/'.get_option('language_setting'));
  }
  else
    {
    include_once(ABSPATH.'wp-content/plugins/wp-shopping-cart/languages/EN_en.php');
    }
        
require_once(ABSPATH.'wp-content/plugins/wp-shopping-cart/classes/variations.class.php');
require_once(ABSPATH.'wp-content/plugins/wp-shopping-cart/classes/cart.class.php');

//includes Jason Sheets mimetype class, yes, it does use file extensions, and no, on some servers, there are no better ways of doing this
require_once(ABSPATH.'wp-content/plugins/wp-shopping-cart/classes/mimetype.php');

// fix for an occasional issue where no session is ever started, I am unsure of the cause of this, but hopefully this puts a nail in its coffin
if(!isset($_SESSION))
  {
  session_start();
  }

/*
 * Handles "bad" session setups that cause the session to be initialised before the cart.class.php file is included
 * The following piece of code uses the serialized cart variable to reconstruct the cart
 * if session is initialised before cart.class.php is called, then the object name of each cart item will be __PHP_Incomplete_Class
 */
$use_serialized_cart = false;
if($_SESSION['nzshpcrt_cart'] != null)
  {
  foreach($_SESSION['nzshpcrt_cart'] as $key => $item)
    {
    if(get_class($item) == "__PHP_Incomplete_Class")    
      {
      $use_serialized_cart = true;
      break;
      }
    }
  }
  else
    {
    if($_SESSION['nzshpcrt_serialized_cart'] != null)
      {
      $use_serialized_cart = true;
      }
    }

if($use_serialized_cart === true)
  {
  $_SESSION['nzshpcrt_cart'] = unserialize($_SESSION['nzshpcrt_serialized_cart']);
  }

$GLOBALS['nzshpcrt_imagesize_info'] = TXT_WPSC_IMAGESIZEINFO;
$nzshpcrt_log_states[0]['name'] = TXT_WPSC_RECEIVED;
$nzshpcrt_log_states[1]['name'] = TXT_WPSC_PROCESSING;
$nzshpcrt_log_states[2]['name'] = TXT_WPSC_PROCESSED;

class wp_shopping_cart
  {
  function wp_shopping_cart()
    {
    return;
    }
    
  function displaypages()
    {
    /*
     * Fairly standard wordpress plugin API stuff for adding the admin pages, rearrange the order to rearrange the pages
     * The bits to display the options page first on first use may be buggy, but tend not to stick around long enough to be identified and fixed
     * if you find bugs, feel free to fix them.
     *
     * If the permissions are changed here, they will likewise need to be changed for the other secions of the admin that either use ajax
     * or bypass the normal download system.
     */
    if(function_exists('add_options_page'))
      {
      if(get_option('nzshpcrt_first_load') == 0)
        {
        $base_page = 'wp-shopping-cart/options.php';
        add_menu_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 7, $base_page);
        add_submenu_page($base_page,TXT_WPSC_OPTIONS, TXT_WPSC_OPTIONS, 7, 'wp-shopping-cart/options.php');
        }
        else
          {
          $base_page = 'wp-shopping-cart/display-log.php';
          add_menu_page(TXT_WPSC_ECOMMERCE, TXT_WPSC_ECOMMERCE, 7, $base_page);
          add_submenu_page('wp-shopping-cart/display-log.php',TXT_WPSC_PURCHASELOG, TXT_WPSC_PURCHASELOG, 7, 'wp-shopping-cart/display-log.php');
          }
      
      
      add_submenu_page($base_page,TXT_WPSC_PRODUCTS, TXT_WPSC_PRODUCTS, 7, 'wp-shopping-cart/display-items.php');
      add_submenu_page($base_page,TXT_WPSC_CATEGORIES, TXT_WPSC_CATEGORIES, 7, 'wp-shopping-cart/display-category.php');
      add_submenu_page($base_page,TXT_WPSC_BRANDS, TXT_WPSC_BRANDS, 7, 'wp-shopping-cart/display-brands.php');
      
      add_submenu_page($base_page,TXT_WPSC_VARIATIONS, TXT_WPSC_VARIATIONS, 7, 'wp-shopping-cart/display_variations.php');
      add_submenu_page($base_page,TXT_WPSC_PAYMENTGATEWAYOPTIONS, TXT_WPSC_PAYMENTGATEWAYOPTIONS, 7, 'wp-shopping-cart/gatewayoptions.php');
      if(get_option('nzshpcrt_first_load') != 0)
        {
        add_submenu_page($base_page,TXT_WPSC_OPTIONS, TXT_WPSC_OPTIONS, 7, 'wp-shopping-cart/options.php');
        }
      if(function_exists('gold_shpcrt_options'))
        {
        gold_shpcrt_options($base_page);
        }
      add_submenu_page($base_page,TXT_WPSC_FORM_FIELDS, TXT_WPSC_FORM_FIELDS, 7, 'wp-shopping-cart/form_fields.php');
      add_submenu_page($base_page,TXT_WPSC_HELPINSTALLATION, TXT_WPSC_HELPINSTALLATION, 7, 'wp-shopping-cart/instructions.php');
      }
    return;
    }
  }

function nzshpcrt_install()
   {
   global $wpdb, $user_level, $wp_rewrite, $wp_version;
   $table_name = $wpdb->prefix . "product_list";
   //$log_table_name = $wpdb->prefix . "sms_log";
   if($wp_version < 2.1)
     {
     get_currentuserinfo();
     if($user_level < 8)
       {
       return;
       }
    }
  $first_install = false;
  $result = mysql_list_tables(DB_NAME);
  $tables = array();
  while($row = mysql_fetch_row($result))
    {
    $tables[] = $row[0];
    }
  if(!in_array($table_name, $tables))
    {
    $first_install = true;
    }
$itemtable = "CREATE TABLE ".$table_name." (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` TEXT NOT NULL ,
  `description` LONGTEXT NOT NULL ,
  `additional_description` LONGTEXT NOT NULL ,
  `price` VARCHAR( 20 ) NOT NULL ,
  `pnp` VARCHAR( 20 ) NOT NULL ,
  `international_pnp` VARCHAR( 20 ) NOT NULL ,
  `file` BIGINT UNSIGNED NOT NULL ,
  `image` TEXT NOT NULL ,
  `category` BIGINT UNSIGNED NOT NULL ,
  `brand` BIGINT UNSIGNED NOT NULL ,
  `quantity_limited` VARCHAR( 1 ) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `special` VARCHAR( 1 ) NOT NULL ,
  `special_price` VARCHAR( 20 ) NOT NULL,
  `notax` VARCHAR( 1 ) DEFAULT '0' NOT NULL ,
  `active` VARCHAR( 1 ) DEFAULT '1' NOT NULL ,
  `donation` VARCHAR( 1 ) DEFAULT '0' NOT NULL ,
  `thumbnail_image` TEXT NULL,
  `thumbnail_state` INTEGER NOT NULL,
  PRIMARY KEY ( `id` )
  ) TYPE = MYISAM ;";

$categorytable = "CREATE TABLE `".$wpdb->prefix."product_categories` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` TEXT NOT NULL ,
  `description` TEXT NOT NULL ,
  `image` TEXT NOT NULL,
  `fee` VARCHAR( 1 ) DEFAULT '0' NOT NULL ,
  `active` VARCHAR( 1 ) DEFAULT '1' NOT NULL ,
  `order` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY ( `id` )
  ) TYPE=MyISAM;";


$category_assoc_table = "CREATE TABLE `".$wpdb->prefix."item_category_associations` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `product_id` bigint(20) unsigned NOT NULL default '0',
  `category_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `product_id` (`product_id`,`category_id`)
  ) TYPE=MyISAM;";

$brandstable = "CREATE TABLE `".$wpdb->prefix."product_brands` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` TEXT NOT NULL ,
  `description` TEXT NOT NULL ,
  `active` VARCHAR( 1 ) DEFAULT '1' NOT NULL ,
  `order` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY ( `id` )
  ) TYPE=MyISAM;";
    
$logtable = "CREATE TABLE `".$wpdb->prefix."purchase_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `totalprice` MEDIUMINT NOT NULL ,
  `statusno` SMALLINT NOT NULL DEFAULT '0' ,
  `sessionid` VARCHAR( 255 ) NOT NULL,
  `transactid` VARCHAR( 255 ) NOT NULL,
  `authcode` VARCHAR( 255 ) NOT NULL,
  `firstname` TEXT NOT NULL ,
  `lastname` TEXT NOT NULL ,
  `email` VARCHAR( 90 ) NOT NULL ,
  `address` TEXT NOT NULL ,
  `phone` VARCHAR( 90 ) NOT NULL,
  `downloadid` BIGINT UNSIGNED NOT NULL,
  `processed` BIGINT UNSIGNED NOT NULL DEFAULT '1',
  `user_ID` BIGINT UNSIGNED NULL,
  `date` VARCHAR( 255 ) NOT NULL ,
  `gateway` VARCHAR( 64 ) NOT NULL ,
  `billing_country` CHAR( 6 ) NOT NULL,
  `shipping_country` CHAR( 6 ) NOT NULL,
  `email_sent` CHAR( 1 ) DEFAULT '0' NOT NULL,
  PRIMARY KEY ( `id` ),
  INDEX ( `gateway` ),
  UNIQUE KEY `sessionid` (`sessionid`)
  ) TYPE=MyISAM;";

$carttable = "CREATE TABLE `".$wpdb->prefix."cart_contents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `prodid` BIGINT UNSIGNED NOT NULL ,
  `purchaseid` BIGINT UNSIGNED NOT NULL ,
  `price` VARCHAR( 128 ) NOT NULL ,
  `pnp` VARCHAR( 128 ) NOT NULL ,
  `gst` VARCHAR( 128 ) NOT NULL ,
  `quantity` INT UNSIGNED NOT NULL ,
  `donation` VARCHAR( 1 ) NOT NULL ,
  PRIMARY KEY ( `id` )
  ) TYPE=MyISAM;";

$cart_variations_table = "CREATE TABLE `".$wpdb->prefix."cart_item_variations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `cart_id` BIGINT UNSIGNED NOT NULL ,
  `variation_id` BIGINT UNSIGNED NOT NULL ,
  `venue_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY ( `id` )
  ) TYPE=MyISAM;";

$downloadtable = "CREATE TABLE `".$wpdb->prefix."download_status` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `fileid` BIGINT UNSIGNED NOT NULL ,
  `purchid` BIGINT UNSIGNED NOT NULL ,
  `downloads` INT NOT NULL ,
  `active` VARCHAR( 1 ) NOT NULL DEFAULT '0',
  `datetime` DATETIME NOT NULL
  ) TYPE = MYISAM ;";

$filetable = "CREATE TABLE `".$wpdb->prefix."product_files` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `filename` VARCHAR( 255 ) NOT NULL ,
  `mimetype` VARCHAR( 128 ) NOT NULL ,
  `idhash` VARCHAR( 45 ) NOT NULL ,
  `date` VARCHAR( 255 ) NOT NULL
  ) TYPE = MYISAM ;";
require dirname(__FILE__) . "/currency_list.php";

$currencytable = "CREATE TABLE `".$wpdb->prefix."currency_list`  (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `country` varchar(255) NOT NULL default '',
  `isocode` char(2) default NULL,
  `currency` varchar(255) NOT NULL default '',
  `symbol` varchar(10) NOT NULL default '',
  `symbol_html` varchar(10) NOT NULL default '',
  `code` char(3) NOT NULL default '',
  `has_regions` char(1) NOT NULL default '0',
  `tax` varchar(8) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;";

$purchase_statuses_table = "CREATE TABLE `".$wpdb->prefix."purchase_statuses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `name` VARCHAR( 128 ) NOT NULL ,
  `active` VARCHAR( 1 ) NOT NULL ,
  `colour` VARCHAR( 6 ) NOT NULL
  ) TYPE = MYISAM ;";

$product_rating_table = "CREATE TABLE `".$wpdb->prefix."product_rating` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `ipnum` varchar(30) NOT NULL default '',
  `productid` bigint(20) unsigned NOT NULL default '0',
  `rated` tinyint(1) NOT NULL default '0',
  `time` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
  ) TYPE=MyISAM;";

$product_variations_table = "CREATE TABLE `".$wpdb->prefix."product_variations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR( 128 ) NOT NULL ,
  `variation_association` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY ( `id` ) ,
  INDEX ( `variation_association` )
  );";

$variation_values_table = "CREATE TABLE `".$wpdb->prefix."variation_values` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR( 128 ) NOT NULL ,
  `variation_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY ( `id` ) ,
  INDEX ( `variation_id` )
  );";

$variation_associations_table = "CREATE TABLE `".$wpdb->prefix."variation_associations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `type` VARCHAR( 64 ) NOT NULL ,
  `name` VARCHAR( 128 ) NOT NULL ,
  `associated_id` BIGINT UNSIGNED NOT NULL ,
  `variation_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY ( `id` ) ,
  INDEX ( `associated_id` ) ,
  INDEX ( `variation_id` )
  );";

$variation_values_associations_table = "CREATE TABLE `".$wpdb->prefix."variation_values_associations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `product_id` BIGINT UNSIGNED NOT NULL ,
  `value_id` BIGINT UNSIGNED NOT NULL ,
  `quantity` INT NOT NULL ,
  `price` VARCHAR( 32 ) NOT NULL ,
  `visible` VARCHAR( 1 ) NOT NULL ,
  `variation_id` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY ( `id` ) ,
  INDEX ( `product_id` , `value_id` , `variation_id` )
  );";



$collected_data_table = "CREATE TABLE `".$wpdb->prefix."collect_data_forms` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR( 255 ) NOT NULL ,
  `type` VARCHAR( 64 ) NOT NULL ,
  `mandatory` VARCHAR( 1 ) NOT NULL ,
  `display_log` char(1) NOT NULL DEFAULT '0',
  `default` VARCHAR( 128 ) NOT NULL ,
  `active` VARCHAR( 1 ) DEFAULT '1' NOT NULL,
  `order` INT UNSIGNED NOT NULL,
  PRIMARY KEY ( `id` ) ,
  INDEX ( `order` )
  );";

$submitted_data_table = "CREATE TABLE `".$wpdb->prefix."submited_form_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` bigint(20) unsigned NOT NULL default '0',
  `form_id` bigint(20) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `log_id` (`log_id`,`form_id`))";

$product_order_table = "CREATE TABLE `".$wpdb->prefix."product_order` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `category_id` bigint(20) unsigned NOT NULL default '0',
  `product_id` bigint(20) unsigned NOT NULL default '0',
  `order` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `category_id` (`category_id`,`product_id`),
  KEY `order` (`order`)
) TYPE=MyISAM ;";
  
  
$region_tax_table = "CREATE TABLE `".$wpdb->prefix."region_tax` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `country_id` bigint(20) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `tax` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `country_id` (`country_id`)
) TYPE=MyISAM;";
  
  
$variation_priceandstock_table = "CREATE TABLE `".$wpdb->prefix."variation_priceandstock` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `product_id` bigint(20) unsigned NOT NULL default '0',
  `variation_id_1` bigint(20) unsigned NOT NULL default '0',
  `variation_id_2` bigint(20) unsigned NOT NULL default '0',
  `stock` bigint(20) unsigned NOT NULL default '0',
  `price` VARCHAR( 32 ) NOT NULL ,
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`),
  KEY `variation_id_1` (`variation_id_1`,`variation_id_2`)
) TYPE=MyISAM;";
  // note to self, use dbDelta for this and the following, would be tidier, simpler and nicer.

  require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
  maybe_create_table($table_name,$itemtable);
  maybe_create_table(($wpdb->prefix."purchase_logs"),$logtable);
  maybe_create_table(($wpdb->prefix."cart_contents"),$carttable);
  maybe_create_table(($wpdb->prefix."cart_item_variations"),$cart_variations_table);
  maybe_create_table(($wpdb->prefix."product_categories"),$categorytable);
  maybe_create_table(($wpdb->prefix."product_brands"),$brandstable);
  maybe_create_table(($wpdb->prefix."download_status"),$downloadtable);
  maybe_create_table(($wpdb->prefix."product_files"),$filetable);
  maybe_create_table(($wpdb->prefix."currency_list"),$currencytable);
  maybe_create_table(($wpdb->prefix."purchase_statuses"),$purchase_statuses_table);
  maybe_create_table(($wpdb->prefix."product_rating"),$product_rating_table);
  
  maybe_create_table(($wpdb->prefix."product_variations"),$product_variations_table);
  maybe_create_table(($wpdb->prefix."variation_values"),$variation_values_table);
  maybe_create_table(($wpdb->prefix."variation_associations"),$variation_associations_table);
  maybe_create_table(($wpdb->prefix."variation_values_associations"),$variation_values_associations_table);
  maybe_create_table(($wpdb->prefix."collect_data_forms"),$collected_data_table);
  maybe_create_table(($wpdb->prefix."submited_form_data"),$submitted_data_table);
  maybe_create_table(($wpdb->prefix."item_category_associations"),$category_assoc_table);
  maybe_create_table(($wpdb->prefix."product_order"),$product_order_table);
  maybe_create_table(($wpdb->prefix."region_tax"),$region_tax_table);
  maybe_create_table(($wpdb->prefix."variation_priceandstock"),$variation_priceandstock_table);
 
  /*
  Updates from old versions, 
  */     
  include_once('update.php');
  
  $add_cart_quantity  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."cart_contents` LIKE 'quantity'",ARRAY_A);
  if($add_cart_quantity == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."cart_contents` ADD `quantity` INT UNSIGNED NOT NULL AFTER `gst` ;");
    }
    
  $add_cart_donation  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."cart_contents` LIKE 'donation'",ARRAY_A);
  if($add_cart_donation == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."cart_contents` ADD `donation` VARCHAR( 1 ) NOT NULL AFTER `quantity` ;");
    }
    
  $add_international_pnp  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'international_pnp'",ARRAY_A);
  if($add_international_pnp == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `international_pnp` VARCHAR( 20 ) NOT NULL AFTER `pnp`;");
    }
    
  $add_gateway_log  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'gateway'",ARRAY_A);
  if($add_gateway_log == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `gateway` VARCHAR( 64 ) NOT NULL AFTER `date`;");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD INDEX ( `gateway` ) ;");
    }    

  $add_shipping_country  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'shipping_country'",ARRAY_A);
  if($add_shipping_country == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `shipping_country` CHAR( 6 ) NOT NULL AFTER `gateway`;");
    }  

  $add_shipping_country  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'user_ID'",ARRAY_A);
  if($add_shipping_country == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `user_ID` BIGINT UNSIGNED NULL AFTER `processed`;");
    }  

  $add_billing_country  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'billing_country'",ARRAY_A);
  if($add_billing_country == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `billing_country` CHAR( 6 ) NOT NULL AFTER `gateway`;");
    // copy shipping_country into billing_country, shipping_country did the job of both before
    $wpdb->query("UPDATE `".$wpdb->prefix."purchase_logs` SET `billing_country` = `shipping_country`");
    } 
//  `email_sent` CHAR( 1 ) NOT NULL,

  $add_email_sent  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'email_sent'",ARRAY_A);
  if($add_email_sent == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `email_sent` CHAR( 1 ) DEFAULT '0' NOT NULL AFTER `shipping_country`;");
    } 
     
  $add_shipping_region  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'shipping_region'",ARRAY_A);
  if($add_shipping_region == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` ADD `shipping_region` CHAR( 6 ) NOT NULL AFTER `shipping_country`;");
    }

  $add_initial_category = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."product_categories`;",ARRAY_A);
  if($add_initial_category[0]['count'] == 0)
    {
    $wpdb->query("INSERT INTO `".$wpdb->prefix."product_categories` (`name` , `description`, `active`) VALUES ('".TXT_WPSC_EXAMPLECATEGORY."', '".TXT_WPSC_EXAMPLEDETAILS."', '1');");
    }
  
  $add_display_frontpage  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'display_frontpage';",ARRAY_A);
  if($add_display_frontpage == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `display_frontpage` VARCHAR( 1 ) NOT NULL AFTER `special_price`;");
    }
  
  $add_currency_tax  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."currency_list` LIKE 'tax';",ARRAY_A);
  if($add_currency_tax == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."currency_list` ADD `tax` VARCHAR( 8 ) NOT NULL AFTER `code`;");
    }
  
  $add_currency_has_regions  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."currency_list` LIKE 'has_regions';",ARRAY_A);
  if($add_currency_has_regions == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."currency_list` ADD `has_regions` VARCHAR( 8 ) NOT NULL AFTER `code`;");
    }
  
  $add_product_thumbnail  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'thumbnail_image';",ARRAY_A);
  if($add_product_thumbnail == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `thumbnail_image` TEXT NULL AFTER `active`;");
    }
  
  $add_thumbnail_state  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'thumbnail_state';",ARRAY_A);
  if($add_thumbnail_state == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `thumbnail_state` INTEGER NOT NULL AFTER `active`;");
    }
  
  $add_thumbnail_state  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'donation';",ARRAY_A);
  if($add_thumbnail_state == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `donation` VARCHAR( 1 ) DEFAULT '0' NOT NULL AFTER `active`;");
    }

  $check_category_assoc = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."item_category_associations`;",ARRAY_A);
  if($check_category_assoc[0]['count'] == 0)
    {
    $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `active`=1";
    $product_list = $wpdb->get_results($sql,ARRAY_A);
    foreach((array)$product_list as $product)
      {
      $results = $wpdb->query("INSERT INTO `".$wpdb->prefix."item_category_associations` (`product_id` , `category_id` ) VALUES ('".$product['id']."', '".$product['category']."');");
      }
    }

  $currency_data  = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."currency_list`");
  if($currency_data == 0)
    {
    $currency_array = explode("\n",$currency_sql);
    foreach($currency_array as $currency_row)
      {
      $wpdb->query($currency_row);
      }
    }

  $purchase_statuses_data  = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."purchase_statuses`",ARRAY_A);
  if($purchase_statuses_data[0]['count'] == 0)
    {
    $wpdb->query("INSERT INTO `".$wpdb->prefix."purchase_statuses` (`name` , `active` , `colour` ) 
    VALUES
    ('".TXT_WPSC_RECEIVED."', '1', ''),
    ('".TXT_WPSC_ACCEPTED_PAYMENT."', '1', ''),
    ('".TXT_WPSC_JOB_DISPATCHED."', '1', ''),
    ('".TXT_WPSC_PROCESSED."', '1', '');");
    }

  $add_category_parent  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_categories` LIKE 'category_parent'",ARRAY_A);
  if($add_category_parent == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_categories` ADD `category_parent` BIGINT UNSIGNED DEFAULT '0' NOT NULL AFTER `active`") ;
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_categories` ADD INDEX ( `category_parent` )");
    }
    
    
    
    
  
  $add_regions = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."region_tax`");
  // exit($add_regions);
  if($add_regions < 1)
    {
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Alberta', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'British Columbia', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Manitoba', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'New Brunswick', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Newfoundland', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Northwest Territories', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Nova Scotia', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Nunavut', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Ontario', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Prince Edward Island', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Quebec', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Saskatchewan', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '100', 'Yukon', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Alabama', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Alaska', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Arizona', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Arkansas', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'California', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Colorado', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Connecticut', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Delaware', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Florida', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Georgia', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Hawaii', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Idaho', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Illinois', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Indiana', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Iowa', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Kansas', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Kentucky', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Louisiana', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Maine', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Maryland', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Massachusetts', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Michigan', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Minnesota', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Mississippi', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Missouri', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Montana', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Nebraska', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Nevada', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'New Hampshire', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'New Jersey', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'New Mexico', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'New York', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'North Carolina', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'North Dakota', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Ohio', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Oklahoma', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Oregon', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Pennsylvania', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Rhode Island', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'South Carolina', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'South Dakota', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Tennessee', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Texas', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Utah', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Vermont', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Virginia', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Washington', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Washington DC', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'West Virginia', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Wisconsin', '0.00')");
    $wpdb->query("INSERT INTO `".$wpdb->prefix."region_tax` ( `country_id` , `name` , `tax` ) VALUES ( '136', 'Wyoming', '0.00')");
    }
    
    
    
$data_forms = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."collect_data_forms`",ARRAY_A);
if($data_forms[0]['count'] == 0)
  {
  //  ".."
$wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order`) VALUES ( '".TXT_WPSC_YOUR_BILLING_CONTACT_DETAILS."', 'heading', '0', '0', '', '1', 1),
( '".TXT_WPSC_FIRSTNAME."', 'text', '1', '1', '', '1', 2),
( '".TXT_WPSC_LASTNAME."', 'text', '1', '1', '', '1', 3),
( '".TXT_WPSC_ADDRESS."', 'address', '1', '0', '', '1', 4),
( '".TXT_WPSC_CITY."', 'city', '1', '0', '', '1', 5),
( '".TXT_WPSC_COUNTRY."', 'country', '1', '0', '', '1', 7),
( '".TXT_WPSC_POSTAL_CODE."', 'text', '0', '0', '', '1', 8),
( '".TXT_WPSC_EMAIL."', 'email', '1', '1', '', '1', 9),
( '".TXT_WPSC_DELIVER_TO_A_FRIEND."', 'heading', '0', '0', '', '1', 10),
( '".TXT_WPSC_FIRSTNAME."', 'text', '0', '0', '', '1', 11),
( '".TXT_WPSC_LASTNAME."', 'text', '0', '0', '', '1', 12),
( '".TXT_WPSC_ADDRESS."', 'address', '0', '0', '', '1', 13),
( '".TXT_WPSC_CITY."', 'city', '0', '0', '', '1', 14),
( '".TXT_WPSC_STATE."', 'text', '0', '0', '', '1', 15),
( '".TXT_WPSC_COUNTRY."', 'delivery_country', '0', '0', '', '1', 16),
( '".TXT_WPSC_POSTAL_CODE."', 'text', '0', '0', '', '1', 17);");
  
  
  /*
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_FIRSTNAME."', 'text', '1', '1', '', '1', '1');");
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_LASTNAME."', 'text', '1', '1', '', '1', '2');");
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_EMAIL."', 'email', '1', '1', '', '1', '3');");
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_ADDRESS1."', 'text', '1', '0', '', '1', '4');");
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_ADDRESS2."', 'text', '0', '0', '', '1', '5');");
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_CITY."', 'text', '1', '0', '', '1', '6');");
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_COUNTRY."', 'country', '1', '0', '', '1', '7');");
  $country_form_id  = $wpdb->get_results("SELECT `id` FROM `".$wpdb->prefix."collect_data_forms` WHERE `name` = '".TXT_WPSC_COUNTRY."' AND `type` = 'country' LIMIT 1",ARRAY_A);
  $email_form_id  = $wpdb->get_results("SELECT `id` FROM `".$wpdb->prefix."collect_data_forms` WHERE `name` = '".TXT_WPSC_EMAIL."' AND `type` = 'country' LIMIT 1",ARRAY_A);
  */
  update_option('country_form_field', $country_form_id[0]['id']);
  update_option('email_form_field', $email_form_id[0]['id']);
  $wpdb->query("INSERT INTO `".$wpdb->prefix."collect_data_forms` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_PHONE."', 'text', '1', '0', '', '1', '8');");
  }
  
  
$product_brands_data  = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."product_brands`",ARRAY_A);
if($product_brands_data[0]['count'] == 0)
  {
  $wpdb->query("INSERT INTO `".$wpdb->prefix."product_brands` ( `name`, `description`, `active`, `order`) VALUES ( '".TXT_WPSC_EXAMPLEBRAND."','".TXT_WPSC_EXAMPLEDETAILS."', '1', '0');");
  }
  
  add_option('show_thumbnails', 1, TXT_WPSC_SHOWTHUMBNAILS, "yes");

  add_option('product_image_width', '', TXT_WPSC_PRODUCTIMAGEWIDTH, 'yes');
  add_option('product_image_height', '', TXT_WPSC_PRODUCTIMAGEHEIGHT, 'yes');

  add_option('category_image_width', '', TXT_WPSC_CATEGORYIMAGEWIDTH, 'yes');
  add_option('category_image_height', '', TXT_WPSC_CATEGORYIMAGEHEIGHT, 'yes');

  add_option('product_list_url', '', TXT_WPSC_PRODUCTLISTURL, 'yes');
  add_option('shopping_cart_url', '', TXT_WPSC_SHOPPINGCARTURL, 'yes');
  add_option('checkout_url', '', TXT_WPSC_CHECKOUTURL, 'yes');
  add_option('transact_url', '', TXT_WPSC_TRANSACTURL, 'yes');
  add_option('payment_gateway', '', TXT_WPSC_PAYMENTGATEWAY, 'yes');
  if(function_exists('register_sidebar') )
    {
    add_option('cart_location', '4', TXT_WPSC_CARTLOCATION, 'yes');
    }
    else
    {
    add_option('cart_location', '1', TXT_WPSC_CARTLOCATION, 'yes');
    }

  if ( function_exists('register_sidebar') )
  {
    add_option('cart_location', '4', TXT_WPSC_CARTLOCATION, 'yes');
  }
  else
  {
  add_option('cart_location', '1', TXT_WPSC_CARTLOCATION, 'yes');
  }

  //add_option('show_categorybrands', '0', TXT_WPSC_SHOWCATEGORYBRANDS, 'yes');

  add_option('currency_type', '156', TXT_WPSC_CURRENCYTYPE, 'yes');
  add_option('currency_sign_location', '3', TXT_WPSC_CURRENCYSIGNLOCATION, 'yes');

  add_option('gst_rate', '1', TXT_WPSC_GSTRATE, 'yes');

  add_option('max_downloads', '1', TXT_WPSC_MAXDOWNLOADS, 'yes');

  add_option('display_pnp', '1', TXT_WPSC_DISPLAYPNP, 'yes');

  add_option('display_specials', '1', TXT_WPSC_DISPLAYSPECIALS, 'yes');
  add_option('do_not_use_shipping', '0', 'do_not_use_shipping', 'yes');

  add_option('postage_and_packaging', '0', TXT_WPSC_POSTAGEAND_PACKAGING, 'yes');
  
  add_option('purch_log_email', '', TXT_WPSC_PURCHLOGEMAIL, 'yes');
  add_option('return_email', '', TXT_WPSC_RETURNEMAIL, 'yes');
  add_option('terms_and_conditions', '', TXT_WPSC_TERMSANDCONDITIONS, 'yes');

 
   add_option('default_brand', 'none', TXT_WPSC_DEFAULTBRAND, 'yes');
   add_option('default_category', 'none', TXT_WPSC_DEFAULTCATEGORY, 'yes');
   
   add_option('product_view', 'default', "", 'yes');
   if(get_option('wpsc_version') != '')
     {
     add_option('wpsc_version', WPSC_VERSION, 'wpsc_version', 'yes');
     }
     
     
   if(get_option('default_category') < 1)
     {
     update_option('default_category','none');
     }
   
    add_option('nzshpcrt_first_load', '0', "", 'yes');
  
  if(!((get_option('show_categorybrands') > 0) && (get_option('show_categorybrands') < 3)))
    {
    update_option('show_categorybrands', 2);
    }
  //add_option('show_categorybrands', '0', TXT_WPSC_SHOWCATEGORYBRANDS, 'yes');
  /* PayPal options */
  add_option('paypal_business', '', TXT_WPSC_PAYPALBUSINESS, 'yes');
  add_option('paypal_url', '', TXT_WPSC_PAYPALURL, 'yes');
  //update_option('paypal_url', "https://www.sandbox.paypal.com/xclick");
  
  
  add_option('paypal_multiple_business', '', TXT_WPSC_PAYPALBUSINESS, 'yes');
  
  if(get_option('paypal_multiple_url') == null)
    {
    add_option('paypal_multiple_url', '', TXT_WPSC_PAYPALURL, 'yes');
    update_option('paypal_multiple_url', "https://www.paypal.com/cgi-bin/webscr");
    }

  add_option('product_ratings', '0', TXT_WPSC_SHOWPRODUCTRATINGS, 'yes');

/*
 * This part creates the pages and automatically puts their URLs into the options page.
 * As you can probably see, it is very easily extendable, just pop in your page and the deafult content in the array and you are good to go.
 */
  $post_date =date("Y-m-d H:i:s");
  $post_date_gmt =gmdate("Y-m-d H:i:s");
  
  $num=0;
  $pages[$num]['name'] = 'products-page';
  $pages[$num]['title'] = TXT_WPSC_PRODUCTSPAGE;
  $pages[$num]['tag'] = '[productspage]';
  $pages[$num]['option'] = 'product_list_url';
  
  $num++;
  $pages[$num]['name'] = 'checkout';
  $pages[$num]['title'] = TXT_WPSC_CHECKOUT;
  $pages[$num]['tag'] = '[shoppingcart]';
  $pages[$num]['option'] = 'shopping_cart_url';
  
//   $num++;
//   $pages[$num]['name'] = 'enter-details';
//   $pages[$num]['title'] = TXT_WPSC_ENTERDETAILS;
//   $pages[$num]['tag'] = '[checkout]';
//   $pages[2$num]['option'] = 'checkout_url';

  $num++;
  $pages[$num]['name'] = 'transaction-results';
  $pages[$num]['title'] = TXT_WPSC_TRANSACTIONRESULTS;
  $pages[$num]['tag'] = '[transactionresults]';
  $pages[$num]['option'] = 'transact_url';
  
  $num++;
  $pages[$num]['name'] = 'your-account';
  $pages[$num]['title'] = TXT_WPSC_YOUR_ACCOUNT;
  $pages[$num]['tag'] = '[userlog]';
  $pages[$num]['option'] = 'user_account_url';
  
  $newpages = false;
  $i = 0;
  $post_parent = 0;
  foreach($pages as $page)
    {
    $check_page = $wpdb->get_results("SELECT * FROM ".$wpdb->posts." WHERE `post_name` = '".$page['name']."' LIMIT 1",ARRAY_A) ;
    if($check_page == null)
      {
      if($i == 0)
        {
        $post_parent = 0;
        }
        else
          {
          $post_parent = $first_id;
          }
      
      if($wp_version >= 2.1)
        {
        $sql ="INSERT INTO ".$wpdb->posts."
        (post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_type)
        VALUES
        ('1', '$post_date', '$post_date_gmt', '".$page['tag']."', '', '".$page['title']."', '', 'publish', 'closed', 'closed', '', '".$page['name']."', '', '', '$post_date', '$post_date_gmt', '$post_parent', '0', 'page')";
        }
        else
        {      
        $sql ="INSERT INTO ".$wpdb->posts."
        (post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order)
        VALUES
        ('1', '$post_date', '$post_date_gmt', '".$page['tag']."', '', '".$page['title']."', '', 'static', 'closed', 'closed', '', '".$page['name']."', '', '', '$post_date', '$post_date_gmt', '$post_parent', '0')";
        }
      $wpdb->query($sql);
      $post_id = $wpdb->insert_id;
      if($i == 0)
        {
        $first_id = $post_id;
        }
      $wpdb->query("UPDATE $wpdb->posts SET guid = '" . get_permalink($post_id) . "' WHERE ID = '$post_id'");
      update_option($page['option'],  get_permalink($post_id));
      if($page['option'] == 'shopping_cart_url')
        {
        update_option('checkout_url',  get_permalink($post_id));
        }
      $newpages = true;
      $i++;
      }
    }
  if($newpages == true)
    {
    wp_cache_delete('all_page_ids', 'pages');
    $wp_rewrite->flush_rules();
    }
    
  /*
   * Moves images to thumbnails directory
   */
  $image_dir = ABSPATH."/wp-content/plugins/wp-shopping-cart/images/";
  $product_images = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/";
  $product_thumbnails = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/";
  if(!is_dir($product_thumbnails))
    {
    mkdir($product_thumbnails, 0775);
    }
  $product_list = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `image` != ''",ARRAY_A);
  foreach((array)$product_list as $product)
    {
    if(!glob($product_thumbnails.$product['image']))
      {
      $new_filename = $product['id']."_".$product['image'];
      if(file_exists($image_dir.$product['image']))
        {
        copy($image_dir.$product['image'], $product_thumbnails.$new_filename);
        if(file_exists($product_images.$product['image']))
          {
          copy($product_images.$product['image'], $product_images.$new_filename);
          }
        $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `image` = '".$new_filename."' WHERE `id`='".$product['id']."' LIMIT 1");
        }        
        else
          {
          $imagedir = $product_thumbnails;
          $name = $new_filename;
          $new_image_path = $product_images.$product['image'];
          $imagepath = $product['image'];
          $height = get_option('product_image_height');
          $width  = get_option('product_image_width');
          if(file_exists($product_images.$product['image']))
            {
            include("extra_image_processing.php");
            copy($product_images.$product['image'], $product_images.$new_filename);
            $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `image` = '".$new_filename."' WHERE `id`='".$product['id']."' LIMIT 1");
            }
          //echo $product['image']." not found <br/>";
          }
      }
    }
  }


function nzshpcrt_style()
  {
  ?>
  <link href='<?php echo get_option('siteurl'); ?>/wp-content/plugins/wp-shopping-cart/style.css' rel="stylesheet" type="text/css" />
  <style type="text/css" media="screen">
    <?php
  if(is_numeric($_GET['brand']) || (get_option('show_categorybrands') == 3))
    {
    $brandstate = 'block';
    $categorystate = 'none';
    }
    else
      {
    $brandstate = 'none';
    $categorystate = 'block';
      }
      
    ?>
    div#categorydisplay{
    display: <?php echo $categorystate; ?>;
    }
    
    div#branddisplay{
    display: <?php echo $brandstate; ?>;
    }
  </style>
  <?php
  }
  
function nzshpcrt_javascript()
  {
  $siteurl = get_option('siteurl'); 
  
    
  if(($_SESSION['nzshpcrt_cart'] == null) && (get_option('show_sliding_cart') == 1))
    {
    ?>
     <style type="text/css" media="screen">
    div#sliding_cart{
     display: none;
     }
    </style>
    <?php
    }
  ?>
<link href='<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/product_rater.css' rel="stylesheet" type="text/css" />
<link href='<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/thickbox.css' rel="stylesheet" type="text/css" />

<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/interface.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/dimensions.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/thickbox.js"></script>
<?php if (get_option('catsprods_display_type') == 1){ ?>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/mootools.v1.00.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/slideMenu.js"></script>
<?php } ?>
<script language='JavaScript' type='text/javascript'>
jQuery.noConflict();
/* base url */
var base_url = "<?php echo $siteurl; ?>";

/* LightBox Configuration start*/
var fileLoadingImage = "<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/images/loading.gif";    
var fileBottomNavCloseImage = "<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/images/closelabel.gif";
var fileThickboxLoadingImage = "<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/images/loadingAnimation.gif";    
var resizeSpeed = 9;  // controls the speed of the image resizing (1=slowest and 10=fastest)
var borderSize = 10;  //if you adjust the padding in the CSS, you will need to update this variable
jQuery(document).ready(
  function()
  {
  <?php
  if(get_option('show_sliding_cart') == 1)
    {
    if(is_numeric($_SESSION['slider_state']))
      {
      if($_SESSION['slider_state'] == 0)
        {
        ?>
        jQuery("#sliding_cart").css({ display: "none"});  
        <?php
        }
        else
        {
        ?>
        jQuery("#sliding_cart").css({ display: "block"});  
        <?php
        }
      }
      else
        {
        if($_SESSION['nzshpcrt_cart'] == null)
          {
          ?>
          jQuery("#sliding_cart").css({ display: "none"});  
          <?php
          }
          else
          {
          ?>
          jQuery("#sliding_cart").css({ display: "block"});  
          <?php
          }
        }
    }
  ?>
  }
);
</script>
<script src="<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/ajax.js" language='JavaScript' type="text/javascript"></script>
<script src="<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/user.js" language='JavaScript' type="text/javascript">
</script>
<?php
  }

function nzshpcrt_css()
  {
  $siteurl = get_option('siteurl'); 
  if(strpos($_SERVER['REQUEST_URI'], 'wp-shopping-cart') !== false)
    {
?>
<link href='<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/admin.css' rel="stylesheet" type="text/css" />
<link href='<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/thickbox.css' rel="stylesheet" type="text/css" />
<script src="<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/ajax.js" language='JavaScript' type="text/javascript"></script>
<script language='JavaScript' type='text/javascript'>
/* base url */
var base_url = "<?php echo $siteurl; ?>";

/* LightBox Configuration start*/
var fileLoadingImage = "<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/images/loading.gif";    
var fileBottomNavCloseImage = "<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/images/closelabel.gif";
var fileThickboxLoadingImage = "<?php echo $siteurl; ?>/wp-content/plugins/wp-shopping-cart/images/loadingAnimation.gif";    

var resizeSpeed = 9;  

var borderSize = 10;
/* LightBox Configuration end*/
/* custom admin functions start*/
<?php
    echo "var TXT_WPSC_DELETE = '".TXT_WPSC_DELETE."';\n\r";
    echo "var TXT_WPSC_TEXT = '".TXT_WPSC_TEXT."';\n\r";
    echo "var TXT_WPSC_EMAIL = '".TXT_WPSC_EMAIL."';\n\r";
    echo "var TXT_WPSC_COUNTRY = '".TXT_WPSC_COUNTRY."';\n\r";
    echo "var TXT_WPSC_TEXTAREA = '".TXT_WPSC_TEXTAREA."';\n\r";
    echo "var TXT_WPSC_HEADING = '".TXT_WPSC_HEADING."';\n\r";
    echo "var HTML_FORM_FIELD_TYPES =\"<option value='text' >".TXT_WPSC_TEXT."</option>";
    echo "<option value='email' >".TXT_WPSC_EMAIL."</option>";
    echo "<option value='address' >".TXT_WPSC_ADDRESS."</option>";
    echo "<option value='city' >".TXT_WPSC_CITY."</option>";
    echo "<option value='country'>".TXT_WPSC_COUNTRY."</option>";
    echo "<option value='delivery_address' >".TXT_WPSC_DELIVERY_ADDRESS."</option>";
    echo "<option value='delivery_city' >".TXT_WPSC_DELIVERY_CITY."</option>";
    echo "<option value='delivery_country'>".TXT_WPSC_DELIVERY_COUNTRY."</option>";
    echo "<option value='textarea' >".TXT_WPSC_TEXTAREA."</option>";
    echo "<option value='heading' >".TXT_WPSC_HEADING."</option>\";\n\r";
?>
/* custom admin functions end*/
</script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/interface.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/dimensions.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/js/thickbox.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo $siteurl;?>/wp-content/plugins/wp-shopping-cart/admin.js"></script>
<?php
    }
  }

function nzshpcrt_displaypages()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->displaypages();
  }

function nzshpcrt_adminpage()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->adminpage();
  }
  
function nzshpcrt_additem()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->additem();
  }

function nzshpcrt_displayitems()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->displayitems();
  }
  
function nzshpcrt_instructions()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->instructions();
  }

function nzshpcrt_options()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->options();
  }

function nzshpcrt_gatewayoptions()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->gatewayoptions();
  }

function nzshpcrt_addcategory()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->addcategory();
  //$GLOBALS['nzshpcrt_activateshpcrt'] = true;
  }
  
function nzshpcrt_editcategory()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->editcategory();
  //$GLOBALS['nzshpcrt_activateshpcrt'] = true;
  }
  
function nzshpcrt_editbrands()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->editbrands();
  //$GLOBALS['nzshpcrt_activateshpcrt'] = true;
  }
  
function nzshpcrt_editvariations()
  {
  $nzshpcrt = new wp_shopping_cart;
  $nzshpcrt->editvariations();
  //$GLOBALS['nzshpcrt_activateshpcrt'] = true;
  }
  
function nzshpcrt_submit_ajax()
  {
  global $wpdb,$user_level,$wp_rewrite;
  get_currentuserinfo();  
  if(get_option('permalink_structure') != '')
    {
    $seperator ="?";
    }
    else
      {
      $seperator ="&amp;";
      }
   
  // if is an AJAX request, cruddy code, could be done better but getting approval would be impossible
  if(($_POST['ajax'] == "true") || ($_GET['ajax'] == "true"))
    {
    if(($_GET['user'] == "true") && is_numeric($_POST['prodid']))
      {
      $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`='".$_POST['prodid']."' LIMIT 1";
      $item_data = $wpdb->get_results($sql,ARRAY_A);
      
      $item_quantity = 0;
      if($_SESSION['nzshpcrt_cart'] != null)
        { 
        foreach($_SESSION['nzshpcrt_cart'] as $cart_key => $cart_item)
          {
          if($cart_item->product_id == $_POST['prodid'])
            {
            if($_SESSION['nzshpcrt_cart'][$cart_key]->product_variations === $_POST['variation'])
              {
              $item_quantity += $_SESSION['nzshpcrt_cart'][$cart_key]->quantity;
              $item_variations = $_SESSION['nzshpcrt_cart'][$cart_key]->product_variations;
              }
            }
          }
        }
      
      $item_stock = null;
      $variation_count = count($_POST['variation']);
      if(($variation_count >= 1) && ($variation_count <= 2))
        {
        foreach($_POST['variation'] as $variation_id)
          {
          if(is_numeric($variation_id))
            {
            $variation_ids[] = $variation_id;
            }
          }
        if(count($variation_ids) == 2)
          {
          $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$_POST['prodid']."' AND (`variation_id_1` = '".$variation_ids[0]."' AND `variation_id_2` = '".$variation_ids[1]."') OR (`variation_id_1` = '".$variation_ids[1]."' AND `variation_id_2` = '".$variation_ids[0]."') LIMIT 1",ARRAY_A);
      //exit("<pre>".print_r($variation_stock_data,true)."</pre>");
          $item_stock = $variation_stock_data['stock'];
          }
          else if(count($variation_ids) == 1)
            {
            $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$_POST['prodid']."' AND (`variation_id_1` = '".$variation_ids[0]."' AND `variation_id_2` = '0') LIMIT 1",ARRAY_A);
            //exit("<pre>".print_r($variation_stock_data,true)."</pre>");
            $item_stock = $variation_stock_data['stock'];
            }          
        }
        
      if($item_stock === null)
        {
        $item_stock = $item_data[0]['quantity'];
        }
      
      if((($item_data[0]['quantity_limited'] == 1) && ($item_stock > 0) && ($item_stock > $item_quantity)) || ($item_data[0]['quantity_limited'] == 0)) 
        {
        $cartcount = count($_SESSION['nzshpcrt_cart']);
        if(is_array($_POST['variation'])) {  $variations = $_POST['variation'];  }  else  { $variations = null; }
        
        $updated_quantity = false;
        if($_SESSION['nzshpcrt_cart'] != null)
          { 
          foreach($_SESSION['nzshpcrt_cart'] as $cart_key => $cart_item)
            {
            if($cart_item->product_id === $_POST['prodid'])
              {
              if(($_SESSION['nzshpcrt_cart'][$cart_key]->product_variations === $variations) && ($_SESSION['nzshpcrt_cart'][$cart_key]->donation_price == $_POST['donation_price']))
                {
                if(is_numeric($_POST['quantity']))
                  {
                  $_SESSION['nzshpcrt_cart'][$cart_key]->quantity += $_POST['quantity'];
                  }
                  else
                    {
                    $_SESSION['nzshpcrt_cart'][$cart_key]->quantity++;
                    }              
                $updated_quantity = true;
                }
              }
            }
          }
        
        if($item_data[0]['donation'] == 1)
          {
          $donation = $_POST['donation_price'];
          }
          else
            {
            $donation = false;
            }
        
        if($updated_quantity === false)
          {
          if(is_numeric($_POST['quantity']))
            {
            if($_POST['quantity'] > 0)
              {
              $new_cart_item = new cart_item($_POST['prodid'],$variations,$_POST['quantity'], $donation);
              }
            }
            else
              {
              $new_cart_item = new cart_item($_POST['prodid'],$variations, 1, $donation);
              }
          $_SESSION['nzshpcrt_cart'][] = $new_cart_item;
          }
        }
        else 
          {
          $quantity_limit = true;
          }
      
      $cart = $_SESSION['nzshpcrt_cart'];
      
      echo  "if(document.getElementById('shoppingcartcontents') != null)
  {   
  document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(nzshpcrt_shopping_basket_internals($cart,$quantity_limit))). "\";
  }
";

      if(($_POST['prodid'] != null) &&(get_option('fancy_notifications') == 1))
        {
        echo "if(document.getElementById('fancy_notification_content') != null)
  {   
  document.getElementById('fancy_notification_content').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(fancy_notification_content($_POST['prodid'], $quantity_limit))). "\";
  jQuery('#loading_animation').css('display', 'none');
  jQuery('#fancy_notification_content').css('display', 'block');  
  }
";
        }
      
      if($_SESSION['slider_state'] == 0)
        {
        echo  'jQuery("#sliding_cart").css({ display: "none"});'."\n\r";
        }
        else
        {
        echo  'jQuery("#sliding_cart").css({ display: "block"});'."\n\r";
        }
      exit();
      }
      else if(($_POST['user'] == "true") && ($_POST['emptycart'] == "true"))
        {
        $_SESSION['nzshpcrt_cart'] = '';
        $_SESSION['nzshpcrt_cart'] = Array();
      
      echo  "if(document.getElementById('shoppingcartcontents') != null)
  {   
  document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "", addslashes(nzshpcrt_shopping_basket_internals($cart))). "\";
  }
";
        //echo nzshpcrt_shopping_basket_internals($cart);
        exit();
        }
        
    /* fill product form */    
    if(($_POST['admin'] == "true") && is_numeric($_POST['prodid']))
      {
      echo nzshpcrt_getproductform($_POST['prodid']);
      exit();
      }  /* fill category form */   
      else if(($_POST['admin'] == "true") && is_numeric($_POST['catid']))
        {
        echo nzshpcrt_getcategoryform($_POST['catid']);
        exit();
        }  /* fill brand form */ 
        else if(($_POST['admin'] == "true") && is_numeric($_POST['brandid']))
          {  
          echo nzshpcrt_getbrandsform($_POST['brandid']);
          exit();
          }
          else if(($_POST['admin'] == "true") && is_numeric($_POST['variation_id']))
            {  
            echo nzshpcrt_getvariationform($_POST['variation_id']);
            exit();
            }
            
    
    if(is_numeric($_POST['currencyid']))
      {
      $currency_data = $wpdb->get_results("SELECT `symbol`,`symbol_html`,`code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".$_POST['currencyid']."' LIMIT 1",ARRAY_A) ;
      $price_out = null;
      if($currency_data[0]['symbol'] != '')
        {
        $currency_sign = $currency_data[0]['symbol_html'];
        }
        else
          {
          $currency_sign = $currency_data[0]['code'];
          }
      echo $currency_sign;
      exit();
      }
    
    
    /* rate item */    
    if(($_POST['rate_item'] == "true") && is_numeric($_POST['product_id']) && is_numeric($_POST['rating']))
      {
      $nowtime = time();
      $prodid = $_POST['product_id'];
      $ip_number = $_SERVER['REMOTE_ADDR'];
      $rating = $_POST['rating'];
      
      $cookie_data = explode(",",$_COOKIE['voting_cookie'][$prodid]);
      
      if(is_numeric($cookie_data[0]) && ($cookie_data[0] > 0))
        {
        $vote_id = $cookie_data[0];
        $wpdb->query("UPDATE `".$wpdb->prefix."product_rating` SET `rated` = '".$rating."' WHERE `id` ='".$vote_id."' LIMIT 1 ;");
        }
        else
          {
          $insert_sql = "INSERT INTO `".$wpdb->prefix."product_rating` ( `ipnum`  , `productid` , `rated`, `time`) VALUES ( '".$ip_number."', '".$prodid."', '".$rating."', '".$nowtime."');";
          $wpdb->query($insert_sql);
          
          $data = $wpdb->get_results("SELECT `id`,`rated` FROM `".$wpdb->prefix."product_rating` WHERE `ipnum`='".$ip_number."' AND `productid` = '".$prodid."'  AND `rated` = '".$rating."' AND `time` = '".$nowtime."' ORDER BY `id` DESC LIMIT 1",ARRAY_A) ;
          
          $vote_id = $data[0]['id'];
          setcookie("voting_cookie[$prodid]", ($vote_id.",".$rating),time()+(60*60*24*360));
          }   
      
      
      
      $output[1]= $prodid;
      $output[2]= $rating;
      echo $output[1].",".$output[2];
      exit();
      }
      
    if(($_POST['get_rating_count'] == "true") && is_numeric($_POST['product_id']))
      {
      $prodid = $_POST['product_id'];
      $data = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."product_rating` WHERE `productid` = '".$prodid."'",ARRAY_A) ;
      echo $data[0]['count'].",".$prodid;
      exit();
      }
      
    if(($_POST['remove_variation_value'] == "true") && is_numeric($_POST['variation_value_id']))
      {
      $wpdb->query("DELETE FROM `".$wpdb->prefix."variation_values_associations` WHERE `value_id` = '".$_POST['variation_value_id']."'");
      $wpdb->query("DELETE FROM `".$wpdb->prefix."variation_values` WHERE `id` = '".$_POST['variation_value_id']."' LIMIT 1");
      exit();
      }
      
    if(($_POST['get_updated_price'] == "true") && is_numeric($_POST['product_id']))
      {
      $notax = $wpdb->get_var("SELECT `notax` FROM `".$wpdb->prefix."product_list` WHERE `id` IN('".$_POST['product_id']."') LIMIT 1");
      foreach((array)$_POST['variation'] as $variation)
        {
        if(is_numeric($variation))
          {
          $variations[] = $variation;
          }      
        }    
      echo "product_id=".$_POST['product_id'].";\n";
      echo "price=\"".nzshpcrt_currency_display(calculate_product_price($_POST['product_id'], $variations), $notax)."\";\n";
      exit();
      }
      
    if(($_GET['log_state'] == "true") && is_numeric($_POST['id']) && is_numeric($_POST['value']))
      {
      $stage_count_sql = "SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."purchase_statuses` WHERE `active`='1'";
      $stage_count_data = $wpdb->get_results($stage_count_sql,ARRAY_A);
      $stage_count = $stage_count_data[0]['count'];
      if(is_numeric($_POST['value']))
        {
        $newvalue = $_POST['value'];
        }
        else
          {
          $newvalue = 1;
          }
      $update_sql = "UPDATE `".$wpdb->prefix."purchase_logs` SET `processed` = '".$newvalue."' WHERE `id` = '".$_POST['id']."' LIMIT 1";
      $wpdb->query($update_sql);
      
      $stage_sql = "SELECT * FROM `".$wpdb->prefix."purchase_statuses` WHERE `id`='".$newvalue."' AND `active`='1' LIMIT 1";
      $stage_data = $wpdb->get_row($stage_sql,ARRAY_A);
              
      echo "document.getElementById(\"form_group_".$_POST['id']."_text\").innerHTML = '".$stage_data['name']."';\n";
      echo "document.getElementById(\"form_group_".$_POST['id']."_text\").style.color = '#".$stage_data['colour']."';\n";
      
      
      $year = date("Y");
      $month = date("m");
      $start_timestamp = mktime(0, 0, 0, $month, 1, $year);
      $end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
      
      echo "document.getElementById(\"log_total_month\").innerHTML = '".addslashes(nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1))."';\n";
      echo "document.getElementById(\"log_total_absolute\").innerHTML = '".addslashes(nzshpcrt_currency_display(admin_display_total_price(),1))."';\n";
      exit();
      }
      
    if(($_POST['list_variation_values'] == "true") && is_numeric($_POST['variation_id']))
      {
      $variation_processor = new nzshpcrt_variations();
      echo "variation_value_id = \"".$_POST['variation_id']."\";\n";
      echo "variation_value_html = \"".$variation_processor->display_variation_values($_POST['prefix'],$_POST['variation_id'])."\";\n";
      exit();
      }
      
    if(($_POST['remove_form_field'] == "true") && is_numeric($_POST['form_id']))
      {
      if($user_level >= 7)
      {
      $wpdb->query("UPDATE `".$wpdb->prefix."collect_data_forms` SET `active` = '0' WHERE `id` ='".$_POST['form_id']."' LIMIT 1 ;");
      exit();
      }
      }
      
    if(($_POST['remove_form_field'] == "true") && is_numeric($_POST['form_id']))
      {
      if($user_level >= 7)
      {
      $wpdb->query("UPDATE `".$wpdb->prefix."collect_data_forms` SET `active` = '0' WHERE `id` ='".$_POST['form_id']."' LIMIT 1 ;");
      exit();
      }
      }
      
      
      /*
       * function for handling the checkout billing address
       */      
    if(preg_match("/[a-zA-Z]{2,4}/", $_POST['billing_country']))
      {
      if($_SESSION['selected_country'] == $_POST['billing_country'])
        {
        $do_not_refresh_regions = true;
        }
        else
        {
        $do_not_refresh_regions = false;
        $_SESSION['selected_country'] = $_POST['billing_country'];
        }
        
      if(is_numeric($_POST['form_id']))
        {
        $form_id = $_POST['form_id'];
        $html_form_id = "region_country_form_$form_id";
        }
        else
          {
          $html_form_id = 'region_country_form';
          }
        
        if(is_numeric($_POST['billing_region']))
          {
          $_SESSION['selected_region'] = $_POST['billing_region'];
          }
      $cart =& $_SESSION['nzshpcrt_cart'];
      echo  "if(document.getElementById('shoppingcartcontents') != null)
  {
  document.getElementById('shoppingcartcontents').innerHTML = \"".str_replace(Array("\n","\r") , "",addslashes(nzshpcrt_shopping_basket_internals($cart,$quantity_limit))). "\";
  }
";

      if($do_not_refresh_regions == false)
        {        
        $region_list = $wpdb->get_results("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".$_POST['billing_country']."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`",ARRAY_A) ;
          if($region_list != null)
            {
            $output .= "<select name='collected_data[".$form_id."][1]' class='current_region' onchange='set_billing_country(\\\"$html_form_id\\\", \\\"$form_id\\\");'>";
            //$output .= "<option value=''>None</option>";
            foreach($region_list as $region)
              {
              if($_SESSION['selected_region'] == $region['id'])
                {
                $selected = "selected='true'";
                }
                else
                  {
                  $selected = "";
                  }
              $output .= "<option value='".$region['id']."' $selected>".$region['name']."</option>";
              }
            $output .= "</select>";
      echo  "if(document.getElementById('region_select_$form_id') != null)
  {
  document.getElementById('region_select_$form_id').innerHTML = \"".$output."\";
  }
";
          }
          else
          {
          echo  "if(document.getElementById('region_select_$form_id') != null)
  {
  document.getElementById('region_select_$form_id').innerHTML = \"\";
  }
";
          }
        }
      exit();
      }
    
    if(($_POST['get_country_tax'] == "true") && preg_match("/[a-zA-Z]{2,4}/",$_POST['country_id']))  
      {
      $country_id = $_POST['country_id'];
      $region_list = $wpdb->get_results("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".$country_id."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`",ARRAY_A) ;
      if($region_list != null)
        {
        echo "<select name='base_region'>\n\r";
        foreach($region_list as $region)
          {
          if(get_option('base_region')  == $region['id'])
            {
            $selected = "selected='true'";
            }
            else
              {
              $selected = "";
              }
          echo "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
          }
        echo "</select>\n\r";    
        }
        else { echo "&nbsp;"; }
      exit();
      }
      
    
    /* fill product form */    
    if(($_POST['set_slider'] == "true") && is_numeric($_POST['state']))
      {
      $_SESSION['slider_state'] = $_POST['state'];
      exit();
      }  /* fill category form */
      
      
     
      
    if($_GET['action'] == "register")
      {
      $siteurl = get_option('siteurl');       
      require_once( ABSPATH . WPINC . '/registration-functions.php');
      if(($_POST['action']=='register') && get_settings('users_can_register'))
        {        
        //exit("fail for testing purposes");
        $user_login = sanitize_user( $_POST['user_login'] );
        $user_email = $_POST['user_email'];
        
        $errors = array();
          
        if ( $user_login == '' )
          exit($errors['user_login'] = __('<strong>ERROR</strong>: Please enter a username.'));
      
        /* checking e-mail address */
        if ($user_email == '') {
          exit(__('<strong>ERROR</strong>: Please type your e-mail address.'));
        } else if (!is_email($user_email)) {
          exit( __('<strong>ERROR</strong>: The email address isn&#8217;t correct.'));
          $user_email = '';
        }
      
        if ( ! validate_username($user_login) ) {
          $errors['user_login'] = __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.');
          $user_login = '';
        }
      
        if ( username_exists( $user_login ) )
          exit( __('<strong>ERROR</strong>: This username is already registered, please choose another one.'));
      
        /* checking the email isn't already used by another user */
        $email_exists = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
        if ( $email_exists)
          die (__('<strong>ERROR</strong>: This email address is already registered, please supply another.'));
      
      
      
        
        if ( 0 == count($errors) ) {
          $password = substr( md5( uniqid( microtime() ) ), 0, 7);
          //xit('there?');      
          $user_id = wp_create_user( $user_login, $password, $user_email );
          if ( !$user_id )
            {
            exit(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_settings('admin_email')));
            }
            else
            {
            wp_new_user_notification($user_id, $password);
            ?>
<div id="login"> 
  <h2><?php _e('Registration Complete') ?></h2>
  <p><?php printf(__('Username: %s'), "<strong>" . wp_specialchars($user_login) . "</strong>") ?><br />
  <?php printf(__('Password: %s'), '<strong>' . __('emailed to you') . '</strong>') ?> <br />
  <?php printf(__('E-mail: %s'), "<strong>" . wp_specialchars($user_email) . "</strong>") ?></p>
</div>
<?php
            }
          }
        }
        else
          {
          // onsubmit='submit_register_form(this);return false;'
          echo "<div id='login'>
    <h2>Register for this blog</h2>
    <form id='registerform' action='index.php?ajax=true&amp;action=register'  onsubmit='submit_register_form(this);return false;' method='post'>
      <p><input type='hidden' value='register' name='action'/>
      <label for='user_login'>Username:</label><br/> <input type='text' value='' maxlength='20' size='20' id='user_login' name='user_login'/><br/></p>
      <p><label for='user_email'>E-mail:</label><br/> <input type='text' value='' maxlength='100' size='25' id='user_email' name='user_email'/></p>
      <p>A password will be emailed to you.</p>
      <p class='submit'><input type='submit' name='submit_form' id='submit' value='Register »'/><img id='register_loading_img' src='$siteurl/wp-content/plugins/wp-shopping-cart/images/loading.gif' alt='' title=''></p>
      
    </form>
    </div>";
         }
      
      exit();
      } 
      
    
    /*
    * AJAX stuff stops here, I would put an exit here, but it may screw up other plugins
    //exit();
    */
    }
    
   if(isset($_POST['language_setting']) && ($_GET['page'] = 'wp-shopping-cart/options.php'))
    {
    if($user_level >= 7)
      {
      update_option('language_setting', $_POST['language_setting']);
      }
    }
  
  if(isset($_POST['language_setting']) && ($_GET['page'] = 'wp-shopping-cart/options.php'))
    {
    if($user_level >= 7)
      {
      update_option('language_setting', $_POST['language_setting']);
      }
    }
    
  if(($_GET['rss'] == "true") && ($_GET['rss_key'] == 'key') && ($_GET['action'] == "purchase_log"))
    {
    $sql = "SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `date`!='' ORDER BY `date` DESC";
    $purchase_log = $wpdb->get_results($sql,ARRAY_A);
    header("Content-Type: application/xml; charset=ISO-8859-1"); 
    header('Content-Disposition: inline; filename="WP_E-Commerce_Purchase_Log.rss"');
    $output = '';
    $output .= "<?xml version='1.0'?>\n\r";
    $output .= "<rss version='2.0'>\n\r";
    $output .= "  <channel>\n\r";
    $output .= "    <title>WP E-Commerce Product Log</title>\n\r";
    $output .= "    <link>".get_option('siteurl')."/wp-admin/admin.php?page=wp-shopping-cart/display-log.php</link>\n\r";
    $output .= "    <description>This is the WP E-Commerce Product Log RSS feed</description>\n\r";
    $output .= "    <generator>WP E-Commerce Plugin</generator>\n\r";
    
    foreach((array)$purchase_log as $purchase)
      {
      $purchase_link = get_option('siteurl')."/wp-admin/admin.php?page=wp-shopping-cart/display-log.php&amp;purchaseid=".$purchase['id'];
      $output .= "    <item>\n\r";
      $output .= "      <title>Purchase No. ".$purchase['id']."</title>\n\r";
      $output .= "      <link>$purchase_link</link>\n\r";
      $output .= "      <description>This is an entry in the purchase log.</description>\n\r";
      $output .= "      <pubDate>".date("r",$purchase['date'])."</pubDate>\n\r";
      $output .= "      <guid>$purchase_link</guid>\n\r";
      $output .= "    </item>\n\r";
      }
    $output .= "  </channel>\n\r";
    $output .= "</rss>";
    echo $output;
    exit();
    }
  
    
    
  if(($_GET['rss'] == "true") && ($_GET['action'] == "product_list"))
    {
    $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `active` IN('1')";
    $product_list = $wpdb->get_results($sql,ARRAY_A);
    header("Content-Type: application/xml; charset=ISO-8859-1"); 
    header('Content-Disposition: inline; filename="WP_E-Commerce_Product_List.rss"');
    $output = '';
    $output .= "<?xml version='1.0'?>\n\r";
    $output .= "<rss version='2.0'>\n\r";
    $output .= "  <channel>\n\r";
    $output .= "    <title>WP E-Commerce Product Log</title>\n\r";
    $output .= "    <link>".get_option('siteurl')."/wp-admin/admin.php?page=wp-shopping-cart/display-log.php</link>\n\r";
    $output .= "    <description>This is the WP E-Commerce Product List RSS feed</description>\n\r";
    $output .= "    <generator>WP E-Commerce Plugin</generator>\n\r";
    
    foreach($product_list as $product)
      {
      $purchase_link = get_option('product_list_url').$seperator."product_id=".$product['id'];
      $output .= "    <item>\n\r";
      $output .= "      <title>".stripslashes($product['name'])."</title>\n\r";
      $output .= "      <link>$purchase_link</link>\n\r";
      $output .= "      <description>".stripslashes($product['description'])."</description>\n\r";
      $output .= "      <pubDate>".date("r")."</pubDate>\n\r";
      $output .= "      <guid>$purchase_link</guid>\n\r";
      $output .= "    </item>\n\r";
      }
    $output .= "  </channel>\n\r";
    $output .= "</rss>";
    echo $output;
    exit();
    }
    
  
  if($_GET['termsandconds'] === 'true')
    {
    echo stripslashes(get_option('terms_and_conditions'));
    exit();
    }
    
    
  if(($_GET['purchase_log_csv'] == "true") && ($_GET['rss_key'] == 'key') && is_numeric($_GET['start_timestamp']) && is_numeric($_GET['end_timestamp']))
    {
    $form_sql = "SELECT * FROM `".$wpdb->prefix."collect_data_forms` WHERE `active` = '1' AND `display_log` = '1';";
    $form_data = $wpdb->get_results($form_sql,ARRAY_A);
    
    $start_timestamp = $_GET['start_timestamp'];
    $end_timestamp = $_GET['end_timestamp'];
    $data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."purchase_logs` WHERE `date` BETWEEN '$start_timestamp' AND '$end_timestamp' ORDER BY `date` DESC",ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: inline; filename="Purchase Log '.date("M-d-Y", $start_timestamp).' to '.date("M-d-Y", $end_timestamp).'.csv"');      
    
    foreach($data as $purchase)
      {
      $country_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".get_option('country_form_field')."' LIMIT 1";
      $country_data = $wpdb->get_results($country_sql,ARRAY_A);
      $country = $country_data[0]['value'];
           
      $output .= "\"".nzshpcrt_find_total_price($purchase['id'],$country) ."\",";
                
      foreach($form_data as $form_field)
        {
        $collected_data_sql = "SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id` = '".$purchase['id']."' AND `form_id` = '".$form_field['id']."' LIMIT 1";
        $collected_data = $wpdb->get_results($collected_data_sql,ARRAY_A);
        $collected_data = $collected_data[0];
        $output .= "\"".$collected_data['value']."\",";
        }
        
      if(get_option('payment_method') == 2)
        {
        $gateway_name = '';
        foreach($GLOBALS['nzshpcrt_gateways'] as $gateway)
          {
          if($purchase['gateway'] != 'testmode')
            {
            if($gateway['internalname'] == $purchase['gateway'] )
              {
              $gateway_name = $gateway['name'];
              }
            }
            else
              {
              $gateway_name = "Manual Payment";
              }
          }
        $output .= "\"". $gateway_name ."\",";
        }
              
      if($purchase['processed'] < 1)
        {
        $purchase['processed'] = 1;
        }
      $stage_sql = "SELECT * FROM `".$wpdb->prefix."purchase_statuses` WHERE `id`='".$purchase['processed']."' AND `active`='1' LIMIT 1";
      $stage_data = $wpdb->get_results($stage_sql,ARRAY_A);
              
      $output .= "\"". $stage_data[0]['name'] ."\",";
      
      $output .= "\"". date("jS M Y",$purchase['date']) ."\"";
      
      $cartsql = "SELECT * FROM `".$wpdb->prefix."cart_contents` WHERE `purchaseid`=".$purchase['id']."";
      $cart = $wpdb->get_results($cartsql,ARRAY_A) ; 
      //exit(nl2br(print_r($cart,true)));
      
      foreach($cart as $item)
        {
        $output .= ",";
        $product = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=".$item['prodid']." LIMIT 1",ARRAY_A);        
        $variation_sql = "SELECT * FROM `".$wpdb->prefix."cart_item_variations` WHERE `cart_id`='".$item['id']."'";
        $variation_data = $wpdb->get_results($variation_sql,ARRAY_A);
         $variation_count = count($variation_data);
          if($variation_count >= 1)
            {
            $variation_list = " (";
            $i = 0;
            foreach($variation_data as $variation)
              {
              if($i > 0)
                {
                $variation_list .= ", ";
                }
              $value_id = $variation['venue_id'];
              $value_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `id`='".$value_id."' LIMIT 1",ARRAY_A);
              $variation_list .= $value_data[0]['name'];              
              $i++;
              }
            $variation_list .= ")";
            }
        
        
        
        $output .= "\"".$item['quantity']." ".$product['name'].$variation_list."\"";
        }
      $output .= "\n"; // terminates the row/line in the CSV file
      }
    echo $output;
    exit();
    }    
    
    
    if(is_numeric($_GET['remove']) && ($_SESSION['nzshpcrt_cart'] != null))
      {
      $key = $_GET['remove'];
      if(is_object($_SESSION['nzshpcrt_cart'][$key]))
        {
        $_SESSION['nzshpcrt_cart'][$key]->empty_item();
        }
      unset($_SESSION['nzshpcrt_cart'][$key]);
      }
    
    if($_GET['cart']== 'empty')
      {
      $_SESSION['nzshpcrt_cart'] = '';
      $_SESSION['nzshpcrt_cart'] = Array();
      }
      
    if(is_numeric($_POST['quantity']) && is_numeric($_POST['key']))
      {
      $quantity = $_POST['quantity'];
      $key = $_POST['key'];
      if(is_object($_SESSION['nzshpcrt_cart'][$key]))
        {
        if($quantity > 0)
          {
          $_SESSION['nzshpcrt_cart'][$key]->quantity = $quantity;
          }
          else
            {
            $_SESSION['nzshpcrt_cart'][$key]->empty_item();
            unset($_SESSION['nzshpcrt_cart'][$key]);
            }
         }
       }
  }
  
function nzshpcrt_getproductform($prodid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;
  $variations_processor = new nzshpcrt_variations;
 /*
  * makes the product form
  * has functions inside a function
  */ 
  function brandslist($current_brand = '')
    {
    global $wpdb;
    $options = "";
    //$options .= "<option value=''>".TXT_WPSC_SELECTACATEGORY."</option>\r\n";
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `active`='1' ORDER BY `id` ASC",ARRAY_A);
    $options .= "<option  $selected value='0'>".TXT_WPSC_SELECTABRAND."</option>\r\n";
    foreach($values as $option)
      {
      if($current_brand == $option['id'])
        {
        $selected = "selected='selected'";
        }
      $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
      $selected = "";
      }
    $concat .= "<select name='brand'>".$options."</select>\r\n";
    return $concat;
    }
  
  function variationslist($current_variation = '')
    {
    global $wpdb;
    $options = "";
    //$options .= "<option value=''>".TXT_WPSC_SELECTACATEGORY."</option>\r\n";
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_variations` ORDER BY `id` ASC",ARRAY_A);
    $options .= "<option  $selected value='0'>".TXT_WPSC_PLEASECHOOSE."</option>\r\n";
    //$options .= "<option  $selected value='add'>".TXT_WPSC_NEW_VARIATION."</option>\r\n";
    if($values != null)
      {
      foreach($values as $option)
        {
        if($current_brand == $option['id'])
          {
          $selected = "selected='selected'";
          }
        $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
        $selected = "";
        }
      }
    $concat .= "<select name='variations' onChange='variation_value_list(this.options[this.selectedIndex].value)'>".$options."</select>\r\n";
    return $concat;
    }
  
  $sql = "SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id`=$prodid LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A) ;
  $product = $product_data[0];
  
  $check_variation_value_count = $wpdb->get_var("SELECT COUNT(*) as `count` FROM `".$wpdb->prefix."variation_values_associations` WHERE `product_id` = '".$product['id']."'");
  
  
  $output .= "        <table>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td class='itemfirstcol'>\n\r";
  $output .= TXT_WPSC_PRODUCTNAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input  size='30' type='text' name='title' value='".stripslashes($product['name'])."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_PRODUCTDESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_ADDITIONALDESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='additional_description' cols='40' rows='8' >".stripslashes($product['additional_description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_PRODUCT_CATEGORIES.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= categorylist($product['id'], 'edit_');
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_CHOOSEABRAND.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= brandslist($product['brand']);
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  if($product['display_frontpage'] == 1)
    {
    $output .= "<input type='checkbox' checked='true' value='yes' name='display_frontpage' id='form_display_frontpage'/>\n\r";
    }
    else
      {
      $output .= "<input type='checkbox' value='yes' name='display_frontpage' id='form_display_frontpage'/>\n\r";
      }
      
  $output .= "<label for='form_display_frontpage'>".TXT_WPSC_DISPLAY_FRONT_PAGE."</form>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";


    
  $output .= "          <tr>\n\r";
  $output .= "            <td colspan='2'>\n\r";
  $output .= "<br /><br /><strong class='form_group'>".TXT_WPSC_PRICE_AND_STOCK_CONTROL."</strong>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td rowspan='2'>\n\r";
  $output .= TXT_WPSC_PRICE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' name='price' size='10' value='".number_format($product['price'], 2, '.', '')."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  if($product['notax'] == 1)
    {
    $checked = "checked='true'";
    }
    else
      {
      $checked = "";
      }

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input id='tax' type='checkbox' name='notax' value='yes' $checked />&nbsp;<label for='tax'>".TXT_WPSC_TAXALREADYINCLUDED."</label>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  if($product['donation'] == 1)
    {
    $checked = "checked='true'";
    }
    else
      {
      $checked = "";
      }
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input id='edit_form_donation' type='checkbox' $checked name='donation' value='yes' />&nbsp;<label for='edit_form_donation'>".TXT_WPSC_IS_DONATION."</label>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  if($product['special'] == 1)
    {
    $checked = "checked='true'";
    }
    else
      {
      $checked = "";
      }
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $disable_form = '';
  if($check_variation_value_count > 0)
    {
    if($product['special'] != 1)
      {
      $disable_form = "disabled='true'";
      $disable_form_label = " style='color: #cccccc;'";
      }
    }
  $output .= "<input id='form_special' type='checkbox' $checked name='special' $disable_form value='yes' onclick='hideelement(\"edit_special\")' /> <label for='form_special' $disable_form_label>".TXT_WPSC_SPECIAL."</label>"; 
  if($disable_form != '')
    {
    $output .="<br /><span class='small'>". TXT_WPSC_VARIATIONS_AND_SPECIALS_DONT_MIX."<span>";
    }
  
  if($product['special'] == 1)
    {
    $output .= "            <div id='edit_special' style='display: block;'>\n\r";
    }
    else
      {
      $output .= "            <div id='edit_special' style='display: none;'>\n\r";
      }
  if($product['special'] == 1)
    {
    $output .= "<input type='text' name='special_price' value='".number_format(($product['price']-$product['special_price']), 2, '.', '')."' size='10' />";
    }
    else
      {
      $output .= "<input type='text' name='special_price' value='0.00' size='10' />";
      }
  $output .= "              </div>\n\r";

  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r"; 
  
  if($product['quantity_limited'] == 1)
    {
    $checked = "checked='true'";
    }
    else
      {
      $checked = "";
      }
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
//   $output .= TXT_WPSC_LIMITED_STOCK.": ";
  //$output .= TXT_WPSC_PRODUCTSTOCK.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input id='form_quantity_limited' type='checkbox' $checked name='quantity_limited' value='yes' onclick='hideelement(\"edit_stock\")' /><label for='form_quantity_limited' class='small'>".TXT_WPSC_UNTICKBOX."</label>";
  $variations_output = $variations_processor->variations_grid_view($product['id']); 
  if($variations_output != '')
    {
    //$output .= $variations_output;
    
    $output .= "<div id='edit_stock' style='display: none;'>\n\r";
    $output .= "<input type='hidden' name='quantity' value='".$product['quantity']."' />";
    $output .= "</div>\n\r";
    }
    else
       {
       switch($product['quantity_limited'])
         {
         case 1:
         $output .= "            <div id='edit_stock' style='display: block;'>\n\r";
         break;
         
         default:
         $output .= "            <div id='edit_stock' style='display: none;'>\n\r";
         break;
         }
       $output .= "<input type='text' name='quantity' size='10' value='".$product['quantity']."' />";
       $output .= "              </div>\n\r";
       }
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  
    
  $output .= "          <tr>\n\r";
  $output .= "            <td colspan='2'>\n\r";
  $output .= "<br /><br /><strong class='form_group'>".TXT_WPSC_VARIATION_CONTROL."</strong>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";  
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_ADD_VAR.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= variationslist();
  //$output .= variationslist();
  $output .= "<div id='edit_product_variations'>";

  $output .= "</div>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
    
  if($check_variation_value_count > 0)
    {
    $output .= "          <tr>\n\r";
    $output .= "            <td>\n\r";
    $output .= TXT_WPSC_EDIT_VAR.": ";
    $output .= "            </td>\n\r";
    $output .= "            <td>\n\r";
    //$variations_processor = new nzshpcrt_variations;
    $output .= $variations_processor->display_attached_variations($product['id']);
    $output .= $variations_output;
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    }
  
  

  $output .= "    <tr>\n\r";
  $output .= "      <td colspan='2'>\n\r";
  $output .= "        <br /><br /><strong class='form_group'>".TXT_WPSC_SHIPPING_DETAILS."</strong>\n\r";
  $output .= "      </td>\n\r";
  $output .= "    </tr>\n\r";
  
  $output .= "    <tr>\n\r";
  $output .= "      <td>";
  $output .= TXT_WPSC_LOCAL_PNP;
  $output .= "      </td>\n\r";
  $output .= "      <td>\n\r";
  $output .= "        <input type='text' size='10' name='pnp' value='".$product['pnp']."' />\n\r";
  $output .= "      </td>\n\r";
  $output .= "    </tr>\n\r";
  
  $output .= "    <tr>\n\r";
  $output .= "      <td>";
  $output .= TXT_WPSC_INTERNATIONAL_PNP;
  if($product['international_pnp'] == 0)
    {
    $product['international_pnp'] = "0.00";
    }
  $output .= "      </td>\n\r";
  $output .= "      <td>\n\r";
  $output .= "        <input type='text' size='10' name='international_pnp' value='".$product['international_pnp']."' />\n\r";
  $output .= "      </td>\n\r";
  $output .= "    </tr>\n\r";
  
  $output .= "          <tr>\n\r";
  $output .= "            <td colspan='2'>\n\r";
  $output .= "<br /><br /><strong class='form_group'>".TXT_WPSC_PRODUCTIMAGE."</strong>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 
  if(function_exists("getimagesize"))
    {
    if($product['image'] != '')
      {
      $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/";
      $imagepath = $imagedir . $product['image'];
      include('getimagesize.php');
      $output .= "          <tr>\n\r";
      $output .= "            <td>\n\r";
      $output .= TXT_WPSC_RESIZEIMAGE.": <br />";
      
      $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/";
      $image_size = @getimagesize($imagedir.$product['image']);
      $output .= "<span class='image_size_text'>".$image_size[0]."x".$image_size[1]."</span>";
      //
      //
      
      $output .= "            </td>\n\r";  
      
      $output .= "            <td>\n\r";
// pe.{
    
      $output .= "<table>";// style='border: 1px solid black'
    $output .= "  <tr>";
    $output .= "    <td style='height: 1em;'>";
    $output .= "<input type='hidden' id='current_thumbnail_image' name='current_thumbnail_image' value='" . $product['thumbnail_image'] . "' />";
    $output .= "<input type='radio' ";
    if ($product['thumbnail_state'] == 0)
    {
      $output .= "checked='true'";
    }
    $output .= " name='image_resize' value='0' id='image_resize0' class='image_resize' onclick='hideOptionElement(null, \"image_resize0\")' /> <label for='image_resize0'> ".TXT_WPSC_DONOTRESIZEIMAGE."<br />";
    $output .= "    </td>";
    // Put lightbox here so doesn't move around with DHTML bits
    $output .= "    <td rowspan=4>";
    if(file_exists(ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/".$product['image']))
      {
      $image_location = "product_images/".$product['image'];
      }
      else
      {
      $image_location = "images/".$product['image'];
      }
    $image_link = "".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/$image_location";
    $output .= "<a  href='".$image_link."' rel='edit_product_1' class='thickbox preview_link'><img id='previewimage' src='$image_link' alt='".TXT_WPSC_PREVIEW."' title='".TXT_WPSC_PREVIEW."' />"."</a>";
  $output .= "<br /><span style=\"font-size: 7pt;\">" . TXT_WPSC_PRODUCT_IMAGE_PREVIEW . "</span><br /><br />";
    
    if(($product['thumbnail_image'] != null))
      {
      // && (file_exists($basepath."/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/".$product['thumbnail_image']))
      $thumbnail_size = @getimagesize($imagedir.$product['thumbnail_image']);
      $thumbnail_location = get_option('siteurl') . "/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/" . $product['thumbnail_image'];
      //$output .= "<a id='preview_link' href='".$image_link."' class='thickbox'  rel='".str_replace(" ", "_",$product['name'])."'>";
           
      $output .= "<a id='preview_link' href='".$thumbnail_location . "' rel='edit_product_2' class='thickbox'><img id='previewimage' src='" . $thumbnail_location . "' alt='".TXT_WPSC_PREVIEW."' title='".TXT_WPSC_PREVIEW."' />"."</a>";
      $output .= "<br /><span style=\"font-size: 7pt;\">" . TXT_WPSC_PRODUCT_THUMBNAIL_PREVIEW . "</span><br />";
      }
           
    //<div id='preview_button'><a id='preview_button' href='#'>".TXT_WPSC_PREVIEW."</a></div>
    // onclick='return display_preview_image(".$product['id'].")' 
    $output .= "    </td>";
    $output .= "  </tr>";

    $output .= "  <tr>";
    $output .= "    <td>";
    $output .= "<input type='radio' ";
    if ($product['thumbnail_state'] == 1)
    {
      $output .= "checked='true'";
    }
    $output .= "name='image_resize' value='1' id='image_resize1' class='image_resize' onclick='hideOptionElement(null, \"image_resize1\")' /> <label for='image_resize1'>".TXT_WPSC_USEDEFAULTSIZE." (".get_option('product_image_height') ."x".get_option('product_image_width').")";
    $output .= "    </td>";
    $output .= "  </tr>";

    $output .= "  <tr>";
    $output .= "    <td>";
    $output .= "<input type='radio' ";
    if ($product['thumbnail_state'] == 2)
    {
      $output .= "checked='true'";
    }
    $output .= " name='image_resize' value='2' id='image_resize2' class='image_resize' onclick='hideOptionElement(\"heightWidth\", \"image_resize2\")' /> <label for='image_resize2'>".TXT_WPSC_USESPECIFICSIZE." </label>
    <div id=\"heightWidth\" style=\"display: ";
    
    if ($product['thumbnail_state'] == 2)
    {
      $output .= "block;";
    }
    else
    {
      $output .= "none;";
    }
    
    $output .= "\">
    <input id='image_width' type='text' size='4' name='width' value='' /><label for='image_resize2'>".TXT_WPSC_PXWIDTH."</label>
    <input id='image_height' type='text' size='4' name='height' value='' /><label for='image_resize2'>".TXT_WPSC_PXHEIGHT." </label></div>";
    $output .= "    </td>";
    $output .= "  </tr>";
    $output .= "  <tr>";
    $output .= "    <td>";
    $output .= "<input type='radio' ";
    if ($product['thumbnail_state'] == 3)
    {
      $output .= "checked='true'";
    }
    $output .= " name='image_resize' value='3' id='image_resize3' class='image_resize' onclick='hideOptionElement(\"browseThumb\", \"image_resize3\")' /> <label for='image_resize3'> ".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />";
    $output .= "<div id='browseThumb' style='display: ";
    
    if($product['thumbnail_state'] == 3)
    {
       $output .= "block";
    }
    else
    {
      $output .= "none";
    }

    $output .= ";'>\n\r<input type='file' name='thumbnailImage' size='15' value='' />";
    $output .= "</div>\n\r";
    $output .= "    </td>";
      $output .= "  </tr>";
    // }.pe

      $output .= "</table>";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
      }
    }
  
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_UPLOADNEWIMAGE.": <br />";
  $output .= "<input type='file' name='image' value='' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  
  if(function_exists("getimagesize"))
    {
    if($product['image'] == '')
      {
      $output .= "          <tr>\n\r";
      $output .= "            <td></td>\n\r";
      $output .= "            <td>\n\r";
      $output .= "<table>\n\r";
      if(is_numeric(get_option('product_image_height')) && is_numeric(get_option('product_image_width')))
        {
        $output .= "      <tr>\n\r";
        $output .= "        <td>\n\r";
        $output .= "      <input type='radio' checked='true' name='image_resize' value='0' id='image_resize0' class='image_resize' onclick='hideOptionElement(null, \"image_resize0\");' /> <label for='image_resize0'>".TXT_WPSC_DONOTRESIZEIMAGE."</label>\n\r";
        $output .= "        </td>\n\r";
        $output .= "      </tr>\n\r";
        $output .= "      <tr>\n\r";
        $output .= "        <td>\n\r";
        $output .= "          <input type='radio' name='image_resize' value='1' id='image_resize1' class='image_resize' onclick='hideOptionElement(null, \"image_resize1\");' /> <label for='image_resize1'>".TXT_WPSC_USEDEFAULTSIZE." (".get_option('product_image_height') ."x".get_option('product_image_width').")</label>\n\r";
        $output .= "        </td>\n\r";
        $output .= "      </tr>\n\r";
        }
      $output .= "      <tr>\n\r";
      $output .= "        <td>\n\r";
      $output .= "          <input type='radio' name='image_resize' value='2' id='image_resize2' class='image_resize' onclick='hideOptionElement(\"heightWidth\", \"image_resize2\");' />\n\r";
      $output .= "      <label for='image_resize2'>".TXT_WPSC_USESPECIFICSIZE."</label>\n\r";
      $output .= "          <div id='heightWidth' style='display: none;'>\n\r";
      $output .= "        <input type='text' size='4' name='width' value='' /><label for='image_resize2'>".TXT_WPSC_PXWIDTH."</label>\n\r";
      $output .= "        <input type='text' size='4' name='height' value='' /><label for='image_resize2'>".TXT_WPSC_PXHEIGHT."</label>\n\r";
      $output .= "      </div>\n\r";
      $output .= "        </td>\n\r";
      $output .= "      </tr>\n\r";
      $output .= "      <tr>\n\r";
      $output .= "      <td>\n\r";
      $output .= "        <input type='radio' name='image_resize' value='3' id='image_resize3' class='image_resize' onclick='hideOptionElement(\"browseThumb\", \"image_resize3\");' />\n\r";
      $output .= "        <label for='image_resize3'>".TXT_WPSC_SEPARATETHUMBNAIL."</label><br />";
      $output .= "        <div id='browseThumb' style='display: none;'>\n\r";
      $output .= "          <input type='file' name='thumbnailImage' value='' />\n\r";
      $output .= "        </div>\n\r";
      $output .= "      </td>\n\r";
      $output .= "    </tr>\n\r";
      $output .= "  </table>\n\r";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
      }
    }
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input id='delete_image' type='checkbox' name='deleteimage' value='1' /> ";
  $output .= "<label for='delete_image'>".TXT_WPSC_DELETEIMAGE."</label>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";


  if(function_exists('edit_multiple_image_form'))
    {
    $output .= edit_multiple_image_form($product['id']); 
    }
    
  if($product['file'] > 0)
    {
    $output .= "          <tr>\n\r";
    $output .= "            <td colspan='2'>\n\r";
    $output .= "<br /><strong class='form_group'>".TXT_WPSC_PRODUCTDOWNLOAD."</strong>";
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    
    $output .= "          <tr>\n\r";
    $output .= "            <td>\n\r";
    $output .= TXT_WPSC_PREVIEW_FILE.": ";
    $output .= "            </td>\n\r";
    $output .= "            <td>\n\r";    
    
    $output .= "<a class='admin_download' href='index.php?admin_preview=true&product_id=".$product['id']."' style='float: left;' ><img align='absmiddle' src='../wp-content/plugins/wp-shopping-cart/images/download.gif' alt='' title='' /><span>".TXT_WPSC_CLICKTODOWNLOAD."</span></a>";
    
    $file_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$product['file']."' LIMIT 1",ARRAY_A);
    if(($file_data != null) && ($file_data[0]['mimetype'] == 'audio/mpeg') && (function_exists('listen_button')))
      {
      $output .= "&nbsp;&nbsp;&nbsp;".listen_button($file_data[0]['idhash']);
      }
      
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
  
              
    $output .= "          <tr>\n\r";
    $output .= "            <td>\n\r";
    $output .= TXT_WPSC_DOWNLOADABLEPRODUCT.": ";
    $output .= "            </td>\n\r";
    $output .= "            <td>\n\r";
    $output .= "<input type='file' name='file' value='' /><br />";    
    $output .= wpsc_select_product_file($product['id']);
    $output .= "            </td>\n\r";
    $output .= "          </tr>\n\r";
    
    if(function_exists("make_mp3_preview") && ($file_data['mimetype'] == 'audio/mpeg'))
      {
      $output .= "          <tr>\n\r";
      $output .= "            <td>\n\r";
      $output .= TXT_WPSC_PREVIEW_FILE.": ";
      $output .= "            </td>\n\r";
      $output .= "            <td>\n\r";
      $output .= "<input type='file' name='preview_file' value='' /><br />";
      $output .= "<span class='admin_product_notes'>".TXT_WPSC_PREVIEW_FILE_NOTE."</span>";
      $output .= "<br /><br />";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
      }
    }
    else
      {
      $output .= "          <tr>\n\r";
      $output .= "            <td colspan='2'>\n\r";
      $output .= "<br /><strong class='form_group'>".TXT_WPSC_PRODUCTDOWNLOAD."</strong>";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
      
      $output .= "       <tr>";
      $output .= "         <td>";
      $output .= "".TXT_WPSC_DOWNLOADABLEPRODUCT.":";
      $output .= "        </td>";
      $output .= "        <td>";
      $output .= "          <input type='file' name='file' value='' />";
      $output .= wpsc_select_product_file($product['id']);
      $output .= "        </td>";
      $output .= "      </tr>";
      }
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input  class='button' style='float:left;'  type='submit' name='submit' value='".TXT_WPSC_EDIT_PRODUCT."' />";
  $output .= "<a class='button delete_button' ' href='admin.php?page=wp-shopping-cart/display-items.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE_PRODUCT."</a>";
  $output .= "            <td>\n\r";
  $output .= "          </tr>\n\r";
  
  $output .= "        </table>\n\r";
  return $output;
  }

function nzshpcrt_getcategoryform($catid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;
  $sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `id`=$catid LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A) ;
  $product = $product_data[0];
  $output .= "        <table>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' name='title' value='".stripslashes($product['name'])."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_CATEGORY_PARENT.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= wpsc_parent_category_list($product['id'], $product['category_parent']);
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_IMAGE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='file' name='image' value='' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  if(function_exists("getimagesize"))
    {
    if($product['image'] != '')
      {
      $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/category_images/";
      $imagepath = $imagedir . $product['image'];
      include('getimagesize.php');
      $output .= "          <tr>\n\r";
      $output .= "            <td>\n\r";
      $output .= "            </td>\n\r";
      $output .= "            <td>\n\r";
      $output .= TXT_WPSC_HEIGHT.":<input type='text' size='6' name='height' value='".$imagetype[1]."' /> ".TXT_WPSC_WIDTH.":<input type='text' size='6' name='width' value='".$imagetype[0]."' /><br /><span class='small'>$nzshpcrt_imagesize_info</span>";
      $output .= "            </td>\n\r";
      $output .= "          </tr>\n\r";
      }
      else
        {
        $output .= "          <tr>\n\r";
        $output .= "            <td>\n\r";
        $output .= "            </td>\n\r";
        $output .= "            <td>\n\r";
        $output .= TXT_WPSC_HEIGHT.":<input type='text' size='6' name='height' value='".get_option('product_image_height')."' /> ".TXT_WPSC_WIDTH.":<input type='text' size='6' name='width' value='".get_option('product_image_width')."' /><br /><span class='small'>$nzshpcrt_imagesize_info</span>";
        $output .= "            </td>\n\r";
        $output .= "          </tr>\n\r";
        }
    }

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DELETEIMAGE.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='checkbox' name='deleteimage' value='1' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;' type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=wp-shopping-cart/display-category.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r"; 
  return $output;
  }

function nzshpcrt_getbrandsform($catid)
  {
  global $wpdb,$nzshpcrt_imagesize_info;

  $sql = "SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `id`='$catid' LIMIT 1";
  $product_data = $wpdb->get_results($sql,ARRAY_A) ;
  $product = $product_data[0];
  $output .= "        <table>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' name='title' value='".stripslashes($product['name'])."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_DESCRIPTION.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<textarea name='description' cols='40' rows='8' >".stripslashes($product['description'])."</textarea>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$product['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;' type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=wp-shopping-cart/display-brands.php&amp;deleteid=".$product['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r";
  return $output;
  }
  
function nzshpcrt_getvariationform($variation_id)
  {
  global $wpdb,$nzshpcrt_imagesize_info;

  $variation_sql = "SELECT * FROM `".$wpdb->prefix."product_variations` WHERE `id`='$variation_id' LIMIT 1";
  $variation_data = $wpdb->get_results($variation_sql,ARRAY_A) ;
  $variation = $variation_data[0];
  $output .= "        <table>\n\r";
  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_NAME.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='text' name='title' value='".stripslashes($variation['name'])."' />";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= TXT_WPSC_VARIATION_VALUES.": ";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $variation_values_sql = "SELECT * FROM `".$wpdb->prefix."variation_values` WHERE `variation_id`='$variation_id' ORDER BY `id` ASC";
  $variation_values = $wpdb->get_results($variation_values_sql,ARRAY_A);
  $variation_value_count = count($variation_values);
  $output .= "<div id='edit_variation_values'>";
  $num = 0;
  foreach($variation_values as $variation_value)
    {
    $output .= "<span id='variation_value_".$num."'>";
    $output .= "<input type='text' name='variation_values[".$variation_value['id']."]' value='".stripslashes($variation_value['name'])."' />";
    if($variation_value_count > 1)
      {
      $output .= " <a  class='image_link' onclick='remove_variation_value(\"variation_value_".$num."\",".$variation_value['id'].")' href='#'><img src='".get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/trash.gif' alt='".TXT_WPSC_DELETE."' title='".TXT_WPSC_DELETE."' /></a>";
      //admin.php?page=wp-shopping-cart/display_variations.php&amp;delete_value=true&amp;variation_id=".$variation_id."&amp;value_id=".$variation_value['id']."
      }
    $output .= "<br />";
    $output .= "</span>";
    $num++;
    }
  $output .= "</div>";
  $output .= "<a href='#'  onclick='return add_variation_value(\"edit\")'>".TXT_WPSC_ADD."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
  $output .= "          </tr>\n\r";

  $output .= "          <tr>\n\r";
  $output .= "            <td>\n\r";
  $output .= "            </td>\n\r";
  $output .= "            <td>\n\r";
  $output .= "<input type='hidden' name='prodid' value='".$variation['id']."' />";
  $output .= "<input type='hidden' name='submit_action' value='edit' />";
  $output .= "<input class='button' style='float:left;'  type='submit' name='submit' value='".TXT_WPSC_EDIT."' />";
  $output .= "<a class='button delete_button' href='admin.php?page=wp-shopping-cart/display_variations.php&amp;deleteid=".$variation['id']."' onclick=\"return conf();\" >".TXT_WPSC_DELETE."</a>";
  $output .= "            </td>\n\r";
  $output .= "          </tr>\n\r";
 $output .= "        </table>\n\r";
  return $output;
  }

function nzshpcrt_submit_checkout()
  {
 /*
  * This is the function used for handling the submitted checkout page
  */
  global $wpdb, $nzshpcrt_gateways, $user_ID;
  session_start();
  if(get_option('permalink_structure') != '')
    {
    $seperator ="?";
    }
    else
      {
      $seperator ="&";
      }
  if(($_POST['submitwpcheckout'] == 'true'))
    {
    //exit("<pre>".print_r($_POST,true)."</pre>");
    $returnurl = "Location: ".get_option('checkout_url').$seperator."total=".$_GET['total'];
    $_SESSION['collected_data'] = $_POST['collected_data'];
    $any_bad_inputs = false;
    foreach($_POST['collected_data'] as $value_id => $value)
      {
      $form_sql = "SELECT * FROM `".$wpdb->prefix."collect_data_forms` WHERE `id` = '$value_id' LIMIT 1";
      $form_data = $wpdb->get_results($form_sql,ARRAY_A);
      $form_data = $form_data[0];
      $bad_input = false;
      if($form_data['mandatory'] == 1)
        {        
        switch($form_data['type'])
          {
          case "email":
          if(!preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/",$value))
            {
            $any_bad_inputs = true;
            $bad_input = true;
            }
          break;
          
          case "delivery_country":
//           if(($value != null))
//             {
//             $_SESSION['delivery_country'] == $value;
//             }
          break;
          
          case "country":
          
          break;
          
          default:
          if($value == null)
            {
            $any_bad_inputs = true;
            $bad_input = true;
            }
          break;
          }
        if($bad_input === true)
          {
          switch($form_data['name'])
            {
            case TXT_WPSC_FIRSTNAME:
            $bad_input_message .= TXT_WPSC_PLEASEENTERAVALIDNAME . "";
            break;
    
            case TXT_WPSC_LASTNAME:
            $bad_input_message .= TXT_WPSC_PLEASEENTERAVALIDSURNAME . "";
            break;
    
            case TXT_WPSC_EMAIL:
            $bad_input_message .= TXT_WPSC_PLEASEENTERAVALIDEMAILADDRESS . "";
            break;
    
            case TXT_WPSC_ADDRESS1:
            case TXT_WPSC_ADDRESS2:
            $bad_input_message .= TXT_WPSC_PLEASEENTERAVALIDADDRESS . "";
            break;
    
            case TXT_WPSC_CITY:
            $bad_input_message .= TXT_WPSC_PLEASEENTERAVALIDCITY . "";
            break;
    
            case TXT_WPSC_PHONE:
            $bad_input_message .= TXT_WPSC_PLEASEENTERAVALIDPHONENUMBER . "";
            break;
    
            case TXT_WPSC_COUNTRY:
            $bad_input_message .= TXT_WPSC_PLEASESELECTCOUNTRY . "";
            break;
    
            default:
            $bad_input_message .= TXT_WPSC_PLEASEENTERAVALID . " " . strtolower($form_data['name']) . ".";
            break;
            }
          $bad_input_message .= "\n\r";
          }
        }
      }
    
    
 
    foreach((array)$_SESSION['nzshpcrt_cart'] as $item)
      {
      $in_stock = check_in_stock($item->product_id, $item->product_variations, $item->quantity);
      if($in_stock == false)
        {
        $bad_input_message .= TXT_WPSC_ITEM_GONE_OUT_OF_STOCK . "";
        $bad_input_message .= "\n\r";
        $any_bad_inputs = true;
        break;
        }
      }
    
    if($any_bad_inputs === true)
      {
      $_SESSION['nzshpcrt_checkouterr'] = nl2br($bad_input_message);
      header($returnurl);
      exit();     
      }
    $cart = $_SESSION['nzshpcrt_cart'];
    $_SESSION['checkoutdata'] = $_POST;
    if($_POST['agree'] != 'yes')
      {
      $_SESSION['nzshpcrt_checkouterr'] = TXT_WPSC_PLEASEAGREETERMSANDCONDITIONS;
      header($returnurl);
      exit();
      }
    
    if($cart == null)
      {
      $_SESSION['nzshpcrt_checkouterr'] = TXT_WPSC_NOTHINGINYOURSHOPPINGCART;
      header($returnurl);
      exit();
      }
    $sessionid = (mt_rand(100,999).time());
   
   if(is_numeric($user_ID) && ($user_ID > 0))
     {     
     }
     else
       {
       $user_ID = 'null';
       }
    $sql = "INSERT INTO `".$wpdb->prefix."purchase_logs` ( `totalprice` , `sessionid` , `firstname`, `lastname`, `email`, `address`, `phone`, `date`, `billing_country`, `shipping_country`,`shipping_region`, `user_ID` ) VALUES ( '".$wpdb->escape($_SESSION['nzshpcrt_totalprice'])."', '".$sessionid."', '".$wpdb->escape($_POST['firstname'])."', '".$wpdb->escape($_POST['lastname'])."', '".$_POST['email']."', '".$wpdb->escape($_POST['address'])."', '".$wpdb->escape($_POST['phone'])."' , '".time()."', '".$_SESSION['selected_country']."', '".$_SESSION['delivery_country']."', '".$_SESSION['selected_region']."' , $user_ID)";
   $wpdb->query($sql) ;
   
   $log_id = $wpdb->get_var("SELECT `id` FROM `".$wpdb->prefix."purchase_logs` WHERE `sessionid` IN('".$sessionid."') LIMIT 1") ;
   foreach($_POST['collected_data'] as $value_id => $value)
     {
     $wpdb->query("INSERT INTO `".$wpdb->prefix."submited_form_data` ( `log_id` , `form_id` , `value` ) VALUES ( '".$log_id."', '".$value_id."', '".$value."');") ;
     }
   
   if(function_exists("nzshpcrt_user_log")) 
     {
     $saved_data_sql = "SELECT * FROM `".$wpdb->prefix."usermeta` WHERE `user_id` = '".$user_ID."' AND `meta_key` = 'wpshpcrt_usr_profile';";
     $saved_data = $wpdb->get_row($saved_data_sql,ARRAY_A);
  
     $new_meta_data = serialize($_POST['collected_data']);
     if(($saved_data != null))
       {
       $wpdb->query("UPDATE `".$wpdb->prefix."usermeta` SET `meta_value` =  '$new_meta_data' WHERE `user_id` IN ('$user_ID') AND `meta_key` IN ('wpshpcrt_usr_profile');");
       }
       else if(is_numeric($user_ID))
         {
         $wpdb->query("INSERT INTO `".$wpdb->prefix."usermeta` ( `user_id` , `meta_key` , `meta_value` ) VALUES ( ".$user_ID.", 'wpshpcrt_usr_profile', '$new_meta_data');");
         }
      }
   
   $downloads = get_option('max_downloads');
   $all_donations = true;
   foreach($cart as $cart_item)
     {
     $row = $cart_item->product_id;
     $quantity = $cart_item->quantity;
     $variations = $cart_item->product_variations;
     //exit("<pre>".print_r($cart_item,true)."</pre>");
     $product_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$row' LIMIT 1",ARRAY_A) ;
     if($product_data['file'] > 0)
       {
       $wpdb->query("INSERT INTO `".$wpdb->prefix."download_status` ( `fileid` , `purchid` , `downloads` , `active` , `datetime` ) VALUES ( '".$product_data['file']."', '".$log_id."', '$downloads', '0', NOW( ));");
       }
     
     if($product_data['donation'] == 1)
       {
       $price = $cart_item->donation_price;
       $gst = 0;
       $donation = 1;
       }
       else
       {
       $price = calculate_product_price($row, $variations);
       if($product_data['notax'] != 1)
         {
         $price = nzshpcrt_calculate_tax($price, $_SESSION['selected_country'], $_SESSION['selected_region']);
         if(get_option('base_country') == $_SESSION['selected_country'])
           {
           $country_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."currency_list` WHERE `isocode` IN('".get_option('base_country')."') LIMIT 1",ARRAY_A);
           if(($country_data['has_regions'] == 1))
             {
             if(get_option('base_region') == $_SESSION['selected_region'])
               {
               $region_data = $wpdb->get_row("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax` WHERE `".$wpdb->prefix."region_tax`.`country_id` IN('".$country_data['id']."') AND `".$wpdb->prefix."region_tax`.`id` IN('".get_option('base_region')."') ",ARRAY_A) ;
               }
             $gst =  $region_data['tax'];
             }
             else
               {
               $gst =  $country_data['tax'];
               }
            }
          }
          else { $gst = 0; }
        $donation = 0;
        $all_donations = false;
        }  
            
    $country = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."submited_form_data` WHERE `log_id`=".$log_id." AND `form_id` = '".get_option('country_form_field')."' LIMIT 1",ARRAY_A);
    $country = $country[0]['value'];
     
     $country_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."currency_list` WHERE `isocode` IN('".get_option('base_country')."') LIMIT 1",ARRAY_A);
     
     // $shipping = $base_shipping + ($additional_shipping * $quantity);
     $shipping = 0;
     $cartsql = "INSERT INTO `".$wpdb->prefix."cart_contents` ( `prodid` , `purchaseid`, `price`, `pnp`, `gst`, `quantity`, `donation` ) VALUES ('".$row."', '".$log_id."','".$price."','".$shipping."', '".$gst."','".$quantity."', '".$donation."')";
    
  
     
     $wpdb->query($cartsql);
     $cart_id = $wpdb->get_results("SELECT LAST_INSERT_ID() AS `id` FROM `".$wpdb->prefix."product_variations` LIMIT 1",ARRAY_A);
     $cart_id = $cart_id[0]['id'];
     if($variations != null)
       {
       foreach($variations as $variation => $value)
         {
         $wpdb->query("INSERT INTO `".$wpdb->prefix."cart_item_variations` ( `cart_id` , `variation_id` , `venue_id` ) VALUES ( '".$cart_id."', '".$variation."', '".$value."' );");
         }
       }
     
     /*
      * This code decrements the stock quantitycart_item_variations`
     */
     if(is_array($variations))
       {
       $variation_values = array_values($variations);
       }
     //$debug .= "<pre>".print_r($variations,true)."</pre>";
     if($product_data['quantity_limited'] == 1)
       {
       switch(count($variation_values))
         {
         case 2:
         $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$product_data['id']."' AND (`variation_id_1` = '".$variation_values[0]."' AND `variation_id_2` = '".$variation_data[1]."') OR (`variation_id_1` = '".$variation_values[1]."' AND `variation_id_2` = '".$variation_values[0]."') LIMIT 1",ARRAY_A);
         //$debug .= "<pre>".print_r($variation_stock_data,true)."</pre>";
         $wpdb->query("UPDATE `".$wpdb->prefix."variation_priceandstock` SET `stock` = '".($variation_stock_data['stock']-$quantity)."'  WHERE `id` = '".$variation_stock_data['id']."' LIMIT 1",ARRAY_A);
         break;
         
         case 1:
         $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$product_data['id']."' AND (`variation_id_1` = '".$variation_values[0]."' AND `variation_id_2` = '0') LIMIT 1",ARRAY_A);
         //$debug .= "<pre>".print_r($variation_stock_data,true)."</pre>";
         $wpdb->query("UPDATE `".$wpdb->prefix."variation_priceandstock` SET `stock` = '".($variation_stock_data['stock']-$quantity)."'  WHERE `id` = '".$variation_stock_data['id']."' LIMIT 1",ARRAY_A);
         break;
        
         default:
         /* normal form of decrementing stock */
         $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `quantity`='".($product_data['quantity']-$quantity)."' WHERE `id`='".$product_data['id']."' LIMIT 1");
         break;
         }
       }     
     }
   //mail( get_option('purch_log_email'),('debug from '.date("d/m/Y H:i:s")), $debug);
   $curgateway = get_option('payment_gateway');
   
  if(get_option('permalink_structure') != '')
    {
    $seperator ="?";
    }
    else
      {
      $seperator ="&";
      }
  if(($_POST['payment_method'] == 2) && (get_option('payment_method') == 2))
    {
    foreach($nzshpcrt_gateways as $gateway)
      {
      if($gateway['internalname'] == 'testmode' )
        {
        $gateway_used = $gateway['internalname'];
        $wpdb->query("UPDATE `".$wpdb->prefix."purchase_logs` SET `gateway` = '".$gateway_used."' WHERE `id` = '".$log_id."' LIMIT 1 ;");
        $gateway['function']($seperator, $sessionid);
        }
      }
    }
    else
      {
      foreach($nzshpcrt_gateways as $gateway)
        {
        if($gateway['internalname'] == $curgateway )
          {
          $gateway_used = $gateway['internalname'];
          $wpdb->query("UPDATE `".$wpdb->prefix."purchase_logs` SET `gateway` = '".$gateway_used."' WHERE `id` = '".$log_id."' LIMIT 1 ;");
          $gateway['function']($seperator, $sessionid);
          }
        }
      }
    //require_once("merchants.php");
    }
  }
   
   
function nzshpcrt_download_file()
  {
  global $wpdb,$user_level,$wp_rewrite; 
  get_currentuserinfo();  
  function readfile_chunked($filename, $retbytes = true)
    {
    $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
    $buffer = '';
    $cnt = 0;
    $handle = fopen($filename, 'rb');
    if($handle === false)
      {
      return false;
      }
      while (!feof($handle))
        {
        $buffer = fread($handle, $chunksize);
        echo $buffer;
        ob_flush();
        flush();
        if($retbytes)
          {
          $cnt += strlen($buffer);
          }
        }
    $status = fclose($handle);
    if($retbytes && $status)
      {
      return $cnt; // return num. bytes delivered like readfile() does.
      }
    return $status;
    }  
  
  if(is_numeric($_GET['downloadid']))
    {
    $id = $_GET['downloadid'];
    $download_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."download_status` WHERE `id`='".$id."' AND `downloads` > '0' AND `active`='1' LIMIT 1",ARRAY_A) ;
    if($download_data != null)
      {
      $file_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$download_data['fileid']."' LIMIT 1",ARRAY_A) ;
      $file_data = $file_data[0];
      $wpdb->query("UPDATE `".$wpdb->prefix."download_status` SET `downloads` = '".($download_data['downloads']-1)."' WHERE `id` = '$id' LIMIT 1");
      $wpdb->query("UPDATE `".$wpdb->prefix."purchase_logs` SET `processed` = '4' WHERE `id` = '".$download_data['purchid']."' LIMIT 1");
      $filedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/files/";
      header('Content-Type: '.$file_data['mimetype']);      
      header('Content-Length: '.filesize($filedir.$file_data['idhash']));
      header('Content-Disposition: attachment; filename="'.stripslashes($file_data['filename']).'"');
      
      $filename = $filedir.$file_data['idhash'];
      readfile_chunked($filename);   
      //print $contents;     
      exit();
      }
    }
    else
      {
      if(($_GET['admin_preview'] == "true") && is_numeric($_GET['product_id']))
        {
        $product_id = $_GET['product_id'];
        $product_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_list` WHERE `id` = '$product_id' LIMIT 1",ARRAY_A);
        //exit("<pre>".print_r($product_data,true)."</pre>");
        if(is_numeric($product_data[0]['file']) && ($product_data[0]['file'] > 0))
          {
          $file_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_files` WHERE `id`='".$product_data[0]['file']."' LIMIT 1",ARRAY_A) ;
          $file_data = $file_data[0];
          $filedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/files/";
          header('Content-Type: '.$file_data['mimetype']);      
          //header('Content-Length: '.filesize($filedir.$file_data['idhash']));
          if($_GET['preview_track'] != 'true')
            {
            header('Content-Disposition: attachment; filename="'.$file_data['filename'].'"');
            }
            else
            {
            header('Content-Disposition: inline; filename="'.$file_data['filename'].'"');
            }
          $filename = $filedir.$file_data['idhash'];  
          readfile_chunked($filename);   
          exit();
          }
        }
      }
  }

function nzshpcrt_display_preview_image()
  {
  global $wpdb;
  if(is_numeric($_GET['productid']) || is_numeric($_GET['image_id']))
    {
     if(function_exists("getimagesize"))
      {
      if(is_numeric($_GET['productid']))
        {
        $imagesql = "SELECT `image`,`thumbnail_image` FROM `".$wpdb->prefix."product_list` WHERE `id`='".$_GET['productid']."' LIMIT 1";
        $imagedata = $wpdb->get_row($imagesql,ARRAY_A);
        if($_GET['thumbnail'] == 'true')
          {
          $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/";
          if($imagedata['thumbnail_image'] != '')
            {
            $image_name = $imagedata['thumbnail_image'];
            }
            else
              {
              $image_name = $imagedata['image'];
              }
          $imagepath = $imagedir . $image_name;
          }
          else
          {
          $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/";
          $imagepath = $imagedir . $imagedata['image'];
          }
        }
        else if($_GET['image_id'])
        {
        $image_id = $_GET['image_id'];
        $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/";
        $image = $wpdb->get_var("SELECT `image` FROM `".$wpdb->prefix."product_images` WHERE `id` = '$image_id' LIMIT 1");
        $imagepath = $imagedir . $image;
        }
      
      
      $image_size = @getimagesize($imagepath);
      if(is_numeric($_GET['height']) && is_numeric($_GET['width']))
        {
        $height = $_GET['height'];
        $width = $_GET['width'];
        }
        else
          {
          $width = $image_size[0];
          $height = $image_size[1];
          }
      if(($height > 0) && ($height <= 1024) && ($width > 0) && ($width <= 1024))
       {
       include("image_preview.php");
       }
       else
         {
         $width = $image_size[0];
         $height = $image_size[1];
         include("image_preview.php");
         }
      }
    }
  }
  
  
function nzshpcrt_listdir($dirname)
    {
    /*
    lists the merchant directory
    */
     $dir = @opendir($dirname);
     $num = 0;
     while(($file = @readdir($dir)) !== false)
       {
       //filter out the dots, macintosh hidden files and any backup files
       if(($file != "..") && ($file != ".") && ($file != ".DS_Store") && !stristr($file, "~") && !( strpos($file, ".") === 0 ))
         {
         $dirlist[$num] = $file;
         $num++;
         }
       }
    if($dirlist == null)
      {
      $dirlist[0] = "paypal.php";
      $dirlist[1] = "testmode.php";
      }
    return $dirlist; 
    }
    
    

function nzshpcrt_product_rating($prodid)
      {
      global $wpdb;
      $get_average = $wpdb->get_results("SELECT AVG(`rated`) AS `average`, COUNT(*) AS `count` FROM `".$wpdb->prefix."product_rating` WHERE `productid`='".$prodid."'",ARRAY_A);
      $average = floor($get_average[0]['average']);
      $count = $get_average[0]['count'];
      $output .= "  <span class='votetext'>";
      for($l=1; $l<=$average; ++$l)
        {
        $output .= "<img class='goldstar' src='". get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/gold-star.gif' alt='$l' title='$l' />";
        }
      $remainder = 5 - $average;
      for($l=1; $l<=$remainder; ++$l)
        {
        $output .= "<img class='goldstar' src='". get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/grey-star.gif' alt='$l' title='$l' />";
        }
      $output .=  "<span class='vote_total'>&nbsp;(<span id='vote_total_$prodid'>".$count."</span>)</span> \r\n";
      $output .=  "</span> \r\n";
      return $output;
      }

// this appears to have some star rating code in it
function nzshpcrt_product_vote($prodid, $starcontainer_attributes = '')
      {
      global $wpdb;
      $output = null;
      $useragent = $_SERVER['HTTP_USER_AGENT'];
      $visibility = "style='display: none;'";
      
      preg_match("/(?<=Mozilla\/)[\d]*\.[\d]*/", $useragent,$rawmozversion );
      $mozversion = $rawmozversion[0];
      if(stristr($useragent,"opera"))
        {
        $firstregexp = "Opera[\s\/]{1}\d\.[\d]+";
        }
        else
          {
          $firstregexp = "MSIE\s\d\.\d";
          }
      preg_match("/$firstregexp|Firefox\/\d\.\d\.\d|Netscape\/\d\.\d\.\d|Safari\/[\d\.]+/", $useragent,$rawbrowserinfo);
      $browserinfo = preg_split("/[\/\s]{1}/",$rawbrowserinfo[0]);
      $browsername = $browserinfo[0];
      $browserversion = $browserinfo[1];  
      
      //exit($browsername . " " . $browserversion);
       
      if(($browsername == 'MSIE') && ($browserversion < 7.0))
        {
        $starimg = ''. get_option('siteurl').'/wp-content/plugins/wp-shopping-cart/images/star.gif';
        $ie_javascript_hack = "onmouseover='ie_rating_rollover(this.id,1)' onmouseout='ie_rating_rollover(this.id,0)'";
        }
        else 
          {
          $starimg = ''. get_option('siteurl').'/wp-content/plugins/wp-shopping-cart/images/24bit-star.png';
          $ie_javascript_hack = '';
          }
       
      $cookie_data = explode(",",$_COOKIE['voting_cookie'][$prodid]);
       
      if(is_numeric($cookie_data[0]))
        {
        $vote_id = $cookie_data[0];
        }
      
      $chkrate = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_rating` WHERE `id`='".$vote_id."' LIMIT 1",ARRAY_A);
      //$output .= "<pre>".print_r($chkrate,true)."</pre>";
      if($chkrate[0]['rated'] > 0)
        {
        $rating = $chkrate[0]['rated'];
        $type = 'voted';
        }
        else
          {
          $rating = 0;
          $type = 'voting';
          }
      //$output .= "<pre>".print_r($rating,true)."</pre>";
      $output .=  "<div class='starcontainer' $starcontainer_attributes >\r\n";
      for($k=1; $k<=5; ++$k)
        {
        $style = '';
        if($k <= $rating)
          {
          $style = "style='background: url(". get_option('siteurl')."/wp-content/plugins/wp-shopping-cart/images/gold-star.gif)'";
          }
        $output .= "      <a id='star".$prodid."and".$k."_link' onclick='rate_item(".$prodid.",".$k.")' class='star$k' $style $ie_javascript_hack ><img id='star".$prodid."and".$k."' class='starimage' src='$starimg' alt='$k' title='$k' /></a>\r\n";
        }
      $output .=  "   </div>\r\n";
      $output .= "";
      $voted = TXT_WPSC_CLICKSTARSTORATE;
      
      switch($ratecount[0]['count'])
        {
        case 0:
        $votestr = TXT_WPSC_NOVOTES;
        break;
        
        case 1:
        $votestr = TXT_WPSC_1VOTE;
        break;
        
        default:
        $votestr = $ratecount[0]['count']." ".TXT_WPSC_VOTES2;
        break;
        }
        
      for($i= 5; $i>= 1; --$i)
         {
        //$tmpcount = $this->db->GetAll("SELECT COUNT(*) AS 'count' FROM `pxtrated` WHERE `pxtid`=".$dbdat['rID']." AND `rated`=$i");
            
         switch($tmpcount[0]['count'])
           {
           case 0:
           $othervotes .= "";
           break;
           
           case 1:
           $othervotes .= "<br />". $tmpcount[0]['count'] . " ".TXT_WPSC_PERSONGIVEN." $i ".TXT_WPSC_PERSONGIVEN2;
           break;
           
           default:
           $othervotes .= "<br />". $tmpcount[0]['count'] . " ".TXT_WPSC_PEOPLEGIVEN." $i ".TXT_WPSC_PEOPLEGIVEN2;
           break;
           }  
         } /*
      $output .=  "</td><td class='centerer2'>&nbsp;</td></tr>\r\n";
      $output .= "<tr><td colspan='3' class='votes' >\r\n";//id='startxtmove'
      $output .= "   <p class='votes'> ".$votestr."<br />$voted <br />
      $othervotes</p>";*/
      
      return Array($output,$type);
      } //*/
  

 function get_country($country_code)  
  {
  global $wpdb;
  $country = $wpdb->get_var("SELECT `country` FROM `".$wpdb->prefix."currency_list` WHERE `isocode` IN ('".$country_code."') LIMIT 1");
  return $country; 
  }

 function get_region($region_code)  
  {
  global $wpdb;
  $region = $wpdb->get_var("SELECT `name` FROM `".$wpdb->prefix."region_tax` WHERE `id` IN('$region_code')");
  return $region; 
  }
  
function get_brand($brand_id)  
  {
  global $wpdb;
  $brand_data = $wpdb->get_results("SELECT `name` FROM `".$wpdb->prefix."product_brands` WHERE `id` IN ('".$brand_id."') LIMIT 1",ARRAY_A);
  return $brand_data[0]['name']; 
  }


function filter_input_wp($input)
  {
  // if the input is numeric, then its probably safe
  if(is_numeric($input))
    {
    $output = $input;
    }
    else
      {
      // if its not numeric, then make it safe
      if(!get_magic_quotes_gpc())
        {
        $output = mysql_real_escape_string($input);
        }
        else
          {
          $output = mysql_real_escape_string(stripslashes($input));
          }
      }
    return $output;
    }
    
function make_csv($array)
  {
  $count = count($array);
  $num = 1;
  foreach($array as $value)
    {
    $output .= "'$value'";
    if($num < $count)
      {
      $output .= ",";
      }
    $num++;
    }
  return $output;
  }   
  
function nzshpcrt_product_log_rss_feed()
  {
  echo "<link type='application/rss+xml' href='".get_option('siteurl')."/index.php?rss=true&amp;rss_key=key&amp;action=purchase_log&amp;type=rss' title='WP E-Commerce Purchase Log RSS' rel='alternate'/>";
  }
  
function nzshpcrt_product_list_rss_feed()
  {
  echo "<link rel='alternate' type='application/rss+xml' title='WP E-Commerce Product List RSS' href='".get_option('siteurl')."/index.php?rss=true&amp;action=product_list&amp;type=rss'/>";
  }


//handles replacing the tags in the pages
  
function nzshpcrt_products_page($content = '')
  {
  if(preg_match("/\[productspage\]/",$content))
    {
    $GLOBALS['nzshpcrt_activateshpcrt'] = true;
    ob_start();
    include_once(dirname(__FILE__) . "/products_page.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/\[productspage\]/",$output, $content);
    }
    else
    {
    return $content;
    }
  }

function nzshpcrt_shopping_cart($content = '')
  {
  if(preg_match("/\[shoppingcart\]/",$content))
    {
    ob_start();
    include_once(dirname(__FILE__) . "/shopping_cart.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/\[shoppingcart\]/", $output, $content);
    }
    else
    {
    return $content;
    }
  }
  

function nzshpcrt_checkout($content = '')
  {
  if(preg_match("/\[checkout\]/",$content))
    {
    ob_start();
    include_once(dirname(__FILE__) . "/checkout.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/\[checkout\]/", $output, $content);
    }
    else
    {
    return $content;
    }
  }

function nzshpcrt_transaction_results($content = '')
  {
  if(preg_match("/\[transactionresults\]/",$content))
    {
    ob_start();
    include_once(dirname(__FILE__) . "/transaction_results.php");
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/\[transactionresults\]/", $output, $content);
    }
    else
    {
    return $content;
    }
  }

function nzshpcrt_user_log($content = '')
  {
  if(preg_match("/\[userlog\]/",$content))
    {
    ob_start();
    include_once(dirname(__FILE__) . '/user-log.php');
    $output = ob_get_contents();
    ob_end_clean();
    return preg_replace("/\[userlog\]/", $output, $content);
    }
    else
    {
    return $content;
    }
  }  
    
    
function wpsc_refresh_page_urls($content)
  {
  global $wpdb;
  $wpsc_pageurl_option['product_list_url'] = '[productspage]';
  $wpsc_pageurl_option['shopping_cart_url'] = '[shoppingcart]';
  $check_chekout = $wpdb->get_var("SELECT `guid` FROM `".$wpdb->prefix."posts` WHERE `post_content` LIKE '%[checkout]%' LIMIT 1");
  if($check_chekout != null)
    {
    $wpsc_pageurl_option['checkout_url'] = '[checkout]';
    }
    else
    {
    $wpsc_pageurl_option['checkout_url'] = '[checkout]';
    }
  $wpsc_pageurl_option['transact_url'] = '[transactionresults]';
  $wpsc_pageurl_option['user_account_url'] = '[userlog]';
  $changes_made = false;
  foreach($wpsc_pageurl_option as $option_key => $page_string)
    {
    $post_id = $wpdb->get_var("SELECT `ID` FROM `".$wpdb->prefix."posts` WHERE `post_content` LIKE '%$page_string%' LIMIT 1");
    update_option($option_key, get_permalink($post_id));
    }
  return $content;
  }
  
    
require_once(dirname(__FILE__) . '/processing_functions.php');
require_once(dirname(__FILE__) . '/product_display_functions.php');

/* 
 * This plugin gets the merchants from the merchants directory and
 * needs to search the merchants directory for merchants, the code to do this starts here
 */
// $gateway_basepath =  str_replace("/wp-admin", "" , getcwd());
// $gateway_directory = $gateway_basepath."/wp-content/plugins/wp-shopping-cart/merchants/";
$gateway_directory = ABSPATH . 'wp-content/plugins/wp-shopping-cart/merchants';
$nzshpcrt_merchant_list = nzshpcrt_listdir($gateway_directory);
$num=0;
foreach($nzshpcrt_merchant_list as $nzshpcrt_merchant)
  {
  if(stristr( $nzshpcrt_merchant , '.php' ))
    {
    require(dirname(__FILE__)."/merchants/".$nzshpcrt_merchant);
    }
  $num++;
  }
/* 
 * and ends here
 */
  
  
$nzshpcrt_basepath =  str_replace("/wp-admin", "" , getcwd());
$nzshpcrt_basepath =  str_replace("\wp-admin", "" , $nzshpcrt_basepath);  // fix for windows servers, but why do people run PHP on windows?
$nzshpcrt_basepath = $nzshpcrt_basepath."/wp-content/plugins/wp-shopping-cart/";
if(file_exists($nzshpcrt_basepath.'gold_shopping_cart.php'))
  {
  require_once(dirname(__FILE__).'/gold_shopping_cart.php');
  }
require_once(dirname(__FILE__)."/currency_converter.inc.php"); 
require_once(dirname(__FILE__)."/form_display_functions.php"); 
require_once(dirname(__FILE__)."/homepage_products_functions.php"); 
require_once(dirname(__FILE__)."/transaction_result_functions.php"); 
require_once(dirname(__FILE__)."/shopping_cart_functions.php"); 

include_once(dirname(__FILE__).'/shopping_cart_widget.php');

include_once(dirname(__FILE__).'/category_widget.php');
include_once(dirname(__FILE__).'/donations_widget.php');
include_once(dirname(__FILE__).'/specials_widget.php');



if(isset($_GET['activate']) && $_GET['activate'] == 'true')
  {
  add_action('init', 'nzshpcrt_install');
  }   
   
function nzshpcrt_enable_page_filters($excerpt = '')
  {
  add_filter('the_content', 'nzshpcrt_products_page');
  add_filter('the_content', 'nzshpcrt_shopping_cart');
  add_filter('the_content', 'nzshpcrt_transaction_results');
  add_filter('the_content', 'nzshpcrt_checkout');
  add_filter('the_content', 'nszhpcrt_homepage_products');
  add_filter('the_content', 'nzshpcrt_user_log');
  add_filter('the_content', 'nszhpcrt_category_tag');
  return $excerpt;
  }

function nzshpcrt_disable_page_filters($excerpt = '')
  {
  remove_filter('the_content', 'nzshpcrt_products_page');
  remove_filter('the_content', 'nzshpcrt_shopping_cart');
  remove_filter('the_content', 'nzshpcrt_transaction_results');
  remove_filter('the_content', 'nzshpcrt_checkout');
  remove_filter('the_content', 'nszhpcrt_homepage_products');
  remove_filter('the_content', 'nzshpcrt_user_log');
  remove_filter('the_content', 'nszhpcrt_category_tag');
  return $excerpt;
  }

nzshpcrt_enable_page_filters();

add_filter('get_the_excerpt', 'nzshpcrt_disable_page_filters', -1000000);
add_filter('get_the_excerpt', 'nzshpcrt_enable_page_filters', 1000000);


add_filter('mod_rewrite_rules', 'wpsc_refresh_page_urls');
//add_action('generate_rewrite_rules', 'wpsc_refresh_page_urls');


//add_filter('wp_list_pages', 'nzshpcrt_hidepages');
 
add_action('wp_head', 'nzshpcrt_style');

add_action('admin_head', 'nzshpcrt_css');
if($_GET['page'] == "wp-shopping-cart/display-log.php")
  {
  add_action('admin_head', 'nzshpcrt_product_log_rss_feed');
  }
add_action('wp_head', 'nzshpcrt_javascript');
add_action('wp_head', 'nzshpcrt_product_list_rss_feed');

if(($_POST['submitwpcheckout'] == 'true'))
  {
  add_action('init', 'nzshpcrt_submit_checkout');
  }
add_action('init', 'nzshpcrt_submit_ajax');
add_action('init', 'nzshpcrt_download_file');
add_action('init', 'nzshpcrt_display_preview_image');

function wpsc_admin_latest_activity()
  {
  echo "<div>";
echo "<h3>".TXT_WPSC_E_COMMERCE."</h3>";
echo "<p>";

echo "<strong>".TXT_WPSC_TOTAL_THIS_MONTH."</strong><br />";
$year = date("Y");
$month = date("m");
$start_timestamp = mktime(0, 0, 0, $month, 1, $year);
$end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
echo nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
echo "</p><br /> ";
echo "<p>";
echo "<strong>".TXT_WPSC_TOTAL_INCOME."</strong><br />";
echo nzshpcrt_currency_display(admin_display_total_price(),1);
echo "</p>";
echo "</div>";
  }

add_action('activity_box_end', 'wpsc_admin_latest_activity');

//this adds all the admin pages, before the code was a mess, now it is slightly less so.
add_action('admin_menu', 'nzshpcrt_displaypages');

// pe.{
include_once("show_cats_brands.php");
include_once("share-this.php");

// if(get_option('cat_brand_loc') != 0)
//   {
//   //add_action('wp_list_pages', 'show_cats_brands');
//   }
// }.pe
add_action('plugins_loaded', 'widget_wp_shopping_cart_init');

switch(get_option('cart_location'))
  {
  case 1:
  add_action('wp_list_pages','nzshpcrt_shopping_basket');
  break;
  
  case 2:
  add_action('the_content', 'nzshpcrt_shopping_basket');
  break;
  
  case 4:
  break;
  
  case 5:
  if(function_exists('drag_and_drop_cart'))
    {
    add_action('wp_head', 'drag_and_drop_js');  
    add_action('init', 'drag_and_drop_cart_ajax');  
    add_action('wp_footer', 'drag_and_drop_cart');  
    }
  break;
  
  case 3:
  //add_action('the_content', 'nzshpcrt_shopping_basket');
  //<?php nzshpcrt_shopping_basket(); ?/>   
  break;
  
  default:
  add_action('the_content', 'nzshpcrt_shopping_basket');
  break;
  }


  
/*
 * This serializes the shopping cart variable as a backup in case the unserialized one gets butchered by various things
 */  
function serialize_shopping_cart()
  {
  @$_SESSION['nzshpcrt_serialized_cart'] = serialize($_SESSION['nzshpcrt_cart']);
  return true;
  }  
register_shutdown_function("serialize_shopping_cart");
?>
