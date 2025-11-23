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
        add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) );

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
            null,
            __( 'Edit Books', 'wisdomrain-html-reader' ),
            __( 'Edit Books', 'wisdomrain-html-reader' ),
            self::CAPABILITY,
            'wrhr-reader-edit',
            array( self::class, 'render_reader_edit_page' )
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
     * Enqueue admin assets on plugin pages.
     *
     * @param string $hook Hook suffix for the current admin page.
     */
    public static function enqueue_assets( $hook ) {
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

        if ( ! $page ) {
            return;
        }

        $allowed_pages = array(
            self::MENU_SLUG,
            'wrhr-manage-readers',
            'wrhr-reader-edit',
            'wrhr-reader-settings',
        );

        if ( ! in_array( $page, $allowed_pages, true ) ) {
            return;
        }

        wp_enqueue_style(
            'wrhr-admin',
            plugins_url( 'assets/css/wrhr-style.css', WRHR_PLUGIN_FILE ),
            array(),
            WRHR_VERSION
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

        $notices = array();

        if ( ! empty( $_POST['wrhr_create_reader'] ) ) {
            check_admin_referer( 'wrhr_create_reader' );

            $name = isset( $_POST['wrhr_reader_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wrhr_reader_name'] ) ) : '';
            $slug = isset( $_POST['wrhr_reader_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['wrhr_reader_slug'] ) ) : '';

            if ( $name ) {
                WRHR_Readers::create( $name, $slug );
                $notices[] = array(
                    'type'    => 'success',
                    'message' => __( 'Reader created successfully.', 'wisdomrain-html-reader' ),
                );
            } else {
                $notices[] = array(
                    'type'    => 'error',
                    'message' => __( 'Reader name is required.', 'wisdomrain-html-reader' ),
                );
            }
        }

        if ( ! empty( $_GET['delete'] ) ) {
            $delete_id = sanitize_text_field( wp_unslash( $_GET['delete'] ) );
            $nonce     = isset( $_GET['_wpnonce'] ) ? wp_unslash( $_GET['_wpnonce'] ) : '';

            if ( wp_verify_nonce( $nonce, 'wrhr_delete_reader_' . $delete_id ) ) {
                if ( WRHR_Readers::delete( $delete_id ) ) {
                    $notices[] = array(
                        'type'    => 'success',
                        'message' => __( 'Reader deleted.', 'wisdomrain-html-reader' ),
                    );
                } else {
                    $notices[] = array(
                        'type'    => 'error',
                        'message' => __( 'Reader not found.', 'wisdomrain-html-reader' ),
                    );
                }
            } else {
                $notices[] = array(
                    'type'    => 'error',
                    'message' => __( 'Security check failed. Please try again.', 'wisdomrain-html-reader' ),
                );
            }
        }

        $readers       = WRHR_Readers::get_all();
        $template_file = 'admin-readers-list.php';
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

    /**
     * Render reader edit page.
     */
    public static function render_reader_edit_page() {
        self::ensure_capability();

        $id     = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
        $reader = WRHR_Readers::get( $id );

        if ( ! $reader ) {
            echo '<h1>Reader not found.</h1>';
            return;
        }

        $notice = '';

        if ( ! empty( $_POST['wrhr_save_books'] ) ) {
            check_admin_referer( 'wrhr_save_books' );

            $titles   = isset( $_POST['wrhr_title'] ) ? (array) wp_unslash( $_POST['wrhr_title'] ) : array();
            $authors  = isset( $_POST['wrhr_author'] ) ? (array) wp_unslash( $_POST['wrhr_author'] ) : array();
            $urls     = isset( $_POST['wrhr_html_url'] ) ? (array) wp_unslash( $_POST['wrhr_html_url'] ) : array();
            $buylinks = isset( $_POST['wrhr_buy_link'] ) ? (array) wp_unslash( $_POST['wrhr_buy_link'] ) : array();

            $books = array();
            foreach ( $titles as $i => $t ) {
                $books[] = array(
                    'title'    => $t,
                    'author'   => isset( $authors[ $i ] ) ? $authors[ $i ] : '',
                    'html_url' => isset( $urls[ $i ] ) ? $urls[ $i ] : '',
                    'buy_link' => isset( $buylinks[ $i ] ) ? $buylinks[ $i ] : '',
                );
            }

            WRHR_Readers::update_reader_books( $id, $books );
            $notice = __( 'Books updated successfully.', 'wisdomrain-html-reader' );

            $reader = WRHR_Readers::get( $id ); // refresh
        }

        $template_file = 'admin-reader-edit.php';
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
