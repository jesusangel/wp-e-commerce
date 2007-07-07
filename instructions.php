<?php
global $wpdb;
?>
<div class="wrap">
  <?php
  if($_GET['debug'] == 'true')
    {
    /*
    $categories = $wpdb->get_results("SELECT * FROM `wp_product_categories`",ARRAY_A);
    foreach($categories as $category)
      {
      $sql = "SELECT `".$wpdb->prefix."product_list`.`id`, `".$wpdb->prefix."product_order`.`order`, IF(ISNULL(`".$wpdb->prefix."product_order`.`order`), 0, 1) AS `order_state` FROM `".$wpdb->prefix."product_list` 
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
'".$category['id']."'
)
ORDER BY `order_state` DESC,`".$wpdb->prefix."product_order`.`order` ASC";

       $products = $wpdb->get_results($sql,ARRAY_A);
       $wpdb->query("DELETE FROM `".$wpdb->prefix."product_order` WHERE `category_id` = '".$category['id']."'");
       $num = 0;
       foreach((array)$products as $product)
         {
         $num++;
         $wpdb->query("INSERT INTO `".$wpdb->prefix."product_order` ( `id` , `category_id` , `product_id` , `order` ) VALUES ('', '".$category['id']."', '".$product['id']."', '$num');");
          }
      }
      
    */
    phpinfo();
    }
    else
      {
      ?>
      <h2><?php echo TXT_WPSC_HELPINSTALLATION;?></h2>
      <p>
        <?php echo TXT_WPSC_INSTRUCTIONS;?>
      </p>
      <?php
      }
  ?>
</div>