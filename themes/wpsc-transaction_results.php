<?php
	/**
	 * The Transaction Results Theme.
	 *
	 * Displays everything within transaction results.  Hopefully much more useable than the previous implementation.
	 *
	 * @package WPSC
	 * @since WPSC 3.8
	 */

global $wpsc_query, $wpdb, $transaction_theme;

?>


<?php 
	if($transaction_theme->check_paypal_ipn()) : 
?>

	<?php
		_e('Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc') . "<p style='margin: 1em 0px 0px 0px;' >".nl2br(stripslashes(get_option('payment_instructions')))."</p>"
	?>

<?php
	else :
?>
	<?php 
		_e('We&#39;re Sorry, your order has not been accepted, the most likely reason is that you have insufficient funds.', 'wpsc');
	?>

<?php 
	endif;
?>