<?php
/**
 * Script para simular la activación del plugin y encontrar el error fatal
 */

echo "=== SIMULACIÓN DE ACTIVACIÓN DEL PLUGIN ===\n";

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

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Simular wpdb con clase personalizada
class MockWPDB {
    public $prefix = 'wp_';
    public $last_error = '';
    
    public function show_errors($value = true) {
        return true;
    }
    
    public function suppress_errors($value = true) {
        return true;
    }
    
    public function get_var($query) {
        // Simular que la tabla no existe para forzar su creación
        if (strpos($query, 'SHOW TABLES LIKE') !== false) {
            return null;
        }
        return null;
    }
    
    public function get_results($query) {
        // Simular columnas de tabla para verificación
        if (strpos($query, 'SHOW COLUMNS FROM') !== false) {
            return array_fill(0, 15, new stdClass()); // 15 columnas simuladas
        }
        return array();
    }
    
    public function get_charset_collate() {
        return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }
    
    public function update($table, $data, $where, $format = null, $where_format = null) {
        return 1;
    }
    
    public function prepare($query, ...$args) {
        return $query;
    }
}

global $wpdb;
$wpdb = new MockWPDB();

// Definir constantes del plugin
define('WPCW_VERSION', '1.2.0');
define('WPCW_PLUGIN_DIR', __DIR__ . '/');
define('WPCW_PLUGIN_URL', 'http://localhost/wp-content/plugins/wp-cupon-whatsapp/');
define('WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp');
define('WPCW_PLUGIN_FILE', __FILE__);
define('WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes');

echo "Constantes del plugin definidas\n";

// Simular funciones de WordPress
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

if (!function_exists('add_option')) {
    function add_option($option, $value = '') {
        echo "ADD_OPTION: $option = $value\n";
        return true;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        echo "UPDATE_OPTION: $option = $value\n";
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('get_post_status')) {
    function get_post_status($post_id) {
        return false;
    }
}

if (!function_exists('wp_insert_post')) {
    function wp_insert_post($post_data) {
        echo "WP_INSERT_POST: Creando página '" . $post_data['post_title'] . "'\n";
        return rand(1, 1000);
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return false;
    }
}

if (!function_exists('dbDelta')) {
    function dbDelta($queries) {
        echo "DBDELTA: Ejecutando consultas SQL\n";
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

if (!function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules() {
        echo "FLUSH_REWRITE_RULES: Limpiando reglas de reescritura\n";
        return true;
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1;
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

echo "Funciones de WordPress simuladas\n";

// Cargar la clase WPCW_Installer
echo "\n[PASO 1] Cargando clase WPCW_Installer...\n";
try {
    require_once __DIR__ . '/includes/class-wpcw-installer.php';
    echo "✅ Clase WPCW_Installer cargada\n";
} catch (Error $e) {
    echo "❌ ERROR al cargar WPCW_Installer: " . $e->getMessage() . "\n";
    exit(1);
}

// Simular la función de activación
echo "\n[PASO 2] Simulando función de activación...\n";

try {
    echo "Ejecutando init_settings...\n";
    WPCW_Installer::init_settings();
    echo "✅ init_settings completado\n";
    
    echo "Ejecutando create_canjes_table...\n";
    $table_result = WPCW_Installer::create_canjes_table();
    echo "✅ create_canjes_table completado (resultado: " . ($table_result ? 'true' : 'false') . ")\n";
    
    echo "Ejecutando auto_create_pages...\n";
    WPCW_Installer::auto_create_pages();
    echo "✅ auto_create_pages completado\n";
    
    echo "Ejecutando flush_rewrite_rules...\n";
    flush_rewrite_rules();
    echo "✅ flush_rewrite_rules completado\n";
    
} catch (Error $e) {
    echo "❌ ERROR FATAL en activación: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ EXCEPCIÓN en activación: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n=== ACTIVACIÓN SIMULADA EXITOSA ===\n";
echo "No se encontraron errores fatales en la simulación de activación.\n";

?>