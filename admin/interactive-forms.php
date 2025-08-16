<?php
/**
 * WP Cupón WhatsApp - Interactive Forms
 *
 * Handles interactive and editable forms for businesses, institutions, and customers
 * with role-based permissions and real-time validation.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Initialize interactive forms
 */
function wpcw_init_interactive_forms() {
    // Only initialize in admin area
    if ( ! is_admin() ) {
        return;
    }
    
    add_action( 'add_meta_boxes', 'wpcw_add_interactive_metaboxes' );
    add_action( 'save_post', 'wpcw_save_interactive_forms_data' );
    add_action( 'wp_ajax_wpcw_validate_field', 'wpcw_ajax_validate_field' );
    add_action( 'wp_ajax_wpcw_get_related_data', 'wpcw_ajax_get_related_data' );
    add_action( 'admin_enqueue_scripts', 'wpcw_enqueue_interactive_forms_assets' );
}
add_action( 'init', 'wpcw_init_interactive_forms' );

/**
 * Add interactive metaboxes for different post types
 */
function wpcw_add_interactive_metaboxes() {
    $screen = get_current_screen();
    
    // Business metaboxes
    if ( $screen && $screen->post_type === 'wpcw_business' ) {
        add_meta_box(
            'wpcw_business_details',
            __( 'Detalles del Comercio', 'wp-cupon-whatsapp' ),
            'wpcw_render_business_details_metabox',
            'wpcw_business',
            'normal',
            'high'
        );
        
        add_meta_box(
            'wpcw_business_contact',
            __( 'Información de Contacto', 'wp-cupon-whatsapp' ),
            'wpcw_render_business_contact_metabox',
            'wpcw_business',
            'normal',
            'high'
        );
        
        add_meta_box(
            'wpcw_business_settings',
            __( 'Configuración Avanzada', 'wp-cupon-whatsapp' ),
            'wpcw_render_business_settings_metabox',
            'wpcw_business',
            'side',
            'default'
        );
    }
    
    // Institution metaboxes
    if ( $screen && $screen->post_type === 'wpcw_institution' ) {
        add_meta_box(
            'wpcw_institution_details',
            __( 'Detalles de la Institución', 'wp-cupon-whatsapp' ),
            'wpcw_render_institution_details_metabox',
            'wpcw_institution',
            'normal',
            'high'
        );
        
        add_meta_box(
            'wpcw_institution_manager',
            __( 'Gestor de la Institución', 'wp-cupon-whatsapp' ),
            'wpcw_render_institution_manager_metabox',
            'wpcw_institution',
            'normal',
            'high'
        );
        
        add_meta_box(
            'wpcw_institution_permissions',
            __( 'Permisos y Configuración', 'wp-cupon-whatsapp' ),
            'wpcw_render_institution_permissions_metabox',
            'wpcw_institution',
            'side',
            'default'
        );
    }
    
    // Customer management metabox for users page
    add_meta_box(
        'wpcw_customer_details',
        __( 'Detalles del Cliente WPCW', 'wp-cupon-whatsapp' ),
        'wpcw_render_customer_details_metabox',
        'user',
        'normal',
        'high'
    );
}

/**
 * Render business details metabox
 */
function wpcw_render_business_details_metabox( $post ) {
    wp_nonce_field( 'wpcw_save_business_details', 'wpcw_business_details_nonce' );
    
    // Get current business category from taxonomy
    $current_categories = wp_get_object_terms( $post->ID, 'wpcw_business_category', array( 'fields' => 'ids' ) );
    $current_category_id = ! empty( $current_categories ) && ! is_wp_error( $current_categories ) ? $current_categories[0] : '';
    
    $business_data = array(
        'legal_name' => get_post_meta( $post->ID, '_wpcw_legal_name', true ),
        'fantasy_name' => get_post_meta( $post->ID, '_wpcw_fantasy_name', true ),
        'cuit' => get_post_meta( $post->ID, '_wpcw_cuit', true ),
        'category' => $current_category_id,
        'description' => get_post_meta( $post->ID, '_wpcw_business_description', true ),
        'website' => get_post_meta( $post->ID, '_wpcw_website', true ),
        'social_media' => get_post_meta( $post->ID, '_wpcw_social_media', true )
    );
    
    $current_user_can_edit = wpcw_user_can_edit_business( $post->ID );
    $readonly = $current_user_can_edit ? '' : 'readonly';
    
    ?>
    <div class="wpcw-interactive-form" data-post-type="business" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
        <table class="form-table wpcw-form-table">
            <tr>
                <th scope="row">
                    <label for="wpcw_legal_name"><?php _e( 'Razón Social', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_legal_name" 
                           name="wpcw_legal_name" 
                           value="<?php echo esc_attr( $business_data['legal_name'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="required|min:3|max:100" 
                           data-field="legal_name"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Nombre legal registrado del comercio', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_fantasy_name"><?php _e( 'Nombre de Fantasía', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_fantasy_name" 
                           name="wpcw_fantasy_name" 
                           value="<?php echo esc_attr( $business_data['fantasy_name'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="max:100" 
                           data-field="fantasy_name"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Nombre comercial o de fantasía', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_cuit"><?php _e( 'CUIT/CUIL', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_cuit" 
                           name="wpcw_cuit" 
                           value="<?php echo esc_attr( $business_data['cuit'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="required|cuit" 
                           data-field="cuit"
                           placeholder="XX-XXXXXXXX-X"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Número de CUIT o CUIL del comercio', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_category"><?php _e( 'Categoría', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <?php
                    $categories = get_terms( array(
                        'taxonomy' => 'wpcw_business_category',
                        'hide_empty' => false
                    ) );
                    ?>
                    <select id="wpcw_business_category" 
                            name="wpcw_business_category" 
                            class="wpcw-validate" 
                            data-field="category"
                            <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value=""><?php _e( 'Seleccionar categoría...', 'wp-cupon-whatsapp' ); ?></option>
                        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                            <?php foreach ( $categories as $category ) : ?>
                                <option value="<?php echo esc_attr( $category->term_id ); ?>" 
                                        <?php selected( $business_data['category'], $category->term_id ); ?>>
                                    <?php echo esc_html( $category->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_description"><?php _e( 'Descripción', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <textarea id="wpcw_business_description" 
                              name="wpcw_business_description" 
                              rows="4" 
                              class="large-text wpcw-validate" 
                              data-validation="max:500" 
                              data-field="description"
                              <?php echo $readonly; ?>><?php echo esc_textarea( $business_data['description'] ); ?></textarea>
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Descripción del comercio y sus servicios', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_website"><?php _e( 'Sitio Web', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="url" 
                           id="wpcw_website" 
                           name="wpcw_website" 
                           value="<?php echo esc_attr( $business_data['website'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="url" 
                           data-field="website"
                           placeholder="https://ejemplo.com"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
        </table>
        
        <?php if ( $current_user_can_edit ) : ?>
            <div class="wpcw-form-actions">
                <button type="button" class="button button-primary wpcw-save-draft">
                    <?php _e( 'Guardar Borrador', 'wp-cupon-whatsapp' ); ?>
                </button>
                <button type="button" class="button wpcw-validate-all">
                    <?php _e( 'Validar Todo', 'wp-cupon-whatsapp' ); ?>
                </button>
                <div class="wpcw-form-status"></div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render business contact metabox
 */
function wpcw_render_business_contact_metabox( $post ) {
    $contact_data = array(
        'email' => get_post_meta( $post->ID, '_wpcw_business_email', true ),
        'phone' => get_post_meta( $post->ID, '_wpcw_business_phone', true ),
        'whatsapp' => get_post_meta( $post->ID, '_wpcw_business_whatsapp', true ),
        'address' => get_post_meta( $post->ID, '_wpcw_business_address', true ),
        'city' => get_post_meta( $post->ID, '_wpcw_business_city', true ),
        'province' => get_post_meta( $post->ID, '_wpcw_business_province', true ),
        'postal_code' => get_post_meta( $post->ID, '_wpcw_business_postal_code', true )
    );
    
    $current_user_can_edit = wpcw_user_can_edit_business( $post->ID );
    $readonly = $current_user_can_edit ? '' : 'readonly';
    
    ?>
    <div class="wpcw-interactive-form" data-section="contact">
        <table class="form-table wpcw-form-table">
            <tr>
                <th scope="row">
                    <label for="wpcw_business_email"><?php _e( 'Email', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="email" 
                           id="wpcw_business_email" 
                           name="wpcw_business_email" 
                           value="<?php echo esc_attr( $contact_data['email'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="required|email" 
                           data-field="email"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_phone"><?php _e( 'Teléfono', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="tel" 
                           id="wpcw_business_phone" 
                           name="wpcw_business_phone" 
                           value="<?php echo esc_attr( $contact_data['phone'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="phone" 
                           data-field="phone"
                           placeholder="+54 11 1234-5678"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_whatsapp"><?php _e( 'WhatsApp', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="tel" 
                           id="wpcw_business_whatsapp" 
                           name="wpcw_business_whatsapp" 
                           value="<?php echo esc_attr( $contact_data['whatsapp'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="required|whatsapp" 
                           data-field="whatsapp"
                           placeholder="+54 9 11 1234-5678"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Número de WhatsApp para comunicación con clientes', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_address"><?php _e( 'Dirección', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <textarea id="wpcw_business_address" 
                              name="wpcw_business_address" 
                              rows="3" 
                              class="large-text wpcw-validate" 
                              data-validation="max:200" 
                              data-field="address"
                              <?php echo $readonly; ?>><?php echo esc_textarea( $contact_data['address'] ); ?></textarea>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_city"><?php _e( 'Ciudad', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_business_city" 
                           name="wpcw_business_city" 
                           value="<?php echo esc_attr( $contact_data['city'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="max:50" 
                           data-field="city"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_province"><?php _e( 'Provincia', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <select id="wpcw_business_province" 
                            name="wpcw_business_province" 
                            class="wpcw-validate" 
                            data-field="province"
                            <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value=""><?php _e( 'Seleccionar provincia...', 'wp-cupon-whatsapp' ); ?></option>
                        <?php
                        $provinces = wpcw_get_argentina_provinces();
                        foreach ( $provinces as $code => $name ) :
                        ?>
                            <option value="<?php echo esc_attr( $code ); ?>" 
                                    <?php selected( $contact_data['province'], $code ); ?>>
                                <?php echo esc_html( $name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_business_postal_code"><?php _e( 'Código Postal', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_business_postal_code" 
                           name="wpcw_business_postal_code" 
                           value="<?php echo esc_attr( $contact_data['postal_code'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="postal_code" 
                           data-field="postal_code"
                           placeholder="1234"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

/**
 * Render business settings metabox
 */
function wpcw_render_business_settings_metabox( $post ) {
    $settings_data = array(
        'status' => get_post_meta( $post->ID, '_wpcw_business_status', true ),
        'auto_approve_coupons' => get_post_meta( $post->ID, '_wpcw_auto_approve_coupons', true ),
        'max_coupons_per_month' => get_post_meta( $post->ID, '_wpcw_max_coupons_per_month', true ),
        'notification_preferences' => get_post_meta( $post->ID, '_wpcw_notification_preferences', true )
    );
    
    $current_user_can_edit = wpcw_user_can_edit_business_settings( $post->ID );
    $disabled = $current_user_can_edit ? '' : 'disabled';
    
    ?>
    <div class="wpcw-interactive-form" data-section="settings">
        <table class="form-table wpcw-form-table">
            <tr>
                <th scope="row">
                    <label for="wpcw_business_status"><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <select id="wpcw_business_status" 
                            name="wpcw_business_status" 
                            class="wpcw-validate" 
                            data-field="status"
                            <?php echo $disabled; ?>>
                        <option value="active" <?php selected( $settings_data['status'], 'active' ); ?>>
                            <?php _e( 'Activo', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="inactive" <?php selected( $settings_data['status'], 'inactive' ); ?>>
                            <?php _e( 'Inactivo', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="suspended" <?php selected( $settings_data['status'], 'suspended' ); ?>>
                            <?php _e( 'Suspendido', 'wp-cupon-whatsapp' ); ?>
                        </option>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <tr>
                <th scope="row">
                    <label for="wpcw_auto_approve_coupons"><?php _e( 'Auto-aprobar Cupones', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               id="wpcw_auto_approve_coupons" 
                               name="wpcw_auto_approve_coupons" 
                               value="1" 
                               <?php checked( $settings_data['auto_approve_coupons'], '1' ); ?>
                               <?php echo $disabled; ?> />
                        <?php _e( 'Aprobar automáticamente los cupones creados por este comercio', 'wp-cupon-whatsapp' ); ?>
                    </label>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_max_coupons_per_month"><?php _e( 'Límite Mensual de Cupones', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="wpcw_max_coupons_per_month" 
                           name="wpcw_max_coupons_per_month" 
                           value="<?php echo esc_attr( $settings_data['max_coupons_per_month'] ); ?>" 
                           class="small-text wpcw-validate" 
                           data-validation="number|min:0|max:1000" 
                           data-field="max_coupons"
                           min="0" 
                           max="1000"
                           <?php echo $disabled; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( '0 = sin límite', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php
}

/**
 * Check if user can edit business
 */
function wpcw_user_can_edit_business( $business_id ) {
    $current_user = wp_get_current_user();
    
    // Administrators can edit everything
    if ( current_user_can( 'manage_options' ) ) {
        return true;
    }
    
    // Business owners can edit their own business
    if ( in_array( 'wpcw_business_owner', $current_user->roles ) ) {
        $owner_user_id = get_post_meta( $business_id, '_wpcw_owner_user_id', true );
        return $owner_user_id == $current_user->ID;
    }
    
    // Business staff can edit basic info of their business
    if ( in_array( 'wpcw_business_staff', $current_user->roles ) ) {
        $business_user_id = get_user_meta( $current_user->ID, '_wpcw_business_id', true );
        return $business_user_id == $business_id;
    }
    
    return false;
}

/**
 * Check if user can edit business settings
 */
function wpcw_user_can_edit_business_settings( $business_id ) {
    $current_user = wp_get_current_user();
    
    // Only administrators and business owners can edit settings
    if ( current_user_can( 'manage_options' ) ) {
        return true;
    }
    
    if ( in_array( 'wpcw_business_owner', $current_user->roles ) ) {
        $owner_user_id = get_post_meta( $business_id, '_wpcw_owner_user_id', true );
        return $owner_user_id == $current_user->ID;
    }
    
    return false;
}

/**
 * Get Argentina provinces
 */
function wpcw_get_argentina_provinces() {
    return array(
        'CABA' => 'Ciudad Autónoma de Buenos Aires',
        'BA' => 'Buenos Aires',
        'CAT' => 'Catamarca',
        'CHA' => 'Chaco',
        'CHU' => 'Chubut',
        'COR' => 'Córdoba',
        'COR' => 'Corrientes',
        'ER' => 'Entre Ríos',
        'FOR' => 'Formosa',
        'JUJ' => 'Jujuy',
        'LP' => 'La Pampa',
        'LR' => 'La Rioja',
        'MEN' => 'Mendoza',
        'MIS' => 'Misiones',
        'NEU' => 'Neuquén',
        'RN' => 'Río Negro',
        'SAL' => 'Salta',
        'SJ' => 'San Juan',
        'SL' => 'San Luis',
        'SC' => 'Santa Cruz',
        'SF' => 'Santa Fe',
        'SE' => 'Santiago del Estero',
        'TF' => 'Tierra del Fuego',
        'TUC' => 'Tucumán'
    );
}

/**
 * Enqueue interactive forms assets
 */
function wpcw_enqueue_interactive_forms_assets( $hook ) {
    global $post_type;
    
    // Only load on relevant admin pages
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'user-edit.php', 'profile.php' ) ) ) {
        return;
    }
    
    if ( ! in_array( $post_type, array( 'wpcw_business', 'wpcw_institution' ) ) && ! in_array( $hook, array( 'user-edit.php', 'profile.php' ) ) ) {
        return;
    }
    
    $plugin_url = defined( 'WPCW_PLUGIN_URL' ) ? WPCW_PLUGIN_URL : plugin_dir_url( dirname( __FILE__ ) );
    $version = defined( 'WPCW_VERSION' ) ? WPCW_VERSION : '1.0.0';
    
    // Enqueue styles
    wp_enqueue_style(
        'wpcw-interactive-forms',
        $plugin_url . 'admin/css/interactive-forms.css',
        array(),
        $version
    );
    
    // Enqueue scripts
    wp_enqueue_script(
        'wpcw-interactive-forms',
        $plugin_url . 'admin/js/interactive-forms.js',
        array( 'jquery', 'wp-util' ),
        $version,
        true
    );
    
    // Localize script
    wp_localize_script( 'wpcw-interactive-forms', 'wpcwInteractiveForms', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'wpcw_interactive_forms' ),
        'messages' => array(
            'validating' => __( 'Validando...', 'wp-cupon-whatsapp' ),
            'valid' => __( 'Válido', 'wp-cupon-whatsapp' ),
            'invalid' => __( 'Inválido', 'wp-cupon-whatsapp' ),
            'required' => __( 'Este campo es obligatorio', 'wp-cupon-whatsapp' ),
            'email' => __( 'Ingrese un email válido', 'wp-cupon-whatsapp' ),
            'url' => __( 'Ingrese una URL válida', 'wp-cupon-whatsapp' ),
            'phone' => __( 'Ingrese un teléfono válido', 'wp-cupon-whatsapp' ),
            'cuit' => __( 'Ingrese un CUIT válido', 'wp-cupon-whatsapp' ),
            'saving' => __( 'Guardando...', 'wp-cupon-whatsapp' ),
            'saved' => __( 'Guardado exitosamente', 'wp-cupon-whatsapp' ),
            'error' => __( 'Error al guardar', 'wp-cupon-whatsapp' )
        )
    ) );
}

/**
 * AJAX field validation
 */
function wpcw_ajax_validate_field() {
    // Verify nonce for security
    if ( ! wp_verify_nonce( $_POST['nonce'], 'wpcw_interactive_forms' ) ) {
        wp_send_json_error( array( 'message' => __( 'Nonce verification failed', 'wp-cupon-whatsapp' ) ) );
    }
    
    $field = sanitize_text_field( $_POST['field'] );
    $value = sanitize_text_field( $_POST['value'] );
    $validation_rules = sanitize_text_field( $_POST['validation'] );
    $post_type = sanitize_text_field( $_POST['post_type'] );
    
    $result = wpcw_validate_field_value( $field, $value, $validation_rules, $post_type );
    
    if ( $result['valid'] ) {
        wp_send_json_success( $result );
    } else {
        wp_send_json_error( $result );
    }
}

/**
 * Validate field value
 */
function wpcw_validate_field_value( $field, $value, $validation_rules, $post_type ) {
    $rules = explode( '|', $validation_rules );
    $errors = array();
    
    foreach ( $rules as $rule ) {
        $rule_parts = explode( ':', $rule );
        $rule_name = $rule_parts[0];
        $rule_value = isset( $rule_parts[1] ) ? $rule_parts[1] : null;
        
        switch ( $rule_name ) {
            case 'required':
                if ( empty( $value ) ) {
                    $errors[] = __( 'Este campo es obligatorio', 'wp-cupon-whatsapp' );
                }
                break;
                
            case 'email':
                if ( ! empty( $value ) && ! is_email( $value ) ) {
                    $errors[] = __( 'Ingrese un email válido', 'wp-cupon-whatsapp' );
                }
                break;
                
            case 'url':
                if ( ! empty( $value ) && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
                    $errors[] = __( 'Ingrese una URL válida', 'wp-cupon-whatsapp' );
                }
                break;
                
            case 'phone':
            case 'whatsapp':
                if ( ! empty( $value ) && ! wpcw_validate_phone( $value ) ) {
                    $errors[] = __( 'Ingrese un teléfono válido', 'wp-cupon-whatsapp' );
                }
                break;
                
            case 'cuit':
                if ( ! empty( $value ) && ! wpcw_validate_cuit( $value ) ) {
                    $errors[] = __( 'Ingrese un CUIT válido', 'wp-cupon-whatsapp' );
                }
                break;
                
            case 'min':
                if ( ! empty( $value ) && strlen( $value ) < intval( $rule_value ) ) {
                    $errors[] = sprintf( __( 'Mínimo %d caracteres', 'wp-cupon-whatsapp' ), $rule_value );
                }
                break;
                
            case 'max':
                if ( ! empty( $value ) && strlen( $value ) > intval( $rule_value ) ) {
                    $errors[] = sprintf( __( 'Máximo %d caracteres', 'wp-cupon-whatsapp' ), $rule_value );
                }
                break;
                
            case 'number':
                if ( ! empty( $value ) && ! is_numeric( $value ) ) {
                    $errors[] = __( 'Debe ser un número', 'wp-cupon-whatsapp' );
                }
                break;
                
            case 'postal_code':
                if ( ! empty( $value ) && ! preg_match( '/^[0-9]{4}$/', $value ) ) {
                    $errors[] = __( 'Código postal debe tener 4 dígitos', 'wp-cupon-whatsapp' );
                }
                break;
        }
    }
    
    return array(
        'valid' => empty( $errors ),
        'errors' => $errors,
        'field' => $field
    );
}

/**
 * Validate phone number
 */
function wpcw_validate_phone( $phone ) {
    // Remove all non-numeric characters except +
    $clean_phone = preg_replace( '/[^0-9+]/', '', $phone );
    
    // Check if it's a valid Argentine phone number
    return preg_match( '/^\+?54?[0-9]{10,11}$/', $clean_phone );
}

/**
 * Validate CUIT
 */
function wpcw_validate_cuit( $cuit ) {
    // Remove all non-numeric characters
    $clean_cuit = preg_replace( '/[^0-9]/', '', $cuit );
    
    // Check if it has 11 digits
    if ( strlen( $clean_cuit ) !== 11 ) {
        return false;
    }
    
    // Validate CUIT algorithm
    $multipliers = array( 5, 4, 3, 2, 7, 6, 5, 4, 3, 2 );
    $sum = 0;
    
    for ( $i = 0; $i < 10; $i++ ) {
        $sum += intval( $clean_cuit[$i] ) * $multipliers[$i];
    }
    
    $remainder = $sum % 11;
    $check_digit = $remainder < 2 ? $remainder : 11 - $remainder;
    
    return intval( $clean_cuit[10] ) === $check_digit;
}

/**
 * Save interactive forms data
 */
function wpcw_save_interactive_forms_data( $post_id ) {
    // Check if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    // Check user permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    $post_type = get_post_type( $post_id );
    
    if ( $post_type === 'wpcw_business' ) {
        wpcw_save_business_data( $post_id );
    } elseif ( $post_type === 'wpcw_institution' ) {
        wpcw_save_institution_data( $post_id );
    }
}

/**
 * Save customer data
 */
function wpcw_save_customer_data( $user_id ) {
    // Check permissions
    if ( ! current_user_can( 'edit_users' ) ) {
        return;
    }
    
    // Customer fields
    $customer_fields = array(
        '_wpcw_dni' => 'wpcw_customer_dni',
        '_wpcw_birth_date' => 'wpcw_customer_birth_date',
        '_wpcw_whatsapp' => 'wpcw_customer_whatsapp',
        '_wpcw_institution_id' => 'wpcw_customer_institution',
        '_wpcw_student_id' => 'wpcw_customer_student_id',
        '_wpcw_customer_status' => 'wpcw_customer_status'
    );
    
    foreach ( $customer_fields as $meta_key => $post_key ) {
        if ( isset( $_POST[$post_key] ) ) {
            $value = sanitize_text_field( $_POST[$post_key] );
            update_user_meta( $user_id, $meta_key, $value );
        }
    }
    
    // Handle favorite categories array
    if ( isset( $_POST['wpcw_customer_favorite_categories'] ) && is_array( $_POST['wpcw_customer_favorite_categories'] ) ) {
        $categories = array_map( 'intval', $_POST['wpcw_customer_favorite_categories'] );
        update_user_meta( $user_id, '_wpcw_favorite_coupon_categories', $categories );
    } else {
        delete_user_meta( $user_id, '_wpcw_favorite_coupon_categories' );
    }
}

/**
 * Render institution details metabox
 */
function wpcw_render_institution_details_metabox( $post ) {
    wp_nonce_field( 'wpcw_save_institution_details', 'wpcw_institution_details_nonce' );
    
    $institution_data = array(
        'legal_name' => get_post_meta( $post->ID, '_wpcw_institution_legal_name', true ),
        'short_name' => get_post_meta( $post->ID, '_wpcw_institution_short_name', true ),
        'type' => get_post_meta( $post->ID, '_wpcw_institution_type', true ),
        'cuit' => get_post_meta( $post->ID, '_wpcw_institution_cuit', true ),
        'description' => get_post_meta( $post->ID, '_wpcw_institution_description', true ),
        'website' => get_post_meta( $post->ID, '_wpcw_institution_website', true ),
        'logo_url' => get_post_meta( $post->ID, '_wpcw_institution_logo_url', true )
    );
    
    $current_user_can_edit = wpcw_user_can_edit_institution( $post->ID );
    $readonly = $current_user_can_edit ? '' : 'readonly';
    
    ?>
    <div class="wpcw-interactive-form" data-post-type="institution" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
        <table class="form-table wpcw-form-table">
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_legal_name"><?php _e( 'Nombre Legal', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_institution_legal_name" 
                           name="wpcw_institution_legal_name" 
                           value="<?php echo esc_attr( $institution_data['legal_name'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="required|min:3|max:150" 
                           data-field="legal_name"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Nombre legal registrado de la institución', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_short_name"><?php _e( 'Nombre Corto', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_institution_short_name" 
                           name="wpcw_institution_short_name" 
                           value="<?php echo esc_attr( $institution_data['short_name'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="max:50" 
                           data-field="short_name"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Nombre corto o siglas de la institución', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_type"><?php _e( 'Tipo de Institución', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <select id="wpcw_institution_type" 
                            name="wpcw_institution_type" 
                            class="wpcw-validate" 
                            data-validation="required" 
                            data-field="type"
                            <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value=""><?php _e( 'Seleccionar tipo...', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="university" <?php selected( $institution_data['type'], 'university' ); ?>>
                            <?php _e( 'Universidad', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="school" <?php selected( $institution_data['type'], 'school' ); ?>>
                            <?php _e( 'Escuela/Colegio', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="institute" <?php selected( $institution_data['type'], 'institute' ); ?>>
                            <?php _e( 'Instituto', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="organization" <?php selected( $institution_data['type'], 'organization' ); ?>>
                            <?php _e( 'Organización', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="government" <?php selected( $institution_data['type'], 'government' ); ?>>
                            <?php _e( 'Entidad Gubernamental', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="other" <?php selected( $institution_data['type'], 'other' ); ?>>
                            <?php _e( 'Otro', 'wp-cupon-whatsapp' ); ?>
                        </option>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_cuit"><?php _e( 'CUIT', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_institution_cuit" 
                           name="wpcw_institution_cuit" 
                           value="<?php echo esc_attr( $institution_data['cuit'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="cuit" 
                           data-field="cuit"
                           placeholder="XX-XXXXXXXX-X"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Número de CUIT de la institución (opcional)', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_description"><?php _e( 'Descripción', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <textarea id="wpcw_institution_description" 
                              name="wpcw_institution_description" 
                              rows="4" 
                              class="large-text wpcw-validate" 
                              data-validation="max:1000" 
                              data-field="description"
                              <?php echo $readonly; ?>><?php echo esc_textarea( $institution_data['description'] ); ?></textarea>
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Descripción de la institución y sus actividades', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_website"><?php _e( 'Sitio Web', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="url" 
                           id="wpcw_institution_website" 
                           name="wpcw_institution_website" 
                           value="<?php echo esc_attr( $institution_data['website'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="url" 
                           data-field="website"
                           placeholder="https://ejemplo.edu.ar"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_logo_url"><?php _e( 'Logo (URL)', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="url" 
                           id="wpcw_institution_logo_url" 
                           name="wpcw_institution_logo_url" 
                           value="<?php echo esc_attr( $institution_data['logo_url'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="url" 
                           data-field="logo_url"
                           placeholder="https://ejemplo.edu.ar/logo.png"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'URL del logo de la institución', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
        </table>
        
        <?php if ( $current_user_can_edit ) : ?>
            <div class="wpcw-form-actions">
                <button type="button" class="button button-primary wpcw-save-draft">
                    <?php _e( 'Guardar Borrador', 'wp-cupon-whatsapp' ); ?>
                </button>
                <button type="button" class="button wpcw-validate-all">
                    <?php _e( 'Validar Todo', 'wp-cupon-whatsapp' ); ?>
                </button>
                <div class="wpcw-form-status"></div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Render institution manager metabox
 */
function wpcw_render_institution_manager_metabox( $post ) {
    $manager_data = array(
        'manager_user_id' => get_post_meta( $post->ID, '_wpcw_manager_user_id', true ),
        'contact_email' => get_post_meta( $post->ID, '_wpcw_institution_contact_email', true ),
        'contact_phone' => get_post_meta( $post->ID, '_wpcw_institution_contact_phone', true ),
        'address' => get_post_meta( $post->ID, '_wpcw_institution_address', true ),
        'city' => get_post_meta( $post->ID, '_wpcw_institution_city', true ),
        'province' => get_post_meta( $post->ID, '_wpcw_institution_province', true ),
        'postal_code' => get_post_meta( $post->ID, '_wpcw_institution_postal_code', true )
    );
    
    $current_user_can_edit = wpcw_user_can_edit_institution( $post->ID );
    $readonly = $current_user_can_edit ? '' : 'readonly';
    
    ?>
    <div class="wpcw-interactive-form" data-section="manager">
        <table class="form-table wpcw-form-table">
            <tr>
                <th scope="row">
                    <label for="wpcw_manager_user_id"><?php _e( 'Gestor Asignado', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <?php
                    $users = get_users( array(
                        'role__in' => array( 'wpcw_institution_manager', 'administrator' ),
                        'orderby' => 'display_name'
                    ) );
                    ?>
                    <select id="wpcw_manager_user_id" 
                            name="wpcw_manager_user_id" 
                            class="wpcw-validate" 
                            data-field="manager_user_id"
                            data-autocomplete="users"
                            <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value=""><?php _e( 'Seleccionar gestor...', 'wp-cupon-whatsapp' ); ?></option>
                        <?php foreach ( $users as $user ) : ?>
                            <option value="<?php echo esc_attr( $user->ID ); ?>" 
                                    <?php selected( $manager_data['manager_user_id'], $user->ID ); ?>>
                                <?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Usuario responsable de gestionar esta institución', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_contact_email"><?php _e( 'Email de Contacto', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="email" 
                           id="wpcw_institution_contact_email" 
                           name="wpcw_institution_contact_email" 
                           value="<?php echo esc_attr( $manager_data['contact_email'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="required|email" 
                           data-field="contact_email"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_contact_phone"><?php _e( 'Teléfono de Contacto', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="tel" 
                           id="wpcw_institution_contact_phone" 
                           name="wpcw_institution_contact_phone" 
                           value="<?php echo esc_attr( $manager_data['contact_phone'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="phone" 
                           data-field="contact_phone"
                           placeholder="+54 11 1234-5678"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_address"><?php _e( 'Dirección', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <textarea id="wpcw_institution_address" 
                              name="wpcw_institution_address" 
                              rows="3" 
                              class="large-text wpcw-validate" 
                              data-validation="max:200" 
                              data-field="address"
                              <?php echo $readonly; ?>><?php echo esc_textarea( $manager_data['address'] ); ?></textarea>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_city"><?php _e( 'Ciudad', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_institution_city" 
                           name="wpcw_institution_city" 
                           value="<?php echo esc_attr( $manager_data['city'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="max:50" 
                           data-field="city"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_province"><?php _e( 'Provincia', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <select id="wpcw_institution_province" 
                            name="wpcw_institution_province" 
                            class="wpcw-validate" 
                            data-field="province"
                            <?php echo $readonly ? 'disabled' : ''; ?>>
                        <option value=""><?php _e( 'Seleccionar provincia...', 'wp-cupon-whatsapp' ); ?></option>
                        <?php
                        $provinces = wpcw_get_argentina_provinces();
                        foreach ( $provinces as $code => $name ) :
                        ?>
                            <option value="<?php echo esc_attr( $code ); ?>" 
                                    <?php selected( $manager_data['province'], $code ); ?>>
                                <?php echo esc_html( $name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_postal_code"><?php _e( 'Código Postal', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_institution_postal_code" 
                           name="wpcw_institution_postal_code" 
                           value="<?php echo esc_attr( $manager_data['postal_code'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="postal_code" 
                           data-field="postal_code"
                           placeholder="1234"
                           <?php echo $readonly; ?> />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

/**
 * Render institution permissions metabox
 */
function wpcw_render_institution_permissions_metabox( $post ) {
    $permissions_data = array(
        'status' => get_post_meta( $post->ID, '_wpcw_institution_status', true ),
        'max_students' => get_post_meta( $post->ID, '_wpcw_max_students', true ),
        'coupon_categories' => get_post_meta( $post->ID, '_wpcw_allowed_coupon_categories', true ),
        'auto_approve_students' => get_post_meta( $post->ID, '_wpcw_auto_approve_students', true )
    );
    
    $current_user_can_edit = wpcw_user_can_edit_institution_settings( $post->ID );
    $disabled = $current_user_can_edit ? '' : 'disabled';
    
    ?>
    <div class="wpcw-interactive-form" data-section="permissions">
        <table class="form-table wpcw-form-table">
            <tr>
                <th scope="row">
                    <label for="wpcw_institution_status"><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <select id="wpcw_institution_status" 
                            name="wpcw_institution_status" 
                            class="wpcw-validate" 
                            data-field="status"
                            <?php echo $disabled; ?>>
                        <option value="active" <?php selected( $permissions_data['status'], 'active' ); ?>>
                            <?php _e( 'Activa', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="inactive" <?php selected( $permissions_data['status'], 'inactive' ); ?>>
                            <?php _e( 'Inactiva', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="suspended" <?php selected( $permissions_data['status'], 'suspended' ); ?>>
                            <?php _e( 'Suspendida', 'wp-cupon-whatsapp' ); ?>
                        </option>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <tr>
                <th scope="row">
                    <label for="wpcw_max_students"><?php _e( 'Límite de Estudiantes', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="wpcw_max_students" 
                           name="wpcw_max_students" 
                           value="<?php echo esc_attr( $permissions_data['max_students'] ); ?>" 
                           class="small-text wpcw-validate" 
                           data-validation="number|min:0|max:50000" 
                           data-field="max_students"
                           min="0" 
                           max="50000"
                           <?php echo $disabled; ?> />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( '0 = sin límite', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_allowed_coupon_categories"><?php _e( 'Categorías de Cupón Permitidas', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <?php
                    $coupon_categories = get_terms( array(
                        'taxonomy' => 'wpcw_coupon_category',
                        'hide_empty' => false
                    ) );
                    $selected_categories = is_array( $permissions_data['coupon_categories'] ) ? $permissions_data['coupon_categories'] : array();
                    ?>
                    <div class="wpcw-checkbox-group">
                        <?php if ( ! empty( $coupon_categories ) && ! is_wp_error( $coupon_categories ) ) : ?>
                            <?php foreach ( $coupon_categories as $category ) : ?>
                                <label>
                                    <input type="checkbox" 
                                           name="wpcw_allowed_coupon_categories[]" 
                                           value="<?php echo esc_attr( $category->term_id ); ?>" 
                                           <?php checked( in_array( $category->term_id, $selected_categories ) ); ?>
                                           <?php echo $disabled; ?> />
                                    <?php echo esc_html( $category->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="description"><?php _e( 'No hay categorías de cupón disponibles.', 'wp-cupon-whatsapp' ); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Categorías de cupones que pueden usar los estudiantes de esta institución', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_auto_approve_students"><?php _e( 'Auto-aprobar Estudiantes', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               id="wpcw_auto_approve_students" 
                               name="wpcw_auto_approve_students" 
                               value="1" 
                               <?php checked( $permissions_data['auto_approve_students'], '1' ); ?>
                               <?php echo $disabled; ?> />
                        <?php _e( 'Aprobar automáticamente a los estudiantes que se registren con esta institución', 'wp-cupon-whatsapp' ); ?>
                    </label>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php
}

/**
 * Render customer details metabox
 */
function wpcw_render_customer_details_metabox( $user ) {
    if ( ! current_user_can( 'edit_users' ) ) {
        return;
    }
    
    $customer_data = array(
        'dni' => get_user_meta( $user->ID, '_wpcw_dni', true ),
        'birth_date' => get_user_meta( $user->ID, '_wpcw_birth_date', true ),
        'whatsapp' => get_user_meta( $user->ID, '_wpcw_whatsapp', true ),
        'institution_id' => get_user_meta( $user->ID, '_wpcw_institution_id', true ),
        'favorite_categories' => get_user_meta( $user->ID, '_wpcw_favorite_coupon_categories', true ),
        'student_id' => get_user_meta( $user->ID, '_wpcw_student_id', true ),
        'status' => get_user_meta( $user->ID, '_wpcw_customer_status', true )
    );
    
    ?>
    <div class="wpcw-interactive-form" data-post-type="customer" data-post-id="<?php echo esc_attr( $user->ID ); ?>">
        <table class="form-table wpcw-form-table">
            <tr>
                <th scope="row">
                    <label for="wpcw_customer_dni"><?php _e( 'DNI', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_customer_dni" 
                           name="wpcw_customer_dni" 
                           value="<?php echo esc_attr( $customer_data['dni'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="dni" 
                           data-field="dni" />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_customer_birth_date"><?php _e( 'Fecha de Nacimiento', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="date" 
                           id="wpcw_customer_birth_date" 
                           name="wpcw_customer_birth_date" 
                           value="<?php echo esc_attr( $customer_data['birth_date'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="date" 
                           data-field="birth_date" />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_customer_whatsapp"><?php _e( 'WhatsApp', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="tel" 
                           id="wpcw_customer_whatsapp" 
                           name="wpcw_customer_whatsapp" 
                           value="<?php echo esc_attr( $customer_data['whatsapp'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="whatsapp" 
                           data-field="whatsapp"
                           placeholder="+54 9 11 1234-5678" />
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_customer_institution"><?php _e( 'Institución', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <?php
                    $institutions = get_posts( array(
                        'post_type' => 'wpcw_institution',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ) );
                    ?>
                    <select id="wpcw_customer_institution" 
                            name="wpcw_customer_institution" 
                            class="wpcw-validate" 
                            data-field="institution_id"
                            data-autocomplete="institutions">
                        <option value=""><?php _e( 'Seleccionar institución...', 'wp-cupon-whatsapp' ); ?></option>
                        <?php foreach ( $institutions as $institution ) : ?>
                            <option value="<?php echo esc_attr( $institution->ID ); ?>" 
                                    <?php selected( $customer_data['institution_id'], $institution->ID ); ?>>
                                <?php echo esc_html( $institution->post_title ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_customer_student_id"><?php _e( 'Número de Estudiante', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="wpcw_customer_student_id" 
                           name="wpcw_customer_student_id" 
                           value="<?php echo esc_attr( $customer_data['student_id'] ); ?>" 
                           class="regular-text wpcw-validate" 
                           data-validation="max:50" 
                           data-field="student_id" />
                    <div class="wpcw-field-feedback"></div>
                    <p class="description"><?php _e( 'Número de legajo o matrícula del estudiante', 'wp-cupon-whatsapp' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wpcw_customer_status"><?php _e( 'Estado del Cliente', 'wp-cupon-whatsapp' ); ?></label>
                </th>
                <td>
                    <select id="wpcw_customer_status" 
                            name="wpcw_customer_status" 
                            class="wpcw-validate" 
                            data-field="status">
                        <option value="active" <?php selected( $customer_data['status'], 'active' ); ?>>
                            <?php _e( 'Activo', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="inactive" <?php selected( $customer_data['status'], 'inactive' ); ?>>
                            <?php _e( 'Inactivo', 'wp-cupon-whatsapp' ); ?>
                        </option>
                        <option value="suspended" <?php selected( $customer_data['status'], 'suspended' ); ?>>
                            <?php _e( 'Suspendido', 'wp-cupon-whatsapp' ); ?>
                        </option>
                    </select>
                    <div class="wpcw-field-feedback"></div>
                </td>
            </tr>
        </table>
        
        <div class="wpcw-form-actions">
            <button type="button" class="button button-primary wpcw-save-draft">
                <?php _e( 'Guardar Cambios', 'wp-cupon-whatsapp' ); ?>
            </button>
            <button type="button" class="button wpcw-validate-all">
                <?php _e( 'Validar Todo', 'wp-cupon-whatsapp' ); ?>
            </button>
            <div class="wpcw-form-status"></div>
        </div>
    </div>
    <?php
}

/**
 * Check if user can edit institution
 */
function wpcw_user_can_edit_institution( $institution_id ) {
    $current_user = wp_get_current_user();
    
    // Administrators can edit everything
    if ( current_user_can( 'manage_options' ) ) {
        return true;
    }
    
    // Institution managers can edit their own institution
    if ( in_array( 'wpcw_institution_manager', $current_user->roles ) ) {
        $manager_user_id = get_post_meta( $institution_id, '_wpcw_manager_user_id', true );
        return $manager_user_id == $current_user->ID;
    }
    
    return false;
}

/**
 * Check if user can edit institution settings
 */
function wpcw_user_can_edit_institution_settings( $institution_id ) {
    // Only administrators can edit institution settings
    return current_user_can( 'manage_options' );
}

/**
 * Save business data
 */
function wpcw_save_business_data( $post_id ) {
    // Verify nonce
    if ( ! isset( $_POST['wpcw_business_details_nonce'] ) || 
         ! wp_verify_nonce( $_POST['wpcw_business_details_nonce'], 'wpcw_save_business_details' ) ) {
        return;
    }
    
    // Check permissions
    if ( ! wpcw_user_can_edit_business( $post_id ) ) {
        return;
    }
    
    // Business details
    $business_fields = array(
        '_wpcw_legal_name' => 'wpcw_legal_name',
        '_wpcw_fantasy_name' => 'wpcw_fantasy_name',
        '_wpcw_cuit' => 'wpcw_cuit',
        '_wpcw_business_description' => 'wpcw_business_description',
        '_wpcw_website' => 'wpcw_website'
    );
    
    foreach ( $business_fields as $meta_key => $post_key ) {
        if ( isset( $_POST[$post_key] ) ) {
            $value = sanitize_text_field( $_POST[$post_key] );
            update_post_meta( $post_id, $meta_key, $value );
        }
    }
    
    // Handle business category taxonomy
    if ( isset( $_POST['wpcw_business_category'] ) && ! empty( $_POST['wpcw_business_category'] ) ) {
        $category_id = absint( $_POST['wpcw_business_category'] );
        wp_set_object_terms( $post_id, array( $category_id ), 'wpcw_business_category' );
    } else {
        // Remove category if none selected
        wp_set_object_terms( $post_id, array(), 'wpcw_business_category' );
    }
    
    // Contact information
    $contact_fields = array(
        '_wpcw_business_email' => 'wpcw_business_email',
        '_wpcw_business_phone' => 'wpcw_business_phone',
        '_wpcw_business_whatsapp' => 'wpcw_business_whatsapp',
        '_wpcw_business_address' => 'wpcw_business_address',
        '_wpcw_business_city' => 'wpcw_business_city',
        '_wpcw_business_province' => 'wpcw_business_province',
        '_wpcw_business_postal_code' => 'wpcw_business_postal_code'
    );
    
    foreach ( $contact_fields as $meta_key => $post_key ) {
        if ( isset( $_POST[$post_key] ) ) {
            if ( $post_key === 'wpcw_business_address' ) {
                $value = sanitize_textarea_field( $_POST[$post_key] );
            } else {
                $value = sanitize_text_field( $_POST[$post_key] );
            }
            update_post_meta( $post_id, $meta_key, $value );
        }
    }
    
    // Settings (only for authorized users)
    if ( wpcw_user_can_edit_business_settings( $post_id ) ) {
        $settings_fields = array(
            '_wpcw_business_status' => 'wpcw_business_status',
            '_wpcw_auto_approve_coupons' => 'wpcw_auto_approve_coupons',
            '_wpcw_max_coupons_per_month' => 'wpcw_max_coupons_per_month'
        );
        
        foreach ( $settings_fields as $meta_key => $post_key ) {
            if ( isset( $_POST[$post_key] ) ) {
                if ( $post_key === 'wpcw_auto_approve_coupons' ) {
                    $value = $_POST[$post_key] === '1' ? '1' : '0';
                } else {
                    $value = sanitize_text_field( $_POST[$post_key] );
                }
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }
}

/**
 * Save institution data
 */
function wpcw_save_institution_data( $post_id ) {
    // Verify nonce
    if ( ! isset( $_POST['wpcw_institution_details_nonce'] ) || 
         ! wp_verify_nonce( $_POST['wpcw_institution_details_nonce'], 'wpcw_save_institution_details' ) ) {
        return;
    }
    
    // Check permissions
    if ( ! wpcw_user_can_edit_institution( $post_id ) ) {
        return;
    }
    
    // Institution details
    $institution_fields = array(
        '_wpcw_institution_legal_name' => 'wpcw_institution_legal_name',
        '_wpcw_institution_short_name' => 'wpcw_institution_short_name',
        '_wpcw_institution_type' => 'wpcw_institution_type',
        '_wpcw_institution_cuit' => 'wpcw_institution_cuit',
        '_wpcw_institution_description' => 'wpcw_institution_description',
        '_wpcw_institution_website' => 'wpcw_institution_website',
        '_wpcw_institution_logo_url' => 'wpcw_institution_logo_url'
    );
    
    foreach ( $institution_fields as $meta_key => $post_key ) {
        if ( isset( $_POST[$post_key] ) ) {
            if ( $post_key === 'wpcw_institution_description' ) {
                $value = sanitize_textarea_field( $_POST[$post_key] );
            } else {
                $value = sanitize_text_field( $_POST[$post_key] );
            }
            update_post_meta( $post_id, $meta_key, $value );
        }
    }
    
    // Manager and contact information
    $manager_fields = array(
        '_wpcw_manager_user_id' => 'wpcw_manager_user_id',
        '_wpcw_institution_contact_email' => 'wpcw_institution_contact_email',
        '_wpcw_institution_contact_phone' => 'wpcw_institution_contact_phone',
        '_wpcw_institution_address' => 'wpcw_institution_address',
        '_wpcw_institution_city' => 'wpcw_institution_city',
        '_wpcw_institution_province' => 'wpcw_institution_province',
        '_wpcw_institution_postal_code' => 'wpcw_institution_postal_code'
    );
    
    foreach ( $manager_fields as $meta_key => $post_key ) {
        if ( isset( $_POST[$post_key] ) ) {
            if ( $post_key === 'wpcw_institution_address' ) {
                $value = sanitize_textarea_field( $_POST[$post_key] );
            } else {
                $value = sanitize_text_field( $_POST[$post_key] );
            }
            update_post_meta( $post_id, $meta_key, $value );
        }
    }
    
    // Settings (only for authorized users)
    if ( wpcw_user_can_edit_institution_settings( $post_id ) ) {
        $settings_fields = array(
            '_wpcw_institution_status' => 'wpcw_institution_status',
            '_wpcw_max_students' => 'wpcw_max_students',
            '_wpcw_auto_approve_students' => 'wpcw_auto_approve_students'
        );
        
        foreach ( $settings_fields as $meta_key => $post_key ) {
            if ( isset( $_POST[$post_key] ) ) {
                if ( $post_key === 'wpcw_auto_approve_students' ) {
                    $value = $_POST[$post_key] === '1' ? '1' : '0';
                } else {
                    $value = sanitize_text_field( $_POST[$post_key] );
                }
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
        
        // Handle coupon categories array
        if ( isset( $_POST['wpcw_allowed_coupon_categories'] ) && is_array( $_POST['wpcw_allowed_coupon_categories'] ) ) {
            $categories = array_map( 'intval', $_POST['wpcw_allowed_coupon_categories'] );
            update_post_meta( $post_id, '_wpcw_allowed_coupon_categories', $categories );
        } else {
            delete_post_meta( $post_id, '_wpcw_allowed_coupon_categories' );
        }
    }
}

/**
 * AJAX get related data
 */
function wpcw_ajax_get_related_data() {
    // Verify nonce for security
    if ( ! wp_verify_nonce( $_POST['nonce'], 'wpcw_interactive_forms' ) ) {
        wp_send_json_error( array( 'message' => __( 'Nonce verification failed', 'wp-cupon-whatsapp' ) ) );
    }
    
    $data_type = sanitize_text_field( $_POST['data_type'] );
    $query = sanitize_text_field( $_POST['query'] );
    
    $results = array();
    
    switch ( $data_type ) {
        case 'businesses':
            $businesses = get_posts( array(
                'post_type' => 'wpcw_business',
                'post_status' => 'publish',
                's' => $query,
                'posts_per_page' => 10
            ) );
            
            foreach ( $businesses as $business ) {
                $results[] = array(
                    'id' => $business->ID,
                    'title' => $business->post_title,
                    'email' => get_post_meta( $business->ID, '_wpcw_business_email', true )
                );
            }
            break;
            
        case 'institutions':
            $institutions = get_posts( array(
                'post_type' => 'wpcw_institution',
                'post_status' => 'publish',
                's' => $query,
                'posts_per_page' => 10
            ) );
            
            foreach ( $institutions as $institution ) {
                $results[] = array(
                    'id' => $institution->ID,
                    'title' => $institution->post_title,
                    'manager' => get_post_meta( $institution->ID, '_wpcw_manager_user_id', true )
                );
            }
            break;
            
        case 'users':
            $users = get_users( array(
                'search' => '*' . $query . '*',
                'number' => 10,
                'fields' => array( 'ID', 'display_name', 'user_email' )
            ) );
            
            foreach ( $users as $user ) {
                $results[] = array(
                    'id' => $user->ID,
                    'title' => $user->display_name,
                    'email' => $user->user_email
                );
            }
            break;
    }
    
    wp_send_json_success( $results );
}

// Initialize interactive forms on WordPress init
add_action( 'init', 'wpcw_init_interactive_forms' );

// Hook save_post para guardar datos de comercios e instituciones
add_action( 'save_post', 'wpcw_handle_save_post', 10, 2 );

/**
 * Handle save post for business and institution post types
 */
function wpcw_handle_save_post( $post_id, $post ) {
    // Avoid infinite loops
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    // Check user permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    // Handle different post types
    switch ( $post->post_type ) {
        case 'wpcw_business':
            wpcw_save_business_data( $post_id );
            break;
        case 'wpcw_institution':
            wpcw_save_institution_data( $post_id );
            break;
    }
}