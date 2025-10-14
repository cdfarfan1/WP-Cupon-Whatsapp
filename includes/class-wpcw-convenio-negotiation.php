<?php
/**
 * WPCW Convenio Negotiation Class
 *
 * Handles counter-offers and negotiation workflow (max 2 rounds).
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCW_Convenio_Negotiation
 */
class WPCW_Convenio_Negotiation {

	/**
	 * Maximum negotiation rounds allowed.
	 */
	const MAX_NEGOTIATION_ROUNDS = 2;

	/**
	 * Submit a counter-offer.
	 *
	 * @param int    $convenio_id Convenio post ID.
	 * @param int    $user_id User making the counter-offer.
	 * @param string $counter_terms New terms proposed.
	 * @param float  $counter_discount New discount proposed.
	 * @param int    $counter_max_uses New max uses proposed.
	 * @param string $justification Reason for counter-offer.
	 * @return bool|WP_Error Success or error.
	 */
	public static function submit_counter_offer( $convenio_id, $user_id, $counter_terms, $counter_discount, $counter_max_uses, $justification ) {
		// Check if negotiation is still allowed
		$current_rounds = get_post_meta( $convenio_id, '_convenio_negotiation_rounds', true ) ?: 0;

		if ( $current_rounds >= self::MAX_NEGOTIATION_ROUNDS ) {
			return new WP_Error(
				'max_rounds_reached',
				__( 'Se alcanzó el límite máximo de rondas de negociación (2).', 'wp-cupon-whatsapp' )
			);
		}

		// Get current negotiation history
		$history = get_post_meta( $convenio_id, '_convenio_negotiation_history', true ) ?: array();

		// Determine party making counter-offer
		$provider_id   = get_post_meta( $convenio_id, '_convenio_provider_id', true );
		$recipient_id  = get_post_meta( $convenio_id, '_convenio_recipient_id', true );
		$user_business = get_user_meta( $user_id, '_wpcw_business_id', true );
		$user_institution = get_user_meta( $user_id, '_wpcw_institution_id', true );

		if ( $user_institution && $user_institution == $provider_id ) {
			$from_party = 'institution';
			$to_party   = 'business';
		} elseif ( $user_business && $user_business == $provider_id ) {
			$from_party = 'business';
			$to_party   = 'institution';
		} elseif ( $user_institution && $user_institution == $recipient_id ) {
			$from_party = 'institution';
			$to_party   = 'business';
		} elseif ( $user_business && $user_business == $recipient_id ) {
			$from_party = 'business';
			$to_party   = 'institution';
		} else {
			return new WP_Error(
				'unauthorized',
				__( 'No tienes autorización para hacer contraoferta en este convenio.', 'wp-cupon-whatsapp' )
			);
		}

		// Add counter-offer to history
		$history[] = array(
			'timestamp'       => current_time( 'mysql' ),
			'user_id'         => $user_id,
			'action'          => 'counter_offered',
			'from_party'      => $from_party,
			'to_party'        => $to_party,
			'terms'           => sanitize_textarea_field( $counter_terms ),
			'discount'        => floatval( $counter_discount ),
			'max_uses'        => absint( $counter_max_uses ),
			'justification'   => sanitize_textarea_field( $justification ),
			'round'           => $current_rounds + 1,
		);

		// Update metadata
		update_post_meta( $convenio_id, '_convenio_negotiation_history', $history );
		update_post_meta( $convenio_id, '_convenio_negotiation_rounds', $current_rounds + 1 );
		update_post_meta( $convenio_id, '_convenio_status', 'counter_offered' );
		update_post_meta( $convenio_id, '_convenio_last_activity', current_time( 'mysql' ) );

		// Update current terms with counter-offer
		update_post_meta( $convenio_id, '_convenio_terms', $counter_terms );
		update_post_meta( $convenio_id, '_convenio_discount_percentage', $counter_discount );
		update_post_meta( $convenio_id, '_convenio_max_uses_per_beneficiary', $counter_max_uses );

		// Send notification to other party
		self::send_counter_offer_notification( $convenio_id, $from_party, $to_party );

		do_action( 'wpcw_counter_offer_submitted', $convenio_id, $user_id, $from_party );

		return true;
	}

	/**
	 * Accept current terms (ends negotiation).
	 *
	 * @param int $convenio_id Convenio post ID.
	 * @param int $user_id User accepting.
	 * @return bool|WP_Error Success or error.
	 */
	public static function accept_terms( $convenio_id, $user_id ) {
		// Verify user has permission
		if ( ! self::can_user_negotiate( $convenio_id, $user_id ) ) {
			return new WP_Error(
				'unauthorized',
				__( 'No tienes autorización para aceptar este convenio.', 'wp-cupon-whatsapp' )
			);
		}

		// Get history
		$history = get_post_meta( $convenio_id, '_convenio_negotiation_history', true ) ?: array();

		// Add acceptance to history
		$history[] = array(
			'timestamp' => current_time( 'mysql' ),
			'user_id'   => $user_id,
			'action'    => 'accepted',
			'message'   => __( 'Términos aceptados', 'wp-cupon-whatsapp' ),
		);

		// Update status to approved
		update_post_meta( $convenio_id, '_convenio_negotiation_history', $history );
		update_post_meta( $convenio_id, '_convenio_status', 'approved' );
		update_post_meta( $convenio_id, '_convenio_approved_by', $user_id );
		update_post_meta( $convenio_id, '_convenio_approved_at', current_time( 'mysql' ) );
		update_post_meta( $convenio_id, '_convenio_last_activity', current_time( 'mysql' ) );

		// Update post status
		wp_update_post(
			array(
				'ID'          => $convenio_id,
				'post_status' => 'publish',
			)
		);

		// Send notification
		self::send_acceptance_notification( $convenio_id );

		do_action( 'wpcw_convenio_accepted', $convenio_id, $user_id );

		return true;
	}

	/**
	 * Reject current terms (ends negotiation).
	 *
	 * @param int    $convenio_id Convenio post ID.
	 * @param int    $user_id User rejecting.
	 * @param string $reason Rejection reason.
	 * @return bool|WP_Error Success or error.
	 */
	public static function reject_terms( $convenio_id, $user_id, $reason ) {
		// Verify user has permission
		if ( ! self::can_user_negotiate( $convenio_id, $user_id ) ) {
			return new WP_Error(
				'unauthorized',
				__( 'No tienes autorización para rechazar este convenio.', 'wp-cupon-whatsapp' )
			);
		}

		// Get history
		$history = get_post_meta( $convenio_id, '_convenio_negotiation_history', true ) ?: array();

		// Add rejection to history
		$history[] = array(
			'timestamp' => current_time( 'mysql' ),
			'user_id'   => $user_id,
			'action'    => 'rejected',
			'reason'    => sanitize_textarea_field( $reason ),
		);

		// Update status to rejected
		update_post_meta( $convenio_id, '_convenio_negotiation_history', $history );
		update_post_meta( $convenio_id, '_convenio_status', 'rejected' );
		update_post_meta( $convenio_id, '_convenio_rejected_by', $user_id );
		update_post_meta( $convenio_id, '_convenio_rejected_at', current_time( 'mysql' ) );
		update_post_meta( $convenio_id, '_convenio_rejection_reason', $reason );
		update_post_meta( $convenio_id, '_convenio_last_activity', current_time( 'mysql' ) );

		// Send notification
		self::send_rejection_notification( $convenio_id, $reason );

		do_action( 'wpcw_convenio_rejected', $convenio_id, $user_id, $reason );

		return true;
	}

	/**
	 * Check if user can negotiate this convenio.
	 *
	 * @param int $convenio_id Convenio post ID.
	 * @param int $user_id User ID.
	 * @return bool True if can negotiate.
	 */
	public static function can_user_negotiate( $convenio_id, $user_id ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$provider_id  = get_post_meta( $convenio_id, '_convenio_provider_id', true );
		$recipient_id = get_post_meta( $convenio_id, '_convenio_recipient_id', true );

		$user_business    = get_user_meta( $user_id, '_wpcw_business_id', true );
		$user_institution = get_user_meta( $user_id, '_wpcw_institution_id', true );

		return ( $user_business && in_array( $user_business, array( $provider_id, $recipient_id ), true ) )
			|| ( $user_institution && in_array( $user_institution, array( $provider_id, $recipient_id ), true ) );
	}

	/**
	 * Get negotiation history formatted for display.
	 *
	 * @param int $convenio_id Convenio post ID.
	 * @return array Formatted history.
	 */
	public static function get_formatted_history( $convenio_id ) {
		$history = get_post_meta( $convenio_id, '_convenio_negotiation_history', true ) ?: array();

		$formatted = array();

		foreach ( $history as $entry ) {
			$user = get_userdata( $entry['user_id'] );
			$user_name = $user ? $user->display_name : __( 'Usuario desconocido', 'wp-cupon-whatsapp' );

			$formatted[] = array(
				'timestamp'   => $entry['timestamp'],
				'date_human'  => human_time_diff( strtotime( $entry['timestamp'] ), current_time( 'timestamp' ) ) . ' ' . __( 'atrás', 'wp-cupon-whatsapp' ),
				'user_name'   => $user_name,
				'action'      => $entry['action'],
				'action_label' => self::get_action_label( $entry['action'] ),
				'from_party'  => isset( $entry['from_party'] ) ? $entry['from_party'] : '',
				'details'     => self::format_entry_details( $entry ),
			);
		}

		return array_reverse( $formatted ); // Most recent first
	}

	/**
	 * Get action label for display.
	 *
	 * @param string $action Action type.
	 * @return string Translated label.
	 */
	private static function get_action_label( $action ) {
		$labels = array(
			'proposed'        => __( 'Propuso convenio', 'wp-cupon-whatsapp' ),
			'counter_offered' => __( 'Hizo contraoferta', 'wp-cupon-whatsapp' ),
			'accepted'        => __( 'Aceptó términos', 'wp-cupon-whatsapp' ),
			'rejected'        => __( 'Rechazó convenio', 'wp-cupon-whatsapp' ),
		);

		return isset( $labels[ $action ] ) ? $labels[ $action ] : ucfirst( $action );
	}

	/**
	 * Format entry details for display.
	 *
	 * @param array $entry History entry.
	 * @return string Formatted details.
	 */
	private static function format_entry_details( $entry ) {
		$details = '';

		if ( isset( $entry['terms'] ) ) {
			$details .= '<strong>' . __( 'Términos:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( wp_trim_words( $entry['terms'], 15 ) ) . '<br>';
		}

		if ( isset( $entry['discount'] ) && $entry['discount'] > 0 ) {
			$details .= '<strong>' . __( 'Descuento:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $entry['discount'] ) . '%<br>';
		}

		if ( isset( $entry['max_uses'] ) && $entry['max_uses'] > 0 ) {
			$details .= '<strong>' . __( 'Usos máximos:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $entry['max_uses'] ) . '<br>';
		}

		if ( isset( $entry['justification'] ) ) {
			$details .= '<strong>' . __( 'Justificación:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $entry['justification'] ) . '<br>';
		}

		if ( isset( $entry['reason'] ) ) {
			$details .= '<strong>' . __( 'Motivo:', 'wp-cupon-whatsapp' ) . '</strong> ' . esc_html( $entry['reason'] ) . '<br>';
		}

		return $details;
	}

	/**
	 * Send counter-offer notification email.
	 *
	 * @param int    $convenio_id Convenio post ID.
	 * @param string $from_party Party making offer (business/institution).
	 * @param string $to_party Party receiving offer.
	 */
	private static function send_counter_offer_notification( $convenio_id, $from_party, $to_party ) {
		$convenio_title = get_the_title( $convenio_id );
		$terms          = get_post_meta( $convenio_id, '_convenio_terms', true );
		$discount       = get_post_meta( $convenio_id, '_convenio_discount_percentage', true );

		// Get recipient email
		if ( $to_party === 'business' ) {
			$recipient_id    = get_post_meta( $convenio_id, '_convenio_recipient_id', true );
			$recipient_email = get_post_meta( $recipient_id, '_business_email', true );
		} else {
			$recipient_id    = get_post_meta( $convenio_id, '_convenio_recipient_id', true );
			$recipient_email = get_post_meta( $recipient_id, '_institution_email', true );
		}

		if ( ! $recipient_email || ! is_email( $recipient_email ) ) {
			return;
		}

		$subject = sprintf( __( 'Nueva contraoferta en: %s', 'wp-cupon-whatsapp' ), $convenio_title );
		$message  = "Hola,\n\n";
		$message .= "Has recibido una contraoferta para el convenio: " . $convenio_title . "\n\n";
		$message .= "Nuevos términos propuestos:\n" . $terms . "\n\n";
		$message .= "Descuento: " . $discount . "%\n\n";
		$message .= "Ingresa a tu panel para revisar y responder:\n";
		$message .= admin_url( 'post.php?post=' . $convenio_id . '&action=edit' );

		wp_mail( $recipient_email, $subject, $message );

		// Send in-app notification
		if (class_exists('WPCW_Notifications')) {
			// Get user ID associated with recipient entity
			$recipient_user_id = self::get_user_from_entity($recipient_id);
			if ($recipient_user_id) {
				WPCW_Notifications::notify_counter_offer($convenio_id, $recipient_user_id);
			}
		}

		// Send WhatsApp notification
		if (class_exists('WPCW_WhatsApp_Notifications')) {
			WPCW_WhatsApp_Notifications::notify_counter_offer($convenio_id, $recipient_id);
		}
	}

	/**
	 * Send acceptance notification email.
	 *
	 * @param int $convenio_id Convenio post ID.
	 */
	private static function send_acceptance_notification( $convenio_id ) {
		$convenio_title = get_the_title( $convenio_id );
		$provider_id    = get_post_meta( $convenio_id, '_convenio_provider_id', true );
		$recipient_id   = get_post_meta( $convenio_id, '_convenio_recipient_id', true );

		// Send to both parties
		$emails = array();
		$provider_email = get_post_meta( $provider_id, '_business_email', true ) ?: get_post_meta( $provider_id, '_institution_email', true );
		$recipient_email = get_post_meta( $recipient_id, '_business_email', true ) ?: get_post_meta( $recipient_id, '_institution_email', true );

		if ( $provider_email && is_email( $provider_email ) ) {
			$emails[] = $provider_email;
		}
		if ( $recipient_email && is_email( $recipient_email ) ) {
			$emails[] = $recipient_email;
		}

		$subject = sprintf( __( '¡Convenio aprobado! %s', 'wp-cupon-whatsapp' ), $convenio_title );
		$message  = "¡Excelentes noticias!\n\n";
		$message .= "El convenio '" . $convenio_title . "' ha sido aprobado.\n\n";
		$message .= "Ya puedes comenzar a crear cupones asociados a este convenio.\n\n";
		$message .= "Ver convenio:\n";
		$message .= admin_url( 'post.php?post=' . $convenio_id . '&action=edit' );

		foreach ( $emails as $email ) {
			wp_mail( $email, $subject, $message );
		}

		// Send in-app notifications to both parties
		if (class_exists('WPCW_Notifications')) {
			$provider_user_id = self::get_user_from_entity($provider_id);
			$recipient_user_id = self::get_user_from_entity($recipient_id);

			if ($provider_user_id) {
				WPCW_Notifications::notify_convenio_approved($convenio_id, $provider_user_id);
			}
			if ($recipient_user_id) {
				WPCW_Notifications::notify_convenio_approved($convenio_id, $recipient_user_id);
			}
		}

		// Send WhatsApp notifications to both parties
		if (class_exists('WPCW_WhatsApp_Notifications')) {
			WPCW_WhatsApp_Notifications::notify_convenio_approved($convenio_id, $provider_id);
			WPCW_WhatsApp_Notifications::notify_convenio_approved($convenio_id, $recipient_id);
		}
	}

	/**
	 * Send rejection notification email.
	 *
	 * @param int    $convenio_id Convenio post ID.
	 * @param string $reason Rejection reason.
	 */
	private static function send_rejection_notification( $convenio_id, $reason ) {
		$convenio_title = get_the_title( $convenio_id );
		$provider_id    = get_post_meta( $convenio_id, '_convenio_provider_id', true );

		$provider_email = get_post_meta( $provider_id, '_business_email', true ) ?: get_post_meta( $provider_id, '_institution_email', true );

		if ( ! $provider_email || ! is_email( $provider_email ) ) {
			return;
		}

		$subject = sprintf( __( 'Convenio rechazado: %s', 'wp-cupon-whatsapp' ), $convenio_title );
		$message  = "Lamentablemente, el convenio '" . $convenio_title . "' ha sido rechazado.\n\n";
		$message .= "Motivo: " . $reason . "\n\n";
		$message .= "Puedes revisar el convenio y considerar proponer nuevos términos:\n";
		$message .= admin_url( 'post.php?post=' . $convenio_id . '&action=edit' );

		wp_mail( $provider_email, $subject, $message );

		// Send in-app notification
		if (class_exists('WPCW_Notifications')) {
			$provider_user_id = self::get_user_from_entity($provider_id);
			if ($provider_user_id) {
				WPCW_Notifications::notify_convenio_rejected($convenio_id, $provider_user_id, $reason);
			}
		}

		// Send WhatsApp notification
		if (class_exists('WPCW_WhatsApp_Notifications')) {
			WPCW_WhatsApp_Notifications::notify_convenio_rejected($convenio_id, $provider_id, $reason);
		}
	}

	/**
	 * Get user ID associated with an entity (business or institution)
	 *
	 * @param int $entity_id Post ID of business or institution
	 * @return int|null User ID or null
	 */
	private static function get_user_from_entity($entity_id) {
		global $wpdb;

		// Look for users with this business or institution ID
		$user_id = $wpdb->get_var($wpdb->prepare("
			SELECT user_id FROM {$wpdb->usermeta}
			WHERE (meta_key = '_wpcw_business_id' OR meta_key = '_wpcw_institution_id')
			AND meta_value = %d
			LIMIT 1
		", $entity_id));

		return $user_id ? (int) $user_id : null;
	}

	/**
	 * Get negotiation status summary.
	 *
	 * @param int $convenio_id Convenio post ID.
	 * @return array Status information.
	 */
	public static function get_negotiation_status( $convenio_id ) {
		$rounds   = get_post_meta( $convenio_id, '_convenio_negotiation_rounds', true ) ?: 0;
		$history  = get_post_meta( $convenio_id, '_convenio_negotiation_history', true ) ?: array();
		$can_negotiate = $rounds < self::MAX_NEGOTIATION_ROUNDS;

		return array(
			'current_round'   => $rounds,
			'max_rounds'      => self::MAX_NEGOTIATION_ROUNDS,
			'can_negotiate'   => $can_negotiate,
			'history_count'   => count( $history ),
			'last_activity'   => get_post_meta( $convenio_id, '_convenio_last_activity', true ),
		);
	}
}
