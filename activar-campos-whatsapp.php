<?php
/**
 * Script para activar los campos de WhatsApp en Mi Cuenta
 * 
 * Este script verifica y activa la configuración necesaria para que
 * los campos personalizados aparezcan en la página "Mi Cuenta" de WooCommerce.
 */

// Cargar WordPress
require_once('../../../wp-config.php');

// Verificar que WordPress esté cargado
if (!defined('ABSPATH')) {
    die('Error: No se pudo cargar WordPress');
}

// Verificar que WooCommerce esté activo
if (!class_exists('WooCommerce')) {
    die('Error: WooCommerce no está activo');
}

// Verificar que el plugin esté activo
if (!defined('WPCW_VERSION')) {
    die('Error: El plugin WP Cupón WhatsApp no está activo');
}

echo "<h1>Activación de Campos de WhatsApp en Mi Cuenta</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .warning { color: orange; }
    .section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #0073aa; }
</style>";

// 1. Verificar configuración actual
echo "<div class='section'>";
echo "<h2>1. Configuración Actual de Campos Requeridos</h2>";
$required_settings = get_option('wpcw_required_fields_settings', array());

if (empty($required_settings)) {
    echo "<p class='warning'>⚠ No hay configuración de campos requeridos</p>";
} else {
    echo "<p class='info'>Configuración actual:</p>";
    echo "<ul>";
    foreach ($required_settings as $field => $value) {
        $status = ($value === '1') ? 'Activado' : 'Desactivado';
        $class = ($value === '1') ? 'success' : 'error';
        echo "<li class='{$class}'>{$field}: {$status}</li>";
    }
    echo "</ul>";
}
echo "</div>";

// 2. Activar campos requeridos
echo "<div class='section'>";
echo "<h2>2. Activando Campos Requeridos</h2>";

$new_settings = array(
    'dni_number' => '1',
    'birth_date' => '1', 
    'whatsapp_number' => '1',
    'user_institution_id' => '0',
    'user_favorite_coupon_categories' => '0'
);

$update_result = update_option('wpcw_required_fields_settings', $new_settings);

if ($update_result) {
    echo "<p class='success'>✓ Configuración de campos requeridos actualizada exitosamente</p>";
} else {
    echo "<p class='info'>ℹ La configuración ya estaba actualizada o no cambió</p>";
}

// Mostrar nueva configuración
echo "<p class='info'>Nueva configuración:</p>";
echo "<ul>";
foreach ($new_settings as $field => $value) {
    $status = ($value === '1') ? 'Activado' : 'Desactivado';
    $class = ($value === '1') ? 'success' : 'info';
    echo "<li class='{$class}'>{$field}: {$status}</li>";
}
echo "</ul>";
echo "</div>";

// 3. Verificar funciones del plugin
echo "<div class='section'>";
echo "<h2>3. Verificación de Funciones del Plugin</h2>";

$functions_to_check = [
    'wpcw_add_custom_account_fields' => 'Añadir campos a Mi Cuenta',
    'wpcw_validate_custom_account_fields' => 'Validar campos',
    'wpcw_save_custom_account_fields' => 'Guardar campos'
];

foreach ($functions_to_check as $function => $description) {
    if (function_exists($function)) {
        echo "<p class='success'>✓ {$description}: {$function}()</p>";
    } else {
        echo "<p class='error'>✗ {$description}: {$function}() NO existe</p>";
    }
}
echo "</div>";

// 4. Verificar hooks
echo "<div class='section'>";
echo "<h2>4. Verificación de Hooks de WooCommerce</h2>";

$hooks_to_check = [
    'woocommerce_edit_account_form' => 'wpcw_add_custom_account_fields',
    'woocommerce_save_account_details_errors' => 'wpcw_validate_custom_account_fields', 
    'woocommerce_save_account_details' => 'wpcw_save_custom_account_fields'
];

foreach ($hooks_to_check as $hook => $function) {
    if (has_action($hook, $function)) {
        echo "<p class='success'>✓ Hook '{$hook}' registrado con '{$function}'</p>";
    } else {
        echo "<p class='error'>✗ Hook '{$hook}' NO registrado</p>";
    }
}
echo "</div>";

// 5. Limpiar caché si es necesario
echo "<div class='section'>";
echo "<h2>5. Limpieza de Caché</h2>";

// Limpiar caché de opciones
wp_cache_delete('wpcw_required_fields_settings', 'options');
echo "<p class='success'>✓ Caché de opciones limpiado</p>";

// Regenerar permalinks
flush_rewrite_rules();
echo "<p class='success'>✓ Permalinks regenerados</p>";
echo "</div>";

// 6. Enlaces útiles
echo "<div class='section'>";
echo "<h2>6. Enlaces Útiles</h2>";

if (function_exists('wc_get_page_id')) {
    $myaccount_page_id = wc_get_page_id('myaccount');
    if ($myaccount_page_id && $myaccount_page_id > 0) {
        $page_url = get_permalink($myaccount_page_id);
        $edit_account_url = wc_get_endpoint_url('edit-account', '', $page_url);
        
        echo "<p><a href='{$page_url}' target='_blank' style='background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;'>→ Ir a Mi Cuenta</a></p>";
        echo "<p><a href='{$edit_account_url}' target='_blank' style='background: #00a32a; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;'>→ Ir a Editar Cuenta</a></p>";
        echo "<p><a href='" . admin_url('admin.php?page=wpcw-settings') . "' target='_blank' style='background: #d63638; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;'>→ Configuración del Plugin</a></p>";
    }
}
echo "</div>";

// 7. Instrucciones finales
echo "<div class='section'>";
echo "<h2>7. Instrucciones Finales</h2>";
echo "<ol>";
echo "<li><strong>Ve a 'Mi Cuenta' → 'Editar Cuenta'</strong> usando los enlaces de arriba</li>";
echo "<li><strong>Verifica que aparezcan los campos:</strong> DNI, Fecha de Nacimiento, Número de WhatsApp</li>";
echo "<li><strong>Completa tu número de WhatsApp</strong> en el formato: +54 9 11 1234-5678</li>";
echo "<li><strong>Guarda los cambios</strong> haciendo clic en 'Guardar cambios'</li>";
echo "<li><strong>Prueba el shortcode de canje</strong> una vez configurado tu WhatsApp</li>";
echo "</ol>";

echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin-top: 15px;'>";
echo "<h3 style='color: #0c5460; margin-top: 0;'>💡 Nota Importante</h3>";
echo "<p style='color: #0c5460; margin-bottom: 0;'>Si los campos aún no aparecen después de esta configuración, puede ser que tu tema esté sobrescribiendo las plantillas de WooCommerce. En ese caso, contacta al desarrollador del tema o considera cambiar a un tema compatible con WooCommerce.</p>";
echo "</div>";
echo "</div>";

echo "<hr>";
echo "<p><em>Script completado. Los campos de WhatsApp deberían estar ahora disponibles en Mi Cuenta → Editar Cuenta.</em></p>";
?>