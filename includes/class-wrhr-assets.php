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

        // ----------------------------------------------------
        // Google Translate Loader (critical)
        // ----------------------------------------------------
        wp_enqueue_script(
            'wrhr-google-translate',
            'https://translate.google.com/translate_a/element.js?cb=wrhrGoogleTranslateInit',
            array(),
            null,
            true
        );

        // A hidden container will hold Google's internal combo
        add_action( 'wp_footer', array( __CLASS__, 'render_google_translate_container' ) );

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

    /**
     * Hidden container + JS callback for Google Translate engine.
     */
    public static function render_google_translate_container() {
        ?>
        <div id="wrhr-google-container" class="notranslate" style="display:none;"></div>

        <script>
        function wrhrGoogleTranslateInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,de,fr,it,pt,tr,ru,es,hi,ja,zh-CN,no,ar,nl,pl',
                autoDisplay: false,
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'wrhr-google-container');
        }
        </script>
        <?php
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

                    <div class="wrhr-lang-dropdown notranslate" id="wrhr-lang-dropdown">
                        <button type="button" class="wrhr-lang-toggle notranslate">Language ▼</button>
                        <div class="wrhr-lang-menu notranslate" style="display:none;">
                            <div class="wrhr-lang-option" data-lang="en">English</div>
                            <div class="wrhr-lang-option" data-lang="de">German</div>
                            <div class="wrhr-lang-option" data-lang="fr">French</div>
                            <div class="wrhr-lang-option" data-lang="it">Italian</div>
                            <div class="wrhr-lang-option" data-lang="pt">Portuguese</div>
                            <div class="wrhr-lang-option" data-lang="tr">Turkish</div>
                            <div class="wrhr-lang-option" data-lang="ru">Russian</div>
                            <div class="wrhr-lang-option" data-lang="es">Spanish</div>
                            <div class="wrhr-lang-option" data-lang="hi">Hindi</div>
                            <div class="wrhr-lang-option" data-lang="ja">Japanese</div>
                            <div class="wrhr-lang-option" data-lang="zh-CN">Chinese (Simplified)</div>
                            <div class="wrhr-lang-option" data-lang="no">Norwegian</div>
                            <div class="wrhr-lang-option" data-lang="ar">Arabic</div>
                            <div class="wrhr-lang-option" data-lang="nl">Dutch</div>
                            <div class="wrhr-lang-option" data-lang="pl">Polish</div>
                        </div>
                    </div>

                    <div class="wrhr-modal-controls">
                        <button class="wrhr-fs-btn" id="wrhr-fs-btn">⤢</button>
                        <button class="wrhr-close" id="wrhr-close">×</button>
                    </div>

                    <?php
                    /**
                     * Language switcher (flag-only) for Google Translate.
                     * Only icons are printed; labels are provided via alt + aria-label.
                     * Wrapped with .notranslate so Google does not touch this UI.
                     */
                    if ( class_exists( 'WRHR_Languages' ) ) :
                        $languages = WRHR_Languages::get_languages();
                        ?>
                        <div class="wrhr-lang-switcher notranslate" id="wrhr-lang-switcher" aria-label="Language selector">
                            <?php foreach ( $languages as $lang ) : ?>
                                <button
                                    type="button"
                                    class="wrhr-lang-item"
                                    data-lang="<?php echo esc_attr( $lang['code'] ); ?>"
                                    aria-label="<?php echo esc_attr( $lang['label'] ); ?>">
                                    <img src="<?php echo esc_url( WRHR_URL . 'assets/flags/' . $lang['flag_file'] ); ?>" alt="<?php echo esc_attr( $lang['label'] ); ?>" />
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
