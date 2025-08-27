<?php
/**
 * Sistema de instalación y configuración automatizada para despliegue masivo
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Auto_Installer {

    private static $instance        = null;
    private $installation_steps     = array();
    private $configuration_profiles = array();
    private $deployment_modes       = array();

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_installation_steps();
        $this->init_configuration_profiles();
        $this->init_deployment_modes();

        add_action( 'wp_ajax_wpcw_auto_install', array( $this, 'ajax_auto_install' ) );
        add_action( 'wp_ajax_wpcw_bulk_install', array( $this, 'ajax_bulk_install' ) );
        add_action( 'wp_ajax_wpcw_get_install_status', array( $this, 'ajax_get_install_status' ) );
        add_action( 'wp_ajax_wpcw_validate_requirements', array( $this, 'ajax_validate_requirements' ) );

        // Hook para instalación automática en activación
        add_action( 'wpcw_plugin_activated', array( $this, 'auto_install_on_activation' ) );
    }

    /**
     * Inicializa los pasos de instalación
     */
    private function init_installation_steps() {
        $this->installation_steps = array(
            'validate_requirements'  => array(
                'name'           => 'Validar Requisitos',
                'description'    => 'Verificar compatibilidad del servidor y WordPress',
                'method'         => 'validate_system_requirements',
                'critical'       => true,
                'estimated_time' => 5,
            ),
            'create_database_tables' => array(
                'name'           => 'Crear Tablas de Base de Datos',
                'description'    => 'Crear estructura de base de datos necesaria',
                'method'         => 'create_database_tables',
                'critical'       => true,
                'estimated_time' => 10,
            ),
            'detect_region'          => array(
                'name'           => 'Detectar Región',
                'description'    => 'Detectar automáticamente la configuración regional',
                'method'         => 'detect_and_configure_region',
                'critical'       => false,
                'estimated_time' => 5,
            ),
            'configure_defaults'     => array(
                'name'           => 'Configurar Valores por Defecto',
                'description'    => 'Aplicar configuración inicial recomendada',
                'method'         => 'configure_default_settings',
                'critical'       => true,
                'estimated_time' => 8,
            ),
            'create_user_roles'      => array(
                'name'           => 'Crear Roles de Usuario',
                'description'    => 'Configurar roles y capacidades personalizadas',
                'method'         => 'create_user_roles',
                'critical'       => true,
                'estimated_time' => 5,
            ),
            'setup_cron_jobs'        => array(
                'name'           => 'Configurar Tareas Programadas',
                'description'    => 'Programar tareas automáticas del sistema',
                'method'         => 'setup_cron_jobs',
                'critical'       => false,
                'estimated_time' => 3,
            ),
            'create_sample_data'     => array(
                'name'           => 'Crear Datos de Ejemplo',
                'description'    => 'Generar contenido de demostración (opcional)',
                'method'         => 'create_sample_data',
                'critical'       => false,
                'estimated_time' => 15,
            ),
            'optimize_performance'   => array(
                'name'           => 'Optimizar Rendimiento',
                'description'    => 'Aplicar configuraciones de optimización',
                'method'         => 'optimize_performance_settings',
                'critical'       => false,
                'estimated_time' => 5,
            ),
            'setup_logging'          => array(
                'name'           => 'Configurar Sistema de Logs',
                'description'    => 'Inicializar sistema de logging centralizado',
                'method'         => 'setup_centralized_logging',
                'critical'       => false,
                'estimated_time' => 3,
            ),
            'finalize_installation'  => array(
                'name'           => 'Finalizar Instalación',
                'description'    => 'Completar proceso y verificar integridad',
                'method'         => 'finalize_installation',
                'critical'       => true,
                'estimated_time' => 5,
            ),
        );
    }

    /**
     * Inicializa perfiles de configuración predefinidos
     */
    private function init_configuration_profiles() {
        $this->configuration_profiles = array(
            'minimal'     => array(
                'name'        => 'Instalación Mínima',
                'description' => 'Solo funcionalidades básicas, ideal para sitios pequeños',
                'steps'       => array(
                    'validate_requirements',
                    'create_database_tables',
                    'detect_region',
                    'configure_defaults',
                    'create_user_roles',
                    'finalize_installation',
                ),
                'settings'    => array(
                    'enable_logging'           => false,
                    'create_sample_data'       => false,
                    'enable_cron_jobs'         => false,
                    'performance_optimization' => 'basic',
                ),
            ),
            'standard'    => array(
                'name'        => 'Instalación Estándar',
                'description' => 'Configuración completa recomendada para la mayoría de sitios',
                'steps'       => array(
                    'validate_requirements',
                    'create_database_tables',
                    'detect_region',
                    'configure_defaults',
                    'create_user_roles',
                    'setup_cron_jobs',
                    'optimize_performance',
                    'setup_logging',
                    'finalize_installation',
                ),
                'settings'    => array(
                    'enable_logging'           => true,
                    'create_sample_data'       => false,
                    'enable_cron_jobs'         => true,
                    'performance_optimization' => 'standard',
                ),
            ),
            'enterprise'  => array(
                'name'        => 'Instalación Empresarial',
                'description' => 'Configuración completa con todas las funcionalidades',
                'steps'       => array_keys( $this->installation_steps ),
                'settings'    => array(
                    'enable_logging'           => true,
                    'create_sample_data'       => true,
                    'enable_cron_jobs'         => true,
                    'performance_optimization' => 'advanced',
                    'centralized_logging'      => true,
                    'remote_monitoring'        => true,
                ),
            ),
            'development' => array(
                'name'        => 'Entorno de Desarrollo',
                'description' => 'Configuración para desarrollo con datos de prueba',
                'steps'       => array_keys( $this->installation_steps ),
                'settings'    => array(
                    'enable_logging'           => true,
                    'create_sample_data'       => true,
                    'enable_cron_jobs'         => false,
                    'performance_optimization' => 'debug',
                    'debug_mode'               => true,
                    'log_level'                => 'debug',
                ),
            ),
        );
    }

    /**
     * Inicializa modos de despliegue
     */
    private function init_deployment_modes() {
        $this->deployment_modes = array(
            'single_site'       => array(
                'name'        => 'Sitio Individual',
                'description' => 'Instalación en un solo sitio WordPress',
                'method'      => 'deploy_single_site',
            ),
            'multisite_network' => array(
                'name'        => 'Red Multisitio',
                'description' => 'Instalación en red WordPress multisitio',
                'method'      => 'deploy_multisite_network',
            ),
            'bulk_deployment'   => array(
                'name'        => 'Despliegue Masivo',
                'description' => 'Instalación automática en múltiples sitios',
                'method'      => 'deploy_bulk_sites',
            ),
        );
    }

    /**
     * Ejecuta instalación automática completa
     */
    public function auto_install( $profile = 'standard', $options = array() ) {
        if ( ! isset( $this->configuration_profiles[ $profile ] ) ) {
            return array(
                'success' => false,
                'message' => 'Perfil de configuración no válido',
            );
        }

        $config   = $this->configuration_profiles[ $profile ];
        $steps    = $config['steps'];
        $settings = array_merge( $config['settings'], $options );

        $results = array(
            'profile'    => $profile,
            'started_at' => current_time( 'mysql' ),
            'steps'      => array(),
            'success'    => true,
            'total_time' => 0,
        );

        // Registrar inicio de instalación
        $this->log_installation_start( $profile, $settings );

        foreach ( $steps as $step_key ) {
            if ( ! isset( $this->installation_steps[ $step_key ] ) ) {
                continue;
            }

            $step       = $this->installation_steps[ $step_key ];
            $start_time = microtime( true );

            try {
                $method = $step['method'];
                if ( method_exists( $this, $method ) ) {
                    $step_result = $this->$method( $settings );
                } else {
                    $step_result = array(
                        'success' => false,
                        'message' => 'Método no implementado: ' . $method,
                    );
                }

                $execution_time         = microtime( true ) - $start_time;
                $results['total_time'] += $execution_time;

                $results['steps'][ $step_key ] = array(
                    'name'           => $step['name'],
                    'success'        => $step_result['success'],
                    'message'        => $step_result['message'] ?? '',
                    'execution_time' => $execution_time,
                    'data'           => $step_result['data'] ?? array(),
                );

                // Si es un paso crítico y falla, detener instalación
                if ( ! $step_result['success'] && $step['critical'] ) {
                    $results['success']     = false;
                    $results['failed_step'] = $step_key;
                    break;
                }
            } catch ( Exception $e ) {
                $results['success']            = false;
                $results['steps'][ $step_key ] = array(
                    'name'           => $step['name'],
                    'success'        => false,
                    'message'        => 'Error: ' . $e->getMessage(),
                    'execution_time' => microtime( true ) - $start_time,
                );

                if ( $step['critical'] ) {
                    $results['failed_step'] = $step_key;
                    break;
                }
            }
        }

        $results['completed_at'] = current_time( 'mysql' );

        // Registrar resultado de instalación
        $this->log_installation_result( $results );

        return $results;
    }

    /**
     * Valida requisitos del sistema
     */
    private function validate_system_requirements( $settings ) {
        $requirements = array(
            'php_version'         => '7.4',
            'wp_version'          => '5.0',
            'mysql_version'       => '5.6',
            'memory_limit'        => '128M',
            'max_execution_time'  => 30,
            'required_extensions' => array( 'json', 'curl', 'mbstring' ),
        );

        $checks     = array();
        $all_passed = true;

        // Verificar versión PHP
        $php_version           = PHP_VERSION;
        $php_check             = version_compare( $php_version, $requirements['php_version'], '>=' );
        $checks['php_version'] = array(
            'required' => $requirements['php_version'],
            'current'  => $php_version,
            'passed'   => $php_check,
        );
        if ( ! $php_check ) {
            $all_passed = false;
        }

        // Verificar versión WordPress
        global $wp_version;
        $wp_check             = version_compare( $wp_version, $requirements['wp_version'], '>=' );
        $checks['wp_version'] = array(
            'required' => $requirements['wp_version'],
            'current'  => $wp_version,
            'passed'   => $wp_check,
        );
        if ( ! $wp_check ) {
            $all_passed = false;
        }

        // Verificar memoria
        $memory_limit           = ini_get( 'memory_limit' );
        $memory_bytes           = $this->convert_to_bytes( $memory_limit );
        $required_bytes         = $this->convert_to_bytes( $requirements['memory_limit'] );
        $memory_check           = $memory_bytes >= $required_bytes;
        $checks['memory_limit'] = array(
            'required' => $requirements['memory_limit'],
            'current'  => $memory_limit,
            'passed'   => $memory_check,
        );
        if ( ! $memory_check ) {
            $all_passed = false;
        }

        // Verificar extensiones PHP
        foreach ( $requirements['required_extensions'] as $extension ) {
            $extension_check                     = extension_loaded( $extension );
            $checks[ 'extension_' . $extension ] = array(
                'required' => 'Habilitada',
                'current'  => $extension_check ? 'Habilitada' : 'No disponible',
                'passed'   => $extension_check,
            );
            if ( ! $extension_check ) {
                $all_passed = false;
            }
        }

        // Verificar permisos de escritura
        $upload_dir                = wp_upload_dir();
        $writable_check            = is_writable( $upload_dir['basedir'] );
        $checks['upload_writable'] = array(
            'required' => 'Escribible',
            'current'  => $writable_check ? 'Escribible' : 'No escribible',
            'passed'   => $writable_check,
        );
        if ( ! $writable_check ) {
            $all_passed = false;
        }

        return array(
            'success' => $all_passed,
            'message' => $all_passed ? 'Todos los requisitos se cumplen' : 'Algunos requisitos no se cumplen',
            'data'    => array( 'checks' => $checks ),
        );
    }

    /**
     * Crea tablas de base de datos
     */
    private function create_database_tables( $settings ) {
        if ( class_exists( 'WPCW_Installer_Fixed' ) ) {
            $installer = new WPCW_Installer_Fixed();
            $result    = $installer->create_tables();

            return array(
                'success' => $result,
                'message' => $result ? 'Tablas creadas exitosamente' : 'Error al crear tablas',
            );
        }

        return array(
            'success' => false,
            'message' => 'Clase instaladora no disponible',
        );
    }

    /**
     * Detecta y configura región automáticamente
     */
    private function detect_and_configure_region( $settings ) {
        if ( class_exists( 'WPCW_Regional_Detector' ) ) {
            $detector  = WPCW_Regional_Detector::get_instance();
            $detection = $detector->detect_region();

            if ( $detection['confidence'] > 30 ) {
                $auto_config   = WPCW_Auto_Config::get_instance();
                $config_result = $auto_config->apply_country_config( $detection['country'] );

                return array(
                    'success' => $config_result,
                    'message' => sprintf(
                        'Región detectada: %s (Confianza: %d%%)',
                        $detection['country'],
                        $detection['confidence']
                    ),
                    'data'    => $detection,
                );
            }
        }

        return array(
            'success' => false,
            'message' => 'No se pudo detectar la región automáticamente',
        );
    }

    /**
     * Configura valores por defecto
     */
    private function configure_default_settings( $settings ) {
        $default_options = array(
            'wpcw_enable_validation'   => true,
            'wpcw_email_suggestions'   => true,
            'wpcw_whatsapp_validation' => true,
            'wpcw_phone_required'      => true,
            'wpcw_auto_configured'     => true,
            'wpcw_installation_date'   => current_time( 'mysql' ),
            'wpcw_version'             => WPCW_VERSION,
        );

        // Aplicar configuraciones específicas del perfil
        if ( isset( $settings['debug_mode'] ) && $settings['debug_mode'] ) {
            $default_options['wpcw_debug_mode'] = true;
            $default_options['wpcw_log_level']  = 'debug';
        }

        if ( isset( $settings['performance_optimization'] ) ) {
            $default_options['wpcw_performance_mode'] = $settings['performance_optimization'];
        }

        $applied = 0;
        foreach ( $default_options as $option => $value ) {
            if ( update_option( $option, $value ) ) {
                ++$applied;
            }
        }

        return array(
            'success' => true,
            'message' => sprintf( 'Configuradas %d opciones por defecto', $applied ),
            'data'    => array( 'applied_options' => $applied ),
        );
    }

    /**
     * Crea roles de usuario personalizados
     */
    private function create_user_roles( $settings ) {
        $roles_created = 0;

        // Rol para empresas
        $business_caps = array(
            'read'                     => true,
            'wpcw_manage_coupons'      => true,
            'wpcw_view_stats'          => true,
            'wpcw_manage_applications' => true,
        );

        if ( add_role( 'wpcw_business', 'Empresa WPCW', $business_caps ) ) {
            ++$roles_created;
        }

        // Rol para instituciones
        $institution_caps = array(
            'read'                   => true,
            'wpcw_view_coupons'      => true,
            'wpcw_apply_coupons'     => true,
            'wpcw_view_applications' => true,
        );

        if ( add_role( 'wpcw_institution', 'Institución WPCW', $institution_caps ) ) {
            ++$roles_created;
        }

        return array(
            'success' => $roles_created > 0,
            'message' => sprintf( 'Creados %d roles de usuario', $roles_created ),
            'data'    => array( 'roles_created' => $roles_created ),
        );
    }

    /**
     * Configura tareas programadas
     */
    private function setup_cron_jobs( $settings ) {
        $jobs_scheduled = 0;

        // Tarea de limpieza de logs
        if ( ! wp_next_scheduled( 'wpcw_cleanup_logs' ) ) {
            wp_schedule_event( time(), 'daily', 'wpcw_cleanup_logs' );
            ++$jobs_scheduled;
        }

        // Tarea de estadísticas
        if ( ! wp_next_scheduled( 'wpcw_generate_stats' ) ) {
            wp_schedule_event( time(), 'daily', 'wpcw_generate_stats' );
            ++$jobs_scheduled;
        }

        // Tarea de envío de logs (si está habilitado)
        if ( $settings['enable_logging'] && ! wp_next_scheduled( 'wpcw_send_batch_logs' ) ) {
            wp_schedule_event( time(), 'hourly', 'wpcw_send_batch_logs' );
            ++$jobs_scheduled;
        }

        return array(
            'success' => true,
            'message' => sprintf( 'Programadas %d tareas automáticas', $jobs_scheduled ),
            'data'    => array( 'jobs_scheduled' => $jobs_scheduled ),
        );
    }

    /**
     * Crea datos de ejemplo
     */
    private function create_sample_data( $settings ) {
        if ( ! $settings['create_sample_data'] ) {
            return array(
                'success' => true,
                'message' => 'Creación de datos de ejemplo omitida',
            );
        }

        $created_items = array(
            'coupons'      => 0,
            'applications' => 0,
            'pages'        => 0,
        );

        // Crear cupones de ejemplo
        $sample_coupons = array(
            array(
                'title'   => 'Descuento 20% - Restaurante Demo',
                'content' => 'Obtén un 20% de descuento en tu próxima visita a nuestro restaurante.',
                'meta'    => array(
                    'wpcw_discount_percentage' => 20,
                    'wpcw_business_name'       => 'Restaurante Demo',
                    'wpcw_whatsapp_number'     => '+5491123456789',
                ),
            ),
            array(
                'title'   => 'Descuento 15% - Tienda Demo',
                'content' => 'Descuento especial del 15% en toda la tienda.',
                'meta'    => array(
                    'wpcw_discount_percentage' => 15,
                    'wpcw_business_name'       => 'Tienda Demo',
                    'wpcw_whatsapp_number'     => '+5491123456788',
                ),
            ),
        );

        foreach ( $sample_coupons as $coupon_data ) {
            $post_id = wp_insert_post(
                array(
					'post_title'   => $coupon_data['title'],
					'post_content' => $coupon_data['content'],
					'post_type'    => 'wpcw_coupon',
					'post_status'  => 'publish',
                )
            );

            if ( $post_id ) {
                foreach ( $coupon_data['meta'] as $key => $value ) {
                    update_post_meta( $post_id, $key, $value );
                }
                ++$created_items['coupons'];
            }
        }

        return array(
            'success' => true,
            'message' => sprintf(
                'Creados %d cupones de ejemplo',
                $created_items['coupons']
            ),
            'data'    => $created_items,
        );
    }

    /**
     * Optimiza configuraciones de rendimiento
     */
    private function optimize_performance_settings( $settings ) {
        $optimization_level    = $settings['performance_optimization'] ?? 'standard';
        $optimizations_applied = 0;

        switch ( $optimization_level ) {
            case 'basic':
                update_option( 'wpcw_cache_enabled', false );
                update_option( 'wpcw_minify_assets', false );
                $optimizations_applied = 2;
                break;

            case 'standard':
                update_option( 'wpcw_cache_enabled', true );
                update_option( 'wpcw_minify_assets', true );
                update_option( 'wpcw_lazy_load', true );
                $optimizations_applied = 3;
                break;

            case 'advanced':
                update_option( 'wpcw_cache_enabled', true );
                update_option( 'wpcw_minify_assets', true );
                update_option( 'wpcw_lazy_load', true );
                update_option( 'wpcw_cdn_enabled', true );
                update_option( 'wpcw_database_optimization', true );
                $optimizations_applied = 5;
                break;

            case 'debug':
                update_option( 'wpcw_cache_enabled', false );
                update_option( 'wpcw_minify_assets', false );
                update_option( 'wpcw_debug_queries', true );
                $optimizations_applied = 3;
                break;
        }

        return array(
            'success' => true,
            'message' => sprintf(
                'Aplicadas %d optimizaciones de rendimiento (%s)',
                $optimizations_applied,
                $optimization_level
            ),
            'data'    => array(
                'level'         => $optimization_level,
                'optimizations' => $optimizations_applied,
            ),
        );
    }

    /**
     * Configura sistema de logging centralizado
     */
    private function setup_centralized_logging( $settings ) {
        if ( ! $settings['enable_logging'] ) {
            return array(
                'success' => true,
                'message' => 'Sistema de logging omitido',
            );
        }

        $log_settings = array(
            'wpcw_centralized_logging' => true,
            'wpcw_log_to_database'     => true,
            'wpcw_log_to_file'         => true,
            'wpcw_min_log_level'       => $settings['log_level'] ?? 'info',
            'wpcw_log_retention_days'  => 30,
            'wpcw_max_log_files'       => 30,
        );

        if ( isset( $settings['centralized_logging'] ) && $settings['centralized_logging'] ) {
            $log_settings['wpcw_log_to_remote'] = true;
        }

        if ( isset( $settings['remote_monitoring'] ) && $settings['remote_monitoring'] ) {
            $log_settings['wpcw_log_critical_emails'] = true;
        }

        $applied = 0;
        foreach ( $log_settings as $option => $value ) {
            if ( update_option( $option, $value ) ) {
                ++$applied;
            }
        }

        // Crear tabla de logs si no existe
        if ( class_exists( 'WPCW_Centralized_Logger' ) ) {
            $logger = WPCW_Centralized_Logger::get_instance();
            // La tabla se crea automáticamente en el primer log
        }

        return array(
            'success' => true,
            'message' => sprintf( 'Sistema de logging configurado (%d opciones)', $applied ),
            'data'    => array( 'log_options' => $applied ),
        );
    }

    /**
     * Finaliza la instalación
     */
    private function finalize_installation( $settings ) {
        // Marcar instalación como completada
        update_option( 'wpcw_installation_completed', true );
        update_option( 'wpcw_installation_completed_at', current_time( 'mysql' ) );
        update_option( 'wpcw_installation_profile', $settings['profile'] ?? 'standard' );

        // Limpiar caché si existe
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }

        // Generar reporte de instalación
        $report = $this->generate_installation_report();
        update_option( 'wpcw_installation_report', $report );

        return array(
            'success' => true,
            'message' => 'Instalación completada exitosamente',
            'data'    => array( 'report' => $report ),
        );
    }

    /**
     * Genera reporte de instalación
     */
    private function generate_installation_report() {
        return array(
            'version'           => WPCW_VERSION,
            'wordpress_version' => get_bloginfo( 'version' ),
            'php_version'       => PHP_VERSION,
            'site_url'          => get_site_url(),
            'installation_date' => current_time( 'mysql' ),
            'detected_country'  => get_option( 'wpcw_detected_country' ),
            'active_features'   => array(
                'validation'  => get_option( 'wpcw_enable_validation', false ),
                'logging'     => get_option( 'wpcw_centralized_logging', false ),
                'auto_config' => get_option( 'wpcw_auto_configured', false ),
            ),
            'database_tables'   => $this->get_created_tables(),
            'user_roles'        => $this->get_created_roles(),
        );
    }

    /**
     * Obtiene tablas creadas
     */
    private function get_created_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'wpcw_coupons',
            $wpdb->prefix . 'wpcw_redemptions',
            $wpdb->prefix . 'wpcw_applications',
            $wpdb->prefix . 'wpcw_stats',
            $wpdb->prefix . 'wpcw_logs',
        );

        $existing_tables = array();
        foreach ( $tables as $table ) {
            $result = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
            if ( $result ) {
                $existing_tables[] = $table;
            }
        }

        return $existing_tables;
    }

    /**
     * Obtiene roles creados
     */
    private function get_created_roles() {
        global $wp_roles;

        $wpcw_roles     = array();
        $roles_to_check = array( 'wpcw_business', 'wpcw_institution' );

        foreach ( $roles_to_check as $role ) {
            if ( isset( $wp_roles->roles[ $role ] ) ) {
                $wpcw_roles[] = $role;
            }
        }

        return $wpcw_roles;
    }

    /**
     * Instalación automática en activación
     */
    public function auto_install_on_activation() {
        $auto_install_enabled = get_option( 'wpcw_auto_install_on_activation', true );

        if ( $auto_install_enabled && ! get_option( 'wpcw_installation_completed' ) ) {
            $profile = get_option( 'wpcw_default_install_profile', 'standard' );
            $this->auto_install( $profile );
        }
    }

    /**
     * Métodos auxiliares
     */
    private function convert_to_bytes( $value ) {
        $value = trim( $value );
        $last  = strtolower( $value[ strlen( $value ) - 1 ] );
        $value = (int) $value;

        switch ( $last ) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    private function log_installation_start( $profile, $settings ) {
        if ( function_exists( 'wpcw_log' ) ) {
            wpcw_log(
                'info',
                'Iniciando instalación automática',
                array(
					'profile'  => $profile,
					'settings' => $settings,
                )
            );
        }
    }

    private function log_installation_result( $results ) {
        if ( function_exists( 'wpcw_log' ) ) {
            $level   = $results['success'] ? 'info' : 'error';
            $message = $results['success'] ? 'Instalación completada exitosamente' : 'Instalación falló';

            wpcw_log(
                $level,
                $message,
                array(
					'profile'         => $results['profile'],
					'total_time'      => $results['total_time'],
					'steps_completed' => count( $results['steps'] ),
					'failed_step'     => $results['failed_step'] ?? null,
                )
            );
        }
    }

    /**
     * AJAX Handlers
     */
    public function ajax_auto_install() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $profile = sanitize_text_field( $_POST['profile'] ?? 'standard' );
        $options = $_POST['options'] ?? array();

        $result = $this->auto_install( $profile, $options );

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    public function ajax_validate_requirements() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        $result = $this->validate_system_requirements( array() );

        wp_send_json_success( $result );
    }

    public function ajax_get_install_status() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        $status = array(
            'completed'    => get_option( 'wpcw_installation_completed', false ),
            'completed_at' => get_option( 'wpcw_installation_completed_at' ),
            'profile'      => get_option( 'wpcw_installation_profile' ),
            'report'       => get_option( 'wpcw_installation_report' ),
        );

        wp_send_json_success( $status );
    }

    public function ajax_bulk_install() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $sites   = $_POST['sites'] ?? array();
        $profile = sanitize_text_field( $_POST['profile'] ?? 'standard' );

        $results = array();

        foreach ( $sites as $site_data ) {
            // En un entorno real, esto requeriría API o acceso remoto
            $results[] = array(
                'site'    => $site_data,
                'success' => false,
                'message' => 'Instalación masiva requiere configuración adicional',
            );
        }

        wp_send_json_success( $results );
    }
}

// Inicializar la clase
WPCW_Auto_Installer::get_instance();
