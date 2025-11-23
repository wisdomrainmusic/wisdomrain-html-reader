<?php
/**
 * WisdomRain HTML Reader Loader.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Core loader for registering assets and template handling.
 */
class WRHR_Loader {
    /**
     * Singleton instance.
     *
     * @var WRHR_Loader|null
     */
    private static $instance = null;

    /**
     * Plugin version.
     *
     * @var string
     */
    private $version;

    /**
     * Plugin slug / asset handle.
     *
     * @var string
     */
    private $slug = 'wisdomrain-html-reader';

    /**
     * Initialize the loader.
     *
     * @param string $version Plugin version.
     */
    private function __construct( $version ) {
        $this->version = $version;

        add_action( 'init', array( $this, 'register_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    /**
     * Register autoloader and initialize hooks.
     *
     * @param string $version Plugin version.
     *
     * @return WRHR_Loader
     */
    public static function init( $version ) {
        self::autoload();

        $cleaner_path = WRHR_PLUGIN_DIR . 'includes/class-wrhr-cleaner.php';

        if ( file_exists( $cleaner_path ) ) {
            require_once $cleaner_path;
        }

        if ( null === self::$instance ) {
            self::$instance = new self( $version );
            self::$instance->init_hooks();
        }

        return self::$instance;
    }

    /**
     * Autoload WRHR classes in the includes directory.
     */
    private static function autoload() {
        spl_autoload_register(
            static function ( $class ) {
                if ( 0 !== strpos( $class, 'WRHR_' ) ) {
                    return;
                }

                $file = strtolower( str_replace( 'WRHR_', 'class-wrhr-', $class ) );
                $file = str_replace( '_', '-', $file );

                $path = WRHR_PLUGIN_DIR . 'includes/' . $file . '.php';

                if ( file_exists( $path ) ) {
                    require_once $path;
                }
            }
        );
    }

    /**
     * Initialize admin or public hooks.
     */
    private function init_hooks() {
        add_action( 'init', array( 'WRHR_Shortcode', 'init' ) );
        WRHR_REST::init();

        if ( is_admin() && class_exists( 'WRHR_Admin' ) ) {
            add_action( 'admin_menu', array( 'WRHR_Admin', 'register_menu' ) );
        }
    }

    /**
     * Register front-end assets.
     */
    public function register_assets() {
        $css_url = plugins_url( 'assets/css/reader.css', WRHR_PLUGIN_FILE );
        $js_url  = plugins_url( 'assets/js/reader.js', WRHR_PLUGIN_FILE );

        wp_register_style( $this->slug, $css_url, array(), $this->version );
        wp_register_script( $this->slug, $js_url, array(), $this->version, true );
    }

    /**
     * Register the HTML reader shortcode.
     */
    public function register_shortcode() {
        add_shortcode( 'wisdomrain_reader', array( $this, 'render_reader' ) );
    }

    /**
     * Render the reader output.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public function render_reader( $atts = array() ) {
        $attributes = shortcode_atts(
            array(
                'source' => '',
            ),
            $atts,
            'wisdomrain_reader'
        );

        // Ensure assets load only when the shortcode renders.
        wp_enqueue_style( $this->slug );
        wp_enqueue_script( $this->slug );

        ob_start();
        include WRHR_PLUGIN_DIR . 'templates/index.php';
        return ob_get_clean();
    }
}
