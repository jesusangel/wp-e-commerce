<?php
 /*
  * more code to update from old versions, messy code too
  */

  $coldata  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_categories` LIKE 'image'",ARRAY_A);
  if($coldata == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_categories` ADD `image` TEXT NOT NULL AFTER `description`");
    }
    
  $coldata2  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'quantity_limited'",ARRAY_A);
  if($coldata2 == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `quantity_limited` VARCHAR( 1 ) NOT NULL AFTER `category`");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `quantity` INT UNSIGNED NOT NULL AFTER `quantity_limited`");
    }
    
  $coldata3  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'file'",ARRAY_A);
  if($coldata3 == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `file` BIGINT UNSIGNED NOT NULL  AFTER `category`");
    }
    
  $coldata4  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'special_price'",ARRAY_A);
  if($coldata4 == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `special_price` VARCHAR( 20 ) NOT NULL AFTER `special`");
    }
    
  $coldata5  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."purchase_logs` LIKE 'processed'",ARRAY_A);
  if($coldata5[0]['Type'] == "char(1)")
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."purchase_logs` CHANGE `processed` `processed` BIGINT UNSIGNED NOT NULL DEFAULT '1'");
    }
    
  $coldata6  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."cart_contents` LIKE 'price'",ARRAY_A);
  if($coldata6 == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."cart_contents` ADD `price` VARCHAR( 128 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."cart_contents` ADD `pnp` VARCHAR( 128 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."cart_contents` ADD `gst` VARCHAR( 128 ) NOT NULL");
    }
    
  $coldata7  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'brand'",ARRAY_A);
  if($coldata7 == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `brand` BIGINT UNSIGNED NOT NULL  AFTER `category`");
    }

  $coldata8  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'additional_description'",ARRAY_A);
  if($coldata8 == null)
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` ADD `additional_description` LONGTEXT NOT NULL AFTER `description`");
    }
    
  $coldata9  = $wpdb->get_results("SHOW FULL COLUMNS FROM `".$wpdb->prefix."product_list` LIKE 'pnp'",ARRAY_A);
  if($coldata9[0]['Type'] != "varchar(20)")
    {
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."product_list` CHANGE `pnp` `pnp` VARCHAR( 20 ) DEFAULT '0' NOT NULL");
    }
?>