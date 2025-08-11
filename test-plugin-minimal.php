<?php
/**
 * Plugin Name: Test Menu Minimal
 * Description: Plugin de prueba mínimo para verificar si WordPress puede mostrar menús
 * Version: 1.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Función para agregar menú de prueba
function test_minimal_menu() {
    error_log('TEST MINIMAL: Función ejecutándose');
    
    $result = add_menu_page(
        'Test Minimal',
        'Test Minimal',
        'read',
        'test-minimal',
        'test_minimal_page_content',
        'dashicons-admin-tools',
        99
    );
    
    error_log('TEST MINIMAL: add_menu_page resultado: ' . ($result ? 'ÉXITO' : 'FALLÓ'));
}

// Función para mostrar contenido de la página
function test_minimal_page_content() {
    echo '<div class="wrap">';
    echo '<h1>Test Minimal Funcionando</h1>';
    echo '<p>Si puedes ver esta página, WordPress puede mostrar menús correctamente.</p>';
    echo '<p>Usuario actual: ' . wp_get_current_user()->user_login . '</p>';
    echo '<p>Capacidades: ' . (current_user_can('read') ? 'SÍ tiene read' : 'NO tiene read') . '</p>';
    echo '<p>Es admin: ' . (is_admin() ? 'SÍ' : 'NO') . '</p>';
    echo '</div>';
}

// Registrar el hook
add_action('admin_menu', 'test_minimal_menu', 1);

error_log('TEST MINIMAL: Plugin cargado correctamente');