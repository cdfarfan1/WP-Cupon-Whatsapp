<?php
/**
 * SDK para integración con WP Cupón WhatsApp
 *
 * @package WPCuponWhatsApp
 * @subpackage SDK
 * @version 1.0.0
 */

namespace WPCW\SDK;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase principal del SDK
 */
class WPCW_SDK {
    /**
     * Version del SDK
     */
    const VERSION = '1.0.0';

    /**
     * URL base de la API
     *
     * @var string
     */
    private $api_url;

    /**
     * Token de autenticación
     *
     * @var string
     */
    private $auth_token;

    /**
     * Constructor
     *
     * @param string $api_url    URL base de la API
     * @param string $auth_token Token de autenticación
     */
    public function __construct($api_url, $auth_token) {
        $this->api_url = rtrim($api_url, '/');
        $this->auth_token = $auth_token;
    }

    /**
     * Realiza una petición a la API
     *
     * @param string $endpoint Endpoint de la API
     * @param string $method   Método HTTP
     * @param array  $data     Datos a enviar
     * @return array|WP_Error
     */
    private function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->api_url . '/' . ltrim($endpoint, '/');

        $args = array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->auth_token,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ),
        );

        if ($data !== null) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    /**
     * Obtiene un cupón por ID
     *
     * @param int $coupon_id ID del cupón
     * @return array|WP_Error
     */
    public function get_coupon($coupon_id) {
        return $this->request('/coupons/' . $coupon_id);
    }

    /**
     * Crea un nuevo cupón
     *
     * @param array $coupon_data Datos del cupón
     * @return array|WP_Error
     */
    public function create_coupon($coupon_data) {
        return $this->request('/coupons', 'POST', $coupon_data);
    }

    /**
     * Canjea un cupón
     *
     * @param int   $coupon_id ID del cupón
     * @param array $data      Datos del canje
     * @return array|WP_Error
     */
    public function redeem_coupon($coupon_id, $data) {
        return $this->request('/coupons/' . $coupon_id . '/redeem', 'POST', $data);
    }

    /**
     * Obtiene estadísticas de un cupón
     *
     * @param int $coupon_id ID del cupón
     * @return array|WP_Error
     */
    public function get_coupon_stats($coupon_id) {
        return $this->request('/coupons/' . $coupon_id . '/stats');
    }

    /**
     * Obtiene un comercio por ID
     *
     * @param int $business_id ID del comercio
     * @return array|WP_Error
     */
    public function get_business($business_id) {
        return $this->request('/businesses/' . $business_id);
    }

    /**
     * Crea un nuevo comercio
     *
     * @param array $business_data Datos del comercio
     * @return array|WP_Error
     */
    public function create_business($business_data) {
        return $this->request('/businesses', 'POST', $business_data);
    }

    /**
     * Actualiza un comercio existente
     *
     * @param int   $business_id   ID del comercio
     * @param array $business_data Datos del comercio
     * @return array|WP_Error
     */
    public function update_business($business_id, $business_data) {
        return $this->request('/businesses/' . $business_id, 'PUT', $business_data);
    }

    /**
     * Obtiene estadísticas de un comercio
     *
     * @param int $business_id ID del comercio
     * @return array|WP_Error
     */
    public function get_business_stats($business_id) {
        return $this->request('/businesses/' . $business_id . '/stats');
    }

    /**
     * Genera un reporte personalizado
     *
     * @param array $params Parámetros del reporte
     * @return array|WP_Error
     */
    public function generate_report($params) {
        return $this->request('/reports/generate', 'POST', $params);
    }

    /**
     * Suscribe un webhook
     *
     * @param string $url    URL del webhook
     * @param array  $events Eventos a suscribir
     * @return array|WP_Error
     */
    public function subscribe_webhook($url, $events) {
        return $this->request('/webhooks', 'POST', array(
            'url' => $url,
            'events' => $events
        ));
    }

    /**
     * Lista los webhooks activos
     *
     * @return array|WP_Error
     */
    public function list_webhooks() {
        return $this->request('/webhooks');
    }

    /**
     * Elimina un webhook
     *
     * @param string $webhook_id ID del webhook
     * @return array|WP_Error
     */
    public function delete_webhook($webhook_id) {
        return $this->request('/webhooks/' . $webhook_id, 'DELETE');
    }
}
