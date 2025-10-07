<?php
/**
 * WPCW - Institution Manager Class
 *
 * Handles logic related to the Institution CPT.
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class WPCW_Institution_Manager {

    /**
     * Get all institutions for use in selectors.
     *
     * @return array An array of institutions formatted as [id => name].
     */
    public static function get_all_institutions() {
        $institutions = array();
        $args = array(
            'post_type'      => 'wpcw_institution',
            'post_status'    => 'publish',
            'posts_per_page' => -1, // Get all of them
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $institutions[ get_the_ID() ] = get_the_title();
            }
        }
        wp_reset_postdata();

        return $institutions;
    }
}
