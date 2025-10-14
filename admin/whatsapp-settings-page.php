<?php
/**
 * WPCW - WhatsApp Settings Page (wa.me version)
 *
 * @package WP_Cupon_Whatsapp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpcw_register_whatsapp_settings_menu() {
    add_submenu_page(
        'wpcw-main-dashboard',
        __( 'Configuración WhatsApp', 'wp-cupon-whatsapp' ),
        __( 'WhatsApp', 'wp-cupon-whatsapp' ),
        'manage_options',
        'wpcw-whatsapp-settings',
        'wpcw_render_whatsapp_settings_page'
    );
}
add_action( 'admin_menu', 'wpcw_register_whatsapp_settings_menu', 99 );

function wpcw_render_whatsapp_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'No tienes permisos para acceder a esta página.', 'wp-cupon-whatsapp' ) );
    }

    // Handle form submission
    if ( isset( $_POST['wpcw_save_whatsapp_settings'] ) ) {
        check_admin_referer( 'wpcw_whatsapp_settings_nonce' );

        $settings = array(
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'send_on_proposal' => isset($_POST['send_on_proposal']) ? 1 : 0,
            'send_on_counter_offer' => isset($_POST['send_on_counter_offer']) ? 1 : 0,
            'send_on_approval' => isset($_POST['send_on_approval']) ? 1 : 0,
            'send_on_rejection' => isset($_POST['send_on_rejection']) ? 1 : 0,
            'send_on_changes_requested' => isset($_POST['send_on_changes_requested']) ? 1 : 0,
            'send_on_redemption' => isset($_POST['send_on_redemption']) ? 1 : 0,
            'send_on_redemption_validation' => isset($_POST['send_on_redemption_validation']) ? 1 : 0
        );

        update_option('wpcw_whatsapp_settings', $settings);

        echo '<div class="notice notice-success is-dismissible"><p>' . __('Configuración guardada exitosamente', 'wp-cupon-whatsapp') . '</p></div>';
    }

    $settings = WPCW_WhatsApp_Links::get_settings();

    ?>
    <div class="wrap">
        <h1><?php _e( 'Configuración de WhatsApp', 'wp-cupon-whatsapp' ); ?></h1>

        <div class="notice notice-info">
            <p>
                <strong><?php _e('ℹ️ Cómo funciona:', 'wp-cupon-whatsapp'); ?></strong><br>
                <?php _e('Este sistema genera links de WhatsApp (wa.me) que pre-rellenan el mensaje. Los usuarios hacen clic en el link y se abre WhatsApp con el mensaje listo para enviar.', 'wp-cupon-whatsapp'); ?>
            </p>
            <p>
                <?php _e('✅ Ventajas: Gratis, ilimitado, no requiere API keys, funciona con cualquier número de WhatsApp.', 'wp-cupon-whatsapp'); ?>
            </p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'wpcw_whatsapp_settings_nonce' ); ?>

            <!-- Enable/Disable -->
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e( 'Habilitar WhatsApp', 'wp-cupon-whatsapp' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enabled" value="1" <?php checked( $settings['enabled'], 1 ); ?>>
                            <?php _e( 'Activar notificaciones por WhatsApp', 'wp-cupon-whatsapp' ); ?>
                        </label>
                        <p class="description"><?php _e('Cuando está habilitado, se mostrarán botones "Enviar por WhatsApp" en las notificaciones.', 'wp-cupon-whatsapp'); ?></p>
                    </td>
                </tr>
            </table>

            <!-- Notification Types -->
            <h2><?php _e( 'Tipos de Notificaciones', 'wp-cupon-whatsapp' ); ?></h2>
            <p class="description"><?php _e( 'Selecciona qué eventos deben mostrar botones de WhatsApp.', 'wp-cupon-whatsapp'); ?></p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e( 'Convenios', 'wp-cupon-whatsapp' ); ?></th>
                    <td>
                        <fieldset>
                            <label><input type="checkbox" name="send_on_proposal" value="1" <?php checked( $settings['send_on_proposal'], 1 ); ?>> <?php _e( 'Nueva propuesta de convenio', 'wp-cupon-whatsapp' ); ?></label><br>
                            <label><input type="checkbox" name="send_on_counter_offer" value="1" <?php checked( $settings['send_on_counter_offer'], 1 ); ?>> <?php _e( 'Contraoferta recibida', 'wp-cupon-whatsapp' ); ?></label><br>
                            <label><input type="checkbox" name="send_on_approval" value="1" <?php checked( $settings['send_on_approval'], 1 ); ?>> <?php _e( 'Convenio aprobado', 'wp-cupon-whatsapp' ); ?></label><br>
                            <label><input type="checkbox" name="send_on_rejection" value="1" <?php checked( $settings['send_on_rejection'], 1 ); ?>> <?php _e( 'Convenio rechazado', 'wp-cupon-whatsapp' ); ?></label><br>
                            <label><input type="checkbox" name="send_on_changes_requested" value="1" <?php checked( $settings['send_on_changes_requested'], 1 ); ?>> <?php _e( 'Cambios solicitados', 'wp-cupon-whatsapp' ); ?></label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Canjes', 'wp-cupon-whatsapp' ); ?></th>
                    <td>
                        <fieldset>
                            <label><input type="checkbox" name="send_on_redemption" value="1" <?php checked( $settings['send_on_redemption'], 1 ); ?>> <?php _e( 'Nuevo canje registrado', 'wp-cupon-whatsapp' ); ?></label><br>
                            <label><input type="checkbox" name="send_on_redemption_validation" value="1" <?php checked( $settings['send_on_redemption_validation'], 1 ); ?>> <?php _e( 'Código de validación enviado', 'wp-cupon-whatsapp' ); ?></label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="wpcw_save_whatsapp_settings" class="button button-primary" value="<?php esc_attr_e( 'Guardar Configuración', 'wp-cupon-whatsapp' ); ?>">
            </p>
        </form>

        <!-- Test Section -->
        <hr>
        <h2><?php _e( 'Prueba de WhatsApp', 'wp-cupon-whatsapp' ); ?></h2>
        <p class="description"><?php _e( 'Haz clic en el botón para probar el sistema de WhatsApp.', 'wp-cupon-whatsapp' ); ?></p>

        <?php
        $test_link = WPCW_WhatsApp_Links::generate_link(
            '+5493883349901',
            '🧪 Mensaje de prueba desde WP Cupón WhatsApp. ¡Tu configuración funciona correctamente!'
        );

        echo WPCW_WhatsApp_Links::render_button(
            $test_link,
            __('Enviar Mensaje de Prueba', 'wp-cupon-whatsapp'),
            array(
                'class' => 'button button-secondary',
                'style' => 'background: #25D366; color: white; border: none; padding: 10px 20px;'
            )
        );
        ?>

        <hr>
        <div class="wpcw-info-box" style="background: #fff; border: 1px solid #ddd; padding: 15px; margin-top: 20px; border-radius: 4px;">
            <h3><?php _e('📚 Documentación', 'wp-cupon-whatsapp'); ?></h3>
            <p><?php _e('Para que funcione correctamente, asegúrate de que:', 'wp-cupon-whatsapp'); ?></p>
            <ul style="list-style: disc; margin-left: 30px;">
                <li><?php _e('Los negocios e instituciones tienen números de teléfono configurados en sus perfiles', 'wp-cupon-whatsapp'); ?></li>
                <li><?php _e('Los números están en formato internacional (ej: +5493883349901)', 'wp-cupon-whatsapp'); ?></li>
                <li><?php _e('Los usuarios tienen WhatsApp instalado en sus dispositivos', 'wp-cupon-whatsapp'); ?></li>
            </ul>
            <p>
                <a href="https://faq.whatsapp.com/5913398998672934" target="_blank" class="button"><?php _e('Ver documentación de WhatsApp', 'wp-cupon-whatsapp'); ?></a>
            </p>
        </div>
    </div>
    <?php
}
