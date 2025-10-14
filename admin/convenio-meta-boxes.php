<?php
/**
 * Meta Boxes for wpcw_convenio CPT
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Adds custom meta boxes to the wpcw_convenio edit screen.
 */
function wpcw_add_convenio_meta_boxes() {
	add_meta_box(
		'wpcw_convenio_details',
		__( 'Detalles del Convenio', 'wp-cupon-whatsapp' ),
		'wpcw_render_convenio_details_meta_box',
		'wpcw_convenio',
		'normal',
		'high'
	);

	add_meta_box(
		'wpcw_convenio_status',
		__( 'Estado y Aprobación', 'wp-cupon-whatsapp' ),
		'wpcw_render_convenio_status_meta_box',
		'wpcw_convenio',
		'side',
		'high'
	);

	add_meta_box(
		'wpcw_convenio_negotiation',
		__( 'Negociación', 'wp-cupon-whatsapp' ),
		'wpcw_render_convenio_negotiation_meta_box',
		'wpcw_convenio',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'wpcw_add_convenio_meta_boxes' );

/**
 * Renders the convenio details meta box.
 *
 * @param WP_Post $post The current post object.
 */
function wpcw_render_convenio_details_meta_box( $post ) {
	wp_nonce_field( 'wpcw_convenio_details_meta_box', 'wpcw_convenio_details_nonce' );

	// Get existing values
	$provider_id    = get_post_meta( $post->ID, '_convenio_provider_id', true );
	$recipient_id   = get_post_meta( $post->ID, '_convenio_recipient_id', true );
	$recipient_type = get_post_meta( $post->ID, '_convenio_recipient_type', true ) ?: 'institution'; // Default: institution
	$terms          = get_post_meta( $post->ID, '_convenio_terms', true );
	$discount       = get_post_meta( $post->ID, '_convenio_discount_percentage', true );
	$max_uses       = get_post_meta( $post->ID, '_convenio_max_uses_per_beneficiary', true );
	$start_date     = get_post_meta( $post->ID, '_convenio_start_date', true );
	$end_date       = get_post_meta( $post->ID, '_convenio_end_date', true );

	?>
	<table class="form-table wpcw-convenio-details">
		<tbody>
			<tr>
				<th scope="row">
					<label for="convenio_provider_id">
						<?php _e( 'Proveedor (Comercio)', 'wp-cupon-whatsapp' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php
					$businesses = get_posts(
						array(
							'post_type'      => 'wpcw_business',
							'numberposts'    => -1,
							'orderby'        => 'title',
							'order'          => 'ASC',
							'post_status'    => 'publish',
						)
					);
					?>
					<select id="convenio_provider_id" name="convenio_provider_id" class="regular-text" required>
						<option value=""><?php _e( '-- Seleccionar Comercio --', 'wp-cupon-whatsapp' ); ?></option>
						<?php foreach ( $businesses as $business ) : ?>
							<option value="<?php echo esc_attr( $business->ID ); ?>" <?php selected( $provider_id, $business->ID ); ?>>
								<?php echo esc_html( $business->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'El comercio que ofrece el beneficio.', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="convenio_recipient_type">
						<?php _e( 'Tipo de Beneficiario', 'wp-cupon-whatsapp' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<select id="convenio_recipient_type" name="convenio_recipient_type" class="regular-text" required onchange="wpcwToggleRecipientField(this.value)">
						<option value="institution" <?php selected( $recipient_type, 'institution' ); ?>>
							<?php _e( 'Institución (empleados de la institución)', 'wp-cupon-whatsapp' ); ?>
						</option>
						<option value="business" <?php selected( $recipient_type, 'business' ); ?>>
							<?php _e( 'Comercio (empleados del comercio - B2B)', 'wp-cupon-whatsapp' ); ?>
						</option>
					</select>
					<p class="description"><?php _e( 'Selecciona si el beneficio es para empleados de una institución o de otro comercio (B2B).', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr class="convenio-recipient-field convenio-recipient-institution" style="<?php echo $recipient_type === 'business' ? 'display:none;' : ''; ?>">
				<th scope="row">
					<label for="convenio_recipient_institution_id">
						<?php _e( 'Institución Beneficiaria', 'wp-cupon-whatsapp' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php
					$institutions = get_posts(
						array(
							'post_type'      => 'wpcw_institution',
							'numberposts'    => -1,
							'orderby'        => 'title',
							'order'          => 'ASC',
							'post_status'    => 'publish',
						)
					);
					?>
					<select id="convenio_recipient_institution_id" name="convenio_recipient_institution_id" class="regular-text">
						<option value=""><?php _e( '-- Seleccionar Institución --', 'wp-cupon-whatsapp' ); ?></option>
						<?php foreach ( $institutions as $institution ) : ?>
							<option value="<?php echo esc_attr( $institution->ID ); ?>" <?php selected( $recipient_id, $institution->ID ); ?>>
								<?php echo esc_html( $institution->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'Los empleados de esta institución podrán usar el beneficio.', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr class="convenio-recipient-field convenio-recipient-business" style="<?php echo $recipient_type === 'institution' ? 'display:none;' : ''; ?>">
				<th scope="row">
					<label for="convenio_recipient_business_id">
						<?php _e( 'Comercio Beneficiario (B2B)', 'wp-cupon-whatsapp' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<?php
					$businesses_recipient = get_posts(
						array(
							'post_type'      => 'wpcw_business',
							'numberposts'    => -1,
							'orderby'        => 'title',
							'order'          => 'ASC',
							'post_status'    => 'publish',
						)
					);
					?>
					<select id="convenio_recipient_business_id" name="convenio_recipient_business_id" class="regular-text">
						<option value=""><?php _e( '-- Seleccionar Comercio --', 'wp-cupon-whatsapp' ); ?></option>
						<?php foreach ( $businesses_recipient as $business_rec ) : ?>
							<option value="<?php echo esc_attr( $business_rec->ID ); ?>" <?php selected( $recipient_id, $business_rec->ID ); ?>>
								<?php echo esc_html( $business_rec->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'Los empleados de este comercio podrán usar el beneficio (convenio B2B).', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="convenio_terms">
						<?php _e( 'Términos del Convenio', 'wp-cupon-whatsapp' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<textarea id="convenio_terms" name="convenio_terms" rows="5" class="large-text" required><?php echo esc_textarea( $terms ); ?></textarea>
					<p class="description"><?php _e( 'Describe los términos y condiciones del beneficio ofrecido.', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="convenio_discount_percentage">
						<?php _e( 'Porcentaje de Descuento', 'wp-cupon-whatsapp' ); ?>
					</label>
				</th>
				<td>
					<input type="number" id="convenio_discount_percentage" name="convenio_discount_percentage"
						   value="<?php echo esc_attr( $discount ); ?>"
						   min="0" max="100" step="0.01" class="small-text">
					<span class="description">%</span>
					<p class="description"><?php _e( 'Porcentaje de descuento ofrecido (opcional si se especifica en los términos).', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="convenio_max_uses_per_beneficiary">
						<?php _e( 'Usos Máximos por Beneficiario', 'wp-cupon-whatsapp' ); ?>
					</label>
				</th>
				<td>
					<input type="number" id="convenio_max_uses_per_beneficiary" name="convenio_max_uses_per_beneficiary"
						   value="<?php echo esc_attr( $max_uses ); ?>"
						   min="0" step="1" class="small-text">
					<p class="description"><?php _e( 'Número máximo de veces que un empleado puede usar cupones de este convenio (0 = ilimitado).', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="convenio_start_date">
						<?php _e( 'Fecha de Inicio', 'wp-cupon-whatsapp' ); ?>
					</label>
				</th>
				<td>
					<input type="date" id="convenio_start_date" name="convenio_start_date"
						   value="<?php echo esc_attr( $start_date ); ?>"
						   class="regular-text">
					<p class="description"><?php _e( 'Fecha desde la cual el convenio está activo (opcional).', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="convenio_end_date">
						<?php _e( 'Fecha de Finalización', 'wp-cupon-whatsapp' ); ?>
					</label>
				</th>
				<td>
					<input type="date" id="convenio_end_date" name="convenio_end_date"
						   value="<?php echo esc_attr( $end_date ); ?>"
						   class="regular-text">
					<p class="description"><?php _e( 'Fecha hasta la cual el convenio está activo (opcional).', 'wp-cupon-whatsapp' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

	<script>
	function wpcwToggleRecipientField(type) {
		var institutionField = document.querySelector('.convenio-recipient-institution');
		var businessField = document.querySelector('.convenio-recipient-business');

		if (type === 'institution') {
			institutionField.style.display = '';
			businessField.style.display = 'none';
			// Clear business value
			document.getElementById('convenio_recipient_business_id').value = '';
		} else {
			institutionField.style.display = 'none';
			businessField.style.display = '';
			// Clear institution value
			document.getElementById('convenio_recipient_institution_id').value = '';
		}
	}
	</script>
	<?php
}

/**
 * Renders the convenio status meta box.
 *
 * @param WP_Post $post The current post object.
 */
function wpcw_render_convenio_status_meta_box( $post ) {
	wp_nonce_field( 'wpcw_convenio_status_meta_box', 'wpcw_convenio_status_nonce' );

	$status       = get_post_meta( $post->ID, '_convenio_status', true );
	$originator   = get_post_meta( $post->ID, '_convenio_originator_id', true );
	$approved_by  = get_post_meta( $post->ID, '_convenio_approved_by', true );
	$approved_at  = get_post_meta( $post->ID, '_convenio_approved_at', true );

	?>
	<div class="wpcw-convenio-status">
		<p>
			<label for="convenio_status"><strong><?php _e( 'Estado del Convenio:', 'wp-cupon-whatsapp' ); ?></strong></label><br>
			<select id="convenio_status" name="convenio_status" class="widefat">
				<optgroup label="<?php _e( 'En Proceso', 'wp-cupon-whatsapp' ); ?>">
					<option value="draft" <?php selected( $status, 'draft' ); ?>><?php _e( '📝 Borrador', 'wp-cupon-whatsapp' ); ?></option>
					<option value="pending_review" <?php selected( $status, 'pending_review' ); ?>><?php _e( '👀 Pendiente de Revisión', 'wp-cupon-whatsapp' ); ?></option>
					<option value="under_negotiation" <?php selected( $status, 'under_negotiation' ); ?>><?php _e( '💬 En Negociación', 'wp-cupon-whatsapp' ); ?></option>
					<option value="counter_offered" <?php selected( $status, 'counter_offered' ); ?>><?php _e( '🔄 Contraoferta Enviada', 'wp-cupon-whatsapp' ); ?></option>
					<option value="awaiting_approval" <?php selected( $status, 'awaiting_approval' ); ?>><?php _e( '⏳ Esperando Aprobación', 'wp-cupon-whatsapp' ); ?></option>
					<option value="pending_supervisor" <?php selected( $status, 'pending_supervisor' ); ?>><?php _e( '👔 Pendiente Supervisor', 'wp-cupon-whatsapp' ); ?></option>
				</optgroup>
				<optgroup label="<?php _e( 'Activos', 'wp-cupon-whatsapp' ); ?>">
					<option value="approved" <?php selected( $status, 'approved' ); ?>><?php _e( '✅ Aprobado', 'wp-cupon-whatsapp' ); ?></option>
					<option value="active" <?php selected( $status, 'active' ); ?>><?php _e( '🟢 Activo', 'wp-cupon-whatsapp' ); ?></option>
					<option value="paused" <?php selected( $status, 'paused' ); ?>><?php _e( '⏸️ Pausado', 'wp-cupon-whatsapp' ); ?></option>
					<option value="near_expiry" <?php selected( $status, 'near_expiry' ); ?>><?php _e( '⚠️ Próximo a Vencer', 'wp-cupon-whatsapp' ); ?></option>
				</optgroup>
				<optgroup label="<?php _e( 'Finalizados', 'wp-cupon-whatsapp' ); ?>">
					<option value="rejected" <?php selected( $status, 'rejected' ); ?>><?php _e( '❌ Rechazado', 'wp-cupon-whatsapp' ); ?></option>
					<option value="expired" <?php selected( $status, 'expired' ); ?>><?php _e( '⏰ Expirado', 'wp-cupon-whatsapp' ); ?></option>
					<option value="cancelled" <?php selected( $status, 'cancelled' ); ?>><?php _e( '🚫 Cancelado', 'wp-cupon-whatsapp' ); ?></option>
				</optgroup>
			</select>
		</p>

		<?php if ( $originator ) : ?>
			<p>
				<strong><?php _e( 'Propuesto por:', 'wp-cupon-whatsapp' ); ?></strong><br>
				<?php
				$originator_user = get_userdata( $originator );
				if ( $originator_user ) {
					echo esc_html( $originator_user->display_name );
					echo '<br><small>' . esc_html( $originator_user->user_email ) . '</small>';
				}
				?>
			</p>
		<?php endif; ?>

		<?php if ( $approved_by && $approved_at ) : ?>
			<p>
				<strong><?php _e( 'Aprobado por:', 'wp-cupon-whatsapp' ); ?></strong><br>
				<?php
				$approver = get_userdata( $approved_by );
				if ( $approver ) {
					echo esc_html( $approver->display_name );
					echo '<br><small>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $approved_at ) ) ) . '</small>';
				}
				?>
			</p>
		<?php endif; ?>

		<p class="description">
			<?php _e( 'Los convenios activos permiten crear cupones asociados.', 'wp-cupon-whatsapp' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Saves the convenio meta data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function wpcw_save_convenio_meta( $post_id ) {
	// Security checks
	if ( ! isset( $_POST['wpcw_convenio_details_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_convenio_details_nonce'], 'wpcw_convenio_details_meta_box' ) ) {
		return;
	}

	if ( ! isset( $_POST['wpcw_convenio_status_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_convenio_status_nonce'], 'wpcw_convenio_status_meta_box' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save provider
	if ( isset( $_POST['convenio_provider_id'] ) ) {
		update_post_meta( $post_id, '_convenio_provider_id', absint( $_POST['convenio_provider_id'] ) );
	}

	// Save recipient type
	if ( isset( $_POST['convenio_recipient_type'] ) ) {
		$recipient_type = sanitize_text_field( $_POST['convenio_recipient_type'] );
		update_post_meta( $post_id, '_convenio_recipient_type', $recipient_type );

		// Save the correct recipient based on type
		if ( $recipient_type === 'institution' && isset( $_POST['convenio_recipient_institution_id'] ) ) {
			update_post_meta( $post_id, '_convenio_recipient_id', absint( $_POST['convenio_recipient_institution_id'] ) );
		} elseif ( $recipient_type === 'business' && isset( $_POST['convenio_recipient_business_id'] ) ) {
			update_post_meta( $post_id, '_convenio_recipient_id', absint( $_POST['convenio_recipient_business_id'] ) );
		}
	}

	// Save terms
	if ( isset( $_POST['convenio_terms'] ) ) {
		update_post_meta( $post_id, '_convenio_terms', sanitize_textarea_field( $_POST['convenio_terms'] ) );
	}

	// Save discount percentage
	if ( isset( $_POST['convenio_discount_percentage'] ) ) {
		$discount = floatval( $_POST['convenio_discount_percentage'] );
		update_post_meta( $post_id, '_convenio_discount_percentage', $discount );
	}

	// Save max uses
	if ( isset( $_POST['convenio_max_uses_per_beneficiary'] ) ) {
		update_post_meta( $post_id, '_convenio_max_uses_per_beneficiary', absint( $_POST['convenio_max_uses_per_beneficiary'] ) );
	}

	// Save dates
	if ( isset( $_POST['convenio_start_date'] ) ) {
		update_post_meta( $post_id, '_convenio_start_date', sanitize_text_field( $_POST['convenio_start_date'] ) );
	}

	if ( isset( $_POST['convenio_end_date'] ) ) {
		update_post_meta( $post_id, '_convenio_end_date', sanitize_text_field( $_POST['convenio_end_date'] ) );
	}

	// Save status
	if ( isset( $_POST['convenio_status'] ) ) {
		$old_status = get_post_meta( $post_id, '_convenio_status', true );
		$new_status = sanitize_text_field( $_POST['convenio_status'] );

		update_post_meta( $post_id, '_convenio_status', $new_status );

		// If status changed to active, record approval
		if ( $old_status !== 'active' && $new_status === 'active' ) {
			update_post_meta( $post_id, '_convenio_approved_by', get_current_user_id() );
			update_post_meta( $post_id, '_convenio_approved_at', current_time( 'mysql' ) );
		}

		// Update post_status to match convenio status
		if ( $new_status === 'active' ) {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				)
			);
		}
	}
}
add_action( 'save_post_wpcw_convenio', 'wpcw_save_convenio_meta' );

/**
 * Add custom columns to the convenios list table.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function wpcw_add_convenio_columns( $columns ) {
	$new_columns = array();

	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;

		if ( $key === 'title' ) {
			$new_columns['provider']  = __( 'Proveedor', 'wp-cupon-whatsapp' );
			$new_columns['recipient'] = __( 'Beneficiario', 'wp-cupon-whatsapp' );
			$new_columns['status']    = __( 'Estado', 'wp-cupon-whatsapp' );
		}
	}

	return $new_columns;
}
add_filter( 'manage_wpcw_convenio_posts_columns', 'wpcw_add_convenio_columns' );

/**
 * Populate custom columns in the convenios list table.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function wpcw_populate_convenio_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'provider':
			$provider_id = get_post_meta( $post_id, '_convenio_provider_id', true );
			if ( $provider_id ) {
				$provider = get_post( $provider_id );
				if ( $provider ) {
					echo '<a href="' . get_edit_post_link( $provider_id ) . '">' . esc_html( $provider->post_title ) . '</a>';
				}
			} else {
				echo '—';
			}
			break;

		case 'recipient':
			$recipient_id = get_post_meta( $post_id, '_convenio_recipient_id', true );
			$recipient_type = get_post_meta( $post_id, '_convenio_recipient_type', true ) ?: 'institution';

			if ( $recipient_id ) {
				$recipient = get_post( $recipient_id );
				if ( $recipient ) {
					$type_label = $recipient_type === 'business' ? '🏪 ' : '🏫 ';
					echo $type_label . '<a href="' . get_edit_post_link( $recipient_id ) . '">' . esc_html( $recipient->post_title ) . '</a>';
				}
			} else {
				echo '—';
			}
			break;

		case 'status':
			$status = get_post_meta( $post_id, '_convenio_status', true );
			$status_labels = array(
				'draft'                => __( 'Borrador', 'wp-cupon-whatsapp' ),
				'pending_review'       => __( 'Pendiente Revisión', 'wp-cupon-whatsapp' ),
				'under_negotiation'    => __( 'En Negociación', 'wp-cupon-whatsapp' ),
				'counter_offered'      => __( 'Contraoferta', 'wp-cupon-whatsapp' ),
				'awaiting_approval'    => __( 'Esperando Aprobación', 'wp-cupon-whatsapp' ),
				'pending_supervisor'   => __( 'Pendiente Supervisor', 'wp-cupon-whatsapp' ),
				'approved'             => __( 'Aprobado', 'wp-cupon-whatsapp' ),
				'active'               => __( 'Activo', 'wp-cupon-whatsapp' ),
				'paused'               => __( 'Pausado', 'wp-cupon-whatsapp' ),
				'near_expiry'          => __( 'Próximo a Vencer', 'wp-cupon-whatsapp' ),
				'rejected'             => __( 'Rechazado', 'wp-cupon-whatsapp' ),
				'expired'              => __( 'Expirado', 'wp-cupon-whatsapp' ),
				'cancelled'            => __( 'Cancelado', 'wp-cupon-whatsapp' ),
			);

			$status_colors = array(
				'draft'                => '#95a5a6', // Gray
				'pending_review'       => '#f39c12', // Orange
				'under_negotiation'    => '#3498db', // Blue
				'counter_offered'      => '#9b59b6', // Purple
				'awaiting_approval'    => '#e67e22', // Dark orange
				'pending_supervisor'   => '#d35400', // Darker orange
				'approved'             => '#27ae60', // Green
				'active'               => '#2ecc71', // Bright green
				'paused'               => '#7f8c8d', // Dark gray
				'near_expiry'          => '#f39c12', // Orange warning
				'rejected'             => '#e74c3c', // Red
				'expired'              => '#95a5a6', // Gray
				'cancelled'            => '#c0392b', // Dark red
			);

			$label = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : __( 'Sin estado', 'wp-cupon-whatsapp' );
			$color = isset( $status_colors[ $status ] ) ? $status_colors[ $status ] : '#999';

			echo '<span style="background-color: ' . esc_attr( $color ) . '; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600;">' . esc_html( $label ) . '</span>';
			break;
	}
}
add_action( 'manage_wpcw_convenio_posts_custom_column', 'wpcw_populate_convenio_columns', 10, 2 );
