<?php

/**
 * This file handles the standard importing of products through a csv file upload. Access this page via WP-admin Settings>Import
 * @package WP e-Commerce
 */
function wpsc_options_import() {
	global $wpdb;
?>
	<form name='cart_options' enctype='multipart/form-data' id='cart_options' method='post' action='<?php echo 'admin.php?page=wpsc-settings&tab=import'; ?>'>
		<div class="wrap">
<?php echo __( '<p>You can import your products from a comma delimited text file.</p><p>An example of a cvs import file would look like this: </p><p>Description, Additional Description, Product Name, Price, SKU, weight, weight unit, stock quantity, is limited quantity</p>', 'wpsc' ); ?>

<?php wp_nonce_field( 'update-options', 'wpsc-update-options' ); ?>
		<input type='hidden' name='MAX_FILE_SIZE' value='5000000' />
		<input type='file' name='csv_file' />
		<input type='submit' value='Import' class='button-primary'>
<?php
//exit('<pre>'.print_r($_FILES, true).'</pre>');

		if ( isset( $_FILES['csv_file']['name'] ) && ($_FILES['csv_file']['name'] != '') ) {
			ini_set( "auto_detect_line_endings", 1 );
			$file = $_FILES['csv_file'];
			//exit('<pre>'.print_r($file,true).'</pre>');
			if ( move_uploaded_file( $file['tmp_name'], WPSC_FILE_DIR . $file['name'] ) ) {
				$content = file_get_contents( WPSC_FILE_DIR . $file['name'] );
				//exit('<pre>'.print_r(WPSC_FILE_DIR.$file['name'], true).'</pre>');
				$handle = @fopen( WPSC_FILE_DIR . $file['name'], 'r' );
				while ( ($csv_data = @fgetcsv( $handle, filesize( $handle ), "," )) !== false ) {
					$fields = count( $csv_data );
					for ( $i = 0; $i < $fields; $i++ ) {
						if ( !is_array( $data1[$i] ) ) {
							$data1[$i] = array( );
						}
						array_push( $data1[$i], $csv_data[$i] );
					}
				}

				$_SESSION['cvs_data'] = $data1;
				$categories = get_terms( 'wpsc_product_category', 'hide_empty=0&parent=' . $category_id );
?>

				<p>For each column, select the field it corresponds to in 'Belongs to'. You can upload as many products as you like.</p>
				<div class='metabox-holder' style='width:90%'>
					<input type='hidden' name='csv_action' value='import'>
<?php
				//	exit('<pre>'.print_r($_SESSION['cvs_data'], true).'</pre>');
				foreach ( (array)$data1 as $key => $datum ) {
?>
					<div style='width:100%;' class='postbox'>
						<h3 class='hndle'>Column (<?php echo $key + 1; ?>)</h3>
						<div class='inside'>
							<table>
								<tr><td style='width:80%;'>
										<input type='hidden' name='column[]' value='<?php echo $key + 1; ?>'>
								<?php
								foreach ( $datum as $column ) {
									echo $column;
									break;
								} ?>
								<br />
							</td><td>
								<select  name='value_name[]'>
									<!-- /* 		These are the current fields that can be imported with products, to add additional fields add more <option> to this dorpdown list */ -->
									<option value='name'>Product Name</option>
									<option value='description'>Description</option>
									<option value='additional_description'>Additional Description</option>
									<option value='price'>Price</option>
									<option value='sku'>SKU</option>
									<option value='weight'>Weight</option>
									<option value='weight_unit'>Weight Unit</option>
									<option value='quantity'>Stock Quantity</option>
									<option value='quantity_limited'>Stock Quantity Limit</option>
								</select>
							</td></tr>
					</table>
				</div>
			</div>
<?php } ?>
			<label for='category'>Please select a category you would like to place all products from this CSV into:</label>
			<select id='category' name='category'>
<?php
			foreach ( $categories as $category ) {
				echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
			}
?>
			</select>
			<input type='submit' value='Import' class='button-primary'>
		</div>
<?php
		} else {
			echo "<br /><br />There was an error while uploading your csv file.";
		}
	}
	if ( isset( $_POST['csv_action'] ) && ('import' == $_POST['csv_action']) ) {
		global $wpdb;

		$cvs_data = $_SESSION['cvs_data'];
		//exit('<pre>'.print_r($_SESSION['cvs_data'], true).'</pre>');
		$column_data = $_POST['column'];
		$value_data = $_POST['value_name'];
		$name = array( );
		foreach ( $value_data as $key => $value ) {

			$cvs_data2[$value] = $cvs_data[$key];
		}
		$num = count( $cvs_data2['name'] );

		for ( $i = 0; $i < $num; $i++ ) {
			$product_columns = array(
				'post_title' => $cvs_data2['name'][$i],
				'content' => $cvs_data2['description'][$i],
				'additional_description' => $cvs_data2['additional_description'][$i],
				'price' => str_replace( '$', '', $cvs_data2['price'][$i] ),
				'weight' => $cvs_data2['weight'][$i],
				'weight_unit' => $cvs_data2['weight_unit'][$i],
				'pnp' => null,
				'international_pnp' => null,
				'file' => null,
				'image' => '0',
				'quantity_limited' => $cvs_data2['quantity_limited'][$i],
				'quantity' => $cvs_data2['quantity'][$i],
				'special' => null,
				'special_price' => null,
				'display_frontpage' => null,
				'notax' => null,
				'publish' => null,
				'active' => null,
				'donation' => null,
				'no_shipping' => null,
				'thumbnail_image' => null,
				'thumbnail_state' => null,
				'category' => array(
					esc_html__( $_POST['category'] )
				),
				'meta' => array(
					'_wpsc_price' => str_replace( '$', '', $cvs_data2['price'][$i] ),
					'_wpsc_sku' => $cvs_data2['sku'][$i],
					'_wpsc_stock' => $cvs_data2['quantity'][$i],
					'_wpsc_product_metadata' => array(
						'weight' => $cvs_data2['weight'][$i],
						'weight_unit' => $cvs_data2['weight_unit'][$i],
					)
				)
			);

			$product_columns = wpsc_sanitise_product_forms( $product_columns );
			wpsc_insert_product( $product_columns );
		}
		echo "<br /><br />Success, your <a href='?page=wpsc-edit-products'>products</a> have been upload.";
	}
?>
		</div>
	</form>
<?php
}
?>
