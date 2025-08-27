<?php
/**
 * Script para corregir archivos faltantes en wp-cupon-whatsapp.php
 * Identifica y comenta las líneas que requieren archivos inexistentes
 */

echo "<h2>Corrección de Archivos Faltantes en wp-cupon-whatsapp.php</h2>";

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

// Lista de archivos que deben verificarse
$files_to_check = [
    'includes/customer-fields.php',
    'includes/recaptcha-integration.php',
    'includes/application-processing.php',
    'includes/ajax-handlers.php',
    'includes/rest-api.php',
    'includes/redemption-logic.php',
    'includes/stats-functions.php',
    'includes/export-functions.php',
    'debug-output.php',
    'debug-headers.php',
    'test-headers.php',
    'admin/institution-stats-page.php'
];

echo "<h3>Verificando archivos requeridos:</h3>";

$lines = explode("\n", $content);
$changes_made = false;
$missing_files = [];

foreach ($files_to_check as $file) {
    $file_path = __DIR__ . '/' . $file;
    $exists = file_exists($file_path);
    
    echo "<p>" . ($exists ? "✅" : "❌") . " $file</p>";
    
    if (!$exists) {
        $missing_files[] = $file;
        
        // Buscar y comentar la línea que requiere este archivo
        foreach ($lines as $index => $line) {
            if (strpos($line, "require_once WPCW_PLUGIN_DIR . '$file'") !== false) {
                $line_number = $index + 1;
                echo "<p style='color: orange;'>⚠️ Comentando línea $line_number: $file</p>";
                
                // Comentar la línea
                $lines[$index] = "    // " . trim($line) . " // Archivo no existe - comentado automáticamente";
                $changes_made = true;
            }
        }
    }
}

if ($changes_made) {
    // Crear backup
    $backup_file = $plugin_file . '.backup.' . date('Y-m-d-H-i-s');
    if (copy($plugin_file, $backup_file)) {
        echo "<p>✅ Backup creado: $backup_file</p>";
    }
    
    // Guardar archivo corregido
    $corrected_content = implode("\n", $lines);
    
    if (file_put_contents($plugin_file, $corrected_content) !== false) {
        echo "<p style='color: green; font-weight: bold;'>✅ Archivo corregido exitosamente</p>";
        echo "<p>Se han comentado las líneas que requieren archivos inexistentes.</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al guardar el archivo corregido</p>";
    }
} else {
    echo "<p style='color: green;'>✅ No se encontraron archivos faltantes que requieran corrección</p>";
}

echo "<hr>";
echo "<h3>Resumen:</h3>";
if (!empty($missing_files)) {
    echo "<p><strong>Archivos faltantes encontrados:</strong></p>";
    echo "<ul>";
    foreach ($missing_files as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
    echo "<p><strong>Acción:</strong> Las líneas que requieren estos archivos han sido comentadas para evitar errores fatales.</p>";
} else {
    echo "<p><strong>Todos los archivos requeridos existen.</strong></p>";
}

echo "<p><strong>Recomendación:</strong> Crear los archivos faltantes o eliminar las referencias si no son necesarios.</p>";
?>