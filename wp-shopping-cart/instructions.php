<?php
global $wpdb;
?>
<div class="wrap">
<?php
/*
$table_name = "{$wpdb->prefix}also_bought_product";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['selected_product'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['associated_product'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['quantity'] = "int(10) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}cart_contents";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL auto_increment";
$wpsc_database_template[$table_name]['columns']['prodid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['purchaseid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['price'] = "varchar(128) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['pnp'] = "varchar(128) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['gst'] = "varchar(128) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['quantity'] = "int(10) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['donation'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['no_shipping'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['files'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}cart_item_extras";
$wpsc_database_template[$table_name]['columns']['id'] = "int(11) NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['cart_id'] = "int(11) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['extra_id'] = "int(11) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}cart_item_variations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['cart_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['value_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}collect_data_forms";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
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
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['country'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['isocode'] = "char(2) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['currency'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['symbol'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['symbol_html'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['code'] = "char(3) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['has_regions'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['tax'] = "varchar(8) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['continent'] = "varchar(20) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}download_status";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['fileid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['purchid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['uniqueid'] = "varchar(64) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['downloads'] = "int(11) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['ip_number'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['datetime'] = "datetime NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['uniqueid'] = "UNIQUE KEY `uniqueid` ( `uniqueid` )";


$table_name = "{$wpdb->prefix}item_category_associations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['category_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = "UNIQUE KEY `product_id` (`product_id`,`category_id`)";


$table_name = "{$wpdb->prefix}product_brands";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['description'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['order'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_categories";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['group_id'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['name'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['nice-name'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['description'] = "text NOT NULL  ";
$wpsc_database_template[$table_name]['columns']['image'] = "text NOT NULL  ";
$wpsc_database_template[$table_name]['columns']['fee'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['category_parent'] = "bigint(20) unsigned NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['order'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['display_type'] = "varchar(10) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['image_width'] = "varchar(32) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['image_height'] = "varchar(32) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['group_id'] = " KEY `group_id` ( `group_id` )";
$wpsc_database_template[$table_name]['indexes']['nice-name'] = " KEY `nice-name` ( `nice-name` )";


$table_name = "{$wpdb->prefix}product_files";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['filename'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['mimetype'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['idhash'] = "varchar(45) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['preview'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['preview_mimetype'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['date'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_images";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['image'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['width'] = "mediumint(8) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['height'] = "mediumint(8) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";


$table_name = "{$wpdb->prefix}product_list";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['description'] = "longtext NOT NULL";
$wpsc_database_template[$table_name]['columns']['additional_description'] = "longtext NOT NULL ";
$wpsc_database_template[$table_name]['columns']['price'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['weight'] = "int(11) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['weight_unit'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['pnp'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['international_pnp'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['file'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['image'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['category'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['brand'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['quantity_limited'] = "varchar(1) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['quantity'] = "int(10) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['special'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['special_price'] = "varchar(20) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['display_frontpage'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['notax'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['donation'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['no_shipping'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['thumbnail_image'] = "text NULL ";
$wpsc_database_template[$table_name]['columns']['thumbnail_state'] = "int(11) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_order";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['category_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['order'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['category_id'] = "UNIQUE KEY `category_id` (`category_id`,`product_id`)";
$wpsc_database_template[$table_name]['indexes']['order'] = " KEY `order` ( `order` )";


$table_name = "{$wpdb->prefix}product_rating";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['ipnum'] = "varchar(30) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['productid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['rated'] = "tinyint(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['time'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}product_variations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['variation_association'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['variation_association'] = " KEY `variation_association` ( `variation_association` )";


$table_name = "{$wpdb->prefix}purchase_logs";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['totalprice'] = "varchar(128) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['statusno'] = "smallint(6) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['sessionid'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['transactid'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['authcode'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['downloadid'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['processed'] = "bigint(20) unsigned NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['user_ID'] = "bigint(20) unsigned NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['date'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['gateway'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['billing_country'] = "char(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['shipping_country'] = "char(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['base_shipping'] = "varchar(128) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['email_sent'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['discount_value'] = "varchar(32) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['discount_data'] = "text NOT NULL";
$wpsc_database_template[$table_name]['columns']['track_id'] = "varchar(50) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['shipping_region'] = "char(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['find_us'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['engravetext'] = "varchar(255) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['closest_store'] = "varchar(255) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['google_order_number'] = "varchar(20) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['google_user_marketing_preference'] = "varchar(10) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['google_status'] = "longtext NOT NULL ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['sessionid'] = "UNIQUE KEY `sessionid` ( `sessionid` )";
$wpsc_database_template[$table_name]['indexes']['gateway'] = " KEY `gateway` ( `gateway` )";


$table_name = "{$wpdb->prefix}purchase_statuses";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['colour'] = "varchar(6) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";


$table_name = "{$wpdb->prefix}region_tax";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['country_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['code'] = "char(2) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['tax'] = "float NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['country_id'] = " KEY `country_id` ( `country_id` )";


$table_name = "{$wpdb->prefix}submited_form_data";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['log_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['form_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['value'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['log_id'] = " KEY `log_id` ( `log_id`, `form_id` )";


$table_name = "{$wpdb->prefix}variation_associations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['type'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['associated_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['associated_id'] = " KEY `associated_id` ( `associated_id` )";
$wpsc_database_template[$table_name]['indexes']['variation_id'] = " KEY `variation_id` ( `variation_id` )";


$table_name = "{$wpdb->prefix}variation_priceandstock";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id_1'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id_2'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['stock'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['price'] = "varchar(32) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['weight'] = "varchar(64) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['visibility'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['file'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";
$wpsc_database_template[$table_name]['indexes']['variation_id_1'] = " KEY `variation_id_1` ( `variation_id_1`, `variation_id_2` )";


$table_name = "{$wpdb->prefix}variation_values";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(128) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['variation_id'] = " KEY `variation_id` ( `variation_id` )";


$table_name = "{$wpdb->prefix}variation_values_associations";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['value_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['quantity'] = "int(11) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['price'] = "varchar(32) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['visible'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id`, `value_id`, `variation_id` )";


$table_name = "{$wpdb->prefix}wpsc_coupon_codes";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['coupon_code'] = "varchar(255) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['value'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['is-percentage'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['use-once'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['is-used'] = "char(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['active'] = "char(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['every_product'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['start'] = "datetime NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['expiry'] = "datetime NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['coupon_code'] = " KEY `coupon_code` ( `coupon_code` )";
$wpsc_database_template[$table_name]['indexes']['active'] = " KEY `active` ( `active` )";
$wpsc_database_template[$table_name]['indexes']['start'] = " KEY `start` ( `start` )";
$wpsc_database_template[$table_name]['indexes']['expiry'] = " KEY `expiry` ( `expiry` )";


$table_name = "{$wpdb->prefix}wpsc_logged_subscriptions";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
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
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['columns']['meta_key'] = "varchar(255) NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['meta_value'] = "longtext NULL ";
$wpsc_database_template[$table_name]['columns']['custom'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";
$wpsc_database_template[$table_name]['indexes']['meta_key'] = " KEY `meta_key` ( `meta_key` )";
$wpsc_database_template[$table_name]['indexes']['custom'] = " KEY `custom` ( `custom` )";


$table_name = "{$wpdb->prefix}wpsc_categorisation_groups";
$wpsc_database_template[$table_name]['columns']['id'] = "bigint(20) unsigned NOT NULL  auto_increment";
$wpsc_database_template[$table_name]['columns']['name'] = "varchar(255) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['description'] = "text NOT NULL ";
$wpsc_database_template[$table_name]['columns']['active'] = "varchar(1) NOT NULL DEFAULT '1' ";
$wpsc_database_template[$table_name]['columns']['default'] = "varchar(1) NOT NULL DEFAULT '0' ";
$wpsc_database_template[$table_name]['indexes']['PRIMARY'] = "PRIMARY KEY  ( `id` )";
$wpsc_database_template[$table_name]['indexes']['group_name'] = " KEY `group_name` ( `name` )";


$table_name = "{$wpdb->prefix}wpsc_variation_combinations";
$wpsc_database_template[$table_name]['columns']['product_id'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['priceandstock_id'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['value_id'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['variation_id'] = "bigint(20) unsigned NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['columns']['all_variation_ids'] = "varchar(64) NOT NULL DEFAULT '' ";
$wpsc_database_template[$table_name]['indexes']['product_id'] = " KEY `product_id` ( `product_id` )";
$wpsc_database_template[$table_name]['indexes']['priceandstock_id'] = " KEY `priceandstock_id` ( `priceandstock_id` )";
$wpsc_database_template[$table_name]['indexes']['value_id'] = " KEY `value_id` ( `value_id` )";
$wpsc_database_template[$table_name]['indexes']['variation_id'] = " KEY `variation_id` ( `variation_id` )";
$wpsc_database_template[$table_name]['indexes']['all_variation_ids'] = " KEY `all_variation_ids` ( `all_variation_ids` )";





foreach($wpsc_database_template as $table_name => $table_data) { 
  
  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    //if the table does not exixt, create the table
  } else {
    //check to see if the table needs updating
    $existing_table_columns = array();
    $existing_table_column_data = $wpdb->get_results("SHOW COLUMNS FROM `$table_name`", ARRAY_A);
    foreach($existing_table_column_data as $existing_table_column) {
      $existing_table_columns[] = $existing_table_column['Field'];
    }
    $supplied_table_columns = array_keys($table_data['columns']);
    // compare the supplied and existing columns to find the differenceirs
    $missing_or_extra_table_columns = array_diff($supplied_table_columns, $existing_table_columns);
    
    
    if(count($missing_or_extra_table_columns) > 0) {
      foreach($missing_or_extra_table_columns as $missing_or_extra_table_column) {
        if(isset($table_data['columns'][$missing_or_extra_table_column])) {
          //table column is missing, add it
          $previous_column = $supplied_table_columns[array_search($missing_or_extra_table_column, $supplied_table_columns)-1];
          if($previous_column != '') {
            $previous_column = "AFTER `$previous_column`";
          }
          $constructed_sql = "ALTER TABLE `$table_name` ADD `$missing_or_extra_table_column` ".$table_data['columns'][$missing_or_extra_table_column]." $previous_column;";
          echo "<br />";
          $wpdb->query($constructed_sql);
        } else {
          //table column is extra, report it
        }
      }
    }
    
    //  ALTER TABLE `wp_postmeta` ADD INDEX ( `meta_value` )  
    $existing_table_index_data = $wpdb->get_results("SHOW INDEX FROM `$table_name`", ARRAY_A);
    $existing_table_indexes = array();
    foreach($existing_table_index_data as $existing_table_index) {
      $existing_table_indexes[] = $existing_table_index['Key_name'];
    }
    $existing_table_indexes = array_unique($existing_table_indexes);
    echo "<pre>".print_r($existing_table_indexes,true)."</pre>";
    
  }
}






/*
  $num = 0;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix."also_bought_product";
$wpsc_tables[$num]['table_sql'] = "CREATE TABLE `".$wpdb->prefix."also_bought_product` (
`id` bigint(20) unsigned NOT NULL auto_increment,
`selected_product` bigint(20) unsigned NOT NULL default '0',
`associated_product` bigint(20) unsigned NOT NULL default '0',
`quantity` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
";


// Table structure for table `".$wpdb->prefix."cart_contents`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'cart_contents';
$wpsc_tables[$num]['table_sql'] = "CREATE TABLE `".$wpdb->prefix."cart_contents` (
`id` bigint(20) unsigned NOT NULL auto_increment,
`prodid` bigint(20) unsigned NOT NULL default '0',
`purchaseid` bigint(20) unsigned NOT NULL default '0',
`price` varchar(128) NOT NULL default '0',
`pnp` varchar(128) NOT NULL default '0',
`gst` varchar(128) NOT NULL default '0',
`quantity` int(10) unsigned NOT NULL default '0',
`donation` varchar(1) NOT NULL default '0',
`no_shipping` varchar(1) NOT NULL default '0',
`files` TEXT NOT NULL default '',
PRIMARY KEY  (`id`)
) TYPE=MyISAM ;
";


// Table structure for table `".$wpdb->prefix."cart_item_extras`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'cart_item_extras';
$wpsc_tables[$num]['table_sql'] = "CREATE TABLE `".$wpdb->prefix."cart_item_extras` (
`id` int(11) NOT NULL auto_increment,
`cart_id` int(11) NOT NULL,
`extra_id` int(11) NOT NULL,
PRIMARY KEY  (`id`)
) TYPE=MyISAM;
";

// Table structure for table `".$wpdb->prefix."cart_item_variations`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'cart_item_variations';
$wpsc_tables[$num]['table_sql'] = "CREATE TABLE `".$wpdb->prefix."cart_item_variations` (
`id` bigint(20) unsigned NOT NULL auto_increment,
`cart_id` bigint(20) unsigned NOT NULL default '0',
`variation_id` bigint(20) unsigned NOT NULL default '0',
`value_id` bigint(20) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`)
) TYPE=MyISAM;
";


// Table structure for table `".$wpdb->prefix."collect_data_forms`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'collect_data_forms';
$wpsc_tables[$num]['table_sql'] = "CREATE TABLE `".$wpdb->prefix."collect_data_forms` (
`id` bigint(20) unsigned NOT NULL auto_increment,
`name` varchar(255) NOT NULL default '',
`type` varchar(64) NOT NULL default '',
`mandatory` varchar(1) NOT NULL default '0',
`display_log` char(1) NOT NULL default '0',
`default` varchar(128) NOT NULL default '0',
`active` varchar(1) NOT NULL default '1',
`order` int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (`id`),
KEY `order` (`order`)
) TYPE=MyISAM ;
";


// Table structure for table `".$wpdb->prefix."currency_list`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'currency_list';
$wpsc_tables[$num]['table_sql'] = "CREATE TABLE `".$wpdb->prefix."currency_list` (
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
) TYPE=MyISAM ;
";


// Table structure for table `".$wpdb->prefix."download_status`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'download_status';


// Table structure for table `".$wpdb->prefix."item_category_associations`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'item_category_associations';

// Table structure for table `".$wpdb->prefix."product_brands`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_brands';

// Table structure for table `".$wpdb->prefix."product_categories`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_categories';

// Table structure for table `".$wpdb->prefix."product_extra`

//$num++;
//$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_extra';
// Table structure for table `".$wpdb->prefix."product_files`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_files';
// Table structure for table `".$wpdb->prefix."product_images`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_images';


// Table structure for table `".$wpdb->prefix."product_list`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_list';

// Table structure for table `".$wpdb->prefix."product_order`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_order';


// Table structure for table `".$wpdb->prefix."product_rating`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_rating';
// Table structure for table `".$wpdb->prefix."product_variations`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'product_variations';

// Table structure for table `".$wpdb->prefix."purchase_logs`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'purchase_logs';

// Table structure for table `".$wpdb->prefix."purchase_statuses`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'purchase_statuses';
// Table structure for table `".$wpdb->prefix."region_tax`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'region_tax';

// Table structure for table `".$wpdb->prefix."submited_form_data`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'submited_form_data';


// Table structure for table `".$wpdb->prefix."variation_associations`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'variation_associations';

// Table structure for table `".$wpdb->prefix."variation_priceandstock`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'variation_priceandstock';

// Table structure for table `".$wpdb->prefix."variation_values`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'variation_values';


// Table structure for table `".$wpdb->prefix."variation_values_associations`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'variation_values_associations';

// Table structure for table `".$wpdb->prefix."wpsc_coupon_codes`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'wpsc_coupon_codes';


// Table structure for table `".$wpdb->prefix."wpsc_logged_subscriptions`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'wpsc_logged_subscriptions';


// Table structure for table `".$wpdb->prefix."wpsc_productmeta`

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'wpsc_productmeta';

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'wpsc_categorisation_groups';

$num++;
$wpsc_tables[$num]['table_name'] = $wpdb->prefix.'wpsc_variation_combinations';
  
  
  echo "<pre>";
  foreach($wpsc_tables as $wpsc_table) {
    $table_name = $wpsc_table['table_name'];
    $table_cols = $wpdb->get_results("SHOW FULL COLUMNS FROM $table_name", ARRAY_A);
    $table_indexes = $wpdb->get_results("SHOW INDEX FROM $table_name", ARRAY_A);
    
    //echo "<h4>$table_name</h4>";
    //echo "<pre>".print_r($table_cols,true)."</pre>";
    //echo "<pre>".print_r($table_indexes,true)."</pre>";
    echo '$table_name = "'.str_replace($wpdb->prefix, "{\$wpdb->prefix}", $table_name)."\";\n";
    foreach($table_cols as $column) {
      
      if($column['Null'] == "YES") {
        $null_status = "NULL";
      } else {
        $null_status = "NOT NULL";
      }
      
      if($column['Extra'] != '') {
        $default = '';
      } else {
        $default = "DEFAULT '$column[Default]'";
      }
      //echo "`{$column['Field']}` {$column['Type']} {$null_status} $default {$column['Extra']} <br />";
      
      echo "\$wpsc_database_template[\$table_name]['columns']['{$column['Field']}'] = \"{$column['Type']} {$null_status} $default {$column['Extra']}\";\n";
      
      
    }
    
    
    
    foreach($table_indexes as $index) {
    
      if($index['Key_name'] == "PRIMARY") {
        $key_prefix = "PRIMARY";
        $key_name = '';
        $array_key_name = "PRIMARY";
      } else {
        if($index['Non_unique'] == 0) {
          $key_prefix = "UNIQUE";
        } else {
          $key_prefix = "";
        }
        $key_name = "`".$index['Key_name']."`";
        $array_key_name = $index['Key_name'];
      }
      
      //echo "{$key_prefix} KEY {$key_name} ( `{$index['Column_name']}` ) <br />";
      echo "\$wpsc_database_template[\$table_name]['indexes']['{$array_key_name}'] = \"{$key_prefix} KEY {$key_name} ( `{$index['Column_name']}` )\";\n";
      
    }
    echo "\n\r";
  }
  echo "</pre>";
  // */
?>
</div>