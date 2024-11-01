<?php
/**
 * @package : Where are my post types?
 * @version : 1.0
 * @last modified : March 21 2014
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function supt_trigger_admin_menu() {
	add_options_page(
		__( 'Where are my post types?', SUPT_TEXT_DOMAIN ),
		__( 'Where are my post types?', SUPT_TEXT_DOMAIN ),
		'manage_options',
		SUPT_TEXT_DOMAIN,
		'supt_admin_menu'
	);
}
add_action( 'admin_menu', 'supt_trigger_admin_menu' );

function supt_admin_menu() {
	if ( empty( $_GET['mode'] ) ) {
		require_once( SUPT_VIEW . 'admin-index.php' );
	} else if ( $_GET['mode'] == 'posttype' ) {
		require_once( SUPT_VIEW . 'admin-post-type.php' );
	}
}

function supt_admin_enqueue_scripts() {
	wp_enqueue_script( 'supt', SUPT_PLUGIN_URL . '/assets/script.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_style('supt', SUPT_PLUGIN_URL . '/assets/style.css');
}
add_action( 'admin_enqueue_scripts', 'supt_admin_enqueue_scripts' );
