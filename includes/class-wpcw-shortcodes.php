<?php
/**
 * WP Cupón WhatsApp - Shortcodes Class
 *
 * Handles frontend shortcodes for the plugin
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Shortcodes class
 */
class WPCW_Shortcodes {

    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode( 'wpcw_solicitud_adhesion_form', array( __CLASS__, 'adhesion_form_shortcode' ) );
        add_shortcode( 'wpcw_mis_cupones', array( __CLASS__, 'my_coupons_shortcode' ) );
        add_shortcode( 'wpcw_cupones_publicos', array( __CLASS__, 'public_coupons_shortcode' ) );
        add_shortcode( 'wpcw_canje_cupon', array( __CLASS__, 'coupon_redemption_shortcode' ) );
        add_shortcode( 'wpcw_mis_canjes', array( __CLASS__, 'my_redemptions_shortcode' ) );
        add_shortcode( 'wpcw_dashboard_usuario', array( __CLASS__, 'user_dashboard_shortcode' ) );
        add_shortcode( 'wpcw_registro_beneficiario', array( __CLASS__, 'render_beneficiary_registration_form' ) );
        add_shortcode( 'wpcw_portal_beneficiario', array( __CLASS__, 'render_beneficiary_portal' ) );

        // Enqueue frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
    }

    /**
     * Enqueue frontend assets
     */
    public static function enqueue_frontend_assets() {
        wp_enqueue_style(
            'wpcw-frontend',
            plugins_url( 'public/css/frontend.css', dirname( __FILE__ ) ),
            array(),
            WPCW_VERSION
        );

        wp_enqueue_script(
            'wpcw-frontend',
            plugins_url( 'public/js/frontend.js', dirname( __FILE__ ) ),
            array( 'jquery' ),
            WPCW_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'wpcw-frontend', 'wpcw_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wpcw_frontend_nonce' ),
            'strings' => array(
                'confirm_redemption' => __( '¿Estás seguro de que quieres canjear este cupón?', 'wp-cupon-whatsapp' ),
                'redemption_success' => __( '¡Canje solicitado exitosamente!', 'wp-cupon-whatsapp' ),
                'redemption_error' => __( 'Error al procesar el canje', 'wp-cupon-whatsapp' ),
                'loading' => __( 'Cargando...', 'wp-cupon-whatsapp' ),
            ),
        ) );
    }

    /**
     * Business adhesion form shortcode
     */
    public static function adhesion_form_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="wpcw-message wpcw-error">' . __( 'Debes iniciar sesión para enviar una solicitud de adhesión.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        $atts = shortcode_atts( array(
            'show_title' => 'true',
            'redirect_url' => '',
        ), $atts );

        ob_start();

        if ( isset( $_POST['wpcw_submit_adhesion'] ) && wp_verify_nonce( $_POST['wpcw_adhesion_nonce'], 'wpcw_adhesion_form' ) ) {
            $result = self::process_adhesion_form();
            if ( $result['success'] ) {
                echo '<div class="wpcw-message wpcw-success">' . esc_html( $result['message'] ) . '</div>';
                if ( ! empty( $atts['redirect_url'] ) ) {
                    echo '<script>window.location.href="' . esc_url( $atts['redirect_url'] ) . '";</script>';
                }
            } else {
                echo '<div class="wpcw-message wpcw-error">' . esc_html( $result['message'] ) . '</div>';
            }
        }

        if ( $atts['show_title'] === 'true' ) {
            echo '<h2>' . __( 'Solicitud de Adhesión al Programa WP Cupón WhatsApp', 'wp-cupon-whatsapp' ) . '</h2>';
        }

        ?>
        <form method="post" class="wpcw-adhesion-form" enctype="multipart/form-data">
            <?php wp_nonce_field( 'wpcw_adhesion_form', 'wpcw_adhesion_nonce' ); ?>

            <div class="wpcw-form-section">
                <h3><?php _e( 'Tipo de Solicitante', 'wp-cupon-whatsapp' ); ?></h3>
                <div class="wpcw-form-row">
                    <label>
                        <input type="radio" name="applicant_type" value="comercio" required>
                        <?php _e( 'Comercio', 'wp-cupon-whatsapp' ); ?>
                    </label>
                    <label>
                        <input type="radio" name="applicant_type" value="institucion">
                        <?php _e( 'Institución', 'wp-cupon-whatsapp' ); ?>
                    </label>
                </div>
            </div>

            <div class="wpcw-form-section">
                <h3><?php _e( 'Información del Negocio', 'wp-cupon-whatsapp' ); ?></h3>

                <div class="wpcw-form-row">
                    <label for="fantasy_name"><?php _e( 'Nombre de Fantasía *', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="text" id="fantasy_name" name="fantasy_name" required>
                </div>

                <div class="wpcw-form-row">
                    <label for="legal_name"><?php _e( 'Razón Social *', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="text" id="legal_name" name="legal_name" required>
                </div>

                <div class="wpcw-form-row">
                    <label for="cuit"><?php _e( 'CUIT *', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="text" id="cuit" name="cuit" required pattern="[0-9]{10,11}" title="CUIT debe tener 10 u 11 dígitos">
                </div>

                <div class="wpcw-form-row">
                    <label for="description"><?php _e( 'Descripción del Negocio', 'wp-cupon-whatsapp' ); ?></label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
            </div>

            <div class="wpcw-form-section">
                <h3><?php _e( 'Información de Contacto', 'wp-cupon-whatsapp' ); ?></h3>

                <div class="wpcw-form-row">
                    <label for="contact_person"><?php _e( 'Persona de Contacto *', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="text" id="contact_person" name="contact_person" required>
                </div>

                <div class="wpcw-form-row">
                    <label for="email"><?php _e( 'Email de Contacto *', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="wpcw-form-row">
                    <label for="whatsapp"><?php _e( 'Número de WhatsApp *', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="text" id="whatsapp" name="whatsapp" required pattern="[0-9\s\+\(\)-]+" title="Número de WhatsApp válido">
                </div>

                <div class="wpcw-form-row">
                    <label for="address_main"><?php _e( 'Dirección Principal', 'wp-cupon-whatsapp' ); ?></label>
                    <textarea id="address_main" name="address_main" rows="3"></textarea>
                </div>
            </div>

            <div class="wpcw-form-section">
                <div class="wpcw-form-row">
                    <button type="submit" name="wpcw_submit_adhesion" class="wpcw-submit-btn">
                        <?php _e( 'Enviar Solicitud', 'wp-cupon-whatsapp' ); ?>
                    </button>
                </div>
            </div>
        </form>

        <style>
        .wpcw-adhesion-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .wpcw-form-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            background: #fafafa;
        }

        .wpcw-form-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #23282d;
        }

        .wpcw-form-row {
            margin-bottom: 15px;
        }

        .wpcw-form-row label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .wpcw-form-row input[type="text"],
        .wpcw-form-row input[type="email"],
        .wpcw-form-row textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .wpcw-form-row input[type="radio"] {
            margin-right: 8px;
        }

        .wpcw-submit-btn {
            background: #007cba;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .wpcw-submit-btn:hover {
            background: #005a87;
        }

        .wpcw-message {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .wpcw-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .wpcw-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        </style>
        <?php

        return ob_get_clean();
    }

    /**
     * My coupons shortcode
     */
    public static function my_coupons_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="wpcw-message wpcw-error">' . __( 'Debes iniciar sesión para ver tus cupones.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        $atts = shortcode_atts( array(
            'show_expired' => 'false',
            'limit' => 10,
        ), $atts );

        $user_id = get_current_user_id();
        $user_institution = get_user_meta( $user_id, '_wpcw_user_institution_id', true );

        if ( ! $user_institution ) {
            return '<div class="wpcw-message wpcw-info">' . __( 'No estás asociado a ninguna institución.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        // Get loyalty coupons for user's institution
        $args = array(
            'post_type' => 'shop_coupon',
            'posts_per_page' => intval( $atts['limit'] ),
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_wpcw_enabled',
                    'value' => 'yes',
                    'compare' => '=',
                ),
                array(
                    'key' => '_wpcw_is_loyalty_coupon',
                    'value' => 'yes',
                    'compare' => '=',
                ),
                array(
                    'key' => '_wpcw_associated_business_id',
                    'value' => $user_institution,
                    'compare' => '=',
                ),
            ),
        );

        if ( $atts['show_expired'] !== 'true' ) {
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => 'date_expires',
                    'value' => current_time( 'Y-m-d' ),
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
                array(
                    'key' => 'date_expires',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        $coupons_query = new WP_Query( $args );

        ob_start();

        if ( $coupons_query->have_posts() ) {
            echo '<div class="wpcw-coupons-grid">';

            while ( $coupons_query->have_posts() ) {
                $coupons_query->the_post();
                $coupon_id = get_the_ID();
                $coupon = new WC_Coupon( $coupon_id );

                self::render_coupon_card( $coupon, true );
            }

            echo '</div>';
        } else {
            echo '<div class="wpcw-no-coupons">' . __( 'No tienes cupones disponibles en este momento.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Public coupons shortcode
     */
    public static function public_coupons_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'limit' => 12,
            'show_filters' => 'true',
        ), $atts );

        $args = array(
            'post_type' => 'shop_coupon',
            'posts_per_page' => intval( $atts['limit'] ),
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_wpcw_enabled',
                    'value' => 'yes',
                    'compare' => '=',
                ),
                array(
                    'key' => '_wpcw_is_public_coupon',
                    'value' => 'yes',
                    'compare' => '=',
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'date_expires',
                        'value' => current_time( 'Y-m-d' ),
                        'compare' => '>=',
                        'type' => 'DATE',
                    ),
                    array(
                        'key' => 'date_expires',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            ),
        );

        $coupons_query = new WP_Query( $args );

        ob_start();

        if ( $atts['show_filters'] === 'true' ) {
            echo '<div class="wpcw-coupons-filters">';
            echo '<input type="text" id="wpcw-coupon-search" placeholder="' . __( 'Buscar cupones...', 'wp-cupon-whatsapp' ) . '">';
            echo '<select id="wpcw-coupon-sort">';
            echo '<option value="date_desc">' . __( 'Más recientes', 'wp-cupon-whatsapp' ) . '</option>';
            echo '<option value="date_asc">' . __( 'Más antiguos', 'wp-cupon-whatsapp' ) . '</option>';
            echo '<option value="title_asc">' . __( 'Nombre A-Z', 'wp-cupon-whatsapp' ) . '</option>';
            echo '<option value="title_desc">' . __( 'Nombre Z-A', 'wp-cupon-whatsapp' ) . '</option>';
            echo '</select>';
            echo '</div>';
        }

        if ( $coupons_query->have_posts() ) {
            echo '<div class="wpcw-coupons-grid" id="wpcw-coupons-container">';

            while ( $coupons_query->have_posts() ) {
                $coupons_query->the_post();
                $coupon_id = get_the_ID();
                $coupon = new WC_Coupon( $coupon_id );

                self::render_coupon_card( $coupon, false );
            }

            echo '</div>';
        } else {
            echo '<div class="wpcw-no-coupons">' . __( 'No hay cupones públicos disponibles en este momento.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Coupon redemption shortcode
     */
    public static function coupon_redemption_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="wpcw-message wpcw-error">' . __( 'Debes iniciar sesión para canjear cupones.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        $atts = shortcode_atts( array(
            'coupon_id' => 0,
        ), $atts );

        $user_id = get_current_user_id();

        // Check if user has WhatsApp configured
        $whatsapp = get_user_meta( $user_id, '_wpcw_whatsapp_number', true );
        if ( empty( $whatsapp ) ) {
            return '<div class="wpcw-message wpcw-warning">' .
                   __( 'Necesitas configurar tu número de WhatsApp para poder canjear cupones.', 'wp-cupon-whatsapp' ) .
                   ' <a href="' . wc_get_account_endpoint_url( 'edit-account' ) . '">' . __( 'Configurar WhatsApp', 'wp-cupon-whatsapp' ) . '</a>' .
                   '</div>';
        }

        ob_start();

        if ( $atts['coupon_id'] > 0 ) {
            // Single coupon redemption
            $coupon = get_post( $atts['coupon_id'] );
            if ( $coupon && $coupon->post_type === 'shop_coupon' ) {
                $wc_coupon = new WC_Coupon( $atts['coupon_id'] );
                self::render_single_coupon_redemption( $wc_coupon );
            } else {
                echo '<div class="wpcw-message wpcw-error">' . __( 'Cupón no encontrado.', 'wp-cupon-whatsapp' ) . '</div>';
            }
        } else {
            // Multiple coupons selection
            echo '<div id="wpcw-redemption-interface">';
            echo '<div id="wpcw-coupon-selector">';
            echo self::public_coupons_shortcode( array( 'limit' => 20 ) );
            echo '</div>';
            echo '<div id="wpcw-redemption-details" style="display: none;"></div>';
            echo '</div>';
        }

        return ob_get_clean();
    }

    /**
     * My redemptions shortcode
     */
    public static function my_redemptions_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="wpcw-message wpcw-error">' . __( 'Debes iniciar sesión para ver tus canjes.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        $atts = shortcode_atts( array(
            'limit' => 10,
        ), $atts );

        $user_id = get_current_user_id();

        global $wpdb;
        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) != $redemptions_table ) {
            return '<div class="wpcw-message wpcw-info">' . __( 'No hay registros de canjes disponibles.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        $redemptions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, p.post_title as coupon_title, b.post_title as business_name
                FROM $redemptions_table r
                LEFT JOIN {$wpdb->posts} p ON r.cupon_id = p.ID
                LEFT JOIN {$wpdb->posts} b ON r.comercio_id = b.ID
                WHERE r.cliente_id = %d
                ORDER BY r.fecha_solicitud_canje DESC
                LIMIT %d",
                $user_id, intval( $atts['limit'] )
            )
        );

        ob_start();

        if ( ! empty( $redemptions ) ) {
            echo '<div class="wpcw-redemptions-list">';

            foreach ( $redemptions as $redemption ) {
                $status_class = 'wpcw-status-' . sanitize_html_class( $redemption->estado_canje );
                $status_label = self::get_redemption_status_label( $redemption->estado_canje );

                echo '<div class="wpcw-redemption-item">';
                echo '<div class="wpcw-redemption-header">';
                echo '<h4>' . esc_html( $redemption->coupon_title ?: 'Cupón desconocido' ) . '</h4>';
                echo '<span class="wpcw-redemption-status ' . $status_class . '">' . esc_html( $status_label ) . '</span>';
                echo '</div>';

                echo '<div class="wpcw-redemption-details">';
                echo '<p><strong>' . __( 'Comercio:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $redemption->business_name ?: 'Comercio desconocido' ) . '</p>';
                echo '<p><strong>' . __( 'Fecha de Solicitud:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $redemption->fecha_solicitud_canje ) ) ) . '</p>';

                if ( $redemption->fecha_confirmacion_canje ) {
                    echo '<p><strong>' . __( 'Fecha de Confirmación:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $redemption->fecha_confirmacion_canje ) ) ) . '</p>';
                }

                if ( $redemption->codigo_cupon_wc ) {
                    echo '<p><strong>' . __( 'Código WC:', 'wp-cupon-whatsapp' ) . '</strong> <code>' . esc_html( $redemption->codigo_cupon_wc ) . '</code></p>';
                }

                if ( $redemption->notas_internas ) {
                    echo '<p><strong>' . __( 'Notas:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $redemption->notas_internas ) . '</p>';
                }

                echo '</div>';
                echo '</div>';
            }

            echo '</div>';
        } else {
            echo '<div class="wpcw-no-redemptions">' . __( 'No tienes canjes registrados.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        return ob_get_clean();
    }

    /**
     * User dashboard shortcode
     */
    public static function user_dashboard_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="wpcw-message wpcw-error">' . __( 'Debes iniciar sesión para acceder al dashboard.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        $user_id = get_current_user_id();
        $user_institution = get_user_meta( $user_id, '_wpcw_user_institution_id', true );

        ob_start();

        echo '<div class="wpcw-user-dashboard">';

        // User info section
        echo '<div class="wpcw-dashboard-section wpcw-user-info">';
        echo '<h3>' . __( 'Mi Información', 'wp-cupon-whatsapp' ) . '</h3>';

        $current_user = wp_get_current_user();
        echo '<p><strong>' . __( 'Nombre:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $current_user->display_name ) . '</p>';
        echo '<p><strong>' . __( 'Email:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $current_user->user_email ) . '</p>';

        $whatsapp = get_user_meta( $user_id, '_wpcw_whatsapp_number', true );
        if ( $whatsapp ) {
            echo '<p><strong>' . __( 'WhatsApp:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $whatsapp ) . '</p>';
        } else {
            echo '<p class="wpcw-warning"><strong>' . __( 'WhatsApp:', 'wp-cupon-whatsapp' ) . '</strong> ' . __( 'No configurado', 'wp-cupon-whatsapp' ) . ' - <a href="' . wc_get_account_endpoint_url( 'edit-account' ) . '">' . __( 'Configurar', 'wp-cupon-whatsapp' ) . '</a></p>';
        }

        if ( $user_institution ) {
            $institution = get_post( $user_institution );
            if ( $institution ) {
                echo '<p><strong>' . __( 'Institución:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $institution->post_title ) . '</p>';
            }
        }

        echo '</div>';

        // Quick actions
        echo '<div class="wpcw-dashboard-section wpcw-quick-actions">';
        echo '<h3>' . __( 'Acciones Rápidas', 'wp-cupon-whatsapp' ) . '</h3>';
        echo '<div class="wpcw-action-buttons">';

        if ( $user_institution ) {
            echo '<a href="#mis-cupones" class="wpcw-action-btn">' . __( 'Ver Mis Cupones', 'wp-cupon-whatsapp' ) . '</a>';
        }

        echo '<a href="#cupones-publicos" class="wpcw-action-btn">' . __( 'Cupones Públicos', 'wp-cupon-whatsapp' ) . '</a>';
        echo '<a href="#canjear-cupones" class="wpcw-action-btn">' . __( 'Canjear Cupones', 'wp-cupon-whatsapp' ) . '</a>';
        echo '<a href="#mis-canjes" class="wpcw-action-btn">' . __( 'Mis Canjes', 'wp-cupon-whatsapp' ) . '</a>';

        echo '</div>';
        echo '</div>';

        // My coupons section
        if ( $user_institution ) {
            echo '<div id="mis-cupones" class="wpcw-dashboard-section">';
            echo '<h3>' . __( 'Mis Cupones de Fidelización', 'wp-cupon-whatsapp' ) . '</h3>';
            echo self::my_coupons_shortcode( array( 'limit' => 6 ) );
            echo '</div>';
        }

        // Public coupons section
        echo '<div id="cupones-publicos" class="wpcw-dashboard-section">';
        echo '<h3>' . __( 'Cupones Públicos Disponibles', 'wp-cupon-whatsapp' ) . '</h3>';
        echo self::public_coupons_shortcode( array( 'limit' => 6 ) );
        echo '</div>';

        // Redemption section
        echo '<div id="canjear-cupones" class="wpcw-dashboard-section">';
        echo '<h3>' . __( 'Canjear Cupones', 'wp-cupon-whatsapp' ) . '</h3>';
        echo self::coupon_redemption_shortcode( array() );
        echo '</div>';

        // My redemptions section
        echo '<div id="mis-canjes" class="wpcw-dashboard-section">';
        echo '<h3>' . __( 'Historial de Canjes', 'wp-cupon-whatsapp' ) . '</h3>';
        echo self::my_redemptions_shortcode( array( 'limit' => 5 ) );
        echo '</div>';

        echo '</div>';

        return ob_get_clean();
    }

    /**
     * Beneficiary Registration Form Shortcode
     */
    public static function render_beneficiary_registration_form( $atts ) {
        ob_start();
        $errors = [];

        // --- Logic by El Artesano ---
        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['action'] ) && $_POST['action'] === 'wpcw_register_beneficiary' ) {
            // --- Security by El Guardián ---
            if ( ! isset( $_POST['wpcw_beneficiary_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_beneficiary_nonce'], 'wpcw_beneficiary_registration' ) ) {
                $errors[] = 'Error de seguridad. Por favor, recarga la página y vuelve a intentarlo.';
            } else {
                // --- Data Validation ---
                $email = isset( $_POST['wpcw_email'] ) ? sanitize_email( $_POST['wpcw_email'] ) : '';
                $institution_id = isset( $_POST['wpcw_institution_id'] ) ? absint( $_POST['wpcw_institution_id'] ) : 0;
                $password = isset( $_POST['wpcw_password'] ) ? $_POST['wpcw_password'] : '';

                if ( empty( $email ) || ! is_email( $email ) ) { $errors[] = 'Por favor, introduce un email válido.'; }
                if ( empty( $password ) ) { $errors[] = 'La contraseña no puede estar vacía.'; }
                if ( $institution_id === 0 ) { $errors[] = 'Debes seleccionar una institución.'; }
                if ( email_exists( $email ) ) { $errors[] = 'Este email ya está registrado. Por favor, inicia sesión.'; }

                if ( empty( $errors ) ) {
                    // --- Core Validation Logic ---
                    $valid_members = get_post_meta( $institution_id, '_wpcw_valid_member_emails', true );
                    if ( is_array( $valid_members ) && in_array( $email, $valid_members ) ) {
                        // Success! Create user.
                        $user_id = wp_create_user( $email, $password, $email );
                        if ( ! is_wp_error( $user_id ) ) {
                            update_user_meta( $user_id, '_wpcw_institution_id', $institution_id );
                            // TODO: Log user in and redirect.
                            echo '<div class="wpcw-message wpcw-success">¡Registro completado! Ahora puedes iniciar sesión.</div>';
                        } else {
                            $errors[] = 'Error al crear el usuario: ' . $user_id->get_error_message();
                        }
                    } else {
                        $errors[] = 'Tu email no se encuentra en la lista de miembros válidos para la institución seleccionada.';
                    }
                }
            }
        }

        // --- UI by La Diseñadora ---
        if ( ! empty( $errors ) ) {
            echo '<div class="wpcw-message wpcw-error"><ul>';
            foreach ( $errors as $error ) {
                echo '<li>' . esc_html( $error ) . '</li>';
            }
            echo '</ul></div>';
        }
        ?>
        <form method="post" class="wpcw-registration-form">
            <input type="hidden" name="action" value="wpcw_register_beneficiary">
            <?php wp_nonce_field( 'wpcw_beneficiary_registration', 'wpcw_beneficiary_nonce' ); ?>

            <p>
                <label for="wpcw_email">Email *</label>
                <input type="email" id="wpcw_email" name="wpcw_email" required>
            </p>
            <p>
                <label for="wpcw_password">Contraseña *</label>
                <input type="password" id="wpcw_password" name="wpcw_password" required>
            </p>
            <p>
                <label for="wpcw_institution_id">Institución *</label>
                <select name="wpcw_institution_id" id="wpcw_institution_id" required>
                    <option value="">-- Seleccionar --</option>
                    <?php
                    $institutions = WPCW_Institution_Manager::get_all_institutions();
                    foreach ( $institutions as $id => $name ) {
                        echo '<option value="' . esc_attr( $id ) . '">' . esc_html( $name ) . '</option>';
                    }
                    ?>
                </select>
            </p>
            <p>
                <button type="submit">Registrarse</button>
            </p>
        </form>
        <?php
        return ob_get_clean();
    }


    /**
     * Process adhesion form
     */
    private static function process_adhesion_form() {
        $errors = array();

        // Validate required fields
        $required_fields = array( 'applicant_type', 'fantasy_name', 'legal_name', 'cuit', 'contact_person', 'email', 'whatsapp' );

        foreach ( $required_fields as $field ) {
            if ( empty( $_POST[$field] ) ) {
                $errors[] = sprintf( __( 'El campo %s es obligatorio.', 'wp-cupon-whatsapp' ), $field );
            }
        }

        if ( ! empty( $errors ) ) {
            return array(
                'success' => false,
                'message' => implode( ' ', $errors ),
            );
        }

        // Create application via API
        $api_response = wp_remote_post( rest_url( 'wpcw/v1/applications' ), array(
            'body' => array(
                'applicant_type' => sanitize_text_field( $_POST['applicant_type'] ),
                'fantasy_name' => sanitize_text_field( $_POST['fantasy_name'] ),
                'legal_name' => sanitize_text_field( $_POST['legal_name'] ),
                'cuit' => sanitize_text_field( $_POST['cuit'] ),
                'contact_person' => sanitize_text_field( $_POST['contact_person'] ),
                'email' => sanitize_email( $_POST['email'] ),
                'whatsapp' => sanitize_text_field( $_POST['whatsapp'] ),
                'address_main' => isset( $_POST['address_main'] ) ? sanitize_textarea_field( $_POST['address_main'] ) : '',
                'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
            ),
        ) );

        if ( is_wp_error( $api_response ) ) {
            return array(
                'success' => false,
                'message' => __( 'Error al procesar la solicitud.', 'wp-cupon-whatsapp' ),
            );
        }

        $response_body = wp_remote_retrieve_body( $api_response );
        $response_data = json_decode( $response_body, true );

        if ( wp_remote_retrieve_response_code( $api_response ) === 201 ) {
            return array(
                'success' => true,
                'message' => __( 'Solicitud enviada exitosamente. Nos pondremos en contacto a la brevedad.', 'wp-cupon-whatsapp' ),
            );
        } else {
            return array(
                'success' => false,
                'message' => isset( $response_data['message'] ) ? $response_data['message'] : __( 'Error desconocido.', 'wp-cupon-whatsapp' ),
            );
        }
    }

    /**
     * Render coupon card
     */
    private static function render_coupon_card( $coupon, $show_redeem_button = true ) {
        $coupon_post = get_post( $coupon->get_id() );

        echo '<div class="wpcw-coupon-card" data-coupon-id="' . esc_attr( $coupon->get_id() ) . '">';
        echo '<div class="wpcw-coupon-header">';
        echo '<h4>' . esc_html( $coupon->get_code() ) . '</h4>';

        if ( $coupon->get_discount_type() === 'percent' ) {
            echo '<div class="wpcw-discount-badge">' . esc_html( $coupon->get_amount() ) . '% OFF</div>';
        } elseif ( $coupon->get_discount_type() === 'fixed_cart' ) {
            echo '<div class="wpcw-discount-badge">$' . esc_html( $coupon->get_amount() ) . ' OFF</div>';
        }

        echo '</div>';

        echo '<div class="wpcw-coupon-body">';

        if ( $coupon_post->post_content ) {
            echo '<p class="wpcw-coupon-description">' . esc_html( wp_trim_words( $coupon_post->post_content, 20 ) ) . '</p>';
        }

        $expiry_date = $coupon->get_date_expires();
        if ( $expiry_date ) {
            echo '<p class="wpcw-coupon-expiry">' . sprintf( __( 'Válido hasta: %s', 'wp-cupon-whatsapp' ), $expiry_date->date_i18n( get_option( 'date_format' ) ) ) . '</p>';
        }

        $usage_limit = $coupon->get_usage_limit();
        if ( $usage_limit ) {
            echo '<p class="wpcw-coupon-usage">' . sprintf( __( 'Límite de uso: %s', 'wp-cupon-whatsapp' ), $usage_limit ) . '</p>';
        }

        echo '</div>';

        if ( $show_redeem_button && is_user_logged_in() ) {
            echo '<div class="wpcw-coupon-actions">';
            echo '<button class="wpcw-redeem-btn button" data-coupon-id="' . esc_attr( $coupon->get_id() ) . '">';
            echo __( 'Canjear por WhatsApp', 'wp-cupon-whatsapp' );
            echo '</button>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Render single coupon redemption
     */
    private static function render_single_coupon_redemption( $coupon ) {
        echo '<div class="wpcw-single-redemption">';
        self::render_coupon_card( $coupon, true );
        echo '</div>';
    }

    /**
     * Get redemption status label
     */
    private static function get_redemption_status_label( $status ) {
        $labels = array(
            'pendiente_confirmacion' => __( 'Pendiente Confirmación', 'wp-cupon-whatsapp' ),
            'confirmado_por_negocio' => __( 'Confirmado', 'wp-cupon-whatsapp' ),
            'rechazado' => __( 'Rechazado', 'wp-cupon-whatsapp' ),
            'utilizado_en_pedido_wc' => __( 'Utilizado', 'wp-cupon-whatsapp' ),
            'expirado' => __( 'Expirado', 'wp-cupon-whatsapp' ),
            'cancelado' => __( 'Cancelado', 'wp-cupon-whatsapp' ),
        );

        return isset( $labels[$status] ) ? $labels[$status] : $status;
    }

    /**
     * Beneficiary Portal Shortcode
     */
    public static function render_beneficiary_portal( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<div class="wpcw-message wpcw-error">' . __( 'Debes iniciar sesión para ver tus beneficios.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        $user_id = get_current_user_id();
        $institution_id = get_user_meta( $user_id, '_wpcw_institution_id', true );

        if ( ! $institution_id ) {
            return '<div class="wpcw-message wpcw-info">' . __( 'Tu cuenta no está asociada a ninguna institución. No podemos mostrarte beneficios.', 'wp-cupon-whatsapp' ) . '</div>';
        }

        ob_start();

        // 1. Find all active convenios for the user's institution
        $convenio_ids = get_posts([
            'post_type' => 'wpcw_convenio',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_convenio_recipient_id',
                    'value' => $institution_id,
                ]
            ],
            'fields' => 'ids',
        ]);

        if ( empty( $convenio_ids ) ) {
            echo '<div class="wpcw-message wpcw-info">' . __( 'Tu institución no tiene convenios activos en este momento.', 'wp-cupon-whatsapp' ) . '</div>';
            return ob_get_clean();
        }

        // 2. Find all coupons associated with those convenios
        $coupon_query_args = [
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_wpcw_associated_convenio_id',
                    'value' => $convenio_ids,
                    'compare' => 'IN',
                ],
                [
                    'relation' => 'OR',
                    [
                        'key' => 'date_expires',
                        'value' => time(),
                        'compare' => '>',
                        'type' => 'NUMERIC'
                    ],
                    [
                        'key' => 'date_expires',
                        'compare' => 'NOT EXISTS'
                    ]
                ]
            ]
        ];

        $coupons_query = new WP_Query( $coupon_query_args );

        echo '<div class="wpcw-beneficiary-portal">';
        echo '<h2>' . __( 'Tu Catálogo de Beneficios', 'wp-cupon-whatsapp' ) . '</h2>';

        if ( $coupons_query->have_posts() ) {
            echo '<div class="wpcw-coupons-grid">';
            while ( $coupons_query->have_posts() ) {
                $coupons_query->the_post();
                $coupon = new WC_Coupon( get_the_ID() );
                self::render_coupon_card( $coupon, true );
            }
            echo '</div>';
        } else {
            echo '<div class="wpcw-message wpcw-info">' . __( 'No hay cupones disponibles para los convenios de tu institución en este momento.', 'wp-cupon-whatsapp' ) . '</div>';
        }
        wp_reset_postdata();

        echo '</div>';

        return ob_get_clean();
    }
}

// Initialize shortcodes
WPCW_Shortcodes::init();