<?php
/**
 * Archivo de prueba para verificar el menú
 * Ejecutar desde WordPress admin para diagnosticar problemas
 */

// Solo ejecutar si estamos en admin
if (!is_admin()) {
    die('Este archivo solo puede ejecutarse desde el admin de WordPress');
}

// Verificar si las funciones existen
echo '<h2>Diagnóstico del Menú WP Cupón WhatsApp</h2>';

echo '<h3>1. Verificación de funciones:</h3>';
echo 'wpcw_register_plugin_admin_menu: ' . (function_exists('wpcw_register_plugin_admin_menu') ? '✅ Existe' : '❌ No existe') . '<br>';
echo 'wpcw_render_dashboard_page: ' . (function_exists('wpcw_render_dashboard_page') ? '✅ Existe' : '❌ No existe') . '<br>';
echo 'wpcw_canjes_page: ' . (function_exists('wpcw_canjes_page') ? '✅ Existe' : '❌ No existe') . '<br>';
echo 'wpcw_render_plugin_settings_page: ' . (function_exists('wpcw_render_plugin_settings_page') ? '✅ Existe' : '❌ No existe') . '<br>';

echo '<h3>2. Verificación de permisos del usuario actual:</h3>';
echo 'manage_options: ' . (current_user_can('manage_options') ? '✅ Sí' : '❌ No') . '<br>';
echo 'edit_posts: ' . (current_user_can('edit_posts') ? '✅ Sí' : '❌ No') . '<br>';
echo 'manage_woocommerce: ' . (current_user_can('manage_woocommerce') ? '✅ Sí' : '❌ No') . '<br>';

echo '<h3>3. Verificación de hooks registrados:</h3>';
global $wp_filter;
if (isset($wp_filter['admin_menu'])) {
    echo 'Hook admin_menu registrado: ✅ Sí<br>';
    echo 'Callbacks registrados:<br>';
    foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                echo '- Prioridad ' . $priority . ': ' . get_class($callback['function'][0]) . '::' . $callback['function'][1] . '<br>';
            } else {
                echo '- Prioridad ' . $priority . ': ' . $callback['function'] . '<br>';
            }
        }
    }
} else {
    echo 'Hook admin_menu: ❌ No registrado<br>';
}

echo '<h3>4. Verificación de constantes:</h3>';
echo 'WPCW_PLUGIN_DIR: ' . (defined('WPCW_PLUGIN_DIR') ? '✅ ' . WPCW_PLUGIN_DIR : '❌ No definida') . '<br>';
echo 'WPCW_VERSION: ' . (defined('WPCW_VERSION') ? '✅ ' . WPCW_VERSION : '❌ No definida') . '<br>';

echo '<h3>5. Verificación de archivos:</h3>';
$admin_menu_file = WPCW_PLUGIN_DIR . 'admin/admin-menu.php';
echo 'admin-menu.php: ' . (file_exists($admin_menu_file) ? '✅ Existe' : '❌ No existe') . '<br>';

echo '<h3>6. Test manual del menú:</h3>';
echo '<p>Intentando registrar el menú manualmente...</p>';

// Intentar registrar el menú manualmente
if (function_exists('wpcw_register_plugin_admin_menu')) {
    try {
        wpcw_register_plugin_admin_menu();
        echo '✅ Función ejecutada sin errores<br>';
    } catch (Exception $e) {
        echo '❌ Error al ejecutar: ' . $e->getMessage() . '<br>';
    }
} else {
    echo '❌ La función wpcw_register_plugin_admin_menu no existe<br>';
}

echo '<p><strong>Diagnóstico completado.</strong></p>';
?>