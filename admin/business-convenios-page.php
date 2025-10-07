<?php
/**
 * WPCW - Business Owner Convenios Management Page
 *
 * This file renders the page for a Business Owner to manage their convenios.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Renders the content of the Business Owner's convenios page.
 */
function wpcw_render_business_convenios_page() {
    ?>
    <div class="wrap wpcw-dashboard-wrap">
        <h1><span class="dashicons dashicons-businesswoman"></span> <?php _e( 'Gestión de Convenios', 'wp-cupon-whatsapp' ); ?></h1>
        <p><?php _e( 'Aquí puede proponer nuevos convenios a instituciones y negocios, y gestionar sus alianzas activas.', 'wp-cupon-whatsapp' ); ?></p>

        <div class="wpcw-page-actions">
            <button id="propose-convenio-btn" class="button button-primary"><?php _e( 'Proponer Nuevo Convenio', 'wp-cupon-whatsapp' ); ?></button>
        </div>

        <!-- Formulario de Propuesta (Oculto por defecto) -->
        <div id="propose-convenio-form-wrap" class="postbox" style="display: none; margin-top: 20px;">
            <h2 class="hndle"><span><?php _e( 'Nueva Propuesta de Convenio', 'wp-cupon-whatsapp' ); ?></span></h2>
            <div class="inside">
                <form id="propose-convenio-form" method="post">
                    <?php
                    // Security Nonce by El Guardián
                    wp_nonce_field( 'wpcw_propose_convenio_nonce', 'wpcw_nonce' );
                    ?>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="wpcw_institution_id"><?php _e( 'Proponer a la Institución', 'wp-cupon-whatsapp' ); ?></label>
                                </th>
                                <td>
                                    <?php
                                    // Data by El Artesano
                                    $institutions = WPCW_Institution_Manager::get_all_institutions();
                                    if ( ! empty( $institutions ) ) {
                                        echo '<select name="wpcw_institution_id" id="wpcw_institution_id" class="regular-text" required>';
                                        echo '<option value="">' . __( '-- Seleccionar Institución --', 'wp-cupon-whatsapp' ) . '</option>';
                                        foreach ( $institutions as $id => $name ) {
                                            echo '<option value="' . esc_attr( $id ) . '">' . esc_html( $name ) . '</option>';
                                        }
                                        echo '</select>';
                                    } else {
                                        echo '<em>' . __( 'No hay instituciones disponibles para proponer un convenio.', 'wp-cupon-whatsapp' ) . '</em>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wpcw_convenio_terms"><?php _e( 'Términos del Beneficio', 'wp-cupon-whatsapp' ); ?></label>
                                </th>
                                <td>
                                    <textarea name="wpcw_convenio_terms" id="wpcw_convenio_terms" rows="5" class="large-text" placeholder="Ej: 15% de descuento en todos los productos para los miembros de la institución." required></textarea>
                                    <p class="description"><?php _e( 'Describe claramente el beneficio que ofreces.', 'wp-cupon-whatsapp' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" name="submit_propose_convenio" id="submit_propose_convenio" class="button button-primary" value="<?php _e( 'Enviar Propuesta', 'wp-cupon-whatsapp' ); ?>">
                        <button type="button" id="cancel-propose-convenio" class="button button-secondary"><?php _e( 'Cancelar', 'wp-cupon-whatsapp' ); ?></button>
                    </p>
                </form>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#propose-convenio-btn').on('click', function() {
                    $('#propose-convenio-form-wrap').slideDown();
                    $(this).hide();
                });

                $('#cancel-propose-convenio').on('click', function() {
                    $('#propose-convenio-form-wrap').slideUp();
                    $('#propose-convenio-btn').show();
                });
            });
        </script>

        <div id="poststuff" style="margin-top: 20px;">
            
            <!-- Marcador para Convenios Activos -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e( 'Convenios Activos', 'wp-cupon-whatsapp' ); ?></span></h2>
                <div class="inside">
                    <p><?php _e( 'Aquí se mostrará una tabla con los convenios que ha propuesto y han sido aceptados, o los que ha aceptado de otros.', 'wp-cupon-whatsapp' ); ?></p>
                </div>
            </div>

            <!-- Marcador para Convenios Pendientes -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e( 'Convenios Pendientes de Aceptación', 'wp-cupon-whatsapp' ); ?></span></h2>
                <div class="inside">
                    <p><?php _e( 'Aquí se mostrarán las propuestas de convenio que ha enviado y están esperando respuesta, y las que ha recibido y necesitan su aprobación.', 'wp-cupon-whatsapp' ); ?></p>
                </div>
            </div>

        </div>

    </div>
    <?php
}

/**
 * Adds the Business Convenios submenu page, visible only to Business Owners.
 */
function wpcw_add_business_convenios_menu() {
    // This capability is defined in our WPCW_Roles_Manager class
    $capability = 'manage_business_profile';

    if ( current_user_can( $capability ) ) {
        add_submenu_page(
            'wpcw-main-dashboard',                  // Slug del menú padre
            __( 'Convenios', 'wp-cupon-whatsapp' ), // Título de la página
            __( 'Convenios', 'wp-cupon-whatsapp' ), // Título del menú
            $capability,                            // Capacidad requerida
            'wpcw-business-convenios',              // Slug del menú
            'wpcw_render_business_convenios_page',  // Callback para renderizar
            2 // Posición dentro del submenú
        );
    }
}
add_action( 'admin_menu', 'wpcw_add_business_convenios_menu', 11 );
