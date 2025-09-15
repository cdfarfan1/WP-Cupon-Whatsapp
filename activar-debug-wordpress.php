<?php
/**
 * Script para activar el registro de debug en WordPress local
 * Modifica wp-config.php para habilitar WP_DEBUG, WP_DEBUG_LOG y WP_DEBUG_DISPLAY
 */

echo "<h2>Activación de Debug en WordPress Local</h2>";

// Ruta al archivo wp-config.php
$wp_config_path = 'C:\\xampp\\htdocs\\webstore\\wp-config.php';

echo "<p>Verificando archivo wp-config.php...</p>";

if (!file_exists($wp_config_path)) {
    echo "<p style='color: red;'>❌ Archivo wp-config.php no encontrado en: $wp_config_path</p>";
    echo "<p>Por favor, verifica que WordPress esté instalado en la ruta correcta.</p>";
    exit;
}

echo "<p>✅ Archivo wp-config.php encontrado</p>";

// Leer el contenido actual
$content = file_get_contents($wp_config_path);

if ($content === false) {
    echo "<p style='color: red;'>❌ No se pudo leer el archivo wp-config.php</p>";
    exit;
}

echo "<p>✅ Archivo leído correctamente</p>";

// Crear backup
$backup_path = $wp_config_path . '.backup.' . date('Y-m-d-H-i-s');
if (copy($wp_config_path, $backup_path)) {
    echo "<p>✅ Backup creado: $backup_path</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No se pudo crear backup</p>";
}

// Configuraciones de debug a añadir/modificar
$debug_configs = [
    'WP_DEBUG' => 'true',
    'WP_DEBUG_LOG' => 'true',
    'WP_DEBUG_DISPLAY' => 'false',
    'SCRIPT_DEBUG' => 'true',
    'WP_DISABLE_FATAL_ERROR_HANDLER' => 'true'
];

echo "<h3>Configurando variables de debug:</h3>";

$modified = false;

foreach ($debug_configs as $constant => $value) {
    $pattern = "/define\s*\(\s*['\"]" . $constant . "['\"]\s*,\s*[^)]+\s*\)\s*;/";
    $replacement = "define( '" . $constant . "', " . $value . " );";
    
    if (preg_match($pattern, $content)) {
        // La constante ya existe, reemplazarla
        $content = preg_replace($pattern, $replacement, $content);
        echo "<p>🔄 Actualizada: $constant = $value</p>";
        $modified = true;
    } else {
        // La constante no existe, añadirla antes de la línea "That's all, stop editing!"
        $insert_before = "/* That's all, stop editing! Happy publishing. */";
        if (strpos($content, $insert_before) !== false) {
            $new_line = "\n// Debug configurado automáticamente\n" . $replacement . "\n";
            $content = str_replace($insert_before, $new_line . $insert_before, $content);
            echo "<p>➕ Añadida: $constant = $value</p>";
            $modified = true;
        }
    }
}

// Verificar que el directorio de logs existe
$wp_content_path = 'C:\\xampp\\htdocs\\webstore\\wp-content';
if (!is_dir($wp_content_path)) {
    echo "<p style='color: orange;'>⚠️ Directorio wp-content no encontrado: $wp_content_path</p>";
} else {
    echo "<p>✅ Directorio wp-content encontrado</p>";
    
    // Verificar permisos de escritura
    if (is_writable($wp_content_path)) {
        echo "<p>✅ Directorio wp-content tiene permisos de escritura</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Directorio wp-content podría no tener permisos de escritura</p>";
    }
}

if ($modified) {
    // Guardar el archivo modificado
    if (file_put_contents($wp_config_path, $content) !== false) {
        echo "<p style='color: green; font-weight: bold;'>✅ Configuración de debug activada exitosamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al guardar el archivo wp-config.php</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ No se realizaron cambios (configuración ya presente)</p>";
}

echo "<hr>";
echo "<h3>📋 Configuración de Debug Activada:</h3>";
echo "<ul>";
echo "<li><strong>WP_DEBUG:</strong> true - Habilita el modo debug</li>";
echo "<li><strong>WP_DEBUG_LOG:</strong> true - Guarda errores en archivo de log</li>";
echo "<li><strong>WP_DEBUG_DISPLAY:</strong> false - No muestra errores en pantalla</li>";
echo "<li><strong>SCRIPT_DEBUG:</strong> true - Usa versiones no minificadas de scripts</li>";
echo "<li><strong>WP_DISABLE_FATAL_ERROR_HANDLER:</strong> true - Desactiva el manejador de errores fatales</li>";
echo "</ul>";

echo "<h3>📁 Ubicación del archivo de log:</h3>";
echo "<p><code>C:\\xampp\\htdocs\\webstore\\wp-content\\debug.log</code></p>";

echo "<h3>🔍 Cómo ver los logs:</h3>";
echo "<ol>";
echo "<li>Navega a: <code>C:\\xampp\\htdocs\\webstore\\wp-content\\</code></li>";
echo "<li>Busca el archivo <code>debug.log</code></li>";
echo "<li>Ábrelo con un editor de texto</li>";
echo "<li>Los nuevos errores aparecerán al final del archivo</li>";
echo "</ol>";

echo "<h3>⚠️ Importante:</h3>";
echo "<p style='color: orange;'>Recuerda desactivar el debug en producción por seguridad.</p>";
echo "<p>Para desactivar, cambia todas las constantes a <code>false</code> en wp-config.php</p>";

echo "<hr>";
echo "<p><strong>Debug activado exitosamente. Ahora puedes ver los errores del plugin en el archivo de log.</strong></p>";
?>