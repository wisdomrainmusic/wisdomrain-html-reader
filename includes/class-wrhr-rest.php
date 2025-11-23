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

        if ( ! is_string( $html ) || '' === trim( $html ) ) {
            return [ 'clean' => '' ];
        }

        if ( class_exists( 'WRHR_Cleaner' ) ) {
            $clean = WRHR_Cleaner::clean_html( $html );
        } else {
            // Fallback: minimal sanitize, ama normalde buraya hiç düşmemeli.
            $clean = wp_kses_post( $html );
        }

        return [ 'clean' => $clean ];
    }
}
