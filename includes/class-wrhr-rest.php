<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WRHR_REST {

    public static function init() {
        add_action(
            'rest_api_init',
            function () {
                register_rest_route(
                    'wrhr/v1',
                    '/clean',
                    [
                        'methods'             => 'POST',
                        'callback'            => [ __CLASS__, 'clean_html' ],
                        'permission_callback' => '__return_true',
                    ]
                );
            }
        );
    }

    public static function clean_html( WP_REST_Request $req ) {
        $html = $req->get_param( 'html' );
        if ( ! $html ) {
            return [ 'error' => 'empty html' ];
        }

        // Remove Word garbage.
        $html = preg_replace( '/class="Mso.*?"/i', '', $html );
        $html = preg_replace( '/mso-[^:]+:[^;"]+;?/i', '', $html );
        $html = preg_replace( '/<span[^>]*?>/i', '', $html );
        $html = preg_replace( '/<\/span>/i', '', $html );

        // Allow only safe tags.
        $allowed = [
            'p'      => [],
            'h2'     => [],
            'h3'     => [],
            'h4'     => [],
            'ul'     => [],
            'ol'     => [],
            'li'     => [],
            'strong' => [],
            'em'     => [],
            'b'      => [],
            'i'      => [],
        ];

        $html = wp_kses( $html, $allowed );

        return [ 'clean' => $html ];
    }
}
