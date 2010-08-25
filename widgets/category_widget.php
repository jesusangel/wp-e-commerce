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
		$show_thumbnails = $instance['image'];
		if (isset($instance['grid']))
			$grid = (bool)$instance['grid'];
		if (isset($instance['width']))
			$width = $instance['width'];
		if (isset($instance['height']))
			$height = $instance['height'];
		if (!isset($instance['categories'])) $instance['categories'] = array();
		foreach(array_keys((array)$instance['categories']) as $category_id) {
		if(file_exists(wpsc_get_theme_file_path("wpsc-category_widget.php"))) {
				include(wpsc_get_theme_file_path("wpsc-category_widget.php"));
			} else {
				include(wpsc_get_theme_file_path("category_widget.php"));
			}
		}
		if(isset($grid) && $grid){
			echo "<div class='clear_category_group'></div>";
		}
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['image'] = $new_instance['image'] ? 1 : 0;
		$instance['categories'] = $new_instance['categories'];
		$instance['grid'] = $new_instance['grid'] ? 1 : 0;
		$instance['height'] = (int)$new_instance['height'];
		$instance['width'] = (int)$new_instance['width'];
		return $instance;
	}

	function form( $instance ) {
	  global $wpdb;
		//Defaults
		$instance = wp_parse_args((array) $instance, array( 'title' => ''));
		$title = esc_attr( $instance['title'] );
		$image = (bool) $instance['image'];
		$width = (int) $instance['width'];
		$height = (int) $instance['height'];
		$grid = (bool) $instance['grid'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<b>Include the following categories:</b><br/>
		<?php wpsc_list_categories('wpsc_category_widget_admin_category_list', array("id"=>$this->get_field_id('categories'),"name"=>$this->get_field_name('categories'),"instance"=>$instance), 0); ?>
		</p>
		<p>
			<b>Presentation:</b><br />
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>"<?php checked( $image ); ?> />
			<label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Display thumbnails', 'wpsc'); ?></label><br />
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('grid'); ?>" name="<?php echo $this->get_field_name('grid'); ?>"<?php checked( $grid ); ?> />
			<label for="<?php echo $this->get_field_id('grid'); ?>"><?php _e('Display Grid', 'wpsc'); ?></label><br />
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width', 'wpsc'); ?></label><br />
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo $width ; ?>" />
			<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height', 'wpsc'); ?></label><br />
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo $height ; ?>" />


		</p>
<?php
	}

}

function wpsc_category_widget_admin_category_list($category, $level, $fieldconfig) {

	// Only let the user choose top-level categories
	if ($level)
		return;

	if (!empty($fieldconfig['instance']['categories']) && array_key_exists($category->term_id, $fieldconfig['instance']['categories'])) {
		$checked = "checked";
	} else {
		$checked = "";
	}
	?>
	<input type="checkbox" class="checkbox" id="<?php echo $fieldconfig['id']; ?>-<?php echo $category->term_id; ?>" name="<?php echo $fieldconfig['name']; ?>[<?php echo $category->term_id; ?>]" <?php echo $checked; ?>><?php echo htmlentities($category->name); ?></input><br/>
	<?php 
}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Product_Categories");'));
?>
