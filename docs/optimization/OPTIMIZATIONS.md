# Optimizaciones y Mejoras de Rendimiento

## Resumen de Optimizaciones Implementadas

Este documento detalla las optimizaciones implementadas para mejorar el rendimiento del plugin WP-Cupón-WhatsApp en WordPress y WooCommerce.

## 1. Optimizaciones de Base de Datos

### Índices Optimizados
```sql
ALTER TABLE `{$wpdb->prefix}wpcw_canjes` 
ADD INDEX `idx_user_business` (`user_id`, `business_id`),
ADD INDEX `idx_status_date` (`status`, `date_created`),
ADD INDEX `idx_coupon_user` (`coupon_id`, `user_id`);
```

### Consultas Eficientes
```php
// Ejemplo de consulta optimizada para listado de cupones
$coupons = $wpdb->get_results($wpdb->prepare("
    SELECT c.ID, c.post_title, m.meta_value as expiry_date
    FROM {$wpdb->posts} c
    LEFT JOIN {$wpdb->postmeta} m ON c.ID = m.post_id AND m.meta_key = 'expiry_date'
    WHERE c.post_type = 'shop_coupon'
    AND c.post_status = 'publish'
    AND NOT EXISTS (
        SELECT 1 FROM {$wpdb->prefix}wpcw_canjes r
        WHERE r.coupon_id = c.ID AND r.user_id = %d
    )
    LIMIT %d
", $user_id, $limit));
```

## 2. Caché Implementado

### Transients para Datos Frecuentes
```php
function get_active_businesses() {
    $cache_key = 'wpcw_active_businesses';
    $businesses = get_transient($cache_key);
    
    if (false === $businesses) {
        $businesses = get_posts([
            'post_type' => 'wpcw_business',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        set_transient($cache_key, $businesses, HOUR_IN_SECONDS);
    }
    
    return $businesses;
}
```

### Fragmentos de Caché
```php
function get_business_stats($business_id) {
    $cache_key = 'wpcw_stats_' . $business_id;
    $stats = wp_cache_get($cache_key);
    
    if (false === $stats) {
        // Calcular estadísticas
        $stats = calculate_business_stats($business_id);
        wp_cache_set($cache_key, $stats, '', 1800); // 30 minutos
    }
    
    return $stats;
}
```

## 3. Optimización de Consultas WP_Query

### Consultas Eficientes
```php
function get_business_coupons($business_id) {
    return new WP_Query([
        'post_type' => 'shop_coupon',
        'posts_per_page' => -1,
        'no_found_rows' => true,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => '_wpcw_business_id',
                'value' => $business_id
            ]
        ],
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false
    ]);
}
```

## 4. Optimización de Assets

### Carga Condicional
```php
function load_plugin_assets() {
    // Solo cargar en páginas necesarias
    if (!is_wpcw_page()) {
        return;
    }

    wp_enqueue_style(
        'wpcw-styles',
        WPCW_PLUGIN_URL . 'assets/css/wpcw.min.css',
        [],
        WPCW_VERSION
    );

    wp_enqueue_script(
        'wpcw-scripts',
        WPCW_PLUGIN_URL . 'assets/js/wpcw.min.js',
        ['jquery'],
        WPCW_VERSION,
        true
    );
}
```

### Minificación
```php
// Configuración de minificación en Grunt/Gulp
module.exports = {
    css: {
        files: {
            'assets/css/wpcw.min.css': ['src/css/*.css']
        }
    },
    js: {
        files: {
            'assets/js/wpcw.min.js': ['src/js/*.js']
        }
    }
}
```

## 5. Optimización de WooCommerce

### Hooks Selectivos
```php
function selective_wc_hooks() {
    // Remover hooks innecesarios en páginas de cupones
    if (is_wpcw_coupon_page()) {
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    }
}
add_action('wp', 'selective_wc_hooks');
```

### Caché de Productos
```php
function cache_coupon_products($coupon_id) {
    $products = wc_get_products([
        'status' => 'publish',
        'limit' => -1,
        'return' => 'ids',
    ]);
    wp_cache_set('wpcw_coupon_products_' . $coupon_id, $products, '', HOUR_IN_SECONDS);
}
```

## 6. Mantenimiento Automático

### Limpieza Programada
```php
function schedule_maintenance() {
    if (!wp_next_scheduled('wpcw_maintenance')) {
        wp_schedule_event(time(), 'daily', 'wpcw_maintenance');
    }
}

function perform_maintenance() {
    // Limpiar canjes expirados
    clean_expired_redemptions();
    
    // Optimizar tablas
    optimize_tables();
    
    // Limpiar caché
    clear_plugin_cache();
}
add_action('wpcw_maintenance', 'perform_maintenance');
```

## 7. Monitoreo de Rendimiento

### Logger de Rendimiento
```php
function log_performance_metrics() {
    global $wpdb;
    
    $metrics = [
        'queries' => $wpdb->num_queries,
        'memory' => memory_get_peak_usage(true),
        'time' => timer_stop()
    ];
    
    if ($metrics['queries'] > 100 || $metrics['memory'] > 64 * MB_IN_BYTES) {
        error_log(sprintf(
            'WPCW Performance Alert: %d queries, %s memory used',
            $metrics['queries'],
            size_format($metrics['memory'])
        ));
    }
}
add_action('shutdown', 'log_performance_metrics');
```

## 8. Recomendaciones de Configuración

### WordPress
1. Configurar object-cache.php
2. Activar el caché de página
3. Usar PHP 7.4 o superior

### WooCommerce
1. Deshabilitar carritos persistentes
2. Optimizar sesiones
3. Limitar productos por página

### Servidor
1. Habilitar compresión GZIP
2. Configurar expires headers
3. Usar servidor de caché (Redis/Memcached)
