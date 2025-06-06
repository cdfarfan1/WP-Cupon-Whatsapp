<?php
/**
 * WP Canje Cupon Whatsapp - Business Owner Statistics Page
 *
 * Renders the statistics page for users with the wpcw_business_owner role.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders the content for the Business Owner Statistics page.
 */
function wpcw_render_business_stats_page_content() {
    $user_id = get_current_user_id();
    if ( !$user_id ) {
        echo '<div class="wrap"><p>' . esc_html__('Error: No se pudo identificar al usuario.', 'wp-cupon-whatsapp') . '</p></div>';
        return;
    }

    // Obtener el CPT wpcw_business asociado a este usuario
    $business_cpt_id = 0;
    $business_name = '';
    $business_cpt_args = array(
        'post_type'  => 'wpcw_business',
        'meta_key'   => '_wpcw_owner_user_id', // Meta key linking user to business CPT
        'meta_value' => $user_id,
        'posts_per_page' => 1,
        'post_status' => 'publish', // Only consider published businesses
        'fields' => 'ids' // We only need the ID
    );
    $business_query = new WP_Query( $business_cpt_args );

    if ( $business_query->have_posts() ) {
        $business_cpt_id = $business_query->posts[0]; // Since 'fields' => 'ids', posts[0] is the ID
        $business_name = get_the_title($business_cpt_id);
    }
    // wp_reset_postdata(); // Not strictly needed after WP_Query with 'fields' => 'ids' but doesn't hurt

    if ( $business_cpt_id === 0 ) {
        echo '<div class="wrap"><p>' . esc_html__('No estás asociado a ningún comercio o tu comercio no está publicado. No se pueden mostrar estadísticas.', 'wp-cupon-whatsapp') . '</p></div>';
        return;
    }

    // Asegurarse de que las funciones de estadísticas estén disponibles
    if ( ! function_exists('wpcw_stats_get_canjes_count_for_business') ||
         ! function_exists('wpcw_stats_get_coupons_count_for_business') ||
         ! function_exists('wpcw_stats_get_top_redeemed_coupons_for_business') ) {
        echo '<div class="wrap"><p>' . esc_html__('Error: Las funciones de estadísticas necesarias no están disponibles.', 'wp-cupon-whatsapp') . '</p></div>';
        return;
    }
    ?>
    <div class="wrap wpcw-stats-page">
        <h1><?php printf( esc_html__( 'Estadísticas para: %s', 'wp-cupon-whatsapp' ), esc_html($business_name) ); ?></h1>

        <div id="wpcw-dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                 <!-- Columna 1 -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="meta-box-sortables">
                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Resumen de Canjes en Mi Comercio', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php
                                $total_canjes_comercio = wpcw_stats_get_canjes_count_for_business($business_cpt_id);
                                $canjes_pendientes_comercio = wpcw_stats_get_canjes_count_for_business($business_cpt_id, array('status' => 'pendiente_confirmacion'));
                                $canjes_confirmados_comercio = wpcw_stats_get_canjes_count_for_business($business_cpt_id, array('status' => 'confirmado_por_negocio'));
                                $canjes_utilizados_comercio = wpcw_stats_get_canjes_count_for_business($business_cpt_id, array('status' => 'utilizado_en_pedido_wc'));
                                ?>
                                <ul style="list-style-type: disc; margin-left: 20px;">
                                    <li><strong><?php esc_html_e( 'Total de Solicitudes de Canje Recibidas:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_canjes_comercio ); ?></li>
                                    <li><?php esc_html_e( 'Pendientes de Tu Confirmación:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_pendientes_comercio ); ?></li>
                                    <li><?php esc_html_e( 'Confirmados por Ti:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_confirmados_comercio ); ?></li>
                                    <li><?php esc_html_e( 'Utilizados en Pedidos (tus canjes confirmados):', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_utilizados_comercio ); ?></li>
                                </ul>
                                 <!-- Placeholder para gráfica -->
                                <div style="height:150px; width:100%; margin-top:15px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999;"><span><?php esc_html_e('Gráfica: Canjes por Estado (Próximamente)', 'wp-cupon-whatsapp'); ?></span></div>
                            </div>
                        </div>

                        <div class="postbox">
                             <h2 class="hndle"><span><?php esc_html_e( 'Mis Cupones', 'wp-cupon-whatsapp' ); ?></span></h2>
                             <div class="inside">
                                <?php $total_cupones_comercio = wpcw_stats_get_coupons_count_for_business($user_id); // Usa user_id aquí ?>
                                <p><strong><?php esc_html_e( 'Total de Cupones Asociados/Creados por Ti:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_cupones_comercio ); ?></p>
                                <?php // Podríamos listar algunos si fueran pocos, o un enlace a la gestión de cupones. ?>
                             </div>
                        </div>

                    </div>
                </div>

                <!-- Columna 2 -->
                <div id="postbox-container-2" class="postbox-container">
                    <div class="meta-box-sortables">
                         <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Top 5 Mis Cupones Más Canjeados (en este comercio)', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php $top_coupons_negocio = wpcw_stats_get_top_redeemed_coupons_for_business($business_cpt_id, 5); ?>
                                <?php if ( !empty($top_coupons_negocio) ) : ?>
                                    <table class="wp-list-table widefat striped" style="margin-top: 10px;">
                                        <thead><tr><th><?php esc_html_e( 'Nombre del Cupón', 'wp-cupon-whatsapp' ); ?></th><th><?php esc_html_e( 'Nro. Canjes', 'wp-cupon-whatsapp' ); ?></th></tr></thead>
                                        <tbody>
                                        <?php foreach ( $top_coupons_negocio as $coupon_stat ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( $coupon_stat->coupon_title ); ?> (ID: <?php echo esc_html($coupon_stat->cupon_id); ?>)</td>
                                                <td><?php echo esc_html( $coupon_stat->count ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p><?php esc_html_e( 'No hay datos de canjes para tus cupones en este comercio todavía.', 'wp-cupon-whatsapp' ); ?></p>
                                <?php endif; ?>
                                <!-- Placeholder para gráfica -->
                                <div style="height:150px; width:100%; margin-top:15px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999;"><span><?php esc_html_e('Gráfica: Top Cupones (Próximamente)', 'wp-cupon-whatsapp'); ?></span></div>
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
