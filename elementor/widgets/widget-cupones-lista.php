<?php
/**
 * Elementor Lista de Cupones Widget.
 *
 * Elementor widget that displays a list of WPCW coupons.
 *
 * @since 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPCW_Elementor_Cupones_Lista_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve Lista de Cupones widget name.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpcw-cupones-lista';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Lista de Cupones widget title.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Lista de Cupones WPCW', 'wp-cupon-whatsapp' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Lista de Cupones widget icon.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Lista de Cupones widget belongs to.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'wpcw-categoria' );
	}

    /**
	 * Get script depends.
	 *
	 * Retrieve the list of scripts the widget depends on.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return array Widget scripts dependencies.
	 */
    public function get_script_depends() {
        // Depend on the main plugin's canje handler script
        return array( 'wpcw-canje-handler' );
    }

	/**
	 * Register Lista de Cupones widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 1.1.0
	 * @access protected
	 */
	protected function _register_controls() {

		// Content Tab
		$this->start_controls_section(
			'section_content_source',
			array(
				'label' => esc_html__( 'Fuente de Cupones', 'wp-cupon-whatsapp' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'tipo_cupon',
			array(
				'label'       => esc_html__( 'Tipo de Cupones a Mostrar', 'wp-cupon-whatsapp' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'publicos',
				'options'     => array(
					'publicos'    => esc_html__( 'Cupones Públicos', 'wp-cupon-whatsapp' ),
					'mis_cupones' => esc_html__( 'Mis Cupones de Lealtad', 'wp-cupon-whatsapp' ),
				),
				'description' => esc_html__( 'Selecciona si mostrar cupones públicos o los cupones de lealtad del usuario actual.', 'wp-cupon-whatsapp' ),
			)
		);

		$this->add_control(
			'mensaje_no_logueado',
			array(
				'label'     => esc_html__( 'Mensaje (Usuario no logueado)', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::TEXTAREA,
				'default'   => esc_html__( 'Por favor, inicia sesión para ver tus cupones disponibles.', 'wp-cupon-whatsapp' ),
				'condition' => array(
					'tipo_cupon' => 'mis_cupones',
				),
			)
		);

        $default_my_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
		$this->add_control(
			'enlace_login',
			array(
				'label'     => esc_html__( 'Enlace a Página de Login/Mi Cuenta', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::URL,
				'default'   => array(
                    'url'         => $default_my_account_url,
                    'is_external' => false,
                    'nofollow'    => true,
                ),
				'condition' => array(
					'tipo_cupon' => 'mis_cupones',
				),
                'dynamic'   => array(
                    'active' => true,
                ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_query',
			array(
				'label' => esc_html__( 'Consulta', 'wp-cupon-whatsapp' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'numero_cupones',
			array(
				'label'       => esc_html__( 'Número de Cupones', 'wp-cupon-whatsapp' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 12,
				'min'         => -1,
				'description' => esc_html__( '-1 para mostrar todos los cupones.', 'wp-cupon-whatsapp' ),
			)
		);

		$this->add_control(
			'ordenar_por',
			array(
				'label'   => esc_html__( 'Ordenar por', 'wp-cupon-whatsapp' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'     => esc_html__( 'Fecha de Publicación', 'wp-cupon-whatsapp' ),
					'title'    => esc_html__( 'Título (Código Cupón)', 'wp-cupon-whatsapp' ),
					'rand'     => esc_html__( 'Aleatorio', 'wp-cupon-whatsapp' ),
					'modified' => esc_html__( 'Última Modificación', 'wp-cupon-whatsapp' ),
				),
			)
		);

		$this->add_control(
			'orden',
			array(
				'label'   => esc_html__( 'Orden', 'wp-cupon-whatsapp' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'ASC'  => array(
						'title' => esc_html__( 'Ascendente', 'wp-cupon-whatsapp' ),
						'icon'  => 'eicon-sort-up',
					),
					'DESC' => array(
						'title' => esc_html__( 'Descendente', 'wp-cupon-whatsapp' ),
						'icon'  => 'eicon-sort-down',
					),
				),
				'default' => 'DESC',
				'toggle'  => false,
			)
		);

		$this->end_controls_section();

        $this->start_controls_section(
			'section_content_layout',
			array(
				'label' => esc_html__( 'Diseño de Tarjeta', 'wp-cupon-whatsapp' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

        $this->add_control(
			'mostrar_imagen',
			array(
				'label'        => esc_html__( 'Mostrar Imagen del Cupón', 'wp-cupon-whatsapp' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Sí', 'wp-cupon-whatsapp' ),
				'label_off'    => esc_html__( 'No', 'wp-cupon-whatsapp' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

        $this->add_control(
			'mostrar_codigo',
			array(
				'label'        => esc_html__( 'Mostrar Código del Cupón', 'wp-cupon-whatsapp' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Sí', 'wp-cupon-whatsapp' ),
				'label_off'    => esc_html__( 'No', 'wp-cupon-whatsapp' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

        $this->add_control(
			'mostrar_descripcion',
			array(
				'label'        => esc_html__( 'Mostrar Descripción', 'wp-cupon-whatsapp' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Sí', 'wp-cupon-whatsapp' ),
				'label_off'    => esc_html__( 'No', 'wp-cupon-whatsapp' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

        $this->add_control(
			'longitud_descripcion',
			array(
				'label'     => esc_html__( 'Longitud de Descripción (palabras)', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 20,
                'min'       => 1,
				'condition' => array(
					'mostrar_descripcion' => 'yes',
				),
			)
		);

        $this->add_control(
			'texto_boton_canje',
			array(
				'label'   => esc_html__( 'Texto del Botón de Canje', 'wp-cupon-whatsapp' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Canjear Cupón', 'wp-cupon-whatsapp' ),
                'dynamic' => array(
                    'active' => true,
                ),
			)
		);

        $this->add_control(
			'mensaje_no_cupones',
			array(
				'label'   => esc_html__( 'Mensaje (No hay cupones)', 'wp-cupon-whatsapp' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'No hay cupones disponibles en este momento.', 'wp-cupon-whatsapp' ),
                'dynamic' => array(
                    'active' => true,
                ),
			)
		);

        $this->end_controls_section();

        // Style Tab
        $this->start_controls_section(
			'section_style_grid',
			array(
				'label' => esc_html__( 'Grid de Cupones', 'wp-cupon-whatsapp' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

        $this->add_responsive_control(
            'columnas',
            array(
                'label'          => esc_html__( 'Columnas', 'wp-cupon-whatsapp' ),
                'type'           => \Elementor\Controls_Manager::SELECT,
                'default'        => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options'        => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ),
                'selectors'      => array(
                    '{{WRAPPER}} .wpcw-coupons-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ),
            )
        );

        $this->add_responsive_control(
			'espaciado_columnas',
			array(
				'label'      => esc_html__( 'Espaciado entre Columnas', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 15,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupons-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

        $this->add_responsive_control(
			'espaciado_filas',
			array(
				'label'      => esc_html__( 'Espaciado entre Filas', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 15,
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupons-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

        $this->end_controls_section();

        $this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Tarjeta de Cupón', 'wp-cupon-whatsapp' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			array(
				'name'     => 'card_background',
				'label'    => esc_html__( 'Fondo', 'wp-cupon-whatsapp' ),
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .wpcw-coupon-card',
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .wpcw-coupon-card',
			)
		);

        $this->add_responsive_control(
			'card_border_radius',
			array(
				'label'      => esc_html__( 'Radio del Borde', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .wpcw-coupon-card',
			)
		);

        $this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
        $this->end_controls_section();

        $this->start_controls_section(
			'section_style_image',
			array(
				'label'     => esc_html__( 'Imagen del Cupón', 'wp-cupon-whatsapp' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'mostrar_imagen' => 'yes',
                ),
			)
		);

        $this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Radio del Borde', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-image-wrapper img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .wpcw-coupon-image-placeholder' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->add_responsive_control(
			'image_height',
			array(
				'label'      => esc_html__( 'Altura', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
                'range'      => array(
					'px' => array(
						'min' => 50,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-image-wrapper img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                    '{{WRAPPER}} .wpcw-coupon-image-placeholder' => 'height: {{SIZE}}{{UNIT}}; display: flex; align-items: center; justify-content: center;',
				),
			)
		);

        $this->end_controls_section();

        $this->start_controls_section(
			'section_style_title',
			array(
				'label' => esc_html__( 'Título del Cupón', 'wp-cupon-whatsapp' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

        $this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-coupon-title' => 'color: {{VALUE}};',
				),
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .wpcw-coupon-title',
			)
		);

        $this->add_responsive_control(
			'title_margin',
			array(
				'label'      => esc_html__( 'Margen', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->end_controls_section();

        $this->start_controls_section(
			'section_style_code',
			array(
				'label'     => esc_html__( 'Código del Cupón', 'wp-cupon-whatsapp' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'mostrar_codigo' => 'yes',
                ),
			)
		);

        $this->add_control(
			'code_color',
			array(
				'label'     => esc_html__( 'Color del Texto', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-coupon-code' => 'color: {{VALUE}};',
				),
			)
		);
        $this->add_control(
			'code_strong_color',
			array(
				'label'     => esc_html__( 'Color del Código (Negrita)', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-coupon-code strong' => 'color: {{VALUE}};',
				),
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'code_typography',
				'selector' => '{{WRAPPER}} .wpcw-coupon-code',
			)
		);
        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'code_strong_typography',
                'label'    => esc_html__( 'Tipografía del Código (Negrita)', 'wp-cupon-whatsapp' ),
				'selector' => '{{WRAPPER}} .wpcw-coupon-code strong',
			)
		);

        $this->add_control(
			'code_background_color',
			array(
				'label'     => esc_html__( 'Color de Fondo (Solo Código)', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-coupon-code strong' => 'background-color: {{VALUE}};',
				),
			)
		);

        $this->add_responsive_control(
			'code_padding',
			array(
				'label'      => esc_html__( 'Padding (Solo Código)', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-code strong' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->add_responsive_control(
			'code_border_radius',
			array(
				'label'      => esc_html__( 'Radio del Borde (Solo Código)', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-code strong' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->add_responsive_control(
			'code_margin',
			array(
				'label'      => esc_html__( 'Margen (Bloque de Código)', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-code' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->end_controls_section();

        $this->start_controls_section(
			'section_style_description',
			array(
				'label'     => esc_html__( 'Descripción del Cupón', 'wp-cupon-whatsapp' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'mostrar_descripcion' => 'yes',
                ),
			)
		);

        $this->add_control(
			'description_color',
			array(
				'label'     => esc_html__( 'Color', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-coupon-description' => 'color: {{VALUE}};',
				),
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .wpcw-coupon-description',
			)
		);

        $this->add_responsive_control(
			'description_margin',
			array(
				'label'      => esc_html__( 'Margen', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-coupon-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
        $this->end_controls_section();

        $this->start_controls_section(
			'section_style_button',
			array(
				'label' => esc_html__( 'Botón de Canje', 'wp-cupon-whatsapp' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .wpcw-canjear-cupon-btn',
			)
		);

        $this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
        $this->add_responsive_control(
			'button_margin',
			array(
				'label'      => esc_html__( 'Margen', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .wpcw-canjear-cupon-btn',
			)
		);

        $this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Radio del Borde', 'wp-cupon-whatsapp' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

        $this->start_controls_tabs( 'button_tabs_style' );

		$this->start_controls_tab(
			'button_tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'wp-cupon-whatsapp' ),
			)
		);

        $this->add_control(
			'button_color_normal',
			array(
				'label'     => esc_html__( 'Color del Texto', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn' => 'color: {{VALUE}};',
				),
			)
		);

        $this->add_control(
			'button_bg_color_normal',
			array(
				'label'     => esc_html__( 'Color de Fondo', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
			'button_tab_hover',
			array(
				'label' => esc_html__( 'Hover', 'wp-cupon-whatsapp' ),
			)
		);

        $this->add_control(
			'button_color_hover',
			array(
				'label'     => esc_html__( 'Color del Texto', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn:hover' => 'color: {{VALUE}};',
				),
			)
		);

        $this->add_control(
			'button_bg_color_hover',
			array(
				'label'     => esc_html__( 'Color de Fondo', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

        $this->add_control(
			'button_border_color_hover',
			array(
				'label'     => esc_html__( 'Color del Borde', 'wp-cupon-whatsapp' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .wpcw-canjear-cupon-btn:hover' => 'border-color: {{VALUE}};',
				),
                'condition' => array(
                    'button_border_border!' => '', // Only if border type is set
                ),
			)
		);

        $this->end_controls_tab();
		$this->end_controls_tabs();
        $this->end_controls_section();
	}

	/**
	 * Render Lista de Cupones widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.1.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

        if ( 'mis_cupones' === $settings['tipo_cupon'] && ! is_user_logged_in() ) {
            if ( ! empty( $settings['mensaje_no_logueado'] ) ) {
                echo '<p class="wpcw-login-required">' . esc_html( $settings['mensaje_no_logueado'] );
                if ( ! empty( $settings['enlace_login']['url'] ) ) {
                    $this->add_link_attributes( 'login_link', $settings['enlace_login'] );
                    echo ' <a ' . $this->get_render_attribute_string( 'login_link' ) . '>' . esc_html__( 'Inicia sesión aquí.', 'wp-cupon-whatsapp' ) . '</a>';
                }
                echo '</p>';
            }
            return;
        }

        $query_args = array(
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
            'posts_per_page' => $settings['numero_cupones'],
            'orderby'        => $settings['ordenar_por'],
            'order'          => $settings['orden'],
            'meta_query'     => array(
                'relation' => 'AND',
            ),
        );

        if ( 'mis_cupones' === $settings['tipo_cupon'] ) {
            $query_args['meta_query'][] = array(
                'key'     => '_wpcw_is_loyalty_coupon',
                'value'   => 'yes',
                'compare' => '=',
            );
            // Aquí podrías añadir más lógica para filtrar cupones de lealtad específicos del usuario si fuera necesario.
        } else { // publicos
            $query_args['meta_query'][] = array(
                'key'     => '_wpcw_is_public_coupon',
                'value'   => 'yes',
                'compare' => '=',
            );
        }

        $coupons_query = new \WP_Query( $query_args );

        if ( $coupons_query->have_posts() ) :
            echo '<div class="wpcw-coupons-grid-wrapper">'; // Wrapper for potential pagination or messages
            echo '<div class="wpcw-coupons-grid">'; // Grid container

            while ( $coupons_query->have_posts() ) :
                $coupons_query->the_post();
                $coupon_id    = get_the_ID();
                $coupon_title = get_the_title();
                $coupon_code  = get_the_title(); // WooCommerce coupon code is the post title

                $coupon_description_full = get_the_excerpt();
                if ( empty( $coupon_description_full ) ) {
                    $coupon_post_content     = get_the_content();
                    $coupon_description_full = $coupon_post_content;
                }

                $coupon_description = '';
                if ( 'yes' === $settings['mostrar_descripcion'] && ! empty( $coupon_description_full ) ) {
                    $coupon_description = wp_trim_words( $coupon_description_full, $settings['longitud_descripcion'], '...' );
                    if ( empty( $coupon_description ) && ! empty( $coupon_description_full ) ) {
                        $coupon_description = $coupon_description_full;
                    }
                }

                $coupon_image_url = '';
                if ( 'yes' === $settings['mostrar_imagen'] ) {
                    $coupon_image_id = get_post_meta( $coupon_id, '_wpcw_coupon_image_id', true );
                    if ( $coupon_image_id ) {
                        $coupon_image_url = wp_get_attachment_image_url( $coupon_image_id, 'medium' );
                    }
                }

                // Render the coupon card using a structure similar to the template
                ?>
                <div class="wpcw-coupon-card" id="wpcw-coupon-<?php echo esc_attr( $coupon_id ); ?>">
                    <?php if ( 'yes' === $settings['mostrar_imagen'] ) : ?>
                    <div class="wpcw-coupon-image-wrapper">
                        <?php if ( ! empty( $coupon_image_url ) ) : ?>
                            <img src="<?php echo esc_url( $coupon_image_url ); ?>" alt="<?php echo esc_attr( $coupon_title ); ?>" class="wpcw-coupon-image"/>
                        <?php else : ?>
                            <div class="wpcw-coupon-image-placeholder">
                                <span><?php esc_html_e( 'Imagen no disponible', 'wp-cupon-whatsapp' ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="wpcw-coupon-details">
                        <h3 class="wpcw-coupon-title"><?php echo esc_html( $coupon_title ); ?></h3>

                        <?php if ( 'yes' === $settings['mostrar_codigo'] && ! empty( $coupon_code ) ) : ?>
                            <p class="wpcw-coupon-code">
                                <?php esc_html_e( 'Código:', 'wp-cupon-whatsapp' ); ?>
                                <strong><?php echo esc_html( $coupon_code ); ?></strong>
                            </p>
                        <?php endif; ?>

                        <?php if ( 'yes' === $settings['mostrar_descripcion'] && ! empty( $coupon_description ) ) : ?>
                        <div class="wpcw-coupon-description">
                            <?php echo wp_kses_post( wpautop( $coupon_description ) ); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="wpcw-coupon-actions">
                        <button type="button" class="wpcw-canjear-cupon-btn elementor-button" data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>">
                            <?php echo esc_html( $settings['texto_boton_canje'] ); ?>
                        </button>
                    </div>
                </div>
                <?php
            endwhile;

            echo '</div>'; // End .wpcw-coupons-grid
            echo '</div>'; // End .wpcw-coupons-grid-wrapper
            wp_reset_postdata();
        else :
            echo '<p class="wpcw-no-coupons-message">' . esc_html( $settings['mensaje_no_cupones'] ) . '</p>';
        endif;
	}

	/**
	 * Render Lista de Cupones widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.1.0
	 * @access protected
	 */
	protected function _content_template() {
        ?>
        <#
        if ( 'mis_cupones' === settings.tipo_cupon && ! elementor.config.user.id ) { // Approx check for logged out
            if ( settings.mensaje_no_logueado ) {
                #>
                <p class="wpcw-login-required">{{{ settings.mensaje_no_logueado }}}
                <# if ( settings.enlace_login.url ) { #>
                    <a href="{{ settings.enlace_login.url }}">
                        <?php esc_html_e( 'Inicia sesión aquí.', 'wp-cupon-whatsapp' ); ?>
                    </a>
                <# } #>
                </p>
                <#
            }
            return;
        }

        // In the editor, we can't execute PHP WP_Query.
        // We'll show a placeholder or a message.
        // For a better preview, you might use Elementor's JS hooks to fetch data via AJAX.
        #>
        <div class="wpcw-coupons-grid-wrapper">
            <div class="wpcw-coupons-grid">
                <#
                var mockCoupons = 3; // Show 3 mock coupons in editor
                if ( settings.numero_cupones != -1 && settings.numero_cupones < mockCoupons) {
                    mockCoupons = settings.numero_cupones;
                }
                if ( settings.numero_cupones == 0) mockCoupons = 0;


                if ( mockCoupons > 0 ) {
                    for ( var i = 0; i < mockCoupons; i++ ) {
                        var coupon_id = 'mock_id_' + i;
                        var coupon_title = '<?php esc_html_e( 'Título del Cupón de Ejemplo', 'wp-cupon-whatsapp' ); ?> ' + (i+1);
                        var coupon_code = '<?php esc_html_e( 'EJEMPLO', 'wp-cupon-whatsapp' ); ?>' + (i+1);
                        var coupon_description_full = '<?php esc_html_e( 'Esta es una breve descripción del cupón de ejemplo para mostrar cómo se verá. Puedes personalizar esto.', 'wp-cupon-whatsapp' ); ?>';
                        var coupon_description = '';
                        if ( 'yes' === settings.mostrar_descripcion && coupon_description_full ) {
                            coupon_description = coupon_description_full.split(' ').slice(0, settings.longitud_descripcion).join(' ') + '...';
                        }
                        var coupon_image_url = ''; // Placeholder or a default image URL
                        if ( 'yes' === settings.mostrar_imagen ) {
                            coupon_image_url = '<?php echo \Elementor\Utils::get_placeholder_image_src(); ?>';
                        }
                #>
                <div class="wpcw-coupon-card" id="wpcw-coupon-{{ coupon_id }}">
                    <# if ( 'yes' === settings.mostrar_imagen ) { #>
                    <div class="wpcw-coupon-image-wrapper">
                        <# if ( coupon_image_url ) { #>
                            <img src="{{ coupon_image_url }}" alt="{{ coupon_title }}" class="wpcw-coupon-image"/>
                        <# } else { #>
                            <div class="wpcw-coupon-image-placeholder">
                                <span><?php esc_html_e( 'Imagen no disponible', 'wp-cupon-whatsapp' ); ?></span>
                            </div>
                        <# } #>
                    </div>
                    <# } #>

                    <div class="wpcw-coupon-details">
                        <h3 class="wpcw-coupon-title">{{{ coupon_title }}}</h3>

                        <# if ( 'yes' === settings.mostrar_codigo && coupon_code ) { #>
                            <p class="wpcw-coupon-code">
                                <?php esc_html_e( 'Código:', 'wp-cupon-whatsapp' ); ?>
                                <strong>{{{ coupon_code }}}</strong>
                            </p>
                        <# } #>

                        <# if ( 'yes' === settings.mostrar_descripcion && coupon_description ) { #>
                        <div class="wpcw-coupon-description">
                            {{{ coupon_description }}}
                        </div>
                        <# } #>
                    </div>

                    <div class="wpcw-coupon-actions">
                        <button type="button" class="wpcw-canjear-cupon-btn elementor-button" data-coupon-id="{{ coupon_id }}">
                            {{{ settings.texto_boton_canje }}}
                        </button>
                    </div>
                </div>
                <#
                    } // end for
                } else { #>
                    <p class="wpcw-no-coupons-message">{{{ settings.mensaje_no_cupones }}}</p>
                <# } #>
            </div>
        </div>
        <?php
	}

    /**
     * Add some basic CSS for the grid layout.
     * More advanced styling should be handled by Elementor controls.
     */
    public function __construct( $data = array(), $args = null ) {
        parent::__construct( $data, $args );
        // Register a general style for the grid, if not already done by another instance.
        // This is a simple way, could be refined.
        // wp_register_style( 'wpcw-elementor-grid', false ); // Bogus handle
        // wp_add_inline_style( 'wpcw-elementor-grid', $this->get_grid_styles() );
        // add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_widget_styles']);

        // The styles are now primarily handled by selector-based controls.
        // However, a base style for the grid display itself is good.
    }
    /*
    public function enqueue_widget_styles() {
        // Inline styles are better handled by Elementor's CSS generation based on controls.
        // If truly global styles for the widget are needed and not achievable via controls,
        // they can be enqueued here or in the main addon class.
        $css = ".wpcw-coupons-grid { display: grid; gap: 15px; }"; // Default gap
        // This is just an example, real styling is done via controls.
        // wp_add_inline_style( 'elementor-frontend', $css ); // Add to a common handle
    }
    */

    // Fallback if the `coupon-card.php` template is not directly usable
    // We'll define the structure directly in render() and _content_template()
}

// The file should not have a closing PHP tag if it's all PHP
//
?>
// Removed closing tag
