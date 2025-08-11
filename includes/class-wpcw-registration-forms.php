<?php
/**
 * Formularios de registro para WP Cupón WhatsApp
 *
 * @package WP_Cupon_WhatsApp
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para manejar los formularios de registro
 */
class WPCW_Registration_Forms {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('wpcw_registro_comercio', array($this, 'render_business_registration'));
        add_shortcode('wpcw_registro_cliente', array($this, 'render_customer_registration'));
        add_action('wp_ajax_nopriv_wpcw_register_business', array($this, 'handle_business_registration'));
        add_action('wp_ajax_nopriv_wpcw_register_customer', array($this, 'handle_customer_registration'));
        add_action('init', array($this, 'register_form_scripts'));
    }

    /**
     * Registra los scripts y estilos necesarios
     */
    public function register_form_scripts() {
        wp_register_style(
            'wpcw-forms',
            plugin_dir_url(__FILE__) . 'css/forms.css',
            array(),
            WPCW_VERSION
        );

        wp_register_script(
            'wpcw-forms',
            plugin_dir_url(__FILE__) . 'js/forms.js',
            array('jquery'),
            WPCW_VERSION,
            true
        );

        wp_localize_script('wpcw-forms', 'wpcw_forms', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpcw_registration'),
            'messages' => array(
                'required' => __('Este campo es obligatorio.', 'wp-cupon-whatsapp'),
                'email' => __('Por favor, ingresa un email válido.', 'wp-cupon-whatsapp'),
                'phone' => __('Por favor, ingresa un número de teléfono válido.', 'wp-cupon-whatsapp'),
                'success' => __('Registro completado con éxito.', 'wp-cupon-whatsapp'),
                'error' => __('Ocurrió un error. Por favor, intenta nuevamente.', 'wp-cupon-whatsapp')
            )
        ));
    }

    /**
     * Renderiza el formulario de registro de comercios
     */
    public function render_business_registration() {
        wp_enqueue_style('wpcw-forms');
        wp_enqueue_script('wpcw-forms');

        ob_start();
        ?>
        <div class="wpcw-form-container">
            <form id="wpcw-business-registration" class="wpcw-form" method="post">
                <h2><?php _e('Registro de Comercio', 'wp-cupon-whatsapp'); ?></h2>
                
                <div class="wpcw-form-group">
                    <label for="business_name"><?php _e('Nombre del Comercio', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="text" id="business_name" name="business_name" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="business_email"><?php _e('Email del Comercio', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="email" id="business_email" name="business_email" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="business_phone"><?php _e('WhatsApp del Comercio', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="tel" id="business_phone" name="business_phone" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="business_address"><?php _e('Dirección', 'wp-cupon-whatsapp'); ?> *</label>
                    <textarea id="business_address" name="business_address" required></textarea>
                </div>

                <div class="wpcw-form-group">
                    <label for="business_description"><?php _e('Descripción del Comercio', 'wp-cupon-whatsapp'); ?></label>
                    <textarea id="business_description" name="business_description"></textarea>
                </div>

                <div class="wpcw-form-group">
                    <label for="owner_name"><?php _e('Nombre del Propietario', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="text" id="owner_name" name="owner_name" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="owner_email"><?php _e('Email del Propietario', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="email" id="owner_email" name="owner_email" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="password"><?php _e('Contraseña', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="password" id="password" name="password" required />
                </div>

                <?php wp_nonce_field('wpcw_business_registration', 'wpcw_business_nonce'); ?>
                
                <div class="wpcw-form-group">
                    <button type="submit" class="wpcw-submit-button">
                        <?php _e('Registrar Comercio', 'wp-cupon-whatsapp'); ?>
                    </button>
                </div>

                <div class="wpcw-form-messages"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderiza el formulario de registro de clientes
     */
    public function render_customer_registration() {
        wp_enqueue_style('wpcw-forms');
        wp_enqueue_script('wpcw-forms');

        ob_start();
        ?>
        <div class="wpcw-form-container">
            <form id="wpcw-customer-registration" class="wpcw-form" method="post">
                <h2><?php _e('Registro de Cliente', 'wp-cupon-whatsapp'); ?></h2>
                
                <div class="wpcw-form-group">
                    <label for="customer_name"><?php _e('Nombre Completo', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="text" id="customer_name" name="customer_name" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="customer_email"><?php _e('Email', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="email" id="customer_email" name="customer_email" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="customer_phone"><?php _e('WhatsApp', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="tel" id="customer_phone" name="customer_phone" required />
                </div>

                <div class="wpcw-form-group">
                    <label for="customer_password"><?php _e('Contraseña', 'wp-cupon-whatsapp'); ?> *</label>
                    <input type="password" id="customer_password" name="customer_password" required />
                </div>

                <?php wp_nonce_field('wpcw_customer_registration', 'wpcw_customer_nonce'); ?>
                
                <div class="wpcw-form-group">
                    <button type="submit" class="wpcw-submit-button">
                        <?php _e('Registrarse', 'wp-cupon-whatsapp'); ?>
                    </button>
                </div>

                <div class="wpcw-form-messages"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Maneja el registro de comercios
     */
    public function handle_business_registration() {
        check_ajax_referer('wpcw_business_registration', 'nonce');

        $business_data = array(
            'post_title' => isset($_POST['business_name']) ? sanitize_text_field($_POST['business_name']) : '',
            'post_content' => sanitize_textarea_field($_POST['business_description']),
            'post_type' => 'wpcw_business',
            'post_status' => 'pending'
        );

        $business_id = wp_insert_post($business_data);

        if (!is_wp_error($business_id)) {
            // Guardar meta datos del comercio
            update_post_meta($business_id, '_wpcw_business_email', sanitize_email($_POST['business_email']));
            update_post_meta($business_id, '_wpcw_business_phone', isset($_POST['business_phone']) ? sanitize_text_field($_POST['business_phone']) : '');
            update_post_meta($business_id, '_wpcw_business_address', sanitize_textarea_field($_POST['business_address']));

            // Crear usuario para el propietario
            $user_data = array(
                'user_login' => sanitize_email($_POST['owner_email']),
                'user_email' => sanitize_email($_POST['owner_email']),
                'user_pass' => $_POST['password'],
                'first_name' => isset($_POST['owner_name']) ? sanitize_text_field($_POST['owner_name']) : '',
                'role' => 'wpcw_business_owner'
            );

            $user_id = wp_insert_user($user_data);

            if (!is_wp_error($user_id)) {
                update_user_meta($user_id, '_wpcw_associated_business', $business_id);
                wp_send_json_success(__('Registro completado. Pendiente de aprobación.', 'wp-cupon-whatsapp'));
            }
        }

        wp_send_json_error(__('Error al procesar el registro.', 'wp-cupon-whatsapp'));
    }

    /**
     * Maneja el registro de clientes
     */
    public function handle_customer_registration() {
        check_ajax_referer('wpcw_customer_registration', 'nonce');

        $user_data = array(
            'user_login' => sanitize_email($_POST['customer_email']),
            'user_email' => sanitize_email($_POST['customer_email']),
            'user_pass' => $_POST['customer_password'],
            'first_name' => isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '',
            'role' => 'customer'
        );

        $user_id = wp_insert_user($user_data);

        if (!is_wp_error($user_id)) {
            update_user_meta($user_id, '_wpcw_phone', isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '');
            wp_send_json_success(__('Registro completado exitosamente.', 'wp-cupon-whatsapp'));
        }

        wp_send_json_error(__('Error al procesar el registro.', 'wp-cupon-whatsapp'));
    }
}

// Inicializar la clase
new WPCW_Registration_Forms();
