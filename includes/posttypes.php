<?php

defined( 'ABSPATH' ) or die( 'Nope!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

// Flush rewrite rules -- to be activated only temporarily for TS of CPT 404s
function sdg_flush_rewrite_rules() {
    flush_rewrite_rules();
}
//add_action( 'init', 'sdg_flush_rewrite_rules' );

// Get plugin options to determine which modules are active
$options = get_option( 'sdg_settings' );
if ( isset($options['sdg_modules']) ) { $active_modules = $options['sdg_modules']; } else { $active_modules = array(); }

function sdg_custom_caps() {
    $use_custom_caps = false;
    if ( isset($options['use_custom_caps']) && !empty($options['use_custom_caps']) ) {
        $use_custom_caps = true;
    }
    return $use_custom_caps;
}

/*** LECTIONARY ***/

if ( in_array('lectionary', $active_modules ) ) {

    // Bible Book
    function register_post_type_bible_book() {

        //if ( sdg_custom_caps() ) { $caps = "bible_book"; } else { $caps = "post"; }
        //if ( sdg_custom_caps() ) { $caps = array('bible_book', 'bible_books'); } else { $caps = "post"; }
        if ( sdg_custom_caps() ) { $caps = array('scripture', 'scripture'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Books of the Bible', 'sdg' ),
            'singular_name' => __( 'Bible Book', 'sdg' ),
            'add_new' => __( 'New Bible Book', 'sdg' ),
            'add_new_item' => __( 'Add New Bible Book', 'sdg' ),
            'edit_item' => __( 'Edit Bible Book', 'sdg' ),
            'new_item' => __( 'New Bible Book', 'sdg' ),
            'view_item' => __( 'View Book of the Bible', 'sdg' ),
            'search_items' => __( 'Search Books of the Bible', 'sdg' ),
            'not_found' =>  __( 'No Books of the Bible Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Books of the Bible found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=lectionary',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'bible-books' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'        => true,
            'has_archive'         => true,
            'hierarchical'        => false,
            //'menu_icon'            => 'dashicons-book',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'bible_book', $args );
    
    }
    add_action( 'init', 'register_post_type_bible_book' );
    
    // Verse -- WIP
    function register_post_type_verse() {

        if ( sdg_custom_caps() ) { $caps = array('scripture', 'scripture'); } else { $caps = "post"; } // TODO: modify caps to prevent editing?
        
        $labels = array(
            'name' => __( 'Bible Verses', 'sdg' ),
            'singular_name' => __( 'Bible Verse', 'sdg' ),
            'add_new' => __( 'New Verse', 'sdg' ),
            'add_new_item' => __( 'Add New Verse', 'sdg' ),
            'edit_item' => __( 'Edit Verse', 'sdg' ),
            'new_item' => __( 'New Verse', 'sdg' ),
            'view_item' => __( 'View Verse', 'sdg' ),
            'search_items' => __( 'Search Bible Verses', 'sdg' ),
            'not_found' =>  __( 'No Bible Verses Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Bible Verses found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=lectionary',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'verses' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'        => true,
            'has_archive'         => true,
            'hierarchical'        => false,
            //'menu_icon'            => 'dashicons-book',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'verse', $args );
    
    }
    add_action( 'init', 'register_post_type_verse' );
    
    // Reading (chapter:verse pairs or ranges of pairs)
    function register_post_type_reading() {

        if ( sdg_custom_caps() ) { $caps = "lectionary_item"; } else { $caps = "post"; }
        //if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Readings', 'sdg' ),
            'singular_name' => __( 'Reading', 'sdg' ),
            'add_new' => __( 'New Reading', 'sdg' ),
            'add_new_item' => __( 'Add New Reading', 'sdg' ),
            'edit_item' => __( 'Edit Reading', 'sdg' ),
            'new_item' => __( 'New Reading', 'sdg' ),
            'view_item' => __( 'View Reading', 'sdg' ),
            'search_items' => __( 'Search Readings', 'sdg' ),
            'not_found' =>  __( 'No Readings Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Readings found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=lectionary',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'readings' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'        => true,
            'has_archive'         => true,
            'hierarchical'        => false,
            //'menu_icon'            => 'dashicons-book',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'reading', $args );
    
    }
    add_action( 'init', 'register_post_type_reading' );

    // Lectionary Day
    function register_post_type_lectionary() {

        if ( sdg_custom_caps() ) { $caps = "lectionary_item"; } else { $caps = "post"; }
        //if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Lectionary', 'sdg' ),
            'singular_name' => __( 'Lectionary Day', 'sdg' ),
            'add_new' => __( 'New Lectionary Day', 'sdg' ),
            'add_new_item' => __( 'Add New Lectionary Day', 'sdg' ),
            'edit_item' => __( 'Edit Lectionary Day', 'sdg' ),
            'new_item' => __( 'New Lectionary Day', 'sdg' ),
            'view_item' => __( 'View Lectionary', 'sdg' ),
            'search_items' => __( 'Search Lectionary', 'sdg' ),
            'not_found' =>  __( 'No Lectionary Days Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Lectionary Days found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'             => true,
            'show_in_menu'         => true,
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'lectionary' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'        => true,
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_icon'            => 'dashicons-calendar-alt',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'lectionary', $args );
    
    }
    add_action( 'init', 'register_post_type_lectionary' );

    // Liturgical Date
    function register_post_type_liturgical_date() {

        if ( sdg_custom_caps() ) { $caps = "lectionary_item"; } else { $caps = "post"; }
        //if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Liturgical Calendar', 'sdg' ),
            'singular_name' => __( 'Liturgical Date', 'sdg' ),
            'add_new' => __( 'New Liturgical Date', 'sdg' ),
            'add_new_item' => __( 'Add New Liturgical Date', 'sdg' ),
            'edit_item' => __( 'Edit Liturgical Date', 'sdg' ),
            'new_item' => __( 'New Liturgical Date', 'sdg' ),
            'view_item' => __( 'View Liturgical Date', 'sdg' ),
            'search_items' => __( 'Search Liturgical Dates', 'sdg' ),
            'not_found' =>  __( 'No Liturgical Dates Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Liturgical Dates found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=lectionary',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'liturgical-dates' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'        => true,
            'has_archive'         => true,
            'hierarchical'        => false,
            //'menu_icon'            => 'dashicons-calendar-alt',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'liturgical_date', $args );
    
    }
    add_action( 'init', 'register_post_type_liturgical_date' );

    // Liturgical Date Calculation
    function register_post_type_liturgical_date_calc() {

        if ( sdg_custom_caps() ) { $caps = "lectionary_item"; } else { $caps = "post"; }
        //if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Liturgical Date Calculations', 'sdg' ),
            'singular_name' => __( 'Liturgical Date Calculation', 'sdg' ),
            'add_new' => __( 'New Liturgical Date Calculation', 'sdg' ),
            'add_new_item' => __( 'Add New Liturgical Date Calculation', 'sdg' ),
            'edit_item' => __( 'Edit Liturgical Date Calculation', 'sdg' ),
            'new_item' => __( 'New Liturgical Date Calculation', 'sdg' ),
            'view_item' => __( 'View Liturgical Date Calculation', 'sdg' ),
            'search_items' => __( 'Search Liturgical Date Calculations', 'sdg' ),
            'not_found' =>  __( 'No Liturgical Date Calculations Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Liturgical Date Calculations found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=lectionary',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'liturgical_date_calc' ),
            'capability_type'    => $caps,
            'map_meta_cap'         => true,
            'has_archive'          => false,
            'hierarchical'         => false,
            //'menu_icon'            => 'dashicons-calendar-alt',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'liturgical_date_calc', $args );
    
    }
    add_action( 'init', 'register_post_type_liturgical_date_calc' );

    // Collect
    function register_post_type_collect() {

        if ( sdg_custom_caps() ) { $caps = "lectionary_item"; } else { $caps = "post"; }
        //if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Collects', 'sdg' ),
            'singular_name' => __( 'Collect', 'sdg' ),
            'add_new' => __( 'New Collect', 'sdg' ),
            'add_new_item' => __( 'Add New Collect', 'sdg' ),
            'edit_item' => __( 'Edit Collect', 'sdg' ),
            'new_item' => __( 'New Collect', 'sdg' ),
            'view_item' => __( 'View Collect', 'sdg' ),
            'search_items' => __( 'Search Collects', 'sdg' ),
            'not_found' =>  __( 'No Collects Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Collects found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=lectionary',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'collects' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'        => true,
            'has_archive'         => true,
            'hierarchical'        => false,
            //'menu_icon'            => 'dashicons-welcome-write-blog',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
            'taxonomies'        => array( ), //'season', 
            'show_in_rest'        => true,
        );

        register_post_type( 'collect', $args );
    
    }
    add_action( 'init', 'register_post_type_collect' );

    // Psalms of the Day
    function register_post_type_psalms_of_the_day() {

        if ( sdg_custom_caps() ) { $caps = "lectionary_item"; } else { $caps = "post"; }
        //if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Psalms of the Day', 'sdg' ),
            'singular_name' => __( 'Psalms of the Day', 'sdg' ),
            'add_new' => __( 'New Psalms of the Day', 'sdg' ),
            'add_new_item' => __( 'Add New Psalms of the Day', 'sdg' ),
            'edit_item' => __( 'Edit Psalms of the Day', 'sdg' ),
            'new_item' => __( 'New Psalms of the Day', 'sdg' ),
            'view_item' => __( 'View Psalms of the Day Record', 'sdg' ),
            'search_items' => __( 'Search Psalms of the Day', 'sdg' ),
            'not_found' =>  __( 'No Psalms of the Day Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Psalms of the Day found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=lectionary',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'psalms_of_the_day' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'         => true,
            'has_archive'          => false,
            'hierarchical'         => false,
            //'menu_icon'            => 'dashicons-welcome-write-blog',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'psalms_of_the_day', $args );
    
    }
    add_action( 'init', 'register_post_type_psalms_of_the_day' );

}

/*** SERMONS ***/

if ( in_array('sermons', $active_modules ) ) {

    // Sermon
    function register_post_type_sermon() {

        if ( sdg_custom_caps() ) { $caps = array('sermon', 'sermons'); } else { $caps = "post"; }
                
        $labels = array(
            'name' => __( 'Sermons', 'sdg' ),
            'singular_name' => __( 'Sermon', 'sdg' ),
            'add_new' => __( 'New Sermon', 'sdg' ),
            'add_new_item' => __( 'Add New Sermon', 'sdg' ),
            'edit_item' => __( 'Edit Sermon', 'sdg' ),
            'new_item' => __( 'New Sermon', 'sdg' ),
            'view_item' => __( 'View Sermon', 'sdg' ),
            'search_items' => __( 'Search Sermons', 'sdg' ),
            'not_found' =>  __( 'No Sermons Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Sermons found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'             => true,
            'show_in_menu'         => true,
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'sermons' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'         => true,
            'has_archive'          => 'sermon-archive',
            //'has_archive'          => true,
            'hierarchical'         => false,
            'menu_icon'            => 'dashicons-welcome-write-blog',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
            'taxonomies'        => array( 'sermon_topic' ),
            'show_in_rest'        => false, // i.e. false = use classic, not block editor
        );

        register_post_type( 'sermon', $args );
    
    }
    add_action( 'init', 'register_post_type_sermon' );

    // Sermon Series
    function register_post_type_sermon_series() {

        if ( sdg_custom_caps() ) { $caps = array('sermon', 'sermons'); } else { $caps = "post"; }
        
        $labels = array(
            'name' => __( 'Sermon Series', 'sdg' ),
            'singular_name' => __( 'Sermon Series', 'sdg' ),
            'add_new' => __( 'New Sermon Series', 'sdg' ),
            'add_new_item' => __( 'Add New Sermon Series', 'sdg' ),
            'edit_item' => __( 'Edit Sermon Series', 'sdg' ),
            'new_item' => __( 'New Sermon Series', 'sdg' ),
            'view_item' => __( 'View Sermon Series', 'sdg' ),
            'search_items' => __( 'Search Sermon Series', 'sdg' ),
            'not_found' =>  __( 'No Sermon Series Found', 'sdg' ),
            'not_found_in_trash' => __( 'No Sermon Series found in Trash', 'sdg' ),
        );
    
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable'=> true,
            'show_ui'              => true,
            'show_in_menu'         => 'edit.php?post_type=sermon',
            'query_var'            => true,
            'rewrite'            => array( 'slug' => 'sermon-series' ), // permalink structure slug
            'capability_type'    => $caps,
            'map_meta_cap'        => true,
            'has_archive'         => true,
            'hierarchical'        => false,
            //'menu_icon'            => 'dashicons-book',
            'menu_position'        => null,
            'supports'             => array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
            'taxonomies'        => array( ),
            'show_in_rest'        => true,
        );

        register_post_type( 'sermon_series', $args );
    
    }
    add_action( 'init', 'register_post_type_sermon_series' );

}

/*** +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ ***/

// ACF Bi-directional fields
// WIP!
// https://www.advancedcustomfields.com/resources/bidirectional-relationships/
if ( !function_exists( 'bidirectional_acf_update_value' ) 
 && !in_array( trailingslashit( WP_PLUGIN_DIR ) . 'whx4/whx4.php', wp_get_active_and_valid_plugins() ) ) {
    function bidirectional_acf_update_value( $value, $post_id, $field  ) {    
    
        // vars
        $field_name = $field['name'];
        $field_key = $field['key'];
        $global_name = 'is_updating_' . $field_name;
        
        // bail early if this filter was triggered from the update_field() function called within the loop below
        // - this prevents an infinite loop
        if( !empty($GLOBALS[ $global_name ]) ) return $value;
        
        
        // set global variable to avoid inifite loop
        // - could also remove_filter() then add_filter() again, but this is simpler
        $GLOBALS[ $global_name ] = 1;
        
        
        // loop over selected posts and add this $post_id
        if( is_array($value) ) {
        
            foreach( $value as $post_id2 ) {
                
                // load existing related posts
                $value2 = get_field($field_name, $post_id2, false);
                
                // allow for selected posts to not contain a value
                if( empty($value2) ) {
                    $value2 = array();
                }
                
                // bail early if the current $post_id is already found in selected post's $value2
                if( in_array($post_id, $value2) ) continue;
                
                // append the current $post_id to the selected post's 'related_posts' value
                $value2[] = $post_id;
                
                // update the selected post's value (use field's key for performance)
                update_field($field_key, $value2, $post_id2);
                
            }
        
        }
        
        // find posts which have been removed
        $old_value = get_field($field_name, $post_id, false);
        
        if ( is_array($old_value) ) {
            
            foreach( $old_value as $post_id2 ) {
                
                // bail early if this value has not been removed
                if( is_array($value) && in_array($post_id2, $value) ) continue;
                
                // load existing related posts
                $value2 = get_field($field_name, $post_id2, false);
                
                // bail early if no value
                if( empty($value2) ) continue;
                
                // find the position of $post_id within $value2 so we can remove it
                $pos = array_search($post_id, $value2);
                
                // remove
                unset( $value2[ $pos] );
                
                // update the un-selected post's value (use field's key for performance)
                update_field($field_key, $value2, $post_id2);
                
            }
            
        }
        
        // reset global varibale to allow this filter to function as per normal
        $GLOBALS[ $global_name ] = 0;    
        
        // return
        return $value;
    }
}

// WIP!
if ( !function_exists( 'acf_update_related_field_on_save' ) 
 && !in_array( trailingslashit( WP_PLUGIN_DIR ) . 'whx4/whx4.php', wp_get_active_and_valid_plugins() ) ) {
    function acf_update_related_field_on_save ( $post_id ) {    
    
        // TODO: figure out how to handle repeater field sub_fields -- e.g. repertoire_events << event program_items
        
        // Get newly saved values -- all fields
        //$values = get_fields( $post_id );
    
        // Check the current (updated) value of a specific field.
        $rows = get_field('program_items', $post_id);
        if ( $rows ) {
            foreach( $rows as $row ) {
                if ( isset($row['program_item'][0]) ) {
                    foreach ( $row['program_item'] as $program_item_obj_id ) {
                        $item_post_type = get_post_type( $program_item_obj_id );
                        if ( $item_post_type == 'repertoire' ) {
                            $rep_related_events = get_field('related_events', $program_item_obj_id);
                            if ( $rep_related_events ) {
                                // Check to see if post_id is already saved to rep record
                            } else {
                                // No related_events set yet, so add the post_id
                                //update_field('related_events', $post_id, $program_item_obj_id );
                            }
                        }    
                    }
                }
            }
        }
        
    }
}

if ( in_array('sermons', $active_modules ) ) {
    add_filter('acf/update_value/name=sermons_series', 'bidirectional_acf_update_value', 10, 3);
}

//
//add_filter('acf/update_value/name=related_compositions', 'bidirectional_acf_update_value', 10, 3);
//add_filter('acf/update_value/type=relationship', array($this, 'update_relationship_field'), 11, 3);
///

// function to copy data from old ACF one-way to new ACF bidirectional relationship fields
add_shortcode('convert_bidirectional_fields', 'convert_bidirectional_fields');
function convert_bidirectional_fields ( $post_id = null, $post_type = "", $old_field_name = "", $new_field_name = "", $verbose = false ) {
    
    $info = "";
    $new_vals = false;
    
    $info .= ">> convert_bidirectional_fields >><br />";
    $info .= "post_id: $post_id<br />";
    $info .= "post_type: $post_type<br />";
    $info .= "old_field_name: $old_field_name<br />";
    $info .= "new_field_name: $new_field_name<br />";
    
    // Get current ACF values, if any
    $arr_acf_values = get_field( $old_field_name, $post_id );
    if( !is_array($arr_acf_values) ) {
        $arr_acf_values = array();
    }
    if( !empty($arr_acf_values) ) { $info .= "[1] arr_acf_values: <pre>".print_r($arr_acf_values, true)."</pre>"; } else { $info .= "[1] arr_acf_values is empty.<br />"; }
    
}

