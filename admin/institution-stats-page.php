<?php
/**
 * WP Canje Cupon Whatsapp - Institution Manager Statistics Page
 *
 * Renders the statistics page for users with the wpcw_institution_manager role.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders the content for the Institution Manager Statistics page.
 */
function wpcw_render_institution_stats_page_content() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        echo '<div class="wrap"><p>' . esc_html__( 'Error: No se pudo identificar al usuario.', 'wp-cupon-whatsapp' ) . '</p></div>';
        return;
    }

    // Obtener el CPT wpcw_institution asociado a este usuario (si existe esta relación directa)
    // O simplemente usar el nombre del usuario/institución si no hay un CPT específico para la institución que gestiona.
    // Para este ejemplo, asumiremos que el nombre de la institución puede derivarse del display_name del usuario manager
    // o de un CPT 'wpcw_institution' si estuviera vinculado al user_id del manager.

    $institution_name      = '';
    $institution_user_data = get_userdata( $user_id );
    if ( $institution_user_data ) {
        $institution_name = $institution_user_data->display_name; // Usar display_name como nombre de la institución
    }

    // Alternativamente, si las instituciones son CPTs y los managers están vinculados a ellas:
    $linked_institution_cpt_id = 0;
    $institution_cpt_args      = array(
        'post_type'      => 'wpcw_institution',
        'meta_key'       => '_wpcw_manager_user_id', // Asumiendo que este meta key vincula el CPT de institución al user ID del manager
        'meta_value'     => $user_id,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    );
    $institution_cpt_query     = new WP_Query( $institution_cpt_args );
    if ( $institution_cpt_query->have_posts() ) {
        $linked_institution_cpt_id = $institution_cpt_query->posts[0];
        $institution_name          = get_the_title( $linked_institution_cpt_id ); // Sobrescribir con el nombre del CPT si se encuentra
    }
    // wp_reset_postdata(); // No es estrictamente necesario aquí

    if ( empty( $institution_name ) ) {
        $institution_name = __( 'Institución Sin Nombre Asignado', 'wp-cupon-whatsapp' );
    }

    // Asegurarse de que las funciones de estadísticas estén disponibles
    if ( ! function_exists( 'wpcw_stats_get_coupons_count_for_institution' ) ||
        ! function_exists( 'wpcw_stats_get_canjes_count_for_institution_coupons' ) ||
        ! function_exists( 'wpcw_stats_get_top_redeemed_coupons_for_institution_user' ) ) {
        echo '<div class="wrap"><p>' . esc_html__( 'Error: Las funciones de estadísticas necesarias no están disponibles.', 'wp-cupon-whatsapp' ) . '</p></div>';
        return;
    }
    ?>
    <div class="wrap wpcw-stats-page">
        <h1><?php printf( esc_html__( 'Estadísticas para Institución: %s', 'wp-cupon-whatsapp' ), esc_html( $institution_name ) ); ?></h1>

        <div id="wpcw-dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <!-- Columna 1 -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="meta-box-sortables">
                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Resumen de Cupones Creados por Mi Institución', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php $total_cupones_institucion = wpcw_stats_get_coupons_count_for_institution( $user_id ); ?>
                                <p><strong><?php esc_html_e( 'Total de Cupones Creados por Mí:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_cupones_institucion ); ?></p>
                            </div>
                        </div>

                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Resumen de Canjes de Mis Cupones (Global)', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php
                                $total_canjes_institucion       = wpcw_stats_get_canjes_count_for_institution_coupons( $user_id );
                                $canjes_pendientes_institucion  = wpcw_stats_get_canjes_count_for_institution_coupons( $user_id, array( 'status' => 'pendiente_confirmacion' ) );
                                $canjes_confirmados_institucion = wpcw_stats_get_canjes_count_for_institution_coupons( $user_id, array( 'status' => 'confirmado_por_negocio' ) );
                                $canjes_utilizados_institucion  = wpcw_stats_get_canjes_count_for_institution_coupons( $user_id, array( 'status' => 'utilizado_en_pedido_wc' ) );
                                ?>
                                <ul style="list-style-type: disc; margin-left: 20px;">
                                    <li><strong><?php esc_html_e( 'Total de Solicitudes de Canje (de mis cupones):', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_canjes_institucion ); ?></li>
                                    <li><?php esc_html_e( 'Pendientes de Confirmación por Negocio:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_pendientes_institucion ); ?></li>
                                    <li><?php esc_html_e( 'Confirmados por Negocio:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_confirmados_institucion ); ?></li>
                                    <li><?php esc_html_e( 'Utilizados en Pedidos WC:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_utilizados_institucion ); ?></li>
                                </ul>
                                <!-- Placeholder para gráfica -->
                                <div style="height:150px; width:100%; margin-top:15px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999;"><span><?php esc_html_e( 'Gráfica: Canjes de Mis Cupones (Próximamente)', 'wp-cupon-whatsapp' ); ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna 2 -->
                <div id="postbox-container-2" class="postbox-container">
                    <div class="meta-box-sortables">
                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Top 5 Mis Cupones Más Canjeados (Globalmente)', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php $top_coupons_institucion = wpcw_stats_get_top_redeemed_coupons_for_institution_user( $user_id, 5 ); ?>
                                <?php if ( ! empty( $top_coupons_institucion ) ) : ?>
                                    <table class="wp-list-table widefat striped" style="margin-top: 10px;">
                                        <thead><tr><th><?php esc_html_e( 'Nombre del Cupón', 'wp-cupon-whatsapp' ); ?></th><th><?php esc_html_e( 'Nro. Canjes', 'wp-cupon-whatsapp' ); ?></th></tr></thead>
                                        <tbody>
                                        <?php foreach ( $top_coupons_institucion as $coupon_stat ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( $coupon_stat->coupon_title ); ?> (ID: <?php echo esc_html( $coupon_stat->cupon_id ); ?>)</td>
                                                <td><?php echo esc_html( $coupon_stat->count ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p><?php esc_html_e( 'No hay datos de canjes para tus cupones todavía.', 'wp-cupon-whatsapp' ); ?></p>
                                <?php endif; ?>
                                <!-- Placeholder para gráfica -->
                                <div style="height:150px; width:100%; margin-top:15px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999;"><span><?php esc_html_e( 'Gráfica: Top Cupones (Próximamente)', 'wp-cupon-whatsapp' ); ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- #dashboard-widgets -->
        </div> <!-- #wpcw-dashboard-widgets-wrap -->
    </div><!-- .wrap -->
    <?php
}
?>
