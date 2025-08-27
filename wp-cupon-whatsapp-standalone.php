<?php
/**
 * Plugin Name: WP Canje Cupon Whatsapp (Standalone)
 * Plugin URI: https://www.pragmaticsolutions.com.ar
 * Description: Versión simplificada del plugin para pruebas locales sin dependencias de WooCommerce/Elementor.
 * Version: 1.2.3-standalone
 * Author: Cristian Farfan, Pragmatic Solutions
 * Author URI: https://www.pragmaticsolutions.com.ar
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-cupon-whatsapp
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define constants first
define( 'WPCW_VERSION', '1.2.3-standalone' );
define( 'WPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp' );
define( 'WPCW_PLUGIN_FILE', __FILE__ );
define( 'WPCW_MIN_WP_VERSION', '5.0' );
define( 'WPCW_MIN_PHP_VERSION', '7.4' );

// Table name for canjes (redeems)
global $wpdb;
define( 'WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes' );

/**
 * Load plugin textdomain for translations
 */
function wpcw_load_textdomain() {
    load_plugin_textdomain( 'wp-cupon-whatsapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wpcw_load_textdomain' );

/**
 * Function to check basic dependencies (solo WordPress y PHP)
 */
function wpcw_check_basic_dependencies() {
    $dependency_errors = array();

    // Verificar PHP version
    if ( version_compare( PHP_VERSION, WPCW_MIN_PHP_VERSION, '<' ) ) {
        $dependency_errors[] = sprintf(
            'PHP %s o superior (actual: %s)',
            WPCW_MIN_PHP_VERSION,
            PHP_VERSION
        );
    }

    // Verificar WordPress version
    if ( version_compare( get_bloginfo( 'version' ), WPCW_MIN_WP_VERSION, '<' ) ) {
        $dependency_errors[] = sprintf(
            'WordPress %s o superior (actual: %s)',
            WPCW_MIN_WP_VERSION,
            get_bloginfo( 'version' )
        );
    }

    if ( ! empty( $dependency_errors ) ) {
        add_action(
            'admin_notices',
            function () use ( $dependency_errors ) {
                ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php echo esc_html( 'WP Canje Cupón WhatsApp (Standalone) requiere:' ); ?></strong>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <?php foreach ( $dependency_errors as $error ) : ?>
                            <li><?php echo esc_html( $error ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </p>
            </div>
                <?php
            }
        );
        return false;
    }

    return true;
}

/**
 * Load core files (solo los esenciales)
 */
function wpcw_load_core_files() {
    $core_files = array(
        'includes/post-types.php',
        'includes/whatsapp-handlers.php',
        'includes/validation-enhanced.php',
        'public/shortcodes.php',
        'admin/admin-menu.php'
    );

    foreach ( $core_files as $file ) {
        $file_path = WPCW_PLUGIN_DIR . $file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}

/**
 * Initialize plugin functionality
 */
function wpcw_init() {
    // Verificar dependencias básicas
    if ( ! wpcw_check_basic_dependencies() ) {
        return;
    }

    // Cargar archivos principales
    wpcw_load_core_files();

    // Register post types
    if ( function_exists( 'wpcw_register_post_types' ) ) {
        wpcw_register_post_types();
    }

    // Initialize roles
    if ( function_exists( 'wpcw_add_roles' ) ) {
        wpcw_add_roles();
    }
}

// Hook into WordPress init action for main functionality
add_action( 'init', 'wpcw_init' );

/**
 * Create canjes table on activation
 */
function wpcw_create_canjes_table() {
    global $wpdb;

    $table_name = WPCW_CANJES_TABLE_NAME;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        cupon_id bigint(20) NOT NULL,
        user_email varchar(100) NOT NULL,
        user_phone varchar(20) NOT NULL,
        user_name varchar(100) NOT NULL,
        status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY cupon_id (cupon_id),
        KEY user_email (user_email),
        KEY status (status)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/**
 * Plugin activation
 */
register_activation_hook(
    __FILE__,
    function () {
        if ( ! wpcw_check_basic_dependencies() ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( 'Por favor, verifica los requisitos del plugin antes de activarlo.' );
        }

        // Crear tabla de canjes
        wpcw_create_canjes_table();

        // Flush rewrite rules
        flush_rewrite_rules();
    }
);

/**
 * Plugin deactivation
 */
register_deactivation_hook(
    __FILE__,
    function () {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
);

/**
 * Add admin notice for standalone version
 */
add_action( 'admin_notices', function() {
    if ( current_user_can( 'manage_options' ) ) {
        ?>
        <div class="notice notice-info">
            <p>
                <strong>WP Canje Cupón WhatsApp (Standalone):</strong> 
                Esta es una versión simplificada para pruebas locales. 
                Para funcionalidad completa, instala WooCommerce y Elementor.
            </p>
        </div>
        <?php
    }
});

/**
 * Enqueue admin styles and scripts
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    // Solo cargar en páginas del plugin
    if ( strpos( $hook, 'wp-cupon-whatsapp' ) !== false ) {
        wp_enqueue_style( 'wpcw-admin-style', WPCW_PLUGIN_URL . 'admin/css/admin-style.css', array(), WPCW_VERSION );
        wp_enqueue_script( 'wpcw-admin-script', WPCW_PLUGIN_URL . 'admin/js/admin-script.js', array( 'jquery' ), WPCW_VERSION, true );
    }
});

/**
 * Enqueue public styles and scripts
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'wpcw-public-style', WPCW_PLUGIN_URL . 'public/css/public-style.css', array(), WPCW_VERSION );
    wp_enqueue_script( 'wpcw-public-script', WPCW_PLUGIN_URL . 'public/js/public-script.js', array( 'jquery' ), WPCW_VERSION, true );
    
    // Localizar script para AJAX
    wp_localize_script( 'wpcw-public-script', 'wpcw_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'wpcw_nonce' )
    ));
});
?>