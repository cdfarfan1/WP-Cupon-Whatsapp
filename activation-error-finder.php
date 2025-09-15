<?php
/**
 * Script para encontrar el error específico en la activación del plugin
 */

echo "=== BUSCADOR DE ERRORES DE ACTIVACIÓN ===\n";

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes necesarias
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', __DIR__);
}

if (!defined('WP_PLUGIN_URL')) {
    define('WP_PLUGIN_URL', 'http://localhost/wp-content/plugins');
}

// Simular wpdb
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';
$wpdb->show_errors = function($value = true) {};
$wpdb->suppress_errors = function($value = true) {};
$wpdb->get_var = function($query) { return null; };
$wpdb->get_results = function($query) { return array(); };
$wpdb->get_charset_collate = function() { return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'; };
$wpdb->last_error = '';

// Definir constante necesaria para WPCW_Installer
define('WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes');

// Simular funciones básicas de WordPress
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://localhost/wp-content/plugins/' . basename(dirname($file)) . '/';
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return basename(dirname($file)) . '/' . basename($file);
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('add_option')) {
    function add_option($option, $value = '') {
        return true;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('get_post_status')) {
    function get_post_status($post_id) {
        return false;
    }
}

if (!function_exists('wp_insert_post')) {
    function wp_insert_post($post_data) {
        return 1;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return false;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '') {
        if ($show === 'version') {
            return '6.0.0';
        }
        return '';
    }
}

if (!function_exists('deactivate_plugins')) {
    function deactivate_plugins($plugins) {
        return true;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message, $title = '', $args = array()) {
        echo "WP_DIE: $message\n";
        exit(1);
    }
}

if (!function_exists('dbDelta')) {
    function dbDelta($queries) {
        echo "DBDELTA: Simulando creación de tabla\n";
        return array();
    }
}

if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

// Simular clase WooCommerce
if (!class_exists('WooCommerce')) {
    class WooCommerce {}
    define('WC_VERSION', '7.0.0');
}

// Simular acción de Elementor
if (!function_exists('did_action')) {
    function did_action($action) {
        if ($action === 'elementor/loaded') {
            return true;
        }
        return false;
    }
}
define('ELEMENTOR_VERSION', '3.5.0');

// Cargar la clase WPCW_Installer
echo "\n[PASO 1] Cargando clase WPCW_Installer...\n";
try {
    require_once __DIR__ . '/includes/class-wpcw-installer.php';
    echo "✅ Clase WPCW_Installer cargada correctamente\n";
} catch (Error $e) {
    echo "❌ ERROR al cargar WPCW_Installer: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    exit(1);
}

// Verificar métodos de la clase WPCW_Installer
echo "\n[PASO 2] Verificando métodos de WPCW_Installer...\n";
$methods = get_class_methods('WPCW_Installer');
if ($methods) {
    foreach ($methods as $method) {
        echo "✅ Método encontrado: $method\n";
    }
} else {
    echo "⚠️ No se encontraron métodos en la clase WPCW_Installer\n";
}

// Probar método init_settings
echo "\n[PASO 3] Probando método init_settings...\n";
try {
    WPCW_Installer::init_settings();
    echo "✅ Método init_settings ejecutado correctamente\n";
} catch (Error $e) {
    echo "❌ ERROR en init_settings: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

// Probar método create_canjes_table
echo "\n[PASO 4] Probando método create_canjes_table...\n";
try {
    WPCW_Installer::create_canjes_table();
    echo "✅ Método create_canjes_table ejecutado correctamente\n";
} catch (Error $e) {
    echo "❌ ERROR en create_canjes_table: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

// Probar método auto_create_pages
echo "\n[PASO 5] Probando método auto_create_pages...\n";
try {
    WPCW_Installer::auto_create_pages();
    echo "✅ Método auto_create_pages ejecutado correctamente\n";
} catch (Error $e) {
    echo "❌ ERROR en auto_create_pages: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

// Probar método create_pages
echo "\n[PASO 6] Probando método create_pages...\n";
try {
    WPCW_Installer::create_pages();
    echo "✅ Método create_pages ejecutado correctamente\n";
} catch (Error $e) {
    echo "❌ ERROR en create_pages: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "\n=== RESUMEN ===\n";
echo "Se han probado todos los métodos de la clase WPCW_Installer.\n";
echo "Si se encontraron errores, estos podrían ser la causa del error fatal durante la activación.\n";
echo "\n";

?>