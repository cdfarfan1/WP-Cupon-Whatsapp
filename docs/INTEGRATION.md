# WP Cupón WhatsApp - Guía de Integración

## Versión 1.1.0

Esta guía explica cómo integrar WP Cupón WhatsApp con otros sistemas utilizando nuestro SDK y API REST.

## SDK

### Instalación

```bash
composer require wpcw/sdk
```

### Uso Básico

```php
use WPCW\SDK\WPCW_SDK;

// Inicializar SDK
$sdk = new WPCW_SDK(
    'https://tudominio.com/wp-json/wp-cupon-whatsapp/v1',
    'tu-token-de-api'
);

// Crear un cupón
$coupon = $sdk->create_coupon([
    'title' => 'Cupón de Prueba',
    'amount' => 100,
    'type' => 'fixed_cart',
    'business_id' => 123
]);

// Canjear un cupón
$redemption = $sdk->redeem_coupon($coupon['id'], [
    'customer_phone' => '+1234567890',
    'customer_name' => 'Juan Pérez'
]);
```

## API REST

### Autenticación

La API utiliza autenticación Bearer Token. Incluye el token en el header:

```
Authorization: Bearer tu-token-de-api
```

### Endpoints

#### Cupones

```
GET /wp-json/wp-cupon-whatsapp/v1/coupons
POST /wp-json/wp-cupon-whatsapp/v1/coupons
GET /wp-json/wp-cupon-whatsapp/v1/coupons/{id}
POST /wp-json/wp-cupon-whatsapp/v1/coupons/{id}/redeem
GET /wp-json/wp-cupon-whatsapp/v1/coupons/{id}/stats
```

#### Comercios

```
GET /wp-json/wp-cupon-whatsapp/v1/businesses
POST /wp-json/wp-cupon-whatsapp/v1/businesses
GET /wp-json/wp-cupon-whatsapp/v1/businesses/{id}
GET /wp-json/wp-cupon-whatsapp/v1/businesses/{id}/stats
```

### Ejemplos

#### Crear Cupón

```bash
curl -X POST \
  https://tudominio.com/wp-json/wp-cupon-whatsapp/v1/coupons \
  -H 'Authorization: Bearer tu-token-de-api' \
  -H 'Content-Type: application/json' \
  -d '{
    "title": "Cupón de Prueba",
    "amount": 100,
    "type": "fixed_cart",
    "business_id": 123
  }'
```

#### Canjear Cupón

```bash
curl -X POST \
  https://tudominio.com/wp-json/wp-cupon-whatsapp/v1/coupons/456/redeem \
  -H 'Authorization: Bearer tu-token-de-api' \
  -H 'Content-Type: application/json' \
  -d '{
    "customer_phone": "+1234567890",
    "customer_name": "Juan Pérez"
  }'
```

## Webhooks

### Suscripción

```php
$sdk->subscribe_webhook(
    'https://tu-servidor.com/webhook',
    ['coupon.redeemed', 'business.created']
);
```

### Eventos Disponibles

- `coupon.created`
- `coupon.redeemed`
- `coupon.expired`
- `business.created`
- `business.updated`
- `redemption.status_changed`

### Formato de Payload

```json
{
  "event": "coupon.redeemed",
  "timestamp": "2025-07-15T14:30:00Z",
  "data": {
    "coupon_id": 456,
    "customer": {
      "phone": "+1234567890",
      "name": "Juan Pérez"
    },
    "business_id": 123,
    "status": "pending"
  }
}
```

## Integraciones Específicas

### WooCommerce

```php
add_action('woocommerce_order_status_changed', 'my_handle_order_status', 10, 3);

function my_handle_order_status($order_id, $old_status, $new_status) {
    $sdk = new WPCW_SDK(/* ... */);
    
    if ($new_status === 'completed') {
        $order = wc_get_order($order_id);
        $coupons = $order->get_coupon_codes();
        
        foreach ($coupons as $coupon_code) {
            $sdk->update_coupon_status($coupon_code, 'used');
        }
    }
}
```

### Salesforce

```php
add_action('wpcw_after_coupon_redeem', 'sync_to_salesforce', 10, 2);

function sync_to_salesforce($coupon_id, $customer_data) {
    $sf_client = new SalesforceClient(/* ... */);
    
    // Crear lead en Salesforce
    $sf_client->create_lead([
        'FirstName' => $customer_data['first_name'],
        'LastName' => $customer_data['last_name'],
        'Phone' => $customer_data['phone'],
        'Description' => "Canjeó cupón ID: $coupon_id"
    ]);
}
```

### Google Analytics 4

```php
add_action('wpcw_after_coupon_redeem', 'track_in_ga4', 10, 2);

function track_in_ga4($coupon_id, $customer_data) {
    wp_enqueue_script('google-analytics');
    
    $coupon = get_post($coupon_id);
    
    ?>
    <script>
    gtag('event', 'coupon_redemption', {
        'coupon_id': '<?php echo esc_js($coupon_id); ?>',
        'coupon_title': '<?php echo esc_js($coupon->post_title); ?>',
        'customer_phone': '<?php echo esc_js($customer_data['phone']); ?>'
    });
    </script>
    <?php
}
```

## Seguridad

### Generación de Token

```php
function generate_api_token($user_id) {
    return wp_generate_password(32, false);
}
```

### Validación de Webhooks

```php
add_action('rest_api_init', function() {
    register_rest_route('wp-cupon-whatsapp/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => 'handle_webhook',
        'permission_callback' => function($request) {
            $signature = $request->get_header('X-WPCW-Signature');
            $payload = $request->get_body();
            return verify_webhook_signature($signature, $payload);
        }
    ]);
});
```

## Manejo de Errores

```php
try {
    $sdk->redeem_coupon($coupon_id, $data);
} catch (WPCW_API_Exception $e) {
    error_log($e->getMessage());
    wp_send_json_error([
        'code' => $e->getCode(),
        'message' => $e->getMessage()
    ]);
}
```

## Límites de API

- Rate limit: 60 requests/minute
- Tamaño máximo de payload: 5MB
- Timeout: 30 segundos

## Soporte

- Email: support@wpcuponwhatsapp.com
- Documentación: https://docs.wpcuponwhatsapp.com
- GitHub: https://github.com/wpcw/wp-cupon-whatsapp
