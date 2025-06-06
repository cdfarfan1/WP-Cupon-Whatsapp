<?php
/**
 * Custom Customer Fields for WP Cupon WhatsApp
 *
 * This file will handle custom customer fields for registration and My Account.
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Functions for custom customer fields will be added here.

/**
 * Add custom fields to the WooCommerce registration form.
 */
function wpcw_add_custom_register_fields() {
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="wpcw_dni_number"><?php esc_html_e( 'DNI', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="wpcw_dni_number" id="wpcw_dni_number" value="<?php echo ( ! empty( $_POST['wpcw_dni_number'] ) ) ? esc_attr( wp_unslash( $_POST['wpcw_dni_number'] ) ) : ''; ?>" required />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="wpcw_birth_date"><?php esc_html_e( 'Fecha de Nacimiento', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
        <input type="date" class="woocommerce-Input woocommerce-Input--text input-text" name="wpcw_birth_date" id="wpcw_birth_date" value="<?php echo ( ! empty( $_POST['wpcw_birth_date'] ) ) ? esc_attr( wp_unslash( $_POST['wpcw_birth_date'] ) ) : ''; ?>" required />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="wpcw_whatsapp_number"><?php esc_html_e( 'Número de WhatsApp', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
        <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="wpcw_whatsapp_number" id="wpcw_whatsapp_number" value="<?php echo ( ! empty( $_POST['wpcw_whatsapp_number'] ) ) ? esc_attr( wp_unslash( $_POST['wpcw_whatsapp_number'] ) ) : ''; ?>" required />
    </p>

    <?php
    // Institution Field
    $institutions = get_posts( array(
        'post_type' => 'wpcw_institution',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ) );

    if ( ! empty( $institutions ) ) {
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="wpcw_user_institution_id"><?php esc_html_e( 'Institución (Opcional)', 'wp-cupon-whatsapp' ); ?></label>
            <select name="wpcw_user_institution_id" id="wpcw_user_institution_id" class="woocommerce-Select select">
                <option value=""><?php esc_html_e( 'Selecciona tu institución', 'wp-cupon-whatsapp' ); ?></option>
                <?php foreach ( $institutions as $institution ) : ?>
                    <option value="<?php echo esc_attr( $institution->ID ); ?>" <?php selected( ! empty( $_POST['wpcw_user_institution_id'] ) ? sanitize_text_field( $_POST['wpcw_user_institution_id'] ) : '', $institution->ID ); ?>>
                        <?php echo esc_html( $institution->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    // Favorite Coupon Categories Field
    $coupon_categories = get_terms( array(
        'taxonomy' => 'wpcw_coupon_category',
        'hide_empty' => false,
    ) );

    if ( ! empty( $coupon_categories ) && ! is_wp_error( $coupon_categories ) ) {
        ?>
        <fieldset class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <legend><?php esc_html_e( 'Categorías de Cupones Favoritas (Opcional)', 'wp-cupon-whatsapp' ); ?></legend>
            <?php foreach ( $coupon_categories as $category ) : ?>
                <p class="form-row form-row-wide">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                        <input type="checkbox"
                               class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                               name="wpcw_user_favorite_coupon_categories[]"
                               id="wpcw_user_favorite_coupon_categories_<?php echo esc_attr( $category->term_id ); ?>"
                               value="<?php echo esc_attr( $category->term_id ); ?>"
                               <?php checked( ! empty( $_POST['wpcw_user_favorite_coupon_categories'] ) && is_array( $_POST['wpcw_user_favorite_coupon_categories'] ) && in_array( $category->term_id, array_map('sanitize_text_field', $_POST['wpcw_user_favorite_coupon_categories']) ) ); ?> />
                        <span><?php echo esc_html( $category->name ); ?></span>
                    </label>
                </p>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }
    ?>
    <?php
}
add_action( 'woocommerce_register_form', 'wpcw_add_custom_register_fields' );

/**
 * Validate the custom registration fields.
 *
 * @param string $username Current username.
 * @param string $email Current email.
 * @param WP_Error $validation_errors WP_Error object.
 * @return WP_Error
 */
function wpcw_validate_custom_register_fields( $username, $email, $validation_errors ) {
    if ( empty( $_POST['wpcw_dni_number'] ) ) {
        $validation_errors->add( 'wpcw_dni_error', __( 'Por favor, introduce tu DNI.', 'wp-cupon-whatsapp' ) );
    }

    if ( empty( $_POST['wpcw_birth_date'] ) ) {
        $validation_errors->add( 'wpcw_birth_date_error', __( 'Por favor, introduce tu fecha de nacimiento.', 'wp-cupon-whatsapp' ) );
    } elseif ( ! empty( $_POST['wpcw_birth_date'] ) ) {
        // Basic date validation (YYYY-MM-DD)
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $_POST['wpcw_birth_date'] ) ) {
            $validation_errors->add( 'wpcw_birth_date_format_error', __( 'Por favor, introduce una fecha de nacimiento válida (AAAA-MM-DD).', 'wp-cupon-whatsapp' ) );
        } else {
            // Check if date is a real date
            $date_parts = explode( '-', $_POST['wpcw_birth_date'] );
            if ( ! checkdate( $date_parts[1], $date_parts[2], $date_parts[0] ) ) {
                $validation_errors->add( 'wpcw_birth_date_invalid_error', __( 'Por favor, introduce una fecha de nacimiento real.', 'wp-cupon-whatsapp' ) );
            }
        }
    }

    if ( empty( $_POST['wpcw_whatsapp_number'] ) ) {
        $validation_errors->add( 'wpcw_whatsapp_number_error', __( 'Por favor, introduce tu número de WhatsApp.', 'wp-cupon-whatsapp' ) );
    } elseif ( ! empty( $_POST['wpcw_whatsapp_number'] ) ) {
        // Basic WhatsApp number validation (allows numbers, +, spaces, parentheses, hyphens)
        // This can be made more strict if needed
        if ( ! preg_match( '/^[0-9\s\+\(\)-]+$/', $_POST['wpcw_whatsapp_number'] ) ) {
            $validation_errors->add( 'wpcw_whatsapp_number_format_error', __( 'Por favor, introduce un número de WhatsApp válido.', 'wp-cupon-whatsapp' ) );
        }
    }

    // DNI basic validation (example: numbers and one optional letter at the end for some countries)
    if ( ! empty( $_POST['wpcw_dni_number'] ) && ! preg_match( '/^[0-9]{7,8}[A-Za-z]?$/', $_POST['wpcw_dni_number'] ) ) {
        // $validation_errors->add( 'wpcw_dni_format_error', __( 'Por favor, introduce un DNI válido (solo números o números seguidos de una letra).', 'wp-cupon-whatsapp' ) );
        // For now, a simple non-empty check is done above. This is a placeholder for more specific DNI logic if required by the client.
        // For a general plugin, DNI formats vary too much. Let's assume the initial non-empty check is sufficient for now,
        // or the client will specify the exact format.
    }


    return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wpcw_validate_custom_register_fields', 10, 3 );

/**
 * Save the custom registration fields.
 *
 * @param int $customer_id New customer ID.
 */
function wpcw_save_custom_register_fields( $customer_id ) {
    if ( isset( $_POST['wpcw_dni_number'] ) && ! empty( $_POST['wpcw_dni_number'] ) ) {
        update_user_meta( $customer_id, '_wpcw_dni_number', sanitize_text_field( $_POST['wpcw_dni_number'] ) );
    }

    if ( isset( $_POST['wpcw_birth_date'] ) && ! empty( $_POST['wpcw_birth_date'] ) ) {
        // Additional validation for date format before saving can be done here if paranoid,
        // but wpcw_validate_custom_register_fields should have caught major issues.
        update_user_meta( $customer_id, '_wpcw_birth_date', sanitize_text_field( $_POST['wpcw_birth_date'] ) );
    }

    if ( isset( $_POST['wpcw_whatsapp_number'] ) && ! empty( $_POST['wpcw_whatsapp_number'] ) ) {
        update_user_meta( $customer_id, '_wpcw_whatsapp_number', sanitize_text_field( $_POST['wpcw_whatsapp_number'] ) );
    }

    if ( isset( $_POST['wpcw_user_institution_id'] ) && ! empty( $_POST['wpcw_user_institution_id'] ) ) {
        update_user_meta( $customer_id, '_wpcw_user_institution_id', absint( $_POST['wpcw_user_institution_id'] ) );
    } else {
        // If it's optional and not set, or an empty value was submitted, ensure no old value persists if the field can be cleared.
        delete_user_meta( $customer_id, '_wpcw_user_institution_id' );
    }

    if ( isset( $_POST['wpcw_user_favorite_coupon_categories'] ) && is_array( $_POST['wpcw_user_favorite_coupon_categories'] ) ) {
        $sanitized_categories = array_map( 'absint', $_POST['wpcw_user_favorite_coupon_categories'] );
        update_user_meta( $customer_id, '_wpcw_user_favorite_coupon_categories', $sanitized_categories );
    } else {
        // If no categories are selected or the field isn't set, save an empty array or delete meta.
        // Saving an empty array can be useful to distinguish between "no selection" and "field never shown/processed".
        update_user_meta( $customer_id, '_wpcw_user_favorite_coupon_categories', array() );
    }
}
add_action( 'woocommerce_created_customer', 'wpcw_save_custom_register_fields' );

/**
 * Add custom fields to the WooCommerce "My Account" page (Edit Account section).
 */
function wpcw_add_custom_account_fields() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }

    $dni_number = get_user_meta( $user_id, '_wpcw_dni_number', true );
    $birth_date = get_user_meta( $user_id, '_wpcw_birth_date', true );
    $whatsapp_number = get_user_meta( $user_id, '_wpcw_whatsapp_number', true );
    ?>
    <fieldset>
        <legend><?php esc_html_e( 'Información Adicional', 'wp-cupon-whatsapp' ); ?></legend>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="wpcw_dni_number"><?php esc_html_e( 'DNI', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="wpcw_dni_number" id="wpcw_dni_number" value="<?php echo esc_attr( $dni_number ); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="wpcw_birth_date"><?php esc_html_e( 'Fecha de Nacimiento', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
            <input type="date" class="woocommerce-Input woocommerce-Input--text input-text" name="wpcw_birth_date" id="wpcw_birth_date" value="<?php echo esc_attr( $birth_date ); ?>" required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="wpcw_whatsapp_number"><?php esc_html_e( 'Número de WhatsApp', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
            <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="wpcw_whatsapp_number" id="wpcw_whatsapp_number" value="<?php echo esc_attr( $whatsapp_number ); ?>" required />
        </p>

        <?php
        // Institution Field
        $user_institution_id = get_user_meta( $user_id, '_wpcw_user_institution_id', true );
        $institutions = get_posts( array(
            'post_type' => 'wpcw_institution',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ) );

        if ( ! empty( $institutions ) ) {
            ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="wpcw_user_institution_id"><?php esc_html_e( 'Institución (Opcional)', 'wp-cupon-whatsapp' ); ?></label>
                <select name="wpcw_user_institution_id" id="wpcw_user_institution_id" class="woocommerce-Select select">
                    <option value=""><?php esc_html_e( 'Selecciona tu institución', 'wp-cupon-whatsapp' ); ?></option>
                    <?php foreach ( $institutions as $institution ) : ?>
                        <option value="<?php echo esc_attr( $institution->ID ); ?>" <?php selected( $user_institution_id, $institution->ID ); ?>>
                            <?php echo esc_html( $institution->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <?php
        }

        // Favorite Coupon Categories Field
        $user_favorite_categories = get_user_meta( $user_id, '_wpcw_user_favorite_coupon_categories', true );
        if ( ! is_array( $user_favorite_categories ) ) {
            $user_favorite_categories = array();
        }
        $coupon_categories = get_terms( array(
            'taxonomy' => 'wpcw_coupon_category',
            'hide_empty' => false,
        ) );

        if ( ! empty( $coupon_categories ) && ! is_wp_error( $coupon_categories ) ) {
            ?>
            <fieldset class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <legend><?php esc_html_e( 'Categorías de Cupones Favoritas (Opcional)', 'wp-cupon-whatsapp' ); ?></legend>
                <?php foreach ( $coupon_categories as $category ) : ?>
                    <p class="form-row form-row-wide">
                        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                            <input type="checkbox"
                                   class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
                                   name="wpcw_user_favorite_coupon_categories[]"
                                   id="wpcw_user_favorite_coupon_categories_<?php echo esc_attr( $category->term_id ); ?>"
                                   value="<?php echo esc_attr( $category->term_id ); ?>"
                                   <?php checked( in_array( $category->term_id, $user_favorite_categories ) ); ?> />
                            <span><?php echo esc_html( $category->name ); ?></span>
                        </label>
                    </p>
                <?php endforeach; ?>
            </fieldset>
            <?php
        }
        ?>
    </fieldset>
    <?php
}
add_action( 'woocommerce_edit_account_form', 'wpcw_add_custom_account_fields' );

/**
 * Validate the custom account fields when saving account details.
 *
 * @param WP_Error $errors WooCommerce errors object passed by reference.
 * @param WP_User  $user   The current user object.
 */
function wpcw_validate_custom_account_fields( &$errors, $user ) {
    if ( empty( $_POST['wpcw_dni_number'] ) ) {
        $errors->add( 'wpcw_dni_error', __( 'Por favor, introduce tu DNI.', 'wp-cupon-whatsapp' ) );
    }

    if ( empty( $_POST['wpcw_birth_date'] ) ) {
        $errors->add( 'wpcw_birth_date_error', __( 'Por favor, introduce tu fecha de nacimiento.', 'wp-cupon-whatsapp' ) );
    } elseif ( ! empty( $_POST['wpcw_birth_date'] ) ) {
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $_POST['wpcw_birth_date'] ) ) {
            $errors->add( 'wpcw_birth_date_format_error', __( 'Por favor, introduce una fecha de nacimiento válida (AAAA-MM-DD).', 'wp-cupon-whatsapp' ) );
        } else {
            $date_parts = explode( '-', $_POST['wpcw_birth_date'] );
            if ( ! checkdate( $date_parts[1], $date_parts[2], $date_parts[0] ) ) {
                $errors->add( 'wpcw_birth_date_invalid_error', __( 'Por favor, introduce una fecha de nacimiento real.', 'wp-cupon-whatsapp' ) );
            }
        }
    }

    if ( empty( $_POST['wpcw_whatsapp_number'] ) ) {
        $errors->add( 'wpcw_whatsapp_number_error', __( 'Por favor, introduce tu número de WhatsApp.', 'wp-cupon-whatsapp' ) );
    } elseif ( ! empty( $_POST['wpcw_whatsapp_number'] ) ) {
        if ( ! preg_match( '/^[0-9\s\+\(\)-]+$/', $_POST['wpcw_whatsapp_number'] ) ) {
            $errors->add( 'wpcw_whatsapp_number_format_error', __( 'Por favor, introduce un número de WhatsApp válido.', 'wp-cupon-whatsapp' ) );
        }
    }
    // DNI basic validation (example: numbers and one optional letter at the end for some countries)
    if ( ! empty( $_POST['wpcw_dni_number'] ) && ! preg_match( '/^[0-9]{7,8}[A-Za-z]?$/', $_POST['wpcw_dni_number'] ) ) {
        // $errors->add( 'wpcw_dni_format_error', __( 'Por favor, introduce un DNI válido (solo números o números seguidos de una letra).', 'wp-cupon-whatsapp' ) );
        // As with registration, keeping DNI validation simple unless specific format is required.
    }
}
add_action( 'woocommerce_save_account_details_errors', 'wpcw_validate_custom_account_fields', 10, 2 );

/**
 * Save the custom account fields.
 *
 * @param int $user_id Current user ID.
 */
function wpcw_save_custom_account_fields( $user_id ) {
    // DNI, Birth Date, and WhatsApp Number are marked as required in the form
    // and validated by wpcw_validate_custom_account_fields to not be empty.
    // So, we expect them to be set in $_POST if validation passed.

    if ( isset( $_POST['wpcw_dni_number'] ) ) { // Should always be set if validation passed
        update_user_meta( $user_id, '_wpcw_dni_number', sanitize_text_field( $_POST['wpcw_dni_number'] ) );
    }

    if ( isset( $_POST['wpcw_birth_date'] ) ) { // Should always be set if validation passed
        update_user_meta( $user_id, '_wpcw_birth_date', sanitize_text_field( $_POST['wpcw_birth_date'] ) );
    }

    if ( isset( $_POST['wpcw_whatsapp_number'] ) ) { // Should always be set if validation passed
        update_user_meta( $user_id, '_wpcw_whatsapp_number', sanitize_text_field( $_POST['wpcw_whatsapp_number'] ) );
    }

    // Institution (Optional)
    if ( isset( $_POST['wpcw_user_institution_id'] ) ) {
        if ( ! empty( $_POST['wpcw_user_institution_id'] ) ) {
            update_user_meta( $user_id, '_wpcw_user_institution_id', absint( $_POST['wpcw_user_institution_id'] ) );
        } else {
            // If the user selected the "Selecciona tu institución" (empty value)
            delete_user_meta( $user_id, '_wpcw_user_institution_id' );
        }
    }

    // Favorite Coupon Categories (Optional)
    if ( isset( $_POST['wpcw_user_favorite_coupon_categories'] ) && is_array( $_POST['wpcw_user_favorite_coupon_categories'] ) ) {
        $sanitized_categories = array_map( 'absint', $_POST['wpcw_user_favorite_coupon_categories'] );
        update_user_meta( $user_id, '_wpcw_user_favorite_coupon_categories', $sanitized_categories );
    } else {
        // If no checkboxes were checked, $_POST['wpcw_user_favorite_coupon_categories'] might not be set.
        // We save an empty array to clear previous selections.
        update_user_meta( $user_id, '_wpcw_user_favorite_coupon_categories', array() );
    }
}
add_action( 'woocommerce_save_account_details', 'wpcw_save_custom_account_fields', 10, 1 );
