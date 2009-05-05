<?php
/**
 * WP eCommerce edit and view sales page functions
 *
 * These are the main WPSC sales page functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

 function wpsc_display_sales_logs(){
 		//$purchlogitem = new wpsc_purchaselogs_items((int)$_REQUEST['purchaselog_id']);
 	?>
	<div class="wrap">
		<?php //screen_icon(); ?>
		<h2><?php echo wp_specialchars( TXT_WPSC_PURCHASELOG ); ?> </h2>
		<?php //START OF PURCHASE LOG DEFAULT VIEW ?>
		<?php if(!isset($_REQUEST['purchaselog_id'])){
				$purchlogs = new wpsc_purchaselogs();
		  		$columns = array(
		  			'cb' => '<input type="checkbox" />',
					'date' => 'Date',
					'name' => '',
					'amount' => 'Amount',
					'details' => 'Details',
					'status' => 'Status',
					'delete' => 'Delete'
				);
				register_column_headers('display-sales-list', $columns);	
				///// start of update message section //////
			if (isset($_GET['skipped']) || isset($_GET['updated']) || isset($_GET['deleted']) ) { ?>
			<div id="message" class="updated fade"><p>
			<?php if ( isset($_GET['updated']) && (int) $_GET['updated'] ) {
				printf( __ngettext( '%s Purchase Log updated.', '%s Purchase Logs updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
				unset($_GET['updated']);
			}
			
			if ( isset($_GET['skipped']) && (int) $_GET['skipped'] )
				unset($_GET['skipped']);
			
			if ( isset($_GET['locked']) && (int) $_GET['locked'] ) {
				printf( __ngettext( '%s product not updated, somebody is editing it.', '%s products not updated, somebody is editing them.', $_GET['locked'] ), number_format_i18n( $_GET['locked'] ) );
				unset($_GET['locked']);
			}
			
			if ( isset($_GET['deleted']) && (int) $_GET['deleted'] ) {
				printf( __ngettext( '%s Purchase Log deleted.', '%s Purchase Logs deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
				unset($_GET['deleted']);
			}
			
			$_SERVER['REQUEST_URI'] = remove_query_arg( array('locked', 'skipped', 'updated', 'deleted'), $_SERVER['REQUEST_URI'] );
			?>
		</p></div>
		<?php } 
		///// end of update message section //////?>
		<div id='dashboard-widgets' style='min-width: 825px;'>
			 <div id='side-info-column-wpsc' class='inner-sidebar'> 
					<div class='meta-box-sortables'>			
						<?php
							//$dates = $purchlogs->wpsc_getdates();
							//exit('<pre>'.print_r($dates, true).'</pre>');
							if(IS_WP27){
								//display_ecomm_admin_menu();
								display_ecomm_rss_feed();
								//wpsc_ordersummary();
							   
							}
						?>
					</div>
			</div>
			<?php /* end of sidebar start of main column */ ?>
			<div id='post-body' class='has-sidebar' style='width:95%;'>
				<div id='dashboard-widgets-main-content-wpsc' class='has-sidebar-content'>
				<?php 
					if(function_exists('wpsc_right_now')) {
						echo wpsc_right_now();
				    }
			   		wpsc_purchaselogs_searchbox();
			   		?> </div><br /><?php 
					wpsc_purchaselogs_displaylist(); 
					?> 				
				
			</div>
		</div>
		<?php }else{ //NOT IN GENERIC PURCHASE LOG PAGE, IN DETAILS PAGE PER PURCHASE LOG 
		$page_back = remove_query_arg( array('locked', 'skipped', 'updated', 'deleted','purchaselog_id'), $_SERVER['REQUEST_URI'] );
		?>
			
			<a href='<?php echo $page_back ?>'>BACK</a>
		<?php

			$columns = array(
	  	'title' => 'Name',
		'sku' => 'SKU',
		'quantity' => 'Quantity',
		'price' => 'Price',
		'tax' => 'Tax',
		'discount' => 'Discount',
		'total' => 'Total'
			);
			register_column_headers('display-purchaselog-details', $columns); 
		?>
			<div id='post-body' class='has-sidebar' style='width:95%;'>
				<div style='width:35%; float:right;border:1px #d3d3d3 solid; margin:.4em;padding:.6em;'>
					<h3>Billing Details</h3>
					<p><strong>Purchase Log Date: </strong><?php echo wpsc_purchaselog_details_date(); ?> </p>
					<p><strong>Purchase Number: </strong><?php echo wpsc_purchaselog_details_purchnumber(); ?> </p>
					<p><strong>Buyers Name: </strong><?php echo wpsc_display_purchlog_buyers_name(); ?></p>
					<p><strong>Phone: </strong><?php echo wpsc_display_purchlog_buyers_phone(); ?></p>
					<p><strong>Email: </strong><?php echo wpsc_display_purchlog_buyers_email(); ?></p>
				</div>
				<div style='width:45%; float:left;border:1px #d3d3d3 solid; margin:.4em;padding:.6em;'>	
					<h3>Shipping Details</h3>
					<p><strong><?php echo wpsc_display_purchlog_shipping_name(); ?></strong></p>
					<p>
					<?php echo wpsc_display_purchlog_shipping_address(); ?><br />
					<?php echo wpsc_display_purchlog_shipping_city(); ?><br />
					<?php echo wpsc_display_purchlog_shipping_state_and_postcode(); ?><br />
					<?php echo wpsc_display_purchlog_shipping_country(); ?><br />
					</p>
				</div>
				
					<table class="widefat" cellspacing="0">
						<thead>
							<tr>
						<?php print_column_headers('display-purchaselog-details'); ?>
							</tr>
						</thead>
					
						<tfoot>
							<tr>
						<?php// print_column_headers('display-purchaselog-details', false); ?>
							</tr>
						</tfoot>
					
						<tbody>
						<?php wpsc_display_purchlog_details(); ?>
						<tr></tr>
						<tr>
							<td colspan='5'></td>
							<th>Shipping </th>
							<td><?php echo wpsc_display_purchlog_shipping(); ?></td>
						</tr>
						<tr>
							<td colspan='5'></td>
							<th>Total </th>
							<td><?php echo wpsc_display_purchlog_totalprice(); ?></td>
						</tr>
						</tbody>
				</table>
			
				
				</div>
			</div>
			<?php }	?>
	</div>
	<?php

 }
 
 function display_ecomm_admin_menu(){
 	?>	
 	<div class="meta-box-sortables ui-sortable" style="position: relative;"> 
 		<div class='postbox'> 
			<h3 class='hndle'><?php echo TXT_WPSC_MENU; ?></h3>
			<div class='inside'>
				<a href="?page=<?php echo WPSC_DIR_NAME;?>/options.php"><?php echo TXT_WPSC_SHOP_SETTINGS; ?></a><br />
				<a href="?page=<?php echo WPSC_DIR_NAME;?>/gatewayoptions.php"><?php echo TXT_WPSC_CHECKOUT_SETTINGS; ?></a><br />
				<a href="?page=<?php echo WPSC_DIR_NAME;?>/form_fields.php"><?php echo TXT_WPSC_CHECKOUT_SETTINGS; ?></a><br />
			</div>
		</div>		
	</div>
	<?php
 }
 function display_ecomm_rss_feed(){
 	require_once (ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss('http://www.instinct.co.nz/feed/');	
		if($rss != null) {
			$rss->items = array_slice((array)$rss->items, 0, 5);
			$current_hash = sha1(serialize($rss->items));
			if((string)get_option('wpsc_ecom_news_hash') !== (string)$current_hash ) {
				?>
				<div class='postbox'> 
					<h3 class='hndle'><?php echo TXT_WPSC_ECOM_NEWS; ?></h3>
					<div class='inside'>
					<ul class='ecom_dashboard'>
					<?php
					foreach($rss->items as $items) {
						echo "<li><a href='".$items['link']."'>".$items['title']."</a></li>";
					}
					?>
					</ul>
					<?php
					if (!IS_WP27)
					 echo "<a href='admin.php?page=<?php echo WPSC_DIR_NAME;?>/display-log.php&#038;hide_news=true' id='close_news_box'>X</a>";
					?>
					</div>
				</div>
				<?php
			}
    }
    function wpsc_ordersummary(){
    ?>
    	<div class='postbox'> 
    	<h3 class='hndle'><?php echo TXT_WPSC_ORDER_SUMMARY; ?></h3>
    
   		 <div class='inside'> 
      <div class='order_summary_subsection'>
      <strong><?php echo TXT_WPSC_TOTAL_THIS_MONTH; ?></strong>
      <p id='log_total_month'>
      <?php 
      $year = date("Y");
      $month = date("m");
      $start_timestamp = mktime(0, 0, 0, $month, 1, $year);
      $end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
      echo nzshpcrt_currency_display(admin_display_total_price($start_timestamp, $end_timestamp),1);
      echo " ".TXT_WPSC_ACCEPTED_PAYMENTS;
      ?>
      </p>
      </div>
      <div class='order_summary_subsection'>
      <strong><?php echo TXT_WPSC_TOTAL_INCOME; ?></strong>
      <p id='log_total_absolute'>
      <?php
       //$total_income = $wpdb->get_results($sql,ARRAY_A);
       echo nzshpcrt_currency_display(admin_display_total_price(),1);
       ?>
      </p>
      </div>
      
      
     
      <div class='order_summary_subsection'>
      <strong><?php echo TXT_WPSC_RSS_FEED_HEADER; ?></strong>
      <p>
        <a class='product_log_rss' href='index.php?rss=true&amp;rss_key=key&amp;action=purchase_log'><img align='middle' src='<?php echo WPSC_URL; ?>/images/rss-icon.jpg' alt='' title='' />&nbsp;<span><?php echo TXT_WPSC_RSS_FEED_LINK; ?></span></a> <?php echo TXT_WPSC_RSS_FEED_TEXT; ?>      </p>
      </div>
         <div class='order_summary_subsection'>
      <strong><?php echo TXT_WPSC_PLUGIN_NEWS_HEADER; ?></strong>
      <p>
      <?php echo TXT_WPSC_PLUGIN_NEWS; ?>        
        <br /><br /><?php echo TXT_WPSC_POWERED_BY; ?><a href='http://www.instinct.co.nz'>Instinct</a>
      </p>
      </div>
    </div>
    <?php
    if(get_option('activation_state') != "true") {
      ?>
      <div class='gold-cart_pesterer'> 
        <div>
        <img src='<?php echo WPSC_URL; ?>/images/gold-cart.png' alt='' title='' /><a href='http://www.instinct.co.nz/e-commerce/shop/'><?php echo TXT_WPSC_UPGRADE_TO_GOLD; ?></a><?php echo TXT_WPSC_UNLEASH_MORE; ?>
        </div>
      </div>
      
      <?php
    }
    ?>
    </div>

	<?php
    }

 }
 function wpsc_purchaselogs_displaylist(){
 
  ?>
  	<form method='post' action=''>
  	&nbsp;<img src='<?php echo WPSC_URL."/images/cornerarrow.png"; ?>' alt='' />
  		<select id='purchlog_multiple_status_change' name='purchlog_multiple_status_change' class='purchlog_multiple_status_change'>
  			<option value='-1'><?php _e('Bulk Actions'); ?></option>
  			<?php while(wpsc_have_purch_items_statuses()) : wpsc_the_purch_status(); ?>
 				<option value='<?php echo wpsc_the_purch_status_id(); ?>' <?php echo wpsc_is_checked_status(); ?> >
 					<?php echo wpsc_the_purch_status_name(); ?> 
 				</option>
 			<?php endwhile; ?>
			<option value="delete"><?php _e('Delete'); ?></option>
  		</select>
  		<input type='hidden' value='purchlog_bulk_modify' name='wpsc_admin_action' />
  		<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
  		<?php /* View functions for purchlogs */?>
  		<label for='view_purchlogs_by'>View:</label>

  		<select id='view_purchlogs_by' name='view_purchlogs_by'>
  			<option value='all' selected='selected'>All</option>
			<option value='3mnths'>Three Months</option>
  			<?php  echo wpsc_purchlogs_getfirstdates(); ?>
  		</select>
  		<select id='view_purchlogs_by_status' name='view_purchlogs_by_status'>
  			<option value='-1'>Status: All</option>
  			<?php while(wpsc_have_purch_items_statuses()) : wpsc_the_purch_status(); ?>
 				<option value='<?php echo wpsc_the_purch_status_id(); ?>' <?php echo wpsc_is_checked_status(); ?> >
 					<?php echo wpsc_the_purch_status_name(); ?> 
 				</option>
 			<?php endwhile; ?>

  		</select>
  		<input type='hidden' value='purchlog_filter_by' name='wpsc_admin_action' />
  		<input type="submit" value="<?php _e('Filter'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
  		<?php if(wpsc_have_purch_items() ==false):  ?>
  		<p style='color:red;'>Oops there are no purchase logs for your selection, please try again.</p>
  		
  		<?php endif;?>
	 	<table class="widefat page fixed" cellspacing="0">
			<thead>
				<tr>
			<?php print_column_headers('display-sales-list'); ?>
				</tr>
			</thead>
		
			<tfoot>
				<tr>
			<?php print_column_headers('display-sales-list', false); ?>
				</tr>
			</tfoot>
		
			<tbody>
			<?php get_purchaselogs_content(); ?>
			</tbody>
		</table>
		<p><strong>Total:</strong> <?php echo nzshpcrt_currency_display(wpsc_the_purch_total(), true); ?></p>
	</form>
	<script type="text/javascript">
	/* <![CDATA[ */
	(function($){
		$(document).ready(function(){
			$('#doaction, #doaction2').click(function(){
				if ( $('select[name^="purchlog_multiple_status_change"]').val() == 'delete' ) {
					var m = '<?php echo js_escape(__("You are about to delete the selected purchase logs.\n  'Cancel' to stop, 'OK' to delete.")); ?>';
					return showNotice.warn(m);
				}
			});
		});
	})(jQuery);
	columns.init('edit');
	/* ]]> */
	</script>

<?php
 
 }
 function get_purchaselogs_content(){
 
 	while(wpsc_have_purch_items()) : wpsc_the_purch_item();	
 	?>
 	<tr>
 		<th class="check-column" scope="row"><input type='checkbox' name='purchlogids[]' class='editcheckbox' value='<?php echo wpsc_the_purch_item_id(); ?>' /></th>
 		<td><?php echo wpsc_the_purch_item_date(); ?></td> <!--Date -->
 		<td><?php echo wpsc_the_purch_item_name(); ?></td> <!--Name/email -->
 		<td><?php echo nzshpcrt_currency_display(wpsc_the_purch_item_price(), true); ?></td><!-- Amount -->
 		<td><a href='<?php echo add_query_arg('purchaselog_id', wpsc_the_purch_item_id()); ?>'><?php echo wpsc_the_purch_item_details();?> Items</a></td><!-- Details -->
 		<td>
 	
 			<select class='selector' name='<?php echo wpsc_the_purch_item_id(); ?>' title='<?php echo wpsc_the_purch_item_id(); ?>' >
 			<?php while(wpsc_have_purch_items_statuses()) : wpsc_the_purch_status(); ?>
 				<option value='<?php echo wpsc_the_purch_status_id(); ?>' <?php echo wpsc_is_checked_status(); ?> ><?php echo wpsc_the_purch_status_name(); ?> </option>
 			<?php endwhile; ?>
 			</select>
 	
 		</td><!-- Status -->
 		<td><a class='submitdelete' title='<?php echo attribute_escape(__('Delete this log')); ?>' href='<?php echo wp_nonce_url("page.php?wpsc_admin_action=delete_purchlog&amp;purchlog_id=".wpsc_the_purch_item_id(), 'delete_purchlog_' . wpsc_the_purch_item_id()); ?>' onclick="if ( confirm(' <?php echo js_escape(sprintf( __("You are about to delete this log '%s'\n 'Cancel' to stop, 'OK' to delete."),  wpsc_the_purch_item_date() )) ?>') ) { return true;}return false;"><img src='<?php echo WPSC_URL."/images/cross.png"; ?>' alt='delete icon' /><?php _e('Delete') ?></a></td><!-- Delete -->
 	</tr>
 	<?php
 	endwhile;
 }
 function wpsc_purchaselogs_searchbox(){
 	?>
 	<form action='' method='post'>
 		<input type='hidden' name='wpsc_admin_action' value='purchlogs_search' />
 		<input type='text' value='<?php if(isset($_POST['purchlogs_searchbox'])) echo $_POST['purchlogs_searchbox']; ?>' name='purchlogs_searchbox' id='purchlogs_searchbox' />
 		<input type="submit" value="<?php _e('Search Logs'); ?>"  class="button-secondary action" />
  	</form>
 	<?php
 }
 function wpsc_display_purchlog_details(){
 	while(wpsc_have_purchaselog_details()) : wpsc_the_purchaselog_item();
 	?>
 	<tr>
 	<td><?php echo wpsc_purchaselog_details_name(); ?></td> <!-- NAME -->
 	<td><?php //echo wpsc_purchaselog_details_SKU(); ?></td> <!-- SKU -->
 	<td><?php echo wpsc_purchaselog_details_quantity(); ?></td> <!-- QUANTITY-->
 	<td><?php echo nzshpcrt_currency_display(wpsc_purchaselog_details_price(),true); ?></td> <!-- PRICE -->
 	<td><?php echo nzshpcrt_currency_display(wpsc_purchaselog_details_tax(),true); ?></td> <!-- TAX -->
 	<td><?php echo nzshpcrt_currency_display(wpsc_purchaselog_details_discount(),true); ?></td> <!-- DISCOUNT -->
 	<td><?php echo nzshpcrt_currency_display(wpsc_purchaselog_details_total(),true); ?></td> <!-- TOTAL -->
 	</tr>
 	<?php
 	endwhile;
 }

 ?>