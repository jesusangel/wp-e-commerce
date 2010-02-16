<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>

	<div id="content" class="narrowcolumn wpsc-products" role="main">

	<?php if (have_posts()) : ?>
		
		<?php
				?>

		<?php while (have_posts()) : the_post(); ?>
			<?php					
			$attached_images = (array)get_posts(array(
				'post_type' => 'attachment',
				'numberposts' => -1,
				'post_status' => null,
				'post_parent' => get_the_ID(),
				'orderby' => 'menu_order',
				'order' => 'ASC'
			));
			
			
			if($attached_images != null) {
				$primary_image = $attached_images[0];
				unset($attached_images[0]);
			}
			
			
			
			
			?>

			<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
				<?php
				if(isset($primary_image)) {
					$image_meta = get_post_meta($primary_image->ID, '');
					foreach($image_meta as $meta_name => $meta_value) {
						$image_meta[$meta_name] = maybe_unserialize(array_pop($meta_value));
					}
					//$image_url = "index.php?wpsc_action=scale_image&amp;attachment_id={$primary_image->ID}&amp;width=120&amp;height=120";
					//echo wpsc_product_image($primary_image->ID, 120, 120);
					?>
					<img class='product-image' src='<?php echo wpsc_product_image($primary_image->ID, 120, 120); ?>' alt='<?php echo __('Preview', 'wpsc'); ?>' title='<?php echo __('Preview', 'wpsc'); ?>' />
					<?php
				}
				?>
				<div class='product-text'>
					<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					<?php /*<small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small> */?>
	
					<div class="entry">
						<?php the_content('Read the rest of this entry &raquo;'); ?>
					
					
					<span class="product-price"><?php echo __('Price', 'wpsc'); ?>: <?php echo wpsc_the_product_price(); ?></span>
					</div>
					
					
					<form id="product_<?php the_ID(); ?>" name="product_<?php the_ID(); ?>" method="post" action="https://sandbox.boiling-pukeko.geek.nz/products-page/" enctype="multipart/form-data" class="product_form">
						<input type="hidden" name="wpsc_ajax_action" value="add_to_cart">
						<input type="hidden" name="product_id" value="<?php the_ID(); ?>">
						<input type="submit" id="product_<?php the_ID(); ?>_submit_button" class="wpsc_buy_button" name="Buy" value="Add To Cart">
					</form>
					
					
					<p class="product-metadata"><?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
				</div>
				
				
			</div>

		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

	<?php else : ?>
		<?php
		//	echo "<pre>".print_r($wp_query, true)."</pre>";
		?>
		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>
		<?php get_search_form(); ?>

	<?php endif; ?>

	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
