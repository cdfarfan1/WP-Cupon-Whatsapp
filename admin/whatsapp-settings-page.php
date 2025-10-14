<?php
/**
 * WPCW - WhatsApp Settings Page
 *
 * Configuration page for WhatsApp API integration
 *
 * @package WP_Cupon_Whatsapp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register WhatsApp settings menu
 */
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

/**
 * Render WhatsApp settings page
 */
function wpcw_render_whatsapp_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'No tienes permisos para acceder a esta página.', 'wp-cupon-whatsapp' ) );
    }

    // Handle form submission
    if ( isset( $_POST['wpcw_save_whatsapp_settings'] ) ) {
        check_admin_referer( 'wpcw_whatsapp_settings_nonce' );

        $settings = array(
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'provider' => sanitize_text_field($_POST['provider']),
            'twilio_account_sid' => sanitize_text_field($_POST['twilio_account_sid']),
            'twilio_auth_token' => sanitize_text_field($_POST['twilio_auth_token']),
            'twilio_phone_number' => sanitize_text_field($_POST['twilio_phone_number']),
            'meta_access_token' => sanitize_text_field($_POST['meta_access_token']),
            'meta_phone_number_id' => sanitize_text_field($_POST['meta_phone_number_id']),
            'wassenger_api_key' => sanitize_text_field($_POST['wassenger_api_key']),
            'wassenger_device_id' => sanitize_text_field($_POST['wassenger_device_id']),
            'send_on_proposal' => isset($_POST['send_on_proposal']) ? 1 : 0,
            'send_on_counter_offer' => isset($_POST['send_on_counter_offer']) ? 1 : 0,
            'send_on_approval' => isset($_POST['send_on_approval']) ? 1 : 0,
            'send_on_rejection' => isset($_POST['send_on_rejection']) ? 1 : 0,
            'send_on_changes_requested' => isset($_POST['send_on_changes_requested']) ? 1 : 0,
            'send_on_redemption' => isset($_POST['send_on_redemption']) ? 1 : 0,
            'send_on_redemption_validation' => isset($_POST['send_on_redemption_validation']) ? 1 : 0
        );

        WPCW_WhatsApp_Notifications::update_settings($settings);

        echo '<div class="notice notice-success is-dismissible"><p>' . __('Configuración guardada exitosamente', 'wp-cupon-whatsapp') . '</p></div>';
    }

    // Handle test message
    if (isset($_POST['wpcw_test_whatsapp'])) {
        check_admin_referer('wpcw_test_whatsapp_nonce');

        $test_phone = sanitize_text_field($_POST['test_phone']);
        $result = WPCW_WhatsApp_Notifications::send_message($test_phone, '🧪 Mensaje de prueba desde WP Cupón WhatsApp. ¡Tu configuración funciona correctamente!');

        if (is_wp_error($result)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Error al enviar mensaje: ', 'wp-cupon-whatsapp') . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Mensaje de prueba enviado exitosamente', 'wp-cupon-whatsapp') . '</p></div>';
        }
    }

    $settings = WPCW_WhatsApp_Notifications::get_settings();

    ?>
    <div class="wrap">
        <h1><?php _e( 'Configuración de WhatsApp', 'wp-cupon-whatsapp' ); ?></h1>

        <p class="description">
            <?php _e( 'Configura tu proveedor de WhatsApp API para enviar notificaciones automáticas sobre convenios, aprobaciones y canjes.', 'wp-cupon-whatsapp' ); ?>
        </p>

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
                    </td>
                </tr>
            </table>

            <!-- Provider Selection -->
            <h2><?php _e( 'Proveedor de API', 'wp-cupon-whatsapp' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e( 'Proveedor', 'wp-cupon-whatsapp' ); ?></th>
                    <td>
                        <select name="provider" id="wpcw-provider-select">
                            <option value="twilio" <?php selected( $settings['provider'], 'twilio' ); ?>>Twilio WhatsApp API</option>
                            <option value="meta_cloud" <?php selected( $settings['provider'], 'meta_cloud' ); ?>>Meta Cloud API (WhatsApp Business)</option>
                            <option value="wassenger" <?php selected( $settings['provider'], 'wassenger' ); ?>>Wassenger</option>
                        </select>
                        <p class="description">
                            <?php _e( 'Selecciona tu proveedor de API de WhatsApp.', 'wp-cupon-whatsapp' ); ?>
                            <a href="https://www.twilio.com/whatsapp" target="_blank">Twilio</a> |
                            <a href="https://developers.facebook.com/docs/whatsapp/cloud-api" target="_blank">Meta Cloud API</a> |
                            <a href="https://wassenger.com/" target="_blank">Wassenger</a>
                        </p>
                    </td>
                </tr>
            </table>

            <!-- Twilio Settings -->
            <div id="twilio-settings" class="provider-settings">
                <h3><?php _e( 'Configuración de Twilio', 'wp-cupon-whatsapp' ); ?></h3>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="twilio_account_sid"><?php _e( 'Account SID', 'wp-cupon-whatsapp' ); ?></label></th>
                        <td>
                            <input type="text" id="twilio_account_sid" name="twilio_account_sid" value="<?php echo esc_attr( $settings['twilio_account_sid'] ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Obtén tu Account SID desde el panel de Twilio.', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="twilio_auth_token"><?php _e( 'Auth Token', 'wp-cupon-whatsapp' ); ?></label></th>
                        <td>
                            <input type="password" id="twilio_auth_token" name="twilio_auth_token" value="<?php echo esc_attr( $settings['twilio_auth_token'] ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Tu Auth Token de Twilio (se mantiene oculto).', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="twilio_phone_number"><?php _e( 'Número de WhatsApp', 'wp-cupon-whatsapp' ); ?></label></th>
                        <td>
                            <input type="text" id="twilio_phone_number" name="twilio_phone_number" value="<?php echo esc_attr( $settings['twilio_phone_number'] ); ?>" class="regular-text" placeholder="+14155238886">
                            <p class="description"><?php _e( 'Tu número de WhatsApp de Twilio (formato internacional con +).', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Meta Cloud API Settings -->
            <div id="meta-settings" class="provider-settings" style="display:none;">
                <h3><?php _e( 'Configuración de Meta Cloud API', 'wp-cupon-whatsapp' ); ?></h3>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="meta_access_token"><?php _e( 'Access Token', 'wp-cupon-whatsapp' ); ?></label></th>
                        <td>
                            <input type="password" id="meta_access_token" name="meta_access_token" value="<?php echo esc_attr( $settings['meta_access_token'] ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Tu access token de WhatsApp Business API.', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="meta_phone_number_id"><?php _e( 'Phone Number ID', 'wp-cupon-whatsapp' ); ?></label></th>
                        <td>
                            <input type="text" id="meta_phone_number_id" name="meta_phone_number_id" value="<?php echo esc_attr( $settings['meta_phone_number_id'] ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'El ID de tu número de teléfono registrado en WhatsApp Business.', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Wassenger Settings -->
            <div id="wassenger-settings" class="provider-settings" style="display:none;">
                <h3><?php _e( 'Configuración de Wassenger', 'wp-cupon-whatsapp' ); ?></h3>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="wassenger_api_key"><?php _e( 'API Key', 'wp-cupon-whatsapp' ); ?></label></th>
                        <td>
                            <input type="password" id="wassenger_api_key" name="wassenger_api_key" value="<?php echo esc_attr( $settings['wassenger_api_key'] ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Tu API key de Wassenger.', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wassenger_device_id"><?php _e( 'Device ID', 'wp-cupon-whatsapp' ); ?></label></th>
                        <td>
                            <input type="text" id="wassenger_device_id" name="wassenger_device_id" value="<?php echo esc_attr( $settings['wassenger_device_id'] ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'El ID del dispositivo desde el cual enviarás mensajes.', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Notification Types -->
            <h2><?php _e( 'Tipos de Notificaciones', 'wp-cupon-whatsapp' ); ?></h2>
            <p class="description"><?php _e( 'Selecciona qué eventos deben generar notificaciones de WhatsApp.', 'wp-cupon-whatsapp' ); ?></p>
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
        <h2><?php _e( 'Prueba de Envío', 'wp-cupon-whatsapp' ); ?></h2>
        <p class="description"><?php _e( 'Envía un mensaje de prueba para verificar que tu configuración funciona correctamente.', 'wp-cupon-whatsapp' ); ?></p>

        <form method="post" action="">
            <?php wp_nonce_field( 'wpcw_test_whatsapp_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="test_phone"><?php _e( 'Número de prueba', 'wp-cupon-whatsapp' ); ?></label></th>
                    <td>
                        <input type="text" id="test_phone" name="test_phone" value="+5493883349901" class="regular-text" placeholder="+5493883349901">
                        <p class="description"><?php _e( 'Número en formato internacional (ej: +5493883349901)', 'wp-cupon-whatsapp' ); ?></p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="wpcw_test_whatsapp" class="button" value="<?php esc_attr_e( 'Enviar Mensaje de Prueba', 'wp-cupon-whatsapp' ); ?>">
            </p>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function toggleProviderSettings() {
            const provider = $('#wpcw-provider-select').val();
            $('.provider-settings').hide();
            $('#' + provider.replace('_', '-') + '-settings').show();
        }

        toggleProviderSettings();
        $('#wpcw-provider-select').on('change', toggleProviderSettings);
    });
    </script>

    <style>
    .provider-settings {
        background: #f9f9f9;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .provider-settings h3 {
        margin-top: 0;
    }
    </style>
    <?php
}
