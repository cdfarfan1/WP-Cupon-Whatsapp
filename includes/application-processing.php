<?php
/**
 * WP Canje Cupon Whatsapp Application Processing
 *
 * Handles the processing of approved applications.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Hook into save_post_wpcw_application to process when an application is approved.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated or not.
 */
function wpcw_on_save_application( $post_id, $post, $update ) {
    // Verify this is not an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // Check the user's permissions.
    // Use 'edit_post' capability for the specific post being saved.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    // Check if it's the correct post type
    if ( 'wpcw_application' !== $post->post_type ) {
        return $post_id;
    }

    // Get the application status from post meta
    // (Assuming the admin sets this meta field when approving)
    $application_status = get_post_meta( $post_id, '_wpcw_application_status', true );

    // Check if the application is marked as 'aprobada'
    if ( 'aprobada' !== $application_status ) {
        return $post_id;
    }

    // Check if the application has already been processed to prevent duplicate processing
    $is_processed = get_post_meta( $post_id, '_wpcw_application_processed', true );
    if ( $is_processed ) {
        return $post_id;
    }

    // If all checks pass, call the handler function (to be created in the next step)
    // Ensure the handler function exists before calling it.
    if ( function_exists( 'wpcw_handle_approved_application' ) ) {
        wpcw_handle_approved_application( $post_id );
    } else {
        // Log an error if the handler function is missing, which would be a critical issue.
        error_log( 'WPCW Critical Error: wpcw_handle_approved_application function does not exist.' );
    }

    return $post_id; // Not strictly necessary to return but good practice for save_post hooks
}
add_action( 'save_post_wpcw_application', 'wpcw_on_save_application', 10, 3 );

/**
 * Handles the processing of an approved application.
 * Creates a new CPT entry for the business/institution and a new user.
 *
 * @param int $application_id The ID of the approved wpcw_application post.
 */
function wpcw_handle_approved_application( $application_id ) {
    error_log( 'WPCW: Iniciando procesamiento para solicitud ID: ' . $application_id );

    // Recuperar datos de la solicitud
    $applicant_type   = get_post_meta( $application_id, '_wpcw_applicant_type', true );
    $fantasy_name     = get_the_title( $application_id ); // El título del CPT wpcw_application es el nombre de fantasía
    $legal_name       = get_post_meta( $application_id, '_wpcw_legal_name', true );
    $cuit             = get_post_meta( $application_id, '_wpcw_cuit', true );
    $contact_person   = get_post_meta( $application_id, '_wpcw_contact_person', true );
    $email            = get_post_meta( $application_id, '_wpcw_email', true );
    $whatsapp         = get_post_meta( $application_id, '_wpcw_whatsapp', true );
    $address_main     = get_post_meta( $application_id, '_wpcw_address_main', true );
    $application_post = get_post( $application_id );
    $description      = $application_post ? $application_post->post_content : ''; // La descripción se guardó como post_content
    $created_user_id  = get_post_meta( $application_id, '_wpcw_created_user_id', true ); // ID del usuario que llenó el formulario, si estaba logueado

    // Verificación básica de datos recuperados
    if ( empty( $applicant_type ) || empty( $email ) || empty( $fantasy_name ) ) {
        error_log( 'WPCW Error: Faltan datos críticos (tipo, email o nombre fantasía) en la solicitud ID: ' . $application_id );
        // Optionally, update application status to 'error_procesamiento'
        // update_post_meta( $application_id, '_wpcw_application_status', 'error_procesamiento' );
        // update_post_meta( $application_id, '_wpcw_processing_error_details', 'Faltan datos críticos: tipo, email o nombre fantasía.' );
        return;
    }

    // c. Crear CPT (wpcw_business o wpcw_institution)
    $new_cpt_type = '';
    if ( $applicant_type === 'comercio' ) {
        $new_cpt_type = 'wpcw_business';
    } elseif ( $applicant_type === 'institucion' ) {
        $new_cpt_type = 'wpcw_institution';
    } else {
        error_log( "WPCW Error: Tipo de solicitante desconocido ('" . esc_html( $applicant_type ) . "') en la solicitud ID: " . $application_id );
        update_post_meta( $application_id, '_wpcw_processing_error', sprintf( __( 'Tipo de solicitante desconocido: %s', 'wp-cupon-whatsapp' ), esc_html( $applicant_type ) ) );
        return; // Salir si el tipo no es válido
    }

    // $fantasy_name and $description are assumed to be sanitized by get_the_title and get_post_field or direct retrieval.
    // However, for direct use in wp_insert_post, ensure they are what you expect.
    // If $fantasy_name or $description could contain malicious HTML/script (e.g. if they were from post meta directly without sanitization),
    // they should be sanitized here. get_the_title is generally safe. $description from $application_post->post_content is raw.

    $cpt_data   = array(
        'post_title'   => $fantasy_name,
        'post_content' => $description,
        'post_type'    => $new_cpt_type,
        'post_status'  => 'publish', // Or 'draft' if further review of the CPT itself is needed.
    );
    $new_cpt_id = wp_insert_post( $cpt_data, true ); // true para devolver WP_Error en caso de fallo

    if ( is_wp_error( $new_cpt_id ) ) {
        error_log( 'WPCW Error: Falló la creación del CPT para la solicitud ID: ' . $application_id . '. Error: ' . $new_cpt_id->get_error_message() );
        update_post_meta( $application_id, '_wpcw_processing_error', sprintf( __( 'Falló la creación del CPT: %s', 'wp-cupon-whatsapp' ), $new_cpt_id->get_error_message() ) );
        return; // Salir si falla la creación del CPT
    }

    // Guardar metas para el nuevo CPT
    // Variables $legal_name, $cuit, etc., are already retrieved and sanitized.
    update_post_meta( $new_cpt_id, '_wpcw_legal_name', $legal_name );
    update_post_meta( $new_cpt_id, '_wpcw_cuit', $cuit );
    update_post_meta( $new_cpt_id, '_wpcw_contact_person', $contact_person );
    update_post_meta( $new_cpt_id, '_wpcw_email', $email ); // This is the contact email for the business/institution itself
    update_post_meta( $new_cpt_id, '_wpcw_whatsapp', $whatsapp );
    update_post_meta( $new_cpt_id, '_wpcw_address_main', $address_main );
    // El logo (_wpcw_logo_image_id) se gestionará por el admin/usuario más tarde.
    // Link back to the original application CPT
    update_post_meta( $new_cpt_id, '_wpcw_original_application_id', $application_id );

    update_post_meta( $application_id, '_wpcw_processed_entity_id', $new_cpt_id );
    error_log( 'WPCW: CPT ' . esc_html( $new_cpt_type ) . ' (ID: ' . $new_cpt_id . ') creado para solicitud ID: ' . $application_id );

    // d. Crear Usuario WordPress
    // Generar un nombre de usuario único basado en el email
    $base_user_login = sanitize_title( explode( '@', $email )[0] ); // Tomar la parte antes del @ del email
    if ( empty( $base_user_login ) ) { // Fallback si el email es extraño o vacío (aunque ya validado)
        $base_user_login = sanitize_title( $contact_person ? str_replace( ' ', '', (string) $contact_person ) : str_replace( ' ', '', (string) $fantasy_name ) );
    }
    if ( empty( $base_user_login ) ) { // Fallback extremo
        $base_user_login = 'user' . $application_id;
    }

    $user_login = $base_user_login;
    $counter    = 1;
    while ( username_exists( $user_login ) ) {
        $user_login = $base_user_login . $counter;
        ++$counter;
    }

    $user_pass = wp_generate_password( 16, true, true ); // Contraseña más larga
    $user_role = ( $applicant_type === 'comercio' ) ? 'wpcw_business_owner' : 'wpcw_institution_manager';

    $user_data = array(
        'user_login'   => $user_login,
        'user_pass'    => $user_pass,
        'user_email'   => $email, // Email de contacto de la solicitud
        'role'         => $user_role,
        'display_name' => $contact_person ? $contact_person : $fantasy_name,
        'first_name'   => $contact_person ? explode( ' ', $contact_person )[0] : '', // Intento de primer nombre
        // 'last_name' => podrías intentar obtenerlo también de $contact_person
    );
    $user_id = wp_insert_user( $user_data );

    if ( is_wp_error( $user_id ) ) {
        error_log( 'WPCW Error: Falló la creación del usuario para la solicitud ID: ' . $application_id . '. Login: ' . esc_html( $user_login ) . '. Error: ' . $user_id->get_error_message() );
        update_post_meta( $application_id, '_wpcw_processing_error', sprintf( __( 'Falló la creación del usuario: %s', 'wp-cupon-whatsapp' ), $user_id->get_error_message() ) );
        // Considerar rollback del CPT creado: wp_delete_post($new_cpt_id, true);
        // Por ahora, para simplificar, no se hace rollback automático. El admin tendría que arreglarlo o rechazar la solicitud.
        return; // Salir si falla la creación del usuario
    }
    error_log( 'WPCW: Usuario (ID: ' . $user_id . ', Login: ' . esc_html( $user_login ) . ') creado para solicitud ID: ' . $application_id );

    // e. Asociar Usuario con CPT
    // $new_cpt_id, $user_id, $applicant_type are available here.
    // $new_cpt_type is also available which is 'wpcw_business' or 'wpcw_institution'

    if ( $applicant_type === 'comercio' ) {
        update_post_meta( $new_cpt_id, '_wpcw_owner_user_id', $user_id );
        update_user_meta( $user_id, '_wpcw_associated_entity_id', $new_cpt_id );
        update_user_meta( $user_id, '_wpcw_associated_entity_type', $new_cpt_type ); // 'wpcw_business'
        error_log( 'WPCW: Usuario ID ' . $user_id . ' asociado como owner al CPT business ID ' . $new_cpt_id );
    } elseif ( $applicant_type === 'institucion' ) {
        update_post_meta( $new_cpt_id, '_wpcw_manager_user_id', $user_id ); // Using a distinct meta key for clarity if needed, or could be generic like _wpcw_managing_user_id
        update_user_meta( $user_id, '_wpcw_associated_entity_id', $new_cpt_id );
        update_user_meta( $user_id, '_wpcw_associated_entity_type', $new_cpt_type ); // 'wpcw_institution'
        error_log( 'WPCW: Usuario ID ' . $user_id . ' asociado como manager al CPT institution ID ' . $new_cpt_id );
    } else {
        // This situation should not occur if previous validations are correct,
        // but it's good to have a log just in case.
        error_log( 'WPCW Critical Error: Intentando asociar usuario a CPT con tipo de aplicante desconocido: ' . esc_html( $applicant_type ) . ' para CPT ID ' . $new_cpt_id . ' y User ID ' . $user_id );
    }

    // TODO: Enviar Notificación al Nuevo Usuario (siguiente paso, incluir $user_pass)
    // f. Enviar Notificación al Nuevo Usuario
    // El tercer parámetro 'user' notifica solo al usuario y le da el enlace para establecer contraseña.
    // 'both' notificaría también al admin, 'admin' solo al admin.
    // 'none' para no enviar ninguna (si quisiéramos manejarlo 100% manual).
    // Usar 'user' es apropiado aquí ya que el admin está realizando la acción de aprobación.
    // La contraseña $user_pass fue generada previamente, wp_new_user_notification la necesita si el segundo param es deprecated.
    // Sin embargo, a partir de WP 4.3.1, el segundo parámetro de wp_new_user_notification() es $deprecated (pasado como null)
    // y la función se encarga de enviar el correo con el enlace de reseteo de contraseña.
    if ( function_exists( 'wp_new_user_notification' ) ) {
        wp_new_user_notification( $user_id, null, 'user' );
        error_log( 'WPCW: Notificación estándar de nuevo usuario enviada para User ID: ' . $user_id );
    } else {
        error_log( 'WPCW Alerta: La función wp_new_user_notification() no existe. No se pudo notificar al usuario ID: ' . $user_id );
        // Considerar un email manual de fallback si esto fuera un problema común (no debería serlo).
    }

    // TODO: Marcar la solicitud como procesada (siguiente paso)
    // g. Marcar Solicitud como Procesada
    update_post_meta( $application_id, '_wpcw_application_processed', true );
    // Optionally, set a final status like 'completada' if it's different from 'aprobada'
    // update_post_meta( $application_id, '_wpcw_application_status', 'completada' );

    $processing_log_message = sprintf(
        __( 'Solicitud procesada exitosamente el %1$s. Tipo de entidad creada: %2$s (ID: %3$d). Usuario creado (ID: %4$d).', 'wp-cupon-whatsapp' ),
        current_time( 'mysql' ),
        esc_html( $new_cpt_type ), // $new_cpt_type was defined earlier
        $new_cpt_id,
        $user_id
    );
    update_post_meta( $application_id, '_wpcw_processing_log', $processing_log_message );

    // Limpiar cualquier error de procesamiento previo si llegamos aquí con éxito.
    delete_post_meta( $application_id, '_wpcw_processing_error' );

    error_log( 'WPCW: Procesamiento completado y marcado como procesada para Solicitud ID: ' . $application_id . '. Log: ' . $processing_log_message );
}
