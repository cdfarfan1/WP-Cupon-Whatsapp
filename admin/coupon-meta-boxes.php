<?php
/**
 * Meta boxes para cupones de WooCommerce
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Agrega campos personalizados al meta box de cupones de WooCommerce
 */
function wpcw_add_coupon_meta_box() {
    add_meta_box(
        'wpcw_coupon_settings',
        __('Configuración de Canje por WhatsApp', 'wp-cupon-whatsapp'),
        'wpcw_coupon_meta_box_content',
        'shop_coupon',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'wpcw_add_coupon_meta_box');

/**
 * Enqueue admin scripts and styles for meta boxes
 */
function wpcw_admin_meta_box_assets($hook) {
    // Solo cargar en las páginas de edición de cupones
    if (!in_array($hook, array('post.php', 'post-new.php')) || get_post_type() !== 'shop_coupon') {
        return;
    }

    $plugin_url = defined('WPCW_PLUGIN_URL') ? WPCW_PLUGIN_URL : plugin_dir_url(dirname(__FILE__));
    $version = defined('WPCW_VERSION') ? WPCW_VERSION : '1.0.0';

    // Registrar y encolar estilos
    wp_register_style(
        'wpcw-meta-boxes',
        $plugin_url . 'admin/css/meta-boxes.css',
        array(),
        $version
    );
    wp_enqueue_style('wpcw-meta-boxes');

    // Registrar y encolar Chart.js
    wp_register_script(
        'chartjs',
        'https://cdn.jsdelivr.net/npm/chart.js',
        array(),
        '3.7.1',
        true
    );

    // Registrar y encolar scripts propios
    wp_register_script(
        'wpcw-meta-boxes',
        $plugin_url . 'admin/js/meta-boxes.js',
        array('jquery', 'chartjs'),
        $version,
        true
    );
    
    wp_enqueue_script('chartjs');
    wp_enqueue_script('wpcw-meta-boxes');
}
add_action('admin_enqueue_scripts', 'wpcw_admin_meta_box_assets');

/**
 * Contenido del meta box de cupones
 */
function wpcw_coupon_meta_box_content($post) {
    // Añadir nonce para verificación
    wp_nonce_field('wpcw_coupon_meta_box', 'wpcw_coupon_meta_box_nonce');

    // Obtener valores guardados
    $enabled = get_post_meta($post->ID, '_wpcw_enabled', true);
    $business_id = get_post_meta($post->ID, '_wpcw_associated_business_id', true);
    $expiry_reminder = get_post_meta($post->ID, '_wpcw_expiry_reminder', true);
    $auto_confirm = get_post_meta($post->ID, '_wpcw_auto_confirm', true);
    $whatsapp_text = get_post_meta($post->ID, '_wpcw_whatsapp_text', true);
    $max_uses = get_post_meta($post->ID, '_wpcw_max_uses_per_user', true);
    $redemption_hours = get_post_meta($post->ID, '_wpcw_redemption_hours', true);
    ?>
    <div class="wpcw-meta-box-tabs">
        <ul class="wpcw-tabs">
            <li class="active">
                <a href="#wpcw-tab-general">
                    <?php _e('General', 'wp-cupon-whatsapp'); ?>
                </a>
            </li>
            <li>
                <a href="#wpcw-tab-whatsapp">
                    <?php _e('WhatsApp', 'wp-cupon-whatsapp'); ?>
                </a>
            </li>
            <li>
                <a href="#wpcw-tab-restrictions">
                    <?php _e('Restricciones', 'wp-cupon-whatsapp'); ?>
                </a>
            </li>
            <li>
                <a href="#wpcw-tab-stats">
                    <?php _e('Estadísticas', 'wp-cupon-whatsapp'); ?>
                </a>
            </li>
        </ul>

        <div class="wpcw-tab-content active" id="wpcw-tab-general">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wpcw_enabled">
                            <?php _e('Habilitar para Canje por WhatsApp', 'wp-cupon-whatsapp'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" id="wpcw_enabled" name="wpcw_enabled" value="1" <?php checked($enabled, '1'); ?> />
                        <p class="description">
                            <?php _e('Permite que este cupón sea canjeado a través del sistema de WhatsApp.', 'wp-cupon-whatsapp'); ?>
                        </p>
                    </td>
                </tr>

        <tr>
            <th scope="row">
                <label for="wpcw_business_id">
                    <?php _e('Comercio Asociado', 'wp-cupon-whatsapp'); ?>
                </label>
            </th>
            <td>
                <?php
                // Obtener lista de comercios
                $businesses = get_posts(array(
                    'post_type' => 'wpcw_business',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'post_status' => 'publish'
                ));
                ?>
                <select id="wpcw_business_id" name="wpcw_business_id">
                    <option value=""><?php _e('Seleccionar comercio...', 'wp-cupon-whatsapp'); ?></option>
                    <?php foreach ($businesses as $business) : ?>
                        <option value="<?php echo esc_attr($business->ID); ?>" <?php selected($business_id, $business->ID); ?>>
                            <?php echo esc_html($business->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">
                    <?php _e('Selecciona el comercio que gestionará este cupón.', 'wp-cupon-whatsapp'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="wpcw_auto_confirm">
                    <?php _e('Confirmación Automática', 'wp-cupon-whatsapp'); ?>
                </label>
            </th>
            <td>
                <input type="checkbox" id="wpcw_auto_confirm" name="wpcw_auto_confirm" value="1" <?php checked($auto_confirm, '1'); ?> />
                <p class="description">
                    <?php _e('Confirmar automáticamente el canje sin requerir acción del comercio.', 'wp-cupon-whatsapp'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="wpcw-tab-content" id="wpcw-tab-whatsapp">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="wpcw_whatsapp_text">
                    <?php _e('Mensaje de WhatsApp', 'wp-cupon-whatsapp'); ?>
                </label>
            </th>
            <td>
                <textarea id="wpcw_whatsapp_text" name="wpcw_whatsapp_text" rows="5" cols="50"><?php echo esc_textarea($whatsapp_text); ?></textarea>
                <p class="description">
                    <?php _e('Mensaje personalizado que se enviará por WhatsApp. Usa {codigo} para incluir el código del cupón.', 'wp-cupon-whatsapp'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="wpcw_redemption_hours">
                    <?php _e('Horario de Canje', 'wp-cupon-whatsapp'); ?>
                </label>
            </th>
            <td>
                <input type="text" id="wpcw_redemption_hours" name="wpcw_redemption_hours" value="<?php echo esc_attr($redemption_hours); ?>" class="regular-text" />
                <p class="description">
                    <?php _e('Horario permitido para canjear el cupón (ejemplo: Lun-Vie 9:00-18:00).', 'wp-cupon-whatsapp'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="wpcw_expiry_reminder">
                    <?php _e('Recordatorio de Vencimiento', 'wp-cupon-whatsapp'); ?>
                </label>
            </th>
            <td>
                <input type="checkbox" id="wpcw_expiry_reminder" name="wpcw_expiry_reminder" value="1" <?php checked($expiry_reminder, '1'); ?> />
                <p class="description">
                    <?php _e('Enviar recordatorio por WhatsApp antes del vencimiento del cupón.', 'wp-cupon-whatsapp'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="wpcw-tab-content" id="wpcw-tab-restrictions">
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="wpcw_max_uses">
                    <?php _e('Máximo de Usos por Usuario', 'wp-cupon-whatsapp'); ?>
                </label>
            </th>
            <td>
                <input type="number" id="wpcw_max_uses" name="wpcw_max_uses" value="<?php echo esc_attr($max_uses); ?>" class="small-text" min="0" />
                <p class="description">
                    <?php _e('Número máximo de veces que un usuario puede canjear este cupón (0 para ilimitado).', 'wp-cupon-whatsapp'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="wpcw-tab-content" id="wpcw-tab-stats">
    <div class="contenedor-estadisticas">
        <?php
        // Obtener estadísticas del cupón
        $total_redemptions = get_post_meta($post->ID, '_wpcw_total_redemptions', true) ?: 0;
        $successful_redemptions = get_post_meta($post->ID, '_wpcw_successful_redemptions', true) ?: 0;
        $failed_redemptions = get_post_meta($post->ID, '_wpcw_failed_redemptions', true) ?: 0;

        // Obtener datos históricos
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_canjes';
        $redemption_data = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(fecha_canje) as fecha, COUNT(*) as total, estado
             FROM $table_name
             WHERE cupon_id = %d
             GROUP BY DATE(fecha_canje), estado
             ORDER BY fecha_canje DESC
             LIMIT 30",
            $post->ID
        ));

        // Preparar datos para el gráfico
        $dates = array();
        $successful_data = array();
        $failed_data = array();
        
        foreach ($redemption_data as $data) {
            $date = date_i18n('d M', strtotime($data->fecha));
            if (!in_array($date, $dates)) {
                $dates[] = $date;
            }
            if ($data->estado === 'completado') {
                $successful_data[$date] = (int)$data->total;
            } else {
                $failed_data[$date] = (int)$data->total;
            }
        }
        ?>
        <div class="resumen-estadisticas">
            <div class="caja-estadistica">
                <h3><?php _e('Total de Canjes', 'wp-cupon-whatsapp'); ?></h3>
                <div class="valor-estadistica"><?php echo esc_html($total_redemptions); ?></div>
            </div>
            <div class="caja-estadistica">
                <h3><?php _e('Canjes Exitosos', 'wp-cupon-whatsapp'); ?></h3>
                <div class="valor-estadistica"><?php echo esc_html($successful_redemptions); ?></div>
            </div>
            <div class="caja-estadistica">
                <h3><?php _e('Canjes Fallidos', 'wp-cupon-whatsapp'); ?></h3>
                <div class="valor-estadistica"><?php echo esc_html($failed_redemptions); ?></div>
            </div>
        </div>

        <div class="contenedor-grafico">
            <canvas id="wpcwRedemptionChart"></canvas>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var ctx = document.getElementById('wpcwRedemptionChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_reverse($dates)); ?>,
                    datasets: [{
                        label: '<?php _e('Canjes Exitosos', 'wp-cupon-whatsapp'); ?>',
                        data: <?php echo json_encode(array_map(function($date) use ($successful_data) {
                            return isset($successful_data[$date]) ? $successful_data[$date] : 0;
                        }, array_reverse($dates))); ?>,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        fill: true,
                        tension: 0.1
                    }, {
                        label: '<?php _e('Canjes Fallidos', 'wp-cupon-whatsapp'); ?>',
                        data: <?php echo json_encode(array_map(function($date) use ($failed_data) {
                            return isset($failed_data[$date]) ? $failed_data[$date] : 0;
                        }, array_reverse($dates))); ?>,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: '<?php _e('Historial de Canjes (Últimos 30 días)', 'wp-cupon-whatsapp'); ?>'
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return 'Fecha: ' + context[0].label;
                                },
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + ' canjes';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value + ' canjes';
                                }
                            },
                            title: {
                                display: true,
                                text: 'Número de Canjes'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Fecha'
                            }
                        }
                    }
                }
            });
        });
        </script>
    </div>
</div>
</div>
    <?php
}

/**
 * Guarda los datos del meta box
 */
function wpcw_save_meta_box($post_id) {
    // Verificar condiciones de guardado
    if (!isset($_POST['wpcw_coupon_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['wpcw_coupon_meta_box_nonce'], 'wpcw_coupon_meta_box') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        'shop_coupon' !== get_post_type($post_id) ||
        !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Array de campos a guardar
    $fields = array(
        '_wpcw_enabled' => array('type' => 'checkbox'),
        '_wpcw_business_id' => array('type' => 'int'),
        '_wpcw_expiry_reminder' => array('type' => 'checkbox'),
        '_wpcw_auto_confirm' => array('type' => 'checkbox'),
        '_wpcw_whatsapp_text' => array('type' => 'text'),
        '_wpcw_max_uses_per_user' => array('type' => 'int'),
        '_wpcw_redemption_hours' => array('type' => 'text')
    );

    // Procesar cada campo
    foreach ($fields as $meta_key => $field) {
        $field_name = str_replace('_', '', $meta_key);
        $old_value = get_post_meta($post_id, $meta_key, true);
        $new_value = wpcw_process_field_value($field_name, $field['type']);

        if ($new_value !== $old_value) {
            update_post_meta($post_id, $meta_key, $new_value);
        }
    }
}
add_action('save_post', 'wpcw_save_meta_box');

/**
 * Procesa y sanitiza el valor del campo según su tipo
 */
function wpcw_process_field_value($field_name, $type) {
    $value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';
    
    switch ($type) {
        case 'checkbox':
            return empty($value) ? '0' : '1';
        case 'int':
            return empty($value) ? '' : absint($value);
        case 'text':
            return empty($value) ? '' : sanitize_text_field($value);
        default:
            return '';
    }
}
