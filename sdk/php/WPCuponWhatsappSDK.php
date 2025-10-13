<?php
/**
 * WP Cupón WhatsApp SDK
 *
 * SDK oficial para integración con WP Cupón WhatsApp
 *
 * @package     WPCuponWhatsappSDK
 * @version     1.0.0
 * @author      Pragmatic Solutions - Innovación Aplicada
 * @copyright   2025 Pragmatic Solutions
 * @license     MIT
 * @link        https://pragmatic-solutions.com.ar
 *
 * Desarrollado por:
 * @CTO - Dr. Viktor Petrov ($1.680B)
 * @API - Carlos Mendoza ($820M)
 * @SECURITY - James O'Connor ($850M)
 */

namespace WPCuponWhatsapp\SDK;

/**
 * Clase principal del SDK
 */
class WPCuponWhatsappSDK {

    /**
     * URL base de la API
     * @var string
     */
    private $api_url;

    /**
     * API Key para autenticación
     * @var string
     */
    private $api_key;

    /**
     * API Secret para autenticación
     * @var string
     */
    private $api_secret;

    /**
     * Timeout de las requests (segundos)
     * @var int
     */
    private $timeout = 30;

    /**
     * Versión de la API
     * @var string
     */
    private $api_version = 'v1';

    /**
     * Modo debug
     * @var bool
     */
    private $debug = false;

    /**
     * Log de requests (si debug = true)
     * @var array
     */
    private $log = array();

    /**
     * Constructor
     *
     * @param string $api_url URL base de la instalación WordPress
     * @param string $api_key API Key
     * @param string $api_secret API Secret
     * @param array $options Opciones adicionales
     *
     * @throws \Exception Si faltan credenciales
     */
    public function __construct($api_url, $api_key, $api_secret, $options = array()) {
        if (empty($api_url) || empty($api_key) || empty($api_secret)) {
            throw new \Exception('API URL, Key y Secret son requeridos');
        }

        $this->api_url = rtrim($api_url, '/');
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;

        // Opciones
        if (isset($options['timeout'])) {
            $this->timeout = (int) $options['timeout'];
        }

        if (isset($options['debug'])) {
            $this->debug = (bool) $options['debug'];
        }

        if (isset($options['api_version'])) {
            $this->api_version = $options['api_version'];
        }
    }

    // ========================================
    // BENEFICIARIOS
    // ========================================

    /**
     * Crear beneficiario
     *
     * @param array $data Datos del beneficiario
     * @return array Respuesta de la API
     *
     * @throws \Exception Si hay error en la request
     */
    public function create_beneficiario($data) {
        $required = array('nombre_completo', 'telefono_whatsapp', 'institucion_id');
        $this->validate_required_fields($data, $required);

        return $this->request('POST', '/beneficiarios', $data);
    }

    /**
     * Obtener beneficiario por ID
     *
     * @param int $id ID del beneficiario
     * @return array Respuesta de la API
     */
    public function get_beneficiario($id) {
        return $this->request('GET', "/beneficiarios/{$id}");
    }

    /**
     * Obtener beneficiario por teléfono
     *
     * @param string $telefono Teléfono del beneficiario
     * @return array Respuesta de la API
     */
    public function get_beneficiario_by_phone($telefono) {
        return $this->request('GET', '/beneficiarios', array(
            'telefono' => $telefono
        ));
    }

    /**
     * Listar beneficiarios
     *
     * @param array $filters Filtros opcionales
     * @return array Respuesta de la API
     */
    public function list_beneficiarios($filters = array()) {
        return $this->request('GET', '/beneficiarios', $filters);
    }

    /**
     * Actualizar beneficiario
     *
     * @param int $id ID del beneficiario
     * @param array $data Datos a actualizar
     * @return array Respuesta de la API
     */
    public function update_beneficiario($id, $data) {
        return $this->request('PUT', "/beneficiarios/{$id}", $data);
    }

    /**
     * Desactivar beneficiario
     *
     * @param int $id ID del beneficiario
     * @return array Respuesta de la API
     */
    public function deactivate_beneficiario($id) {
        return $this->request('POST', "/beneficiarios/{$id}/deactivate");
    }

    // ========================================
    // CUPONES
    // ========================================

    /**
     * Crear cupón
     *
     * @param array $data Datos del cupón
     * @return array Respuesta de la API
     */
    public function create_cupon($data) {
        $required = array('codigo', 'tipo_descuento', 'monto', 'institucion_id');
        $this->validate_required_fields($data, $required);

        return $this->request('POST', '/cupones', $data);
    }

    /**
     * Obtener cupón por código
     *
     * @param string $codigo Código del cupón
     * @return array Respuesta de la API
     */
    public function get_cupon($codigo) {
        return $this->request('GET', "/cupones/{$codigo}");
    }

    /**
     * Listar cupones disponibles
     *
     * @param array $filters Filtros opcionales
     * @return array Respuesta de la API
     */
    public function list_cupones($filters = array()) {
        return $this->request('GET', '/cupones', $filters);
    }

    /**
     * Listar cupones por beneficiario
     *
     * @param int $beneficiario_id ID del beneficiario
     * @return array Respuesta de la API
     */
    public function get_cupones_by_beneficiario($beneficiario_id) {
        return $this->request('GET', "/beneficiarios/{$beneficiario_id}/cupones");
    }

    /**
     * Validar si un cupón puede ser usado
     *
     * @param string $codigo Código del cupón
     * @param int $beneficiario_id ID del beneficiario
     * @return array Respuesta de la API
     */
    public function validate_cupon($codigo, $beneficiario_id) {
        return $this->request('POST', '/cupones/validate', array(
            'codigo' => $codigo,
            'beneficiario_id' => $beneficiario_id
        ));
    }

    // ========================================
    // CANJES
    // ========================================

    /**
     * Registrar canje
     *
     * @param array $data Datos del canje
     * @return array Respuesta de la API
     */
    public function create_canje($data) {
        $required = array('beneficiario_id', 'cupon_id', 'tipo_canje');
        $this->validate_required_fields($data, $required);

        return $this->request('POST', '/canjes', $data);
    }

    /**
     * Obtener canje por ID
     *
     * @param int $id ID del canje
     * @return array Respuesta de la API
     */
    public function get_canje($id) {
        return $this->request('GET', "/canjes/{$id}");
    }

    /**
     * Listar canjes
     *
     * @param array $filters Filtros opcionales
     * @return array Respuesta de la API
     */
    public function list_canjes($filters = array()) {
        return $this->request('GET', '/canjes', $filters);
    }

    /**
     * Obtener historial de canjes de un beneficiario
     *
     * @param int $beneficiario_id ID del beneficiario
     * @param array $filters Filtros opcionales
     * @return array Respuesta de la API
     */
    public function get_historial_beneficiario($beneficiario_id, $filters = array()) {
        return $this->request('GET', "/beneficiarios/{$beneficiario_id}/historial", $filters);
    }

    // ========================================
    // INSTITUCIONES
    // ========================================

    /**
     * Obtener información de institución
     *
     * @param int $id ID de la institución
     * @return array Respuesta de la API
     */
    public function get_institucion($id) {
        return $this->request('GET', "/instituciones/{$id}");
    }

    /**
     * Listar instituciones
     *
     * @return array Respuesta de la API
     */
    public function list_instituciones() {
        return $this->request('GET', '/instituciones');
    }

    /**
     * Obtener estadísticas de institución
     *
     * @param int $id ID de la institución
     * @param array $filters Filtros opcionales (fecha_desde, fecha_hasta)
     * @return array Respuesta de la API
     */
    public function get_stats_institucion($id, $filters = array()) {
        return $this->request('GET', "/instituciones/{$id}/stats", $filters);
    }

    // ========================================
    // WHATSAPP
    // ========================================

    /**
     * Enviar mensaje de WhatsApp
     *
     * @param string $telefono Teléfono destino
     * @param string $mensaje Mensaje a enviar
     * @return array Respuesta de la API
     */
    public function send_whatsapp($telefono, $mensaje) {
        return $this->request('POST', '/whatsapp/send', array(
            'telefono' => $telefono,
            'mensaje' => $mensaje
        ));
    }

    /**
     * Enviar template de WhatsApp
     *
     * @param string $telefono Teléfono destino
     * @param string $template_name Nombre del template
     * @param array $params Parámetros del template
     * @return array Respuesta de la API
     */
    public function send_whatsapp_template($telefono, $template_name, $params = array()) {
        return $this->request('POST', '/whatsapp/send-template', array(
            'telefono' => $telefono,
            'template' => $template_name,
            'params' => $params
        ));
    }

    // ========================================
    // WEBHOOKS
    // ========================================

    /**
     * Registrar webhook
     *
     * @param string $event Evento a escuchar
     * @param string $url URL del webhook
     * @return array Respuesta de la API
     */
    public function register_webhook($event, $url) {
        return $this->request('POST', '/webhooks', array(
            'event' => $event,
            'url' => $url
        ));
    }

    /**
     * Listar webhooks registrados
     *
     * @return array Respuesta de la API
     */
    public function list_webhooks() {
        return $this->request('GET', '/webhooks');
    }

    /**
     * Eliminar webhook
     *
     * @param int $id ID del webhook
     * @return array Respuesta de la API
     */
    public function delete_webhook($id) {
        return $this->request('DELETE', "/webhooks/{$id}");
    }

    /**
     * Verificar firma de webhook
     *
     * @param string $payload Cuerpo de la request
     * @param string $signature Firma recibida en header
     * @return bool True si la firma es válida
     */
    public function verify_webhook_signature($payload, $signature) {
        $expected = hash_hmac('sha256', $payload, $this->api_secret);
        return hash_equals($expected, $signature);
    }

    // ========================================
    // MÉTODOS PRIVADOS
    // ========================================

    /**
     * Realizar request a la API
     *
     * @param string $method Método HTTP
     * @param string $endpoint Endpoint de la API
     * @param array $data Datos a enviar
     * @return array Respuesta decodificada
     *
     * @throws \Exception Si hay error en la request
     */
    private function request($method, $endpoint, $data = array()) {
        $url = $this->api_url . '/wp-json/wpcw/' . $this->api_version . $endpoint;

        // Headers
        $headers = array(
            'Authorization: Bearer ' . $this->generate_token(),
            'Content-Type: application/json',
            'X-API-Key: ' . $this->api_key,
            'User-Agent: WPCuponWhatsapp-SDK/1.0.0'
        );

        // Inicializar cURL
        $ch = curl_init();

        // Configurar request según método
        switch ($method) {
            case 'GET':
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                }
                break;

            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;

            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        // Opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        // Ejecutar
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // Log si debug está activo
        if ($this->debug) {
            $this->log[] = array(
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'response' => $response,
                'http_code' => $http_code,
                'timestamp' => date('Y-m-d H:i:s')
            );
        }

        // Manejo de errores
        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        if ($http_code >= 400) {
            $error_data = json_decode($response, true);
            $error_message = isset($error_data['message']) ? $error_data['message'] : 'HTTP Error ' . $http_code;
            throw new \Exception($error_message, $http_code);
        }

        // Decodificar respuesta
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error decodificando JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Generar token de autenticación
     *
     * @return string Token JWT
     */
    private function generate_token() {
        // Implementación simplificada de JWT
        $header = array(
            'typ' => 'JWT',
            'alg' => 'HS256'
        );

        $payload = array(
            'iss' => $this->api_key,
            'iat' => time(),
            'exp' => time() + 3600 // Token válido por 1 hora
        );

        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $header_encoded . '.' . $payload_encoded, $this->api_secret, true);
        $signature_encoded = $this->base64url_encode($signature);

        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }

    /**
     * Encode Base64 URL-safe
     *
     * @param string $data Datos a encodear
     * @return string Datos encodeados
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Validar campos requeridos
     *
     * @param array $data Datos a validar
     * @param array $required Campos requeridos
     * @throws \Exception Si falta algún campo
     */
    private function validate_required_fields($data, $required) {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Campo requerido faltante: {$field}");
            }
        }
    }

    // ========================================
    // MÉTODOS PÚBLICOS DE UTILIDAD
    // ========================================

    /**
     * Obtener log de requests (si debug = true)
     *
     * @return array Log de requests
     */
    public function get_log() {
        return $this->log;
    }

    /**
     * Limpiar log
     */
    public function clear_log() {
        $this->log = array();
    }

    /**
     * Activar modo debug
     */
    public function enable_debug() {
        $this->debug = true;
    }

    /**
     * Desactivar modo debug
     */
    public function disable_debug() {
        $this->debug = false;
    }

    /**
     * Obtener versión del SDK
     *
     * @return string Versión
     */
    public function get_version() {
        return '1.0.0';
    }

    /**
     * Ping a la API
     *
     * @return array Respuesta de la API
     */
    public function ping() {
        return $this->request('GET', '/ping');
    }
}
