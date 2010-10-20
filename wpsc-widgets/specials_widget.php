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
		
		echo $before_widget;
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Product Specials' ) : $instance['title'] );
		if ( $title )
			echo $before_title . $title . $after_title;

		wpsc_specials($args, $instance);
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
		$instance['show_description']  = (bool)$new_instance['show_description'];

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
			'show_description' => false,
			'show_thumbnails' => false,
			'number' => 5
		) );
		
		// Values
		$title = esc_attr( $instance['title'] );
		$number = (int)$instance['number'];
		$show_thumbnails = (bool)$instance['show_thumbnails'];
		$show_description = (bool)$instance['show_description'];
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of products to show:', 'wpsc' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $number; ?>" size="3" />
		</p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'show_description' ); ?>" name="<?php echo $this->get_field_name( 'show_description' ); ?>" <?php echo $show_description ? 'checked="checked"' : ""; ?>>
			<label for="<?php echo $this->get_field_id( 'show_description' ); ?>"><?php _e( 'Show Description', 'wpsc' ); ?></label><br />
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'show_thumbnails' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnails' ); ?>" <?php echo $show_thumbnails ? 'checked="checked"' : ""; ?>>
			<label for="<?php echo $this->get_field_id( 'show_thumbnails' ); ?>"><?php _e( 'Show Thumbnails', 'wpsc' ); ?></label>
		</p>			
<?php
	}

}

add_action( 'widgets_init', create_function( '', 'return register_widget("WP_Widget_Product_Specials");' ) );



/**
 * Product Specials Widget content function
 *
 * Displays the latest products.
 *
 * @todo Remove marketplace theme specific code and maybe replce with a filter for the image output? (not required if themeable as above)
 *
 * Changes made in 3.8 that may affect users:
 *
 * 1. The product title link text does now not have a bold tag, it should be styled via css.
 * 2. <br /> tags have been ommitted. Padding and margins should be applied via css.
 * 3. Each product is enclosed in a <div> with a 'wpec-special-product' class.
 * 4. The product list is enclosed in a <div> with a 'wpec-special-products' class.
 * 5. Function now expect a single paramter with an array of options (used to be a string which prepended the output).
 */
 
function wpsc_specials( $args = null, $instance ) {
	
	global $wpdb;
	
	$args = wp_parse_args( (array)$args, array( 'number' => 5 ) );
	
	$siteurl = get_option( 'siteurl' );
	
	if ( !$number = (int) $instance['number'] )
		$number = 5;
		
	$show_thumbnails  = isset($instance['show_thumbnails']) ? (bool)$instance['show_thumbnails'] : FALSE;
	$show_description  = isset($instance['show_description']) ? (bool)$instance['show_description'] : FALSE;
	
	$excludes = wpsc_specials_excludes();
		
	$special_products = query_posts( array(
		'post_type'   		=> 'wpsc-product',
		'caller_get_posts' 	=> 1,
		'post_status' 		=> 'publish',
		'post_parent'		=> 0,
		'post__not_in' 		=> $excludes,
		'posts_per_page' 	=> $number
	) );
	
	$output = '';
	//exit('<pre>'.print_r($special_products,true).'</pre>');
	if ( count( $special_products ) > 0 ) {
		list( $wp_query, $special_products ) = array( $special_products, $wp_query ); // swap the wpsc_query object
		while ( wpsc_have_products() ) : wpsc_the_product(); 
				 if ( wpsc_the_product_thumbnail() ) : ?>
						<a rel="<?php echo str_replace(array(" ", '"',"'", '&quot;','&#039;'), array("_", "", "", "",''), wpsc_the_product_title()); ?>" href="<?php echo wpsc_the_product_permalink(); ?>">
							<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/>
						</a>
				<?php else: ?>
							<a href="<?php echo wpsc_the_product_permalink(); ?>">
							<img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo WPSC_URL; ?>/wpsc-theme/images/noimage.png" width="<?php echo get_option('product_image_width'); ?>" height="<?php echo get_option('product_image_height'); ?>" />
							</a>
				<?php endif; ?>
				<br />
				<span id="special_product_price_<?php echo $special_product->ID; ?>">
				<!-- price display -->
				<?php if(wpsc_have_variation_groups()):
					while (wpsc_have_variation_groups()) : wpsc_the_variation_group(); ?>
								<?php /** the variation HTML and loop */?>
								<?php $variation_outputs = Array(); ?>
								<?php while (wpsc_have_variations()) : wpsc_the_variation(); ?>
										<?php
										$variation_outputs[] = '';	
										$variation_prices[] = wpsc_the_variation_price(true);
									endwhile;

									// Sort the variations into price order before outputting
									$data[] = $variation_outputs;
									$data[] = $variation_prices;
									array_multisort($data[1],SORT_ASC,SORT_NUMERIC,
											        $data[0],SORT_ASC,SORT_STRING);?>
						<?php endwhile; 

					 echo __('From', 'wpsc').' : '.wpsc_currency_display(  $data[1][0] ); ?>
				<?php else: ?>
				<?php echo wpsc_currency_display( wpsc_calculate_price( $special_product->ID,null,true ) ); ?>				
				<?php endif; ?>
				</span><br />			
				<strong><a class="wpsc_product_title" href="<?php echo wpsc_product_url( $special_product->ID, false ); ?>"><?php echo wpsc_the_product_title(); ?></a></strong><br /> <?php
				
				
				
		endwhile;
		list( $wp_query, $special_products ) = array( $special_products, $wp_query ); // swap the wpsc_query object
		
		
		
		/*

		$output .= '<div class="wpec-special-products">';		
		foreach ( $special_products as $special_product ) {
			$attached_images = (array)get_posts( array(
				'post_type'   => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $special_product->ID,
				'orderby'     => 'menu_order',
				'order'       => 'ASC'
			) );
			
			//Images are handled here

			if(!empty($attached_images)){
				$attached_image = $attached_images[0];
				if ( ( $attached_image->ID > 0 ) && ($show_thumbnails != 1) )
					$output .= '<img src="' . wpsc_product_image( $attached_image->ID, get_option( 'product_image_width' ), get_option( 'product_image_height' ) ) . '" title="' . $special_product->post_title . '" alt="' . $special_product->post_title . '" /><br />';
			
			}
			//Product Title is here
			$special_product->post_title = htmlentities( stripslashes( $special_product->post_title ), ENT_QUOTES, 'UTF-8' );
			$output .= '<strong><a class="wpsc_product_title" href="' . wpsc_product_url( $special_product->ID, false ) . '">' . $special_product->post_title . '</a></strong><br />';
			
			//Description is handled here
			if ( $show_description == 1 )
				$output .= $special_product->post_content . '<br />';
			
			$output .= '<span id="special_product_price_' . $special_product->ID . '">';
			$output .= wpsc_currency_display( wpsc_calculate_price( $special_product->ID,null,true ) );
			$output .= '</span><br />';			
		}
		$output .= '</div>';
	}
*/
	}
///	echo $output;
	
}
function wpsc_specials_excludes(){
	global $wpdb;

	$exclude_products = $wpdb->get_results("SELECT ID FROM ".$wpdb->prefix."posts JOIN ".$wpdb->prefix."postmeta ON (".$wpdb->prefix."posts.ID = ".$wpdb->prefix."postmeta.post_id) WHERE 1=1  AND ".$wpdb->prefix."posts.post_type = 'wpsc-product' AND ".$wpdb->prefix."posts.post_status = 'publish' AND ".$wpdb->prefix."postmeta.meta_key = '_wpsc_special_price' AND ".$wpdb->prefix."postmeta.meta_value = 0 GROUP BY ".$wpdb->prefix."posts.ID ORDER BY ".$wpdb->prefix."posts.post_date DESC LIMIT 0, 10");
	
	foreach($exclude_products as $exclude_product)
		$excludes[] = $exclude_product->ID;

	return $excludes;
}
?>
