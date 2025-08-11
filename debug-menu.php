<?php
/**
 * Archivo de diagnóstico para el menú de WP Cupón WhatsApp
 * Ejecutar desde: wp-admin/admin.php?page=debug-menu
 */

if (!defined('ABSPATH')) {
    exit;
}

// Función para mostrar el diagnóstico
function wpcw_debug_menu_page() {
    echo '<div class="wrap">';
    echo '<h1>🔍 Diagnóstico del Menú WP Cupón WhatsApp</h1>';
    
    echo '<div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h2>📋 Estado del Plugin</h2>';
    
    // Verificar si el plugin está activo
    echo '<p><strong>Plugin activo:</strong> ';
    if (defined('WPCW_VERSION')) {
        echo '<span style="color: green;">✅ SÍ (Versión: ' . WPCW_VERSION . ')</span>';
    } else {
        echo '<span style="color: red;">❌ NO - Constante WPCW_VERSION no definida</span>';
    }
    echo '</p>';
    
    // Verificar permisos del usuario
    echo '<p><strong>Permisos del usuario actual:</strong></p>';
    echo '<ul>';
    echo '<li>manage_options: ' . (current_user_can('manage_options') ? '✅ SÍ' : '❌ NO') . '</li>';
    echo '<li>edit_posts: ' . (current_user_can('edit_posts') ? '✅ SÍ' : '❌ NO') . '</li>';
    echo '<li>Usuario ID: ' . get_current_user_id() . '</li>';
    echo '<li>Roles: ' . implode(', ', wp_get_current_user()->roles) . '</li>';
    echo '</ul>';
    
    // Verificar si las funciones existen
    echo '<h3>🔧 Funciones del Plugin</h3>';
    $functions = [
        'wpcw_register_plugin_admin_menu',
        'wpcw_render_dashboard_page',
        'wpcw_canjes_page',
        'wpcw_render_plugin_settings_page'
    ];
    
    foreach ($functions as $func) {
        echo '<p>' . $func . ': ';
        if (function_exists($func)) {
            echo '<span style="color: green;">✅ Existe</span>';
        } else {
            echo '<span style="color: red;">❌ No existe</span>';
        }
        echo '</p>';
    }
    
    // Verificar archivos
    echo '<h3>📁 Archivos del Plugin</h3>';
    $files = [
        'admin/admin-menu.php',
        'admin/settings-page.php',
        'admin/canjes-page.php',
        'includes/post-types.php'
    ];
    
    foreach ($files as $file) {
        $full_path = WPCW_PLUGIN_DIR . $file;
        echo '<p>' . $file . ': ';
        if (file_exists($full_path)) {
            echo '<span style="color: green;">✅ Existe</span>';
        } else {
            echo '<span style="color: red;">❌ No existe</span>';
        }
        echo '</p>';
    }
    
    // Verificar hooks registrados
    echo '<h3>🎣 Hooks Registrados</h3>';
    global $wp_filter;
    
    if (isset($wp_filter['admin_menu'])) {
        echo '<p><strong>Hooks en admin_menu:</strong></p>';
        echo '<ul>';
        foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $function_name = 'Función desconocida';
                if (is_array($callback['function'])) {
                    $function_name = get_class($callback['function'][0]) . '::' . $callback['function'][1];
                } elseif (is_string($callback['function'])) {
                    $function_name = $callback['function'];
                }
                echo '<li>Prioridad ' . $priority . ': ' . $function_name . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<p style="color: red;">❌ No hay hooks registrados en admin_menu</p>';
    }
    
    // Verificar menús globales
    echo '<h3>📋 Menús Registrados en WordPress</h3>';
    global $menu, $submenu;
    
    echo '<p><strong>Menús principales:</strong></p>';
    if (!empty($menu)) {
        echo '<ul>';
        foreach ($menu as $item) {
            if (strpos($item[2], 'wpcw') !== false || strpos($item[0], 'Cupón') !== false || strpos($item[0], 'WhatsApp') !== false) {
                echo '<li style="color: green;">✅ ' . $item[0] . ' (slug: ' . $item[2] . ')</li>';
            }
        }
        echo '</ul>';
    }
    
    echo '<p><strong>Submenús de nuestro plugin:</strong></p>';
    if (!empty($submenu)) {
        $found_submenus = false;
        foreach ($submenu as $parent => $items) {
            if (strpos($parent, 'wpcw') !== false) {
                $found_submenus = true;
                echo '<p>Submenús de ' . $parent . ':</p>';
                echo '<ul>';
                foreach ($items as $item) {
                    echo '<li>' . $item[0] . ' (slug: ' . $item[2] . ')</li>';
                }
                echo '</ul>';
            }
        }
        if (!$found_submenus) {
            echo '<p style="color: red;">❌ No se encontraron submenús del plugin</p>';
        }
    }
    
    // Intentar registrar el menú manualmente
    echo '<h3>🔧 Prueba Manual de Registro</h3>';
    echo '<p>Intentando registrar el menú manualmente...</p>';
    
    if (function_exists('wpcw_register_plugin_admin_menu')) {
        try {
            wpcw_register_plugin_admin_menu();
            echo '<p style="color: green;">✅ Función ejecutada sin errores</p>';
        } catch (Exception $e) {
            echo '<p style="color: red;">❌ Error al ejecutar: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p style="color: red;">❌ La función wpcw_register_plugin_admin_menu no existe</p>';
    }
    
    echo '</div>';
    echo '</div>';
}

// Registrar la página de diagnóstico temporalmente
if (is_admin() && current_user_can('manage_options')) {
    add_action('admin_menu', function() {
        add_menu_page(
            'Debug Menu WPCW',
            'Debug Menu WPCW',
            'manage_options',
            'debug-menu',
            'wpcw_debug_menu_page',
            'dashicons-admin-tools',
            99
        );
    }, 999);
}

?>