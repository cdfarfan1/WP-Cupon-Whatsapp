<?php
/**
 * WPCW - Notifications Center Page
 *
 * Central hub for viewing and managing all in-app notifications.
 *
 * @package WP_Cupon_Whatsapp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the notifications menu page
 * DISABLED: Will be registered in admin-menu.php for better organization
 */
function wpcw_register_notifications_menu() {
    // Commented out - will be added to admin-menu.php
    /*
    add_submenu_page(
        'wpcw-main-dashboard',
        __( 'Notificaciones', 'wp-cupon-whatsapp' ),
        __( 'Notificaciones', 'wp-cupon-whatsapp' ),
        'read',
        'wpcw-notifications',
        'wpcw_render_notifications_page',
        1 // Position at top of submenu
    );
    */
}
// add_action( 'admin_menu', 'wpcw_register_notifications_menu', 12 ); // Disabled

/**
 * Render the notifications center page
 */
function wpcw_render_notifications_page() {
    $user_id = get_current_user_id();
    $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';

    // Handle bulk actions
    if (isset($_POST['wpcw_bulk_action']) && isset($_POST['wpcw_notification_ids'])) {
        check_admin_referer('wpcw_notifications_bulk', 'wpcw_notifications_nonce');

        $action = sanitize_text_field($_POST['wpcw_bulk_action']);
        $notification_ids = array_map('absint', $_POST['wpcw_notification_ids']);

        if ($action === 'mark_read') {
            foreach ($notification_ids as $notification_id) {
                WPCW_Notifications::mark_as_read($notification_id, $user_id);
            }
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Notificaciones marcadas como leídas', 'wp-cupon-whatsapp') . '</p></div>';
        } elseif ($action === 'delete') {
            foreach ($notification_ids as $notification_id) {
                WPCW_Notifications::delete($notification_id, $user_id);
            }
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Notificaciones eliminadas', 'wp-cupon-whatsapp') . '</p></div>';
        }
    }

    // Get notifications based on filter
    $unread_only = ($filter === 'unread');
    $notifications = WPCW_Notifications::get_notifications($user_id, $unread_only, 50);
    $unread_count = WPCW_Notifications::get_unread_count($user_id);

    ?>
    <div class="wrap wpcw-notifications-wrap">
        <h1>
            <span class="dashicons dashicons-bell"></span>
            <?php _e( 'Centro de Notificaciones', 'wp-cupon-whatsapp' ); ?>
            <?php if ($unread_count > 0): ?>
                <span class="wpcw-notification-badge-title"><?php echo esc_html($unread_count); ?></span>
            <?php endif; ?>
        </h1>

        <!-- Filters and Actions Bar -->
        <div class="wpcw-notifications-toolbar">
            <div class="wpcw-notification-filters">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpcw-notifications&filter=all')); ?>"
                   class="button <?php echo $filter === 'all' ? 'button-primary' : ''; ?>">
                    <?php _e('Todas', 'wp-cupon-whatsapp'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpcw-notifications&filter=unread')); ?>"
                   class="button <?php echo $filter === 'unread' ? 'button-primary' : ''; ?>">
                    <?php printf(__('No leídas (%d)', 'wp-cupon-whatsapp'), $unread_count); ?>
                </a>
            </div>

            <?php if ($unread_count > 0): ?>
            <form method="post" style="display: inline;">
                <?php wp_nonce_field('wpcw_mark_all_read_action', 'wpcw_mark_all_read_nonce'); ?>
                <button type="submit" name="wpcw_mark_all_read" class="button" id="mark-all-read-btn">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e('Marcar todas como leídas', 'wp-cupon-whatsapp'); ?>
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <!-- Empty State -->
            <div class="wpcw-notifications-empty">
                <div class="wpcw-empty-icon">
                    <span class="dashicons dashicons-bell"></span>
                </div>
                <h2><?php _e('No hay notificaciones', 'wp-cupon-whatsapp'); ?></h2>
                <p><?php _e('Cuando recibas notificaciones sobre convenios y actividades, aparecerán aquí.', 'wp-cupon-whatsapp'); ?></p>
            </div>
        <?php else: ?>
            <!-- Notifications List -->
            <form method="post" id="notifications-form">
                <?php wp_nonce_field('wpcw_notifications_bulk', 'wpcw_notifications_nonce'); ?>

                <!-- Bulk Actions -->
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="wpcw_bulk_action" id="bulk-action-selector-top">
                            <option value=""><?php _e('Acciones en lote', 'wp-cupon-whatsapp'); ?></option>
                            <option value="mark_read"><?php _e('Marcar como leídas', 'wp-cupon-whatsapp'); ?></option>
                            <option value="delete"><?php _e('Eliminar', 'wp-cupon-whatsapp'); ?></option>
                        </select>
                        <input type="submit" class="button action" value="<?php esc_attr_e('Aplicar', 'wp-cupon-whatsapp'); ?>">
                    </div>
                </div>

                <div class="wpcw-notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <?php wpcw_render_notification_item($notification); ?>
                    <?php endforeach; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <style>
        .wpcw-notifications-wrap {
            max-width: 900px;
        }
        .wpcw-notification-badge-title {
            display: inline-block;
            background: #d63638;
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .wpcw-notifications-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .wpcw-notification-filters {
            display: flex;
            gap: 10px;
        }
        .wpcw-notifications-empty {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 20px;
        }
        .wpcw-empty-icon .dashicons {
            font-size: 80px;
            width: 80px;
            height: 80px;
            color: #ccc;
        }
        .wpcw-notifications-empty h2 {
            color: #666;
            margin: 20px 0 10px;
        }
        .wpcw-notifications-empty p {
            color: #999;
        }
        .wpcw-notifications-list {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
        }
        .wpcw-notification-item {
            display: flex;
            align-items: flex-start;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
            position: relative;
        }
        .wpcw-notification-item:last-child {
            border-bottom: none;
        }
        .wpcw-notification-item:hover {
            background-color: #f9f9f9;
        }
        .wpcw-notification-item.unread {
            background-color: #f0f6fc;
        }
        .wpcw-notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #2271b1;
        }
        .wpcw-notification-checkbox {
            margin-right: 15px;
            margin-top: 3px;
        }
        .wpcw-notification-icon {
            margin-right: 15px;
            flex-shrink: 0;
        }
        .wpcw-notification-icon .dashicons {
            width: 40px;
            height: 40px;
            font-size: 24px;
            padding: 8px;
            border-radius: 50%;
            color: white;
        }
        .wpcw-notification-icon.type-convenio_proposed .dashicons {
            background: #3498db;
        }
        .wpcw-notification-icon.type-convenio_approved .dashicons {
            background: #27ae60;
        }
        .wpcw-notification-icon.type-convenio_rejected .dashicons {
            background: #e74c3c;
        }
        .wpcw-notification-icon.type-counter_offer .dashicons {
            background: #9b59b6;
        }
        .wpcw-notification-icon.type-supervisor_approval_needed .dashicons {
            background: #f39c12;
        }
        .wpcw-notification-icon.type-changes_requested .dashicons {
            background: #00a0d2;
        }
        .wpcw-notification-content {
            flex: 1;
        }
        .wpcw-notification-title {
            font-weight: 600;
            margin: 0 0 5px 0;
            color: #1d2327;
        }
        .wpcw-notification-message {
            color: #666;
            margin: 0 0 8px 0;
        }
        .wpcw-notification-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #999;
        }
        .wpcw-notification-actions {
            display: flex;
            gap: 8px;
            margin-left: 15px;
        }
        .wpcw-notification-action {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            background: white;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        .wpcw-notification-action:hover {
            background: #f0f0f0;
            border-color: #999;
        }
        .wpcw-notification-action.primary {
            background: #2271b1;
            color: white;
            border-color: #2271b1;
        }
        .wpcw-notification-action.primary:hover {
            background: #135e96;
            border-color: #135e96;
        }
        #mark-all-read-btn .dashicons {
            margin-top: 3px;
        }
    </style>
    <?php
}

/**
 * Render a single notification item
 *
 * @param object $notification Notification object from database
 */
function wpcw_render_notification_item($notification) {
    $is_unread = !$notification->is_read;
    $icon_class = 'type-' . esc_attr($notification->type);

    // Map notification types to icons
    $icon_map = array(
        'convenio_proposed' => 'dashicons-megaphone',
        'convenio_approved' => 'dashicons-yes-alt',
        'convenio_rejected' => 'dashicons-dismiss',
        'counter_offer' => 'dashicons-update',
        'negotiation_accepted' => 'dashicons-thumbs-up',
        'supervisor_approval_needed' => 'dashicons-warning',
        'changes_requested' => 'dashicons-edit',
        'convenio_expiring' => 'dashicons-clock',
        'convenio_activated' => 'dashicons-star-filled'
    );

    $icon = isset($icon_map[$notification->type]) ? $icon_map[$notification->type] : 'dashicons-info';

    // Format time ago
    $time_ago = human_time_diff(strtotime($notification->created_at), current_time('timestamp'));

    ?>
    <div class="wpcw-notification-item <?php echo $is_unread ? 'unread' : ''; ?>" data-notification-id="<?php echo esc_attr($notification->id); ?>">
        <input type="checkbox" name="wpcw_notification_ids[]" value="<?php echo esc_attr($notification->id); ?>" class="wpcw-notification-checkbox">

        <div class="wpcw-notification-icon <?php echo esc_attr($icon_class); ?>">
            <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
        </div>

        <div class="wpcw-notification-content">
            <h3 class="wpcw-notification-title"><?php echo esc_html($notification->title); ?></h3>
            <p class="wpcw-notification-message"><?php echo esc_html($notification->message); ?></p>
            <div class="wpcw-notification-meta">
                <span class="wpcw-notification-time">
                    <span class="dashicons dashicons-clock" style="font-size: 14px; margin-top: -2px;"></span>
                    <?php printf(__('Hace %s', 'wp-cupon-whatsapp'), $time_ago); ?>
                </span>
                <?php if ($is_unread): ?>
                    <span class="wpcw-notification-status">
                        <span class="dashicons dashicons-marker" style="font-size: 14px; margin-top: -2px; color: #2271b1;"></span>
                        <?php _e('No leída', 'wp-cupon-whatsapp'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="wpcw-notification-actions">
            <?php if (!empty($notification->link)): ?>
                <a href="<?php echo esc_url($notification->link); ?>" class="wpcw-notification-action primary" data-mark-read="<?php echo esc_attr($notification->id); ?>">
                    <?php _e('Ver', 'wp-cupon-whatsapp'); ?>
                </a>
            <?php endif; ?>
            <?php if ($is_unread): ?>
                <button type="button" class="wpcw-notification-action" data-mark-read="<?php echo esc_attr($notification->id); ?>">
                    <?php _e('Marcar leída', 'wp-cupon-whatsapp'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Handle mark all as read form submission
 */
function wpcw_handle_mark_all_read() {
    if (!isset($_POST['wpcw_mark_all_read'])) {
        return;
    }

    check_admin_referer('wpcw_mark_all_read_action', 'wpcw_mark_all_read_nonce');

    $user_id = get_current_user_id();
    WPCW_Notifications::mark_all_as_read($user_id);

    wp_safe_redirect(admin_url('admin.php?page=wpcw-notifications&marked=all'));
    exit;
}
add_action('admin_init', 'wpcw_handle_mark_all_read');
