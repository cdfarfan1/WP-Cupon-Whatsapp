<?php
/**
 * WPCW Convenio Approval System
 *
 * Handles multi-level approval workflows for convenios with configurable thresholds.
 * Based on enterprise approval systems and Harvard Business Review governance models.
 *
 * @package WP_Cupon_Whatsapp
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPCW_Convenio_Approval
 *
 * Manages supervisor approval workflows, thresholds, and escalation logic.
 */
class WPCW_Convenio_Approval {

    /**
     * Default approval threshold (monetary value or strategic importance score)
     */
    const DEFAULT_APPROVAL_THRESHOLD = 10000;

    /**
     * Option key for storing approval settings
     */
    const SETTINGS_OPTION_KEY = 'wpcw_convenio_approval_settings';

    /**
     * Initialize approval system hooks
     */
    public static function init() {
        add_action('save_post_wpcw_convenio', array(__CLASS__, 'check_approval_requirements'), 20, 2);
        add_action('admin_init', array(__CLASS__, 'handle_supervisor_actions'));
    }

    /**
     * Check if a convenio requires supervisor approval based on thresholds
     *
     * @param int $convenio_id The convenio post ID
     * @return bool True if supervisor approval is required
     */
    public static function requires_supervisor_approval($convenio_id) {
        $settings = self::get_approval_settings();

        // Get convenio financial data
        $discount_percent = (float) get_post_meta($convenio_id, '_convenio_discount_percent', true);
        $max_uses = (int) get_post_meta($convenio_id, '_convenio_max_uses', true);
        $estimated_value = $discount_percent * $max_uses; // Simplified calculation

        // Check monetary threshold
        if ($estimated_value >= $settings['monetary_threshold']) {
            return true;
        }

        // Check if it's a strategic partner (large institution)
        $recipient_id = get_post_meta($convenio_id, '_convenio_recipient_id', true);
        $is_strategic = get_post_meta($recipient_id, '_is_strategic_partner', true);

        if ($is_strategic && $settings['strategic_requires_approval']) {
            return true;
        }

        // Check if it's a long-term commitment
        $duration_months = (int) get_post_meta($convenio_id, '_convenio_duration_months', true);
        if ($duration_months >= $settings['long_term_threshold_months']) {
            return true;
        }

        return false;
    }

    /**
     * Automatically route convenio to supervisor if needed
     *
     * @param int     $post_id The post ID
     * @param WP_Post $post    The post object
     */
    public static function check_approval_requirements($post_id, $post) {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Only process if status is 'approved' (ready for final approval)
        $status = get_post_meta($post_id, '_convenio_status', true);
        if ($status !== 'approved') {
            return;
        }

        // Check if already has supervisor approval
        $has_approval = get_post_meta($post_id, '_convenio_supervisor_approved', true);
        if ($has_approval) {
            return;
        }

        // Route to supervisor if requirements are met
        if (self::requires_supervisor_approval($post_id)) {
            self::escalate_to_supervisor($post_id);
        }
    }

    /**
     * Escalate a convenio to supervisor approval
     *
     * @param int $convenio_id The convenio post ID
     * @return bool True on success
     */
    public static function escalate_to_supervisor($convenio_id) {
        // Update status to pending_supervisor
        update_post_meta($convenio_id, '_convenio_status', 'pending_supervisor');

        // Record escalation timestamp
        update_post_meta($convenio_id, '_convenio_escalated_at', current_time('mysql'));

        // Find assigned supervisor or use default
        $supervisor_id = self::get_assigned_supervisor($convenio_id);
        if ($supervisor_id) {
            update_post_meta($convenio_id, '_convenio_assigned_supervisor', $supervisor_id);
        }

        // Add to approval history
        $history = get_post_meta($convenio_id, '_convenio_approval_history', true) ?: array();
        $history[] = array(
            'timestamp' => current_time('mysql'),
            'action' => 'escalated',
            'message' => __('Convenio escalado a supervisor para aprobación final', 'wp-cupon-whatsapp'),
            'escalated_by' => get_current_user_id()
        );
        update_post_meta($convenio_id, '_convenio_approval_history', $history);

        // Send notification to supervisor
        self::notify_supervisor($convenio_id, $supervisor_id);

        return true;
    }

    /**
     * Get assigned supervisor for a convenio
     *
     * @param int $convenio_id The convenio post ID
     * @return int|null Supervisor user ID or null
     */
    public static function get_assigned_supervisor($convenio_id) {
        // First check if manually assigned
        $assigned = get_post_meta($convenio_id, '_convenio_assigned_supervisor', true);
        if ($assigned) {
            return (int) $assigned;
        }

        // Get provider (institution or business)
        $provider_id = get_post_meta($convenio_id, '_convenio_provider_id', true);
        $provider_type = get_post_meta($convenio_id, '_convenio_provider_type', true);

        // Check if provider has a designated supervisor
        $supervisor_id = get_post_meta($provider_id, '_designated_supervisor', true);
        if ($supervisor_id) {
            return (int) $supervisor_id;
        }

        // Fallback: Find any user with 'approve_convenios' capability
        $supervisors = get_users(array(
            'role' => 'wpcw_benefits_supervisor',
            'number' => 1
        ));

        return !empty($supervisors) ? $supervisors[0]->ID : null;
    }

    /**
     * Supervisor approves a convenio
     *
     * @param int    $convenio_id   The convenio post ID
     * @param int    $supervisor_id The supervisor user ID
     * @param string $notes         Optional approval notes
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function supervisor_approve($convenio_id, $supervisor_id, $notes = '') {
        // Verify user has approval capability
        $user = get_userdata($supervisor_id);
        if (!$user || !user_can($supervisor_id, 'approve_convenios')) {
            return new WP_Error('insufficient_permissions', __('No tienes permisos para aprobar convenios', 'wp-cupon-whatsapp'));
        }

        // Verify convenio is in pending_supervisor status
        $status = get_post_meta($convenio_id, '_convenio_status', true);
        if ($status !== 'pending_supervisor') {
            return new WP_Error('invalid_status', __('Este convenio no está pendiente de aprobación de supervisor', 'wp-cupon-whatsapp'));
        }

        // Update to active status
        update_post_meta($convenio_id, '_convenio_status', 'active');
        update_post_meta($convenio_id, '_convenio_supervisor_approved', true);
        update_post_meta($convenio_id, '_convenio_approved_by_supervisor', $supervisor_id);
        update_post_meta($convenio_id, '_convenio_supervisor_approved_at', current_time('mysql'));
        update_post_meta($convenio_id, '_convenio_supervisor_notes', $notes);

        // Add to approval history
        $history = get_post_meta($convenio_id, '_convenio_approval_history', true) ?: array();
        $history[] = array(
            'timestamp' => current_time('mysql'),
            'action' => 'supervisor_approved',
            'supervisor_id' => $supervisor_id,
            'supervisor_name' => $user->display_name,
            'notes' => $notes
        );
        update_post_meta($convenio_id, '_convenio_approval_history', $history);

        // Notify parties
        self::notify_approval_complete($convenio_id);

        return true;
    }

    /**
     * Supervisor rejects a convenio
     *
     * @param int    $convenio_id   The convenio post ID
     * @param int    $supervisor_id The supervisor user ID
     * @param string $reason        Rejection reason (required)
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function supervisor_reject($convenio_id, $supervisor_id, $reason) {
        if (empty($reason)) {
            return new WP_Error('missing_reason', __('Debes proporcionar una razón para el rechazo', 'wp-cupon-whatsapp'));
        }

        // Verify user has approval capability
        $user = get_userdata($supervisor_id);
        if (!$user || !user_can($supervisor_id, 'approve_convenios')) {
            return new WP_Error('insufficient_permissions', __('No tienes permisos para rechazar convenios', 'wp-cupon-whatsapp'));
        }

        // Verify convenio is in pending_supervisor status
        $status = get_post_meta($convenio_id, '_convenio_status', true);
        if ($status !== 'pending_supervisor') {
            return new WP_Error('invalid_status', __('Este convenio no está pendiente de aprobación de supervisor', 'wp-cupon-whatsapp'));
        }

        // Update to rejected status
        update_post_meta($convenio_id, '_convenio_status', 'rejected');
        update_post_meta($convenio_id, '_convenio_supervisor_rejected', true);
        update_post_meta($convenio_id, '_convenio_rejected_by_supervisor', $supervisor_id);
        update_post_meta($convenio_id, '_convenio_supervisor_rejected_at', current_time('mysql'));
        update_post_meta($convenio_id, '_convenio_supervisor_rejection_reason', $reason);

        // Add to approval history
        $history = get_post_meta($convenio_id, '_convenio_approval_history', true) ?: array();
        $history[] = array(
            'timestamp' => current_time('mysql'),
            'action' => 'supervisor_rejected',
            'supervisor_id' => $supervisor_id,
            'supervisor_name' => $user->display_name,
            'reason' => $reason
        );
        update_post_meta($convenio_id, '_convenio_approval_history', $history);

        // Notify parties
        self::notify_rejection($convenio_id, $reason);

        return true;
    }

    /**
     * Request changes/revision from supervisor
     *
     * @param int    $convenio_id   The convenio post ID
     * @param int    $supervisor_id The supervisor user ID
     * @param string $feedback      Required changes
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function supervisor_request_changes($convenio_id, $supervisor_id, $feedback) {
        if (empty($feedback)) {
            return new WP_Error('missing_feedback', __('Debes especificar qué cambios se requieren', 'wp-cupon-whatsapp'));
        }

        // Verify user has approval capability
        $user = get_userdata($supervisor_id);
        if (!$user || !user_can($supervisor_id, 'approve_convenios')) {
            return new WP_Error('insufficient_permissions', __('No tienes permisos para solicitar cambios', 'wp-cupon-whatsapp'));
        }

        // Update to under_negotiation status
        update_post_meta($convenio_id, '_convenio_status', 'under_negotiation');
        update_post_meta($convenio_id, '_convenio_supervisor_feedback', $feedback);
        update_post_meta($convenio_id, '_convenio_changes_requested_at', current_time('mysql'));

        // Add to approval history
        $history = get_post_meta($convenio_id, '_convenio_approval_history', true) ?: array();
        $history[] = array(
            'timestamp' => current_time('mysql'),
            'action' => 'changes_requested',
            'supervisor_id' => $supervisor_id,
            'supervisor_name' => $user->display_name,
            'feedback' => $feedback
        );
        update_post_meta($convenio_id, '_convenio_approval_history', $history);

        // Notify originator
        self::notify_changes_requested($convenio_id, $feedback);

        return true;
    }

    /**
     * Handle supervisor action form submissions
     */
    public static function handle_supervisor_actions() {
        if (!isset($_POST['wpcw_supervisor_action'])) {
            return;
        }

        // Verify nonce
        if (!isset($_POST['wpcw_supervisor_nonce']) || !wp_verify_nonce($_POST['wpcw_supervisor_nonce'], 'wpcw_supervisor_action')) {
            wp_die(__('Error de seguridad', 'wp-cupon-whatsapp'));
        }

        $action = sanitize_text_field($_POST['wpcw_supervisor_action']);
        $convenio_id = absint($_POST['convenio_id']);
        $supervisor_id = get_current_user_id();

        $result = null;

        switch ($action) {
            case 'approve':
                $notes = sanitize_textarea_field($_POST['approval_notes'] ?? '');
                $result = self::supervisor_approve($convenio_id, $supervisor_id, $notes);
                $success_message = __('Convenio aprobado exitosamente', 'wp-cupon-whatsapp');
                break;

            case 'reject':
                $reason = sanitize_textarea_field($_POST['rejection_reason'] ?? '');
                $result = self::supervisor_reject($convenio_id, $supervisor_id, $reason);
                $success_message = __('Convenio rechazado', 'wp-cupon-whatsapp');
                break;

            case 'request_changes':
                $feedback = sanitize_textarea_field($_POST['change_feedback'] ?? '');
                $result = self::supervisor_request_changes($convenio_id, $supervisor_id, $feedback);
                $success_message = __('Cambios solicitados', 'wp-cupon-whatsapp');
                break;
        }

        // Handle result
        if (is_wp_error($result)) {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            });
        } elseif ($result === true) {
            add_action('admin_notices', function() use ($success_message) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($success_message) . '</p></div>';
            });
        }
    }

    /**
     * Get approval history formatted for display
     *
     * @param int $convenio_id The convenio post ID
     * @return array Formatted history entries
     */
    public static function get_approval_history($convenio_id) {
        $history = get_post_meta($convenio_id, '_convenio_approval_history', true);
        return is_array($history) ? $history : array();
    }

    /**
     * Get approval settings
     *
     * @return array Settings array
     */
    public static function get_approval_settings() {
        $defaults = array(
            'monetary_threshold' => self::DEFAULT_APPROVAL_THRESHOLD,
            'strategic_requires_approval' => true,
            'long_term_threshold_months' => 12,
            'auto_escalate' => true,
            'notification_email' => get_option('admin_email')
        );

        $settings = get_option(self::SETTINGS_OPTION_KEY, array());
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Update approval settings
     *
     * @param array $settings New settings
     * @return bool True on success
     */
    public static function update_approval_settings($settings) {
        return update_option(self::SETTINGS_OPTION_KEY, $settings);
    }

    /**
     * Calculate approval metrics for dashboard
     *
     * @return array Metrics array
     */
    public static function calculate_approval_metrics() {
        global $wpdb;

        // Get all convenios pending supervisor approval
        $pending_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = %s
            AND pm.meta_key = '_convenio_status'
            AND pm.meta_value = %s
        ", 'wpcw_convenio', 'pending_supervisor'));

        // Calculate average approval time
        $avg_approval_time = $wpdb->get_var("
            SELECT AVG(
                TIMESTAMPDIFF(HOUR,
                    escalated.meta_value,
                    approved.meta_value
                )
            )
            FROM {$wpdb->postmeta} escalated
            INNER JOIN {$wpdb->postmeta} approved
                ON escalated.post_id = approved.post_id
            WHERE escalated.meta_key = '_convenio_escalated_at'
            AND approved.meta_key = '_convenio_supervisor_approved_at'
        ");

        // Get supervisor response rate
        $total_escalated = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT post_id)
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
        ", '_convenio_escalated_at'));

        $responded = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT post_id)
            FROM {$wpdb->postmeta}
            WHERE meta_key IN (%s, %s)
        ", '_convenio_supervisor_approved', '_convenio_supervisor_rejected'));

        $response_rate = $total_escalated > 0 ? ($responded / $total_escalated) * 100 : 0;

        return array(
            'pending_approval_count' => (int) $pending_count,
            'average_approval_hours' => round($avg_approval_time, 1),
            'supervisor_response_rate' => round($response_rate, 1)
        );
    }

    /**
     * Send notification to supervisor about pending approval
     *
     * @param int $convenio_id   The convenio post ID
     * @param int $supervisor_id The supervisor user ID
     */
    private static function notify_supervisor($convenio_id, $supervisor_id) {
        if (!$supervisor_id) {
            return;
        }

        $supervisor = get_userdata($supervisor_id);
        if (!$supervisor || !$supervisor->user_email) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);
        $convenio_url = admin_url('post.php?post=' . $convenio_id . '&action=edit');

        $subject = sprintf(__('[Acción Requerida] Convenio pendiente de aprobación: %s', 'wp-cupon-whatsapp'), $convenio_title);

        $message = sprintf(
            __("Hola %s,\n\nSe ha escalado un convenio que requiere tu aprobación:\n\n%s\n\nRevisa y aprueba en: %s\n\nGracias,\nSistema de Convenios", 'wp-cupon-whatsapp'),
            $supervisor->display_name,
            $convenio_title,
            $convenio_url
        );

        wp_mail($supervisor->user_email, $subject, $message);

        // Send in-app notification
        if (class_exists('WPCW_Notifications')) {
            WPCW_Notifications::notify_supervisor_approval_needed($convenio_id, $supervisor_id);
        }
    }

    /**
     * Notify parties when approval is complete
     *
     * @param int $convenio_id The convenio post ID
     */
    private static function notify_approval_complete($convenio_id) {
        // Get originator
        $originator_id = get_post_meta($convenio_id, '_convenio_originator_id', true);
        if (!$originator_id) {
            return;
        }

        $originator = get_userdata($originator_id);
        if (!$originator || !$originator->user_email) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);
        $subject = sprintf(__('Convenio aprobado: %s', 'wp-cupon-whatsapp'), $convenio_title);
        $message = sprintf(
            __("¡Buenas noticias!\n\nEl convenio '%s' ha sido aprobado por el supervisor y está ahora activo.\n\nPuedes verlo en tu panel de convenios.\n\nSaludos,\nSistema de Convenios", 'wp-cupon-whatsapp'),
            $convenio_title
        );

        wp_mail($originator->user_email, $subject, $message);

        // Send in-app notification
        if (class_exists('WPCW_Notifications')) {
            WPCW_Notifications::notify_convenio_approved($convenio_id, $originator_id);
        }
    }

    /**
     * Notify parties when convenio is rejected
     *
     * @param int    $convenio_id The convenio post ID
     * @param string $reason      Rejection reason
     */
    private static function notify_rejection($convenio_id, $reason) {
        $originator_id = get_post_meta($convenio_id, '_convenio_originator_id', true);
        if (!$originator_id) {
            return;
        }

        $originator = get_userdata($originator_id);
        if (!$originator || !$originator->user_email) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);
        $subject = sprintf(__('Convenio rechazado: %s', 'wp-cupon-whatsapp'), $convenio_title);
        $message = sprintf(
            __("El convenio '%s' ha sido rechazado por el supervisor.\n\nMotivo: %s\n\nPuedes revisar y proponer un nuevo convenio con ajustes.\n\nSaludos,\nSistema de Convenios", 'wp-cupon-whatsapp'),
            $convenio_title,
            $reason
        );

        wp_mail($originator->user_email, $subject, $message);

        // Send in-app notification
        if (class_exists('WPCW_Notifications')) {
            WPCW_Notifications::notify_convenio_rejected($convenio_id, $originator_id, $reason);
        }
    }

    /**
     * Notify originator when changes are requested
     *
     * @param int    $convenio_id The convenio post ID
     * @param string $feedback    Change feedback
     */
    private static function notify_changes_requested($convenio_id, $feedback) {
        $originator_id = get_post_meta($convenio_id, '_convenio_originator_id', true);
        if (!$originator_id) {
            return;
        }

        $originator = get_userdata($originator_id);
        if (!$originator || !$originator->user_email) {
            return;
        }

        $convenio_title = get_the_title($convenio_id);
        $convenio_url = admin_url('post.php?post=' . $convenio_id . '&action=edit');

        $subject = sprintf(__('Cambios solicitados en convenio: %s', 'wp-cupon-whatsapp'), $convenio_title);
        $message = sprintf(
            __("El supervisor ha solicitado cambios en el convenio '%s'.\n\nFeedback del supervisor:\n%s\n\nEdita el convenio aquí: %s\n\nSaludos,\nSistema de Convenios", 'wp-cupon-whatsapp'),
            $convenio_title,
            $feedback,
            $convenio_url
        );

        wp_mail($originator->user_email, $subject, $message);

        // Send in-app notification
        if (class_exists('WPCW_Notifications')) {
            WPCW_Notifications::notify_changes_requested($convenio_id, $originator_id, $feedback);
        }
    }
}

// Initialize the approval system
WPCW_Convenio_Approval::init();
