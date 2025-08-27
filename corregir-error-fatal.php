<?php
/**
 * Script para corregir el error fatal en wp-cupon-whatsapp.php
 * Error: Uso de WC_VERSION sin verificar si está definida
 */

echo "<h2>Corrección del Error Fatal en wp-cupon-whatsapp.php</h2>";

$plugin_file = __DIR__ . '/wp-cupon-whatsapp.php';

if (!file_exists($plugin_file)) {
    echo "<p style='color: red;'>❌ Archivo wp-cupon-whatsapp.php no encontrado</p>";
    exit;
}

echo "<p>✅ Archivo encontrado: $plugin_file</p>";

// Leer el contenido del archivo
$content = file_get_contents($plugin_file);

if ($content === false) {
    echo "<p style='color: red;'>❌ No se pudo leer el archivo</p>";
    exit;
}

echo "<p>✅ Archivo leído correctamente</p>";

// Buscar la línea problemática
$lines = explode("\n", $content);
$error_found = false;
$line_number = 0;

foreach ($lines as $index => $line) {
    $line_number = $index + 1;
    
    // Buscar la línea que contiene el error
    if (strpos($line, 'version_compare( WC_VERSION,') !== false) {
        echo "<p style='color: orange;'>⚠️ Error encontrado en línea $line_number:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px;'>$line</pre>";
        
        // Corregir la línea
        $corrected_line = str_replace(
            'version_compare( WC_VERSION,',
            'defined( \'WC_VERSION\' ) && version_compare( WC_VERSION,',
            $line
        );
        
        echo "<p style='color: green;'>✅ Línea corregida:</p>";
        echo "<pre style='background: #e8f5e8; padding: 10px;'>$corrected_line</pre>";
        
        $lines[$index] = $corrected_line;
        $error_found = true;
        break;
    }
}

if (!$error_found) {
    echo "<p style='color: orange;'>⚠️ No se encontró el error específico. Buscando patrones similares...</p>";
    
    // Buscar otros patrones problemáticos
    foreach ($lines as $index => $line) {
        $line_number = $index + 1;
        
        if (strpos($line, 'WC_VERSION') !== false && strpos($line, 'defined') === false) {
            echo "<p style='color: orange;'>⚠️ Posible problema en línea $line_number:</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px;'>$line</pre>";
        }
    }
} else {
    // Guardar el archivo corregido
    $corrected_content = implode("\n", $lines);
    
    // Crear backup
    $backup_file = $plugin_file . '.backup.' . date('Y-m-d-H-i-s');
    if (copy($plugin_file, $backup_file)) {
        echo "<p>✅ Backup creado: $backup_file</p>";
    }
    
    // Guardar archivo corregido
    if (file_put_contents($plugin_file, $corrected_content) !== false) {
        echo "<p style='color: green; font-weight: bold;'>✅ Archivo corregido exitosamente</p>";
        echo "<p>El error fatal ha sido solucionado. Ahora puedes intentar activar el plugin nuevamente.</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al guardar el archivo corregido</p>";
    }
}

echo "<hr>";
echo "<h3>Resumen de la corrección:</h3>";
echo "<p><strong>Problema:</strong> El código usaba WC_VERSION sin verificar si la constante estaba definida.</p>";
echo "<p><strong>Solución:</strong> Se agregó defined('WC_VERSION') && antes de usar la constante.</p>";
echo "<p><strong>Línea corregida:</strong> Aproximadamente línea 85 en wp-cupon-whatsapp.php</p>";
?>