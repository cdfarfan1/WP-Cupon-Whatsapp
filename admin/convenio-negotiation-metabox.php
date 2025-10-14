<?php
/**
 * Convenio Negotiation Meta Box
 *
 * Renders negotiation history and counter-offer interface.
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the negotiation meta box.
 *
 * @param WP_Post $post Current post object.
 */
function wpcw_render_convenio_negotiation_meta_box( $post ) {
	$status          = get_post_meta( $post->ID, '_convenio_status', true );
	$negotiation_status = WPCW_Convenio_Negotiation::get_negotiation_status( $post->ID );
	$history         = WPCW_Convenio_Negotiation::get_formatted_history( $post->ID );
	$can_user_negotiate = WPCW_Convenio_Negotiation::can_user_negotiate( $post->ID, get_current_user_id() );

	?>
	<div class="wpcw-negotiation-wrap">

		<!-- Negotiation Status Summary -->
		<div class="negotiation-status-bar">
			<div class="status-info">
				<span class="dashicons dashicons-admin-comments"></span>
				<strong><?php _e( 'Ronda de Negociación:', 'wp-cupon-whatsapp' ); ?></strong>
				<?php echo esc_html( $negotiation_status['current_round'] ); ?> / <?php echo esc_html( $negotiation_status['max_rounds'] ); ?>
			</div>
			<?php if ( ! $negotiation_status['can_negotiate'] ) : ?>
				<div class="status-warning">
					<span class="dashicons dashicons-warning"></span>
					<?php _e( 'Límite de rondas alcanzado', 'wp-cupon-whatsapp' ); ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Negotiation History Timeline -->
		<?php if ( ! empty( $history ) ) : ?>
			<div class="negotiation-timeline">
				<h4><?php _e( 'Historial de Negociación', 'wp-cupon-whatsapp' ); ?></h4>
				<?php foreach ( $history as $entry ) : ?>
					<div class="timeline-entry timeline-<?php echo esc_attr( $entry['action'] ); ?>">
						<div class="timeline-marker">
							<?php
							$icons = array(
								'proposed'        => 'dashicons-megaphone',
								'counter_offered' => 'dashicons-update',
								'accepted'        => 'dashicons-yes-alt',
								'rejected'        => 'dashicons-dismiss',
							);
							$icon = isset( $icons[ $entry['action'] ] ) ? $icons[ $entry['action'] ] : 'dashicons-admin-generic';
							?>
							<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
						</div>
						<div class="timeline-content">
							<div class="timeline-header">
								<strong><?php echo esc_html( $entry['user_name'] ); ?></strong>
								<span class="timeline-action"><?php echo esc_html( $entry['action_label'] ); ?></span>
								<span class="timeline-time"><?php echo esc_html( $entry['date_human'] ); ?></span>
							</div>
							<?php if ( ! empty( $entry['details'] ) ) : ?>
								<div class="timeline-details">
									<?php echo wp_kses_post( $entry['details'] ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="no-history"><?php _e( 'No hay historial de negociación aún.', 'wp-cupon-whatsapp' ); ?></p>
		<?php endif; ?>

		<!-- Counter-Offer Form (if negotiation is open) -->
		<?php if ( $can_user_negotiate && in_array( $status, array( 'pending_review', 'under_negotiation', 'counter_offered' ), true ) && $negotiation_status['can_negotiate'] ) : ?>
			<div class="counter-offer-form-wrap">
				<h4><?php _e( 'Responder a la Propuesta', 'wp-cupon-whatsapp' ); ?></h4>

				<form id="wpcw-counter-offer-form" method="post" action="">
					<?php wp_nonce_field( 'wpcw_counter_offer_nonce', 'wpcw_counter_offer_nonce_field' ); ?>
					<input type="hidden" name="convenio_id" value="<?php echo esc_attr( $post->ID ); ?>">
					<input type="hidden" name="wpcw_negotiation_action" value="counter_offer">

					<table class="form-table">
						<tr>
							<th><label for="counter_terms"><?php _e( 'Nuevos Términos Propuestos', 'wp-cupon-whatsapp' ); ?></label></th>
							<td>
								<textarea name="counter_terms" id="counter_terms" rows="4" class="large-text"><?php echo esc_textarea( get_post_meta( $post->ID, '_convenio_terms', true ) ); ?></textarea>
								<p class="description"><?php _e( 'Modifica los términos según tu contraoferta.', 'wp-cupon-whatsapp' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="counter_discount"><?php _e( 'Descuento Propuesto (%)', 'wp-cupon-whatsapp' ); ?></label></th>
							<td>
								<input type="number" name="counter_discount" id="counter_discount" min="0" max="100" step="0.1" class="small-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_convenio_discount_percentage', true ) ); ?>">
								<span>%</span>
							</td>
						</tr>
						<tr>
							<th><label for="counter_max_uses"><?php _e( 'Usos Máximos por Beneficiario', 'wp-cupon-whatsapp' ); ?></label></th>
							<td>
								<input type="number" name="counter_max_uses" id="counter_max_uses" min="0" step="1" class="small-text" value="<?php echo esc_attr( get_post_meta( $post->ID, '_convenio_max_uses_per_beneficiary', true ) ); ?>">
								<p class="description"><?php _e( '0 = ilimitado', 'wp-cupon-whatsapp' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="justification"><?php _e( 'Justificación', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label></th>
							<td>
								<textarea name="justification" id="justification" rows="3" class="large-text" required></textarea>
								<p class="description"><?php _e( 'Explica por qué propones estos cambios (obligatorio).', 'wp-cupon-whatsapp' ); ?></p>
							</td>
						</tr>
					</table>

					<div class="negotiation-actions">
						<button type="submit" class="button button-primary button-large">
							<span class="dashicons dashicons-update"></span>
							<?php _e( 'Enviar Contraoferta', 'wp-cupon-whatsapp' ); ?>
						</button>
						<button type="button" class="button button-secondary button-large" id="accept-terms-btn">
							<span class="dashicons dashicons-yes"></span>
							<?php _e( 'Aceptar Términos Actuales', 'wp-cupon-whatsapp' ); ?>
						</button>
						<button type="button" class="button button-link-delete button-large" id="reject-terms-btn">
							<span class="dashicons dashicons-no"></span>
							<?php _e( 'Rechazar Convenio', 'wp-cupon-whatsapp' ); ?>
						</button>
					</div>
				</form>

				<!-- Accept/Reject Forms (hidden, triggered by buttons) -->
				<form id="accept-form" method="post" action="" style="display:none;">
					<?php wp_nonce_field( 'wpcw_accept_nonce', 'wpcw_accept_nonce_field' ); ?>
					<input type="hidden" name="convenio_id" value="<?php echo esc_attr( $post->ID ); ?>">
					<input type="hidden" name="wpcw_negotiation_action" value="accept">
				</form>

				<form id="reject-form" method="post" action="" style="display:none;">
					<?php wp_nonce_field( 'wpcw_reject_nonce', 'wpcw_reject_nonce_field' ); ?>
					<input type="hidden" name="convenio_id" value="<?php echo esc_attr( $post->ID ); ?>">
					<input type="hidden" name="wpcw_negotiation_action" value="reject">
					<input type="hidden" name="rejection_reason" id="rejection_reason_hidden">
				</form>
			</div>

			<script>
			jQuery(document).ready(function($) {
				$('#accept-terms-btn').on('click', function(e) {
					e.preventDefault();
					if (confirm('<?php echo esc_js( __( '¿Estás seguro de que quieres aceptar los términos actuales? Esta acción finalizará la negociación.', 'wp-cupon-whatsapp' ) ); ?>')) {
						$('#accept-form').submit();
					}
				});

				$('#reject-terms-btn').on('click', function(e) {
					e.preventDefault();
					var reason = prompt('<?php echo esc_js( __( 'Motivo del rechazo (obligatorio):', 'wp-cupon-whatsapp' ) ); ?>');
					if (reason && reason.trim() !== '') {
						$('#rejection_reason_hidden').val(reason);
						$('#reject-form').submit();
					}
				});
			});
			</script>

			<style>
			.wpcw-negotiation-wrap {
				max-width: 100%;
			}

			.negotiation-status-bar {
				background: #f8f9fa;
				border-left: 4px solid #3498db;
				padding: 15px;
				margin-bottom: 20px;
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			.status-info {
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.status-warning {
				color: #e74c3c;
				display: flex;
				align-items: center;
				gap: 5px;
				font-weight: 600;
			}

			.negotiation-timeline {
				margin-bottom: 30px;
			}

			.negotiation-timeline h4 {
				margin-bottom: 15px;
			}

			.timeline-entry {
				display: flex;
				gap: 15px;
				margin-bottom: 20px;
				padding-bottom: 20px;
				border-bottom: 1px solid #e0e0e0;
			}

			.timeline-entry:last-child {
				border-bottom: none;
			}

			.timeline-marker {
				width: 40px;
				height: 40px;
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
				flex-shrink: 0;
			}

			.timeline-proposed .timeline-marker {
				background-color: #3498db;
			}

			.timeline-counter_offered .timeline-marker {
				background-color: #9b59b6;
			}

			.timeline-accepted .timeline-marker {
				background-color: #27ae60;
			}

			.timeline-rejected .timeline-marker {
				background-color: #e74c3c;
			}

			.timeline-marker .dashicons {
				color: #fff;
				font-size: 20px;
				width: 20px;
				height: 20px;
			}

			.timeline-content {
				flex: 1;
			}

			.timeline-header {
				margin-bottom: 8px;
			}

			.timeline-action {
				color: #666;
				margin-left: 10px;
			}

			.timeline-time {
				color: #999;
				font-size: 12px;
				margin-left: 10px;
			}

			.timeline-details {
				background: #f8f9fa;
				padding: 10px;
				border-radius: 4px;
				font-size: 13px;
				line-height: 1.6;
			}

			.counter-offer-form-wrap {
				background: #fff;
				border: 1px solid #ddd;
				padding: 20px;
				border-radius: 4px;
			}

			.counter-offer-form-wrap h4 {
				margin-top: 0;
			}

			.negotiation-actions {
				display: flex;
				gap: 10px;
				margin-top: 20px;
				padding-top: 20px;
				border-top: 1px solid #e0e0e0;
			}

			.negotiation-actions .button {
				display: inline-flex;
				align-items: center;
				gap: 5px;
			}

			.no-history {
				text-align: center;
				padding: 40px;
				color: #999;
				background: #f8f9fa;
				border-radius: 4px;
			}

			.required {
				color: #d63638;
			}
			</style>
		<?php else : ?>
			<div class="negotiation-closed">
				<p>
					<?php
					if ( ! $negotiation_status['can_negotiate'] ) {
						echo '<span class="dashicons dashicons-info"></span> ';
						_e( 'La negociación ha finalizado (límite de rondas alcanzado).', 'wp-cupon-whatsapp' );
					} elseif ( in_array( $status, array( 'approved', 'active' ), true ) ) {
						echo '<span class="dashicons dashicons-yes-alt"></span> ';
						_e( 'El convenio ha sido aprobado. Ya no es posible negociar.', 'wp-cupon-whatsapp' );
					} elseif ( $status === 'rejected' ) {
						echo '<span class="dashicons dashicons-dismiss"></span> ';
						_e( 'El convenio fue rechazado.', 'wp-cupon-whatsapp' );
					} else {
						echo '<span class="dashicons dashicons-lock"></span> ';
						_e( 'No puedes negociar este convenio.', 'wp-cupon-whatsapp' );
					}
					?>
				</p>
			</div>
		<?php endif; ?>

	</div>
	<?php
}

/**
 * Handle negotiation form submissions.
 */
function wpcw_handle_negotiation_submissions() {
	if ( ! isset( $_POST['wpcw_negotiation_action'] ) ) {
		return;
	}

	$action      = sanitize_text_field( $_POST['wpcw_negotiation_action'] );
	$convenio_id = isset( $_POST['convenio_id'] ) ? absint( $_POST['convenio_id'] ) : 0;

	if ( ! $convenio_id ) {
		return;
	}

	$user_id = get_current_user_id();

	switch ( $action ) {
		case 'counter_offer':
			// Verify nonce
			if ( ! isset( $_POST['wpcw_counter_offer_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpcw_counter_offer_nonce_field'], 'wpcw_counter_offer_nonce' ) ) {
				wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
			}

			$counter_terms    = isset( $_POST['counter_terms'] ) ? sanitize_textarea_field( $_POST['counter_terms'] ) : '';
			$counter_discount = isset( $_POST['counter_discount'] ) ? floatval( $_POST['counter_discount'] ) : 0;
			$counter_max_uses = isset( $_POST['counter_max_uses'] ) ? absint( $_POST['counter_max_uses'] ) : 0;
			$justification    = isset( $_POST['justification'] ) ? sanitize_textarea_field( $_POST['justification'] ) : '';

			$result = WPCW_Convenio_Negotiation::submit_counter_offer(
				$convenio_id,
				$user_id,
				$counter_terms,
				$counter_discount,
				$counter_max_uses,
				$justification
			);

			if ( is_wp_error( $result ) ) {
				add_action(
					'admin_notices',
					function () use ( $result ) {
						echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
					}
				);
			} else {
				add_action(
					'admin_notices',
					function () {
						echo '<div class="notice notice-success"><p>' . __( 'Contraoferta enviada exitosamente.', 'wp-cupon-whatsapp' ) . '</p></div>';
					}
				);
			}
			break;

		case 'accept':
			// Verify nonce
			if ( ! isset( $_POST['wpcw_accept_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpcw_accept_nonce_field'], 'wpcw_accept_nonce' ) ) {
				wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
			}

			$result = WPCW_Convenio_Negotiation::accept_terms( $convenio_id, $user_id );

			if ( is_wp_error( $result ) ) {
				add_action(
					'admin_notices',
					function () use ( $result ) {
						echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
					}
				);
			} else {
				add_action(
					'admin_notices',
					function () {
						echo '<div class="notice notice-success"><p>' . __( '¡Convenio aprobado exitosamente!', 'wp-cupon-whatsapp' ) . '</p></div>';
					}
				);
			}
			break;

		case 'reject':
			// Verify nonce
			if ( ! isset( $_POST['wpcw_reject_nonce_field'] ) || ! wp_verify_nonce( $_POST['wpcw_reject_nonce_field'], 'wpcw_reject_nonce' ) ) {
				wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
			}

			$reason = isset( $_POST['rejection_reason'] ) ? sanitize_textarea_field( $_POST['rejection_reason'] ) : '';

			$result = WPCW_Convenio_Negotiation::reject_terms( $convenio_id, $user_id, $reason );

			if ( is_wp_error( $result ) ) {
				add_action(
					'admin_notices',
					function () use ( $result ) {
						echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
					}
				);
			} else {
				add_action(
					'admin_notices',
					function () {
						echo '<div class="notice notice-warning"><p>' . __( 'Convenio rechazado.', 'wp-cupon-whatsapp' ) . '</p></div>';
					}
				);
			}
			break;
	}
}
add_action( 'admin_init', 'wpcw_handle_negotiation_submissions' );
