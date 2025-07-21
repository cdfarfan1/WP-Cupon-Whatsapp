# Gestión de Cupones

## Descripción General
El sistema de gestión de cupones permite crear, administrar y realizar seguimiento de cupones digitales que pueden ser canjeados a través de WhatsApp.

## Estructura de Datos

### Post Type: shop_coupon
Extiende el post type nativo de WooCommerce con campos adicionales.

```php
// Meta campos adicionales
_wpcw_enabled           => bool    // Habilitar para canje por WhatsApp
_wpcw_business_id       => int     // ID del comercio asociado
_wpcw_expiry_reminder   => bool    // Enviar recordatorio de vencimiento
_wpcw_auto_confirm      => bool    // Confirmación automática
_wpcw_whatsapp_text    => string  // Mensaje personalizado de WhatsApp
_wpcw_max_uses         => int     // Máximo de usos por usuario
_wpcw_redemption_hours => string  // Horario permitido para canje
```

## Flujo de Canje

1. **Solicitud de Canje**
```php
function wpcw_request_coupon_redemption($coupon_id, $user_id) {
    // Validaciones
    // Generación de token
    // Creación de registro de canje
    // Envío de mensaje WhatsApp
}
```

2. **Validación de Canje**
```php
function wpcw_validate_redemption($token) {
    // Verificación de token
    // Actualización de estado
    // Notificación a usuario
}
```

## Estadísticas y Reportes

### Métricas Disponibles
- Total de canjes
- Canjes exitosos
- Canjes fallidos
- Histórico por fechas
- Tasa de conversión

### Gráficos
```javascript
// Ejemplo de configuración de Chart.js
new Chart(ctx, {
    type: 'line',
    data: {
        datasets: [{
            label: 'Canjes Exitosos',
            data: successful_data,
            borderColor: 'rgb(75, 192, 192)'
        }]
    }
});
```

## Hooks Disponibles

### Filtros
```php
// Modificar texto de WhatsApp
apply_filters('wpcw_whatsapp_message', $message, $coupon);

// Personalizar validación
apply_filters('wpcw_validate_redemption', $is_valid, $token);
```

### Acciones
```php
// Antes del canje
do_action('wpcw_before_redemption', $coupon_id, $user_id);

// Después del canje
do_action('wpcw_after_redemption', $redemption_id, $status);
```

## Integración con WooCommerce

### Campos Personalizados
```php
add_action('woocommerce_coupon_options', 'wpcw_add_coupon_options');
add_action('woocommerce_coupon_options_save', 'wpcw_save_coupon_options');
```

### Validación
```php
add_filter('woocommerce_coupon_is_valid', 'wpcw_validate_coupon', 10, 2);
```

## Seguridad

### Validaciones Implementadas
1. Verificación de usuario
2. Control de límites de uso
3. Validación de horarios
4. Protección contra duplicados
5. Verificación de tokens

### Sanitización de Datos
```php
// Ejemplo de sanitización
$input = sanitize_text_field($_POST['input']);
$number = absint($_POST['number']);
```

## Personalización

### CSS Personalizado
```css
.wpcw-coupon-card {
    /* Estilos base */
}

.wpcw-coupon-card.active {
    /* Estilos para cupones activos */
}

.wpcw-coupon-card.expired {
    /* Estilos para cupones vencidos */
}
```

### JavaScript
```javascript
// Ejemplo de personalización del comportamiento
jQuery(document).ready(function($) {
    $('.wpcw-redeem-button').on('click', function(e) {
        e.preventDefault();
        // Lógica de canje
    });
});
```

## Depuración

### Modo Debug
```php
define('WPCW_DEBUG', true);
```

### Registro de Errores
```php
WPCW_Logger::log('error', 'Mensaje de error', [
    'coupon_id' => $coupon_id,
    'user_id' => $user_id
]);
```

## Pruebas

### Pruebas Unitarias
```bash
# Ejecutar pruebas
phpunit tests/
```

### Casos de Prueba Recomendados
1. Creación de cupón
2. Solicitud de canje
3. Validación de token
4. Límites de uso
5. Notificaciones

## Optimización

### Caché
```php
// Ejemplo de caché
$cache_key = 'wpcw_coupon_stats_' . $coupon_id;
$stats = wp_cache_get($cache_key);

if (false === $stats) {
    $stats = wpcw_calculate_coupon_stats($coupon_id);
    wp_cache_set($cache_key, $stats, 'wpcw', HOUR_IN_SECONDS);
}
```

### Índices de Base de Datos
```sql
CREATE INDEX idx_token_confirmacion ON {prefix}wpcw_canjes (token_confirmacion);
CREATE INDEX idx_estado_canje ON {prefix}wpcw_canjes (estado_canje);
```
