<?php
/**
 * Prueba Escalonada de Funciones del Plugin WP Cupón WhatsApp
 * 
 * Este script activa las funciones del plugin de manera gradual
 * para identificar exactamente dónde ocurre el error fatal
 */

// Configuración de errores para capturar todo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Prueba Escalonada - WP Cupón WhatsApp</h1>";
echo "<hr>";

// Función para mostrar el estado de cada paso
function mostrar_paso($numero, $descripcion, $estado = 'ejecutando') {
    $colores = [
        'ejecutando' => '#007cba',
        'exitoso' => '#46b450',
        'error' => '#dc3232',
        'advertencia' => '#ffb900'
    ];
    
    $iconos = [
        'ejecutando' => '⏳',
        'exitoso' => '✅',
        'error' => '❌',
        'advertencia' => '⚠️'
    ];
    
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid {$colores[$estado]}; background: #f9f9f9;'>";
    echo "<strong>{$iconos[$estado]} Paso $numero:</strong> $descripcion";
    echo "</div>";
}

// Función para capturar errores
function capturar_error($mensaje) {
    echo "<div style='color: red; font-weight: bold; margin: 10px 0; padding: 10px; background: #ffe6e6; border: 1px solid #ff0000;'>";
    echo "💥 ERROR CAPTURADO: $mensaje";
    echo "</div>";
}

try {
    // PASO 1: Verificar entorno básico
    mostrar_paso(1, "Verificando entorno básico");
    
    echo "<ul>";
    echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
    echo "<li><strong>WordPress:</strong> " . (defined('ABSPATH') ? 'Detectado' : 'NO detectado') . "</li>";
    echo "<li><strong>Memoria PHP:</strong> " . ini_get('memory_limit') . "</li>";
    echo "<li><strong>Tiempo máximo:</strong> " . ini_get('max_execution_time') . "s</li>";
    echo "</ul>";
    
    mostrar_paso(1, "Entorno básico verificado", 'exitoso');
    
} catch (Exception $e) {
    mostrar_paso(1, "Error en verificación de entorno: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
}

try {
    // PASO 2: Definir constantes básicas
    mostrar_paso(2, "Definiendo constantes básicas");
    
    if (!defined('WPCW_VERSION')) {
        define('WPCW_VERSION', '1.2.3-test');
        echo "✓ WPCW_VERSION definida<br>";
    }
    
    if (!defined('WPCW_PLUGIN_DIR')) {
        define('WPCW_PLUGIN_DIR', __DIR__ . '/');
        echo "✓ WPCW_PLUGIN_DIR definida: " . WPCW_PLUGIN_DIR . "<br>";
    }
    
    if (!defined('WPCW_TEXT_DOMAIN')) {
        define('WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp');
        echo "✓ WPCW_TEXT_DOMAIN definida<br>";
    }
    
    mostrar_paso(2, "Constantes básicas definidas", 'exitoso');
    
} catch (Exception $e) {
    mostrar_paso(2, "Error definiendo constantes: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
}

try {
    // PASO 3: Verificar archivos principales
    mostrar_paso(3, "Verificando existencia de archivos principales");
    
    $archivos_principales = [
        'wp-cupon-whatsapp.php' => 'Archivo principal original',
        'wp-cupon-whatsapp-standalone.php' => 'Archivo principal standalone',
        'includes/post-types.php' => 'Tipos de post',
        'includes/whatsapp-handlers.php' => 'Manejadores WhatsApp',
        'includes/validation-enhanced.php' => 'Validaciones mejoradas',
        'admin/admin-menu.php' => 'Menú de administración',
        'public/shortcodes.php' => 'Shortcodes públicos'
    ];
    
    echo "<ul>";
    foreach ($archivos_principales as $archivo => $descripcion) {
        $ruta_completa = WPCW_PLUGIN_DIR . $archivo;
        if (file_exists($ruta_completa)) {
            echo "<li style='color: green;'>✅ <strong>$descripcion:</strong> $archivo</li>";
        } else {
            echo "<li style='color: red;'>❌ <strong>FALTA:</strong> $archivo - $descripcion</li>";
        }
    }
    echo "</ul>";
    
    mostrar_paso(3, "Verificación de archivos completada", 'exitoso');
    
} catch (Exception $e) {
    mostrar_paso(3, "Error verificando archivos: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
}

try {
    // PASO 4: Probar carga de archivo post-types.php
    mostrar_paso(4, "Probando carga de includes/post-types.php");
    
    $archivo_post_types = WPCW_PLUGIN_DIR . 'includes/post-types.php';
    if (file_exists($archivo_post_types)) {
        // Verificar sintaxis primero
        $output = [];
        $return_code = 0;
        exec("php -l \"$archivo_post_types\"", $output, $return_code);
        
        if ($return_code === 0) {
            echo "✓ Sintaxis correcta en post-types.php<br>";
            
            // Intentar incluir el archivo
            ob_start();
            include_once $archivo_post_types;
            $output_content = ob_get_clean();
            
            if (!empty($output_content)) {
                echo "⚠️ Output generado: <pre>" . htmlspecialchars($output_content) . "</pre>";
            }
            
            echo "✓ Archivo post-types.php cargado exitosamente<br>";
            mostrar_paso(4, "post-types.php cargado correctamente", 'exitoso');
        } else {
            echo "❌ Error de sintaxis en post-types.php: " . implode("\n", $output) . "<br>";
            mostrar_paso(4, "Error de sintaxis en post-types.php", 'error');
        }
    } else {
        mostrar_paso(4, "Archivo post-types.php no encontrado", 'advertencia');
    }
    
} catch (Exception $e) {
    mostrar_paso(4, "Error cargando post-types.php: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
} catch (ParseError $e) {
    mostrar_paso(4, "Error de sintaxis en post-types.php: " . $e->getMessage(), 'error');
    capturar_error("Parse Error: " . $e->getMessage());
} catch (Fatal $e) {
    mostrar_paso(4, "Error fatal en post-types.php: " . $e->getMessage(), 'error');
    capturar_error("Fatal Error: " . $e->getMessage());
}

try {
    // PASO 5: Probar carga de whatsapp-handlers.php
    mostrar_paso(5, "Probando carga de includes/whatsapp-handlers.php");
    
    $archivo_whatsapp = WPCW_PLUGIN_DIR . 'includes/whatsapp-handlers.php';
    if (file_exists($archivo_whatsapp)) {
        // Verificar sintaxis primero
        $output = [];
        $return_code = 0;
        exec("php -l \"$archivo_whatsapp\"", $output, $return_code);
        
        if ($return_code === 0) {
            echo "✓ Sintaxis correcta en whatsapp-handlers.php<br>";
            
            // Intentar incluir el archivo
            ob_start();
            include_once $archivo_whatsapp;
            $output_content = ob_get_clean();
            
            if (!empty($output_content)) {
                echo "⚠️ Output generado: <pre>" . htmlspecialchars($output_content) . "</pre>";
            }
            
            echo "✓ Archivo whatsapp-handlers.php cargado exitosamente<br>";
            mostrar_paso(5, "whatsapp-handlers.php cargado correctamente", 'exitoso');
        } else {
            echo "❌ Error de sintaxis en whatsapp-handlers.php: " . implode("\n", $output) . "<br>";
            mostrar_paso(5, "Error de sintaxis en whatsapp-handlers.php", 'error');
        }
    } else {
        mostrar_paso(5, "Archivo whatsapp-handlers.php no encontrado", 'advertencia');
    }
    
} catch (Exception $e) {
    mostrar_paso(5, "Error cargando whatsapp-handlers.php: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
} catch (ParseError $e) {
    mostrar_paso(5, "Error de sintaxis en whatsapp-handlers.php: " . $e->getMessage(), 'error');
    capturar_error("Parse Error: " . $e->getMessage());
}

try {
    // PASO 6: Probar carga de validation-enhanced.php
    mostrar_paso(6, "Probando carga de includes/validation-enhanced.php");
    
    $archivo_validation = WPCW_PLUGIN_DIR . 'includes/validation-enhanced.php';
    if (file_exists($archivo_validation)) {
        // Verificar sintaxis primero
        $output = [];
        $return_code = 0;
        exec("php -l \"$archivo_validation\"", $output, $return_code);
        
        if ($return_code === 0) {
            echo "✓ Sintaxis correcta en validation-enhanced.php<br>";
            
            // Intentar incluir el archivo
            ob_start();
            include_once $archivo_validation;
            $output_content = ob_get_clean();
            
            if (!empty($output_content)) {
                echo "⚠️ Output generado: <pre>" . htmlspecialchars($output_content) . "</pre>";
            }
            
            echo "✓ Archivo validation-enhanced.php cargado exitosamente<br>";
            mostrar_paso(6, "validation-enhanced.php cargado correctamente", 'exitoso');
        } else {
            echo "❌ Error de sintaxis en validation-enhanced.php: " . implode("\n", $output) . "<br>";
            mostrar_paso(6, "Error de sintaxis en validation-enhanced.php", 'error');
        }
    } else {
        mostrar_paso(6, "Archivo validation-enhanced.php no encontrado", 'advertencia');
    }
    
} catch (Exception $e) {
    mostrar_paso(6, "Error cargando validation-enhanced.php: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
} catch (ParseError $e) {
    mostrar_paso(6, "Error de sintaxis en validation-enhanced.php: " . $e->getMessage(), 'error');
    capturar_error("Parse Error: " . $e->getMessage());
}

try {
    // PASO 7: Probar carga de admin-menu.php
    mostrar_paso(7, "Probando carga de admin/admin-menu.php");
    
    $archivo_admin = WPCW_PLUGIN_DIR . 'admin/admin-menu.php';
    if (file_exists($archivo_admin)) {
        // Verificar sintaxis primero
        $output = [];
        $return_code = 0;
        exec("php -l \"$archivo_admin\"", $output, $return_code);
        
        if ($return_code === 0) {
            echo "✓ Sintaxis correcta en admin-menu.php<br>";
            
            // Intentar incluir el archivo
            ob_start();
            include_once $archivo_admin;
            $output_content = ob_get_clean();
            
            if (!empty($output_content)) {
                echo "⚠️ Output generado: <pre>" . htmlspecialchars($output_content) . "</pre>";
            }
            
            echo "✓ Archivo admin-menu.php cargado exitosamente<br>";
            mostrar_paso(7, "admin-menu.php cargado correctamente", 'exitoso');
        } else {
            echo "❌ Error de sintaxis en admin-menu.php: " . implode("\n", $output) . "<br>";
            mostrar_paso(7, "Error de sintaxis en admin-menu.php", 'error');
        }
    } else {
        mostrar_paso(7, "Archivo admin-menu.php no encontrado", 'advertencia');
    }
    
} catch (Exception $e) {
    mostrar_paso(7, "Error cargando admin-menu.php: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
} catch (ParseError $e) {
    mostrar_paso(7, "Error de sintaxis en admin-menu.php: " . $e->getMessage(), 'error');
    capturar_error("Parse Error: " . $e->getMessage());
}

try {
    // PASO 8: Probar carga de shortcodes.php
    mostrar_paso(8, "Probando carga de public/shortcodes.php");
    
    $archivo_shortcodes = WPCW_PLUGIN_DIR . 'public/shortcodes.php';
    if (file_exists($archivo_shortcodes)) {
        // Verificar sintaxis primero
        $output = [];
        $return_code = 0;
        exec("php -l \"$archivo_shortcodes\"", $output, $return_code);
        
        if ($return_code === 0) {
            echo "✓ Sintaxis correcta en shortcodes.php<br>";
            
            // Intentar incluir el archivo
            ob_start();
            include_once $archivo_shortcodes;
            $output_content = ob_get_clean();
            
            if (!empty($output_content)) {
                echo "⚠️ Output generado: <pre>" . htmlspecialchars($output_content) . "</pre>";
            }
            
            echo "✓ Archivo shortcodes.php cargado exitosamente<br>";
            mostrar_paso(8, "shortcodes.php cargado correctamente", 'exitoso');
        } else {
            echo "❌ Error de sintaxis en shortcodes.php: " . implode("\n", $output) . "<br>";
            mostrar_paso(8, "Error de sintaxis en shortcodes.php", 'error');
        }
    } else {
        mostrar_paso(8, "Archivo shortcodes.php no encontrado", 'advertencia');
    }
    
} catch (Exception $e) {
    mostrar_paso(8, "Error cargando shortcodes.php: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
} catch (ParseError $e) {
    mostrar_paso(8, "Error de sintaxis en shortcodes.php: " . $e->getMessage(), 'error');
    capturar_error("Parse Error: " . $e->getMessage());
}

try {
    // PASO 9: Probar funciones específicas
    mostrar_paso(9, "Probando funciones específicas del plugin");
    
    echo "<h3>Funciones disponibles:</h3>";
    echo "<ul>";
    
    // Verificar funciones de post types
    if (function_exists('wpcw_register_post_types')) {
        echo "<li style='color: green;'>✅ wpcw_register_post_types() disponible</li>";
    } else {
        echo "<li style='color: red;'>❌ wpcw_register_post_types() NO disponible</li>";
    }
    
    // Verificar funciones de WhatsApp
    if (function_exists('wpcw_generate_whatsapp_link')) {
        echo "<li style='color: green;'>✅ wpcw_generate_whatsapp_link() disponible</li>";
    } else {
        echo "<li style='color: red;'>❌ wpcw_generate_whatsapp_link() NO disponible</li>";
    }
    
    // Verificar funciones de validación
    if (function_exists('wpcw_validate_phone_number')) {
        echo "<li style='color: green;'>✅ wpcw_validate_phone_number() disponible</li>";
    } else {
        echo "<li style='color: red;'>❌ wpcw_validate_phone_number() NO disponible</li>";
    }
    
    echo "</ul>";
    
    mostrar_paso(9, "Verificación de funciones completada", 'exitoso');
    
} catch (Exception $e) {
    mostrar_paso(9, "Error verificando funciones: " . $e->getMessage(), 'error');
    capturar_error($e->getMessage());
}

// PASO 10: Resumen final
echo "<hr>";
echo "<h2>📊 Resumen de la Prueba Escalonada</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Directorio del Plugin:</strong> " . (defined('WPCW_PLUGIN_DIR') ? WPCW_PLUGIN_DIR : 'No definido') . "</p>";

echo "<h3>🎯 Próximos Pasos Recomendados:</h3>";
echo "<ol>";
echo "<li>Si todos los pasos fueron exitosos, el problema puede estar en la integración con WordPress</li>";
echo "<li>Si algún paso falló, revisar el archivo específico que causó el error</li>";
echo "<li>Verificar que WordPress esté correctamente configurado</li>";
echo "<li>Probar la activación del plugin standalone en WordPress</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>Prueba escalonada completada. Revisa los resultados anteriores para identificar problemas específicos.</em></p>";
?>