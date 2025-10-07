<?php
/**
 * WP Cupón WhatsApp - Adhesion Form Widget
 *
 * Elementor widget for business adhesion form
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Adhesion_Form_Widget class
 */
class WPCW_Adhesion_Form_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'wpcw_adhesion_form';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __( 'Formulario de Adhesión WPCW', 'wp-cupon-whatsapp' );
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return array( 'wpcw-widgets' );
    }

    /**
     * Register widget controls
     */
    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Contenido', 'wp-cupon-whatsapp' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_title',
            array(
                'label' => __( 'Mostrar Título', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'title',
            array(
                'label' => __( 'Título', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __( 'Solicitud de Adhesión', 'wp-cupon-whatsapp' ),
                'condition' => array(
                    'show_title' => 'yes',
                ),
            )
        );

        $this->add_control(
            'description',
            array(
                'label' => __( 'Descripción', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __( 'Complete el formulario para adherirse al programa de fidelización WP Cupón WhatsApp.', 'wp-cupon-whatsapp' ),
            )
        );

        $this->add_control(
            'redirect_url',
            array(
                'label' => __( 'URL de Redirección', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __( 'https://tu-sitio.com/gracias', 'wp-cupon-whatsapp' ),
                'description' => __( 'URL a la que redirigir después de enviar el formulario exitosamente.', 'wp-cupon-whatsapp' ),
            )
        );

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __( 'Estilos', 'wp-cupon-whatsapp' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'form_background',
            array(
                'label' => __( 'Fondo del Formulario', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f9f9f9',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-adhesion-form' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'form_padding',
            array(
                'label' => __( 'Padding del Formulario', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'default' => array(
                    'top' => '20',
                    'right' => '20',
                    'bottom' => '20',
                    'left' => '20',
                    'unit' => 'px',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-adhesion-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'form_border_radius',
            array(
                'label' => __( 'Radio del Borde', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'default' => array(
                    'top' => '8',
                    'right' => '8',
                    'bottom' => '8',
                    'left' => '8',
                    'unit' => 'px',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-adhesion-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'button_background',
            array(
                'label' => __( 'Fondo del Botón', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-submit-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_color',
            array(
                'label' => __( 'Color del Texto del Botón', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-submit-btn' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( ! is_user_logged_in() ) {
            echo '<div class="wpcw-message wpcw-error">' . __( 'Debes iniciar sesión para enviar una solicitud de adhesión.', 'wp-cupon-whatsapp' ) . '</div>';
            return;
        }

        $shortcode_atts = array(
            'show_title' => $settings['show_title'] === 'yes' ? 'true' : 'false',
        );

        if ( ! empty( $settings['redirect_url']['url'] ) ) {
            $shortcode_atts['redirect_url'] = $settings['redirect_url']['url'];
        }

        // Custom title if provided
        if ( ! empty( $settings['title'] ) && $settings['show_title'] === 'yes' ) {
            echo '<h2 class="wpcw-custom-title">' . esc_html( $settings['title'] ) . '</h2>';
            $shortcode_atts['show_title'] = 'false';
        }

        // Custom description if provided
        if ( ! empty( $settings['description'] ) ) {
            echo '<div class="wpcw-custom-description">' . wp_kses_post( $settings['description'] ) . '</div>';
        }

        // Render the shortcode
        echo WPCW_Shortcodes::adhesion_form_shortcode( $shortcode_atts );
    }

    /**
     * Render widget output in live preview
     */
    protected function _content_template() {
        ?>
        <#
        if ( settings.show_title === 'yes' && settings.title ) {
            #>
            <h2 class="wpcw-custom-title">{{{ settings.title }}}</h2>
            <#
        }

        if ( settings.description ) {
            #>
            <div class="wpcw-custom-description">{{{ settings.description }}}</div>
            <#
        }
        #>

        <div class="wpcw-adhesion-form elementor-preview-placeholder">
            <p><?php echo __( 'Vista previa del formulario de adhesión', 'wp-cupon-whatsapp' ); ?></p>
            <p><?php echo __( 'El formulario completo se mostrará en el frontend.', 'wp-cupon-whatsapp' ); ?></p>
        </div>
        <?php
    }
}