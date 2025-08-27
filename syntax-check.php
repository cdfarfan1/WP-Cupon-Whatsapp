<?php
/**
 * Script para verificar la sintaxis del archivo principal del plugin
 */

echo "=== VERIFICACIÓN DE SINTAXIS ===\n";

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

$file = 'wp-cupon-whatsapp.php';

echo "Verificando archivo: $file\n";

// Verificar que el archivo existe
if (!file_exists($file)) {
    echo "❌ ERROR: El archivo $file no existe\n";
    exit(1);
}

// Verificar sintaxis usando php -l
$output = [];
$return_var = 0;
exec("php -l $file", $output, $return_var);

echo "\nResultado de verificación de sintaxis:\n";
foreach ($output as $line) {
    echo "$line\n";
}

if ($return_var !== 0) {
    echo "\n❌ ERROR: El archivo tiene errores de sintaxis\n";
    exit(1);
}

echo "\n✅ Sintaxis correcta\n";

// Intentar cargar el archivo para ver si hay errores fatales
echo "\nIntentando cargar el archivo para detectar errores fatales...\n";

try {
    // Definir constantes necesarias para evitar errores
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
    
    // Simular funciones básicas
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
    
    // Incluir el archivo
    include_once $file;
    echo "✅ Archivo cargado sin errores fatales\n";
} catch (ParseError $e) {
    echo "❌ ERROR DE SINTAXIS: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ ERROR FATAL: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
?>