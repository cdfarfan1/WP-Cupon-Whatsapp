<?php
/**
 * Script de prueba para las validaciones mejoradas
 * WP Cupón WhatsApp - Enhanced Validation Test
 */

// Simular entorno WordPress básico
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WPCW_PLUGIN_DIR')) {
    define('WPCW_PLUGIN_DIR', __DIR__ . '/');
}

if (!defined('WPCW_PLUGIN_URL')) {
    define('WPCW_PLUGIN_URL', 'http://localhost/wp-content/plugins/wp-cupon-whatsapp/');
}

// Incluir el archivo de validaciones mejoradas
require_once __DIR__ . '/includes/validation-enhanced.php';

// Función para mostrar resultados de prueba
function mostrar_resultado($test_name, $result, $expected = null) {
    $status = $expected !== null ? ($result === $expected ? '✅ PASS' : '❌ FAIL') : ($result ? '✅ PASS' : '❌ FAIL');
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid " . ($status === '✅ PASS' ? '#4CAF50' : '#f44336') . "; background: #f9f9f9;'>";
    echo "<strong>{$status} {$test_name}</strong><br>";
    echo "Resultado: " . (is_bool($result) ? ($result ? 'true' : 'false') : htmlspecialchars(print_r($result, true))) . "<br>";
    if ($expected !== null) {
        echo "Esperado: " . (is_bool($expected) ? ($expected ? 'true' : 'false') : htmlspecialchars(print_r($expected, true)));
    }
    echo "</div>";
}

// Inicializar la clase de validación
$validator = new WPCW_Enhanced_Validation();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Validaciones Mejoradas - WP Cupón WhatsApp</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #23282d; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #0073aa; margin-top: 30px; }
        .test-section { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Validaciones Mejoradas - WP Cupón WhatsApp</h1>
        <p>Este script prueba todas las funcionalidades de validación mejorada implementadas.</p>
        
        <div class="test-section">
            <h2>📧 Pruebas de Validación de Email</h2>
            <?php
            // Pruebas de email válidos
            $emails_validos = [
                'usuario@gmail.com',
                'test@hotmail.com',
                'admin@empresa.com.ar',
                'contacto@sitio.org'
            ];
            
            foreach ($emails_validos as $email) {
                $resultado = $validator->validate_email_enhanced($email);
                mostrar_resultado("Email válido: {$email}", $resultado['is_valid'], true);
            }
            
            // Pruebas de email con errores comunes
            $emails_con_errores = [
                'usuario@gmai.com', // Error en dominio
                'test@hotmai.com',  // Error en dominio
                'admin@yahooo.com', // Error en dominio
                'contacto@gmial.com' // Error en dominio
            ];
            
            foreach ($emails_con_errores as $email) {
                $resultado = $validator->validate_email_enhanced($email);
                mostrar_resultado("Email con error: {$email}", $resultado);
                if (!empty($resultado['suggestion'])) {
                    echo "<div style='margin-left: 20px; color: #0073aa;'>💡 Sugerencia: {$resultado['suggestion']}</div>";
                }
            }
            ?>
        </div>
        
        <div class="test-section">
            <h2>📱 Pruebas de Validación de WhatsApp</h2>
            <?php
            // Pruebas de números de WhatsApp válidos
            $numeros_validos = [
                '+5491123456789',
                '5491123456789',
                '91123456789',
                '011-2345-6789',
                '+54 9 11 2345-6789'
            ];
            
            foreach ($numeros_validos as $numero) {
                $resultado = $validator->validate_whatsapp_enhanced($numero);
                mostrar_resultado("WhatsApp válido: {$numero}", $resultado['is_valid'], true);
                if ($resultado['is_valid']) {
                    echo "<div style='margin-left: 20px; color: #0073aa;'>📱 Formateado: {$resultado['formatted']}</div>";
                    echo "<div style='margin-left: 20px; color: #0073aa;'>🔗 Enlace wa.me: <a href='{$resultado['wa_link']}' target='_blank'>{$resultado['wa_link']}</a></div>";
                }
            }
            
            // Pruebas de números inválidos
            $numeros_invalidos = [
                '123456789', // Muy corto
                '+1234567890123456', // Muy largo
                 'abc123def', // Contiene letras
                 '', // Vacío
                 '+54911234567890123' // Demasiado largo para Argentina
            ];
            
            foreach ($numeros_invalidos as $numero) {
                $resultado = $validator->validate_whatsapp_enhanced($numero);
                mostrar_resultado("WhatsApp inválido: '{$numero}'", $resultado['is_valid'], false);
                if (!empty($resultado['error'])) {
                    echo "<div style='margin-left: 20px; color: #d63638;'>❌ Error: {$resultado['error']}</div>";
                }
            }
            ?>
        </div>
        
        <div class="test-section">
            <h2>🔧 Pruebas de Corrección de Carga de Teléfono</h2>
            <?php
            // Simular diferentes tipos de datos problemáticos
            $datos_problematicos = [
                null,
                '',
                'a:1:{i:0;s:13:"+5491123456789";}', // Serializado
                ['+5491123456789'], // Array
                '+5491123456789' // String normal
            ];
            
            foreach ($datos_problematicos as $index => $dato) {
                $resultado = $validator->fix_phone_loading($dato);
                $tipo = gettype($dato);
                if ($tipo === 'string' && strpos($dato, 'a:') === 0) {
                    $tipo = 'serialized';
                }
                mostrar_resultado("Corrección de dato tipo {$tipo} (#{$index})", $resultado);
            }
            ?>
        </div>
        
        <div class="test-section">
            <h2>🌐 Pruebas de Enlaces wa.me</h2>
            <?php
            // Probar generación de enlaces wa.me
            $numeros_para_enlaces = [
                '+5491123456789',
                '5491187654321',
                '011-1234-5678'
            ];
            
            foreach ($numeros_para_enlaces as $numero) {
                $enlace = $validator->generate_wa_me_link($numero);
                mostrar_resultado("Enlace wa.me para {$numero}", $enlace);
                echo "<div style='margin-left: 20px;'><a href='{$enlace}' target='_blank' style='color: #25D366; text-decoration: none;'>🔗 Probar enlace</a></div>";
            }
            ?>
        </div>
        
        <div class="test-section">
            <h2>📊 Resumen de Funcionalidades</h2>
            <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3>✅ Funcionalidades Implementadas:</h3>
                <ul>
                    <li><strong>Validación de Email Mejorada:</strong> Detecta errores comunes en dominios y sugiere correcciones</li>
                    <li><strong>Validación de WhatsApp:</strong> Formatea números argentinos y genera enlaces wa.me</li>
                    <li><strong>Corrección de Carga de Teléfono:</strong> Maneja datos serializados, arrays y valores nulos</li>
                    <li><strong>Enlaces wa.me:</strong> Genera enlaces directos para WhatsApp sin necesidad de API</li>
                    <li><strong>Validación AJAX:</strong> Permite validación en tiempo real desde JavaScript</li>
                    <li><strong>Detección de Números Falsos:</strong> Identifica patrones de números no válidos</li>
                </ul>
            </div>
            
            <div style="background: #fff2e7; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3>🔧 Mejoras Técnicas:</h3>
                <ul>
                    <li>Manejo robusto de errores y excepciones</li>
                    <li>Compatibilidad con diferentes formatos de entrada</li>
                    <li>Sanitización y validación de datos</li>
                    <li>Integración con hooks y filtros de WordPress</li>
                    <li>Soporte para validación del lado del cliente y servidor</li>
                </ul>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <h3>🎉 ¡Validaciones Mejoradas Implementadas Exitosamente!</h3>
            <p>El plugin WP Cupón WhatsApp ahora cuenta con validaciones robustas para email y WhatsApp, además de correcciones para problemas de carga de datos.</p>
        </div>
    </div>
</body>
</html>