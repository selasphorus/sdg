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

/* +~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+~+ */

// Register our sdg_settings_init to the admin_init action hook.
add_action( 'admin_init', 'sdg_settings_init' );

/**
 * Custom option and settings
 */
function sdg_settings_init() {

	// Register a new setting for "sdg" page.
	register_setting( 'sdg', 'sdg_options' );

	// Register a new section in the "sdg" page.
	add_settings_section(
		'sdg_settings',
		__( 'SDG Plugin Settings', 'sdg' ), 'sdg_settings_section_callback',
		'sdg'
	);

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
	
}

/**
 * Settings section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function sdg_settings_section_callback( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Test Settings Section Header', 'sdg' ); ?></p>
	<?php
}

/**
 * Pill field callback function.
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
	$options = get_option( 'sdg_options' );
	?>
	<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			data-custom="<?php echo esc_attr( $args['sdg_custom_data'] ); ?>"
			name="sdg_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
		<option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'red pill', 'sdg' ); ?>
		</option>
 		<option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
			<?php esc_html_e( 'blue pill', 'sdg' ); ?>
		</option>
	</select>
	<p class="description">
		<?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'sdg' ); ?>
	</p>
	<p class="description">
		<?php esc_html_e( 'You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.', 'sdg' ); ?>
	</p>
	<?php
}


// Render a text field -- example
function sdg_text_field_cb( $args ) {
	$options = get_option( 'sdg_options' );
	?>
	<input type="text" 
		id="<?php echo esc_attr( $args['id'] ); ?>" 
		name="sdg_examples_options[<?php echo esc_attr( $args['name'] ); ?>]" 
		value="<?php echo isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : esc_attr( $args['default_value']); ?>" 
		class="<?php echo isset($args['class']) ? $args['class']: '' ?>" 
		style="<?php echo isset($args['style']) ? $args['style']: '' ?>" 
		placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"/>
	<?php
}


// Register our sdg_options_page to the admin_menu action hook.
add_action( 'admin_menu', 'sdg_options_page' );

// Add the top level menu page.
function sdg_options_page() {
	add_menu_page(
		'SDG',
		'SDG Options',
		'manage_options',
		'sdg',
		'sdg_options_page_html'
	);
}

/**
 * Top level menu callback function
 */
function sdg_options_page_html() {

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

?>