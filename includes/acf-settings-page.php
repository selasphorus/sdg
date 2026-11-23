<?php
/**
 * SDG Options Page
 *
 * @link https://www.advancedcustomfields.com/resources/options-page/
 */

/**
 * Check if ACF PRO is active and function exists
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_action( 'acf/init', 'sdg_register_sermons_options_page' );
}

/**
 * Register the Sermons module options page as a submenu of the Sermon CPT.
 *
 * @return void
 */
function sdg_register_sermons_options_page(): void {
    if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
        return; // ACF Pro not active.
    }

    acf_add_options_sub_page( array(
        'page_title'  => __( 'Sermons Options', 'whx4' ),
        'menu_title'  => __( 'Options', 'whx4' ),
        'menu_slug'   => 'sermons-module-options',      // Must match location 'value'.
        'parent_slug' => 'edit.php?post_type=sermon',   // Must match your CPT slug.
        'capability'  => 'manage_options',
        'post_id'     => 'options',                     // Default; read via get_field( 'x', 'option' ).
    ) );
}