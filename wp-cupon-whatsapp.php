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
// Include public shortcodes
require_once WPCW_PLUGIN_DIR . 'public/shortcodes.php';
