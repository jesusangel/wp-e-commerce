<?php
/**
 * WP eCommerce Database template
 *
 * This is the WPSC database template it is a multidimensional associative array used to create and update the database tables.
 * @package wp-e-commerce
 * @subpackage wpsc-updating-code 
 */



$table_name = "{$wpdb->prefix}also_bought_product";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['selected_product'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['associated_product'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['quantity'] = "int(10) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}cart_contents";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['prodid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['purchaseid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['price'] = "decimal(11,2) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['pnp'] = "decimal(11,2) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['tax_charged'] = "decimal(11,2) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['gst'] = "decimal(11,2) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['quantity'] = "int(10) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['donation'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['no_shipping'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['files'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['meta'] = "longtext NULL ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY ( `id` )";


$table_name = "{$wpdb->prefix}cart_item_extras";
$wpsc_database_template[$table_name]['columns']['id'] = "int(11) NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['cart_id'] = "int(11) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['extra_id'] = "int(11) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY ( `id` )";


$table_name = "{$wpdb->prefix}cart_item_variations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['cart_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['value_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY ( `id` )";


$table_name = "{$wpdb->prefix}collect_data_forms";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['type'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['mandatory'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['display_log'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['default'] = "varchar(128) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['order'] = "int(10) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['order'] = " KEY `order` ( `order` )";


$table_name = "{$wpdb->prefix}currency_list";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['country'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['isocode'] = "char(2) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['currency'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['symbol'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['symbol_html'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['code'] = "char(3) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['has_regions'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['tax'] = "varchar(8) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['continent'] = "varchar(20) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['visible'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}download_status";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['fileid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['purchid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['cartid'] = "bigint(20) unsigned NULL";
$wpsc_database_template[$table_name]['columns']['uniqueid'] = "varchar(64) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['downloads'] = "int(11) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['ip_number'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['datetime'] = "datetime NOT NULL";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['uniqueid'] = "UNIQUE KEY `uniqueid` ( `uniqueid` )";


$table_name = "{$wpdb->prefix}item_category_associations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['category_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = "UNIQUE KEY `product_id` (`product_id`,`category_id`)";

///# Brands are no longer used, they are now product categorisations
// $table_name = "{$wpdb->prefix}product_brands";
// $wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) NOT NULL auto_increment";
// $wpsc_database_template[$table_name]['columns']['name'] = "text NOT NULL ";
// $wpsc_database_template[$table_name]['columns']['description'] = "text NOT NULL ";
// $wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
// $wpsc_database_template[$table_name]['columns']['order'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
// $wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_categories";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['group_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['name'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['nice-name'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['description'] = "text NOT NULL  ";
$wpsc_database_template[$table_name]['columns']['image'] = "text NULL ";
$wpsc_database_template[$table_name]['columns']['fee'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['category_parent'] = "bigint(20) unsigned NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['order'] = "bigint(20) unsigned NULL ";
$wpsc_database_template[$table_name]['columns']['display_type'] = "varchar(10) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['image_width'] = "varchar(32) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['image_height'] = "varchar(32) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['group_id'] = " KEY `group_id` ( `group_id` )";
$wpsc_database_template[$table_name]['indexes']['nice-name'] = " KEY `nice-name` ( `nice-name` )";


$table_name = "{$wpdb->prefix}product_files";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['filename'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['mimetype'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['idhash'] = "varchar(45) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['preview'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['preview_mimetype'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['date'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_images";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['image'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['width'] = "mediumint(8) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['height'] = "mediumint(8) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['image_order'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['meta'] = "longtext NULL";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";


$table_name = "{$wpdb->prefix}product_list";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['description'] = "longtext NOT NULL";
$wpsc_database_template[$table_name]['columns']['additional_description'] = "longtext NOT NULL ";
$wpsc_database_template[$table_name]['columns']['price'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['weight'] = "float NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['weight_unit'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['pnp'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['international_pnp'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['file'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['image'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['category'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['brand'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['quantity_limited'] = "varchar(1) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['quantity'] = "int(10) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['special'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['special_price'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['display_frontpage'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['notax'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['publish'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['donation'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['no_shipping'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['thumbnail_image'] = "text NULL ";
$wpsc_database_template[$table_name]['columns']['thumbnail_state'] = "int(11) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_order";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['category_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['order'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['category_id'] = "UNIQUE KEY `category_id` (`category_id`,`product_id`)";
$wpsc_database_template[$table_name]['indexes']['order'] = " KEY `order` ( `order` )";


$table_name = "{$wpdb->prefix}product_rating";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['ipnum'] = "varchar(30) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['productid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['rated'] = "tinyint(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['time'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_variations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['variation_association'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['variation_association'] = " KEY `variation_association` ( `variation_association` )";


$table_name = "{$wpdb->prefix}purchase_logs";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['totalprice'] = "decimal(11,2) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['statusno'] = "smallint(6) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['sessionid'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['transactid'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['authcode'] = "varchar(255) NOT NULL DEFAULT '' ";
//$wpsc_database_template[$table_name]['columns']['downloadid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['processed'] = "bigint(20) unsigned NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['user_ID'] = "bigint(20) unsigned NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['date'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['gateway'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['billing_country'] = "char(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['shipping_country'] = "char(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['base_shipping'] = "decimal(11,2) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['email_sent'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['stock_adjusted'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['discount_value'] = "decimal(11,2) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['discount_data'] = "text NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['track_id'] = "varchar(50) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['shipping_region'] = "char(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['find_us'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['engravetext'] = "varchar(255) NULL DEFAULT '' ";
//$wpsc_database_template[$table_name]['columns']['closest_store'] = "varchar(255) NULL DEFAULT '' ";
//$wpsc_database_template[$table_name]['columns']['google_order_number'] = "varchar(20) NOT NULL DEFAULT '' ";
//$wpsc_database_template[$table_name]['columns']['google_user_marketing_preference'] = "varchar(10) NOT NULL DEFAULT '' ";
//$wpsc_database_template[$table_name]['columns']['google_status'] = "longtext NOT NULL ";
$wpsc_database_template[$table_name]['columns']['shipping_method'] = "VARCHAR(64) NULL ";
$wpsc_database_template[$table_name]['columns']['shipping_option'] = "VARCHAR(128) NULL ";
$wpsc_database_template[$table_name]['columns']['affiliate_id'] = "VARCHAR(32) NULL ";
$wpsc_database_template[$table_name]['columns']['plugin_version'] = "VARCHAR(32) NULL ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['sessionid'] = "UNIQUE KEY `sessionid` ( `sessionid` )";
$wpsc_database_template[$table_name]['indexes']['gateway'] = " KEY `gateway` ( `gateway` )";


$table_name = "{$wpdb->prefix}purchase_statuses";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['colour'] = "varchar(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}region_tax";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['country_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['code'] = "char(2) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['tax'] = "float NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['country_id'] = " KEY `country_id` ( `country_id` )";


$table_name = "{$wpdb->prefix}submited_form_data";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['log_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['form_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['value'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['log_id'] = " KEY `log_id` ( `log_id`, `form_id` )";


$table_name = "{$wpdb->prefix}variation_associations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['type'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['associated_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['associated_id'] = " KEY `associated_id` ( `associated_id` )";
$wpsc_database_template[$table_name]['indexes']['variation_id'] = " KEY `variation_id` ( `variation_id` )";


$table_name = "{$wpdb->prefix}variation_priceandstock";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
//  $wpsc_database_template[$table_name]['columns']['variation_id_1'] = "bigint(20) unsigned NOT NULL DEFAULT '0' "; # Old, no longer used
//  $wpsc_database_template[$table_name]['columns']['variation_id_2'] = "bigint(20) unsigned NOT NULL DEFAULT '0' "; # Old, no longer used
$wpsc_database_template[$table_name]['columns']['stock'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['price'] = "varchar(32) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['weight'] = "varchar(64) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['weight_unit'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['visibility'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['file'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";
//  $wpsc_database_template[$table_name]['indexes']['variation_id_1'] = " KEY `variation_id_1` ( `variation_id_1`, `variation_id_2` )"; # Old, no longer used


$table_name = "{$wpdb->prefix}variation_values";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['variation_id'] = " KEY `variation_id` ( `variation_id` )";


$table_name = "{$wpdb->prefix}variation_values_associations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['value_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
//$wpsc_database_template[$table_name]['columns']['quantity'] = "int(11) NOT NULL DEFAULT '0' "; # Old, no longer used
//$wpsc_database_template[$table_name]['columns']['price'] = "varchar(32) NOT NULL DEFAULT '0' "; # Old, no longer used
$wpsc_database_template[$table_name]['columns']['visible'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id`, `value_id`, `variation_id` )";


$table_name = "{$wpdb->prefix}wpsc_coupon_codes";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['coupon_code'] = "varchar(255) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['value'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['is-percentage'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['use-once'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['is-used'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['active'] = "char(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['every_product'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['start'] = "datetime NOT NULL";
$wpsc_database_template[$table_name]['columns']['expiry'] = "datetime NOT NULL";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['coupon_code'] = " KEY `coupon_code` ( `coupon_code` )";
$wpsc_database_template[$table_name]['indexes']['active'] = " KEY `active` ( `active` )";
$wpsc_database_template[$table_name]['indexes']['start'] = " KEY `start` ( `start` )";
$wpsc_database_template[$table_name]['indexes']['expiry'] = " KEY `expiry` ( `expiry` )";


$table_name = "{$wpdb->prefix}wpsc_logged_subscriptions";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['cart_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['user_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['length'] = "varchar(64) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['start_time'] = "varchar(64) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['cart_id'] = " KEY `cart_id` ( `cart_id` )";
$wpsc_database_template[$table_name]['indexes']['user_id'] = " KEY `user_id` ( `user_id` )";
$wpsc_database_template[$table_name]['indexes']['start_time'] = " KEY `start_time` ( `start_time` )";


$table_name = "{$wpdb->prefix}wpsc_productmeta";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['meta_key'] = "varchar(255) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['meta_value'] = "longtext NULL ";
$wpsc_database_template[$table_name]['columns']['custom'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";
$wpsc_database_template[$table_name]['indexes']['meta_key'] = " KEY `meta_key` ( `meta_key` )";
$wpsc_database_template[$table_name]['indexes']['custom'] = " KEY `custom` ( `custom` )";


$table_name = "{$wpdb->prefix}wpsc_categorisation_groups";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['description'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['default'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['group_name'] = " KEY `group_name` ( `name` )";


$table_name = "{$wpdb->prefix}wpsc_variation_combinations";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['priceandstock_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['value_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['all_variation_ids'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";
$wpsc_database_template[$table_name]['indexes']['priceandstock_id'] = " KEY `priceandstock_id` ( `priceandstock_id` )";
$wpsc_database_template[$table_name]['indexes']['value_id'] = " KEY `value_id` ( `value_id` )";
$wpsc_database_template[$table_name]['indexes']['variation_id'] = " KEY `variation_id` ( `variation_id` )";
$wpsc_database_template[$table_name]['indexes']['all_variation_ids'] = " KEY `all_variation_ids` ( `all_variation_ids` )";



$table_name = "{$wpdb->prefix}wpsc_claimed_stock";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) UNSIGNED NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_stock_id'] = "bigint(20) UNSIGNED NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['stock_claimed'] = "FLOAT NOT NULL ";
$wpsc_database_template[$table_name]['columns']['last_activity'] = "DATETIME NOT NULL ";
$wpsc_database_template[$table_name]['columns']['cart_id'] = "VARCHAR( 255 ) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['cart_submitted'] = "VARCHAR( 1 ) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['unique_key'] = "UNIQUE KEY `unique_key` ( `product_id`,`variation_stock_id`,`cart_id`)";
$wpsc_database_template[$table_name]['indexes']['last_activity'] = "KEY `last_activity` ( `last_activity` )";
$wpsc_database_template[$table_name]['indexes']['cart_submitted'] = "KEY `cart_submitted` ( `cart_submitted` )";
//) ENGINE = memory;

?>