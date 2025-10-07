<?php
/**
 * Tests for WPCW_Redemption_Handler class
 *
 * @package WP_Cupon_WhatsApp
 */

class Test_WPCW_Redemption_Handler extends WPCW_UnitTestCase {

    /**
     * Test redemption eligibility check
     */
    public function test_can_redeem() {
        $coupon_id = $this->create_test_coupon();
        $user_id = $this->factory->user->create();

        // Coupon not enabled for WPCW
        $can_redeem = WPCW_Redemption_Handler::can_redeem( $coupon_id, $user_id );
        $this->assertWPError( $can_redeem );

        // Enable coupon for WPCW
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->save();

        // User without WhatsApp
        $can_redeem = WPCW_Redemption_Handler::can_redeem( $coupon_id, $user_id );
        $this->assertWPError( $can_redeem );

        // Add WhatsApp to user
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Should be eligible now
        $can_redeem = WPCW_Redemption_Handler::can_redeem( $coupon_id, $user_id );
        $this->assertTrue( $can_redeem );
    }

    /**
     * Test redemption process
     */
    public function test_process_redemption_request() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();
        $user_id = $this->factory->user->create();

        // Setup coupon
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        // Setup user
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Process redemption
        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );

        $this->assertArrayHasKey( 'success', $result );
        $this->assertTrue( $result['success'] );
        $this->assertArrayHasKey( 'whatsapp_url', $result );
        $this->assertArrayHasKey( 'redemption_id', $result );
        $this->assertArrayHasKey( 'redemption_number', $result );
        $this->assertArrayHasKey( 'token', $result );

        // Verify WhatsApp URL
        $this->assertStringStartsWith( 'https://wa.me/', $result['whatsapp_url'] );
        $this->assertStringContains( '5491123456789', $result['whatsapp_url'] );

        // Verify database record
        global $wpdb;
        $redemption = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $result['redemption_id']
            )
        );

        $this->assertNotNull( $redemption );
        $this->assertEquals( $user_id, $redemption->user_id );
        $this->assertEquals( $coupon_id, $redemption->coupon_id );
        $this->assertEquals( $result['redemption_number'], $redemption->numero_canje );
        $this->assertEquals( $result['token'], $redemption->token_confirmacion );
        $this->assertEquals( 'pendiente_confirmacion', $redemption->estado_canje );
    }

    /**
     * Test redemption number generation
     */
    public function test_redemption_number_generation() {
        $reflection = new ReflectionClass( 'WPCW_Redemption_Handler' );
        $method = $reflection->getMethod( 'generate_redemption_number' );
        $method->setAccessible( true );

        $number1 = $method->invoke( null );
        $number2 = $method->invoke( null );

        // Should be different
        $this->assertNotEquals( $number1, $number2 );

        // Should have correct format (date + random)
        $this->assertMatchesRegularExpression( '/^\d{8}\d{4}$/', $number1 );
        $this->assertMatchesRegularExpression( '/^\d{8}\d{4}$/', $number2 );
    }

    /**
     * Test WhatsApp message generation
     */
    public function test_whatsapp_message_generation() {
        $coupon_id = $this->create_test_coupon();
        $redemption_number = '202312011234';
        $token = 'test_token_123';

        $reflection = new ReflectionClass( 'WPCW_Redemption_Handler' );
        $method = $reflection->getMethod( 'generate_whatsapp_message' );
        $method->setAccessible( true );

        $message = $method->invoke( null, $coupon_id, $redemption_number, $token );

        $this->assertStringContains( 'Test Coupon', $message );
        $this->assertStringContains( $redemption_number, $message );
        $this->assertStringContains( $token, $message );
    }

    /**
     * Test custom WhatsApp message
     */
    public function test_custom_whatsapp_message() {
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        $custom_message = 'Custom message for {coupon_code} with number {redemption_number} and token {token}';
        $coupon->set_whatsapp_text( $custom_message );
        $coupon->save();

        $redemption_number = '202312011234';
        $token = 'test_token_123';

        $reflection = new ReflectionClass( 'WPCW_Redemption_Handler' );
        $method = $reflection->getMethod( 'generate_whatsapp_message' );
        $method->setAccessible( true );

        $message = $method->invoke( null, $coupon_id, $redemption_number, $token );

        $this->assertEquals(
            'Custom message for Test Coupon with number 202312011234 and token test_token_123',
            $message
        );
    }

    /**
     * Test business notification
     */
    public function test_business_notification() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();
        $user_id = $this->factory->user->create();

        // Setup coupon
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        // Setup user
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Create redemption
        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );
        $redemption_id = $result['redemption_id'];

        // Test business notification
        $notification_url = WPCW_Redemption_Handler::notify_business_redemption_request( $redemption_id );

        $this->assertStringStartsWith( 'https://wa.me/5491123456789', $notification_url );
        $this->assertStringContains( 'Nueva solicitud de canje', $notification_url );
        $this->assertStringContains( 'Test Coupon', $notification_url );
    }

    /**
     * Test redemption confirmation
     */
    public function test_confirm_redemption() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();
        $user_id = $this->factory->user->create();
        $business_user_id = $this->factory->user->create();

        // Setup coupon
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        // Setup users
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );
        update_user_meta( $business_user_id, '_wpcw_business_access', array( $business_id ) );

        // Create redemption
        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );
        $redemption_id = $result['redemption_id'];

        // Confirm redemption
        $confirmation = WPCW_Redemption_Handler::confirm_redemption( $redemption_id, $business_user_id );

        $this->assertTrue( $confirmation );

        // Verify status update
        global $wpdb;
        $redemption = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $redemption_id
            )
        );

        $this->assertEquals( 'confirmado_por_negocio', $redemption->estado_canje );
        $this->assertNotNull( $redemption->fecha_confirmacion_canje );
    }

    /**
     * Test redemption confirmation without permission
     */
    public function test_confirm_redemption_no_permission() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();
        $user_id = $this->factory->user->create();
        $unauthorized_user_id = $this->factory->user->create();

        // Setup coupon
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        // Setup users
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );
        // Unauthorized user has no business access

        // Create redemption
        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );
        $redemption_id = $result['redemption_id'];

        // Try to confirm with unauthorized user
        $confirmation = WPCW_Redemption_Handler::confirm_redemption( $redemption_id, $unauthorized_user_id );

        $this->assertWPError( $confirmation );
        $this->assertEquals( 'access_denied', $confirmation->get_error_code() );
    }
}