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
            'wrhr-frontend',
            $base_url . 'assets/css/wrhr-frontend.css',
            array(),
            WRHR_VERSION
        );

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
            'wrhr-frontend',
            $base_url . 'assets/js/wrhr-frontend.js',
            array(),
            WRHR_VERSION,
            true
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

        // Prepare translate widget config for JS (Phase 3 will consume this).
        if ( class_exists( 'WRHR_Languages' ) ) {
            wp_localize_script(
                'wrhr-renderer',
                'wrhrTranslateConfig',
                array(
                    'languages'   => WRHR_Languages::get_js_config(),
                    'flagsBase'   => trailingslashit( WRHR_URL . 'assets/flags' ),
                    'defaultLang' => 'en',
                )
            );
        }
    }

    /** Render the global reader modal markup. */
    public static function render_modal() {
        ?>
        <div id="wrhr-modal" class="wrhr-modal" aria-hidden="true">

            <div class="wrhr-overlay" id="wrhr-modal-overlay"></div>

            <div class="wrhr-modal-content" id="wrhr-modal-content">
                <?php // Header includes title, fullscreen notice and language switcher. ?>
                <div class="wrhr-modal-header">
                    <div class="wrhr-modal-title-wrapper">
                        <h3 class="wrhr-modal-title" id="wrhr-modal-title"></h3>

                        <div class="wrhr-notice-mini">
                            Full screen mode recommended
                        </div>
                    </div>

                    <div class="wrhr-modal-controls">
                        <button class="wrhr-fs-btn" id="wrhr-fs-btn">⤢</button>
                        <button class="wrhr-close" id="wrhr-close">×</button>
                    </div>

                </div>

                <div class="wrhr-reader-container">
                    <div id="wrhr-page-wrapper"></div>
                </div>

                <div id="google_translate_element" class="wrhr-google-dropdown notranslate" translate="no"></div>

                <div class="wrhr-controls wrhr-toolbar notranslate" translate="no">
                    <button id="wrhr-prev">⟨⟨</button>
                    <span id="wrhr-page-info">Page 1 / 1</span>
                    <button id="wrhr-next">⟩⟩</button>
                </div>

                <div id="wrhr-custom-translate">
                    <select id="wrhr-custom-lang">
                        <option value="en">English (Original)</option>
                        <option value="de">German</option>
                        <option value="fr">French</option>
                        <option value="it">Italian</option>
                        <option value="pt">Portuguese</option>
                        <option value="tr">Turkish</option>
                        <option value="ru">Russian</option>
                        <option value="es">Spanish</option>
                        <option value="hi">Hindi</option>
                        <option value="ja">Japanese</option>
                        <option value="zh-CN">Chinese (Simplified)</option>
                        <option value="no">Norwegian</option>
                        <option value="ar">Arabic</option>
                        <option value="nl">Dutch</option>
                        <option value="pl">Polish</option>
                    </select>
                </div>

                <script type="text/javascript">
                function googleTranslateElementInit() {
                  new google.translate.TranslateElement(
                    {
                      pageLanguage: 'en',
                      includedLanguages: 'en,de,fr,it,pt,tr,ru,es,hi,ja,zh-CN,no,ar,nl,pl',
                      autoDisplay: false
                    },
                    'google_translate_element'
                  );
                }
                </script>

                <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

            </div>
        </div>
        <?php
    }
}
add_action( 'wp_footer', array( 'WRHR_Assets', 'render_modal' ) );
