<?php
/**
 * Detector avanzado de configuración regional para múltiples sitios
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Regional_Detector {

    private static $instance   = null;
    private $detection_methods = array();
    private $regional_patterns = array();
    private $confidence_scores = array();

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_detection_methods();
        $this->init_regional_patterns();
    }

    /**
     * Inicializa los métodos de detección
     */
    private function init_detection_methods() {
        $this->detection_methods = array(
            'locale'           => array(
                'weight' => 40,
                'method' => 'detect_by_locale',
            ),
            'timezone'         => array(
                'weight' => 30,
                'method' => 'detect_by_timezone',
            ),
            'currency'         => array(
                'weight' => 20,
                'method' => 'detect_by_currency_symbols',
            ),
            'content_analysis' => array(
                'weight' => 15,
                'method' => 'detect_by_content_analysis',
            ),
            'domain'           => array(
                'weight' => 10,
                'method' => 'detect_by_domain',
            ),
            'server_headers'   => array(
                'weight' => 5,
                'method' => 'detect_by_server_headers',
            ),
        );
    }

    /**
     * Inicializa patrones regionales
     */
    private function init_regional_patterns() {
        $this->regional_patterns = array(
            'AR' => array(
                'locales'          => array( 'es_AR', 'es' ),
                'timezones'        => array( 'America/Argentina/Buenos_Aires', 'America/Argentina/Cordoba' ),
                'currency_symbols' => array( '$', 'ARS', 'peso', 'pesos' ),
                'domain_tlds'      => array( '.ar', '.com.ar' ),
                'phone_patterns'   => array( '+54', '054', '11', '15' ),
                'content_keywords' => array( 'argentina', 'buenos aires', 'córdoba', 'mendoza', 'rosario' ),
                'date_formats'     => array( 'd/m/Y', 'd-m-Y' ),
                'number_formats'   => array( '1.234,56', '1234,56' ),
            ),
            'MX' => array(
                'locales'          => array( 'es_MX', 'es' ),
                'timezones'        => array( 'America/Mexico_City', 'America/Cancun', 'America/Tijuana' ),
                'currency_symbols' => array( '$', 'MXN', 'peso', 'pesos' ),
                'domain_tlds'      => array( '.mx', '.com.mx' ),
                'phone_patterns'   => array( '+52', '052' ),
                'content_keywords' => array( 'méxico', 'mexico', 'cdmx', 'guadalajara', 'monterrey' ),
                'date_formats'     => array( 'd/m/Y', 'd-m-Y' ),
                'number_formats'   => array( '1,234.56', '1234.56' ),
            ),
            'CO' => array(
                'locales'          => array( 'es_CO', 'es' ),
                'timezones'        => array( 'America/Bogota' ),
                'currency_symbols' => array( '$', 'COP', 'peso', 'pesos' ),
                'domain_tlds'      => array( '.co', '.com.co' ),
                'phone_patterns'   => array( '+57', '057' ),
                'content_keywords' => array( 'colombia', 'bogotá', 'medellín', 'cali', 'barranquilla' ),
                'date_formats'     => array( 'd/m/Y', 'd-m-Y' ),
                'number_formats'   => array( '1.234,56', '1234,56' ),
            ),
            'ES' => array(
                'locales'          => array( 'es_ES', 'es' ),
                'timezones'        => array( 'Europe/Madrid' ),
                'currency_symbols' => array( '€', 'EUR', 'euro', 'euros' ),
                'domain_tlds'      => array( '.es', '.com.es' ),
                'phone_patterns'   => array( '+34', '034' ),
                'content_keywords' => array( 'españa', 'madrid', 'barcelona', 'valencia', 'sevilla' ),
                'date_formats'     => array( 'd/m/Y', 'd-m-Y' ),
                'number_formats'   => array( '1.234,56', '1234,56' ),
            ),
            'US' => array(
                'locales'          => array( 'en_US', 'en' ),
                'timezones'        => array( 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles' ),
                'currency_symbols' => array( '$', 'USD', 'dollar', 'dollars' ),
                'domain_tlds'      => array( '.us', '.com' ),
                'phone_patterns'   => array( '+1', '001' ),
                'content_keywords' => array( 'united states', 'usa', 'america', 'new york', 'california' ),
                'date_formats'     => array( 'm/d/Y', 'm-d-Y' ),
                'number_formats'   => array( '1,234.56', '1234.56' ),
            ),
            'BR' => array(
                'locales'          => array( 'pt_BR', 'pt' ),
                'timezones'        => array( 'America/Sao_Paulo', 'America/Rio_Branco' ),
                'currency_symbols' => array( 'R$', 'BRL', 'real', 'reais' ),
                'domain_tlds'      => array( '.br', '.com.br' ),
                'phone_patterns'   => array( '+55', '055' ),
                'content_keywords' => array( 'brasil', 'brazil', 'são paulo', 'rio de janeiro', 'brasília' ),
                'date_formats'     => array( 'd/m/Y', 'd-m-Y' ),
                'number_formats'   => array( '1.234,56', '1234,56' ),
            ),
        );
    }

    /**
     * Detecta la región con múltiples métodos y puntuación de confianza
     */
    public function detect_region() {
        $this->confidence_scores = array();

        // Inicializar puntuaciones
        foreach ( array_keys( $this->regional_patterns ) as $country ) {
            $this->confidence_scores[ $country ] = 0;
        }

        // Aplicar cada método de detección
        foreach ( $this->detection_methods as $method_name => $method_config ) {
            $method = $method_config['method'];
            $weight = $method_config['weight'];

            if ( method_exists( $this, $method ) ) {
                $results = $this->$method();
                $this->apply_detection_results( $results, $weight );
            }
        }

        // Obtener el país con mayor puntuación
        $detected_country = $this->get_highest_confidence_country();

        return array(
            'country'      => $detected_country,
            'confidence'   => $this->confidence_scores[ $detected_country ] ?? 0,
            'scores'       => $this->confidence_scores,
            'methods_used' => array_keys( $this->detection_methods ),
        );
    }

    /**
     * Detección por locale
     */
    private function detect_by_locale() {
        $locale  = get_locale();
        $results = array();

        foreach ( $this->regional_patterns as $country => $patterns ) {
            foreach ( $patterns['locales'] as $pattern_locale ) {
                if ( $locale === $pattern_locale || strpos( $locale, $pattern_locale ) === 0 ) {
                    $results[ $country ] = ( $locale === $pattern_locale ) ? 100 : 80;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Detección por timezone
     */
    private function detect_by_timezone() {
        $timezone = get_option( 'timezone_string' );
        if ( empty( $timezone ) ) {
            return array();
        }

        $results = array();

        foreach ( $this->regional_patterns as $country => $patterns ) {
            foreach ( $patterns['timezones'] as $pattern_timezone ) {
                if ( $timezone === $pattern_timezone ) {
                    $results[ $country ] = 100;
                    break;
                } elseif ( strpos( $timezone, dirname( $pattern_timezone ) ) === 0 ) {
                    $results[ $country ] = 70;
                }
            }
        }

        return $results;
    }

    /**
     * Detección por símbolos de moneda en contenido
     */
    private function detect_by_currency_symbols() {
        $results = array();

        // Buscar en opciones del tema y configuraciones
        $content_to_analyze = array(
            get_option( 'blogdescription', '' ),
            get_option( 'woocommerce_currency', '' ),
            get_option( 'wpcw_currency_symbol', '' ),
        );

        $content = implode( ' ', $content_to_analyze );
        $content = strtolower( $content );

        foreach ( $this->regional_patterns as $country => $patterns ) {
            $score = 0;
            foreach ( $patterns['currency_symbols'] as $symbol ) {
                if ( strpos( $content, strtolower( $symbol ) ) !== false ) {
                    $score += 25;
                }
            }
            if ( $score > 0 ) {
                $results[ $country ] = min( $score, 100 );
            }
        }

        return $results;
    }

    /**
     * Detección por análisis de contenido
     */
    private function detect_by_content_analysis() {
        $results = array();

        // Analizar títulos de posts recientes y páginas
        $recent_posts = get_posts(
            array(
				'numberposts' => 10,
				'post_status' => 'publish',
				'post_type'   => array( 'post', 'page' ),
            )
        );

        $content = '';
        foreach ( $recent_posts as $post ) {
            $content .= ' ' . $post->post_title . ' ' . $post->post_content;
        }

        // Agregar información del sitio
        $content .= ' ' . get_bloginfo( 'name' ) . ' ' . get_bloginfo( 'description' );
        $content  = strtolower( strip_tags( $content ) );

        foreach ( $this->regional_patterns as $country => $patterns ) {
            $score = 0;
            foreach ( $patterns['content_keywords'] as $keyword ) {
                $occurrences = substr_count( $content, strtolower( $keyword ) );
                $score      += $occurrences * 10;
            }
            if ( $score > 0 ) {
                $results[ $country ] = min( $score, 100 );
            }
        }

        return $results;
    }

    /**
     * Detección por dominio
     */
    private function detect_by_domain() {
        $domain  = parse_url( get_site_url(), PHP_URL_HOST );
        $results = array();

        foreach ( $this->regional_patterns as $country => $patterns ) {
            foreach ( $patterns['domain_tlds'] as $tld ) {
                if ( strpos( $domain, $tld ) !== false ) {
                    $results[ $country ] = 90;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Detección por headers del servidor
     */
    private function detect_by_server_headers() {
        $results = array();

        // Verificar headers de Cloudflare
        if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
            $cf_country = $_SERVER['HTTP_CF_IPCOUNTRY'];
            if ( isset( $this->regional_patterns[ $cf_country ] ) ) {
                $results[ $cf_country ] = 95;
            }
        }

        // Verificar otros headers de geolocalización
        $geo_headers = array(
            'HTTP_X_COUNTRY_CODE',
            'HTTP_X_GEOIP_COUNTRY',
            'HTTP_CF_IPCOUNTRY',
        );

        foreach ( $geo_headers as $header ) {
            if ( isset( $_SERVER[ $header ] ) ) {
                $country_code = $_SERVER[ $header ];
                if ( isset( $this->regional_patterns[ $country_code ] ) ) {
                    $results[ $country_code ] = 85;
                }
            }
        }

        return $results;
    }

    /**
     * Aplica los resultados de detección con peso
     */
    private function apply_detection_results( $results, $weight ) {
        foreach ( $results as $country => $confidence ) {
            $weighted_score                       = ( $confidence * $weight ) / 100;
            $this->confidence_scores[ $country ] += $weighted_score;
        }
    }

    /**
     * Obtiene el país con mayor confianza
     */
    private function get_highest_confidence_country() {
        if ( empty( $this->confidence_scores ) ) {
            return 'AR'; // Default
        }

        return array_keys( $this->confidence_scores, max( $this->confidence_scores ) )[0];
    }

    /**
     * Obtiene información detallada de detección
     */
    public function get_detection_details() {
        $detection = $this->detect_region();

        return array(
            'detection'        => $detection,
            'current_settings' => array(
                'locale'      => get_locale(),
                'timezone'    => get_option( 'timezone_string' ),
                'site_url'    => get_site_url(),
                'admin_email' => get_option( 'admin_email' ),
            ),
            'recommendations'  => $this->get_configuration_recommendations( $detection['country'] ),
        );
    }

    /**
     * Obtiene recomendaciones de configuración
     */
    private function get_configuration_recommendations( $country ) {
        if ( ! isset( $this->regional_patterns[ $country ] ) ) {
            return array();
        }

        $patterns         = $this->regional_patterns[ $country ];
        $current_locale   = get_locale();
        $current_timezone = get_option( 'timezone_string' );

        $recommendations = array();

        // Recomendación de locale
        if ( ! in_array( $current_locale, $patterns['locales'] ) ) {
            $recommendations[] = array(
                'type'        => 'locale',
                'current'     => $current_locale,
                'recommended' => $patterns['locales'][0],
                'priority'    => 'high',
            );
        }

        // Recomendación de timezone
        if ( ! in_array( $current_timezone, $patterns['timezones'] ) ) {
            $recommendations[] = array(
                'type'        => 'timezone',
                'current'     => $current_timezone,
                'recommended' => $patterns['timezones'][0],
                'priority'    => 'medium',
            );
        }

        return $recommendations;
    }

    /**
     * Valida si la configuración actual es coherente
     */
    public function validate_current_configuration() {
        $detection       = $this->detect_region();
        $current_country = get_option( 'wpcw_detected_country', 'AR' );

        $is_coherent          = ( $detection['country'] === $current_country );
        $confidence_threshold = 50;

        return array(
            'is_coherent'      => $is_coherent,
            'current_country'  => $current_country,
            'detected_country' => $detection['country'],
            'confidence'       => $detection['confidence'],
            'needs_update'     => ! $is_coherent && $detection['confidence'] > $confidence_threshold,
            'recommendations'  => $this->get_configuration_recommendations( $detection['country'] ),
        );
    }

    /**
     * Fuerza una nueva detección y actualización
     */
    public function force_redetection() {
        $detection = $this->detect_region();

        if ( $detection['confidence'] > 30 ) {
            $auto_config = WPCW_Auto_Config::get_instance();
            $auto_config->apply_country_config( $detection['country'] );

            update_option( 'wpcw_detected_country', $detection['country'] );
            update_option( 'wpcw_detection_confidence', $detection['confidence'] );
            update_option( 'wpcw_last_detection', current_time( 'mysql' ) );

            return array(
                'success'    => true,
                'country'    => $detection['country'],
                'confidence' => $detection['confidence'],
            );
        }

        return array(
            'success'    => false,
            'message'    => 'Confianza insuficiente para aplicar configuración automática',
            'confidence' => $detection['confidence'],
        );
    }
}

// Inicializar si la clase de auto-config existe
if ( class_exists( 'WPCW_Auto_Config' ) ) {
    WPCW_Regional_Detector::get_instance();
}
