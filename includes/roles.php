<?php
/**
 * User Roles and Capabilities for the Benefits Platform.
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Manages the creation and removal of custom roles and capabilities.
 */
class WPCW_Roles_Manager {

    /**
     * Add all custom roles and capabilities.
     */
    public static function add_roles() {
        // Remove existing roles to ensure a clean slate on update.
        self::remove_roles();

        // Capability definitions
        $institution_manager_caps = [
            'read' => true,
            'manage_institution' => true, // Key capability for this role
            'view_institution_dashboard' => true,
            'manage_business_in_institution' => true,
            'view_institution_reports' => true,
            'export_institution_data' => true,
        ];

        $business_owner_caps = [
            'read' => true,
            'manage_business_profile' => true,
            'manage_local_coupons' => true,
            'opt_in_campaigns' => true,
            'manage_business_employees' => true,
            'view_business_reports' => true,
            'export_business_data' => true,
            'approve_beneficiary_requests' => true, // New capability
        ];

        $supervisor_caps = [
            'read' => true,
            'approve_beneficiary_requests' => true,
            'view_business_reports' => true, // Can see reports but not manage other settings
        ];

        $employee_caps = [
            'read' => true,
            'redeem_coupons' => true, // Key capability for this role
        ];

        // Add the roles
        add_role( 'wpcw_institution_manager', __( 'Gerente de Institución', 'wp-cupon-whatsapp' ), $institution_manager_caps );
        add_role( 'wpcw_business_owner', __( 'Dueño de Negocio', 'wp-cupon-whatsapp' ), $business_owner_caps );
        add_role( 'wpcw_benefits_supervisor', __( 'Supervisor de Beneficios', 'wp-cupon-whatsapp' ), $supervisor_caps );
        add_role( 'wpcw_employee', __( 'Staff del Negocio', 'wp-cupon-whatsapp' ), $employee_caps );

        // Add capabilities to the administrator role
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            $admin->add_cap( 'manage_institution' );
            $admin->add_cap( 'manage_business_profile' );
            $admin->add_cap( 'redeem_coupons' );
            $admin->add_cap( 'view_institution_dashboard' );
            $admin->add_cap( 'approve_beneficiary_requests' );
        }
    }

    /**
     * Remove all custom roles.
     * This is useful for deactivation or for a clean re-installation of roles.
     */
    public static function remove_roles() {
        remove_role( 'wpcw_institution_manager' );
        remove_role( 'wpcw_business_owner' );
        remove_role( 'wpcw_benefits_supervisor' );
        remove_role( 'wpcw_employee' );
        remove_role( 'wpcw_business_staff' ); // Also remove the old role if it exists
    }
}

// Register hooks for activation and deactivation
// The roles are added on plugin activation to avoid doing it on every page load.
register_activation_hook( WPCW_PLUGIN_FILE, [ 'WPCW_Roles_Manager', 'add_roles' ] );
register_deactivation_hook( WPCW_PLUGIN_FILE, [ 'WPCW_Roles_Manager', 'remove_roles' ] );

// Also add a hook to run it manually if needed, e.g., after an update
add_action( 'admin_init', function() {
    if ( get_option( 'wpcw_roles_version' ) !== '1.0' ) {
        WPCW_Roles_Manager::add_roles();
        update_option( 'wpcw_roles_version', '1.0' );
    }
} );
