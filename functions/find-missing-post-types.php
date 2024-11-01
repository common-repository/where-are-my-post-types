<?php
/**
 * @package : Where are my post types?
 * @version : 1.0
 * @last modified : March 21 2014
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function supt_find_missing_post_types_callback() {
	global $wpdb; // this is how you get access to the database

	$post_types = $wpdb->get_results( "SELECT post_type FROM {$wpdb->posts} GROUP BY post_type" );
	$missing_post_type = array();
	$activated_post_types = explode( ',', $_POST['activated_post_types'] );

	foreach( $post_types as &$post_type ) {
		if ( !in_array( $post_type->post_type, $activated_post_types ) )
			$missing_post_type[] = $post_type->post_type;
	}

	$missing_post_type = implode( ',', $missing_post_type );

	update_option( 'supt_missing_posttypes', $missing_post_type );
	echo $missing_post_type;

	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_supt_find_missing_post_types', 'supt_find_missing_post_types_callback' );