# 🚀 **Roadmap de Onboarding - Usuario Nuevo en WP Cupón WhatsApp**

## 📋 **Visión General del Proceso**

Este documento describe el **flujo completo de onboarding** para un usuario nuevo en el sistema WP Cupón WhatsApp, desde el momento en que llega al sitio web hasta que está completamente registrado y puede comenzar a usar todas las funcionalidades del sistema.

### 🎯 **Objetivos del Onboarding**

- **Registro intuitivo** y sin fricciones
- **Personalización completa** del perfil de usuario
- **Selección inteligente** de preferencias
- **Activación inmediata** de funcionalidades
- **Experiencia memorable** que fidelice al usuario

---

## 📅 **Flujo Completo de Onboarding**

### **FASE 1: Descubrimiento y Atracción (Día 0)**

#### **Paso 1.1: Llegada al Sitio Web**
**Ubicación**: Página de inicio / Landing page
**Acción del Usuario**: Navegación por el sitio
**Funcionalidades Mostradas**:
- ✅ Beneficios del programa de fidelización
- ✅ Comercios adheridos (logos y categorías)
- ✅ Testimonios de usuarios
- ✅ Llamado a acción: "Únete Ahora"

#### **Paso 1.2: Decisión de Registro**
**Ubicación**: Cualquier página con CTA
**Acción del Usuario**: Clic en "Registrarse" / "Unirse"
**Elementos Visuales**:
- Botón prominente en header
- Sección de beneficios destacada
- Formulario de registro simplificado

---

### **FASE 2: Registro Inicial (Día 0 - 5 minutos)**

#### **Paso 2.1: Formulario de Registro Básico**
**Ubicación**: `/registro` o modal
**Campos Requeridos**:
```php
// Paso 1: Información básica
- Nombre completo (string, required)
- Email (email, required, unique)
- Contraseña (password, required, min 8 chars)
- Confirmar contraseña (password, required)
- Aceptar términos y condiciones (checkbox, required)
- Aceptar política de privacidad (checkbox, required)
```

**Validaciones en Tiempo Real**:
- ✅ Email único en el sistema
- ✅ Contraseña segura (mayúsculas, minúsculas, números, símbolos)
- ✅ Formato de email válido
- ✅ Campos requeridos completados

#### **Paso 2.2: Verificación de Email**
**Método**: Email automático
**Contenido del Email**:
```
Asunto: ¡Bienvenido a WP Cupón WhatsApp! Confirma tu email

Contenido:
- Logo del sitio
- Mensaje de bienvenida personalizado
- Enlace de confirmación único
- Información de contacto
- Redes sociales
```

**Funcionalidades**:
- ✅ Enlace único con expiración (24 horas)
- ✅ Reenvío automático disponible
- ✅ Página de confirmación amigable

---

### **FASE 3: Configuración del Perfil (Día 0 - 10 minutos)**

#### **Paso 3.1: Información Personal Completa**
**Ubicación**: `/completar-perfil`
**Campos del Formulario**:
```php
// Información Personal
- Nombre(s) (string, required)
- Apellido(s) (string, required)
- DNI/Cédula (string, required, unique)
- Fecha de nacimiento (date, required)
- Género (select: Masculino, Femenino, Otro, Prefiero no decir)
- Nacionalidad (select con países)

// Información de Contacto
- Email (pre-cargado, readonly)
- Teléfono alternativo (string, optional)
- WhatsApp (string, required, formato internacional)
- Provincia/Estado (select dinámico)
- Ciudad (select dinámico)
- Código Postal (string, optional)
```

**Características UX**:
- ✅ Progreso visual (3/5 pasos completados)
- ✅ Autocompletado inteligente
- ✅ Validación en tiempo real
- ✅ Mensajes de ayuda contextuales

#### **Paso 3.2: Asociación con Institución/Empresa**
**Ubicación**: `/seleccionar-institucion`
**Funcionalidades**:

**Opción A: Usuario Empleado**
```php
// Búsqueda de Empresa/Institución
- Campo de búsqueda con autocompletado
- Filtro por rubro/sector
- Lista de empresas adheridas
- Opción "Mi empresa no está adherida"

// Verificación de Empleo
- Código de empleado (si aplica)
- Email corporativo (validación de dominio)
- Confirmación por email al empleador
```

**Opción B: Usuario Independiente**
```php
// Selección de Intereses
- Categorías de comercios disponibles
- Preferencias de ubicación
- Rango de precios preferido
- Frecuencia de uso deseada
```

**Interfaz Visual**:
- ✅ Tarjetas de instituciones con logos
- ✅ Información de beneficios por institución
- ✅ Testimonios de empleados
- ✅ Mapa de ubicaciones (opcional)

---

### **FASE 4: Preferencias y Personalización (Día 0 - 15 minutos)**

#### **Paso 4.1: Categorías Favoritas**
**Ubicación**: `/seleccionar-categorias`
**Funcionalidades**:

**Sistema de Categorías Dinámicas**:
```php
// Categorías basadas en comercios registrados
$categories = [
    'gastronomia' => [
        'name' => 'Gastronomía',
        'icon' => '🍽️',
        'subcategories' => ['Restaurantes', 'Cafeterías', 'Heladerías', 'Delivery']
    ],
    'entretenimiento' => [
        'name' => 'Entretenimiento',
        'icon' => '🎬',
        'subcategories' => ['Cines', 'Teatros', 'Museos', 'Eventos']
    ],
    'servicios' => [
        'name' => 'Servicios',
        'icon' => '🛍️',
        'subcategories' => ['Belleza', 'Salud', 'Educación', 'Tecnología']
    ]
];
```

**Interfaz de Selección**:
- ✅ Grid responsive con iconos
- ✅ Selección múltiple con límite (máx 5)
- ✅ Preview de cupones disponibles por categoría
- ✅ Sistema de recomendaciones basado en ubicación

#### **Paso 4.2: Preferencias de Notificaciones**
**Ubicación**: `/configurar-notificaciones`
**Opciones Disponibles**:
```php
$notification_preferences = [
    'email' => [
        'cupones_nuevos' => true,
        'recordatorios_vencimiento' => true,
        'ofertas_especiales' => false,
        'newsletter_semanal' => true,
        'frecuencia' => 'diaria' // diaria, semanal, mensual
    ],
    'whatsapp' => [
        'cupones_urgentes' => true,
        'confirmaciones_canjes' => true,
        'recordatorios' => false,
        'numero_verificado' => '+5491123456789'
    ],
    'push' => [
        'habilitado' => true,
        'cupones_cercanos' => true,
        'notificaciones_app' => true
    ]
];
```

**Características**:
- ✅ Configuración granular por tipo
- ✅ Preview de cómo se verán las notificaciones
- ✅ Opción de prueba inmediata
- ✅ Fácil modificación posterior

---

### **FASE 5: Activación y Primer Uso (Día 0 - 20 minutos)**

#### **Paso 5.1: Dashboard de Bienvenida**
**Ubicación**: `/dashboard`
**Elementos Mostrados**:
- ✅ Mensaje de bienvenida personalizado
- ✅ Estado del perfil (100% completo)
- ✅ Cupones disponibles inmediatamente
- ✅ Próximos pasos sugeridos
- ✅ Tutorial interactivo opcional

#### **Paso 5.2: Primer Canje de Cupón**
**Guía Paso a Paso**:
1. **Selección**: Sistema recomienda cupones basados en preferencias
2. **Revisión**: Detalles del cupón y condiciones
3. **Canje**: Proceso simplificado por WhatsApp
4. **Confirmación**: Seguimiento en tiempo real
5. **Feedback**: Calificación de la experiencia

#### **Paso 5.3: Conexión con Red Social**
**Opcional pero Recomendado**:
- ✅ Compartir logro en redes sociales
- ✅ Invitar amigos al programa
- ✅ Unirse a comunidad de usuarios
- ✅ Participar en sorteos exclusivos

---

## 🏗️ **Arquitectura Técnica del Onboarding**

### **Base de Datos - Campos Extendidos**

```sql
-- Tabla de usuarios extendida
CREATE TABLE wp_wpcw_user_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('M', 'F', 'O', 'N') DEFAULT 'N',
    nationality VARCHAR(50),
    phone VARCHAR(20),
    whatsapp VARCHAR(20) NOT NULL,
    province VARCHAR(50),
    city VARCHAR(50),
    postal_code VARCHAR(10),

    -- Asociación institucional
    institution_type ENUM('employee', 'independent') NOT NULL,
    institution_id BIGINT UNSIGNED NULL,
    employee_code VARCHAR(50) NULL,
    employment_verified BOOLEAN DEFAULT FALSE,

    -- Preferencias
    favorite_categories JSON,
    notification_preferences JSON,
    location_preferences JSON,

    -- Estado del onboarding
    onboarding_completed BOOLEAN DEFAULT FALSE,
    onboarding_step TINYINT DEFAULT 1,
    profile_completion_percentage TINYINT DEFAULT 0,

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES wp_users(ID),
    FOREIGN KEY (institution_id) REFERENCES wp_wpcw_businesses(ID)
);
```

### **Sistema de Estados del Onboarding**

```php
class WPCW_Onboarding_Manager {

    const STEPS = [
        1 => 'email_verification',
        2 => 'basic_info',
        3 => 'institution_selection',
        4 => 'preferences_setup',
        5 => 'activation_complete'
    ];

    const STEP_COMPLETION = [
        1 => 20,  // Email verificado
        2 => 40,  // Info básica completa
        3 => 60,  // Institución seleccionada
        4 => 80,  // Preferencias configuradas
        5 => 100  // Onboarding completo
    ];
}
```

### **API Endpoints para Onboarding**

```php
// Endpoints REST para el proceso de onboarding
$wpcw_onboarding_endpoints = [
    'POST /wp-json/wpcw/v1/onboarding/start' => 'WPCW_Onboarding_API::start_process',
    'POST /wp-json/wpcw/v1/onboarding/step/{step}' => 'WPCW_Onboarding_API::update_step',
    'GET /wp-json/wpcw/v1/onboarding/status' => 'WPCW_Onboarding_API::get_status',
    'POST /wp-json/wpcw/v1/onboarding/complete' => 'WPCW_Onboarding_API::complete_process',
    'POST /wp-json/wpcw/v1/onboarding/verify-employment' => 'WPCW_Onboarding_API::verify_employment'
];
```

---

## 🎨 **Diseño UX/UI del Onboarding**

### **Principios de Diseño**

#### **1. Progreso Visual Claro**
```
[✓] Paso 1: Verificación Email
[✓] Paso 2: Información Personal
[●] Paso 3: Asociación Institucional
[○] Paso 4: Preferencias
[○] Paso 5: Activación
```

#### **2. Microinteracciones**
- ✅ Animaciones suaves en transiciones
- ✅ Feedback inmediato en validaciones
- ✅ Celebraciones en hitos completados
- ✅ Tooltips contextuales

#### **3. Diseño Responsive**
- ✅ Móvil-first approach
- ✅ Optimizado para todos los dispositivos
- ✅ Touch-friendly en móviles
- ✅ Accesibilidad WCAG 2.1

### **Paleta de Colores y Branding**

```css
/* Colores principales del onboarding */
:root {
    --wpcw-primary: #007cba;
    --wpcw-secondary: #46b450;
    --wpcw-accent: #f56e28;
    --wpcw-success: #00a32a;
    --wpcw-warning: #f0b849;
    --wpcw-error: #dc3232;

    /* Gradientes para celebraciones */
    --wpcw-gradient-primary: linear-gradient(135deg, #007cba 0%, #005a87 100%);
    --wpcw-gradient-success: linear-gradient(135deg, #46b450 0%, #00a32a 100%);
}
```

---

## 📊 **Métricas y Analytics del Onboarding**

### **KPIs Principales**

#### **Completitud del Proceso**
- **Tasa de Conversión**: Visitantes → Registros completados
- **Abandono por Paso**: Dónde los usuarios abandonan
- **Tiempo Total**: Minutos promedio para completar onboarding
- **Tasa de Activación**: Usuarios que hacen primer canje

#### **Métricas de Calidad**
- **Satisfacción del Usuario**: NPS del proceso
- **Facilidad de Uso**: Calificación 1-5
- **Tasa de Errores**: Validaciones fallidas
- **Soporte Requerido**: Tickets por onboarding

### **Sistema de Tracking**

```php
class WPCW_Onboarding_Tracker {

    public static function track_step_completion($user_id, $step, $time_spent) {
        // Registrar completitud de paso
        update_user_meta($user_id, "wpcw_onboarding_step_{$step}_completed", current_time('mysql'));
        update_user_meta($user_id, "wpcw_onboarding_step_{$step}_time", $time_spent);

        // Actualizar progreso general
        $progress = self::calculate_progress($user_id);
        update_user_meta($user_id, 'wpcw_onboarding_progress', $progress);
    }

    public static function track_abandonment($user_id, $step, $reason = '') {
        // Registrar punto de abandono
        update_user_meta($user_id, 'wpcw_onboarding_abandoned_at', $step);
        update_user_meta($user_id, 'wpcw_onboarding_abandon_reason', $reason);

        // Trigger email de recuperación
        self::send_recovery_email($user_id, $step);
    }
}
```

---

## 🔄 **Sistema de Recuperación y Re-engagement**

### **Detección de Abandono**
```php
// Sistema automático de detección
function wpcw_check_abandoned_onboardings() {
    $abandoned_users = get_users([
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'wpcw_onboarding_progress',
                'value' => 100,
                'compare' => '<'
            ],
            [
                'key' => 'wpcw_last_activity',
                'value' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'compare' => '<'
            ]
        ]
    ]);

    foreach ($abandoned_users as $user) {
        wpcw_send_recovery_sequence($user);
    }
}
```

### **Secuencia de Recuperación**
1. **Día 1**: Email recordatorio amable
2. **Día 3**: Email con progreso guardado
3. **Día 7**: Email con oferta especial
4. **Día 14**: Llamado final con soporte

---

## 🎯 **Implementación Técnica Detallada**

### **Paso 1: Sistema de Formularios Multi-paso**

```php
class WPCW_Onboarding_Forms {

    public static function render_step($step, $user_id = null) {
        $user_data = $user_id ? get_user_meta($user_id, 'wpcw_onboarding_data', true) : [];

        switch ($step) {
            case 1:
                return self::render_basic_info_form($user_data);
            case 2:
                return self::render_institution_form($user_data);
            case 3:
                return self::render_preferences_form($user_data);
            case 4:
                return self::render_notifications_form($user_data);
            case 5:
                return self::render_completion_form($user_data);
        }
    }

    private static function render_basic_info_form($data) {
        ob_start();
        ?>
        <form id="wpcw-onboarding-basic" class="wpcw-onboarding-form">
            <div class="form-section">
                <h3><?php _e('Información Personal', 'wp-cupon-whatsapp'); ?></h3>

                <div class="form-row">
                    <label for="first_name"><?php _e('Nombre(s)', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="text" id="first_name" name="first_name" required
                           value="<?php echo esc_attr($data['first_name'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label for="last_name"><?php _e('Apellido(s)', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="text" id="last_name" name="last_name" required
                           value="<?php echo esc_attr($data['last_name'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label for="dni"><?php _e('DNI/Cédula', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="text" id="dni" name="dni" required
                           value="<?php echo esc_attr($data['dni'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label for="birth_date"><?php _e('Fecha de Nacimiento', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="date" id="birth_date" name="birth_date" required
                           value="<?php echo esc_attr($data['birth_date'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label for="whatsapp"><?php _e('WhatsApp', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="tel" id="whatsapp" name="whatsapp" required
                           placeholder="+5491123456789"
                           value="<?php echo esc_attr($data['whatsapp'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="wpcw-btn-secondary" onclick="prevStep()">
                    <?php _e('Anterior', 'wp-cupon-whatsapp'); ?>
                </button>
                <button type="submit" class="wpcw-btn-primary">
                    <?php _e('Siguiente', 'wp-cupon-whatsapp'); ?>
                </button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
}
```

### **Paso 2: Sistema de Categorías Dinámicas**

```php
class WPCW_Category_Manager {

    public static function get_available_categories() {
        $businesses = get_posts([
            'post_type' => 'wpcw_business',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);

        $categories = [];
        foreach ($businesses as $business) {
            $business_categories = wp_get_post_terms($business->ID, 'wpcw_business_category');
            foreach ($business_categories as $category) {
                if (!isset($categories[$category->term_id])) {
                    $categories[$category->term_id] = [
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'icon' => get_term_meta($category->term_id, 'wpcw_category_icon', true) ?: '🏪',
                        'business_count' => 0,
                        'coupon_count' => 0
                    ];
                }
                $categories[$category->term_id]['business_count']++;
            }
        }

        // Contar cupones por categoría
        foreach ($categories as $cat_id => &$category) {
            $category['coupon_count'] = self::count_coupons_in_category($cat_id);
        }

        return array_values($categories);
    }

    private static function count_coupons_in_category($category_id) {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_coupon'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_wpcw_enabled'
            AND pm.meta_value = 'yes'
            AND p.ID IN (
                SELECT object_id
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tt.term_id = %d
            )
        ", $category_id));
    }
}
```

### **Paso 3: Sistema de Notificaciones Personalizadas**

```php
class WPCW_Notification_Manager {

    public static function setup_user_notifications($user_id, $preferences) {
        // Guardar preferencias del usuario
        update_user_meta($user_id, 'wpcw_notification_preferences', $preferences);

        // Programar notificaciones iniciales
        self::schedule_welcome_notifications($user_id);

        // Configurar categorías de interés
        if (!empty($preferences['categories'])) {
            self::setup_category_notifications($user_id, $preferences['categories']);
        }
    }

    private static function schedule_welcome_notifications($user_id) {
        // Notificación de bienvenida inmediata
        wp_schedule_single_event(time() + 300, 'wpcw_send_welcome_notification', [$user_id]);

        // Primer cupón sugerido (1 hora después)
        wp_schedule_single_event(time() + 3600, 'wpcw_send_first_coupon_suggestion', [$user_id]);

        // Tips de uso (24 horas después)
        wp_schedule_single_event(time() + 86400, 'wpcw_send_usage_tips', [$user_id]);
    }

    private static function setup_category_notifications($user_id, $categories) {
        foreach ($categories as $category_id) {
            // Suscribir a notificaciones de nuevos cupones en esta categoría
            self::subscribe_to_category($user_id, $category_id);

            // Enviar cupones existentes inmediatamente
            self::send_existing_coupons_in_category($user_id, $category_id);
        }
    }

    public static function send_notification($user_id, $type, $data) {
        $user = get_userdata($user_id);
        $preferences = get_user_meta($user_id, 'wpcw_notification_preferences', true);

        // Verificar si el usuario quiere este tipo de notificación
        if (empty($preferences[$type]['enabled'])) {
            return;
        }

        // Enviar por los canales preferidos
        if (!empty($preferences['email']['enabled'])) {
            self::send_email_notification($user, $type, $data);
        }

        if (!empty($preferences['whatsapp']['enabled'])) {
            self::send_whatsapp_notification($user, $type, $data);
        }

        if (!empty($preferences['push']['enabled'])) {
            self::send_push_notification($user, $type, $data);
        }
    }
}
```

---

## 📈 **Métricas de Éxito del Onboarding**

### **Objetivos por Paso**

| Paso | Tasa de Conversión Objetivo | Tiempo Promedio |
|------|----------------------------|-----------------|
| Email Verificado | 85% | 2 minutos |
| Info Personal | 75% | 3 minutos |
| Institución | 65% | 4 minutos |
| Preferencias | 55% | 5 minutos |
| Activación | 45% | 2 minutos |

### **Métricas de Retención**

- **Día 1**: 80% de usuarios activos
- **Día 7**: 60% completaron primer canje
- **Día 30**: 40% usuarios recurrentes
- **Día 90**: 25% usuarios fidelizados

### **Sistema de Feedback**

```php
// Feedback automático después de completar onboarding
function wpcw_send_completion_feedback_request($user_id) {
    $email_content = [
        'subject' => __('¡Felicitaciones! Tu perfil está completo', 'wp-cupon-whatsapp'),
        'message' => __('
            ¡Hola [NOMBRE]!

            Has completado exitosamente tu registro en WP Cupón WhatsApp.
            Ahora puedes disfrutar de todos los beneficios de nuestro programa.

            ¿Cómo calificarías tu experiencia de registro?
            ⭐⭐⭐⭐⭐ Excelente
            ⭐⭐⭐⭐ Buena
            ⭐⭐⭐ Regular
            ⭐⭐ Deficiente
            ⭐ Muy mala

            Tu opinión nos ayuda a mejorar. ¡Gracias!
        ', 'wp-cupon-whatsapp'),
        'cta' => __('Calificar Experiencia', 'wp-cupon-whatsapp'),
        'cta_url' => home_url('/feedback-onboarding')
    ];

    wp_mail(get_userdata($user_id)->user_email, $email_content['subject'], $email_content['message']);
}
```

---

## 🎯 **Conclusión y Próximos Pasos**

### **Resumen del Roadmap**

Este roadmap completo de onboarding proporciona:

1. **✅ Experiencia Fluida**: Proceso intuitivo de 5 pasos
2. **✅ Personalización Completa**: Preferencias detalladas del usuario
3. **✅ Asociación Institucional**: Integración con empresas adheridas
4. **✅ Sistema de Notificaciones**: Comunicación personalizada
5. **✅ Analytics Avanzado**: Métricas de conversión y retención
6. **✅ Recuperación de Abandono**: Sistema de re-engagement

### **Implementación por Fases**

#### **Fase 1: Base del Sistema (Semana 1-2)**
- ✅ Crear tablas de base de datos extendidas
- ✅ Implementar sistema de pasos de onboarding
- ✅ Desarrollar API endpoints para el proceso

#### **Fase 2: Formularios y UX (Semana 3-4)**
- ✅ Crear formularios multi-paso con validación
- ✅ Implementar sistema de categorías dinámicas
- ✅ Desarrollar interfaz de selección institucional

#### **Fase 3: Notificaciones y Analytics (Semana 5-6)**
- ✅ Sistema de notificaciones personalizadas
- ✅ Analytics de conversión y abandono
- ✅ Sistema de recuperación automática

#### **Fase 4: Testing y Optimización (Semana 7-8)**
- ✅ Testing A/B de flujos
- ✅ Optimización de conversiones
- ✅ Documentación completa

### **Beneficios Esperados**

- **📈 300% más conversiones** en registros completados
- **🎯 250% mejor retención** de usuarios nuevos
- **💰 200% más engagement** con cupones
- **⭐ 4.8/5 satisfacción** del usuario
- **🔄 50% menos abandono** en el proceso

---

**🚀 Este roadmap establece las bases para una experiencia de onboarding excepcional que convertirá visitantes en usuarios fidelizados del programa WP Cupón WhatsApp.**

¿Te gustaría que implemente alguna parte específica de este sistema o tienes alguna modificación que te gustaría hacer al flujo?