# Gestión de Roles y Permisos

## Descripción General
El sistema implementa un sistema de roles y permisos personalizado que se integra con el sistema de roles de WordPress.

## Roles Definidos

### 1. Superadministrador
```php
// Capacidades del Superadministrador
$capabilities = [
    'manage_wpcw_settings',       // Gestionar configuración
    'manage_wpcw_coupons',        // Gestionar todos los cupones
    'manage_wpcw_businesses',     // Gestionar comercios
    'view_wpcw_reports',         // Ver reportes
    'export_wpcw_data',          // Exportar datos
    'manage_wpcw_templates'      // Gestionar plantillas
];
```

### 2. Administrador de Comercio
```php
// Capacidades del Administrador de Comercio
$business_capabilities = [
    'manage_own_wpcw_coupons',    // Gestionar cupones propios
    'view_own_wpcw_reports',      // Ver reportes propios
    'validate_wpcw_redemptions'   // Validar canjes
];
```

### 3. Cliente
```php
// Capacidades del Cliente
$customer_capabilities = [
    'view_wpcw_coupons',          // Ver cupones disponibles
    'redeem_wpcw_coupons',        // Canjear cupones
    'view_own_wpcw_history'       // Ver historial propio
];
```

## Implementación

### Registro de Roles
```php
class WPCW_Roles {
    public static function register_roles() {
        // Registrar rol de Administrador de Comercio
        add_role(
            'wpcw_business_admin',
            __('Administrador de Comercio', 'wp-cupon-whatsapp'),
            self::get_business_admin_capabilities()
        );
        
        // Añadir capacidades al rol de Cliente
        $customer = get_role('customer');
        foreach (self::get_customer_capabilities() as $cap) {
            $customer->add_cap($cap);
        }
    }
    
    private static function get_business_admin_capabilities() {
        return [
            'read' => true,
            'manage_own_wpcw_coupons' => true,
            'view_own_wpcw_reports' => true,
            'validate_wpcw_redemptions' => true
        ];
    }
}
```

### Verificación de Permisos
```php
class WPCW_Permissions {
    /**
     * Verifica si un usuario puede gestionar un cupón
     */
    public static function can_manage_coupon($user_id, $coupon_id) {
        if (user_can($user_id, 'manage_wpcw_coupons')) {
            return true;
        }
        
        if (user_can($user_id, 'manage_own_wpcw_coupons')) {
            $business_id = get_user_meta($user_id, '_wpcw_business_id', true);
            $coupon_business = get_post_meta($coupon_id, '_wpcw_business_id', true);
            return $business_id == $coupon_business;
        }
        
        return false;
    }
    
    /**
     * Verifica si un usuario puede validar un canje
     */
    public static function can_validate_redemption($user_id, $redemption_id) {
        if (user_can($user_id, 'manage_wpcw_coupons')) {
            return true;
        }
        
        if (user_can($user_id, 'validate_wpcw_redemptions')) {
            $business_id = get_user_meta($user_id, '_wpcw_business_id', true);
            $redemption_business = WPCW_Redemption::get_business_id($redemption_id);
            return $business_id == $redemption_business;
        }
        
        return false;
    }
}
```

## Metaboxes y UI

### Asignación de Comercio
```php
function wpcw_render_business_assignment_metabox($post) {
    // Verificar permisos
    if (!current_user_can('manage_wpcw_businesses')) {
        return;
    }
    
    $current_business = get_post_meta($post->ID, '_wpcw_business_id', true);
    
    // Obtener lista de comercios
    $businesses = get_posts([
        'post_type' => 'wpcw_business',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    ?>
    <select name="wpcw_business_id">
        <option value=""><?php _e('Seleccionar comercio...', 'wp-cupon-whatsapp'); ?></option>
        <?php foreach ($businesses as $business) : ?>
            <option value="<?php echo esc_attr($business->ID); ?>" 
                    <?php selected($current_business, $business->ID); ?>>
                <?php echo esc_html($business->post_title); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}
```

### Interfaz de Asignación de Roles
```php
function wpcw_render_role_assignment_page() {
    if (!current_user_can('manage_wpcw_settings')) {
        wp_die(__('No tienes permiso para acceder a esta página.', 'wp-cupon-whatsapp'));
    }
    
    ?>
    <div class="wrap">
        <h1><?php _e('Gestión de Roles', 'wp-cupon-whatsapp'); ?></h1>
        
        <form method="post" action="">
            <?php
            wp_nonce_field('wpcw_role_assignment');
            
            $users = get_users([
                'role__in' => ['wpcw_business_admin', 'customer']
            ]);
            
            foreach ($users as $user) {
                $business_id = get_user_meta($user->ID, '_wpcw_business_id', true);
                ?>
                <div class="user-role-assignment">
                    <h3><?php echo esc_html($user->display_name); ?></h3>
                    <select name="user_role[<?php echo $user->ID; ?>]">
                        <option value="customer" <?php selected(in_array('customer', $user->roles)); ?>>
                            <?php _e('Cliente', 'wp-cupon-whatsapp'); ?>
                        </option>
                        <option value="wpcw_business_admin" <?php selected(in_array('wpcw_business_admin', $user->roles)); ?>>
                            <?php _e('Administrador de Comercio', 'wp-cupon-whatsapp'); ?>
                        </option>
                    </select>
                </div>
                <?php
            }
            ?>
            <input type="submit" class="button button-primary" value="<?php _e('Guardar Cambios', 'wp-cupon-whatsapp'); ?>">
        </form>
    </div>
    <?php
}
```

## Filtros y Consultas

### Filtrar Contenido por Permisos
```php
function wpcw_filter_coupon_queries($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->get('post_type') !== 'shop_coupon') {
        return;
    }
    
    $user_id = get_current_user_id();
    
    if (user_can($user_id, 'manage_wpcw_coupons')) {
        return;
    }
    
    if (user_can($user_id, 'manage_own_wpcw_coupons')) {
        $business_id = get_user_meta($user_id, '_wpcw_business_id', true);
        
        $meta_query = $query->get('meta_query', []);
        $meta_query[] = [
            'key' => '_wpcw_business_id',
            'value' => $business_id
        ];
        
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'wpcw_filter_coupon_queries');
```

### Filtrar Menús Administrativos
```php
function wpcw_filter_admin_menu() {
    $user_id = get_current_user_id();
    
    if (!user_can($user_id, 'manage_wpcw_settings')) {
        remove_submenu_page('wpcw-main-menu', 'wpcw-settings');
    }
    
    if (!user_can($user_id, 'view_wpcw_reports')) {
        remove_submenu_page('wpcw-main-menu', 'wpcw-reports');
    }
}
add_action('admin_menu', 'wpcw_filter_admin_menu', 999);
```

## Hooks

### Filtros
```php
// Modificar capacidades
add_filter('wpcw_role_capabilities', function($capabilities, $role) {
    if ($role === 'wpcw_business_admin') {
        $capabilities[] = 'custom_capability';
    }
    return $capabilities;
}, 10, 2);

// Personalizar asignación de roles
add_filter('wpcw_user_role_assignment', function($role, $user_id) {
    // Lógica personalizada
    return $role;
}, 10, 2);
```

### Acciones
```php
// Al cambiar rol de usuario
add_action('wpcw_user_role_changed', function($user_id, $old_role, $new_role) {
    // Lógica al cambiar rol
}, 10, 3);

// Al asignar comercio
add_action('wpcw_business_assigned', function($user_id, $business_id) {
    // Lógica al asignar comercio
}, 10, 2);
```

## Migraciones y Actualización

### Actualizar Roles
```php
function wpcw_update_roles_and_capabilities() {
    // Obtener versión anterior
    $old_version = get_option('wpcw_version', '1.0.0');
    
    if (version_compare($old_version, '1.2.0', '<')) {
        // Añadir nuevas capacidades
        $admin_role = get_role('administrator');
        $admin_role->add_cap('new_capability');
        
        // Actualizar roles existentes
        $business_admins = get_users([
            'role' => 'wpcw_business_admin'
        ]);
        
        foreach ($business_admins as $user) {
            $user->add_cap('new_capability');
        }
    }
    
    update_option('wpcw_version', '1.2.0');
}
```

## Pruebas

### Casos de Prueba
```php
class WPCW_Roles_Test extends WP_UnitTestCase {
    public function test_role_capabilities() {
        $user_id = $this->factory->user->create([
            'role' => 'wpcw_business_admin'
        ]);
        
        $this->assertTrue(user_can($user_id, 'manage_own_wpcw_coupons'));
        $this->assertFalse(user_can($user_id, 'manage_wpcw_settings'));
    }
    
    public function test_business_assignment() {
        $user_id = $this->factory->user->create();
        $business_id = $this->factory->post->create([
            'post_type' => 'wpcw_business'
        ]);
        
        update_user_meta($user_id, '_wpcw_business_id', $business_id);
        
        $assigned_business = get_user_meta($user_id, '_wpcw_business_id', true);
        $this->assertEquals($business_id, $assigned_business);
    }
}
```
