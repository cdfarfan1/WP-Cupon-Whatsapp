<?php
/**
 * Script web para activar los campos de WhatsApp en Mi Cuenta
 * 
 * Coloca este archivo en la raíz de WordPress y accede desde el navegador
 */

// Cargar WordPress
require_once('wp-config.php');

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

?><!DOCTYPE html>
<html>
<head>
    <title>Activar Campos WhatsApp - Mi Cuenta</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #00a32a; font-weight: bold; }
        .error { color: #d63638; font-weight: bold; }
        .info { color: #0073aa; }
        .warning { color: #dba617; font-weight: bold; }
        .section { background: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa; border-radius: 4px; }
        .button { background: #0073aa; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .button.success { background: #00a32a; }
        .button.warning { background: #dba617; }
        h1 { color: #23282d; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #0073aa; }
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
        .highlight { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Activar Campos de WhatsApp en Mi Cuenta</h1>
        
        <?php
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
        
        // 6. Verificar usuario actual
        echo "<div class='section'>";
        echo "<h2>6. Información del Usuario Actual</h2>";
        
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            echo "<p class='success'>✓ Usuario logueado: {$current_user->display_name} (ID: {$current_user->ID})</p>";
            
            // Verificar metadatos actuales
            $whatsapp = get_user_meta($current_user->ID, '_wpcw_whatsapp_number', true);
            $dni = get_user_meta($current_user->ID, '_wpcw_dni_number', true);
            $birth_date = get_user_meta($current_user->ID, '_wpcw_birth_date', true);
            
            echo "<p class='info'>Datos actuales:</p>";
            echo "<ul>";
            echo "<li>WhatsApp: " . ($whatsapp ? "<span class='success'>{$whatsapp}</span>" : "<span class='error'>No configurado</span>") . "</li>";
            echo "<li>DNI: " . ($dni ? "<span class='success'>{$dni}</span>" : "<span class='error'>No configurado</span>") . "</li>";
            echo "<li>Fecha Nacimiento: " . ($birth_date ? "<span class='success'>{$birth_date}</span>" : "<span class='error'>No configurado</span>") . "</li>";
            echo "</ul>";
        } else {
            echo "<p class='warning'>⚠ No hay usuario logueado. Debes iniciar sesión para ver los campos en Mi Cuenta.</p>";
        }
        echo "</div>";
        
        // 7. Enlaces útiles
        echo "<div class='section'>";
        echo "<h2>7. Enlaces de Prueba</h2>";
        
        if (function_exists('wc_get_page_id')) {
            $myaccount_page_id = wc_get_page_id('myaccount');
            if ($myaccount_page_id && $myaccount_page_id > 0) {
                $page_url = get_permalink($myaccount_page_id);
                $edit_account_url = wc_get_endpoint_url('edit-account', '', $page_url);
                
                echo "<p>";
                echo "<a href='{$page_url}' target='_blank' class='button'>→ Ir a Mi Cuenta</a>";
                echo "<a href='{$edit_account_url}' target='_blank' class='button success'>→ Ir a Editar Cuenta</a>";
                echo "<a href='" . admin_url('admin.php?page=wpcw-settings') . "' target='_blank' class='button warning'>→ Configuración del Plugin</a>";
                echo "</p>";
            }
        }
        echo "</div>";
        
        // 8. Instrucciones finales
        echo "<div class='highlight'>";
        echo "<h2>📋 Instrucciones Finales</h2>";
        echo "<ol>";
        echo "<li><strong>Haz clic en 'Ir a Editar Cuenta'</strong> usando el botón verde de arriba</li>";
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
        
        // 9. Test de campos en vivo
        if (is_user_logged_in() && function_exists('wpcw_add_custom_account_fields')) {
            echo "<div class='section'>";
            echo "<h2>9. Vista Previa de Campos Personalizados</h2>";
            echo "<p class='info'>Estos son los campos que deberían aparecer en 'Editar Cuenta':</p>";
            echo "<div style='border: 2px dashed #0073aa; padding: 20px; background: white; margin: 10px 0;'>";
            
            // Capturar la salida de la función
            ob_start();
            wpcw_add_custom_account_fields();
            $fields_output = ob_get_clean();
            
            if (!empty($fields_output)) {
                echo $fields_output;
                echo "<p class='success' style='margin-top: 15px;'>✓ Los campos se generan correctamente</p>";
            } else {
                echo "<p class='error'>✗ La función no genera ningún campo</p>";
            }
            
            echo "</div>";
            echo "</div>";
        }
        ?>
        
        <hr>
        <p><em>Script completado. Los campos de WhatsApp deberían estar ahora disponibles en Mi Cuenta → Editar Cuenta.</em></p>
        
        <p style="text-align: center; margin-top: 30px;">
            <strong>¿Los campos aparecen correctamente?</strong><br>
            <a href="<?php echo wc_get_endpoint_url('edit-account', '', get_permalink(wc_get_page_id('myaccount'))); ?>" class="button success" style="font-size: 16px; padding: 15px 25px;">🚀 Probar Ahora en Mi Cuenta</a>
        </p>
    </div>
</body>
</html>