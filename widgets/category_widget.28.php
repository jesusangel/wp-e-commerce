<?php
/**
 * Product Categories widget class
 *
 * @since 3.7.1
 */
class WP_Widget_Product_Categories extends WP_Widget {

	function WP_Widget_Product_Categories() {

		$widget_ops = array('classname' => 'widget_wpsc_categorisation', 'description' => __('Product Grouping Widget', 'wpsc'));
		$this->WP_Widget('wpsc_categorisation', __('Product Categories','wpsc'), $widget_ops);
	}

	function widget( $args, $instance ) {
	  global $wpdb, $wpsc_theme_path;
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Product Categories' ) : $instance['title']);
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		//echo wpsc_get_theme_file_path("category_widget.php");
		include(wpsc_get_theme_file_path("category_widget.php"));


		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['categorisation'] = $new_instance['categorisation'];
		$instance['image'] = $new_instance['image'] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
	  global $wpdb;
		//Defaults
		$instance = wp_parse_args((array) $instance, array( 'title' => ''));
		$title = esc_attr( $instance['title'] );
		$image = (bool) $instance['image'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>"<?php checked( $image ); ?> />
			<label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Display the Group thumbnails in the sidebar', 'wpsc'); ?></label><br />
		</p>
<?php
	}

}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Product_Categories");'));
?>