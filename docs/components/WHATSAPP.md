# Integración con WhatsApp

## Descripción General
El sistema utiliza la API de WhatsApp Business para enviar notificaciones y facilitar el proceso de canje de cupones.

## Configuración

### Requisitos
1. Cuenta de WhatsApp Business API
2. Número verificado
3. Webhooks configurados
4. Token de acceso

### Configuración en WordPress
```php
// Ejemplo de configuración
define('WPCW_WHATSAPP_API_KEY', 'tu_api_key');
define('WPCW_WHATSAPP_NUMBER', 'tu_numero');
```

## Funcionalidades

### 1. Envío de Mensajes
```php
/**
 * Envía un mensaje de WhatsApp
 */
class WPCW_WhatsApp_Handler {
    public function send_message($to, $template, $params) {
        // Validación de número
        // Formateo de mensaje
        // Envío a través de API
        // Registro de respuesta
    }
}
```

### 2. Plantillas de Mensajes

#### Canje de Cupón
```json
{
    "template_name": "coupon_redemption",
    "language": "es",
    "components": [
        {
            "type": "header",
            "parameters": [
                {
                    "type": "text",
                    "text": "🎫 Cupón {{coupon_code}}"
                }
            ]
        },
        {
            "type": "body",
            "parameters": [
                {
                    "type": "text",
                    "text": "{{business_name}}"
                },
                {
                    "type": "text",
                    "text": "{{expiry_date}}"
                }
            ]
        }
    ]
}
```

### 3. Webhooks

#### Endpoints
```php
// Registro de webhook
add_action('rest_api_init', function() {
    register_rest_route('wp-cupon-whatsapp/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => 'wpcw_handle_webhook',
        'permission_callback' => '__return_true'
    ]);
});
```

#### Manejo de Eventos
```php
function wpcw_handle_webhook($request) {
    $body = $request->get_body();
    $data = json_decode($body, true);

    switch ($data['type']) {
        case 'message':
            return handle_message($data);
        case 'status':
            return handle_status($data);
        default:
            return new WP_Error('unknown_type');
    }
}
```

## Seguridad

### 1. Verificación de Webhooks
```php
function verify_webhook_signature($request) {
    $signature = $request->get_header('X-Hub-Signature');
    $body = $request->get_body();
    
    $expected = hash_hmac('sha256', $body, WPCW_WHATSAPP_WEBHOOK_SECRET);
    return hash_equals('sha256=' . $expected, $signature);
}
```

### 2. Validación de Números
```php
function validate_phone_number($number) {
    // Limpieza
    $number = preg_replace('/[^0-9]/', '', $number);
    
    // Formato internacional
    if (substr($number, 0, 2) !== '54') {
        $number = '54' . $number;
    }
    
    return $number;
}
```

## Manejo de Errores

### 1. Códigos de Error
```php
class WPCW_WhatsApp_Error_Codes {
    const INVALID_NUMBER = 'invalid_number';
    const API_ERROR = 'api_error';
    const TEMPLATE_ERROR = 'template_error';
    const RATE_LIMIT = 'rate_limit';
}
```

### 2. Registro de Errores
```php
function log_whatsapp_error($error, $context = []) {
    WPCW_Logger::log('whatsapp_error', $error, $context);
}
```

## Optimización

### 1. Cola de Mensajes
```php
// Programar envío
wp_schedule_single_event(
    time() + 300,
    'wpcw_send_delayed_message',
    [$to, $template, $params]
);
```

### 2. Caché de Estado
```php
function cache_message_status($message_id, $status) {
    wp_cache_set(
        'wpcw_msg_' . $message_id,
        $status,
        'wpcw_whatsapp',
        HOUR_IN_SECONDS
    );
}
```

## Pruebas

### 1. Ambiente de Pruebas
```php
define('WPCW_WHATSAPP_TEST_MODE', true);
define('WPCW_WHATSAPP_TEST_NUMBER', '5491100000000');
```

### 2. Casos de Prueba
```php
class WPCW_WhatsApp_Test extends WP_UnitTestCase {
    public function test_message_sending() {
        $handler = new WPCW_WhatsApp_Handler();
        $result = $handler->send_message(
            WPCW_WHATSAPP_TEST_NUMBER,
            'test_template',
            ['param1' => 'test']
        );
        $this->assertTrue($result->success);
    }
}
```

## Monitoreo

### 1. Métricas
- Tasa de entrega
- Tiempo de respuesta
- Errores por tipo
- Uso de plantillas

### 2. Panel de Control
```php
function wpcw_whatsapp_dashboard() {
    $metrics = [
        'messages_sent' => get_total_messages(),
        'delivery_rate' => get_delivery_rate(),
        'error_rate' => get_error_rate()
    ];
    
    include WPCW_PLUGIN_DIR . 'templates/whatsapp-dashboard.php';
}
```

## Personalización

### 1. Filtros
```php
// Modificar mensaje antes del envío
add_filter('wpcw_whatsapp_message', function($message, $template) {
    return $message;
}, 10, 2);

// Personalizar manejo de respuestas
add_filter('wpcw_whatsapp_handle_response', function($response, $message) {
    return $response;
}, 10, 2);
```

### 2. Acciones
```php
// Antes del envío
do_action('wpcw_before_whatsapp_send', $to, $template, $params);

// Después del envío
do_action('wpcw_after_whatsapp_send', $result, $to, $template);
```
