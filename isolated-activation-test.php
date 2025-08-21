<?php
/**
 * Test de activación aislado - Simula el entorno de WordPress
 * para identificar el error específico de activación del plugin
 */

echo "=== TEST DE ACTIVACIÓN AISLADO ===\n";
echo "Simulando entorno WordPress...\n\n";

// Simular constantes de WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', __DIR__);
}

if (!defined('WP_PLUGIN_URL')) {
    define('WP_PLUGIN_URL', 'http://localhost/wp-content/plugins');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/isolated-test-errors.log');

echo "[PASO 1] Verificando archivo principal del plugin...\n";
if (!file_exists('wp-cupon-whatsapp.php')) {
    echo "❌ ERROR: wp-cupon-whatsapp.php no encontrado\n";
    exit(1);
}
echo "✅ Archivo principal encontrado\n\n";

echo "[PASO 2] Simulando funciones básicas de WordPress...\n";

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
        echo "  📌 Hook registrado: $hook -> $callback\n";
        return true;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        echo "  🔧 Hook de activación registrado: $callback\n";
        return true;
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {
        echo "  🔧 Hook de desactivación registrado: $callback\n";
        return true;
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
        echo "  🌐 Textdomain cargado: $domain\n";
        return true;
    }
}

if (!function_exists('register_post_type')) {
    function register_post_type($post_type, $args = array()) {
        echo "  📝 Post type registrado: $post_type\n";
        return (object) array('name' => $post_type);
    }
}

if (!function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules($hard = true) {
        echo "  🔄 Rewrite rules flushed\n";
        return true;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message, $title = '', $args = array()) {
        echo "❌ WP_DIE: $message\n";
        exit(1);
    }
}

echo "✅ Funciones de WordPress simuladas\n\n";

echo "[PASO 3] Intentando cargar el plugin principal...\n";

// Capturar cualquier salida o error
ob_start();
$error_occurred = false;

try {
    // Incluir el archivo principal del plugin
    include_once 'wp-cupon-whatsapp.php';
    echo "✅ Plugin principal cargado exitosamente\n";
} catch (ParseError $e) {
    $error_occurred = true;
    echo "❌ PARSE ERROR: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
} catch (Error $e) {
    $error_occurred = true;
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
} catch (Exception $e) {
    $error_occurred = true;
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

$output = ob_get_clean();

if (!empty($output)) {
    echo "\n[SALIDA CAPTURADA]\n";
    echo $output . "\n";
}

echo "\n[PASO 4] Verificando funciones definidas por el plugin...\n";

// Verificar si las funciones principales del plugin fueron definidas
$expected_functions = [
    'wpcw_init',
    'wpcw_activate_plugin',
    'wpcw_deactivate_plugin'
];

foreach ($expected_functions as $func) {
    if (function_exists($func)) {
        echo "✅ Función encontrada: $func\n";
    } else {
        echo "⚠️  Función no encontrada: $func\n";
    }
}

echo "\n[PASO 5] Simulando activación del plugin...\n";

if (function_exists('wpcw_activate_plugin')) {
    try {
        echo "Ejecutando hook de activación...\n";
        wpcw_activate_plugin();
        echo "✅ Hook de activación ejecutado exitosamente\n";
    } catch (Exception $e) {
        echo "❌ ERROR en activación: " . $e->getMessage() . "\n";
        $error_occurred = true;
    }
} else {
    echo "⚠️  No se encontró función de activación\n";
}

echo "\n=== RESUMEN DEL TEST ===\n";
if ($error_occurred) {
    echo "❌ SE DETECTARON ERRORES durante el test\n";
    echo "📋 Revisa el archivo isolated-test-errors.log para más detalles\n";
} else {
    echo "✅ TEST COMPLETADO SIN ERRORES CRÍTICOS\n";
    echo "💡 El plugin debería activarse correctamente\n";
}

echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Si hay errores, corregir los archivos indicados\n";
echo "2. Si no hay errores, el problema puede estar en el servidor\n";
echo "3. Verificar permisos de archivos en el servidor\n";
echo "4. Revisar logs de WordPress en el servidor\n";
echo "\n";

?>