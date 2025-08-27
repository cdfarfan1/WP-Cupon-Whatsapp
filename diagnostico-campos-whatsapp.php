<?php
/**
 * Diagnóstico de Campos Personalizados de WhatsApp
 * 
 * Este script ayuda a diagnosticar problemas con los campos personalizados
 * en la página "Mi Cuenta" de WooCommerce.
 */

// Verificar si WordPress está cargado
if (!defined('ABSPATH')) {
    // Si no está en WordPress, intentar cargar WordPress
    $wp_load_paths = [
        'C:\\xampp\\htdocs\\webstore\\wp-load.php',
        dirname(__FILE__) . '/../../../../wp-load.php',
        dirname(__FILE__) . '/../../../wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die("No se pudo cargar WordPress. Ejecuta este script desde el admin de WordPress o ajusta las rutas.");
    }
}

echo "<h1>Diagnóstico de Campos Personalizados - WP Cupón WhatsApp</h1>";

// 1. Verificar si WooCommerce está activo
echo "<h2>1. Verificación de WooCommerce</h2>";
if (class_exists('WooCommerce')) {
    echo "<p style='color: green;'>✓ WooCommerce está activo</p>";
    echo "<p>Versión de WooCommerce: " . WC()->version . "</p>";
} else {
    echo "<p style='color: red;'>✗ WooCommerce NO está activo</p>";
    echo "<p><strong>SOLUCIÓN:</strong> Activa WooCommerce para que los campos personalizados funcionen.</p>";
}

// 2. Verificar si el plugin está activo
echo "<h2>2. Verificación del Plugin WP Cupón WhatsApp</h2>";
if (defined('WPCW_VERSION')) {
    echo "<p style='color: green;'>✓ Plugin WP Cupón WhatsApp está activo</p>";
    echo "<p>Versión: " . WPCW_VERSION . "</p>";
} else {
    echo "<p style='color: red;'>✗ Plugin WP Cupón WhatsApp NO está activo</p>";
    echo "<p><strong>SOLUCIÓN:</strong> Activa el plugin WP Cupón WhatsApp.</p>";
}

// 3. Verificar si las funciones de campos personalizados existen
echo "<h2>3. Verificación de Funciones de Campos Personalizados</h2>";
$functions_to_check = [
    'wpcw_add_custom_account_fields' => 'Función para añadir campos a Mi Cuenta',
    'wpcw_validate_custom_account_fields' => 'Función para validar campos',
    'wpcw_save_custom_account_fields' => 'Función para guardar campos'
];

foreach ($functions_to_check as $function => $description) {
    if (function_exists($function)) {
        echo "<p style='color: green;'>✓ {$description}: {$function}()</p>";
    } else {
        echo "<p style='color: red;'>✗ {$description}: {$function}() NO existe</p>";
    }
}

// 4. Verificar hooks registrados
echo "<h2>4. Verificación de Hooks Registrados</h2>";
$hooks_to_check = [
    'woocommerce_edit_account_form' => 'wpcw_add_custom_account_fields',
    'woocommerce_save_account_details_errors' => 'wpcw_validate_custom_account_fields',
    'woocommerce_save_account_details' => 'wpcw_save_custom_account_fields'
];

foreach ($hooks_to_check as $hook => $function) {
    $priority = has_action($hook, $function);
    if ($priority !== false) {
        echo "<p style='color: green;'>✓ Hook '{$hook}' registrado con función '{$function}' (prioridad: {$priority})</p>";
    } else {
        echo "<p style='color: red;'>✗ Hook '{$hook}' NO está registrado con función '{$function}'</p>";
    }
}

// 5. Verificar usuario actual
echo "<h2>5. Verificación de Usuario Actual</h2>";
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    echo "<p style='color: green;'>✓ Usuario logueado: {$current_user->display_name} (ID: {$current_user->ID})</p>";
    
    // Verificar metadatos del usuario
    echo "<h3>Metadatos del Usuario:</h3>";
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
            echo "<p>• {$label}: <strong>{$value}</strong></p>";
        } else {
            echo "<p>• {$label}: <em>No configurado</em></p>";
        }
    }
} else {
    echo "<p style='color: orange;'>⚠ No hay usuario logueado</p>";
    echo "<p><strong>NOTA:</strong> Debes estar logueado para ver los campos personalizados en Mi Cuenta.</p>";
}

// 6. Verificar página Mi Cuenta
echo "<h2>6. Verificación de Página Mi Cuenta</h2>";
if (function_exists('wc_get_page_id')) {
    $myaccount_page_id = wc_get_page_id('myaccount');
    if ($myaccount_page_id && $myaccount_page_id > 0) {
        $page_url = get_permalink($myaccount_page_id);
        echo "<p style='color: green;'>✓ Página Mi Cuenta configurada (ID: {$myaccount_page_id})</p>";
        echo "<p>URL: <a href='{$page_url}' target='_blank'>{$page_url}</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Página Mi Cuenta NO está configurada</p>";
        echo "<p><strong>SOLUCIÓN:</strong> Ve a WooCommerce > Ajustes > Avanzado > Configuración de páginas y configura la página Mi Cuenta.</p>";
    }
}

// 7. Verificar archivos del plugin
echo "<h2>7. Verificación de Archivos del Plugin</h2>";
if (defined('WPCW_PLUGIN_DIR')) {
    $customer_fields_file = WPCW_PLUGIN_DIR . 'includes/customer-fields.php';
    if (file_exists($customer_fields_file)) {
        echo "<p style='color: green;'>✓ Archivo customer-fields.php existe</p>";
        echo "<p>Ruta: {$customer_fields_file}</p>";
    } else {
        echo "<p style='color: red;'>✗ Archivo customer-fields.php NO existe</p>";
        echo "<p>Ruta esperada: {$customer_fields_file}</p>";
    }
}

// 8. Soluciones recomendadas
echo "<h2>8. Soluciones Recomendadas</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0073aa;'>";
echo "<h3>Si los campos no aparecen en Mi Cuenta:</h3>";
echo "<ol>";
echo "<li><strong>Verifica que WooCommerce esté activo</strong> - Los campos personalizados dependen de WooCommerce.</li>";
echo "<li><strong>Verifica que el plugin esté activo</strong> - El plugin debe estar activado para que los hooks funcionen.</li>";
echo "<li><strong>Limpia la caché</strong> - Si usas un plugin de caché, límpialo.</li>";
echo "<li><strong>Verifica el tema</strong> - Algunos temas personalizan la página Mi Cuenta y pueden ocultar campos.</li>";
echo "<li><strong>Verifica otros plugins</strong> - Otros plugins pueden interferir con los campos de WooCommerce.</li>";
echo "<li><strong>Regenera permalinks</strong> - Ve a Ajustes > Enlaces Permanentes y guarda.</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 15px;'>";
echo "<h3>Si los campos aparecen pero no se guardan:</h3>";
echo "<ol>";
echo "<li><strong>Verifica permisos de usuario</strong> - El usuario debe tener permisos para editar su perfil.</li>";
echo "<li><strong>Verifica la validación</strong> - Puede haber errores de validación que impiden guardar.</li>";
echo "<li><strong>Verifica la base de datos</strong> - Los metadatos deben guardarse en wp_usermeta.</li>";
echo "<li><strong>Verifica JavaScript</strong> - Errores de JS pueden impedir el envío del formulario.</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin-top: 15px;'>";
echo "<h3>Enlaces Útiles:</h3>";
echo "<ul>";
if (is_user_logged_in() && function_exists('wc_get_page_id')) {
    $myaccount_page_id = wc_get_page_id('myaccount');
    if ($myaccount_page_id && $myaccount_page_id > 0) {
        $edit_account_url = wc_get_endpoint_url('edit-account', '', get_permalink($myaccount_page_id));
        echo "<li><a href='{$edit_account_url}' target='_blank'>Ir a Editar Cuenta</a></li>";
    }
}
echo "<li><a href='" . admin_url('admin.php?page=wc-settings&tab=advanced&section=page_setup') . "' target='_blank'>Configurar Páginas de WooCommerce</a></li>";
echo "<li><a href='" . admin_url('options-permalink.php') . "' target='_blank'>Regenerar Enlaces Permanentes</a></li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><em>Diagnóstico completado. Si sigues teniendo problemas, contacta al desarrollador con esta información.</em></p>";
?>