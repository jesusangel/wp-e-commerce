// This is the wp-e-commerce front end javascript "library"

jQuery(document).ready( function () {

  // this makes the product list table sortable
  jQuery('table#wpsc_product_list').sortable({
		update: function(event, ui) {
			category_id = jQuery('input#products_page_category_id').val();
			
			product_order = jQuery('table#wpsc_product_list').sortable( 'serialize');
			post_values = "category_id="+category_id+"&"+product_order;
			jQuery.post( 'index.php?wpsc_admin_action=save_product_order', post_values, function(returned_data) { });
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
	jQuery('#poststuff .postbox h3').click( function() {
		jQuery(jQuery(this).parent('div.postbox')).toggleClass('closed');
			if(jQuery(jQuery(this).parent('div.postbox')).hasClass('closed')) {
				jQuery('a.togbox',this).html('+');
			} else {
				jQuery('a.togbox',this).html('&ndash;');
			}
			wpsc_save_postboxes_state('products_page_edit-products', '#poststuff');
	});


	jQuery("#add-product-image").click(function(){
		swfu.selectFiles();
	});
	
	jQuery('.hide-postbox-tog').click( function() {
		var box = jQuery(this).val();
		if ( jQuery(this).attr('checked') ) {
			jQuery('#' + box).show();
			if ( jQuery.isFunction( postboxes.pbshow ) ) {
				postboxes.pbshow( box );
			}
		} else {
			jQuery('#' + box).hide();
			if ( jQuery.isFunction( postboxes.pbhide ) ) {
				postboxes.pbhide( box );
			}
		}
		postboxes.save_state('products_page_edit-products');
	} );
	
	
	// postbox sorting
	jQuery('.meta-box-sortables').sortable( {
			placeholder: 'sortable-placeholder',
			connectWith: [ '.meta-box-sortables' ],
			items: '> .postbox',
			handle: '.hndle',
			distance: 2,
			tolerance: 'pointer',
			sort: function(e,ui) {
				if ( jQuery(document).width() - e.clientX < 300 ) {
					if ( ! jQuery('#post-body').hasClass('has-sidebar') ) {
						var pos = jQuery('#side-sortables').offset();
	
						jQuery('#side-sortables').append(ui.item)
						jQuery(ui.placeholder).css({'top':pos.top,'left':pos.left}).width(jQuery(ui.item).width())
						postboxes.expandSidebar(1);
					}
				}
			},
			stop: function() {
				var postVars = {
					action: 'product-page-order',
					ajax: 'true'
				}
				//jQuery(this).css("border","1px solid red");
				jQuery(this).each( function() {
					postVars["order[" + this.id.split('-')[0] + "]"] = jQuery(this).sortable( 'toArray' ).join(',');
				} );
				jQuery.post( 'index.php?admin=true&ajax=true', postVars, function() {
					postboxes.expandSidebar();
				} );
			}
	} );
	
	// show or hide the stock input forms
	jQuery("input.limited_stock_checkbox").click( function ()  {
    parent_form = jQuery(this).parents('form');
    if(jQuery(this).attr('checked') == true) {
			jQuery("div.edit_stock",parent_form).show();
			jQuery("th.stock, td.stock", parent_form).show();
    } else {
			jQuery("div.edit_stock", parent_form).hide();
			jQuery("th.stock, td.stock", parent_form).hide();
    }
  });
	
	jQuery("#table_rate_price").click(
		function() {
			if (this.checked) {
				jQuery("#table_rate").show();
			} else {
				jQuery("#table_rate").hide();
			}
		}
	);
	
	jQuery(".add_level").click(
		function() {
			added = jQuery(this).parent().children('table').append('<tr><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][table_price][]"/></td></tr>');
		}
	);
	
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
	
	// delete upload
	jQuery(".file_delete_button").click(
		function() {
			jQuery(this).parent().remove();
			file_hash = jQuery(this).siblings("input").val();
			post_values = "admin=true&del_file=true&del_file_hash="+file_hash;
			jQuery.post( 'index.php?ajax=true', post_values, function(returned_data) { });
		}
	);
});



// function for adding more custom meta
function add_more_meta(e) {
  current_meta_forms = jQuery(e).parent().children("div.product_custom_meta:last");  // grab the form container
  new_meta_forms = current_meta_forms.clone(true); // clone the form container
  jQuery("label input", new_meta_forms).val(''); // reset all contained forms to empty
  current_meta_forms.after(new_meta_forms);  // append it after the container of the clicked element
  return false;
}

// function for removing custom meta
function remove_meta(e, meta_id) {
  current_meta_form = jQuery(e).parent("div.product_custom_meta");  // grab the form container
  //meta_name = jQuery("input#custom_meta_name_"+meta_id, current_meta_form).val();
  //meta_value = jQuery("input#custom_meta_value_"+meta_id, current_meta_form).val();
	returned_value = jQuery.ajax({
		type: "POST",
		url: "admin.php?ajax=true",
		data: "admin=true&remove_meta=true&meta_id="+meta_id+"",
		success: function(results) {
			if(results > 0) {
			  jQuery("div#custom_meta_"+meta_id).remove();
			}
		}
	}); 
  return false;
}


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


function wpsc_save_postboxes_state(page, container) {
	var closed = jQuery(container+' .postbox').filter('.closed').map(function() { return this.id; }).get().join(',');
	jQuery.post(postboxL10n.requestFile, {
		action: 'closed-postboxes',
		closed: closed,
		closedpostboxesnonce: jQuery('#closedpostboxesnonce').val(),
		page: page
	});
}

  
function hideelement(id) {
  state = document.getElementById(id).style.display;
  //alert(document.getElementById(id).style.display);
  if(state != 'block') {
    document.getElementById(id).style.display = 'block';
	} else {
		document.getElementById(id).style.display = 'none';
	}
}