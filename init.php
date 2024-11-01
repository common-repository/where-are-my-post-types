<?php
/**
 * Plugin Name: Where are my post types?
 * Plugin URI: http://www.sujinc.com/
 * Description:
 * Version: 1.0
 * Author: Sujin 수진 Choi
 * Author URI: http://www.sujinc.com/
 * License: GPLv2 or later
 * Text Domain: where-are-my-post-type
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

ini_set('display_errors',1);
error_reporting(-1);

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if ( is_admin() ) {
	define( 'SUPT_VERSION', '1.0' );
	define( 'SUPT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'SUPT_FUNCTION', dirname( __FILE__ ) . '/functions/' );
	define( 'SUPT_VIEW', dirname( __FILE__ ) . '/views/' );
	define( 'SUPT_TEXT_DOMAIN', 'where-are-my-post-type' );

	$default_post_types = array(
		'post',
		'page',
		'attachment',
		'revision',
		'nav_menu_item',
	);

	require_once( SUPT_FUNCTION . 'admin-menu.php' );
	require_once( SUPT_FUNCTION . 'find-missing-post-types.php' );

	function supt_load_plugin_textdomain() {
		$lang_dir = basename( dirname( __FILE__ ) ) . '/languages';
		load_plugin_textdomain( SUPT_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'supt_load_plugin_textdomain' );
}
