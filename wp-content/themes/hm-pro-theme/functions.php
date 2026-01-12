<?php
/**
 * HM Pro Theme functions and definitions
 *
 * @package HMPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HMPRO_THEME_VERSION', '0.1.0' );
define( 'HMPRO_THEME_PATH', get_template_directory() );
define( 'HMPRO_THEME_URL', get_template_directory_uri() );

// Core includes (kept minimal for Commit 001).
$hmpro_includes = array(
	'/inc/core/setup.php',
	'/inc/core/enqueue.php',
);

foreach ( $hmpro_includes as $file ) {
	$path = HMPRO_THEME_PATH . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}
