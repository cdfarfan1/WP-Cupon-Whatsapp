<?php
/**
 * WPCW - Seeder Class for dummy data generation.
 *
 * Genera datos de ejemplo para desarrollo y testing con emails y teléfonos reales
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WPCW_Seeder {

    private static $seeded_meta_key = '_wpcw_is_seeded';

    /**
     * Emails reales para testing
     */
    private static $test_emails = [
        'farfancris@gmail.com',
        'criis2709@gmail.com',
    ];

    /**
     * Teléfonos reales para testing
     */
    private static $test_phones = [
        '+5493883349901',
        '+5493885214566',
    ];

    /**
     * Master function to seed all data.
     */
    public static function seed_all() {
        $results = [];

        $results['institutions'] = self::seed_institutions( 3 );
        $results['businesses'] = self::seed_businesses( 10 );
        $results['convenios'] = self::seed_convenios( 8 );
        $results['coupons'] = self::seed_coupons( 30 );
        $results['beneficiaries'] = self::seed_beneficiaries( 20 );
        $results['business_owners'] = self::seed_business_owners( 5 );
        $results['business_staff'] = self::seed_business_staff( 15 );
        $results['institution_users'] = self::seed_institution_users( 3 );
        $results['redemptions'] = self::seed_redemptions( 50 );

        $total = array_sum($results);
        return ['message' => sprintf('✅ Ecosistema generado: %d elementos creados (%d instituciones, %d comercios, %d convenios, %d cupones, %d beneficiarios, %d dueños de comercio, %d empleados/vendedores, %d usuarios institución, %d canjes)',
            $total,
            $results['institutions'],
            $results['businesses'],
            $results['convenios'],
            $results['coupons'],
            $results['beneficiaries'],
            $results['business_owners'],
            $results['business_staff'],
            $results['institution_users'],
            $results['redemptions']
        )];
    }

    /**
     * Master function to clear all seeded data.
     */
    public static function clear_all() {
        global $wpdb;

        $counts = [
            'posts' => 0,
            'users' => 0,
            'redemptions' => 0,
        ];

        // Delete Posts (CPTs)
        $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", self::$seeded_meta_key ) );
        foreach ( $post_ids as $post_id ) {
            if ( wp_delete_post( $post_id, true ) ) {
                $counts['posts']++;
            }
        }

        // Delete Users
        $user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s", self::$seeded_meta_key ) );
        require_once( ABSPATH . 'wp-admin/includes/user.php' );
        foreach ( $user_ids as $user_id ) {
            if ( wp_delete_user( $user_id ) ) {
                $counts['users']++;
            }
        }

        // Delete redemptions from custom table (marked with [SEEDED] in notas_internas)
        $table_name = $wpdb->prefix . 'wpcw_canjes';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
            $deleted = $wpdb->query(
                "DELETE FROM {$table_name} WHERE notas_internas LIKE '[SEEDED]%' OR origen_canje = 'seeder'"
            );
            $counts['redemptions'] = $deleted !== false ? $deleted : 0;
        }

        $total = array_sum($counts);
        return ['message' => sprintf('🗑️ Limpieza completada: %d elementos eliminados (%d posts, %d usuarios, %d canjes)',
            $total,
            $counts['posts'],
            $counts['users'],
            $counts['redemptions']
        )];
    }

    /**
     * Seed institutions with real test data
     */
    private static function seed_institutions( $count ) {
        $institution_names = [
            'Municipalidad de San Fernando del Valle de Catamarca',
            'Gobierno de la Provincia de Catamarca',
            'Universidad Nacional de Catamarca',
            'Hospital San Juan Bautista',
            'Policía de Catamarca',
        ];

        $created = 0;
        for ( $i = 0; $i < min($count, count($institution_names)); $i++ ) {
            $email = self::$test_emails[$i % count(self::$test_emails)];
            $phone = self::$test_phones[$i % count(self::$test_phones)];

            $post_id = wp_insert_post( [
                'post_title' => $institution_names[$i],
                'post_type' => 'wpcw_institution',
                'post_status' => 'publish',
                'post_content' => 'Institución de ejemplo para testing y desarrollo',
            ] );

            if ( $post_id && ! is_wp_error($post_id) ) {
                update_post_meta( $post_id, self::$seeded_meta_key, true );
                update_post_meta( $post_id, '_institution_email', $email );
                update_post_meta( $post_id, '_institution_phone', $phone );
                update_post_meta( $post_id, '_institution_address', 'Av. Belgrano ' . rand(100, 999) . ', Catamarca' );
                update_post_meta( $post_id, '_institution_cuit', '30-' . rand(10000000, 99999999) . '-' . rand(0,9) );
                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed businesses (comercios) with real test data
     */
    private static function seed_businesses( $count ) {
        $business_types = ['Restaurante', 'Panadería', 'Farmacia', 'Supermercado', 'Librería', 'Ferretería', 'Ropa', 'Calzado', 'Electrónica', 'Gimnasio'];
        $business_names = [
            'La Esquina del Sabor',
            'Panadería Don Juan',
            'Farmacia Central',
            'Supermercado El Ahorro',
            'Librería Catamarca',
            'Ferretería San Martín',
            'Boutique Fashion',
            'Calzados Piero',
            'Electro Hogar',
            'Gym Fitness Center',
        ];

        $created = 0;
        for ( $i = 0; $i < min($count, count($business_names)); $i++ ) {
            $email = self::$test_emails[$i % count(self::$test_emails)];
            $phone = self::$test_phones[$i % count(self::$test_phones)];

            $post_id = wp_insert_post( [
                'post_title' => $business_names[$i],
                'post_type' => 'wpcw_business',
                'post_status' => 'publish',
                'post_content' => 'Comercio de ejemplo para testing - ' . $business_types[$i % count($business_types)],
            ] );

            if ( $post_id && ! is_wp_error($post_id) ) {
                update_post_meta( $post_id, self::$seeded_meta_key, true );
                update_post_meta( $post_id, '_business_email', $email );
                update_post_meta( $post_id, '_business_phone', $phone );
                update_post_meta( $post_id, '_business_whatsapp', $phone );
                update_post_meta( $post_id, '_business_address', 'Calle ' . rand(1, 50) . ' N° ' . rand(100, 999) . ', Catamarca' );
                update_post_meta( $post_id, '_business_category', $business_types[$i % count($business_types)] );
                update_post_meta( $post_id, '_business_cuit', '20-' . rand(10000000, 99999999) . '-' . rand(0,9) );
                update_post_meta( $post_id, '_business_status', 'approved' );
                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed convenios (agreements between institutions and businesses)
     */
    private static function seed_convenios( $count ) {
        $institutions = get_posts(['post_type' => 'wpcw_institution', 'posts_per_page' => -1, 'fields' => 'ids']);
        $businesses = get_posts(['post_type' => 'wpcw_business', 'posts_per_page' => -1, 'fields' => 'ids']);

        if ( empty($institutions) || empty($businesses) ) {
            return 0;
        }

        $created = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $provider = $businesses[array_rand($businesses)];
            $recipient = $institutions[array_rand($institutions)];

            $provider_name = get_the_title($provider);
            $recipient_name = get_the_title($recipient);

            $post_id = wp_insert_post( [
                'post_title' => 'Convenio: ' . $provider_name . ' - ' . $recipient_name,
                'post_type' => 'wpcw_convenio',
                'post_status' => 'publish',
                'post_content' => sprintf('Convenio de descuentos entre %s y %s. Vigencia: %s', $provider_name, $recipient_name, date('Y')),
            ] );

            if ( $post_id && ! is_wp_error($post_id) ) {
                update_post_meta( $post_id, self::$seeded_meta_key, true );
                update_post_meta( $post_id, '_convenio_provider_id', $provider );
                update_post_meta( $post_id, '_convenio_recipient_id', $recipient );
                update_post_meta( $post_id, '_convenio_status', 'active' );
                update_post_meta( $post_id, '_convenio_discount_percentage', rand(10, 30) );
                update_post_meta( $post_id, '_convenio_start_date', date('Y-m-d', strtotime('-30 days')) );
                update_post_meta( $post_id, '_convenio_end_date', date('Y-m-d', strtotime('+365 days')) );
                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed WooCommerce coupons associated with convenios
     */
    private static function seed_coupons( $count ) {
        $convenios = get_posts(['post_type' => 'wpcw_convenio', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids']);

        if ( empty($convenios) ) {
            return 0;
        }

        $created = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $code = 'TEST' . strtoupper(wp_generate_password( 6, false, false ));
            $convenio_id = $convenios[array_rand($convenios)];
            $discount = rand(10, 50);

            $post_id = wp_insert_post( [
                'post_title' => $code,
                'post_type' => 'shop_coupon',
                'post_status' => 'publish',
                'post_excerpt' => 'Cupón de ejemplo para testing - ' . $discount . '% de descuento',
            ] );

            if ( $post_id && ! is_wp_error($post_id) ) {
                update_post_meta( $post_id, self::$seeded_meta_key, true );
                update_post_meta( $post_id, 'discount_type', 'percent' );
                update_post_meta( $post_id, 'coupon_amount', $discount );
                update_post_meta( $post_id, 'individual_use', 'yes' );
                update_post_meta( $post_id, 'usage_limit', '1' );
                update_post_meta( $post_id, 'usage_limit_per_user', '1' );
                update_post_meta( $post_id, 'expiry_date', date('Y-m-d', strtotime('+90 days')) );
                update_post_meta( $post_id, '_wpcw_associated_convenio_id', $convenio_id );
                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed beneficiary users with real test emails and phones
     */
    private static function seed_beneficiaries( $count ) {
        $institutions = get_posts(['post_type' => 'wpcw_institution', 'posts_per_page' => -1, 'fields' => 'ids']);

        if ( empty($institutions) ) {
            return 0;
        }

        $first_names = ['Juan', 'María', 'Carlos', 'Ana', 'Roberto', 'Laura', 'Diego', 'Sofía', 'Martín', 'Valentina', 'Lucas', 'Camila', 'Franco', 'Lucía', 'Mateo', 'Emma', 'Santiago', 'Isabella', 'Nicolás', 'Mía'];
        $last_names = ['González', 'Rodríguez', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Díaz', 'Torres', 'Ramírez', 'Flores', 'Castro', 'Morales', 'Jiménez', 'Rojas', 'Herrera', 'Mendoza', 'Silva', 'Vargas'];

        $created = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $first_name = $first_names[array_rand($first_names)];
            $last_name = $last_names[array_rand($last_names)];
            $username = 'beneficiario_' . strtolower($first_name) . '_' . $i;

            // Alternar entre los emails reales proporcionados
            $email = self::$test_emails[$i % count(self::$test_emails)];
            $phone = self::$test_phones[$i % count(self::$test_phones)];

            // Agregar sufijo si el email ya existe
            $email_final = $email;
            $counter = 1;
            while ( email_exists( $email_final ) ) {
                $email_parts = explode('@', $email);
                $email_final = $email_parts[0] . '+beneficiario' . $i . '_' . $counter . '@' . $email_parts[1];
                $counter++;
            }

            // Verificar si el username existe
            $username_final = $username;
            $counter = 1;
            while ( username_exists( $username_final ) ) {
                $username_final = $username . '_' . $counter;
                $counter++;
            }

            $dni = rand(20000000, 45000000);

            $user_id = wp_create_user( $username_final, 'Beneficiario123!', $email_final );

            if ( ! is_wp_error( $user_id ) ) {
                $institution_id = $institutions[array_rand($institutions)];

                // Set user role
                $user = new WP_User( $user_id );
                $user->set_role( 'customer' ); // WooCommerce customer role

                // Update user meta
                wp_update_user([
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $first_name . ' ' . $last_name,
                ]);

                update_user_meta( $user_id, self::$seeded_meta_key, true );
                update_user_meta( $user_id, '_wpcw_dni', $dni );
                update_user_meta( $user_id, '_wpcw_phone', $phone );
                update_user_meta( $user_id, '_wpcw_whatsapp', $phone );
                update_user_meta( $user_id, '_wpcw_institution_id', $institution_id );
                update_user_meta( $user_id, '_wpcw_fecha_nacimiento', date('Y-m-d', strtotime('-' . rand(20, 60) . ' years')) );
                update_user_meta( $user_id, 'billing_phone', $phone );
                update_user_meta( $user_id, 'billing_email', $email_final );
                update_user_meta( $user_id, 'billing_first_name', $first_name );
                update_user_meta( $user_id, 'billing_last_name', $last_name );

                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed business owners (dueños de comercio - rol: wpcw_business_owner)
     */
    private static function seed_business_owners( $count ) {
        $businesses = get_posts(['post_type' => 'wpcw_business', 'posts_per_page' => -1, 'fields' => 'ids']);

        if ( empty($businesses) ) {
            return 0;
        }

        $first_names = ['Roberto', 'María', 'Carlos', 'Ana', 'Jorge', 'Laura', 'Diego', 'Silvia'];
        $last_names = ['Gómez', 'Fernández', 'López', 'Martínez', 'Silva', 'Rodríguez', 'Torres', 'Castro'];

        $created = 0;
        for ( $i = 0; $i < min($count, count($businesses)); $i++ ) {
            $first_name = $first_names[$i % count($first_names)];
            $last_name = $last_names[$i % count($last_names)];
            $username = 'dueno_comercio_' . strtolower($first_name) . '_' . $i;
            $email = self::$test_emails[$i % count(self::$test_emails)];
            $phone = self::$test_phones[$i % count(self::$test_phones)];

            // Agregar sufijo si el email ya existe
            $email_final = $email;
            $counter = 1;
            while ( email_exists( $email_final ) ) {
                $email_parts = explode('@', $email);
                $email_final = $email_parts[0] . '+dueno' . $i . '_' . $counter . '@' . $email_parts[1];
                $counter++;
            }

            // Verificar username
            $username_final = $username;
            $counter = 1;
            while ( username_exists( $username_final ) ) {
                $username_final = $username . '_' . $counter;
                $counter++;
            }

            $user_id = wp_create_user( $username_final, 'DuenoComercio123!', $email_final );

            if ( ! is_wp_error( $user_id ) ) {
                $business_id = $businesses[$i % count($businesses)];
                $business_name = get_the_title($business_id);

                // Set user role
                $user = new WP_User( $user_id );
                $user->set_role( 'wpcw_business_owner' );

                wp_update_user([
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $first_name . ' ' . $last_name . ' (Dueño - ' . $business_name . ')',
                ]);

                update_user_meta( $user_id, self::$seeded_meta_key, true );
                update_user_meta( $user_id, '_wpcw_business_id', $business_id );
                update_user_meta( $user_id, '_wpcw_phone', $phone );
                update_user_meta( $user_id, '_wpcw_user_type', 'business_owner' );
                update_user_meta( $user_id, 'billing_phone', $phone );
                update_user_meta( $user_id, 'billing_email', $email_final );

                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed business staff/employees (vendedores/empleados - rol: wpcw_employee)
     * Estos son los que validan canjes en el punto de venta
     */
    private static function seed_business_staff( $count ) {
        $businesses = get_posts(['post_type' => 'wpcw_business', 'posts_per_page' => -1, 'fields' => 'ids']);

        if ( empty($businesses) ) {
            return 0;
        }

        $first_names = ['Juan', 'Sofía', 'Lucas', 'Valentina', 'Mateo', 'Emma', 'Santiago', 'Isabella', 'Nicolás', 'Mía', 'Franco', 'Camila', 'Benjamín', 'Martina', 'Tomás'];
        $last_names = ['Pérez', 'González', 'Díaz', 'Ramírez', 'Flores', 'Castro', 'Morales', 'Jiménez', 'Rojas', 'Herrera', 'Mendoza', 'Vargas', 'Sánchez', 'Romero', 'Acosta'];
        $positions = ['Vendedor', 'Cajero', 'Encargado de Turno', 'Asistente de Ventas', 'Supervisor'];

        $created = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $first_name = $first_names[$i % count($first_names)];
            $last_name = $last_names[$i % count($last_names)];
            $position = $positions[$i % count($positions)];
            $username = 'vendedor_' . strtolower($first_name) . '_' . $i;
            $email = self::$test_emails[$i % count(self::$test_emails)];
            $phone = self::$test_phones[$i % count(self::$test_phones)];

            // Agregar sufijo si el email ya existe
            $email_final = $email;
            $counter = 1;
            while ( email_exists( $email_final ) ) {
                $email_parts = explode('@', $email);
                $email_final = $email_parts[0] . '+vendedor' . $i . '_' . $counter . '@' . $email_parts[1];
                $counter++;
            }

            // Verificar username
            $username_final = $username;
            $counter = 1;
            while ( username_exists( $username_final ) ) {
                $username_final = $username . '_' . $counter;
                $counter++;
            }

            $user_id = wp_create_user( $username_final, 'Vendedor123!', $email_final );

            if ( ! is_wp_error( $user_id ) ) {
                $business_id = $businesses[array_rand($businesses)];
                $business_name = get_the_title($business_id);

                // Set user role - wpcw_employee tiene permiso para validar canjes
                $user = new WP_User( $user_id );
                $user->set_role( 'wpcw_employee' );

                wp_update_user([
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $first_name . ' ' . $last_name . ' (' . $position . ')',
                ]);

                update_user_meta( $user_id, self::$seeded_meta_key, true );
                update_user_meta( $user_id, '_wpcw_business_id', $business_id );
                update_user_meta( $user_id, '_wpcw_phone', $phone );
                update_user_meta( $user_id, '_wpcw_user_type', 'business_staff' );
                update_user_meta( $user_id, '_wpcw_position', $position );
                update_user_meta( $user_id, 'billing_phone', $phone );
                update_user_meta( $user_id, 'billing_email', $email_final );

                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed institution users (admin staff)
     */
    private static function seed_institution_users( $count ) {
        $institutions = get_posts(['post_type' => 'wpcw_institution', 'posts_per_page' => -1, 'fields' => 'ids']);

        if ( empty($institutions) ) {
            return 0;
        }

        $created = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $username = 'institucion_admin_' . $i;
            $email = self::$test_emails[$i % count(self::$test_emails)];
            $phone = self::$test_phones[$i % count(self::$test_phones)];

            // Agregar sufijo si el email ya existe
            $email_final = $email;
            $counter = 1;
            while ( email_exists( $email_final ) ) {
                $email_parts = explode('@', $email);
                $email_final = $email_parts[0] . '+institucion' . $i . '_' . $counter . '@' . $email_parts[1];
                $counter++;
            }

            // Verificar username
            $username_final = $username;
            $counter = 1;
            while ( username_exists( $username_final ) ) {
                $username_final = $username . '_' . $counter;
                $counter++;
            }

            $user_id = wp_create_user( $username_final, 'Institucion123!', $email_final );

            if ( ! is_wp_error( $user_id ) ) {
                $institution_id = $institutions[array_rand($institutions)];
                $institution_name = get_the_title($institution_id);

                // Set user role
                $user = new WP_User( $user_id );
                $user->set_role( 'administrator' );

                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => 'Admin - ' . $institution_name,
                ]);

                update_user_meta( $user_id, self::$seeded_meta_key, true );
                update_user_meta( $user_id, '_wpcw_institution_id', $institution_id );
                update_user_meta( $user_id, '_wpcw_phone', $phone );
                update_user_meta( $user_id, '_wpcw_user_type', 'institution' );

                $created++;
            }
        }

        return $created;
    }

    /**
     * Seed coupon redemptions in the custom table with realistic temporal distribution
     */
    private static function seed_redemptions( $count ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_canjes';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) {
            return 0;
        }

        $coupons = get_posts(['post_type' => 'shop_coupon', 'posts_per_page' => -1, 'fields' => 'ids']);
        $beneficiaries = get_users(['meta_key' => self::$seeded_meta_key, 'fields' => 'ids']);
        $businesses = get_posts(['post_type' => 'wpcw_business', 'posts_per_page' => -1, 'fields' => 'ids']);

        if ( empty($coupons) || empty($beneficiaries) || empty($businesses) ) {
            return 0;
        }

        $statuses = ['pending', 'approved', 'rejected', 'used'];
        $created = 0;

        // Distribución temporal realista para estadísticas
        // 16% últimos 7 días, 34% últimos 30 días, 66% últimos 60 días, 100% últimos 90 días
        $temporal_distribution = [];
        for ( $i = 0; $i < $count; $i++ ) {
            if ( $i < $count * 0.16 ) {
                // Últimos 7 días
                $temporal_distribution[] = rand(1, 7);
            } elseif ( $i < $count * 0.34 ) {
                // Entre 8 y 30 días
                $temporal_distribution[] = rand(8, 30);
            } elseif ( $i < $count * 0.66 ) {
                // Entre 31 y 60 días
                $temporal_distribution[] = rand(31, 60);
            } else {
                // Entre 61 y 90 días
                $temporal_distribution[] = rand(61, 90);
            }
        }
        shuffle($temporal_distribution);

        for ( $i = 0; $i < $count; $i++ ) {
            $coupon_id = $coupons[array_rand($coupons)];
            $user_id = $beneficiaries[array_rand($beneficiaries)];
            $business_id = $businesses[array_rand($businesses)];
            $status = $statuses[array_rand($statuses)];

            $coupon_code = get_the_title($coupon_id);
            $codigo_validacion = 'VAL' . strtoupper(wp_generate_password(8, false, false));
            $numero_canje = date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $token_confirmacion = wp_generate_password(32, false, false);

            // Usar distribución temporal pre-calculada
            $days_ago = $temporal_distribution[$i];
            $hours = rand(8, 20); // Horario comercial 8am - 8pm
            $minutes = rand(0, 59);
            $fecha_canje = date('Y-m-d H:i:s', strtotime("-{$days_ago} days {$hours}:{$minutes}:00"));

            // Check which columns exist in the table
            $columns = $wpdb->get_col( "DESCRIBE {$table_name}", 0 );

            $data = [
                'user_id' => $user_id,
                'estado_canje' => $status,
                'comercio_id' => $business_id,
                'origen_canje' => 'seeder',
                'notas_internas' => '[SEEDED] Datos de ejemplo para testing',
            ];

            $format = ['%d', '%s', '%d', '%s', '%s'];

            // Add columns conditionally based on what exists
            if ( in_array('cupon_id', $columns) || in_array('coupon_id', $columns) ) {
                $data['coupon_id'] = $coupon_id;
                $format[] = '%d';
            }

            if ( in_array('codigo_cupon', $columns) || in_array('codigo_cupon_wc', $columns) ) {
                $data['codigo_cupon_wc'] = $coupon_code;
                $format[] = '%s';
            }

            if ( in_array('fecha_canje', $columns) || in_array('fecha_solicitud_canje', $columns) ) {
                $data['fecha_solicitud_canje'] = $fecha_canje;
                $format[] = '%s';
            }

            if ( in_array('codigo_validacion', $columns) ) {
                $data['codigo_validacion'] = $codigo_validacion;
                $format[] = '%s';
            }

            if ( in_array('numero_canje', $columns) ) {
                $data['numero_canje'] = $numero_canje;
                $format[] = '%s';
            }

            if ( in_array('token_confirmacion', $columns) ) {
                $data['token_confirmacion'] = $token_confirmacion;
                $format[] = '%s';
            }

            if ( in_array('ip_address', $columns) ) {
                $data['ip_address'] = '127.0.0.1';
                $format[] = '%s';
            }

            if ( in_array('user_agent', $columns) ) {
                $data['user_agent'] = 'Test Seeder';
                $format[] = '%s';
            }

            $inserted = $wpdb->insert( $table_name, $data, $format );

            if ( $inserted ) {
                $created++;
            }
        }

        return $created;
    }
}
