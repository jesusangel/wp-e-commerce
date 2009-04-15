<?php
if(!is_numeric($_GET['category_group']) || ((int)$_GET['category_group'] == null)) {
  $current_categorisation =  $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."wpsc_categorisation_groups` WHERE `active` IN ('1') AND `default` IN ('1') LIMIT 1 ",ARRAY_A);
} else {
  $current_categorisation =  $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."wpsc_categorisation_groups` WHERE `active` IN ('1') AND `id` IN ('".(int)$_GET['category_group']."') LIMIT 1 ",ARRAY_A);
}

 function wpsc_category_tm(){
 global $wpdb;
 /* START OF TARGET MARKET SELECTION */					
	$countrylist = $wpdb->get_results("SELECT id,country,visible FROM `".$wpdb->prefix."currency_list` ORDER BY country ASC ",ARRAY_A);
?>
	<tr>
		<td colspan="2"><h4>Target Market Restrictions</h4></td></tr><tr>
		<td>
		<?php echo TXT_WPSC_TM; ?>:
		</td>
		<td>
		<div id='resizeable' class='ui-widget-content multiple-select'>
		<input type='checkbox' name='countrylist2[]' value='all' />Select All<br />
		<input type='checkbox' name='countrylist2[]' value='none' />Uncheck All<br />
		<?php
		
		foreach($countrylist as $country){
			if($country['visible'] == 1){
			echo "<input type='checkbox' name='countrylist2[]' value='".$country['id']."'  checked='".$country['visible']."' />".$country['country']."<br />";
			}else{
			echo "<input type='checkbox' name='countrylist2[]' value='".$country['id']."'  />".$country['country']."<br />";
			}
				
		}

		?>		
		
		</div><br />
		Select the markets you are selling this category to.
		</td>
	
	</tr>
<?php
}
function admin_categorylist($curent_category) {
  global $wpdb;
  $options = "";
  //$options .= "<option value=''>".TXT_WPSC_SELECTACATEGORY."</option>\r\n";
  $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_categories` ORDER BY `id` ASC",ARRAY_A);
  foreach($values as $option) {
    if($curent_category == $option['id']) {
      $selected = "selected='selected'";
		}
    $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
    $selected = "";
	}
  $concat .= "<select name='category'>".$options."</select>\r\n";
  return $concat;
}

function display_categories($group_id, $id = null, $level = 0) {
  global $wpdb,$category_data;
  if(is_numeric($id)) {
    $category_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `group_id` IN ('$group_id') AND `active`='1' AND `category_parent` = '".$id."' ORDER BY `id`";
    $category_list = $wpdb->get_results($category_sql,ARRAY_A);
	} else {
		$category_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `group_id` IN ('$group_id') AND `active`='1' AND `category_parent` = '0' ORDER BY `id`";
		$category_list = $wpdb->get_results($category_sql,ARRAY_A);
	}
  if($category_list != null) {
    foreach($category_list as $category) {
      display_category_row($category, $level);
      display_categories($group_id, $category['id'], ($level+1));
		}
	}
}

function display_category_row($category,$subcategory_level = 0) {
  echo "     <tr>\n\r";
  echo "       <td colspan='4' class='colspan'>\n\r";
  if($subcategory_level > 0) {
    echo "<div class='subcategory' style='padding-left: ".(1*$subcategory_level)."em;'>";
    echo "<img class='category_indenter' src='".WPSC_URL."/images/indenter.gif' alt='' title='' />";
	}
  echo "        <table class='itemlist'>\n\r";
  echo "          <tr>\n\r";
  echo "            <td>\n\r";
  if($category['image'] !=null) {
		echo "<img src='".WPSC_CATEGORY_URL.$category['image']."' title='".$category['name']."' alt='".$category['name']."' width='35' height='35' />";
	} else {
		echo "<img style='border-style:solid; border-color: red' src='".WPSC_URL."/images/no-image-uploaded.gif' title='".$category['name']."' alt='".$category['name']."' width='35' height='35'  />";
	}
  echo "            </td>\n\r";
  
  echo "            <td>\n\r";
  echo "".htmlentities(stripslashes($category['name']), ENT_QUOTES, 'UTF-8')." (".$category['id'].")";
  echo "            </td>\n\r";
  /*
  $displaydescription = substr(stripslashes($category['description']),0,44);
  if($displaydescription != $category['description']) {
    $displaydescription_arr = explode(" ",$displaydescription);
    $lastword = count($displaydescription_arr);
    if($lastword > 1) {
      unset($displaydescription_arr[$lastword-1]);
      $displaydescription = '';
      $j = 0;
      foreach($displaydescription_arr as $displaydescription_row) {
        $j++;
        $displaydescription .= $displaydescription_row;
        if($j < $lastword -1) {
          $displaydescription .= " ";
				}
			}
		}
    $displaydescription .= "...";
	}
  
  echo "            <td>\n\r";
  echo "".stripslashes($displaydescription)."";
  echo "            </td>\n\r";
  */
  echo "            <td>\n\r";
  echo "<a href='#' onclick='fillcategoryform(".$category['id'].");return false;'>".TXT_WPSC_EDIT."</a>";
  echo "            </td>\n\r";
  echo "          </tr>\n\r";
  echo "        </table>";
  
  if($subcategory_level > 0) {
    echo "</div>";
	}
  echo "       </td>\n\r";
  echo "      </tr>\n\r";
}

   // Jeff 15-04-09 Used for category target market options
	   
    if($_POST['countrylist2'] != null){
    	exit('Work in Progress.<br /><pre>'.print_r($_POST['countrylist2']).'</pre>');
    	$AllSelected = false;
    	if(in_array('all',$_POST['countrylist2'])){
    		$countryList = $wpdb->get_col("SELECT `id` FROM  `".$wpdb->prefix."currency_list`");
    		//$wpdb->query("UPDATE `".$wpdb->prefix."wpsc_category_tm` SET visible = '1'");
			//$AllSelected = true;
    	}
    	if(in_array('none', $_POST['countrylist2'])){
    		$wpdb->query("UPDATE `".$wpdb->prefix."wpsc_category_tm` SET visible = '0'");
			$AllSelected = true;
    	}
    	if($AllSelected != true){
	//		$countrylist = $wpdb->get_col("SELECT id FROM `".$wpdb->prefix."wpsc_category_tm` ORDER BY country ASC ");
			//find the countries not selected 
			$unselectedCountries = array_diff($countrylist, $_POST['countrylist2']);
			foreach($unselectedCountries as $unselected){
	//			$wpdb->query("UPDATE `".$wpdb->prefix."wpsc_category_tm` SET visible = 0 WHERE id = '".$unselected."' LIMIT 1");
			} 
	
			//find the countries that are selected
			$selectedCountries = array_intersect($countrylist, $_POST['countrylist2']);
			foreach($selectedCountries as $selected){
			//	$wpdb->query("UPDATE `".$wpdb->prefix."wpsc_category_tm` SET visible = 1	WHERE id = '".$selected."' LIMIT 1");
			}
 		}
	} 

  if($_POST['submit_action'] == "add") {
    //exit("<pre>".print_r($_POST,true)."</pre>"); 
    if(($_FILES['image'] != null) && preg_match("/\.(gif|jp(e)*g|png){1}$/i",$_FILES['image']['name'])) {
      if(function_exists("getimagesize")) {
				if(((int)$_POST['width'] > 10 && (int)$_POST['width'] < 512) && ((int)$_POST['height'] > 10 && (int)$_POST['height'] < 512) ) {
					$width = (int)$_POST['width'];
					$height = (int)$_POST['height'];
					image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']), $width, $height);
				} else {
					image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']));
				}  
				$image = $wpdb->escape($_FILES['image']['name']);
			} else {
				$new_image_path = (WPSC_CATEGORY_DIR.basename($_FILES['image']['name']));
				move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path);
				$stat = stat( dirname( $new_image_path ));
				$perms = $stat['mode'] & 0000666;
				@ chmod( $new_image_path, $perms );	
				$image = $wpdb->escape($_FILES['image']['name']);
			}
		} else {
			$image = '';
		}
    
    if(is_numeric($_POST['category_parent'])) {
      $parent_category = (int)$_POST['category_parent'];
		} else {
      $parent_category = 0;
		}
      
   
    $url_name = sanitize_title($_POST['name']);
    $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".$wpdb->prefix."product_categories` WHERE `nice-name` REGEXP '^($url_name){1}(\d)*$' ",ARRAY_A);
    $extension_number = '';
    if($similar_names['count'] > 0) {
      $extension_number = (int)$similar_names['max_number']+1;
		}
    $url_name .= $extension_number;
    
    
    
		switch($_POST['display_type']) {
			case "grid":
				$display_type = 'grid';
			break;
			
			case "list":
				$display_type = 'list';
			break;
			
			case "default":
				$display_type = 'default';
			break;
			
			default:
				$display_type = '';
			break;
		}
      
      
    if($_POST['product_height'] > 0) {
      $product_height = (int)$_POST['product_height'];
    } else {
      $product_height = '';
    }
    
    
    if($_POST['product_width'] > 0) {
      $product_width = (int)$_POST['product_width'];
    } else {
      $product_width = '';
    }
    
    if(trim($_POST['name']) != null) {
      $insertsql = "INSERT INTO `".$wpdb->prefix."product_categories` (`group_id`, `name` , `nice-name` , `description`, `image`, `fee` , `active`, `category_parent`, `order` ) VALUES ( '".(int)$_POST['categorisation_group']."', '".$wpdb->escape(stripslashes($_POST['name']))."', '".$url_name."', '".$wpdb->escape(stripslashes($_POST['description']))."', '$image', '0', '1' ,'$parent_category', '0')";
      $wp_rewrite->flush_rules(); 
      if($wpdb->query($insertsql)) {
        echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASBEENADDED."</p></div>";
      } else {
        echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASNOTBEENADDED."</p></div>";
      }
      $wp_rewrite->flush_rules();
    } else {
      echo "<div class='updated'><p align='center'>".TXT_WPSC_ITEMHASNOTBEENADDED."</p></div>";
    }
	}

  if(($_POST['submit_action'] == "edit") && is_numeric($_POST['prodid'])) {
    if(($_FILES['image'] != null) && preg_match("/\.(gif|jp(e)*g|png){1}$/i",$_FILES['image']['name'])) {
      if(function_exists("getimagesize")) {
      		if(((int)$_POST['width'] >= 10 && (int)$_POST['width'] <= 512) && ((int)$_POST['height'] >= 10 && (int)$_POST['height'] <= 512) ) {
      		  $height = (int)$_POST['width'];
      		  $height = (int)$_POST['height'];
						image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']), $width, $height);
    		  } else {
						image_processing($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']));
    		  }  
					$image = $wpdb->escape($_FILES['image']['name']);
        } else {
					move_uploaded_file($_FILES['image']['tmp_name'], (WPSC_CATEGORY_DIR.$_FILES['image']['name']));
					$image = $wpdb->escape($_FILES['image']['name']);
        }
      } else {
				$image = '';
      }
    
    if(is_numeric($_POST['height']) && is_numeric($_POST['width']) && ($image == null)) {
      $imagesql = "SELECT `image` FROM `".$wpdb->prefix."product_categories` WHERE `id`=".(int)$_POST['prodid']." LIMIT 1";
      $imagedata = $wpdb->get_results($imagesql,ARRAY_A);
      if($imagedata[0]['image'] != null) {
        $height = $_POST['height'];
        $width = $_POST['width'];
        $imagepath = WPSC_CATEGORY_DIR . $imagedata[0]['image'];
        $image_output = WPSC_CATEGORY_DIR . $imagedata[0]['image'];
        image_processing($imagepath, $image_output, $width, $height);
			}
		}
   
    $category_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `id` IN ('".(int)$_POST['prodid']."')", ARRAY_A);
    
    if(($_POST['title'] != $category_data['name']) && (trim($_POST['title']) != null)) {
      $category_name = $_POST['title'];
      $category_sql_list[] = "`name` = '$category_name' ";
      
      /* creates and checks the tidy URL name */     
      $tidied_name = trim($category_name);
      $tidied_name = strtolower($tidied_name);
      $url_name = preg_replace(array("/(\s-\s)+/","/(\s)+/","/[^\w-]+/i"), array("-","-", ''), $tidied_name);
      if($url_name != $category_data['nice-name']) {
        $similar_names = $wpdb->get_row("SELECT COUNT(*) AS `count`, MAX(REPLACE(`nice-name`, '$url_name', '')) AS `max_number` FROM `".$wpdb->prefix."product_categories` WHERE `nice-name` REGEXP '^($url_name){1}(0-9)*$' AND `id` NOT IN ('".(int)$category_data['id']."') ",ARRAY_A);
        //exit("<pre>".print_r($similar_names,true)."</pre>");
        $extension_number = '';
        if($similar_names['count'] > 0) {
          $extension_number = (int)$similar_names['max_number']+1;
				}
        $url_name .= $extension_number;   
			}
      /* checks again, just in case */
      if($url_name != $category_data['nice-name']) {
        $category_sql_list[] = "`nice-name` = '$url_name' ";
			}
      $wp_rewrite->flush_rules(); 
		}   
    
    if($_POST['description'] != $category_data['description']) {
      $description = $_POST['description'];
      $category_sql_list[] = "`description` = '$description' ";
		}
      
    if(is_numeric($_POST['category_parent']) and ($_POST['category_parent'] != $category_data['category_parent'])) {
      $parent_category = (int)$_POST['category_parent'];
      $category_sql_list[] = "`category_parent` = '$parent_category' ";
		}      
    
    if($_POST['deleteimage'] == 1) {
      $category_sql_list[] = "`image` = ''";
		} else {
      if($image != null) {
        $category_sql_list[] = "`image` = '$image'";
			}
		}

		if($_POST['display_type'] != $category_data['display_type']) {
			switch($_POST['display_type']) {
				case "grid":
					$display_type = 'grid';
				break;
				
				case "list":
					$display_type = 'list';
				break;
				
				case "default":
					$display_type = 'default';
				break;
				
				default:
					$display_type = '';
				break;
			}
      $category_sql_list[] = "`display_type` = '$display_type' ";
		}

		

		  //echo "<pre>".print_r($category_sql_list,true)."</pre>";

    if($_POST['product_height'] > 0) {
      $product_height = (int)$_POST['product_height'];
    } else {
      $product_height = '';
    }
		$category_sql_list[] = "`image_height` = '$product_height' ";
    
    if($_POST['product_width'] > 0) {
      $product_width = (int)$_POST['product_width'];
    } else {
      $product_width = '';
    }
		$category_sql_list[] = "`image_width` = '$product_height' ";

    if(count($category_sql_list) > 0) {
      $category_sql = implode(", ",$category_sql_list);
      $wpdb->query("UPDATE `".$wpdb->prefix."product_categories` SET $category_sql WHERE `id`='".(int)$_POST['prodid']."' LIMIT 1");
      $wp_rewrite->flush_rules(); 
		}
    echo "<div class='updated'><p align='center'>".TXT_WPSC_CATEGORYHASBEENEDITED."</p></div>";
	}
  
if($_POST['submit_action'] == "add_categorisation") {  
  $wpdb->query("INSERT INTO `{$wpdb->prefix}wpsc_categorisation_groups` ( `name`, `description`, `active`, `default`) VALUES ( '".$wpdb->escape(stripslashes($_POST['name']))."', '".$wpdb->escape(stripslashes($_POST['description']))."', '1', '0')");
	echo "<div class='updated'><p align='center'>".TXT_WPSC_CATEGORISATIONHASBEENADDED."</p></div>";  

}


  
if($_POST['submit_action'] == "edit_categorisation") {  
  $edit_group_id = $_POST['group_id'];
  
  $wpdb->query("UPDATE `{$wpdb->prefix}wpsc_categorisation_groups` SET `name` = '".$wpdb->escape(stripslashes($_POST['name']))."', `description` = '".$wpdb->escape(stripslashes($_POST['description']))."' WHERE `id` IN('$edit_group_id') LIMIT 1 ");
	
	
	echo "<div class='updated'><p align='center'>".TXT_WPSC_CATEGORISATIONHASBEENEDITED."</p></div>";  
	
		
	if(!is_numeric($_GET['category_group']) || ((int)$_GET['category_group'] == null)) {
		$current_categorisation =  $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."wpsc_categorisation_groups` WHERE `active` IN ('1') AND `default` IN ('1') LIMIT 1 ",ARRAY_A);
	} else {
		$current_categorisation =  $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."wpsc_categorisation_groups` WHERE `active` IN ('1') AND `id` IN ('".(int)$_GET['category_group']."') LIMIT 1 ",ARRAY_A);
	}
}


if(is_numeric($_GET['category_delete_id'])) {
  $delete_id = (int)$_GET['category_delete_id'];
  $deletesql = "UPDATE `".$wpdb->prefix."wpsc_categorisation_groups` SET `active` = '0' WHERE `id`='{$delete_id}' AND `default` IN ('0') LIMIT 1";
  $wpdb->query($deletesql);
  $delete_subcat_sql = "UPDATE `".$wpdb->prefix."product_categories` SET `active` = '0', `nice-name` = '' WHERE `group_id`='{$delete_id}'";
  $wpdb->query($delete_subcat_sql);
	$wp_rewrite->flush_rules(); 
}


if(is_numeric($_GET['deleteid'])) {
  $delete_id = (int)$_GET['deleteid'];
  $deletesql = "UPDATE `".$wpdb->prefix."product_categories` SET `active` = '0', `nice-name` = '' WHERE `id`='{$delete_id}' LIMIT 1";
  if($wpdb->query($deletesql)) {
		$delete_subcat_sql = "UPDATE `".$wpdb->prefix."product_categories` SET `active` = '0', `nice-name` = '' WHERE `category_parent`='{$delete_id}'";
		$wpdb->query($delete_subcat_sql);
		// if this is the default category, we need to find a new default category
		if($delete_id == get_option('wpsc_default_category')) {
			// select the category that is not deleted with the greatest number of products in it
			$new_default = $wpdb->get_var("SELECT `cat`.`id` FROM `{$wpdb->prefix}product_categories` AS `cat`
				LEFT JOIN `{$wpdb->prefix}item_category_associations` AS `assoc` ON `cat`.`id` = `assoc`.`category_id`
				WHERE `cat`.`active` IN ( '1' )
				GROUP BY `cat`.`id`
				ORDER BY COUNT( `assoc`.`id` ) DESC
				LIMIT 1");
			if($new_default > 0) {
				update_option('wpsc_default_category', $new_default);
			}
		}
		$wp_rewrite->flush_rules(); 
	}
}

?>
<script language='javascript' type='text/javascript'>
function conf() {
  var check = confirm("<?php echo TXT_WPSC_SURETODELETECATEGORY;?>");
  if(check) {
    return true;
	} else {
	  return false;
	}
}
function categorisation_conf() {
  var check = confirm("<?php echo TXT_WPSC_SURETODELETECATEGORISATION;?>");
  if(check) {
    return true;
	} else {
	  return false;
	}
}

<?php
  if(is_numeric($_POST['prodid'])) {
    echo "fillcategoryform(".$_POST['prodid'].");";
	}
?>
</script>
<div class="wrap">
  <h2><?php echo TXT_WPSC_CATEGORISATION;?></h2>
    <?php
  
  
	if(function_exists('add_object_page')) {
		echo "<div id='dashboard-widgets' class='metabox-holder'>";
	}
?>
  <span><?php echo TXT_WPSC_CATEGORISATION_GROUPS_DESCR;?></span>
  
<?php
	if (function_exists('add_object_page')) {
		echo "<div class='wpsc_products_nav27'>";
	} else {
		echo "<div class='tablenav wpsc_groups_nav' >";
	}
?>

	<div class="alignleft product_group" style='width: 500px;'>
	  <form action='' method='GET' id='submit_categorisation_form' >
	  <input type='hidden' value='<?php echo $_GET['page']; ?>' name='page'  />
	  <?php
	  $categorisation_groups =  $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpsc_categorisation_groups` WHERE `active` IN ('1')", ARRAY_A);
		//echo "<ul class='categorisation_links'>\n\r";
		echo "<label for='select_categorisation_group' class='select_categorisation_group'>".TXT_WPSC_SELECT_PRODUCT_GROUP.":&nbsp;&nbsp;</label>";
		echo "<select name='category_group' id='select_categorisation_group' onchange='submit_status_form(\"submit_categorisation_form\")'>"; 
		foreach((array)$categorisation_groups as $categorisation_group) {
			$selected = '';
			if($current_categorisation['id'] == $categorisation_group['id']) {
				//$selected = "class='selected'";
				$selected = "selected='selected'";
			}
			echo "<option value='{$categorisation_group['id']}' $selected >{$categorisation_group['name']}</option>";
			//echo "  <li $selected >\n\r";
			//echo "    <a href='?page={$_GET['page']}&amp;category_group={$categorisation_group['id']}'>{$categorisation_group['name']}</a> ";
			//echo "  </li>\n\r";
		}
		echo "</select>"; 
		//echo "<li>- <a href='' onclick='return showadd_categorisation_form()'><span>".TXT_WPSC_ADD_CATEGORISATION."</span></a></li>";
		//echo "</ul>\n\r";
	  ?>
		
		<?php echo "<a class='button add_categorisation_group' href='#' onclick='return showadd_categorisation_form()'><span>".TXT_WPSC_ADD_CATEGORISATION."</span></a>"; ?>
	  </form>
	</div>
	
	  
	<!--
<div class="alignright">
		<a target="_blank" href='http://www.instinct.co.nz/e-commerce/product-groups/' class='about_this_page'><span><?php echo TXT_WPSC_ABOUT_THIS_PAGE;?></span>&nbsp;</a>
	</div>
-->
	<br class="clear"/>
</div>




<div id='add_categorisation'>
  <strong><?php echo TXT_WPSC_ADD_CATEGORISATION;?></strong>
	<form method='POST' enctype='multipart/form-data'>
  
		<fieldset>
		<label for='add_categorisation_name'>Name</label>
		<input type='text' name='name' value='' id='add_categorisation_name' />
		</fieldset>
		
		<fieldset>
		<label for='add_categorisation_description'>Description</label>
		<input type='text' name='description' value='' id='add_categorisation_description' />
		</fieldset>
		
		<fieldset>
		<label>&nbsp;</label>
		
		<input type='hidden' name='submit_action' value='add_categorisation' />
		<input type='submit' name='submit_form' value='<?php echo TXT_WPSC_SUBMIT; ?>' />
		</fieldset>
	</form>
	<br/>
</div>

<div id='edit_categorisation'>
  <strong><?php echo TXT_WPSC_EDIT_CATEGORISATION;?></strong>
  
  <form method='POST' enctype='multipart/form-data'>
  
		<fieldset>
			<label for='add_categorisation_name'>Name</label>
			<input type='text' name='name' value='<?php echo $current_categorisation['name']; ?>' id='add_categorisation_name' />
		</fieldset>
		
		<fieldset>
			<label for='add_categorisation_description'>Description</label>
			<input type='text' name='description' value='<?php echo $current_categorisation['description']; ?>' id='add_categorisation_description' />
		</fieldset>
		
		<fieldset>
			<label>&nbsp;</label>		
			<input type='hidden' name='group_id' value='<?php echo $current_categorisation['id']; ?>' />
			<input type='hidden' name='submit_action' value='edit_categorisation' />
			<input type='submit' name='submit_form' value='<?php echo TXT_WPSC_SUBMIT; ?>' />
			<?php if($current_categorisation['default'] != 1) { ?>
			<a href='<?php echo "?page={$_GET['page']}&amp;category_delete_id={$current_categorisation['id']}"  ?>' onclick='return categorisation_conf()' > <?php echo TXT_WPSC_DELETE; ?></a>
			<?php 	} ?>
		</fieldset>
	</form>
	<br/>
</div>

<?php
 
$num = 0;

echo "  <table id='productpage' style='margin-top: 1ex;'>\n\r";
echo "    <tr><td class='firstcol'>\n\r";
if (function_exists('add_object_page')){
	echo "<div class='postbox' style='margin-right: 15px; min-width:255px;'>";
	echo "<h3 class='hndle'>".ucfirst(TXT_WPSC_GROUPCOUNT_PLURAL)."</h3>";
	echo "<div class='inside'>";
}
//echo "<div class='categorisation_title'><a href='' onclick='return showaddform()' class='add_category_link'><span>". TXT_WPSC_ADDNEWCATEGORY."</span></a><strong class='form_group'>".str_replace("[categorisation]", $current_categorisation['name'], TXT_WPSC_MANAGE_CATEGORISATION)." <a href='#' onclick='return showedit_categorisation_form()'>[".TXT_WPSC_EDIT."]</a> </strong></div>";
echo "      <table id='itemlist'>\n\r";
if (function_exists('add_object_page')) {
	echo "<tr></tr>";
} else {
	echo "        <tr class='firstrow categorisation_title'>\n\r";
	echo "          <td>\n\r";
	echo TXT_WPSC_IMAGE;
	echo "          </td>\n\r";
	
	echo "          <td>\n\r";
	echo TXT_WPSC_NAME;
	echo "          </td>\n\r";
	
	echo "          <td>\n\r";
	//echo TXT_WPSC_DESCRIPTION;
	echo "          </td>\n\r";
	
	echo "          <td>\n\r";
	echo TXT_WPSC_EDIT;
	echo "          </td>\n\r";
	
	echo "        </tr>\n\r";
}



echo "     <tr>\n\r";
echo "       <td colspan='4' class='colspan'>\n\r";
echo "<div class='editing_this_group'><p>";
echo str_replace("[categorisation]", $current_categorisation['name'], TXT_WPSC_EDITING_GROUP);

echo "       [ <a href='#' onclick='return showedit_categorisation_form()'>".TXT_WPSC_EDIT_THIS_GROUP."</a> ]";

echo "</p></div>";
echo "<a href='' onclick='return showaddform()' class='add_category_link'><span>". TXT_WPSC_ADDNEWCATEGORY."</span></a>";
echo "       </td>\n\r";
echo "     <tr>\n\r";

display_categories($current_categorisation['id']);
if (function_exists('add_object_page')){
	echo "</table>";
	echo "</div>"; //class inside ends
	echo "</div>"; //class postbox ends
} else {
	echo "</table>\n\r";
}
echo "      </td><td class='secondcol product_groups_page'>\n\r";
echo "        <div id='productform' class='postbox'>";
echo "<form method='POST'  enctype='multipart/form-data' name='editproduct$num'>\n\r";

if (function_exists('add_object_page')) {
	echo "<h3 class='hndle'>".str_replace("[categorisation]", $current_categorisation['name'], TXT_WPSC_EDITING_IN_GROUP)."</h3>";
	echo "<div class='inside'>";
} else {
	echo "<div class='categorisation_title'><strong class='form_group'>".TXT_WPSC_EDITDETAILS." </strong></div>\n\r";
echo "<div class='editing_this_group'><p>".str_replace("[categorisation]", $current_categorisation['name'], TXT_WPSC_EDITING_IN_GROUP) ."</p></div>";
}


echo "        <div id='formcontent'>\n\r";
echo "        </div>\n\r";
if (function_exists('add_object_page')) {
	echo "</div>";
}
echo "</form>\n\r";
echo "        </div>\n\r";
?>

<?php
if (function_exists('add_object_page')) {
	echo "<div id='additem' class='postbox'>";
	echo "<h3 class='hndle'>".str_replace("[categorisation]", $current_categorisation['name'], TXT_WPSC_ADDING_TO_GROUP)."</h3>";
	echo "<div class='inside'>";
} else {
	echo "<div id='additem'>";
	echo "<div class='categorisation_title'><strong class='form_group'>".TXT_WPSC_ADDDETAILS."</strong></div>";
}
?>
  <form method='POST' enctype='multipart/form-data' class='additem'>
	<div class='editing_this_group'><p> <?php echo "".str_replace("[categorisation]", $current_categorisation['name'], TXT_WPSC_ADDING_TO_GROUP) .""; ?></p></div>
  <table class='category_forms'>
    <tr>
      <td>
        <?php echo TXT_WPSC_NAME;?>:
      </td>
      <td>
        <input type='text' class="text" name='name' value=''  />
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_DESCRIPTION;?>:
      </td>
      <td>
        <textarea name='description' rows='8'></textarea>
      </td>
    </tr>   
    
       <tr>
      <td>
        <?php echo TXT_WPSC_CATEGORY_PARENT;?>:
      </td>
      <td>
        <?php echo wpsc_parent_category_list($current_categorisation['id'], 0,0); ?>
      </td>
    </tr>
    <tr>
      <td>
        <?php echo TXT_WPSC_GROUP_IMAGE;?>:
      </td>
      <td>
        <input type='file' name='image' value='' />
      </td>
    </tr>
<?php
if(function_exists("getimagesize")) {
		?>
		<tr>
			<td>
			</td>
			<td>
				<?php echo TXT_WPSC_HEIGHT;?>:<input type='text' size='6' name='height' value='<?php echo get_option('category_image_height'); ?>' /> <?php echo TXT_WPSC_WIDTH;?>:<input type='text' size='6' name='width' value='<?php echo get_option('category_image_width'); ?>' /> <br />
				<span class='small'><?php echo TXT_WPSC_GROUP_IMAGE_TEXT; ?></span>
			</td>
		</tr>
		<?php
}
?>
	<?php  wpsc_category_tm(); ?>
    <tr>
      <td colspan='2' class='category_presentation_settings'>
        <h4><?php echo TXT_WPSC_PRESENTATIONSETTINGS;?></h4>
        <span class='small'><?php echo TXT_WPSC_GROUP_PRESENTATION_TEXT; ?></span>
      </td>
		</tr>
    
    
    
    <tr>
      <td>
        <?php echo TXT_WPSC_CATALOG_VIEW;?>:
      </td>
      <td>
    			<select name='product_view'>
						<option value='default' <?php echo $product_view1; ?>><?php echo TXT_WPSC_DEFAULT;?></option>
						<?php
						if(function_exists('product_display_list')) {
							?>
							<option value='list' <?php echo $product_view2; ?>><?php echo TXT_WPSC_LIST;?></option>
							<?php      
						}  else {
							?>
							<option value='list' disabled='disabled' <?php echo $product_view2; ?>><?php echo TXT_WPSC_LIST;?></option>
							<?php
						}
						
						if(function_exists('product_display_grid')) {
							?>
						<option value='grid' <?php echo $product_view3; ?>><?php echo TXT_WPSC_GRID;?></option>
							<?php   
						} else {
							?>
						<option value='grid' disabled='disabled' <?php echo $product_view3; ?>><?php echo TXT_WPSC_GRID;?></option>
							<?php 
						}
						?>
					</select>
      </td>
    </tr>
    
		<tr>
			<td>
			<?php echo TXT_WPSC_THUMBNAIL_SIZE; ?>:
			</td>
			<td>
				<?php echo TXT_WPSC_HEIGHT; ?>: <input type='text' value='' name='product_height' size='6'/>
				<?php echo TXT_WPSC_WIDTH; ?>: <input type='text' value='' name='product_width' size='6'/> <br/>
			</td>
		</tr>
    
    <tr>
      <td>
      </td>
      <td>
        <input type='hidden' name='categorisation_group' value='<?php echo $current_categorisation['id']; ?>' />
        <input type='hidden' name='submit_action' value='add' />
        <input class='button' type='submit' name='submit' value='<?php echo TXT_WPSC_ADD;?>' />
      </td>
    </tr>
    


    
  </table>
  </form>
</div>
<?php
echo "      </td></tr>\n\r";
echo "     </table>\n\r";
if (function_exists('add_object_page')) {
	echo "</div>";
}
  ?>
</div>
</div>
