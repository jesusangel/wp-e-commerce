// This is the wp-e-commerce front end javascript "library"

jQuery(document).ready( function () {
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
  
		 jQuery('.selector').change(function(){	
			purchlog_id = jQuery(this).attr('title');
	 		purchlog_status = jQuery(this).val();
	 		post_values = "purchlog_id="+purchlog_id+"&purchlog_status="+purchlog_status;
			jQuery.post( 'index.php?wpsc_admin_action=purchlog_edit_status', post_values, function(returned_data) {
	
	 		});
	 });
	 
	 jQuery('#view_purchlogs_by').change(function(){
	 		purchlog_date = jQuery(this).val();
	 		post_values = "purchlog_date="+purchlog_date;
			jQuery.post( 'index.php?wpsc_admin_action=purchlog_view_by', post_values, function(returned_data) {
	
	 		});
	 });
	
//  	if (typeof tinyMCE != "undefined") {
// 		tinyMCE.init({
// 			theme : "advanced",
// 			mode : "specific_textareas",
// 			width : '100%',
// 			height : '194px',
// 			skin : 'wp_theme',
// 			editor_selector : "mceEditor",
// 			plugins : "spellchecker,pagebreak",
// 			theme_advanced_buttons1 : "bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,pagebreak",
// 			theme_advanced_buttons2 : "",
// 			theme_advanced_buttons3 : "",
// 			theme_advanced_toolbar_location : "top",
// 			theme_advanced_toolbar_align : "left",
// 			theme_advanced_statusbar_location : "bottom",
// 			theme_advanced_resizing : true,
// 			content_css : WPSC_URL+"/js/tinymce3/mce.css",
// 			theme_advanced_resize_horizontal : false
// 		});
//   }
});
