<?php
/**
 * WPCW WhatsApp Notifications System
 *
 * Handles WhatsApp notifications for convenios, approvals, and redemptions.
 * Supports multiple WhatsApp API providers: Twilio, WhatsApp Business API, Meta Cloud API, Wassenger.
 *
 * @package WP_Cupon_Whatsapp
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPCW_WhatsApp_Notifications
 *
 * Manages WhatsApp message sending through configured API provider.
 */
class WPCW_WhatsApp_Notifications {

    /**
     * WhatsApp API providers
     */
    const PROVIDER_TWILIO = 'twilio';
    const PROVIDER_WHATSAPP_BUSINESS = 'whatsapp_business';
    const PROVIDER_META_CLOUD = 'meta_cloud';
    const PROVIDER_WASSENGER = 'wassenger';

    /**
     * Initialize WhatsApp notifications system
     */
    public static function init() {
        // Hook to send WhatsApp when email is sent
        add_action('wp_mail', array(__CLASS__, 'send_whatsapp_parallel_to_email'), 10, 1);
    }

    /**
     * Get WhatsApp settings from options
     *
     * @return array Settings array
     */
    public static function get_settings() {
        $defaults = array(
            'enabled' => false,
            'provider' => self::PROVIDER_TWILIO,
            'twilio_account_sid' => '',
            'twilio_auth_token' => '',
            'twilio_phone_number' => '',
            'meta_access_token' => '',
            'meta_phone_number_id' => '',
            'wassenger_api_key' => '',
            'wassenger_device_id' => '',
            'send_on_proposal' => true,
            'send_on_counter_offer' => true,
            'send_on_approval' => true,
            'send_on_rejection' => true,
            'send_on_changes_requested' => true,
            'send_on_redemption' => true,
            'send_on_redemption_validation' => true
        );

        $settings = get_option('wpcw_whatsapp_settings', array());
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Update WhatsApp settings
     *
     * @param array $settings New settings
     * @return bool Success
     */
    public static function update_settings($settings) {
        return update_option('wpcw_whatsapp_settings', $settings);
    }

    /**
     * Check if WhatsApp notifications are enabled
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = self::get_settings();
        return !empty($settings['enabled']);
    }

    /**
     * Send WhatsApp message
     *
     * @param string $to Phone number (international format: +5493883349901)
     * @param string $message Message text
     * @param array  $options Optional parameters (buttons, media, etc)
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function send_message($to, $message, $options = array()) {
        if (!self::is_enabled()) {
            return new WP_Error('whatsapp_disabled', __('WhatsApp notifications are disabled', 'wp-cupon-whatsapp'));
        }

        $settings = self::get_settings();
        $provider = $settings['provider'];

        // Sanitize phone number
        $to = self::sanitize_phone_number($to);
        if (!$to) {
            return new WP_Error('invalid_phone', __('Invalid phone number', 'wp-cupon-whatsapp'));
        }

        // Log the attempt
        self::log_message('outgoing', $to, $message, $provider);

        // Send based on provider
        switch ($provider) {
            case self::PROVIDER_TWILIO:
                return self::send_via_twilio($to, $message, $options);

            case self::PROVIDER_META_CLOUD:
                return self::send_via_meta_cloud($to, $message, $options);

            case self::PROVIDER_WASSENGER:
                return self::send_via_wassenger($to, $message, $options);

            default:
                return new WP_Error('invalid_provider', __('Invalid WhatsApp provider', 'wp-cupon-whatsapp'));
        }
    }

    /**
     * Send message via Twilio WhatsApp API
     *
     * @param string $to Phone number
     * @param string $message Message text
     * @param array  $options Options
     * @return bool|WP_Error
     */
    private static function send_via_twilio($to, $message, $options) {
        $settings = self::get_settings();

        $account_sid = $settings['twilio_account_sid'];
        $auth_token = $settings['twilio_auth_token'];
        $from = 'whatsapp:' . $settings['twilio_phone_number'];
        $to = 'whatsapp:' . $to;

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token)
            ),
            'body' => array(
                'From' => $from,
                'To' => $to,
                'Body' => $message
            )
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error_code'])) {
            return new WP_Error('twilio_error', $body['message']);
        }

        return true;
    }

    /**
     * Send message via Meta Cloud API (WhatsApp Business Platform)
     *
     * @param string $to Phone number
     * @param string $message Message text
     * @param array  $options Options
     * @return bool|WP_Error
     */
    private static function send_via_meta_cloud($to, $message, $options) {
        $settings = self::get_settings();

        $access_token = $settings['meta_access_token'];
        $phone_number_id = $settings['meta_phone_number_id'];

        $url = "https://graph.facebook.com/v17.0/{$phone_number_id}/messages";

        $data = array(
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => array(
                'body' => $message
            )
        );

        // Add buttons if provided
        if (isset($options['buttons']) && !empty($options['buttons'])) {
            $data['type'] = 'interactive';
            $data['interactive'] = array(
                'type' => 'button',
                'body' => array('text' => $message),
                'action' => array(
                    'buttons' => $options['buttons']
                )
            );
            unset($data['text']);
        }

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data)
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('meta_error', $body['error']['message']);
        }

        return true;
    }

    /**
     * Send message via Wassenger API
     *
     * @param string $to Phone number
     * @param string $message Message text
     * @param array  $options Options
     * @return bool|WP_Error
     */
    private static function send_via_wassenger($to, $message, $options) {
        $settings = self::get_settings();

        $api_key = $settings['wassenger_api_key'];
        $device_id = $settings['wassenger_device_id'];

        $url = "https://api.wassenger.com/v1/messages";

        $data = array(
            'phone' => $to,
            'message' => $message,
            'device' => $device_id
        );

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Token' => $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data)
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('wassenger_error', $body['error']);
        }

        return true;
    }

    /**
     * Sanitize phone number to international format
     *
     * @param string $phone Phone number
     * @return string|false Sanitized phone or false
     */
    private static function sanitize_phone_number($phone) {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Ensure it starts with +
        if (substr($phone, 0, 1) !== '+') {
            // Try to add Argentina country code if looks local
            if (strlen($phone) == 10) {
                $phone = '+54' . $phone;
            } else {
                return false;
            }
        }

        // Basic validation: should be + followed by 8-15 digits
        if (!preg_match('/^\+\d{8,15}$/', $phone)) {
            return false;
        }

        return $phone;
    }

    /**
     * Get phone number for entity (business or institution)
     *
     * @param int $entity_id Post ID
     * @return string|false Phone number or false
     */
    public static function get_entity_phone($entity_id) {
        $phone = get_post_meta($entity_id, '_business_phone', true);
        if (!$phone) {
            $phone = get_post_meta($entity_id, '_institution_phone', true);
        }

        return $phone ? self::sanitize_phone_number($phone) : false;
    }

    /**
     * Get phone number for user
     *
     * @param int $user_id User ID
     * @return string|false Phone number or false
     */
    public static function get_user_phone($user_id) {
        $phone = get_user_meta($user_id, 'billing_phone', true);
        if (!$phone) {
            $phone = get_user_meta($user_id, 'phone', true);
        }

        return $phone ? self::sanitize_phone_number($phone) : false;
    }

    /**
     * Log WhatsApp message
     *
     * @param string $direction 'outgoing' or 'incoming'
     * @param string $phone Phone number
     * @param string $message Message content
     * @param string $provider Provider used
     */
    private static function log_message($direction, $phone, $message, $provider) {
        if (!defined('WPCW_DEBUG_MODE') || !WPCW_DEBUG_MODE) {
            return;
        }

        $log_entry = sprintf(
            "[%s] WhatsApp %s via %s to %s: %s\n",
            current_time('mysql'),
            $direction,
            $provider,
            $phone,
            substr($message, 0, 100)
        );

        error_log($log_entry);
    }

    // =================================================================
    // NOTIFICATION HELPERS FOR SPECIFIC EVENTS
    // =================================================================

    /**
     * Send convenio proposal notification via WhatsApp
     *
     * @param int $convenio_id Convenio post ID
     * @param int $recipient_entity_id Recipient business/institution ID
     */
    public static function notify_convenio_proposed($convenio_id, $recipient_entity_id) {
        $settings = self::get_settings();
        if (!$settings['send_on_proposal']) {
            return;
        }

        $phone = self::get_entity_phone($recipient_entity_id);
        if (!$phone) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);
        $provider_id = get_post_meta($convenio_id, '_convenio_provider_id', true);
        $provider_name = get_the_title($provider_id);

        $message = sprintf(
            "🤝 *Nueva Propuesta de Convenio*\n\n" .
            "Has recibido una propuesta de convenio de *%s*\n\n" .
            "Convenio: %s\n\n" .
            "Ingresa a tu panel para revisar los detalles y responder.",
            $provider_name,
            $convenio_title
        );

        self::send_message($phone, $message);
    }

    /**
     * Send counter-offer notification via WhatsApp
     *
     * @param int $convenio_id Convenio post ID
     * @param int $recipient_entity_id Recipient business/institution ID
     */
    public static function notify_counter_offer($convenio_id, $recipient_entity_id) {
        $settings = self::get_settings();
        if (!$settings['send_on_counter_offer']) {
            return;
        }

        $phone = self::get_entity_phone($recipient_entity_id);
        if (!$phone) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);
        $discount = get_post_meta($convenio_id, '_convenio_discount_percentage', true);

        $message = sprintf(
            "💬 *Nueva Contraoferta Recibida*\n\n" .
            "Convenio: %s\n" .
            "Descuento propuesto: %s%%\n\n" .
            "Revisa la contraoferta y responde en tu panel.",
            $convenio_title,
            $discount
        );

        self::send_message($phone, $message);
    }

    /**
     * Send convenio approval notification via WhatsApp
     *
     * @param int $convenio_id Convenio post ID
     * @param int $originator_entity_id Originator business/institution ID
     */
    public static function notify_convenio_approved($convenio_id, $originator_entity_id) {
        $settings = self::get_settings();
        if (!$settings['send_on_approval']) {
            return;
        }

        $phone = self::get_entity_phone($originator_entity_id);
        if (!$phone) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);

        $message = sprintf(
            "✅ *¡Convenio Aprobado!*\n\n" .
            "El convenio *%s* ha sido aprobado y está ahora activo.\n\n" .
            "Ya puedes comenzar a crear cupones asociados a este convenio.\n\n" .
            "¡Felicitaciones! 🎉",
            $convenio_title
        );

        self::send_message($phone, $message);
    }

    /**
     * Send convenio rejection notification via WhatsApp
     *
     * @param int    $convenio_id Convenio post ID
     * @param int    $originator_entity_id Originator business/institution ID
     * @param string $reason Rejection reason
     */
    public static function notify_convenio_rejected($convenio_id, $originator_entity_id, $reason) {
        $settings = self::get_settings();
        if (!$settings['send_on_rejection']) {
            return;
        }

        $phone = self::get_entity_phone($originator_entity_id);
        if (!$phone) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);

        $message = sprintf(
            "❌ *Convenio Rechazado*\n\n" .
            "El convenio *%s* ha sido rechazado.\n\n" .
            "Motivo: %s\n\n" .
            "Puedes revisar y proponer un nuevo convenio con ajustes.",
            $convenio_title,
            $reason
        );

        self::send_message($phone, $message);
    }

    /**
     * Send supervisor approval needed notification via WhatsApp
     *
     * @param int $convenio_id Convenio post ID
     * @param int $supervisor_user_id Supervisor user ID
     */
    public static function notify_supervisor_approval_needed($convenio_id, $supervisor_user_id) {
        $settings = self::get_settings();
        if (!$settings['send_on_approval']) {
            return;
        }

        $phone = self::get_user_phone($supervisor_user_id);
        if (!$phone) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);

        $message = sprintf(
            "⚠️ *Aprobación de Supervisor Requerida*\n\n" .
            "El convenio *%s* requiere tu aprobación.\n\n" .
            "Por favor, revisa y aprueba en tu panel lo antes posible.",
            $convenio_title
        );

        self::send_message($phone, $message);
    }

    /**
     * Send changes requested notification via WhatsApp
     *
     * @param int    $convenio_id Convenio post ID
     * @param int    $originator_entity_id Originator business/institution ID
     * @param string $feedback Supervisor feedback
     */
    public static function notify_changes_requested($convenio_id, $originator_entity_id, $feedback) {
        $settings = self::get_settings();
        if (!$settings['send_on_changes_requested']) {
            return;
        }

        $phone = self::get_entity_phone($originator_entity_id);
        if (!$phone) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);

        $message = sprintf(
            "📝 *Cambios Solicitados en Convenio*\n\n" .
            "Convenio: %s\n\n" .
            "Feedback del supervisor:\n%s\n\n" .
            "Por favor, edita el convenio según las indicaciones.",
            $convenio_title,
            wp_trim_words($feedback, 30)
        );

        self::send_message($phone, $message);
    }

    /**
     * Send redemption notification via WhatsApp
     *
     * @param int $redemption_id Redemption ID
     */
    public static function notify_redemption($redemption_id) {
        $settings = self::get_settings();
        if (!$settings['send_on_redemption']) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_canjes';

        $redemption = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $redemption_id
        ));

        if (!$redemption) {
            return;
        }

        // Get business phone
        $business_id = $redemption->business_id;
        $phone = self::get_entity_phone($business_id);
        if (!$phone) {
            return;
        }

        $coupon_title = get_the_title($redemption->coupon_id);
        $beneficiary_name = $redemption->beneficiary_name;

        $message = sprintf(
            "🎟️ *Nuevo Canje Registrado*\n\n" .
            "Cupón: %s\n" .
            "Beneficiario: %s\n" .
            "Código: %s\n\n" .
            "Recuerda validar el canje cuando el beneficiario se presente.",
            $coupon_title,
            $beneficiary_name,
            $redemption->redemption_code
        );

        self::send_message($phone, $message);
    }

    /**
     * Send redemption validation code via WhatsApp
     *
     * @param int    $redemption_id Redemption ID
     * @param string $validation_code Validation code
     */
    public static function send_validation_code($redemption_id, $validation_code) {
        $settings = self::get_settings();
        if (!$settings['send_on_redemption_validation']) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_canjes';

        $redemption = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $redemption_id
        ));

        if (!$redemption) {
            return;
        }

        // Get beneficiary phone
        $phone = self::sanitize_phone_number($redemption->beneficiary_phone);
        if (!$phone) {
            return;
        }

        $coupon_title = get_the_title($redemption->coupon_id);
        $business_name = get_the_title($redemption->business_id);

        $message = sprintf(
            "🔐 *Código de Validación de Cupón*\n\n" .
            "Cupón: %s\n" .
            "Negocio: %s\n\n" .
            "Tu código de validación es:\n*%s*\n\n" .
            "Proporciona este código al negocio para validar tu canje.",
            $coupon_title,
            $business_name,
            $validation_code
        );

        self::send_message($phone, $message);
    }

    /**
     * Send redemption validated confirmation via WhatsApp
     *
     * @param int $redemption_id Redemption ID
     */
    public static function notify_redemption_validated($redemption_id) {
        $settings = self::get_settings();
        if (!$settings['send_on_redemption_validation']) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_canjes';

        $redemption = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $redemption_id
        ));

        if (!$redemption) {
            return;
        }

        // Get beneficiary phone
        $phone = self::sanitize_phone_number($redemption->beneficiary_phone);
        if (!$phone) {
            return;
        }

        $coupon_title = get_the_title($redemption->coupon_id);
        $business_name = get_the_title($redemption->business_id);

        $message = sprintf(
            "✅ *Cupón Validado Exitosamente*\n\n" .
            "Cupón: %s\n" .
            "Negocio: %s\n\n" .
            "Tu cupón ha sido validado y aplicado.\n\n" .
            "¡Disfruta tu beneficio! 🎉",
            $coupon_title,
            $business_name
        );

        self::send_message($phone, $message);
    }
}

// Initialize WhatsApp notifications
WPCW_WhatsApp_Notifications::init();
