<?php
/**
 * Script para crear archivos faltantes del plugin WP Cupon WhatsApp
 * Ejecutar desde línea de comandos: php crear-archivos-faltantes.php
 */

echo "=== CREACION DE ARCHIVOS FALTANTES ===\n";
echo "Plugin: WP Cupon WhatsApp\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Lista de archivos que el plugin intenta cargar
$archivosRequeridos = [
    'includes/customer-fields.php',
    'includes/recaptcha-integration.php',
    'includes/application-processing.php',
    'includes/ajax-handlers.php',
    'includes/rest-api.php',
    'includes/redemption-logic.php',
    'includes/stats-functions.php',
    'includes/export-functions.php',
    'public/shortcodes.php',
    'public/my-account-endpoints.php',
    'debug-output.php',
    'debug-headers.php',
    'test-headers.php',
    'admin/institution-stats-page.php',
    'elementor/elementor-addon.php'
];

echo "1. VERIFICANDO ARCHIVOS EN DIRECTORIO LOCAL:\n\n";

$archivosFaltantes = [];
foreach ($archivosRequeridos as $archivo) {
    if (file_exists($archivo)) {
        echo "✓ $archivo\n";
    } else {
        echo "✗ FALTA: $archivo\n";
        $archivosFaltantes[] = $archivo;
    }
}

echo "\n2. VERIFICANDO SINTAXIS DEL ARCHIVO PRINCIPAL:\n";
$archivoMain = 'wp-cupon-whatsapp.php';
if (file_exists($archivoMain)) {
    $sintaxis = shell_exec("php -l $archivoMain 2>&1");
    if (strpos($sintaxis, 'No syntax errors') !== false) {
        echo "✓ Sintaxis correcta en $archivoMain\n";
    } else {
        echo "✗ Error de sintaxis en $archivoMain\n";
        echo $sintaxis . "\n";
    }
} else {
    echo "✗ Archivo principal no encontrado: $archivoMain\n";
}

echo "\n=== RESUMEN ===\n";
echo "Archivos faltantes: " . count($archivosFaltantes) . "\n";

if (count($archivosFaltantes) > 0) {
    echo "\nARCHIVOS FALTANTES:\n";
    foreach ($archivosFaltantes as $archivo) {
        echo "  - $archivo\n";
    }
    
    echo "\nCreando archivos faltantes automáticamente...\n";
    
    foreach ($archivosFaltantes as $archivo) {
        $directorio = dirname($archivo);
        
        // Crear directorio si no existe
        if ($directorio && $directorio !== '.' && !is_dir($directorio)) {
            mkdir($directorio, 0755, true);
            echo "✓ Directorio creado: $directorio\n";
        }
        
        // Crear archivo con contenido básico
        $contenidoBasico = <<<'PHP'
<?php
/**
 * Plugin: WP Cupon WhatsApp
 * Generado automáticamente
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// TODO: Implementar funcionalidad específica
PHP;
        
        file_put_contents($archivo, $contenidoBasico);
        echo "✓ Archivo creado: $archivo\n";
    }
    
    echo "\n✓ Archivos faltantes creados exitosamente\n";
    echo "Ahora puedes intentar activar el plugin nuevamente\n";
} else {
    echo "✓ Todos los archivos requeridos están presentes\n";
}

echo "\nScript completado.\n";
?>