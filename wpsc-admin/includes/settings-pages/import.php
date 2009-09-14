<?php
function wpsc_options_import(){
global $wpdb;
?>
	<form name='cart_options' enctype='multipart/form-data' id='cart_options' method='post' action='<?php echo 'admin.php?page=wpsc-settings&tab=import'; ?>'>
	<div class="wrap">
		<h2><?php echo TXT_WPSC_IMPORT_CSV;?></h2>
		<?php echo TXT_WPSC_IMPORT_CSV_DESCRIPTION;?>
	
		<input type='hidden' name='MAX_FILE_SIZE' value='5000000' />
		<input type='file' name='csv_file' />
		<input type='submit' value='Import' class='button-primary'>
<?php
//exit('<pre>'.print_r($_FILES, true).'</pre>');
if ($_FILES['csv_file']['name'] != '') {

	$file = $_FILES['csv_file'];
	//exit('<pre>'.print_r($file,true).'</pre>');
	if(move_uploaded_file($file['tmp_name'],WPSC_FILE_DIR.$file['name'])){
		$content = file_get_contents(WPSC_FILE_DIR.$file['name']);
		//exit('<pre>'.print_r(WPSC_FILE_DIR.$file['name'], true).'</pre>');
		$handle = @fopen(WPSC_FILE_DIR.$file['name'], 'r');
		while (($csv_data = @fgetcsv($handle, filesize($handle), ",")) !== false) {
			$fields = count($csv_data);
			for ($i=0;$i<$fields;$i++) {
				if (!is_array($data1[$i])){
					$data1[$i] = array();
				}
				array_push($data1[$i], $csv_data[$i]);
			}
		}
		//exit("<pre>".print_r($data1, 1)."</pre>");
		$_SESSION['cvs_data'] = $data1;
		?>
		<p>For each column, select the field it corresponds to in 'Belongs to'. You can upload as many products as you like.</p>
		<div class='metabox-holder' style='width:90%'>
		<input type='hidden' name='csv_action' value='import'>
		<?php
	//	exit('<pre>'.print_r($_SESSION['cvs_data'], true).'</pre>');
		foreach ((array)$data1 as $key => $datum) {
		?>
			<div style='width:100%;' class='postbox'>
			<h3 class='hndle'>Column (<?php echo $key+1; ?>)</h3>
			<div class='inside'>
			<table>
			<tr><td style='width:80%;'>
			<input type='hidden' name='column[]' value='<?php echo $key+1; ?>'>
			<?php
			foreach ($datum as $column) {
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
			<?php
		}
		?>
		<input type='submit' value='Import' class='button-primary'>
		</div>
	<?php
	}else{
	echo "<br /><br />There was an error while uploading your csv file.";

	}
}
if($_POST['csv_action'] == 'import'){
	global $wpdb;

	$cvs_data = $_SESSION['cvs_data'];
	//exit('<pre>'.print_r($_SESSION['cvs_data'], true).'</pre>');
	$column_data = $_POST['column'];
	$value_data = $_POST['value_name'];
	$name = array();
/*
	foreach ($value_data as $key => $value) {
		$value_data[$key] = $cvs_data[$key];
	}
*/
	//echo('<pre>'.print_r($value_data, true).'</pre><pre>'.print_r($column_data, true).'</pre>');
	foreach ($value_data as $key => $value) {

			$cvs_data2[$value] = $cvs_data[$key];

	}
	//exit('<pre>'.print_r($cvs_data2, true).'</pre>');
	$num = count($cvs_data2['name']);
	
	for($i =0; $i < $num; $i++){
		
		 $cvs_data2['price'][$i] = str_replace('$','',$cvs_data2['price'][$i]);
		//exit( $cvs_data2['price'][$i]);
		
	//	exit($key. ' ' . print_r($data));		
		$query = "('".$cvs_data2['name'][$i]."', '".$cvs_data2['description'][$i]."', '".$cvs_data2['additional_description'][$i]."','".$cvs_data2['price'][$i]."','".$cvs_data2['weight'][$i]."','".$cvs_data2['weight_unit'][$i]."','".$cvs_data2['quantity'][$i]."','".$cvs_data2['quantity_limited'][$i]."')";
		$query = "INSERT INTO `".WPSC_TABLE_PRODUCT_LIST."` (name, description, additional_description, price, weight, weight_unit, quantity, quantity_limited) VALUES ".$query;
	//	echo($query);
		$wpdb->query($query);
		$id = $wpdb->get_var("SELECT LAST_INSERT_ID() as id FROM `".WPSC_TABLE_PRODUCT_LIST."`");
		$meta_query = "INSERT INTO `".WPSC_TABLE_PRODUCTMETA."` VALUES ('', '$id', 'sku', '".$cvs_data2['sku'][$i]."', '0')";
		$wpdb->query($meta_query);
		$category_query = "INSERT INTO `".WPSC_TABLE_ITEM_CATEGORY_ASSOC."` VALUES ('','{$id}','1')";
		$wpdb->query($category_query);
		}
	
/* 	$query = "INSERT INTO {$wpdb->prefix}product_list (name, description, addictional_description, price) VALUES ".$query; */
	echo "<br /><br />Success, your <a href='?page=wpsc-edit-products'>products</a> have been upload.";
		
}

?>	</div>
</form>
<?php

}
?>