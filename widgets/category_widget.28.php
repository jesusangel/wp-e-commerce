<?php
/**
 * Categories widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Product_Categories extends WP_Widget {

	function WP_Widget_Product_Categories() {

		$widget_ops = array('classname' => 'widget_wpsc_categorisation', 'description' => __('Product Grouping Widget', 'wpsc'));
		$this->WP_Widget('wpsc_categorisation', __('Product Categories','wpsc'), $widget_ops);
	}

	function widget( $args, $instance ) {
	  global $wpdb;
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Product Categories' ) : $instance['title']);
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		$selected_categorisations = array_keys((array)$instance['categorisation'], true);	
		if($selected_categorisations != null) {
			foreach($selected_categorisations as $key => $selected_categorisation) {
				$selected_categorisations[$key] = (int)$selected_categorisation;
			}
			$selected_values = implode(',',$selected_categorisations);

			$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `id` IN ({$selected_values}) AND `active` IN ('1')", ARRAY_A);
			foreach($categorisation_groups as $categorisation_group) {
				echo "<div id='categorisation_group_".$categorisation_group['id']."'>\n\r";
				if(count($categorisation_groups) > 1) {  // no title unless multiple category groups
					echo "<h2 class='categorytitle'>{$categorisation_group['name']}</h2>\n\r";
				}
				show_cats_brands($categorisation_group['id'], 'sidebar', 'name', $instance['image']);
				echo "\n\r";
				echo "</div>\n\r";
			}
			//echo("<pre>".print_r($selected_categorisations,true)."</pre>");
		} else {
			show_cats_brands(null, 'sidebar');
		}

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
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
		<?php
		$categorisation_groups =  $wpdb->get_results("SELECT * FROM `".WPSC_TABLE_CATEGORISATION_GROUPS."` WHERE `active` IN ('1')", ARRAY_A);
			foreach($categorisation_groups as $cat_group) {
				$category_state = false;
				$category_count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPSC_TABLE_PRODUCT_CATEGORIES."` WHERE `group_id` IN ('{$cat_group['id']}')");
				$category_state = (bool)$instance['categorisation'][$cat_group['id']];
				?>
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('categorisation')."-{$cat_group['id']}"; ?>" name="<?php echo $this->get_field_name('categorisation')."[{$cat_group['id']}]"; ?>"<?php checked($category_state); ?> />
					<label for="<?php echo $this->get_field_id('categorisation')."-{$cat_group['id']}"; ?>"><?php echo str_replace(":category:",$cat_group['name'],TXT_WPSC_DISPLAY_THE_GROUP); ?></label><br />
				<?
			}
			?>
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