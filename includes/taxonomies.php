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

// This is a test taxonomy
function register_taxonomy_color() {
     $labels = array(
         'name'              => _x( 'Colors', 'taxonomy general name' ),
         'singular_name'     => _x( 'Color', 'taxonomy singular name' ),
         'search_items'      => __( 'Search Colors' ),
         'all_items'         => __( 'All Colors' ),
         'parent_item'       => __( 'Parent Color' ),
         'parent_item_colon' => __( 'Parent Color:' ),
         'edit_item'         => __( 'Edit Color' ),
         'update_item'       => __( 'Update Color' ),
         'add_new_item'      => __( 'Add New Color' ),
         'new_item_name'     => __( 'New Color Name' ),
         'menu_name'         => __( 'Colors' ),
     );
     $args   = array(
         'hierarchical'      => true, // make it hierarchical (like categories)
         'labels'            => $labels,
         'show_ui'           => true,
         'show_admin_column' => true,
         'query_var'         => true,
         'rewrite'           => [ 'slug' => 'color' ],
     );
     register_taxonomy( 'color', [ 'sdg_dinosaur', 'post', 'repertoire', 'event' ], $args );
}
//add_action( 'init', 'register_taxonomy_color' );


/*** Taxonomies for GENERAL & ADMIN USE ***/

// Custom Taxonomy: Admin Tag
// NB: taxonomy is registered for 'collection' which is posttype defined via display-content plugin
// TODO: figure out how to correctly apply taxonomy -- redeclare via display-content, perhaps?
function register_taxonomy_admin_tag() {
    $labels = array(
        'name'              => _x( 'Admin Tags', 'taxonomy general name' ),
        'singular_name'     => _x( 'Admin Tag', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Admin Tags' ),
        'all_items'         => __( 'All Admin Tags' ),
        'parent_item'       => __( 'Parent Admin Tag' ),
        'parent_item_colon' => __( 'Parent Admin Tag:' ),
        'edit_item'         => __( 'Edit Admin Tag' ),
        'update_item'       => __( 'Update Admin Tag' ),
        'add_new_item'      => __( 'Add New Admin Tag' ),
        'new_item_name'     => __( 'New Admin Tag Name' ),
        'menu_name'         => __( 'Admin Tags' ),
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
        'rewrite'           => [ 'slug' => 'admin_tag' ],
    );
	/*if ( sdg_custom_caps() ) {
		$cap = 'music';
		$args['capabilities'] = array(
			'manage_terms'  =>   'manage_'.$cap.'_terms',
			'edit_terms'    =>   'edit_'.$cap.'_terms',
			'delete_terms'  =>   'delete_'.$cap.'_terms',
			'assign_terms'  =>   'assign_'.$cap.'_terms',
		);
	}*/
    register_taxonomy( 'admin_tag', [ 'admin_note', 'attachment', 'bible_book', 'collect', 'collection', 'data_table', 'edition', 'ensemble', 'event', 'event-recurring', 'event_series', 'lectionary', 'liturgical_date', 'liturgical_date_calc', 'location', 'music_list', 'page', 'person', 'post', 'product', 'project', 'psalms_of_the_day', 'publication', 'publisher', 'reading', 'repertoire', 'sermon', 'sermon_series', 'snippet', 'venue' ], $args );
}
add_action( 'init', 'register_taxonomy_admin_tag' );

if ( in_array('admin_notes', $sdg_modules ) ) {
	// Custom Taxonomy: Admin Notes Category
	function register_taxonomy_adminnote_category() {
		//$cap = 'XXX';
		$labels = array(
			'name'              => _x( 'Admin Note Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Admin Note Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Admin Note Categories' ),
			'all_items'         => __( 'All Admin Note Categories' ),
			'parent_item'       => __( 'Parent Admin Note Category' ),
			'parent_item_colon' => __( 'Parent Admin Note Category:' ),
			'edit_item'         => __( 'Edit Admin Note Category' ),
			'update_item'       => __( 'Update Admin Note Category' ),
			'add_new_item'      => __( 'Add New Admin Note Category' ),
			'new_item_name'     => __( 'New Admin Note Category Name' ),
			'menu_name'         => __( 'Admin Note Categories' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'adminnote_category' ],
		);
		/*if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}*/
		register_taxonomy( 'adminnote_category', [ 'admin_note' ], $args );
	}
	add_action( 'init', 'register_taxonomy_adminnote_category' );
}

if ( in_array('data_tables', $sdg_modules ) ) {
	// Custom Taxonomy: Data Table
	function register_taxonomy_data_table() {
		$labels = array(
			'name'              => _x( 'Data Tables', 'taxonomy general name' ),
			'singular_name'     => _x( 'Data Table', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Data Tables' ),
			'all_items'         => __( 'All Data Tables' ),
			'parent_item'       => __( 'Parent Data Table' ),
			'parent_item_colon' => __( 'Parent Data Table:' ),
			'edit_item'         => __( 'Edit Data Table' ),
			'update_item'       => __( 'Update Data Table' ),
			'add_new_item'      => __( 'Add New Data Table' ),
			'new_item_name'     => __( 'New Data Table Name' ),
			'menu_name'         => __( 'Data Tables' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'data_table' ],
		);
		/*if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}*/
		register_taxonomy( 'data_table', [ 'admin_note' ], $args );
	}
	add_action( 'init', 'register_taxonomy_data_table' );
}

// Custom Taxonomy: Query Tag
function register_taxonomy_query_tag() {
    $labels = array(
        'name'              => _x( 'Query Tags', 'taxonomy general name' ),
        'singular_name'     => _x( 'Query Tag', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Query Tags' ),
        'all_items'         => __( 'All Query Tags' ),
        'parent_item'       => __( 'Parent Query Tag' ),
        'parent_item_colon' => __( 'Parent Query Tag:' ),
        'edit_item'         => __( 'Edit Query Tag' ),
        'update_item'       => __( 'Update Query Tag' ),
        'add_new_item'      => __( 'Add New Query Tag' ),
        'new_item_name'     => __( 'New Query Tag Name' ),
        'menu_name'         => __( 'Query Tags' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'query_tag' ],
    );
	/*if ( sdg_custom_caps() ) {
		$cap = 'XXX';
		$args['capabilities'] = array(
			'manage_terms'  =>   'manage_'.$cap.'_terms',
			'edit_terms'    =>   'edit_'.$cap.'_terms',
			'delete_terms'  =>   'delete_'.$cap.'_terms',
			'assign_terms'  =>   'assign_'.$cap.'_terms',
		);
	}*/
    register_taxonomy( 'query_tag', [ 'admin_note' ], $args );
}
add_action( 'init', 'register_taxonomy_query_tag' );

/*** Taxonomies for DEFAULT POST TYPES ***/

// Custom Taxonomy: Media Category
function register_taxonomy_media_category() {
    $labels = array(
        'name'              => _x( 'Media Categories', 'taxonomy general name' ),
        'singular_name'     => _x( 'Media Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Media Categories' ),
        'all_items'         => __( 'All Media Categories' ),
        'parent_item'       => __( 'Parent Media Category' ),
        'parent_item_colon' => __( 'Parent Media Category:' ),
        'edit_item'         => __( 'Edit Media Category' ),
        'update_item'       => __( 'Update Media Category' ),
        'add_new_item'      => __( 'Add New Media Category' ),
        'new_item_name'     => __( 'New Media Category Name' ),
        'menu_name'         => __( 'Media Categories' ),
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
        'rewrite'           => [ 'slug' => 'media_category' ],
    );
    /*if ( sdg_custom_caps() ) {
		$cap = 'XXX';
		$args['capabilities'] = array(
			'manage_terms'  =>   'manage_'.$cap.'_terms',
			'edit_terms'    =>   'edit_'.$cap.'_terms',
			'delete_terms'  =>   'delete_'.$cap.'_terms',
			'assign_terms'  =>   'assign_'.$cap.'_terms',
		);
	}*/	
    register_taxonomy( 'media_category', [ 'attachment' ], $args );
}
add_action( 'init', 'register_taxonomy_media_category' );

// Custom Taxonomy: Media Tag
function register_taxonomy_media_tag() {
    $labels = array(
        'name'              => _x( 'Media Tags', 'taxonomy general name' ),
        'singular_name'     => _x( 'Media Tag', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Media Tags' ),
        'all_items'         => __( 'All Media Tags' ),
        'parent_item'       => __( 'Parent Media Tag' ),
        'parent_item_colon' => __( 'Parent Media Tag:' ),
        'edit_item'         => __( 'Edit Media Tag' ),
        'update_item'       => __( 'Update Media Tag' ),
        'add_new_item'      => __( 'Add New Media Tag' ),
        'new_item_name'     => __( 'New Media Tag Name' ),
        'menu_name'         => __( 'Media Tags' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'media_tag' ],
    );
    /*if ( sdg_custom_caps() ) {
		$cap = 'XXX';
		$args['capabilities'] = array(
			'manage_terms'  =>   'manage_'.$cap.'_terms',
			'edit_terms'    =>   'edit_'.$cap.'_terms',
			'delete_terms'  =>   'delete_'.$cap.'_terms',
			'assign_terms'  =>   'assign_'.$cap.'_terms',
		);
	}*/	
    register_taxonomy( 'media_tag', [ 'attachment' ], $args );
}
add_action( 'init', 'register_taxonomy_media_tag' );


// Custom Taxonomy: Page Tag
function register_taxonomy_page_tag() {
    //$cap = 'XXX';
    $labels = array(
        'name'              => _x( 'Page Tags', 'taxonomy general name' ),
        'singular_name'     => _x( 'Page Tag', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Page Tags' ),
        'all_items'         => __( 'All Page Tags' ),
        'parent_item'       => __( 'Parent Page Tag' ),
        'parent_item_colon' => __( 'Parent Page Tag:' ),
        'edit_item'         => __( 'Edit Page Tag' ),
        'update_item'       => __( 'Update Page Tag' ),
        'add_new_item'      => __( 'Add New Page Tag' ),
        'new_item_name'     => __( 'New Page Tag Name' ),
        'menu_name'         => __( 'Page Tags' ),
    );
    $args = array(
        'labels'            => $labels,
        'description'          => '',
        'public'               => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        /*'capabilities'         => array(
            'manage_terms'  =>   'manage_'.$cap.'_terms',
            'edit_terms'    =>   'edit_'.$cap.'_terms',
            'delete_terms'  =>   'delete_'.$cap.'_terms',
            'assign_terms'  =>   'assign_'.$cap.'_terms',
        ),*/
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'page_tag' ],
    );
    register_taxonomy( 'page_tag', [ 'page' ], $args );
}
add_action( 'init', 'register_taxonomy_page_tag' );



/*** Taxonomies for PEOPLE ***/
/*** Taxonomies for GROUPS (ORGS/ENSEMBLES) ***/
/*** Taxonomies for EVENT PROGRAMS ***/
// >>>> See WHX4 for person, group, event-program-related taxonomies <<<< //


/*** Taxonomies for PROJECTS ***/

if ( in_array('projects', $sdg_modules ) ) {
	// Custom Taxonomy: Project Category
	function register_taxonomy_project_category() {
		$labels = array(
			'name'              => _x( 'Project Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Project Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Project Categories' ),
			'all_items'         => __( 'All Project Categories' ),
			'parent_item'       => __( 'Parent Project Category' ),
			'parent_item_colon' => __( 'Parent Project Category:' ),
			'edit_item'         => __( 'Edit Project Category' ),
			'update_item'       => __( 'Update Project Category' ),
			'add_new_item'      => __( 'Add New Project Category' ),
			'new_item_name'     => __( 'New Project Category Name' ),
			'menu_name'         => __( 'Project Categories' ),
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
			'rewrite'           => [ 'slug' => 'project_category' ],
		);
		/*if ( sdg_custom_caps() ) {
			$cap = 'project';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}*/	
		register_taxonomy( 'project_category', [ 'project' ], $args );
	}
	add_action( 'init', 'register_taxonomy_project_category' );
}

/*** Taxonomies for REPERTOIRE ***/
// >>>> MLIB
if ( in_array('music', $sdg_modules ) ) {

	/*** Taxonomies for REPERTOIRE ***/
	/*
	// Custom Taxonomy: Occasion
	function register_taxonomy_occasion() {
		$labels = array(
			'name'              => _x( 'Occasions', 'taxonomy general name' ),
			'singular_name'     => _x( 'Occasion', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Occasions' ),
			'all_items'         => __( 'All Occasions' ),
			'parent_item'       => __( 'Parent Occasion' ),
			'parent_item_colon' => __( 'Parent Occasion:' ),
			'edit_item'         => __( 'Edit Occasion' ),
			'update_item'       => __( 'Update Occasion' ),
			'add_new_item'      => __( 'Add New Occasion' ),
			'new_item_name'     => __( 'New Occasion Name' ),
			'menu_name'         => __( 'Occasions' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'occasion' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'occasion', [ 'repertoire' ], $args );
	}
	add_action( 'init', 'register_taxonomy_occasion' );

	// Custom Taxonomy: Repertoire Category
	function register_taxonomy_repertoire_category() {
		$labels = array(
			'name'              => _x( 'Rep Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Repertoire Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Rep Categories' ),
			'all_items'         => __( 'All Rep Categories' ),
			'parent_item'       => __( 'Parent Rep Category' ),
			'parent_item_colon' => __( 'Parent Rep Category:' ),
			'edit_item'         => __( 'Edit Rep Category' ),
			'update_item'       => __( 'Update Rep Category' ),
			'add_new_item'      => __( 'Add New Rep Category' ),
			'new_item_name'     => __( 'New Rep Category Name' ),
			'menu_name'         => __( 'Rep Categories' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'repertoire_category' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'repertoire_category', [ 'repertoire' ], $args );
	}
	add_action( 'init', 'register_taxonomy_repertoire_category' );
	*/

	/*** Taxonomies for EDITIONS ***/
	/*
	// Custom Taxonomy: Instrument
	function register_taxonomy_instrument() {
		$labels = array(
			'name'              => _x( 'Instruments', 'taxonomy general name' ),
			'singular_name'     => _x( 'Instrument', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Instruments' ),
			'all_items'         => __( 'All Instruments' ),
			'parent_item'       => __( 'Parent Instrument' ),
			'parent_item_colon' => __( 'Parent Instrument:' ),
			'edit_item'         => __( 'Edit Instrument' ),
			'update_item'       => __( 'Update Instrument' ),
			'add_new_item'      => __( 'Add New Instrument' ),
			'new_item_name'     => __( 'New Instrument Name' ),
			'menu_name'         => __( 'Instrument' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			//'publicly_queryable'   => true, // inherit from 'public'
			'hierarchical'      => true, // make it hierarchical (like categories)
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'instrument' ],
			//'default_term'         => array( 'name', 'slug', 'description' ),
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'instrument', [ 'edition' ], $args );
	}
	add_action( 'init', 'register_taxonomy_instrument' );

	// Custom Taxonomy: Key
	function register_taxonomy_key() {
		$labels = array(
			'name'              => _x( 'Keys', 'taxonomy general name' ),
			'singular_name'     => _x( 'Key', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Keys' ),
			'all_items'         => __( 'All Keys' ),
			'parent_item'       => __( 'Parent Key' ),
			'parent_item_colon' => __( 'Parent Key:' ),
			'edit_item'         => __( 'Edit Key' ),
			'update_item'       => __( 'Update Key' ),
			'add_new_item'      => __( 'Add New Key' ),
			'new_item_name'     => __( 'New Key Name' ),
			'menu_name'         => __( 'Key' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'key' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'key', [ 'edition' ], $args );
	}
	add_action( 'init', 'register_taxonomy_key' );

	// Custom Taxonomy: Soloist
	function register_taxonomy_soloist() {
		$labels = array(
			'name'              => _x( 'Soloists', 'taxonomy general name' ),
			'singular_name'     => _x( 'Soloist', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Soloists' ),
			'all_items'         => __( 'All Soloists' ),
			'parent_item'       => __( 'Parent Soloist' ),
			'parent_item_colon' => __( 'Parent Soloist:' ),
			'edit_item'         => __( 'Edit Soloist' ),
			'update_item'       => __( 'Update Soloist' ),
			'add_new_item'      => __( 'Add New Soloist' ),
			'new_item_name'     => __( 'New Soloist Name' ),
			'menu_name'         => __( 'Soloist' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'soloist' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'soloist', [ 'edition' ], $args );
	}
	add_action( 'init', 'register_taxonomy_soloist' ); // TMP disabled until I figure out how to add fields: Abbreviation (abbr) & Sort Num (sort_num)

	// Custom Taxonomy: Voicing
	function register_taxonomy_voicing() {
		$labels = array(
			'name'              => _x( 'Voicings', 'taxonomy general name' ),
			'singular_name'     => _x( 'Voicing', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Voicings' ),
			'all_items'         => __( 'All Voicings' ),
			'parent_item'       => __( 'Parent Voicing' ),
			'parent_item_colon' => __( 'Parent Voicing:' ),
			'edit_item'         => __( 'Edit Voicing' ),
			'update_item'       => __( 'Update Voicing' ),
			'add_new_item'      => __( 'Add New Voicing' ),
			'new_item_name'     => __( 'New Voicing Name' ),
			'menu_name'         => __( 'Voicing' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'voicing' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'voicing', [ 'edition' ], $args );
	}
	add_action( 'init', 'register_taxonomy_voicing' );

	// Custom Taxonomy: Library Tag
	function register_taxonomy_library_tag() {	
		$labels = array(
			'name'              => _x( 'Library Tags', 'taxonomy general name' ),
			'singular_name'     => _x( 'Library Tag', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Library Tags' ),
			'all_items'         => __( 'All Library Tags' ),
			'parent_item'       => __( 'Parent Library Tag' ),
			'parent_item_colon' => __( 'Parent Library Tag:' ),
			'edit_item'         => __( 'Edit Library Tag' ),
			'update_item'       => __( 'Update Library Tag' ),
			'add_new_item'      => __( 'Add New Library Tag' ),
			'new_item_name'     => __( 'New Library Tag Name' ),
			'menu_name'         => __( 'Library Tags' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_menu'      => 'edit.php?post_type=repertoire',
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'library_tag' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}	
		register_taxonomy( 'library_tag', [ 'edition' ], $args );
	}
	add_action( 'init', 'register_taxonomy_library_tag' );
	*/

	/*** Taxonomies for PUBLICATIONS ***/
	/*
	// Custom Taxonomy: Publication Category
	function register_taxonomy_publication_category() {
		$labels = array(
			'name'              => _x( 'Publication Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Publication Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Publication Categories' ),
			'all_items'         => __( 'All Publication Categories' ),
			'parent_item'       => __( 'Parent Publication Category' ),
			'parent_item_colon' => __( 'Parent Publication Category:' ),
			'edit_item'         => __( 'Edit Publication Category' ),
			'update_item'       => __( 'Update Publication Category' ),
			'add_new_item'      => __( 'Add New Publication Category' ),
			'new_item_name'     => __( 'New Publication Category Name' ),
			'menu_name'         => __( 'Publication Categories' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'publication_category' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'music';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'publication_category', [ 'publication' ], $args );
	}
	add_action( 'init', 'register_taxonomy_publication_category' );
	*/
}

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

/*** Taxonomies for VENUES ***/
// >>>> WHX4

/*** Taxonomies for ORGANS ***/
// >>>> MLIB
// TODO: generalize as instrument/instrument_tag
/*
if ( in_array('organs', $sdg_modules ) ) {

	// Custom Taxonomy: Action Type
	function register_taxonomy_action_type() {
		$labels = array(
			'name'              => _x( 'Action Types', 'taxonomy general name' ),
			'singular_name'     => _x( 'Action Type', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Action Types' ),
			'all_items'         => __( 'All Action Types' ),
			'parent_item'       => __( 'Parent Action Type' ),
			'parent_item_colon' => __( 'Parent Action Type:' ),
			'edit_item'         => __( 'Edit Action Type' ),
			'update_item'       => __( 'Update Action Type' ),
			'add_new_item'      => __( 'Add New Action Type' ),
			'new_item_name'     => __( 'New Action Type Name' ),
			'menu_name'         => __( 'Action Types' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'action_type' ],
		);
		if ( sdg_custom_caps() ) {
			$cap = 'organ';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}
		register_taxonomy( 'action_type', [ 'organ' ], $args );
	}
	add_action( 'init', 'register_taxonomy_action_type' );
	
	// Custom Taxonomy: Organ Tag
	function register_taxonomy_organ_tag() {
		$cap = 'organ';
		$labels = array(
			'name'              => _x( 'Organ Tags', 'taxonomy general name' ),
			'singular_name'     => _x( 'Organ Tag', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Organ Tags' ),
			'all_items'         => __( 'All Organ Tags' ),
			'parent_item'       => __( 'Parent Organ Tag' ),
			'parent_item_colon' => __( 'Parent Organ Tag:' ),
			'edit_item'         => __( 'Edit Organ Tag' ),
			'update_item'       => __( 'Update Organ Tag' ),
			'add_new_item'      => __( 'Add New Organ Tag' ),
			'new_item_name'     => __( 'New Organ Tag Name' ),
			'menu_name'         => __( 'Organ Tags' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'capabilities'         => array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			),
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'organ_tag' ],
		);
		register_taxonomy( 'organ_tag', [ 'organ' ], $args );
	}
	add_action( 'init', 'register_taxonomy_organ_tag' );
	
}
*/
/*** Taxonomies for LINKS ***/

if ( in_array('links', $sdg_modules ) ) {

	// Custom Taxonomy: Link Category WIP
	function register_taxonomy_link_category() {
		$labels = array(
			'name'              => _x( 'Link Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Link Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Link Categories' ),
			'all_items'         => __( 'All Link Categories' ),
			'parent_item'       => __( 'Parent Link Category' ),
			'parent_item_colon' => __( 'Parent Link Category:' ),
			'edit_item'         => __( 'Edit Link Category' ),
			'update_item'       => __( 'Update Link Category' ),
			'add_new_item'      => __( 'Add New Link Category' ),
			'new_item_name'     => __( 'New Link Category Name' ),
			'menu_name'         => __( 'Link Categories' ),
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
			'rewrite'           => [ 'slug' => 'link_category' ],
		);
		/*if ( sdg_custom_caps() ) {
			$cap = 'link';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}*/
		register_taxonomy( 'link_category', [ 'link' ], $args );
	}
	add_action( 'init', 'register_taxonomy_link_category' );
	
	// Custom Taxonomy: Link Tag (WIP ??? does it need to be that specific, or would generic tags do just as well?)
	function register_taxonomy_link_tag() {
		$labels = array(
			'name'              => _x( 'Link Tags', 'taxonomy general name' ),
			'singular_name'     => _x( 'Link Tag', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Link Tags' ),
			'all_items'         => __( 'All Link Tags' ),
			'parent_item'       => __( 'Parent Link Tag' ),
			'parent_item_colon' => __( 'Parent Link Tag:' ),
			'edit_item'         => __( 'Edit Link Tag' ),
			'update_item'       => __( 'Update Link Tag' ),
			'add_new_item'      => __( 'Add New Link Tag' ),
			'new_item_name'     => __( 'New Link Tag Name' ),
			'menu_name'         => __( 'Link Tags' ),
		);
		$args = array(
			'labels'            => $labels,
			'description'          => '',
			'public'               => true,
			'hierarchical'      => false, // changed from true 11/17/22 because no topics had parents and AG wanted to be able to search
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'link_tag' ],
		);
		/*if ( sdg_custom_caps() ) {
			$cap = 'link';
			$args['capabilities'] = array(
				'manage_terms'  =>   'manage_'.$cap.'_terms',
				'edit_terms'    =>   'edit_'.$cap.'_terms',
				'delete_terms'  =>   'delete_'.$cap.'_terms',
				'assign_terms'  =>   'assign_'.$cap.'_terms',
			);
		}*/
		register_taxonomy( 'link_tag', [ 'link' ], $args );
	}
	add_action( 'init', 'register_taxonomy_link_tag' );
	
}

?>