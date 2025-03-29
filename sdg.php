<?php
/**
 * @package SDG
 */

/*
Plugin Name: SDG
Plugin URI: 
Description: Custom post types, taxonomies and functions for music and more
Version: 1.070324.1
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

// Define our handy constants.
define( 'SDG_VERSION', '0.1.5' );
define( 'SDG_PLUGIN_DIR', __DIR__ );
define( 'SDG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SDG_PLUGIN_BLOCKS', SDG_PLUGIN_DIR . '/blocks/' );
//
$plugin_path = plugin_dir_path( __FILE__ );

// TODO: Deal w/ plugin dependencies -- Display Content; ACF; EM; &c.?
// TODO: Check for ACF field groups; import them from plugin copies if not found?
// TODO: formalize dependencies between modules -- e.g. events, music both require people

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

// Function to check for dev/admin user
function sdg_queenbee() {
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;
	$useremail = $current_user->user_email;
	//
    if ( $username == 'stcdev' || $useremail == "birdhive@gmail.com" ) {
    	return true;
    } else {
    	return false;
    }
}

//
if ( ! is_admin() ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' ); // so that we can run functions like post_exists on the front end
}

/* +~+~+ ACF +~+~+ */

// Set custom load & save JSON points for ACF sync
require 'includes/acf-json.php';

// Register blocks and other handy ACF Block helpers
//require 'includes/acf-blocks.php';

// Register a default "Site Settings" Options Page
//require 'includes/acf-settings-page.php';

// Restrict access to ACF Admin screens
require 'includes/acf-restrict-access.php';

// Display and template helpers
require 'includes/template-tags.php';

// Load ACF field groups hard-coded as PHP
require 'includes/acf-field-groups.php';

/* +~+~+ *** +~+~+ */

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
    
    // Checkbox to show/hide troubleshooting messages
	add_settings_field(
        'show_ts',
        esc_attr__('Show TS', 'sdg'),
        'sdg_ts_field_cb',
        'sdg',
        'sdg_settings',
        array( 
            'type'         => 'checkbox',
            //'option_group' => 'sdg_settings', 
            'name'         => 'show_ts',
            'label_for'    => 'show_ts',
            'value'        => (empty(get_option('sdg_settings')['show_ts'])) ? 0 : get_option('sdg_settings')['show_ts'],
            'description'  => __( 'Show troubleshooting info.', 'sdg' ),
            'checked'      => (!isset(get_option('sdg_settings')['show_ts'])) ? 0 : get_option('sdg_settings')['show_ts'],
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

function sdg_ts_field_cb( $args ) { 

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
		///'events' => __( 'Events' ), // >>> WHx4
		///'people' => __( 'People' ), // >>> WHx4
		///'groups' => __( 'Groups' ), // >>> WHx4
		//'ensembles' => __( 'Ensembles' ), 
		///'music' => __( 'Music Library' ), // >>> MLib
		'webcasts' => __( 'Webcasts' ), 
		'sermons' => __( 'Sermons' ), 
		'lectionary' => __( 'Lectionary' ), 
		//
		'organizations' => __( 'Organizations (deprecated)' ),
		'projects' => __( 'Projects' ), 
		'press' => __( 'Press' ), 
		//'recordings' => __( 'Recordings' ),
		'links' => __( 'Links' ),
		'snippets' => __( 'Snippets' ), 
		'newsletters' => __( 'Newsletters' ),
		//'sources' => __( 'Sources' ),
		//
		///'organs' => __( 'Organs' ), // >>> MLib
		//
		///'venues' => __( 'Venues' ), // >>> WHx4
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
        
        if ( isset($_SERVER['HTTP_HOST']) ) {
        	$subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
        	if ( $subdomain == "dev" ) { return true; } // RS dev site
        }        
        
        return false;
    }
//}

// Include sub-files
// TODO: make them required? Otherwise dependencies may be an issue.
// TODO: maybe: convert to classes/methods approach??
// TODO: maybe: split this into several separate plugins -- SDG WooCommerce; SDG Developer Functions; &c. -- ?!? WIP ?!?

$includes = array( 'posttypes', 'taxonomies' );
$admin_functions_filepath = $plugin_path . 'includes/'.'admin_functions.php';
$common_functions_filepath = $plugin_path . 'includes/'.'common_functions.php';

if ( file_exists($admin_functions_filepath) ) { include_once( $admin_functions_filepath ); } else { echo "file $admin_functions_filepath not found"; }
if ( file_exists($common_functions_filepath) ) { include_once( $common_functions_filepath ); } else { echo "file $common_functions_filepath not found"; }

foreach ( $includes as $inc ) {
    $filepath = $plugin_path . 'includes/'.$inc.'.php'; 
    if ( file_exists($filepath) ) { include_once( $filepath ); } else { echo "inc file $filepath not found"; }
}

foreach ( $modules as $module ) {
    $filepath = $plugin_path . 'modules/'.$module.'.php';
    $arr_exclusions = array ( 'admin_notes', 'data_tables', 'links', 'organizations', 'ensembles', 'press', 'projects', 'sources' ); // , 'groups', 'newsletters', 'snippets', 'logbook', 'venues', 'organs' 
    if ( !in_array( $module, $arr_exclusions) ) { // skip modules w/ no files
    	if ( file_exists($filepath) ) { include_once( $filepath ); } //else { echo "module file $filepath not found"; }
    }
}


// Loop through active modules and add options page per CPT for adding featured image, page-top content, &c.
if ( function_exists('acf_add_options_page') ) {
    
    foreach ( $modules as $module ) {
    
    	$cpt_names = array(); // array because some modules include multiple post types
    	
    	// Deal w/ modules whose names don't perfectly match their CPT names
    	/*if ( $module == "people" ) {
    		$primary_cpt = "person";
    		$cpt_names[] = "person";
    	} else if ( $module == "music" ) {
    		$primary_cpt = "repertoire";
    		$cpt_names[] = "repertoire";
    		//$cpt_names[] = "edition";
    		//$cpt_names[] = "publisher";
    		//$cpt_names[] = "publication";
    		//$cpt_names[] = "music_list";
    	} else */if ( $module == "lectionary" ) {
    		$primary_cpt = "lectionary";
    		//$cpt_names[] = "bible_book";
    		//$cpt_names[] = "reading";
    		//$cpt_names[] = "lectionary";
    		$cpt_names[] = "liturgical_date";
    		//$cpt_names[] = "liturgical_date_calc";
    		//$cpt_names[] = "collect";
    		//$cpt_names[] = "psalms_of_the_day";
    	} else if ( $module == "sermons" ) {
    		$primary_cpt = "sermon";
    		$cpt_names[] = "sermon";
    		$cpt_names[] = "sermon_series";
    	}/* else if ( $module == "events" ) {
    		$primary_cpt = "event";
    		$cpt_names[] = "event";
    		$cpt_names[] = "event_series";
    	} else if ( $module == "organs" ) {
    		$primary_cpt = "organ";
    		$cpt_names[] = "organ";
    		//$cpt_names[] = "builder"; // division, manual, stop
    	} */else {
    		$cpt_name = $module;
    		// Make it singular -- remove trailing "s"
    		if ( substr($cpt_name, -1) == "s" && $cpt_name != "press" ) { $cpt_name = substr($cpt_name, 0, -1); }
    		$primary_cpt = $cpt_name;
    		$cpt_names[] = $cpt_name;
    	}
    	
    	// Add module options page
    	acf_add_options_sub_page(array(
			'page_title'	=> ucfirst($module).' Module Options',
			'menu_title'    => ucfirst($module).' Module Options',//'menu_title'    => 'Archive Options', //ucfirst($cpt_name).
			'menu_slug' 	=> $module.'-module-options',
			'parent_slug'   => 'edit.php?post_type='.$primary_cpt,
		));
			
    	// Add options pages per cpt?
		/*foreach ( $cpt_names as $cpt ) {
		
			acf_add_options_sub_page(array(
				'page_title'     => ucfirst($cpt).' CPT Options',
				'menu_title'    => ucfirst($cpt_name).' Options',//'menu_title'    => 'Archive Options', //ucfirst($cpt_name).
				'menu_slug' 	=> $cpt.'-cpt-options',
				'parent_slug'    => 'edit.php?post_type='.$cpt,
			));
		
		}*/		
		
	}

}

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

//wp_enqueue_script('sdg_js_script', plugins_url('sdg.js', __FILE__), array('json_settings'), true );

/**
 * Enqueue scripts and styles
 */
add_action( 'wp_enqueue_scripts', 'sdg_scripts_method' );
function sdg_scripts_method() {
    
    global $current_user;
    $current_user = wp_get_current_user();
    
    $fpath = WP_PLUGIN_DIR . '/sdg/sdg.css';
    if (file_exists($fpath)) { $ver = filemtime($fpath); } else { $ver = "240823"; }  
    wp_enqueue_style( 'sdg-style', plugins_url( 'sdg.css', __FILE__ ), $ver );
    //wp_enqueue_style( 'sdg-style', plugin_dir_url( __FILE__ ) . 'sdg.css', $ver );
    
    $fpath = WP_PLUGIN_DIR . '/sdg/js/sdg.js';
    if (file_exists($fpath)) { $ver = filemtime($fpath); } else { $ver = date('Ymd.hi'); }
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
	$qvars[] = 'dev';
	$qvars[] = 'y'; // = year -- for date_calculations (and - ?)
    return $qvars;
}

add_filter( 'body_class', 'sdg_body_class' );
function sdg_body_class( $classes ) {
	
	if ( wp_is_mobile() ) { $classes[] = 'mobile'; } else { $classes[] = 'not-mobile'; }
	
    // Determine whether to show admin-specific & troublshooting infos
	$current_user = wp_get_current_user();
    //if ( user_is_administrator() === true ) { 
    if ( current_user_can('edit_roles') || current_user_can('publish_events') ) {
    //if ( in_array( 'stc_administrator', $current_user->roles ) ) {
        $classes[] = 'admin';
        $classes[] = 'admin-view';
    }
    
    $devmode = get_query_var( 'dev' ) ? get_query_var( 'dev' ) : false;
    if ( $devmode ) { $classes[] = 'devmode'; }
    
    // Show troubleshooting info only for webdev
    if ( function_exists( 'queenbee' ) && sdg_queenbee() ) { $classes[] = 'queenbee'; }
    
    // Show TS?
    $options = get_option( 'sdg_settings' );
	if ( isset($options['show_ts']) && !empty($options['show_ts']) ) {
		$classes[] = 'devmode';
	}
	
	if ( is_dev_site() ) { $classes[] = 'devsite'; }
	if ( sdg_editmode() ) { $classes[] = 'editmode'; }
	
	// Always has_msg_bar(?)
	$classes[] = 'has_msg_bar';
    
	// If post has snippets but no widgets, we need to add a body class for styling to preserve the sidebar layout
    if ( function_exists( 'get_snippets' ) ) {
    	
    	$args = array( 'classes' => $classes, 'return' => 'ids' ); //  
    	$snippets = get_snippets( $args );
    	if ( is_array($snippets) && count($snippets) > 0 ) {
    		if ( !is_page_template( 'page-homepage.php' ) &&
    			 !is_page_template( 'page-centered.php' ) &&
    			 !is_page_template( 'page-fullwidth.php' ) ) {
    			 $classes[] = 'snippets';
    		}
    		//if ( array_search('no-sidebar', $classes) == false ) { $classes[] = 'snippets'; }
    		//if (($key = array_search('no-sidebar', $classes)) !== false) { unset($classes[$key]); }
    	} else {
    		$classes[] = 'no-snippets';
    	}
    }
	
	return $classes;
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
    
    if ( is_page() || is_single() || is_singular() ) {
        
        $og_type = "article";
        $post_id = get_queried_object_id();
        $og_url = get_the_permalink( $post_id );
        
        // Get title via post object to avoid filters
        $post = get_post();
        $og_title = $post->post_title;
        //$og_title = get_the_title( $post_id );
        
        // Clean up the title in case it's been formatted for italics etc.
        
        // Get the featured image URL, if there is one
        // TODO: get image from content if no featured image?
        if ( get_the_post_thumbnail_url( $post_id ) ) { $og_image = get_the_post_thumbnail_url( $post_id ); }
        
        // Get and clean up the excerpt for use in the description meta tag
        $excerpt = get_the_excerpt( $post_id );
        if ( !empty($excerpt) ) {
        	$excerpt = str_replace('&nbsp;Read more...','...',$excerpt); // Remove the "read more" tag from auto-excerpts
        	$og_description = wp_strip_all_tags( $excerpt, true );
        }
                
        if ( empty($og_description) ) {
        	if ( $og_title != "Saint Thomas Church" ) {
        		$post_type = ucfirst(get_post_type($post_id));
        		$og_description = $og_title." (".$post_type.")";
        	}        	
        }
        
    }
    
    // Default description
    if ( empty($og_description) ) {
    	$og_description = 'Saint Thomas Fifth Avenue offers at least one mass every single day of the year. On Sundays there is a 9am service suitable for children and families, an 11am High Mass and, during the academic year, a 4pm service of Choral Evensong. Also during the academic term, there are 5:30pm services with our Choir of Men and Boys on most Tuesdays, Wednesdays, and Thursdays.';
    	//$og_description = get_bloginfo( 'description' );
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
add_filter( 'widget_text', 'do_shortcode' );
//
if ( is_dev_site() ) {
	add_filter( 'widget_text', 'shortcode_unautop' );
}

// ACF
add_filter('acf/settings/row_index_offset', '__return_zero');
// TODO: update other calls to ACF functions in case this screws them up?

// Certain operations should only be run in devmode
function devmode_active( $arr_qvar_vals = array() ) {
	
	$devmode = false; // init
	
	// TODO: enforce that user must be logged in as admin OR have proper cookie value saved in order to activate devmode(?)
    /*if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
		$username = $current_user->user_login;
    } else {
    	$username = null;
    }*/
    
	$qvar_val = get_query_var('dev');
	
	if ( $qvar_val && !empty($arr_qvar_vals) && in_array($qvar_val, $arr_qvar_vals) ) {
		return true;
	} else if ( $qvar_val && in_array($qvar_val, array("true","yes") ) ) { //if ( empty($arr_qvar_vals) ) { $arr_qvar_vals = array("true","yes"); }
		return true;
	} else if ( !empty($qvar_val) ) {
		return $qvar_val;        
	}
	
	return false;
}

// Function to determine whether to activate front-end editmode
function sdg_editmode() {
	
	//$dev = devmode_active();	
	$current_user = wp_get_current_user();
	
    if ( devmode_active() === true && in_array( 'administrator', (array) $current_user->roles ) ) {
    	return true;
    } else {
    	return false;
    }
    
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
	
	$devmode = devmode_active( array("sdg") );
	
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
    $wp_args = array(
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
		$wp_args['tax_query'] = array(
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
    $posts = get_posts( $wp_args );
    
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
		'timestamp' => time(),
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
		$wp_args['tax_query'] = $tax_query;
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
		
		//$msg .= "<!-- ".date("l jS \of F Y h:i:s A e")." -->";
		//
		
		$info .= '<div id="post-'.$post_id.'" class="'.$post_type.' featured-post">';
		$info .= "<p>";
		$info .= $msg;
		$info .= $ts_info;
		$info .= "</p>";
		$info .= '</div>';
		$info .= '</div><!-- /banner -->';
		
    } else {
    	
		//$msg .= "<!-- sdg_msg_bar: no active post [".date("l jS \of F Y h:i:s A e")."] -->";
		//$info .= $msg;
		
    }
    
    //$info .= "testing: ".$testing."; orderby: $orderby; order: $order; meta_key: $meta_key; ";
    //$info .= "year: $year<br />";
    //$info .= "[num posts: ".count($webcasts_posts)."]<br />";
	
    return $info;
    
}

add_shortcode('top','anchor_link_top');
function anchor_link_top() {
    return '<a href="#top" class="anchor_top"><span class="up"></span>top</a>';
}

// Function to determine default taxonomy for a given post_type, for use with display_posts shortcode, &c.
function get_default_taxonomy( $post_type = null ) {
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
function get_default_category() {
	
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

// Facilitate search by str in post_title (as oppposed to built-in search by content or by post name, aka slug)
add_filter( 'posts_where', 'sdg_posts_where', 10, 2 );
function sdg_posts_where( $where, $wp_query ) {
    
    global $wpdb;
    
    if ( $search_term = $wp_query->get( '_search_title' ) ) {
        $search_term = $wpdb->esc_like( $search_term );
        $search_term = esc_sql($search_term);
        $search_term = strtolower($search_term);
        //$search_term = '\'%' . $search_term . '%\'';
        $where .= ' AND LOWER(' . $wpdb->posts . '.post_title) LIKE \'%' . $search_term. '%\'';
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
	// meta_keys for repeater fields are named according to the number of rows
	// ... e.g. item_0_description, item_1_description, so we need to adjust the search to use a wildcard for matching
	// Replace comparision operator "=" with "LIKE" and replace the wildcard placeholder "XYZ" with the actual wildcard character "%"
	//$where = str_replace("meta_key = 'program_items_XYZ", "meta_key LIKE 'program_items_%", $where);
    /*$where = str_replace("meta_key = 'program_items_XYZ", "meta_key LIKE 'program_items_%", $where);
    $where = str_replace("meta_key = 'personnel_XYZ", "meta_key LIKE 'personnel_%", $where);
	$where = str_replace("meta_key = 'date_calculations_XYZ", "meta_key LIKE 'date_calculations_%", $where);
	$where = str_replace("meta_key = 'date_assignments_XYZ", "meta_key LIKE 'date_assignments_%", $where);
	*/
	
	$pattern = '/meta_key = \'([A-Za-z_]+)_XYZ/i';
	if ( preg_match("/meta_key = '[A-Za-z_]+_XYZ/i", $where) ) {
		$where = preg_replace($pattern, "meta_key LIKE '$1_%", $where); //$where = str_replace("meta_key = 'program_items_XYZ", "meta_key LIKE 'program_items_%", $where);
	}
	
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
function sdg_list_terms( $atts = array(), $content = null, $tag = '' ) {

	$info = "";
	
	$args = shortcode_atts( array(
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
    
    // Extract
	extract( $args );
	
	$all_items_url = ""; // tft
	$all_items_link = ""; // tft
	$exclusions_per_taxonomy = array(); // init
	
	if ( $tax == "category" ) {
		$exclusions_per_taxonomy = array(); // TODO: set/get this as option -- e.g. STC array(1389, 1674, 1731)
		$all_items_url = "/news/";
	} else if ( $tax == "event-categories" ) {
		$exclusions_per_taxonomy = array();  // TODO: set/get this as option -- e.g. STC array(1675, 1690)
		$all_items_url = "/events/";
	}
	// Turn exclusion/inclusion attribute from comma-sep list into array as prep for merge/ for use w/ sdg_get_terms_orderby
	if ( !empty($exclude) ) { $exclude = array_map('intval', explode(',', $exclude) ); } //$integerIDs = array_map('intval', explode(',', $string));
	if ( !empty($include) ) { $include = array_map('intval', explode(',', $include) ); }
	$exclusions = array_merge($exclude, $exclusions_per_taxonomy);
	$inclusions = $include;
	$term_names_to_skip = array(); // e.g. 'Featured Posts', 'Featured Events'
	
	// List terms in a given taxonomy using wp_list_categories (also useful as a widget if using a PHP Code plugin)
    $wp_args = array(
        'child_of' => $child_of,
		//'depth' => $depth,
		'exclude' => $exclusions,
		'include' => $inclusions,
        //'current_category'    => $cat,
        'taxonomy'     => $tax,
        'orderby'      => $orderby,
        //'show_count'   => $show_count,
        //'hierarchical' => $hierarchical,
        //'title_li'     => $title
    );
	$info .= "<!-- wp_args: ".print_r($wp_args, true)." -->"; // tft
	
	$terms = get_terms($wp_args);
	
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
        if ( $tax === "event-categories" ) {
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
				$term_link = get_term_link( $term );
				/*if ( $tax === "event-categories" ) {
                    $term_link = "/events/?category=".$term->slug; // old way of doing things for EM
                } else {
                    $term_link = get_term_link( $term );
                }*/
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
    $do_ts = devmode_active( array("sdg", "updates") );
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
    $do_ts = devmode_active( array("sdg", "updates") ); 
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
// OR: break it up, move to separate plugins for relevant cpts
function sdg_custom_post_content() {
	
	// TS/logging setup
    $do_ts = devmode_active( array("sdg") ); 
    $do_log = false;
    sdg_log( "divline2", $do_log );
    
    // Init vars
	$info = "";
	$ts_info = "";
	$post_type = get_post_type( get_the_ID() );
	
	$info .= "<!-- START sdg_custom_post_content: ".$post_type." -->";
	$cpt_function = "get_cpt_".$post_type."_content";
	if ( function_exists($cpt_function) ) {
		$cpt_info = $cpt_function();
		if ( is_array($cpt_info) ) {
			$info .= $cpt_info['info'];
			$ts_info .= $cpt_info['ts_info'];
		} else {
			$info .= $cpt_function();
		}
	} else {
		$info .= "<!-- No such function: ".$cpt_function." -->";
	}
	
	if ( $do_ts ) { $info .= $ts_info; }
	$info .= "<!-- END sdg_custom_post_content -->";
	
	return $info;
}
//
add_filter( 'the_content', 'sdg_the_content', 20, 1 );
function sdg_the_content( $content ) {
    //$content .= sdg_custom_post_content();
    $content = sdg_custom_post_content().$content;    
    return $content;    
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
		// NB: default order is DESC by post publication date (TBC)
        if ( isset($query->query_vars['post_type']) ) {
            $post_type = $query->query_vars['post_type'];
            if ( $post_type === 'bible_book' ) {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'sort_num');
                $query->set('order', 'ASC');
            } else if ( $post_type === 'sermon' ) {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', 'sermon_date');
                $query->set('order', 'DESC');
            } else if ( $post_type === 'person' ) {
            	$query->set('orderby', 'meta_value');
                $query->set('meta_key', 'last_name');
                $query->set('order', 'ASC');
            } else if ( $post_type === 'organ' ) { // TODO: change to 'instrument'?
                // borough, post_title
            	$query->set('orderby', 'meta_value title');
                $query->set('meta_key', 'borough');
                $query->set('order', 'ASC');                
            } else if ( $post_type === 'builder' ) {
                $query->set('orderby', 'title');
                $query->set('order', 'ASC');                
            } else if ( $post_type === 'newsletter' ) {
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
    $widget_content = wpautop($widget_content);
    
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
	//$content = preg_replace( '/<p>(\s|&nbsp;)+</p>/', '', $content );
	return $content;
}


/*** MATCH PLACEHOLDERS ***/

/*** Match placeholders to real post objects; return id (single match) or posts array (multiple matches) ***/
function match_placeholder( $args = array() ) {
    
    // TS/logging setup
    $do_ts = devmode_active( array("sdg", "updates") ); 
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
    $wp_args = array(
		'post_type' => $arr_post_types,
		'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby'	=> 'ID',
        'order'	=> 'ASC',
	);
    
    // If we're NOT looking for repertoire, try matching the sanitized title_str to an existing post slug
    // TODO: account for non-rep program_items other than 'sermon' -- e.g. ???? ...
    if ( $field_name != 'program_item' || ( $field_name == 'program_item' && $label_str == 'sermon' ) ) {
        
        $wp_args['name'] = $sanitized_title;
        $wp_args['numberposts'] = 1;
        
    } else {
        
        // For non-sermon program items, add meta_query
        // (1) Search by title_uid/title_for_matching -- best, most likely to be accurate
        $wp_args['meta_query'] = 
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
    
	$arr_posts = new WP_Query( $wp_args );
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

// Convert the time string HH:MM:SS to number of seconds (for webcast cuepoints &c.)
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

// Function to create string from array of meta or taxonomy values
function get_arr_str( $arr, $type = 'posts' ) {

	$str = "";
	if ( empty($arr) || !is_array($arr) ) { return $str; }
	
	// TODO: consider possibility of array of values that are NOT ids?
	foreach ( $arr as $id ) {
		if ( !empty($id) ) {
			if ( $type == "posts" ) {
				$substr = get_the_title( $id );
			} else if ( $type == "terms" ) {
				$term = get_term( $id );
				$substr = $term->name;
			}
			if ($substr) { 
				$str .= $substr;
				if ( count($arr) > 1 ) {
					$str .= ", ";
				}
			}
		}
	}
	if ( count($arr) > 1 && substr($str, -2) == ', ' ) {
		$str = substr($str, 0, -2); // Trim trailing comma and space
	}
	
	return $str;

}

// https://developer.wordpress.org/reference/hooks/document_title_parts/
// Filters the parts of the document title.
add_filter( 'document_title_parts', function( $title_parts_array ) {
    
    // TS/logging setup
    $do_ts = devmode_active( array("sdg") );
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
function get_image_url_from_id( $atts = array() ) {
    
    $args = shortcode_atts( array(
		'id'   => null
	), $atts );
    
    // Extract
	extract( $args );
	
	if ( ! empty($id) ) { $url = wp_get_attachment_url($id); } else { $url = null; }
	
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
function sdg_selectmenu ( $args = array() ) {
	
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
		'display_formula' => 'post_title',
	);
	
	// Parse & Extract args
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
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
		$dropdown_menu .= '<select name="'.$select_name.'" id="'.$select_name.'" class="postform '.$display_formula.'">';
		
		// Add the "Any" option at the top of the list
		if ($selected == 'any') { $selection = ' selected="selected"'; } else { $selection = ''; }
		if ($label == "Text") { $label = "Book"; } // TODO: do this better -- perhaps menu label & other label...
		$dropdown_menu .= '<option class="level-0" value="any"'.$selection.'>Any '.$label.'</option>';
		
		$count_optgroups = 0; // init
		
		if ( $display_formula != 'post_title' ) {
			$display_formula = get_field($display_formula, 'option');
		}
		
		// Loop through the array of select options
		foreach ($arr_values as $key => $value) {
			
			if (is_object($value)) {
				$obj = $value;
				if ($obj->ID) {
					$obj_id = $obj->ID;
					$value = $obj_id;
					$display_value = "";
					// TODO: add option to display a meta_value instead of the post_title
					if ( $display_formula != 'post_title' ) {
						$fields = explode( " ",$display_formula );
						foreach ( $fields as $field ) {
							$fv = get_field($field, $obj_id, false);
							$display_value .= $fv." ";
						}
					}
					if ( empty($display_value) && $obj->post_title ) { 
						$display_value = $obj->post_title; 
					}
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
function sdg_search_filter_links ( $atts = array(), $content = null, $tag = '' ) {

	//global $wp;
	//esc_url( remove_query_arg( 'type' ) ); // this doesn't seem to work
	//$current_url = home_url( add_query_arg( array(), $wp->request ) );
  	$current_url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
	//$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) ); // nope
	$current_url = remove_query_arg( 'type', $current_url );
	//$current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	$info = "";
	
	$args = shortcode_atts( array(
		'exclude'       => '',
		'include'       => '',
     	'orderby'		=> 'name', // 'id', 'meta_value'
      	'show_count'	=> 1,
		'title'        	=> '',
    ), $atts );
    
    // Extract
	extract( $args );
	
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
	//$datestamp = current_time('Ymd'); // date('d-M-Y') // Old version -- daily logs
	// New version -- monthly logs
	$this_month = current_time('Ym');
    $log_file = $log_filename.'/'.$this_month.'-sdg_dev.log';
	
	// TODO/WIP: check server for past months' logs and delete them if they're more than one month old
	$last_month = date("Ym", strtotime("first day of previous month"));
	//$two_months_ago = $this_month-2;
	$two_months_ago = date("Ym", strtotime("-2 months"));
	if ( $last_month != $two_months_ago ) {
		$stale_log_file = $log_filename.'/'.$two_months_ago.'-sdg_dev.log';
	} else {
		$two_months_ago = date("Ym", strtotime("-3 months"));
		// wip deal w/ possible issue with date math if month has 31 days...
		$stale_log_file = $log_filename.'/'.$two_months_ago.'-sdg_dev.log';
	}	
	if (file_exists($stale_log_file)) {
		$log_msg .= "\n>>>sdg_log<<< unlinked stale_log_file: $stale_log_file";
		unlink($stale_log_file);
	} else {
		//$log_msg .= "\n>>>sdg_log<<< No match found for stale_log_file: $stale_log_file"; // tft
	}
	
	// Syntax: file_put_contents(filename, data, mode, context)
	// (If filename does not exist, the file is created. Otherwise, the existing file is overwritten, unless the FILE_APPEND flag is set.)
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

/*** MISC ***/

// Function to delete capabilities
// Based on http://chrisburbridge.com/delete-unwanted-wordpress-custom-capabilities/
//add_action( 'admin_init', 'sdg_clean_unwanted_caps' ); // tmp disabled -- this function need only ever run once per site per set of caps
function sdg_clean_unwanted_caps() {
	global $wp_roles;
	$delete_caps = array(
		
        'edit_ensembles', 'edit_published_ensembles', 'edit_private_ensembles', 'edit_others_ensembles', 'delete_ensembles', 'delete_published_ensembles', 'delete_others_ensembles', 'delete_private_ensembles', 'publish_ensembles', 'read_private_ensembles',
        'edit_lectionary', 'edit_published_lectionary', 'edit_private_lectionary', 'edit_others_lectionary', 'delete_lectionary', 'delete_published_lectionary', 'delete_others_lectionary', 'delete_private_lectionary', 'publish_lectionary', 'read_private_lectionary',
        'edit_lectionarys', 'edit_published_lectionarys', 'edit_private_lectionarys', 'edit_others_lectionarys', 'delete_lectionarys', 'delete_published_lectionarys', 'delete_others_lectionarys', 'delete_private_lectionarys', 'publish_lectionarys', 'read_private_lectionarys',
        'edit_lectionary_days', 'edit_published_lectionary_days', 'edit_private_lectionary_days', 'edit_others_lectionary_days', 'delete_lectionary_days', 'delete_published_lectionary_days', 'delete_others_lectionary_days', 'delete_private_lectionary_days', 'publish_lectionary_days', 'read_private_lectionary_days',
        'edit_music', 'edit_published_music', 'edit_private_music', 'edit_others_music', 'delete_music', 'delete_published_music', 'delete_others_music', 'delete_private_music', 'publish_music', 'read_private_music',
        'edit_musics', 'edit_published_musics', 'edit_private_musics', 'edit_others_musics', 'delete_musics', 'delete_published_musics', 'delete_others_musics', 'delete_private_musics', 'publish_musics', 'read_private_musics',
        //'edit_dinosaurs', 'edit_published_dinosaurs', 'edit_private_dinosaurs', 'edit_others_dinosaurs', 'delete_dinosaurs', 'delete_published_dinosaurs', 'delete_others_dinosaurs', 'delete_private_dinosaurs', 'publish_dinosaurs', 'read_private_dinosaurs',
        
	 );
	foreach ($delete_caps as $cap) {
		foreach (array_keys($wp_roles->roles) as $role) {
			$wp_roles->remove_cap($role, $cap);
		}
	}
}

/**
 * Debug Pending Updates
 *
 * Crude debugging method that will spit out all pending plugin
 * and theme updates for admin level users when ?debug_updates is
 * added to a /wp-admin/ URL.
 */
function debug_pending_updates() {

    // Rough safety nets
    if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) return;
    if ( ! isset( $_GET['debug_updates'] ) ) return;

    $output = "";

    // Check plugins
    $plugin_updates = get_site_transient( 'update_plugins' );
    if ( $plugin_updates && ! empty( $plugin_updates->response ) ) {
        foreach ( $plugin_updates->response as $plugin => $details ) {
            $output .= "<p><strong>Plugin</strong> <u>$plugin</u> is reporting an available update.</p>";
        }
    }

    // Check themes
    wp_update_themes();
    $theme_updates = get_site_transient( 'update_themes' );
    if ( $theme_updates && ! empty( $theme_updates->response ) ) {
        foreach ( $theme_updates->response as $theme => $details ) {
            $output .= "<p><strong>Theme</strong> <u>$theme</u> is reporting an available update.</p>";
        }
    }

    if ( empty( $output ) ) $output = "No pending updates found in the database.";

    wp_die( $output );
}
//add_action( 'init', 'debug_pending_updates' ); // activate as needed

/*******/
function surprise() {

	// Set up an array of fun words and things...
	$surprises = array();
	
	// Pick one at random
	$surprise = array_rand($surprises, 1);
	
	// Surprise!
	return $surprise;	

}

?>