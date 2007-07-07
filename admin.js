if(typeof(select_min_height) == undefined)
  {
  var select_min_height = 0;
  var select_max_height = 200;
  }

jQuery(document).ready(
  function()
  {  
  jQuery('div.select_product_file').Resizable({
    minWidth: 300,
    minHeight: select_min_height,
    maxWidth: 300,
    maxHeight: select_max_height,
    handlers: {
      s: '.select_product_handle'
      }
    });
  }
);

function activate_resizable()
  {
  jQuery('div.edit_select_product_file').Resizable({
    minWidth: 300,
    minHeight: select_min_height,
    maxWidth: 300,
    maxHeight: select_max_height,
    handlers: {
      s: '.edit_select_product_handle'
      }
    });
  }
  
function categorylist(url)
  {
  self.location = url;
  }
  
function submit_change_country()
  {
  document.cart_options.submit();
  //document.cart_options.submit();
  }
  
var getresults=function(results)
  {
  document.getElementById('formcontent').innerHTML = results;
  document.getElementById('additem').style.display = 'none';
  document.getElementById('productform').style.display = 'block';
  document.getElementById('loadingindicator_span').style.display = 'none';
  activate_resizable();
  TB_init();
  }  

function filleditform(prodid)
   {
   ajax.post("index.php",getresults,"ajax=true&admin=true&prodid="+prodid);
   document.getElementById('loadingimage').src = '../wp-content/plugins/wp-shopping-cart/images/indicator.gif';
   document.getElementById('loadingindicator_span').style.display = 'inline';
   }
   
function fillvariationform(variation_id)
  {
  ajax.post("index.php",getresults,"ajax=true&admin=true&variation_id="+variation_id);
  document.getElementById('loadingimage').src = '../wp-content/plugins/wp-shopping-cart/images/indicator.gif';
  document.getElementById('loadingindicator_span').style.display = 'inline';
  }
   
function showaddform()
   {
   document.getElementById('productform').style.display = 'none';
   document.getElementById('additem').style.display = 'block';
   return false;
   }
   
   
function fillcategoryform(catid)
   {
   ajax.post("index.php",getresults,"ajax=true&admin=true&catid="+catid);
   }

function fillbrandform(catid)
   {
   ajax.post("index.php",getresults,"ajax=true&admin=true&brandid="+catid);
   }

var gercurrency=function(results)
  {
  document.getElementById('cslchar1').innerHTML = results;
  document.getElementById('cslchar2').innerHTML = results;
  document.getElementById('cslchar3').innerHTML = results;
  document.getElementById('cslchar4').innerHTML = results;
  }

function getcurrency(id)
   {
   ajax.post("index.php",gercurrency,"ajax=true&currencyid="+id);
   }
  
function country_list(id)
  {
  var country_list=function(results)
    {
    document.getElementById('options_region').innerHTML = results;
    }
  ajax.post("index.php",country_list,"ajax=true&get_country_tax=true&country_id="+id);
  }     
  
function hideelement(id)
  {
  state = document.getElementById(id).style.display;
  //alert(document.getElementById(id).style.display);
  if(state != 'block')
    {
    document.getElementById(id).style.display = 'block';
    }
    else
      {
      document.getElementById(id).style.display = 'none';
      }
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




function checkimageresize()
   {
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
    new_element_contents += "<input type='text' name='new_variation_values[]' value='' />";
    }
    else
      {
      new_element_contents += "<input type='text' name='variation_values[]' value='' />";
      }
  new_element_contents += " <a class='image_link' href='#' onclick='remove_variation_value_field(\""+new_element_id+"\")'><img src='../wp-content/plugins/wp-shopping-cart/images/trash.gif' alt='"+TXT_WPSC_DELETE+"' title='"+TXT_WPSC_DELETE+"' /></a><br />";
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
  
function variation_value_list(id)
  {
  var display_list=function(results)
    {
    eval(results);
    if(variation_value_html != '')
      {
      new_element_id = "product_variations_"+variation_value_id;
      if(document.getElementById(new_element_id) === null)
        {
        new_element = document.createElement('span');
        new_element.id = new_element_id;
        document.getElementById("edit_product_variations").appendChild(new_element);
        document.getElementById(new_element_id).innerHTML = variation_value_html;
        }
      }
    }
  ajax.post("index.php",display_list,"ajax=true&list_variation_values=true&variation_id="+id+"&prefix=edit_product_variations");
  }
  

  
function add_variation_value_list(id)
  {
  var display_list=function(results)
    {    
    eval(results);
    if(variation_value_html != '')
      {
      new_element_id = "add_product_variations_"+variation_value_id;
      if(document.getElementById(new_element_id) === null)
        {
        new_element = document.createElement('span');
        new_element.id = new_element_id;
        document.getElementById("add_product_variations").appendChild(new_element);
        document.getElementById(new_element_id).innerHTML = variation_value_html;
        }
      }
    }
  ajax.post("index.php",display_list,"ajax=true&list_variation_values=true&variation_id="+id+"&prefix=add_product_variations");
  }
  
function remove_variation_value_list(prefix,id)
  {
  if(prefix == "edit_product_variations")
    {
    target_element_id = "product_variations_"+id;
    }
    else
      {
      target_element_id = prefix+"_"+id;
      }
  target_element = document.getElementById(target_element_id);
  document.getElementById(prefix).removeChild(target_element);  
  return false;
  }
  
function tick_active(target_id,input_value)
  {
  if(input_value != '')
    {
    document.getElementById(target_id).checked = true;
    }
  }
  
function add_form_field()
  {
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
  new_element_contents += "<td  style='text-align: center; width: 12px;'><a class='image_link' href='#' onclick='return remove_new_form_field(\""+new_element_id+"\");'><img src='../wp-content/plugins/wp-shopping-cart/images/trash.gif' alt='"+TXT_WPSC_DELETE+"' title='"+TXT_WPSC_DELETE+"' /></a></td>\n\r";
  new_element_contents += "<td></td>\n\r";
  new_element_contents += "</tr></table>";
  
  new_element = document.createElement('div');
  new_element.id = new_element_id;
   
  document.getElementById("form_field_form_container").appendChild(new_element);
  document.getElementById(new_element_id).innerHTML = new_element_contents;
  return false;
  }
  
function remove_new_form_field(id)
  {
  element_count = document.getElementById("form_field_form_container").childNodes.length;
  if(element_count > 1)
    {
    target_element = document.getElementById(id);
    document.getElementById("form_field_form_container").removeChild(target_element);
    }
  return false;
  }
  
function remove_form_field(id,form_id)
  {
  var delete_variation_value=function(results)
    {
    }
  element_count = document.getElementById("form_field_form_container").childNodes.length;
  if(element_count > 1)
    {
    ajax.post("index.php",delete_variation_value,"ajax=true&remove_form_field=true&form_id="+form_id);
    target_element = document.getElementById(id);
    document.getElementById("form_field_form_container").removeChild(target_element);
    }
  return false;
  }  
  
function show_status_box(id,image_id)
  {
  state = document.getElementById(id).style.display; 
  if(state != 'block')
    {
    document.getElementById(id).style.display = 'block';
    document.getElementById(image_id).src = '../wp-content/plugins/wp-shopping-cart/images/icon_window_collapse.gif';
    }
    else
      {
      document.getElementById(id).style.display = 'none';
      document.getElementById(image_id).src = '../wp-content/plugins/wp-shopping-cart/images/icon_window_expand.gif';
      }
  return false;
  }
  
function submit_status_form(id)
  {
  document.getElementById(id).submit();
  }
  
// pe.{
var prevElement = null;
var prevOption = null;

function hideOptionElement(id, option)
{
  if (prevOption == option)
  {
    return;
  }
  
  if (prevElement != null)
  {
    prevElement.style.display = "none";
  }
  
  if (id == null)
  {
    prevElement = null;
  }
  else
  {
    prevElement = document.getElementById(id);
    prevElement.style.display = "block";
  }
  prevOption = option;
}

// }.pe  

function toggle_display_options(state)
  {
  switch(state)
    {
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
  
function log_submitform(id)
  {    
  var get_log_results=function(results)
    {
    eval(results);
    }
  frm = document.getElementById(id);
  ajax.post("index.php?ajax=true&log_state=true",get_log_results,ajax.serialize(frm));
  return false;
  }