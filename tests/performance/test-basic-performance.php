<?php
/**
 * Basic performance tests for WP Cupón WhatsApp
 *
 * @package WP_Cupon_WhatsApp
 */

class Test_Basic_Performance extends WPCW_UnitTestCase {

    /**
     * Test coupon creation performance
     */
    public function test_coupon_creation_performance() {
        $start_time = microtime( true );

        // Create multiple coupons
        $coupon_ids = array();
        for ( $i = 0; $i < 10; $i++ ) {
            $coupon_ids[] = $this->create_test_coupon( array(
                'post_title' => 'Performance Test Coupon ' . $i,
            ) );
        }

        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;

        // Should complete within reasonable time (less than 1 second for 10 coupons)
        $this->assertLessThan( 1.0, $execution_time, 'Coupon creation took too long' );

        // Verify all coupons were created
        foreach ( $coupon_ids as $coupon_id ) {
            $this->assertGreaterThan( 0, $coupon_id );
            $coupon = wpcw_get_coupon( $coupon_id );
            $this->assertInstanceOf( 'WPCW_Coupon', $coupon );
        }
    }

    /**
     * Test redemption process performance
     */
    public function test_redemption_performance() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();

        // Setup coupon
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        // Create user
        $user_id = $this->factory->user->create();
        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        $start_time = microtime( true );

        // Process redemption
        $result = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );

        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;

        // Should complete within reasonable time (less than 0.5 seconds)
        $this->assertLessThan( 0.5, $execution_time, 'Redemption processing took too long' );

        // Verify result
        $this->assertTrue( $result['success'] );
        $this->assertArrayHasKey( 'redemption_id', $result );
    }

    /**
     * Test multiple concurrent redemptions
     */
    public function test_concurrent_redemptions_performance() {
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();

        // Setup coupon
        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        // Create multiple users
        $user_ids = array();
        for ( $i = 0; $i < 5; $i++ ) {
            $user_ids[] = $this->factory->user->create();
            update_user_meta( $user_ids[$i], '_wpcw_whatsapp_number', '54911' . str_pad( $i, 7, '0' ) . '000' );
        }

        $start_time = microtime( true );

        // Process multiple redemptions
        $results = array();
        foreach ( $user_ids as $user_id ) {
            $results[] = WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );
        }

        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;

        // Should complete within reasonable time (less than 2 seconds for 5 redemptions)
        $this->assertLessThan( 2.0, $execution_time, 'Concurrent redemptions took too long' );

        // Verify all results
        foreach ( $results as $result ) {
            $this->assertTrue( $result['success'] );
            $this->assertArrayHasKey( 'redemption_id', $result );
        }

        // Verify unique redemption numbers
        $redemption_numbers = array_column( $results, 'redemption_number' );
        $this->assertCount( 5, array_unique( $redemption_numbers ), 'Redemption numbers are not unique' );
    }

    /**
     * Test database query performance
     */
    public function test_database_query_performance() {
        global $wpdb;

        // Create test data
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();
        $user_id = $this->factory->user->create();

        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Create multiple redemptions
        for ( $i = 0; $i < 10; $i++ ) {
            WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );
        }

        $start_time = microtime( true );

        // Query redemptions
        $redemptions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE user_id = %d ORDER BY fecha_solicitud_canje DESC",
                $user_id
            )
        );

        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;

        // Should complete within reasonable time (less than 0.1 seconds)
        $this->assertLessThan( 0.1, $execution_time, 'Database query took too long' );

        // Verify results
        $this->assertCount( 10, $redemptions );
    }

    /**
     * Test memory usage
     */
    public function test_memory_usage() {
        $initial_memory = memory_get_usage();

        // Create test scenario
        $business_id = $this->create_test_business();
        $coupon_id = $this->create_test_coupon();
        $user_id = $this->factory->user->create();

        $coupon = wpcw_get_coupon( $coupon_id );
        $coupon->set_wpcw_enabled( true );
        $coupon->set_associated_business_id( $business_id );
        $coupon->save();

        update_user_meta( $user_id, '_wpcw_whatsapp_number', '5491123456789' );

        // Process redemption
        WPCW_Redemption_Handler::process_redemption_request( $user_id, $coupon_id );

        $final_memory = memory_get_usage();
        $memory_used = $final_memory - $initial_memory;

        // Should use reasonable memory (less than 5MB)
        $this->assertLessThan( 5 * 1024 * 1024, $memory_used, 'Memory usage is too high' );
    }
}