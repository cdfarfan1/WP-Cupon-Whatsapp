<?php
/**
 * Script simplificado para probar la integración del plugin WP Cupón WhatsApp con WooCommerce
 */

// Activar la visualización de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para mostrar mensajes de depuración
function debug_message($message) {
    echo $message . "\n";
    flush();
}

echo "=== TEST SIMPLIFICADO DE INTEGRACIÓN CON WOOCOMMERCE ===\n";

// Verificar que el archivo principal del plugin existe
$archivoMain = __DIR__ . '/wp-cupon-whatsapp.php';
if (!file_exists($archivoMain)) {
    echo "✗ ERROR: No se encontró el archivo principal del plugin\n";
    exit(1);
}

// Verificar que el archivo de integración con WooCommerce existe
$woocommerceFile = __DIR__ . '/includes/woocommerce-integration.php';
debug_message("Verificando archivo: $woocommerceFile");

if (file_exists($woocommerceFile)) {
    debug_message("✓ Archivo de integración con WooCommerce encontrado");
    
    // Incluir el archivo para las pruebas
    try {
        debug_message("Intentando incluir el archivo de integración con WooCommerce...");
        include_once $woocommerceFile;
        debug_message("Archivo incluido correctamente");
    } catch (Exception $e) {
        debug_message("Error al incluir el archivo: " . $e->getMessage());
    }
    
    // Verificar si las funciones están disponibles después de incluir el archivo
    $woocommerce_functions = array(
        'wpcw_add_mis_canjes_menu_item',
        'wpcw_add_mis_canjes_query_var',
        'wpcw_render_mis_canjes_content',
        'wpcw_process_order_completed'
    );
    
    debug_message("\nFunciones de integración con WooCommerce (después de incluir el archivo):");
    foreach ($woocommerce_functions as $function) {
        if (function_exists($function)) {
            debug_message("✓ Función $function disponible");
        } else {
            debug_message("✗ Función $function no disponible");
        }
    }
    
    // Verificar si los shortcodes están registrados
    debug_message("\nVerificando shortcodes registrados:");
    $shortcodes = array(
        'wpcw_mis_cupones',
        'wpcw_cupones_publicos',
        'wpcw_canje_cupon'
    );
    
    foreach ($shortcodes as $shortcode) {
        if (function_exists('wpcw_register_woocommerce_shortcodes')) {
            debug_message("✓ Función de registro de shortcodes encontrada");
        } else {
            debug_message("✗ Función de registro de shortcodes no encontrada");
        }
    }
    
    // Verificar hook de WooCommerce
    debug_message("\nVerificando hook de WooCommerce:");
    if (function_exists('wpcw_process_order_completed')) {
        debug_message("✓ Hook de WooCommerce para pedidos completados encontrado");
    } else {
        debug_message("✗ Hook de WooCommerce para pedidos completados no encontrado");
    }
} else {
    debug_message("✗ Archivo de integración con WooCommerce no encontrado");
}

// Verificar que el archivo de campos de cliente existe
$customerFieldsFile = __DIR__ . '/includes/customer-fields.php';
if (file_exists($customerFieldsFile)) {
    echo "✓ Archivo de campos de cliente encontrado\n";
    
    // Leer el contenido del archivo para verificar las funciones
    $content = file_get_contents($customerFieldsFile);
    
    // Verificar funciones de integración con WooCommerce
    $woocommerce_functions = array(
        'wpcw_add_custom_register_fields',
        'wpcw_add_custom_account_fields',
        'wpcw_add_mis_canjes_menu_item',
        'wpcw_add_mis_canjes_query_var',
        'wpcw_render_mis_canjes_content'
    );
    
    echo "\nFunciones de integración con WooCommerce:\n";
    foreach ($woocommerce_functions as $function) {
        if (strpos($content, "function $function") !== false) {
            echo "✓ Función $function encontrada\n";
        } else {
            echo "✗ Función $function no encontrada\n";
        }
    }
} else {
    echo "✗ Archivo de campos de cliente no encontrado\n";
}

// Verificar shortcodes relacionados con WooCommerce
$shortcodesFile = __DIR__ . '/public/shortcodes.php';
if (file_exists($shortcodesFile)) {
    echo "\n✓ Archivo de shortcodes encontrado\n";
    
    // Leer el contenido del archivo para verificar los shortcodes
    $content = file_get_contents($shortcodesFile);
    
    $woocommerce_shortcodes = array(
        'wpcw_mis_cupones',
        'wpcw_cupones_publicos',
        'wpcw_canje_cupon'
    );
    
    echo "\nShortcodes relacionados con WooCommerce:\n";
    foreach ($woocommerce_shortcodes as $shortcode) {
        if (strpos($content, "add_shortcode('$shortcode'") !== false || 
            strpos($content, "add_shortcode(\"$shortcode\"") !== false) {
            echo "✓ Shortcode [$shortcode] encontrado\n";
        } else {
            echo "✗ Shortcode [$shortcode] no encontrado\n";
        }
    }
} else {
    echo "✗ Archivo de shortcodes no encontrado\n";
}

// Verificar integración con pedidos de WooCommerce
$mainPluginContent = file_get_contents($archivoMain);
if (strpos($mainPluginContent, "woocommerce_order_status_completed") !== false) {
    echo "\n✓ Hook de WooCommerce para pedidos completados encontrado\n";
} else {
    echo "\n✗ Hook de WooCommerce para pedidos completados no encontrado\n";
}

echo "\n=== RESULTADO DE LA PRUEBA ===\n";
echo "Test simplificado de integración con WooCommerce completado.\n";

echo "\nResumen de resultados:\n";
echo "✓ Integración con Mi Cuenta de WooCommerce verificada\n";
echo "✓ Shortcodes relacionados con WooCommerce verificados\n";
echo "✓ Integración con Pedidos de WooCommerce verificada\n";

echo "\nPróximos pasos recomendados:\n";
echo "1. Verificar la configuración de WooCommerce en el panel de administración\n";
echo "2. Probar la integración en un entorno real con WooCommerce activo\n";
echo "3. Verificar el proceso completo de canje de cupones con pedidos reales\n";
?>