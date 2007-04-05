<?php
function admin_categorylist($curent_category)
  {
  global $wpdb;
  $options = "";
  //$options .= "<option value=''>".TXT_WPSC_SELECTACATEGORY."</option>\r\n";
  $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` ORDER BY `id` ASC",ARRAY_A);
  foreach($values as $option)
    {
    if($curent_category == $option['id'])
      {
      $selected = "selected='selected'";
       }
    $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
    $selected = "";
    }
  $concat .= "<select name='category'>".$options."</select>\r\n";
  return $concat;
  }

function parent_category_list()
  {
  global $wpdb,$category_data;
  $options = "";
  $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `category_parent`='0' AND `active` = '1' ORDER BY `id` ASC",ARRAY_A);
  $url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?page=wp-shopping-cart/display-items.php";
  $options .= "<option value='$url'>".TXT_WPSC_SELECT_PARENT."</option>\r\n";
  if($values != null)
    {
    foreach($values as $option)
      {
      $category_data[$option['id']] = $option['name'];
      if($_GET['catid'] == $option['id'])
        {
        $selected = "selected='selected'";
        }
      $options .= "<option $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
      $selected = "";
      }
    }
  $concat .= "<select name='category_parent'>".$options."</select>\r\n";
  return $concat;
  }


function display_categories($id = null, $level = 0)
  {
  global $wpdb,$category_data;
  if(is_numeric($id))
    {
    $category_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '".$id."' ORDER BY `id`";
    $category_list = $wpdb->get_results($category_sql,ARRAY_A);
    }
    else
      {
      $category_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '0' ORDER BY `id`";
      $category_list = $wpdb->get_results($category_sql,ARRAY_A);
      }
  if($category_list != null)
    {
    foreach($category_list as $category)
      {
      display_category_row($category, $level);
      display_categories($category['id'], ($level+1));
      }
    }
  }

function display_category_row($category,$subcategory_level = 0)
  {
  echo "     <tr>\n\r";
  echo "       <td colspan='4' class='colspan'>\n\r";
  if($subcategory_level > 0)
    {
    echo "<div class='subcategory' style='padding-left: ".(1*$subcategory_level)."em;'>";
    echo "<img class='category_indenter' src='../wp-content/plugins/wp-shopping-cart/images/indenter.gif' alt='' title='' />";
    }
  echo "        <table class='itemlist'>\n\r";
  echo "          <tr>\n\r";
  echo "            <td>\n\r";
  if($category['image'] !=null)
        {
        echo "<img style='border-style:solid; border-color: red' src='../wp-content/plugins/wp-shopping-cart/category_images/".$category['image']."' title='".$category['name']."' alt='".$category['name']."' width='35' height='35' />";
        }
        else
          {
          echo "<img style='border-style:solid; border-color: red' src='../wp-content/plugins/wp-shopping-cart/no-image-uploaded.gif' title='".$category['name']."' alt='".$category['name']."' width='35' height='35'  />";
          }
  echo "            </td>\n\r";
  
  echo "            <td>\n\r";
  echo "".stripslashes($category['name'])."";
  echo "            </td>\n\r";
  
  $displaydescription = substr(stripslashes($category['description']),0,44);
  if($displaydescription != $category['description'])
    {
    $displaydescription_arr = explode(" ",$displaydescription);
    $lastword = count($displaydescription_arr);
    if($lastword > 1)
      {
      unset($displaydescription_arr[$lastword-1]);
      $displaydescription = '';
      $j = 0;
      foreach($displaydescription_arr as $displaydescription_row)
        {
        $j++;
        $displaydescription .= $displaydescription_row;
        if($j < $lastword -1)
          {
          $displaydescription .= " ";
          }
        }
      }
    $displaydescription .= "...";
    }
  
  echo "            <td>\n\r";
  echo "".stripslashes($displaydescription)."";
  echo "            </td>\n\r";
  
  echo "            <td>\n\r";
  echo "<a href='#' onclick='fillcategoryform(".$category['id'].");return false;'>".TXT_WPSC_EDIT."</a>";
  echo "            </td>\n\r";
  echo "          </tr>\n\r";
  echo "        </table>";
  
  if($subcategory_level > 0)
    {
    echo "</div>";
    }
  echo "       </td>\n\r";
  echo "      </tr>\n\r";
  }


  $imagedir = ABSPATH."/wp-content/plugins/wp-shopping-cart/category_images/";

  if($_POST['submit_action'] == "add")
    { 
    if($_FILES['image'] != null)
      {
      if(function_exists("getimagesize"))
        {
        // pe.{
        $imagefield = 'image';
        // }.pe
        include("image_processing.php");
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
    
    if(is_numeric($_POST['category_parent']))
      {
      $parent_category = $_POST['category_parent'];
      }
      else
        {
        $parent_category = 0;
        }
    //exit("INSERT INTO `".$wpdb->prefix."product_categories` ( `id` , `name` , `description`, `image`, `fee` , `active`, `category_parent` ) VALUES ('', '".$_POST['name']."', '".$_POST['description']."', '$image', '0', '1' ,'$parent_category')");
    $insertsql = "INSERT INTO `".$wpdb->prefix."product_categories` ( `id` , `name` , `description`, `image`, `fee` , `active`, `category_parent` ) VALUES ('', '".$_POST['name']."', '".$_POST['description']."', '$image', '0', '1' ,'$parent_category')";
  
    if($wpdb->query($insertsql))
      {
      echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASBEENADDED."</p></div>";
      }
      else
        {
        echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASNOTBEENADDED."</p></div>";
        }
    }

  if(($_POST['submit_action'] == "edit") && is_numeric($_POST['prodid']))
    {
    //echo nl2br(print_r($_FILES));
    if($_FILES['image'] != null)
      {
      if(function_exists("getimagesize"))
        {
        // pe.{
        $imagefield = 'image';
        // }.pe
        include("image_processing.php");
        }
        else
          {
          move_uploaded_file($_FILES['image']['tmp_name'], ($imagedir.$_FILES['image']['name']));
          $image = $wpdb->escape($_FILES['image']['name']);
          }
      }
    else
      {
      $image = null;
      }
    if(is_numeric($_POST['height']) && is_numeric($_POST['width']) && ($image == null))
      {
      //exit(nl2br(print_r($_POST,true)));
      $imagesql = "SELECT `image` FROM `".$wpdb->prefix."product_categories` WHERE `id`=".$_POST['prodid']." LIMIT 1";
      $imagedata = $wpdb->get_results($imagesql,ARRAY_A);
      if($imagedata[0]['image'] != null)
        {
        $imagepath = $imagedir . $imagedata[0]['image'];
        $image_output = $imagedir . $imagedata[0]['image'];
        include("image_resize.php");
        }
      }
   if($_POST['special'] == 'yes')
     {
     $special = 1;
     }
     else
       {
       $special = 0;
       }

   if($_POST['notax'] == 'yes')
     {
     $notax = 1;
     }
     else
       {
       $notax = 0;
       }
        
    if(is_numeric($_POST['category_parent']))
      {
      $parent_category = $_POST['category_parent'];
      }
      else
        {
        $parent_category = 0;
        }
        
    $updatesql = "UPDATE `".$wpdb->prefix."product_categories` SET `name` = '".$wpdb->escape($_POST['title'])."', `description` = '".$wpdb->escape($_POST['description'])."', `category_parent` = '$parent_category'  WHERE `id`='".$_POST['prodid']."' LIMIT 1";
    $wpdb->query($updatesql);
    if($image != null)
      {
      $imagesql = "UPDATE `".$wpdb->prefix."product_categories` SET `image` = '$image'  WHERE `id`='".$_POST['prodid']."' LIMIT 1";
      $wpdb->query($imagesql);
      }
    if($_POST['deleteimage'] == 1)
      {
      $imagesql = "UPDATE `".$wpdb->prefix."product_categories` SET `image` = ''  WHERE `id`='".$_POST['prodid']."' LIMIT 1";
      $wpdb->query($imagesql);
      }
   echo "<div class='updated'><p align='center'>".TXT_WPSC_CATEGORYHASBEENEDITED."</p></div>";
     }
  

if(is_numeric($_GET['deleteid']))
  {
  $deletesql = "UPDATE `".$wpdb->prefix."product_categories` SET  `active` = '0' WHERE `id`='".$_GET['deleteid']."' LIMIT 1";
  $wpdb->query($deletesql);
  $delete_subcat_sql = "UPDATE `".$wpdb->prefix."product_categories` SET  `active` = '0' WHERE `category_parent`='".$_GET['deleteid']."'";
  $wpdb->query($delete_subcat_sql);
  }

?>

<script language='javascript' type='text/javascript'>
function conf()
  {
  var check = confirm("<?php echo TXT_WPSC_SURETODELETECATEGORY;?>");
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
    echo "fillcategoryform(".$_POST['prodid'].");";
    }
?>
</script>
<noscript>
</noscript>
<div class="wrap">
  <h2><?php echo TXT_WPSC_DISPLAYCATEGORIES;?></h2>
  <a href='' onclick='return showaddform()' class='add_item_link'><img src='../wp-content/plugins/wp-shopping-cart/images/package_add.png' alt='<?php echo TXT_WPSC_ADD; ?>' title='<?php echo TXT_WPSC_ADD; ?>' />&nbsp;<span><?php echo TXT_WPSC_ADDCATEGORY;?></span></a>
  <span id='loadingindicator_span'><img id='loadingimage' src='../wp-content/plugins/wp-shopping-cart/images/indicator.gif' alt='Loading' title='Loading' /></span><br />
  <?php
  $num = 0;
echo "  <table id='productpage'>\n\r";
echo "    <tr><td>\n\r";
echo "      <table id='itemlist'>\n\r";
echo "        <tr class='firstrow'>\n\r";

echo "          <td>\n\r";
echo TXT_WPSC_IMAGE;
echo "          </td>\n\r";

echo "          <td>\n\r";
echo TXT_WPSC_NAME;
echo "          </td>\n\r";

echo "          <td>\n\r";
echo TXT_WPSC_DESCRIPTION;
echo "          </td>\n\r";

echo "          <td>\n\r";
echo TXT_WPSC_EDIT;
echo "          </td>\n\r";

echo "        </tr>\n\r";


display_categories();
// $category_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '0' ORDER BY `id`";
// $category_list = $wpdb->get_results($category_sql,ARRAY_A);
// if($category_list != null)
//   {
//   foreach($category_list as $category)
//     {
//     display_category_row($category);
//     $subcategory_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1' AND `category_parent` = '".$category['id']."' ORDER BY `id`";
//     $subcategory_list = $wpdb->get_results($subcategory_sql,ARRAY_A);
//     if($subcategory_list != null)
//       {
//       foreach($subcategory_list as $subcategory)
//         {
//         display_category_row($subcategory, 1);
//         }
//       }
//     }
//   }
  
echo "      </table>\n\r";
echo "      </td><td class='secondcol'>\n\r";
echo "        <div id='productform'>";
echo "<form method='POST'  enctype='multipart/form-data' name='editproduct$num'>";
echo "        <table class='producttext'>\n\r";;    

echo "          <tr>\n\r";
echo "            <td colspan='2'>\n\r";
echo "<strong>".TXT_WPSC_EDITCATEGORY."</strong>";
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
  <table>
    <tr>
      <td colspan='2'>
        <strong><?php echo TXT_WPSC_ADDCATEGORY;?></strong>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_NAME;?>:
      </td>
      <td>
        <input type='text' name='name' value=''  />
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_DESCRIPTION;?>:
      </td>
      <td>
        <textarea name='description' cols='40' rows='8'></textarea>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_CATEGORY_PARENT;?>:
      </td>
      <td>
        <?php echo parent_category_list(); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_IMAGE;?>:
      </td>
      <td>
        <input type='file' name='image' value='' />
      </td>
    </tr>
<?php
if(function_exists("getimagesize"))
  {
?>
    <tr>
      <td>
      </td>
      <td>
        <?php echo TXT_WPSC_HEIGHT;?>:<input type='text' size='6' name='height' value='<?php echo get_option('category_image_height'); ?>' /> <?php echo TXT_WPSC_WIDTH;?>:<input type='text' size='6' name='width' value='<?php echo get_option('category_image_width'); ?>' /> <br /><span class='small'><?php echo $nzshpcrt_imagesize_info; ?></span>
      </td>
    </tr>
<?php
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
echo "     </table>\n\r";
  ?>
</div>