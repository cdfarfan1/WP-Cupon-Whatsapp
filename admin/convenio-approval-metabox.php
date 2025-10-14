<?php
/**
 * WPCW Convenio Approval Meta Box
 *
 * Visual interface for supervisor approval workflow
 *
 * @package WP_Cupon_Whatsapp
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render the supervisor approval meta box
 *
 * @param WP_Post $post The current post object
 */
function wpcw_render_convenio_approval_meta_box($post) {
    $convenio_id = $post->ID;
    $status = get_post_meta($convenio_id, '_convenio_status', true);
    $current_user_id = get_current_user_id();
    $can_approve = current_user_can('approve_convenios');

    // Get approval data
    $requires_approval = WPCW_Convenio_Approval::requires_supervisor_approval($convenio_id);
    $escalated_at = get_post_meta($convenio_id, '_convenio_escalated_at', true);
    $assigned_supervisor = get_post_meta($convenio_id, '_convenio_assigned_supervisor', true);
    $supervisor_approved = get_post_meta($convenio_id, '_convenio_supervisor_approved', true);
    $supervisor_rejected = get_post_meta($convenio_id, '_convenio_supervisor_rejected', true);
    $approval_history = WPCW_Convenio_Approval::get_approval_history($convenio_id);

    ?>
    <style>
        .wpcw-approval-container {
            padding: 15px;
        }
        .wpcw-approval-status {
            background: #f8f9fa;
            border-left: 4px solid #0073aa;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        .wpcw-approval-status.requires-approval {
            border-left-color: #f0ad4e;
            background: #fff8e5;
        }
        .wpcw-approval-status.approved {
            border-left-color: #46b450;
            background: #ecf7ed;
        }
        .wpcw-approval-status.rejected {
            border-left-color: #dc3232;
            background: #fde7e7;
        }
        .wpcw-approval-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
        }
        .wpcw-approval-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        .wpcw-approval-badge.approved {
            background: #d4edda;
            color: #155724;
        }
        .wpcw-approval-badge.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .wpcw-approval-timeline {
            position: relative;
            padding-left: 40px;
            margin: 20px 0;
        }
        .wpcw-approval-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }
        .wpcw-approval-timeline-item {
            position: relative;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .wpcw-approval-timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 20px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0073aa;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #ddd;
        }
        .wpcw-approval-timeline-item.escalated::before {
            background: #f0ad4e;
        }
        .wpcw-approval-timeline-item.approved::before {
            background: #46b450;
        }
        .wpcw-approval-timeline-item.rejected::before {
            background: #dc3232;
        }
        .wpcw-approval-timeline-item.changes-requested::before {
            background: #00a0d2;
        }
        .wpcw-approval-timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .wpcw-approval-timeline-action {
            font-weight: 600;
            color: #0073aa;
        }
        .wpcw-approval-timeline-time {
            font-size: 12px;
            color: #666;
        }
        .wpcw-approval-timeline-user {
            font-size: 13px;
            color: #555;
            margin-bottom: 5px;
        }
        .wpcw-approval-timeline-content {
            font-size: 13px;
            color: #666;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 3px;
        }
        .wpcw-approval-actions {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .wpcw-approval-actions h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        .wpcw-approval-form-group {
            margin-bottom: 15px;
        }
        .wpcw-approval-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #444;
        }
        .wpcw-approval-form-group textarea {
            width: 100%;
            min-height: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .wpcw-approval-button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .wpcw-approval-button {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .wpcw-approval-button.approve {
            background: #46b450;
            color: white;
        }
        .wpcw-approval-button.approve:hover {
            background: #3ea842;
        }
        .wpcw-approval-button.reject {
            background: #dc3232;
            color: white;
        }
        .wpcw-approval-button.reject:hover {
            background: #c92c2c;
        }
        .wpcw-approval-button.request-changes {
            background: #00a0d2;
            color: white;
        }
        .wpcw-approval-button.request-changes:hover {
            background: #0091c2;
        }
        .wpcw-approval-info {
            background: #e5f5fa;
            border-left: 4px solid #00a0d2;
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 3px;
        }
        .wpcw-approval-info-icon {
            display: inline-block;
            margin-right: 5px;
        }
        .wpcw-supervisor-assignment {
            background: #fff;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>

    <div class="wpcw-approval-container">

        <?php if ($requires_approval): ?>
            <div class="wpcw-approval-status requires-approval">
                <span class="dashicons dashicons-warning"></span>
                <strong><?php _e('Este convenio requiere aprobación de supervisor', 'wp-cupon-whatsapp'); ?></strong>
                <?php if ($status === 'pending_supervisor'): ?>
                    <span class="wpcw-approval-badge pending"><?php _e('Pendiente', 'wp-cupon-whatsapp'); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($supervisor_approved): ?>
            <div class="wpcw-approval-status approved">
                <span class="dashicons dashicons-yes-alt"></span>
                <strong><?php _e('Convenio aprobado por supervisor', 'wp-cupon-whatsapp'); ?></strong>
                <span class="wpcw-approval-badge approved"><?php _e('Aprobado', 'wp-cupon-whatsapp'); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($supervisor_rejected): ?>
            <div class="wpcw-approval-status rejected">
                <span class="dashicons dashicons-dismiss"></span>
                <strong><?php _e('Convenio rechazado por supervisor', 'wp-cupon-whatsapp'); ?></strong>
                <span class="wpcw-approval-badge rejected"><?php _e('Rechazado', 'wp-cupon-whatsapp'); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($assigned_supervisor): ?>
            <div class="wpcw-supervisor-assignment">
                <strong><?php _e('Supervisor Asignado:', 'wp-cupon-whatsapp'); ?></strong>
                <?php
                $supervisor = get_userdata($assigned_supervisor);
                if ($supervisor) {
                    echo esc_html($supervisor->display_name);
                    echo ' <span style="color: #666;">(' . esc_html($supervisor->user_email) . ')</span>';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($approval_history)): ?>
            <h4><?php _e('Historial de Aprobaciones', 'wp-cupon-whatsapp'); ?></h4>
            <div class="wpcw-approval-timeline">
                <?php foreach (array_reverse($approval_history) as $entry): ?>
                    <?php
                    $action_class = sanitize_html_class($entry['action']);
                    $action_labels = array(
                        'escalated' => __('Escalado a Supervisor', 'wp-cupon-whatsapp'),
                        'supervisor_approved' => __('Aprobado por Supervisor', 'wp-cupon-whatsapp'),
                        'supervisor_rejected' => __('Rechazado por Supervisor', 'wp-cupon-whatsapp'),
                        'changes_requested' => __('Cambios Solicitados', 'wp-cupon-whatsapp')
                    );
                    $action_label = isset($action_labels[$entry['action']]) ? $action_labels[$entry['action']] : $entry['action'];
                    ?>
                    <div class="wpcw-approval-timeline-item <?php echo esc_attr($action_class); ?>">
                        <div class="wpcw-approval-timeline-header">
                            <span class="wpcw-approval-timeline-action"><?php echo esc_html($action_label); ?></span>
                            <span class="wpcw-approval-timeline-time">
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($entry['timestamp']))); ?>
                            </span>
                        </div>

                        <?php if (isset($entry['supervisor_name'])): ?>
                            <div class="wpcw-approval-timeline-user">
                                <span class="dashicons dashicons-admin-users"></span>
                                <?php echo esc_html($entry['supervisor_name']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($entry['notes']) && !empty($entry['notes'])): ?>
                            <div class="wpcw-approval-timeline-content">
                                <strong><?php _e('Notas:', 'wp-cupon-whatsapp'); ?></strong><br>
                                <?php echo nl2br(esc_html($entry['notes'])); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($entry['reason']) && !empty($entry['reason'])): ?>
                            <div class="wpcw-approval-timeline-content">
                                <strong><?php _e('Motivo:', 'wp-cupon-whatsapp'); ?></strong><br>
                                <?php echo nl2br(esc_html($entry['reason'])); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($entry['feedback']) && !empty($entry['feedback'])): ?>
                            <div class="wpcw-approval-timeline-content">
                                <strong><?php _e('Feedback:', 'wp-cupon-whatsapp'); ?></strong><br>
                                <?php echo nl2br(esc_html($entry['feedback'])); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($entry['message']) && !empty($entry['message'])): ?>
                            <div class="wpcw-approval-timeline-content">
                                <?php echo nl2br(esc_html($entry['message'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($can_approve && $status === 'pending_supervisor'): ?>
            <div class="wpcw-approval-actions">
                <h4><?php _e('Acciones de Supervisor', 'wp-cupon-whatsapp'); ?></h4>

                <div class="wpcw-approval-info">
                    <span class="dashicons dashicons-info wpcw-approval-info-icon"></span>
                    <?php _e('Como supervisor, puedes aprobar este convenio, rechazarlo, o solicitar cambios antes de tomar una decisión.', 'wp-cupon-whatsapp'); ?>
                </div>

                <!-- Approve Form -->
                <form method="post" id="approve-form" style="display: none;">
                    <?php wp_nonce_field('wpcw_supervisor_action', 'wpcw_supervisor_nonce'); ?>
                    <input type="hidden" name="wpcw_supervisor_action" value="approve">
                    <input type="hidden" name="convenio_id" value="<?php echo esc_attr($convenio_id); ?>">

                    <div class="wpcw-approval-form-group">
                        <label for="approval_notes"><?php _e('Notas de Aprobación (Opcional)', 'wp-cupon-whatsapp'); ?></label>
                        <textarea name="approval_notes" id="approval_notes" placeholder="<?php esc_attr_e('Añade comentarios sobre esta aprobación...', 'wp-cupon-whatsapp'); ?>"></textarea>
                    </div>

                    <button type="submit" class="button button-primary button-large" style="width: 100%;">
                        <span class="dashicons dashicons-yes"></span> <?php _e('Confirmar Aprobación', 'wp-cupon-whatsapp'); ?>
                    </button>
                </form>

                <!-- Reject Form -->
                <form method="post" id="reject-form" style="display: none;">
                    <?php wp_nonce_field('wpcw_supervisor_action', 'wpcw_supervisor_nonce'); ?>
                    <input type="hidden" name="wpcw_supervisor_action" value="reject">
                    <input type="hidden" name="convenio_id" value="<?php echo esc_attr($convenio_id); ?>">

                    <div class="wpcw-approval-form-group">
                        <label for="rejection_reason"><?php _e('Motivo del Rechazo *', 'wp-cupon-whatsapp'); ?></label>
                        <textarea name="rejection_reason" id="rejection_reason" required placeholder="<?php esc_attr_e('Explica por qué se rechaza este convenio...', 'wp-cupon-whatsapp'); ?>"></textarea>
                    </div>

                    <button type="submit" class="button button-large" style="width: 100%; background: #dc3232; color: white;">
                        <span class="dashicons dashicons-no"></span> <?php _e('Confirmar Rechazo', 'wp-cupon-whatsapp'); ?>
                    </button>
                </form>

                <!-- Request Changes Form -->
                <form method="post" id="changes-form" style="display: none;">
                    <?php wp_nonce_field('wpcw_supervisor_action', 'wpcw_supervisor_nonce'); ?>
                    <input type="hidden" name="wpcw_supervisor_action" value="request_changes">
                    <input type="hidden" name="convenio_id" value="<?php echo esc_attr($convenio_id); ?>">

                    <div class="wpcw-approval-form-group">
                        <label for="change_feedback"><?php _e('Cambios Requeridos *', 'wp-cupon-whatsapp'); ?></label>
                        <textarea name="change_feedback" id="change_feedback" required placeholder="<?php esc_attr_e('Especifica qué cambios deben realizarse antes de aprobar...', 'wp-cupon-whatsapp'); ?>"></textarea>
                    </div>

                    <button type="submit" class="button button-large" style="width: 100%; background: #00a0d2; color: white;">
                        <span class="dashicons dashicons-edit"></span> <?php _e('Solicitar Cambios', 'wp-cupon-whatsapp'); ?>
                    </button>
                </form>

                <!-- Action Buttons -->
                <div class="wpcw-approval-button-group" id="action-buttons">
                    <button type="button" class="wpcw-approval-button approve" onclick="showApproveForm()">
                        <span class="dashicons dashicons-yes"></span> <?php _e('Aprobar', 'wp-cupon-whatsapp'); ?>
                    </button>
                    <button type="button" class="wpcw-approval-button request-changes" onclick="showChangesForm()">
                        <span class="dashicons dashicons-edit"></span> <?php _e('Solicitar Cambios', 'wp-cupon-whatsapp'); ?>
                    </button>
                    <button type="button" class="wpcw-approval-button reject" onclick="showRejectForm()">
                        <span class="dashicons dashicons-no"></span> <?php _e('Rechazar', 'wp-cupon-whatsapp'); ?>
                    </button>
                </div>

                <script>
                function showApproveForm() {
                    document.getElementById('action-buttons').style.display = 'none';
                    document.getElementById('reject-form').style.display = 'none';
                    document.getElementById('changes-form').style.display = 'none';
                    document.getElementById('approve-form').style.display = 'block';
                }

                function showRejectForm() {
                    document.getElementById('action-buttons').style.display = 'none';
                    document.getElementById('approve-form').style.display = 'none';
                    document.getElementById('changes-form').style.display = 'none';
                    document.getElementById('reject-form').style.display = 'block';
                }

                function showChangesForm() {
                    document.getElementById('action-buttons').style.display = 'none';
                    document.getElementById('approve-form').style.display = 'none';
                    document.getElementById('reject-form').style.display = 'none';
                    document.getElementById('changes-form').style.display = 'block';
                }

                // Confirmation for approve
                document.getElementById('approve-form').addEventListener('submit', function(e) {
                    if (!confirm('<?php echo esc_js(__('¿Estás seguro de que deseas aprobar este convenio? Esta acción lo activará inmediatamente.', 'wp-cupon-whatsapp')); ?>')) {
                        e.preventDefault();
                    }
                });

                // Confirmation for reject
                document.getElementById('reject-form').addEventListener('submit', function(e) {
                    if (!confirm('<?php echo esc_js(__('¿Estás seguro de que deseas rechazar este convenio? Esta acción no se puede deshacer.', 'wp-cupon-whatsapp')); ?>')) {
                        e.preventDefault();
                    }
                });
                </script>
            </div>
        <?php elseif ($status === 'pending_supervisor' && !$can_approve): ?>
            <div class="wpcw-approval-info">
                <span class="dashicons dashicons-info wpcw-approval-info-icon"></span>
                <?php _e('Este convenio está pendiente de aprobación por un supervisor. Serás notificado cuando se tome una decisión.', 'wp-cupon-whatsapp'); ?>
            </div>
        <?php endif; ?>

    </div>
    <?php
}

/**
 * Register the approval meta box
 */
function wpcw_register_approval_meta_box() {
    add_meta_box(
        'wpcw_convenio_approval',
        __('Aprobación de Supervisor', 'wp-cupon-whatsapp'),
        'wpcw_render_convenio_approval_meta_box',
        'wpcw_convenio',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'wpcw_register_approval_meta_box');
