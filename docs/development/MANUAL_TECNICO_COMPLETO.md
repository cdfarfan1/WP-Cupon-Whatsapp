# MANUAL TÉCNICO COMPLETO - WP CUPÓN WHATSAPP

## 📋 INFORMACIÓN GENERAL

**Plugin:** WP Cupón WhatsApp  
**Versión:** 1.5.0  
**Desarrollador:** Cristian Farfan, Pragmatic Solutions  
**Licencia:** GPL-2.0+  
**Sitio Web:** https://www.pragmaticsolutions.com.ar  

## 🎯 PROPÓSITO DEL PLUGIN

WP Cupón WhatsApp es un plugin de WordPress que implementa un sistema de programa de fidelización donde los clientes pueden obtener cupones de descuento y canjearlos a través de WhatsApp. Está integrado con WooCommerce para gestionar los cupones y el sistema de e-commerce.

## 🏗️ ARQUITECTURA DEL PLUGIN

### Estructura de Archivos Principal

```
WP-Cupon-Whatsapp/
├── wp-cupon-whatsapp.php          # Archivo principal del plugin
├── readme.txt                     # Información del plugin para WordPress.org
├── admin/                         # Archivos de administración
│   ├── admin-menu.php            # Menús del panel de administración
│   └── coupon-meta-boxes.php     # Meta boxes para cupones
├── includes/                      # Archivos de funcionalidad core
│   ├── post-types.php            # Tipos de post personalizados
│   ├── roles.php                 # Roles y capacidades
│   ├── class-wpcw-logger.php     # Sistema de logging
│   ├── taxonomies.php            # Taxonomías personalizadas
│   ├── class-wpcw-installer-fixed.php  # Instalador del plugin
│   ├── class-wpcw-coupon.php     # Clase para manejo de cupones
│   └── rest-api.php              # API REST
└── public/                       # Archivos públicos
    └── shortcodes.php            # Shortcodes del plugin
```

### Archivos de Documentación

```
├── README.md                     # Documentación principal
├── MANUAL_USUARIO.md            # Manual para usuarios finales
├── GUIA_INSTALACION.md          # Guía de instalación
├── CHANGELOG.md                 # Historial de cambios
└── MANUAL_TECNICO_COMPLETO.md   # Este manual técnico
```

## 🔧 FUNCIONALIDADES PRINCIPALES

### 1. Sistema de Cupones de Fidelización
- Generación automática de códigos de cupón
- Integración con WooCommerce para aplicar descuentos
- Gestión de cupones desde el panel de administración
- Validación de cupones antes del canje

### 2. Integración con WhatsApp
- Envío de cupones vía WhatsApp usando `wa.me`
- Mensajes personalizables para los cupones
- Integración con WhatsApp Business API (opcional)
- Generación de enlaces directos a WhatsApp

### 3. Panel de Administración
- **Dashboard:** Información general del plugin y estado del sistema
- **Configuración:** Ajustes de WhatsApp, mensajes y opciones generales
- **Canjes:** Historial de cupones canjeados por los usuarios
- **Estadísticas:** Métricas de uso del plugin

### 4. Compatibilidad con WooCommerce
- Declaración de compatibilidad con High-Performance Order Storage
- Integración con el sistema de cupones de WooCommerce
- Soporte para diferentes tipos de descuentos

## 📊 BASE DE DATOS

### Tabla Principal: `wp_wpcw_canjes`

```sql
CREATE TABLE wp_wpcw_canjes (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    coupon_code varchar(100) NOT NULL,
    business_id bigint(20) NOT NULL,
    redeemed_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

**Campos:**
- `id`: Identificador único del canje
- `user_id`: ID del usuario que canjeó el cupón
- `coupon_code`: Código del cupón canjeado
- `business_id`: ID del negocio (para multi-tenant)
- `redeemed_at`: Fecha y hora del canje

## 🔌 HOOKS Y FILTROS

### Hooks de Acción

```php
// Inicialización del plugin
add_action( 'init', 'wpcw_init' );

// Registro del menú de administración
add_action( 'admin_menu', 'wpcw_register_menu' );

// Limpieza de logs antiguos
add_action( 'wpcw_clean_old_logs', array( 'WPCW_Logger', 'limpiar_logs_antiguos' ) );

// Compatibilidad con WooCommerce
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});
```

### Hooks de Activación/Desactivación

```php
// Activación del plugin
register_activation_hook( __FILE__, 'wpcw_activate' );

// Desactivación del plugin
register_deactivation_hook( __FILE__, 'wpcw_deactivate' );
```

## 🎨 INTERFAZ DE USUARIO

### Menú de Administración

El plugin añade un menú principal "WP Cupón WhatsApp" con las siguientes subpáginas:

1. **Dashboard** (`wpcw-dashboard`)
   - Estado del plugin
   - Información del sistema
   - Verificación de dependencias

2. **Configuración** (`wpcw-settings`)
   - Token de WhatsApp Business API
   - Número de WhatsApp
   - Mensaje personalizado para cupones

3. **Canjes** (`wpcw-canjes`)
   - Historial de cupones canjeados
   - Información de usuarios
   - Estados de canje

4. **Estadísticas** (`wpcw-estadisticas`)
   - Métricas de uso
   - Gráficos de rendimiento
   - Análisis de datos

### Shortcodes Disponibles

```php
// Shortcode para mostrar formulario de cupones
[wpcw_coupon_form]

// Shortcode para mostrar cupones del usuario
[wpcw_user_coupons]

// Shortcode para mostrar estadísticas públicas
[wpcw_public_stats]
```

## ⚙️ CONFIGURACIÓN

### Variables de Configuración

```php
// Constantes del plugin
define( 'WPCW_VERSION', '1.5.0' );
define( 'WPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp' );
define( 'WPCW_PLUGIN_FILE', __FILE__ );
```

### Opciones de Base de Datos

```php
// Versión del plugin
update_option( 'wpcw_version', WPCW_VERSION );

// Configuración de WhatsApp
get_option( 'wpcw_whatsapp_api' );
get_option( 'wpcw_whatsapp_number' );
get_option( 'wpcw_coupon_message' );
```

## 🚀 INSTALACIÓN Y CONFIGURACIÓN

### Requisitos del Sistema

- **WordPress:** 5.0 o superior
- **PHP:** 7.4 o superior
- **WooCommerce:** 6.0 o superior
- **MySQL:** 5.6 o superior

### Proceso de Instalación

1. **Subir archivos:**
   ```bash
   # Copiar a la carpeta de plugins
   cp -r WP-Cupon-Whatsapp/ /wp-content/plugins/wp-cupon-whatsapp/
   ```

2. **Activar plugin:**
   - Ir a Plugins > Plugins Instalados
   - Buscar "WP Cupón WhatsApp"
   - Hacer clic en "Activar"

3. **Configurar opciones:**
   - Ir a WP Cupón WhatsApp > Configuración
   - Configurar número de WhatsApp
   - Personalizar mensajes

### Verificación de Instalación

El plugin incluye verificaciones automáticas:

```php
// Verificar WooCommerce
if ( ! class_exists( 'WooCommerce' ) ) {
    add_action( 'admin_notices', 'wpcw_woocommerce_missing_notice' );
    return;
}

// Verificar WordPress
if ( ! defined( 'ABSPATH' ) ) {
    die;
}
```

## 🔍 SISTEMA DE LOGGING

### Clase WPCW_Logger

```php
class WPCW_Logger {
    // Crear tabla de logs
    public static function crear_tabla_log();
    
    // Registrar log
    public static function log( $level, $message );
    
    // Programar limpieza de logs
    public static function schedule_log_cleanup();
    
    // Limpiar logs antiguos
    public static function limpiar_logs_antiguos();
}
```

### Niveles de Log

- `info`: Información general
- `warning`: Advertencias
- `error`: Errores
- `debug`: Información de depuración

## 🛠️ DESARROLLO Y MANTENIMIENTO

### Estructura de Código

El plugin sigue las mejores prácticas de WordPress:

1. **Prefijo único:** Todas las funciones usan el prefijo `wpcw_`
2. **Sanitización:** Todas las entradas se sanitizan
3. **Escape:** Todas las salidas se escapan
4. **Nonces:** Protección CSRF en formularios
5. **Capabilities:** Verificación de permisos

### Estándares de Código

```php
// Función bien formada
function wpcw_render_dashboard() {
    // Verificar permisos
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'No tienes permisos para acceder a esta página.' );
    }
    
    // Sanitizar datos
    $version = sanitize_text_field( WPCW_VERSION );
    
    // Escapar salida
    echo '<h1>' . esc_html( 'WP Cupón WhatsApp' ) . '</h1>';
}
```

### Debugging

Para habilitar el debugging:

```php
// En wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

## 🐛 PROBLEMAS CONOCIDOS Y SOLUCIONES

### 1. Error "Headers already sent"

**Problema:** Error durante la activación del plugin.

**Solución:**
```php
// Agregar al inicio del archivo principal
ob_start();
```

### 2. Plugin no se activa

**Problema:** Error fatal durante la activación.

**Solución:**
- Verificar que WooCommerce esté activo
- Verificar versión de PHP (7.4+)
- Revisar logs de error de WordPress

### 3. Menú de administración no aparece

**Problema:** El menú no se muestra en el admin.

**Solución:**
```php
// Verificar permisos del usuario
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}
```

### 4. Incompatibilidad con WooCommerce HPOS

**Problema:** Error con High-Performance Order Storage.

**Solución:**
```php
// Declarar compatibilidad temprano
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});
```

## 📈 OPTIMIZACIÓN Y RENDIMIENTO

### Optimizaciones Implementadas

1. **Carga condicional:** Los archivos se cargan solo cuando es necesario
2. **Queries optimizadas:** Uso de índices en la base de datos
3. **Caché:** Implementación de sistema de caché para consultas frecuentes
4. **Limpieza automática:** Limpieza programada de logs antiguos

### Monitoreo de Rendimiento

```php
// Medir tiempo de ejecución
$start_time = microtime( true );
// ... código ...
$end_time = microtime( true );
$execution_time = $end_time - $start_time;

WPCW_Logger::log( 'debug', "Tiempo de ejecución: {$execution_time} segundos" );
```

## 🔒 SEGURIDAD

### Medidas de Seguridad Implementadas

1. **Verificación de nonces:** Protección CSRF
2. **Sanitización:** Limpieza de datos de entrada
3. **Escape:** Protección XSS en salidas
4. **Capabilities:** Control de acceso por roles
5. **Validación:** Verificación de datos antes de procesar

### Ejemplo de Formulario Seguro

```php
function wpcw_handle_form_submission() {
    // Verificar nonce
    if ( ! wp_verify_nonce( $_POST['wpcw_nonce'], 'wpcw_form_action' ) ) {
        wp_die( 'Acción no autorizada.' );
    }
    
    // Sanitizar datos
    $whatsapp_number = sanitize_text_field( $_POST['whatsapp_number'] );
    $coupon_message = sanitize_textarea_field( $_POST['coupon_message'] );
    
    // Validar datos
    if ( empty( $whatsapp_number ) ) {
        wp_die( 'El número de WhatsApp es requerido.' );
    }
    
    // Procesar datos
    // ...
}
```

## 🌐 INTERNACIONALIZACIÓN

### Preparación para Traducción

```php
// Cargar dominio de texto
load_plugin_textdomain( 'wp-cupon-whatsapp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

// Usar funciones de traducción
echo __( 'WP Cupón WhatsApp', 'wp-cupon-whatsapp' );
echo _e( 'Configuración', 'wp-cupon-whatsapp' );
```

### Archivos de Idioma

- `languages/wp-cupon-whatsapp.pot` - Archivo de plantilla
- `languages/wp-cupon-whatsapp-es_ES.po` - Traducción al español
- `languages/wp-cupon-whatsapp-es_ES.mo` - Archivo compilado

## 📋 CHECKLIST DE DESARROLLO

### Antes de Lanzar una Nueva Versión

- [ ] Verificar compatibilidad con WordPress
- [ ] Verificar compatibilidad con WooCommerce
- [ ] Ejecutar pruebas de activación/desactivación
- [ ] Verificar que no hay errores de sintaxis PHP
- [ ] Actualizar documentación
- [ ] Incrementar número de versión
- [ ] Crear changelog
- [ ] Probar en entorno de staging

### Mantenimiento Regular

- [ ] Revisar logs de error
- [ ] Actualizar dependencias
- [ ] Verificar compatibilidad con nuevas versiones
- [ ] Limpiar código obsoleto
- [ ] Optimizar consultas de base de datos

## 🔄 CONTROL DE VERSIONES Y GITHUB

### Repositorio GitHub

**URL del Repositorio:** https://github.com/cristianfarfan/wp-cupon-whatsapp  
**Rama Principal:** `main`  
**Última Versión:** 1.5.0  

### Estructura de Ramas

```
main                    # Rama principal estable
├── develop            # Rama de desarrollo
├── feature/*          # Ramas de características
├── hotfix/*           # Ramas de correcciones urgentes
└── release/*          # Ramas de preparación de releases
```

### Comandos de Control de Versiones

```bash
# Clonar el repositorio
git clone https://github.com/cristianfarfan/wp-cupon-whatsapp.git

# Crear nueva rama de desarrollo
git checkout -b feature/nueva-funcionalidad

# Agregar cambios
git add .
git commit -m "feat: Agregar nueva funcionalidad de cupones"

# Subir cambios
git push origin feature/nueva-funcionalidad

# Crear Pull Request en GitHub
# Merge a main después de revisión
```

### Tags de Versión

```bash
# Crear tag para nueva versión
git tag -a v1.5.0 -m "Release version 1.5.0"
git push origin v1.5.0

# Listar tags
git tag -l

# Verificar tag
git show v1.5.0
```

### Workflow de Desarrollo

1. **Desarrollo:** Trabajar en rama `develop`
2. **Testing:** Probar en entorno de staging
3. **Release:** Crear rama `release/v1.x.x`
4. **Tag:** Crear tag de versión
5. **Deploy:** Desplegar a producción
6. **Hotfix:** Correcciones urgentes en `hotfix/*`

## 📞 SOPORTE Y CONTACTO

**Desarrollador:** Cristian Farfan  
**Empresa:** Pragmatic Solutions  
**Email:** info@pragmaticsolutions.com.ar  
**Sitio Web:** https://www.pragmaticsolutions.com.ar  
**GitHub:** https://github.com/cristianfarfan  
**LinkedIn:** https://linkedin.com/in/cristianfarfan  

### Canales de Soporte

1. **GitHub Issues:** https://github.com/cristianfarfan/wp-cupon-whatsapp/issues
2. **Email:** info@pragmaticsolutions.com.ar
3. **Documentación:** Manuales y guías en el repositorio
4. **Pull Requests:** Contribuciones bienvenidas

## 📚 RECURSOS ADICIONALES

### Documentación de WordPress
- [Plugin API](https://developer.wordpress.org/plugins/)
- [Hooks y Filtros](https://developer.wordpress.org/plugins/hooks/)
- [Base de Datos](https://developer.wordpress.org/plugins/plugin-basics/database-operations/)

### Documentación de WooCommerce
- [WooCommerce Developer Docs](https://woocommerce.com/developers/)
- [Coupon API](https://woocommerce.github.io/code-reference/classes/WC-Coupon.html)

### Herramientas de Desarrollo
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [WordPress Debug](https://wordpress.org/support/article/debugging-in-wordpress/)

---

**Última actualización:** 15 de Septiembre de 2025  
**Versión del manual:** 1.0  
**Estado:** Completo y actualizado
