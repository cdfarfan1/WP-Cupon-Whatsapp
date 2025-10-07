<?php
/**
 * WP Cupón WhatsApp - Dashboard Class
 *
 * Handles dashboard metrics, charts, and notifications
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Dashboard class
 */
class WPCW_Dashboard {

    /**
     * Get dashboard metrics
     *
     * @return array Dashboard metrics
     */
    public static function get_metrics() {
        global $wpdb;

        $metrics = array();

        // Applications metrics
        $applications_pending = wp_count_posts( 'wpcw_application' )->pending ?? 0;
        $applications_total = wp_count_posts( 'wpcw_application' )->publish ?? 0;

        $metrics['applications'] = array(
            'pending' => $applications_pending,
            'total' => $applications_total,
            'approved' => $applications_total - $applications_pending,
        );

        // Businesses metrics
        $businesses_total = wp_count_posts( 'wpcw_business' )->publish ?? 0;
        $businesses_draft = wp_count_posts( 'wpcw_business' )->draft ?? 0;

        $metrics['businesses'] = array(
            'total' => $businesses_total,
            'active' => $businesses_total,
            'inactive' => $businesses_draft,
        );

        // Institutions metrics
        $institutions_total = wp_count_posts( 'wpcw_institution' )->publish ?? 0;

        $metrics['institutions'] = array(
            'total' => $institutions_total,
        );

        // Coupons metrics
        $coupons_total = wp_count_posts( 'shop_coupon' )->publish ?? 0;
        $coupons_draft = wp_count_posts( 'shop_coupon' )->draft ?? 0;

        // Count WPCW enabled coupons
        $wpcw_coupons = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'shop_coupon'
                AND p.post_status = 'publish'
                AND pm.meta_key = '_wpcw_enabled'
                AND pm.meta_value = 'yes'"
            )
        );

        $metrics['coupons'] = array(
            'total' => $coupons_total,
            'wpcw_enabled' => $wpcw_coupons ?: 0,
            'inactive' => $coupons_draft,
        );

        // Redemptions metrics
        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) == $redemptions_table ) {
            $redemptions_total = $wpdb->get_var( "SELECT COUNT(*) FROM $redemptions_table" );
            $redemptions_pending = $wpdb->get_var( "SELECT COUNT(*) FROM $redemptions_table WHERE estado_canje = 'pendiente_confirmacion'" );
            $redemptions_confirmed = $wpdb->get_var( "SELECT COUNT(*) FROM $redemptions_table WHERE estado_canje = 'confirmado_por_negocio'" );
            $redemptions_used = $wpdb->get_var( "SELECT COUNT(*) FROM $redemptions_table WHERE estado_canje = 'utilizado_en_pedido_wc'" );

            $metrics['redemptions'] = array(
                'total' => $redemptions_total ?: 0,
                'pending' => $redemptions_pending ?: 0,
                'confirmed' => $redemptions_confirmed ?: 0,
                'used' => $redemptions_used ?: 0,
            );
        } else {
            $metrics['redemptions'] = array(
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'used' => 0,
            );
        }

        // Users metrics
        $users_total = count_users()['total_users'] ?? 0;

        // Count users with WhatsApp
        $users_with_whatsapp = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta}
            WHERE meta_key = '_wpcw_whatsapp_number'
            AND meta_value != ''"
        );

        // Count users with institution membership
        $users_with_institution = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta}
            WHERE meta_key = '_wpcw_user_institution_id'
            AND meta_value != ''"
        );

        $metrics['users'] = array(
            'total' => $users_total,
            'with_whatsapp' => $users_with_whatsapp ?: 0,
            'with_institution' => $users_with_institution ?: 0,
        );

        return $metrics;
    }

    /**
     * Get chart data for the last 30 days
     *
     * @return array Chart data
     */
    public static function get_chart_data() {
        global $wpdb;

        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) != $redemptions_table ) {
            return array(
                'labels' => array(),
                'datasets' => array(),
            );
        }

        // Get data for last 30 days
        $data = array();
        for ( $i = 29; $i >= 0; $i-- ) {
            $date = date( 'Y-m-d', strtotime( "-{$i} days" ) );
            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $redemptions_table
                    WHERE DATE(fecha_solicitud_canje) = %s",
                    $date
                )
            );

            $data[] = array(
                'date' => $date,
                'count' => $count ?: 0,
            );
        }

        $labels = array_map( function( $item ) {
            return date( 'M j', strtotime( $item['date'] ) );
        }, $data );

        $values = array_map( function( $item ) {
            return $item['count'];
        }, $data );

        return array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => __( 'Canjes Diarios', 'wp-cupon-whatsapp' ),
                    'data' => $values,
                    'borderColor' => '#007cba',
                    'backgroundColor' => 'rgba(0, 124, 186, 0.1)',
                    'fill' => true,
                ),
            ),
        );
    }

    /**
     * Get recent notifications
     *
     * @return array Recent notifications
     */
    public static function get_recent_notifications() {
        global $wpdb;

        $notifications = array();

        // Recent pending applications
        $recent_applications = get_posts( array(
            'post_type' => 'wpcw_application',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
        ) );

        foreach ( $recent_applications as $application ) {
            $notifications[] = array(
                'type' => 'application',
                'title' => sprintf( __( 'Nueva solicitud: %s', 'wp-cupon-whatsapp' ), $application->post_title ),
                'message' => __( 'Una nueva solicitud de adhesión requiere revisión.', 'wp-cupon-whatsapp' ),
                'url' => get_edit_post_link( $application->ID ),
                'time' => $application->post_date,
                'icon' => '📝',
            );
        }

        // Recent redemptions
        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) == $redemptions_table ) {
            $recent_redemptions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $redemptions_table
                    WHERE estado_canje = 'pendiente_confirmacion'
                    ORDER BY fecha_solicitud_canje DESC
                    LIMIT 3"
                )
            );

            foreach ( $recent_redemptions as $redemption ) {
                $user = get_user_by( 'id', $redemption->user_id );
                $coupon = get_post( $redemption->coupon_id );

                if ( $user && $coupon ) {
                    $notifications[] = array(
                        'type' => 'redemption',
                        'title' => sprintf( __( 'Canje pendiente: %s', 'wp-cupon-whatsapp' ), $coupon->post_title ),
                        'message' => sprintf( __( 'Usuario %s solicita canjear cupón.', 'wp-cupon-whatsapp' ), $user->display_name ),
                        'url' => admin_url( 'admin.php?page=wpcw-canjes&action=view&id=' . $redemption->id ),
                        'time' => $redemption->fecha_solicitud_canje,
                        'icon' => '🎫',
                    );
                }
            }
        }

        // Sort by time (most recent first)
        usort( $notifications, function( $a, $b ) {
            return strtotime( $b['time'] ) - strtotime( $a['time'] );
        } );

        return array_slice( $notifications, 0, 5 );
    }

    /**
     * Get system health status
     *
     * @return array Health status
     */
    public static function get_system_health() {
        $health = array(
            'status' => 'good',
            'issues' => array(),
            'warnings' => array(),
        );

        // Check WooCommerce
        if ( ! class_exists( 'WooCommerce' ) ) {
            $health['issues'][] = __( 'WooCommerce no está instalado o activado', 'wp-cupon-whatsapp' );
            $health['status'] = 'critical';
        }

        // Check database tables
        global $wpdb;
        $tables = array(
            $wpdb->prefix . 'wpcw_canjes',
            $wpdb->prefix . 'wpcw_logs',
        );

        foreach ( $tables as $table ) {
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
                $health['issues'][] = sprintf( __( 'Tabla %s no existe', 'wp-cupon-whatsapp' ), $table );
                $health['status'] = 'critical';
            }
        }

        // Check PHP version
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            $health['warnings'][] = __( 'Versión de PHP inferior a 7.4 recomendada', 'wp-cupon-whatsapp' );
            if ( $health['status'] === 'good' ) {
                $health['status'] = 'warning';
            }
        }

        // Check memory limit
        $memory_limit = ini_get( 'memory_limit' );
        if ( $memory_limit && wp_convert_hr_to_bytes( $memory_limit ) < 128 * 1024 * 1024 ) {
            $health['warnings'][] = __( 'Límite de memoria inferior a 128MB recomendado', 'wp-cupon-whatsapp' );
            if ( $health['status'] === 'good' ) {
                $health['status'] = 'warning';
            }
        }

        return $health;
    }

    /**
     * Render dashboard metrics cards
     */
    public static function render_metrics_cards() {
        $metrics = self::get_metrics();

        ?>
        <div class="wpcw-metrics-grid">
            <!-- Applications Card -->
            <div class="wpcw-metric-card">
                <div class="wpcw-metric-header">
                    <span class="wpcw-metric-icon">📝</span>
                    <h3><?php _e( 'Solicitudes', 'wp-cupon-whatsapp' ); ?></h3>
                </div>
                <div class="wpcw-metric-value"><?php echo number_format( $metrics['applications']['pending'] ); ?></div>
                <div class="wpcw-metric-subtitle"><?php _e( 'Pendientes', 'wp-cupon-whatsapp' ); ?></div>
                <div class="wpcw-metric-details">
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['applications']['total'] ); ?> <?php _e( 'Total', 'wp-cupon-whatsapp' ); ?></span>
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['applications']['approved'] ); ?> <?php _e( 'Aprobadas', 'wp-cupon-whatsapp' ); ?></span>
                </div>
            </div>

            <!-- Businesses Card -->
            <div class="wpcw-metric-card">
                <div class="wpcw-metric-header">
                    <span class="wpcw-metric-icon">🏪</span>
                    <h3><?php _e( 'Comercios', 'wp-cupon-whatsapp' ); ?></h3>
                </div>
                <div class="wpcw-metric-value"><?php echo number_format( $metrics['businesses']['total'] ); ?></div>
                <div class="wpcw-metric-subtitle"><?php _e( 'Activos', 'wp-cupon-whatsapp' ); ?></div>
                <div class="wpcw-metric-details">
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['businesses']['inactive'] ); ?> <?php _e( 'Inactivos', 'wp-cupon-whatsapp' ); ?></span>
                </div>
            </div>

            <!-- Coupons Card -->
            <div class="wpcw-metric-card">
                <div class="wpcw-metric-header">
                    <span class="wpcw-metric-icon">🎫</span>
                    <h3><?php _e( 'Cupones', 'wp-cupon-whatsapp' ); ?></h3>
                </div>
                <div class="wpcw-metric-value"><?php echo number_format( $metrics['coupons']['wpcw_enabled'] ); ?></div>
                <div class="wpcw-metric-subtitle"><?php _e( 'WPCW Habilitados', 'wp-cupon-whatsapp' ); ?></div>
                <div class="wpcw-metric-details">
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['coupons']['total'] ); ?> <?php _e( 'Total', 'wp-cupon-whatsapp' ); ?></span>
                </div>
            </div>

            <!-- Redemptions Card -->
            <div class="wpcw-metric-card">
                <div class="wpcw-metric-header">
                    <span class="wpcw-metric-icon">✅</span>
                    <h3><?php _e( 'Canjes', 'wp-cupon-whatsapp' ); ?></h3>
                </div>
                <div class="wpcw-metric-value"><?php echo number_format( $metrics['redemptions']['confirmed'] ); ?></div>
                <div class="wpcw-metric-subtitle"><?php _e( 'Confirmados', 'wp-cupon-whatsapp' ); ?></div>
                <div class="wpcw-metric-details">
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['redemptions']['pending'] ); ?> <?php _e( 'Pendientes', 'wp-cupon-whatsapp' ); ?></span>
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['redemptions']['used'] ); ?> <?php _e( 'Utilizados', 'wp-cupon-whatsapp' ); ?></span>
                </div>
            </div>

            <!-- Users Card -->
            <div class="wpcw-metric-card">
                <div class="wpcw-metric-header">
                    <span class="wpcw-metric-icon">👥</span>
                    <h3><?php _e( 'Usuarios', 'wp-cupon-whatsapp' ); ?></h3>
                </div>
                <div class="wpcw-metric-value"><?php echo number_format( $metrics['users']['with_whatsapp'] ); ?></div>
                <div class="wpcw-metric-subtitle"><?php _e( 'Con WhatsApp', 'wp-cupon-whatsapp' ); ?></div>
                <div class="wpcw-metric-details">
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['users']['total'] ); ?> <?php _e( 'Total', 'wp-cupon-whatsapp' ); ?></span>
                    <span class="wpcw-metric-detail"><?php echo number_format( $metrics['users']['with_institution'] ); ?> <?php _e( 'Con Institución', 'wp-cupon-whatsapp' ); ?></span>
                </div>
            </div>

            <!-- Institutions Card -->
            <div class="wpcw-metric-card">
                <div class="wpcw-metric-header">
                    <span class="wpcw-metric-icon">🏫</span>
                    <h3><?php _e( 'Instituciones', 'wp-cupon-whatsapp' ); ?></h3>
                </div>
                <div class="wpcw-metric-value"><?php echo number_format( $metrics['institutions']['total'] ); ?></div>
                <div class="wpcw-metric-subtitle"><?php _e( 'Activas', 'wp-cupon-whatsapp' ); ?></div>
            </div>
        </div>

        <style>
        .wpcw-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .wpcw-metric-card {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .wpcw-metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .wpcw-metric-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .wpcw-metric-icon {
            font-size: 24px;
        }

        .wpcw-metric-header h3 {
            margin: 0;
            font-size: 16px;
            color: #23282d;
        }

        .wpcw-metric-value {
            font-size: 36px;
            font-weight: bold;
            color: #007cba;
            margin-bottom: 5px;
        }

        .wpcw-metric-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .wpcw-metric-details {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .wpcw-metric-detail {
            font-size: 12px;
            color: #666;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
        }
        </style>
        <?php
    }

    /**
     * Render dashboard chart
     */
    public static function render_chart() {
        $chart_data = self::get_chart_data();

        ?>
        <div class="wpcw-chart-container">
            <h3><?php _e( 'Actividad de Canjes - Últimos 30 Días', 'wp-cupon-whatsapp' ); ?></h3>
            <canvas id="wpcw-redemptions-chart" width="400" height="200"></canvas>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('wpcw-redemptions-chart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: <?php echo json_encode( $chart_data ); ?>,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
        </script>

        <style>
        .wpcw-chart-container {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .wpcw-chart-container h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #23282d;
        }

        #wpcw-redemptions-chart {
            max-height: 300px;
        }
        </style>
        <?php
    }

    /**
     * Render notifications panel
     */
    public static function render_notifications() {
        $notifications = self::get_recent_notifications();

        ?>
        <div class="wpcw-notifications-panel">
            <h3><?php _e( 'Notificaciones Recientes', 'wp-cupon-whatsapp' ); ?></h3>

            <?php if ( empty( $notifications ) ) : ?>
                <div class="wpcw-no-notifications">
                    <span class="wpcw-notification-icon">📭</span>
                    <p><?php _e( 'No hay notificaciones recientes', 'wp-cupon-whatsapp' ); ?></p>
                </div>
            <?php else : ?>
                <div class="wpcw-notifications-list">
                    <?php foreach ( $notifications as $notification ) : ?>
                        <div class="wpcw-notification-item wpcw-notification-<?php echo esc_attr( $notification['type'] ); ?>">
                            <div class="wpcw-notification-icon">
                                <?php echo esc_html( $notification['icon'] ); ?>
                            </div>
                            <div class="wpcw-notification-content">
                                <h4><?php echo esc_html( $notification['title'] ); ?></h4>
                                <p><?php echo esc_html( $notification['message'] ); ?></p>
                                <time><?php echo esc_html( human_time_diff( strtotime( $notification['time'] ), current_time( 'timestamp' ) ) . ' ' . __( 'atrás', 'wp-cupon-whatsapp' ) ); ?></time>
                            </div>
                            <?php if ( ! empty( $notification['url'] ) ) : ?>
                                <div class="wpcw-notification-actions">
                                    <a href="<?php echo esc_url( $notification['url'] ); ?>" class="button button-small">
                                        <?php _e( 'Ver', 'wp-cupon-whatsapp' ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <style>
        .wpcw-notifications-panel {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .wpcw-notifications-panel h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #23282d;
        }

        .wpcw-no-notifications {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .wpcw-no-notifications .wpcw-notification-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        .wpcw-notifications-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .wpcw-notification-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            background: #fafafa;
            transition: background-color 0.2s ease;
        }

        .wpcw-notification-item:hover {
            background: #f8f9fa;
        }

        .wpcw-notification-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .wpcw-notification-content {
            flex: 1;
        }

        .wpcw-notification-content h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: 600;
            color: #23282d;
        }

        .wpcw-notification-content p {
            margin: 0 0 5px 0;
            font-size: 13px;
            color: #666;
        }

        .wpcw-notification-content time {
            font-size: 11px;
            color: #999;
        }

        .wpcw-notification-actions {
            flex-shrink: 0;
        }

        .wpcw-notification-application {
            border-left: 4px solid #007cba;
        }

        .wpcw-notification-redemption {
            border-left: 4px solid #46b450;
        }
        </style>
        <?php
    }

    /**
     * Render system health status
     */
    public static function render_system_health() {
        $health = self::get_system_health();

        $status_colors = array(
            'good' => '#46b450',
            'warning' => '#ffb900',
            'critical' => '#dc3232',
        );

        $status_icons = array(
            'good' => '✅',
            'warning' => '⚠️',
            'critical' => '❌',
        );

        ?>
        <div class="wpcw-health-status">
            <h3><?php _e( 'Estado del Sistema', 'wp-cupon-whatsapp' ); ?></h3>

            <div class="wpcw-health-summary">
                <span class="wpcw-health-icon" style="color: <?php echo esc_attr( $status_colors[ $health['status'] ] ); ?>">
                    <?php echo esc_html( $status_icons[ $health['status'] ] ); ?>
                </span>
                <span class="wpcw-health-text">
                    <?php
                    switch ( $health['status'] ) {
                        case 'good':
                            _e( 'Sistema funcionando correctamente', 'wp-cupon-whatsapp' );
                            break;
                        case 'warning':
                            _e( 'Sistema con advertencias menores', 'wp-cupon-whatsapp' );
                            break;
                        case 'critical':
                            _e( 'Sistema con problemas críticos', 'wp-cupon-whatsapp' );
                            break;
                    }
                    ?>
                </span>
            </div>

            <?php if ( ! empty( $health['issues'] ) ) : ?>
                <div class="wpcw-health-issues">
                    <h4><?php _e( 'Problemas Detectados', 'wp-cupon-whatsapp' ); ?></h4>
                    <ul>
                        <?php foreach ( $health['issues'] as $issue ) : ?>
                            <li><?php echo esc_html( $issue ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $health['warnings'] ) ) : ?>
                <div class="wpcw-health-warnings">
                    <h4><?php _e( 'Advertencias', 'wp-cupon-whatsapp' ); ?></h4>
                    <ul>
                        <?php foreach ( $health['warnings'] as $warning ) : ?>
                            <li><?php echo esc_html( $warning ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <style>
        .wpcw-health-status {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .wpcw-health-status h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #23282d;
        }

        .wpcw-health-summary {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .wpcw-health-icon {
            font-size: 24px;
        }

        .wpcw-health-text {
            font-weight: 600;
            color: #23282d;
        }

        .wpcw-health-issues,
        .wpcw-health-warnings {
            margin-top: 20px;
        }

        .wpcw-health-issues h4,
        .wpcw-health-warnings h4 {
            margin: 0 0 10px 0;
            color: #dc3232;
            font-size: 14px;
        }

        .wpcw-health-warnings h4 {
            color: #ffb900;
        }

        .wpcw-health-issues ul,
        .wpcw-health-warnings ul {
            margin: 0;
            padding-left: 20px;
        }

        .wpcw-health-issues li,
        .wpcw-health-warnings li {
            margin-bottom: 5px;
            color: #666;
        }
        </style>
        <?php
    }
}