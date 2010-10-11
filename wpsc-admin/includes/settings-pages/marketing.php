<?php

function wpsc_options_marketing() {

	if ( isset( $_POST['change-settings'] ) && 'true' == $_POST['change-settings'] ) {

		if ( isset( $_POST['wpsc_also_bought'] ) && $_POST['wpsc_also_bought'] == 'on' )
			update_option( 'wpsc_also_bought', 1 );
		else
			update_option( 'wpsc_also_bought', 0 );

		if ( isset( $_POST['display_find_us'] ) && $_POST['display_find_us'] == 'on' )
			update_option( 'display_find_us', 1 );
		else
			update_option( 'display_find_us', 0 );

		if ( isset( $_POST['wpsc_share_this'] ) && $_POST['wpsc_share_this'] == 'on' )
			update_option( 'wpsc_share_this', 1 );
		else
			update_option( 'wpsc_share_this', 0 );

	}

?>

	<div class='metabox-holder'>

		<?php
			add_meta_box( 'wpsc_marketing_settings', __( 'Marketing Section', 'wpsc' ), 'wpsc_marketing_meta_box', 'wpsc' );
			add_meta_box( 'wpsc_rss_address', __( 'RSS Address', 'wpsc' ), 'wpsc_rss_address_meta_box', 'wpsc' );
			add_meta_box( 'wpsc_google_merch_center', __( 'Google Merchant Centre / Google Product Search', 'wpsc' ), 'wpsc_google_merch_center_meta_box', 'wpsc' );

			do_meta_boxes( 'wpsc', 'advanced', null );
		?>

	</div>

<?php

}

function wpsc_marketing_meta_box() {

	$wpsc_also_bought  = get_option( 'wpsc_also_bought' );
	$wpsc_also_bought1 = '';

	if ( '1' == $wpsc_also_bought )
		$wpsc_also_bought1 = "checked ='checked'";

	$wpsc_share_this  = get_option( 'wpsc_share_this' );
	$wpsc_share_this1 = '';

	if ( '1' == $wpsc_share_this )
		$wpsc_share_this1 = "checked ='checked'";

	$display_find_us  = get_option( 'display_find_us' );
	$display_find_us1 = '';

	if ( '1' == $display_find_us )
		$display_find_us1 = "checked ='checked'";

?>

	<form name='cart_options' method='post' action=''>
		<input type='hidden' value='true' name='change-settings' />
		<p><span class='input_label'><?php _e( 'Display Cross Sales', 'wpsc' ); ?></span><input <?php echo $wpsc_also_bought1; ?> type='checkbox' name='wpsc_also_bought' /><span class='description'>  <?php _e( 'Adds the \'Users who bought this also bought\' item to the single products page.', 'wpsc' ); ?></span></p><br />
		<p><span class='input_label'><?php _e( 'Show Share This (Social Bookmarks)', 'wpsc' ); ?></span><input <?php echo $wpsc_share_this1; ?> type='checkbox' name='wpsc_share_this' /><span class='description'>  <?php _e( 'Adds the \'Share this link\' item to the single products page.', 'wpsc' ); ?></span></p><br />
		<p><span class='input_label'> <?php _e( 'Display How Customer Found Us Survey', 'wpsc' ) ?></span><input <?php echo $display_find_us1; ?> type='checkbox' name='display_find_us' /><span class='description'>  <?php _e( 'Adds the \'How did you find out about us\' drop-down option at checkout.', 'wpsc' ) ?></span></p><br />
		<p><input  type='submit' class='button-primary' value='<?php _e( 'Submit', 'wpsc' ); ?>' name='form_submit' /></p>
	</form>

<?php
}

function wpsc_rss_address_meta_box() { ?>

	<p><?php _e( 'People can use this RSS feed to keep up to date with your product list.', 'wpsc' ); ?></p>
	<p><?php _e( 'RSS Feed Address', 'wpsc' ) ?> :	<?php echo get_bloginfo( 'url' ) . "/index.php?rss=true&amp;action=product_list"; ?></p>

<?php
}

function wpsc_google_merch_center_meta_box() { ?>

	<p><?php _e( 'To import your products into <a href="http://www.google.com/merchants/" target="_blank">Google Merchant Centre</a> so that they appear within Google Product Search results, sign up for a Google Merchant Centre account and add a scheduled data feed with the following URL:', 'wpsc' ); ?></p>

	<?php $google_feed_url = get_bloginfo( 'url' ) . "/index.php?rss=true&action=product_list&xmlformat=google"; ?>

	<a href="<?php echo htmlentities( $google_feed_url ); ?>"><?php echo htmlentities( $google_feed_url ); ?></a>

<?php
}

?>
