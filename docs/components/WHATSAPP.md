# Integración con WhatsApp

## Descripción General
El sistema utiliza enlaces wa.me para facilitar la comunicación directa con WhatsApp sin necesidad de API. Esto permite a los usuarios abrir conversaciones de WhatsApp directamente desde el navegador o aplicación.

## Configuración

### Requisitos
1. Número de WhatsApp válido del comercio
2. Formato correcto del número (internacional)
3. WhatsApp instalado en el dispositivo del usuario

### Configuración en WordPress
```php
// No se requiere configuración de API
// Solo números de teléfono válidos en los comercios
$whatsapp_number = '+5491123456789';
$wa_link = 'https://wa.me/' . $whatsapp_number;
```

## Funcionalidades

### 1. Generación de Enlaces wa.me
```php
/**
 * Genera enlaces de WhatsApp usando wa.me
 */
function wpcw_generate_whatsapp_link($phone_number, $message = '') {
    $formatted_number = wpcw_format_whatsapp_number($phone_number);
    $base_url = 'https://wa.me/';
    
    if (!empty($message)) {
        return $base_url . $formatted_number . '?text=' . urlencode($message);
    }
    
    return $base_url . $formatted_number;
}
```

### 2. Formateo de Números
```php
/**
 * Formatea números para WhatsApp (Argentina)
 */
function wpcw_format_whatsapp_number($phone_number) {
    // Eliminar caracteres no numéricos
    $clean_number = preg_replace('/\D/', '', $phone_number);
    
    // Agregar código de país si es necesario
    if (substr($clean_number, 0, 1) === '0') {
        $clean_number = '54' . substr($clean_number, 1);
    }
    
    if (strlen($clean_number) <= 10) {
        $clean_number = '54' . $clean_number;
    }
    
    return $clean_number;
}
```

### 3. Plantillas de Mensajes

#### Canje de Cupón
```php
function wpcw_get_canje_message($canje_data) {
    return sprintf(
        "🎫 *Solicitud de Canje de Cupón*\n\n" .
        "Número de Canje: %s\n" .
        "Cupón: %s\n" .
        "Comercio: %s\n" .
        "Fecha: %s\n\n" .
        'Para confirmar el canje, utilice este código: %s',
        $canje_data['numero_canje'],
        $canje_data['nombre_cupon'],
        $canje_data['nombre_comercio'],
        $canje_data['fecha_solicitud'],
        $canje_data['token_confirmacion']
    );
}
```

### 4. Validación de Enlaces
```php
/**
 * Valida un número de WhatsApp
 */
function wpcw_validate_whatsapp_number($phone_number) {
    if (empty($phone_number) || !is_string($phone_number)) {
        return false;
    }
    
    $clean_number = wpcw_format_whatsapp_number($phone_number);
    
    // Verificar longitud mínima
    if (strlen($clean_number) < 11) {
        return false;
    }
    
    // Verificar código de país Argentina
    if (substr($clean_number, 0, 2) !== '54') {
        return false;
    }
    
    return true;
}
```

### 5. Generación de Enlaces Completos
```php
/**
 * Genera el enlace completo para canje
 */
function wpcw_get_canje_whatsapp_link($canje_data) {
    $message = wpcw_get_canje_message($canje_data);
    return wpcw_generate_whatsapp_link($canje_data['telefono_comercio'], $message);
}
```

## Seguridad

### 1. Sanitización de Números
```php
function wpcw_sanitize_phone_number($number) {
    // Eliminar caracteres peligrosos
    $number = sanitize_text_field($number);
    
    // Solo permitir números, espacios, guiones y signos +
    $number = preg_replace('/[^0-9\s\-\+]/', '', $number);
    
    return $number;
}
```

### 2. Validación Estricta
```php
function wpcw_validate_phone_strict($number) {
    $sanitized = wpcw_sanitize_phone_number($number);
    $formatted = wpcw_format_whatsapp_number($sanitized);
    
    // Verificar que no sea un número falso conocido
    $fake_numbers = [
        '541111111111',
        '541234567890',
        '549999999999'
    ];
    
    return !in_array($formatted, $fake_numbers) && 
           wpcw_validate_whatsapp_number($formatted);
}
```

### 3. Escape de Mensajes
```php
function wpcw_escape_message($message) {
    // Escapar caracteres especiales para URL
    return urlencode(sanitize_text_field($message));
}
```

## Manejo de Errores

### 1. Códigos de Error
```php
class WPCW_WhatsApp_Error_Codes {
    const INVALID_NUMBER = 'invalid_number';
    const FORMATTING_ERROR = 'formatting_error';
    const VALIDATION_ERROR = 'validation_error';
    const FAKE_NUMBER = 'fake_number';
}
```

### 2. Registro de Errores
```php
function wpcw_log_whatsapp_error($error, $context = []) {
    error_log(sprintf(
        '[WP Cupón WhatsApp] Error: %s | Context: %s',
        $error,
        json_encode($context)
    ));
}
```

## Optimización

### 1. Caché de Validación
```php
// Cachear resultados de validación
function wpcw_cache_validation($phone, $result) {
    wp_cache_set(
        'wpcw_validation_' . md5($phone),
        $result,
        'wpcw_whatsapp',
        HOUR_IN_SECONDS
    );
}

function wpcw_get_cached_validation($phone) {
    return wp_cache_get(
        'wpcw_validation_' . md5($phone),
        'wpcw_whatsapp'
    );
}
```

### 2. Formateo Eficiente
```php
// Evitar formateo repetitivo
function wpcw_format_once($phone) {
    static $formatted_cache = [];
    
    if (!isset($formatted_cache[$phone])) {
        $formatted_cache[$phone] = wpcw_format_whatsapp_number($phone);
    }
    
    return $formatted_cache[$phone];
}
```

## Pruebas

### 1. Números de Prueba
```php
// Números válidos para pruebas
define('WPCW_TEST_VALID_NUMBER', '5491123456789');
define('WPCW_TEST_INVALID_NUMBER', '541111111111');
```

### 2. Casos de Prueba
```php
class WPCW_WhatsApp_Test extends WP_UnitTestCase {
    
    public function test_number_formatting() {
        $result = wpcw_format_whatsapp_number('011-2345-6789');
        $this->assertEquals('5491123456789', $result);
    }
    
    public function test_link_generation() {
        $link = wpcw_generate_whatsapp_link('5491123456789', 'Test message');
        $expected = 'https://wa.me/5491123456789?text=' . urlencode('Test message');
        $this->assertEquals($expected, $link);
    }
    
    public function test_number_validation() {
        $this->assertTrue(wpcw_validate_whatsapp_number(WPCW_TEST_VALID_NUMBER));
        $this->assertFalse(wpcw_validate_whatsapp_number(WPCW_TEST_INVALID_NUMBER));
    }
}
```

## Monitoreo

### 1. Métricas de Uso
```php
// Registrar uso de enlaces wa.me
function wpcw_track_whatsapp_usage($phone, $action = 'link_generated') {
    $stats = get_option('wpcw_whatsapp_stats', []);
    $today = date('Y-m-d');
    
    if (!isset($stats[$today])) {
        $stats[$today] = [];
    }
    
    if (!isset($stats[$today][$action])) {
        $stats[$today][$action] = 0;
    }
    
    $stats[$today][$action]++;
    update_option('wpcw_whatsapp_stats', $stats);
}
```

### 2. Validación de Enlaces
```php
// Verificar que los enlaces generados sean válidos
function wpcw_validate_generated_link($link) {
    return filter_var($link, FILTER_VALIDATE_URL) && 
           strpos($link, 'https://wa.me/') === 0;
}
```

### 3. Reportes
```php
// Generar reporte de uso
function wpcw_get_whatsapp_report($days = 30) {
    $stats = get_option('wpcw_whatsapp_stats', []);
    $report = [];
    
    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $report[$date] = $stats[$date] ?? [];
    }
    
    return $report;
}
 ```

## Ventajas del Enfoque wa.me

### 1. Simplicidad
- No requiere configuración de API
- No necesita tokens de acceso
- Funciona inmediatamente

### 2. Compatibilidad
- Compatible con todos los dispositivos
- Funciona en web y móvil
- No depende de servicios externos

### 3. Confiabilidad
- Sin límites de API
- Sin problemas de autenticación
- Siempre disponible

### 4. Facilidad de Mantenimiento
- Código más simple
- Menos puntos de falla
- Fácil de debuggear

## Implementación Recomendada

### 1. Uso Básico
```php
// Generar enlace simple
$link = wpcw_generate_whatsapp_link('5491123456789');
echo '<a href="' . esc_url($link) . '" target="_blank">Contactar por WhatsApp</a>';
```

### 2. Con Mensaje Predefinido
```php
// Enlace con mensaje de canje
$canje_data = [
    'numero_canje' => '12345',
    'nombre_cupon' => 'Descuento 20%',
    'nombre_comercio' => 'Mi Comercio',
    'fecha_solicitud' => date('Y-m-d H:i:s'),
    'token_confirmacion' => 'ABC123',
    'telefono_comercio' => '5491123456789'
];

$link = wpcw_get_canje_whatsapp_link($canje_data);
echo '<a href="' . esc_url($link) . '" target="_blank">Solicitar Canje</a>';
```

### 3. Validación Previa
```php
// Validar antes de generar enlace
$phone = '011-2345-6789';
if (wpcw_validate_whatsapp_number($phone)) {
    $link = wpcw_generate_whatsapp_link($phone, 'Hola, me interesa el cupón');
    // Mostrar enlace
} else {
    // Mostrar error
}
```
