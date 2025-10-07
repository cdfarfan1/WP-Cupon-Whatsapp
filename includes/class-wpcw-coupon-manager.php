<?php
/**
 * WP Cupón WhatsApp - Coupon Manager Class
 *
 * Handles bulk coupon operations, import/export, and advanced management
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Coupon_Manager class
 */
class WPCW_Coupon_Manager {

    /**
     * Bulk create coupons
     *
     * @param array $coupon_data Array of coupon data
     * @return array Results with success/error counts
     */
    public static function bulk_create_coupons( $coupon_data ) {
        $results = array(
            'success' => 0,
            'errors' => array(),
            'created_coupons' => array(),
        );

        foreach ( $coupon_data as $index => $data ) {
            try {
                $coupon_id = self::create_single_coupon( $data );

                if ( $coupon_id ) {
                    $results['success']++;
                    $results['created_coupons'][] = $coupon_id;
                } else {
                    $results['errors'][] = array(
                        'row' => $index + 1,
                        'message' => 'Error al crear cupón',
                        'data' => $data,
                    );
                }
            } catch ( Exception $e ) {
                $results['errors'][] = array(
                    'row' => $index + 1,
                    'message' => $e->getMessage(),
                    'data' => $data,
                );
            }
        }

        return $results;
    }

    /**
     * Create single coupon
     *
     * @param array $data Coupon data
     * @return int|bool Coupon ID or false on failure
     */
    private static function create_single_coupon( $data ) {
        // Validate required fields
        if ( empty( $data['code'] ) ) {
            throw new Exception( 'Código de cupón es requerido' );
        }

        // Check if coupon code already exists
        if ( wc_get_coupon_id_by_code( $data['code'] ) ) {
            throw new Exception( 'El código de cupón ya existe: ' . $data['code'] );
        }

        // Create coupon post
        $coupon_post = array(
            'post_title' => $data['code'],
            'post_content' => isset( $data['description'] ) ? $data['description'] : '',
            'post_status' => 'publish',
            'post_type' => 'shop_coupon',
        );

        $coupon_id = wp_insert_post( $coupon_post );

        if ( ! $coupon_id || is_wp_error( $coupon_id ) ) {
            throw new Exception( 'Error al crear el post del cupón' );
        }

        // Set coupon meta data
        $meta_fields = array(
            'discount_type' => isset( $data['discount_type'] ) ? $data['discount_type'] : 'percent',
            'coupon_amount' => isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0,
            'individual_use' => isset( $data['individual_use'] ) ? $data['individual_use'] : 'no',
            'usage_limit' => isset( $data['usage_limit'] ) ? intval( $data['usage_limit'] ) : '',
            'usage_limit_per_user' => isset( $data['usage_limit_per_user'] ) ? intval( $data['usage_limit_per_user'] ) : '',
            'date_expires' => isset( $data['expiry_date'] ) ? strtotime( $data['expiry_date'] ) : '',
            'minimum_amount' => isset( $data['minimum_amount'] ) ? floatval( $data['minimum_amount'] ) : '',
            'maximum_amount' => isset( $data['maximum_amount'] ) ? floatval( $data['maximum_amount'] ) : '',
        );

        foreach ( $meta_fields as $key => $value ) {
            if ( ! empty( $value ) || $value === 0 ) {
                update_post_meta( $coupon_id, $key, $value );
            }
        }

        // Set WPCW specific meta
        if ( isset( $data['wpcw_enabled'] ) && $data['wpcw_enabled'] ) {
            update_post_meta( $coupon_id, '_wpcw_enabled', 'yes' );
        }

        if ( isset( $data['business_id'] ) ) {
            update_post_meta( $coupon_id, '_wpcw_associated_business_id', $data['business_id'] );
        }

        if ( isset( $data['coupon_type'] ) ) {
            if ( $data['coupon_type'] === 'loyalty' ) {
                update_post_meta( $coupon_id, '_wpcw_is_loyalty_coupon', 'yes' );
            } elseif ( $data['coupon_type'] === 'public' ) {
                update_post_meta( $coupon_id, '_wpcw_is_public_coupon', 'yes' );
            }
        }

        if ( isset( $data['whatsapp_text'] ) ) {
            update_post_meta( $coupon_id, '_wpcw_whatsapp_text', $data['whatsapp_text'] );
        }

        // Log coupon creation
        WPCW_Logger::log( 'info', 'Coupon created via bulk import', array(
            'coupon_id' => $coupon_id,
            'code' => $data['code'],
            'created_by' => get_current_user_id(),
        ) );

        return $coupon_id;
    }

    /**
     * Bulk update coupons
     *
     * @param array $updates Array of coupon updates
     * @return array Results
     */
    public static function bulk_update_coupons( $updates ) {
        $results = array(
            'success' => 0,
            'errors' => array(),
        );

        foreach ( $updates as $coupon_id => $data ) {
            try {
                $result = self::update_single_coupon( $coupon_id, $data );

                if ( $result ) {
                    $results['success']++;
                } else {
                    $results['errors'][] = array(
                        'coupon_id' => $coupon_id,
                        'message' => 'Error al actualizar cupón',
                    );
                }
            } catch ( Exception $e ) {
                $results['errors'][] = array(
                    'coupon_id' => $coupon_id,
                    'message' => $e->getMessage(),
                );
            }
        }

        return $results;
    }

    /**
     * Update single coupon
     *
     * @param int $coupon_id Coupon ID
     * @param array $data Update data
     * @return bool Success
     */
    private static function update_single_coupon( $coupon_id, $data ) {
        $coupon = get_post( $coupon_id );

        if ( ! $coupon || $coupon->post_type !== 'shop_coupon' ) {
            throw new Exception( 'Cupón no encontrado' );
        }

        // Update post data if provided
        if ( isset( $data['description'] ) ) {
            wp_update_post( array(
                'ID' => $coupon_id,
                'post_content' => $data['description'],
            ) );
        }

        // Update meta fields
        $meta_fields = array(
            'discount_type', 'coupon_amount', 'individual_use', 'usage_limit',
            'usage_limit_per_user', 'date_expires', 'minimum_amount', 'maximum_amount',
            '_wpcw_enabled', '_wpcw_associated_business_id', '_wpcw_is_loyalty_coupon',
            '_wpcw_is_public_coupon', '_wpcw_whatsapp_text'
        );

        foreach ( $meta_fields as $field ) {
            if ( isset( $data[$field] ) ) {
                update_post_meta( $coupon_id, $field, $data[$field] );
            }
        }

        // Log update
        WPCW_Logger::log( 'info', 'Coupon updated via bulk operation', array(
            'coupon_id' => $coupon_id,
            'updated_by' => get_current_user_id(),
        ) );

        return true;
    }

    /**
     * Export coupons to CSV
     *
     * @param array $filters Export filters
     * @return string CSV content
     */
    public static function export_coupons_csv( $filters = array() ) {
        $args = array(
            'post_type' => 'shop_coupon',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(),
        );

        // Apply filters
        if ( isset( $filters['business_id'] ) ) {
            $args['meta_query'][] = array(
                'key' => '_wpcw_associated_business_id',
                'value' => $filters['business_id'],
                'compare' => '=',
            );
        }

        if ( isset( $filters['wpcw_enabled'] ) && $filters['wpcw_enabled'] ) {
            $args['meta_query'][] = array(
                'key' => '_wpcw_enabled',
                'value' => 'yes',
                'compare' => '=',
            );
        }

        $coupons = get_posts( $args );

        // CSV headers
        $csv_data = array();
        $csv_data[] = array(
            'Código',
            'Tipo de Descuento',
            'Monto',
            'Límite de Uso',
            'Límite por Usuario',
            'Fecha de Expiración',
            'Monto Mínimo',
            'Monto Máximo',
            'Uso Individual',
            'WPCW Habilitado',
            'ID Comercio Asociado',
            'Tipo de Cupón',
            'Texto WhatsApp',
            'Descripción',
        );

        // Add coupon data
        foreach ( $coupons as $coupon_post ) {
            $coupon = new WC_Coupon( $coupon_post->ID );

            $csv_data[] = array(
                $coupon->get_code(),
                $coupon->get_discount_type(),
                $coupon->get_amount(),
                $coupon->get_usage_limit(),
                $coupon->get_usage_limit_per_user(),
                $coupon->get_date_expires() ? $coupon->get_date_expires()->date( 'Y-m-d' ) : '',
                get_post_meta( $coupon_post->ID, 'minimum_amount', true ),
                get_post_meta( $coupon_post->ID, 'maximum_amount', true ),
                $coupon->get_individual_use() ? 'Sí' : 'No',
                get_post_meta( $coupon_post->ID, '_wpcw_enabled', true ) === 'yes' ? 'Sí' : 'No',
                get_post_meta( $coupon_post->ID, '_wpcw_associated_business_id', true ),
                self::get_coupon_type_label( $coupon_post->ID ),
                get_post_meta( $coupon_post->ID, '_wpcw_whatsapp_text', true ),
                $coupon_post->post_content,
            );
        }

        // Generate CSV
        $csv_content = '';
        foreach ( $csv_data as $row ) {
            $csv_content .= '"' . implode( '","', $row ) . '"' . "\n";
        }

        return $csv_content;
    }

    /**
     * Import coupons from CSV
     *
     * @param string $csv_content CSV content
     * @return array Import results
     */
    public static function import_coupons_csv( $csv_content ) {
        $lines = explode( "\n", trim( $csv_content ) );
        $header = str_getcsv( trim( $lines[0] ), ',', '"' );

        $coupon_data = array();

        for ( $i = 1; $i < count( $lines ); $i++ ) {
            if ( empty( trim( $lines[$i] ) ) ) continue;

            $row = str_getcsv( trim( $lines[$i] ), ',', '"' );

            if ( count( $row ) !== count( $header ) ) {
                continue; // Skip malformed rows
            }

            $data = array_combine( $header, $row );

            // Map CSV columns to coupon data
            $coupon_data[] = array(
                'code' => $data['Código'],
                'discount_type' => $data['Tipo de Descuento'],
                'amount' => $data['Monto'],
                'usage_limit' => $data['Límite de Uso'],
                'usage_limit_per_user' => $data['Límite por Usuario'],
                'expiry_date' => $data['Fecha de Expiración'],
                'minimum_amount' => $data['Monto Mínimo'],
                'maximum_amount' => $data['Monto Máximo'],
                'individual_use' => $data['Uso Individual'] === 'Sí' ? 'yes' : 'no',
                'wpcw_enabled' => $data['WPCW Habilitado'] === 'Sí',
                'business_id' => $data['ID Comercio Asociado'],
                'coupon_type' => self::get_coupon_type_from_label( $data['Tipo de Cupón'] ),
                'whatsapp_text' => $data['Texto WhatsApp'],
                'description' => $data['Descripción'],
            );
        }

        return self::bulk_create_coupons( $coupon_data );
    }

    /**
     * Get coupon type label
     *
     * @param int $coupon_id Coupon ID
     * @return string Type label
     */
    private static function get_coupon_type_label( $coupon_id ) {
        if ( get_post_meta( $coupon_id, '_wpcw_is_loyalty_coupon', true ) === 'yes' ) {
            return 'Fidelización';
        } elseif ( get_post_meta( $coupon_id, '_wpcw_is_public_coupon', true ) === 'yes' ) {
            return 'Público';
        }
        return 'Estándar';
    }

    /**
     * Get coupon type from label
     *
     * @param string $label Type label
     * @return string Type slug
     */
    private static function get_coupon_type_from_label( $label ) {
        switch ( $label ) {
            case 'Fidelización':
                return 'loyalty';
            case 'Público':
                return 'public';
            default:
                return 'standard';
        }
    }

    /**
     * Get coupon statistics
     *
     * @param array $filters Filters
     * @return array Statistics
     */
    public static function get_coupon_statistics( $filters = array() ) {
        global $wpdb;

        $stats = array(
            'total_coupons' => 0,
            'active_coupons' => 0,
            'expired_coupons' => 0,
            'wpcw_enabled_coupons' => 0,
            'loyalty_coupons' => 0,
            'public_coupons' => 0,
            'total_redemptions' => 0,
            'redemptions_this_month' => 0,
        );

        // Base query
        $args = array(
            'post_type' => 'shop_coupon',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        if ( isset( $filters['business_id'] ) ) {
            $args['meta_query'][] = array(
                'key' => '_wpcw_associated_business_id',
                'value' => $filters['business_id'],
                'compare' => '=',
            );
        }

        $coupons = get_posts( $args );
        $stats['total_coupons'] = count( $coupons );

        // Analyze coupons
        foreach ( $coupons as $coupon_post ) {
            $coupon = new WC_Coupon( $coupon_post->ID );

            // Check if active
            $expiry_date = $coupon->get_date_expires();
            if ( ! $expiry_date || $expiry_date->getTimestamp() > time() ) {
                $stats['active_coupons']++;
            } else {
                $stats['expired_coupons']++;
            }

            // Check WPCW status
            if ( get_post_meta( $coupon_post->ID, '_wpcw_enabled', true ) === 'yes' ) {
                $stats['wpcw_enabled_coupons']++;
            }

            // Check coupon type
            if ( get_post_meta( $coupon_post->ID, '_wpcw_is_loyalty_coupon', true ) === 'yes' ) {
                $stats['loyalty_coupons']++;
            } elseif ( get_post_meta( $coupon_post->ID, '_wpcw_is_public_coupon', true ) === 'yes' ) {
                $stats['public_coupons']++;
            }
        }

        // Get redemption statistics
        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) == $redemptions_table ) {
            $where_clause = '';
            if ( isset( $filters['business_id'] ) ) {
                $where_clause = $wpdb->prepare( "WHERE comercio_id = %d", $filters['business_id'] );
            }

            $stats['total_redemptions'] = $wpdb->get_var( "SELECT COUNT(*) FROM $redemptions_table $where_clause" );

            $this_month_where = $where_clause ? $where_clause . ' AND ' : 'WHERE ';
            $this_month_where .= "MONTH(fecha_solicitud_canje) = MONTH(CURRENT_DATE()) AND YEAR(fecha_solicitud_canje) = YEAR(CURRENT_DATE())";

            $stats['redemptions_this_month'] = $wpdb->get_var( "SELECT COUNT(*) FROM $redemptions_table $this_month_where" );
        }

        return $stats;
    }

    /**
     * Bulk delete coupons
     *
     * @param array $coupon_ids Coupon IDs to delete
     * @return array Results
     */
    public static function bulk_delete_coupons( $coupon_ids ) {
        $results = array(
            'success' => 0,
            'errors' => array(),
        );

        foreach ( $coupon_ids as $coupon_id ) {
            $result = wp_delete_post( $coupon_id, true );

            if ( $result ) {
                $results['success']++;

                // Log deletion
                WPCW_Logger::log( 'info', 'Coupon deleted via bulk operation', array(
                    'coupon_id' => $coupon_id,
                    'deleted_by' => get_current_user_id(),
                ) );
            } else {
                $results['errors'][] = array(
                    'coupon_id' => $coupon_id,
                    'message' => 'Error al eliminar cupón',
                );
            }
        }

        return $results;
    }
}