<?php
/**
 * WPCW - Institution Convenios Management Page
 *
 * Allows institutions to propose and manage convenios with businesses.
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles the submission of the propose convenio form from institutions.
 */
function wpcw_handle_institution_propose_convenio_form() {
	// Check if our form has been submitted
	if ( ! isset( $_POST['submit_institution_propose_convenio'] ) ) {
		return;
	}

	// Security check
	if ( ! isset( $_POST['wpcw_institution_convenio_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_institution_convenio_nonce'], 'wpcw_institution_propose_convenio' ) ) {
		wp_die( __( 'Error de seguridad. Inténtalo de nuevo.', 'wp-cupon-whatsapp' ) );
	}

	// Check user capability - institutions use administrator or custom capability
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_institution_profile' ) ) {
		wp_die( __( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
	}

	// Data validation
	$recipient_business_id = isset( $_POST['wpcw_business_id'] ) ? absint( $_POST['wpcw_business_id'] ) : 0;
	$convenio_terms        = isset( $_POST['wpcw_convenio_terms'] ) ? sanitize_textarea_field( $_POST['wpcw_convenio_terms'] ) : '';
	$requested_discount    = isset( $_POST['wpcw_requested_discount'] ) ? floatval( $_POST['wpcw_requested_discount'] ) : 0;
	$max_uses_per_member   = isset( $_POST['wpcw_max_uses_per_member'] ) ? absint( $_POST['wpcw_max_uses_per_member'] ) : 0;
	$duration_months       = isset( $_POST['wpcw_duration_months'] ) ? absint( $_POST['wpcw_duration_months'] ) : 12;

	if ( $recipient_business_id === 0 || empty( $convenio_terms ) ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Error: Debes seleccionar un comercio y describir los términos del convenio.', 'wp-cupon-whatsapp' ) . '</p></div>';
			}
		);
		return;
	}

	// Get institution ID from current user
	$current_user_id      = get_current_user_id();
	$provider_institution_id = get_user_meta( $current_user_id, '_wpcw_institution_id', true );

	if ( ! $provider_institution_id ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Error: Tu usuario no está asociado a ninguna institución.', 'wp-cupon-whatsapp' ) . '</p></div>';
			}
		);
		return;
	}

	$provider_name  = get_the_title( $provider_institution_id );
	$recipient_name = get_the_title( $recipient_business_id );

	// Create convenio post
	$convenio_post = array(
		'post_title'  => sprintf( 'Convenio: %s - %s', $provider_name, $recipient_name ),
		'post_type'   => 'wpcw_convenio',
		'post_status' => 'publish',
		'post_author' => $current_user_id,
	);

	$convenio_id = wp_insert_post( $convenio_post );

	if ( is_wp_error( $convenio_id ) ) {
		add_action(
			'admin_notices',
			function () use ( $convenio_id ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( __( 'Error al crear el convenio: %s', 'wp-cupon-whatsapp' ), $convenio_id->get_error_message() ) . '</p></div>';
			}
		);
		return;
	}

	// Store metadata - INSTITUTION is provider, BUSINESS is recipient
	$token = bin2hex( random_bytes( 32 ) );
	update_post_meta( $convenio_id, '_convenio_provider_id', $provider_institution_id );
	update_post_meta( $convenio_id, '_convenio_provider_type', 'institution' ); // NEW: Track who initiated
	update_post_meta( $convenio_id, '_convenio_recipient_id', $recipient_business_id );
	update_post_meta( $convenio_id, '_convenio_recipient_type', 'business' ); // NEW: Track recipient type
	update_post_meta( $convenio_id, '_convenio_status', 'pending_review' );
	update_post_meta( $convenio_id, '_convenio_terms', $convenio_terms );
	update_post_meta( $convenio_id, '_convenio_originator_id', $current_user_id );
	update_post_meta( $convenio_id, '_convenio_response_token', $token );
	update_post_meta( $convenio_id, '_convenio_discount_percentage', $requested_discount );
	update_post_meta( $convenio_id, '_convenio_max_uses_per_beneficiary', $max_uses_per_member );

	// Calculate dates
	$start_date = current_time( 'Y-m-d' );
	$end_date   = date( 'Y-m-d', strtotime( "+{$duration_months} months" ) );
	update_post_meta( $convenio_id, '_convenio_start_date', $start_date );
	update_post_meta( $convenio_id, '_convenio_end_date', $end_date );

	// Initialize negotiation history
	$negotiation_history = array(
		array(
			'timestamp'   => current_time( 'mysql' ),
			'user_id'     => $current_user_id,
			'action'      => 'proposed',
			'from_party'  => 'institution',
			'terms'       => $convenio_terms,
			'discount'    => $requested_discount,
			'max_uses'    => $max_uses_per_member,
			'duration'    => $duration_months,
		),
	);
	update_post_meta( $convenio_id, '_convenio_negotiation_history', $negotiation_history );
	update_post_meta( $convenio_id, '_convenio_negotiation_rounds', 0 );

	// Send notification email to business
	$business_email = get_post_meta( $recipient_business_id, '_business_email', true );
	if ( $business_email && is_email( $business_email ) ) {
		$subject = sprintf( 'Nueva propuesta de convenio de %s', $provider_name );

		$response_url = add_query_arg(
			array(
				'convenio_id' => $convenio_id,
				'token'       => $token,
			),
			home_url( '/responder-convenio/' )
		);

		$message  = "Hola,\n\n";
		$message .= "Has recibido una nueva propuesta de convenio de la institución '" . $provider_name . "'.\n\n";
		$message .= "Términos propuestos:\n" . $convenio_terms . "\n\n";
		$message .= "Descuento solicitado: " . $requested_discount . "%\n";
		$message .= "Duración: " . $duration_months . " meses\n";
		$message .= "Usos máximos por miembro: " . ( $max_uses_per_member > 0 ? $max_uses_per_member : 'ilimitado' ) . "\n\n";
		$message .= "Para revisar, aceptar o rechazar esta propuesta, ingresa a tu panel de administración:\n";
		$message .= admin_url( 'admin.php?page=wpcw-business-convenios' ) . "\n\n";
		$message .= "O usa este enlace seguro:\n" . esc_url( $response_url );

		wp_mail( $business_email, $subject, $message );
	}

	// Redirect with success message
	$redirect_url = add_query_arg( 'wpcw_notice', 'convenio_propuesto', admin_url( 'admin.php?page=wpcw-institution-convenios' ) );
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_init', 'wpcw_handle_institution_propose_convenio_form' );

/**
 * Displays admin notices for convenio management.
 */
function wpcw_institution_convenios_admin_notices() {
	if ( ! isset( $_GET['wpcw_notice'] ) ) {
		return;
	}

	if ( $_GET['wpcw_notice'] === 'convenio_propuesto' ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . __( '¡Propuesta de convenio enviada exitosamente al comercio!', 'wp-cupon-whatsapp' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'wpcw_institution_convenios_admin_notices' );

/**
 * Renders the content of the Institution's convenios page.
 */
function wpcw_render_institution_convenios_page() {
	$current_user_id = get_current_user_id();
	$institution_id  = get_user_meta( $current_user_id, '_wpcw_institution_id', true );

	if ( ! $institution_id ) {
		echo '<div class="wrap"><h1>' . __( 'Error', 'wp-cupon-whatsapp' ) . '</h1>';
		echo '<p>' . __( 'Tu usuario no está asociado a ninguna institución.', 'wp-cupon-whatsapp' ) . '</p></div>';
		return;
	}

	$institution_name = get_the_title( $institution_id );
	?>
	<div class="wrap wpcw-dashboard-wrap">
		<h1><span class="dashicons dashicons-businesswoman"></span> <?php printf( __( 'Gestión de Convenios - %s', 'wp-cupon-whatsapp' ), esc_html( $institution_name ) ); ?></h1>
		<p><?php _e( 'Propón nuevos convenios a comercios para ofrecer beneficios a tus miembros. Gestiona las propuestas activas y pendientes.', 'wp-cupon-whatsapp' ); ?></p>

		<div class="wpcw-page-actions">
			<button id="propose-convenio-btn" class="button button-primary"><span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span> <?php _e( 'Proponer Nuevo Convenio', 'wp-cupon-whatsapp' ); ?></button>
		</div>

		<!-- Formulario de Propuesta (Oculto por defecto) -->
		<div id="propose-convenio-form-wrap" class="postbox" style="display: none; margin-top: 20px;">
			<div class="postbox-header">
				<h2><?php _e( 'Nueva Propuesta de Convenio a Comercio', 'wp-cupon-whatsapp' ); ?></h2>
			</div>
			<div class="inside">
				<form id="propose-convenio-form" method="post" action="">
					<?php wp_nonce_field( 'wpcw_institution_propose_convenio', 'wpcw_institution_convenio_nonce' ); ?>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									<label for="wpcw_business_id"><?php _e( 'Comercio Destinatario', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
								</th>
								<td>
									<?php
									$businesses = get_posts(
										array(
											'post_type'      => 'wpcw_business',
											'posts_per_page' => -1,
											'orderby'        => 'title',
											'order'          => 'ASC',
											'post_status'    => 'publish',
										)
									);
									if ( ! empty( $businesses ) ) {
										echo '<select name="wpcw_business_id" id="wpcw_business_id" class="regular-text" required>';
										echo '<option value="">' . __( '-- Seleccionar Comercio --', 'wp-cupon-whatsapp' ) . '</option>';
										foreach ( $businesses as $business ) {
											$category = get_post_meta( $business->ID, '_business_category', true );
											echo '<option value="' . esc_attr( $business->ID ) . '">' . esc_html( $business->post_title );
											if ( $category ) {
												echo ' (' . esc_html( $category ) . ')';
											}
											echo '</option>';
										}
										echo '</select>';
									} else {
										echo '<em>' . __( 'No hay comercios disponibles para proponer un convenio.', 'wp-cupon-whatsapp' ) . '</em>';
									}
									?>
									<p class="description"><?php _e( 'Selecciona el comercio al que quieres proponer el convenio.', 'wp-cupon-whatsapp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpcw_convenio_terms"><?php _e( 'Términos y Beneficios Solicitados', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></label>
								</th>
								<td>
									<textarea name="wpcw_convenio_terms" id="wpcw_convenio_terms" rows="5" class="large-text" placeholder="Ej: Solicitamos un descuento del 15% en todos los productos para nuestros 500 miembros activos." required></textarea>
									<p class="description"><?php _e( 'Describe claramente el beneficio que solicitas para tus miembros.', 'wp-cupon-whatsapp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpcw_requested_discount"><?php _e( 'Descuento Solicitado (%)', 'wp-cupon-whatsapp' ); ?></label>
								</th>
								<td>
									<input type="number" name="wpcw_requested_discount" id="wpcw_requested_discount" min="0" max="100" step="0.1" class="small-text" value="10">
									<span>%</span>
									<p class="description"><?php _e( 'Porcentaje de descuento que solicitas (opcional si se especifica en los términos).', 'wp-cupon-whatsapp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpcw_max_uses_per_member"><?php _e( 'Usos Máximos por Miembro', 'wp-cupon-whatsapp' ); ?></label>
								</th>
								<td>
									<input type="number" name="wpcw_max_uses_per_member" id="wpcw_max_uses_per_member" min="0" step="1" class="small-text" value="0">
									<p class="description"><?php _e( 'Cuántas veces cada miembro puede usar el beneficio (0 = ilimitado).', 'wp-cupon-whatsapp' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wpcw_duration_months"><?php _e( 'Duración del Convenio', 'wp-cupon-whatsapp' ); ?></label>
								</th>
								<td>
									<input type="number" name="wpcw_duration_months" id="wpcw_duration_months" min="1" max="60" step="1" class="small-text" value="12">
									<span><?php _e( 'meses', 'wp-cupon-whatsapp' ); ?></span>
									<p class="description"><?php _e( 'Duración propuesta para el convenio (12 meses por defecto).', 'wp-cupon-whatsapp' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" name="submit_institution_propose_convenio" id="submit_institution_propose_convenio" class="button button-primary" value="<?php _e( 'Enviar Propuesta al Comercio', 'wp-cupon-whatsapp' ); ?>">
						<button type="button" id="cancel-propose-convenio" class="button button-secondary"><?php _e( 'Cancelar', 'wp-cupon-whatsapp' ); ?></button>
					</p>
				</form>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#propose-convenio-btn').on('click', function() {
					$('#propose-convenio-form-wrap').slideDown();
					$(this).hide();
				});

				$('#cancel-propose-convenio').on('click', function() {
					$('#propose-convenio-form-wrap').slideUp();
					$('#propose-convenio-btn').show();
				});
			});
		</script>

		<div id="poststuff" style="margin-top: 20px;">
			<?php
			// Get all convenios where this institution is provider
			$active_convenios = new WP_Query(
				array(
					'post_type'      => 'wpcw_convenio',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'   => '_convenio_provider_id',
							'value' => $institution_id,
						),
						array(
							'key'     => '_convenio_status',
							'value'   => array( 'approved', 'active' ),
							'compare' => 'IN',
						),
					),
				)
			);

			$pending_convenios = new WP_Query(
				array(
					'post_type'      => 'wpcw_convenio',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'   => '_convenio_provider_id',
							'value' => $institution_id,
						),
						array(
							'key'     => '_convenio_status',
							'value'   => array( 'pending_review', 'under_negotiation', 'counter_offered', 'awaiting_approval' ),
							'compare' => 'IN',
						),
					),
				)
			);
			?>

			<!-- Convenios Activos -->
			<div class="postbox">
				<div class="postbox-header">
					<h2><?php _e( 'Convenios Activos', 'wp-cupon-whatsapp' ); ?> <span class="count">(<?php echo $active_convenios->post_count; ?>)</span></h2>
				</div>
				<div class="inside">
					<?php if ( $active_convenios->have_posts() ) : ?>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php _e( 'Comercio', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Términos', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Descuento', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Vigencia', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Acciones', 'wp-cupon-whatsapp' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								while ( $active_convenios->have_posts() ) :
									$active_convenios->the_post();
									$convenio_id   = get_the_ID();
									$business_id   = get_post_meta( $convenio_id, '_convenio_recipient_id', true );
									$business_name = get_the_title( $business_id );
									$terms         = get_post_meta( $convenio_id, '_convenio_terms', true );
									$discount      = get_post_meta( $convenio_id, '_convenio_discount_percentage', true );
									$status        = get_post_meta( $convenio_id, '_convenio_status', true );
									$end_date      = get_post_meta( $convenio_id, '_convenio_end_date', true );
									?>
									<tr>
										<td><strong><?php echo esc_html( $business_name ); ?></strong></td>
										<td><?php echo esc_html( wp_trim_words( $terms, 10 ) ); ?></td>
										<td><?php echo esc_html( $discount ); ?>%</td>
										<td><span class="badge badge-success"><?php echo esc_html( ucfirst( $status ) ); ?></span></td>
										<td><?php echo esc_html( $end_date ); ?></td>
										<td><a href="<?php echo get_edit_post_link( $convenio_id ); ?>" class="button button-small"><?php _e( 'Ver Detalles', 'wp-cupon-whatsapp' ); ?></a></td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p><?php _e( 'No tienes convenios activos aún. Propón uno haciendo clic en el botón de arriba.', 'wp-cupon-whatsapp' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Convenios Pendientes -->
			<div class="postbox">
				<div class="postbox-header">
					<h2><?php _e( 'Propuestas Pendientes', 'wp-cupon-whatsapp' ); ?> <span class="count">(<?php echo $pending_convenios->post_count; ?>)</span></h2>
				</div>
				<div class="inside">
					<?php if ( $pending_convenios->have_posts() ) : ?>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php _e( 'Comercio', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Términos', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Descuento', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Fecha Propuesta', 'wp-cupon-whatsapp' ); ?></th>
									<th><?php _e( 'Acciones', 'wp-cupon-whatsapp' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								while ( $pending_convenios->have_posts() ) :
									$pending_convenios->the_post();
									$convenio_id   = get_the_ID();
									$business_id   = get_post_meta( $convenio_id, '_convenio_recipient_id', true );
									$business_name = get_the_title( $business_id );
									$terms         = get_post_meta( $convenio_id, '_convenio_terms', true );
									$discount      = get_post_meta( $convenio_id, '_convenio_discount_percentage', true );
									$status        = get_post_meta( $convenio_id, '_convenio_status', true );
									?>
									<tr>
										<td><strong><?php echo esc_html( $business_name ); ?></strong></td>
										<td><?php echo esc_html( wp_trim_words( $terms, 10 ) ); ?></td>
										<td><?php echo esc_html( $discount ); ?>%</td>
										<td><span class="badge badge-warning"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $status ) ) ); ?></span></td>
										<td><?php echo get_the_date(); ?></td>
										<td><a href="<?php echo get_edit_post_link( $convenio_id ); ?>" class="button button-small"><?php _e( 'Ver Detalles', 'wp-cupon-whatsapp' ); ?></a></td>
									</tr>
								<?php endwhile; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p><?php _e( 'No tienes propuestas pendientes.', 'wp-cupon-whatsapp' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<?php wp_reset_postdata(); ?>

		</div>

		<style>
			.wpcw-dashboard-wrap .postbox {
				margin-bottom: 20px;
			}
			.wpcw-dashboard-wrap .postbox-header h2 {
				padding: 12px;
			}
			.wpcw-dashboard-wrap .widefat th {
				padding: 10px;
			}
			.wpcw-dashboard-wrap .badge {
				display: inline-block;
				padding: 3px 8px;
				border-radius: 3px;
				font-size: 11px;
				font-weight: 600;
				color: #fff;
			}
			.wpcw-dashboard-wrap .badge-success {
				background-color: #2ecc71;
			}
			.wpcw-dashboard-wrap .badge-warning {
				background-color: #f39c12;
			}
			.wpcw-dashboard-wrap .count {
				background: #2271b1;
				color: #fff;
				padding: 2px 8px;
				border-radius: 10px;
				font-size: 12px;
				margin-left: 5px;
			}
			.wpcw-dashboard-wrap .required {
				color: #d63638;
			}
		</style>
	</div>
	<?php
}

/**
 * Adds the Institution Convenios menu page.
 */
function wpcw_add_institution_convenios_menu() {
	$current_user_id = get_current_user_id();
	$institution_id  = get_user_meta( $current_user_id, '_wpcw_institution_id', true );

	// Only show if user has institution associated or is admin
	if ( $institution_id || current_user_can( 'manage_options' ) ) {
		add_submenu_page(
			'wpcw-main-dashboard',
			__( 'Convenios', 'wp-cupon-whatsapp' ),
			__( 'Convenios', 'wp-cupon-whatsapp' ),
			'read',
			'wpcw-institution-convenios',
			'wpcw_render_institution_convenios_page',
			3
		);
	}
}
add_action( 'admin_menu', 'wpcw_add_institution_convenios_menu', 11 );
