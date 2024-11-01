<?php
/**
 * @package : Where are my post types?
 * @version : 1.0
 * @last modified : March 21 2014
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( SUPT_FUNCTION . 'type-walker.php' );

global $wp_query, $wpdb;
$post_type = $_GET['posttype'];

$query_args = array(
	'post_type' => $post_type
);
if ( !empty( $_GET['paged'] ) ) {
	$query_args['paged'] = $_GET['paged'];
}

$wp_query = new WP_Query( $query_args );

$wp_list_table = new SUPT_Posts_List_Table( $post_type );
$pagenum = $wp_list_table->get_pagenum();

$wp_list_table->prepare_items();
?>

<div class="wrap supt-wrapper">
	<h2><?php _e( 'Post Type', SUPT_TEXT_DOMAIN ); ?> : <?php _e( $post_type, SUPT_TEXT_DOMAIN ); ?></h2>

	<?php $wp_list_table->views(); ?>
	<?php $wp_list_table->display(); ?>
</div>
