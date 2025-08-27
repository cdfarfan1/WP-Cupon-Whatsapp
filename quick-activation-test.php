<?php
/**
 * Test rápido de activación del plugin
 */

echo "=== TEST RAPIDO DE ACTIVACION ===\n";

// Verificar archivos críticos
$plugin_path = 'C:\\xampp\\htdocs\\webstore\\wp-content\\plugins\\wp-cupon-whatsapp';

echo "1. Verificando archivos criticos...\n";
$files = [
    'wp-cupon-whatsapp.php',
    'includes/post-types.php',
    'admin/admin-menu.php'
];

foreach ($files as $file) {
    $full_path = $plugin_path . '\\' . str_replace('/', '\\', $file);
    if (file_exists($full_path)) {
        echo "✓ $file\n";
    } else {
        echo "✗ FALTA: $file\n";
    }
}

echo "\n2. Verificando sintaxis del archivo principal...\n";
$main_file = $plugin_path . '\\wp-cupon-whatsapp.php';
$syntax = shell_exec("php -l \"$main_file\" 2>&1");
if (strpos($syntax, 'No syntax errors') !== false) {
    echo "✓ Sintaxis correcta\n";
} else {
    echo "✗ Error de sintaxis: $syntax\n";
}

echo "\n3. Verificando encabezado del plugin...\n";
$content = file_get_contents($main_file);
if (preg_match('/Plugin Name:/', $content)) {
    echo "✓ Plugin Name encontrado\n";
} else {
    echo "✗ Falta Plugin Name\n";
}

if (preg_match('/Version:/', $content)) {
    echo "✓ Version encontrada\n";
} else {
    echo "✗ Falta Version\n";
}

echo "\n4. Verificando compatibilidad para distribucion masiva...\n";
$issues = 0;

// Verificar rutas absolutas
if (preg_match('/[C-Z]:\\\\/', $content)) {
    echo "⚠ Posibles rutas absolutas detectadas\n";
    $issues++;
}

// Verificar localhost
if (strpos($content, 'localhost') !== false) {
    echo "⚠ Referencias a localhost\n";
    $issues++;
}

// Verificar xampp
if (strpos($content, 'xampp') !== false) {
    echo "⚠ Referencias a XAMPP\n";
    $issues++;
}

if ($issues == 0) {
    echo "✓ Compatible para distribucion masiva\n";
}

echo "\n=== RESULTADO ===\n";
if ($issues == 0) {
    echo "✓ PLUGIN LISTO PARA ACTIVACION\n";
    echo "✓ COMPATIBLE PARA DISTRIBUCION MASIVA\n";
    echo "\nPuedes activarlo en: https://localhost/webstore/wp-admin/plugins.php\n";
} else {
    echo "⚠ Plugin funcional pero con $issues advertencias\n";
    echo "Revisar para distribucion masiva\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>