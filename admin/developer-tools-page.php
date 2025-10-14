<?php
/**
 * WPCW - Developer Tools Page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Adds the Developer Tools submenu page.
 * Always available for administrators to seed/clear test data.
 */
function wpcw_add_developer_tools_menu() {
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
    $notice_type = 'info';

    if ( isset( $_POST['wpcw_action'] ) && isset( $_POST['wpcw_nonce_dev'] ) && wp_verify_nonce( $_POST['wpcw_nonce_dev'], 'wpcw_dev_tools_nonce' ) ) {
        $action = sanitize_key( $_POST['wpcw_action'] );
        if ( method_exists( 'WPCW_Seeder', $action ) ) {
            $result = WPCW_Seeder::$action();
            $notice = $result['message'];
            $notice_type = ( strpos($action, 'clear') !== false ) ? 'warning' : 'success';
        }
    }

    // Get current data statistics
    global $wpdb;
    $stats = [
        'institutions' => wp_count_posts('wpcw_institution')->publish,
        'businesses' => wp_count_posts('wpcw_business')->publish,
        'convenios' => wp_count_posts('wpcw_convenio')->publish,
        'coupons' => wp_count_posts('shop_coupon')->publish,
        'beneficiaries' => count(get_users(['role' => 'customer'])),
        'business_owners' => count(get_users(['role' => 'wpcw_business_owner'])),
        'business_staff' => count(get_users(['role' => 'wpcw_employee'])),
        'institution_users' => count(get_users(['role' => 'wpcw_institution_manager'])),
        'redemptions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wpcw_canjes"),
    ];

    ?>
    <div class="wrap">
        <h1>🔧 <?php _e( 'Herramientas de Desarrollo', 'wp-cupon-whatsapp' ); ?></h1>

        <?php if ( $notice ) : ?>
            <div class="notice notice-<?php echo esc_attr($notice_type); ?> is-dismissible">
                <p><?php echo esc_html( $notice ); ?></p>
            </div>
        <?php endif; ?>

        <div class="notice notice-warning">
            <p><strong>⚠️ <?php _e( 'ADVERTENCIA:', 'wp-cupon-whatsapp' ); ?></strong> <?php _e( 'Estas herramientas son solo para entornos de desarrollo. Pueden añadir o eliminar grandes cantidades de datos de su base de datos.', 'wp-cupon-whatsapp' ); ?></p>
        </div>

        <!-- Estadísticas Actuales -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span>📊 Estado Actual de Datos</span></h2>
            <div class="inside">
                <table class="widefat" style="max-width: 600px;">
                    <thead>
                        <tr>
                            <th>Tipo de Dato</th>
                            <th style="text-align: center;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>🏛️ Instituciones</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['institutions']); ?></strong></td>
                        </tr>
                        <tr class="alternate">
                            <td>🏪 Comercios</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['businesses']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>🤝 Convenios</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['convenios']); ?></strong></td>
                        </tr>
                        <tr class="alternate">
                            <td>🎫 Cupones</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['coupons']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>👥 Beneficiarios (Customers)</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['beneficiaries']); ?></strong></td>
                        </tr>
                        <tr class="alternate">
                            <td>👔 Dueños de Comercio</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['business_owners']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>🛒 Vendedores/Empleados</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['business_staff']); ?></strong></td>
                        </tr>
                        <tr class="alternate">
                            <td>🏛️ Usuarios Institución</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['institution_users']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>📋 Canjes Registrados</td>
                            <td style="text-align: center;"><strong><?php echo esc_html($stats['redemptions']); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Generador de Datos -->
        <div id="wpcw-seeder-tools" class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span>🌱 <?php _e( 'Generador de Datos de Ejemplo (Seeder)', 'wp-cupon-whatsapp' ); ?></span></h2>
            <div class="inside">
                <p><?php _e( 'Usa estos botones para poblar la base de datos con datos de ejemplo para realizar pruebas.', 'wp-cupon-whatsapp' ); ?></p>

                <div class="wpcw-seed-info" style="background: #f0f9ff; border-left: 4px solid #0073aa; padding: 15px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0;">ℹ️ Lo que se generará:</h4>
                    <ul style="margin-left: 20px;">
                        <li>✅ <strong>3 Instituciones</strong> (Municipalidad, Gobierno Provincial, Universidad, etc.)</li>
                        <li>✅ <strong>10 Comercios</strong> (Restaurantes, Farmacias, Supermercados, etc.)</li>
                        <li>✅ <strong>8 Convenios</strong> activos entre instituciones y comercios</li>
                        <li>✅ <strong>30 Cupones WooCommerce</strong> con descuentos del 10-50%</li>
                        <li>✅ <strong>20 Beneficiarios</strong> (usuarios con rol customer)</li>
                        <li>✅ <strong>5 Dueños de Comercio</strong> (rol wpcw_business_owner - administran el comercio)</li>
                        <li>✅ <strong>15 Vendedores/Empleados</strong> (rol wpcw_employee - <strong>validan canjes</strong> en punto de venta)</li>
                        <li>✅ <strong>3 Usuarios de Institución</strong> (rol wpcw_institution_manager)</li>
                        <li>✅ <strong>50 Canjes</strong> con diferentes estados (pending, approved, used, rejected)</li>
                    </ul>
                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 15px;">
                        <strong>👥 Tipos de usuarios:</strong>
                        <ul style="margin: 5px 0 5px 20px;">
                            <li><strong>Beneficiarios:</strong> Clientes que reciben y canjean cupones</li>
                            <li><strong>Dueños de Comercio:</strong> Propietarios que gestionan su negocio</li>
                            <li><strong>Vendedores/Empleados:</strong> <span style="color: #d63031;">⚡ Estos validan los canjes en el punto de venta</span></li>
                            <li><strong>Usuarios Institución:</strong> Administradores del sistema de beneficios</li>
                        </ul>
                    </div>
                    <p style="margin: 15px 0 0 0;"><strong>📧 Emails:</strong> <code>farfancris@gmail.com</code>, <code>criis2709@gmail.com</code></p>
                    <p style="margin: 5px 0 0 0;"><strong>📱 Teléfonos:</strong> <code>+5493883349901</code>, <code>+5493885214566</code></p>
                    <p style="margin: 5px 0 0 0;"><strong>🔐 Contraseñas:</strong> <code>Beneficiario123!</code> / <code>DuenoComercio123!</code> / <code>Vendedor123!</code> / <code>Institucion123!</code></p>
                </div>

                <form method="post">
                    <?php wp_nonce_field( 'wpcw_dev_tools_nonce', 'wpcw_nonce_dev' ); ?>

                    <p>
                        <button type="submit" name="wpcw_action" value="seed_all" class="button button-primary button-hero">
                            <span class="dashicons dashicons-database-add" style="margin-top: 8px;"></span>
                            <?php _e( '🌱 Generar Ecosistema Completo', 'wp-cupon-whatsapp' ); ?>
                        </button>
                    </p>

                    <hr style="margin: 30px 0;">

                    <h3 style="color: #dc3545;">🗑️ Zona de Peligro</h3>
                    <p><?php _e( 'Esta acción eliminará TODOS los datos de ejemplo generados por esta herramienta.', 'wp-cupon-whatsapp' ); ?></p>

                    <p>
                        <button type="submit" name="wpcw_action" value="clear_all" class="button button-danger button-large"
                                onclick="return confirm('⚠️ ¿Estás ABSOLUTAMENTE SEGURO?\n\nEsta acción eliminará:\n• Todos los posts marcados como datos de ejemplo\n• Todos los usuarios de ejemplo\n• Todos los canjes de ejemplo\n\nEsta acción NO se puede deshacer.');">
                            <span class="dashicons dashicons-trash" style="margin-top: 6px;"></span>
                            <?php _e( 'BORRAR TODOS LOS DATOS DE EJEMPLO', 'wp-cupon-whatsapp' ); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span>📚 Información de Uso</span></h2>
            <div class="inside">
                <h4>Acceso a esta página:</h4>
                <p>Esta página está disponible para todos los administradores en <strong>WP Cupón WhatsApp → Herramientas DEV</strong></p>

                <h4>Datos generados - Características:</h4>
                <ul>
                    <li>✅ <strong>Contraseñas predecibles</strong> para testing (ver arriba)</li>
                    <li>✅ <strong>Emails reales</strong> con aliases de Gmail (+sufijo) para evitar duplicados</li>
                    <li>✅ <strong>Metadatos especiales</strong> en todos los elementos para identificación y eliminación</li>
                    <li>✅ <strong>Fechas realistas</strong> - canjes distribuidos en los últimos 90 días</li>
                    <li>✅ <strong>Distribución temporal</strong>: 16% últimos 7 días, 34% últimos 30 días</li>
                    <li>✅ <strong>Horarios comerciales</strong>: Canjes entre 8am-8pm para estadísticas realistas</li>
                </ul>

                <h4>Casos de uso:</h4>
                <ul>
                    <li>🧪 <strong>Testing</strong> - Probar funcionalidad sin afectar datos reales</li>
                    <li>📊 <strong>Demos</strong> - Mostrar el sistema con datos completos</li>
                    <li>🎓 <strong>Capacitación</strong> - Entrenar usuarios con datos de ejemplo</li>
                    <li>🔍 <strong>Desarrollo</strong> - Debuggear con datasets consistentes</li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .button-danger {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }
        .button-danger:hover {
            background: #c82333;
            border-color: #bd2130;
            color: white;
        }
        .button-hero {
            font-size: 16px !important;
            height: auto !important;
            padding: 12px 24px !important;
        }
    </style>
    <?php
}
