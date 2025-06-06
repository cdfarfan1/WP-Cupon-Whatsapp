<?php
/**
 * WP Canje Cupon Whatsapp Statistics Functions
 *
 * Functions to retrieve and process data for the statistics module.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get total count of redemptions, optionally filtered.
 *
 * @param array $filters Associative array of filters. Possible keys: 'status'.
 * @return int Total count of redemptions.
 */
function wpcw_stats_get_total_canjes( $filters = array() ) {
    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME; // Make sure this constant is defined
    $sql = "SELECT COUNT(*) FROM {$tabla_canjes}";
    $where_clauses = array();
    $params = array();

    if ( ! empty( $filters['status'] ) ) {
        $where_clauses[] = "estado_canje = %s";
        $params[] = sanitize_text_field( $filters['status'] );
    }
    // TODO: Add date filters (date_start, date_end) for fecha_solicitud_canje

    if ( ! empty( $where_clauses ) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }

    if ( empty($params) ) {
        return (int) $wpdb->get_var( $sql );
    } else {
        return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
    }
}

/**
 * Get total count of a specific Custom Post Type, optionally filtered by meta query.
 *
 * @param string $post_type The post type slug.
 * @param array  $meta_query_args Array for meta_query (WP_Query style).
 * @return int Total count of posts.
 */
function wpcw_stats_get_total_cpt_count( $post_type, $meta_query_args = array() ) {
    $args = array(
        'post_type'      => sanitize_key( $post_type ),
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Count all
        'fields'         => 'ids', // Optimization: only fetch IDs
    );

    if ( ! empty( $meta_query_args ) ) {
        $args['meta_query'] = $meta_query_args;
    }

    $query = new WP_Query( $args );
    return (int) $query->found_posts;
}

/**
 * Get top redeemed coupons.
 *
 * @param int   $limit Number of top coupons to return.
 * @param array $filters Filters (e.g., date range - for future use).
 * @return array Array of objects (cupon_id, coupon_title, count).
 */
function wpcw_stats_get_top_coupons_redeemed( $limit = 5, $filters = array() ) {
    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME;
    $limit = absint( $limit );

    // For now, only count 'confirmado_por_negocio' or 'utilizado_en_pedido_wc' as "redeemed"
    $redeemed_statuses = "'confirmado_por_negocio', 'utilizado_en_pedido_wc'";

    // It's safer to build the IN clause with multiple %s placeholders if statuses were dynamic.
    // However, since these are fixed strings, directly embedding is common, though less ideal than repeated %s.
    // For fixed strings, it's generally okay.
    $sql = $wpdb->prepare(
        "SELECT cupon_id, COUNT(cupon_id) as redemption_count
         FROM {$tabla_canjes}
         WHERE estado_canje IN ({$redeemed_statuses})
         GROUP BY cupon_id
         ORDER BY redemption_count DESC
         LIMIT %d",
        $limit
    );

    $results = $wpdb->get_results( $sql );
    $top_coupons = array();

    if ( $results ) {
        foreach ( $results as $result ) {
            $coupon_title = get_the_title( $result->cupon_id );
            $top_coupons[] = (object) array(
                'cupon_id'       => (int) $result->cupon_id,
                'coupon_title'   => $coupon_title ? $coupon_title : __('Cupón Desconocido', 'wp-cupon-whatsapp'),
                'count'          => (int) $result->redemption_count,
            );
        }
    }
    return $top_coupons;
}

/**
 * Get top businesses by redemptions associated with them.
 *
 * @param int   $limit Number of top businesses to return.
 * @param array $filters Filters (e.g., date range - for future use).
 * @return array Array of objects (comercio_id, business_name, count).
 */
function wpcw_stats_get_top_businesses_by_redemptions( $limit = 5, $filters = array() ) {
    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME;
    $limit = absint( $limit );

    $redeemed_statuses = "'confirmado_por_negocio', 'utilizado_en_pedido_wc'";

    $sql = $wpdb->prepare(
        "SELECT comercio_id, COUNT(comercio_id) as redemption_count
         FROM {$tabla_canjes}
         WHERE comercio_id IS NOT NULL AND comercio_id > 0 AND estado_canje IN ({$redeemed_statuses})
         GROUP BY comercio_id
         ORDER BY redemption_count DESC
         LIMIT %d",
        $limit
    );

    $results = $wpdb->get_results( $sql );
    $top_businesses = array();

    if ( $results ) {
        foreach ( $results as $result ) {
            $business_name = get_the_title( $result->comercio_id );
            $top_businesses[] = (object) array(
                'comercio_id'   => (int) $result->comercio_id,
                'business_name' => $business_name ? $business_name : __('Comercio Desconocido', 'wp-cupon-whatsapp'),
                'count'         => (int) $result->redemption_count,
            );
        }
    }
    return $top_businesses;
}


/**
 * Get total count of redemptions for a specific business, optionally filtered.
 *
 * @param int   $business_cpt_id The Post ID of the wpcw_business CPT.
 * @param array $filters Associative array of filters. Possible keys: 'status'.
 * @return int Total count of redemptions for the business.
 */
function wpcw_stats_get_canjes_count_for_business( $business_cpt_id, $filters = array() ) {
    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME;
    $business_cpt_id = absint( $business_cpt_id );

    if ( $business_cpt_id <= 0 ) {
        return 0;
    }

    $sql = "SELECT COUNT(*) FROM {$tabla_canjes}";
    $where_clauses = array("comercio_id = %d");
    $params = array( $business_cpt_id );

    if ( ! empty( $filters['status'] ) ) {
        $where_clauses[] = "estado_canje = %s";
        $params[] = sanitize_text_field( $filters['status'] );
    }
    // TODO: Add date filters

    if ( ! empty( $where_clauses ) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }

    return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
}

/**
 * Get total count of coupons associated with or created by a specific business owner.
 *
 * @param int   $business_user_id The User ID of the wpcw_business_owner.
 * @param array $filters Filters (e.g., for coupon status or type - for future use).
 * @return int Total count of coupons for the business.
 */
function wpcw_stats_get_coupons_count_for_business( $business_user_id, $filters = array() ) {
    $business_user_id = absint( $business_user_id );
    if ( $business_user_id <= 0 ) {
        return 0;
    }

    // Primero, obtener el ID del CPT wpcw_business asociado a este business_user_id
    // Asumiendo que el user_id del dueño se guarda en el meta '_wpcw_owner_user_id' del CPT wpcw_business
    $business_cpt_args = array(
        'post_type'  => 'wpcw_business',
        'post_status' => 'publish', // Considerar solo negocios activos/publicados
        'meta_key'   => '_wpcw_owner_user_id',
        'meta_value' => $business_user_id,
        'posts_per_page' => 1, // Solo debería haber uno, o el primero encontrado
        'fields'     => 'ids',
    );
    $business_cpt_query = new WP_Query( $business_cpt_args );
    $business_cpt_id = !empty( $business_cpt_query->posts ) ? $business_cpt_query->posts[0] : 0;

    $query1_ids = array();
    if ( $business_cpt_id > 0 ) {
        $args1 = array(
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_wpcw_associated_business_id',
                    'value' => $business_cpt_id,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                )
            )
        );
        // Aplicar filtros adicionales si existen en $filters
        // Ejemplo: if (isset($filters['coupon_type'])) { ... }
        $q1 = new WP_Query($args1);
        $query1_ids = $q1->posts;
    }

    // Cupones donde el autor es el business_user_id
    // Esto es relevante si el rol wpcw_business_owner tiene capacidad 'edit_shop_coupons'
    // y WordPress asigna correctamente el post_author.
    $args2 = array(
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'author' => $business_user_id,
    );
    // Aplicar filtros adicionales si existen en $filters
    $q2 = new WP_Query($args2);
    $query2_ids = $q2->posts;

    // Combinar y obtener IDs únicos
    $all_coupon_ids = array_unique( array_merge( $query1_ids, $query2_ids ) );

    return count( $all_coupon_ids );
}

/**
 * Get total count of coupons created by a specific institution manager.
 *
 * @param int   $institution_user_id The User ID of the wpcw_institution_manager.
 * @param array $filters Filters (e.g., for coupon status - for future use).
 * @return int Total count of coupons for the institution.
 */
function wpcw_stats_get_coupons_count_for_institution( $institution_user_id, $filters = array() ) {
    $institution_user_id = absint( $institution_user_id );
    if ( $institution_user_id <= 0 ) {
        return 0;
    }

    $coupon_args = array(
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish', // O considerar otros estados si es relevante
        'posts_per_page' => -1,
        'fields'         => 'ids', // Solo necesitamos contar
        'author'         => $institution_user_id, // Cupones creados por este usuario
    );

    // TODO: Añadir $filters si es necesario (ej. meta_query para tipo de cupón específico de WPCW si se añade)
    // if ( ! empty( $filters['meta_query'] ) ) {
    //     $coupon_args['meta_query'] = $filters['meta_query'];
    // }

    $query = new WP_Query( $coupon_args );
    return (int) $query->found_posts;
}

/**
 * Get total count of redemptions for coupons created by a specific institution manager.
 *
 * @param int   $institution_user_id The User ID of the wpcw_institution_manager.
 * @param array $filters Associative array of filters for canjes. Possible keys: 'status'.
 * @return int Total count of redemptions.
 */
function wpcw_stats_get_canjes_count_for_institution_coupons( $institution_user_id, $filters = array() ) {
    global $wpdb;
    $institution_user_id = absint( $institution_user_id );
    if ( $institution_user_id <= 0 ) {
        return 0;
    }

    // Paso A: Obtener IDs de los cupones creados por esta institución
    $coupon_ids_args = array(
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish', // Considerar todos los estados si un cupón no publicado aún puede tener canjes (poco probable)
        'author'         => $institution_user_id,
        'posts_per_page' => -1,
        'fields'         => 'ids', // Solo obtener IDs
    );
    $coupon_ids_query = new WP_Query( $coupon_ids_args );
    $coupon_ids = $coupon_ids_query->posts;

    if ( empty( $coupon_ids ) ) {
        return 0; // La institución no tiene cupones, por lo tanto, no hay canjes de sus cupones.
    }

    // Paso B: Contar canjes para esos cupones
    $tabla_canjes = WPCW_CANJES_TABLE_NAME;

    // Crear placeholders para la cláusula IN
    $ids_placeholders = implode( ', ', array_fill( 0, count( $coupon_ids ), '%d' ) );

    $sql = "SELECT COUNT(*) FROM {$tabla_canjes} WHERE cupon_id IN ( " . $ids_placeholders . " )";

    $params = $coupon_ids; // Los IDs de cupón son los primeros parámetros

    $where_clauses_canjes = array(); // Renamed to avoid conflict with $where_clauses in other functions
    if ( ! empty( $filters['status'] ) ) {
        $where_clauses_canjes[] = "estado_canje = %s";
        $params[] = sanitize_text_field( $filters['status'] );
    }
    // TODO: Add date filters for canjes

    if ( ! empty( $where_clauses_canjes ) ) {
        // Añadir AND si ya hay una cláusula WHERE (la de cupon_id IN (...))
        $sql .= " AND " . implode( " AND ", $where_clauses_canjes );
    }

    return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
}

?>
