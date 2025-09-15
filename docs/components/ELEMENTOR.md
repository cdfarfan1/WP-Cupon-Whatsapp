# Elementor Integration

## Overview
The plugin provides custom Elementor widgets for displaying coupons and integration forms.

## Widgets

### 1. Coupon List Widget

#### Registration
```php
class WPCW_Elementor_Coupons_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'wpcw_coupons_list';
    }
    
    public function get_title() {
        return __('WP Cupón Lista', 'wp-cupon-whatsapp');
    }
    
    public function get_icon() {
        return 'eicon-coupon';
    }
    
    public function get_categories() {
        return ['wp-cupon-whatsapp'];
    }
}
```

#### Controls
```php
protected function _register_controls() {
    $this->start_controls_section(
        'content_section',
        [
            'label' => __('Contenido', 'wp-cupon-whatsapp'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );

    $this->add_control(
        'show_expired',
        [
            'label' => __('Mostrar Vencidos', 'wp-cupon-whatsapp'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'no'
        ]
    );

    $this->end_controls_section();
}
```

#### Render
```php
protected function render() {
    $settings = $this->get_settings_for_display();
    
    $args = [
        'post_type' => 'shop_coupon',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_wpcw_enabled',
                'value' => '1'
            ]
        ]
    ];
    
    $coupons = new WP_Query($args);
    
    if ($coupons->have_posts()) {
        while ($coupons->have_posts()) {
            $coupons->the_post();
            $this->render_coupon(get_the_ID());
        }
    }
    
    wp_reset_postdata();
}
```

### 2. Registration Form Widget

#### Registration
```php
class WPCW_Elementor_Registration_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'wpcw_registration_form';
    }
    
    public function get_title() {
        return __('Formulario de Adhesión', 'wp-cupon-whatsapp');
    }
}
```

#### Form Handler
```php
protected function handle_form_submission() {
    if (!isset($_POST['wpcw_registration_nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['wpcw_registration_nonce'], 'wpcw_registration')) {
        return;
    }
    
    // Process form data
    $business_data = [
        'name' => sanitize_text_field($_POST['business_name']),
        'email' => sanitize_email($_POST['business_email']),
        'phone' => sanitize_text_field($_POST['business_phone'])
    ];
    
    // Create business
    $business_id = wp_insert_post([
        'post_type' => 'wpcw_business',
        'post_title' => $business_data['name'],
        'post_status' => 'draft'
    ]);
    
    if (!is_wp_error($business_id)) {
        // Add business meta
        update_post_meta($business_id, '_wpcw_business_email', $business_data['email']);
        update_post_meta($business_id, '_wpcw_business_phone', $business_data['phone']);
        
        // Notify admin
        do_action('wpcw_new_business_registration', $business_id);
    }
}
```

## Styling

### CSS Classes
```css
/* Coupon List Widget */
.wpcw-elementor-coupons-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

.wpcw-elementor-coupon-card {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Registration Form Widget */
.wpcw-elementor-registration-form {
    max-width: 600px;
    margin: 0 auto;
}

.wpcw-elementor-form-field {
    margin-bottom: 15px;
}
```

### Custom Style Controls
```php
protected function register_style_controls() {
    $this->start_controls_section(
        'style_section',
        [
            'label' => __('Estilos', 'wp-cupon-whatsapp'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]
    );

    $this->add_control(
        'card_background_color',
        [
            'label' => __('Color de Fondo', 'wp-cupon-whatsapp'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wpcw-elementor-coupon-card' => 'background-color: {{VALUE}}'
            ]
        ]
    );

    $this->end_controls_section();
}
```

## JavaScript Integration

### Widget Scripts
```javascript
class WPCWElementorHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                couponCard: '.wpcw-elementor-coupon-card',
                redeemButton: '.wpcw-redeem-button'
            }
        };
    }
    
    bindEvents() {
        this.elements.$redeemButton.on('click', this.handleRedemption.bind(this));
    }
    
    handleRedemption(event) {
        event.preventDefault();
        const couponId = event.currentTarget.dataset.couponId;
        
        // Handle redemption logic
    }
}
```

### Registration
```php
function register_widget_scripts() {
    wp_register_script(
        'wpcw-elementor-widgets',
        WPCW_PLUGIN_URL . 'elementor/js/widgets.js',
        ['elementor-frontend'],
        WPCW_VERSION,
        true
    );
}
add_action('elementor/frontend/after_register_scripts', 'register_widget_scripts');
```

## Templates

### Coupon Card Template
```php
function render_coupon_template($coupon_id) {
    ?>
    <div class="wpcw-elementor-coupon-card">
        <div class="coupon-header">
            <h3><?php echo esc_html(get_the_title($coupon_id)); ?></h3>
            <span class="discount">
                <?php echo esc_html(get_post_meta($coupon_id, 'coupon_amount', true)) . '%'; ?>
            </span>
        </div>
        
        <div class="coupon-details">
            <?php echo wp_kses_post(get_post_meta($coupon_id, 'description', true)); ?>
        </div>
        
        <div class="coupon-footer">
            <button class="wpcw-redeem-button" data-coupon-id="<?php echo esc_attr($coupon_id); ?>">
                <?php _e('Canjear', 'wp-cupon-whatsapp'); ?>
            </button>
        </div>
    </div>
    <?php
}
```

## Hooks

### Filters
```php
// Modify coupon display data
add_filter('wpcw_elementor_coupon_data', function($data, $coupon_id) {
    // Modify data
    return $data;
}, 10, 2);

// Customize form fields
add_filter('wpcw_elementor_registration_fields', function($fields) {
    // Add/modify fields
    return $fields;
});
```

### Actions
```php
// Before coupon display
do_action('wpcw_elementor_before_coupon', $coupon_id);

// After form submission
do_action('wpcw_elementor_after_registration', $business_id);
```

## Responsive Design

### Breakpoints
```php
protected function register_responsive_controls() {
    $this->add_responsive_control(
        'columns',
        [
            'label' => __('Columnas', 'wp-cupon-whatsapp'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => '3',
            'tablet_default' => '2',
            'mobile_default' => '1',
            'options' => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4'
            ],
            'selectors' => [
                '{{WRAPPER}} .wpcw-elementor-coupons-list' => 'grid-template-columns: repeat({{VALUE}}, 1fr);'
            ]
        ]
    );
}
```

## Error Handling

### Form Validation
```php
protected function validate_form($data) {
    $errors = new WP_Error();
    
    if (empty($data['business_name'])) {
        $errors->add(
            'empty_name',
            __('El nombre del comercio es obligatorio', 'wp-cupon-whatsapp')
        );
    }
    
    if (!is_email($data['business_email'])) {
        $errors->add(
            'invalid_email',
            __('El email no es válido', 'wp-cupon-whatsapp')
        );
    }
    
    return $errors;
}
```

## Integration Testing

### Test Cases
```php
class WPCW_Elementor_Test extends WP_UnitTestCase {
    public function test_widget_registration() {
        $widgets_manager = new \Elementor\Widgets_Manager();
        
        $this->assertTrue(
            $widgets_manager->get_widget_types('wpcw_coupons_list') !== null
        );
    }
    
    public function test_form_submission() {
        $_POST['wpcw_registration_nonce'] = wp_create_nonce('wpcw_registration');
        $_POST['business_name'] = 'Test Business';
        
        $widget = new WPCW_Elementor_Registration_Widget();
        $result = $widget->handle_form_submission();
        
        $this->assertTrue($result > 0);
    }
}
```
