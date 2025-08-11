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
    $required_settings = get_option('wpcw_required_fields_settings', array());

    // DNI
    if ( !empty($required_settings['dni_number']) && $required_settings['dni_number'] === '1' ) {
        if ( empty($_POST['wpcw_dni_number']) ) {
            $validation_errors->add('wpcw_dni_error', __('Por favor, introduce tu DNI.', 'wp-cupon-whatsapp'));
        }
    }
    // DNI format validation (optional, if DNI is provided, even if not required)
    if ( !empty($_POST['wpcw_dni_number']) && !preg_match('/^[0-9]{7,8}[A-Za-z]?$/', $_POST['wpcw_dni_number']) && !preg_match('/^[0-9]{7,8}$/', $_POST['wpcw_dni_number']) ) {
         // Example: allows 7-8 digits, or 7-8 digits followed by a letter. Adjust regex as needed.
         // $validation_errors->add('wpcw_dni_format_error', __('El formato del DNI no es válido.', 'wp-cupon-whatsapp'));
    }


    // Fecha de Nacimiento
    $birth_date_value = isset($_POST['wpcw_birth_date']) ? trim($_POST['wpcw_birth_date']) : '';
    if ( !empty($required_settings['birth_date']) && $required_settings['birth_date'] === '1' ) {
        if ( empty($birth_date_value) ) {
            $validation_errors->add('wpcw_birth_date_error', __('Por favor, introduce tu Fecha de Nacimiento.', 'wp-cupon-whatsapp'));
        } elseif ( !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date_value) || !wp_checkdate((string)substr($birth_date_value, 5, 2), (string)substr($birth_date_value, 8, 2), (string)substr($birth_date_value, 0, 4), $birth_date_value) ) {
            $validation_errors->add('wpcw_birth_date_format_error', __('El formato de la Fecha de Nacimiento no es válido (YYYY-MM-DD) o la fecha es incorrecta.', 'wp-cupon-whatsapp'));
        }
    } elseif (!empty($birth_date_value)) { // Validar formato si se ingresa algo, aunque no sea obligatorio
         if ( !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date_value) || !wp_checkdate((string)substr($birth_date_value, 5, 2), (string)substr($birth_date_value, 8, 2), (string)substr($birth_date_value, 0, 4), $birth_date_value) ) {
            $validation_errors->add('wpcw_birth_date_format_error', __('El formato de la Fecha de Nacimiento no es válido (YYYY-MM-DD) o la fecha es incorrecta.', 'wp-cupon-whatsapp'));
        }
    }


    // Número de WhatsApp
    $whatsapp_number_value = isset($_POST['wpcw_whatsapp_number']) ? trim($_POST['wpcw_whatsapp_number']) : '';
    if ( !empty($required_settings['whatsapp_number']) && $required_settings['whatsapp_number'] === '1' ) {
        if ( empty($whatsapp_number_value) ) {
            $validation_errors->add('wpcw_whatsapp_error', __('Por favor, introduce tu Número de WhatsApp.', 'wp-cupon-whatsapp'));
        } elseif ( !preg_match('/^[\s0-9()+\-]+$/', $whatsapp_number_value) ) {
            $validation_errors->add('wpcw_whatsapp_format_error', __('El formato del Número de WhatsApp no es válido.', 'wp-cupon-whatsapp'));
        }
    } elseif (!empty($whatsapp_number_value)) { // Validar formato si se ingresa algo, aunque no sea obligatorio
        if ( !preg_match('/^[\s0-9()+\-]+$/', $whatsapp_number_value) ) {
            $validation_errors->add('wpcw_whatsapp_format_error', __('El formato del Número de WhatsApp no es válido.', 'wp-cupon-whatsapp'));
        }
    }

    // Para user_institution_id y user_favorite_coupon_categories, la validación de "obligatorio"
    // significaría que se debe seleccionar algo (no la opción por defecto para el select, o al menos un checkbox).
    // Esto se puede abordar si se descomentan esos campos en los ajustes y se define la lógica de "no vacío".

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
    $required_settings = get_option('wpcw_required_fields_settings', array());
    // $user_id = $user->ID; // No se usa directamente aquí, pero disponible.

    // DNI
    if ( !empty($required_settings['dni_number']) && $required_settings['dni_number'] === '1' ) {
        if ( empty($_POST['wpcw_dni_number']) ) {
            $errors->add('wpcw_dni_error', __('Por favor, introduce tu DNI.', 'wp-cupon-whatsapp'));
        }
    }
    // DNI format validation (optional)
    if ( !empty($_POST['wpcw_dni_number']) && !preg_match('/^[0-9]{7,8}[A-Za-z]?$/', $_POST['wpcw_dni_number']) && !preg_match('/^[0-9]{7,8}$/', $_POST['wpcw_dni_number']) ) {
        // $errors->add('wpcw_dni_format_error', __('El formato del DNI no es válido.', 'wp-cupon-whatsapp'));
    }


    // Fecha de Nacimiento
    $birth_date_value_acc = isset($_POST['wpcw_birth_date']) ? trim($_POST['wpcw_birth_date']) : '';
    if ( !empty($required_settings['birth_date']) && $required_settings['birth_date'] === '1' ) {
        if ( empty($birth_date_value_acc) ) {
            $errors->add('wpcw_birth_date_error', __('Por favor, introduce tu Fecha de Nacimiento.', 'wp-cupon-whatsapp'));
        } elseif ( !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date_value_acc) || !wp_checkdate((string)substr($birth_date_value_acc, 5, 2), (string)substr($birth_date_value_acc, 8, 2), (string)substr($birth_date_value_acc, 0, 4), $birth_date_value_acc) ) {
            $errors->add('wpcw_birth_date_format_error', __('El formato de la Fecha de Nacimiento no es válido (YYYY-MM-DD) o la fecha es incorrecta.', 'wp-cupon-whatsapp'));
        }
    } elseif (!empty($birth_date_value_acc)) { // Validar formato si se ingresa algo, aunque no sea obligatorio
         if ( !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date_value_acc) || !wp_checkdate((string)substr($birth_date_value_acc, 5, 2), (string)substr($birth_date_value_acc, 8, 2), (string)substr($birth_date_value_acc, 0, 4), $birth_date_value_acc) ) {
            $errors->add('wpcw_birth_date_format_error', __('El formato de la Fecha de Nacimiento no es válido (YYYY-MM-DD) o la fecha es incorrecta.', 'wp-cupon-whatsapp'));
        }
    }

    // Número de WhatsApp
    $whatsapp_number_value_acc = isset($_POST['wpcw_whatsapp_number']) ? trim($_POST['wpcw_whatsapp_number']) : '';
    if ( !empty($required_settings['whatsapp_number']) && $required_settings['whatsapp_number'] === '1' ) {
        if ( empty($whatsapp_number_value_acc) ) {
            $errors->add('wpcw_whatsapp_error', __('Por favor, introduce tu Número de WhatsApp.', 'wp-cupon-whatsapp'));
        } elseif ( !preg_match('/^[\s0-9()+\-]+$/', $whatsapp_number_value_acc) ) {
            $errors->add('wpcw_whatsapp_format_error', __('El formato del Número de WhatsApp no es válido.', 'wp-cupon-whatsapp'));
        }
    } elseif (!empty($whatsapp_number_value_acc)) { // Validar formato si se ingresa algo, aunque no sea obligatorio
        if ( !preg_match('/^[\s0-9()+\-]+$/', $whatsapp_number_value_acc) ) {
            $errors->add('wpcw_whatsapp_format_error', __('El formato del Número de WhatsApp no es válido.', 'wp-cupon-whatsapp'));
        }
    }

    // No se validan user_institution_id ni user_favorite_coupon_categories como obligatorios aquí,
    // ya que su obligatoriedad se define por si el admin los marca, pero su naturaleza es opcional en el perfil.
    // Si se quisiera hacerlos obligatorios desde el perfil, se necesitaría una lógica similar.
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
