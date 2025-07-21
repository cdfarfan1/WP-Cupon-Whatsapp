<?php
/**
 * Gestión de Estados de Canje
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Define los estados posibles para un canje
 *
 * @return array
 */
function wpcw_get_estados_canje() {
    return array(
        'pendiente_confirmacion' => __('Pendiente de Confirmación', 'wp-cupon-whatsapp'),
        'confirmado' => __('Confirmado', 'wp-cupon-whatsapp'),
        'rechazado' => __('Rechazado', 'wp-cupon-whatsapp'),
        'expirado' => __('Expirado', 'wp-cupon-whatsapp'),
        'cancelado' => __('Cancelado', 'wp-cupon-whatsapp')
    );
}

/**
 * Verifica si un estado de canje es válido
 *
 * @param string $estado
 * @return boolean
 */
function wpcw_es_estado_canje_valido($estado) {
    return array_key_exists($estado, wpcw_get_estados_canje());
}

/**
 * Obtiene el texto para mostrar del estado
 *
 * @param string $estado
 * @return string
 */
function wpcw_get_estado_canje_texto($estado) {
    $estados = wpcw_get_estados_canje();
    return isset($estados[$estado]) ? $estados[$estado] : $estado;
}

/**
 * Obtiene la clase CSS para el estado
 *
 * @param string $estado
 * @return string
 */
function wpcw_get_estado_canje_clase($estado) {
    $clases = array(
        'pendiente_confirmacion' => 'estado-pendiente',
        'confirmado' => 'estado-confirmado',
        'rechazado' => 'estado-rechazado',
        'expirado' => 'estado-expirado',
        'cancelado' => 'estado-cancelado'
    );
    return isset($clases[$estado]) ? $clases[$estado] : '';
}

/**
 * Verifica las transiciones válidas de estado
 *
 * @param string $estado_actual
 * @param string $nuevo_estado
 * @return boolean
 */
function wpcw_es_transicion_estado_valida($estado_actual, $nuevo_estado) {
    $transiciones_validas = array(
        'pendiente_confirmacion' => array('confirmado', 'rechazado', 'cancelado', 'expirado'),
        'confirmado' => array('cancelado'),
        'rechazado' => array('pendiente_confirmacion'),
        'expirado' => array(),
        'cancelado' => array('pendiente_confirmacion')
    );
    
    return isset($transiciones_validas[$estado_actual]) && 
           in_array($nuevo_estado, $transiciones_validas[$estado_actual]);
}
