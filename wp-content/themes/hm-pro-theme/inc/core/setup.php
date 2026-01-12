<?php
/**
 * Theme setup
 *
 * @package HMPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'hmpro_theme_setup' );

function hmpro_theme_setup() {
	load_theme_textdomain( 'hmpro', HMPRO_THEME_PATH . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'responsive-embeds' );

	// WooCommerce support (styling will come later).
	add_theme_support( 'woocommerce' );

	register_nav_menus(
		array(
			'hm_primary' => __( 'Primary Menu', 'hmpro' ),
			'hm_footer'  => __( 'Footer Menu', 'hmpro' ),
		)
	);
}
