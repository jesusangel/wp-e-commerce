<?php
class cart_handler
  {
  function cart_handler()
    {
    // look at line 774
    // look at line 1734
    }
  }
  
class cart_item
  {
  var $product_id;
  var $product_variations;
  var $quantity;
  var $donation_price;
    
  function cart_item($product_id,$variations = null,$quantity = 1, $donation_price = null)
    {
    $this->product_id = filter_input_wp($product_id);
    $this->quantity = filter_input_wp($quantity);
    if(is_array($variations))
      {
      $this->product_variations = $variations;
      }
    $this->donation_price = $donation_price; 
    }
  
  function update_item($quantity)
    {
    $this->quantity = filter_input_wp($quantity);
    }
  
  function empty_item()
    {
    unset($this->product_id);
    unset($this->quantity);
    unset($this->product_variations); 
    unset($this->donation_price); 
    }
  }
?>
