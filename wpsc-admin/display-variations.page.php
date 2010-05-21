<?php
/**
 * WP eCommerce edit and add variation group page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_display_variations_page() {
	$columns = array(
		'title' => __('Name', 'wpsc'),
		'edit' => __('Edit', 'wpsc')
	);
	register_column_headers('display-variations-list', $columns);	
	
	?>
	<script language='javascript' type='text/javascript'>
		function conf() {
			var check = confirm("<?php echo __('Are you sure you want to delete this product?', 'wpsc');?>");
			if(check) {
				return true;
			} else {
				return false;
			}
		}
		
		<?php

		?>
	</script><noscript>
	</noscript>
	
	<div class="wrap">
		<?php// screen_icon(); ?>
		<h2><?php echo wp_specialchars( __('Display Variations', 'wpsc') ); ?> </h2>
		<p>	
				<?php echo __('A variation can be anything "optional" about a product. ie: Size, Color, etc <br />For example: if you are selling t-shirts you might setup a variation set called size with the values small, medium, large...', 'wpsc');?>
		</p>
  
		
		<?php if (isset($_GET['deleted']) || isset($_GET['message'])) { ?>
			<div id="message" class="updated fade">
				<p>
				<?php		
				if (isset($_GET['message']) ) {
					$message = absint( $_GET['message'] );
					$messages[1] =  __( 'Product updated.', 'wpsc');
					echo $messages[$message];
					unset($_GET['message']);
				}
				
				$_SERVER['REQUEST_URI'] = remove_query_arg( array('deleted', 'message'), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
		<?php } ?>
				
		<div id="col-container" class=''>
			<div id="col-right">			
				<div id='poststuff' class="col-wrap">
					<form id="modify-variation-groups" method="post" action="" enctype="multipart/form-data" >
					<?php
						//$product_id = absint($_GET['product_id']);
						//wpsc_display_product_form($product_id);
						wpsc_admin_variation_forms($_GET['variation_id']);
					?>
					</form>
				</div>
			</div>
			
			<div id="col-left">
				<div class="col-wrap">		
					<?php
						wpsc_admin_variation_group_list($category_id);
					?>
				</div>
			</div>
		</div>
				
				
	</div>
	<?php
}


/*
 * wpsc_admin_variation_group_list, prints the left hand side of the edit variations page
 * no parameters
 * nothing returned
 */

function wpsc_admin_variation_group_list() {
  global $wpdb;
	?>
		<table class="widefat page" id='wpsc_variation_list' cellspacing="0">
			<thead>
				<tr>
					<?php print_column_headers('display-variations-list'); ?>
				</tr>
			</thead>
		
			<tfoot>
				<tr>
					<?php print_column_headers('display-variations-list', false); ?>
				</tr>
			</tfoot>
		
			<tbody>
				<?php
				$variations = get_terms('wpsc-variation', array(
					'hide_empty'=> 0,
					'parent' => 0
				));
				// echo "<pre>".print_r($variations,true)."</pre>";
				foreach((array)$variations as $variation) {
					?>
						<tr class="variation-edit" id="variation-<?php echo $variation->term_id; ?>">
								<td class="variation-name"><?php echo htmlentities($variation->name, ENT_QUOTES, 'UTF-8'); ?></td>
								<td class="edit-variation">
								<a href='<?php echo add_query_arg('variation_id', $variation->term_id); ?>'><?php echo __('Edit', 'wpsc'); ?></a>
								</td>
						</tr>
					<?php
				}
				?>			
			</tbody>
		</table>
		<?php
}


/*
 * wpsc_admin_variation_group_list, prints the right hand side of the edit variations page
 * @param int $variation_id the variation ID
 * nothing returned
 */
function wpsc_admin_variation_forms($variation_id =  null) {
	global $wpdb;
	$variation_value_count = 0;
	$variation_name = '';
	if($variation_id > 0 ) {
		$variation_id = absint($variation_id);
		
		//$variation_name = $wpdb->get_var("SELECT `name` FROM `".WPSC_TABLE_PRODUCT_VARIATIONS."` WHERE `id`='$variation_id' LIMIT 1");
		//$variation_values = $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_VARIATION_VALUES."` WHERE `variation_id`='$variation_id' ORDER BY `id` ASC",ARRAY_A);
		
		$variation_set = get_term($variation_id, 'wpsc-variation', ARRAY_A);
		
		$variations = get_terms('wpsc-variation', array(
			'hide_empty' => 0,
			'parent' => $variation_id
		));
		//echo "<pre>".print_r($variations,true)."</pre>";
		//exit();
		$variation_count = count($variations);
	}
	if(($_GET['valuecount'] > 0)) { 
		$value_form_count = absint($_GET['valuecount']);
	} else {
		$value_form_count = 3;
		remove_query_arg( array('valuecount'), $_SERVER['REQUEST_URI'] );
	}
	if($variation_name != '') {
		?>
		<h3><?php echo __('Edit Variation Set', 'wpsc'); ?><span> (<a href="admin.php?page=wpsc-edit-variations">Add new Variation Set</a>)</span></h3>
		<?php
	} else {
		?>
		<h3><?php echo __('Add Variation Set', 'wpsc'); ?></h3>
		<?php 
	}
	?>
	<table class='category_forms'>
		<tr>
			<td>
				<?php echo __('Name', 'wpsc'); ?>:
			</td>
			<td>
				<input type='text'  class="text" name='name' value='<?php echo $variation_set['name']; ?>' />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('Variation Values', 'wpsc'); ?>:
			</td>
			<td>
				<div id='variation_values'>
					<?php 
						if($variation_count > 0) {
							$num = 0;
							foreach($variations as $variation) {
								$delete_url = wp_nonce_url("admin.php?wpsc_admin_action=wpsc-variation-set&amp;delete_value=true&amp;variation_id={$variation_id}&amp;value_id={$variation->term_id}", "delete-variation-{$variation->term_id}");
								?>
								<div class='variation_value' id='variation_row_<?php echo $variation->term_id; ?>'>
								<a class='variation_handle' href="#">&equiv;</a>
								<input type='text' class='text' name='variation_values[<?php echo $variation->term_id; ?>]' value='<?php echo htmlentities($variation->name, ENT_QUOTES, 'UTF-8'); ?>' />
								<input type='hidden' class='variation_values_id' name='variation_values_id[]' value='<?php echo $variation->term_id; ?>' />
								<?php if($variation_count > 1): ?>
									<a class='image_link delete_variation_value' href='<?php echo $delete_url; ?>' >
									  <img src='<?php echo WPSC_URL; ?>/images/trash.gif' alt='<?php echo __('Delete', 'wpsc'); ?>' title='<?php echo __('Delete', 'wpsc'); ?>' />
									</a>
								<?php endif; ?>
								</div>
								<?php
								$num++;
							}
						} else {
							for($i = 0; $i <= $value_form_count; $i++) {
								?>
								<div class='variation_value'>
									<input type='text' class="text" name='new_variation_values[]' value='' />
										<a class='image_link delete_variation_value' href='#'>
											<img src='<?php echo WPSC_URL; ?>/images/trash.gif' alt='<?php echo __('Delete', 'wpsc'); ?>' title='<?php echo __('Delete', 'wpsc'); ?>' />
										</a>
								</div>
								<?php 
							}
					}
				?>
				</div>
				<a href='#' class='add_variation_item_form'>+ <?php _e('Add Value'); ?></a>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				<?php wp_nonce_field('edit-variation', 'wpsc-edit-variation'); ?>
		        <input type='hidden' name='wpsc_admin_action' value='wpsc-variation-set' />
				
				<?php if($variation_id > 0) { ?>
					<?php
					$nonced_url = wp_nonce_url("admin.php?wpsc_admin_action=wpsc-delete-variation-set&amp;deleteid={$variation_id}", 'delete-variation');
					?>
					<input type='hidden' name='variation_id' value='<?php echo $variation_id; ?>' />
					<input type='hidden' name='submit_action' value='edit' />
					<input class='button' style='float:left;'  type='submit' name='submit' value='<?php echo __('Edit', 'wpsc'); ?>' />
					<a class='button delete_button' href='<?php echo $nonced_url; ?>' onclick="return conf();" ><?php echo __('Delete', 'wpsc'); ?></a>
				<?php } else { ?>
					<input type='hidden' name='submit_action' value='add' />
					<input class='button'  type='submit' name='submit' value='<?php echo __('Add', 'wpsc');?>' />
				<?php } ?>    
			</td>
		</tr>
	</table>
  <?php
}


?>