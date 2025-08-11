<?php
/**
 * Setup Wizard for WP Cupón WhatsApp
 * 
 * Guía al usuario a través de la configuración inicial del plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Mostrar el asistente de configuración inicial
 */
function wpcw_show_setup_wizard() {
    // Solo mostrar si es la primera vez que se activa
    if (!get_option('wpcw_setup_wizard_completed', false)) {
        add_action('admin_notices', 'wpcw_setup_wizard_notice');
    }
}
add_action('admin_init', 'wpcw_show_setup_wizard');

/**
 * Mostrar aviso del asistente de configuración
 */
function wpcw_setup_wizard_notice() {
    $screen = get_current_screen();
    
    // Solo mostrar en páginas relevantes
    if (!in_array($screen->id, ['dashboard', 'plugins', 'toplevel_page_wpcw-dashboard'])) {
        return;
    }
    
    ?>
    <div class="notice notice-info is-dismissible wpcw-setup-notice" style="border-left-color: #0073aa; padding: 15px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 24px;">🎉</div>
            <div>
                <h3 style="margin: 0 0 10px 0; color: #0073aa;">¡Bienvenido a WP Cupón WhatsApp!</h3>
                <p style="margin: 0 0 10px 0;">El plugin se ha instalado correctamente. Te recomendamos completar la configuración inicial para aprovechar todas las funcionalidades.</p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="<?php echo admin_url('admin.php?page=wpcw-setup-wizard'); ?>" class="button button-primary">
                        🚀 Iniciar Configuración Guiada
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wpcw-dashboard'); ?>" class="button button-secondary">
                        📋 Ir al Dashboard
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wpcw-settings'); ?>" class="button button-secondary">
                        ⚙️ Configuración Manual
                    </a>
                    <button type="button" class="button button-link" onclick="wpcwDismissSetupNotice()" style="color: #666;">
                        ❌ No mostrar más
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function wpcwDismissSetupNotice() {
        if (confirm('¿Estás seguro de que no quieres ver más este aviso? Siempre puedes acceder a la configuración desde el menú del plugin.')) {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=wpcw_dismiss_setup_notice&nonce=<?php echo wp_create_nonce('wpcw_dismiss_setup'); ?>'
            }).then(() => {
                document.querySelector('.wpcw-setup-notice').style.display = 'none';
            });
        }
    }
    </script>
    <?php
}

/**
 * Manejar la dismissal del aviso
 */
function wpcw_dismiss_setup_notice() {
    if (!wp_verify_nonce($_POST['nonce'], 'wpcw_dismiss_setup')) {
        wp_die('Nonce verification failed');
    }
    
    update_option('wpcw_setup_wizard_completed', true);
    wp_die('OK');
}
add_action('wp_ajax_wpcw_dismiss_setup_notice', 'wpcw_dismiss_setup_notice');

/**
 * Registrar la página del asistente de configuración
 */
function wpcw_register_setup_wizard_page() {
    add_submenu_page(
        null, // No parent (página oculta)
        'Configuración Inicial - WP Cupón WhatsApp',
        'Setup Wizard',
        'manage_options',
        'wpcw-setup-wizard',
        'wpcw_render_setup_wizard_page'
    );
}
add_action('admin_menu', 'wpcw_register_setup_wizard_page');

/**
 * Renderizar la página del asistente de configuración
 */
function wpcw_render_setup_wizard_page() {
    $step = isset($_GET['step']) ? intval($_GET['step']) : 1;
    $max_steps = 4;
    
    // Procesar formularios
    if ($_POST && wp_verify_nonce($_POST['wpcw_setup_nonce'], 'wpcw_setup_wizard')) {
        wpcw_process_setup_step($step);
        
        // Redirigir al siguiente paso
        if ($step < $max_steps) {
            wp_redirect(admin_url('admin.php?page=wpcw-setup-wizard&step=' . ($step + 1)));
            exit;
        } else {
            // Completar el wizard
            update_option('wpcw_setup_wizard_completed', true);
            wp_redirect(admin_url('admin.php?page=wpcw-dashboard&setup=completed'));
            exit;
        }
    }
    
    ?>
    <div class="wrap wpcw-setup-wizard">
        <h1>🎯 Configuración Inicial - WP Cupón WhatsApp</h1>
        
        <!-- Barra de progreso -->
        <div style="background: #f1f1f1; height: 10px; border-radius: 5px; margin: 20px 0; overflow: hidden;">
            <div style="background: #0073aa; height: 100%; width: <?php echo ($step / $max_steps) * 100; ?>%; transition: width 0.3s ease;"></div>
        </div>
        
        <p><strong>Paso <?php echo $step; ?> de <?php echo $max_steps; ?></strong></p>
        
        <div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 8px; max-width: 800px;">
            <?php
            switch ($step) {
                case 1:
                    wpcw_render_setup_step_welcome();
                    break;
                case 2:
                    wpcw_render_setup_step_pages();
                    break;
                case 3:
                    wpcw_render_setup_step_settings();
                    break;
                case 4:
                    wpcw_render_setup_step_complete();
                    break;
            }
            ?>
        </div>
    </div>
    
    <style>
    .wpcw-setup-wizard .form-table th {
        width: 200px;
        padding-left: 0;
    }
    .wpcw-setup-wizard .button-primary {
        background: #0073aa;
        border-color: #0073aa;
        font-size: 16px;
        padding: 10px 20px;
        height: auto;
    }
    .wpcw-setup-card {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        margin: 15px 0;
        border-left: 4px solid #0073aa;
    }
    </style>
    <?php
}

/**
 * Paso 1: Bienvenida
 */
function wpcw_render_setup_step_welcome() {
    ?>
    <h2>🎉 ¡Bienvenido a WP Cupón WhatsApp!</h2>
    
    <div class="wpcw-setup-card">
        <h3>✅ ¿Qué se ha configurado automáticamente?</h3>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li><strong>Menú de administración:</strong> Disponible en la barra lateral izquierda</li>
            <li><strong>Tipos de contenido:</strong> Comercios, Instituciones y Solicitudes</li>
            <li><strong>Base de datos:</strong> Tabla para gestionar canjes de cupones</li>
            <li><strong>Páginas del plugin:</strong> Creadas automáticamente con shortcodes</li>
        </ul>
    </div>
    
    <div class="wpcw-setup-card">
        <h3>🔧 ¿Qué vamos a configurar ahora?</h3>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li><strong>Verificar páginas:</strong> Asegurar que todas las páginas necesarias existen</li>
            <li><strong>Configurar reCAPTCHA:</strong> Proteger formularios contra spam (opcional)</li>
            <li><strong>Campos obligatorios:</strong> Definir qué información es requerida</li>
            <li><strong>Configuración final:</strong> Revisar y completar la instalación</li>
        </ul>
    </div>
    
    <p><strong>⏱️ Tiempo estimado:</strong> 3-5 minutos</p>
    
    <form method="post">
        <?php wp_nonce_field('wpcw_setup_wizard', 'wpcw_setup_nonce'); ?>
        <p>
            <button type="submit" class="button button-primary button-large">
                🚀 Comenzar Configuración
            </button>
            <a href="<?php echo admin_url('admin.php?page=wpcw-dashboard'); ?>" class="button button-secondary" style="margin-left: 10px;">
                ⏭️ Saltar y ir al Dashboard
            </a>
        </p>
    </form>
    <?php
}

/**
 * Paso 2: Verificar páginas
 */
function wpcw_render_setup_step_pages() {
    $pages_status = wpcw_check_plugin_pages_status();
    
    ?>
    <h2>📄 Verificación de Páginas del Plugin</h2>
    
    <p>El plugin necesita algunas páginas para funcionar correctamente. Vamos a verificar su estado:</p>
    
    <table class="widefat" style="margin: 20px 0;">
        <thead>
            <tr>
                <th>Página</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages_status as $page): ?>
            <tr>
                <td><strong><?php echo esc_html($page['title']); ?></strong><br>
                    <small>Shortcode: <code><?php echo esc_html($page['shortcode']); ?></code></small>
                </td>
                <td>
                    <?php if ($page['exists']): ?>
                        <span style="color: green;">✅ Existe</span><br>
                        <small><a href="<?php echo get_permalink($page['id']); ?>" target="_blank">Ver página</a></small>
                    <?php else: ?>
                        <span style="color: red;">❌ No existe</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$page['exists']): ?>
                        <small>Se creará automáticamente</small>
                    <?php else: ?>
                        <small>✅ Lista</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (count(array_filter($pages_status, function($p) { return !$p['exists']; })) > 0): ?>
        <div class="wpcw-setup-card">
            <p><strong>ℹ️ Información:</strong> Las páginas faltantes se crearán automáticamente al continuar.</p>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <?php wp_nonce_field('wpcw_setup_wizard', 'wpcw_setup_nonce'); ?>
        <input type="hidden" name="create_missing_pages" value="1">
        <p>
            <button type="submit" class="button button-primary button-large">
                ➡️ Continuar
            </button>
        </p>
    </form>
    <?php
}

/**
 * Paso 3: Configuraciones básicas
 */
function wpcw_render_setup_step_settings() {
    ?>
    <h2>⚙️ Configuraciones Básicas</h2>
    
    <form method="post">
        <?php wp_nonce_field('wpcw_setup_wizard', 'wpcw_setup_nonce'); ?>
        
        <div class="wpcw-setup-card">
            <h3>🛡️ Protección contra Spam (Opcional)</h3>
            <p>Configura Google reCAPTCHA v2 para proteger tus formularios:</p>
            
            <table class="form-table">
                <tr>
                    <th><label for="recaptcha_site_key">Site Key:</label></th>
                    <td>
                        <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" 
                               value="<?php echo esc_attr(get_option('wpcw_recaptcha_site_key', '')); ?>" 
                               class="regular-text" placeholder="6Lc...">
                        <p class="description">Obtén tus claves en <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA</a></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="recaptcha_secret_key">Secret Key:</label></th>
                    <td>
                        <input type="text" id="recaptcha_secret_key" name="recaptcha_secret_key" 
                               value="<?php echo esc_attr(get_option('wpcw_recaptcha_secret_key', '')); ?>" 
                               class="regular-text" placeholder="6Lc...">
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="wpcw-setup-card">
            <h3>📋 Campos Obligatorios</h3>
            <p>Selecciona qué campos serán obligatorios en el registro de usuarios:</p>
            
            <table class="form-table">
                <tr>
                    <th>Campos requeridos:</th>
                    <td>
                        <label>
                            <input type="checkbox" name="required_fields[]" value="dni" 
                                   <?php checked(get_option('wpcw_required_field_dni', false)); ?>>
                            DNI/Documento de Identidad
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="required_fields[]" value="whatsapp" 
                                   <?php checked(get_option('wpcw_required_field_whatsapp', false)); ?>>
                            Número de WhatsApp
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="required_fields[]" value="fecha_nacimiento" 
                                   <?php checked(get_option('wpcw_required_field_fecha_nacimiento', false)); ?>>
                            Fecha de Nacimiento
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <p>
            <button type="submit" class="button button-primary button-large">
                💾 Guardar y Continuar
            </button>
        </p>
    </form>
    <?php
}

/**
 * Paso 4: Completar configuración
 */
function wpcw_render_setup_step_complete() {
    ?>
    <h2>🎉 ¡Configuración Completada!</h2>
    
    <div class="wpcw-setup-card">
        <h3>✅ ¿Qué se ha configurado?</h3>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li>Páginas del plugin creadas y verificadas</li>
            <li>Configuraciones básicas guardadas</li>
            <li>Sistema listo para usar</li>
        </ul>
    </div>
    
    <div class="wpcw-setup-card">
        <h3>🚀 Próximos Pasos</h3>
        <ol style="padding-left: 20px;">
            <li><strong>Crear tu primer cupón:</strong> Ve a WooCommerce > Cupones</li>
            <li><strong>Configurar comercios:</strong> Añade comercios desde el menú del plugin</li>
            <li><strong>Personalizar páginas:</strong> Edita las páginas creadas según tus necesidades</li>
            <li><strong>Probar el sistema:</strong> Realiza un canje de prueba</li>
        </ol>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo admin_url('admin.php?page=wpcw-dashboard'); ?>" class="button button-primary button-large">
            🏠 Ir al Dashboard del Plugin
        </a>
        
        <a href="<?php echo admin_url('post-new.php?post_type=shop_coupon'); ?>" class="button button-secondary" style="margin-left: 10px;">
            🎫 Crear Primer Cupón
        </a>
    </div>
    
    <p style="text-align: center; color: #666;">
        <small>💡 Tip: Puedes acceder a todas las configuraciones desde <strong>WP Cupón WhatsApp > Configuración</strong></small>
    </p>
    <?php
}

/**
 * Procesar cada paso del wizard
 */
function wpcw_process_setup_step($step) {
    switch ($step) {
        case 2:
            if (isset($_POST['create_missing_pages'])) {
                WPCW_Installer::create_pages();
            }
            break;
            
        case 3:
            // Guardar configuraciones de reCAPTCHA
            if (isset($_POST['recaptcha_site_key'])) {
                update_option('wpcw_recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
            }
            if (isset($_POST['recaptcha_secret_key'])) {
                update_option('wpcw_recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));
            }
            
            // Guardar campos obligatorios
            $required_fields = isset($_POST['required_fields']) ? $_POST['required_fields'] : array();
            update_option('wpcw_required_field_dni', in_array('dni', $required_fields));
            update_option('wpcw_required_field_whatsapp', in_array('whatsapp', $required_fields));
            update_option('wpcw_required_field_fecha_nacimiento', in_array('fecha_nacimiento', $required_fields));
            break;
    }
}

/**
 * Verificar el estado de las páginas del plugin
 */
function wpcw_check_plugin_pages_status() {
    $pages = array(
        array(
            'option_name' => 'wpcw_page_id_mis_cupones',
            'title' => 'Mis Cupones Disponibles',
            'shortcode' => '[wpcw_mis_cupones]'
        ),
        array(
            'option_name' => 'wpcw_page_id_cupones_publicos',
            'title' => 'Cupones Públicos',
            'shortcode' => '[wpcw_cupones_publicos]'
        ),
        array(
            'option_name' => 'wpcw_page_id_formulario_adhesion',
            'title' => 'Formulario de Adhesión',
            'shortcode' => '[wpcw_formulario_adhesion]'
        ),
        array(
            'option_name' => 'wpcw_page_id_canje_cupon',
            'title' => 'Canje de Cupón',
            'shortcode' => '[wpcw_canje_cupon]'
        )
    );
    
    $status = array();
    foreach ($pages as $page) {
        $page_id = get_option($page['option_name']);
        $exists = $page_id && get_post_status($page_id) !== false;
        
        $status[] = array(
            'title' => $page['title'],
            'shortcode' => $page['shortcode'],
            'exists' => $exists,
            'id' => $page_id
        );
    }
    
    return $status;
}

?>