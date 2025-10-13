<?php
/**
 * Plugin Name: [PLUGIN_NAME]
 * Plugin URI: https://cristianfarfan.com.ar/plugins/[plugin-slug]
 * Description: [PLUGIN_DESCRIPTION]
 * Version: 1.0.0
 * Author: Cristian Farfan
 * Author URI: https://cristianfarfan.com.ar
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: [plugin-slug]
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.5
 *
 * @package     [Plugin_Package]
 * @author      Cristian Farfan <farfancris@gmail.com>
 * @copyright   2024-2025 Cristian Farfan
 * @license     MIT https://opensource.org/licenses/MIT
 * @link        https://cristianfarfan.com.ar
 * @since       1.0.0
 *
 * GitHub Plugin URI: cdfarfan1/[plugin-slug]
 * GitHub Branch: main
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( '[PLUGIN_PREFIX]_VERSION', '1.0.0' );
define( '[PLUGIN_PREFIX]_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( '[PLUGIN_PREFIX]_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( '[PLUGIN_PREFIX]_PLUGIN_FILE', __FILE__ );
define( '[PLUGIN_PREFIX]_TEXT_DOMAIN', '[plugin-slug]' );

/**
 * The code that runs during plugin activation.
 */
function activate_[plugin_function_prefix]() {
    require_once [PLUGIN_PREFIX]_PLUGIN_DIR . 'includes/class-[plugin-slug]-activator.php';
    [Plugin_Class]_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_[plugin_function_prefix]() {
    require_once [PLUGIN_PREFIX]_PLUGIN_DIR . 'includes/class-[plugin-slug]-deactivator.php';
    [Plugin_Class]_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_[plugin_function_prefix]' );
register_deactivation_hook( __FILE__, 'deactivate_[plugin_function_prefix]' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require [PLUGIN_PREFIX]_PLUGIN_DIR . 'includes/class-[plugin-slug].php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_[plugin_function_prefix]() {
    $plugin = new [Plugin_Class]();
    $plugin->run();
}
run_[plugin_function_prefix]();
