<?php
/**
 * WP Cupón WhatsApp - Redemption Manager Class
 *
 * RESPONSABILIDAD: Gestión MASIVA y REPORTES de redenciones
 * - Listar redenciones con filtros y paginación
 * - Operaciones bulk (aprobar/rechazar múltiples)
 * - Generar reportes y estadísticas
 * - Exportar a CSV
 * - Análisis de tendencias
 *
 * NOTA: Para procesar redenciones INDIVIDUALES usar WPCW_Redemption_Handler
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class WPCW_Redemption_Manager
 *
 * Handles bulk operations and reporting for redemptions
 */
class WPCW_Redemption_Manager {

    /**
     * Get pending redemptions
     *
     * @param array $filters Filters
     * @return array Pending redemptions
     */
    public static function get_pending_redemptions( $filters = array() ) {
        global $wpdb;

        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) != $redemptions_table ) {
            return array(
                'redemptions' => array(),
                'total' => 0,
                'pages' => 0,
            );
        }

        $where_clauses = array( "estado_canje = 'pendiente_confirmacion'" );
        $join_clauses = array();
        $select_fields = "r.*";

        // Add business filter
        if ( isset( $filters['business_id'] ) ) {
            $where_clauses[] = $wpdb->prepare( "r.comercio_id = %d", $filters['business_id'] );
        }

        // Add date range filter
        if ( isset( $filters['date_from'] ) ) {
            $where_clauses[] = $wpdb->prepare( "DATE(r.fecha_solicitud_canje) >= %s", $filters['date_from'] );
        }

        if ( isset( $filters['date_to'] ) ) {
            $where_clauses[] = $wpdb->prepare( "DATE(r.fecha_solicitud_canje) <= %s", $filters['date_to'] );
        }

        // Add search filter
        if ( isset( $filters['search'] ) && ! empty( $filters['search'] ) ) {
            $search_term = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
            $where_clauses[] = $wpdb->prepare(
                "(r.numero_canje LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s)",
                $search_term, $search_term, $search_term
            );
            $join_clauses[] = "LEFT JOIN {$wpdb->users} u ON r.cliente_id = u.ID";
            $select_fields .= ", u.display_name, u.user_email";
        }

        $where_sql = implode( ' AND ', $where_clauses );
        $join_sql = implode( ' ', $join_clauses );

        // Get total count
        $total_query = "SELECT COUNT(*) FROM $redemptions_table r $join_sql WHERE $where_sql";
        $total = $wpdb->get_var( $total_query );

        // Pagination
        $per_page = isset( $filters['per_page'] ) ? intval( $filters['per_page'] ) : 20;
        $current_page = isset( $filters['paged'] ) ? intval( $filters['paged'] ) : 1;
        $offset = ( $current_page - 1 ) * $per_page;

        // Get redemptions
        $query = $wpdb->prepare(
            "SELECT $select_fields FROM $redemptions_table r $join_sql WHERE $where_sql ORDER BY r.fecha_solicitud_canje DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        );

        $redemptions = $wpdb->get_results( $query );

        // Format redemptions data
        $formatted_redemptions = array();
        foreach ( $redemptions as $redemption ) {
            $formatted_redemptions[] = self::format_redemption_data( $redemption );
        }

        return array(
            'redemptions' => $formatted_redemptions,
            'total' => $total,
            'pages' => ceil( $total / $per_page ),
            'current_page' => $current_page,
        );
    }

    /**
     * Bulk process redemptions
     *
     * @param array $redemption_ids Redemption IDs
     * @param string $action Action (approve/reject)
     * @param string $reason Rejection reason (if applicable)
     * @return array Results
     */
    public static function bulk_process_redemptions( $redemption_ids, $action, $reason = '' ) {
        $results = array(
            'success' => 0,
            'errors' => array(),
        );

        foreach ( $redemption_ids as $redemption_id ) {
            try {
                if ( $action === 'approve' ) {
                    // Delegate to Handler for individual redemption processing
                    $result = WPCW_Redemption_Handler::confirm_redemption( $redemption_id, get_current_user_id() );
                } elseif ( $action === 'reject' ) {
                    $result = self::reject_redemption( $redemption_id, $reason );
                }

                if ( $result ) {
                    $results['success']++;
                } else {
                    $results['errors'][] = array(
                        'redemption_id' => $redemption_id,
                        'message' => 'Error al procesar canje',
                    );
                }
            } catch ( Exception $e ) {
                $results['errors'][] = array(
                    'redemption_id' => $redemption_id,
                    'message' => $e->getMessage(),
                );
            }
        }

        return $results;
    }

    /**
     * Reject redemption
     *
     * @param int $redemption_id Redemption ID
     * @param string $reason Rejection reason
     * @return bool Success
     */
    public static function reject_redemption( $redemption_id, $reason = '' ) {
        global $wpdb;

        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        $result = $wpdb->update(
            $redemptions_table,
            array(
                'estado_canje' => 'rechazado',
                'fecha_rechazo' => current_time( 'mysql' ),
                'notas_internas' => $reason,
                'updated_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $redemption_id ),
            array( '%s', '%s', '%s', '%s' ),
            array( '%d' )
        );

        if ( $result !== false ) {
            // Log rejection
            WPCW_Logger::log( 'info', 'Redemption rejected', array(
                'redemption_id' => $redemption_id,
                'reason' => $reason,
                'rejected_by' => get_current_user_id(),
            ) );

            return true;
        }

        return false;
    }

    /**
     * Get redemption statistics
     *
     * @param array $filters Filters
     * @return array Statistics
     */
    public static function get_redemption_statistics( $filters = array() ) {
        global $wpdb;

        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) != $redemptions_table ) {
            return array(
                'total_redemptions' => 0,
                'pending_redemptions' => 0,
                'confirmed_redemptions' => 0,
                'rejected_redemptions' => 0,
                'used_redemptions' => 0,
                'expired_redemptions' => 0,
            );
        }

        $where_clauses = array();
        if ( isset( $filters['business_id'] ) ) {
            $where_clauses[] = $wpdb->prepare( "comercio_id = %d", $filters['business_id'] );
        }

        if ( isset( $filters['date_from'] ) ) {
            $where_clauses[] = $wpdb->prepare( "DATE(fecha_solicitud_canje) >= %s", $filters['date_from'] );
        }

        if ( isset( $filters['date_to'] ) ) {
            $where_clauses[] = $wpdb->prepare( "DATE(fecha_solicitud_canje) <= %s", $filters['date_to'] );
        }

        $where_sql = ! empty( $where_clauses ) ? 'WHERE ' . implode( ' AND ', $where_clauses ) : '';

        $stats = array();

        // Total redemptions
        $stats['total_redemptions'] = $wpdb->get_var( "SELECT COUNT(*) FROM $redemptions_table $where_sql" );

        // By status
        $status_query = "SELECT estado_canje, COUNT(*) as count FROM $redemptions_table $where_sql GROUP BY estado_canje";
        $status_results = $wpdb->get_results( $status_query );

        $stats['pending_redemptions'] = 0;
        $stats['confirmed_redemptions'] = 0;
        $stats['rejected_redemptions'] = 0;
        $stats['used_redemptions'] = 0;
        $stats['expired_redemptions'] = 0;

        foreach ( $status_results as $result ) {
            switch ( $result->estado_canje ) {
                case 'pendiente_confirmacion':
                    $stats['pending_redemptions'] = $result->count;
                    break;
                case 'confirmado_por_negocio':
                    $stats['confirmed_redemptions'] = $result->count;
                    break;
                case 'rechazado':
                    $stats['rejected_redemptions'] = $result->count;
                    break;
                case 'utilizado_en_pedido_wc':
                    $stats['used_redemptions'] = $result->count;
                    break;
                case 'expirado':
                    $stats['expired_redemptions'] = $result->count;
                    break;
            }
        }

        // Time-based statistics
        $current_month = date( 'Y-m' );
        $stats['current_month_redemptions'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $redemptions_table WHERE DATE_FORMAT(fecha_solicitud_canje, '%Y-%m') = %s $where_sql",
                $current_month
            )
        );

        $last_month = date( 'Y-m', strtotime( 'last month' ) );
        $stats['last_month_redemptions'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $redemptions_table WHERE DATE_FORMAT(fecha_solicitud_canje, '%Y-%m') = %s $where_sql",
                $last_month
            )
        );

        // Average processing time
        $avg_time_query = "SELECT AVG(TIMESTAMPDIFF(HOUR, fecha_solicitud_canje, fecha_confirmacion_canje)) as avg_hours
                          FROM $redemptions_table
                          WHERE estado_canje = 'confirmado_por_negocio'
                          AND fecha_confirmacion_canje IS NOT NULL $where_sql";

        $stats['avg_processing_hours'] = $wpdb->get_var( $avg_time_query );

        return $stats;
    }

    /**
     * Get redemption reports
     *
     * @param array $filters Filters
     * @return array Report data
     */
    public static function get_redemption_reports( $filters = array() ) {
        global $wpdb;

        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) != $redemptions_table ) {
            return array();
        }

        $where_clauses = array();
        if ( isset( $filters['business_id'] ) ) {
            $where_clauses[] = $wpdb->prepare( "r.comercio_id = %d", $filters['business_id'] );
        }

        if ( isset( $filters['status'] ) ) {
            $where_clauses[] = $wpdb->prepare( "r.estado_canje = %s", $filters['status'] );
        }

        if ( isset( $filters['date_from'] ) ) {
            $where_clauses[] = $wpdb->prepare( "DATE(r.fecha_solicitud_canje) >= %s", $filters['date_from'] );
        }

        if ( isset( $filters['date_to'] ) ) {
            $where_clauses[] = $wpdb->prepare( "DATE(r.fecha_solicitud_canje) <= %s", $filters['date_to'] );
        }

        $where_sql = ! empty( $where_clauses ) ? 'WHERE ' . implode( ' AND ', $where_clauses ) : '';

        $query = "SELECT
                    r.*,
                    u.display_name as user_name,
                    u.user_email,
                    p.post_title as coupon_title,
                    b.post_title as business_name
                  FROM $redemptions_table r
                  LEFT JOIN {$wpdb->users} u ON r.cliente_id = u.ID
                  LEFT JOIN {$wpdb->posts} p ON r.cupon_id = p.ID
                  LEFT JOIN {$wpdb->posts} b ON r.comercio_id = b.ID
                  $where_sql
                  ORDER BY r.fecha_solicitud_canje DESC";

        if ( isset( $filters['limit'] ) ) {
            $query .= $wpdb->prepare( " LIMIT %d", $filters['limit'] );
        }

        $results = $wpdb->get_results( $query );

        $reports = array();
        foreach ( $results as $result ) {
            $reports[] = array(
                'id' => $result->id,
                'redemption_number' => $result->numero_canje,
                'user_name' => $result->user_name,
                'user_email' => $result->user_email,
                'coupon_title' => $result->coupon_title,
                'business_name' => $result->business_name,
                'status' => $result->estado_canje,
                'request_date' => $result->fecha_solicitud_canje,
                'confirmation_date' => $result->fecha_confirmacion_canje,
                'rejection_date' => $result->fecha_rechazo,
                'wc_coupon_code' => $result->codigo_cupon_wc,
                'wc_order_id' => $result->id_pedido_wc,
                'whatsapp_url' => $result->whatsapp_url,
                'internal_notes' => $result->notas_internas,
            );
        }

        return $reports;
    }

    /**
     * Export redemptions to CSV
     *
     * @param array $filters Filters
     * @return string CSV content
     */
    public static function export_redemptions_csv( $filters = array() ) {
        $reports = self::get_redemption_reports( $filters );

        // CSV headers
        $csv_data = array();
        $csv_data[] = array(
            'ID Canje',
            'Número de Canje',
            'Usuario',
            'Email',
            'Cupón',
            'Comercio',
            'Estado',
            'Fecha Solicitud',
            'Fecha Confirmación',
            'Fecha Rechazo',
            'Código WC',
            'ID Pedido WC',
            'Notas Internas',
        );

        // Add redemption data
        foreach ( $reports as $report ) {
            $csv_data[] = array(
                $report['id'],
                $report['redemption_number'],
                $report['user_name'],
                $report['user_email'],
                $report['coupon_title'],
                $report['business_name'],
                self::get_status_label( $report['status'] ),
                $report['request_date'],
                $report['confirmation_date'],
                $report['rejection_date'],
                $report['wc_coupon_code'],
                $report['wc_order_id'],
                $report['internal_notes'],
            );
        }

        // Generate CSV
        $csv_content = '';
        foreach ( $csv_data as $row ) {
            $csv_content .= '"' . implode( '","', array_map( 'addslashes', $row ) ) . '"' . "\n";
        }

        return $csv_content;
    }

    /**
     * Get status label
     *
     * @param string $status Status slug
     * @return string Status label
     */
    private static function get_status_label( $status ) {
        $labels = array(
            'pendiente_confirmacion' => 'Pendiente Confirmación',
            'confirmado_por_negocio' => 'Confirmado por Negocio',
            'rechazado' => 'Rechazado',
            'utilizado_en_pedido_wc' => 'Utilizado en Pedido WC',
            'expirado' => 'Expirado',
            'cancelado' => 'Cancelado',
        );

        return isset( $labels[$status] ) ? $labels[$status] : $status;
    }

    /**
     * Format redemption data
     *
     * @param object $redemption Raw redemption data
     * @return array Formatted redemption data
     */
    private static function format_redemption_data( $redemption ) {
        $user = get_user_by( 'id', $redemption->cliente_id );
        $coupon = get_post( $redemption->cupon_id );
        $business = get_post( $redemption->comercio_id );

        return array(
            'id' => $redemption->id,
            'redemption_number' => $redemption->numero_canje,
            'user_id' => $redemption->cliente_id,
            'user_name' => $user ? $user->display_name : 'Usuario desconocido',
            'user_email' => $user ? $user->user_email : '',
            'coupon_id' => $redemption->cupon_id,
            'coupon_title' => $coupon ? $coupon->post_title : 'Cupón desconocido',
            'business_id' => $redemption->comercio_id,
            'business_name' => $business ? $business->post_title : 'Comercio desconocido',
            'status' => $redemption->estado_canje,
            'request_date' => $redemption->fecha_solicitud_canje,
            'confirmation_date' => $redemption->fecha_confirmacion_canje,
            'rejection_date' => $redemption->fecha_rechazo,
            'wc_coupon_code' => $redemption->codigo_cupon_wc,
            'wc_order_id' => $redemption->id_pedido_wc,
            'whatsapp_url' => $redemption->whatsapp_url,
            'internal_notes' => $redemption->notas_internas,
            'token' => $redemption->token_confirmacion,
        );
    }

    /**
     * Get redemption trends for charts
     *
     * @param int $days Number of days
     * @param array $filters Filters
     * @return array Chart data
     */
    public static function get_redemption_trends( $days = 30, $filters = array() ) {
        global $wpdb;

        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) != $redemptions_table ) {
            return array(
                'labels' => array(),
                'datasets' => array(),
            );
        }

        $where_clauses = array();
        if ( isset( $filters['business_id'] ) ) {
            $where_clauses[] = $wpdb->prepare( "comercio_id = %d", $filters['business_id'] );
        }

        $where_sql = ! empty( $where_clauses ) ? 'WHERE ' . implode( ' AND ', $where_clauses ) : '';

        $data = array();
        for ( $i = $days - 1; $i >= 0; $i-- ) {
            $date = date( 'Y-m-d', strtotime( "-{$i} days" ) );

            $count = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $redemptions_table WHERE DATE(fecha_solicitud_canje) = %s $where_sql",
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
}