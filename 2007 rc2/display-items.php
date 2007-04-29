<?php
$category_data = null;
function topcategorylist()
  {
  global $wpdb,$category_data;
  $siteurl = get_option('siteurl'); 
  $options = "";
  //$options .= "<option value=''>".TXT_WPSC_SELECTACATEGORY."</option>\r\n";
  $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' ORDER BY `id` ASC",ARRAY_A);
  $url = $siteurl."/wp-admin/admin.php?page=wp-shopping-cart/display-items.php";
  $options .= "<option value='$url'>".TXT_WPSC_ALLCATEGORIES."</option>\r\n";
  if($values != null)
    {
    foreach($values as $option)
      {
      $category_data[$option['id']] = $option['name'];
      if($_GET['catid'] == $option['id'])
        {
        $selected = "selected='selected'";
        }
      $options .= "<option $selected value='$url&amp;catid=".$option['id']."'>".stripslashes($option['name'])."</option>\r\n";
      $selected = "";
      }
    }
  $concat .= "<select name='category' onChange='categorylist(this.options[this.selectedIndex].value)'>".$options."</select>\r\n";
  return $concat;
  }

function brandslist($current_brand = '')
  {
  global $wpdb;
  $options = "";
  $options .= "<option  $selected value='0'>".TXT_WPSC_SELECTABRAND."</option>\r\n";
  $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `active`='1' ORDER BY `id` ASC",ARRAY_A);
  foreach($values as $option)
    {
    if($curent_category == $option['id'])
      {
      $selected = "selected='selected'";
       }
    $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
    $selected = "";
    }
  $concat .= "<select name='brand'>".$options."</select>\r\n";
  return $concat;
  }
  
function variationslist($current_variation = '')
    {
    global $wpdb;
    $options = "";
    //$options .= "<option value=''>".TXT_WPSC_SELECTACATEGORY."</option>\r\n";
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_variations` ORDER BY `id` ASC",ARRAY_A);
    $options .= "<option  $selected value='0'>".TXT_WPSC_ADD_ANOTHER_VARIATION."</option>\r\n";
    //$options .= "<option  $selected value='add'>".TXT_WPSC_NEW_VARIATION."</option>\r\n";
    if($values != null)
      {
      foreach($values as $option)
        {
        if($current_brand == $option['id'])
          {
          $selected = "selected='selected'";
          }
        $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
        $selected = "";
        }
      }
    $concat .= "<select name='variations' onChange='add_variation_value_list(this.options[this.selectedIndex].value)'>".$options."</select>\r\n";
    return $concat;
    }

/*
 * Makes the order changes
 */
if(is_numeric($_GET['catid']) && is_numeric($_GET['product_id']) && ($_GET['position_action'] != ''))
  {
  $position_cat_id = $_GET['catid'];
  $position_prod_id = $_GET['product_id'];
  $current_order_row = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('$position_prod_id') LIMIT 1;",ARRAY_A);
  $current_order_row = $current_order_row[0];
  switch($_GET['position_action'])
    {
    case 'top':   
    if($current_order_row['order'] > 0) 
      {
      $check_existing = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN('$position_cat_id') AND `order` IN('0') LIMIT 1;",ARRAY_A);
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '0' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('$position_prod_id') LIMIT 1;");
      if($check_existing != null)
        {
        $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = (`order` + 1) WHERE `category_id` IN('$position_cat_id') AND `product_id` NOT IN('$position_prod_id') AND `order` < '".$current_order_row['order']."'");
        }
      }
    break;
    
    case 'up':
    if($current_order_row['order'] > 0) 
      {
      $target_rows = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN ('".$position_cat_id."') AND `order` <= '".$current_order_row['order']."' ORDER BY `order` DESC LIMIT 2",ARRAY_A);
      //exit("<pre>".print_r($target_rows,true)."</pre>");
      if(count($target_rows) == 2)
        {
        $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[1]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[0]['product_id']."') LIMIT 1");
        $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[0]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[1]['product_id']."') LIMIT 1");
        }
      }
    break;
    
    case 'down':
    $target_rows = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN ('".$position_cat_id."') AND `order` >= '".$current_order_row['order']."' ORDER BY `order` ASC LIMIT 2",ARRAY_A);
    //exit("<pre>".print_r($target_rows,true)."</pre>");
    if(count($target_rows) == 2)
      {
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[1]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[0]['product_id']."') LIMIT 1");
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '".$target_rows[0]['order']."' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('".$target_rows[1]['product_id']."') LIMIT 1");
      }
    break;
    
    case 'bottom':
    $end_row = $wpdb->get_results("SELECT MAX( `order` ) AS `order` FROM `".$wpdb->prefix."product_order` WHERE `category_id` IN ('9') LIMIT 1",ARRAY_A);
    $end_order_number = $end_row[0]['order'];
    if($current_order_row['order'] < $end_order_number)
      {
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = '$end_order_number' WHERE `category_id` IN('$position_cat_id') AND `product_id` IN('$position_prod_id') LIMIT 1;");      
      $wpdb->query("UPDATE `".$wpdb->prefix."product_order` SET `order` = (`order` - 1) WHERE `category_id` IN('$position_cat_id') AND `product_id` NOT IN('$position_prod_id') AND `order` > '".$current_order_row['order']."'");
      }
    break;
    
    default:
    break;    
    }
  }

/*
 * Sorts out file directories
 */


  $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/thumbnails/";
  $product_images = ABSPATH."/wp-content/plugins/wp-shopping-cart/product_images/";
  $filedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/files/";
  $preview_clips_dir = ABSPATH."/wp-content/plugins/wp-shopping-cart/preview_clips/";
/*
 * Adds new products
 */
if($_POST['submit_action'] == 'add')
  {  
  //exit(nl2br(print_r($_POST,true)));
  $image = '';

  if($_FILES['image'] != null)
    {
    if(!is_dir($product_images))
      {
      mkdir($product_images);
      }
    if(function_exists("getimagesize"))
      {
      switch($_POST['image_resize'])
        {
        case 1:
        $height = get_option('product_image_height');
        $width  = get_option('product_image_width');
        break;

        case 2:
        $height = $_POST['height'];
        $width  = $_POST['width'];
        break;
        }

      // pe.{
      // Put this first so $image has correct value in for SQL later (from image_processing.php) (hack).
      // Leaving duplicate image (as copy above to images & product_images & then also upload thumb)?
      $thumbnail_image = '';
      
      if($_POST['image_resize'] == 3 && $_FILES['thumbnailImage'] != null)
        {
        copy($_FILES['thumbnailImage']['tmp_name'], ($product_images.$_FILES['thumbnailImage']['name']));
        $imagefield = 'thumbnailImage';
        include("image_processing.php");
        $thumbnail_image = $image;
        }
      // }.pe  
      
      if(file_exists($_FILES['image']['tmp_name']))
        {
        copy($_FILES['image']['tmp_name'], $product_images.$_FILES['image']['name']);
        // pe.{
        $imagefield = 'image';
        // }.pe    
        include("image_processing.php");
        }
      }
      else
        {
        move_uploaded_file($_FILES['image']['tmp_name'], ($imagedir.$_FILES['image']['name']));
        $image = $wpdb->escape($_FILES['image']['name']);
        }
    }
    else
      {
      $image = '';
      }
  if($_FILES['file']['name'] != null)
    {
    $timestamp = time();
    $wpdb->query("INSERT INTO `".$wpdb->prefix."product_files` ( `id` , `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '' , '', '', '', '$timestamp');");
    $fileid_raw = $wpdb->get_results("SELECT `id` FROM `".$wpdb->prefix."product_files` WHERE `date` = '$timestamp'",ARRAY_A);
    $fileid = $fileid_raw[0]['id'];
    $idhash = sha1($fileid);
    $mimetype = $_FILES['file']['type'];
    $splitname = explode(".",$_FILES['file']['name']);
    $splitname = array_reverse($splitname);
    $filename = $_FILES['file']['name'];
    if(move_uploaded_file($_FILES['file']['tmp_name'],($filedir.$idhash)))
      {
      if(function_exists("make_mp3_preview"))
        {
        if(!is_dir($preview_clips_dir))
          {
          mkdir($preview_clips_dir);
          }
        if($mimetype == "audio/mpeg" && (!isset($_FILES['preview_file']['tmp_name'])))
          {
          make_mp3_preview(($filedir.$idhash), ($preview_clips_dir.$idhash.".mp3"));
          }
        }
      $wpdb->query("UPDATE `".$wpdb->prefix."product_files` SET `filename` = '".addslashes($filename)."', `mimetype` = '$mimetype', `idhash` = '$idhash' WHERE `id` = '$fileid' LIMIT 1");
      }
   if(file_exists($_FILES['preview_file']['tmp_name']) && ($_FILES['preview_file']['type'] == "audio/mpeg"))
     {
     copy($_FILES['preview_file']['tmp_name'], ($preview_clips_dir.$idhash.".mp3"));
     //make_mp3_preview(($filedir.$idhash), ($preview_clips_dir.$idhash.".mp3"));
     }
    $file = $fileid;
    }
    else
      {
      $file = '0';
      }
      
   
   if($_POST['special'] == 'yes')
     {
     $special = 1;
     if(is_numeric($_POST['special_price']))
       {
       $special_price = $_POST['price'] - $_POST['special_price'];
       }
     }
     else
       {
       $special = 0;
       $special_price = '';
       }
       
   if($_POST['notax'] == 'yes')
     {
     $notax = 1;
     }
     else
       {
       $notax = 0;
       }
   
   if(is_numeric($_POST['quantity']) && ($_POST['quantity_limited'] == "yes"))
     {
     $quantity_limited = 1;
     $quantity = $_POST['quantity'];
     }
     else
       {
       $quantity_limited = 0;
       $quantity = 0;
       }
   
   if($_POST['display_frontpage'] == "yes")
     {
     $display_frontpage = 1;
     }
     else
       {
       $display_frontpage = 0;
       }
       

       
  $insertsql = "INSERT INTO `".$wpdb->prefix."product_list` ( `id` , `name` , `description` , `additional_description` , `price` , `pnp`, `international_pnp`, `file` , `image` , `category`, `brand`, `quantity_limited`, `quantity`, `special`, `special_price`,`notax`, `thumbnail_image`, `thumbnail_state`) VALUES ('', '".$wpdb->escape($_POST['name'])."', '".$wpdb->escape($_POST['description'])."', '".$wpdb->escape($_POST['additional_description'])."','".$wpdb->escape(str_replace(",","",$_POST['price']))."', '".$wpdb->escape($_POST['pnp'])."', '".$wpdb->escape($_POST['international_pnp'])."', '".$file."', '".$image."', '".$wpdb->escape($_POST['category'])."', '".$wpdb->escape($_POST['brand'])."', '$quantity_limited','$quantity','$special','$special_price','$notax', '$thumbnail_image', '" . $_POST['image_resize'] . "');";
  //echo $insertsql;
  if($wpdb->query($insertsql))
    {
    $product_id_data = $wpdb->get_results("SELECT LAST_INSERT_ID() AS `id` FROM `".$wpdb->prefix."product_list` LIMIT 1",ARRAY_A);
    $product_id = $product_id_data[0]['id'];
  
  if(($_FILES['extra_image'] != null) && function_exists('edit_submit_extra_images'))
    {
    $var = edit_submit_extra_images($product_id);
    }
  
  $variations_procesor = new nzshpcrt_variations;
  if($_POST['variation_values'] != null)
    {
    $variations_procesor->add_to_existing_product($product_id,$_POST['variation_values']); 
    }
  
  $counter = 0;
  $item_list = '';
  if(count($_POST['category']) > 0)
    {
    foreach($_POST['category'] as $category_id)
      {
      $check_existing = $wpdb->get_var("SELECT `id` FROM `".$wpdb->prefix."item_category_associations` WHERE `product_id` = ".$product_id." AND `category_id` = '$category_id' LIMIT 1");
      if($check_existing == null)
        {
        $wpdb->query("INSERT INTO `".$wpdb->prefix."item_category_associations` ( `id` , `product_id` , `category_id` ) VALUES ('', '".$product_id."', '".$category_id."');");        
        }
      }
    }
  
  $display_added_product = "filleditform(".$product_id.");";
  
  echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASBEENADDED."</p></div>";
  }
  else
    {
    echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASNOTBEENADDED."</p></div>";
    }
 }

if($_GET['submit_action'] == "remove_set")
  {
  if(is_numeric($_GET['product_id']) && is_numeric($_GET['variation_assoc_id']))
    {
    $product_id = $_GET['product_id'];
    $variation_assoc_id = $_GET['variation_assoc_id'];
    $check_association_id = $wpdb->get_var("SELECT `id` FROM `".$table_prefix."variation_associations` WHERE `id` = '$variation_assoc_id' LIMIT 1");
    if(($variation_assoc_id > 0) && ($variation_assoc_id == $check_association_id))
      {
      $variation_association = $wpdb->get_row("SELECT * FROM `".$table_prefix."variation_associations` WHERE `id` = '$variation_assoc_id' LIMIT 1",ARRAY_A);
      $delete_variation_sql = "DELETE FROM `".$table_prefix."variation_associations` WHERE `id` = '$variation_assoc_id' LIMIT 1";
      $wpdb->query($delete_variation_sql);
      //echo("<pre>".print_r($variation_association,true)."</pre>");
      if($variation_association != null)
        {
        $variation_id = $variation_association['variation_id'];
        $delete_value_sql = "DELETE FROM `".$table_prefix."variation_values_associations` WHERE `product_id` = '$product_id' AND `variation_id` = '$variation_id'";
        //exit($delete_value_sql);
        $wpdb->query($delete_value_sql);
        }
      echo "<div class='updated'><p align='center'>".TXT_WPSC_PRODUCTHASBEENEDITED."</p></div>";
      }
    } 
  }

if($_POST['submit_action'] == "edit")
  {
  //exit("<pre>".print_r($_POST,true)."</pre>");
  $id = $_POST['prodid'];
  if(function_exists('edit_submit_extra_images'))
    {
    if(($_FILES['extra_image'] != null))
      {
      $var = edit_submit_extra_images($id);
      }
    }
  if(function_exists('edit_extra_images'))
    {
    $var = edit_extra_images($id);
    } 
  
  if($_FILES['file']['name'] != null)
    {
    $id = $_POST['prodid'];
    $fileid_data = $wpdb->get_results("SELECT `file` FROM `".$wpdb->prefix."product_list` WHERE `id` = '$id' LIMIT 1",ARRAY_A);
    $fileid = $fileid_data[0]['file'];
    $file_data = $wpdb->get_results("SELECT `id`,`idhash` FROM `".$wpdb->prefix."product_files` WHERE `id` = '$fileid' LIMIT 1",ARRAY_A);
    if($file_data != null)
      {
      if($idhash != null)
        {
        $idhash = $file_data[0]['idhash'];
        }
        else
        {   
        $idhash = sha1($file_data[0]['id']);
        }
      $mimetype = $_FILES['file']['type'];
      $filename = $_FILES['file']['name'];
      //echo("<pre>".print_r($file_data,true)."</pre>");
      //exit($filedir.$idhash);
      if(move_uploaded_file($_FILES['file']['tmp_name'],($filedir.$idhash)))
        {
        if(function_exists("make_mp3_preview") && ($mimetype == 'audio/mpeg') && (!isset($_FILES['preview_file']['tmp_name'])))
          {
          if(!is_dir($preview_clips_dir))
            {
            mkdir($preview_clips_dir);
            }
          make_mp3_preview(($filedir.$idhash), ($preview_clips_dir.$idhash.".mp3"));
          }
        $wpdb->query("UPDATE `".$wpdb->prefix."product_files` SET `filename` = '".addslashes($filename)."', `mimetype` = '$mimetype' WHERE `id` = '".$file_data[0]['id']."' LIMIT 1");
        }
      }
      else
      {
      $timestamp = time();
      $wpdb->query("INSERT INTO `".$wpdb->prefix."product_files` ( `id` , `filename`  , `mimetype` , `idhash` , `date` ) VALUES ( '' , '', '', '', '$timestamp');");
      $fileid_raw = $wpdb->get_results("SELECT `id` FROM `".$wpdb->prefix."product_files` WHERE `date` = '$timestamp'",ARRAY_A);
      $fileid = $fileid_raw[0]['id'];
      $idhash = sha1($fileid);
      $mimetype = $_FILES['file']['type'];
      $splitname = explode(".",$_FILES['file']['name']);
      $splitname = array_reverse($splitname);
      $filename = $_FILES['file']['name'];
      if(move_uploaded_file($_FILES['file']['tmp_name'],($filedir.$idhash)))
        {
        if(function_exists("make_mp3_preview"))
          {
          if(!is_dir($preview_clips_dir))
            {
            mkdir($preview_clips_dir);
            }
          if($mimetype == "audio/mpeg" && (!isset($_FILES['preview_file']['tmp_name'])))
            {
            make_mp3_preview(($filedir.$idhash), ($preview_clips_dir.$idhash.".mp3"));
            }
          }
        $wpdb->query("UPDATE `".$wpdb->prefix."product_files` SET `filename` = '".addslashes($filename)."', `mimetype` = '$mimetype', `idhash` = '$idhash' WHERE `id` = '$fileid' LIMIT 1");
        }
      if(file_exists($_FILES['preview_file']['tmp_name']) && ($_FILES['preview_file']['type'] == "audio/mpeg"))
        {
        copy($_FILES['preview_file']['tmp_name'], ($preview_clips_dir.$idhash.".mp3"));
        //make_mp3_preview(($filedir.$idhash), ($preview_clips_dir.$idhash.".mp3"));
        }
      $file = $fileid;
      }
    }
    
  
   if(file_exists($_FILES['preview_file']['tmp_name']) && ($_FILES['preview_file']['type'] == "audio/mpeg"))
     {
     $fileid_data = $wpdb->get_results("SELECT `file` FROM `".$wpdb->prefix."product_list` WHERE `id` = '$id' LIMIT 1",ARRAY_A);
     $fileid = $fileid_data[0]['file'];
     $file_data = $wpdb->get_results("SELECT `id`,`idhash` FROM `".$wpdb->prefix."product_files` WHERE `id` = '$fileid' LIMIT 1",ARRAY_A);
     $idhash = $file_data[0]['idhash'];
     copy($_FILES['preview_file']['tmp_name'], ($preview_clips_dir.$idhash.".mp3"));
     }

  if($_FILES['image'] != null)
    {
    if(!is_dir($product_images))
      {
      mkdir($product_images);
      }
    if(function_exists("getimagesize"))
      {
      switch($_POST['image_resize'])
        {
        case 1:
        $height = get_option('product_image_height');
        $width  = get_option('product_image_width');
        break;

        case 2:
        $height = $_POST['height'];
        $width  = $_POST['width'];
        break;
        }
      // pe.{
      // Put this first so $image has correct value in for SQL later (from image_processing.php) (hack).
      // Leaving duplicate image (as copy above to images & product_images & then also upload thumb)?
      if (($_POST['image_resize'] == 3) && ($_FILES['thumbnailImage'] != null) && file_exists($_FILES['thumbnailImage']['tmp_name']))
      {
        copy($_FILES['thumbnailImage']['tmp_name'], ($product_images.$_FILES['thumbnailImage']['name']));
        $imagefield = 'thumbnailImage';
        include("image_processing.php");
        $thumbnail_image = $image;
      }
      // }.pe
      if(file_exists($_FILES['image']['tmp_name']))
        {
        copy($_FILES['image']['tmp_name'], ($product_images.$_FILES['image']['name']));
        }
      // pe.{
      $imagefield = 'image';
      // }.pe
      include("image_processing.php");
      }
      else
        {
        if(file_exists($_FILES['image']['tmp_name']))
          {
          move_uploaded_file($_FILES['image']['tmp_name'], ($imagedir.$_FILES['image']['name']));
          }
        $image = $wpdb->escape($_FILES['image']['name']);
        }
    if(!is_file($imagedir.$_FILES['image']['name']))
      {
      $image = '';
      }
    }
    else
      {
      $image = '';
      }

  if(is_numeric($_POST['prodid']))
    {
    if(($_POST['image_resize'] == 1 || $_POST['image_resize'] == 2) && ($image === ''))
      {
      $image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".$wpdb->prefix."product_list` WHERE `id`=".$_POST['prodid']." LIMIT 1",ARRAY_A);
      
      $check_multiple_use = $wpdb->get_var("SELECT COUNT(`image`) AS `count` FROM `".$wpdb->prefix."product_list` WHERE `image`='".$image_data['image']."'");
      if($check_multiple_use > 1)
        {        
        $new_filename = $image_data['id']."_".$image_data['image'];
        if(file_exists($imagedir.$image_data['image']))
          {
          copy($imagedir.$image_data['image'], $imagedir.$new_filename);
          }
        if(file_exists($product_images.$image_data['image']))
          {
          copy($product_images.$image_data['image'], $product_images.$new_filename);
          }
        $wpdb->query("UPDATE `".$wpdb->prefix."product_list` SET `image` = '".$new_filename."' WHERE `id`='".$image_data['id']."' LIMIT 1");
        $image_data = $wpdb->get_row("SELECT `id`,`image` FROM `".$wpdb->prefix."product_list` WHERE `id`=".$_POST['prodid']." LIMIT 1",ARRAY_A);
        }
      
      if($image_data['image'] != '')
        {
        $imagepath = $product_images . $image_data['image'];
        $image_output = $imagedir . $image_data['image'];
        switch($_POST['image_resize'])
          {
          case 1:
          $height = get_option('product_image_height');
          $width  = get_option('product_image_width');
          break;
  
          case 2:
          $height = $_POST['height'];
          $width  = $_POST['width'];
          break;
          }
        include("image_resize.php");
        }
      }
    
    if(is_numeric($_POST['prodid']))
      {
      $counter = 0;
      $item_list = '';
      if(count($_POST['category']) > 0)
        {
        foreach($_POST['category'] as $category_id)
          {
          $check_existing = $wpdb->get_var("SELECT `id` FROM `".$wpdb->prefix."item_category_associations` WHERE `product_id` = ".$id." AND `category_id` = '$category_id' LIMIT 1");
          if($check_existing == null)
            {
            $wpdb->query("INSERT INTO `".$wpdb->prefix."item_category_associations` ( `id` , `product_id` , `category_id` ) VALUES ('', '".$id."', '".$category_id."');");        
            }
          if($counter > 0)
            {
            $item_list .= ", ";
            }
          $item_list .= "'".$category_id."'";
          $counter++;
          }
        }
        else
          {
          $item_list = "'0'";
          }
      $wpdb->query("DELETE FROM `".$wpdb->prefix."item_category_associations` WHERE `product_id`= '$id' AND `category_id` NOT IN (".$item_list.")"); 
      }
      
      
   if($_POST['variation_priceandstock'] != null)
     {
     foreach($_POST['variation_priceandstock'] as $key[0] => $variation_row)
       {
       foreach($variation_row as $key[1] => $variation_data)
         {
         if(is_numeric($key[0]) && is_numeric($key[1]) && (is_numeric($variation_data['stock']) || is_numeric($variation_data['price'])))
           {
           $variation_stock_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."variation_priceandstock` WHERE `product_id` = '".$_POST['prodid']."' AND (`variation_id_1` = '".$key[0]."' AND `variation_id_2` = '".$key[1]."') OR (`variation_id_1` = '".$key[1]."' AND `variation_id_2` = '".$key[0]."') LIMIT 1",ARRAY_A);
           if(is_numeric($variation_data['stock'])) { $variation_stock = $variation_data['stock']; } else { $variation_stock = 0; }
           if(is_numeric($variation_data['price'])) { $variation_price = $variation_data['price']; } else { $variation_price = ''; }
           if(is_numeric($variation_stock_data['id']))
             {
             if(($variation_stock_data['stock'] != $stock))
               {
               $variation_sql[] = "`stock` = '".$variation_stock."'";
               }
             if(($variation_stock_data['stock'] != $stock))
               {
               $variation_sql[] = "`price` = '".$variation_price."'";
               }
             if($variation_sql != null)
               {
               $wpdb->query("UPDATE `".$wpdb->prefix."variation_priceandstock` SET ".implode(",",$variation_sql)."WHERE `id` = '".$variation_stock_data['id']."' LIMIT 1 ;");
               }
             }
             else
               {
               $wpdb->query("INSERT INTO `".$wpdb->prefix."variation_priceandstock` ( `id` , `product_id` , `variation_id_1` , `variation_id_2` , `stock`, `price` ) VALUES ('', '".$_POST['prodid']."', '".$key[0]."', '".$key[1]."', '".$variation_stock."', '".$variation_price."');");
               }
           }
         }
       }
     }
         
   if(is_numeric($_POST['quantity']) && ($_POST['quantity_limited'] == "yes"))
     {
     //exit("<pre>".print_r($_POST,true)."</pre>");
     $quantity_limited = 1;
     $quantity = $_POST['quantity'];
     }
     else
       {
       $quantity_limited = 0;
       $quantity = 0;
       }
       
    if($_POST['special'] == 'yes')
      {
      $special = 1;
     if(is_numeric($_POST['special_price']))
       {
       $special_price = $_POST['price'] - $_POST['special_price'];
       }
      }
      else
        {
        $special = 0;
        $special_price = '';
        }
  
    if($_POST['notax'] == 'yes')
      {
      $notax = 1;
      }
      else
        {
        $notax = 0;
        }

      
   if($_POST['display_frontpage'] == "yes")
     {
     $display_frontpage = 1;
     }
     else
       {
       $display_frontpage = 0;
       }
             
      $updatesql = "UPDATE `".$wpdb->prefix."product_list` SET `name` = '".$wpdb->escape($_POST['title'])."', `description` = '".$wpdb->escape($_POST['description'])."', `additional_description` = '".$wpdb->escape($_POST['additional_description'])."', `price` = '".$wpdb->escape(str_replace(",","",$_POST['price']))."', `pnp` = '".$wpdb->escape($_POST['pnp'])."', `international_pnp` = '".$wpdb->escape($_POST['international_pnp'])."', `category` = '".$wpdb->escape($_POST['category'])."', `brand` = '".$wpdb->escape($_POST['brand'])."', quantity_limited = '".$quantity_limited."', `quantity` = '".$quantity."', `special`='$special', `special_price`='$special_price', `display_frontpage`='$display_frontpage', `notax`='$notax'  WHERE `id`='".$_POST['prodid']."' LIMIT 1";
      
      $wpdb->query($updatesql);
      if(($_FILES['image']['name'] != null) && ($image != null))
        {
        $updatesql2 = "UPDATE `".$wpdb->prefix."product_list` SET `image` = '".$image."' WHERE `id`='".$_POST['prodid']."' LIMIT 1";
        $wpdb->query($updatesql2);
        }
    
    if(!($thumbnail_image == null && $_POST['image_resize'] == 3 && $_POST['current_thumbnail_image'] != null))
      {
        if($thumbnail_image != null)
        {
          $updatesql2 = "UPDATE `".$wpdb->prefix."product_list` SET `thumbnail_image` = '".$thumbnail_image."' WHERE `id`='".$_POST['prodid']."' LIMIT 1";
          $wpdb->query($updatesql2);
        }
        else
        {
          $updatesql2 = "UPDATE `".$wpdb->prefix."product_list` SET `thumbnail_image` = '' WHERE `id`='".$_POST['prodid']."' LIMIT 1";
          $wpdb->query($updatesql2);
        }
      }
      
      $updatesql3 = "UPDATE `".$wpdb->prefix."product_list` SET `thumbnail_state` = '" . $_POST['image_resize'] . "' WHERE `id`='".$_POST['prodid']."' LIMIT 1";
    $wpdb->query($updatesql3);
    if($_POST['deleteimage'] == 1)
      {
      $updatesql2 = "UPDATE `".$wpdb->prefix."product_list` SET `image` = ''  WHERE `id`='".$_POST['prodid']."' LIMIT 1";
      $wpdb->query($updatesql2);
      }
        
        
     
     $variations_procesor = new nzshpcrt_variations;
     if($_POST['variation_values'] != null)
       {
       $variations_procesor->add_to_existing_product($_POST['prodid'],$_POST['variation_values']); 
       }
     
     if($_POST['edit_variation_values'] != null)
       {
       $variations_procesor->edit_product_values($_POST['prodid'],$_POST['edit_variation_values']);
       }
     
     if($_POST['edit_add_variation_values'] != null)
       {
       $variations_procesor->edit_add_product_values($_POST['prodid'],$_POST['edit_add_variation_values']);
       }
     
     echo "<div class='updated'><p align='center'>".TXT_WPSC_PRODUCTHASBEENEDITED."</p></div>";
     }
  }

if(is_numeric($_GET['deleteid']))
  {
  $deletesql = "UPDATE `".$wpdb->prefix."product_list` SET  `active` = '0' WHERE `id`='".$_GET['deleteid']."' LIMIT 1";
  $wpdb->query($deletesql);
  }
  
  
/*
 * Gets the product list, commented to make it stick out more, as it is hard to notice 
 */
if(is_numeric($_GET['catid']))  // if we are getting items from only one category, this is a monster SQL query to do this with the product order
  {  
  $sql = "SELECT `".$wpdb->prefix."product_list`.`id` , `".$wpdb->prefix."product_list`.`name` , `".$wpdb->prefix."product_list`.`price` , `".$wpdb->prefix."product_list`.`image`, `".$wpdb->prefix."item_category_associations`.`category_id`,`".$wpdb->prefix."product_order`.`order`, IF(ISNULL(`".$wpdb->prefix."product_order`.`order`), 0, 1) AS `order_state`
FROM `".$wpdb->prefix."product_list` 
LEFT JOIN `".$wpdb->prefix."item_category_associations` ON `".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."item_category_associations`.`product_id` 
LEFT JOIN `".$wpdb->prefix."product_order` ON ( (
`".$wpdb->prefix."product_list`.`id` = `".$wpdb->prefix."product_order`.`product_id` 
)
AND (
`".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_order`.`category_id` 
) ) 
WHERE `".$wpdb->prefix."product_list`.`active` = '1'
AND `".$wpdb->prefix."item_category_associations`.`category_id` 
IN (
'".$_GET['catid']."'
)
ORDER BY `order_state` DESC,`".$wpdb->prefix."product_order`.`order` ASC";
  }
  else
    {
    $sql = "SELECT DISTINCT `".$wpdb->prefix."product_list`.* FROM `".$wpdb->prefix."product_list` WHERE `".$wpdb->prefix."product_list`.`active`='1'";
    }   
    
$product_list = $wpdb->get_results($sql,ARRAY_A) ;
/*
 * The product list is stored in $product_list now
 */
?>


<div class="wrap">
  <h2><?php echo TXT_WPSC_DISPLAYPRODUCTS;?></h2>
  <a href='' onclick='return showaddform()' class='add_item_link'><img src='../wp-content/plugins/wp-shopping-cart/images/package_add.png' alt='<?php echo TXT_WPSC_ADD; ?>' title='<?php echo TXT_WPSC_ADD; ?>' />&nbsp;<span><?php echo TXT_WPSC_ADDPRODUCT;?></span></a><br />
  <?php echo TXT_WPSC_PLEASESELECTACATEGORY;?>:
  <?php
  echo topcategorylist() . " <span id='loadingindicator_span'><img id='loadingimage' src='../wp-content/plugins/wp-shopping-cart/images/indicator.gif' alt='Loading' title='Loading' /></span><br /><br />";
  ?>
  <script language='javascript' type='text/javascript'>
function conf()
  {
  var check = confirm("<?php echo TXT_WPSC_SURETODELETEPRODUCT;?>");
  if(check)
    {
    return true;
  }
  else
    {
    return false;
    }
  }
<?php
if(is_numeric($_POST['prodid']))
  {
  echo "filleditform(".$_POST['prodid'].");";
  }
  else if(is_numeric($_GET['product_id']))
    {
    echo "filleditform(".$_GET['product_id'].");";
    }
  
echo $display_added_product ;
?>
</script>
  <?php
  

$num = 0;

echo "    <table id='productpage'>\n\r";
echo "      <tr><td>\n\r";
echo "        <table id='itemlist'>\n\r";
echo "          <tr>\n\r";
echo "            <td colspan='6' style='text-align: left;'>\n\r";
echo "<strong class='form_group'>".TXT_WPSC_SELECT_PRODUCT."</strong>";
echo "            </td>\n\r";
echo "          </tr>\n\r";
echo "          <tr class='firstrow'>\n\r";

echo "            <td>\n\r";
echo TXT_WPSC_NAME;
echo "            </td>\n\r";

echo "            <td>\n\r";
echo "            </td>\n\r";

echo "            <td>\n\r";
echo TXT_WPSC_PRICE;
echo "            </td>\n\r";

if(is_numeric($_GET['catid']))
  {
  echo "            <td>\n\r";
  echo TXT_WPSC_POSITION;
  echo "            </td>\n\r";
  }
  else
    {
    echo "            <td>\n\r";
    echo TXT_WPSC_CATEGORIES;
    echo "            </td>\n\r";
    }

echo "            <td>\n\r";
echo TXT_WPSC_EDIT;
echo "            </td>\n\r";

echo "          </tr>\n\r";
if($product_list != null)
  {
  $order_number = 0;
  foreach($product_list as $product)
    {
    /*
     * Creates order table entries if they are not already present
     * No need for extra database queries to determine the highest order number
     * anything without one is automatically at the bottom
     * so anything with an order number is already processed by the time it starts adding rows
     */
    if(is_numeric($_GET['catid']))
      {
      if($product['order_state'] > 0)
        {
        if($product['order'] > $order_number)
          {
          $order_number = $product['order'];
          $order_number++;
          }      
        }
        else
          {
          $wpdb->query("INSERT INTO `".$wpdb->prefix."product_order` ( `id` , `category_id` , `product_id` , `order` ) VALUES ('', '".$product['category_id']."', '".$product['id']."', '$order_number');");
          $order_number++;
          }
      }
    echo "          <tr>\n\r";
    echo "            <td>\n\r";
    if(($product['image'] != null) && file_exists($imagedir.$product['image']))
      {
      echo "<img src='../wp-content/plugins/wp-shopping-cart/product_images/thumbnails/".$product['image']."' title='".$product['name']."' alt='".$product['name']."' width='35' height='35' />";
      }
      else
        {
        echo "<img src='../wp-content/plugins/wp-shopping-cart/no-image-uploaded.gif' title='".$product['name']."' alt='".$product['name']."' width='35' height='35'  />";
          }
    echo "            </td>\n\r";
    
    echo "            <td>\n\r";
    echo "".stripslashes($product['name'])."";
    echo "            </td>\n\r";    
    
    echo "            <td>\n\r";
    echo nzshpcrt_currency_display($product['price'], 1);
    echo "            </td>\n\r";
    
    if(is_numeric($_GET['catid']))
      {
      echo "            <td class='positioning_buttons'>\n\r";
      $position_url = "?page=".$_GET['page']."&amp;catid=".$_GET['catid']."&amp;product_id=".$product['id']."&amp;position_action=";
      echo "<a href='".$position_url."top'><img src='../wp-content/plugins/wp-shopping-cart/images/order_top.png' alt='Move to Top' title='Move to Top'/></a>";
      echo "<a href='".$position_url."up'><img src='../wp-content/plugins/wp-shopping-cart/images/order_up.png' alt='Move Up' title='Move Up'/></a>";
      echo "<a href='".$position_url."down'><img src='../wp-content/plugins/wp-shopping-cart/images/order_down.png' alt='Move Down' title='Move Down'/></a>";
      echo "<a href='".$position_url."bottom'><img src='../wp-content/plugins/wp-shopping-cart/images/order_bottom.png' alt='Move to Bottom' title='Move to Bottom'/> </a>";
      echo "            </td>\n\r";
      }
      else
        {
        echo "            <td>\n\r";
        $category_list = $wpdb->get_results("SELECT `".$wpdb->prefix."product_categories`.`id`,`".$wpdb->prefix."product_categories`.`name` FROM `".$wpdb->prefix."item_category_associations` , `".$wpdb->prefix."product_categories` WHERE `".$wpdb->prefix."item_category_associations`.`product_id` IN ('".$product['id']."') AND `".$wpdb->prefix."item_category_associations`.`category_id` = `".$wpdb->prefix."product_categories`.`id`",ARRAY_A);
        $i = 0;
        foreach((array)$category_list as $category_row)
          {
          if($i > 0)
            {
            echo "<br />";
            }
          echo "<a href='?page=".$_GET['page']."&amp;catid=".$category_row['id']."'>".stripslashes($category_row['name'])."</a>";
          $i++;
          }        
        echo "            </td>\n\r";
        }
        
    echo "            <td>\n\r";
    echo "<a href='#' onclick='filleditform(".$product['id'].");return false;'>".TXT_WPSC_EDIT."</a>";
    echo "            </td>\n\r";
    
    echo "          </tr>\n\r";
    }
  }
echo "        </table>\n\r";
echo "      </td><td class='secondcol'>\n\r";
echo "        <div id='productform'>";
echo "<form method='POST'  enctype='multipart/form-data' name='editproduct$num'>";
echo "        <table class='producttext'>\n\r";;    

echo "          <tr>\n\r";
echo "            <td colspan='2'>\n\r";
//echo "<strong>".TXT_WPSC_EDITITEM."</strong>";
echo "<strong class='form_group'>".TXT_WPSC_PRODUCTDETAILS." <span>".TXT_WPSC_ENTERPRODUCTDETAILSHERE."</span></strong>";
echo "            </td>\n\r";
echo "          </tr>\n\r";

echo "        </table>\n\r";
echo "        <div id='formcontent'>\n\r";
echo "        </div>\n\r";
echo "</form>";
echo "        </div>";
?>
<div id='additem'>
  <form method='POST' enctype='multipart/form-data'>
  <table class='additem'>
    <tr>
      <td colspan='2'>
        <strong class='form_group'><?php echo TXT_WPSC_PRODUCTDETAILS;?> <span><?php echo TXT_WPSC_ENTERPRODUCTDETAILSHERE;?></span></strong>
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol'>
        <?php echo TXT_WPSC_PRODUCTNAME;?>:
      </td>
      <td>
        <input size='30' type='text' name='name' value=''  />
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol'>
        <?php echo TXT_WPSC_PRODUCTDESCRIPTION;?>:
      </td>
      <td>
        <textarea name='description' cols='40' rows='8'></textarea><br />
      </td>
    </tr>
    <tr>
      <td class='itemfirstcol'>
       <?php echo TXT_WPSC_ADDITIONALDESCRIPTION;?>:
      </td>
      <td>
        <textarea name='additional_description' cols='40' rows='8'></textarea><br />
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_PRODUCT_CATEGORIES;?>:
      </td>
      <td>
        <?php echo categorylist(false, 'add_'); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_CHOOSEABRAND;?>:
      </td>
      <td>
        <?php echo brandslist(); ?>
      </td>
    </tr>
    <tr>
      <td>
      </td>
      <td>
        <input type="checkbox" value="yes" id="add_form_display_frontpage" name="display_frontpage"/> 
        <label for='add_form_display_frontpage'><?php echo TXT_WPSC_DISPLAY_FRONT_PAGE;?></label>
      </td>
    </tr>
    
    
    <tr>
      <td colspan="2">
      <br/><br/>
      <strong class="form_group"><?php echo TXT_WPSC_PRICE_AND_STOCK_CONTROL;?></strong>
      </td>
    </tr>
    
    <tr>
      <td rowspan='2'>
       <?php echo TXT_WPSC_PRICE;?>:
      </td>
      <td>
        <input type='text' size='10' name='price' value='0.00' />
      </td>
    </tr>
    <tr>
       <td>
          <input id='add_form_tax' type='checkbox' name='notax' value='yes' />&nbsp;<label for='add_form_tax'><?php echo TXT_WPSC_TAXALREADYINCLUDED;?></label>
       </td>
    </tr>
    <tr>
      <td>
      </td>
      <td>
        <input type="checkbox" onclick="hideelement('add_special')" value="yes" name="special" id="add_form_special"/>
        <label for="add_form_special"><?php echo TXT_WPSC_SPECIAL;?></label>
        <div style="display: none;" id="add_special">
          <input type="text" size="10" value="0.00" name="special_price"/>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <?php /* echo TXT_WPSC_PRODUCTSTOCK;*/ ?>
      </td>
      <td>
        <input id='add_form_quantity_limited' type="checkbox" onclick="hideelement('add_stock')" value="yes" name="quantity_limited"/>
        <label for='add_form_quantity_limited' class='small'><?php echo TXT_WPSC_UNTICKBOX;?></label>
        <div style="display: none;" id="add_stock">
          <input type='text' name='quantity' value='0' size='10' />
        </div>
      </td>
    </tr>
    
    <tr>
      <td>
      </td>
      <td>
        <?php echo variationslist(); ?>
        <div id='add_product_variations'>
        
        </div>
      </td>
    </tr> 

    <tr>
      <td colspan='2'>
        <br /><br />
        <strong class='form_group'><?php echo TXT_WPSC_SHIPPING_DETAILS; ?></strong>
      </td>
    </tr>
  
    <tr>
      <td>
      <?php echo TXT_WPSC_LOCAL_PNP; ?> 
      </td>
      <td>
        <input type='text' size='10' name='pnp' value='0.00' />
      </td>
    </tr>
  
    <tr>
      <td>
      <?php echo TXT_WPSC_INTERNATIONAL_PNP; ?>
      </td>
      <td>
        <input type='text' size='10' name='international_pnp' value='0.00' />
      </td>
    </tr>
    
    <tr>
      <td colspan='2'>
        <br /><br />
        <strong class='form_group'><?php echo TXT_WPSC_PRODUCTIMAGES;?></strong>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_PRODUCTIMAGE;?>:
      </td>
      <td>
        <input type='file' name='image' value='' />
      </td>
    </tr>
    <tr>
      <td></td><td>
      <table>
  <?php
  // pe.{ & table opening above
  if(function_exists("getimagesize") && is_numeric(get_option('product_image_height')) && is_numeric(get_option('product_image_width')))
    {
    ?>
      <tr>
        <td>
      <input type='radio' checked='true' name='image_resize' value='0' id='add_image_resize0' class='image_resize' onclick='hideOptionElement(null, "image_resize0");' /> <label for='add_image_resize0'><?php echo TXT_WPSC_DONOTRESIZEIMAGE; ?></label>
        </td>
      </tr>
      <tr>
        <td>
          <input type='radio' name='image_resize' value='1' id='add_image_resize1' class='image_resize' onclick='hideOptionElement(null, "image_resize1");' /> <label for='add_image_resize1'><?php echo TXT_WPSC_USEDEFAULTSIZE;?> (<?php echo get_option('product_image_height') ."x".get_option('product_image_width'); ?>)</label>
        </td>
      </tr>
    <?php  
    $default_size_set = true;
    }
  
  if(function_exists("getimagesize"))
    {
    ?>
      <tr>
        <td>
          <input type='radio' name='image_resize' value='2' id='add_image_resize2' class='image_resize'  onclick='hideOptionElement("heightWidth", "image_resize2");' />
      <label for='add_image_resize2'><?php echo TXT_WPSC_USESPECIFICSIZE; ?> </label>        
          <div id='heightWidth' style='display: none;'>
        <input type='text' size='4' name='width' value='' /><label for='add_image_resize2'><?php echo TXT_WPSC_PXWIDTH;?></label>
        <input type='text' size='4' name='height' value='' /><label for='add_image_resize2'><?php echo TXT_WPSC_PXHEIGHT; ?> </label>
      </div>
        </td>
      </tr>
      <tr>
      <td>
        <input type='radio' name='image_resize' value='3' id='add_image_resize3' class='image_resize' onclick='hideOptionElement("browseThumb", "image_resize3");' />
        <label for='add_image_resize3'><?php echo TXT_WPSC_SEPARATETHUMBNAIL; ?></label><br />
        <div id='browseThumb' style='display: none;'>
          <input type='file' name='thumbnailImage' value='' />
        </div>
      </td>
    </tr>
    <?php
    }
    // }.pe

    // pe.{ table closing below }.pe
  ?>
        </table>
      </td>
    </tr>
    <?php
    if(function_exists('add_multiple_image_form'))
      {
      echo add_multiple_image_form(); 
      }
    ?>
    <tr>
      <td colspan='2'>
        <br /><br />
        <strong class='form_group'><?php echo TXT_WPSC_PRODUCTDOWNLOAD;?></strong>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_DOWNLOADABLEPRODUCT;?>?:
      </td>
      <td>
        <input type='file' name='file' value='' /> <span class='small'><br /><?php echo TXT_WPSC_FILETOBEPRODUCT;?></span><br /><br />
      </td>
    </tr>
<?php
if(function_exists("make_mp3_preview"))
  {    
  echo "    <tr>\n\r";
  echo "      <td>\n\r";
  echo TXT_WPSC_PREVIEW_FILE.": ";
  echo "      </td>\n\r";
  echo "      <td>\n\r";
  echo "<input type='file' name='preview_file' value='' /><br />";
  echo TXT_WPSC_PREVIEW_FILE_NOTE;
  echo "      </td>\n\r";
  echo "    </tr>\n\r";
  }
    ?>
    <tr>
      <td>
      </td>
      <td>
        <input type='hidden' name='submit_action' value='add' />
        <input type='submit' name='submit' value='<?php echo TXT_WPSC_ADD;?>' />
      </td>
    </tr>
  </table>
  </form>
  </div>
<?php
echo "      </td></tr>\n\r";
echo "     </table>\n\r"

  ?>
</div>