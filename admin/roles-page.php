<?php
/**
 * Página de administración de roles
 *
 * @package WP_Cupon_WhatsApp
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Agrega la página de administración de roles al menú
 */
function wpcw_add_roles_page() {
    add_submenu_page(
        'wpcw-dashboard',
        __('Administración de Roles', 'wp-cupon-whatsapp'),
        __('Roles', 'wp-cupon-whatsapp'),
        'manage_options',
        'wpcw-roles',
        'wpcw_roles_page_content'
    );
}
add_action('admin_menu', 'wpcw_add_roles_page');

/**
 * Muestra el contenido de la página de roles
 */
function wpcw_roles_page_content() {
    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'wp-cupon-whatsapp'));
    }

    // Procesar acciones
    if (isset($_POST['wpcw_roles_action']) && check_admin_referer('wpcw_roles_nonce')) {
        $action = sanitize_text_field($_POST['wpcw_roles_action']);
        
        switch ($action) {
            case 'update_caps':
                wpcw_update_role_capabilities();
                break;
            case 'assign_role':
                wpcw_assign_role_to_user();
                break;
        }
    }

    ?>
    <div class="wrap">
        <h1><?php _e('Administración de Roles', 'wp-cupon-whatsapp'); ?></h1>

        <div class="wpcw-roles-grid">
            <!-- Resumen de Roles -->
            <div class="wpcw-role-card">
                <h2><?php _e('Dueño de Comercio', 'wp-cupon-whatsapp'); ?></h2>
                <p><?php _e('Puede gestionar su comercio, crear y administrar cupones, y ver reportes.', 'wp-cupon-whatsapp'); ?></p>
                <?php wpcw_display_role_capabilities('wpcw_business_owner'); ?>
            </div>

            <div class="wpcw-role-card">
                <h2><?php _e('Personal de Comercio', 'wp-cupon-whatsapp'); ?></h2>
                <p><?php _e('Puede procesar canjes y ver reportes básicos.', 'wp-cupon-whatsapp'); ?></p>
                <?php wpcw_display_role_capabilities('wpcw_business_staff'); ?>
            </div>
        </div>

        <!-- Formulario para asignar roles -->
        <div class="wpcw-assign-role-form">
            <h2><?php _e('Asignar Rol a Usuario', 'wp-cupon-whatsapp'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('wpcw_roles_nonce'); ?>
                <input type="hidden" name="wpcw_roles_action" value="assign_role" />
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="user_id"><?php _e('Usuario', 'wp-cupon-whatsapp'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_dropdown_users(array(
                                'name' => 'user_id',
                                'role__not_in' => array('administrator'),
                                'show_option_none' => __('Seleccionar usuario...', 'wp-cupon-whatsapp')
                            ));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="role"><?php _e('Rol', 'wp-cupon-whatsapp'); ?></label>
                        </th>
                        <td>
                            <select name="role" id="role">
                                <option value=""><?php _e('Seleccionar rol...', 'wp-cupon-whatsapp'); ?></option>
                                <option value="wpcw_business_owner"><?php _e('Dueño de Comercio', 'wp-cupon-whatsapp'); ?></option>
                                <option value="wpcw_business_staff"><?php _e('Personal de Comercio', 'wp-cupon-whatsapp'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Asignar Rol', 'wp-cupon-whatsapp')); ?>
            </form>
        </div>

        <!-- Lista de usuarios con roles del plugin -->
        <div class="wpcw-users-list">
            <h2><?php _e('Usuarios con Roles del Plugin', 'wp-cupon-whatsapp'); ?></h2>
            <?php wpcw_display_plugin_users(); ?>
        </div>
    </div>
    <?php
}

/**
 * Muestra las capacidades de un rol
 */
function wpcw_display_role_capabilities($role_name) {
    $role = get_role($role_name);
    if (!$role) return;

    echo '<div class="wpcw-capabilities-list">';
    echo '<h3>' . __('Capacidades:', 'wp-cupon-whatsapp') . '</h3>';
    echo '<ul>';
    
    $capability_labels = array(
        'manage_wpcw_redemptions' => __('Gestionar Canjes', 'wp-cupon-whatsapp'),
        'view_wpcw_reports' => __('Ver Reportes', 'wp-cupon-whatsapp'),
        'manage_wpcw_settings' => __('Administrar Configuración', 'wp-cupon-whatsapp'),
        'edit_shop_coupons' => __('Editar Cupones', 'wp-cupon-whatsapp'),
        'publish_shop_coupons' => __('Publicar Cupones', 'wp-cupon-whatsapp'),
        'edit_wpcw_business' => __('Editar Comercio', 'wp-cupon-whatsapp'),
    );

    foreach ($capability_labels as $cap => $label) {
        if (isset($role->capabilities[$cap])) {
            $has_cap = $role->capabilities[$cap] ? '✓' : '✗';
            echo '<li>' . esc_html($label) . ' <span class="wpcw-cap-status">' . $has_cap . '</span></li>';
        }
    }
    
    echo '</ul>';
    echo '</div>';
}

/**
 * Muestra la lista de usuarios con roles del plugin
 */
function wpcw_display_plugin_users() {
    $users = get_users(array(
        'role__in' => array('wpcw_business_owner', 'wpcw_business_staff')
    ));

    if (empty($users)) {
        echo '<p>' . __('No hay usuarios con roles del plugin.', 'wp-cupon-whatsapp') . '</p>';
        return;
    }

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('Usuario', 'wp-cupon-whatsapp') . '</th>';
    echo '<th>' . __('Rol', 'wp-cupon-whatsapp') . '</th>';
    echo '<th>' . __('Comercio', 'wp-cupon-whatsapp') . '</th>';
    echo '<th>' . __('Acciones', 'wp-cupon-whatsapp') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($users as $user) {
        $role = $user->roles[0];
        $role_display = $role === 'wpcw_business_owner' ? 
            __('Dueño de Comercio', 'wp-cupon-whatsapp') : 
            __('Personal de Comercio', 'wp-cupon-whatsapp');

        // Obtener el comercio asociado
        $business_id = get_user_meta($user->ID, '_wpcw_associated_business', true);
        $business_name = $business_id ? get_the_title($business_id) : __('No asignado', 'wp-cupon-whatsapp');

        echo '<tr>';
        echo '<td>' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</td>';
        echo '<td>' . esc_html($role_display) . '</td>';
        echo '<td>' . esc_html($business_name) . '</td>';
        echo '<td>';
        echo '<a href="' . esc_url(add_query_arg(array(
            'page' => 'wpcw_roles',
            'action' => 'edit',
            'user_id' => $user->ID
        ), admin_url('admin.php'))) . '" class="button button-secondary">';
        echo __('Editar', 'wp-cupon-whatsapp');
        echo '</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}

/**
 * Actualiza las capacidades de un rol
 */
function wpcw_update_role_capabilities() {
    if (!current_user_can('manage_wpcw_settings')) {
        return;
    }

    $role_name = sanitize_text_field($_POST['role']);
    $role = get_role($role_name);
    
    if (!$role) {
        add_settings_error(
            'wpcw_roles',
            'role_not_found',
            __('El rol especificado no existe.', 'wp-cupon-whatsapp'),
            'error'
        );
        return;
    }

    $capabilities = array(
        'manage_wpcw_redemptions',
        'view_wpcw_reports',
        'manage_wpcw_settings',
        'edit_shop_coupons',
        'publish_shop_coupons',
        'edit_wpcw_business'
    );

    foreach ($capabilities as $cap) {
        if (isset($_POST['caps'][$cap])) {
            $role->add_cap($cap);
        } else {
            $role->remove_cap($cap);
        }
    }

    add_settings_error(
        'wpcw_roles',
        'caps_updated',
        __('Las capacidades han sido actualizadas.', 'wp-cupon-whatsapp'),
        'success'
    );
}

/**
 * Asigna un rol a un usuario
 */
function wpcw_assign_role_to_user() {
    if (!current_user_can('manage_wpcw_settings')) {
        return;
    }

    $user_id = absint($_POST['user_id']);
    $role = sanitize_text_field($_POST['role']);

    if (!$user_id || !$role) {
        add_settings_error(
            'wpcw_roles',
            'missing_data',
            __('Por favor, selecciona un usuario y un rol.', 'wp-cupon-whatsapp'),
            'error'
        );
        return;
    }

    $user = get_user_by('id', $user_id);
    if (!$user) {
        add_settings_error(
            'wpcw_roles',
            'user_not_found',
            __('Usuario no encontrado.', 'wp-cupon-whatsapp'),
            'error'
        );
        return;
    }

    $user->set_role($role);
    
    add_settings_error(
        'wpcw_roles',
        'role_assigned',
        sprintf(
            __('El rol ha sido asignado a %s correctamente.', 'wp-cupon-whatsapp'),
            $user->display_name
        ),
        'success'
    );
}
