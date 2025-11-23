<?php
/**
 * Uninstall handler for WisdomRain HTML Reader.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Options that may be stored by future versions of the plugin.
$wrhr_options = array(
    'wrhr_readers',
    'wrhr_settings',
);

// Delete options for the current site.
foreach ( $wrhr_options as $option ) {
    delete_option( $option );
    delete_site_option( $option );
}

// Ensure multisite installs clean up per-site options.
if ( is_multisite() ) {
    $site_ids = get_sites( array( 'fields' => 'ids' ) );

    foreach ( $site_ids as $site_id ) {
        switch_to_blog( $site_id );

        foreach ( $wrhr_options as $option ) {
            delete_option( $option );
        }

        restore_current_blog();
    }
}
