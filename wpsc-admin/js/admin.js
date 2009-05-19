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
	 
	 
	jQuery("#submit_category_select").click(function() {
			new_url = jQuery("#category_select option:selected").val();
			//console.log(new_url);
			window.location = new_url;
			return false;
	});
	 
	
  // this loads the edit-products page using javascript
	 jQuery('.edit-product').click(function(){	
			product_id = jQuery(this).attr('href').match(/product_id=(\d{1,})/);
	 		post_values = "product_id="+product_id[1]+"";
	 		
	 		
	 		///*
			jQuery.post( 'index.php?wpsc_admin_action=load_product', post_values, function(returned_data) {
				tinyMCE.execCommand("mceRemoveControl",false,"content"); 
				jQuery('form#modify-products #content').remove();
				
				
			  jQuery('form#modify-products').html(returned_data);
			  
				if ( getUserSetting( 'editor' ) != 'html' ) {
					jQuery("#quicktags").css('display', "none");
					tinyMCE.execCommand("mceAddControl", false, "content");
				}
			});
	 		return false;
	 		// */
	 });
	
	jQuery("a.thickbox").livequery(function(){
	 tb_init(this);
	});
	
	jQuery("div.admin_product_name a.shorttag_toggle").livequery(function(){
	  jQuery(this).toggle(
			function () {
				jQuery("div.admin_product_shorttags", jQuery(this).parents("table.product_editform")).css('display', 'block');
				return false;
			},
			function () {
				//jQuery("div#admin_product_name a.shorttag_toggle").toggleClass('toggled');
				jQuery("div.admin_product_shorttags", jQuery(this).parents("table.product_editform")).css('display', 'none');
				return false;
			}
		);
	});
	
	
	
	jQuery('#poststuff .postbox h3').livequery(function(){
	  jQuery(this).click( function() {
			jQuery(jQuery(this).parent('div.postbox')).toggleClass('closed');
				if(jQuery(jQuery(this).parent('div.postbox')).hasClass('closed')) {
					jQuery('a.togbox',this).html('+');
				} else {
					jQuery('a.togbox',this).html('&ndash;');
				}
				wpsc_save_postboxes_state('products_page_edit-products', '#poststuff');
		});		
	});
	


	jQuery("#add-product-image").click(function(){
		swfu.selectFiles();
	});
	
	jQuery('.hide-postbox-tog').livequery(function(){
		jQuery(this).click( function() {
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
		});
	});
	
	
	// postbox sorting
	jQuery('.meta-box-sortables').livequery(function(){
	  jQuery(this).sortable({
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
		});
	});
	
	// show or hide the stock input forms
	jQuery("input.limited_stock_checkbox").livequery(function(){
	  jQuery(this).click( function ()  {
			parent_form = jQuery(this).parents('form');
			if(jQuery(this).attr('checked') == true) {
				jQuery("div.edit_stock",parent_form).show();
				jQuery("th.stock, td.stock", parent_form).show();
			} else {
				jQuery("div.edit_stock", parent_form).hide();
				jQuery("th.stock, td.stock", parent_form).hide();
			}
		});
	});
	
	
	jQuery("#table_rate_price").livequery(function(){
	  jQuery(this).click( function() {
			if (this.checked) {
				jQuery("#table_rate").show();
			} else {
				jQuery("#table_rate").hide();
			}
		});
	});
	
	
	jQuery(".add_level").livequery(function(){
	  jQuery(this).click(function() {
			added = jQuery(this).parent().children('table').append('<tr><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][table_price][]"/></td></tr>');
		});
	});
	
	
	jQuery(".remove_line").livequery(function(){
	  jQuery(this).click(function() {
			jQuery(this).parent().parent('tr').remove();
		});		
	});
/* shipping options start */
	// gets shipping form for admin page
		// show or hide the stock input forms
	jQuery(".wpsc-shipping-actions a").livequery(function(){
	  jQuery(this).click( function ()  {
		
		var module = jQuery(this).attr('rel');
		jQuery.ajax({
			method: "post", url: "index.php", data: "wpsc_admin_action=get_shipping_form&shippingname="+module,
			
			success: function(html){
				//jQuery(".gateway_settings").children(".form-table").html(html)
				jQuery("td.gateway_settings table.form-table").html('<tr><td><input type="hidden" name="shippingname" value="'+module+'" /></td></tr>'+html);
			}
		
		})
		});
	});
	
	jQuery('#addweightlayer').livequery(function(){
		jQuery(this).click(function(){
		jQuery(this).parent().append("<tr class='rate_row'><td><i style='color:grey'>"+TXT_WPSC_IF_WEIGHT_IS+"</i><input type='text' name='weight_layer[]' size='4'> <i style='color:grey'>"+TXT_WPSC_AND_ABOVE+"</i></td><td><input type='text' name='weight_shipping[]' size='4'>&nbsp;&nbsp;<a href='#' class='delete_button nosubmit' >"+TXT_WPSC_DELETE+"</a></td></tr>");
		bind_shipping_rate_deletion();
		});
	
	})
	
	jQuery('#addlayer').livequery(function(){
		jQuery(this).click(function(){
		jQuery(this).parent().append("<tr class='rate_row'><td><i style='color:grey'>"+TXT_WPSC_IF_PRICE_IS+"</i><input type='text' name='layer[]' size='4'> <i style='color:grey'>"+TXT_WPSC_AND_ABOVE+"</i></td><td><input type='text' name='shipping[]' size='4'>&nbsp;&nbsp;<a href='#' class='delete_button nosubmit' >"+TXT_WPSC_DELETE+"</a></td></tr>");
		bind_shipping_rate_deletion();
		});
	
	})
	
  jQuery('table#gateway_options a.delete_button').livequery(function(){
  		jQuery(this).click(function () {
    this_row = jQuery(this).parent('div');
    if(jQuery(this).hasClass('nosubmit')) {
			// if the row was added using JS, just scrap it
			jQuery(this_row).remove();
    } else {
			// otherwise, empty it and submit it
			jQuery('input', this_row).val('');
			jQuery(this).parents('form').submit();
    }
    return false;
	});
	});

/*shipping options end */
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
	jQuery("div.previewimage").livequery(function(){
	  jQuery(this).hover(
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
	});
	
	
	// display image editing menu
	jQuery("a.editButton").livequery(function(){
	  jQuery(this).click( function(){
			jQuery(this).hide();
			jQuery('#image_settings_box').show('fast');
		});	
	});
	// hide image editing menu
	jQuery("a.closeimagesettings").livequery(function(){
	  jQuery(this).click(function (e) {
			jQuery("div#image_settings_box").hide();
		});
	});
	
	// delete upload
	jQuery(".file_delete_button").livequery(function(){
			jQuery(this).click(function() {
				jQuery(this).parent().remove();
				file_hash = jQuery(this).siblings("input").val();
				post_values = "admin=true&del_file=true&del_file_hash="+file_hash;
				jQuery.post( 'index.php?ajax=true', post_values, function(returned_data) { });
			});
		});
		
	// Options page ajax tab display 
	jQuery(function() {
		  // set us up some mighty fine tabs for the options page
		  
		  if (typeof jQuery('#wpsc_options > ul#tabs').tabs != "undefined") {
        $tabs = jQuery('#wpsc_options > ul#tabs').tabs();
			}
// 			current_tab = window.location.href.split('#');
			
			// this here code handles remembering what tab you were on
			jQuery('#wpsc_options > ul').bind('tabsselect', function(event, ui) {
				form_action = jQuery('#cart_options').attr('action').split('#');  //split at the #
				form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
				jQuery('#cart_options').attr('action', form_action); // stick it all back in the action attribute
// 				var current_tab = $tabs.data('selected.tabs');
// 				alert(current_tab);
// 				if (current_tab == '3') {
// 					form_action = jQuery('#shipping_options').attr('action').split('#');  //split at the #
// 					form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
// 
// 					jQuery('#shipping_options').attr('action', form_action); // stick it all back in the action attribute
// 				}
// 				if (current_tab == '4') {
// 					form_action = jQuery('#gateway_options').attr('action').split('#');  //split at the #
// 					form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
// 					jQuery('#gateway_options').attr('action', form_action); // stick it all back in the action attribute
// 				}
			});
			jQuery('#wpsc_options > ul').bind('tabsload', function(event, ui) {
			  // bind_shipping_rate_deletion();
// 				form_action = jQuery('#cart_options').attr('action').split('#');  //split at the #
// 				form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
// 				jQuery('#cart_options').attr('action', form_action); // stick it all back in the action attribute
				var current_tab = $tabs.data('selected.tabs');
				if (current_tab == '3') {
					form_action = jQuery('#shipping_options').attr('action').split('#');  //split at the #
					form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
					jQuery('#shipping_options').attr('action', form_action); // stick it all back in the action attribute
				}
				if (current_tab == '4') {
					form_action = jQuery('#gateway_options_tbl').attr('action').split('#');  //split at the #
					form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
					jQuery('#gateway_options_tbl').attr('action', form_action); // stick it all back in the action attribute
				}
				if (current_tab == '5') {
					form_action = jQuery('#chekcout_options_tbl').attr('action').split('#');  //split at the #
					form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
					jQuery('#chekcout_options_tbl').attr('action', form_action); // stick it all back in the action attribute
				}
				if (current_tab == '6') {
					form_action = jQuery('#gold_cart_form').attr('action').split('#');  //split at the #
					form_action = form_action[0]+"#"+ui.panel.id; // get the first item, add the hash then our current tab ID
					jQuery('#gold_cart_form').attr('action', form_action); // stick it all back in the action attribute
				}
					//alert(current_tab);
			});
		});



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

/*
 * Modified copy of the wordpress edToolbar function that does the same job, it uses document.write, we cannot.
*/
function wpsc_edToolbar() {
	//document.write('<div id="ed_toolbar">');
	output = '';
	for (i = 0; i < edButtons.length; i++) {
		output += 	wpsc_edShowButton(edButtons[i], i);
	}
	output += '<input type="button" id="ed_spell" class="ed_button" onclick="edSpell(edCanvas);" title="' + quicktagsL10n.dictionaryLookup + '" value="' + quicktagsL10n.lookup + '" />';
	output += '<input type="button" id="ed_close" class="ed_button" onclick="edCloseAllTags();" title="' + quicktagsL10n.closeAllOpenTags + '" value="' + quicktagsL10n.closeTags + '" />';
//	edShowLinks(); // disabled by default
	//document.write('</div>');
	jQuery('div#ed_toolbar').html(output);
}


/*
 * Modified copy of the wordpress edShowButton function that does the same job, it uses document.write, we cannot.
*/

function wpsc_edShowButton(button, i) {
	if (button.id == 'ed_img') {
		output = '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertImage(edCanvas);" value="' + button.display + '" />';
	}
	else if (button.id == 'ed_link') {
		output = '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertLink(edCanvas, ' + i + ');" value="' + button.display + '" />';
	}
	else {
		output = '<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertTag(edCanvas, ' + i + ');" value="' + button.display + '"  />';
	}
	return output;
}



function fillcategoryform(catid) {
  post_values = 'ajax=true&admin=true&catid='+catid;
	jQuery.post( 'index.php', post_values, function(returned_data) {
	  
		jQuery('#formcontent').html( returned_data );
		jQuery('form.edititem').css('display', 'block');
		jQuery('#additem').css('display', 'none');
		jQuery('#productform').css('display', 'block');
		jQuery("#loadingindicator_span").css('visibility','hidden');
	});
}
   
function showaddform() {
   document.getElementById('productform').style.display = 'none';
   document.getElementById('additem').style.display = 'block';
   return false;
}