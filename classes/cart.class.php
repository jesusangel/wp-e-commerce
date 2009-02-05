<?php
class cart_handler {
	function cart_handler() {
		// look at line 774
		// look at line 1734
	}
}

class cart_item {
	var $product_id;
	var $product_variations;
	var $quantity;
	var $donation_price;
	var $extras;
	var $file_data;
	var $comment;
	var $time_requested;
	var $meta;
	
	function cart_item($product_id,$variations = null,$quantity = 1, $donation_price = null,$extras=null,$comment=null,$time_requested=null,$meta=null) {
		$this->product_id = (int)$product_id;
		$this->quantity = (int)$quantity;
		$this->extras = $extras;
		if(is_array($variations)) {
			$this->product_variations = $variations;
		}
		$this->donation_price = (float)$donation_price;
		$this->file_data = null;
		$this->comment = $comment;
		$this->time_requested = $time_requested;
		$this->meta = $meta;
	}

	function update_item($quantity) {
		$this->quantity = (int)$quantity;
	}
  
	function empty_item() {
		unset($this->product_id);
		unset($this->quantity);
		unset($this->product_variations);
		unset($this->donation_price);
		unset($this->file_data);
		unset($this->comment);
		unset($this->time_requested);
		unset($this->meta);
	}
}
?>
