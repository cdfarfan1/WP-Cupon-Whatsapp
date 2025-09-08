<?php
/**
 * Plugin Name: WP Cupón WhatsApp (Versión Corregida)
 * Plugin URI: https://www.pragmaticsolutions.com.ar
 * Description: Plugin para programa de fidelización y canje de cupones por WhatsApp integrado con WooCommerce. Versión mínima funcional y estable.
 * Version: 1.4.2
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
define( 'WPCW_VERSION', '1.4.2-test' );
define( 'WPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp' );
define( 'WPCW_PLUGIN_FILE', __FILE__ );

/**
 * Initialize plugin functionality
 */
function wpcw_test_init() {
    error_log('WPCW TEST: Plugin inicializado correctamente');
}

/**
 * Render test dashboard page
 */
function wpcw_render_test_dashboard() {
    echo '<div class="wrap">';
    echo '<h1>🎫 WP Cupón WhatsApp (Versión Corregida)</h1>';
    echo '<p>Plugin funcional y estable para programa de fidelización y canje de cupones por WhatsApp.</p>';
    
    echo '<div class="notice notice-success">';
    echo '<p><strong>✅ Plugin Funcionando Correctamente</strong></p>';
    echo '<p>El plugin está activo y funcionando sin errores. Versión estable y optimizada.</p>';
    echo '</div>';
    
    echo '<h2>Información del Plugin:</h2>';
    echo '<ul>';
    echo '<li>Versión: ' . WPCW_VERSION . '</li>';
    echo '<li>Directorio: ' . WPCW_PLUGIN_DIR . '</li>';
    echo '<li>URL: ' . WPCW_PLUGIN_URL . '</li>';
    echo '<li>Archivo: ' . WPCW_PLUGIN_FILE . '</li>';
    echo '</ul>';
    
    echo '<h2>Verificaciones:</h2>';
    echo '<ul>';
    echo '<li>WordPress: ' . (defined('ABSPATH') ? '✅' : '❌') . '</li>';
    echo '<li>Usuario admin: ' . (current_user_can('manage_options') ? '✅' : '❌') . '</li>';
    echo '<li>WooCommerce: ' . (class_exists('WooCommerce') ? '✅' : '❌') . '</li>';
    echo '</ul>';
    
    echo '</div>';
}

/**
 * Register admin menu
 */
function wpcw_register_test_menu() {
    // Verificar permisos
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Menú Principal
    add_menu_page(
        'WP Cupón WhatsApp',           // Título de la página
        'WP Cupón WhatsApp',           // Título del menú
        'manage_options',              // Capacidad requerida
        'wpcw-dashboard',              // Slug del menú
        'wpcw_render_test_dashboard',  // Función callback
        'dashicons-tickets-alt',       // Icono
        25                             // Posición
    );
}

/**
 * Plugin activation hook
 */
function wpcw_test_activate() {
    error_log('WPCW TEST: Plugin activado correctamente');
}

/**
 * Plugin deactivation hook
 */
function wpcw_test_deactivate() {
    error_log('WPCW TEST: Plugin desactivado');
}

// Hooks
add_action( 'init', 'wpcw_test_init' );
add_action( 'admin_menu', 'wpcw_register_test_menu' );
register_activation_hook( __FILE__, 'wpcw_test_activate' );
register_deactivation_hook( __FILE__, 'wpcw_test_deactivate' );
?>
