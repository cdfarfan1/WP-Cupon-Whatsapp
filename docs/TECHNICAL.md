# WP Cupón WhatsApp - Documentación Técnica

## Versión 1.1.0

### Descripción
Plugin de WordPress para la gestión de cupones canjeables a través de WhatsApp, integrado con WooCommerce y Elementor.

### Requisitos del Sistema
- WordPress >= 5.0
- PHP >= 7.4
- WooCommerce >= 6.0.0
- Elementor >= 3.0.0

### Base de Datos
#### Tablas Personalizadas
- `{prefix}wpcw_canjes`: Almacena información de canjes
- `{prefix}wpcw_logs`: Registro de actividades

#### Post Types
- `wpcw_business`: Comercios
- `shop_coupon`: Cupones (WooCommerce extendido)

#### Taxonomías
- `wpcw_business_type`: Tipos de comercio

### APIs y Endpoints

#### REST API
Endpoints disponibles en `wp-json/wp-cupon-whatsapp/v1/`:

```
GET /coupons
POST /coupons/redeem
GET /businesses
POST /businesses/register
GET /stats
```

#### Webhooks
- Entrada: `/wp-json/wp-cupon-whatsapp/v1/webhook`
- Salida: Configurables en ajustes

### Constantes y Configuración
```php
define('WPCW_VERSION', '1.1.0');
define('WPCW_MIN_WP_VERSION', '5.0');
define('WPCW_MIN_PHP_VERSION', '7.4');
define('WPCW_MIN_ELEMENTOR_VERSION', '3.0.0');
define('WPCW_MIN_WOOCOMMERCE_VERSION', '6.0.0');
```

### Hooks

#### Filtros
```php
apply_filters('wpcw_validate_coupon', $is_valid, $coupon_id, $user_id);
apply_filters('wpcw_whatsapp_message_template', $message, $context);
apply_filters('wpcw_business_fields', $fields);
apply_filters('wpcw_redemption_rules', $rules, $coupon_id);
```

#### Acciones
```php
do_action('wpcw_before_coupon_redeem', $coupon_id, $user_id);
do_action('wpcw_after_coupon_redeem', $coupon_id, $user_id);
do_action('wpcw_business_registered', $business_id);
do_action('wpcw_redemption_status_changed', $redemption_id, $status);
```

### Integraciones

#### WhatsApp Business API
- Meta WhatsApp Business API
- Twilio WhatsApp API
- 360dialog WhatsApp API

Configuración en `includes/integrations/whatsapp/`:
```php
abstract class WPCW_WhatsApp_Provider {
    abstract public function send_message($to, $template, $params);
    abstract public function verify_number($number);
}
```

#### CRM
- HubSpot
- Salesforce
- Zoho

Implementación en `includes/integrations/crm/`:
```php
interface WPCW_CRM_Provider {
    public function sync_contact($user_data);
    public function sync_deal($coupon_data);
    public function track_event($event_name, $properties);
}
```

#### Analytics
- Google Analytics 4
- Facebook Pixel
- Custom Events

### Seguridad

#### Validación y Sanitización
- Nonces en todos los formularios
- Sanitización de entrada/salida
- Escape de datos

#### GDPR
- Exportadores de datos personales
- Eliminadores de datos
- Registros de consentimiento

### Optimización

#### Caché
```php
class WPCW_Cache {
    public static function get($key) {
        return wp_cache_get($key, 'wp-cupon-whatsapp');
    }

    public static function set($key, $value, $expiration = 3600) {
        wp_cache_set($key, $value, 'wp-cupon-whatsapp', $expiration);
    }
}
```

#### Transients
```php
class WPCW_Transients {
    public static function get_coupon_stats($coupon_id) {
        $stats = get_transient('wpcw_coupon_stats_' . $coupon_id);
        if (false === $stats) {
            $stats = self::calculate_coupon_stats($coupon_id);
            set_transient('wpcw_coupon_stats_' . $coupon_id, $stats, HOUR_IN_SECONDS);
        }
        return $stats;
    }
}
```

### CLI

WP-CLI comandos personalizados:
```bash
wp wpcw coupon list
wp wpcw coupon create
wp wpcw business import
wp wpcw stats generate
```

### Testing

#### PHPUnit Tests
```bash
composer install
./vendor/bin/phpunit
```

#### E2E Tests
```bash
npm install
npm run test:e2e
```

### Deployment

#### Continuous Integration
- GitHub Actions
- CircleCI
- Jenkins

#### Release Process
1. Actualizar versión en archivos
2. Generar changelog
3. Crear tag
4. Deploy a WordPress.org

### Contribución
Ver CONTRIBUTING.md para guidelines.

### Licencia
GPL v2 o posterior
