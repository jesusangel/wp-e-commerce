<?php
function wpsc_grid_title_and_price(&$product) {
	$output .= "<div class='grid_price'>";
	if ($soldout) {
		$output .= "<span class='soldoutgrid'>Sold out</span>";
	} else {
		if($product['special']==1) {
			$output .= nzshpcrt_currency_display(($product['price'] - $product['special_price']), $product['notax'],false,$product['id']) . "\n\r";
		} else {
			$output .= nzshpcrt_currency_display($product['price'], $product['notax']) . "\n\r";
		}
	}
	$output .= "</div>";
	$output .= "<div class='grid_prodcut_title'><a href='".wpsc_product_url($product['id'])."'>".stripslashes($product['name'])."</a></div>";

  return $output;
}
?>