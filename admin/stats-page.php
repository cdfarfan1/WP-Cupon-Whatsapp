<?php
/**
 * WP Canje Cupon Whatsapp - Superadmin Statistics Page
 *
 * Renders the main statistics page for superadmins.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders the content for the Superadmin Statistics page.
 */
function wpcw_render_superadmin_stats_page() {
    // Asegurarse de que las funciones de estadísticas estén disponibles
    // Estas funciones se encuentran en includes/stats-functions.php, que ya debería estar incluido.
    if ( ! function_exists('wpcw_stats_get_total_canjes') ||
         ! function_exists('wpcw_stats_get_total_cpt_count') ||
         ! function_exists('wpcw_stats_get_top_coupons_redeemed') ||
         ! function_exists('wpcw_stats_get_top_businesses_by_redemptions') ) {
        echo '<div class="wrap"><p>' . esc_html__('Error: Las funciones de estadísticas necesarias no están disponibles. Asegúrate de que el archivo includes/stats-functions.php esté cargado correctamente.', 'wp-cupon-whatsapp') . '</p></div>';
        return;
    }
    ?>
    <div class="wrap wpcw-stats-page">
        <h1><?php esc_html_e( 'Estadísticas Generales - WP Canje Cupon', 'wp-cupon-whatsapp' ); ?></h1>

        <div id="wpcw-dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">

                <!-- Columna 1 -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="meta-box-sortables">

                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Resumen de Canjes', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php
                                $total_canjes = wpcw_stats_get_total_canjes();
                                $canjes_pendientes = wpcw_stats_get_total_canjes(array('status' => 'pendiente_confirmacion'));
                                $canjes_confirmados = wpcw_stats_get_total_canjes(array('status' => 'confirmado_por_negocio'));
                                $canjes_utilizados = wpcw_stats_get_total_canjes(array('status' => 'utilizado_en_pedido_wc'));
                                ?>
                                <ul style="list-style-type: disc; margin-left: 20px;">
                                    <li><strong><?php esc_html_e( 'Total de Solicitudes de Canje:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_canjes ); ?></li>
                                    <li><?php esc_html_e( 'Pendientes de Confirmación:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_pendientes ); ?></li>
                                    <li><?php esc_html_e( 'Confirmados por Negocio:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_confirmados ); ?></li>
                                    <li><?php esc_html_e( 'Utilizados en Pedidos WC:', 'wp-cupon-whatsapp' ); ?> <?php echo esc_html( $canjes_utilizados ); ?></li>
                                </ul>
                                <!-- Placeholder para gráfica de estados de canje -->
                                <div id="wpcw-chart-canjes-status-placeholder" style="height:200px; width:100%; margin-top:15px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999;"><span><?php esc_html_e('Gráfica: Canjes por Estado (Próximamente)', 'wp-cupon-whatsapp'); ?></span></div>
                            </div>
                        </div>

                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Resumen de Entidades', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php
                                $total_comercios = wpcw_stats_get_total_cpt_count('wpcw_business');
                                $total_instituciones = wpcw_stats_get_total_cpt_count('wpcw_institution');
                                $total_cupones_lealtad = wpcw_stats_get_total_cpt_count('shop_coupon', array(array('key'=>'_wpcw_is_loyalty_coupon', 'value'=>'yes', 'compare'=>'=')));
                                $total_cupones_publicos = wpcw_stats_get_total_cpt_count('shop_coupon', array(array('key'=>'_wpcw_is_public_coupon', 'value'=>'yes', 'compare'=>'=')));
                                ?>
                                <ul style="list-style-type: disc; margin-left: 20px;">
                                    <li><strong><?php esc_html_e( 'Comercios Registrados:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_comercios ); ?></li>
                                    <li><strong><?php esc_html_e( 'Instituciones Registradas:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_instituciones ); ?></li>
                                    <li><strong><?php esc_html_e( 'Cupones de Lealtad Activos:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_cupones_lealtad ); ?></li>
                                    <li><strong><?php esc_html_e( 'Cupones Públicos Activos:', 'wp-cupon-whatsapp' ); ?></strong> <?php echo esc_html( $total_cupones_publicos ); ?></li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Columna 2 -->
                <div id="postbox-container-2" class="postbox-container">
                    <div class="meta-box-sortables">

                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Top 5 Cupones Más Canjeados', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php $top_coupons = wpcw_stats_get_top_coupons_redeemed(5); ?>
                                <?php if ( !empty($top_coupons) ) : ?>
                                    <table class="wp-list-table widefat striped" style="margin-top: 10px;">
                                        <thead><tr><th><?php esc_html_e( 'Nombre del Cupón', 'wp-cupon-whatsapp' ); ?></th><th><?php esc_html_e( 'Nro. Canjes', 'wp-cupon-whatsapp' ); ?></th></tr></thead>
                                        <tbody>
                                        <?php foreach ( $top_coupons as $coupon_stat ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( $coupon_stat->coupon_title ); ?> (ID: <?php echo esc_html($coupon_stat->cupon_id); ?>)</td>
                                                <td><?php echo esc_html( $coupon_stat->count ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p><?php esc_html_e( 'No hay datos de canjes de cupones todavía.', 'wp-cupon-whatsapp' ); ?></p>
                                <?php endif; ?>
                                <!-- Placeholder para gráfica de top cupones -->
                                <div id="wpcw-chart-top-coupons-placeholder" style="height:200px; width:100%; margin-top:15px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; color:#999;"><span><?php esc_html_e('Gráfica: Top Cupones (Próximamente)', 'wp-cupon-whatsapp'); ?></span></div>
                            </div>
                        </div>

                        <div class="postbox">
                            <h2 class="hndle"><span><?php esc_html_e( 'Top 5 Comercios por Canjes Confirmados', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <?php $top_businesses = wpcw_stats_get_top_businesses_by_redemptions(5); ?>
                                <?php if ( !empty($top_businesses) ) : ?>
                                    <table class="wp-list-table widefat striped" style="margin-top: 10px;">
                                        <thead><tr><th><?php esc_html_e( 'Nombre del Comercio', 'wp-cupon-whatsapp' ); ?></th><th><?php esc_html_e( 'Nro. Canjes', 'wp-cupon-whatsapp' ); ?></th></tr></thead>
                                        <tbody>
                                        <?php foreach ( $top_businesses as $business_stat ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( $business_stat->business_name ); ?> (ID: <?php echo esc_html($business_stat->comercio_id); ?>)</td>
                                                <td><?php echo esc_html( $business_stat->count ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p><?php esc_html_e( 'No hay datos de canjes asociados a comercios todavía.', 'wp-cupon-whatsapp' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div><!-- #dashboard-widgets -->
        </div><!-- #wpcw-dashboard-widgets-wrap -->
    </div><!-- .wrap -->
    <?php
}

// El wrapper wpcw_render_superadmin_stats_page_content_wrapper ya está en admin-menu.php
// y llamará a wpcw_render_superadmin_stats_page() si existe.

?>
