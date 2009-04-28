// This is the wp-e-commerce front end javascript "library"

jQuery(window).load( function () {
  jQuery('table#wpsc_product_list').sortable({
		update: function(event, ui) {
		category_id = jQuery('input#products_page_category_id').val();
		
 		product_order = jQuery('table#wpsc_product_list').sortable( 'serialize');
 		post_values = "category_id="+category_id+"&"+product_order;
		jQuery.post( 'index.php?wpsc_admin_action=save_product_order', post_values, function(returned_data) {
			
		});
		},
    items: 'tr.product-edit',
    axis: 'y',
    containment: 'table#wpsc_product_list',
    placeholder: 'product-placeholder'
  });
});