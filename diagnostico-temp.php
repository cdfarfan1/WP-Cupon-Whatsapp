<?php
// Diagnóstico directo para verificar si WordPress está ejecutando nuestros hooks
// Archivo temporal de diagnóstico

// Agregar un hook simple que siempre debería funcionar
add_action('admin_menu', function() {
    error_log('DIAGNÓSTICO: Hook admin_menu ejecutándose correctamente');
    
    // Crear un menú super simple
    add_menu_page(
        'DIAGNÓSTICO WPCW',
        'DIAGNÓSTICO',
        'manage_options',
        'diagnostico-wpcw',
        function() {
            echo '<h1>Diagnóstico funcionando!</h1>';
        },
        'dashicons-admin-tools',
        2
    );
});

// Verificar si nuestra función existe
add_action('admin_init', function() {
    error_log('DIAGNÓSTICO: admin_init ejecutándose');
    error_log('DIAGNÓSTICO: Función wpcw_register_plugin_admin_menu existe: ' . (function_exists('wpcw_register_plugin_admin_menu') ? 'SÍ' : 'NO'));
    error_log('DIAGNÓSTICO: Usuario actual: ' . get_current_user_id());
    error_log('DIAGNÓSTICO: Capacidades usuario actual: ' . implode(', ', array_keys(wp_get_current_user()->allcaps ?? [])));
});

// Debug de todos los menús registrados
add_action('admin_menu', function() {
    global $menu;
    error_log('DIAGNÓSTICO: Menús registrados: ' . print_r($menu, true));
}, 999);
?>
