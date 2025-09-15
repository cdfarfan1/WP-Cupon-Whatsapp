<?php
/**
 * PHPUnit bootstrap file para WP Cupón WhatsApp
 *
 * @package WP_Cupon_WhatsApp
 */

// Composer autoloader.
require_once dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	// Cargar WooCommerce primero si está disponible
	if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
		require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
	}
	
	// Cargar nuestro plugin
	require dirname( dirname( __FILE__ ) ) . '/wp-cupon-whatsapp.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Configurar constantes de prueba
 */
function _setup_test_constants() {
	// Definir constantes necesarias para las pruebas
	if ( ! defined( 'WPCW_PLUGIN_DIR' ) ) {
		define( 'WPCW_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) . '/' );
	}
	
	if ( ! defined( 'WPCW_PLUGIN_URL' ) ) {
		define( 'WPCW_PLUGIN_URL', 'http://example.org/wp-content/plugins/wp-cupon-whatsapp/' );
	}
	
	if ( ! defined( 'WPCW_VERSION' ) ) {
		define( 'WPCW_VERSION', '1.4.0' );
	}
	
	// Constantes de base de datos para pruebas
	if ( ! defined( 'WPCW_CANJES_TABLE_NAME' ) ) {
		global $wpdb;
		define( 'WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes' );
	}
}
tests_add_filter( 'init', '_setup_test_constants' );

/**
 * Configurar datos de prueba
 */
function _setup_test_data() {
	// Crear tablas personalizadas si es necesario
	if ( function_exists( 'wpcw_create_tables' ) ) {
		wpcw_create_tables();
	}
	
	// Configurar roles y capacidades
	if ( function_exists( 'wpcw_setup_roles' ) ) {
		wpcw_setup_roles();
	}
}
tests_add_filter( 'wp_loaded', '_setup_test_data' );

// Start up the WP testing environment.
require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

/**
 * Clase base para pruebas del plugin
 */
abstract class WPCW_UnitTestCase extends WP_UnitTestCase {
	
	/**
	 * Configuración antes de cada prueba
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Limpiar datos de prueba
		$this->clean_up_global_scope();
		
		// Configurar usuario administrador para pruebas
		$this->set_current_user_as_admin();
	}
	
	/**
	 * Limpieza después de cada prueba
	 */
	public function tearDown(): void {
		// Limpiar datos de prueba
		$this->clean_test_data();
		
		parent::tearDown();
	}
	
	/**
	 * Establecer usuario actual como administrador
	 */
	protected function set_current_user_as_admin() {
		$user_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );
		return $user_id;
	}
	
	/**
	 * Crear un cupón de prueba
	 */
	protected function create_test_coupon( $args = array() ) {
		$defaults = array(
			'post_title'   => 'Test Coupon',
			'post_type'    => 'shop_coupon',
			'post_status'  => 'publish',
			'post_content' => 'Test coupon description',
		);
		
		$args = wp_parse_args( $args, $defaults );
		$coupon_id = wp_insert_post( $args );
		
		// Configurar meta datos del cupón
		update_post_meta( $coupon_id, '_wpcw_enabled', '1' );
		update_post_meta( $coupon_id, 'discount_type', 'percent' );
		update_post_meta( $coupon_id, 'coupon_amount', '10' );
		
		return $coupon_id;
	}
	
	/**
	 * Crear un comercio de prueba
	 */
	protected function create_test_business( $args = array() ) {
		$defaults = array(
			'post_title'  => 'Test Business',
			'post_type'   => 'wpcw_business',
			'post_status' => 'publish',
		);
		
		$args = wp_parse_args( $args, $defaults );
		$business_id = wp_insert_post( $args );
		
		// Configurar meta datos del comercio
		update_post_meta( $business_id, '_wpcw_business_whatsapp', '5491123456789' );
		update_post_meta( $business_id, '_wpcw_business_email', 'test@business.com' );
		
		return $business_id;
	}
	
	/**
	 * Limpiar datos de prueba
	 */
	protected function clean_test_data() {
		global $wpdb;
		
		// Limpiar tabla de canjes
		if ( defined( 'WPCW_CANJES_TABLE_NAME' ) ) {
			$wpdb->query( "TRUNCATE TABLE " . WPCW_CANJES_TABLE_NAME );
		}
		
		// Limpiar posts de prueba
		$test_posts = get_posts( array(
			'post_type'   => array( 'shop_coupon', 'wpcw_business' ),
			'post_status' => 'any',
			'numberposts' => -1,
		) );
		
		foreach ( $test_posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}
	
	/**
	 * Limpiar scope global
	 */
	protected function clean_up_global_scope() {
		$_GET = array();
		$_POST = array();
		$_REQUEST = array();
	}
	
	/**
	 * Simular petición AJAX
	 */
	protected function make_ajax_request( $action, $data = array() ) {
		$_POST['action'] = $action;
		$_POST = array_merge( $_POST, $data );
		$_REQUEST = $_POST;
		
		try {
			do_action( 'wp_ajax_' . $action );
		} catch ( WPAjaxDieContinueException $e ) {
			// Capturar la excepción normal de AJAX
		}
	}
}