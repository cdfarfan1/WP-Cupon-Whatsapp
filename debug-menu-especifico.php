<?php
/**
 * Debug específico para el menú WPCW
 */

if (!defined('ABSPATH')) {
    exit;
}

// Función de debug específica para el menú
function wpcw_debug_menu_especifico() {
    // error_log('=== DEBUG MENÚ ESPECÍFICO WPCW ===');
    
    // Verificar si la función principal existe
    if (function_exists('wpcw_register_plugin_admin_menu')) {
        // error_log('DEBUG: Función wpcw_register_plugin_admin_menu EXISTE');
        
        // Intentar ejecutar la función manualmente
        try {
            wpcw_register_plugin_admin_menu();
            // error_log('DEBUG: Función ejecutada manualmente SIN ERRORES');
        } catch (Exception $e) {
            // error_log('DEBUG: ERROR al ejecutar función: ' . $e->getMessage());
        }
    } else {
        // error_log('DEBUG: Función wpcw_register_plugin_admin_menu NO EXISTE');
    }
    
    // Verificar hooks registrados específicamente para nuestro plugin
    global $wp_filter;
    if (isset($wp_filter['admin_menu'])) {
        foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $func_name = '';
                if (is_array($callback['function'])) {
                    if (is_object($callback['function'][0])) {
                        $func_name = get_class($callback['function'][0]) . '::' . $callback['function'][1];
                    } else {
                        $func_name = $callback['function'][0] . '::' . $callback['function'][1];
                    }
                } else {
                    if (is_string($callback['function'])) {
                        $func_name = $callback['function'];
                    } else {
                        $func_name = 'Closure o función anónima';
                    }
                }
                
                if (is_string($func_name) && strpos($func_name, 'wpcw') !== false) {
                    // error_log('DEBUG: Hook WPCW encontrado - Prioridad: ' . $priority . ', Función: ' . $func_name);
                }
            }
        }
    }
    
    // Verificar menús después de admin_menu
    global $menu;
    if (is_array($menu)) {
        foreach ($menu as $item) {
            if (is_array($item) && isset($item[0], $item[2])) {
                $item_slug = is_string($item[2]) ? $item[2] : '';
                $item_name = is_string($item[0]) ? $item[0] : '';
                if ((is_string($item_slug) && strpos($item_slug, 'wpcw') !== false) || 
                    (is_string($item_name) && strpos($item_name, 'WP Cupón') !== false)) {
                    $safe_name = is_string($item[0]) ? strip_tags($item[0]) : 'Menú no string';
                    $safe_slug = is_string($item[2]) ? $item[2] : 'Slug no string';
                    // error_log('DEBUG: Menú WPCW encontrado: ' . $safe_name . ' (slug: ' . $safe_slug . ')');
                }
            }
        }
    }
    
    // error_log('=== FIN DEBUG MENÚ ESPECÍFICO ===');
}

// Ejecutar después de que se registren todos los menús
add_action('admin_menu', 'wpcw_debug_menu_especifico', 999);

// error_log('DEBUG: Archivo debug-menu-especifico.php cargado');