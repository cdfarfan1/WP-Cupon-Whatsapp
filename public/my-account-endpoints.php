<?php
/**
 * WP Canje Cupon Whatsapp My Account Endpoints
 *
 * Handles custom endpoints for the WooCommerce My Account page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register new endpoints for the My Account page.
 */
function wpcw_add_my_account_endpoints() {
    add_rewrite_endpoint( 'mis-canjes', EP_ROOT | EP_PAGES );
    // Nota: Es posible que sea necesario hacer flush de las rewrite rules
    // visitando Ajustes > Enlaces Permanentes después de añadir esto si el plugin ya está activo.
    // El flush_rewrite_rules() en la activación del plugin debería cubrir esto para nuevas instalaciones.
}
add_action( 'init', 'wpcw_add_my_account_endpoints' );

/**
 * Add "mis-canjes" to the WooCommerce query vars.
 *
 * @param array $vars Existing query vars.
 * @return array Modified query vars.
 */
function wpcw_add_mis_canjes_query_var( $vars ) {
    $vars['mis-canjes'] = 'mis-canjes'; // El valor puede ser cualquier cosa, usualmente el mismo slug.
    return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'wpcw_add_mis_canjes_query_var', 10, 1 );

/**
 * Adds "Mis Canjes" to the WooCommerce My Account menu.
 *
 * @param array $items Existing menu items.
 * @return array Modified menu items.
 */
function wpcw_add_mis_canjes_menu_item( $items ) {
    // El slug del endpoint es la clave, el texto del menú es el valor.
    $new_items = array();

    // Intentar insertar "Mis Canjes" antes de "Salir" (customer-logout)
    // o al final si "Salir" no está presente por alguna razón.
    $logout_key = 'customer-logout';

    if ( array_key_exists( $logout_key, $items ) ) {
        foreach ( $items as $key => $value ) {
            if ( $key === $logout_key ) {
                // Añadir nuestro item antes de "Salir"
                $new_items['mis-canjes'] = __( 'Mis Canjes', 'wp-cupon-whatsapp' );
            }
            $new_items[ $key ] = $value;
        }
    } else {
        // Si "Salir" no está, añadir todos los items y luego el nuestro
        $new_items               = $items;
        $new_items['mis-canjes'] = __( 'Mis Canjes', 'wp-cupon-whatsapp' );
    }

    return $new_items;
}
add_filter( 'woocommerce_account_menu_items', 'wpcw_add_mis_canjes_menu_item', 10, 1 );

/**
 * Renders the content for the "Mis Canjes" endpoint in My Account.
 */
function wpcw_render_mis_canjes_content() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        echo '<p>' . esc_html__( 'No se pudo identificar al usuario.', 'wp-cupon-whatsapp' ) . '</p>';
        return;
    }

    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME; // Asegúrate que esta constante esté definida

    // Consulta para obtener los canjes del usuario actual
    // Ordenados por fecha de solicitud descendente
    $canjes = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$tabla_canjes} WHERE cliente_id = %d ORDER BY fecha_solicitud_canje DESC",
            $user_id
        )
    );

    echo '<h2>' . esc_html__( 'Historial de Mis Canjes', 'wp-cupon-whatsapp' ) . '</h2>';

    if ( empty( $canjes ) ) {
        // Usar la clase de mensaje de WooCommerce para consistencia
        wc_print_notice( __( 'Aún no has realizado ningún canje.', 'wp-cupon-whatsapp' ), 'notice' );
        return;
    }

    // Iniciar tabla
    echo '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">';
    echo '<thead><tr>';
    echo '<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr">' . esc_html__( 'Nro. Canje', 'wp-cupon-whatsapp' ) . '</span></th>';
    echo '<th class="woocommerce-orders-table__header woocommerce-orders-table__header-date"><span class="nobr">' . esc_html__( 'Fecha Solicitud', 'wp-cupon-whatsapp' ) . '</span></th>';
    echo '<th class="woocommerce-orders-table__header woocommerce-orders-table__header-status"><span class="nobr">' . esc_html__( 'Estado', 'wp-cupon-whatsapp' ) . '</span></th>';
    echo '<th class="woocommerce-orders-table__header woocommerce-orders-table__header-total"><span class="nobr">' . esc_html__( 'Cupón', 'wp-cupon-whatsapp' ) . '</span></th>';
    echo '<th class="woocommerce-orders-table__header woocommerce-orders-table__header-total"><span class="nobr">' . esc_html__( 'Comercio', 'wp-cupon-whatsapp' ) . '</span></th>';
    echo '<th class="woocommerce-orders-table__header woocommerce-orders-table__header-actions"><span class="nobr">' . esc_html__( 'Código WC', 'wp-cupon-whatsapp' ) . '</span></th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ( $canjes as $canje ) {
        echo '<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-' . esc_attr( $canje->estado_canje ) . ' order">'; // order_item

        // Número de Canje
        echo '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="' . esc_attr__( 'Nro. Canje', 'wp-cupon-whatsapp' ) . '">';
        echo esc_html( $canje->numero_canje );
        echo '</td>';

        // Fecha Solicitud
        echo '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-date" data-title="' . esc_attr__( 'Fecha Solicitud', 'wp-cupon-whatsapp' ) . '">';
        echo esc_html( date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime( $canje->fecha_solicitud_canje ) ) );
        echo '</td>';

        // Estado
        $estado_display = wpcw_get_displayable_canje_status( $canje->estado_canje );
        echo '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-status" data-title="' . esc_attr__( 'Estado', 'wp-cupon-whatsapp' ) . '" style="white-space:nowrap;">';
        echo esc_html( $estado_display );
        echo '</td>';

        // Cupón (Nombre)
        echo '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-total" data-title="' . esc_attr__( 'Cupón', 'wp-cupon-whatsapp' ) . '">';
        $coupon_title = $canje->cupon_id ? get_the_title( $canje->cupon_id ) : __( 'N/A', 'wp-cupon-whatsapp' );
        echo esc_html( $coupon_title );
        echo '</td>';

        // Comercio (Nombre)
        echo '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-total" data-title="' . esc_attr__( 'Comercio', 'wp-cupon-whatsapp' ) . '">';
        $comercio_title = '';
        if ( ! empty( $canje->comercio_id ) ) {
            $comercio_post = get_post( $canje->comercio_id );
            if ( $comercio_post && $comercio_post->post_type === 'wpcw_business' ) {
                $comercio_title = get_the_title( $canje->comercio_id );
            } else {
                $comercio_title = __( 'Comercio no especificado o inválido', 'wp-cupon-whatsapp' );
            }
        } else {
            // Podría ser un cupón de institución o uno no asociado a un comercio específico
            $original_coupon_post = get_post( $canje->cupon_id );
            if ( $original_coupon_post ) {
                $coupon_author_id = $original_coupon_post->post_author;
                $user_data        = get_userdata( $coupon_author_id );
                if ( $user_data && in_array( 'wpcw_institution_manager', (array) $user_data->roles ) ) {
                    // Intentar obtener el nombre de la institución asociada al manager
                    // Esto asume que el manager está vinculado a un CPT wpcw_institution
                    // Esta lógica puede necesitar ajuste según cómo se vinculen managers e instituciones
                    $linked_institution_id = get_user_meta( $coupon_author_id, '_wpcw_associated_entity_id', true );
                    if ( $linked_institution_id && get_post_type( $linked_institution_id ) === 'wpcw_institution' ) {
                        $comercio_title = get_the_title( $linked_institution_id );
                    } else {
                        $comercio_title = $user_data->display_name; // Nombre del manager si no hay CPT de institución
                    }
                } else {
                    $comercio_title = __( 'N/A', 'wp-cupon-whatsapp' );
                }
            } else {
                $comercio_title = __( 'N/A', 'wp-cupon-whatsapp' );
            }
        }
        echo esc_html( $comercio_title );
        echo '</td>';

        // Código Cupón WC
        echo '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-actions" data-title="' . esc_attr__( 'Código WC', 'wp-cupon-whatsapp' ) . '">';
        if ( ! empty( $canje->codigo_cupon_wc ) ) {
            echo '<strong>' . esc_html( $canje->codigo_cupon_wc ) . '</strong>';
        } else {
            // Si el estado es 'pendiente_confirmacion', podríamos mostrar un mensaje.
            if ( $canje->estado_canje === 'pendiente_confirmacion' ) {
                echo '<em>' . esc_html__( 'Esperando confirmación del negocio', 'wp-cupon-whatsapp' ) . '</em>';
            } else {
                echo '-';
            }
        }
        echo '</td>';

        echo '</tr>';
    }

    echo '</tbody></table>';

    // TODO: Añadir paginación para los canjes si son muchos.
}
add_action( 'woocommerce_account_mis-canjes_endpoint', 'wpcw_render_mis_canjes_content' );

/**
 * Helper function to get displayable status for canje.
 *
 * @param string $status_key The status key from the database.
 * @return string The displayable status.
 */
if ( ! function_exists( 'wpcw_get_displayable_canje_status' ) ) {
    function wpcw_get_displayable_canje_status( $status_key ) {
        $statuses = array(
            'pendiente_confirmacion' => __( 'Pendiente Confirmación Negocio', 'wp-cupon-whatsapp' ),
            'confirmado_por_negocio' => __( 'Confirmado (Código WC generado)', 'wp-cupon-whatsapp' ),
            'utilizado_en_pedido_wc' => __( 'Utilizado en Pedido', 'wp-cupon-whatsapp' ),
            'cancelado_por_usuario'  => __( 'Cancelado por Usuario', 'wp-cupon-whatsapp' ),
            'cancelado_por_admin'    => __( 'Cancelado por Administrador', 'wp-cupon-whatsapp' ),
            'vencido'                => __( 'Vencido', 'wp-cupon-whatsapp' ),
        );
        return isset( $statuses[ $status_key ] ) ? $statuses[ $status_key ] : ucwords( str_replace( '_', ' ', (string) $status_key ) );
    }
}
