<?php
/**
 * Script para probar la activación del plugin WP Cupon WhatsApp
 * Simula el proceso de activación de WordPress con todas las funciones necesarias
 */

echo "=== TEST DE ACTIVACION DEL PLUGIN ===\n";
echo "Plugin: WP Cupon WhatsApp\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Simular constantes de WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', 'C:/xampp/htdocs/webstore/');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins');
}
if (!defined('WP_PLUGIN_URL')) {
    define('WP_PLUGIN_URL', 'http://localhost/webstore/wp-content/plugins');
}
if (!defined('WPMU_PLUGIN_DIR')) {
    define('WPMU_PLUGIN_DIR', ABSPATH . 'wp-content/mu-plugins');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Simular objeto global $wpdb
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';

echo "1. VERIFICANDO CONSTANTES DE WORDPRESS:\n";
echo "✓ ABSPATH: " . ABSPATH . "\n";
echo "✓ WP_PLUGIN_DIR: " . WP_PLUGIN_DIR . "\n";
echo "✓ WP_PLUGIN_URL: " . WP_PLUGIN_URL . "\n";
echo "✓ wpdb->prefix: " . $wpdb->prefix . "\n\n";

echo "2. PROBANDO CARGA DEL ARCHIVO PRINCIPAL:\n";
$archivoMain = 'wp-cupon-whatsapp.php';

if (!file_exists($archivoMain)) {
    echo "✗ ERROR: Archivo principal no encontrado: $archivoMain\n";
    exit(1);
}

echo "✓ Archivo principal encontrado: $archivoMain\n";

echo "\n3. SIMULANDO FUNCIONES DE WORDPRESS:\n";

// Función helper para convertir callbacks a string
function callback_to_string($callback) {
    if (is_string($callback)) {
        return $callback;
    } elseif (is_array($callback)) {
        return 'Array[' . (is_object($callback[0]) ? get_class($callback[0]) : $callback[0]) . '::' . $callback[1] . ']';
    } elseif (is_object($callback)) {
        return 'Closure';
    } else {
        return 'Unknown';
    }
}

// Simular todas las funciones de WordPress necesarias
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        echo "  add_action('$hook', '" . callback_to_string($callback) . "')\n";
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        echo "  add_filter('$hook', '" . callback_to_string($callback) . "')\n";
        return true;
    }
}

if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $callback) {
        echo "  add_shortcode('$tag', '" . callback_to_string($callback) . "')\n";
        return true;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        echo "  register_activation_hook('$file', '" . callback_to_string($callback) . "')\n";
        return true;
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {
        echo "  register_deactivation_hook('$file', '" . callback_to_string($callback) . "')\n";
        return true;
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return WP_PLUGIN_URL . '/' . basename(dirname($file)) . '/';
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
        echo "  wp_enqueue_script('$handle')\n";
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
        echo "  wp_enqueue_style('$handle')\n";
        return true;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('did_action')) {
    function did_action($hook) {
        return 0;
    }
}

if (!function_exists('wp_ajax_nopriv_wp_cupon_whatsapp_submit')) {
    function wp_ajax_nopriv_wp_cupon_whatsapp_submit() {
        echo "  AJAX handler registered\n";
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'test_nonce_12345';
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = array()) {
        echo "wp_die: $message\n";
        exit;
    }
}

if (!function_exists('wp_redirect')) {
    function wp_redirect($location, $status = 302) {
        echo "wp_redirect: $location\n";
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        echo "  update_option('$option')\n";
        return true;
    }
}

if (!function_exists('register_post_type')) {
    function register_post_type($post_type, $args = array()) {
        echo "  register_post_type('$post_type')\n";
        return true;
    }
}

if (!function_exists('register_taxonomy')) {
    function register_taxonomy($taxonomy, $object_type, $args = array()) {
        echo "  register_taxonomy('$taxonomy')\n";
        return true;
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
        echo "  load_plugin_textdomain('$domain')\n";
        return true;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '', $filter = 'raw') {
        switch ($show) {
            case 'version':
                return '6.0';
            case 'name':
                return 'Test Site';
            case 'url':
                return 'http://localhost/webstore';
            default:
                return 'test_value';
        }
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        $user = new stdClass();
        $user->ID = 1;
        $user->user_login = 'admin';
        return $user;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true;
    }
}

if (!function_exists('wp_safe_redirect')) {
    function wp_safe_redirect($location, $status = 302) {
        echo "wp_safe_redirect: $location\n";
        return true;
    }
}

if (!function_exists('home_url')) {
    function home_url($path = '', $scheme = null) {
        return 'http://localhost/webstore' . $path;
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {
        return 'http://localhost/webstore/wp-admin/' . $path;
    }
}

if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults = '') {
        if (is_object($args)) {
            $r = get_object_vars($args);
        } elseif (is_array($args)) {
            $r =& $args;
        } else {
            wp_parse_str($args, $r);
        }
        
        if (is_array($defaults)) {
            return array_merge($defaults, $r);
        }
        return $r;
    }
}

if (!function_exists('wp_parse_str')) {
    function wp_parse_str($string, &$array) {
        parse_str($string, $array);
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        $file = str_replace('\\', '/', $file);
        $file = preg_replace('|/+|', '/', $file);
        $plugin_dir = str_replace('\\', '/', WP_PLUGIN_DIR);
        $plugin_dir = preg_replace('|/+|', '/', $plugin_dir);
        $mu_plugin_dir = str_replace('\\', '/', WPMU_PLUGIN_DIR);
        $mu_plugin_dir = preg_replace('|/+|', '/', $mu_plugin_dir);
        
        if (strpos($file, $plugin_dir) === 0) {
            return substr($file, strlen($plugin_dir) + 1);
        } elseif ($mu_plugin_dir && strpos($file, $mu_plugin_dir) === 0) {
            return substr($file, strlen($mu_plugin_dir) + 1);
        }
        
        return basename(dirname($file)) . '/' . basename($file);
    }
}

if (!function_exists('add_settings_link')) {
    function add_settings_link($links, $file) {
        return $links;
    }
}

if (!function_exists('plugin_action_links')) {
    function plugin_action_links($links) {
        return $links;
    }
}

echo "✓ Funciones de WordPress simuladas\n";

echo "\n4. INTENTANDO CARGAR EL PLUGIN:\n";

// Capturar errores
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include_once $archivoMain;
    echo "✓ Plugin cargado exitosamente\n";
    
    // Verificar si se definieron las constantes principales
    if (defined('WPCW_VERSION') && defined('WPCW_PLUGIN_DIR') && defined('WPCW_PLUGIN_URL')) {
        echo "✓ Constantes principales del plugin definidas correctamente\n";
        echo "  - WPCW_VERSION: " . WPCW_VERSION . "\n";
        echo "  - WPCW_PLUGIN_DIR: " . WPCW_PLUGIN_DIR . "\n";
        echo "  - WPCW_PLUGIN_URL: " . WPCW_PLUGIN_URL . "\n";
    } else {
        echo "⚠ Constantes principales del plugin no definidas correctamente\n";
    }
    
    // Verificar si se definieron las funciones principales
    if (function_exists('wpcw_init') && function_exists('wpcw_load_textdomain')) {
        echo "✓ Funciones principales del plugin definidas correctamente\n";
    } else {
        echo "⚠ Funciones principales del plugin no definidas correctamente\n";
    }
    
} catch (Error $e) {
    echo "✗ ERROR FATAL: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "✗ EXCEPCION: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

$output = ob_get_clean();
if (!empty($output)) {
    echo "\nSALIDA CAPTURADA:\n$output\n";
}

echo "\n5. VERIFICANDO FUNCIONALIDADES ESPECÍFICAS:\n";

// Verificar shortcodes registrados
echo "Shortcodes registrados:\n";
$shortcodes = array(
    'wpcw_solicitud_adhesion_form',
    'wpcw_mis_cupones',
    'wpcw_cupones_publicos',
    'wpcw_canje_cupon'
);

foreach ($shortcodes as $shortcode) {
    if (function_exists('shortcode_exists')) {
        echo "  - $shortcode: " . (shortcode_exists($shortcode) ? "✓" : "✗") . "\n";
    } else {
        echo "  - $shortcode: Simulado en prueba\n";
    }
}

// Verificar integración con WooCommerce
echo "\nIntegración con WooCommerce:\n";
if (function_exists('wpcw_add_custom_register_fields') && 
    function_exists('wpcw_add_custom_account_fields')) {
    echo "  ✓ Campos personalizados de WooCommerce\n";
} else {
    echo "  ✗ Campos personalizados de WooCommerce no definidos\n";
}

// Verificar integración con WhatsApp
echo "\nIntegración con WhatsApp:\n";
if (function_exists('wpcw_request_canje_handler')) {
    echo "  ✓ Manejador de canjes por WhatsApp\n";
} else {
    echo "  ✗ Manejador de canjes por WhatsApp no definido\n";
}

// Verificar integración con Elementor
echo "\nIntegración con Elementor:\n";
if (file_exists(WPCW_PLUGIN_DIR . 'elementor/elementor-addon.php')) {
    echo "  ✓ Archivo de integración con Elementor encontrado\n";
} else {
    echo "  ✗ Archivo de integración con Elementor no encontrado\n";
}

echo "\n=== RESULTADO ===\n";
echo "Test de activación completado.\n";
echo "Si no hay errores arriba, el plugin debería activarse correctamente.\n";
echo "\nPróximos pasos recomendados:\n";
echo "1. Activar el plugin en WordPress\n";
echo "2. Configurar el plugin en WP Cupon > Configuración\n";
echo "3. Crear cupones de prueba\n";
echo "4. Probar los shortcodes en páginas de prueba\n";
echo "5. Verificar la integración con WhatsApp usando enlaces wa.me\n";
?>