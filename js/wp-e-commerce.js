// This is the wp-e-commerce front end javascript "library"
//jQuery.noConflict();
// this function is for binding actions to events and rebinding them after they are replaced by AJAX


// these functions are bound to events on elements when the page is fully loaded.
function wpsc_bind_to_events() {
	// empty the cart using ajax when the form is submitted,  
	jQuery("form.wpsc_empty_the_cart").submit(function() {
		form_values = "ajax=true&";
		form_values += jQuery(this).serialize( );
		jQuery.post( 'index.php', form_values, function(returned_data) {
			eval(returned_data);
			wpsc_bind_to_events();
		});
		return false;
	});
}    
    

	

jQuery(document).ready(function () {
  wpsc_bind_to_events();
	
	// Submit the product form using AJAX
  jQuery("form.product_form").submit(function() {
		form_values = jQuery(this).serialize( );
		jQuery.post( 'index.php?ajax=true', form_values, function(returned_data) {
			eval(returned_data);
			wpsc_bind_to_events();
			if(jQuery('#fancy_notification') != null) {
				jQuery('#loading_animation').css("display", 'none');
				//jQuery('#fancy_notificationimage').css("display", 'none');
			}
			
		});
		wpsc_fancy_notification(this);
		
		return false;
	});
  
  
  // Toggle the additional description content  
  jQuery("a.additional_description_link").click(function() {
    parent_element = jQuery(this).parent('.additional_description_span');
    jQuery('.additional_description',parent_element).toggle();
		return false;
	});
});


// update the totals when shipping methods are changed.
function switchmethod(key,key1){
// 	total=document.getElementById("shopping_cart_total_price").value;
	form_values = "ajax=true&";
	form_values += "wpsc_ajax_action=update_shipping_price&";
	form_values += "key1="+key1+"&";
	form_values += "key="+key;
	
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
	});
}

// submit the country forms.
function submit_change_country(){
  document.forms.change_country.submit();
}

// submit the country forms.
function wpsc_fancy_notification(parent_form){
  if(typeof(WPSC_SHOW_FANCY_NOTIFICATION) == 'undefined'){
    WPSC_SHOW_FANCY_NOTIFICATION = true;
	}
	if((WPSC_SHOW_FANCY_NOTIFICATION == true) && (jQuery('#fancy_notification') != null)){
    var options = {
      margin: 1 ,
      border: 1 ,
      padding: 1 ,
      scroll: 1 
      };

    form_button_id = jQuery(parent_form).attr('id') + "_submit_button";
    //console.log(form_button_id);
    //return;
    var container_offset = {};
    new_container_offset = jQuery('#products_page_container').offset(options, container_offset);
    
		if(container_offset['left'] == null) {
      container_offset['left'] = new_container_offset.left;
      container_offset['top'] = new_container_offset.top;
    }    

    var button_offset = {};
    new_button_offset = jQuery('#'+form_button_id).offset(options, button_offset)
    
    if(button_offset['left'] == null) {
      button_offset['left'] = new_button_offset.left;
      button_offset['top'] = new_button_offset.top;
    }
        
    jQuery('#fancy_notification').css("left", (button_offset['left'] - container_offset['left'] + 10) + 'px');
    jQuery('#fancy_notification').css("top", ((button_offset['top']  - container_offset['top']) -60) + 'px');
       
    
    jQuery('#fancy_notification').css("display", 'block');
    jQuery('#loading_animation').css("display", 'block');
    jQuery('#fancy_notification_content').css("display", 'none');  
	}
}