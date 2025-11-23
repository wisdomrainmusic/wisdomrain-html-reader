<?php
/**
 * Registry of supported languages for the WRHR translate widget.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lightweight static language registry for the translate widget.
 */
class WRHR_Languages {

    /**
     * Ordered list of supported languages with metadata.
     *
     * @var array<int, array<string, string>>
     */
    private const LANGUAGES = array(
        array(
            'code'        => 'en',
            'label'       => 'English',
            'google_code' => 'en',
            'flag'        => 'en.svg',
        ),
        array(
            'code'        => 'de',
            'label'       => 'German',
            'google_code' => 'de',
            'flag'        => 'de.svg',
        ),
        array(
            'code'        => 'fr',
            'label'       => 'French',
            'google_code' => 'fr',
            'flag'        => 'fr.svg',
        ),
        array(
            'code'        => 'it',
            'label'       => 'Italian',
            'google_code' => 'it',
            'flag'        => 'it.svg',
        ),
        array(
            'code'        => 'pt',
            'label'       => 'Portuguese',
            'google_code' => 'pt',
            'flag'        => 'pt.svg',
        ),
        array(
            'code'        => 'tr',
            'label'       => 'Turkish',
            'google_code' => 'tr',
            'flag'        => 'tr.svg',
        ),
        array(
            'code'        => 'ru',
            'label'       => 'Russian',
            'google_code' => 'ru',
            'flag'        => 'ru.svg',
        ),
        array(
            'code'        => 'es',
            'label'       => 'Spanish',
            'google_code' => 'es',
            'flag'        => 'es.svg',
        ),
        array(
            'code'        => 'hi',
            'label'       => 'Hindi',
            'google_code' => 'hi',
            'flag'        => 'hi.svg',
        ),
        array(
            'code'        => 'ja',
            'label'       => 'Japanese',
            'google_code' => 'ja',
            'flag'        => 'ja.svg',
        ),
        array(
            'code'        => 'zh-cn',
            'label'       => 'Chinese (Simplified)',
            'google_code' => 'zh-CN',
            'flag'        => 'zh-cn.svg',
        ),
        array(
            'code'        => 'no',
            'label'       => 'Norwegian',
            'google_code' => 'no',
            'flag'        => 'no.svg',
        ),
        array(
            'code'        => 'ar',
            'label'       => 'Arabic',
            'google_code' => 'ar',
            'flag'        => 'ar.svg',
        ),
        array(
            'code'        => 'nl',
            'label'       => 'Dutch',
            'google_code' => 'nl',
            'flag'        => 'nl.svg',
        ),
        array(
            'code'        => 'pl',
            'label'       => 'Polish',
            'google_code' => 'pl',
            'flag'        => 'pl.svg',
        ),
    );

    /**
     * Retrieve the ordered language registry.
     *
     * @return array<int, array<string, string>>
     */
    public static function all() {
        return self::get_languages();
    }

    /**
     * Alias for the ordered language registry.
     *
     * @return array<int, array<string, string>>
     */
    public static function get_languages() {
        return self::LANGUAGES;
    }

    /**
     * Base URL for flag assets.
     *
     * @return string
     */
    public static function get_flags_base_url() {
        return trailingslashit( plugin_dir_url( WRHR_PLUGIN_FILE ) . 'assets/flags' );
    }

    /**
     * Provide a simplified language map for script localization.
     *
     * @return array<int, array<string, string>>
     */
    public static function get_js_config() {
        return array_map(
            static function( $lang ) {
                return array(
                    'code'  => $lang['code'],
                    'label' => $lang['label'],
                );
            },
            self::get_languages()
        );
    }

    /**
     * Map languages to a lightweight client payload.
     *
     * @return array<int, array<string, string>>
     */
    public static function as_client_payload() {
        return array_map(
            static function( $lang ) {
                return array(
                    'code'        => $lang['code'],
                    'label'       => $lang['label'],
                    'google_code' => $lang['google_code'],
                    'flag'        => $lang['flag'],
                );
            },
            self::get_languages()
        );
    }
}
