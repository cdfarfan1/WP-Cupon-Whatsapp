<?php
/**
 * WPCW Notifications System
 *
 * In-app notification system for real-time updates without email dependency.
 * Based on Facebook/LinkedIn notification patterns and WordPress admin notices best practices.
 *
 * @package WP_Cupon_Whatsapp
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPCW_Notifications
 *
 * Manages in-app notifications with bell icon, badge counts, and notification center.
 */
class WPCW_Notifications {

    /**
     * Database table name
     */
    const TABLE_NAME = 'wpcw_notifications';

    /**
     * Notification types
     */
    const TYPE_CONVENIO_PROPOSED = 'convenio_proposed';
    const TYPE_CONVENIO_APPROVED = 'convenio_approved';
    const TYPE_CONVENIO_REJECTED = 'convenio_rejected';
    const TYPE_COUNTER_OFFER = 'counter_offer';
    const TYPE_NEGOTIATION_ACCEPTED = 'negotiation_accepted';
    const TYPE_SUPERVISOR_APPROVAL_NEEDED = 'supervisor_approval_needed';
    const TYPE_CHANGES_REQUESTED = 'changes_requested';
    const TYPE_CONVENIO_EXPIRING = 'convenio_expiring';
    const TYPE_CONVENIO_ACTIVATED = 'convenio_activated';

    /**
     * Initialize notifications system
     */
    public static function init() {
        add_action('admin_bar_menu', array(__CLASS__, 'add_notification_bell'), 999);
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('wp_ajax_wpcw_get_notifications', array(__CLASS__, 'ajax_get_notifications'));
        add_action('wp_ajax_wpcw_mark_notification_read', array(__CLASS__, 'ajax_mark_notification_read'));
        add_action('wp_ajax_wpcw_mark_all_read', array(__CLASS__, 'ajax_mark_all_read'));

        // Cron job to clean old notifications
        add_action('wpcw_cleanup_old_notifications', array(__CLASS__, 'cleanup_old_notifications'));
        if (!wp_next_scheduled('wpcw_cleanup_old_notifications')) {
            wp_schedule_event(time(), 'daily', 'wpcw_cleanup_old_notifications');
        }
    }

    /**
     * Create notifications database table
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            message text NOT NULL,
            link varchar(500) DEFAULT NULL,
            related_post_id bigint(20) unsigned DEFAULT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            read_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY is_read (is_read),
            KEY created_at (created_at),
            KEY type (type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create a notification
     *
     * @param int    $user_id         User ID to receive notification
     * @param string $type            Notification type constant
     * @param string $title           Notification title
     * @param string $message         Notification message
     * @param string $link            Optional link to related page
     * @param int    $related_post_id Optional related post ID
     * @return int|false Notification ID on success, false on failure
     */
    public static function create($user_id, $type, $title, $message, $link = '', $related_post_id = 0) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'related_post_id' => $related_post_id,
                'is_read' => 0,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get notifications for a user
     *
     * @param int  $user_id User ID
     * @param bool $unread_only Only get unread notifications
     * @param int  $limit Number of notifications to retrieve
     * @param int  $offset Offset for pagination
     * @return array Array of notification objects
     */
    public static function get_notifications($user_id, $unread_only = false, $limit = 20, $offset = 0) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $where = $wpdb->prepare("WHERE user_id = %d", $user_id);

        if ($unread_only) {
            $where .= " AND is_read = 0";
        }

        $sql = "SELECT * FROM $table_name
                $where
                ORDER BY created_at DESC
                LIMIT %d OFFSET %d";

        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));
    }

    /**
     * Get unread notification count for a user
     *
     * @param int $user_id User ID
     * @return int Unread count
     */
    public static function get_unread_count($user_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND is_read = 0",
            $user_id
        ));
    }

    /**
     * Mark a notification as read
     *
     * @param int $notification_id Notification ID
     * @param int $user_id User ID (for security verification)
     * @return bool Success
     */
    public static function mark_as_read($notification_id, $user_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->update(
            $table_name,
            array(
                'is_read' => 1,
                'read_at' => current_time('mysql')
            ),
            array(
                'id' => $notification_id,
                'user_id' => $user_id
            ),
            array('%d', '%s'),
            array('%d', '%d')
        );
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param int $user_id User ID
     * @return bool Success
     */
    public static function mark_all_as_read($user_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->update(
            $table_name,
            array(
                'is_read' => 1,
                'read_at' => current_time('mysql')
            ),
            array(
                'user_id' => $user_id,
                'is_read' => 0
            ),
            array('%d', '%s'),
            array('%d', '%d')
        );
    }

    /**
     * Delete a notification
     *
     * @param int $notification_id Notification ID
     * @param int $user_id User ID (for security verification)
     * @return bool Success
     */
    public static function delete($notification_id, $user_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->delete(
            $table_name,
            array(
                'id' => $notification_id,
                'user_id' => $user_id
            ),
            array('%d', '%d')
        );
    }

    /**
     * Clean up old notifications (>90 days)
     */
    public static function cleanup_old_notifications() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->query(
            "DELETE FROM $table_name
             WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
    }

    /**
     * Add notification bell to admin bar
     *
     * @param WP_Admin_Bar $wp_admin_bar Admin bar object
     */
    public static function add_notification_bell($wp_admin_bar) {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $unread_count = self::get_unread_count($user_id);

        $badge = $unread_count > 0 ? '<span class="wpcw-notification-badge">' . $unread_count . '</span>' : '';

        $wp_admin_bar->add_node(array(
            'id'    => 'wpcw-notifications',
            'title' => '<span class="dashicons dashicons-bell" style="font-size: 20px; margin-top: 2px;"></span>' . $badge,
            'href'  => admin_url('admin.php?page=wpcw-notifications'),
            'meta'  => array(
                'class' => 'wpcw-notification-bell',
                'title' => __('Notificaciones', 'wp-cupon-whatsapp')
            )
        ));
    }

    /**
     * Enqueue scripts and styles for notifications
     */
    public static function enqueue_scripts() {
        wp_enqueue_style(
            'wpcw-notifications',
            WPCW_PLUGIN_URL . 'admin/css/wpcw-notifications.css',
            array(),
            WPCW_VERSION
        );

        wp_enqueue_script(
            'wpcw-notifications',
            WPCW_PLUGIN_URL . 'admin/js/wpcw-notifications.js',
            array('jquery'),
            WPCW_VERSION,
            true
        );

        wp_localize_script('wpcw-notifications', 'wpcwNotifications', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpcw_notifications_nonce'),
            'refreshInterval' => 30000, // 30 seconds
            'strings' => array(
                'markAllRead' => __('Marcar todas como leídas', 'wp-cupon-whatsapp'),
                'noNotifications' => __('No hay notificaciones', 'wp-cupon-whatsapp'),
                'viewAll' => __('Ver todas', 'wp-cupon-whatsapp')
            )
        ));
    }

    /**
     * AJAX: Get notifications
     */
    public static function ajax_get_notifications() {
        check_ajax_referer('wpcw_notifications_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('Not logged in');
        }

        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 10;
        $unread_only = isset($_POST['unread_only']) ? (bool) $_POST['unread_only'] : false;

        $notifications = self::get_notifications($user_id, $unread_only, $limit);
        $unread_count = self::get_unread_count($user_id);

        wp_send_json_success(array(
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ));
    }

    /**
     * AJAX: Mark notification as read
     */
    public static function ajax_mark_notification_read() {
        check_ajax_referer('wpcw_notifications_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('Not logged in');
        }

        $notification_id = isset($_POST['notification_id']) ? absint($_POST['notification_id']) : 0;
        if (!$notification_id) {
            wp_send_json_error('Invalid notification ID');
        }

        $result = self::mark_as_read($notification_id, $user_id);

        if ($result !== false) {
            $unread_count = self::get_unread_count($user_id);
            wp_send_json_success(array('unread_count' => $unread_count));
        } else {
            wp_send_json_error('Failed to mark as read');
        }
    }

    /**
     * AJAX: Mark all notifications as read
     */
    public static function ajax_mark_all_read() {
        check_ajax_referer('wpcw_notifications_nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('Not logged in');
        }

        $result = self::mark_all_as_read($user_id);

        if ($result !== false) {
            wp_send_json_success(array('unread_count' => 0));
        } else {
            wp_send_json_error('Failed to mark all as read');
        }
    }

    /**
     * Helper: Create convenio proposal notification
     *
     * @param int $convenio_id Convenio post ID
     * @param int $recipient_user_id User ID to notify
     */
    public static function notify_convenio_proposed($convenio_id, $recipient_user_id) {
        $convenio_title = get_the_title($convenio_id);
        $provider_id = get_post_meta($convenio_id, '_convenio_provider_id', true);
        $provider_name = get_the_title($provider_id);

        self::create(
            $recipient_user_id,
            self::TYPE_CONVENIO_PROPOSED,
            sprintf(__('Nueva propuesta de convenio: %s', 'wp-cupon-whatsapp'), $provider_name),
            sprintf(__('Has recibido una propuesta de convenio de %s', 'wp-cupon-whatsapp'), $provider_name),
            admin_url('post.php?post=' . $convenio_id . '&action=edit'),
            $convenio_id
        );
    }

    /**
     * Helper: Create convenio approval notification
     *
     * @param int $convenio_id Convenio post ID
     * @param int $originator_user_id User ID to notify
     */
    public static function notify_convenio_approved($convenio_id, $originator_user_id) {
        $convenio_title = get_the_title($convenio_id);

        self::create(
            $originator_user_id,
            self::TYPE_CONVENIO_APPROVED,
            __('Convenio aprobado', 'wp-cupon-whatsapp'),
            sprintf(__('Tu convenio "%s" ha sido aprobado y está ahora activo', 'wp-cupon-whatsapp'), $convenio_title),
            admin_url('post.php?post=' . $convenio_id . '&action=edit'),
            $convenio_id
        );
    }

    /**
     * Helper: Create convenio rejection notification
     *
     * @param int    $convenio_id Convenio post ID
     * @param int    $originator_user_id User ID to notify
     * @param string $reason Rejection reason
     */
    public static function notify_convenio_rejected($convenio_id, $originator_user_id, $reason) {
        $convenio_title = get_the_title($convenio_id);

        self::create(
            $originator_user_id,
            self::TYPE_CONVENIO_REJECTED,
            __('Convenio rechazado', 'wp-cupon-whatsapp'),
            sprintf(__('Tu convenio "%s" ha sido rechazado. Motivo: %s', 'wp-cupon-whatsapp'), $convenio_title, $reason),
            admin_url('post.php?post=' . $convenio_id . '&action=edit'),
            $convenio_id
        );
    }

    /**
     * Helper: Create counter-offer notification
     *
     * @param int $convenio_id Convenio post ID
     * @param int $recipient_user_id User ID to notify
     */
    public static function notify_counter_offer($convenio_id, $recipient_user_id) {
        $convenio_title = get_the_title($convenio_id);

        self::create(
            $recipient_user_id,
            self::TYPE_COUNTER_OFFER,
            __('Nueva contraoferta recibida', 'wp-cupon-whatsapp'),
            sprintf(__('Has recibido una contraoferta para "%s"', 'wp-cupon-whatsapp'), $convenio_title),
            admin_url('post.php?post=' . $convenio_id . '&action=edit'),
            $convenio_id
        );
    }

    /**
     * Helper: Create supervisor approval needed notification
     *
     * @param int $convenio_id Convenio post ID
     * @param int $supervisor_user_id User ID to notify
     */
    public static function notify_supervisor_approval_needed($convenio_id, $supervisor_user_id) {
        $convenio_title = get_the_title($convenio_id);

        self::create(
            $supervisor_user_id,
            self::TYPE_SUPERVISOR_APPROVAL_NEEDED,
            __('Aprobación de supervisor requerida', 'wp-cupon-whatsapp'),
            sprintf(__('El convenio "%s" requiere tu aprobación', 'wp-cupon-whatsapp'), $convenio_title),
            admin_url('post.php?post=' . $convenio_id . '&action=edit'),
            $convenio_id
        );
    }

    /**
     * Helper: Create changes requested notification
     *
     * @param int    $convenio_id Convenio post ID
     * @param int    $originator_user_id User ID to notify
     * @param string $feedback Supervisor feedback
     */
    public static function notify_changes_requested($convenio_id, $originator_user_id, $feedback) {
        $convenio_title = get_the_title($convenio_id);

        self::create(
            $originator_user_id,
            self::TYPE_CHANGES_REQUESTED,
            __('Cambios solicitados en convenio', 'wp-cupon-whatsapp'),
            sprintf(__('Se han solicitado cambios en "%s". Feedback: %s', 'wp-cupon-whatsapp'), $convenio_title, wp_trim_words($feedback, 20)),
            admin_url('post.php?post=' . $convenio_id . '&action=edit'),
            $convenio_id
        );
    }

    /**
     * Helper: Create convenio expiring notification
     *
     * @param int $convenio_id Convenio post ID
     * @param int $user_id User ID to notify
     * @param int $days_until_expiry Days until expiration
     */
    public static function notify_convenio_expiring($convenio_id, $user_id, $days_until_expiry) {
        $convenio_title = get_the_title($convenio_id);

        self::create(
            $user_id,
            self::TYPE_CONVENIO_EXPIRING,
            __('Convenio próximo a vencer', 'wp-cupon-whatsapp'),
            sprintf(__('El convenio "%s" vence en %d días', 'wp-cupon-whatsapp'), $convenio_title, $days_until_expiry),
            admin_url('post.php?post=' . $convenio_id . '&action=edit'),
            $convenio_id
        );
    }
}

// Initialize notifications system
WPCW_Notifications::init();

// Create table on activation
register_activation_hook(WPCW_PLUGIN_FILE, array('WPCW_Notifications', 'create_table'));
