<?php
if(preg_match("/[a-zA-Z]{2,4}/",$_GET['isocode']))
  {
  include('tax_and_shipping.php');
  }
  else
  {
  if($_POST != null)
    {
    if($_POST['product_list_url'] != null)
      {
      update_option('product_list_url', $_POST['product_list_url']);
      }
  
    if($_POST['shopping_cart_url'] != null)
      {
      update_option('shopping_cart_url', $_POST['shopping_cart_url']);
      }
  
    if($_POST['checkout_url'] != null)
      {
      update_option('checkout_url', $_POST['checkout_url']);
      }
  
    if($_POST['transact_url'] != null)
      {
      update_option('transact_url', $_POST['transact_url']);
      }
  
    if($_POST['gst_rate'] != null)
      {
      $gst_rate = ($_POST['gst_rate']/100) + 1;
      update_option('gst_rate', $gst_rate);
      }
  
    if($_POST['purch_log_email'] != null)
      {
      update_option('purch_log_email', $_POST['purch_log_email']);
      }
  
    if($_POST['return_email'] != null)
      {
      update_option('return_email', $_POST['return_email']);
      }
  
    if($_POST['terms_and_conditions'] != get_option('terms_and_conditions'))
      {
      update_option('terms_and_conditions', $_POST['terms_and_conditions']);
      }
  
    if($_POST['product_image_height'] != get_option('product_image_height'))
      {
      update_option('product_image_height', $_POST['product_image_height']);
      }
    if($_POST['product_image_width'] != get_option('product_image_width'))
      {
      update_option('product_image_width', $_POST['product_image_width']);
      }
  
    if($_POST['category_image_height'] != get_option('category_image_height'))
      {
      update_option('category_image_height', $_POST['category_image_height']);
      }
  
    if($_POST['category_image_width'] != get_option('category_image_width'))
      {
      update_option('category_image_width', $_POST['category_image_width']);
      }
  
    if(is_numeric($_POST['max_downloads']))
      {
      update_option('max_downloads', $_POST['max_downloads']);
      }
  
    if(is_numeric($_POST['postage_and_packaging']))
      {
      update_option('postage_and_packaging', $_POST['postage_and_packaging']);
      }
  
    if(is_numeric($_POST['currency_type']))
      {
      update_option('currency_type', $_POST['currency_type']);
      }
  
    if(is_numeric($_POST['currency_sign_location']))
      {
      update_option('currency_sign_location', $_POST['currency_sign_location']);
      }
  
    if(is_numeric($_POST['cart_location']))
      {
      update_option('cart_location', $_POST['cart_location']);
      }
      
    // pe.{
    if(is_numeric($_POST['cat_brand_loc']))
      {
      update_option('cat_brand_loc', $_POST['cat_brand_loc']);
      }
    // }.pe
  
    if(is_numeric($_POST['show_categorybrands']))
      {
      update_option('show_categorybrands', $_POST['show_categorybrands']);
      }
  
    if($_POST['default_category'] != get_option('default_category'))
      {
      update_option('default_category', $_POST['default_category']);
      }
  
    if($_POST['default_brand'] != get_option('default_brand'))
      {
      update_option('default_brand', $_POST['default_brand']);
      }
  
    if($_POST['product_view'] != get_option('product_view'))
      {
      update_option('product_view', $_POST['product_view']);
      }
  
    if($_POST['show_thumbnails'] == 1)
      {
      update_option('show_thumbnails', 1);
      }
      else
        {
        update_option('show_thumbnails', 0);
        }
  
    if($_POST['display_pnp'] == 1)
      {
      update_option('display_pnp', 1);
      }
      else
        {
        update_option('display_pnp', 0);
        }
  
    if($_POST['display_specials'] == 1)
      {
      update_option('display_specials', 1);
      }
      else
        {
        update_option('display_specials', 0);
        }
        
    if(is_numeric($_POST['product_ratings']))
      {
      update_option('product_ratings', $_POST['product_ratings']);
      }
        
    if(isset($_POST['language_setting']))
      {
      update_option('language_setting', $_POST['language_setting']);
      }
      
    if(isset($_POST['base_local_shipping']))
      {
      update_option('base_local_shipping', $_POST['base_local_shipping']);
      }
      
    if(isset($_POST['base_international_shipping']))
      {
      update_option('base_international_shipping', $_POST['base_international_shipping']);
      }
      
    if(isset($_POST['base_country']))
      {
      update_option('base_country', $_POST['base_country']);
      }
        
    if(is_numeric($_POST['country_id']) && is_numeric($_POST['country_tax']))
      {
      $wpdb->query("UPDATE `".$wpdb->prefix."currency_list` SET `tax` = '".$_POST['country_tax']."' WHERE `id` = '".$_POST['country_id']."' LIMIT 1 ;");
      }      
        
    if(isset($_POST['base_region']))
      {
      update_option('base_region', $_POST['base_region']);
      }
      
    if(is_numeric($_POST['country_form_field']))
      {
      update_option('country_form_field', $_POST['country_form_field']);
      }
      
    if(is_numeric($_POST['email_form_field']))
      {
      update_option('email_form_field', $_POST['email_form_field']);
      }
    
    if($_POST['list_view_quantity'] == 1)
      {
      update_option('list_view_quantity', 1);
      }
      else
        {
        update_option('list_view_quantity', 0);
        }
    echo "<div class='updated'><p align='center'>".TXT_WPSC_THANKSAPPLIED."</p></div>";
    }
  if(get_option('nzshpcrt_first_load') == 0)
    {
    echo "<div class='updated'><p align='center'>".TXT_WPSC_INITIAL_SETUP."</p></div>";
    update_option('nzshpcrt_first_load', 1);
    }
  function options_categorylist()
    {
    global $wpdb;
    $cat_sql = "SELECT * FROM `".$wpdb->prefix."product_categories` WHERE `active`='1'";
    $category_data = $wpdb->get_results($cat_sql,ARRAY_A);
    $current_default = get_option('default_category');
    $categorylist .= "<select name='default_category'>";
    $categorylist .= "<option value='none' ".$selected." >".TXT_WPSC_SELECTACATEGORY."</option>";
    foreach($category_data as $category)
      {
      if(get_option('default_category') == $category['id'])
        {
        $selected = "selected='true'";
        }
        else
          {
          $selected = "";
          }
      $categorylist .= "<option value='".$category['id']."' ".$selected." >".$category['name']."</option>";
      }
    $categorylist .= "</select>";
    return $categorylist;
    }
  
  function brandslist($current_brand = '')
    {
    global $wpdb;
    $options = "";
    $options .= "<option  $selected value='none'>".TXT_WPSC_SELECTABRAND."</option>\r\n";
    $values = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."product_brands` WHERE `active`='1' ORDER BY `id` ASC",ARRAY_A);
    foreach($values as $option)
      {
      if(get_option('default_brand') == $option['id'])
        {
        $selected = "selected='selected'";
        }
      $options .= "<option  $selected value='".$option['id']."'>".$option['name']."</option>\r\n";
      $selected = "";
      }
    $concat .= "<select name='default_brand'>".$options."</select>\r\n";
    return $concat;
    }
    
  function country_list($selected_country = null)
      {
      global $wpdb;
      $output = "";
      $output .= "<option value=''></option>";
      $country_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
      foreach ($country_data as $country)
        {
        $selected ='';
        if($selected_country == $country['isocode'])
          {
          $selected = "selected='true'";
          }
        $output .= "<option value='".$country['isocode']."' $selected>".$country['country']."</option>";
        }
      return $output;
      }
  ?>
  <div class="wrap">
    <h2><?php echo TXT_WPSC_OPTIONS;?></h2>
    <?php
    ?>
    <form name='cart_options' id='cart_options' method='POST' action='?page=wp-shopping-cart/options.php'>
    <table class='options'>
      <tr>
        <td colspan='2'><br />
        <strong class="form_group"><?php echo TXT_WPSC_GENERAL_SETTINGS;?>:</strong>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_BASE_COUNTRY;?>:
        </td>
        <td>
        <select name='base_country' onChange='submit_change_country();'>
        <?php echo country_list(get_option('base_country')); ?>
        </select>
        <span id='options_region'>
        <?php
        $region_list = $wpdb->get_results("SELECT `".$wpdb->prefix."region_tax`.* FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".get_option('base_country')."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`",ARRAY_A) ;
        if($region_list != null)
          {
          echo "<select name='base_region'>\n\r";
          foreach($region_list as $region)
            {
            if(get_option('base_region')  == $region['id'])
              {
              $selected = "selected='true'";
              }
              else
                {
                $selected = "";
                }
            echo "<option value='".$region['id']."' $selected>".$region['name']."</option>\n\r";
            }
          echo "</select>\n\r";    
          }
        
        //
        ?>
        </span>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_TAX_SETTINGS;?>:
        </td>
        <td>
        <span id='options_region'>
        <?php
        $country_data = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."currency_list` WHERE `isocode`='".get_option('base_country')."' LIMIT 1",ARRAY_A);
        echo $country_data['country'];
        
        
        $region_count = $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."region_tax`, `".$wpdb->prefix."currency_list`  WHERE `".$wpdb->prefix."currency_list`.`isocode` IN('".get_option('base_country')."') AND `".$wpdb->prefix."currency_list`.`id` = `".$wpdb->prefix."region_tax`.`country_id`") ;
        
        
        if($country_data['has_regions'] == 1)
          {
          echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='?page=wp-shopping-cart/options.php&isocode=".get_option('base_country')."'>".$region_count." Regions</a>";
          }
          else
            {
            echo "<input type='hidden' name='country_id' value='".$country_data['id']."'>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' name='country_tax' class='tax_forms' maxlength='5' size='5' value='".$country_data['tax']."'>%";
            }
        ?>
        </span>
        </td>
      </tr>
      <tr>
        <td colspan='2'><br />
        <strong class="form_group"><?php echo TXT_WPSC_URLSETTINGS;?>:</strong>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_PRODUCTLISTURL;?>:
        </td>
        <td>
        <input class='text' type='text' size='40' value='<?php echo get_option('product_list_url'); ?>' name='product_list_url' />
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_SHOPPINGCARTURL;?>:
        </td>
        <td>
        <input class='text' type='text' size='40' value='<?php echo get_option('shopping_cart_url'); ?>' name='shopping_cart_url' />
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_CHECKOUTURL;?>:
        </td>
        <td>
        <input class='text' type='text' size='40' value='<?php echo get_option('checkout_url'); ?>' name='checkout_url' />
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_TRANSACTIONDETAILSURL;?>:
        </td>
        <td>
        <input class='text' type='text' size='40' value='<?php echo get_option('transact_url'); ?>' name='transact_url' />
        </td>
      </tr>
  
  
      <tr>
        <td colspan='2'><br />
        <strong class="form_group"><?php echo TXT_WPSC_PRESENTATIONSETTINGS;?>:</strong>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_LANGUAGE;?>:
        </td>
        <td>
        <select name='language_setting'>
        <?php
        if(get_option('language_setting') != '')
          {
          $language_setting =get_option('language_setting');
          }
          else
            {
            $language_setting = "EN_en.php";
            }
        $languages_directory = ABSPATH . 'wp-content/plugins/wp-shopping-cart/languages';
        $language_files = nzshpcrt_listdir($languages_directory);
        //echo "<pre>".print_r($language_files,true)."</pre>";
        foreach($language_files as $language_file)
          {
          switch($language_file)
            {
            case "EN_en.php";
            $language = "English";
            break;
            
            case "DE_de.php";
            $language = "Deutsch";
            break;
            
            case "FR_fr.php";
            $language = "Français";
            break;
            
            case "IT_it.php";
            $language = "Italian";
            break;
            
            case "JP_jp.php";
            $language = "日本語";
            break;
            
            case "pt_BR.php";
            $language = "Brazilian Portuguese";
            break;
                      
            case "RU_ru.php";
            $language = "Russian";
            break;
            
            case "SP_sp.php";
            $language = "Spanish";
            break;
            
            case "SV_sv.php";
            $language = "Swedish";
            break;
                    
            case "TR_tr.php";
            $language = "Türkçe";
            break; 
  
            case "EL_el.php";
            $language = "Ελληνικά";
            break;
            
            default:
            continue 2;
            break;
            }
          if($language_setting == $language_file)
            {
            echo "<option selected='true' value='".$language_file."'>".$language."</option>";
            }
            else
              {
              echo "<option value='".$language_file."'>".$language."</option>";            
              }
          }
        ?>
        </select>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_CARTLOCATION;?>:
        </td>
        <td>
        <?php
  $cart_location = get_option('cart_location');
  $cart1 = "";
  $cart2 = "";
  switch($cart_location)
    {
    case 1:
    $cart1 = "checked ='true'";
    break;
    
    case 2:
    $cart2 = "checked ='true'";
    break;
    
    case 3:
    $cart3 = "checked ='true'";
    break;
    
    case 4:
    $cart4 = "checked ='true'";
    break;
    
    case 5:
    $cart5 = "checked ='true'";
    break;
    }
        ?>
        <input type='radio' value='1' name='cart_location' id='cart1' <?php echo $cart1; ?> /> <label for='cart1'><?php echo TXT_WPSC_SIDEBAR;?></label> &nbsp;
        <input type='radio' value='2' name='cart_location' id='cart2' <?php echo $cart2; ?> /> <label for='cart2'><?php echo TXT_WPSC_PAGE;?></label> &nbsp;
        <?php
        if(function_exists('register_sidebar_widget'))
          {
          ?>
          <input type='radio' value='4' name='cart_location' id='cart4' <?php echo $cart4; ?> /> <label for='cart4'><?php echo TXT_WPSC_WIDGET;?></label> &nbsp;
          <?php
          }
          else
            {
            ?>
            <input type='radio' disabled='true' value='4' name='cart_location' id='cart4' alt='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>' title='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>' <?php echo $cart4; ?> /> <label style='color: #666666;' for='cart4' title='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>'><?php echo TXT_WPSC_WIDGET;?></label> &nbsp;
            <?php
            }
          ?>  <?php
        if(function_exists('drag_and_drop_cart'))
          {
          ?>
          <input type='radio' value='5' name='cart_location' id='cart5' <?php echo $cart5; ?> /> <label for='cart5'><?php echo TXT_WPSC_GOLD_DROPSHOP;?></label> &nbsp;
          <?php
          }
          else
            {
            ?>
            <input type='radio' disabled='true' value='5' name='cart_location' id='cart5' alt='<?php echo TXT_WPSC_NEEDTOENABLEWIDGET;?>' title='<?php echo TXT_WPSC_NEEDTOENABLEDROPSHOP;?>' <?php echo $cart5; ?> /> <label style='color: #666666;' for='cart5' title='<?php echo TXT_WPSC_NEEDTOENABLEDROPSHOP;?>'><?php echo TXT_WPSC_GOLD_DROPSHOP;?></label> &nbsp;
            <?php
            }
          ?>
        <input type='radio' value='3' name='cart_location' id='cart3' <?php echo $cart3; ?> /> <label for='cart3'><?php echo TXT_WPSC_MANUAL;?> <span style='font-size: 7pt;'>(PHP code: &lt;?php echo nzshpcrt_shopping_basket(); ?&gt; )</span></label>
        </td>
      </tr>
      <?php /* pe.{ */ ?>
      <tr>
      <td>
        <?php echo TXT_WPSC_CATSBRANDSLOCATION;?>:
      </td>
      <td>
        <!-- This bombs on my machine if I use the value '1'.  It's fine with 0, 2 & 3.  I have absolutely no idea why. -->
        <input type='radio' value='0' name='cat_brand_loc' id='cat_brand0' <?php if (get_option('cat_brand_loc') == 0) { echo "checked='true'"; } ?> /><label for='cat_brand0'>Page</label>&nbsp;
        <input type='radio' value='2' name='cat_brand_loc' id='cat_brand2' <?php if (get_option('cat_brand_loc') == 2) { echo "checked='true'"; } ?> /><label for='cat_brand2'>Manual in shop</label>&nbsp;
        <input type='radio' value='3' name='cat_brand_loc' id='cat_brand3' <?php if (get_option('cat_brand_loc') == 3) { echo "checked='true'"; } ?> /><label for='cat_brand3'>Manual on every page
        <br /><span style='font-size: 7pt;'>(Manual options PHP code: &lt;?php show_cats_brands(); ?&gt; )</span></label>
      </td>
      </tr>
      <?php /* }.pe */ ?>
      <tr>
        <td>
        <?php echo TXT_WPSC_SHOWCATEGORIESBRANDS;?>:
        </td>
        <td>
        <?php
  $show_categorybrands = get_option('show_categorybrands');
  $cart1 = "";
  $cart2 = "";
  $cart3 = "";
  switch($show_categorybrands)
    {
    case 1:
    $cart1 = "checked ='true'";
    break;
  
    case 2:
    $cart2 = "checked ='true'";
    break;
  
    case 3:
    $cart3 = "checked ='true'";
    break;
    }
        ?>
        <input type='radio' value='2' name='show_categorybrands' id='categorybrands2' <?php echo $cart2; ?> /> <label for='categorybrands2'><?php echo TXT_WPSC_CATEGORIES;?></label>&nbsp;
        <input type='radio' value='3' name='show_categorybrands' id='categorybrands3' <?php echo $cart3; ?> /> <label for='categorybrands3'><?php echo TXT_WPSC_BRANDS;?></label>&nbsp;
        <input type='radio' value='1' name='show_categorybrands' id='categorybrands1' <?php echo $cart1; ?> /> <label for='categorybrands1'><?php echo TXT_WPSC_BOTH;?></label>&nbsp;
      </tr>
      
      <?php
    if(function_exists('product_display_list') || function_exists('product_display_grid'))
      {
      ?>    
      <tr>
        <td>
        <?php echo TXT_WPSC_PRODUCT_DISPLAY;?>:
        </td>
        <td>
        <?php
  $display_pnp = get_option('product_view');
  $product_view1 = "";
  $product_view2 = "";
  switch($display_pnp)
    {
    case "grid":
    if(function_exists('product_display_grid'))
      {
      $product_view3 = "selected ='true'";
      $list_view_quantity_style = "style='display: none;'";
      break;
      }
    
    case "list":
    if(function_exists('product_display_list'))
      {
      $product_view2 = "selected ='true'";
      $list_view_quantity_style = "style='display: block;'";
      break;
      }
    
    default:
    $product_view1 = "selected ='true'";
    $list_view_quantity_style = "style='display: none;'";
    break;
    }
  
  if(get_option('list_view_quantity') == 1)
    {
    $list_view_quantity_value = "checked='true'";
    }
    else
      {
      $list_view_quantity_value = 0;
      }
        ?>
        <select name='product_view' onchange="hideelement('list_view_quantity_container')">
        <option value='default' <?php echo $product_view1; ?>><?php echo TXT_WPSC_DEFAULT;?></option>
        <?php
        if(function_exists('product_display_list'))
          {
          ?>
        <option value='list' <?php echo $product_view2; ?>><?php echo TXT_WPSC_LIST;?></option>
          <?php      
          }
        
        if(function_exists('product_display_grid'))
          {
          ?>
        <option value='grid' <?php echo $product_view3; ?>><?php echo TXT_WPSC_GRID;?></option>
          <?php   
          }
        ?>
        </select>
          <div id='list_view_quantity_container' <?php echo $list_view_quantity_style;?>>
            <input type='checkbox' value='1' name='list_view_quantity' id='list_view_quantity' <?php echo $list_view_quantity_value;?> />
            <label for='list_view_quantity'><?php echo TXT_WPSC_ADJUSTABLE_QUANTITY;?></label>
          </div>
        </td>
      </tr>
      <?php
      }
      ?>    
      <tr>
        <td>
        <?php echo TXT_WPSC_DEFAULTCATEGORY;?>:
        </td>
        <td>
        <?php echo options_categorylist(); ?>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_DEFAULTBRAND;?>:
        </td>
        <td>
        <?php echo brandslist(); ?>
        </td>
      </tr>
      <?php
  if(function_exists("getimagesize"))
    {
  ?>
      <tr>
        <td>
        <?php echo TXT_WPSC_PRODUCTTHUMBNAILSIZE;?>:
        </td>
        <td>
        <?php echo TXT_WPSC_HEIGHT;?>:<input type='text' size='6' name='product_image_height' value='<?php echo get_option('product_image_height'); ?>' /> <?php echo TXT_WPSC_WIDTH;?>:<input type='text' size='6' name='product_image_width' value='<?php echo get_option('product_image_width'); ?>' /> <span class='small'></span>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_CATEGORYTHUMBNAILSIZE;?>:
        </td>
        <td>
        <?php echo TXT_WPSC_HEIGHT;?>:<input type='text' size='6' name='category_image_height' value='<?php echo get_option('category_image_height'); ?>' /> <?php echo TXT_WPSC_WIDTH;?>:<input type='text' size='6' name='category_image_width' value='<?php echo get_option('category_image_width'); ?>' /> <span class='small'></span>
        </td>
      </tr>
  
  <?php
    }
  ?>
      <tr>
        <td>
        <?php echo TXT_WPSC_SHOWTHUMBNAILS;?>:
        </td>
        <td>
        <?php
  $display_pnp = get_option('show_thumbnails');
  $show_thumbnails1 = "";
  $show_thumbnails2 = "";
  switch($display_pnp)
    {
    case 0:
    $show_thumbnails2 = "checked ='true'";
    break;
    
    case 1:
    $show_thumbnails1 = "checked ='true'";
    break;
    }
  
        ?>
        <input type='radio' value='1' name='show_thumbnails' id='show_thumbnails1' <?php echo $show_thumbnails1; ?> /> <label for='show_thumbnails1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
        <input type='radio' value='0' name='show_thumbnails' id='show_thumbnails2' <?php echo $show_thumbnails2; ?> /> <label for='show_thumbnails2'><?php echo TXT_WPSC_NO;?></label>
        </td>
      </tr>
      
    <tr>
        <td>
        <?php echo TXT_WPSC_SHOWPOSTAGEANDPACKAGING;?>:
        </td>
        <td>
        <?php
  $display_pnp = get_option('display_pnp');
  $display_pnp1 = "";
  $display_pnp2 = "";
  switch($display_pnp)
    {
    case 0:
    $display_pnp2 = "checked ='true'";
    break;
    
    case 1:
    $display_pnp1 = "checked ='true'";
    break;
    }
  
        ?>
        <input type='radio' value='1' name='display_pnp' id='display_pnp1' <?php echo $display_pnp1; ?> /> <label for='display_pnp1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
        <input type='radio' value='0' name='display_pnp' id='display_pnp2' <?php echo $display_pnp2; ?> /> <label for='display_pnp2'><?php echo TXT_WPSC_NO;?></label>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_SHOWSPECIALS;?>:
        </td>
        <td>
        <?php
  $display_pnp = get_option('display_specials');
  $display_specials1 = "";
  $display_specials2 = "";
  switch($display_pnp)
    {
    case 0:
    $display_specials2 = "checked ='true'";
    break;
    
    case 1:
    $display_specials1 = "checked ='true'";
    break;
    }
  
        ?>
        <input type='radio' value='1' name='display_specials' id='display_specials1' <?php echo $display_specials1; ?> /> <label for='display_specials1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
        <input type='radio' value='0' name='display_specials' id='display_specials2' <?php echo $display_specials2; ?> /> <label for='display_specials2'><?php echo TXT_WPSC_NO;?></label>
        </td>
      </tr>
      
      <tr>
        <td>
        <?php echo TXT_WPSC_SHOWPRODUCTRATINGS;?>:
        </td>
        <td>
        <?php
  $display_pnp = get_option('product_ratings');
  $product_ratings1 = "";
  $product_ratings2 = "";
  switch($display_pnp)
    {
    case 0:
    $product_ratings2 = "checked ='true'";
    break;
    
    case 1:
    $product_ratings1 = "checked ='true'";
    break;
    }
  
        ?>
        <input type='radio' value='1' name='product_ratings' id='product_ratings1' <?php echo $product_ratings1; ?> /> <label for='product_ratings1'><?php echo TXT_WPSC_YES;?></label> &nbsp;
        <input type='radio' value='0' name='product_ratings' id='product_ratings2' <?php echo $product_ratings2; ?> /> <label for='product_ratings2'><?php echo TXT_WPSC_NO;?></label>
        </td>
      </tr>
      <tr>
        <td colspan='2'><br />
        <strong class="form_group"><?php echo TXT_WPSC_CURRENCYSETTINGS;?>:</strong>
        </td>
      </tr>
      <?php
      /*
      <tr>
        <td>
        <?php echo TXT_WPSC_GSTTAXRATE;?>:
        </td>
        <td>
        <input type='text' size='10' value='<?php echo 100*(get_option('gst_rate') - 1); ?>' name='gst_rate' />
        </td>
      </tr>
      */
      ?>
      <tr>
        <td>
        <?php echo TXT_WPSC_CURRENCYTYPE;?>:
        </td>
        <td>
        <select name='currency_type' onChange='getcurrency(this.options[this.selectedIndex].value);'>
        <?php
        
        $currency_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."currency_list` ORDER BY `country` ASC",ARRAY_A);
        foreach($currency_data as $currency)
          {
          if(get_option('currency_type') == $currency['id'])
            {
            $selected = "selected='true'";
            }
            else
              {
              $selected = "";
              }
          echo "        <option value='".$currency['id']."' ".$selected." >".$currency['country']." (".$currency['currency'].")</option>";
          }
        
      $currency_data = $wpdb->get_row("SELECT `symbol`,`symbol_html`,`code` FROM `".$wpdb->prefix."currency_list` WHERE `id`='".get_option('currency_type')."' LIMIT 1",ARRAY_A) ;
      if($currency_data['symbol'] != '')
        {
        $currency_sign = $currency_data['symbol_html'];
        }
        else
          {
          $currency_sign = $currency_data['code'];
          }
        ?>
        </select>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_CURRENCYSIGNLOCATION;?>:
        </td>
        <td>
        <?php
  $currency_sign_location = get_option('currency_sign_location');
  $csl1 = "";
  $csl2 = "";
  $csl3 = "";
  $csl4 = "";
  switch($currency_sign_location)
    {
    case 1:
    $csl1 = "checked ='true'";
    break;
  
    case 2:
    $csl2 = "checked ='true'";
    break;
  
    case 3:
    $csl3 = "checked ='true'";
    break;
  
    case 4:
    $csl4 = "checked ='true'";
    break;
    }
        ?>
        <input type='radio' value='1' name='currency_sign_location' id='csl1' <?php echo $csl1; ?> /> <span for='csl1'>100<span id=cslchar1><?php echo $currency_sign; ?></span></label> &nbsp;
        <input type='radio' value='2' name='currency_sign_location' id='csl2' <?php echo $csl2; ?> /> <label for='csl2'>100 <span id=cslchar2><?php echo $currency_sign; ?></span></label> &nbsp;
        <input type='radio' value='3' name='currency_sign_location' id='csl3' <?php echo $csl3; ?> /> <label for='csl3'><span id=cslchar3><?php echo $currency_sign; ?></span>100</label> &nbsp;
        <input type='radio' value='4' name='currency_sign_location' id='csl4' <?php echo $csl4; ?> /> <label for='csl4'><span id=cslchar4><?php echo $currency_sign; ?></span> 100</label>
        </td>
      </tr>
      <?php
      /*
      <tr>
        <td>
        <?php echo TXT_WPSC_DEFAULTPOSTAGEPACKAGING;?>:
        </td>
        <td>
        <input type='text' size='10' value='<?php echo get_option('postage_and_packaging'); ?>' name='postage_and_packaging' />
        </td>
      </tr>
      */
      ?>
  
  
  
    <tr>
        <td colspan='2'><br />
        <strong class="form_group"><?php echo TXT_WPSC_SHIPPINGSETTINGS;?>:</strong>
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_BASE_LOCAL;?>:
        </td>
        <td>
        <input type='text' size='10' value='<?php echo number_format(get_option('base_local_shipping'), 2); ?>' name='base_local_shipping' />
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_BASE_INTERNATIONAL;?>:
        </td>
        <td>
        <input type='text' size='10' value='<?php echo number_format(get_option('base_international_shipping'), 2); ?>' name='base_international_shipping' />
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
        <?php echo TXT_WPSC_SHIPPING_NOTE;?>
        </td>
      </tr>
        
      
        
  
      <tr>
        <td colspan='2'><br />
        <strong class="form_group"><?php echo TXT_WPSC_ADMINISTRATIONSETTINGS;?>:</strong>
        </td>
      </tr>
      
      
      <tr>
        <td>
        <?php echo TXT_WPSC_MAXDOWNLOADSPERFILE;?>:
        </td>
        <td>
        <input type='text' size='10' value='<?php echo get_option('max_downloads'); ?>' name='max_downloads' />
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_PURCHASELOGEMAIL;?>:
        </td>
        <td>
        <input class='text' type='text' size='40' value='<?php echo get_option('purch_log_email'); ?>' name='purch_log_email' />
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_REPLYEMAIL;?>:
        </td>
        <td>
        <input class='text' type='text' size='40' value='<?php echo get_option('return_email'); ?>' name='return_email' />
        </td>
      </tr>
      <tr>
        <td>
        <?php echo TXT_WPSC_TERMS2;?>:
        </td>
        <td>
        <textarea name='terms_and_conditions' size='40'><?php echo stripslashes(get_option('terms_and_conditions')); ?></textarea>
        </td>
      </tr>
  
      <tr>
        <td>
        </td>
        <td>
        <input type='submit' value='<?php echo TXT_WPSC_SUBMIT;?>' name='form_submit' />
        </td>
      </tr>
    </table>
    </form>
  </div>
  <?php
  }
?>