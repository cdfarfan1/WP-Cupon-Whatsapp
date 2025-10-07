# 📊 Esquema de Base de Datos - WP Cupón WhatsApp

## 🗄️ Tablas del Sistema

### **wp_wpcw_canjes** - Tabla Principal de Canjes
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

### **wp_options** - Configuraciones del Plugin
```sql
-- Configuraciones principales del plugin
INSERT INTO wp_options (option_name, option_value, autoload) VALUES
('wpcw_version', '1.5.0', 'yes'),
('wpcw_mongodb_enabled', '0', 'yes'),
('wpcw_email_verification_enabled', '1', 'yes'),
('wpcw_default_coupon_validity_days', '30', 'yes'),
('wpcw_max_coupons_per_user', '5', 'yes'),
('wpcw_allow_public_coupons', '1', 'yes'),
('wpcw_setup_wizard_completed', '0', 'yes');
```

## 🔗 Relaciones entre Tablas

### **Diagrama de Relaciones**

```
wp_users (WordPress Core)
    ↑
    └── user_id (wp_wpcw_canjes)

wp_posts (WordPress Core - shop_coupon)
    ↑
    └── coupon_id (wp_wpcw_canjes)

wp_posts (wpcw_business CPT)
    ↑
    └── comercio_id (wp_wpcw_canjes)

wp_posts (wpcw_institution CPT)
    ↑
    └── institución relacionada (meta)

wp_woocommerce_order_items (WooCommerce)
    ↑
    └── id_pedido_wc (wp_wpcw_canjes)
```

## 📋 Estados de Canje

### **Valores Permitidos para estado_canje**
- `pendiente_confirmacion` - Esperando confirmación del comercio
- `confirmado_por_negocio` - Confirmado por el comercio vía WhatsApp
- `rechazado` - Rechazado por el comercio
- `expirado` - Expirado sin acción del comercio
- `cancelado` - Cancelado por el usuario
- `utilizado_en_pedido_wc` - Utilizado en un pedido de WooCommerce

### **Transiciones de Estado Válidas**
```php
$transiciones_validas = array(
    'pendiente_confirmacion' => array('confirmado_por_negocio', 'rechazado', 'expirado', 'cancelado'),
    'confirmado_por_negocio' => array('utilizado_en_pedido_wc', 'expirado'),
    'rechazado' => array(), // Estado final
    'expirado' => array(), // Estado final
    'cancelado' => array(), // Estado final
    'utilizado_en_pedido_wc' => array(), // Estado final
);
```

## 🏷️ Meta Keys de WooCommerce Coupons

### **Campos WPCW en Cupones**
```php
// Campos booleanos
'_wpcw_enabled' => '1', // Habilitado para WhatsApp
'_wpcw_is_loyalty_coupon' => '1', // Es cupón de lealtad
'_wpcw_is_public_coupon' => '1', // Es cupón público
'_wpcw_expiry_reminder' => '1', // Enviar recordatorio de vencimiento
'_wpcw_auto_confirm' => '1', // Confirmación automática

// Campos de texto
'_wpcw_associated_business_id' => '123', // ID del comercio asociado
'_wpcw_whatsapp_text' => 'Mensaje personalizado...', // Mensaje WhatsApp
'_wpcw_redemption_hours' => 'Lun-Vie 9:00-18:00', // Horario de canje
'_wpcw_max_uses_per_user' => '5', // Máximo de usos por usuario
'_wpcw_coupon_category_id' => '456', // ID de categoría de cupón
'_wpcw_coupon_image_id' => '789', // ID de imagen del cupón

// Campos institucionales
'_wpcw_instit_coupon_applicable_businesses' => array(1, 2, 3), // Comercios aplicables
'_wpcw_instit_coupon_applicable_categories' => array(4, 5, 6), // Categorías aplicables
```

## 👥 Meta Keys de Usuarios

### **Campos WPCW en Usuarios**
```php
// Información personal
'_wpcw_dni_number' => '12345678',
'_wpcw_birth_date' => '1990-01-01',
'_wpcw_whatsapp_number' => '+5491123456789',

// Institucional
'_wpcw_user_institution_id' => '123', // ID de institución
'_wpcw_user_favorite_coupon_categories' => array(1, 2, 3), // Categorías favoritas

// Estadísticas
'_wpcw_total_redemptions' => '15',
'_wpcw_successful_redemptions' => '12',
'_wpcw_last_redemption_date' => '2025-09-16 10:30:00',
```

## 🏪 Meta Keys de Comercios (wpcw_business)

### **Campos de Comercio**
```php
'_wpcw_owner_user_id' => '123', // Usuario dueño
'_wpcw_legal_name' => 'Empresa S.A.',
'_wpcw_cuit' => '30123456789',
'_wpcw_contact_person' => 'Juan Pérez',
'_wpcw_email' => 'contacto@empresa.com',
'_wpcw_whatsapp' => '+5491123456789',
'_wpcw_address_main' => 'Calle Principal 123, Ciudad',
'_wpcw_logo_image_id' => '456',
'_wpcw_business_status' => 'active', // active, inactive, suspended
'_wpcw_registration_date' => '2025-01-15',
```

## 🏫 Meta Keys de Instituciones (wpcw_institution)

### **Campos de Institución**
```php
'_wpcw_manager_user_id' => '123', // Usuario gestor
'_wpcw_legal_name' => 'Institución Educativa S.A.',
'_wpcw_cuit' => '2730123456789',
'_wpcw_contact_person' => 'María González',
'_wpcw_email' => 'info@institucion.edu.ar',
'_wpcw_whatsapp' => '+5491123456789',
'_wpcw_address_main' => 'Av. Educación 456, Ciudad',
'_wpcw_institution_type' => 'university', // university, school, etc.
'_wpcw_member_count' => '1500', // Número de miembros
```

## 📊 Tablas de Estadísticas

### **wp_wpcw_coupon_stats** (Meta de Cupones)
```php
// Estructura de estadísticas por mes
'_wpcw_coupon_stats' => array(
    '2025-09' => array(
        'redeemed' => 45,
        'used' => 38,
        'expired' => 7
    ),
    '2025-08' => array(
        'redeemed' => 52,
        'used' => 41,
        'expired' => 11
    )
)
```

### **wp_wpcw_business_stats** (Meta de Comercios)
```php
'_wpcw_business_stats' => array(
    'total_coupons' => 25,
    'active_coupons' => 18,
    'total_redemptions' => 156,
    'successful_redemptions' => 142,
    'failed_redemptions' => 14,
    'avg_response_time' => '2.5', // horas
    'last_activity' => '2025-09-16 14:30:00'
)
```

## 🔍 Consultas Comunes

### **Canjes Pendientes por Comercio**
```sql
SELECT c.*, u.display_name, p.post_title as coupon_title
FROM wp_wpcw_canjes c
LEFT JOIN wp_users u ON c.user_id = u.ID
LEFT JOIN wp_posts p ON c.coupon_id = p.ID
WHERE c.estado_canje = 'pendiente_confirmacion'
AND c.comercio_id = %d
ORDER BY c.fecha_solicitud_canje DESC
```

### **Estadísticas de Canjes por Mes**
```sql
SELECT
    DATE_FORMAT(fecha_solicitud_canje, '%Y-%m') as mes,
    COUNT(*) as total_canjes,
    SUM(CASE WHEN estado_canje = 'confirmado_por_negocio' THEN 1 ELSE 0 END) as confirmados,
    SUM(CASE WHEN estado_canje = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM wp_wpcw_canjes
WHERE fecha_solicitud_canje >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(fecha_solicitud_canje, '%Y-%m')
ORDER BY mes DESC
```

### **Cupones Más Utilizados**
```sql
SELECT
    c.coupon_id,
    p.post_title as coupon_title,
    COUNT(*) as total_canjes,
    SUM(CASE WHEN c.estado_canje = 'utilizado_en_pedido_wc' THEN 1 ELSE 0 END) as utilizados
FROM wp_wpcw_canjes c
LEFT JOIN wp_posts p ON c.coupon_id = p.ID
WHERE c.fecha_solicitud_canje >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY c.coupon_id, p.post_title
ORDER BY total_canjes DESC
LIMIT 10
```

## 🚀 Optimizaciones de Base de Datos

### **Índices Recomendados**
```sql
-- Índices adicionales para optimización
CREATE INDEX idx_wpcw_canjes_user_date ON wp_wpcw_canjes (user_id, fecha_solicitud_canje);
CREATE INDEX idx_wpcw_canjes_coupon_status ON wp_wpcw_canjes (coupon_id, estado_canje);
CREATE INDEX idx_wpcw_canjes_comercio_date ON wp_wpcw_canjes (comercio_id, fecha_solicitud_canje);
CREATE INDEX idx_wpcw_canjes_token ON wp_wpcw_canjes (token_confirmacion);
```

### **Particionamiento (para tablas grandes)**
```sql
-- Particionamiento por mes para tablas con muchos registros
ALTER TABLE wp_wpcw_canjes
PARTITION BY RANGE (YEAR(fecha_solicitud_canje)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

## 🔧 Mantenimiento

### **Limpieza de Datos Antiguos**
```sql
-- Eliminar canjes expirados después de 1 año
DELETE FROM wp_wpcw_canjes
WHERE estado_canje = 'expirado'
AND fecha_solicitud_canje < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Archivar canjes antiguos (opcional)
INSERT INTO wp_wpcw_canjes_archive
SELECT * FROM wp_wpcw_canjes
WHERE fecha_solicitud_canje < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

### **Verificación de Integridad**
```sql
-- Verificar que todos los coupon_id existen
SELECT c.id, c.coupon_id
FROM wp_wpcw_canjes c
LEFT JOIN wp_posts p ON c.coupon_id = p.ID
WHERE p.ID IS NULL;

-- Verificar que todos los user_id existen
SELECT c.id, c.user_id
FROM wp_wpcw_canjes c
LEFT JOIN wp_users u ON c.user_id = u.ID
WHERE u.ID IS NULL;
```

---

*Documento creado el: 16 de septiembre de 2025*
*Última actualización: 16 de septiembre de 2025*