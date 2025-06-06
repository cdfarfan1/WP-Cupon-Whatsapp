<?php
/**
 * WP Canje Cupon Whatsapp Export Functions
 *
 * Functions for generating and downloading CSV exports.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles the generation and download of CSV files based on export type.
 * This function is expected to call exit() after sending the CSV file.
 *
 * @param string $export_type The type of data to export
 *                            (e.g., 'comercios', 'instituciones', 'clientes', 'cupones', 'canjes').
 */
function wpcw_generate_and_download_csv( $export_type ) {
    // Asegurarse de que el usuario tiene permisos (doble check, aunque ya se hizo en el handler)
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'No tienes permisos para realizar esta acción de exportación.', 'wp-cupon-whatsapp' ) );
    }

    // Nonce ya verificado en el handler de admin_init (wpcw_handle_admin_actions).

    $type = sanitize_key( $export_type ); // Sanitizar el tipo de exportación

    // Aquí iría la lógica para generar el CSV basado en $type.
    // Por ahora, solo placeholders con wp_die().

    switch ( $type ) {
        case 'comercios':
            $data_to_export = array();
            $filename = 'wpcw-export-comercios-' . date('Y-m-d-H-i-s') . '.csv';

            // Definir Cabeceras del CSV
            $headers = array(
                __('ID Comercio (Post ID)', 'wp-cupon-whatsapp'),
                __('Nombre Fantasía (Título)', 'wp-cupon-whatsapp'),
                __('Nombre Legal', 'wp-cupon-whatsapp'),
                __('CUIT', 'wp-cupon-whatsapp'),
                __('Persona de Contacto', 'wp-cupon-whatsapp'),
                __('Email Contacto', 'wp-cupon-whatsapp'),
                __('WhatsApp Contacto', 'wp-cupon-whatsapp'),
                __('Dirección Principal', 'wp-cupon-whatsapp'),
                __('ID Usuario Dueño', 'wp-cupon-whatsapp'),
                __('Email Usuario Dueño', 'wp-cupon-whatsapp'),
                // __('ID Logo', 'wp-cupon-whatsapp'), // Podríamos añadir URL del logo si es necesario
            );
            $data_to_export[] = $headers;

            // Obtener Datos de Comercios
            $args = array(
                'post_type'      => 'wpcw_business',
                'post_status'    => 'publish',
                'posts_per_page' => -1, // Todos los comercios
                'orderby'        => 'title',
                'order'          => 'ASC',
            );
            $comercios_query = new WP_Query( $args ); // Usar WP_Query para poder usar wp_reset_postdata si fuera necesario después

            if ( $comercios_query->have_posts() ) {
                while ( $comercios_query->have_posts() ) {
                    $comercios_query->the_post();
                    $comercio_id = get_the_ID();
                    $comercio_title = get_the_title();

                    $owner_user_id = get_post_meta( $comercio_id, '_wpcw_owner_user_id', true );
                    $owner_email = '';
                    if ( $owner_user_id ) {
                        $user_data = get_userdata( $owner_user_id );
                        if ( $user_data ) {
                            $owner_email = $user_data->user_email;
                        }
                    }

                    $row = array(
                        $comercio_id,
                        $comercio_title, // Nombre de Fantasía
                        get_post_meta( $comercio_id, '_wpcw_legal_name', true ),
                        get_post_meta( $comercio_id, '_wpcw_cuit', true ),
                        get_post_meta( $comercio_id, '_wpcw_contact_person', true ),
                        get_post_meta( $comercio_id, '_wpcw_email', true ),
                        get_post_meta( $comercio_id, '_wpcw_whatsapp', true ),
                        get_post_meta( $comercio_id, '_wpcw_address_main', true ),
                        $owner_user_id ? $owner_user_id : '', // Asegurar que sea string
                        $owner_email,
                        // get_post_meta( $comercio_id, '_wpcw_logo_image_id', true ),
                    );
                    $data_to_export[] = $row;
                }
                wp_reset_postdata(); // Importante después de un loop personalizado con WP_Query
            }

            // Generar y Enviar CSV
            // Asegurarse de que no haya salida previa
            if (headers_sent()) {
                error_log('WPCW Export Error: Headers already sent. Cannot initiate CSV download for comercios.');
                wp_die( esc_html__( 'Error al iniciar la descarga del CSV. Revisa los logs del servidor.', 'wp-cupon-whatsapp' ) );
            }

            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' ); // Comillas en filename
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            $output_stream = fopen( 'php://output', 'w' );

            // Escribir BOM para UTF-8 (opcional, pero ayuda a Excel con caracteres especiales)
            // fprintf($output_stream, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ( $data_to_export as $row_data ) {
                fputcsv( $output_stream, $row_data );
            }
            fclose($output_stream);

            exit; // Terminar ejecución después de enviar el archivo
            // break; // No se alcanzará debido al exit()
        case 'instituciones':
            $data_to_export = array();
            $filename = 'wpcw-export-instituciones-' . date('Y-m-d-H-i-s') . '.csv';

            // Definir Cabeceras del CSV
            $headers = array(
                __('ID Institución (Post ID)', 'wp-cupon-whatsapp'),
                __('Nombre Institución (Título)', 'wp-cupon-whatsapp'),
                __('Nombre Legal', 'wp-cupon-whatsapp'),
                __('CUIT', 'wp-cupon-whatsapp'),
                __('Persona de Contacto', 'wp-cupon-whatsapp'),
                __('Email Contacto', 'wp-cupon-whatsapp'),
                __('WhatsApp Contacto', 'wp-cupon-whatsapp'),
                __('Dirección Principal', 'wp-cupon-whatsapp'),
                __('ID Usuario Gestor', 'wp-cupon-whatsapp'),
                __('Email Usuario Gestor', 'wp-cupon-whatsapp'),
                // __('ID Logo', 'wp-cupon-whatsapp'), // Podríamos añadir URL del logo si es necesario
            );
            $data_to_export[] = $headers;

            // Obtener Datos de Instituciones
            $args = array(
                'post_type'      => 'wpcw_institution',
                'post_status'    => 'publish',
                'posts_per_page' => -1, // Todas las instituciones
                'orderby'        => 'title',
                'order'          => 'ASC',
            );
            $instituciones_query = new WP_Query( $args ); // Usar WP_Query para consistencia y wp_reset_postdata

            if ( $instituciones_query->have_posts() ) {
                while ( $instituciones_query->have_posts() ) {
                    $instituciones_query->the_post();
                    $institucion_id = get_the_ID();
                    $institucion_title = get_the_title();

                    $manager_user_id = get_post_meta( $institucion_id, '_wpcw_manager_user_id', true );
                    $manager_email = '';
                    if ( $manager_user_id ) {
                        $user_data = get_userdata( $manager_user_id );
                        if ( $user_data ) {
                            $manager_email = $user_data->user_email;
                        }
                    }

                    // Asumimos que los nombres de los metas son análogos a wpcw_business
                    $row = array(
                        $institucion_id,
                        $institucion_title, // Nombre de la Institución
                        get_post_meta( $institucion_id, '_wpcw_legal_name', true ),
                        get_post_meta( $institucion_id, '_wpcw_cuit', true ),
                        get_post_meta( $institucion_id, '_wpcw_contact_person', true ),
                        get_post_meta( $institucion_id, '_wpcw_email', true ),
                        get_post_meta( $institucion_id, '_wpcw_whatsapp', true ),
                        get_post_meta( $institucion_id, '_wpcw_address_main', true ),
                        $manager_user_id ? $manager_user_id : '',
                        $manager_email,
                        // get_post_meta( $institucion_id, '_wpcw_logo_image_id', true ),
                    );
                    $data_to_export[] = $row;
                }
                wp_reset_postdata();
            }

            // Generar y Enviar CSV
            if (headers_sent()) {
                error_log('WPCW Export Error: Headers already sent. Cannot initiate CSV download for instituciones.');
                wp_die( __('Error: Las cabeceras ya fueron enviadas, no se puede generar el CSV.', 'wp-cupon-whatsapp') );
            }

            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            $output_stream = fopen( 'php://output', 'w' );

            // fprintf($output_stream, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 (opcional)

            foreach ( $data_to_export as $row_data ) {
                fputcsv( $output_stream, $row_data );
            }
            fclose($output_stream);

            exit;

            // break; // No se alcanzará
        case 'clientes':
            // TODO: Implementar lógica de exportación para Clientes
            wp_die(
                sprintf( esc_html__( 'La exportación para "%s" está en desarrollo.', 'wp-cupon-whatsapp' ), esc_html__( 'Clientes', 'wp-cupon-whatsapp' ) ),
                esc_html__( 'Exportación en Progreso', 'wp-cupon-whatsapp' ),
                array('response' => 200, 'link_text' => esc_html__('Volver a Ajustes', 'wp-cupon-whatsapp'), 'link_url' => admin_url('admin.php?page=wpcw-main-menu'))
            );
            break;
        case 'cupones':
            // TODO: Implementar lógica de exportación para Cupones
            wp_die(
                sprintf( esc_html__( 'La exportación para "%s" está en desarrollo.', 'wp-cupon-whatsapp' ), esc_html__( 'Cupones', 'wp-cupon-whatsapp' ) ),
                esc_html__( 'Exportación en Progreso', 'wp-cupon-whatsapp' ),
                array('response' => 200, 'link_text' => esc_html__('Volver a Ajustes', 'wp-cupon-whatsapp'), 'link_url' => admin_url('admin.php?page=wpcw-main-menu'))
            );
            break;
        case 'canjes':
            // TODO: Implementar lógica de exportación para Canjes
            wp_die(
                sprintf( esc_html__( 'La exportación para "%s" está en desarrollo.', 'wp-cupon-whatsapp' ), esc_html__( 'Canjes', 'wp-cupon-whatsapp' ) ),
                esc_html__( 'Exportación en Progreso', 'wp-cupon-whatsapp' ),
                array('response' => 200, 'link_text' => esc_html__('Volver a Ajustes', 'wp-cupon-whatsapp'), 'link_url' => admin_url('admin.php?page=wpcw-main-menu'))
            );
            break;
        default:
            wp_die(
                esc_html__( 'Tipo de exportación no válido o no especificado.', 'wp-cupon-whatsapp' ),
                esc_html__( 'Error de Exportación', 'wp-cupon-whatsapp' ),
                array('response' => 400, 'back_link' => true)
            );
            break;
    }

    // La función real de exportación llamaría a exit() después de enviar las cabeceras y el archivo.
    // Como wp_die() llama a exit(), no necesitamos un exit() explícito aquí para los placeholders.
}

?>
