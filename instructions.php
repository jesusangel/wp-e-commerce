<?php
global $wpdb;
?>
<div class="wrap">
<?php
//$wpsc_swfupload_log = get_option('wpsc_product_page_order');

$req = 'cmd=_notify-validate';

$replace_strings[0] = 'http://';
$replace_strings[1] = 'https://';
$replace_strings[2] = '/cgi-bin/webscr';

$paypal_url = str_replace($replace_strings, "",get_option('paypal_multiple_url'));



$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ($paypal_url, 80, $errno, $errstr, 30);

fputs ($fp, $header . $req);
while (!feof($fp)) {
	$res .= fgets ($fp, 1024);
}

echo $res;

// $regions = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}region_tax`", ARRAY_A);
// 
// foreach((array)$regions as $region) {
//   //echo "<pre>".print_r($region,true)."</pre>";
//   
//   
//   //echo "\$wpdb->query(\"INSERT INTO `{\$wpdb->prefix}region_tax` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '{$region['country_id']}', '{$region['name']}', '{$region['code']}', '{$region['tax']}')\");<br />";
// }


/*

$failure_reasons = array();
$upgrade_failed = false;
foreach((array)$wpsc_database_template as $table_name => $table_data) {
  if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'")) {
    //if the table does not exixt, create the table
    
    $constructed_sql_parts = array();
    $constructed_sql = "CREATE TABLE `{$table_name}` (\n";
    
    // loop through the columns
    foreach((array)$table_data['columns'] as $column => $properties) {
      $constructed_sql_parts[] = "`$column` $properties";
    }
    // then through the indexes
    foreach((array)$table_data['indexes'] as $properties) {
      $constructed_sql_parts[] = "$properties";
    }
    $constructed_sql .= implode(",\n", $constructed_sql_parts);
    $constructed_sql .= "\n) ENGINE=MyISAM;";
  
    if(!$wpdb->query($constructed_sql)) {
      $upgrade_failed = true;
      $failure_reasons[] = $wpdb->last_error;
    }
    //echo "<pre>$constructed_sql</pre>";
  } else {
    //check to see if the table needs updating
    $existing_table_columns = array();
    $existing_table_column_data = $wpdb->get_results("SHOW COLUMNS FROM `$table_name`", ARRAY_A);
    foreach((array)$existing_table_column_data as $existing_table_column) {
      $existing_table_columns[] = $existing_table_column['Field'];
    }
    $supplied_table_columns = array_keys($table_data['columns']);
    
    // compare the supplied and existing columns to find the differences
    $missing_or_extra_table_columns = array_diff($supplied_table_columns, $existing_table_columns);
        
    if(count($missing_or_extra_table_columns) > 0) {
      foreach((array)$missing_or_extra_table_columns as $missing_or_extra_table_column) {
        if(isset($table_data['columns'][$missing_or_extra_table_column])) {
          //table column is missing, add it
          $previous_column = $supplied_table_columns[array_search($missing_or_extra_table_column, $supplied_table_columns)-1];
          if($previous_column != '') {
            $previous_column = "AFTER `$previous_column`";
          }
          $constructed_sql = "ALTER TABLE `$table_name` ADD `$missing_or_extra_table_column` ".$table_data['columns'][$missing_or_extra_table_column]." $previous_column;";
          if(!$wpdb->query($constructed_sql)) {
            $upgrade_failed = true;
            $failure_reasons[] = $wpdb->last_error;
          }
        }
      }
    }
    
    // get the list of existing indexes
    $existing_table_index_data = $wpdb->get_results("SHOW INDEX FROM `$table_name`", ARRAY_A);
    $existing_table_indexes = array();
    foreach($existing_table_index_data as $existing_table_index) {
      $existing_table_indexes[] = $existing_table_index['Key_name'];
    }
    
    $existing_table_indexes = array_unique($existing_table_indexes);
    $supplied_table_indexes = array_keys($table_data['indexes']);
    
    // compare the supplied and existing indxes to find the differences
    $missing_or_extra_table_indexes = array_diff($supplied_table_indexes, $existing_table_indexes);
    
    
     if(count($missing_or_extra_table_indexes) > 0) {
      foreach($missing_or_extra_table_indexes as $missing_or_extra_table_index) {
        if(isset($table_data['indexes'][$missing_or_extra_table_index])) {
          $constructed_sql = "ALTER TABLE `$table_name` ADD ".$table_data['indexes'][$missing_or_extra_table_index].";";
          if(!$wpdb->query($constructed_sql)) {
            $upgrade_failed = true;
            $failure_reasons[] = $wpdb->last_error;
          }
        }
      }
    }
  }
}*/






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