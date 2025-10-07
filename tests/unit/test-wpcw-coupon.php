<?php
/**
 * Tests for WPCW_Coupon class
 *
 * @package WP_Cupon_WhatsApp
 */

class Test_WPCW_Coupon extends WPCW_UnitTestCase {

    /**
     * Test coupon creation and basic properties
     */
    public function test_coupon_creation() {
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        $this->assertInstanceOf( 'WPCW_Coupon', $coupon );
        $this->assertEquals( $coupon_id, $coupon->get_id() );
        $this->assertEquals( 'Test Coupon', $coupon->get_code() );
    }

    /**
     * Test WPCW specific properties
     */
    public function test_wpcw_properties() {
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        // Test default values
        $this->assertFalse( $coupon->is_wpcw_enabled() );
        $this->assertEquals( '', $coupon->get_associated_business_id() );
        $this->assertFalse( $coupon->is_loyalty_coupon() );
        $this->assertFalse( $coupon->is_public_coupon() );

        // Test setters
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( 123 );
        $coupon->set_loyalty_coupon( true );
        $coupon->set_public_coupon( false );

        $this->assertTrue( $coupon->is_wpcw_enabled() );
        $this->assertEquals( 123, $coupon->get_associated_business_id() );
        $this->assertTrue( $coupon->is_loyalty_coupon() );
        $this->assertFalse( $coupon->is_public_coupon() );
    }

    /**
     * Test WhatsApp message functionality
     */
    public function test_whatsapp_message() {
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        // Test default message
        $message = $coupon->get_whatsapp_text();
        $this->assertStringContains( 'Test Coupon', $message );

        // Test custom message
        $custom_message = 'Custom WhatsApp message for {coupon_code}';
        $coupon->set_whatsapp_text( $custom_message );

        $message = $coupon->get_whatsapp_text();
        $this->assertEquals( $custom_message, $message );
    }

    /**
     * Test redemption eligibility
     */
    public function test_redemption_eligibility() {
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        $user_id = $this->factory->user->create();

        // Coupon not enabled for WPCW
        $can_redeem = $coupon->can_user_redeem( $user_id );
        $this->assertWPError( $can_redeem );
        $this->assertEquals( 'coupon_not_enabled', $can_redeem->get_error_code() );

        // Enable coupon for WPCW
        $coupon->set_wpcw_enabled( true );

        // User without WhatsApp
        $can_redeem = $coupon->can_user_redeem( $user_id );
        $this->assertWPError( $can_redeem );
        $this->assertEquals( 'no_whatsapp', $can_redeem->get_error_code() );

        // Add WhatsApp to user
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Should be eligible now
        $can_redeem = $coupon->can_user_redeem( $user_id );
        $this->assertTrue( $can_redeem );
    }

    /**
     * Test WhatsApp URL generation
     */
    public function test_whatsapp_url_generation() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );

        $user_id = $this->factory->user->create();
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        $url = $coupon->get_whatsapp_redemption_url( $user_id, 'TEST123', 'token123' );

        $this->assertStringStartsWith( 'https://wa.me/5491123456789', $url );
        $this->assertStringContains( 'TEST123', $url );
        $this->assertStringContains( 'token123', $url );
    }

    /**
     * Test usage limits
     */
    public function test_usage_limits() {
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        $coupon->set_wpcw_enabled( true );
        $coupon->set_max_uses_per_user( 2 );

        $user_id = $this->factory->user->create();
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Should be eligible initially
        $can_redeem = $coupon->can_user_redeem( $user_id );
        $this->assertTrue( $can_redeem );

        // Simulate usage
        $usage_data = array(
            array(
                'user_id' => $user_id,
                'usage_count' => 2,
            ),
        );

        // Mock the used_by data
        $reflection = new ReflectionClass( $coupon );
        $property = $reflection->getProperty( 'used_by' );
        $property->setAccessible( true );
        $property->setValue( $coupon, $usage_data );

        // Should not be eligible after reaching limit
        $can_redeem = $coupon->can_user_redeem( $user_id );
        $this->assertWPError( $can_redeem );
        $this->assertEquals( 'user_limit_reached', $can_redeem->get_error_code() );
    }

    /**
     * Test loyalty coupon restrictions
     */
    public function test_loyalty_coupon_restrictions() {
        $coupon_id = $this->create_test_coupon();
        $coupon = wpcw_get_coupon( $coupon_id );

        $coupon->set_wpcw_enabled( true );
        $coupon->set_loyalty_coupon( true );

        $user_id = $this->factory->user->create();
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // User without institution membership
        $can_redeem = $coupon->can_user_redeem( $user_id );
        $this->assertWPError( $can_redeem );
        $this->assertEquals( 'not_loyalty_member', $can_redeem->get_error_code() );

        // Add institution membership
        update_user_meta( $user_id, '_wpcw_user_institution_id', 123 );

        // Should be eligible now
        $can_redeem = $coupon->can_user_redeem( $user_id );
        $this->assertTrue( $can_redeem );
    }
}