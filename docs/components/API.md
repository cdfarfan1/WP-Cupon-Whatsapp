# API REST

## Descripción General
La API REST del plugin proporciona endpoints para interactuar con el sistema de cupones y canjes programáticamente.

## Endpoints

### Cupones

#### Listar Cupones
```http
GET /wp-json/wp-cupon-whatsapp/v1/coupons
```

**Parámetros**
```json
{
    "page": 1,
    "per_page": 10,
    "status": "active",
    "business_id": 123
}
```

**Respuesta**
```json
{
    "status": "success",
    "data": {
        "coupons": [
            {
                "id": 1,
                "code": "DESCUENTO20",
                "business_id": 123,
                "expiry_date": "2025-12-31",
                "status": "active"
            }
        ],
        "total": 50,
        "pages": 5
    }
}
```

#### Obtener Cupón
```http
GET /wp-json/wp-cupon-whatsapp/v1/coupons/{id}
```

**Respuesta**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "code": "DESCUENTO20",
        "business_id": 123,
        "expiry_date": "2025-12-31",
        "status": "active",
        "redemption_count": 15,
        "whatsapp_enabled": true
    }
}
```

### Canjes

#### Solicitar Canje
```http
POST /wp-json/wp-cupon-whatsapp/v1/redemptions
```

**Cuerpo**
```json
{
    "coupon_id": 1,
    "user_id": 456,
    "phone": "5491112345678"
}
```

**Respuesta**
```json
{
    "status": "success",
    "data": {
        "redemption_id": 789,
        "token": "abc123xyz",
        "expiry": "2025-07-18 15:00:00"
    }
}
```

#### Validar Canje
```http
PUT /wp-json/wp-cupon-whatsapp/v1/redemptions/{id}/validate
```

**Cuerpo**
```json
{
    "token": "abc123xyz",
    "business_id": 123
}
```

### Comercios

#### Listar Comercios
```http
GET /wp-json/wp-cupon-whatsapp/v1/businesses
```

**Respuesta**
```json
{
    "status": "success",
    "data": {
        "businesses": [
            {
                "id": 123,
                "name": "Comercio Demo",
                "phone": "5491112345678",
                "status": "active"
            }
        ]
    }
}
```

## Autenticación

### JWT
```php
// Configuración de JWT
add_filter('jwt_auth_token_before_dispatch', function($data, $user) {
    $data['business_id'] = get_user_meta($user->ID, 'business_id', true);
    return $data;
}, 10, 2);
```

### Nonces
```php
// Para uso en frontend
$nonce = wp_create_nonce('wp_rest');
```

## Manejo de Errores

### Códigos de Error
```php
class WPCW_API_Errors {
    const INVALID_COUPON = 'invalid_coupon';
    const EXPIRED_TOKEN = 'expired_token';
    const UNAUTHORIZED = 'unauthorized';
}
```

### Formato de Error
```json
{
    "status": "error",
    "code": "invalid_coupon",
    "message": "El cupón no existe o ha expirado",
    "data": {
        "status": 404
    }
}
```

## Permisos

### Roles y Capacidades
```php
// Verificación de permisos
function wpcw_api_permissions_check($request) {
    $user_id = get_current_user_id();
    
    if (!$user_id) {
        return false;
    }
    
    return current_user_can('manage_wpcw_coupons');
}
```

## Rate Limiting

### Configuración
```php
function wpcw_api_rate_limit($request) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $endpoint = $request->get_route();
    
    $key = "wpcw_rate_limit_{$ip}_{$endpoint}";
    $count = (int)get_transient($key);
    
    if ($count > 100) { // 100 requests per hour
        return new WP_Error(
            'too_many_requests',
            'Rate limit exceeded',
            array('status' => 429)
        );
    }
    
    set_transient($key, $count + 1, HOUR_IN_SECONDS);
    return true;
}
```

## Caché

### Configuración
```php
// Caché de respuestas
add_filter('rest_post_dispatch', function($result, $server, $request) {
    if ('GET' === $request->get_method()) {
        $cache_key = 'wpcw_api_' . md5($request->get_route());
        wp_cache_set($cache_key, $result, 'wpcw_api', 300);
    }
    return $result;
}, 10, 3);
```

## Documentación OpenAPI

### Especificación
```yaml
openapi: 3.0.0
info:
  title: WP Cupón WhatsApp API
  version: 1.0.0
paths:
  /wp-json/wp-cupon-whatsapp/v1/coupons:
    get:
      summary: Lista cupones
      parameters:
        - name: page
          in: query
          schema:
            type: integer
```

## Webhooks

### Registro de Webhook
```php
function register_webhook($url, $events) {
    $webhook = [
        'url' => esc_url_raw($url),
        'events' => array_map('sanitize_text_field', $events),
        'secret' => wp_generate_password(32, false)
    ];
    
    update_option('wpcw_webhooks', $webhook);
}
```

### Envío de Eventos
```php
function send_webhook_event($event, $data) {
    $webhook = get_option('wpcw_webhooks');
    
    if (!$webhook || !in_array($event, $webhook['events'])) {
        return;
    }
    
    $payload = [
        'event' => $event,
        'timestamp' => time(),
        'data' => $data
    ];
    
    $signature = hash_hmac('sha256', json_encode($payload), $webhook['secret']);
    
    wp_remote_post($webhook['url'], [
        'body' => $payload,
        'headers' => [
            'X-WPCW-Signature' => $signature
        ]
    ]);
}
```
