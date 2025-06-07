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
            $data_to_export = array();
            $filename = 'wpcw-export-clientes-' . date('Y-m-d-H-i-s') . '.csv';

            // Definir Cabeceras del CSV
            $headers = array(
                __('ID Usuario', 'wp-cupon-whatsapp'),
                __('Login', 'wp-cupon-whatsapp'),
                __('Email', 'wp-cupon-whatsapp'),
                __('Nombre Mostrado', 'wp-cupon-whatsapp'),
                __('Fecha Registro', 'wp-cupon-whatsapp'),
                __('DNI (WPCW)', 'wp-cupon-whatsapp'),
                __('Fecha Nacimiento (WPCW)', 'wp-cupon-whatsapp'),
                __('WhatsApp (WPCW)', 'wp-cupon-whatsapp'),
                __('ID Institución Usuario (WPCW)', 'wp-cupon-whatsapp'),
                __('Nombre Institución Usuario (WPCW)', 'wp-cupon-whatsapp'),
                __('Categorías Favoritas (WPCW)', 'wp-cupon-whatsapp'),
            );
            $data_to_export[] = $headers;

            // Obtener Datos de Clientes
            $args_users = array(
                'role'    => 'customer', // Solo usuarios con rol 'customer'
                'orderby' => 'ID',
                'order'   => 'ASC',
                // 'fields' => 'all_with_meta', // No es necesario si hacemos get_user_meta individualmente
            );
            $clientes = get_users( $args_users );

            if ( $clientes ) {
                foreach ( $clientes as $cliente ) {
                    $user_id = $cliente->ID;

                    // Obtener nombre de institución
                    $institution_id_meta = get_user_meta( $user_id, '_wpcw_user_institution_id', true );
                    $institution_name = '';
                    if ( $institution_id_meta ) { // Asegurarse de que no sea vacío o 0
                        $institution_id = absint($institution_id_meta);
                        if ($institution_id > 0) {
                            $institution_post = get_post( $institution_id );
                            if ( $institution_post && $institution_post->post_type === 'wpcw_institution' ) {
                                $institution_name = $institution_post->post_title;
                            } else {
                                $institution_name = __('ID Institución Inválido', 'wp-cupon-whatsapp');
                            }
                        }
                    }

                    // Obtener nombres de categorías favoritas
                    $favorite_cat_ids = get_user_meta( $user_id, '_wpcw_user_favorite_coupon_categories', true );
                    $favorite_cat_names_array = array();
                    if ( is_array($favorite_cat_ids) && !empty($favorite_cat_ids) ) {
                        foreach ( $favorite_cat_ids as $term_id_meta ) {
                            $term_id = absint($term_id_meta);
                            if ($term_id > 0) {
                                $term = get_term( $term_id, 'wpcw_coupon_category' );
                                if ( $term && ! is_wp_error( $term ) ) {
                                    $favorite_cat_names_array[] = $term->name;
                                }
                            }
                        }
                    }
                    $favorite_cat_names_str = implode(', ', $favorite_cat_names_array);

                    $row = array(
                        $user_id,
                        $cliente->user_login,
                        $cliente->user_email,
                        $cliente->display_name,
                        date_i18n( get_option('date_format'), strtotime( $cliente->user_registered ) ),
                        get_user_meta( $user_id, '_wpcw_dni_number', true ),
                        get_user_meta( $user_id, '_wpcw_birth_date', true ),
                        get_user_meta( $user_id, '_wpcw_whatsapp_number', true ),
                        $institution_id_meta ? $institution_id_meta : '', // Guardar el ID tal como está en la meta
                        $institution_name,
                        $favorite_cat_names_str,
                    );
                    $data_to_export[] = $row;
                }
            }

            // Generar y Enviar CSV (misma lógica que para comercios/instituciones)
            if (headers_sent()) {
                error_log('WPCW Export Error: Headers already sent before CSV export for clientes.');
                wp_die( __('Error: Las cabeceras ya fueron enviadas, no se puede generar el CSV. Revisa si hay salida inesperada antes de esta acción.', 'wp-cupon-whatsapp') );
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
        case 'cupones':
            $data_to_export = array();
            $filename = 'wpcw-export-cupones-' . date('Y-m-d-H-i-s') . '.csv';

            // Definir Cabeceras del CSV
            $headers = array(
                // Estándar WooCommerce
                __('ID Cupón', 'wp-cupon-whatsapp'),
                __('Código Cupón', 'wp-cupon-whatsapp'),
                __('Descripción', 'wp-cupon-whatsapp'),
                __('Tipo de Descuento', 'wp-cupon-whatsapp'),
                __('Importe del Cupón', 'wp-cupon-whatsapp'),
                __('Permitir Envío Gratuito', 'wp-cupon-whatsapp'),
                __('Fecha de Caducidad', 'wp-cupon-whatsapp'),
                __('Gasto Mínimo', 'wp-cupon-whatsapp'),
                __('Gasto Máximo', 'wp-cupon-whatsapp'),
                __('Uso Individual', 'wp-cupon-whatsapp'),
                __('Excluir Artículos en Oferta', 'wp-cupon-whatsapp'),
                __('IDs de Productos', 'wp-cupon-whatsapp'),
                __('IDs de Productos Excluidos', 'wp-cupon-whatsapp'),
                __('IDs de Categorías de Productos', 'wp-cupon-whatsapp'),
                __('IDs de Categorías de Productos Excluidas', 'wp-cupon-whatsapp'),
                __('Emails Restringidos', 'wp-cupon-whatsapp'),
                __('Límite de Uso por Cupón', 'wp-cupon-whatsapp'),
                __('Límite de Uso por Usuario', 'wp-cupon-whatsapp'),
                // Campos WPCW
                __('Es de Lealtad (WPCW)', 'wp-cupon-whatsapp'),
                __('Es Público (WPCW)', 'wp-cupon-whatsapp'),
                __('ID Comercio Asociado (WPCW)', 'wp-cupon-whatsapp'),
                __('Nombre Comercio Asociado (WPCW)', 'wp-cupon-whatsapp'),
                __('ID Categoría Cupón (WPCW)', 'wp-cupon-whatsapp'),
                __('Nombre Categoría Cupón (WPCW)', 'wp-cupon-whatsapp'),
                __('URL Imagen Cupón (WPCW)', 'wp-cupon-whatsapp'),
                __('Comercios Aplicables (Institución - WPCW IDs)', 'wp-cupon-whatsapp'),
                __('Categorías Aplicables (Institución - WPCW Nombres)', 'wp-cupon-whatsapp'),
            );
            $data_to_export[] = $headers;

            // Obtener Datos de Cupones
            $args_coupons = array(
                'post_type'      => 'shop_coupon',
                'post_status'    => 'any', // Incluir todos los estados (publicado, privado, borrador, etc.)
                'posts_per_page' => -1,
                'orderby'        => 'ID',
                'order'          => 'ASC',
            );
            $coupons_query = new WP_Query( $args_coupons ); // Usar WP_Query

            if ( $coupons_query->have_posts() ) {
                while ( $coupons_query->have_posts() ) {
                    $coupons_query->the_post();
                    $coupon_id = get_the_ID();

                    if (!class_exists('WC_Coupon')) { // Asegurarse de que la clase WC_Coupon esté disponible
                        // Esto es improbable si WooCommerce está activo, pero es una guarda.
                        error_log('WPCW Export Error: WC_Coupon class not found.');
                        // Podríamos optar por no incluir datos de WC_Coupon si la clase no existe,
                        // o detener el proceso. Por ahora, intentaremos continuar con lo que se pueda.
                        $coupon_obj = null;
                    } else {
                        $coupon_obj = new WC_Coupon($coupon_id);
                    }


                    // Campos WPCW
                    $wpcw_associated_business_id = get_post_meta( $coupon_id, '_wpcw_associated_business_id', true );
                    $wpcw_business_name = '';
                    if ( $wpcw_associated_business_id ) {
                        $business_post = get_post( absint($wpcw_associated_business_id) );
                        if ( $business_post && $business_post->post_type === 'wpcw_business') {
                            $wpcw_business_name = $business_post->post_title;
                        }
                    }

                    $wpcw_coupon_category_id = get_post_meta( $coupon_id, '_wpcw_coupon_category_id', true );
                    $wpcw_category_name = '';
                    if ( $wpcw_coupon_category_id ) {
                        $term = get_term( absint($wpcw_coupon_category_id), 'wpcw_coupon_category' );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $wpcw_category_name = $term->name;
                        }
                    }

                    $wpcw_image_id = get_post_meta( $coupon_id, '_wpcw_coupon_image_id', true );
                    $wpcw_image_url = $wpcw_image_id ? wp_get_attachment_url( absint($wpcw_image_id) ) : '';

                    $wpcw_instit_businesses_ids = get_post_meta( $coupon_id, '_wpcw_instit_coupon_applicable_businesses', true );
                    $wpcw_instit_businesses_str = is_array($wpcw_instit_businesses_ids) ? implode(', ', array_map('absint', $wpcw_instit_businesses_ids)) : '';

                    $wpcw_instit_categories_ids = get_post_meta( $coupon_id, '_wpcw_instit_coupon_applicable_categories', true );
                    $wpcw_instit_categories_str = '';
                    if (is_array($wpcw_instit_categories_ids) && !empty($wpcw_instit_categories_ids)) {
                        $term_names = array();
                        foreach($wpcw_instit_categories_ids as $term_id_meta){
                             $term_id = absint($term_id_meta);
                            if ($term_id > 0) {
                                $term = get_term($term_id, 'wpcw_coupon_category');
                                if($term && !is_wp_error($term)){
                                    $term_names[] = $term->name;
                                }
                            }
                        }
                        $wpcw_instit_categories_str = implode(', ', $term_names);
                    }

                    $row = array(
                        // Estándar WooCommerce
                        $coupon_id,
                        $coupon_obj ? $coupon_obj->get_code() : get_the_title($coupon_id), // Fallback al título si WC_Coupon falla
                        $coupon_obj ? $coupon_obj->get_description() : get_the_excerpt($coupon_id),
                        $coupon_obj ? $coupon_obj->get_discount_type() : get_post_meta($coupon_id, 'discount_type', true),
                        $coupon_obj ? $coupon_obj->get_amount() : get_post_meta($coupon_id, 'coupon_amount', true),
                        $coupon_obj ? ($coupon_obj->get_free_shipping() ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp')) : (get_post_meta($coupon_id, 'free_shipping', true) === 'yes' ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp')),
                        $coupon_obj && $coupon_obj->get_date_expires() ? $coupon_obj->get_date_expires()->date('Y-m-d') : get_post_meta($coupon_id, 'date_expires', true),
                        $coupon_obj ? $coupon_obj->get_minimum_amount() : get_post_meta($coupon_id, 'minimum_amount', true),
                        $coupon_obj ? $coupon_obj->get_maximum_amount() : get_post_meta($coupon_id, 'maximum_amount', true),
                        $coupon_obj ? ($coupon_obj->get_individual_use() ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp')) : (get_post_meta($coupon_id, 'individual_use', true) === 'yes' ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp')),
                        $coupon_obj ? ($coupon_obj->get_exclude_sale_items() ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp')) : (get_post_meta($coupon_id, 'exclude_sale_items', true) === 'yes' ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp')),
                        $coupon_obj ? implode(', ', $coupon_obj->get_product_ids()) : implode(', ', (array) get_post_meta($coupon_id, 'product_ids', true)),
                        $coupon_obj ? implode(', ', $coupon_obj->get_excluded_product_ids()) : implode(', ', (array) get_post_meta($coupon_id, 'exclude_product_ids', true)),
                        $coupon_obj ? implode(', ', $coupon_obj->get_product_categories()) : implode(', ', (array) get_post_meta($coupon_id, 'product_categories', true)),
                        $coupon_obj ? implode(', ', $coupon_obj->get_excluded_product_categories()) : implode(', ', (array) get_post_meta($coupon_id, 'exclude_product_categories', true)),
                        $coupon_obj ? implode(', ', $coupon_obj->get_email_restrictions()) : implode(', ', (array) get_post_meta($coupon_id, 'customer_email', true)),
                        $coupon_obj ? $coupon_obj->get_usage_limit() : get_post_meta($coupon_id, 'usage_limit', true),
                        $coupon_obj ? $coupon_obj->get_usage_limit_per_user() : get_post_meta($coupon_id, 'usage_limit_per_user', true),
                        // Campos WPCW
                        get_post_meta( $coupon_id, '_wpcw_is_loyalty_coupon', true ) === 'yes' ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp'),
                        get_post_meta( $coupon_id, '_wpcw_is_public_coupon', true ) === 'yes' ? __('Sí', 'wp-cupon-whatsapp') : __('No', 'wp-cupon-whatsapp'),
                        $wpcw_associated_business_id ? $wpcw_associated_business_id : '',
                        $wpcw_business_name,
                        $wpcw_coupon_category_id ? $wpcw_coupon_category_id : '',
                        $wpcw_category_name,
                        $wpcw_image_url,
                        $wpcw_instit_businesses_str,
                        $wpcw_instit_categories_str,
                    );
                    $data_to_export[] = $row;
                }
                wp_reset_postdata();
            }

            // Generar y Enviar CSV
            if (headers_sent()) {
                error_log('WPCW Export Error: Headers already sent before CSV export for cupones.');
                wp_die( __('Error: Las cabeceras ya fueron enviadas, no se puede generar el CSV.', 'wp-cupon-whatsapp') );
            }

            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            $output_stream = fopen( 'php://output', 'w' );
            foreach ( $data_to_export as $row_data ) {
                fputcsv( $output_stream, $row_data );
            }
            fclose($output_stream); // fclose es bueno aquí

            exit;

            // break; // No se alcanzará
        case 'canjes':
            $data_to_export = array();
            $filename = 'wpcw-export-canjes-' . date('Y-m-d-H-i-s') . '.csv';

            // Definir Cabeceras del CSV
            $headers = array(
                __('ID Canje (DB)', 'wp-cupon-whatsapp'),
                __('Número de Canje', 'wp-cupon-whatsapp'),
                __('ID Cliente', 'wp-cupon-whatsapp'),
                __('Email Cliente', 'wp-cupon-whatsapp'),
                __('ID Cupón Original', 'wp-cupon-whatsapp'),
                __('Código Cupón Original', 'wp-cupon-whatsapp'),
                __('ID Comercio', 'wp-cupon-whatsapp'),
                __('Nombre Comercio', 'wp-cupon-whatsapp'),
                __('Fecha Solicitud Canje', 'wp-cupon-whatsapp'),
                __('Fecha Confirmación Canje', 'wp-cupon-whatsapp'),
                __('Estado Canje', 'wp-cupon-whatsapp'),
                __('Token Confirmación', 'wp-cupon-whatsapp'),
                __('Código Cupón WC Generado', 'wp-cupon-whatsapp'),
                __('ID Pedido WC', 'wp-cupon-whatsapp'),
                __('Origen del Canje', 'wp-cupon-whatsapp'),
                __('Notas Internas', 'wp-cupon-whatsapp'),
            );
            $data_to_export[] = $headers;

            // Obtener Datos de Canjes
            global $wpdb;
            $tabla_canjes = WPCW_CANJES_TABLE_NAME;
            $canjes = $wpdb->get_results( "SELECT * FROM {$tabla_canjes} ORDER BY fecha_solicitud_canje DESC" );

            if ( $canjes ) {
                foreach ( $canjes as $canje ) {
                    $cliente_email = '';
                    if ( $canje->cliente_id ) {
                        $user_data = get_userdata( $canje->cliente_id );
                        if ( $user_data ) {
                            $cliente_email = $user_data->user_email;
                        }
                    }

                    $coupon_code_original = '';
                    if ( $canje->cupon_id ) {
                        $coupon_post = get_post( $canje->cupon_id );
                        if ( $coupon_post ) {
                            $coupon_code_original = $coupon_post->post_title;
                        }
                    }

                    $comercio_name = '';
                    if ( $canje->comercio_id ) {
                        $comercio_post = get_post( $canje->comercio_id );
                        if ( $comercio_post && $comercio_post->post_type === 'wpcw_business' ) {
                            $comercio_name = $comercio_post->post_title;
                        } elseif ($comercio_post) {
                            $comercio_name = __('ID de comercio no es un CPT wpcw_business', 'wp-cupon-whatsapp');
                        }
                    }

                    $estado_legible = function_exists('wpcw_get_displayable_canje_status') ?
                                        wpcw_get_displayable_canje_status($canje->estado_canje) :
                                        $canje->estado_canje;

                    $row = array(
                        $canje->id,
                        $canje->numero_canje,
                        $canje->cliente_id,
                        $cliente_email,
                        $canje->cupon_id,
                        $coupon_code_original,
                        $canje->comercio_id ? $canje->comercio_id : '',
                        $comercio_name,
                        $canje->fecha_solicitud_canje,
                        $canje->fecha_confirmacion_canje ? $canje->fecha_confirmacion_canje : '',
                        $estado_legible,
                        $canje->token_confirmacion,
                        $canje->codigo_cupon_wc ? $canje->codigo_cupon_wc : '',
                        $canje->id_pedido_wc ? $canje->id_pedido_wc : '',
                        $canje->origen_canje ? $canje->origen_canje : '',
                        $canje->notas_internas ? $canje->notas_internas : '',
                    );
                    $data_to_export[] = $row;
                }
            }

            // Generar y Enviar CSV
            if (headers_sent()) {
                error_log('WPCW Export Error: Headers already sent before CSV export for canjes.');
                wp_die( __('Error: Las cabeceras ya fueron enviadas, no se puede generar el CSV.', 'wp-cupon-whatsapp') );
            }

            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            $output_stream = fopen( 'php://output', 'w' );
            foreach ( $data_to_export as $row_data ) {
                fputcsv( $output_stream, $row_data );
            }
            fclose($output_stream); // fclose es bueno aquí

            exit;

            // break; // No se alcanzará
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
