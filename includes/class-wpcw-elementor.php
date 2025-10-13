<?php
/**
 * WP Cupón WhatsApp - Elementor Integration Class
 *
 * Handles Elementor widgets and integration
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Elementor class
 */
class WPCW_Elementor {

    /**
     * Initialize Elementor integration
     */
    public static function init() {
        add_action( 'elementor/widgets/widgets_registered', array( __CLASS__, 'register_widgets' ) );
        add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'add_elementor_widget_categories' ) );
        add_action( 'elementor/frontend/after_enqueue_styles', array( __CLASS__, 'enqueue_elementor_styles' ) );
    }

    /**
     * Register Elementor widgets
     */
    public static function register_widgets() {
        // Include widget files
        require_once WPCW_PLUGIN_DIR . 'includes/widgets/class-wpcw-adhesion-form-widget.php';
        require_once WPCW_PLUGIN_DIR . 'includes/widgets/class-wpcw-coupons-list-widget.php';
        require_once WPCW_PLUGIN_DIR . 'includes/widgets/class-wpcw-user-dashboard-widget.php';

        // Register widgets
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WPCW_Adhesion_Form_Widget() );
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WPCW_Coupons_List_Widget() );
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WPCW_User_Dashboard_Widget() );
    }

    /**
     * Add Elementor widget categories
     */
    public static function add_elementor_widget_categories() {
        \Elementor\Plugin::$instance->elements_manager->add_category(
            'wpcw-widgets',
            array(
                'title' => __( 'WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
                'icon' => 'fa fa-ticket-alt',
            )
        );
    }

    /**
     * Enqueue Elementor styles
     */
    public static function enqueue_elementor_styles() {
        wp_enqueue_style(
            'wpcw-elementor',
            WPCW_PLUGIN_URL . 'public/css/elementor.css',
            array(),
            WPCW_VERSION
        );
    }
}

// Initialize Elementor integration
if ( did_action( 'elementor/loaded' ) ) {
    WPCW_Elementor::init();
}