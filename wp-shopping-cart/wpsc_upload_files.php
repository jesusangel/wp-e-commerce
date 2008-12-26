<?php
/* Called by SWFupload to upload files
/
*/

if ( defined('ABSPATH') )
	require_once(ABSPATH . 'wp-load.php');
else
	require_once('../../../wp-load.php');  // presumes wp-load.php/wp-content/plugins/wp-shopping-cart

// Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
	$_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
	$_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];
unset($current_user);
require_once(ABSPATH.'/wp-admin/admin.php');

header('Content-Type: text/plain; charset=' . get_option('blog_charset'));

if ( !current_user_can('upload_files') )
	wp_die(__('You do not have permission to upload files.'));
	
check_admin_referer('wp-shopping-cart');


	$fileid = wpsc_item_process_file($_REQUEST['subaction']);
	$file = $fileid;
	
	// Rebuild the file table table
	$new_table = wpsc_select_product_file($_REQUEST['prodid']);
	die($new_table);

?>