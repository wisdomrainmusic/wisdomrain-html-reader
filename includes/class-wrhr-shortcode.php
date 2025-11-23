<?php
/**
 * Front-end shortcode handler.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRHR_Shortcode {

    /** Register shortcode handler. */
    public static function init() {
        add_shortcode( 'wrhr_reader', array( __CLASS__, 'render_reader' ) );
    }

    /** Render shortcode output. */
    public static function render_reader( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => '',
            ),
            $atts
        );

        $id = sanitize_text_field( $atts['id'] );
        if ( ! $id ) {
            return "<div class='wrhr-error'>Reader ID missing.</div>";
        }

        $reader = WRHR_Readers::get( $id );
        if ( ! $reader ) {
            return "<div class='wrhr-error'>Reader not found.</div>";
        }

        // Enqueue styles + JS stub
        WRHR_Assets::enqueue_frontend();

        ob_start();
        include WRHR_PLUGIN_DIR . 'templates/shortcode-reader.php';
        return ob_get_clean();
    }
}
