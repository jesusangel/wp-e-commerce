<?php
function wpsc_options_admin(){
global $wpdb;
?>
<form name='cart_options' id='cart_options' method='post' action=''>
	<div id="options_admin">
	  <h2><?php echo TXT_WPSC_OPTIONS_ADMIN_HEADER; ?></h2>
			<table class='wpsc_options form-table'>            
				<tr>
					<th scope="row"><?php echo TXT_WPSC_MAXDOWNLOADSPERFILE;?>:	</th>
					<td>
						<input type='text' size='10' value='<?php echo get_option('max_downloads'); ?>' name='wpsc_options[max_downloads]' />
					</td>
				</tr>				
				<?php
				$wpsc_ip_lock_downloads1 = "";
				$wpsc_ip_lock_downloads2 = "";
				switch(get_option('wpsc_ip_lock_downloads')) {    
					case 1:
					$wpsc_ip_lock_downloads1 = "checked ='checked'";
					break;
							
					case 0:
					default:
					$wpsc_ip_lock_downloads2 = "checked ='checked'";
					break;
				}
	
				?>
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_LOCK_DOWNLOADS_TO_IP;?>:
					</th>
					<td>
						<input type='radio' value='1' name='wpsc_options[wpsc_ip_lock_downloads]' id='wpsc_ip_lock_downloads2' <?php echo $wpsc_ip_lock_downloads1; ?> /> <label for='wpsc_ip_lock_downloads2'><?php echo TXT_WPSC_YES;?></label>&nbsp;
						<input type='radio' value='0' name='wpsc_options[wpsc_ip_lock_downloads]' id='wpsc_ip_lock_downloads1' <?php echo $wpsc_ip_lock_downloads2; ?> /> <label for='wpsc_ip_lock_downloads1'><?php echo TXT_WPSC_NO;?></label><br />
					</td>
				</tr>     
				
				
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_PURCHASELOGEMAIL;?>:
					</th>
					<td>
					<input class='text' name='wpsc_options[purch_log_email]' type='text' size='40' value='<?php echo get_option('purch_log_email'); ?>' />
					</td>
				</tr>
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_REPLYEMAIL;?>:
					</th>
					<td>
					<input class='text' name='wpsc_options[return_email]' type='text' size='40' value='<?php echo get_option('return_email'); ?>'  />
					</td>
				</tr>
				<tr>
					<th scope="row">
					<?php echo TXT_WPSC_TERMS2;?>:
					</th>
					<td>
					<textarea name='wpsc_options[terms_and_conditions]' cols='' rows='' style='width: 100%; height: 200px;'><?php echo stripslashes(get_option('terms_and_conditions')); ?></textarea>
					</td>
				</tr>
	
			</table> 
			<h3 class="form_group"><?php echo TXT_WPSC_EMAIL_SETTINGS;?>:</h3>
			<table class='wpsc_options form-table'>
				<tr>
					<th colspan="2"><?php echo TXT_WPSC_TAGS_CAN_BE_USED;?>: %shop_name%,<!-- %order_status%,--> %product_list%, %total_price%, %total_shipping%</th>
				</tr>
				<tr>
					<th><?php echo TXT_WPSC_PURCHASERECEIPT;?></th>
					<td><textarea name="wpsc_options[wpsc_email_receipt]" cols='' rows=''   style='width: 100%; height: 200px;'><?php echo get_option('wpsc_email_receipt');?></textarea></td>
				</tr>
				<tr>
					<th><?php echo TXT_WPSC_ADMIN_REPORT;?></th>
					<td><textarea name="wpsc_options[wpsc_email_admin]" cols='' rows='' style='width: 100%; height: 200px;'><?php echo get_option('wpsc_email_admin');?></textarea></td>
				</tr>
			</table>
			<h3 class="form_group"><?php echo TXT_WPSC_URLSETTINGS;?>:</h3>
			<table class='wpsc_options form-table'>
			
				<tr class='merged'>
					<th scope="row">
					<?php echo TXT_WPSC_PRODUCTLISTURL;?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('product_list_url'); ?>' name='wpsc_options[product_list_url]' />
					</td>
				</tr>
				<tr class='merged'>
					<th scope="row">
					<?php echo TXT_WPSC_SHOPPINGCARTURL;?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('shopping_cart_url'); ?>' name='wpsc_options[shopping_cart_url]' />
					</td>
				</tr>
				<?php /*
				<tr class='merged'>
					<th scope="row">
					<?php echo TXT_WPSC_CHECKOUTURL;?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('checkout_url'); ?>' name='checkout_url' />
					</td>
				</tr>*/
				?>
				<tr class='merged'>
					<th scope="row">
					<?php echo TXT_WPSC_TRANSACTIONDETAILSURL;?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('transact_url'); ?>' name='wpsc_options[transact_url]' />
					</td>
				</tr>
			<?php
			if(function_exists("nzshpcrt_user_log")) {
			?>
				<tr class='merged'>
					<th scope="row">
					<?php echo TXT_WPSC_USERACCOUNTURL;?>:
					</th>
					<td>
					<input class='text' type='text' size='50' value='<?php echo get_option('user_account_url'); ?>' name='wpsc_options[user_account_url]' />
					</td>
				</tr>
			<?php
			}
			?>
				<tr class='merged'>
					<td colspan="2"><a href='<?php echo wp_nonce_url("page.php?wpsc_admin_action=update_page_urls"); ?>' ><?php echo TXT_WPSC_UPDATE_PAGE_URLS; ?></a> &nbsp; | &nbsp;
					<a href='<?php echo wp_nonce_url("page.php?wpsc_admin_action=clean_categories"); ?>'><?php echo TXT_WPSC_FIX_CATEGORY_PERMALINKS; ?></a>
					</td>
				</tr>
			</table>					  
		<?php
		/* here end the admin options */						  
	  ?>
		<div class="submit">
			<input type='hidden' name='wpsc_admin_action' value='submit_options' /> 
			<input type="submit" value="<?php echo TXT_WPSC_UPDATE_BUTTON;?>" name="updateoption"/>
       </div>
   </div>
</form>
						
<?php						
}					
						
						


?>