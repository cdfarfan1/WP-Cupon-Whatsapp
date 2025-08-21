<?php
/**
 * Validaciones Mejoradas - WP Cupón WhatsApp
 * 
 * Incluye validación de email y WhatsApp usando wa.me sin API
 * Corrige errores de carga de teléfono
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para validaciones mejoradas
 */
class WPCW_Enhanced_Validation {
    
    /**
     * Inicializar validaciones
     */
    public static function init() {
        add_action('wp_ajax_wpcw_validate_email', array(__CLASS__, 'ajax_validate_email'));
        add_action('wp_ajax_wpcw_validate_whatsapp', array(__CLASS__, 'ajax_validate_whatsapp'));
        add_action('wp_ajax_wpcw_test_whatsapp_link', array(__CLASS__, 'ajax_test_whatsapp_link'));
        
        // Hooks para validación en guardado
        add_filter('wpcw_validate_business_data', array(__CLASS__, 'validate_business_data'), 10, 2);
        add_filter('wpcw_validate_user_data', array(__CLASS__, 'validate_user_data'), 10, 2);
    }
    
    /**
     * Validar email mejorado
     * 
     * @param string $email Email a validar
     * @return array Resultado de validación
     */
    public static function validate_email($email) {
        $result = array(
            'valid' => false,
            'message' => '',
            'suggestions' => array()
        );
        
        // Verificar si está vacío
        if (empty($email)) {
            $result['message'] = __('El email es obligatorio', 'wp-cupon-whatsapp');
            return $result;
        }
        
        // Limpiar email
        $email = sanitize_email($email);
        
        // Validación básica de formato
        if (!is_email($email)) {
            $result['message'] = __('Formato de email inválido', 'wp-cupon-whatsapp');
            return $result;
        }
        
        // Validaciones adicionales
        $domain = substr(strrchr($email, '@'), 1);
        
        // Verificar dominio común mal escrito
        $common_domains = array(
            'gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com',
            'live.com', 'icloud.com', 'protonmail.com'
        );
        
        $suggestions = self::suggest_email_corrections($email, $common_domains);
        if (!empty($suggestions)) {
            $result['suggestions'] = $suggestions;
        }
        
        // Verificar longitud del dominio
        if (strlen($domain) < 2) {
            $result['message'] = __('Dominio de email inválido', 'wp-cupon-whatsapp');
            return $result;
        }
        
        // Verificar caracteres especiales
        if (preg_match('/[<>"\']/', $email)) {
            $result['message'] = __('El email contiene caracteres no permitidos', 'wp-cupon-whatsapp');
            return $result;
        }
        
        $result['valid'] = true;
        $result['message'] = __('Email válido', 'wp-cupon-whatsapp');
        
        return $result;
    }
    
    /**
     * Sugerir correcciones de email
     * 
     * @param string $email Email original
     * @param array $common_domains Dominios comunes
     * @return array Sugerencias
     */
    private static function suggest_email_corrections($email, $common_domains) {
        $suggestions = array();
        $domain = substr(strrchr($email, '@'), 1);
        $username = substr($email, 0, strrpos($email, '@'));
        
        // Verificar similitud con dominios comunes
        foreach ($common_domains as $common_domain) {
            $similarity = 0;
            similar_text($domain, $common_domain, $similarity);
            
            if ($similarity > 70 && $domain !== $common_domain) {
                $suggestions[] = $username . '@' . $common_domain;
            }
        }
        
        return array_slice($suggestions, 0, 2); // Máximo 2 sugerencias
    }
    
    /**
     * Validar número de WhatsApp mejorado
     * 
     * @param string $phone Número de teléfono
     * @return array Resultado de validación
     */
    public static function validate_whatsapp($phone) {
        $result = array(
            'valid' => false,
            'message' => '',
            'formatted' => '',
            'wa_link' => ''
        );
        
        // Verificar si está vacío
        if (empty($phone)) {
            $result['message'] = __('El número de WhatsApp es obligatorio', 'wp-cupon-whatsapp');
            return $result;
        }
        
        // Limpiar número (solo dígitos)
        $clean_phone = preg_replace('/\D/', '', $phone);
        
        // Verificar longitud mínima
        if (strlen($clean_phone) < 8) {
            $result['message'] = __('Número de WhatsApp muy corto', 'wp-cupon-whatsapp');
            return $result;
        }
        
        // Formatear para Argentina
        $formatted = self::format_argentine_phone($clean_phone);
        
        if (!$formatted) {
            $result['message'] = __('Formato de número de WhatsApp inválido para Argentina', 'wp-cupon-whatsapp');
            return $result;
        }
        
        // Verificar que no sea un número obviamente falso
        if (self::is_fake_phone($formatted)) {
            $result['message'] = __('El número de WhatsApp parece ser inválido', 'wp-cupon-whatsapp');
            return $result;
        }
        
        // Generar enlace wa.me
        $wa_link = self::generate_wa_me_link($formatted);
        
        $result['valid'] = true;
        $result['message'] = __('Número de WhatsApp válido', 'wp-cupon-whatsapp');
        $result['formatted'] = $formatted;
        $result['wa_link'] = $wa_link;
        
        return $result;
    }
    
    /**
     * Formatear número argentino
     * 
     * @param string $phone Número limpio
     * @return string|false Número formateado o false si es inválido
     */
    private static function format_argentine_phone($phone) {
        // Remover código de país si está presente
        if (substr($phone, 0, 2) === '54') {
            $phone = substr($phone, 2);
        }
        
        // Remover 0 inicial si está presente
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }
        
        // Verificar longitud (debe ser 10 dígitos para Argentina)
        if (strlen($phone) !== 10) {
            return false;
        }
        
        // Verificar que comience con 9 (celular) o código de área válido
        $area_code = substr($phone, 0, 2);
        $valid_area_codes = array('11', '22', '23', '26', '29', '34', '35', '37', '38', '26', '29');
        
        if (substr($phone, 0, 1) !== '9' && !in_array($area_code, $valid_area_codes)) {
            return false;
        }
        
        // Agregar código de país
        return '54' . $phone;
    }
    
    /**
     * Verificar si es un número falso
     * 
     * @param string $phone Número formateado
     * @return bool
     */
    private static function is_fake_phone($phone) {
        // Números con todos los dígitos iguales
        $digits = substr($phone, 2); // Remover código de país
        if (preg_match('/^(.)\1+$/', $digits)) {
            return true;
        }
        
        // Números secuenciales
        if (preg_match('/^(0123456789|1234567890|9876543210)/', $digits)) {
            return true;
        }
        
        // Números conocidos como falsos
        $fake_numbers = array(
            '541111111111',
            '541234567890',
            '549999999999'
        );
        
        return in_array($phone, $fake_numbers);
    }
    
    /**
     * Generar enlace wa.me
     * 
     * @param string $phone Número formateado
     * @param string $message Mensaje opcional
     * @return string URL de WhatsApp
     */
    public static function generate_wa_me_link($phone, $message = '') {
        $base_url = 'https://wa.me/';
        $url = $base_url . $phone;
        
        if (!empty($message)) {
            $url .= '?text=' . urlencode($message);
        }
        
        return $url;
    }
    
    /**
     * AJAX: Validar email
     */
    public static function ajax_validate_email() {
        check_ajax_referer('wpcw_validation_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email'] ?? '');
        $result = self::validate_email($email);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX: Validar WhatsApp
     */
    public static function ajax_validate_whatsapp() {
        check_ajax_referer('wpcw_validation_nonce', 'nonce');
        
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $result = self::validate_whatsapp($phone);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX: Probar enlace de WhatsApp
     */
    public static function ajax_test_whatsapp_link() {
        check_ajax_referer('wpcw_validation_nonce', 'nonce');
        
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_text_field($_POST['message'] ?? 'Prueba de conexión');
        
        $validation = self::validate_whatsapp($phone);
        
        if ($validation['valid']) {
            $link = self::generate_wa_me_link($validation['formatted'], $message);
            wp_send_json_success(array(
                'link' => $link,
                'message' => __('Enlace de WhatsApp generado correctamente', 'wp-cupon-whatsapp')
            ));
        } else {
            wp_send_json_error(array(
                'message' => $validation['message']
            ));
        }
    }
    
    /**
     * Validar datos de comercio
     * 
     * @param array $errors Errores existentes
     * @param array $data Datos a validar
     * @return array Errores actualizados
     */
    public static function validate_business_data($errors, $data) {
        // Validar email
        if (!empty($data['email'])) {
            $email_validation = self::validate_email($data['email']);
            if (!$email_validation['valid']) {
                $errors['email'] = $email_validation['message'];
            }
        }
        
        // Validar WhatsApp
        if (!empty($data['whatsapp'])) {
            $whatsapp_validation = self::validate_whatsapp($data['whatsapp']);
            if (!$whatsapp_validation['valid']) {
                $errors['whatsapp'] = $whatsapp_validation['message'];
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar datos de usuario
     * 
     * @param array $errors Errores existentes
     * @param array $data Datos a validar
     * @return array Errores actualizados
     */
    public static function validate_user_data($errors, $data) {
        // Validar email
        if (!empty($data['user_email'])) {
            $email_validation = self::validate_email($data['user_email']);
            if (!$email_validation['valid']) {
                $errors['user_email'] = $email_validation['message'];
            }
        }
        
        return $errors;
    }
    
    /**
     * Corregir error de carga de teléfono
     * 
     * @param mixed $value Valor del meta
     * @param int $post_id ID del post
     * @param string $meta_key Clave del meta
     * @return mixed Valor corregido
     */
    public static function fix_phone_loading($value, $post_id, $meta_key) {
        // Verificar si es un campo de teléfono
        if (in_array($meta_key, array('_wpcw_business_phone', '_wpcw_business_whatsapp'))) {
            // Si el valor está vacío o es null, devolver string vacío
            if (is_null($value) || $value === false) {
                return '';
            }
            
            // Si es un array (error común), tomar el primer elemento
            if (is_array($value)) {
                return isset($value[0]) ? $value[0] : '';
            }
            
            // Asegurar que sea string
            return (string) $value;
        }
        
        return $value;
    }
}

// Inicializar validaciones mejoradas
WPCW_Enhanced_Validation::init();

// Hook para corregir carga de teléfono
add_filter('get_post_metadata', array('WPCW_Enhanced_Validation', 'fix_phone_loading'), 10, 3);

/**
 * Funciones de utilidad para compatibilidad
 */

/**
 * Validar email (función de compatibilidad)
 * 
 * @param string $email Email a validar
 * @return bool
 */
function wpcw_validate_email_enhanced($email) {
    $result = WPCW_Enhanced_Validation::validate_email($email);
    return $result['valid'];
}

/**
 * Validar WhatsApp (función de compatibilidad)
 * 
 * @param string $phone Número a validar
 * @return bool
 */
function wpcw_validate_whatsapp_enhanced($phone) {
    $result = WPCW_Enhanced_Validation::validate_whatsapp($phone);
    return $result['valid'];
}

/**
 * Generar enlace wa.me (función de compatibilidad)
 * 
 * @param string $phone Número de teléfono
 * @param string $message Mensaje opcional
 * @return string URL de WhatsApp
 */
function wpcw_generate_wa_me_link_enhanced($phone, $message = '') {
    return WPCW_Enhanced_Validation::generate_wa_me_link($phone, $message);
}