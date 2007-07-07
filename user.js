var testsuccess = 0;
var lnid = new Array();
function categorylist(url)
  {
  self.location = url;
  }
 
var noresults=function(results)
  {
  return true;
  }
  
var getresults=function(results)
  {
  eval(results);
  if(window.drag_and_drop_cart_updater)
    {
     drag_and_drop_cart_updater();
    }
  if(document.getElementById('loadingimage') != null)
    {
    document.getElementById('loadingindicator').style.visibility = 'hidden';
    }
    else if(document.getElementById('alt_loadingimage') != null)
    {
    document.getElementById('alt_loadingindicator').style.visibility = 'hidden';
    }
  if((document.getElementById('sliding_cart') != null) && (document.getElementById('sliding_cart').style.display == 'none'))
    {
    //alert(base_url+"/wp-content/plugins/wp-shopping-cart/images/minus.png");
    jQuery("#fancy_collapser").attr("src", (base_url+"/wp-content/plugins/wp-shopping-cart/images/minus.png"));
    jQuery("#sliding_cart").show("fast",function(){
    ajax.post("index.php",noresults,"ajax=true&set_slider=true&state=1"); });
    }
  if(document.getElementById('fancy_notification') != null)
    {
    jQuery('#loading_animation').css("display", 'none');
    //jQuery('#fancy_notificationimage').css("display", 'none');  
    }
  }
  
function set_billing_country(html_form_id, form_id)
  {
  var billing_region = '';
  country = jQuery(("div#"+html_form_id+" select[@class=current_country]")).val();  
  region = jQuery(("div#"+html_form_id+" select[@class=current_region]")).val();
//   alert(region);
  if(/[\d]{1,6}/.test(region)) // number over 6 digits for a region ID? yeah right, not in the lifetime of this code
    {
    billing_region = "&billing_region="+region;
    }  
  ajax.post("index.php",getresults,("ajax=true&form_id="+form_id+"&billing_country="+country+billing_region));
  }

function submitform(frm, show_notification)
  {
  if(show_notification != false)
    {
    show_notification = true;
    }
  //alert(ajax.serialize(frm));
  ajax.post("index.php?ajax=true&user=true",getresults,ajax.serialize(frm));
  if(document.getElementById('loadingimage') != null)
    {
    document.getElementById('loadingimage').src = base_url+'/wp-content/plugins/wp-shopping-cart/images/indicator.gif';
    document.getElementById('loadingindicator').style.visibility = 'visible';
    } 
    else if(document.getElementById('alt_loadingimage') != null)
    {
    document.getElementById('alt_loadingimage').src = base_url+'/wp-content/plugins/wp-shopping-cart/images/indicator.gif';
    document.getElementById('alt_loadingindicator').style.visibility = 'visible';
    }     
  if((show_notification == true) && (document.getElementById('fancy_notification') != null))
    {
    var options = {
      margin: 1 ,
      border: 1 ,
      padding: 1 ,
      scroll: 1 
      };
        
    form_button_id = frm.id + "_submit_button";
    //alert(form_button_id);
    
    var container_offset = {};
    jQuery('#products_page_container').offset(options, container_offset);
    
    var button_offset = {};
    jQuery('#'+form_button_id).offset(options, button_offset);
    //alert(offset['left'] + " " + offset['top']);
    jQuery('#fancy_notification').css("left", (button_offset['left'] - container_offset['left'] + 10) + 'px');
    jQuery('#fancy_notification').css("top", ((button_offset['top']  - container_offset['top']) -60) + 'px');
    jQuery('#fancy_notification').css("display", 'block');
    jQuery('#loading_animation').css("display", 'block');
    jQuery('#fancy_notification_content').css("display", 'none');  
    }
  return false;
  }

function emptycart()
  {
  ajax.post("index.php",getresults,"ajax=true&user=true&emptycart=true");
  if(document.getElementById('loadingimage') != null)
    {
    document.getElementById('loadingimage').src = base_url+'/wp-content/plugins/wp-shopping-cart/images/indicator.gif';
    document.getElementById('loadingindicator').style.visibility = 'visible';
    } 
    else if(document.getElementById('alt_loadingimage') != null)
    {
    document.getElementById('alt_loadingimage').src = base_url+'/wp-content/plugins/wp-shopping-cart/images/indicator.gif';
    document.getElementById('alt_loadingindicator').style.visibility = 'visible';
    }    
  }

function show_additional_description(id,image_id)
  {
  currentstate = document.getElementById(id).style.display;
  //document.getElementById(id).style.display = 'inline';
  if(currentstate != 'inline')
    {
    document.getElementById(id).style.display = 'inline';
    document.getElementById(image_id).src = base_url+'/wp-content/plugins/wp-shopping-cart/images/icon_window_collapse.gif';
    }
    else
      {
      document.getElementById(id).style.display = 'none';
      document.getElementById(image_id).src = base_url+'/wp-content/plugins/wp-shopping-cart/images/icon_window_expand.gif';
      }
  return false;
  }

function prodgroupswitch(state)
  {
  if(state == 'brands')
    {
    document.getElementById('categorydisplay').style.display = 'none';
    document.getElementById('branddisplay').style.display = 'block';
    }
    else if(state == 'categories')
      {
      document.getElementById('branddisplay').style.display = 'none';
      document.getElementById('categorydisplay').style.display = 'block';
      }
  return false;
  }
  
var previous_rating;
function ie_rating_rollover(id,state)
  {
  target_element = document.getElementById(id);
  switch(state)
    {
    case 1:
    previous_rating = target_element.style.background;
    target_element.style.background = "url("+base_url+"/wp-content/plugins/wp-shopping-cart/images/blue-star.gif)";
    break;
    
    default:
    if(target_element.style.background != "url("+base_url+"/wp-content/plugins/wp-shopping-cart/images/gold-star.gif)")
      {
      target_element.style.background = previous_rating;
      }
    break;
    }
  }  
  
var apply_rating=function(results)
  {
  outarr = results.split(",");
  //alert(results);
  for(i=1;i<=outarr[1];i++)
    {
    id = "star"+outarr[0]+"and"+i+"_link";
    document.getElementById(id).style.background = "url("+base_url+"/wp-content/plugins/wp-shopping-cart/images/gold-star.gif)";
    }
    
  for(i=5;i>outarr[1];i--)
    {
    id = "star"+outarr[0]+"and"+i+"_link";
    document.getElementById(id).style.background = "#c4c4b8";
    }
  lnid[outarr[0]] = 1; 
    
  rating_id = 'rating_'+outarr[0]+'_text';
  //alert(rating_id);
  if(document.getElementById(rating_id).innerHTML != "Your Rating:")
    {
    document.getElementById(rating_id).innerHTML = "Your Rating:";
    }
    
  saved_id = 'saved_'+outarr[0]+'_text';
  document.getElementById(saved_id).style.display = "inline";
  update_vote_count(outarr[0]);
  }
  
function hide_save_indicator(id)
  {
  document.getElementById(id).style.display = "none";
  }
  
function rate_item(prodid,rating)
  {
  ajax.post("index.php",apply_rating,"ajax=true&rate_item=true&product_id="+prodid+"&rating="+rating);
  }
  
function update_vote_count(prodid)
  {
  var update_vote_count=function(results)
    {
    outarr = results.split(",");
    vote_count = outarr[0];
    prodid = outarr[1];
    vote_count_id = 'vote_total_'+prodid;
    document.getElementById(vote_count_id).innerHTML = vote_count;
    }
  ajax.post("index.php",update_vote_count,"ajax=true&get_rating_count=true&product_id="+prodid);
  }
  

function submit_change_country()
  {
  document.forms.change_country.submit();
  }
  
function update_preview_url(prodid)
  {
  image_height = document.getElementById("image_height").value;
  image_width = document.getElementById("image_width").value;
  if(((image_height > 0) && (image_height <= 1024)) && ((image_width > 0) && (image_width <= 1024)))
    {
    new_url = "index.php?productid="+prodid+"&height="+image_height+"&width="+image_width+"";
    document.getElementById("preview_link").setAttribute('href',new_url);
    }
    else
      {
      new_url = "index.php?productid="+prodid+"";
      document.getElementById("preview_link").setAttribute('href',new_url);
      }
  return false;
  }
  
function change_variation(product_id, variation_ids, special)
  {
  value_ids = '';
  special_prefix = "";
  if(special == true)
    {
    form_id = "specials_"+product_id;
    }
    else
    {
    form_id = "product_"+product_id;
    }
  for(var i in variation_ids)
    {
    if(!isNaN(parseInt(i)))
      {
      variation_name = "variation["+variation_ids[i]+"]";
      value_ids += "&variation[]="+document.getElementById(form_id).elements[variation_name].value;
      }
    }
  if(special == true)
    {
    var return_price=function(results)
      {
      //alert(results);
      eval(results);
      if(product_id != null)
        {
        target_id = "special_product_price_"+product_id;
        document.getElementById(target_id).firstChild.innerHTML = price;
        }
      }
    }
    else
    {
    var return_price=function(results)
      {
      //alert(results);
      eval(results);
      if(product_id != null)
        {
        target_id = "product_price_"+product_id;
        document.getElementById(target_id).firstChild.innerHTML = price;
        }
      }
    }
  ajax.post("index.php",return_price,"ajax=true&get_updated_price=true&product_id="+product_id+value_ids);
  }
  
function shopping_cart_collapser()
  {
  switch(document.getElementById("sliding_cart").style.display)
    {
    case 'none':
    jQuery("#sliding_cart").toggle("fast",function(){
      ajax.post("index.php",noresults,"ajax=true&set_slider=true&state=1");
      jQuery("#fancy_collapser").attr("src", (base_url+"/wp-content/plugins/wp-shopping-cart/images/minus.png"));
      });
    break;
    
    default:
    jQuery("#sliding_cart").toggle("fast",function(){
      ajax.post("index.php",noresults,"ajax=true&set_slider=true&state=0");
      jQuery("#fancy_collapser").attr("src", (base_url+"/wp-content/plugins/wp-shopping-cart/images/plus.png"));
      });
    break;
    }
  return false;
  }
  
function show_details_box(id,image_id)
  {
  state = document.getElementById(id).style.display; 
  if(state != 'block')
    {
    document.getElementById(id).style.display = 'block';
    document.getElementById(image_id).src = base_url+"/wp-content/plugins/wp-shopping-cart/images/icon_window_collapse.gif";
    }
    else
      {
      document.getElementById(id).style.display = 'none';
      document.getElementById(image_id).src = base_url+"/wp-content/plugins/wp-shopping-cart/images/icon_window_expand.gif";
      }
  return false;
  }
  
var register_results=function(results)
  {  
  jQuery("div#TB_ajaxContent").html(results);
  jQuery('div#checkout_login_box').css("border", '1px solid #339933');
  jQuery('div#checkout_login_box').css("background-color", '#e8fcea');
  }
  
function submit_register_form(frm)
  {
  jQuery('img#register_loading_img').css("display", 'inline');
  ajax.post("index.php?ajax=true&action=register",register_results,ajax.serialize(frm));

  return false;
  }