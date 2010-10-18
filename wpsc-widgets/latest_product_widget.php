<?php
/**
 * Latest Product widget class
 *
 * Takes the settings, works out if there is anything to display, if so, displays it.
 *
 * @since 3.8
 */
class WP_Widget_Latest_Products extends WP_Widget {

	/**
	 * Widget Constuctor
	 */
	function WP_Widget_Latest_Products() {
		$widget_ops = array( 'classname' => 'widget_wpsc_latest_products','description' => __( 'Latest Products Widget', 'wpsc' ) );
		$this->WP_Widget( 'wpsc_latest_products', __( 'Latest Products', 'wpsc' ), $widget_ops );
	}

	/**
	 * Widget Output
	 *
	 * @param $args (array)
	 * @param $instance (array) Widget values.
	 */
	function widget( $args, $instance ) {

		global $wpdb, $table_prefix;

		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Latest Products' ) : $instance['title'] );

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;
		wpsc_latest_product( $args, $instance );
		echo $after_widget;
	}

	/**
	 * Update Widget
	 *
	 * @param $new_instance (array) New widget values.
	 * @param $old_instance (array) Old widget values.
	 *
	 * @return (array) New values.
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['number'] = (int)$new_instance['number'];
		$instance['show_thumbnails'] = (bool)$new_instance['show_thumbnails'];

		return $instance;

	}

	/**
	 * Widget Options Form
	 *
	 * @param $instance (array) Widget values.
	 */
	function form( $instance ) {

		global $wpdb;

		// Defaults
		$instance = wp_parse_args( (array)$instance, array( 'title' => '', 'number' => 5 ) );

		// Values
		$title  = esc_attr( $instance['title'] );
		$number = (int)$instance['number'];
		$show_thumbnails = (bool)$instance['show_thumbnails']; ?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of products to show:', 'wpsc' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $number; ?>" size="3" />
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'show_thumbnails' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnails' ); ?>" <?php echo $show_thumbnails ? 'checked="checked"' : ""; ?>>
			<label for="<?php echo $this->get_field_id( 'show_thumbnails' ); ?>"><?php _e( 'Show Thumbnails', 'wpsc' ); ?></label>
		</p>
<?php
	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("WP_Widget_Latest_Products");' ) );

function wpsc_latest_product() {
	global $wpdb;
	$args = wp_parse_args( (array)$args, array( 'number' => 5 ) );
	$siteurl = get_option( 'siteurl' );
	$options = get_option( 'wpsc-widget_latest_products' );
	$number  = isset($instance['number']) ? (int)$instance['number'] : 5;
	$show_thumbnails  = isset($instance['show_thumbnails']) ? (bool)$instance['show_thumbnails'] : FALSE;
	$latest_products = get_posts( array(
		'post_type'   => 'wpsc-product',
		'numberposts' => $number, 
		'orderby'     => 'post_date',
		'post_parent' => 0,
		'post_status' => 'all',
		'order'       => 'DESC'
	) );
	$output = '';

	if ( count( $latest_products ) > 0 ) {
		$output .= '<ul class="wpec-latest-products">';		
		foreach ( $latest_products as $latest_product ) {
			$output .= '<li class="wpec-latest-product">';
			// Thumbnails, if required
			if (!$show_thumbnails) {
				$output .= '<div class="item_image">';
				$output .= '<a href="' . wpsc_product_url( $latest_product->ID, null ) . '">';
				$attached_images = (array)get_posts( array(
					'post_type'   => 'attachment',
					'numberposts' => 1,
					'post_status' => null,
					'post_parent' => $latest_product->ID,
					'orderby'     => 'menu_order',
					'order'       => 'ASC'
				) );
				$attached_image = $attached_images[0]; 
				if ( $attached_image->ID > 0 ) {
					if ( get_option( 'wpsc_selected_theme' ) == 'marketplace' ) {
						$src = WPSC_IMAGE_URL . $special['image'];
						$output .= '<img src="' . wpsc_product_image( $attached_image->ID, 100, 75 ) . '" title="' . $latest_product->post_title . '" alt="' . $latest_product->post_title . '" />';
					} else {
						$output .= '<img src="' . wpsc_product_image( $attached_image->ID, get_option('product_image_width'), get_option('product_image_height') ) . '" title="' . $latest_product->post_title . '" alt="' . $latest_product->post_title . '" />';
					}
				}
				$output .= '</a>';
				$output .= '</div>';
			}
			// Link
			$output .= '<a href="' . wpsc_product_url( $latest_product->ID, null ) . '" class="wpec-product-title">'.stripslashes( $latest_product->post_title ).'</a>';
			$output .= '</li>';
		}
		$output .= "</ul>";
	}
	echo $output;
	}
?>
