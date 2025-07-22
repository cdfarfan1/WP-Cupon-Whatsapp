<?php
/**
 * Elementor Formulario de Solicitud de Adhesión Widget.
 *
 * Elementor widget that displays the WPCW application form.
 *
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPCW_Elementor_Formulario_Adhesion_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wpcw-formulario-adhesion';
	}

	public function get_title() {
		return esc_html__( 'Formulario Solicitud Adhesión WPCW', 'wp-cupon-whatsapp' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return [ 'wpcw-categoria' ];
	}

    public function get_script_depends() {
        $scripts = [];
        if ( function_exists('wpcw_is_recaptcha_enabled') && wpcw_is_recaptcha_enabled() && function_exists('wpcw_get_recaptcha_site_key') && wpcw_get_recaptcha_site_key() ) {
            $scripts[] = 'google-recaptcha'; // Handle registered by wpcw_public_enqueue_scripts_styles in recaptcha-integration.php
        }
        return $scripts;
    }


	protected function _register_controls() {
		// Content Tab: Form Texts
        $this->start_controls_section(
			'section_content_form_texts',
			[
				'label' => esc_html__( 'Textos del Formulario', 'wp-cupon-whatsapp' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'legend_applicant_type',
			[
				'label' => esc_html__( 'Leyenda: Tipo de Solicitante', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Tipo de Solicitante', 'wp-cupon-whatsapp' ),
                'dynamic' => [ 'active' => true ],
			]
		);
        $this->add_control(
			'label_applicant_type_comercio',
			[
				'label' => esc_html__( 'Etiqueta: Comercio', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Comercio', 'wp-cupon-whatsapp' ),
                'dynamic' => [ 'active' => true ],
			]
		);
        $this->add_control(
			'label_applicant_type_institucion',
			[
				'label' => esc_html__( 'Etiqueta: Institución', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Institución', 'wp-cupon-whatsapp' ),
                'dynamic' => [ 'active' => true ],
			]
		);

        $fields = [
            'wpcw_fantasy_name' => 'Nombre de Fantasía',
            'wpcw_legal_name' => 'Nombre Legal',
            'wpcw_cuit' => 'CUIT',
            'wpcw_contact_person' => 'Persona de Contacto',
            'wpcw_email' => 'Email de Contacto',
            'wpcw_whatsapp' => 'Número de WhatsApp',
            'wpcw_address_main' => 'Dirección Principal',
            'wpcw_description' => 'Descripción del Negocio/Institución',
        ];

        foreach ($fields as $key => $default_label_text) {
            $this->add_control(
                'label_' . $key,
                [
                    'label' => sprintf(esc_html__( 'Etiqueta: %s', 'wp-cupon-whatsapp' ), esc_html__( $default_label_text, 'wp-cupon-whatsapp' )),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => esc_html__( $default_label_text, 'wp-cupon-whatsapp' ),
                    'dynamic' => [ 'active' => true ],
                ]
            );
        }

        $this->add_control(
			'text_submit_button',
			[
				'label' => esc_html__( 'Texto del Botón de Envío', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Enviar Solicitud', 'wp-cupon-whatsapp' ),
                'dynamic' => [ 'active' => true ],
			]
		);

        $this->add_control(
			'text_success_message',
			[
				'label' => esc_html__( 'Mensaje de Éxito', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Su solicitud ha sido enviada con éxito. Nos pondremos en contacto a la brevedad.', 'wp-cupon-whatsapp' ),
                'dynamic' => [ 'active' => true ],
			]
		);

        $this->end_controls_section();

        // Content Tab: reCAPTCHA
        if ( function_exists('wpcw_is_recaptcha_enabled') && wpcw_is_recaptcha_enabled() ) {
            $this->start_controls_section(
                'section_content_recaptcha',
                [
                    'label' => esc_html__( 'Google reCAPTCHA', 'wp-cupon-whatsapp' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );
            $this->add_control(
                'info_recaptcha',
                [
                    'type' => \Elementor\Controls_Manager::RAW_HTML,
                    'raw' => __( 'Google reCAPTCHA v2 está activado en los ajustes del plugin y se mostrará en el formulario.', 'wp-cupon-whatsapp' ),
                    'content_classes' => 'elementor-descriptor',
                ]
            );
            $this->end_controls_section();
        }

        // Style Tab: Form General
        $this->start_controls_section(
			'section_style_form_general',
			[
				'label' => esc_html__( 'Formulario General', 'wp-cupon-whatsapp' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->add_responsive_control(
			'form_max_width',
			[
				'label' => esc_html__( 'Ancho Máximo del Formulario', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 200, 'max' => 1200 ],
					'%' => [ 'min' => 10, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor' => 'max-width: {{SIZE}}{{UNIT}}; margin-left: auto; margin-right: auto;',
				],
			]
		);
        $this->end_controls_section();


		// Style Tab: Field Labels
        $this->start_controls_section(
			'section_style_labels',
			[
				'label' => esc_html__( 'Etiquetas de Campos', 'wp-cupon-whatsapp' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Color', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor label' => 'color: {{VALUE}};',
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor legend' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor label, {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor legend',
			]
		);
        $this->add_responsive_control(
			'label_margin_bottom',
			[
				'label' => esc_html__( 'Espaciado Inferior', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor legend' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor fieldset p label' => 'margin-bottom: 0;', // Reset for radio labels
				],
			]
		);
        $this->end_controls_section();

        // Style Tab: Input Fields
        $this->start_controls_section(
			'section_style_fields',
			[
				'label' => esc_html__( 'Campos de Entrada (Texto, Email, Tel, Textarea)', 'wp-cupon-whatsapp' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'field_typography',
				'selector' => '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="text"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="email"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="tel"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor textarea',
			]
		);
        $this->add_control(
			'field_text_color',
			[
				'label' => esc_html__( 'Color del Texto', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="text"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="email"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="tel"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor textarea' => 'color: {{VALUE}};',
				],
			]
		);
        $this->add_control(
			'field_background_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="text"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="email"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="tel"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor textarea' => 'background-color: {{VALUE}};',
				],
			]
		);
        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'field_border',
				'selector' => '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="text"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="email"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="tel"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor textarea',
			]
		);
        $this->add_responsive_control(
			'field_border_radius',
			[
				'label' => esc_html__( 'Radio del Borde', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="text"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="email"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="tel"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
        $this->add_responsive_control(
			'field_padding',
			[
				'label' => esc_html__( 'Padding', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="text"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="email"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="tel"], {{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; width: 100%; box-sizing: border-box;',
				],
			]
		);
        $this->add_responsive_control(
			'field_margin_bottom',
			[
				'label' => esc_html__( 'Espaciado Inferior del Párrafo Contenedor', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
                'range' => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor p:not(.wpcw-submit-p)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);
        $this->end_controls_section();

        // Style Tab: Submit Button
        $this->start_controls_section(
			'section_style_submit_button',
			[
				'label' => esc_html__( 'Botón de Envío', 'wp-cupon-whatsapp' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]',
			]
		);
		$this->add_responsive_control(
			'button_width',
			[
				'label' => esc_html__( 'Ancho del Botón', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'auto' => esc_html__( 'Automático', 'wp-cupon-whatsapp' ),
					'full' => esc_html__( 'Ancho Completo', 'wp-cupon-whatsapp' ),
                    'custom' => esc_html__( 'Personalizado', 'wp-cupon-whatsapp' ),
				],
				'default' => 'auto',
                'prefix_class' => 'wpcw-submit-button-width-',
			]
		);
        $this->add_responsive_control(
            'button_custom_width',
            [
                'label' => esc_html__( 'Ancho Personalizado', 'wp-cupon-whatsapp' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%', 'px' ],
                'range' => [
                    '%' => [ 'min' => 10, 'max' => 100 ],
                    'px' => [ 'min' => 50, 'max' => 500 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [ 'button_width' => 'custom' ],
            ]
        );
        $this->add_responsive_control(
			'button_align',
			[
				'label' => esc_html__( 'Alineación', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [ 'title' => esc_html__( 'Izquierda', 'wp-cupon-whatsapp' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Centro', 'wp-cupon-whatsapp' ), 'icon' => 'eicon-text-align-center' ],
					'right' => [ 'title' => esc_html__( 'Derecha', 'wp-cupon-whatsapp' ), 'icon' => 'eicon-text-align-right' ],
				],
				'selectors' => [
					'{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor .wpcw-submit-p' => 'text-align: {{VALUE}};',
				],
                'condition' => [ 'button_width!' => 'full' ],
			]
		);
        $this->start_controls_tabs( 'tabs_button_style' );
		$this->start_controls_tab(
			'tab_button_normal',
			[ 'label' => esc_html__( 'Normal', 'wp-cupon-whatsapp' ) ]
		);
        $this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Color del Texto', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]' => 'color: {{VALUE}};' ],
			]
		);
        $this->add_control(
			'button_background_color',
			[
				'label' => esc_html__( 'Color de Fondo', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]' => 'background-color: {{VALUE}};' ],
			]
		);
        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]',
			]
		);
        $this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Radio del Borde', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
				'selectors' => [ '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);
        $this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [ '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);
        $this->end_controls_tab();
		$this->start_controls_tab(
			'tab_button_hover',
			[ 'label' => esc_html__( 'Hover', 'wp-cupon-whatsapp' ) ]
		);
        $this->add_control(
			'button_text_color_hover',
			[
				'label' => esc_html__( 'Color del Texto', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]:hover' => 'color: {{VALUE}};' ],
			]
		);
        $this->add_control(
			'button_background_color_hover',
			[
				'label' => esc_html__( 'Color de Fondo', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]:hover' => 'background-color: {{VALUE}};' ],
			]
		);
        $this->add_control(
			'button_border_color_hover',
			[
				'label' => esc_html__( 'Color del Borde', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} #wpcw-solicitud-adhesion-form-elementor input[type="submit"]:hover' => 'border-color: {{VALUE}};' ],
                'condition' => [ 'button_border_border!' => '' ]
			]
		);
        $this->end_controls_tab();
		$this->end_controls_tabs();
        $this->end_controls_section();

        // Style Tab: Messages (Error/Success)
        $this->start_controls_section(
			'section_style_messages',
			[
				'label' => esc_html__( 'Mensajes (Error/Éxito)', 'wp-cupon-whatsapp' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'message_typography',
				'selector' => '{{WRAPPER}} .wpcw-form-errors p, {{WRAPPER}} .wpcw-form-success',
			]
		);
        $this->add_control(
			'success_message_color',
			[
				'label' => esc_html__( 'Color Texto Éxito', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wpcw-form-success' => 'color: {{VALUE}};' ],
			]
		);
        $this->add_control(
			'success_message_background_color',
			[
				'label' => esc_html__( 'Color Fondo Éxito', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wpcw-form-success' => 'background-color: {{VALUE}}; border-color: {{VALUE}}' ],
			]
		);
        $this->add_control(
			'error_message_color',
			[
				'label' => esc_html__( 'Color Texto Error', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wpcw-form-errors p' => 'color: {{VALUE}};' ],
			]
		);
        $this->add_control(
			'error_message_background_color',
			[
				'label' => esc_html__( 'Color Fondo Error', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wpcw-form-errors' => 'background-color: {{VALUE}}; border-color: {{VALUE}}' ],
			]
		);
        $this->add_responsive_control(
			'message_padding',
			[
				'label' => esc_html__( 'Padding del Mensaje', 'wp-cupon-whatsapp' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpcw-form-errors' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; margin-bottom: 15px; border-width: 1px; border-style: solid;',
                    '{{WRAPPER}} .wpcw-form-success' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; margin-bottom: 15px; border-width: 1px; border-style: solid;',
                ],
			]
		);
        $this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
        $form_processed_successfully = false;
        $errors = []; // Initialize errors array

        // --- Start Form Processing Logic (similar to shortcode) ---
        if ( isset( $_POST['wpcw_submit_solicitud_elementor'] ) ) {
            if ( ! isset( $_POST['wpcw_solicitud_adhesion_nonce_elementor'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wpcw_solicitud_adhesion_nonce_elementor'] ), 'wpcw_solicitud_adhesion_action_elementor' ) ) {
                $errors[] = __( 'Error de seguridad. Inténtalo de nuevo.', 'wp-cupon-whatsapp' );
            } else {
                if ( function_exists('wpcw_is_recaptcha_enabled') && wpcw_is_recaptcha_enabled() ) {
                    $recaptcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';
                    if ( function_exists('wpcw_verify_recaptcha') && !wpcw_verify_recaptcha($recaptcha_token) ) {
                        $errors[] = __('La verificación reCAPTCHA ha fallado. Por favor, inténtalo de nuevo.', 'wp-cupon-whatsapp');
                    }
                }

                if ( empty( $errors ) ) {
                    $applicant_type = isset( $_POST['wpcw_applicant_type'] ) ? sanitize_text_field( $_POST['wpcw_applicant_type'] ) : '';
                    $fantasy_name = isset( $_POST['wpcw_fantasy_name'] ) ? sanitize_text_field( $_POST['wpcw_fantasy_name'] ) : '';
                    $legal_name = isset( $_POST['wpcw_legal_name'] ) ? sanitize_text_field( $_POST['wpcw_legal_name'] ) : '';
                    $cuit = isset( $_POST['wpcw_cuit'] ) ? sanitize_text_field( $_POST['wpcw_cuit'] ) : '';
                    $contact_person = isset( $_POST['wpcw_contact_person'] ) ? sanitize_text_field( $_POST['wpcw_contact_person'] ) : '';
                    $email = isset( $_POST['wpcw_email'] ) ? sanitize_email( $_POST['wpcw_email'] ) : '';
                    $whatsapp = isset( $_POST['wpcw_whatsapp'] ) ? sanitize_text_field( $_POST['wpcw_whatsapp'] ) : '';

                    if ( empty( $applicant_type ) ) $errors[] = __( 'Por favor, selecciona el tipo de solicitante.', 'wp-cupon-whatsapp' );
                    if ( empty( $fantasy_name ) ) $errors[] = __( 'Por favor, introduce el nombre de fantasía.', 'wp-cupon-whatsapp' );
                    if ( empty( $legal_name ) ) $errors[] = __( 'Por favor, introduce el nombre legal.', 'wp-cupon-whatsapp' );
                    if ( empty( $cuit ) ) { $errors[] = __( 'Por favor, introduce el CUIT.', 'wp-cupon-whatsapp' ); }
                    elseif ( ! preg_match( '/^[0-9]{10,11}$/', $cuit ) ) { $errors[] = __( 'Por favor, introduce un CUIT válido (solo números, 10 u 11 dígitos).', 'wp-cupon-whatsapp' );}
                    if ( empty( $contact_person ) ) $errors[] = __( 'Por favor, introduce el nombre de la persona de contacto.', 'wp-cupon-whatsapp' );
                    if ( empty( $email ) ) { $errors[] = __( 'Por favor, introduce un email de contacto.', 'wp-cupon-whatsapp' ); }
                    elseif ( ! is_email( $email ) ) { $errors[] = __( 'Por favor, introduce un email de contacto válido.', 'wp-cupon-whatsapp' ); }
                    if ( empty( $whatsapp ) ) { $errors[] = __( 'Por favor, introduce un número de WhatsApp.', 'wp-cupon-whatsapp' ); }
                    elseif ( ! preg_match( '/^[0-9\s\+\(\)-]+$/', $whatsapp ) ) { $errors[] = __( 'Por favor, introduce un número de WhatsApp válido.', 'wp-cupon-whatsapp' ); }

                    if ( empty( $errors ) ) {
                        $address_main = isset( $_POST['wpcw_address_main'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpcw_address_main'] ) ) : '';
                        $description = isset( $_POST['wpcw_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpcw_description'] ) ) : '';
                        $post_data = [
                            'post_title'  => $fantasy_name,
                            'post_content' => $description,
                            'post_type'   => 'wpcw_application',
                            'post_status' => 'publish',
                        ];
                        $post_id = wp_insert_post( $post_data );

                        if ( ! is_wp_error( $post_id ) && $post_id > 0 ) {
                            update_post_meta( $post_id, '_wpcw_applicant_type', $applicant_type );
                            update_post_meta( $post_id, '_wpcw_legal_name', $legal_name );
                            update_post_meta( $post_id, '_wpcw_cuit', $cuit );
                            update_post_meta( $post_id, '_wpcw_contact_person', $contact_person );
                            update_post_meta( $post_id, '_wpcw_email', $email );
                            update_post_meta( $post_id, '_wpcw_whatsapp', $whatsapp );
                            update_post_meta( $post_id, '_wpcw_address_main', $address_main );
                            update_post_meta( $post_id, '_wpcw_application_status', 'pendiente_revision' );
                            if ( is_user_logged_in() ) {
                                update_post_meta( $post_id, '_wpcw_created_user_id', get_current_user_id() );
                            }

                            // Admin Email Notification (copied from shortcode, should be refactored into a function)
                            $admin_email = get_option( 'admin_email' );
                            $subject = sprintf( __( 'Nueva Solicitud de Adhesión: %s', 'wp-cupon-whatsapp' ), $fantasy_name );
                            $message_body = sprintf( __( 'Se ha recibido una nueva solicitud de adhesión para el programa WP Canje Cupon Whatsapp.', 'wp-cupon-whatsapp' ) ) . "\r\n\r\n";
                            $message_body .= sprintf( __( 'Nombre de Fantasía: %s', 'wp-cupon-whatsapp' ), $fantasy_name ) . "\r\n";
                            // ... (rest of the email body fields) ...
                            $edit_link = get_edit_post_link( $post_id );
                            if ( $edit_link ) {
                                $message_body .= sprintf( __( 'Puedes revisar y procesar esta solicitud aquí: %s', 'wp-cupon-whatsapp' ), $edit_link ) . "\r\n";
                            }
                            $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
                            wp_mail( $admin_email, $subject, $message_body, $headers );

                            $form_processed_successfully = true;
                        } else {
                            $errors[] = __( 'Hubo un error al procesar su solicitud. Por favor, inténtelo de nuevo más tarde.', 'wp-cupon-whatsapp' );
                        }
                    }
                }
            }
        }
        // --- End Form Processing Logic ---

        if ( $form_processed_successfully ) {
            echo '<div class="wpcw-form-success">' . esc_html( $settings['text_success_message'] ) . '</div>';
            return; // Don't show the form again
        }

        if ( ! empty( $errors ) ) {
            echo '<div class="wpcw-form-errors">';
            foreach ( $errors as $error ) {
                echo '<p>' . esc_html( $error ) . '</p>';
            }
            echo '</div>';
        }
        ?>
        <form id="wpcw-solicitud-adhesion-form-elementor" method="POST" action="<?php echo esc_url( get_permalink() ); /* Or rely on Elementor's default action which is current page */ ?>">
            <fieldset>
                <legend><?php echo esc_html( $settings['legend_applicant_type'] ); ?></legend>
                <p>
                    <label>
                        <input type="radio" name="wpcw_applicant_type" value="comercio" <?php checked( (isset($_POST['wpcw_applicant_type']) && $_POST['wpcw_applicant_type'] === 'comercio'), true, true ); ?><?php echo ( !isset($_POST['wpcw_applicant_type']) ) ? 'checked' : ''; ?>>
                        <?php echo esc_html( $settings['label_applicant_type_comercio'] ); ?>
                    </label>
                </p>
                <p>
                    <label>
                        <input type="radio" name="wpcw_applicant_type" value="institucion" <?php checked( (isset($_POST['wpcw_applicant_type']) && $_POST['wpcw_applicant_type'] === 'institucion'), true, true ); ?>>
                        <?php echo esc_html( $settings['label_applicant_type_institucion'] ); ?>
                    </label>
                </p>
            </fieldset>

            <?php
            $form_fields = [
                'wpcw_fantasy_name' => ['type' => 'text', 'required' => true, 'label_key' => 'label_wpcw_fantasy_name'],
                'wpcw_legal_name'   => ['type' => 'text', 'required' => true, 'label_key' => 'label_wpcw_legal_name'],
                'wpcw_cuit'         => ['type' => 'text', 'required' => true, 'label_key' => 'label_wpcw_cuit'],
                'wpcw_contact_person' => ['type' => 'text', 'required' => true, 'label_key' => 'label_wpcw_contact_person'],
                'wpcw_email'        => ['type' => 'email', 'required' => true, 'label_key' => 'label_wpcw_email'],
                'wpcw_whatsapp'     => ['type' => 'tel', 'required' => true, 'label_key' => 'label_wpcw_whatsapp'],
                'wpcw_address_main' => ['type' => 'textarea', 'required' => false, 'rows' => 3, 'label_key' => 'label_wpcw_address_main'],
                'wpcw_description'  => ['type' => 'textarea', 'required' => false, 'rows' => 5, 'label_key' => 'label_wpcw_description'],
            ];

            foreach ($form_fields as $name => $field) :
                $value = isset( $_POST[$name] ) ? sanitize_text_field( wp_unslash( $_POST[$name] ) ) : '';
                if ($field['type'] === 'textarea') {
                    $value = isset( $_POST[$name] ) ? sanitize_textarea_field( wp_unslash( $_POST[$name] ) ) : '';
                } elseif ($field['type'] === 'email') {
                     $value = isset( $_POST[$name] ) ? sanitize_email( wp_unslash( $_POST[$name] ) ) : '';
                }
            ?>
            <p>
                <label for="<?php echo esc_attr( $name ); ?>_elementor"><?php echo esc_html( $settings[$field['label_key']] ); ?></label><br>
                <?php if ($field['type'] === 'textarea') : ?>
                    <textarea id="<?php echo esc_attr( $name ); ?>_elementor" name="<?php echo esc_attr( $name ); ?>" rows="<?php echo esc_attr($field['rows']); ?>" <?php if ($field['required']) echo 'required'; ?>><?php echo esc_textarea( $value ); ?></textarea>
                <?php else : ?>
                    <input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr( $name ); ?>_elementor" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php if ($field['required']) echo 'required'; ?>>
                <?php endif; ?>
            </p>
            <?php endforeach; ?>

            <?php wp_nonce_field( 'wpcw_solicitud_adhesion_action_elementor', 'wpcw_solicitud_adhesion_nonce_elementor' ); ?>

            <?php
            if ( function_exists('wpcw_is_recaptcha_enabled') && wpcw_is_recaptcha_enabled() && function_exists('wpcw_display_recaptcha') ) {
                wpcw_display_recaptcha(); // This function should output the reCAPTCHA HTML
            }
            ?>

            <p class="wpcw-submit-p">
                <input type="submit" name="wpcw_submit_solicitud_elementor" value="<?php echo esc_attr( $settings['text_submit_button'] ); ?>">
            </p>
        </form>
        <?php
	}

	protected function _content_template() {
        ?>
        <#
        // In editor, just display the form structure. Actual submission won't work.
        var form_fields = {
            'wpcw_fantasy_name': {type: 'text', required: true, label_key: 'label_wpcw_fantasy_name'},
            'wpcw_legal_name': {type: 'text', required: true, label_key: 'label_wpcw_legal_name'},
            'wpcw_cuit': {type: 'text', required: true, label_key: 'label_wpcw_cuit'},
            'wpcw_contact_person': {type: 'text', required: true, label_key: 'label_wpcw_contact_person'},
            'wpcw_email': {type: 'email', required: true, label_key: 'label_wpcw_email'},
            'wpcw_whatsapp': {type: 'tel', required: true, label_key: 'label_wpcw_whatsapp'},
            'wpcw_address_main': {type: 'textarea', required: false, rows: 3, label_key: 'label_wpcw_address_main'},
            'wpcw_description': {type: 'textarea', required: false, rows: 5, label_key: 'label_wpcw_description'},
        };
        #>
        <form id="wpcw-solicitud-adhesion-form-elementor" method="POST" action="">
            <fieldset>
                <legend>{{{ settings.legend_applicant_type }}}</legend>
                <p>
                    <label>
                        <input type="radio" name="wpcw_applicant_type" value="comercio" checked>
                        {{{ settings.label_applicant_type_comercio }}}
                    </label>
                </p>
                <p>
                    <label>
                        <input type="radio" name="wpcw_applicant_type" value="institucion">
                        {{{ settings.label_applicant_type_institucion }}}
                    </label>
                </p>
            </fieldset>

            <# _.each( form_fields, function( field, name ) { #>
            <p>
                <label for="{{ name }}_elementor">{{{ settings[field.label_key] }}}</label><br>
                <# if (field.type === 'textarea') { #>
                    <textarea id="{{ name }}_elementor" name="{{ name }}" rows="{{ field.rows }}" <# if (field.required) { #>required<# } #>></textarea>
                <# } else { #>
                    <input type="{{ field.type }}" id="{{ name }}_elementor" name="{{ name }}" value="" <# if (field.required) { #>required<# } #>>
                <# } #>
            </p>
            <# }); #>

            <?php // Nonce field is not rendered in JS template for security. ?>
            <?php // reCAPTCHA placeholder for editor if needed ?>
            <#
                if (typeof wpcw_is_recaptcha_enabled === 'function' && wpcw_is_recaptcha_enabled()) {
                    // This is tricky because wpcw_is_recaptcha_enabled is PHP.
                    // We assume if the section_content_recaptcha control section was added, it's enabled.
                    // A better way would be to pass a setting or use JS detection if possible.
                    var siteKey = '<?php echo esc_js(function_exists("wpcw_get_recaptcha_site_key") ? wpcw_get_recaptcha_site_key() : ""); ?>';
                    if (siteKey) {
            #>
                <div class="g-recaptcha-placeholder" style="height:78px; width: 304px; background: #f0f0f0; border: 1px dashed #ccc; display:flex; align-items:center; justify-content:center; margin-bottom: 15px;">
                    <span style="font-size:12px; color: #777;"><?php esc_html_e( 'reCAPTCHA aquí', 'wp-cupon-whatsapp' ); ?></span>
                </div>
            <#      }
                }
            #>


            <p class="wpcw-submit-p">
                <input type="submit" name="wpcw_submit_solicitud_elementor" value="{{{ settings.text_submit_button }}}">
            </p>
        </form>
        <?php
	}
}
