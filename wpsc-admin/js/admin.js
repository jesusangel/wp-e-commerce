// This is the wp-e-commerce front end javascript "library"

jQuery(window).load( function () {
  jQuery('table#wpsc_product_list').sortable({
    items: 'tr.product-edit',
    axis: 'y'
  });
});