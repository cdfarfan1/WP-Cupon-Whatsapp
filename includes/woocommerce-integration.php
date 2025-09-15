<?php
/**
 * Integración con WooCommerce para WP Cupón WhatsApp
 *
 * Este archivo contiene las funciones necesarias para integrar
 * el plugin WP Cupón WhatsApp con WooCommerce.
 *
 * @package WP_Cupon_WhatsApp
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

/**
 * Agrega un elemento de menú "Mis Canjes" en la página de Mi Cuenta de WooCommerce
 */
function wpcw_add_mis_canjes_menu_item($menu_items) {
    // Insertar después de "pedidos" pero antes de "descargas"
    $new_items = array();
    
    foreach ($menu_items as $key => $value) {
        $new_items[$key] = $value;
        
        if ($key === 'orders') {
            $new_items['mis-canjes'] = __('Mis Canjes', 'wp-cupon-whatsapp');
        }
    }
    
    return $new_items;
}
add_filter('woocommerce_account_menu_items', 'wpcw_add_mis_canjes_menu_item');

/**
 * Registra la variable de consulta para la página de Mis Canjes
 */
function wpcw_add_mis_canjes_query_var($vars) {
    $vars[] = 'mis-canjes';
    return $vars;
}
add_filter('query_vars', 'wpcw_add_mis_canjes_query_var');

/**
 * Agrega el endpoint para Mis Canjes
 */
function wpcw_add_mis_canjes_endpoint() {
    add_rewrite_endpoint('mis-canjes', EP_ROOT | EP_PAGES);
}
add_action('init', 'wpcw_add_mis_canjes_endpoint');

/**
 * Renderiza el contenido de la página Mis Canjes
 */
function wpcw_render_mis_canjes_content() {
    // Obtener el ID del usuario actual
    $user_id = get_current_user_id();
    
    // Obtener los canjes del usuario
    global $wpdb;
    $tabla_canjes = $wpdb->prefix . 'wpcw_canjes';
    
    $canjes = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $tabla_canjes WHERE user_id = %d ORDER BY fecha_canje DESC",
            $user_id
        )
    );
    
    // Mostrar los canjes
    echo '<h2>' . __('Mis Canjes de Cupones', 'wp-cupon-whatsapp') . '</h2>';
    
    if (empty($canjes)) {
        echo '<p>' . __('No has canjeado ningún cupón todavía.', 'wp-cupon-whatsapp') . '</p>';
        return;
    }
    
    echo '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('Cupón', 'wp-cupon-whatsapp') . '</th>';
    echo '<th>' . __('Código', 'wp-cupon-whatsapp') . '</th>';
    echo '<th>' . __('Fecha de Canje', 'wp-cupon-whatsapp') . '</th>';
    echo '<th>' . __('Estado', 'wp-cupon-whatsapp') . '</th>';
    echo '<th>' . __('Acciones', 'wp-cupon-whatsapp') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($canjes as $canje) {
        $cupon = get_post($canje->cupon_id);
        if (!$cupon) continue;
        
        echo '<tr>';
        echo '<td>' . esc_html($cupon->post_title) . '</td>';
        echo '<td>' . esc_html($canje->codigo) . '</td>';
        echo '<td>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($canje->fecha_canje)) . '</td>';
        
        // Estado del canje
        $estado = $canje->estado;
        $clase_estado = '';
        
        switch ($estado) {
            case 'pendiente':
                $clase_estado = 'wpcw-status-pending';
                $texto_estado = __('Pendiente', 'wp-cupon-whatsapp');
                break;
            case 'canjeado':
                $clase_estado = 'wpcw-status-completed';
                $texto_estado = __('Canjeado', 'wp-cupon-whatsapp');
                break;
            case 'expirado':
                $clase_estado = 'wpcw-status-expired';
                $texto_estado = __('Expirado', 'wp-cupon-whatsapp');
                break;
            default:
                $texto_estado = $estado;
        }
        
        echo '<td><span class="wpcw-status ' . esc_attr($clase_estado) . '">' . esc_html($texto_estado) . '</span></td>';
        
        // Acciones
        echo '<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">';
        
        // Enlace para ver detalles
        echo '<a href="' . esc_url(get_permalink($cupon->ID)) . '" class="woocommerce-button button view">' . __('Ver', 'wp-cupon-whatsapp') . '</a> ';
        
        // Enlace para canjear por WhatsApp si está pendiente
        if ($estado === 'pendiente') {
            $whatsapp_link = wpcw_get_canje_whatsapp_link($canje->codigo);
            if ($whatsapp_link) {
                echo '<a href="' . esc_url($whatsapp_link) . '" class="woocommerce-button button whatsapp" target="_blank">' . __('Canjear por WhatsApp', 'wp-cupon-whatsapp') . '</a>';
            }
        }
        
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    // Agregar estilos para los estados
    echo '<style>
        .wpcw-status {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
            display: inline-block;
        }
        .wpcw-status-pending {
            background-color: #f8dda7;
            color: #94660c;
        }
        .wpcw-status-completed {
            background-color: #c6e1c6;
            color: #5b841b;
        }
        .wpcw-status-expired {
            background-color: #eba3a3;
            color: #761919;
        }
        .button.whatsapp {
            background-color: #25D366;
            color: white;
        }
        .button.whatsapp:hover {
            background-color: #128C7E;
            color: white;
        }
    </style>';
}
add_action('woocommerce_account_mis-canjes_endpoint', 'wpcw_render_mis_canjes_content');

/**
 * Procesa el canje de cupones cuando un pedido se completa
 */
function wpcw_process_order_completed($order_id) {
    // Obtener el pedido
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    // Obtener el usuario
    $user_id = $order->get_user_id();
    if (!$user_id) return;
    
    // Verificar si hay cupones disponibles para este usuario
    $cupones_disponibles = get_posts(array(
        'post_type' => 'wpcw_cupon',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'wpcw_disponible_para_pedidos',
                'value' => 'yes',
                'compare' => '='
            )
        )
    ));
    
    if (empty($cupones_disponibles)) return;
    
    // Seleccionar un cupón aleatorio
    $cupon = $cupones_disponibles[array_rand($cupones_disponibles)];
    
    // Generar código único
    $codigo = wpcw_generate_unique_code();
    
    // Registrar el canje en la base de datos
    global $wpdb;
    $tabla_canjes = $wpdb->prefix . 'wpcw_canjes';
    
    $wpdb->insert(
        $tabla_canjes,
        array(
            'user_id' => $user_id,
            'cupon_id' => $cupon->ID,
            'codigo' => $codigo,
            'fecha_canje' => current_time('mysql'),
            'estado' => 'pendiente',
            'order_id' => $order_id
        )
    );
    
    // Notificar al usuario
    $user = get_user_by('id', $user_id);
    if ($user) {
        $mensaje = sprintf(
            __('¡Felicidades! Has recibido un cupón por tu compra. Visita tu cuenta para ver los detalles: %s', 'wp-cupon-whatsapp'),
            wc_get_account_endpoint_url('mis-canjes')
        );
        
        wp_mail(
            $user->user_email,
            __('Has recibido un cupón por tu compra', 'wp-cupon-whatsapp'),
            $mensaje
        );
    }
}
add_action('woocommerce_order_status_completed', 'wpcw_process_order_completed');

/**
 * Genera un código único para los canjes
 */
function wpcw_generate_unique_code() {
    $prefix = 'WPCW';
    $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    return $prefix . '-' . $random;
}

/**
 * Registra los shortcodes relacionados con WooCommerce
 */
function wpcw_register_woocommerce_shortcodes() {
    add_shortcode('wpcw_mis_cupones', 'wpcw_mis_cupones_shortcode');
    add_shortcode('wpcw_cupones_publicos', 'wpcw_cupones_publicos_shortcode');
    add_shortcode('wpcw_canje_cupon', 'wpcw_canje_cupon_shortcode');
}
add_action('init', 'wpcw_register_woocommerce_shortcodes');

/**
 * Shortcode para mostrar los cupones del usuario actual
 */
function wpcw_mis_cupones_shortcode($atts) {
    // Si el usuario no está logueado, mostrar mensaje de error
    if (!is_user_logged_in()) {
        return '<p>' . __('Debes iniciar sesión para ver tus cupones.', 'wp-cupon-whatsapp') . '</p>';
    }
    
    ob_start();
    wpcw_render_mis_canjes_content();
    return ob_get_clean();
}

/**
 * Shortcode para mostrar cupones públicos disponibles
 */
function wpcw_cupones_publicos_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10,
        'columns' => 3,
    ), $atts);
    
    $cupones = get_posts(array(
        'post_type' => 'wpcw_cupon',
        'posts_per_page' => intval($atts['limit']),
        'meta_query' => array(
            array(
                'key' => 'wpcw_publico',
                'value' => 'yes',
                'compare' => '='
            )
        )
    ));
    
    if (empty($cupones)) {
        return '<p>' . __('No hay cupones disponibles en este momento.', 'wp-cupon-whatsapp') . '</p>';
    }
    
    $columns = intval($atts['columns']);
    if ($columns < 1) $columns = 3;
    
    $output = '<div class="wpcw-cupones-grid columns-' . esc_attr($columns) . '">';
    
    foreach ($cupones as $cupon) {
        $imagen = get_the_post_thumbnail_url($cupon->ID, 'medium');
        if (!$imagen) {
            $imagen = plugins_url('/public/img/cupon-default.png', dirname(__FILE__));
        }
        
        $output .= '<div class="wpcw-cupon-item">';
        $output .= '<div class="wpcw-cupon-imagen"><img src="' . esc_url($imagen) . '" alt="' . esc_attr($cupon->post_title) . '"></div>';
        $output .= '<h3>' . esc_html($cupon->post_title) . '</h3>';
        $output .= '<div class="wpcw-cupon-excerpt">' . wp_trim_words($cupon->post_content, 20) . '</div>';
        $output .= '<a href="' . esc_url(get_permalink($cupon->ID)) . '" class="wpcw-cupon-button">' . __('Ver Detalles', 'wp-cupon-whatsapp') . '</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    // Agregar estilos
    $output .= '<style>
        .wpcw-cupones-grid {
            display: grid;
            grid-template-columns: repeat(' . $columns . ', 1fr);
            gap: 20px;
        }
        .wpcw-cupon-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        .wpcw-cupon-imagen img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .wpcw-cupon-button {
            display: inline-block;
            background-color: #25D366;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }
        .wpcw-cupon-button:hover {
            background-color: #128C7E;
            color: white;
        }
        @media (max-width: 768px) {
            .wpcw-cupones-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .wpcw-cupones-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>';
    
    return $output;
}

/**
 * Shortcode para mostrar el formulario de canje de cupones
 */
function wpcw_canje_cupon_shortcode($atts) {
    $atts = shortcode_atts(array(
        'redirect' => '',
    ), $atts);
    
    $output = '<div class="wpcw-canje-form">';
    
    // Procesar el formulario si se envió
    if (isset($_POST['wpcw_canje_codigo']) && isset($_POST['wpcw_canje_submit'])) {
        $codigo = sanitize_text_field($_POST['wpcw_canje_codigo']);
        
        // Verificar si el código existe
        global $wpdb;
        $tabla_canjes = $wpdb->prefix . 'wpcw_canjes';
        
        $canje = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla_canjes WHERE codigo = %s",
                $codigo
            )
        );
        
        if (!$canje) {
            $output .= '<div class="wpcw-error">' . __('El código ingresado no es válido.', 'wp-cupon-whatsapp') . '</div>';
        } else if ($canje->estado !== 'pendiente') {
            $output .= '<div class="wpcw-error">' . __('Este cupón ya ha sido canjeado o ha expirado.', 'wp-cupon-whatsapp') . '</div>';
        } else {
            // Obtener el cupón
            $cupon = get_post($canje->cupon_id);
            
            if (!$cupon) {
                $output .= '<div class="wpcw-error">' . __('Error al procesar el cupón.', 'wp-cupon-whatsapp') . '</div>';
            } else {
                // Mostrar información del cupón
                $output .= '<div class="wpcw-success">';
                $output .= '<h3>' . __('¡Cupón válido!', 'wp-cupon-whatsapp') . '</h3>';
                $output .= '<p><strong>' . __('Cupón:', 'wp-cupon-whatsapp') . '</strong> ' . esc_html($cupon->post_title) . '</p>';
                $output .= '<p><strong>' . __('Código:', 'wp-cupon-whatsapp') . '</strong> ' . esc_html($canje->codigo) . '</p>';
                
                // Enlace de WhatsApp para canjear
                $whatsapp_link = wpcw_get_canje_whatsapp_link($canje->codigo);
                if ($whatsapp_link) {
                    $output .= '<a href="' . esc_url($whatsapp_link) . '" class="wpcw-whatsapp-button" target="_blank">';
                    $output .= __('Canjear por WhatsApp', 'wp-cupon-whatsapp');
                    $output .= '</a>';
                }
                
                $output .= '</div>';
                
                // Redireccionar si se especificó una URL
                if (!empty($atts['redirect'])) {
                    $redirect_url = esc_url($atts['redirect']);
                    $output .= '<script>setTimeout(function() { window.location.href = "' . $redirect_url . '"; }, 3000);</script>';
                }
            }
        }
    }
    
    // Mostrar el formulario
    $output .= '<form method="post" class="wpcw-canje-cupon-form">';
    $output .= '<h3>' . __('Canjear Cupón', 'wp-cupon-whatsapp') . '</h3>';
    $output .= '<div class="wpcw-form-group">';
    $output .= '<label for="wpcw_canje_codigo">' . __('Código del Cupón:', 'wp-cupon-whatsapp') . '</label>';
    $output .= '<input type="text" name="wpcw_canje_codigo" id="wpcw_canje_codigo" required>';
    $output .= '</div>';
    $output .= '<div class="wpcw-form-group">';
    $output .= '<button type="submit" name="wpcw_canje_submit" class="wpcw-submit-button">' . __('Verificar Cupón', 'wp-cupon-whatsapp') . '</button>';
    $output .= '</div>';
    $output .= '</form>';
    
    $output .= '</div>';
    
    // Agregar estilos
    $output .= '<style>
        .wpcw-canje-form {
            max-width: 500px;
            margin: 0 auto;
        }
        .wpcw-form-group {
            margin-bottom: 15px;
        }
        .wpcw-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .wpcw-form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .wpcw-submit-button {
            background-color: #25D366;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .wpcw-submit-button:hover {
            background-color: #128C7E;
        }
        .wpcw-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .wpcw-success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        .wpcw-whatsapp-button {
            display: inline-block;
            background-color: #25D366;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 10px;
        }
        .wpcw-whatsapp-button:hover {
            background-color: #128C7E;
            color: white;
        }
    </style>';
    
    return $output;
}
?>