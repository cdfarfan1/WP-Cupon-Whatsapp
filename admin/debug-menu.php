<?php
/**
 * Archivo de diagnóstico para el menú de WP Cupón WhatsApp
 * Se registra como un submenú de "WP Cupón WhatsApp".
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agrega el submenú de diagnóstico.
 */
function wpcw_add_debug_menu() {
    add_submenu_page(
        'wpcw-dashboard',           // Parent slug
        'Diagnóstico',              // Page title
        'Diagnóstico',              // Menu title
        'manage_options',           // Capability
        'wpcw-debug',               // Menu slug
        'wpcw_render_debug_page',   // Function
        99                          // Position
    );
}
add_action('admin_menu', 'wpcw_add_debug_menu', 99);

/**
 * Renderiza la página de diagnóstico.
 */
function wpcw_render_debug_page() {
    ?>
    <div class="wrap">
        <h1>🔍 Diagnóstico del Plugin WP Cupón WhatsApp</h1>

        <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 5px;">
            <h2>📋 Estado del Plugin</h2>
            <p><strong>Plugin activo:</strong>
            <?php if (defined('WPCW_VERSION')) : ?>
                <span style="color: green;">✅ SÍ (Versión: <?php echo WPCW_VERSION; ?>)</span>
            <?php else : ?>
                <span style="color: red;">❌ NO - Constante WPCW_VERSION no definida</span>
            <?php endif; ?>
            </p>

            <h2>👤 Usuario Actual</h2>
            <p><strong>Permisos del usuario actual:</strong></p>
            <ul>
                <li><strong>ID de Usuario:</strong> <?php echo get_current_user_id(); ?></li>
                <li><strong>Roles:</strong> <?php echo implode(', ', wp_get_current_user()->roles); ?></li>
                <li><strong>Capacidad 'manage_options':</strong> <?php echo current_user_can('manage_options') ? '<span style="color: green;">✅ SÍ</span>' : '<span style="color: red;">❌ NO</span>'; ?></li>
            </ul>

            <h2>🔧 Funciones del Plugin</h2>
            <?php
            $functions = [
                'wpcw_register_plugin_admin_menu',
                'wpcw_render_dashboard_page',
                'wpcw_canjes_page',
                'wpcw_render_plugin_settings_page'
            ];
            foreach ($functions as $func) {
                echo '<p><strong>' . esc_html($func) . ':</strong> ';
                if (function_exists($func)) {
                    echo '<span style="color: green;">✅ Existe</span>';
                } else {
                    echo '<span style="color: red;">❌ No existe</span>';
                }
                echo '</p>';
            }
            ?>

            <h2>📁 Archivos del Plugin</h2>
            <?php
            $files = [
                'admin/admin-menu.php',
                'admin/settings-page.php',
                'admin/canjes-page.php',
                'includes/post-types.php'
            ];
            foreach ($files as $file) {
                $full_path = WPCW_PLUGIN_DIR . $file;
                echo '<p><strong>' . esc_html($file) . ':</strong> ';
                if (file_exists($full_path)) {
                    echo '<span style="color: green;">✅ Existe</span>';
                } else {
                    echo '<span style="color: red;">❌ No existe</span>';
                }
                echo '</p>';
            }
            ?>

            <h2>🎣 Hooks Registrados en 'admin_menu'</h2>
            <?php
            global $wp_filter;
            if (isset($wp_filter['admin_menu'])) {
                echo '<ul>';
                foreach ($wp_filter['admin_menu']->callbacks as $priority => $callbacks) {
                    foreach ($callbacks as $callback) {
                        $function_name = 'Función desconocida';
                        if (is_array($callback['function'])) {
                            $function_name = (is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0]) . '::' . $callback['function'][1];
                        } elseif (is_string($callback['function'])) {
                            $function_name = $callback['function'];
                        }
                        echo '<li>Prioridad ' . esc_html($priority) . ': ' . esc_html($function_name) . '</li>';
                    }
                }
                echo '</ul>';
            } else {
                echo '<p style="color: red;">❌ No hay hooks registrados en admin_menu.</p>';
            }
            ?>

            <h2>📋 Menús y Submenús Registrados</h2>
            <?php
            global $menu, $submenu;
            echo '<h3>Menús Principales Relevantes:</h3><ul>';
            if (!empty($menu)) {
                foreach ($menu as $item) {
                    if (strpos($item[2], 'wpcw') !== false || strpos($item[0], 'Cupón') !== false) {
                        echo '<li>' . esc_html($item[0]) . ' (slug: ' . esc_html($item[2]) . ')</li>';
                    }
                }
            }
            echo '</ul>';

            echo '<h3>Submenús del Plugin:</h3>';
            if (!empty($submenu['wpcw-dashboard'])) {
                echo '<ul>';
                foreach ($submenu['wpcw-dashboard'] as $item) {
                    echo '<li>' . esc_html($item[0]) . ' (slug: ' . esc_html($item[2]) . ')</li>';
                }
                echo '</ul>';
            } else {
                echo '<p style="color: red;">❌ No se encontraron submenús para "wpcw-dashboard".</p>';
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Registra logs de diagnóstico durante la inicialización del admin.
 * (Funcionalidad de diagnostico-temp.php)
 */
function wpcw_diagnostic_logs() {
    if (get_option('wpcw_enable_diagnostic_logs', false)) {
        error_log('DIAGNÓSTICO WPCW: Hook admin_init ejecutándose.');
        error_log('DIAGNÓSTICO WPCW: Función wpcw_register_plugin_admin_menu existe: ' . (function_exists('wpcw_register_plugin_admin_menu') ? 'SÍ' : 'NO'));
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            error_log('DIAGNÓSTICO WPCW: Usuario actual ID: ' . $user->ID);
            error_log('DIAGNÓSTICO WPCW: Capacidades usuario: ' . implode(', ', array_keys($user->allcaps)));
        }
    }
}
add_action('admin_init', 'wpcw_diagnostic_logs');

/**
 * Log de los menús registrados al final de la carga del menú.
 * (Funcionalidad de diagnostico-temp.php)
 */
function wpcw_log_registered_menus() {
    if (get_option('wpcw_enable_diagnostic_logs', false)) {
        global $menu;
        error_log('DIAGNÓSTICO WPCW: Menús registrados finales: ' . print_r($menu, true));
    }
}
add_action('admin_menu', 'wpcw_log_registered_menus', 9999);

// Añadiremos un campo en la página de ajustes para habilitar/deshabilitar estos logs,
// por ahora, se pueden habilitar manualmente con: update_option('wpcw_enable_diagnostic_logs', true);
?>
