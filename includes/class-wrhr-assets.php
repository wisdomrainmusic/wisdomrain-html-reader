<?php
/**
 * Front-end asset loader.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register and enqueue front-end assets for the reader shortcode.
 */
class WRHR_Assets {

    /** Enqueue public-facing CSS and JS. */
    public static function enqueue_frontend() {
        $base_url = plugin_dir_url( WRHR_PLUGIN_FILE );

        wp_enqueue_style(
            'wrhr-style',
            $base_url . 'assets/css/wrhr-style.css',
            array(),
            WRHR_VERSION
        );

        wp_enqueue_script(
            'wrhr-renderer',
            $base_url . 'assets/js/wrhr-renderer.js',
            array( 'jquery' ),
            WRHR_VERSION,
            true
        );
    }
}
