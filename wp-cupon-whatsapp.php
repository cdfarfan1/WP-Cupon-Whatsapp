<?php
/**
 * Plugin Name: WP Cupón WhatsApp
 * Plugin URI: https://www.pragmaticsolutions.com.ar
 * Description: Plugin para programa de fidelización y canje de cupones por WhatsApp integrado con WooCommerce.
 * Version: 1.5.1
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
define( 'WPCW_VERSION', '1.5.1' );
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

// Include core files (non-WooCommerce dependent)
require_once WPCW_PLUGIN_DIR . 'includes/php8-compat.php'; // PHP 8.x compatibility layer (must be first)
require_once WPCW_PLUGIN_DIR . 'includes/post-types.php';
require_once WPCW_PLUGIN_DIR . 'includes/roles.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-logger.php';
require_once WPCW_PLUGIN_DIR . 'includes/taxonomies.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-installer-fixed.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-database-migrator.php'; // Sistema de migración automática
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-dashboard.php';
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-business-manager.php';
require_once WPCW_PLUGIN_DIR . 'includes/utils.php'; // Carga de funciones de utilidad
require_once WPCW_PLUGIN_DIR . 'includes/managers/class-wpcw-institution-manager.php'; // Carga del nuevo gestor

// WooCommerce-dependent files - Load after WooCommerce is ready
// Moved to wpcw_load_woocommerce_dependencies() function below

// Include admin files
require_once WPCW_PLUGIN_DIR . 'admin/admin-menu.php';
require_once WPCW_PLUGIN_DIR . 'admin/setup-wizard.php'; // Setup wizard - configuración inicial
require_once WPCW_PLUGIN_DIR . 'admin/database-status-page.php'; // Verificación de estado de BD
require_once WPCW_PLUGIN_DIR . 'admin/business-management.php';
require_once WPCW_PLUGIN_DIR . 'admin/coupon-meta-boxes.php';
require_once WPCW_PLUGIN_DIR . 'admin/dashboard-pages.php';
require_once WPCW_PLUGIN_DIR . 'admin/stats-page.php'; // Página de estadísticas - Corregido Error #13
require_once WPCW_PLUGIN_DIR . 'admin/settings-page.php'; // Página de configuración - Corregido Error #14
require_once WPCW_PLUGIN_DIR . 'admin/canjes-page.php'; // Página de canjes - Corregido Error #15
require_once WPCW_PLUGIN_DIR . 'admin/institution-dashboard-page.php'; // Carga del nuevo panel
require_once WPCW_PLUGIN_DIR . 'admin/business-convenios-page.php'; // Carga del panel de convenios para negocios
require_once WPCW_PLUGIN_DIR . 'admin/validate-redemption-page.php'; // Carga de la página de validación de canjes
require_once WPCW_PLUGIN_DIR . 'admin/migration-notice.php'; // Aviso de migración de base de datos

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
 * Load WooCommerce-dependent files
 * Called after WooCommerce is fully loaded
 * 
 * @since 1.5.1
 * @author Sarah Thompson - Fix for WC_Coupon not found error
 */
function wpcw_load_woocommerce_dependencies() {
    // Only load if WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Now it's safe to load files that extend WooCommerce classes
    require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-coupon.php';
    require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-coupon-manager.php';
    require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-redemption-manager.php';
    require_once WPCW_PLUGIN_DIR . 'includes/redemption-handler.php';
    require_once WPCW_PLUGIN_DIR . 'includes/ajax-handlers.php';
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
    // Admin menu is registered in admin/admin-menu.php
    // via wpcw_register_plugin_admin_menu() hook

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
        WPCW_PLUGIN_URL . 'admin/css/admin.css',
        array(),
        WPCW_VERSION
    );

    wp_enqueue_script(
        'wpcw-admin',
        WPCW_PLUGIN_URL . 'admin/js/admin.js',
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
        WPCW_PLUGIN_URL . 'public/css/public.css',
        array(),
        WPCW_VERSION
    );

    wp_enqueue_script(
        'wpcw-public',
        WPCW_PLUGIN_URL . 'public/js/public.js',
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

// El menú administrativo se registra en admin/admin-menu.php
// mediante wpcw_register_plugin_admin_menu() con prioridad 1
// Slug: 'wpcw-main-dashboard' | 7 submenús incluidos
// Legacy redirect implementado para compatibilidad (6 meses)

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

// Las funciones de renderizado se han movido a admin/dashboard-pages.php
// para evitar duplicación de código

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
            
            // Ejecutar migraciones de base de datos automáticamente
            if ( class_exists( 'WPCW_Database_Migrator' ) ) {
                WPCW_Database_Migrator::run();
            }

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
add_action( 'plugins_loaded', 'wpcw_load_woocommerce_dependencies', 20 ); // Load after WooCommerce (priority 20)
add_action( 'init', 'wpcw_init' );
// Admin menu hook is in admin/admin-menu.php (wpcw_register_plugin_admin_menu)
add_action( 'wpcw_clean_old_logs', array( 'WPCW_Logger', 'limpiar_logs_antiguos' ) );
add_action( 'wpcw_create_indexes', array( 'WPCW_Installer_Fixed', 'create_table_indexes' ) ); // Background index creation
register_activation_hook( __FILE__, 'wpcw_activate' );
register_deactivation_hook( __FILE__, 'wpcw_deactivate' );
?>
