var someresults=function()  {
	document.getElementById('changenotice').innerHTML = "Thank you, your change has been saved";
}

var noresults=function()  {
	// see nothing, know nothing, do nothing
}

if(typeof(select_min_height) == undefined) {
	var select_min_height = 0;
	var select_max_height = 200;
}

jQuery(document).ready(
  function() {  
//   jQuery('div.select_product_file').Resizable({
//     minWidth: 300,
//     minHeight: select_min_height,
//     maxWidth: 300,
//     maxHeight: select_max_height,
//     handlers: {
//       s: '.select_product_handle'
//       }
//     });
		jQuery("div.admin_product_name a.shorttag_toggle").toggle(
			function () {
				jQuery("div.admin_product_shorttags", jQuery(this).parent("div.admin_product_name")).css('display', 'block');
			},
			function () {
				//jQuery("div#admin_product_name a.shorttag_toggle").toggleClass('toggled');
				jQuery("div.admin_product_shorttags", jQuery(this).parent("div.admin_product_name")).css('display', 'none');
			}
		);
	  enablebuttons();
  }
);

function activate_resizable() {
//   jQuery('div.edit_select_product_file').Resizable({
//     minWidth: 300,
//     minHeight: select_min_height,
//     maxWidth: 300,
//     maxHeight: select_max_height,
//     handlers: {
//       s: '.edit_select_product_handle'
//       }
// 	});
}
  
	jQuery(document).ready(function(){
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
			});
		});
	});
  
  
function categorylist(url) {
  self.location = url;
}
  
function submit_change_country() {
  document.cart_options.submit();
  //document.cart_options.submit();
}
  
var getresults=function(results) {
  document.getElementById('formcontent').innerHTML = results;
  document.getElementById('additem').style.display = 'none';
  document.getElementById('productform').style.display = 'block';
	jQuery("#loadingindicator_span").css('visibility','hidden');
	enablebuttons();
	
jQuery('#formcontent .postbox h3').click( function() {
	jQuery(jQuery(this).parent('div.postbox')).toggleClass('closed');
		if(jQuery(jQuery(this).parent('div.postbox')).hasClass('closed')) {
			jQuery('a.togbox',this).html('+');
		} else {
			jQuery('a.togbox',this).html('&ndash;');
		}
	  wpsc_save_postboxes_state('editproduct', '#formcontent');
});


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
				ajax.post("index.php",imageorderresults,"ajax=true&prodid="+prodid+"&imageorder=true&order="+order);
			},
	'opacity':0.5
});

function imageorderresults(results) {
  eval(results);
  
	jQuery('#gallery_image_'+ser).append(output);

	enablebuttons();
}

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

jQuery("a.closeimagesettings").click(
	function (e) {
		jQuery("div#image_settings_box").hide();
	}
);

jQuery("#table_rate_price").click(
	function() {
		if (this.checked) {
			jQuery("#table_rate").slideDown("fast");
		} else {
			jQuery("#table_rate").slideUp("fast");
		}
	}
);

jQuery(".add_level").click(
	function() {
		jQuery(this).parent().children('table').append('<tr><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][table_price][]"/></td><td><img src="'+WPSC_URL+'/images/cross.png" class="remove_line"></td></tr>');
	}
);

jQuery(".remove_label").click(
	function(){
		jQuery(this).parent().parent().parent().remove();
	}
);

jQuery("#add_label").click(
	function(){
		jQuery("#labels").append("<br><table><tr><td>"+TXT_WPSC_LABEL+" :</td><td><input type='text' name='productmeta_values[labels][]'></td></tr><tr><td>"+TXT_WPSC_LIFE_NUMBER+" :</td><td><input type='text' name='productmeta_values[life_number][]'></td></tr><tr><td>"+TXT_WPSC_ITEM_NUMBER+" :</td><td><input type='text' name='productmeta_values[item_number][]'></td></tr><tr><td>"+TXT_WPSC_PRODUCT_CODE+" :</td><td><input type='text' name='productmeta_values[product_code][]'></td></tr><tr><td>"+TXT_WPSC_PDF+" :</td><td><input type='file' name='productmeta_values[product_pdf][]'></td></tr></table>");
	}
);

jQuery(".remove_line").click(
	function() {
		jQuery(this).parent().parent('tr').remove();
	}
);
//SWFUpload
	filesizeLimit = 5120000;
	
// 	if (typeof SWFUpload != "undefined") {
	  
	  
    var swfu = new SWFUpload({
      flash_url : WPSC_URL+'/js/swfupload.swf',
      button_placeholder_id : "spanButtonPlaceholder",
      button_width: 103,
      button_height: 24,
      button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
      button_cursor: SWFUpload.CURSOR.HAND,
      upload_url: base_url+'/?action=wpsc_add_image',
      post_params: {"prodid" : jQuery('#prodid').val()},
      file_queue_limit : 1,
      file_size_limit : filesizeLimit+'b',
      file_types : "*.jpg;*.jpeg;*.png;*.gif",
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
// 	}

	jQuery("#add-product-image").click(function(){
    swfu.selectFiles();
	});
	activate_resizable();
	tb_init("a.thickbox");

	jQuery("div.admin_product_name a.shorttag_toggle").toggle(
		function () {
			jQuery("div.admin_product_shorttags", jQuery(this).parent("div.admin_product_name")).css('display', 'block');
		},
		function () {
			//jQuery("div#admin_product_name a.shorttag_toggle").toggleClass('toggled');
			jQuery("div.admin_product_shorttags", jQuery(this).parent("div.admin_product_name")).css('display', 'none');
		}
	);
}

function filleditform(prodid)	{
	ajax.post("index.php",getresults,"ajax=true&admin=true&prodid="+prodid);
	jQuery('#loadingimage').attr('src', jQuery("#loadingimage").attr('src'));
	jQuery('#loadingindicator_span').css('visibility','visible');
}
   
function fillvariationform(variation_id) {
  ajax.post("index.php",getresults,"ajax=true&admin=true&variation_id="+variation_id);
	jQuery('#loadingimage').attr('src', WPSC_URL+'/images/indicator.gif');
	jQuery('#loadingindicator_span').css('visibility','visible');
}
   
function showaddform() {
   document.getElementById('productform').style.display = 'none';
   document.getElementById('additem').style.display = 'block';
   return false;
}
   
function showadd_categorisation_form() {
	if(jQuery('div#add_categorisation').css('display') != 'block') {
		jQuery('div#add_categorisation').css('display', 'block');
		jQuery('div#edit_categorisation').css('display', 'none');
	} else {
		jQuery('div#add_categorisation').css('display', 'none');
	}
	return false;
}


function showedit_categorisation_form() {
	if(jQuery('div#edit_categorisation').css('display') != 'block') {
		jQuery('div#edit_categorisation').css('display', 'block');
		jQuery('div#add_categorisation').css('display', 'none');
	} else {
		jQuery('div#edit_categorisation').css('display', 'none');
	}
	return false;
}
	
function fillcategoryform(catid) {
	ajax.post("index.php",getresults,"ajax=true&admin=true&catid="+catid);
}

function fillbrandform(catid) {
	ajax.post("index.php",getresults,"ajax=true&admin=true&brandid="+catid);
}

var gercurrency=function(results)
  {
  document.getElementById('cslchar1').innerHTML = results;
  document.getElementById('cslchar2').innerHTML = results;
  document.getElementById('cslchar3').innerHTML = results;
  document.getElementById('cslchar4').innerHTML = results;
  }

function getcurrency(id) {
	ajax.post("index.php",gercurrency,"ajax=true&currencyid="+id);
}
  
function country_list(id) {
  var country_list=function(results) {
    document.getElementById('options_region').innerHTML = results;
	}
  ajax.post("index.php",country_list,"ajax=true&get_country_tax=true&country_id="+id);
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
  
function update_preview_url(prodid) {
  image_height = document.getElementById("image_height").value;
  image_width = document.getElementById("image_width").value;
  if(((image_height > 0) && (image_height <= 1024)) && ((image_width > 0) && (image_width <= 1024))) {
    new_url = "index.php?productid="+prodid+"&height="+image_height+"&width="+image_width+"";
    document.getElementById("preview_link").setAttribute('href',new_url);
	} else {
		new_url = "index.php?productid="+prodid+"";
		document.getElementById("preview_link").setAttribute('href',new_url);
	}
  return false;
}




function checkimageresize() {
	document.getElementById('image_resize2').checked = true;
}
   
      
   
function add_variation_value(value_type)
  {
  container_id = value_type+"_variation_values";
  //alert(container_id);
  last_element_id = document.getElementById(container_id).lastChild.id;
  last_element_id = last_element_id.split("_");
  last_element_id = last_element_id.reverse();
  new_element_id = "variation_value_"+(parseInt(last_element_id[0])+1);
  
  
  old_elements = document.getElementById(container_id).innerHTML;
  
  //new_element_contents = "<span id='"+new_element_id+"'>";
  new_element_contents = "";
  if(value_type == "edit")
    {
    new_element_contents += "<input type='text' class='text' name='new_variation_values[]' value='' />";
    }
    else
      {
      new_element_contents += "<input type='text' class='text' name='variation_values[]' value='' />";
      }
  new_element_contents += " <a class='image_link' href='#' onclick='remove_variation_value_field(\""+new_element_id+"\")'><img src='"+WPSC_URL+"/images/trash.gif' alt='"+TXT_WPSC_DELETE+"' title='"+TXT_WPSC_DELETE+"' /></a><br />";
  //new_element_contents += "</span>";
  
  new_element = document.createElement('span');
  new_element.id = new_element_id;
   
  document.getElementById(container_id).appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;
  return false;
  }
  
  
 // if(($_POST['ajax'] == "true") && ($_POST['remove_variation_value'] == "true") && is_numeric($_POST['variation_value_id']))
function remove_variation_value(id,variation_value)
  {
  var delete_variation_value=function(results)
    {
    }
  element_count = document.getElementById("add_variation_values").childNodes.length;
  if(element_count > 1)
    {
    ajax.post("index.php",delete_variation_value,"ajax=true&remove_variation_value=true&variation_value_id="+variation_value);
    target_element = document.getElementById(id);
    document.getElementById("add_variation_values").removeChild(target_element);
    }
  }
 
function remove_variation_value_field(id)
  {
  element_count = document.getElementById("add_variation_values").childNodes.length;
  if(element_count > 1)
    {
    target_element = document.getElementById(id);
    document.getElementById("add_variation_values").removeChild(target_element);
    }
  }
  
function variation_value_list(id) {
  if(id == null) {
    id = '';
  }
	var display_list=function(results) {
		eval(results);
    jQuery("label.variation_checkbox"+id+" input[@type='checkbox']").removeAttr("disabled", "true");
    if(id != '') {
      jQuery("#edit_variations_container").html(edit_variation_combinations_html);
    } else {
      jQuery("#add_product_variation_details").html(add_variation_combinations_html);
    }
	}
	selected_price = jQuery("div#price_and_stock input[@name='price']").val();
	
	current_variations = jQuery("label.variation_checkbox"+id+" input[@type='checkbox']").serialize();
	jQuery("label.variation_checkbox"+id+" input[@type='checkbox']").attr("disabled", "true");
	ajax.post("index.php",display_list,"ajax=true&list_variation_values=true&product_id="+id+"&selected_price="+selected_price+"&"+current_variations+"");
}

 
  
  
  
var display_list_ajaxx=function(results) {
	jQuery("div#edit_variations_container").html(results);
	//alert(results);
}
  
function add_variation_value_list(id)
  {
	var display_list=function(results) {
		eval(results);
    if(variation_subvalue_html != '') {
        new_element_id = "add_product_variations";
        if(document.getElementById(new_element_id) === null) {
          new_element = document.createElement('span');
          new_element.id = new_element_id;
          document.getElementById("add_product_variations").appendChild(new_element);
          //document.getElementById(new_element_id).innerHTML = variation_value_html;
        }
      jQuery("#add_product_variation_details").html(variation_subvalue_html);
    }
		jQuery("#edit_product_variations input[@type='checkbox']").each(function() {
// 		  alert(this.id);
    });
		//ajax.post("index.php",display_list_ajaxx,"ajax=true&list_variation_values_ajaxx=true");
	}
	current_variations = jQuery("input.variation_ids").serialize();
	ajax.post("index.php",display_list,"ajax=true&list_variation_values=true&new_variation_id="+id+"&prefix=add_product_variations&"+current_variations+"");
}
  
  
function edit_variation_value_list(id) {
  // haah, the javascript end does essentially nothing of interest, just sends a request, and dumps the output in a div tag
	var display_variation_forms=function(results) {
		if(results !== "false") { // do nothing if just the word false is returned
	  //alert(jQuery("div#edit_variations_container").html(results));
		
			//alert(jQuery("div#edit_variations_container"));
			jQuery("div#edit_variations_container").html(results);	
		}
	}	
	product_id= jQuery("#prodid").val();
	ajax.post("index.php",display_variation_forms,"ajax=true&edit_variation_value_list=true&variation_id="+id+"&product_id="+product_id);
 }

function remove_variation_value_list(prefix,id){
	var redisplay_list=function(results) {
		jQuery("#add_product_variation_details").html(results);
	}
  if(prefix == "edit_product_variations") {
    target_element_id = "product_variations_"+id;
	} else {
		target_element_id = prefix+"_"+id;
	}
  target_element = document.getElementById(target_element_id);
  document.getElementById(prefix).removeChild(target_element);
  if(prefix == "add_product_variations") {
		current_variations = jQuery("input.variation_ids").serialize();
		ajax.post("index.php",redisplay_list,"ajax=true&redisplay_variation_values=true&"+current_variations+"");
  }  
  return false;
}
  
function tick_active(target_id,input_value) {
  if(input_value != '') {
    document.getElementById(target_id).checked = true;
  }
}
  
function add_form_field() {
  time = new Date();
  new_element_number = time.getTime();
  new_element_id = "form_id_"+new_element_number;
  
  new_element_contents = "";
  new_element_contents += " <table><tr>\n\r";
  new_element_contents += "<td class='namecol'><input type='text' name='new_form_name["+new_element_number+"]' value='' /></td>\n\r";
  new_element_contents += "<td class='typecol'><select name='new_form_type["+new_element_number+"]'>"+HTML_FORM_FIELD_TYPES+"</select></td>\n\r"; 
  new_element_contents += "<td class='mandatorycol' style='text-align: center;'><input type='checkbox' name='new_form_mandatory["+new_element_number+"]' value='1' /></td>\n\r";
  new_element_contents += "<td class='logdisplaycol' style='text-align: center;'><input type='checkbox' name='new_form_display_log["+new_element_number+"]' value='1' /></td>\n\r";
  new_element_contents += "<td class='ordercol'><input type='text' size='3' name='new_form_order["+new_element_number+"]' value='' /></td>\n\r";
  new_element_contents += "<td  style='text-align: center; width: 12px;'><a class='image_link' href='#' onclick='return remove_new_form_field(\""+new_element_id+"\");'><img src='"+WPSC_URL+"/images/trash.gif' alt='"+TXT_WPSC_DELETE+"' title='"+TXT_WPSC_DELETE+"' /></a></td>\n\r";
  new_element_contents += "<td></td>\n\r";
  new_element_contents += "</tr></table>";
  
  new_element = document.createElement('div');
  new_element.id = new_element_id;
   
  document.getElementById("form_field_form_container").appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;
  return false;
}
  
function remove_new_form_field(id) {
  element_count = document.getElementById("form_field_form_container").childNodes.length;
  if(element_count > 1) {
    target_element = document.getElementById(id);
    document.getElementById("form_field_form_container").removeChild(target_element);
  }
  return false;
}
  
function remove_form_field(id,form_id) {
  var delete_variation_value=function(results) { }
  element_count = document.getElementById("form_field_form_container").childNodes.length;
  if(element_count > 1) {
    ajax.post("index.php",delete_variation_value,"ajax=true&remove_form_field=true&form_id="+form_id);
    target_element = document.getElementById(id);
    document.getElementById("form_field_form_container").removeChild(target_element);
  }
  return false;
} 
  
function show_status_box(id,image_id) {
  state = document.getElementById(id).style.display; 
  if(state != 'block') {
    document.getElementById(id).style.display = 'block';
    document.getElementById(image_id).src = WPSC_URL+'/images/icon_window_collapse.gif';
  } else {
    document.getElementById(id).style.display = 'none';
    document.getElementById(image_id).src = WPSC_URL+'/images/icon_window_expand.gif';
  }
  return false;
}
  
function submit_status_form(id) {
  document.getElementById(id).submit();
}
  
// pe.{
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


// }.pe  

function toggle_display_options(state) {
  switch(state) {
    case 'list':
    document.getElementById('grid_view_options').style.display = 'none';
    document.getElementById('list_view_options').style.display = 'block';    
    break;
    
    case 'grid':
    document.getElementById('list_view_options').style.display = 'none';
    document.getElementById('grid_view_options').style.display = 'block';
    break;
    
    default:
    document.getElementById('list_view_options').style.display = 'none';
    document.getElementById('grid_view_options').style.display = 'none';
    break;
  }
}
  
function log_submitform(id) {
	value1 = document.getElementById(id);
	if (ajax.serialize(value1).search(/value=3/)!=-1) {
    document.getElementById("track_id").style.display="block";
	} else {
    document.getElementById("track_id").style.display="none";
	}
	var get_log_results=function(results) {
    eval(results);
  }
  frm = document.getElementById(id);
  ajax.post("index.php?ajax=true&log_state=true",get_log_results,ajax.serialize(frm));
  return false;
}

function save_tracking_id(id) {
  value1 = document.getElementById('tracking_id_'+id).value;
  value1 ="id="+id +"&value="+value1;
  ajax.post("index.php?ajax=true&save_tracking_id=true",noresults,value1);
  return false;
}
  

/* the following is written by Allen */
// jQuery(document).ready(
// 	function()
// 	{
// // 		jQuery('#description').Resizable(
// // 			{
// // 				minWidth: 50,
// // 				minHeight: 50,
// // 				maxWidth: 400,
// // 				maxHeight: 400,
// // 				handlers: {
// // 					s: '#resizeS'
// // 				},
// // 				onResize: function(size)
// // 				{
// // 					jQuery('textarea', this).css('height', size.height - 6 + 'px');
// // 				}
// // 			}
// // 		);
// 	}
// );
// 
// jQuery(document).ready(
// 	function()
// 	{
// 		jQuery('#description1').Resizable(
// 			{
// 				minWidth: 50,
// 				minHeight: 50,
// 				maxWidth: 400,
// 				maxHeight: 400,
// 				handlers: {
// 					s: '#resizeS1'
// 				},
// 				onResize: function(size)
// 				{
// 					jQuery('textarea', this).css('height', size.height - 6 + 'px');
// 				}
// 			}
// 		);
// 	}
// );

var select_min_height = 75;
var select_max_height = 50;

//ToolTip JavaScript
jQuery('img').Tooltip(
	{
		className: 'inputsTooltip',
		position: 'mouse',
		delay: 200
	}
);

jQuery(window).load( function () {

	
	jQuery('.additem .postbox h3').click( function() {
		jQuery(jQuery(this).parent('div.postbox')).toggleClass('closed');
		if(jQuery(jQuery(this).parent('div.postbox')).hasClass('closed')) {
			jQuery('a.togbox',this).html('+');
		} else {
			jQuery('a.togbox',this).html('&ndash;');
		}
	  wpsc_save_postboxes_state('products', '.additem');
	});
	
	jQuery('a.closeEl').bind('click', toggleContent);
 	jQuery('div.groupWrapper').sortable( {
			accept: 'groupItem',
 			helperclass: 'sortHelper',
 			activeclass : 	'sortableactive',
 			hoverclass : 	'sortablehover',
 			handle: 'div.itemHeader',
 			tolerance: 'pointer',
 			onStart : function() {
 				jQuery.iAutoscroller.start(this, document.getElementsByTagName('body'));
 			},
 			onStop : function() {
				jQuery.iAutoscroller.stop();
 			},
 			update : function(e,ui) {
 				serial = jQuery('div.groupWrapper').sortable('toArray');
 				category_id = jQuery("input#item_list_category_id").val();
 				
 				ajax.post("index.php", noresults, "ajax=true&changeorder=true&category_id="+category_id+"&sort1="+serial);
 			}
 		}
 	);

	jQuery('a#close_news_box').click( function () {
		jQuery('div.wpsc_news').css( 'display', 'none' );
		ajax.post("index.php", noresults, "ajax=true&admin=true&hide_ecom_dashboard=true");
		return false;
	});
});
var toggleContent = function(e)
{
	var targetContent = $('div.itemContent', this.parentNode.parentNode);
	if (targetContent.css('display') == 'none') {
		targetContent.slideDown(300);
		$(this).html('[-]');
	} else {
		targetContent.slideUp(300);
		$(this).html('[+]');
	}
	return false;
};


function hideelement1(id, item_value)
  {
  //alert(value);  
		if(item_value == 5) {
			jQuery(document.getElementById(id)).css('display', 'block');
		} else {
			jQuery(document.getElementById(id)).css('display', 'none');
		}
  }

  
function suspendsubs(user_id)
{
	var comm =jQuery("#suspend_subs"+user_id).attr("checked");
	//alert(comm);
	if (comm == true){
		ajax.post("index.php",noresults,"ajax=true&log_state=true&suspend=true&value=1&id="+user_id);
	} else {		
		ajax.post("index.php",noresults,"ajax=true&log_state=true&suspend=true&value=2&id="+user_id);
	}
	return false;
}

function delete_extra_preview(preview_name, prodid) {
	var preview_name_results=function(results) {
		filleditform(prodid);
	}
	ajax.post("index.php",preview_name_results,"ajax=true&admin=true&prodid="+prodid+"&preview_name="+preview_name);
}

function shipwire_sync() {
	ajax.post("index.php",noresults,"ajax=true&shipwire_sync=ture");
}

function shipwire_tracking() {
	ajax.post("index.php",noresults,"ajax=true&shipwire_tracking=ture");
}

function display_settings_button() {
	jQuery("#settings_button").slideToggle(200);
	//document.getElementById("settings_button").style.display='block';
}

function submittogoogle(id){
	value1=document.getElementById("google_command_list_"+id).value;
	value2=document.getElementById("partial_amount_"+id).value;
	reason=document.getElementById("cancel_reason_"+id).value;
	comment=document.getElementById("cancel_comment_"+id).value;
	message=document.getElementById("message_to_buyer_message_"+id).value;
	document.getElementById("google_command_indicator").style.display='inline';
	ajax.post("index.php",submittogoogleresults,"ajax=true&submittogoogle=true&message="+message+"&value="+value1+"&amount="+value2+"&comment="+comment+"&reason="+reason+"&id="+id);
	return true;
}

var submittogoogleresults=function (results) {
	window.location.reload(true);
}

function display_partial_box(id){
	value1=document.getElementById("google_command_list_"+id).value;
	if ((value1=='Refund') || (value1=='Charge')){
		document.getElementById("google_partial_radio_"+id).style.display='inline';
		if (value1=='Refund'){
			document.getElementById("google_cancel_"+id).style.display='block';
			document.getElementById("cancel_reason_"+id).style.display='inline';
			document.getElementById("cancel_div_comment_"+id).style.display='none';
		}
	}else if ((value1=='Cancel')||(value1=='Refund')) {
		document.getElementById("google_cancel_"+id).style.display='block';
		document.getElementById("cancel_reason_"+id).style.display='inline';
	}else if (value1=='Send Message') {
		document.getElementById("message_to_buyer_"+id).style.display='block';
	} else {
		document.getElementById("cancel_div_comment_"+id).style.display='none';
		document.getElementById("google_cancel_"+id).style.display='none';
		document.getElementById("cancel_reason_"+id).style.display='none';
		document.getElementById("message_to_buyer_"+id).style.display='none';
		document.getElementById("google_partial_radio_"+id).style.display='none';
		document.getElementById("partial_amount_"+id).style.display='none';
	}
}

function add_more_meta(e) {
  current_meta_forms = jQuery(e).parent("div.product_custom_meta");  // grab the form container
  new_meta_forms = current_meta_forms.clone(true); // clone the form container
  jQuery("label input", new_meta_forms).val(''); // reset all contained forms to empty
  current_meta_forms.after(new_meta_forms);  // append it after the container of the clicked element
  return false;
}

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


function wpsc_save_postboxes_state(page, container) {
	var closed = jQuery(container+' .postbox').filter('.closed').map(function() { return this.id; }).get().join(',');
	jQuery.post(postboxL10n.requestFile, {
		action: 'closed-postboxes',
		closed: closed,
		closedpostboxesnonce: jQuery('#closedpostboxesnonce').val(),
		page: page
	});
}

jQuery(document).ready(function(){
	jQuery('.deleteproducts > button.button').click(
		function () {
			var ids='0';
			jQuery('.deletecheckbox:checked').each(
				function () {
					ids += ","+jQuery(this).val();
				}
			);
			var r=confirm("Please confirm deletion");
			if (r==true) {
				ajax.post("index.php",reloadresults,"ajax=true&del_prod=true&del_prod_id="+ids);
			}
		}
	);
	jQuery('#selectall').click(
		function () {
			if (this.checked) {
				jQuery('.deletecheckbox').each(function(){this.checked = true;});
			} else {
				jQuery('.deletecheckbox').each(function(){this.checked = false;});
			}
		}
	);
	jQuery('.pickdate').datepicker({ dateFormat: 'yy-mm-dd' });
	filesizeLimit = 5120000;
	
// 	alert('test 1');
	if (typeof SWFUpload != "undefined") {
//     alert('test 2');
	  var swfu = new SWFUpload({
      flash_url : WPSC_URL+'/js/swfupload.swf',
      upload_url: base_url+'/?action=wpsc_add_image',
      button_placeholder_id : "spanButtonPlaceholder",
      button_width: 103,
      button_height: 24,
      button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
      button_cursor: SWFUpload.CURSOR.HAND,
      post_params: {"prodid" : 0},
      file_queue_limit : 1,
      file_size_limit : filesizeLimit+'b',
      file_types : "*.jpg;*.jpeg;*.png;*.gif",
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
  	jQuery("#add-product-image").click(function(){
      swfu.selectFiles();
  	});
});

function addlayer(){
	jQuery("tr.addlayer").before("<tr><td><input type='text' name='layer[]'> and above</td><td><input type='text' name='shipping[]'>&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onclick='removelayer()'><img src='../wp-content/plugins/wp-shopping-cart/images/delete.png'></a></td></tr>");
}

function addweightlayer(){
	jQuery("tr.addlayer").before("<tr><td><input type='text' name='weight_layer[]'> and above</td><td><input type='text' name='weight_shipping[]'>&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onclick='removelayer()'><img src='../wp-content/plugins/wp-shopping-cart/images/delete.png'></a></td></tr>");
}

function removelayer() {
	this.parent.parent.innerHTML='';
}

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
  jQuery("span.swfupload_loadingindicator").css('visibility', 'visible');
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
  /* alert(results) */;
  	//Don't delete, initiate id is neccesary.
  	
    jQuery("span.swfupload_loadingindicator").css('visibility', 'hidden');
  	var id = null;
	eval(results);
	
	if (id == null ) {
	  	if(replacement_src != null) {
	  	  	jQuery("li.first div.previewimage a.thickbox").attr('href', replacement_src);
	  	  	jQuery("li.first div.previewimage a.thickbox img.previewimage").attr('src', replacement_src);
	  	} else {
        if (jQuery('#gold_present').val() != '1') {
          jQuery('#add-product-image').remove();
        }
        jQuery(this.sorting).attr({'value':src});
        var img = jQuery('<div class="previewimage" id="'+id+'"><a href="'+WPSC_IMAGE_URL+src+'" rel="product_extra_image_'+id+'" class="thickbox"><img src="'+WPSC_IMAGE_URL+src+'" width="60" height="60" class="previewimage" /></a></div>').appendTo(this.targetHolder).hide();
        set = jQuery("#gallery_list").sortable('toArray');

        jQuery('#gallery_image_0').append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
        jQuery('#gallery_image_0').parent('li').addClass('first');
        jQuery('#gallery_image_0').parent('li').attr('id', 0);
        jQuery('#gallery_image_0 img.deleteButton').remove();
        enablebuttons();
		}
	} else {
		//jQuery(this.targetHolder).attr({'id':'image-'+src});
		jQuery(this.targetHolder).attr({'id':id});
		div_id = 'gallery_image_'+id;
		jQuery(this.targetHolder).html('');
		var img = jQuery('<div class="previewimage" id="'+div_id+'"><input type="hidden" name="images[]" value="'+src+'"><a href="'+WPSC_IMAGE_URL+src+'" rel="product_extra_image_'+id+'" class="thickbox"><img src="'+WPSC_IMAGE_URL+src+'" width="60" height="60" class="previewimage" /></a></div>').appendTo(this.targetHolder).hide();
		
    jQuery('#gallery_image_0').append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
    jQuery('#gallery_image_0').parent('li').addClass('first');
    jQuery('#gallery_image_0').parent('li').attr('id', 0);
    jQuery('#gallery_image_0 img.deleteButton').remove();
    
    
    if (jQuery('#gallery_list li').size() > 1) {
      jQuery('#gallery_list').sortable('refresh');
    } else {
      jQuery('#gallery_list').sortable();
		}
    set = jQuery("#gallery_list").sortable('toArray');
    order = set.join(',');
    prodid = jQuery('#prodid').val();
    if(prodid == null) {
      prodid = 0;
    }
    
      function imageorderresults(results) {
        eval(results);
        jQuery('#gallery_image_'+ser).append(output);
        enablebuttons();
      }
    
    ajax.post("index.php",imageorderresults,"ajax=true&prodid="+prodid+"&imageorder=true&order="+order+"");
    
    
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
	if (jQuery('#gallery_list li').size() > 1)
		jQuery('#gallery_list').sortable('refresh');
	else
		jQuery('#gallery_list').sortable();
}

function imageQueueComplete (uploads) {

}

function enablebuttons(){
	jQuery("img.deleteButton").click(
		function(){
			var r=confirm("Please confirm deletion");
			if (r==true) {
				img_id = jQuery(this).parent().parent('li').attr('id');
				jQuery(this).parent().parent('li').remove();
				ajax.post("index.php",noresults,"ajax=true&del_img=true&del_img_id="+img_id);
			}
		 }
	);
	
	jQuery("a.delete_primary_image").click(
		function(){
			var r=confirm("Please confirm deletion");
			if (r==true) {
				img_id = jQuery(this).parents('li.first').attr('id');
				//ajax.post("index.php",noresults,"ajax=true&del_img=true&del_img_id="+img_id);
				jQuery(this).parents('li.first').remove();
				
				
				
				set = jQuery("#gallery_list").sortable('toArray');
				jQuery('#gallery_image_'+set[0]).children('img.deleteButton').remove();
				jQuery('#gallery_image_'+set[0]).append("<a class='editButton'>Edit   <img src='"+WPSC_URL+"/images/pencil.png'/></a>");
				jQuery('#gallery_image_'+set[0]).parent('li').addClass('first');
				jQuery('#gallery_image_'+set[0]).parent('li').attr('id', 0);
				for(i=1;i<set.length;i++) {
					jQuery('#gallery_image_'+set[i]).children('a.editButton').remove();
					jQuery('#gallery_image_'+set[i]).append("<img alt='-' class='deleteButton' src='"+WPSC_URL+"/images/cross.png'/>");
					
          //alert(jQuery('#gallery_image_'+set[i]).parent('li').attr('id'));
					  //alert(element_id);
					if(element_id == 0) {
            jQuery('#gallery_image_'+set[i]).parent('li').attr('id', img_id);
					}
				}
				order = set.join(',');
				prodid = jQuery('#prodid').val();
				ajax.post("index.php",imageorderresults,"ajax=true&prodid="+prodid+"&imageorder=true&order="+order+"&delete_primary=true");
				
				jQuery(this).parents('li.first').attr('id', '0');
			}
      return false;
		 }
	);

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
	
	jQuery("a.editButton").click(
		function(){
			jQuery(this).hide();
			jQuery('#image_settings_box').show('fast');
		}
	);
	
	jQuery("a.closeimagesettings").click(
		function (e) {
			jQuery("div#image_settings_box").hide();
		}
	);
	
  function imageorderresults(results) {
    eval(results);
    jQuery('#gallery_image_'+ser).append(output);
    enablebuttons();
  }
}

function reloadresults(){
	window.location = window.location.href;
}

jQuery(document).ready(function(){
	jQuery("#table_rate_price").click(
		function() {
			if (this.checked) {
				jQuery("#table_rate").slideDown("fast");
			} else {
				jQuery("#table_rate").slideUp("fast");
			}
		}
	);
	jQuery("#add_label").click(
		function(){
			jQuery("#labels").append("<br><table><tr><td>"+TXT_WPSC_LABEL+" :</td><td><input type='text' name='productmeta_values[labels][]'></td></tr><tr><td>"+TXT_WPSC_LABEL_DESC+" :</td><td><textarea name='productmeta_values[labels_desc][]'></textarea></td></tr><tr><td>"+TXT_WPSC_LIFE_NUMBER+" :</td><td><input type='text' name='productmeta_values[life_number][]'></td></tr><tr><td>"+TXT_WPSC_ITEM_NUMBER+" :</td><td><input type='text' name='productmeta_values[item_number][]'></td></tr><tr><td>"+TXT_WPSC_PRODUCT_CODE+" :</td><td><input type='text' name='productmeta_values[product_code][]'></td></tr><tr><td>"+TXT_WPSC_PDF+" :</td><td><input type='file' name='productmeta_values[product_pdf][]'></td></tr></table>");
		}
	);
	jQuery(".add_level").click(
		function() {
			added = jQuery(this).parent().children('table').append('<tr><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][quantity][]"/> and above</td><td><input type="text" size="10" value="" name="productmeta_values[table_rate_price][table_price][]"/></td></tr>');
		}
	);
	
	jQuery(".file_delete_button").click(
		function() {
			jQuery(this).parent().remove();
			file_hash = jQuery(this).siblings("input").val();
			ajax.post("index.php",noresults,"ajax=true&del_file=true&del_file_hash="+file_hash);
		}
	);
	
  jQuery(".pricedisplay").each(
		function () {
			jQuery(this).attr("id",jQuery(this).parent().attr('id'));
		}
	);
 	jQuery(".pricedisplay").editable(base_url+"/?inline_price=true", {
         indicator : "Saving...",
         tooltip   : 'Click to edit...'
    });
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
	    	jQuery('.meta-box-sortables').each( function() {
	    		postVars["order[" + this.id.split('-')[0] + "]"] = jQuery(this).sortable( 'toArray' ).join(',');
	    	} );
	    	jQuery.post( base_url+'/?ajax=true', postVars, function() {
	    		postboxes.expandSidebar();
	    	} );
	    }
	} );
});




function wpsc_upload_switcher(target_state) {
  switch(target_state) {
    case 'flash':
    jQuery("table.browser-image-uploader").css("display","none");
    jQuery("table.flash-image-uploader").css("display","block");
    ajax.post("index.php",noresults,"ajax=true&save_image_upload_state=true&image_upload_state=1");
    break;
    
    case 'browser':
    jQuery("table.flash-image-uploader").css("display","none");
    jQuery("table.browser-image-uploader").css("display","block");
    ajax.post("index.php",noresults,"ajax=true&save_image_upload_state=true&image_upload_state=0");
    break;
  }
}


function image_resize_extra_forms(option) {
  container = jQuery(option).parent();
  jQuery("div.image_resize_extra_forms").css('display', 'none');
  jQuery("div.image_resize_extra_forms",container).css('display', 'block');

}

function open_variation_settings(element_id) {
  jQuery("tr#"+element_id+" td div.variation_settings").toggle();
  return false;
}

