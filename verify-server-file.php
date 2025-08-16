<?php
/**
 * Script de verificación para confirmar el estado del archivo debug-headers.php en el servidor
 * Subir este archivo al servidor y ejecutarlo para verificar la sincronización
 */

// Prevent direct access in production
if (!defined('ABSPATH') && !isset($_GET['verify'])) {
    die('Access denied. Add ?verify=1 to URL to run verification.');
}

// Set content type for better display
header('Content-Type: text/plain; charset=utf-8');

echo "=== VERIFICACIÓN DEL ARCHIVO DEBUG-HEADERS.PHP ===\n\n";

// Get the plugin directory
$plugin_dir = dirname(__FILE__) . '/';
$debug_file = $plugin_dir . 'debug-headers.php';

echo "Ruta del archivo: $debug_file\n";
echo "Archivo existe: " . (file_exists($debug_file) ? 'SÍ' : 'NO') . "\n\n";

if (!file_exists($debug_file)) {
    echo "❌ ERROR: El archivo debug-headers.php no existe en la ruta especificada.\n";
    exit;
}

// Get file info
$file_size = filesize($debug_file);
$file_mtime = filemtime($debug_file);
$file_date = date('Y-m-d H:i:s', $file_mtime);

echo "Tamaño del archivo: $file_size bytes\n";
echo "Última modificación: $file_date\n\n";

// Read and analyze the file
$content = file_get_contents($debug_file);
$lines = explode("\n", $content);
$total_lines = count($lines);

echo "Total de líneas: $total_lines\n\n";

// Check specific lines around the problematic area
echo "=== ANÁLISIS DE LÍNEAS CRÍTICAS ===\n\n";

// Check line 32 (the problematic line)
if (isset($lines[31])) { // Array is 0-indexed
    echo "Línea 32: " . trim($lines[31]) . "\n";
} else {
    echo "❌ Línea 32 no encontrada\n";
}

// Check lines around 85 (where the fix should be)
echo "\nLíneas 83-87 (área de corrección):\n";
for ($i = 82; $i <= 86; $i++) {
    if (isset($lines[$i])) {
        $line_content = trim($lines[$i]);
        $marker = ($i == 84) ? ' <-- LÍNEA 85 (CORRECCIÓN)' : '';
        echo sprintf("Línea %d: %s%s\n", $i + 1, $line_content, $marker);
    }
}

// Check for syntax errors
echo "\n=== VERIFICACIÓN DE SINTAXIS ===\n\n";

// Create a temporary file to test syntax
$temp_file = sys_get_temp_dir() . '/debug-headers-test.php';
file_put_contents($temp_file, $content);

// Use php -l to check syntax
$output = array();
$return_code = 0;
exec("php -l \"$temp_file\" 2>&1", $output, $return_code);

if ($return_code === 0) {
    echo "✅ SINTAXIS VÁLIDA: El archivo no tiene errores de sintaxis\n";
} else {
    echo "❌ ERROR DE SINTAXIS DETECTADO:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
}

// Clean up
unlink($temp_file);

// Check for the specific fix
echo "\n=== VERIFICACIÓN DE LA CORRECCIÓN ===\n\n";

$fix_found = false;
foreach ($lines as $line_num => $line) {
    if (strpos($line, 'Added missing closing brace for the main foreach loop') !== false) {
        echo "✅ CORRECCIÓN ENCONTRADA en línea " . ($line_num + 1) . "\n";
        echo "   Contenido: " . trim($line) . "\n";
        $fix_found = true;
        break;
    }
}

if (!$fix_found) {
    echo "❌ CORRECCIÓN NO ENCONTRADA: El archivo no contiene la corrección esperada\n";
    echo "   Buscar: 'Added missing closing brace for the main foreach loop'\n";
}

// Final recommendation
echo "\n=== RECOMENDACIÓN ===\n\n";

if ($return_code === 0 && $fix_found) {
    echo "✅ El archivo está CORRECTO en el servidor\n";
    echo "   Si aún ves errores, verifica:\n";
    echo "   1. Caché de PHP/OPcache\n";
    echo "   2. Múltiples instalaciones del plugin\n";
    echo "   3. Reinicia el servidor web\n";
} else {
    echo "❌ El archivo necesita ser ACTUALIZADO en el servidor\n";
    echo "   Sube la versión corregida desde tu entorno local\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>