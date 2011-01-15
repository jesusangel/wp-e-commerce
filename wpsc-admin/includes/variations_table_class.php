<?php
/**
 * Variations List Table class.
 *
 * @package WPEC
 * @subpackage List_Table
 * @since 3.1.0
 */
class WPEC_Variations_List_Table extends WP_List_Table {

	/**
	 * Whether the items should be displayed hierarchically or linearly
	 *
	 * @since 3.1.0
	 * @var bool
	 * @access protected
	 */
	var $hierarchical_display;

	/**
	 * Holds the number of pending comments for each post
	 *
	 * @since 3.1.0
	 * @var int
	 * @access protected
	 */
	var $comment_pending_count;

	/**
	 * Holds the number of posts for this user
	 *
	 * @since 3.1.0
	 * @var int
	 * @access private
	 */
	var $user_posts_count;

	/**
	 * Holds the number of posts which are sticky.
	 *
	 * @since 3.1.0
	 * @var int
	 * @access private
	 */
	var $sticky_posts_count = 0;

	function WPEC_Variations_List_Table() {
		global $post_type_object, $post_type, $wpdb;

                $post_type = 'wpsc-product';
		
		$post_type_object = get_post_type_object( $post_type );

		if ( !current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			$this->user_posts_count = $wpdb->get_var( $wpdb->prepare( "
				SELECT COUNT( 1 ) FROM $wpdb->posts
				WHERE post_type = %s AND post_status NOT IN ( 'trash', 'auto-draft' )
				AND post_author = %d
			", $post_type, get_current_user_id() ) );

			if ( $this->user_posts_count && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['all_posts'] ) && empty( $_REQUEST['author'] ) && empty( $_REQUEST['show_sticky'] ) )
				$_GET['author'] = get_current_user_id();
		}

		if ( 'wpsc-product' == $post_type && $sticky_posts = get_option( 'sticky_posts' ) ) {
			$sticky_posts = implode( ', ', array_map( 'absint', (array) $sticky_posts ) );
			$this->sticky_posts_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( 1 ) FROM $wpdb->posts WHERE post_type = %s AND ID IN ($sticky_posts)", $post_type ) );
		}

		parent::WP_List_Table( array(
			'plural' => 'products',
		) );
	}

	function check_permissions() {
		global $post_type_object;

		if ( !current_user_can( $post_type_object->cap->edit_posts ) )
			wp_die( __( 'Cheatin&#8217; uh?' ) );
	}

	function prepare_items() {
		global $post_type_object, $post_type, $avail_post_stati, $wp_query, $per_page, $mode;
      	$avail_post_stati = wp_edit_posts_query ( $wp_query->query );
             
	
		$this->hierarchical_display = ( $post_type_object->hierarchical && 'menu_order title' == $wp_query->query['orderby'] );
	}

	function has_items() {
		return have_posts();
	}

	function no_items() {
		global $post_type_object;

		if ( isset( $_REQUEST['post_status'] ) && 'trash' == $_REQUEST['post_status'] )
			echo $post_type_object->labels->not_found_in_trash;
		else
			echo $post_type_object->labels->not_found;
	}

	function get_views() {
		global $post_type, $post_type_object, $locked_post_status, $avail_post_stati;

		if ( !empty($locked_post_status) )
			return array();

		$status_links = array();
		$num_posts = wp_count_posts( $post_type, 'readable' );
		$class = '';
		$allposts = '';

		$current_user_id = get_current_user_id();

		if ( $this->user_posts_count ) {
			if ( isset( $_GET['author'] ) && ( $_GET['author'] == $current_user_id ) )
				$class = ' class="current"';
			$status_links['mine'] = "<a href='edit.php?post_type=$post_type&author=$current_user_id'$class>" . sprintf( _nx( 'Mine <span class="count">(%s)</span>', 'Mine <span class="count">(%s)</span>', $this->user_posts_count, 'posts' ), number_format_i18n( $this->user_posts_count ) ) . '</a>';
			$allposts = '&all_posts=1';
		}

		$total_posts = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array('show_in_admin_all_list' => false) ) as $state )
			$total_posts -= $num_posts->$state;

		$class = empty( $class ) && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['show_sticky'] ) ? ' class="current"' : '';
		$status_links['all'] = "<a href='edit.php?post_type=$post_type{$allposts}'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach ( get_post_stati(array('show_in_admin_status_list' => true), 'objects') as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( !in_array( $status_name, $avail_post_stati ) )
				continue;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status'] )
				$class = ' class="current"';

			$status_links[$status_name] = "<a href='edit.php?post_status=$status_name&amp;post_type=$post_type'$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		if ( ! empty( $this->sticky_posts_count ) ) {
			$class = ! empty( $_REQUEST['show_sticky'] ) ? ' class="current"' : '';

			$sticky_link = array( 'sticky' => "<a href='edit.php?post_type=$post_type&amp;show_sticky=1'$class>" . sprintf( _nx( 'Sticky <span class="count">(%s)</span>', 'Sticky <span class="count">(%s)</span>', $this->sticky_posts_count, 'posts' ), number_format_i18n( $this->sticky_posts_count ) ) . '</a>' );

			// Sticky comes after Publish, or if not listed, after All.
			$split = 1 + array_search( ( isset( $status_links['publish'] ) ? 'publish' : 'all' ), array_keys( $status_links ) );
			$status_links = array_merge( array_slice( $status_links, 0, $split ), $sticky_link, array_slice( $status_links, $split ) );
		}

		return $status_links;
	}

	function get_bulk_actions() {
		$actions = array();

		if ( $this->is_trash )
			$actions['untrash'] = __( 'Restore' );
		else
			$actions['edit'] = __( 'Edit' );

		if ( $this->is_trash || !EMPTY_TRASH_DAYS )
			$actions['delete'] = __( 'Delete Permanently' );
		else
			$actions['trash'] = __( 'Move to Trash' );

		return $actions;
	}

	function extra_tablenav( $which ) {
		global $post_type, $post_type_object, $cat;

		if ( 'top' == $which && !is_singular() ) {
?>
		<div class="alignleft actions">
<?php
			$this->months_dropdown( $post_type );

			if ( is_object_in_taxonomy( $post_type, 'category' ) ) {
				$dropdown_options = array(
					'show_option_all' => __( 'View all categories' ),
					'hide_empty' => 0,
					'hierarchical' => 1,
					'show_count' => 0,
					'orderby' => 'name',
					'selected' => $cat
				);
				wp_dropdown_categories( $dropdown_options );
			}
			do_action( 'restrict_manage_posts' );
			submit_button( __( 'Filter' ), 'secondary', 'post-query-submit', false );
?>
		</div>
<?php
		}

		if ( $this->is_trash && current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			submit_button( __( 'Empty Trash' ), 'button-secondary apply', 'delete_all', false );
		}
	}

	function current_action() {
		if ( isset( $_REQUEST['delete_all'] ) || isset( $_REQUEST['delete_all2'] ) )
			return 'delete_all';

		return parent::current_action();
	}

	function pagination( $which ) {
		global $post_type_object, $mode;

		parent::pagination( $which );

		if ( 'top' == $which && !$post_type_object->hierarchical )
			$this->view_switcher( $mode );
	}

	function get_table_classes() {
		global $post_type_object;

		return array( 'widefat', 'fixed', $post_type_object->hierarchical ? 'pages' : 'posts' );
	}

        function display_tablenav() {
            return null;
        }

        function get_column_info() {

		$columns = get_column_headers( 'wpsc-product_variants' );
		$hidden = get_hidden_columns( 'wpsc-product_variants' );
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		return $this->_column_headers;
	}

	function display_rows( $posts = array() ) {
		global $wp_query, $post_type_object, $per_page;
;
		if ( empty( $posts ) )
			$posts = $wp_query->posts;

                    $this->_display_rows( $posts );
	}

	function _display_rows( $posts ) {
		global $post, $mode;
                
		add_filter( 'the_title', 'esc_html' );

		// Create array of post IDs.
		$post_ids = array();

		foreach ( $posts as $a_post )
			$post_ids[] = $a_post->ID;

		foreach ( $posts as $post )
			$this->single_row( $post );
	}

	function _display_rows_hierarchical( $pages, $pagenum = 1, $per_page = 20 ) {
		global $wpdb;

		$level = 0;

		if ( ! $pages ) {
			$pages = get_pages( array( 'sort_column' => 'menu_order' ) );

			if ( ! $pages )
				return false;
		}

		/*
		 * arrange pages into two parts: top level pages and children_pages
		 * children_pages is two dimensional array, eg.
		 * children_pages[10][] contains all sub-pages whose parent is 10.
		 * It only takes O( N ) to arrange this and it takes O( 1 ) for subsequent lookup operations
		 * If searching, ignore hierarchy and treat everything as top level
		 */
		if ( empty( $_REQUEST['s'] ) ) {

			$top_level_pages = array();
			$children_pages = array();

			foreach ( $pages as $page ) {

				// catch and repair bad pages
				if ( $page->post_parent == $page->ID ) {
					$page->post_parent = 0;
					$wpdb->update( $wpdb->posts, array( 'post_parent' => 0 ), array( 'ID' => $page->ID ) );
					clean_page_cache( $page->ID );
				}

				if ( 0 == $page->post_parent )
					$top_level_pages[] = $page;
				else
					$children_pages[ $page->post_parent ][] = $page;
			}

			$pages = &$top_level_pages;
		}

		$count = 0;
		$start = ( $pagenum - 1 ) * $per_page;
		$end = $start + $per_page;

		foreach ( $pages as $page ) {
			if ( $count >= $end )
				break;

			if ( $count >= $start )
				echo "\t" . $this->single_row( $page, $level );

			$count++;

			if ( isset( $children_pages ) )
				$this->_page_rows( $children_pages, $count, $page->ID, $level + 1, $pagenum, $per_page );
		}

		// if it is the last pagenum and there are orphaned pages, display them with paging as well
		if ( isset( $children_pages ) && $count < $end ){
			foreach ( $children_pages as $orphans ){
				foreach ( $orphans as $op ) {
					if ( $count >= $end )
						break;
					if ( $count >= $start )
						echo "\t" . $this->single_row( $op, 0 );
					$count++;
				}
			}
		}
	}

	/**
	 * Given a top level page ID, display the nested hierarchy of sub-pages
	 * together with paging support
	 *
	 * @since 3.1.0 (Standalone function exists since 2.6.0)
	 *
	 * @param unknown_type $children_pages
	 * @param unknown_type $count
	 * @param unknown_type $parent
	 * @param unknown_type $level
	 * @param unknown_type $pagenum
	 * @param unknown_type $per_page
	 */
	function _page_rows( &$children_pages, &$count, $parent, $level, $pagenum, $per_page ) {

		if ( ! isset( $children_pages[$parent] ) )
			return;

		$start = ( $pagenum - 1 ) * $per_page;
		$end = $start + $per_page;

		foreach ( $children_pages[$parent] as $page ) {

			if ( $count >= $end )
				break;

			// If the page starts in a subtree, print the parents.
			if ( $count == $start && $page->post_parent > 0 ) {
				$my_parents = array();
				$my_parent = $page->post_parent;
				while ( $my_parent ) {
					$my_parent = get_post( $my_parent );
					$my_parents[] = $my_parent;
					if ( !$my_parent->post_parent )
						break;
					$my_parent = $my_parent->post_parent;
				}
				$num_parents = count( $my_parents );
				while ( $my_parent = array_pop( $my_parents ) ) {
					echo "\t" . $this->single_row( $my_parent, $level - $num_parents );
					$num_parents--;
				}
			}

			if ( $count >= $start )
				echo "\t" . $this->single_row( $page, $level );

			$count++;

			$this->_page_rows( $children_pages, $count, $page->ID, $level + 1, $pagenum, $per_page );
		}

		unset( $children_pages[$parent] ); //required in order to keep track of orphans
	}

	function single_row( $a_post, $level = 0 ) {
		global $post, $current_screen, $mode;
		static $rowclass;
		$global_post = $post;
		$product = $post = $a_post;
		setup_postdata( $post );

		$rowclass = 'alternate' == $rowclass ? '' : 'alternate';
		$post_owner = ( get_current_user_id() == $post->post_author ? 'self' : 'other' );
		$edit_link = get_edit_post_link( $post->ID );
		$title = _draft_or_post_title();
		$post_type_object = get_post_type_object( $post->post_type );
		$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );
		$post_format = get_post_format( $post->ID );
		$post_format_class = ( $post_format && !is_wp_error($post_format) ) ? 'format-' . sanitize_html_class( $post_format ) : 'format-default';
	
               ?>
		<tr id='post-<?php echo $post->ID; ?>' class='<?php echo trim( $rowclass . ' author-' . $post_owner . ' status-' . $post->post_status . ' ' . $post_format_class); ?> iedit' valign="top">
	<?php

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";
                        
			switch ( $column_name ) {


			case 'title':
				if ( $this->hierarchical_display ) {
					$attributes = 'class="post-title page-title column-title"' . $style;

					if ( 0 == $level && (int) $post->post_parent > 0 ) {
						//sent level 0 by accident, by default, or because we don't know the actual level
						$find_main_page = (int) $post->post_parent;
						while ( $find_main_page > 0 ) {
							$parent = get_page( $find_main_page );

							if ( is_null( $parent ) )
								break;

							$level++;
							$find_main_page = (int) $parent->post_parent;

							if ( !isset( $parent_name ) )
								$parent_name = $parent->post_title;
						}
					}

					$post->post_title = esc_html( $post->post_title );
					$pad = str_repeat( '&#8212; ', $level );
?>
			<td <?php echo $attributes ?>><strong><?php if ( $can_edit_post && $post->post_status != 'trash' ) { ?><a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ); ?>"><?php echo $pad; echo $title ?></a><?php } else { echo $pad; echo $title; }; _post_states( $post ); echo isset( $parent_name ) ? ' | ' . $post_type_object->labels->parent_item_colon . ' ' . esc_html( $parent_name ) : ''; ?></strong>
<?php
				}
				else {
					$attributes = 'class="post-title page-title column-title"' . $style;
?>
			<td <?php echo $attributes ?>><strong><?php if ( $can_edit_post && $post->post_status != 'trash' ) { ?><a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ); ?>"><?php echo $title ?></a><?php } else { echo $title; }; _post_states( $post ); ?></strong>
<?php
					if ( 'excerpt' == $mode ) {
						the_excerpt();
					}
				}

				$actions = array();
				if ( $can_edit_post && 'trash' != $post->post_status ) {
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
					$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
				}
		
				$actions = apply_filters( $this->hierarchical_display ? 'page_row_actions' : 'post_row_actions', $actions, $post );
				echo $this->row_actions( $actions );

				get_inline_data( $post );
				echo '</td>';
			break;

		case 'SKU':
			$sku = get_post_meta($product->ID, '_wpsc_sku', true);
			if($sku == ''){
				$sku = 'N/A';
			}
			?>
				<td  <?php echo $attributes ?>>
					<span class="skudisplay"><?php echo $sku; ?></span>
                                        <?php echo '<div id="inline_' . $post->ID . '_sku" class="hidden">' . $sku . '</div>'; ?>
				</td>
			<?php
		break;
		case 'sale_price':

			$price = get_post_meta($product->ID, '_wpsc_special_price', true);
			?>
				<td  <?php echo $attributes ?>>
					<?php echo wpsc_currency_display( $price ); ?>
                                        <?php echo '<div id="inline_' . $post->ID . '_sale_price" class="hidden">' . $price . '</div>'; ?>
				</td>
			<?php

		break;



		case 'image':  /* !image case */
			?>
			<td class="product-image ">
			<?php
		   $attached_images = (array)get_posts(array(
	          'post_type' => 'attachment',
	          'numberposts' => 1,
	          'post_status' => null,
	          'post_parent' => $product->ID,
	          'orderby' => 'menu_order',
	          'order' => 'ASC'
		    ));



		 	 if(isset($product->ID) && has_post_thumbnail($product->ID)){
				echo get_the_post_thumbnail($product->ID, 'admin-product-thumbnails');
		     }elseif(!empty($attached_images)){
			    $attached_image = $attached_images[0];

				$src =wp_get_attachment_url($attached_image->ID);
		     ?>
		     	<div style='width:38px;height:38px;overflow:hidden;'>
					<img title='Drag to a new position' src='<?php echo $src; ?>' alt='<?php echo $title; ?>' width='38' height='38' />
				</div>
				<?php


		     } else {
		      	$image_url = WPSC_CORE_IMAGES_URL . "/no-image-uploaded.gif";
				?>
					<img title='Drag to a new position' src='<?php echo $image_url; ?>' alt='<?php echo $title; ?>' width='38' height='38' />
				<?php


		      }

		?>
			</td>
			<?php
		break;
                
		case 'price':  /* !price case */

			$price = get_post_meta($product->ID, '_wpsc_price', true);
			?>
				<td  <?php echo $attributes ?>>
					<?php echo wpsc_currency_display( $price ); ?>
                                        <?php echo '<div id="inline_' . $post->ID . '_price" class="hidden">' . $price . '</div>'; ?>
				</td>
			<?php
		break;

		case 'weight' :

			$product_data['meta'] = array();
			$product_data['meta'] = get_post_meta($product->ID, '');
				foreach($product_data['meta'] as $meta_name => $meta_value) {
					$product_data['meta'][$meta_name] = maybe_unserialize(array_pop($meta_value));
				}
		$product_data['transformed'] = array();
		if(!isset($product_data['meta']['_wpsc_product_metadata']['weight'])) $product_data['meta']['_wpsc_product_metadata']['weight'] = "";
		if(!isset($product_data['meta']['_wpsc_product_metadata']['weight_unit'])) $product_data['meta']['_wpsc_product_metadata']['weight_unit'] = "";

		$product_data['transformed']['weight'] = wpsc_convert_weight($product_data['meta']['_wpsc_product_metadata']['weight'], "gram", $product_data['meta']['_wpsc_product_metadata']['weight_unit']);
			$weight = $product_data['transformed']['weight'];
			if($weight == ''){
				$weight = '0';
			}

			$unit = $product_data['meta']['_wpsc_product_metadata']['weight_unit'];
			switch($unit) {
				case "pound":
					$unit = " lbs.";
					break;
				case "ounce":
					$unit = " oz.";
					break;
				case "gram":
					$unit = " g";
					break;
				case "kilograms":
				case "kilogram":
					$unit = " kgs.";
					break;
			}
			?>
				<td  <?php echo $attributes ?>>
					<span class="weightdisplay"><?php echo $weight; ?></span>
                                        <?php echo '<div id="inline_' . $post->ID . '_weight" class="hidden">' . $weight . '</div>'; ?>
				</td>
			<?php

		break;

		case 'stock' :
			$stock = get_post_meta($product->ID, '_wpsc_stock', true);
			if($stock == ''){
				$stock = 'N/A';
			}
			?>
				<td  <?php echo $attributes ?>>
					<span class="stockdisplay"><?php echo $stock; ?></span>
                                        <?php echo '<div id="inline_' . $post->ID . '_stock" class="hidden">' . $stock . '</div>'; ?>
				</td>
	<?php
		break;

				
			default:
			?>
			<td <?php echo $attributes ?>><?php
				do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );
			?></td>
			<?php
			break;
		}
	}
	?>
		</tr>
	<?php
		$post = $global_post;
	}

	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 3.1.0
	 */
	function inline_edit() {
		global $mode;

		$screen = get_current_screen();

		$post = get_default_post_to_edit( $screen->post_type );
		$post_type_object = get_post_type_object( $screen->post_type );

		$taxonomy_names = get_object_taxonomies( $screen->post_type );
		$hierarchical_taxonomies = array();
		$flat_taxonomies = array();
		foreach ( $taxonomy_names as $taxonomy_name ) {
			$taxonomy = get_taxonomy( $taxonomy_name );

			if ( !$taxonomy->show_ui )
				continue;

			if ( $taxonomy->hierarchical )
				$hierarchical_taxonomies[] = $taxonomy;
			else
				$flat_taxonomies[] = $taxonomy;
		}

		$m = ( isset( $mode ) && 'excerpt' == $mode ) ? 'excerpt' : 'list';
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );
		$core_columns = array( 'title' => true );

	?>

	<form method="get" action=""><table style="display: none"><tbody id="inlineedit">
		<?php
		$hclass = count( $hierarchical_taxonomies ) ? 'post' : 'page';
		$bulk = 0;
		while ( $bulk < 2 ) { ?>

		<tr id="<?php echo $bulk ? 'bulk-edit' : 'inline-edit'; ?>" class="inline-edit-row inline-edit-row-<?php echo "$hclass inline-edit-$screen->post_type ";
			echo $bulk ? "bulk-edit-row bulk-edit-row-$hclass bulk-edit-$screen->post_type" : "quick-edit-row quick-edit-row-$hclass inline-edit-$screen->post_type";
		?>" style="display: none"><td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">

		<fieldset class="inline-edit-col-left"><div class="inline-edit-col">
			<h4><?php echo $bulk ? __( 'Bulk Edit' ) : __( 'Quick Edit' ); ?></h4>
	<?php

	if ( post_type_supports( $screen->post_type, 'title' ) ) :
		if ( $bulk ) : ?>
			<div id="bulk-title-div">
				<div id="bulk-titles"></div>
			</div>

	<?php else : // $bulk ?>

			<label>
				<span class="title"><?php _e( 'Title' ); ?></span>
				<span class="input-text-wrap"><input type="text" name="post_title" class="ptitle" value="" /></span>
			</label>

	<?php endif; // $bulk
	endif; // post_type_supports title 

	?>

		</div></fieldset>

	<?php
		list( $columns ) = $this->get_column_info();
                
		foreach ( $columns as $column_name => $column_display_name ) {
			if ( isset( $core_columns[$column_name] ) )
				continue;
			do_action( $bulk ? 'bulk_edit_custom_box' : 'quick_edit_custom_box', $column_name, $screen->post_type );
		}
	?>
		<p class="submit inline-edit-save">
			<a accesskey="c" href="#inline-edit" title="<?php _e( 'Cancel' ); ?>" class="button-secondary cancel alignleft"><?php _e( 'Cancel' ); ?></a>
			<?php if ( ! $bulk ) {
				wp_nonce_field( 'inlineeditnonce', '_inline_edit', false );
				$update_text = __( 'Update' );
				?>
				<a accesskey="s" href="#inline-edit" title="<?php _e( 'Update' ); ?>" class="button-primary save alignright"><?php echo esc_attr( $update_text ); ?></a>
				<img class="waiting" style="display:none;" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
			<?php } else {
				submit_button( __( 'Update' ), 'button-primary alignright', 'bulk_edit', false, array( 'accesskey' => 's' ) );
			} ?>
			<input type="hidden" name="post_view" value="<?php echo esc_attr( $m ); ?>" />
			<input type="hidden" name="screen" value="<?php echo esc_attr( $screen->id ); ?>" />
			<br class="clear" />
		</p>
		</td></tr>
	<?php
		$bulk++;
		}
?>
		</tbody></table></form>
<?php
	}
}

?>
