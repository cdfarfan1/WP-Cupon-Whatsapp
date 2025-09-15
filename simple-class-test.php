<?php
/**
 * Script simple para probar la carga de la clase WPCW_Installer
 */

echo "=== PRUEBA SIMPLE DE CLASE ===\n";

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes mínimas
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Simular wpdb básico
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';

// Definir constante necesaria
define('WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes');

echo "Constantes definidas correctamente\n";

// Verificar que el archivo existe
$installer_file = __DIR__ . '/includes/class-wpcw-installer.php';
if (!file_exists($installer_file)) {
    echo "❌ ERROR: El archivo $installer_file no existe\n";
    exit(1);
}

echo "Archivo encontrado: $installer_file\n";

// Intentar incluir el archivo
echo "Intentando incluir el archivo...\n";
try {
    require_once $installer_file;
    echo "✅ Archivo incluido correctamente\n";
} catch (ParseError $e) {
    echo "❌ ERROR DE SINTAXIS: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    exit(1);
} catch (Error $e) {
    echo "❌ ERROR FATAL: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ EXCEPCIÓN: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
    exit(1);
}

// Verificar que la clase existe
if (class_exists('WPCW_Installer')) {
    echo "✅ Clase WPCW_Installer encontrada\n";
    
    // Listar métodos
    $methods = get_class_methods('WPCW_Installer');
    echo "Métodos disponibles:\n";
    foreach ($methods as $method) {
        echo "  - $method\n";
    }
} else {
    echo "❌ ERROR: La clase WPCW_Installer no se encontró\n";
    exit(1);
}

echo "\n=== PRUEBA COMPLETADA ===\n";
?>