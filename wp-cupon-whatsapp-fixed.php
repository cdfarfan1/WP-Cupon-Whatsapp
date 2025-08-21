<?php
/**
 * Plugin Name: WP Cupón WhatsApp (Versión Corregida)
 * Plugin URI: https://github.com/tu-usuario/wp-cupon-whatsapp
 * Description: Sistema completo de cupones con integración WhatsApp, WooCommerce y Elementor. Permite gestionar cupones institucionales y comerciales con canjes automáticos. Versión corregida para evitar errores fatales durante la activación.
 * Version: 1.4.0
 * Author: Tu Nombre
 * Author URI: https://tu-sitio.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-cupon-whatsapp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin constants
 */
define( 'WPCW_VERSION', '1.4.0' );
define( 'WPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp' );
define( 'WPCW_PLUGIN_FILE', __FILE__ );

// Definir tabla de canjes de forma segura
if ( ! defined( 'WPCW_CANJES_TABLE_NAME' ) ) {
    global $wpdb;
    if ( isset( $wpdb ) && is_object( $wpdb ) && property_exists( $wpdb, 'prefix' ) ) {
        define( 'WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes' );
    } else {
        // Fallback si $wpdb no está disponible aún
        define( 'WPCW_CANJES_TABLE_NAME', 'wp_wpcw_canjes' );
    }
}

/**
 * Initialize plugin functionality
 */
function wpcw_init() {
    // Register post types
    if (function_exists('wpcw_register_post_types')) {
        wpcw_register_post_types();
    }
    
    // Register taxonomies
    if (function_exists('wpcw_register_taxonomies')) {
        wpcw_register_taxonomies();
    }
    
    // Initialize roles
    if (function_exists('wpcw_add_roles')) {
        wpcw_add_roles();
    }
}
add_action( 'init', 'wpcw_init' );

/**
 * Initialize taxonomies
 */
function wpcw_init_taxonomies() {
    if (function_exists('wpcw_register_taxonomies')) {
        wpcw_register_taxonomies();
    }
}
add_action( 'init', 'wpcw_init_taxonomies', 5 );

/**
 * Initialize roles
 */
function wpcw_init_roles() {
    if (function_exists('wpcw_add_roles')) {
        wpcw_add_roles();
    }
}
add_action( 'init', 'wpcw_init_roles', 10 );

/**
 * Load plugin text domain
 */
function wpcw_load_textdomain() {
    load_plugin_textdomain( 'wp-cupon-whatsapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wpcw_load_textdomain' );

/**
 * Check plugin dependencies
 */
function wpcw_check_dependencies() {
    $errors = array();
    
    // Check PHP version
    if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
        $errors[] = sprintf(
            __( 'WP Cupón WhatsApp requiere PHP 7.4 o superior. Versión actual: %s', 'wp-cupon-whatsapp' ),
            PHP_VERSION
        );
    }
    
    // Check if WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) ) {
        $errors[] = __( 'WP Cupón WhatsApp requiere WooCommerce para funcionar correctamente.', 'wp-cupon-whatsapp' );
    } elseif ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '5.0', '<' ) ) {
        $errors[] = sprintf(
            __( 'WP Cupón WhatsApp requiere WooCommerce 5.0 o superior. Versión actual: %s', 'wp-cupon-whatsapp' ),
            WC_VERSION
        );
    }
    
    // Check if Elementor is active (optional but recommended)
    if ( ! did_action( 'elementor/loaded' ) ) {
        $errors[] = __( 'Se recomienda tener Elementor instalado para aprovechar todas las funcionalidades.', 'wp-cupon-whatsapp' );
    } elseif ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.0', '<' ) ) {
        $errors[] = sprintf(
            __( 'Se recomienda Elementor 3.0 o superior para compatibilidad completa. Versión actual: %s', 'wp-cupon-whatsapp' ),
            ELEMENTOR_VERSION
        );
    }
    
    return $errors;
}

/**
 * Display admin notices for dependency errors
 */
function wpcw_admin_notices() {
    $errors = wpcw_check_dependencies();
    
    if ( ! empty( $errors ) ) {
        foreach ( $errors as $error ) {
            echo '<div class="error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
        }
    }
}
add_action( 'admin_notices', 'wpcw_admin_notices' );

/**
 * Load core files
 */
function wpcw_load_core_files() {
    $dependency_errors = wpcw_check_dependencies();
    
    // Allow partial loading even with dependency errors to show admin notices
    if ( ! empty( $dependency_errors ) && ! is_admin() ) {
        return;
    }
    
    // Check WordPress version
    if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>' . esc_html__( 'WP Cupón WhatsApp requiere WordPress 5.0 o superior.', 'wp-cupon-whatsapp' ) . '</p></div>';
        });
        return;
    }
    
    // Load core files
    $core_files = array(
        'includes/class-wpcw-installer.php',
        'includes/class-wpcw-logger.php',
        'includes/class-wpcw-messages.php',
        'includes/class-wpcw-mongodb.php',
        'includes/post-types.php',
        'includes/taxonomies.php',
        'includes/roles.php',
        'includes/email-verification.php',
        'includes/redemption-handler.php',
        'includes/validation-enhanced.php',
    );
    
    foreach ( $core_files as $file ) {
        $file_path = WPCW_PLUGIN_DIR . $file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}
add_action( 'plugins_loaded', 'wpcw_load_core_files', 5 );

/**
 * Enqueue admin scripts and styles
 */
function wpcw_enqueue_admin_scripts( $hook ) {
    // Only load on plugin pages
    if ( strpos( $hook, 'wpcw' ) === false && ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
        return;
    }
    
    wp_enqueue_style( 'wpcw-admin', WPCW_PLUGIN_URL . 'admin/css/admin.css', array(), WPCW_VERSION );
    wp_enqueue_script( 'wpcw-admin', WPCW_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), WPCW_VERSION, true );
    
    wp_localize_script( 'wpcw-admin', 'wpcw_admin', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'wpcw_admin_nonce' ),
        'strings' => array(
            'confirm_delete' => __( '¿Estás seguro de que quieres eliminar este elemento?', 'wp-cupon-whatsapp' ),
            'processing' => __( 'Procesando...', 'wp-cupon-whatsapp' ),
        )
    ));
}
add_action( 'admin_enqueue_scripts', 'wpcw_enqueue_admin_scripts' );

/**
 * Load admin functionality
 */
function wpcw_load_admin() {
    if ( ! is_admin() ) {
        return;
    }
    
    $admin_files = array(
        'admin/admin-menu.php',
        'admin/settings-page.php',
        'admin/canjes-page.php',
        'admin/stats-page.php',
        'admin/roles-page.php',
        'admin/meta-boxes.php',
    );
    
    foreach ( $admin_files as $file ) {
        $file_path = WPCW_PLUGIN_DIR . $file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}
add_action( 'plugins_loaded', 'wpcw_load_admin', 10 );

/**
 * Load public functionality
 */
function wpcw_load_public() {
    if ( is_admin() ) {
        return;
    }
    
    $public_files = array(
        'public/shortcodes.php',
        'public/my-account-endpoints.php',
    );
    
    foreach ( $public_files as $file ) {
        $file_path = WPCW_PLUGIN_DIR . $file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}
add_action( 'plugins_loaded', 'wpcw_load_public', 10 );

/**
 * Plugin activation hook with error handling
 */
function wpcw_activate_plugin() {
    try {
        // Check dependencies first
        $dependency_errors = wpcw_check_dependencies();
        
        if ( ! empty( $dependency_errors ) ) {
            $error_message = implode( '\n', $dependency_errors );
            wp_die( 
                esc_html( $error_message ),
                esc_html__( 'Error de Dependencias - WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
                array( 'back_link' => true )
            );
        }
        
        // Load installer class
        $installer_file = WPCW_PLUGIN_DIR . 'includes/class-wpcw-installer.php';
        if ( ! file_exists( $installer_file ) ) {
            wp_die( 
                esc_html__( 'Error: No se pudo encontrar el archivo de instalación del plugin.', 'wp-cupon-whatsapp' ),
                esc_html__( 'Error de Instalación - WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
                array( 'back_link' => true )
            );
        }
        
        require_once $installer_file;
        
        if ( ! class_exists( 'WPCW_Installer' ) ) {
            wp_die( 
                esc_html__( 'Error: No se pudo cargar la clase de instalación del plugin.', 'wp-cupon-whatsapp' ),
                esc_html__( 'Error de Instalación - WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
                array( 'back_link' => true )
            );
        }
        
        // Load required files for activation
        $required_files = array(
            'includes/post-types.php',
            'includes/taxonomies.php',
            'includes/roles.php',
        );
        
        foreach ( $required_files as $file ) {
            $file_path = WPCW_PLUGIN_DIR . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
        
        // Initialize plugin settings
        if ( method_exists( 'WPCW_Installer', 'init_settings' ) ) {
            WPCW_Installer::init_settings();
        }
        
        // Create canjes table with error handling
        if ( method_exists( 'WPCW_Installer', 'create_canjes_table' ) ) {
            $table_created = WPCW_Installer::create_canjes_table();
            if ( ! $table_created ) {
                // Log error but don't stop activation
                error_log( 'WP Cupón WhatsApp: No se pudo crear la tabla de canjes durante la activación.' );
            }
        }
        
        // Register CPTs
        if ( function_exists( 'wpcw_register_post_types' ) ) {
            wpcw_register_post_types();
        }
        
        // Register Taxonomies
        if ( function_exists( 'wpcw_register_taxonomies' ) ) {
            wpcw_register_taxonomies();
        }
        
        // Add User Roles
        if ( function_exists( 'wpcw_add_roles' ) ) {
            wpcw_add_roles();
        }
        
        // Create plugin pages automatically
        if ( method_exists( 'WPCW_Installer', 'auto_create_pages' ) ) {
            WPCW_Installer::auto_create_pages();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
    } catch ( Exception $e ) {
        // Log the error
        error_log( 'WP Cupón WhatsApp Activation Error: ' . $e->getMessage() );
        
        // Show user-friendly error message
        wp_die( 
            esc_html__( 'Error durante la activación del plugin. Por favor, revise los logs del servidor para más detalles.', 'wp-cupon-whatsapp' ),
            esc_html__( 'Error de Activación - WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
            array( 'back_link' => true )
        );
    }
}
register_activation_hook( __FILE__, 'wpcw_activate_plugin' );

/**
 * Plugin deactivation hook
 */
function wpcw_deactivate_plugin() {
    // Include roles management
    $roles_file = WPCW_PLUGIN_DIR . 'includes/roles.php';
    if ( file_exists( $roles_file ) ) {
        require_once $roles_file;
        
        // Remove User Roles
        if ( function_exists( 'wpcw_remove_roles' ) ) {
            wpcw_remove_roles();
        }
    }
    
    // Clear rewrite rules
    flush_rewrite_rules();
    
    // Clean up options if needed
    // delete_option('wpcw_settings');
    
    // Note: We don't delete the canjes table by default to preserve data
    // If you want to delete the table, uncomment the following lines:
    // global $wpdb;
    // $wpdb->query("DROP TABLE IF EXISTS " . WPCW_CANJES_TABLE_NAME);
}
register_deactivation_hook( __FILE__, 'wpcw_deactivate_plugin' );

/**
 * Plugin uninstall hook
 */
function wpcw_uninstall_plugin() {
    // This function is called when the plugin is deleted
    // Add cleanup code here if needed
}
register_uninstall_hook( __FILE__, 'wpcw_uninstall_plugin' );

// Initialize the plugin
if ( ! function_exists( 'wpcw_init_plugin' ) ) {
    function wpcw_init_plugin() {
        // Plugin initialization code
        do_action( 'wpcw_loaded' );
    }
    add_action( 'plugins_loaded', 'wpcw_init_plugin', 20 );
}

?>