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

        wp_enqueue_style(
            'wrhr-reader-css',
            WRHR_URL . 'assets/css/wrhr-reader.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'wrhr-renderer',
            $base_url . 'assets/js/wrhr-renderer.js',
            array( 'jquery' ),
            WRHR_VERSION,
            true
        );

        if ( ! wp_script_is( 'wp-api-fetch', 'registered' ) ) {
            wp_register_script( 'wp-api-fetch', '/wp-includes/js/dist/api-fetch.min.js', array( 'wp-hooks', 'wp-i18n', 'wp-url' ), false, true );
        }

        wp_localize_script(
            'wrhr-renderer',
            'wpApiSettings',
            array(
                'root' => esc_url_raw( rest_url() ),
            )
        );
    }

    /** Render the global reader modal markup. */
    public static function render_modal() {
        ?>
        <div id="wrhr-modal" class="wrhr-modal" aria-hidden="true">

            <div class="wrhr-overlay" id="wrhr-modal-overlay"></div>

            <div class="wrhr-modal-content" id="wrhr-modal-content">

                <button class="wrhr-close" id="wrhr-close">×</button>
                <button class="wrhr-fs-btn" id="wrhr-fs-btn">⤢</button>

                <div class="wrhr-modal-header">
                    <h3 class="wrhr-modal-title" id="wrhr-modal-title"></h3>

                    <!-- MINI NOTICE: Fullscreen Suggested -->
                    <div class="wrhr-notice-mini">
                        Full ekran modu önerilir
                    </div>
                </div>

                <div class="wrhr-reader-container">
                    <div id="wrhr-page-wrapper"></div>
                </div>

                <div class="wrhr-controls">
                    <button id="wrhr-prev">⟨⟨</button>
                    <span id="wrhr-page-info">Page 1 / 1</span>
                    <button id="wrhr-next">⟩⟩</button>
                </div>

            </div>
        </div>
        <?php
    }
}

add_action( 'wp_footer', array( 'WRHR_Assets', 'render_modal' ) );
