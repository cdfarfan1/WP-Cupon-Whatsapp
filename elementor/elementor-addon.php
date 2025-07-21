<?php
/**
 * Elementor Addon for WP Cupón WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Elementor Addon Class
 *
 * The main class that initiates and runs the Elementor addon.
 *
 * @since 1.1.0
 */
final class WPCW_Elementor_Addon {

	/**
	 * Plugin Version
	 *
	 * @since 1.1.0
	 * @var string The plugin version.
	 */
	const VERSION = '1.1.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.1.0
	 * @var string Minimum Elementor version required to run the addon.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.1.0
	 * @var string Minimum PHP version required to run the addon.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Instance
	 *
	 * @since 1.1.0
	 * @access private
	 * @static
	 * @var WPCW_Elementor_Addon The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.1.0
	 * @access public
	 * @static
	 * @return WPCW_Elementor_Addon An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function i18n() {
		// This is handled by the main plugin file
	}

	/**
	 * On Plugins Loaded
	 *
	 * Checks if Elementor has loaded, and performs some compatibility checks.
	 * If all checks pass, registers the actions.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function on_plugins_loaded() {
		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'init' ] );
		}
	}

	/**
	 * Compatibility Checks
	 *
	 * Checks if the installed version of Elementor meets the addon's minimum requirement.
	 * Checks if the installed PHP version meets the addon's minimum requirement.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return bool True if compatible, False otherwise.
	 */
	public function is_compatible() {
		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return false;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return false;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return false;
		}

		return true;
	}

	/**
	 * Initialize the addon
	 *
	 * Load the plugin classes and initialize the addon.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function init() {
		// Add Plugin actions
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_categories' ] );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'widget_styles' ] );
        // Elementor 3.5.0+ uses elementor/widgets/register hook.
        // For older versions, use elementor/widgets/widgets_registered.
        // The 'register' hook is more appropriate for modern Elementor.
	}

    /**
     * Widget Styles
     *
     * Load styles for the widgets
     *
     * @since 1.1.0
     * @access public
     */
    public function widget_styles() {
        wp_enqueue_style(
            'wpcw-elementor-widgets',
            WPCW_PLUGIN_URL . 'elementor/css/widgets.css',
            [],
            self::VERSION
        );
    }

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		// Define the path to your widgets directory
		$widgets_dir = WPCW_PLUGIN_DIR . 'elementor/widgets/';

		// Include Widget files
		require_once( $widgets_dir . 'widget-cupones-lista.php' );
		require_once( $widgets_dir . 'widget-formulario-adhesion.php' );

		// Register Widgets
		$widgets_manager->register( new \WPCW_Elementor_Cupones_Lista_Widget() );
		$widgets_manager->register( new \WPCW_Elementor_Formulario_Adhesion_Widget() );
	}

    /**
	 * Register Widget Categories
	 *
	 * Adds a custom category to the Elementor widget panel.
	 *
	 * @since 1.1.0
	 * @access public
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function register_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'wpcw-categoria',
			[
				'title' => __( 'WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
				'icon' => 'fa fa-whatsapp', // Or your custom icon class
			]
		);
	}


	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'wp-cupon-whatsapp' ),
			'<strong>' . esc_html__( 'WP Cupón WhatsApp Elementor Addon', 'wp-cupon-whatsapp' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'wp-cupon-whatsapp' ) . '</strong>'
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Minimum Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'wp-cupon-whatsapp' ),
			'<strong>' . esc_html__( 'WP Cupón WhatsApp Elementor Addon', 'wp-cupon-whatsapp' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'wp-cupon-whatsapp' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {
		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Minimum PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'wp-cupon-whatsapp' ),
			'<strong>' . esc_html__( 'WP Cupón WhatsApp Elementor Addon', 'wp-cupon-whatsapp' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'wp-cupon-whatsapp' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}
}

// Instantiate WPCW_Elementor_Addon
WPCW_Elementor_Addon::instance();

// Create widgets directory if it doesn't exist
if ( ! is_dir( WPCW_PLUGIN_DIR . 'elementor/widgets' ) ) {
    mkdir( WPCW_PLUGIN_DIR . 'elementor/widgets', 0755, true );
}
