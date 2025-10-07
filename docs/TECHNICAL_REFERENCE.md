# 🔧 Referencias Técnicas - WP Cupón WhatsApp

## 📊 Base de Datos

### **Tabla Principal: wp_wpcw_canjes**

```sql
CREATE TABLE wp_wpcw_canjes (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) UNSIGNED NOT NULL COMMENT 'ID del usuario que realiza el canje',
    coupon_id bigint(20) UNSIGNED NOT NULL COMMENT 'ID del cupón de WooCommerce',
    numero_canje varchar(20) NOT NULL COMMENT 'Número único de canje (ej: 20250916-1234)',
    token_confirmacion varchar(64) NOT NULL COMMENT 'Token único para confirmar el canje',
    estado_canje varchar(50) NOT NULL DEFAULT 'pendiente_confirmacion' COMMENT 'Estado del canje',
    fecha_solicitud_canje datetime NOT NULL COMMENT 'Fecha y hora de la solicitud',
    fecha_confirmacion_canje datetime DEFAULT NULL COMMENT 'Fecha de confirmación por el comercio',
    comercio_id bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID del comercio asociado',
    whatsapp_url text DEFAULT NULL COMMENT 'URL completa de WhatsApp generada',
    codigo_cupon_wc varchar(100) DEFAULT NULL COMMENT 'Código del cupón WC generado',
    id_pedido_wc bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ID del pedido WC si se genera',
    origen_canje varchar(50) DEFAULT 'webapp' COMMENT 'Origen del canje (webapp, api, etc.)',
    notas_internas text DEFAULT NULL COMMENT 'Notas internas del administrador',
    fecha_rechazo datetime DEFAULT NULL COMMENT 'Fecha de rechazo del canje',
    fecha_cancelacion datetime DEFAULT NULL COMMENT 'Fecha de cancelación del canje',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_coupon_id (coupon_id),
    KEY idx_numero_canje (numero_canje),
    KEY idx_estado_canje (estado_canje),
    KEY idx_fecha_solicitud (fecha_solicitud_canje),
    KEY idx_comercio_id (comercio_id),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Estados de Canje**
- `pendiente_confirmacion` - Esperando confirmación del comercio
- `confirmado_por_negocio` - Confirmado por el comercio vía WhatsApp
- `rechazado` - Rechazado por el comercio
- `expirado` - Expirado sin acción del comercio
- `cancelado` - Cancelado por el usuario
- `utilizado_en_pedido_wc` - Utilizado en un pedido de WooCommerce

### **Meta Keys de WooCommerce Coupons**

```php
// Campos booleanos
'_wpcw_enabled' => 'yes'                    // Habilitado para WhatsApp
'_wpcw_is_loyalty_coupon' => 'yes'          // Es cupón de lealtad
'_wpcw_is_public_coupon' => 'yes'           // Es cupón público
'_wpcw_expiry_reminder' => 'yes'            // Enviar recordatorio de vencimiento
'_wpcw_auto_confirm' => 'yes'               // Confirmación automática

// Campos de texto
'_wpcw_associated_business_id' => '123'     // ID del comercio asociado
'_wpcw_whatsapp_text' => 'Mensaje...'       // Mensaje WhatsApp personalizado
'_wpcw_redemption_hours' => 'Lun-Vie 9-18'  // Horario de canje
'_wpcw_max_uses_per_user' => '5'            // Límite por usuario
'_wpcw_coupon_category_id' => '456'         // ID de categoría
'_wpcw_coupon_image_id' => '789'            // ID de imagen
```

### **Meta Keys de Usuarios**

```php
// Información personal
'_wpcw_dni_number' => '12345678'
'_wpcw_birth_date' => '1990-01-01'
'_wpcw_whatsapp_number' => '+5491123456789'

// Institucional
'_wpcw_user_institution_id' => '123'        // ID de institución
'_wpcw_user_favorite_coupon_categories' => array(1, 2, 3)

// Estadísticas
'_wpcw_total_redemptions' => '15'
'_wpcw_successful_redemptions' => '14'
'_wpcw_last_redemption_date' => '2025-09-16 10:30:00'
```

### **Meta Keys de Comercios (wpcw_business)**

```php
'_wpcw_owner_user_id' => '123'               // Usuario dueño
'_wpcw_legal_name' => 'Empresa S.A.'
'_wpcw_cuit' => '30123456789'
'_wpcw_contact_person' => 'Juan Pérez'
'_wpcw_email' => 'contacto@empresa.com'
'_wpcw_whatsapp' => '+5491123456789'
'_wpcw_address_main' => 'Calle Principal 123'
'_wpcw_logo_image_id' => '456'
'_wpcw_business_status' => 'active'          // active, inactive, suspended
'_wpcw_registration_date' => '2025-01-15'
```

### **Opciones de WordPress (wp_options)**

```php
// Configuración principal
'wpcw_version' => '1.5.0'
'wpcw_mongodb_enabled' => '0'
'wpcw_email_verification_enabled' => '1'
'wpcw_default_coupon_validity_days' => '30'
'wpcw_max_coupons_per_user' => '5'
'wpcw_allow_public_coupons' => '1'
'wpcw_setup_wizard_completed' => '0'

// WhatsApp
'wpcw_whatsapp_api_token' => 'token_aqui'
'wpcw_whatsapp_business_number' => '+5491123456789'
'wpcw_default_whatsapp_message' => 'Mensaje por defecto...'

// Seguridad
'wpcw_enable_rate_limiting' => '1'
'wpcw_max_requests_per_minute' => '100'
'wpcw_enable_ip_blocking' => '1'

// Email
'wpcw_smtp_host' => 'smtp.gmail.com'
'wpcw_smtp_port' => '587'
'wpcw_smtp_username' => 'user@gmail.com'
'wpcw_smtp_password' => 'password'
```

---

## 🔗 REST API Endpoints

### **Base URL**
```
https://sitio.com/wp-json/wpcw/v1/
```

### **Autenticación**
```http
X-WP-Nonce: [nonce_value]
Authorization: Bearer [token] (opcional)
Content-Type: application/json
```

### **Endpoints de Cupones**

#### `GET /coupons`
Obtiene lista de cupones disponibles para el usuario.

**Parámetros:**
- `user_id` (int, opcional): ID del usuario
- `type` (string, opcional): "loyalty" | "public" | "all"
- `category` (int, opcional): ID de categoría
- `business_id` (int, opcional): ID del comercio
- `page` (int, opcional): Página (default: 1)
- `per_page` (int, opcional): Items por página (default: 20)
- `search` (string, opcional): Búsqueda por título/descripción

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "coupons": [
      {
        "id": 123,
        "code": "DESCUENTO20",
        "title": "20% de descuento",
        "description": "Descuento especial en productos seleccionados",
        "discount_type": "percent",
        "amount": "20",
        "expiry_date": "2025-12-31",
        "business_name": "Tienda Ejemplo",
        "image_url": "https://example.com/wp-content/uploads/coupon-image.jpg",
        "can_redeem": true,
        "redemption_url": "https://wa.me/5491123456789?text=...",
        "categories": ["Electrónicos", "Tecnología"]
      }
    ],
    "pagination": {
      "total": 45,
      "total_pages": 3,
      "current_page": 1,
      "per_page": 20
    }
  }
}
```

#### `POST /coupons/{id}/redeem`
Inicia el proceso de canje de un cupón.

**Parámetros URL:**
- `id` (int, requerido): ID del cupón

**Body:**
```json
{
  "business_id": 456,
  "notes": "Comentarios adicionales"
}
```

**Respuesta (200):**
```json
{
  "success": true,
  "data": {
    "redemption_id": 789,
    "redemption_number": "20250916-1234",
    "whatsapp_url": "https://wa.me/5491123456789?text=Hola%20quiero%20canjear...",
    "token": "abc123def456",
    "expires_in": 3600,
    "instructions": "Envía este mensaje por WhatsApp para confirmar el canje"
  }
}
```

### **Endpoints de Comercios**

#### `GET /businesses`
Obtiene lista de comercios registrados.

**Parámetros:**
- `status` (string, opcional): "active" | "inactive" | "all"
- `category` (string, opcional): Categoría del comercio
- `location` (string, opcional): Ubicación
- `search` (string, opcional): Búsqueda
- `page` (int, opcional): Página
- `per_page` (int, opcional): Items por página

#### `GET /businesses/{id}`
Obtiene detalles de un comercio específico.

### **Endpoints de Estadísticas**

#### `GET /stats`
Obtiene estadísticas del sistema.

**Parámetros:**
- `period` (string, opcional): "day" | "week" | "month" | "year"
- `business_id` (int, opcional): Filtrar por comercio
- `institution_id` (int, opcional): Filtrar por institución
- `start_date` (string, opcional): Fecha inicio (YYYY-MM-DD)
- `end_date` (string, opcional): Fecha fin (YYYY-MM-DD)

#### `GET /stats/business/{id}`
Estadísticas específicas de un comercio.

### **Endpoints de Confirmación**

#### `GET /confirm-redemption`
Confirma o rechaza un canje vía token.

**Parámetros:**
- `token` (string, requerido): Token de confirmación
- `action` (string, requerido): "confirm" | "reject"

### **Endpoints de Usuarios**

#### `GET /user/profile`
Obtiene perfil del usuario actual.

#### `POST /user/profile`
Actualiza perfil del usuario.

**Body:**
```json
{
  "whatsapp": "+5491123456789",
  "favorite_categories": [1, 2, 3]
}
```

### **Endpoints de Aplicaciones**

#### `POST /applications`
Envía una solicitud de adhesión.

**Body:**
```json
{
  "applicant_type": "comercio",
  "fantasy_name": "Mi Comercio",
  "legal_name": "Mi Comercio S.A.",
  "cuit": "30123456789",
  "contact_person": "Juan Pérez",
  "email": "contacto@micomercio.com",
  "whatsapp": "+5491123456789",
  "address_main": "Calle Principal 123",
  "description": "Descripción del comercio",
  "recaptcha_token": "recaptcha_response"
}
```

---

## 🎣 Hooks y Filtros de WordPress

### **Actions (Acciones)**

#### **Sistema de Cupones**
```php
do_action( 'wpcw_before_coupon_redemption', $coupon_id, $user_id, $business_id );
do_action( 'wpcw_after_coupon_redemption', $redemption_id, $coupon_id, $user_id );
do_action( 'wpcw_coupon_redemption_confirmed', $redemption_id, $coupon_data );
do_action( 'wpcw_coupon_redemption_rejected', $redemption_id, $reason );
do_action( 'wpcw_coupon_created', $coupon_id, $coupon_data );
do_action( 'wpcw_coupon_updated', $coupon_id, $old_data, $new_data );
```

#### **Sistema de Comercios**
```php
do_action( 'wpcw_business_registered', $business_id, $user_id );
do_action( 'wpcw_business_approved', $business_id, $application_id );
do_action( 'wpcw_business_rejected', $business_id, $reason );
do_action( 'wpcw_business_status_changed', $business_id, $old_status, $new_status );
do_action( 'wpcw_business_user_assigned', $business_id, $user_id, $role );
do_action( 'wpcw_business_user_removed', $business_id, $user_id );
```

#### **Sistema de Usuarios**
```php
do_action( 'wpcw_user_profile_updated', $user_id, $old_data, $new_data );
do_action( 'wpcw_user_institution_assigned', $user_id, $institution_id );
do_action( 'wpcw_user_whatsapp_verified', $user_id, $whatsapp_number );
do_action( 'wpcw_user_loyalty_status_changed', $user_id, $status );
```

#### **Sistema de Aplicaciones**
```php
do_action( 'wpcw_application_submitted', $application_id, $application_data );
do_action( 'wpcw_application_status_changed', $application_id, $old_status, $new_status );
do_action( 'wpcw_application_approved', $application_id, $business_id );
do_action( 'wpcw_application_rejected', $application_id, $reason );
```

### **Filters (Filtros)**

#### **Sistema de Cupones**
```php
apply_filters( 'wpcw_coupon_redemption_message', $message, $coupon, $user, $business );
apply_filters( 'wpcw_coupon_redemption_allowed', true, $coupon, $user, $business );
apply_filters( 'wpcw_coupon_expiry_reminder_days', 7, $coupon );
apply_filters( 'wpcw_coupon_max_uses_per_user', $max_uses, $coupon, $user );
apply_filters( 'wpcw_coupon_whatsapp_url', $url, $coupon, $user, $business );
```

#### **Sistema de Comercios**
```php
apply_filters( 'wpcw_business_registration_fields', $fields );
apply_filters( 'wpcw_business_approval_required', true, $application_data );
apply_filters( 'wpcw_business_status_options', $statuses );
apply_filters( 'wpcw_business_user_roles', $roles, $business );
```

#### **Sistema de Estadísticas**
```php
apply_filters( 'wpcw_dashboard_stats', $stats, $period );
apply_filters( 'wpcw_business_stats', $stats, $business_id );
apply_filters( 'wpcw_user_stats', $stats, $user_id );
apply_filters( 'wpcw_chart_data', $data, $chart_type, $period );
```

#### **Sistema de APIs**
```php
apply_filters( 'wpcw_api_response', $response, $request );
apply_filters( 'wpcw_api_rate_limit', $limit, $endpoint, $user_id );
apply_filters( 'wpcw_api_authentication_required', true, $endpoint );
```

#### **Sistema de Templates**
```php
apply_filters( 'wpcw_coupon_card_template', $template, $coupon );
apply_filters( 'wpcw_redemption_form_template', $template, $coupon );
apply_filters( 'wpcw_business_card_template', $template, $business );
apply_filters( 'wpcw_user_dashboard_template', $template, $user );
```

---

## ⚙️ Configuración del Plugin

### **Opciones Principales**

#### **WhatsApp Configuration**
```php
// Token de API de WhatsApp Business
'wpcw_whatsapp_api_token' => string

// Número de WhatsApp del negocio
'wpcw_whatsapp_business_number' => string

// Mensaje por defecto para WhatsApp
'wpcw_default_whatsapp_message' => string

// Habilitar verificación de números
'wpcw_whatsapp_verification_enabled' => '1' | '0'
```

#### **Sistema de Cupones**
```php
// Días de validez por defecto
'wpcw_default_coupon_validity_days' => int

// Máximo de cupones por usuario
'wpcw_max_coupons_per_user' => int

// Permitir cupones públicos
'wpcw_allow_public_coupons' => '1' | '0'

// Habilitar recordatorios de expiración
'wpcw_expiry_reminders_enabled' => '1' | '0'
```

#### **Sistema de Usuarios**
```php
// Verificación de email obligatoria
'wpcw_email_verification_required' => '1' | '0'

// Campos de perfil requeridos
'wpcw_required_profile_fields' => array

// Habilitar registro de comercios
'wpcw_business_registration_enabled' => '1' | '0'
```

#### **Sistema de Seguridad**
```php
// Habilitar rate limiting
'wpcw_rate_limiting_enabled' => '1' | '0'

// Requests por minuto
'wpcw_max_requests_per_minute' => int

// Habilitar bloqueo de IP
'wpcw_ip_blocking_enabled' => '1' | '0'

// Duración del bloqueo (minutos)
'wpcw_ip_block_duration' => int
```

#### **Sistema de Reportes**
```php
// Período de retención de logs (días)
'wpcw_log_retention_days' => int

// Enviar reportes por email
'wpcw_email_reports_enabled' => '1' | '0'

// Destinatarios de reportes
'wpcw_report_recipients' => array

// Frecuencia de reportes
'wpcw_report_frequency' => 'daily' | 'weekly' | 'monthly'
```

### **Configuración Avanzada**

#### **Base de Datos**
```php
// Habilitar MongoDB (experimental)
'wpcw_mongodb_enabled' => '0'

// Connection string MongoDB
'wpcw_mongodb_connection' => string

// Database name MongoDB
'wpcw_mongodb_database' => string
```

#### **Performance**
```php
// Habilitar caching
'wpcw_caching_enabled' => '1'

// TTL del cache (segundos)
'wpcw_cache_ttl' => int

// Cache driver
'wpcw_cache_driver' => 'file' | 'redis' | 'memcached'
```

#### **Integraciones Externas**
```php
// Webhook URLs
'wpcw_webhook_urls' => array

// Zapier webhook URL
'wpcw_zapier_webhook_url' => string

// API keys externas
'wpcw_external_api_keys' => array
```

---

## 📝 Shortcodes Disponibles

### **Sistema de Adhesión**
```php
[wpcw_solicitud_adhesion_form]
// Formulario completo de registro de comercios
// Parámetros opcionales:
// - redirect: URL de redirección después del envío
// - recaptcha: 'true' | 'false' (default: true)
```

### **Sistema de Cupones**
```php
[wpcw_mis_cupones]
// Lista de cupones de lealtad del usuario actual
// Parámetros:
// - limit: Número máximo de cupones (default: 10)
// - category: ID de categoría para filtrar
// - show_expired: 'true' | 'false' (default: false)

[wpcw_cupones_publicos]
// Lista de cupones públicos disponibles
// Parámetros:
// - limit: Número máximo de cupones (default: 20)
// - category: ID de categoría
// - search: Término de búsqueda
// - orderby: 'date' | 'title' | 'expiry' (default: date)
// - order: 'ASC' | 'DESC' (default: DESC)

[wpcw_canje_cupon]
// Formulario de canje individual
// Parámetros:
// - coupon_id: ID específico del cupón
// - show_details: 'true' | 'false' (default: true)
```

### **Sistema de Usuario**
```php
[wpcw_dashboard_usuario]
// Dashboard completo del usuario
// Parámetros:
// - show_stats: 'true' | 'false' (default: true)
// - show_history: 'true' | 'false' (default: true)
// - limit: Número de items en historial (default: 10)
```

### **Sistema de Comercios**
```php
[wpcw_lista_comercios]
// Lista de comercios registrados
// Parámetros:
// - category: Categoría de comercio
// - location: Ubicación
// - status: 'active' | 'inactive' | 'all' (default: active)
// - limit: Número máximo (default: 12)
// - layout: 'grid' | 'list' (default: grid)
```

---

## 🎨 Elementor Widgets

### **WPCW Formulario Adhesión**
```php
// Controles disponibles:
- Título del formulario
- Descripción
- Campos a mostrar/ocultar
- Estilos personalizados
- Mensajes de validación
- Integración reCAPTCHA
- Redirección después del envío
```

### **WPCW Lista de Cupones**
```php
// Controles disponibles:
- Tipo de cupones: 'loyalty' | 'public' | 'all'
- Número de cupones por página
- Filtros por categoría
- Estilo de visualización: 'grid' | 'list' | 'carousel'
- Columnas responsive
- Colores y tipografía
- Animaciones de carga
```

### **WPCW Dashboard Usuario**
```php
// Controles disponibles:
- Secciones a mostrar: stats, history, profile
- Número de items en historial
- Estilos de tarjetas
- Colores del tema
- Responsive breakpoints
```

---

## 🔒 Constantes del Plugin

```php
// Información básica
define( 'WPCW_VERSION', '1.5.0' );
define( 'WPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp' );
define( 'WPCW_PLUGIN_FILE', __FILE__ );

// Límites y configuraciones
define( 'WPCW_MAX_COUPONS_PER_USER', 5 );
define( 'WPCW_DEFAULT_COUPON_VALIDITY_DAYS', 30 );
define( 'WPCW_REDEMPTION_TOKEN_EXPIRY', 3600 ); // 1 hora
define( 'WPCW_RATE_LIMIT_REQUESTS', 100 ); // por minuto
define( 'WPCW_RATE_LIMIT_WINDOW', 60 ); // segundos

// Estados de canje
define( 'WPCW_REDEMPTION_STATUS_PENDING', 'pendiente_confirmacion' );
define( 'WPCW_REDEMPTION_STATUS_CONFIRMED', 'confirmado_por_negocio' );
define( 'WPCW_REDEMPTION_STATUS_REJECTED', 'rechazado' );
define( 'WPCW_REDEMPTION_STATUS_EXPIRED', 'expirado' );
define( 'WPCW_REDEMPTION_STATUS_CANCELLED', 'cancelado' );
define( 'WPCW_REDEMPTION_STATUS_USED', 'utilizado_en_pedido_wc' );

// Roles de usuario
define( 'WPCW_ROLE_SYSTEM_ADMIN', 'administrator' );
define( 'WPCW_ROLE_BUSINESS_OWNER', 'wpcw_business_owner' );
define( 'WPCW_ROLE_BUSINESS_STAFF', 'wpcw_business_staff' );
define( 'WPCW_ROLE_INSTITUTION_MANAGER', 'wpcw_institution_manager' );
define( 'WPCW_ROLE_CUSTOMER', 'customer' );

// Tipos de post personalizados
define( 'WPCW_POST_TYPE_BUSINESS', 'wpcw_business' );
define( 'WPCW_POST_TYPE_INSTITUTION', 'wpcw_institution' );
define( 'WPCW_POST_TYPE_APPLICATION', 'wpcw_application' );

// Taxonomías
define( 'WPCW_TAXONOMY_BUSINESS_CATEGORY', 'wpcw_business_category' );
define( 'WPCW_TAXONOMY_COUPON_CATEGORY', 'wpcw_coupon_category' );
define( 'WPCW_TAXONOMY_INSTITUTION_TYPE', 'wpcw_institution_type' );

// URLs y paths
define( 'WPCW_API_BASE', 'wpcw/v1' );
define( 'WPCW_TEMPLATE_PATH', WPCW_PLUGIN_DIR . 'templates/' );
define( 'WPCW_ASSETS_URL', WPCW_PLUGIN_URL . 'assets/' );

// Debugging
define( 'WPCW_DEBUG_MODE', defined( 'WP_DEBUG' ) && WP_DEBUG );
define( 'WPCW_LOG_LEVEL', WPCW_DEBUG_MODE ? 'debug' : 'info' );
```

---

## 🧪 Clases Principales

### **WPCW_Coupon**
```php
class WPCW_Coupon extends WC_Coupon {
    // Propiedades
    public $wpcw_enabled;
    public $associated_business_id;
    public $is_loyalty_coupon;
    public $is_public_coupon;
    public $whatsapp_text;
    public $redemption_hours;
    public $expiry_reminder;
    public $max_uses_per_user;

    // Métodos principales
    public function is_wpcw_enabled();
    public function get_associated_business_id();
    public function is_loyalty_coupon();
    public function is_public_coupon();
    public function get_whatsapp_text();
    public function get_whatsapp_redemption_url( $user_id, $redemption_number, $token );
    public function can_user_redeem( $user_id );
    public function get_usage_by_user_id( $user_id );
}
```

### **WPCW_Redemption_Handler**
```php
class WPCW_Redemption_Handler {
    // Métodos principales
    public static function initiate_redemption( $coupon_id, $user_id, $business_id = null );
    public static function process_redemption_request( $data );
    public static function can_redeem( $coupon, $user, $business = null );
    public static function confirm_redemption( $token, $action );
    public static function notify_business_redemption_request( $redemption_id );
    public static function generate_redemption_number();
    public static function generate_whatsapp_message( $coupon_id, $redemption_number, $token );
}
```

### **WPCW_Dashboard**
```php
class WPCW_Dashboard {
    // Métodos principales
    public function get_metrics();
    public function get_chart_data( $period = '30 days' );
    public function get_recent_notifications();
    public function get_system_health();
    public function render_applications_section();
    public function render_businesses_section();
    public function render_coupons_section();
    public function render_redemptions_section();
    public function render_users_section();
    public function render_institutions_section();
}
```

### **WPCW_Business_Manager**
```php
class WPCW_Business_Manager {
    // Métodos principales
    public function register_business( $data );
    public function approve_business( $business_id );
    public function assign_user_to_business( $user_id, $business_id, $role );
    public function remove_user_from_business( $user_id, $business_id );
    public function get_business_stats( $business_id );
    public function update_business_status( $business_id, $status );
}
```

### **WPCW_REST_API**
```php
class WPCW_REST_API {
    // Registro de rutas
    public function register_routes();
    public function get_coupons( $request );
    public function redeem_coupon( $request );
    public function get_businesses( $request );
    public function get_stats( $request );
    public function confirm_redemption( $request );

    // Utilidades
    public function check_authentication( $request );
    public function validate_request_data( $data, $rules );
    public function format_api_response( $data, $success = true, $message = '' );
}
```

---

## 📋 Scripts y Comandos Útiles

### **WP-CLI Commands**
```bash
# Gestión del plugin
wp wpcw activate                    # Activar funcionalidades
wp wpcw deactivate                  # Desactivar funcionalidades
wp wpcw status                      # Estado del plugin

# Base de datos
wp wpcw create-tables               # Crear tablas personalizadas
wp wpcw migrate                     # Ejecutar migraciones
wp wpcw seed-test-data              # Cargar datos de prueba

# Mantenimiento
wp wpcw clean-expired-redemptions   # Limpiar canjes expirados
wp wpcw update-stats                # Actualizar estadísticas
wp wpcw clear-cache                 # Limpiar cache

# Utilidades
wp wpcw export-data --format=csv    # Exportar datos
wp wpcw import-data --file=data.csv # Importar datos
wp wpcw health-check                # Verificación de salud
```

### **Composer Scripts**
```json
{
    "scripts": {
        "test": "phpunit",
        "test-coverage": "phpunit --coverage-html coverage-report",
        "lint": "phpcs --standard=WordPress src/",
        "lint-fix": "phpcbf --standard=WordPress src/",
        "build": "npm run build && composer dump-autoload",
        "deploy": "composer run build && rsync -avz . user@server:/path/to/wp-content/plugins/wp-cupon-whatsapp/"
    }
}
```

### **NPM Scripts**
```json
{
    "scripts": {
        "dev": "webpack --mode=development --watch",
        "build": "webpack --mode=production",
        "lint": "eslint assets/js/",
        "lint-fix": "eslint assets/js/ --fix",
        "test": "karma start karma.conf.js"
    }
}
```

---

## 🚨 Códigos de Error

### **Errores de API**
```php
// Errores HTTP
400 => 'WPCW_BAD_REQUEST'           // Datos inválidos
401 => 'WPCW_UNAUTHORIZED'          // No autenticado
403 => 'WPCW_FORBIDDEN'             // Sin permisos
404 => 'WPCW_NOT_FOUND'             // Recurso no encontrado
409 => 'WPCW_CONFLICT'              // Conflicto de estado
429 => 'WPCW_RATE_LIMIT_EXCEEDED'   // Límite de requests excedido
500 => 'WPCW_INTERNAL_ERROR'        // Error interno del servidor
```

### **Errores de Aplicación**
```php
// Errores de cupones
'COUPON_NOT_FOUND'         => 'Cupón no encontrado'
'COUPON_EXPIRED'           => 'Cupón expirado'
'COUPON_NOT_AVAILABLE'     => 'Cupón no disponible para canje'
'COUPON_LIMIT_REACHED'     => 'Límite de uso alcanzado'

// Errores de usuario
'USER_NOT_VERIFIED'        => 'Usuario no verificado'
'USER_NOT_AUTHORIZED'      => 'Usuario no autorizado'
'USER_MISSING_WHATSAPP'    => 'Número de WhatsApp requerido'

// Errores de negocio
'BUSINESS_INACTIVE'        => 'Comercio inactivo'
'BUSINESS_NOT_FOUND'       => 'Comercio no encontrado'
'INVALID_BUSINESS_USER'    => 'Usuario no asociado al comercio'

// Errores técnicos
'DATABASE_ERROR'           => 'Error de base de datos'
'EXTERNAL_API_ERROR'       => 'Error en API externa'
'VALIDATION_ERROR'         => 'Error de validación'
```

---

**📅 Última Actualización**: Octubre 2025
**🔧 Versión de Referencia**: 1.5.0
**📚 Documentación Completa**: Ver archivos individuales en `/docs/`