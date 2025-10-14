# Guía Técnica de Implementación: Sistema de Convenios Bidireccionales

**Para desarrolladores del proyecto WP Cupon WhatsApp**

---

## Índice

1. [Estados y Transiciones](#estados-y-transiciones)
2. [Estructura de Base de Datos](#estructura-de-base-de-datos)
3. [Funciones Helper Recomendadas](#funciones-helper-recomendadas)
4. [Ejemplos de Código PHP](#ejemplos-de-código-php)
5. [Hooks y Acciones](#hooks-y-acciones)
6. [Frontend: JavaScript y UI](#frontend-javascript-y-ui)
7. [Testing y Validación](#testing-y-validación)

---

## 1. Estados y Transiciones

### Enum de Estados Expandido

```php
/**
 * Get all possible convenio statuses
 *
 * @return array Associative array of status => label
 */
function wpcw_get_convenio_statuses() {
    return [
        // Fase 1: Negociación
        'draft'              => __( 'Borrador', 'wp-cupon-whatsapp' ),
        'pending_review'     => __( 'Pendiente de Revisión', 'wp-cupon-whatsapp' ),
        'under_negotiation'  => __( 'En Negociación', 'wp-cupon-whatsapp' ),
        'counter_offered'    => __( 'Contra-Oferta Enviada', 'wp-cupon-whatsapp' ),
        'awaiting_approval'  => __( 'Esperando Aprobación', 'wp-cupon-whatsapp' ),

        // Fase 2: Aprobación
        'pending_supervisor' => __( 'Pendiente de Supervisor', 'wp-cupon-whatsapp' ),
        'approved'           => __( 'Aprobado', 'wp-cupon-whatsapp' ),

        // Fase 3: Activo
        'active'             => __( 'Activo', 'wp-cupon-whatsapp' ),
        'paused'             => __( 'Pausado', 'wp-cupon-whatsapp' ),
        'near_expiry'        => __( 'Próximo a Vencer', 'wp-cupon-whatsapp' ),

        // Fase 4: Terminal
        'rejected'           => __( 'Rechazado', 'wp-cupon-whatsapp' ),
        'expired'            => __( 'Expirado', 'wp-cupon-whatsapp' ),
        'cancelled'          => __( 'Cancelado', 'wp-cupon-whatsapp' ),
    ];
}

/**
 * Get status phase
 *
 * @param string $status Status key
 * @return string Phase name
 */
function wpcw_get_status_phase( $status ) {
    $phases = [
        'negotiation' => [ 'draft', 'pending_review', 'under_negotiation', 'counter_offered', 'awaiting_approval' ],
        'approval'    => [ 'pending_supervisor', 'approved' ],
        'active'      => [ 'active', 'paused', 'near_expiry' ],
        'terminal'    => [ 'rejected', 'expired', 'cancelled' ],
    ];

    foreach ( $phases as $phase => $statuses ) {
        if ( in_array( $status, $statuses, true ) ) {
            return $phase;
        }
    }

    return 'unknown';
}

/**
 * Get CSS class for status badge
 *
 * @param string $status Status key
 * @return string CSS class
 */
function wpcw_get_status_badge_class( $status ) {
    $classes = [
        'draft'              => 'badge-gray',
        'pending_review'     => 'badge-yellow',
        'under_negotiation'  => 'badge-blue',
        'counter_offered'    => 'badge-yellow',
        'awaiting_approval'  => 'badge-blue',
        'pending_supervisor' => 'badge-blue',
        'approved'           => 'badge-green',
        'active'             => 'badge-green',
        'paused'             => 'badge-orange',
        'near_expiry'        => 'badge-orange',
        'rejected'           => 'badge-red',
        'expired'            => 'badge-red',
        'cancelled'          => 'badge-red',
    ];

    return $classes[ $status ] ?? 'badge-gray';
}
```

### Validador de Transiciones

```php
/**
 * Check if status transition is valid
 *
 * @param string $from_status Current status
 * @param string $to_status Desired status
 * @param int    $user_id Current user ID
 * @param int    $convenio_id Convenio post ID
 * @return bool|WP_Error True if valid, WP_Error if not
 */
function wpcw_validate_status_transition( $from_status, $to_status, $user_id, $convenio_id ) {
    // Transition matrix
    $valid_transitions = [
        'draft' => [ 'pending_review' ],
        'pending_review' => [ 'under_negotiation', 'awaiting_approval', 'rejected' ],
        'under_negotiation' => [ 'counter_offered', 'rejected' ],
        'counter_offered' => [ 'under_negotiation', 'awaiting_approval', 'expired' ],
        'awaiting_approval' => [ 'pending_supervisor', 'approved', 'rejected' ],
        'pending_supervisor' => [ 'approved', 'rejected' ],
        'approved' => [ 'active' ],
        'active' => [ 'paused', 'near_expiry', 'cancelled', 'expired' ],
        'paused' => [ 'active', 'cancelled' ],
        'near_expiry' => [ 'active', 'expired' ],
    ];

    // Check if transition exists in matrix
    if ( ! isset( $valid_transitions[ $from_status ] ) ||
         ! in_array( $to_status, $valid_transitions[ $from_status ], true ) ) {
        return new WP_Error(
            'invalid_transition',
            sprintf(
                __( 'No se puede cambiar de "%s" a "%s"', 'wp-cupon-whatsapp' ),
                $from_status,
                $to_status
            )
        );
    }

    // Check user permissions for specific transitions
    $convenio = get_post( $convenio_id );
    $provider_id = get_post_meta( $convenio_id, '_convenio_provider_id', true );
    $recipient_id = get_post_meta( $convenio_id, '_convenio_recipient_id', true );

    // Only supervisor can approve if pending_supervisor
    if ( $from_status === 'pending_supervisor' && $to_status === 'approved' ) {
        if ( ! current_user_can( 'approve_institution_benefits' ) ) {
            return new WP_Error(
                'permission_denied',
                __( 'Solo un Benefits Supervisor puede aprobar este convenio.', 'wp-cupon-whatsapp' )
            );
        }
    }

    // Only parties involved can negotiate
    if ( in_array( $from_status, [ 'pending_review', 'under_negotiation', 'counter_offered' ], true ) ) {
        $user_business_id = get_user_meta( $user_id, '_wpcw_business_id', true );
        $user_institution_id = get_user_meta( $user_id, '_wpcw_institution_id', true );

        $is_party = ( $user_business_id && $user_business_id == $provider_id ) ||
                    ( $user_institution_id && $user_institution_id == $recipient_id ) ||
                    current_user_can( 'manage_woocommerce' );

        if ( ! $is_party ) {
            return new WP_Error(
                'permission_denied',
                __( 'Solo las partes involucradas pueden modificar este convenio.', 'wp-cupon-whatsapp' )
            );
        }
    }

    return true;
}
```

---

## 2. Estructura de Base de Datos

### Post Meta Schema

```php
/**
 * Complete post meta schema for wpcw_convenio
 */
$convenio_meta_schema = [
    // Relaciones básicas (existentes)
    '_convenio_provider_id'              => 'int',      // ID del comercio
    '_convenio_recipient_id'             => 'int',      // ID de la institución
    '_convenio_originator_id'            => 'int',      // User ID del creador

    // Estado y aprobación (existentes mejorados)
    '_convenio_status'                   => 'string',   // Estado actual (12 opciones)
    '_convenio_approved_by'              => 'int',      // User ID aprobador
    '_convenio_approved_at'              => 'datetime', // Timestamp aprobación

    // Términos y condiciones (existentes)
    '_convenio_terms'                    => 'text',     // Términos actuales
    '_convenio_discount_percentage'      => 'float',    // Porcentaje descuento
    '_convenio_max_uses_per_beneficiary' => 'int',      // Usos máximos
    '_convenio_start_date'               => 'date',     // Fecha inicio
    '_convenio_end_date'                 => 'date',     // Fecha fin

    // Negociación y versionado (NUEVOS)
    '_convenio_version_current'          => 'int',      // Versión actual (1, 2, 3...)
    '_convenio_negotiation_history'      => 'array',    // Serialized array de cambios
    '_convenio_negotiation_rounds'       => 'int',      // Contador de counter-offers
    '_convenio_last_change_reason'       => 'text',     // Justificación último cambio

    // Aprobación multi-nivel (NUEVOS)
    '_convenio_requires_supervisor'      => 'bool',     // Requiere 2da aprobación
    '_convenio_approval_level'           => 'int',      // 1 o 2
    '_convenio_supervisor_id'            => 'int',      // User ID supervisor
    '_convenio_supervisor_approved_at'   => 'datetime', // Timestamp aprobación supervisor

    // Métricas y tracking (NUEVOS)
    '_convenio_total_coupons_created'    => 'int',      // Total cupones creados
    '_convenio_total_redemptions'        => 'int',      // Total canjes
    '_convenio_total_beneficiaries_unique' => 'int',    // Beneficiarios únicos
    '_convenio_last_redemption_date'     => 'date',     // Última redención
    '_convenio_last_activity_date'       => 'date',     // Última actividad

    // Alertas (NUEVOS)
    '_convenio_days_until_expiry'        => 'int',      // Calculado diariamente
    '_convenio_renewal_reminder_sent'    => 'bool',     // Flag de reminder

    // Restricciones avanzadas (NUEVOS - OPCIONALES)
    '_convenio_valid_locations'          => 'array',    // IDs de sucursales
    '_convenio_valid_days_of_week'       => 'array',    // ['monday', 'friday']
    '_convenio_valid_hours'              => 'string',   // '09:00-18:00'
    '_convenio_valid_product_categories' => 'array',    // WooCommerce term IDs
    '_convenio_excluded_products'        => 'array',    // WooCommerce product IDs

    // Comunicación (NUEVOS)
    '_convenio_primary_contact_business'     => 'email',  // Email contacto comercio
    '_convenio_primary_contact_institution'  => 'email',  // Email contacto institución
    '_convenio_notification_preferences'     => 'array',  // Prefs de notificación

    // Seguridad (existente)
    '_convenio_response_token'           => 'string',   // Token para respuesta segura
];
```

### Estructura de `_convenio_negotiation_history`

```php
/**
 * Structure of negotiation history array
 */
$example_history = [
    [
        'version'   => 1,
        'user_id'   => 5,
        'user_role' => 'business_owner',
        'action'    => 'proposed',
        'timestamp' => '2025-10-13 10:30:00',
        'changes'   => [],
        'comment'   => 'Propuesta inicial de convenio',
        'metadata'  => [
            'ip_address'  => '192.168.1.1',
            'user_agent'  => 'Mozilla/5.0...',
        ],
    ],
    [
        'version'   => 2,
        'user_id'   => 12,
        'user_role' => 'institution_manager',
        'action'    => 'counter_offered',
        'timestamp' => '2025-10-14 15:20:00',
        'changes'   => [
            'discount_percentage' => [
                'old' => 15.0,
                'new' => 20.0,
            ],
            'max_uses_per_beneficiary' => [
                'old' => 0,
                'new' => 3,
            ],
        ],
        'comment'   => 'Solicitamos incrementar el descuento a 20% y limitar usos',
        'metadata'  => [
            'ip_address'  => '192.168.1.5',
            'user_agent'  => 'Mozilla/5.0...',
        ],
    ],
    [
        'version'   => 3,
        'user_id'   => 5,
        'user_role' => 'business_owner',
        'action'    => 'accepted',
        'timestamp' => '2025-10-15 09:00:00',
        'changes'   => [],
        'comment'   => 'Aceptamos los términos propuestos',
        'metadata'  => [
            'ip_address'  => '192.168.1.1',
            'user_agent'  => 'Mozilla/5.0...',
        ],
    ],
];
```

### Tabla de Agregación (Opcional pero Recomendada)

```sql
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpcw_convenio_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `convenio_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,

  -- Métricas diarias
  `redemptions_count` int(11) DEFAULT 0,
  `unique_beneficiaries` int(11) DEFAULT 0,
  `total_discount_value` decimal(10,2) DEFAULT 0.00,

  -- Métricas acumuladas
  `cumulative_redemptions` int(11) DEFAULT 0,
  `cumulative_beneficiaries` int(11) DEFAULT 0,
  `cumulative_discount_value` decimal(10,2) DEFAULT 0.00,

  -- Metadata
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `convenio_date` (`convenio_id`, `date`),
  KEY `convenio_id` (`convenio_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. Funciones Helper Recomendadas

### Gestión de Negociación

```php
/**
 * Create a counter-offer for a convenio
 *
 * @param int    $convenio_id Convenio post ID
 * @param array  $changes Associative array of field => new_value
 * @param string $reason Justification for changes
 * @param int    $user_id User making the counter-offer
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function wpcw_create_counter_offer( $convenio_id, $changes, $reason, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    // Get current status
    $current_status = get_post_meta( $convenio_id, '_convenio_status', true );

    // Validate transition
    $validation = wpcw_validate_status_transition( $current_status, 'counter_offered', $user_id, $convenio_id );
    if ( is_wp_error( $validation ) ) {
        return $validation;
    }

    // Check counter-offer limit
    $rounds = (int) get_post_meta( $convenio_id, '_convenio_negotiation_rounds', true );
    if ( $rounds >= 2 ) {
        return new WP_Error(
            'max_rounds_reached',
            __( 'Se ha alcanzado el máximo de 2 rondas de negociación.', 'wp-cupon-whatsapp' )
        );
    }

    // Get current history
    $history = get_post_meta( $convenio_id, '_convenio_negotiation_history', true );
    if ( ! is_array( $history ) ) {
        $history = [];
    }

    // Build changes array with old/new values
    $change_log = [];
    foreach ( $changes as $field => $new_value ) {
        $old_value = get_post_meta( $convenio_id, '_convenio_' . $field, true );
        $change_log[ $field ] = [
            'old' => $old_value,
            'new' => $new_value,
        ];

        // Update the field
        update_post_meta( $convenio_id, '_convenio_' . $field, $new_value );
    }

    // Get user role
    $user = get_userdata( $user_id );
    $user_role = wpcw_get_user_primary_role( $user );

    // Increment version
    $current_version = (int) get_post_meta( $convenio_id, '_convenio_version_current', true );
    $new_version = $current_version + 1;

    // Add to history
    $history[] = [
        'version'   => $new_version,
        'user_id'   => $user_id,
        'user_role' => $user_role,
        'action'    => 'counter_offered',
        'timestamp' => current_time( 'mysql' ),
        'changes'   => $change_log,
        'comment'   => sanitize_textarea_field( $reason ),
        'metadata'  => [
            'ip_address' => wpcw_get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ],
    ];

    // Update metadata
    update_post_meta( $convenio_id, '_convenio_negotiation_history', $history );
    update_post_meta( $convenio_id, '_convenio_version_current', $new_version );
    update_post_meta( $convenio_id, '_convenio_negotiation_rounds', $rounds + 1 );
    update_post_meta( $convenio_id, '_convenio_last_change_reason', $reason );
    update_post_meta( $convenio_id, '_convenio_status', 'counter_offered' );
    update_post_meta( $convenio_id, '_convenio_last_activity_date', current_time( 'mysql' ) );

    // Send notification to other party
    wpcw_send_counter_offer_notification( $convenio_id );

    // Hook for extensions
    do_action( 'wpcw_counter_offer_created', $convenio_id, $changes, $reason, $user_id );

    return true;
}

/**
 * Accept a convenio proposal or counter-offer
 *
 * @param int $convenio_id Convenio post ID
 * @param int $user_id User accepting
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function wpcw_accept_convenio( $convenio_id, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    $current_status = get_post_meta( $convenio_id, '_convenio_status', true );

    // Determine next status based on approval requirements
    $requires_supervisor = get_post_meta( $convenio_id, '_convenio_requires_supervisor', true );
    $next_status = $requires_supervisor ? 'pending_supervisor' : 'approved';

    // Validate transition
    $validation = wpcw_validate_status_transition( $current_status, 'awaiting_approval', $user_id, $convenio_id );
    if ( is_wp_error( $validation ) ) {
        return $validation;
    }

    // Update status
    update_post_meta( $convenio_id, '_convenio_status', $next_status );
    update_post_meta( $convenio_id, '_convenio_approved_by', $user_id );
    update_post_meta( $convenio_id, '_convenio_approved_at', current_time( 'mysql' ) );
    update_post_meta( $convenio_id, '_convenio_last_activity_date', current_time( 'mysql' ) );

    // Add to history
    $history = get_post_meta( $convenio_id, '_convenio_negotiation_history', true );
    if ( ! is_array( $history ) ) {
        $history = [];
    }

    $version = (int) get_post_meta( $convenio_id, '_convenio_version_current', true ) + 1;
    $user_role = wpcw_get_user_primary_role( get_userdata( $user_id ) );

    $history[] = [
        'version'   => $version,
        'user_id'   => $user_id,
        'user_role' => $user_role,
        'action'    => 'accepted',
        'timestamp' => current_time( 'mysql' ),
        'changes'   => [],
        'comment'   => __( 'Convenio aceptado', 'wp-cupon-whatsapp' ),
        'metadata'  => [
            'ip_address' => wpcw_get_user_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ],
    ];

    update_post_meta( $convenio_id, '_convenio_negotiation_history', $history );
    update_post_meta( $convenio_id, '_convenio_version_current', $version );

    // If requires supervisor, notify them
    if ( $requires_supervisor ) {
        wpcw_send_supervisor_approval_notification( $convenio_id );
    } else {
        // Auto-activate if no supervisor needed
        wpcw_activate_convenio( $convenio_id );
    }

    do_action( 'wpcw_convenio_accepted', $convenio_id, $user_id );

    return true;
}
```

### Cálculo de Métricas

```php
/**
 * Calculate and update convenio metrics
 *
 * @param int $convenio_id Convenio post ID
 * @return array Calculated metrics
 */
function wpcw_calculate_convenio_metrics( $convenio_id ) {
    global $wpdb;

    // Get all coupons for this convenio
    $coupon_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_wpcw_associated_convenio_id'
         AND meta_value = %d",
        $convenio_id
    ) );

    if ( empty( $coupon_ids ) ) {
        return [
            'total_coupons'   => 0,
            'total_redemptions' => 0,
            'unique_beneficiaries' => 0,
        ];
    }

    // Get redemptions (canjes) for these coupons
    $placeholders = implode( ',', array_fill( 0, count( $coupon_ids ), '%d' ) );

    $stats = $wpdb->get_row( $wpdb->prepare(
        "SELECT
            COUNT(*) as total_redemptions,
            COUNT(DISTINCT user_id) as unique_beneficiaries,
            MAX(fecha_canje) as last_redemption
         FROM {$wpdb->prefix}wpcw_canjes
         WHERE coupon_id IN ($placeholders)
         AND estado_canje IN ('confirmado', 'utilizado_en_pedido_wc')",
        ...$coupon_ids
    ) );

    $metrics = [
        'total_coupons'         => count( $coupon_ids ),
        'total_redemptions'     => (int) $stats->total_redemptions,
        'unique_beneficiaries'  => (int) $stats->unique_beneficiaries,
        'last_redemption_date'  => $stats->last_redemption,
    ];

    // Update post meta
    update_post_meta( $convenio_id, '_convenio_total_coupons_created', $metrics['total_coupons'] );
    update_post_meta( $convenio_id, '_convenio_total_redemptions', $metrics['total_redemptions'] );
    update_post_meta( $convenio_id, '_convenio_total_beneficiaries_unique', $metrics['unique_beneficiaries'] );

    if ( $metrics['last_redemption_date'] ) {
        update_post_meta( $convenio_id, '_convenio_last_redemption_date', $metrics['last_redemption_date'] );
    }

    return $metrics;
}

/**
 * Update all convenios' expiry warnings
 * Should be run daily via cron
 */
function wpcw_update_expiry_warnings() {
    $args = [
        'post_type'      => 'wpcw_convenio',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [
            [
                'key'     => '_convenio_status',
                'value'   => 'active',
            ],
        ],
    ];

    $convenios = get_posts( $args );

    foreach ( $convenios as $convenio ) {
        $end_date = get_post_meta( $convenio->ID, '_convenio_end_date', true );

        if ( empty( $end_date ) ) {
            continue;
        }

        $now = current_time( 'timestamp' );
        $end_timestamp = strtotime( $end_date );
        $days_until_expiry = floor( ( $end_timestamp - $now ) / DAY_IN_SECONDS );

        update_post_meta( $convenio->ID, '_convenio_days_until_expiry', $days_until_expiry );

        // Transition to near_expiry if <= 30 days
        if ( $days_until_expiry <= 30 && $days_until_expiry > 0 ) {
            update_post_meta( $convenio->ID, '_convenio_status', 'near_expiry' );
        }

        // Transition to expired if past end date
        if ( $days_until_expiry <= 0 ) {
            update_post_meta( $convenio->ID, '_convenio_status', 'expired' );
            wp_update_post( [
                'ID'          => $convenio->ID,
                'post_status' => 'trash',
            ] );
        }

        // Send reminder at 30, 15, 7 days if not sent yet
        $reminder_sent = get_post_meta( $convenio->ID, '_convenio_renewal_reminder_sent', true );
        if ( in_array( $days_until_expiry, [ 30, 15, 7 ], true ) && ! $reminder_sent ) {
            wpcw_send_expiry_reminder( $convenio->ID, $days_until_expiry );
            update_post_meta( $convenio->ID, '_convenio_renewal_reminder_sent', true );
        }

        // Reset reminder flag if renewed
        if ( $days_until_expiry > 30 ) {
            delete_post_meta( $convenio->ID, '_convenio_renewal_reminder_sent' );
        }
    }
}
add_action( 'wpcw_daily_cron', 'wpcw_update_expiry_warnings' );
```

---

## 4. Ejemplos de Código PHP

### Formulario de Counter-Offer

```php
/**
 * Render counter-offer form in convenio response page
 *
 * @param int $convenio_id Convenio post ID
 */
function wpcw_render_counter_offer_form( $convenio_id ) {
    $convenio = get_post( $convenio_id );

    // Get current values
    $discount = get_post_meta( $convenio_id, '_convenio_discount_percentage', true );
    $max_uses = get_post_meta( $convenio_id, '_convenio_max_uses_per_beneficiary', true );
    $start_date = get_post_meta( $convenio_id, '_convenio_start_date', true );
    $end_date = get_post_meta( $convenio_id, '_convenio_end_date', true );
    $terms = get_post_meta( $convenio_id, '_convenio_terms', true );

    // Check if max rounds reached
    $rounds = (int) get_post_meta( $convenio_id, '_convenio_negotiation_rounds', true );
    $max_rounds_reached = $rounds >= 2;

    ?>
    <div id="counter-offer-form" class="postbox">
        <h2><?php _e( 'Hacer Contra-Oferta', 'wp-cupon-whatsapp' ); ?></h2>

        <?php if ( $max_rounds_reached ) : ?>
            <div class="notice notice-warning">
                <p><?php _e( 'Se ha alcanzado el máximo de 2 rondas de negociación. Solo puedes aceptar o rechazar.', 'wp-cupon-whatsapp' ); ?></p>
            </div>
        <?php else : ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'wpcw_counter_offer', 'wpcw_counter_offer_nonce' ); ?>
                <input type="hidden" name="convenio_id" value="<?php echo esc_attr( $convenio_id ); ?>">
                <input type="hidden" name="action" value="counter_offer">

                <table class="form-table">
                    <tr>
                        <th><?php _e( 'Descuento Actual', 'wp-cupon-whatsapp' ); ?></th>
                        <td>
                            <strong><?php echo esc_html( $discount ); ?>%</strong>
                            <label>
                                <input type="checkbox" name="change_discount" id="change_discount" value="1">
                                <?php _e( 'Modificar', 'wp-cupon-whatsapp' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr id="new_discount_row" style="display: none;">
                        <th><?php _e( 'Nuevo Descuento', 'wp-cupon-whatsapp' ); ?></th>
                        <td>
                            <input type="number"
                                   name="new_discount_percentage"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   class="small-text">
                            <span>%</span>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e( 'Usos Máximos por Beneficiario', 'wp-cupon-whatsapp' ); ?></th>
                        <td>
                            <strong><?php echo $max_uses ? esc_html( $max_uses ) : __( 'Ilimitado', 'wp-cupon-whatsapp' ); ?></strong>
                            <label>
                                <input type="checkbox" name="change_max_uses" id="change_max_uses" value="1">
                                <?php _e( 'Modificar', 'wp-cupon-whatsapp' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr id="new_max_uses_row" style="display: none;">
                        <th><?php _e( 'Nuevos Usos Máximos', 'wp-cupon-whatsapp' ); ?></th>
                        <td>
                            <input type="number"
                                   name="new_max_uses_per_beneficiary"
                                   min="0"
                                   step="1"
                                   class="small-text">
                            <p class="description"><?php _e( '0 = ilimitado', 'wp-cupon-whatsapp' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e( 'Vigencia Actual', 'wp-cupon-whatsapp' ); ?></th>
                        <td>
                            <strong>
                                <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ) ); ?>
                                -
                                <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ) ); ?>
                            </strong>
                            <label>
                                <input type="checkbox" name="change_dates" id="change_dates" value="1">
                                <?php _e( 'Modificar', 'wp-cupon-whatsapp' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr id="new_dates_row" style="display: none;">
                        <th><?php _e( 'Nueva Vigencia', 'wp-cupon-whatsapp' ); ?></th>
                        <td>
                            <label><?php _e( 'Desde:', 'wp-cupon-whatsapp' ); ?></label>
                            <input type="date" name="new_start_date" class="regular-text"><br>
                            <label><?php _e( 'Hasta:', 'wp-cupon-whatsapp' ); ?></label>
                            <input type="date" name="new_end_date" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th><?php _e( 'Justificación', 'wp-cupon-whatsapp' ); ?> <span class="required">*</span></th>
                        <td>
                            <textarea name="counter_offer_reason"
                                      rows="4"
                                      class="large-text"
                                      required
                                      placeholder="<?php esc_attr_e( 'Explica por qué propones estos cambios...', 'wp-cupon-whatsapp' ); ?>"></textarea>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php _e( 'Enviar Contra-Oferta', 'wp-cupon-whatsapp' ); ?>
                    </button>
                    <button type="button" id="cancel_counter_offer" class="button button-secondary">
                        <?php _e( 'Cancelar', 'wp-cupon-whatsapp' ); ?>
                    </button>
                </p>
            </form>

            <script>
            jQuery(document).ready(function($) {
                // Show/hide fields based on checkboxes
                $('#change_discount').on('change', function() {
                    $('#new_discount_row').toggle(this.checked);
                });
                $('#change_max_uses').on('change', function() {
                    $('#new_max_uses_row').toggle(this.checked);
                });
                $('#change_dates').on('change', function() {
                    $('#new_dates_row').toggle(this.checked);
                });

                $('#cancel_counter_offer').on('click', function() {
                    $('#counter-offer-form').hide();
                });
            });
            </script>
        <?php endif; ?>
    </div>
    <?php
}
```

### Handler para Counter-Offer

```php
/**
 * Handle counter-offer form submission
 */
function wpcw_handle_counter_offer_submission() {
    if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'counter_offer' ) {
        return;
    }

    // Verify nonce
    if ( ! isset( $_POST['wpcw_counter_offer_nonce'] ) ||
         ! wp_verify_nonce( $_POST['wpcw_counter_offer_nonce'], 'wpcw_counter_offer' ) ) {
        wp_die( __( 'Error de seguridad.', 'wp-cupon-whatsapp' ) );
    }

    $convenio_id = isset( $_POST['convenio_id'] ) ? absint( $_POST['convenio_id'] ) : 0;
    $reason = isset( $_POST['counter_offer_reason'] ) ? sanitize_textarea_field( $_POST['counter_offer_reason'] ) : '';

    if ( ! $convenio_id || empty( $reason ) ) {
        wp_die( __( 'Datos incompletos.', 'wp-cupon-whatsapp' ) );
    }

    // Build changes array
    $changes = [];

    if ( isset( $_POST['change_discount'] ) && $_POST['change_discount'] === '1' ) {
        $new_discount = floatval( $_POST['new_discount_percentage'] );
        if ( $new_discount > 0 && $new_discount <= 100 ) {
            $changes['discount_percentage'] = $new_discount;
        }
    }

    if ( isset( $_POST['change_max_uses'] ) && $_POST['change_max_uses'] === '1' ) {
        $new_max_uses = absint( $_POST['new_max_uses_per_beneficiary'] );
        $changes['max_uses_per_beneficiary'] = $new_max_uses;
    }

    if ( isset( $_POST['change_dates'] ) && $_POST['change_dates'] === '1' ) {
        $new_start = sanitize_text_field( $_POST['new_start_date'] );
        $new_end = sanitize_text_field( $_POST['new_end_date'] );

        if ( ! empty( $new_start ) ) {
            $changes['start_date'] = $new_start;
        }
        if ( ! empty( $new_end ) ) {
            $changes['end_date'] = $new_end;
        }
    }

    if ( empty( $changes ) ) {
        wp_die( __( 'No has seleccionado ningún cambio.', 'wp-cupon-whatsapp' ) );
    }

    // Create counter-offer
    $result = wpcw_create_counter_offer( $convenio_id, $changes, $reason );

    if ( is_wp_error( $result ) ) {
        wp_die( $result->get_error_message() );
    }

    // Redirect with success message
    $redirect_url = add_query_arg( 'wpcw_notice', 'counter_offer_sent', wp_get_referer() );
    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'admin_init', 'wpcw_handle_counter_offer_submission' );
```

---

## 5. Hooks y Acciones

### Hooks Disponibles para Extensiones

```php
/**
 * Fired when a convenio proposal is created
 *
 * @param int $convenio_id Convenio post ID
 * @param int $user_id User who created it
 */
do_action( 'wpcw_convenio_proposed', $convenio_id, $user_id );

/**
 * Fired when a counter-offer is created
 *
 * @param int   $convenio_id Convenio post ID
 * @param array $changes Array of changes made
 * @param string $reason Justification
 * @param int   $user_id User who created counter-offer
 */
do_action( 'wpcw_counter_offer_created', $convenio_id, $changes, $reason, $user_id );

/**
 * Fired when a convenio is accepted
 *
 * @param int $convenio_id Convenio post ID
 * @param int $user_id User who accepted
 */
do_action( 'wpcw_convenio_accepted', $convenio_id, $user_id );

/**
 * Fired when a convenio is rejected
 *
 * @param int $convenio_id Convenio post ID
 * @param int $user_id User who rejected
 * @param string $reason Rejection reason
 */
do_action( 'wpcw_convenio_rejected', $convenio_id, $user_id, $reason );

/**
 * Fired when a convenio becomes active
 *
 * @param int $convenio_id Convenio post ID
 */
do_action( 'wpcw_convenio_activated', $convenio_id );

/**
 * Fired when a convenio expires
 *
 * @param int $convenio_id Convenio post ID
 */
do_action( 'wpcw_convenio_expired', $convenio_id );

/**
 * Fired daily for each active convenio
 * Useful for custom calculations
 *
 * @param int $convenio_id Convenio post ID
 */
do_action( 'wpcw_convenio_daily_check', $convenio_id );
```

### Ejemplo de Uso de Hooks

```php
/**
 * Send WhatsApp notification when convenio is activated
 */
function my_custom_whatsapp_on_activation( $convenio_id ) {
    $provider_id = get_post_meta( $convenio_id, '_convenio_provider_id', true );
    $recipient_id = get_post_meta( $convenio_id, '_convenio_recipient_id', true );

    $provider_phone = get_post_meta( $provider_id, '_business_phone', true );
    $recipient_email = get_post_meta( $recipient_id, '_institution_email', true );

    if ( $provider_phone ) {
        // Send WhatsApp to business
        $message = sprintf(
            '¡Tu convenio con %s ha sido activado! Ya puedes crear cupones.',
            get_the_title( $recipient_id )
        );
        wpcw_send_whatsapp_message( $provider_phone, $message );
    }

    // Also send email to institution
    if ( $recipient_email ) {
        wp_mail(
            $recipient_email,
            'Convenio Activado',
            'El convenio ha sido activado exitosamente.'
        );
    }
}
add_action( 'wpcw_convenio_activated', 'my_custom_whatsapp_on_activation' );
```

---

## 6. Frontend: JavaScript y UI

### Vue.js Component para Counter-Offer (Opcional)

```javascript
// convenio-counter-offer.vue
<template>
  <div class="counter-offer-modal" v-if="show">
    <div class="modal-content">
      <h2>{{ __('Hacer Contra-Oferta') }}</h2>

      <div class="field-group">
        <label>
          <input type="checkbox" v-model="changes.discount.enabled">
          {{ __('Cambiar descuento') }}
        </label>
        <div v-if="changes.discount.enabled" class="sub-field">
          <span class="current-value">{{ __('Actual:') }} {{ currentValues.discount }}%</span>
          <input type="number"
                 v-model="changes.discount.value"
                 min="0"
                 max="100"
                 step="0.01"
                 class="small-text">
          <span>%</span>
        </div>
      </div>

      <div class="field-group">
        <label>
          <input type="checkbox" v-model="changes.maxUses.enabled">
          {{ __('Cambiar usos máximos') }}
        </label>
        <div v-if="changes.maxUses.enabled" class="sub-field">
          <span class="current-value">{{ __('Actual:') }} {{ currentValues.maxUses || __('Ilimitado') }}</span>
          <input type="number"
                 v-model="changes.maxUses.value"
                 min="0"
                 step="1"
                 class="small-text">
          <span class="description">{{ __('0 = ilimitado') }}</span>
        </div>
      </div>

      <div class="field-group">
        <label>{{ __('Justificación') }} <span class="required">*</span></label>
        <textarea v-model="reason"
                  rows="4"
                  :placeholder="__('Explica por qué propones estos cambios...')"
                  required></textarea>
      </div>

      <div class="changes-summary" v-if="hasChanges">
        <h3>{{ __('Resumen de Cambios') }}</h3>
        <ul>
          <li v-if="changes.discount.enabled">
            {{ __('Descuento:') }}
            <strong class="old-value">{{ currentValues.discount }}%</strong>
            →
            <strong class="new-value">{{ changes.discount.value }}%</strong>
          </li>
          <li v-if="changes.maxUses.enabled">
            {{ __('Usos máximos:') }}
            <strong class="old-value">{{ currentValues.maxUses || __('Ilimitado') }}</strong>
            →
            <strong class="new-value">{{ changes.maxUses.value || __('Ilimitado') }}</strong>
          </li>
        </ul>
      </div>

      <div class="modal-actions">
        <button @click="submitCounterOffer"
                :disabled="!canSubmit"
                class="button button-primary">
          {{ __('Enviar Contra-Oferta') }}
        </button>
        <button @click="close" class="button button-secondary">
          {{ __('Cancelar') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConvenioCounterOffer',
  props: {
    convenioId: {
      type: Number,
      required: true
    },
    currentValues: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      show: false,
      changes: {
        discount: {
          enabled: false,
          value: this.currentValues.discount
        },
        maxUses: {
          enabled: false,
          value: this.currentValues.maxUses
        }
      },
      reason: ''
    }
  },
  computed: {
    hasChanges() {
      return this.changes.discount.enabled || this.changes.maxUses.enabled;
    },
    canSubmit() {
      return this.hasChanges && this.reason.trim().length > 0;
    }
  },
  methods: {
    open() {
      this.show = true;
    },
    close() {
      this.show = false;
      this.resetForm();
    },
    resetForm() {
      this.changes.discount.enabled = false;
      this.changes.maxUses.enabled = false;
      this.reason = '';
    },
    async submitCounterOffer() {
      const data = {
        action: 'wpcw_submit_counter_offer',
        nonce: wpcw_ajax.nonce,
        convenio_id: this.convenioId,
        changes: this.buildChangesObject(),
        reason: this.reason
      };

      try {
        const response = await jQuery.post(wpcw_ajax.ajax_url, data);

        if (response.success) {
          this.$emit('counter-offer-sent', response.data);
          this.close();
        } else {
          alert(response.data.message);
        }
      } catch (error) {
        console.error('Error submitting counter-offer:', error);
        alert(this.__('Error al enviar la contra-oferta'));
      }
    },
    buildChangesObject() {
      const changes = {};

      if (this.changes.discount.enabled) {
        changes.discount_percentage = this.changes.discount.value;
      }
      if (this.changes.maxUses.enabled) {
        changes.max_uses_per_beneficiary = this.changes.maxUses.value;
      }

      return changes;
    },
    __(text) {
      // Localization helper
      return wp.i18n.__(text, 'wp-cupon-whatsapp');
    }
  }
}
</script>

<style scoped>
.counter-offer-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.field-group {
  margin-bottom: 20px;
}

.sub-field {
  margin-left: 25px;
  margin-top: 10px;
}

.current-value {
  display: block;
  color: #666;
  font-size: 0.9em;
  margin-bottom: 5px;
}

.changes-summary {
  background: #f9f9f9;
  padding: 15px;
  border-left: 4px solid #0073aa;
  margin: 20px 0;
}

.old-value {
  text-decoration: line-through;
  color: #dc3545;
}

.new-value {
  color: #28a745;
}

.modal-actions {
  margin-top: 20px;
  text-align: right;
}

.modal-actions button {
  margin-left: 10px;
}
</style>
```

---

## 7. Testing y Validación

### Unit Tests con PHPUnit

```php
/**
 * Test case for convenio negotiation
 */
class WPCW_Convenio_Negotiation_Test extends WP_UnitTestCase {

    public function setUp() {
        parent::setUp();

        // Create test users
        $this->business_owner = $this->factory->user->create([
            'role' => 'wpcw_business_owner',
        ]);

        $this->institution_manager = $this->factory->user->create([
            'role' => 'wpcw_institution_manager',
        ]);

        // Create test business and institution
        $this->business_id = $this->factory->post->create([
            'post_type' => 'wpcw_business',
            'post_title' => 'Test Business',
        ]);

        $this->institution_id = $this->factory->post->create([
            'post_type' => 'wpcw_institution',
            'post_title' => 'Test Institution',
        ]);

        // Link users to entities
        update_user_meta( $this->business_owner, '_wpcw_business_id', $this->business_id );
        update_user_meta( $this->institution_manager, '_wpcw_institution_id', $this->institution_id );
    }

    public function test_create_convenio_proposal() {
        wp_set_current_user( $this->business_owner );

        $convenio_id = wp_insert_post([
            'post_type' => 'wpcw_convenio',
            'post_title' => 'Test Convenio',
            'post_status' => 'pending',
        ]);

        update_post_meta( $convenio_id, '_convenio_provider_id', $this->business_id );
        update_post_meta( $convenio_id, '_convenio_recipient_id', $this->institution_id );
        update_post_meta( $convenio_id, '_convenio_status', 'pending_review' );
        update_post_meta( $convenio_id, '_convenio_discount_percentage', 15.0 );

        $this->assertNotEmpty( $convenio_id );
        $this->assertEquals( 'pending_review', get_post_meta( $convenio_id, '_convenio_status', true ) );
    }

    public function test_counter_offer_limits() {
        wp_set_current_user( $this->institution_manager );

        $convenio_id = $this->create_test_convenio();

        // First counter-offer should succeed
        $result1 = wpcw_create_counter_offer(
            $convenio_id,
            [ 'discount_percentage' => 20.0 ],
            'Need higher discount',
            $this->institution_manager
        );

        $this->assertTrue( $result1 );
        $this->assertEquals( 1, get_post_meta( $convenio_id, '_convenio_negotiation_rounds', true ) );

        // Accept counter-offer to continue negotiation
        update_post_meta( $convenio_id, '_convenio_status', 'under_negotiation' );

        // Second counter-offer should succeed
        $result2 = wpcw_create_counter_offer(
            $convenio_id,
            [ 'discount_percentage' => 18.0 ],
            'Counter to counter',
            $this->business_owner
        );

        $this->assertTrue( $result2 );
        $this->assertEquals( 2, get_post_meta( $convenio_id, '_convenio_negotiation_rounds', true ) );

        // Third counter-offer should fail
        $result3 = wpcw_create_counter_offer(
            $convenio_id,
            [ 'discount_percentage' => 19.0 ],
            'One more try',
            $this->institution_manager
        );

        $this->assertWPError( $result3 );
        $this->assertEquals( 'max_rounds_reached', $result3->get_error_code() );
    }

    public function test_status_transitions() {
        $convenio_id = $this->create_test_convenio();

        // Valid transition: pending_review -> awaiting_approval
        $validation1 = wpcw_validate_status_transition(
            'pending_review',
            'awaiting_approval',
            $this->institution_manager,
            $convenio_id
        );

        $this->assertTrue( $validation1 );

        // Invalid transition: pending_review -> active (should go through approval first)
        $validation2 = wpcw_validate_status_transition(
            'pending_review',
            'active',
            $this->institution_manager,
            $convenio_id
        );

        $this->assertWPError( $validation2 );
    }

    public function test_negotiation_history_logging() {
        $convenio_id = $this->create_test_convenio();

        wpcw_create_counter_offer(
            $convenio_id,
            [ 'discount_percentage' => 20.0 ],
            'Need higher discount',
            $this->institution_manager
        );

        $history = get_post_meta( $convenio_id, '_convenio_negotiation_history', true );

        $this->assertIsArray( $history );
        $this->assertNotEmpty( $history );

        $last_entry = end( $history );

        $this->assertEquals( 'counter_offered', $last_entry['action'] );
        $this->assertEquals( $this->institution_manager, $last_entry['user_id'] );
        $this->assertArrayHasKey( 'changes', $last_entry );
        $this->assertEquals( 20.0, $last_entry['changes']['discount_percentage']['new'] );
    }

    private function create_test_convenio() {
        $convenio_id = wp_insert_post([
            'post_type' => 'wpcw_convenio',
            'post_title' => 'Test Convenio',
            'post_status' => 'pending',
        ]);

        update_post_meta( $convenio_id, '_convenio_provider_id', $this->business_id );
        update_post_meta( $convenio_id, '_convenio_recipient_id', $this->institution_id );
        update_post_meta( $convenio_id, '_convenio_status', 'pending_review' );
        update_post_meta( $convenio_id, '_convenio_discount_percentage', 15.0 );
        update_post_meta( $convenio_id, '_convenio_negotiation_rounds', 0 );
        update_post_meta( $convenio_id, '_convenio_version_current', 1 );

        return $convenio_id;
    }
}
```

### Checklist de Testing Manual

```markdown
## Testing de Sistema de Convenios Bidireccionales

### Flujo Básico: Propuesta Simple
- [ ] Comercio puede crear propuesta
- [ ] Institución recibe email de notificación
- [ ] Institución puede ver propuesta en dashboard
- [ ] Institución puede aceptar propuesta
- [ ] Estado cambia a 'approved' o 'pending_supervisor'
- [ ] Comercio recibe notificación de aceptación
- [ ] Convenio activo permite crear cupones

### Flujo de Negociación: Counter-Offers
- [ ] Institución puede hacer counter-offer en primera ronda
- [ ] Comercio recibe notificación de counter-offer
- [ ] Comercio puede ver cambios propuestos (diff)
- [ ] Comercio puede aceptar counter-offer
- [ ] Comercio puede hacer counter-offer a counter-offer
- [ ] Sistema bloquea más de 2 rondas
- [ ] Mensaje de error claro cuando se alcanza límite

### Aprobación Multi-Nivel
- [ ] Institución con `requires_supervisor` entra en flujo de 2 niveles
- [ ] Manager aprueba y estado va a 'pending_supervisor'
- [ ] Supervisor recibe notificación
- [ ] Supervisor ve solo convenios pendientes de su aprobación
- [ ] Supervisor puede aprobar/rechazar
- [ ] Al aprobar, convenio se activa

### Validaciones de Negocio
- [ ] Solo partes involucradas pueden modificar convenio
- [ ] Admin puede forzar cualquier estado
- [ ] Convenios activos no permiten editar términos básicos
- [ ] Convenios vencidos no permiten crear cupones
- [ ] Fechas de vigencia son obligatorias al aprobar
- [ ] `near_expiry` se activa automáticamente a 30 días

### Métricas y Reportes
- [ ] Dashboard de institución muestra convenios activos
- [ ] Dashboard de comercio muestra estadísticas de canjes
- [ ] Admin dashboard muestra marketplace health
- [ ] Cron diario actualiza `days_until_expiry`
- [ ] Emails de reminder se envían a 30, 15, 7 días
- [ ] Métricas se calculan correctamente

### Seguridad
- [ ] Nonces válidos en todos los formularios
- [ ] Verificación de permisos en cada acción
- [ ] Tokens de respuesta se invalidan después de uso
- [ ] No se puede aprobar convenios de otros
- [ ] Logs de auditoría registran todos los cambios

### UX y Notificaciones
- [ ] Badges de estado con colores correctos
- [ ] Notificaciones in-app funcionan
- [ ] Emails tienen formato HTML responsive
- [ ] Tour de onboarding se muestra en primer login
- [ ] Wizard multi-paso es intuitivo

### Performance
- [ ] Listado de convenios carga en < 2 segundos con 1000+ convenios
- [ ] Cálculo de métricas no bloquea UI
- [ ] Tabla de agregación se actualiza correctamente
- [ ] Queries de dashboard están optimizados (no N+1)
```

---

## Conclusión

Esta guía técnica proporciona los fundamentos para implementar el sistema de convenios bidireccionales. Los ejemplos de código son específicos para el proyecto WP Cupon WhatsApp y siguen las convenciones ya establecidas.

### Recursos Adicionales

- **Documentación de WordPress:** https://developer.wordpress.org/
- **WooCommerce Hooks:** https://woocommerce.github.io/code-reference/hooks/hooks.html
- **PHPUnit para WordPress:** https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/

### Contacto

Para consultas técnicas sobre esta implementación, contactar al equipo de desarrollo de WP Cupon WhatsApp.

---

**Documento técnico elaborado:** Octubre 2025
**Versión:** 1.0
**Próxima revisión:** Tras implementación de Fase 1
