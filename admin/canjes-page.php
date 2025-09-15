<?php
/**
 * Página de gestión de canjes
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

function wpcw_canjes_page() {
    // Verificar permisos
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( __( 'No tienes permisos suficientes para acceder a esta página.', 'wp-cupon-whatsapp' ) );
    }

    // Procesar acciones
    if ( isset( $_POST['action'] ) && isset( $_POST['canje_id'] ) && isset( $_POST['_wpnonce'] ) ) {
        if ( wp_verify_nonce( $_POST['_wpnonce'], 'wpcw_canje_action' ) ) {
            $canje_id = absint( $_POST['canje_id'] );
            switch ( $_POST['action'] ) {
                case 'confirm':
                    wpcw_confirm_canje( $canje_id );
                    break;
                case 'reject':
                    wpcw_reject_canje( $canje_id );
                    break;
                case 'cancel':
                    wpcw_cancel_canje( $canje_id );
                    break;
            }
        }
    }

    // Obtener filtros
    $status      = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
    $comercio_id = isset( $_GET['comercio_id'] ) ? absint( $_GET['comercio_id'] ) : 0;
    $search      = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

    // Obtener canjes
    $canjes = wpcw_get_canjes_list( $status, $comercio_id, $search );
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e( 'Gestión de Canjes', 'wp-cupon-whatsapp' ); ?></h1>

        <!-- Filtros -->
        <div class="wpcw-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="wpcw-canjes">
                
                <select name="status">
                    <option value=""><?php _e( 'Todos los estados', 'wp-cupon-whatsapp' ); ?></option>
                    <?php foreach ( wpcw_get_estados_canje() as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="comercio_id">
                    <option value=""><?php _e( 'Todos los comercios', 'wp-cupon-whatsapp' ); ?></option>
                    <?php
                    $comercios = get_posts(
                        array(
							'post_type'      => 'wpcw_business',
							'posts_per_page' => -1,
							'orderby'        => 'title',
							'order'          => 'ASC',
                        )
                    );
                    foreach ( $comercios as $comercio ) :
                        ?>
                        <option value="<?php echo esc_attr( $comercio->ID ); ?>" <?php selected( $comercio_id, $comercio->ID ); ?>>
                            <?php echo esc_html( $comercio->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" 
                        placeholder="<?php esc_attr_e( 'Buscar por número o cliente...', 'wp-cupon-whatsapp' ); ?>">

                <?php submit_button( __( 'Filtrar', 'wp-cupon-whatsapp' ), 'secondary', 'submit', false ); ?>
            </form>
        </div>

        <!-- Lista de Canjes -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e( 'Número', 'wp-cupon-whatsapp' ); ?></th>
                    <th><?php _e( 'Cliente', 'wp-cupon-whatsapp' ); ?></th>
                    <th><?php _e( 'Cupón', 'wp-cupon-whatsapp' ); ?></th>
                    <th><?php _e( 'Comercio', 'wp-cupon-whatsapp' ); ?></th>
                    <th><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></th>
                    <th><?php _e( 'Fecha', 'wp-cupon-whatsapp' ); ?></th>
                    <th><?php _e( 'Acciones', 'wp-cupon-whatsapp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $canjes ) ) : ?>
                    <?php foreach ( $canjes as $canje ) : ?>
                        <tr>
                            <td><?php echo esc_html( $canje->numero_canje ); ?></td>
                            <td>
                                <?php
                                $user = get_user_by( 'id', $canje->cliente_id );
                                echo $user ? esc_html( $user->display_name ) : __( 'Usuario eliminado', 'wp-cupon-whatsapp' );
                                ?>
                            </td>
                            <td>
                                <?php
                                $cupon = get_post( $canje->cupon_id );
                                echo $cupon ? esc_html( $cupon->post_title ) : __( 'Cupón eliminado', 'wp-cupon-whatsapp' );
                                ?>
                            </td>
                            <td>
                                <?php
                                $comercio = get_post( $canje->comercio_id );
                                echo $comercio ? esc_html( $comercio->post_title ) : __( 'Comercio eliminado', 'wp-cupon-whatsapp' );
                                ?>
                            </td>
                            <td>
                                <span class="estado-<?php echo esc_attr( $canje->estado_canje ); ?>">
                                    <?php echo esc_html( wpcw_get_estado_canje_texto( $canje->estado_canje ) ); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                echo esc_html(
                                    date_i18n(
                                        get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i:s' ),
                                        strtotime( $canje->fecha_solicitud_canje )
                                    )
                                );
                                ?>
                            </td>
                            <td>
                                <?php if ( wpcw_can_change_canje_status( $canje ) ) : ?>
                                    <div class="row-actions">
                                        <?php
                                        $actions = array();
                                        if ( wpcw_es_transicion_estado_valida( $canje->estado_canje, 'confirmado' ) ) {
                                            $actions['confirm'] = sprintf(
                                                '<a href="#" class="wpcw-action" data-action="confirm" data-id="%d">%s</a>',
                                                $canje->id,
                                                __( 'Confirmar', 'wp-cupon-whatsapp' )
                                            );
                                        }
                                        if ( wpcw_es_transicion_estado_valida( $canje->estado_canje, 'rechazado' ) ) {
                                            $actions['reject'] = sprintf(
                                                '<a href="#" class="wpcw-action" data-action="reject" data-id="%d">%s</a>',
                                                $canje->id,
                                                __( 'Rechazar', 'wp-cupon-whatsapp' )
                                            );
                                        }
                                        if ( wpcw_es_transicion_estado_valida( $canje->estado_canje, 'cancelado' ) ) {
                                            $actions['cancel'] = sprintf(
                                                '<a href="#" class="wpcw-action" data-action="cancel" data-id="%d">%s</a>',
                                                $canje->id,
                                                __( 'Cancelar', 'wp-cupon-whatsapp' )
                                            );
                                        }
                                        echo implode( ' | ', $actions );
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7"><?php _e( 'No se encontraron canjes.', 'wp-cupon-whatsapp' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.wpcw-action').click(function(e) {
            e.preventDefault();
            
            const action = $(this).data('action');
            const id = $(this).data('id');
            
            if (confirm('¿Estás seguro de que deseas ' + action + ' este canje?')) {
                const form = $('<form>', {
                    'method': 'post',
                    'action': ''
                });
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'action',
                    'value': action
                }));
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'canje_id',
                    'value': id
                }));
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_wpnonce',
                    'value': '<?php echo wp_create_nonce( 'wpcw_canje_action' ); ?>'
                }));
                
                $('body').append(form);
                form.submit();
            }
        });
    });
    </script>
    <?php
}

/**
 * Obtiene la lista de canjes filtrada
 */
function wpcw_get_canjes_list( $status = '', $comercio_id = 0, $search = '' ) {
    global $wpdb;

    $where  = array( '1=1' );
    $values = array();

    if ( $status ) {
        $where[]  = 'estado_canje = %s';
        $values[] = $status;
    }

    if ( $comercio_id ) {
        $where[]  = 'comercio_id = %d';
        $values[] = $comercio_id;
    }

    if ( $search ) {
        $where[]  = '(numero_canje LIKE %s OR cliente_id IN (SELECT ID FROM ' . $wpdb->users . ' WHERE display_name LIKE %s))';
        $values[] = '%' . $wpdb->esc_like( $search ) . '%';
        $values[] = '%' . $wpdb->esc_like( $search ) . '%';
    }

    $sql = "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE " . implode( ' AND ', $where ) . ' ORDER BY fecha_solicitud_canje DESC';

    if ( ! empty( $values ) ) {
        $sql = $wpdb->prepare( $sql, $values );
    }

    return $wpdb->get_results( $sql );
}

/**
 * Verifica si se puede cambiar el estado de un canje
 */
function wpcw_can_change_canje_status( $canje ) {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return false;
    }

    // No se puede cambiar si está utilizado en un pedido
    if ( $canje->estado_canje === 'utilizado_en_pedido_wc' ) {
        return false;
    }

    // No se puede cambiar si está expirado
    if ( $canje->estado_canje === 'expirado' ) {
        return false;
    }

    return true;
}

/**
 * Confirma un canje
 */
function wpcw_confirm_canje( $canje_id ) {
    global $wpdb;

    $canje = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
            $canje_id
        )
    );

    if ( ! $canje || ! wpcw_es_transicion_estado_valida( $canje->estado_canje, 'confirmado' ) ) {
        return false;
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'wpcw_canjes',
        array(
            'estado_canje'       => 'confirmado',
            'fecha_confirmacion' => current_time( 'mysql' ),
        ),
        array( 'id' => $canje_id ),
        array( '%s', '%s' ),
        array( '%d' )
    );

    if ( $updated ) {
        // Registrar la acción
        WPCW_Logger::log(
            'info',
            'Canje confirmado',
            array(
				'canje_id' => $canje_id,
				'user_id'  => get_current_user_id(),
            )
        );

        do_action( 'wpcw_canje_confirmado', $canje_id );
    }

    return $updated;
}

/**
 * Rechaza un canje
 */
function wpcw_reject_canje( $canje_id ) {
    global $wpdb;

    $canje = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
            $canje_id
        )
    );

    if ( ! $canje || ! wpcw_es_transicion_estado_valida( $canje->estado_canje, 'rechazado' ) ) {
        return false;
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'wpcw_canjes',
        array(
            'estado_canje'  => 'rechazado',
            'fecha_rechazo' => current_time( 'mysql' ),
        ),
        array( 'id' => $canje_id ),
        array( '%s', '%s' ),
        array( '%d' )
    );

    if ( $updated ) {
        // Registrar la acción
        WPCW_Logger::log(
            'info',
            'Canje rechazado',
            array(
				'canje_id' => $canje_id,
				'user_id'  => get_current_user_id(),
            )
        );

        do_action( 'wpcw_canje_rechazado', $canje_id );
    }

    return $updated;
}

/**
 * Cancela un canje
 */
function wpcw_cancel_canje( $canje_id ) {
    global $wpdb;

    $canje = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
            $canje_id
        )
    );

    if ( ! $canje || ! wpcw_es_transicion_estado_valida( $canje->estado_canje, 'cancelado' ) ) {
        return false;
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'wpcw_canjes',
        array(
            'estado_canje'      => 'cancelado',
            'fecha_cancelacion' => current_time( 'mysql' ),
        ),
        array( 'id' => $canje_id ),
        array( '%s', '%s' ),
        array( '%d' )
    );

    if ( $updated ) {
        // Registrar la acción
        WPCW_Logger::log(
            'info',
            'Canje cancelado',
            array(
				'canje_id' => $canje_id,
				'user_id'  => get_current_user_id(),
            )
        );

        do_action( 'wpcw_canje_cancelado', $canje_id );
    }

    return $updated;
}
