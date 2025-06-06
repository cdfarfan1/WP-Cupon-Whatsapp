<?php
/**
 * Plugin Name: WP Canje Cupon Whatsapp
 * Plugin URI: https://www.pragmaticsolutions.com.ar
 * Description: Plugin para programa de fidelización y canje de cupones por WhatsApp integrado con WooCommerce.
 * Version: 1.0.0
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

// Define constants
define( 'WPCW_VERSION', '1.0.0' );
define( 'WPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp' );
define( 'WPCW_PLUGIN_FILE', __FILE__ );

// Table name for canjes (redeems)
global $wpdb;
define( 'WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes' );

/**
 * The code that runs during plugin activation.
 */
function wpcw_activate_plugin() {
    // Include installer class.
    require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-installer.php';
    // Include post types registration.
    require_once WPCW_PLUGIN_DIR . 'includes/post-types.php';
    // Include taxonomies registration.
    require_once WPCW_PLUGIN_DIR . 'includes/taxonomies.php';
    // Include roles management.
    require_once WPCW_PLUGIN_DIR . 'includes/roles.php';

    // Create canjes table.
    WPCW_Installer::create_canjes_table();

    // Register CPTs.
    wpcw_register_post_types();
    // Register Taxonomies.
    wpcw_register_taxonomies();
    // Add User Roles.
    wpcw_add_roles();

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 */
function wpcw_deactivate_plugin() {
    // Include roles management.
    require_once WPCW_PLUGIN_DIR . 'includes/roles.php';
    // Remove User Roles.
    wpcw_remove_roles();
    // Nothing to do here yet.
}

register_activation_hook( __FILE__, 'wpcw_activate_plugin' );
register_deactivation_hook( __FILE__, 'wpcw_deactivate_plugin' );

/**
 * Load plugin textdomain.
 */
function wpcw_load_textdomain() {
    load_plugin_textdomain(
        WPCW_TEXT_DOMAIN,
        false,
        dirname( plugin_basename( WPCW_PLUGIN_FILE ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'wpcw_load_textdomain' );

// Include post types
require_once WPCW_PLUGIN_DIR . 'includes/post-types.php';
// Include taxonomies
require_once WPCW_PLUGIN_DIR . 'includes/taxonomies.php';
// Include roles
require_once WPCW_PLUGIN_DIR . 'includes/roles.php';
// Include customer fields
require_once WPCW_PLUGIN_DIR . 'includes/customer-fields.php';
// Include reCAPTCHA integration
require_once WPCW_PLUGIN_DIR . 'includes/recaptcha-integration.php';
// Include application processing
require_once WPCW_PLUGIN_DIR . 'includes/application-processing.php';
// Include AJAX handlers
require_once WPCW_PLUGIN_DIR . 'includes/ajax-handlers.php';
// Include REST API endpoints
require_once WPCW_PLUGIN_DIR . 'includes/rest-api.php';
// Include redemption logic
require_once WPCW_PLUGIN_DIR . 'includes/redemption-logic.php';
// Include statistics functions
require_once WPCW_PLUGIN_DIR . 'includes/stats-functions.php';
// Include export functions
require_once WPCW_PLUGIN_DIR . 'includes/export-functions.php';
// Include public shortcodes
require_once WPCW_PLUGIN_DIR . 'public/shortcodes.php';
// Include My Account endpoint functions
require_once WPCW_PLUGIN_DIR . 'public/my-account-endpoints.php';

// Admin specific includes
if ( is_admin() ) {
    require_once WPCW_PLUGIN_DIR . 'admin/meta-boxes.php';
    require_once WPCW_PLUGIN_DIR . 'admin/admin-menu.php';
    // Futuras inclusiones específicas de admin podrían ir aquí también.
}

/**
 * Enqueue public-facing scripts and styles.
 */
function wpcw_public_enqueue_scripts_styles() {
    // Encolar estilos públicos si los hubiera (ej. public/css/public.css)
    // wp_enqueue_style(
    //     'wpcw-public-style',
    //     WPCW_PLUGIN_URL . 'public/css/public.css',
    //     array(),
    //     WPCW_VERSION
    // );

    // Encolar el script de canje
    // Solo encolar si es necesario, ej. si una página muestra cupones.
    // Por ahora, lo encolaremos globalmente en el frontend para asegurar que esté disponible.
    // Una mejora sería encolarlo solo si el shortcode [wpcw_mis_cupones] o [wpcw_cupones_publicos] está presente.
    // O si se detecta la clase 'wpcw-canjear-cupon-btn' en la página.
    if ( !is_admin() ) { // Solo encolar en el frontend
        wp_enqueue_script(
            'wpcw-canje-handler',
            WPCW_PLUGIN_URL . 'public/js/canje-handler.js',
            array( 'jquery' ), // Dependencia de jQuery
            WPCW_VERSION,     // Versión del plugin
            true              // Cargar en el footer
        );

        // Localizar el script para pasar datos de PHP a JS
        wp_localize_script(
            'wpcw-canje-handler', // Handle del script al que se le pasan los datos
            'wpcw_canje_obj',    // Nombre del objeto JavaScript que contendrá los datos
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ), // URL para peticiones AJAX
                'nonce'    => wp_create_nonce( 'wpcw_request_canje_action_nonce' ), // Nonce para la acción específica
                // Se pueden añadir más datos aquí si son necesarios, como mensajes traducibles.
                'text_processing' => __( 'Procesando...', 'wp-cupon-whatsapp' ),
                'text_error_generic' => __( 'Ocurrió un error. Por favor, inténtalo de nuevo.', 'wp-cupon-whatsapp' ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wpcw_public_enqueue_scripts_styles' );
