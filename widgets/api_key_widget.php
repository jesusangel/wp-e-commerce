<?php
/*
 * API Key Widget Class
 */
 
class Wpsc_Api_Key_Widget extends WP_Widget {
    /** constructor */
    function Wpsc_Api_Key_Widget() {
		$widget_ops = array('classname' => 'widget_wpsc_apikey', 'description' => __('Product API Widget', 'wpsc'));
		$this->WP_Widget('wpsc_apikey', __('Product API','wpsc'), $widget_ops);
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        if($_SESSION['api_info'] == ''){
        ?>
        
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . $instance['title']
                      . $after_title; ?>
                  <form action='' method='post'>
                  <p>
                  	<label for='wpsc_transaction_id'><?php __('Please enter your Paypal Transaction Id:') ?></label>
                  	<input type='text' name='wpsc_transaction_id' value='' />	
                  	<input type='hidden'  name='wpsc_ajax_action' value='api_key_finder' />
                  	<input type='submit' class='button' name='submit' value='Submit' />
                  </p>
                  </form>
              <?php echo $after_widget; ?>
        <?php
        }else{ ?>
        	
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . $instance['title']
                      . $after_title; ?>
					<?php __('Thanks', 'wpsc').' '.$_SESSION['api_info']['first_name']; ?>
					<?php __('Your Api Name : ').' '.$_SESSION['api_info']['name']; ?>
					<?php __('Your Api Key : ').' '.$_SESSION['api_info']['key']; ?>

              <?php echo $after_widget; ?>        
        <?php
        }
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php 
    }

} // class FooWidget

add_action('widgets_init', create_function('', 'return register_widget("Wpsc_Api_Key_Widget");'));