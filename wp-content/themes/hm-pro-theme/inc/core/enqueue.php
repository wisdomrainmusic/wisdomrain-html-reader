<?php
/**
 * Enqueue styles/scripts
 *
 * @package HMPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'hmpro_enqueue_assets' );

function hmpro_enqueue_assets() {
	// Main stylesheet is style.css (header required by WP). We will enqueue real CSS later.
	wp_enqueue_style(
		'hmpro-style',
		get_stylesheet_uri(),
		array(),
		HMPRO_THEME_VERSION
	);
}
