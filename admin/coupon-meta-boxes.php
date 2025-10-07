<?php
/**
 * Meta Boxes for WooCommerce Coupons - Refactored for Convenios
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

// --- 1. Meta Box Registration ---

add_action( 'add_meta_boxes', 'wpcw_add_coupon_meta_boxes' );

function wpcw_add_coupon_meta_boxes() {
    add_meta_box(
        'wpcw_coupon_settings',
        __( 'Configuración de la Plataforma de Beneficios', 'wp-cupon-whatsapp' ),
        'wpcw_render_convenio_coupon_meta_box',
        'shop_coupon',
        'normal',
        'high'
    );
}

// --- 2. Meta Box Rendering ---

function wpcw_render_convenio_coupon_meta_box( $post ) {
    wp_nonce_field( 'wpcw_coupon_meta_box', 'wpcw_coupon_meta_box_nonce' );

    // --- Logic by El Artesano ---
    $current_user_id = get_current_user_id();
    // This needs a helper function to get all entities (businesses/institutions) managed by the user.
    // For now, we assume the user is associated with one entity.
    $user_entity_id = get_user_meta( $current_user_id, '_wpcw_entity_id', true ); // Placeholder for user's main entity ID

    $convenios_query_args = [
        'post_type' => 'wpcw_convenio',
        'post_status' => 'publish', // Active
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => '_convenio_provider_id',
                'value' => $user_entity_id,
            ],
            [
                'key' => '_convenio_recipient_id',
                'value' => $user_entity_id,
            ],
        ],
    ];
    $active_convenios = get_posts( $convenios_query_args );

    $associated_convenio_id = get_post_meta( $post->ID, '_wpcw_associated_convenio_id', true );

    ?>
    <div class="wpcw-coupon-settings">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="wpcw_associated_convenio_id">
                            <?php _e( 'Asociar a Convenio', 'wp-cupon-whatsapp' ); ?>
                        </label>
                    </th>
                    <td>
                        <?php if ( ! empty( $active_convenios ) ) : ?>
                            <select id="wpcw_associated_convenio_id" name="wpcw_associated_convenio_id" required>
                                <option value=""><?php _e( 'Seleccionar un convenio activo...', 'wp-cupon-whatsapp' ); ?></option>
                                <?php foreach ( $active_convenios as $convenio ) : ?>
                                    <option value="<?php echo esc_attr( $convenio->ID ); ?>"
                                            <?php selected( $associated_convenio_id, $convenio->ID ); ?>>
                                        <?php echo esc_html( $convenio->post_title ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Este cupón solo será válido bajo los términos y para los beneficiarios de este convenio.', 'wp-cupon-whatsapp' ); ?></p>
                        <?php else : ?>
                            <p><strong><?php _e( 'No tienes convenios activos. Debes proponer y tener un convenio aceptado para poder crear un cupón.', 'wp-cupon-whatsapp' ); ?></strong></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- Other fields can remain here -->
            </tbody>
        </table>
    </div>
    <?php
}

// --- 3. Save Meta Data ---

add_action( 'save_post_shop_coupon', 'wpcw_save_convenio_coupon_meta' );

function wpcw_save_convenio_coupon_meta( $post_id ) {
    // --- Security checks by El Guardián ---
    if ( ! isset( $_POST['wpcw_coupon_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_coupon_meta_box_nonce'], 'wpcw_coupon_meta_box' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // --- Save Convenio Association ---
    if ( isset( $_POST['wpcw_associated_convenio_id'] ) ) {
        $convenio_id = absint( $_POST['wpcw_associated_convenio_id'] );
        update_post_meta( $post_id, '_wpcw_associated_convenio_id', $convenio_id );
    }

    // --- Deprecate old logic ---
    delete_post_meta( $post_id, '_wpcw_associated_business_id' );
}

// --- 4. Admin Column Display ---

add_filter( 'manage_edit-shop_coupon_columns', 'wpcw_add_convenio_column_to_coupons_list' );

function wpcw_add_convenio_column_to_coupons_list( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[$key] = $value;
        if ( $key === 'usage' ) { // Add column after the 'usage' column
            $new_columns['wpcw_convenio'] = __( 'Convenio Asociado', 'wp-cupon-whatsapp' );
        }
    }
    return $new_columns;
}

add_action( 'manage_shop_coupon_posts_custom_column', 'wpcw_render_convenio_column', 10, 2 );

function wpcw_render_convenio_column( $column, $post_id ) {
    if ( $column === 'wpcw_convenio' ) {
        $convenio_id = get_post_meta( $post_id, '_wpcw_associated_convenio_id', true );
        if ( $convenio_id ) {
            $convenio_title = get_the_title( $convenio_id );
            $edit_link = get_edit_post_link( $convenio_id );
            if ( $convenio_title ) {
                echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $convenio_title ) . '</a>';
            } else {
                echo '<em>' . __( 'Convenio no encontrado', 'wp-cupon-whatsapp' ) . '</em>';
            }
        } else {
            echo '—';
        }
    }
}
