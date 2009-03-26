// This is the wp-e-commerce front end javascript "library"
//jQuery.noConflict();
// this function is for binding actions to events and rebinding them after they are replaced by AJAX



 function wpsc_bind_to_events() {
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
});



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


function submit_change_country()
  {
  document.forms.change_country.submit();
  }