<?php
/**
 * WP Cupón WhatsApp - Business Management Admin Pages
 *
 * Handles business list, approval system, and user management
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Render business applications page
 */
function wpcw_render_business_applications_page() {
    // Handle bulk actions
    if ( isset( $_POST['bulk_action'] ) && isset( $_POST['application_ids'] ) ) {
        wpcw_handle_applications_bulk_action();
    }

    // Handle single actions
    if ( isset( $_GET['action'] ) && isset( $_GET['application_id'] ) ) {
        wpcw_handle_single_application_action();
    }

    // Get filter parameters
    $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
    $type_filter = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
    $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
    $paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

    // Get applications
    $args = array(
        'paged' => $paged,
        'posts_per_page' => 20,
    );

    if ( ! empty( $status_filter ) ) {
        $args['application_status'] = $status_filter;
    }

    if ( ! empty( $type_filter ) ) {
        $args['applicant_type'] = $type_filter;
    }

    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    $applications_data = WPCW_Business_Manager::get_business_applications( $args );

    ?>
    <div class="wrap">
        <h1><?php _e( 'Solicitudes de Adhesión', 'wp-cupon-whatsapp' ); ?></h1>

        <!-- Filters -->
        <div class="wpcw-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="wpcw-applications">

                <div class="wpcw-filter-row">
                    <select name="status">
                        <option value=""><?php _e( 'Todos los Estados', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="pendiente_revision" <?php selected( $status_filter, 'pendiente_revision' ); ?>><?php _e( 'Pendiente Revisión', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="aprobada" <?php selected( $status_filter, 'aprobada' ); ?>><?php _e( 'Aprobada', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="rechazada" <?php selected( $status_filter, 'rechazada' ); ?>><?php _e( 'Rechazada', 'wp-cupon-whatsapp' ); ?></option>
                    </select>

                    <select name="type">
                        <option value=""><?php _e( 'Todos los Tipos', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="comercio" <?php selected( $type_filter, 'comercio' ); ?>><?php _e( 'Comercio', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="institucion" <?php selected( $type_filter, 'institucion' ); ?>><?php _e( 'Institución', 'wp-cupon-whatsapp' ); ?></option>
                    </select>

                    <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php _e( 'Buscar...', 'wp-cupon-whatsapp' ); ?>">

                    <button type="submit" class="button"><?php _e( 'Filtrar', 'wp-cupon-whatsapp' ); ?></button>
                    <a href="<?php echo admin_url( 'admin.php?page=wpcw-applications' ); ?>" class="button"><?php _e( 'Limpiar', 'wp-cupon-whatsapp' ); ?></a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions Form -->
        <form method="post" action="">
            <?php wp_nonce_field( 'wpcw_applications_bulk_action', 'wpcw_applications_nonce' ); ?>

            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action">
                        <option value=""><?php _e( 'Acciones en Lote', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="approve"><?php _e( 'Aprobar', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="reject"><?php _e( 'Rechazar', 'wp-cupon-whatsapp' ); ?></option>
                    </select>
                    <button type="submit" class="button action"><?php _e( 'Aplicar', 'wp-cupon-whatsapp' ); ?></button>
                </div>

                <div class="tablenav-pages">
                    <?php
                    echo paginate_links( array(
                        'base' => add_query_arg( 'paged', '%#%' ),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $applications_data['pages'],
                        'current' => $applications_data['current_page'],
                    ) );
                    ?>
                </div>
            </div>

            <!-- Applications Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all">
                        </th>
                        <th scope="col"><?php _e( 'Nombre', 'wp-cupon-whatsapp' ); ?></th>
                        <th scope="col"><?php _e( 'Tipo', 'wp-cupon-whatsapp' ); ?></th>
                        <th scope="col"><?php _e( 'Contacto', 'wp-cupon-whatsapp' ); ?></th>
                        <th scope="col"><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></th>
                        <th scope="col"><?php _e( 'Fecha', 'wp-cupon-whatsapp' ); ?></th>
                        <th scope="col"><?php _e( 'Acciones', 'wp-cupon-whatsapp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $applications_data['applications'] ) ) : ?>
                        <tr>
                            <td colspan="7"><?php _e( 'No se encontraron solicitudes.', 'wp-cupon-whatsapp' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $applications_data['applications'] as $application ) : ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="application_ids[]" value="<?php echo esc_attr( $application['id'] ); ?>">
                                </th>
                                <td>
                                    <strong><?php echo esc_html( $application['title'] ); ?></strong>
                                    <?php if ( ! empty( $application['legal_name'] ) ) : ?>
                                        <br><small><?php echo esc_html( $application['legal_name'] ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="wpcw-applicant-type wpcw-type-<?php echo esc_attr( $application['applicant_type'] ); ?>">
                                        <?php echo esc_html( ucfirst( $application['applicant_type'] ) ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html( $application['contact_person'] ); ?>
                                    <?php if ( ! empty( $application['email'] ) ) : ?>
                                        <br><small><?php echo esc_html( $application['email'] ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="wpcw-status wpcw-status-<?php echo esc_attr( $application['status'] ); ?>">
                                        <?php
                                        switch ( $application['status'] ) {
                                            case 'pendiente_revision':
                                                _e( 'Pendiente', 'wp-cupon-whatsapp' );
                                                break;
                                            case 'aprobada':
                                                _e( 'Aprobada', 'wp-cupon-whatsapp' );
                                                break;
                                            case 'rechazada':
                                                _e( 'Rechazada', 'wp-cupon-whatsapp' );
                                                break;
                                            default:
                                                echo esc_html( $application['status'] );
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $application['created_date'] ) ) ); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="<?php echo admin_url( 'post.php?post=' . $application['id'] . '&action=edit' ); ?>" class="button button-small">
                                            <?php _e( 'Ver', 'wp-cupon-whatsapp' ); ?>
                                        </a>

                                        <?php if ( $application['status'] === 'pendiente_revision' ) : ?>
                                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=wpcw-applications&action=approve&application_id=' . $application['id'] ), 'wpcw_approve_application' ); ?>" class="button button-small button-primary">
                                                <?php _e( 'Aprobar', 'wp-cupon-whatsapp' ); ?>
                                            </a>
                                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=wpcw-applications&action=reject&application_id=' . $application['id'] ), 'wpcw_reject_application' ); ?>" class="button button-small button-link-delete">
                                                <?php _e( 'Rechazar', 'wp-cupon-whatsapp' ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>

        <style>
        .wpcw-filters {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }

        .wpcw-filter-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .wpcw-filter-row select,
        .wpcw-filter-row input[type="text"] {
            min-width: 150px;
        }

        .wpcw-applicant-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .wpcw-type-comercio {
            background: #46b450;
            color: white;
        }

        .wpcw-type-institucion {
            background: #00a0d2;
            color: white;
        }

        .wpcw-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .wpcw-status-pendiente_revision {
            background: #ffb900;
            color: #000;
        }

        .wpcw-status-aprobada {
            background: #46b450;
            color: white;
        }

        .wpcw-status-rechazada {
            background: #dc3232;
            color: white;
        }

        .row-actions .button {
            margin-right: 5px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Select all checkbox
            $('#cb-select-all').on('change', function() {
                $('input[name="application_ids[]"]').prop('checked', $(this).prop('checked'));
            });
        });
        </script>
    </div>
    <?php
}

/**
 * Handle bulk actions for applications
 */
function wpcw_handle_applications_bulk_action() {
    if ( ! isset( $_POST['wpcw_applications_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_applications_nonce'], 'wpcw_applications_bulk_action' ) ) {
        wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
    }

    $action = sanitize_text_field( $_POST['bulk_action'] );
    $application_ids = isset( $_POST['application_ids'] ) ? array_map( 'absint', $_POST['application_ids'] ) : array();

    if ( empty( $application_ids ) ) {
        return;
    }

    $success_count = 0;
    $errors = array();

    foreach ( $application_ids as $application_id ) {
        if ( $action === 'approve' ) {
            $result = WPCW_Business_Manager::approve_application( $application_id );
            if ( is_wp_error( $result ) ) {
                $errors[] = $result->get_error_message();
            } else {
                $success_count++;
            }
        } elseif ( $action === 'reject' ) {
            $result = WPCW_Business_Manager::reject_application( $application_id );
            if ( is_wp_error( $result ) ) {
                $errors[] = $result->get_error_message();
            } else {
                $success_count++;
            }
        }
    }

    // Set admin notice
    if ( $success_count > 0 ) {
        set_transient( 'wpcw_admin_notice', array(
            'type' => 'success',
            'message' => sprintf(
                __( '%d solicitudes procesadas exitosamente.', 'wp-cupon-whatsapp' ),
                $success_count
            ),
        ), 30 );
    }

    if ( ! empty( $errors ) ) {
        set_transient( 'wpcw_admin_notice', array(
            'type' => 'error',
            'message' => implode( '<br>', $errors ),
        ), 30 );
    }

    // Redirect back
    wp_redirect( admin_url( 'admin.php?page=wpcw-applications' ) );
    exit;
}

/**
 * Handle single application actions
 */
function wpcw_handle_single_application_action() {
    $action = sanitize_text_field( $_GET['action'] );
    $application_id = absint( $_GET['application_id'] );

    if ( $action === 'approve' ) {
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wpcw_approve_application' ) ) {
            wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
        }

        $result = WPCW_Business_Manager::approve_application( $application_id );

        if ( is_wp_error( $result ) ) {
            set_transient( 'wpcw_admin_notice', array(
                'type' => 'error',
                'message' => $result->get_error_message(),
            ), 30 );
        } else {
            set_transient( 'wpcw_admin_notice', array(
                'type' => 'success',
                'message' => __( 'Solicitud aprobada exitosamente.', 'wp-cupon-whatsapp' ),
            ), 30 );
        }
    } elseif ( $action === 'reject' ) {
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wpcw_reject_application' ) ) {
            wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
        }

        $result = WPCW_Business_Manager::reject_application( $application_id );

        if ( is_wp_error( $result ) ) {
            set_transient( 'wpcw_admin_notice', array(
                'type' => 'error',
                'message' => $result->get_error_message(),
            ), 30 );
        } else {
            set_transient( 'wpcw_admin_notice', array(
                'type' => 'success',
                'message' => __( 'Solicitud rechazada.', 'wp-cupon-whatsapp' ),
            ), 30 );
        }
    }

    wp_redirect( admin_url( 'admin.php?page=wpcw-applications' ) );
    exit;
}

/**
 * Render businesses management page
 */
function wpcw_render_businesses_page() {
    // Get filter parameters
    $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
    $paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

    // Get businesses
    $args = array(
        'paged' => $paged,
        'posts_per_page' => 20,
    );

    if ( ! empty( $search ) ) {
        $args['s'] = $search;
    }

    $businesses_data = WPCW_Business_Manager::get_businesses( $args );

    ?>
    <div class="wrap">
        <h1><?php _e( 'Gestión de Comercios', 'wp-cupon-whatsapp' ); ?></h1>

        <!-- Filters -->
        <div class="wpcw-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="wpcw-businesses">

                <div class="wpcw-filter-row">
                    <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php _e( 'Buscar comercios...', 'wp-cupon-whatsapp' ); ?>">
                    <button type="submit" class="button"><?php _e( 'Buscar', 'wp-cupon-whatsapp' ); ?></button>
                    <?php if ( ! empty( $search ) ) : ?>
                        <a href="<?php echo admin_url( 'admin.php?page=wpcw-businesses' ); ?>" class="button"><?php _e( 'Limpiar', 'wp-cupon-whatsapp' ); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Businesses Table -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e( 'Nombre del Comercio', 'wp-cupon-whatsapp' ); ?></th>
                    <th scope="col"><?php _e( 'Contacto', 'wp-cupon-whatsapp' ); ?></th>
                    <th scope="col"><?php _e( 'Estadísticas', 'wp-cupon-whatsapp' ); ?></th>
                    <th scope="col"><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></th>
                    <th scope="col"><?php _e( 'Fecha de Aprobación', 'wp-cupon-whatsapp' ); ?></th>
                    <th scope="col"><?php _e( 'Acciones', 'wp-cupon-whatsapp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $businesses_data['businesses'] ) ) : ?>
                    <tr>
                        <td colspan="6"><?php _e( 'No se encontraron comercios.', 'wp-cupon-whatsapp' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $businesses_data['businesses'] as $business ) : ?>
                        <?php $stats = WPCW_Business_Manager::get_business_stats( $business['id'] ); ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $business['title'] ); ?></strong>
                                <?php if ( ! empty( $business['legal_name'] ) ) : ?>
                                    <br><small><?php echo esc_html( $business['legal_name'] ); ?></small>
                                <?php endif; ?>
                                <?php if ( ! empty( $business['cuit'] ) ) : ?>
                                    <br><small>CUIT: <?php echo esc_html( $business['cuit'] ); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo esc_html( $business['contact_person'] ); ?>
                                <?php if ( ! empty( $business['email'] ) ) : ?>
                                    <br><small><?php echo esc_html( $business['email'] ); ?></small>
                                <?php endif; ?>
                                <?php if ( ! empty( $business['whatsapp'] ) ) : ?>
                                    <br><small>WhatsApp: <?php echo esc_html( $business['whatsapp'] ); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="wpcw-business-stats">
                                    <div class="stat-item">
                                        <span class="stat-label"><?php _e( 'Cupones:', 'wp-cupon-whatsapp' ); ?></span>
                                        <span class="stat-value"><?php echo number_format( $stats['coupons_active'] ); ?> / <?php echo number_format( $stats['coupons_total'] ); ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label"><?php _e( 'Canjes:', 'wp-cupon-whatsapp' ); ?></span>
                                        <span class="stat-value"><?php echo number_format( $stats['redemptions_total'] ); ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label"><?php _e( 'Este mes:', 'wp-cupon-whatsapp' ); ?></span>
                                        <span class="stat-value"><?php echo number_format( $stats['redemptions_this_month'] ); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="wpcw-status wpcw-status-active">
                                    <?php _e( 'Activo', 'wp-cupon-whatsapp' ); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $business['approval_date'] ) ) ); ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="<?php echo admin_url( 'post.php?post=' . $business['id'] . '&action=edit' ); ?>" class="button button-small">
                                        <?php _e( 'Editar', 'wp-cupon-whatsapp' ); ?>
                                    </a>
                                    <a href="<?php echo admin_url( 'admin.php?page=wpcw-business-users&business_id=' . $business['id'] ); ?>" class="button button-small">
                                        <?php _e( 'Gestionar Usuarios', 'wp-cupon-whatsapp' ); ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $businesses_data['pages'] > 1 ) : ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links( array(
                        'base' => add_query_arg( 'paged', '%#%' ),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $businesses_data['pages'],
                        'current' => $businesses_data['current_page'],
                    ) );
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <style>
        .wpcw-business-stats {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }

        .stat-value {
            font-size: 12px;
            font-weight: 600;
            color: #007cba;
        }
        </style>
    </div>
    <?php
}

/**
 * Render business user management page
 */
function wpcw_render_business_users_page() {
    $business_id = isset( $_GET['business_id'] ) ? absint( $_GET['business_id'] ) : 0;

    if ( ! $business_id ) {
        echo '<div class="wrap"><h1>Error</h1><p>ID de comercio no válido.</p></div>';
        return;
    }

    $business = get_post( $business_id );
    if ( ! $business || $business->post_type !== 'wpcw_business' ) {
        echo '<div class="wrap"><h1>Error</h1><p>Comercio no encontrado.</p></div>';
        return;
    }

    // Handle user assignment
    if ( isset( $_POST['assign_user'] ) && isset( $_POST['user_id'] ) ) {
        wpcw_handle_user_assignment( $business_id );
    }

    // Handle user removal
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'remove_user' && isset( $_GET['user_id'] ) ) {
        wpcw_handle_user_removal( $business_id );
    }

    // Get current business users
    $business_users = wpcw_get_business_users( $business_id );

    ?>
    <div class="wrap">
        <h1><?php printf( __( 'Usuarios de %s', 'wp-cupon-whatsapp' ), esc_html( $business['post_title'] ) ); ?></h1>

        <div class="wpcw-business-users-section">
            <!-- Current Users -->
            <div class="wpcw-current-users">
                <h2><?php _e( 'Usuarios Asignados', 'wp-cupon-whatsapp' ); ?></h2>

                <?php if ( empty( $business_users ) ) : ?>
                    <p><?php _e( 'No hay usuarios asignados a este comercio.', 'wp-cupon-whatsapp' ); ?></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e( 'Usuario', 'wp-cupon-whatsapp' ); ?></th>
                                <th><?php _e( 'Rol', 'wp-cupon-whatsapp' ); ?></th>
                                <th><?php _e( 'Email', 'wp-cupon-whatsapp' ); ?></th>
                                <th><?php _e( 'Fecha de Asignación', 'wp-cupon-whatsapp' ); ?></th>
                                <th><?php _e( 'Acciones', 'wp-cupon-whatsapp' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $business_users as $user ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $user['display_name'] ); ?></td>
                                    <td><?php echo esc_html( ucfirst( $user['role'] ) ); ?></td>
                                    <td><?php echo esc_html( $user['email'] ); ?></td>
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $user['assigned_date'] ) ) ); ?></td>
                                    <td>
                                        <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=wpcw-business-users&business_id=' . $business_id . '&action=remove_user&user_id=' . $user['ID'] ), 'wpcw_remove_user' ); ?>" class="button button-small button-link-delete">
                                            <?php _e( 'Remover', 'wp-cupon-whatsapp' ); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Assign New User -->
            <div class="wpcw-assign-user">
                <h2><?php _e( 'Asignar Nuevo Usuario', 'wp-cupon-whatsapp' ); ?></h2>

                <form method="post" action="">
                    <?php wp_nonce_field( 'wpcw_assign_user', 'wpcw_assign_user_nonce' ); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e( 'Seleccionar Usuario', 'wp-cupon-whatsapp' ); ?></th>
                            <td>
                                <select name="user_id" required>
                                    <option value=""><?php _e( 'Seleccionar usuario...', 'wp-cupon-whatsapp' ); ?></option>
                                    <?php
                                    $users = get_users( array(
                                        'orderby' => 'display_name',
                                        'order' => 'ASC',
                                    ) );

                                    foreach ( $users as $user ) {
                                        // Skip users already assigned to this business
                                        $assigned_users = wp_list_pluck( $business_users, 'ID' );
                                        if ( in_array( $user->ID, $assigned_users ) ) {
                                            continue;
                                        }

                                        echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->display_name . ' (' . $user->user_email . ')' ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'Rol en el Comercio', 'wp-cupon-whatsapp' ); ?></th>
                            <td>
                                <select name="user_role" required>
                                    <option value="staff"><?php _e( 'Personal', 'wp-cupon-whatsapp' ); ?></option>
                                    <option value="owner"><?php _e( 'Dueño', 'wp-cupon-whatsapp' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" name="assign_user" class="button button-primary" value="<?php _e( 'Asignar Usuario', 'wp-cupon-whatsapp' ); ?>">
                    </p>
                </form>
            </div>
        </div>

        <style>
        .wpcw-business-users-section {
            display: grid;
            gap: 30px;
        }

        .wpcw-current-users,
        .wpcw-assign-user {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
        }

        .wpcw-current-users h2,
        .wpcw-assign-user h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #23282d;
        }
        </style>
    </div>
    <?php
}

/**
 * Get users assigned to a business
 */
function wpcw_get_business_users( $business_id ) {
    $business_access = get_users( array(
        'meta_key' => '_wpcw_business_access',
        'meta_value' => $business_id,
        'meta_compare' => 'LIKE',
    ) );

    $users = array();
    foreach ( $business_access as $user ) {
        $user_roles = get_user_meta( $user->ID, '_wpcw_business_role', true );
        $assigned_date = get_user_meta( $user->ID, '_wpcw_assigned_date', true );

        $users[] = array(
            'ID' => $user->ID,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'role' => $user_roles ?: 'staff',
            'assigned_date' => $assigned_date ?: $user->user_registered,
        );
    }

    return $users;
}

/**
 * Handle user assignment to business
 */
function wpcw_handle_user_assignment( $business_id ) {
    if ( ! isset( $_POST['wpcw_assign_user_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_assign_user_nonce'], 'wpcw_assign_user' ) ) {
        wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
    }

    $user_id = absint( $_POST['user_id'] );
    $user_role = sanitize_text_field( $_POST['user_role'] );

    $result = WPCW_Business_Manager::assign_user_to_business( $user_id, $business_id, $user_role );

    if ( is_wp_error( $result ) ) {
        set_transient( 'wpcw_admin_notice', array(
            'type' => 'error',
            'message' => $result->get_error_message(),
        ), 30 );
    } else {
        set_transient( 'wpcw_admin_notice', array(
            'type' => 'success',
            'message' => __( 'Usuario asignado exitosamente al comercio.', 'wp-cupon-whatsapp' ),
        ), 30 );
    }

    wp_redirect( admin_url( 'admin.php?page=wpcw-business-users&business_id=' . $business_id ) );
    exit;
}

/**
 * Handle user removal from business
 */
function wpcw_handle_user_removal( $business_id ) {
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wpcw_remove_user' ) ) {
        wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
    }

    $user_id = absint( $_GET['user_id'] );

    $result = WPCW_Business_Manager::remove_user_from_business( $user_id, $business_id );

    if ( is_wp_error( $result ) ) {
        set_transient( 'wpcw_admin_notice', array(
            'type' => 'error',
            'message' => $result->get_error_message(),
        ), 30 );
    } else {
        set_transient( 'wpcw_admin_notice', array(
            'type' => 'success',
            'message' => __( 'Usuario removido exitosamente del comercio.', 'wp-cupon-whatsapp' ),
        ), 30 );
    }

    wp_redirect( admin_url( 'admin.php?page=wpcw-business-users&business_id=' . $business_id ) );
    exit;
}

/**
 * Display admin notices
 */
function wpcw_display_admin_notices() {
    $notice = get_transient( 'wpcw_admin_notice' );
    if ( $notice ) {
        echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . ' is-dismissible">';
        echo '<p>' . wp_kses_post( $notice['message'] ) . '</p>';
        echo '</div>';
        delete_transient( 'wpcw_admin_notice' );
    }
}
add_action( 'admin_notices', 'wpcw_display_admin_notices' );