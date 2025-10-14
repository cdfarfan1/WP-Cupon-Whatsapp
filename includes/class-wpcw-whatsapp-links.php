<?php
/**
 * WPCW WhatsApp Links Generator (wa.me)
 *
 * Genera links wa.me para notificaciones de WhatsApp sin necesidad de API.
 * Basado en WhatsApp Click to Chat API: https://faq.whatsapp.com/5913398998672934
 *
 * @package WP_Cupon_Whatsapp
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_WhatsApp_Links {

    /**
     * Generar link wa.me con mensaje pre-rellenado
     *
     * @param string $phone Número en formato internacional (+5493883349901)
     * @param string $message Mensaje pre-rellenado
     * @param array  $options Opciones adicionales (analytics, etc)
     * @return string URL completa de wa.me
     */
    public static function generate_link($phone, $message, $options = array()) {
        // Sanitizar número (solo dígitos y +)
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Remover + para wa.me (wa.me/5493883349901, NO wa.me/+5493883349901)
        $phone = ltrim($phone, '+');

        // URL encode del mensaje
        $encoded_message = urlencode($message);

        // Construir URL
        $url = "https://wa.me/{$phone}?text={$encoded_message}";

        // Agregar parámetros de tracking si se especifican
        if (isset($options['utm_source'])) {
            $url .= '&utm_source=' . urlencode($options['utm_source']);
        }

        return $url;
    }

    /**
     * Generar link para notificación de convenio propuesto
     *
     * @param int $convenio_id ID del convenio
     * @param int $entity_id ID de la entidad (business/institution)
     * @return string|false URL de wa.me o false si no hay teléfono
     */
    public static function get_convenio_proposed_link($convenio_id, $entity_id) {
        $phone = self::get_entity_phone($entity_id);
        if (!$phone) {
            return false;
        }

        $convenio_title = get_the_title($convenio_id);
        $provider_id = get_post_meta($convenio_id, '_convenio_provider_id', true);
        $provider_name = get_the_title($provider_id);
        $convenio_url = admin_url('post.php?post=' . $convenio_id . '&action=edit');

        $message = sprintf(
            "🤝 *Nueva Propuesta de Convenio*\n\n" .
            "Has recibido una propuesta de convenio de *%s*\n\n" .
            "Convenio: %s\n\n" .
            "Revisa los detalles aquí:\n%s",
            $provider_name,
            $convenio_title,
            $convenio_url
        );

        return self::generate_link($phone, $message, array(
            'utm_source' => 'wpcw_convenio_notification'
        ));
    }

    /**
     * Generar link para contraoferta
     *
     * @param int $convenio_id ID del convenio
     * @param int $entity_id ID de la entidad
     * @return string|false URL de wa.me
     */
    public static function get_counter_offer_link($convenio_id, $entity_id) {
        $phone = self::get_entity_phone($entity_id);
        if (!$phone) {
            return false;
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

        return self::generate_link($phone, $message);
    }

    /**
     * Generar link para aprobación de supervisor
     *
     * @param int $convenio_id ID del convenio
     * @param int $supervisor_id ID del supervisor
     * @return string|false URL de wa.me o false
     */
    public static function get_supervisor_approval_link($convenio_id, $supervisor_id) {
        $phone = self::get_user_phone($supervisor_id);
        if (!$phone) {
            return false;
        }

        $convenio_title = get_the_title($convenio_id);
        $convenio_url = admin_url('post.php?post=' . $convenio_id . '&action=edit');

        $message = sprintf(
            "⚠️ *Aprobación de Supervisor Requerida*\n\n" .
            "El convenio *%s* requiere tu aprobación.\n\n" .
            "Por favor, revisa y aprueba aquí:\n%s",
            $convenio_title,
            $convenio_url
        );

        return self::generate_link($phone, $message);
    }

    /**
     * Generar link para convenio aprobado
     *
     * @param int $convenio_id ID del convenio
     * @param int $entity_id ID de la entidad
     * @return string|false URL de wa.me
     */
    public static function get_convenio_approved_link($convenio_id, $entity_id) {
        $phone = self::get_entity_phone($entity_id);
        if (!$phone) {
            return false;
        }

        $convenio_title = get_the_title($convenio_id);

        $message = sprintf(
            "✅ *¡Convenio Aprobado!*\n\n" .
            "El convenio *%s* ha sido aprobado y está ahora activo.\n\n" .
            "Ya puedes comenzar a crear cupones asociados a este convenio.\n\n" .
            "¡Felicitaciones! 🎉",
            $convenio_title
        );

        return self::generate_link($phone, $message);
    }

    /**
     * Generar link para convenio rechazado
     *
     * @param int    $convenio_id ID del convenio
     * @param int    $entity_id ID de la entidad
     * @param string $reason Motivo del rechazo
     * @return string|false URL de wa.me
     */
    public static function get_convenio_rejected_link($convenio_id, $entity_id, $reason) {
        $phone = self::get_entity_phone($entity_id);
        if (!$phone) {
            return false;
        }

        $convenio_title = get_the_title($convenio_id);

        $message = sprintf(
            "❌ *Convenio Rechazado*\n\n" .
            "El convenio *%s* ha sido rechazado.\n\n" .
            "Motivo: %s\n\n" .
            "Puedes revisar y proponer un nuevo convenio con ajustes.",
            $convenio_title,
            wp_trim_words($reason, 20)
        );

        return self::generate_link($phone, $message);
    }

    /**
     * Generar link para cambios solicitados
     *
     * @param int    $convenio_id ID del convenio
     * @param int    $entity_id ID de la entidad
     * @param string $feedback Feedback del supervisor
     * @return string|false URL de wa.me
     */
    public static function get_changes_requested_link($convenio_id, $entity_id, $feedback) {
        $phone = self::get_entity_phone($entity_id);
        if (!$phone) {
            return false;
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

        return self::generate_link($phone, $message);
    }

    /**
     * Generar link para código de validación de canje
     *
     * @param int    $redemption_id ID del canje
     * @param string $validation_code Código de validación
     * @return string|false URL de wa.me
     */
    public static function get_validation_code_link($redemption_id, $validation_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_canjes';

        $redemption = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $redemption_id
        ));

        if (!$redemption) {
            return false;
        }

        $phone = self::sanitize_phone_number($redemption->beneficiary_phone);
        if (!$phone) {
            return false;
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

        return self::generate_link($phone, $message);
    }

    /**
     * Generar link para notificación de nuevo canje
     *
     * @param int $redemption_id ID del canje
     * @return string|false URL de wa.me
     */
    public static function get_redemption_notification_link($redemption_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_canjes';

        $redemption = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $redemption_id
        ));

        if (!$redemption) {
            return false;
        }

        $phone = self::get_entity_phone($redemption->business_id);
        if (!$phone) {
            return false;
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

        return self::generate_link($phone, $message);
    }

    /**
     * Obtener teléfono de entidad (business o institution)
     *
     * @param int $entity_id ID de la entidad
     * @return string|false Teléfono sanitizado o false
     */
    private static function get_entity_phone($entity_id) {
        $phone = get_post_meta($entity_id, '_business_phone', true);
        if (!$phone) {
            $phone = get_post_meta($entity_id, '_institution_phone', true);
        }

        return $phone ? self::sanitize_phone_number($phone) : false;
    }

    /**
     * Obtener teléfono de usuario
     *
     * @param int $user_id ID del usuario
     * @return string|false Teléfono sanitizado o false
     */
    private static function get_user_phone($user_id) {
        $phone = get_user_meta($user_id, 'billing_phone', true);
        if (!$phone) {
            $phone = get_user_meta($user_id, 'phone', true);
        }

        return $phone ? self::sanitize_phone_number($phone) : false;
    }

    /**
     * Sanitizar número de teléfono a formato internacional
     *
     * @param string $phone Número de teléfono
     * @return string|false Teléfono en formato internacional o false
     */
    public static function sanitize_phone_number($phone) {
        // Remover todo excepto dígitos y +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Si no empieza con +, agregar código de país (Argentina por defecto)
        if (substr($phone, 0, 1) !== '+') {
            // Si es número local de 10 dígitos (ej: 3883349901)
            if (strlen($phone) == 10) {
                $phone = '+54' . $phone;
            } else {
                return false;
            }
        }

        // Validar longitud (8-15 dígitos después del +)
        if (!preg_match('/^\+\d{8,15}$/', $phone)) {
            return false;
        }

        return $phone;
    }

    /**
     * Renderizar botón de WhatsApp con link wa.me
     *
     * @param string $wa_link URL de wa.me
     * @param string $button_text Texto del botón
     * @param array  $args Argumentos adicionales (class, style, etc)
     * @return string HTML del botón
     */
    public static function render_button($wa_link, $button_text = '', $args = array()) {
        if (!$wa_link) {
            return '';
        }

        $defaults = array(
            'class' => 'wpcw-whatsapp-button button',
            'style' => 'background: #25D366; color: white; border: none;',
            'icon' => true,
            'target' => '_blank',
            'rel' => 'noopener noreferrer'
        );

        $args = wp_parse_args($args, $defaults);

        if (empty($button_text)) {
            $button_text = __('Enviar por WhatsApp', 'wp-cupon-whatsapp');
        }

        $icon_html = '';
        if ($args['icon']) {
            $icon_html = '<span class="dashicons dashicons-whatsapp" style="margin-right: 5px; margin-top: 3px;"></span>';
        }

        return sprintf(
            '<a href="%s" class="%s" style="%s" target="%s" rel="%s">%s%s</a>',
            esc_url($wa_link),
            esc_attr($args['class']),
            esc_attr($args['style']),
            esc_attr($args['target']),
            esc_attr($args['rel']),
            $icon_html,
            esc_html($button_text)
        );
    }

    /**
     * Check if WhatsApp notifications are enabled
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = get_option('wpcw_whatsapp_settings', array('enabled' => true));
        return !empty($settings['enabled']);
    }

    /**
     * Get WhatsApp settings
     *
     * @return array
     */
    public static function get_settings() {
        return get_option('wpcw_whatsapp_settings', array(
            'enabled' => true,
            'send_on_proposal' => true,
            'send_on_counter_offer' => true,
            'send_on_approval' => true,
            'send_on_rejection' => true,
            'send_on_changes_requested' => true,
            'send_on_redemption' => true,
            'send_on_redemption_validation' => true
        ));
    }
}
