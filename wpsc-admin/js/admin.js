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
			jQuery.post( 'index.php?wpsc_admin_action=purchlog_edit_status', post_values, function(returned_data) { });
	 });
	 

	jQuery("tr.product-edit").hover(
		function() {
			jQuery(".wpsc-row-actions", this).css("visibility", "visible");
		},	
		function() {
			jQuery(".wpsc-row-actions", this).css("visibility", "hidden");
		}
	);
	
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

/*
* SWFUpload code starts here
*/
// jQuery(document).ready( function () {
filesizeLimit = 20480000;


jQuery(document).ready( function () {
	if (typeof SWFUpload != "undefined") {
	    swfu = new SWFUpload({
			flash_url : WPSC_URL+'/js/swfupload.swf',
			upload_url: base_url+'/?action=wpsc_add_image',
			button_placeholder_id : "spanButtonPlaceholder",
			button_width: 103,
			button_height: 24,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,
			post_params: {"prodid" : jQuery('#product_id').val()},
			file_queue_limit : 1,
			file_size_limit : filesizeLimit+'b',
			file_types : "*.jpg;*.jpeg;*.png;*.gif;*.JPG;*.JPEG;*.PNG;*.GIF",
			file_types_description : "Web-compatible Image Files",
			file_upload_limit : filesizeLimit,
				custom_settings : {
				targetHolder : false,
				progressBar : false,
				sorting : false
			},
			debug: false,
			
			file_queued_handler : imageFileQueued,
			file_queue_error_handler : imageFileQueueError,
			file_dialog_complete_handler : imageFileDialogComplete,
			upload_start_handler : startImageUpload,
			upload_progress_handler : imageUploadProgress,
			upload_error_handler : imageUploadError,
			upload_success_handler : imageUploadSuccess,
			upload_complete_handler : imageUploadComplete,
			queue_complete_handler : imageQueueComplete
		});
	}
});

	
/**
* SWFUpload Image Uploading events
**/

function imageFileQueued (file) {

}

function imageFileQueueError (file, error, message) {
	if (error == SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
		alert("You selected too many files to upload at one time. " + (message === 0 ? "You have reached the upload limit." : "You may upload " + (message > 1 ? "up to " + message + " files." : "only one file.")));
		return;
	}

}

function imageFileDialogComplete (selected, queued) {
	try {
		this.startUpload();
	} catch (ex) {
		this.debug(ex);
	}
}

function startImageUpload (file) {
//   alert('start '+jQuery("div#swfupload_img_indicator").css('display'));
	jQuery("div#swfupload_img_indicator").css('display', 'block');
	var cell = jQuery('<li></li>').appendTo(jQuery('#gallery_list'));
	var sorting = jQuery('<input type="hidden" name="images[]" value="" />').appendTo(cell);
	var progress = jQuery('<div class="progress"></div>').appendTo(cell);
	var bar = jQuery('<div class="bar"></div>').appendTo(progress);
	var art = jQuery('<div class="gloss"></div>').appendTo(progress);
	this.targetHolder = cell;
	this.progressBar = bar;
	this.sorting = sorting;
	return true;
}

function imageUploadProgress (file, loaded, total) {
	var progress = Math.ceil((loaded/total)*76);
	jQuery(this.progressBar).animate({'width':progress+'px'},100);
}

function imageUploadError (file, error, message) {
	console.log(error+": "+message);
}

function imageUploadSuccess (file, results) {
		//Don't delete, initiate id is neccesary.
		var id = null;
		var pid = null;
		
		jQuery("span.swfupload_loadingindicator").css('visibility', 'hidden');
		eval(results);
// 		jQuery(this).css('border', '1px solid red');
		if(pid >= 1) {
			context = jQuery("div#productform");
		} else {
			context = jQuery("div#additem");
		}
	
	if (id == null ) {
			if(replacement_src != null) {
					jQuery("li.first div.previewimage a.thickbox", context).attr('href', replacement_src);
					jQuery("li.first div.previewimage a.thickbox img.previewimage", context).attr('src', replacement_src);
			} else {
				if (jQuery('#gold_present', context).val() != '1') {
					jQuery('#add-product-image', context).remove();
				}
				jQuery(this.sorting).attr({'value':src});
				var img = jQuery('<div class="previewimage" id="'+id+'"><a href="'+WPSC_IMAGE_URL+src+'" rel="product_extra_image_'+id+'" class="thickbox"><img src="'+WPSC_IMAGE_URL+src+'" width="60" height="60" class="previewimage" /></a></div>').appendTo(this.targetHolder).hide();
				set = jQuery("#gallery_list", context).sortable('toArray');

				jQuery('#gallery_image_0', context).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
				jQuery('#gallery_image_0', context).parent('li').addClass('first');
				jQuery('#gallery_image_0', context).parent('li').attr('id', 0);
				jQuery('#gallery_image_0 img.deleteButton', context).remove();
				enablebuttons();
		}
	} else {
		//jQuery(this.targetHolder).attr({'id':'image-'+src});
		jQuery(this.targetHolder).attr({'id':id});
		div_id = 'gallery_image_'+id;
		jQuery(this.targetHolder).html('');
		var img = jQuery('<div class="previewimage" id="'+div_id+'"><input type="hidden" name="images[]" value="'+src+'"><a href="'+WPSC_IMAGE_URL+src+'" rel="product_extra_image_'+id+'" class="thickbox"><img src="'+WPSC_IMAGE_URL+src+'" width="60" height="60" class="previewimage" /></a></div>').appendTo(this.targetHolder).hide();
		
		jQuery('#gallery_image_0', context).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
		jQuery('#gallery_image_0', context).parent('li').addClass('first');
		jQuery('#gallery_image_0', context).parent('li').attr('id', 0);
		jQuery('#gallery_image_0 img.deleteButton', context).remove();
		
		
		if (jQuery('#gallery_list li', context).size() > 1) {
			jQuery('#gallery_list', context).sortable('refresh');
		} else {
			jQuery('#gallery_list', context).sortable();
		}
		set = jQuery("#gallery_list", context).sortable('toArray');
		order = set.join(',');
		prodid = jQuery('#prodid', context).val();

		if(prodid == null) {
			prodid = 0;
		}
		
			function imageorderresults(results) {
				eval(results);
				jQuery('#gallery_image_'+ser).append(output);
				enablebuttons();
			}
		
		ajax.post("index.php",imageorderresults,"admin=true&ajax=true&prodid="+prodid+"&imageorder=true&order="+order+"");
		
		
		enablebuttons();

	}
	jQuery(this.progressBar).animate({'width':'76px'},250,function () {
		jQuery(this).parent().fadeOut(500,function() {
			jQuery(this).remove();
			jQuery(img).fadeIn('500');
			jQuery(img).append('<img class="deleteButton" src="'+WPSC_URL+'/images/cross.png" alt="-" style="display: none;"/>');
			enablebuttons()
			//enableDeleteButton(deleteButton);
		});
	});
}

function imageUploadComplete (file) {
	
	jQuery("div#swfupload_img_indicator").css('display', 'none');
	if (jQuery('#gallery_list li').size() > 1)
		jQuery('#gallery_list').sortable('refresh');
	else
		jQuery('#gallery_list').sortable();
}

function imageQueueComplete (uploads) {

}