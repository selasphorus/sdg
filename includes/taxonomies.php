<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// Get plugin options to determine which modules are active
$options = get_option( 'sdg_settings' );
if ( isset($options['sdg_modules']) ) { $sdg_modules = $options['sdg_modules']; } else { $sdg_modules = array(); }

/*** Taxonomies for LECTIONARY ***/

if ( in_array('lectionary', $sdg_modules ) ) {

    // Custom Taxonomy: Liturgical Date Category
    function register_taxonomy_liturgical_date_category() {
        $labels = array(
            'name'              => _x( 'Lit Date Categories', 'taxonomy general name' ),
            'singular_name'     => _x( 'Lit Date Category', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Lit Date Categories' ),
            'all_items'         => __( 'All Lit Date Categories' ),
            'parent_item'       => __( 'Parent Lit Date Category' ),
            'parent_item_colon' => __( 'Parent Lit Date Category:' ),
            'edit_item'         => __( 'Edit Lit Date Category' ),
            'update_item'       => __( 'Update Lit Date Category' ),
            'add_new_item'      => __( 'Add New Lit Date Category' ),
            'new_item_name'     => __( 'New Lit Date Category Name' ),
            'menu_name'         => __( 'Lit Date Categories' ),
        );
        $args = array(
            'labels'            => $labels,
            'description'          => '',
            'public'               => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_menu'      => 'edit.php?post_type=lectionary',
            //'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'liturgical_date_category' ],
        );
        if ( sdg_custom_caps() ) {
            $cap = 'lectionary';
            $args['capabilities'] = array(
                'manage_terms'  =>   'manage_'.$cap.'_terms',
                'edit_terms'    =>   'edit_'.$cap.'_terms',
                'delete_terms'  =>   'delete_'.$cap.'_terms',
                'assign_terms'  =>   'assign_'.$cap.'_terms',
            );
        }
        register_taxonomy( 'liturgical_date_category', [ 'liturgical_date' ], $args ); // 'lectionary', 
    }
    add_action( 'init', 'register_taxonomy_liturgical_date_category' );

    // Custom Taxonomy: Service Type -- Obsolete?
    function register_taxonomy_service_type() {
        $labels = array(
            'name'              => _x( 'Service Types', 'taxonomy general name' ),
            'singular_name'     => _x( 'Service Type', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Service Types' ),
            'all_items'         => __( 'All Service Types' ),
            'parent_item'       => __( 'Parent Service Type' ),
            'parent_item_colon' => __( 'Parent Service Type:' ),
            'edit_item'         => __( 'Edit Service Type' ),
            'update_item'       => __( 'Update Service Type' ),
            'add_new_item'      => __( 'Add New Service Type' ),
            'new_item_name'     => __( 'New Service Type Name' ),
            'menu_name'         => __( 'Service Types' ),
        );
        $args = array(
            'labels'            => $labels,
            'description'          => '',
            'public'               => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'service_type' ],
        );
        if ( sdg_custom_caps() ) {
            $cap = 'lectionary';
            $args['capabilities'] = array(
                'manage_terms'  =>   'manage_'.$cap.'_terms',
                'edit_terms'    =>   'edit_'.$cap.'_terms',
                'delete_terms'  =>   'delete_'.$cap.'_terms',
                'assign_terms'  =>   'assign_'.$cap.'_terms',
            );
        }
        register_taxonomy( 'service_type', [ 'lectionary' ], $args );
    }
    add_action( 'init', 'register_taxonomy_service_type' );
    
    // Custom Taxonomy: Season -- DISABLED! Obsolete?
    /*function egister_taxonomy_season() {
        $labels = array(
            'name'              => _x( 'Seasons', 'taxonomy general name' ),
            'singular_name'     => _x( 'Season', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Seasons' ),
            'all_items'         => __( 'All Seasons' ),
            'parent_item'       => __( 'Parent Season' ),
            'parent_item_colon' => __( 'Parent Season:' ),
            'edit_item'         => __( 'Edit Season' ),
            'update_item'       => __( 'Update Season' ),
            'add_new_item'      => __( 'Add New Season' ),
            'new_item_name'     => __( 'New Season Name' ),
            'menu_name'         => __( 'Seasons' ),
        );
        $args = array(
            'labels'            => $labels,
            'description'          => '',
            'public'               => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'season' ],
        );
        if ( sdg_custom_caps() ) {
            $cap = 'XXX';
            $args['capabilities'] = array(
                'manage_terms'  =>   'manage_'.$cap.'_terms',
                'edit_terms'    =>   'edit_'.$cap.'_terms',
                'delete_terms'  =>   'delete_'.$cap.'_terms',
                'assign_terms'  =>   'assign_'.$cap.'_terms',
            );
        }
        register_taxonomy( 'season', [ 'collect', 'liturgical_date', 'repertoire' ], $args );
    }*/
    //add_action( 'init', 'allsouls_register_taxonomy_season' );

}

/*** Taxonomies for SERMONS ***/

if ( in_array('sermons', $sdg_modules ) ) {

    // Custom Taxonomy: Sermon Topic
    function register_taxonomy_sermon_topic() {
        $labels = array(
            'name'              => _x( 'Sermon Topics', 'taxonomy general name' ),
            'singular_name'     => _x( 'Sermon Topic', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Sermon Topics' ),
            'all_items'         => __( 'All Sermon Topics' ),
            'parent_item'       => __( 'Parent Sermon Topic' ),
            'parent_item_colon' => __( 'Parent Sermon Topic:' ),
            'edit_item'         => __( 'Edit Sermon Topic' ),
            'update_item'       => __( 'Update Sermon Topic' ),
            'add_new_item'      => __( 'Add New Sermon Topic' ),
            'new_item_name'     => __( 'New Sermon Topic Name' ),
            'menu_name'         => __( 'Sermon Topics' ),
        );
        $args = array(
            'labels'            => $labels,
            'description'          => '',
            'public'               => true,
            'hierarchical'      => false, // changed from true 11/17/22 because no topics had parents and AG wanted to be able to search
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'sermon_topic' ],
        );
        if ( sdg_custom_caps() ) {
            $cap = 'sermon';
            $args['capabilities'] = array(
                'manage_terms'  =>   'manage_'.$cap.'_terms',
                'edit_terms'    =>   'edit_'.$cap.'_terms',
                'delete_terms'  =>   'delete_'.$cap.'_terms',
                'assign_terms'  =>   'assign_'.$cap.'_terms',
            );
        }
        register_taxonomy( 'sermon_topic', [ 'sermon' ], $args );
    }
    add_action( 'init', 'register_taxonomy_sermon_topic' );

}
