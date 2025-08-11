<?php
/**
 * Diagnóstico completo del sistema WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

// Función de diagnóstico completo
function wpcw_diagnostico_completo() {
    // error_log('=== DIAGNÓSTICO COMPLETO WPCW ===');
    
    // 1. Verificar estado de WordPress
    // error_log('1. WordPress Version: ' . get_bloginfo('version'));
    // error_log('1. PHP Version: ' . PHP_VERSION);
    // error_log('1. is_admin(): ' . (is_admin() ? 'SÍ' : 'NO'));
    // error_log('1. is_user_logged_in(): ' . (is_user_logged_in() ? 'SÍ' : 'NO'));
    
    // 2. Verificar usuario actual
    $current_user = wp_get_current_user();
    // error_log('2. Usuario ID: ' . $current_user->ID);
    // error_log('2. Usuario Login: ' . $current_user->user_login);
    // error_log('2. Usuario Roles: ' . implode(', ', $current_user->roles));
    
    // 3. Verificar capacidades
    $caps_to_check = ['read', 'edit_posts', 'manage_options', 'administrator'];
    foreach ($caps_to_check as $cap) {
        // error_log('3. Capacidad ' . $cap . ': ' . (current_user_can($cap) ? 'SÍ' : 'NO'));
    }
    
    // 4. Verificar hooks
    global $wp_filter;
    if (isset($wp_filter['admin_menu'])) {
        // error_log('4. Hook admin_menu registrado: SÍ');
        // error_log('4. Callbacks en admin_menu: ' . count($wp_filter['admin_menu']->callbacks));
        
        foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
            // error_log('4. Prioridad ' . $priority . ': ' . count($callbacks) . ' callbacks');
            foreach ($callbacks as $callback) {
                if (is_array($callback['function'])) {
                    if (is_object($callback['function'][0])) {
                        // error_log('4. - Callback: ' . get_class($callback['function'][0]) . '::' . $callback['function'][1]);
                    } else {
                        $class_name = is_string($callback['function'][0]) ? $callback['function'][0] : 'Clase no string';
                        // error_log('4. - Callback: ' . $class_name . '::' . $callback['function'][1]);
                    }
                } else {
                    if (is_string($callback['function'])) {
                        // error_log('4. - Callback: ' . $callback['function']);
                    } else {
                        // error_log('4. - Callback: Closure o función anónima');
                    }
                }
            }
        }
    } else {
        // error_log('4. Hook admin_menu: NO REGISTRADO');
    }
    
    // 5. Verificar menús existentes
    global $menu, $submenu;
    // error_log('5. Menús principales registrados: ' . (is_array($menu) ? count($menu) : '0'));
    if (is_array($menu)) {
        foreach ($menu as $item) {
            if (is_array($item) && isset($item[0], $item[2])) {
                $menu_name = is_string($item[0]) ? strip_tags($item[0]) : 'Menú no string';
                $menu_slug = is_string($item[2]) ? $item[2] : 'Slug no string';
                // error_log('5. - Menú: ' . $menu_name . ' (slug: ' . $menu_slug . ')');
            }
        }
    }
    
    // 6. Verificar plugins activos
    $active_plugins = get_option('active_plugins', array());
    // error_log('6. Plugins activos: ' . count($active_plugins));
    foreach ($active_plugins as $plugin) {
        // error_log('6. - Plugin: ' . $plugin);
    }
    
    // 7. Verificar tema activo
    $theme = wp_get_theme();
    // error_log('7. Tema activo: ' . $theme->get('Name') . ' v' . $theme->get('Version'));
    
    // 8. Verificar errores PHP
    $last_error = error_get_last();
    if ($last_error) {
        // error_log('8. Último error PHP: ' . $last_error['message'] . ' en ' . $last_error['file'] . ':' . $last_error['line']);
    } else {
        // error_log('8. No hay errores PHP recientes');
    }
    
    // error_log('=== FIN DIAGNÓSTICO COMPLETO ===');
}

// Ejecutar diagnóstico en admin_init
add_action('admin_init', 'wpcw_diagnostico_completo', 1);

// error_log('DIAGNÓSTICO: Archivo de diagnóstico completo cargado');