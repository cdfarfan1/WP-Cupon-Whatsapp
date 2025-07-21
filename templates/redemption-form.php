<?php
/**
 * Template para el Formulario de Canje
 *
 * Este template se usa para mostrar el formulario de canje de cupones
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar que el usuario está logueado
if (!is_user_logged_in()) {
    echo '<p>' . esc_html__('Debes iniciar sesión para canjear cupones.', 'wp-cupon-whatsapp') . '</p>';
    return;
}

// Obtener el cupón
$coupon_id = get_query_var('coupon_id');
if (!$coupon_id) {
    echo '<p>' . esc_html__('Cupón no especificado.', 'wp-cupon-whatsapp') . '</p>';
    return;
}

$coupon = new WC_Coupon($coupon_id);
if (!$coupon || !$coupon->get_id()) {
    echo '<p>' . esc_html__('Cupón no válido.', 'wp-cupon-whatsapp') . '</p>';
    return;
}

// Verificar si el usuario puede canjear
$user_id = get_current_user_id();
if (!WPCW_Redemption_Handler::can_redeem($coupon_id, $user_id)) {
    echo '<p>' . esc_html__('No puedes canjear este cupón.', 'wp-cupon-whatsapp') . '</p>';
    return;
}

// Obtener comercios disponibles
$businesses = WPCW_Redemption_Handler::get_available_businesses($coupon_id);
if (empty($businesses)) {
    echo '<p>' . esc_html__('No hay comercios disponibles para este cupón.', 'wp-cupon-whatsapp') . '</p>';
    return;
}

// Mostrar el formulario
?>
<div class="wpcw-redemption-form">
    <h2><?php echo esc_html(sprintf(__('Canjear Cupón: %s', 'wp-cupon-whatsapp'), $coupon->get_code())); ?></h2>
    
    <?php if (isset($_GET['error'])) : ?>
        <div class="wpcw-error">
            <?php echo esc_html(urldecode($_GET['error'])); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('wpcw_redeem_coupon', 'wpcw_redemption_nonce'); ?>
        <input type="hidden" name="action" value="wpcw_redeem_coupon">
        <input type="hidden" name="coupon_id" value="<?php echo esc_attr($coupon_id); ?>">

        <div class="form-group">
            <label for="business_id"><?php esc_html_e('Selecciona el Comercio:', 'wp-cupon-whatsapp'); ?></label>
            <select name="business_id" id="business_id" required>
                <option value=""><?php esc_html_e('-- Selecciona un comercio --', 'wp-cupon-whatsapp'); ?></option>
                <?php foreach ($businesses as $business) : ?>
                    <option value="<?php echo esc_attr($business->ID); ?>">
                        <?php echo esc_html($business->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <p class="description">
                <?php esc_html_e('Al canjear el cupón, se enviará una notificación al comercio vía WhatsApp para su aprobación.', 'wp-cupon-whatsapp'); ?>
            </p>
        </div>

        <div class="form-group">
            <button type="submit" class="button button-primary">
                <?php esc_html_e('Canjear Cupón', 'wp-cupon-whatsapp'); ?>
            </button>
        </div>
    </form>
</div>

<style>
.wpcw-redemption-form {
    max-width: 600px;
    margin: 2em auto;
    padding: 20px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.wpcw-redemption-form .form-group {
    margin-bottom: 1.5em;
}

.wpcw-redemption-form label {
    display: block;
    margin-bottom: 0.5em;
    font-weight: bold;
}

.wpcw-redemption-form select {
    width: 100%;
    padding: 8px;
}

.wpcw-redemption-form .description {
    font-style: italic;
    color: #666;
}

.wpcw-error {
    background: #f8d7da;
    color: #721c24;
    padding: 1em;
    margin-bottom: 1em;
    border-radius: 3px;
}
</style>
