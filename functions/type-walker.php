<?php
/**
 * Posts List Table class.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 * @access private
 */
class SUPT_Posts_List_Table extends WP_List_Table {

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

	function __construct( $post_type ) {
		global $post_type_object, $wpdb;
		$activated_post_types = get_post_types();

		$this->activated_post_types = array();
		$this->activated_post_types_object = array();
		foreach ( $activated_post_types as $post_type ) {
			if ( !in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item' ) ) ) {
				$this->activated_post_types_object[] = get_post_type_object( $post_type );
				$this->activated_post_types[] = $post_type;
			}
		}

		parent::__construct();

		$this->post_type = $post_type;
	}

	function ajax_user_can() {
		return current_user_can( get_post_type_object( $this->post_type )->cap->edit_posts );
	}

	function prepare_items() {
		global $avail_post_stati, $wp_query, $per_page, $mode;

		$avail_post_stati = wp_edit_posts_query();

		$this->hierarchical_display = ( is_post_type_hierarchical( $this->post_type ) && 'menu_order title' == $wp_query->query['orderby'] );

		$total_items = $this->hierarchical_display ? $wp_query->post_count : $wp_query->found_posts;

		$post_type = $this->post_type;
		$per_page = $this->get_items_per_page( 'edit_' . $post_type . '_per_page' );
 		$per_page = apply_filters( 'edit_posts_per_page', $per_page, $post_type );

		if ( $this->hierarchical_display )
			$total_pages = ceil( $total_items / $per_page );
		else
			$total_pages = $wp_query->max_num_pages;

		$mode = empty( $_REQUEST['mode'] ) ? 'list' : $_REQUEST['mode'];

		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page
		) );
	}

	function has_items() {
		return have_posts();
	}

	function no_items() {
		if ( isset( $_REQUEST['post_status'] ) && 'trash' == $_REQUEST['post_status'] )
			echo get_post_type_object( $this->post_type )->labels->not_found_in_trash;
		else
			echo get_post_type_object( $this->post_type )->labels->not_found;
	}

	function get_views() {
		$status_links = array();
		$status_links['delete_all'] = "<a href='#'>" . __( 'Delete Permanently' ) . '</a>';

		foreach ( $this->activated_post_types as $i => $post_type ) {
			$status_links['convert_' . $post_type] = "<a href='#'>" . __( 'To ' ) . $this->activated_post_types_object[ $i ]->labels->name . '</a>';
		}

		return $status_links;
	}

	function pagination( $which ) {
		parent::pagination( $which );
	}

	function get_table_classes() {
		return array( 'widefat', 'fixed', is_post_type_hierarchical( $this->post_type ) ? 'pages' : 'posts' );
	}

	function get_sortable_columns() {
		return array();
	}

	function display_rows( $posts = array(), $level = 0 ) {
		global $wp_query, $per_page;

		if ( empty( $posts ) )
			$posts = $wp_query->posts;

		add_filter( 'the_title', 'esc_html' );

		if ( $this->hierarchical_display ) {
			$this->_display_rows_hierarchical( $posts, $this->get_pagenum(), $per_page );
		} else {
			$this->_display_rows( $posts, $level );
		}
	}

	function _display_rows( $posts, $level = 0 ) {
		global $mode;

		// Create array of post IDs.
		$post_ids = array();

		foreach ( $posts as $a_post )
			$post_ids[] = $a_post->ID;

		$this->comment_pending_count = get_pending_comments_num( $post_ids );

		foreach ( $posts as $post )
			$this->single_row( $post, $level );
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
					clean_post_cache( $page );
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

			if ( $count >= $start ) {
				echo "\t";
				$this->single_row( $page, $level );
			}

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

					if ( $count >= $start ) {
						echo "\t";
						$this->single_row( $op, 0 );
					}

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
	 * @param array $children_pages
	 * @param int $count
	 * @param int $parent
	 * @param int $level
	 * @param int $pagenum
	 * @param int $per_page
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
					echo "\t";
					$this->single_row( $my_parent, $level - $num_parents );
					$num_parents--;
				}
			}

			if ( $count >= $start ) {
				echo "\t";
				$this->single_row( $page, $level );
			}

			$count++;

			$this->_page_rows( $children_pages, $count, $page->ID, $level + 1, $pagenum, $per_page );
		}

		unset( $children_pages[$parent] ); //required in order to keep track of orphans
	}

	function get_bulk_actions() {
		$actions = array();
		$actions['delete'] = __( 'Delete Permanently' );

		foreach ( $this->activated_post_types as $i => $post_type ) {
			$actions['convert_' . $post_type] = __( 'To ' ) . $this->activated_post_types_object[ $i ]->labels->name;
		}

		return $actions;
	}

	function get_column_info() {
		$get_column_info = array(
			array(
				'cb' => 'cb',
				'thumbnail' => '',
				'title' => __( 'Title' ),
				'author' => __( 'Author' ),
				'date' => __( 'Date' ),
			),
			array(
			),
			array(
				'title' => array(
					'0' => 'title',
					'1' => ''
				),
				'parent' => array(
					'0' => 'parent',
					'1' => ''
				),
				'date' => array(
					'0' => 'date',
					'1' => ''
				)
			)
		);
		return $get_column_info;
	}

	function single_row( $post, $level = 0 ) {
		global $mode;
		static $alternate;

		$global_post = get_post();
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		$edit_link = get_edit_post_link( $post->ID );
		$title = _draft_or_post_title();
		$post_type_object = get_post_type_object( $post->post_type );

		$alternate = 'alternate' == $alternate ? '' : 'alternate';
		$classes = $alternate . ' iedit author-' . ( get_current_user_id() == $post->post_author ? 'self' : 'other' );

		$lock_holder = wp_check_post_lock( $post->ID );
		if ( $lock_holder ) {
			$classes .= ' wp-locked';
			$lock_holder = get_userdata( $lock_holder );
		}

		if ( $post->post_parent ) {
		    $count = count( get_post_ancestors( $post->ID ) );
		    $classes .= ' level-'. $count;
		} else {
		    $classes .= ' level-0';
		}
	?>
		<tr id="post-<?php echo $post->ID; ?>" class="<?php echo implode( ' ', get_post_class( $classes, $post->ID ) ); ?>" valign="top">
	<?php

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ( $column_name ) {

			case 'cb':
			?>
			<th scope="row" class="check-column">
				<label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php printf( __( 'Select %s' ), $title ); ?></label>
				<input id="cb-select-<?php the_ID(); ?>" type="checkbox" name="post[]" value="<?php the_ID(); ?>" />
				<div class="locked-indicator"></div>
			</th>
			<?php
			break;

			case 'thumbnail':
				echo '<td class="thumbnail">';
				if ( has_post_thumbnail() ) {
					echo get_the_post_thumbnail( $post->ID, array( 60, 60 ) );
				}
				echo '</td>';
			break;

			case 'title':
				$attributes = 'class="post-title page-title column-title"' . $style;
				if ( $this->hierarchical_display ) {
					if ( 0 == $level && (int) $post->post_parent > 0 ) {
						//sent level 0 by accident, by default, or because we don't know the actual level
						$find_main_page = (int) $post->post_parent;
						while ( $find_main_page > 0 ) {
							$parent = get_post( $find_main_page );

							if ( is_null( $parent ) )
								break;

							$level++;
							$find_main_page = (int) $parent->post_parent;

							if ( !isset( $parent_name ) ) {
								/** This filter is documented in wp-includes/post-template.php */
								$parent_name = apply_filters( 'the_title', $parent->post_title, $parent->ID );
							}
						}
					}
				}

				$pad = str_repeat( '&#8212; ', $level );
				echo "<td $attributes>";
				echo "<strong>";

				echo $pad . $title;

				_post_states( $post );

				if ( isset( $parent_name ) )
					echo ' | ' . $post_type_object->labels->parent_item_colon . ' ' . esc_html( $parent_name );

				echo "</strong>\n";

				echo '<p class="description">' . __( 'Status' ) . ' : ' . __( $post->post_status ) . '</p>';

				the_excerpt();

				$actions = array();

				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . "</a>";
				$actions = apply_filters( is_post_type_hierarchical( $post->post_type ) ? 'page_row_actions' : 'post_row_actions', $actions, $post );

				foreach ( $this->activated_post_types as $i => $post_type ) {
					$actions['convert_' . $post_type] = "<a href='#'>" . __( 'To ' ) . $this->activated_post_types_object[ $i ]->labels->name . '</a>';
				}
				echo $this->row_actions( $actions );

				echo '</td>';
			break;

			case 'date':
				if ( '0000-00-00 00:00:00' == $post->post_date ) {
					$t_time = $h_time = __( 'Unpublished' );
					$time_diff = 0;
				} else {
					$t_time = get_the_time( __( 'Y/m/d g:i:s A' ) );
					$m_time = $post->post_date;
					$time = get_post_time( 'G', true, $post );

					$time_diff = time() - $time;

					if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS )
						$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
					else
						$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
				}

				echo '<td ' . $attributes . '>';
				if ( 'excerpt' == $mode )
					echo apply_filters( 'post_date_column_time', $t_time, $post, $column_name, $mode );
				else
					echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, $column_name, $mode ) . '</abbr>';
				echo '<br />';
				if ( 'publish' == $post->post_status ) {
					_e( 'Published' );
				} elseif ( 'future' == $post->post_status ) {
					if ( $time_diff > 0 )
						echo '<strong class="attention">' . __( 'Missed schedule' ) . '</strong>';
					else
						_e( 'Scheduled' );
				} else {
					_e( 'Last Modified' );
				}
				echo '</td>';
			break;

			case 'author':
			?>
			<td <?php echo $attributes ?>><?php
				printf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'author' => get_the_author_meta( 'ID' ) ), 'edit.php' )),
					get_the_author()
				);
			?></td>
			<?php
			break;
			}
		}
	?>
		</tr>
	<?php
		$GLOBALS['post'] = $global_post;
	}
}
