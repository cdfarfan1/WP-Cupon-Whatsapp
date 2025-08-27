<?php
/**
 * Script de diagnóstico para errores fatales del plugin WP Cupón WhatsApp
 * 
 * Este script ayuda a identificar y resolver errores de activación del plugin
 */

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Error Fatal - WP Cupón WhatsApp</h1>";
echo "<hr>";

// 1. Verificar versión de PHP
echo "<h2>1. Verificación de PHP</h2>";
echo "<strong>Versión de PHP:</strong> " . phpversion() . "<br>";
echo "<strong>Versión mínima requerida:</strong> 7.4<br>";

if (version_compare(phpversion(), '7.4', '<')) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERROR: PHP versión muy antigua. Se requiere PHP 7.4 o superior.</div>";
} else {
    echo "<div style='color: green; font-weight: bold;'>✅ Versión de PHP compatible</div>";
}

echo "<br>";

// 2. Verificar archivos del plugin
echo "<h2>2. Verificación de Archivos del Plugin</h2>";
$plugin_path = 'C:\\xampp\\htdocs\\webstore\\wp-content\\plugins\\wp-cupon-whatsapp';

echo "<strong>Ruta del plugin:</strong> $plugin_path<br>";

if (!is_dir($plugin_path)) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERROR: Carpeta del plugin no encontrada</div>";
    exit;
}

$required_files = [
    'wp-cupon-whatsapp.php' => 'Archivo principal del plugin',
    'admin/admin-menu.php' => 'Menú de administración',
    'includes/post-types.php' => 'Tipos de post personalizados',
    'includes/whatsapp-handlers.php' => 'Manejadores de WhatsApp',
    'includes/validation-enhanced.php' => 'Validaciones mejoradas',
    'public/shortcodes.php' => 'Shortcodes públicos'
];

foreach ($required_files as $file => $description) {
    $full_path = $plugin_path . '\\' . $file;
    if (file_exists($full_path)) {
        echo "✅ <strong>$description:</strong> $file<br>";
    } else {
        echo "❌ <strong>FALTA:</strong> $file - $description<br>";
    }
}

echo "<br>";

// 3. Verificar sintaxis de archivos principales
echo "<h2>3. Verificación de Sintaxis</h2>";

$main_files = [
    'wp-cupon-whatsapp.php',
    'admin/admin-menu.php',
    'includes/post-types.php'
];

foreach ($main_files as $file) {
    $full_path = $plugin_path . '\\' . $file;
    if (file_exists($full_path)) {
        $output = [];
        $return_code = 0;
        exec("php -l \"$full_path\"", $output, $return_code);
        
        if ($return_code === 0) {
            echo "✅ <strong>$file:</strong> Sintaxis correcta<br>";
        } else {
            echo "❌ <strong>$file:</strong> Error de sintaxis<br>";
            echo "<pre style='color: red;'>" . implode("\n", $output) . "</pre>";
        }
    }
}

echo "<br>";

// 4. Verificar logs de WordPress
echo "<h2>4. Verificación de Logs de WordPress</h2>";

$log_files = [
    'C:\\xampp\\htdocs\\webstore\\wp-content\\debug.log',
    'C:\\xampp\\htdocs\\webstore\\error_log',
    'C:\\xampp\\logs\\php_error_log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "<strong>Log encontrado:</strong> $log_file<br>";
        
        // Leer las últimas 20 líneas del log
        $lines = file($log_file);
        $recent_lines = array_slice($lines, -20);
        
        echo "<strong>Últimas entradas del log:</strong><br>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: scroll;'>";
        foreach ($recent_lines as $line) {
            if (stripos($line, 'wp-cupon-whatsapp') !== false || 
                stripos($line, 'fatal') !== false || 
                stripos($line, 'error') !== false) {
                echo "<span style='color: red; font-weight: bold;'>$line</span>";
            } else {
                echo htmlspecialchars($line);
            }
        }
        echo "</pre><br>";
    } else {
        echo "Log no encontrado: $log_file<br>";
    }
}

echo "<br>";

// 5. Verificar dependencias y conflictos
echo "<h2>5. Verificación de Dependencias</h2>";

// Verificar si WordPress está disponible
if (defined('ABSPATH')) {
    echo "✅ WordPress detectado<br>";
} else {
    echo "❌ WordPress no detectado (ejecutar desde WordPress)<br>";
}

// Verificar extensiones PHP requeridas
$required_extensions = ['json', 'curl', 'mbstring'];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extensión PHP '$ext' disponible<br>";
    } else {
        echo "❌ Extensión PHP '$ext' NO disponible<br>";
    }
}

echo "<br>";

// 6. Recomendaciones de solución
echo "<h2>6. Recomendaciones de Solución</h2>";
echo "<ol>";
echo "<li><strong>Verificar logs:</strong> Revisar los logs de WordPress para errores específicos</li>";
echo "<li><strong>Desactivar otros plugins:</strong> Temporalmente desactivar otros plugins para verificar conflictos</li>";
echo "<li><strong>Verificar permisos:</strong> Asegurar que los archivos tengan permisos correctos (644 para archivos, 755 para carpetas)</li>";
echo "<li><strong>Limpiar caché:</strong> Limpiar cualquier caché de WordPress o del servidor</li>";
echo "<li><strong>Modo debug:</strong> Activar WP_DEBUG en wp-config.php para más información</li>";
echo "</ol>";

echo "<br>";
echo "<h2>7. Configuración Recomendada para wp-config.php</h2>";
echo "<pre style='background: #f0f0f0; padding: 10px;'>";
echo "// Agregar estas líneas a wp-config.php para debug\n";
echo "define('WP_DEBUG', true);\n";
echo "define('WP_DEBUG_LOG', true);\n";
echo "define('WP_DEBUG_DISPLAY', false);\n";
echo "define('SCRIPT_DEBUG', true);\n";
echo "</pre>";

echo "<hr>";
echo "<p><strong>Diagnóstico completado.</strong> Revisa los resultados anteriores para identificar el problema.</p>";
?>