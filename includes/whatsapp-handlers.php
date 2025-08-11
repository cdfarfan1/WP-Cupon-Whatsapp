<?php
/**
 * Funciones de manejo de WhatsApp usando enlaces wa.me
 *
 * @package WP_Cupon_WhatsApp
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Formatea un número de teléfono para WhatsApp
 * 
 * @param string $phone_number Número de teléfono a formatear
 * @return string Número formateado para WhatsApp
 */
function wpcw_format_whatsapp_number($phone_number) {
    // Verificar si el número es null o vacío
    if (empty($phone_number) || !is_string($phone_number)) {
        return '';
    }
    
    // Eliminar todos los caracteres no numéricos
    $clean_number = preg_replace('/\D/', '', $phone_number);
    
    // Si el número comienza con 0, reemplazarlo con el código de país
    if (substr($clean_number, 0, 1) === '0') {
        $clean_number = '54' . substr($clean_number, 1);
    }
    
    // Si no tiene código de país, agregar 54 (Argentina)
    if (strlen($clean_number) <= 10) {
        $clean_number = '54' . $clean_number;
    }
    
    return $clean_number;
}

/**
 * Genera un enlace de WhatsApp usando wa.me
 * 
 * @param string $phone_number Número de teléfono
 * @param string $message Mensaje predefinido (opcional)
 * @return string URL de WhatsApp
 */
function wpcw_generate_whatsapp_link($phone_number, $message = '') {
    $formatted_number = wpcw_format_whatsapp_number($phone_number);
    $base_url = 'https://wa.me/';
    
    if (!empty($message)) {
        return $base_url . $formatted_number . '?text=' . urlencode($message);
    }
    
    return $base_url . $formatted_number;
}

/**
 * Genera el mensaje para el canje del cupón
 * 
 * @param array $canje_data Datos del canje
 * @return string Mensaje formateado
 */
function wpcw_get_canje_message($canje_data) {
    return sprintf(
        "🎫 *Solicitud de Canje de Cupón*\n\n" .
        "Número de Canje: %s\n" .
        "Cupón: %s\n" .
        "Comercio: %s\n" .
        "Fecha: %s\n\n" .
        "Para confirmar el canje, utilice este código: %s",
        $canje_data['numero_canje'],
        $canje_data['nombre_cupon'],
        $canje_data['nombre_comercio'],
        $canje_data['fecha_solicitud'],
        $canje_data['token_confirmacion']
    );
}

/**
 * Genera el link de WhatsApp para el canje
 * 
 * @param array $canje_data Datos del canje
 * @return string URL de WhatsApp
 */
function wpcw_get_canje_whatsapp_link($canje_data) {
    $message = wpcw_get_canje_message($canje_data);
    return wpcw_generate_whatsapp_link($canje_data['telefono_comercio'], $message);
}

/**
 * Valida un número de WhatsApp
 * 
 * @param string $phone_number Número a validar
 * @return boolean
 */
function wpcw_validate_whatsapp_number($phone_number) {
    // Verificar si el número es null o vacío
    if (empty($phone_number) || !is_string($phone_number)) {
        return false;
    }
    
    $clean_number = wpcw_format_whatsapp_number($phone_number);
    
    // Verificar longitud mínima (código país + número)
    if (strlen($clean_number) < 11) {
        return false;
    }
    
    // Verificar que comience con 54 (Argentina)
    if (substr($clean_number, 0, 2) !== '54') {
        return false;
    }
    
    return true;
}

/**
 * Obtiene las plantillas de mensajes predefinidos
 * 
 * @param string $type Tipo de mensaje
 * @param array $data Datos para el mensaje
 * @return string Mensaje formateado
 */
function wpcw_get_message_template($type, $data = array()) {
    $templates = array(
        'solicitud_canje' => "🎫 *Solicitud de Canje*\n\nCupón: {cupon}\nComercio: {comercio}\nCódigo: {codigo}",
        'confirmacion_canje' => "✅ *Canje Confirmado*\n\nCupón: {cupon}\nCódigo WC: {codigo_wc}\nVálido hasta: {fecha_validez}",
        'rechazo_canje' => "❌ *Canje Rechazado*\n\nCupón: {cupon}\nMotivo: {motivo}",
        'recordatorio' => "⏰ *Recordatorio*\n\nTu cupón {cupon} vence el {fecha_vencimiento}"
    );
    
    if (!isset($templates[$type])) {
        return '';
    }
    
    $message = $templates[$type];
    foreach ($data as $key => $value) {
        $message = str_replace('{' . $key . '}', (string) $value, $message);
    }
    
    return $message;
}
