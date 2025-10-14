<?php
/**
 * WPCW Convenio Metrics Class
 *
 * Handles calculation and storage of convenio KPIs and metrics.
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCW_Convenio_Metrics
 */
class WPCW_Convenio_Metrics {

	/**
	 * Calculate all metrics for a specific convenio.
	 *
	 * @param int $convenio_id Convenio post ID.
	 * @return array Array of calculated metrics.
	 */
	public static function calculate_convenio_metrics( $convenio_id ) {
		global $wpdb;

		$provider_id     = get_post_meta( $convenio_id, '_convenio_provider_id', true );
		$recipient_id    = get_post_meta( $convenio_id, '_convenio_recipient_id', true );
		$provider_type   = get_post_meta( $convenio_id, '_convenio_provider_type', true ) ?: 'business';
		$start_date      = get_post_meta( $convenio_id, '_convenio_start_date', true );
		$end_date        = get_post_meta( $convenio_id, '_convenio_end_date', true );

		// Get all coupons associated with this convenio
		$coupons = get_posts(
			array(
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => '_wpcw_associated_convenio_id',
						'value' => $convenio_id,
					),
				),
				'fields'         => 'ids',
			)
		);

		$total_coupons = count( $coupons );

		// Get redemptions for this convenio
		$table_name = $wpdb->prefix . 'wpcw_canjes';
		$redemptions_query = $wpdb->prepare(
			"SELECT
				COUNT(*) as total_redemptions,
				COUNT(DISTINCT user_id) as unique_beneficiaries,
				COUNT(DISTINCT coupon_id) as coupons_redeemed,
				SUM(CASE WHEN estado_canje = 'approved' THEN 1 ELSE 0 END) as approved_redemptions,
				SUM(CASE WHEN estado_canje = 'used' THEN 1 ELSE 0 END) as used_redemptions
			FROM {$table_name}
			WHERE convenio_id = %d",
			$convenio_id
		);

		$redemptions = $wpdb->get_row( $redemptions_query, ARRAY_A );

		// Calculate engagement rate (% of institution members who have redeemed)
		$institution_id = ( $provider_type === 'institution' ) ? $provider_id : $recipient_id;
		$total_members  = self::get_institution_members_count( $institution_id );
		$engagement_rate = ( $total_members > 0 ) ? ( $redemptions['unique_beneficiaries'] / $total_members ) * 100 : 0;

		// Calculate redemption rate (% of coupons that have been redeemed)
		$redemption_rate = ( $total_coupons > 0 ) ? ( $redemptions['coupons_redeemed'] / $total_coupons ) * 100 : 0;

		// Calculate average savings per beneficiary
		$discount_percentage = get_post_meta( $convenio_id, '_convenio_discount_percentage', true ) ?: 0;
		// Estimate based on average ticket (this should be customized per business)
		$average_ticket = 1000; // Default: $1000 pesos
		$average_savings = ( $discount_percentage / 100 ) * $average_ticket;

		// Days active
		$days_active = self::calculate_days_active( $start_date );

		// Days until expiry
		$days_until_expiry = self::calculate_days_until_expiry( $end_date );

		// Proposal success rate (for institutions)
		$proposal_success_rate = self::calculate_proposal_success_rate( $institution_id );

		// Time to activation (days from created to active)
		$time_to_activation = self::calculate_time_to_activation( $convenio_id );

		// Repeat customer rate (beneficiaries who redeemed more than once)
		$repeat_customers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id)
				FROM {$table_name}
				WHERE convenio_id = %d
				GROUP BY user_id
				HAVING COUNT(*) > 1",
				$convenio_id
			)
		);
		$repeat_customer_rate = ( $redemptions['unique_beneficiaries'] > 0 )
			? ( $repeat_customers / $redemptions['unique_beneficiaries'] ) * 100
			: 0;

		// Recent activity (last 7 days)
		$recent_redemptions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$table_name}
				WHERE convenio_id = %d
				AND fecha_solicitud_canje >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
				$convenio_id
			)
		);

		$metrics = array(
			// Core metrics
			'total_coupons'           => $total_coupons,
			'total_redemptions'       => (int) $redemptions['total_redemptions'],
			'unique_beneficiaries'    => (int) $redemptions['unique_beneficiaries'],
			'approved_redemptions'    => (int) $redemptions['approved_redemptions'],
			'used_redemptions'        => (int) $redemptions['used_redemptions'],

			// KPIs
			'engagement_rate'         => round( $engagement_rate, 2 ),
			'redemption_rate'         => round( $redemption_rate, 2 ),
			'average_savings'         => round( $average_savings, 2 ),
			'repeat_customer_rate'    => round( $repeat_customer_rate, 2 ),
			'proposal_success_rate'   => round( $proposal_success_rate, 2 ),

			// Time-based
			'days_active'             => $days_active,
			'days_until_expiry'       => $days_until_expiry,
			'time_to_activation'      => $time_to_activation,
			'recent_redemptions_7d'   => (int) $recent_redemptions,

			// Context
			'total_members'           => $total_members,
			'institution_id'          => $institution_id,
			'provider_type'           => $provider_type,
			'last_calculated'         => current_time( 'mysql' ),
		);

		// Store metrics in post meta for caching
		update_post_meta( $convenio_id, '_convenio_metrics', $metrics );
		update_post_meta( $convenio_id, '_convenio_metrics_last_update', current_time( 'timestamp' ) );

		return $metrics;
	}

	/**
	 * Get institution members count.
	 *
	 * @param int $institution_id Institution post ID.
	 * @return int Number of members.
	 */
	private static function get_institution_members_count( $institution_id ) {
		$members = get_users(
			array(
				'meta_query' => array(
					array(
						'key'   => '_wpcw_institution_id',
						'value' => $institution_id,
					),
				),
				'fields'     => 'ID',
			)
		);

		return count( $members );
	}

	/**
	 * Calculate days active since start date.
	 *
	 * @param string $start_date Start date in Y-m-d format.
	 * @return int Days active.
	 */
	private static function calculate_days_active( $start_date ) {
		if ( empty( $start_date ) ) {
			return 0;
		}

		$start = strtotime( $start_date );
		$now   = current_time( 'timestamp' );

		return max( 0, floor( ( $now - $start ) / DAY_IN_SECONDS ) );
	}

	/**
	 * Calculate days until expiry.
	 *
	 * @param string $end_date End date in Y-m-d format.
	 * @return int Days until expiry (negative if expired).
	 */
	private static function calculate_days_until_expiry( $end_date ) {
		if ( empty( $end_date ) ) {
			return 999; // No expiry
		}

		$end = strtotime( $end_date );
		$now = current_time( 'timestamp' );

		return floor( ( $end - $now ) / DAY_IN_SECONDS );
	}

	/**
	 * Calculate proposal success rate for an institution.
	 *
	 * @param int $institution_id Institution post ID.
	 * @return float Success rate percentage.
	 */
	private static function calculate_proposal_success_rate( $institution_id ) {
		$total_proposals = get_posts(
			array(
				'post_type'      => 'wpcw_convenio',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => '_convenio_provider_id',
						'value' => $institution_id,
					),
				),
				'fields'         => 'ids',
			)
		);

		$approved_proposals = get_posts(
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
				'fields'         => 'ids',
			)
		);

		if ( count( $total_proposals ) === 0 ) {
			return 0;
		}

		return ( count( $approved_proposals ) / count( $total_proposals ) ) * 100;
	}

	/**
	 * Calculate time to activation (days from creation to active status).
	 *
	 * @param int $convenio_id Convenio post ID.
	 * @return int Days to activation.
	 */
	private static function calculate_time_to_activation( $convenio_id ) {
		$post        = get_post( $convenio_id );
		$created_at  = strtotime( $post->post_date );
		$approved_at = get_post_meta( $convenio_id, '_convenio_approved_at', true );

		if ( empty( $approved_at ) ) {
			// Not yet approved, calculate days since creation
			$now = current_time( 'timestamp' );
			return floor( ( $now - $created_at ) / DAY_IN_SECONDS );
		}

		$approved_timestamp = strtotime( $approved_at );
		return floor( ( $approved_timestamp - $created_at ) / DAY_IN_SECONDS );
	}

	/**
	 * Get cached metrics or calculate if stale.
	 *
	 * @param int $convenio_id Convenio post ID.
	 * @param int $cache_duration Cache duration in seconds (default 1 hour).
	 * @return array Metrics array.
	 */
	public static function get_metrics( $convenio_id, $cache_duration = 3600 ) {
		$last_update = get_post_meta( $convenio_id, '_convenio_metrics_last_update', true );
		$metrics     = get_post_meta( $convenio_id, '_convenio_metrics', true );

		// Check if cache is valid
		if ( $last_update && $metrics && ( current_time( 'timestamp' ) - $last_update ) < $cache_duration ) {
			return $metrics;
		}

		// Recalculate metrics
		return self::calculate_convenio_metrics( $convenio_id );
	}

	/**
	 * Calculate marketplace-level metrics.
	 *
	 * @return array Marketplace health metrics.
	 */
	public static function calculate_marketplace_metrics() {
		global $wpdb;

		// Total convenios by status
		$status_counts = array();
		$all_convenios = get_posts(
			array(
				'post_type'      => 'wpcw_convenio',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $all_convenios as $convenio_id ) {
			$status = get_post_meta( $convenio_id, '_convenio_status', true );
			if ( ! isset( $status_counts[ $status ] ) ) {
				$status_counts[ $status ] = 0;
			}
			$status_counts[ $status ]++;
		}

		$total_convenios  = count( $all_convenios );
		$active_convenios = isset( $status_counts['active'] ) ? $status_counts['active'] : 0;

		// Marketplace liquidity (% of active convenios)
		$marketplace_liquidity = ( $total_convenios > 0 ) ? ( $active_convenios / $total_convenios ) * 100 : 0;

		// Network density (average connections per node)
		$institutions_count = wp_count_posts( 'wpcw_institution' )->publish;
		$businesses_count   = wp_count_posts( 'wpcw_business' )->publish;
		$total_nodes        = $institutions_count + $businesses_count;
		$network_density    = ( $total_nodes > 0 ) ? ( $active_convenios / $total_nodes ) : 0;

		// Average time to activation
		$activation_times = array();
		foreach ( $all_convenios as $convenio_id ) {
			$status = get_post_meta( $convenio_id, '_convenio_status', true );
			if ( in_array( $status, array( 'active', 'approved' ), true ) ) {
				$activation_times[] = self::calculate_time_to_activation( $convenio_id );
			}
		}
		$avg_time_to_activation = ! empty( $activation_times ) ? array_sum( $activation_times ) / count( $activation_times ) : 0;

		// Total redemptions across all convenios
		$table_name        = $wpdb->prefix . 'wpcw_canjes';
		$total_redemptions = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

		// Recent activity (last 30 days)
		$recent_convenios = get_posts(
			array(
				'post_type'      => 'wpcw_convenio',
				'posts_per_page' => -1,
				'date_query'     => array(
					array(
						'after' => '30 days ago',
					),
				),
				'fields'         => 'ids',
			)
		);

		return array(
			'total_convenios'         => $total_convenios,
			'active_convenios'        => $active_convenios,
			'marketplace_liquidity'   => round( $marketplace_liquidity, 2 ),
			'network_density'         => round( $network_density, 2 ),
			'avg_time_to_activation'  => round( $avg_time_to_activation, 1 ),
			'total_redemptions'       => (int) $total_redemptions,
			'new_convenios_30d'       => count( $recent_convenios ),
			'institutions_count'      => $institutions_count,
			'businesses_count'        => $businesses_count,
			'status_distribution'     => $status_counts,
			'last_calculated'         => current_time( 'mysql' ),
		);
	}

	/**
	 * Get metrics for all convenios of an institution.
	 *
	 * @param int $institution_id Institution post ID.
	 * @return array Aggregated metrics.
	 */
	public static function get_institution_aggregated_metrics( $institution_id ) {
		$convenios = get_posts(
			array(
				'post_type'      => 'wpcw_convenio',
				'posts_per_page' => -1,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'   => '_convenio_provider_id',
						'value' => $institution_id,
					),
					array(
						'key'   => '_convenio_recipient_id',
						'value' => $institution_id,
					),
				),
				'fields'         => 'ids',
			)
		);

		$aggregated = array(
			'total_convenios'         => count( $convenios ),
			'total_redemptions'       => 0,
			'total_unique_beneficiaries' => 0,
			'avg_engagement_rate'     => 0,
			'avg_redemption_rate'     => 0,
			'total_savings_estimate'  => 0,
		);

		$engagement_rates = array();
		$redemption_rates = array();

		foreach ( $convenios as $convenio_id ) {
			$metrics = self::get_metrics( $convenio_id );

			$aggregated['total_redemptions'] += $metrics['total_redemptions'];
			$aggregated['total_unique_beneficiaries'] += $metrics['unique_beneficiaries'];
			$aggregated['total_savings_estimate'] += $metrics['average_savings'] * $metrics['total_redemptions'];

			$engagement_rates[] = $metrics['engagement_rate'];
			$redemption_rates[] = $metrics['redemption_rate'];
		}

		if ( ! empty( $engagement_rates ) ) {
			$aggregated['avg_engagement_rate'] = array_sum( $engagement_rates ) / count( $engagement_rates );
		}

		if ( ! empty( $redemption_rates ) ) {
			$aggregated['avg_redemption_rate'] = array_sum( $redemption_rates ) / count( $redemption_rates );
		}

		return $aggregated;
	}

	/**
	 * Schedule automatic metrics recalculation.
	 */
	public static function schedule_metrics_update() {
		if ( ! wp_next_scheduled( 'wpcw_recalculate_metrics' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpcw_recalculate_metrics' );
		}
	}

	/**
	 * Recalculate all convenio metrics (scheduled task).
	 */
	public static function recalculate_all_metrics() {
		$convenios = get_posts(
			array(
				'post_type'      => 'wpcw_convenio',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $convenios as $convenio_id ) {
			self::calculate_convenio_metrics( $convenio_id );
		}

		// Also update marketplace metrics
		$marketplace_metrics = self::calculate_marketplace_metrics();
		update_option( 'wpcw_marketplace_metrics', $marketplace_metrics );
		update_option( 'wpcw_marketplace_metrics_last_update', current_time( 'timestamp' ) );
	}
}

// Schedule metrics recalculation
add_action( 'init', array( 'WPCW_Convenio_Metrics', 'schedule_metrics_update' ) );
add_action( 'wpcw_recalculate_metrics', array( 'WPCW_Convenio_Metrics', 'recalculate_all_metrics' ) );
