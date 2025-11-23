<?php
/**
 * Admin menu registration for WisdomRain HTML Reader.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin functionality for the plugin.
 */
class WRHR_Admin {
    const CAPABILITY = 'manage_options';
    const MENU_SLUG  = 'wrhr-reader';

    /**
     * Register a top-level admin menu.
     */
    public static function register_menu() {
        add_menu_page(
            __( 'WisdomRain Reader', 'wisdomrain-html-reader' ),
            __( 'WR HTML Reader', 'wisdomrain-html-reader' ),
            self::CAPABILITY,
            self::MENU_SLUG,
            array( self::class, 'render_dashboard_page' ),
            'dashicons-book',
            75
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Dashboard', 'wisdomrain-html-reader' ),
            __( 'Dashboard', 'wisdomrain-html-reader' ),
            self::CAPABILITY,
            self::MENU_SLUG,
            array( self::class, 'render_dashboard_page' )
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Manage Readers', 'wisdomrain-html-reader' ),
            __( 'Manage Readers', 'wisdomrain-html-reader' ),
            self::CAPABILITY,
            'wrhr-manage-readers',
            array( self::class, 'render_manage_readers_page' )
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Settings', 'wisdomrain-html-reader' ),
            __( 'Settings', 'wisdomrain-html-reader' ),
            self::CAPABILITY,
            'wrhr-reader-settings',
            array( self::class, 'render_settings_page' )
        );
    }

    /**
     * Render dashboard page.
     */
    public static function render_dashboard_page() {
        self::ensure_capability();
        self::render_template( 'admin-dashboard.php' );
    }

    /**
     * Render manage readers page.
     */
    public static function render_manage_readers_page() {
        self::ensure_capability();
        self::render_template( 'admin-manage-readers.php' );
    }

    /**
     * Render admin settings page placeholder.
     */
    public static function render_settings_page() {
        self::ensure_capability();
        self::render_template( 'admin-settings.php' );
    }

    /**
     * Capability guard.
     */
    private static function ensure_capability() {
        if ( ! current_user_can( self::CAPABILITY ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'wisdomrain-html-reader' ) );
        }
    }

    /**
     * Safely load an admin template.
     *
     * @param string $template_file Template file name.
     */
    private static function render_template( $template_file ) {
        $template_path = WRHR_PLUGIN_DIR . 'templates/' . $template_file;
        $template_path = apply_filters( 'wrhr_admin_template_path', $template_path, $template_file );

        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__( 'Template not found.', 'wisdomrain-html-reader' )
            );
        }
    }
}
