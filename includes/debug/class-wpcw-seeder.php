<?php
/**
 * WPCW - Seeder Class for dummy data generation.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WPCW_Seeder {

    private static $seeded_meta_key = '_wpcw_is_seeded';

    /**
     * Master function to seed all data.
     */
    public static function seed_all() {
        self::seed_institutions( 5 );
        self::seed_businesses( 20 );
        self::seed_convenios( 15 );
        self::seed_coupons( 50 );
        self::seed_beneficiaries( 100 );
        return ['message' => 'Ecosistema de datos de ejemplo generado con éxito.'];
    }

    /**
     * Master function to clear all seeded data.
     */
    public static function clear_all() {
        global $wpdb;

        // Delete Posts (CPTs)
        $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", self::$seeded_meta_key ) );
        foreach ( $post_ids as $post_id ) {
            wp_delete_post( $post_id, true );
        }

        // Delete Users
        $user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s", self::$seeded_meta_key ) );
        require_once( ABSPATH . 'wp-admin/includes/user.php' );
        foreach ( $user_ids as $user_id ) {
            wp_delete_user( $user_id );
        }

        return ['message' => 'Todos los datos de ejemplo han sido eliminados.'];
    }

    private static function seed_institutions( $count ) {
        for ( $i = 0; $i < $count; $i++ ) {
            $post_id = wp_insert_post( [
                'post_title' => 'Institución de Ejemplo ' . ( $i + 1 ),
                'post_type' => 'wpcw_institution',
                'post_status' => 'publish',
            ] );
            if ( $post_id ) {
                update_post_meta( $post_id, self::$seeded_meta_key, true );
                update_post_meta( $post_id, '_institution_email', 'institution' . $i . '@example.com' );
            }
        }
    }

    private static function seed_businesses( $count ) {
        for ( $i = 0; $i < $count; $i++ ) {
            $post_id = wp_insert_post( [
                'post_title' => 'Negocio de Ejemplo ' . ( $i + 1 ),
                'post_type' => 'wpcw_business',
                'post_status' => 'publish',
            ] );
            if ( $post_id ) {
                update_post_meta( $post_id, self::$seeded_meta_key, true );
            }
        }
    }

    private static function seed_convenios( $count ) {
        $institutions = get_posts(['post_type' => 'wpcw_institution', 'posts_per_page' => -1, 'fields' => 'ids']);
        $businesses = get_posts(['post_type' => 'wpcw_business', 'posts_per_page' => -1, 'fields' => 'ids']);
        if(empty($institutions) || empty($businesses)) return;

        for ( $i = 0; $i < $count; $i++ ) {
            $provider = $businesses[array_rand($businesses)];
            $recipient = $institutions[array_rand($institutions)];
            $post_id = wp_insert_post( [
                'post_title' => 'Convenio entre ' . get_the_title($provider) . ' y ' . get_the_title($recipient),
                'post_type' => 'wpcw_convenio',
                'post_status' => 'publish', // active
            ] );
            if ( $post_id ) {
                update_post_meta( $post_id, self::$seeded_meta_key, true );
                update_post_meta( $post_id, '_convenio_provider_id', $provider );
                update_post_meta( $post_id, '_convenio_recipient_id', $recipient );
                update_post_meta( $post_id, '_convenio_status', 'active' );
            }
        }
    }

    private static function seed_coupons( $count ) {
        $convenios = get_posts(['post_type' => 'wpcw_convenio', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids']);
        if(empty($convenios)) return;

        for ( $i = 0; $i < $count; $i++ ) {
            $code = 'EJEMPLO' . wp_generate_password( 8, false );
            $post_id = wp_insert_post( [
                'post_title' => $code,
                'post_type' => 'shop_coupon',
                'post_status' => 'publish',
            ] );
            if ( $post_id ) {
                $convenio_id = $convenios[array_rand($convenios)];
                update_post_meta( $post_id, self::$seeded_meta_key, true );
                update_post_meta( $post_id, 'discount_type', 'percent' );
                update_post_meta( $post_id, 'coupon_amount', rand(10, 50) );
                update_post_meta( $post_id, '_wpcw_associated_convenio_id', $convenio_id );
            }
        }
    }

    private static function seed_beneficiaries( $count ) {
        $institutions = get_posts(['post_type' => 'wpcw_institution', 'posts_per_page' => -1, 'fields' => 'ids']);
        if(empty($institutions)) return;

        for ( $i = 0; $i < $count; $i++ ) {
            $email = 'beneficiario' . $i . '@example.com';
            $dni = rand(10000000, 40000000);
            if ( ! email_exists( $email ) ) {
                $user_id = wp_create_user( $email, 'password', $email );
                if ( ! is_wp_error( $user_id ) ) {
                    $institution_id = $institutions[array_rand($institutions)];
                    update_user_meta( $user_id, self::$seeded_meta_key, true );
                    update_user_meta( $user_id, '_wpcw_dni', $dni );
                    update_user_meta( $user_id, '_wpcw_institution_id', $institution_id );
                }
            }
        }
    }
}
