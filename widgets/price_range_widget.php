<?php



/**
 * Admin Menu widget class
 *
 * @since 3.8
 */
class WP_Widget_Price_Range extends WP_Widget {
	
	/**
	 * Widget Constuctor
	 */
	function WP_Widget_Price_Range() {

		$widget_ops = array(
			'classname'   => 'widget_wpsc_price_range',
			'description' => __( 'Price Range Widget', 'wpsc' )
		);
		
		$this->WP_Widget( 'wpsc_price_range', __( 'Price Range', 'wpsc' ), $widget_ops );
	
	}

	/**
	 * Widget Output
	 *
	 * @param $args (array)
	 * @param $instance (array) Widget values.
	 *
	 * @todo Add individual capability checks for each menu item rather than just manage_options.
	 */
	function widget( $args, $instance ) {
		
		global $wpdb, $table_prefix;
		
		extract( $args );
	
		echo $before_widget;
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Price Range' ) : $instance['title'] );
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		nzshpcrt_price_range();
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
		$instance = wp_parse_args( (array)$instance, array( 'title' => '' ) );
		
		// Values
		$title  = esc_attr( $instance['title'] );
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php
		
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("WP_Widget_Price_Range");' ) );

/**
 * Price Range Widget content function
 *
 * Displays a list of price ranges.
 *
 * @param $args (array) Arguments.
 */
function nzshpcrt_price_range( $args = null ) {

	global $wpdb;
	
	// Filter args not used at the moment, but this is here ready
	$args = wp_parse_args( (array)$args, array() );
	
	$siteurl = get_option( 'siteurl' );
	$product_page = get_option( 'product_list_url' );
	$result = $wpdb->get_results( "SELECT DISTINCT CAST(`meta_value` AS DECIMAL) AS `price` FROM " . $wpdb->postmeta . " AS `m` WHERE `meta_key` IN ('_wpsc_price') ORDER BY `price` ASC", ARRAY_A );
	
	if ( $result != null ) {
		sort( $result );
		$count = count( $result );
		$price_seperater = ceil( $count / 6 );
		for ( $i = 0; $i < $count; $i += $price_seperater ) {
			$ranges[] = round( $result[$i]['price'], -1 );
		}
		$ranges = array_unique( $ranges );
		
		$final_count = count( $ranges );
		$ranges = array_merge( array(), $ranges );
		$_SESSION['price_range'] = $ranges;
		
		for ( $i = 0; $i < $final_count; $i++ ) {
			$j = $i;
			if ( $i == $final_count - 1 ) {
				echo "<a href='" . add_query_arg( 'range', $j, $product_page ) . "'>Over " . $ranges[$i] . "</a><br/>";
			} else if ( $ranges[$i] == 0 ) {
				echo "<a href='" . add_query_arg( 'range', $j, $product_page ) . "'>Under " . $ranges[$i + 1] . "</a><br/>";
			} else {
				echo "<a href='" . add_query_arg( 'range', $j, $product_page ) . "'>" . $ranges[$i] . " - " . $ranges[$i + 1] . "</a><br/>";
			}
		}
		echo "<a href='" . add_query_arg( 'range', 'all', get_option( 'product_list_url' ) ) . "'>" . __( 'Show All', 'wpsc' ) . "</a><br/>";
	}
	
}



?>