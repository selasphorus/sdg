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
add_action( 'init', 'sdg_flush_rewrite_rules' );

// Get plugin options to determine which modules are active
$options = get_option( 'sdg_settings' );
if ( isset($options['sdg_modules']) ) { $sdg_modules = $options['sdg_modules']; } else { $sdg_modules = array(); }

function sdg_custom_caps() {
	$use_custom_caps = false;
	if ( isset($options['use_custom_caps']) && !empty($options['use_custom_caps']) ) {
		$use_custom_caps = true;
	}
	return $use_custom_caps;
}

// TODO: review and revise capabilities to make sure they'll be compatible for sites with and without sophisticated permissions management (e.g. Members plugin)
/*if ( is_plugin_active( 'plugin-directory/plugin-file.php' ) ) {
	//plugin is activated
}*/

/*** GENERAL/ADMIN POST TYPES ***/

if ( in_array('admin_notes', $sdg_modules ) ) {
	// Admin Note
	function register_post_type_admin_note() {

		//if ( sdg_custom_caps() ) { $caps = array('admin_note', 'admin_notes'); } else { $caps = "post"; }
		if ( sdg_custom_caps() ) { $caps = "admin_note"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Admin Notes', 'sdg' ),
			'singular_name' => __( 'Admin Note', 'sdg' ),
			'add_new' => __( 'New Admin Note', 'sdg' ),
			'add_new_item' => __( 'Add New Admin Note', 'sdg' ),
			'edit_item' => __( 'Edit Admin Note', 'sdg' ),
			'new_item' => __( 'New Admin Note', 'sdg' ),
			'view_item' => __( 'View Admin Note', 'sdg' ),
			'search_items' => __( 'Search Admin Notes', 'sdg' ),
			'not_found' =>  __( 'No Admin Notes Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Admin Notes found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite' 			=> array( 'slug' => 'admin_notes' ), // permalink structure slug
			'capability_type' 	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'   		=> 'dashicons-groups',
			'menu_position'     => null,
			'supports'			=> array( 'title', 'author', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), // 
			'taxonomies' 		=> array( 'adminnote_category', 'admin_tag', 'data_table', 'query_tag', 'admin_tag' ),
			'show_in_rest'		=> false,    
		);

		register_post_type( 'admin_note', $args );
	
	}
	add_action( 'init', 'register_post_type_admin_note' );
}

// DB Query -- deprecated -- merged with Admin Notes
function register_post_type_db_query() {

	if ( sdg_custom_caps() ) { $caps = array('admin_note', 'admin_notes'); } else { $caps = "post"; }
	
	$labels = array(
		'name' => __( 'DB Queries', 'sdg' ),
		'singular_name' => __( 'DB Query', 'sdg' ),
		'add_new' => __( 'New DB Query', 'sdg' ),
		'add_new_item' => __( 'Add New DB Query', 'sdg' ),
		'edit_item' => __( 'Edit DB Query', 'sdg' ),
		'new_item' => __( 'New DB Query', 'sdg' ),
		'view_item' => __( 'View DB Querie', 'sdg' ),
		'search_items' => __( 'Search DB Queries', 'sdg' ),
		'not_found' =>  __( 'No DB Queries Found', 'sdg' ),
		'not_found_in_trash' => __( 'No DB Queries found in Trash', 'sdg' ),
	);
	
	$args = array(
		'labels' => $labels,
	 	'public' => true,
        'publicly_queryable'=> true,
        'show_ui'  			=> true,
        'show_in_menu' 		=> 'edit.php?post_type=admin_note',
        'query_var'			=> true,
        'rewrite'			=> array( 'slug' => 'db_query' ),
        'capability_type'	=> $caps,
        'map_meta_cap' 		=> true,
        'has_archive'  		=> true,
        'hierarchical' 		=> false,
	 	//'menu_icon'			=> 'dashicons-welcome-write-blog',
        'menu_position'		=> null,
        'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
		'taxonomies'		=> array( 'data_table', 'query_tag', 'admin_tag' ),
		'show_in_rest'		=> true,    
	);

	register_post_type( 'db_query', $args );
	
}
//add_action( 'init', 'register_post_type_db_query' );

if ( in_array('data_tables', $sdg_modules ) ) {
	// Data Table
	function register_post_type_data_table() {

		if ( sdg_custom_caps() ) { $caps = array('admin_note', 'admin_notes'); } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Data Tables', 'sdg' ),
			'singular_name' => __( 'Data Table', 'sdg' ),
			'add_new' => __( 'New Data Table', 'sdg' ),
			'add_new_item' => __( 'Add New Data Table', 'sdg' ),
			'edit_item' => __( 'Edit Data Table', 'sdg' ),
			'new_item' => __( 'New Data Table', 'sdg' ),
			'view_item' => __( 'View Data Tables', 'sdg' ),
			'search_items' => __( 'Search Data Tables', 'sdg' ),
			'not_found' =>  __( 'No Data Tables Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Data Tables found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=admin_note',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'data-tables' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,    
		);

		register_post_type( 'data_table', $args );
	
	}
	add_action( 'init', 'register_post_type_data_table' );
}

if ( in_array('snippets', $sdg_modules ) ) {
	// Snippet
	function register_post_type_snippet() {

		if ( sdg_custom_caps() ) { $caps = "snippet"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Snippets', 'sdg' ),
			'singular_name' => __( 'Snippet', 'sdg' ),
			'add_new' => __( 'New Snippet', 'sdg' ),
			'add_new_item' => __( 'Add New Snippet', 'sdg' ),
			'edit_item' => __( 'Edit Snippet', 'sdg' ),
			'new_item' => __( 'New Snippet', 'sdg' ),
			'view_item' => __( 'View Snippet', 'sdg' ),
			'search_items' => __( 'Search Snippets', 'sdg' ),
			'not_found' =>  __( 'No Snippets Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Snippets found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite' 			=> array( 'slug' => 'snippets' ), // permalink structure slug
			'capability_type' 	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'   		=> 'dashicons-groups',
			'menu_position'     => null,
			'supports'			=> array( 'title', 'author', 'editor', 'thumbnail', 'revisions', 'page-attributes' ), // , 'excerpt', 'custom-fields'
			'taxonomies' 		=> array( 'category', 'tag', 'admin_tag' ),
			'show_in_rest'		=> true,    
		);

		register_post_type( 'snippet', $args );
	
	}
	add_action( 'init', 'register_post_type_snippet' );
}

/*** PEOPLE & ENSEMBLES ***/
// >>>> See WHX4 for person/group CPTs <<<< //

// TODO/WIP: merge Ensemble/Organization/Group into a single post type

if ( in_array('ensembles', $sdg_modules ) ) {
	// Ensemble
	function register_post_type_ensemble() {

		if ( sdg_custom_caps() ) { $caps = "group"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Ensembles', 'sdg' ),
			'singular_name' => __( 'Ensemble', 'sdg' ),
			'add_new' => __( 'New Ensemble', 'sdg' ),
			'add_new_item' => __( 'Add New Ensemble', 'sdg' ),
			'edit_item' => __( 'Edit Ensemble', 'sdg' ),
			'new_item' => __( 'New Ensemble', 'sdg' ),
			'view_item' => __( 'View Ensemble', 'sdg' ),
			'search_items' => __( 'Search Ensembles', 'sdg' ),
			'not_found' =>  __( 'No Ensembles Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Ensemble found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'ensembles' ),
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-groups',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,    
		);

		register_post_type( 'ensemble', $args );
	
	}
	add_action( 'init', 'register_post_type_ensemble' );
}

if ( in_array('organizations', $sdg_modules ) ) {
	// Organization
	function register_post_type_organization() {

		if ( sdg_custom_caps() ) { $caps = "group"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Organizations', 'sdg' ),
			'singular_name' => __( 'Organization', 'sdg' ),
			'add_new' => __( 'New Organization', 'sdg' ),
			'add_new_item' => __( 'Add New Organization', 'sdg' ),
			'edit_item' => __( 'Edit Organization', 'sdg' ),
			'new_item' => __( 'New Organization', 'sdg' ),
			'view_item' => __( 'View Organization', 'sdg' ),
			'search_items' => __( 'Search Organizations', 'sdg' ),
			'not_found' =>  __( 'No Organizations Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Organizations found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'organizations' ),
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-groups',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,    
		);

		register_post_type( 'organization', $args );
	
	}
	add_action( 'init', 'register_post_type_organization' );
}

/*** PROJECTS ***/

if ( in_array('projects', $sdg_modules ) ) {

	// Project
	function register_post_type_project() {

		if ( sdg_custom_caps() ) { $caps = "project"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Projects', 'sdg' ),
			'singular_name' => __( 'Project', 'sdg' ),
			'add_new' => __( 'New Project', 'sdg' ),
			'add_new_item' => __( 'Add New Project', 'sdg' ),
			'edit_item' => __( 'Edit Project', 'sdg' ),
			'new_item' => __( 'New Project', 'sdg' ),
			'view_item' => __( 'View Project', 'sdg' ),
			'search_items' => __( 'Search Projects', 'sdg' ),
			'not_found' =>  __( 'No Projects Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Projects found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'projects' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
			'taxonomies'		=> array( 'admin_tag', 'project_category' ),
			'show_in_rest'		=> false, // false = use classic, not block editor
		);

		register_post_type( 'project', $args );
	
	}
	add_action( 'init', 'register_post_type_project' );
	
	// Recording (Discography)
	function register_post_type_recording() {

		if ( sdg_custom_caps() ) { $caps = "project"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Recordings', 'sdg' ),
			'singular_name' => __( 'Recording', 'sdg' ),
			'add_new' => __( 'New Recording', 'sdg' ),
			'add_new_item' => __( 'Add New Recording', 'sdg' ),
			'edit_item' => __( 'Edit Recording', 'sdg' ),
			'new_item' => __( 'New Recording', 'sdg' ),
			'view_item' => __( 'View Recording', 'sdg' ),
			'search_items' => __( 'Search Recordings', 'sdg' ),
			'not_found' =>  __( 'No Recordings Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Recordings found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'recordings' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			'menu_icon'			=> 'dashicons-album',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
			'taxonomies'		=> array( 'recording_category', 'admin_tag' ),
			'show_in_rest'		=> false, // false = use classic, not block editor
		);

		register_post_type( 'recording', $args );
	
	}
	//add_action( 'init', 'register_post_type_recording' );

}

/*** PRESS ***/

if ( in_array('press', $sdg_modules ) ) {
	// Press
	function sdg_register_post_type_press() {

		if ( sdg_custom_caps() ) { $caps = "project"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Press', 'sdg' ),
			'singular_name' => __( 'Press', 'sdg' ),
			'add_new' => __( 'New Press Item', 'sdg' ),
			'add_new_item' => __( 'Add New Press Item', 'sdg' ),
			'edit_item' => __( 'Edit Press Item', 'sdg' ),
			'new_item' => __( 'New Press Item', 'sdg' ),
			'view_item' => __( 'View Press', 'sdg' ),
			'search_items' => __( 'Search Press', 'sdg' ),
			'not_found' =>  __( 'No Press Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Press found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'press' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
			'taxonomies'		=> array( 'admin_tag', 'press_category' ),
			'show_in_rest'		=> true,    
		);

		register_post_type( 'press', $args );
	
	}
	add_action( 'init', 'sdg_register_post_type_press' );
}

/*** NEWSLETTER ***/

if ( in_array('newsletters', $sdg_modules ) ) {
	// Newsletter
	function sdg_register_post_type_newsletter() {

		if ( sdg_custom_caps() ) { $caps = "newsletter"; } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Newsletters', 'sdg' ),
			'singular_name' => __( 'Newsletter', 'sdg' ),
			'add_new' => __( 'New Newsletter', 'sdg' ),
			'add_new_item' => __( 'Add New Newsletter', 'sdg' ),
			'edit_item' => __( 'Edit Newsletter', 'sdg' ),
			'new_item' => __( 'New Newsletter', 'sdg' ),
			'view_item' => __( 'View Newsletter', 'sdg' ),
			'search_items' => __( 'Search Newsletters', 'sdg' ),
			'not_found' =>  __( 'No Newsletter Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Newsletters found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'newsletters' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
			//'taxonomies'		=> array( 'admin_tag', 'press_category' ),
			'show_in_rest'		=> false, // no block editor option    
		);

		register_post_type( 'newsletter', $args );
	
	}
	add_action( 'init', 'sdg_register_post_type_newsletter' );
}

/*** MUSIC LIBRARY ***/
// >>> See MLIB

/*** LECTIONARY ***/

if ( in_array('lectionary', $sdg_modules ) ) {

	// Bible Book
	function register_post_type_bible_book() {

		if ( sdg_custom_caps() ) { $caps = array('bible_book', 'bible_books'); } else { $caps = "post"; }
		
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
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=lectionary',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'bible-books' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-book',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'bible_book', $args );
	
	}
	add_action( 'init', 'register_post_type_bible_book' );

	// Reading
	function register_post_type_reading() {

		if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
		
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
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=lectionary',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'readings' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-book',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'reading', $args );
	
	}
	add_action( 'init', 'register_post_type_reading' );

	// Lectionary Day
	function register_post_type_lectionary() {

		if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
		
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
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'lectionary' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			'menu_icon'			=> 'dashicons-calendar-alt',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'lectionary', $args );
	
	}
	add_action( 'init', 'register_post_type_lectionary' );

	// Liturgical Date
	function register_post_type_liturgical_date() {

		if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
		
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
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=lectionary',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'liturgical-dates' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-calendar-alt',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'liturgical_date', $args );
	
	}
	add_action( 'init', 'register_post_type_liturgical_date' );

	// Liturgical Date Calculation
	function register_post_type_liturgical_date_calc() {

		if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
		
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
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=lectionary',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'liturgical_date_calc' ),
			'capability_type'	=> $caps,
			'map_meta_cap' 		=> true,
			'has_archive'  		=> false,
			'hierarchical' 		=> false,
			//'menu_icon'			=> 'dashicons-calendar-alt',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'liturgical_date_calc', $args );
	
	}
	add_action( 'init', 'register_post_type_liturgical_date_calc' );

	// Collect
	function register_post_type_collect() {

		if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
		
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
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=lectionary',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'collects' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
			'taxonomies'		=> array( 'admin_tag' ), //'season', 
			'show_in_rest'		=> true,
		);

		register_post_type( 'collect', $args );
	
	}
	add_action( 'init', 'register_post_type_collect' );

	// Psalms of the Day
	function register_post_type_psalms_of_the_day() {

		if ( sdg_custom_caps() ) { $caps = array('lectionary_item', 'lectionary'); } else { $caps = "post"; }
		
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
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=lectionary',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'psalms_of_the_day' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap' 		=> true,
			'has_archive'  		=> false,
			'hierarchical' 		=> false,
			//'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //'editor', 
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'psalms_of_the_day', $args );
	
	}
	add_action( 'init', 'register_post_type_psalms_of_the_day' );

}

/*** SERMONS ***/

if ( in_array('sermons', $sdg_modules ) ) {

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
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'sermons' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap' 		=> true,
			'has_archive'  		=> 'sermon-archive',
			//'has_archive'  		=> true,
			'hierarchical' 		=> false,
			'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
			'taxonomies'		=> array( 'sermon_topic', 'admin_tag' ),
			'show_in_rest'		=> false, // i.e. false = use classic, not block editor
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
			'show_ui'  			=> true,
			'show_in_menu' 		=> 'edit.php?post_type=sermon',
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'sermon-series' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-book',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'sermon_series', $args );
	
	}
	add_action( 'init', 'register_post_type_sermon_series' );

}

/*** EVENTS (extended EM) ***/
// >>>> See WHX4 for event_series CPT <<<< //

/*** ORGANS ***/
// >>> See MLIB
// TODO: generalize as "instruments"?

/*** VENUES ***/
/*** ADDRESSES ***/
// >>>> See WHX4 for venue, address, building CPTs <<<< //


/*** SOURCES ***/
// TODO: phase this out, replace w/ RESOURCES(?)
if ( in_array('sources', $sdg_modules ) ) {

	// Source
	function register_post_type_source() {

		if ( sdg_custom_caps() ) { $caps = array('source', 'sources'); } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Sources', 'sdg' ),
			'singular_name' => __( 'Source', 'sdg' ),
			'add_new' => __( 'New Source', 'sdg' ),
			'add_new_item' => __( 'Add New Source', 'sdg' ),
			'edit_item' => __( 'Edit Source', 'sdg' ),
			'new_item' => __( 'New Source', 'sdg' ),
			'view_item' => __( 'View Source', 'sdg' ),
			'search_items' => __( 'Search Sources', 'sdg' ),
			'not_found' =>  __( 'No Sources Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Sources found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'sources' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> false, // i.e. false = use classic, not block editor
		);

		register_post_type( 'source', $args );
	
	}
	add_action( 'init', 'register_post_type_source' );

}

/*** LINKS ***/
// TODO: phase this out, replace w/ RESOURCES(?)
if ( in_array('links', $sdg_modules ) ) {

	// Links
	function register_post_type_link() {

		if ( sdg_custom_caps() ) { $caps = array('resource', 'resources'); } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Links', 'sdg' ),
			'singular_name' => __( 'Link', 'sdg' ),
			'add_new' => __( 'New Link', 'sdg' ),
			'add_new_item' => __( 'Add New Link', 'sdg' ),
			'edit_item' => __( 'Edit Link', 'sdg' ),
			'new_item' => __( 'New Link', 'sdg' ),
			'view_item' => __( 'View Link', 'sdg' ),
			'search_items' => __( 'Search Links', 'sdg' ),
			'not_found' =>  __( 'No Links Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Links found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'links' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ), //, 'excerpt' , 'editor'
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> false, // i.e. false = use classic, not block editor
		);

		register_post_type( 'link', $args );
	
	}
	add_action( 'init', 'register_post_type_link' );

}

/*** RESOURCES ***/

if ( in_array('resources', $sdg_modules ) ) {

	// Resources
	function register_post_type_resource() {

		if ( sdg_custom_caps() ) { $caps = array('resource', 'resources'); } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Resources', 'sdg' ),
			'singular_name' => __( 'Resource', 'sdg' ),
			'add_new' => __( 'New Resource', 'sdg' ),
			'add_new_item' => __( 'Add New Resource', 'sdg' ),
			'edit_item' => __( 'Edit Resource', 'sdg' ),
			'new_item' => __( 'New Resource', 'sdg' ),
			'view_item' => __( 'View Resource', 'sdg' ),
			'search_items' => __( 'Search Resources', 'sdg' ),
			'not_found' =>  __( 'No Resources Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Resources found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'resources' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ), //
			'taxonomies'		=> array( 'admin_tag' ),
			'show_in_rest'		=> false, // i.e. false = use classic, not block editor
		);

		register_post_type( 'resource', $args );
	
	}
	add_action( 'init', 'register_post_type_resource' );

}


/*** +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ ***/

// TODO: Move Logbook, Documents, Inventory, Ledger to a separate plugin or plugins?

/*** LOGBOOK ***/
// WIP -- consider log entries model vs calendar events -- see ATCHQ ACF field group "Logbook (Library)" >> log_entries repeater.
// Is there any need for a special post type -- or instead a Logbook/Log Entries field group applied to multiple post types? 
if ( in_array('logbook', $sdg_modules ) ) {
	// Log Entry
	function sdg_register_post_type_log_entry() {

		if ( sdg_custom_caps() ) { $caps = array('admin_note', 'admin_notes'); } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Logbook', 'sdg' ),
			'singular_name' => __( 'Log Entry', 'sdg' ),
			'add_new' => __( 'New Log Entry', 'sdg' ),
			'add_new_item' => __( 'Add New Log Entry', 'sdg' ),
			'edit_item' => __( 'Edit Log Entry', 'sdg' ),
			'new_item' => __( 'New Log Entry', 'sdg' ),
			'view_item' => __( 'View Log Entry', 'sdg' ),
			'search_items' => __( 'Search Log Entries', 'sdg' ),
			'not_found' =>  __( 'No Log Entries Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Log Entries found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite' 			=> array( 'slug' => 'logbook' ), // permalink structure slug
			'capability_type' 	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			//'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
			//'taxonomies' 		=> array( 'admin_tag', 'press_category' ),
			'show_in_rest'		=> false, // false = use classic, not block editor
		);

		register_post_type( 'log_entry', $args );
	
	}
	add_action( 'init', 'sdg_register_post_type_log_entry' );
}

/*** INVENTORY ***/
// WIP
// item? thing? possession? object?
if ( in_array('inventory', $sdg_modules ) ) {
	// Thing
	function sdg_register_post_type_thing() {

		if ( sdg_custom_caps() ) { $caps = array('thing', 'things'); } else { $caps = "post"; }
		
		$labels = array(
			'name' => __( 'Things', 'sdg' ),
			'singular_name' => __( 'Thing', 'sdg' ),
			'add_new' => __( 'New Thing', 'sdg' ),
			'add_new_item' => __( 'Add New Thing', 'sdg' ),
			'edit_item' => __( 'Edit Thing', 'sdg' ),
			'new_item' => __( 'New Thing', 'sdg' ),
			'view_item' => __( 'View Thing', 'sdg' ),
			'search_items' => __( 'Search Things', 'sdg' ),
			'not_found' =>  __( 'No Things Found', 'sdg' ),
			'not_found_in_trash' => __( 'No Things found in Trash', 'sdg' ),
		);
	
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui' 			=> true,
			'show_in_menu'     	=> true,
			'query_var'        	=> true,
			'rewrite'			=> array( 'slug' => 'things' ), // permalink structure slug
			'capability_type'	=> $caps,
			'map_meta_cap'		=> true,
			'has_archive' 		=> true,
			'hierarchical'		=> false,
			'menu_icon'			=> 'dashicons-welcome-write-blog',
			'menu_position'		=> null,
			'supports' 			=> array( 'title', 'author', 'thumbnail', 'editor', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
			//'taxonomies'		=> array( 'admin_tag', 'press_category' ),
			'show_in_rest'		=> true,
		);

		register_post_type( 'thing', $args );
	
	}
	add_action( 'init', 'sdg_register_post_type_thing' );
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

if ( in_array('sermons', $sdg_modules ) ) {
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
    $ts_info = "";
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


?>