<?php
/**
 * WP Cupón WhatsApp - User Dashboard Widget
 *
 * Elementor widget for user dashboard
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_User_Dashboard_Widget class
 */
class WPCW_User_Dashboard_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'wpcw_user_dashboard';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __( 'Dashboard de Usuario WPCW', 'wp-cupon-whatsapp' );
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-dashboard';
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
            'show_user_info',
            array(
                'label' => __( 'Mostrar Información del Usuario', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_loyalty_coupons',
            array(
                'label' => __( 'Mostrar Cupones de Fidelización', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_public_coupons',
            array(
                'label' => __( 'Mostrar Cupones Públicos', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_redemption_section',
            array(
                'label' => __( 'Mostrar Sección de Canje', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_redemptions_history',
            array(
                'label' => __( 'Mostrar Historial de Canjes', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'loyalty_coupons_limit',
            array(
                'label' => __( 'Límite de Cupones de Fidelización', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 20,
                'condition' => array(
                    'show_loyalty_coupons' => 'yes',
                ),
            )
        );

        $this->add_control(
            'public_coupons_limit',
            array(
                'label' => __( 'Límite de Cupones Públicos', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 20,
                'condition' => array(
                    'show_public_coupons' => 'yes',
                ),
            )
        );

        $this->add_control(
            'redemptions_limit',
            array(
                'label' => __( 'Límite de Historial de Canjes', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 20,
                'condition' => array(
                    'show_redemptions_history' => 'yes',
                ),
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
            'dashboard_background',
            array(
                'label' => __( 'Fondo del Dashboard', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f9f9f9',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-user-dashboard' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'section_background',
            array(
                'label' => __( 'Fondo de las Secciones', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-dashboard-section' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'section_border_color',
            array(
                'label' => __( 'Color del Borde de Secciones', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e1e1e1',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-dashboard-section' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'section_border_radius',
            array(
                'label' => __( 'Radio del Borde de Secciones', 'wp-cupon-whatsapp' ),
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
                    '{{WRAPPER}} .wpcw-dashboard-section' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'heading_color',
            array(
                'label' => __( 'Color de los Títulos', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#23282d',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-dashboard-section h3' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_background',
            array(
                'label' => __( 'Fondo de los Botones', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-action-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_color',
            array(
                'label' => __( 'Color del Texto de Botones', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-action-btn' => 'color: {{VALUE}};',
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

        // Build shortcode attributes
        $shortcode_atts = array();

        if ( $settings['show_user_info'] !== 'yes' ) {
            $shortcode_atts['show_user_info'] = 'false';
        }

        if ( $settings['show_loyalty_coupons'] !== 'yes' ) {
            $shortcode_atts['show_loyalty_coupons'] = 'false';
        }

        if ( $settings['show_public_coupons'] !== 'yes' ) {
            $shortcode_atts['show_public_coupons'] = 'false';
        }

        if ( $settings['show_redemption_section'] !== 'yes' ) {
            $shortcode_atts['show_redemption_section'] = 'false';
        }

        if ( $settings['show_redemptions_history'] !== 'yes' ) {
            $shortcode_atts['show_redemptions_history'] = 'false';
        }

        if ( $settings['loyalty_coupons_limit'] !== 6 ) {
            $shortcode_atts['loyalty_coupons_limit'] = $settings['loyalty_coupons_limit'];
        }

        if ( $settings['public_coupons_limit'] !== 6 ) {
            $shortcode_atts['public_coupons_limit'] = $settings['public_coupons_limit'];
        }

        if ( $settings['redemptions_limit'] !== 5 ) {
            $shortcode_atts['redemptions_limit'] = $settings['redemptions_limit'];
        }

        // Render the dashboard
        echo WPCW_Shortcodes::user_dashboard_shortcode( $shortcode_atts );

        // Add custom styles
        echo '<style>
            .elementor-element-' . $this->get_id() . ' .wpcw-user-dashboard {
                padding: 20px;
                border-radius: 8px;
            }
            .elementor-element-' . $this->get_id() . ' .wpcw-dashboard-section {
                margin-bottom: 30px;
                border: 1px solid #e1e1e1;
                padding: 20px;
            }
            .elementor-element-' . $this->get_id() . ' .wpcw-dashboard-section h3 {
                margin-top: 0;
                margin-bottom: 20px;
                font-size: 18px;
                font-weight: 600;
            }
            .elementor-element-' . $this->get_id() . ' .wpcw-action-buttons {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 15px;
            }
            .elementor-element-' . $this->get_id() . ' .wpcw-action-btn {
                padding: 10px 15px;
                border: none;
                border-radius: 4px;
                text-decoration: none;
                display: inline-block;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            .elementor-element-' . $this->get_id() . ' .wpcw-action-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        </style>';
    }

    /**
     * Render widget output in live preview
     */
    protected function _content_template() {
        ?>
        <div class="wpcw-user-dashboard elementor-preview-placeholder" style="padding: 20px; background: #f9f9f9; border-radius: 8px;">
            <div class="wpcw-dashboard-section" style="background: white; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #23282d;">Mi Información</h3>
                <p>Nombre: Usuario Demo</p>
                <p>Email: usuario@demo.com</p>
                <p>WhatsApp: +5491123456789</p>
            </div>

            <div class="wpcw-dashboard-section" style="background: white; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #23282d;">Acciones Rápidas</h3>
                <div class="wpcw-action-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="#" class="wpcw-action-btn" style="padding: 10px 15px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;">Ver Mis Cupones</a>
                    <a href="#" class="wpcw-action-btn" style="padding: 10px 15px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;">Cupones Públicos</a>
                    <a href="#" class="wpcw-action-btn" style="padding: 10px 15px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;">Canjear Cupones</a>
                </div>
            </div>

            <# if ( settings.show_loyalty_coupons === 'yes' ) { #>
                <div class="wpcw-dashboard-section" style="background: white; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #23282d;">Mis Cupones de Fidelización</h3>
                    <p>Vista previa de cupones de fidelización</p>
                </div>
            <# } #>

            <# if ( settings.show_public_coupons === 'yes' ) { #>
                <div class="wpcw-dashboard-section" style="background: white; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #23282d;">Cupones Públicos Disponibles</h3>
                    <p>Vista previa de cupones públicos</p>
                </div>
            <# } #>

            <# if ( settings.show_redemption_section === 'yes' ) { #>
                <div class="wpcw-dashboard-section" style="background: white; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #23282d;">Canjear Cupones</h3>
                    <p>Vista previa de sección de canje</p>
                </div>
            <# } #>

            <# if ( settings.show_redemptions_history === 'yes' ) { #>
                <div class="wpcw-dashboard-section" style="background: white; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #23282d;">Historial de Canjes</h3>
                    <p>Vista previa de historial de canjes</p>
                </div>
            <# } #>
        </div>
        <?php
    }
}