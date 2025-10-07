<?php
/**
 * WPCW - Institution Manager Dashboard Page
 *
 * This file renders the dashboard for the Institution Manager role.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action( 'admin_init', 'wpcw_handle_propose_convenio_form' );

/**
 * Handles the upload of the member CSV file.
 */
function wpcw_handle_member_csv_upload() {
    if ( ! isset( $_POST['submit_member_list'] ) || ! isset( $_FILES['wpcw_members_csv'] ) ) {
        return;
    }

    // --- Security Check by El Guardián ---
    if ( ! isset( $_POST['wpcw_nonce_members'] ) || ! wp_verify_nonce( $_POST['wpcw_nonce_members'], 'wpcw_member_list_upload_nonce' ) ) {
        wp_die( 'Error de seguridad.' );
    }
    if ( ! current_user_can( 'manage_institution' ) ) {
        wp_die( 'No tienes permisos para esta acción.' );
    }

    // --- File Validation ---
    if ( $_FILES['wpcw_members_csv']['error'] !== UPLOAD_ERR_OK ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>Error al subir el archivo. Código: ' . $_FILES['wpcw_members_csv']['error'] . '</p></div>';
        } );
        return;
    }
    if ( $_FILES['wpcw_members_csv']['type'] !== 'text/csv' ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>Error: El archivo debe ser de tipo CSV.</p></div>';
        } );
        return;
    }

    // --- CSV Processing by El Artesano ---
    $file_path = $_FILES['wpcw_members_csv']['tmp_name'];
    $emails = [];
    if ( ( $handle = fopen( $file_path, 'r' ) ) !== FALSE ) {
        $header = fgetcsv( $handle, 1000, "," );
        $email_column_index = array_search( 'email', array_map( 'trim', $header ) );

        if ( $email_column_index === false ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error"><p>Error: El archivo CSV debe contener una columna llamada \'email\'.</p></div>';
            } );
            return;
        }

        while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
            if ( isset( $data[$email_column_index] ) && is_email( $data[$email_column_index] ) ) {
                $emails[] = sanitize_email( $data[$email_column_index] );
            }
        }
        fclose( $handle );
    }

    // --- Data Storage ---
    // This needs a helper function to get the institution ID for the current manager.
    $institution_id = get_user_meta( get_current_user_id(), '_wpcw_institution_id', true );
    if ( ! $institution_id ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>Error: Tu usuario no está asociado a ninguna institución.</p></div>';
        } );
        return;
    }

    update_post_meta( $institution_id, '_wpcw_valid_member_emails', $emails );

    // --- User Feedback ---
    $redirect_url = add_query_arg( 'wpcw_notice', 'members_updated', admin_url( 'admin.php?page=wpcw-institution-dashboard' ) );
    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'admin_init', 'wpcw_handle_member_csv_upload' );


/**
 * Displays admin notices for institution dashboard actions.
 */
function wpcw_institution_admin_notices() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'wpcw-institution-dashboard' || ! isset( $_GET['wpcw_notice'] ) ) {
        return;
    }

    if ( $_GET['wpcw_notice'] === 'members_updated' ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __( '¡Lista de miembros actualizada correctamente!', 'wp-cupon-whatsapp' ) . '</p></div>';
    }
}
add_action( 'admin_notices', 'wpcw_institution_admin_notices' );


/**
 * Renders the content of the Institution Manager dashboard.
 */
function wpcw_render_institution_dashboard_page() {
    ?>
    <div class="wrap wpcw-dashboard-wrap">
        <h1><span class="dashicons dashicons-building"></span> <?php _e( 'Panel de Gestión de Institución', 'wp-cupon-whatsapp' ); ?></h1>
        <p><?php _e( 'Desde aquí puede gestionar sus negocios adheridos, supervisar campañas y analizar estadísticas.', 'wp-cupon-whatsapp' ); ?></p>

        <?php
        // Aquí es donde cargaremos los componentes del dashboard definidos en la Fase 1.
        // Por ahora, dejaremos marcadores de posición.
        ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Columna Principal -->
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        
                        <!-- Marcador para Métricas Clave -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Vista General', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Aquí se mostrarán las métricas clave: total de negocios, canjes del mes, etc.', 'wp-cupon-whatsapp' ); ?></p>
                            </div>
                        </div>

                        <!-- Marcador para Lista de Negocios -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Mis Negocios', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Aquí se mostrará la tabla con los negocios adheridos y la opción de invitar nuevos.', 'wp-cupon-whatsapp' ); ?></p>
                            </div>
                        </div>

                        <!-- Nueva sección para Gestión de Miembros -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Gestión de Miembros Beneficiarios', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Aquí puede gestionar la lista de miembros de su institución que tendrán acceso a los beneficios.', 'wp-cupon-whatsapp' ); ?></p>
                                
                                <form method="post" enctype="multipart/form-data">
                                    <?php wp_nonce_field( 'wpcw_member_list_upload_nonce', 'wpcw_nonce_members' ); ?>
                                    
                                    <h4><?php _e( 'Subir Nueva Lista de Miembros', 'wp-cupon-whatsapp' ); ?></h4>
                                    <p class="description"><?php _e( 'Suba un archivo CSV con una columna llamada \'email\'. Esto reemplazará la lista existente.', 'wp-cupon-whatsapp' ); ?></p>
                                    <p>
                                        <input type="file" name="wpcw_members_csv" id="wpcw_members_csv" accept=".csv">
                                    </p>
                                    
                                    <p class="submit">
                                        <input type="submit" name="submit_member_list" class="button button-primary" value="<?php _e( 'Subir y Reemplazar Lista', 'wp-cupon-whatsapp' ); ?>">
                                    </p>
                                </form>
                                <hr>
                                <h4><?php _e( 'Miembros Válidos Actuales', 'wp-cupon-whatsapp' ); ?></h4>
                                <div class="wpcw-member-list">
                                    <p><em><?php _e( 'Aquí se mostrará la lista de emails de los miembros actualmente válidos.', 'wp-cupon-whatsapp' ); ?></em></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Columna Lateral -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="meta-box-sortables">

                        <!-- Marcador para Campañas -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Campañas Institucionales', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Aquí se mostrará el seguimiento de adhesión a las campañas.', 'wp-cupon-whatsapp' ); ?></p>
                            </div>
                        </div>

                        <!-- Marcador para Acciones Rápidas -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Acciones Rápidas', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><a href="#" class="button button-primary"><?php _e( 'Invitar Negocio', 'wp-cupon-whatsapp' ); ?></a></p>
                                <p><a href="#" class="button button-secondary"><?php _e( 'Exportar Reporte', 'wp-cupon-whatsapp' ); ?></a></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
    <?php
}

/**
 * Adds the Institution Dashboard submenu page, but only for users with the correct capability.
 */
function wpcw_add_institution_dashboard_menu() {
    // Asumimos que el rol 'institution_manager' tiene esta capability única.
    // Esto lo definiremos formalmente al crear los roles.
    $capability = 'manage_institution';

    if ( current_user_can( $capability ) ) {
        add_submenu_page(
            'wpcw-main-dashboard',                  // Slug del menú padre
            __( 'Panel de Institución', 'wp-cupon-whatsapp' ), // Título de la página
            __( 'Panel de Institución', 'wp-cupon-whatsapp' ), // Título del menú
            $capability,                            // Capacidad requerida
            'wpcw-institution-dashboard',           // Slug del menú
            'wpcw_render_institution_dashboard_page', // Callback para renderizar
            1 // Posición dentro del submenú
        );
    }
}
add_action( 'admin_menu', 'wpcw_add_institution_dashboard_menu', 10 ); // Prioridad 10 para que se ejecute después del menú principal
