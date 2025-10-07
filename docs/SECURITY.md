# 🔒 Seguridad y Validación - WP Cupón WhatsApp

## 🛡️ **Principios de Seguridad**

### **Defensa en Profundidad**
El plugin implementa múltiples capas de seguridad:

1. **Validación de Entrada** - Todas las entradas son sanitizadas
2. **Autenticación** - Verificación de usuarios y permisos
3. **Autorización** - Control de acceso basado en roles
4. **Auditoría** - Logging completo de todas las acciones
5. **Rate Limiting** - Prevención de ataques de fuerza bruta
6. **Encriptación** - Protección de datos sensibles

---

## 🔐 **Autenticación y Autorización**

### **Sistema de Roles y Capacidades**

#### **Roles Personalizados**
```php
// Definición de capacidades por rol
$wpcw_roles = array(
    'wpcw_business_owner' => array(
        'manage_own_business' => true,
        'create_coupons' => true,
        'view_redemptions' => true,
        'manage_staff' => false,
    ),
    'wpcw_business_staff' => array(
        'manage_own_business' => false,
        'create_coupons' => false,
        'view_redemptions' => true,
        'confirm_redemptions' => true,
    ),
    'wpcw_institution_manager' => array(
        'manage_institution' => true,
        'create_loyalty_coupons' => true,
        'view_analytics' => true,
        'manage_users' => true,
    )
);
```

#### **Verificación de Permisos**
```php
function wpcw_check_permissions($action, $resource_id = null) {
    $user = wp_get_current_user();
    $user_id = get_current_user_id();

    // Verificar si usuario está logueado
    if (!$user_id) {
        return new WP_Error('not_logged_in', 'Usuario no autenticado');
    }

    // Verificar capacidades específicas
    switch ($action) {
        case 'redeem_coupon':
            if (!wpcw_can_user_redeem_coupon($user_id, $resource_id)) {
                return new WP_Error('insufficient_permissions', 'No tienes permisos para canjear este cupón');
            }
            break;

        case 'manage_business':
            if (!wpcw_is_business_owner($user_id, $resource_id)) {
                return new WP_Error('insufficient_permissions', 'No eres el dueño de este comercio');
            }
            break;
    }

    return true;
}
```

### **Verificación de Email**
```php
class WPCW_Email_Verification {
    public static function is_email_verified($user_id) {
        $verified = get_user_meta($user_id, '_wpcw_email_verified', true);
        return $verified === 'yes';
    }

    public static function send_verification_email($user_id) {
        $user = get_user_by('ID', $user_id);
        $token = wp_generate_password(32, false);

        // Almacenar token con expiración
        update_user_meta($user_id, '_wpcw_verification_token', $token);
        update_user_meta($user_id, '_wpcw_token_expires', time() + 24 * HOUR_IN_SECONDS);

        // Enviar email
        $verification_url = add_query_arg(array(
            'wpcw_verify' => $token,
            'user_id' => $user_id
        ), home_url());

        wp_mail(
            $user->user_email,
            'Verifica tu email - WP Cupón WhatsApp',
            "Haz clic en el siguiente enlace para verificar tu email: $verification_url"
        );
    }
}
```

---

## ✅ **Validación de Datos**

### **Sanitización de Entradas**

#### **Funciones de Sanitización**
```php
function wpcw_sanitize_coupon_data($data) {
    return array(
        'code' => sanitize_text_field($data['code'] ?? ''),
        'description' => wp_kses_post($data['description'] ?? ''),
        'amount' => floatval($data['amount'] ?? 0),
        'whatsapp_text' => wp_kses($data['whatsapp_text'] ?? '', array(
            'br' => array(),
            'strong' => array(),
            'em' => array()
        )),
        'business_id' => absint($data['business_id'] ?? 0),
        'expiry_date' => sanitize_text_field($data['expiry_date'] ?? ''),
    );
}

function wpcw_sanitize_user_data($data) {
    return array(
        'whatsapp' => wpcw_sanitize_phone($data['whatsapp'] ?? ''),
        'dni' => wpcw_sanitize_dni($data['dni'] ?? ''),
        'birth_date' => sanitize_text_field($data['birth_date'] ?? ''),
    );
}

function wpcw_sanitize_phone($phone) {
    // Remover todos los caracteres no numéricos excepto +
    $clean = preg_replace('/[^0-9+]/', '', $phone);

    // Validar formato básico
    if (!preg_match('/^\+?[0-9]{10,15}$/', $clean)) {
        return '';
    }

    return $clean;
}
```

#### **Validación de Formularios**

##### **Validación de Solicitudes de Adhesión**
```php
function wpcw_validate_application($data) {
    $errors = array();

    // Validar tipo de solicitante
    if (empty($data['applicant_type']) || !in_array($data['applicant_type'], array('comercio', 'institucion'))) {
        $errors[] = 'Tipo de solicitante inválido';
    }

    // Validar nombre de fantasía
    if (empty($data['fantasy_name']) || strlen($data['fantasy_name']) < 3) {
        $errors[] = 'El nombre de fantasía debe tener al menos 3 caracteres';
    }

    // Validar CUIT
    if (empty($data['cuit']) || !wpcw_validate_cuit($data['cuit'])) {
        $errors[] = 'CUIT inválido';
    }

    // Validar email
    if (empty($data['email']) || !is_email($data['email'])) {
        $errors[] = 'Email inválido';
    }

    // Validar WhatsApp
    if (empty($data['whatsapp']) || !wpcw_validate_phone($data['whatsapp'])) {
        $errors[] = 'Número de WhatsApp inválido';
    }

    return $errors;
}

function wpcw_validate_cuit($cuit) {
    // Algoritmo de validación de CUIT argentino
    $cuit = preg_replace('/[^0-9]/', '', $cuit);

    if (strlen($cuit) !== 11) {
        return false;
    }

    $multipliers = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
    $sum = 0;

    for ($i = 0; $i < 10; $i++) {
        $sum += $cuit[$i] * $multipliers[$i];
    }

    $verification_digit = (11 - ($sum % 11)) % 11;

    return $cuit[10] == $verification_digit;
}
```

### **Validación de Cupones**
```php
function wpcw_validate_coupon($data) {
    $errors = array();

    // Validar código único
    if (empty($data['code'])) {
        $errors[] = 'El código del cupón es obligatorio';
    } elseif (wpcw_coupon_code_exists($data['code'], $data['id'] ?? 0)) {
        $errors[] = 'Ya existe un cupón con este código';
    }

    // Validar tipo de descuento
    $valid_discount_types = array('percent', 'fixed_cart', 'fixed_product');
    if (!in_array($data['discount_type'], $valid_discount_types)) {
        $errors[] = 'Tipo de descuento inválido';
    }

    // Validar monto
    if ($data['amount'] <= 0) {
        $errors[] = 'El monto debe ser mayor a cero';
    }

    // Validar fecha de expiración
    if (!empty($data['expiry_date']) && strtotime($data['expiry_date']) <= time()) {
        $errors[] = 'La fecha de expiración debe ser futura';
    }

    return $errors;
}
```

---

## 🔑 **Protección CSRF y Nonces**

### **Implementación de Nonces**
```php
// Generar nonce para formularios
function wpcw_generate_form_nonce($action = 'wpcw_form_action') {
    return wp_nonce_field($action, 'wpcw_nonce', true, false);
}

// Verificar nonce
function wpcw_verify_nonce($nonce, $action = 'wpcw_form_action') {
    if (!wp_verify_nonce($nonce, $action)) {
        wp_die('Error de seguridad: Nonce inválido');
    }
    return true;
}

// Uso en formularios
function wpcw_render_secure_form() {
    ob_start();
    ?>
    <form method="post" action="">
        <?php wpcw_generate_form_nonce('submit_application'); ?>

        <!-- Campos del formulario -->

        <input type="submit" name="wpcw_submit" value="Enviar">
    </form>
    <?php
    return ob_get_clean();
}
```

### **Protección de AJAX**
```php
// Verificar nonce en llamadas AJAX
add_action('wp_ajax_wpcw_redeem_coupon', 'wpcw_ajax_redeem_coupon');
add_action('wp_ajax_nopriv_wpcw_redeem_coupon', 'wpcw_ajax_redeem_coupon');

function wpcw_ajax_redeem_coupon() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wpcw_ajax_nonce')) {
        wp_send_json_error('Nonce inválido');
        return;
    }

    // Verificar permisos
    if (!is_user_logged_in()) {
        wp_send_json_error('Usuario no autenticado');
        return;
    }

    // Procesar la solicitud...
}
```

---

## 🚦 **Rate Limiting**

### **Implementación de Límites**
```php
class WPCW_Rate_Limiter {
    private static $limits = array(
        'coupon_redemption' => array('max' => 10, 'window' => 3600), // 10 por hora
        'application_submit' => array('max' => 3, 'window' => 86400), // 3 por día
        'api_requests' => array('max' => 100, 'window' => 60), // 100 por minuto
    );

    public static function check_limit($action, $identifier = null) {
        $identifier = $identifier ?: self::get_client_identifier();

        $key = "wpcw_rate_limit_{$action}_{$identifier}";
        $attempts = get_transient($key) ?: 0;

        if ($attempts >= self::$limits[$action]['max']) {
            return false; // Límite excedido
        }

        // Incrementar contador
        set_transient($key, $attempts + 1, self::$limits[$action]['window']);

        return true;
    }

    private static function get_client_identifier() {
        // Usar IP + User Agent para identificar cliente
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return md5($ip . $user_agent);
    }
}

// Uso
if (!WPCW_Rate_Limiter::check_limit('coupon_redemption')) {
    wp_die('Demasiadas solicitudes. Inténtalo más tarde.');
}
```

---

## 📊 **Auditoría y Logging**

### **Sistema de Logs**
```php
class WPCW_Logger {
    public static function log($message, $level = 'info', $context = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'user_id' => get_current_user_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'context' => $context
        );

        // Almacenar en base de datos
        self::store_log_entry($log_entry);

        // Log crítico en error_log de PHP
        if (in_array($level, array('error', 'critical'))) {
            error_log("WPCW [$level]: $message");
        }
    }

    private static function store_log_entry($entry) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'wpcw_logs',
            array(
                'timestamp' => $entry['timestamp'],
                'level' => $entry['level'],
                'message' => $entry['message'],
                'user_id' => $entry['user_id'],
                'ip_address' => $entry['ip'],
                'user_agent' => $entry['user_agent'],
                'context' => json_encode($entry['context'])
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
    }
}

// Uso
WPCW_Logger::log('Canje confirmado', 'info', array(
    'redemption_id' => 123,
    'coupon_id' => 456,
    'user_id' => 789
));
```

### **Eventos Auditables**
- Creación/modificación/eliminación de cupones
- Solicitudes de canje
- Confirmaciones/rechazos de canjes
- Cambios en comercios/instituciones
- Intentos de acceso no autorizado
- Cambios en configuraciones

---

## 🔒 **Encriptación de Datos Sensibles**

### **Almacenamiento Seguro**
```php
class WPCW_Encryption {
    private static function get_key() {
        if (!defined('WPCW_ENCRYPTION_KEY')) {
            // Generar clave única por instalación
            $key = get_option('wpcw_encryption_key');
            if (!$key) {
                $key = wp_generate_password(32, true, true);
                update_option('wpcw_encryption_key', $key);
            }
            define('WPCW_ENCRYPTION_KEY', $key);
        }
        return WPCW_ENCRYPTION_KEY;
    }

    public static function encrypt($data) {
        $key = self::get_key();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($encrypted_data) {
        $key = self::get_key();
        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}

// Uso para datos sensibles
$encrypted_whatsapp = WPCW_Encryption::encrypt('+5491123456789');
$decrypted_whatsapp = WPCW_Encryption::decrypt($encrypted_whatsapp);
```

---

## 🚨 **Detección y Prevención de Fraudes**

### **Validaciones Anti-Fraude**
```php
function wpcw_detect_fraudulent_activity($user_id, $action) {
    $suspicious_patterns = array();

    // Verificar múltiples canjes desde la misma IP
    $recent_redemptions = wpcw_get_recent_redemptions_from_ip($user_id);
    if (count($recent_redemptions) > 5) {
        $suspicious_patterns[] = 'multiple_redemptions_same_ip';
    }

    // Verificar canjes en corto tiempo
    $time_window_redemptions = wpcw_get_redemptions_in_time_window($user_id, 300); // 5 minutos
    if (count($time_window_redemptions) > 3) {
        $suspicious_patterns[] = 'rapid_succession_redemptions';
    }

    // Verificar uso de cupones expirados frecuentemente
    $expired_attempts = wpcw_get_expired_coupon_attempts($user_id);
    if ($expired_attempts > 10) {
        $suspicious_patterns[] = 'frequent_expired_attempts';
    }

    if (!empty($suspicious_patterns)) {
        WPCW_Logger::log('Actividad sospechosa detectada', 'warning', array(
            'user_id' => $user_id,
            'patterns' => $suspicious_patterns,
            'action' => $action
        ));

        // Implementar acciones preventivas
        wpcw_implement_fraud_prevention($user_id, $suspicious_patterns);
    }

    return $suspicious_patterns;
}

function wpcw_implement_fraud_prevention($user_id, $patterns) {
    // Suspender temporalmente al usuario
    update_user_meta($user_id, '_wpcw_suspended_until', time() + 3600); // 1 hora

    // Notificar administradores
    $admin_email = get_option('admin_email');
    wp_mail(
        $admin_email,
        'Actividad Sospechosa Detectada - WP Cupón WhatsApp',
        "Se detectó actividad sospechosa para el usuario ID: $user_id\nPatrones: " . implode(', ', $patterns)
    );
}
```

---

## 🔧 **Configuración de Seguridad**

### **Opciones de Seguridad**
```php
// Configuraciones de seguridad recomendadas
$wpcw_security_settings = array(
    'enable_email_verification' => true,
    'enable_rate_limiting' => true,
    'max_redemptions_per_hour' => 10,
    'max_applications_per_day' => 3,
    'enable_fraud_detection' => true,
    'log_security_events' => true,
    'encrypt_sensitive_data' => true,
    'session_timeout' => 3600, // 1 hora
    'password_min_length' => 8,
    'require_strong_passwords' => true,
    'enable_2fa' => false, // Para futuras versiones
);
```

### **Checklist de Seguridad**
- [ ] Todas las entradas están sanitizadas
- [ ] Nonces implementados en todos los formularios
- [ ] Permisos verificados antes de cada acción
- [ ] Rate limiting configurado
- [ ] Logs de seguridad habilitados
- [ ] Datos sensibles encriptados
- [ ] Validaciones anti-fraude activas
- [ ] HTTPS requerido para producción
- [ ] Actualizaciones de seguridad regulares

---

## 🚨 **Respuesta a Incidentes**

### **Protocolo de Respuesta**
1. **Detección**: Monitoreo continuo de logs y alertas
2. **Contención**: Suspensión inmediata de usuarios sospechosos
3. **Investigación**: Análisis detallado de logs y patrones
4. **Recuperación**: Restauración de sistemas afectados
5. **Lecciones Aprendidas**: Actualización de medidas preventivas

### **Herramientas de Monitoreo**
```php
// Monitoreo de seguridad en tiempo real
function wpcw_security_monitoring() {
    // Verificar intentos de acceso fallidos
    $failed_attempts = wpcw_get_failed_login_attempts(3600); // Última hora
    if ($failed_attempts > 50) {
        wpcw_send_security_alert('Exceso de intentos de login fallidos');
    }

    // Verificar canjes sospechosos
    $suspicious_redemptions = wpcw_get_suspicious_redemptions(3600);
    if (count($suspicious_redemptions) > 10) {
        wpcw_send_security_alert('Múltiples canjes sospechosos detectados');
    }

    // Verificar uso de recursos
    if (wpcw_is_resource_usage_high()) {
        wpcw_send_security_alert('Uso alto de recursos detectado');
    }
}
add_action('wpcw_hourly_cron', 'wpcw_security_monitoring');
```

---

*Documento creado el: 16 de septiembre de 2025*
*Última actualización: 16 de septiembre de 2025*