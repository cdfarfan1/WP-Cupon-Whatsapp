# 🏗️ Arquitectura y Estructura - WP Cupón WhatsApp

## 📋 Visión General de la Arquitectura

WP Cupón WhatsApp sigue una arquitectura modular y escalable basada en WordPress, con integración profunda con WooCommerce y extensibilidad a través de hooks y APIs REST.

### 🎯 Principios Arquitectónicos
- **Modularidad**: Componentes independientes con responsabilidades claras
- **Extensibilidad**: Sistema de hooks y filtros para personalización
- **Escalabilidad**: Diseño preparado para alto volumen de operaciones
- **Seguridad**: Validación en múltiples capas y protección contra ataques comunes
- **Performance**: Optimización de consultas y estrategias de caching

## 🏛️ Estructura General del Plugin

```
wp-cupon-whatsapp/
├── 🎯 wp-cupon-whatsapp.php          # Archivo principal y bootstrap
├── 📁 includes/                      # Lógica de negocio y clases core
│   ├── Core Classes
│   │   ├── class-wpcw-coupon.php     # Extensión de WC_Coupon
│   │   ├── class-wpcw-dashboard.php  # Dashboard y métricas
│   │   ├── class-wpcw-business-manager.php # Gestión de comercios
│   │   └── class-wpcw-installer-fixed.php # Instalación robusta
│   ├── API & Integrations
│   │   ├── class-wpcw-rest-api.php   # REST API endpoints
│   │   ├── class-wpcw-elementor.php  # Elementor widgets
│   │   └── class-wpcw-shortcodes.php # Shortcodes system
│   └── Utilities
│       ├── class-wpcw-logger.php     # Sistema de logging
│       └── redemption-handler.php    # Lógica de canje
├── 📁 admin/                         # Panel de administración
│   ├── Core Admin
│   │   ├── admin-menu.php            # Menú y páginas admin
│   │   ├── business-management.php   # Gestión de comercios
│   │   └── coupon-meta-boxes.php     # Meta boxes de cupones
│   └── Assets
│       ├── css/                      # Estilos admin
│       └── js/                       # JavaScript admin
├── 📁 public/                        # Frontend público
│   ├── shortcodes.php                # Shortcodes públicos
│   ├── my-account-endpoints.php      # Endpoints de cuenta
│   └── Assets
│       ├── css/                      # Estilos frontend
│       └── js/                       # JavaScript frontend
├── 📁 elementor/                     # Integración Elementor
│   ├── elementor-addon.php           # Inicialización Elementor
│   └── widgets/                      # Widgets personalizados
├── 📁 tests/                         # Suite de testing
│   ├── unit/                         # Tests unitarios
│   ├── integration/                  # Tests de integración
│   └── performance/                  # Tests de rendimiento
├── 📁 docs/                          # Documentación completa
├── 📁 languages/                     # Traducciones i18n
└── 📁 templates/                     # Plantillas personalizables
```

## 🔧 Componentes Principales

### **1. Sistema Core (Núcleo)**
```php
// Bootstrap y inicialización
wp-cupon-whatsapp.php
├── wpcw_init()           # Inicialización principal
├── wpcw_check_dependencies() # Validación de dependencias
├── wpcw_activate()       # Activación del plugin
└── wpcw_deactivate()     # Desactivación del plugin
```

### **2. Gestión de Usuarios y Roles**
```php
includes/roles.php
├── wpcw_add_roles()      # Registro de roles personalizados
├── wpcw_remove_roles()   # Limpieza de roles
└── Roles definidos:
    ├── Administrador del Sistema
    ├── Dueño de Comercio
    ├── Personal de Comercio
    ├── Gestor de Institución
    └── Cliente
```

### **3. Custom Post Types (CPTs)**
```php
includes/post-types.php
├── wpcw_business         # Comercios registrados
├── wpcw_institution      # Instituciones educativas
└── wpcw_application      # Solicitudes de adhesión
```

### **4. Sistema de Cupones Extendido**
```php
class WPCW_Coupon extends WC_Coupon {
    // Propiedades WPCW específicas
    - wpcw_enabled              # Habilitado para WhatsApp
    - associated_business_id    # Comercio asociado
    - is_loyalty_coupon        # Cupón de lealtad
    - is_public_coupon         # Cupón público
    - whatsapp_text            # Mensaje personalizado
    - redemption_hours         # Horario de canje
    - expiry_reminder          # Recordatorio de vencimiento
    - max_uses_per_user        # Límite por usuario

    // Métodos principales
    - can_user_redeem()        # Validación de elegibilidad
    - get_whatsapp_redemption_url() # Generación de URL WhatsApp
    - get_usage_by_user_id()   # Uso por usuario específico
}
```

### **5. Integración WhatsApp**
```php
redemption-handler.php
├── WPCW_Redemption_Handler
│   ├── initiate_redemption()          # Iniciar proceso de canje
│   ├── process_redemption_request()   # Procesar solicitud
│   ├── can_redeem()                   # Validar elegibilidad
│   ├── confirm_redemption()           # Confirmar canje
│   ├── notify_business_redemption_request() # Notificar comercio
│   ├── generate_redemption_number()   # Generar número único
│   └── generate_whatsapp_message()    # Crear mensaje WhatsApp
```

### **6. Panel de Administración**
```php
class WPCW_Dashboard {
    // Dashboard con métricas en tiempo real
    - get_metrics()              # Métricas del sistema
    - get_chart_data()           # Datos para gráficos
    - get_recent_notifications() # Notificaciones recientes
    - get_system_health()        # Estado del sistema

    // Secciones del dashboard
    - render_applications_section()
    - render_businesses_section()
    - render_coupons_section()
    - render_redemptions_section()
    - render_users_section()
    - render_institutions_section()
}
```

## 🗄️ Arquitectura de Base de Datos

### **Tablas Personalizadas**
```sql
wp_wpcw_canjes (Tabla principal de canjes)
├── id (PRIMARY KEY)
├── user_id (Usuario que canjea)
├── coupon_id (Cupón de WooCommerce)
├── numero_canje (Número único)
├── token_confirmacion (Token de seguridad)
├── estado_canje (Estado del proceso)
├── fecha_solicitud_canje
├── fecha_confirmacion_canje
├── comercio_id (Comercio asociado)
├── whatsapp_url (URL generada)
├── codigo_cupon_wc (Código generado)
├── id_pedido_wc (ID del pedido)
└── Índices optimizados para consultas frecuentes
```

### **Meta Keys de WooCommerce**
```php
// Cupones (wp_postmeta con post_type = shop_coupon)
_wpcw_enabled => 'yes'                    # Habilitado para WPCW
_wpcw_associated_business_id => '123'     # Comercio asociado
_wpcw_is_loyalty_coupon => 'yes'          # Cupón de lealtad
_wpcw_is_public_coupon => 'yes'           # Cupón público
_wpcw_whatsapp_text => 'Mensaje...'       # Texto personalizado
_wpcw_redemption_hours => 'Lun-Vie 9-18' # Horario de canje
_wpcw_expiry_reminder => '7'              # Días antes del recordatorio
_wpcw_max_uses_per_user => '5'            # Límite por usuario
```

### **Meta Keys de Usuarios**
```php
// Información del usuario (wp_usermeta)
_wpcw_dni_number => '12345678'
_wpcw_birth_date => '1990-01-01'
_wpcw_whatsapp_number => '+5491123456789'
_wpcw_user_institution_id => '123'
_wpcw_user_favorite_coupon_categories => array(1,2,3)
_wpcw_total_redemptions => '15'
_wpcw_successful_redemptions => '14'
```

## 🔗 APIs REST y Endpoints

### **Base URL**
```
https://sitio.com/wp-json/wpcw/v1/
```

### **Endpoints Principales**
```php
GET    /coupons              # Lista de cupones disponibles
POST   /coupons/{id}/redeem  # Iniciar canje de cupón
GET    /businesses           # Lista de comercios
GET    /businesses/{id}      # Detalles de comercio
GET    /stats                # Estadísticas del sistema
GET    /stats/business/{id}  # Estadísticas de comercio
GET    /confirm-redemption   # Confirmar canje vía token
GET    /user/profile         # Perfil del usuario actual
POST   /user/profile         # Actualizar perfil
POST   /applications         # Enviar solicitud de adhesión
```

### **Autenticación**
- **WordPress Nonces**: Para requests desde frontend
- **Bearer Tokens**: Para integraciones externas
- **User Authentication**: Basado en sesiones de WordPress

## 🎨 Integraciones Externas

### **WooCommerce Integration**
```php
// Hooks utilizados
woocommerce_coupon_loaded          # Extensión de cupones
woocommerce_process_shop_order     # Procesamiento de pedidos
woocommerce_order_status_changed   # Cambios de estado
woocommerce_coupon_get_discount_amount # Cálculo de descuentos
```

### **Elementor Integration**
```php
// Widgets disponibles
WPCW_Coupons_Lista     # Lista de cupones con filtros
WPCW_Formulario_Adhesion # Formulario de registro de comercios
WPCW_Dashboard_Usuario  # Panel de usuario personalizado
```

### **WordPress Core Integration**
```php
// Hooks y filtros
init                    # Registro de CPTs y taxonomías
admin_menu             # Menú de administración
wp_enqueue_scripts     # Assets del frontend
admin_enqueue_scripts  # Assets del admin
wp_ajax_*             # Handlers AJAX
rest_api_init         # Registro de REST routes
```

## 🔒 Arquitectura de Seguridad

### **Capas de Validación**
1. **Input Sanitization**: Todos los inputs sanitizados
2. **Data Validation**: Validación de tipos y formatos
3. **Permission Checks**: Verificación de capacidades de usuario
4. **CSRF Protection**: Nonces en formularios
5. **SQL Injection Prevention**: Prepared statements
6. **XSS Protection**: Output escaping

### **Rate Limiting**
```php
// Límites por endpoint
GET  /coupons           => 100 requests/minute
POST /coupons/{id}/redeem => 10 requests/minute per user
POST /applications      => 5 requests/hour per IP
```

## 📊 Sistema de Logging y Monitoreo

### **WPCW_Logger Class**
```php
class WPCW_Logger {
    // Niveles de logging
    - emergency  # Sistema inoperable
    - alert      # Acción inmediata requerida
    - critical   # Condiciones críticas
    - error      # Errores de ejecución
    - warning    # Advertencias
    - notice     # Eventos normales pero importantes
    - info       # Información general
    - debug      # Información de debugging

    // Métodos principales
    - log()              # Registrar evento
    - crear_tabla_log()  # Crear tabla de logs
    - limpiar_logs_antiguos() # Mantenimiento
}
```

### **Monitoreo de Sistema**
- **Health Checks**: Verificación automática de componentes
- **Performance Monitoring**: Métricas de respuesta y uso de recursos
- **Error Tracking**: Captura y reporte de excepciones
- **Audit Trail**: Registro completo de acciones de usuario

## 🚀 Escalabilidad y Performance

### **Optimizaciones Implementadas**
- **Database Indexing**: Índices en campos de consulta frecuente
- **Query Optimization**: Consultas eficientes con JOINs optimizados
- **Caching Strategy**: Object caching para datos recurrentes
- **Lazy Loading**: Carga diferida de componentes no críticos
- **Asset Optimization**: Minificación y concatenación de assets

### **Arquitectura Escalable**
- **Modular Design**: Componentes independientes
- **Event-Driven**: Sistema de hooks para extensibilidad
- **API-First**: Diseño preparado para integraciones
- **Microservices Ready**: Arquitectura preparada para distribución

## 🔧 Patrones de Diseño Utilizados

### **MVC Pattern**
- **Model**: Clases de datos (WPCW_Coupon, CPTs)
- **View**: Templates y shortcodes
- **Controller**: Handlers y API endpoints

### **Factory Pattern**
```php
function wpcw_get_coupon( $coupon = 0 ) {
    return new WPCW_Coupon( $coupon );
}
```

### **Observer Pattern**
```php
// Sistema de hooks de WordPress
do_action( 'wpcw_before_coupon_redemption', $coupon_id, $user_id );
do_action( 'wpcw_after_coupon_redemption', $redemption_id );
```

### **Strategy Pattern**
```php
// Diferentes estrategias de validación
interface RedemptionValidator {
    public function validate( $coupon, $user );
}

class LoyaltyCouponValidator implements RedemptionValidator { ... }
class PublicCouponValidator implements RedemptionValidator { ... }
```

## 📚 Dependencias y Requisitos

### **Dependencias Obligatorias**
- **WordPress**: 5.0+
- **WooCommerce**: 6.0+
- **PHP**: 7.4+
- **MySQL**: 5.6+

### **Dependencias Opcionales**
- **Elementor**: 3.0+ (para widgets visuales)
- **Redis**: Para caching avanzado
- **MongoDB**: Para analytics avanzados

### **Entorno de Desarrollo Recomendado**
- **Servidor Web**: Apache/Nginx
- **PHP**: 8.0+ para desarrollo
- **Base de Datos**: MySQL 8.0+
- **Node.js**: Para build de assets (opcional)

---

**📅 Última Actualización**: Octubre 2025
**🏗️ Arquitectura**: Modular y Escalable
**🔧 Patrón**: MVC con Hooks y Filtros