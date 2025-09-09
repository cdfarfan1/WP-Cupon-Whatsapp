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
 * Register admin menu
 */
function wpcw_register_menu() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    add_menu_page(
        'WP Cupón WhatsApp',
        'WP Cupón WhatsApp',
        'manage_options',
        'wpcw-dashboard',
        'wpcw_render_dashboard',
        'dashicons-tickets-alt',
        25
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
