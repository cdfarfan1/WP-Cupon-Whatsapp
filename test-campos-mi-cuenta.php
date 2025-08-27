<?php
/**
 * Test específico para campos personalizados en Mi Cuenta
 * 
 * Este script debe ejecutarse desde el navegador mientras estés logueado
 * URL: https://localhost/webstore/wp-content/plugins/wp-cupon-whatsapp/test-campos-mi-cuenta.php
 */

// Cargar WordPress
require_once('../../../wp-load.php');

// Verificar si el usuario está logueado
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Obtener usuario actual
$current_user = wp_get_current_user();

?><!DOCTYPE html>
<html>
<head>
    <title>Test Campos Mi Cuenta - WP Cupón WhatsApp</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .test-section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #0073aa; }
        .form-test { background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0; }
        input, select { padding: 8px; margin: 5px; width: 200px; }
        button { padding: 10px 20px; background: #0073aa; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>Test de Campos Personalizados - Mi Cuenta</h1>
    
    <div class="test-section">
        <h2>Información del Usuario</h2>
        <p><strong>Usuario:</strong> <?php echo esc_html($current_user->display_name); ?> (ID: <?php echo $current_user->ID; ?>)</p>
        <p><strong>Email:</strong> <?php echo esc_html($current_user->user_email); ?></p>
    </div>

    <?php
    // Test 1: Verificar si las funciones existen
    echo '<div class="test-section">';
    echo '<h2>Test 1: Verificación de Funciones</h2>';
    
    $functions = [
        'wpcw_add_custom_account_fields',
        'wpcw_validate_custom_account_fields', 
        'wpcw_save_custom_account_fields'
    ];
    
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "<p class='success'>✓ Función {$func}() existe</p>";
        } else {
            echo "<p class='error'>✗ Función {$func}() NO existe</p>";
        }
    }
    echo '</div>';
    
    // Test 2: Verificar hooks
    echo '<div class="test-section">';
    echo '<h2>Test 2: Verificación de Hooks</h2>';
    
    $hooks = [
        'woocommerce_edit_account_form' => 'wpcw_add_custom_account_fields',
        'woocommerce_save_account_details_errors' => 'wpcw_validate_custom_account_fields',
        'woocommerce_save_account_details' => 'wpcw_save_custom_account_fields'
    ];
    
    foreach ($hooks as $hook => $function) {
        $priority = has_action($hook, $function);
        if ($priority !== false) {
            echo "<p class='success'>✓ Hook '{$hook}' registrado (prioridad: {$priority})</p>";
        } else {
            echo "<p class='error'>✗ Hook '{$hook}' NO registrado</p>";
        }
    }
    echo '</div>';
    
    // Test 3: Mostrar metadatos actuales
    echo '<div class="test-section">';
    echo '<h2>Test 3: Metadatos Actuales del Usuario</h2>';
    
    $meta_fields = [
        '_wpcw_user_dni' => 'DNI',
        '_wpcw_user_birth_date' => 'Fecha de Nacimiento',
        '_wpcw_user_whatsapp' => 'Número de WhatsApp',
        '_wpcw_user_institution' => 'Institución',
        '_wpcw_user_favorite_coupon_categories' => 'Categorías Favoritas'
    ];
    
    foreach ($meta_fields as $meta_key => $label) {
        $value = get_user_meta($current_user->ID, $meta_key, true);
        if (!empty($value)) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            echo "<p class='success'>• {$label}: <strong>{$value}</strong></p>";
        } else {
            echo "<p class='warning'>• {$label}: <em>No configurado</em></p>";
        }
    }
    echo '</div>';
    
    // Test 4: Simular campos del formulario
    echo '<div class="test-section">';
    echo '<h2>Test 4: Simulación de Campos Personalizados</h2>';
    echo '<p class="info">Estos son los campos que deberían aparecer en "Editar Cuenta":</p>';
    
    // Simular la función wpcw_add_custom_account_fields
    if (function_exists('wpcw_add_custom_account_fields')) {
        echo '<div class="form-test">';
        echo '<h3>Campos Personalizados (Simulación)</h3>';
        
        // Capturar la salida de la función
        ob_start();
        wpcw_add_custom_account_fields();
        $fields_output = ob_get_clean();
        
        if (!empty($fields_output)) {
            echo $fields_output;
            echo '<p class="success">✓ Los campos se generan correctamente</p>';
        } else {
            echo '<p class="error">✗ La función no genera ningún campo</p>';
        }
        
        echo '</div>';
    }
    echo '</div>';
    
    // Test 5: Verificar página Mi Cuenta
    echo '<div class="test-section">';
    echo '<h2>Test 5: Enlaces a Mi Cuenta</h2>';
    
    if (function_exists('wc_get_page_id')) {
        $myaccount_page_id = wc_get_page_id('myaccount');
        if ($myaccount_page_id && $myaccount_page_id > 0) {
            $page_url = get_permalink($myaccount_page_id);
            $edit_account_url = wc_get_endpoint_url('edit-account', '', $page_url);
            
            echo "<p class='success'>✓ Página Mi Cuenta configurada</p>";
            echo "<p><a href='{$page_url}' target='_blank'>→ Ir a Mi Cuenta</a></p>";
            echo "<p><a href='{$edit_account_url}' target='_blank'>→ Ir a Editar Cuenta</a></p>";
        } else {
            echo "<p class='error'>✗ Página Mi Cuenta no configurada</p>";
        }
    }
    echo '</div>';
    
    // Test 6: Verificar tema activo
    echo '<div class="test-section">';
    echo '<h2>Test 6: Información del Tema</h2>';
    
    $theme = wp_get_theme();
    echo "<p><strong>Tema activo:</strong> {$theme->get('Name')} v{$theme->get('Version')}</p>";
    echo "<p><strong>Directorio del tema:</strong> {$theme->get_stylesheet_directory()}</p>";
    
    // Verificar si el tema tiene plantillas personalizadas de WooCommerce
    $theme_wc_templates = [
        'myaccount/form-edit-account.php',
        'myaccount/my-account.php'
    ];
    
    foreach ($theme_wc_templates as $template) {
        $template_path = $theme->get_stylesheet_directory() . '/woocommerce/' . $template;
        if (file_exists($template_path)) {
            echo "<p class='warning'>⚠ Plantilla personalizada encontrada: {$template}</p>";
            echo "<p class='info'>→ El tema puede estar sobrescribiendo los campos de WooCommerce</p>";
        }
    }
    echo '</div>';
    
    // Test 7: Verificar plugins activos
    echo '<div class="test-section">';
    echo '<h2>Test 7: Plugins que Pueden Interferir</h2>';
    
    $active_plugins = get_option('active_plugins');
    $potential_conflicts = [];
    
    foreach ($active_plugins as $plugin) {
        // Buscar plugins que puedan interferir con campos de usuario
        if (strpos($plugin, 'user') !== false || 
            strpos($plugin, 'account') !== false || 
            strpos($plugin, 'profile') !== false ||
            strpos($plugin, 'woocommerce') !== false) {
            $potential_conflicts[] = $plugin;
        }
    }
    
    if (!empty($potential_conflicts)) {
        echo '<p class="warning">⚠ Plugins que podrían interferir:</p>';
        foreach ($potential_conflicts as $plugin) {
            echo "<p>• {$plugin}</p>";
        }
    } else {
        echo '<p class="success">✓ No se detectaron plugins conflictivos obvios</p>';
    }
    echo '</div>';
    
    // Soluciones
    echo '<div class="test-section">';
    echo '<h2>Soluciones Recomendadas</h2>';
    echo '<ol>';
    echo '<li><strong>Verifica el tema:</strong> Si el tema tiene plantillas personalizadas de WooCommerce, puede estar ocultando los campos.</li>';
    echo '<li><strong>Desactiva otros plugins:</strong> Temporalmente desactiva otros plugins para ver si hay conflictos.</li>';
    echo '<li><strong>Limpia caché:</strong> Si usas plugins de caché, límpialo completamente.</li>';
    echo '<li><strong>Verifica permisos:</strong> Asegúrate de que el usuario tenga permisos para editar su perfil.</li>';
    echo '<li><strong>Regenera permalinks:</strong> Ve a Ajustes > Enlaces Permanentes y guarda.</li>';
    echo '</ol>';
    echo '</div>';
    ?>
    
    <div class="test-section">
        <h2>Acciones Rápidas</h2>
        <p>
            <button onclick="window.open('<?php echo admin_url('options-permalink.php'); ?>', '_blank')">
                Regenerar Permalinks
            </button>
            <button onclick="window.open('<?php echo admin_url('admin.php?page=wc-settings&tab=advanced&section=page_setup'); ?>', '_blank')">
                Configurar Páginas WC
            </button>
            <?php if (function_exists('wc_get_page_id')): ?>
            <button onclick="window.open('<?php echo wc_get_endpoint_url('edit-account', '', get_permalink(wc_get_page_id('myaccount'))); ?>', '_blank')">
                Ir a Editar Cuenta
            </button>
            <?php endif; ?>
        </p>
    </div>
    
    <hr>
    <p><em>Test completado. Si los campos no aparecen en "Editar Cuenta", revisa las soluciones recomendadas.</em></p>
</body>
</html>