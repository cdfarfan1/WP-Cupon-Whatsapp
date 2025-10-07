<?php
/**
 * Plugin Name: WP Cupón WhatsApp
 * Plugin URI: https://www.pragmaticsolutions.com.ar
 * Description: Plugin para programa de fidelización y canje de cupones por WhatsApp integrado con WooCommerce.
 * Version: 1.5.0
 * Author: Cristian Farfan, Pragmatic Solutions
 * Author URI: https://www.pragmaticsolutions.com.ar
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-cupon-whatsapp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define constants
define( 'WPCW_VERSION', '1.5.0' );
define( 'WPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp' );
define( 'WPCW_PLUGIN_FILE', __FILE__ );

// Declare WooCommerce compatibility early
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

// Include core files
require_once WPCW_PLUGIN_DIR . 'includes/post-types.php';
require_once WPCW_PLUGIN_DIR . 'includes/roles.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-logger.php';
require_once WPCW_PLUGIN_DIR . 'includes/taxonomies.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-installer-fixed.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-coupon.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-dashboard.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-business-manager.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-coupon-manager.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-redemption-manager.php';
require_once WPCW_PLUGIN_DIR . 'includes/redemption-handler.php';
require_once WPCW_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once WPCW_PLUGIN_DIR . 'includes/utils.php'; // Carga de funciones de utilidad
require_once WPCW_PLUGIN_DIR . 'includes/managers/class-wpcw-institution-manager.php'; // Carga del nuevo gestor

// Include admin files
require_once WPCW_PLUGIN_DIR . 'admin/admin-menu.php';
require_once WPCW_PLUGIN_DIR . 'admin/business-management.php';
require_once WPCW_PLUGIN_DIR . 'admin/coupon-meta-boxes.php';
require_once WPCW_PLUGIN_DIR . 'admin/dashboard-pages.php';
require_once WPCW_PLUGIN_DIR . 'admin/institution-dashboard-page.php'; // Carga del nuevo panel
require_once WPCW_PLUGIN_DIR . 'admin/business-convenios-page.php'; // Carga del panel de convenios para negocios
require_once WPCW_PLUGIN_DIR . 'admin/validate-redemption-page.php'; // Carga de la página de validación de canjes

// Include public files
require_once WPCW_PLUGIN_DIR . 'public/shortcodes.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-shortcodes.php';
require_once WPCW_PLUGIN_DIR . 'public/response-handler.php'; // Carga del manejador de respuestas de convenio

// Include API files (rest-api.php no existe, usando class-wpcw-rest-api.php que ya está cargado)

// Include onboarding system
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-onboarding-manager.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-rest-api.php';

// Include Elementor integration
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-elementor.php';

// Load debug tools only if debug mode is enabled
if ( defined( 'WPCW_DEBUG_MODE' ) && WPCW_DEBUG_MODE ) {
    require_once WPCW_PLUGIN_DIR . 'admin/developer-tools-page.php';
    require_once WPCW_PLUGIN_DIR . 'includes/debug/class-wpcw-seeder.php';
}

/**
 * Initialize plugin functionality
 * Main initialization function following WordPress best practices
 */
function wpcw_init() {
    try {
        // Load plugin text domain for internationalization
        $language_loaded = load_plugin_textdomain(
            'wp-cupon-whatsapp',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );

        if ( ! $language_loaded ) {
            // Log warning if language files couldn't be loaded
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'warning', 'Plugin text domain could not be loaded' );
            }
        }

        // Check critical dependencies
        $dependencies_check = wpcw_check_dependencies();
        if ( ! $dependencies_check['passed'] ) {
            foreach ( $dependencies_check['errors'] as $error ) {
                add_action( 'admin_notices', function() use ( $error ) {
                    echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'WP Cupón WhatsApp:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $error ) . '</p></div>';
                } );
            }
            return;
        }

        // Initialize user roles and capabilities
        if ( function_exists( 'wpcw_add_roles' ) ) {
            wpcw_add_roles();
        }

        // Initialize logging system
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::crear_tabla_log();
            WPCW_Logger::schedule_log_cleanup();
        }

        // Initialize REST API endpoints
        if ( class_exists( 'WPCW_REST_API' ) ) {
            WPCW_REST_API::init();
        }

        // Register custom post types and taxonomies
        if ( function_exists( 'wpcw_register_post_types' ) ) {
            add_action( 'init', 'wpcw_register_post_types', 10 );
        }

        if ( function_exists( 'wpcw_register_taxonomies' ) ) {
            add_action( 'init', 'wpcw_register_taxonomies', 10 );
        }

        // Initialize admin functionality
        if ( is_admin() ) {
            wpcw_init_admin();
        }

        // Initialize public functionality
        if ( ! is_admin() ) {
            wpcw_init_public();
        }

        // AJAX handlers are auto-initialized in ajax-handlers.php

        // Log successful initialization
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'WP Cupón WhatsApp plugin initialized successfully', array(
                'version' => WPCW_VERSION,
                'wp_version' => get_bloginfo( 'version' ),
                'php_version' => PHP_VERSION,
                'woocommerce_active' => class_exists( 'WooCommerce' ),
            ) );
        }

    } catch ( Exception $e ) {
        // Log initialization error
        error_log( 'WPCW Initialization Error: ' . $e->getMessage() );

        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'error', 'Plugin initialization failed: ' . $e->getMessage() );
        }

        // Show user-friendly error message
        add_action( 'admin_notices', function() use ( $e ) {
            echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'WP Cupón WhatsApp:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html__( 'Error during plugin initialization. Please check the error logs.', 'wp-cupon-whatsapp' ) . '</p></div>';
        } );
    }
}

/**
 * Check plugin dependencies
 */
function wpcw_check_dependencies() {
    $errors = array();
    $passed = true;

    // Check WooCommerce
    if ( ! class_exists( 'WooCommerce' ) ) {
        $errors[] = __( 'WooCommerce is required but not active.', 'wp-cupon-whatsapp' );
        $passed = false;
    } else {
        // Check WooCommerce version
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '6.0', '<' ) ) {
            $errors[] = sprintf(
                /* translators: %s: WooCommerce version */
                __( 'WooCommerce version %s or higher is required.', 'wp-cupon-whatsapp' ),
                '6.0'
            );
            $passed = false;
        }
    }

    // Check WordPress version
    if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
        $errors[] = sprintf(
            /* translators: %s: WordPress version */
            __( 'WordPress version %s or higher is required.', 'wp-cupon-whatsapp' ),
            '5.0'
        );
        $passed = false;
    }

    // Check PHP version
    if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
        $errors[] = sprintf(
            /* translators: %s: PHP version */
            __( 'PHP version %s or higher is required.', 'wp-cupon-whatsapp' ),
            '7.4'
        );
        $passed = false;
    }

    return array(
        'passed' => $passed,
        'errors' => $errors,
    );
}

/**
 * Initialize admin functionality
 */
function wpcw_init_admin() {
    // Add admin menu
    if ( function_exists( 'wpcw_register_menu' ) ) {
        add_action( 'admin_menu', 'wpcw_register_menu' );
    }

    // Enqueue admin assets
    add_action( 'admin_enqueue_scripts', 'wpcw_enqueue_admin_assets' );
}

/**
 * Initialize public functionality
 */
function wpcw_init_public() {
    // Enqueue public assets
    add_action( 'wp_enqueue_scripts', 'wpcw_enqueue_public_assets' );

    // Register shortcodes
    if ( class_exists( 'WPCW_Shortcodes' ) ) {
        WPCW_Shortcodes::init();
    }
}

/**
 * Enqueue admin assets
 */
function wpcw_enqueue_admin_assets( $hook ) {
    // Only load on our plugin pages
    if ( strpos( $hook, 'wpcw' ) === false ) {
        return;
    }

    wp_enqueue_style(
        'wpcw-admin',
        plugins_url( 'admin/css/admin.css', dirname( __FILE__ ) ),
        array(),
        WPCW_VERSION
    );

    wp_enqueue_script(
        'wpcw-admin',
        plugins_url( 'admin/js/admin.js', dirname( __FILE__ ) ),
        array( 'jquery' ),
        WPCW_VERSION,
        true
    );

    // Localize script
    wp_localize_script( 'wpcw-admin', 'wpcw_admin', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'wpcw_admin_nonce' ),
        'strings' => array(
            'confirm_delete' => __( '¿Estás seguro de que quieres eliminar este elemento?', 'wp-cupon-whatsapp' ),
            'loading' => __( 'Cargando...', 'wp-cupon-whatsapp' ),
            'error' => __( 'Ha ocurrido un error', 'wp-cupon-whatsapp' ),
        ),
    ) );
}

/**
 * Enqueue public assets
 */
function wpcw_enqueue_public_assets() {
    wp_enqueue_style(
        'wpcw-public',
        plugins_url( 'public/css/public.css', dirname( __FILE__ ) ),
        array(),
        WPCW_VERSION
    );

    wp_enqueue_script(
        'wpcw-public',
        plugins_url( 'public/js/public.js', dirname( __FILE__ ) ),
        array( 'jquery' ),
        WPCW_VERSION,
        true
    );

    // Localize script
    wp_localize_script( 'wpcw-public', 'wpcw_public', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'wpcw_public_nonce' ),
        'strings' => array(
            'loading' => __( 'Cargando...', 'wp-cupon-whatsapp' ),
            'error' => __( 'Ha ocurrido un error', 'wp-cupon-whatsapp' ),
        ),
    ) );
}

/**
 * Register admin menu
 *
 * Menu registration is kept here for WordPress standards
 */
function wpcw_register_menu() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Main menu
    add_menu_page(
        'WP Cupón WhatsApp',
        'WP Cupón WhatsApp',
        'manage_options',
        'wpcw-dashboard',
        'wpcw_render_dashboard',
        'dashicons-tickets-alt',
        25
    );

    // Submenu pages
    add_submenu_page(
        'wpcw-dashboard',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'wpcw-dashboard',
        'wpcw_render_dashboard'
    );

    add_submenu_page(
        'wpcw-dashboard',
        'Configuración',
        'Configuración',
        'manage_options',
        'wpcw-settings',
        'wpcw_render_settings'
    );

    add_submenu_page(
        'wpcw-dashboard',
        'Canjes',
        'Canjes',
        'manage_options',
        'wpcw-canjes',
        'wpcw_render_canjes'
    );

    add_submenu_page(
        'wpcw-dashboard',
        'Estadísticas',
        'Estadísticas',
        'manage_options',
        'wpcw-estadisticas',
        'wpcw_render_estadisticas'
    );
}

/**
 * WooCommerce missing notice
 * Displays an admin notice when WooCommerce is not active
 */
function wpcw_woocommerce_missing_notice() {
    // Only show to users who can manage plugins
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    // Check if WooCommerce is installed but not active
    $woocommerce_installed = false;
    if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
        $woocommerce_installed = true;
    }

    $message = '<strong>' . esc_html__( 'WP Cupón WhatsApp:', 'wp-cupon-whatsapp' ) . '</strong> ';

    if ( $woocommerce_installed ) {
        $message .= sprintf(
            /* translators: %s: WooCommerce activation link */
            wp_kses_post( __( 'WooCommerce is installed but not active. Please <a href="%s">activate WooCommerce</a> to use this plugin.', 'wp-cupon-whatsapp' ) ),
            esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php' ), 'activate-plugin_woocommerce/woocommerce.php' ) )
        );
    } else {
        $message .= sprintf(
            /* translators: %s: WooCommerce installation link */
            wp_kses_post( __( 'WooCommerce is required for this plugin to work. Please <a href="%s">install WooCommerce</a>.', 'wp-cupon-whatsapp' ) ),
            esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) )
        );
    }

    echo '<div class="notice notice-error is-dismissible"><p>' . wp_kses_post( $message ) . '</p></div>';
}

/**
 * Render dashboard page
 * Displays the main plugin dashboard with system information and status
 */
function wpcw_render_dashboard() {
    // Security check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-cupon-whatsapp' ) );
    }

    // Get system information
    $system_info = wpcw_get_system_info();

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . esc_html__( '🎫 WP Cupón WhatsApp', 'wp-cupon-whatsapp' ) . '</h1>';

    // Plugin status notice
    $plugin_status = wpcw_get_plugin_status();
    echo '<div class="notice notice-' . esc_attr( $plugin_status['type'] ) . ' is-dismissible">';
    echo '<p><strong>' . wp_kses_post( $plugin_status['title'] ) . '</strong></p>';
    echo '<p>' . wp_kses_post( $plugin_status['message'] ) . '</p>';
    echo '</div>';

    // Quick stats section
    echo '<div class="wpcw-dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">';

    // Get stats data
    $stats = wpcw_get_dashboard_stats();

    foreach ( $stats as $stat ) {
        echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
        echo '<div class="wpcw-stat-icon" style="font-size: 2em; margin-bottom: 10px;">' . wp_kses_post( $stat['icon'] ) . '</div>';
        echo '<div class="wpcw-stat-number" style="font-size: 2em; font-weight: bold; color: ' . esc_attr( $stat['color'] ) . ';">' . esc_html( $stat['value'] ) . '</div>';
        echo '<div class="wpcw-stat-label" style="color: #666; font-size: 14px;">' . esc_html( $stat['label'] ) . '</div>';
        echo '</div>';
    }

    echo '</div>';

    // System Information
    echo '<div class="wpcw-system-info">';
    echo '<h2>' . esc_html__( 'Información del Sistema', 'wp-cupon-whatsapp' ) . '</h2>';
    echo '<table class="widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . esc_html__( 'Componente', 'wp-cupon-whatsapp' ) . '</th>';
    echo '<th>' . esc_html__( 'Versión', 'wp-cupon-whatsapp' ) . '</th>';
    echo '<th>' . esc_html__( 'Estado', 'wp-cupon-whatsapp' ) . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ( $system_info as $component => $info ) {
        echo '<tr>';
        echo '<td><strong>' . esc_html( $component ) . '</strong></td>';
        echo '<td>' . esc_html( $info['version'] ) . '</td>';
        echo '<td>' . wp_kses_post( $info['status'] ) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    // Features overview
    echo '<div class="wpcw-features-overview">';
    echo '<h2>' . esc_html__( 'Funcionalidades del Plugin', 'wp-cupon-whatsapp' ) . '</h2>';
    echo '<div class="wpcw-features-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">';

    $features = wpcw_get_features_list();
    foreach ( $features as $feature ) {
        echo '<div class="wpcw-feature-card" style="background: #fff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 20px;">';
        echo '<h3 style="margin-top: 0; color: #23282d;">' . esc_html( $feature['title'] ) . '</h3>';
        echo '<p style="color: #666; margin-bottom: 15px;">' . esc_html( $feature['description'] ) . '</p>';
        echo '<div class="wpcw-feature-status">';
        echo '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ';
        echo '<span style="color: #46b450; font-weight: 600;">' . esc_html__( 'Activo', 'wp-cupon-whatsapp' ) . '</span>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';

    echo '</div>';
}

/**
 * Get system information
 */
function wpcw_get_system_info() {
    return array(
        __( 'WP Cupón WhatsApp', 'wp-cupon-whatsapp' ) => array(
            'version' => WPCW_VERSION,
            'status' => '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Activo', 'wp-cupon-whatsapp' ),
        ),
        __( 'WordPress', 'wp-cupon-whatsapp' ) => array(
            'version' => get_bloginfo( 'version' ),
            'status' => version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Compatible', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-warning" style="color: #f56e28;"></span> ' . __( 'Actualización recomendada', 'wp-cupon-whatsapp' ),
        ),
        __( 'WooCommerce', 'wp-cupon-whatsapp' ) => array(
            'version' => defined( 'WC_VERSION' ) ? WC_VERSION : __( 'No instalado', 'wp-cupon-whatsapp' ),
            'status' => class_exists( 'WooCommerce' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Activo', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Requerido', 'wp-cupon-whatsapp' ),
        ),
        __( 'PHP', 'wp-cupon-whatsapp' ) => array(
            'version' => PHP_VERSION,
            'status' => version_compare( PHP_VERSION, '7.4', '>=' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Compatible', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Versión insuficiente', 'wp-cupon-whatsapp' ),
        ),
        __( 'MySQL', 'wp-cupon-whatsapp' ) => array(
            'version' => wpcw_get_mysql_version(),
            'status' => version_compare( wpcw_get_mysql_version(), '5.6', '>=' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Compatible', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-warning" style="color: #f56e28;"></span> ' . __( 'Actualización recomendada', 'wp-cupon-whatsapp' ),
        ),
    );
}

/**
 * Get MySQL version
 */
function wpcw_get_mysql_version() {
    global $wpdb;
    try {
        return $wpdb->get_var( 'SELECT VERSION()' );
    } catch ( Exception $e ) {
        return __( 'Desconocida', 'wp-cupon-whatsapp' );
    }
}

/**
 * Get plugin status
 */
function wpcw_get_plugin_status() {
    $errors = array();

    // Check critical dependencies
    if ( ! class_exists( 'WooCommerce' ) ) {
        $errors[] = 'woocommerce_missing';
    }

    if ( ! function_exists( 'wp_get_current_user' ) ) {
        $errors[] = 'wordpress_core_missing';
    }

    if ( empty( $errors ) ) {
        return array(
            'type' => 'success',
            'title' => __( '✅ Plugin funcionando correctamente', 'wp-cupon-whatsapp' ),
            'message' => __( 'Todas las dependencias están activas y el plugin está listo para usar.', 'wp-cupon-whatsapp' ),
        );
    } else {
        return array(
            'type' => 'warning',
            'title' => __( '⚠️ Plugin con problemas de configuración', 'wp-cupon-whatsapp' ),
            'message' => __( 'Algunas dependencias no están disponibles. Revisa la información del sistema para más detalles.', 'wp-cupon-whatsapp' ),
        );
    }
}

/**
 * Get dashboard statistics
 */
function wpcw_get_dashboard_stats() {
    // Get basic stats - in a real implementation, these would come from the database
    return array(
        array(
            'icon' => '🎫',
            'value' => '0',
            'label' => __( 'Cupones Activos', 'wp-cupon-whatsapp' ),
            'color' => '#2271b1',
        ),
        array(
            'icon' => '🏪',
            'value' => '0',
            'label' => __( 'Comercios Registrados', 'wp-cupon-whatsapp' ),
            'color' => '#46b450',
        ),
        array(
            'icon' => '📱',
            'value' => '0',
            'label' => __( 'Canjes Realizados', 'wp-cupon-whatsapp' ),
            'color' => '#00a32a',
        ),
        array(
            'icon' => '👥',
            'value' => '0',
            'label' => __( 'Usuarios Activos', 'wp-cupon-whatsapp' ),
            'color' => '#d63638',
        ),
    );
}

/**
 * Get features list
 */
function wpcw_get_features_list() {
    return array(
        array(
            'title' => __( 'Sistema de Cupones', 'wp-cupon-whatsapp' ),
            'description' => __( 'Gestión completa de cupones de fidelización y promocionales integrados con WooCommerce.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Integración WhatsApp', 'wp-cupon-whatsapp' ),
            'description' => __( 'Canje de cupones directamente a través de WhatsApp con confirmación automática.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Panel de Administración', 'wp-cupon-whatsapp' ),
            'description' => __( 'Interfaz completa para gestionar comercios, cupones y canjes con reportes detallados.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'APIs REST', 'wp-cupon-whatsapp' ),
            'description' => __( 'APIs completas para integración con sistemas externos y aplicaciones móviles.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Shortcodes', 'wp-cupon-whatsapp' ),
            'description' => __( 'Shortcodes para integrar formularios y listados en cualquier página de WordPress.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Elementor Integration', 'wp-cupon-whatsapp' ),
            'description' => __( 'Widgets drag-and-drop para Elementor con controles completos de personalización.', 'wp-cupon-whatsapp' ),
        ),
    );
}

/**
 * Render settings page
 */
function wpcw_render_settings() {
    echo '<div class="wrap">';
    echo '<h1>⚙️ Configuración - WP Cupón WhatsApp</h1>';
    
    echo '<div class="notice notice-info">';
    echo '<p><strong>Configuración del Plugin</strong></p>';
    echo '<p>Aquí puedes configurar las opciones principales del plugin.</p>';
    echo '</div>';
    
    echo '<form method="post" action="options.php">';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row">WhatsApp Business API</th>';
    echo '<td><input type="text" name="wpcw_whatsapp_api" value="" class="regular-text" placeholder="Token de API de WhatsApp Business" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Número de WhatsApp</th>';
    echo '<td><input type="text" name="wpcw_whatsapp_number" value="" class="regular-text" placeholder="+5491123456789" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Mensaje de Cupón</th>';
    echo '<td><textarea name="wpcw_coupon_message" rows="4" cols="50" placeholder="Tu cupón de descuento es: {coupon_code}">Tu cupón de descuento es: {coupon_code}</textarea></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Configuración" /></p>';
    echo '</form>';
    
    echo '</div>';
}

/**
 * Render canjes page
 */
function wpcw_render_canjes() {
    echo '<div class="wrap">';
    echo '<h1>🎫 Canjes de Cupones</h1>';
    
    echo '<div class="notice notice-info">';
    echo '<p><strong>Historial de Canjes</strong></p>';
    echo '<p>Aquí puedes ver todos los canjes de cupones realizados.</p>';
    echo '</div>';
    
    // Simular datos de canjes
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Usuario</th>';
    echo '<th>Código de Cupón</th>';
    echo '<th>Fecha de Canje</th>';
    echo '<th>Estado</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>1</td>';
    echo '<td>Usuario Demo</td>';
    echo '<td>DESCUENTO20</td>';
    echo '<td>' . date('Y-m-d H:i:s') . '</td>';
    echo '<td><span class="dashicons dashicons-yes-alt" style="color: green;"></span> Canjeado</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="5" style="text-align: center; color: #666;">No hay canjes registrados aún</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    
    echo '</div>';
}

/**
 * Render estadisticas page
 */
function wpcw_render_estadisticas() {
    echo '<div class="wrap">';
    echo '<h1>📊 Estadísticas - WP Cupón WhatsApp</h1>';
    
    echo '<div class="notice notice-info">';
    echo '<p><strong>Estadísticas del Plugin</strong></p>';
    echo '<p>Aquí puedes ver las estadísticas de uso del plugin.</p>';
    echo '</div>';
    
    echo '<div class="wpcw-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">';
    
    // Tarjeta de cupones generados
    echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #1d2327;">Cupones Generados</h3>';
    echo '<div style="font-size: 2em; font-weight: bold; color: #2271b1;">0</div>';
    echo '<p style="margin: 5px 0 0 0; color: #666;">Total de cupones creados</p>';
    echo '</div>';
    
    // Tarjeta de canjes realizados
    echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #1d2327;">Canjes Realizados</h3>';
    echo '<div style="font-size: 2em; font-weight: bold; color: #00a32a;">0</div>';
    echo '<p style="margin: 5px 0 0 0; color: #666;">Cupones canjeados</p>';
    echo '</div>';
    
    // Tarjeta de usuarios activos
    echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #1d2327;">Usuarios Activos</h3>';
    echo '<div style="font-size: 2em; font-weight: bold; color: #d63638;">0</div>';
    echo '<p style="margin: 5px 0 0 0; color: #666;">Usuarios que han usado cupones</p>';
    echo '</div>';
    
    echo '</div>';
    
    echo '</div>';
}

/**
 * Register admin menu
 */
function wpcw_register_menu() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Menú principal
    add_menu_page(
        'WP Cupón WhatsApp',
        'WP Cupón WhatsApp',
        'manage_options',
        'wpcw-dashboard',
        'wpcw_render_dashboard',
        'dashicons-tickets-alt',
        25
    );
    
    // Subpáginas
    add_submenu_page(
        'wpcw-dashboard',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'wpcw-dashboard',
        'wpcw_render_dashboard'
    );
    
    add_submenu_page(
        'wpcw-dashboard',
        'Configuración',
        'Configuración',
        'manage_options',
        'wpcw-settings',
        'wpcw_render_settings'
    );
    
    add_submenu_page(
        'wpcw-dashboard',
        'Canjes',
        'Canjes',
        'manage_options',
        'wpcw-canjes',
        'wpcw_render_canjes'
    );
    
    add_submenu_page(
        'wpcw-dashboard',
        'Estadísticas',
        'Estadísticas',
        'manage_options',
        'wpcw-estadisticas',
        'wpcw_render_estadisticas'
    );
}

/**
 * Plugin activation hook
 * Handles plugin activation tasks following WordPress best practices
 */
function wpcw_activate() {
    try {
        // Log activation start
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Plugin activation started', array(
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
            ) );
        }

        // Verify WordPress environment
        if ( ! defined( 'ABSPATH' ) ) {
            throw new Exception( 'WordPress environment not detected' );
        }

        // Check PHP version compatibility
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die(
                sprintf(
                    /* translators: %s: PHP version */
                    __( 'WP Cupón WhatsApp requires PHP version 7.4 or higher. You are running version %s. Please upgrade PHP and try again.', 'wp-cupon-whatsapp' ),
                    PHP_VERSION
                )
            );
        }

        // Check WordPress version compatibility
        if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die(
                sprintf(
                    /* translators: %s: WordPress version */
                    __( 'WP Cupón WhatsApp requires WordPress version 5.0 or higher. You are running version %s. Please upgrade WordPress and try again.', 'wp-cupon-whatsapp' ),
                    get_bloginfo( 'version' )
                )
            );
        }

        // Log before installer
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Starting installation checks', array(
                'memory_usage' => memory_get_usage(true),
            ) );
        }

        // Use the installer class for proper setup
        if ( class_exists( 'WPCW_Installer_Fixed' ) ) {
            $installer_result = WPCW_Installer_Fixed::run_installation_checks();

            if ( isset( $installer_result['table_creation'] ) && ! $installer_result['table_creation'] ) {
                error_log( 'WPCW: Database table creation failed during activation' );
            }

            if ( isset( $installer_result['settings_initialization'] ) && ! $installer_result['settings_initialization'] ) {
                error_log( 'WPCW: Settings initialization failed during activation' );
            }

            // Log installer results
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'info', 'Installation checks completed', array(
                    'results' => $installer_result,
                    'memory_usage' => memory_get_usage(true),
                ) );
            }
        } else {
            // Fallback to basic activation if installer class is not available
            wpcw_fallback_activation();
        }

        // Log before setting options
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Setting plugin options', array(
                'memory_usage' => memory_get_usage(true),
            ) );
        }

        // Set plugin version
        update_option( 'wpcw_version', WPCW_VERSION );
        update_option( 'wpcw_activated_at', current_time( 'mysql' ) );

        // Clear any cached data
        wp_cache_flush();

        // Log successful activation
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Plugin activated successfully', array(
                'version' => WPCW_VERSION,
                'wp_version' => get_bloginfo( 'version' ),
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
            ) );
        }

    } catch ( Exception $e ) {
        // Log the error
        error_log( 'WPCW Activation Error: ' . $e->getMessage() );

        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'error', 'Plugin activation failed: ' . $e->getMessage(), array(
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
            ) );
        }

        // Deactivate the plugin on critical errors
        deactivate_plugins( plugin_basename( __FILE__ ) );

        wp_die(
            sprintf(
                /* translators: %s: error message */
                __( 'WP Cupón WhatsApp could not be activated due to an error: %s', 'wp-cupon-whatsapp' ),
                esc_html( $e->getMessage() )
            )
        );
    }
}

/**
 * Fallback activation function
 * Used when the main installer class is not available
 */
function wpcw_fallback_activation() {
    global $wpdb;

    // Create database tables
    $charset_collate = $wpdb->get_charset_collate();

    // Create canjes table
    $table_name = $wpdb->prefix . 'wpcw_canjes';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        coupon_code varchar(100) NOT NULL,
        business_id bigint(20) NOT NULL,
        redeemed_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Create user profiles table for onboarding
    WPCW_Onboarding_Manager::create_user_profiles_table();

    // Initialize basic settings
    add_option( 'wpcw_auto_create_pages', true );
    add_option( 'wpcw_debug_mode', false );
}

/**
 * Plugin deactivation hook
 * Handles plugin deactivation tasks following WordPress best practices
 */
function wpcw_deactivate() {
    try {
        // Log deactivation start
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Plugin deactivation started' );
        }

        // Remove custom roles
        if ( function_exists( 'wpcw_remove_roles' ) ) {
            wpcw_remove_roles();
        }

        // Unschedule cron jobs
        if ( class_exists( 'WPCW_Logger' ) && method_exists( 'WPCW_Logger', 'unschedule_log_cleanup' ) ) {
            WPCW_Logger::unschedule_log_cleanup();
        }

        // Clear scheduled events
        $scheduled_events = array(
            'wpcw_clean_old_logs',
            'wpcw_daily_maintenance',
            'wpcw_weekly_reports',
        );

        foreach ( $scheduled_events as $event ) {
            $timestamp = wp_next_scheduled( $event );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $event );
            }
        }

        // Clear any cached data
        wp_cache_flush();

        // Clear rewrite rules if custom post types were registered
        if ( function_exists( 'flush_rewrite_rules' ) ) {
            flush_rewrite_rules();
        }

        // Log successful deactivation
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Plugin deactivated successfully', array(
                'version' => WPCW_VERSION,
                'deactivated_at' => current_time( 'mysql' ),
            ) );
        }

    } catch ( Exception $e ) {
        // Log deactivation error
        error_log( 'WPCW Deactivation Error: ' . $e->getMessage() );

        // Still log if possible
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'error', 'Plugin deactivation error: ' . $e->getMessage() );
        }
    }
}

// Hooks
add_action( 'init', 'wpcw_init' );
add_action( 'admin_menu', 'wpcw_register_menu' );
add_action( 'wpcw_clean_old_logs', array( 'WPCW_Logger', 'limpiar_logs_antiguos' ) );
register_activation_hook( __FILE__, 'wpcw_activate' );
register_deactivation_hook( __FILE__, 'wpcw_deactivate' );
?>
