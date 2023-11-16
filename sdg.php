<?php
/**
 * @package SDG
 */

/*
Plugin Name: SDG
Plugin URI: 
Description: Custom post types, taxonomies and functions for music and more
Version: 0.1
Author: atc
Author URI: 
License: 
Text Domain: sdg
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

$plugin_path = plugin_dir_path( __FILE__ );

// TODO: Deal w/ plugin dependencies -- Display Content; ACF; EM; &c.?
// TODO: Check for ACF field groups; import them from plugin copies if not found?
// TODO: formalize dependencies between modules -- e.g. events, music both require people

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

if ( ! is_admin() ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' ); // so that we can run functions like post_exists on the front end
}

// Register our sdg_settings_init to the admin_init action hook.
add_action( 'admin_init', 'sdg_settings_init' );

/**
 * Custom option and settings
 */
function sdg_settings_init() {

	// Register a new setting for "sdg" page.
	register_setting( 'sdg', 'sdg_settings' );

	// Register a new section in the "sdg" page.
	add_settings_section(
		'sdg_settings',
		__( 'SDG Plugin Settings', 'sdg' ), 'sdg_settings_section_callback',
		'sdg'
	);
	/*
	// Register a new field in the "sdg_settings" section, inside the "sdg" page.
	add_settings_field(
		'sdg_test_text_field',
		__( 'Test Text Field', 'sdg' ),
		'sdg_text_field_cb',
		'sdg',
		'sdg_settings',
		['id' => 'sdg_test_text_field', 'name' => 'sdg_test_text_field', 'placeholder' => __('Test Text Field', 'sdg'), 'default_value' => 'test_value', 'class' => 'form-field form-required', 'style' => 'width:15rem']
	);
	
	// Register a new field in the "sdg_settings" section, inside the "sdg" page.
	add_settings_field(
		'sdg_test_select_field', // As of WP 4.6 this value is used only internally.
		__( 'Test Select Field', 'sdg' ),
		'sdg_select_field_cb',
		'sdg',
		'sdg_settings',
		array(
			'label_for'         => 'sdg_test_select_field',
			'class'             => 'sdg_row',
			'sdg_custom_data' 	=> 'custom',
		)
	);
	*/
	
	// Checkbox to designate dev site
	add_settings_field(
        'is_dev_site',
        esc_attr__('Dev Site', 'sdg'),
        'sdg_devsite_field_cb',
        'sdg',
        'sdg_settings',
        array( 
            'type'         => 'checkbox',
            //'option_group' => 'sdg_settings', 
            'name'         => 'is_dev_site',
            'label_for'    => 'is_dev_site',
            'value'        => (empty(get_option('sdg_settings')['is_dev_site'])) ? 0 : get_option('sdg_settings')['is_dev_site'],
            'description'  => __( 'This is a dev site.', 'sdg' ),
            'checked'      => (!isset(get_option('sdg_settings')['is_dev_site'])) ? 0 : get_option('sdg_settings')['is_dev_site'],
            // Used 0 in this case but will still return Boolean not[see notes below] 
            ///'tip'          => esc_attr__( 'Use if plugin fields drastically changed when installing this plugin.', 'wpdevref' ) 
            )
    );
    
    // Checkbox to determine whether or not to use custom capabilities
	add_settings_field(
        'use_custom_caps',
        esc_attr__('Capabilities (Permissions)', 'sdg'),
        'sdg_caps_field_cb',
        'sdg',
        'sdg_settings',
        array( 
            'type'         => 'checkbox',
            'name'         => 'use_custom_caps',
            'label_for'    => 'use_custom_caps',
            'value'        => (empty(get_option('sdg_settings')['use_custom_caps'])) ? 0 : get_option('sdg_settings')['use_custom_caps'],
            'description'  => __( 'Use custom capabilities.', 'sdg' ),
            'checked'      => (!isset(get_option('sdg_settings')['use_custom_caps'])) ? 0 : get_option('sdg_settings')['use_custom_caps'],
            )
    ); 
	
	// Register a new section in the "sdg" page.
	add_settings_section(
		'sdg_modules',
		__( 'SDG Modules', 'sdg' ), 'sdg_modules_section_callback',
		'sdg'
	);
	
	// Register a new field in the "sdg_modules" section, inside the "sdg" page.
	add_settings_field(
		'sdg_modules', // As of WP 4.6 this value is used only internally.
		__( 'Active Modules', 'sdg' ),
		'sdg_modules_field_cb',
		'sdg',
		'sdg_modules',
		array(
			'label_for'         => 'sdg_modules',
			//'value'        		=> (empty(get_option('sdg_settings')['sdg_modules'])) ? 0 : get_option('sdg_settings')['sdg_modules'],
			'class'             => 'sdg_row',
			'sdg_custom_data' 	=> 'custom',
		)
	);
	
	// TODO: new section/field(s) geared toward individual artist site -- see "artiste" plugin posttypes draft
	
	
}

/**
 * Settings section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function sdg_settings_section_callback( $args ) {

	$options = get_option( 'sdg_settings' );
	//echo "options: <pre>".print_r($options,true)."</pre>"; // tft
	//echo '<!--p id="'.esc_attr( $args['id'] ).'">'.esc_html_e( 'Test Settings Section Header', 'sdg' ).'></p-->';

}

function sdg_modules_section_callback( $args ) {
	echo '<p id="'.esc_attr( $args['id'] ).'">'.esc_html_e( 'Select modules to activate.', 'sdg' ).'</p>';
}

// Render a text field
function sdg_text_field_cb( $args ) {

	$options = get_option( 'sdg_settings' );
	
	//echo "args: <pre>".print_r($args,true)."</pre>"; // tft
	//echo "options: <pre>".print_r($options,true)."</pre>"; // tft
	
	$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : esc_attr( $args['default_value']);
	$class = isset($args['class']) ? $args['class'] : '';
	$style = isset($args['style']) ? $args['style']: '';
	
	echo '<input type="text" 
		id="'.esc_attr( $args['id'] ).'" 
		name="sdg_settings['.esc_attr( $args['name'] ).']" 
		value="'.$value.'" 
		class="'.$class.'" 
		style="'.$style.'" 
		placeholder="'.esc_attr( $args['placeholder'] ).'"/>';
}

/**
 * Pill field callback function -- EXAMPLE/MODEl FIELD
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function sdg_select_field_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'sdg_settings' );
	?>
	<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			data-custom="<?php echo esc_attr( $args['sdg_custom_data'] ); ?>"
			name="sdg_settings[<?php echo esc_attr( $args['label_for'] ); ?>]">
		<option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'red pill', 'sdg' ); ?>
		</option>
 		<option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'blue pill', 'sdg' ); ?>
		</option>
	</select>
	<p class="description">
		<?php esc_html_e( 'Blue...', 'sdg' ); ?>
	</p>
	<p class="description">
		<?php esc_html_e( 'Red...', 'sdg' ); ?>
	</p>
	<?php
}

/** 
 * switch for 'is_dev_site' field
 * @since 2.0.1
 * @input type checkbox
 */
// TODO: make this a radio button instead?
function sdg_devsite_field_cb( $args ) { 

	//echo "args: <pre>".print_r($args,true)."</pre>"; // tft
	
    $checked = '';
    $options = get_option( 'sdg_settings' );
	//echo "value: <pre>[".print_r($value,true)."]</pre>"; // tft
    
    $value   = ( !isset( $options[$args['name']] ) ) 
                ? null : $options[$args['name']];
    if ($value) { $checked = ' checked="checked" '; }
        // Could use ob_start.
        $html  = '';
        $html .= '<input id="' . esc_attr( $args['name'] ) . '" 
        name="sdg_settings' . esc_attr('['.$args['name'].']') .'" 
        type="checkbox" ' . $checked . '/>';
        $html .= '<span class="">' . esc_html( $args['description'] ) .'</span>';
        //$html .= '<b class="wntip" data-title="'. esc_attr( $args['tip'] ) .'"> ? </b>';

        echo $html;
}

// TODO: make this a radio button instead?
function sdg_caps_field_cb( $args ) { 
	
    $checked = '';
    $options = get_option( 'sdg_settings' );
    
    $value   = ( !isset( $options[$args['name']] ) ) 
                ? null : $options[$args['name']];
    if ($value) { $checked = ' checked="checked" '; }
        // Could use ob_start.
        $html  = '';
        $html .= '<input id="' . esc_attr( $args['name'] ) . '" 
        name="sdg_settings' . esc_attr('['.$args['name'].']') .'" 
        type="checkbox" ' . $checked . '/>';
        $html .= '<span class="">' . esc_html( $args['description'] ) .'</span>';

        echo $html;
}

function sdg_modules_field_cb( $args ) {
	
	$options = get_option( 'sdg_settings' );
	$modules = array( 
		'events' => __( 'Events' ), 
		'people' => __( 'People' ), 
		'groups' => __( 'Groups' ), 
		//'ensembles' => __( 'Ensembles' ), 
		'music' => __( 'Music Library' ), 
		'webcasts' => __( 'Webcasts' ), 
		'sermons' => __( 'Sermons' ), 
		'lectionary' => __( 'Lectionary' ), 
		//
		'organizations' => __( 'Organizations' ), 
		'projects' => __( 'Projects' ), 
		'press' => __( 'Press' ), 
		//'recordings' => __( 'Recordings' ),
		'links' => __( 'Links' ),
		'snippets' => __( 'Snippets' ), 
		'newsletters' => __( 'Newsletters' ),
		//'sources' => __( 'Sources' ),
		//
		'organs' => __( 'Organs' ), 
		//
		'venues' => __( 'Venues' ), 
		//
		'slider' => __( 'Slider' ), 
		'ninjaforms' => __( 'Ninja Forms' ), 
		//
		'admin_notes' => __( 'Admin Notes' ), 
		'data_tables' => __( 'Data Tables' ),
	);
	
	$value   = ( !isset( $options[$args['label_for']] ) ) ? array() : $options[$args['label_for']];
                
	//echo "args: <pre>".print_r($args,true)."</pre>"; // tft
	//echo "value: <pre>[".print_r($value,true)."]</pre>"; // tft
	
	foreach ( $modules as $name => $option ) {
		?>
		<div class="sdg-options">
			<input
			type="checkbox"
			id="sdg_modules_<?php echo esc_attr( $name ); ?>"
			name="sdg_settings[sdg_modules][]"
			class="<?php echo esc_attr( $name ); ?>"
			value="<?php echo esc_attr( $name ); ?>"
			<?php if ( in_array($name, $value) ) { echo ' checked="checked" '; } ?>
			<?php //echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], $name, false ) ) : ( '' ); ?>
			/>
			<label for="sdg_modules_<?php echo esc_attr( $name ); ?>" class="sdg-option-label">
			<?php echo esc_html( $modules[ $name ] ); ?>
			</label>
		</div>
		<?php
	}
}

// Render a checkbox
// TODO

// Register our sdg_settings_page to the admin_menu action hook.
add_action( 'admin_menu', 'sdg_settings_page' );

// Add the top level menu page.
function sdg_settings_page() {
	add_menu_page(
		'SDG',
		'SDG Options',
		'manage_options',
		'sdg',
		'sdg_settings_page_html'
	);
}

/**
 * Top level menu callback function
 */
function sdg_settings_page_html() {

	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// add error/update messages

	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'sdg_messages', 'sdg_message', __( 'Settings Saved', 'sdg' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'sdg_messages' );
	
	// Include the form to display the setting fields
	//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/sdg-admin-settings.php';
	
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "sdg"
			settings_fields( 'sdg' );
			
			// output setting sections and their fields
			// (sections are registered for "sdg", each field is registered to a specific section)
			do_settings_sections( 'sdg' );
			
			// output save settings button
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}

// Get plugin options -- WIP
$options = get_option( 'sdg_settings' );
if ( isset($options['sdg_modules']) ) { $modules = $options['sdg_modules']; } else { $modules = array(); }

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

//if ( !function_exists( 'is_dev_site' ) ) {
    function is_dev_site() {
        
        $options = get_option( 'sdg_settings' );
        
        if ( isset($options['is_dev_site']) ) { 
            if ( !empty($options['is_dev_site']) ) {
                return true;
            } else {
                return false;
            }            
        }
        
        $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
        if ( $subdomain == "dev" ) { return true; } // RS dev site
        
        return false;
    }
//}

// Include sub-files
// TODO: make them required? Otherwise dependencies may be an issue.
// TODO: maybe: convert to classes/methods approach??
// TODO: maybe: split this into several separate plugins -- SDG WooCommerce; SDG Developer Functions; &c. -- ?!? WIP ?!?

$includes = array( 'posttypes', 'taxonomies' );
$admin_functions_filepath = $plugin_path . 'inc/'.'admin_functions.php';
$common_functions_filepath = $plugin_path . 'inc/'.'common_functions.php';

if ( file_exists($admin_functions_filepath) ) { include_once( $admin_functions_filepath ); } else { echo "no $admin_functions_filepath found"; }
if ( file_exists($common_functions_filepath) ) { include_once( $common_functions_filepath ); } else { echo "no $common_functions_filepath found"; }

foreach ( $includes as $inc ) {
    $filepath = $plugin_path . 'inc/'.$inc.'.php'; 
    if ( file_exists($filepath) ) { include_once( $filepath ); } else { echo "no $filepath found"; }
}

foreach ( $modules as $module ) {
    $filepath = $plugin_path . 'modules/'.$module.'.php';
    $arr_exclusions = array ( 'admin_notes', 'data_tables', 'links', 'organizations', 'ensembles', 'organs', 'press', 'projects', 'snippets', 'sources', 'venues' ); // , 'groups', 'newsletters'
    if ( !in_array( $module, $arr_exclusions) ) { // skip modules w/ no files
    	if ( file_exists($filepath) ) { include_once( $filepath ); } else { echo "no $filepath found"; }
    }
}


// Loop through active modules and add options page per CPT for adding featured image, page-top content, &c.
if ( function_exists('acf_add_options_page') ) {
    
    foreach ( $modules as $module ) {
    
    	$cpt_name = $module;
    	// Make it singular -- remove trailing "s"
    	if ( substr($cpt_name, -1) == "s" ) { $cpt_name = substr($cpt_name, 0, -1); }
		
		acf_add_options_sub_page(array(
			'page_title'     => ucfirst($cpt_name).' CPT Options',
			'menu_title'    => 'Archive Options', //ucfirst($cpt_name).
			'menu_slug' 	=> $module.'-cpt-options',
			'parent_slug'    => 'edit.php?post_type='.$cpt_name,
		));
		
	}

}

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

//wp_enqueue_script('sdg_js_script', plugins_url('sdg.js', __FILE__), array('json_settings'), true );

/**
 * Enqueue a script with jQuery as a dependency.
 */
add_action( 'wp_enqueue_scripts', 'sdg_scripts_method' );
function sdg_scripts_method() {
    
    global $current_user;
    $current_user = wp_get_current_user();
    
    $fpath = WP_PLUGIN_DIR . '/sdg/sdg.css';
    if (file_exists($fpath)) { $ver = filemtime($fpath); } else { $ver = "230628"; }  
    wp_enqueue_style( 'sdg-style', plugins_url( 'sdg.css', __FILE__ ), $ver );
    //wp_enqueue_style( 'sdg-style', plugin_dir_url( __FILE__ ) . 'sdg.css', $ver );
    
    $fpath = WP_PLUGIN_DIR . '/sdg/js/sdg.js';
    if (file_exists($fpath)) { $ver = filemtime($fpath); } else { $ver = "201209"; }    
    wp_enqueue_script( 'sdg', plugins_url( 'js/sdg.js', __FILE__ ), array( 'jquery-ui-dialog' ), $ver  );
    //wp_enqueue_script( 'sdg-js', plugins_url( 'sdg.js', __FILE__ ), array( 'jquery', 'jquery-ui-dialog' ), $ver  );
    //wp_enqueue_script( 'sdg-js', plugins_url( 'sdg.js', __FILE__ ), array( 'jquery', 'jquery-ui-dialog' ), '2.0', true );
    wp_localize_script( 'sdg', 'theUser', array (
        //'current_user' => $current_user,
        //'current_user' => wp_get_current_user()
        'username' => $current_user->user_login,
    ) );
    
    // Cookie utility functions
    $fpath = WP_PLUGIN_DIR . '/sdg/js/cookies.js';
    if (file_exists($fpath)) { $ver = filemtime($fpath); } else { $ver = "201209"; }    
    wp_enqueue_script( 'cookies', plugins_url( 'js/cookies.js', __FILE__ ), array(), $ver  );
    
    // Enqueue styles for jQuery UI
    //$wp_scripts = wp_scripts();
    //wp_enqueue_style('jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-autocomplete']->ver . '/themes/smoothness/jquery-ui.css', false, null, false );
    
    // Font Awesome 5 (Free)
    
    
}

// Add custom query vars
add_filter( 'query_vars', 'sdg_query_vars' );
function sdg_query_vars( $qvars ) {
	$qvars[] = 'devmode';
	$qvars[] = 'y'; // = year -- for date_calculations (and - ?)
    return $qvars;
}

// Add post_type query var to edit_post_link so as to be able to selectively load plugins via plugins-corral MU plugin
add_filter( 'get_edit_post_link', 'sdg_add_post_type_query_var', 10, 3 );
function sdg_add_post_type_query_var( $url, $post_id, $context ) {

    $post_type = get_post_type( $post_id );
    
    // TODO: consider whether to add query_arg only for certain CPTS?
    if ( $post_type && !empty($post_type) ) { $url = add_query_arg( 'post_type', $post_type, $url ); }
    
    return $url;
}

add_action('wp_head', 'sdg_meta_tags');
function sdg_meta_tags() { 
    
    // Set defaults
    $og_url = get_bloginfo( 'url' ); //get_site_url();
    $og_type = "website";
    $og_title = get_bloginfo( 'name' );
    $og_image = get_option( 'og_image', '' );    
    $og_description = get_bloginfo( 'description' );
    
    if ( is_page() || is_single() || is_singular() ) {
        
        $og_type = "article";
        $post_id = get_queried_object_id();
        $og_url = get_the_permalink( $post_id );
        $og_title = get_the_title( $post_id );
        
        // Get the featured image URL, if there is one
        if ( get_the_post_thumbnail_url( $post_id ) ) { $og_image = get_the_post_thumbnail_url( $post_id ); }
        
        // Get and clean up the excerpt for use in the description meta tag
        $excerpt = get_the_excerpt( $post_id );
        $excerpt = str_replace('&nbsp;Read more...','...',$excerpt); // Remove the "read more" tag from auto-excerpts
        $og_description = wp_strip_all_tags( $excerpt, true );
        
    }

    echo '<meta property="og:url" content="'.$og_url.'" />';
    echo '<meta property="og:type" content="'.$og_type.'" />';
    echo '<meta property="og:title" content="'.$og_title.'" />';
    echo '<meta property="og:image" content="'.$og_image.'" />';
    echo '<meta property="og:description" content="'.$og_description.'" />';
    //fb:app_id
    
}

// WIP -- selectively dequeue scripts and styles for faster CMS load times
//add_action( 'admin_init', 'selectively_dequeue_admin_scripts_and_styles' );
function sdg_selectively_dequeue_admin_scripts_and_styles() {
    
    // wp_deregister_style( string $handle )
    // https://developer.wordpress.org/reference/functions/wp_deregister_style/
    
    // wp_dequeue_style( string $handle )
    // https://developer.wordpress.org/reference/functions/wp_dequeue_style/
    
    // https://developer.wordpress.org/reference/functions/wp_deregister_script/
    // wp_deregister_script( string $handle )
    
    // https://developer.wordpress.org/reference/functions/wp_dequeue_script/
    // wp_dequeue_script( string $handle ) -- Remove a previously enqueued script.
    
    // NB: De-register will remove script/style completely, where as de-queue will only stop it loading.
    
    wp_deregister_style(
        'wp-admin',
        'ie',
        'colors',
        'colors-fresh',
        'colors-classic',
        'media',
        'install',
        'thickbox'
    );
}

// Enable shortcodes in sidebar widgets
if ( function_exists('is_dev_site') && !is_dev_site() ) {
	add_filter( 'widget_text', 'shortcode_unautop' );
}
add_filter( 'widget_text', 'do_shortcode' );

// ACF
add_filter('acf/settings/row_index_offset', '__return_zero');
// TODO: update other calls to ACF functions in case this screws them up?

// Certain operations should only be run in devmode
function devmode_active() {
	
	$devmode = false; // init
	$queenbee = get_option( 'devadmin_username', 'queenbee' );
	
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
		$username = $current_user->user_login;
    } else {
    	$username = null;
    }
    
	$devmode = get_query_var('devmode');
	if ($devmode !== "" && $devmode !== "false") {      
		return true;        
	} else if ( $username == 'queenbee' && is_dev_site() ) { 
        return true;
	}
	
	return false;
}

// Function to display troubleshooting info
add_shortcode( 'troubleshooting', 'sdg_show_troubleshooting_info' );
function sdg_show_troubleshooting_info ( ) {
	
	global $post;
	global $wp_query;
	//$post = get_post();
    
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
		$username = $current_user->user_login;		
		if ( $username != 'queenbee' ) { 
			return false;
		}        
	} else {
        return false;
    }
	
	$devmode = devmode_active();
	
	$info = '<div class="troubleshooting">';
	
	if ( $devmode === true ) { $info .= "devmode: active<br />"; } else { $info .= "devmode: inactive [$devmode]<br />"; }
	
	if ($post) { $info .= "post_type: ".$post->post_type."<br />"; }
	
	if ( is_singular() ) {
		$info .= "is_singular()<br />";
		$info .= "post_id: ".$post->ID."<br />";
	}
	if ( is_archive() ) { $info .= "is_archive()<br />"; }
	if ( is_post_type_archive() ) { $info .= "is_post_type_archive()<br />"; }
	if ( is_category() ) { $info .= "is_category()<br />"; }
	if ( is_tax() ) { $info .= "is_tax()<br />"; }
	if ( is_front_page() ) { $info .= "is_front_page()<br />"; }
	if ( is_home() ) { $info .= "is_home()<br />"; }
    
	$info .= '</div>';
	
	return $info;
	
}


/*** Add Custom Post Status: Archived ***/

add_action( 'init', 'sdg_custom_post_status_creation' );
function sdg_custom_post_status_creation(){
	register_post_status( 'archived', array(
		'label'                     => _x( 'Archived', 'post' ), 
		'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>'),
		'public'                    => false,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'post_type'                 => array( 'post', 'nf_sub' ),
	));
}

add_filter( 'display_post_states', 'sdg_display_status_label' );
function sdg_display_status_label( $statuses ) {
	global $post; // we need it to check current post status
	if( get_query_var( 'post_status' ) != 'archived' ){ // not for pages with all posts of this status
		if ( $post && $post->post_status == 'archived' ){ // если статус поста - Архив
			return array('Archived'); // returning our status label
		}
	}
	return $statuses; // returning the array with default statuses
}

// TODO: move script to JS file and enqueue it properly(?)
add_action('admin_footer-edit.php','sdg_status_into_inline_edit');
function sdg_status_into_inline_edit() { // ultra-simple example
	echo "<script>
	jQuery(document).ready( function() {
		jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"archived\">Archived</option>' );
	});
	</script>";
}

add_action( 'post_submitbox_misc_actions', 'sdg_post_submitbox_misc_actions' );
function sdg_post_submitbox_misc_actions(){

    global $post;

    //only when editing a post
    if ( $post->post_type == 'post' || $post->post_type == 'event' ){

        // custom post status: approved
        $complete = '';
        $label = '';   

        if( $post->post_status == 'archived' ){
            $complete = 'selected=\"selected\"';
            $label = '<span id=\"post-status-display\"> Archived</span>';
        }

        echo '<script>'.
                 'jQuery(document).ready(function($){'.
                     '$("select#post_status").append('.
                         '"<option value=\"archived\" '.$complete.'>'.
                             'Archived'.
                         '</option>"'.
                     ');'.
                     '$(".misc-pub-section label").append("'.$label.'");'.
                 '});'.
             '</script>';
    }
}


/*** AJAX ***/

// AUTOCOMPLETE

add_action('wp_ajax_nopriv_autocompleteSearch', 'sdg_autocomplete_search');
add_action('wp_ajax_autocompleteSearch', 'sdg_autocomplete_search');
function sdg_autocomplete_search() {
	
    check_ajax_referer('autocompleteSearchNonce', 'security');
    
	// Abort if the search term is empty
    if (!isset($_REQUEST['term'])) {
		echo json_encode([]);
	}
    
    // Prep the query based on the search term
    if ( isset($_REQUEST['term']) && $_REQUEST['term'] != "" ) { $search_term = $_REQUEST['term']; } else { $search_term = null; }
    if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] != "" ) { $post_type = $_REQUEST['post_type']; } else { $post_type = "post"; }
	//$search_term = $_REQUEST['term'];
    //$post_type = $_REQUEST['post_type'];
    $post_status = "publish";
    
    // Set up basic query args
    $args = array(
		'post_type'       => $post_type,
		'post_status'     => $post_status,
		'posts_per_page'  => -1, //$posts_per_page,
        'fields'          => 'ids', // return ids only in hopes this will speed things up...
        's' => $search_term,
	);
    
    // taxonomy?
    if ( isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy'] != "" ) { $taxonomy = $_REQUEST['taxonomy']; } else { $taxonomy = null; }
    if ( isset($_REQUEST['tax_terms']) && $_REQUEST['tax_terms'] != "" ) { $tax_terms = $_REQUEST['tax_terms']; } else { $tax_terms = null; }
    //$tax_terms = $_REQUEST['tax_terms'];
    $tax_field = "slug"; // Possible values are ‘term_id’, ‘name’, ‘slug’ or ‘term_taxonomy_id’. Default value is ‘term_id’.
    $tax_operator = 'IN';
    
    if ( $taxonomy ) {    
		$args['tax_query'] = array(
			array(
				'taxonomy'  => $taxonomy,
				'field'     => $tax_field,
				'terms'     => $tax_terms,
				'operator'  => $tax_operator,
			)
		);
    }
    
	$suggestions = []; // init results array
    
	// Run the search
    $posts = get_posts( $args );
    
    foreach ( $posts as $post_id ) {
        $suggestions[] = [
            'id' => $post_id,
            'label' => get_the_title($post_id),
        ];
    }
    
	echo json_encode($suggestions);
    
	wp_die();
}

/*** MISC ***/

/**
 * Displays message/announcement bar/banner
 */
function sdg_msg_bar( $args = array() ) {

	$info = "";
	$ts_info = "";
	
	//$ts_info .= "<!-- <pre>sdg_msg_bar args: ".print_r($args, true)."</pre> -->";	
    
	// Defaults
	$defaults = array(
		'post_type'	=> "post",
		'num_posts' => 1,
		'prioritize_livestream' => true,
		'post_id' => null,
	);

	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	// If no specific post id was passed to the fcn, then look for a post to feature
	
	// First, check to see if there is a webcast event currently livestreaming
	if ( !$post_id && $prioritize_livestream ) {
		$post_id = get_live_webcast_id();
	}
    
    // If no ID was submitted AND there's no livestreaming event, look for a featured post
    // TODO: consider building a separate get_featured_post_id fcn
    if ( !$post_id ) {
    
    	$wp_args = array(
			'post_type'   => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $num_posts,
			'orderby'   => 'date',
			'order'     => 'DESC',
			'fields'	=> 'ids',
		);
		
		// TODO: change this to accept taxonomy & slug for more general use
		if ( $post_type == "post" ) {
			$wp_args['category_name'] = 'featured-posts';
		}
    	
    	// Tax query? TBD maybe later
    	/*    
		$tax_query = array(
			array(
				'taxonomy' => 'notes_category',
				'field'    => 'slug',
				'terms'    => 'banners', //$category
			),
		);
		//$info .= "tax_query: <pre>".print_r($tax_query, true)."</pre>";
		$args['tax_query'] = $tax_query;
		*/
		
		$query = new WP_Query( $wp_args );
		$posts = $query->posts;    
		if ( count($posts) > 0 ) {
			$post_id = $posts[0];        
		}
		//$info .= "<!-- args: ".print_r($args, true)." -->"; // tft <pre></pre>
		//$info .= "<!-- Last SQL-Query (query): {$query->request} -->";

	}
	
	if ( $post_id ) {
	
		$post_type = get_post_type( $post_id );
		$ts_info .= "<!-- post_id: $post_id; post_type: $post_type -->";
		
		$colorscheme = "";
																  
		$info .= '<div id="msg_bar" class="msg_bar '.$post_type.$colorscheme.'">';
		$info .= '<span class="msg_bar_close" tabindex="0" role="button" aria-label="Close Announcement"></span>';
	
		/*if ( has_post_thumbnail($post_id) ) {
			$img = get_the_post_thumbnail( $post_id, 'full' );
			if ( !empty($img) ) {
				$ts_info .= "<!-- img -->";
				$info .= $img;
			}
		} else {
			$ts_info .= "<!-- content -->";
			$post = get_post( $post_id );
			$the_content = apply_filters('the_content', $post->post_content);
			$info .= $the_content;
			//$info .= get_the_content();
			//$info .= get_the_content($post_id);
		}*/
		if ( $post_type == "event" ) {
			$msg = "Currently livestreaming: ";
			$event_title = get_the_title( $post_id );
            $msg .= make_link( get_permalink($post_id), $event_title );
		} else {
			$post = get_post( $post_id );
			$post_title = get_the_title( $post_id );
			//$excerpt = $post->post_excerpt;
			if ( has_excerpt( $post_id ) ) { 
				$msg = $post->post_excerpt; // custom excerpt
				$msg .= '&nbsp;'.make_link( get_permalink($post_id), '<span class="readmore">Read More...</span>', $post_title );
			} else {
				$msg = get_the_excerpt( $post_id );
			}
			//$msg = $excerpt;
			//$msg = get_the_excerpt( $post_id );
			//$msg .= '&nbsp;'.make_link( get_permalink($post_id), '<span class="readmore">Read More...</span>' );
		}
		
		$info .= '<div id="post-'.$post_id.'" class="'.$post_type.' featured-post">';
		$info .= "<p>";
		$info .= $msg;
		$info .= $ts_info;
		$info .= "</p>";
		$info .= '</div>';
		$info .= '</div><!-- /banner -->';
		
    }
    
    //$info .= "testing: ".$a['testing']."; orderby: $orderby; order: $order; meta_key: $meta_key; ";
    //$info .= "year: $year<br />";
    //$info .= "[num posts: ".count($webcasts_posts)."]<br />";
	
    return $info;
    
}

add_shortcode('top','anchor_link_top');
function anchor_link_top() {
    return '<a href="#top" class="anchor_top"><span class="up"></span>top</a>';
}

// Function to determine default taxonomy for a given post_type, for use with display_posts shortcode, &c.
function get_default_taxonomy ( $post_type = null ) {
    switch ($post_type) {
        case "post":
            return "category";
        case "page":
            return "page_tag"; // ??
        case "event":
            return "event-categories";
        case "product":
            return "product_cat";
        case "repertoire":
            return "repertoire_category";
        case "person":
            return "person_category";
        case "sermon":
            return "sermon_topic";
        default:
            return "category"; // default -- applies to type 'post'
    }
}

// Function to determine default category for given page, for purposes of Recent Posts &c.
function get_default_category () {
	
	$default_cat = "";
	
    if ( is_category() ) {
        $category = get_queried_object();
        $default_cat = $category->name;
    } else if ( is_single() ) {
        $categories = get_the_category();
        $post_id = get_the_ID();
        $parent_id = wp_get_post_parent_id( $post_id );
        //$parent = $post->post_parent;
    }
	
	if ( ! empty( $categories ) ) {
		//echo esc_html( $categories[0]->name );		 
	} else if ( empty($default_cat) ) {
        
		// TODO: check to see if name of Page is same as name of any Category
		if ( is_page() ) {
			// get page slug
			// compare slug to category slugs
		}
	}
	
	return $default_cat;
}

function sdg_digit_to_word($number){
    switch($number){
        case 0:$word = "zero";break;
        case 1:$word = "one";break;
        case 2:$word = "two";break;
        case 3:$word = "three";break;
        case 4:$word = "four";break;
        case 5:$word = "five";break;
        case 6:$word = "six";break;
        case 7:$word = "seven";break;
        case 8:$word = "eight";break;
        case 9:$word = "nine";break;
    }
    return $word;
}

// Facilitate search by str in post_title (as oppposed to built-in search by content or by post name, aka slug)
add_filter( 'posts_where', 'sdg_posts_where', 10, 2 );
function sdg_posts_where( $where, $wp_query ) {
    
    global $wpdb;
    
    if ( $search_term = $wp_query->get( '_search_title' ) ) {
        $search_term = $wpdb->esc_like( $search_term );
        $search_term = '\'%' . $search_term . '%\'';
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE ' . $search_term;
        //$where .= " AND " . $wpdb->posts . ".post_title LIKE '" . esc_sql( $wpdb->esc_like( $title ) ) . "%'";
    }
    
    // Get query vars
    $tax_args = isset( $wp_query->query_vars['tax_query'] ) ? $wp_query->query_vars['tax_query'] : null;
    $meta_args   = isset( $wp_query->query_vars['meta_query'] ) ? $wp_query->query_vars['meta_query'] : null;
    $meta_or_tax = isset( $wp_query->query_vars['_meta_or_tax'] ) ? wp_validate_boolean( $wp_query->query_vars['_meta_or_tax'] ) : false;

    // Construct the "tax OR meta" query
    if( $meta_or_tax && is_array( $tax_args ) && is_array( $meta_args )  ) {

        // Primary id column
        $field = 'ID';

        // Tax query
        $sql_tax  = get_tax_sql( $tax_args, $wpdb->posts, $field );

        // Meta query
        $sql_meta = get_meta_sql( $meta_args, 'post', $wpdb->posts, $field );

        // Modify the 'where' part
        if( isset( $sql_meta['where'] ) && isset( $sql_tax['where'] ) ) {
            $where  = str_replace( [ $sql_meta['where'], $sql_tax['where'] ], '', $where );
            $where .= sprintf( ' AND ( %s OR  %s ) ', substr( trim( $sql_meta['where'] ), 4 ), substr( trim( $sql_tax['where']  ), 4 ) );
        }
    }
    
    // Filter query results to enable searching per ACF repeater fields
    // See https://www.advancedcustomfields.com/resources/repeater/#faq "How is the data saved"
	// meta_keys for repeater fields are named according to the number of rows, e.g. item_0_description, item_1_description, so we need to adjust the search to use a wildcard for matching
	// Replace comparision operator "=" with "LIKE" and replace the wildcard placeholder "XYZ" with the actual wildcard character "%"
	//$where = str_replace("meta_key = 'program_items_XYZ", "meta_key LIKE 'program_items_%", $where);
    $where = str_replace("meta_key = 'program_items_XYZ", "meta_key LIKE 'program_items_%", $where);
    $where = str_replace("meta_key = 'personnel_XYZ", "meta_key LIKE 'personnel_%", $where);
	$where = str_replace("meta_key = 'date_calculations_XYZ", "meta_key LIKE 'date_calculations_%", $where);
	$where = str_replace("meta_key = 'date_assignments_XYZ", "meta_key LIKE 'date_assignments_%", $where);
	
	// TODO: do a similar replacement for meta VALUES so as to ignore punctuation -- e.g. compare LIKE value veni_XYZ_redemptor => veni%redemptor
	$where = str_replace("XXX", "%", $where);
    
    return $where;
}

/**
 * Explode list using "," and ", ".
 *
 * @param string $string String to split up.
 * @return array Array of string parts.
 */
function sdg_att_explode( $string = '' ) {
	$string = str_replace( ', ', ',', $string );
	return explode( ',', $string );
}

///

// Get a linked list of Terms
add_shortcode('list_terms', 'sdg_list_terms');
function sdg_list_terms ($atts = [], $content = null, $tag = '') {

	$info = "";
	
	$a = shortcode_atts( array(
      	'child_of'		=> 0,
		'cat'			=> 0,
		//'depth'			=> 0,
		'exclude'       => array(),
      	'hierarchical'	=> true,
		'include'       => array(),
		//'meta_key'	=> 'key_name',
     	'orderby'		=> 'name', // 'id', 'meta_value'
      	'show_count'	=> 0,
		'tax'			=> 'category',
		'title'        	=> '',
    ), $atts );
	
	$all_items_url = ""; // tft
	$all_items_link = ""; // tft
	$exclusions_per_taxonomy = array(); // init
	
	if ( $a['tax'] == "category" ) {
		$exclusions_per_taxonomy = array(); // TODO: set/get this as option -- e.g. STC array(1389, 1674, 1731)
		$all_items_url = "/news/";
	} else if ( $a['tax'] == "event-categories" ) {
		$exclusions_per_taxonomy = array();  // TODO: set/get this as option -- e.g. STC array(1675, 1690)
		$all_items_url = "/events/";
	}
	// Turn exclusion/inclusion attribute from comma-sep list into array as prep for merge/ for use w/ sdg_get_terms_orderby
	if ( !empty($a['exclude']) ) { $a['exclude'] = array_map('intval', explode(',', $a['exclude']) ); } //$integerIDs = array_map('intval', explode(',', $string));
	if ( !empty($a['include']) ) { $a['include'] = array_map('intval', explode(',', $a['include']) ); }
	$exclusions = array_merge($a['exclude'], $exclusions_per_taxonomy);
	$inclusions = $a['include'];
	$term_names_to_skip = array(); // e.g. 'Featured Posts', 'Featured Events'
	
	// List terms in a given taxonomy using wp_list_categories (also useful as a widget if using a PHP Code plugin)
    $args = array(
        'child_of' => $a['child_of'],
		//'depth' => $a['depth'],
		'exclude' => $exclusions,
		'include' => $inclusions,
        //'current_category'    => $a['cat'],
        'taxonomy'     => $a['tax'],
        'orderby'      => $a['orderby'],
        //'show_count'   => $a['show_count'],
        //'hierarchical' => $a['hierarchical'],
        //'title_li'     => $a['title']
    );
	$info .= "<!-- ".print_r($args, true)." -->"; // tft
	
	$terms = get_terms($args);
	
	/*
	'meta_query' => array(
        [
            'key' => 'meta_key_slug_1',
            'value' => 'desired value to look for'
        ]
    ),
    'meta_key' => 'meta_key_slug_2',
    'orderby' => 'meta_key_slug_2'
	*/
	
    if ($all_items_url) { 
        $all_items_link = '<a href="'.$all_items_url.'"';
        if ( $a['tax'] === "event-categories" ) {
            $all_items_link .= ' title="All Events">All Events';
        } else {
            $all_items_link .= ' title="All Articles">All Articles';
        }
        $all_items_link .= '</a>';
    }
	
	
	if ( !empty( $terms ) && !is_wp_error( $terms ) ){
		$info .= "<ul>";
		$info .= '<li>'.$all_items_link.'</li>';
		foreach ( $terms as $term ) {
			if ( !in_array($term->name, $term_names_to_skip) ) {
			//if ($term->name != 'Featured Events' AND $term->name != 'Featured Events (2)') {
				if ( $a['tax'] === "event-categories" ) {
                    $term_link = "/events/?category=".$term->slug;
                } else {
                    $term_link = get_term_link( $term );
                }
                $term_name = $term->name;
				//if ($term_name === "Worship Services") { $term_name = "All Worship Services"; }
				$info .= '<li>';
				$info .= '<a href="'.$term_link.'" rel="bookmark">'.$term_name.'</a>';
				$info .= '</li>';
			}		
		}
		$info .= "</ul>";
	} else {
		$info .= "No terms.";
	}
	return $info;
}

// Function to facilitate custom order when calling get_terms
/**
 * Modifies the get_terms_orderby argument if orderby == include
 *
 * @param  string $orderby Default orderby SQL string.
 * @param  array  $args    get_terms( $taxonomy, $args ) arg.
 * @return string $orderby Modified orderby SQL string.
 */
add_filter( 'get_terms_orderby', 'sdg_get_terms_orderby', 10, 2 );
function sdg_get_terms_orderby( $orderby, $args ) {
  	//if ( isset( $args['orderby'] ) && 'include' == $args['orderby'] ) {
	if ( isset( $args['orderby'] ) ) {
		if ($args['orderby'] === 'include') {
          $ids = implode(',', array_map( 'absint', $args['include'] ));
          $orderby = "FIELD( t.term_id, $ids )";
		} /*else if ($args['orderby'] === 'post_types') {
          $ids = implode(',', array_map( 'absint', $args['post_types'] ));
          $orderby = "FIELD( t.term_id, $ids )";
		}*/
	} 
	return $orderby;
}

// WIP -- add term to post programmatically
function sdg_add_post_term( $post_id = null, $arr_term_slugs = array(), $taxonomy = "", $return_info = false ) {
    
    // TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
    $term_ids = array();
    $ts_info = "";
    $result = "";
    
    // If post_id is empty, abort
    if ( empty($post_id) ) { return false; } // wip -- should this be null? or info msg?
    
    // Get the post_type
    $post_type = get_post_type( $post_id );
    
    // Get the available taxonomies for the given post_type
    $taxonomies = get_object_taxonomies( $post_type );
    
    // If a string was passed instead of an array, then explode it.
    if ( !is_array($arr_term_slugs) ) { $arr_term_slugs = explode(',', $arr_term_slugs); }    
    
    // NB: Hierarchical taxonomies must always pass IDs rather than names -- so, must get the IDs
    foreach ( $arr_term_slugs as $term_slug ) {
        
        foreach ( $taxonomies as $taxonomy ) {
        	$arr_term = term_exists( $term_slug, $taxonomy );
        	if ( $arr_term ) {
        		if ( has_term( $term_slug, $taxonomy, $post_id ) ) {
					$ts_info .= "[sdg_add_post_term] post $post_id already has $taxonomy: '$term_slug'. No changes made.<br />";
					//$ts_info .= "<!-- [sdg_add_post_term] post $post_id already has $taxonomy: $term_slug. No changes made. -->";
					//return '<div class="troubleshooting">'.$ts_info.'</div>';
					return $ts_info;
				}
        		//$term_ids[] = $arr_term['term_id'];
        		$term_id = $arr_term['term_id'];
        		if ( $return_info ) {
					$ts_info .= "[sdg_add_post_term] ";
					//$ts_info .= "<!-- [sdg_add_post_term] ";
					$ts_info .= "post_id: ".$post_id."; ";
					$ts_info .= "taxonomy: ".$taxonomy."; ";
					$ts_info .= "term_slug: ".$term_slug."; ";
					$ts_info .= "term_id: ".$term_id;
				}
        		$result = wp_set_post_terms( $post_id, $term_id, $taxonomy, true );
        		if ( $result ) { 
					$ts_info .= " >> success!"; 
				} else { 
					$ts_info .= " >> FAILED!";
				}
				$ts_info .= "<br />";
				//$ts_info .= " -->";
        		
        	}
        }
        
        //$term = get_term_by( 'slug', $term_slug, $taxonomy_name );
        //$term_id = $term->term_id; // get term id from slug
        //$term_ids[] = $term_id;
        
    }
    
    if ( $return_info ) {
		return $ts_info;	
	} else {		
		return $result;
	}
	
}

function sdg_remove_post_term( $post_id = null, $term_slug = null, $taxonomy = "", $return_info = false ) {
    
    // TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
    $term_ids = array();
    $ts_info = "";
    $result = "";
    
    // If post_id is empty, abort
    if ( empty($post_id) ) { return false; } // wip -- should this be null? or info msg?
    
    // TODO -- Cleanup: remove t4m-updated from all events -- it doesn't apply because events don't have a title_for_matching field -- they have title_uid instead
    
    $result = wp_remove_object_terms( $post_id, $term_slug, $taxonomy ); // wp_remove_object_terms( int $object_id, string|int|array $terms, array|string $taxonomy )
    
    if ( $return_info == true ) {
        $info .= "<!-- wp_remove_object_terms -- ";
        $info .= $term_slug;
        if ( $result ) { $info .= "success!"; } else { $info .= "FAILED!"; }
        $info .= " -->";
        return $info;
    } else {
        return $result;
    }
}

// Function to sort arrays by value
function sdg_arr_sort( $sort_type = "value", $key_name = null, $order = 'ASC' ) {

	if ( $sort_type == "value" ) {
    
    	// Sort by value
        return function ( $a, $b ) use ( $key_name, $order ) {
            if ( $order == "DESC" ) {
                return ($a[$key_name] > $b[$key_name]) ? -1 : 1; 
            } else {
                return ($a[$key_name] < $b[$$key_name]) ? -1 : 1;
            }
        };
        
    } else {
    
    	// Sort by key
        return function ( $a, $b ) use ( $order ) {

			if ($a==$b) return 0;
			//return ($a < $b) ? -1 : 1;
			
			if ( $order == "DESC" ) {
                return ($a > $b) ? -1 : 1;
            } else {
                return ($a < $b) ? -1 : 1;
            }
            
        };
        
    }
}

// Update Widget Title if "Recent Posts in Category"
add_filter( 'widget_title', 'sdg_widget_title' );
function sdg_widget_title( $title ) {
	if (strtolower($title) === strtolower("Recent Posts In Category")) {
		$default_cat = sdg_get_default_category();
		if ($default_cat === "Latest News") {
			return "Latest News";
		} else {
			return "Recent Articles in " . $default_cat;
		}
	} else {
		//echo "title: $title<br />";
		return $title;
	}	
}

/**
 * Adds meta boxes to the content editing screens.
 */
add_action( 'add_meta_boxes', 'sdg_add_meta_boxes' );
function sdg_add_meta_boxes() {

	global $post;
	$post_type = get_post_type( $post );
	
    // Add metabox for troubleshooting
    $screen = get_current_screen();
    if ($post_type == 'event-recurring') {
        // CPT Object Info Editor Sidebar Meta Box
        add_meta_box(
            'post_obj_info', // $id
            __( 'Info for Troubleshooting: Post Object Info', 'sdg' ), // $title
            'sdg_postobj_info_meta_box_callback', // $callback
            null, //array('post', $screen),
            'normal', // $context
            'high' // $priority -- default
        );
    } else if ($post_type == 'snippet') {
        add_meta_box( 
        	'troubleshooting_meta_box', 
        	__( 'Snippet Posts', 'sdg' ), 
        	'sdg_snippet_posts_display_callback', 
        	null,
        	'side', 
        	'high' 
    	);
    } else {
        add_meta_box( 
        	'troubleshooting_meta_box', 
        	__( 'Info for Troubleshooting', 'sdg' ), 
        	'sdg_troubleshooting_display_callback', 
        	null, //array('post', $screen),
        	'side', 
        	'high' 
    	);
    }
    
}

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function sdg_troubleshooting_display_callback( $post ) {
    echo "test";
}

function sdg_snippet_posts_display_callback( $post ) {
    $info = "";
    $info .= "This snippet is set to appear on the following posts:<br />";
    $info .= "coming soon... posts array";
    
    $meta = get_post_meta( $post );
    //
    
    echo $info;
}
 
/**
 * Post Object Info: Prints the metabox content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function sdg_postobj_info_meta_box_callback( $post ) {

	//
	$screen = get_current_screen();
	//echo "<p>screen id/base/post_type: $screen->id/$screen->base/$screen->post_type</p>";
	
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'sdg_save_meta_box_data', 'sdg_meta_box_nonce' );
    
	$post_type = get_post_type( $post );
    $obj = get_post_type_object( $post_type );

	//$post_id = $post->ID;
	//echo "post_id: $post_id<br />";
	
    $info = '<pre>'.print_r( $obj, true ).'</pre>';
    echo $info;
	
}

/*** Admin Columns Pro -- Capabilities (permissions) ***/

/**
* Allow another role to manage columns.
* By default, only administrators are allowed to manage columns.
*/
add_action( 'admin_init', function () {
    global $wp_roles;
    // Allow users with the role 'sdg_music' to manage columns
    $wp_roles->add_cap( 'sdg_music', 'manage_admin_columns' );
} );


/*** Custom Post Types Content ***/

// Umbrella function to get CPT content
// TODO: phase this out? It makes fine-tuning content ordering a bit tricky...
function sdg_custom_post_content() {
	
	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
	$info = "";
	$ts_info = "";
	$post_type = get_post_type( get_the_ID() );
	
	if ($post_type === "group") {
		$info .= get_cpt_group_content();
	} else if ($post_type === "liturgical_date") {
		$info .= get_cpt_liturgical_date_content();
	} else if ($post_type === "person") {
		$info .= get_cpt_person_content();
	} else if ($post_type === "repertoire") {
		$arr_info = get_cpt_repertoire_content();
		$info .= $arr_info['info'];
		$ts_info .= $arr_info['ts_info'];
	} else if ($post_type === "edition") {
		$info .= get_cpt_edition_content();
	} else if ($post_type === "reading") {
		$info .= get_cpt_reading_content();
	} else if ($post_type === "sermon") {
		//$info .= get_cpt_sermon_content(); // Disabled because the function doesn't currently add any actual custom content.
	} else {
		//$info .= "<p>[post] content (default)-- coming soon</p>";
		//return false;
		//return;
	}
	
	if ( $do_ts ) { $info .= $ts_info; }
	
	return $info;
}

// Modify the display order of CPT archives
//add_filter( 'posts_orderby' , 'custom_cpt_order' );
add_action('pre_get_posts', 'sdg_pre_get_posts'); //mind_pre_get_posts
function sdg_pre_get_posts( $query ) {
  
    if ( is_admin() ) {
        return $query; 
    }
	
	if ( is_archive() && $query->is_main_query() ) {
		
		// Custom CPT ORDER
        if ( isset($query->query_vars['post_type']) ) {
            $post_type = $query->query_vars['post_type'];
            if ($post_type === 'bible_book') {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'sort_num');
                $query->set('order', 'ASC');
            } else if ($post_type === 'sermon') {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'sermon_date');
                $query->set('order', 'DESC');
            } else if ($post_type === 'person') {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'last_name');
                $query->set('order', 'ASC');
            } else if ($post_type === 'newsletter') {
                $meta_query = array(
					'relation' => 'AND',
					'volume' => array(
						'key' => 'volume_num',
						'type' => 'NUMERIC',
					),
					'number' => array(
						'key' => 'newsletter_num',
						'type' => 'NUMERIC',
					),
				);
				$query->set('meta_query', $meta_query);
                $query->set('orderby', array( 'volume' => 'DESC', 'number' => 'DESC' ) );
            } /*else if ($post_type === 'liturgical_date') { // atcwip
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'date_time');
                $query->set('order', 'DESC');
            }*/
        }
        
	}
                                                               
  	return $query;
}

// Category/Taxonomy Description as Widget
add_shortcode('post_category_widget', 'sdg_get_category_widget');
function sdg_get_category_widget ( $post_id = null ) { // or $term_id?
    //#_CATEGORYNOTES -- Events Manager
}

// Post Resources (ACF field)
add_shortcode('post_sidebar_widget', 'get_post_sidebar_widget');
function get_post_sidebar_widget ( $post_id = null ) {
    
    if ($post_id == null) { $post_id = get_the_ID(); }
    $info = "";
    $info = "<!-- Post Sidebar Widget Content for post_id: $post_id -->";
    
    $widget_title = get_post_meta( $post_id, 'post_sidebar_widget_title', true );
    if ( empty($widget_title) ) { $widget_title = "More Resources"; }
    
    $widget_content = get_post_meta( $post_id, 'post_sidebar_widget_content', true );
    //$widget_content = wpautop($widget_content);
    
    //$info .= "<!-- ACF post_sidebar_widget_content for post_id $post_id: ".print_r($sidebar_content,true)."-->";

    if ( !empty($widget_content) ) {
        //$info .= $widget_content;
        $info .= '<section class="widget widget_text widget_custom_html">';
        $info .= '<h2 class="widget-title">'.$widget_title.'</h2>';
        $info .= '<div class="textwidget custom-html-widget">';
        $info .= $widget_content;
        $info .= '</div>';
        $info .= '</section>'; 
        
    } else {
        $info .= "<!-- No post_sidebar_widget_content found. -->";
    }
    
    return $info;
    
}

// Post Resources (ACF field)
add_shortcode('display_post_resources', 'get_post_resources');
function get_post_resources ( $post_id = null ) {
    
    if ($post_id == null) { $post_id = get_the_ID(); }
    $info = "<!-- Resources for post_id: $post_id -->";
    
    $post = get_post( $post_id );
    $post_type = $post->post_type;
    
    $resources = get_post_meta( $post_id, 'post_resources', true );
    $info .= "<!-- ACF resources for post_id $post_id: ".print_r($resources,true)."-->";

    if ( !empty($resources) ) {

        $info .= "<hr />";
        $resources_header = get_post_meta( $post_id, 'resources_header', true );
        if ( empty($resources_header) ) { $resources_header = "Resources"; }
        $link_class = get_post_meta( $post_id, 'resource_link_styling', true );

        $info .= '<h2 id="resources">'.$resources_header.'</h2>';

        foreach ( $resources as $attachment ) {

            if ( isset($attachment['ID']) ) {
                $attachment_id = $attachment['ID'];
            } else {
                $attachment_id = $attachment;
            }

            $info .= "<!-- attachment: ".print_r($attachment,true)."-->";          

            $attachment_url = wp_get_attachment_url( $attachment_id );
            $attachment_title = get_the_title( $attachment_id );
            
            // TODO: get/set via options? -- these are STC-specific naming adjustments
            if ( strpos($attachment_title, 'Leaflet') !== false && $post_type == 'event' ) {
                $attachment_title = 'Leaflet';
            } else if ( strpos($attachment_title, 'Program') !== false && $post_type == 'event' ) {
                $attachment_title = 'Program';
            }
            
            $info .= make_link($attachment_url, $attachment_title, null, $link_class, "_blank")."<br />";

        }
    } else {
        $info .= "<!-- No Resources found. -->";
    }
    
    return $info;
    
}

// ACF: Change the gallery button label in the CMS
add_action('acf/input/admin_footer', 'sdg_acf_admin_footer');
function sdg_acf_admin_footer() {
	
	?>
	<script>
	(function($) {
		
		$('.acf-field-54ae017167b45 a.add-attachment').text('Add/Edit Resources');
		
	})(jQuery);	
	</script>
	<?php
	
}

//add_filter( 'the_content', 'sdg_the_content', 20, 1 );
function sdg_the_content( $content ) {
    
    $post_id = get_the_ID();    
    return $content;
    
 }

/**
 * Remove empty paragraphs created by wpautop()
 * @author Ryan Hamilton
 * @link https://gist.github.com/Fantikerz/5557617
 */
//add_filter('the_content', 'remove_empty_p', 20, 1);
function sdg_remove_empty_p( $content ) {
	//$content = force_balance_tags( $content );
	$content = preg_replace( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content );
	$content = preg_replace( '~\s?<p>(\s|&nbsp;)+</p>\s?~', '', $content );
	return $content;
}




/*** MATCH PLACEHOLDERS ***/

/*** Match placeholders to real post objects; return id (single match) or posts array (multiple matches) ***/
function match_placeholder ( $args = array() ) {
    
    // TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
    $arr_info = array();
    $matches = array();
    $info = "";

    $defaults = array( // the defaults will be overidden if set in $args
        'index'         => null,
        'post_id'       => null,
        'item_title'    => null,
        'item_label'    => null,
        'repeater_name' => null,
        'field_name'    => null,
        'taxonomy'      => null,
        'display'      => null, // Do we really need this?
    );
    
    // Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$i = $index; // tmp -- TODO: solve this better
    
    //$info .= "match args: <pre>".print_r($args, true)."</pre>";
    
    // TODO: deal specially w/ junk placeholders like 'x'? Or just delete these directly via sql queries?
    
    // Abort if no post_id. TODO: determine additional conditions for which to abort? e.g....?
    if ( empty($post_id) ) { 
        $info .= "[match_placeholder] post_id is empty -> match process aborted<br />";
        $arr_info['matches'] = $matches; // empty array
		$arr_info['info'] = $info;
		return $arr_info;
    }
    
    if ( !($taxonomy) ) {
    	//$info .= "[match_placeholder] find_matching_post<br />";
        $arr_match_results = find_matching_post( $item_title, $item_label, $field_name, 'single' );
    } else {
    	//$info .= "[match_placeholder] taxonomy: find_matching_term<br />";
        $arr_match_results = find_matching_term( $item_title, $field_name, 'single' );
    }
    $info .= "[match_placeholder] ".$arr_match_results['info']; // ."<br />"
    
    if ( isset($arr_match_results['post_id']) || isset($arr_match_results['term_id']) ) {
        
        // If a single match was found, update the repeater row accordingly
        
        if ( isset($arr_match_results['post_id']) ) {
			$match_id = $arr_match_results['post_id']; // A single matching POST was found
        } else if ( isset($arr_match_results['term_id']) ) {        	
        	$match_id = $arr_match_results['term_id']; // A single matching TERM was found
        }
        
        $matches[] = $match_id; // populate the field for return -- is this really needed? maybe not...
        
        //$info .= '[match_placeholder] <span class="nb">match found</span> for placeholder!: post_id ['.$match_id.']<br />';
        //$info .= "<!-- match found for placeholder!: post_id [".$match_id."] -->";
        
        // TODO: ??? remove program-placeholders or program-personnel-placeholders or program-item-placeholders admin_tag, if applicable -- dev: 2176; live: 2547
        
        if ( $repeater_name && $match_id ) {
        
            // Update "field_name" within the $i-th row of "repeater_name"
            $sub_field_value = $match_id;
            // TODO: determine whether it's necessary to format value differently if updating a relationship field which accepts multiple values... format as array(?)            
            $info .= "[match_placeholder] update_sub_field ((row $i/$repeater_name/$field_name)) for post_id: $post_id with val: $sub_field_value >> ";
            //$info .= '[match_placeholder] <span class="nb">['.$i.'] update_sub_field ['.$repeater_name.'/'.$field_name.']: ';
            $info .= '<span class="nb">';
            if ( update_sub_field( array($repeater_name, $i, $field_name), $sub_field_value, $post_id ) ) { $info .= "SUCCESS!"; } else { $info .= "FAILED!"; }
            $info .= '</span><br />';
            
            $info .= sdg_add_post_term( $post_id, 'placeholder-matched', 'admin_tag', true );
            
        }
        
    } else if ( isset($arr_match_results['posts']) || isset($arr_match_results['terms']) ) {
    
    	// Multiple matches found...
    	if ( isset($arr_match_results['posts']) ) {
			$matches = $arr_match_results['posts']; // Multiple matching POSTs were found
        } else if ( isset($arr_match_results['terms']) ) {        	
        	$matches = $arr_match_results['terms']; // Multiple matching TERMs were found -- at the moment this is not actually possible given the way the find_matching_term function is written, but this is subject to change
        }
    	$info .= count($matches)." match(es) found for placeholder!: <pre>".print_r($matches, true)."</pre><br />";
        // .... more than one item... what to do in terms of repeater row updates?
        $info .= sdg_add_post_term( $post_id, 'multiple-placeholder-matches', 'admin_tag', true );
        
    } else {
        
        // No match found
        // TODO: fine tune this to add program-personnel-placeholders or program-item-placeholders tag?
        //$info .= "No matches found.<br />";
        $info .= sdg_add_post_term( $post_id, 'program-placeholders', 'admin_tag', true );
    }
    
    $arr_info['matches'] = $matches;
    $arr_info['info'] = $info;
    return $arr_info;
    
}

function find_matching_post( $title_str = null, $label_str = null, $field_name = null, $return = 'single') {
    
    if ( $title_str == null ) { return null; } // Nothing to match if the title_str is empty
    
    $sanitized_title = $str = sanitize_title( $title_str );
    $title_for_matching = super_sanitize_title( $title_str );
    
    // Initialize vars
    $arr_post_types = array( 'repertoire', 'person', 'group', 'ensemble', 'sermon', 'reading' ); // TODO: consider other options for post_types
    $arr_info = array();
    $info = "";
    
    // Set up the basic query args
    $args = array(
		'post_type' => $arr_post_types,
		'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby'	=> 'ID',
        'order'	=> 'ASC',
	);
    
    // If we're NOT looking for repertoire, try matching the sanitized title_str to an existing post slug
    // TODO: account for non-rep program_items other than 'sermon' -- e.g. ???? ...
    if ( $field_name != 'program_item' || ( $field_name == 'program_item' && $label_str == 'sermon' ) ) {
        
        $args['name'] = $sanitized_title;
        $args['numberposts'] = 1;
        
    } else {
        
        // For non-sermon program items, add meta_query
        // (1) Search by title_uid/title_for_matching -- best, most likely to be accurate
        $args['meta_query'] = 
            array(
                //'relation' => 'OR',
                array(
                    'key'   => "title_for_matching", // is this the only option? what about matching post_title? etc. Also TODO: check to see if the title_for_matching is correctly populated for all posts...
                    'value' => $title_for_matching,
                ),
                /*array(
                    'key'   => "title_clean",
                    'value' => $title_str,
                )*/
            );

    }
    
	$arr_posts = new WP_Query( $args );
    $posts = $arr_posts->posts;
    
    // WIP -- things to try if no matches are found with first query
    if ( !$posts && $field_name == 'program_item' && $label_str !== 'sermon' ) { // also if field_name == "person"?
        
        //$info .= "query args: ".print_r($args, true)."<br />";
        //$info .= "<!-- query args: ".print_r($args, true)." -->";
        
        // TODO:
        // If no posts were matched, try again
        //$info .= "No matches found in first query. Try another.\n";
        
        // Try again after removing 'Anonymous' -- e.g. 'The Coventry Carol, Anonymous'

        // (2) Search by post_title
        // (3) Search by title_clean

        // Service Settings
        // If it's Evensong, then Service = Mag & Nunc
        // If it's a Eucharist, then Service = Mass setting/Communion service
        // e.g. Howells' "Collegium Regale":
        // * Collegium Regale
        // * Communion Service in B-flat (Collegium Regale)
        // * Magnificat and Nunc dimittis in B-flat (Collegium Regale)
        // * Nunc dimittis Collegium Regale
        // * Te Deum (Collegium Regale)
        // * Te Deum & Jubilate (Collegium Regale)

        // Hymns (by label_str): search by catalog_num
        //if ( $label_str == "Hymn" ) {
            // If $title_str is simply an integer, then search for matching catalog_num w/ Hymns category
        //}
        
        // Psalms
        // if num only, prepend 'Psalm' or 'Psalms'
        
        // Rites
        // e.g. 'Rite I' -- Program Label to match is 'Holy Eucharist, Rite I'
        
    }
    
    if ( $posts ) {
        
        if ( $return == 'arr' ) {        
            
            $arr_info['posts'] = $posts;
            
        } else {
            
            if ( count($posts) == 1 ) {
                $arr_info['post_id'] = $posts[0]->ID;
                $info .= "matching post found [id: ".$posts[0]->ID."]";
            } else if ( count($posts) > 1 ) {
                $arr_info['posts'] = $posts;
                $info .= "multiple post matches found";
            }
            
        }
        
    } else {
        
        //$info .= "<!-- Last SQL-Query: <pre>".$arr_posts->request."</pre> -->";        
        $info .= "No post matches found for title_str: $title_str";
        
    }
    
    $info = '[find_matching_post] <span class="nb">'.$info.'</span><br />';
    $arr_info['info'] = $info;
    
    return $arr_info;
    
}

function find_matching_term( $title_str = null, $field_name = null, $return = 'single') {
    
    if ( $title_str == null ) { return null; } // Nothing to match if the title_str is empty
    
    // init vars
    $arr_info = array();
    $term_tax = null;
    $info = "";
    
    if ( $field_name == 'item_label' ) {
        $term_tax = 'program_label';
    } else if ( $field_name == 'role' ) {
        $term_tax = 'person_role';
    } else  {
        $term_tax = $field_name;
    }
    
    // Get term by name in custom taxonomy: $term_tax.
    if ( $term = get_term_by('name', $title_str, $term_tax ) ) {
        $arr_info['term_id'] = $term->term_id;
        $info .= "term '".$term->name."' found [id: ".$term->term_id."]";
    } else {
        $info .= "No matching term found for $term_tax: '$title_str'";
    }
    
    $info = '[find_matching_term] <span class="nb">'.$info.'</span><br />';
    $arr_info['info'] = $info;
    
    return $arr_info;
    
}

/*** End MATCH PLACEHOLDERS ***/


/*** MISC UTILITY/HELPER FUNCTIONS ***/

/**
 * Simple helper to debug to the console
 *
 * @param $data object, array, string $data
 * @param $context string  Optional a description.
 *
 * @return string
 */
function sdg_debug_to_console($data, $context = 'Debug in Console') {

    // Buffering to solve problems frameworks, like header() in this and not a solid return.
    ob_start();

    $output  = 'console.info(\'' . $context . ':\');';
    $output .= 'console.log(' . json_encode($data) . ');';
    $output  = sprintf('<script>%s</script>', $output);

    echo $output;
}

// Convert the time string HH:MM:SS to number of seconds (for flowplayer cuepoints &c.)
function xtime_to_seconds($str_time){

	$parsed = date_parse($str_time);
	$num_seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
	
	return $num_seconds;
	
}

// Array sorter function, for use with usort
function sdg_array_sorter( $key, $key_type = 'array_key' ) {
    return function ( $a, $b ) use ( $key, $key_type ) {
        
        if ( $key_type == 'meta_key' ) {
            $a_meta = get_post_meta( $a['ID'], $key, true );
            $b_meta = get_post_meta( $b['ID'], $key, true );
            //return $a_meta - $b_meta;
            return strnatcmp($a_meta, $b_meta); //return strcmp($a_meta, $b_meta);
        } else {
            return strnatcmp($a[$key], $b[$key]);
        }
        
    };
}

// https://developer.wordpress.org/reference/hooks/document_title_parts/
// Filters the parts of the document title.
add_filter( 'document_title_parts', function( $title_parts_array ) {
    
    // TS/logging setup
    $do_ts = true; 
    $do_log = false;
    if ( get_post_type( get_the_ID() ) == 'event' ) { $do_log = true; }
    sdg_log( "divline2", $do_log );
    
    sdg_log( "filter: document_title_parts", $do_log );
    
    if ( get_post_type( get_the_ID() ) == 'event' ) {
    	$post_id = get_the_ID();
        $title_parts_array['title'] = make_clean_title( $post_id );
    }
    
    return $title_parts_array;
    
} );

// Get Image URL from ID
// [image_url_from_id id='#_ATT{landing_page_image}']
add_shortcode('image_url_from_id', 'get_image_url_from_id');
function get_image_url_from_id( $atts = [] ) {
    
    $args = shortcode_atts( 
        array(
            'id'   => null
        ), $atts );
	$image_id = $a['id'];
	//$image_id = (int) $a['id'];
	
	if ( ! empty($image_id) ) { $url = wp_get_attachment_url($image_id); } else { $url = null; }
	
	return $url;
	
}

// Helper function: get link by slug
function get_link_by_slug($slug, $type = 'post') {
  $post = get_page_by_path($slug, OBJECT, $type);
  return get_permalink($post->ID);
}

//
function restore_html( $info ) {
	$arr = array('&lt;'=>'<','&gt;'=>'>','&quot;'=>'"');
	foreach ($arr as $search=>$replace) {
		$info = str_replace($search,$replace,$info);
	}
	return $info;
}

// Make hyperlink
function make_link( $url, $text, $title = null, $class = null, $target = null) {
	
	// TODO: sanitize URL?
	$link = '<a href="'.$url.'"';
	if ( $text && empty($title) ) { $title = $text; } // Use text as title if title is empty
	if ( $title ) { $link .= ' title="'.$title.'"'; }
	if ( $target ) { $link .= ' target="'.$target.'"'; }
    if ( $class ) { $link .= ' class="'.$class.'"'; }
	$link .= '>'.$text.'</a>';
	//return '<a href="'.$url.'">'.$linktext.'</a>';
	
	return $link;
}

// Check to see if a postmeta record already exists for the specified post_type, meta_key, and meta_value.
// Option to exclude a specific post_id from the search -- e.g. in searching to see if any *other* post has the same title_for_matching.
function meta_value_exists( $post_type, $post_id, $meta_key, $meta_value ) { //, $num_posts
    
    if ( ! ($post_type && $meta_key && $meta_value) ){
        return null;
    }
    
    $wp_args = array(
        'post_type'   => $post_type,
        'post_status' => 'publish',
        //'numberposts' => $num_posts,
        'meta_key'    => $meta_key,
        'meta_value'  => $meta_value,
        'fields'      => 'ids'
    );
    // if post_id has been provided, then exclude that ID from the search
    if ($post_id ) {
        $wp_args['exclude'] = array( $post_id );
    } 
    
    $matching_posts = get_posts($wp_args);
    
    if ( count($matching_posts) > 0 ) {
        return count($matching_posts);
    } else {
        return false;
    }
    
}

// @param $include array to limit posts to certain IDs
function sdg_select_distinct ( $args = '' ) {
	
    global $wpdb;
	
	$info = "";
	$dropdown_menu = ""; // init
	
	$defaults = array(
		'post_type'   	=> '',
		'meta_key'   	=> '',
		'arr_include'   => array(),
		//'orderby'   	=> 'date',
		//'relationship'   => '',
	);
	
	// Parse incoming $args into an array and merge it with $defaults
	$r = wp_parse_args( $args, $defaults );
	
	$post_type = $r['post_type'];
	$meta_key = $r['meta_key'];
	$arr_include = $r['arr_include'];
	//$orderby = $r['orderby'];
	
	$info .= "<p>post_type: $post_type; meta_key: $meta_key; arr_include: ".print_r($arr_include, true)."</p>";
	
    $sql = "SELECT DISTINCT `meta_value` AS 'ID', `post_name`, `post_title` FROM {$wpdb->prefix}postmeta AS pm, {$wpdb->prefix}posts AS p WHERE meta_key = '".$meta_key."' AND p.`ID`=pm.`meta_value` AND `p`.`post_type`='".$post_type."'";
    
    // TODO: make this more secure somehow?
    // see https://stackoverflow.com/questions/20203063/mysql-where-id-is-in-array
    if ($arr_include) { 
        $include = implode(",",$arr_include);
        $sql .= "AND p.`ID` IN ($include)";
    }
    //$info .= '<pre>sql: '.$sql.'</pre>';

    $results = $wpdb->get_results( $sql );
	
	//$info .= 'last_query: '.print_r( $wpdb->last_query, true); // '<pre></pre>'
   	//$info .= '<pre>'.print_r($results, true).'</pre>';
	//echo $info;
	//return $info; // tft
	
	return $results;
}

// TODO: document params &c.
function sdg_selectmenu ( $args = '' ) {
	
	$info = "";
	$ts_info = "";
	$dropdown_menu = ""; // init
	
	$defaults = array(
		'label'   	 	=> '',
		'arr_values' 	=> null,
		'arr_type'   	=> 'objects',
		'select_name'	=> '',
		'meta_key'   	=> '', // redundant w/ select_name?
		'show_any'   	=> true,
		'show_other' 	=> false,
		'tax'   		=> '',
		'orderby'   	=> '',
		'value_field'   => 'term_id',
	);
	
	// Parse incoming $args into an array and merge it with $defaults
	$r = wp_parse_args( $args, $defaults );
	
	$label = $r['label'];
	$arr_values = $r['arr_values'];
	//$arr_type = $r['arr_type']; // wip
	$select_name = $r['select_name'];
	//$meta_key = $r['meta_key'];
	$show_other = $r['show_other'];
	$show_any = $r['show_any'];
	$tax = $r['tax'];
	$orderby = $r['orderby'];
	$value_field = $r['value_field'];
	
	if ( isset($_GET[$select_name]) ) { 
		$selected = $_GET[$select_name]; 
	} else if ( isset($_GET[$tax]) ) { 
		//$select_name = $tax;
		$selected = $_GET[$tax];
		$ts_info .= 'selected ['.$tax.']: '.$selected.'<br />';
	} else { 
		$selected = null;
	}
	//$ts_info .= 'selected ['.$select_name.']: '.$selected.'<br />';
	
	$info .= '<span class="menu_label">'.$label.':</span>'; // Label preceding select menu
	
	if ($arr_values) {
		
		//$ts_info .= '<pre>'.print_r($arr_values, true).'</pre>';
		
		// Set up the select menu
		$dropdown_menu .= '<select name="'.$select_name.'" id="'.$select_name.'" class="postform">';
		
		// Add the "Any" option at the top of the list
		if ($selected == 'any') { $selection = ' selected="selected"'; } else { $selection = ''; }
		if ($label == "Text") { $label = "Book"; } // TODO: do this better -- perhaps menu label & other label...
		$dropdown_menu .= '<option class="level-0" value="any"'.$selection.'>Any '.$label.'</option>';
		
		$count_optgroups = 0; // init
		
		// Loop through the array of select options
		foreach ($arr_values as $key => $value) {
		//foreach ($arr_values as $key => $obj) {
			
			if (is_object($value)) {
				$obj = $value;
				if ($obj->ID) { 
					$value = $obj->ID;
				}
				if ($obj->post_title) { 
					$display_value = $obj->post_title; 
				}
			} else {
				//$value = $key;
				$display_value = $value; 
			}
            
			
			// Check to see if the obj is an optgroup label
			if ( substr( $value, 0, 8 ) === "optgroup" ) {
			//if ($value == "optgroup_label") {
				if ($count_optgroups > 0) { $dropdown_menu .= '</optgroup>'; }
				$dropdown_menu .= '<optgroup label="'.$display_value.'">';
				$count_optgroups++;
			} else {
                //echo "post_id: $obj->post_id; post_name: $obj->post_name; post_title: $obj->post_title<br />";
                if ($selected == $value) { $selection = ' selected="selected"'; } else { $selection = ''; }
                $dropdown_menu .= '<option class="level-0" value="'.$value.'"'.$selection.'>'.$display_value;
                //$dropdown_menu .=' ['.$obj->post_id.']';
                //$dropdown_menu .='($obj->count)';
                $dropdown_menu .= '</option>';
			}
        }
		if ($show_other === true) {
			if ($selected == 'other') { $selection = ' selected="selected"'; } else { $selection = ''; }
			$dropdown_menu .= '<option class="level-0" value="other"'.$selection.'>Other</option>';
		}
		$dropdown_menu .= '</optgroup>';
		$dropdown_menu .= '</select>';
		
	} else if ($tax !== null) {
		
		$args = array(
			'show_option_all' => 'Any '.$label,
            'name' => $select_name, // $tax
            'taxonomy' => $tax,
            'orderby' => $orderby,
            'echo' => false,
            'show_count'	=> 1,
            'hierarchical'	=> 1,
            'selected' => $selected,
            'value_field' => $value_field
        );
		
		$dropdown_menu = wp_dropdown_categories( $args );
		
	}
	
	$info .= $dropdown_menu;
	$info .= $ts_info;
	
	return $info;

}

/*** SEARCH ***/

// Get name of post_type
function get_post_type_str( $type = "" ) {
	if ($type === "") { $type = get_post_type(); }
	if ($type === 'post') {
		return "Article";
	} else if ($type === 'event') {
		return "Event";
	} else if ($type === 'event_series') {
		return "Event Series";
	} else if ($type === 'repertoire') {
		return "Musical Work";
	} else if ($type === 'liturgical_date') {
		return "Liturgical Date";
	} else {
		return ucfirst($type);
	}
}

// Filter search results
// Add shortcode for display of search filter links
add_shortcode('display_search_filters', 'sdg_search_filter_links');
// Get a linked list of Terms
function sdg_search_filter_links ($atts = [], $content = null, $tag = '') {

	//global $wp;
	//esc_url( remove_query_arg( 'type' ) ); // this doesn't seem to work
	//$current_url = home_url( add_query_arg( array(), $wp->request ) );
  	$current_url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
	//$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) ); // nope
	$current_url = remove_query_arg( 'type', $current_url );
	//$current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	$info = "";
	
	$a = shortcode_atts( array(
		'exclude'       => '',
		'include'       => '',
     	'orderby'		=> 'name', // 'id', 'meta_value'
      	'show_count'	=> 1,
		'title'        	=> '',
    ), $atts );
	
	$terms = array();
	$terms[] = array('name'=>'People', 'post_type' => 'person');
	$terms[] = array('name'=>'Pages', 'post_type' => 'page');
	$terms[] = array('name'=>'Articles', 'post_type' => 'post');
	$terms[] = array('name'=>'Musical Works', 'post_type' => 'repertoire');
	$terms[] = array('name'=>'Events', 'post_type' => 'event');
	// TODO: add "Other" option to exclude all explicitly filterable post types
	
	if ( !empty( $terms ) && !is_wp_error( $terms ) ){
		$info .= "<ul>";
		$info .= '<li>'.$all_items_link.'</li>';
		foreach ( $terms as $term ) {
			//if ( !in_array($term->name, $term_names_to_skip) ) {
				//$term_link = get_term_link( $term );
				$term_link = $current_url."&type=".$term['post_type'];
				//$term_name = $term->name;
				$term_name = $term['name'];
				//if ($term_name === "Worship Services") { $term_name = "All Worship Services"; }
				$info .= '<li>';
				$info .= '<a href="'.$term_link.'" rel="bookmark">'.$term_name.'</a>';
				$info .= '</li>';
			//}		
		}
		$info .= "</ul>";
	} else {
		$info .= "No terms.";
	}
	return $info;
}

// Write log info to the JS console.
// NB: this breaks the WooCommerce checkout process! And possibly other functionality -- use with caution -- related to AJAX responses...
function sdg_console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

//
function sdg_log( $log_msg, $do_log = true ) {
    
    // Set do_ts to true for active troubleshooting; false for cleaner source & logs
    if ( $do_log === false ) { return; } // Abort if logging is turned off (set per calling fcn)
    
	// Create directory for storage of log files, if it doesn't exist already
	$log_filename = $_SERVER['DOCUMENT_ROOT']."/_sdg-devlog";
    if (!file_exists($log_filename)) {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
	
	$timestamp = current_time('mysql'); // use WordPress function instead of straight PHP so that timezone is correct -- see https://codex.wordpress.org/Function_Reference/current_time
	$datestamp = current_time('Ymd'); // date('d-M-Y')
	
	//sdg_log( "loop_item_divider", $do_log );
	if ($log_msg == "divline1") {
		$log_msg = "\n=================================================================================\n";
	} else if ($log_msg == "divline2") {
		$log_msg = "-----------";
	} else {
		$log_msg = "[sdg_log ".$timestamp."] ".$log_msg;
		//$log_msg = "[sdg_log ".$timestamp."] ".$log_msg."\n";
	}
	
	// Generate a new log file name based on the date
	// (If filename does not exist, the file is created. Otherwise, the existing file is overwritten, unless the FILE_APPEND flag is set.)
    $log_file = $log_filename.'/' . $datestamp . '-sdg_dev.log';
	// Syntax: file_put_contents(filename, data, mode, context)
	file_put_contents($log_file, $log_msg . "\n", FILE_APPEND); 
}

/*** Archive Pages ***/

//
add_filter( 'get_the_archive_title', 'sdg_theme_archive_title' );
function sdg_theme_archive_title( $title ) {
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    } /*elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_author() ) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    } elseif ( is_tax() ) {
        $title = single_term_title( '', false );
    }*/
  
    return $title;
}

//
add_action("template_redirect","sdg_post_type_access_limiter");
function sdg_post_type_access_limiter(){
    if( ! current_user_can('read_admin_notes') && in_array( get_post_type(), array( 'admin_note' ) ) ) {
    //if( ! is_user_logged_in() && in_array( get_post_type(), array( 'admin_note' ) ) ) {
        wp_redirect( wp_login_url() ); 
        exit; 
    }
}


/*** WIDGETS >> SNIPPETS -- WIP! ***/

add_shortcode('cs_sidebars_xfer', 'cs_sidebars_xfer');
function cs_sidebars_xfer ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: cs_sidebars_xfer", $do_log );
    
    // Init vars
    $info = "";
	
	// Set up basic query args for snippets retrieval
    $wp_args = array(
		'post_type'		=> 'any',
		'post_status' => array( 'private', 'draft', 'publish', 'archive' ),
		'posts_per_page'=> -1,
        'fields'		=> 'ids',
        'orderby'		=> 'meta_value',
		'order'			=> 'ASC',
        'meta_key'		=> '_cs_replacements',
	);
	
	// Meta query
	$meta_query = array(
		'_cs_replacements' => array(
			'key' => '_cs_replacements',
			'compare' => '!=',
			'value' => '',
		),
	);
	$wp_args['meta_query'] = $meta_query;
	
	$arr_posts = new WP_Query( $wp_args );
	$posts = $arr_posts->posts;
    //$info .= "WP_Query run as follows:";
    //$info .= "<pre>args: ".print_r($wp_args, true)."</pre>";
    $info .= "[".count($posts)."] posts found.<br />";
    
    // Determine which snippets should be displayed for the post in question
	foreach ( $posts as $post_id ) {
		
		$info .= "post_id: ".$post_id."<br />";
		
		$cs = get_post_meta( $post_id, '_cs_replacements', true );
		$sidebar_id = get_post_meta( $post_id, 'sidebar_id', true );
		//$info .= "sidebar_id: <pre>".print_r($sidebar_id, true)."</pre>";
		if ( empty($sidebar_id) ) {
			$sidebar_id = "";
			$info .= "_cs_replacements:<br />";
			foreach ( $cs as $k => $v ) {
				$info .= "k: ".$k." => v: ".$v."<br />";
				$sidebar_id .= $v;
				if ( count($cs) > 1 ) { $sidebar_id .= ";"; } // will this ever happen? don't think so, but just in case...
			}
		}
		//$info .= "custom sidebar: <pre>".print_r($cs, true)."</pre>";
		$info .= "revised sidebar_id: ".$sidebar_id."<br />";
		
		// Update postmeta with revised sidebar_id value
		$update_key = 'sidebar_id';
		if ( update_field( $update_key, $sidebar_id, $post_id ) ) {
			$info .= "updated field: ".$update_key." for post_id: $post_id<br />";
		} else {
			$info .= "update FAILED for field: ".$update_key." for post_id: $post_id<br />";
		}
		
		$info .= "<br />";
		
	}
	
	return $info;

}

//
add_shortcode('snippets', 'get_snippets');
function get_snippets ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: show_snippets", $do_log );
    
    // Init vars
    $info = "";
	$ts_info = "";
	$post_snippets = array(); // this array will containing snippets matched for display on the given post
    
    $args = shortcode_atts( array(
    	'post_id' => null,
		'limit'   => -1,
        'run_updates'  => false,
        'dev' => false,
        'return' => 'info',
        'sidebar_id' => 'sidebar-1', // default
    ), $atts );
    
    // Extract
	extract( $args );
	
	//
	if ( $dev ) { 
		$info .= '<h2>Snippets -- WIP</h2>';
		//$info .= '<p>show : Show everywhere<br />hide : Hide everywhere<br />selected : Show widget on selected<br />notselected : Hide widget on selected</p>';
		$info .= "args: <pre>".print_r($args, true)."</pre>";
	}
    
    // Is this a single post of some kind, or another kind of page (e.g. taxonomy archive)
    
	// Get post_type, if applicable
	if ( is_singular() ) { // is_single
		$ts_info .= "is_singular<br />";
		if ( $post_id === null ) { $post_id = get_the_ID(); }
		$post_type = get_post_type( $post_id );
	} else {
		$ts_info .= "NOT is_singular<br />";
		//$post_type = get_post_type( get_queried_object_id() );
		$post_type = "N/A";
		//post_type_archive_title();
		if ( is_archive() ) {
			$ts_info .= "is_archive<br />";
			// what kind of archive?
			$object = get_queried_object();
			$object_class = get_class($object);
			$ts_info .= "object_class: ".$object_class."<br />";
			//$ts_info .= "get_queried_object: <pre>".print_r($object,true)."</pre>";
			if ( is_tax() ) {
				$tax = $object->taxonomy;
				$ts_info .= "tax: ".$tax."<br />";
				$tax_obj = get_taxonomy($tax);
				$tax_post_types = $tax_obj->object_type;
				$ts_info .= "tax_post_types: ".print_r($tax_post_types,true)."<br />";
				if ( count($tax_post_types) == 1 ) { $post_type = $tax_post_types[0]; }
			} else if ( is_post_type_archive() ) {
				$ts_info .= "is_post_type_archive: ";
				$post_archive_title = post_type_archive_title("",false);
				$ts_info .= $post_archive_title."<br />";
				if ( $object->name ) {
					$object_name = $object->name;
				} else {
					$object_name = strtolower($post_archive_title);
				}
				$ts_info .= "object_name: ".$object_name."<br />";
				$post_type = $object_name;
			} else {
				//$ts_info .= "get_the_archive_title: ".get_the_archive_title()."<br />";
				//$ts_info .= "post_type_archive_title: ".post_type_archive_title()."<br />";
			}
			// WIP
		}
	}
	$ts_info .= "post_type: $post_type<br />";
		
	// Check for custom sidebars 
	$cs = get_post_meta( $post_id, '_cs_replacements', true );
	//if ( $cs ) { $ts_info .= "custom sidebar: <pre>".print_r($cs, true)."</pre>"; }
	//e.g. Array( [sidebar-1] => cs-17 )
	
	// Set up basic query args for snippets retrieval
    $wp_args = array(
		'post_type'		=> 'snippet',
		'post_status'	=> 'publish',
		'posts_per_page'=> $limit,
        'fields'		=> 'ids',
        'orderby'		=> 'meta_value',
		'order'			=> 'ASC',
        'meta_key'		=> 'sidebar_sortnum',
	);
	
	// Meta query
	$meta_query = array(
		'relation' => 'AND',
		'snippet_display' => array(
			'key' => 'snippet_display',
			'value' => array('show', 'selected', 'notselected'),
			'compare' => 'IN',
		),
		/*'sidebar_id' => array(
			'key' => 'sidebar_id',
			'value' => $sidebar_id,
			'compare' => '=',
		),*/
		// The sidebar clause ensures that we don't get widgets from bottom-widgets, wp_inactive_widgets, etc.
		'sidebar_id' => array(
			'relation' => 'OR',
			array(
				'key' => 'sidebar_id',
				'value' => $sidebar_id,
				'compare' => '=',
			),
			array(
				'key' => 'sidebar_id',
				'value' => 'cs-',
				'compare' => 'LIKE',
			),
		),
	);
	$wp_args['meta_query'] = $meta_query;
	
	$arr_posts = new WP_Query( $wp_args );
	$snippets = $arr_posts->posts;
    //$ts_info .= "WP_Query run as follows:";
    //$ts_info .= "<pre>args: ".print_r($wp_args, true)."</pre>";
    $ts_info .= "[".count($snippets)."] snippets found.<br />";
    
    // Determine which snippets should be displayed for the post in question
	foreach ( $snippets as $snippet_id ) {
	
		$snippet_info = "";
		$snippet_logic_info = "";
		//
		$snippet_display = get_post_meta( $snippet_id, 'snippet_display', true );
		$sidebar_id = get_post_meta( $snippet_id, 'sidebar_id', true );
		$any_all = get_post_meta( $snippet_id, 'any_all', true );
		if ( empty($any_all) ) { $any_all = "any"; } // TODO: update_post_meta
		//
		$title = get_the_title( $snippet_id );
		$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
		//
		$snippet_status = "unknown"; // init
		$snippet_info .= '<div class="troubleshooting">';
		$snippet_info .= $title.' ['.$snippet_id.'/'.$widget_uid.'/'.$snippet_display;
		if ( $sidebar_id ) { $snippet_info .= '/'.$sidebar_id; }
		$snippet_info .= ']<br />';
		
		// Run updates?
		if ( $run_updates ) { $snippet_info .= '<div class="code">'.update_snippet_logic ( array( 'snippet_id' => $snippet_id ) ).'</div>'; }
		
		// TMP during transition?
		// TODO: add snippet status field?
		if ( $sidebar_id == "wp_inactive_widgets" ) {
		
			$snippet_status = "inactive";
			$snippet_logic_info .= "Snippet belongs to wp_inactive_widgets, i.e. status is inactive<br />";
			// TODO: remove from post_snippets array, if it was previously added...
			
		} else if ( $snippet_display == "show" ) {
		
			$post_snippets[] = $snippet_id; // add the item to the post_snippets array
			$snippet_status = "active";
			$snippet_logic_info .= "Snippet is set to show everywhere<br />";
			$snippet_logic_info .= "=> snippet_id added to post_snippets array<br />";
			
		} else {
		
			// Conditional display -- determine whether the given post should display this widget		
			$snippet_logic_info .= "<h3>Analysing display conditions...</h3>";
			
			// Set default snippet status for show on selected vs hide on selected
			if ( $snippet_display == "selected" ) {
				$snippet_status = "inactive";
			} else if ( $snippet_display == "notselected" ) {
				$post_snippets[] = $snippet_id; // add the item to the post_snippets array
				$snippet_status = "active";
			}
		
			$meta_keys = array( 'target_by_post', 'exclude_by_post', 'target_by_url', 'exclude_by_url', 'target_by_taxonomy', 'target_by_taxonomy_archive', 'target_by_post_type', 'target_by_post_type_archive', 'target_by_location' );
			foreach ( $meta_keys as $key ) {
			
				$$key = get_post_meta( $snippet_id, $key, true );
				//$snippet_info .= "key: $key => ".$$key."<br />";
				
				if ( !empty($$key) ) { //  && is_array($$key) && count($$key) == 1 && !empty($$key[0])
				
					$snippet_logic_info .= "key: $key =><br />";//$snippet_logic_info .= "key: $key => [".print_r($$key, true)."]<br />"; // ." [count: ".count($$key)."]"
					//$snippet_logic_info .= "[".print_r($$key, true)."]<br />";
					
					if ( ( $key == 'target_by_post_type' && $post_type != "N/A" ) || $key == 'target_by_post_type_archive') {
						
						if ( $key == 'target_by_post_type' ) {
							// This condition applies to singular posts only
							// Is the current page singular?
							if ( is_singular() ) {
								$snippet_logic_info .= "current page is_singular<br />";
							} else {
								$snippet_logic_info .= "current page NOT is_singular >> target_by_post_type does not apply<br /><br />";
								continue;
							}						
						} else {
							// This condition applies to archives only
							// Is the current page some kind of archive?
							if ( is_archive() ) {
								$snippet_logic_info .= "current page is_archive<br />";
							} else {
								$snippet_logic_info .= "current page NOT is_archive >> target_by_post_type_archive does not apply<br /><br />";
								continue;
							}
							if ( is_post_type_archive() ) { $snippet_logic_info .= "current page is_post_type_archive<br />"; }
						}
						
						// Is the given post of the matching type?
						$target_post_types = get_field($key, $snippet_id, false);
						//$snippet_logic_info .= "target_post_types: <pre>".print_r($target_post_types, true)."</pre><br />";
						//
						//
						// WIP: stored values are not bare post types, but rather e.g. [Array ( [0] => is_archive-event [1] => is_singular-person [2] => is_singular-product ) ]
						// => parse accordingly
						//
						//if ( $target_type && $post_type == $target_type ) {
						if ( is_array($target_post_types) && in_array($post_type, $target_post_types) ) {
							$snippet_logic_info .= "current post_type [$post_type] is in target post_types array<br />";//$snippet_logic_info .= "This post matches target post_type [$target_type].<br />";
							// TODO: figure out whether to do the any/all check now, or 
							// just add the id to the array and remove it later if "all" AND another condition requires exclusion?
							if ( $snippet_display == "selected" && $any_all == "any" ) {
								$post_snippets[] = $snippet_id; // add the item to the post_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> snippet_id added to post_snippets array<br />";
								//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							} else if ( $snippet_display == "notselected" ) {
								$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
								$snippet_status = "inactive";
								$snippet_logic_info .= "...but because snippet_display == notselected, that means it should not be shown<br />";
							}
						} else {
							$snippet_logic_info .= "This post does NOT match any of the array values.<br />";
							$snippet_logic_info .= "=> continue<br />";
						}
					
					} else if ( $key == 'target_by_post' || $key == 'exclude_by_post' ) {
					
						if ( is_singular() ) {
							$snippet_logic_info .= "current page is_singular<br />";
						} else {
							$snippet_logic_info .= "current page NOT is_singular >> $key does not apply<br /><br />";
							continue;
						}
							
						// Is the given post targetted or excluded?
						$target_posts = get_field($key, $snippet_id, false);
						if ( is_array($target_posts) && !empty($target_posts) && in_array($post_id, $target_posts) ) {
						
							// Post is in the target array
							$snippet_logic_info .= "This post is in the target_posts array<br />";
							// If it's for inclusion, add it to the array
							if ( $key == 'target_by_post' && $snippet_display == "selected" ) { //$any_all == "any" && 
								$post_snippets[] = $snippet_id; // add the item to the post_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> snippet_id added to post_snippets array (target_by_post/selected)<br />";
								//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							} else if ( $key == 'exclude_by_post' && $snippet_display == "notselected" ) { //$any_all == "any" && 
								$post_snippets[] = $snippet_id; // add the item to the post_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> snippet_id added to post_snippets array (exclude_by_post/notselected)<br />";
								//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							}
							// Snippet is inactive -- is in array, and either selected/excluded or notselected/targeted
							$snippet_logic_info .= "=> snippet inactive due to key: ".$key."/".$snippet_display."<br />";
							$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
							//if ( $snippet_display == "selected" ) { $snippet_status = "inactive"; } 
							$snippet_status = "inactive"; // ???
							break;
							
						} else {
						
							if ( empty($target_posts) ) { // redundant?
								$snippet_logic_info .= "The target_posts array is empty.<br />";
							} else {
								//???
								$snippet_logic_info .= "This post is NOT in the target_posts array.<br />";
								$snippet_logic_info .= "<!-- post_id: $post_id/target_posts: ".print_r($target_posts, true)." -->"; 
								if ( $snippet_display == "selected" ) {
									$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
									$snippet_status = "inactive";
								}
							}
							$snippet_logic_info .= "=> continue<br />";
						}
						
					} else if ( $key == 'target_by_url' || $key == 'exclude_by_url' ) {
					
						// Is the given post targetted or excluded?
						$target_urls = get_field($key, $snippet_id, false);
						
						// Loop through target urls looking for matches
						if ( is_array($target_urls) && !empty($target_urls) ) {
						
							//$snippet_logic_info .= "target_urls (<em>".$key."</em>): <br />";//$snippet_logic_info .= $key." target_urls: ".print_r($target_urls, true)."<br />";
						
							// Get current page path and/or slug
							global $wp;
							$current_url = home_url( add_query_arg( array(), $wp->request ) );
							//$snippet_logic_info .= "current_url: ".$current_url."<br />";
							$permalink = get_the_permalink($post_id);
							//$snippet_logic_info .= "permalink: ".$permalink."<br />";
							//if ( $permalink != $current_url ) { $current_url = $permalink; }
							$current_path = parse_url($current_url, PHP_URL_PATH);
							$snippet_logic_info .= "current_path: ".$current_path."<br />";
							
							foreach ( $target_urls as $k => $v ) {
								//$url = $v['url'];
								//$field = get_field_object('my_field');
								//$field_key = $field['key'];
								// WIP/TODO: get field key from key name?
								//$field_key = acf_maybe_get_field( 'field_name', false, false );
								if ( $key == 'target_by_url' ) {
									$field_key = 'field_6530630a97804';
								} else {
									$field_key = 'field_65306bc897806';
								}
								if ( isset($v[$field_key]) ) {
								
									$url = $v[$field_key];									
									if ( substr($url, -1) == "/" ) { $url = substr($url, 0, -1); } // Trim trailing slash, if any
									
									//$snippet_logic_info .= "target_url :: k: $k => v: ".print_r($v, true)."<br />";
									//$snippet_logic_info .= "target_url: ".$url."<br />";
									// compare url to current post path/slug
									$url_match = false;
									if ( $url == $current_path ) {
										// URL matches current path
										$snippet_logic_info .= "target_url: ".$url." matches current_path<br />";
										$url_match = true;										
									} else if ( strpos($url, '*') !== false ) {
										// Check for wildcard match
										$snippet_logic_info .= "** Wildcard url<br />";
										$snippet_logic_info .= "target_url: ".print_r($v, true)."<br />"; //$snippet_logic_info .= "target_url :: k: $k => v: ".print_r($v, true)."<br />";
										// Remove the asterisk to get the url_base
										$url_base = trim( substr($url, 0, strpos($url, '*')) );
										// clean up the bases so that the /s don't get in the way -- TODO: do this more efficiently, maybe with a custom trim fcn?
										if ( substr($url_base, 0, 1) == "/" ) { $url_base = substr($url_base, 1); } // Trim leading slash, if any
										if ( substr($url_base, -1) == "/" ) { $url_base = substr($url_base, 0, -1); } // Trim trailing slash, if any
										$snippet_logic_info .= "url_base: $url_base<br />";
										$current_path_base = $current_path;
										if ( substr($current_path_base, 0, 1) == "/" ) { $current_path_base = substr($current_path_base, 1); } // Trim leading slash, if any
										if ( substr($current_path_base, -1) == "/" ) { $current_path_base = substr($current_path_base, 0, -1); } // Trim trailing slash, if any
										$snippet_logic_info .= "current_path_base: $current_path_base<br />";
										// match to $current_path? true if current_path begins with url_base
										if ( substr($current_path_base, 0, strlen($url_base)) == $url_base ) {
											$url_match = true;
											$snippet_logic_info .= "current_path_base begins with wildcard url_base: $url_base<br />";
										}
									} else {
										//$snippet_logic_info .= "url $url does not match current_path $current_path<br />";
									}
								}
								if ( $url_match ) {
									if ( $key == 'target_by_url' && $snippet_display == "selected" ) {
										$post_snippets[] = $snippet_id; // add the item to the post_snippets array
										$snippet_status = "active";
										$snippet_logic_info .= "=> snippet_id added to post_snippets array (target_by_url/selected)<br />";
										//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
										$snippet_logic_info .= "=> BREAK<br />";
										break;
									} else if ( $key == 'exclude_by_url' && $snippet_display == "notselected" ) {
										$post_snippets[] = $snippet_id; // add the item to the post_snippets array
										$snippet_status = "active";
										$snippet_logic_info .= "=> snippet_id added to post_snippets array (exclude_by_url/notselected)<br />";
										//$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
										$snippet_logic_info .= "=> BREAK<br />";
										break;
									}
									// Snippet is inactive -- found in target urls, and either selected/excluded or notselected/targeted
									$snippet_logic_info .= "=> snippet inactive due to key: ".$key."/".$snippet_display."<br />";
									$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
									$snippet_status = "inactive";
									break;
								}
							} // foreach ( $target_urls as $k => $v ) {
							$snippet_logic_info .= "current_path not targeted<br />";
							
						} // if ( is_array($target_urls) && !empty($target_urls) ) {
						
					} else if ( $key == 'target_by_taxonomy' ) { //  || $key == 'widget_logic_taxonomy'
						
						$target_taxonomies = get_field($key, $snippet_id, false);
						//$snippet_logic_info .= "target_taxonomies: <pre>".print_r($target_taxonomies, true)."</pre><br />";
						$arr_post_taxonomies = get_post_taxonomies();
						//$snippet_logic_info .= "arr_post_taxonomies: <pre>".print_r($arr_post_taxonomies, true)."</pre><br />";
						
						// TODO: simplify this logic
						if ( match_terms( $target_taxonomies, $post_id ) ) { // ! empty( $target_taxonomies ) && 
							$snippet_logic_info .= "This post matches the target taxonomy terms<br />";
							if ( $snippet_display == "selected" ) {
								$post_snippets[] = $snippet_id; // add the item to the post_snippets array
								$snippet_status = "active";
							} else {
								$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
								$snippet_status = "inactive";
								$snippet_logic_info .= "...but because snippet_display == notselected, that means it should not be shown<br />";
							}
							$snippet_logic_info .= "=> BREAK<br />";
							break;
						} else {
							$snippet_logic_info .= "This post does NOT match the target taxonomy terms<br />";
							if ( $snippet_display == "selected" ) {
								$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
								$snippet_status = "inactive";
								if ( $any_all == "all" ) {
									$snippet_logic_info .= "=> BREAK<br />";
									break;								
								}
							} else if ( $snippet_display == "notselected" ) {
								// WIP
								//$post_snippets[] = $snippet_id; // add the item to the post_snippets array
								//$snippet_status = "active";
								//$snippet_logic_info .= "...but because snippet_display == notselected, that means it should be shown<br />";
							}
							// break?							
						}
					
					} else if ( $key == 'target_by_taxonomy_archive' ) {
					
						$target_taxonomies = get_field($key, $snippet_id, false);
						//$snippet_logic_info .= "target_taxonomies (archives): <pre>".print_r($target_taxonomies, true)."</pre><br />";
						
						if ( is_tax() ) {
							// If this is a taxonomy archive AND target_taxonomies are set, check for a match
							$snippet_logic_info .= "current page is_tax<br />";
							foreach ( $target_taxonomies as $taxonomy ) {
								if ( is_tax($taxonomy) ) {
									$snippet_logic_info .= "This post is_tax archive for target taxonomy: $taxonomy<br />";
									if ( $snippet_display == "selected" ) {
										$post_snippets[] = $snippet_id; // add the item to the post_snippets array
										$snippet_status = "active";
										$snippet_logic_info .= "=> BREAK<br />";
										break;
									} else {
										$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
										$snippet_status = "inactive";
										$snippet_logic_info .= "...but because snippet_display == notselected, that means it should NOT be shown<br />";
									}
								}
							}
						}
					
					} else if ( $key == 'target_by_location' ) {
						// Is the given post/page in the right site location?
						$target_locations = get_field($key, $snippet_id, false);
						$locations = array( 'is_home', 'is_single', 'is_page', 'is_archive', 'is_search', 'is_attachment', 'is_category', 'is_tag' ); // is_singular
						$current_locations = array();
						foreach ( $locations as $location ) {
							if ( $location() ) {
								//$snippet_logic_info .= "current page/post ".$location."<br />";
								$current_locations[] = $location;
							}
						}
						//
						$snippet_logic_info .= "target_locations: ".print_r($target_locations, true)."<br />";
						$snippet_logic_info .= "current_locations: ".print_r($current_locations, true)."<br />";
						if ( count($current_locations) == 1 ) { $current_location = $current_locations[0]; } else { $current_location = "multiple"; } // wip
						//
						//if ( match_locations( $target_locations, $post_id ) ) { // TODO? make match_locations fcn?
						if ( in_array($current_location, $target_locations) ) {
							//$post_snippets[] = $snippet_id; // add the item to the post_snippets array
							//$snippet_status = "active";
							$snippet_logic_info .= "This post matches the target_locations<br />";
							if ( $snippet_display == "selected" ) {
								$post_snippets[] = $snippet_id; // add the item to the post_snippets array
								$snippet_status = "active";
								$snippet_logic_info .= "=> BREAK<br />";
								break;
							} else {
								$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
								$snippet_status = "inactive";
								$snippet_logic_info .= "...but because snippet_display == notselected, that means it should NOT be shown<br />";
							}
						} else {
							$snippet_logic_info .= "This post does NOT match the target_locations<br />";
							if ( $snippet_display == "selected" ) {
								$post_snippets = array_diff($post_snippets, array($snippet_id)); // remove the item from the post_snippets array
								$snippet_status = "inactive";
							}
						}
						//
					} else {
						$snippet_logic_info .= "unmatched key: ".$key."<br />";
					}
					$snippet_logic_info .= "<br />";
					
				} else {
					$snippet_logic_info .= "key: $key => [empty]<br /><br />";
				}
			}
		
		}
		$snippet_logic_info .= "<hr />";
		$snippet_logic_info .= "snippet_status: ".$snippet_status;
		$snippet_info .= '<div class="code '.$snippet_status.'">'.$snippet_logic_info.'</div>';
		//
		$snippet_info .= '</div>'; // <div class="troubleshooting">
		$ts_info .= $snippet_info;
    }
    
    // Make sure there are no duplicates in the post_snippets array
    $post_snippets = array_unique($post_snippets); // SORT_REGULAR
	
	// If returning array of IDs, finish here
	if ( $return == "ids" ) { return $post_snippets; }
	
	// Compile info for the matching snippets for display
	foreach ( $post_snippets as $snippet_id ) {
		$title = get_the_title( $snippet_id );
		$snippet_content = get_the_content( null, false, $snippet_id );
		//$snippet_content = apply_filters('the_content', $snippet_content); // causes error -- instead use apply_shortcodes in sidebar.php
		//$snippet_content = do_shortcode($snippet_content); // causes error -- instead use apply_shortcodes in sidebar.php
		$snippet_content = wpautop($snippet_content);
		$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
		$sidebar_sortnum = get_post_meta( $snippet_id, 'sidebar_sortnum', true );
		//
		if ( $title == "Snippets" ) { continue; }
		//
		$info .= '<section id="snippet-'.$snippet_id.'" class="snippet widget widget_text widget_custom_html">';
		$info .= '<h2 class="widget-title">'.$title.'</h2>';
		$info .= '<div class="textwidget custom-html-widget">';
		$info .= $snippet_content;		
		if ( $sidebar_sortnum ) { $info .= '<!-- position: '.$sidebar_sortnum.'/widget_uid: $widget_uid -->'; }
		$info .= '</div>';
		$info .= '</section>';
	}
	// 
	if ( $dev ) { $info .= "<hr />".$ts_info; } else { $info .= '<div class="troubleshooting">'.$ts_info.'</div>';}
	
	return $info;
	
}

//
function get_snippet_by_widget_uid ( $widget_uid = null ) {

	$snippet_id = null;
	$info = "";
	
	if ( $widget_uid ) {
		$wp_args = array(
			'post_type'   => 'snippet',
			'post_status' => 'publish',
			'meta_key'    => 'widget_uid',
			'meta_value'  => $widget_uid,
			'fields'      => 'ids'
		);	
		$snippets = get_posts($wp_args);
	}
	
	if ( $snippets ) {
		//$info .= "snippets: <pre>".print_r($snippets,true)."</pre><hr />";
		// get existing post id
		if ( count($snippets) == 1 ) {
			$snippet_id = $snippets[0];
		} else if ( count($snippets) > 1 ) {
			//$info .= "More than one matching snippet!<br />";
			//$info .= "snippets: <pre>".print_r($snippets,true)."</pre><hr />";
		}
		//$info .= "snippet_id: ".$snippet_id."<br />";
	}
	
	return $snippet_id;

}

//
add_shortcode('widgets_to_snippets', 'convert_widgets_to_snippets');
function convert_widgets_to_snippets ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: convert_widgets_to_snippets", $do_log );
    
    $args = shortcode_atts( array(
		'limit'   => 1,
        'sidebar_id' => null,
        'widget_id'	=> null,
        'run_updates' => false,   
    ), $atts );
    
    // Extract
	extract( $args );
	
	$info = "";
	$i = 0;
	
	// Get wpstc_options data
	$arr_sidebars_widgets = get_option('sidebars_widgets'); // array of sidebars and their widgets (per sidebar id, e.g. "wp_inactive_widgets", "cs-11" )
	$widget_logic = get_option('widget_logic_options'); // widget display logic ( WidgetContext plugin -- being phased out )
	$cs_sidebars = get_option('cs_sidebars'); // contains name, id, description, before_widget, etc. for custom sidebars
	$text_widgets = get_option('widget_text');
	$html_widgets = get_option('widget_custom_html');
	//
	//$info .= "text_widgets: <pre>".print_r($text_widgets,true)."</pre><hr />";
	//$info .= "html_widgets: <pre>".print_r($html_widgets,true)."</pre><hr />";
	////////
	
	// Loop through sidebars and convert widgets to snippets
	
	$info .= "<h2>Sidebars/Widgets</h2>";
	//$info .= "<pre>arr_sidebars_widgets: ".print_r($arr_sidebars_widgets,true)."</pre><hr /><hr />";
	foreach ( $arr_sidebars_widgets as $sidebar => $widgets ) {
		
		// If we're handling a specific sidebar and this isn't it, move on to the next
		if ( $sidebar_id && $sidebar != $sidebar_id ) { continue; }
		
		// Skip wp_inactive_widgets -- tft
		//if ( $sidebar == 'wp_inactive_widgets' ) { continue; }
		//if ( $sidebar == "wp_inactive_widgets" || $sidebar == "mega-menu" || $sidebar == "array_version" || empty($widgets) ) { continue; }
		
		// Get the registered sidebar info -- name, id, description, before_widget, etc.
		$sidebar_name = null; // init
		$sidebar_info = wp_get_sidebar( $sidebar );
		if ( $sidebar_info ) { $sidebar_name = $sidebar_info['name']; }
		
		// Is this a Custom Sidebar?
		if ( strpos($sidebar, 'cs-') !== false ) {
			$custom_sidebar = true;
			if ( $sidebar == "cs-29" ) { $info .= "Sermons sidebar... skip it for now<br />"; continue; } // Sermons sidebar. Special case
		} else {
			$custom_sidebar = false;
		}
		
		$info .= "<h3>sidebar: ";
		$info .= $sidebar;
		if ( $sidebar_name ) { $info .= ' => "'.$sidebar_name.'"'; }
		if ( $custom_sidebar ) { $info .= " [cs]"; }
		//$info .= " => sidebar_info: <pre>".print_r($sidebar_info,true)."</pre>";
		$info .= "</h3>";
		
		//$info .= "sidebar: ".$sidebar." => widgets: <pre>".print_r($widgets,true)."</pre><hr />";
		//$info .= "widgets: <pre>".print_r($widgets,true)."</pre><hr />";
		
		$info .= '<div class="code">';
		
		// Loop through widgets and create corresponding snippet records
		if ( is_array($widgets) ) {
		
			$info .= "<h4>Widgets</h4>";
			foreach ( $widgets as $i => $widget_uid ) {
			
				$info .= "<h5>widget ".$i.": ".$widget_uid."</h5>";

				// Separate type and id from widget_uid
				$wtype = substr($widget_uid, 0, strpos($widget_uid, "-"));
				$wid = substr($widget_uid, strpos($widget_uid, "-") + 1);
				$info .= "wtype: ".$wtype."/"."wid: ".$wid."<br />";
				// Widget type?
				if ( $wtype == "text" && isset($text_widgets[$wid]) ) {
					$widget = $text_widgets[$wid];
					$info .= "Matching text widget found.<br />";
				} else if ( $wtype == "custom_html" && isset($html_widgets[$wid]) ) {
					$widget = $html_widgets[$wid];
					$info .= "Matching custom_html widget found.<br />";
				} else {
					$widget = null; // tft
					$info .= "This is not a standard WP text/custom_html widget<br />";
				}
					
				// If a widget was found, gather the info needed to create/update the corresponding snippet
				if ( $widget ) {
					
					$postarr = array();
					$meta_input = array();
					$conditions = array();
				
					// Does a snippet already exist based on this widget?
					$snippet_id = get_snippet_by_widget_uid ( $widget_uid );
					if ( $snippet_id ) {
						$postarr['ID'] = $snippet_id;
						$info .= "<h5>&rarr; snippet_id: ".$snippet_id."/".get_the_title($snippet_id)."</h5>";
					} else {
						$info .= "No existing snippet found for widget_uid: ".$widget_uid."<br />";
					}
				
					// Array fields for text widgets: title, text, filter, visual, csb_visibility, csb_clone...
					// TODO: check if fields are same for e.g. custom_html
		
					// Title
					if ( isset($widget['title']) && !empty($widget['title']) ) {
						$snippet_title = $widget['title'];
					} else {
						$snippet_title = $widget_uid;
					}
					$info .= "title: ".$snippet_title."<br />";
					
					// Content
					if ( isset($widget['text']) ) {
						$snippet_content = $widget['text'];
					} else if ( isset($widget['content']) ) {
						$snippet_content = $widget['content'];
					} else {
						$snippet_content = null; // ???
					}
					
					// WIP: find STC absolute hyperlinks in snippet content and relativize them (i.e. more clean up after AG...)
					// e.g. <a href="https://stcnyclive.wpengine.com/theology/">Gain understanding by attending classes</a>
					if ( strpos($snippet_content, 'http') !== false ) {
						$info .= "** Absolute urls in snippet_content => relativize them<br />";
						//$info = str_replace($search,$replace,$info);
						//$snippet_content = str_replace('https://stcnyclive.wpengine.com/','/',$snippet_content);
						$snippet_content = str_replace('https://stcnyclive.wpengine.com/','/',$snippet_content);						
					}
					
					
					// WIP: find if widget is included in one or more sidebars --> get sidebar_id(s)
					//$widget_sidebar_id = get_sidebar_id($widget_uid);
					
					// TODO: check to see if snippet already exists with matching uid
					// If no match, create new snippet post record with title and text as above
					// If match, check for changes?
					
					// If title and content are set, then prep to save widget as snippet
					if ( $snippet_title && $snippet_content ) {
						//
						$postarr['post_title'] = wp_strip_all_tags( $snippet_title );
						$postarr['post_content'] = $snippet_content;
						$postarr['post_type'] = 'snippet';
						$postarr['post_status'] = 'publish';
						$postarr['post_author'] = 1; // get_current_user_id()
						// Set up preliminary meta_input
						$meta_input['widget_type'] = $wtype;
						$meta_input['widget_id'] = $wid;
						$meta_input['widget_uid'] = $widget_uid;
						if ( $sidebar ) {
							$meta_input['sidebar_id'] = $sidebar;
							$meta_input['sidebar_sortnum'] = $i;
						}
						
						// Proceed to processing widget display logic
						
						/*
						// Get existing value for sidebar_id field, if any
						$sidebars = get_post_meta( $snippet_id, 'sidebar_id', true );
						$info .= "snippet sidebars: ".$sidebars."<br />";
						$sidebars_revised = "";
						if ( empty($sidebars) ) {
							$sidebars_revised = $sidebar;
						} else if ( $sidebars != $sidebar ) {
							$sidebars_revised = $sidebars."; ".$sidebar;
						}
						$info .= "snippet sidebars_revised: ".$sidebars_revised."<br />";
						*/
						
						// Is this a Custom Sidebar?
						if ( $custom_sidebar ) {
						
							// This may be overridden later by the widget logic for this particular widget, 
							// ... but if not, default to showing it only on selected posts which were set to use this custom sidebar
							$meta_input['snippet_display'] = "selected";
							
							// NB/WIP only CS with sidebar location rules appears to be Sermons Sidebar => display on all individual sermon posts and sermon post archives

							// Get array of ids for posts using this custom sidebar
							global $wpdb;
	
							$sql = "SELECT `post_id` 
									FROM $wpdb->postmeta
									WHERE `meta_key` = '_cs_replacements'
									AND `meta_value` LIKE '%".'"'.$sidebar.'"'."%'";

							$arr_objs = $wpdb->get_results($sql);
							$cs_post_ids = array_column($arr_objs, 'post_id');
							sort($cs_post_ids); // Sort the array -- TODO: sort instead by post title
							if ( count($cs_post_ids) > 0 ) {
							
								$info .= count($cs_post_ids)." posts using this sidebar:<br />";
								//$info .= count($cs_post_ids)." posts using this sidebar: ".print_r($cs_post_ids,true)."<br />";
								
								//
								// WIP generalized fcn to determine revised value
								$updates = get_updated_field_value( $snippet_id, 'cs_post_ids', $cs_post_ids, 'array' ); // post_id, key, new_value, type
								$info .= $updates['info'];
								$updated_field_value = $updates['updated_value'];
								//
								if ( $updates && count($updated_field_value) > 0 ) {
									$info .= count($updated_field_value)." items in updated_field_value array<br />";
									//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
									$meta_input['cs_post_ids'] = serialize($updated_field_value);
								}
								
							} else {
								$info .= "There are no posts using this custom sidebar<br />";
								$meta_input['snippet_display'] = "hide";
							}
							$info .= "<hr />";
							
						} // END special handling for custom sidebars
					
						// Get widget logic -- WIP
						if ( isset($widget_logic[$widget_uid]) ) {
							$info .= "... found widget logic ...<br />";
							//$info .= "logic: <pre>".print_r($widget_logic[$widget_uid],true)."</pre><br />";
							$conditions = $widget_logic[$widget_uid];
						}
					
						// Loop through the conditions and prepare to save them to the snippet ACF fields, as applicable
						// NB: this is only step one; after the snippet has been created/updated,
						// .. we'll update the snippet logic and translate the old widget logic to fit the new ACF fields
						foreach ( $conditions as $condition => $subconditions ) {
					
							$condition_info = "";
							$subs_info = "";
							$subs_empty = true;
							$check_wordcount = false;
							//
							//$info .= "condition: ".$condition."<br />";
							//$info .= "subconditions: <br />";
							if ( $condition == 'incexc' ) {
								
								if ( !$custom_sidebar || $subconditions['condition'] != "show" ) {
									$meta_input['snippet_display'] = $subconditions['condition'];
								}
			
							} else if ( $condition == "url" ) {
		
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
								if ( isset($subconditions['urls']) && !empty($subconditions['urls']) ) {					
									$meta_input['widget_logic_target_by_url'] = $subconditions['urls']; // backup/transitional field
								}		
				
							} else if ( $condition == "urls_invert" ) {
		
								if ( isset($subconditions['urls_invert']) && !empty($subconditions['urls_invert']) ) {
									$meta_input['widget_logic_exclude_by_url'] = $subconditions['urls_invert']; // backup/transitional field					
								}
			
							} else if ( $condition == "location" || $condition == "custom_post_types_taxonomies" ) {
			
								$info .= "condition: ".$condition."<br />";
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
			
								// Init values array
								$values = array();
			
								// location => array (is_front_page, is_home, etc) --> target_by_location
								// custom_post_types_taxonomies => array of post types and custom taxonomy archives etc to target (or exclude)
			
								// Save only array elements where $v == 1
								foreach ( $subconditions as $k => $v ) {
									//$info .= "k: ".$k." => v: ".$v."<br />";
									if ( $v == 1 ) {
										$info .= "k: ".$k." => v: ".$v."<br />";
										$values[$k] = $v;
									}
								}
			
								// Determine the appropriate meta_key
								if ( $condition == "location" ) { $meta_key = 'widget_logic_location'; } else { $meta_key = 'widget_logic_custom_post_types_taxonomies'; }
			
								// Add the value(s) to the meta_input array
								if ( !empty($values) ) { $meta_input[$meta_key] = serialize($values); }
		
							} else if ( $condition == "taxonomy" ) {
			
								$info .= "condition: ".$condition."<br />";
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
			
								if ( isset($subconditions['taxonomies']) ) { 
									$taxonomies = $subconditions['taxonomies'];
									$info .= "taxonomies: ".$taxonomies."<br />";
									$meta_input['widget_logic_taxonomy'] = $taxonomies; // TODO: figure out why this isn't working
									$meta_input['target_by_taxonomy'] = $taxonomies;
								}
		
							} else if ( $condition == "word_count" ) {
		
								$info .= "condition: ".$condition."<br />";
								// WIP
								//$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
		
							} else if ( is_array($subconditions) && !empty($subconditions) ) {
								$info .= "condition: ".$condition."<br />";
								if ( count($subconditions) == 1 && empty($subconditions[0]) ) {
									//$info .= "single empty subcondition<br />";
								} else {
									$info .= "subconditions: <pre>".print_r($subconditions,true)."</pre><br />";
									//$info .= count($subconditions)." subconditions<br />";
								}
								/*foreach ( $subconditions as $k => $v ) {
									//$info .= "k: ".$k." => v: ".$v."<br />";
								}*/
							} else {
								$info .= "condition: ".$condition."<br />";
								$info .= $subconditions." [not an array]<br />";
								//$meta_input[$condition] = $subconditions;
							}
							if ( !$subs_empty ) {
								//$condition_info .= $subs_info;
								//$condition_info .= $condition;
							}
							//
							//$info .= $condition_info;
		
						} // END foreach ( $conditions as $condition => $subconditions )
		
						// WIP
						$meta_input['widget_logic'] = print_r($conditions, true);
						
						// Init action var
						$action = null;
						
						// Finish setting up the post array for update/insert							
						$postarr['meta_input'] = $meta_input;
						
						//$info .= "snippet postarr: <pre>".print_r($postarr,true)."</pre>";
						if ( $snippet_id ) { //if ( isset($postarr['ID']) ) {
							$info .= "&rarr; About to update existing snippet [$snippet_id]<br />";
							// Update existing snippet
							$snippet_id = wp_update_post($postarr);
							if ( !is_wp_error($snippet_id) ) { $action = "updated"; }
						} else {
							$info .= "&rarr; About to create a new snippet<br />";
							// Insert the post into the database
							$snippet_id = wp_insert_post($postarr);
							if ( !is_wp_error($snippet_id) ) { $action = "inserted"; }
						}
						// Handle errors
						if ( is_wp_error($snippet_id) ) {
							//$info .= $snippet_id->get_error_message();
							$errors = $snippet_id->get_error_messages();
							foreach ($errors as $error) {
								$info .= $error;
							}
						}
		
						//
						if ( $action && $snippet_id ) {
							$info .= "&rarr;&rarr; Success! -- snippet record ".$action." [".$snippet_id."]<br />";				
							// Update snippet logic
							$info .= "&rarr;&rarr; update_snippet_logic<br />";
							$info .= update_snippet_logic ( array( 'snippet_id' => $snippet_id ) ); //$info .= '<div class="code">'.update_snippet_logic ( $snippet_id ).'</div>';
						} else {
							$info .= "&rarr;&rarr; No action<br />";
							//$info .= "snippet postarr: <pre>".print_r($postarr,true)."</pre>";
						}
		
					}
				
				}
				
				//if ( $i > $limit ) { break; } // tft
				
				$info .= "<hr />";
			} // foreach ( $widgets...
			
		}
		
		//...
		$info .= '</div>';
	}
	
	////////
	
	return $info;
	
} // END function convert_widgets_to_snippets

// Purpose: update new fields from legacy fields, e.g. target_by_url => target_by_post
add_shortcode('update_snippet_logic', 'update_snippet_logic');
function update_snippet_logic ( $atts = [] ) { //function update_snippet_logic ( $snippet_id = null ) {

	// TS/logging setup
    $do_ts = true; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    $args = shortcode_atts( array(
        'snippet_id' => null,
    ), $atts );
    
    // Extract
	extract( $args );
	
    // Init vars
    $info = "";
	$ts_info = "";
	
	if ( $snippet_id === null ) { return false; }
	
	//if ( $snippet_id === null ) { $snippet_id = get_the_ID(); }
	//$snippet = get_post ( $snippet_id );
	//$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
	
	//
	$info .= '<div class="code">';
	$info .= ">> update_snippet_logic for snippet_id: $snippet_id<br />";
	//$info .= "widget_uid: $widget_uid<br />";
	
	// Get snippet logic
	// -- WIP
	$meta_keys = array( 'cs_post_ids', 'widget_logic_target_by_url', 'target_by_url', 'exclude_by_url', 'widget_logic_exclude_by_url', 'target_by_post_type', 'widget_logic_custom_post_types_taxonomies', 'target_by_location', 'widget_logic_location', 'widget_logic_taxonomy', 'target_by_taxonomy' );
	//$meta_keys = array( 'target_by_url_txt', 'exclude_by_url_txt', 'target_by_taxonomy', 'target_by_post_type', 'target_by_location' );
	foreach ( $meta_keys as $key ) {
	
		$$key = get_field( $key, $snippet_id );
		//$$key = get_post_meta( $snippet_id, $key, true );
		$key_ts_info = "";
		$key_ts_info .= "<strong>key: $key</strong><br />";
		//$key_ts_info .= "key: $key => ".$$key."<br />";
		//$key_ts_info .= "=> <pre>".print_r($$key, true)."</pre>";
		
		// If the key has a corresponding value, then proceed to process that value
		if ( !empty($$key) ) {
		
			//$key_ts_info .= "<strong>key: $key</strong><br />";
			//$key_ts_info .= "=> <pre>".print_r($$key, true)."</pre>"; // ." [count: ".count($$key)."]"
			
			// Unserialize as needed (legacy fields only, yes? -- perhaps consolidate with below)
			if ( !is_array($$key) && strpos($$key, '{') !== false ) {
				$key_ts_info .= "key: $key => ";//$key_ts_info .= "key: $key => ".$$key."<br />";
				$key_ts_info .= "unserialize...<br >";
				$$key = unserialize($$key);
				//$key_ts_info .= "unserialized key: $key => ".print_r($$key,true)."<br />";
			}	
				
			// Clean up legacy field values
			if ( !is_array($$key) && strpos($key, 'widget_logic_') !== false ) {
			
				$key_ts_info .= "widget_logic field; not array => clean up, update, and explode<br />";
				// Replace multiple (one or more) line breaks with a single one.
				$$key = preg_replace("/[\r\n]+/", "\n", $$key);
				// Update the legacy field with the cleaned-up version
				update_field( $key, $$key, $snippet_id );
			
				// Turn the text into an array of conditions
				$conditions = explode("\n",$$key);
						
			} else {
			
				$conditions = $$key;
				
			}
			
			//
			//if ( !is_array($conditions) ) { continue; }
			// TODO: fine-tune sorting in case of multidimensional or associative arrays(?)
			// TODO: run the following ONLY for one-dimensional non-associative arrays 
			//if ( is_array($conditions) ) { sort($conditions); }
			
			//$key_ts_info .= count($conditions)." condition(s)<br />";
			//$key_ts_info .= "conditions: <pre>".print_r($conditions, true)."</pre>";
			
			// TODO: streamline! get rid of code redundancy -- WIP 231027
			
			if ( $key == 'cs_post_ids' ) {
			
				if ( is_array($$key) ) {
					$$key = array_unique($$key);
					$key_ts_info .= count($$key)." $key<br />";
				} else {
					$key_ts_info .= "$key => ".print_r($$key,true)."<br />";
					continue; // can't do much with a non-array... wip
				}
				
				$matched_posts = array();
				
				$key_ts_info .= "-----------<br />";
				foreach ( $conditions as $condition ) {				
					$p_id = intval($condition);
					//$key_ts_info .= "p_id: ".$p_id."<br />";
					// Check to see if p_id is a valid post id
					$post = get_post( $p_id );
					if ( $post ) {
						$recurrence_id = get_post_meta( $p_id, '_recurrence_id', true );
						if ( $recurrence_id ) {
							$key_ts_info .= "p_id: ".$p_id."";
							$key_ts_info .= '&rarr; RID: <span class="nb">'.$recurrence_id.'</span><br />';
							$matched_posts[] = $recurrence_id; // WIP
						} else {
							$key_ts_info .= "p_id: ".$p_id." (not attached to a recurring event)<br />";
							$matched_posts[] = $p_id;
							//$key_ts_info .= "postmeta: ".print_r(get_post_meta($id), true)."<br />";
						}
					} else {
						$key_ts_info .= "NO POST FOUND for p_id: ".$p_id."";
					}		
				}
				$matched_posts = array_unique($matched_posts);
				$key_ts_info .= count($matched_posts)." matched_posts<br />";
				
				/*
				foreach ( $cs_post_ids as $x => $id ) {
							
					$post_info = $x.".) ".get_the_title($id)." [$id]";
				
					// Get post status -- we're only interested published posts
					$post_status = get_post_status( $id );
					if ( $post_status != "publish" ) { $post_info .= " <em>*** ".$post_status." ***</em>"; }
					//$post_info .= "<br />";
				
					// Is this an attached instance of a recurring event?
					$recurrence_id = get_post_meta( $id, '_recurrence_id', true );
					if ( $recurrence_id ) {
						$post_info .= '&rarr; RID: <span class="nb">'.$recurrence_id.'</span>';
						// Remove individual instance id from ids array and save parent id instead? or.... WIP
					} else {
						//$post_info .= "postmeta: ".print_r(get_post_meta($id), true)."<br />";
					}
					$post_info .= "<br />";
					$info .= $post_info;
				}
				$info .= "<hr />";
				*/
				
				// Save the matched posts to the 'cs_post_ids' snippet field -- this is largely for backup
				$update_key = $key;
				$updated_field_value = $matched_posts;
				//$updates = get_updated_field_value( $snippet_id, $update_key, $update_value, 'array' ); // post_id, key, new_value, type
				//$key_ts_info .= $updates['info'];
				//$updated_field_value = $updates['updated_value'];
				//if ( $updates && count($updated_field_value) > 0 ) {
				if ( count($updated_field_value) > 0 ) {
					$key_ts_info .= "about to update field '$update_key'<br />";
					$key_ts_info .= count($updated_field_value)." items in updated_field_value array<br />";
					//$key_ts_info .= count($updated_field_value)." items in updated_field_value array<br />";
					//$key_ts_info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
					//$key_ts_info .= "about to update field '$update_key' with value(s): ".print_r($updated_field_value, true)."<br />";
					$key_ts_info .= "[[Updates only for 50 records or fewer]]<br />";
					if ( count($updated_field_value) < 50 ) { // TMP limit
						if ( update_field( $key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					}
				} else {
					//$updated_field_value  = $matched_posts; // for purposes of secondary and tertiary updates
				}
				
				// Also save those same matched_posts/updated_field_value to the target_by_post field -- this field determines actual snippet display
				$key_ts_info .= "<br /><strong>Preparing for secondary snippet updates...</strong><br /><br />";
				$update_key = 'target_by_post';
				$update_value = $updated_field_value;
				$secondary_updates = get_updated_field_value( $snippet_id, $update_key, $update_value, 'array' ); // post_id, key, new_value, type
				$key_ts_info .= $secondary_updates['info'];
				$secondary_updated_field_value = $secondary_updates['updated_value'];
				if ( $secondary_updates && count($secondary_updated_field_value) > 0 ) {
					$key_ts_info .= "about to update field '$update_key'<br />";
					$key_ts_info .= count($secondary_updated_field_value)." items in secondary_updated_field_value array<br />";
					//$key_ts_info .= "=> <pre>".print_r($secondary_updated_field_value, true)."</pre>";
					//$key_ts_info .= "about to update field '$update_key' with value(s): ".print_r($secondary_updated_field_value, true)."<br />";
					$key_ts_info .= "[[Updates only for 50 records or fewer]]<br />";
					if ( count($secondary_updated_field_value) < 50 ) { // TMP limit
						if ( update_field( $update_key, $secondary_updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					}
				}
				
				//
				$sidebar_id = get_post_meta( $snippet_id, 'sidebar_id', true );
				// Is this a Custom Sidebar? If so, update other snippets accordingly
				if ( strpos($sidebar_id, 'cs-') !== false ) {
				
					// Update other snippets to prevent display of these cs_post_ids
					// Update matching snippets with arr_ids...
					
					// TODO: figure out how to edit widget logic for remaining widgets (not snippets) to add cs- posts to list of exclusions
					// 
	
					// WIP 231113
					$key_ts_info .= "<br /><strong>Preparing for tertiary snippet updates...</strong><br /><br />";
					// add cs_posts_ids to widgets that are set to snippet_display == notselected
					// ... otherwise sidebar-1 widgets like News, Events will be displayed
					// ... AND add/merge into exclude_by_post field for snippet_display == selected
					$wp_args = array(
						'post_type'   => 'snippet',
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'fields'      => 'ids',
					);
					$meta_query = array(
						'relation' => 'AND',
						'snippet_display' => array(
							'key' => 'snippet_display',
							'value' => array('selected', 'notselected'),
							'compare' => 'IN',
						),
						'sidebar_id' => array(
							'key' => 'sidebar_id',
							'value' => 'cs-',
							'compare' => 'NOT LIKE',
						),
					);
					$wp_args['meta_query'] = $meta_query;
					$snippets = get_posts($wp_args);
					if ( $snippets ) {
						$key_ts_info .= "Found ".count($snippets)." snippets eligible for tertiary updates based on CS data<br /><hr /><br />";
						//$key_ts_info .= "Found ".count($snippets)." snippets for args: ";
						//$key_ts_info .= "=> <pre>".print_r($wp_args, true)."</pre>";
						foreach ( $snippets as $i => $snip_id ) {
							$snippet_display = get_field('snippet_display', $snip_id, false);
							$sidebar_id = get_field('sidebar_id', $snip_id, false);
							if ( $snippet_display == "selected" ) {
								$update_key = 'exclude_by_post';
							} else {
								$update_key = 'cs_post_ids';
							}
							$key_ts_info .= $i.") id: ".$snip_id." [sidebar_id: ".$sidebar_id."/snippet_display: ".$snippet_display."/update_key: ".$update_key."]<br />";
							//
							$update_value = $updated_field_value;
							$tertiary_updates = get_updated_field_value( $snip_id, $update_key, $update_value, 'array' ); // post_id, key, new_value, type
							$key_ts_info .= $tertiary_updates['info'];
							$tertiary_updated_field_value = $tertiary_updates['updated_value'];
							if ( $tertiary_updates && count($tertiary_updated_field_value) > 0 ) {
								$key_ts_info .= "about to update field '$update_key' for snip_id: $snip_id<br />";
								$key_ts_info .= count($tertiary_updated_field_value)." items in tertiary_updated_field_value array<br />";
								//
								$key_ts_info .= "[[Updates tmp disabled]]<br />";
								if ( $update_key == 'cs_post_ids' ) { // text field, not relationship => save as string								
									$tertiary_updated_field_value = serialize($tertiary_updated_field_value);
									//$key_ts_info .= "serialized tertiary_updated_field_value: ".print_r($tertiary_updated_field_value,true)."<br />";
								}
								//
								//$key_ts_info .= "=> <pre>".print_r($tertiary_updated_field_value, true)."</pre>";
								//$key_ts_info .= "about to update field '$key' with value(s): ".print_r($tertiary_updated_field_value, true)."<br />";
								/*if ( update_field( $update_key, $tertiary_updated_field_value, $snip_id ) ) {
									$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snip_id<br />";
								} else {
									$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snip_id<br />";
								}*/
							}
							$key_ts_info .= "<br />";
						}
					}
					
				}
					
			} else if ( $key == 'widget_logic_target_by_url' || $key == 'target_by_url' || $key == 'widget_logic_exclude_by_url' || $key == 'exclude_by_url' ) {
				
				// Init arrays
				$matched_posts = array();
				$repeater_additions = array();
				$repeater_removals = array();				
				//
				if ( $key == 'widget_logic_target_by_url' || $key == 'target_by_url' ) {
					$target_key = 'target_by_post';
					$repeater_key = 'target_by_url';
				} else if ( $key == 'widget_logic_exclude_by_url' || $key == 'exclude_by_url' ) {
					$target_key = 'exclude_by_post';
					$repeater_key = 'exclude_by_url';
				}
				//
				$repeater_rows = get_field( $repeater_key, $snippet_id );
				if ( empty($repeater_rows) ) { 
					$repeater_rows = array();
					$repeater_values = array();
				} else {
					// Sort the existing repeater_rows and save the sorted array
					$repeater_values = array_column($repeater_rows, 'url');
					//$key_ts_info .= "repeater_rows repeater_values: ".print_r($repeater_rows, true)."<br />";
					array_multisort($repeater_values, SORT_ASC, $repeater_rows);
					update_field( $repeater_key, $repeater_rows, $snippet_id );
				}
				//$key_ts_info .= "existing repeater_rows: ".print_r($repeater_rows, true)."<br />";
				//
				// TODO: (re-)check repeater_rows to see if updates are needed from target_by_url => target_by_post
				//
				$key_ts_info .= "-----------<br />";
				foreach ( $conditions as $condition ) {
					//
					if ( empty($condition) ) { continue; }
					$condition_info = "";
					$url = null;
					$slug = null;
					$post_type = null;
					$matched_post_id = null;
					//
					if ( is_array($condition) ) {
						if ( isset($condition['url']) ) {
							$url = $condition['url'];
							$condition_info .= "url: ".$url."<br />";
						} else {
							$condition_info .= "condition: <pre>".print_r($condition, true)."</pre>";
							continue;
						}					
					} /*else if ( gettype($condition) == "string" ) {
						$p_id = intval($condition);
						$condition_info .= "p_id: ".$p_id."<br />";
						// Check to see if p_id is a valid post id
						$post = get_post( $p_id );
						if ( $post ) { $matched_post_id = $p_id; }
					} */else {
						$condition_info .= "condition: ".$condition." [".gettype($condition)."]<br />";
						$url = $condition;
					}					
					//
					if ( $url ) {
					
						$url_bk = $url; // in case we're relativizing and post is matched, so we can remove the url from the repeater array
						
						// Parse the url
						$hostname = parse_url($url, PHP_URL_HOST);
						if ( !empty($hostname) ) { $condition_info .= "&rarr; hostname: $hostname<br />"; }
						//
						$path = parse_url($url, PHP_URL_PATH);
						//$key_ts_info .= "&rarr; path: $path<br />";
						$querystring = parse_url($url, PHP_URL_QUERY);
						if ( !empty($querystring) ) {
							$querystring = "?".$querystring;
							$condition_info .= "&rarr; querystring: $querystring<br />";
						}
						
						// Does the URL contain a wildcard character? i.e. asterisk?
						// e.g. /shop*, events/*
						if ( strpos($url, '*') !== false ) {
							$wildcard = true;
							$condition_info .= "** Wildcard url => add to repeaters; can't be matched<br />";
							$repeater_additions[] = $url;
						} else {
							$wildcard = false;
						}
						
						// Is this an STC absolute URL? If so, remove the first bit
						if ( substr($url, 0, 4) == "http" ) {
							$condition_info .= "** Absolute url => relativize it [$url]<br />";
							//$url_bits = parse_url($url);
							// If this is an STC url, remove everything except the path
							if ( preg_match("/stc|saint/", $hostname) ) {
								$url = $path.$querystring;
								$condition_info .= "&rarr; revised url: $url<br />";
								$condition_info .= "&rarr; remove old: $url_bk<br />";
								$repeater_removals[] = $url_bk; // Remove the absolute URL
							}
						}
						//
						$date_validation_regex = "/\/[0-9]{4}\/[0-9]{1,2}\/[0-9]{1,2}/"; 
						if ( substr($url, 5) == "event" || substr($url, 1, 5) == "event" ) {
							$post_type = "event";
						} else if ( preg_match($date_validation_regex, $url) ) {
							$post_type = "post";
						}
						//
						if ( $post_type ) {
							// Extract slug from path
							// First, trim trailing slash, if any
							if ( substr($path, -1) == "/" ) { $path = substr($path, 0, -1); }
							$url_bits = explode("/",$path); // The last bit is slug
							$slug = end($url_bits);
							//$condition_info .= "url_bits: ".print_r($url_bits, true)."<br />";
							$condition_info .= "$post_type slug: $slug<br />";
						} else {
							$condition_info .= "path: $path<br />";
							//$condition_info .= "url: $url<br />";
						
						}
						// Look for matching post
						if ( $wildcard == false ) {
							if ( $slug && $post_type ) {
								$condition_info .= "get_page_by_path with slug: $slug; post_type: $post_type<br />";
								$matched_post = get_page_by_path($slug, OBJECT, $post_type);
							} else {
								$condition_info .= "get_page_by_path with path: $path<br />";
								$matched_post = get_page_by_path($path);
							}
							if ( $matched_post ) { $matched_post_id = $matched_post->ID; }
						} else {
							$condition_info .= "wildcard url => no attempt made to match post<br />";
						}
						
					}
					
					//
					if ( $matched_post_id ) {
						$condition_info .= "&rarr; matching post found with id: $matched_post_id<br />";
						$matched_posts[] = $matched_post_id;
						if ( $url ) {
							$condition_info .= "&rarr; remove from repeater_rows array: $url<br />";
							$repeater_removals[] = $url;
						}						
					} else {
						$condition_info .= "&rarr; NO matching post found<br />";
						if ( $url ) {
							$tmp_urls = array_column($repeater_rows, 'url');
							// Is the url already to be found in the repeater_rows
							$match_key = array_search($url, $tmp_urls); //$match_key = array_search($url, array_column($repeater_rows, 'url')); // not working -- why not?!?
							if ( $match_key ) {
								$condition_info .= "&rarr; The url '".$url."' is already in repeater_rows array at position ".$match_key."<br />";
							} else {
								// TODO: check to see if the url is already in the array!
								$repeater_additions[] = $url;
								$condition_info .= "&rarr; No match_key &rarr; Added url '".$url."' to repeater_additions array<br />";
							}
						}
					}
					$condition_info .= "---<br />";
					//
					$key_ts_info .= $condition_info;
					
				} // END foreach $conditions
				$key_ts_info .= "<hr />";
				
				// Save the matched posts to the snippet field
				$updates = get_updated_field_value( $snippet_id, $target_key, $matched_posts, 'array' ); // post_id, key, new_value, type
				$key_ts_info .= $updates['info'];
				$updated_field_value = $updates['updated_value'];
				if ( $updates && count($updated_field_value) > 0 ) {
					$key_ts_info .= "about to update field '$target_key'<br />";
					$key_ts_info .= count($updated_field_value)." items in updated_field_value array<br />";
					//$key_ts_info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
					//$key_ts_info .= "about to update field '$target_key' with value(s): ".print_r($updated_field_value, true)."<br />";
					if ( update_field( $target_key, $updated_field_value, $snippet_id ) ) {
						$key_ts_info .= "updated field: ".$target_key." for snippet_id: $snippet_id<br />";
					} else {
						$key_ts_info .= "update FAILED for field: ".$target_key." for snippet_id: $snippet_id<br />";
					}
				}
				
				// Update the associated repeater field as needed
				
				// First, remove duplicates and repeater_removals
				$repeater_rows_revised = array();
				//
				if ( !empty($repeater_rows) ) {
			
					$key_ts_info .= count($repeater_rows)." repeater_rows<br />"; //$key_ts_info .= "repeater_rows: <pre>".print_r($repeater_rows, true)."</pre>";//"<br />"; //<pre></pre>
				
					$key_ts_info .= "<h4>About to clean up repeater_rows by removing repeater_removals...</h4>";
					// Update repeater_rows array by removing removals
					if ( !empty($repeater_removals) ) {
						sort($repeater_removals); //$repeater_removals = array_unique($repeater_removals, SORT_REGULAR);
						//$key_ts_info .= "repeater_removals: <pre>".print_r($repeater_removals, true)."</pre>";
						foreach ( $repeater_rows as $k => $v ) {
							$repeater_url = $v['url'];
							//$key_ts_info .= "k: $k / repeater_url (v): $repeater_url<br />";
							if ( in_array($repeater_url, $repeater_removals) ) {
								$key_ts_info .= "The url: $repeater_url will NOT be added to the repeater_rows_revised array<br />";
								//$key_ts_info .= "removing url: $repeater_url<br />";
								//unset($repeater_rows[$k]);
							} else {
								//$key_ts_info .= "Adding url to repeater_rows_revised -- not in repeater_removals array: $repeater_url<br />";
								$repeater_rows_revised[] = array('url' => $repeater_url); //$repeater_rows_revised[$k] = $repeater_rows[$k];
							}
						}
					} else {
						$key_ts_info .= "repeater_removals array is empty<br />";
					}
				}
			
				// Second, add repeater_additions, making sure they're not duplicates...
				if ( !empty($repeater_additions) ) {
					$key_ts_info .= "<h4>About to add repeater_additions to repeater_rows...</h4>";
					//$key_ts_info .= "repeater_additions: <pre>".print_r($repeater_additions, true)."</pre>";
					foreach ( $repeater_additions as $url ) {
						// TODO: make sure url isn't a duplicate of an existing array item
						if ( in_array($url, $repeater_values) ) {
							$key_ts_info .= "The url '".$url."' is already in the repeater_rows array<br />";
						} else {
							$repeater_rows_revised[] = array('url' => $url);
							$key_ts_info .= "Added url '".$url."' to the repeater_rows_revised array<br />";
						}
					}
				}
			
				// Update the field with the revised array
				if ( !empty($repeater_rows_revised) ) {
				
					// Remove duplicates
					$key_ts_info .= "About to update repeater_rows...<br />";
					//
					//$repeater_rows_revised = array_unique($repeater_rows_revised, SORT_REGULAR); // not working!
					//
					$repeater_values = array_column($repeater_rows_revised, 'url');
					//$key_ts_info .= "repeater_rows_revised repeater_values: <pre>".print_r($repeater_values, true)."</pre><br />";
					array_multisort($repeater_values, SORT_ASC, $repeater_rows_revised);
					// Fix the sorting!
					//
					$key_ts_info .= "repeater_key: ".$repeater_key."<br />";
					//$key_ts_info .= "repeater_rows_revised: <pre>".print_r($repeater_rows_revised, true)."</pre><br />";
					//
					if ( $repeater_rows_revised == $repeater_rows ) {
						$key_ts_info .= "No changes necessary -- repeater_rows_revised == repeater_rows<br />";
					} else {
						//$key_ts_info .= "updates tmp disabled<br />";
						if ( update_field( $repeater_key, $repeater_rows_revised, $snippet_id ) ) {
							$key_ts_info .= "updated repeater field: ".$repeater_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for repeater field: ".$repeater_key." for snippet_id: $snippet_id<br />";
						}
					}
				
				}
				
				
			} else if ( $key == 'target_by_post_type' || $key == 'target_by_taxonomy_archive' || $key == 'widget_logic_custom_post_types_taxonomies' ) {
			
				// If this is the widget_logic version of the field, update our new target_by_post_type field
				if ( $key == 'widget_logic_custom_post_types_taxonomies' ) {
				
					$key_ts_info .= "conditions: <pre>".print_r($conditions, true)."</pre>";
					//
					$cpt_conditions = array();
					$cpt_archive_conditions = array();
					$tax_conditions = array();
					$updated_cpt_conditions = array();
					$updated_cpt_archive_conditions = array();
					$updated_tax_conditions = array();
					
					foreach ( $conditions as $condition => $value ) {
						$key_ts_info .= "condition: $condition => $value<br />";
						if ( strpos($condition, 'is_tax') !== false ) {
							// get rid of the is_tax- prefix before saving
							$condition = substr($condition,strlen('is_tax-'));
							$tax_conditions[] = $condition;
						} else if ( strpos($condition, 'is_archive') !== false ) {
							// get rid of the is_archive- prefix before saving
							$condition = substr($condition,strlen('is_archive-'));
							$cpt_archive_conditions[] = $condition;
						} else if ( strpos($condition, 'is_singular') !== false ) {
							// get rid of the is_singular- prefix before saving
							$condition = substr($condition,strlen('is_singular-'));
							$cpt_conditions[] = $condition;
						} else {
							$key_ts_info .= "uncategorized condition: $condition [$value]<br />";
						}			
					}
					$key_ts_info .= "tax_conditions: ".print_r($tax_conditions, true)."<br />";
					$key_ts_info .= "cpt_archive_conditions: ".print_r($cpt_archive_conditions, true)."<br />";
					$key_ts_info .= "cpt_conditions: ".print_r($cpt_conditions, true)."<br />";
					
					// CPT conditions
					$update_key = 'target_by_post_type';
					$updates = get_updated_field_value( $snippet_id, $update_key, $cpt_conditions, 'array' ); // post_id, key, new_value, type
					$key_ts_info .= $updates['info'];
					$updated_field_value = $updates['updated_value'];
					if ( $updates && count($updated_field_value) > 0 ) {
						$key_ts_info .= "about to update field '$update_key'<br />";
						$info .= count($updated_field_value)." items in updated_field_value array<br />";
						//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
						if ( update_field( $update_key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= count($updated_field_value)." count(updated_field_value) but no update because....???<br />";					
					}
					$key_ts_info .= "<hr />";
					/*
					$existing_cpt_conditions = get_field( 'target_by_post_type', $snippet_id );
					if ( empty($existing_cpt_conditions) ) {
						$key_ts_info .= "No existing_cpt_conditions => update `target_by_post_type` with widget_logic cpt_conditions<br />";
						$updated_cpt_conditions = $cpt_conditions;
					} else if ( $existing_cpt_conditions == $cpt_conditions ) {
						$key_ts_info .= "existing_cpt_conditions in `target_by_post_type` field same as widget_logic cpt_conditions => no update needed<br />";
					} else {
						// Merge the arrays
						$updated_cpt_conditions = array_unique(array_merge($existing_cpt_conditions, $cpt_conditions));
					}
					if ( $updated_cpt_conditions ) {							
						$key_ts_info .= "updated_cpt_conditions: ".print_r($updated_cpt_conditions, true)."<br />";
						if ( update_field( 'target_by_post_type', $updated_cpt_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_post_type` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_post_type` for snippet_id: $snippet_id<br />";
						}
					}
					*/
					
					// CPT Archive conditions
					$update_key = 'target_by_post_type_archive';
					$updates = get_updated_field_value( $snippet_id, $update_key, $cpt_archive_conditions, 'array' ); // post_id, key, new_value, type
					$key_ts_info .= $updates['info'];
					$updated_field_value = $updates['updated_value'];
					if ( $updates && count($updated_field_value) > 0 ) {
						$key_ts_info .= "about to update field '$update_key'<br />";
						$info .= count($updated_field_value)." items in updated_field_value array<br />";
						//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
						if ( update_field( $update_key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= count($updated_field_value)." count(updated_field_value) but no update because....???<br />";					
					}
					$key_ts_info .= "<hr />";
					/*
					$existing_cpt_archive_conditions = get_field( 'target_by_post_type_archive', $snippet_id );
					if ( empty($existing_cpt_archive_conditions) ) {
						$key_ts_info .= "No existing_cpt_archive_conditions => update `target_by_post_type_archive` with widget_logic cpt_archive_conditions<br />";
						$updated_cpt_archive_conditions = $cpt_archive_conditions;
					} else if ( $existing_cpt_archive_conditions == $cpt_archive_conditions ) {
						$key_ts_info .= "existing_cpt_archive_conditions in `target_by_post_type_archive` field same as widget_logic cpt_archive_conditions => no update needed<br />";
					} else {
						// Merge the arrays
						$updated_cpt_archive_conditions = array_unique(array_merge($existing_cpt_archive_conditions, $cpt_archive_conditions));
					}
					//
					if ( $updated_cpt_archive_conditions ) {							
						$key_ts_info .= "updated_cpt_archive_conditions: ".print_r($updated_cpt_archive_conditions, true)."<br />";
						if ( update_field( 'target_by_post_type_archive', $updated_cpt_archive_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_post_type_archive` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_post_type_archive` for snippet_id: $snippet_id<br />";
						}
					}
					*/
					
					// Taxonomy Archive Conditions
					$update_key = 'target_by_taxonomy_archive';
					$updates = get_updated_field_value( $snippet_id, $update_key, $tax_conditions, 'array' ); // post_id, key, new_value, type
					$key_ts_info .= $updates['info'];
					$updated_field_value = $updates['updated_value'];
					if ( $updates && count($updated_field_value) > 0 ) {
						$key_ts_info .= "about to update field '$update_key'<br />";
						$info .= count($updated_field_value)." items in updated_field_value array<br />";
						//$info .= "=> <pre>".print_r($updated_field_value, true)."</pre>";
						if ( update_field( $update_key, $updated_field_value, $snippet_id ) ) {
							$key_ts_info .= "updated field: ".$update_key." for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field: ".$update_key." for snippet_id: $snippet_id<br />";
						}
					} else {
						$key_ts_info .= count($updated_field_value)." count(updated_field_value) but no update because....???<br />";					
					}
					$key_ts_info .= "<hr />";
					/*
					$existing_tax_conditions = get_field( 'target_by_taxonomy_archive', $snippet_id );
					if ( empty($existing_tax_conditions) ) {
						$key_ts_info .= "No existing_tax_conditions => update `target_by_post_type` with widget_logic conditions<br />";
						$updated_tax_conditions = $tax_conditions;
					} else if ( $existing_tax_conditions == $tax_conditions ) {
						$key_ts_info .= "existing_tax_conditions in `target_by_post_type` field same as widget_logic conditions => no update needed<br />";
					} else {
						$updated_tax_conditions = array_unique(array_merge($existing_tax_conditions, $tax_conditions)); // Merge the arrays
					}
					//
					if ( $updated_tax_conditions ) {							
						$key_ts_info .= "updated_tax_conditions: ".print_r($updated_tax_conditions, true)."<br />";
						//$key_ts_info .= "updates tmp disabled<br />";
						if ( update_field( 'target_by_taxonomy_archive', $updated_tax_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_taxonomy_archive` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_taxonomy_archive` for snippet_id: $snippet_id<br />";
						}
					}
					*/
				}
				
			} else if ( $key == 'target_by_taxonomy' || $key == 'widget_logic_taxonomy' ) {
			
				//
				if ( $conditions ) { $key_ts_info .= "tax_pairs => <pre>".print_r($conditions, true)."</pre>"; } // tax_pairs => conditions			
				// ... WIP ...
					
				// WIP -- TODO: use fcns copied from WidgetContext customizations to split pairs into array and compare/merge etc
				//$target_taxonomies = get_field($key, $snippet_id, false);
				
			
			} else if ( $key == 'target_by_location' || $key == 'widget_logic_location' ) {
			
				// If this is the widget_logic version of the field, update our new target_by_post_type field
				if ( $key == 'widget_logic_location' ) {
				
					$wll_conditions = array();
					$updated_conditions = array();
					
					foreach ( $conditions as $condition => $value ) {
						$wll_conditions[] = $condition;
					}
					
					// TODO: update to use new get_updated_field_value fcn
					$existing_conditions = get_field( 'target_by_location', $snippet_id );
					if ( empty($existing_conditions) ) {
						$key_ts_info .= "No existing_conditions => update `target_by_location` with widget_logic cpt_conditions<br />";
						$updated_conditions = $wll_conditions;
					} else if ( $existing_conditions == $wll_conditions ) {
						$key_ts_info .= "existing_conditions in `target_by_location` field same as widget_logic wll_conditions => no update needed<br />";
					} else {
						$updated_conditions = array_unique(array_merge($existing_conditions, $wll_conditions)); // Merge the arrays
					}
					//
					if ( $updated_conditions ) {							
						$key_ts_info .= "updated_cpt_conditions: ".print_r($updated_conditions, true)."<br />";
						if ( update_field( 'target_by_location', $updated_conditions, $snippet_id ) ) {
							$key_ts_info .= "updated field `target_by_location` for snippet_id: $snippet_id<br />";
						} else {
							$key_ts_info .= "update FAILED for field `target_by_location` for snippet_id: $snippet_id<br />";
						}
					}
					
				}
				
				// WIP -- TODO: translate widget_logic_location to target_by_location options
				/*
				widget_logic e.g.:
				is_front_page
				is_home
				is_singular == All posts, pages and custom post types
				is_single
				is_page
				is_attachment => 1
				is_search
				is_404
				is_archive
				is_category == All category archives
				
				// Related WP functions
				is_archive(): bool == Archive pages include category, tag, author, date, custom post type, and custom taxonomy based archives.
				See also
				is_category()
				is_tag()
				is_author()
				is_date()
				is_post_type_archive()
				is_tax()
				//
				is_singular( string|string[] $post_types = '' ): bool == Determines whether the query is for an existing single post of any post type (post, attachment, page, custom post types).
				*/
				
			}
			
			$ts_info .= $key_ts_info;
			$ts_info .= "<hr />";
			
		} else {
			//$ts_info .= "No meta data found for key: $key<br />";
		}
	}
	
	if ( $do_ts ) { $info .= $ts_info; }
	
	$info .= '</div>';
	
	return $info;
	
}

//
function get_updated_field_value ( $post_id = null, $key = null, $new_value = null, $type = 'array' ) {

	// init
	$arr = array();
	$info = "";
	$info .= ">> get_updated_field_value for key: $key <<<br />";
	//
	if ( $type == 'array' ) {
		$updated_value = array();
	} else {
		$updated_value = null;
	}
	
	// Get existing field value, if any
	if ( $post_id ) {
		$old_value = get_field( $key, $post_id, false ); //get_field($selector, $post_id, $format_value);
		//$old_value = get_post_meta( $post_id, $key, true );
	} else {
		$old_value = null;
	}
	
	if ( $type == 'array' ) {
		
		//$info .= "field/var type == 'array'<br />";
		
		// Unserialize as needed -- TODO: eliminate redundancy
		if ( !is_array($old_value) && strpos($old_value, '{') !== false ) {
			$info .= "unserialize old_value...<br >";
			$old_value = unserialize($old_value);
			//$info .= "=> ".print_r($old_value,true)."<br />";
		}
		//if ( !is_array($old_value) && !empty($old_value) ) { $old_value = json_decode($old_value); }
		
		// Sort the existing values and save the sorted array
		if ( is_array($old_value) && !empty($old_value) ) {
			$info .= count($old_value)." items in old_value array<br />";
			//$info .= "=> ".print_r($old_value, true)."<br />"; //"<pre></pre>";
			// TODO: what about if this isn't an array of post ids? generalize... tbd
			$old_value_sorted = sort_post_ids_by_title($old_value);
			if ( $old_value_sorted ) {
				$info .= $old_value_sorted['info'];
				$old_value = $old_value_sorted['post_ids'];
				//$info .= "old_value (sorted): ".print_r($old_value, true)."<br />"; //"<pre></pre>";
			}
			// re-serialize?
			//update_field( $target_key, $old_value, $snippet_id );
		} else if ( empty($old_value) ) {
			$info .= "old_value is empty<br />";
		} else {
			$info .= "old_value: ".print_r($old_value, true)."<br />";
		}
		
		// Evaluate the new data
		if ( !empty($new_value) ) {
		
			$info .= count($new_value)." items in new_value array<br />";
			//$info .= "new_value: <pre>".print_r($new_value, true)."</pre>";
			// WIP -- TODO: sort by post title and update
			$new_value_sorted = sort_post_ids_by_title($new_value);
			if ( $new_value_sorted ) {
				$info .= $new_value_sorted['info'];
				$new_value = $new_value_sorted['post_ids'];
				//$info .= "new_value (sorted): ".print_r($new_value, true)."<br />"; //"<pre></pre>";
			}
			// TODO, maybe: look for patterns in post types, categories, if there are many similar posts? (e.g. instances of recurring events)
			// Determine whether an update is needed
			if ( empty($old_value) ) {
				$info .= $key." field is empty (old_value) >> use new_value<br />";
				$updated_value = $new_value;
			} else if ( $new_value == $old_value ) {
				$info .= "new_value for '$key' same as old_value => no update needed<br />";
			} else {
				// Merge old and new arrays
				$info .= "Merge old_value with new_value for '$key' field<br />";
				$updated_value = array_unique(array_merge($old_value, $new_value));
				sort($updated_value); // Sort the array -- TODO: sort instead by post title
				$info .= count($updated_value)." items in updated_value array<br />";
			}
		
		} else {
		
			$info .= "new_value array is empty ==> no update needed<br />";
			
		}
		
	} else {
		// WIP/TBD as needed...
	}
	
	$info .= "updated_value: ".print_r($updated_value, true)."<br />"; //"<pre></pre>";
	
	$arr['info'] = $info;
	$arr['updated_value'] = $updated_value;
	
	//return $updated_value;
	return $arr;
	
}

/*** Copied from mods to WidgetContext ***/
//
function match_terms( $rules, $post_id ) {
        
    $ts_info = "";
    
	if ( $post_id == null ) { 
		$post_id = get_the_ID();
		//$post_type = get_post_type( $post_id );
	}
	$ts_info .= "post_id: ".$post_id."<br />";
	
	if ( function_exists('sdg_log') ) { 
		//sdg_log("divline2");
		//sdg_log("function called: match_terms");
		//sdg_log("post_id: ".$post_id);
	}
	
	// init/defaults
	//$match = false;
	//$arr_tterms = array(); // Multidimensional array of taxonomies & terms
	
	// Determine the match_type
	if ( (strpos($rules, '||') !== false && strpos($rules, '&&') !== false )  // String includes both 'and' AND 'or' operators
		|| preg_match("/\s*\:\s*-\s*/", $rules) != 0 // has_exclusions
		//|| preg_match("/(\\()*(\\))/", $x) !== false // String contains something (enclosed within parens)
		) { 
		$match_type = 'complex';
		$matches_found = 0;
	} else if ( 
		strpos($rules, '&&') !== false // If string contains "and" but no or operator, and no parens
		|| preg_match("/match_type\s*\:\s*all/", $rules) != 0 // Match if string contains "match_type:all" (with or without whitespace around colon)
		|| preg_match("/\s*\:\s*-\s*/", $rules) != 0 // has_exclusions
		) {
		$match_type = 'all';
	} else {
		$match_type = 'any'; // Default: match any of the given terms
	}
	$ts_info .= "match_type: ".$match_type."<br />";
	$ts_info .= "rules (str): ".$rules."<br />";
	
	if ( function_exists('sdg_log') ) { 
		//sdg_log("rules (str): '".$rules."'");
		//sdg_log("match_type: ".$match_type); // ."; has_exclusions: ".$has_exclusions
	}
	
	// Explode the rules string into an array, items separated by line breaks
	$pairs = explode( "\n", $rules );
	//if ( function_exists('sdg_log') ) { sdg_log("pairs: ".print_r($pairs,true)); }
	
	// Build an associative array of the given rules
	//$arr_rules = array_map('process_tax_pair', $pairs); // why doesn't this work???
	$arr_rules = array();
	foreach ( $pairs as $pair ) {
		$arr_rules[] = process_tax_pair($pair);
	}
	
	if ( function_exists('sdg_log') ) { 
		if ( !empty($arr_rules) ) {
			//sdg_log"arr_rules: ".print_r($arr_rules,true));
		} else {
			//sdg_log"arr_rules is empty.");
		}
	}
			
	/*
	// TODO: deal w/ possibility of combinations of terms -- allow e.g. has term AND term (+); has term OR term; has term NOT term (-)?

	e.g. 
	match_type:complex
	(event-categories:worship-services
	|| event-categories:video-webcasts)
	&&
	sermon_topic:abraham
	
	e.g.
	(event-categories:worship-services && category:music) 
	|| sermon_topic:abraham 
	
	*/
	
	$num_rules = count($arr_rules);
	
	if ( $arr_rules ) {
		foreach ( $arr_rules as $rule ) {
			
			if ( empty($rule) ) { continue; }
			
			$taxonomy = $rule['taxonomy'];
			$term = $rule['term'];
			if ( isset($rule['operator']) ) { $operator = $rule['operator']; } else { $operator = null; }
			$exclusion = $rule['exclusion'];
			//if ( function_exists('sdg_log') ) { sdg_log("term: ".$term."; taxonomy: ".$taxonomy."; operator: ".$operator."; exclusion: ".$exclusion); }
			$ts_info .= "term: ".$term."; taxonomy: ".$taxonomy."; operator: ".$operator."; exclusion: ".$exclusion."<br />";
			
			if ( $taxonomy == 'match_type' || empty($taxonomy) ) {
				//if ( function_exists('sdg_log') ) { sdg_log("match_type or empty >> continue"); }
				continue; // This is not actually a taxonomy rule; move on to the next.
			}
			
			// Check to see if taxonomy even APPLIES to the given post before worrying about whether it matches a specific term in that taxonomy
			$arr_taxonomies = get_post_taxonomies(); // get_post_taxonomies( $post_id );
			if ( !in_array( $taxonomy, $arr_taxonomies ) ) {
				$ts_info .= "taxonomy '$taxonomy' does not apply => arr_taxonomies: ".print_r($arr_taxonomies,true)."<br />";
				continue;
			}
			
			// Handle the matching based on the number and complexity of the rules
			if ( $num_rules == 1 ) {
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $exclusion == 'no' ) {
						$ts_info .= "Match found (single rule; has_term; exclusion false) >> return true<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (single rule; has_term; exclusion false) >> return true"); }
						//return $ts_info; //
						return true; // post has term for single rule AND term is not negated, therefore it is a match
					} else {
						$ts_info .= "Match found (single rule; has_term; exclusion TRUE) >> return false<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (single rule; has_term; exclusion TRUE) >> return false"); }
						return false;
					}                        
				} else if ($exclusion == 'no') {
					$ts_info .= "NO match found (single rule; NOT has_term; exclusion false) >> return false<br />";
					//if ( function_exists('sdg_log') ) { sdg_log("NO match found (single rule; NOT has_term; exclusion false) >> return false"); }
					return false; // post has term but single rule requires posts withOUT that term, therefore no match
				}
				
			} else if ( $match_type == 'any' && has_term( $term, $taxonomy, $post_id ) && $exclusion == 'no' ) { 
				
				$ts_info .= "Match found (match_type 'any'; has_term; exclusion false) >> return true<br />";
				//if ( function_exists('sdg_log') ) { sdg_log("match found (match_type 'any'; has_term; exclusion false) >> return true"); }
				return true; // Match any => match found (no need to check remaining rules, if any)
				
			} else if ( $match_type == 'all' ) {
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $exclusion == 'yes' ) {
						$ts_info .= "Match found (match_type 'all'; has_term; exclusion TRUE) >> return false<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (match_type 'all'; has_term; exclusion TRUE) >> return false"); }
						return false; // post has the term but rules say it must NOT have this term
					} else {
						$ts_info .= "Ok so far! (match_type 'all'; has_term; exclusion false) >> continue<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Ok so far! (match_type 'all'; has_term; exclusion false) >> continue"); }
					}
				} else if ( $exclusion == 'no' ) {
					$ts_info .= "NO match found (match_type 'all'; NOT has_term; exclusion false) >> return false<br />";
					//if ( function_exists('sdg_log') ) { sdg_log("NO match found (match_type 'all'; NOT has_term; exclusion false) >> return false"); }
					return false; // post does not have the term and rules require it must match all
				}
				
			} else if ( $match_type == 'complex' ) {
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $exclusion == 'yes' ) {
						$ts_info .= "Match found (match_type 'complex'; has_term; exclusion TRUE) >> return false<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Match found (match_type 'complex'; has_term; exclusion TRUE) >> return false"); }
						return false; // post has the term but rules say it must NOT have this term
					} else {
						$ts_info .= "Ok so far! (match_type 'complex'; has_term; exclusion false) >> continue<br />";
						//if ( function_exists('sdg_log') ) { sdg_log("Ok so far! (match_type 'complex'; has_term; exclusion false) >> continue"); }
						$matches_found++;
					}
				} else if ( $exclusion == 'no' ) {
					$ts_info .= "NO match found (match_type 'complex'; NOT has_term; exclusion false) >> return false (?)<br />";
					//if ( function_exists('sdg_log') ) { sdg_log("NO match found (match_type 'complex'; NOT has_term; exclusion false) >> return false"); }
					//return false; // post does not have the term and rules require it must match all
				}
				
			}
			
			/*
			// Store terms in tterms array for match_type = all and complex matching once the loop is finished
			if ( $exclusion == true ) {
				$term = "-".$term;
			}
			if ($arr_tterms[$taxonomy]) {
				array_push($arr_tterms[$taxonomy],$term);
			} else {
				$arr_tterms[$taxonomy] = array($term);
			}
			*/
			
		} // end foreach $arr_rules
		
		// If we got through the entire list of rules and the post matched all the rules, return true
		if ( $match_type == 'all' ) {
			//if ( function_exists('sdg_log') ) { sdg_log("Matched! (match_type 'all') >> return true"); }
			return true;
		} else if ( $match_type == 'complex' && $matches_found > 0 ) {
			//if ( function_exists('sdg_log') ) { sdg_log("Matched! (match_type 'complex') with at least one positive match (and no matches to excluded categories) >> return true"); }
			return true;
		}
		
		// Now all that's left is to deal with complex queries...
		
		//if ( !empty($operator) )
		
		/*if ( $arr_tterms ) {
			
			foreach ( $arr_tterms as $taxonomy => $arr_terms ) {
				sdg_log("taxonomy: ".$taxonomy."; arr_terms: ".print_r($arr_terms, true));
				
				$post_terms = get_the_terms( $post_id, $taxonomy );
				
				if ( has_term( $term, $taxonomy, $post_id ) ) {
					if ( $match_type == 'all' ) { // If a match has been found and this is a simple query, go ahead and return match/true
						return true;
					} else if ( $match_type == 'complex' ) {
						//
					}
				}
			}
		}*/
		
	}

	//if ( function_exists('sdg_log') ) { sdg_log("End of the line. Returning null."); }
	return null;
	//return $match;
	
}

//
function process_tax_pair($rule) {
	
	if ( function_exists('sdg_log') ) { 
		sdg_log("function called: process_tax_pair");
		sdg_log("rule: ".$rule); 
	}
	
	$arr = array();

	// If this is an empty line, i.e. doesn't actually contain a pair, then return false
	if (strpos($rule, ':') === false) {
		return $arr;
	}
	
	// Remove all whitespace
	$x = preg_replace('/\s+/', '', $rule);
	
	// Check for operator
	if (strpos($x, '||') !== false) {
		$arr['operator'] = 'OR';
		$x = str_replace('||','',$x);
	} elseif (strpos($x, '&&') !== false) {
		$arr['operator'] = 'AND';
		$x = str_replace('&&','',$x);
	}
	
	// Check for minus sign to indicate EXclusion
	if (strpos($x, ':-') !== false) {
		$arr['exclusion'] = 'yes';
	} else {
		$arr['exclusion'] = 'no';
	}
	
	$arr['taxonomy'] = trim(substr($x,0,stripos($x,":")));
	//$arr['term'] = trim(substr($x,stripos($x,":")+1));
	$term = trim(substr($x,stripos($x,":")+1));
	$term = ltrim($term,"-");
	$arr['term'] = $term;

	/*
	$taxonomy = trim(substr($x,0,stripos($x,":")));
	$arr['taxonomy'] = $taxonomy;
	$arr['term'] = $term;

	if ($arr_tterms[$taxonomy]) {
		array_push($arr_tterms[$taxonomy],$term);
	} else {
		$arr_tterms[$taxonomy] = array($term);
	}
	*/

	return $arr;
	
}

//
function make_terms_array($x) {
	$arr = array(trim(substr($x,0,stripos($x,":"))),trim(substr($x,stripos($x,":")+1)));
	return $arr;
}

/*** END copied from WidgetContext ***/

// WIP
/*
https://developer.wordpress.org/reference/functions/wp_find_widgets_sidebar/
wp_find_widgets_sidebar( string $widget_id ): string|null
Finds the sidebar that a given widget belongs to.
$widget_id string Required
The widget ID to look for.

Return
string|null The found sidebar's ID, or null if it was not found.
*/
function get_sidebar_id( $widget_uid = null) {
	//
	return null;

}

// WIP
add_shortcode('widget_and_snippets', 'show_widgets_and_snippets');
function show_widgets_and_snippets ( $atts = [] ) {

	// TS/logging setup
    $do_ts = false; 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    sdg_log( "function called: show_widgets_and_snippets", $do_log );
    
    $info = "";
    $ts_info = "";
    
    $args = shortcode_atts( array(
		'limit'   => 1,
		'post_id' => null,
    ), $atts );
    
    // Extract
	extract( $args );
	
	// Get widgets and snippets set to display per post
	
	// If no post_id has been specified, get random post ids according to $limit
	// TODO
	
	if ( $post_id === null ) { return "No post_id; "; } // tft
	//
	$arr_sidebars_widgets = get_option('sidebars_widgets');
	$cs_sidebars = get_option('cs_sidebars');
	//
	$info .= '<div class="code">';
	
	$info .= "<h3>post_id: ".$post_id." -- ".get_the_title($post_id)."</h3>";
	
	// Default sidebar_id
	$sidebar_id  = "sidebar-1";
	
	// Check for custom sidebars 
	$cs_replacements = get_post_meta( $post_id, '_cs_replacements', true );
	if ( $cs_replacements ) { 
		$info .= "cs_replacement: ".print_r($cs_replacements, true)."<br />";
		$first_key = array_key_first($cs_replacements);
		if ($first_key !== null) {
			$sidebar_id = $cs_replacements[$first_key];
		}
	} else {
		// Get page template? i.e. make sure this post uses a sidebar at all...
		//...
		$info .= "This post uses the default sidebar.<br />";
	}
	$info .= "sidebar_id: ".$sidebar_id."<br />";
	
	//$sidebar = wp_list_widget_controls($sidebar_id); // Show the widgets and their settings for a sidebar -- Used in the admin widget config screen -- DN seem to work at all on front end
	$sidebar = wp_get_sidebar( $sidebar_id ); // Retrieves the registered sidebar with the given ID: name, id, description, before_widget, etc.
	//$info .= "sidebar: ".print_r($sidebar, true)."<br />";
	$info .= '=> "'.$sidebar['name'].'"<br />';
	
	// Get widgets
	// -------
	$widgets = array(); // init
	if ( isset($arr_sidebars_widgets[$sidebar_id]) ) {
		$widgets = $arr_sidebars_widgets[$sidebar_id];
	} else if ( isset($cs_sidebars[$sidebar_id]) ) {
		$widgets = $cs_sidebars[$sidebar_id];
	}
	//$info .= "widgets: <pre>".print_r($widgets, true)."</pre>";
	
	// WIP --  // TODO: fix this -- not working because it's not filtering according to post_id passed to fcn, but rather according to current URL
	/*
	$sidebars_widgets = array( $sidebar_id => $widgets );
	$sidebars_widgets_filtered = apply_filters( 'sidebars_widgets', $sidebars_widgets );
	$filtered_widgets = $sidebars_widgets_filtered[$sidebar_id];
	//
	*/
	/*
	foreach ( $sidebars_widgets as $widget_area => $widget_list ) {

		if ( 'wp_inactive_widgets' === $widget_area || empty( $widget_list ) ) {
			continue;
		}

		foreach ( $widget_list as $pos => $widget_id ) {

			if ( ! $this->check_widget_visibility( $widget_id ) ) {
				unset( $sidebars_widgets[ $widget_area ][ $pos ] );
			}
		}
	}
	*/
	/*
	//$filtered_widgets = maybe_unset_widgets_by_context( $sidebars_widgets );
	//$info .= "filtered_widgets: <pre>".print_r($filtered_widgets, true)."</pre>";
	$info .= '<div class="code float-left" style="width: 49%;">';
	$info .= "filtered_widgets [WIP]:<br />";
	//$info .= "<pre>";
	foreach ( $filtered_widgets as $pos => $widget_uid ) {
		$info .= "[".$pos."] => ".$widget_uid;
		// If this is a text or html widget, get more info from the corresponding snippet records
		$snippet_id = get_snippet_by_widget_uid ( $widget_uid );
		if ( $snippet_id ) {
			$info .= " => snippet_id: ".$snippet_id;
			$info .= " => ".get_the_title($snippet_id);
		}
		//." => ".$widget_title
		$info .= "<br />";
	}
	//$info .= "</pre>";
	$info .= "</div>";
	*/
	// Get snippets
	// -------
	$snippets = get_snippets ( array( 'post_id' => $post_id, 'return' => 'ids', 'sidebar_id' => $sidebar_id ) );
	
	if ( $snippets ) {
		$info .= '<div class="code">'; // float-left" style="width: 49%;
		$info .= "snippets:<br />";
		//$info .= "<pre>".print_r($snippets, true)."</pre>";
		//$info .= "<pre>";
		$details = "";
		foreach ( $snippets as $pos => $snippet_id ) {
			$snippet_info = "";
			$widget_uid = get_post_meta( $snippet_id, 'widget_uid', true );
			$widget_sidebar_id = get_post_meta( $snippet_id, 'sidebar_id', true );
			$snippet_info .= "[".$pos."] => ".$widget_uid;
			$snippet_info .= " => snippet_id: ".$snippet_id;
			$snippet_info .= " => ".get_the_title($snippet_id);
			$snippet_info .= " [".$widget_sidebar_id."]";
			$snippet_info .= "<br />";
			//
			$info .= $snippet_info;
			$details .= $snippet_info;
			// Check/update widget/snippet logic
			//$details .= 
		}
		//$info .= "</pre>";
		$info .= '</div>';
	}
	
	$info .= "</div>";
	
	$info .= '<hr class="clear" />';
	
	return $info;
	
}

/*** MISC ***/

function surprise() {

	// Set up an array of fun words and things...
	$surprises = array();
	
	// Pick one at random
	$surprise = array_rand($surprises, 1);
	
	// Surprise!
	return $surprise;	

}

?>