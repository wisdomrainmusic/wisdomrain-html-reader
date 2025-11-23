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
