<?php
/**
 * WPCW - Developer Tools Page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Adds the Developer Tools submenu page, only if debug mode is on.
 */
function wpcw_add_developer_tools_menu() {
    if ( ! defined( 'WPCW_DEBUG_MODE' ) || ! WPCW_DEBUG_MODE ) {
        return;
    }

    add_submenu_page(
        'wpcw-main-dashboard',
        __( 'Herramientas de Desarrollo', 'wp-cupon-whatsapp' ),
        __( 'Herramientas DEV', 'wp-cupon-whatsapp' ),
        'manage_options',
        'wpcw-dev-tools',
        'wpcw_render_developer_tools_page',
        99
    );
}
add_action( 'admin_menu', 'wpcw_add_developer_tools_menu', 99 );

/**
 * Renders the content of the developer tools page.
 */
function wpcw_render_developer_tools_page() {
    $notice = '';
    if ( isset( $_POST['wpcw_action'] ) && isset( $_POST['wpcw_nonce_dev'] ) && wp_verify_nonce( $_POST['wpcw_nonce_dev'], 'wpcw_dev_tools_nonce' ) ) {
        $action = sanitize_key( $_POST['wpcw_action'] );
        if ( method_exists( 'WPCW_Seeder', $action ) ) {
            $result = WPCW_Seeder::$action();
            $notice = $result['message'];
        }
    }

    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-hammer"></span> <?php _e( 'Herramientas de Desarrollo', 'wp-cupon-whatsapp' ); ?></h1>
        <?php if ( $notice ) : ?>
            <div class="notice notice-info is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
        <?php endif; ?>
        <div class="notice notice-warning">
            <p><strong><?php _e( 'ADVERTENCIA:', 'wp-cupon-whatsapp' ); ?></strong> <?php _e( 'Estas herramientas son solo para entornos de desarrollo. Pueden añadir o eliminar grandes cantidades de datos de su base de datos.', 'wp-cupon-whatsapp' ); ?></p>
        </div>

        <div id="wpcw-seeder-tools" class="postbox">
            <h2 class="hndle"><span><?php _e( 'Generador de Datos de Ejemplo (Seeder)', 'wp-cupon-whatsapp' ); ?></span></h2>
            <div class="inside">
                <p><?php _e( 'Usa estos botones para poblar la base de datos con datos de ejemplo para realizar pruebas.', 'wp-cupon-whatsapp' ); ?></p>
                <form method="post">
                    <?php wp_nonce_field( 'wpcw_dev_tools_nonce', 'wpcw_nonce_dev' ); ?>
                    <p>
                        <button type="submit" name="wpcw_action" value="seed_all" class="button button-primary"><?php _e( 'Poblar todo el Ecosistema', 'wp-cupon-whatsapp' ); ?></button>
                    </p>
                    <hr>
                    <p>
                        <button type="submit" name="wpcw_action" value="clear_all" class="button button-danger" onclick="return confirm('<?php _e( '¿Estás SEGURO de que quieres borrar TODOS los datos de ejemplo (convenios, cupones, beneficiarios, etc.)?', 'wp-cupon-whatsapp' ); ?>');"><?php _e( 'BORRAR TODOS LOS DATOS DE EJEMPLO', 'wp-cupon-whatsapp' ); ?></button>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <style>.button-danger { background: #dc3545; color: white; border-color: #dc3545; }</style>
    <?php
}
