// This is the wp-e-commerce front end javascript "library"

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
    // we cannot submit a file through AJAX, so this needs to return true to submit the form normally if a file formfield is present
    file_upload_elements = jQuery.makeArray(jQuery('input[type=file]', jQuery(this)));
		if(file_upload_elements.length > 0) {
			return true;
		} else {
			form_values = jQuery(this).serialize();
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
		}
	});
  
  
  //  this is for storing data with the product image, like the product ID, for things like dropshop and the the ike.
  jQuery("form.product_form").load( function() {
    product_id = jQuery('input[name=product_id]',this).val();
    image_element_id = 'product_image_'+product_id;
    jQuery("#"+image_element_id).data("product_id", product_id);
  });
  jQuery("form.product_form").trigger('load');
  
  // Toggle the additional description content  
  jQuery("a.additional_description_link").click(function() {
    parent_element = jQuery(this).parent('.additional_description_span');
    jQuery('.additional_description',parent_element).toggle();
		return false;
	});
	
	
  // update the price when the variations are altered.
  jQuery("div.wpsc_variation_forms .wpsc_select_variation").change(function() {
    parent_form = jQuery(this).parents("form.product_form");
    form_values =jQuery("input[name=product_id],div.wpsc_variation_forms .wpsc_select_variation",parent_form).serialize( );
		jQuery.post( 'index.php?update_product_price=true', form_values, function(returned_data) {
			eval(returned_data);
      if(product_id != null) {
        target_id = "product_price_"+product_id;
				buynow_id = "BB_BuyButtonForm"+product_id;
				//document.getElementById(target_id).firstChild.innerHTML = price;			
				if(jQuery("input#"+target_id).attr('type') == 'text') {
				  jQuery("input#"+target_id).val(numeric_price);
				} else {
				  jQuery("#"+target_id+" span.pricedisplay").html(price);
				}
			}
		});
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

// submit the fancy notifications forms.
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


  
function set_billing_country(html_form_id, form_id){
  var billing_region = '';
  country = jQuery(("div#"+html_form_id+" select[@class=current_country]")).val();
  region = jQuery(("div#"+html_form_id+" select[@class=current_region]")).val();
  if(/[\d]{1,}/.test(region)) {
    billing_region = "&billing_region="+region;
	}
	
	form_values = "wpsc_ajax_action=change_tax&form_id="+form_id+"&billing_country="+country+billing_region;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
	});
  //ajax.post("index.php",changetaxntotal,("ajax=true&form_id="+form_id+"&billing_country="+country+billing_region));
}
