<?php
/**
 * WP Cupón WhatsApp - Coupons List Widget
 *
 * Elementor widget for displaying coupons
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Coupons_List_Widget class
 */
class WPCW_Coupons_List_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'wpcw_coupons_list';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __( 'Lista de Cupones WPCW', 'wp-cupon-whatsapp' );
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-post-list';
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
            'coupon_type',
            array(
                'label' => __( 'Tipo de Cupones', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'public',
                'options' => array(
                    'public' => __( 'Cupones Públicos', 'wp-cupon-whatsapp' ),
                    'loyalty' => __( 'Cupones de Fidelización', 'wp-cupon-whatsapp' ),
                    'all' => __( 'Todos los Cupones', 'wp-cupon-whatsapp' ),
                ),
            )
        );

        $this->add_control(
            'limit',
            array(
                'label' => __( 'Número de Cupones', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 50,
            )
        );

        $this->add_control(
            'show_filters',
            array(
                'label' => __( 'Mostrar Filtros', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_redeem_buttons',
            array(
                'label' => __( 'Mostrar Botones de Canje', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Sí', 'wp-cupon-whatsapp' ),
                'label_off' => __( 'No', 'wp-cupon-whatsapp' ),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'business_id',
            array(
                'label' => __( 'ID de Comercio (opcional)', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __( 'Mostrar solo cupones de un comercio específico', 'wp-cupon-whatsapp' ),
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
            'columns',
            array(
                'label' => __( 'Columnas', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => array(
                    '1' => __( '1 Columna', 'wp-cupon-whatsapp' ),
                    '2' => __( '2 Columnas', 'wp-cupon-whatsapp' ),
                    '3' => __( '3 Columnas', 'wp-cupon-whatsapp' ),
                    '4' => __( '4 Columnas', 'wp-cupon-whatsapp' ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-coupons-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ),
            )
        );

        $this->add_control(
            'card_background',
            array(
                'label' => __( 'Fondo de la Tarjeta', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-coupon-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_border_color',
            array(
                'label' => __( 'Color del Borde', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e1e1e1',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-coupon-card' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_border_radius',
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
                    '{{WRAPPER}} .wpcw-coupon-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'discount_badge_color',
            array(
                'label' => __( 'Color del Badge de Descuento', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#46b450',
                'selectors' => array(
                    '{{WRAPPER}} .wpcw-discount-badge' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .wpcw-redeem-btn' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .wpcw-redeem-btn' => 'color: {{VALUE}};',
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

        $shortcode_atts = array(
            'limit' => $settings['limit'],
            'show_filters' => $settings['show_filters'] === 'yes' ? 'true' : 'false',
        );

        if ( $settings['coupon_type'] === 'public' ) {
            echo WPCW_Shortcodes::public_coupons_shortcode( $shortcode_atts );
        } elseif ( $settings['coupon_type'] === 'loyalty' ) {
            echo WPCW_Shortcodes::my_coupons_shortcode( $shortcode_atts );
        } else {
            // Show both types
            echo '<div class="wpcw-coupons-combined">';

            if ( is_user_logged_in() ) {
                $user_institution = get_user_meta( get_current_user_id(), '_wpcw_user_institution_id', true );
                if ( $user_institution ) {
                    echo '<h3>' . __( 'Mis Cupones de Fidelización', 'wp-cupon-whatsapp' ) . '</h3>';
                    echo WPCW_Shortcodes::my_coupons_shortcode( array_merge( $shortcode_atts, array( 'limit' => ceil( $settings['limit'] / 2 ) ) ) );
                }
            }

            echo '<h3>' . __( 'Cupones Públicos', 'wp-cupon-whatsapp' ) . '</h3>';
            echo WPCW_Shortcodes::public_coupons_shortcode( array_merge( $shortcode_atts, array( 'limit' => ceil( $settings['limit'] / 2 ) ) ) );

            echo '</div>';
        }

        // Add custom styles
        echo '<style>
            .elementor-element-' . $this->get_id() . ' .wpcw-coupons-grid {
                gap: 20px;
                margin: 20px 0;
            }
            .elementor-element-' . $this->get_id() . ' .wpcw-coupon-card {
                border: 1px solid #e1e1e1;
                padding: 20px;
                transition: all 0.3s ease;
            }
            .elementor-element-' . $this->get_id() . ' .wpcw-coupon-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
        </style>';
    }

    /**
     * Render widget output in live preview
     */
    protected function _content_template() {
        ?>
        <div class="wpcw-coupons-grid elementor-preview-placeholder" style="grid-template-columns: repeat({{ settings.columns }}, 1fr); gap: 20px;">
            <# for (var i = 1; i <= 3; i++) { #>
                <div class="wpcw-coupon-card" style="border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px;">
                    <div class="wpcw-coupon-header" style="margin-bottom: 15px;">
                        <h4 style="margin: 0; color: #23282d;">CUPON{{ i }}0OFF</h4>
                        <div class="wpcw-discount-badge" style="display: inline-block; padding: 4px 8px; background: #46b450; color: white; border-radius: 4px; font-size: 12px; margin-top: 5px;">
                            {{ i }}0% OFF
                        </div>
                    </div>
                    <div class="wpcw-coupon-body">
                        <p style="margin: 0; color: #666; font-size: 14px;">
                            Descripción del cupón de ejemplo para vista previa.
                        </p>
                        <p style="margin: 5px 0; color: #888; font-size: 12px;">
                            Válido hasta: 2025-12-31
                        </p>
                    </div>
                    <# if ( settings.show_redeem_buttons === 'yes' ) { #>
                        <div class="wpcw-coupon-actions" style="margin-top: 15px;">
                            <button class="wpcw-redeem-btn" style="background: #007cba; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                Canjear por WhatsApp
                            </button>
                        </div>
                    <# } #>
                </div>
            <# } #>
        </div>
        <?php
    }
}