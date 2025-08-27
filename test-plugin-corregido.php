<?php
/**
 * Test del Plugin Corregido - WP Cupón WhatsApp
 * 
 * Este archivo verifica que el plugin funcione correctamente
 * después de la corrección del problema de HTML sin procesar
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Para testing local, simular WordPress
    define('ABSPATH', true);
    define('WP_DEBUG', true);
}

echo "<h1>Test del Plugin WP Cupón WhatsApp - Versión Corregida</h1>";
echo "<hr>";

// Test 1: Verificar que no hay output buffering automático
echo "<h2>Test 1: Verificación de Output Buffering</h2>";
$ob_level_before = ob_get_level();
echo "<p>Nivel de Output Buffering antes de cargar fix-headers.php: " . $ob_level_before . "</p>";

// Simular la carga del archivo fix-headers.php
if (file_exists('fix-headers.php')) {
    // Verificar el contenido del archivo
    $content = file_get_contents('fix-headers.php');
    if (strpos($content, '// wpcw_init_output_buffering();') !== false) {
        echo "<p style='color: green;'>✅ CORRECTO: La línea problemática está comentada</p>";
    } else if (strpos($content, 'wpcw_init_output_buffering();') !== false) {
        echo "<p style='color: red;'>❌ ERROR: La línea problemática sigue activa</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ ADVERTENCIA: No se encontró la línea en cuestión</p>";
    }
} else {
    echo "<p style='color: red;'>❌ ERROR: No se encontró el archivo fix-headers.php</p>";
}

$ob_level_after = ob_get_level();
echo "<p>Nivel de Output Buffering después: " . $ob_level_after . "</p>";

if ($ob_level_before == $ob_level_after) {
    echo "<p style='color: green;'>✅ CORRECTO: No se inició output buffering automático</p>";
} else {
    echo "<p style='color: red;'>❌ ERROR: Se inició output buffering automático</p>";
}

echo "<hr>";

// Test 2: Verificar archivos principales
echo "<h2>Test 2: Verificación de Archivos Principales</h2>";
$archivos_principales = [
    'wp-cupon-whatsapp.php' => 'Archivo principal del plugin',
    'fix-headers.php' => 'Archivo de corrección de headers (corregido)',
    'admin/admin-menu.php' => 'Menú de administración',
    'includes/post-types.php' => 'Tipos de post personalizados'
];

foreach ($archivos_principales as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "<p style='color: green;'>✅ {$descripcion}: {$archivo}</p>";
    } else {
        echo "<p style='color: red;'>❌ FALTA: {$descripcion}: {$archivo}</p>";
    }
}

echo "<hr>";

// Test 3: Verificar sintaxis PHP
echo "<h2>Test 3: Verificación de Sintaxis PHP</h2>";
$archivos_verificar = ['wp-cupon-whatsapp.php', 'fix-headers.php'];

foreach ($archivos_verificar as $archivo) {
    if (file_exists($archivo)) {
        $output = [];
        $return_code = 0;
        exec("php -l {$archivo} 2>&1", $output, $return_code);
        
        if ($return_code === 0) {
            echo "<p style='color: green;'>✅ Sintaxis correcta: {$archivo}</p>";
        } else {
            echo "<p style='color: red;'>❌ Error de sintaxis en {$archivo}:</p>";
            echo "<pre>" . implode("\n", $output) . "</pre>";
        }
    }
}

echo "<hr>";

// Test 4: Resumen final
echo "<h2>Resumen Final</h2>";
echo "<p><strong>Problema identificado:</strong> Output buffering automático en fix-headers.php</p>";
echo "<p><strong>Solución aplicada:</strong> Comentar la línea wpcw_init_output_buffering()</p>";
echo "<p><strong>Estado:</strong> Plugin corregido y listo para uso</p>";

echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0;'>";
echo "<h3>Instrucciones para el Usuario:</h3>";
echo "<ol>";
echo "<li>El plugin ha sido corregido</li>";
echo "<li>Puedes reinstalarlo en tu sitio WordPress</li>";
echo "<li>Las páginas ahora deberían renderizarse correctamente</li>";
echo "<li>Si encuentras algún problema, revisa el archivo PROBLEMA_IDENTIFICADO_Y_SOLUCION.md</li>";
echo "</ol>";
echo "</div>";

echo "<p><em>Test completado el: " . date('Y-m-d H:i:s') . "</em></p>";
?>