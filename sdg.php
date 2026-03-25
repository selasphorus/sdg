<?php
/**
 * @package SDG
 */

/*
Plugin Name: SDG-OG
Plugin URI:
Description: Custom post types, taxonomies and functions for music and more
Version: 1.032526.1
Author: atc
Author URI:
License:
Text Domain: sdg
*/

// NB: this is a transitional version of the plugins, with all methods removed that don't pertain to the lectionary or worship
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

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */
if ( ! is_admin() ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' ); // so that we can run functions like post_exists on the front end
}

/* +~+~+ ACF +~+~+ */

// Set custom load & save JSON points for ACF sync
require 'includes/acf-json.php';

// Restrict access to ACF Admin screens
require 'includes/acf-restrict-access.php';

// Display and template helpers
require 'includes/template-tags.php';

// Load ACF field groups hard-coded as PHP
require 'includes/acf-field-groups.php';

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

    // Register a new "Modules" section in the settings page.
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
            //'value'                => (empty(get_option('sdg_settings')['sdg_modules'])) ? 0 : get_option('sdg_settings')['sdg_modules'],
            'class'             => 'sdg_row',
            'sdg_custom_data'     => 'custom',
        )
    );
}

/**
 * Settings section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function sdg_settings_section_callback( $args ) {
    $options = get_option( 'sdg_settings' );
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
function sdg_caps_field_cb( $args )
{
    $checked = '';
    $options = get_option( 'sdg_settings' );
    $value   = ( !isset( $options[$args['name']] ) ) ? null : $options[$args['name']];
    if ($value) { $checked = ' checked="checked" '; }
	// Could use ob_start.
	$html  = '';
	$html .= '<input id="' . esc_attr( $args['name'] ) . '" name="sdg_settings' . esc_attr('['.$args['name'].']') .'" type="checkbox" ' . $checked . '/>';
	$html .= '<span class="">' . esc_html( $args['description'] ) .'</span>';
	echo $html;
}

function sdg_modules_field_cb( $args ) 
{
    $options = get_option( 'sdg_settings' );
    $modules = array(
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
        'slider' => __( 'Slider' ),
        'ninjaforms' => __( 'Ninja Forms' ),
        //
        'admin_notes' => __( 'Admin Notes' ),
        'data_tables' => __( 'Data Tables' ),
    );

    $value   = ( !isset( $options[$args['label_for']] ) ) ? array() : $options[$args['label_for']];

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
            />
            <label for="sdg_modules_<?php echo esc_attr( $name ); ?>" class="sdg-option-label">
            <?php echo esc_html( $modules[ $name ] ); ?>
            </label>
        </div>
        <?php
    }
}

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
            'page_title'    => ucfirst($module).' Module Options',
            'menu_title'    => ucfirst($module).' Module Options',//'menu_title'    => 'Archive Options', //ucfirst($cpt_name).
            'menu_slug'     => $module.'-module-options',
            'parent_slug'   => 'edit.php?post_type='.$primary_cpt,
        ));

        // Add options pages per cpt?
        /*foreach ( $cpt_names as $cpt ) {

            acf_add_options_sub_page(array(
                'page_title'     => ucfirst($cpt).' CPT Options',
                'menu_title'    => ucfirst($cpt_name).' Options',//'menu_title'    => 'Archive Options', //ucfirst($cpt_name).
                'menu_slug'     => $cpt.'-cpt-options',
                'parent_slug'    => 'edit.php?post_type='.$cpt,
            ));

        }*/

    }

}

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

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
