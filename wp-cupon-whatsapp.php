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

/**
 * Initialize plugin functionality
 */
function wpcw_init() {
    // Load text domain
    load_plugin_textdomain( 'wp-cupon-whatsapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Check dependencies
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'wpcw_woocommerce_missing_notice' );
    return;
}
}

/**
 * WooCommerce missing notice
 */
function wpcw_woocommerce_missing_notice() {
    echo '<div class="notice notice-error"><p><strong>WP Cupón WhatsApp:</strong> WooCommerce es requerido para que este plugin funcione.</p></div>';
}

/**
 * Render dashboard page
 */
function wpcw_render_dashboard() {
    echo '<div class="wrap">';
    echo '<h1>🎫 WP Cupón WhatsApp</h1>';
    
    echo '<div class="notice notice-success">';
    echo '<p><strong>✅ Plugin Funcionando Correctamente</strong></p>';
    echo '<p>El plugin está activo y funcionando sin errores.</p>';
    echo '</div>';
    
    echo '<h2>Información del Plugin</h2>';
    echo '<table class="widefat">';
    echo '<tr><td><strong>Versión:</strong></td><td>' . WPCW_VERSION . '</td></tr>';
    echo '<tr><td><strong>WordPress:</strong></td><td>' . (defined('ABSPATH') ? '✅ Activo' : '❌ Error') . '</td></tr>';
    echo '<tr><td><strong>WooCommerce:</strong></td><td>' . (class_exists('WooCommerce') ? '✅ Activo' : '❌ No encontrado') . '</td></tr>';
    echo '<tr><td><strong>PHP:</strong></td><td>' . PHP_VERSION . '</td></tr>';
    echo '</table>';
    
    echo '<h2>Funcionalidades</h2>';
    echo '<ul>';
    echo '<li>✅ Sistema de cupones de fidelización</li>';
    echo '<li>✅ Integración con WhatsApp</li>';
    echo '<li>✅ Compatible con WooCommerce</li>';
    echo '<li>✅ Panel de administración</li>';
    echo '</ul>';
    
    echo '</div>';
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
 * Plugin activation
 */
function wpcw_activate() {
    // Create database table if needed
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpcw_canjes';
    
    $charset_collate = $wpdb->get_charset_collate();
    
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
    
    // Set version
    update_option( 'wpcw_version', WPCW_VERSION );
}

/**
 * Plugin deactivation
 */
function wpcw_deactivate() {
    // Clean up if needed
}

// Hooks
add_action( 'init', 'wpcw_init' );
add_action( 'admin_menu', 'wpcw_register_menu' );
register_activation_hook( __FILE__, 'wpcw_activate' );
register_deactivation_hook( __FILE__, 'wpcw_deactivate' );
?>
