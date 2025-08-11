<?php
/**
 * Manejador de Aprobaciones
 * 
 * Gestiona el proceso de aprobación de comercios e instituciones
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPCW_Approval_Handler {
    /**
     * Inicializa los hooks necesarios
     */
    public static function init() {
        // Hook para nuevo registro de comercio/institución
        add_action('transition_post_status', array(__CLASS__, 'handle_registration'), 10, 3);
        
        // Hook para aprobación/rechazo
        add_action('admin_action_wpcw_approve_business', array(__CLASS__, 'approve_business'));
        add_action('admin_action_wpcw_reject_business', array(__CLASS__, 'reject_business'));
        
        // Añadir columna de estado en el listado
        add_filter('manage_wpcw_business_posts_columns', array(__CLASS__, 'add_status_column'));
        add_action('manage_wpcw_business_posts_custom_column', array(__CLASS__, 'render_status_column'), 10, 2);
        
        // Añadir acciones rápidas
        add_filter('post_row_actions', array(__CLASS__, 'add_quick_actions'), 10, 2);
        
        // Añadir metabox de aprobación
        add_action('add_meta_boxes', array(__CLASS__, 'add_approval_metabox'));
    }

    /**
     * Maneja el cambio de estado de un registro nuevo
     */
    public static function handle_registration($new_status, $old_status, $post) {
        if ($post->post_type !== 'wpcw_business' && $post->post_type !== 'wpcw_institution') {
            return;
        }

        // Si es un nuevo registro y la aprobación automática está desactivada
        if ($old_status === 'new' && !get_option('wpcw_auto_approve_businesses', false)) {
            // Establecer como pendiente
            update_post_meta($post->ID, '_wpcw_approval_status', 'pending');
            
            // Notificar al administrador
            self::notify_admin_new_registration($post);
        }
    }

    /**
     * Notifica al administrador de un nuevo registro
     */
    private static function notify_admin_new_registration($post) {
        $admin_email = get_option('admin_email', 'admin@example.com');
        $subject = sprintf(
            __('[%s] Nueva solicitud de registro: %s', 'wp-cupon-whatsapp'),
            get_bloginfo('name'),
            $post->post_title
        );

        $approve_url = wp_nonce_url(
            admin_url('admin.php?action=wpcw_approve_business&post=' . $post->ID),
            'wpcw_approve_business_' . $post->ID
        );
        
        $reject_url = wp_nonce_url(
            admin_url('admin.php?action=wpcw_reject_business&post=' . $post->ID),
            'wpcw_reject_business_' . $post->ID
        );

        $message = sprintf(
            __("Se ha recibido una nueva solicitud de registro:\n\n" .
               "Nombre: %s\n" .
               "Tipo: %s\n\n" .
               "Para aprobar: %s\n" .
               "Para rechazar: %s\n\n" .
               "O revisa los detalles en el panel de administración: %s",
               'wp-cupon-whatsapp'),
            $post->post_title,
            get_post_type_object($post->post_type)->labels->singular_name,
            $approve_url,
            $reject_url,
            get_edit_post_link($post->ID, '')
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Aprueba un registro
     */
    public static function approve_business() {
        if (!isset($_GET['post'])) {
            wp_die(__('No se especificó el ID del registro.', 'wp-cupon-whatsapp'));
        }

        $post_id = absint($_GET['post']);
        check_admin_referer('wpcw_approve_business_' . $post_id);

        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp'));
        }

        update_post_meta($post_id, '_wpcw_approval_status', 'approved');
        wp_publish_post($post_id);

        // Notificar al comercio/institución
        self::notify_business_approval($post_id, true);

        $edit_link = get_edit_post_link($post_id, 'url');
        $redirect_url = $edit_link ? add_query_arg('approved', '1', $edit_link) : admin_url('edit.php?post_type=' . get_post_type($post_id) . '&approved=1');
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Rechaza un registro
     */
    public static function reject_business() {
        if (!isset($_GET['post'])) {
            wp_die(__('No se especificó el ID del registro.', 'wp-cupon-whatsapp'));
        }

        $post_id = absint($_GET['post']);
        check_admin_referer('wpcw_reject_business_' . $post_id);

        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp'));
        }

        update_post_meta($post_id, '_wpcw_approval_status', 'rejected');
        wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'draft'
        ));

        // Notificar al comercio/institución
        self::notify_business_approval($post_id, false);

        $edit_link = get_edit_post_link($post_id, 'url');
        $redirect_url = $edit_link ? add_query_arg('rejected', '1', $edit_link) : admin_url('edit.php?post_type=' . get_post_type($post_id) . '&rejected=1');
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Notifica al comercio/institución sobre su aprobación/rechazo
     */
    private static function notify_business_approval($post_id, $approved) {
        $post = get_post($post_id);
        $admin_user = get_user_by('id', $post->post_author);
        
        if (!$admin_user) {
            return;
        }

        $subject = sprintf(
            __('[%s] Estado de tu solicitud de registro', 'wp-cupon-whatsapp'),
            get_bloginfo('name')
        );

        if ($approved) {
            $message = sprintf(
                __("¡Buenas noticias! Tu solicitud de registro ha sido aprobada.\n\n" .
                   "Ya puedes acceder a tu cuenta y comenzar a gestionar tus cupones.\n\n" .
                   "Accede aquí: %s",
                   'wp-cupon-whatsapp'),
                wp_login_url()
            );
        } else {
            $message = sprintf(
                __("Lo sentimos, tu solicitud de registro ha sido rechazada.\n\n" .
                   "Si tienes preguntas, por favor contáctanos respondiendo este email.",
                   'wp-cupon-whatsapp')
            );
        }

        wp_mail($admin_user->user_email, $subject, $message);
    }

    /**
     * Añade la columna de estado de aprobación
     */
    public static function add_status_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['approval_status'] = __('Estado Aprobación', 'wp-cupon-whatsapp');
            }
        }
        return $new_columns;
    }

    /**
     * Renderiza el contenido de la columna de estado
     */
    public static function render_status_column($column, $post_id) {
        if ($column !== 'approval_status') {
            return;
        }

        $status = get_post_meta($post_id, '_wpcw_approval_status', true);
        $status_labels = array(
            'pending' => __('Pendiente', 'wp-cupon-whatsapp'),
            'approved' => __('Aprobado', 'wp-cupon-whatsapp'),
            'rejected' => __('Rechazado', 'wp-cupon-whatsapp')
        );

        $status_classes = array(
            'pending' => 'notice-warning',
            'approved' => 'notice-success',
            'rejected' => 'notice-error'
        );

        if (!empty($status)) {
            printf(
                '<span class="wpcw-status %s">%s</span>',
                esc_attr($status_classes[$status]),
                esc_html($status_labels[$status])
            );
        }
    }

    /**
     * Añade acciones rápidas en el listado
     */
    public static function add_quick_actions($actions, $post) {
        if ($post->post_type !== 'wpcw_business' && $post->post_type !== 'wpcw_institution') {
            return $actions;
        }

        if (!current_user_can('manage_options')) {
            return $actions;
        }

        $status = get_post_meta($post->ID, '_wpcw_approval_status', true);

        if ($status === 'pending' || $status === 'rejected') {
            $actions['approve'] = sprintf(
                '<a href="%s" class="wpcw-approve">%s</a>',
                wp_nonce_url(admin_url('admin.php?action=wpcw_approve_business&post=' . $post->ID), 'wpcw_approve_business_' . $post->ID),
                __('Aprobar', 'wp-cupon-whatsapp')
            );
        }

        if ($status === 'pending' || $status === 'approved') {
            $actions['reject'] = sprintf(
                '<a href="%s" class="wpcw-reject">%s</a>',
                wp_nonce_url(admin_url('admin.php?action=wpcw_reject_business&post=' . $post->ID), 'wpcw_reject_business_' . $post->ID),
                __('Rechazar', 'wp-cupon-whatsapp')
            );
        }

        return $actions;
    }

    /**
     * Añade el metabox de aprobación
     */
    public static function add_approval_metabox() {
        add_meta_box(
            'wpcw_approval_status',
            __('Estado de Aprobación', 'wp-cupon-whatsapp'),
            array(__CLASS__, 'render_approval_metabox'),
            array('wpcw_business', 'wpcw_institution'),
            'side',
            'high'
        );
    }

    /**
     * Renderiza el metabox de aprobación
     */
    public static function render_approval_metabox($post) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $status = get_post_meta($post->ID, '_wpcw_approval_status', true);
        $status_labels = array(
            'pending' => __('Pendiente', 'wp-cupon-whatsapp'),
            'approved' => __('Aprobado', 'wp-cupon-whatsapp'),
            'rejected' => __('Rechazado', 'wp-cupon-whatsapp')
        );

        echo '<div class="wpcw-approval-status">';
        echo '<p><strong>' . __('Estado actual:', 'wp-cupon-whatsapp') . '</strong> ';
        echo esc_html($status_labels[$status] ?? __('No definido', 'wp-cupon-whatsapp')) . '</p>';

        if ($status !== 'approved') {
            echo '<p><a href="' . wp_nonce_url(admin_url('admin.php?action=wpcw_approve_business&post=' . $post->ID), 'wpcw_approve_business_' . $post->ID) . '" class="button button-primary">' . __('Aprobar', 'wp-cupon-whatsapp') . '</a></p>';
        }
        
        if ($status !== 'rejected') {
            echo '<p><a href="' . wp_nonce_url(admin_url('admin.php?action=wpcw_reject_business&post=' . $post->ID), 'wpcw_reject_business_' . $post->ID) . '" class="button">' . __('Rechazar', 'wp-cupon-whatsapp') . '</a></p>';
        }
        echo '</div>';
    }
}

// Inicializar el manejador
add_action('init', array('WPCW_Approval_Handler', 'init'));
