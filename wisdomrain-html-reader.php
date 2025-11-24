<?php
/**
 * Plugin Name:       WisdomRain HTML Reader
 * Description:       A lightweight HTML reader plugin skeleton for WordPress.
 * Version:           0.1.0
 * Author:            WisdomRain
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Text Domain:       wisdomrain-html-reader
 * Domain Path:       /languages
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WRHR_VERSION', '0.1.0' );
define( 'WRHR_PLUGIN_FILE', __FILE__ );
define( 'WRHR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WRHR_URL', plugin_dir_url( __FILE__ ) );
define( 'WRHR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once WRHR_PLUGIN_DIR . 'includes/class-wrhr-loader.php';

/**
 * Initialize plugin loader.
 */
function wrhr_initialize_plugin() {
    return WRHR_Loader::init( WRHR_VERSION );
}

/**
 * Load the plugin text domain.
 */
function wrhr_load_textdomain() {
    load_plugin_textdomain( 'wisdomrain-html-reader', false, dirname( WRHR_PLUGIN_BASENAME ) . '/languages' );
}

add_action( 'plugins_loaded', 'wrhr_initialize_plugin' );
add_action( 'init', 'wrhr_load_textdomain' );

/* ===============================================================
   WRHR — FINAL INLINE STYLE CLEANER (Word HTML Cleanup)
   =============================================================== */

add_filter( 'wrhr_clean_html_before_paginate', function ( $html ) {

    // 1) Remove all inline style="" attributes
    $html = preg_replace( '/\s*style=("|\')(.*?)("|\')/i', '', $html );

    // 2) Remove all inline font-family attributes
    $html = preg_replace( '/\s*font-family:[^;"]*;?/i', '', $html );

    // 3) Remove color attributes (Word paints titles blue)
    $html = preg_replace( '/\s*color:[^;"]*;?/i', '', $html );

    // 4) Remove size attributes (font-size)
    $html = preg_replace( '/\s*font-size:[^;"]*;?/i', '', $html );

    // 5) Remove line-height attributes
    $html = preg_replace( '/\s*line-height:[^;"]*;?/i', '', $html );

    // 6) Clean Word spans: <span class=...> → <span>
    $html = preg_replace( '/<span[^>]*>/i', '<span>', $html );

    // 7) Remove empty spans created by Word
    $html = preg_replace( '/<span>\s*<\/span>/i', '', $html );

    // 8) Remove <font> tags completely (for older docs)
    $html = preg_replace( '/<\/?font[^>]*>/i', '', $html );

    return $html;
} );



