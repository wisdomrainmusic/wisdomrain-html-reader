<?php
/**
 * Reader data model option storage.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Manage WRHR readers stored in the `wrhr_readers` option.
 */
class WRHR_Readers {

    const OPTION_KEY = 'wrhr_readers';

    /** Retrieve all readers */
    public static function get_all() {
        $data = get_option( self::OPTION_KEY, array() );
        return is_array( $data ) ? $data : array();
    }

    /** Get single reader */
    public static function get( $id ) {
        $all = self::get_all();
        return $all[ $id ] ?? null;
    }

    /** Create new reader */
    public static function create( $name, $slug = '' ) {
        $all = self::get_all();

        $id   = 'wrhr_' . wp_generate_uuid4();
        $slug = $slug ? sanitize_title( $slug ) : sanitize_title( $name );

        $all[ $id ] = array(
            'id'    => $id,
            'name'  => sanitize_text_field( $name ),
            'slug'  => $slug,
            'books' => array(),
        );

        update_option( self::OPTION_KEY, $all );
        return $id;
    }

    /** Update reader main fields */
    public static function update_meta( $id, $fields ) {
        $all = self::get_all();
        if ( ! isset( $all[ $id ] ) ) {
            return false;
        }

        foreach ( $fields as $k => $v ) {
            $all[ $id ][ $k ] = sanitize_text_field( $v );
        }

        update_option( self::OPTION_KEY, $all );
        return true;
    }

    /** Delete reader */
    public static function delete( $id ) {
        $all = self::get_all();
        if ( isset( $all[ $id ] ) ) {
            unset( $all[ $id ] );
            update_option( self::OPTION_KEY, $all );
            return true;
        }
        return false;
    }

    public static function update_reader_books( $id, $books ) {
        $all = self::get_all();
        if ( ! isset( $all[ $id ] ) ) {
            return false;
        }

        $clean = array();

        foreach ( $books as $b ) {
            if ( empty( $b['title'] ) && empty( $b['html_url'] ) ) {
                continue;
            }

            $clean[] = array(
                'title'    => sanitize_text_field( $b['title'] ),
                'author'   => sanitize_text_field( $b['author'] ),
                'html_url' => esc_url_raw( $b['html_url'] ),
                'buy_link' => esc_url_raw( $b['buy_link'] ),
            );
        }

        $all[ $id ]['books'] = $clean;
        update_option( self::OPTION_KEY, $all );

        return true;
    }
}
