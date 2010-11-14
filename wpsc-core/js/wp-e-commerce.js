// This is the wp-e-commerce front end javascript "library"
// empty the cart using ajax when the form is submitted,
function check_make_purchase_button(){
	toggle = jQuery('#noca_gateway').attr('checked');
	if(toggle == true){
		//jQuery('.make_purchase').hide();
		jQuery('#OCPsubmit').show();
	}else{
		jQuery('.make_purchase').show();
		jQuery('#OCPsubmit').hide();
	}
}
// this function is for binding actions to events and rebinding them after they are replaced by AJAX
// these functions are bound to events on elements when the page is fully loaded.
jQuery(document).ready(function () {

	//this bit of code runs on the checkout page. If the checkbox is selected it copies the valus in the billing country and puts it in the shipping country form fields. 23.07.09
	//jQuery('.wpsc_shipping_forms').hide();
	jQuery("#shippingSameBilling").click(function(){

		// If checked
		jQuery("#shippingSameBilling").livequery(function(){

			if(jQuery(this).is(":checked")){
				var fname = jQuery("input[title='billingfirstname']").val();
				var lname = jQuery("input[title='billinglastname']").val();
				var addr = jQuery("textarea[title='billingaddress']").val();
				var city = jQuery("input[title='billingcity']").val();
				var pcode = jQuery("input[title='billingpostcode']").val();
				var phone = jQuery("input[title='billingphone']").val();
				var email = jQuery("input[title='billingfirstname']").val();
				var state = jQuery("select[title='billingregion'] :selected").text();
				if( jQuery("select[title='billingstate']").val() ){
					var state = jQuery("select[title='billingstate'] :selected").text();
				}
				if( jQuery("input[title='billingstate']").val()){
					var state = jQuery("input[title='billingstate']").val();
				}
				var country = jQuery("select[title='billingcountry'] :selected").text();
				var countryID = jQuery("select[title='billingcountry'] :selected").val();

				jQuery("input[title='shippingfirstname']").val(fname).removeClass('intra-field-label');
				jQuery("input[title='shippinglastname']").val(lname).removeClass('intra-field-label');
				jQuery("textarea[title='shippingaddress']").val(addr).removeClass('intra-field-label');
				jQuery("input[title='shippingcity']").val(city).removeClass('intra-field-label');
				jQuery("input[title='shippingpostcode']").val(pcode).removeClass('intra-field-label');
				jQuery("input[title='shippingphone']").val(phone).removeClass('intra-field-label');
				jQuery("input[title='shippingemail']").val(email).removeClass('intra-field-label');
				jQuery("input[title='shippingstate']").val(state).removeClass('intra-field-label');
				jQuery("input.shipping_country").val(countryID).removeClass('intra-field-label');
				jQuery("span.shipping_country_name").html(country).removeClass('intra-field-label');
				jQuery("select#current_country").val(countryID).removeClass('intra-field-label');

				jQuery("select[title='shippingcountry']").val(countryID).removeClass('intra-field-label');
				var html_form_id = jQuery("select[title='shippingcountry']").attr('id');
				var form_id =  jQuery("select[title='shippingcountry']").attr('name');
				form_id = form_id.replace("collected_data[", "");
				form_id = form_id.replace("]", "");
				form_id = form_id.replace("[0]", "");
				set_shipping_country(html_form_id, form_id)
				if(jQuery("select[title='billingcountry'] :selected").val() != jQuery("select[name='country'] :selected").val()){
					id = jQuery("select[name='country'] :selected").val();
					if(id == 'undefined'){
						jQuery("select[name='country']").val(countryID);
						submit_change_country();
					}
				}

			} else {

			}

			//otherwise, hide it
			//jQuery("#extra").hide("fast");
		});
	});

	// Submit the product form using AJAX
	jQuery("form.product_form").submit(function() {
		// we cannot submit a file through AJAX, so this needs to return true to submit the form normally if a file formfield is present
		file_upload_elements = jQuery.makeArray(jQuery('input[type=file]', jQuery(this)));
		if(file_upload_elements.length > 0) {
			return true;
		} else {
			form_values = jQuery(this).serialize();
			// Sometimes jQuery returns an object instead of null, using length tells us how many elements are in the object, which is more reliable than comparing the object to null
			if(jQuery('#fancy_notification').length == 0) {
				jQuery('div.wpsc_loading_animation',this).css('visibility', 'visible');
			}
			jQuery.post( 'index.php?ajax=true', form_values, function(returned_data) {
				eval(returned_data);
				jQuery('div.wpsc_loading_animation').css('visibility', 'hidden');

				if(jQuery('#fancy_notification') != null) {
					jQuery('#loading_animation').css("display", 'none');
				//jQuery('#fancy_notificationimage').css("display", 'none');
				}

			});
			wpsc_fancy_notification(this);
			return false;
		}
	});


	jQuery('a.wpsc_category_link, a.wpsc_category_image_link').click(function(){
		product_list_count = jQuery.makeArray(jQuery('ul.category-product-list'));
		if(product_list_count.length > 0) {
			jQuery('ul.category-product-list', jQuery(this).parent()).toggle();
			return false;
		}
	});

	//  this is for storing data with the product image, like the product ID, for things like dropshop and the the ike.
	jQuery("form.product_form").livequery(function(){
		product_id = jQuery('input[name=product_id]',this).val();
		image_element_id = 'product_image_'+product_id;
		jQuery("#"+image_element_id).data("product_id", product_id);
		parent_container = jQuery(this).parents('div.product_view_'+product_id);
		jQuery("div.item_no_image", parent_container).data("product_id", product_id);
	});
	//jQuery("form.product_form").trigger('load');

	// Toggle the additional description content
	jQuery("a.additional_description_link").click(function() {
		parent_element = jQuery(this).parent(".additional_description_container, .additional_description_span");
		jQuery('.additional_description',parent_element).slideToggle('fast');
		return false;
	});

	// update the price when the variations are altered.
	jQuery(".wpsc_select_variation").change(function() {
		jQuery('option[value="0"]', this).attr('disabled', 'disabled');
		parent_form = jQuery(this).parents("form.product_form");
		form_values =jQuery("input[name=product_id], .wpsc_select_variation",parent_form).serialize( );

		jQuery.post( 'index.php?update_product_price=true', form_values, function(returned_data) {
			product_msg = '';
			message_exists = null;
			eval(returned_data);
			if(product_id != null) {
				if(product_msg != ''){
					jQuery("input#product_"+product_id+"_submit_button").attr('disabled','disabled');
					message_exists = jQuery(".wpsc_variation_message_"+product_id).html();
					if(message_exists == null){
						jQuery("input#product_"+product_id+"_submit_button").before('<span class="wpsc_variation_message_'+product_id+'"">'+product_msg+'</span>');
					}else{
						jQuery(".wpsc_variation_message_"+product_id).css('display','inline');
					}
				}else{
					jQuery("input#product_"+product_id+"_submit_button").removeAttr('disabled');
					jQuery('.wpsc_variation_message_'+product_id).css('display','none');
				}
				target_id = "product_price_"+product_id;
				second_target_id = "donation_price_"+product_id;
				third_target_id = "old_product_price_"+product_id;
				yousave_target_id = "yousave_"+product_id;
				buynow_id = "BB_BuyButtonForm"+product_id;
				if(jQuery("input#"+target_id).attr('type') == 'text') {
					jQuery("input#"+target_id).val(numeric_price);
				} else {
					jQuery("#"+target_id+".pricedisplay").html(price);
					jQuery("#"+third_target_id).html(old_price);
					jQuery("#"+yousave_target_id).html(you_save);
				}
				jQuery("input#"+second_target_id).val(numeric_price);
			}
		});
		return false;
	});

	// Object frame destroying code.
	jQuery("div.shopping_cart_container").livequery(function(){
		object_html = jQuery(this).html();
		window.parent.jQuery("div.shopping-cart-wrapper").html(object_html);
	});


	// Ajax cart loading code.
	jQuery("div.wpsc_cart_loading").livequery(function(){
		form_values = "ajax=true"
		jQuery.post( 'index.php?wpsc_ajax_action=get_cart', form_values, function(returned_data) {
			eval(returned_data);
		});
	});

	// Object frame destroying code.
	jQuery("form.wpsc_product_rating").livequery(function(){
		jQuery(this).rating();
	});

	jQuery("form.wpsc_empty_the_cart").livequery(function(){
		jQuery(this).submit(function() {
			form_values = "ajax=true&";
			form_values += jQuery(this).serialize();
			jQuery.post( 'index.php', form_values, function(returned_data) {
				eval(returned_data);
			});
			return false;
		});
	});

	jQuery("form.wpsc_empty_the_cart a.emptycart").live('click',function(){
		parent_form = jQuery(this).parents("form.wpsc_empty_the_cart");
		form_values = "ajax=true&";
		form_values += jQuery(parent_form).serialize();
		jQuery.post( 'index.php', form_values, function(returned_data) {
			eval(returned_data);
		});
		return false;
	});

	//Shipping bug fix by James Collins
	var radios = jQuery(".productcart input:radio[name=shipping_method]");
	if (radios.length == 1) {
		// If there is only 1 shipping quote available during checkout, automatically select it
		jQuery(radios).click();
	} else if (radios.length > 1) {
		// There are multiple shipping quotes, simulate a click on the checked one
		jQuery(".productcart input:radio[name=shipping_method]:checked").click();
	}
});

// update the totals when shipping methods are changed.
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

// submit the country forms.
function submit_change_country(){
	document.forms.change_country.submit();
}

// submit the fancy notifications forms.
function wpsc_fancy_notification(parent_form){
	if(typeof(WPSC_SHOW_FANCY_NOTIFICATION) == 'undefined'){
		WPSC_SHOW_FANCY_NOTIFICATION = true;
	}
	if((WPSC_SHOW_FANCY_NOTIFICATION == true) && (jQuery('#fancy_notification') != null)){
		var options = {
			margin: 1 ,
			border: 1 ,
			padding: 1 ,
			scroll: 1
		};

		form_button_id = jQuery(parent_form).attr('id') + "_submit_button";
		//console.log(form_button_id);
		//return;
		var container_offset = {};
		new_container_offset = jQuery('#products_page_container').offset(options, container_offset);

		if(container_offset['left'] == null) {
			container_offset['left'] = new_container_offset.left;
			container_offset['top'] = new_container_offset.top;
		}

		var button_offset = {};
		new_button_offset = jQuery('#'+form_button_id).offset(options, button_offset)

		if(button_offset['left'] == null) {
			button_offset['left'] = new_button_offset.left;
			button_offset['top'] = new_button_offset.top;
		}

		jQuery('#fancy_notification').css("left", (button_offset['left'] - container_offset['left'] + 10) + 'px');
		jQuery('#fancy_notification').css("top", ((button_offset['top']  - container_offset['top']) -60) + 'px');


		jQuery('#fancy_notification').css("display", 'block');
		jQuery('#loading_animation').css("display", 'block');
		jQuery('#fancy_notification_content').css("display", 'none');
	}
}

function shopping_cart_collapser() {
	switch(jQuery("#sliding_cart").css("display")) {
		case 'none':
			jQuery("#sliding_cart").slideToggle("fast",function(){
				jQuery.post( 'index.php', "ajax=true&set_slider=true&state=1", function(returned_data) { });
				jQuery("#fancy_collapser").attr("src", (WPSC_CORE_IMAGES_URL + "/minus.png"));
			});
			break;

		default:
			jQuery("#sliding_cart").slideToggle("fast",function(){
				jQuery.post( 'index.php', "ajax=true&set_slider=true&state=0", function(returned_data) { });
				jQuery("#fancy_collapser").attr("src", (WPSC_CORE_IMAGES_URL + "/plus.png"));
			});
			break;
	}
	return false;
}

function set_billing_country(html_form_id, form_id){
	var billing_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();
	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		billing_region = "&billing_region="+region;
	}

	form_values = "wpsc_ajax_action=change_tax&form_id="+form_id+"&billing_country="+country+billing_region;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
	});
}
function set_shipping_country(html_form_id, form_id){
	var shipping_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();

	if(country == 'undefined'){
		country =  jQuery("select[title='billingcountry']").val();
	}

	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		shipping_region = "&shipping_region="+region;
	}

	form_values = "wpsc_ajax_action=change_tax&form_id="+form_id+"&shipping_country="+country+shipping_region;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
	});
//ajax.post("index.php",changetaxntotal,("ajax=true&form_id="+form_id+"&billing_country="+country+billing_region));
}

jQuery(document).ready(function(){
	jQuery('.wpsc_checkout_table input, .wpsc_checkout_table textarea').each(function(){
		var real_value = jQuery(this).val();
		value = jQuery('label[for="'+jQuery(this).attr('id')+'"]').html();
		if(null != value){
			value = value.replace(/\*/i,'');
		}
		jQuery(this).inlineFieldLabel({label:jQuery.trim(value)});
		if(real_value != '')
			jQuery(this).val(real_value).removeClass('intra-field-label');
	});
});

//Javascript for variations: bounce the variatio box when nothing is selected and return false for add to cart button.
jQuery(document).ready(function(){
	jQuery('.productcol, .textcol').each(function(){
		jQuery('.wpsc_buy_button', this).click(function(){
			jQuery(this).parents('form:first').find('select.wpsc_select_variation').each(function(){
				if(jQuery(this).val() <= 0)
					jQuery(this).css('position','relative');
					jQuery(this).animate({'left': '-=5px'}, 50, function(){
						jQuery(this).animate({'left': '+=10px'}, 100, function(){
							jQuery(this).animate({'left': '-=10px'}, 100, function(){
								jQuery(this).animate({'left': '+=10px'}, 100, function(){
									jQuery(this).animate({'left': '-=5px'}, 50);
								});
							});
						});
					});
			});
			if(jQuery(this).parents('form:first').find('select.wpsc_select_variation[value=0]:first').length)
				return false;
		});
	});
});

jQuery(document).ready(function(){
	jQuery('.attachment-gold-thumbnails').click(function(){
		jQuery(this).parents('.imagecol:first').find('.product_image').attr('src', jQuery(this).parent().attr('rev'));
		jQuery(this).parents('.imagecol:first').find('.product_image').parent('a:first').attr('href', jQuery(this).parent().attr('href'));
		return false;
	});
});