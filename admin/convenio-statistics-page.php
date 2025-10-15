<?php
/**
 * WPCW - Convenio Statistics Dashboard
 *
 * Unified dashboard showing KPIs and metrics for convenios.
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue scripts and styles for statistics page.
 */
function wpcw_enqueue_statistics_scripts( $hook ) {
	if ( 'wp-cupón-whatsapp_page_wpcw-convenio-statistics' !== $hook && 'toplevel_page_wpcw-main-dashboard' !== $hook ) {
		return;
	}

	// Enqueue Chart.js from CDN
	wp_enqueue_script(
		'chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
		array(),
		'4.4.0',
		true
	);

	// Enqueue custom statistics script
	wp_enqueue_script(
		'wpcw-statistics',
		WPCW_PLUGIN_URL . 'admin/js/wpcw-statistics.js',
		array( 'jquery', 'chartjs' ),
		WPCW_VERSION,
		true
	);

	// Enqueue statistics styles
	wp_enqueue_style(
		'wpcw-statistics',
		WPCW_PLUGIN_URL . 'admin/css/wpcw-statistics.css',
		array(),
		WPCW_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'wpcw_enqueue_statistics_scripts' );

/**
 * Render the convenio statistics page.
 */
function wpcw_render_convenio_statistics_page() {
	$current_user_id = get_current_user_id();
	$user_type       = 'admin'; // Default

	// Determine user type
	$institution_id = get_user_meta( $current_user_id, '_wpcw_institution_id', true );
	$business_id    = get_user_meta( $current_user_id, '_wpcw_business_id', true );

	if ( $institution_id ) {
		$user_type = 'institution';
		$entity_id = $institution_id;
	} elseif ( $business_id ) {
		$user_type = 'business';
		$entity_id = $business_id;
	}

	// Get metrics based on user type
	if ( $user_type === 'admin' ) {
		$marketplace_metrics = get_option( 'wpcw_marketplace_metrics' );
		if ( ! $marketplace_metrics ) {
			$marketplace_metrics = WPCW_Convenio_Metrics::calculate_marketplace_metrics();
		}
	} else {
		$aggregated_metrics = WPCW_Convenio_Metrics::get_institution_aggregated_metrics( $entity_id );
	}

	?>
	<div class="wrap wpcw-statistics-wrap">
		<h1>
			<span class="dashicons dashicons-chart-bar"></span>
			<?php
			if ( $user_type === 'admin' ) {
				_e( 'Estadísticas del Marketplace', 'wp-cupon-whatsapp' );
			} elseif ( $user_type === 'institution' ) {
				printf( __( 'Estadísticas - %s', 'wp-cupon-whatsapp' ), esc_html( get_the_title( $entity_id ) ) );
			} else {
				printf( __( 'Estadísticas - %s', 'wp-cupon-whatsapp' ), esc_html( get_the_title( $entity_id ) ) );
			}
			?>
		</h1>

		<?php if ( $user_type === 'admin' ) : ?>
			<?php wpcw_render_admin_dashboard( $marketplace_metrics ); ?>
		<?php else : ?>
			<?php wpcw_render_entity_dashboard( $user_type, $entity_id, $aggregated_metrics ); ?>
		<?php endif; ?>

	</div>
	<?php
}

/**
 * Render admin marketplace dashboard.
 *
 * @param array $metrics Marketplace metrics.
 */
function wpcw_render_admin_dashboard( $metrics ) {
	?>
	<!-- KPI Cards -->
	<div class="wpcw-kpi-grid">
		<!-- Marketplace Liquidity -->
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #3498db;">
				<span class="dashicons dashicons-chart-line"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Liquidez del Marketplace', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value"><?php echo esc_html( $metrics['marketplace_liquidity'] ); ?>%</div>
				<div class="kpi-meta">
					<?php
					$liquidity_status = $metrics['marketplace_liquidity'] >= 75 ? 'success' : 'warning';
					$liquidity_text   = $metrics['marketplace_liquidity'] >= 75 ? __( 'Excelente', 'wp-cupon-whatsapp' ) : __( 'Necesita mejora', 'wp-cupon-whatsapp' );
					?>
					<span class="badge badge-<?php echo esc_attr( $liquidity_status ); ?>"><?php echo esc_html( $liquidity_text ); ?></span>
					<span class="kpi-target"><?php _e( 'Meta: >75%', 'wp-cupon-whatsapp' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Network Density -->
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #9b59b6;">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Densidad de Red', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value"><?php echo esc_html( $metrics['network_density'] ); ?></div>
				<div class="kpi-meta">
					<?php
					$density_status = $metrics['network_density'] >= 2.5 ? 'success' : 'warning';
					$density_text   = $metrics['network_density'] >= 2.5 ? __( 'Saludable', 'wp-cupon-whatsapp' ) : __( 'Bajo', 'wp-cupon-whatsapp' );
					?>
					<span class="badge badge-<?php echo esc_attr( $density_status ); ?>"><?php echo esc_html( $density_text ); ?></span>
					<span class="kpi-target"><?php _e( 'Meta: >2.5', 'wp-cupon-whatsapp' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Time to Activation -->
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #f39c12;">
				<span class="dashicons dashicons-clock"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Tiempo hasta Activación', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value"><?php echo esc_html( $metrics['avg_time_to_activation'] ); ?> <?php _e( 'días', 'wp-cupon-whatsapp' ); ?></div>
				<div class="kpi-meta">
					<?php
					$time_status = $metrics['avg_time_to_activation'] <= 7 ? 'success' : 'warning';
					$time_text   = $metrics['avg_time_to_activation'] <= 7 ? __( 'Rápido', 'wp-cupon-whatsapp' ) : __( 'Lento', 'wp-cupon-whatsapp' );
					?>
					<span class="badge badge-<?php echo esc_attr( $time_status ); ?>"><?php echo esc_html( $time_text ); ?></span>
					<span class="kpi-target"><?php _e( 'Meta: <7 días', 'wp-cupon-whatsapp' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Total Convenios -->
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #27ae60;">
				<span class="dashicons dashicons-businesswoman"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Total Convenios Activos', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value"><?php echo esc_html( $metrics['active_convenios'] ); ?></div>
				<div class="kpi-meta">
					<span class="kpi-secondary"><?php printf( __( 'de %d totales', 'wp-cupon-whatsapp' ), $metrics['total_convenios'] ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<!-- Approval Metrics Section (Admin Only) -->
	<?php
	$approval_metrics = WPCW_Convenio_Approval::calculate_approval_metrics();
	if ( $approval_metrics['pending_approval_count'] > 0 || $approval_metrics['supervisor_response_rate'] > 0 ) :
	?>
	<div class="wpcw-approval-section">
		<h2>
			<span class="dashicons dashicons-yes-alt"></span>
			<?php _e( 'Métricas de Aprobación', 'wp-cupon-whatsapp' ); ?>
		</h2>
		<div class="wpcw-kpi-grid" style="margin-top: 20px;">
			<!-- Pending Approvals -->
			<div class="wpcw-kpi-card">
				<div class="kpi-icon" style="background-color: #f39c12;">
					<span class="dashicons dashicons-hourglass"></span>
				</div>
				<div class="kpi-content">
					<h3><?php _e( 'Pendientes de Aprobación', 'wp-cupon-whatsapp' ); ?></h3>
					<div class="kpi-value"><?php echo esc_html( $approval_metrics['pending_approval_count'] ); ?></div>
					<div class="kpi-meta">
						<?php if ( $approval_metrics['pending_approval_count'] > 5 ) : ?>
							<span class="badge badge-warning"><?php _e( 'Requiere atención', 'wp-cupon-whatsapp' ); ?></span>
						<?php else : ?>
							<span class="badge badge-success"><?php _e( 'Normal', 'wp-cupon-whatsapp' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Average Approval Time -->
			<div class="wpcw-kpi-card">
				<div class="kpi-icon" style="background-color: #3498db;">
					<span class="dashicons dashicons-clock"></span>
				</div>
				<div class="kpi-content">
					<h3><?php _e( 'Tiempo Promedio de Aprobación', 'wp-cupon-whatsapp' ); ?></h3>
					<div class="kpi-value"><?php echo esc_html( $approval_metrics['average_approval_hours'] ); ?> <?php _e( 'hrs', 'wp-cupon-whatsapp' ); ?></div>
					<div class="kpi-meta">
						<?php
						$avg_hours = $approval_metrics['average_approval_hours'];
						if ( $avg_hours <= 24 ) {
							echo '<span class="badge badge-success">' . esc_html__( 'Excelente', 'wp-cupon-whatsapp' ) . '</span>';
						} elseif ( $avg_hours <= 48 ) {
							echo '<span class="badge badge-warning">' . esc_html__( 'Bueno', 'wp-cupon-whatsapp' ) . '</span>';
						} else {
							echo '<span class="badge badge-danger">' . esc_html__( 'Lento', 'wp-cupon-whatsapp' ) . '</span>';
						}
						?>
						<span class="kpi-target"><?php _e( 'Meta: <24hrs', 'wp-cupon-whatsapp' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Supervisor Response Rate -->
			<div class="wpcw-kpi-card">
				<div class="kpi-icon" style="background-color: #27ae60;">
					<span class="dashicons dashicons-admin-users"></span>
				</div>
				<div class="kpi-content">
					<h3><?php _e( 'Tasa de Respuesta Supervisores', 'wp-cupon-whatsapp' ); ?></h3>
					<div class="kpi-value"><?php echo esc_html( $approval_metrics['supervisor_response_rate'] ); ?>%</div>
					<div class="kpi-meta">
						<?php
						$response_rate = $approval_metrics['supervisor_response_rate'];
						if ( $response_rate >= 90 ) {
							echo '<span class="badge badge-success">' . esc_html__( 'Excelente', 'wp-cupon-whatsapp' ) . '</span>';
						} elseif ( $response_rate >= 70 ) {
							echo '<span class="badge badge-warning">' . esc_html__( 'Bueno', 'wp-cupon-whatsapp' ) . '</span>';
						} else {
							echo '<span class="badge badge-danger">' . esc_html__( 'Bajo', 'wp-cupon-whatsapp' ) . '</span>';
						}
						?>
						<span class="kpi-target"><?php _e( 'Meta: >90%', 'wp-cupon-whatsapp' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- Charts Section -->
	<div class="wpcw-charts-grid">
		<!-- Status Distribution Chart -->
		<div class="wpcw-chart-card">
			<h2><?php _e( 'Distribución por Estado', 'wp-cupon-whatsapp' ); ?></h2>
			<canvas id="statusDistributionChart" width="400" height="300"></canvas>
		</div>

		<!-- Network Overview Chart -->
		<div class="wpcw-chart-card">
			<h2><?php _e( 'Resumen de Red', 'wp-cupon-whatsapp' ); ?></h2>
			<canvas id="networkOverviewChart" width="400" height="300"></canvas>
		</div>
	</div>

	<!-- Details Table -->
	<div class="wpcw-details-section">
		<h2><?php _e( 'Detalles del Sistema', 'wp-cupon-whatsapp' ); ?></h2>
		<table class="widefat striped">
			<tbody>
				<tr>
					<th><?php _e( 'Total Instituciones', 'wp-cupon-whatsapp' ); ?></th>
					<td><?php echo esc_html( $metrics['institutions_count'] ); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Total Comercios', 'wp-cupon-whatsapp' ); ?></th>
					<td><?php echo esc_html( $metrics['businesses_count'] ); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Total Canjes Registrados', 'wp-cupon-whatsapp' ); ?></th>
					<td><?php echo esc_html( number_format( $metrics['total_redemptions'] ) ); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Nuevos Convenios (30 días)', 'wp-cupon-whatsapp' ); ?></th>
					<td><?php echo esc_html( $metrics['new_convenios_30d'] ); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Convenios Pendientes Supervisor', 'wp-cupon-whatsapp' ); ?></th>
					<td>
						<?php echo esc_html( $approval_metrics['pending_approval_count'] ); ?>
						<?php if ( $approval_metrics['pending_approval_count'] > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpcw_convenio&convenio_status=pending_supervisor' ) ); ?>" class="button button-small" style="margin-left: 10px;">
								<?php _e( 'Ver Pendientes', 'wp-cupon-whatsapp' ); ?>
							</a>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Última Actualización', 'wp-cupon-whatsapp' ); ?></th>
					<td><?php echo esc_html( $metrics['last_calculated'] ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<script>
		// Pass metrics to JavaScript
		var wpcwMarketplaceMetrics = <?php echo wp_json_encode( $metrics ); ?>;
	</script>
	<?php
}

/**
 * Render entity-specific dashboard (institution or business).
 *
 * @param string $user_type User type (institution or business).
 * @param int    $entity_id Entity post ID.
 * @param array  $metrics Aggregated metrics.
 */
function wpcw_render_entity_dashboard( $user_type, $entity_id, $metrics ) {
	// Get all convenios for this entity
	$convenios = get_posts(
		array(
			'post_type'      => 'wpcw_convenio',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => '_convenio_provider_id',
					'value' => $entity_id,
				),
				array(
					'key'   => '_convenio_recipient_id',
					'value' => $entity_id,
				),
			),
		)
	);

	?>
	<!-- KPI Cards for Entity -->
	<div class="wpcw-kpi-grid">
		<!-- Engagement Rate -->
		<?php if ( $user_type === 'institution' ) : ?>
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #e74c3c;">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Tasa de Engagement', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value"><?php echo esc_html( round( $metrics['avg_engagement_rate'], 1 ) ); ?>%</div>
				<div class="kpi-meta">
					<?php
					$engagement_status = $metrics['avg_engagement_rate'] >= 40 ? 'success' : 'warning';
					$engagement_text   = $metrics['avg_engagement_rate'] >= 40 ? __( 'Excelente', 'wp-cupon-whatsapp' ) : __( 'Mejorable', 'wp-cupon-whatsapp' );
					?>
					<span class="badge badge-<?php echo esc_attr( $engagement_status ); ?>"><?php echo esc_html( $engagement_text ); ?></span>
					<span class="kpi-target"><?php _e( 'Meta: >40%', 'wp-cupon-whatsapp' ); ?></span>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Redemption Rate -->
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #2ecc71;">
				<span class="dashicons dashicons-tickets-alt"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Tasa de Canje', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value"><?php echo esc_html( round( $metrics['avg_redemption_rate'], 1 ) ); ?>%</div>
				<div class="kpi-meta">
					<?php
					$redemption_status = $metrics['avg_redemption_rate'] >= 30 ? 'success' : 'warning';
					$redemption_text   = $metrics['avg_redemption_rate'] >= 30 ? __( 'Bueno', 'wp-cupon-whatsapp' ) : __( 'Bajo', 'wp-cupon-whatsapp' );
					?>
					<span class="badge badge-<?php echo esc_attr( $redemption_status ); ?>"><?php echo esc_html( $redemption_text ); ?></span>
					<span class="kpi-target"><?php _e( 'Meta: >30%', 'wp-cupon-whatsapp' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Total Savings -->
		<?php if ( $user_type === 'institution' ) : ?>
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #f39c12;">
				<span class="dashicons dashicons-money-alt"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Ahorro Total Estimado', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value">$<?php echo esc_html( number_format( $metrics['total_savings_estimate'], 0 ) ); ?></div>
				<div class="kpi-meta">
					<span class="kpi-secondary"><?php printf( __( 'En %d canjes', 'wp-cupon-whatsapp' ), $metrics['total_redemptions'] ); ?></span>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Total Convenios -->
		<div class="wpcw-kpi-card">
			<div class="kpi-icon" style="background-color: #9b59b6;">
				<span class="dashicons dashicons-businesswoman"></span>
			</div>
			<div class="kpi-content">
				<h3><?php _e( 'Convenios Totales', 'wp-cupon-whatsapp' ); ?></h3>
				<div class="kpi-value"><?php echo esc_html( $metrics['total_convenios'] ); ?></div>
				<div class="kpi-meta">
					<span class="kpi-secondary"><?php _e( 'Activos y pendientes', 'wp-cupon-whatsapp' ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<!-- Convenios Performance Table -->
	<div class="wpcw-details-section">
		<h2><?php _e( 'Rendimiento por Convenio', 'wp-cupon-whatsapp' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php _e( 'Convenio', 'wp-cupon-whatsapp' ); ?></th>
					<th><?php _e( 'Estado', 'wp-cupon-whatsapp' ); ?></th>
					<th><?php _e( 'Canjes', 'wp-cupon-whatsapp' ); ?></th>
					<th><?php _e( 'Beneficiarios', 'wp-cupon-whatsapp' ); ?></th>
					<th><?php _e( 'Engagement', 'wp-cupon-whatsapp' ); ?></th>
					<th><?php _e( 'Tasa Canje', 'wp-cupon-whatsapp' ); ?></th>
					<th><?php _e( 'Días Activo', 'wp-cupon-whatsapp' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $convenios as $convenio ) : ?>
					<?php
					$convenio_metrics = WPCW_Convenio_Metrics::get_metrics( $convenio->ID );
					$status           = get_post_meta( $convenio->ID, '_convenio_status', true );
					?>
					<tr>
						<td><strong><a href="<?php echo get_edit_post_link( $convenio->ID ); ?>"><?php echo esc_html( $convenio->post_title ); ?></a></strong></td>
						<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $status ) ) ); ?></td>
						<td><?php echo esc_html( $convenio_metrics['total_redemptions'] ); ?></td>
						<td><?php echo esc_html( $convenio_metrics['unique_beneficiaries'] ); ?></td>
						<td><?php echo esc_html( round( $convenio_metrics['engagement_rate'], 1 ) ); ?>%</td>
						<td><?php echo esc_html( round( $convenio_metrics['redemption_rate'], 1 ) ); ?>%</td>
						<td><?php echo esc_html( $convenio_metrics['days_active'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Add statistics page to admin menu.
 * DISABLED: Menu is registered in admin-menu.php to avoid duplication
 */
function wpcw_add_statistics_menu() {
	// Commented out to prevent duplicate "Estadísticas" menu
	// Menu is now registered in admin/admin-menu.php
	/*
	add_submenu_page(
		'wpcw-main-dashboard',
		__( 'Estadísticas', 'wp-cupon-whatsapp' ),
		__( 'Estadísticas', 'wp-cupon-whatsapp' ),
		'read',
		'wpcw-convenio-statistics',
		'wpcw_render_convenio_statistics_page',
		10
	);
	*/
}
// add_action( 'admin_menu', 'wpcw_add_statistics_menu', 12 ); // Disabled to prevent duplicate
