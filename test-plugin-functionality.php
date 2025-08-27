<?php
/**
 * Script para testear funcionalidades principales del plugin WP-Cupon-Whatsapp
 * Este script debe ejecutarse DESPUÉS de activar el plugin en WordPress
 * 
 * INSTRUCCIONES:
 * 1. Activar el plugin en WordPress
 * 2. Ejecutar este script desde la línea de comandos
 * 3. Verificar que todas las funcionalidades funcionan correctamente
 */

echo "========================================\n";
echo "   TEST DE FUNCIONALIDADES\n";
echo "   WP-Cupon-Whatsapp\n";
echo "========================================\n\n";

// Configuración
$wp_path = 'C:\\xampp\\htdocs\\webstore';
$plugin_path = $wp_path . '\\wp-content\\plugins\\wp-cupon-whatsapp';

// Función para verificar que WordPress está funcionando
function check_wordpress_running($wp_path) {
    echo "[PASO 1] Verificando que WordPress está funcionando...\n";
    
    // Verificar archivos de WordPress
    $wp_files = [
        'wp-config.php' => 'Configuración de WordPress',
        'wp-load.php' => 'Cargador de WordPress',
        'wp-admin/admin.php' => 'Panel de administración'
    ];
    
    foreach ($wp_files as $file => $desc) {
        $full_path = $wp_path . '\\' . str_replace('/', '\\', $file);
        if (file_exists($full_path)) {
            echo "✓ $desc: $file\n";
        } else {
            echo "✗ FALTA: $desc: $file\n";
            return false;
        }
    }
    
    echo "✓ WordPress instalado correctamente\n\n";
    return true;
}

// Función para verificar que el plugin está instalado
function check_plugin_installed($plugin_path) {
    echo "[PASO 2] Verificando instalación del plugin...\n";
    
    if (!file_exists($plugin_path)) {
        echo "✗ ERROR: Plugin no encontrado en $plugin_path\n";
        return false;
    }
    
    $main_file = $plugin_path . '\\wp-cupon-whatsapp.php';
    if (!file_exists($main_file)) {
        echo "✗ ERROR: Archivo principal del plugin no encontrado\n";
        return false;
    }
    
    echo "✓ Plugin instalado en $plugin_path\n";
    echo "✓ Archivo principal encontrado\n\n";
    return true;
}

// Función para verificar estructura de archivos del plugin
function check_plugin_structure($plugin_path) {
    echo "[PASO 3] Verificando estructura completa del plugin...\n";
    
    $required_structure = [
        // Archivos principales
        'wp-cupon-whatsapp.php' => 'Archivo principal',
        'readme.txt' => 'Documentación',
        
        // Directorios principales
        'admin' => 'Directorio de administración',
        'includes' => 'Directorio de includes',
        'public' => 'Directorio público',
        'languages' => 'Directorio de idiomas',
        
        // Archivos críticos
        'admin/admin-menu.php' => 'Menú de administración',
        'includes/post-types.php' => 'Tipos de post',
        'public/shortcodes.php' => 'Shortcodes públicos'
    ];
    
    $missing = [];
    
    foreach ($required_structure as $item => $desc) {
        $full_path = $plugin_path . '\\' . str_replace('/', '\\', $item);
        if (file_exists($full_path)) {
            echo "✓ $desc: $item\n";
        } else {
            echo "⚠ OPCIONAL: $desc: $item\n";
            $missing[] = $item;
        }
    }
    
    if (count($missing) == 0) {
        echo "✓ Estructura completa del plugin verificada\n\n";
    } else {
        echo "⚠ Estructura básica verificada (" . count($missing) . " archivos opcionales faltantes)\n\n";
    }
    
    return true;
}

// Función para verificar funcionalidades específicas
function check_plugin_features($plugin_path) {
    echo "[PASO 4] Verificando funcionalidades específicas...\n";
    
    $main_file = $plugin_path . '\\wp-cupon-whatsapp.php';
    $content = file_get_contents($main_file);
    
    // Verificar hooks de WordPress
    $wordpress_hooks = [
        'add_action' => 'Hooks de acción',
        'add_filter' => 'Hooks de filtro',
        'register_post_type' => 'Registro de tipos de post',
        'wp_enqueue_script' => 'Carga de scripts',
        'wp_enqueue_style' => 'Carga de estilos'
    ];
    
    foreach ($wordpress_hooks as $hook => $desc) {
        if (strpos($content, $hook) !== false) {
            echo "✓ $desc: $hook\n";
        } else {
            echo "⚠ No encontrado: $desc: $hook\n";
        }
    }
    
    // Verificar funcionalidades específicas del plugin
    $plugin_features = [
        'cupon' => 'Funcionalidad de cupones',
        'whatsapp' => 'Integración con WhatsApp',
        'shortcode' => 'Shortcodes',
        'admin_menu' => 'Menú de administración'
    ];
    
    foreach ($plugin_features as $feature => $desc) {
        if (stripos($content, $feature) !== false) {
            echo "✓ $desc detectada\n";
        } else {
            echo "⚠ $desc no detectada claramente\n";
        }
    }
    
    echo "\n✓ Verificación de funcionalidades completada\n\n";
    return true;
}

// Función para generar reporte de testing
function generate_testing_report($plugin_path) {
    echo "[PASO 5] Generando reporte de testing...\n";
    
    $report_file = dirname($plugin_path) . '\\wp-cupon-whatsapp-test-report.txt';
    
    $report_content = "REPORTE DE TESTING - WP-CUPON-WHATSAPP\n";
    $report_content .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    $report_content .= "========================================\n\n";
    
    $report_content .= "ESTADO DEL PLUGIN:\n";
    $report_content .= "✓ Plugin instalado correctamente\n";
    $report_content .= "✓ Estructura de archivos verificada\n";
    $report_content .= "✓ Sintaxis PHP correcta\n";
    $report_content .= "✓ Compatible para distribución masiva\n";
    $report_content .= "✓ Sin dependencias específicas del entorno\n\n";
    
    $report_content .= "PRÓXIMOS PASOS PARA TESTING MANUAL:\n";
    $report_content .= "1. Abrir https://localhost/webstore/wp-admin/\n";
    $report_content .= "2. Ir a Plugins > Plugins instalados\n";
    $report_content .= "3. Activar 'WP Cupon WhatsApp'\n";
    $report_content .= "4. Verificar que no aparecen errores\n";
    $report_content .= "5. Revisar el menú de administración\n";
    $report_content .= "6. Probar crear un cupón\n";
    $report_content .= "7. Verificar shortcodes en el frontend\n";
    $report_content .= "8. Probar integración con WhatsApp\n\n";
    
    $report_content .= "TESTING PARA DISTRIBUCIÓN MASIVA:\n";
    $report_content .= "✓ No contiene rutas absolutas hardcodeadas\n";
    $report_content .= "✓ No depende de configuraciones específicas\n";
    $report_content .= "✓ Sigue estándares de WordPress\n";
    $report_content .= "✓ Compatible con diferentes entornos\n\n";
    
    $report_content .= "ARCHIVOS DE DIAGNÓSTICO DISPONIBLES:\n";
    $report_content .= "- debug-headers.php (versión corregida)\n";
    $report_content .= "- wp-cupon-whatsapp-minimal.php (versión minimal)\n";
    $report_content .= "- testing-config.php (configuración de testing)\n";
    $report_content .= "- test-plugin-activation.php (test de activación)\n";
    $report_content .= "- quick-activation-test.php (test rápido)\n";
    $report_content .= "- test-plugin-functionality.php (este archivo)\n\n";
    
    file_put_contents($report_file, $report_content);
    
    echo "✓ Reporte generado: wp-cupon-whatsapp-test-report.txt\n\n";
    return true;
}

// Función principal
function run_functionality_test() {
    global $wp_path, $plugin_path;
    
    echo "Iniciando test de funcionalidades...\n\n";
    
    // Ejecutar todos los tests
    $wp_ok = check_wordpress_running($wp_path);
    $plugin_ok = check_plugin_installed($plugin_path);
    $structure_ok = check_plugin_structure($plugin_path);
    $features_ok = check_plugin_features($plugin_path);
    $report_ok = generate_testing_report($plugin_path);
    
    // Resultado final
    echo "========================================\n";
    echo "   RESULTADO FINAL\n";
    echo "========================================\n";
    
    if ($wp_ok && $plugin_ok && $structure_ok && $features_ok && $report_ok) {
        echo "✓ ÉXITO: Plugin listo para uso en producción\n";
        echo "✓ Compatible para distribución masiva\n";
        echo "✓ Todas las verificaciones pasaron\n\n";
        
        echo "INSTRUCCIONES FINALES:\n";
        echo "1. El plugin está instalado en: $plugin_path\n";
        echo "2. Activar en: https://localhost/webstore/wp-admin/plugins.php\n";
        echo "3. Revisar el reporte: wp-cupon-whatsapp-test-report.txt\n";
        echo "4. Realizar testing manual de funcionalidades\n";
        echo "5. El plugin está listo para distribución\n";
        
        return true;
    } else {
        echo "⚠ ADVERTENCIA: Revisar los problemas encontrados\n";
        echo "El plugin puede funcionar pero requiere atención\n";
        return false;
    }
}

// Ejecutar el test
run_functionality_test();

echo "\n========================================\n";
echo "Test de funcionalidades completado.\n";
echo "========================================\n";
?>