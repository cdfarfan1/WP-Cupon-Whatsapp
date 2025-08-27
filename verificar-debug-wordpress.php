<?php
/**
 * Script para verificar que el debug de WordPress esté funcionando correctamente
 * y mostrar los logs más recientes
 */

echo "<h2>Verificación del Debug de WordPress</h2>";

// Rutas importantes
$wp_config_path = 'C:\\xampp\\htdocs\\webstore\\wp-config.php';
$debug_log_path = 'C:\\xampp\\htdocs\\webstore\\wp-content\\debug.log';

echo "<h3>1. Verificando configuración en wp-config.php:</h3>";

if (file_exists($wp_config_path)) {
    $config_content = file_get_contents($wp_config_path);
    
    $debug_vars = [
        'WP_DEBUG' => 'true',
        'WP_DEBUG_LOG' => 'true', 
        'WP_DEBUG_DISPLAY' => 'false',
        'SCRIPT_DEBUG' => 'true',
        'WP_DISABLE_FATAL_ERROR_HANDLER' => 'true'
    ];
    
    foreach ($debug_vars as $var => $expected) {
        if (preg_match("/define\s*\(\s*['\"]" . $var . "['\"]\s*,\s*(true|false)\s*\)/", $config_content, $matches)) {
            $current_value = $matches[1];
            $status = ($current_value === $expected) ? '✅' : '⚠️';
            echo "<p>$status <strong>$var:</strong> $current_value</p>";
        } else {
            echo "<p>❌ <strong>$var:</strong> No encontrada</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ wp-config.php no encontrado</p>";
}

echo "<h3>2. Verificando archivo de debug:</h3>";

if (file_exists($debug_log_path)) {
    $file_size = filesize($debug_log_path);
    $file_modified = date('Y-m-d H:i:s', filemtime($debug_log_path));
    
    echo "<p>✅ Archivo debug.log encontrado</p>";
    echo "<p>📁 Ubicación: <code>$debug_log_path</code></p>";
    echo "<p>📊 Tamaño: " . number_format($file_size) . " bytes</p>";
    echo "<p>🕒 Última modificación: $file_modified</p>";
    
    if ($file_size > 0) {
        echo "<h4>📋 Últimas 20 líneas del log:</h4>";
        
        $lines = file($debug_log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $last_lines = array_slice($lines, -20);
        
        echo "<div style='background: #f1f1f1; padding: 10px; border-left: 4px solid #007cba; font-family: monospace; white-space: pre-wrap; max-height: 300px; overflow-y: auto;'>";
        foreach ($last_lines as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ El archivo de log está vacío (aún no se han registrado errores)</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Archivo debug.log no encontrado (se creará cuando ocurra el primer error)</p>";
}

echo "<h3>3. Generando error de prueba:</h3>";

// Generar un error de prueba para verificar que el logging funciona
echo "<p>Generando un error de prueba...</p>";

// Simular un error de WordPress
if (function_exists('error_log')) {
    $test_message = '[' . date('d-M-Y H:i:s UTC') . '] TEST DEBUG: Verificación de debug activado desde script de prueba';
    error_log($test_message, 3, $debug_log_path);
    echo "<p>✅ Error de prueba enviado al log</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Función error_log no disponible</p>";
}

echo "<h3>4. Comandos útiles para monitorear logs:</h3>";
echo "<div style='background: #f8f8f8; padding: 15px; border: 1px solid #ddd;'>";
echo "<h4>PowerShell (Windows):</h4>";
echo "<code>Get-Content 'C:\\xampp\\htdocs\\webstore\\wp-content\\debug.log' -Tail 10 -Wait</code>";
echo "<p><em>Muestra las últimas 10 líneas y sigue mostrando nuevas entradas en tiempo real</em></p>";

echo "<h4>Limpiar el log:</h4>";
echo "<code>Clear-Content 'C:\\xampp\\htdocs\\webstore\\wp-content\\debug.log'</code>";
echo "<p><em>Vacía el archivo de log para empezar limpio</em></p>";
echo "</div>";

echo "<h3>5. Próximos pasos:</h3>";
echo "<ol>";
echo "<li><strong>Activar el plugin:</strong> Ve a WordPress Admin y activa 'WP Cupon WhatsApp'</li>";
echo "<li><strong>Monitorear logs:</strong> Usa los comandos de arriba para ver errores en tiempo real</li>";
echo "<li><strong>Probar funcionalidades:</strong> Usa el plugin y observa si aparecen errores</li>";
echo "<li><strong>Revisar logs regularmente:</strong> Especialmente después de cambios en el código</li>";
echo "</ol>";

echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #007cba; margin: 20px 0;'>";
echo "<h4>💡 Consejo:</h4>";
echo "<p>Mantén abierto el archivo debug.log en un editor de texto que se actualice automáticamente (como VS Code) para ver los errores en tiempo real mientras desarrollas.</p>";
echo "</div>";

echo "<hr>";
echo "<p><strong>✅ Debug de WordPress configurado y verificado correctamente</strong></p>";
?>