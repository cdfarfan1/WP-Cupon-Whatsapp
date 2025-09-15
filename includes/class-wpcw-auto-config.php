<?php
/**
 * Clase para configuración automática del plugin en múltiples sitios
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Auto_Config {

    private static $instance  = null;
    private $country_configs  = array();
    private $detected_country = null;
    private $site_config      = array();

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_country_configs();
        add_action( 'init', array( $this, 'detect_and_configure' ) );
        add_action( 'wp_ajax_wpcw_auto_configure', array( $this, 'ajax_auto_configure' ) );
        add_action( 'wp_ajax_wpcw_get_country_config', array( $this, 'ajax_get_country_config' ) );
    }

    /**
     * Inicializa las configuraciones por país
     */
    private function init_country_configs() {
        $this->country_configs = array(
            'AR' => array(
                'name'             => 'Argentina',
                'phone_format'     => '+54 9 %s',
                'phone_regex'      => '/^\+?54\s?9?\s?[0-9]{8,10}$/',
                'whatsapp_code'    => '54',
                'currency'         => 'ARS',
                'currency_symbol'  => '$',
                'date_format'      => 'd/m/Y',
                'timezone'         => 'America/Argentina/Buenos_Aires',
                'language'         => 'es_AR',
                'validation_rules' => array(
                    'phone_required'      => true,
                    'email_suggestions'   => true,
                    'whatsapp_validation' => true,
                ),
            ),
            'MX' => array(
                'name'             => 'México',
                'phone_format'     => '+52 %s',
                'phone_regex'      => '/^\+?52\s?[0-9]{10}$/',
                'whatsapp_code'    => '52',
                'currency'         => 'MXN',
                'currency_symbol'  => '$',
                'date_format'      => 'd/m/Y',
                'timezone'         => 'America/Mexico_City',
                'language'         => 'es_MX',
                'validation_rules' => array(
                    'phone_required'      => true,
                    'email_suggestions'   => true,
                    'whatsapp_validation' => true,
                ),
            ),
            'CO' => array(
                'name'             => 'Colombia',
                'phone_format'     => '+57 %s',
                'phone_regex'      => '/^\+?57\s?[0-9]{10}$/',
                'whatsapp_code'    => '57',
                'currency'         => 'COP',
                'currency_symbol'  => '$',
                'date_format'      => 'd/m/Y',
                'timezone'         => 'America/Bogota',
                'language'         => 'es_CO',
                'validation_rules' => array(
                    'phone_required'      => true,
                    'email_suggestions'   => true,
                    'whatsapp_validation' => true,
                ),
            ),
            'ES' => array(
                'name'             => 'España',
                'phone_format'     => '+34 %s',
                'phone_regex'      => '/^\+?34\s?[0-9]{9}$/',
                'whatsapp_code'    => '34',
                'currency'         => 'EUR',
                'currency_symbol'  => '€',
                'date_format'      => 'd/m/Y',
                'timezone'         => 'Europe/Madrid',
                'language'         => 'es_ES',
                'validation_rules' => array(
                    'phone_required'      => true,
                    'email_suggestions'   => true,
                    'whatsapp_validation' => true,
                ),
            ),
            'US' => array(
                'name'             => 'United States',
                'phone_format'     => '+1 %s',
                'phone_regex'      => '/^\+?1\s?[0-9]{10}$/',
                'whatsapp_code'    => '1',
                'currency'         => 'USD',
                'currency_symbol'  => '$',
                'date_format'      => 'm/d/Y',
                'timezone'         => 'America/New_York',
                'language'         => 'en_US',
                'validation_rules' => array(
                    'phone_required'      => true,
                    'email_suggestions'   => true,
                    'whatsapp_validation' => true,
                ),
            ),
            'BR' => array(
                'name'             => 'Brasil',
                'phone_format'     => '+55 %s',
                'phone_regex'      => '/^\+?55\s?[0-9]{10,11}$/',
                'whatsapp_code'    => '55',
                'currency'         => 'BRL',
                'currency_symbol'  => 'R$',
                'date_format'      => 'd/m/Y',
                'timezone'         => 'America/Sao_Paulo',
                'language'         => 'pt_BR',
                'validation_rules' => array(
                    'phone_required'      => true,
                    'email_suggestions'   => true,
                    'whatsapp_validation' => true,
                ),
            ),
        );
    }

    /**
     * Detecta el país y configura automáticamente
     */
    public function detect_and_configure() {
        // Solo ejecutar en admin o durante la instalación
        if ( ! is_admin() && ! defined( 'WP_CLI' ) ) {
            return;
        }

        $this->detected_country = $this->detect_country();

        if ( $this->detected_country && ! get_option( 'wpcw_auto_configured' ) ) {
            $this->apply_country_config( $this->detected_country );
            update_option( 'wpcw_auto_configured', true );
            update_option( 'wpcw_detected_country', $this->detected_country );
        }
    }

    /**
     * Detecta el país del sitio web
     */
    private function detect_country() {
        // 1. Verificar si ya está configurado manualmente
        $manual_country = get_option( 'wpcw_manual_country' );
        if ( $manual_country ) {
            return $manual_country;
        }

        // 2. Detectar por idioma de WordPress
        $locale              = get_locale();
        $country_from_locale = $this->get_country_from_locale( $locale );
        if ( $country_from_locale ) {
            return $country_from_locale;
        }

        // 3. Detectar por timezone
        $timezone              = get_option( 'timezone_string' );
        $country_from_timezone = $this->get_country_from_timezone( $timezone );
        if ( $country_from_timezone ) {
            return $country_from_timezone;
        }

        // 4. Detectar por IP (solo si está disponible)
        $country_from_ip = $this->get_country_from_ip();
        if ( $country_from_ip ) {
            return $country_from_ip;
        }

        // 5. Por defecto, usar configuración genérica
        return 'AR'; // Argentina como default
    }

    /**
     * Obtiene país desde locale
     */
    private function get_country_from_locale( $locale ) {
        $locale_map = array(
            'es_AR' => 'AR',
            'es_MX' => 'MX',
            'es_CO' => 'CO',
            'es_ES' => 'ES',
            'en_US' => 'US',
            'pt_BR' => 'BR',
        );

        return isset( $locale_map[ $locale ] ) ? $locale_map[ $locale ] : null;
    }

    /**
     * Obtiene país desde timezone
     */
    private function get_country_from_timezone( $timezone ) {
        if ( empty( $timezone ) ) {
            return null;
        }

        $timezone_map = array(
            'America/Argentina/'  => 'AR',
            'America/Mexico_City' => 'MX',
            'America/Bogota'      => 'CO',
            'Europe/Madrid'       => 'ES',
            'America/New_York'    => 'US',
            'America/Sao_Paulo'   => 'BR',
        );

        foreach ( $timezone_map as $tz_pattern => $country ) {
            if ( strpos( $timezone, $tz_pattern ) === 0 ) {
                return $country;
            }
        }

        return null;
    }

    /**
     * Obtiene país desde IP (método básico)
     */
    private function get_country_from_ip() {
        // Solo intentar si está en un entorno seguro
        if ( ! isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) && ! function_exists( 'geoip_country_code_by_name' ) ) {
            return null;
        }

        // Cloudflare country header
        if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            return $_SERVER['HTTP_CF_IPCOUNTRY'];
        }

        return null;
    }

    /**
     * Aplica la configuración del país
     */
    public function apply_country_config( $country_code ) {
        if ( ! isset( $this->country_configs[ $country_code ] ) ) {
            return false;
        }

        $config = $this->country_configs[ $country_code ];

        // Aplicar configuraciones básicas
        update_option( 'wpcw_phone_format', $config['phone_format'] );
        update_option( 'wpcw_phone_regex', $config['phone_regex'] );
        update_option( 'wpcw_whatsapp_code', $config['whatsapp_code'] );
        update_option( 'wpcw_currency', $config['currency'] );
        update_option( 'wpcw_currency_symbol', $config['currency_symbol'] );
        update_option( 'wpcw_date_format', $config['date_format'] );

        // Aplicar reglas de validación
        foreach ( $config['validation_rules'] as $rule => $value ) {
            update_option( 'wpcw_' . $rule, $value );
        }

        // Configurar timezone si no está configurado
        if ( get_option( 'timezone_string' ) === '' ) {
            update_option( 'timezone_string', $config['timezone'] );
        }

        // Log de configuración aplicada
        $this->log_config_applied( $country_code, $config );

        return true;
    }

    /**
     * Obtiene la configuración actual del sitio
     */
    public function get_site_config() {
        return array(
            'detected_country' => get_option( 'wpcw_detected_country' ),
            'manual_country'   => get_option( 'wpcw_manual_country' ),
            'phone_format'     => get_option( 'wpcw_phone_format' ),
            'whatsapp_code'    => get_option( 'wpcw_whatsapp_code' ),
            'currency'         => get_option( 'wpcw_currency' ),
            'timezone'         => get_option( 'timezone_string' ),
            'locale'           => get_locale(),
            'auto_configured'  => get_option( 'wpcw_auto_configured', false ),
        );
    }

    /**
     * Obtiene todas las configuraciones disponibles
     */
    public function get_available_countries() {
        return $this->country_configs;
    }

    /**
     * Configura manualmente un país
     */
    public function set_manual_country( $country_code ) {
        if ( ! isset( $this->country_configs[ $country_code ] ) ) {
            return false;
        }

        update_option( 'wpcw_manual_country', $country_code );
        $this->apply_country_config( $country_code );

        return true;
    }

    /**
     * Resetea la configuración automática
     */
    public function reset_auto_config() {
        delete_option( 'wpcw_auto_configured' );
        delete_option( 'wpcw_detected_country' );
        delete_option( 'wpcw_manual_country' );

        // Redetectar y reconfigurar
        $this->detect_and_configure();
    }

    /**
     * AJAX: Auto configurar
     */
    public function ajax_auto_configure() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $country = sanitize_text_field( $_POST['country'] ?? '' );

        if ( empty( $country ) ) {
            $this->reset_auto_config();
            $country = get_option( 'wpcw_detected_country' );
        } else {
            $this->set_manual_country( $country );
        }

        wp_send_json_success(
            array(
				'country' => $country,
				'config'  => $this->get_site_config(),
				'message' => 'Configuración aplicada correctamente',
            )
        );
    }

    /**
     * AJAX: Obtener configuración de país
     */
    public function ajax_get_country_config() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        $country = sanitize_text_field( $_GET['country'] ?? '' );

        if ( ! isset( $this->country_configs[ $country ] ) ) {
            wp_send_json_error( 'País no encontrado' );
        }

        wp_send_json_success( $this->country_configs[ $country ] );
    }

    /**
     * Log de configuración aplicada
     */
    private function log_config_applied( $country_code, $config ) {
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log(
                'auto_config',
                sprintf(
                    'Configuración automática aplicada para %s (%s). Formato teléfono: %s, Código WhatsApp: %s',
                    $config['name'],
                    $country_code,
                    $config['phone_format'],
                    $config['whatsapp_code']
                )
            );
        }
    }

    /**
     * Verifica si el sitio necesita reconfiguración
     */
    public function needs_reconfiguration() {
        $current_locale     = get_locale();
        $configured_country = get_option( 'wpcw_detected_country' );
        $expected_country   = $this->get_country_from_locale( $current_locale );

        return $configured_country !== $expected_country;
    }

    /**
     * Exporta la configuración actual
     */
    public function export_config() {
        return array(
            'version'          => WPCW_VERSION,
            'timestamp'        => current_time( 'mysql' ),
            'site_url'         => get_site_url(),
            'config'           => $this->get_site_config(),
            'country_settings' => $this->country_configs[ get_option( 'wpcw_detected_country', 'AR' ) ],
        );
    }

    /**
     * Importa configuración
     */
    public function import_config( $config_data ) {
        if ( ! is_array( $config_data ) || ! isset( $config_data['config'] ) ) {
            return false;
        }

        $config = $config_data['config'];

        // Aplicar configuración importada
        foreach ( $config as $key => $value ) {
            if ( $key !== 'auto_configured' ) {
                update_option( 'wpcw_' . $key, $value );
            }
        }

        update_option( 'wpcw_auto_configured', true );

        return true;
    }
}

// Inicializar la clase
WPCW_Auto_Config::get_instance();
