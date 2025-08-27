<?php
/**
 * Sistema de optimización de rendimiento para despliegue masivo
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Performance_Optimizer {

    private static $instance       = null;
    private $optimization_profiles = array();
    private $performance_metrics   = array();
    private $cache_handlers        = array();

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_optimization_profiles();
        $this->init_cache_handlers();

        add_action( 'init', array( $this, 'init_performance_monitoring' ) );
        add_action( 'wp_ajax_wpcw_optimize_performance', array( $this, 'ajax_optimize_performance' ) );
        add_action( 'wp_ajax_wpcw_get_performance_metrics', array( $this, 'ajax_get_performance_metrics' ) );
        add_action( 'wp_ajax_wpcw_clear_cache', array( $this, 'ajax_clear_cache' ) );

        // Hooks para optimización automática
        add_action( 'wpcw_after_coupon_save', array( $this, 'invalidate_coupon_cache' ) );
        add_action( 'wpcw_after_application_submit', array( $this, 'optimize_database_queries' ) );

        // Optimizaciones automáticas en carga
        add_action( 'wp_loaded', array( $this, 'auto_optimize_on_load' ) );
    }

    /**
     * Inicializa perfiles de optimización
     */
    private function init_optimization_profiles() {
        $this->optimization_profiles = array(
            'basic'       => array(
                'name'                 => 'Optimización Básica',
                'description'          => 'Optimizaciones mínimas para sitios pequeños',
                'settings'             => array(
                    'enable_query_cache'    => true,
                    'enable_object_cache'   => false,
                    'enable_page_cache'     => false,
                    'minify_css'            => false,
                    'minify_js'             => false,
                    'optimize_images'       => false,
                    'lazy_load'             => false,
                    'database_optimization' => false,
                    'cdn_integration'       => false,
                ),
                'expected_improvement' => '10-20%',
            ),
            'standard'    => array(
                'name'                 => 'Optimización Estándar',
                'description'          => 'Configuración balanceada para la mayoría de sitios',
                'settings'             => array(
                    'enable_query_cache'    => true,
                    'enable_object_cache'   => true,
                    'enable_page_cache'     => true,
                    'minify_css'            => true,
                    'minify_js'             => true,
                    'optimize_images'       => true,
                    'lazy_load'             => true,
                    'database_optimization' => true,
                    'cdn_integration'       => false,
                ),
                'expected_improvement' => '30-50%',
            ),
            'aggressive'  => array(
                'name'                 => 'Optimización Agresiva',
                'description'          => 'Máximo rendimiento para sitios de alto tráfico',
                'settings'             => array(
                    'enable_query_cache'         => true,
                    'enable_object_cache'        => true,
                    'enable_page_cache'          => true,
                    'minify_css'                 => true,
                    'minify_js'                  => true,
                    'optimize_images'            => true,
                    'lazy_load'                  => true,
                    'database_optimization'      => true,
                    'cdn_integration'            => true,
                    'preload_critical_resources' => true,
                    'compress_responses'         => true,
                    'optimize_database_tables'   => true,
                ),
                'expected_improvement' => '50-80%',
            ),
            'development' => array(
                'name'                 => 'Modo Desarrollo',
                'description'          => 'Sin caché para desarrollo y depuración',
                'settings'             => array(
                    'enable_query_cache'       => false,
                    'enable_object_cache'      => false,
                    'enable_page_cache'        => false,
                    'minify_css'               => false,
                    'minify_js'                => false,
                    'optimize_images'          => false,
                    'lazy_load'                => false,
                    'database_optimization'    => false,
                    'cdn_integration'          => false,
                    'debug_queries'            => true,
                    'show_performance_metrics' => true,
                ),
                'expected_improvement' => 'N/A (Modo debug)',
            ),
        );
    }

    /**
     * Inicializa manejadores de caché
     */
    private function init_cache_handlers() {
        $this->cache_handlers = array(
            'query_cache'    => array(
                'name'    => 'Caché de Consultas',
                'class'   => 'WPCW_Query_Cache',
                'enabled' => false,
            ),
            'object_cache'   => array(
                'name'    => 'Caché de Objetos',
                'class'   => 'WPCW_Object_Cache',
                'enabled' => false,
            ),
            'page_cache'     => array(
                'name'    => 'Caché de Páginas',
                'class'   => 'WPCW_Page_Cache',
                'enabled' => false,
            ),
            'fragment_cache' => array(
                'name'    => 'Caché de Fragmentos',
                'class'   => 'WPCW_Fragment_Cache',
                'enabled' => false,
            ),
        );
    }

    /**
     * Aplica perfil de optimización
     */
    public function apply_optimization_profile( $profile_name ) {
        if ( ! isset( $this->optimization_profiles[ $profile_name ] ) ) {
            return array(
                'success' => false,
                'message' => 'Perfil de optimización no válido',
            );
        }

        $profile               = $this->optimization_profiles[ $profile_name ];
        $settings              = $profile['settings'];
        $applied_optimizations = array();

        // Aplicar configuraciones de caché
        if ( $settings['enable_query_cache'] ) {
            $this->enable_query_cache();
            $applied_optimizations[] = 'Query Cache';
        }

        if ( $settings['enable_object_cache'] ) {
            $this->enable_object_cache();
            $applied_optimizations[] = 'Object Cache';
        }

        if ( $settings['enable_page_cache'] ) {
            $this->enable_page_cache();
            $applied_optimizations[] = 'Page Cache';
        }

        // Aplicar minificación
        if ( $settings['minify_css'] ) {
            $this->enable_css_minification();
            $applied_optimizations[] = 'CSS Minification';
        }

        if ( $settings['minify_js'] ) {
            $this->enable_js_minification();
            $applied_optimizations[] = 'JS Minification';
        }

        // Aplicar optimización de imágenes
        if ( $settings['optimize_images'] ) {
            $this->enable_image_optimization();
            $applied_optimizations[] = 'Image Optimization';
        }

        // Aplicar lazy loading
        if ( $settings['lazy_load'] ) {
            $this->enable_lazy_loading();
            $applied_optimizations[] = 'Lazy Loading';
        }

        // Aplicar optimización de base de datos
        if ( $settings['database_optimization'] ) {
            $this->optimize_database();
            $applied_optimizations[] = 'Database Optimization';
        }

        // Aplicar CDN si está configurado
        if ( $settings['cdn_integration'] ) {
            $this->enable_cdn_integration();
            $applied_optimizations[] = 'CDN Integration';
        }

        // Configuraciones adicionales para perfil agresivo
        if ( isset( $settings['preload_critical_resources'] ) && $settings['preload_critical_resources'] ) {
            $this->preload_critical_resources();
            $applied_optimizations[] = 'Resource Preloading';
        }

        if ( isset( $settings['compress_responses'] ) && $settings['compress_responses'] ) {
            $this->enable_response_compression();
            $applied_optimizations[] = 'Response Compression';
        }

        // Configuraciones para desarrollo
        if ( isset( $settings['debug_queries'] ) && $settings['debug_queries'] ) {
            $this->enable_query_debugging();
            $applied_optimizations[] = 'Query Debugging';
        }

        // Guardar configuración aplicada
        update_option( 'wpcw_optimization_profile', $profile_name );
        update_option( 'wpcw_optimization_applied', $applied_optimizations );
        update_option( 'wpcw_optimization_applied_at', current_time( 'mysql' ) );

        return array(
            'success' => true,
            'message' => sprintf(
                'Perfil "%s" aplicado exitosamente. %d optimizaciones activadas.',
                $profile['name'],
                count( $applied_optimizations )
            ),
            'data'    => array(
                'profile'              => $profile_name,
                'optimizations'        => $applied_optimizations,
                'expected_improvement' => $profile['expected_improvement'],
            ),
        );
    }

    /**
     * Habilita caché de consultas
     */
    private function enable_query_cache() {
        update_option( 'wpcw_query_cache_enabled', true );
        update_option( 'wpcw_query_cache_ttl', 3600 ); // 1 hora
        update_option( 'wpcw_query_cache_max_size', 100 ); // 100 consultas

        // Crear tabla de caché si no existe
        $this->create_cache_table( 'query_cache' );

        $this->cache_handlers['query_cache']['enabled'] = true;
    }

    /**
     * Habilita caché de objetos
     */
    private function enable_object_cache() {
        update_option( 'wpcw_object_cache_enabled', true );
        update_option( 'wpcw_object_cache_ttl', 1800 ); // 30 minutos

        // Usar caché de WordPress si está disponible
        if ( function_exists( 'wp_cache_set' ) ) {
            update_option( 'wpcw_use_wp_object_cache', true );
        } else {
            // Usar caché interno
            $this->create_cache_table( 'object_cache' );
        }

        $this->cache_handlers['object_cache']['enabled'] = true;
    }

    /**
     * Habilita caché de páginas
     */
    private function enable_page_cache() {
        update_option( 'wpcw_page_cache_enabled', true );
        update_option( 'wpcw_page_cache_ttl', 7200 ); // 2 horas
        update_option( 'wpcw_page_cache_exclude_logged_in', true );

        // Crear directorio de caché
        $cache_dir = WP_CONTENT_DIR . '/cache/wpcw-pages/';
        if ( ! file_exists( $cache_dir ) ) {
            wp_mkdir_p( $cache_dir );
        }

        $this->cache_handlers['page_cache']['enabled'] = true;
    }

    /**
     * Habilita minificación CSS
     */
    private function enable_css_minification() {
        update_option( 'wpcw_css_minification_enabled', true );
        update_option( 'wpcw_css_combine_files', true );

        // Crear directorio para archivos minificados
        $minified_dir = WP_CONTENT_DIR . '/cache/wpcw-minified/css/';
        if ( ! file_exists( $minified_dir ) ) {
            wp_mkdir_p( $minified_dir );
        }

        // Hook para procesar CSS
        add_action( 'wp_print_styles', array( $this, 'process_css_files' ), 999 );
    }

    /**
     * Habilita minificación JavaScript
     */
    private function enable_js_minification() {
        update_option( 'wpcw_js_minification_enabled', true );
        update_option( 'wpcw_js_combine_files', true );
        update_option( 'wpcw_js_defer_loading', true );

        // Crear directorio para archivos minificados
        $minified_dir = WP_CONTENT_DIR . '/cache/wpcw-minified/js/';
        if ( ! file_exists( $minified_dir ) ) {
            wp_mkdir_p( $minified_dir );
        }

        // Hook para procesar JS
        add_action( 'wp_print_scripts', array( $this, 'process_js_files' ), 999 );
    }

    /**
     * Habilita optimización de imágenes
     */
    private function enable_image_optimization() {
        update_option( 'wpcw_image_optimization_enabled', true );
        update_option( 'wpcw_image_quality', 85 );
        update_option( 'wpcw_image_webp_conversion', true );
        update_option( 'wpcw_image_progressive_jpeg', true );

        // Hook para procesar imágenes subidas
        add_filter( 'wp_handle_upload', array( $this, 'optimize_uploaded_image' ) );
    }

    /**
     * Habilita lazy loading
     */
    private function enable_lazy_loading() {
        update_option( 'wpcw_lazy_loading_enabled', true );
        update_option( 'wpcw_lazy_loading_threshold', 200 ); // 200px antes de cargar

        // Hook para modificar contenido
        add_filter( 'the_content', array( $this, 'add_lazy_loading_to_images' ) );
        add_filter( 'post_thumbnail_html', array( $this, 'add_lazy_loading_to_thumbnails' ) );
    }

    /**
     * Optimiza base de datos
     */
    private function optimize_database() {
        global $wpdb;

        update_option( 'wpcw_database_optimization_enabled', true );

        // Crear índices para mejorar rendimiento
        $indexes = array(
            $wpdb->prefix . 'wpcw_coupons'      => array(
                'idx_status'       => 'status',
                'idx_created_date' => 'created_at',
                'idx_business_id'  => 'business_id',
            ),
            $wpdb->prefix . 'wpcw_applications' => array(
                'idx_coupon_status' => 'coupon_id, status',
                'idx_user_date'     => 'user_email, created_at',
                'idx_status_date'   => 'status, created_at',
            ),
            $wpdb->prefix . 'wpcw_redemptions'  => array(
                'idx_coupon_date' => 'coupon_id, redeemed_at',
                'idx_user_date'   => 'user_email, redeemed_at',
            ),
        );

        foreach ( $indexes as $table => $table_indexes ) {
            foreach ( $table_indexes as $index_name => $columns ) {
                $sql = "CREATE INDEX {$index_name} ON {$table} ({$columns})";
                $wpdb->query( $sql );
            }
        }

        // Programar limpieza automática
        if ( ! wp_next_scheduled( 'wpcw_database_cleanup' ) ) {
            wp_schedule_event( time(), 'weekly', 'wpcw_database_cleanup' );
        }
    }

    /**
     * Habilita integración CDN
     */
    private function enable_cdn_integration() {
        update_option( 'wpcw_cdn_enabled', true );

        // Configuración CDN por defecto (se puede personalizar)
        $cdn_settings = array(
            'cdn_url'           => '',
            'cdn_includes'      => array( 'css', 'js', 'images' ),
            'cdn_exclude_admin' => true,
        );

        update_option( 'wpcw_cdn_settings', $cdn_settings );

        // Hook para reemplazar URLs
        add_filter( 'wp_get_attachment_url', array( $this, 'replace_with_cdn_url' ) );
        add_filter( 'stylesheet_uri', array( $this, 'replace_with_cdn_url' ) );
        add_filter( 'script_loader_src', array( $this, 'replace_with_cdn_url' ) );
    }

    /**
     * Precarga recursos críticos
     */
    private function preload_critical_resources() {
        update_option( 'wpcw_preload_enabled', true );

        // Recursos críticos a precargar
        $critical_resources = array(
            'css'   => array(
                plugins_url( 'admin/css/wpcw-admin.css', WPCW_PLUGIN_FILE ),
                plugins_url( 'public/css/wpcw-public.css', WPCW_PLUGIN_FILE ),
            ),
            'js'    => array(
                plugins_url( 'admin/js/validation-enhanced.js', WPCW_PLUGIN_FILE ),
            ),
            'fonts' => array(
                // Fuentes críticas si las hay
            ),
        );

        update_option( 'wpcw_critical_resources', $critical_resources );

        // Hook para añadir preload headers
        add_action( 'wp_head', array( $this, 'add_preload_headers' ), 1 );
    }

    /**
     * Habilita compresión de respuestas
     */
    private function enable_response_compression() {
        update_option( 'wpcw_compression_enabled', true );

        // Habilitar compresión gzip si no está activa
        if ( ! ob_get_level() && extension_loaded( 'zlib' ) ) {
            add_action(
                'init',
                function () {
                    if ( ! headers_sent() ) {
                        ob_start( 'ob_gzhandler' );
                    }
                }
            );
        }

        // Configurar headers de compresión
        add_action( 'send_headers', array( $this, 'add_compression_headers' ) );
    }

    /**
     * Habilita depuración de consultas
     */
    private function enable_query_debugging() {
        update_option( 'wpcw_query_debug_enabled', true );

        if ( ! defined( 'SAVEQUERIES' ) ) {
            define( 'SAVEQUERIES', true );
        }

        // Hook para mostrar estadísticas de consultas
        add_action( 'wp_footer', array( $this, 'show_query_debug_info' ) );
    }

    /**
     * Monitoreo de rendimiento
     */
    public function init_performance_monitoring() {
        if ( get_option( 'wpcw_performance_monitoring_enabled', false ) ) {
            add_action( 'wp_loaded', array( $this, 'start_performance_timer' ) );
            add_action( 'wp_footer', array( $this, 'end_performance_timer' ) );
        }
    }

    public function start_performance_timer() {
        $this->performance_metrics['start_time']   = microtime( true );
        $this->performance_metrics['start_memory'] = memory_get_usage();
    }

    public function end_performance_timer() {
        $this->performance_metrics['end_time']       = microtime( true );
        $this->performance_metrics['end_memory']     = memory_get_usage();
        $this->performance_metrics['execution_time'] = $this->performance_metrics['end_time'] - $this->performance_metrics['start_time'];
        $this->performance_metrics['memory_usage']   = $this->performance_metrics['end_memory'] - $this->performance_metrics['start_memory'];

        // Guardar métricas
        $this->save_performance_metrics();
    }

    /**
     * Guarda métricas de rendimiento
     */
    private function save_performance_metrics() {
        global $wpdb;

        $metrics = array(
            'url'            => $_SERVER['REQUEST_URI'] ?? '',
            'execution_time' => $this->performance_metrics['execution_time'],
            'memory_usage'   => $this->performance_metrics['memory_usage'],
            'query_count'    => get_num_queries(),
            'timestamp'      => current_time( 'mysql' ),
            'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
        );

        // Crear tabla de métricas si no existe
        $this->create_metrics_table();

        $wpdb->insert(
            $wpdb->prefix . 'wpcw_performance_metrics',
            $metrics
        );
    }

    /**
     * Obtiene estadísticas de rendimiento
     */
    public function get_performance_stats( $days = 7 ) {
        global $wpdb;

        $table      = $wpdb->prefix . 'wpcw_performance_metrics';
        $date_limit = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $stats = $wpdb->get_row(
            "
            SELECT 
                COUNT(*) as total_requests,
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                MIN(execution_time) as min_execution_time,
                AVG(memory_usage) as avg_memory_usage,
                MAX(memory_usage) as max_memory_usage,
                AVG(query_count) as avg_query_count
            FROM {$table}
            WHERE timestamp >= '{$date_limit}'
        "
        );

        return $stats;
    }

    /**
     * Limpia caché
     */
    public function clear_all_cache() {
        $cleared = array();

        // Limpiar caché de consultas
        if ( $this->cache_handlers['query_cache']['enabled'] ) {
            $this->clear_query_cache();
            $cleared[] = 'Query Cache';
        }

        // Limpiar caché de objetos
        if ( $this->cache_handlers['object_cache']['enabled'] ) {
            $this->clear_object_cache();
            $cleared[] = 'Object Cache';
        }

        // Limpiar caché de páginas
        if ( $this->cache_handlers['page_cache']['enabled'] ) {
            $this->clear_page_cache();
            $cleared[] = 'Page Cache';
        }

        // Limpiar archivos minificados
        $this->clear_minified_files();
        $cleared[] = 'Minified Files';

        // Limpiar caché de WordPress
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
            $cleared[] = 'WordPress Cache';
        }

        return array(
            'success' => true,
            'message' => 'Caché limpiado exitosamente',
            'data'    => array( 'cleared' => $cleared ),
        );
    }

    /**
     * Métodos de limpieza de caché específicos
     */
    private function clear_query_cache() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpcw_query_cache" );
    }

    private function clear_object_cache() {
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        } else {
            global $wpdb;
            $wpdb->query( "DELETE FROM {$wpdb->prefix}wpcw_object_cache" );
        }
    }

    private function clear_page_cache() {
        $cache_dir = WP_CONTENT_DIR . '/cache/wpcw-pages/';
        if ( is_dir( $cache_dir ) ) {
            $this->delete_directory_contents( $cache_dir );
        }
    }

    private function clear_minified_files() {
        $minified_dirs = array(
            WP_CONTENT_DIR . '/cache/wpcw-minified/css/',
            WP_CONTENT_DIR . '/cache/wpcw-minified/js/',
        );

        foreach ( $minified_dirs as $dir ) {
            if ( is_dir( $dir ) ) {
                $this->delete_directory_contents( $dir );
            }
        }
    }

    /**
     * Optimización automática en carga
     */
    public function auto_optimize_on_load() {
        $auto_optimize = get_option( 'wpcw_auto_optimize_enabled', false );

        if ( $auto_optimize ) {
            // Optimizaciones ligeras que se pueden ejecutar en cada carga
            $this->optimize_current_request();
        }
    }

    private function optimize_current_request() {
        // Remover query strings innecesarios
        add_filter( 'script_loader_src', array( $this, 'remove_query_strings' ) );
        add_filter( 'style_loader_src', array( $this, 'remove_query_strings' ) );

        // Optimizar consultas de base de datos
        add_filter( 'posts_request', array( $this, 'optimize_posts_query' ) );
    }

    /**
     * Métodos auxiliares
     */
    private function create_cache_table( $cache_type ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_' . $cache_type;

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_value longtext NOT NULL,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key (cache_key),
            KEY expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private function create_metrics_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_performance_metrics';

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url varchar(500) NOT NULL,
            execution_time decimal(10,6) NOT NULL,
            memory_usage bigint(20) NOT NULL,
            query_count int(11) NOT NULL,
            timestamp datetime NOT NULL,
            user_agent text,
            PRIMARY KEY (id),
            KEY timestamp (timestamp),
            KEY url (url(255))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private function delete_directory_contents( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $files = glob( $dir . '*', GLOB_MARK );
        foreach ( $files as $file ) {
            if ( is_dir( $file ) ) {
                $this->delete_directory_contents( $file );
                rmdir( $file );
            } else {
                unlink( $file );
            }
        }
    }

    public function remove_query_strings( $src ) {
        if ( strpos( $src, '?ver=' ) ) {
            $src = remove_query_arg( 'ver', $src );
        }
        return $src;
    }

    public function optimize_posts_query( $query ) {
        // Optimizaciones específicas para consultas de posts
        if ( strpos( $query, 'wpcw_' ) !== false ) {
            // Añadir LIMIT si no existe para evitar consultas masivas
            if ( strpos( $query, 'LIMIT' ) === false && strpos( $query, 'SELECT' ) === 0 ) {
                $query .= ' LIMIT 100';
            }
        }
        return $query;
    }

    /**
     * AJAX Handlers
     */
    public function ajax_optimize_performance() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $profile = sanitize_text_field( $_POST['profile'] ?? 'standard' );
        $result  = $this->apply_optimization_profile( $profile );

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    public function ajax_get_performance_metrics() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        $days  = intval( $_POST['days'] ?? 7 );
        $stats = $this->get_performance_stats( $days );

        wp_send_json_success(
            array(
				'stats'           => $stats,
				'current_profile' => get_option( 'wpcw_optimization_profile', 'none' ),
				'cache_status'    => array(
					'query_cache'  => $this->cache_handlers['query_cache']['enabled'],
					'object_cache' => $this->cache_handlers['object_cache']['enabled'],
					'page_cache'   => $this->cache_handlers['page_cache']['enabled'],
				),
            )
        );
    }

    public function ajax_clear_cache() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $result = $this->clear_all_cache();

        wp_send_json_success( $result );
    }
}

// Inicializar la clase
WPCW_Performance_Optimizer::get_instance();
