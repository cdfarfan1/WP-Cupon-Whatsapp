<?php
/**
 * Template for displaying a single coupon card.
 *
 * Available variables:
 * \$coupon_id (int) - The post ID of the coupon.
 * \$coupon_title (string) - The title of the coupon.
 * \$coupon_description (string) - The description of the coupon.
 * \$coupon_image_url (string) - URL for the coupon image.
 * \$coupon_code (string) - The actual coupon code (from post_title of shop_coupon or a meta).
 * \$canje_url (string) - The URL to initiate the redemption process.
 *
 * (Estos son ejemplos de variables que podríamos pasarle. La estructura de datos exacta se definirá al llamar a la plantilla)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Ejemplo de variables que se espera que estén definidas al incluir esta plantilla:
// global \$wpcw_coupon_data;
// \$coupon_id = isset(\$wpcw_coupon_data['id']) ? \$wpcw_coupon_data['id'] : 0;
// \$coupon_title = isset(\$wpcw_coupon_data['title']) ? \$wpcw_coupon_data['title'] : __('Cupón Desconocido', 'wp-cupon-whatsapp');
// \$coupon_image_url = isset(\$wpcw_coupon_data['image_url']) ? \$wpcw_coupon_data['image_url'] : ''; // URL a una imagen placeholder
// \$coupon_description = isset(\$wpcw_coupon_data['description']) ? \$wpcw_coupon_data['description'] : '';

// Para simplificar, asumiremos que las variables se pasan directamente o a través de set_query_var.
// Por ejemplo, si se usa include con variables en el scope, o extract(\$args).

// Define variables if they are not set to avoid warnings, provide defaults
$coupon_id = isset($coupon_id) ? $coupon_id : uniqid();
$coupon_title = isset($coupon_title) ? $coupon_title : __('Título del Cupón no disponible', 'wp-cupon-whatsapp');
$coupon_description = isset($coupon_description) ? $coupon_description : __('Descripción no disponible.', 'wp-cupon-whatsapp');
$coupon_image_url = isset($coupon_image_url) ? $coupon_image_url : '';
$coupon_code = isset($coupon_code) ? $coupon_code : '';
// $canje_url is not used in this template yet, but defined in comments.

?>
<div class="wpcw-coupon-card" id="wpcw-coupon-<?php echo esc_attr( $coupon_id ); ?>">
    <div class="wpcw-coupon-image-wrapper">
        <?php if ( ! empty( $coupon_image_url ) ) : ?>
            <img src="<?php echo esc_url( $coupon_image_url ); ?>" alt="<?php echo esc_attr( $coupon_title ); ?>" class="wpcw-coupon-image"/>
        <?php else : ?>
            <div class="wpcw-coupon-image-placeholder">
                <span><?php esc_html_e( 'Imagen no disponible', 'wp-cupon-whatsapp' ); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="wpcw-coupon-details">
        <h3 class="wpcw-coupon-title"><?php echo esc_html( $coupon_title ); ?></h3>

        <?php if ( ! empty( $coupon_code ) ) : // El código del cupón real ?>
            <p class="wpcw-coupon-code">
                <?php esc_html_e( 'Código:', 'wp-cupon-whatsapp' ); ?>
                <strong><?php echo esc_html( $coupon_code ); ?></strong>
            </p>
        <?php endif; ?>

        <div class="wpcw-coupon-description">
            <?php // Usar wp_kses_post para permitir algo de HTML si la descripción viene del editor de WC.
                  // Usar wpautop para convertir saltos de línea en párrafos.
                  echo wp_kses_post( wpautop( $coupon_description ) );
            ?>
        </div>
    </div>

    <div class="wpcw-coupon-actions">
        <?php // El botón de canje se manejará más adelante, posiblemente con un form y AJAX.
              // Por ahora, un placeholder.
        ?>
        <button type="button" class="wpcw-canjear-cupon-btn" data-coupon-id="<?php echo esc_attr( $coupon_id ); ?>">
            <?php esc_html_e( 'Canjear Cupón', 'wp-cupon-whatsapp' ); ?>
        </button>
    </div>
</div>
