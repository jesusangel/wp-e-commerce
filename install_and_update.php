<?php
function wpsc_auto_update() {
  global $wpdb;

  wpsc_create_or_update_tables();
  
  include_once('updates/updating_tasks.php');
  
  wpsc_create_upload_directories();

  wpsc_product_files_htaccess();  
  wpsc_check_and_copy_files();
  
  if((get_option('wpsc_version') < WPSC_VERSION) || (get_option('wpsc_version') == WPSC_VERSION) && (get_option('wpsc_minor_version') < WPSC_MINOR_VERSION)) {
    update_option('wpsc_version', WPSC_VERSION);
    update_option('wpsc_minor_version', WPSC_MINOR_VERSION);
	}
}

function nzshpcrt_install()
   {
   global $wpdb, $user_level, $wp_rewrite, $wp_version;
   $table_name = $wpdb->prefix . "product_list";
   //$log_table_name = $wpdb->prefix . "sms_log";
   if($wp_version < 2.1) {
     get_currentuserinfo();
     if($user_level < 8) {
       return;
    }
  }
  $first_install = false;
  $result = mysql_list_tables(DB_NAME);
  $tables = array();
  while($row = mysql_fetch_row($result)) {
    $tables[] = $row[0];
	}
  if(!in_array($table_name, $tables)) {
    $first_install = true;
	}    

  if(get_option('wpsc_version') == null) {
    add_option('wpsc_version', WPSC_VERSION, 'wpsc_version', 'yes');
	}





  // run the create or update code here.
  wpsc_create_or_update_tables();
  




 
  wpsc_create_upload_directories();
  
 
	require dirname(__FILE__) . "/currency_list.php";
	
	/*
    if(get_option('wpsc_version') <= 3.5) {
      include_once('updates/update-to-3.5.0.php');
    }
    include_once('updates/update-to-3.5.2.php');
    
    include_once('updates/update-to-3.5.2.php');
    include_once('updates/update-to-3.6.0.php');
    include_once('updates/update-to-3.6.4.php');
    
    */
  /* all code to add new database tables and columns must be above here */  
  if((get_option('wpsc_version') < WPSC_VERSION) || (get_option('wpsc_version') == WPSC_VERSION) && (get_option('wpsc_minor_version') < WPSC_MINOR_VERSION)) {
    update_option('wpsc_version', WPSC_VERSION);
    update_option('wpsc_minor_version', WPSC_MINOR_VERSION);
	}

  $currency_data  = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_CURRENCY_LIST."`");
  if($currency_data == 0) {
    $currency_array = explode("\n",$currency_sql);
    foreach($currency_array as $currency_row) {
      $wpdb->query($currency_row);
		}
	}

  $add_initial_category = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."`;",ARRAY_A);
  if($add_initial_category[0]['count'] == 0) {
		$wpdb->query("INSERT INTO `".WPSC_TABLE_CATEGORISATION_GROUPS."` (`id`, `name`, `description`, `active`, `default`) VALUES (1, 'Categories', 'Product Categories', '1', '1')");
		$wpdb->query("INSERT INTO `".WPSC_TABLE_CATEGORISATION_GROUPS."` (`id`, `name`, `description`, `active`, `default`) VALUES (2, 'Brands', 'Product Brands', '1', '0')");	
		
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_CATEGORIES."` (`group_id`, `name` , `description`, `active`) VALUES ('1', '".TXT_WPSC_EXAMPLECATEGORY."', '".TXT_WPSC_EXAMPLEDETAILS."', '1');");    
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PRODUCT_CATEGORIES."` (`group_id`, `name` , `description`, `active`) VALUES ('2', '".TXT_WPSC_EXAMPLEBRAND."', '".TXT_WPSC_EXAMPLEDETAILS."', '1');");
	}
  

  $purchase_statuses_data  = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_PURCHASE_STATUSES."`",ARRAY_A);
  if($purchase_statuses_data[0]['count'] == 0) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_PURCHASE_STATUSES."` (`name` , `active` , `colour` ) 
    VALUES
    ('".TXT_WPSC_RECEIVED."', '1', ''),
    ('".TXT_WPSC_ACCEPTED_PAYMENT."', '1', ''),
    ('".TXT_WPSC_JOB_DISPATCHED."', '1', ''),
    ('".TXT_WPSC_PROCESSED."', '1', '');");
	}

  $check_category_assoc = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."`;",ARRAY_A);
  if($check_category_assoc[0]['count'] == 0) {
    $sql = "SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active`=1";
    $product_list = $wpdb->get_results($sql,ARRAY_A);
    foreach((array)$product_list as $product) {
      $results = $wpdb->query("INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` (`product_id` , `category_id` ) VALUES ('".$product['id']."', '".$product['category']."');");
		}
	}
  
  
  $add_regions = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_REGION_TAX."`");
  // exit($add_regions);
  if($add_regions < 1) {
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Alberta', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'British Columbia', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Manitoba', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'New Brunswick', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Newfoundland', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Northwest Territories', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Nova Scotia', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Nunavut', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Ontario', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Prince Edward Island', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Quebec', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Saskatchewan', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '100', 'Yukon', '', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Alabama', 'AL', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Alaska', 'AK', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Arizona', 'AZ', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Arkansas', 'AR', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'California', 'CA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Colorado', 'CO', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Connecticut', 'CT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Delaware', 'DE', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Florida', 'FL', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Georgia', 'GA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Hawaii', 'HI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Idaho', 'ID', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Illinois', 'IL', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Indiana', 'IN', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Iowa', 'IA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Kansas', 'KS', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Kentucky', 'KY', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Louisiana', 'LA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Maine', 'ME', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Maryland', 'MD', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Massachusetts', 'MA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Michigan', 'MI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Minnesota', 'MN', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Mississippi', 'MS', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Missouri', 'MO', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Montana', 'MT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Nebraska', 'NE', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Nevada', 'NV', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New Hampshire', 'NH', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New Jersey', 'NJ', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New Mexico', 'NM', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'New York', 'NY', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'North Carolina', 'NC', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'North Dakota', 'ND', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Ohio', 'OH', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Oklahoma', 'OK', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Oregon', 'OR', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Pennsylvania', 'PA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Rhode Island', 'RI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'South Carolina', 'SC', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'South Dakota', 'SD', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Tennessee', 'TN', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Texas', 'TX', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Utah', 'UT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Vermont', 'VT', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Virginia', 'VA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Washington', 'WA', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Washington DC', 'DC', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'West Virginia', 'WV', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Wisconsin', 'WI', '0')");
    $wpdb->query("INSERT INTO `".WPSC_TABLE_REGION_TAX."` ( `country_id` , `name` ,`code`, `tax` ) VALUES ( '136', 'Wyoming', 'WY', '0')");
	}
    
    
	$data_forms = $wpdb->get_results("SELECT COUNT(*) AS `count` FROM `".WPSC_TABLE_CHECKOUT_FORMS."`",ARRAY_A);
	if($data_forms[0]['count'] == 0) { 
	$wpdb->query("INSERT INTO `".WPSC_TABLE_CHECKOUT_FORMS."` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order`) VALUES ( '".TXT_WPSC_YOUR_BILLING_CONTACT_DETAILS."', 'heading', '0', '0', '', '1', 1),
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
		update_option('country_form_field', $country_form_id[0]['id']);
		update_option('email_form_field', $email_form_id[0]['id']);
		$wpdb->query("INSERT INTO `".WPSC_TABLE_CHECKOUT_FORMS."` ( `name`, `type`, `mandatory`, `display_log`, `default`, `active`, `order` ) VALUES ( '".TXT_WPSC_PHONE."', 'text', '1', '0', '', '1', '8');");
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
  if(function_exists('register_sidebar') ) {
    add_option('cart_location', '4', TXT_WPSC_CARTLOCATION, 'yes');
	} else {
    add_option('cart_location', '1', TXT_WPSC_CARTLOCATION, 'yes');
	}

  if ( function_exists('register_sidebar') ) {
    add_option('cart_location', '4', TXT_WPSC_CARTLOCATION, 'yes');
  } else {
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

	add_option('google_key', 'none', TXT_WPSC_GOOGLEMECHANTKEY, 'yes');
	add_option('google_id', 'none', TXT_WPSC_GOOGLEMECHANTID, 'yes');
 
   add_option('default_brand', 'none', TXT_WPSC_DEFAULTBRAND, 'yes');
   add_option('wpsc_default_category', 'none', TXT_WPSC_DEFAULTCATEGORY, 'yes');
   
   add_option('product_view', 'default', "", 'yes');
   add_option('add_plustax', 'default', "", '1');
    
   
	add_option('nzshpcrt_first_load', '0', "", 'yes');
  
  if(!((get_option('show_categorybrands') > 0) && (get_option('show_categorybrands') < 3))) {
    update_option('show_categorybrands', 2);
	}
  //add_option('show_categorybrands', '0', TXT_WPSC_SHOWCATEGORYBRANDS, 'yes');
  /* PayPal options */
  add_option('paypal_business', '', TXT_WPSC_PAYPALBUSINESS, 'yes');
  add_option('paypal_url', '', TXT_WPSC_PAYPALURL, 'yes');
  add_option('paypal_ipn', '1', TXT_WPSC_PAYPALURL, 'yes');
  //update_option('paypal_url', "https://www.sandbox.paypal.com/xclick");
  
  
  add_option('paypal_multiple_business', '', TXT_WPSC_PAYPALBUSINESS, 'yes');
  
  if(get_option('paypal_multiple_url') == null) {
    add_option('paypal_multiple_url', TXT_WPSC_PAYPALURL, 'yes');
    update_option('paypal_multiple_url', "https://www.paypal.com/cgi-bin/webscr");
	}

  add_option('product_ratings', '0', TXT_WPSC_SHOWPRODUCTRATINGS, 'yes');
  add_option('wpsc_email_receipt', TXT_WPSC_DEFAULT_PURCHASE_RECEIPT, 'yes');
  add_option('wpsc_email_admin', TXT_WPSC_DEFAULT_PURCHASE_REPORT, 'yes');
  if(get_option('wpsc_selected_theme') == '') {
    add_option('wpsc_selected_theme', 'default', 'Selected Theme', 'yes');
    update_option('wpsc_selected_theme', "default");
	}


	
	if(!get_option('product_image_height')) {
		update_option('product_image_height', '96');
		update_option('product_image_width', '96');
	}
		
		
	if(!get_option('category_image_height')) {
		update_option('category_image_height', '96');
		update_option('category_image_width', '96');
	}
		
		
	if(!get_option('single_view_image_height')) {
		update_option('single_view_image_height', '128');
		update_option('single_view_image_width', '128');
	}
  
  wpsc_product_files_htaccess();
  
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
  foreach($pages as $page) {
    $check_page = $wpdb->get_row("SELECT * FROM `".$wpdb->posts."` WHERE `post_content` LIKE '%".$page['tag']."%'  AND `post_type` NOT IN('revision') LIMIT 1",ARRAY_A);
    if($check_page == null) {
      if($i == 0) {
        $post_parent = 0;
			} else {
        $post_parent = $first_id;
			}
      
      if($wp_version >= 2.1) {
        $sql ="INSERT INTO ".$wpdb->posts."
        (post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_type)
        VALUES
        ('1', '$post_date', '$post_date_gmt', '".$page['tag']."', '', '".$page['title']."', '', 'publish', 'closed', 'closed', '', '".$page['name']."', '', '', '$post_date', '$post_date_gmt', '$post_parent', '0', 'page')";
			} else {      
        $sql ="INSERT INTO ".$wpdb->posts."
        (post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order)
        VALUES
        ('1', '$post_date', '$post_date_gmt', '".$page['tag']."', '', '".$page['title']."', '', 'static', 'closed', 'closed', '', '".$page['name']."', '', '', '$post_date', '$post_date_gmt', '$post_parent', '0')";
			}
      $wpdb->query($sql);
      $post_id = $wpdb->insert_id;
      if($i == 0) {
        $first_id = $post_id;
        }
      $wpdb->query("UPDATE $wpdb->posts SET guid = '" . get_permalink($post_id) . "' WHERE ID = '$post_id'");
      update_option($page['option'],  get_permalink($post_id));
      if($page['option'] == 'shopping_cart_url') {
        update_option('checkout_url',  get_permalink($post_id));
			}
      $newpages = true;
      $i++;
		}
	}
  if($newpages == true) {
    wp_cache_delete('all_page_ids', 'pages');
    $wp_rewrite->flush_rules();
	}
   
   
   /* adds nice names for permalinks for products */
   $check_product_names = $wpdb->get_results("SELECT `".WPSC_TABLE_PRODUCT_LIST."`.`id`, `".WPSC_TABLE_PRODUCT_LIST."`.`name`, `".WPSC_TABLE_PRODUCTMETA."`.`meta_key` FROM `".WPSC_TABLE_PRODUCT_LIST."` LEFT JOIN `".WPSC_TABLE_PRODUCTMETA."` ON `".WPSC_TABLE_PRODUCT_LIST."`.`id` = `".WPSC_TABLE_PRODUCTMETA."`.`product_id` WHERE (`".WPSC_TABLE_PRODUCTMETA."`.`meta_key` IN ('url_name') AND  `".WPSC_TABLE_PRODUCTMETA."`.`meta_value` IN (''))  OR ISNULL(`".WPSC_TABLE_PRODUCTMETA."`.`meta_key`)");  
  if($check_product_names != null) {
    $sql_query = "SELECT `id`, `name` FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `active` IN('1')";
    $sql_data = $wpdb->get_results($sql_query,ARRAY_A);    
    foreach((array)$sql_data as $datarow) {
      $tidied_name = trim($datarow['name']);
      $tidied_name = strtolower($tidied_name);
			$url_name = preg_replace(array("/(\s-\s)+/","/(\s)+/","/[^\w-]+/i"), array("-","-", ''), $tidied_name);     
      $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`meta_value`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCTMETA."` WHERE `meta_key` LIKE 'url_name' AND `meta_value` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
      $extension_number = '';
      if($similar_names['count'] > 0) {
        $extension_number = (int)$similar_names['max_number']+1;
			}      
      if(get_product_meta($datarow['id'], 'url_name') != false) {
        $current_url_name = get_product_meta($datarow['id'], 'url_name');
        if($current_url_name != $url_name) {
          $url_name .= $extension_number;
          update_product_meta($datarow['id'], 'url_name', $url_name);
				}
			} else {
        $url_name .= $extension_number;
        add_product_meta($datarow['id'], 'url_name', $url_name, true);
			}
		}
	}
    
  
  /* adds nice names for permalinks for categories */
  $check_category_names = $wpdb->get_results("SELECT DISTINCT `nice-name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `nice-name` IN ('') AND `active` IN ('1')");
  if($check_category_names != null) {
    $sql_query = "SELECT `id`, `name` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `active` IN('1')";
    $sql_data = $wpdb->get_results($sql_query,ARRAY_A);    
    foreach((array)$sql_data as $datarow) {
      $tidied_name = trim($datarow['name']);
      $tidied_name = strtolower($tidied_name);
			$url_name = preg_replace(array("/(\s-\s)+/","/(\s)+/","/[^\w-]+/i"), array("-","-", ''), $tidied_name);    
      $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `nice-name` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
      $extension_number = '';
      if($similar_names['count'] > 0) {
        $extension_number = (int)$similar_names['max_number']+1;
			}
      $url_name .= $extension_number;
      $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_CATEGORIES."` SET `nice-name` = '$url_name' WHERE `id` = '".$datarow['id']."' LIMIT 1 ;");
		}
		$wp_rewrite->flush_rules();
	}
    
    
  
  /* Moves images to thumbnails directory */
   // this code should no longer be needed, as most people will be using a sufficiently new version
  $image_dir = WPSC_FILE_PATH."/images/";
  $product_images = WPSC_IMAGE_DIR;
  $product_thumbnails = WPSC_THUMBNAIL_DIR;
  if(!is_dir($product_thumbnails)) {
    @ mkdir($product_thumbnails, 0775);
	}
  $product_list = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_PRODUCT_LIST."` WHERE `image` != ''",ARRAY_A);
  foreach((array)$product_list as $product) {
    if(!glob($product_thumbnails.$product['image'])) {
      $new_filename = $product['id']."_".$product['image'];
      if(file_exists($image_dir.$product['image'])) {
        copy($image_dir.$product['image'], $product_thumbnails.$new_filename);
        if(file_exists($product_images.$product['image'])) {
          copy($product_images.$product['image'], $product_images.$new_filename);
				}
        $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$new_filename."' WHERE `id`='".$product['id']."' LIMIT 1");
			} else {
        $imagedir = $product_thumbnails;
        $name = $new_filename;
        $new_image_path = $product_images.$product['image'];
        $imagepath = $product['image'];
        $height = get_option('product_image_height');
        $width  = get_option('product_image_width');
        if(file_exists($product_images.$product['image'])) {
          include("extra_image_processing.php");
          copy($product_images.$product['image'], $product_images.$new_filename);
          $wpdb->query("UPDATE `".WPSC_TABLE_PRODUCT_LIST."` SET `image` = '".$new_filename."' WHERE `id`='".$product['id']."' LIMIT 1");
				}
			}
		}
	}	// */
   
}
  
function wpsc_uninstall_plugin() {
  global $wpdb;
  if(current_user_can('edit_plugins')) {
		$option_list[] = 'addtocart_or_buynow ';
		$option_list[] = 'add_plustax ';
		$option_list[] = 'base_country ';
		$option_list[] = 'base_international_shipping ';
		$option_list[] = 'base_local_shipping ';
		$option_list[] = 'cart_location ';
		$option_list[] = 'category_image_height ';
		$option_list[] = 'category_image_width ';
		$option_list[] = 'catsprods_display_type ';
		$option_list[] = 'cat_brand_loc ';
		$option_list[] = 'checkbox_variations ';
		$option_list[] = 'checkout_url ';
		$option_list[] = 'country_form_field ';
		$option_list[] = 'currency_sign_location ';
		$option_list[] = 'currency_type ';
		$option_list[] = 'default_brand ';
		$option_list[] = 'display_pnp ';
		$option_list[] = 'display_specials ';
		$option_list[] = 'do_not_use_shipping ';
		$option_list[] = 'email_form_field ';
		$option_list[] = 'fancy_notifications ';
		$option_list[] = 'googleStoreLocator ';
		$option_list[] = 'google_button_bg ';
		$option_list[] = 'google_button_size ';
		$option_list[] = 'google_cur ';
		$option_list[] = 'google_id ';
		$option_list[] = 'google_key ';
		$option_list[] = 'google_server_type ';
		$option_list[] = 'google_shipping_country ';
		$option_list[] = 'gst_rate ';
		$option_list[] = 'hide_addtocart_button ';
		$option_list[] = 'hide_name_link ';
		$option_list[] = 'language_setting ';
		$option_list[] = 'list_view_quantity ';
		$option_list[] = 'max_downloads ';
		$option_list[] = 'nzshpcrt_first_load ';
		$option_list[] = 'payment_gateway ';
		$option_list[] = 'paypal_business ';
		$option_list[] = 'paypal_curcode ';
		$option_list[] = 'paypal_ipn ';
		$option_list[] = 'paypal_multiple_business ';
		$option_list[] = 'paypal_multiple_url ';
		$option_list[] = 'paypal_url ';
		$option_list[] = 'postage_and_packaging ';
		$option_list[] = 'product_image_height ';
		$option_list[] = 'product_image_width ';
		$option_list[] = 'product_list_url ';
		$option_list[] = 'product_ratings ';
		$option_list[] = 'product_view ';
		$option_list[] = 'purch_log_email ';
		$option_list[] = 'require_register ';
		$option_list[] = 'return_email ';
		$option_list[] = 'shopping_cart_url ';
		$option_list[] = 'show_advanced_search ';
		$option_list[] = 'show_categorybrands ';
		$option_list[] = 'show_category_count ';
		$option_list[] = 'show_category_thumbnails ';
		$option_list[] = 'show_images_only ';
		$option_list[] = 'show_live_search ';
		$option_list[] = 'show_search ';
		$option_list[] = 'show_sliding_cart ';
		$option_list[] = 'show_thumbnails ';
		$option_list[] = 'single_view_image_height ';
		$option_list[] = 'single_view_image_width ';
		$option_list[] = 'terms_and_conditions ';
		$option_list[] = 'transact_url ';
		$option_list[] = 'user_account_url ';
		$option_list[] = 'use_pagination ';
		$option_list[] = 'usps_user_id ';
		$option_list[] = 'wpsc_also_bought ';
		$option_list[] = 'wpsc_category_description ';
		$option_list[] = 'wpsc_dropshop_theme ';
		$option_list[] = 'wpsc_minor_version ';
		$option_list[] = 'wpsc_page_number_position ';
		$option_list[] = 'wpsc_products_per_page ';
		$option_list[] = 'wpsc_selected_theme ';
		$option_list[] = 'wpsc_use_pnp_cols ';
		$option_list[] = 'wpsc_version'; 
		$option_list[] = 'wpsc_incomplete_file_transfer'; 
		$option_list[] = 'wpsc_ip_lock_downloads'; 
		$option_list[] = 'wpsc_database_check'; 		
		$option_list[] = 'wpsc_default_category'; 
		$option_list[] = 'wpsc_email_receipt'; 
		$option_list[] = 'wpsc_email_admin'; 
		$option_list[] = 'wpsc_email_receipt'; 
		$option_list[] = 'wpsc_email_admin'; 
		$option_list[] = 'shipwire'; 
		$option_list[] = 'base_zipcode'; 
		$option_list[] = 'custom_gateway_options'; 
		$option_list[] = 'paypal_certified_apiuser'; 
		$option_list[] = 'paypal_certified_apipass'; 
		$option_list[] = 'paypal_certified_apisign'; 
		
		
		foreach($option_list as $wpsc_option) {
			delete_option($wpsc_option);
		}
		
		
    include_once('updates/database_template.php');
    
    $wpsc_table_list = array_keys($wpsc_database_template);
		foreach($wpsc_table_list as $wpsc_table_name) {
			$wpdb->query("DROP TABLE `{$wpsc_table_name}`");
		}
		
		$active_plugins = get_option('active_plugins');
		unset($active_plugins[array_search(WPSC_DIR_NAME.'/wp-shopping-cart.php', $active_plugins)]);
		update_option('active_plugins', $active_plugins);
		header('Location: '.get_option('siteurl').'/wp-admin/plugins.php');
		exit();
	}
}

function wpsc_uninstall_plugin_link($plugin) {
	if(($plugin == WPSC_DIR_NAME.'/wp-shopping-cart.php') && current_user_can('edit_plugins')) {
		echo "<td class='plugin-update' colspan='5' style='background: #ff7777;'>";
		echo "Are you sure, uninstalling will permanently delete all your wp-e-commerce settings: <a href='?wpsc_uninstall=verified'>Yes</a> or <a href='plugins.php'>No</a>";
		echo "</td>";
	}
}
if($_GET['wpsc_uninstall'] === 'verified') {
  add_action( 'init', 'wpsc_uninstall_plugin' );
}

add_action('register_uninstall_hook', 'wpsc_uninstall_plugin');

if($_GET['wpsc_uninstall'] === 'ask') {
  add_action( 'after_plugin_row', 'wpsc_uninstall_plugin_link' );
}
// add_action( 'after_plugin_row', 'wpsc_uninstall_plugin_link' );

function wpsc_product_files_htaccess() {
  if(!is_file(WPSC_FILE_DIR.".htaccess")) {
		$htaccess = "order deny,allow\n\r";
		$htaccess .= "deny from all\n\r";
		$htaccess .= "allow from none\n\r";
		$filename = WPSC_FILE_DIR.".htaccess";
		$file_handle = @ fopen($filename, 'w+');
		@ fwrite($file_handle, $htaccess);
		@ fclose($file_handle);
		@ chmod($file_handle, 0665);
  }
}



function wpsc_check_and_copy_files() {
  $upload_path = 'wp-content/plugins/'.WPSC_DIR_NAME;
  
	$wpsc_dirs['files']['old'] = ABSPATH."{$upload_path}/files/";
	$wpsc_dirs['files']['new'] = WPSC_FILE_DIR;
	
	$wpsc_dirs['previews']['old'] = ABSPATH."{$upload_path}/preview_clips/";
	$wpsc_dirs['previews']['new'] = WPSC_PREVIEW_DIR;
	
	// I don't include the thumbnails directory in this list, as it is a subdirectory of the images directory and is moved along with everything else
	$wpsc_dirs['images']['old'] = ABSPATH."{$upload_path}/product_images/";
	$wpsc_dirs['images']['new'] = WPSC_IMAGE_DIR;
	
	$wpsc_dirs['categories']['old'] = ABSPATH."{$upload_path}/category_images/";
	$wpsc_dirs['categories']['new'] = WPSC_CATEGORY_DIR;
	$incomplete_file_transfer = false;
	foreach($wpsc_dirs as $wpsc_dir) {
	  if(is_dir($wpsc_dir['old'])) {
	    $files_in_dir = glob($wpsc_dir['old']."*");
			$stat = stat($wpsc_dir['new']);
			
	    if(count($files_in_dir) > 0) {
	      foreach($files_in_dir as $file_in_dir) {
	        $file_name = str_replace($wpsc_dir['old'], '', $file_in_dir);
	        if( @ rename($wpsc_dir['old'].$file_name, $wpsc_dir['new'].$file_name) ) {
	          if(is_dir($wpsc_dir['new'].$file_name)) {
							$perms = $stat['mode'] & 0000775;
	          } else { $perms = $stat['mode'] & 0000665; }
	          
	          @ chmod( ($wpsc_dir['new'].$file_name), $perms );	
	        } else {
	          $incomplete_file_transfer = true;
	        }
	      }
	    }
	  }
	}
	if($incomplete_file_transfer == true) {
		add_option('wpsc_incomplete_file_transfer', 'default', "", 'true');
	}
	
}


function wpsc_create_upload_directories() {
  $wpsc_files_directory = WP_CONTENT_DIR.'/uploads/wpsc/';
  
  if(!is_dir(WP_CONTENT_DIR.'/uploads')) {
	  @ mkdir(WP_CONTENT_DIR.'/uploads', 0775);
  }

  if(!is_dir($wpsc_files_directory)) {
	  @ mkdir($wpsc_files_directory, 0775);
  }
  
  if(!is_dir(WPSC_FILE_DIR)) {
	  @ mkdir(WPSC_FILE_DIR, 0775);
		wpsc_product_files_htaccess();  
  }
  
	if(!is_dir(WPSC_PREVIEW_DIR)) {
		@ mkdir(WPSC_PREVIEW_DIR, 0775);
	}
		
	if(!is_dir(WPSC_IMAGE_DIR)) {
		@ mkdir(WPSC_IMAGE_DIR, 0775);
	}
		
	if(!is_dir(WPSC_THUMBNAIL_DIR)) {
		@ mkdir(WPSC_THUMBNAIL_DIR, 0775);
	}
		
	if(!is_dir(WPSC_CATEGORY_DIR)) {
		@ mkdir(WPSC_CATEGORY_DIR, 0775);
	}
		
	if(!is_dir(WPSC_USER_UPLOADS_DIR)) {
		@ mkdir(WPSC_USER_UPLOADS_DIR, 0775);
	}
	
	if(!is_dir(WPSC_CACHE_DIR)) {
		@ mkdir(WPSC_CACHE_DIR, 0775);
	}
	
	
	$wpsc_file_directory = ABSPATH.get_option('upload_path').'/wpsc/';
	if(is_dir($wpsc_file_directory)) {
	  // sort the permissions out in case they are not already sorted out.	  
		@ chmod( ABSPATH.get_option('upload_path'), 0775 );			
		@ chmod( $wpsc_file_directory, 0775 );			
		@ chmod( WPSC_FILE_DIR, 0775 );			
		@ chmod( WPSC_PREVIEW_DIR, 0775 );			
		@ chmod( WPSC_IMAGE_DIR, 0775 );	
		@ chmod( WPSC_CATEGORY_DIR, 0775 );	
		@ chmod( WPSC_USER_UPLOADS_DIR, 0775 );	
		@ chmod( WPSC_CACHE_DIR, 0775 );
	}
}


function wpsc_create_or_update_tables($debug = false) {
  global $wpdb;
  // creates or updates the structure of the shopping cart tables
  
  include('updates/database_template.php');
  
  $template_hash = sha1(serialize($wpsc_database_template));
  
  if((get_option('wpsc_database_check') == $template_hash) && ($debug == false)) {
    return true;
  }
  
  $failure_reasons = array();
  $upgrade_failed = false;
  foreach((array)$wpsc_database_template as $table_name => $table_data) {
    if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'") && !$wpdb->get_var("SHOW TABLES LIKE '{$table_data['previous_names']}'")) {
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
      $constructed_sql .= "\n) ENGINE=MyISAM";
      
			
      // if mySQL is new enough, set the character encoding
			if( method_exists($wpdb, 'db_version') &&  version_compare($wpdb->db_version(), '4.1', '>=')) {
				$constructed_sql .= " CHARSET=utf8";
			}
			$constructed_sql .= ";";
    
      if(!$wpdb->query($constructed_sql)) {
        $upgrade_failed = true;
        $failure_reasons[] = $wpdb->last_error;
      }
      //echo "<pre>$constructed_sql</pre>";
    } else {
      // check to see if the new table name is in use
			if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'") && $wpdb->get_var("SHOW TABLES LIKE '{$table_data['previous_names']}'")) {
				$wpdb->query("RENAME TABLE `{$table_data['previous_names']}`  TO `{$table_name}`;");
				$failure_reasons[] = $wpdb->last_error;
			}
    
      //check to see if the table needs updating
      $existing_table_columns = array();
      //check and possibly update the character encoding      			
			if( method_exists($wpdb, 'db_version') &&  version_compare($wpdb->db_version(), '4.1', '>=')) {
				$table_status_data = $wpdb->get_row("SHOW TABLE STATUS LIKE '$table_name'", ARRAY_A);
				if($table_status_data['Collation'] != 'utf8_general_ci') {
					$wpdb->query("ALTER TABLE `$table_name`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
				}
			}
      //get the column list
      $existing_table_column_data = $wpdb->get_results("SHOW FULL COLUMNS FROM `$table_name`", ARRAY_A);

      foreach((array)$existing_table_column_data as $existing_table_column) {
        $column_name = $existing_table_column['Field'];
        $existing_table_columns[] = $column_name;
        
				//echo "<pre>".print_r($existing_table_column,true)."</pre>";
        if(isset($table_data['columns'][$column_name]) && (stristr($table_data['columns'][$column_name], $existing_table_column['Type']) === false)) {
          $wpdb->query("ALTER TABLE `$table_name` CHANGE `$column_name` `$column_name` {$table_data['columns'][$column_name]} ");
				}
        
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
  }
  
	if($upgrade_failed !== true) {
		//update_option('wpsc_database_check', $template_hash);
		return true;
	} else {
	  return false;
	}
	//echo "<pre>".print_r($missing_or_extra_table_indexes,true)."</pre>";
}
?>
