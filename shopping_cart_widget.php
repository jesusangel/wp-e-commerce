<?php
function widget_wp_shopping_cart($args) {
        extract($args);
         $options = get_option('widget_wp_shopping_cart');
         $title = empty($options['title']) ? __('Shopping Cart') : $options['title'];
         echo $before_widget;
         $full_title = $before_title . $title . $after_title;
         nzshpcrt_shopping_basket($full_title);
         echo $after_widget;
}

function widget_wp_shopping_cart_control() {
        $options = $newoptions = get_option('widget_wp_shopping_cart');
        if ( $_POST["wp_shopping_cart-submit"] ) {
                $newoptions['title'] = strip_tags(stripslashes($_POST["wp_shopping_cart-title"]));
        }
        if ( $options != $newoptions ) {
                $options = $newoptions;
                update_option('widget_wp_shopping_cart', $options);
        }
        $title = htmlspecialchars($options['title'], ENT_QUOTES);
?>
                        <p><label for="wp_shopping_cart-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="wp_shopping_cart-title" name="wp_shopping_cart-title" type="text" value="<?php echo $title; ?>" /></label></p>
                        <input type="hidden" id="wp_shopping_cart-submit" name="wp_shopping_cart-submit" value="1" />
<?php
}

 function widget_wp_shopping_cart_init()
   {
   if(function_exists('register_sidebar_widget'))
    {
    register_sidebar_widget('Shopping Cart', 'widget_wp_shopping_cart');
    register_widget_control('Shopping Cart', 'widget_wp_shopping_cart_control', 300, 90);
    }
   return;
   }
?>