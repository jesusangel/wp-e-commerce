<?php



/**
 * Admin Menu widget class
 *
 * @since 3.8
 *
 * @todo Special count does not work when figuring out wether to show widget.
 * @todo Add option to set how many products show?
 */
class WP_Widget_Product_Specials extends WP_Widget {
	
	/**
	 * Widget Constuctor
	 */
	function WP_Widget_Product_Specials() {

		$widget_ops = array(
			'classname'   => 'widget_wpsc_product_specials',
			'description' => __( 'Product Specials Widget', 'wpsc' )
		);
		
		$this->WP_Widget( 'wpsc_product_specials', __( 'Product Specials', 'wpsc' ), $widget_ops );
	
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
		
		// Special count currently does not work - returns total product count
		$special_count = $wpdb->get_var( "SELECT DISTINCT `p`.`ID`
			FROM `" . $wpdb->postmeta . "` AS `m`
			JOIN `" . $wpdb->posts . "` AS `p` ON `m`.`post_id` = `p`.`ID`
			WHERE `m`.`meta_key`
			IN ('_wpsc_special_price')
			AND `m`.`meta_value` >0
			AND `p`.`post_status` = 'publish'
			" );
		
		if ( $special_count > 0 ) {
			echo $before_widget;
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Product Specials' ) : $instance['title'] );
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			nzshpcrt_specials();
			echo $after_widget;
		}
	
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
		$instance['show_description']  = absint( $new_instance['show_description'] );

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
		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'show_description' => false
		) );
		
		// Values
		$title = esc_attr( $instance['title'] );
		$show_description = $instance['show_description'] == 1 ? ' checked="checked"' : '';
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<input<?php echo $show_description; ?> id="<?php echo $this->get_field_id( 'show_description' ); ?>" name="<?php echo $this->get_field_name( 'show_description' ); ?>" type="checkbox" class="checkbox" value="1" />
			<label for="<?php echo $this->get_field_id('show_description'); ?>"><?php _e( 'Show Description' ); ?></label>
		</p>
		<?php
		
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("WP_Widget_Product_Specials");' ) );



/**
 * Specials Widget content function
 * Displays the products
 *
 * @todo make this use wp_query and a theme file
 *
 * Changes made in 3.8 that may affect users:
 *
 * 1. $input does not get prepended to output.
 */
function nzshpcrt_specials( $args = null ) {
	
	global $wpdb;
	
	// Args not used yet but this is ready for when it is
	$args = wp_parse_args( (array)$args, array() );
	
	$image_width = get_option( 'product_image_width' );
	$image_height = get_option( 'product_image_height' );
	$siteurl = get_option( 'siteurl' );
	
	$product = $wpdb->get_results( "SELECT DISTINCT `p` . * , `m`.`meta_value` AS `special_price`
		FROM `" . $wpdb->postmeta . "` AS `m`
		JOIN `" . $wpdb->posts . "` AS `p` ON `m`.`post_id` = `p`.`ID`
		WHERE `m`.`meta_key`
		IN ('_wpsc_special_price')
		AND `m`.`meta_value` >0
		AND `p`.`post_status` = 'publish'
		AND `p`.`post_type` IN ('wpsc-product')
		ORDER BY RAND( )
		LIMIT 1", ARRAY_A );
	
	if ( $product != null ) {
		$output = '<div>';
		foreach ( $product as $special ) {
			
			$attached_images = (array)get_posts( array(
				'post_type'   => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $special['ID'],
				'orderby'     => 'menu_order',
				'order'       => 'ASC'
			) );
			$attached_image = $attached_images[0];
			if ( ( $attached_image->ID > 0 ) ) {
				$output .= '<img src="' . wpsc_product_image( $attached_image->ID, get_option( 'product_image_width' ), get_option( 'product_image_height' ) ) . '" title="' . $product['post_title'] . '" alt="' . $product['post_title'] . '" /><br />';
			}
			
			$special['name'] = htmlentities( stripslashes( $special['name'] ), ENT_QUOTES, 'UTF-8' );
			$output .= '<strong><a class="wpsc_product_title" href="' . wpsc_product_url( $special['id'], false ) . '">' . $special['post_title'] . '</a></strong><br />';
			
			if ( get_option( 'wpsc_special_description' ) != '1' ) {
				$output .= $special['post_content'] . '<br />';
			}
			
			$output .= '<span id="special_product_price_' . $special['ID'] . '"><span class="pricedisplay">';
			$output .= wpsc_calculate_price( $special['ID'] );
			$output .= '</span></span><br />';
			
			$output .= '<form id="specials_' . $special['ID'] . '" method="post" action="" onsubmit="submitform(this, null); return false;">';
			$output .= '<input type="hidden" name="product_id" value="' . $special['ID'] . '" />';
			$output .= '<input type="hidden" name="item" value="' . $special['ID'] . '" />';
			$output .= '<input type="hidden" name="wpsc_ajax_action" value="special_widget" />';
			$output .= '</form>';
			
		}
		$output .= '</div>';
	} else {
		$output = '';
	}
	echo $output;
	
}



?>