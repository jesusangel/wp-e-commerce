// This is the wp-e-commerce front end javascript "library"

jQuery(document).ready( function () {

  // this makes the product list table sortable
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
   
	// this helps show the links in the product list table, it is partially done using CSS, but that breaks in IE6
	jQuery("tr.product-edit").hover(
		function() {
			jQuery(".wpsc-row-actions", this).css("visibility", "visible");
		},	
		function() {
			jQuery(".wpsc-row-actions", this).css("visibility", "hidden");
		}
	);
	
  // this changes the purchase log item status
	 jQuery('.selector').change(function(){	
			purchlog_id = jQuery(this).attr('title');
	 		purchlog_status = jQuery(this).val();
	 		post_values = "purchlog_id="+purchlog_id+"&purchlog_status="+purchlog_status;
			jQuery.post( 'index.php?wpsc_admin_action=purchlog_edit_status', post_values, function(returned_data) { });
	 });
	
	jQuery("div.admin_product_name a.shorttag_toggle").toggle(
		function () {
			jQuery("div.admin_product_shorttags", jQuery(this).parent("div.admin_product_name")).css('display', 'block');
			return false;
		},
		function () {
			//jQuery("div#admin_product_name a.shorttag_toggle").toggleClass('toggled');
			jQuery("div.admin_product_shorttags", jQuery(this).parent("div.admin_product_name")).css('display', 'none');
			return false;
		}
	);
	
	jQuery("#add-product-image").click(function(){
		swfu.selectFiles();
	});
	
	// start off the gallery_list sortable
	/*
	jQuery("#gallery_list").sortable({
		revert: false,
		placeholder: "ui-selected",
		start: function(e,ui) {
			jQuery('#image_settings_box').hide();
			jQuery('a.editButton').hide();
			jQuery('img.deleteButton').hide();
			jQuery('ul#gallery_list').children('li').removeClass('first');
		},
		stop:function (e,ui) {
			jQuery('ul#gallery_list').children('li:first').addClass('first');
		},
		update: function (e,ui){
					set = jQuery("#gallery_list").sortable('toArray');
					img_id = jQuery('#gallery_image_'+set[0]).parent('li').attr('id');
					
					jQuery('#gallery_image_'+set[0]).children('img.deleteButton').remove();
					jQuery('#gallery_image_'+set[0]).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
					jQuery('#gallery_image_'+set[0]).parent('li').attr('id', 0);
					//for(i=1;i<set.length;i++) {
					//	jQuery('#gallery_image_'+set[i]).children('a.editButton').remove();
					//	jQuery('#gallery_image_'+set[i]).append("<img alt='-' class='deleteButton' src='"+WPSC_URL+"/images/cross.png'/>");
					//}
					
					for(i=1;i<set.length;i++) {
						jQuery('#gallery_image_'+set[i]).children('a.editButton').remove();
						jQuery('#gallery_image_'+set[i]).append("<img alt='-' class='deleteButton' src='"+WPSC_URL+"/images/cross.png'/>");
						
									element_id = jQuery('#gallery_image_'+set[i]).parent('li').attr('id');
						if(element_id == 0) {
										jQuery('#gallery_image_'+set[i]).parent('li').attr('id', img_id);
						}
					}
					
					order = set.join(',');
					prodid = jQuery('#prodid').val();
					ajax.post("index.php",imageorderresults,"admin=true&ajax=true&prodid="+prodid+"&imageorder=true&order="+order);
				},
		'opacity':0.5
	});*/
	
	// hover for gallery view
	jQuery("div.previewimage").hover(
		function () {
			jQuery(this).children('img.deleteButton').show();
			if(jQuery('#image_settings_box').css('display')!='block')
				jQuery(this).children('a.editButton').show();
		},
		function () {
			jQuery(this).children('img.deleteButton').hide();
			jQuery(this).children('a.editButton').hide();
		}
	);
	// display image editing menu
	jQuery("a.editButton").click(
		function(){
			jQuery(this).hide();
			jQuery('#image_settings_box').show('fast');
		}
	);
	// hide image editing menu
	jQuery("a.closeimagesettings").click(
		function (e) {
			jQuery("div#image_settings_box").hide();
		}
	);
	
});


// function for switching the state of the image upload forms
function wpsc_upload_switcher(target_state) {
  switch(target_state) {
    case 'flash':
    jQuery("table.browser-image-uploader").css("display","none");
    jQuery("table.flash-image-uploader").css("display","block");
    jQuery.post( 'index.php?admin=true', "admin=true&ajax=true&save_image_upload_state=true&image_upload_state=1", function(returned_data) { });
    break;
    
    case 'browser':
    jQuery("table.flash-image-uploader").css("display","none");
    jQuery("table.browser-image-uploader").css("display","block");
    jQuery.post( 'index.php?admin=true', "admin=true&ajax=true&save_image_upload_state=true&image_upload_state=0", function(returned_data) { });
    break;
  }
}

// function for switching the state of the extra resize forms
function image_resize_extra_forms(option) {
	container = jQuery(option).parent();
	jQuery("div.image_resize_extra_forms").css('display', 'none');
	jQuery("div.image_resize_extra_forms",container).css('display', 'block');
}


var prevElement = null;
var prevOption = null;

function hideOptionElement(id, option) {
	if (prevOption == option) {
		return;
	}
	if (prevElement != null) {
		prevElement.style.display = "none";
	}
  
	if (id == null) {
		prevElement = null;
	} else {
		prevElement = document.getElementById(id);
		jQuery('#'+id).css( 'display','block');
	}
	prevOption = option;
}

