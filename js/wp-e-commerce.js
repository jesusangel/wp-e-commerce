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