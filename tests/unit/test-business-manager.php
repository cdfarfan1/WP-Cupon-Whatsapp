<?php
/**
 * Tests for WPCW_Business_Manager class
 *
 * @package WP_Cupon_WhatsApp
 */

class Test_WPCW_Business_Manager extends WPCW_UnitTestCase {

    /**
     * Test getting business applications
     */
    public function test_get_business_applications() {
        // Create test applications
        $application1_id = $this->create_test_application();
        $application2_id = $this->create_test_application();

        $applications_data = WPCW_Business_Manager::get_business_applications();

        $this->assertArrayHasKey( 'applications', $applications_data );
        $this->assertArrayHasKey( 'total', $applications_data );
        $this->assertGreaterThanOrEqual( 2, $applications_data['total'] );

        // Test filtering by status
        $pending_apps = WPCW_Business_Manager::get_business_applications( array(
            'application_status' => 'pendiente_revision'
        ) );

        $this->assertArrayHasKey( 'applications', $pending_apps );
    }

    /**
     * Test getting businesses
     */
    public function test_get_businesses() {
        // Create test businesses
        $business1_id = $this->create_test_business();
        $business2_id = $this->create_test_business();

        $businesses_data = WPCW_Business_Manager::get_businesses();

        $this->assertArrayHasKey( 'businesses', $businesses_data );
        $this->assertArrayHasKey( 'total', $businesses_data );
        $this->assertGreaterThanOrEqual( 2, $businesses_data['total'] );
    }

    /**
     * Test application approval
     */
    public function test_approve_application() {
        $application_id = $this->create_test_application();
        $business_id = $this->create_test_business();

        // Update application to have business association
        update_post_meta( $application_id, '_wpcw_applicant_type', 'comercio' );

        $result = WPCW_Business_Manager::approve_application( $application_id );

        $this->assertIsInt( $result );
        $this->assertGreaterThan( 0, $result );

        // Verify application status
        $status = get_post_meta( $application_id, '_wpcw_application_status', true );
        $this->assertEquals( 'aprobada', $status );

        // Verify business was created
        $business = get_post( $result );
        $this->assertNotNull( $business );
        $this->assertEquals( 'wpcw_business', $business->post_type );
    }

    /**
     * Test application rejection
     */
    public function test_reject_application() {
        $application_id = $this->create_test_application();

        $result = WPCW_Business_Manager::reject_application( $application_id, 'No cumple requisitos' );

        $this->assertTrue( $result );

        // Verify application status
        $status = get_post_meta( $application_id, '_wpcw_application_status', true );
        $this->assertEquals( 'rechazada', $status );

        // Verify rejection reason
        $reason = get_post_meta( $application_id, '_wpcw_rejection_reason', true );
        $this->assertEquals( 'No cumple requisitos', $reason );
    }

    /**
     * Test business statistics
     */
    public function test_get_business_stats() {
        $business_id = $this->create_test_business();

        $stats = WPCW_Business_Manager::get_business_stats( $business_id );

        $this->assertArrayHasKey( 'coupons_total', $stats );
        $this->assertArrayHasKey( 'coupons_active', $stats );
        $this->assertArrayHasKey( 'redemptions_total', $stats );
        $this->assertArrayHasKey( 'redemptions_this_month', $stats );
        $this->assertArrayHasKey( 'users_served', $stats );

        // All should be numeric
        foreach ( $stats as $key => $value ) {
            $this->assertIsInt( $value );
        }
    }

    /**
     * Test user assignment to business
     */
    public function test_assign_user_to_business() {
        $business_id = $this->create_test_business();
        $user_id = $this->factory->user->create();

        $result = WPCW_Business_Manager::assign_user_to_business( $user_id, $business_id, 'staff' );

        $this->assertTrue( $result );

        // Verify user meta
        $business_access = get_user_meta( $user_id, '_wpcw_business_access', true );
        $this->assertContains( $business_id, $business_access );

        $business_role = get_user_meta( $user_id, '_wpcw_business_role', true );
        $this->assertEquals( 'staff', $business_role );

        // Verify user role
        $user = new WP_User( $user_id );
        $this->assertContains( 'wpcw_business_staff', $user->roles );
    }

    /**
     * Test user removal from business
     */
    public function test_remove_user_from_business() {
        $business_id = $this->create_test_business();
        $user_id = $this->factory->user->create();

        // First assign user
        WPCW_Business_Manager::assign_user_to_business( $user_id, $business_id, 'staff' );

        // Then remove
        $result = WPCW_Business_Manager::remove_user_from_business( $user_id, $business_id );

        $this->assertTrue( $result );

        // Verify user meta is cleared
        $business_access = get_user_meta( $user_id, '_wpcw_business_access', true );
        $this->assertNotContains( $business_id, $business_access );

        $business_role = get_user_meta( $user_id, '_wpcw_business_role', true );
        $this->assertEmpty( $business_role );
    }

    /**
     * Test error handling for invalid application
     */
    public function test_approve_invalid_application() {
        $result = WPCW_Business_Manager::approve_application( 99999 );

        $this->assertWPError( $result );
        $this->assertEquals( 'invalid_application', $result->get_error_code() );
    }

    /**
     * Test error handling for invalid business
     */
    public function test_assign_user_to_invalid_business() {
        $user_id = $this->factory->user->create();

        $result = WPCW_Business_Manager::assign_user_to_business( $user_id, 99999, 'staff' );

        $this->assertWPError( $result );
        $this->assertEquals( 'invalid_business', $result->get_error_code() );
    }

    /**
     * Test error handling for invalid user
     */
    public function test_assign_invalid_user_to_business() {
        $business_id = $this->create_test_business();

        $result = WPCW_Business_Manager::assign_user_to_business( 99999, $business_id, 'staff' );

        $this->assertWPError( $result );
        $this->assertEquals( 'invalid_user', $result->get_error_code() );
    }
}