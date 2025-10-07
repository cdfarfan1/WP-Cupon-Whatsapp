<?php
/**
 * Meta Boxes for WooCommerce Coupons - WP Cupón WhatsApp
 *
 * Adds custom meta boxes to WooCommerce coupon editing page
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add meta boxes to coupon edit page
 */
function wpcw_add_coupon_meta_boxes() {
    add_meta_box(
        'wpcw_coupon_settings',
        __( 'WP Cupón WhatsApp - Configuración', 'wp-cupon-whatsapp' ),
        'wpcw_coupon_settings_meta_box',
        'shop_coupon',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'wpcw_add_coupon_meta_boxes' );

/**
 * Render WPCW coupon settings meta box
 *
 * @param WP_Post $post
 */
function wpcw_coupon_settings_meta_box( $post ) {
    wp_nonce_field( 'wpcw_coupon_meta_box', 'wpcw_coupon_meta_box_nonce' );

    $coupon = wpcw_get_coupon( $post->ID );

    // Get businesses for dropdown
    $businesses = get_posts( array(
        'post_type'      => 'wpcw_business',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    ?>
    <div class="wpcw-coupon-settings">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="wpcw_enabled">
                            <?php _e( 'Habilitar para WhatsApp', 'wp-cupon-whatsapp' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox"
                               id="wpcw_enabled"
                               name="wpcw_enabled"
                               value="yes"
                               <?php checked( $coupon->is_wpcw_enabled(), true ); ?> />
                        <p class="description">
                            <?php _e( 'Permitir canje de este cupón a través de WhatsApp', 'wp-cupon-whatsapp' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpcw_associated_business_id">
                            <?php _e( 'Comercio Asociado', 'wp-cupon-whatsapp' ); ?>
                        </label>
                    </th>
                    <td>
                        <select id="wpcw_associated_business_id" name="wpcw_associated_business_id">
                            <option value=""><?php _e( 'Seleccionar comercio...', 'wp-cupon-whatsapp' ); ?></option>
                            <?php foreach ( $businesses as $business ) : ?>
                                <option value="<?php echo esc_attr( $business->ID ); ?>"
                                        <?php selected( $coupon->get_associated_business_id(), $business->ID ); ?>>
                                    <?php echo esc_html( $business->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e( 'Comercio que procesará el canje de este cupón', 'wp-cupon-whatsapp' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php _e( 'Tipo de Cupón', 'wp-cupon-whatsapp' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio"
                                       name="wpcw_coupon_type"
                                       value="loyalty"
                                       <?php checked( $coupon->is_loyalty_coupon(), true ); ?> />
                                <?php _e( 'Cupón de Lealtad', 'wp-cupon-whatsapp' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio"
                                       name="wpcw_coupon_type"
                                       value="public"
                                       <?php checked( $coupon->is_public_coupon(), true ); ?> />
                                <?php _e( 'Cupón Público', 'wp-cupon-whatsapp' ); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio"
                                       name="wpcw_coupon_type"
                                       value="standard"
                                       <?php checked( ! $coupon->is_loyalty_coupon() && ! $coupon->is_public_coupon(), true ); ?> />
                                <?php _e( 'Cupón Estándar', 'wp-cupon-whatsapp' ); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php _e( 'Los cupones de lealtad requieren membresía institucional. Los cupones públicos están disponibles para todos.', 'wp-cupon-whatsapp' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpcw_whatsapp_text">
                            <?php _e( 'Mensaje de WhatsApp', 'wp-cupon-whatsapp' ); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="wpcw_whatsapp_text"
                                  name="wpcw_whatsapp_text"
                                  rows="3"
                                  cols="50"
                                  placeholder="<?php esc_attr_e( '¡Hola! Tengo el cupón {coupon_code} y quiero canjearlo.', 'wp-cupon-whatsapp' ); ?>"><?php echo esc_textarea( $coupon->get_whatsapp_text() ); ?></textarea>
                        <p class="description">
                            <?php _e( 'Mensaje que se enviará por WhatsApp. Use {coupon_code} para el código del cupón.', 'wp-cupon-whatsapp' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpcw_redemption_hours">
                            <?php _e( 'Horario de Canje', 'wp-cupon-whatsapp' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text"
                               id="wpcw_redemption_hours"
                               name="wpcw_redemption_hours"
                               value="<?php echo esc_attr( $coupon->get_redemption_hours() ); ?>"
                               placeholder="Lunes a Viernes 9:00-18:00" />
                        <p class="description">
                            <?php _e( 'Horarios disponibles para canje (opcional)', 'wp-cupon-whatsapp' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpcw_expiry_reminder">
                            <?php _e( 'Recordatorio de Vencimiento (días)', 'wp-cupon-whatsapp' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number"
                               id="wpcw_expiry_reminder"
                               name="wpcw_expiry_reminder"
                               value="<?php echo esc_attr( $coupon->get_expiry_reminder() ); ?>"
                               min="0"
                               max="30" />
                        <p class="description">
                            <?php _e( 'Días antes del vencimiento para enviar recordatorio (0 = desactivado)', 'wp-cupon-whatsapp' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpcw_max_uses_per_user">
                            <?php _e( 'Máximo de Usos por Usuario', 'wp-cupon-whatsapp' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number"
                               id="wpcw_max_uses_per_user"
                               name="wpcw_max_uses_per_user"
                               value="<?php echo esc_attr( $coupon->get_max_uses_per_user() ); ?>"
                               min="0" />
                        <p class="description">
                            <?php _e( 'Límite de usos por usuario (0 = sin límite)', 'wp-cupon-whatsapp' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <style>
        .wpcw-coupon-settings .form-table th {
            width: 200px;
        }
        .wpcw-coupon-settings .description {
            font-style: italic;
            color: #666;
        }
        .wpcw-coupon-settings fieldset {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 5px;
        }
        .wpcw-coupon-settings fieldset label {
            display: block;
            margin-bottom: 5px;
        }
    </style>
    <?php
}

/**
 * Save coupon meta data
 *
 * @param int $post_id
 */
function wpcw_save_coupon_meta( $post_id ) {
    // Check if our nonce is set
    if ( ! isset( $_POST['wpcw_coupon_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid
    if ( ! wp_verify_nonce( $_POST['wpcw_coupon_meta_box_nonce'], 'wpcw_coupon_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check if it's a shop_coupon
    if ( get_post_type( $post_id ) !== 'shop_coupon' ) {
        return;
    }

    $coupon = wpcw_get_coupon( $post_id );

    // Save WPCW enabled
    $wpcw_enabled = isset( $_POST['wpcw_enabled'] ) ? 'yes' : 'no';
    $coupon->set_wpcw_enabled( $wpcw_enabled );

    // Save associated business
    if ( isset( $_POST['wpcw_associated_business_id'] ) ) {
        $business_id = absint( $_POST['wpcw_associated_business_id'] );
        $coupon->set_associated_business_id( $business_id );
    }

    // Save coupon type
    $coupon_type = isset( $_POST['wpcw_coupon_type'] ) ? sanitize_text_field( $_POST['wpcw_coupon_type'] ) : 'standard';

    $coupon->set_loyalty_coupon( $coupon_type === 'loyalty' );
    $coupon->set_public_coupon( $coupon_type === 'public' );

    // Save WhatsApp text
    if ( isset( $_POST['wpcw_whatsapp_text'] ) ) {
        $whatsapp_text = sanitize_textarea_field( $_POST['wpcw_whatsapp_text'] );
        $coupon->set_whatsapp_text( $whatsapp_text );
    }

    // Save redemption hours
    if ( isset( $_POST['wpcw_redemption_hours'] ) ) {
        $redemption_hours = sanitize_text_field( $_POST['wpcw_redemption_hours'] );
        $coupon->set_redemption_hours( $redemption_hours );
    }

    // Save expiry reminder
    if ( isset( $_POST['wpcw_expiry_reminder'] ) ) {
        $expiry_reminder = absint( $_POST['wpcw_expiry_reminder'] );
        $coupon->set_expiry_reminder( $expiry_reminder );
    }

    // Save max uses per user
    if ( isset( $_POST['wpcw_max_uses_per_user'] ) ) {
        $max_uses_per_user = absint( $_POST['wpcw_max_uses_per_user'] );
        $coupon->set_max_uses_per_user( $max_uses_per_user );
    }

    // Save the coupon
    $coupon->save();

    // Log the update
    WPCW_Logger::log( 'info', 'Coupon updated with WPCW settings', array(
        'coupon_id' => $post_id,
        'wpcw_enabled' => $wpcw_enabled,
        'coupon_type' => $coupon_type,
    ) );
}
add_action( 'save_post', 'wpcw_save_coupon_meta' );
