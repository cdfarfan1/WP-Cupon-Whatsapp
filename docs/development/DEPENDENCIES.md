# Requisitos y Dependencias del Plugin

## Plugins Requeridos

### 1. WooCommerce
- **Versión Mínima:** 6.0.0
- **Descripción:** El plugin base de comercio electrónico para WordPress.
- **Uso en el Plugin:** 
  - Sistema de cupones base
  - Gestión de productos
  - Sistema de usuarios y roles
  - API REST para integraciones
- **URL:** https://wordpress.org/plugins/woocommerce/

### 2. Elementor
- **Versión Mínima:** 3.0.0
- **Descripción:** Constructor de páginas necesario para los widgets personalizados.
- **Uso en el Plugin:**
  - Widget de lista de cupones
  - Widget de formulario de adhesión
  - Plantillas personalizadas
- **URL:** https://wordpress.org/plugins/elementor/

## Plugins Recomendados

### 1. WooCommerce PDF Invoices & Packing Slips
- **Descripción:** Para generar comprobantes de canjes en PDF.
- **Uso Opcional:**
  - Generación de comprobantes de canje
  - Historial de transacciones en PDF
- **URL:** https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/

### 2. WPML (Para sitios multilingües)
- **Descripción:** Si necesitas el plugin en múltiples idiomas.
- **Uso Opcional:**
  - Traducción de textos del plugin
  - Gestión de cupones multilingüe
- **URL:** https://wpml.org/

## Requisitos del Sistema

### WordPress
- **Versión Mínima:** 5.0
- **Configuración Recomendada:**
  - Permalinks activados
  - Roles y capacidades estándar habilitados
  - REST API habilitada

### Servidor
- **PHP Versión:** 7.4 o superior
- **MySQL Versión:** 5.6 o superior
- **Extensiones PHP Requeridas:**
  - JSON
  - cURL
  - MySQLi o PDO_MySQL
  - mbstring

### Requisitos de Base de Datos
- Privilegios para crear tablas
- Codificación UTF-8
- Permisos para crear índices

## Configuración Recomendada

### WooCommerce
1. Configuración de Cupones:
   - Habilitar el uso de cupones
   - Configurar restricciones globales

2. Roles y Permisos:
   - Asegurar que existe el rol "customer"
   - Configurar permisos de administrador

### Elementor
1. Configuración General:
   - Habilitar widgets personalizados
   - Configurar permisos de edición

2. Plantillas:
   - Importar plantillas predefinidas
   - Configurar estilos globales

## Instalación y Activación

1. **Orden de Instalación:**
   ```
   1. WooCommerce
   2. Elementor
   3. WP-Cupón-WhatsApp
   ```

2. **Verificación Post-Instalación:**
   - Comprobar creación de tablas en base de datos
   - Verificar roles y capacidades
   - Confirmar widgets disponibles
   - Validar shortcodes

3. **Configuración Inicial:**
   ```php
   // Ejemplo de verificación de dependencias
   function wpcw_check_dependencies() {
       if (!class_exists('WooCommerce')) {
           deactivate_plugins(plugin_basename(__FILE__));
           wp_die(__('WP-Cupón-WhatsApp requiere WooCommerce para funcionar.', 'wp-cupon-whatsapp'));
       }
       
       if (!did_action('elementor/loaded')) {
           deactivate_plugins(plugin_basename(__FILE__));
           wp_die(__('WP-Cupón-WhatsApp requiere Elementor para funcionar.', 'wp-cupon-whatsapp'));
       }
   }
   ```

## Solución de Problemas Comunes

### 1. Conflictos de Plugins
- Verificar compatibilidad de versiones
- Deshabilitar plugins conflictivos
- Revisar logs de errores

### 2. Problemas de Base de Datos
- Verificar privilegios de MySQL
- Comprobar tablas creadas
- Validar índices y relaciones

### 3. Errores de Elementor
- Actualizar caché de widgets
- Recompilar assets
- Verificar conflictos de templates

## Hooks para Compatibilidad

```php
// Filtro para compatibilidad con otros plugins de cupones
add_filter('wpcw_coupon_compatibility', function($coupon_data, $coupon) {
    // Personalización para otros plugins
    return $coupon_data;
}, 10, 2);

// Acción para integraciones externas
add_action('wpcw_after_redemption', function($redemption_id) {
    // Integración con otros plugins
}, 10, 1);
```

## Actualizaciones

### Procedimiento de Actualización
1. Hacer backup de la base de datos
2. Verificar versiones mínimas de dependencias
3. Actualizar plugins requeridos
4. Actualizar WP-Cupón-WhatsApp
5. Verificar funcionalidad

### Verificación Post-Actualización
```php
function wpcw_verify_update() {
    // Verificar versiones
    if (version_compare(WC_VERSION, WPCW_MIN_WOOCOMMERCE_VERSION, '<')) {
        // Mostrar advertencia
    }
    
    if (version_compare(ELEMENTOR_VERSION, WPCW_MIN_ELEMENTOR_VERSION, '<')) {
        // Mostrar advertencia
    }
    
    // Verificar tablas y datos
    wpcw_verify_database_structure();
}
```
