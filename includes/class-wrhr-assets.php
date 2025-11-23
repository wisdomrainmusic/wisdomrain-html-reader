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

        $flags_base_url = WRHR_Languages::get_flags_base_url();

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

        wp_localize_script(
            'wrhr-renderer',
            'wrhrLangConfig',
            array(
                'languages'        => WRHR_Languages::as_client_payload(),
                'flags_base_url'   => esc_url_raw( $flags_base_url ),
                'storage_keys'     => array(
                    'last_lang'        => 'wrhr_last_lang',
                    'last_page_prefix' => 'wrhr_last_page_',
                ),
                'google_selectors' => array(
                    'combo' => 'select.goog-te-combo',
                ),
            )
        );
    }

    /** Render the global reader modal markup. */
    public static function render_modal() {
        $flags_base_url = WRHR_Languages::get_flags_base_url();
        ?>
        <div id="wrhr-modal" class="wrhr-modal" aria-hidden="true">

            <div class="wrhr-overlay" id="wrhr-modal-overlay"></div>

            <div class="wrhr-modal-content" id="wrhr-modal-content">

                <button class="wrhr-close" id="wrhr-close">×</button>
                <button class="wrhr-fs-btn" id="wrhr-fs-btn">⤢</button>

                <div class="wrhr-modal-header">
                    <div class="wrhr-modal-header-main">
                        <h3 class="wrhr-modal-title" id="wrhr-modal-title"></h3>

                        <!-- MINI NOTICE: Fullscreen Suggested -->
                        <div class="wrhr-notice-mini">
                            Full screen mode recommended
                        </div>
                    </div>

                    <div class="wrhr-lang-switcher notranslate" id="wrhr-lang-switcher" aria-label="Translate language chooser">
                        <?php foreach ( WRHR_Languages::all() as $lang ) :
                            $flag = $flags_base_url . $lang['flag'];
                            ?>
                            <button
                                type="button"
                                class="wrhr-lang-btn notranslate"
                                data-lang="<?php echo esc_attr( $lang['code'] ); ?>"
                                data-google="<?php echo esc_attr( $lang['google_code'] ); ?>"
                                aria-label="<?php echo esc_attr( $lang['label'] ); ?>">
                                <img
                                    src="<?php echo esc_url( $flag ); ?>"
                                    alt="<?php echo esc_attr( $lang['label'] ); ?>"
                                    class="notranslate" />
                            </button>
                        <?php endforeach; ?>
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
