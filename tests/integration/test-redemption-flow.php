<?php
/**
 * Integration tests for the complete redemption flow
 *
 * @package WP_Cupon_WhatsApp
 */

class Test_Redemption_Flow_Integration extends WPCW_UnitTestCase {

    /**
     * Test complete redemption flow from coupon creation to confirmation
     */
    public function test_complete_redemption_flow() {
        // 1. Create business
        $business_id = $this->create_test_business();

        // 2. Create coupon
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->set_whatsapp_text( 'Hola, quiero canjear {coupon_code} (ID: {redemption_number}) con token {token}' );
        $coupon->save();

        // 3. Create user
        $user_id = $this->factory->user->create();
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // 4. Create business user
        $business_user_id = $this->factory->user->create();
        update_user_meta( $business_user_id, '_wpcw_business_access', array( $business_id ) );

        // 5. Initiate redemption
        $redemption_id = WPCW_Redemption_Handler::initiate_redemption( $coupon_id, $user_id, $business_id );

        $this->assertIsInt( $redemption_id );
        $this->assertGreaterThan( 0, $redemption_id );

        // 6. Verify redemption record
        global $wpdb;
        $redemption = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $redemption_id
            )
        );

        $this->assertNotNull( $redemption );
        $this->assertEquals( $user_id, $redemption->user_id );
        $this->assertEquals( $coupon_id, $redemption->coupon_id );
        $this->assertEquals( 'pendiente_confirmacion', $redemption->estado_canje );
        $this->assertNotEmpty( $redemption->numero_canje );
        $this->assertNotEmpty( $redemption->token_confirmacion );
        $this->assertNotEmpty( $redemption->whatsapp_url );

        // 7. Verify WhatsApp URL contains custom message
        $this->assertStringContains( 'Hola, quiero canjear', $redemption->whatsapp_url );
        $this->assertStringContains( 'Test Coupon', $redemption->whatsapp_url );
        $this->assertStringContains( $redemption->numero_canje, $redemption->whatsapp_url );
        $this->assertStringContains( $redemption->token_confirmacion, $redemption->whatsapp_url );

        // 8. Confirm redemption
        $confirmation = WPCW_Redemption_Handler::confirm_redemption( $redemption_id, $business_user_id );
        $this->assertTrue( $confirmation );

        // 9. Verify confirmation
        $redemption_updated = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $redemption_id
            )
        );

        $this->assertEquals( 'confirmado_por_negocio', $redemption_updated->estado_canje );
        $this->assertNotNull( $redemption_updated->fecha_confirmacion_canje );
    }

    /**
     * Test redemption with loyalty coupon
     */
    public function test_loyalty_redemption_flow() {
        // 1. Create institution
        $institution_id = wp_insert_post( array(
            'post_title'  => 'Test Institution',
            'post_type'   => 'wpcw_institution',
            'post_status' => 'publish',
        ) );

        // 2. Create business
        $business_id = $this->create_test_business();

        // 3. Create loyalty coupon
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_loyalty_coupon( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        // 4. Create user with institution membership
        $user_id = $this->factory->user->create();
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );
        update_user_meta( $user_id, '_wpcw_user_institution_id', $institution_id );

        // 5. Test redemption eligibility
        $can_redeem = WPCW_Redemption_Handler::can_redeem( $coupon_id, $user_id );
        $this->assertTrue( $can_redeem );

        // 6. Complete redemption flow
        $redemption_id = WPCW_Redemption_Handler::initiate_redemption( $coupon_id, $user_id, $business_id );
        $this->assertIsInt( $redemption_id );

        // Clean up
        wp_delete_post( $institution_id, true );
    }

    /**
     * Test multiple redemptions and usage tracking
     */
    public function test_multiple_redemptions() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();

        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->set_max_uses_per_user( 2 );
        $coupon->save();

        // Create multiple users
        $user1_id = $this->factory->user->create();
        $user2_id = $this->factory->user->create();

        update_user_meta( $user1_id, '_wpcw_whatsapp_number', '5491111111111' );
        update_user_meta( $user2_id, '_wpcw_whatsapp_number', '5491222222222' );

        // Create business user
        $business_user_id = $this->factory->user->create();
        update_user_meta( $business_user_id, '_wpcw_business_access', array( $business_id ) );

        // First redemption for user 1
        $redemption1_id = WPCW_Redemption_Handler::initiate_redemption( $coupon_id, $user1_id, $business_id );
        $this->assertIsInt( $redemption1_id );

        // Second redemption for user 1 (should still be allowed)
        $redemption2_id = WPCW_Redemption_Handler::initiate_redemption( $coupon_id, $user1_id, $business_id );
        $this->assertIsInt( $redemption2_id );

        // Third redemption for user 1 (should be blocked by limit)
        $redemption3_result = WPCW_Redemption_Handler::can_redeem( $coupon_id, $user1_id );
        $this->assertWPError( $redemption3_result );
        $this->assertEquals( 'user_limit_reached', $redemption3_result->get_error_code() );

        // Redemption for user 2 (should still be allowed)
        $redemption4_id = WPCW_Redemption_Handler::initiate_redemption( $coupon_id, $user2_id, $business_id );
        $this->assertIsInt( $redemption4_id );

        // Confirm all redemptions
        WPCW_Redemption_Handler::confirm_redemption( $redemption1_id, $business_user_id );
        WPCW_Redemption_Handler::confirm_redemption( $redemption2_id, $business_user_id );
        WPCW_Redemption_Handler::confirm_redemption( $redemption4_id, $business_user_id );

        // Verify final counts
        global $wpdb;
        $total_redemptions = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}wpcw_canjes WHERE coupon_id = %d AND estado_canje = 'confirmado_por_negocio'",
                $coupon_id
            )
        );

        $this->assertEquals( 3, $total_redemptions );
    }

    /**
     * Test error handling in redemption flow
     */
    public function test_redemption_error_handling() {
        $user_id = $this->factory->user->create();

        // Test with invalid coupon
        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, 99999 );
        $this->assertWPError( $result );
        $this->assertEquals( 'invalid_coupon', $result->get_error_code() );

        // Test with valid coupon but user without WhatsApp
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->save();

        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );
        $this->assertWPError( $result );
        $this->assertEquals( 'no_whatsapp', $result->get_error_code() );

        // Test with expired coupon
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Set coupon expiry to past date
        $past_date = date( 'Y-m-d', strtotime( '-1 day' ) );
        update_post_meta( $coupon_id, 'date_expires', $past_date );

        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );
        $this->assertWPError( $result );
        $this->assertEquals( 'coupon_expired', $result->get_error_code() );
    }
}